<?php

namespace App\Imports;

use App\Models\MachineBreakdown;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class MachineBreakdownImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $row = $row instanceof Collection ? $row : collect($row);

            $line = $this->pick($row, ['line', 'line no', 'line_no', 'Line No']);
            if (blank($line)) {
                continue;
            }

            $dateValue = $this->pick($row, ['date', 'Date', 'tanggal', 'Tanggal']);
            $statusValue = $this->pick($row, ['status', 'Status']);
            // Import all rows without filtering by status (both Result and Target are recorded)
            $kpiValue = $this->pick($row, ['kpi', 'KPI', 'kpi value']);
            $subKpValue = $this->pick($row, ['sub_kp', 'sub kp', 'sub kpi', 'Sub KPI', 'Sub-KP', 'subkp']);
            $catValue = $this->pick($row, ['cat', 'Cat', 'category', 'Category']);
            $durationValue = $this->pick($row, ['duration', 'Duration', 'durasi', 'Durasi', 'duration mtbf', 'durasi mtbf', 'mtbf duration', 'mtbfduration']);
            $frequencyValue = $this->pick($row, ['frequency', 'Frequency', 'freq', 'Freq', 'frekuensi', 'Frekuensi']);
            $persenValue = $this->pick($row, ['', '%', 'percent', 'persen', 'persentase', 'Persen', 'Persentase', 'Percent', 'pct']);
            $targetValue = $this->pick($row, ['target', 'Target', 'target mtbf', 'Target MTBF', 'target_mbtf', 'mtbf target', 'MTBF Target']);
            $mtbfValue = $this->pick($row, ['mtbf', 'MTBF', 'mbtf', 'MBTF', 'avg mtbf', 'Avg MTBF', 'mtbf average', 'MTBF Average']);
            $targetMtbfValue = $this->pick($row, ['target mtbf', 'target_mbtf', 'mtbf target', 'MTBF Target', 'mbtf target']);

            if (blank($durationValue) && ! blank($mtbfValue)) {
                $durationValue = $mtbfValue;
            }

            if (blank($targetValue) && ! blank($targetMtbfValue)) {
                $targetValue = $targetMtbfValue;
            }

            $parsedDate = $this->parseDate($dateValue);
            $duration = $this->toFloat($durationValue);
            $frequency = $this->toFloat($frequencyValue);
            $percentage = $this->normalizePercent($persenValue);
            $target = $this->normalizePercent($targetValue, 1.5);

            MachineBreakdown::updateOrCreate(
                [
                    'date' => $parsedDate,
                    'status' => $statusValue,
                    'kpi' => $kpiValue,
                    'sub_kp' => $subKpValue,
                    'line' => $line,
                ],
                [
                    'cat' => $catValue,
                    'duration' => $duration,
                    'frequency' => $frequency,
                    'persen' => $percentage,
                    'target' => $target,
                ]
            );
        }
    }

    protected function normalizePercent($value, $default = null)
    {
        $number = $this->toFloat($value);

        if ($number === null) {
            return $default;
        }

        return $number;
    }

    protected function parseDate($value)
    {
        if (blank($value)) {
            return null;
        }

        $value = trim((string) $value);

        if (preg_match('/^\d+(?:\.\d+)?$/', $value)) {
            $numeric = (float) $value;
            if ($numeric > 1000 && $numeric < 60000) {
                try {
                    return ExcelDate::excelToDateTimeObject($numeric)->format('Y-m-d');
                } catch (\Throwable $e) {
                    // continue to next parse attempt
                }
            }
        }

        // Check if format is 'M-y', e.g. 'Apr-26' or 'Jan-27'
        if (preg_match('/^[A-Za-z]{3}-\d{2}$/', $value)) {
            try {
                return Carbon::createFromFormat('M-y', $value)->startOfMonth()->format('Y-m-d');
            } catch (\Throwable $e) {
                // fallback to general parse
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function toFloat($value)
    {
        if (blank($value)) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '-' || $value === '#DIV/0!' || $value === '#N/A' || $value === '#VALUE!') {
            return null;
        }

        // Standardize European vs US decimals:
        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace(',', '', $value);
        } elseif (str_contains($value, ',') && !str_contains($value, '.')) {
            if (substr_count($value, ',') > 1) {
                $value = str_replace(',', '', $value);
            } else {
                if (preg_match('/^\s*\d{1,3},\d{3}\s*$/', $value)) {
                    $value = str_replace(',', '', $value);
                } else {
                    $value = str_replace(',', '.', $value);
                }
            }
        }

        $value = preg_replace('/[^\d.\-]/', '', $value);

        if (substr_count($value, '.') > 1) {
            $parts = explode('.', $value);
            $value = array_shift($parts) . '.' . implode('', $parts);
        }

        return is_numeric($value) ? (float) $value : null;
    }

    protected function pick(Collection $row, array $keys)
    {
        $normalizedMap = [];
        foreach ($row->keys() as $actualKey) {
            $normalizedMap[$this->normalizeKey((string) $actualKey)] = $actualKey;
        }

        // 1. First pass: exact normalized match
        foreach ($keys as $key) {
            $target = $this->normalizeKey($key);
            if (isset($normalizedMap[$target])) {
                $actualKey = $normalizedMap[$target];
                $value = $row->get($actualKey);
                $cleaned = $this->cleanValue($value);
                if (!blank($cleaned)) {
                    return $cleaned;
                }
            }
        }

        // 2. Second pass: prefix/suffix/contains match ONLY if target is not empty
        foreach ($keys as $key) {
            $target = $this->normalizeKey($key);
            if (empty($target)) {
                continue;
            }
            foreach ($normalizedMap as $normalizedName => $actualName) {
                if (empty($normalizedName)) {
                    continue;
                }
                if ($normalizedName === $target || str_starts_with($normalizedName, $target)) {
                    $value = $row->get($actualName);
                    $cleaned = $this->cleanValue($value);
                    if (!blank($cleaned)) {
                        return $cleaned;
                    }
                }
            }
        }

        return null;
    }

    protected function cleanValue($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        return trim((string) $value);
    }

    protected function normalizeKey($value)
    {
        $value = strtolower(trim((string) $value));
        if ($value === '%' || $value === 'persen' || $value === 'percent') {
            return 'percent';
        }
        $value = preg_replace('/[^a-z0-9]+/', '', $value);

        return $value;
    }
}

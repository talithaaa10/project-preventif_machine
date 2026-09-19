<?php

namespace App\Imports;

use App\Models\Battery;
use App\Models\InputBattery;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class BatteryImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            0 => new MasterBatterySheetImport(),
            1 => new InputBatterySheetImport(),
        ];
    }
}

/**
 * SHEET 1: Master Battery Asset Data -> tabel `batteries`
 */
class MasterBatterySheetImport implements ToModel, WithHeadingRow, WithCalculatedFormulas
{
    public function model(array $rawRow)
    {
        $row = [];
        foreach ($rawRow as $key => $val) {
            $cleanKey = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string)$key));
            $row[$cleanKey] = is_string($val) ? trim($val) : $val;
        }

        $line = $this->cleanString($row['line'] ?? null, 191);
        $machineNo = $this->cleanString($row['machineno'] ?? null, 191);
        $batteryModel = $this->cleanString($row['batterymodel'] ?? null, 191);
        $area = $this->cleanString($row['area'] ?? null, 191);

        if (empty($line) && empty($machineNo) && empty($batteryModel) && empty($area)) {
            return null;
        }



        $installDate = $this->parseDate($row['installdate'] ?? null);
        $replacementCycleMonth = isset($row['replacementcyclemonth']) ? (int) $row['replacementcyclemonth'] : null;
        $nextReplaceDate = $this->parseDate($row['nextreplacedate'] ?? null);

        if (!$nextReplaceDate && $installDate && $replacementCycleMonth > 0) {
            $nextReplaceDate = $installDate->copy()->addMonths($replacementCycleMonth);
        }

        $statusExcel = strtolower((string)($row['statusaktif'] ?? $row['status'] ?? ''));

        $today = Carbon::today();
        $in30Days = Carbon::today()->addDays(30);

        if ($statusExcel === 'aktif' || $statusExcel === 'active') {
            $status = 'active';
        } elseif ($statusExcel === 'warning') {
            $status = 'warning';
        } elseif ($statusExcel === 'error' || $statusExcel === 'change' || $statusExcel === 'expired') {
            $status = 'error';
        } elseif (is_null($nextReplaceDate)) {
            $status = 'error';
        } elseif ($nextReplaceDate->lt($today)) {
            $status = 'error';
        } elseif ($nextReplaceDate->lte($in30Days)) {
            $status = 'warning';
        } else {
            $status = 'active';
        }

        return new Battery([
            'level' => $this->cleanString($row['level'] ?? null, 191),
            'battery_id' => $this->cleanString($row['batteryid'] ?? null, 191),
            'area' => $area,
            'line' => $line,
            'machine_no' => $machineNo,
            'machine_name' => $this->cleanString($row['machinename'] ?? null, 191),
            'maker' => $this->cleanString($row['maker'] ?? null, 191),
            'equipment_type' => $this->cleanString($row['equipmenttype'] ?? null, 191),
            'device' => $this->cleanString($row['device'] ?? null, 191),
            'battery_model' => $batteryModel,
            'battery_type' => $this->cleanString($row['batterytype'] ?? null, 191),
            'std_volt' => $this->cleanString($row['stdvolt'] ?? null, 191),
            'install_date' => $installDate,
            'replacement_cycle_month' => $replacementCycleMonth,
            'next_replace_date' => $nextReplaceDate,
            'how_many' => $this->cleanString($row['howmany'] ?? null, 255),
            'number_of' => $this->cleanString(
                $row['numberofmachine'] ??
                $row['numberofmachines'] ??
                $row['numberof'] ??
                $row['number_of_machine'] ??
                null,
                255
            ),
            'aggregate' => $this->cleanString(
                $row['aggregatetotaldevice'] ??
                $row['aggregate'] ??
                $row['totaldevice'] ??
                null,
                255
            ),
            'op_number' => $this->cleanString($row['opnumber'] ?? $row['opno'] ?? null, 255),
            'exchange_type' => $this->cleanString($row['exchangetype'] ?? null, 255),
            'trend_pengganti' => $this->cleanString($row['trendpengganti'] ?? $row['trendpenggantian'] ?? null, 255),
            'status_aktif' => $status,
        ]);
    }

    private function cleanString($val, int $maxLen = 191): ?string
    {
        if (is_null($val) || $val === '') {
            return null;
        }

        $str = trim((string)$val);

        // Abaikan jika berupa formula Excel mentah yang gagal terhitung
        if ($str === '' || str_starts_with($str, '=')) {
            return null;
        }

        // Abaikan error rumus Excel
        if (in_array(strtoupper($str), ['#VALUE!', '#REF!', '#N/A', '#NAME?', '#NUM!', '#DIV/0!', '#NULL!'])) {
            return null;
        }

        return mb_substr($str, 0, $maxLen);
    }

    private function parseDate($value)
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        if (is_string($value)) {
            $trim = trim($value);
            if ($trim === '' || str_starts_with($trim, '=') || in_array(strtoupper($trim), ['#VALUE!', '#REF!', '#N/A'])) {
                return null;
            }
        }

        if ($value instanceof \DateTime) {
            return Carbon::instance($value);
        }

        if (is_numeric($value)) {
            try {
                $dt = ExcelDate::excelToDateTimeObject((float) $value);
                return Carbon::instance($dt);
            } catch (\Throwable $e) {
                return null;
            }
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }
}

/**
 * SHEET 2: Input Data Battery Operational -> tabel `input_batteries`
 */
class InputBatterySheetImport implements ToModel, WithHeadingRow, WithCalculatedFormulas
{
    public function model(array $rawRow)
    {
        $row = [];
        foreach ($rawRow as $key => $val) {
            $cleanKey = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string)$key));
            $row[$cleanKey] = is_string($val) ? trim($val) : $val;
        }

        $line = $this->cleanString($row['line'] ?? null, 100);
        $opNumber = $this->cleanString($row['opnumber'] ?? $row['opno'] ?? null, 100);
        $machineNo = $this->cleanString($row['machineno'] ?? null, 100);
        $batteryModel = $this->cleanString($row['batterymodel'] ?? null, 100);

        if (empty($line) && empty($machineNo) && empty($opNumber) && empty($batteryModel)) {
            return null;
        }

        $lastDay = $this->parseDate($row['lastday'] ?? $row['lastdaypengganti'] ?? null);
        $trendPengganti = $this->parseDate($row['trendpengganti'] ?? $row['trendpenggantian'] ?? null);
        $standartVolt = $this->cleanString($row['standartvolt'] ?? $row['stdvolt'] ?? null, 50);
        $exchangeType = $this->cleanString($row['exchangetype'] ?? null, 100);
        $equipmentType = $this->cleanString($row['equipmenttype'] ?? null, 100);

        // Cari relasi ke Master Battery (Sheet 1) berdasarkan line dan machine_no
        $matchedBattery = null;
        if (!empty($line) && !empty($machineNo)) {
            $matchedBattery = Battery::whereRaw('LOWER(TRIM(line)) = ?', [strtolower(trim($line))])
                ->whereRaw('LOWER(TRIM(machine_no)) = ?', [strtolower(trim($machineNo))])
                ->first();
        }

        $status = 'active';
        $today = Carbon::today();
        $in30Days = Carbon::today()->addDays(30);

        if ($trendPengganti) {
            if ($trendPengganti->lt($today)) {
                $status = 'error';
            } elseif ($trendPengganti->lte($in30Days)) {
                $status = 'warning';
            } else {
                $status = 'active';
            }
        } elseif (is_null($trendPengganti) && is_null($lastDay)) {
            $status = 'error';
        }

        $area = $this->cleanString($row['area'] ?? null, 100);
        if (empty($area) && $matchedBattery) {
            $area = $matchedBattery->area;
        }
        if (empty($area) && !empty($line)) {
            $area = Battery::where('line', $line)->whereNotNull('area')->value('area');
        }

        return new InputBattery([
            'battery_id' => $matchedBattery?->id,
            'area' => $area,
            'line' => $line,
            'op_number' => $opNumber,
            'machine_no' => $machineNo,
            'equipment_type' => $equipmentType,
            'battery_model' => $batteryModel,
            'exchange_type' => $exchangeType,
            'last_day' => $lastDay,
            'trend_pengganti' => $trendPengganti,
            'standart_volt' => $standartVolt,
            'status' => $status,
        ]);
    }

    private function cleanString($val, int $maxLen = 100): ?string
    {
        if (is_null($val) || $val === '') {
            return null;
        }

        $str = trim((string)$val);

        if ($str === '' || str_starts_with($str, '=')) {
            return null;
        }

        if (in_array(strtoupper($str), ['#VALUE!', '#REF!', '#N/A', '#NAME?', '#NUM!', '#DIV/0!', '#NULL!'])) {
            return null;
        }

        return mb_substr($str, 0, $maxLen);
    }

    private function parseDate($value)
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        if (is_string($value)) {
            $trim = trim($value);
            if ($trim === '' || str_starts_with($trim, '=') || in_array(strtoupper($trim), ['#VALUE!', '#REF!', '#N/A'])) {
                return null;
            }
        }

        if ($value instanceof \DateTime) {
            return Carbon::instance($value);
        }

        if (is_numeric($value)) {
            try {
                $dt = ExcelDate::excelToDateTimeObject((float) $value);
                return Carbon::instance($dt);
            } catch (\Throwable $e) {
                return null;
            }
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }
}

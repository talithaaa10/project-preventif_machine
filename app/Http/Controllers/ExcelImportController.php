<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

class ExcelImportController extends Controller
{
    public function import(Request $request)
    {
        // 1. Validasi File
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls'
        ]);

        $file = $request->file('file_excel');
        $spreadsheet = IOFactory::load($file->getRealPath());

        // 2. Looping membaca SEMUA Sheet yang ada di file Excel tersebut
        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();

            // Ambil Header (Baris ke-1)
            $headers = [];
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $val = strtolower(trim($sheet->getCell($col . '1')->getValue()));
                if ($val) {
                    $headers[$col] = $val;
                }
            }

            // Gabungkan nama-nama header untuk pengecekan kata kunci
            $headerString = implode(' ', $headers);

            // =========================================================
            // DISTRIBUSI AUTOMATIS KE TABEL MYSQL SESUAI JENIS DATA
            // =========================================================

            // A. TABEL MACHINES (Data Line & Master Mesin)
            if (str_contains($headerString, 'line') || str_contains($headerString, 'mc')) {
                // Opsional: Hapus data lama jika mau di-overwrite
                // DB::table('machines')->truncate();

                for ($row = 2; $row <= $highestRow; $row++) {
                    $lineName = $this->getValueByHeader($sheet, $headers, $row, ['line', 'nama line', 'line name']);
                    $type     = $this->getValueByHeader($sheet, $headers, $row, ['type', 'jenis', 'mc_type', 'kategori']);

                    if ($lineName) {
                        DB::table('machines')->insert([
                            'line_name'  => $lineName,
                            'type'       => $type ?? 'Main Machine',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // B. TABEL KPIS (MTTR & MTBF)
            elseif (str_contains($headerString, 'mttr') || str_contains($headerString, 'mtbf')) {
                for ($row = 2; $row <= $highestRow; $row++) {
                    $period = $this->getValueByHeader($sheet, $headers, $row, ['year', 'tahun', 'month', 'periode']);
                    $mttr   = $this->getValueByHeader($sheet, $headers, $row, ['mttr']);
                    $mtbf   = $this->getValueByHeader($sheet, $headers, $row, ['mtbf']);

                    if ($period) {
                        DB::table('kpis')->insert([
                            'period'     => $period,
                            'mttr'       => (float)$mttr,
                            'mtbf'       => (float)$mtbf,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // C. TABEL BATTERY_MONITORINGS
            elseif (str_contains($headerString, 'battery') || str_contains($headerString, 'voltage') || str_contains($headerString, 'volt')) {
                for ($row = 2; $row <= $highestRow; $row++) {
                    $batteryId = $this->getValueByHeader($sheet, $headers, $row, ['battery_id', 'no battery', 'nama battery']);
                    $voltage   = $this->getValueByHeader($sheet, $headers, $row, ['voltage', 'tegangan', 'volt']);

                    if ($batteryId) {
                        DB::table('battery_monitorings')->insert([
                            'battery_id' => $batteryId,
                            'voltage'    => (float)$voltage,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // D. TABEL PM_MONITORINGS
            elseif (str_contains($headerString, 'pm') || str_contains($headerString, 'preventive')) {
                for ($row = 2; $row <= $highestRow; $row++) {
                    $month = $this->getValueByHeader($sheet, $headers, $row, ['month', 'bulan']);
                    $ach   = $this->getValueByHeader($sheet, $headers, $row, ['achievement', 'persen', '%', 'achieved']);

                    if ($month) {
                        DB::table('pm_monitorings')->insert([
                            'month'               => $month,
                            'achievement_percent' => (float)$ach,
                            'created_at'          => now(),
                            'updated_at'          => now(),
                        ]);
                    }
                }
            }
        }

        return back()->with('success', 'Semua sheet di file Excel berhasil di-import ke tabelnya masing-masing!');
    }

    // Helper fungsi pencari data berdasar kemiripan nama header
    private function getValueByHeader($sheet, $headers, $row, array $keywords)
    {
        foreach ($headers as $col => $headerName) {
            foreach ($keywords as $keyword) {
                if (str_contains($headerName, $keyword)) {
                    return $sheet->getCell($col . $row)->getValue();
                }
            }
        }
        return null;
    }
}

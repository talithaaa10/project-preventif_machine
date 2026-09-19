<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\MachineBreakdown;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory; // Import library untuk membaca Excel

class Analytics extends Controller
{
    public function index(Request $request)
    {
        // Tangani jika ada form login yang mengirim POST ke /dashboard
        if ($request->isMethod('post') && $request->filled('email-username') && $request->filled('password')) {
            $username = $request->input('email-username');
            $password = $request->input('password');
            $user = \App\Models\User::where('name', $username)->first();

            if ($user && (\Illuminate\Support\Facades\Hash::check($password, $user->password) || $user->password === $password)) {
                \Illuminate\Support\Facades\Auth::login($user);
                session([
                    'user_name' => $user->name,
                    'user_role' => $user->role,
                ]);
            }
        }

        // 1. Tentukan lokasi file Excel kamu di storage
        $filePath = storage_path('app/public/Data_Mesin.xlsx');

        // Variabel default jika file belum/tidak ditemukan
        $lineLabels      = [];
        $mainMachineData = [];
        $equipmentData   = [];
        $pmLabels        = ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'];
        $pmData          = [95, 98, 100, 85, 90, 92];

        // 2. Cek apakah file Excel ada
        if (file_exists($filePath)) {
            $spreadsheet = IOFactory::load($filePath);

            // --- BACA SHEET 'Line' (Daftar Nama Line) ---
            $sheetLine = $spreadsheet->getSheetByName('Line');
            $validLines = [];

            if ($sheetLine) {
                $highestRowLine = $sheetLine->getHighestRow();
                for ($row = 2; $row <= $highestRowLine; $row++) {
                    $lineName = trim($sheetLine->getCell('B' . $row)->getValue());
                    if ($lineName) {
                        $validLines[$lineName] = [
                            'Main'      => 0,
                            'Equipment' => 0
                        ];
                    }
                }
            }

            // --- BACA SHEET 'list MC' (Hitung Main Machine & Equipment) ---
            $sheetListMC = $spreadsheet->getSheetByName('list MC');

            if ($sheetListMC) {
                $highestRowMC = $sheetListMC->getHighestRow();

                for ($row = 2; $row <= $highestRowMC; $row++) {
                    $mcLine = trim($sheetListMC->getCell('B' . $row)->getValue());
                    $mcType = trim($sheetListMC->getCell('C' . $row)->getValue());

                    if ($mcLine && isset($validLines[$mcLine])) {
                        // Cek jenis mesin dengan kata "Main"
                        if (stripos($mcType, 'main') !== false) {
                            $validLines[$mcLine]['Main']++;
                        } else {
                            $validLines[$mcLine]['Equipment']++;
                        }
                    }
                }
            }

            // Extrak hasil olahan data ke bentuk array sederhana
            $lineLabels      = array_keys($validLines);
            $mainMachineData = array_column($validLines, 'Main');
            $equipmentData   = array_column($validLines, 'Equipment');
        }

        // =========================================================================
        // DATA 4 KPI BREAKDOWN & LOSS (MACHINE BREAKDOWN, LINE STOP, MTTR, MTBF)
        // =========================================================================
        $fiscalYearStartMonth = 4;
        $defaultFiscalYear = now()->month >= $fiscalYearStartMonth ? now()->year : now()->year - 1;
        $selectedYear = (int) ($request->get('year', $defaultFiscalYear));
        $fiscalMonthOrder = [4, 5, 6, 7, 8, 9, 10, 11, 12, 1, 2, 3];

        $startDate = Carbon::create($selectedYear, $fiscalYearStartMonth, 1, 0, 0, 0)->format('Y-m-d');
        $endDate = Carbon::create($selectedYear + 1, $fiscalYearStartMonth - 1, 31, 23, 59, 59)->format('Y-m-d');

        $totalLineCount = 19; // Standar 19 line mesin HINO

        // 1. Machine Breakdown Records (Result)
        $mbRecords = MachineBreakdown::query()
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('persen')
            ->whereRaw('LOWER(TRIM(status)) = ?', ['result'])
            ->where(function ($q) {
                $q->whereRaw('LOWER(TRIM(kpi)) LIKE ?', ['%machine breakdown%'])
                  ->orWhereRaw('LOWER(TRIM(kpi)) LIKE ?', ['%breakdown%']);
            })
            ->get();

        // 2. Line Stop Records (Result)
        $lsRecords = MachineBreakdown::query()
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('duration')
            ->whereRaw('LOWER(TRIM(status)) = ?', ['result'])
            ->where(function ($q) {
                $q->whereRaw('LOWER(TRIM(kpi)) LIKE ?', ['%linestop%'])
                  ->orWhereRaw('LOWER(TRIM(kpi)) LIKE ?', ['%line%stop%']);
            })
            ->get();

        // 3. MTTR Records (Result)
        $mttrRecords = MachineBreakdown::query()
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('duration')
            ->whereRaw('LOWER(TRIM(status)) = ?', ['result'])
            ->where(function ($q) {
                $q->whereRaw('LOWER(TRIM(kpi)) LIKE ?', ['%mttr%'])
                  ->orWhereRaw('LOWER(TRIM(sub_kp)) LIKE ?', ['%mttr%']);
            })
            ->get();

        // 4. MTBF Records (Result)
        $mtbfRecords = MachineBreakdown::query()
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('duration')
            ->whereRaw('LOWER(TRIM(status)) = ?', ['result'])
            ->where(function ($q) {
                $q->whereRaw('LOWER(TRIM(kpi)) LIKE ?', ['%mtbf%'])
                  ->orWhereRaw('LOWER(TRIM(sub_kp)) LIKE ?', ['%mtbf%']);
            })
            ->get();

        $fiscalLabels = [];
        $mbSeries = [];
        $lineStopSeries = [];
        $lineStopFreqSeries = [];
        $mttrSeries = [];
        $mtbfSeries = [];

        $mbTarget = 1.5;
        $lineStopTarget = 620;
        $mttrTarget = 12;
        $mtbfTarget = 4943;

        foreach ($fiscalMonthOrder as $monthNumber) {
            $labelYear = $monthNumber >= $fiscalYearStartMonth ? $selectedYear : $selectedYear + 1;
            $monthName = Carbon::create($labelYear, $monthNumber, 1)->translatedFormat('M y');
            $fiscalLabels[] = $monthName;

            // Machine Breakdown (%)
            $mbRows = $mbRecords->filter(function ($item) use ($monthNumber, $labelYear) {
                $d = $item->date ? Carbon::parse($item->date) : null;
                return $d && (int) $d->month === $monthNumber && (int) $d->year === $labelYear;
            });
            if ($mbRows->isNotEmpty()) {
                $sumPersen = $mbRows->sum('persen') * 100;
                $mbSeries[] = round($sumPersen / $totalLineCount, 2);
            } else {
                $mbSeries[] = null;
            }

            // Line Stop (Durasi & Frekuensi)
            $lsRows = $lsRecords->filter(function ($item) use ($monthNumber, $labelYear) {
                $d = $item->date ? Carbon::parse($item->date) : null;
                return $d && (int) $d->month === $monthNumber && (int) $d->year === $labelYear;
            })->unique(function ($item) {
                return strtolower(trim(str_replace(['"', "'"], '', $item->line ?? '')));
            });
            if ($lsRows->isNotEmpty()) {
                $lineStopSeries[] = (int) round((float) $lsRows->sum('duration'));
                $freqSum = (float) $lsRows->sum('frequency');
                $lineStopFreqSeries[] = ($freqSum == round($freqSum)) ? (int) $freqSum : round($freqSum, 2);
            } else {
                $lineStopSeries[] = null;
                $lineStopFreqSeries[] = null;
            }

            // MTTR
            $mttrRows = $mttrRecords->filter(function ($item) use ($monthNumber, $labelYear) {
                $d = $item->date ? Carbon::parse($item->date) : null;
                return $d && (int) $d->month === $monthNumber && (int) $d->year === $labelYear;
            })->sortByDesc(fn($i) => (float)($i->duration ?? 0))->unique(function ($item) {
                return strtolower(trim(str_replace(['"', "'"], '', $item->line ?? '')));
            });
            if ($mttrRows->isNotEmpty()) {
                $sumDuration = (float) $mttrRows->sum('duration');
                $mttrSeries[] = round($sumDuration / $totalLineCount, 2);
            } else {
                $mttrSeries[] = null;
            }

            // MTBF
            $mtbfRows = $mtbfRecords->filter(function ($item) use ($monthNumber, $labelYear) {
                $d = $item->date ? Carbon::parse($item->date) : null;
                return $d && (int) $d->month === $monthNumber && (int) $d->year === $labelYear;
            })->sortByDesc(fn($i) => (float)($i->duration ?? 0))->unique(function ($item) {
                return strtolower(trim(str_replace(['"', "'"], '', $item->line ?? '')));
            });
            if ($mtbfRows->isNotEmpty()) {
                $mtbfSeries[] = (int) round((float) $mtbfRows->avg('duration'));
            } else {
                $mtbfSeries[] = null;
            }
        }

        // Summary KPI terbaru (ambil bulan terakhir yang memiliki data)
        $latestIdx = null;
        for ($i = count($fiscalLabels) - 1; $i >= 0; $i--) {
            if ($mbSeries[$i] !== null || $lineStopSeries[$i] !== null || $mttrSeries[$i] !== null || $mtbfSeries[$i] !== null) {
                $latestIdx = $i;
                break;
            }
        }

        $latestSummary = [
            'month' => $latestIdx !== null ? $fiscalLabels[$latestIdx] : '-',
            'mb' => [
                'val' => $latestIdx !== null ? $mbSeries[$latestIdx] : null,
                'target' => $mbTarget,
                'achieved' => $latestIdx !== null && $mbSeries[$latestIdx] !== null ? $mbSeries[$latestIdx] <= $mbTarget : null,
            ],
            'line_stop' => [
                'val' => $latestIdx !== null ? $lineStopSeries[$latestIdx] : null,
                'freq' => $latestIdx !== null ? $lineStopFreqSeries[$latestIdx] : null,
                'target' => $lineStopTarget,
                'achieved' => $latestIdx !== null && $lineStopSeries[$latestIdx] !== null ? $lineStopSeries[$latestIdx] <= $lineStopTarget : null,
            ],
            'mttr' => [
                'val' => $latestIdx !== null ? $mttrSeries[$latestIdx] : null,
                'target' => $mttrTarget,
                'achieved' => $latestIdx !== null && $mttrSeries[$latestIdx] !== null ? $mttrSeries[$latestIdx] <= $mttrTarget : null,
            ],
            'mtbf' => [
                'val' => $latestIdx !== null ? $mtbfSeries[$latestIdx] : null,
                'target' => $mtbfTarget,
                'achieved' => $latestIdx !== null && $mtbfSeries[$latestIdx] !== null ? $mtbfSeries[$latestIdx] >= $mtbfTarget : null,
            ],
        ];

        // 3. Oper data ke view 'dashboards-analytics'
        return view('content.dashboard.dashboards-analytics', compact(
            'lineLabels',
            'mainMachineData',
            'equipmentData',
            'pmLabels',
            'pmData',
            'fiscalLabels',
            'mbSeries',
            'mbTarget',
            'lineStopSeries',
            'lineStopFreqSeries',
            'lineStopTarget',
            'mttrSeries',
            'mttrTarget',
            'mtbfSeries',
            'mtbfTarget',
            'selectedYear',
            'latestSummary'
        ));
    }
}

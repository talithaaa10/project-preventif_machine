<?php

namespace App\Http\Controllers;

use App\Imports\MachineBreakdownImport;
use App\Models\MachineBreakdown;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class MachineBreakdownController extends Controller
{
    /**
     * Halaman 1: Machine Breakdown
     * Ketentuan:
     * - Status: Result
     * - Grafik Batang-Line
     * - Nilai: Persen dijumlah per bulan (%)
     * - Target: 1.5%
     */
    /**
     * Halaman 1: Machine Breakdown
     * Ketentuan:
     * - Status: Result
     * - Grafik Batang-Line
     * - Nilai: Rata-rata Persen per bulan (Average dari 19 Line Mesin)
     * - Target: 1.5%
     */
    public function index(Request $request)
    {
        $this->cleanDuplicateRecords();

        $fiscalYearStartMonth = 4;
        $defaultFiscalYear = now()->month >= $fiscalYearStartMonth ? now()->year : now()->year - 1;
        $selectedYear = (int) ($request->get('year', $defaultFiscalYear));

        $fiscalMonthOrder = [4, 5, 6, 7, 8, 9, 10, 11, 12, 1, 2, 3];

        $startDate = Carbon::create($selectedYear, $fiscalYearStartMonth, 1, 0, 0, 0)->format('Y-m-d');
        $endDate = Carbon::create($selectedYear + 1, $fiscalYearStartMonth - 1, 31, 23, 59, 59)->format('Y-m-d');

        // Query Persen khusus status 'Result' dan KPI Machine Breakdown
        $allRecords = MachineBreakdown::query()
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('persen')
            ->whereRaw('LOWER(TRIM(status)) = ?', ['result'])
            ->where(function ($q) {
                $q->whereRaw('LOWER(TRIM(kpi)) LIKE ?', ['%machine breakdown%'])
                  ->orWhereRaw('LOWER(TRIM(kpi)) LIKE ?', ['%breakdown%']);
            })
            ->get();

        // Urutan standar 19 line mesin sesuai Excel HINO
        $standardLineOrder = [
            'CONROD', 'CAMSHAFT', 'CRANKSHAFT', 'CYL HEAD', 'CYL BLOCK',
            'REAR AXLE HOUSING 13', 'END YOKE', 'INNER SHAFT', 'CENTER BEARING',
            'NYLON COATED', 'FLANGE', 'SLIDING', 'COUPLING', 'SUB ASSY Y230',
            'Y230 INTER AXLE/CENTER BEARING', 'REAR AXLE HOUSING 17', 'E/G LINE',
            'AXLE ASSY', 'TRANSMISI'
        ];

        $dbLines = $allRecords->pluck('line')->unique()->filter()->values()->toArray();
        $distinctLines = !empty($dbLines) ? array_values(array_unique(array_merge($standardLineOrder, $dbLines))) : $standardLineOrder;
        $totalLineCount = 19; // Standar pembagi 19 line mesin HINO seperti di Excel

        $labels = [];
        $breakdownSeries = [];
        $targetSeries = [];
        $targetConstant = 1.5; // Target 1,5%
        $tableData = [];

        $matrixColumns = [];
        $monthlyAverages = [];

        foreach ($fiscalMonthOrder as $monthNumber) {
            $labelYear = $monthNumber >= $fiscalYearStartMonth ? $selectedYear : $selectedYear + 1;
            $monthName = Carbon::create($labelYear, $monthNumber, 1)->translatedFormat('M Y');
            $shortMonth = Carbon::create($labelYear, $monthNumber, 1)->format('M-y');
            $labels[] = $monthName;
            $matrixColumns[] = [
                'monthNumber' => $monthNumber,
                'year' => $labelYear,
                'name' => $monthName,
                'short' => $shortMonth
            ];

            // Ambil data untuk bulan fiskal ini
            $monthRows = $allRecords->filter(function ($item) use ($monthNumber, $labelYear) {
                $d = $item->date ? Carbon::parse($item->date) : null;
                return $d && (int) $d->month === $monthNumber && (int) $d->year === $labelYear;
            });

            if ($monthRows->isNotEmpty()) {
                // Di Excel: AVERAGE = SUM(persen) seluruh 19 line dibagi 19
                // Karena persen di DB tersimpan dalam desimal (misal 0.0090 = 0.90%), maka dikali 100
                $sumPersen = $monthRows->sum('persen') * 100;
                $val = round($sumPersen / $totalLineCount, 2);
                $count = $monthRows->count();
            } else {
                $val = null;
                $count = 0;
            }

            $breakdownSeries[] = $val;
            $targetSeries[] = $targetConstant;
            $monthlyAverages[$monthNumber] = $val;

            $tableData[] = [
                'month' => $monthName,
                'count' => $count,
                'value' => $val,
                'target' => $targetConstant,
                'diff' => $val !== null ? round($val - $targetConstant, 2) : null,
            ];
        }

        // Susun baris matrix untuk setiap line mesin (persis tampilan Excel)
        $matrixRows = [];
        foreach ($distinctLines as $lineName) {
            $monthValues = [];
            $sumLinePct = 0;

            foreach ($fiscalMonthOrder as $monthNumber) {
                $labelYear = $monthNumber >= $fiscalYearStartMonth ? $selectedYear : $selectedYear + 1;
                $matched = $allRecords->filter(function ($item) use ($lineName, $monthNumber, $labelYear) {
                    $d = $item->date ? Carbon::parse($item->date) : null;
                    $cleanDbLine = trim(str_replace(['"', "'"], '', $item->line ?? ''));
                    $cleanTargetLine = trim(str_replace(['"', "'"], '', $lineName));
                    return strcasecmp($cleanDbLine, $cleanTargetLine) === 0
                        && $d && (int) $d->month === $monthNumber && (int) $d->year === $labelYear;
                })->sortByDesc(fn($i) => (float)($i->persen ?? 0))->first();

                if ($matched && $matched->persen !== null) {
                    $pct = round((float) $matched->persen * 100, 2);
                    $monthValues[$monthNumber] = $pct;
                    $sumLinePct += $pct;
                } else {
                    $monthValues[$monthNumber] = 0.0;
                }
            }

            // FY Average per line = sum dibagi 12 bulan fiskal
            $fyAverage = round($sumLinePct / 12, 2);

            $matrixRows[] = [
                'line' => $lineName,
                'months' => $monthValues,
                'fyAverage' => $fyAverage
            ];
        }

        // Hitung FY Average keseluruhan = rata-rata 12 bulan (persis 0.33% di Excel)
        $totalSumMonthly = array_sum(array_map(fn($v) => $v ?? 0.0, $monthlyAverages));
        $fyTotalAverage = round($totalSumMonthly / 12, 2);

        // Rata-rata bulan berjalan (hanya bulan yang ada datanya)
        $validAverages = array_filter($monthlyAverages, fn($v) => $v !== null);
        $runningAverage = count($validAverages) > 0 ? round(array_sum($validAverages) / count($validAverages), 2) : 0;

        $fiscalYearLabel = $selectedYear . '/' . ($selectedYear + 1);
        $availableYears = $this->getAvailableYears($selectedYear);

        return view('content.machine-breakdown.index', compact(
            'labels',
            'breakdownSeries',
            'targetSeries',
            'targetConstant',
            'tableData',
            'runningAverage',
            'fyTotalAverage',
            'fiscalYearLabel',
            'selectedYear',
            'availableYears',
            'matrixColumns',
            'matrixRows',
            'monthlyAverages'
        ));
    }

    /**
     * Halaman 2: MTTR
     * Ketentuan:
     * - Status: Result
     * - Grafik Line with Marker vs Target 12
     * - Nilai: Rata-rata durasi per bulan dari 19 Line Mesin (SUM / 19)
     * - Target: 12
     */
    public function mttr(Request $request)
    {
        $this->cleanDuplicateRecords();

        $fiscalYearStartMonth = 4;
        $defaultFiscalYear = now()->month >= $fiscalYearStartMonth ? now()->year : now()->year - 1;
        $selectedYear = (int) ($request->get('year', $defaultFiscalYear));

        $fiscalMonthOrder = [4, 5, 6, 7, 8, 9, 10, 11, 12, 1, 2, 3];

        $startDate = Carbon::create($selectedYear, $fiscalYearStartMonth, 1, 0, 0, 0)->format('Y-m-d');
        $endDate = Carbon::create($selectedYear + 1, $fiscalYearStartMonth - 1, 31, 23, 59, 59)->format('Y-m-d');

        // Query seluruh data MTTR khusus status 'Result'
        $allRecords = MachineBreakdown::query()
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('duration')
            ->whereRaw('LOWER(TRIM(status)) = ?', ['result'])
            ->where(function ($q) {
                $q->whereRaw('LOWER(TRIM(kpi)) LIKE ?', ['%mttr%'])
                  ->orWhereRaw('LOWER(TRIM(sub_kp)) LIKE ?', ['%mttr%']);
            })
            ->get();

        $standardLineOrder = [
            'CONROD', 'CAMSHAFT', 'CRANKSHAFT', 'CYL HEAD', 'CYL BLOCK',
            'REAR AXLE HOUSING 13', 'END YOKE', 'INNER SHAFT', 'CENTER BEARING',
            'NYLON COATED', 'FLANGE', 'SLIDING', 'COUPLING', 'SUB ASSY Y230',
            'Y230 INTER AXLE/CENTER BEARING', 'REAR AXLE HOUSING 17', 'E/G LINE',
            'AXLE ASSY', 'TRANSMISI'
        ];

        $dbLines = $allRecords->pluck('line')->unique()->filter()->values()->toArray();
        $distinctLines = !empty($dbLines) ? array_values(array_unique(array_merge($standardLineOrder, $dbLines))) : $standardLineOrder;
        $totalLineCount = 19; // Standar pembagi 19 line mesin HINO seperti di Excel

        $labels = [];
        $mttrSeries = [];
        $targetSeries = [];
        $targetConstant = 12; // Ketentuan Target 12

        $tableData = [];
        $matrixColumns = [];
        $monthlyAverages = [];

        foreach ($fiscalMonthOrder as $monthNumber) {
            $labelYear = $monthNumber >= $fiscalYearStartMonth ? $selectedYear : $selectedYear + 1;
            $monthName = Carbon::create($labelYear, $monthNumber, 1)->translatedFormat('M Y');
            $shortMonth = Carbon::create($labelYear, $monthNumber, 1)->format('M-y');
            $labels[] = $monthName;
            $matrixColumns[] = [
                'monthNumber' => $monthNumber,
                'year' => $labelYear,
                'name' => $monthName,
                'short' => $shortMonth
            ];

            // Filter records untuk bulan fiskal ini & urutkan duration DESC agar baris bernilai valid terpilih
            $monthRows = $allRecords->filter(function ($item) use ($monthNumber, $labelYear) {
                $d = $item->date ? Carbon::parse($item->date) : null;
                return $d && (int) $d->month === $monthNumber && (int) $d->year === $labelYear;
            })->sortByDesc(fn($i) => (float)($i->duration ?? 0))->unique(function ($item) {
                return strtolower(trim(str_replace(['"', "'"], '', $item->line ?? '')));
            });

            if ($monthRows->isNotEmpty()) {
                // Di Excel: AVERAGE = SUM(duration) dari seluruh 19 line dibagi 19
                $sumDuration = (float) $monthRows->sum('duration');
                $val = round($sumDuration / $totalLineCount, 2);
                $count = $monthRows->count();
            } else {
                $val = null;
                $count = 0;
            }

            $mttrSeries[] = $val;
            $targetSeries[] = $targetConstant;
            $monthlyAverages[$monthNumber] = $val;

            $tableData[] = [
                'month' => $monthName,
                'count' => $count,
                'value' => $val,
                'target' => $targetConstant,
                'diff' => $val !== null ? round($val - $targetConstant, 2) : null,
            ];
        }

        // Susun baris matrix untuk setiap line mesin (persis Excel screenshot)
        $matrixRows = [];
        foreach ($distinctLines as $lineName) {
            $monthValues = [];

            foreach ($fiscalMonthOrder as $monthNumber) {
                $labelYear = $monthNumber >= $fiscalYearStartMonth ? $selectedYear : $selectedYear + 1;
                $matched = $allRecords->filter(function ($item) use ($lineName, $monthNumber, $labelYear) {
                    $d = $item->date ? Carbon::parse($item->date) : null;
                    $cleanDbLine = trim(str_replace(['"', "'"], '', $item->line ?? ''));
                    $cleanTargetLine = trim(str_replace(['"', "'"], '', $lineName));
                    return strcasecmp($cleanDbLine, $cleanTargetLine) === 0
                        && $d && (int) $d->month === $monthNumber && (int) $d->year === $labelYear;
                })->sortByDesc(fn($i) => (float)($i->duration ?? 0))->first();

                if ($matched && $matched->duration !== null && (float)$matched->duration > 0) {
                    $dur = round((float) $matched->duration, 2);
                    $monthValues[$monthNumber] = $dur;
                } else {
                    $monthValues[$monthNumber] = 0.0;
                }
            }

            $matrixRows[] = [
                'line' => $lineName,
                'months' => $monthValues
            ];
        }

        $activeValues = collect($mttrSeries)->filter(fn($v) => $v !== null);
        $avgMttr = $activeValues->count() > 0 ? round((float) $activeValues->avg(), 2) : 0;

        $fiscalYearLabel = $selectedYear . '/' . ($selectedYear + 1);
        $availableYears = $this->getAvailableYears($selectedYear);

        return view('content.machine-breakdown.mttr', compact(
            'labels',
            'mttrSeries',
            'targetSeries',
            'targetConstant',
            'tableData',
            'avgMttr',
            'fiscalYearLabel',
            'selectedYear',
            'availableYears',
            'matrixColumns',
            'matrixRows',
            'monthlyAverages'
        ));
    }

    /**
     * Halaman 3: Line Stop
     * Ketentuan:
     * - Grafik Batang-Line
     * - Status: Result
     * - Nilai: Durasi dijumlah per bulan (angka bulat murni tanpa satuan)
     * - Target: 620
     */
    public function lineStop(Request $request)
    {
        $this->cleanDuplicateRecords();

        $fiscalYearStartMonth = 4;
        $defaultFiscalYear = now()->month >= $fiscalYearStartMonth ? now()->year : now()->year - 1;
        $selectedYear = (int) ($request->get('year', $defaultFiscalYear));

        $fiscalMonthOrder = [4, 5, 6, 7, 8, 9, 10, 11, 12, 1, 2, 3];

        $startDate = Carbon::create($selectedYear, $fiscalYearStartMonth, 1, 0, 0, 0)->format('Y-m-d');
        $endDate = Carbon::create($selectedYear + 1, $fiscalYearStartMonth - 1, 31, 23, 59, 59)->format('Y-m-d');

        // Query seluruh data Linestop khusus status 'Result'
        $allRecords = MachineBreakdown::query()
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('duration')
            ->whereRaw('LOWER(TRIM(status)) = ?', ['result'])
            ->where(function ($q) {
                $q->whereRaw('LOWER(TRIM(kpi)) LIKE ?', ['%linestop%'])
                  ->orWhereRaw('LOWER(TRIM(kpi)) LIKE ?', ['%line%stop%']);
            })
            ->get();

        // Urutan standar 19 line mesin sesuai Excel HINO
        $standardLineOrder = [
            'CONROD', 'CAMSHAFT', 'CRANKSHAFT', 'CYL HEAD', 'CYL BLOCK',
            'REAR AXLE HOUSING 13', 'END YOKE', 'INNER SHAFT', 'CENTER BEARING',
            'NYLON COATED', 'FLANGE', 'SLIDING', 'COUPLING', 'SUB ASSY Y230',
            'Y230 INTER AXLE/CENTER BEARING', 'REAR AXLE HOUSING 17', 'E/G LINE',
            'AXLE ASSY', 'TRANSMISI'
        ];

        $dbLines = $allRecords->pluck('line')->unique()->filter()->values()->toArray();
        $distinctLines = !empty($dbLines) ? array_values(array_unique(array_merge($standardLineOrder, $dbLines))) : $standardLineOrder;

        $labels = [];
        $lineStopSeries = [];
        $lineStopFreqSeries = [];
        $targetSeries = [];
        $targetConstant = 620; // Target 620

        $tableData = [];
        $matrixColumns = [];
        $monthlyTotals = [];
        $monthlyFreqTotals = [];

        foreach ($fiscalMonthOrder as $monthNumber) {
            $labelYear = $monthNumber >= $fiscalYearStartMonth ? $selectedYear : $selectedYear + 1;
            $monthName = Carbon::create($labelYear, $monthNumber, 1)->translatedFormat('M Y');
            $shortMonth = Carbon::create($labelYear, $monthNumber, 1)->format('M-y');
            $labels[] = $monthName;
            $matrixColumns[] = [
                'monthNumber' => $monthNumber,
                'year' => $labelYear,
                'name' => $monthName,
                'short' => $shortMonth
            ];

            // Filter records untuk bulan fiskal ini & pastikan tiap line mesin hanya dihitung 1x (anti-duplikasi)
            $monthRows = $allRecords->filter(function ($item) use ($monthNumber, $labelYear) {
                $d = $item->date ? Carbon::parse($item->date) : null;
                return $d && (int) $d->month === $monthNumber && (int) $d->year === $labelYear;
            })->unique(function ($item) {
                return strtolower(trim(str_replace(['"', "'"], '', $item->line ?? '')));
            });

            if ($monthRows->isNotEmpty()) {
                // Di Excel: TOTAL adalah penjumlahan bulat seluruh line
                $totalSum = (int) round($monthRows->sum('duration'));
                $val = $totalSum;
                $count = $monthRows->count();
                $freqSum = (float) $monthRows->sum('frequency');
                $freqVal = ($freqSum == round($freqSum)) ? (int) $freqSum : round($freqSum, 2);
            } else {
                $val = null;
                $count = 0;
                $freqVal = null;
            }

            $lineStopSeries[] = $val;
            $lineStopFreqSeries[] = $freqVal;
            $targetSeries[] = $targetConstant;
            $monthlyTotals[$monthNumber] = $val;
            $monthlyFreqTotals[$monthNumber] = $freqVal;

            $tableData[] = [
                'month' => $monthName,
                'count' => $count,
                'value' => $val,
                'freq' => $freqVal,
                'target' => $targetConstant,
                'diff' => $val !== null ? ($val - $targetConstant) : null,
            ];
        }

        // Susun baris matrix untuk setiap line mesin (persis Excel screenshot)
        $matrixRows = [];
        foreach ($distinctLines as $lineName) {
            $monthValues = [];
            $monthFreqValues = [];
            $sumLine = 0;
            $sumFreqLine = 0;

            foreach ($fiscalMonthOrder as $monthNumber) {
                $labelYear = $monthNumber >= $fiscalYearStartMonth ? $selectedYear : $selectedYear + 1;
                $matched = $allRecords->filter(function ($item) use ($lineName, $monthNumber, $labelYear) {
                    $d = $item->date ? Carbon::parse($item->date) : null;
                    $cleanDbLine = trim(str_replace(['"', "'"], '', $item->line ?? ''));
                    $cleanTargetLine = trim(str_replace(['"', "'"], '', $lineName));
                    return strcasecmp($cleanDbLine, $cleanTargetLine) === 0
                        && $d && (int) $d->month === $monthNumber && (int) $d->year === $labelYear;
                })->sortByDesc(fn($i) => (float)($i->duration ?? 0))->first();

                if ($matched && $matched->duration !== null) {
                    $dur = (int) round((float) $matched->duration);
                    $monthValues[$monthNumber] = $dur;
                    $sumLine += $dur;
                } else {
                    $monthValues[$monthNumber] = 0;
                }

                if ($matched && $matched->frequency !== null) {
                    $freq = (int) round((float) $matched->frequency);
                    $monthFreqValues[$monthNumber] = $freq;
                    $sumFreqLine += $freq;
                } else {
                    $monthFreqValues[$monthNumber] = 0;
                }
            }

            $matrixRows[] = [
                'line' => $lineName,
                'months' => $monthValues,
                'freqMonths' => $monthFreqValues,
                'total' => $sumLine,
                'totalFreq' => $sumFreqLine
            ];
        }

        $activeValues = collect($lineStopSeries)->filter(fn($v) => $v !== null);
        $grandTotal = $activeValues->sum();
        $avgDowntime = $activeValues->count() > 0 ? (int) round($activeValues->avg()) : 0;

        $fiscalYearLabel = $selectedYear . '/' . ($selectedYear + 1);
        $availableYears = $this->getAvailableYears($selectedYear);

        return view('content.machine-breakdown.line-stop', compact(
            'labels',
            'lineStopSeries',
            'lineStopFreqSeries',
            'targetSeries',
            'targetConstant',
            'tableData',
            'grandTotal',
            'avgDowntime',
            'fiscalYearLabel',
            'selectedYear',
            'availableYears',
            'matrixColumns',
            'matrixRows',
            'monthlyTotals',
            'monthlyFreqTotals'
        ));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        Excel::import(new MachineBreakdownImport, $request->file('file'));

        return redirect()->back()->with('success', 'Data Machine Breakdown berhasil diimport!');
    }

    public function mbtf(Request $request)
    {
        $this->cleanDuplicateRecords();

        $fiscalYearStartMonth = 4;
        $defaultFiscalYear = now()->month >= $fiscalYearStartMonth ? now()->year : now()->year - 1;
        $selectedYear = (int) ($request->get('year', $defaultFiscalYear));

        $fiscalMonthOrder = [4, 5, 6, 7, 8, 9, 10, 11, 12, 1, 2, 3];

        $startDate = Carbon::create($selectedYear, $fiscalYearStartMonth, 1, 0, 0, 0)->format('Y-m-d');
        $endDate = Carbon::create($selectedYear + 1, $fiscalYearStartMonth - 1, 31, 23, 59, 59)->format('Y-m-d');

        // Query seluruh data MTBF khusus status 'Result'
        $allRecords = MachineBreakdown::query()
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('duration')
            ->whereRaw('LOWER(TRIM(status)) = ?', ['result'])
            ->where(function ($q) {
                $q->whereRaw('LOWER(TRIM(kpi)) LIKE ?', ['%mtbf%'])
                  ->orWhereRaw('LOWER(TRIM(sub_kp)) LIKE ?', ['%mtbf%']);
            })
            ->get();

        // Urutan standar 19 line mesin sesuai standar HINO
        $standardLineOrder = [
            'CONROD', 'CAMSHAFT', 'CRANKSHAFT', 'CYL HEAD', 'CYL BLOCK',
            'REAR AXLE HOUSING 13', 'END YOKE', 'INNER SHAFT', 'CENTER BEARING',
            'NYLON COATED', 'FLANGE', 'SLIDING', 'COUPLING', 'SUB ASSY Y230',
            'Y230 INTER AXLE/CENTER BEARING', 'REAR AXLE HOUSING 17', 'E/G LINE',
            'AXLE ASSY', 'TRANSMISI'
        ];

        $dbLines = $allRecords->pluck('line')->unique()->filter()->values()->toArray();
        $distinctLines = !empty($dbLines) ? array_values(array_unique(array_merge($standardLineOrder, $dbLines))) : $standardLineOrder;

        $labels = [];
        $mtbfSeries = [];
        $targetSeries = [];
        $targetConstant = 4943; // Ketentuan Target 4943

        $tableData = [];
        $matrixColumns = [];
        $monthlyAverages = [];

        foreach ($fiscalMonthOrder as $monthNumber) {
            $labelYear = $monthNumber >= $fiscalYearStartMonth ? $selectedYear : $selectedYear + 1;
            $monthName = Carbon::create($labelYear, $monthNumber, 1)->translatedFormat('M Y');
            $shortMonth = Carbon::create($labelYear, $monthNumber, 1)->format('M-y');
            $labels[] = $monthName;
            $matrixColumns[] = [
                'monthNumber' => $monthNumber,
                'year' => $labelYear,
                'name' => $monthName,
                'short' => $shortMonth
            ];

            // Filter records untuk bulan fiskal ini & urutkan duration DESC
            $monthRows = $allRecords->filter(function ($item) use ($monthNumber, $labelYear) {
                $d = $item->date ? Carbon::parse($item->date) : null;
                return $d && (int) $d->month === $monthNumber && (int) $d->year === $labelYear;
            })->sortByDesc(fn($i) => (float)($i->duration ?? 0))->unique(function ($item) {
                return strtolower(trim(str_replace(['"', "'"], '', $item->line ?? '')));
            });

            if ($monthRows->isNotEmpty()) {
                $val = (int) round((float) $monthRows->avg('duration'));
                $count = $monthRows->count();
            } else {
                $val = null;
                $count = 0;
            }

            $mtbfSeries[] = $val;
            $targetSeries[] = $targetConstant;
            $monthlyAverages[$monthNumber] = $val;

            $tableData[] = [
                'month' => $monthName,
                'count' => $count,
                'value' => $val,
                'target' => $targetConstant,
                'diff' => $val !== null ? $val - $targetConstant : null,
            ];
        }

        // Susun baris matrix untuk setiap line mesin
        $matrixRows = [];
        foreach ($distinctLines as $lineName) {
            $monthValues = [];

            foreach ($fiscalMonthOrder as $monthNumber) {
                $labelYear = $monthNumber >= $fiscalYearStartMonth ? $selectedYear : $selectedYear + 1;
                $matched = $allRecords->filter(function ($item) use ($lineName, $monthNumber, $labelYear) {
                    $d = $item->date ? Carbon::parse($item->date) : null;
                    $cleanDbLine = trim(str_replace(['"', "'"], '', $item->line ?? ''));
                    $cleanTargetLine = trim(str_replace(['"', "'"], '', $lineName));
                    return strcasecmp($cleanDbLine, $cleanTargetLine) === 0
                        && $d && (int) $d->month === $monthNumber && (int) $d->year === $labelYear;
                })->sortByDesc(fn($i) => (float)($i->duration ?? 0))->first();

                if ($matched && $matched->duration !== null) {
                    $dur = (int) round((float) $matched->duration);
                    $monthValues[$monthNumber] = $dur;
                } else {
                    $monthValues[$monthNumber] = 0;
                }
            }

            $matrixRows[] = [
                'line' => $lineName,
                'months' => $monthValues
            ];
        }

        $activeValues = collect($mtbfSeries)->filter(fn($v) => $v !== null);
        $avgMtbf = $activeValues->count() > 0 ? (int) round((float) $activeValues->avg()) : 0;

        $fiscalYearLabel = $selectedYear . '/' . ($selectedYear + 1);
        $availableYears = $this->getAvailableYears($selectedYear);

        return view('content.machine-breakdown.mbtf', compact(
            'labels',
            'mtbfSeries',
            'targetSeries',
            'avgMtbf',
            'fiscalYearLabel',
            'selectedYear',
            'availableYears',
            'targetConstant',
            'tableData',
            'matrixColumns',
            'matrixRows',
            'monthlyAverages'
        ));
    }

    protected function getAvailableYears(int $selectedYear): array
    {
        $availableYears = MachineBreakdown::query()
            ->whereNotNull('date')
            ->selectRaw('DISTINCT YEAR(date) as yr')
            ->pluck('yr')
            ->map(fn($y) => (int)$y)
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [$selectedYear];
        }

        return $availableYears;
    }

    protected function cleanDuplicateRecords(): void
    {
        try {
            DB::statement("
                DELETE FROM machine_breakdowns 
                WHERE duration IS NULL AND frequency IS NULL AND persen IS NULL
            ");

            DB::statement("
                DELETE t1 FROM machine_breakdowns t1
                INNER JOIN machine_breakdowns t2 
                WHERE t1.id > t2.id 
                  AND t1.date = t2.date 
                  AND TRIM(LOWER(t1.line)) = TRIM(LOWER(t2.line)) 
                  AND TRIM(LOWER(t1.kpi)) = TRIM(LOWER(t2.kpi)) 
                  AND TRIM(LOWER(t1.status)) = TRIM(LOWER(t2.status))
            ");
        } catch (\Throwable $e) {
            // fallback gracefully
        }
    }

    /**
     * Halaman Input Data & Management Machine Breakdown
     */
    public function inputIndex(Request $request)
    {
        $query = MachineBreakdown::query();

        if ($request->filled('line') && $request->line !== 'ALL') {
            $query->where('line', $request->line);
        }

        if ($request->filled('kpi') && $request->kpi !== 'ALL') {
            $query->where('kpi', $request->kpi);
        }

        if ($request->filled('status') && $request->status !== 'ALL') {
            $query->where('status', $request->status);
        }

        if ($request->filled('year') && $request->year !== 'ALL') {
            $query->whereYear('date', $request->year);
        }

        $records = $query->orderByDesc('date')->orderBy('line')->paginate(25)->withQueryString();

        $availableLines = [
            'CONROD', 'CAMSHAFT', 'CRANKSHAFT', 'CYL HEAD', 'CYL BLOCK',
            'REAR AXLE HOUSING 13', 'END YOKE', 'INNER SHAFT', 'CENTER BEARING',
            'NYLON COATED', 'FLANGE', 'SLIDING', 'COUPLING', 'SUB ASSY Y230',
            'Y230 INTER AXLE/CENTER BEARING', 'REAR AXLE HOUSING 17', 'E/G LINE',
            'AXLE ASSY', 'TRANSMISI'
        ];

        // Also merge any existing lines from DB
        $dbLines = MachineBreakdown::whereNotNull('line')->distinct()->pluck('line')->toArray();
        $allLines = array_values(array_unique(array_merge($availableLines, $dbLines)));
        sort($allLines);

        $kpis = ['Machine Breakdown', 'Linestop', 'MTTR', 'MTBF', 'Working Hours'];
        $dbKpis = MachineBreakdown::whereNotNull('kpi')->distinct()->pluck('kpi')->toArray();
        $allKpis = array_values(array_unique(array_merge($kpis, $dbKpis)));

        $statuses = ['Result', 'Target'];
        $years = MachineBreakdown::whereNotNull('date')->selectRaw('DISTINCT YEAR(date) as yr')->pluck('yr')->toArray();
        if (empty($years)) {
            $years = [now()->year];
        }
        rsort($years);

        return view('content.input-data.machine-breakdown', compact(
            'records',
            'allLines',
            'allKpis',
            'statuses',
            'years'
        ));
    }

    /**
     * Store manual single record
     */
    public function inputStore(Request $request)
    {
        $validated = $request->validate([
            'cat' => 'nullable|string|max:100',
            'date' => 'required|date',
            'status' => 'required|string|max:50',
            'kpi' => 'required|string|max:100',
            'sub_kp' => 'nullable|string|max:100',
            'line' => 'required|string|max:100',
            'target' => 'nullable|numeric',
            'duration' => 'nullable|numeric',
            'frequency' => 'nullable|numeric',
            'persen' => 'nullable|numeric',
        ]);

        MachineBreakdown::create($validated);

        return redirect()->route('input.machine-breakdown')->with('success', 'Data Machine Breakdown berhasil ditambahkan!');
    }

    /**
     * Delete a single record
     */
    public function inputDestroy($id)
    {
        $record = MachineBreakdown::findOrFail($id);
        $record->delete();

        return redirect()->route('input.machine-breakdown')->with('success', 'Data Machine Breakdown berhasil dihapus!');
    }
}

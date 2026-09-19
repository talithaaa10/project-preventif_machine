<?php

namespace App\Http\Controllers;

use App\Imports\BatteryImport;
use App\Models\Battery;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class BatteryController extends Controller
{
    public function index(Request $request)
    {
        $allMasterBatteries = Battery::select('id', 'area', 'line', 'machine_no', 'machine_name', 'op_number', 'equipment_type', 'battery_model', 'std_volt')
            ->whereNotNull('area')
            ->where('area', '!=', '')
            ->orderBy('area')
            ->orderBy('line')
            ->orderBy('machine_no')
            ->get();

        $cascadeTree = [];
        foreach ($allMasterBatteries as $b) {
            $area = trim((string)$b->area);
            $line = trim((string)($b->line ?: '-'));
            $op = trim((string)($b->machine_no ?: ($b->op_number ?: '-')));
            $mc = trim((string)($b->machine_name ?: $op));
            $eq = trim((string)($b->equipment_type ?: '-'));
            $model = trim((string)($b->battery_model ?: '-'));
            $volt = trim((string)($b->std_volt ?: ''));

            if ($area === '' || $area === '-') continue;

            if (!isset($cascadeTree[$area])) $cascadeTree[$area] = [];
            if (!isset($cascadeTree[$area][$line])) $cascadeTree[$area][$line] = [];
            if (!isset($cascadeTree[$area][$line][$op])) {
                $cascadeTree[$area][$line][$op] = [
                    'machines' => [],
                    'equipments' => [],
                ];
            }

            if (!in_array($mc, $cascadeTree[$area][$line][$op]['machines'])) {
                $cascadeTree[$area][$line][$op]['machines'][] = $mc;
            }

            if (!isset($cascadeTree[$area][$line][$op]['equipments'][$eq])) {
                $cascadeTree[$area][$line][$op]['equipments'][$eq] = [];
            }

            $exists = false;
            foreach ($cascadeTree[$area][$line][$op]['equipments'][$eq] as $mItem) {
                if ($mItem['model'] === $model) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $cascadeTree[$area][$line][$op]['equipments'][$eq][] = [
                    'model' => $model,
                    'std_volt' => $volt,
                    'battery_id' => $b->id,
                    'machine_no' => $mc,
                ];
            }
        }

        $recentIds = session('recent_input_battery_ids', []);
        $showAll = $request->boolean('show_all');

        $inputQuery = \App\Models\InputBattery::with('battery')->latest('id');
        if (!$showAll) {
            // Tampilkan seluruh data input manual pengguna (bukan riwayat import sheet 2 mentah)
            $inputQuery->where(function ($q) use ($recentIds) {
                $q->whereNotNull('voltage_before')
                    ->orWhereNotNull('voltage_after')
                    ->orWhereNotNull('prev_status_aktif');
                if (!empty($recentIds)) {
                    $q->orWhereIn('id', $recentIds);
                }
            });
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $inputQuery->where(function ($q) use ($s) {
                $q->where('line', 'like', "%{$s}%")
                    ->orWhere('op_number', 'like', "%{$s}%")
                    ->orWhere('machine_no', 'like', "%{$s}%")
                    ->orWhere('equipment_type', 'like', "%{$s}%")
                    ->orWhere('battery_model', 'like', "%{$s}%")
                    ->orWhere('area', 'like', "%{$s}%")
                    ->orWhere('exchange_type', 'like', "%{$s}%");
            });
        }
        $inputBatteries = $inputQuery->paginate(20)->withQueryString();
        $totalInputCount = $inputBatteries->total();
        $totalMasterCount = Battery::count();
        $availableAreas = Battery::whereNotNull('area')->where('area', '!=', '')->distinct()->orderBy('area')->pluck('area');

        return view('content.input-data.battery', compact('cascadeTree', 'inputBatteries', 'totalInputCount', 'totalMasterCount', 'showAll', 'availableAreas'));
    }

    public function getNextExchangeType(Request $request)
    {
        $line = trim((string)$request->get('line'));
        $op = trim((string)$request->get('op'));
        $machineNo = trim((string)$request->get('machine_no'));
        $equipmentType = trim((string)$request->get('equipment_type'));
        $batteryModel = trim((string)$request->get('battery_model'));
        $area = trim((string)$request->get('area'));

        $query = \App\Models\InputBattery::query();

        if ($line !== '') {
            $query->where('line', $line);
        }
        if ($op !== '') {
            $query->where(function ($q) use ($op) {
                $q->where('op_number', $op)
                    ->orWhere('machine_no', $op);
            });
        }
        if ($equipmentType !== '' && $equipmentType !== '-') {
            $query->where('equipment_type', $equipmentType);
        }
        if ($batteryModel !== '' && $batteryModel !== '-') {
            $query->where('battery_model', $batteryModel);
        }

        $records = $query->orderBy('id', 'desc')->get(['id', 'exchange_type', 'last_day', 'voltage_before', 'voltage_after', 'standart_volt', 'status']);

        $maxNum = 0;
        $lastExchangeRaw = null;
        foreach ($records as $r) {
            $ex = (string)$r->exchange_type;
            if (preg_match('/(\d+)/', $ex, $matches)) {
                $num = (int)$matches[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                    $lastExchangeRaw = $ex;
                }
            } elseif (!$lastExchangeRaw && $ex !== '') {
                $lastExchangeRaw = $ex;
            }
        }

        $nextNum = $maxNum > 0 ? ($maxNum + 1) : 1;
        $nextExchangeType = "Replace on exchange {$nextNum}";

        // Cari master battery record untuk std_volt dan status_aktif strictly dari master asset (batteries)
        $masterQuery = Battery::where('line', $line)
            ->where(function ($q) use ($op) {
                $q->where('machine_no', $op)->orWhere('op_number', $op);
            });
        if ($machineNo !== '' && $machineNo !== '-') {
            $masterQuery->where(function ($q) use ($machineNo) {
                $q->where('machine_name', $machineNo)->orWhere('machine_no', $machineNo);
            });
        }
        if ($area !== '' && $area !== '-') {
            $masterQuery->where('area', $area);
        }
        if ($equipmentType !== '' && $equipmentType !== '-') {
            $masterQuery->where('equipment_type', $equipmentType);
        }
        if ($batteryModel !== '' && $batteryModel !== '-') {
            $masterQuery->where('battery_model', $batteryModel);
        }
        $master = $masterQuery->first();

        if (!$master) {
            $masterFallbackQuery = Battery::where('line', $line)
                ->where(function ($q) use ($op) {
                    $q->where('machine_no', $op)->orWhere('op_number', $op);
                });
            if ($area !== '' && $area !== '-') {
                $masterFallbackQuery->where('area', $area);
            }
            if ($equipmentType !== '' && $equipmentType !== '-') {
                $masterFallbackQuery->where('equipment_type', $equipmentType);
            }
            if ($batteryModel !== '' && $batteryModel !== '-') {
                $masterFallbackQuery->where('battery_model', $batteryModel);
            }
            $master = $masterFallbackQuery->first();
        }

        // Histori record terakhir
        $latestRecord = $records->first();

        $lastReplacementDate = null;
        if ($latestRecord && $latestRecord->last_day) {
            $lastReplacementDate = Carbon::parse($latestRecord->last_day)->locale('id')->translatedFormat('d M Y');
        } elseif ($master && $master->install_date) {
            $lastReplacementDate = Carbon::parse($master->install_date)->locale('id')->translatedFormat('d M Y');
        }

        // Standard Volt strictly dari kolom StdVolt (std_volt) pada tabel batteries
        $standardVolt = $master?->std_volt ? trim((string)$master->std_volt) : ($latestRecord?->standart_volt ?: '-');
        $lastVoltageBefore = $latestRecord?->voltage_before ?: '-';
        $lastVoltageAfter = $latestRecord?->voltage_after ?: '-';
        // Status di kolom histori strictly menyesuaikan column statusAktif (status_aktif) pada tabel batteries
        $rawStatus = $master?->status_aktif ? trim((string)$master->status_aktif) : ($latestRecord?->status ?: 'active');
        $lastStatus = in_array(strtolower($rawStatus), ['error', 'change']) ? 'Change' : (strtolower($rawStatus) === 'warning' ? 'Warning' : 'Active');

        return response()->json([
            'success' => true,
            'last_number' => $maxNum,
            'last_exchange_type' => $lastExchangeRaw ?: ($latestRecord?->exchange_type ?: ($maxNum > 0 ? "Replace on exchange {$maxNum}" : '-')),
            'next_number' => $nextNum,
            'next_exchange_type' => $nextExchangeType,
            'last_replacement_date' => $lastReplacementDate ?: 'Belum ada data',
            'standard_volt' => $standardVolt,
            'last_voltage_before' => $lastVoltageBefore,
            'last_voltage_after' => $lastVoltageAfter,
            'last_status' => $lastStatus,
            'count_history' => $records->count(),
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        DB::transaction(function () use ($request) {
            // 1. Simpan backup semua data input manual pengguna sebelum import
            $manualInputs = \App\Models\InputBattery::where(function ($q) {
                $q->whereNotNull('voltage_before')
                    ->orWhereNotNull('voltage_after')
                    ->orWhereNotNull('prev_status_aktif');
            })->get()->toArray();

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            \App\Models\InputBattery::query()->delete();
            \App\Models\Battery::query()->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // 2. Import Master Battery (Sheet 1) & Riwayat Awal (Sheet 2)
            Excel::import(new BatteryImport, $request->file('file'));

            // 3. Kembalikan data input manual pengguna dan link kembali ke Master Battery baru
            $restoredIds = [];
            foreach ($manualInputs as $inputData) {
                unset($inputData['created_at'], $inputData['updated_at']);

                $line = $inputData['line'] ?? null;
                $op = $inputData['op_number'] ?? null;
                $equip = $inputData['equipment_type'] ?? null;
                $model = $inputData['battery_model'] ?? null;

                // Temukan master battery yang sesuai
                $matchedBattery = Battery::where('line', $line)
                    ->where(function ($q) use ($op) {
                        $q->where('machine_no', $op)->orWhere('op_number', $op);
                    })
                    ->where('equipment_type', $equip)
                    ->where('battery_model', $model)
                    ->first();

                if (!$matchedBattery) {
                    $matchedBattery = Battery::where('line', $line)
                        ->where(function ($q) use ($op) {
                            $q->where('machine_no', $op)->orWhere('op_number', $op);
                        })
                        ->first();
                }

                $inputData['battery_id'] = $matchedBattery?->id;
                $newRecord = \App\Models\InputBattery::create($inputData);
                $restoredIds[] = $newRecord->id;

                // Re-apply ke master battery agar status tetap 'active'
                if ($matchedBattery && !empty($inputData['last_day'])) {
                    $date = Carbon::parse($inputData['last_day']);
                    $matchedBattery->install_date = $date->format('Y-m-d');
                    if ($matchedBattery->replacement_cycle_month && (int)$matchedBattery->replacement_cycle_month > 0) {
                        $matchedBattery->next_replace_date = $date->copy()->addMonths((int)$matchedBattery->replacement_cycle_month)->format('Y-m-d');
                    }
                    $matchedBattery->status_aktif = 'active';
                    $matchedBattery->save();
                }
            }

            session(['recent_input_battery_ids' => $restoredIds]);
        });

        \Illuminate\Support\Facades\Cache::forget('battery_available_areas');
        \Illuminate\Support\Facades\Cache::forget('battery_available_lines');

        return redirect()->route('input.battery')
            ->with('success', 'File Excel Data Battery berhasil diimport dan data input manual tetap dipertahankan!');
    }

    public function storeInputBattery(Request $request)
    {
        $validated = $request->validate([
            'area' => 'required|string|max:100',
            'line' => 'required|string|max:100',
            'op' => 'required|string|max:100',
            'machine_no' => 'nullable|string|max:100',
            'equipment_type' => 'required|string|max:100',
            'battery_model' => 'required|string|max:100',
            'exchange_type' => 'required|string|max:100',
            'voltage_before' => 'required|string|max:50',
            'voltage_after' => 'required|string|max:50',
            'date' => 'required|date|before_or_equal:today',
        ], [
            'area.required' => 'Field Area wajib dipilih.',
            'line.required' => 'Field Line wajib dipilih.',
            'op.required' => 'Field OP Number wajib dipilih.',
            'equipment_type.required' => 'Field Equipment Type wajib dipilih.',
            'battery_model.required' => 'Field Battery Model wajib dipilih.',
            'exchange_type.required' => 'Field Exchange Type wajib terisi.',
            'voltage_before.required' => 'Field Voltage Before wajib diisi.',
            'voltage_after.required' => 'Field Voltage After wajib diisi.',
            'date.required' => 'Tanggal penggantian wajib diisi.',
            'date.before_or_equal' => 'Tanggal penggantian tidak boleh melebihi hari ini (hari esok / masa depan tidak diperbolehkan).',
        ]);

        $op = trim($validated['op']);
        $line = trim($validated['line']);
        $equip = trim($validated['equipment_type']);
        $model = trim($validated['battery_model']);
        $date = Carbon::parse($validated['date']);

        $mc = !empty($validated['machine_no']) ? trim($validated['machine_no']) : null;

        // Cari relasi ke Master Battery (Sheet 1)
        $matchedQuery = Battery::where('line', $line)
            ->where(function ($q) use ($op) {
                $q->where('machine_no', $op)
                    ->orWhere('op_number', $op);
            })
            ->where('equipment_type', $equip)
            ->where('battery_model', $model);
        if ($mc) {
            $matchedQuery->where(function ($q) use ($mc) {
                $q->where('machine_name', $mc)->orWhere('machine_no', $mc);
            });
        }
        $matchedBattery = $matchedQuery->first();

        if (!$matchedBattery) {
            $matchedBattery = Battery::where('line', $line)
                ->where(function ($q) use ($op) {
                    $q->where('machine_no', $op)
                        ->orWhere('op_number', $op);
                })
                ->where('equipment_type', $equip)
                ->where('battery_model', $model)
                ->first();
        }

        if (!$matchedBattery) {
            $matchedBattery = Battery::where('line', $line)
                ->where(function ($q) use ($op) {
                    $q->where('machine_no', $op)
                        ->orWhere('op_number', $op);
                })
                ->first();
        }

        // Snapshot previous state master battery sebelum update
        $prevInstallDate = $matchedBattery?->install_date;
        $prevNextReplaceDate = $matchedBattery?->next_replace_date;
        $prevStatusAktif = $matchedBattery?->status_aktif ?: 'error';

        // Update master battery status dan install_date jika ada
        if ($matchedBattery) {
            $matchedBattery->install_date = $date->format('Y-m-d');
            if ($matchedBattery->replacement_cycle_month && (int)$matchedBattery->replacement_cycle_month > 0) {
                $matchedBattery->next_replace_date = $date->copy()->addMonths((int)$matchedBattery->replacement_cycle_month)->format('Y-m-d');
            }
            $matchedBattery->status_aktif = 'active';
            $matchedBattery->save();
        }

        $mcNo = !empty($validated['machine_no']) ? trim($validated['machine_no']) : ($matchedBattery?->machine_name ?? $op);
        $userName = Auth::check() ? Auth::user()->name : (session('user_name') ?: 'Admin');

        $created = \App\Models\InputBattery::create([
            'battery_id' => $matchedBattery?->id,
            'area' => $validated['area'] ?: ($matchedBattery?->area ?? null),
            'line' => $line,
            'op_number' => $op,
            'machine_no' => $mcNo,
            'equipment_type' => $equip,
            'battery_model' => $model,
            'exchange_type' => $validated['exchange_type'] ?: null,
            'voltage_before' => $validated['voltage_before'] ?: null,
            'voltage_after' => $validated['voltage_after'] ?: null,
            'standart_volt' => $matchedBattery?->std_volt ?: null,
            'last_day' => $date->format('Y-m-d'),
            'trend_pengganti' => $matchedBattery?->next_replace_date ? Carbon::parse($matchedBattery->next_replace_date)->format('Y-m-d') : null,
            'status' => 'active',
            'prev_install_date' => $prevInstallDate,
            'prev_next_replace_date' => $prevNextReplaceDate,
            'prev_status_aktif' => $prevStatusAktif,
            'created_by' => $userName,
        ]);

        session()->push('recent_input_battery_ids', $created->id);

        \Illuminate\Support\Facades\Cache::forget('battery_available_areas');
        \Illuminate\Support\Facades\Cache::forget('battery_available_lines');

        return redirect()->route('input.battery')
            ->with('success', 'Data Input Penggantian Battery berhasil disimpan!');
    }

    /**
     * Memastikan hanya administrator yang berhak mengubah atau menghapus data battery
     */
    protected function checkAdmin()
    {
        $role = Auth::user()?->role ?? session('user_role');
        if (strtolower($role ?? '') !== 'admin') {
            abort(403, 'Akses Ditolak: Hanya Administrator yang berhak mengubah atau menghapus data.');
        }
    }

    public function updateInputBattery(Request $request, $id)
    {
        $this->checkAdmin();
        $item = \App\Models\InputBattery::findOrFail($id);
        $validated = $request->validate([
            'voltage_before' => 'nullable|string|max:50',
            'voltage_after' => 'nullable|string|max:50',
            'exchange_type' => 'nullable|string|max:100',
            'last_day' => 'required|date',
            'status' => 'nullable|string|max:50',
        ]);

        $item->update([
            'voltage_before' => $validated['voltage_before'] ?? $item->voltage_before,
            'voltage_after' => $validated['voltage_after'] ?? $item->voltage_after,
            'exchange_type' => $validated['exchange_type'] ?? $item->exchange_type,
            'last_day' => Carbon::parse($validated['last_day'])->format('Y-m-d'),
            'status' => $validated['status'] ?? $item->status,
        ]);

        return redirect()->route('input.battery')
            ->with('success', 'Data Input Battery berhasil diperbarui!');
    }

    public function exportInputBattery(?Request $request = null)
    {
        $request = $request ?: request();
        $recentIds = session('recent_input_battery_ids', []);
        $query = \App\Models\InputBattery::orderBy('id', 'desc');

        // Jika export spesifik rentang waktu (start_date / end_date) atau area diberikan, utamakan filter tersebut
        $hasCustomFilter = $request->filled('start_date') || $request->filled('end_date') || ($request->filled('area') && $request->area !== 'ALL');

        if (!$hasCustomFilter && !$request->boolean('all') && !empty($recentIds)) {
            $query->whereIn('id', $recentIds);
        }

        // Filter rentang waktu (mutasi) tanggal penggantian
        if ($request->filled('start_date')) {
            $query->whereDate('last_day', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('last_day', '<=', $request->end_date);
        }

        // Filter area
        if ($request->filled('area') && $request->area !== 'ALL') {
            $query->where(function ($q) use ($request) {
                $q->where('area', $request->area)
                    ->orWhereHas('battery', function ($bq) use ($request) {
                        $bq->where('area', $request->area);
                    });
            });
        }

        $records = $query->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Penggantian Battery');

        $headers = [
            'A1' => 'No',
            'B1' => 'Area',
            'C1' => 'Line',
            'D1' => 'OP Number',
            'E1' => 'Machine No',
            'F1' => 'Equipment Type',
            'G1' => 'Battery Model',
            'H1' => 'Exchange Type',
            'I1' => 'Voltage Before (V)',
            'J1' => 'Voltage After (V)',
            'K1' => 'Tanggal Penggantian',
            'L1' => 'Estimasi Penggantian Berikutnya',
            'M1' => 'Status',
            'N1' => 'Input By',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Header style
        $sheet->getStyle('A1:N1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E293B']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $rowNum = 2;
        $no = 1;
        foreach ($records as $item) {
            $sheet->setCellValue("A{$rowNum}", $no++);
            $sheet->setCellValue("B{$rowNum}", $item->area ?: '-');
            $sheet->setCellValue("C{$rowNum}", $item->line ?: '-');
            $sheet->setCellValue("D{$rowNum}", $item->op_number ?: '-');
            $sheet->setCellValue("E{$rowNum}", $item->machine_no ?: '-');
            $sheet->setCellValue("F{$rowNum}", $item->equipment_type ?: '-');
            $sheet->setCellValue("G{$rowNum}", $item->battery_model ?: '-');
            $sheet->setCellValue("H{$rowNum}", $item->exchange_type ?: '-');
            $sheet->setCellValue("I{$rowNum}", $item->voltage_before ? "{$item->voltage_before} V" : '-');
            $sheet->setCellValue("J{$rowNum}", $item->voltage_after ? "{$item->voltage_after} V" : ($item->standart_volt ? "{$item->standart_volt} V" : '-'));
            $sheet->setCellValue("K{$rowNum}", $item->last_day ? Carbon::parse($item->last_day)->locale('id')->translatedFormat('d M Y') : '-');
            $sheet->setCellValue("L{$rowNum}", $item->trend_pengganti ? Carbon::parse($item->trend_pengganti)->locale('id')->translatedFormat('d M Y') : '-');
            $sheet->setCellValue("M{$rowNum}", ucfirst($item->status ?: 'active'));
            $sheet->setCellValue("N{$rowNum}", $item->created_by ?: 'System');

            $sheet->getStyle("A{$rowNum}:N{$rowNum}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle("A{$rowNum}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("K{$rowNum}:N{$rowNum}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $rowNum++;
        }

        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Data_Penggantian_Battery_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function exportMasterBattery(Request $request)
    {
        $query = Battery::query()->orderBy('area')->orderBy('line')->orderBy('machine_no');

        // Filter Area
        if ($request->filled('area') && $request->area !== 'ALL') {
            $query->where('area', $request->area);
        }

        // Filter Line
        if ($request->filled('line') && $request->line !== 'ALL') {
            $query->where('line', $request->line);
        }

        // Filter Status
        if ($request->filled('status') && $request->status !== 'ALL') {
            $query->where('status_aktif', $request->status);
        }

        // Filter Rentang Waktu Mutasi (Install Date / Next Replace Date)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->where(function ($q) use ($request) {
                $q->whereBetween('install_date', [$request->start_date, $request->end_date])
                    ->orWhereBetween('next_replace_date', [$request->start_date, $request->end_date]);
            });
        } elseif ($request->filled('start_date')) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('install_date', '>=', $request->start_date)
                    ->orWhereDate('next_replace_date', '>=', $request->start_date);
            });
        } elseif ($request->filled('end_date')) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('install_date', '<=', $request->end_date)
                    ->orWhereDate('next_replace_date', '<=', $request->end_date);
            });
        }

        $records = $query->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('MST_Battery');

        $headers = [
            'A1' => 'No',
            'B1' => 'LEVEL',
            'C1' => 'BatteryID',
            'D1' => 'Area',
            'E1' => 'Line',
            'F1' => 'MachineNo',
            'G1' => 'MachineName',
            'H1' => 'Maker',
            'I1' => 'EquipmentType',
            'J1' => 'Device',
            'K1' => 'BatteryModel',
            'L1' => 'BatteryType',
            'M1' => 'StdVolt',
            'N1' => 'InstallDate',
            'O1' => 'ReplacementCycleMonth',
            'P1' => 'NextReplaceDate',
            'Q1' => 'StatusAktif',
            'R1' => 'How many',
            'S1' => 'Number of machines',
            'T1' => 'Aggregate (Total Device)',
            'U1' => 'Input By',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Header style (dark navy slate)
        $sheet->getStyle('A1:U1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F172A']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $rowNum = 2;
        $no = 1;
        foreach ($records as $item) {
            $sheet->setCellValue("A{$rowNum}", $no++);
            $sheet->setCellValue("B{$rowNum}", $item->level ?: '-');
            $sheet->setCellValue("C{$rowNum}", $item->battery_id ?: '-');
            $sheet->setCellValue("D{$rowNum}", $item->area ?: '-');
            $sheet->setCellValue("E{$rowNum}", $item->line ?: '-');
            $sheet->setCellValue("F{$rowNum}", $item->machine_no ?: ($item->op_number ?: '-'));
            $sheet->setCellValue("G{$rowNum}", $item->machine_name ?: '-');
            $sheet->setCellValue("H{$rowNum}", $item->maker ?: '-');
            $sheet->setCellValue("I{$rowNum}", $item->equipment_type ?: '-');
            $sheet->setCellValue("J{$rowNum}", $item->device ?: '-');
            $sheet->setCellValue("K{$rowNum}", $item->battery_model ?: '-');
            $sheet->setCellValue("L{$rowNum}", $item->battery_type ?: '-');
            $sheet->setCellValue("M{$rowNum}", $item->std_volt ? "{$item->std_volt} V" : '-');
            $sheet->setCellValue("N{$rowNum}", $item->install_date ? Carbon::parse($item->install_date)->locale('id')->translatedFormat('d M Y') : '-');
            $sheet->setCellValue("O{$rowNum}", $item->replacement_cycle_month ? (int)$item->replacement_cycle_month : '-');
            $sheet->setCellValue("P{$rowNum}", $item->next_replace_date ? Carbon::parse($item->next_replace_date)->locale('id')->translatedFormat('d M Y') : '-');
            $sheet->setCellValue("Q{$rowNum}", ucfirst($item->status_aktif ?: 'active'));
            $sheet->setCellValue("R{$rowNum}", $item->how_many ?: '1');
            $sheet->setCellValue("S{$rowNum}", $item->number_of ?: '1');
            $sheet->setCellValue("T{$rowNum}", $item->aggregate ?: '1');
            $sheet->setCellValue("U{$rowNum}", $item->created_by ?: 'System');

            $sheet->getStyle("A{$rowNum}:U{$rowNum}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle("A{$rowNum}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$rowNum}:C{$rowNum}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("M{$rowNum}:U{$rowNum}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $rowNum++;
        }

        foreach (range('A', 'U') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Master_Data_Battery_MST_Battery_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function masterBattery(Request $request)
    {
        $query = Battery::query();

        if ($request->filled('area')) {
            $query->where('area', $request->area);
        }
        if ($request->filled('line')) {
            $query->where('line', $request->line);
        }
        if ($request->filled('status')) {
            $query->where('status_aktif', $request->status);
        }
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('machine_no', 'like', "%{$s}%")
                    ->orWhere('machine_name', 'like', "%{$s}%")
                    ->orWhere('op_number', 'like', "%{$s}%")
                    ->orWhere('line', 'like', "%{$s}%")
                    ->orWhere('area', 'like', "%{$s}%")
                    ->orWhere('equipment_type', 'like', "%{$s}%")
                    ->orWhere('battery_model', 'like', "%{$s}%");
            });
        }

        $batteries = $query->orderBy('area')->orderBy('line')->orderBy('machine_no')->paginate(25)->withQueryString();

        // 1. Anti Double Data & Normalized Areas and Lines
        $areas = Battery::whereNotNull('area')
            ->where('area', '!=', '')
            ->pluck('area')
            ->map(fn($v) => trim((string)$v))
            ->filter(fn($v) => $v !== '' && $v !== '-')
            ->unique()
            ->sort()
            ->values();

        $lineQuery = Battery::whereNotNull('line')->where('line', '!=', '');
        if ($request->filled('area')) {
            $lineQuery->where('area', $request->area);
        }
        $lines = $lineQuery->pluck('line')
            ->map(fn($v) => trim((string)$v))
            ->filter(fn($v) => $v !== '' && $v !== '-')
            ->unique()
            ->sort()
            ->values();

        // 2. Comprehensive Master Data Options for All Form Columns (Anti-Double / Distinct)
        $masterOptions = [
            'areas' => $areas,
            'lines' => $lines,
            'machine_nos' => Battery::where(function($q) {
                    $q->whereNotNull('machine_no')->where('machine_no', '!=', '')
                      ->orWhereNotNull('op_number')->where('op_number', '!=', '');
                })
                ->pluck('machine_no')
                ->merge(Battery::whereNotNull('op_number')->where('op_number', '!=', '')->pluck('op_number'))
                ->map(fn($v) => trim((string)$v))
                ->filter(fn($v) => $v !== '' && $v !== '-')
                ->unique()
                ->sort()
                ->values(),
            'machine_names' => Battery::whereNotNull('machine_name')->where('machine_name', '!=', '')
                ->pluck('machine_name')
                ->map(fn($v) => trim((string)$v))
                ->filter(fn($v) => $v !== '' && $v !== '-')
                ->unique()
                ->sort()
                ->values(),
            'levels' => Battery::whereNotNull('level')->where('level', '!=', '')
                ->pluck('level')
                ->merge(['1', '2', 'L1', 'L2', 'LEVEL 1', 'LEVEL 2'])
                ->map(fn($v) => trim((string)$v))
                ->filter(fn($v) => $v !== '' && $v !== '-')
                ->unique()
                ->sort()
                ->values(),
            'battery_ids' => Battery::whereNotNull('battery_id')->where('battery_id', '!=', '')
                ->pluck('battery_id')
                ->map(fn($v) => trim((string)$v))
                ->filter(fn($v) => $v !== '' && $v !== '-')
                ->unique()
                ->sort()
                ->values(),
            'makers' => Battery::whereNotNull('maker')->where('maker', '!=', '')
                ->pluck('maker')
                ->map(fn($v) => trim((string)$v))
                ->filter(fn($v) => $v !== '' && $v !== '-')
                ->unique()
                ->sort()
                ->values(),
            'equipment_types' => Battery::whereNotNull('equipment_type')->where('equipment_type', '!=', '')
                ->pluck('equipment_type')
                ->map(fn($v) => trim((string)$v))
                ->filter(fn($v) => $v !== '' && $v !== '-')
                ->unique()
                ->sort()
                ->values(),
            'devices' => Battery::whereNotNull('device')->where('device', '!=', '')
                ->pluck('device')
                ->map(fn($v) => trim((string)$v))
                ->filter(fn($v) => $v !== '' && $v !== '-')
                ->unique()
                ->sort()
                ->values(),
            'battery_models' => Battery::whereNotNull('battery_model')->where('battery_model', '!=', '')
                ->pluck('battery_model')
                ->map(fn($v) => trim((string)$v))
                ->filter(fn($v) => $v !== '' && $v !== '-')
                ->unique()
                ->sort()
                ->values(),
            'battery_types' => Battery::whereNotNull('battery_type')->where('battery_type', '!=', '')
                ->pluck('battery_type')
                ->merge(['Lithium', 'Alkali', 'NiMH', 'Lead Acid', 'Others'])
                ->map(fn($v) => trim((string)$v))
                ->filter(fn($v) => $v !== '' && $v !== '-')
                ->unique()
                ->sort()
                ->values(),
            'std_volts' => Battery::whereNotNull('std_volt')->where('std_volt', '!=', '')
                ->pluck('std_volt')
                ->merge(['1.5', '3', '3.6', '6', '12'])
                ->map(fn($v) => trim((string)$v))
                ->filter(fn($v) => $v !== '' && $v !== '-')
                ->unique()
                ->sort()
                ->values(),
            'cycles' => Battery::whereNotNull('replacement_cycle_month')->where('replacement_cycle_month', '!=', '')
                ->pluck('replacement_cycle_month')
                ->merge(['6', '12', '18', '24', '36', '48', '60'])
                ->map(fn($v) => (string)(int)trim((string)$v))
                ->filter(fn($v) => (int)$v > 0)
                ->unique()
                ->sort(SORT_NUMERIC)
                ->values(),
            'how_manys' => Battery::whereNotNull('how_many')->where('how_many', '!=', '')
                ->pluck('how_many')
                ->merge(['1', '2', '3', '4', '6'])
                ->map(fn($v) => trim((string)$v))
                ->filter(fn($v) => $v !== '' && $v !== '-')
                ->unique()
                ->sort()
                ->values(),
            'number_ofs' => Battery::whereNotNull('number_of')->where('number_of', '!=', '')
                ->pluck('number_of')
                ->merge(['1', '2', '3', '4'])
                ->map(fn($v) => trim((string)$v))
                ->filter(fn($v) => $v !== '' && $v !== '-')
                ->unique()
                ->sort()
                ->values(),
            'aggregates' => Battery::whereNotNull('aggregate')->where('aggregate', '!=', '')
                ->pluck('aggregate')
                ->merge(['1', '2', '3', '4'])
                ->map(fn($v) => trim((string)$v))
                ->filter(fn($v) => $v !== '' && $v !== '-')
                ->unique()
                ->sort()
                ->values(),
        ];

        $today = Carbon::today();
        $stats = [
            'total' => Battery::count(),
            'total_active' => Battery::where('status_aktif', 'active')->count(),
            'total_change' => Battery::where(function ($q) use ($today) {
                $q->where('status_aktif', 'change')
                    ->orWhere(function ($sq) use ($today) {
                        $sq->where('status_aktif', '!=', 'active')
                            ->whereNotNull('next_replace_date')
                            ->where('next_replace_date', '<', $today);
                    });
            })->count(),
            'total_warning' => Battery::where(function ($q) {
                $q->where('status_aktif', 'warning')
                    ->orWhere(function ($sq) {
                        $sq->where('status_aktif', '!=', 'active')
                            ->whereNull('next_replace_date');
                    });
            })->count(),
        ];

        $allMasterRecords = Battery::select([
            'area', 'line', 'machine_no', 'op_number', 'machine_name', 'level',
            'battery_id', 'maker', 'equipment_type', 'device', 'battery_model',
            'battery_type', 'std_volt', 'replacement_cycle_month'
        ])
        ->whereNotNull('area')
        ->where('area', '!=', '')
        ->get()
        ->map(function($b) {
            $op = trim((string)($b->machine_no ?: ($b->op_number ?: '')));
            return [
                'area' => trim((string)$b->area),
                'line' => trim((string)$b->line),
                'machine_no' => $op,
                'machine_name' => trim((string)$b->machine_name),
                'level' => trim((string)$b->level),
                'battery_id' => trim((string)$b->battery_id),
                'maker' => trim((string)$b->maker),
                'equipment_type' => trim((string)$b->equipment_type),
                'device' => trim((string)$b->device),
                'battery_model' => trim((string)$b->battery_model),
                'battery_type' => trim((string)$b->battery_type),
                'std_volt' => trim((string)$b->std_volt),
                'replacement_cycle_month' => (int)$b->replacement_cycle_month,
            ];
        })
        ->values();

        return view('content.master-data.battery', compact('batteries', 'areas', 'lines', 'stats', 'masterOptions', 'allMasterRecords'));
    }

    public function destroyInputBattery($id)
    {
        $this->checkAdmin();
        $item = \App\Models\InputBattery::findOrFail($id);

        // Cari battery terkait
        $battery = $item->battery ?: ($item->battery_id ? Battery::find($item->battery_id) : null);
        if (!$battery) {
            $op = trim((string)$item->op_number);
            $mc = trim((string)$item->machine_no);
            $line = trim((string)$item->line);
            $equip = trim((string)$item->equipment_type);
            $model = trim((string)$item->battery_model);

            $matchedQuery = Battery::where('line', $line);
            if ($op !== '' && $op !== '-') {
                $matchedQuery->where(function ($q) use ($op) {
                    $q->where('machine_no', $op)->orWhere('op_number', $op);
                });
            }
            if ($equip !== '' && $equip !== '-') {
                $matchedQuery->where('equipment_type', $equip);
            }
            if ($model !== '' && $model !== '-') {
                $matchedQuery->where('battery_model', $model);
            }
            if ($mc !== '' && $mc !== '-') {
                $matchedQuery->where(function ($q) use ($mc) {
                    $q->where('machine_name', $mc)->orWhere('machine_no', $mc);
                });
            }
            $battery = $matchedQuery->first();

            if (!$battery) {
                $battery = Battery::where('line', $line)
                    ->where(function ($q) use ($op) {
                        $q->where('machine_no', $op)->orWhere('op_number', $op);
                    })
                    ->where('equipment_type', $equip)
                    ->where('battery_model', $model)
                    ->first();
            }
            if (!$battery) {
                $battery = Battery::where('line', $line)
                    ->where(function ($q) use ($op) {
                        $q->where('machine_no', $op)->orWhere('op_number', $op);
                    })
                    ->first();
            }
        }

        if ($battery) {
            // Cek apakah ada record InputBattery lain yang lebih baru dari item ini untuk battery ini
            $laterRecord = \App\Models\InputBattery::where('id', '!=', $item->id)
                ->where(function ($q) use ($battery, $item) {
                    if ($battery->id) {
                        $q->where('battery_id', $battery->id);
                    }
                    $q->orWhere(function ($sq) use ($item) {
                        $sq->where('line', $item->line)
                            ->where(function ($oq) use ($item) {
                                $oq->where('op_number', $item->op_number)
                                    ->orWhere('machine_no', $item->op_number);
                            })
                            ->where('equipment_type', $item->equipment_type)
                            ->where('battery_model', $item->battery_model);
                    });
                })
                ->where('id', '>', $item->id)
                ->orderBy('id', 'desc')
                ->first();

            // Jika item yang dihapus adalah input terakhir (tidak ada record penggantian setelahnya)
            if (!$laterRecord) {
                if (!empty($item->prev_status_aktif)) {
                    // Revert menggunakan snapshot nilai sebelum input
                    $battery->install_date = $item->prev_install_date;
                    $battery->next_replace_date = $item->prev_next_replace_date;
                    $battery->status_aktif = $item->prev_status_aktif;
                } else {
                    // Fallback: cari input sebelumnya
                    $prevInput = \App\Models\InputBattery::where('id', '!=', $item->id)
                        ->where(function ($q) use ($battery, $item) {
                            if ($battery->id) {
                                $q->where('battery_id', $battery->id);
                            }
                            $q->orWhere(function ($sq) use ($item) {
                                $sq->where('line', $item->line)
                                    ->where(function ($oq) use ($item) {
                                        $oq->where('op_number', $item->op_number)
                                            ->orWhere('machine_no', $item->op_number);
                                    })
                                    ->where('equipment_type', $item->equipment_type)
                                    ->where('battery_model', $item->battery_model);
                            });
                        })
                        ->orderByDesc('last_day')
                        ->orderByDesc('id')
                        ->first();

                    if ($prevInput) {
                        $battery->install_date = $prevInput->last_day;
                        $battery->next_replace_date = $prevInput->trend_pengganti;
                        $today = Carbon::today();
                        $nextDate = $prevInput->trend_pengganti ? Carbon::parse($prevInput->trend_pengganti) : null;
                        if (!$nextDate || $nextDate->lt($today)) {
                            $battery->status_aktif = 'error';
                        } else {
                            $battery->status_aktif = 'active';
                        }
                    } else {
                        // Tidak ada riwayat penggantian lain: kembalikan ke unreplaced / error
                        $battery->status_aktif = 'error';
                        if ($battery->install_date && Carbon::parse($battery->install_date)->isSameDay(Carbon::parse($item->last_day))) {
                            $battery->install_date = null;
                            $battery->next_replace_date = null;
                        }
                    }
                }
                $battery->save();
            }
        }

        $item->delete();

        $recentIds = session('recent_input_battery_ids', []);
        $recentIds = array_values(array_filter($recentIds, fn($val) => $val != $id));
        session(['recent_input_battery_ids' => $recentIds]);

        \Illuminate\Support\Facades\Cache::forget('battery_available_areas');
        \Illuminate\Support\Facades\Cache::forget('battery_available_lines');

        return redirect()->route('input.battery')
            ->with('success', 'Data Input Battery berhasil dihapus dan status aset telah disesuaikan kembali!');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'line' => 'required|string|max:100',
            'machine_no' => 'nullable|string|max:100',
            'machine_name' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:100',
            'equipment_type' => 'nullable|string|max:100',
            'battery_model' => 'nullable|string|max:100',
            'std_volt' => 'nullable|string|max:50',
            'install_date' => 'nullable|date',
            'replacement_cycle_month' => 'nullable|integer',
            'next_replace_date' => 'nullable|date',
            'how_many' => 'nullable|string|max:100',
            'number_of' => 'nullable|string|max:100',
            'aggregate' => 'nullable|string|max:100',
            'status_aktif' => 'nullable|string|max:50',
            'maker' => 'nullable|string|max:100',
            'device' => 'nullable|string|max:100',
            'battery_type' => 'nullable|string|max:100',
            'level' => 'nullable|string|max:100',
            'battery_id' => 'nullable|string|max:100',
        ]);

        $installDate = !empty($validated['install_date']) ? Carbon::parse($validated['install_date']) : null;
        $cycle = !empty($validated['replacement_cycle_month']) ? (int) $validated['replacement_cycle_month'] : null;
        $nextDate = !empty($validated['next_replace_date']) ? Carbon::parse($validated['next_replace_date']) : null;

        if (!$nextDate && $installDate && $cycle > 0) {
            $nextDate = $installDate->copy()->addMonths($cycle);
            $validated['next_replace_date'] = $nextDate->format('Y-m-d');
        }

        if (empty($validated['status_aktif'])) {
            $today = Carbon::today();
            $in30Days = Carbon::today()->addDays(30);

            if (is_null($nextDate)) {
                $validated['status_aktif'] = 'error';
            } elseif ($nextDate->lt($today)) {
                $validated['status_aktif'] = 'error';
            } elseif ($nextDate->lte($in30Days)) {
                $validated['status_aktif'] = 'warning';
            } else {
                $validated['status_aktif'] = 'active';
            }
        }

        $userName = Auth::check() ? Auth::user()->name : (session('user_name') ?: 'Admin');
        $validated['created_by'] = $userName;

        Battery::create($validated);

        \Illuminate\Support\Facades\Cache::forget('battery_available_areas');
        \Illuminate\Support\Facades\Cache::forget('battery_available_lines');

        if ($request->header('referer') && str_contains($request->header('referer'), 'master-data')) {
            return redirect()->route('master-data.battery')
                ->with('success', 'Data Master Battery berhasil ditambahkan!');
        }

        return redirect()->back()
            ->with('success', 'Data Master Battery berhasil ditambahkan!');
    }

    public function destroy(Request $request, $id)
    {
        $this->checkAdmin();
        $item = Battery::findOrFail($id);
        $item->delete();

        \Illuminate\Support\Facades\Cache::forget('battery_available_areas');
        \Illuminate\Support\Facades\Cache::forget('battery_available_lines');

        if ($request->header('referer') && str_contains($request->header('referer'), 'master-data')) {
            return redirect()->route('master-data.battery')
                ->with('success', 'Data Master Battery berhasil dihapus!');
        }

        return redirect()->back()
            ->with('success', 'Data Master Battery berhasil dihapus!');
    }

    public function dashboardBattery(Request $request)
    {
        $query = Battery::query()->select([
            'id', 'level', 'battery_id', 'area', 'line', 'machine_no', 'machine_name',
            'maker', 'equipment_type', 'device', 'battery_model', 'battery_type',
            'std_volt', 'install_date', 'replacement_cycle_month', 'next_replace_date', 'status_aktif',
            'op_number', 'exchange_type', 'trend_pengganti', 'how_many', 'number_of', 'aggregate', 'created_by'
        ]);

        $selectedArea = $request->get('area', 'ALL');
        $selectedLine = $request->get('line', 'ALL');

        // Distinct areas cached
        $availableAreas = \Illuminate\Support\Facades\Cache::remember('battery_available_areas', 3600, function () {
            return Battery::whereNotNull('area')->where('area', '!=', '')->distinct()->orderBy('area')->pluck('area')->toArray();
        });

        // Filter available lines based on selected area
        if ($selectedArea && $selectedArea !== 'ALL') {
            $availableLines = Battery::where('area', $selectedArea)
                ->whereNotNull('line')
                ->where('line', '!=', '')
                ->distinct()
                ->orderBy('line')
                ->pluck('line')
                ->toArray();

            // If currently selectedLine is not in this area's available lines, reset to ALL
            if ($selectedLine !== 'ALL' && !in_array($selectedLine, $availableLines)) {
                $selectedLine = 'ALL';
            }
        } else {
            $availableLines = \Illuminate\Support\Facades\Cache::remember('battery_available_lines', 3600, function () {
                return Battery::whereNotNull('line')->where('line', '!=', '')->distinct()->orderBy('line')->pluck('line')->toArray();
            });
        }

        if ($selectedArea && $selectedArea !== 'ALL') {
            $query->where('area', $selectedArea);
        }

        if ($selectedLine && $selectedLine !== 'ALL') {
            $query->where('line', $selectedLine);
        }

        $allBatteries = $query->orderBy('line')->orderBy('machine_no')->get();
        $totalBatteries = $allBatteries->count();

        $today = Carbon::today();
        $in30Days = Carbon::today()->addDays(30);

        $activeCount = 0;
        $changeCount = 0;
        $warningCount = 0;
        $expiringSoonCount = 0;
        $missingCount = 0;
        $totalBatteryQuantity = 0;
        $totalDeviceCount = 0;
        $totalBatteryModels = 0;
        $totalMachines = 0;
        $totalNumberOfMachines = 0;
        $distinctMachinesSet = [];
        $alkaliCount = 0;
        $lithiumCount = 0;
        $otherTypeCount = 0;

        $modelCounts = [];
        $modelHowManyCounts = [];
        $modelStats = [];
        $lineStats = [];
        $deviceCounts = [];
        $calendarDue = [];
        $calendarStatus = [];
        $monthlySchedule = [];
        $areaMachiningStats = ['active' => 0, 'change' => 0, 'warning' => 0, 'total' => 0];
        $areaShaftStats = ['active' => 0, 'change' => 0, 'warning' => 0, 'total' => 0];
        $areaEngineStats = ['active' => 0, 'change' => 0, 'warning' => 0, 'total' => 0];

        $todayYear = $today->year;
        $todayMonth = $today->month;
        $trendStart = $today->copy()->startOfMonth();
        $trendEnd = $today->copy()->addMonths(6)->endOfMonth();

        $batteriesData = [];

        foreach ($allBatteries as $b) {
            $nextDate = $b->next_replace_date instanceof Carbon ? $b->next_replace_date : ($b->next_replace_date ? Carbon::parse($b->next_replace_date) : null);
            $installDate = $b->install_date instanceof Carbon ? $b->install_date : ($b->install_date ? Carbon::parse($b->install_date) : null);

            // Quantity (How Many) - default 0 jika kosong agar SUM tepat 1568
            $qty = 0;
            $hasHowMany = false;
            if (!empty($b->how_many)) {
                $cleanQty = preg_replace('/[^0-9]/', '', (string)$b->how_many);
                if (is_numeric($cleanQty) && (int)$cleanQty > 0) {
                    $qty = (int)$cleanQty;
                    $hasHowMany = true;
                }
            }
            $totalBatteryQuantity += $qty;
            if ($hasHowMany) {
                $totalDeviceCount++;
            }

            // Group by Area (Machining 5C & QC, Production Shaft / PS, Engine)
            $areaLower = strtolower($b->area ?: '');
            $areaKey = 'other';
            if (str_contains($areaLower, 'machining') || str_contains($areaLower, 'quality') || str_contains($areaLower, 'qc')) {
                $areaKey = 'machining';
            } elseif (str_contains($areaLower, 'shaft') || str_contains($areaLower, 'housing') || str_contains($areaLower, 'ps') || str_contains($areaLower, 'pro.shaft')) {
                $areaKey = 'shaft';
            } elseif (str_contains($areaLower, 'engine') || str_contains($areaLower, 'assy') || str_contains($areaLower, 'tm')) {
                $areaKey = 'engine';
            }

            // Status determination per area:
            // 1. Machining 5C:
            //    - Aktif (105): status_aktif === 'active'
            //    - Change (502): next_replace_date < today (expired)
            //    - Warning / Error (29): next_replace_date IS NULL (data kurang / error)
            //    Jumlah unit dihitung dari SUM(aggregate)
            // 2. Production Shaft & Engine: Status Aktif (Hijau) dan Warning (Kuning)
            $isActive = ($b->status_aktif === 'active');
            $diff = $nextDate ? (int)$today->diffInDays($nextDate, false) : null;

            // Aggregate value from column Aggregate
            $agg = 1;
            if (!empty($b->aggregate)) {
                $cleanAgg = preg_replace('/[^0-9]/', '', (string)$b->aggregate);
                if (is_numeric($cleanAgg) && (int)$cleanAgg > 0) {
                    $agg = (int)$cleanAgg;
                }
            }

            // Status determination (berlaku untuk semua area, lines, dan machines):
            // - Aktif: status_aktif === 'active'
            // - Warning / Error: next_replace_date IS NULL (data kurang / error)
            // - Change: expired / next_replace_date < today
            // Perhitungan dihitung dari SUM(aggregate)
            if ($isActive) {
                $status = 'active';
                $activeCount += $agg;
                $statusLabel = $nextDate ? "Normal ({$diff} Hari)" : 'Aktif';
                $statusColor = 'emerald';
                $daysRemaining = $diff;
            } elseif (is_null($nextDate)) {
                $status = 'warning';
                $warningCount += $agg;
                $statusLabel = 'Warning / Error (Data Kurang)';
                $statusColor = 'amber';
                $daysRemaining = null;
            } else {
                $status = 'change';
                $changeCount += $agg;
                $statusLabel = 'Change' . ($diff !== null ? ($diff < 0 ? " (Expired " . abs($diff) . " Hari)" : " (Sisa {$diff} Hari)") : '');
                $statusColor = 'rose';
                $daysRemaining = $diff;
            }

            if ($areaKey === 'machining') {
                $areaMachiningStats['total'] += $agg;
                $areaMachiningStats[$status] += $agg;
            } elseif ($areaKey === 'shaft') {
                $areaShaftStats['total'] += $agg;
                $areaShaftStats[$status] += $agg;
            } elseif ($areaKey === 'engine') {
                $areaEngineStats['total'] += $agg;
                $areaEngineStats[$status] += $agg;
            }

            // Group by Model (hanya model yang valid agar total model tepat 971)
            $rawModel = trim((string)($b->battery_model ?? ''));
            $hasModel = ($rawModel !== '' && $rawModel !== '-');
            if ($hasModel) {
                $modelName = $rawModel;
                $modelCounts[$modelName] = ($modelCounts[$modelName] ?? 0) + 1;
                $totalBatteryModels++;

                $modelHowManyCounts[$modelName] = ($modelHowManyCounts[$modelName] ?? 0) + $qty;
                if (!isset($modelStats[$modelName])) {
                    $modelStats[$modelName] = ['active' => 0, 'change' => 0, 'warning' => 0, 'total' => 0, 'qty' => 0];
                }
                $modelStats[$modelName]['total']++;
                $modelStats[$modelName]['qty'] += $qty;
                $modelStats[$modelName][$status] += $qty;
            }

            // Group by Device
            $deviceName = trim($b->device ?: 'PLC/Control');
            $deviceCounts[$deviceName] = ($deviceCounts[$deviceName] ?? 0) + 1;

            // Group by Line (active, change, warning, dan sum how_many)
            $lineName = trim($b->line ?: 'Line Unknown');
            if (!isset($lineStats[$lineName])) {
                $lineStats[$lineName] = [
                    'active' => 0,
                    'change' => 0,
                    'warning' => 0,
                    'total' => 0,
                    'how_many' => 0,
                    'count_how_many' => 0,
                    'active_how_many' => 0,
                    'change_how_many' => 0,
                    'warning_how_many' => 0
                ];
            }
            $lineStats[$lineName]['total'] += $agg;
            $lineStats[$lineName]['how_many'] += $qty;
            if ($hasHowMany) {
                $lineStats[$lineName]['count_how_many']++;
            }
            if ($status === 'active') {
                $lineStats[$lineName]['active'] += $agg;
                $lineStats[$lineName]['active_how_many'] += $qty;
            } elseif ($status === 'change') {
                $lineStats[$lineName]['change'] += $agg;
                $lineStats[$lineName]['change_how_many'] += $qty;
            } else {
                $lineStats[$lineName]['warning'] += $agg;
                $lineStats[$lineName]['warning_how_many'] += $qty;
            }

            // Calendar this month
            if ($nextDate && $nextDate->year === $todayYear && $nextDate->month === $todayMonth) {
                $day = $nextDate->day;
                $calendarDue[$day] = ($calendarDue[$day] ?? 0) + 1;
                if (!isset($calendarStatus[$day])) {
                    $calendarStatus[$day] = ['active' => 0, 'change' => 0, 'warning' => 0, 'total' => 0];
                }
                $calendarStatus[$day]['total']++;
                $calendarStatus[$day][$status]++;
            }

            // Trend Next 6 Months
            if ($nextDate && $nextDate->gte($trendStart) && $nextDate->lte($trendEnd)) {
                $ym = $nextDate->format('M Y');
                $monthlySchedule[$ym] = ($monthlySchedule[$ym] ?? 0) + 1;
            }

            // Machine Counting (Kolom Number of Machine / machine_no & number_of)
            if (!empty($b->machine_no) && $b->machine_no !== '-') {
                $totalMachines++;
                $distinctMachinesSet[trim((string)$b->machine_no)] = true;
            }
            if (!empty($b->number_of)) {
                $cleanNumOf = preg_replace('/[^0-9]/', '', (string)$b->number_of);
                if (is_numeric($cleanNumOf) && (int)$cleanNumOf > 0) {
                    $totalNumberOfMachines += (int)$cleanNumOf;
                }
            }

            // Battery Type Counting (Alkali & Lithium berdasarkan SUM how_many)
            $bType = strtolower(trim((string)($b->battery_type ?? '')));
            if (str_contains($bType, 'alkali')) {
                $alkaliCount += $qty;
            } elseif (str_contains($bType, 'lithium')) {
                $lithiumCount += $qty;
            } elseif ($bType !== '' && $bType !== '-') {
                $otherTypeCount += $qty;
            }

            // Prepare JSON payload for interactive client-side modal filtering
            $batteriesData[] = [
                'id' => $b->id,
                'level' => $b->level ?: '-',
                'battery_id' => $b->battery_id ?: ('BAT-' . str_pad($b->id, 4, '0', STR_PAD_LEFT)),
                'area' => $b->area ?: '-',
                'area_key' => $areaKey,
                'line' => $b->line ?: '-',
                'machine_no' => $b->machine_no ?: '-',
                'machine_name' => $b->machine_name ?: '-',
                'maker' => $b->maker ?: '-',
                'equipment_type' => $b->equipment_type ?: '-',
                'device' => $b->device ?: '-',
                'battery_model' => $b->battery_model ?: '-',
                'battery_type' => $b->battery_type ?: '-',
                'std_volt' => $b->std_volt ?: '3.6V',
                'install_date' => $installDate ? $installDate->locale('id')->translatedFormat('d M Y') : '-',
                'replacement_cycle_month' => $b->replacement_cycle_month ? ($b->replacement_cycle_month . ' Bulan') : '-',
                'next_replace_date' => $nextDate ? $nextDate->locale('id')->translatedFormat('d M Y') : '-',
                'raw_next_replace_date' => $nextDate ? $nextDate->format('Y-m-d') : '',
                'how_many' => $qty,
                'has_how_many' => $hasHowMany ? 1 : 0,
                'aggregate' => $agg,
                'status' => $status,
                'status_label' => $statusLabel,
                'status_color' => $statusColor,
                'days_remaining' => $daysRemaining,
                'created_by' => $b->created_by ?: 'System',
            ];
        }

        // Calculate Ratios
        $healthRate = $totalBatteries > 0 ? round(($activeCount / $totalBatteries) * 100, 1) : 100;
        $changeRate = $totalBatteries > 0 ? round(($changeCount / $totalBatteries) * 100, 1) : 0;
        $warningRate = $totalBatteries > 0 ? round(($warningCount / $totalBatteries) * 100, 1) : 0;
        $availabilityRate = $totalBatteries > 0 ? round((($totalBatteries - $warningCount) / $totalBatteries) * 100, 1) : 100;
        $complianceRate = $totalBatteries > 0 ? round((($activeCount + $changeCount) / $totalBatteries) * 100, 1) : 100;
        $expiringSoonCount = $changeCount;
        $missingCount = $warningCount;

        // Machine stats & Battery type stats
        $distinctMachines = count($distinctMachinesSet);
        $totalKnownTypes = $alkaliCount + $lithiumCount + $otherTypeCount;
        $alkaliRate = $totalKnownTypes > 0 ? round(($alkaliCount / $totalKnownTypes) * 100, 1) : 0;
        $lithiumRate = $totalKnownTypes > 0 ? round(($lithiumCount / $totalKnownTypes) * 100, 1) : 0;

        // Top 5 Models for Total Battery chart
        arsort($modelCounts);
        $topModels = array_slice($modelCounts, 0, 5, true);
        $otherModelsCount = array_sum(array_slice($modelCounts, 5, null, true));
        if ($otherModelsCount > 0) {
            $topModels['Others'] = $otherModelsCount;
        }

        // Top 5 Models by SUM(how_many) for Jumlah Battery Donut Chart
        arsort($modelHowManyCounts);
        $topModelHowMany = array_slice($modelHowManyCounts, 0, 5, true);
        $otherHowManyCount = array_sum(array_slice($modelHowManyCounts, 5, null, true));
        if ($otherHowManyCount > 0) {
            $topModelHowMany['Others'] = $otherHowManyCount;
        }

        // Sort Models by qty (SUM how_many) for Model Status Bar Chart (dari terbesar ke terkecil)
        uasort($modelStats, fn($a, $b) => $b['qty'] <=> $a['qty']);
        $topModelStats = $modelStats;

        // Sort Lines by how_many (sum of How Many per line) (dari terbesar ke terkecil)
        uasort($lineStats, fn($a, $b) => $b['how_many'] <=> $a['how_many']);
        $allLineStats = $lineStats;
        $topLines = $lineStats;

        // Sort Devices by total count (dari terbesar ke terkecil)
        arsort($deviceCounts);

        // Days in current month for Calendar
        $currentMonthDays = $today->daysInMonth;
        $firstDayOfMonth = $today->copy()->startOfMonth()->dayOfWeek;
        $currentMonthName = $today->format('F Y');

        // Kalender Nasional Indonesia (Hari Libur Nasional)
        $indonesianHolidays = [
            // Libur Nasional Tetap (berlaku tiap tahun)
            '01-01' => 'Tahun Baru Masehi',
            '05-01' => 'Hari Buruh Internasional',
            '06-01' => 'Hari Lahir Pancasila',
            '08-17' => 'Hari Kemerdekaan RI (HUT RI)',
            '12-25' => 'Hari Raya Natal',

            // Libur Nasional 2026
            '2026-01-16' => 'Isra Mi\'raj Nabi Muhammad SAW',
            '2026-02-17' => 'Tahun Baru Imlek 2577',
            '2026-03-19' => 'Hari Suci Nyepi (Saka 1948)',
            '2026-03-20' => 'Hari Raya Idul Fitri 1447 H',
            '2026-03-21' => 'Hari Raya Idul Fitri 1447 H',
            '2026-04-03' => 'Wafat Yesus Kristus (Jumat Agung)',
            '2026-05-14' => 'Kenaikan Yesus Kristus',
            '2026-05-27' => 'Hari Raya Idul Adha 1447 H',
            '2026-05-31' => 'Hari Raya Waisak 2570 BE',
            '2026-06-16' => 'Tahun Baru Islam 1448 H',
            '2026-08-25' => 'Maulid Nabi Muhammad SAW',
        ];

        $namaBulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $currentMonthIndo = $namaBulanIndo[$today->month] . ' ' . $today->year;

        // Cari hari libur nasional di bulan berjalan
        $currentMonthHolidays = [];
        for ($d = 1; $d <= $currentMonthDays; $d++) {
            $dateStr = sprintf('%04d-%02d-%02d', $today->year, $today->month, $d);
            $monthDayStr = sprintf('%02d-%02d', $today->month, $d);
            if (isset($indonesianHolidays[$dateStr])) {
                $currentMonthHolidays[$d] = $indonesianHolidays[$dateStr];
            } elseif (isset($indonesianHolidays[$monthDayStr])) {
                $currentMonthHolidays[$d] = $indonesianHolidays[$monthDayStr];
            }
        }

        // Frequent Battery Replacements by Equipment Type on Each Machine
        // (diambil dari kolom exchange_type dan equipment_type pada sheet history battery / input_batteries)
        $exchangeQuery = \App\Models\InputBattery::query()
            ->whereNotNull('exchange_type')
            ->where('exchange_type', '!=', '')
            ->whereNotNull('equipment_type')
            ->where('equipment_type', '!=', '')
            ->where('equipment_type', '!=', '-');

        if ($selectedArea && $selectedArea !== 'ALL') {
            $exchangeQuery->where(function ($q) use ($selectedArea) {
                $q->where('area', $selectedArea)
                  ->orWhereIn('line', function ($sq) use ($selectedArea) {
                      $sq->select('line')->from('batteries')->where('area', $selectedArea)->whereNotNull('line');
                  });
            });
        }

        if ($selectedLine && $selectedLine !== 'ALL') {
            $exchangeQuery->where('line', $selectedLine);
        }

        $frequentExchangeList = $exchangeQuery
            ->selectRaw("
                line,
                machine_no,
                op_number,
                equipment_type,
                battery_model,
                area,
                COUNT(*) as total_exchange,
                COUNT(CASE WHEN exchange_type LIKE 'Replace on exchange%' THEN 1 END) as replace_count,
                COUNT(CASE WHEN exchange_type = 'Update on exchange' THEN 1 END) as update_count,
                MAX(last_day) as last_exchange_date
            ")
            ->groupBy('line', 'machine_no', 'op_number', 'equipment_type', 'battery_model', 'area')
            ->orderByDesc('total_exchange')
            ->get();

        $topFrequentMachines = $frequentExchangeList->take(10)->map(function ($m) {
            $mName = !empty($m->machine_no) && $m->machine_no !== '-' ? $m->machine_no : (!empty($m->op_number) ? $m->op_number : 'Unknown');
            $op = !empty($m->op_number) && $m->op_number !== '-' ? $m->op_number : '';
            $eq = trim((string)$m->equipment_type);
            return [
                'equipment_type' => $eq,
                'machine_name' => $mName,
                'op_number' => $op,
                'battery_model' => $m->battery_model ?: '-',
                'line' => $m->line ?: '-',
                'area' => $m->area ?: '-',
                'label' => "{$eq} ({$mName})",
                'short_label' => "{$eq} ({$mName})",
                'total' => (int)$m->total_exchange,
                'replace_count' => (int)$m->replace_count,
                'update_count' => (int)$m->update_count,
                'last_date' => $m->last_exchange_date ? Carbon::parse($m->last_exchange_date)->locale('id')->translatedFormat('d M Y') : '-',
            ];
        })->values();

        $allFrequentMachines = $frequentExchangeList->map(function ($m, $idx) {
            $mName = !empty($m->machine_no) && $m->machine_no !== '-' ? $m->machine_no : (!empty($m->op_number) ? $m->op_number : 'Unknown');
            $op = !empty($m->op_number) && $m->op_number !== '-' ? $m->op_number : '';
            $eq = trim((string)$m->equipment_type);
            return [
                'rank' => $idx + 1,
                'equipment_type' => $eq,
                'machine_name' => $mName,
                'op_number' => $op,
                'battery_model' => $m->battery_model ?: '-',
                'line' => $m->line ?: '-',
                'area' => $m->area ?: '-',
                'total' => (int)$m->total_exchange,
                'replace_count' => (int)$m->replace_count,
                'update_count' => (int)$m->update_count,
                'last_date' => $m->last_exchange_date ? Carbon::parse($m->last_exchange_date)->locale('id')->translatedFormat('d M Y') : '-',
            ];
        })->values();

        return view('content.main-dashboard.battery', compact(
            'totalBatteries',
            'totalBatteryModels',
            'totalBatteryQuantity',
            'totalDeviceCount',
            'activeCount',
            'changeCount',
            'warningCount',
            'expiringSoonCount',
            'missingCount',
            'healthRate',
            'warningRate',
            'changeRate',
            'availabilityRate',
            'complianceRate',
            'selectedArea',
            'selectedLine',
            'availableAreas',
            'availableLines',
            'topModels',
            'topModelHowMany',
            'modelHowManyCounts',
            'topModelStats',
            'modelStats',
            'topFrequentMachines',
            'allFrequentMachines',
            'topLines',
            'allLineStats',
            'deviceCounts',
            'calendarDue',
            'calendarStatus',
            'monthlySchedule',
            'currentMonthDays',
            'firstDayOfMonth',
            'currentMonthName',
            'currentMonthIndo',
            'currentMonthHolidays',
            'batteriesData',
            'areaMachiningStats',
            'areaShaftStats',
            'areaEngineStats',
            'totalMachines',
            'distinctMachines',
            'totalNumberOfMachines',
            'alkaliCount',
            'lithiumCount',
            'alkaliRate',
            'lithiumRate'
        ));
    }
}

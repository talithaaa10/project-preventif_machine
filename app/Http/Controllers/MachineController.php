<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\listMachinesImport;

class MachineController extends Controller
{
    public function index()
    {
        $machines = Machine::all();
        return view('content.machines.index', compact('machines'));
    }

    public function showImportForm()
    {
        $machines = Machine::all();
        return view('content.machines.index', compact('machines'));
    }

    public function dashboardMachine()
    {
        $totalMachine = Machine::count();

        // Data Grafik Per Line
        $machineByLine = Machine::selectRaw('line, count(*) as total')
                                ->whereNotNull('line')
                                ->groupBy('line')
                                ->orderBy('total', 'desc')
                                ->limit(8)
                                ->get();

        $machineByMaker = Machine::selectRaw('maker, count(*) as total')
                                 ->whereNotNull('maker')
                                 ->groupBy('maker')
                                 ->orderBy('total', 'desc')
                                 ->limit(5)
                                 ->get();

        return view('content.main-dashboard.machine', compact('totalMachine', 'machineByLine', 'machineByMaker'));
    }
    public function createMachine()
    {
        return view('content.machines.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'hmmi' => 'required',
            'op_no' => 'required',
            'line' => 'required',
        ]);

        Machine::create($request->all());

        return redirect()->route('machines.index')->with('success', 'Data mesin berhasil ditambahkan!');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new listMachinesImport, $request->file('file'));

        return redirect()->route('machines.index')->with('success', 'Data dari sheet List MC berhasil diimport!');
    }
}

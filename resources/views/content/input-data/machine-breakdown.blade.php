@extends('layouts.alpineLayout')

@section('title', 'Input Data Machine Breakdown - CMMS')
@section('page-title', 'Input & Kelola Machine Breakdown')

@section('content')
<div class="space-y-6 text-slate-100" x-data="{ manualModal: false, uploadModal: false }">

  <!-- 1. HEADER & ACTION CARD -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl">
    <div class="flex items-center gap-3">
      <div class="p-2.5 rounded-xl bg-blue-500/10 border border-blue-500/20 text-blue-400">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
      </div>
      <div>
        <h2 class="text-lg font-bold text-white tracking-wide">Input & Data Machine Breakdown</h2>
        <p class="text-xs text-slate-400 mt-0.5">
          Kelola data Line Stop, Machine Breakdown, MTTR, MTBF &bull; Total: <span class="text-blue-400 font-bold font-mono">{{ $records->total() }} Data</span>
        </p>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center flex-wrap gap-2">
      <!-- Tombol Tambah Manual -->
      @hasPermission('breakdown_create')
      <button 
        type="button" 
        @click="manualModal = true"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold transition shadow-lg shadow-blue-600/30"
      >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Manual
      </button>
      @endhasPermission

      <!-- Tombol Upload Excel -->
      @hasPermission('breakdown_import')
      <button 
        type="button" 
        @click="uploadModal = true"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition border border-slate-700"
      >
        <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
        </svg>
        Upload Excel / CSV
      </button>
      @endhasPermission

      <!-- Shortcut Dashboard -->
      <a 
        href="{{ route('machine-breakdown.index') }}" 
        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-400 hover:text-white text-xs font-medium transition border border-slate-800"
      >
        <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
        Lihat Dashboard
      </a>
    </div>
  </div>

  <!-- 2. FLASH NOTIFICATIONS -->
  @if (session('success'))
    <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs flex items-center gap-2.5 shadow-lg">
      <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <span class="font-medium">{{ session('success') }}</span>
    </div>
  @endif

  @if ($errors->any())
    <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs shadow-lg">
      <div class="font-bold mb-1">Terdapat kesalahan:</div>
      <ul class="list-disc list-inside space-y-0.5">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <!-- 3. FILTER CARD -->
  <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl">
    <form method="GET" action="{{ route('input.machine-breakdown') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 items-end">
      
      <!-- Filter Line -->
      <div>
        <label class="block text-[11px] font-semibold text-slate-400 mb-1">Filter Line</label>
        <select name="line" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white outline-none focus:ring-1 focus:ring-blue-500">
          <option value="ALL">Semua Line ({{ count($allLines) }})</option>
          @foreach ($allLines as $ln)
            <option value="{{ $ln }}" {{ request('line') === $ln ? 'selected' : '' }}>{{ $ln }}</option>
          @endforeach
        </select>
      </div>

      <!-- Filter KPI -->
      <div>
        <label class="block text-[11px] font-semibold text-slate-400 mb-1">Filter KPI</label>
        <select name="kpi" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white outline-none focus:ring-1 focus:ring-blue-500">
          <option value="ALL">Semua KPI</option>
          @foreach ($allKpis as $kpi)
            <option value="{{ $kpi }}" {{ request('kpi') === $kpi ? 'selected' : '' }}>{{ $kpi }}</option>
          @endforeach
        </select>
      </div>

      <!-- Filter Status -->
      <div>
        <label class="block text-[11px] font-semibold text-slate-400 mb-1">Filter Status</label>
        <select name="status" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white outline-none focus:ring-1 focus:ring-blue-500">
          <option value="ALL">Semua Status</option>
          @foreach ($statuses as $st)
            <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
          @endforeach
        </select>
      </div>

      <!-- Filter Tahun -->
      <div>
        <label class="block text-[11px] font-semibold text-slate-400 mb-1">Tahun</label>
        <select name="year" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white outline-none focus:ring-1 focus:ring-blue-500">
          <option value="ALL">Semua Tahun</option>
          @foreach ($years as $yr)
            <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>{{ $yr }}</option>
          @endforeach
        </select>
      </div>

      <!-- Submit & Reset -->
      <div class="flex items-center gap-2">
        <button type="submit" class="flex-1 py-2 px-3 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl transition shadow">
          Filter
        </button>
        <a href="{{ route('input.machine-breakdown') }}" class="py-2 px-3 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl transition border border-slate-700">
          Reset
        </a>
      </div>

    </form>
  </div>

  <!-- 4. DATA TABLE CARD -->
  <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
    <div class="p-4 border-b border-slate-800 bg-slate-950/40 flex items-center justify-between text-xs">
      <div class="flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-blue-400"></span>
        <h3 class="font-semibold text-white">Daftar Rekap Machine Breakdown</h3>
      </div>
      <span class="font-mono text-slate-400">
        Halaman {{ $records->currentPage() }} dari {{ $records->lastPage() }}
      </span>
    </div>

    <div class="overflow-x-auto custom-scrollbar">
      <table class="w-full text-left text-xs border-collapse">
        <thead>
          <tr class="border-b border-slate-800 text-[11px] font-mono text-slate-400 uppercase bg-slate-950/60 whitespace-nowrap">
            <th class="py-3 px-3.5 text-center">#</th>
            <th class="py-3 px-3.5">Tanggal</th>
            <th class="py-3 px-3.5">Line</th>
            <th class="py-3 px-3.5">KPI</th>
            <th class="py-3 px-3.5">Status</th>
            <th class="py-3 px-3.5 text-right">Duration (mnt)</th>
            <th class="py-3 px-3.5 text-right">Freq (kali)</th>
            <th class="py-3 px-3.5 text-right">% (Persen)</th>
            <th class="py-3 px-3.5 text-right">Target</th>
            <th class="py-3 px-3.5">Category</th>
            <th class="py-3 px-3.5 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-800/60 font-sans whitespace-nowrap">
          @forelse ($records as $item)
            <tr class="hover:bg-slate-800/40 transition-colors">
              <td class="py-2.5 px-3.5 text-center font-mono text-slate-500">{{ $item->id }}</td>
              <td class="py-2.5 px-3.5 font-mono text-slate-300">
                {{ optional($item->date)->format('M Y') ?: '-' }}
              </td>
              <td class="py-2.5 px-3.5">
                <span class="px-2 py-0.5 rounded bg-slate-800 text-blue-300 border border-slate-700 font-mono text-[11px] font-bold">
                  {{ $item->line ?: '-' }}
                </span>
              </td>
              <td class="py-2.5 px-3.5 font-medium text-white">
                <span class="text-cyan-300">{{ $item->kpi ?: '-' }}</span>
                @if($item->sub_kp)
                  <span class="text-[10px] text-slate-500 block font-mono">{{ $item->sub_kp }}</span>
                @endif
              </td>
              <td class="py-2.5 px-3.5">
                @php
                  $st = strtolower(trim($item->status ?? ''));
                @endphp
                @if($st === 'result')
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">RESULT</span>
                @elseif($st === 'target')
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-amber-500/10 text-amber-400 border border-amber-500/30">TARGET</span>
                @else
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-mono bg-slate-800 text-slate-400 border border-slate-700">{{ strtoupper($item->status ?: 'N/A') }}</span>
                @endif
              </td>
              <td class="py-2.5 px-3.5 text-right font-mono text-slate-300">
                {{ $item->duration !== null ? number_format($item->duration, 2, ',', '.') : '-' }}
              </td>
              <td class="py-2.5 px-3.5 text-right font-mono text-slate-300">
                {{ $item->frequency !== null ? number_format($item->frequency, 0, ',', '.') : '-' }}
              </td>
              <td class="py-2.5 px-3.5 text-right font-mono font-bold text-amber-400">
                {{ $item->persen !== null ? number_format($item->persen, 4, ',', '.') . '%' : '-' }}
              </td>
              <td class="py-2.5 px-3.5 text-right font-mono text-slate-400">
                {{ $item->target !== null ? number_format($item->target, 2, ',', '.') : '-' }}
              </td>
              <td class="py-2.5 px-3.5 text-slate-400 font-mono text-[11px]">
                {{ $item->cat ?: '-' }}
              </td>
              <td class="py-2.5 px-3.5 text-center">
                @hasPermission('breakdown_delete')
                <form action="{{ route('input.machine-breakdown.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');" class="inline-block">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="p-1 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 transition" title="Hapus Data">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </form>
                @endhasPermission
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="11" class="py-12 text-center text-slate-500 font-mono text-xs">
                Belum ada data machine breakdown terdaftar. Silakan upload file Excel atau tambah secara manual.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Table Footer with Pagination -->
    <div class="p-4 border-t border-slate-800 bg-slate-950/40 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
      <div>
        Menampilkan data <strong class="text-white">{{ $records->firstItem() ?? 0 }}</strong> - <strong class="text-white">{{ $records->lastItem() ?? 0 }}</strong> dari <strong class="text-blue-400">{{ $records->total() }}</strong> total data
      </div>

      <div class="flex items-center gap-2">
        @if ($records->onFirstPage())
          <span class="px-3 py-1.5 rounded-lg bg-slate-800/50 text-slate-600 cursor-not-allowed font-mono text-xs">&larr; Prev</span>
        @else
          <a href="{{ $records->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white font-mono text-xs transition-colors">&larr; Prev</a>
        @endif

        <span class="font-mono text-xs text-slate-300 px-2">
          Hal <span class="text-white font-bold">{{ $records->currentPage() }}</span> / {{ $records->lastPage() }}
        </span>

        @if ($records->hasMorePages())
          <a href="{{ $records->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white font-mono text-xs transition-colors">Next &rarr;</a>
        @else
          <span class="px-3 py-1.5 rounded-lg bg-slate-800/50 text-slate-600 cursor-not-allowed font-mono text-xs">Next &rarr;</span>
        @endif
      </div>
    </div>
  </div>

  <!-- 5. MODAL FORM INPUT MANUAL -->
  <div 
    x-show="manualModal" 
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 overflow-y-auto" 
    style="display: none;"
  >
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity" @click="manualModal = false"></div>

    <div class="relative min-h-screen flex items-center justify-center p-4">
      <div class="relative w-full max-w-2xl bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl overflow-hidden p-6 sm:p-8">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-800">
          <div class="flex items-center gap-3">
            <div class="p-2 rounded-xl bg-blue-500/10 text-blue-400 border border-blue-500/20">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
            </div>
            <div>
              <h3 class="text-base font-bold text-white tracking-wide">Input Data Machine Breakdown Manual</h3>
              <p class="text-xs text-slate-400">Tambah data performa mesin satuan langsung ke database</p>
            </div>
          </div>
          <button @click="manualModal = false" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Form -->
        <form action="{{ route('input.machine-breakdown.store') }}" method="POST" class="space-y-4">
          @csrf

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            
            <!-- Tanggal -->
            <div>
              <label class="block text-[11px] font-semibold text-slate-300 mb-1">Tanggal / Bulan <span class="text-rose-400">*</span></label>
              <input type="date" name="date" required value="{{ date('Y-m-01') }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white outline-none focus:ring-1 focus:ring-blue-500">
            </div>

            <!-- Line -->
            <div>
              <label class="block text-[11px] font-semibold text-slate-300 mb-1">Line Mesin <span class="text-rose-400">*</span></label>
              <input type="text" name="line" required list="lineList" placeholder="Pilih atau ketik nama Line" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white outline-none focus:ring-1 focus:ring-blue-500">
              <datalist id="lineList">
                @foreach ($allLines as $ln)
                  <option value="{{ $ln }}"></option>
                @endforeach
              </datalist>
            </div>

            <!-- KPI -->
            <div>
              <label class="block text-[11px] font-semibold text-slate-300 mb-1">KPI <span class="text-rose-400">*</span></label>
              <select name="kpi" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white outline-none focus:ring-1 focus:ring-blue-500">
                <option value="Machine Breakdown">Machine Breakdown</option>
                <option value="Linestop">Linestop</option>
                <option value="MTTR">MTTR</option>
                <option value="MTBF">MTBF</option>
                <option value="Working Hours">Working Hours</option>
              </select>
            </div>

            <!-- Status -->
            <div>
              <label class="block text-[11px] font-semibold text-slate-300 mb-1">Status <span class="text-rose-400">*</span></label>
              <select name="status" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white outline-none focus:ring-1 focus:ring-blue-500">
                <option value="Result">Result (Aktual)</option>
                <option value="Target">Target</option>
              </select>
            </div>

            <!-- Duration -->
            <div>
              <label class="block text-[11px] font-semibold text-slate-300 mb-1">Duration (Menit)</label>
              <input type="number" step="0.01" name="duration" placeholder="Contoh: 45.50" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white outline-none focus:ring-1 focus:ring-blue-500">
            </div>

            <!-- Frequency -->
            <div>
              <label class="block text-[11px] font-semibold text-slate-300 mb-1">Frequency (Frekuensi / Kali)</label>
              <input type="number" step="1" name="frequency" placeholder="Contoh: 3" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white outline-none focus:ring-1 focus:ring-blue-500">
            </div>

            <!-- Persen (%) -->
            <div>
              <label class="block text-[11px] font-semibold text-slate-300 mb-1">Persen (%)</label>
              <input type="number" step="0.0001" name="persen" placeholder="Contoh: 0.0250" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white outline-none focus:ring-1 focus:ring-blue-500">
            </div>

            <!-- Target -->
            <div>
              <label class="block text-[11px] font-semibold text-slate-300 mb-1">Target</label>
              <input type="number" step="0.01" name="target" placeholder="Contoh: 1.5 atau 12" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white outline-none focus:ring-1 focus:ring-blue-500">
            </div>

            <!-- Category -->
            <div>
              <label class="block text-[11px] font-semibold text-slate-300 mb-1">Category (Opsional)</label>
              <input type="text" name="cat" value="Performance" placeholder="Contoh: Performance" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white outline-none focus:ring-1 focus:ring-blue-500">
            </div>

            <!-- Sub-KPI -->
            <div>
              <label class="block text-[11px] font-semibold text-slate-300 mb-1">Sub-KPI (Opsional)</label>
              <input type="text" name="sub_kp" placeholder="Contoh: Sub proses" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white outline-none focus:ring-1 focus:ring-blue-500">
            </div>

          </div>

          <!-- Buttons -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800 mt-6">
            <button 
              type="button" 
              @click="manualModal = false" 
              class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition border border-slate-700"
            >
              Batal
            </button>
            <button 
              type="submit" 
              class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold transition shadow-lg shadow-blue-600/30 flex items-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              Simpan Data
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>

  <!-- 6. MODAL UPLOAD EXCEL / CSV -->
  <div 
    x-show="uploadModal" 
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 overflow-y-auto" 
    style="display: none;"
  >
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity" @click="uploadModal = false"></div>

    <div class="relative min-h-screen flex items-center justify-center p-4">
      <div class="relative w-full max-w-lg bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl overflow-hidden p-6 sm:p-8">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-800">
          <div class="flex items-center gap-3">
            <div class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
              </svg>
            </div>
            <div>
              <h3 class="text-base font-bold text-white tracking-wide">Upload Data Machine Breakdown</h3>
              <p class="text-xs text-slate-400">Import berkas Excel (.xlsx, .xls) atau .csv</p>
            </div>
          </div>
          <button @click="uploadModal = false" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Form Upload -->
        <form action="{{ route('machine-breakdown.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
          @csrf
          <div>
            <label for="mb_file" class="block text-xs font-semibold text-slate-300 mb-2">
              Pilih Berkas Excel / CSV
            </label>
            <input 
              type="file" 
              id="mb_file" 
              name="file" 
              required
              accept=".xlsx,.xls,.csv"
              class="block w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-500 bg-slate-950 border border-slate-800 rounded-xl cursor-pointer p-1"
            >
            <p class="text-[11px] text-slate-500 mt-2">
              Format header kolom yang didukung: <code>Cat, Date, Status, KPI, Sub-KPI, Line, Duration, Freq, %, Target</code>.
            </p>
          </div>

          <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800 mt-6">
            <button 
              type="button" 
              @click="uploadModal = false" 
              class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition border border-slate-700"
            >
              Batal
            </button>
            <button 
              type="submit" 
              class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition shadow-lg shadow-emerald-600/30 flex items-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
              </svg>
              Mulai Import
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>

</div>
@endsection

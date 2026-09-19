@extends('layouts.alpineLayout')

@section('title', 'Master Data Mesin - CMMS')
@section('page-title', 'Master Data Mesin')

@section('content')
<div 
  x-data="machineTable()" 
  class="space-y-6 text-slate-100"
>

  <!-- 1. HEADER & ACTION BAR -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl">
    <div>
      <div class="flex items-center gap-2.5">
        <div class="p-2 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
          </svg>
        </div>
        <div>
          <h2 class="text-lg font-bold text-white tracking-wide">Master Data Mesin (Sheet List MC)</h2>
          <p class="text-xs text-slate-400 mt-0.5">
            Daftar inventaris mesin terdaftar &bull; Total: <span class="text-amber-400 font-bold font-mono">{{ $machines->count() }} Mesin</span>
          </p>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center flex-wrap gap-2.5">
      <!-- Search input -->
      <div class="relative w-full sm:w-64">
        <input 
          type="text" 
          x-model="search"
          @input="currentPage = 1"
          placeholder="Cari HMMI, Line, Maker..."
          class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-9 pr-3 py-2 text-xs text-white placeholder-slate-500 outline-none focus:ring-1 focus:ring-amber-500"
        >
        <svg class="w-4 h-4 text-slate-500 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>

      <!-- Tambah Mesin Link -->
      @hasPermission('machine_create')
      <a 
        href="{{ route('input.machine') }}" 
        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-medium text-xs transition border border-slate-700"
      >
        <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Manual
      </a>
      @endhasPermission

      @hasPermission('machine_import')
      <!-- Import Excel Modal Trigger -->
      <button 
        @click="importModalOpen = true"
        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs transition shadow-lg shadow-amber-600/30"
      >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
        </svg>
        Import Excel
      </button>
      @endhasPermission
    </div>
  </div>

  <!-- 2. FLASH NOTIFICATIONS -->
  @if (session('success'))
    <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs flex items-center justify-between shadow-lg">
      <div class="flex items-center gap-2.5">
        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="font-medium">{{ session('success') }}</span>
      </div>
    </div>
  @endif

  @if ($errors->any())
    <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs shadow-lg">
      <div class="font-bold mb-1">Terdapat kesalahan input:</div>
      <ul class="list-disc list-inside space-y-0.5">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <!-- 3. MACHINE DATA TABLE CARD -->
  <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
    <div class="p-4 border-b border-slate-800 bg-slate-950/40 flex items-center justify-between text-xs">
      <div class="flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
        <span class="font-semibold text-white">Daftar Mesin Terdaftar</span>
      </div>
      <span class="font-mono text-slate-400">
        <span x-text="filteredMachines.length"></span> Unit Ditemukan
      </span>
    </div>

    <div class="overflow-x-auto custom-scrollbar">
      <table class="w-full text-left text-xs border-collapse">
        <thead>
          <tr class="border-b border-slate-800 text-[11px] font-mono text-slate-400 uppercase bg-slate-950/60">
            <th class="py-3 px-4 w-12 text-center">NO</th>
            <th class="py-3 px-4">HMMI</th>
            <th class="py-3 px-4">OP NO</th>
            <th class="py-3 px-4">LINE</th>
            <th class="py-3 px-4">PRODUCT</th>
            <th class="py-3 px-4">MAKER</th>
            <th class="py-3 px-4">MODEL / TYPE</th>
            <th class="py-3 px-4">SERIAL NO</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-800/60 font-sans">
          <template x-for="(mc, idx) in paginatedMachines" :key="mc.id || idx">
            <tr class="hover:bg-slate-800/40 transition-colors">
              <td class="py-3 px-4 text-center font-mono text-slate-500" x-text="((currentPage - 1) * perPage) + idx + 1"></td>
              <td class="py-3 px-4">
                <span class="font-bold text-amber-400 font-mono" x-text="mc.hmmi"></span>
              </td>
              <td class="py-3 px-4 font-mono text-slate-300" x-text="mc.op_no"></td>
              <td class="py-3 px-4">
                <span class="px-2 py-0.5 rounded-md bg-slate-800 border border-slate-700 font-mono text-[11px] text-white" x-text="mc.line"></span>
              </td>
              <td class="py-3 px-4 text-slate-300" x-text="mc.product || '-'"></td>
              <td class="py-3 px-4 text-slate-300 font-medium" x-text="mc.maker || '-'"></td>
              <td class="py-3 px-4 text-slate-300 font-mono" x-text="mc.model_type || '-'"></td>
              <td class="py-3 px-4 text-slate-400 font-mono" x-text="mc.serial_no || '-'"></td>
            </tr>
          </template>

          <template x-if="filteredMachines.length === 0">
            <tr>
              <td colspan="8" class="py-12 text-center text-slate-500 font-mono text-xs">
                Tidak ada data mesin yang cocok dengan kriteria pencarian.
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <!-- Table Footer with Pagination -->
    <div class="p-4 border-t border-slate-800 bg-slate-950/40 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
      <div>
        Menampilkan <strong class="text-white" x-text="filteredMachines.length === 0 ? 0 : ((currentPage - 1) * perPage) + 1"></strong> - <strong class="text-white" x-text="Math.min(currentPage * perPage, filteredMachines.length)"></strong> dari <strong class="text-amber-400" x-text="filteredMachines.length"></strong> mesin
      </div>

      <div class="flex items-center gap-2" x-show="totalPages > 1">
        <button 
          @click="prevPage()" 
          :disabled="currentPage === 1"
          class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white disabled:opacity-40 disabled:cursor-not-allowed font-mono text-xs transition-colors"
        >
          &larr; Prev
        </button>
        <span class="font-mono text-xs text-slate-300 px-2">
          Hal <span class="text-white font-bold" x-text="currentPage"></span> / <span x-text="totalPages"></span>
        </span>
        <button 
          @click="nextPage()" 
          :disabled="currentPage === totalPages"
          class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white disabled:opacity-40 disabled:cursor-not-allowed font-mono text-xs transition-colors"
        >
          Next &rarr;
        </button>
      </div>
    </div>
  </div>

  @hasPermission('machine_import')
  <!-- 4. MODAL POPUP FORM IMPORT EXCEL -->
  <div 
    x-show="importModalOpen" 
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
    style="display: none;"
  >
    <div 
      @click.away="importModalOpen = false"
      class="bg-slate-900 border border-slate-700/80 rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden"
    >
      <div class="p-4 border-b border-slate-800 flex items-center justify-between bg-slate-950/60">
        <div class="flex items-center gap-2.5">
          <div class="w-2.5 h-2.5 rounded-full bg-amber-400"></div>
          <h3 class="text-sm font-bold text-white tracking-wide">Import Data List Machine</h3>
        </div>
        <button 
          @click="importModalOpen = false"
          class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
        >
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <form action="{{ url('/import-machines') }}" method="POST" enctype="multipart/form-data" class="p-5 space-y-4">
        @csrf
        <div>
          <label for="fileExcel" class="block text-xs font-semibold text-slate-300 mb-1.5">
            Pilih File Excel (.xlsx, .xls, .csv)
          </label>
          <input 
            type="file" 
            id="fileExcel" 
            name="file" 
            class="block w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-600 file:text-white hover:file:bg-amber-500 bg-slate-950 border border-slate-800 rounded-xl cursor-pointer p-1" 
            accept=".xlsx, .xls, .csv" 
            required 
          />
          <p class="text-[11px] text-slate-400 mt-2 flex items-center gap-1">
            <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Sistem membaca khusus sheet bernama <strong class="text-white font-mono">'List MC'</strong>.
          </p>
        </div>

        <div class="pt-3 border-t border-slate-800 flex items-center justify-end gap-2.5">
          <button 
            type="button" 
            @click="importModalOpen = false"
            class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition"
          >
            Batal
          </button>
          <button 
            type="submit" 
            class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold transition shadow-lg shadow-amber-600/30"
          >
            Upload & Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
  @endhasPermission

</div>

<script>
function machineTable() {
  return {
    search: '',
    currentPage: 1,
    perPage: 25,
    importModalOpen: false,
    rawMachines: Object.freeze(@json($machines)),

    get filteredMachines() {
      if (!this.search.trim()) {
        return this.rawMachines;
      }
      const q = this.search.toLowerCase();
      return this.rawMachines.filter(m => 
        (m.hmmi && m.hmmi.toLowerCase().includes(q)) ||
        (m.op_no && m.op_no.toLowerCase().includes(q)) ||
        (m.line && m.line.toLowerCase().includes(q)) ||
        (m.product && m.product.toLowerCase().includes(q)) ||
        (m.maker && m.maker.toLowerCase().includes(q)) ||
        (m.model_type && m.model_type.toLowerCase().includes(q)) ||
        (m.serial_no && m.serial_no.toLowerCase().includes(q))
      );
    },

    get totalPages() {
      return Math.ceil(this.filteredMachines.length / this.perPage) || 1;
    },

    get paginatedMachines() {
      const start = (this.currentPage - 1) * this.perPage;
      return this.filteredMachines.slice(start, start + this.perPage);
    },

    nextPage() {
      if (this.currentPage < this.totalPages) this.currentPage++;
    },

    prevPage() {
      if (this.currentPage > 1) this.currentPage--;
    }
  };
}
</script>
@endsection

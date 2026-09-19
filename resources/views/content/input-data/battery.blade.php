@extends('layouts.alpineLayout')

@section('title', 'Input Data Battery - CMMS')
@section('page-title', 'Input Data Battery')

@section('content')
@php
  $userRole = strtolower(Auth::user()->role ?? session('user_role') ?? '');
  $isAdmin = ($userRole === 'admin');
@endphp
  <style>
    input[type="date"] {
      color-scheme: dark;
    }
    input[type="date"]::-webkit-calendar-picker-indicator {
      cursor: pointer;
      filter: invert(0.85);
      opacity: 0.85;
      transition: all 0.2s ease;
    }
    input[type="date"]::-webkit-calendar-picker-indicator:hover {
      filter: invert(1);
      opacity: 1;
      transform: scale(1.1);
    }
  </style>

  <div class="space-y-6 text-slate-100" x-data="batteryInputApp()">

    <!-- 1. HEADER & ACTION BAR -->
    <div
      class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-5 sm:p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl">
      <div class="flex items-center gap-3.5">
        <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400">
          <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
        </div>
        <div>
          <h2 class="text-xl font-bold text-white tracking-wide flex items-center gap-2.5">
            Input & Monitoring Penggantian Battery
          </h2>
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-2.5">
        <!-- Master Data Battery Shortcut -->
        <a href="{{ route('master-data.battery') }}"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-cyan-300 hover:text-white text-sm font-semibold transition border border-slate-700 shadow">
          <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
          </svg>
          Master Data Battery
        </a>

        <!-- Toggle Upload Excel -->
        @hasPermission('battery_import')
        <button type="button" @click="showUpload = !showUpload"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold transition border border-slate-700 shadow">
          <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
          </svg>
          <span x-text="showUpload ? 'Tutup Import' : 'Import Excel'"></span>
        </button>
        @endhasPermission

        <!-- Shortcut to Battery Dashboard -->
        <a href="{{ route('main-dashboard.battery') }}"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-sm font-semibold transition border border-slate-700 shadow">
          <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
          Dashboard Monitoring
        </a>
      </div>
    </div>

    <!-- 2. FLASH NOTIFICATIONS -->
    @if (session('success'))
      <div
        class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center gap-3 shadow-lg">
        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="font-semibold">{{ session('success') }}</span>
      </div>
    @endif

    @if ($errors->any())
      <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm shadow-lg">
        <div class="font-bold mb-1.5 flex items-center gap-2">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Terdapat kesalahan input:
        </div>
        <ul class="list-disc list-inside space-y-1 ml-1 text-xs sm:text-sm">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <!-- 3. COLLAPSIBLE UPLOAD EXCEL CARD -->
    <div x-show="showUpload" x-transition class="p-5 rounded-2xl bg-slate-900/90 border border-slate-800 shadow-xl"
      style="display: none;">
      <div class="flex items-center gap-2.5 pb-3 mb-4 border-b border-slate-800">
        <svg class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
        </svg>
        <div>
          <h3 class="text-base font-bold text-white">Import File Excel Battery</h3>
          <p class="text-xs text-slate-400 mt-0.5">
            Sistem membaca Sheet 1 (Master Battery Asset) dan Sheet 2 (Input Data Battery).
          </p>
        </div>
      </div>

      <form action="{{ route('input.battery.import') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
          <div class="md:col-span-3">
            <label for="file" class="block text-sm font-semibold text-slate-300 mb-2">
              Pilih File Excel (.xlsx, .xls, .csv)
            </label>
            <input type="file" id="file" name="file"
              class="block w-full text-sm text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-amber-600 file:text-white hover:file:bg-amber-500 bg-slate-950 border border-slate-800 rounded-xl cursor-pointer p-1.5"
              accept=".xlsx,.xls,.csv" required>
          </div>
          <div class="md:col-span-1">
            <button type="submit"
              class="w-full py-3 px-4 bg-amber-600 hover:bg-amber-500 text-white text-sm font-bold rounded-xl transition shadow-lg shadow-amber-600/30 flex items-center justify-center gap-2">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
              </svg>
              Upload Sekarang
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- 4. MAIN FORM: SMART CASCADING INPUT BATTERY (TANPA NOMOR & TULISAN LEBIH BESAR & JELAS) -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-visible">

      <!-- Form Header -->
      <div
        class="p-5 sm:p-6 border-b border-slate-800 bg-slate-950/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3.5">
          <div class="p-2.5 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
          </div>
          <div>
            <h3 class="text-base sm:text-lg font-bold text-white tracking-wide flex items-center gap-2.5">
              <span>Form Input Penggantian Battery</span>
            </h3>
            <div class="flex flex-wrap items-center gap-2 mt-1">
              <span class="text-xs text-slate-400">Pencatatan riwayat penggantian baterai mesin</span>
              <span class="text-slate-600 hidden sm:inline">&bull;</span>
              <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-cyan-500/10 border border-cyan-500/30 text-cyan-400 font-mono text-[11px] font-semibold">
                <svg class="w-3 h-3 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>Input By: <strong>{{ Auth::user()->name ?? (session('user_name') ?: 'Admin') }}</strong></span>
              </span>
            </div>
          </div>
        </div>

        <!-- Quick Reset Button -->
        <button type="button" @click="resetForm()"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-sm font-semibold transition border border-slate-700 self-start sm:self-auto cursor-pointer">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Reset Form
        </button>
      </div>

      <!-- Form Body -->
      <form id="batteryInputForm" x-ref="batteryInputForm" action="{{ route('input.input-battery.store') }}" method="POST" class="p-6 sm:p-8 space-y-6">
        @csrf
        <input type="hidden" name="created_by" value="{{ Auth::user()->name ?? (session('user_name') ?: 'Admin') }}">

        <!-- Hidden inputs for form submission -->
        <input type="hidden" name="area" :value="selectedArea" required>
        <input type="hidden" name="line" :value="selectedLine" required>
        <input type="hidden" name="op" :value="selectedOp" required>
        <input type="hidden" name="machine_no" :value="selectedMachine">
        <input type="hidden" name="equipment_type" :value="selectedEquipment" required>
        <input type="hidden" name="battery_model" :value="selectedModel" required>

        <!-- BARIS 1: AREA & LINE SATU BARIS -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

          <!-- Area Custom Dropdown (KEBAWAH) -->
          <div class="relative" x-data="{ open: false }">
            <label class="block text-sm font-bold text-slate-200 mb-2 flex items-center justify-between">
              <span>Area <span class="text-rose-400">*</span></span>
              <span class="text-xs font-mono text-cyan-400 font-semibold" x-show="selectedArea"
                x-text="'Terpilih: ' + selectedArea"></span>
            </label>
            <button type="button" @click="open = !open; closeOthers('area')"
              class="w-full flex items-center justify-between bg-slate-950 border border-slate-800 hover:border-slate-700 focus:border-cyan-500 rounded-xl px-4 py-3 text-sm text-left outline-none transition shadow-inner font-medium cursor-pointer">
              <span :class="selectedArea ? 'text-white font-bold' : 'text-slate-500'"
                x-text="selectedArea || '-- Pilih Area --'"></span>
              <svg class="w-5 h-5 text-slate-400 transition-transform" :class="open ? 'rotate-180 text-cyan-400' : ''"
                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <!-- Dropdown List Selalu Kebawah -->
            <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100"
              x-transition:enter-start="opacity-0 translate-y-[-4px]" x-transition:enter-end="opacity-100 translate-y-0"
              class="absolute top-full left-0 right-0 mt-1.5 max-h-64 overflow-y-auto custom-scrollbar bg-slate-950 border border-slate-700 rounded-xl shadow-2xl z-50 py-1 divide-y divide-slate-800/50"
              style="display: none;">
              <template x-for="areaName in Object.keys(tree).sort()" :key="areaName">
                <div @click="selectedArea = areaName; onAreaChange(); open = false"
                  class="px-4 py-2.5 hover:bg-slate-800 text-sm cursor-pointer transition flex items-center justify-between"
                  :class="selectedArea === areaName ? 'text-cyan-400 bg-slate-800/80 font-bold' : 'text-slate-200'">
                  <span x-text="areaName"></span>
                  <svg x-show="selectedArea === areaName" class="w-4 h-4 text-cyan-400" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
              </template>
            </div>
            <p class="text-xs text-slate-400 mt-1.5">Pilih area plant</p>
          </div>

          <!-- Line Custom Dropdown (KEBAWAH) -->
          <div class="relative" x-data="{ open: false }">
            <label class="block text-sm font-bold text-slate-200 mb-2 flex items-center justify-between">
              <span>Line <span class="text-rose-400">*</span></span>
              <span class="text-xs font-mono text-cyan-400 font-semibold" x-show="selectedLine"
                x-text="'Terpilih: ' + selectedLine"></span>
            </label>
            <button type="button"
              @click="if(selectedArea && availableLines.length > 0) { open = !open; closeOthers('line'); }"
              :disabled="!selectedArea || availableLines.length === 0"
              class="w-full flex items-center justify-between border border-slate-800 rounded-xl px-4 py-3 text-sm text-left outline-none transition shadow-inner font-medium"
              :class="(!selectedArea || availableLines.length === 0) ? 'opacity-50 cursor-not-allowed bg-slate-950/60' :
              'bg-slate-950 hover:border-slate-700 cursor-pointer focus:border-cyan-500'">
              <span :class="selectedLine ? 'text-white font-bold' : 'text-slate-500'"
                x-text="selectedLine || '-- Pilih Line --'"></span>
              <svg class="w-5 h-5 text-slate-400 transition-transform" :class="open ? 'rotate-180 text-cyan-400' : ''"
                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <!-- Dropdown List Selalu Kebawah -->
            <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100"
              x-transition:enter-start="opacity-0 translate-y-[-4px]" x-transition:enter-end="opacity-100 translate-y-0"
              class="absolute top-full left-0 right-0 mt-1.5 max-h-64 overflow-y-auto custom-scrollbar bg-slate-950 border border-slate-700 rounded-xl shadow-2xl z-50 py-1 divide-y divide-slate-800/50"
              style="display: none;">
              <template x-for="lineName in availableLines" :key="lineName">
                <div @click="selectedLine = lineName; onLineChange(); open = false"
                  class="px-4 py-2.5 hover:bg-slate-800 text-sm cursor-pointer transition flex items-center justify-between"
                  :class="selectedLine === lineName ? 'text-cyan-400 bg-slate-800/80 font-bold' : 'text-slate-200'">
                  <span x-text="lineName"></span>
                  <svg x-show="selectedLine === lineName" class="w-4 h-4 text-cyan-400" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
              </template>
            </div>
            <p class="text-xs text-slate-400 mt-1.5"
              x-text="!selectedArea ? 'Pilih Area terlebih dahulu' : (availableLines.length + ' Line tersedia')"></p>
          </div>

        </div>

        <!-- BARIS 2: OP NUMBER & MACHINE NO SATU BARIS -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

          <!-- OP Number Custom Dropdown (KEBAWAH dengan pencarian) -->
          <div class="relative" x-data="{ open: false, search: '' }">
            <label class="block text-sm font-bold text-slate-200 mb-2 flex items-center justify-between">
              <span>OP Number <span class="text-rose-400">*</span></span>
              <span class="text-xs font-mono text-cyan-400 font-semibold" x-show="selectedOp"
                x-text="'Terpilih: ' + selectedOp"></span>
            </label>
            <button type="button"
              @click="if(selectedLine && availableOps.length > 0) { open = !open; search = ''; closeOthers('op'); }"
              :disabled="!selectedLine || availableOps.length === 0"
              class="w-full flex items-center justify-between border border-slate-800 rounded-xl px-4 py-3 text-sm text-left outline-none transition shadow-inner font-mono font-medium"
              :class="(!selectedLine || availableOps.length === 0) ? 'opacity-50 cursor-not-allowed bg-slate-950/60' :
              'bg-slate-950 hover:border-slate-700 cursor-pointer focus:border-cyan-500'">
              <span :class="selectedOp ? 'text-amber-300 font-bold' : 'text-slate-500'"
                x-text="selectedOp || '-- Pilih OP Number --'"></span>
              <svg class="w-5 h-5 text-slate-400 transition-transform" :class="open ? 'rotate-180 text-cyan-400' : ''"
                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <!-- Dropdown List Selalu Kebawah -->
            <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100"
              x-transition:enter-start="opacity-0 translate-y-[-4px]" x-transition:enter-end="opacity-100 translate-y-0"
              class="absolute top-full left-0 right-0 mt-1.5 max-h-72 overflow-y-auto custom-scrollbar bg-slate-950 border border-slate-700 rounded-xl shadow-2xl z-50 py-1"
              style="display: none;">
              <!-- Search bar di dalam dropdown agar mudah memilih dari banyak OP -->
              <div class="p-2.5 border-b border-slate-800 sticky top-0 bg-slate-950 z-10">
                <input type="text" x-model="search" placeholder="Cari OP Number..." @click.stop
                  class="w-full bg-slate-900 border border-slate-800 rounded-lg px-3 py-2 text-sm text-white placeholder-slate-500 outline-none focus:border-cyan-500 font-mono">
              </div>
              <div class="divide-y divide-slate-800/50">
                <template
                  x-for="opName in availableOps.filter(o => !search || o.toLowerCase().includes(search.toLowerCase()))"
                  :key="opName">
                  <div @click="selectedOp = opName; onOpChange(); open = false"
                    class="px-4 py-2.5 hover:bg-slate-800 text-sm cursor-pointer transition flex items-center justify-between font-mono"
                    :class="selectedOp === opName ? 'text-amber-300 bg-slate-800/80 font-bold' : 'text-slate-200'">
                    <span x-text="opName"></span>
                    <svg x-show="selectedOp === opName" class="w-4 h-4 text-cyan-400" fill="none"
                      viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                  </div>
                </template>
              </div>
            </div>
            <p class="text-xs text-slate-400 mt-1.5"
              x-text="!selectedLine ? 'Pilih Line terlebih dahulu' : (availableOps.length + ' OP tersedia')"></p>
          </div>

          <!-- Machine No (Sebelah OP - Terhubung otomatis) -->
          <div class="relative" x-data="{ open: false }">
            <label class="block text-sm font-bold text-slate-200 mb-2 flex items-center justify-between">
              <span>Machine No <span class="text-cyan-400 text-xs font-normal">(Kode Mesin)</span></span>
              <span class="text-xs font-mono text-cyan-400 font-semibold" x-show="selectedMachine"
                x-text="selectedMachine"></span>
            </label>
            <button type="button"
              @click="if(selectedOp && availableMachines.length > 0) { open = !open; closeOthers('mc'); }"
              :disabled="!selectedOp || availableMachines.length === 0"
              class="w-full flex items-center justify-between border border-slate-800 rounded-xl px-4 py-3 text-sm text-left outline-none transition shadow-inner font-mono font-medium"
              :class="(!selectedOp || availableMachines.length === 0) ? 'opacity-50 cursor-not-allowed bg-slate-950/60' :
              'bg-slate-950 hover:border-slate-700 cursor-pointer focus:border-cyan-500'">
              <span :class="selectedMachine ? 'text-cyan-300 font-bold' : 'text-slate-500'"
                x-text="selectedMachine || '-- Auto / Pilih No Mesin --'"></span>
              <svg class="w-5 h-5 text-slate-400 transition-transform" :class="open ? 'rotate-180 text-cyan-400' : ''"
                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <!-- Dropdown List Selalu Kebawah -->
            <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100"
              x-transition:enter-start="opacity-0 translate-y-[-4px]" x-transition:enter-end="opacity-100 translate-y-0"
              class="absolute top-full left-0 right-0 mt-1.5 max-h-64 overflow-y-auto custom-scrollbar bg-slate-950 border border-slate-700 rounded-xl shadow-2xl z-50 py-1 divide-y divide-slate-800/50"
              style="display: none;">
              <template x-for="mcName in availableMachines" :key="mcName">
                <div @click="selectedMachine = mcName; onMachineChange(); open = false"
                  class="px-4 py-2.5 hover:bg-slate-800 text-sm cursor-pointer transition flex items-center justify-between font-mono"
                  :class="selectedMachine === mcName ? 'text-cyan-400 bg-slate-800/80 font-bold' : 'text-slate-200'">
                  <span x-text="mcName"></span>
                  <svg x-show="selectedMachine === mcName" class="w-4 h-4 text-cyan-400" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
              </template>
            </div>
            <p class="text-xs text-slate-400 mt-1.5"
              x-text="!selectedOp ? 'Pilih OP terlebih dahulu' : 'Otomatis sinkron dengan OP terpilih'"></p>
          </div>

        </div>

        <!-- BARIS 3: EQUIPMENT TYPE & BATTERY MODEL SATU BARIS -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

          <!-- Equipment Type Custom Dropdown (KEBAWAH) -->
          <div class="relative" x-data="{ open: false }">
            <label class="block text-sm font-bold text-slate-200 mb-2 flex items-center justify-between">
              <span>Equipment Type <span class="text-rose-400">*</span></span>
              <span class="text-xs font-mono text-cyan-400 font-semibold" x-show="selectedEquipment"
                x-text="'Terpilih: ' + selectedEquipment"></span>
            </label>
            <button type="button"
              @click="if(selectedOp && availableEquipment.length > 0) { open = !open; closeOthers('eq'); }"
              :disabled="!selectedOp || availableEquipment.length === 0"
              class="w-full flex items-center justify-between border border-slate-800 rounded-xl px-4 py-3 text-sm text-left outline-none transition shadow-inner font-medium"
              :class="(!selectedOp || availableEquipment.length === 0) ? 'opacity-50 cursor-not-allowed bg-slate-950/60' :
              'bg-slate-950 hover:border-slate-700 cursor-pointer focus:border-cyan-500'">
              <span :class="selectedEquipment ? 'text-white font-bold' : 'text-slate-500'"
                x-text="selectedEquipment || '-- Pilih Equipment Type --'"></span>
              <svg class="w-5 h-5 text-slate-400 transition-transform" :class="open ? 'rotate-180 text-cyan-400' : ''"
                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <!-- Dropdown List Selalu Kebawah -->
            <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100"
              x-transition:enter-start="opacity-0 translate-y-[-4px]" x-transition:enter-end="opacity-100 translate-y-0"
              class="absolute top-full left-0 right-0 mt-1.5 max-h-64 overflow-y-auto custom-scrollbar bg-slate-950 border border-slate-700 rounded-xl shadow-2xl z-50 py-1 divide-y divide-slate-800/50"
              style="display: none;">
              <template x-for="eqName in availableEquipment" :key="eqName">
                <div @click="selectedEquipment = eqName; onEquipmentChange(); open = false"
                  class="px-4 py-2.5 hover:bg-slate-800 text-sm cursor-pointer transition flex items-center justify-between"
                  :class="selectedEquipment === eqName ? 'text-cyan-400 bg-slate-800/80 font-bold' : 'text-slate-200'">
                  <span x-text="eqName"></span>
                  <svg x-show="selectedEquipment === eqName" class="w-4 h-4 text-cyan-400" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
              </template>
            </div>
            <p class="text-xs text-slate-400 mt-1.5"
              x-text="!selectedOp ? 'Pilih OP terlebih dahulu' : (availableEquipment.length + ' Tipe equipment terpasang')">
            </p>
          </div>

          <!-- Battery Model Custom Dropdown (KEBAWAH) -->
          <div class="relative" x-data="{ open: false }">
            <label class="block text-sm font-bold text-slate-200 mb-2 flex items-center justify-between">
              <span>Battery Model <span class="text-rose-400">*</span></span>
              <span class="text-xs font-mono text-cyan-400 font-semibold" x-show="selectedModel"
                x-text="'Terpilih: ' + selectedModel"></span>
            </label>
            <button type="button"
              @click="if(selectedEquipment && availableModels.length > 0) { open = !open; closeOthers('model'); }"
              :disabled="!selectedEquipment || availableModels.length === 0"
              class="w-full flex items-center justify-between border border-slate-800 rounded-xl px-4 py-3 text-sm text-left outline-none transition shadow-inner font-mono font-medium"
              :class="(!selectedEquipment || availableModels.length === 0) ? 'opacity-50 cursor-not-allowed bg-slate-950/60' :
              'bg-slate-950 hover:border-slate-700 cursor-pointer focus:border-cyan-500'">
              <span :class="selectedModel ? 'text-amber-300 font-bold' : 'text-slate-500'"
                x-text="selectedModel || '-- Pilih Model Baterai --'"></span>
              <svg class="w-5 h-5 text-slate-400 transition-transform" :class="open ? 'rotate-180 text-cyan-400' : ''"
                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <!-- Dropdown List Selalu Kebawah -->
            <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100"
              x-transition:enter-start="opacity-0 translate-y-[-4px]" x-transition:enter-end="opacity-100 translate-y-0"
              class="absolute top-full left-0 right-0 mt-1.5 max-h-64 overflow-y-auto custom-scrollbar bg-slate-950 border border-slate-700 rounded-xl shadow-2xl z-50 py-1 divide-y divide-slate-800/50"
              style="display: none;">
              <template x-for="mItem in availableModels" :key="mItem.model">
                <div @click="selectedModel = mItem.model; onModelChange(); open = false"
                  class="px-4 py-2.5 hover:bg-slate-800 text-sm cursor-pointer transition flex items-center justify-between font-mono"
                  :class="selectedModel === mItem.model ? 'text-amber-300 bg-slate-800/80 font-bold' : 'text-slate-200'">
                  <span x-text="mItem.model + (mItem.std_volt ? ' (Std: ' + mItem.std_volt + ' V)' : '')"></span>
                  <svg x-show="selectedModel === mItem.model" class="w-4 h-4 text-cyan-400" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
              </template>
            </div>
            <p class="text-xs text-slate-400 mt-1.5"
              x-text="!selectedEquipment ? 'Pilih Equipment Type dahulu' : (availableModels.length + ' Model baterai terdaftar')">
            </p>
          </div>

        </div>

        <!-- ========================================================================= -->
        <!-- SIDE-BY-SIDE: HISTORI (KIRI) & INPUT PENGGANTIAN BARU (KANAN) DALAM 1 CARD FORM -->
        <!-- ========================================================================= -->
        <div 
          x-show="selectedModel" 
          x-transition
          class="pt-2 border-t border-slate-800"
          style="display: none;"
        >
          <!-- Header Bar Ringkasan Unit Terpilih -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-4 mb-5 border-b border-slate-800/80">
            <div class="flex items-center gap-2.5 flex-wrap">
              <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                Unit Terhubung
              </span>
              <span class="font-mono font-bold text-white bg-slate-950 px-3 py-1 rounded-xl border border-slate-800 text-xs sm:text-sm" 
                x-text="selectedArea + ' &bull; ' + selectedLine + ' &bull; ' + selectedOp + (selectedMachine ? ' (' + selectedMachine + ')' : '')"></span>
              <span class="font-mono text-amber-300 font-bold text-xs sm:text-sm bg-amber-500/10 border border-amber-500/20 px-2.5 py-1 rounded-xl" x-text="selectedModel"></span>
            </div>
            
            <div class="flex items-center gap-2 text-xs font-mono text-slate-400">
              <span x-show="isLoadingExchange" class="flex items-center gap-1.5 text-amber-400 font-semibold">
                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                Memuat riwayat...
              </span>
            </div>
          </div>

          <!-- GRID 2 KOLOM BERDAMPINGAN: KIRI = HISTORI, KANAN = INPUT DATA PENGGANTIAN -->
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- ========================================================================= -->
            <!-- KOLOM KIRI (5/12): HISTORI DATA SEBELUMNYA -->
            <!-- ========================================================================= -->
            <div class="lg:col-span-5 bg-slate-950/60 border border-slate-800/90 rounded-2xl p-5 space-y-3.5 relative overflow-hidden">
              <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-cyan-500 to-amber-500"></div>
              
              <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                <h4 class="text-sm font-bold text-white flex items-center gap-2">
                  <span class="p-1 rounded-lg bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </span>
                  <span>Histori Terakhir Unit</span>
                </h4>
                <span class="text-[11px] font-mono text-slate-400">Data Sebelumnya</span>
              </div>

              <div class="space-y-2.5">
                
                <!-- 1. Terakhir Penggantian -->
                <div class="bg-slate-900/90 border border-slate-800/80 rounded-xl p-3 flex items-center justify-between">
                  <div class="flex items-center gap-2.5">
                    <div class="p-1.5 rounded-lg bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 shrink-0">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                    </div>
                    <div>
                      <span class="text-xs text-slate-400 block font-medium">1. Terakhir Penggantian</span>
                      <span class="text-[11px] text-slate-500">Tanggal ganti sebelumnya</span>
                    </div>
                  </div>
                  <div class="text-right">
                    <span class="font-mono font-bold text-white text-sm tracking-wide" x-text="historyData.last_replacement_date || lastReplacementDate || '-'"></span>
                  </div>
                </div>

                <!-- 2. Standard Volt -->
                <div class="bg-slate-900/90 border border-slate-800/80 rounded-xl p-3 flex items-center justify-between">
                  <div class="flex items-center gap-2.5">
                    <div class="p-1.5 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20 shrink-0">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                      </svg>
                    </div>
                    <div>
                      <span class="text-xs text-slate-400 block font-medium">2. Standard Volt</span>
                      <span class="text-[11px] text-slate-500">Standar tegangan alat</span>
                    </div>
                  </div>
                  <div class="text-right">
                    <span class="font-mono font-bold text-amber-300 text-sm tracking-wide">
                      <span x-text="historyData.standard_volt || (matchedAssetInfo && matchedAssetInfo.std_volt ? matchedAssetInfo.std_volt : '-')"></span> V
                    </span>
                  </div>
                </div>

                <!-- 3. Voltage Before -->
                <div class="bg-slate-900/90 border border-slate-800/80 rounded-xl p-3 flex items-center justify-between">
                  <div class="flex items-center gap-2.5">
                    <div class="p-1.5 rounded-lg bg-rose-500/10 text-rose-400 border border-rose-500/20 shrink-0">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                      </svg>
                    </div>
                    <div>
                      <span class="text-xs text-slate-400 block font-medium">3. Voltage Before</span>
                      <span class="text-[11px] text-slate-500">Tegangan sebelum dilepas</span>
                    </div>
                  </div>
                  <div class="text-right">
                    <span class="font-mono font-bold text-rose-300 text-sm tracking-wide">
                      <span x-text="historyData.last_voltage_before || '-'"></span>
                      <span class="text-xs font-normal" x-show="historyData.last_voltage_before && historyData.last_voltage_before !== '-'">V</span>
                    </span>
                  </div>
                </div>

                <!-- 4. Status -->
                <div class="bg-slate-900/90 border border-slate-800/80 rounded-xl p-3 flex items-center justify-between">
                  <div class="flex items-center gap-2.5">
                    <div class="p-1.5 rounded-lg border shrink-0 transition-colors"
                      :class="{
                        'bg-emerald-500/10 text-emerald-400 border-emerald-500/20': ['active'].includes((historyData.last_status || 'active').toLowerCase()),
                        'bg-amber-500/10 text-amber-400 border-amber-500/20': ['warning'].includes((historyData.last_status || '').toLowerCase()),
                        'bg-rose-500/10 text-rose-400 border-rose-500/20': ['change', 'error'].includes((historyData.last_status || '').toLowerCase()),
                      }">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </div>
                    <div>
                      <span class="text-xs text-slate-400 block font-medium">4. Status</span>
                      <span class="text-[11px] text-slate-500">Status kondisi terakhir</span>
                    </div>
                  </div>
                  <div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-bold font-mono uppercase"
                      :class="{
                        'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30': ['active'].includes((historyData.last_status || 'active').toLowerCase()),
                        'bg-amber-500/20 text-amber-300 border border-amber-500/30': ['warning'].includes((historyData.last_status || '').toLowerCase()),
                        'bg-rose-500/20 text-rose-300 border border-rose-500/30': ['change', 'error'].includes((historyData.last_status || '').toLowerCase()),
                      }">
                      <span class="w-1.5 h-1.5 rounded-full" 
                        :class="{
                          'bg-emerald-400': ['active'].includes((historyData.last_status || 'active').toLowerCase()),
                          'bg-amber-400': ['warning'].includes((historyData.last_status || '').toLowerCase()),
                          'bg-rose-400': ['change', 'error'].includes((historyData.last_status || '').toLowerCase()),
                        }"></span>
                      <span x-text="['change', 'error'].includes((historyData.last_status || '').toLowerCase()) ? 'Change' : (['warning'].includes((historyData.last_status || '').toLowerCase()) ? 'Warning' : 'Active')"></span>
                    </span>
                  </div>
                </div>

                <!-- 5. Exchange Type -->
                <div class="bg-slate-900/90 border border-slate-800/80 rounded-xl p-3 flex items-center justify-between">
                  <div class="flex items-center gap-2.5">
                    <div class="p-1.5 rounded-lg bg-purple-500/10 text-purple-400 border border-purple-500/20 shrink-0">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                      </svg>
                    </div>
                    <div>
                      <span class="text-xs text-slate-400 block font-medium">5. Exchange Type</span>
                      <span class="text-[11px] text-slate-500">Riwayat exchange sebelumnya</span>
                    </div>
                  </div>
                  <div class="text-right">
                    <span class="font-mono font-bold text-purple-300 text-xs sm:text-sm" x-text="historyData.last_exchange_type || '-'"></span>
                  </div>
                </div>

              </div>
            </div>

            <!-- ========================================================================= -->
            <!-- KOLOM KANAN (7/12): INPUT DATA PENGGANTIAN BARU (LANGSUNG MENYATU) -->
            <!-- ========================================================================= -->
            <div class="lg:col-span-7 space-y-4">
              
              <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                <h4 class="text-sm font-bold text-white flex items-center gap-2">
                  <span class="p-1 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </span>
                  <span>Input Data Penggantian Baru</span>
                </h4>
                <span class="text-xs text-rose-400 font-medium">* Wajib diisi (tidak boleh null)</span>
              </div>

              <!-- Baris 1 Input: Exchange Type (Locked) -->
              <div>
                <label class="block text-sm font-bold text-slate-200 mb-2 flex items-center justify-between">
                  <span>1. Exchange Type <span class="text-rose-400">*</span> <span class="text-purple-300 text-xs font-normal">(Locked)</span></span>
                  <span x-show="isLoadingExchange" class="text-xs text-amber-400 font-mono flex items-center gap-1 font-semibold">
                    <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    Menghitung...
                  </span>
                </label>
                <div class="relative">
                  <input 
                    type="text" 
                    name="exchange_type" 
                    x-model="exchangeType" 
                    readonly 
                    required
                    placeholder="Otomatis terisi dari riwayat"
                    class="w-full bg-slate-950/80 border border-slate-800 rounded-xl px-4 py-3 text-sm text-purple-300 placeholder-slate-600 outline-none font-mono font-bold shadow-inner cursor-not-allowed pl-10"
                  >
                  <div class="absolute left-3.5 top-3.5 text-slate-500 pointer-events-none">
                    <svg class="w-4 h-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                  </div>
                </div>
                <p class="text-xs text-slate-400 mt-1.5" x-text="exchangeInfo ? ('Berikutnya: ' + exchangeType) : 'Otomatis dihitung dari urutan terakhir'"></p>
              </div>

              <!-- Baris 2 Input: Voltage Before & Voltage After (2 Kolom) -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <!-- 2. Voltage Before -->
                <div>
                  <label class="block text-sm font-bold text-slate-200 mb-2 flex items-center justify-between">
                    <span>2. Voltage Before (Trend Before) <span class="text-rose-400">*</span></span>
                  </label>
                  <div class="relative">
                    <input 
                      type="text" 
                      name="voltage_before" 
                      x-model="voltageBefore"
                      required
                      placeholder="Contoh: 2.8" 
                      class="w-full bg-slate-950 border border-slate-800 focus:border-amber-500 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 outline-none focus:ring-1 focus:ring-amber-500 transition-colors font-mono shadow-inner pr-14"
                    >
                    <span class="absolute right-4 top-3 text-sm font-mono text-slate-400 pointer-events-none font-semibold">Volt</span>
                  </div>
                  <p class="text-xs text-slate-400 mt-1.5">Tegangan sebelum baterai dilepas</p>
                </div>

                <!-- 3. Voltage After -->
                <div>
                  <label class="block text-sm font-bold text-slate-200 mb-2 flex items-center justify-between">
                    <span>3. Voltage After (Trend After) <span class="text-rose-400">*</span></span>
                  </label>
                  <div class="relative">
                    <input 
                      type="text" 
                      name="voltage_after" 
                      x-model="voltageAfter"
                      required
                      placeholder="Contoh: 3.6" 
                      class="w-full bg-slate-950 border border-slate-800 focus:border-amber-500 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 outline-none focus:ring-1 focus:ring-amber-500 transition-colors font-mono text-emerald-300 shadow-inner pr-14 font-bold"
                    >
                    <span class="absolute right-4 top-3 text-sm font-mono text-slate-400 pointer-events-none font-semibold">Volt</span>
                  </div>
                  <p class="text-xs text-slate-400 mt-1.5">Tegangan sesudah baterai baru dipasang</p>
                </div>

              </div>

              <!-- Baris 3 Input: Last Change & Date (2 Kolom) -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <!-- 4. Last Change -->
                <div>
                  <label class="block text-sm font-bold text-slate-200 mb-2 flex items-center justify-between">
                    <span>4. Last Change</span>
                    <span class="text-xs font-mono text-amber-400 font-semibold" x-show="lastReplacementDate" x-text="'Riwayat Terakhir'"></span>
                  </label>
                  <div class="relative">
                    <input 
                      type="text" 
                      readonly 
                      :value="lastReplacementDate || 'Pilih OP/Mesin untuk cek riwayat'"
                      class="w-full bg-slate-950/80 border border-slate-800 rounded-xl px-4 py-3 text-sm text-amber-300 font-mono font-bold outline-none cursor-not-allowed pl-11 shadow-inner"
                    >
                    <div class="absolute left-3.5 top-3 text-amber-400 pointer-events-none">
                      <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </div>
                  </div>
                  <p class="text-xs text-slate-400 mt-1.5">Tanggal riwayat sebelum ini</p>
                </div>

                <!-- 5. Date (Tanggal Penggantian Baru, Max Today) -->
                <div>
                  <label class="block text-sm font-bold text-slate-200 mb-2 flex items-center justify-between">
                    <span>5. Date (Tanggal Penggantian) <span class="text-rose-400">*</span></span>
                    <span class="text-xs font-mono font-bold" :class="isDateFuture() ? 'text-rose-400' : 'text-emerald-400'" x-text="formatDisplayDate(replacementDate)"></span>
                  </label>
                  <div class="relative">
                    <input 
                      type="date" 
                      id="replacementDatePicker"
                      name="date" 
                      x-model="replacementDate" 
                      max="{{ date('Y-m-d') }}"
                      @change="validateDateInput()"
                      required 
                      onclick="try { this.showPicker(); } catch(e){}"
                      class="w-full bg-slate-950 border rounded-xl px-4 py-3 text-sm text-white outline-none transition-colors font-mono shadow-inner cursor-pointer"
                      :class="isDateFuture() ? 'border-rose-500 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 bg-rose-950/20' : 'border-slate-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500'"
                    >
                    <button 
                      type="button" 
                      onclick="try { document.getElementById('replacementDatePicker').showPicker(); } catch(e){}"
                      class="absolute right-3.5 top-3 text-slate-400 hover:text-white transition"
                      title="Buka Kalender"
                    >
                      <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                    </button>
                  </div>

                  <!-- Pesan Error jika memilih hari esok / masa depan -->
                  <template x-if="isDateFuture()">
                    <div class="flex items-center gap-1.5 text-xs text-rose-400 font-bold mt-1.5 animate-pulse">
                      <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                      </svg>
                      <span>Error: Tanggal tidak boleh melebihi hari ini (hari esok tidak diperbolehkan)!</span>
                    </div>
                  </template>
                  <template x-if="!isDateFuture()">
                    <p class="text-xs text-slate-400 mt-1.5">Maksimal hari ini (tidak dapat memilih hari esok)</p>
                  </template>
                </div>

              </div>

            </div>

          </div>

        </div>

        <!-- ========================================================================= -->
        <!-- ALERT VALIDASI NULL / KOSONG (JIKA ADA FIELD BELUM TERISI) -->
        <!-- ========================================================================= -->
        <div 
          x-show="validationErrorMessage" 
          x-transition
          class="p-4 rounded-xl bg-rose-500/15 border border-rose-500/40 text-rose-300 text-sm flex items-start gap-3 shadow-lg"
          style="display: none;"
        >
          <svg class="w-5 h-5 text-rose-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div class="flex-1">
            <span class="font-bold block mb-0.5">Gagal Menyimpan: Terdapat data yang masih kosong (Null)!</span>
            <span class="text-xs text-rose-200" x-text="validationErrorMessage"></span>
          </div>
          <button type="button" @click="validationErrorMessage = ''" class="text-rose-400 hover:text-white">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- SUBMIT BUTTON & RESET -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-slate-800">
          <div class="flex items-center gap-2.5 text-sm text-slate-400">
            <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Data yang disimpan akan langsung muncul pada tabel di bawah untuk Anda review/update.</span>
          </div>

          <div class="flex items-center gap-3 w-full sm:w-auto">
            <button 
              type="button" 
              @click="resetForm()"
              class="px-5 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold transition border border-slate-700 w-1/3 sm:w-auto cursor-pointer"
            >
              Reset
            </button>

            <!-- Tombol trigger konfirmasi popup Yes/No -->
            @hasPermission('battery_create')
            <button 
              type="button" 
              @click="promptSaveConfirmation()"
              :disabled="!selectedLine || !selectedOp || !selectedEquipment || !selectedModel || isDateFuture()"
              :class="(!selectedLine || !selectedOp || !selectedEquipment || !selectedModel || isDateFuture()) ?
              'opacity-50 cursor-not-allowed bg-amber-600/60' :
              'bg-amber-600 hover:bg-amber-500 hover:shadow-amber-500/20'"
              class="flex-1 sm:flex-initial px-8 py-3 rounded-xl text-white text-sm font-bold transition shadow-lg flex items-center justify-center gap-2.5 cursor-pointer"
            >
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>Simpan Data Battery</span>
            </button>
            @endhasPermission
          </div>
        </div>

      </form>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL POPUP KONFIRMASI SIMPAN DATA (YES & NO) -->
    <!-- ========================================================================= -->
    <div 
      x-show="confirmModalOpen" 
      x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0" 
      x-transition:enter-end="opacity-100"
      x-transition:leave="transition ease-in duration-150" 
      x-transition:leave-start="opacity-100"
      x-transition:leave-end="opacity-0"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
      style="display: none;"
    >
      <div 
        @click.away="confirmModalOpen = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95" 
        x-transition:enter-end="opacity-100 scale-100"
        class="bg-slate-900 border border-slate-700/80 rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden"
      >
        <!-- Modal Top / Header -->
        <div class="p-6 border-b border-slate-800 bg-slate-950/50 flex items-start gap-4">
          <div class="p-3 rounded-2xl bg-amber-500/10 text-amber-400 border border-amber-500/20 shrink-0">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <h3 class="text-lg font-bold text-white">Simpan Data Penggantian Battery?</h3>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
              Pastikan nilai tegangan dan tanggal sudah benar. Data akan dicatat ke dalam database riwayat baterai.
            </p>
          </div>
        </div>

        <!-- Ringkasan Data yang Akan Disimpan -->
        <div class="p-6 space-y-3 bg-slate-900/60">
          <div class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Ringkasan Data:</div>
          <div class="rounded-xl bg-slate-950 border border-slate-800 p-4 space-y-2.5 text-sm font-mono">
            <div class="flex justify-between border-b border-slate-800/80 pb-2">
              <span class="text-slate-400">Area / Line:</span>
              <span class="text-cyan-300 font-bold" x-text="selectedArea + ' / ' + selectedLine"></span>
            </div>
            <div class="flex justify-between border-b border-slate-800/80 pb-2">
              <span class="text-slate-400">OP / Mesin:</span>
              <span class="text-white font-bold" x-text="selectedOp + (selectedMachine ? ' (' + selectedMachine + ')' : '')"></span>
            </div>
            <div class="flex justify-between border-b border-slate-800/80 pb-2">
              <span class="text-slate-400">Model Baterai:</span>
              <span class="text-amber-300 font-bold" x-text="selectedModel"></span>
            </div>
            <div class="flex justify-between border-b border-slate-800/80 pb-2">
              <span class="text-slate-400">Exchange Type:</span>
              <span class="text-purple-300 font-bold" x-text="exchangeType"></span>
            </div>
            <div class="flex justify-between border-b border-slate-800/80 pb-2">
              <span class="text-slate-400">Volt Before / After:</span>
              <span class="text-white font-bold">
                <span class="text-rose-300" x-text="voltageBefore + ' V'"></span>
                <span class="text-slate-500"> &rarr; </span>
                <span class="text-emerald-300" x-text="voltageAfter + ' V'"></span>
              </span>
            </div>
            <div class="flex justify-between pt-0.5">
              <span class="text-slate-400">Tanggal Penggantian:</span>
              <span class="text-emerald-400 font-bold" x-text="formatDisplayDate(replacementDate)"></span>
            </div>
          </div>
        </div>

        <!-- Tombol Aksi Modal (YES & NO) -->
        <div class="p-5 border-t border-slate-800 bg-slate-950/70 flex items-center justify-end gap-3">
          <!-- Tombol NO (Batal) -->
          <button 
            type="button" 
            @click="confirmModalOpen = false"
            class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold transition border border-slate-700 cursor-pointer"
          >
            No (Batal)
          </button>

          <!-- Tombol YES (Simpan) -->
          <button 
            type="button" 
            @click="submitConfirmedForm()"
            class="px-6 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-sm font-bold transition shadow-lg shadow-amber-600/30 flex items-center gap-2 cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>Yes (Simpan Data)</span>
          </button>
        </div>

      </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL FILTER EXPORT EXCEL (RENTANG WAKTU / MUTASI & PILIHAN AREA)           -->
    <!-- ========================================================================= -->
    <div 
      x-show="exportModalOpen" 
      x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100"
      x-transition:leave="transition ease-in duration-150"
      x-transition:leave-start="opacity-100"
      x-transition:leave-end="opacity-0"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
      style="display: none;"
    >
      <div 
        @click.away="closeExportModal()"
        class="bg-slate-900 border border-slate-700/80 rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-150"
      >
        <!-- Header Modal Export -->
        <div class="p-5 border-b border-slate-800 bg-slate-950/60 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <div>
              <h3 class="text-base font-bold text-white">Export Data Penggantian Battery</h3>
              <p class="text-xs text-slate-400">Pilih rentang waktu (mutasi) dan area data yang akan didownload</p>
            </div>
          </div>
          <button type="button" @click="closeExportModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Body Form Filter Export -->
        <div class="p-6 space-y-4">
          <!-- Pilihan Rentang Waktu (Mutasi) -->
          <div>
            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider font-mono mb-2 flex items-center gap-2">
              <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span>Rentang Waktu Penggantian (Mutasi)</span>
            </label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <span class="text-[11px] text-slate-400 block mb-1">Dari Tanggal:</span>
                <div class="relative">
                  <input 
                    type="date" 
                    id="exportStartDate"
                    x-model="exportFilter.start_date"
                    onclick="try { this.showPicker(); } catch(e){}"
                    style="color-scheme: dark;"
                    class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-3.5 py-2.5 pr-10 text-sm text-white font-mono focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none cursor-pointer"
                  />
                  <button 
                    type="button" 
                    onclick="try { document.getElementById('exportStartDate').showPicker(); } catch(e){}"
                    class="absolute right-3 top-2.5 text-slate-400 hover:text-emerald-400 transition cursor-pointer"
                    title="Buka Kalender"
                  >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </button>
                </div>
              </div>
              <div>
                <span class="text-[11px] text-slate-400 block mb-1">Sampai Tanggal:</span>
                <div class="relative">
                  <input 
                    type="date" 
                    id="exportEndDate"
                    x-model="exportFilter.end_date"
                    onclick="try { this.showPicker(); } catch(e){}"
                    style="color-scheme: dark;"
                    class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-3.5 py-2.5 pr-10 text-sm text-white font-mono focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none cursor-pointer"
                  />
                  <button 
                    type="button" 
                    onclick="try { document.getElementById('exportEndDate').showPicker(); } catch(e){}"
                    class="absolute right-3 top-2.5 text-slate-400 hover:text-emerald-400 transition cursor-pointer"
                    title="Buka Kalender"
                  >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
            <p class="text-[11px] text-slate-500 mt-1.5">* Kosongkan tanggal jika ingin mendownload tanpa batasan waktu.</p>
          </div>

          <!-- Pilihan Area -->
          <div>
            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider font-mono mb-2 flex items-center gap-2">
              <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              <span>Pilihan Area</span>
            </label>
            <select 
              x-model="exportFilter.area"
              class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-sm text-white font-mono focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none cursor-pointer"
            >
              <option value="ALL">Semua Area (ALL AREAS)</option>
              @if(isset($availableAreas))
                @foreach($availableAreas as $areaItem)
                  <option value="{{ $areaItem }}">{{ $areaItem }}</option>
                @endforeach
              @endif
            </select>
          </div>

          <!-- Pilihan Scope Data: Data Baru Saja vs Semua Riwayat -->
          <div class="pt-2 border-t border-slate-800 flex items-center justify-between text-xs">
            <span class="text-slate-400">Lingkup Riwayat:</span>
            <div class="flex items-center gap-3">
              <label class="inline-flex items-center gap-1.5 text-slate-300 cursor-pointer">
                <input type="radio" value="0" x-model="exportFilter.all" class="text-emerald-500 focus:ring-emerald-500 bg-slate-950 border-slate-700" />
                <span>Sesi Baru</span>
              </label>
              <label class="inline-flex items-center gap-1.5 text-slate-300 cursor-pointer">
                <input type="radio" value="1" x-model="exportFilter.all" class="text-emerald-500 focus:ring-emerald-500 bg-slate-950 border-slate-700" />
                <span>Semua Data Database</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Tombol Aksi Modal Export -->
        <div class="p-5 border-t border-slate-800 bg-slate-950/70 flex items-center justify-end gap-3">
          <button 
            type="button" 
            @click="closeExportModal()"
            class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold transition border border-slate-700 cursor-pointer"
          >
            Batal
          </button>
          <button 
            type="button" 
            @click="submitExportExcel()"
            class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-bold transition shadow-lg shadow-emerald-600/30 flex items-center gap-2 cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            <span>Download Excel (.xlsx)</span>
          </button>
        </div>

      </div>
    </div>

    <!-- 5. TABEL DATA SUKSES TERSIMPAN (HANYA MENAMPILKAN DATA YANG BARU DITAMBAHKAN) -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">

      <!-- Table Header & Action Buttons -->
      <div
        class="p-5 sm:p-6 border-b border-slate-800 bg-slate-950/50 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
          <div class="p-2.5 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 shrink-0">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <h3 class="font-bold text-white text-base sm:text-lg flex items-center gap-2.5">
              <span>Data Penggantian Battery yang Baru Ditambahkan</span>
              <span
                class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 shrink-0">
                {{ $totalInputCount }} Data
              </span>
            </h3>
          </div>
        </div>

        <!-- Action: Export Excel, Toggle, & Search (SATU BARIS SEJAJAR RAPI) -->
        <div class="flex items-center gap-2.5 flex-nowrap shrink-0 overflow-x-auto py-1">
          <!-- Export to Excel Button (Membuka Modal Filter Rentang Waktu & Area) -->
          @hasPermission('battery_export')
          <button 
            type="button"
            @click="openExportModal()"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs sm:text-sm transition shadow-lg shadow-emerald-600/30 whitespace-nowrap shrink-0 cursor-pointer"
            title="Download data tabel ke format Excel (.xlsx) dengan pilihan rentang waktu dan area">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Export ke Excel</span>
          </button>
          @endhasPermission

          <!-- Toggle View All / Only Recent -->
          <a href="{{ route('input.battery', ['show_all' => $showAll ? 0 : 1]) }}"
            class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs sm:text-sm font-semibold transition border border-slate-700 whitespace-nowrap shrink-0 cursor-pointer">
            <span x-text="'{{ $showAll ? 'Tampilkan Data Baru Saja' : 'Lihat Semua Riwayat' }}'"></span>
          </a>

          <!-- Search Form (Sejajar dalam 1 Baris) -->
          <form method="GET" action="{{ route('input.battery') }}"
            class="flex items-center gap-1.5 flex-nowrap shrink-0">
            @if ($showAll)
              <input type="hidden" name="show_all" value="1">
            @endif
            <div class="relative flex items-center">
              <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Line, OP..."
                class="w-36 sm:w-48 bg-slate-950 border border-slate-800 rounded-xl pl-8 pr-3 py-2 text-xs sm:text-sm text-white placeholder-slate-500 outline-none focus:ring-1 focus:ring-amber-500 font-medium">
              <svg class="w-4 h-4 text-slate-500 absolute left-2.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <button type="submit"
              class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs sm:text-sm transition border border-slate-700 whitespace-nowrap cursor-pointer">
              Cari
            </button>
            @if (request('search'))
              <a href="{{ route('input.battery', ['show_all' => $showAll ? 1 : 0]) }}"
                class="px-2.5 py-2 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-400 hover:text-white text-xs transition border border-slate-700 whitespace-nowrap"
                title="Reset Pencarian">
                Reset
              </a>
            @endif
          </form>
        </div>
      </div>

      <!-- Table Content -->
      <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left text-sm border-collapse">
          <thead>
            <tr
              class="border-b border-slate-800 text-xs font-mono font-bold text-slate-300 uppercase bg-slate-950/80 whitespace-nowrap tracking-wider">
              <th class="py-3.5 px-4 text-center">#</th>
              <th class="py-3.5 px-4">Tanggal</th>
              <th class="py-3.5 px-4">Area</th>
              <th class="py-3.5 px-4">Line</th>
              <th class="py-3.5 px-4">OP Number</th>
              <th class="py-3.5 px-4">Machine No</th>
              <th class="py-3.5 px-4">Equipment Type</th>
              <th class="py-3.5 px-4">Battery Model</th>
              <th class="py-3.5 px-4">Exchange Type</th>
              <th class="py-3.5 px-4 text-center">Volt Before</th>
              <th class="py-3.5 px-4 text-center">Volt After</th>
              <th class="py-3.5 px-4 text-center">Status</th>
              <th class="py-3.5 px-4 text-center">Input By</th>
              @if ($isAdmin)
                <th class="py-3.5 px-4 text-center">Aksi</th>
              @endif
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/60 font-sans whitespace-nowrap">
            @forelse ($inputBatteries as $item)
              <tr class="hover:bg-slate-800/40 transition-colors">
                <td class="py-3.5 px-4 text-center font-mono text-slate-400 font-medium">{{ $item->id }}</td>

                <!-- Tanggal (Date) -->
                <td class="py-3.5 px-4 font-mono text-white font-semibold">
                  {{ $item->last_day ? \Carbon\Carbon::parse($item->last_day)->locale('id')->translatedFormat('d M Y') : ($item->created_at ? $item->created_at->locale('id')->translatedFormat('d M Y') : '-') }}
                </td>

                <!-- Area -->
                <td class="py-3.5 px-4 text-slate-200 font-medium">
                  {{ $item->area ?: $item->battery->area ?? '-' }}
                </td>

                <!-- Line -->
                <td class="py-3.5 px-4">
                  <span
                    class="px-2.5 py-1 rounded-lg bg-slate-800 text-amber-300 border border-slate-700 font-mono text-xs font-bold">
                    {{ $item->line }}
                  </span>
                </td>

                <!-- OP Number -->
                <td class="py-3.5 px-4 font-mono text-amber-300 font-bold">
                  {{ $item->op_number ?: '-' }}
                </td>

                <!-- Machine No -->
                <td class="py-3.5 px-4 font-mono text-cyan-300 font-semibold">
                  {{ $item->machine_no ?: '-' }}
                </td>

                <!-- Equipment Type -->
                <td class="py-3.5 px-4 text-slate-200">{{ $item->equipment_type ?: '-' }}</td>

                <!-- Battery Model -->
                <td class="py-3.5 px-4 text-indigo-300 font-mono font-semibold">{{ $item->battery_model ?: '-' }}</td>

                <!-- Exchange Type -->
                <td class="py-3.5 px-4 font-mono text-xs">
                  <span
                    class="px-2.5 py-1 rounded-lg bg-purple-950/80 text-purple-300 border border-purple-800/60 font-semibold">
                    {{ $item->exchange_type ?: '-' }}
                  </span>
                </td>

                <!-- Volt Before -->
                <td class="py-3.5 px-4 text-center font-mono text-slate-300 font-medium">
                  {{ $item->voltage_before ? $item->voltage_before . (str_contains($item->voltage_before, 'V') ? '' : ' V') : '-' }}
                </td>

                <!-- Volt After -->
                <td class="py-3.5 px-4 text-center font-mono text-emerald-400 font-bold">
                  {{ $item->voltage_after ? $item->voltage_after . (str_contains($item->voltage_after, 'V') ? '' : ' V') : ($item->standart_volt ? $item->standart_volt . ' V' : '-') }}
                </td>

                <!-- Status -->
                <td class="py-3.5 px-4 text-center">
                  @php
                    $st = strtolower($item->status ?? 'active');
                  @endphp
                  @if ($st === 'active')
                    <span
                      class="px-2.5 py-1 rounded-full text-xs font-bold font-mono bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">ACTIVE</span>
                  @elseif ($st === 'warning')
                    <span
                      class="px-2.5 py-1 rounded-full text-xs font-bold font-mono bg-amber-500/10 text-amber-400 border border-amber-500/30">WARNING</span>
                  @else
                    <span
                      class="px-2.5 py-1 rounded-full text-xs font-bold font-mono bg-rose-500/10 text-rose-400 border border-rose-500/30">CHANGE</span>
                  @endif
                </td>

                <!-- Input By -->
                <td class="py-3.5 px-4 text-center">
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-800 text-slate-300 border border-slate-700 font-mono" title="Diinput oleh {{ $item->created_by ?: 'System' }} pada {{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-' }}">
                    <svg class="w-3 h-3 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>{{ $item->created_by ?: 'System' }}</span>
                  </span>
                </td>

                <!-- Aksi (Update & Delete) -->
                @if ($isAdmin)
                <td class="py-3.5 px-4 text-center">
                  <div class="flex items-center justify-center gap-1.5">
                    <!-- Tombol Update / Edit -->
                    @hasPermission('battery_edit')
                    <button type="button"
                      @click="openEditModal({
                      id: {{ $item->id }},
                      line: '{{ addslashes($item->line) }}',
                      op_number: '{{ addslashes($item->op_number) }}',
                      machine_no: '{{ addslashes($item->machine_no) }}',
                      equipment_type: '{{ addslashes($item->equipment_type) }}',
                      battery_model: '{{ addslashes($item->battery_model) }}',
                      exchange_type: '{{ addslashes($item->exchange_type) }}',
                      voltage_before: '{{ addslashes($item->voltage_before) }}',
                      voltage_after: '{{ addslashes($item->voltage_after) }}',
                      last_day: '{{ $item->last_day ? \Carbon\Carbon::parse($item->last_day)->format('Y-m-d') : '' }}',
                      status: '{{ $item->status ?: 'active' }}'
                    })"
                      title="Edit / Update Data"
                      class="p-2 rounded-xl text-slate-300 hover:text-cyan-300 hover:bg-slate-800 transition cursor-pointer">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    @endhasPermission

                    <!-- Tombol Hapus / Delete -->
                    @hasPermission('battery_delete')
                    <form action="{{ route('input.input-battery.destroy', $item->id) }}" method="POST"
                      onsubmit="return confirm('Yakin ingin menghapus data penggantian battery ID #{{ $item->id }}?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" title="Hapus Data"
                        class="p-2 rounded-xl text-slate-300 hover:text-rose-400 hover:bg-slate-800 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </form>
                    @endhasPermission
                  </div>
                </td>
                @endif
              </tr>
            @empty
              <tr>
                <td colspan="{{ $isAdmin ? 14 : 13 }}" class="py-14 text-center text-slate-400 font-mono text-sm">
                  @if ($showAll)
                    Tidak ada data yang ditemukan.
                  @else
                  @endif
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Table Footer with Pagination (if multiple pages) -->
      @if ($inputBatteries->hasPages())
        <div
          class="p-5 border-t border-slate-800 bg-slate-950/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-slate-400">
          <div>
            Menampilkan <strong class="text-white">{{ $inputBatteries->firstItem() ?? 0 }}</strong> - <strong
              class="text-white">{{ $inputBatteries->lastItem() ?? 0 }}</strong> dari <strong
              class="text-amber-400">{{ $inputBatteries->total() }}</strong> data
          </div>

          <div class="flex items-center gap-2">
            @if ($inputBatteries->onFirstPage())
              <span
                class="px-3.5 py-2 rounded-xl bg-slate-800/50 text-slate-600 cursor-not-allowed font-mono text-xs">&larr;
                Prev</span>
            @else
              <a href="{{ $inputBatteries->previousPageUrl() }}"
                class="px-3.5 py-2 rounded-xl bg-slate-800 text-slate-200 hover:bg-slate-700 hover:text-white font-mono text-xs transition-colors">&larr;
                Prev</a>
            @endif

            <span class="font-mono text-xs text-slate-200 px-2.5">
              Hal <span class="text-white font-bold">{{ $inputBatteries->currentPage() }}</span> /
              {{ $inputBatteries->lastPage() }}
            </span>

            @if ($inputBatteries->hasMorePages())
              <a href="{{ $inputBatteries->nextPageUrl() }}"
                class="px-3.5 py-2 rounded-xl bg-slate-800 text-slate-200 hover:bg-slate-700 hover:text-white font-mono text-xs transition-colors">Next
                &rarr;</a>
            @else
              <span
                class="px-3.5 py-2 rounded-xl bg-slate-800/50 text-slate-600 cursor-not-allowed font-mono text-xs">Next
                &rarr;</span>
            @endif
          </div>
        </div>
      @endif

    </div>

    <!-- 6. MODAL UPDATE / EDIT DATA -->
    <div x-show="editModalOpen" x-cloak
      class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4"
      style="display: none;">
      <div @click.away="closeEditModal()"
        class="w-full max-w-lg bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl overflow-hidden">
        <div class="p-5 border-b border-slate-800 bg-slate-950/60 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="p-2.5 rounded-xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </div>
            <div>
              <h3 class="text-base font-bold text-white">Update Data Penggantian Battery</h3>
              <p class="text-xs text-slate-400 font-mono mt-0.5"
                x-text="'ID: #' + editItem.id + ' &bull; ' + editItem.line + ' &bull; ' + editItem.op_number"></p>
            </div>
          </div>
          <button type="button" @click="closeEditModal()"
            class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition cursor-pointer">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form :action="editItem.action_url" method="POST" class="p-6 space-y-4">
          @csrf
          @method('PUT')

          <!-- Readonly Information -->
          <div class="p-3.5 rounded-xl bg-slate-950/60 border border-slate-800 text-sm grid grid-cols-2 gap-3">
            <div>
              <span class="text-slate-400 block text-xs">Mesin / OP:</span>
              <span class="font-mono text-cyan-300 font-bold"
                x-text="editItem.op_number + ' (' + editItem.machine_no + ')'"></span>
            </div>
            <div>
              <span class="text-slate-400 block text-xs">Model Battery:</span>
              <span class="font-mono text-amber-300 font-bold" x-text="editItem.battery_model"></span>
            </div>
          </div>

          <!-- Exchange Type -->
          <div>
            <label class="block text-sm font-bold text-slate-200 mb-1.5">Exchange Type</label>
            <input type="text" name="exchange_type" x-model="editItem.exchange_type"
              class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-purple-300 font-mono font-bold outline-none focus:ring-1 focus:ring-cyan-500">
          </div>

          <!-- Voltage Before & After -->
          <div class="grid grid-cols-2 gap-3.5">
            <div>
              <label class="block text-sm font-bold text-slate-200 mb-1.5">Volt Before (V)</label>
              <input type="text" name="voltage_before" x-model="editItem.voltage_before"
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-white font-mono outline-none focus:ring-1 focus:ring-cyan-500">
            </div>
            <div>
              <label class="block text-sm font-bold text-slate-200 mb-1.5">Volt After (V)</label>
              <input type="text" name="voltage_after" x-model="editItem.voltage_after"
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-emerald-300 font-mono font-bold outline-none focus:ring-1 focus:ring-cyan-500">
            </div>
          </div>

          <!-- Tanggal Penggantian -->
          <div>
            <label class="block text-sm font-bold text-slate-200 mb-1.5">Tanggal Penggantian (Date)</label>
            <input type="date" name="last_day" x-model="editItem.last_day" required
              onclick="try { this.showPicker(); } catch(e){}"
              class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-white font-mono outline-none focus:ring-1 focus:ring-cyan-500 cursor-pointer">
          </div>

          <!-- Status -->
          <div>
            <label class="block text-sm font-bold text-slate-200 mb-1.5">Status Kondisi</label>
            <select name="status" x-model="editItem.status"
              class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-white outline-none focus:ring-1 focus:ring-cyan-500">
              <option value="active">Active (Normal)</option>
              <option value="warning">Warning (Perlu Perhatian)</option>
              <option value="change">Change (Perlu Ganti)</option>
            </select>
          </div>

          <div class="flex items-center justify-end gap-3 pt-3.5 border-t border-slate-800">
            <button type="button" @click="closeEditModal()"
              class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold transition cursor-pointer">
              Batal
            </button>
            <button type="submit"
              class="px-5 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white text-sm font-bold transition shadow-lg shadow-cyan-600/30 cursor-pointer">
              Simpan Perubahan
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>

  <script>
    function batteryInputApp() {
      return {
        tree: @json($cascadeTree),
        selectedArea: '',
        selectedLine: '',
        selectedOp: '',
        selectedMachine: '',
        selectedEquipment: '',
        selectedModel: '',
        exchangeType: '',
        voltageBefore: '',
        voltageAfter: '',
        lastReplacementDate: '',
        replacementDate: '{{ date('Y-m-d') }}',

        availableLines: [],
        availableOps: [],
        availableMachines: [],
        availableEquipment: [],
        availableModels: [],

        isLoadingExchange: false,
        exchangeInfo: null,
        matchedAssetInfo: null,
        showUpload: false,

        // Kolom Histori State
        historyLoaded: false,
        historyCount: 0,
        historyData: {
          last_replacement_date: '',
          standard_volt: '',
          last_voltage_before: '',
          last_voltage_after: '',
          last_status: 'active',
          last_exchange_type: ''
        },

        // Popup Konfirmasi Simpan (Yes & No) State
        confirmModalOpen: false,
        validationErrorMessage: '',

        // Modal Filter Export Excel (Rentang Waktu / Mutasi & Pilihan Area)
        exportModalOpen: false,
        exportFilter: {
          start_date: '',
          end_date: '',
          area: 'ALL',
          all: '{{ $showAll ? 1 : 0 }}'
        },

        openExportModal() {
          this.exportModalOpen = true;
        },

        closeExportModal() {
          this.exportModalOpen = false;
        },

        submitExportExcel() {
          const params = new URLSearchParams();
          if (this.exportFilter.all) params.append('all', this.exportFilter.all);
          if (this.exportFilter.start_date) params.append('start_date', this.exportFilter.start_date);
          if (this.exportFilter.end_date) params.append('end_date', this.exportFilter.end_date);
          if (this.exportFilter.area && this.exportFilter.area !== 'ALL') params.append('area', this.exportFilter.area);
          
          const url = `{{ route('input.battery.export') }}?${params.toString()}`;
          window.location.href = url;
          this.exportModalOpen = false;
        },

        // Edit modal state
        editModalOpen: false,
        editItem: {
          id: null,
          line: '',
          op_number: '',
          machine_no: '',
          equipment_type: '',
          battery_model: '',
          exchange_type: '',
          voltage_before: '',
          voltage_after: '',
          last_day: '',
          status: 'active',
          action_url: ''
        },

        closeOthers(exclude) {
          // Helper function if needed
        },

        isDateFuture() {
          if (!this.replacementDate) return false;
          const today = new Date();
          const todayStr = today.toISOString().split('T')[0];
          return this.replacementDate > todayStr;
        },

        validateDateInput() {
          if (this.isDateFuture()) {
            this.validationErrorMessage = 'Tanggal penggantian tidak boleh melebihi hari ini (hari esok / masa depan tidak diperbolehkan)!';
          } else if (this.validationErrorMessage && this.validationErrorMessage.includes('Tanggal')) {
            this.validationErrorMessage = '';
          }
        },

        formatDisplayDate(dateStr) {
          if (!dateStr) return '-';
          const parts = dateStr.includes('-') ? dateStr.split('-') : dateStr.split('/');
          if (parts.length === 3) {
            let year, month, day;
            if (parts[0].length === 4) {
              // Format YYYY-MM-DD
              year = parts[0];
              month = parseInt(parts[1], 10);
              day = parseInt(parts[2], 10);
            } else {
              // Format DD-MM-YYYY
              day = parseInt(parts[0], 10);
              month = parseInt(parts[1], 10);
              year = parts[2];
            }
            const monthNames = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sept', 'Okt', 'Nov', 'Des'];
            const mName = monthNames[month] || parts[1];
            return `${day} ${mName} ${year}`;
          }
          return dateStr;
        },

        promptSaveConfirmation() {
          this.validationErrorMessage = '';

          // 1. Cek field null / kosong
          const missingFields = [];
          if (!this.selectedArea) missingFields.push('Area');
          if (!this.selectedLine) missingFields.push('Line');
          if (!this.selectedOp) missingFields.push('OP Number');
          if (!this.selectedEquipment) missingFields.push('Equipment Type');
          if (!this.selectedModel) missingFields.push('Battery Model');
          if (!this.exchangeType) missingFields.push('Exchange Type');
          if (this.voltageBefore === '' || this.voltageBefore === null || this.voltageBefore === undefined) missingFields.push('Voltage Before');
          if (this.voltageAfter === '' || this.voltageAfter === null || this.voltageAfter === undefined) missingFields.push('Voltage After');
          if (!this.replacementDate) missingFields.push('Tanggal Penggantian');

          if (missingFields.length > 0) {
            this.validationErrorMessage = 'Field berikut wajib diisi dan tidak boleh null/kosong: ' + missingFields.join(', ');
            window.scrollTo({ top: 300, behavior: 'smooth' });
            return;
          }

          // 2. Cek apakah tanggal melebihi hari ini
          if (this.isDateFuture()) {
            this.validationErrorMessage = 'Tanggal penggantian tidak boleh melebihi hari ini (hari esok / masa depan tidak diperbolehkan)!';
            return;
          }

          // 3. Jika semua valid, buka modal konfirmasi Yes/No
          this.confirmModalOpen = true;
        },

        submitConfirmedForm() {
          this.confirmModalOpen = false;
          const form = document.getElementById('batteryInputForm');
          if (form) {
            form.submit();
          }
        },

        openEditModal(item) {
          this.editItem = {
            id: item.id,
            line: item.line || '',
            op_number: item.op_number || '',
            machine_no: item.machine_no || '',
            equipment_type: item.equipment_type || '',
            battery_model: item.battery_model || '',
            exchange_type: item.exchange_type || '',
            voltage_before: item.voltage_before || '',
            voltage_after: item.voltage_after || '',
            last_day: item.last_day || '',
            status: item.status || 'active',
            action_url: `{{ url('/input/input-battery') }}/${item.id}`
          };
          this.editModalOpen = true;
        },

        closeEditModal() {
          this.editModalOpen = false;
        },

        onAreaChange() {
          this.selectedLine = '';
          this.selectedOp = '';
          this.selectedMachine = '';
          this.selectedEquipment = '';
          this.selectedModel = '';
          this.exchangeType = '';
          this.lastReplacementDate = '';
          this.exchangeInfo = null;
          this.matchedAssetInfo = null;
          this.historyLoaded = false;
          this.historyCount = 0;
          this.historyData = {
            last_replacement_date: '',
            standard_volt: '',
            last_voltage_before: '',
            last_voltage_after: '',
            last_status: 'active',
            last_exchange_type: ''
          };

          if (this.selectedArea && this.tree[this.selectedArea]) {
            this.availableLines = Object.keys(this.tree[this.selectedArea]).sort();
          } else {
            this.availableLines = [];
          }
          this.availableOps = [];
          this.availableMachines = [];
          this.availableEquipment = [];
          this.availableModels = [];
        },

        onLineChange() {
          this.selectedOp = '';
          this.selectedMachine = '';
          this.selectedEquipment = '';
          this.selectedModel = '';
          this.exchangeType = '';
          this.lastReplacementDate = '';
          this.exchangeInfo = null;
          this.matchedAssetInfo = null;
          this.historyLoaded = false;
          this.historyCount = 0;
          this.historyData = {
            last_replacement_date: '',
            standard_volt: '',
            last_voltage_before: '',
            last_voltage_after: '',
            last_status: 'active',
            last_exchange_type: ''
          };

          if (this.selectedArea && this.selectedLine && this.tree[this.selectedArea]?.[this.selectedLine]) {
            this.availableOps = Object.keys(this.tree[this.selectedArea][this.selectedLine]).sort();
          } else {
            this.availableOps = [];
          }
          this.availableMachines = [];
          this.availableEquipment = [];
          this.availableModels = [];
        },

        onOpChange() {
          this.selectedMachine = '';
          this.selectedEquipment = '';
          this.selectedModel = '';
          this.exchangeType = '';
          this.lastReplacementDate = '';
          this.exchangeInfo = null;
          this.matchedAssetInfo = null;
          this.historyLoaded = false;
          this.historyCount = 0;
          this.historyData = {
            last_replacement_date: '',
            standard_volt: '',
            last_voltage_before: '',
            last_voltage_after: '',
            last_status: 'active',
            last_exchange_type: ''
          };

          const opData = this.tree[this.selectedArea]?.[this.selectedLine]?.[this.selectedOp];
          if (opData) {
            this.availableMachines = opData.machines || [];
            if (this.availableMachines.length > 0) {
              this.selectedMachine = this.availableMachines[0];
            }
            this.availableEquipment = Object.keys(opData.equipments || {}).sort();
            // Auto-select if only 1 option
            if (this.availableEquipment.length === 1) {
              this.selectedEquipment = this.availableEquipment[0];
              this.onEquipmentChange();
            }
          } else {
            this.availableMachines = [];
            this.availableEquipment = [];
          }
          this.availableModels = [];
        },

        onEquipmentChange() {
          this.selectedModel = '';
          this.exchangeType = '';
          this.lastReplacementDate = '';
          this.exchangeInfo = null;
          this.matchedAssetInfo = null;
          this.historyLoaded = false;
          this.historyCount = 0;
          this.historyData = {
            last_replacement_date: '',
            standard_volt: '',
            last_voltage_before: '',
            last_voltage_after: '',
            last_status: 'active',
            last_exchange_type: ''
          };

          const opData = this.tree[this.selectedArea]?.[this.selectedLine]?.[this.selectedOp];
          if (opData && this.selectedEquipment && opData.equipments?.[this.selectedEquipment]) {
            this.availableModels = opData.equipments[this.selectedEquipment];
            // Auto-select if only 1 option
            if (this.availableModels.length === 1) {
              this.selectedModel = this.availableModels[0].model;
              this.onModelChange();
            }
          } else {
            this.availableModels = [];
          }
        },

        onMachineChange() {
          if (this.selectedModel) {
            this.voltageAfter = '';
            this.fetchNextExchangeType();
          }
        },

        onModelChange() {
          if (!this.selectedModel) {
            this.exchangeType = '';
            this.lastReplacementDate = '';
            this.exchangeInfo = null;
            this.matchedAssetInfo = null;
            this.historyLoaded = false;
            this.historyCount = 0;
            return;
          }

          // Find matched asset info for default voltage and machine
          const item = this.availableModels.find(m => m.model === this.selectedModel);
          if (item) {
            this.matchedAssetInfo = item;
            if (item.machine_no && !this.selectedMachine) {
              this.selectedMachine = item.machine_no;
            }
            if (item.std_volt) {
              this.voltageAfter = item.std_volt;
            }
          }

          // Auto-fetch next exchange type & last replacement date & history
          this.fetchNextExchangeType();
        },

        async fetchNextExchangeType() {
          if (!this.selectedLine || !this.selectedOp) return;

          this.isLoadingExchange = true;
          try {
            const params = new URLSearchParams({
              area: this.selectedArea || '',
              line: this.selectedLine || '',
              op: this.selectedOp || '',
              machine_no: this.selectedMachine || '',
              equipment_type: this.selectedEquipment || '',
              battery_model: this.selectedModel || ''
            });

            const res = await fetch(`{{ route('input.battery.next-exchange') }}?${params.toString()}`);
            const data = await res.json();
            if (data.success) {
              this.exchangeInfo = data;
              this.exchangeType = data.next_exchange_type;
              this.lastReplacementDate = data.last_replacement_date;
              this.historyLoaded = true;
              this.historyCount = data.count_history || 0;
              this.historyData = {
                last_replacement_date: data.last_replacement_date || '-',
                standard_volt: data.standard_volt || '-',
                last_voltage_before: data.last_voltage_before || '-',
                last_voltage_after: data.last_voltage_after || '-',
                last_status: data.last_status || 'active',
                last_exchange_type: data.last_exchange_type || '-'
              };

              // Selalu isi / update voltageAfter dengan standard_volt master yang akurat dari server
              if (data.standard_volt && data.standard_volt !== '-') {
                this.voltageAfter = data.standard_volt;
              }

              // Jika voltage before masih kosong dan histori punya nilai, defaultkan ke volt before sebelumnya
              if (!this.voltageBefore && data.last_voltage_before && data.last_voltage_before !== '-') {
                this.voltageBefore = data.last_voltage_before;
              }
            }
          } catch (err) {
            console.error('Failed to fetch next exchange type & history:', err);
          } finally {
            this.isLoadingExchange = false;
          }
        },

        resetForm() {
          this.selectedArea = '';
          this.selectedLine = '';
          this.selectedOp = '';
          this.selectedMachine = '';
          this.selectedEquipment = '';
          this.selectedModel = '';
          this.exchangeType = '';
          this.voltageBefore = '';
          this.voltageAfter = '';
          this.lastReplacementDate = '';
          this.replacementDate = '{{ date('Y-m-d') }}';
          this.availableLines = [];
          this.availableOps = [];
          this.availableMachines = [];
          this.availableEquipment = [];
          this.availableModels = [];
          this.exchangeInfo = null;
          this.matchedAssetInfo = null;
          this.historyLoaded = false;
          this.historyCount = 0;
          this.historyData = {
            last_replacement_date: '',
            standard_volt: '',
            last_voltage_before: '',
            last_voltage_after: '',
            last_status: 'active',
            last_exchange_type: ''
          };
          this.confirmModalOpen = false;
          this.validationErrorMessage = '';
        }
      };
    }
  </script>
@endsection

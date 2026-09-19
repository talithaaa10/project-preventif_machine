@extends('layouts.alpineLayout')

@section('title', 'Master Data Battery - CMMS')
@section('page-title', 'Master Data Battery')

@section('content')
  @php
    $masterOptions = $masterOptions ?? [];
    $opt = function ($key, $default = []) use ($masterOptions, $areas, $lines) {
        if (isset($masterOptions[$key]) && count($masterOptions[$key]) > 0) {
            return collect($masterOptions[$key])
                ->map(fn($v) => trim((string) $v))
                ->filter(fn($v) => $v !== '' && $v !== '-')
                ->unique()
                ->values();
        }
        if ($key === 'areas' && isset($areas)) {
            return collect($areas)
                ->map(fn($v) => trim((string) $v))
                ->filter(fn($v) => $v !== '' && $v !== '-')
                ->unique()
                ->values();
        }
        if ($key === 'lines' && isset($lines)) {
            return collect($lines)
                ->map(fn($v) => trim((string) $v))
                ->filter(fn($v) => $v !== '' && $v !== '-')
                ->unique()
                ->values();
        }
        return collect($default)
            ->map(fn($v) => trim((string) $v))
            ->filter(fn($v) => $v !== '' && $v !== '-')
            ->unique()
            ->values();
    };
  @endphp
  <script>
    function masterBatteryComponent() {
      return {
        openMasterModal: false,
        exportMasterModalOpen: false,
        records: @json($allMasterRecords ?? []),
        exportFilter: {
          start_date: '',
          end_date: '',
          area: 'ALL',
          line: 'ALL',
          status: 'ALL'
        },
        openExportMasterModal() {
          this.exportMasterModalOpen = true;
        },
        closeExportMasterModal() {
          this.exportMasterModalOpen = false;
        },
        submitExportMaster() {
          const params = new URLSearchParams();
          if (this.exportFilter.start_date) params.append('start_date', this.exportFilter.start_date);
          if (this.exportFilter.end_date) params.append('end_date', this.exportFilter.end_date);
          if (this.exportFilter.area && this.exportFilter.area !== 'ALL') params.append('area', this.exportFilter.area);
          if (this.exportFilter.line && this.exportFilter.line !== 'ALL') params.append('line', this.exportFilter.line);
          if (this.exportFilter.status && this.exportFilter.status !== 'ALL') params.append('status', this.exportFilter
            .status);
          window.location.href = '{{ route('master-data.battery.export') }}?' + params.toString();
          this.exportMasterModalOpen = false;
        },
        masterForm: {
          level: '',
          battery_id: '',
          area: '',
          line: '',
          machine_no: '',
          machine_name: '',
          maker: '',
          equipment_type: '',
          device: '',
          battery_model: '',
          battery_type: '',
          std_volt: '',
          install_date: '',
          replacement_cycle_month: '',
          next_replace_date: '',
          status_aktif: '',
          how_many: '1',
          number_of: '1',
          aggregate: '1'
        },

        // Dynamic Cascade Filter Getters (Sesuai Area yang dipilih)
        get filteredLines() {
          let list = this.records;
          const area = (this.masterForm.area || '').trim().toLowerCase();
          if (area !== '') {
            list = list.filter(r => r.area && r.area.toLowerCase() === area);
          }
          const items = list.map(r => r.line).filter(Boolean);
          return [...new Set(items)].sort();
        },

        get filteredExportLines() {
          let list = this.records;
          const area = (this.exportFilter.area || '').trim().toLowerCase();
          if (area !== '' && area !== 'all') {
            list = list.filter(r => r.area && r.area.toLowerCase() === area);
          }
          const items = list.map(r => r.line).filter(Boolean);
          return [...new Set(items)].sort();
        },

        get filteredMachineNos() {
          let list = this.records;
          const area = (this.masterForm.area || '').trim().toLowerCase();
          const line = (this.masterForm.line || '').trim().toLowerCase();
          if (area !== '') {
            list = list.filter(r => r.area && r.area.toLowerCase() === area);
          }
          if (line !== '') {
            list = list.filter(r => r.line && r.line.toLowerCase() === line);
          }
          const items = list.map(r => r.machine_no).filter(Boolean);
          return [...new Set(items)].sort();
        },

        get filteredMachineNames() {
          let list = this.records;
          const area = (this.masterForm.area || '').trim().toLowerCase();
          const line = (this.masterForm.line || '').trim().toLowerCase();
          if (area !== '') {
            list = list.filter(r => r.area && r.area.toLowerCase() === area);
          }
          if (line !== '') {
            list = list.filter(r => r.line && r.line.toLowerCase() === line);
          }
          const items = list.map(r => r.machine_name).filter(Boolean);
          return [...new Set(items)].sort();
        },

        get filteredLevels() {
          const defaults = ['1', '2', 'L1', 'L2', 'LEVEL 1', 'LEVEL 2'];
          let list = this.records;
          const area = (this.masterForm.area || '').trim().toLowerCase();
          if (area !== '') {
            list = list.filter(r => r.area && r.area.toLowerCase() === area);
          }
          const items = list.map(r => r.level).filter(Boolean);
          return [...new Set([...items, ...defaults])].sort();
        },

        get filteredBatteryIds() {
          let list = this.records;
          const area = (this.masterForm.area || '').trim().toLowerCase();
          const line = (this.masterForm.line || '').trim().toLowerCase();
          if (area !== '') {
            list = list.filter(r => r.area && r.area.toLowerCase() === area);
          }
          if (line !== '') {
            list = list.filter(r => r.line && r.line.toLowerCase() === line);
          }
          const items = list.map(r => r.battery_id).filter(Boolean);
          return [...new Set(items)].sort();
        },

        get filteredMakers() {
          let list = this.records;
          const area = (this.masterForm.area || '').trim().toLowerCase();
          if (area !== '') {
            list = list.filter(r => r.area && r.area.toLowerCase() === area);
          }
          const items = list.map(r => r.maker).filter(Boolean);
          return [...new Set(items)].sort();
        },

        get filteredEquipmentTypes() {
          let list = this.records;
          const area = (this.masterForm.area || '').trim().toLowerCase();
          const line = (this.masterForm.line || '').trim().toLowerCase();
          if (area !== '') {
            list = list.filter(r => r.area && r.area.toLowerCase() === area);
          }
          if (line !== '') {
            list = list.filter(r => r.line && r.line.toLowerCase() === line);
          }
          const items = list.map(r => r.equipment_type).filter(Boolean);
          return [...new Set(items)].sort();
        },

        get filteredDevices() {
          let list = this.records;
          const area = (this.masterForm.area || '').trim().toLowerCase();
          if (area !== '') {
            list = list.filter(r => r.area && r.area.toLowerCase() === area);
          }
          const items = list.map(r => r.device).filter(Boolean);
          return [...new Set(items)].sort();
        },

        get filteredBatteryModels() {
          let list = this.records;
          const area = (this.masterForm.area || '').trim().toLowerCase();
          if (area !== '') {
            list = list.filter(r => r.area && r.area.toLowerCase() === area);
          }
          const items = list.map(r => r.battery_model).filter(Boolean);
          return [...new Set(items)].sort();
        },

        get filteredBatteryTypes() {
          const defaults = ['Lithium', 'Alkali', 'NiMH', 'Lead Acid', 'Zinc-Air', 'CR2032', 'Others'];
          let list = this.records;
          const area = (this.masterForm.area || '').trim().toLowerCase();
          if (area !== '') {
            list = list.filter(r => r.area && r.area.toLowerCase() === area);
          }
          const items = list.map(r => r.battery_type).filter(Boolean);
          return [...new Set([...items, ...defaults])].sort();
        },

        get filteredStdVolts() {
          const defaults = ['1.5', '3.0', '3.6', '6.0', '12'];
          let list = this.records;
          const area = (this.masterForm.area || '').trim().toLowerCase();
          if (area !== '') {
            list = list.filter(r => r.area && r.area.toLowerCase() === area);
          }
          const items = list.map(r => r.std_volt).filter(Boolean);
          return [...new Set([...items, ...defaults])].sort();
        },

        // Auto-suggest atau bantu isi data mesin yang cocok ketika Machine No dipilih
        onMachineSelected() {
          const op = (this.masterForm.machine_no || '').trim().toLowerCase();
          if (!op) return;
          const area = (this.masterForm.area || '').trim().toLowerCase();
          const line = (this.masterForm.line || '').trim().toLowerCase();

          const match = this.records.find(r => {
            const rOp = (r.machine_no || '').toLowerCase();
            const rArea = (r.area || '').toLowerCase();
            const rLine = (r.line || '').toLowerCase();
            if (rOp !== op) return false;
            if (area !== '' && rArea !== area) return false;
            if (line !== '' && rLine !== line) return false;
            return true;
          }) || this.records.find(r => (r.machine_no || '').toLowerCase() === op);

          if (match) {
            if (!this.masterForm.area && match.area) this.masterForm.area = match.area;
            if (!this.masterForm.line && match.line) this.masterForm.line = match.line;
            if (!this.masterForm.machine_name && match.machine_name) this.masterForm.machine_name = match.machine_name;
            if (!this.masterForm.maker && match.maker) this.masterForm.maker = match.maker;
            if (!this.masterForm.equipment_type && match.equipment_type) this.masterForm.equipment_type = match
              .equipment_type;
            if (!this.masterForm.device && match.device) this.masterForm.device = match.device;
            if (!this.masterForm.battery_model && match.battery_model) this.masterForm.battery_model = match
            .battery_model;
            if (!this.masterForm.battery_type && match.battery_type) this.masterForm.battery_type = match.battery_type;
            if (!this.masterForm.std_volt && match.std_volt) this.masterForm.std_volt = match.std_volt;
            if (!this.masterForm.level && match.level) this.masterForm.level = match.level;
            if (!this.masterForm.battery_id && match.battery_id) this.masterForm.battery_id = match.battery_id;
            if (!this.masterForm.replacement_cycle_month && match.replacement_cycle_month) {
              this.masterForm.replacement_cycle_month = match.replacement_cycle_month;
              this.calculateNextDate();
            }
          }
        },

        calculateNextDate() {
          if (this.masterForm.install_date && this.masterForm.replacement_cycle_month > 0) {
            let d = new Date(this.masterForm.install_date);
            d.setMonth(d.getMonth() + parseInt(this.masterForm.replacement_cycle_month));
            let y = d.getFullYear();
            let m = String(d.getMonth() + 1).padStart(2, '0');
            let day = String(d.getDate()).padStart(2, '0');
            this.masterForm.next_replace_date = `${y}-${m}-${day}`;
          }
        },

        resetMasterForm() {
          this.masterForm = {
            level: '',
            battery_id: '',
            area: '',
            line: '',
            machine_no: '',
            machine_name: '',
            maker: '',
            equipment_type: '',
            device: '',
            battery_model: '',
            battery_type: '',
            std_volt: '',
            install_date: '',
            replacement_cycle_month: '',
            next_replace_date: '',
            status_aktif: '',
            how_many: '1',
            number_of: '1',
            aggregate: '1'
          };
        }
      };
    }
  </script>

  <div class="space-y-6 text-slate-100" x-data="masterBatteryComponent()">

    <!-- Alert Notifications -->
    @if (session('success'))
      <div
        class="p-4 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 flex items-center justify-between shadow-lg animate-in fade-in duration-200">
        <div class="flex items-center gap-3">
          <div class="p-1 rounded-lg bg-emerald-500/20 text-emerald-400">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <span class="text-sm font-semibold">{{ session('success') }}</span>
        </div>
        <button type="button" @click="$el.parentElement.remove()"
          class="text-emerald-400 hover:text-white p-1 rounded-lg hover:bg-emerald-500/20 transition">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    @endif

    @if (isset($errors) && $errors->any())
      <div
        class="p-4 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 flex items-center justify-between shadow-lg animate-in fade-in duration-200">
        <div class="flex items-center gap-3">
          <div class="p-1 rounded-lg bg-rose-500/20 text-rose-400">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <span class="text-sm font-semibold">{{ $errors->first() }}</span>
        </div>
        <button type="button" @click="$el.parentElement.remove()"
          class="text-rose-400 hover:text-white p-1 rounded-lg hover:bg-rose-500/20 transition">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    @endif

    <!-- 1. HEADER & KPI CARDS -->
    <div
      class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl">
      <div>
        <div class="flex items-center gap-2.5">
          <div class="p-2.5 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
          <div>
            <h2 class="text-xl font-bold text-white tracking-wide">Master Data Battery (Sheet 1)</h2>
            <p class="text-xs text-slate-400 mt-0.5">
              Daftar master aset baterai mesin seluruh area &bull; Total: <span
                class="text-cyan-400 font-bold font-mono">{{ number_format($stats['total']) }} Unit</span>
            </p>
          </div>
        </div>
      </div>

      <!-- Quick Action (Export & Input Berdampingan Rapi) -->
      <div class="flex flex-wrap items-center gap-2.5">
        <!-- Tombol Export ke Excel (Sheet MST_Battery) -->
        @hasPermission('battery_export')
        <button type="button" @click="openExportMasterModal()"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-xs shadow-lg shadow-emerald-600/30 transition cursor-pointer"
          title="Export data master battery ke Excel (.xlsx)">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          <span>Export ke Excel</span>
        </button>
        @endhasPermission

        <!-- Tombol Input Data Master Battery -->
        @hasPermission('master_input')
        <button type="button" @click="resetMasterForm(); openMasterModal = true"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-xs shadow-lg shadow-indigo-600/30 transition cursor-pointer"
          title="Tambah data aset master baterai baru (Sheet 1)">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span>Input Data Master Battery</span>
        </button>
        @endhasPermission

        <!-- Tombol Input Penggantian Battery -->
        @hasPermission('battery_input')
        <a href="{{ route('input.battery') }}"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-medium text-xs shadow-lg shadow-purple-600/30 transition"
          title="Pindah ke Form Input Penggantian Battery (Sheet 2)">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span>Input Penggantian Battery</span>
        </a>
        @endhasPermission
      </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="p-4 rounded-xl bg-slate-900/80 border border-slate-800 flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center text-slate-300 font-bold shrink-0">
          <svg class="w-5 h-5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
          </svg>
        </div>
        <div>
          <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Total Master</span>
          <span class="text-lg font-bold text-white font-mono">{{ number_format($stats['total']) }}</span>
        </div>
      </div>

      <div class="p-4 rounded-xl bg-slate-900/80 border border-slate-800 flex items-center gap-3.5">
        <div
          class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 shrink-0">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div>
          <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Status Active</span>
          <span class="text-lg font-bold text-emerald-400 font-mono">{{ number_format($stats['total_active']) }}</span>
        </div>
      </div>

      <div class="p-4 rounded-xl bg-slate-900/80 border border-slate-800 flex items-center gap-3.5">
        <div
          class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400 shrink-0">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <div>
          <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Perlu Ganti</span>
          <span class="text-lg font-bold text-rose-400 font-mono">{{ number_format($stats['total_change']) }}</span>
        </div>
      </div>

      <div class="p-4 rounded-xl bg-slate-900/80 border border-slate-800 flex items-center gap-3.5">
        <div
          class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 shrink-0">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div>
          <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Status Warning</span>
          <span class="text-lg font-bold text-amber-400 font-mono">{{ number_format($stats['total_warning']) }}</span>
        </div>
      </div>
    </div>

    <!-- 2. FILTER FORM -->
    <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 shadow-lg">
      <form method="GET" action="{{ route('master-data.battery') }}"
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <!-- Filter Area -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Filter Area</label>
          <select name="area" onchange="if(this.form.line) this.form.line.value=''; this.form.submit();"
            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white outline-none focus:ring-1 focus:ring-cyan-500 cursor-pointer">
            <option value="">-- Semua Area --</option>
            @foreach ($areas as $a)
              <option value="{{ $a }}" {{ request('area') == $a ? 'selected' : '' }}>{{ $a }}
              </option>
            @endforeach
          </select>
        </div>

        <!-- Filter Line -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">
            Filter Line
            @if (request('area'))
              <span class="text-[10px] text-cyan-400 font-normal lowercase">({{ request('area') }})</span>
            @endif
          </label>
          <select name="line" onchange="this.form.submit()"
            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white outline-none focus:ring-1 focus:ring-cyan-500 cursor-pointer">
            <option value="">-- Semua Line --</option>
            @foreach ($lines as $l)
              <option value="{{ $l }}" {{ request('line') == $l ? 'selected' : '' }}>{{ $l }}
              </option>
            @endforeach
          </select>
        </div>

        <!-- Filter Status -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Status</label>
          <select name="status" onchange="this.form.submit()"
            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white outline-none focus:ring-1 focus:ring-cyan-500">
            <option value="">-- Semua Status --</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="change" {{ request('status') == 'change' ? 'selected' : '' }}>Change (Perlu Ganti)</option>
            <option value="warning" {{ request('status') == 'warning' ? 'selected' : '' }}>Warning</option>
          </select>
        </div>

        <!-- Search Input -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Pencarian</label>
          <div class="relative">
            <input type="text" name="search" value="{{ request('search') }}"
              placeholder="Cari OP, Mesin, Model..."
              class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-9 pr-3 py-2 text-xs text-white placeholder-slate-500 outline-none focus:ring-1 focus:ring-cyan-500">
            <svg class="w-4 h-4 text-slate-500 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
      </form>
    </div>

    <!-- 3. TABLE DATA -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 shadow-xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-300">
          <thead
            class="bg-slate-950/80 text-slate-400 uppercase font-bold text-[11px] tracking-wider border-b border-slate-800">
            <tr>
              <th class="py-3 px-3 text-center w-12">No</th>
              <th class="py-3 px-3">Area</th>
              <th class="py-3 px-3">Line</th>
              <th class="py-3 px-3">OP Number</th>
              <th class="py-3 px-3">Machine No</th>
              <th class="py-3 px-3">Equipment Type</th>
              <th class="py-3 px-3">Battery Model</th>
              <th class="py-3 px-3 text-center">Std Volt</th>
              <th class="py-3 px-3 text-center">Install Date</th>
              <th class="py-3 px-3 text-center">Cycle (Mo)</th>
              <th class="py-3 px-3 text-center">Next Replace</th>
              <th class="py-3 px-3 text-center">Status</th>
              <th class="py-3 px-3 text-center">Input By</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/60">
            @forelse($batteries as $index => $b)
              <tr class="hover:bg-slate-800/40 transition">
                <td class="py-3 px-3 text-center font-mono text-slate-500">{{ $batteries->firstItem() + $index }}</td>
                <td class="py-3 px-3 font-medium text-white">{{ $b->area ?: '-' }}</td>
                <td class="py-3 px-3 font-semibold text-cyan-400">{{ $b->line ?: '-' }}</td>
                <td class="py-3 px-3 font-bold text-amber-300">{{ $b->machine_no ?: ($b->op_number ?: '-') }}</td>
                <td class="py-3 px-3 font-mono text-slate-300">{{ $b->machine_name ?: '-' }}</td>
                <td class="py-3 px-3 text-slate-300">{{ $b->equipment_type ?: '-' }}</td>
                <td class="py-3 px-3 font-mono text-indigo-300">{{ $b->battery_model ?: '-' }}</td>
                <td class="py-3 px-3 text-center font-mono font-semibold text-slate-200">
                  {{ $b->std_volt ? $b->std_volt . ' V' : '-' }}</td>
                <td class="py-3 px-3 text-center text-slate-400">
                  {{ $b->install_date ? \Carbon\Carbon::parse($b->install_date)->format('d/m/Y') : '-' }}</td>
                <td class="py-3 px-3 text-center font-mono text-slate-400">
                  {{ $b->replacement_cycle_month ? $b->replacement_cycle_month . ' bln' : '-' }}</td>
                <td class="py-3 px-3 text-center font-mono text-slate-300">
                  {{ $b->next_replace_date ? \Carbon\Carbon::parse($b->next_replace_date)->format('d/m/Y') : '-' }}</td>
                <td class="py-3 px-3 text-center">
                  @php
                    $status = strtolower($b->status_aktif ?? 'active');
                  @endphp
                  @if ($status === 'change' || $status === 'rusak' || $status === 'danger')
                    <span
                      class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/15 text-rose-400 border border-rose-500/30">
                      Perlu Ganti
                    </span>
                  @elseif($status === 'warning')
                    <span
                      class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-400 border border-amber-500/30">
                      Warning
                    </span>
                  @else
                    <span
                      class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                      Active
                    </span>
                  @endif
                </td>
                <td class="py-3 px-3 text-center">
                  <span
                    class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-800 text-slate-300 border border-slate-700"
                    title="Diinput oleh {{ $b->created_by ?: 'System' }} pada {{ $b->created_at ? $b->created_at->format('d/m/Y H:i') : '-' }}">
                    <svg class="w-3 h-3 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>{{ $b->created_by ?: 'System' }}</span>
                  </span>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="13" class="py-8 text-center text-slate-500">
                  Tidak ada data master battery yang cocok dengan kriteria filter.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div
        class="p-4 border-t border-slate-800 bg-slate-950/40 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
        <div>
          Menampilkan <span class="font-bold text-white">{{ $batteries->firstItem() ?? 0 }}</span> sampai <span
            class="font-bold text-white">{{ $batteries->lastItem() ?? 0 }}</span> dari <span
            class="font-bold text-white">{{ $batteries->total() }}</span> total aset
        </div>
        <div>
          {{ $batteries->links() }}
        </div>
      </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL POPUP INPUT DATA MASTER BATTERY (SHEET 1) -->
    <!-- ========================================================================= -->
    <div x-show="openMasterModal" x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
      x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
      x-transition:leave-end="opacity-0"
      class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-slate-950/80 backdrop-blur-md overflow-y-auto"
      style="display: none;">
      <div @click.away="openMasterModal = false" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        class="bg-slate-900 border border-slate-700/80 rounded-2xl max-w-4xl w-full shadow-2xl overflow-hidden my-8">
        <!-- Modal Header -->
        <div class="p-5 sm:p-6 border-b border-slate-800 bg-slate-950/60 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="p-2.5 rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
              <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
            <div>
              <h3 class="text-base sm:text-lg font-bold text-white">Form Input Data Master Battery</h3>
              <div class="flex flex-wrap items-center gap-2 mt-1">
                <p class="text-xs text-slate-400">Tambah aset master baterai baru ke sheet mst_battery (Sheet 1)</p>
                <span class="text-slate-600 hidden sm:inline">&bull;</span>
                <span
                  class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-cyan-500/10 border border-cyan-500/30 text-cyan-400 font-mono text-[11px] font-semibold">
                  <svg class="w-3 h-3 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  <span>Input By: <strong>{{ Auth::user()->name ?? (session('user_name') ?: 'Admin') }}</strong></span>
                </span>
              </div>
            </div>
          </div>

          <button type="button" @click="openMasterModal = false"
            class="text-slate-400 hover:text-white p-2 rounded-xl hover:bg-slate-800 transition cursor-pointer"
            title="Tutup Modal">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Form Body -->
        <form action="{{ route('master-data.battery.store') }}" method="POST"
          class="p-6 sm:p-8 space-y-6 max-h-[75vh] overflow-y-auto custom-scrollbar">
          @csrf
          <input type="hidden" name="created_by"
            value="{{ Auth::user()->name ?? (session('user_name') ?: 'Admin') }}">

          <!-- SECTION 1: IDENTITAS MESIN & LOKASI -->
          <div class="space-y-4">
            <div
              class="flex items-center gap-2 pb-2 border-b border-slate-800 text-xs font-bold font-mono uppercase tracking-wider text-indigo-400">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              <span>1. Lokasi & Identitas Mesin</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <!-- Area (Anti Double Data & Bisa Pilih / Tulis) -->
              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
                  <span>Area <span class="text-rose-400">*</span></span>
                  <span class="text-[10px] text-cyan-400">Pilih / Tulis</span>
                </label>
                <input type="text" name="area" x-model="masterForm.area" list="masterFormAreaList" required
                  placeholder="Pilih atau ketik area..." onclick="try { this.showPicker(); } catch(e){}"
                  class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition cursor-pointer">
                <datalist id="masterFormAreaList">
                  @foreach ($opt('areas') as $item)
                    <option value="{{ $item }}">{{ $item }}</option>
                  @endforeach
                </datalist>
              </div>

              <!-- Line (Cascade sesuai Area & Bisa Pilih / Tulis) -->
              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
                  <span>Line <span class="text-rose-400">*</span></span>
                  <span class="text-[10px] text-cyan-400"
                    x-text="masterForm.area ? 'Sesuai Area &bull; Tulis' : 'Pilih / Tulis'">Pilih / Tulis</span>
                </label>
                <input type="text" name="line" x-model="masterForm.line" list="masterFormLineList" required
                  placeholder="Pilih atau ketik line (Contoh: CYLINDER BLOCK, AXLE 13...)"
                  onclick="try { this.showPicker(); } catch(e){}"
                  class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition cursor-pointer">
                <datalist id="masterFormLineList">
                  <template x-for="item in filteredLines" :key="item">
                    <option :value="item" x-text="item"></option>
                  </template>
                </datalist>
              </div>

              <!-- Machine No (OP Number) (Cascade sesuai Area/Line & Auto-Fill & Bisa Ketik Bebas) -->
              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
                  <span>Machine No (OP Number)</span>
                  <span class="text-[10px] text-cyan-400"
                    x-text="masterForm.area ? 'Sesuai Area &bull; Tulis' : 'Pilih / Tulis'">Pilih / Tulis</span>
                </label>
                <input type="text" name="machine_no" x-model="masterForm.machine_no" @input="onMachineSelected()"
                  @change="onMachineSelected()" list="masterFormMachineNoList"
                  placeholder="Pilih atau ketik no mesin (Contoh: OP-120, 1MC2...)"
                  onclick="try { this.showPicker(); } catch(e){}"
                  class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition cursor-pointer">
                <datalist id="masterFormMachineNoList">
                  <template x-for="item in filteredMachineNos" :key="item">
                    <option :value="item" x-text="item"></option>
                  </template>
                </datalist>
              </div>

              <!-- Machine Name (Cascade sesuai Area & Bisa Pilih / Tulis) -->
              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
                  <span>Machine Name</span>
                  <span class="text-[10px] text-cyan-400">Pilih / Tulis</span>
                </label>
                <input type="text" name="machine_name" x-model="masterForm.machine_name"
                  list="masterFormMachineNameList"
                  placeholder="Pilih atau ketik nama mesin (Contoh: 6-MCV-01, CNC Lathe...)"
                  onclick="try { this.showPicker(); } catch(e){}"
                  class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition cursor-pointer">
                <datalist id="masterFormMachineNameList">
                  <template x-for="item in filteredMachineNames" :key="item">
                    <option :value="item" x-text="item"></option>
                  </template>
                </datalist>
              </div>

              <!-- LEVEL (Bisa Pilih / Tulis) -->
              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
                  <span>LEVEL</span>
                  <span class="text-[10px] text-cyan-400">Pilih / Tulis</span>
                </label>
                <input type="text" name="level" x-model="masterForm.level" list="masterFormLevelList"
                  placeholder="Pilih atau ketik level (Contoh: 1, 2, L1...)"
                  onclick="try { this.showPicker(); } catch(e){}"
                  class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition cursor-pointer">
                <datalist id="masterFormLevelList">
                  <template x-for="item in filteredLevels" :key="item">
                    <option :value="item" x-text="item"></option>
                  </template>
                </datalist>
              </div>

              <!-- Battery ID (Cascade sesuai Area & Bisa Pilih / Tulis) -->
              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
                  <span>Battery ID</span>
                  <span class="text-[10px] text-cyan-400">Pilih / Tulis</span>
                </label>
                <input type="text" name="battery_id" x-model="masterForm.battery_id" list="masterFormBatteryIdList"
                  placeholder="Pilih atau ketik Battery ID (Contoh: BATT-001, BAT-12...)"
                  onclick="try { this.showPicker(); } catch(e){}"
                  class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition cursor-pointer">
                <datalist id="masterFormBatteryIdList">
                  <template x-for="item in filteredBatteryIds" :key="item">
                    <option :value="item" x-text="item"></option>
                  </template>
                </datalist>
              </div>
            </div>
          </div>

          <!-- SECTION 2: SPESIFIKASI PERANGKAT & BATERAI -->
          <div class="space-y-4">
            <div
              class="flex items-center gap-2 pb-2 border-b border-slate-800 text-xs font-bold font-mono uppercase tracking-wider text-cyan-400">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
              </svg>
              <span>2. Spesifikasi Perangkat & Baterai</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <!-- Maker (Cascade sesuai Area & Bisa Pilih / Tulis) -->
              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
                  <span>Maker</span>
                  <span class="text-[10px] text-cyan-400">Pilih / Tulis</span>
                </label>
                <input type="text" name="maker" x-model="masterForm.maker" list="masterFormMakerList"
                  placeholder="Pilih atau ketik maker (Contoh: FANUC, MITSUBISHI...)"
                  onclick="try { this.showPicker(); } catch(e){}"
                  class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 outline-none transition cursor-pointer">
                <datalist id="masterFormMakerList">
                  <template x-for="item in filteredMakers" :key="item">
                    <option :value="item" x-text="item"></option>
                  </template>
                </datalist>
              </div>

              <!-- Equipment Type (Cascade sesuai Area & Bisa Pilih / Tulis) -->
              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
                  <span>Equipment Type</span>
                  <span class="text-[10px] text-cyan-400">Pilih / Tulis</span>
                </label>
                <input type="text" name="equipment_type" x-model="masterForm.equipment_type"
                  list="masterFormEquipmentTypeList"
                  placeholder="Pilih atau ketik tipe equipment (Contoh: ROBOT, Screen LCD...)"
                  onclick="try { this.showPicker(); } catch(e){}"
                  class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 outline-none transition cursor-pointer">
                <datalist id="masterFormEquipmentTypeList">
                  <template x-for="item in filteredEquipmentTypes" :key="item">
                    <option :value="item" x-text="item"></option>
                  </template>
                </datalist>
              </div>

              <!-- Device (Cascade sesuai Area & Bisa Pilih / Tulis) -->
              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
                  <span>Device</span>
                  <span class="text-[10px] text-cyan-400">Pilih / Tulis</span>
                </label>
                <input type="text" name="device" x-model="masterForm.device" list="masterFormDeviceList"
                  placeholder="Pilih atau ketik device (Contoh: CNC, PLC, Inverter...)"
                  onclick="try { this.showPicker(); } catch(e){}"
                  class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 outline-none transition cursor-pointer">
                <datalist id="masterFormDeviceList">
                  <template x-for="item in filteredDevices" :key="item">
                    <option :value="item" x-text="item"></option>
                  </template>
                </datalist>
              </div>

              <!-- Battery Model (Cascade sesuai Area & Bisa Pilih / Tulis) -->
              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
                  <span>Battery Model</span>
                  <span class="text-[10px] text-cyan-400">Pilih / Tulis</span>
                </label>
                <input type="text" name="battery_model" x-model="masterForm.battery_model"
                  list="masterFormBatteryModelList"
                  placeholder="Pilih atau ketik model baterai (Contoh: ER-6 /3,6V, SIZE D...)"
                  onclick="try { this.showPicker(); } catch(e){}"
                  class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 outline-none transition font-mono cursor-pointer">
                <datalist id="masterFormBatteryModelList">
                  <template x-for="item in filteredBatteryModels" :key="item">
                    <option :value="item" x-text="item"></option>
                  </template>
                </datalist>
              </div>

              <!-- Battery Type (Bisa Pilih Opsi Master Lainnya & Bisa Ketik Bebas) -->
              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
                  <span>Battery Type</span>
                  <span class="text-[10px] text-cyan-400">Pilih / Tulis Bebas</span>
                </label>
                <input type="text" name="battery_type" x-model="masterForm.battery_type"
                  list="masterFormBatteryTypeList"
                  placeholder="Pilih atau ketik tipe baterai (Lithium, Alkali, NiMH, CR2032...)"
                  onclick="try { this.showPicker(); } catch(e){}"
                  class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 outline-none transition font-mono cursor-pointer">
                <datalist id="masterFormBatteryTypeList">
                  <template x-for="item in filteredBatteryTypes" :key="item">
                    <option :value="item" x-text="item"></option>
                  </template>
                </datalist>
              </div>

              <!-- Std Volt (Cascade sesuai Area & Bisa Pilih / Tulis) -->
              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
                  <span>Std Volt (V)</span>
                  <span class="text-[10px] text-cyan-400">Pilih / Tulis</span>
                </label>
                <input type="text" name="std_volt" x-model="masterForm.std_volt" list="masterFormStdVoltList"
                  placeholder="Pilih atau ketik standar volt (Contoh: 3.6, 1.5, 3.0, 6...)"
                  onclick="try { this.showPicker(); } catch(e){}"
                  class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 outline-none transition font-mono cursor-pointer">
                <datalist id="masterFormStdVoltList">
                  <template x-for="item in filteredStdVolts" :key="item">
                    <option :value="item" x-text="item"></option>
                  </template>
                </datalist>
              </div>
            </div>
          </div>

          <!-- SECTION 3: SIKLUS PENGGANTIAN & STATUS -->
          <div class="space-y-4">
            <div
              class="flex items-center gap-2 pb-2 border-b border-slate-800 text-xs font-bold font-mono uppercase tracking-wider text-amber-400">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span>3. Siklus Penggantian & Status</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <!-- Install Date (Kalender / Ketik Manual) -->
              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
                  <span>Install Date</span>
                  <span class="text-[10px] text-slate-500">Pilih / Tulis</span>
                </label>
                <input type="date" name="install_date" x-model="masterForm.install_date"
                  @change="calculateNextDate()" onclick="try { this.showPicker(); } catch(e){}"
                  style="color-scheme: dark;"
                  class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none transition font-mono cursor-pointer">
              </div>

              <!-- Replacement Cycle Month (Bisa Pilih / Tulis) -->
              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
                  <span>Cycle (Bulan)</span>
                  <span class="text-[10px] text-slate-500">Pilih / Tulis</span>
                </label>
                <input type="number" min="1" name="replacement_cycle_month"
                  x-model="masterForm.replacement_cycle_month" @input="calculateNextDate()" list="masterFormCycleList"
                  placeholder="Pilih atau ketik bulan (Contoh: 12, 24...)"
                  onclick="try { this.showPicker(); } catch(e){}"
                  class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none transition font-mono cursor-pointer">
                <datalist id="masterFormCycleList">
                  @foreach ($opt('cycles', ['6', '12', '18', '24', '36', '48', '60']) as $item)
                    <option value="{{ $item }}">{{ $item }} Bulan</option>
                  @endforeach
                </datalist>
              </div>

              <!-- Next Replace Date (Otomatis / Kalender / Ketik Manual) -->
              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
                  <span>Next Replace Date</span>
                  <span class="text-[10px] text-amber-400 font-normal">(Otomatis/Manual)</span>
                </label>
                <input type="date" name="next_replace_date" x-model="masterForm.next_replace_date"
                  onclick="try { this.showPicker(); } catch(e){}" style="color-scheme: dark;"
                  class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none transition font-mono cursor-pointer">
              </div>

              <!-- Status Aktif (Bisa Pilih / Tulis) -->
              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
                  <span>Status Aktif</span>
                  <span class="text-[10px] text-slate-500">Pilih / Tulis</span>
                </label>
                <input type="text" name="status_aktif" x-model="masterForm.status_aktif"
                  list="masterFormStatusAktifList" placeholder="Pilih atau ketik status (active/change/warning)..."
                  onclick="try { this.showPicker(); } catch(e){}"
                  class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none transition cursor-pointer">
                <datalist id="masterFormStatusAktifList">
                  <option value="active">Active (Normal)</option>
                  <option value="change">Change (Perlu Ganti)</option>
                  <option value="warning">Warning</option>
                  <option value="error">Error</option>
                </datalist>
              </div>
            </div>
          </div>

          <!-- SECTION 4: KUANTITAS & AGREGASI -->
          <div class="space-y-4">
            <div
              class="flex items-center gap-2 pb-2 border-b border-slate-800 text-xs font-bold font-mono uppercase tracking-wider text-emerald-400">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
              </svg>
              <span>4. Kuantitas & Agregasi</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <!-- How many (Bisa Pilih / Tulis) -->
              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
                  <span>How many</span>
                  <span class="text-[10px] text-slate-500">Pilih / Tulis</span>
                </label>
                <input type="text" name="how_many" x-model="masterForm.how_many" list="masterFormHowManyList"
                  placeholder="Pilih atau ketik jumlah (Contoh: 1, 2, 4)"
                  onclick="try { this.showPicker(); } catch(e){}"
                  class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition font-mono cursor-pointer">
                <datalist id="masterFormHowManyList">
                  @foreach ($opt('how_manys', ['1', '2', '3', '4', '6']) as $item)
                    <option value="{{ $item }}">{{ $item }}</option>
                  @endforeach
                </datalist>
              </div>

              <!-- Number of machines (Bisa Pilih / Tulis) -->
              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
                  <span>Number of machines</span>
                  <span class="text-[10px] text-slate-500">Pilih / Tulis</span>
                </label>
                <input type="text" name="number_of" x-model="masterForm.number_of" list="masterFormNumberOfList"
                  placeholder="Pilih atau ketik jumlah mesin (Contoh: 1, 2)"
                  onclick="try { this.showPicker(); } catch(e){}"
                  class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition font-mono cursor-pointer">
                <datalist id="masterFormNumberOfList">
                  @foreach ($opt('number_ofs', ['1', '2', '3', '4']) as $item)
                    <option value="{{ $item }}">{{ $item }}</option>
                  @endforeach
                </datalist>
              </div>

              <!-- Aggregate (Total Device) (Bisa Pilih / Tulis) -->
              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
                  <span>Aggregate (Total Device)</span>
                  <span class="text-[10px] text-slate-500">Pilih / Tulis</span>
                </label>
                <input type="text" name="aggregate" x-model="masterForm.aggregate" list="masterFormAggregateList"
                  placeholder="Pilih atau ketik total device (Contoh: 1, 2)"
                  onclick="try { this.showPicker(); } catch(e){}"
                  class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition font-mono cursor-pointer">
                <datalist id="masterFormAggregateList">
                  @foreach ($opt('aggregates', ['1', '2', '3', '4']) as $item)
                    <option value="{{ $item }}">{{ $item }}</option>
                  @endforeach
                </datalist>
              </div>
            </div>
          </div>

          <!-- Form Actions -->
          <div
            class="p-4 border-t border-slate-800 bg-slate-950/40 rounded-xl flex flex-col sm:flex-row items-center justify-between gap-3">
            <button type="button" @click="resetMasterForm()"
              class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-semibold transition border border-slate-700 cursor-pointer w-full sm:w-auto">
              Reset Form
            </button>

            <div class="flex items-center gap-3 w-full sm:w-auto">
              <button type="button" @click="openMasterModal = false"
                class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-semibold transition border border-slate-700 cursor-pointer w-1/2 sm:w-auto">
                Batal
              </button>
              <button type="submit"
                class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 transition flex items-center justify-center gap-2 cursor-pointer w-1/2 sm:w-auto">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Simpan Master Battery</span>
              </button>
            </div>
          </div>

        </form>
      </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL FILTER EXPORT EXCEL (RENTANG WAKTU / MUTASI & PILIHAN AREA)           -->
    <!-- ========================================================================= -->
    <div x-show="exportMasterModalOpen" x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
      x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
      x-transition:leave-end="opacity-0"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
      style="display: none;">
      <div @click.away="closeExportMasterModal()"
        class="bg-slate-900 border border-slate-700/80 rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-150">
        <!-- Header Modal Export -->
        <div class="p-5 border-b border-slate-800 bg-slate-950/60 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <div>
              <h3 class="text-base font-bold text-white">Export Master Battery ke Excel</h3>
              <p class="text-xs text-slate-400">Sheet: <span
                  class="text-emerald-400 font-mono font-semibold">MST_Battery</span> &bull; Filter mutasi & area</p>
            </div>
          </div>
          <button type="button" @click="closeExportMasterModal()"
            class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Body Form Filter Export -->
        <div class="p-6 space-y-4">
          <!-- Pilihan Rentang Waktu (Mutasi) -->
          <div>
            <label
              class="block text-xs font-bold text-slate-300 uppercase tracking-wider font-mono mb-2 flex items-center gap-2">
              <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span>Rentang Waktu Mutasi (Install / Next Replace Date)</span>
            </label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <span class="text-[11px] text-slate-400 block mb-1">Dari Tanggal:</span>
                <div class="relative">
                  <input type="date" id="masterExportStartDate" x-model="exportFilter.start_date"
                    onclick="try { this.showPicker(); } catch(e){}" style="color-scheme: dark;"
                    class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-3.5 py-2.5 pr-10 text-sm text-white font-mono focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none cursor-pointer" />
                  <button type="button"
                    onclick="try { document.getElementById('masterExportStartDate').showPicker(); } catch(e){}"
                    class="absolute right-3 top-2.5 text-slate-400 hover:text-emerald-400 transition cursor-pointer"
                    title="Buka Kalender">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </button>
                </div>
              </div>
              <div>
                <span class="text-[11px] text-slate-400 block mb-1">Sampai Tanggal:</span>
                <div class="relative">
                  <input type="date" id="masterExportEndDate" x-model="exportFilter.end_date"
                    onclick="try { this.showPicker(); } catch(e){}" style="color-scheme: dark;"
                    class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-3.5 py-2.5 pr-10 text-sm text-white font-mono focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none cursor-pointer" />
                  <button type="button"
                    onclick="try { document.getElementById('masterExportEndDate').showPicker(); } catch(e){}"
                    class="absolute right-3 top-2.5 text-slate-400 hover:text-emerald-400 transition cursor-pointer"
                    title="Buka Kalender">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
            <p class="text-[11px] text-slate-500 mt-1.5">* Kosongkan tanggal jika ingin mendownload seluruh data tanpa
              batasan waktu mutasi.</p>
          </div>

          <!-- Pilihan Area -->
          <div>
            <label
              class="block text-xs font-bold text-slate-300 uppercase tracking-wider font-mono mb-2 flex items-center gap-2">
              <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              <span>Pilihan Area</span>
            </label>
            <select x-model="exportFilter.area" @change="exportFilter.line = 'ALL'"
              class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-sm text-white font-mono focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none cursor-pointer">
              <option value="ALL">Semua Area (ALL AREAS)</option>
              @if (isset($areas))
                @foreach ($areas as $areaItem)
                  <option value="{{ $areaItem }}">{{ $areaItem }}</option>
                @endforeach
              @endif
            </select>
          </div>

          <!-- Pilihan Line & Status (Tambahan Filter) -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-slate-800">
            <div>
              <label
                class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1 flex items-center justify-between">
                <span>Pilihan Line</span>
                <span class="text-[10px] text-cyan-400 font-normal" x-show="exportFilter.area !== 'ALL'"
                  x-text="'Sesuai Area'"></span>
              </label>
              <select x-model="exportFilter.line"
                class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white font-mono focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none cursor-pointer">
                <option value="ALL">Semua Line (ALL LINES)</option>
                <template x-for="item in filteredExportLines" :key="item">
                  <option :value="item" x-text="item"></option>
                </template>
              </select>
            </div>

            <div>
              <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Status
                Aktif</label>
              <select x-model="exportFilter.status"
                class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white font-mono focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none cursor-pointer">
                <option value="ALL">Semua Status</option>
                <option value="active">Active (Normal)</option>
                <option value="change">Change (Perlu Ganti)</option>
                <option value="warning">Warning</option>
              </select>
            </div>
          </div>

        </div>

        <!-- Tombol Aksi Modal Export -->
        <div class="p-5 border-t border-slate-800 bg-slate-950/70 flex items-center justify-end gap-3">
          <button type="button" @click="closeExportMasterModal()"
            class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold transition border border-slate-700 cursor-pointer">
            Batal
          </button>
          <button type="button" @click="submitExportMaster()"
            class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-bold transition shadow-lg shadow-emerald-600/30 flex items-center gap-2 cursor-pointer">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            <span>Download Excel (.xlsx)</span>
          </button>
        </div>
      </div>
    </div>

  </div>
@endsection

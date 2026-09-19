@extends('layouts.alpineLayout')

@section('title', 'Battery Monitoring Dashboard')
@section('page-title', 'Battery Monitoring System')

@section('content')
  <div x-data="batteryDashboard()" x-init="initDashboard()" class="space-y-5 select-none font-sans text-slate-100">

    <!-- ========================================== -->
    <!-- 1. TOP HEADER & TELEMETRY ROW (TV STYLE)   -->
    <!-- ========================================== -->
    <div
      class="p-4 rounded-2xl bg-slate-900 border border-slate-800/80 shadow-2xl relative overflow-hidden backdrop-blur-md">
      <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute -top-24 -right-24 w-96 h-96 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>

      <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 items-start relative z-10">

        <!-- LEFT: STATION / AREA BADGE & CLOCK & KALENDER NASIONAL INDONESIA -->
        <div
          class="xl:col-span-5 flex flex-col justify-between border-b xl:border-b-0 xl:border-r border-slate-800/80 pb-4 xl:pb-0 xl:pr-4 space-y-3">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="p-2.5 rounded-xl bg-slate-950/80 border border-slate-800 text-center min-w-[140px] shadow-inner">
              <span class="text-[9px] font-mono tracking-widest text-indigo-400 uppercase block font-semibold">STATION /
                AREA</span>
              <span class="text-lg font-black tracking-wider text-white truncate block max-w-[160px]">
                {{ $selectedLine !== 'ALL' ? $selectedLine : ($selectedArea !== 'ALL' ? $selectedArea : 'ALL AREA') }}
              </span>
              <span class="inline-flex items-center gap-1.5 text-xs text-emerald-400 font-mono font-semibold mt-0.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> ONLINE
              </span>
            </div>

            <div class="text-right">
              <div
                class="text-2xl sm:text-3xl font-mono font-black text-cyan-400 tracking-wider drop-shadow-[0_0_12px_rgba(6,182,212,0.4)]"
                x-text="currentTime">
                14:08:54
              </div>
              <div class="text-xs font-bold text-slate-300 font-mono tracking-wider mt-0.5" x-text="currentDate">
                {{ now()->locale('id')->translatedFormat('d F Y') }}
              </div>
            </div>
          </div>

          <!-- DI BAWAH JAM: KALENDER NASIONAL INDONESIA (HARI LIBUR NASIONAL) -->
          <div class="p-3 rounded-xl bg-slate-950/70 border border-slate-800/90 shadow-inner">
            <div class="flex items-center justify-between mb-2 pb-1.5 border-b border-slate-800/70">
              <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                <span class="text-xs font-bold tracking-wider text-white font-mono uppercase">
                  {{ $currentMonthIndo }}
                </span>
              </div>
              <span
                class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-rose-950/60 text-rose-300 border border-rose-800/50">
                HARI LIBUR NASIONAL
              </span>
            </div>

            <div
              class="grid grid-cols-7 gap-1 text-center text-[11px] font-mono font-bold mb-1.5 pb-1 border-b border-slate-800/50">
              <span class="text-rose-400 font-black">MIN</span>
              <span class="text-slate-300">SEN</span>
              <span class="text-slate-300">SEL</span>
              <span class="text-slate-300">RAB</span>
              <span class="text-slate-300">KAM</span>
              <span class="text-slate-300">JUM</span>
              <span class="text-slate-300">SAB</span>
            </div>

            <div class="grid grid-cols-7 gap-1 text-center text-xs font-mono font-semibold">
              @for ($i = 0; $i < $firstDayOfMonth; $i++)
                <div class="p-1 text-slate-800/60 select-none">-</div>
              @endfor

              @for ($day = 1; $day <= $currentMonthDays; $day++)
                @php
                  $isToday = $day === now()->day;
                  $isSunday = ($firstDayOfMonth + $day - 1) % 7 === 0;
                  $holidayName = $currentMonthHolidays[$day] ?? null;
                  $isHoliday = !empty($holidayName);

                  if ($isToday) {
                      $dayStyle = 'bg-cyan-500/25 text-cyan-300 font-extrabold ring-1 ring-cyan-400 rounded-md';
                      $title =
                          "Hari Ini: {$day} {$currentMonthIndo}" .
                          ($isHoliday ? " ({$holidayName})" : ($isSunday ? ' (Hari Minggu)' : ''));
                  } elseif ($isHoliday) {
                      $dayStyle =
                          'bg-rose-500/20 text-rose-400 font-bold border border-rose-500/50 rounded-md animate-pulse';
                      $title = "Tanggal {$day} {$currentMonthIndo}: {$holidayName} (Libur Nasional)";
                  } elseif ($isSunday) {
                      $dayStyle = 'text-rose-400 font-bold hover:bg-rose-500/10 rounded-md';
                      $title = "Tanggal {$day} {$currentMonthIndo}: Hari Minggu";
                  } else {
                      $dayStyle = 'text-slate-200 hover:bg-slate-800/70 hover:text-white rounded-md';
                      $title = "Tanggal {$day} {$currentMonthIndo}";
                  }
                @endphp
                <div
                  class="p-1 flex flex-col items-center justify-center transition-all cursor-default {{ $dayStyle }}"
                  title="{{ $title }}">
                  <span class="leading-none text-xs font-bold">{{ $day }}</span>
                  @if ($isHoliday)
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mt-0.5"></span>
                  @endif
                </div>
              @endfor
            </div>

            <div
              class="mt-2.5 pt-2 border-t border-slate-800/70 flex flex-wrap items-center justify-between text-[10px] font-mono text-slate-400 gap-1">
              <span class="flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span> Tanggal Merah
              </span>
              <span class="flex items-center gap-1 text-cyan-300 font-semibold">
                <span class="w-2 h-2 rounded-full bg-cyan-400"></span> Hari Ini
              </span>
            </div>

            @if (count($currentMonthHolidays) > 0)
              <div class="mt-1.5 pt-1.5 border-t border-slate-800/50 text-[10px] font-mono text-rose-300 truncate">
                @foreach ($currentMonthHolidays as $hDay => $hName)
                  <span class="inline-block mr-2" title="{{ $hName }}">&bull; Tgl {{ $hDay }}:
                    {{ $hName }}</span>
                @endforeach
              </div>
            @endif
          </div>
        </div>

        <!-- RIGHT: TOP ROW METRICS & BOTTOM ROW STATUS RINGS -->
        <div class="xl:col-span-7 flex flex-col justify-between gap-3">
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 sm:gap-3 items-center">


            <button @click="openTotalBatteryModal()"
              class="text-center group bg-slate-950/50 border border-slate-800/80 hover:border-cyan-500/50 hover:bg-slate-800/40 p-3 rounded-xl transition-all cursor-pointer shadow-inner flex flex-col items-center justify-between h-full">
              <div
                class="text-xs font-bold text-slate-300 uppercase tracking-wider group-hover:text-cyan-400 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span> TOTAL BATTERY
              </div>
              <div class="my-1">
                <div
                  class="text-2xl sm:text-3xl font-mono font-black text-white group-hover:text-cyan-400 transition-colors">
                  {{ $totalBatteryQuantity }} <span class="text-xs font-semibold text-slate-400">UNIT</span>
                </div>
              </div>
              <div class="text-[11px] font-mono text-cyan-400/90 font-semibold truncate max-w-full">
                Jumlah Kolom How Many
              </div>
            </button>

            <div
              class="text-center bg-slate-950/50 border border-slate-800/80 hover:border-indigo-500/50 hover:bg-slate-800/40 p-3 rounded-xl transition-all shadow-inner flex flex-col items-center justify-between h-full group cursor-default">
              <div
                class="text-xs font-bold text-slate-300 uppercase tracking-wider group-hover:text-indigo-400 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-indigo-400"></span> JUMLAH MACHINE
              </div>
              <div class="my-1">
                <div
                  class="text-2xl sm:text-3xl font-mono font-black text-white group-hover:text-indigo-400 transition-colors">
                  {{ $totalNumberOfMachines > 0 ? $totalNumberOfMachines : $distinctMachines }} <span class="text-xs font-semibold text-slate-400">MESIN</span>
                </div>
              </div>
              <div class="text-[11px] font-mono text-indigo-400/90 font-semibold truncate max-w-full">
                Jumlah Kolom Number of Machines
              </div>
            </div>

            <button type="button" @click="openBatteryTypeModal()"
              class="bg-slate-950/50 border border-slate-800/80 hover:border-cyan-500/60 hover:bg-slate-800/50 p-3 rounded-xl transition-all shadow-inner flex flex-col justify-between h-full group text-left cursor-pointer hover:shadow-cyan-500/10">
              <div class="flex items-center justify-between px-1">
                <span
                  class="text-xs font-bold text-slate-300 uppercase tracking-wider group-hover:text-cyan-400 flex items-center gap-1.5">
                  <span class="w-2 h-2 rounded-full bg-cyan-400"></span> BATTERY TYPE
                </span>
                <span
                  class="text-[10px] font-mono font-bold text-cyan-400 bg-cyan-950/90 px-1.5 py-0.5 rounded border border-cyan-800/60">DETAIL
                  &rarr;</span>
              </div>

              <div class="flex items-center justify-center gap-2 my-1">
                <div class="relative w-10 h-10 shrink-0 flex items-center justify-center">
                  <canvas id="batteryTypeMiniDonutChart" class="max-w-[40px] max-h-[40px]"></canvas>
                </div>
                <div class="flex flex-col gap-0.5 text-left font-mono text-[11px]">
                  <div class="flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-cyan-400 shrink-0"></span>
                    <span class="text-slate-200">Lithium:</span>
                    <span class="text-cyan-300 font-bold ml-auto">{{ $lithiumRate }}%</span>
                  </div>
                  <div class="flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-amber-400 shrink-0"></span>
                    <span class="text-slate-200">Alkali:</span>
                    <span class="text-amber-300 font-bold ml-auto">{{ $alkaliRate }}%</span>
                  </div>
                </div>
              </div>

              <div
                class="text-[10px] font-mono font-semibold text-slate-300 flex items-center justify-between px-1 border-t border-slate-800/50 pt-1">
                <span>Li: {{ $lithiumCount }} Pcs</span>
                <span>Alk: {{ $alkaliCount }} Pcs</span>
              </div>
            </button>

          </div>

          <div class="grid grid-cols-3 gap-2.5 sm:gap-3 text-center">
            <div
              @click="openStatusModal('active', 'Status Baterai: Aktif (Healthy)', 'Monitoring grafik per line & daftar unit baterai status Aktif (Hijau)')"
              class="group cursor-pointer p-2.5 rounded-xl bg-slate-950/40 border border-slate-800/80 hover:border-emerald-500/50 hover:bg-slate-800/40 transition-all flex flex-col items-center justify-between shadow-inner">
              <div
                class="flex items-center gap-1 text-xs font-bold text-slate-200 tracking-wider group-hover:text-emerald-400">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> AKTIF
              </div>
              <div class="relative w-12 h-12 my-1 flex items-center justify-center">
                <svg class="w-12 h-12 -rotate-90" viewBox="0 0 36 36">
                  <path class="text-slate-800" stroke-width="3.5" stroke="currentColor" fill="none"
                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                  <path class="text-emerald-400 transition-all duration-1000" stroke-dasharray="{{ $healthRate }}, 100"
                    stroke-linecap="round" stroke-width="3.5" stroke="currentColor" fill="none"
                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                </svg>
                <span class="absolute font-mono text-[11px] font-black text-white">{{ $healthRate }}%</span>
              </div>
              <div class="text-xs font-mono text-emerald-400 font-bold">
                {{ $activeCount }} <span class="text-slate-400 text-[10px] font-normal">UNIT</span>
              </div>
            </div>

            <div
              @click="openStatusModal('change', 'Status Baterai: Change (Wajib Ganti)', 'Monitoring grafik per line & daftar unit baterai status Change (Sisa <= 30 Hari)')"
              class="group cursor-pointer p-2.5 rounded-xl bg-slate-950/40 border border-slate-800/80 hover:border-rose-500/50 hover:bg-slate-800/40 transition-all flex flex-col items-center justify-between shadow-inner">
              <div
                class="flex items-center gap-1 text-xs font-bold text-slate-200 tracking-wider group-hover:text-rose-400">
                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span> CHANGE
              </div>
              <div class="relative w-12 h-12 my-1 flex items-center justify-center">
                <svg class="w-12 h-12 -rotate-90" viewBox="0 0 36 36">
                  <path class="text-slate-800" stroke-width="3.5" stroke="currentColor" fill="none"
                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                  <path class="text-rose-500 transition-all duration-1000" stroke-dasharray="{{ $changeRate }}, 100"
                    stroke-linecap="round" stroke-width="3.5" stroke="currentColor" fill="none"
                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                </svg>
                <span class="absolute font-mono text-[11px] font-black text-white">{{ $changeRate }}%</span>
              </div>
              <div class="text-xs font-mono text-rose-400 font-bold">
                {{ $changeCount }} <span class="text-slate-400 text-[10px] font-normal">UNIT</span>
              </div>
            </div>

            <div
              @click="openStatusModal('warning', 'Status Baterai: Warning (Expired / Data Kurang)', 'Monitoring grafik per line & daftar unit baterai status Warning (Expired / Data Kurang)')"
              class="group cursor-pointer p-2.5 rounded-xl bg-slate-950/40 border border-slate-800/80 hover:border-amber-500/50 hover:bg-slate-800/40 transition-all flex flex-col items-center justify-between shadow-inner">
              <div
                class="flex items-center gap-1 text-xs font-bold text-slate-200 tracking-wider group-hover:text-amber-400">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span> WARNING
              </div>
              <div class="relative w-12 h-12 my-1 flex items-center justify-center">
                <svg class="w-12 h-12 -rotate-90" viewBox="0 0 36 36">
                  <path class="text-slate-800" stroke-width="3.5" stroke="currentColor" fill="none"
                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                  <path class="text-amber-400 transition-all duration-1000" stroke-dasharray="{{ $warningRate }}, 100"
                    stroke-linecap="round" stroke-width="3.5" stroke="currentColor" fill="none"
                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                </svg>
                <span class="absolute font-mono text-[11px] font-black text-white">{{ $warningRate }}%</span>
              </div>
              <div class="text-xs font-mono text-amber-400 font-bold">
                {{ $warningCount }} <span class="text-slate-400 text-[10px] font-normal">UNIT</span>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- ========================================== -->
    <!-- 2. MAIN KPI CARDS: DONUT MONITORING (5 CARDS) -->
    <!-- ========================================== -->
    @php
      $showMachiningCard = $selectedArea === 'ALL' || $areaMachiningStats['total'] > 0;
      $showShaftCard = $selectedArea === 'ALL' || $areaShaftStats['total'] > 0;
      $showEngineCard = $selectedArea === 'ALL' || $areaEngineStats['total'] > 0;
      $activeKpiCards = 2 + ($showMachiningCard ? 1 : 0) + ($showShaftCard ? 1 : 0) + ($showEngineCard ? 1 : 0);
    @endphp
    <div
      class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 {{ $activeKpiCards === 5 ? 'lg:grid-cols-5' : ($activeKpiCards === 4 ? 'lg:grid-cols-4' : 'lg:grid-cols-3') }} gap-4">

      <div @click="openTotalBatteryModal()"
        class="group bg-slate-900/90 border border-slate-800 hover:border-cyan-500/50 rounded-2xl p-4 flex flex-col justify-between shadow-xl transition-all duration-200 cursor-pointer hover:shadow-cyan-500/10 hover:-translate-y-1">
        <div class="flex items-center justify-between">
          <span
            class="text-sm font-bold uppercase tracking-wider text-slate-300 group-hover:text-cyan-400 flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span> TOTAL BATTERY
          </span>
          <span
            class="text-xs font-mono font-bold text-cyan-400 bg-cyan-950/80 px-2 py-0.5 rounded border border-cyan-800/60">DETAIL
            &rarr;</span>
        </div>

        <div class="relative my-2 flex items-center justify-center h-36">
          <canvas id="modelDonutChart" class="max-h-36 max-w-full"></canvas>
          <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
            <span class="text-3xl font-black font-mono text-white">{{ $totalBatteryQuantity }}</span>
            <span class="text-xs uppercase font-bold tracking-widest text-slate-400">TOTAL BATTERY</span>
          </div>
        </div>

        <div class="flex items-center justify-center flex-wrap gap-2 text-xs text-slate-300 font-mono font-semibold">
          @php
            $howManyColors = ['#06b6d4', '#6366f1', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#64748b'];
            $hmIdx = 0;
          @endphp
          @foreach (array_slice($topModelHowMany, 0, 3, true) as $mName => $mTotal)
            <span class="flex items-center gap-1">
              <span class="w-2 h-2 rounded-full"
                style="background-color: {{ $howManyColors[$hmIdx % count($howManyColors)] }}"></span>
              {{ $mName }} ({{ $mTotal }} pcs)
            </span>
            @php $hmIdx++; @endphp
          @endforeach
        </div>
      </div>

      <div @click="openJumlahDeviceModal()"
        class="group bg-slate-900/90 border border-slate-800 hover:border-indigo-500/50 rounded-2xl p-4 flex flex-col justify-between shadow-xl transition-all duration-200 cursor-pointer hover:shadow-indigo-500/10 hover:-translate-y-1">
        <div class="flex items-center justify-between">
          <span
            class="text-sm font-bold uppercase tracking-wider text-slate-300 group-hover:text-indigo-400 flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-indigo-400"></span> JUMLAH DEVICE
          </span>
          <span
            class="text-xs font-mono font-bold text-indigo-400 bg-indigo-950/80 px-2 py-0.5 rounded border border-indigo-800/60">DETAIL
            &rarr;</span>
        </div>

        <div class="relative my-2 flex items-center justify-center h-36">
          <canvas id="howManyDonutChart" class="max-h-36 max-w-full"></canvas>
          <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
            <span class="text-3xl font-black font-mono text-white">{{ $totalDeviceCount }}</span>
            <span class="text-xs uppercase font-bold tracking-widest text-slate-400">TOTAL DEVICE</span>
          </div>
        </div>

        <div class="flex items-center justify-center flex-wrap gap-2 text-xs text-slate-300 font-mono font-semibold">
          @php
            $deviceColors = ['#6366f1', '#8b5cf6', '#a855f7', '#06b6d4', '#10b981', '#f59e0b', '#ec4899'];
            $devIdx = 0;
          @endphp
          @foreach (array_slice($topModels, 0, 3, true) as $mName => $mCount)
            <span class="flex items-center gap-1">
              <span class="w-2 h-2 rounded-full"
                style="background-color: {{ $deviceColors[$devIdx % count($deviceColors)] }}"></span>
              {{ $mName }} ({{ $mCount }} dev)
            </span>
            @php $devIdx++; @endphp
          @endforeach
        </div>
      </div>

      @if ($showMachiningCard)
        <div
          @click="openAreaModal('machining', 'Area Machining 5C & Quality Control', 'Monitoring grafik jumlah baterai per line di area Machining 5C & QC')"
          class="group bg-slate-900/90 border border-slate-800 hover:border-emerald-500/50 rounded-2xl p-4 flex flex-col justify-between shadow-xl transition-all duration-200 cursor-pointer hover:shadow-emerald-500/10 hover:-translate-y-1">
          <div class="flex items-center justify-between">
            <span
              class="text-sm font-bold uppercase tracking-wider text-slate-300 group-hover:text-emerald-400 flex items-center gap-1.5 truncate">
              <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span> MACHINING 5C & QC
            </span>
            <span
              class="text-xs font-mono font-bold text-emerald-400 bg-emerald-950/80 px-2 py-0.5 rounded border border-emerald-800/60">DETAIL
              &rarr;</span>
          </div>

          <div class="relative my-2 flex items-center justify-center h-36">
            <canvas id="machiningDonutChart" class="max-h-36 max-w-full"></canvas>
            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
              <span class="text-3xl font-black font-mono text-white">{{ $areaMachiningStats['total'] }}</span>
              <span class="text-xs uppercase font-bold tracking-widest text-slate-400">UNIT</span>
            </div>
          </div>

          <div class="flex items-center justify-center gap-3 text-xs text-slate-300 font-mono font-semibold">
            <span class="flex items-center gap-1.5" title="Aktif"><span class="w-2 h-2 rounded-full bg-emerald-400"></span>
              {{ $areaMachiningStats['active'] }}</span>
            <span class="flex items-center gap-1.5" title="Change"><span class="w-2 h-2 rounded-full bg-rose-500"></span>
              {{ $areaMachiningStats['change'] }}</span>
            <span class="flex items-center gap-1.5" title="Warning / Error"><span class="w-2 h-2 rounded-full bg-amber-400"></span>
              {{ $areaMachiningStats['warning'] }}</span>
          </div>
        </div>
      @endif

      @if ($showShaftCard)
        <div
          @click="openAreaModal('shaft', 'Area Production Shaft (PS)', 'Monitoring grafik jumlah baterai per line di area Pro.shaft & Axle Housing')"
          class="group bg-slate-900/90 border border-slate-800 hover:border-cyan-500/50 rounded-2xl p-4 flex flex-col justify-between shadow-xl transition-all duration-200 cursor-pointer hover:shadow-cyan-500/10 hover:-translate-y-1">
          <div class="flex items-center justify-between">
            <span
              class="text-sm font-bold uppercase tracking-wider text-slate-300 group-hover:text-cyan-400 flex items-center gap-1.5 truncate">
              <span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span> PROD. SHAFT (PS)
            </span>
            <span
              class="text-xs font-mono font-bold text-cyan-400 bg-cyan-950/80 px-2 py-0.5 rounded border border-cyan-800/60">DETAIL
              &rarr;</span>
          </div>

          <div class="relative my-2 flex items-center justify-center h-36">
            <canvas id="shaftDonutChart" class="max-h-36 max-w-full"></canvas>
            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
              <span class="text-3xl font-black font-mono text-white">{{ $areaShaftStats['total'] }}</span>
              <span class="text-xs uppercase font-bold tracking-widest text-slate-400">UNIT</span>
            </div>
          </div>

          <div class="flex items-center justify-center gap-3 text-xs text-slate-300 font-mono font-semibold">
            <span class="flex items-center gap-1.5" title="Aktif"><span class="w-2 h-2 rounded-full bg-emerald-400"></span>
              {{ $areaShaftStats['active'] }}</span>
            <span class="flex items-center gap-1.5" title="Change"><span class="w-2 h-2 rounded-full bg-rose-500"></span>
              {{ $areaShaftStats['change'] }}</span>
            <span class="flex items-center gap-1.5" title="Warning / Error"><span class="w-2 h-2 rounded-full bg-amber-400"></span>
              {{ $areaShaftStats['warning'] }}</span>
          </div>
        </div>
      @endif

      @if ($showEngineCard)
        <div
          @click="openAreaModal('engine', 'Area Engine & TM', 'Monitoring grafik jumlah baterai per line di area Engine & TM, Axle Assy')"
          class="group bg-slate-900/90 border border-slate-800 hover:border-indigo-500/50 rounded-2xl p-4 flex flex-col justify-between shadow-xl transition-all duration-200 cursor-pointer hover:shadow-indigo-500/10 hover:-translate-y-1">
          <div class="flex items-center justify-between">
            <span
              class="text-sm font-bold uppercase tracking-wider text-slate-300 group-hover:text-indigo-400 flex items-center gap-1.5 truncate">
              <span class="w-2.5 h-2.5 rounded-full bg-indigo-400"></span> AREA ENGINE
            </span>
            <span
              class="text-xs font-mono font-bold text-indigo-400 bg-indigo-950/80 px-2 py-0.5 rounded border border-indigo-800/60">DETAIL
              &rarr;</span>
          </div>

          <div class="relative my-2 flex items-center justify-center h-36">
            <canvas id="engineDonutChart" class="max-h-36 max-w-full"></canvas>
            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
              <span class="text-3xl font-black font-mono text-white">{{ $areaEngineStats['total'] }}</span>
              <span class="text-xs uppercase font-bold tracking-widest text-slate-400">UNIT</span>
            </div>
          </div>

          <div class="flex items-center justify-center gap-3 text-xs text-slate-300 font-mono font-semibold">
            <span class="flex items-center gap-1.5" title="Aktif"><span class="w-2 h-2 rounded-full bg-emerald-400"></span>
              {{ $areaEngineStats['active'] }}</span>
            <span class="flex items-center gap-1.5" title="Change"><span class="w-2 h-2 rounded-full bg-rose-500"></span>
              {{ $areaEngineStats['change'] }}</span>
            <span class="flex items-center gap-1.5" title="Warning / Error"><span class="w-2 h-2 rounded-full bg-amber-400"></span>
              {{ $areaEngineStats['warning'] }}</span>
          </div>
        </div>
      @endif

    </div>

    <!-- ========================================== -->
    <!-- 3. BOTTOM ROW: CHARTS & ANALYTICS          -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <!-- MOST FREQUENT BATTERY REPLACEMENTS BY EQUIPMENT TYPE -->
      <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 shadow-xl flex flex-col justify-between">
        <div class="flex items-center justify-between mb-2">
          <div>
            <h3 class="text-sm sm:text-base font-bold text-white uppercase tracking-wider">
              MOST FREQUENT BATTERY REPLACEMENTS BY EQUIPMENT TYPE
            </h3>
            <p class="text-xs text-slate-400 font-mono mt-0.5">
              Frekuensi penggantian baterai per Equipment Type di setiap mesin
            </p>
          </div>
          <button @click="openFrequentExchangeModal()"
            class="text-xs font-mono font-bold text-indigo-400 hover:text-indigo-300 hover:underline cursor-pointer">
            View All &rarr;
          </button>
        </div>
        <div class="h-56 relative">
          <canvas id="lineStatusBarChart"></canvas>
        </div>
      </div>

      <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 shadow-xl flex flex-col justify-between">
        <div class="flex items-center justify-between mb-2">
          <div>
            <h3 class="text-sm sm:text-base font-bold text-white uppercase tracking-wider">REPLACEMENT CYCLE TREND</h3>
            <p class="text-xs text-slate-400 font-mono mt-0.5">Proyeksi kebutuhan ganti baterai 6 bulan ke depan</p>
          </div>
          <button
            @click="openModal('change', 'Proyeksi Penggantian 6 Bulan', 'Baterai yang akan jatuh tempo dalam beberapa bulan')"
            class="text-xs font-mono font-bold text-cyan-400 hover:text-cyan-300 hover:underline">
            Timeline &rarr;
          </button>
        </div>
        <div class="h-56 relative">
          <canvas id="replacementTrendChart"></canvas>
        </div>
      </div>

      <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 shadow-xl flex flex-col justify-between">
        <div class="flex items-center justify-between mb-2">
          <div>
            <h3 class="text-sm sm:text-base font-bold text-white uppercase tracking-wider">DEVICE & EQUIPMENT TYPE</h3>
            <p class="text-xs text-slate-400 font-mono mt-0.5">Distribusi aplikasi baterai pada mesin</p>
          </div>
          <button @click="openModal('all', 'Distribusi Device Mesin', 'Sebaran battery pada PLC, Servo, CNC, dll')"
            class="text-xs font-mono font-bold text-emerald-400 hover:text-emerald-300 hover:underline">
            Breakdown &rarr;
          </button>
        </div>
        <div class="h-56 relative">
          <canvas id="deviceBarChart"></canvas>
        </div>
      </div>
    </div>

    <!-- ========================================== -->
    <!-- 4. BOTTOM FILTER & FOOTER BAR (TV STYLE)   -->
    <!-- ========================================== -->
    <div
      class="p-3.5 rounded-xl bg-slate-900 border border-slate-800/80 shadow-lg flex flex-col md:flex-row items-center justify-between gap-3 text-sm">
      <form method="GET" action="{{ route('main-dashboard.battery') }}" class="flex items-center flex-wrap gap-3">
        <div class="flex items-center gap-2">
          <span class="text-slate-300 font-mono text-xs sm:text-sm font-bold">AREA:</span>
          <select name="area" onchange="if(this.form.line) { this.form.line.value='ALL'; } this.form.submit();"
            class="bg-slate-950 border border-slate-800 text-cyan-400 font-bold font-mono text-sm rounded-lg px-3 py-1.5 outline-none focus:ring-1 focus:ring-cyan-500 cursor-pointer">
            <option value="ALL" {{ $selectedArea === 'ALL' ? 'selected' : '' }}>ALL AREAS</option>
            @foreach ($availableAreas as $area)
              <option value="{{ $area }}" {{ $selectedArea === $area ? 'selected' : '' }}>{{ $area }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="flex items-center gap-2">
          <span class="text-slate-300 font-mono text-xs sm:text-sm font-bold">LINE:</span>
          <select name="line" onchange="this.form.submit()"
            class="bg-slate-950 border border-slate-800 text-indigo-400 font-bold font-mono text-sm rounded-lg px-3 py-1.5 outline-none focus:ring-1 focus:ring-indigo-500 cursor-pointer">
            <option value="ALL" {{ $selectedLine === 'ALL' ? 'selected' : '' }}>ALL LINES</option>
            @foreach ($availableLines as $line)
              <option value="{{ $line }}" {{ $selectedLine === $line ? 'selected' : '' }}>{{ $line }}
              </option>
            @endforeach
          </select>
        </div>

        @if ($selectedArea !== 'ALL' || $selectedLine !== 'ALL')
          <a href="{{ route('main-dashboard.battery') }}"
            class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 font-mono text-xs font-bold transition-colors">
            Reset
          </a>
        @endif
      </form>

      <div class="flex items-center gap-4 text-slate-400 font-mono text-xs">
        <div class="flex items-center gap-1.5">
          <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
          <span>CONNECTED TO DATABASE</span>
        </div>
        <div class="hidden sm:block">
          <span>LAST UPDATED: </span>
          <span class="text-slate-200 font-bold" x-text="currentTime"></span>
        </div>
        <div class="text-indigo-400 font-bold hidden md:block">
          ENGINEERING SERVICE MACHINING DEPT
        </div>
      </div>
    </div>

    <!-- ========================================== -->
    <!-- 5. INTERACTIVE DETAIL MODAL (DRILL-DOWN)   -->
    <!-- ========================================== -->
    <div x-show="modalOpen" x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
      x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
      x-transition:leave-end="opacity-0 scale-95"
      class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-slate-950/80 backdrop-blur-md"
      style="display: none;">
      <div @click.away="modalOpen = false"
        class="bg-slate-900 border border-slate-700/80 rounded-2xl w-full max-w-6xl max-h-[90vh] flex flex-col shadow-2xl overflow-hidden">

        <div class="p-4 border-b border-slate-800 flex items-center justify-between bg-slate-950/60 shrink-0">
          <div class="flex items-center gap-3">
            <div class="w-3.5 h-3.5 rounded-full bg-cyan-400 animate-ping"></div>
            <div>
              <h2 class="text-xl font-black text-white tracking-wide" x-text="modalTitle">Detail Battery</h2>
              <p class="text-sm text-slate-300 mt-0.5" x-text="modalSubtitle">Daftar item terpilih</p>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <span
              class="px-3.5 py-1.5 rounded-full text-sm font-mono font-bold bg-cyan-500/10 text-cyan-300 border border-cyan-500/40">
              <span x-text="filteredBatteries.length"></span> Unit Ditemukan
            </span>
            <button @click="modalOpen = false"
              class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
              <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <div id="modalScrollableBody" class="flex-1 overflow-y-auto custom-scrollbar flex flex-col">
          <div id="modalBarChartSection" x-show="showBarChart && isMachinePlanModal"
            class="p-4 border-b border-slate-800 bg-slate-950/70 shrink-0">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
              <div>
                <h3 class="text-sm sm:text-base font-bold text-white uppercase tracking-wider flex items-center gap-2">
                  <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 animate-pulse"></span>
                  <span x-show="modalChartType === 'lineHowMany'">DATA PEMAKAIAN BATERAI PER LINE (SUM HOW MANY)</span>
                  <span x-show="modalChartType === 'areaLine'">DATA JUMLAH BATERAI PER LINE (SUM HOW MANY)</span>
                  <span x-show="modalChartType === 'totalBattery'">DATA TOTAL BATERAI PER LINE & MODEL (SUM HOW MANY)</span>
                  <span x-show="modalChartType === 'batteryType'">DISTRIBUSI BATTERY TYPE (LITHIUM & ALKALI)</span>
                  <span x-show="modalChartType === 'deviceCount'">DATA JUMLAH DEVICE PER LINE (COUNT HOW MANY)</span>
                </h3>
              </div>

              <div class="flex items-center flex-wrap gap-2 font-mono text-xs sm:text-sm shrink-0">
                <button @click="setChartStatusFilter('all')"
                  :class="chartStatusFilter === 'all' ? 'bg-indigo-600 text-white font-bold ring-1 ring-indigo-400' :
                      'bg-slate-800 text-slate-300 hover:text-white'"
                  class="px-3 py-1.5 rounded-lg transition-all font-semibold">
                  Semua (<span x-text="modalScopeStats.total"></span>)
                </button>
                <button @click="setChartStatusFilter('active')"
                  :class="chartStatusFilter === 'active' ? 'bg-emerald-600 text-white font-bold ring-1 ring-emerald-400' :
                      'bg-slate-800 text-emerald-400 hover:text-emerald-300'"
                  class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 font-semibold">
                  <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Aktif (<span
                    x-text="modalScopeStats.active"></span>)
                </button>
                <button
                  @click="setChartStatusFilter('change')"
                  :class="chartStatusFilter === 'change' ? 'bg-rose-600 text-white font-bold ring-1 ring-rose-400' :
                      'bg-slate-800 text-rose-400 hover:text-rose-300'"
                  class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 font-semibold">
                  <span class="w-2 h-2 rounded-full bg-rose-500"></span> Change (<span
                    x-text="modalScopeStats.change"></span>)
                </button>
                <button
                  @click="setChartStatusFilter('warning')"
                  :class="chartStatusFilter === 'warning' ? 'bg-amber-600 text-white font-bold ring-1 ring-amber-400' :
                      'bg-slate-800 text-amber-400 hover:text-amber-300'"
                  class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 font-semibold">
                  <span class="w-2 h-2 rounded-full bg-amber-400"></span> Warning (<span
                    x-text="modalScopeStats.warning"></span>)
                </button>
              </div>
            </div>

            <div class="relative w-full h-[280px]">
              <canvas id="modalLineStatusBarChart"></canvas>
            </div>
          </div>

          <div x-show="isMachinePlanModal && showBarChart && selectedLineFilter && (modalChartType === 'areaLine' || modalChartType === 'lineHowMany' || modalChartType === 'deviceCount' || modalChartType === 'totalBattery')"
            x-transition id="modalMachineChartSection" class="p-4 border-b border-cyan-800/50 bg-slate-950/70">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2.5">
              <div>
                <h4
                  class="text-sm sm:text-base font-bold text-cyan-300 uppercase tracking-wider flex items-center gap-2">
                  <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 animate-pulse"></span>
                  GRAFIK DETAIL PER MESIN &bull; LINE: <span
                    class="text-white font-mono bg-cyan-950 px-2.5 py-0.5 rounded border border-cyan-700/60"
                    x-text="selectedLineFilter"></span>
                </h4>
              </div>
              <div class="flex items-center gap-2 font-mono text-xs sm:text-sm shrink-0">
                <button
                  @click="selectedLineFilter = null; modalFilterLine = 'ALL'; modalFilterOp = 'ALL'; modalFilterMachine = 'ALL'; currentPage = 1; renderModalBarChart();"
                  class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 transition-colors flex items-center gap-1 font-semibold shadow">
                  <span>&times;</span> Tutup Grafik Mesin
                </button>
              </div>
            </div>
            <div class="relative w-full h-[260px]">
              <canvas id="modalMachineStatusBarChart"></canvas>
            </div>
          </div>

          <div id="modalFilterSearchBar"
            class="sticky top-0 z-20 p-3.5 sm:p-4 border-b border-slate-800/90 bg-slate-900/95 backdrop-blur-md space-y-3 shadow-xl shrink-0">
            <!-- Row 1: 4 Cascading Dropdowns in a Neat Responsive Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
              <!-- 1. AREA -->
              <div class="flex flex-col gap-1.5">
                <label class="text-[11px] font-mono font-bold uppercase tracking-wider text-slate-300 flex items-center gap-1.5">
                  <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                  <span>Area:</span>
                </label>
                <select x-model="modalFilterArea" @change="onModalAreaChange()"
                  class="w-full bg-slate-950 border border-slate-800 text-cyan-400 font-bold font-mono text-xs sm:text-sm rounded-xl px-3 py-2 outline-none focus:ring-1 focus:ring-cyan-500 cursor-pointer shadow-inner">
                  <option value="ALL">ALL AREAS</option>
                  <template x-for="a in modalAvailableAreas" :key="a.key">
                    <option :value="a.key" x-text="a.name"></option>
                  </template>
                </select>
              </div>

              <!-- 2. LINE -->
              <div class="flex flex-col gap-1.5">
                <label class="text-[11px] font-mono font-bold uppercase tracking-wider text-slate-300 flex items-center gap-1.5">
                  <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                  <span>Line:</span>
                </label>
                <select x-model="modalFilterLine" @change="onModalLineChange()"
                  class="w-full bg-slate-950 border border-slate-800 text-indigo-400 font-bold font-mono text-xs sm:text-sm rounded-xl px-3 py-2 outline-none focus:ring-1 focus:ring-indigo-500 cursor-pointer shadow-inner">
                  <option value="ALL">ALL LINES</option>
                  <template x-for="ln in modalAvailableLines" :key="ln">
                    <option :value="ln" x-text="ln"></option>
                  </template>
                </select>
              </div>

              <!-- 3. OP (OPERATION / STATION NUMBER) -->
              <div class="flex flex-col gap-1.5">
                <label class="text-[11px] font-mono font-bold uppercase tracking-wider text-slate-300 flex items-center gap-1.5">
                  <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                  <span>OP (Station):</span>
                </label>
                <select x-model="modalFilterOp" @change="onModalOpChange()"
                  class="w-full bg-slate-950 border border-slate-800 text-amber-300 font-bold font-mono text-xs sm:text-sm rounded-xl px-3 py-2 outline-none focus:ring-1 focus:ring-amber-500 cursor-pointer shadow-inner">
                  <option value="ALL">ALL OP</option>
                  <template x-for="op in modalAvailableOps" :key="op">
                    <option :value="op" x-text="op"></option>
                  </template>
                </select>
              </div>

              <!-- 4. MACHINE (MACHINE NAME) -->
              <div class="flex flex-col gap-1.5">
                <label class="text-[11px] font-mono font-bold uppercase tracking-wider text-slate-300 flex items-center gap-1.5">
                  <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                  <span>Machine:</span>
                </label>
                <select x-model="modalFilterMachine" @change="onModalMachineChange()"
                  class="w-full bg-slate-950 border border-slate-800 text-emerald-400 font-bold font-mono text-xs sm:text-sm rounded-xl px-3 py-2 outline-none focus:ring-1 focus:ring-emerald-500 cursor-pointer shadow-inner">
                  <option value="ALL">ALL MACHINES</option>
                  <template x-for="m in modalAvailableMachines" :key="m">
                    <option :value="m" x-text="m"></option>
                  </template>
                </select>
              </div>
            </div>

            <!-- Row 2: Status Filter Buttons & Action Buttons -->
            <div class="flex flex-col md:flex-row items-center justify-between gap-3 pt-2.5 border-t border-slate-800/70">
              <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto font-mono text-xs sm:text-sm shrink-0">
                <span class="text-slate-400 font-bold text-xs uppercase mr-1 hidden sm:inline">Status:</span>
                <button @click="setChartStatusFilter('all'); filterSpecificDate = null;"
                  :class="filterStatus === 'all' && !filterSpecificDate ? 'bg-indigo-600 text-white font-bold ring-1 ring-indigo-400 shadow-md' :
                      'bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700'"
                  class="px-3 py-1.5 rounded-lg transition-colors font-semibold">Semua</button>
                <button @click="setChartStatusFilter('active'); filterSpecificDate = null;"
                  :class="filterStatus === 'active' ? 'bg-emerald-600 text-white font-bold ring-1 ring-emerald-400 shadow-md' :
                      'bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700'"
                  class="px-3 py-1.5 rounded-lg transition-colors font-semibold flex items-center gap-1.5">
                  <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Aktif (Hijau)
                </button>
                <button
                  @click="setChartStatusFilter('change'); filterSpecificDate = null;"
                  :class="filterStatus === 'change' ? 'bg-rose-600 text-white font-bold ring-1 ring-rose-400 shadow-md' :
                      'bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700'"
                  class="px-3 py-1.5 rounded-lg transition-colors font-semibold flex items-center gap-1.5">
                  <span class="w-2 h-2 rounded-full bg-rose-500"></span> Change (Merah)
                </button>
                <button
                  @click="setChartStatusFilter('warning'); filterSpecificDate = null;"
                  :class="filterStatus === 'warning' ? 'bg-amber-600 text-white font-bold ring-1 ring-amber-400 shadow-md' :
                      'bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700'"
                  class="px-3 py-1.5 rounded-lg transition-colors font-semibold flex items-center gap-1.5">
                  <span class="w-2 h-2 rounded-full bg-amber-400"></span> Warning (Kuning)
                </button>
              </div>

              <div class="flex items-center gap-2.5 w-full md:w-auto justify-end shrink-0">
                <button
                  x-show="modalFilterArea !== 'ALL' || modalFilterLine !== 'ALL' || modalFilterOp !== 'ALL' || modalFilterMachine !== 'ALL' || selectedLineFilter || selectedModelFilter || selectedBatteryTypeFilter"
                  @click="resetModalDropdownFilters()"
                  class="px-3 py-1.5 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 hover:text-rose-200 font-mono text-xs sm:text-sm font-bold border border-rose-500/30 transition-colors flex items-center gap-1.5 shadow">
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                  </svg>
                  <span>Reset Filter</span>
                </button>

                <button x-show="isMachinePlanModal"
                  @click="showBarChart = !showBarChart; if(showBarChart) { $nextTick(() => { renderModalBarChart(); }); }"
                  class="px-3 py-1.5 rounded-lg border border-slate-700 bg-slate-800/80 hover:bg-slate-700 text-slate-200 hover:text-white font-mono text-xs sm:text-sm transition-colors flex items-center gap-1.5 font-semibold shadow">
                  <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>
                  <span x-text="showBarChart ? 'Tutup Grafik' : 'Lihat Grafik'"></span>
                </button>
              </div>
            </div>
          </div>

          <div id="modalBatteryTableSection" class="p-2 sm:p-4 flex-1">
            <div x-show="selectedLineFilter || selectedModelFilter || selectedBatteryTypeFilter" x-transition
              class="mb-3 p-3 rounded-xl bg-cyan-950/80 border border-cyan-500/50 flex flex-col sm:flex-row sm:items-center justify-between gap-2 shadow-lg shadow-cyan-950/40">
              <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-cyan-400 animate-ping"></span>
                <div class="text-xs font-mono">
                  <span class="text-slate-300">Menampilkan Detail Unit Baterai: </span>
                  <strong
                    class="text-white text-sm font-bold bg-cyan-900/60 px-2 py-0.5 rounded border border-cyan-700/50"
                    x-text="selectedLineFilter ? ('Line ' + selectedLineFilter) : (selectedBatteryTypeFilter ? ('Type ' + selectedBatteryTypeFilter) : ('Model ' + selectedModelFilter))"></strong>
                  <span class="text-cyan-300 font-semibold ml-1.5">
                    (<span x-text="filteredBatteries.length"></span> unit ditemukan)
                  </span>
                </div>
              </div>
              <div class="flex items-center gap-2 shrink-0">
                <button @click="scrollToChart()"
                  class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-cyan-300 hover:text-white text-xs font-mono border border-slate-700 flex items-center gap-1 transition-colors shadow">
                  <span>&uarr;</span> Kembali ke Grafik
                </button>
                <button
                  @click="selectedLineFilter = null; selectedModelFilter = null; selectedBatteryTypeFilter = null; currentPage = 1; renderModalBarChart(); scrollToChart();"
                  class="px-2.5 py-1 rounded-lg bg-red-950/70 hover:bg-red-900/70 text-red-300 hover:text-white text-xs font-mono border border-red-800/60 flex items-center gap-1 transition-colors shadow">
                  <span>&times;</span> Reset Filter
                </button>
              </div>
            </div>

            <div id="modalBatteryTableContainer" class="overflow-x-auto">
              <table class="w-full text-left text-xs border-collapse">
                <thead>
                  <tr
                    class="border-b border-slate-800 text-[11px] font-mono text-slate-400 uppercase bg-slate-950/40 whitespace-nowrap">
                    <th class="py-2.5 px-3 text-center">No</th>
                    <th class="py-2.5 px-3">Area</th>
                    <th class="py-2.5 px-3">Line</th>
                    <th class="py-2.5 px-3">OP (Station)</th>
                    <th class="py-2.5 px-3">Machine</th>
                    <th class="py-2.5 px-3">Model</th>
                    <th class="py-2.5 px-3">Equipment Type</th>
                    <th class="py-2.5 px-3">Device</th>
                    <th class="py-2.5 px-3">Volt</th>
                    <th class="py-2.5 px-3">Next Replace</th>
                    <th class="py-2.5 px-3 text-center">Status</th>
                    <th class="py-2.5 px-3 text-center">Input By</th>
                    <th class="py-2.5 px-3 text-center">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-sans">
                  <template x-for="(item, index) in paginatedBatteries" :key="item.id">
                    <tr class="hover:bg-slate-800/40 transition-colors">
                      <td class="py-2.5 px-3 text-center font-mono text-slate-500"
                        x-text="((currentPage - 1) * perPage) + index + 1"></td>
                      <td class="py-2.5 px-3 text-slate-300 font-medium whitespace-nowrap" x-text="item.area || '-'">
                      </td>
                      <td class="py-2.5 px-3 whitespace-nowrap">
                        <span
                          class="px-2 py-0.5 rounded bg-slate-800 text-cyan-300 border border-slate-700 font-mono text-[11px] font-bold"
                          x-text="item.line"></span>
                      </td>
                      <td class="py-2.5 px-3 font-mono font-bold text-amber-300 whitespace-nowrap" x-text="item.machine_no || '-'"></td>
                      <td class="py-2.5 px-3 font-mono font-semibold text-emerald-300 whitespace-nowrap" x-text="item.machine_name || '-'"></td>
                      <td class="py-2.5 px-3 whitespace-nowrap">
                        <span class="font-mono font-bold text-amber-300 block"
                          x-text="item.battery_model || '-'"></span>
                        <span class="text-[10px] text-slate-400" x-text="item.battery_id"></span>
                      </td>
                      <td class="py-2.5 px-3 text-slate-300 whitespace-nowrap" x-text="item.equipment_type || '-'"></td>
                      <td class="py-2.5 px-3 text-slate-300 whitespace-nowrap" x-text="item.device || '-'"></td>
                      <td class="py-2.5 px-3 font-mono text-slate-300 whitespace-nowrap" x-text="item.std_volt || '-'">
                      </td>
                      <td class="py-2.5 px-3 font-mono whitespace-nowrap">
                        <span class="text-white block" x-text="item.next_replace_date"></span>
                        <span class="text-[10px] block"
                          :class="item.status === 'change' ? 'text-rose-400 font-bold' : (item.status === 'warning' ?
                              'text-amber-400 font-semibold' : 'text-slate-500')"
                          x-text="item.status_label"></span>
                      </td>
                      <td class="py-2.5 px-3 text-center whitespace-nowrap">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono inline-block border"
                          :class="{
                              'bg-emerald-500/10 text-emerald-400 border-emerald-500/30': item
                                  .status === 'active',
                              'bg-rose-500/10 text-rose-400 border-rose-500/30 font-bold': item
                                  .status === 'change',
                              'bg-amber-500/10 text-amber-400 border-amber-500/30 font-semibold': item
                                  .status === 'warning'
                          }"
                          x-text="item.status === 'active' ? 'AKTIF' : (item.status === 'change' ? 'CHANGE' : 'WARNING')"></span>
                      </td>
                      <td class="py-2.5 px-3 text-center whitespace-nowrap">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-800 text-slate-300 border border-slate-700 font-mono">
                          <svg class="w-2.5 h-2.5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                          </svg>
                          <span x-text="item.created_by || 'System'"></span>
                        </span>
                      </td>
                      <td class="py-2.5 px-3 text-center whitespace-nowrap">
                        <button @click="viewSpec(item)"
                          class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-cyan-600 text-slate-300 hover:text-white transition-colors text-[10px] font-mono border border-slate-700 hover:border-cyan-500 shadow">
                          Spec
                        </button>
                      </td>
                    </tr>
                  </template>

                  <template x-if="filteredBatteries.length === 0">
                    <tr>
                      <td colspan="13" class="py-8 text-center text-slate-500 font-mono text-xs">
                        Tidak ada data battery yang cocok dengan kriteria pencarian.
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div
          class="p-3 border-t border-slate-800 bg-slate-950/60 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400 shrink-0">
          <div class="flex items-center gap-2">
            <span>Menampilkan <strong class="text-white"
                x-text="filteredBatteries.length === 0 ? 0 : ((currentPage - 1) * perPage) + 1"></strong> - <strong
                class="text-white" x-text="Math.min(currentPage * perPage, filteredBatteries.length)"></strong> dari
              <strong class="text-cyan-400" x-text="filteredBatteries.length"></strong> unit</span>
          </div>

          <div class="flex items-center gap-2" x-show="totalPages > 1">
            <button @click="prevPage()" :disabled="currentPage === 1"
              class="px-3 py-1 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white disabled:opacity-40 disabled:cursor-not-allowed font-mono text-xs transition-colors">
              &larr; Prev
            </button>
            <span class="font-mono text-xs text-slate-300">
              Hal <span class="text-white font-bold" x-text="currentPage"></span> / <span x-text="totalPages"></span>
            </span>
            <button @click="nextPage()" :disabled="currentPage === totalPages"
              class="px-3 py-1 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white disabled:opacity-40 disabled:cursor-not-allowed font-mono text-xs transition-colors">
              Next &rarr;
            </button>
          </div>

          <div class="flex items-center gap-3">
            <button @click="modalOpen = false"
              class="px-4 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-medium transition-colors">
              Tutup
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- ========================================== -->
    <!-- 6. BATTERY SPEC SHEET POPUP (INDIVIDUAL)   -->
    <!-- ========================================== -->
    <div x-show="specOpen" x-transition:enter="transition ease-out duration-150"
      x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
      x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100"
      x-transition:leave-end="opacity-0 scale-90"
      class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
      style="display: none;"
      @click.stop>
      <div @click.away="closeSpecModal()"
        class="bg-slate-900 border border-cyan-500/30 rounded-2xl w-full max-w-lg p-5 shadow-2xl shadow-cyan-500/10">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3 mb-4">
          <div>
            <span class="text-[10px] font-mono text-cyan-400 uppercase tracking-widest font-bold">SPECIFICATION
              SHEET</span>
            <h3 class="text-lg font-bold text-white" x-text="activeSpec.battery_id"></h3>
          </div>
          <button @click="closeSpecModal()" class="text-slate-400 hover:text-white p-1 cursor-pointer">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="grid grid-cols-2 gap-3 text-xs">
          <div class="p-2.5 rounded-xl bg-slate-950/80 border border-slate-800">
            <span class="text-[10px] font-mono text-slate-500 block">NAMA MESIN</span>
            <span class="font-bold text-white text-sm" x-text="activeSpec.machine_name"></span>
          </div>
          <div class="p-2.5 rounded-xl bg-slate-950/80 border border-slate-800">
            <span class="text-[10px] font-mono text-slate-500 block">NO MESIN / LINE</span>
            <span class="font-bold text-white text-sm" x-text="activeSpec.machine_no + ' / ' + activeSpec.line"></span>
          </div>
          <div class="p-2.5 rounded-xl bg-slate-950/80 border border-slate-800">
            <span class="text-[10px] font-mono text-slate-500 block">MODEL BATTERY</span>
            <span class="font-bold text-cyan-400 text-sm" x-text="activeSpec.battery_model"></span>
          </div>
          <div class="p-2.5 rounded-xl bg-slate-950/80 border border-slate-800">
            <span class="text-[10px] font-mono text-slate-500 block">MAKER / VOLT</span>
            <span class="font-bold text-white text-sm"
              x-text="activeSpec.maker + ' (' + activeSpec.std_volt + ')'"></span>
          </div>
          <div class="p-2.5 rounded-xl bg-slate-950/80 border border-slate-800">
            <span class="text-[10px] font-mono text-slate-500 block">DEVICE APLIKASI</span>
            <span class="font-medium text-slate-300" x-text="activeSpec.device"></span>
          </div>
          <div class="p-2.5 rounded-xl bg-slate-950/80 border border-slate-800">
            <span class="text-[10px] font-mono text-slate-500 block">CYCLE PENGGANTIAN</span>
            <span class="font-medium text-slate-300" x-text="activeSpec.replacement_cycle_month"></span>
          </div>
          <div class="p-2.5 rounded-xl bg-slate-950/80 border border-slate-800">
            <span class="text-[10px] font-mono text-slate-500 block">TANGGAL PASANG</span>
            <span class="font-medium text-slate-300" x-text="activeSpec.install_date"></span>
          </div>
          <div class="p-2.5 rounded-xl bg-slate-950/80 border border-slate-800">
            <span class="text-[10px] font-mono text-slate-500 block">NEXT REPLACE DATE</span>
            <span class="font-bold text-rose-400" x-text="activeSpec.next_replace_date"></span>
          </div>
        </div>

        <div class="mt-4 pt-3 border-t border-slate-800 flex items-center justify-between">
          <span class="text-[10px] font-mono text-slate-500" x-text="'Area: ' + activeSpec.area"></span>
          <button @click="closeSpecModal()"
            class="px-4 py-1.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs transition-colors cursor-pointer">
            Selesai
          </button>
        </div>
      </div>
    </div>

    <!-- ========================================================= -->
    <!-- MODAL VIEW ALL: MOST FREQUENT BATTERY REPLACEMENTS        -->
    <!-- ========================================================= -->
    <div x-show="frequentExchangeModalOpen" x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
      x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
      x-transition:leave-end="opacity-0 scale-95"
      class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-slate-950/80 backdrop-blur-md"
      style="display: none;">
      <div @click.away="frequentExchangeModalOpen = false"
        class="bg-slate-900 border border-slate-700/80 rounded-2xl w-full max-w-5xl max-h-[85vh] flex flex-col shadow-2xl overflow-hidden">

        <!-- Modal Header -->
        <div class="p-4 sm:p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/70 shrink-0">
          <div class="flex items-center gap-3">
            <div class="p-2.5 rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 shrink-0">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <div>
              <h2 class="text-lg sm:text-xl font-bold text-white tracking-wide">Most Frequent Battery Replacements by Equipment Type</h2>
              <p class="text-xs text-slate-400 font-mono mt-0.5">Peringkat frekuensi penggantian baterai per Equipment Type di setiap mesin (Exchange Type)</p>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-indigo-500/10 text-indigo-300 border border-indigo-500/30">
              <span x-text="filteredFrequentMachines.length"></span> Item
            </span>
            <button @click="frequentExchangeModalOpen = false"
              class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors cursor-pointer">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Search Bar & Scope -->
        <div class="p-4 border-b border-slate-800 bg-slate-950/40 flex items-center justify-between gap-3 shrink-0">
          <div class="relative flex-1 max-w-md">
            <input type="text" x-model="frequentExchangeSearch" placeholder="Cari Equipment Type, mesin, OP, line..."
              class="w-full bg-slate-900 border border-slate-800 rounded-xl pl-9 pr-4 py-2 text-xs text-white placeholder-slate-500 outline-none focus:border-indigo-500 font-mono">
            <svg class="w-4 h-4 text-slate-500 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
          <div class="text-xs font-mono text-slate-400">
            Scope: <span class="text-indigo-400 font-bold">{{ $selectedArea !== 'ALL' ? $selectedArea : 'ALL AREAS' }}</span>
            @if($selectedLine !== 'ALL')
              / <span class="text-cyan-400 font-bold">{{ $selectedLine }}</span>
            @endif
          </div>
        </div>

        <!-- Table Body -->
        <div class="flex-1 overflow-y-auto custom-scrollbar p-4">
          <table class="w-full text-left text-xs font-mono border-collapse">
            <thead>
              <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider bg-slate-950/60">
                <th class="py-2.5 px-3 text-center">Rank</th>
                <th class="py-2.5 px-3">Equipment Type</th>
                <th class="py-2.5 px-3">Nama Mesin</th>
                <th class="py-2.5 px-3">OP Number</th>
                <th class="py-2.5 px-3">Line</th>
                <th class="py-2.5 px-3">Area</th>
                <th class="py-2.5 px-3">Model</th>
                <th class="py-2.5 px-3 text-center">Replace</th>
                <th class="py-2.5 px-3 text-center">Update</th>
                <th class="py-2.5 px-3 text-center">Total Exchange</th>
                <th class="py-2.5 px-3 text-right">Tgl Terakhir</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
              <template x-for="item in filteredFrequentMachines" :key="item.rank + '-' + item.equipment_type + '-' + item.machine_name + '-' + item.line">
                <tr class="hover:bg-slate-800/40 transition-colors">
                  <td class="py-2.5 px-3 text-center font-bold" :class="item.rank <= 3 ? 'text-amber-400' : 'text-slate-400'" x-text="'#' + item.rank"></td>
                  <td class="py-2.5 px-3 font-bold text-cyan-300" x-text="item.equipment_type"></td>
                  <td class="py-2.5 px-3 font-bold text-white" x-text="item.machine_name"></td>
                  <td class="py-2.5 px-3 text-amber-300 font-semibold" x-text="item.op_number || '-'"></td>
                  <td class="py-2.5 px-3 text-indigo-300" x-text="item.line"></td>
                  <td class="py-2.5 px-3 text-slate-400" x-text="item.area"></td>
                  <td class="py-2.5 px-3 text-emerald-300" x-text="item.battery_model || '-'"></td>
                  <td class="py-2.5 px-3 text-center text-indigo-400 font-bold" x-text="item.replace_count"></td>
                  <td class="py-2.5 px-3 text-center text-purple-400 font-bold" x-text="item.update_count"></td>
                  <td class="py-2.5 px-3 text-center">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/40" x-text="item.total + 'x'"></span>
                  </td>
                  <td class="py-2.5 px-3 text-right text-slate-300" x-text="item.last_date"></td>
                </tr>
              </template>
              <template x-if="filteredFrequentMachines.length === 0">
                <tr>
                  <td colspan="11" class="py-8 text-center text-slate-500">Tidak ada data penggantian battery ditemukan.</td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>

        <!-- Modal Footer -->
        <div class="p-3.5 border-t border-slate-800 bg-slate-950/70 flex justify-end shrink-0">
          <button @click="frequentExchangeModalOpen = false"
            class="px-5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition cursor-pointer">
            Tutup
          </button>
        </div>

      </div>
    </div>

  </div> <!-- Penutup utama x-data="batteryDashboard()" -->
@endsection

@push('scripts')
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('batteryDashboard', () => ({
        currentTime: '00:00:00',
        currentDate: '',
        modalOpen: false,
        modalTitle: '',
        modalSubtitle: '',
        searchQuery: '',
        filterStatus: 'all',
        filterSpecificDate: null,
        selectedLineFilter: null,
        selectedModelFilter: null,
        selectedBatteryTypeFilter: null,
        modalFilterArea: 'ALL',
        modalFilterLine: 'ALL',
        modalFilterOp: 'ALL',
        modalFilterMachine: 'ALL',
        initialAreaKey: null,
        showBarChart: false,
        isMachinePlanModal: false,
        modalChartType: 'modelUsage',
        chartStatusFilter: 'all',
        selectedAreaFilter: null,
        modelDonutChartInstance: null,
        howManyDonutChartInstance: null,
        machiningDonutChartInstance: null,
        shaftDonutChartInstance: null,
        engineDonutChartInstance: null,
        batteryTypeDonutChartInstance: null,
        modalLineChartInstance: null,
        modalMachineChartInstance: null,
        specOpen: false,
        activeSpec: {},
        currentPage: 1,
        perPage: 25,
        allBatteries: {!! json_encode($batteriesData ?? []) !!},
        frequentExchangeModalOpen: false,
        frequentExchangeSearch: '',
        topFrequentMachines: @json($topFrequentMachines ?? []),
        allFrequentMachines: @json($allFrequentMachines ?? []),

        openFrequentExchangeModal() {
          this.frequentExchangeModalOpen = true;
          this.frequentExchangeSearch = '';
        },

        get filteredFrequentMachines() {
          if (!this.frequentExchangeSearch) return this.allFrequentMachines;
          const s = this.frequentExchangeSearch.toLowerCase();
          return this.allFrequentMachines.filter(m => 
            (m.equipment_type && m.equipment_type.toLowerCase().includes(s)) ||
            (m.machine_name && m.machine_name.toLowerCase().includes(s)) ||
            (m.op_number && m.op_number.toLowerCase().includes(s)) ||
            (m.line && m.line.toLowerCase().includes(s)) ||
            (m.area && m.area.toLowerCase().includes(s)) ||
            (m.battery_model && m.battery_model.toLowerCase().includes(s))
          );
        },

        initDashboard() {
          this.updateClock();
          setInterval(() => this.updateClock(), 1000);

          this.$nextTick(() => {
            this.renderDonutChart();
            this.renderHowManyDonutChart();
            this.renderBatteryTypeDonutChart();
            this.renderAreaMachiningDonutChart();
            this.renderAreaShaftDonutChart();
            this.renderAreaEngineDonutChart();
            this.renderLineStatusBarChart();
            this.renderTrendChart();
            this.renderDeviceBarChart();
          });
        },

        updateClock() {
          const now = new Date();
          this.currentTime = now.toTimeString().split(' ')[0];
          const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
          const d = String(now.getDate()).padStart(2, '0');
          const m = months[now.getMonth()];
          const y = now.getFullYear();
          this.currentDate = `${d} ${m} ${y}`;
        },

        get modalAvailableAreas() {
          return [{
              key: 'machining',
              name: 'MACHINING 5C & QC'
            },
            {
              key: 'shaft',
              name: 'PRODUCTION SHAFT (PS)'
            },
            {
              key: 'engine',
              name: 'AREA ENGINE & TM'
            }
          ];
        },

        get modalAvailableLines() {
          let list = (this.allBatteries && Array.isArray(this.allBatteries)) ? this.allBatteries : [];
          const activeArea = (this.modalFilterArea && this.modalFilterArea !== 'ALL') ? this.modalFilterArea :
            this.selectedAreaFilter;
          if (activeArea) list = list.filter(b => b.area_key === activeArea);
          const linesSet = new Set();
          list.forEach(b => {
            if (b.line && String(b.line).trim() !== '' && b.line !== '-') {
              linesSet.add(String(b.line).trim());
            }
          });
          return Array.from(linesSet).sort();
        },

        get modalAvailableOps() {
          let list = (this.allBatteries && Array.isArray(this.allBatteries)) ? this.allBatteries : [];
          const activeArea = (this.modalFilterArea && this.modalFilterArea !== 'ALL') ? this.modalFilterArea :
            this.selectedAreaFilter;
          if (activeArea) list = list.filter(b => b.area_key === activeArea);
          const activeLine = (this.modalFilterLine && this.modalFilterLine !== 'ALL') ? this.modalFilterLine :
            this.selectedLineFilter;
          if (activeLine) list = list.filter(b => b.line === activeLine);
          if (this.modalFilterMachine && this.modalFilterMachine !== 'ALL') {
            list = list.filter(b => b.machine_name === this.modalFilterMachine);
          }
          const opsSet = new Set();
          list.forEach(b => {
            const op = b.machine_no ? String(b.machine_no).trim() : '';
            if (op && op !== '-') {
              opsSet.add(op);
            }
          });
          return Array.from(opsSet).sort((a, b) => a.localeCompare(b, undefined, { numeric: true, sensitivity: 'base' }));
        },

        get modalAvailableMachines() {
          let list = (this.allBatteries && Array.isArray(this.allBatteries)) ? this.allBatteries : [];
          const activeArea = (this.modalFilterArea && this.modalFilterArea !== 'ALL') ? this.modalFilterArea :
            this.selectedAreaFilter;
          if (activeArea) list = list.filter(b => b.area_key === activeArea);
          const activeLine = (this.modalFilterLine && this.modalFilterLine !== 'ALL') ? this.modalFilterLine :
            this.selectedLineFilter;
          if (activeLine) list = list.filter(b => b.line === activeLine);
          if (this.modalFilterOp && this.modalFilterOp !== 'ALL') {
            list = list.filter(b => b.machine_no === this.modalFilterOp);
          }
          const machinesSet = new Set();
          list.forEach(b => {
            const mc = b.machine_name ? String(b.machine_name).trim() : '';
            if (mc && mc !== '-') {
              machinesSet.add(mc);
            }
          });
          return Array.from(machinesSet).sort((a, b) => a.localeCompare(b, undefined, { numeric: true, sensitivity: 'base' }));
        },

        get modalScopeStats() {
          let items = this.allBatteries;
          const activeArea = (this.modalFilterArea && this.modalFilterArea !== 'ALL') ? this.modalFilterArea :
            this.selectedAreaFilter;
          if (activeArea) items = items.filter(b => b.area_key === activeArea);
          const activeLine = (this.modalFilterLine && this.modalFilterLine !== 'ALL') ? this.modalFilterLine :
            this.selectedLineFilter;
          if (activeLine) items = items.filter(b => b.line === activeLine);
          if (this.modalFilterOp && this.modalFilterOp !== 'ALL') items = items.filter(b => b.machine_no === this.modalFilterOp);
          if (this.modalFilterMachine && this.modalFilterMachine !== 'ALL') items = items.filter(b => b.machine_name === this.modalFilterMachine);
          if (this.filterSpecificDate) items = items.filter(b => b.raw_next_replace_date === this
            .filterSpecificDate);

          if (this.modalChartType === 'deviceCount') {
            let total = 0,
              active = 0,
              change = 0,
              warning = 0;
            for (let i = 0; i < items.length; i++) {
              const hasHm = Number(items[i].has_how_many) || (Number(items[i].how_many) > 0 ? 1 : 0);
              if (hasHm) {
                total++;
                if (items[i].status === 'active') active++;
                else if (items[i].status === 'change') change++;
                else if (items[i].status === 'warning') warning++;
              }
            }
            return {
              total,
              active,
              change,
              warning,
              unit: 'Device'
            };
          } else if (this.modalChartType === 'lineHowMany' || this.modalChartType === 'areaLine') {
            let total = 0,
              active = 0,
              change = 0,
              warning = 0;
            for (let i = 0; i < items.length; i++) {
              const qty = Number(items[i].how_many) || 0;
              total += qty;
              if (items[i].status === 'active') active += qty;
              else if (items[i].status === 'change') change += qty;
              else if (items[i].status === 'warning') warning += qty;
            }
            return {
              total,
              active,
              change,
              warning,
              unit: 'Pcs'
            };
          } else if (this.modalChartType === 'totalBattery') {
            let total = 0,
              active = 0,
              change = 0,
              warning = 0;
            for (let i = 0; i < items.length; i++) {
              const hasModel = items[i].battery_model && String(items[i].battery_model).trim() !== '' &&
                items[i].battery_model !== '-';
              if (hasModel) {
                const qty = Number(items[i].how_many) || 0;
                total += qty;
                if (items[i].status === 'active') active += qty;
                else if (items[i].status === 'change') change += qty;
                else if (items[i].status === 'warning') warning += qty;
              }
            }
            return {
              total,
              active,
              change,
              warning,
              unit: 'Pcs'
            };
          } else if (this.modalChartType === 'batteryType') {
            let total = 0,
              active = 0,
              change = 0,
              warning = 0;
            for (let i = 0; i < items.length; i++) {
              const bt = String(items[i].battery_type || '').toLowerCase();
              if (bt.includes('lithium') || bt.includes('alkali')) {
                const qty = Number(items[i].how_many) || 0;
                total += qty;
                if (items[i].status === 'active') active += qty;
                else if (items[i].status === 'change') change += qty;
                else if (items[i].status === 'warning') warning += qty;
              }
            }
            return {
              total,
              active,
              change,
              warning,
              unit: 'Pcs'
            };
          } else {
            let total = 0,
              active = 0,
              change = 0,
              warning = 0;
            for (let i = 0; i < items.length; i++) {
              const agg = Number(items[i].aggregate) || 1;
              total += agg;
              if (items[i].status === 'active') active += agg;
              else if (items[i].status === 'change') change += agg;
              else if (items[i].status === 'warning') warning += agg;
            }
            return {
              total,
              active,
              change,
              warning,
              unit: 'Unit'
            };
          }
        },

        get filteredBatteries() {
          let list = this.allBatteries;

          if (this.modalChartType === 'totalBattery' && !this.selectedModelFilter) {
            list = list.filter(b => b.battery_model && String(b.battery_model).trim() !== '' && b
              .battery_model !== '-');
          }

          if (this.modalChartType === 'batteryType') {
            list = list.filter(b => {
              const bt = String(b.battery_type || '').toLowerCase();
              return bt.includes('lithium') || bt.includes('alkali');
            });
            if (this.selectedBatteryTypeFilter) {
              const target = this.selectedBatteryTypeFilter.toLowerCase();
              list = list.filter(b => String(b.battery_type || '').toLowerCase().includes(target));
            }
          }

          const activeArea = (this.modalFilterArea && this.modalFilterArea !== 'ALL') ? this.modalFilterArea :
            this.selectedAreaFilter;
          if (activeArea) list = list.filter(b => b.area_key === activeArea);

          const activeLine = (this.modalFilterLine && this.modalFilterLine !== 'ALL') ? this.modalFilterLine :
            this.selectedLineFilter;
          if (activeLine) list = list.filter(b => b.line === activeLine);

          if (this.modalFilterOp && this.modalFilterOp !== 'ALL') {
            list = list.filter(b => b.machine_no === this.modalFilterOp);
          }

          if (this.modalFilterMachine && this.modalFilterMachine !== 'ALL') {
            list = list.filter(b => b.machine_name === this.modalFilterMachine);
          }

          if (this.filterStatus === 'calendar') {
            const curMonth = '{{ now()->format('Y-m-') }}';
            list = list.filter(b => b.raw_next_replace_date && b.raw_next_replace_date.startsWith(curMonth));
          } else if (this.filterStatus && this.filterStatus !== 'all') {
            list = list.filter(b => b.status === this.filterStatus);
          }

          if (this.selectedModelFilter) {
            list = list.filter(b => b.battery_model === this.selectedModelFilter);
          }

          if (this.filterSpecificDate) {
            list = list.filter(b => b.raw_next_replace_date === this.filterSpecificDate);
          }

          if (this.searchQuery && this.searchQuery.trim() !== '') {
            const q = this.searchQuery.toLowerCase();
            list = list.filter(b =>
              (b.machine_name && String(b.machine_name).toLowerCase().includes(q)) ||
              (b.machine_no && String(b.machine_no).toLowerCase().includes(q)) ||
              (b.battery_id && String(b.battery_id).toLowerCase().includes(q)) ||
              (b.device && String(b.device).toLowerCase().includes(q)) ||
              (b.line && String(b.line).toLowerCase().includes(q)) ||
              (b.battery_model && String(b.battery_model).toLowerCase().includes(q)) ||
              (b.battery_type && String(b.battery_type).toLowerCase().includes(q))
            );
          }

          return list;
        },

        get totalPages() {
          return Math.ceil(this.filteredBatteries.length / this.perPage) || 1;
        },

        get paginatedBatteries() {
          const start = (this.currentPage - 1) * this.perPage;
          return this.filteredBatteries.slice(start, start + this.perPage);
        },

        nextPage() {
          if (this.currentPage < this.totalPages) this.currentPage++;
        },
        prevPage() {
          if (this.currentPage > 1) this.currentPage--;
        },
        goToPage(page) {
          if (page >= 1 && page <= this.totalPages) this.currentPage = page;
        },

        getModalChartData() {
          const stats = {};
          const filter = this.chartStatusFilter || 'all';
          const isLineChart = (this.modalChartType === 'lineHowMany' || this.modalChartType === 'areaLine' || this.modalChartType === 'deviceCount');
          const isDeviceCount = (this.modalChartType === 'deviceCount');
          const isHowManySum = (this.modalChartType === 'lineHowMany' || this.modalChartType === 'totalBattery' || this.modalChartType === 'batteryType' || this.modalChartType === 'areaLine');
          const isBatteryType = (this.modalChartType === 'batteryType');

          let items = (this.allBatteries && Array.isArray(this.allBatteries)) ? this.allBatteries : [];
          const activeArea = (this.modalFilterArea && this.modalFilterArea !== 'ALL') ? this.modalFilterArea :
            this.selectedAreaFilter;
          if (activeArea) items = items.filter(b => b.area_key === activeArea);
          const activeLine = (this.modalFilterLine && this.modalFilterLine !== 'ALL') ? this.modalFilterLine :
            this.selectedLineFilter;
          if (activeLine) items = items.filter(b => b.line === activeLine);
          if (this.modalFilterOp && this.modalFilterOp !== 'ALL') {
            items = items.filter(b => b.machine_no === this.modalFilterOp);
          }
          if (this.modalFilterMachine && this.modalFilterMachine !== 'ALL') {
            items = items.filter(b => b.machine_name === this.modalFilterMachine);
          }
          if (this.filterSpecificDate) items = items.filter(b => b.raw_next_replace_date === this
            .filterSpecificDate);

          for (let i = 0; i < items.length; i++) {
            const b = items[i];
            const qty = Number(b.how_many) || 0;
            let key;
            if (isLineChart) {
              key = (b.line && String(b.line).trim() !== '' && b.line !== '-') ? String(b.line).trim() :
                'Line Unknown';
            } else if (isBatteryType) {
              const bType = String(b.battery_type || '').toLowerCase();
              if (bType.includes('lithium')) key = 'Lithium';
              else if (bType.includes('alkali')) key = 'Alkali';
              else continue;
            } else {
              const m = (b.battery_model && String(b.battery_model).trim() !== '' && b.battery_model !== '-') ?
                String(b.battery_model).trim() : null;
              if (!m) continue;
              key = m;
            }

            let addVal;
            if (isDeviceCount) {
              const hasHm = Number(b.has_how_many) || (Number(b.how_many) > 0 ? 1 : 0);
              if (!hasHm) continue;
              addVal = 1;
            } else if (isHowManySum) {
              addVal = qty;
            } else {
              addVal = Number(b.aggregate) || 1;
            }

            if (!stats[key]) stats[key] = {
              active: 0,
              change: 0,
              warning: 0,
              total: 0
            };
            stats[key].total += addVal;
            if (b.status === 'active') stats[key].active += addVal;
            else if (b.status === 'change') stats[key].change += addVal;
            else if (b.status === 'warning') stats[key].warning += addVal;
          }

          const allKeys = Object.keys(stats);
          if (isBatteryType) {
            ['Lithium', 'Alkali'].forEach(k => {
              if (!stats[k]) {
                stats[k] = {
                  active: 0,
                  change: 0,
                  warning: 0,
                  total: 0
                };
                if (!allKeys.includes(k)) allKeys.push(k);
              }
            });
          }

          const sortedKeys = allKeys.slice().sort((a, b) => {
            const statA = stats[a] || {
              active: 0,
              change: 0,
              warning: 0,
              total: 0
            };
            const statB = stats[b] || {
              active: 0,
              change: 0,
              warning: 0,
              total: 0
            };
            let valA = filter === 'active' ? statA.active : filter === 'change' ? statA.change : filter ===
              'warning' ? statA.warning : statA.total;
            let valB = filter === 'active' ? statB.active : filter === 'change' ? statB.change : filter ===
              'warning' ? statB.warning : statB.total;
            return (valB - valA) || (statB.total - statA.total) || a.localeCompare(b);
          });

          return {
            labels: sortedKeys.length > 0 ? sortedKeys : ['Tidak Ada Data'],
            stats,
            isLine: isLineChart,
            isHowMany: isHowManySum,
            isDeviceCount: isDeviceCount,
            isBatteryType
          };
        },

        setChartStatusFilter(status) {
          this.chartStatusFilter = status;
          this.filterStatus = status;
          this.currentPage = 1;
          this.showBarChart = true;
          this.$nextTick(() => {
            this.renderModalBarChart();
            if (this.selectedLineFilter && (this.modalChartType === 'areaLine' || this.modalChartType === 'lineHowMany' || this.modalChartType === 'deviceCount' || this.modalChartType === 'totalBattery')) {
              this.renderMachineBarChart();
            }
          });
        },

        scheduleModalBarChartRender(scrollTarget = null) {
          const run = () => {
            this.renderModalBarChart();
            if (scrollTarget) this.scrollToTable();
          };
          this.$nextTick(() => {
            run();
            setTimeout(run, 80);
            setTimeout(() => {
              run();
              if (this.modalLineChartInstance) this.modalLineChartInstance.resize();
            }, 260);
          });
        },

        onModalAreaChange() {
          this.selectedAreaFilter = (this.modalFilterArea === 'ALL') ? null : this.modalFilterArea;
          this.modalFilterLine = 'ALL';
          this.selectedLineFilter = null;
          this.modalFilterOp = 'ALL';
          this.modalFilterMachine = 'ALL';
          this.currentPage = 1;
          this.renderModalBarChart();
          if (this.modalMachineChartInstance) {
            try {
              this.modalMachineChartInstance.destroy();
            } catch (e) {}
            this.modalMachineChartInstance = null;
          }
        },

        onModalLineChange() {
          this.selectedLineFilter = (this.modalFilterLine === 'ALL') ? null : this.modalFilterLine;
          this.modalFilterOp = 'ALL';
          this.modalFilterMachine = 'ALL';
          this.currentPage = 1;
          this.renderModalBarChart();
          if (this.selectedLineFilter && (this.modalChartType === 'areaLine' || this.modalChartType === 'lineHowMany' || this.modalChartType === 'deviceCount' || this.modalChartType === 'totalBattery')) {
            this.$nextTick(() => {
              this.renderMachineBarChart();
              this.scrollToMachineChart();
            });
          } else if (this.modalMachineChartInstance) {
            try {
              this.modalMachineChartInstance.destroy();
            } catch (e) {}
            this.modalMachineChartInstance = null;
          }
        },

        onModalOpChange() {
          if (this.modalFilterMachine !== 'ALL') {
            const validMachines = this.modalAvailableMachines;
            if (!validMachines.includes(this.modalFilterMachine)) {
              this.modalFilterMachine = 'ALL';
            }
          }
          this.currentPage = 1;
          this.renderModalBarChart();
          this.scrollToTable();
        },

        onModalMachineChange() {
          if (this.modalFilterOp !== 'ALL') {
            const validOps = this.modalAvailableOps;
            if (!validOps.includes(this.modalFilterOp)) {
              this.modalFilterOp = 'ALL';
            }
          }
          this.currentPage = 1;
          this.renderModalBarChart();
          this.scrollToTable();
        },

        resetModalDropdownFilters() {
          this.modalFilterArea = this.initialAreaKey ? this.initialAreaKey : 'ALL';
          this.selectedAreaFilter = (this.modalFilterArea !== 'ALL') ? this.modalFilterArea : null;
          this.modalFilterLine = 'ALL';
          this.selectedLineFilter = null;
          this.modalFilterOp = 'ALL';
          this.modalFilterMachine = 'ALL';
          this.selectedModelFilter = null;
          this.selectedBatteryTypeFilter = null;
          this.currentPage = 1;
          this.renderModalBarChart();
          if (this.modalMachineChartInstance) {
            try {
              this.modalMachineChartInstance.destroy();
            } catch (e) {}
            this.modalMachineChartInstance = null;
          }
        },

        scrollToMachineChart() {
          this.$nextTick(() => {
            setTimeout(() => {
              const scrollBody = document.getElementById('modalScrollableBody');
              const machineSection = document.getElementById('modalMachineChartSection');
              if (scrollBody && machineSection) {
                scrollBody.scrollTo({
                  top: Math.max(0, machineSection.offsetTop - 15),
                  behavior: 'smooth'
                });
              }
            }, 60);
          });
        },

        openTotalBatteryModal(model = null) {
          const pageArea = '{{ $selectedArea }}';
          const initArea = (pageArea && pageArea !== 'ALL') ?
            (pageArea.toLowerCase().includes('machin') ? 'machining' : (pageArea.toLowerCase().includes('shaft') || pageArea.toLowerCase().includes('housing') ? 'shaft' : 'engine')) : 'ALL';
          this.initialAreaKey = (initArea !== 'ALL') ? initArea : null;
          this.selectedAreaFilter = (initArea !== 'ALL') ? initArea : null;
          this.modalFilterArea = initArea;
          this.modalFilterLine = 'ALL';
          this.modalFilterOp = 'ALL';
          this.modalFilterMachine = 'ALL';
          this.modalChartType = 'totalBattery';
          this.isMachinePlanModal = true;
          this.showBarChart = true;
          this.chartStatusFilter = 'all';
          this.filterStatus = 'all';
          this.selectedLineFilter = null;
          this.selectedModelFilter = model;
          this.selectedBatteryTypeFilter = null;
          this.modalTitle = model ? `Total Battery - Model: ${model}` : 'Total Battery (SUM Kolom HowMany)';
          this.modalSubtitle = model ? `Daftar unit baterai model ${model} (SUM Kolom HowMany)` :
            'Grafik dan daftar data total battery berdasarkan SUM kolom HowMany';
          this.searchQuery = '';
          this.filterSpecificDate = null;
          this.currentPage = 1;
          this.modalOpen = true;
          if (this.modalMachineChartInstance) {
            try {
              this.modalMachineChartInstance.destroy();
            } catch (e) {}
            this.modalMachineChartInstance = null;
          }
          this.scheduleModalBarChartRender(model);
        },

        openJumlahDeviceModal(line = null) {
          const pageArea = '{{ $selectedArea }}';
          const initArea = (pageArea && pageArea !== 'ALL') ?
            (pageArea.toLowerCase().includes('machin') ? 'machining' : (pageArea.toLowerCase().includes('shaft') || pageArea.toLowerCase().includes('housing') ? 'shaft' : 'engine')) : 'ALL';
          this.initialAreaKey = (initArea !== 'ALL') ? initArea : null;
          this.selectedAreaFilter = (initArea !== 'ALL') ? initArea : null;
          this.modalFilterArea = initArea;
          this.modalFilterLine = line || 'ALL';
          this.modalFilterOp = 'ALL';
          this.modalFilterMachine = 'ALL';
          this.modalChartType = 'deviceCount';
          this.isMachinePlanModal = true;
          this.showBarChart = true;
          this.chartStatusFilter = 'all';
          this.filterStatus = 'all';
          this.selectedLineFilter = line;
          this.selectedModelFilter = null;
          this.selectedBatteryTypeFilter = null;
          this.modalTitle = line ? `Jumlah Device - Line: ${line}` :
            'Jumlah Device (Count Kolom HowMany)';
          this.modalSubtitle = 'Data jumlah device grafik batang per Line & daftar unit berdasarkan COUNT kolom HowMany';
          this.searchQuery = '';
          this.filterSpecificDate = null;
          this.currentPage = 1;
          this.modalOpen = true;
          if (this.modalMachineChartInstance) {
            try {
              this.modalMachineChartInstance.destroy();
            } catch (e) {}
            this.modalMachineChartInstance = null;
          }
          this.scheduleModalBarChartRender(line);
        },

        openJumlahBateraiModal(line = null) {
          this.openJumlahDeviceModal(line);
        },

        openAreaModal(areaKey, title, subtitle) {
          this.initialAreaKey = areaKey;
          this.selectedAreaFilter = areaKey;
          this.modalFilterArea = areaKey;
          this.modalFilterLine = 'ALL';
          this.modalFilterOp = 'ALL';
          this.modalFilterMachine = 'ALL';
          this.modalChartType = 'areaLine';
          this.isMachinePlanModal = true;
          this.showBarChart = true;
          this.chartStatusFilter = 'all';
          this.filterStatus = 'all';
          this.selectedLineFilter = null;
          this.selectedModelFilter = null;
          this.selectedBatteryTypeFilter = null;
          this.modalTitle = title;
          this.modalSubtitle = subtitle;
          this.searchQuery = '';
          this.filterSpecificDate = null;
          this.currentPage = 1;
          this.modalOpen = true;
          if (this.modalMachineChartInstance) {
            try {
              this.modalMachineChartInstance.destroy();
            } catch (e) {}
            this.modalMachineChartInstance = null;
          }
          this.scheduleModalBarChartRender();
        },

        openBatteryTypeModal(type = null) {
          const pageArea = '{{ $selectedArea }}';
          const initArea = (pageArea && pageArea !== 'ALL') ?
            (pageArea.toLowerCase().includes('machin') ? 'machining' : (pageArea.toLowerCase().includes('shaft') || pageArea.toLowerCase().includes('housing') ? 'shaft' : 'engine')) : 'ALL';
          this.initialAreaKey = (initArea !== 'ALL') ? initArea : null;
          this.selectedAreaFilter = (initArea !== 'ALL') ? initArea : null;
          this.modalFilterArea = initArea;
          this.modalFilterLine = 'ALL';
          this.modalFilterOp = 'ALL';
          this.modalFilterMachine = 'ALL';
          this.modalChartType = 'batteryType';
          this.isMachinePlanModal = true;
          this.showBarChart = true;
          this.chartStatusFilter = 'all';
          this.filterStatus = 'all';
          this.selectedLineFilter = null;
          this.selectedModelFilter = null;
          this.selectedBatteryTypeFilter = type;
          this.modalTitle = type ? `Battery Type - ${type}` : 'Battery Type (Lithium & Alkali)';
          this.modalSubtitle = 'Distribusi pemakaian tipe baterai (Lithium & Alkali)';
          this.searchQuery = '';
          this.filterSpecificDate = null;
          this.currentPage = 1;
          this.modalOpen = true;
          if (this.modalMachineChartInstance) {
            try {
              this.modalMachineChartInstance.destroy();
            } catch (e) {}
            this.modalMachineChartInstance = null;
          }
          this.scheduleModalBarChartRender(type);
        },

        openStatusModal(status, title, subtitle) {
          this.initialAreaKey = null;
          this.selectedAreaFilter = null;
          this.modalFilterArea = 'ALL';
          this.modalFilterLine = 'ALL';
          this.modalFilterOp = 'ALL';
          this.modalFilterMachine = 'ALL';
          this.modalChartType = 'lineHowMany';
          this.isMachinePlanModal = true;
          this.showBarChart = true;
          this.chartStatusFilter = status;
          this.filterStatus = status;
          this.selectedLineFilter = null;
          this.selectedModelFilter = null;
          this.selectedBatteryTypeFilter = null;
          this.modalTitle = title;
          this.modalSubtitle = subtitle;
          this.searchQuery = '';
          this.filterSpecificDate = null;
          this.currentPage = 1;
          this.modalOpen = true;
          if (this.modalMachineChartInstance) {
            try {
              this.modalMachineChartInstance.destroy();
            } catch (e) {}
            this.modalMachineChartInstance = null;
          }
          this.scheduleModalBarChartRender();
        },

        openModal(status, title, subtitle, specificDate = null) {
          this.initialAreaKey = null;
          this.selectedAreaFilter = null;
          this.modalFilterArea = 'ALL';
          this.modalFilterLine = 'ALL';
          this.modalFilterOp = 'ALL';
          this.modalFilterMachine = 'ALL';
          this.isMachinePlanModal = false;
          this.showBarChart = false;
          this.chartStatusFilter = (status === 'active' || status === 'change' || status === 'warning') ?
            status : 'all';
          this.filterStatus = (status === 'total' || status === 'all' || status === 'day' || status ===
            'calendar') ? 'all' : status;
          this.selectedLineFilter = null;
          this.selectedModelFilter = null;
          this.selectedBatteryTypeFilter = null;
          this.modalTitle = title;
          this.modalSubtitle = subtitle;
          this.searchQuery = '';
          this.filterSpecificDate = specificDate;
          this.currentPage = 1;
          this.modalOpen = true;
          if (this.modalMachineChartInstance) {
            try {
              this.modalMachineChartInstance.destroy();
            } catch (e) {}
            this.modalMachineChartInstance = null;
          }
        },

        viewSpec(item) {
          this.activeSpec = item;
          this.specOpen = true;
        },

        closeSpecModal() {
          this.specOpen = false;
          this.modalOpen = true;
          this.showBarChart = true;
          this.$nextTick(() => {
            this.scrollToChart();
          });
        },

        scrollToTable() {
          this.$nextTick(() => {
            setTimeout(() => {
              const scrollBody = document.getElementById('modalScrollableBody');
              const tableSec = document.getElementById('modalBatteryTableSection');
              if (scrollBody && tableSec) {
                scrollBody.scrollTo({
                  top: Math.max(0, tableSec.offsetTop - 15),
                  behavior: 'smooth'
                });
              } else if (tableSec) {
                tableSec.scrollIntoView({
                  behavior: 'smooth',
                  block: 'start'
                });
              }
            }, 60);
          });
        },

        scrollToChart() {
          const scrollBody = document.getElementById('modalScrollableBody');
          if (scrollBody) {
            scrollBody.scrollTo({
              top: 0,
              behavior: 'smooth'
            });
          }
        },

        renderHowManyDonutChart() {
          const ctx = document.getElementById('howManyDonutChart');
          if (!ctx) return;
          const existing = Chart.getChart(ctx);
          if (existing) existing.destroy();
          if (this.howManyDonutChartInstance) {
            try {
              this.howManyDonutChartInstance.destroy();
            } catch (e) {}
            this.howManyDonutChartInstance = null;
          }

          const topModels = @json($topModels);
          const labels = Object.keys(topModels);
          const data = Object.values(topModels);
          const colors = ['#6366f1', '#8b5cf6', '#a855f7', '#06b6d4', '#10b981', '#f59e0b', '#ec4899'];

          this.howManyDonutChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
              labels: labels,
              datasets: [{
                data: data.length > 0 ? data : [1],
                backgroundColor: colors.slice(0, Math.max(labels.length, 1)),
                borderColor: '#0f172a',
                borderWidth: 2,
                hoverOffset: 4
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              cutout: '72%',
              onClick: () => {
                this.openJumlahDeviceModal();
              },
              plugins: {
                legend: {
                  display: false
                },
                tooltip: {
                  backgroundColor: '#0f172a',
                  titleColor: '#818cf8',
                  bodyColor: '#ffffff',
                  borderColor: '#334155',
                  borderWidth: 1,
                  callbacks: {
                    label: function(context) {
                      return ' ' + context.label + ': ' + context.raw + ' device';
                    }
                  }
                }
              }
            }
          });
        },

        renderAreaMachiningDonutChart() {
          const ctx = document.getElementById('machiningDonutChart');
          if (!ctx) return;
          const existing = Chart.getChart(ctx);
          if (existing) existing.destroy();
          if (this.machiningDonutChartInstance) {
            try {
              this.machiningDonutChartInstance.destroy();
            } catch (e) {}
            this.machiningDonutChartInstance = null;
          }

          const stats = @json($areaMachiningStats);
          this.machiningDonutChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
              labels: ['Aktif', 'Change', 'Warning / Error'],
              datasets: [{
                data: [stats.active || 0, stats.change || 0, stats.warning || 0],
                backgroundColor: ['#10b981', '#ef4444', '#f59e0b'],
                borderColor: '#0f172a',
                borderWidth: 2,
                hoverOffset: 4
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              cutout: '72%',
              plugins: {
                legend: {
                  display: false
                },
                tooltip: {
                  backgroundColor: '#0f172a',
                  titleColor: '#10b981',
                  bodyColor: '#ffffff',
                  borderColor: '#334155',
                  borderWidth: 1
                }
              }
            }
          });
        },

        renderAreaShaftDonutChart() {
          const ctx = document.getElementById('shaftDonutChart');
          if (!ctx) return;
          const existing = Chart.getChart(ctx);
          if (existing) existing.destroy();
          if (this.shaftDonutChartInstance) {
            try {
              this.shaftDonutChartInstance.destroy();
            } catch (e) {}
            this.shaftDonutChartInstance = null;
          }

          const stats = @json($areaShaftStats);
          this.shaftDonutChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
              labels: ['Aktif', 'Change', 'Warning / Error'],
              datasets: [{
                data: [stats.active || 0, stats.change || 0, stats.warning || 0],
                backgroundColor: ['#10b981', '#ef4444', '#f59e0b'],
                borderColor: '#0f172a',
                borderWidth: 2,
                hoverOffset: 4
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              cutout: '72%',
              plugins: {
                legend: {
                  display: false
                },
                tooltip: {
                  backgroundColor: '#0f172a',
                  titleColor: '#06b6d4',
                  bodyColor: '#ffffff',
                  borderColor: '#334155',
                  borderWidth: 1
                }
              }
            }
          });
        },

        renderAreaEngineDonutChart() {
          const ctx = document.getElementById('engineDonutChart');
          if (!ctx) return;
          const existing = Chart.getChart(ctx);
          if (existing) existing.destroy();
          if (this.engineDonutChartInstance) {
            try {
              this.engineDonutChartInstance.destroy();
            } catch (e) {}
            this.engineDonutChartInstance = null;
          }

          const stats = @json($areaEngineStats);
          this.engineDonutChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
              labels: ['Aktif', 'Change', 'Warning / Error'],
              datasets: [{
                data: [stats.active || 0, stats.change || 0, stats.warning || 0],
                backgroundColor: ['#10b981', '#ef4444', '#f59e0b'],
                borderColor: '#0f172a',
                borderWidth: 2,
                hoverOffset: 4
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              cutout: '72%',
              plugins: {
                legend: {
                  display: false
                },
                tooltip: {
                  backgroundColor: '#0f172a',
                  titleColor: '#6366f1',
                  bodyColor: '#ffffff',
                  borderColor: '#334155',
                  borderWidth: 1
                }
              }
            }
          });
        },

        renderBatteryTypeDonutChart() {
          const ctx = document.getElementById('batteryTypeMiniDonutChart');
          if (!ctx) return;
          const existing = Chart.getChart(ctx);
          if (existing) existing.destroy();
          if (this.batteryTypeDonutChartInstance) {
            try {
              this.batteryTypeDonutChartInstance.destroy();
            } catch (e) {}
            this.batteryTypeDonutChartInstance = null;
          }

          const lithiumRate = Number({{ $lithiumRate }}) || 0;
          const alkaliRate = Number({{ $alkaliRate }}) || 0;
          const lithiumCount = Number({{ $lithiumCount }}) || 0;
          const alkaliCount = Number({{ $alkaliCount }}) || 0;

          this.batteryTypeDonutChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
              labels: ['Lithium', 'Alkali'],
              datasets: [{
                data: [lithiumRate, alkaliRate],
                backgroundColor: ['#06b6d4', '#f59e0b'],
                borderColor: '#0f172a',
                borderWidth: 1.5,
                hoverOffset: 2
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              cutout: '68%',
              plugins: {
                legend: {
                  display: false
                },
                tooltip: {
                  backgroundColor: '#0f172a',
                  borderColor: '#334155',
                  borderWidth: 1,
                  callbacks: {
                    label: function(context) {
                      const idx = context.dataIndex;
                      const count = (idx === 0) ? lithiumCount : alkaliCount;
                      return ` ${context.label}: ${context.raw}% (${count} Unit)`;
                    }
                  }
                }
              },
              onClick: (evt, elements) => {
                if (elements && elements.length > 0) {
                  const idx = elements[0].index;
                  this.openBatteryTypeModal((idx === 0) ? 'Lithium' : 'Alkali');
                }
              }
            }
          });
        },

        renderModalBarChart() {
          const canvas = document.getElementById('modalLineStatusBarChart');
          if (!canvas) return;

          try {
            const existing = Chart.getChart(canvas);
            if (existing) existing.destroy();
            if (this.modalLineChartInstance) {
              try {
                this.modalLineChartInstance.destroy();
              } catch (e) {}
              this.modalLineChartInstance = null;
            }

            const chartData = this.getModalChartData();
            const labels = chartData.labels || ['Tidak Ada Data'];
            const stats = chartData.stats || {};
            const isLine = !!chartData.isLine;
            const isHowMany = !!chartData.isHowMany;
            const isDeviceCount = !!chartData.isDeviceCount;
            const isBatteryType = !!chartData.isBatteryType;
            const filter = this.chartStatusFilter || 'all';

            const activeData = labels.map(l => (stats[l] && stats[l].active) ? stats[l].active : 0);
            const changeData = labels.map(l => (stats[l] && stats[l].change) ? stats[l].change : 0);
            const warningData = labels.map(l => (stats[l] && stats[l].warning) ? stats[l].warning : 0);
            const totalData = labels.map(l => (stats[l] && stats[l].total) ? stats[l].total : 0);

            const unitSuffix = isDeviceCount ? '(Device)' : (isHowMany ? '(Pcs)' : '(Unit)');
            const datasets = [];

            if (filter === 'all') {
              if (isBatteryType) {
                datasets.push({
                  label: 'Jumlah Unit (Lithium & Alkali)',
                  data: totalData,
                  backgroundColor: labels.map(l => l.toLowerCase().includes('lithium') ?
                    'rgba(6, 182, 212, 0.85)' : 'rgba(245, 158, 11, 0.85)'),
                  borderColor: labels.map(l => l.toLowerCase().includes('lithium') ? '#22d3ee' : '#fbbf24'),
                  borderWidth: 1.5,
                  borderRadius: 4
                });
              } else if (isDeviceCount) {
                datasets.push({
                  label: 'Jumlah Device (Count HowMany)',
                  data: totalData,
                  backgroundColor: 'rgba(99, 102, 241, 0.85)',
                  borderColor: '#818cf8',
                  borderWidth: 1.5,
                  borderRadius: 4
                });
              } else {
                datasets.push({
                  label: isHowMany ? 'Total Battery (SUM HowMany)' : (isLine ? 'Jumlah Battery (Unit)' :
                    `Total ${unitSuffix}`),
                  data: totalData,
                  backgroundColor: isHowMany ? 'rgba(6, 182, 212, 0.85)' : (isLine ?
                    'rgba(14, 165, 233, 0.85)' : 'rgba(6, 182, 212, 0.85)'),
                  borderColor: isHowMany ? '#22d3ee' : (isLine ? '#38bdf8' : '#22d3ee'),
                  borderWidth: 1.5,
                  borderRadius: 4
                });
              }
            } else if (filter === 'active') {
              datasets.push({
                label: `Aktif ${unitSuffix}`,
                data: activeData,
                backgroundColor: 'rgba(16, 185, 129, 0.85)',
                borderColor: '#34d399',
                borderWidth: 1.5,
                borderRadius: 4
              });
            } else if (filter === 'change') {
              datasets.push({
                label: `Change ${unitSuffix}`,
                data: changeData,
                backgroundColor: 'rgba(239, 68, 68, 0.85)',
                borderColor: '#f87171',
                borderWidth: 1.5,
                borderRadius: 4
              });
            } else if (filter === 'warning') {
              datasets.push({
                label: `Warning ${unitSuffix}`,
                data: warningData,
                backgroundColor: 'rgba(245, 158, 11, 0.85)',
                borderColor: '#fbbf24',
                borderWidth: 1.5,
                borderRadius: 4
              });
            }

            const ctx = canvas.getContext('2d');
            this.modalLineChartInstance = new Chart(ctx, {
              type: 'bar',
              data: {
                labels: labels,
                datasets: datasets
              },
              options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                  duration: 200
                },
                scales: {
                  x: {
                    title: {
                      display: true,
                      text: isLine ? 'Line' : (isBatteryType ? 'Tipe Baterai' : 'Battery Model'),
                      color: '#94a3b8',
                      font: {
                        size: 11,
                        weight: 'bold',
                        family: 'monospace'
                      }
                    },
                    ticks: {
                      autoSkip: false,
                      color: '#94a3b8',
                      font: {
                        size: 10,
                        family: 'monospace'
                      },
                      maxRotation: 45
                    },
                    grid: {
                      color: 'rgba(30, 41, 59, 0.4)'
                    }
                  },
                  y: {
                    beginAtZero: true,
                    suggestedMax: 10,
                    title: {
                      display: true,
                      text: isHowMany ? 'Total HowMany (Pcs)' : (isLine ? 'Jumlah Battery (Unit)' :
                        'Jumlah Unit'),
                      color: '#94a3b8',
                      font: {
                        size: 11,
                        weight: 'bold',
                        family: 'monospace'
                      }
                    },
                    ticks: {
                      color: '#94a3b8',
                      font: {
                        size: 10,
                        family: 'monospace'
                      },
                      precision: 0
                    },
                    grid: {
                      color: 'rgba(30, 41, 59, 0.5)'
                    }
                  }
                },
                plugins: {
                  legend: {
                    display: true,
                    position: 'top',
                    labels: {
                      color: '#cbd5e1',
                      font: {
                        size: 11,
                        family: 'monospace'
                      },
                      boxWidth: 14,
                      boxHeight: 14
                    }
                  },
                  tooltip: {
                    backgroundColor: '#0f172a',
                    borderColor: '#334155',
                    borderWidth: 1,
                    callbacks: {
                      title: (tooltipItems) => tooltipItems.length ? (isLine ? 'Line: ' : (isBatteryType ?
                        'Tipe: ' : 'Model: ')) + tooltipItems[0].label : '',
                      label: (context) => {
                        const idx = context.dataIndex;
                        const unitLabel = isHowMany ? 'pcs' : 'unit';
                        const total = totalData[idx] || 0;
                        if (filter === 'all') return ` Total: ${total} ${unitLabel}`;
                        if (filter === 'active')
                          return ` Aktif: ${activeData[idx] || 0} ${unitLabel} (Total: ${total} ${unitLabel})`;
                        if (filter === 'change')
                          return ` Change: ${changeData[idx] || 0} ${unitLabel} (Total: ${total} ${unitLabel})`;
                        return ` Warning: ${warningData[idx] || 0} ${unitLabel} (Total: ${total} ${unitLabel})`;
                      }
                    }
                  }
                },
                onClick: (evt, elements) => {
                  if (elements && elements.length > 0) {
                    const idx = elements[0].index;
                    const clickedKey = labels[idx];
                    if (clickedKey && clickedKey !== 'Tidak Ada Data') {
                      if (isLine) {
                        this.selectedLineFilter = clickedKey;
                        this.modalFilterLine = clickedKey;
                        this.modalFilterOp = 'ALL';
                        this.modalFilterMachine = 'ALL';
                        this.currentPage = 1;
                        if (this.modalChartType === 'areaLine' || this.modalChartType === 'lineHowMany' || this.modalChartType === 'deviceCount' || this.modalChartType === 'totalBattery') {
                          this.$nextTick(() => {
                            this.renderMachineBarChart();
                            this.scrollToMachineChart();
                          });
                        } else {
                          this.scrollToTable();
                        }
                      } else if (isBatteryType) {
                        this.selectedBatteryTypeFilter = (this.selectedBatteryTypeFilter === clickedKey) ?
                          null : clickedKey;
                        this.currentPage = 1;
                        this.scrollToTable();
                      } else {
                        this.selectedModelFilter = clickedKey;
                        this.currentPage = 1;
                        this.scrollToTable();
                      }
                    }
                  }
                }
              }
            });
          } catch (err) {
            console.error('Error rendering modal bar chart:', err);
          }
        },

        renderModalLineChart() {
          this.renderModalBarChart();
        },

        renderMachineBarChart() {
          const canvas = document.getElementById('modalMachineStatusBarChart');
          if (!canvas) return;

          try {
            const existing = Chart.getChart(canvas);
            if (existing) existing.destroy();
            if (this.modalMachineChartInstance) {
              try {
                this.modalMachineChartInstance.destroy();
              } catch (e) {}
              this.modalMachineChartInstance = null;
            }
            if (!this.selectedLineFilter) return;

            let items = (this.allBatteries && Array.isArray(this.allBatteries)) ? this.allBatteries : [];
            const activeArea = (this.modalFilterArea && this.modalFilterArea !== 'ALL') ? this.modalFilterArea :
              this.selectedAreaFilter;
            if (activeArea) items = items.filter(b => b.area_key === activeArea);
            items = items.filter(b => b.line === this.selectedLineFilter);

            const isDeviceCount = (this.modalChartType === 'deviceCount');
            const isHowManySum = (this.modalChartType === 'lineHowMany' || this.modalChartType === 'totalBattery' || this.modalChartType === 'areaLine');
            const unitLabel = isDeviceCount ? 'Device' : (isHowManySum ? 'Pcs' : 'Unit');

            const machineStats = {};
            for (let i = 0; i < items.length; i++) {
              const b = items[i];
              const mNo = (b.machine_no && String(b.machine_no).trim() !== '' && b.machine_no !== '-') ? String(
                b.machine_no).trim() : 'Unknown-OP';
              if (!machineStats[mNo]) machineStats[mNo] = {
                total: 0,
                active: 0,
                change: 0,
                warning: 0,
                machine_name: b.machine_name || ''
              };
              let addVal;
              if (isDeviceCount) {
                const hasHm = Number(b.has_how_many) || (Number(b.how_many) > 0 ? 1 : 0);
                if (!hasHm) continue;
                addVal = 1;
              } else if (isHowManySum) {
                addVal = Number(b.how_many) || 0;
              } else {
                addVal = Number(b.aggregate) || 1;
              }
              machineStats[mNo].total += addVal;
              if (b.status === 'active') machineStats[mNo].active += addVal;
              else if (b.status === 'change') machineStats[mNo].change += addVal;
              else if (b.status === 'warning') machineStats[mNo].warning += addVal;
            }

            const allMachines = Object.keys(machineStats);
            const filter = this.chartStatusFilter || 'all';

            const sortedMachines = allMachines.sort((a, b) => {
              const stA = machineStats[a] || {
                active: 0,
                change: 0,
                warning: 0,
                total: 0
              };
              const stB = machineStats[b] || {
                active: 0,
                change: 0,
                warning: 0,
                total: 0
              };
              let valA = filter === 'active' ? stA.active : filter === 'change' ? stA.change : filter ===
                'warning' ? stA.warning : stA.total;
              let valB = filter === 'active' ? stB.active : filter === 'change' ? stB.change : filter ===
                'warning' ? stB.warning : stB.total;
              return (valB - valA) || (stB.total - stA.total) || a.localeCompare(b);
            });

            const labels = sortedMachines.length > 0 ? sortedMachines : ['Tidak Ada Data'];
            const totalData = labels.map(m => machineStats[m] ? machineStats[m].total : 0);
            const activeData = labels.map(m => machineStats[m] ? machineStats[m].active : 0);
            const changeData = labels.map(m => machineStats[m] ? machineStats[m].change : 0);
            const warningData = labels.map(m => machineStats[m] ? machineStats[m].warning : 0);

            const mainLabel = isDeviceCount ? 'Jumlah Device (Count HowMany)' : (isHowManySum ? 'Total Battery (SUM HowMany)' : 'Jumlah Battery (Unit)');
            const activeLabel = `Aktif (${unitLabel})`;
            const changeLabel = `Change (${unitLabel})`;
            const warningLabel = `Warning (${unitLabel})`;

            const datasets = [];
            if (filter === 'all') datasets.push({
              label: mainLabel,
              data: totalData,
              backgroundColor: isDeviceCount ? 'rgba(99, 102, 241, 0.85)' : 'rgba(6, 182, 212, 0.85)',
              borderColor: isDeviceCount ? '#818cf8' : '#22d3ee',
              borderWidth: 1.5,
              borderRadius: 4
            });
            else if (filter === 'active') datasets.push({
              label: activeLabel,
              data: activeData,
              backgroundColor: 'rgba(16, 185, 129, 0.85)',
              borderColor: '#34d399',
              borderWidth: 1.5,
              borderRadius: 4
            });
            else if (filter === 'change') datasets.push({
              label: changeLabel,
              data: changeData,
              backgroundColor: 'rgba(239, 68, 68, 0.85)',
              borderColor: '#f87171',
              borderWidth: 1.5,
              borderRadius: 4
            });
            else if (filter === 'warning') datasets.push({
              label: warningLabel,
              data: warningData,
              backgroundColor: 'rgba(245, 158, 11, 0.85)',
              borderColor: '#fbbf24',
              borderWidth: 1.5,
              borderRadius: 4
            });

            const ctx = canvas.getContext('2d');
            this.modalMachineChartInstance = new Chart(ctx, {
              type: 'bar',
              data: {
                labels: labels,
                datasets: datasets
              },
              options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                  duration: 200
                },
                onClick: (evt, elements) => {
                  if (elements && elements.length > 0) {
                    const idx = elements[0].index;
                    const clickedMachine = labels[idx];
                    if (clickedMachine && clickedMachine !== 'Tidak Ada Data') {
                      this.modalFilterOp = clickedMachine;
                      this.modalFilterMachine = 'ALL';
                      this.currentPage = 1;
                      this.scrollToTable();
                    }
                  }
                },
                scales: {
                  x: {
                    title: {
                      display: true,
                      text: 'Machine No (OP)',
                      color: '#94a3b8',
                      font: {
                        size: 11,
                        weight: 'bold',
                        family: 'monospace'
                      }
                    },
                    ticks: {
                      color: '#94a3b8',
                      font: {
                        size: 10,
                        family: 'monospace'
                      },
                      maxRotation: 45
                    },
                    grid: {
                      color: 'rgba(30, 41, 59, 0.4)'
                    }
                  },
                  y: {
                    beginAtZero: true,
                    suggestedMax: 5,
                    title: {
                      display: true,
                      text: isDeviceCount ? 'Jumlah Device' : (isHowManySum ? 'Total Battery (Pcs)' : 'Jumlah Battery (Unit)'),
                      color: '#94a3b8',
                      font: {
                        size: 11,
                        weight: 'bold',
                        family: 'monospace'
                      }
                    },
                    ticks: {
                      color: '#94a3b8',
                      font: {
                        size: 10,
                        family: 'monospace'
                      },
                      precision: 0
                    },
                    grid: {
                      color: 'rgba(30, 41, 59, 0.5)'
                    }
                  }
                },
                plugins: {
                  legend: {
                    display: true,
                    position: 'top',
                    labels: {
                      color: '#cbd5e1',
                      font: {
                        size: 11,
                        family: 'monospace'
                      }
                    }
                  },
                  tooltip: {
                    backgroundColor: '#0f172a',
                    borderColor: '#334155',
                    borderWidth: 1,
                    callbacks: {
                      label: (context) => {
                        const idx = context.dataIndex;
                        const total = totalData[idx] || 0;
                        if (filter === 'all') return ` Total: ${total} ${unitLabel}`;
                        if (filter === 'active')
                          return ` Aktif: ${activeData[idx] || 0} ${unitLabel} (Total: ${total} ${unitLabel})`;
                        if (filter === 'change')
                          return ` Change: ${changeData[idx] || 0} ${unitLabel} (Total: ${total} ${unitLabel})`;
                        return ` Warning: ${warningData[idx] || 0} ${unitLabel} (Total: ${total} ${unitLabel})`;
                      }
                    }
                  }
                }
              }
            });
          } catch (err) {
            console.error('Error rendering machine bar chart:', err);
          }
        },

        renderDonutChart() {
          const ctx = document.getElementById('modelDonutChart');
          if (!ctx) return;
          const existing = Chart.getChart(ctx);
          if (existing) existing.destroy();
          if (this.modelDonutChartInstance) {
            try {
              this.modelDonutChartInstance.destroy();
            } catch (e) {}
            this.modelDonutChartInstance = null;
          }

          const topHowMany = @json($topModelHowMany);
          const labels = Object.keys(topHowMany);
          const data = Object.values(topHowMany);

          this.modelDonutChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
              labels: labels,
              datasets: [{
                data: data.length > 0 ? data : [1],
                backgroundColor: ['#06b6d4', '#6366f1', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6',
                  '#64748b'
                ],
                borderColor: '#0f172a',
                borderWidth: 2,
                hoverOffset: 6
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              cutout: '72%',
              onClick: (evt, elements) => {
                if (elements && elements.length > 0) {
                  const selectedModel = labels[elements[0].index];
                  this.openTotalBatteryModal((selectedModel && selectedModel !== 'Others') ?
                    selectedModel : null);
                }
              },
              plugins: {
                legend: {
                  display: false
                },
                tooltip: {
                  backgroundColor: '#0f172a',
                  titleColor: '#06b6d4',
                  bodyColor: '#ffffff',
                  borderColor: '#334155',
                  borderWidth: 1,
                  callbacks: {
                    label: c => ` ${c.label}: ${c.raw || 0} pcs (Klik untuk filter)`
                  }
                }
              }
            }
          });
        },

        renderLineStatusBarChart() {
          const ctx = document.getElementById('lineStatusBarChart');
          if (!ctx) return;
          const existing = Chart.getChart(ctx);
          if (existing) existing.destroy();

          const topMachines = this.topFrequentMachines || [];
          const labels = topMachines.map(m => m.short_label || m.machine_name);
          const replaceData = topMachines.map(m => m.replace_count || 0);
          const updateData = topMachines.map(m => m.update_count || 0);

          new Chart(ctx, {
            type: 'bar',
            data: {
              labels: labels.length > 0 ? labels : ['No Data'],
              datasets: [
                {
                  label: 'Replace on Exchange',
                  data: replaceData.length > 0 ? replaceData : [0],
                  backgroundColor: '#6366f1',
                  hoverBackgroundColor: '#818cf8',
                  borderRadius: 4
                },
                {
                  label: 'Update on Exchange',
                  data: updateData.length > 0 ? updateData : [0],
                  backgroundColor: '#a855f7',
                  hoverBackgroundColor: '#c084fc',
                  borderRadius: 4
                }
              ]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              onClick: (evt, elements) => {
                this.openFrequentExchangeModal();
              },
              scales: {
                x: {
                  stacked: true,
                  ticks: {
                    autoSkip: false,
                    color: '#94a3b8',
                    font: {
                      size: 9,
                      family: 'monospace'
                    },
                    maxRotation: 45
                  },
                  grid: {
                    display: false
                  }
                },
                y: {
                  stacked: true,
                  ticks: {
                    color: '#94a3b8',
                    font: {
                      size: 10,
                      family: 'monospace'
                    },
                    precision: 0
                  },
                  grid: {
                    color: '#1e293b'
                  }
                }
              },
              plugins: {
                legend: {
                  position: 'top',
                  align: 'end',
                  labels: {
                    boxWidth: 10,
                    boxHeight: 10,
                    color: '#cbd5e1',
                    font: {
                      size: 10,
                      family: 'monospace'
                    }
                  }
                },
                tooltip: {
                  backgroundColor: '#0f172a',
                  borderColor: '#334155',
                  borderWidth: 1,
                  padding: 10,
                  callbacks: {
                    title: (items) => {
                      const idx = items[0].dataIndex;
                      const item = topMachines[idx];
                      return item ? `${item.equipment_type} - ${item.machine_name}` : '';
                    },
                    afterTitle: (items) => {
                      const idx = items[0].dataIndex;
                      const item = topMachines[idx];
                      return item ? `Line: ${item.line} | OP: ${item.op_number || '-'} | Area: ${item.area || '-'}` : '';
                    },
                    afterBody: (items) => {
                      const idx = items[0].dataIndex;
                      const item = topMachines[idx];
                      return item ? `Model: ${item.battery_model || '-'}\nTotal Penggantian: ${item.total}x\nTgl Terakhir: ${item.last_date}` : '';
                    }
                  }
                }
              }
            }
          });
        },

        renderTrendChart() {
          const ctx = document.getElementById('replacementTrendChart');
          if (!ctx) return;
          const existing = Chart.getChart(ctx);
          if (existing) existing.destroy();

          const monthly = @json($monthlySchedule);
          const labels = Object.keys(monthly);
          const data = Object.values(monthly);

          new Chart(ctx, {
            type: 'line',
            data: {
              labels: labels.length > 0 ? labels : ['Current Month', 'Next Month'],
              datasets: [{
                label: 'Scheduled Replacements',
                data: data.length > 0 ? data : [0, 0],
                borderColor: '#06b6d4',
                backgroundColor: 'rgba(6, 182, 212, 0.1)',
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#06b6d4',
                pointRadius: 4
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              scales: {
                x: {
                  ticks: {
                    color: '#94a3b8',
                    font: {
                      size: 10
                    }
                  },
                  grid: {
                    display: false
                  }
                },
                y: {
                  ticks: {
                    color: '#94a3b8',
                    font: {
                      size: 10
                    },
                    stepSize: 1
                  },
                  grid: {
                    color: '#1e293b'
                  }
                }
              },
              plugins: {
                legend: {
                  display: false
                },
                tooltip: {
                  backgroundColor: '#0f172a',
                  borderColor: '#334155',
                  borderWidth: 1
                }
              }
            }
          });
        },

        renderDeviceBarChart() {
          const ctx = document.getElementById('deviceBarChart');
          if (!ctx) return;
          const existing = Chart.getChart(ctx);
          if (existing) existing.destroy();

          const devices = @json($deviceCounts);
          const labels = Object.keys(devices).filter(k => (devices[k] || 0) > 0).sort((a, b) => (devices[b] -
            devices[a]) || a.localeCompare(b)).slice(0, 10);
          const data = labels.map(k => devices[k]);

          new Chart(ctx, {
            type: 'bar',
            data: {
              labels: labels.length > 0 ? labels : ['No Data'],
              datasets: [{
                label: 'Total Unit',
                data: data.length > 0 ? data : [0],
                backgroundColor: 'rgba(99, 102, 241, 0.85)',
                borderColor: '#818cf8',
                borderWidth: 1.5,
                borderRadius: 4
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              scales: {
                x: {
                  ticks: {
                    autoSkip: false,
                    color: '#94a3b8',
                    font: {
                      size: 9,
                      family: 'monospace'
                    },
                    maxRotation: 45
                  },
                  grid: {
                    display: false
                  }
                },
                y: {
                  beginAtZero: true,
                  ticks: {
                    color: '#94a3b8',
                    font: {
                      size: 10,
                      family: 'monospace'
                    },
                    precision: 0
                  },
                  grid: {
                    color: '#1e293b'
                  }
                }
              },
              plugins: {
                legend: {
                  display: false
                },
                tooltip: {
                  backgroundColor: '#0f172a',
                  borderColor: '#334155',
                  borderWidth: 1
                }
              }
            }
          });
        }
      }));
    });
  </script>
@endpush

@extends('layouts.alpineLayout')

@section('title', 'Preventive Maintenance Command Center')
@section('page-title', 'Preventive Machine Dashboard')

@section('content')
  <div class="space-y-6" x-data="dashboardAnalytics()">

    <!-- 1. TOP HEADER ACTION BAR -->
    <div
      class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-4 rounded-2xl bg-slate-900/80 border border-slate-800 shadow-xl">

      <!-- Filter Buttons & Time Range -->
      <div class="flex items-center flex-wrap gap-2 text-xs">
        <div class="inline-flex rounded-xl p-1 bg-slate-950/70 border border-slate-800">
          <button @click="timeRange = 'day'"
            :class="timeRange === 'day' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'"
            class="px-3 py-1.5 rounded-lg transition">Today</button>
          <button @click="timeRange = 'week'"
            :class="timeRange === 'week' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'"
            class="px-3 py-1.5 rounded-lg transition">This Week</button>
          <button @click="timeRange = 'month'"
            :class="timeRange === 'month' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'"
            class="px-3 py-1.5 rounded-lg transition">Sep 2026</button>
        </div>

        <!-- Shift Selector -->
        <!-- <select class="bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
              <option>All Shifts</option>
              <option selected>Shift 1 (07:00 - 15:30)</option>
              <option>Shift 2 (15:30 - 23:00)</option>
              <option>Shift 3 (23:00 - 07:00)</option>
            </select> -->

        <button
          class="flex items-center gap-1.5 px-3 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl transition shadow-md shadow-indigo-600/30">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          <span>Refresh</span>
        </button>
      </div>
    </div>

    <!-- 2. HIGH-LEVEL KPI METRICS (4 CARDS) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

      <!-- KPI 1: Availability -->
      <div
        class="p-4 rounded-2xl bg-slate-900 border border-slate-800 relative overflow-hidden group hover:border-indigo-500/50 transition duration-300">
        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-indigo-500/5 rounded-full blur-xl pointer-events-none"></div>
        <div class="flex items-center justify-between text-xs text-slate-400 mb-2">
          <span class="font-medium uppercase tracking-wider">Machine Availability</span>
          <span
            class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">+1.2%</span>
        </div>
        <div class="flex items-baseline gap-2">
          <span class="text-3xl font-extrabold text-white font-mono">96.4%</span>
          <span class="text-xs text-slate-400">/ Target 95.0%</span>
        </div>
        <div class="w-full bg-slate-800 h-1.5 rounded-full mt-3 overflow-hidden">
          <div class="bg-indigo-500 h-full rounded-full" style="width: 96.4%"></div>
        </div>
      </div>

      <!-- KPI 2: OEE -->
      <div
        class="p-4 rounded-2xl bg-slate-900 border border-slate-800 relative overflow-hidden group hover:border-emerald-500/50 transition duration-300">
        <div class="flex items-center justify-between text-xs text-slate-400 mb-2">
          <span class="font-medium uppercase tracking-wider">Overall OEE Rate</span>
          <span
            class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">Class
            A</span>
        </div>
        <div class="flex items-baseline gap-2">
          <span class="text-3xl font-extrabold text-white font-mono">88.2%</span>
          <span class="text-xs text-slate-400">Benchmark: 85%</span>
        </div>
        <div class="w-full bg-slate-800 h-1.5 rounded-full mt-3 overflow-hidden">
          <div class="bg-emerald-500 h-full rounded-full" style="width: 88.2%"></div>
        </div>
      </div>

      <!-- KPI 3: PM Compliance -->
      <div
        class="p-4 rounded-2xl bg-slate-900 border border-slate-800 relative overflow-hidden group hover:border-amber-500/50 transition duration-300">
        <div class="flex items-center justify-between text-xs text-slate-400 mb-2">
          <span class="font-medium uppercase tracking-wider">PM Compliance</span>
          <span
            class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">11
            Remaining</span>
        </div>
        <div class="flex items-baseline gap-2">
          <span class="text-3xl font-extrabold text-white font-mono">94.5%</span>
          <span class="text-xs text-slate-400">189 / 200 Orders</span>
        </div>
        <div class="w-full bg-slate-800 h-1.5 rounded-full mt-3 overflow-hidden">
          <div class="bg-amber-500 h-full rounded-full" style="width: 94.5%"></div>
        </div>
      </div>

      <!-- KPI 4: Active Breakdown Alarm -->
      <div
        class="p-4 rounded-2xl bg-slate-900 border border-slate-800 relative overflow-hidden group hover:border-rose-500/50 transition duration-300">
        <div class="flex items-center justify-between text-xs text-slate-400 mb-2">
          <span class="font-medium uppercase tracking-wider">Ongoing Downtime</span>
          <span class="flex h-2 w-2 relative">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
          </span>
        </div>
        <div class="flex items-baseline gap-2">
          <span class="text-3xl font-extrabold text-rose-400 font-mono">1 Mesin</span>
          <span class="text-xs text-rose-400/80 font-medium">Conrod MC-04</span>
        </div>
        <div class="w-full bg-slate-800 h-1.5 rounded-full mt-3 overflow-hidden">
          <div class="bg-rose-500 h-full rounded-full" style="width: 35%"></div>
        </div>
      </div>

    </div>

    <!-- 3. BARIS 1: INDUSTRIAL CLOCK, PM MONITORING, BREAKDOWN TREND -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

      <!-- 1. INDUSTRIAL CLOCK & SHIFT (3 Cols) -->
      <div
        class="lg:col-span-3 rounded-2xl bg-slate-900 border border-slate-800 p-5 flex flex-col justify-between items-center text-center shadow-lg relative overflow-hidden">
        <!-- Glow background -->
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-cyan-500 via-indigo-500 to-rose-500"></div>

        <div>
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ date('l') }}</span>
          <h3 class="text-sm font-bold text-white tracking-wide mt-0.5">{{ date('d F Y') }}</h3>
        </div>

        <!-- Canvas Jam Analog -->
        <div class="my-3 p-2 rounded-full bg-slate-950/80 border border-slate-800 shadow-inner">
          <canvas id="analogClock" width="130" height="130" class="block"></canvas>
        </div>

        <!-- Jam Digital -->
        <div class="w-full space-y-2">
          <div
            class="text-2xl font-black text-amber-400 font-mono tracking-widest bg-slate-950/90 py-1.5 px-4 rounded-xl border border-slate-800"
            id="digitalClock">
            00:00:00
          </div>
          <div class="flex items-center justify-center gap-2 text-xs">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span class="text-slate-300 font-semibold">Shift 1 &bull; Normal Operation</span>
          </div>
        </div>
      </div>

      <!-- 2. WIDGET PM MONITORING (5 Cols) -->
      <div
        class="lg:col-span-5 rounded-2xl bg-slate-900 border border-slate-800 p-5 flex flex-col justify-between shadow-lg">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800/80">
          <div>
            <h3 class="text-sm font-bold text-white">Preventive Maintenance (PM) Monitoring</h3>
            <p class="text-xs text-slate-400">Monthly Achievement vs Plan</p>
          </div>
          <span
            class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">Monthly</span>
        </div>

        <!-- Grafik Batang PM -->
        <div class="my-3" style="height: 160px; position: relative;">
          <canvas x-data="chartjs"
            data-config='{
          "type": "bar",
          "data": {
            "labels": ["Apr", "May", "Jun", "Jul", "Aug", "Sep"],
            "datasets": [{
              "label": "Achieved (%)",
              "data": [95, 98, 100, 85, 90, 94.5],
              "backgroundColor": "#6366f1",
              "hoverBackgroundColor": "#818cf8",
              "borderRadius": 6
            }]
          },
          "options": {
            "responsive": true,
            "maintainAspectRatio": false,
            "plugins": {
              "legend": { "display": false }
            },
            "scales": {
              "y": {
                "min": 70,
                "max": 105,
                "grid": { "color": "rgba(51, 65, 85, 0.3)" }
              },
              "x": {
                "grid": { "display": false }
              }
            }
          }
        }'></canvas>
        </div>

        <!-- Kategori PM (SC, PS, EG) -->
        <div class="grid grid-cols-3 gap-2 pt-3 border-t border-slate-800/80 text-center">
          <div class="p-2 rounded-xl bg-slate-950/60 border border-slate-800/60">
            <div class="text-base font-bold text-amber-400 font-mono">92%</div>
            <div class="text-[11px] font-medium text-slate-400 uppercase">SC (Check)</div>
          </div>
          <div class="p-2 rounded-xl bg-slate-950/60 border border-slate-800/60">
            <div class="text-base font-bold text-cyan-400 font-mono">88%</div>
            <div class="text-[11px] font-medium text-slate-400 uppercase">PS (Service)</div>
          </div>
          <div class="p-2 rounded-xl bg-slate-950/60 border border-slate-800/60">
            <div class="text-base font-bold text-indigo-400 font-mono">100%</div>
            <div class="text-[11px] font-medium text-slate-400 uppercase">EG (Engine)</div>
          </div>
        </div>
      </div>

      <!-- 3. BREAKDOWN TREND & COST (4 Cols) -->
      <div
        class="lg:col-span-4 rounded-2xl bg-slate-900 border border-slate-800 p-5 flex flex-col justify-between shadow-lg">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800/80">
          <div>
            <h3 class="text-sm font-bold text-white">Breakdown Loss & Trend</h3>
            <p class="text-xs text-slate-400">Total Downtime (Hours) & Repair Cost</p>
          </div>
          <span
            class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-rose-500/10 text-rose-400 border border-rose-500/20">Trend</span>
        </div>

        <div class="my-3" style="height: 190px; position: relative;">
          <canvas x-data="chartjs"
            data-config='{
          "type": "line",
          "data": {
            "labels": ["Apr", "May", "Jun", "Jul", "Aug", "Sep"],
            "datasets": [
              {
                "label": "Downtime (Hours)",
                "data": [18, 14, 11, 24, 16, 9],
                "borderColor": "#f43f5e",
                "backgroundColor": "rgba(244, 63, 94, 0.15)",
                "fill": true,
                "tension": 0.35,
                "pointRadius": 4,
                "pointBackgroundColor": "#f43f5e"
              }
            ]
          },
          "options": {
            "responsive": true,
            "maintainAspectRatio": false,
            "plugins": {
              "legend": { "display": false }
            },
            "scales": {
              "y": {
                "grid": { "color": "rgba(51, 65, 85, 0.3)" }
              },
              "x": {
                "grid": { "display": false }
              }
            }
          }
        }'></canvas>
        </div>

        <div class="flex items-center justify-between text-xs text-slate-400 pt-2 border-t border-slate-800/80">
          <span>Target: &lt; 15 Jam/Bulan</span>
          <span class="text-emerald-400 font-semibold">&darr; Turun 43% vs Juli</span>
        </div>
      </div>

    </div>

    <!-- 4. MACHINE STATUS MATRIX (REAL-TIME LINE CONDITION) -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 p-5 shadow-lg">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h3 class="text-sm font-bold text-white">Machine Line Health Matrix</h3>
          <p class="text-xs text-slate-400">Live operation status by production area</p>
        </div>
        <div class="flex items-center gap-3 text-xs">
          <span class="flex items-center gap-1.5 text-slate-300">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Normal (6)
          </span>
          <span class="flex items-center gap-1.5 text-slate-300">
            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Maintenance (1)
          </span>
          <span class="flex items-center gap-1.5 text-slate-300">
            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Stop (0)
          </span>
        </div>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
        <!-- Conrod -->
        <div class="p-3 rounded-xl bg-slate-950 border border-amber-500/40 relative">
          <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
          <div class="text-xs font-bold text-white">Conrod</div>
          <div class="text-[11px] text-amber-400 font-medium mt-1">Check Required</div>
          <div class="text-[10px] text-slate-500 mt-2">OEE: 84.1%</div>
        </div>
        <!-- Cam Shaft -->
        <div class="p-3 rounded-xl bg-slate-950 border border-emerald-500/30 relative">
          <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-emerald-400"></span>
          <div class="text-xs font-bold text-white">Cam Shaft</div>
          <div class="text-[11px] text-emerald-400 font-medium mt-1">Optimal</div>
          <div class="text-[10px] text-slate-500 mt-2">OEE: 91.5%</div>
        </div>
        <!-- Crank Shaft -->
        <div class="p-3 rounded-xl bg-slate-950 border border-emerald-500/30 relative">
          <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-emerald-400"></span>
          <div class="text-xs font-bold text-white">Crank Shaft</div>
          <div class="text-[11px] text-emerald-400 font-medium mt-1">Optimal</div>
          <div class="text-[10px] text-slate-500 mt-2">OEE: 89.2%</div>
        </div>
        <!-- Cyl Head -->
        <div class="p-3 rounded-xl bg-slate-950 border border-emerald-500/30 relative">
          <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-emerald-400"></span>
          <div class="text-xs font-bold text-white">Cyl Head</div>
          <div class="text-[11px] text-emerald-400 font-medium mt-1">Optimal</div>
          <div class="text-[10px] text-slate-500 mt-2">OEE: 88.0%</div>
        </div>
        <!-- Cyl Block -->
        <div class="p-3 rounded-xl bg-slate-950 border border-emerald-500/30 relative">
          <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-emerald-400"></span>
          <div class="text-xs font-bold text-white">Cyl Block</div>
          <div class="text-[11px] text-emerald-400 font-medium mt-1">Optimal</div>
          <div class="text-[10px] text-slate-500 mt-2">OEE: 93.4%</div>
        </div>
        <!-- Axle 13 -->
        <div class="p-3 rounded-xl bg-slate-950 border border-emerald-500/30 relative">
          <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-emerald-400"></span>
          <div class="text-xs font-bold text-white">Axle 13</div>
          <div class="text-[11px] text-emerald-400 font-medium mt-1">Optimal</div>
          <div class="text-[10px] text-slate-500 mt-2">OEE: 90.1%</div>
        </div>
        <!-- Transmisi -->
        <div class="p-3 rounded-xl bg-slate-950 border border-emerald-500/30 relative">
          <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-emerald-400"></span>
          <div class="text-xs font-bold text-white">Transmisi</div>
          <div class="text-[11px] text-emerald-400 font-medium mt-1">Optimal</div>
          <div class="text-[10px] text-slate-500 mt-2">OEE: 87.8%</div>
        </div>
      </div>
    </div>

    <!-- 4. INVENTORY MACHINE (STACKED BAR CHART) -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 p-5 shadow-lg">
      <div class="flex items-center justify-between pb-3 border-b border-slate-800/80">
        <div>
          <h3 class="text-sm font-bold text-white">Inventory Machine Distribution</h3>
          <p class="text-xs text-slate-400">Main Machine vs Auxiliary Equipment per Area</p>
        </div>
        <div class="flex items-center gap-3 text-xs">
          <span class="flex items-center gap-1.5 text-indigo-400 font-medium">
            <span class="w-3 h-3 rounded bg-indigo-500"></span> Main Machine
          </span>
          <span class="flex items-center gap-1.5 text-amber-400 font-medium">
            <span class="w-3 h-3 rounded bg-amber-500"></span> Equipment
          </span>
        </div>
      </div>

      <div class="my-4" style="height: 250px; position: relative;">
        <canvas x-data="chartjs"
          data-config='{
        "type": "bar",
        "data": {
          "labels": ["Conrod", "Cam Shaft", "Crank Shaft", "Cyl Head", "Cyl Block", "Axle 13", "Transmisi"],
          "datasets": [
            {
              "label": "Main Machine",
              "data": [24, 23, 21, 43, 60, 18, 40],
              "backgroundColor": "#6366f1",
              "borderRadius": 4
            },
            {
              "label": "Equipment",
              "data": [5, 8, 12, 35, 58, 10, 15],
              "backgroundColor": "#f59e0b",
              "borderRadius": 4
            }
          ]
        },
        "options": {
          "responsive": true,
          "maintainAspectRatio": false,
          "plugins": {
            "legend": { "display": false }
          },
          "scales": {
            "x": {
              "stacked": true,
              "grid": { "display": false }
            },
            "y": {
              "stacked": true,
              "grid": { "color": "rgba(51, 65, 85, 0.3)" }
            }
          }
        }
      }'></canvas>
      </div>

      <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
        <span>Total Registered Assets: <strong class="text-white">357 Units</strong></span>
        <a href="{{ route('machines.index') }}"
          class="text-indigo-400 hover:text-indigo-300 font-semibold flex items-center gap-1">
          Lihat Detail Data &rarr;
        </a>
      </div>
    </div>

    <!-- 5. BREAKDOWN & LOSS KPI PERFORMANCE (4 REAL CHARTS: MACHINE BREAKDOWN, LINE STOP, MTTR, MTBF) -->
    <div class="space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl">
        <div>
          <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-rose-500 animate-pulse"></span>
            <h2 class="text-base font-bold text-white tracking-wide">Breakdown &amp; Loss KPI Analytics</h2>
            <span class="px-2.5 py-0.5 rounded text-xs font-mono bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
              FY {{ $selectedYear }}/{{ $selectedYear + 1 }} &bull; 12 Bulan Fiskal
            </span>
          </div>
          <p class="text-xs text-slate-400 mt-1">
            Data aktual Machine Breakdown (%), Line Stop, MTTR, dan MTBF (Status: Result) terintegrasi dengan target operasional HINO
          </p>
        </div>
        <div class="flex items-center gap-2 text-xs flex-wrap">
          <a href="{{ route('machine-breakdown.index') }}" class="px-2.5 py-1.5 rounded-lg bg-slate-950 hover:bg-slate-800 text-blue-400 border border-slate-800 font-medium transition">
            Breakdown (%)
          </a>
          <a href="{{ route('machine-breakdown.line-stop') }}" class="px-2.5 py-1.5 rounded-lg bg-slate-950 hover:bg-slate-800 text-cyan-400 border border-slate-800 font-medium transition">
            Line Stop
          </a>
          <a href="{{ route('machine-breakdown.mttr') }}" class="px-2.5 py-1.5 rounded-lg bg-slate-950 hover:bg-slate-800 text-indigo-400 border border-slate-800 font-medium transition">
            MTTR
          </a>
          <a href="{{ route('machine-breakdown.mbtf') }}" class="px-2.5 py-1.5 rounded-lg bg-slate-950 hover:bg-slate-800 text-emerald-400 border border-slate-800 font-medium transition">
            MTBF
          </a>
        </div>
      </div>

      <!-- 4 Charts Grid (2x2) -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Chart 1: Machine Breakdown (%) -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl flex flex-col justify-between hover:border-blue-500/40 transition">
          <div>
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                <div>
                  <h3 class="text-sm font-bold text-white">Machine Breakdown (%)</h3>
                  <p class="text-xs text-slate-400">Rata-rata 19 Line Mesin &bull; Target: 1,5%</p>
                </div>
              </div>
              <a href="{{ route('machine-breakdown.index') }}" class="text-xs font-semibold text-blue-400 hover:text-blue-300 flex items-center gap-1">
                Detail &amp; Pareto &rarr;
              </a>
            </div>

            <!-- Stat Strip -->
            <div class="grid grid-cols-3 gap-2 my-3 p-2.5 rounded-xl bg-slate-950/70 border border-slate-800 text-center text-xs">
              <div>
                <span class="text-[10px] text-slate-400 uppercase">Bulan Terkini</span>
                <div class="font-bold text-white font-mono mt-0.5">{{ $latestSummary['month'] }}</div>
              </div>
              <div>
                <span class="text-[10px] text-slate-400 uppercase">Nilai Aktual</span>
                <div class="font-bold font-mono mt-0.5 {{ $latestSummary['mb']['achieved'] ? 'text-emerald-400' : 'text-rose-400' }}">
                  {{ $latestSummary['mb']['val'] !== null ? number_format($latestSummary['mb']['val'], 2, ',', '.') . '%' : '-' }}
                </div>
              </div>
              <div>
                <span class="text-[10px] text-slate-400 uppercase">Status Target</span>
                <div class="font-bold text-[11px] mt-0.5 {{ $latestSummary['mb']['achieved'] ? 'text-emerald-400' : 'text-rose-400' }}">
                  {{ $latestSummary['mb']['achieved'] ? '✅ Memenuhi' : '⚠️ Melebihi' }}
                </div>
              </div>
            </div>

            <!-- Canvas -->
            <div class="relative w-full" style="height: 220px;">
              <canvas id="dashMbChart"></canvas>
            </div>
          </div>
        </div>

        <!-- Chart 2: Line Stop Event -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl flex flex-col justify-between hover:border-cyan-500/40 transition">
          <div>
            <div class="flex items-center justify-between pb-3 border-b border-slate-800 gap-2">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span>
                <div>
                  <h3 class="text-sm font-bold text-white">Line Stop Event</h3>
                  <p class="text-xs text-slate-400">Durasi (Sumbu Kiri) vs Frekuensi Kejadian (Sumbu Kanan)</p>
                </div>
              </div>
              <a href="{{ route('machine-breakdown.line-stop') }}" class="text-xs font-semibold text-cyan-400 hover:text-cyan-300 flex items-center gap-1">
                Detail &amp; Pareto &rarr;
              </a>
            </div>

            <!-- Stat Strip (4 Metrik Responsif) -->
            <div class="grid grid-cols-4 gap-2 my-3 p-2.5 rounded-xl bg-slate-950/70 border border-slate-800 text-center text-xs">
              <div>
                <span class="text-[10px] text-slate-400 uppercase">Bulan Terkini</span>
                <div class="font-bold text-white font-mono mt-0.5">{{ $latestSummary['month'] }}</div>
              </div>
              <div>
                <span class="text-[10px] text-slate-400 uppercase">Total Durasi</span>
                <div class="font-bold font-mono mt-0.5 {{ $latestSummary['line_stop']['achieved'] ? 'text-emerald-400' : 'text-rose-400' }}">
                  {{ $latestSummary['line_stop']['val'] !== null ? number_format($latestSummary['line_stop']['val'], 0, ',', '.') : '-' }}
                </div>
              </div>
              <div>
                <span class="text-[10px] text-slate-400 uppercase">Frekuensi</span>
                <div class="font-bold font-mono mt-0.5 text-amber-400">
                  {{ $latestSummary['line_stop']['freq'] !== null ? number_format($latestSummary['line_stop']['freq'], 0, ',', '.') . 'x' : '-' }}
                </div>
              </div>
              <div>
                <span class="text-[10px] text-slate-400 uppercase">Status Target</span>
                <div class="font-bold text-[11px] mt-0.5 {{ $latestSummary['line_stop']['achieved'] ? 'text-emerald-400' : 'text-rose-400' }}">
                  {{ $latestSummary['line_stop']['achieved'] ? '✅ Memenuhi' : '⚠️ Melebihi' }}
                </div>
              </div>
            </div>

            <!-- Legend Indicator -->
            <div class="flex items-center justify-end flex-wrap gap-3 text-[11px] mb-2 text-slate-300">
              <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-cyan-500 inline-block"></span>
                <span>Durasi (Kiri)</span>
              </div>
              <div class="flex items-center gap-1.5">
                <span class="w-3.5 h-0.5 border-t-2 border-dashed border-rose-500 inline-block"></span>
                <span class="text-rose-400">Target 620</span>
              </div>
              <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 inline-block border border-white"></span>
                <span class="text-amber-400">Freq (Kanan)</span>
              </div>
            </div>

            <!-- Canvas -->
            <div class="relative w-full" style="height: 220px;">
              <canvas id="dashLsChart"></canvas>
            </div>
          </div>
        </div>

        <!-- Chart 3: MTTR (Mean Time to Repair) -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl flex flex-col justify-between hover:border-indigo-500/40 transition">
          <div>
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-400"></span>
                <div>
                  <h3 class="text-sm font-bold text-white">MTTR (Mean Time To Repair)</h3>
                  <p class="text-xs text-slate-400">Grafik Garis &bull; Rata-rata 19 Line &bull; Target: 12 Menit</p>
                </div>
              </div>
              <a href="{{ route('machine-breakdown.mttr') }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300 flex items-center gap-1">
                Detail &amp; Sumbu X &rarr;
              </a>
            </div>

            <!-- Stat Strip -->
            <div class="grid grid-cols-3 gap-2 my-3 p-2.5 rounded-xl bg-slate-950/70 border border-slate-800 text-center text-xs">
              <div>
                <span class="text-[10px] text-slate-400 uppercase">Bulan Terkini</span>
                <div class="font-bold text-white font-mono mt-0.5">{{ $latestSummary['month'] }}</div>
              </div>
              <div>
                <span class="text-[10px] text-slate-400 uppercase">Nilai MTTR</span>
                <div class="font-bold font-mono mt-0.5 {{ $latestSummary['mttr']['achieved'] ? 'text-emerald-400' : 'text-rose-400' }}">
                  {{ $latestSummary['mttr']['val'] !== null ? number_format($latestSummary['mttr']['val'], 2, ',', '.') . ' m' : '-' }}
                </div>
              </div>
              <div>
                <span class="text-[10px] text-slate-400 uppercase">Status Target</span>
                <div class="font-bold text-[11px] mt-0.5 {{ $latestSummary['mttr']['achieved'] ? 'text-emerald-400' : 'text-rose-400' }}">
                  {{ $latestSummary['mttr']['achieved'] ? '✅ Memenuhi' : '⚠️ Melebihi' }}
                </div>
              </div>
            </div>

            <!-- Canvas -->
            <div class="relative w-full" style="height: 220px;">
              <canvas id="dashMttrChart"></canvas>
            </div>
          </div>
        </div>

        <!-- Chart 4: MTBF (Mean Time Between Failures) -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl flex flex-col justify-between hover:border-emerald-500/40 transition">
          <div>
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                <div>
                  <h3 class="text-sm font-bold text-white">MTBF (Mean Time Between Failures)</h3>
                  <p class="text-xs text-slate-400">Keandalan Operasi Mesin &bull; Target: 4.943 Menit</p>
                </div>
              </div>
              <a href="{{ route('machine-breakdown.mbtf') }}" class="text-xs font-semibold text-emerald-400 hover:text-emerald-300 flex items-center gap-1">
                Detail &amp; Sumbu X &rarr;
              </a>
            </div>

            <!-- Stat Strip -->
            <div class="grid grid-cols-3 gap-2 my-3 p-2.5 rounded-xl bg-slate-950/70 border border-slate-800 text-center text-xs">
              <div>
                <span class="text-[10px] text-slate-400 uppercase">Bulan Terkini</span>
                <div class="font-bold text-white font-mono mt-0.5">{{ $latestSummary['month'] }}</div>
              </div>
              <div>
                <span class="text-[10px] text-slate-400 uppercase">Nilai MTBF</span>
                <div class="font-bold font-mono mt-0.5 {{ $latestSummary['mtbf']['achieved'] ? 'text-emerald-400' : 'text-amber-400' }}">
                  {{ $latestSummary['mtbf']['val'] !== null ? number_format($latestSummary['mtbf']['val'], 0, ',', '.') . ' m' : '-' }}
                </div>
              </div>
              <div>
                <span class="text-[10px] text-slate-400 uppercase">Status Target</span>
                <div class="font-bold text-[11px] mt-0.5 {{ $latestSummary['mtbf']['achieved'] ? 'text-emerald-400' : 'text-amber-400' }}">
                  {{ $latestSummary['mtbf']['achieved'] ? '✅ Memenuhi' : '⚠️ Di Bawah' }}
                </div>
              </div>
            </div>

            <!-- Canvas -->
            <div class="relative w-full" style="height: 220px;">
              <canvas id="dashMtbfChart"></canvas>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- 6. UPCOMING PREVENTIVE MAINTENANCE WORK ORDERS (TABLE) -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 p-5 shadow-lg">
      <div class="flex items-center justify-between pb-4 border-b border-slate-800">
        <div>
          <h3 class="text-sm font-bold text-white">Upcoming PM Work Orders</h3>
          <p class="text-xs text-slate-400">Jadwal pemeliharaan preventif mesin terdekat minggu ini</p>
        </div>
        <a href="{{ route('machines.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold">
          Kelola Jadwal &rarr;
        </a>
      </div>

      <div class="overflow-x-auto mt-4">
        <table class="w-full text-left text-xs text-slate-300">
          <thead class="bg-slate-950/80 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
            <tr>
              <th class="py-3 px-4">Line & Mesin</th>
              <th class="py-3 px-4">Item Pekerjaan</th>
              <th class="py-3 px-4">Kategori PM</th>
              <th class="py-3 px-4">Jadwal Eksekusi</th>
              <th class="py-3 px-4">Teknisi PIC</th>
              <th class="py-3 px-4">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/60">
            <tr class="hover:bg-slate-800/40 transition">
              <td class="py-3 px-4 font-bold text-white">Conrod &bull; MC-04</td>
              <td class="py-3 px-4">Hydraulic Oil Filter & Pressure Sensor Calibration</td>
              <td class="py-3 px-4"><span
                  class="px-2 py-0.5 rounded-full text-[10px] bg-amber-500/10 text-amber-400 font-semibold border border-amber-500/20">PS
                  - Periodic</span></td>
              <td class="py-3 px-4 font-mono text-slate-300">12 Sep 2026, 08:00</td>
              <td class="py-3 px-4">Agus S.</td>
              <td class="py-3 px-4"><span
                  class="px-2 py-0.5 rounded-full text-[10px] bg-rose-500/10 text-rose-400 font-semibold border border-rose-500/20">Urgent
                  Today</span></td>
            </tr>
            <tr class="hover:bg-slate-800/40 transition">
              <td class="py-3 px-4 font-bold text-white">Cam Shaft &bull; Grinder #2</td>
              <td class="py-3 px-4">Spindle Vibration Analysis & Belt Tensioning</td>
              <td class="py-3 px-4"><span
                  class="px-2 py-0.5 rounded-full text-[10px] bg-cyan-500/10 text-cyan-400 font-semibold border border-cyan-500/20">SC
                  - Check</span></td>
              <td class="py-3 px-4 font-mono text-slate-300">13 Sep 2026, 10:30</td>
              <td class="py-3 px-4">Budi Prasetyo</td>
              <td class="py-3 px-4"><span
                  class="px-2 py-0.5 rounded-full text-[10px] bg-indigo-500/10 text-indigo-400 font-semibold border border-indigo-500/20">Scheduled</span>
              </td>
            </tr>
            <tr class="hover:bg-slate-800/40 transition">
              <td class="py-3 px-4 font-bold text-white">Cylinder Head &bull; CNC #07</td>
              <td class="py-3 px-4">Coolant Circulation Flushing & Tool Holder Greasing</td>
              <td class="py-3 px-4"><span
                  class="px-2 py-0.5 rounded-full text-[10px] bg-emerald-500/10 text-emerald-400 font-semibold border border-emerald-500/20">EG
                  - Engine</span></td>
              <td class="py-3 px-4 font-mono text-slate-300">14 Sep 2026, 14:00</td>
              <td class="py-3 px-4">Dimas R.</td>
              <td class="py-3 px-4"><span
                  class="px-2 py-0.5 rounded-full text-[10px] bg-emerald-500/10 text-emerald-400 font-semibold border border-emerald-500/20">Ready</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
@endsection

@push('scripts')
  <script>
    function dashboardAnalytics() {
      return {
        timeRange: 'month'
      };
    }

    // Live Analog & Digital Industrial Clock Script
    document.addEventListener('DOMContentLoaded', function() {
      const canvas = document.getElementById('analogClock');
      const digitalClock = document.getElementById('digitalClock');
      if (!canvas) return;

      const ctx = canvas.getContext('2d');
      const radius = canvas.height / 2;
      const clockRadius = radius * 0.90;

      function renderClock() {
        ctx.save();
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.translate(radius, radius);

        // Lingkaran luar dial
        ctx.beginPath();
        ctx.arc(0, 0, clockRadius, 0, 2 * Math.PI);
        ctx.fillStyle = '#090d16';
        ctx.fill();
        ctx.lineWidth = clockRadius * 0.05;
        ctx.strokeStyle = '#4f46e5';
        ctx.stroke();

        // Pin Pusat
        ctx.beginPath();
        ctx.arc(0, 0, clockRadius * 0.08, 0, 2 * Math.PI);
        ctx.fillStyle = '#f59e0b';
        ctx.fill();

        // Angka Jam
        ctx.font = 'bold ' + (clockRadius * 0.16) + 'px "JetBrains Mono", sans-serif';
        ctx.textBaseline = 'middle';
        ctx.textAlign = 'center';
        ctx.fillStyle = '#94a3b8';
        for (let num = 1; num <= 12; num++) {
          const ang = (num * Math.PI) / 6;
          ctx.rotate(ang);
          ctx.translate(0, -clockRadius * 0.76);
          ctx.rotate(-ang);
          ctx.fillText(num.toString(), 0, 0);
          ctx.rotate(ang);
          ctx.translate(0, clockRadius * 0.76);
          ctx.rotate(-ang);
        }

        // Time
        const now = new Date();
        let hour = now.getHours();
        let minute = now.getMinutes();
        let second = now.getSeconds();

        if (digitalClock) {
          digitalClock.textContent = [hour, minute, second]
            .map(v => String(v).padStart(2, '0'))
            .join(':');
        }

        // Jarum Jam
        hour = (hour % 12) + minute / 60 + second / 3600;
        drawHand(ctx, (hour * Math.PI) / 6, clockRadius * 0.50, clockRadius * 0.06, '#ffffff');

        // Jarum Menit
        minute = minute + second / 60;
        drawHand(ctx, (minute * Math.PI) / 30, clockRadius * 0.72, clockRadius * 0.04, '#06b6d4');

        // Jarum Detik
        drawHand(ctx, (second * Math.PI) / 30, clockRadius * 0.85, clockRadius * 0.02, '#f43f5e');

        ctx.restore();
      }

      function drawHand(ctx, pos, length, width, color) {
        ctx.beginPath();
        ctx.lineWidth = width;
        ctx.lineCap = 'round';
        ctx.strokeStyle = color;
        ctx.moveTo(0, 0);
        ctx.rotate(pos);
        ctx.lineTo(0, -length);
        ctx.stroke();
        ctx.rotate(-pos);
      }

      renderClock();
      setInterval(renderClock, 1000);

      // =========================================================================
      // 4 BREAKDOWN & LOSS KPI CHARTS (Chart.js)
      // =========================================================================
      const fiscalLabels = @json($fiscalLabels);
      const mbData = @json($mbSeries);
      const mbTarget = {{ $mbTarget }};
      const lsData = @json($lineStopSeries);
      const lsTarget = {{ $lineStopTarget }};
      const mttrData = @json($mttrSeries);
      const mttrTarget = {{ $mttrTarget }};
      const mtbfData = @json($mtbfSeries);
      const mtbfTarget = {{ $mtbfTarget }};

      const commonGridOptions = {
        grid: { color: 'rgba(51, 65, 85, 0.35)', drawBorder: false },
        ticks: { color: '#94a3b8', font: { family: 'JetBrains Mono', size: 10 } }
      };

      // 1. Machine Breakdown Chart
      const mbCanvas = document.getElementById('dashMbChart');
      if (mbCanvas) {
        new Chart(mbCanvas.getContext('2d'), {
          type: 'bar',
          data: {
            labels: fiscalLabels,
            datasets: [
              {
                type: 'line',
                label: 'Target (1,5%)',
                data: fiscalLabels.map(() => mbTarget),
                borderColor: '#ef4444',
                borderDash: [5, 4],
                borderWidth: 2,
                pointRadius: 0,
                fill: false,
                order: 1
              },
              {
                type: 'bar',
                label: 'Breakdown (%)',
                data: mbData,
                backgroundColor: mbData.map(v => v !== null && v > mbTarget ? 'rgba(244, 63, 94, 0.85)' : 'rgba(14, 165, 233, 0.85)'),
                hoverBackgroundColor: mbData.map(v => v !== null && v > mbTarget ? 'rgba(225, 29, 72, 1)' : 'rgba(2, 132, 199, 1)'),
                borderColor: mbData.map(v => v !== null && v > mbTarget ? '#f43f5e' : '#0ea5e9'),
                borderWidth: 1.5,
                borderRadius: 6,
                order: 2
              }
            ]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { display: false },
              tooltip: {
                callbacks: {
                  label: ctx => {
                    const val = ctx.parsed.y;
                    if (val === null) return null;
                    const st = val <= mbTarget ? '✅ Memenuhi Target' : '⚠️ Melebihi Target';
                    return ` ${ctx.dataset.label}: ${val}% (${st})`;
                  }
                }
              }
            },
            scales: {
              y: { ...commonGridOptions, beginAtZero: true, ticks: { ...commonGridOptions.ticks, callback: v => v + '%' } },
              x: { ...commonGridOptions, grid: { display: false } }
            }
          }
        });
      }

      // 2. Line Stop Chart (Dual Axis: Primary Y Durasi vs Secondary Y1 Frekuensi)
      const lsCanvas = document.getElementById('dashLsChart');
      const lsFreqData = @json($lineStopFreqSeries);
      if (lsCanvas) {
        new Chart(lsCanvas.getContext('2d'), {
          type: 'bar',
          data: {
            labels: fiscalLabels,
            datasets: [
              // Target 620 (Primary Axis)
              {
                type: 'line',
                label: 'Target (620)',
                data: fiscalLabels.map(() => lsTarget),
                yAxisID: 'y',
                borderColor: '#ef4444',
                borderDash: [5, 4],
                borderWidth: 2,
                pointRadius: 0,
                fill: false,
                order: 2
              },
              // Batang Durasi Linestop (Primary Axis)
              {
                type: 'bar',
                label: 'Durasi Stop',
                data: lsData,
                yAxisID: 'y',
                backgroundColor: lsData.map(v => v !== null && v > lsTarget ? 'rgba(244, 63, 94, 0.85)' : 'rgba(6, 182, 212, 0.85)'),
                hoverBackgroundColor: lsData.map(v => v !== null && v > lsTarget ? 'rgba(225, 29, 72, 1)' : 'rgba(8, 145, 178, 1)'),
                borderColor: lsData.map(v => v !== null && v > lsTarget ? '#f43f5e' : '#06b6d4'),
                borderWidth: 1.5,
                borderRadius: 6,
                order: 3
              },
              // Line Frekuensi Kejadian (Secondary Axis)
              {
                type: 'line',
                label: 'Frekuensi Kejadian',
                data: lsFreqData,
                yAxisID: 'y1',
                borderColor: '#f59e0b',
                backgroundColor: '#f59e0b',
                borderWidth: 2,
                pointRadius: 3.5,
                pointHoverRadius: 5.5,
                pointBackgroundColor: '#f59e0b',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 1.5,
                fill: false,
                tension: 0.25,
                order: 1
              }
            ]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
              mode: 'index',
              intersect: false
            },
            plugins: {
              legend: { display: false },
              tooltip: {
                callbacks: {
                  label: ctx => {
                    const val = ctx.parsed.y;
                    if (val === null || val === undefined) return null;
                    if (ctx.dataset.yAxisID === 'y1') {
                      return ` ${ctx.dataset.label}: ${val} kali`;
                    }
                    if (ctx.dataset.type === 'line' && ctx.dataset.label.includes('Target')) {
                      return ` ${ctx.dataset.label}: ${val}`;
                    }
                    const st = val <= lsTarget ? '✅ Memenuhi Target' : '⚠️ Melebihi Target';
                    return ` ${ctx.dataset.label}: ${val.toLocaleString('id-ID')} menit (${st})`;
                  }
                }
              }
            },
            scales: {
              // Primary Axis (Kiri): Durasi
              y: {
                ...commonGridOptions,
                type: 'linear',
                display: true,
                position: 'left',
                beginAtZero: true,
                ticks: {
                  ...commonGridOptions.ticks,
                  callback: v => v.toLocaleString('id-ID')
                }
              },
              // Secondary Axis (Kanan): Frekuensi
              y1: {
                type: 'linear',
                display: true,
                position: 'right',
                beginAtZero: true,
                grid: { drawOnChartArea: false },
                ticks: {
                  color: '#f59e0b',
                  font: { family: 'JetBrains Mono', size: 10 },
                  callback: v => v + 'x'
                }
              },
              x: { ...commonGridOptions, grid: { display: false } }
            }
          }
        });
      }

      // 3. MTTR Chart (Line with Marker seperti di modul MTTR)
      const mttrCanvas = document.getElementById('dashMttrChart');
      if (mttrCanvas) {
        new Chart(mttrCanvas.getContext('2d'), {
          type: 'line',
          data: {
            labels: fiscalLabels,
            datasets: [
              {
                type: 'line',
                label: 'Target (12 m)',
                data: fiscalLabels.map(() => mttrTarget),
                borderColor: '#ef4444',
                borderDash: [6, 4],
                borderWidth: 2,
                pointRadius: 0,
                fill: false,
                order: 1
              },
              {
                type: 'line',
                label: 'MTTR Actual',
                data: mttrData,
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.12)',
                borderWidth: 2.5,
                pointStyle: 'circle',
                pointRadius: mttrData.map(v => v !== null && v > mttrTarget ? 6 : 5),
                pointHoverRadius: 8,
                pointBackgroundColor: mttrData.map(v => v !== null && v > mttrTarget ? '#f43f5e' : '#6366f1'),
                pointBorderColor: mttrData.map(v => v !== null && v > mttrTarget ? '#fecdd3' : '#0f172a'),
                pointBorderWidth: 2,
                fill: false,
                tension: 0.15,
                order: 2
              }
            ]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { display: false },
              tooltip: {
                callbacks: {
                  label: ctx => {
                    const val = ctx.parsed.y;
                    if (val === null) return null;
                    const st = val <= mttrTarget ? '✅ Memenuhi Target' : '⚠️ Melebihi Target';
                    return ` ${ctx.dataset.label}: ${val} menit (${st})`;
                  }
                }
              }
            },
            scales: {
              y: { ...commonGridOptions, beginAtZero: true, ticks: { ...commonGridOptions.ticks, callback: v => v + ' m' } },
              x: { ...commonGridOptions, grid: { display: false } }
            }
          }
        });
      }

      // 4. MTBF Chart
      const mtbfCanvas = document.getElementById('dashMtbfChart');
      if (mtbfCanvas) {
        new Chart(mtbfCanvas.getContext('2d'), {
          type: 'line',
          data: {
            labels: fiscalLabels,
            datasets: [
              {
                type: 'line',
                label: 'Target (4.943 m)',
                data: fiscalLabels.map(() => mtbfTarget),
                borderColor: '#ef4444',
                borderDash: [5, 4],
                borderWidth: 2,
                pointRadius: 0,
                fill: false,
                order: 1
              },
              {
                type: 'line',
                label: 'MTBF (Menit)',
                data: mtbfData,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.12)',
                borderWidth: 2.5,
                fill: true,
                tension: 0.35,
                pointRadius: mtbfData.map(v => v !== null && v < mtbfTarget ? 6 : 5),
                pointHoverRadius: 8,
                pointBackgroundColor: mtbfData.map(v => v !== null && v < mtbfTarget ? '#f43f5e' : '#10b981'),
                pointBorderColor: mtbfData.map(v => v !== null && v < mtbfTarget ? '#fecdd3' : '#0f172a'),
                pointBorderWidth: 2,
                order: 2
              }
            ]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { display: false },
              tooltip: {
                callbacks: {
                  label: ctx => {
                    const val = ctx.parsed.y;
                    if (val === null) return null;
                    const st = val >= mtbfTarget ? '✅ Memenuhi Target' : '⚠️ Di Bawah Target';
                    return ` ${ctx.dataset.label}: ${val.toLocaleString('id-ID')} m (${st})`;
                  }
                }
              }
            },
            scales: {
              y: { ...commonGridOptions, beginAtZero: true, ticks: { ...commonGridOptions.ticks, callback: v => v.toLocaleString('id-ID') + ' m' } },
              x: { ...commonGridOptions, grid: { display: false } }
            }
          }
        });
      }
    });
  </script>
@endpush

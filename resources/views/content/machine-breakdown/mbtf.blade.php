@extends('layouts.alpineLayout')

@section('title', 'MTBF (Mean Time Between Failures)')
@section('page-title', 'MTBF Performance Trend')

@section('content')
  <div class="space-y-6" x-data="{ uploadOpen: false, showTableMatrix: false }">

    <!-- 1. HEADER & FILTER BAR -->
    <div
      class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl">
      <div>
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-blue-500 animate-pulse"></span>
          <h2 class="text-lg font-bold text-white tracking-wide">MTBF (MEAN TIME BETWEEN FAILURES)</h2>
        </div>
        <p class="text-xs text-slate-400 mt-1">
          Rata-rata durasi keandalan mesin per bulan (Semua status Result & Target) &bull; Target Tetap: <strong
            class="text-rose-400">{{ number_format($targetConstant, 0, ',', '.') }} Menit</strong>
        </p>
      </div>

      <div class="flex items-center flex-wrap gap-3 text-xs">
        <!-- Year Selector Form -->
        <form method="GET" action="{{ route('machine-breakdown.mbtf') }}" class="flex items-center gap-2">
          <label for="yearSelect" class="text-slate-400 font-medium">Tahun Fiskal:</label>
          <select name="year" id="yearSelect" onchange="this.form.submit()"
            class="bg-slate-950 border border-slate-800 text-white font-semibold rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-blue-500">
            @foreach ($availableYears as $yr)
              <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>
                FY {{ $yr }}/{{ $yr + 1 }}
              </option>
            @endforeach
          </select>
        </form>

        <!-- Toggle Upload Button -->
        @hasPermission('breakdown_import')
        <button @click="uploadOpen = !uploadOpen"
          class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-medium transition border border-slate-700">
          <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
          </svg>
          <span x-text="uploadOpen ? 'Tutup Upload' : 'Upload Excel'"></span>
        </button>
        @endhasPermission
      </div>
    </div>

    <!-- 2. FLASH NOTIFICATIONS -->
    @if (session('success'))
      <div
        class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs flex items-center justify-between">
        <div class="flex items-center gap-2">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <span>{{ session('success') }}</span>
        </div>
      </div>
    @endif

    @if ($errors->any())
      <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs">
        <div class="font-bold mb-1">Terdapat kesalahan:</div>
        <ul class="list-disc list-inside space-y-0.5">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <!-- 3. ACCORDION UPLOAD EXCEL FORM -->
    @hasPermission('breakdown_import')
    <div x-show="uploadOpen" x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
      x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
      x-transition:leave-end="opacity-0 -translate-y-2"
      class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl" style="display: none;">
      <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-800">
        <h3 class="text-sm font-bold text-white">Import File Excel Machine Breakdown</h3>
        <span class="text-xs text-slate-400">Format: .xlsx, .xls, .csv</span>
      </div>

      <form action="{{ route('machine-breakdown.import') }}" method="POST" enctype="multipart/form-data"
        class="space-y-4">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
          <div class="md:col-span-3">
            <label for="file" class="block text-xs font-semibold text-slate-300 mb-1.5">Pilih Berkas Excel:</label>
            <input type="file" id="file" name="file"
              class="block w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-500 bg-slate-950 border border-slate-800 rounded-xl cursor-pointer p-1"
              accept=".xlsx,.xls,.csv" required>
          </div>
          <div class="md:col-span-1">
            <button type="submit"
              class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl transition shadow-lg shadow-blue-600/30">
              Upload & Proses
            </button>
          </div>
        </div>
        <p class="text-[11px] text-slate-500">
          * Sistem mengimpor seluruh baris breakdown dan menghitung rata-rata durasi bulanan tanpa mengabaikan status data
          (Result maupun Target).
        </p>
      </form>
    </div>
    @endhasPermission

    <!-- 4. STAT SUMMARY CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Card 1: Rata-Rata MTBF -->
      <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 shadow-md">
        <div class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-1">Rata-Rata MTBF FY
          {{ $fiscalYearLabel }}</div>
        <div class="flex items-baseline gap-2">
          <span class="text-3xl font-extrabold text-blue-400 font-mono">{{ number_format($avgMtbf, 1, ',', '.') }}</span>
          <span class="text-xs text-slate-400">menit</span>
        </div>
        <div class="mt-2 text-[11px] text-slate-500">Dihitung dari rata-rata durasi per bulan</div>
      </div>

      <!-- Card 2: Target MTBF -->
      <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 shadow-md">
        <div class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-1">Target MTBF (Ketentuan)</div>
        <div class="flex items-baseline gap-2">
          <span
            class="text-3xl font-extrabold text-rose-400 font-mono">{{ number_format($targetConstant, 0, ',', '.') }}</span>
          <span class="text-xs text-slate-400">menit</span>
        </div>
        <div class="mt-2 text-[11px] text-slate-500">Garis benchmark target tetap</div>
      </div>

      <!-- Card 3: Status Pencapaian -->
      <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 shadow-md">
        <div class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-1">Status Keandalan</div>
        <div class="mt-1">
          @if ($avgMtbf >= $targetConstant)
            <span
              class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
              &check; Memenuhi Target
            </span>
          @else
            <span
              class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
              &excl; Di Bawah Target
            </span>
          @endif
        </div>
        <div class="mt-2 text-[11px] text-slate-500">
          Selisih: <strong
            class="{{ $avgMtbf >= $targetConstant ? 'text-emerald-400' : 'text-rose-400' }}">{{ number_format($avgMtbf - $targetConstant, 1, ',', '.') }}
            menit</strong>
        </div>
      </div>

      <!-- Card 4: Periode Siklus -->
      <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 shadow-md">
        <div class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-1">Periode Fiskal</div>
        <div class="text-lg font-bold text-white font-mono mt-1">April {{ $selectedYear }} - Maret
          {{ $selectedYear + 1 }}</div>
      </div>
    </div>

    <!-- 5. MAIN MTBF CHART: LINE WITH MARKER -->
    <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b border-slate-800 gap-2">
        <div>
          <h3 class="text-base font-bold text-white flex items-center gap-2">
            <span>MTBF & MTTR Performance Trend</span>
            <span
              class="px-2 py-0.5 rounded text-[11px] font-mono bg-blue-500/10 text-blue-400 border border-blue-500/20">
              FY {{ $fiscalYearLabel }}
            </span>
          </h3>
          <p class="text-xs text-slate-400 mt-0.5">Grafik garis dengan marker titik untuk rata-rata durasi setiap bulan vs
            target 4.943</p>
        </div>

        <!-- Legend Indicator & Click Hint -->
        <div class="flex items-center flex-wrap gap-3 text-xs">
          <div class="flex items-center gap-1.5 text-slate-200">
            <span class="w-3 h-3 rounded-full bg-blue-500 border-2 border-white inline-block"></span>
            <span class="font-semibold">MTBF Actual (Rata-rata Durasi)</span>
          </div>
          <div class="flex items-center gap-1.5 text-slate-200">
            <span class="w-4 h-0.5 border-t-2 border-dashed border-rose-500 inline-block"></span>
            <span class="font-semibold text-rose-400">Target (4.943)</span>
          </div>
          <div
            class="hidden sm:flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-500/10 text-blue-300 border border-blue-500/20 text-[11px]">
            <svg class="w-3.5 h-3.5 text-blue-400 animate-bounce" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
            </svg>
            <span>Klik titik bulan untuk melihat grafik per line</span>
          </div>
        </div>
      </div>

      <!-- Chart Container -->
      <div class="relative w-full" style="height: 380px;">
        <canvas id="mtbfMarkerChart"></canvas>
      </div>
    </div>

    <!-- 6. GRAFIK RINCIAN MTBF PER LINE / AREA MESIN (SUMBU X: LINE, SUMBU Y: MENIT) -->
    <div id="lineDetailChartSection" class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl scroll-mt-6">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between pb-4 mb-4 border-b border-slate-800 gap-3">
        <div>
          <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-blue-400"></span>
            <h3 class="text-base font-bold text-white flex items-center gap-2">
              <span>Grafik MTBF per Line / Area</span>
              <span id="selectedMonthBadge"
                class="px-2.5 py-0.5 rounded text-xs font-mono bg-blue-500/10 text-blue-400 border border-blue-500/20">
                <!-- Diisi otomatis oleh JavaScript -->
              </span>
            </h3>
          </div>
          <p class="text-xs text-slate-400 mt-1">
            Sumbu X: Line &bull; Sumbu Y: Durasi MTBF (Menit) &bull; Garis Target:
            4.943 Menit
          </p>
        </div>

        <!-- Month Filter Pills -->
        <div class="flex items-center flex-wrap gap-1 bg-slate-950 p-1.5 rounded-xl border border-slate-800"
          id="monthPillsContainer">
          @foreach ($matrixColumns as $idx => $col)
            <button type="button" onclick="selectMonth({{ $idx }})" id="monthPill-{{ $idx }}"
              class="month-pill px-2.5 py-1 rounded-lg text-xs font-semibold transition bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800">
              {{ $col['short'] }}
            </button>
          @endforeach
        </div>
      </div>

      <!-- Stat Badges Rincian Bulan Terpilih -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
        <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800">
          <div class="text-[11px] text-slate-400 uppercase tracking-wider">Rata-Rata Bulan Ini</div>
          <div class="text-lg font-bold text-blue-400 font-mono mt-0.5" id="monthAvgStat">0 m</div>
        </div>
        <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800">
          <div class="text-[11px] text-slate-400 uppercase tracking-wider">Line Paling Andal (Min)</div>
          <div class="text-sm font-bold text-emerald-400 truncate mt-0.5" id="monthPeakLine">-</div>
        </div>
      </div>

      <!-- Canvas Grafik Detail Line MTBF -->
      <div class="relative w-full" style="height: 430px;">
        <canvas id="lineMtbfDetailChart"></canvas>
      </div>

      <!-- Toggle Tabel Matriks (Opsional & Tersembunyi Default) -->
      <div class="mt-5 pt-4 border-t border-slate-800/70 flex items-center justify-between">
        <button type="button" @click="showTableMatrix = !showTableMatrix"
          class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-950 text-slate-400 hover:text-white border border-slate-800 transition">
          <svg class="w-3.5 h-3.5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 10h18M3 14h18m-9-4v8m-7 4h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <span
            x-text="showTableMatrix ? 'Sembunyikan Tabel Matriks' : 'Lihat Data Tabel Matriks (Format Excel)'"></span>
        </button>
        <span class="text-[11px] text-slate-500" x-show="!showTableMatrix">Mode grafik aktif &bull; Klik tombol di
          samping jika ingin melihat tabel data</span>
      </div>

      <div x-show="showTableMatrix" x-cloak class="mt-4 overflow-x-auto rounded-xl border border-slate-800">
        <table class="w-full text-left text-xs text-slate-300">
          <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
            <tr>
              <th class="py-3 px-3 min-w-[200px] sticky left-0 bg-slate-950 z-10">AREA / LINE</th>
              @foreach ($matrixColumns as $col)
                <th class="py-3 px-2.5 text-center min-w-[70px]">{{ $col['short'] }}</th>
              @endforeach
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/50 font-mono text-[11px]">
            @foreach ($matrixRows as $row)
              <tr class="hover:bg-slate-800/50 transition">
                <td
                  class="py-2.5 px-3 font-sans font-semibold text-slate-200 sticky left-0 bg-slate-900 z-10 border-r border-slate-800/80">
                  {{ $row['line'] }}
                </td>
                @foreach ($matrixColumns as $col)
                  @php
                    $mVal = $row['months'][$col['monthNumber']] ?? 0;
                  @endphp
                  <td class="py-2.5 px-2.5 text-center {{ $mVal > 0 ? 'text-white font-semibold' : 'text-slate-500' }}">
                    {{ $mVal > 0 ? number_format($mVal, 0, ',', '.') : '0' }}
                  </td>
                @endforeach
              </tr>
            @endforeach
          </tbody>
          <tfoot class="bg-blue-500/10 border-t-2 border-blue-500/40 font-mono text-xs font-bold bg-slate-950/80">
            <tr>
              <td
                class="py-3 px-3 font-sans uppercase tracking-wider sticky left-0 bg-slate-950 z-10 text-amber-400 border-r border-slate-800">
                AVERAGE
              </td>
              @foreach ($matrixColumns as $col)
                @php
                  $avgVal = $monthlyAverages[$col['monthNumber']] ?? null;
                @endphp
                <td
                  class="py-3 px-2.5 text-center {{ $avgVal !== null ? 'text-emerald-400 text-[12px] font-extrabold' : 'text-slate-500' }}">
                  {{ $avgVal !== null ? number_format($avgVal, 0, ',', '.') : '0' }}
                </td>
              @endforeach
            </tr>
            <tr class="bg-rose-500/10 border-t border-slate-800">
              <td
                class="py-2.5 px-3 font-sans uppercase tracking-wider sticky left-0 bg-slate-950 z-10 text-rose-400 border-r border-slate-800">
                TARGET
              </td>
              @foreach ($matrixColumns as $col)
                <td class="py-2.5 px-2.5 text-center text-rose-400">
                  {{ number_format($targetConstant, 0, ',', '.') }}
                </td>
              @endforeach
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- 7. TABEL RINCIAN BULANAN -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 shadow-xl overflow-hidden">
      <div class="p-4 border-b border-slate-800 flex items-center justify-between">
        <h3 class="text-sm font-bold text-white">Rincian Nilai MTBF Bulanan</h3>
        <span class="text-xs text-slate-400">Total 12 Bulan Fiskal</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-300">
          <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
            <tr>
              <th class="py-3 px-4">Bulan (Fiskal)</th>
              <th class="py-3 px-4 text-center">Jumlah Data Terbaca</th>
              <th class="py-3 px-4 text-right">Rata-Rata Durasi (MTBF)</th>
              <th class="py-3 px-4 text-right">Target</th>
              <th class="py-3 px-4 text-right">Selisih (+/-)</th>
              <th class="py-3 px-4 text-center">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/60 font-mono">
            @foreach ($tableData as $item)
              <tr class="hover:bg-slate-800/40 transition">
                <td class="py-3 px-4 font-sans font-semibold text-white">{{ $item['month'] }}</td>
                <td class="py-3 px-4 text-center text-slate-400">{{ $item['count'] }} data</td>
                <td
                  class="py-3 px-4 text-right font-bold {{ $item['value'] !== null ? ($item['value'] >= $item['target'] ? 'text-emerald-400' : 'text-blue-400') : 'text-slate-600' }}">
                  {{ $item['value'] !== null ? number_format($item['value'], 1, ',', '.') : '-' }}
                </td>
                <td class="py-3 px-4 text-right text-rose-400">{{ number_format($item['target'], 0, ',', '.') }}</td>
                <td class="py-3 px-4 text-right">
                  @if ($item['diff'] !== null)
                    <span class="{{ $item['diff'] >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                      {{ ($item['diff'] >= 0 ? '+' : '') . number_format($item['diff'], 1, ',', '.') }}
                    </span>
                  @else
                    <span class="text-slate-600">-</span>
                  @endif
                </td>
                <td class="py-3 px-4 text-center font-sans">
                  @if ($item['value'] === null)
                    <span class="px-2 py-0.5 rounded text-[10px] bg-slate-800 text-slate-500">Belum Ada Data</span>
                  @elseif ($item['value'] >= $item['target'])
                    <span
                      class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Achieved</span>
                  @else
                    <span
                      class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">Below
                      Target</span>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

  </div>
@endsection

@push('scripts')
  <!-- Chart.js Plugin DataLabels -->
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js">
  </script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const labels = @json(array_values($labels));
      const mtbfSeries = @json(array_values($mtbfSeries));
      const targetSeries = @json(array_values($targetSeries));
      const matrixColumns = @json(array_values($matrixColumns));
      const matrixRows = @json(array_values($matrixRows));
      const monthlyAverages = @json($monthlyAverages);
      const targetConstant = {{ (float) $targetConstant }};

      const formatNumber = (value) => {
        if (value === null || value === undefined) return '';
        return Number(value).toLocaleString('id-ID', {
          minimumFractionDigits: 0,
          maximumFractionDigits: 1
        });
      };

      // =========================================================================
      // 1. GRAFIK UTAMA: RATA-RATA MTBF BULANAN (12 BULAN FISKAL)
      // =========================================================================
      const canvas = document.getElementById('mtbfMarkerChart');
      if (!canvas) return;

      const ctx = canvas.getContext('2d');

      const topChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [
            // 1. DATASET MTBF ACTUAL
            {
              label: 'MTBF Actual',
              data: mtbfSeries,
              borderColor: '#10b981',
              backgroundColor: '#10b981',
              borderWidth: 2.5,
              pointStyle: 'circle',
              pointRadius: mtbfSeries.map(v => v !== null && v < targetConstant ? 7 : 6),
              pointHoverRadius: 9,
              pointBackgroundColor: mtbfSeries.map(v => v !== null && v < targetConstant ? '#f43f5e' : '#10b981'),
              pointBorderColor: mtbfSeries.map(v => v !== null && v < targetConstant ? '#fecdd3' : '#0f172a'),
              pointBorderWidth: 2,
              fill: false,
              tension: 0.1,
              spanGaps: false,
              order: 1,
              datalabels: {
                display: function(context) {
                  return context.dataset.data[context.dataIndex] !== null;
                },
                anchor: 'end',
                align: 'top',
                offset: 6,
                formatter: (value) => formatNumber(value),
                color: function(context) {
                  const val = context.dataset.data[context.dataIndex];
                  return (val !== null && val < targetConstant) ? '#fda4af' : '#a7f3d0';
                },
                font: {
                  family: 'Inter, sans-serif',
                  weight: '600',
                  size: 11
                }
              }
            },
            // 2. DATASET TARGET (4.943)
            {
              label: 'Target (4.943)',
              data: targetSeries,
              borderColor: '#ef4444',
              backgroundColor: '#ef4444',
              borderDash: [8, 5],
              borderWidth: 2.5,
              pointRadius: 0,
              fill: false,
              tension: 0,
              order: 2,
              datalabels: {
                display: false
              }
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
          // INTERAKSI KLIK: Ketika titik/marker bulan dipencet -> Langsung scroll & tampilkan grafik per line
          onClick: function(event, elements) {
            if (elements && elements.length > 0) {
              const clickedIndex = elements[0].index;
              selectMonth(clickedIndex);
              const targetSection = document.getElementById('lineDetailChartSection');
              if (targetSection) {
                targetSection.scrollIntoView({
                  behavior: 'smooth',
                  block: 'start'
                });
                targetSection.classList.add('ring-2', 'ring-blue-400');
                setTimeout(() => {
                  targetSection.classList.remove('ring-2', 'ring-blue-400');
                }, 1200);
              }
            }
          },
          onHover: function(event, chartElement) {
            event.native.target.style.cursor = chartElement[0] ? 'pointer' : 'default';
          },
          layout: {
            padding: {
              top: 25,
              bottom: 10,
              left: 10,
              right: 15
            }
          },
          scales: {
            y: {
              beginAtZero: false,
              grid: {
                color: 'rgba(51, 65, 85, 0.4)',
                drawBorder: false
              },
              ticks: {
                color: '#94a3b8',
                stepSize: 2000,
                font: {
                  family: 'JetBrains Mono',
                  size: 11
                },
                callback: (v) => formatNumber(v)
              },
              title: {
                display: true,
                text: 'MTBF-Minute',
                color: '#e2e8f0',
                font: {
                  weight: 'bold',
                  size: 12
                }
              }
            },
            x: {
              grid: {
                color: 'rgba(51, 65, 85, 0.15)',
                drawBorder: false
              },
              ticks: {
                color: '#cbd5e1',
                font: {
                  weight: '600',
                  size: 11
                }
              }
            }
          },
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              backgroundColor: 'rgba(15, 23, 42, 0.95)',
              titleColor: '#f8fafc',
              bodyColor: '#e2e8f0',
              borderColor: '#334155',
              borderWidth: 1,
              padding: 10,
              titleFont: {
                weight: 'bold',
                size: 12
              },
              bodyFont: {
                family: 'JetBrains Mono',
                size: 12
              },
              callbacks: {
                label: function(context) {
                  if (context.parsed.y === null) return null;
                  const status = context.parsed.y >= targetConstant ? '✅ Memenuhi Target' : '⚠️ Di Bawah Target';
                  return ' ' + context.dataset.label + ': ' + formatNumber(context.parsed.y) + ' menit (' + status + ')';
                },
                afterBody: function() {
                  return ['\n👉 Klik titik ini untuk melihat rincian grafik per line'];
                }
              }
            }
          }
        },
        plugins: [ChartDataLabels]
      });

      // =========================================================================
      // 2. GRAFIK DETAIL PER LINE / AREA UNTUK BULAN TERPILIH (MTBF)
      // Sumbu X: Line / Area Mesin | Sumbu Y: Durasi MTBF (Menit)
      // =========================================================================
      const lineCanvas = document.getElementById('lineMtbfDetailChart');
      if (!lineCanvas) return;

      const lineCtx = lineCanvas.getContext('2d');
      const lineNames = matrixRows.map(r => r.line);

      let lineDetailChart = null;

      function renderLineChart(monthIndex) {
        if (!matrixColumns[monthIndex]) return;

        const col = matrixColumns[monthIndex];
        const monthNum = col.monthNumber;

        // Ambil data setiap line untuk bulan yang dipilih
        const rawLineData = matrixRows.map(r => {
          const val = r.months && r.months[monthNum] !== undefined ? Number(r.months[monthNum]) : 0;
          return {
            line: r.line,
            val: val
          };
        });

        // MTBF: Urutkan dari TERKECIL hingga TERBESAR (Prioritas line dengan keandalan terendah)
        rawLineData.sort((a, b) => a.val - b.val);

        const lineNames = rawLineData.map(d => d.line);
        const lineValues = rawLineData.map(d => d.val);
        const targetValues = lineNames.map(() => targetConstant);

        // Styling warna: hijau jika >= 4943 (achieved), amber/rose jika < 4943 (below target)
        const bgColors = lineValues.map(v => v >= targetConstant ? 'rgba(16, 185, 129, 0.85)' :
          'rgba(245, 158, 11, 0.85)');
        const hoverBgColors = lineValues.map(v => v >= targetConstant ? 'rgba(5, 150, 105, 1)' :
          'rgba(217, 119, 6, 1)');
        const borderColors = lineValues.map(v => v >= targetConstant ? '#10b981' : '#f59e0b');

        // Update Stat Cards & Badges
        const badgeEl = document.getElementById('selectedMonthBadge');
        if (badgeEl) badgeEl.innerText = col.name;

        const avgEl = document.getElementById('monthAvgStat');
        const avgVal = monthlyAverages[monthNum] !== undefined && monthlyAverages[monthNum] !== null ?
          monthlyAverages[monthNum] : null;
        if (avgEl) avgEl.innerText = avgVal !== null ? formatNumber(avgVal) + ' m' : '0 m';

        // Hitung line paling andal (angka terkecil), achieved, below
        // Karena rawLineData sudah diurutkan dari terkecil ke terbesar, ambil item dengan nilai terkecil
        const positiveLines = rawLineData.filter(d => d.val > 0);
        const minItem = positiveLines.length > 0 ? positiveLines[0] : (rawLineData.length > 0 ? rawLineData[0] :
          null);

        let achievedCount = 0;
        let belowCount = 0;

        lineValues.forEach((val) => {
          if (val >= targetConstant) {
            achievedCount++;
          } else {
            belowCount++;
          }
        });

        const peakEl = document.getElementById('monthPeakLine');
        if (peakEl) {
          peakEl.innerText = minItem && minItem.val > 0 ? `${minItem.line} (${formatNumber(minItem.val)} m)` : '-';
        }

        const achievedEl = document.getElementById('monthAchievedCount');
        if (achievedEl) achievedEl.innerText = `${achievedCount} / ${lineNames.length}`;

        const belowEl = document.getElementById('monthBelowCount');
        if (belowEl) belowEl.innerText = `${belowCount} / ${lineNames.length}`;

        // Update Pill Aktif
        document.querySelectorAll('.month-pill').forEach((pill, idx) => {
          if (idx === monthIndex) {
            pill.className =
              'month-pill px-2.5 py-1 rounded-lg text-xs font-semibold transition bg-blue-600 text-white shadow ring-1 ring-blue-400';
          } else {
            pill.className =
              'month-pill px-2.5 py-1 rounded-lg text-xs font-semibold transition bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800';
          }
        });

        // Hitung batas maksimum sumbu Y secara proporsional dengan ruang headroom
        const maxDataVal = Math.max(...lineValues, targetConstant);
        const yMax = Math.ceil((maxDataVal + 1000) / 1000) * 1000;

        if (!lineDetailChart) {
          lineDetailChart = new Chart(lineCtx, {
            data: {
              labels: lineNames,
              datasets: [
                // 1. Line Target (4.943 Menit)
                {
                  type: 'line',
                  label: 'Target (4.943 Menit)',
                  data: targetValues,
                  borderColor: '#ef4444',
                  backgroundColor: '#ef4444',
                  borderDash: [6, 4],
                  borderWidth: 2,
                  pointRadius: 0,
                  fill: false,
                  order: 1,
                  datalabels: {
                    display: false
                  }
                },
                // 2. Bar MTBF tiap Line
                {
                  type: 'bar',
                  label: 'MTBF Line (Menit)',
                  data: lineValues,
                  backgroundColor: bgColors,
                  hoverBackgroundColor: hoverBgColors,
                  borderColor: borderColors,
                  borderWidth: 1.5,
                  borderRadius: 6,
                  borderSkipped: false,
                  order: 2,
                  datalabels: {
                    display: function(context) {
                      return Number(context.dataset.data[context.dataIndex]) > 0;
                    },
                    anchor: 'end',
                    align: 'top',
                    offset: 4,
                    formatter: (value) => formatNumber(value),
                    color: function(context) {
                      return Number(context.dataset.data[context.dataIndex]) >= targetConstant ? '#6ee7b7' :
                        '#fde68a';
                    },
                    font: {
                      family: 'JetBrains Mono, monospace',
                      weight: 'bold',
                      size: 10
                    }
                  }
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
              layout: {
                padding: {
                  top: 25,
                  bottom: 10,
                  left: 10,
                  right: 15
                }
              },
              scales: {
                y: {
                  beginAtZero: true,
                  max: yMax,
                  grid: {
                    color: 'rgba(51, 65, 85, 0.4)',
                    drawBorder: false
                  },
                  ticks: {
                    color: '#94a3b8',
                    font: {
                      family: 'JetBrains Mono',
                      size: 11
                    },
                    callback: (v) => formatNumber(v) + ' m'
                  },
                  title: {
                    display: true,
                    text: 'MTBF (Menit)',
                    color: '#e2e8f0',
                    font: {
                      weight: 'bold',
                      size: 12
                    }
                  }
                },
                x: {
                  grid: {
                    color: 'rgba(51, 65, 85, 0.15)',
                    drawBorder: false
                  },
                  ticks: {
                    color: '#cbd5e1',
                    autoSkip: false,
                    maxRotation: 45,
                    minRotation: 30,
                    font: {
                      family: 'Inter, sans-serif',
                      weight: '600',
                      size: 10
                    }
                  }
                }
              },
              plugins: {
                legend: {
                  display: false
                },
                tooltip: {
                  backgroundColor: 'rgba(15, 23, 42, 0.95)',
                  titleColor: '#f8fafc',
                  bodyColor: '#e2e8f0',
                  borderColor: '#334155',
                  borderWidth: 1,
                  padding: 10,
                  titleFont: {
                    weight: 'bold',
                    size: 12
                  },
                  bodyFont: {
                    family: 'JetBrains Mono',
                    size: 12
                  },
                  callbacks: {
                    label: function(context) {
                      const val = context.parsed.y;
                      if (val === null || val === undefined) return null;
                      const status = val >= targetConstant ? '✅ Memenuhi Target' : '⚠️ Di Bawah Target';
                      return ` ${context.dataset.label}: ${formatNumber(val)} menit (${status})`;
                    }
                  }
                }
              }
            },
            plugins: [ChartDataLabels]
          });
        } else {
          lineDetailChart.data.labels = lineNames;
          lineDetailChart.data.datasets[0].data = targetValues;
          lineDetailChart.data.datasets[1].data = lineValues;
          lineDetailChart.data.datasets[1].backgroundColor = bgColors;
          lineDetailChart.data.datasets[1].hoverBackgroundColor = hoverBgColors;
          lineDetailChart.data.datasets[1].borderColor = borderColors;
          lineDetailChart.options.scales.y.max = yMax;
          lineDetailChart.update();
        }
      }

      // Expose selectMonth globally agar bisa dipanggil tombol pill
      window.selectMonth = function(idx) {
        renderLineChart(idx);
      };

      // Tentukan bulan default yang aktif (pilih bulan pertama yang ada datanya, misal April)
      let initialMonthIndex = 0;
      for (let i = 0; i < matrixColumns.length; i++) {
        const mNum = matrixColumns[i].monthNumber;
        if (monthlyAverages[mNum] !== null && monthlyAverages[mNum] !== undefined && monthlyAverages[mNum] > 0) {
          initialMonthIndex = i;
          break;
        }
      }
      renderLineChart(initialMonthIndex);
    });
  </script>
@endpush

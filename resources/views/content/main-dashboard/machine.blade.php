@extends('layouts/contentNavbarLayout')

@section('title', 'Main Dashboard Machine - CMMS')

@section('content')
  <h4 class="fw-bold py-3 mb-4">
    <span class="text-muted fw-light">Main Dashboard /</span> Machine
  </h4>

  <!-- STAT CARD RINGKASAN -->
  <div class="row mb-4">
    <div class="col-lg-3 col-md-6 col-12 mb-4">
      <div class="card">
        <div class="card-body">
          <div class="card-title d-flex align-items-start justify-content-between">
            <div class="avatar flex-shrink-0">
              <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-cog"></i></span>
            </div>
          </div>
          <span class="fw-semibold d-block mb-1">Total Mesin Terdaftar</span>
          <h3 class="card-title mb-2">{{ number_format($totalMachine) }}</h3>
          <small class="text-success fw-semibold"><i class="bx bx-check-circle"></i> Sync dari MySQL</small>
        </div>
      </div>
    </div>
  </div>

  <!-- SECTION GRAFIK -->
  <div class="row">
    <!-- Grafik Per Line -->
    <div class="col-md-8 mb-4">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between pb-0">
          <div class="card-title mb-0">
            <h5 class="m-0 me-2">Top Line Produksi</h5>
            <small class="text-muted">Jumlah mesin berdasarkan line</small>
          </div>
        </div>
        <div class="card-body pt-3">
          <canvas id="chartLine" height="130"></canvas>
        </div>
      </div>
    </div>

    <!-- Grafik Distribusi Maker -->
    <div class="col-md-4 mb-4">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between pb-0">
          <div class="card-title mb-0">
            <h5 class="m-0 me-2">Distribusi Maker / Brand</h5>
            <small class="text-muted">Persentase merk mesin</small>
          </div>
        </div>
        <div class="card-body d-flex justify-content-center align-items-center pt-3">
          <canvas id="chartMaker" style="max-height: 220px;"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Script Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // Bar Chart - Line Produksi
    const ctxLine = document.getElementById('chartLine').getContext('2d');
    new Chart(ctxLine, {
      type: 'bar',
      data: {
        labels: {!! json_encode($machineByLine->pluck('line')) !!},
        datasets: [{
          label: 'Total Mesin',
          data: {!! json_encode($machineByLine->pluck('total')) !!},
          backgroundColor: '#696cff',
          borderRadius: 4
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: false
          }
        }
      }
    });

    // Doughnut Chart - Maker
    const ctxMaker = document.getElementById('chartMaker').getContext('2d');
    new Chart(ctxMaker, {
      type: 'doughnut',
      data: {
        labels: {!! json_encode($machineByMaker->pluck('maker')) !!},
        datasets: [{
          data: {!! json_encode($machineByMaker->pluck('total')) !!},
          backgroundColor: ['#696cff', '#8592a3', '#71dd37', '#ffab00', '#ff3e1d']
        }]
      },
      options: {
        responsive: true
      }
    });
  </script>
@endsection

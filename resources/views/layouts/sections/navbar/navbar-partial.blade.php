@php
  use Illuminate\Support\Facades\Auth;
  use Illuminate\Support\Facades\Route;
@endphp

<!-- Brand demo (display only for navbar-full and hide on below xl) -->
@if (isset($navbarFull))
  <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
    <a href="{{ url('/') }}" class="app-brand-link gap-2">
      <span class="app-brand-logo demo">@include('_partials.macros')</span>
      <span class="app-brand-text demo menu-text fw-bold text-heading">{{ config('variables.templateName') }}</span>
    </a>
  </div>
@endif

<!-- Toggle Menu untuk Mobile/Tablet -->
@if (!isset($navbarHideToggle))
  <div
    class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 {{ isset($contentNavbar) ? ' d-xl-none ' : '' }}">
    <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
      <i class="icon-base bx bx-menu icon-md"></i>
    </a>
  </div>
@endif

<!-- BAGIAN UTAMA NAVBAR (BANNER HINO & USER PROFILE) -->
<div class="navbar-nav-right d-flex align-items-center justify-content-between w-100" id="navbar-collapse">

  <!-- LEFT: LOGO HINO + JUDUL SYSTEM -->
  <div class="d-flex align-items-center gap-3">
    <div>
      <h5 class="mb-0 fw-bold text-dark">DASHBOARD MAINTENANCE</h5>
    </div>
  </div>

  <!-- RIGHT: TANGGAL & USER DROPDOWN -->
  <ul class="navbar-nav flex-row align-items-center ms-auto gap-3">

    <!-- Tanggal Otomatis (Muncul di Layar Sedang - Besar) -->
    <li class="nav-item d-none d-md-block">
      <span id="currentDateNav" class="badge bg-label-primary fs-6 px-3 py-2 fw-semibold">
        {{ date('l, d F Y') }}
      </span>
    </li>

    <!-- User Profile Dropdown -->
    <li class="nav-item navbar-dropdown dropdown-user dropdown">
      <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
        <div
          class="avatar avatar-online d-flex align-items-center justify-content-center bg-label-primary rounded-circle">
          <i class="bx bx-user fs-4"></i>
        </div>
      </a>
      <ul class="dropdown-menu dropdown-menu-end">
        <li>
          <a class="dropdown-item" href="javascript:void(0);">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0 me-3">
                <div
                  class="avatar avatar-online d-flex align-items-center justify-content-center bg-primary text-white rounded-circle">
                  <i class="bx bx-user fs-5"></i>
                </div>
              </div>
              <div class="flex-grow-1">
                <span class="fw-semibold d-block">{{ Auth::user()->name ?? session('user_name', 'Guest') }}</span>
                <small class="text-muted">{{ ucfirst(Auth::user()->role ?? session('user_role', '')) }}</small>
              </div>
            </div>
          </a>
        </li>
        <li>
          <div class="dropdown-divider"></div>
        </li>
        <li>
          <!-- Link Logout Berfungsi -->
          <a class="dropdown-item text-danger" href="{{ route('logout') }}"
            onclick="event.preventDefault(); document.getElementById('logout-form-nav').submit();">
            <i class="icon-base bx bx-power-off icon-md me-2"></i>
            <span class="align-middle">Log Out</span>
          </a>
          <form id="logout-form-nav" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
          </form>
        </li>
      </ul>
    </li>
    <!--/ User -->
  </ul>
</div>

<script>
  // Script update tanggal otomatis sesuai waktu sistem
  document.addEventListener('DOMContentLoaded', function() {
    const options = {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    };
    const dateEl = document.getElementById('currentDateNav');
    if (dateEl) {
      dateEl.innerText = new Date().toLocaleDateString('en-US', options);
    }
  });
</script>

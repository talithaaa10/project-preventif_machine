<header
  class="h-16 bg-slate-900/90 backdrop-blur border-b border-slate-800 sticky top-0 z-30 flex items-center justify-between px-4 lg:px-6">

  <!-- Left Side: Mobile Hamburger & Breadcrumb / Title -->
  <div class="flex items-center space-x-3">
    <!-- Mobile Hamburger Toggle -->
    <button @click="mobileSidebarOpen = true"
      class="lg:hidden p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition"
      aria-label="Open Sidebar">
      <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
      </svg>
    </button>

    <!-- Page Title / Breadcrumb -->
    <div>
      <h1 class="text-base lg:text-lg font-bold text-white tracking-wide flex items-center gap-2">
        <span>@yield('page-title', 'Dashboard Maintenance')</span>
      </h1>
      <p class="text-xs text-slate-400 hidden sm:block">Preventive Maintenance & Machine Monitoring System</p>
    </div>
  </div>

  <!-- Right Side: Live Indicator, Shift, Notifications, User -->
  <div class="flex items-center space-x-3 sm:space-x-4">

    <!-- Real-time Status Badge -->
    <div class="hidden md:flex items-center px-3 py-1.5 rounded-xl bg-slate-800/80 border border-slate-700/60 text-xs">
      <span class="relative flex h-2.5 w-2.5 mr-2">
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
      </span>
      <span class="text-slate-300 font-medium">IoT Stream:</span>
      <span class="ml-1 text-emerald-400 font-semibold">Connected</span>
    </div>

    <!-- Date Text -->
    <div class="hidden xl:block text-right">
      <div class="text-xs font-semibold text-slate-200">{{ date('l, d M Y') }}</div>
      <!-- <div class="text-[11px] text-slate-400">Timezone: Asia/Jakarta</div> -->
    </div>

    <!-- Notification Button / Dropdown -->
    <div x-data="{ notifOpen: false }" class="relative">
      <button @click="notifOpen = !notifOpen"
        class="relative p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-rose-500 ring-2 ring-slate-900"></span>
      </button>

      <!-- Dropdown Content -->
      <div x-show="notifOpen" @click.outside="notifOpen = false" x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-80 bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl py-2 z-50 text-slate-200"
        style="display: none;">
        <div class="px-4 py-2 border-b border-slate-800 flex items-center justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Notifikasi Mesin</span>
        </div>
      </div>
    </div>

    <!-- User Profile Dropdown -->
    @php
      $currentUser = Auth::user();
      $userName = $currentUser->name ?? session('user_name', 'Guest');
      $userRole = $currentUser->role ?? session('user_role', '');
      $initials = strtoupper(substr($userName, 0, 2));
    @endphp
    <div x-data="{ userOpen: false }" class="relative">
      <button @click="userOpen = !userOpen"
        class="flex items-center space-x-3 p-1.5 rounded-xl hover:bg-slate-800 transition focus:outline-none cursor-pointer"
        title="Profil Pengguna">
        <div
          class="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center font-bold text-white text-xs shadow-md shrink-0">
          {{ $initials }}
        </div>
        <div class="hidden sm:block text-left">
          <div class="text-xs font-bold text-slate-200 leading-tight">{{ $userName }}</div>
          @if($userRole)
            <div class="text-[10px] text-indigo-400 font-semibold uppercase tracking-wider">{{ $userRole }}</div>
          @endif
        </div>
        <svg class="w-4 h-4 text-slate-400 hidden sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </button>

      <!-- Dropdown Menu -->
      <div x-show="userOpen" @click.outside="userOpen = false" x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-56 bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl py-2 z-50 text-slate-200 text-xs"
        style="display: none;">
        <div class="px-4 py-2.5 border-b border-slate-800">
          <div class="font-bold text-white text-sm">{{ $userName }}</div>
          @if($userRole)
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold mt-1 {{ strtolower($userRole) === 'admin' ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30' }}">
              {{ strtoupper($userRole) }}
            </span>
          @else
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold mt-1 bg-amber-500/20 text-amber-300 border border-amber-500/30">
              BELUM LOGIN
            </span>
          @endif
        </div>

        @if(Auth::check() || session()->has('user_name'))
          @if(strtolower($userRole ?? '') === 'admin')
            <a href="{{ route('users.index') }}"
              class="flex items-center px-4 py-2.5 hover:bg-slate-800 text-slate-300 hover:text-white transition">
              <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              User Accounts
            </a>
            <a href="{{ route('roles.index') }}"
              class="flex items-center px-4 py-2.5 hover:bg-slate-800 text-slate-300 hover:text-white transition">
              <svg class="w-4 h-4 mr-2 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
              Role & Hak Akses
            </a>
            <div class="border-t border-slate-800 my-1"></div>
          @endif
          <form id="logout-form-alpine" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
          </form>
          <a href="{{ route('logout') }}"
            onclick="event.preventDefault(); document.getElementById('logout-form-alpine').submit();"
            class="flex items-center px-4 py-2.5 hover:bg-rose-500/10 text-rose-400 hover:text-rose-300 transition cursor-pointer">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Log Out
          </a>
        @else
          <a href="{{ route('auth-login-basic') }}"
            class="flex items-center px-4 py-2.5 hover:bg-indigo-600/20 text-indigo-400 hover:text-white transition font-semibold">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
            </svg>
            Masuk / Login Akun
          </a>
        @endif
      </div>
    </div>

  </div>

</header>

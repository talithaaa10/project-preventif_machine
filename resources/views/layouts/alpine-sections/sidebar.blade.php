<!-- Sidebar Container -->
<aside
  class="fixed inset-y-0 left-0 z-40 flex flex-col bg-slate-900 border-r border-slate-800 transition-all duration-300 ease-in-out select-none"
  :class="{
      'w-64': sidebarOpen,
      'w-20': !sidebarOpen,
      '-translate-x-full lg:translate-x-0': !mobileSidebarOpen,
      'translate-x-0': mobileSidebarOpen
  }">
  <!-- Sidebar Header / Logo -->
  <div class="h-16 flex items-center justify-between px-4 border-b border-slate-800/80 bg-slate-950/40">
    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 overflow-hidden">
      <div
        class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-cyan-400 flex items-center justify-center shadow-lg shadow-indigo-500/25 shrink-0">
        <!-- Machine / Cog Icon -->
        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
      </div>
      <div x-show="sidebarOpen" x-transition.opacity.duration.200ms class="truncate">
        <span class="text-base font-bold tracking-wider text-white">HINO</span>
        <span class="block text-[10px] uppercase font-semibold text-indigo-400 tracking-widest -mt-1">Preventive
          Maint.</span>
      </div>
    </a>

    <!-- Mobile Close Button -->
    <button @click="mobileSidebarOpen = false"
      class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
      <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
  </div>

  <!-- Navigation Menu List -->
  <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1.5 custom-scrollbar text-sm font-medium">

    <!-- 1. DASHBOARD UTAMA -->
    <div>
      <a href="{{ route('dashboard') }}"
        class="group flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
        <svg
          class="w-5 h-5 shrink-0 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-indigo-400' }}"
          fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="ml-3 truncate">Dashboard</span>
        <span x-show="sidebarOpen"
          class="ml-auto px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Live</span>
      </a>
    </div>
    <!-- 2. MAIN DASHBOARD (ACCORDION TUNGGAL) -->
    <div x-data="{ open: {{ request()->is('machine-breakdown*') || request()->is('main-dashboard*') ? 'true' : 'false' }} }">
      <button @click="open = !open; if(!sidebarOpen) sidebarOpen = true"
        class="w-full group flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->is('machine-breakdown*') || request()->is('main-dashboard*') ? 'text-white bg-slate-800/90' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
        <div class="flex items-center overflow-hidden">
          <svg
            class="w-5 h-5 shrink-0 {{ request()->is('machine-breakdown*') || request()->is('main-dashboard*') ? 'text-indigo-400' : 'text-slate-400 group-hover:text-indigo-400' }}"
            fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
          <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="ml-3 truncate">Main Dashboard</span>
        </div>
        <svg x-show="sidebarOpen" class="w-4 h-4 shrink-0 transition-transform duration-200"
          :class="open ? 'rotate-180 text-indigo-400' : 'text-slate-500'" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </button>

      <div x-show="open && sidebarOpen" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2" class="mt-1 pl-9 pr-2 space-y-1">
        <a href="{{ route('main-dashboard.battery') }}"
          class="block px-2.5 py-1.5 rounded-lg text-xs {{ request()->routeIs('main-dashboard.battery') ? 'text-indigo-400 bg-indigo-500/10 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">Battery</a>
        <a href="{{ route('machine-breakdown.line-stop') }}"
          class="block px-2.5 py-1.5 rounded-lg text-xs {{ request()->routeIs('machine-breakdown.line-stop') ? 'text-indigo-400 bg-indigo-500/10 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">Line Stop</a>
        <a href="{{ route('machine-breakdown.index') }}"
          class="block px-2.5 py-1.5 rounded-lg text-xs {{ request()->routeIs('machine-breakdown.index') ? 'text-indigo-400 bg-indigo-500/10 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">Machine Breakdown</a>
        <a href="{{ route('machine-breakdown.mttr') }}"
          class="block px-2.5 py-1.5 rounded-lg text-xs {{ request()->routeIs('machine-breakdown.mttr') ? 'text-indigo-400 bg-indigo-500/10 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">MTTR</a>
        <a href="{{ route('machine-breakdown.mbtf') }}"
          class="block px-2.5 py-1.5 rounded-lg text-xs {{ request()->routeIs('machine-breakdown.mbtf') ? 'text-indigo-400 bg-indigo-500/10 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">MTBF</a>
      </div>
    </div>

    <!-- 3. MASTER DATA (ACCORDION) -->
    <div x-data="{ open: {{ request()->is('master-data*') ? 'true' : 'false' }} }">
      <button @click="open = !open; if(!sidebarOpen) sidebarOpen = true"
        class="w-full group flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->is('master-data*') ? 'text-white bg-slate-800/90' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
        <div class="flex items-center overflow-hidden">
          <svg
            class="w-5 h-5 shrink-0 {{ request()->is('master-data*') ? 'text-cyan-400' : 'text-slate-400 group-hover:text-cyan-400' }}"
            fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
          </svg>
          <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="ml-3 truncate">Master Data</span>
        </div>
        <svg x-show="sidebarOpen" class="w-4 h-4 shrink-0 transition-transform duration-200"
          :class="open ? 'rotate-180 text-cyan-400' : 'text-slate-500'" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </button>

      <div x-show="open && sidebarOpen" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2" class="mt-1 pl-9 pr-2 space-y-1">
        <a href="{{ route('master-data.battery') }}"
          class="block px-2.5 py-1.5 rounded-lg text-xs {{ request()->routeIs('master-data.battery') ? 'text-cyan-400 bg-cyan-500/10 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">Battery</a>
        <a href="{{ route('machine-breakdown.index') }}"
          class="block px-2.5 py-1.5 rounded-lg text-xs {{ request()->routeIs('machine-breakdown.index') ? 'text-cyan-400 bg-cyan-500/10 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">Machine Breakdown</a>
        <a href="{{ route('machine-breakdown.line-stop') }}"
          class="block px-2.5 py-1.5 rounded-lg text-xs {{ request()->routeIs('machine-breakdown.line-stop') ? 'text-cyan-400 bg-cyan-500/10 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">Line Stop</a>
        <a href="{{ route('machine-breakdown.mbtf') }}"
          class="block px-2.5 py-1.5 rounded-lg text-xs {{ request()->routeIs('machine-breakdown.mbtf') ? 'text-cyan-400 bg-cyan-500/10 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">MTBF</a>
        <a href="{{ route('machine-breakdown.mttr') }}"
          class="block px-2.5 py-1.5 rounded-lg text-xs {{ request()->routeIs('machine-breakdown.mttr') ? 'text-cyan-400 bg-cyan-500/10 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">MTTR</a>
      </div>
    </div>

    <!-- 4. INPUT MANUAL DATA & MASTER DATA (ACCORDION) -->
    <div x-data="{ open: {{ request()->is('input*') || request()->routeIs('machines.*') ? 'true' : 'false' }} }">
      <button @click="open = !open; if(!sidebarOpen) sidebarOpen = true"
        class="w-full group flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->is('input*') || request()->routeIs('machines.*') ? 'text-white bg-slate-800/90' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
        <div class="flex items-center overflow-hidden">
          <svg
            class="w-5 h-5 shrink-0 {{ request()->is('input*') || request()->routeIs('machines.*') ? 'text-amber-400' : 'text-slate-400 group-hover:text-amber-400' }}"
            fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
          <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="ml-3 truncate">Input Manual Data</span>
        </div>
        <svg x-show="sidebarOpen" class="w-4 h-4 shrink-0 transition-transform duration-200"
          :class="open ? 'rotate-180 text-amber-400' : 'text-slate-500'" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </button>

      <div x-show="open && sidebarOpen" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2" class="mt-1 pl-9 pr-2 space-y-1">
        <a href="{{ route('machines.index') }}"
          class="block px-2.5 py-1.5 rounded-lg text-xs {{ request()->routeIs('machines.*') ? 'text-amber-400 bg-amber-500/10 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">Master Data MC</a>
        <a href="{{ route('input.machine') }}"
          class="block px-2.5 py-1.5 rounded-lg text-xs {{ request()->routeIs('input.machine') ? 'text-amber-400 bg-amber-500/10 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">Input Mesin Baru</a>
        <a href="{{ route('input.battery') }}"
          class="block px-2.5 py-1.5 rounded-lg text-xs {{ request()->routeIs('input.battery') ? 'text-amber-400 bg-amber-500/10 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">Input Battery Check</a>
      </div>
    </div>

    <!-- SEPARATOR HEADER & ADMINISTRASI (Hanya Tampil Untuk Admin) -->
    @php
      $currentUserRole = Auth::user()?->role ?? session('user_role', '');
      $isAdmin = strtolower($currentUserRole) === 'admin';
    @endphp

    @if($isAdmin)
    <div x-show="sidebarOpen" class="pt-4 pb-1 px-3">
      <span class="text-[10px] uppercase font-bold tracking-wider text-slate-500">Administrasi</span>
    </div>

    <!-- 5. USER MANAGEMENT -->
    <div>
      <a href="{{ route('users.index') }}"
        class="group flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('users.*') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
        <svg
          class="w-5 h-5 shrink-0 {{ request()->routeIs('users.*') ? 'text-indigo-400' : 'text-slate-400 group-hover:text-indigo-400' }}"
          fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="ml-3 truncate">User Management</span>
      </a>
    </div>

    <!-- 6. ROLE -->
    <div>
      <a href="{{ route('roles.index') }}"
        class="group flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('roles.*') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/70' }}">
        <svg
          class="w-5 h-5 shrink-0 {{ request()->routeIs('roles.*') ? 'text-purple-400' : 'text-slate-400 group-hover:text-purple-400' }}"
          fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        </svg>
        <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="ml-3 truncate">Role</span>
      </a>
    </div>
    @endif

  </nav>

  <!-- Sidebar Footer: Plant Status & Collapse Trigger -->
  <div class="p-3 border-t border-slate-800/80 bg-slate-950/40">
    <!-- Active Shift Status
    <div x-show="sidebarOpen" class="mb-3 px-3 py-2 rounded-xl bg-slate-800/60 border border-slate-700/50 flex items-center justify-between">
      <div class="flex items-center space-x-2">
        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
        <span class="text-xs font-semibold text-slate-300">Shift 1 (Active)</span>
      </div>
      <span class="text-[11px] font-mono text-indigo-400">Plant #1</span>
    </div> -->

    <!-- Collapse Toggle Button (Desktop) -->
    <button @click="sidebarOpen = !sidebarOpen"
      class="hidden lg:flex w-full items-center justify-center p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
      :title="sidebarOpen ? 'Collapse Sidebar' : 'Expand Sidebar'">
      <svg class="w-5 h-5 transition-transform duration-300" :class="sidebarOpen ? '' : 'rotate-180'" fill="none"
        viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
      </svg>
      <span x-show="sidebarOpen" class="ml-2 text-xs font-medium">Kecilkan Menu</span>
    </button>
  </div>
</aside>

<!DOCTYPE html>
<html lang="en" class="dark">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Dashboard Maintenance') - HINO</title>
  <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

  <!-- Google Fonts: Inter & JetBrains Mono for industrial numbers -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap"
    rel="stylesheet">

  <!-- Tailwind CSS CDN with configuration -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
            mono: ['JetBrains Mono', 'monospace'],
          },
          colors: {
            industrial: {
              50: '#f8fafc',
              100: '#f1f5f9',
              800: '#1e293b',
              900: '#0f172a',
              950: '#020617',
            }
          },
          animation: {
            'spin-slow': 'spin 8s linear infinite',
            'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
          }
        }
      }
    }
  </script>

  <!-- Custom Global Scrollbar Style -->
  <style>
    .custom-scrollbar::-webkit-scrollbar {
      width: 5px;
      height: 5px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
      background: rgba(15, 23, 42, 0.6);
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
      background: rgba(51, 65, 85, 0.8);
      border-radius: 9999px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
      background: rgba(99, 102, 241, 0.8);
    }
  </style>

  <!-- Chart.js CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- Alpine.js Core with defer -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

  <!-- Global Alpine Component Registration -->
  <script>
    function registerGlobalAlpineComponents() {
      if (window.Alpine) {
        // Alpine Chart.js integration
        Alpine.data('chartjs', () => ({
          _chart: null,
          init() {
            const el = this.$el;
            const ctx = el.getContext ? el.getContext('2d') : el.querySelector('canvas')?.getContext('2d');
            if (!ctx) return;

            let config = {};
            try {
              const raw = el.getAttribute('data-config');
              config = raw ? JSON.parse(raw) : {};
            } catch (err) {
              console.error('Error parsing chart data-config:', err);
            }

            // Set default dark mode styles for Chart.js
            Chart.defaults.color = '#94a3b8';
            Chart.defaults.borderColor = 'rgba(51, 65, 85, 0.4)';

            this._chart = new Chart(ctx, config);
          },
          destroy() {
            if (this._chart) {
              this._chart.destroy();
              this._chart = null;
            }
          }
        }));
      }
    }

    document.addEventListener('alpine:init', registerGlobalAlpineComponents);
    if (window.Alpine) {
      registerGlobalAlpineComponents();
    }
  </script>

  @stack('styles')
</head>

<body class="bg-slate-950 text-slate-100 font-sans antialiased selection:bg-indigo-500 selection:text-white"
  x-data="{
      sidebarOpen: true,
      mobileSidebarOpen: false
  }">

  <!-- Mobile Overlay Backdrop -->
  <div x-show="mobileSidebarOpen" @click="mobileSidebarOpen = false" x-transition.opacity.duration.300ms
    class="fixed inset-0 z-30 bg-slate-950/80 backdrop-blur-sm lg:hidden" style="display: none;"></div>

  <!-- Sidebar Component -->
  @include('layouts.alpine-sections.sidebar')

  <!-- Main Content Area -->
  <div class="min-h-screen flex flex-col transition-all duration-300 ease-in-out"
    :class="{
        'lg:ml-64': sidebarOpen,
        'lg:ml-20': !sidebarOpen
    }">
    <!-- Top Navbar -->
    @include('layouts.alpine-sections.navbar')

    <!-- Content Slot -->
    <main class="flex-1 p-4 lg:p-6 max-w-[1600px] w-full mx-auto">
      @yield('content')
    </main>

    <!-- Footer -->
    <footer class="py-4 px-6 border-t border-slate-800/80 text-center text-xs text-slate-500 bg-slate-900/40">
      <span>&copy; {{ date('Y') }} PT HINO Motors Manufacturing Indonesia &bull; Preventive Maintenance
        System</span>
    </footer>
  </div>

  @stack('scripts')
</body>

</html>

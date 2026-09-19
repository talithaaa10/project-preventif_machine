@extends('layouts.alpineLayout')

@section('title', 'Role & Hak Akses Tombol - CMMS')
@section('page-title', 'Manajemen Role & Hak Akses Tombol')

@section('content')
<div 
  x-data="{ 
    addRoleModalOpen: false,
    selectedRole: {{ $selectedRole->id }},
    selectAll(category) {
      document.querySelectorAll(`input[data-category='${category}']`).forEach(el => el.checked = true);
    },
    deselectAll(category) {
      document.querySelectorAll(`input[data-category='${category}']`).forEach(el => el.checked = false);
    },
    toggleGlobal(state) {
      document.querySelectorAll('input.perm-checkbox').forEach(el => el.checked = state);
    }
  }" 
  class="space-y-6 text-slate-100"
>

  <!-- 1. HEADER & ACTION BAR -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl">
    <div class="flex items-center gap-3">
      <div class="p-2.5 rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-400">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        </svg>
      </div>
      <div>
        <h2 class="text-lg font-bold text-white tracking-wide">Pengaturan Hak Akses Tombol & Peran</h2>
        <p class="text-xs text-slate-400 mt-0.5">
          Tentukan tombol-tombol aksi apa saja yang dapat digunakan oleh masing-masing peran pengguna &bull; Total: <span class="text-purple-400 font-bold font-mono">{{ $roles->count() }} Role</span>
        </p>
      </div>
    </div>

    <!-- Tombol Tambah Role Baru -->
    <div class="flex items-center gap-2">
      <button 
        @click="addRoleModalOpen = true"
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs transition shadow-lg shadow-purple-600/30"
      >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Role Baru
      </button>
    </div>
  </div>

  <!-- 2. FLASH NOTIFICATIONS -->
  @if (session('success'))
    <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs flex items-center gap-2.5 shadow-lg">
      <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <span class="font-medium">{{ session('success') }}</span>
    </div>
  @endif

  @if (isset($errors) && $errors->any())
    <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs shadow-lg">
      <div class="font-bold mb-1">Terdapat kesalahan input:</div>
      <ul class="list-disc list-inside space-y-0.5">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <!-- 3. MAIN GRID (ROLES LIST & PERMISSIONS MATRIX) -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

    <!-- LEFT COLUMN: ROLES SELECTOR (4 COLS) -->
    <div class="lg:col-span-4 space-y-4">
      <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-slate-800">
          <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-purple-400"></span>
            Pilih Role Pengguna
          </h3>
          <span class="text-[11px] font-mono text-slate-400">{{ $roles->count() }} Role</span>
        </div>

        <div class="space-y-2.5">
          @foreach($roles as $role)
            @php
              $isSelected = $selectedRole->id === $role->id;
              $activePermCount = is_array($role->permissions) ? count($role->permissions) : 0;
              $userCount = $roleUserCounts[$role->name] ?? 0;
            @endphp
            <a 
              href="{{ route('roles.index', ['role_id' => $role->id]) }}"
              class="block p-3.5 rounded-xl border transition-all duration-200 {{ $isSelected ? 'bg-purple-600/10 border-purple-500/50 shadow-lg shadow-purple-600/10 ring-1 ring-purple-500/40' : 'bg-slate-950/60 border-slate-800/80 hover:bg-slate-800/40 hover:border-slate-700' }}"
            >
              <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs shrink-0 {{ $role->name === 'admin' ? 'bg-indigo-500/20 text-indigo-400 border border-indigo-500/30' : 'bg-cyan-500/20 text-cyan-400 border border-cyan-500/30' }}">
                    @if($role->name === 'admin')
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                      </svg>
                    @else
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                      </svg>
                    @endif
                  </div>
                  <div>
                    <div class="flex items-center gap-2">
                      <span class="font-bold text-white text-xs">{{ $role->display_name }}</span>
                      @if($isSelected)
                        <span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span>
                      @endif
                    </div>
                    <span class="text-[10px] font-mono text-slate-400 uppercase">Slug: {{ $role->name }}</span>
                  </div>
                </div>

                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-semibold {{ $isSelected ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : 'bg-slate-800 text-slate-400' }}">
                  {{ $userCount }} Pengguna
                </span>
              </div>

              <div class="mt-2.5 pt-2 border-t border-slate-800/60 flex items-center justify-between text-[11px] text-slate-400">
                <span class="truncate max-w-[180px]">{{ $role->description ?? 'Tidak ada keterangan' }}</span>
                <span class="font-mono text-purple-400 text-[10px] font-semibold shrink-0">{{ $activePermCount }} Tombol Aktif</span>
              </div>
            </a>
          @endforeach
        </div>
      </div>

      <!-- Quick Info Card -->
      <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800 text-xs text-slate-400 space-y-2">
        <div class="flex items-center gap-2 font-semibold text-slate-300">
          <svg class="w-4 h-4 text-purple-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span>Cara Kerja Hak Akses Tombol:</span>
        </div>
        <p class="text-[11px] leading-relaxed">
          Semua pengguna dengan role yang sama masuk ke tampilan halaman yang sama (Dashboard, Battery, Line Stop, dll).
        </p>
        <p class="text-[11px] leading-relaxed">
          Tombol aksi (seperti <strong class="text-slate-200">Tambah Manual</strong>, <strong class="text-slate-200">Edit</strong>, <strong class="text-slate-200">Hapus</strong>, <strong class="text-slate-200">Import</strong>, atau <strong class="text-slate-200">Export</strong>) hanya akan tampil jika dicentang pada checklist di sebelah kanan.
        </p>
      </div>
    </div>

    <!-- RIGHT COLUMN: PERMISSIONS CHECKLIST MATRIX (8 COLS) -->
    <div class="lg:col-span-8">
      <form action="{{ route('roles.updatePermissions', $selectedRole->id) }}" method="POST" class="space-y-6">
        @csrf

        <!-- Top Role Header Bar -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-purple-600 to-indigo-600 flex items-center justify-center font-bold text-white shadow-lg shadow-purple-600/20 shrink-0">
              {{ strtoupper(substr($selectedRole->name, 0, 2)) }}
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h3 class="text-base font-bold text-white">{{ $selectedRole->display_name }}</h3>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase bg-purple-500/10 text-purple-400 border border-purple-500/20">
                  {{ $selectedRole->name }}
                </span>
              </div>
              <p class="text-xs text-slate-400 mt-0.5">
                Konfigurasi tombol yang dapat dilihat dan diklik oleh pengguna ber-role ini
              </p>
            </div>
          </div>

          <!-- Quick Action Buttons -->
          <div class="flex items-center gap-2">
            <button 
              type="button" 
              @click="toggleGlobal(true)"
              class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-[11px] font-medium transition border border-slate-700"
            >
              Pilih Semua
            </button>
            <button 
              type="button" 
              @click="toggleGlobal(false)"
              class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-slate-200 text-[11px] font-medium transition border border-slate-700"
            >
              Batal Semua
            </button>
            <button 
              type="submit" 
              class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs transition shadow-lg shadow-purple-600/30"
            >
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              Simpan Hak Akses
            </button>
          </div>
        </div>

        <!-- Permissions Categories Cards -->
        @php
          $currentPerms = is_array($selectedRole->permissions) ? $selectedRole->permissions : [];
        @endphp

        <div class="space-y-4">
          @foreach($availablePermissions as $categoryName => $permissions)
            @php
              $catSlug = \Illuminate\Support\Str::slug($categoryName);
            @endphp
            <div class="rounded-2xl bg-slate-900 border border-slate-800 shadow-xl overflow-hidden">
              <div class="p-3.5 bg-slate-950/60 border-b border-slate-800/80 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                  <span class="w-2 h-2 rounded-full bg-purple-400"></span>
                  <h4 class="text-xs font-bold text-white uppercase tracking-wider">{{ $categoryName }}</h4>
                </div>
                <div class="flex items-center gap-2">
                  <button 
                    type="button" 
                    @click="selectAll('{{ $catSlug }}')"
                    class="text-[10px] text-purple-400 hover:text-purple-300 font-mono transition"
                  >
                    Centang Kategori
                  </button>
                  <span class="text-slate-600">&bull;</span>
                  <button 
                    type="button" 
                    @click="deselectAll('{{ $catSlug }}')"
                    class="text-[10px] text-slate-500 hover:text-slate-400 font-mono transition"
                  >
                    Hapus
                  </button>
                </div>
              </div>

              <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($permissions as $permKey => $permMeta)
                  @php
                    $isChecked = in_array($permKey, $currentPerms, true);
                  @endphp
                  <label class="group relative flex items-start gap-3 p-3 rounded-xl border transition-all cursor-pointer select-none bg-slate-950/40 border-slate-800 hover:bg-slate-800/40 hover:border-slate-700">
                    <input 
                      type="checkbox" 
                      name="permissions[]" 
                      value="{{ $permKey }}"
                      data-category="{{ $catSlug }}"
                      class="perm-checkbox mt-0.5 h-4 w-4 rounded border-slate-700 bg-slate-900 text-purple-600 focus:ring-purple-500 focus:ring-offset-slate-900 transition shrink-0 cursor-pointer"
                      {{ $isChecked ? 'checked' : '' }}
                    >
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center justify-between gap-1">
                        <span class="text-xs font-bold text-slate-200 group-hover:text-white transition">
                          {{ $permMeta['label'] }}
                        </span>
                        <span class="text-[9px] font-mono text-purple-400/80 bg-purple-500/10 px-1.5 py-0.5 rounded border border-purple-500/20 shrink-0">
                          {{ $permKey }}
                        </span>
                      </div>
                      <p class="text-[11px] text-slate-400 mt-1 leading-normal">
                        {{ $permMeta['description'] }}
                      </p>
                    </div>
                  </label>
                @endforeach
              </div>
            </div>
          @endforeach
        </div>

        <!-- Sticky Bottom Save Bar -->
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl flex items-center justify-between">
          <div class="text-xs text-slate-400">
            Pastikan perubahan hak akses tombol telah sesuai kebutuhan operasional sebelum disimpan.
          </div>
          <button 
            type="submit" 
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs transition shadow-lg shadow-purple-600/30"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Simpan Perubahan Hak Akses
          </button>
        </div>

      </form>
    </div>

  </div>

  <!-- 4. MODAL TAMBAH ROLE BARU -->
  <div 
    x-show="addRoleModalOpen" 
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
    style="display: none;"
  >
    <div 
      @click.away="addRoleModalOpen = false"
      class="bg-slate-900 border border-slate-700/80 rounded-2xl w-full max-w-md shadow-2xl overflow-hidden"
    >
      <div class="p-4 border-b border-slate-800 flex items-center justify-between bg-slate-950/60">
        <div class="flex items-center gap-2.5">
          <div class="w-2.5 h-2.5 rounded-full bg-purple-400"></div>
          <h3 class="text-sm font-bold text-white tracking-wide">Tambah Role / Peran Baru</h3>
        </div>
        <button 
          @click="addRoleModalOpen = false"
          class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
        >
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <form action="{{ route('roles.store') }}" method="POST" class="p-5 space-y-4">
        @csrf

        <div>
          <label for="new_role_display_name" class="block text-xs font-semibold text-slate-300 mb-1.5">
            Nama Tampilan Role <span class="text-rose-400">*</span>
          </label>
          <input 
            type="text" 
            id="new_role_display_name" 
            name="display_name" 
            placeholder="Contoh: Line Leader, Supervisor" 
            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 outline-none focus:ring-1 focus:ring-purple-500"
            required 
          />
        </div>

        <div>
          <label for="new_role_name" class="block text-xs font-semibold text-slate-300 mb-1.5">
            Kode / Identitas Role (Slug) <span class="text-rose-400">*</span>
          </label>
          <input 
            type="text" 
            id="new_role_name" 
            name="name" 
            placeholder="Contoh: leader, supervisor" 
            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 outline-none focus:ring-1 focus:ring-purple-500 font-mono"
            required 
          />
          <p class="text-[10px] text-slate-500 mt-1 font-mono">Hanya huruf kecil, angka, garis bawah (_)</p>
        </div>

        <div>
          <label for="new_role_desc" class="block text-xs font-semibold text-slate-300 mb-1.5">
            Deskripsi / Keterangan Peran
          </label>
          <textarea 
            id="new_role_desc" 
            name="description" 
            rows="2"
            placeholder="Keterangan cakupan peran ini..." 
            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 outline-none focus:ring-1 focus:ring-purple-500"
          ></textarea>
        </div>

        <div class="pt-3 border-t border-slate-800 flex items-center justify-end gap-2.5">
          <button 
            type="button" 
            @click="addRoleModalOpen = false"
            class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition"
          >
            Batal
          </button>
          <button 
            type="submit" 
            class="px-5 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold transition shadow-lg shadow-purple-600/30"
          >
            Simpan Role
          </button>
        </div>
      </form>
    </div>
  </div>

</div>
@endsection

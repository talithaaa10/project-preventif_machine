@extends('layouts.alpineLayout')

@section('title', 'User Management - CMMS')
@section('page-title', 'User Management')

@section('content')
<div 
  x-data="{ addUserModalOpen: false, search: '' }" 
  class="space-y-6 text-slate-100"
>

  <!-- 1. HEADER & ACTION BAR -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl">
    <div class="flex items-center gap-3">
      <div class="p-2.5 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
      </div>
      <div>
        <h2 class="text-lg font-bold text-white tracking-wide">Pengaturan Akun Pengguna</h2>
        <p class="text-xs text-slate-400 mt-0.5">
          Kelola hak akses dan peran pengguna aplikasi &bull; Total: <span class="text-indigo-400 font-bold font-mono">{{ $users->count() }} Pengguna</span>
        </p>
      </div>
    </div>

    <!-- Add User Button -->
    @hasPermission('user_manage')
    <button 
      @click="addUserModalOpen = true"
      class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs transition shadow-lg shadow-indigo-600/30"
    >
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
      </svg>
      Tambah User Baru
    </button>
    @endhasPermission
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

  @if ($errors->any())
    <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs shadow-lg">
      <div class="font-bold mb-1">Terdapat kesalahan input:</div>
      <ul class="list-disc list-inside space-y-0.5">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <!-- 3. USERS TABLE CARD -->
  <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
    <div class="p-4 border-b border-slate-800 bg-slate-950/40 flex items-center justify-between text-xs">
      <div class="flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
        <h3 class="font-semibold text-white">Daftar Akun Pengguna Terdaftar</h3>
      </div>
      <span class="font-mono text-slate-400">{{ $users->count() }} Akun</span>
    </div>

    <div class="overflow-x-auto custom-scrollbar">
      <table class="w-full text-left text-xs border-collapse">
        <thead>
          <tr class="border-b border-slate-800 text-[11px] font-mono text-slate-400 uppercase bg-slate-950/60">
            <th class="py-3 px-4 w-12 text-center">NO</th>
            <th class="py-3 px-4">USERNAME / NAMA</th>
            <th class="py-3 px-4">ROLE SAAT INI</th>
            <th class="py-3 px-4">UBAH ROLE</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-800/60 font-sans">
          @forelse($users as $user)
            <tr class="hover:bg-slate-800/40 transition-colors">
              <td class="py-3 px-4 text-center font-mono text-slate-500">{{ $loop->iteration }}</td>
              <td class="py-3 px-4">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-xs text-indigo-400 shrink-0">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                  </div>
                  <div>
                    <span class="font-bold text-white block">{{ $user->name }}</span>
                    <span class="text-[10px] text-slate-500 font-mono">ID: #{{ $user->id }}</span>
                  </div>
                </div>
              </td>
              <td class="py-3 px-4">
                @if ($user->role === 'admin')
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold font-mono bg-indigo-500/10 text-indigo-400 border border-indigo-500/30">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    ADMIN
                  </span>
                @else
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold font-mono bg-cyan-500/10 text-cyan-400 border border-cyan-500/30">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    USER
                  </span>
                @endif
              </td>
              <td class="py-3 px-4">
                @hasPermission('user_manage')
                <form action="{{ route('users.updateRole', $user->id) }}" method="POST" class="flex items-center gap-2">
                  @csrf
                  <select 
                    name="role" 
                    class="bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl px-3 py-1.5 outline-none focus:ring-1 focus:ring-indigo-500 font-mono"
                  >
                    @foreach($roles as $r)
                      <option value="{{ $r->name }}" {{ $user->role === $r->name ? 'selected' : '' }}>
                        {{ $r->display_name }} ({{ strtoupper($r->name) }})
                      </option>
                    @endforeach
                  </select>
                  <button 
                    type="submit" 
                    class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-indigo-600 text-slate-300 hover:text-white text-xs font-semibold transition border border-slate-700"
                  >
                    Update
                  </button>
                </form>
                @else
                <span class="text-slate-500 font-mono text-xs italic">Akses Terbatas</span>
                @endhasPermission
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="py-12 text-center text-slate-500 font-mono text-xs">
                Belum ada akun user terdaftar.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- 4. MODAL POPUP TAMBAH USER -->
  <div 
    x-show="addUserModalOpen" 
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
      @click.away="addUserModalOpen = false"
      class="bg-slate-900 border border-slate-700/80 rounded-2xl w-full max-w-md shadow-2xl overflow-hidden"
    >
      <div class="p-4 border-b border-slate-800 flex items-center justify-between bg-slate-950/60">
        <div class="flex items-center gap-2.5">
          <div class="w-2.5 h-2.5 rounded-full bg-indigo-400"></div>
          <h3 class="text-sm font-bold text-white tracking-wide">Tambah Akun Baru</h3>
        </div>
        <button 
          @click="addUserModalOpen = false"
          class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
        >
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <form action="{{ route('users.store') }}" method="POST" class="p-5 space-y-4">
        @csrf

        <div>
          <label for="name" class="block text-xs font-semibold text-slate-300 mb-1.5">
            Username / Nama <span class="text-rose-400">*</span>
          </label>
          <input 
            type="text" 
            id="name" 
            name="name" 
            placeholder="Masukkan Username" 
            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 outline-none focus:ring-1 focus:ring-indigo-500"
            required 
          />
        </div>

        <div>
          <label for="password" class="block text-xs font-semibold text-slate-300 mb-1.5">
            Password <span class="text-rose-400">*</span>
          </label>
          <input 
            type="password" 
            id="password" 
            name="password" 
            placeholder="••••••••" 
            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 outline-none focus:ring-1 focus:ring-indigo-500"
            required 
          />
        </div>

        <div>
          <label for="role" class="block text-xs font-semibold text-slate-300 mb-1.5">
            Hak Akses (Role) <span class="text-rose-400">*</span>
          </label>
          <select 
            id="role" 
            name="role" 
            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white outline-none focus:ring-1 focus:ring-indigo-500"
            required
          >
            @foreach($roles as $r)
              <option value="{{ $r->name }}">{{ $r->display_name }} ({{ strtoupper($r->name) }})</option>
            @endforeach
          </select>
        </div>

        <div class="pt-3 border-t border-slate-800 flex items-center justify-end gap-2.5">
          <button 
            type="button" 
            @click="addUserModalOpen = false"
            class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition"
          >
            Batal
          </button>
          <button 
            type="submit" 
            class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition shadow-lg shadow-indigo-600/30"
          >
            Simpan Akun
          </button>
        </div>
      </form>
    </div>
  </div>

</div>
@endsection

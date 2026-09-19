@extends('layouts.alpineLayout')

@section('title', 'Input Mesin Baru - CMMS')
@section('page-title', 'Form Input Mesin Manual')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 text-slate-100">

  <!-- Header Card -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl">
    <div class="flex items-center gap-3">
      <div class="p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
      </div>
      <div>
        <h2 class="text-lg font-bold text-white tracking-wide">Input Mesin Baru</h2>
        <p class="text-xs text-slate-400 mt-0.5">Tambah data mesin secara manual ke master data</p>
      </div>
    </div>

    <a 
      href="{{ route('machines.index') }}" 
      class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-semibold transition border border-slate-700 w-fit"
    >
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
      </svg>
      Kembali ke Master Data
    </a>
  </div>

  <!-- Flash Notifications -->
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

  <!-- Form Card -->
  <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl">
    <form action="{{ route('input.machine.store') }}" method="POST" class="space-y-5">
      @csrf

      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <!-- HMMI -->
        <div>
          <label for="hmmi" class="block text-xs font-semibold text-slate-300 mb-1.5">
            HMMI <span class="text-rose-400">*</span>
          </label>
          <input 
            type="text" 
            id="hmmi" 
            name="hmmi" 
            value="{{ old('hmmi') }}"
            placeholder="Contoh: HMMI-001"
            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 outline-none focus:ring-1 focus:ring-amber-500" 
            required
          />
        </div>

        <!-- OP NO -->
        <div>
          <label for="op_no" class="block text-xs font-semibold text-slate-300 mb-1.5">
            OP NO <span class="text-rose-400">*</span>
          </label>
          <input 
            type="text" 
            id="op_no" 
            name="op_no" 
            value="{{ old('op_no') }}"
            placeholder="Contoh: OP120"
            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 outline-none focus:ring-1 focus:ring-amber-500" 
            required
          />
        </div>

        <!-- LINE -->
        <div>
          <label for="line" class="block text-xs font-semibold text-slate-300 mb-1.5">
            LINE <span class="text-rose-400">*</span>
          </label>
          <input 
            type="text" 
            id="line" 
            name="line" 
            value="{{ old('line') }}"
            placeholder="Contoh: CONROD, CRANK SHAFT"
            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 outline-none focus:ring-1 focus:ring-amber-500" 
            required
          />
        </div>

        <!-- PRODUCT -->
        <div>
          <label for="product" class="block text-xs font-semibold text-slate-300 mb-1.5">
            PRODUCT
          </label>
          <input 
            type="text" 
            id="product" 
            name="product" 
            value="{{ old('product') }}"
            placeholder="Contoh: J08E, P11C"
            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 outline-none focus:ring-1 focus:ring-amber-500"
          />
        </div>

        <!-- MAKER -->
        <div>
          <label for="maker" class="block text-xs font-semibold text-slate-300 mb-1.5">
            MAKER
          </label>
          <input 
            type="text" 
            id="maker" 
            name="maker" 
            value="{{ old('maker') }}"
            placeholder="Contoh: FANUC, MITSUBISHI, MAZAK"
            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 outline-none focus:ring-1 focus:ring-amber-500"
          />
        </div>

        <!-- MODEL / TYPE -->
        <div>
          <label for="model_type" class="block text-xs font-semibold text-slate-300 mb-1.5">
            MODEL / TYPE
          </label>
          <input 
            type="text" 
            id="model_type" 
            name="model_type" 
            value="{{ old('model_type') }}"
            placeholder="Contoh: CNC Lathe, Machining Center"
            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 outline-none focus:ring-1 focus:ring-amber-500"
          />
        </div>

        <!-- SERIAL NO -->
        <div class="md:col-span-2">
          <label for="serial_no" class="block text-xs font-semibold text-slate-300 mb-1.5">
            SERIAL NO
          </label>
          <input 
            type="text" 
            id="serial_no" 
            name="serial_no" 
            value="{{ old('serial_no') }}"
            placeholder="Nomor Serial Mesin"
            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 outline-none focus:ring-1 focus:ring-amber-500"
          />
        </div>
      </div>

      <div class="pt-4 border-t border-slate-800 flex items-center justify-end gap-3">
        <a 
          href="{{ route('machines.index') }}" 
          class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition"
        >
          Batal
        </a>
        @hasPermission('machine_create')
        <button 
          type="submit" 
          class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold transition shadow-lg shadow-amber-600/30 flex items-center gap-2"
        >
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          Simpan Data Mesin
        </button>
        @endhasPermission
      </div>
    </form>
  </div>

</div>
@endsection

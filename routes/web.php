<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ExcelImportController;
use App\Http\Controllers\MachineBreakdownController;
use App\Http\Controllers\BatteryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Halaman Login & Proses Authenticate (Terbuka untuk Tamu)
Route::get('/', [LoginBasic::class, 'index'])->name('login');
Route::get('/login', fn () => redirect('/'));
Route::post('/login', [LoginBasic::class, 'authenticate'])->middleware('throttle:5,1')->name('login.post');

// Route Logout
Route::match(['get', 'post'], '/logout', [LoginBasic::class, 'logout'])->name('logout');

// Seluruh Rute Internal (Wajib Login / Authenticated)
Route::middleware(['auth'])->group(function () {
    // 2. Dashboard Utama
    Route::match(['get', 'post'], '/dashboard', [Analytics::class, 'index'])->name('dashboard');

    // 3. Data Mesin (Master Data - Tabel & Import)
    Route::get('/machines', [MachineController::class, 'index'])->name('machines.index');
    Route::get('/import-machines', [MachineController::class, 'showImportForm'])->name('machines.import.form');
    Route::post('/import-machines', [MachineController::class, 'import'])->name('machines.import');

    // Machine Breakdown
    Route::prefix('machine-breakdown')->group(function () {
        Route::get('/', [MachineBreakdownController::class, 'index'])->name('machine-breakdown.index');
        Route::get('/line-stop', [MachineBreakdownController::class, 'lineStop'])->name('machine-breakdown.line-stop');
        Route::get('/mbtf', [MachineBreakdownController::class, 'mbtf'])->name('machine-breakdown.mbtf');
        Route::get('/mttr', [MachineBreakdownController::class, 'mttr'])->name('machine-breakdown.mttr');
        Route::post('/import', [MachineBreakdownController::class, 'import'])->name('machine-breakdown.import');
    });

    // 4. Main Dashboard (Grafik & Analytics)
    Route::prefix('main-dashboard')->group(function () {
        Route::get('/machine', [MachineController::class, 'dashboardMachine'])->name('main-dashboard.machine');
        Route::get('/battery', [BatteryController::class, 'dashboardBattery'])->name('main-dashboard.battery');
    });

    // 5. Input Data (Form Input Manual)
    Route::prefix('input')->group(function () {
        Route::get('/machine', [MachineController::class, 'createMachine'])->name('input.machine');
        Route::post('/machine', [MachineController::class, 'store'])->name('input.machine.store');
        Route::get('/battery', [BatteryController::class, 'index'])->name('input.battery');
        Route::get('/battery/next-exchange', [BatteryController::class, 'getNextExchangeType'])->name('input.battery.next-exchange');
        Route::get('/battery/export', [BatteryController::class, 'exportInputBattery'])->name('input.battery.export');
        Route::post('/battery', [BatteryController::class, 'store'])->name('input.battery.store');
        Route::delete('/battery/{id}', [BatteryController::class, 'destroy'])->name('input.battery.destroy');
        Route::post('/battery/import', [BatteryController::class, 'import'])->name('input.battery.import');
        Route::post('/input-battery', [BatteryController::class, 'storeInputBattery'])->name('input.input-battery.store');
        Route::put('/input-battery/{id}', [BatteryController::class, 'updateInputBattery'])->name('input.input-battery.update');
        Route::delete('/input-battery/{id}', [BatteryController::class, 'destroyInputBattery'])->name('input.input-battery.destroy');
        Route::get('/machine-breakdown', [MachineBreakdownController::class, 'inputIndex'])->name('input.machine-breakdown');
        Route::post('/machine-breakdown', [MachineBreakdownController::class, 'inputStore'])->name('input.machine-breakdown.store');
        Route::delete('/machine-breakdown/{id}', [MachineBreakdownController::class, 'inputDestroy'])->name('input.machine-breakdown.destroy');
    });

    // Master Data
    Route::prefix('master-data')->group(function () {
        Route::get('/battery', [BatteryController::class, 'masterBattery'])->name('master-data.battery');
        Route::get('/battery/export', [BatteryController::class, 'exportMasterBattery'])->name('master-data.battery.export');
        Route::post('/battery', [BatteryController::class, 'store'])->name('master-data.battery.store');
    });

    Route::post('/import-excel', [ExcelImportController::class, 'import'])->name('excel.import');

    // Settings / User Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users/store', [UserController::class, 'store'])->name('users.store');
    Route::post('/users/{id}/role', [UserController::class, 'updateRole'])->name('users.updateRole');

    // Role & Hak Akses Management
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('/roles/store', [RoleController::class, 'store'])->name('roles.store');
    Route::post('/roles/{id}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.updatePermissions');
});





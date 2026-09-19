<?php

// Bootstrap the Laravel app and run the BatteryImport for the CSV file
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\BatteryImport, base_path('excel/2. Battery Monitoring.csv'));
    echo "Import finished\n";
} catch (Throwable $e) {
    echo "Import error: " . $e->getMessage() . "\n";
}

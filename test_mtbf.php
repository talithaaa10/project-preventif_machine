<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MachineBreakdown;
use Illuminate\Support\Facades\DB;

$res = MachineBreakdown::selectRaw('status, kpi, count(*) as c, avg(duration) as avg_d')
    ->groupBy('status', 'kpi')
    ->get();

file_put_contents(__DIR__ . '/test_output.json', json_encode($res->toArray(), JSON_PRETTY_PRINT));
echo "OK\n";

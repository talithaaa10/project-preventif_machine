<?php

$host = '127.0.0.1';
$port = 3306;
$db = 'nyoba';
$user = 'root';
$pass = 'root';
$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    echo "DB connection failed: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

$sql = "SELECT YEAR(`date`) as yr, MONTH(`date`) as mo, COUNT(*) as cnt, ROUND(SUM(duration),2) as total_duration, ROUND(AVG(duration),6) as avg_duration FROM machine_breakdowns WHERE LOWER(COALESCE(kpi,'')) LIKE '%mtbf%' OR LOWER(COALESCE(sub_kp,'')) LIKE '%mtbf%' GROUP BY YEAR(`date`), MONTH(`date`) ORDER BY YEAR(`date`), MONTH(`date`);";

try {
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($rows)) {
        echo "No MTBF rows found.\n";
        exit(0);
    }
    echo str_pad('Year',6) . str_pad('Month',7) . str_pad('Count',8) . str_pad('Sum',15) . "Avg\n";
    foreach ($rows as $r) {
        echo str_pad($r['yr'],6) . str_pad($r['mo'],7) . str_pad($r['cnt'],8) . str_pad($r['total_duration'],15) . $r['avg_duration'] . PHP_EOL;
    }
} catch (PDOException $e) {
    echo "Query failed: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

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
$sql = "SELECT id, date, status, kpi, sub_kp, line, duration, target FROM machine_breakdowns WHERE LOWER(COALESCE(status,'')) = 'target' AND (LOWER(COALESCE(kpi,'')) LIKE '%mtbf%' OR LOWER(COALESCE(sub_kp,'')) LIKE '%mtbf%') ORDER BY date, id LIMIT 200;";
try {
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($rows)) {
        echo "No target MTBF rows found.\n";
        exit(0);
    }
    // print header
    echo implode("\t", array_keys($rows[0])) . PHP_EOL;
    foreach ($rows as $r) {
        echo implode("\t", array_map(function($v){ return $v === null ? '' : $v; }, $r)) . PHP_EOL;
    }
} catch (PDOException $e) {
    echo "Query failed: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

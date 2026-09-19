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

echo "Counts of MTBF rows by month (duration IS NOT NULL):\n";
$sql = "SELECT YEAR(date) as yr, MONTH(date) as mo, COUNT(*) as cnt, ROUND(AVG(duration),6) as avg_duration FROM machine_breakdowns WHERE (LOWER(COALESCE(kpi,'')) LIKE '%mtbf%' OR LOWER(COALESCE(sub_kp,'')) LIKE '%mtbf%') AND duration IS NOT NULL GROUP BY YEAR(date), MONTH(date) ORDER BY YEAR(date), MONTH(date);";
try {
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($rows)) {
        echo "No MTBF rows with non-null duration found.\n";
    } else {
        foreach ($rows as $r) {
            echo $r['yr'] . "-" . str_pad($r['mo'],2,' ',STR_PAD_LEFT) . " -> count=" . $r['cnt'] . " avg=" . ($r['avg_duration'] ?? 'NULL') . PHP_EOL;
        }
    }
} catch (PDOException $e) {
    echo "Query failed: " . $e->getMessage() . PHP_EOL;
}

echo "\nSample MTBF rows with duration NOT NULL (limit 20):\n";
$sql2 = "SELECT id, date, status, kpi, sub_kp, line, duration, target FROM machine_breakdowns WHERE (LOWER(COALESCE(kpi,'')) LIKE '%mtbf%' OR LOWER(COALESCE(sub_kp,'')) LIKE '%mtbf%') AND duration IS NOT NULL ORDER BY date, id LIMIT 20;";
try {
    $stmt2 = $pdo->query($sql2);
    $rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    if (empty($rows2)) {
        echo "No sample rows.\n";
    } else {
        echo implode("\t", array_keys($rows2[0])) . PHP_EOL;
        foreach ($rows2 as $r) {
            echo implode("\t", array_map(function($v){ return $v===null? '': $v; }, $r)) . PHP_EOL;
        }
    }
} catch (PDOException $e) {
    echo "Sample query failed: " . $e->getMessage() . PHP_EOL;
}

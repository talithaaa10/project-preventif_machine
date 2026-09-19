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

$condition = "LOWER(COALESCE(status,'')) = 'target' AND (LOWER(COALESCE(kpi,'')) LIKE '%mtbf%' OR LOWER(COALESCE(sub_kp,'')) LIKE '%mtbf%')";

// count rows to backup
try {
    $countStmt = $pdo->query("SELECT COUNT(*) FROM machine_breakdowns WHERE $condition");
    $count = (int) $countStmt->fetchColumn();
    if ($count === 0) {
        echo "No target MTBF rows to backup/delete.\n";
        exit(0);
    }
} catch (PDOException $e) {
    echo "Count query failed: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

$backupName = 'machine_breakdowns_backup_target_mtbf_' . date('Ymd_His');

try {
    // create backup table
    $pdo->exec("CREATE TABLE `{$backupName}` AS SELECT * FROM machine_breakdowns WHERE {$condition}");
    echo "Backup table created: {$backupName} (rows: {$count})\n";

    // delete rows from original
    $deleted = $pdo->exec("DELETE FROM machine_breakdowns WHERE {$condition}");
    echo "Rows deleted from machine_breakdowns: {$deleted}\n";
} catch (PDOException $e) {
    echo "Backup/delete failed: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

// run aggregate script to show new monthly averages
echo "\nRunning mtbf_agg.php to show monthly aggregates after delete...\n\n";
passthru('php "scripts/mtbf_agg.php"', $ret);
if ($ret !== 0) {
    echo "mtbf_agg.php returned non-zero status: {$ret}\n";
}

echo "\nDone. Backup name: {$backupName}\n";

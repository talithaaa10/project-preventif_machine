<?php
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
echo 'DB_PASSWORD_RAW:' . getenv('DB_PASSWORD') . PHP_EOL;

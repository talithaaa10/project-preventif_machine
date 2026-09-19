<?php
$content = file_get_contents(__DIR__.'/ .env');
if ($content === false) {
	echo "failed to read .env\n";
	exit(1);
}
$lines = preg_split('/\r?\n/', $content);
foreach ($lines as $i => $line) {
	$hex = bin2hex($line);
	printf("%03d: %s | %s\n", $i+1, $hex, $line);
}

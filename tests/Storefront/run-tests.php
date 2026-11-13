<?php
$test = require __DIR__ . '/environment.php';
passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($test['root'] . '/vendor/bin/phpunit'), $status);
exit($status);

<?php

$test = require __DIR__.'/environment.php';
// Unit/feature runs need a fresh limiter store; the HTTP harness deliberately uses durable file limits.
putenv('STOREFRONT_RATE_LIMIT_STORE=array');
$_ENV['STOREFRONT_RATE_LIMIT_STORE'] = $_SERVER['STOREFRONT_RATE_LIMIT_STORE'] = 'array';
passthru(escapeshellarg(PHP_BINARY).' '.escapeshellarg($test['root'].'/vendor/bin/phpunit').' '.implode(' ', array_map('escapeshellarg', array_slice($argv, 1))), $status);
exit($status);

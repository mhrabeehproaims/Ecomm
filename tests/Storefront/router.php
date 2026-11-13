<?php
$test = require __DIR__ . '/environment.php';
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path === '/__storefront-test/identity') {
    header('Content-Type: application/json');
    echo json_encode(['environment' => 'isolated-storefront-test', 'database' => 'isolated.sqlite', 'payments' => $testKey === '' ? 'disabled' : 'test-only']);
    return;
}
if ($path === '/storage/storefront-test/sample.png') {
    header('Content-Type: image/png');
    readfile($test['root'] . '/public/assets/logo/vantara_logo.png');
    return;
}
require $test['root'] . '/public/index.php';

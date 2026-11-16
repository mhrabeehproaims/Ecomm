<?php
$test = require __DIR__ . '/environment.php';
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (getenv('STOREFRONT_PERF_LOG') === 'true') {
    file_put_contents($test['directory'].'/http-requests.ndjson', json_encode(['method' => $_SERVER['REQUEST_METHOD'], 'path' => $path]).PHP_EOL, FILE_APPEND | LOCK_EX);
}
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
if (preg_match('#^/storage/Products/([a-f0-9-]+\.png)$#D', $path, $match)) {
    $file = $test['directory'].'/public/Products/'.$match[1];
    if (is_file($file)) {
        header('Content-Type: image/png');
        readfile($file);
        return;
    }
}
require $test['root'] . '/public/index.php';

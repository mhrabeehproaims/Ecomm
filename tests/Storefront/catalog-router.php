<?php

if (! str_starts_with(getenv('STOREFRONT_TEST_RUN') ?: '', 'catalog-')) {
    throw new RuntimeException('A dedicated catalog-* test run is required.');
}
$test = require __DIR__.'/environment.php';
require $test['root'].'/vendor/autoload.php';
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
if ($path === '/__storefront-test/identity') {
    header('Content-Type: application/json');
    echo json_encode(['environment' => 'isolated-storefront-test', 'payments' => 'disabled']);

    return;
}
if (preg_match('~^/storage/Products/([a-zA-Z0-9-]+\.(png|jpg|jpeg|webp))$~D', $path, $matches)) {
    $file = $test['directory'].'/public/Products/'.$matches[1];
    if (is_file($file)) {
        header('Content-Type: '.mime_content_type($file));
        readfile($file);

        return;
    }
}
$app = require $test['root'].'/bootstrap/app.php';
$app->instance('request', Illuminate\Http\Request::capture());
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
config(['storefront.test_checkout' => false, 'storefront.cod_enabled' => false,
    'app.url' => 'http://127.0.0.1:8140', 'filesystems.disks.public.root' => $test['directory'].'/public']);
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);

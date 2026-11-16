<?php

if (! str_starts_with(getenv('STOREFRONT_TEST_RUN') ?: '', 'admin-cart-')) {
    throw new RuntimeException('A dedicated admin-cart-* test run is required.');
}
$test = require __DIR__.'/environment.php';
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
if ($path === '/__storefront-test/identity') {
    header('Content-Type: application/json');
    echo json_encode(['environment' => 'isolated-admin-cart-test', 'database' => 'isolated.sqlite', 'payments' => 'disabled']);
    return;
}
// The PHP server's document root is public; only existing static assets bypass Laravel.
if (str_starts_with($path, '/assets/') && ! str_contains($path, '..') && is_file($test['root'].'/public'.$path)) {
    return false;
}
if ($path === '/storage/storefront-test/sample.png') {
    header('Content-Type: image/png');
    readfile($test['root'].'/public/assets/logo/vantara_logo.png');
    return;
}
require $test['root'].'/vendor/autoload.php';
$app = require $test['root'].'/bootstrap/app.php';
$app->instance('request', Illuminate\Http\Request::capture());
if (! is_dir($test['directory'].'/sessions')) mkdir($test['directory'].'/sessions', 0777, true);
// Configure sessions before route registration resolves the web guard/session store.
$app->afterBootstrapping(Illuminate\Foundation\Bootstrap\LoadConfiguration::class, function ($app) use ($test) {
    $app['config']->set(['app.url' => 'http://127.0.0.1:8142', 'session.driver' => 'file', 'session.files' => $test['directory'].'/sessions',
        'session.cookie' => 'isolated_admin_cart_session', 'session.domain' => null, 'session.secure' => false,
        'storefront.test_checkout' => false, 'storefront.cod_enabled' => false, 'broadcasting.connections.pusher.key' => null]);
});
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== $test['database']) {
    throw new RuntimeException('Isolated database required.');
}
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);

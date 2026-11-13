<?php
$test = require __DIR__ . '/environment.php';
require $test['root'] . '/vendor/autoload.php';
if (file_exists($test['database'])) {
    fwrite(STDERR, "The isolated test database already exists. Use a new test run directory or explicitly remove that exact test file after stopping its server.\n");
    exit(1);
}
touch($test['database']);
$app = require $test['root'] . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== $test['database']) {
    throw new RuntimeException('Isolation check failed; refusing database setup.');
}
$status = Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
echo Illuminate\Support\Facades\Artisan::output();
exit($status);

<?php

$test = require __DIR__.($argv[1] ?? '' ? '/mysql-environment.php' : '/environment.php');
require $test['root'].'/vendor/autoload.php';
$app = require $test['root'].'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db = config('database.default');
if ($db === 'sqlite' && config('database.connections.sqlite.database') !== $test['database']) {
    throw new RuntimeException('Refusing non-test database.');
}
if ($db !== 'sqlite' && ! ($db === 'mysql' && config('database.connections.mysql.database') === $databaseName && (int) config('database.connections.mysql.port') === 3339)) {
    throw new RuntimeException('Refusing non-test database.');
}
$status = Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
echo Illuminate\Support\Facades\Artisan::output();
exit($status);

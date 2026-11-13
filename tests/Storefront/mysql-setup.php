<?php

$test = require __DIR__.'/mysql-environment.php';
if ($probe->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$databaseName'")->fetchColumn()) {
    throw new RuntimeException('The isolated MySQL database already exists; refusing to overwrite it.');
}
$probe->exec("CREATE DATABASE `$databaseName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
require $test['root'].'/vendor/autoload.php';
$app = require $test['root'].'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (config('database.default') !== 'mysql' || config('database.connections.mysql.database') !== $databaseName || (int) config('database.connections.mysql.port') !== 3339) {
    throw new RuntimeException('Isolation check failed.');
}
$status = Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
echo Illuminate\Support\Facades\Artisan::output();
exit($status);

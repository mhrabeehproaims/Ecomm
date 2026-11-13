<?php

// Connect only to the separately initialized, task-owned MySQL datadir on port 3339.
$test = require __DIR__.'/environment.php';
$probe = new PDO('mysql:host=127.0.0.1;port=3339;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$actual = strtolower(str_replace('\\', '/', $probe->query('SELECT @@datadir')->fetchColumn()));
$expected = strtolower(str_replace('\\', '/', $test['root'].'/storage/framework/testing/mysql-checkout-20261113/'));
if (rtrim($actual, '/') !== rtrim($expected, '/')) {
    throw new RuntimeException('Refusing a MySQL server outside the isolated test datadir.');
}
$databaseName = getenv('STOREFRONT_MYSQL_TEST_DATABASE') ?: 'vanthara_checkout_test';
if (! preg_match('/^vanthara_checkout_test(?:_\d{14})?$/D', $databaseName)) {
    throw new RuntimeException('Invalid isolated database name.');
}
foreach (['DB_CONNECTION' => 'mysql', 'DB_HOST' => '127.0.0.1', 'DB_PORT' => '3339', 'DB_DATABASE' => $databaseName,
    'DB_USERNAME' => 'root', 'DB_PASSWORD' => '', 'DB_URL' => '', 'STOREFRONT_RAZORPAY_TEST_KEY' => 'rzp_test_fixture',
    'STOREFRONT_RAZORPAY_TEST_SECRET' => 'mock-only-secret-not-provider-credentials'] as $key => $value) {
    putenv("$key=$value");
    $_ENV[$key] = $_SERVER[$key] = $value;
}

return $test;

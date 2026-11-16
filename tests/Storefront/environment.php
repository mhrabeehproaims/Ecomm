<?php

// Test-only bootstrap. This never reads or writes the configured application database.
$root = dirname(__DIR__, 2);
$run = getenv('STOREFRONT_TEST_RUN') ?: 'v2';
if (! preg_match('/^[a-zA-Z0-9_-]{1,40}$/D', $run)) {
    throw new RuntimeException('Invalid isolated test run label.');
}
$directory = $root.'/storage/framework/testing/vanthara-checkout-'.$run;
if (! is_dir($directory)) {
    mkdir($directory, 0777, true);
}
$database = $directory.'/isolated.sqlite';
$testKey = getenv('STOREFRONT_RAZORPAY_TEST_KEY') ?: '';
$testQueue = getenv('STOREFRONT_TEST_QUEUE_CONNECTION') ?: 'sync';
if (! in_array($testQueue, ['sync', 'database'], true)) {
    throw new RuntimeException('Only sync or the isolated database queue is allowed.');
}
if ($testKey !== '' && ! str_starts_with($testKey, 'rzp_test_')) {
    throw new RuntimeException('Only Razorpay test keys are allowed in this harness.');
}
foreach ([
    'APP_ENV' => 'testing', 'APP_DEBUG' => 'false',
    'APP_CONFIG_CACHE' => $directory.'/unused-config.php',
    'APP_KEY' => 'base64:'.base64_encode(hash('sha256', 'storefront-isolated-test-key-not-for-production', true)),
    'APP_URL' => 'http://127.0.0.1:8138',
    'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $database, 'DB_URL' => '',
    'DB_CACHE_CONNECTION' => 'sqlite', 'DB_CACHE_LOCK_CONNECTION' => 'sqlite',
    'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array',
    'QUEUE_CONNECTION' => $testQueue, 'MAIL_MAILER' => 'array',
    'BROADCAST_CONNECTION' => 'log', 'LOG_CHANNEL' => 'null',
    'RAZORPAY_KEY' => $testKey, 'RAZORPAY_SECRET' => getenv('STOREFRONT_RAZORPAY_TEST_SECRET') ?: '',
    'STOREFRONT_RAZORPAY_TEST_KEY' => $testKey,
    'STOREFRONT_RAZORPAY_TEST_SECRET' => getenv('STOREFRONT_RAZORPAY_TEST_SECRET') ?: '',
    'STOREFRONT_RAZORPAY_WEBHOOK_SECRET' => getenv('STOREFRONT_RAZORPAY_WEBHOOK_SECRET') ?: '',
    'STOREFRONT_TEST_CHECKOUT' => 'true', 'STOREFRONT_TEST_COD' => 'true',
    'STOREFRONT_TEST_SHIPPING_MINOR' => '{"kerala (KL)":0,"tamil_nadu (TN)":5000}',
    'STOREFRONT_TEST_SHIPPING_TAX_BPS' => '0',
    'STOREFRONT_RATE_LIMIT_PATH' => $directory.'/limits',
    'STOREFRONT_DOCUMENT_ROOT' => $directory.'/documents',
] as $key => $value) {
    putenv("$key=$value");
    $_ENV[$key] = $_SERVER[$key] = $value;
}

return ['root' => $root, 'database' => $database, 'directory' => $directory];

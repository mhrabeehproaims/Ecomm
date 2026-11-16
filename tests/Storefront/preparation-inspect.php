<?php

// Local read-only configuration metadata. Never prints keys, hashes or credential values.
require dirname(__DIR__, 2).'/vendor/autoload.php';
$root = dirname(__DIR__, 2);
try {
    $env = Dotenv\Dotenv::parse(file_get_contents($root.'/.env'));
    $cache = is_file($root.'/bootstrap/cache/config.php') ? require $root.'/bootstrap/cache/config.php' : [];
    $configured = fn (string $name) => isset($env[$name]) && trim((string) $env[$name]) !== '';
    $history = [];
    foreach (['0dda0390383e', '0ca10169687d'] as $commit) {
        $process = proc_open(['git', 'show', $commit.':.env'], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $root, null, ['bypass_shell' => true]);
        if (! is_resource($process)) {
            throw new RuntimeException('Cannot inspect recorded Git exposure.');
        }
        fclose($pipes[0]);
        $contents = stream_get_contents($pipes[1]);
        stream_get_contents($pipes[2]); // Suppress raw source/diagnostics from the report.
        fclose($pipes[1]);
        fclose($pipes[2]);
        $status = proc_close($process);
        if ($status !== 0) {
            $history[] = ['commit' => $commit, 'available' => false];

            continue;
        }
        $historical = Dotenv\Dotenv::parse($contents);
        $key = $historical['APP_KEY'] ?? '';
        $history[] = ['commit' => $commit, 'available' => true, 'nonempty_key_in_history' => $key !== '',
            'current_local_key_matches' => $key !== '' && isset($env['APP_KEY']) && hash_equals($key, $env['APP_KEY'])];
    }
    echo json_encode([
        'scope' => 'Local configuration only; no deployed environment or application database inspected.',
        'local_environment' => $env['APP_ENV'] ?? null,
        'local_app_url_host' => parse_url($env['APP_URL'] ?? '', PHP_URL_HOST),
        'config_cache_exists' => (bool) $cache,
        'cached_environment' => $cache['app']['env'] ?? null,
        'cached_app_key_matches_local_env' => isset($cache['app']['key'], $env['APP_KEY']) && hash_equals($cache['app']['key'], $env['APP_KEY']),
        'cached_session_data_encrypted' => $cache['session']['encrypt'] ?? null,
        'razorpay_test_key_present' => $configured('STOREFRONT_RAZORPAY_TEST_KEY'),
        'razorpay_test_secret_present' => $configured('STOREFRONT_RAZORPAY_TEST_SECRET'),
        'webhook_secret_present' => $configured('STOREFRONT_RAZORPAY_WEBHOOK_SECRET'),
        'legacy_razorpay_key_present' => $configured('RAZORPAY_KEY'),
        'local_checkout_flag' => filter_var($env['STOREFRONT_TEST_CHECKOUT'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'deployed_key_reuse' => 'unknown; requires an authorized host-side comparison',
        'recorded_history_comparison' => $history,
    ], JSON_PRETTY_PRINT).PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, 'Preparation inspection failed ('.get_class($error).'); no secret values emitted.'.PHP_EOL);
    exit(1);
}

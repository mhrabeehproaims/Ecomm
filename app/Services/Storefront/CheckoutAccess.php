<?php

namespace App\Services\Storefront;

final class CheckoutAccess
{
    public static function enabled(): bool
    {
        if (! app()->environment(['local', 'testing']) || config('storefront.test_checkout') !== true) {
            return false;
        }
        $driver = config('database.default');
        if ($driver === 'sqlite') {
            $database = config('database.connections.sqlite.database');
            if ($database === ':memory:') {
                return app()->environment('testing');
            }
            $root = realpath(storage_path('framework/testing'));
            $path = realpath($database ?? '');

            return $root && $path && str_starts_with(strtolower($path), strtolower($root.DIRECTORY_SEPARATOR));
        }

        return $driver === 'mysql' && in_array(config('database.connections.mysql.host'), ['127.0.0.1', 'localhost'], true)
            && (int) config('database.connections.mysql.port') === 3339
            && preg_match('/^vanthara_checkout_test(?:_\d{14})?$/D', config('database.connections.mysql.database', '')) === 1;
    }

    public static function assertEnabled(): void
    {
        abort_unless(self::enabled(), 503, 'Checkout and payments are disabled. Production activation has not been approved.');
    }

    public static function assertGateway(): void
    {
        self::assertEnabled();
        abort_unless(str_starts_with(config('storefront.razorpay_key', ''), 'rzp_test_') && strlen(config('storefront.razorpay_secret', '')) >= 8,
            503, 'Razorpay test credentials are not configured. No payment was initiated.');
    }
}

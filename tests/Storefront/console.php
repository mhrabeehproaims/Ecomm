<?php

$test = require __DIR__.'/environment.php';
require $test['root'].'/vendor/autoload.php';
$allowed = ['storefront:expire-reservations', 'storefront:dispatch-events', 'storefront:reconcile-payment', 'storefront:refund-return', 'storefront:recover-refund', 'storefront:financial-maintenance', 'storefront:financial-status', 'storefront:archive-documents', 'storefront:refund-late-capture', 'storefront:recover-refund-intent', 'queue:work', 'queue:failed', 'schedule:run', 'schedule:list'];
if (! in_array($argv[1] ?? '', $allowed, true)) {
    throw new RuntimeException('Only isolated storefront operations are allowed by this wrapper.');
}
$app = require $test['root'].'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
App\Services\Storefront\CheckoutAccess::assertEnabled();
exit($kernel->handle(new Symfony\Component\Console\Input\ArgvInput($argv), new Symfony\Component\Console\Output\ConsoleOutput));

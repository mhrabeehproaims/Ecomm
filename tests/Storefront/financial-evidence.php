<?php

// Read-only evidence from disposable test databases; never calls a provider or marks settlement.
$test = require __DIR__.(getenv('STOREFRONT_MYSQL_TEST_DATABASE') ? '/mysql-environment.php' : '/environment.php');
require $test['root'].'/vendor/autoload.php';
if (! getenv('STOREFRONT_MYSQL_TEST_DATABASE') && ! is_file($test['database'])) {
    throw new RuntimeException('An existing isolated sample database is required. Nothing was created.');
}
$app = require $test['root'].'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
App\Services\Storefront\CheckoutAccess::assertEnabled();

use Illuminate\Support\Facades\DB;

$sqlite = config('database.default') === 'sqlite';
$before = $sqlite ? hash_file('sha256', $test['database']) : null;
DB::statement($sqlite ? 'PRAGMA query_only = ON' : 'SET SESSION TRANSACTION READ ONLY');
$result = DB::transaction(function () {
    $orders = DB::table('orders as o')->join('storefront_checkouts as c', 'c.order_id', '=', 'o.order_id')
        ->select('o.order_id', 'o.payment_method', 'o.payment_status', 'o.total_amount', 'c.breakdown', 'c.state')->orderBy('o.order_id')->limit(1001)->get();
    if ($orders->count() > 1000) {
        throw new RuntimeException('This local evidence helper is limited to1000 sample orders; no partial report was accepted.');
    }
    $rows = [];
    foreach ($orders as $order) {
        $entries = DB::table('storefront_financial_entries')->where('order_id', $order->order_id)->get();
        $sum = fn (string $kind) => (int) $entries->where('kind', $kind)->sum('amount_minor');
        $attempt = DB::table('storefront_payment_attempts')->where('order_id', $order->order_id)->first();
        $observedRefunds = (int) DB::table('storefront_provider_refunds')->where('order_id', $order->order_id)->where('state', 'processed')->sum('amount_minor');
        $due = json_decode($order->breakdown, true, flags: JSON_THROW_ON_ERROR)['total_minor'];
        $issues = [];
        if (App\Services\Storefront\Money::minor($order->total_amount) !== $due) {
            $issues[] = 'order_quote_total_mismatch';
        }
        if ($attempt && (int) $attempt->amount_minor !== $due) {
            $issues[] = 'payment_attempt_total_mismatch';
        }
        if ($attempt?->state === 'captured' && $sum('capture') !== (int) $attempt->amount_minor) {
            $issues[] = 'capture_ledger_evidence_missing_or_mismatched';
        }
        if ($sum('refund') !== $observedRefunds || ($attempt && (int) $attempt->refunded_minor !== $observedRefunds)) {
            $issues[] = 'refund_records_do_not_balance';
        }
        if ($sum('refund') > $sum('capture')) {
            $issues[] = 'refund_exceeds_recorded_capture';
        }
        if ($sum('cod_collection') > $due || $sum('cod_remittance') > $sum('cod_collection')) {
            $issues[] = 'cod_receipts_exceed_balance';
        }
        if ($order->payment_method === 'cod' && $order->payment_status === 'paid' && $sum('cod_collection') !== $due) {
            $issues[] = 'cod_paid_status_lacks_collection_evidence';
        }
        if ($order->payment_method === 'online' && in_array($order->payment_status, ['paid', 'refunded', 'partially_refunded']) && $attempt?->state !== 'captured') {
            $issues[] = 'paid_status_lacks_capture_evidence';
        }
        $rows[] = ['order_id' => (int) $order->order_id, 'checkout_state' => $order->state, 'payment_status' => $order->payment_status,
            'total_minor' => $due, 'capture_minor' => $sum('capture'), 'refund_minor' => $sum('refund'), 'cod_collected_minor' => $sum('cod_collection'),
            'cod_remitted_minor' => $sum('cod_remittance'), 'cod_unremitted_minor' => $sum('cod_collection') - $sum('cod_remittance'), 'issues' => $issues];
    }

    return ['scope' => 'Isolated application evidence only; not a provider report, bank feed or bank statement.', 'currency' => 'INR',
        'order_scope' => 'Orders with a storefront_checkouts snapshot only; legacy evidence needs separate review.',
        'legacy_orders_excluded' => DB::table('orders')->whereNotIn('order_id', DB::table('storefront_checkouts')->select('order_id'))->count(),
        'bank_settlement_verified' => false, 'records_updated' => 0, 'provider_requests' => 0, 'orders_reviewed' => count($rows),
        'orders_requiring_evidence_review' => count(array_filter($rows, fn ($row) => count($row['issues']) > 0)), 'orders' => $rows];
});
if ($sqlite && ! hash_equals($before, hash_file('sha256', $test['database']))) {
    throw new RuntimeException('Sample database hash changed during inspection; review concurrent test activity.');
}
$result['sqlite_file_hash_unchanged'] = $sqlite ? true : null;
echo json_encode($result, JSON_PRETTY_PRINT).PHP_EOL;

<?php

$test = require __DIR__.'/environment.php';
require $test['root'].'/vendor/autoload.php';
$app = require $test['root'].'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== $test['database']) {
    throw new RuntimeException('Isolation check failed.');
}
use Illuminate\Support\Facades\DB;

$now = now();
$timestamps = ['created_at' => $now, 'updated_at' => $now];
if (! DB::table('products')->exists()) {
    $category = DB::table('categories')->insertGetId(['categ_name' => 'Isolated test collection', 'categ_desc' => 'Sample fixtures only', 'return_days' => 7, 'status' => 1] + $timestamps, 'categ_id');
    $brand = DB::table('brands')->insertGetId(['brand_name' => 'Vanthara TEST', 'status' => 1] + $timestamps, 'brand_id');
    $tax = DB::table('tax_schedules')->insertGetId(['tax_name' => 'TEST zero tax', 'tax_percentage' => 0, 'cgst' => 0, 'sgst' => 0, 'igst' => 0, 'status' => 1] + $timestamps, 'tax_id');
    foreach ([['QA Cotton Bag', 250, 20], ['QA Wooden Bowl', 400, 10], ['QA Unavailable Item', 100, 0]] as [$name, $price, $stock]) {
        $product = DB::table('products')->insertGetId(['product_name' => $name, 'description' => 'Isolated test sample. Not real inventory.', 'category_id' => $category, 'brand_id' => $brand, 'mrp' => $price, 'retail' => $price, 'special_price' => $price, 'primary_price' => 'retail', 'stock' => $stock, 'minimum_stock' => 0, 'tax_schedule_id' => $tax, 'status' => 1] + $timestamps, 'product_id');
        DB::table('product_images')->insert(['product_id' => $product, 'image_path' => 'storefront-test/sample.png', 'is_primary' => 1] + $timestamps);
    }
    DB::table('coupons')->insert(['code' => 'TESTSAVE', 'coupon_name' => 'Isolated test only', 'type' => 'fixed', 'value' => 25, 'min_order_amount' => 100, 'max_uses' => 100, 'max_uses_per_user' => 20, 'start_date' => now()->subDay()->toDateString(), 'end_date' => now()->addDays(10)->toDateString(), 'is_active' => 1] + $timestamps);
    $customer = DB::table('customers')->insertGetId(['first_name' => 'Fixture', 'last_name' => 'Customer', 'phone' => '9000000011', 'password' => Illuminate\Support\Facades\Hash::make('FixturePassword123!'), 'status' => 1] + $timestamps, 'customer_id');
}
$customer = DB::table('customers')->where('phone', '9000000011')->value('customer_id');
if (! $customer || DB::table('orders')->where('customer_id', $customer)->exists()) {
    throw new RuntimeException('Fixture customer missing or delivered fixture already present.');
}
DB::table('return_policies')->insert(['title' => 'Isolated sample return policy', 'return_days' => 7, 'return_type' => 'refund_or_exchange', 'is_active' => 1] + $timestamps);
$order = DB::table('orders')->insertGetId(['customer_id' => $customer, 'total_amount' => 250, 'discount' => 0, 'total_tax_amount' => 0, 'status' => 'delivered', 'payment_method' => 'cod', 'payment_status' => 'paid', 'paid_at' => $now, 'delivery_state' => 'kerala (KL)'] + $timestamps, 'order_id');
DB::table('order_status_logs')->insert(['order_id' => $order, 'status' => 'delivered', 'changed_at' => $now] + $timestamps);
DB::table('order_items')->insert(['order_id' => $order, 'product_id' => 1, 'quantity' => 1, 'price' => 250, 'actual_price' => 250, 'total' => 250, 'amount_wo_tax' => 250] + $timestamps);
echo json_encode(['database' => 'isolated.sqlite', 'products' => 3, 'fixture_customer' => '9000000011', 'delivered_order' => $order]).PHP_EOL;

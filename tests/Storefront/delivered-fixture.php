<?php

// Creates a disposable delivered order for a return test, only in the isolated database.
$test = require __DIR__.'/environment.php';
require $test['root'].'/vendor/autoload.php';
$app = require $test['root'].'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== $test['database']) {
    throw new RuntimeException('Isolation check failed.');
}
$db = Illuminate\Support\Facades\DB::class;
$stamp = ['created_at' => now(), 'updated_at' => now()];
$phone = '92'.random_int(10000000, 99999999);
$password = 'IsolatedReturnPassword123!';
$customer = $db::table('customers')->insertGetId(['first_name' => 'Return fixture', 'phone' => $phone, 'password' => Illuminate\Support\Facades\Hash::make($password), 'status' => 1] + $stamp, 'customer_id');
$order = $db::table('orders')->insertGetId(['customer_id' => $customer, 'total_amount' => 250, 'discount' => 0, 'total_tax_amount' => 0, 'status' => 'delivered', 'payment_method' => 'cod', 'payment_status' => 'paid', 'paid_at' => now(), 'delivery_state' => 'kerala (KL)'] + $stamp, 'order_id');
$db::table('order_status_logs')->insert(['order_id' => $order, 'status' => 'delivered', 'changed_at' => now()] + $stamp);
$db::table('order_items')->insert(['order_id' => $order, 'product_id' => 1, 'quantity' => 1, 'price' => 250, 'actual_price' => 250, 'total' => 250, 'amount_wo_tax' => 250] + $stamp);
echo json_encode(['phone' => $phone, 'password' => $password, 'order_id' => $order]);

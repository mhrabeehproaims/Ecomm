<?php

namespace Tests\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

trait StorefrontFixtures
{
    protected int $customer;

    protected int $other;

    protected int $product;

    protected int $address;

    protected function fixtures(): void
    {
        $stamp = ['created_at' => now(), 'updated_at' => now()];
        $this->customer = DB::table('customers')->insertGetId(['first_name' => 'Test', 'phone' => '9000000011', 'password' => Hash::make('FixturePassword123!'), 'status' => 1] + $stamp, 'customer_id');
        $this->other = DB::table('customers')->insertGetId(['first_name' => 'Other', 'phone' => '9000000012', 'password' => Hash::make('FixturePassword123!'), 'status' => 1] + $stamp, 'customer_id');
        $category = DB::table('categories')->insertGetId(['categ_name' => 'Test category', 'return_days' => 7, 'status' => 1] + $stamp, 'categ_id');
        $brand = DB::table('brands')->insertGetId(['brand_name' => 'TEST', 'status' => 1] + $stamp, 'brand_id');
        $tax = DB::table('tax_schedules')->insertGetId(['tax_name' => 'TEST 18%', 'tax_percentage' => 18, 'cgst' => 9, 'sgst' => 9, 'igst' => 18, 'status' => 1] + $stamp, 'tax_id');
        $this->product = DB::table('products')->insertGetId(['product_name' => 'Test bag', 'category_id' => $category, 'brand_id' => $brand,
            'tax_schedule_id' => $tax, 'mrp' => 100, 'retail' => 100, 'wholesale' => 90, 'special_price' => 95, 'primary_price' => 'retail', 'stock' => 20, 'status' => 1] + $stamp, 'product_id');
        $this->address = $this->addressFor($this->customer);
        DB::table('return_policies')->insert(['title' => 'TEST seven day policy', 'return_days' => 7, 'return_type' => 'refund_or_exchange', 'is_active' => 1] + $stamp);
        DB::table('coupons')->insert(['code' => 'TESTSAVE', 'type' => 'fixed', 'value' => 10, 'min_order_amount' => 100, 'max_uses' => 10,
            'max_uses_per_user' => 2, 'start_date' => now()->subDay()->toDateString(), 'end_date' => now()->addDay()->toDateString(), 'is_active' => 1] + $stamp);
    }

    protected function addressFor(int $customer): int
    {
        return DB::table('delivery_addresses')->insertGetId(['customer_id' => $customer, 'phone' => '9000000011', 'address_line1' => 'Test lane',
            'city' => 'Test city', 'state' => 'kerala (KL)', 'country' => 'India', 'postal_code' => '673001', 'is_default' => true,
            'created_at' => now(), 'updated_at' => now()], 'address_id');
    }

    protected function bag(int $quantity = 1, ?int $customer = null, ?int $product = null): void
    {
        DB::table('carts')->insert(['user_id' => $customer ?? $this->customer, 'product_id' => $product ?? $this->product, 'quantity' => $quantity,
            'price' => 1, 'created_at' => now(), 'updated_at' => now()]); // Deliberately stale/untrusted cart price.
    }
}

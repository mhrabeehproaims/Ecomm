<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Support\StorefrontFixtures;
use Tests\TestCase;

class StorefrontShoppingStateTest extends TestCase
{
    use StorefrontFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null,
            'cache.default' => 'array', 'cache.limiter' => 'array', 'catalog.revalidate_url' => '', 'storefront.test_checkout' => false]);
        DB::purge('sqlite');
        Artisan::call('migrate', ['--force' => true]);
        Http::preventStrayRequests();
        $this->fixtures();
        $this->bag(2);
        $this->bag(7, $this->other);
        DB::table('wishlists')->insert(['user_id' => $this->customer, 'product_id' => $this->product]);
    }

    private function headers(): array
    {
        return ['Authorization' => 'Bearer '.Customer::findOrFail($this->customer)->createToken('shopping-read-test')->plainTextToken];
    }

    public function test_combined_read_is_private_owned_and_preserves_existing_cart_and_wishlist_contracts(): void
    {
        $headers = $this->headers();
        $before = DB::table('carts')->orderBy('cart_id')->get()->toJson();
        $state = $this->getJson('/api/shopping-state', $headers)->assertOk()->assertJsonPath('status', 'success')
            ->assertJsonPath('customer.customer_id', $this->customer)->assertJsonCount(1, 'cart')->assertJsonCount(1, 'wishlist')
            ->assertJsonPath('cart.0.quantity', 2)->assertJsonPath('wishlist.0.product_name', 'Test bag');
        $this->assertStringContainsString('no-store', $state->headers->get('Cache-Control'));
        $this->assertStringContainsString('Authorization', $state->headers->get('Vary'));
        $this->assertArrayNotHasKey('password', $state->json('customer'));
        $this->assertArrayNotHasKey('remember_token', $state->json('customer'));
        $this->assertSame($this->customer, $state->json('customer.delivery_address.0.customer_id'));
        $this->assertEquals($this->product, decrypt($state->json('cart.0.product.encrypted_product_id')));
        $this->assertEquals($this->product, decrypt($state->json('wishlist.0.product_id')));
        $legacyCart = $this->getJson('/api/cart', $headers)->assertOk();
        $legacyWishlist = $this->getJson('/api/wishlist', $headers)->assertOk();
        $this->assertEquals($legacyCart->json('data.0.price'), $state->json('cart.0.price'));
        $this->assertEquals($legacyWishlist->json('data.0.retail'), $state->json('wishlist.0.retail'));
        $this->assertSame($before, DB::table('carts')->orderBy('cart_id')->get()->toJson());
    }

    public function test_combined_read_rejects_anonymous_and_inactive_accounts(): void
    {
        $this->getJson('/api/shopping-state')->assertUnauthorized();
        $headers = $this->headers();
        DB::table('customers')->where('customer_id', $this->customer)->update(['status' => 0]);
        $this->getJson('/api/shopping-state', $headers)->assertForbidden();
    }

    public function test_reads_are_fresh_after_cart_quantity_and_wishlist_changes(): void
    {
        $headers = $this->headers();
        $this->getJson('/api/shopping-state', $headers)->assertOk()->assertJsonPath('cart.0.quantity', 2);
        $this->postJson('/api/cart/update-count', ['product_id' => encrypt($this->product), 'count' => 3], $headers)->assertOk();
        $this->postJson('/api/wishlist', ['product_id' => encrypt($this->product)], $headers)->assertOk();
        $this->getJson('/api/shopping-state', $headers)->assertOk()->assertJsonPath('cart.0.quantity', 3)->assertJsonCount(0, 'wishlist');
    }
}

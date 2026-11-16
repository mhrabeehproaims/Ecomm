<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminCartItemsTest extends TestCase
{
    private int $firstCustomer;
    private int $secondCustomer;
    private int $firstProduct;
    private int $secondProduct;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null, 'cache.default' => 'array', 'catalog.revalidate_url' => '']);
        DB::purge('sqlite');
        Artisan::call('migrate', ['--force' => true]);
        Http::preventStrayRequests();
        $this->firstCustomer = DB::table('customers')->insertGetId(['first_name' => 'Cart Alice', 'last_name' => 'Shopper', 'phone' => '9000001011', 'password' => Hash::make('IsolatedPassword123!'), 'status' => 1], 'customer_id');
        $this->secondCustomer = DB::table('customers')->insertGetId(['first_name' => 'Cart Bob', 'last_name' => 'Customer', 'phone' => '9000001012', 'password' => Hash::make('IsolatedPassword123!'), 'status' => 1], 'customer_id');
        $category = DB::table('categories')->insertGetId(['categ_name' => 'Test clothes', 'status' => 1], 'categ_id');
        $size = DB::table('sizes')->insertGetId(['size_name' => 'Medium', 'status' => 1], 'size_id');
        $brand = DB::table('brands')->insertGetId(['brand_name' => 'Cart test brand', 'status' => 1], 'brand_id');
        $this->firstProduct = DB::table('products')->insertGetId(['product_name' => 'Admin cart shirt', 'category_id' => $category, 'brand_id' => $brand, 'size_id' => $size, 'mrp' => 1500, 'retail' => 1200, 'primary_price' => 'retail', 'stock' => 7, 'status' => 1], 'product_id');
        $this->secondProduct = DB::table('products')->insertGetId(['product_name' => 'Inactive cart jacket', 'category_id' => $category, 'brand_id' => $brand, 'mrp' => 2500, 'stock' => 0, 'status' => 0], 'product_id');
        $this->cart($this->firstCustomer, $this->firstProduct, 2, 1100);
        $this->cart($this->secondCustomer, $this->firstProduct, 1, 1200);
        $this->cart($this->firstCustomer, $this->secondProduct, 3, 2000);
    }

    private function cart(?int $customer, ?int $product, int $quantity, int $price): void
    {
        DB::table('carts')->insert(['user_id' => $customer, 'product_id' => $product, 'quantity' => $quantity, 'price' => $price, 'created_at' => now(), 'updated_at' => now()]);
    }

    private function admin(): void
    {
        $this->actingAs(User::factory()->create());
    }

    public function test_cart_records_require_an_admin_web_session(): void
    {
        $this->get('/carts')->assertRedirect(route('login.index'));
        $this->post('/get_carts_byCustomer')->assertRedirect(route('login.index'));
        $token = Customer::findOrFail($this->firstCustomer)->createToken('isolated-cart-test')->plainTextToken;
        $this->withToken($token)->get('/carts')->assertRedirect(route('login.index'));
    }

    public function test_listing_shows_real_cart_quantities_customers_sizes_and_saved_prices_without_writes(): void
    {
        $this->admin();
        $before = DB::table('carts')->orderBy('cart_id')->get()->toJson();
        $response = $this->get('/carts')->assertOk()->assertViewIs('Cart.carts')
            ->assertSee('Cart Alice Shopper')->assertSee('Cart Bob Customer')->assertSee('9000001011')
            ->assertSee('Size: Medium')->assertSee('₹1,100.00')->assertSee('Inactive cart jacket')->assertSee('Inactive')
            ->assertSee('Cart Items')->assertSee('Wishlisted Items')->assertSee('Saved unit price');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertSame(3, $response->viewData('cart_products')->total());
        $row = $response->viewData('cart_products')->getCollection()->first(fn ($cart) => $cart->user_id === $this->firstCustomer && $cart->product_id === $this->firstProduct);
        $this->assertSame(2, $row->quantity);
        $this->assertEquals(1100, $row->price);
        $this->assertSame($before, DB::table('carts')->orderBy('cart_id')->get()->toJson());
    }

    public function test_customer_product_combined_and_empty_filters_use_the_same_listing(): void
    {
        $this->admin();
        foreach ([['customer' => $this->firstCustomer], ['product' => $this->firstProduct], ['customer' => $this->firstCustomer, 'product' => $this->firstProduct], []] as $filters) {
            $rows = $this->get('/carts?'.http_build_query($filters))->assertOk()->assertViewIs('Cart.carts')->viewData('cart_products');
            $this->assertSame(count($filters) === 2 ? 1 : ($filters ? 2 : 3), $rows->total());
            foreach ($rows as $row) {
                if (isset($filters['customer'])) $this->assertSame($this->firstCustomer, $row->user_id);
                if (isset($filters['product'])) $this->assertSame($this->firstProduct, $row->product_id);
            }
            $this->post('/get_carts_byCustomer', $filters)->assertRedirect(route('web.carts.index', $filters));
        }
        $this->get('/carts?'.http_build_query(['customer' => $this->secondCustomer, 'product' => $this->secondProduct]))
            ->assertOk()->assertSee('No cart items found for these filters.');
        $this->getJson('/carts?customer=999999')->assertUnprocessable();
        $this->getJson('/carts?product=invalid')->assertUnprocessable();
    }

    public function test_storefront_cart_api_changes_are_reflected_immediately_in_the_admin_listing(): void
    {
        $token = Customer::findOrFail($this->firstCustomer)->createToken('isolated-cart-sync')->plainTextToken;
        $encryptedId = encrypt($this->firstProduct);
        $this->postJson('/api/cart', ['product_id' => $encryptedId, 'quantity' => 1], ['Authorization' => 'Bearer '.$token])->assertCreated();
        $this->admin();
        $url = '/carts?'.http_build_query(['customer' => $this->firstCustomer, 'product' => $this->firstProduct]);
        $rows = $this->get($url)->assertOk()->viewData('cart_products');
        $this->assertSame(1, $rows->total());
        $this->assertSame(3, $rows->first()->quantity);
        $this->assertEquals(1200, $rows->first()->price);
    }

    public function test_empty_guest_and_missing_product_rows_are_safe_and_pagination_preserves_filters(): void
    {
        $this->admin();
        $this->cart(null, null, 1, 25);
        $this->get('/carts')->assertOk()->assertSee('Guest / customer unavailable')->assertSee('Product unavailable');
        for ($i = 0; $i < 26; $i++) $this->cart($this->firstCustomer, $this->firstProduct, 1, 1100);
        $response = $this->get('/carts?customer='.$this->firstCustomer)->assertOk();
        $this->assertSame(25, $response->viewData('cart_products')->count());
        $this->assertStringContainsString('customer='.$this->firstCustomer, $response->viewData('cart_products')->nextPageUrl());
        DB::table('carts')->delete(); // Only this test's explicitly isolated in-memory database.
        $this->get('/carts')->assertOk()->assertSee('No cart items found.');
    }
}

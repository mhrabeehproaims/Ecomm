<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Services\Storefront\CatalogInvalidation;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StorefrontCatalogTest extends TestCase
{
    private array $data;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null,
            'cache.default' => 'array', 'catalog.revalidate_url' => '', 'storefront.test_checkout' => false]);
        DB::purge('sqlite');
        Artisan::call('migrate', ['--force' => true]);
        Storage::fake('public');
        Http::preventStrayRequests();
        $category = DB::table('categories')->insertGetId(['categ_name' => 'Admin category', 'status' => 1], 'categ_id');
        $brand = DB::table('brands')->insertGetId(['brand_name' => 'Admin brand', 'status' => 1], 'brand_id');
        $this->data = ['product_name' => 'Admin Catalog Product', 'product_desc' => 'Real admin request', 'health_benefit' => '',
            'category' => $category, 'product_brand' => $brand, 'product_mrp' => 1500, 'special_price' => 1150,
            'retail_price' => 1200, 'primary_price' => 'retail', 'product_stock' => 7];
        $this->actingAs(User::factory()->create());
    }

    private function createProduct(): Product
    {
        $this->postJson('/products/store', $this->data)->assertRedirect();

        return Product::firstOrFail();
    }

    public function test_admin_create_update_and_deactivate_flow_matches_public_api(): void
    {
        $product = $this->createProduct();
        $this->assertSame(1, (int) $product->status);
        $list = $this->getJson('/api/api-products')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.product_name', $this->data['product_name'])->json('data.0');
        $this->assertEquals($product->product_id, decrypt($list['product_id']));
        $this->assertEquals(1200, $list['retail']);
        $this->getJson('/api/api-products/'.rawurlencode($list['product_id']))->assertOk()->assertJsonPath('0.product_name', $this->data['product_name']);
        $this->putJson('/products/update/'.$product->product_id, array_replace($this->data, ['product_name' => 'Updated Admin Product', 'retail_price' => 1100, 'product_stock' => 0]))->assertRedirect();
        $this->getJson('/api/api-products?keyword=Updated')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.stock', 0);
        $this->getJson('/products/'.$product->product_id.'/updateStatus')->assertOk()->assertJsonPath('status', false);
        $this->getJson('/api/api-products')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/api-products/'.rawurlencode($list['product_id']))->assertNotFound();
    }

    public function test_unavailable_stock_and_missing_image_do_not_hide_active_products(): void
    {
        $this->data['product_stock'] = 0;
        $this->createProduct();
        $this->getJson('/api/api-products?category_name=Admin%20category')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.primary_image', null);
        $this->getJson('/api/api-products?category_name=Unknown')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/api-products/not-a-ciphertext')->assertNotFound();
    }

    public function test_inactive_products_are_removed_from_featured_and_random_endpoints(): void
    {
        $product = $this->createProduct();
        $this->postJson('/featured-products', ['selected_products' => [$product->product_id]])->assertOk();
        $this->getJson('/api/featured-prods')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/products/'.$product->product_id.'/updateStatus')->assertOk();
        $this->getJson('/api/featured-prods')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/random_products')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_cache_notification_waits_for_commit_and_coalesces_admin_changes(): void
    {
        config(['catalog.revalidate_url' => 'http://storefront.test/api/catalog/revalidate', 'catalog.revalidate_secret' => str_repeat('a', 32)]);
        Http::fake(['*' => Http::response(['revalidated' => true])]);
        DB::beginTransaction();
        $product = $this->createProduct();
        $service = app(CatalogInvalidation::class);
        $service->flush();
        Http::assertNothingSent();
        DB::commit();
        $this->putJson('/products/update/'.$product->product_id, array_replace($this->data, ['retail_price' => 1100]))->assertRedirect();
        $service->flush();
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer '.str_repeat('a', 32)));
        $this->getJson('/products/'.$product->product_id.'/updateStatus')->assertOk();
        $service->flush();
        Http::assertSentCount(2);
    }

    public function test_rollback_does_not_invalidate_and_failed_notification_does_not_fail_admin_save(): void
    {
        config(['catalog.revalidate_url' => 'http://storefront.test/api/catalog/revalidate', 'catalog.revalidate_secret' => str_repeat('b', 32)]);
        Http::fake(['*' => Http::response([], 503)]);
        DB::beginTransaction();
        app(CatalogInvalidation::class)->changed();
        DB::rollBack();
        app(CatalogInvalidation::class)->flush();
        Http::assertNothingSent();
        $this->createProduct();
        app(CatalogInvalidation::class)->flush();
        $this->assertDatabaseCount('products', 1);
        Http::assertSentCount(1);
    }

    public function test_rolled_back_catalog_changes_do_not_invalidate(): void
    {
        config(['catalog.revalidate_url' => 'http://storefront.test/api/catalog/revalidate', 'catalog.revalidate_secret' => str_repeat('a', 32)]);
        Http::fake();
        DB::beginTransaction();
        $this->createProduct();
        DB::rollBack();
        app(CatalogInvalidation::class)->flush();
        Http::assertNothingSent();
        $this->assertDatabaseCount('products', 0);
    }

    public function test_failed_invalidation_does_not_undo_a_successful_admin_save(): void
    {
        config(['catalog.revalidate_url' => 'http://storefront.test/api/catalog/revalidate', 'catalog.revalidate_secret' => str_repeat('a', 32)]);
        Http::fake(['*' => Http::response([], 503)]);
        $this->createProduct();
        app(CatalogInvalidation::class)->flush();
        Http::assertSentCount(1);
        $this->getJson('/api/api-products')->assertOk()->assertJsonCount(1, 'data');
    }
}

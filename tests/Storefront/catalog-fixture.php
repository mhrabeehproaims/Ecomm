<?php

// CLI-only: reproduce authenticated Blade admin requests in a separate SQLite database.
if (PHP_SAPI !== 'cli' || ! str_starts_with(getenv('STOREFRONT_TEST_RUN') ?: '', 'catalog-')) {
    throw new RuntimeException('A dedicated catalog-* test run is required.');
}
$test = require __DIR__.'/environment.php';
require $test['root'].'/vendor/autoload.php';
$app = require $test['root'].'/bootstrap/app.php';
$app->instance('request', Illuminate\Http\Request::create('/'));
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== $test['database'] || ! is_file($test['database'])) {
    throw new RuntimeException('Catalog database isolation check failed.');
}
config(['filesystems.disks.public.root' => $test['directory'].'/public', 'storefront.test_checkout' => false, 'storefront.cod_enabled' => false]);
$action = $argv[1] ?? 'inspect';
$admin = App\Models\User::firstOrCreate(['email' => 'catalog@example.test'], ['name' => 'Isolated catalog admin', 'password' => Illuminate\Support\Facades\Hash::make('IsolatedTestOnly123!')]);
Illuminate\Support\Facades\Auth::guard('web')->login($admin);
$category = App\Models\Category::latest('categ_id')->first();
$product = App\Models\Product::latest('product_id')->first();
$db = Illuminate\Support\Facades\DB::class;
$path = $method = null;
$data = $files = [];
if ($action === 'prepare' || $action === 'category') {
    $db::table('brands')->insertOrIgnore(['brand_id' => 1, 'brand_name' => 'Catalog test brand', 'status' => 1]);
    $method = 'POST';
    $path = '/categories';
    $data = ['categ_name' => $action === 'prepare' ? 'Admin Catalog Category' : 'New Catalog Category', 'categ_desc' => 'Isolated test category', 'return_days' => 0, 'categ_status' => 1];
} elseif (in_array($action, ['create', 'no-image', 'update'], true)) {
    $method = $action === 'update' ? 'PUT' : 'POST';
    $path = $action === 'update' ? '/products/update/'.$product->product_id : '/products/store';
    $data = ['product_name' => $action === 'update' ? 'Updated Catalog Product' : ($action === 'no-image' ? 'Image Free Product' : 'Admin Catalog Product'),
        'product_desc' => 'Created through the real authenticated admin controller.', 'health_benefit' => '',
        'category' => $category->categ_id, 'product_brand' => $db::table('brands')->value('brand_id'),
        'product_mrp' => 1500, 'retail_price' => $action === 'update' ? 1100 : 1200,
        'wholesale_price' => 1000, 'special_price' => 1150, 'primary_price' => 'retail',
        'product_stock' => $action === 'update' ? 0 : 7, 'minimum_stock' => 0];
    if ($action === 'create') {
        $files['product_images'] = [Illuminate\Http\UploadedFile::fake()->createWithContent('catalog.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a5WQAAAAASUVORK5CYII='))];
    } elseif ($action === 'update') {
        $data['existing_images'] = $product->images->pluck('image_id')->all();
    }
} elseif ($action === 'toggle') {
    $method = 'GET';
    $path = '/products/'.$product->product_id.'/updateStatus';
} elseif ($action === 'feature') {
    $method = 'POST';
    $path = '/featured-products';
    $data = ['selected_products' => [$product->product_id], 'status' => 1];
}
if ($path) {
    $request = Illuminate\Http\Request::create($path, $method, $data, [], $files, ['HTTP_ACCEPT' => 'application/json']);
    $response = $kernel->handle($request);
    if ($response->getStatusCode() >= 400 || ($response->isRedirect() && $response->headers->get('Location') === 'http://localhost')) {
        fwrite(STDERR, $response->getContent());
        throw new RuntimeException('Admin catalog request failed: '.$response->getStatusCode());
    }
    $kernel->terminate($request, $response);
}
echo json_encode(['action' => $action, 'database' => 'isolated.sqlite', 'products' => $db::table('products')->select('product_id', 'product_name', 'status', 'stock', 'retail')->get(), 'categories' => $db::table('categories')->pluck('categ_name')]), PHP_EOL;

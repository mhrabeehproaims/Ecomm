<?php

// CLI-only fixture: never connects to the normal application's database.
if (PHP_SAPI !== 'cli' || ! str_starts_with(getenv('STOREFRONT_TEST_RUN') ?: '', 'fashion-')) {
    throw new RuntimeException('A dedicated fashion-* test run is required.');
}
$test = require __DIR__.'/environment.php';
require $test['root'].'/vendor/autoload.php';
$app = require $test['root'].'/bootstrap/app.php';
$app->instance('request', Illuminate\Http\Request::create('/'));
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== $test['database']) {
    throw new RuntimeException('Database isolation check failed.');
}
config(['filesystems.disks.public.root' => $test['directory'].'/public']);
$db = Illuminate\Support\Facades\DB::class;
if (! $db::table('products')->where('product_name', 'QA Fashion Shirt')->exists()) {
    $size = $db::table('sizes')->insertGetId(['size_name' => 'Medium', 'status' => 1, 'created_at' => now(), 'updated_at' => now()]);
    $admin = App\Models\User::firstOrCreate(['email' => 'fashion@example.test'], ['name' => 'Isolated fashion admin', 'password' => Illuminate\Support\Facades\Hash::make('IsolatedTestOnly123!')]);
    Illuminate\Support\Facades\Auth::guard('web')->login($admin);
    $pixel = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a5WQAAAAASUVORK5CYII=');
    $request = Illuminate\Http\Request::create('/products/store', 'POST', [
        'product_name' => 'QA Fashion Shirt', 'product_desc' => 'Isolated sized apparel fixture. Not real inventory.', 'health_benefit' => '',
        'category' => $db::table('categories')->value('categ_id'), 'product_brand' => $db::table('brands')->value('brand_id'),
        'product_size' => $size, 'product_mrp' => 1500, 'retail_price' => 1200, 'wholesale_price' => 1000, 'special_price' => 1150,
        'primary_price' => 'retail', 'product_stock' => 7, 'minimum_stock' => 0, 'tax_schedule' => $db::table('tax_schedules')->value('tax_id'),
    ], [], ['product_images' => [Illuminate\Http\UploadedFile::fake()->createWithContent('front.png', $pixel), Illuminate\Http\UploadedFile::fake()->createWithContent('back.png', $pixel)]], ['HTTP_ACCEPT' => 'application/json']);
    $response = $kernel->handle($request);
    if ($response->getStatusCode() >= 400) {
        throw new RuntimeException('Admin fixture creation failed: '.$response->getContent());
    }
    $kernel->terminate($request, $response);
}
$product = App\Models\Product::where('product_name', 'QA Fashion Shirt')->firstOrFail();
echo json_encode(['environment' => 'isolated-storefront-test', 'product' => (new App\Http\Resources\ProductResource($product))->resolve()]).PHP_EOL;

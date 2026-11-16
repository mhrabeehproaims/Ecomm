<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\AuthControllerAdmin;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\Categories;
use App\Http\Controllers\CheckoutEventController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\Customers;
use App\Http\Controllers\DeliveryPartnerController;
use App\Http\Controllers\FeaturedProductController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PackagingTypeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReturnPolicyController;
use App\Http\Controllers\SalesReturnController;
use App\Http\Controllers\SizeController;
use App\Http\Controllers\StockManagementController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\WeightController;
use App\Http\Controllers\WholesaleEnquiryController;
use App\Http\Controllers\WishlistWebController;
use Illuminate\Support\Facades\Route;

if (! auth()->check()) {
    Route::get('/', [AuthControllerAdmin::class, 'index'])->name('login.index')->middleware('guest');
} else {
    return redirect()->route('home');
}
Route::post('validate_creds', [AuthControllerAdmin::class, 'validate'])->name('login.validate');

Route::middleware('AuthMiddleware')->group(function () {
    // Route::get('/', function () {
    //     return view('index');
    // })->name('home');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('home');
    Route::resource('categories', Categories::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('sub-categories', SubCategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('featured-products', FeaturedProductController::class)->only(['index', 'store', 'destroy']);
    Route::resource('delivery_partners', DeliveryPartnerController::class);
    Route::patch('delivery_partners/switchStatus/{delivery_partner}', [DeliveryPartnerController::class, 'switchStatus']);
    Route::delete('/featured-products/delete-multiple', [FeaturedProductController::class, 'destroy']);
    Route::resource('brands', BrandController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('units', UnitController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('packages', PackagingTypeController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('sizes', SizeController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('tax-master', TaxController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);
    Route::get('get-subcategories/{categoryId}', [ProductController::class, 'getSubcategories']);
    Route::get('get-offer-products', [ProductController::class, 'getOfferProducts']);
    Route::get('get-productBy-subcategories/{subCatId}', [ProductController::class, 'getproductsBySubcategories']);
    Route::resource('weight', WeightController::class);

    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('products_create', [ProductController::class, 'create'])->name('products.create');
    Route::post('products/store', [ProductController::class, 'store'])->name('products.store');
    Route::get('products/{product}/updateStatus', [ProductController::class, 'updateStatus'])->name('products.update.status');
    Route::get('products/show/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::get('products-edit-{product}', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('products/update/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.delete');
    Route::resource('stocks', StockManagementController::class);

    Route::resource('customers', Customers::class);
    Route::get('/customers/purchase-history/{id}', [Customers::class, 'purchase_history'])->name('customers.purchaseHistory');
    Route::get('customers/{customer}/updateStatus', [Customers::class, 'updateStatus'])->name('customer.update.status');
    Route::resource('banner', BannerController::class);
    Route::get('banner/getBannerTypes/{type}', [BannerController::class, 'getBannerTypes'])->name('banner.BannerTypes');
    Route::get('wholesale-enquiries', [WholesaleEnquiryController::class, 'index'])->name('wholsale.enquiry');
    Route::put('enquiries/update-status', [WholesaleEnquiryController::class, 'updateStatus'])->name('enquiry.update');
    Route::delete('enquiries/delete/{id}', [WholesaleEnquiryController::class, 'destroy'])->name('enquiry.destroy');

    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('today_deliveries', [OrderController::class, 'today_deliveries'])->name('orders.today_deliveries');
    Route::post('today_deliveries/add_followup', [OrderController::class, 'add_followup'])->name('today_deliveries.followup');
    Route::get('today_deliveries/show_history/{id}', [OrderController::class, 'get_followup_history'])->name('today_deliveries.show_history');
    Route::get('get-orders', [OrderController::class, 'getOrders'])->name('orders.getOrders');
    Route::get('order-details/{id}', [OrderController::class, 'show'])->name('order.show');
    Route::put('update-order-status/{id}', [OrderController::class, 'update_status']);
    Route::post('orders/{order}/cod-receipt', [\App\Http\Controllers\CodAccountingController::class, 'store'])->whereNumber('order')->name('orders.cod-receipt');

    // wishlist
    Route::get('wishlist', [WishlistWebController::class, 'index'])->name('web.wishlist.index');
    Route::post('get_wishist_byCustomer', [WishlistWebController::class, 'get_wishist_byCustomer'])->name('web.wishlist.byCustomer');

    // Cart
    Route::get('carts', [CheckoutEventController::class, 'index'])->name('web.carts.index');
    Route::post('get_carts_byCustomer', [CheckoutEventController::class, 'get_carts_byCustomer'])->name('web.carts.byCustomer');

    // return Policies
    Route::resource('policies', ReturnPolicyController::class);

    // Coupon code
    Route::resource('coupons', CouponController::class);

    Route::resource('offers', OfferController::class);

    // Route::get('/create', [OfferController::class, 'create'])->name('offers.create');
    // Route::post('/store', [OfferController::class, 'store'])->name('offers.store');

    // Notifications
    Route::resource('notification', NotificationController::class);

    // Sales Return
    Route::resource('sales-return', SalesReturnController::class);
    Route::put('update-return-status/{id}', [SalesReturnController::class, 'update_status']);

    // Admin profile
    Route::get('admin-profile', [AdminController::class, 'index'])->name('admin.profile');
    Route::put('admin-profile-update', [AdminController::class, 'profile_update'])->name('profile.update');

    Route::get('admin/analytics', [AnalyticsController::class, 'index'])
        ->name('admin.analytics');
    Route::get('admin/checkout-history', [AnalyticsController::class, 'getCheckoutHistory'])
        ->name('admin.checkoutHistory');
    Route::get('carts/{id}', [CheckoutEventController::class, 'show'])->name('web.carts.show');

    Route::get('logout', [AuthControllerAdmin::class, 'logout'])->name('logout');
});

Route::get('/download-invoice/{filename}', [\App\Http\Controllers\OrderDocumentController::class, 'invoice'])->name('download.invoice');
Route::get('/download-packing-slip/{filename}', [\App\Http\Controllers\OrderDocumentController::class, 'packingSlip'])->name('download.packing-slip');

// Route::get('/razorpay', [PaymentController::class, 'index']);
// Route::post('/razorpay/payment', [PaymentController::class, 'payment'])->name('razorpay.payment');
// Route::post('/razorpay/success', [PaymentController::class, 'success'])->name('razorpay.success');

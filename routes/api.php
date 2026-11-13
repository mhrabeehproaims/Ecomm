<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BannerAPIController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CouponApiController;
use App\Http\Controllers\Api\CustomersController;
use App\Http\Controllers\Api\DeliveryAddressController;
use App\Http\Controllers\Api\FeaturedApiController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SalesReturnController;
use App\Http\Controllers\Api\SubCategory;
use App\Http\Controllers\Api\UtilityController;
use App\Http\Controllers\Api\WholesaleEnquiryApi;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CheckoutEventController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CouponController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('create-customer', [CustomersController::class, 'store']);
Route::post('login', [AuthController::class, 'login'])->name('login');

Route::post('customer-enquiry', [UtilityController::class, 'sendEnquiryMail']);

Route::apiResource('api-categories', CategoryController::class);
Route::apiResource('api-sub-categories', SubCategory::class);
Route::apiResource('api-products', ProductController::class)->middleware('optional.sanctum')->only(['index', 'show']);
Route::get('products_by_category/{categ_id}', [UtilityController::class, 'getProductsByCategory'])->name('products_by_category');
Route::get('products_by_SubCategory/{sub_categ_id}', [UtilityController::class, 'getProductsBySubCategory'])->name('products_by_Subcategory');
Route::get('products_by_Brand/{brand_id}', [UtilityController::class, 'getProductsByBrand'])->name('products_by_brand');
Route::get('random_products', [UtilityController::class, 'get_random_products'])->name('random_products')->middleware('optional.sanctum');
Route::get('get-banners', [BannerAPIController::class, 'index']);
Route::get('featured-prods', [FeaturedApiController::class, 'index'])->middleware('optional.sanctum');
Route::post('wholesale-enquiry', [WholesaleEnquiryApi::class, 'create']);
Route::get('brands', [UtilityController::class, 'get_all_brands']);
Route::get('top-selling', [UtilityController::class, 'top_selling_products']);
Route::get('get-coupons', [CouponApiController::class, 'getAllCoupons']);
Route::get('return-policies', [UtilityController::class, 'getAllPolicies']);
Route::post('track-visit', [AnalyticsController::class, 'trackVisit'])->middleware('optional.sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('wishlist', WishlistController::class);
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::post('cart/remove-item', [CartController::class, 'remove']);
    Route::post('cart/update-count', [CartController::class, 'change_count']);
    Route::post('update-customer', [CustomersController::class, 'update']);
    Route::get('show-customer', [CustomersController::class, 'show']);
    Route::apiResource('delivery-address', DeliveryAddressController::class);
    Route::delete('customers/delete-account', [CustomersController::class, 'destroy']);
    Route::post('cart/checkout', [CheckoutController::class, 'checkout']);
    Route::get('purchase/history', [CheckoutController::class, 'purchase_history']);
    Route::get('/order-items/{orderItemId}/comments', [CommentController::class, 'show']);
    Route::post('/order-items/{orderItemId}/comments', [CommentController::class, 'store']);
    Route::post('/coupon/validate', [UtilityController::class, 'validate_coupon']);
    Route::post('checkout-click', [CheckoutEventController::class, 'checkoutClick']);
    Route::resource('sales-return/save', SalesReturnController::class)->only('store');

    // RazorPay
    Route::post('/razorpay/payment', [PaymentController::class, 'payment']);
    Route::post('/razorpay/success', [PaymentController::class, 'success']);
    Route::post('/razorpay/failed', [PaymentController::class, 'failed']);

    Route::post('logout', [AuthController::class, 'logout']);
});

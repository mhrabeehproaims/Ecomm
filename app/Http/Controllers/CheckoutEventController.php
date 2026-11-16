<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CheckoutEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutEventController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->cartFilters($request);
        $cart_products = Cart::with(['customer', 'product.primaryImage', 'product.size'])
            ->when($filters['customer'] ?? null, fn ($query, $customer) => $query->where('user_id', $customer))
            ->when($filters['product'] ?? null, fn ($query, $product) => $query->where('product_id', $product))
            ->orderByDesc('updated_at')->orderByDesc('cart_id')->paginate(25)->withQueryString();

        // Use uncached membership queries: cart writes must appear immediately.
        $customers = DB::table('customers')->whereExists(fn ($query) => $query->selectRaw('1')
            ->from('carts')->whereColumn('carts.user_id', 'customers.customer_id'))
            ->orderBy('first_name')->orderBy('last_name')->get(['customer_id', 'first_name', 'last_name', 'phone']);
        $products = DB::table('products')->whereExists(fn ($query) => $query->selectRaw('1')
            ->from('carts')->whereColumn('carts.product_id', 'products.product_id'))
            ->orderBy('product_name')->get(['product_id', 'product_name', 'status']);

        return response()->view('Cart.carts', compact('cart_products', 'customers', 'products', 'filters'))
            ->header('Cache-Control', 'private, no-store');
    }

    private function cartFilters(Request $request): array
    {
        return array_filter($request->validate([
            'customer' => ['nullable', 'integer', 'exists:customers,customer_id'],
            'product' => ['nullable', 'integer', 'exists:products,product_id'],
        ]), fn ($value) => $value !== null && $value !== '');
    }

    public function checkoutClick(Request $request)
    {
        $sessionId = $request->input('session_id');

        $user = $request->user();

        CheckoutEvent::updateOrCreate(
            ['session_id' => $sessionId],
            [
                'customer_id' => $user ? $user->customer_id : null,
                'cart_id' => $request->input('cart_id'),
                'clicked_at' => now(),
            ]
        );

        return response()->json(['status' => true]);
    }

    public function get_carts_byCustomer(Request $request)
    {
        // Preserve existing form links while using one consistent, bookmarkable listing.
        return redirect()->route('web.carts.index', $this->cartFilters($request));
    }
    public function show($id)
    {
        $checkoutEvent = CheckoutEvent::with([
            'customer',
            'customer.carts.product',
            'cart.customer'
        ])->find($id);

        if (!$checkoutEvent) {
            return response()->json(['message' => 'Checkout event not found'], 404);
        }

        // Build product list with quantity
        $products = $checkoutEvent->customer->carts->map(function ($cart) {
            return [
                'name' => $cart->product->product_name ?? null,
                'qty'  => $cart->quantity ?? 1,
                'unit'  => $cart->product->unit->unit_name ?? null,
            ];
        })->filter(fn($p) => $p['name'])->values()->toArray();

        $responseData = [
            'id'                => $checkoutEvent->id,
            'session_id'        => $checkoutEvent->session_id,
            'clicked_at'        => $checkoutEvent->clicked_at->format('d-m-Y H:i A'),
            'customer_name'     => $checkoutEvent->customer->full_name ?? 'N/A',
            'cart_customer_name' => $checkoutEvent->cart->customer->full_name ?? 'N/A',
            'products'          => $products,
        ];

        return response()->json($responseData);
    }
}

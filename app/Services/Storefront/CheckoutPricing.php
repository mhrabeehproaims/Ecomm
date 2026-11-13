<?php

namespace App\Services\Storefront;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CheckoutPricing
{
    public function calculate(int $customer, ?int $addressId, string $couponCode, string $method, bool $lock = false): array
    {
        $address = $addressId ? DB::table('delivery_addresses')->where('customer_id', $customer)->where('address_id', $addressId)->first() : null;
        if ($addressId && ! $address) {
            abort(404, 'Address not found.');
        }
        $shipping = 0;
        $snapshot = null;
        if ($address) {
            $rates = config('storefront.shipping_minor');
            $state = $address->state;
            // Accept the existing enum or the matching human-readable state name.
            if (! array_key_exists($state, $rates)) {
                foreach (array_keys($rates) as $code) {
                    $label = str_replace('_', ' ', preg_replace('/ \([A-Z]+\)$/', '', $code));
                    if (strcasecmp($state, $label) === 0) {
                        $state = $code;
                        break;
                    }
                }
            }
            if (strcasecmp($address->country ?? 'India', 'India') !== 0 || ! preg_match('/^[1-9]\d{5}$/D', $address->postal_code)
                || ! array_key_exists($state, $rates) || ! is_int($rates[$state]) || $rates[$state] < 0
                || (config('storefront.postal_codes') && ! in_array($address->postal_code, config('storefront.postal_codes'), true))) {
                throw ValidationException::withMessages(['address_id' => 'Delivery is not configured for this address.']);
            }
            $shipping = $rates[$state];
            $snapshot = array_intersect_key((array) $address, array_flip(['address_id', 'phone', 'email', 'address_line1', 'address_line2', 'city', 'state', 'country', 'postal_code']));
            $snapshot['state'] = $state;
        }
        if ($method === 'cod' && ! config('storefront.cod_enabled')) {
            throw ValidationException::withMessages(['payment_method' => 'Cash on delivery is not available.']);
        }
        $cartQuery = DB::table('carts')->where('user_id', $customer)->orderBy('product_id')->orderBy('cart_id');
        $cart = ($lock ? $cartQuery->lockForUpdate() : $cartQuery)->get();
        if ($cart->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'Your bag is empty.']);
        }
        $ids = $cart->pluck('product_id')->unique()->sort()->values();
        $productsQuery = DB::table('products')->whereIn('product_id', $ids)->orderBy('product_id');
        $products = ($lock ? $productsQuery->lockForUpdate() : $productsQuery)->get()->keyBy('product_id');
        $lines = [];
        $subtotal = 0;
        foreach ($cart->groupBy('product_id') as $productId => $entries) {
            $p = $products[$productId] ?? null;
            $quantity = $entries->sum('quantity');
            if (! $p || (int) $p->status !== 1 || $quantity < 1 || $quantity > 99 || $quantity !== (int) $quantity || $p->stock < $quantity) {
                throw ValidationException::withMessages(['cart' => 'A product is unavailable or its quantity exceeds available stock.']);
            }
            $model = (new Product)->newFromBuilder((array) $p);
            $base = match ($p->primary_price) {
                'retail' => $p->retail, 'wholesale' => $p->wholesale, 'special_price' => $p->special_price, default => $p->mrp
            };
            $actual = Money::minor($base);
            $offer = $model->offer_price;
            $unit = $offer === null ? $actual : min($actual, Money::minor($offer));
            $tax = DB::table('tax_schedules')->where('tax_id', $p->tax_schedule_id)->where('status', 1)->first();
            if (! $tax) {
                throw ValidationException::withMessages(['tax' => 'A product tax schedule is unavailable.']);
            }
            $rate = Money::minor($tax->tax_percentage);
            if ($rate > 10000) {
                throw ValidationException::withMessages(['tax' => 'Invalid product tax schedule.']);
            }
            $cgst = Money::minor($tax->cgst);
            $sgst = Money::minor($tax->sgst);
            $igst = Money::minor($tax->igst);
            $local = ! $snapshot || $snapshot['state'] === config('storefront.origin_state');
            if (($local && $cgst + $sgst !== $rate) || (! $local && $igst !== $rate)) {
                throw ValidationException::withMessages(['tax' => 'Product GST components do not match the configured tax rate.']);
            }
            $total = $unit * $quantity;
            $subtotal += $total;
            if ($subtotal > 9999999999) {
                throw ValidationException::withMessages(['cart' => 'Order amount exceeds supported limits.']);
            }
            $lines[] = ['product_id' => (int) $productId, 'name' => $p->product_name, 'quantity' => $quantity, 'unit_minor' => $unit,
                'actual_minor' => $actual, 'line_minor' => $total, 'tax_basis_points' => $rate,
                'cgst_basis_points' => $local ? $cgst : 0, 'sgst_basis_points' => $local ? $sgst : 0, 'igst_basis_points' => $local ? 0 : $igst,
                'cart' => $entries->map(fn ($e) => ['id' => $e->cart_id, 'quantity' => $e->quantity])->values()->all(),
                'return_policy' => app(ReturnService::class)->policy((int) $productId)];
        }
        [$coupon, $discount] = $this->coupon($customer, $couponCode, $subtotal, $lock);
        $allocated = 0;
        $taxTotal = 0;
        foreach ($lines as $i => &$line) {
            // Cumulative proportional allocation ensures every paise is assigned exactly once.
            $cumulative = array_sum(array_column(array_slice($lines, 0, $i + 1), 'line_minor'));
            $share = $subtotal ? Money::ratio($discount, $cumulative, $subtotal) - $allocated : 0;
            $allocated += $share;
            $line['discount_minor'] = $share;
            $line['total_minor'] = $line['line_minor'] - $share;
            $line['tax_minor'] = Money::ratio($line['total_minor'], $line['tax_basis_points'], 10000 + $line['tax_basis_points']);
            $line['base_minor'] = $line['total_minor'] - $line['tax_minor'];
            $taxTotal += $line['tax_minor'];
        }
        unset($line);
        $shippingTax = 0;
        if ($shipping > 0) {
            $shippingRate = filter_var(config('storefront.shipping_tax_basis_points'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 10000]]);
            if ($shippingRate === false || config('storefront.shipping_tax_basis_points') === null) {
                throw ValidationException::withMessages(['delivery' => 'Delivery tax has not been configured.']);
            }
            $shippingTax = Money::ratio($shipping, $shippingRate, 10000 + $shippingRate);
            $taxTotal += $shippingTax;
        }
        $total = $subtotal - $discount + $shipping;
        if ($total > 9999999999 || $total < 0) {
            throw ValidationException::withMessages(['cart' => 'Order amount exceeds supported limits.']);
        }
        if ($method === 'online' && $address && $total < 100) {
            throw ValidationException::withMessages(['payment_method' => 'Online payment requires an order of at least INR 1.']);
        }

        return ['currency' => 'INR', 'address' => $snapshot, 'payment_method' => $method, 'items' => $lines,
            'subtotal_minor' => $subtotal, 'discount_minor' => $discount, 'tax_minor' => $taxTotal, 'shipping_minor' => $shipping,
            'total_minor' => $total, 'shipping_tax_minor' => $shippingTax, 'tax_inclusive' => true, 'coupon' => $coupon ? ['id' => $coupon->id, 'code' => $coupon->code, 'type' => $coupon->type, 'value' => $coupon->value] : null];
    }

    private function coupon(int $customer, string $code, int $subtotal, bool $lock): array
    {
        if ($code === '') {
            return [null, 0];
        }
        $query = DB::table('coupons')->where('code', $code);
        $coupon = ($lock ? $query->lockForUpdate() : $query)->first();
        $invalid = ! $coupon || (int) $coupon->is_active !== 1
            || ($coupon->start_date && now()->toDateString() < substr($coupon->start_date, 0, 10))
            || ($coupon->end_date && now()->toDateString() > substr($coupon->end_date, 0, 10));
        if ($invalid) {
            throw ValidationException::withMessages(['coupon_code' => 'Invalid coupon code or coupon is inactive/expired.']);
        }
        $usage = DB::table('coupon_usages')->where('coupon_id', $coupon->id);
        $reserved = DB::table('storefront_checkouts')->where('coupon_id', $coupon->id)->where('state', 'reserved');
        // Locking current reads avoid stale MySQL repeatable-read snapshots after waiting on a coupon lock.
        $usages = ($lock ? $usage->lockForUpdate() : $usage)->get();
        $reservations = ($lock ? $reserved->lockForUpdate() : $reserved)->get();
        $used = $usages->sum('usage_count') + $reservations->count();
        $own = $usages->where('customer_id', $customer)->sum('usage_count') + $reservations->where('customer_id', $customer)->count();
        if (($coupon->max_uses && $used >= $coupon->max_uses) || ($coupon->max_uses_per_user && $own >= $coupon->max_uses_per_user)) {
            throw ValidationException::withMessages(['coupon_code' => 'This coupon usage limit has been reached.']);
        }
        if ($subtotal < Money::minor($coupon->min_order_amount ?? 0)) {
            throw ValidationException::withMessages(['coupon_code' => 'The minimum order amount has not been reached.']);
        }
        $value = Money::minor($coupon->value);
        if (! in_array($coupon->type, ['fixed', 'percentage']) || ($coupon->type === 'percentage' && $value > 10000)) {
            throw ValidationException::withMessages(['coupon_code' => 'Coupon configuration is invalid.']);
        }

        return [$coupon, min($subtotal, $coupon->type === 'fixed' ? $value : Money::ratio($subtotal, $value, 10000))];
    }
}

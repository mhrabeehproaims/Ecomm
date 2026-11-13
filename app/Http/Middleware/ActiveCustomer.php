<?php

namespace App\Http\Middleware;

use App\Models\Customer;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class ActiveCustomer
{
    public function handle(Request $request, Closure $next)
    {
        $customer = $request->user();
        abort_unless($customer instanceof Customer && (int) DB::table('customers')->where('customer_id', $customer->customer_id)->value('status') === 1, 403, 'An active customer account is required.');

        return $next($request);
    }
}

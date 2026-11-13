<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CheckoutEvent;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Visit;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index()
    {
        $todayVisitors = Visit::whereDate('visited_at', today())->distinct('session_id')->count('session_id');

        $totalVisitors = Visit::distinct('session_id')->count('session_id');

        $activeVisitors = Visit::where('visited_at', '>=', now()->subMinutes(5))
            ->distinct('session_id')
            ->count('session_id');

        $activeCustomers = Customer::where('last_seen_at', '>=', now()->subMinutes(5))->count();

        // Page-wise analytics (top 10)
        $topPages = Visit::selectRaw('page_url, COUNT(*) as total')
            ->groupBy('page_url')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        $cartCount = Cart::count();
        $abandonedCheckouts = CheckoutEvent::count();
        $purchaseCount = Order::count();
        // Because completed ones are deleted

        return view('Analytics.index', compact(
            'todayVisitors',
            'totalVisitors',
            'activeVisitors',
            'activeCustomers',
            'topPages',
            'cartCount',
            'abandonedCheckouts',
            'purchaseCount'
        ));
    }

    public function trackVisit(Request $request)
    {
        $sessionId = $request->input('session_id');
        $user = $request->user();

        // 1️⃣ Main logic: Update or create the current session record
        $visit = Visit::updateOrCreate(
            ['session_id' => $sessionId],
            [
                'user_id'        => $user ? $user->customer_id : null,
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->userAgent(),
                'page_url'       => $request->input('page_url'),
                'visited_at'     => now(),
                'last_active_at' => now(),
            ]
        );

        // 2️⃣ If user is logged in, update their last activity
        if ($user) {
            $user->update(['last_seen_at' => now()]);

            // 3️⃣ Cleanup: Remove old session entries (duplicates)
            Visit::where('user_id', $user->customer_id)
                ->where('session_id', '!=', $sessionId)
                ->delete();
        }

        return response()->json(['status' => true]);
    }


    public function getCheckoutHistory()
    {
        $allCheckouts = CheckoutEvent::paginate(10);
        return view('Analytics.checkoutHistory', compact('allCheckouts'));
    }
    public function getCheckoutHistorybyUser(Request $request)
    {
        $sessionId = $request->input('session_id');
        $user = $request->user();

        $checkoutHistory = CheckoutEvent::where('session_id', $sessionId)->get();
        if ($user) {
            $user->update(['last_seen_at' => now()]);
        }

        return response()->json(['status' => true]);
    }
}

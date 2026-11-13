<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Storefront\ReturnService;
use Illuminate\Http\Request;

class SalesReturnController extends Controller
{
    public function store(Request $request, ReturnService $service)
    {
        $data = $request->validate(['order_id' => 'required|integer|min:1', 'items' => 'required|array|min:1|max:99',
            'items.*.product_id' => 'required|string|max:4096', 'items.*.quantity' => 'required|integer|min:1|max:99',
            'items.*.order_item_id' => 'nullable|integer|min:1',
            'return_type' => 'required|in:refund,replacement', 'return_reason' => 'required|string|min:5|max:1000',
            'product_condition' => 'required|in:damaged,unused,opened,other', 'remarks' => 'nullable|string|max:1000', 'request_key' => 'nullable|uuid',
            'status' => 'prohibited', 'reject_reason' => 'prohibited']);
        foreach ($data['items'] as &$line) {
            try {
                $line['product_id'] = (int) decrypt($line['product_id']);
            } catch (\Throwable $e) {
                return response()->json(['message' => 'Invalid product ID encryption.'], 422);
            }
        }
        unset($line);
        $id = $service->request($request->user()->customer_id, $data);

        return response()->json(['status' => 'success', 'return_id' => $id, 'message' => 'Return request submitted for review.'], 201);
    }
}

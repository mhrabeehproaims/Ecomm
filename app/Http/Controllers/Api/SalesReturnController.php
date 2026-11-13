<?php

namespace App\Http\Controllers\Api;

use App\Events\sendReturnNotification;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SalesReturn;
use App\Models\SalesReturnItems;
use App\Models\StockManagement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SalesReturnController extends Controller
{
    public function store(Request $request)
    {
        $requestData = $request->all();

        // Decrypt product_id values
        if (isset($requestData['items'])) {
            foreach ($requestData['items'] as $key => $item) {
                if (isset($item['product_id'])) {
                    try {
                        $requestData['items'][$key]['product_id'] = decrypt($item['product_id']);
                    } catch (\Exception $e) {
                        return response()->json([
                            'message' => 'Invalid product ID encryption',
                            'errors' => [
                                "items.{$key}.product_id" => ['The product ID encryption is invalid.']
                            ]
                        ], 422);
                    }
                }
            }
        }

        // Validate the modified data
        $validator = Validator::make($requestData, [
            'order_id' => 'required|exists:orders,order_id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,product_id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'return_type' => 'nullable|in:refund,replacement',
            'reject_reason' => 'nullable|string',
            'return_reason' => 'nullable|string',
            'product_condition' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        $customer = Auth::user(); // customer authenticated via API

        $return = SalesReturn::create([
            'order_id' => $data['order_id'],
            'customer_id' => $customer->customer_id,
            'return_date' => now(),
            'total_amount' => collect($data['items'])->sum(fn($i) => $i['quantity'] * $i['price']),
            'notes' => $data['remarks'] ?? null,
            'return_reason' =>  $data['return_reason'],
            'product_condition' => $data['product_condition'],
            'status' => 'requested',
            'return_type' => $data['return_type']
        ]);

        foreach ($data['items'] as $item) {
            $order_item_id = OrderItem::where('order_id', $data['order_id'])
                ->where('product_id', $item['product_id'])
                ->value('order_item_id');

            if ($order_item_id) {
                SalesReturnItems::create([
                    'sales_return_id' => $return->return_id,
                    'product_id' => $item['product_id'],
                    'order_item_id' => $order_item_id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'],
                ]);
            }
        }

        $notif_data=[
            'return' =>$return,
            'customer'=>$customer->full_name,
            'order_id'=>$data['order_id'],
            'subtotal'=>$item['quantity'] * $item['price'],
            'item_id'=>$item['product_id'],
        ];
        
        event(new sendReturnNotification($notif_data));

        return response()->json([
            'status' => 'success',
            'message' => 'Sales return saved successfully',
        ]);
    }
}

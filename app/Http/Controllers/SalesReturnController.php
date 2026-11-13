<?php

namespace App\Http\Controllers;

use App\Mail\OrderReissuedMail;
use App\Mail\ReturnAcceptMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Models\Product;
use App\Models\SalesReturn;
use App\Models\SalesReturnItems;
use App\Models\StockManagement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SalesReturnController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $returns = SalesReturn::with('customer', 'items', 'reforder')->withCount('items')->latest()->paginate(10);
        return view('SalesReturn.returns', compact('returns'));
        // return response()->json($returns);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $id = decrypt($id);
        $return_details = SalesReturn::where('return_id', $id)->with('items')->first();
        return view('SalesReturn.return-details', compact('return_details'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SalesReturn $salesReturn)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SalesReturn $salesReturn)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SalesReturn $salesReturn)
    {
        //
    }

    //     public function update_status($id, Request $request)
    //     {
    //         $request->validate([
    //             'status' => 'required|in:requested,accepted,rejected,picked_up,collected,refunded,replaced'
    //         ]);

    //         $status = $request->status;
    //         $salesReturn = SalesReturn::with(['order', 'items.product'])->findOrFail($id);

    //         // ✅ Update main return record status
    //         $salesReturn->update(['status' => $status]);

    //         // 🧾 Handle Refund Logic
    //         if ($status == "refunded") {
    //             foreach ($salesReturn->items as $item) {
    //                 $product = $item->product;

    //                 $product->increment('stock', $item->quantity);

    //                 StockManagement::create([
    //                     'product_id'   => $product->product_id,
    //                     'quantity'     => $item->quantity,
    //                     'stock_status' => 'in_stock',
    //                     'notes'        => 'Product returned from sales',
    //                 ]);
    //             }
    //         }

    //         // 🔄 Handle Replace (Re-Issue) Logic
    //         if ($status == "replaced") {
    //             $originalOrder = $salesReturn->order;
    //             $user = $originalOrder->customer;

    //             DB::beginTransaction();
    //             try {
    //                 // ✅ Step 1: Create new re-issued order
    //                 $newOrder = Order::create([
    //                     'customer_id'        => $user->customer_id,
    //                     'reference_order_id' => $originalOrder->order_id,
    //                     'total_tax_amount'   => 0,
    //                     'discount'           => 0,
    //                     'total_amount'       => 0,
    //                     'status'             => 'reissued',
    //                     'payment_status'     => 'unpaid',
    //                     'paid_at'            => now(),
    //                 ]);

    //                 // ✅ Step 2: Add reissued products to order_items
    //                 foreach ($salesReturn->items as $item) {
    //                     $product = $item->product;
    //                     $price = $product->special_price;

    //                     OrderItem::create([
    //                         'order_id'       => $newOrder->order_id,
    //                         'product_id'     => $product->product_id,
    //                         'quantity'       => $item->quantity,
    //                         'price'          => $price,
    //                         'total'          => $price * $item->quantity,
    //                         'tax_percent'    => $product->taxSchedule->tax_percentage ?? 0,
    //                         'cgst_percent'   => $product->taxSchedule->cgst ?? 0,
    //                         'sgst_percent'   => $product->taxSchedule->sgst ?? 0,
    //                         'igst_percent'   => $product->taxSchedule->igst ?? 0,
    //                         'amount_wo_tax'  => $price,
    //                         'cgst_amount'    => 0,
    //                         'sgst_amount'    => 0,
    //                         'igst_amount'    => 0,
    //                     ]);

    //                     // ✅ Step 3: Reduce stock for replaced product
    //                     // $product->decrement('stock', $item->quantity);

    //                     // StockManagement::create([
    //                     //     'product_id'   => $product->product_id,
    //                     //     'quantity'     => $item->quantity,
    //                     //     'stock_status' => 'out_stock',
    //                     //     'notes'        => 'Product reissued as replacement',
    //                     // ]);
    //                 }

    //                 // ✅ Step 4: Link reissued order to sales_return
    //                 $salesReturn->update([
    //                     'reissued_order_id' => $newOrder->order_id,
    //                 ]);

    //                 OrderStatusLog::create([
    //                     'order_id'   => $newOrder->order_id,
    //                     'status'     => 'Reissued',
    //                     'changed_by' => 1, //Admin
    //                     'remarks'    => 'Status updated by admin',
    //                 ]);


    //                 DB::commit();

    //                 // Send mail or notification
    //                 if (filled($user->email)) {
    //                     Mail::to($user->email)->send(new OrderReissuedMail($newOrder, $originalOrder));
    //                 }
    //             } catch (\Exception $e) {
    //                 DB::rollBack();
    //                 return response()->json([
    //                     'status' => 'error',
    //                     'message' => 'Reissue failed: ' . $e->getMessage(),
    //                 ], 500);
    //             }
    //         }

    //         return response()->json([
    //             'status' => 'success',
    //             'status2' => $status,
    //         ]);
    //     }

    public function update_status($id, Request $request)
    {
        $request->validate([
            'status' => 'required|in:requested,accepted,rejected,picked_up,collected,refunded,replaced'
        ]);

        $status = $request->status;
        $salesReturn = SalesReturn::with(['order.customer', 'items.product.taxSchedule'])->findOrFail($id);

        $salesReturn->update(['status' => $status]);

        DB::beginTransaction();

        try {
            $originalOrder = $salesReturn->order;
            $user = $originalOrder->customer;
            if ($status === "accepted") {
                $item = SalesReturnItems::where('sales_return_id', $id)->first();
                if (filled($user->email)) {
                    Mail::to($user->email)->send(new ReturnAcceptMail($user, $item));
                }
                OrderStatusLog::create([
                    'order_id'   => $originalOrder->order_id,
                    'status'     => 'accepted',
                    'changed_by' =>  1,
                    'remarks'    => 'Return Accepted',
                ]);
            } elseif ($status === "refunded") {
                foreach ($salesReturn->items as $item) {
                    $product = $item->product;

                    $product->increment('stock', $item->quantity);

                    StockManagement::create([
                        'product_id'   => $product->product_id,
                        'quantity'     => $item->quantity,
                        'stock_status' => 'refunded',
                        'notes'        => 'Refunded product returned from sales return ID: ' . $salesReturn->return_id,
                    ]);
                }

                OrderStatusLog::create([
                    'order_id'   => $originalOrder->order_id,
                    'status'     => 'refunded',
                    'changed_by' =>  1,
                    'remarks'    => 'Amount Refunded',
                ]);
            } elseif ($status === "replaced") {

                $newOrder = Order::create([
                    'customer_id'        => $user->customer_id,
                    'reference_order_id' => $originalOrder->order_id,
                    'total_tax_amount'   => 0,
                    'discount'           => 0,
                    'total_amount'       => 0,
                    'status'             => 'reissued',
                    'payment_status'     => 'unpaid',
                    'paid_at'            => now(),
                ]);

                foreach ($salesReturn->items as $item) {
                    $product = $item->product;
                    $price = $product->special_price ?? $product->default_price_value;
                    $taxSchedule = $product->taxSchedule;

                    $itemTotal = $price * $item->quantity;
                    $taxPercent = $taxSchedule->tax_percentage ?? 0;
                    $taxAmount = ($itemTotal * $taxPercent) / 100;

                    $tax_value = $this->reverseTaxCalc($taxPercent ?? 0, $itemTotal);
                    $tax_amount = $tax_value['tax_amount'] ?? 0;
                    $base_price = $tax_value['base_price'] ?? 0;

                    OrderItem::create([
                        'order_id'      => $newOrder->order_id,
                        'product_id'    => $product->product_id,
                        'quantity'      => $item->quantity,
                        'price'         => $price,
                        'total'         => $itemTotal,
                        'tax_percent'   => $taxPercent,
                        'cgst_percent'  => $taxSchedule->cgst ?? 0,
                        'sgst_percent'  => $taxSchedule->sgst ?? 0,
                        'igst_percent'  => $taxSchedule->igst ?? 0,
                        'amount_wo_tax' => $base_price,
                        'cgst_amount'   => $tax_amount / 2,
                        'sgst_amount'   => $tax_amount / 2,
                        'igst_amount'   => $tax_amount,
                    ]);

                    // 🔻 Stock decrease only for replaced items
                    // $product->decrement('stock', $item->quantity);

                    // StockManagement::create([
                    //     'product_id'   => $product->product_id,
                    //     'quantity'     => $item->quantity,
                    //     'stock_status' => 'out_stock',
                    //     'notes'        => 'Reissued product for sales return ID: ' . $salesReturn->return_id,
                    // ]);
                }

                $newOrder->update([
                    'total_amount' => $tax_amount + $base_price,
                    'total_tax_amount' => $tax_amount,
                ]);

                $salesReturn->update(['reissued_order_id' => $newOrder->order_id]);

                OrderStatusLog::create([
                    'order_id'   => $newOrder->order_id,
                    'status'     => 'reissued',
                    'changed_by' =>  1,
                    'remarks'    => 'Reissued items for sales return #' . $salesReturn->return_id,
                ]);

                if (filled($user->email)) {
                    $item = SalesReturnItems::where('sales_return_id', $id)->first();
                    Mail::to($user->email)->send(new OrderReissuedMail($newOrder, $item));
                }
            } else {
                OrderStatusLog::create([
                    'order_id'   => $originalOrder->order_id,
                    'status'     => 'delivered',
                    'changed_by' =>  1,
                    'remarks'    => '',
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'status2' => $status,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Action failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function reverseTaxCalc($tax_percentage, $total_amount)
    {
        // Method: 1
        // $base = ($total_amount*100)/(100+$tax_percentage);
        // $tax = $total_amount - $base;

        //Method: 2
        $base = $total_amount / (1 + ($tax_percentage / 100));
        $tax = $total_amount - $base;

        return [
            "base_price" => round($base, 2),
            "tax_amount" => round($tax, 2),
        ];
    }
}

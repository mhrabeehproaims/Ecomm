<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class OrderHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $secure = \Illuminate\Support\Facades\Schema::hasTable('storefront_checkouts');
        $checkout = $secure ? \Illuminate\Support\Facades\DB::table('storefront_checkouts')->where('order_id', $this->order_id)->first() : null;

        return [
            'created_at' => $this->created_at,
            'checkout_state' => $checkout?->state,
            'reservation_expires_at' => $checkout?->expires_at,
            'address_snapshot' => $checkout ? json_decode($checkout->address_snapshot, true) : null,
            'quote' => $checkout ? json_decode($checkout->breakdown, true) : null,
            'order_id' => $this->order_id,
            'customer_id' => $this->customer_id,
            'total_amount' => $this->total_amount,
            'discount' => $this->discount,
            'total_tax_amount' => $this->total_tax_amount,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'paid_at' => $this->paid_at,
            'invoice' => $this->invoice ? route('download.invoice', basename($this->invoice->invoice_number.'.pdf')) : '',
            'items' => $this->items->map(function ($item) {
                return [
                    'order_item_id' => $item->order_item_id,
                    'order_id' => $item->order_id,
                    'product_id' => encrypt($item->product_id),
                    'product_name' => $item->product?->product_name ?? '',
                    'category_return_days' => $item->product?->category?->return_days ?? '',
                    'sub_category_return_days' => $item->product?->subCategory?->return_days ?? '',
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->total,
                    'amount_wo_tax' => $item->amount_wo_tax,
                    'primary_image' => $item->product?->primaryImage?->image_path ?? null,
                    'is_returned' => $item->returned !== null,
                    'return_status' => $item->returned?->salesReturn?->status
                        ? Str::headline($item->returned->salesReturn->status)
                        : '',
                ];
            }),
        ];
    }
}

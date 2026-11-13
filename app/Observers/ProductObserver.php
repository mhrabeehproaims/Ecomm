<?php

namespace App\Observers;

use App\Models\Notification;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        // Log::info($product->stock <= $product->minimum_stock ? 'good' : 'bad');

        if ($product->stock <= $product->minimum_stock) {

            $notification = Notification::firstOrCreate(
                [
                    'notifiable_id' => $product->product_id,
                    'notifiable_type' => get_class($product),
                    'type' => 'stock',
                    'is_read' => 0,
                ],
                [
                    'user_id' => 1,
                    'title' => 'Stock Low Warning - '.$product->product_name,
                    'message' => 'Stock has reached the minimum threshold of ' . $product->minimum_stock,
                    'data' => ['product_name' => $product->product_name],
                ]
            );

            // ✅ Optional: if you want to reset an existing notification to unread
            if (!$notification->wasRecentlyCreated) {
                $notification->update(['is_read' => 0]);
            }
        }
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        //
    }
}

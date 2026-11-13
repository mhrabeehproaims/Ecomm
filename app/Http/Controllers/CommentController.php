<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function show($orderItemId)
    {
       $comments = Comment::where('order_item_id', $orderItemId)
    ->whereNull('parent_id')
    ->with('customer', 'replies.customer')
    ->get();


        return response()->json([
            'status' => true,
            'data' => $comments,
        ]);
    }

    public function store(Request $request, $orderItemId)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
            'rating' => 'nullable|integer|min:1|max:5',
            'parent_id' => 'nullable|exists:comments,comment_id',
        ]);

        $orderItem = OrderItem::findOrFail($orderItemId);

        if ($orderItem->order->customer_id !== $request->user()->customer_id && !$request->parent_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment = Comment::create([
            'customer_id' => $request->user()->customer_id,
            'order_item_id' => $orderItemId,
            'comment' => $request->comment,
            'rating' => $request->rating,
            'parent_id' => $request->parent_id,
        ]);

        return response()->json([
            'status' => true,
            'message' => $request->parent_id ? 'Reply added successfully!' : 'Comment added successfully!',
        ]);
    }
}

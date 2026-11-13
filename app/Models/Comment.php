<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $primaryKey = "comment_id";
    protected $fillable = ['customer_id', 'order_item_id', 'parent_id', 'comment', 'rating'];

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id', 'comment_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id', 'comment_id')->with('replies.customer');
    }

    // public function customer()
    // {
    //     return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    // }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }
}

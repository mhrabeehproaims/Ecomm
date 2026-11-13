<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $fillable = [
        'session_id',
        'user_id',
        'ip_address',
        'user_agent',
        'page_url',
        'visited_at',
        'last_active_at',
    ];

    public $timestamps = false;
}

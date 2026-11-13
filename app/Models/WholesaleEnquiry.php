<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;

class WholesaleEnquiry extends Model
{
    use Cachable;
    
    public $fillable = ['name', 'email', 'phone', 'company_name', 'message', 'status'];
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BannerAPIController extends Controller
{
    public function index()
    {
        $banner = Banner::where('banner_status', 1)->latest()->get();
        return response()->json($banner);
    }
}

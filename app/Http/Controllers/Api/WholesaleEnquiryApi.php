<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WholesaleEnquiry;
use Illuminate\Http\Request;

class WholesaleEnquiryApi extends Controller
{
    public function create(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'nullable',
            'phone' => 'required',
            'company_name' => 'nullable',
            'message' => 'required|string',
        ]);

        $validated['status'] = 'pending';
        WholesaleEnquiry::create($validated);
        return response()->json([
            'status' => 'success',
            'message' => 'Created successfully'
        ], 201);
    }
}

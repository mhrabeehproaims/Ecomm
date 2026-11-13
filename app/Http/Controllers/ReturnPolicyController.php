<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\ReturnPolicy;
use Illuminate\Http\Request;

class ReturnPolicyController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    public function index()
    {
        $policies = ReturnPolicy::latest()->get();
        return view('ReturnPolicy.policies', compact('policies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(Request $request)
    {
        $request->validate([
            'policy_title' => 'required|string',
            'return_days' => 'required|',
            'return_type' => 'required|in:refund_or_exchange,refund,exchange',
            'policy_status' => 'required|in:0,1',
            'policy_conditions' => 'required|string',
        ]);

        ReturnPolicy::create([
            'title' => $request->policy_title,
            'return_days' => $request->return_days,
            'return_type' => $request->return_type,
            'is_active' => $request->policy_status,
            'conditions' => $request->policy_conditions,
            'created_by' => 1
        ]);

        return response()->json(['message' => 'success', 'response' => 'Policy created successfully']);
    }

    public function edit(ReturnPolicy $policy)
    {
        return response()->json($policy);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,)
    {
        $request->validate([
            'policy_title' => 'required|string',
            'return_days' => 'required|',
            'return_type' => 'required|in:refund_or_exchange,refund,exchange',
            'policy_status' => 'required|in:0,1',
            'policy_conditions' => 'required|string',
        ]);

        $id = $request->input('hidden_id');
        $returnPolicy = ReturnPolicy::find($id);
        $returnPolicy->update([
            'title' => $request->policy_title,
            'return_days' => $request->return_days,
            'return_type' => $request->return_type,
            'is_active' => $request->policy_status,
            'conditions' => $request->policy_conditions,
            'created_by' => 1
        ]);

        return response()->json(['message' => 'success', 'response' => 'updated']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReturnPolicy $policy)
    {
        $policy->delete();
        return response()->json(['status' => 'success', 'response' => 'deleted']);
    }
}

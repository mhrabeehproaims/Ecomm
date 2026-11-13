<?php

namespace App\Http\Controllers;

use App\Models\WholesaleEnquiry;
use Illuminate\Http\Request;

class WholesaleEnquiryController extends Controller
{
    public function index()
    {
        $WholesaleEnquiry = WholesaleEnquiry::latest()->get();
        return view('WholesaleEnquiry.wholsale', ['WholesaleEnquiry' => $WholesaleEnquiry]);
    }
    public function updateStatus(Request $request)
    {

        $validated = $request->validate([
            'id' => 'required|integer|exists:wholesale_enquiries,id',
            'status' => 'required|in:pending,reviewed,resolved',
        ]);
        
        $enquiry = WholesaleEnquiry::findOrFail($validated['id']);
        $enquiry->update(['status' => $validated['status']]);

        // 3. Return success JSON
        return response()->json([
            'status' => 'success',
            'message' => 'Enquiry status updated successfully',
        ], 200);
    }
    public function destroy(String $id)
    {
        $enquiry = WholesaleEnquiry::findOrFail(decrypt($id));
        $enquiry->delete();

        return response()->json([
            'status' => 'success',
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class Customers extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $keyword = trim($request->query('user_name_phone'));
        $customers = Customer::latest();

        if ($keyword) {
            if (preg_match('/^[0-9\+\-\s]+$/', $keyword)) {
                // Search by phone number
                $customers->where('phone', 'LIKE', "%{$keyword}%");
            } else {
                // Search by name
                $customers->where(function ($q) use ($keyword) {
                    $q->where('first_name', 'LIKE', "%{$keyword}%")
                        ->orWhere('last_name', 'LIKE', "%{$keyword}%");
                });
            }
        }

        $customers = $customers->paginate(10)->appends(['user_name_phone' => $keyword]);
        return view('Customers.customers', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $customer = Customer::where('customer_id', $id)->first();
        return view('Customers.edit', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'first_name' => 'required|string',
            'last_name'  => 'nullable|string',
            'email'      => [
                'nullable',
                'email',
                Rule::unique('customers', 'email')->ignore($id, 'customer_id'),
            ],
            'phone'      => [
                'nullable',
                Rule::unique('customers', 'phone')->ignore($id, 'customer_id'),
            ],
            'address'    => 'nullable|string',
            'city'       => 'nullable|string',
            'state'      => 'nullable|string',
            'country'    => 'nullable|string',
            'zip'        => 'nullable|string',
            'status'     => 'nullable|integer',
        ]);

        $validated['status'] = $validated['status'] ?? 1;


        if ($request->hasFile('profile_pic')) {
            $file = $request->file('profile_pic');
            $image_name = time() . '_' . Str::random(5) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('CustomerImage', $image_name, 'public');
            $validated['profile_pic'] = $image_name;
        }

        $customer = Customer::findOrFail($id);
        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully!');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cust = Customer::findOrFail($id);
        $cust->delete();
        // if ($cust->image) {
        // Storage::disk('public')->delete('CustomerImage/' . $cat->image);
        // }
        return response()->json(['status' => 'success']);
    }

    public function updateStatus(Customer $customer)
    {
        $customer->status = !$customer->status;
        $customer->update();

        return response()->json([
            'message' => 'success',
            'status' =>  $customer->status == true ? 1 : 0
        ]);
    }

    public function purchase_history($encryptedId)
    {
        try {
            $customer_id = decrypt($encryptedId);
            $customer = Customer::findOrFail($customer_id);
            $fullname = $customer->full_name;
            $orders = Order::with('customer', 'items')->withCount('items')->where('customer_id', $customer_id)->latest()->paginate(10);
            return view('Customers.purchase_history', compact('orders', 'fullname'));
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'Invalid customer ID');
        }
    }
}

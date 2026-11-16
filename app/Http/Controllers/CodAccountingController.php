<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Storefront\CodAccounting;
use Illuminate\Http\Request;

final class CodAccountingController extends Controller
{
    public function store(Request $request, int $order, CodAccounting $accounting)
    {
        abort_unless($request->user('web') instanceof User, 403);
        $data = $request->validate(['amount_minor' => 'required_without:amount|integer|min:1', 'amount' => ['nullable', 'string', 'regex:/^\d+(?:\.\d{1,2})?$/D'], 'reference' => 'required|uuid',
            'receipt_reference' => 'required|string|min:3|max:200', 'kind' => 'required|in:collection,remittance']);
        $minor = isset($data['amount']) ? \App\Services\Storefront\Money::minor($data['amount']) : $data['amount_minor'];
        abort_if(isset($data['amount_minor']) && (int) $data['amount_minor'] !== $minor, 422, 'Conflicting receipt amounts.');
        $balance = $accounting->record($order, $minor, $data['reference'], $data['receipt_reference'], $request->user('web')->id, $data['kind'] === 'remittance');

        return $request->expectsJson() ? response()->json(['status' => 'success', 'balance' => $balance]) : back()->with('message', 'COD receipt recorded.');
    }
}

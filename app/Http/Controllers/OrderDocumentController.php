<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use App\Services\Storefront\OrderDocuments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final class OrderDocumentController extends Controller
{
    public function customer(Request $request, int $order, string $kind)
    {
        abort_unless(in_array($kind, ['invoice', 'packing-slip']), 404);
        $invoice = Invoice::where('order_id', $order)->first();
        abort_unless($invoice, 404);

        return $this->download($request, $invoice, $kind);
    }

    public function invoice(Request $request, string $filename, OrderDocuments $documents)
    {
        return $this->byFilename($request, $filename, 'invoice');
    }

    public function packingSlip(Request $request, string $filename)
    {
        return $this->byFilename($request, $filename, 'packing-slip');
    }

    private function byFilename(Request $request, string $filename, string $kind)
    {
        abort_unless(preg_match('/^[a-zA-Z0-9_-]+\.pdf$/D', $filename), 404);
        $number = substr($filename, 0, -4);
        if ($kind === 'packing-slip') {
            $number = preg_replace('/_packing_slip$/', '', $number);
        }
        $invoice = Invoice::where('invoice_number', $number)->first();
        abort_unless($invoice, 404);

        return $this->download($request, $invoice, $kind);
    }

    private function download(Request $request, Invoice $invoice, string $kind)
    {
        $admin = Auth::guard('web')->user();
        $customer = Auth::guard('sanctum')->user();
        $owner = DB::table('orders')->where('order_id', $invoice->order_id)->value('customer_id');
        $permitted = $admin instanceof User || ($customer instanceof Customer && (int) $customer->customer_id === (int) $owner
            && DB::table('customers')->where('customer_id', $owner)->where('status', 1)->exists());
        abort_unless($permitted, 404); // Same result for unknown, anonymous and foreign documents.
        $path = app(OrderDocuments::class)->file($invoice, $kind);

        return response()->download($path, $invoice->invoice_number.($kind === 'invoice' ? '.pdf' : '_packing_slip.pdf'),
            ['Content-Type' => 'application/pdf', 'Cache-Control' => 'private, no-store, max-age=0', 'X-Content-Type-Options' => 'nosniff', 'X-Robots-Tag' => 'noindex, nofollow']);
    }
}

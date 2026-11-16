<?php

namespace App\Services\Storefront;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class OrderDocuments
{
    public function preserveHistorical(Invoice $invoice, string $kind): void
    {
        CheckoutAccess::assertEnabled();
        abort_unless(in_array($kind, ['invoice', 'packing-slip']), 422);
        DB::transaction(function () use ($invoice, $kind) {
            DB::table('orders')->where('order_id', $invoice->order_id)->lockForUpdate()->first();
            if (DB::table('storefront_order_documents')->where('invoice_id', $invoice->id)->where('kind', $kind)->exists()) {
                return;
            }
            $existing = $this->file($invoice, $kind);
            $bytes = file_get_contents($existing);
            $path = 'historical/'.Str::uuid().'.pdf';
            Storage::disk('documents')->put($path, $bytes);
            abort_unless(hash_file('sha256', Storage::disk('documents')->path($path)) === hash('sha256', $bytes), 503, 'Historical document copy could not be verified.');
            DB::table('storefront_order_documents')->insert(['invoice_id' => $invoice->id, 'order_id' => $invoice->order_id, 'kind' => $kind,
                'path' => $path, 'sha256' => hash('sha256', $bytes), 'snapshot' => null, 'source' => 'legacy_preserved', 'created_at' => now(), 'updated_at' => now()]);
        }, 5);
    }

    public function snapshot(Invoice $invoice): array
    {
        $order = DB::table('orders')->where('order_id', $invoice->order_id)->first();
        $checkout = Schema::hasTable('storefront_checkouts') ? DB::table('storefront_checkouts')->where('order_id', $invoice->order_id)->first() : null;
        abort_unless($order && $checkout, 409, 'This legacy order has no verified historical delivery snapshot. Preserve its existing documents; operator review is required.');
        $data = json_decode($checkout->breakdown, true, flags: JSON_THROW_ON_ERROR);
        abort_unless(Money::minor($order->total_amount) === $data['total_minor'] && Money::minor($invoice->total_amount) === $data['total_minor'], 409, 'Invoice and accepted order totals require reconciliation.');

        return ['invoice_number' => $invoice->invoice_number, 'invoice_date' => (string) $invoice->invoice_date,
            'order_id' => (int) $order->order_id, 'order_date' => $order->created_at,
            'customer' => $data['customer_snapshot'] ?? null, 'address' => json_decode($checkout->address_snapshot, true),
            'pricing' => $data, 'tracking_number' => $order->delivery_tracking_id,
            'carrier' => $order->delivery_agent_id ? DB::table('delivery_partners')->where('partner_id', $order->delivery_agent_id)->value('partner_name') : null];
    }

    public function generate(int $orderId): Invoice
    {
        return DB::transaction(function () use ($orderId) {
            $order = DB::table('orders')->where('order_id', $orderId)->lockForUpdate()->first();
            abort_unless($order, 404);
            $invoice = Invoice::where('order_id', $orderId)->first();
            if ($invoice) {
                return $invoice;
            } // Never regenerate or silently rewrite issued documents.
            abort_unless(Schema::hasTable('storefront_checkouts') && DB::table('storefront_checkouts')->where('order_id', $orderId)->exists(), 409, 'Historical delivery evidence is missing; no invoice was fabricated.');
            $invoice = Invoice::create(['order_id' => $orderId, 'invoice_number' => 'INV'.str_pad((string) $orderId, 7, '0', STR_PAD_LEFT),
                'invoice_date' => now()->toDateString(), 'total_amount' => $order->total_amount]);
            $snapshot = $this->snapshot($invoice);
            foreach (['invoice', 'packing-slip'] as $kind) {
                $pdf = Pdf::loadView($kind === 'invoice' ? 'invoices.pdf' : 'invoices.packing_slip', ['document' => $snapshot])
                    ->setPaper($kind === 'invoice' ? 'A4' : 'A5')->setOption('isRemoteEnabled', false)->setOption('isPhpEnabled', false)->setOption('isFontSubsettingEnabled', true);
                $bytes = $pdf->output();
                $path = 'order-documents/'.Str::uuid().'.pdf';
                abort_unless(Storage::disk('documents')->put($path, $bytes), 503, 'Document storage is unavailable.');
                DB::table('storefront_order_documents')->insert(['invoice_id' => $invoice->id, 'order_id' => $orderId, 'kind' => $kind,
                    'path' => $path, 'sha256' => hash('sha256', $bytes), 'snapshot' => json_encode($snapshot), 'source' => 'order_snapshot', 'created_at' => now(), 'updated_at' => now()]);
            }

            return $invoice;
        }, 5);
    }

    public function file(Invoice $invoice, string $kind): string
    {
        $row = Schema::hasTable('storefront_order_documents') ? DB::table('storefront_order_documents')->where('invoice_id', $invoice->id)->where('kind', $kind)->first() : null;
        if ($row) {
            $path = Storage::disk('documents')->path($row->path);
            abort_unless(is_file($path) && hash_equals($row->sha256, hash_file('sha256', $path)), 404);

            return $path;
        }
        // Authenticated access to an existing historical PDF preserves its bytes.
        $filename = $invoice->invoice_number.($kind === 'invoice' ? '.pdf' : '_packing_slip.pdf');
        abort_unless(preg_match('/^[a-zA-Z0-9_-]+\.pdf$/D', $filename), 404);
        $path = Storage::disk('public')->path(($kind === 'invoice' ? 'Invoices/' : 'PackingSlips/').$filename);
        abort_unless(is_file($path), 404);

        return $path;
    }
}

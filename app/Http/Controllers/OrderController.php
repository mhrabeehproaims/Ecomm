<?php

namespace App\Http\Controllers;

use App\Events\orderShippedEvent;
use App\Mail\OrderDeliveredMail;
use App\Models\DeliveryFollowup;
use App\Models\DeliveryPartner;
use app\Models\Invoice;
use App\Models\Order;
use App\Models\OrderStatusLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
{
    // public function index()
    // {
    //     $orders = Order::with('customer', 'items')->withCount('items')->latest()->paginate(10);
    //     return view('Order.orders', compact('orders'));
    // }
    public function index(Request $request)
    {
        $delivery_agents = DeliveryPartner::all();

        // Get filter parameter (from both GET and AJAX requests)
        $search_status = $request->input('search_status', null) ?: $request->input('status_filter', null);
        $dateFrom = $request->input('dateFrom', null) ?: $request->input('date_from', null);
        $dateTo = $request->input('dateTo', null) ?: $request->input('date_to', null);

        // Calculate stats (always show today's stats regardless of filter)
        $today_order_count = Order::whereDate('created_at', today())->count();

        $today_order_total = Order::whereDate('created_at', today())->sum('total_amount');

        $today_delivered_count = Order::where('status', 'delivered')
            ->whereDate('delivery_date', today())
            ->count();

        // Handle AJAX request for DataTables
        if ($request->ajax()) {
            $orders = Order::select([
                'orders.*',
                'customers.first_name',
                'customers.last_name',
                'customers.phone',
            ])
                ->leftJoin('customers', 'customers.customer_id', '=', 'orders.customer_id')
                // Apply status filter from AJAX request
                ->when($request->input('status_filter'), function ($query, $status) {
                    return $query->where('orders.status', $status);
                })
                ->when($request->input('date_from'), function ($query, $dateFrom) {
                    return $query->whereDate('orders.created_at', '>=', $dateFrom);
                })
                ->when($request->input('date_to'), function ($query, $dateTo) {
                    return $query->whereDate('orders.created_at', '<=', $dateTo);
                })
                ->orderBy('orders.created_at', 'desc');

            return DataTables::of($orders)
                ->addIndexColumn()
                ->editColumn('order_id', fn ($row) => '#VAN'.str_pad($row->order_id, 7, '0', STR_PAD_LEFT))
                ->editColumn('ref_order', function ($row) {
                    $icon = $row->coupon_discount
                        ? '<i class="bx bxs-discount fs-5 p-0 m-0 ms-1"></i><br>₹'.$row->coupon_discount
                        : '';

                    if ($row->reference_order_id) {
                        return '<span class="badge bg-warning p-1 text-dark fw-bold d-inline-block">
                    #VAN'.str_pad($row->reference_order_id, 7, '0', STR_PAD_LEFT).'<br>'.$icon.'
                </span>';
                    }

                    return '<span class="badge bg-success p-1 text-white d-inline-block">
                New Order<br>'.$icon.'
            </span>';
                })
                ->rawColumns(['ref_order'])
                ->addColumn('date', fn ($row) => $row->created_at?->format('d-m-Y h:i A'))
                ->addColumn('payment_status', fn ($row) => $row->payment_status ?? 'N/A')
                ->addColumn('customer_name', fn ($row) => ucwords($row->customer?->first_name).' '.ucwords($row->customer?->last_name) ?? 'N/A')
                ->addColumn('customer_contact', fn ($row) => $row->customer?->phone ?? 'N/A')
                ->addColumn('delivery_loc', fn ($row) => $row->customer?->defaultDeliveryAddress->city ?? 'N/A')
                ->addColumn('created_at', fn ($row) => $row->customer?->created_at ?? 'N/A')
                ->addColumn('order_status', function ($row) {
                    $status = strtolower($row->status ?? 'ordered');

                    $colors = [
                        'ordered' => 'btn-sm btn-secondary',
                        'rejected' => 'btn-sm btn-danger',
                        'confirmed' => 'btn-sm btn-primary',
                        'processing' => 'btn-sm btn-info',
                        'despatched' => 'btn-sm btn-warning',
                        'ontransist' => 'btn-sm btn-dark',
                        'delivered' => 'btn-sm btn-success',
                        'reissued' => 'btn-sm btn-primary',
                    ];

                    $colorClass = $colors[$status] ?? 'btn-light';

                    return '<button class="btn btn-sm statusBtn fw-bold '.$colorClass.'"
                    data-curstatus="'.e($row->status).'"
                    data-id="'.e($row->order_id).'" '
                        .($row->status == 'delivered' ? 'disabled' : '').'>'
                        .ucfirst($status)
                        .'</button>';
                })
                ->addColumn('action', function ($row) {
                    $url = route('order.show', encrypt($row->order_id));

                    $orderBtn = '<a href="'.$url.'" class="btn btn-light btn-sm me-1 mb-1">
                    <i class="bx bx-show"></i> Order Details
                 </a>';

                    $downloadBtn = $row->invoice
                        ? '<a href="'.asset('storage/Invoices/'.$row->invoice->invoice_number.'.pdf').'" 
                        download="'.$row->invoice->invoice_number.'.pdf" 
                        class="btn btn-secondary btn-sm me-1 mb-1">
                        <i class="bx bx-download"></i> Invoice
                    </a>'
                        : '';

                    $delSlipBtn = $row->invoice
                        ? '<a href="'.asset('storage/PackingSlips/'.$row->invoice->invoice_number.'_packing_slip.pdf').'" 
                        download="'.$row->invoice->invoice_number.'_packing_slip.pdf" 
                        class="btn btn-success btn-sm mb-1">
                        <i class="bx bx-download"></i> Slip
                    </a>'
                        : '';

                    return $orderBtn.$downloadBtn.$delSlipBtn;
                })
                ->rawColumns(['order_status', 'action', 'ref_order'])
                ->make(true);
        }

        return view('Order.orders', [
            'delivery_agents' => $delivery_agents,
            'today_order_count' => $today_order_count,
            'today_order_total' => $today_order_total,
            'today_delivered_count' => $today_delivered_count,
            'search_status' => $search_status,  // Pass selected status to view
        ]);
    }

    public function show($id)
    {
        $id = decrypt($id);
        $delivery_agents = DeliveryPartner::all();
        $order_details = Order::where('order_id', $id)->with('items', 'statusLogs', 'latestStatus', 'payment')->first();

        if ($order_details) {
            return view('Order.order-details', compact('order_details', 'delivery_agents'));
        } else {
            return back()->with(['title' => 'Failed', 'message' => 'This order does not exist!']);
        }
    }

    public function update_status($id, Request $request)
    {
        $request->validate([
            'status' => 'required|in:ordered,rejected,confirmed,processing,despatched,ontransist,delivered',
            'unique_tracking_id' => 'nullable|required_if:status,despatched|string|max:255',
            'approx_delivery_date' => 'nullable|required_if:status,despatched|date',
            'selectAgent' => 'nullable|required_if:status,despatched|exists:delivery_partners,partner_id',
            'approx_delivery_charge' => 'nullable|numeric',
        ]);

        $status = $request->status;
        if (\Illuminate\Support\Facades\Schema::hasTable('storefront_checkouts')
            && ($secure = \Illuminate\Support\Facades\DB::table('storefront_checkouts')->where('order_id', $id)->first())) {
            $record = \Illuminate\Support\Facades\DB::table('orders')->where('order_id', $id)->first();
            $quotedShipping = json_decode($secure->breakdown, true)['shipping_minor'];
            if ($request->filled('approx_delivery_charge') && \App\Services\Storefront\Money::minor($request->input('approx_delivery_charge')) !== $quotedShipping) {
                throw \Illuminate\Validation\ValidationException::withMessages(['approx_delivery_charge' => 'The accepted checkout delivery charge cannot be changed during fulfillment.']);
            }
            $request->merge(['approx_delivery_charge' => \App\Services\Storefront\Money::decimal($quotedShipping)]);
            if ($status === 'rejected' && $secure->state === 'reserved') {
                app(\App\Services\Storefront\CheckoutService::class)->release($record->customer_id, (int) $id);

                return response()->json(['status' => 'success', 'status2' => $status]);
            }
            if (in_array($status, ['confirmed', 'processing', 'despatched', 'ontransist', 'delivered'])) {
                abort_unless($secure->state === 'committed' && ($record->payment_method !== 'online' || $record->payment_status === 'paid'), 409, 'Verified capture and a committed reservation are required before fulfillment.');
            }
        }
        $order = Order::findOrFail($id);
        $order->update(['status' => $status]);

        if ($status == 'despatched') {
            $deliverAgent = [
                'delivery_tracking_id' => $request->input('unique_tracking_id'),
                'delivery_date' => $request->input('approx_delivery_date'),
                'delivery_agent_id' => $request->input('selectAgent'),
                'delivery_charge' => $request->input('approx_delivery_charge') ?? 0,
            ];
            $order->update($deliverAgent);

            $invoicePath = $this->generateInvoice($order);
            if ($order->customer?->email) {
                $invoicePath = asset('storage/'.$invoicePath);
                event(new orderShippedEvent($order, $invoicePath));
            }

            // old
            // if ($order->customer?->email) {
            //     event(new orderShippedEvent($order, $invoicePath));
            // }
        }

        // if ($status == "delivered") {
        $data = [
            'order_id' => $order->order_id,
            'note' => 'Added from Orders.',
            'delivery_status' => $status,
            'email' => $order->customer?->email ?? '',
        ];

        $request = new Request($data);
        $this->add_followup($request);

        // }

        // $log_data = [
        //     'order_id' => $id,
        //     'status' => $status,
        //     'changed_by' => 1, //Admin
        //     'remarks' => '',
        //     'changed_at' => date('Y-m-d h:i:s')
        // ];
        // OrderStatusLog::create($log_data);
        return response()->json([
            'status' => 'success',
            'status2' => $status,
        ]);
    }

    protected function generateInvoice($order)
    {
        // If invoice already exists, reuse the old PDF path
        if ($order->invoice) {
            return 'Invoices/'.$order->invoice->invoice_number.'.pdf';
        }

        // ✅ Generate a new invoice number
        $invoiceNumber = 'INV'.str_pad($order->order_id, 7, '0', STR_PAD_LEFT);

        // Create and save the invoice record
        $invoice = new Invoice;
        $invoice->order_id = $order->order_id;
        $invoice->invoice_number = $invoiceNumber;
        $invoice->invoice_date = now();
        $invoice->total_amount = $order->total_amount;
        $invoice->save();

        // ✅ Generate PDF and return the path
        $this->generatePackingSlip($invoice);

        return $this->generateInvoicePDF($invoice);
    }

    protected function generateInvoicePDF($invoice)
    {
        // Generate PDF content from the Blade view
        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));

        // Create the file name
        $fileName = 'Invoices/'.$invoice->invoice_number.'.pdf';

        // Store the PDF into the 'public' disk
        Storage::disk('public')->put($fileName, $pdf->output());

        // Optional: return the file path if you need it
        return $fileName;
    }

    protected function generatePackingSlip($invoice)
    {
        $pdf = Pdf::loadView('invoices.packing_slip', compact('invoice'))
            ->setPaper('A5', 'portrait');  // A5 fits well on boxes

        $fileName = 'PackingSlips/'.$invoice->invoice_number.'_packing_slip.pdf';

        Storage::disk('public')->put($fileName, $pdf->output());

        return $fileName;
    }

    public function today_deliveries(Request $request)
    {
        $keyword = trim($request->query('user_name_phone'));

        $today_deliveries = Order::with('customer')
            ->whereDate('delivery_date', now())
            ->when($keyword, function ($query, $keyword) {
                $query->whereHas('customer', function ($q) use ($keyword) {
                    if (preg_match('/^[0-9\+\-\s]+$/', $keyword)) {
                        // Search by phone number
                        $q->where('phone', 'LIKE', "%{$keyword}%");
                    } else {
                        // Search by name
                        $q
                            ->where('first_name', 'LIKE', "%{$keyword}%")
                            ->orWhere('last_name', 'LIKE', "%{$keyword}%");
                    }
                });
            })
            ->latest()
            ->paginate(10);

        return view('Order.today_deliveries', compact('today_deliveries'));
    }

    public function add_followup(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|exists:orders,order_id',
            'note' => 'nullable|string',
            'delivery_status' => 'required|string',
            'email' => 'nullable|email',
        ]);

        $order = Order::find($data['order_id']);

        if ($data['delivery_status'] === 'delivered' && \Illuminate\Support\Facades\Schema::hasTable('storefront_checkouts')
            && ($secure = \Illuminate\Support\Facades\DB::table('storefront_checkouts')->where('order_id', $order->order_id)->first())) {
            abort_unless($secure->state === 'committed' && ($order->payment_method !== 'online' || $order->payment_status === 'paid'), 409, 'Verified capture and a committed reservation are required before delivery.');
        }

        if (in_array($data['delivery_status'], ['call', 'called', 'follow-up', 'delivered'])) {
            DeliveryFollowup::create([
                'order_id' => $data['order_id'],
                'note' => $data['note'] ?? null,
                'delivery_status' => $data['delivery_status'],
            ]);

            if ($data['delivery_status'] === 'delivered') {
                $order->update(['status' => $data['delivery_status']]);
                if (! empty($data['email'])) {
                    Mail::to($data['email'])->send(new OrderDeliveredMail($order));
                }
            }
        }

        $log_data = [
            'order_id' => $data['order_id'],
            'status' => $data['delivery_status'] === 'ontransist' ? 'ontransit' : $data['delivery_status'],
            'changed_by' => 1,  // Admin
            'remarks' => '',
            'changed_at' => date('Y-m-d h:i:s'),
        ];
        if (! in_array($data['delivery_status'], ['call', 'called', 'follow-up'])) {
            OrderStatusLog::create($log_data);
        }

        return response()->json([
            'success' => true,
            'status' => ucfirst($data['delivery_status']),
        ]);
    }

    public function get_followup_history($id)
    {
        $followups = DeliveryFollowup::whereOrderId($id)->get();
        if ($followups) {
            $data = [
                'status' => 'success',
                'data' => $followups,
            ];
        } else {
            $data = [
                'status' => 'failed',
                'data' => '',
            ];
        }

        return response()->json($data);
    }
}

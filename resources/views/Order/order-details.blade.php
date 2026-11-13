@extends('layouts.app')
@push('styles')
    {{-- image view lib --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.3/viewer.min.css" />

    {{-- to adjust image viwer bg color --}}
    <style>
        .viewer-backdrop {
            background-color: rgba(0, 0, 0, 0.7) !important;
        }

        .page-content * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .breadcrumb {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            /* padding: 0px 20px; */
            border-radius: 12px;
            margin-bottom: 5px;
            /* color: white; */
            font-size: 14px;
            display: flex;
            justify-content: end;
        }

        .breadcrumb a {
            color: rgba(17, 6, 6, 0.8);
            text-decoration: none;
            transition: color 0.3s;
        }

        .breadcrumb a:hover {
            color: rgb(16, 14, 14);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title {
            /* color: white; */
            font-size: 18px;
            font-weight: 600;
        }

        .status-badge {
            background: linear-gradient(135deg, #02a01a 0%, #36d103 100%);
            /* background: #02a01a; */
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-weight: 500;
            font-size: 13px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            /* animation: pulse 2s infinite; */
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .grid-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        .card {
            background: white;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 16px;
            border-bottom: 2px solid #f0f0f0;
        }

        .card-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
            font-size: 24px;
        }

        .card-title {
            font-size: 20px;
            font-weight: 700;
            color: #2d3748;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid #f7fafc;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #718096;
            font-size: 14px;
            font-weight: 500;
        }

        .info-value {
            color: #2d3748;
            font-size: 15px;
            font-weight: 600;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        .items-container {
            display: grid;
            gap: 16px;
        }

        .item-card {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border-radius: 12px;
            padding: 20px;
            /* border-left: 4px solid #667eea; */
            transition: all 0.3s;
        }

        .item-card:hover {
            background: linear-gradient(135deg, #edf2f7 0%, #e2e8f0 100%);
            /* border-left-width: 6px; */
        }

        .item-header {
            font-size: 16px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 12px;
        }

        .item-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px;
        }

        .item-detail {
            display: flex;
            flex-direction: column;
        }

        .item-detail-label {
            font-size: 11px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .item-detail-value {
            font-size: 14px;
            color: #2d3748;
            font-weight: 600;
        }

        .timeline {
            position: relative;
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px 0;
            overflow-x: auto;
            /* Allow horizontal scrolling if needed */
        }

        /*  */

        .timeline-item {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 120px;
            /* Adjust as needed */
            z-index: 1;
        }

        .timeline-dot {
            position: relative;
            left: 0;
            width: 20px;
            height: 20px;
            background: rgb(255, 255, 255);
            border: 3px solid #667eea;
            border-radius: 50%;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            margin-bottom: 12px;
        }

        /* Tick mark for all dots except the last one (unless delivered) */
        .timeline-dot::after {
            content: '';
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -60%) rotate(45deg);
            width: 5px;
            height: 9px;
            border: solid #667eea;
            border-width: 0 2px 2px 0;
        }

        /* Last status - Orange background, no tick */
        .timeline-dot:last-of-type::after {
            display: none;
        }

        .timeline-dot:last-of-type {
            background: linear-gradient(135deg, #ff9500 0%, #ff7b00 100%);
            border-color: #ff9500;
            box-shadow: 0 4px 15px rgba(255, 149, 0, 0.4);
        }

        /* Delivered status - Green background, white tick */
        .timeline-dot.delivered {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            border-color: #48bb78;
            box-shadow: 0 4px 15px rgba(72, 187, 120, 0.4);
        }

        .timeline-dot.delivered::after {
            display: block;
            border-color: white;
        }

        /* Arrow between items */
        .timeline-item:not(:last-child)::after {
            content: '→';
            position: absolute;
            right: -25px;
            top: 25px;
            font-size: 20px;
            color: #667eea;
            z-index: 2;
        }

        .timeline-item:(:last-child)::after {

            background: rgb(12, 10, 10);

        }

        .timeline-status {
            font-weight: 600;
            color: #2d3748;
            font-size: 15px;
            margin-bottom: 4px;
            text-align: center;
        }

        .timeline-date {
            color: #718096;
            font-size: 13px;
            text-align: center;
            white-space: nowrap;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #718096;
            font-style: italic;
        }

        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }

        .product-image:hover {
            transform: scale(2.1);
        }

        .disabled-status {
            opacity: 0.6;
            cursor: not-allowed;
            pointer-events: none;
            /* prevents click */
        }

        @media (max-width: 968px) {
            .grid-container {
                grid-template-columns: 1fr;
            }

            .item-details {
                grid-template-columns: 1fr;
            }

            .product-image {
                width: 50px;
                height: 50px;
            }

            .product-name {
                font-size: 16px;
            }
        }
    </style>
@endpush
@section('title', 'Order Management')
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="breadcrumb">
                    <a href="#">Dashboard &nbsp; </a> / <span> &nbsp; Order Management</span>
                </div>

                <div class="page-header">
                    <h1 class="page-title">ORDER DETAILS</h1>
                    {!! $order_details->invoice
        ? '<a href="' .
        asset('storage/Invoices/' . $order_details->invoice->invoice_number . '.pdf') .
        '" 
                                                                                                                                                                                                    download="' .
        $order_details->invoice->invoice_number .
        '.pdf" 
                                                                                                                                                                                                       class="btn btn-success btn-sm">
                                                                                                                                                                                                       <i class="bx bx-download"></i> Invoice
                                                                                                                                                                                                   </a>'
        : '' !!}
                    @if ($order_details->latestStatus)
                        {{-- <div class="status-badge">● {{ $order_details->latestStatus?->status ?? '' }}</div> --}}
                        <div class="statusBtn status-badge fw-bold cur-status {{ $order_details->latestStatus?->status == 'delivered' ? 'disabled-status' : '' }}"
                            style="cursor: pointer;" data-curstatus="{{ $order_details->latestStatus?->status }}"
                            data-id="{{ $order_details->order_id }}">
                            ● {{ ucwords($order_details->latestStatus?->status) }}
                        </div>
                    @endif
                </div>
                <!-- Order Timeline -->
                <div class="card full-width">
                    <div class="card-header">
                        <div class="card-icon">📊</div>
                        <h2 class="card-title">Order Timeline</h2>
                    </div>

                    <div class="timeline">
                        @forelse ($order_details->statusLogs ?? [] as $logs)
                            <div class="timeline-item">
                                <div class="timeline-dot {{ $loop->last ? 'delivered' : '' }}"></div>
                                <div class="timeline-status">{{ ucwords($logs->status) }}</div>
                                <div class="timeline-date">{{ $logs->created_at->format('d-m-Y h:i a') }}</div>
                            </div>

                        @empty
                            <div class="timeline-item">
                                <span>No Logs on this order</span>
                            </div>
                        @endforelse
                    </div>
                </div>
                <div class="grid-container">
                    <!-- Customer Information -->
                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon">👤</div>
                            <h2 class="card-title">Customer Information</h2>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Customer Name</span>
                            <span
                                class="info-value">{{ $order_details->customer?->first_name ?? ('' . ' ' . $order_details->customer?->last_name ?? '') }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Phone Number</span>
                            <span class="info-value">{{ $order_details->customer->phone ?? '' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">City</span>
                            <span class="info-value">{{ $order_details->customer->city ?? '' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">State</span>
                            <span class="info-value">{{ $order_details->customer->state ?? '' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Coupon Code Applied</span>
                            <span class="info-value">{{ $order_details->coupon_code ?? 'No' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Coupon Value</span>
                            <span class="info-value">{{ $order_details->formatted_coupon_value }}</span>
                        </div>
                    </div>

                    <!-- Payment Details -->
                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon">💳</div>
                            <h2 class="card-title">Payment Details
                                {{ $order_details ? '(' . ucwords($order_details->payment_method) . ')' : '' }}
                            </h2>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Order Amount</span>
                            <span
                                class="info-value">{{ $order_details->total_amount + $order_details->discount ?? 0 }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Coupon Discount</span>
                            <span class="info-value">{{ $order_details->discount ?? 0 }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Total After Discount</span>
                            <span class="info-value">{{ $order_details->total_amount ?? 0 }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Tax Amount</span>
                            <span class="info-value">{{ $order_details->total_tax_amount ?? '' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Payment Status</span>
                            <span class="info-value">{{ strtoupper($order_details->payment_status) ?? '' }}</span>
                        </div>
                        <div class="info-row">
                            <span
                                class="info-label">{{ ($order_details->payment_status == "paid" || $order_details->payment_status == "") ? "Paid Date" : "Reason" }}</span>
                            <span class="info-value">
                                @if($order_details->payment_status == "paid")
                                    {{ optional($order_details?->paid_at)->format('d-m-Y h:i A') ?? '' }}
                                @else
                                    {{ ucfirst($order_details->payment?->meta['reason'] ?? '') }}
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="card full-width">
                        <div class="card-header mb-2">
                            <div class="card-icon">🏠</div>
                            <h2 class="card-title">Delivery Address</h2>
                        </div>
                        <div class="items-container">
                            <div class="item-card">
                                <div class="item-details">
                                    <div class="item-detail">
                                        <span class="item-detail-label">Address Line 1</span>
                                        <span
                                            class="item-detail-value">{{ ucwords($order_details?->customer?->defaultDeliveryAddress?->address_line1 ?? '') }}</span>
                                    </div>
                                    @if ($order_details?->customer?->defaultDeliveryAddress?->address_line2)
                                        <div class="item-detail">
                                            <span class="item-detail-label">Address Line 2</span>
                                            <span
                                                class="item-detail-value">{{ ucwords($order_details?->customer?->defaultDeliveryAddress?->address_line2) }}</span>
                                        </div>
                                    @endif
                                    <div class="item-detail">
                                        <span class="item-detail-label">City</span>
                                        <span
                                            class="item-detail-value">{{ ucwords($order_details?->customer?->defaultDeliveryAddress?->city ?? '') }}</span>
                                    </div>
                                    <div class="item-detail">
                                        <span class="item-detail-label">State</span>
                                        <span
                                            class="item-detail-value">{{ ucwords($order_details?->customer?->defaultDeliveryAddress?->state ?? '') }}</span>
                                    </div>
                                    <div class="item-detail">
                                        <span class="item-detail-label">Pin Code</span>
                                        <span
                                            class="item-detail-value">{{ $order_details?->customer?->defaultDeliveryAddress?->postal_code ?? '' }}</span>
                                    </div>
                                    <div class="item-detail">
                                        <span class="item-detail-label">Country</span>
                                        <span
                                            class="item-detail-value">{{ ucwords($order_details?->customer?->defaultDeliveryAddress?->country ?? '') }}</span>
                                    </div>
                                    <div class="item-detail">
                                        <span class="item-detail-label">Phone</span>
                                        <span
                                            class="item-detail-value">{{ $order_details?->customer?->defaultDeliveryAddress?->phone ?? '' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ordered Items -->
                    <div class="card full-width">
                        <div class="card-header mb-2">
                            <div class="card-icon">📦</div>
                            <h2 class="card-title">Ordered Items</h2>
                        </div>
                        <div class="items-container">
                            @forelse ($order_details?->items as $item)
                                <div class="item-card">
                                    <div class="item-header">{{ ucwords($item->product?->product_name) }}</div>
                                    <div class="product-info mb-2">
                                        @if($item->product?->primaryImage)
                                            <img src="{{ $item->product?->primaryImage?->image_path ? asset('storage/' . $item->product->primaryImage->image_path) : asset('images/placeholder.png') }}"
                                                alt="{{ $item->product?->product_name }}" class="product-image">
                                        @endif
                                    </div>
                                    <div class="item-details">
                                        <div class="item-detail">
                                            <span class="item-detail-label">Quantity</span>
                                            <span class="item-detail-value">{{ $item->quantity }}</span>
                                        </div>

                                        @php
                                            $coupon_discount = $item->order->discount;
                                        @endphp

                                        @if ($coupon_discount > 0 || $item->offer_applied == 1)
                                            <div class="item-detail">
                                                <span class="item-detail-label">Actual Price (Single)</span>
                                                <span
                                                    class="item-detail-value">{{ $item->actual_price > 0 ? $item->actual_price : $item->price }}</span>
                                            </div>
                                        @endif

                                        <div class="item-detail">
                                            <span class="item-detail-label">Price (Single)</span>
                                            <span class="item-detail-value">{{ $item->price }}</span>
                                        </div>

                                        <div class="item-detail">
                                            <span class="item-detail-label">Total W/o Tax</span>
                                            <span
                                                class="item-detail-value">{{ $item->amount_wo_tax ? ' ₹' . $item->amount_wo_tax : '' }}</span>
                                        </div>
                                        <div class="item-detail">
                                            <span class="item-detail-label">Tax (%)</span>
                                            <span
                                                class="item-detail-value">{{ $item->tax_percent ? $item->tax_percent . ' (%)' : '' }}</span>
                                        </div>
                                        @if ($order_details->delivery_state == 'kerala')
                                            <div class="item-detail">
                                                <span class="item-detail-label">CGST (%)</span>
                                                <span
                                                    class="item-detail-value">{{ $item->cgst_percent ? $item->cgst_percent . ' (%)' : '' }}</span>
                                            </div>
                                            <div class="item-detail">
                                                <span class="item-detail-label">SGST (%)</span>
                                                <span
                                                    class="item-detail-value">{{ $item->sgst_percent ? $item->sgst_percent . ' (%)' : '' }}</span>
                                            </div>
                                            <div class="item-detail">
                                                <span class="item-detail-label">CGST Amount</span>
                                                <span
                                                    class="item-detail-value">{{ $item->cgst_amount ? ' ₹' . $item->cgst_amount : '' }}</span>
                                            </div>
                                            <div class="item-detail">
                                                <span class="item-detail-label">SGST Amount</span>
                                                <span
                                                    class="item-detail-value">{{ $item->sgst_amount ? ' ₹' . $item->sgst_amount : '' }}</span>
                                            </div>
                                        @else
                                            <div class="item-detail">
                                                <span class="item-detail-label">IGST (%)</span>
                                                <span
                                                    class="item-detail-value">{{ $item->igst_percent ? $item->igst_percent . ' (%)' : '' }}</span>
                                            </div>

                                            <div class="item-detail">
                                                <span class="item-detail-label">IGST Amount</span>
                                                <span
                                                    class="item-detail-value">{{ $item->igst_amount ? ' ₹' . $item->igst_amount : '' }}</span>
                                            </div>
                                        @endif
                                        <div class="item-detail">
                                            <span class="item-detail-label fs-6 fw-semibold">Total</span>
                                            <span class="fw-semibold fs-4">{{ '₹ ' . $item->total }}</span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <span>No Items on this order</span>
                            @endforelse

                            {{-- <div class="item-card">
                                <div class="item-header">Smart Watch Pro</div>
                                <div class="item-details">
                                    <div class="item-detail">
                                        <span class="item-detail-label">Quantity</span>
                                        <span class="item-detail-value">1</span>
                                    </div>
                                    <div class="item-detail">
                                        <span class="item-detail-label">Price</span>
                                        <span class="item-detail-value">$750.00</span>
                                    </div>
                                    <div class="item-detail">
                                        <span class="item-detail-label">Total</span>
                                        <span class="item-detail-value">$750.00</span>
                                    </div>
                                    <div class="item-detail">
                                        <span class="item-detail-label">Tax (%)</span>
                                        <span class="item-detail-value">18%</span>
                                    </div>
                                    <div class="item-detail">
                                        <span class="item-detail-label">CGST (%)</span>
                                        <span class="item-detail-value">9%</span>
                                    </div>
                                    <div class="item-detail">
                                        <span class="item-detail-label">SGST (%)</span>
                                        <span class="item-detail-value">9%</span>
                                    </div>
                                    <div class="item-detail">
                                        <span class="item-detail-label">CGST Amount</span>
                                        <span class="item-detail-value">$67.50</span>
                                    </div>
                                    <div class="item-detail">
                                        <span class="item-detail-label">SGST Amount</span>
                                        <span class="item-detail-value">$67.50</span>
                                    </div>
                                </div>
                            </div> --}}
                        </div>
                    </div>
                </div>
                <div class="page-header d-flex justify-content-end">
                    @if ($order_details->latestStatus)
                        {{-- <div class="status-badge">● {{ $order_details->latestStatus?->status ?? '' }}</div> --}}
                        <div class="statusBtn status-badge fw-bold cur-status {{ $order_details->latestStatus?->status == 'delivered' ? 'disabled-status' : '' }}"
                            style="cursor: pointer;" data-curstatus="{{ $order_details->latestStatus?->status }}"
                            data-id="{{ $order_details->order_id }}">
                            ● {{ ucwords($order_details->latestStatus?->status) }}
                        </div>
                    @endif
                </div>
            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->
        <x-change-order-status-component :delivery_agents="$delivery_agents" />
        <x-footer />
    </div>
@endsection

@push('page-scripts')
    <script src="{{ asset('assets/js/enquiry.js') }}"></script>
@endpush
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

        .statusBtn:hover {
            cursor: pointer;
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
            padding-left: 40px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 12px;
            top: 0;
            bottom: 0;
            width: 2px;
            /* background: linear-gradient(180deg, #667eea 0%, #764ba2 100%); */
        }

        .timeline-item {
            position: relative;
            padding: 16px 0;
        }

        .timeline-dot {
            position: absolute;
            left: -33px;
            width: 24px;
            height: 24px;
            background: white;
            border: 3px solid #667eea;
            border-radius: 50%;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .timeline-status {
            font-weight: 600;
            color: #2d3748;
            font-size: 15px;
            margin-bottom: 4px;
        }

        .timeline-date {
            color: #718096;
            font-size: 13px;
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

        @media (max-width: 968px) {
            .grid-container {
                grid-template-columns: 1fr;
            }

            .product-image {
                width: 50px;
                height: 50px;
            }
        }
    </style>
@endpush
@section('title', 'Return Management')
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="breadcrumb">
                    <a href="#">Dashboard &nbsp; </a> / <span> &nbsp; Return Management</span>
                </div>

                <div class="page-header">
                    <h1 class="page-title">SALES RETURN DETAILS</h1>
                    <div class="status-badge statusBtn" data-curstatus="{{ $return_details->status ?? '' }}"
                        data-id="{{ $return_details->return_id ?? '' }}"
                        data-return_type="{{ $return_details->return_type ?? '' }}" href="#"><strong>●
                            {{ \Illuminate\Support\Str::headline($return_details->status ?? '') }}</strong></div>
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
                                class="info-value">{{ $return_details->customer?->first_name ?? ('' . ' ' . $return_details->customer?->last_name ?? '') }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Phone Number</span>
                            <span class="info-value">{{ $return_details->customer->phone ?? '' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">City</span>
                            <span class="info-value">{{ $return_details->customer->city ?? '' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">State</span>
                            <span class="info-value">{{ $return_details->customer->state ?? '' }}</span>
                        </div>

                    </div>

                    <!-- Payment Details -->
                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon">💳</div>
                            <h2 class="card-title">Payment Details</h2>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Total Return Amount</span>
                            <span class="info-value">{{ $return_details->total_amount ?? 0 }}</span>
                        </div>


                        {{-- <div class="info-row">
                            <span class="info-label">Tax Amount</span>
                            <span class="info-value">{{ $return_details->total_tax_amount ?? '' }}</span>
                        </div> --}}
                        {{-- <div class="info-row">
                            <span class="info-label">Payment Status</span>
                            <span class="info-value">
                                @if ($return_details->status == 'paid')
                                    {{ ucwords($return_details->status) ?? '' }}
                                @else
                                    Not Paid
                                @endif
                            </span>
                        </div> --}}
                        {{-- <div class="info-row">
                            <span class="info-label">Paid Date</span>
                            <span class="info-value">{{ optional($return_details?->paid_at)->format('d-m-Y') ?? '' }}</span>
                        </div> --}}
                        <div class="info-row">
                            <span class="info-label">Current Status</span>
                            <span
                                class="info-value curStatus">{{ \Illuminate\Support\Str::headline($return_details->status ?? '') }}</span>
                        </div>
                    </div>

                    <!-- Returned Items -->
                    <div class="card full-width">
                        <div class="card-header mb-2">
                            <div class="card-icon">📦</div>
                            <h2 class="card-title">Returned Items</h2>
                        </div>
                        <div class="items-container">
                            @forelse ($return_details?->items as $item)
                                <div class="item-card">
                                    <div class="product-info mb-2">
                                        <div class="item-header">{{ $item->product?->product_name }}</div>
                                        <img src="{{ $item->product?->primaryImage?->image_path ? asset('storage/' . $item->product->primaryImage->image_path) : asset('images/placeholder.png') }}"
                                            alt="{{ $item->product?->product_name }}" class="product-image">
                                    </div>
                                    <div class="item-details">
                                        <div class="item-detail">
                                            <span class="item-detail-label">Quantity</span>
                                            <span class="item-detail-value">{{ $item->quantity }}</span>
                                        </div>
                                        <div class="item-detail">
                                            <span class="item-detail-label">Price (Single)</span>
                                            <span class="item-detail-value">{{ $item->price }}</span>
                                        </div>
                                        <div class="item-detail">
                                            <span class="item-detail-label">Total</span>
                                            <span class="item-detail-value">{{ $item->quantity * $item->price }}</span>
                                        </div>
                                        {{-- <div class="item-detail">
                                            <span class="item-detail-label">Tax (%)</span>
                                            <span
                                                class="item-detail-value">{{ $item->tax_percent ? $item->tax_percent . ' (%)' : '' }}</span>
                                        </div>
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
                                            <span class="item-detail-label">IGST (%)</span>
                                            <span
                                                class="item-detail-value">{{ $item->igst_percent ? $item->igst_percent . ' (%)' : '' }}</span>
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
                                        <div class="item-detail">
                                            <span class="item-detail-label">IGST Amount</span>
                                            <span
                                                class="item-detail-value">{{ $item->igst_amount ? ' ₹' . $item->igst_amount : '' }}</span>
                                        </div>
                                        <div class="item-detail">
                                            <span class="item-detail-label">Total W/o Tax</span>
                                            <span
                                                class="item-detail-value">{{ $item->amount_wo_tax ? ' ₹' . $item->amount_wo_tax : '' }}</span>
                                        </div> --}}
                                    </div>
                                </div>

                            @empty
                                <span>No Items on this order</span>
                            @endforelse

                        </div>
                    </div>
                </div>
            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->
        <x-change_return_status_component />
        <x-footer />
    </div>
@endsection

@push('page-scripts')
    {{-- <script src="{{ asset('assets/js/enquiry.js') }}"></script> --}}
    <script src="{{ asset('assets/js/update-return-status.js') }}"></script>
@endpush

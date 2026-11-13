@extends('layouts.app')
@push('styles')
    {{-- image view lib --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.3/viewer.min.css" />
    <link href="{{ asset('assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />

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
            font-weight: 650;
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

        /* .card:hover {
                        transform: translateY(-5px);
                        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
                    } */

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

        @media (max-width: 968px) {
            .grid-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush
@section('title', 'Wishlist')
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Wishlist of {{ $wishlists->first_name ?? '' }}</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Wishlist</li>
                                </ol>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="py-0 my-0">
                                <div class="card p-3">
                                    <form action="{{ route('web.wishlist.byCustomer') }}" method="post">
                                        @csrf
                                        <div class="row g-3 align-items-end">
                                            <div class="col-12 col-md-4">
                                                <label for="customer" class="form-label">Customer</label>
                                                <select class="form-select select2" id="customer" name="customer">
                                                    <option value="" selected>--Select Customer--</option>
                                                    @foreach ($customers as $customer)
                                                        <option value="{{ $customer->customer_id ?? '' }}"
                                                            {{ $customer->customer_id == ($customer_id ?? '') ? 'selected' : '' }}>
                                                            {{ $customer->full_name ?? '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label for="product" class="form-label">Product</label>
                                                <select class="form-select select2" id="product" name="product">
                                                    <option value="" selected>--Select Product--</option>
                                                    @foreach ($products as $product)
                                                        <option value="{{ $product->product_id ?? '' }}"
                                                            {{ $product->product_id == ($product_id ?? '') ? 'selected' : '' }}>
                                                            {{ $product->product_name ?? '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-3">
                                                <button class="btn btn-secondary" type="submit"
                                                    style="width: 100px">Filter</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid-container">
                    <!-- Customer Information -->
                    <div class="card full-width">
                        <div class="card-header">
                            <div class="card-icon">👤</div>
                            <h2 class="card-title">Customer Information</h2>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Customer Name</span>
                            <span class="info-value">{{ $wishlists->full_name }}</span>
                        </div>
                        @php
                            $phone = preg_replace('/\D/', '', $wishlists->phone ?? '');
                            // Add Indian country code if not present
                            if (!empty($phone) && !Str::startsWith($phone, '91')) {
                                $phone = '91' . $phone;
                            }
                        @endphp
                        <div class="info-row">
                            <span class="info-label">Phone Number</span>
                            <span class="info-value">
                                @if (!empty($wishlists->phone))
                                    {{ $wishlists->phone }}
                                    <a href="https://wa.me/{{ $phone }}?text={{ 'Hi! You recently added some items to your wishlist on Vantara. Just checking if you need any help with the products or if you’d like to place an order. Happy to assist!' }}" target="_blank" class="ms-2 text-success"
                                        title="Chat on WhatsApp">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">City</span>
                            <span class="info-value">{{ $wishlists->city ?? '' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">State</span>
                            <span class="info-value">{{ $wishlists->state ?? '' }}</span>
                        </div>
                    </div>

                    <!-- Ordered Items -->
                    <div class="card full-width">
                        <div class="card-header mb-2">
                            <div class="card-icon">📦</div>
                            <h2 class="card-title">Wishlisted Items</h2>
                        </div>
                        <div class="items-container">
                            @forelse ($wishlists->wishlist as $item)
                                <div class="item-card">
                                    <div class="item-header">{{ $item->product?->product_name }}</div>
                                      <div class="product-info mb-2">
                                        <img src="{{ $item->product?->primaryImage?->image_path ? asset('storage/' . $item->product->primaryImage->image_path) : asset('images/placeholder.png') }}"
                                            alt="{{ $item->product?->product_name }}" class="product-image">
                                    </div>
                                    <div class="item-details">
                                        <div class="item-detail">
                                            <span class="item-detail-label">Added Date</span>
                                            <span class="item-detail-value">{{ $item->created_at }}</span>
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
            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->
        <x-footer />
    </div>
@endsection

@push('page-scripts')
    {{-- //for select2 start --}}
    <script src="{{ asset('assets/libs/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form-advanced.init.js') }}"></script>
    {{-- //for select2 end --}}
       <script>
        $('.select2').select2({
            placeholder: "--Select Customer--",
            allowClear: true
        });
    </script>
@endpush

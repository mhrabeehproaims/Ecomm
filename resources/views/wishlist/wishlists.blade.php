@extends('layouts.app')
@push('styles')
    <link href="{{ asset('assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('title', 'Wishlist Management')
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Customer Wishlisted Products</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Order Management</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="card p-3">
                            <div class="row">
                                <div class="col-12">
                                    <form action="{{ route('web.wishlist.byCustomer') }}" method="post">
                                        @csrf
                                        <div class="row g-3 align-items-end">
                                            <div class="col-12 col-md-4">
                                                <label for="customer" class="form-label">Customer</label>
                                                <select class="form-select select2" id="customer" name="customer">
                                                    <option value="" selected>--Select Customer--</option>
                                                    @foreach ($customers as $customer)
                                                        <option value="{{ $customer->customer_id ?? '' }}"
                                                            {{ $customer->customer_id == old('customer') ? 'selected' : '' }}>
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
                                                            {{ $product->product_id == old('product') ? 'selected' : '' }}>
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
                        <div class="card">
                            <div class="card-body">
                                <table id="datatable" class="table table-bordered dt-responsive w-100">
                                    <thead>
                                        <tr class="text-center">
                                            <th class="text-start" style="width: 5%">#</th>
                                            <th class="text-start" style="width: 15%">Product Name</th>
                                            <th class="text-start" style="width: 15%">Customer Name</th>
                                            <th class="text-start" style="width: 15%">Customer Contact</th>
                                            <th class="text-start" style="width: 15%">Added Date</th>


                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($wishlists as $wishlist)
                                            <tr>
                                                <td class="text-center">
                                                    {{ $loop->iteration }}
                                                </td>
                                                <td class="text-start">{{ $wishlist->product->product_name }}</td>
                                                <td class="text-start">
                                                    {{ $wishlist->customer?->first_name ?? ('' . ' ' . $wishlist->customer?->last_name ?? '') }}
                                                </td>
                                                @php
                                                    $phone = preg_replace('/\D/', '', $wishlist->customer->phone ?? '');
                                                    // Add Indian country code if not present
                                                    if (!empty($phone) && !Str::startsWith($phone, '91')) {
                                                        $phone = '91' . $phone;
                                                    }
                                                @endphp

                                                <td class="text-start">
                                                    @if (!empty($wishlist->customer->phone))
                                                        {{ $wishlist->customer->phone }}
                                                        <a href="https://wa.me/{{ $phone }}?text={{ 'Hi! You recently added some items to your wishlist on Vantara. Just checking if you need any help with the products or if you’d like to place an order. Happy to assist!' }}"
                                                            target="_blank" class="ms-2 text-success"
                                                            title="Chat on WhatsApp">
                                                            <i class="fab fa-whatsapp"></i>
                                                        </a>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="text-start">{{ $wishlist->created_at?->format('d-m-Y') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div> <!-- end col -->
                </div> <!-- end row -->

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

        <div class="modal fade modal-md" id="showStatusModal" tabindex="-1" aria-labelledby="showStatusModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form id="changeStatusForm">
                        <div class="modal-header">
                            <h5 class="modal-title" id="showStatusModalLabel">Update Order Status</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        {{-- hidden input (inside form now) --}}
                        <input type="hidden" name="hidden_id" id="hidden_id">

                        <div class="modal-body">
                            <div class="mb-3 text-center">
                                <span class="fw-semibold">Current Status:</span>
                                <span class="ms-2 info-value"></span>
                            </div>

                            <div class="mb-3">
                                <label for="statusSelect" class="form-label">Change Status</label>
                                <select name="statusSelect" id="statusSelect" class="form-select">
                                    <option value="pending">Pending</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="processing">Processing</option>
                                    <option value="shipped">Shipped</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="refunded">Refunded</option>
                                </select>
                            </div>
                        </div>


                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Confirm</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

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

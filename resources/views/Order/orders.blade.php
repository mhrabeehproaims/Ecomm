@extends('layouts.app')
@push('styles')
<link href="{{ asset('assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
{{-- image view lib --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.3/viewer.min.css" />

{{-- to adjust image viwer bg color --}}
<style>
    .viewer-backdrop {
        background-color: rgba(0, 0, 0, 0.7) !important;
    }

    .info-value {
        color: #2d3748;
        font-size: 15px;
        font-weight: 600;
    }

    .info-label {
        color: #718096;
        font-size: 14px;
        font-weight: 500;
    }
</style>
@endpush
@section('title', 'Order Management')
@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Order Management</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                <li class="breadcrumb-item active">Order Management</li>
                            </ol>
                        </div>

                    </div>
                </div>
            </div>
            <div class="row">
                <!-- Order Statistics Cards -->
                <div class="col-xl-4 col-md-6">
                    <div class="card bg-white border-0">
                        <div class="card-body">
                            <p class="text-muted mb-2 fw-medium">Today's Orders</p>
                            <h4 class="mb-0">{{ $today_order_count }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="card bg-white border-0">
                        <div class="card-body">
                            <p class="text-muted mb-2 fw-medium">Today's Order Amount</p>
                            <h4 class="mb-0">₹{{ number_format($today_order_total, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="card bg-white border-0">
                        <div class="card-body">
                            <p class="text-muted mb-2 fw-medium">Today Delivered Orders</p>
                            <h4 class="mb-0">{{ $today_delivered_count }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Filter Section -->
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-3">Filter Orders</h4>
                    <div class="row align-items-end mb-3">
                        <!-- Status Filter -->
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label for="statusFilter" class="form-label">Order Status</label>
                            <select name="search_status" id="statusFilter" class="form-select select2">
                                <option value="">All Orders</option>
                                <option value="ordered" {{ ($search_status ?? '') == 'ordered' ? 'selected' : '' }}>
                                    Ordered</option>
                                <option value="rejected" {{ ($search_status ?? '') == 'rejected' ? 'selected' : '' }}>
                                    Rejected</option>
                                <option value="confirmed" {{ ($search_status ?? '') == 'confirmed' ? 'selected' : '' }}>
                                    Confirmed</option>
                                <option value="processing" {{ ($search_status ?? '') == 'processing' ? 'selected' : '' }}>
                                    Processing</option>
                                <option value="despatched" {{ ($search_status ?? '') == 'despatched' ? 'selected' : '' }}>
                                    Despatched</option>
                                <option value="ontransist" {{ ($search_status ?? '') == 'ontransist' ? 'selected' : '' }}>
                                    On-Transit</option>
                                <option value="delivered" {{ ($search_status ?? '') == 'delivered' ? 'selected' : '' }}>
                                    Delivered</option>
                            </select>
                        </div>

                        <!-- Date From Filter -->
                        <div class="col-md-2 mb-3 mb-md-0">
                            <label for="dateFrom" class="form-label">From Date</label>
                            <input type="date" id="dateFrom" class="form-control" value="{{ $date_from ?? '' }}"
                                max="{{ date('Y-m-d') }}">
                        </div>

                        <!-- Date To Filter -->
                        <div class="col-md-2 mb-3 mb-md-0">
                            <label for="dateTo" class="form-label">To Date</label>
                            <input type="date" id="dateTo" class="form-control" value="{{ $date_to ?? '' }}"
                                max="{{ date('Y-m-d') }}">
                        </div>

                        <!-- Filter Buttons -->
                        <div class="col-md-3">
                            <button type="button" id="filterBtn" class="btn btn-primary waves-effect waves-light">
                                <i class="bx bx-filter-alt me-1"></i>Filter
                            </button>
                            <button type="button" id="resetBtn" class="btn btn-light waves-effect waves-light ms-1">
                                <i class="bx bx-reset me-1"></i>Reset
                            </button>
                        </div>
                    </div>

                </div>
            </div>
            <!-- end page title -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <table id="" class="table table-bordered rounded dt-responsive w-100 data-tables">
                                <thead>
                                    <tr class="text-center">
                                        <th class="text-start" style="width: 5%">#</th>
                                        <th class="text-start" style="width: 10%">Order ID</th>
                                        <th class="text-start" style="width: 8%">Order Ref.</th>
                                        <th class="text-start" style="width: 10%">Date and Time</th>
                                        <th class="text-start" style="width: 13%">Customer Name</th>
                                        <th class="text-start" style="width: 8%">Delivery Location</th>
                                        <th class="text-start" style="width: 10%">Contact</th>
                                        <th class="text-start" style="width: 8%">Total Amount</th>
                                        <th class="text-start" style="width: 8%">Payment Status</th>
                                        <th class="text-start" style="width: 9%">Order Status</th>
                                        <th class="text-start" style="width: 15%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div> <!-- end col -->
            </div> <!-- end row -->

        </div> <!-- container-fluid -->
    </div>
    <!-- End Page-content -->


    {{-- <x-assignDeliveryAgent-modal :deliveryAgents="$delivery_agents" /> --}}
    <x-change-order-status-component :delivery_agents="$delivery_agents" />
    <x-footer />
</div>
@endsection

@push('page-scripts')
<script src="{{ asset('assets/libs/select2/js/select2.min.js') }}"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2();

        let table = $('.data-tables').DataTable({
            processing: true,
            serverSide: true,
            order: [
                [3, 'desc']
            ], // sort by date column
            ajax: {
                url: "{{ route('orders.index') }}",
                data: function(d) {
                    // Send the status filter to the server
                    d.status_filter = $('#statusFilter').val();
                    d.date_from = $('#dateFrom').val();
                    d.date_to = $('#dateTo').val();
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'order_id',
                    name: 'orders.order_id'
                },
                {
                    data: 'ref_order',
                    name: 'ref_order',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'date',
                    name: 'orders.created_at'
                },
                {
                    data: 'customer_name',
                    name: 'customers.first_name'
                },
                {
                    data: 'delivery_loc',
                    name: 'customers.delivery_loc',
                    searchable: false
                },
                {
                    data: 'customer_contact',
                    name: 'customers.phone'
                },
                {
                    data: 'total_amount',
                    name: 'orders.total_amount',
                    render: function(data, type, row) {
                        return '₹ ' + parseFloat(data).toFixed(2);
                    }
                },
                {
                    data: 'payment_status',
                    name: 'orders.payment_status'
                },
                {
                    data: 'order_status',
                    name: 'orders.order_status',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ]
        });

        // Filter button click
        $('#filterBtn').on('click', function() {
            table.draw(); // Reload table with filter
        });

        // Reset button click
        $('#resetBtn').on('click', function() {
            $('#dateFrom').val('').trigger('change');
            $('#dateTo').val('').trigger('change');
            $('#statusFilter').val('').trigger('change');
            table.draw(); // Reload table without filter
        });

        // Optional: Auto-filter on select change (without clicking Filter button)
        $('#statusFilter').on('change', function() {
            table.draw();
        });

        // --- Debounced search ---
        let searchDelay;
        let $searchInput = $('.dataTables_filter input');

        $searchInput
            .unbind() // remove default DataTables keyup
            .on('input', function(e) {
                // "input" catches typing, pasting, AND clear button click
                clearTimeout(searchDelay);
                let query = this.value;

                searchDelay = setTimeout(() => {
                    table.search(query).draw();
                }, 800);
            });
    });
</script>

<script src="{{ asset('assets/js/order.js') }}"></script>
@endpush
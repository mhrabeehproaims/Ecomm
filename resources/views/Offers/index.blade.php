@extends('layouts.app')
@section('title', 'Vantara | Offers')
<link rel="stylesheet" href="{{ asset('assets/css/offerModal.css') }}">
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Offer Managdement</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Offer Master</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->
                <div class="create-btn py-2">
                    <button id="btnSelectOffer" class="btn btn-secondary">Create Offer</button>
                </div>
                @session('message')
                    <div class="alert alert-warning alert-dismissible fade show col-12 col-md-8" role="alert">
                        <strong>{{ session('message') }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endsession
                <div class="row flex justify-content-center">
                    <div class="col-12">
                        <div class="card p-3">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-bordered align-middle w-100">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center">Sl.no</th>
                                            <th class="text-center">Offer Name</th>
                                            <th class="text-center">Discount Type</th>
                                            <th class="text-center">Discount Value</th>
                                            <th class="text-center">Discount From</th>
                                            <th class="text-center">Products</th>
                                            <th class="text-center">Period</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($offers as $offer)
                                            <tr>
                                                <td class="text-center">{{ $loop->iteration }}</td>
                                                <td>{{ ucwords($offer->title) }}</td>
                                                <td>{{ $offer->discount_type == 'fixed' ? 'Fixed Amount' : 'Percentage' }}</td>
                                                <td class="text-center">
                                                    {{ $offer->discount_type == 'fixed' ? '₹' . $offer->discount_value : $offer->discount_value . '%' }}
                                                </td>
                                                <td class="text-center">{{ strtoupper($offer->discount_from) }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-info text-light p-1">
                                                        {{ collect($offer->applicable_product_ids)->count() }} Products
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    {{ $offer->start_date?->format('d-m-Y') }} -
                                                    {{ $offer->end_date?->format('d-m-Y') }}
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <button
                                                            class="btn btn-sm btn-light d-flex BtnDetails align-items-center justify-content-center"
                                                            data-id="{{ $offer->id }}" title="View Details"
                                                            style="width: 32px; height: 32px;"><i
                                                                class="fas fa-eye"></i></button>
                                                        <form action="{{ route('offers.destroy', $offer) }}" method="post"
                                                            class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button
                                                                class="btn btn-sm btn-light d-flex align-items-center justify-content-center"
                                                                onclick="return confirm('Are you sure you want to delete this offer?')"
                                                                style="width: 32px; height: 32px;"><i class="fa fa-trash"
                                                                    aria-hidden="true"></i></button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{ $offers->links() }}
                        </div>
                    </div> <!-- end col -->
                </div> <!-- end row -->
            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

        {{-- Offer select modal --}}
        <!-- Modal -->
        <div class="modal fade" id="offerSelectModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="staticBackdropLabel">Select Offer Type</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <button class="btn offer-item w-100 mb-2 border"
                            data-url="{{ route('offers.create', ['type' => 'amount_off_products', 'disc_type' => 'percentage']) }}"
                            id="amount_off_products">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0 pt-1">
                                    <i class="bi bi-tag offer-icon"></i>
                                </div>
                                <div class="flex-grow-1 ms-3 text-start">
                                    <div class="offer-title">Amount off products - Percentage</div>
                                    <p class="offer-description">Discount specific products or collections of
                                        products</p>
                                </div>
                                <div class="flex-shrink-0 pt-1">
                                    <i class="bi bi-chevron-right arrow-icon"></i>
                                </div>
                            </div>
                        </button>
                        <button class="btn offer-item w-100 mb-2 border"
                            data-url="{{ route('offers.create', ['type' => 'amount_off_products', 'disc_type' => 'fixed']) }}"
                            id="amount_off_products">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0 pt-1">
                                    <i class="bi bi-tag offer-icon"></i>
                                </div>
                                <div class="flex-grow-1 ms-3 text-start">
                                    <div class="offer-title">Amount off products -Fixed Amount</div>
                                    <p class="offer-description">Discount specific products or collections of
                                        products</p>
                                </div>
                                <div class="flex-shrink-0 pt-1">
                                    <i class="bi bi-chevron-right arrow-icon"></i>
                                </div>
                            </div>
                        </button>

                        <!-- Buy X get Y -->
                        {{-- <button class="btn offer-item w-100 p-3 mb-2 border"
                            data-url="{{ route('offers.create', ['type' => 'buy_x_get_y']) }}" id="buy_x_get_y">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0 pt-1">
                                    <i class="bi bi-cart offer-icon"></i>
                                </div>
                                <div class="flex-grow-1 ms-3 text-start">
                                    <div class="offer-title">Buy X get Y</div>
                                    <p class="offer-description">Discount specific products or collections of
                                        products</p>
                                </div>
                                <div class="flex-shrink-0 pt-1">
                                    <i class="bi bi-chevron-right arrow-icon"></i>
                                </div>
                            </div>
                        </button> --}}

                        <!-- Free shipping -->
                        {{-- <button class="btn offer-item w-100 p-3 mb-2 border"
                            data-url="{{ route('offers.create', ['type' => 'free_shipping']) }}" id="free_shipping">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0 pt-1">
                                    <i class="bi bi-truck offer-icon"></i>
                                </div>
                                <div class="flex-grow-1 ms-3 text-start">
                                    <div class="offer-title">Free shipping</div>
                                    <p class="offer-description">Offer free shipping on an order</p>
                                </div>
                                <div class="flex-shrink-0 pt-1">
                                    <i class="bi bi-chevron-right arrow-icon"></i>
                                </div>
                            </div>
                        </button> --}}
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- end modal -->


        <!-- Offer Details Modal -->
        <div class="modal fade" id="offerDetailsModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Offer Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <!-- Basic Info Card -->
                        <div class="row g-4">

                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="info-card p-1 rounded shadow-sm">
                                    <div class="info-row d-flex justify-content-between align-items-center mb-1">
                                        <div class="info-label d-flex align-items-center gap-2">
                                            <i class="bi bi-tag-fill text-primary"></i>
                                            <span>Title</span>
                                        </div>
                                        <div class="info-value fw-bold" id="d_title"></div>
                                    </div>

                                    <div class="info-row d-flex justify-content-between align-items-center mb-1">
                                        <div class="info-label d-flex align-items-center gap-2">
                                            <i class="bi bi-gift-fill text-success"></i>
                                            <span>Offer Type</span>
                                        </div>
                                        <div class="info-value">
                                            <span id="d_offer_type" class="badge bg-primary rounded-pill px-3 py-2"></span>
                                        </div>
                                    </div>

                                    <div class="info-row d-flex justify-content-between align-items-center mb-1">
                                        <div class="info-label d-flex align-items-center gap-2">
                                            <i class="bi bi-percent text-warning"></i>
                                            <span>Disc. Type</span>
                                        </div>
                                        <div class="info-value">
                                            <span id="discount_type" class="badge bg-success rounded-pill px-3 py-2"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="info-card p-2 rounded shadow-sm">
                                    <div class="info-row d-flex justify-content-between align-items-center mb-1">
                                        <div class="info-label d-flex align-items-center gap-2">
                                            <i class="bi bi-cash-coin text-danger"></i>
                                            <span>Disc. From</span>
                                        </div>
                                        <div class="info-value fw-semibold" id="discount_from"></div>
                                    </div>
                                    <div class="info-row d-flex justify-content-between align-items-center mb-1">
                                        <div class="info-label d-flex align-items-center gap-2">
                                            <i class="bi bi-calendar-check text-info"></i>
                                            <span>Start Date</span>
                                        </div>
                                        <div class="info-value fw-semibold" id="d_start_date"></div>
                                    </div>

                                    <div class="info-row d-flex justify-content-between align-items-center mb-1">
                                        <div class="info-label d-flex align-items-center gap-2">
                                            <i class="bi bi-calendar-x text-danger"></i>
                                            <span>End Date</span>
                                        </div>
                                        <div class="info-value fw-semibold" id="d_end_date"></div>
                                    </div>
                                </div>
                            </div>

                        </div>


                        <!-- Date Info Card -->
                        {{-- <div class="info-card">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-calendar-check"></i>
                                    <span>Start Date:</span>
                                </div>
                                <div class="info-value" id="d_start_date"></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-calendar-x"></i>
                                    <span>End Date:</span>
                                </div>
                                <div class="info-value" id="d_end_date"></div>
                            </div>
                        </div> --}}

                        <!-- Discount Info Card -->


                        <hr class="divider">

                        <!-- Products Section -->
                        <h5 class="section-title">
                            <i class="bi bi-box-seam"></i>
                            Products
                        </h5>
                        <div class="products-table-wrapper">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Product</th>
                                        <th>MRP</th>
                                        <th>SRate</th>
                                        <th>Disc. Value</th>
                                        <th>Disc. Amount</th>
                                        <th>Final Price</th>
                                    </tr>
                                </thead>
                                <tbody id="d_products_table"></tbody>
                            </table>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-2"></i>Close
                        </button>
                    </div>

                </div>
            </div>
        </div>
        <x-footer />
    </div>
@endsection
@push('page-scripts')
    <script>
        let offer_url = "{{ route('offers.index') }}";
    </script>

    <script src="{{ asset('assets/js/offers.js') }}"></script>
@endpush
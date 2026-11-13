@extends('layouts.app')
@section('title', 'Vantara | Offers')
@push('styles')
    <link href="{{ asset('assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />

    <style>
        .hover-color:hover {
            color: blue;
        }

        .offer-title {
            font-size: 16px;
            font-weight: 700px;
            color: black;
        }

        /* Select2 checkbox styling */
        .select2-option-wrapper {
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .select2-container--default .select2-results__option {
            padding: 8px 12px;
            /* Selected bg color */
            color: #0e0505 !important;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #0a62ba !important;
            color: #fff !important;
        }

        .select2-container--default .select2-selection--multiple {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            min-height: 38px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #4e19ec;
            border: 1px solid #0d6efd;

            color: rgb(12, 2, 2);
            border-radius: 4px;
            padding: 3px 8px;
            margin: 4px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: rgb(14, 4, 4);
            margin-right: 5px;
            font-weight: bold;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #0e0303;
        }

        /* Prevent text selection */
        .select2-results__option {
            user-select: none;
        }

        .select2-checkbox {
            cursor: pointer;
            margin-right: 8px;
            width: 16px;
            height: 16px;
        }

        #product_table thead tr th,
        #product_table thead tr td {
            position: sticky;
            top: 0;
            background-color: #343a40;
            /* matches .table-dark */
            z-index: 2;
        }

        .form-section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            border-left: 4px solid #0d6efd;
            padding-left: 10px;
        }

        .card-custom {
            border-radius: 10px;
            border: 1px solid #e3e3e3;
            box-shadow: 0px 3px 10px rgba(0, 0, 0, 0.05);
        }

        table thead {
            position: sticky;
            top: 0;
            background: #212529;
            z-index: 10;
        }

        .input-title {
            font-weight: 500;
        }
    </style>
@endpush
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Create New Offer</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                <li class="breadcrumb-item active">Offers</li>
                            </ol>
                        </div>

                    </div>
                </div>
                <div class="container-fluid bg-white p-4">

                    <div class="card card-custom p-4">
                        <form action="{{ route('offers.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="offer_type" value="amount_off_products">

                            {{-- =================== BASIC DETAILS =================== --}}
                            <div class="form-section-title">Basic Details</div>

                            <div class="mb-4">
                                <label class="form-label input-title">Discount Name</label>
                                <input type="text" name="name" class="form-control" placeholder="Eg: Winter Sale 10%"
                                    required>
                            </div>

                            {{-- =================== DISCOUNT SECTION =================== --}}
                            <div class="form-section-title">Discount Settings</div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label input-title">Discount Type</label>
                                    <select class="form-select" id="discount_type" name="discount_type" required>
                                        <option value="percentage" {{ $disc_type == 'percentage' ? 'selected' : '' }}>
                                            Percentage
                                        </option>
                                        <option value="fixed" {{ $disc_type == 'fixed' ? 'selected' : '' }}>Fixed Amount
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label input-title">Value</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="discount_value" name="discount_value"
                                            placeholder="0" required>
                                        <span class="input-group-text">{{ $disc_type == 'fixed' ? '₹' : '%' }}</span>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label input-title">Discount From</label>
                                    <select class="form-select" id="discount_from" name="discount_from">
                                        <option value="mrp">MRP</option>
                                        <option value="srp">SRP</option>
                                    </select>
                                </div>
                            </div>

                            {{-- =================== CATEGORY & BRAND SECTION =================== --}}
                            <div class="form-section-title">Filter Products</div>

                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="category" class="form-label input-title">Categories</label>
                                    <select class="form-select select2-checkbox selectdCategory" id="category"
                                        name="category[]" multiple required>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->categ_id }}">{{ $category->categ_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label input-title">Subcategories</label>
                                    <select id="subcategory" name="subcategory[]" class="form-select" multiple>
                                        <option value="">-- Select Subcategory --</option>
                                    </select>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label input-title">Brands</label>
                                    <select class="form-select select2" id="brand_name" name="brand_name[]" multiple>
                                        @foreach ($brands as $brand)
                                            <option value="{{ $brand->brand_id }}">{{ $brand->brand_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 mb-3 d-flex align-items-end">
                                    <button id="getProductsBtn" type="button" class="btn btn-success w-100">
                                        Get Products
                                    </button>
                                </div>
                            </div>

                            {{-- =================== PRODUCT TABLE =================== --}}
                            <div class="form-section-title">Selected Products</div>

                            <div class="table-responsive mb-4" style="max-height: 350px; overflow-y: auto;">
                                <table class="table table-dark table-bordered" id="product_table">
                                    <thead>
                                        <tr>
                                            <th>Sl.</th>
                                            <th>Product Name</th>
                                            <th>MRP</th>
                                            <th>S-Rate</th>
                                            <th><span id="disc_type"></span> Disc on <span id="price_type"></span></th>
                                            <th>Disc. Amount</th>
                                            <th>New Price</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <div id="products-result"></div>
                            </div>

                            {{-- =================== DATE SECTION =================== --}}
                            <div class="form-section-title">Offer Duration</div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label input-title">Start Date</label>
                                    <input type="datetime-local" class="form-control" name="starts_at" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label input-title">End Date</label>
                                    <input type="datetime-local" class="form-control" name="ends_at" required>
                                </div>
                            </div>

                            {{-- =================== NOTES =================== --}}
                            <div class="mb-4">
                                <label class="form-label input-title">Notes (Optional)</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>

                            {{-- =================== SUBMIT =================== --}}
                            <button type="submit" class="btn btn-primary w-100 py-2">
                                Save Offer
                            </button>

                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <x-footer />
@endsection
@push('page-scripts')
    <script src="{{ asset('assets/libs/select2/js/select2.min.js') }}"></script>
    <script>
        let oldSubcategory = "{{ old('subcategory') }}";
        let oldproduct_id = "{{ old('product_name') }}";
    </script>
    <script src="{{ asset('assets/js/create-offer.js') }}"></script>
@endpush

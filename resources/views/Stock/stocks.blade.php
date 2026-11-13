@extends('layouts.app')
@push('styles')
    <link href="{{ asset('assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{ asset('assets/css/upload_product_image.css') }}">
@endpush
@section('title', 'Vantara | Stock Management')
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Stock Management</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Stock Management</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->
                {{-- <div class="create-btn py-2"> --}}
                {{-- <button class="btn btn-secondary" id="create_brand">Create New Brand</button> --}}
                {{-- </div> --}}

                <div class="row">
                    <div class="col-12">

                        <div class="py-0 my-0">
                            <div class="card p-3">
                                <div class="row g-3 align-items-end">
                                    {{-- Category --}}
                                    <div class="col-md-2">
                                        <form action="{{ route('stocks.index') }}" method="GET">
                                            <label for="category" class="form-label">Category</label>
                                            <select class="form-select select2" id="category" name="category" required>
                                                <option value="" selected>--Select Category--</option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->categ_id ?? '' }}"
                                                        {{ request('category') == $category->categ_id ? 'selected' : '' }}>
                                                        {{ $category->categ_name ?? '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                    </div>

                                    {{-- Subcategory --}}
                                    <div class="col-md-2">
                                        <label for="subcategory" class="form-label">Subcategory</label>
                                        <select id="subcategory" name="subcategory" class="form-select select2">
                                            <option selected value="">--Select Subcategory--</option>
                                        </select>
                                    </div>

                                    {{-- Product --}}
                                    <div class="col-md-2">
                                        <label for="product_name" class="form-label">Product</label>
                                        <select id="product_name" name="product_name" class="form-select select2">
                                            <option selected value="">--Select Product--</option>
                                        </select>
                                    </div>

                                    {{-- Brand --}}
                                    <div class="col-md-2">
                                        <label for="brand_name" class="form-label">Brand</label>
                                        <select id="brand_name" name="brand_name" class="form-select select2">
                                            <option selected value="">--Select Brand--</option>
                                            @foreach ($brands as $brand)
                                                <option value="{{ $brand->brand_id ?? '' }}"
                                                    {{ request('brand_name') == $brand->brand_id ? 'selected' : '' }}>
                                                    {{ $brand->brand_name ?? '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button class="btn btn-secondary" style="width: 100px">Filter</button>
                                        </form>
                                        <a href="{{ route('stocks.index') }}" class="btn btn-light">Clear Filter</a>
                                    </div>

                                </div>
                            </div>

                        </div>
                        <div class="card pt-1 mt-1">
                            <div class="card-body">
                                <table id="datatable" class="table table-bordered dt-responsive  nowrap w-100">
                                    <thead>
                                        <tr class="text-center">
                                            <th class="text-center">#</th>
                                            <th class="text-start">Name</th>
                                            <th class="text-start">Category</th>
                                            <th class="text-start">Sub Category</th>
                                            <th class="text-center">Stock</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($products as $product)
                                            <tr>
                                                <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                                <td class="text-start align-middle">{{ $product->product_name }}</td>
                                                <td class="text-start align-middle">{{ $product->category->categ_name }}
                                                </td>
                                                <td class="text-start align-middle">
                                                    {{ optional($product->subCategory)->subCat_name }}</td>
                                                <td class="text-center align-middle">

                                                    <a href="{{ route('stocks.show', $product->product_id) }}"
                                                        style="min-width: 90px; text-align: center;"
                                                        class="btn @if ($product->stock <= $product->minimum_stock) btn-danger @else btn-light @endif showQuoantity-{{ $product->product_id }}">
                                                        {{ ($product->stock ?? 0) . ' ' . ($product->unit?->unit_name ?? '') }}
                                                    </a>

                                                </td>

                                                <td class="text-center"><button class="btn btn-light addStock"
                                                        data-id="{{ $product->product_id }}"
                                                        data-name="{{ $product->product_name }}"
                                                        data-stock="{{ $product->stock ?? 0 }}"
                                                        data-unit="{{ ucwords(optional($product->unit)->unit_name) }}">Add
                                                        Stock</button></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No products found</td>
                                            </tr>
                                        @endforelse

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div> <!-- end col -->
                </div> <!-- end row -->

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

        {{-- Create Brand modal start --}}
        <div class="modal fade modal-md" id="updateStockModal" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <div class="p-0">
                            <h5 class="modal-title" id="stockModalLabel">
                                <label for="recipient-name" class="col-form-label">Manage Product Stock</label>
                            </h5>
                            <span class="prod_name text-capitalize"></span>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="update-product-stock">
                            <div class="mb-3 row">
                                <div class="col-sm-12 col-md-6">
                                    <div class="mb-1">
                                        <label for="recipient-name" class="col-form-label">Current Stock</label>
                                        <div class="input-group flex-nowrap">
                                            <input type="number" name="current_stock"
                                                class="fw-medium form-control current_stock" required readonly>
                                            <span class="input-group-text stock_unit">stock_unit</span>
                                        </div>
                                    </div>
                                </div>
                                <input type="number" name="product_id" id="hidden_id" value="" hidden>
                                <input type="text" name="hidden_unit" class="hidden_unit" hidden>

                                <div class="col-sm-12 col-md-6">
                                    <div class="mb-1">
                                        <label for="recipient-name" class="col-form-label">Enter New Stock</label>
                                        <div class="input-group flex-nowrap">
                                            <input type="number" name= "new_stock"
                                                class="fw-medium form-control new_stock" required>
                                            <span class="input-group-text stock_unit">stock_unit</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <div class="col-sm-12 col-md-6">
                                    <label for="" class="col-form-label">Stock Status</label>
                                    <select name="stock_status" id="stock_status" class="form-select">
                                        <option value="in_stock">Add</option>
                                        <option value="out_stock">Less</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-1 text-center">
                                        <label class="col-form-label">Total Stock</label>
                                        <div class="fs-4 fw-medium rounded total_stock p-2 bg-light border">
                                            <span class="TotalNewStock">0.00
                                            </span> {{ ucwords($product?->unit?->unit_name ?? 'N/A') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>
                    <div class="modal-footer ">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- end modal -->

        <x-delete-modal id="DeleteStockModal" form_id="delete-brand" />

        <x-footer />
    </div>
@endsection

@push('page-scripts')
    <script>
        let oldSubcategory = "{{ old('subcategory') }}";
        let oldproduct_id = "{{ old('product_id') }}";
    </script>
    {{-- image view script  --}}
    <script src="{{ asset('assets/libs/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/upload_product_image.js') }}"></script>
    <script src="{{ asset('assets/js/stock.js') }}"></script>
@endpush

@extends('layouts.app')
@push('styles')
    <link href="{{ asset('assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{ asset('assets/css/upload_product_image.css') }}">
@endpush
@section('title', 'Vantara | Create Product')
@section('content')

    <div class="main-content">
        <div class="page-content" style="background-color: rgb(254, 253, 253)">
            <div class="container-fluid">
                <!-- start page title -->

                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                                <div class="page-title-left">
                                    <h4 class="mb-1 font-size-24">Add New Product</h4>
                                    <span class="text-muted">Add a new product to your store</span>
                                </div>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                                        <li class="breadcrumb-item">Create Product</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row my-2">
                        <div class="col-xl-6">
                            <form action="{{ route('products.store') }}" method="POST" id="productForm" enctype="multipart/form-data">

                                @csrf
                                <div class="border border-dark-subtle rounded-3 mb-4">
                                    <div class="bg-light rounded-top-3 p-3">
                                        <h6 class="fs-5 fw-bold mb-0">Name and Description</h6>
                                    </div>
                                    <div class="p-3">
                                        <!-- Product Name -->
                                        <div class="mb-3">
                                            <label for="product_name" class="form-label">Product Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text"
                                                class="form-control @error('product_name') is-invalid @enderror"
                                                id="product_name" name="product_name" value="{{ old('product_name') }}"
                                                required>
                                            @error('product_name')
                                                <span class="text-danger">
                                                    {{ $message }}
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="product_name" class="form-label">HSN Code </label>
                                            <input type="number" class="form-control" name="hsn_code"
                                                value="{{ old('hsn_code') }}">

                                        </div>

                                        <!-- Product Description -->
                                        <div class="mb-3">
                                            <label for="product_desc" class="form-label">Product Description</label>
                                            <textarea id="product_desc" name="product_desc" class="form-control" rows="5">{{ old('product_desc') }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="health_benefit" class="form-label">Detailed Description
                                            </label>
                                            <textarea id="health_benefit" name="health_benefit" class="form-control" rows="5">{{ old('health_benefit') }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="health_benefit" class="form-label">Description Images
                                            </label>
                                            <div class="border border-secondary-subtle rounded p-2 bg-white">
                                                <div id="desc-image-preview-wrapper" class="row g-3 align-items-start">

                                                    <div class="col-auto" id="desc-upload-button-wrapper">
                                                        <div class="addImage-box-button text-center p-4">
                                                            <label class="d-block">
                                                                <i
                                                                    class="bx bx-image-add text-muted fw-light fs-1 d-block mb-2"></i>
                                                                <p class="mb-0 text-muted">Click to Upload</p>
                                                                <input type="file" id="desc_images" name="desc_images[]"
                                                                    accept="image/*" multiple hidden>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="border border-dark-subtle rounded-3 mb-3">
                                    <div class="bg-light rounded-top-3 rounded-top p-3">
                                        <h6 class="fs-5 fw-bold mb-0">Category</h6>
                                    </div>
                                    <div class="row p-3">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="formrow-email-input" class="form-label">Select
                                                    Category <span class="text-danger">*</span></label>
                                                <div class="mb-3">
                                                    <select
                                                        class="form-control select2 @error('category') is-invalid @enderror"
                                                        id="category" name="category" required>
                                                        <option value="" selected> --Select Category--</option>
                                                        @foreach ($categories as $category)
                                                            <option value="{{ $category->categ_id ?? '' }}"
                                                                {{ $category->categ_id == old('category') ? 'selected' : '' }}>
                                                                {{ $category->categ_name ? $category->categ_name . ' (Return: ' . $category->return_days . ' ' . Str::plural('day', $category->return_days) . ')' : '' }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('category')
                                                        <span class="text-danger">
                                                            {{ $message }}
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="formrow-email-input" class="form-label">Select
                                                    Subcategory</label>
                                                <div class="mb-3">
                                                    <select id="subcategory" name="subcategory"
                                                        class="form-control select2">
                                                        <option selected value="">--Select Subcategory--</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="border border-dark-subtle rounded-3 mb-3">
                                    <div class="bg-light rounded-top-3 rounded-top p-3">
                                        <h6 class="fs-5 fw-bold mb-0">Weight & Diamensions</h6>
                                    </div>

                                    <div class="row p-3">
                                        <div class="col-md-12 row">
                                            <div class="mb-3 col-6">
                                                <label for="formrow-email-input" class="form-label">Brand <span
                                                        class="text-danger">*</span></label>
                                                <div class="mb-0">
                                                    <select
                                                        class="form-control select2 @error('product_brand') is-invalid @enderror"
                                                        id="product_brand" name="product_brand" required>
                                                        <option value="" selected>--Select Brand--</option>
                                                        @foreach ($brands as $brand)
                                                            <option value="{{ $brand->brand_id }}"
                                                                {{ $brand->brand_id == old('product_brand') ? 'selected' : '' }}>
                                                                {{ $brand->brand_name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('product_brand')
                                                        <span class="text-danger">
                                                            {{ $message }}
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="mb-0 col-6">
                                                <label for="formrow-email-input" class="form-label">Prodcut Size</label>
                                                <div class="mb-3">
                                                    <select class="form-control select2" name="product_size"
                                                        id="product_size">
                                                        <option value="" selected>--Select Size--</option>
                                                        @foreach ($sizes as $size)
                                                            <option value="{{ $size->size_id }}"
                                                                {{ $size->size_id == old('product_size') ? 'selected' : '' }}>
                                                                {{ $size->size_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 row">
                                            <div class="mb-0 col-6">
                                                <label for="formrow-email-input" class="form-label">Unit</label>
                                                <div class="mb-3">
                                                    <select class="form-control select2" name="product_unit"
                                                        id="product_unit">
                                                        <option value="" selected>--Select Unit--</option>
                                                        @foreach ($units as $value)
                                                            <option value="{{ $value->unit_id }}"
                                                                {{ $value->unit_id == old('product_unit') ? 'selected' : '' }}
                                                                data-name="{{ $value->unit_name }}">
                                                                {{ $value->unit_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            {{-- package type --}}
                                            {{-- <div class="mb-0 col-6">
                                                <label for="formrow-email-input" class="form-label">Package
                                                    Type</label>
                                                <div class="mb-3">
                                                    <select class="form-control select2" name="package_type"
                                                        id="package_type">
                                                        <option value="" selected>--Select Package Type--</option>
                                                        @foreach ($package_types as $type)
                                                            <option value="{{ $type->package_id }}"
                                                                {{ $type->package_id == old('package_type') ? 'selected' : '' }}>
                                                                {{ $type->package_type }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div> --}}

                                            {{-- </div> --}}
                                            <div class="mb-0 col-6">
                                                <label for="formrow-email-input" class="form-label">Weight/Nos. </label>
                                                <div class="mb-3 input-group">
                                                    {{-- <select class="form-control select2 rounded-start"
                                                        name="product_weight" id="product_weight"
                                                        style="width: 78% !important">
                                                        <option value="" selected>--Select Product Weight--</option>
                                                        @foreach ($weights as $weight)
                                                            <option value="{{ $weight->weight_id }}"
                                                                {{ $weight->weight_id == old('product_weight') ? 'selected' : '' }}>
                                                                {{ $weight->weight_name }}</option>
                                                        @endforeach
                                                    </select> --}}
                                                    <input type="number" class="form-control" name="product_weight"
                                                        id="product_weight">
                                                    <span id="showUnit" class="input-group-text text-center rounded-end"
                                                        style="width: 26% !important"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="border border-dark-subtle rounded-3 mb-3">
                                    <div class="bg-light rounded-top-3 rounded-top p-3">
                                        <h6 class="fs-5 fw-bold mb-0">Manage Stock</h6>
                                    </div>
                                    <div class="row p-3">
                                        <div class="col-md-12 row">
                                            <div class="mb-3 col-6">
                                                <label for="formrow-email-input" class="form-label">Product
                                                    Stock</label>
                                                <input type="number" class="form-control" id="product_stock"
                                                    name="product_stock" value="{{ old('product_stock') }}">
                                            </div>
                                            <div class="mb-3 col-6">
                                                <label for="formrow-email-input" class="form-label">Minimum
                                                    Stock</label>
                                                <input type="number" class="form-control" id="minimum_stock"
                                                    value="{{ old('minimum_stock') }}" name="minimum_stock">
                                            </div>
                                        </div>
                                    </div>
                                </div>


                        </div>
                        <!-- end col -->

                        <div class="col-xl-6">
                            <div class="border border-dark-subtle rounded-3 mb-3">
                                <div class="bg-light rounded-top-3 rounded-top p-3">
                                    <h6 class="fs-5 fw-bold mb-0">Product Pricing</h6>
                                </div>

                                <div class="row p-3">
                                    <div class="col-md-12 row">
                                        <div class="mb-1">
                                            <div class="alert alert-warning d-flex align-items-center" role="alert">
                                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                                <div>
                                                    <strong>Default Price is <span>S-Rate</span> </strong>
                                                </div>
                                            </div>
                                            <input type="hidden" name="primary_price" value="special_price">
                                        </div>
                                        {{-- <div class="mb-3">
                                            <label for="formrow-email-input" class="form-label">Default
                                                Price Shows is S-Rate</label>
                                            <select class="form-control select2-search-disable" class="primary_price"
                                                name="primary_price_display" id="primary_price_display" disabled>
                                                <option value= "mrp"
                                                    {{ old('primary_price') == 'mrp' ? 'selected' : '' }}>
                                                    MRP
                                                </option>
                                                <option value="retail"
                                                    {{ old('primary_price') == 'retail' ? 'selected' : '' }}>Retail
                                                </option>
                                                <option value= "wholesale"
                                                    {{ old('primary_price') == 'wholesale' ? 'selected' : '' }}>
                                                    Wholesale
                                                </option>
                                                <option value="special_price"
                                                    {{ old('primary_price') == 'special_price' ? 'selected' : '' }}
                                                    selected>
                                                    S Rate
                                                </option>
                                            </select>
                                        </div>
                                        <input type="hidden" name="primary_price" value="special_price">
                                        <div class="mb-0">
                                            <div class="mb-0">
                                                <label for="formrow-email-input" class="form-label">Net Primary
                                                    price
                                                    <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="product_netPrice"
                                                    value="{{ old('product_netPrice') }}" name="product_netPrice"
                                                    readonly>
                                            </div>
                                        </div> --}}
                                    </div>
                                    <input type="text" class="form-control" id="product_netPrice"
                                        value="{{ old('product_netPrice', $gst['primary_price'] ?? 0) }}"
                                        name="product_netPrice" readonly hidden>

                                    <div class="col-md-12 row">
                                        <div class="mb-3 col-6">
                                            <label for="formrow-email-input" class="form-label">MRP <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" value="{{ old('product_mrp') }}"
                                                class="form-control  @error('product_mrp') is-invalid @enderror"
                                                id="product_mrp" name="product_mrp" required>
                                            @error('product_mrp')
                                                <span class="text-danger">
                                                    {{ $message }}
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="mb-0 col-6">
                                            <label for="formrow-email-input" class="form-label">S Rate
                                                <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="special_price"
                                                value="{{ old('special_price') }}" name="special_price" required>
                                            @error('special_price')
                                                <span class="text-danger" id="sp-price-error">
                                                    {{ $message }}
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-12 row">
                                        <div class="mb-3 col-6">
                                            <label for="formrow-email-input" class="form-label">Wholesale</label>
                                            <input type="number" value="{{ old('number') }}"
                                                class="form-control  @error('wholesale_price') is-invalid @enderror"
                                                id="wholesale_price" name="wholesale_price">
                                            @error('wholesale_price')
                                                <span class="text-danger">
                                                    {{ $message }}
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="mb-0 col-6">
                                            <label for="formrow-email-input" class="form-label">Retail</label>
                                            <input type="number" class="form-control" id="retail_price"
                                                value="{{ old('retail_price') }}" name="retail_price">
                                        </div>
                                    </div>
                                    {{-- <div class="col-md-12 row mb-3">
                                        <div class="mb-0 col-6">
                                            <label for="formrow-email-input" class="form-label">Discount Type</label>
                                            <select class="form-control select2-search-disable" class="discount_type"
                                                name="discount_type" id="discount_type">
                                                <option value= "rupee"
                                                    {{ old('discount_type') == 'rupee' ? 'selected' : '' }}>Rupee</option>
                                                <option value="percentage"
                                                    {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>Percentage
                                                </option>
                                            </select>
                                        </div>

                                        <div class="mb-0 col-6">
                                            <label for="formrow-email-input" class="form-label">Discount</label>
                                            <input type="number" class="form-control" id="product_discount"
                                                value="{{ old('product_discount') }}" name="product_discount">
                                        </div>
                                    </div> --}}

                                </div>
                            </div>

                            <div class="border border-dark-subtle rounded-3 mb-3">
                                <div class="bg-light rounded-top-3 rounded-top p-3">
                                    <h6 class="fs-5 fw-bold mb-0">Product Tax on S-Rate</h6>
                                </div>

                                <div class="row p-3">
                                    <div class="col-md-12 row">
                                        <div class="mb-2 col-6">
                                            <label for="formrow-email-input" class="form-label">Select Tax
                                                Schedule</label>
                                            <div class="mb-3">
                                                <select class="form-control select2-search-disable" id="tax_schedule"
                                                    name="tax_schedule">
                                                    <option value="" selected> --Select Tax--</option>
                                                    @foreach ($taxSchedules as $schedule)
                                                        <option value="{{ $schedule->tax_id }}"
                                                            data-cgst="{{ $schedule->cgst }}"
                                                            data-sgst="{{ $schedule->sgst }}"
                                                            data-igst="{{ $schedule->igst }}"
                                                            {{ $schedule->tax_id == old('tax_schedule') ? 'selected' : '' }}>
                                                            {{ $schedule->tax_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-2 col-6">
                                            <label for="formrow-email-input" class="form-label">IGST Amount <span
                                                    id="igst_percent"></span></label>
                                            <input type="number" class="form-control" id="product_igst"
                                                value="{{ old('product_igst') }}" name="product_igst" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-12 row">
                                        <div class="mb-2 col-6">
                                            <label for="formrow-email-input" class="form-label">CGST Amount <span
                                                    id="cgst_percent"></span></label>
                                            <input type="number" class="form-control" id="product_cgst"
                                                value="{{ old('product_cgst') }}" name="product_cgst" readonly>
                                        </div>
                                        <div class="mb-2 col-6">
                                            <label for="formrow-email-input" class="form-label">SGST Amount <span
                                                    id="sgst_percent"></span></label>
                                            <input type="number" class="form-control" id="product_sgst"
                                                value="{{ old('product_sgst') }}" name="product_sgst" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mt-2 row">
                                        <div class="mb-2 col-12">
                                            <label for="formrow-email-input" class="form-label">Total Primary
                                                Price <span class="text-danger">*</span></label>

                                            <div class="input-group mb-3">
                                                <span class="input-group-text fw-semibold" id="basic-addon1">₹</span>
                                                <input type="text" class="form-control fw-semibold" id="total_price"
                                                    value="{{ old('total_price') }}" name="total_price" readonly required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="border border-dark-subtle rounded-3 mb-3">
                                <div class="bg-light rounded-top-3 rounded-top p-3">
                                    <h6 class="fs-5 fw-bold mb-0">Courier Weight & Diamensions</h6>
                                </div>
                                <div class="p-3">
                                    <div class="col-md-12 row">
                                        {{-- package type --}}
                                        <div class="mb-0 col-12">
                                            <label for="formrow-email-input" class="form-label">Package
                                                Type</label>
                                            <div class="mb-3">
                                                <select class="form-control select2" name="package_type"
                                                    id="package_type">
                                                    <option value="" selected>--Select Package Type--
                                                    </option>
                                                    @foreach ($package_types as $type)
                                                        <option value="{{ $type->package_id }}"
                                                            {{ $type->package_id == old('package_type') ? 'selected' : '' }}>
                                                            {{ $type->package_type }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 row">
                                        {{-- package type --}}
                                        <div class="mb-0 col-12">
                                            <label for="formrow-email-input" class="form-label">
                                                Package Weight (Gram)</label>
                                            <div class="mb-3">
                                                <input type="text" class="form-control" name="package_weight"
                                                    value="{{ old('package_weight') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="border border-dark-subtle rounded-3 mb-3">
                                <div class="bg-light rounded-top-3 rounded-top p-3">
                                    <h6 class="fs-5 fw-bold mb-0">Product Image</h6>
                                </div>

                                <div class="p-3">
                                    <div id="image-preview-wrapper" class="row g-3 align-items-start">

                                        <div class="col-auto" id="upload-button-wrapper">
                                            <div class="addImage-box-button text-center p-4">
                                                <label class="d-block">
                                                    <i class="bx bx-image-add text-muted fw-light fs-1 d-block mb-2"></i>
                                                    <p class="mb-0 text-muted">Click to Upload</p>
                                                    <input type="file" id="product_images" name="product_images[]"
                                                        accept="image/*" multiple hidden>
                                                </label>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-outline-secondary w-md fw-medium"> <i
                                        class="fas fa-save"></i> &nbsp; Save Product</button>
                            </div>
                            </form>
                        </div>
                        <!-- end card body -->
                    </div>
                </div>

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

        <x-delete-modal id="DeleteCategoryModal" form_id="delete-categ" />

        <x-footer />
    </div>


@endsection

@push('page-scripts')

    <script>
        let oldSubcategory = "{{ old('subcategory') }}";
    </script>
    <script src="{{ asset('assets/libs/select2/js/select2.min.js') }}"></script>
    <!-- form advanced init -->
    <script src="{{ asset('assets/js/pages/form-advanced.init.js') }}"></script>
    <script src="{{ asset('assets/js/upload_product_image.js') }}"></script>
    <script src="{{ asset('assets/js/product_calculations.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if (session('message') == 'success')
        <script>
            Swal.fire({
                title: 'Product Created!',
                text: "Do you want to add more?",
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: 'No, Go Back',
                cancelButtonText: 'Yes, Add More'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('products.index') }}";
                }
                // else {
                //     window.location.href = "{{ route('products.create') }}";
                // }
            });
        </script>
    @endif
@endpush

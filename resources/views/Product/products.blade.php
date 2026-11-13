@extends('layouts.app')
@section('title', 'Vantara | Products')
<style>
    .hover-color:hover {
        color: blue;
    }
</style>
@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Product Management</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                <li class="breadcrumb-item active">Product Master</li>
                            </ol>
                        </div>

                    </div>
                </div>
            </div>
            <!-- end page title -->
            <div class="create-btn py-2">
                <a href="{{ route('products.create') }}" class="btn btn-light">Create Product</a>
            </div>

            <div class="row flex justify-content-center">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">

                            <table id="datatable" class="table table-bordered dt-responsive  nowrap w-100">
                                <thead>
                                    <tr class="text-center">
                                        <th class="text-center">#</th>
                                        <th class="text-start">Name</th>
                                        <th class="text-center">Category</th>
                                        <th class="text-center">S Rate</th>
                                        <th class="text-center">GST</th>
                                        <th class="text-center">Weight</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($products as $product)
                                    <tr class="text-center align-middle">
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="text-start" style="cursor: pointer;"><span
                                                class="btnShowProductDetails hover-color"
                                                title="Click to view product details"
                                                data-id="{{ $product->product_id }}">{{ $product->product_name }}</span>
                                            <span
                                                class="d-flex align-items-center p-0 justify-content-center spinner{{ $product->product_id }} d-none"
                                                style="width:30px; height:30px;">
                                                <span class="spinner-border"
                                                    style="width:14px; height:14px; border-width:2px;"
                                                    role="status" aria-hidden="true">
                                                </span>
                                                <span class="visually-hidden">Loading...</span>
                                            </span>
                                        </td>


                                        {{-- <button title="Show Product Details"
                                                        class="btn btnShowProductDetails btn-outline-secondary p-0 m-0 d-flex align-items-center justify-content-center"
                                                        style="width:30px; height:30px;"
                                                        data-id="{{ $product->product_id }}">
                                        <i class="fas fa-eye"></i>
                                        </button> --}}
                                        <td>{{ $product->category->categ_name ?? '' }}</td>
                                        <td>{{ $product->special_price ? '₹ ' . $product->special_price : '0.00' }}
                                        </td>
                                        <td>{{ $product->taxSchedule->tax_name ?? '' }}</td>
                                        <td>{{ $product->weight ? $product->weight->weight_name . ' ' . $product->unit?->unit_name : '' }}
                                        </td>
                                        <td>
                                            <div class="form-check form-switch d-flex justify-content-center">
                                                <input class="form-check-input switchStatus text-center"
                                                    type="checkbox" role="switch"
                                                    data-id="{{ $product->product_id }}"
                                                    data-url="{{ route('products.update.status', $product->product_id) }}"
                                                    name="switchStatus" style="font-size: 1.1rem"
                                                    {{ $product->status == 1 ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td class="text-center row gap-2 justify-content-center">

                                            {{-- //Products details loading button Start --}}
                                            {{-- <button title="Show Product Details"
                                                        class="btn btnShowProductDetails btn-outline-secondary p-0 m-0 d-flex align-items-center justify-content-center"
                                                        style="width:30px; height:30px;"
                                                        data-id="{{ $product->product_id }}">
                                            <i class="fas fa-eye"></i>
                                            </button> --}}

                                            {{-- <button
                                                        class="btn btn-outline-secondary d-flex align-items-center p-0 justify-content-center spinner{{ $product->product_id }} d-none"
                                            style="width:30px; height:30px;">
                                            <span class="spinner-border"
                                                style="width:14px; height:14px; border-width:2px;"
                                                role="status" aria-hidden="true">
                                            </span>
                                            <span class="visually-hidden">Loading...</span>
                                            </button> --}}
                                            {{-- //Products details loading button End --}}
                                            <button title="Share Product"
                                                class="btn btn-outline-secondary p-0 m-0 d-flex align-items-center justify-content-center share-btn @if ($product->status == 0) d-none @endif"
                                                id="ecommerceshare-{{ $product->product_id }}"
                                                style="width:30px; height:30px;"
                                                data-url="https://vanthara.com/productDetails/{{ $product->product_id }}">
                                                <i class="fas fa-share-alt"></i>
                                            </button>
                                            <a href="https://vanthara.com/productDetails/{{ encrypt($product->product_id) }}"
                                                title="View in E-Commerce" target="_blank"
                                                class="btn btn-outline-secondary p-0 m-0 d-flex align-items-center justify-content-center  @if ($product->status == 0) d-none @endif"
                                                id="ecommerceview-{{ $product->product_id }}"
                                                style="width:30px; height:30px;"> <i class="fas fa-eye"></i>
                                            </a>

                                            <a href="{{ route('products.edit', $product->product_id) }}"
                                                title="Edit Product"
                                                class="btn editBtn btn-outline-secondary m-0 d-flex align-items-center justify-content-center"
                                                style="width:30px; height:30px;"
                                                data-id="{{ $product->product_id }}">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>

                                            <button title="Delete Product"
                                                class="btn BtnDelete btn-outline-secondary m-0 d-flex align-items-center justify-content-center"
                                                style="width:30px; height:30px;"
                                                data-id="{{ $product->product_id }}"
                                                data-name="{{ $product->product_name }}">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
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

    <x-product-show-modal />
    <!-- end modal -->
    <x-delete-modal id="DeleteProductModal" form_id="deleteProductForm" />
    <x-footer />
</div>
@endsection
@push('page-scripts')
<script src="{{ asset('assets/js/toggleStatus.js') }}"></script>
<script src="{{ asset('assets/js/productPage.js') }}"></script>
@endpush
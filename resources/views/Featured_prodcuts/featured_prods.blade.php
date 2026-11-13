@extends('layouts.app')
@push('styles')
    {{-- image view lib --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.3/viewer.min.css" />

    {{-- to adjust image viwer bg color --}}
    <style>
        .viewer-backdrop {
            background-color: rgba(0, 0, 0, 0.7) !important;
        }
    </style>
@endpush
@section('title', 'Featured Products')
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">featured Products</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Sub-Categories</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->
                <div class="create-btn py-2 d-flex justify-content-between">
                    <button class="btn btn-light" id="create_subCateg">Add Featured Products</button>
                    <button class="btn btn-danger" id="remove_items">Remove</button>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <table id="datatable" class="table table-bordered dt-responsive  nowrap w-100">
                                    <thead>
                                        <tr class="text-center">
                                            <th class="text-center">#</th>
                                            <th class="text-center">Category</th>
                                            <th class="text-center">Sub Category</th>
                                            <th class="text-center">Product Name</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($featureds as $feautured)
                                            <tr>
                                                <td class="text-center"><input type="checkbox" class="form-check-input featured-items" data-id="{{ $feautured->featured_id }}"> &nbsp; {{$loop->iteration}}</td>
                                                <td class="text-center">{{ $feautured->product?->category?->categ_name }}</td>
                                                <td class="text-center">{{ $feautured->product?->subCategory?->subCat_name }}</td>
                                                <td class="text-center">{{ $feautured->product?->product_name }}</td>
                                                <td class="text-center">{{ $feautured->status==1 ? 'Active'  : 'Inctive' }}</td>
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

        {{-- Create category modal start --}}
        <div class="modal fade modal-md" id="FeaturedModal" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="subCategModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="create-featured">
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label">Select Category Name</label>
                                <select class="form-select select2" id="category" name="category" required>
                                    <option value="" selected>--Select Category--</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->categ_id ?? '' }}"
                                            {{ $category->categ_id == old('category') ? 'selected' : '' ?? '' }}>
                                            {{ $category->categ_name ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="subcategory" class="form-label">Subcategory</label>
                                <select id="subcategory" name="subcategory" class="form-select select2">
                                    <option selected value="">--Select Subcategory--</option>
                                </select>
                            </div>
                            <!-- Selected products will be shown here -->
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label">Products</label>
                                <select id="product_name" name="product_name" class="form-select select2">
                                    <option value="">--Select Products--</option>
                                </select>
                            </div>

                            <!-- Selected products will be shown here -->
                            <div class="mb-3">
                                <label class="col-form-label">Selected Products</label>
                                <div id="selected-products" class="d-flex flex-wrap gap-2 border p-1"></div>
                            </div>


                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary"></button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- end modal -->
        <x-delete-modal id="DeleteFeaturedModal" form_id="delete-categ" />

        <x-footer />
    </div>
@endsection

@push('page-scripts')
    <script>
        let oldSubcategory = "{{ old('subcategory') }}";
        let oldproduct_id = "{{ old('product_name') }}";
    </script>
    s
    {{-- image view script  --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.3/viewer.min.js"></script>

    <script>
        document.querySelectorAll('.catgimage').forEach(img => {
            new Viewer(img);
        });
    </script>

    <script src="{{ asset('assets/js/featured-products.js') }}"></script>
@endpush

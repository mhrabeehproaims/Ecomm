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

@section('title', 'Vantara | Brands')
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Brand Management</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Brands</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->
                <div class="create-btn py-2">
                    <button class="btn btn-secondary" id="create_brand">Create New Brand</button>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <table id="datatable" class="table table-bordered dt-responsive  nowrap w-100">
                                    <thead>
                                        <tr class="text-center">
                                            <th class="text-center">#</th>
                                            <th class="text-center">Image</th>
                                            <th class="text-center">Brand</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($brands as $index => $brand)
                                            <tr>
                                                <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                                <td class="text-center align-middle">
                                                    @if ($brand->image)
                                                        <img class="catgimage rounded"
                                                            src="{{ asset('storage/BrandImage/' . $brand->image) }}"
                                                            alt="Brand image" srcset="" width="70px">
                                                    @else
                                                        No Image
                                                    @endif
                                                </td>
                                                <td class="text-center align-middle">{{ $brand->brand_name }}</td>
                                                <td class="text-center align-middle">{{ $brand->Statustext }}</td>
                                                <td class="text-center align-middle">
                                                    <button class="btn editBtn m-0" data-id={{ $brand->brand_id }}
                                                        data-cat_name="{{ $brand->brand_name }}"
                                                        data-image="{{ $brand->image }}"
                                                        data-status="{{ $brand->status }}"><i
                                                            class="fas fa-pencil-alt"></i></button>
                                                    <button class="btn BtnDelete m-0" data-id="{{ $brand->brand_id }}"
                                                        data-name="{{ $brand->brand_name }}">
                                                        <i class="fa fa-trash" aria-hidden="true"></i></button>
                                                </td>
                                            </tr>
                                        @empty
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
        <div class="modal fade modal-md" id="brandModal" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="brandModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="create-brand">
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label">Brand Name</label>
                                <input type="text" name= "brand_name" class="form-control" id="brand_name" required>
                            </div>
                            <input type="hidden" name="hidden_id" id="hidden_id">
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label">Status</label>
                                <select name="brand_status" id="brand_status" class="form-select">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label">Brand Image</label>
                                <input type="file" name= "brand_image" class="form-control" id="brand_image">
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

        <x-delete-modal id="DeleteBrandModal" form_id="delete-brand" />

        <x-footer />
    </div>
@endsection

@push('page-scripts')
    {{-- image view script  --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.3/viewer.min.js"></script>

    <script>
        document.querySelectorAll('.catgimage').forEach(img => {
            new Viewer(img);
        });
    </script>

    <script src="{{ asset('assets/js/brand.js') }}"></script>
@endpush

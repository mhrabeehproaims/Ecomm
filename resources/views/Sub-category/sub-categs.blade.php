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
@section('title', 'Sub Categories')
@section('content')

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Sub Categories</h4>

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
                <div class="create-btn py-2">
                    <button class="btn btn-light" id="create_subCateg">Create Sub Category</button>
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
                                            <th class="text-center">Category</th>
                                            <th class="text-center">Sub Category</th>
                                            <th class="text-center">Description</th>
                                            <th class="text-center">Return Days</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($sub_categs as $value)
                                            <tr class="text-center align-middle">
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    @if ($value->image)
                                                        <img class="catgimage"
                                                            src="{{ asset('storage/subCategImage/' . $value->image) }}"
                                                            alt="Image" srcset="" width="50px">
                                                    @else
                                                        No Image
                                                    @endif
                                                </td>
                                                <td>{{ $value->category->categ_name ?? 'N/A' }}</td>
                                                <td>{{ $value->subCat_name }}</td>
                                                <td>{{ $value->subcateg_desc }}</td>
                                                <td>{{ $value->return_days }}</td>
                                                <td>{{ $value->StatusText }}</td>
                                                <td class="text-center">
                                                    <button class="btn editBtn m-0" data-id={{ $value->subCat_id }}
                                                        data-catname="{{ $value->category->categ_id ?? '' }}"
                                                        data-subcat="{{ $value->subCat_name }}"
                                                        data-desc="{{ $value->subcateg_desc }}"
                                                        data-return_day="{{ $value->return_days }}"
                                                        data-status="{{ $value->status }}"><i
                                                            class="fas fa-pencil-alt"></i></button>
                                                    <button class="btn BtnDelete m-0" data-id="{{ $value->subCat_id }}"
                                                        data-name="{{ $value->subCat_name }}">
                                                        <i class="fa fa-trash" aria-hidden="true"></i></button>
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

        {{-- Create category modal start --}}
        <div class="modal fade modal-md" id="subCategoryModal" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="subCategModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="create-subCateg">
                            <input type="hidden" name="hidden_id" id="hidden_id">

                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label">Select Category Name</label>
                                <select name="category" id="category" class="form-select" required>
                                    <option value="">-- Select Category -- </option>
                                    @foreach ($categs as $cat)
                                        <option value="{{ $cat->categ_id }}">{{ $cat->categ_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label">Sub Category Name</label>
                                <input type="text" name= "subCateg_name" class="form-control" id="subCateg_name"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label">Description</label>
                                <textarea name="SubCateg_desc" id="SubCateg_desc" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-6">
                                        <label for="recipient-name" class="col-form-label">Return Days
                                            <i class='bx bx-info-circle text-primary'
                                                style="font-size: 1.3rem; margin-left: 6px; cursor: pointer; vertical-align: middle;"
                                                title="Specify the number of days within which an item can be returned for this category.">
                                            </i>
                                        </label>
                                        <input type="number" name="return_days" id="return_days"
                                            data-return="return_days" class="form-control">
                                    </div>
                                    <div class="col-6">
                                        <label for="recipient-name" class="col-form-label">Status</label>
                                        <select name="subCateg_status" id="subCateg_status" class="form-select">
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="" class="col-form-label">Image</label>
                                <input type="file" name="SubCateg_image" id="SubCateg_image" class="form-control">
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
        <x-delete-modal id="DeleteCategoryModal" form_id="delete-categ" />

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

    <script src="{{ asset('assets/js/sub-category.js') }}"></script>
@endpush

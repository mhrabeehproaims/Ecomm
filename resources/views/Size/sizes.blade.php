@extends('layouts.app')
@section('title', 'Sizes')
@section('content')

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Size Management</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Sizes</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->
                <div class="create-btn py-2">
                    <button class="btn btn-light" id="create_size">Create Size</button>
                </div>

                <div class="row flex justify-content-center">
                    <div class="col-9">
                        <div class="card">
                            <div class="card-body">

                                <table id="datatable" class="table table-bordered dt-responsive  nowrap w-100">
                                    <thead>
                                        <tr class="text-center">
                                            <th class="text-center">#</th>
                                            <th class="text-center">Size</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($sizes as $size)
                                            <tr class="text-center align-middle">
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $size->size_name }}</td>
                                                <td>{{ $size->status_text }}</td>
                                                <td class="text-center">
                                                    <button class="btn editBtn m-0" data-id={{ $size->size_id }}
                                                        data-sizename="{{ $size->size_name }}"
                                                        data-status="{{ $size->status }}"><i
                                                            class="fas fa-pencil-alt"></i></button>
                                                    <button class="btn BtnDelete m-0" data-id="{{ $size->size_id }}"
                                                        data-name="{{ $size->size_name }}">
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

        {{-- Create size modal start --}}
        <div class="modal fade modal-md" id="SizeModal" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="SizeModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="create-size">
                            <input type="hidden" name="hidden_id" id="hidden_id">
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label">Type Size Name</label>
                                <input type="text" name= "size_name" class="form-control" id="size_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label">Status</label>
                                <select name="size_status" id="size_status" class="form-select">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
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
        <x-delete-modal id="DeleteSizeModal" form_id="deleteSizeForm" />
        <x-footer />
    </div>
@endsection

@push('page-scripts')
    <script src="{{ asset('assets/js/size.js') }}"></script>
@endpush

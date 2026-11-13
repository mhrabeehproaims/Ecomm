@extends('layouts.app')
{{-- @push('styles')
@endpush --}}
@section('title', 'Weight Management | Vantara')
@section('content')

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Weight Management</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Weight</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->


                <div class="row flex justify-content-center">
                    <div class="col-9">
                        <div class="create-btn py-2">
                            <button class="btn btn-light" id="create_weight">Create New Weight</button>
                        </div>
                        <div class="card">
                            <div class="card-body">

                                <table id="datatable" class="table table-bordered dt-responsive  nowrap w-100">
                                    <thead>
                                        <tr class="text-center">
                                            <th class="text-center">#</th>
                                            <th class="text-center">Weight</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($weights as $weight)
                                            <tr class="text-center align-middle">
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $weight->weight_name }}</td>
                                                <td>
                                                    <div class="form-check form-switch d-flex justify-content-center">
                                                        <input class="form-check-input switchStatus text-center"
                                                            type="checkbox" role="switch"
                                                            data-id="{{ $weight->weight_id }}"
                                                            data-url="{{ route('weight.edit', $weight->weight_id) }}"
                                                            name="switchStatus" style="font-size: 1.1rem"
                                                            {{ $weight->status == 1 ? 'checked' : '' }}>
                                                    </div>
                                                </td>

                                                <td class="text-center">
                                                    <button class="btn editBtn m-0" data-id={{ $weight->weight_id }}
                                                        data-weightname="{{ $weight->weight_name }}"
                                                        data-status="{{ $weight->status }}"><i
                                                            class="fas fa-pencil-alt"></i></button>
                                                    <button class="btn BtnDelete m-0" data-id="{{ $weight->weight_id }}"
                                                        data-name="{{ $weight->weight_name }}">
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

        {{-- Create Weight modal start --}}
        <div class="modal fade modal-md" id="WeightModal" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="WeightModalLabel">Create Weight</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="create-weight">
                            <input type="hidden" name="hidden_id" id="hidden_id">
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label">Type Weight Name</label>
                                <input type="text" name= "weight_name" class="form-control" id="weight_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label">Status</label>
                                <select name="weight_status" id="weight_status" class="form-select">
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
        <x-delete-modal id="DeleteWeightModal" form_id="deleteWeightForm" />
        <x-footer />
    </div>
@endsection
{{-- @push('datatable-scripts')
@endpush --}}
@push('page-scripts')
    <script src="{{ asset('assets/js/weight.js') }}"></script>
    <script src="{{ asset('assets/js/toggleStatus.js') }}"></script>
@endpush

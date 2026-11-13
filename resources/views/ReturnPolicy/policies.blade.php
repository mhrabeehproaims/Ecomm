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

@section('title', 'Vantara | Return Policy')
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Return Policy Management</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Policies</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->
                <div class="create-btn py-2">
                    <button class="btn btn-secondary" id="create_policy">Create Return Policy</button>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                <table id="" class="table table-bordered dt-responsive  nowrap w-100">
                                    <thead>
                                        <tr class="text-center">
                                            <th class="text-center" style="width: 5%">#</th>
                                            <th class="text-center" style="width: 15%">Title</th>
                                            <th class="text-center" style="width: 5%">Return Days</th>
                                            <th class="text-center" style="width: 10%">Return Type </th>
                                            <th class="text-center" style="width: 5%">Status</th>
                                            <th class="text-center" style="width: 50%">Conditions </th>
                                            <th class="text-center" style="width: 10%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($policies as $index => $policy)
                                            <tr>
                                                <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                                <td class="text-center align-middle">{{ $policy->title }}</td>
                                                <td class="text-center align-middle">{{ $policy->return_days }}</td>
                                                <td class="text-center align-middle">{{ ucwords($policy->return_type) }}</td>
                                                <td class="text-center align-middle"> <span @class(['p-1 rounded text-white', 'bg-success' => $policy->is_active,'bg-danger' => !$policy->is_active,])>{{ $policy->is_active == 1 ? 'Active' : 'Inactive' }}</span></td>
                                                <td class="text-center align-middle">{{ ucwords($policy->conditions) }}</td>
                                                <td class="text-center align-middle">
                                                    <button class="btn editBtn m-0" data-id={{ $policy->policy_id }}
                                                        data-title="{{ $policy->title }}"
                                                        data-return_days="{{ $policy->return_days }}"
                                                        data-return_type="{{ $policy->return_type }}"
                                                        data-status="{{ $policy->is_active }}"
                                                        data-conditions="{{ $policy->conditions }}">
                                                        <i class="fas fa-pencil-alt"></i></button>
                                                    <button class="btn BtnDelete m-0" data-id="{{ $policy->policy_id }}"
                                                        data-name="{{ $policy->title ?? '' }}">
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

        {{-- Create Return Policy modal start --}}
        <div class="modal fade modal-lg" id="policyModal" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="policyModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="create-policy">
                            <input type="hidden" name="hidden_id" id="hidden_id">
                            <div class="row">
                                <div class="mb-3 col-6">
                                    <label for="recipient-name" class="col-form-label">Policy Title</label>
                                    <input type="text" name="policy_title" class="form-control" id="policy_title">
                                </div>
                                <div class="mb-3 col-6">
                                    <label for="recipient-name" class="col-form-label">Return Days</label>
                                    <input type="number" name="return_days" class="form-control" id="return_days"
                                        value="{{ old('return_days', 7) }}">
                                </div>
                            </div>
                            <div class="row">

                                <div class="mb-3 col-6">
                                    <label for="recipient-name" class="col-form-label">Return Type</label>
                                    <select class="form-select" id="return_type" name="return_type">
                                        <option value="refund_or_exchange">Refund/Exchange</option>
                                        <option value="refund">Refund</option>
                                        <option value="exchange">Exchange</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label for="recipient-name" class="col-form-label">Status</label>
                                    <select name="policy_status" id="policy_status" class="form-select">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="conditions" class="col-form-label">Conditions</label>
                                <textarea name="policy_conditions" id="policy_conditions" class="form-control" rows="5"></textarea>
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

        <x-delete-modal id="DeletePolicyModal" form_id="delete-policy" />

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

    <script src="{{ asset('assets/js/policy.js') }}"></script>
@endpush

@extends('layouts.app')

@section('title', 'Tax Management')
@section('content')

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Tax Management</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Tax Master</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->
                <div class="create-btn py-2">
                    <button class="btn btn-light" id="create_tax">Create Tax</button>
                </div>

                <div class="row flex justify-content-center">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                <table id="datatable" class="table table-bordered dt-responsive  nowrap w-100">
                                    <thead>
                                        <tr class="text-center">
                                            <th class="text-center">#</th>
                                            <th class="text-center">Name</th>
                                            <th class="text-center">Tax (%)</th>
                                            <th class="text-center">CGST (%)</th>
                                            <th class="text-center">SGST (%)</th>
                                            <th class="text-center">IGST (%)</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($taxes as $tax)
                                            <tr class="text-center align-middle">
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $tax->tax_name }}</td>
                                                <td>{{ $tax->tax_percentage }}</td>
                                                <td>{{ $tax->cgst }}</td>
                                                <td>{{ $tax->sgst }}</td>
                                                <td>{{ $tax->igst }}</td>
                                                {{-- <td>{{ $tax->StatusText }}</td> --}}
                                                <td>
                                                    <div class="form-check form-switch d-flex justify-content-center">
                                                        <input class="form-check-input switchStatus text-center"
                                                            type="checkbox" role="switch" data-id="{{ $tax->tax_id }}"
                                                            data-url="{{ route('tax-master.edit', $tax->tax_id) }}"
                                                            name="switchStatus" style="font-size: 1.1rem"
                                                            {{ $tax->status == 1 ? 'checked' : '' }}>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn editBtn m-0" data-id={{ $tax->tax_id }}
                                                        data-taxname="{{ $tax->tax_name }}"
                                                        data-taxprcnt="{{ $tax->tax_percentage }}"
                                                        data-cgst="{{ $tax->cgst }}" data-sgst="{{ $tax->sgst }}"
                                                        data-igst="{{ $tax->igst }}"
                                                        data-status="{{ $tax->status }}"><i
                                                            class="fas fa-pencil-alt"></i></button>
                                                    <button class="btn BtnDelete m-0" data-id="{{ $tax->tax_id }}"
                                                        data-name="{{ $tax->tax_name }}">
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

        {{-- Create Tax modal start --}}
        <div class="modal fade modal-md" id="TaxModal" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="TaxModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="create-tax">
                            <input type="hidden" name="hidden_id" id="hidden_id">
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label">Tax Name</label>
                                <input type="text" name="tax_name" class="form-control" id="tax_name" required>
                            </div>

                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <label for="recipient-name" class="col-form-label">Enter GST Percentage</label>
                                        <input type="number" name= "tax_percent" class="form-control" id="tax_percent"
                                            required>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="recipient-name" class="col-form-label">IGST (%)</label>
                                        <input type="number" name= "tax_igst" class="form-control" id="tax_igst" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <label for="recipient-name" class="col-form-label">CGST (%)</label>
                                        <input type="number" name= "tax_cgst" class="form-control" id="tax_cgst"
                                            readonly>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="recipient-name" class="col-form-label">SGST (%)</label>
                                        <input type="number" name= "tax_sgst" class="form-control" id="tax_sgst"
                                            readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label">Status</label>
                                <select name="tax_status" id="tax_status" class="form-select">
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
        <x-delete-modal id="DeleteTaxModal" form_id="deleteTaxForm" />
        <x-footer />
    </div>
@endsection

@push('page-scripts')
    <script src="{{ asset('assets/js/tax.js') }}"></script>
    <script src="{{ asset('assets/js/toggleStatus.js') }}"></script>
@endpush

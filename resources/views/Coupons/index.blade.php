@extends('layouts.app')
@section('title', 'Coupons')
@section('content')

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Coupon Management</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Coupons</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->
                <div class="create-btn py-2">
                    <button class="btn btn-light" id="create_coupon">Create Coupon</button>
                </div>

                <div class="row flex justify-content-center">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                <table id="datatable" class="table table-bordered dt-responsive  nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Code</th>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>Value</th>
                                            <th>Status</th>
                                            <th>Validity</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($coupons as $coupon)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $coupon->code }}</td>
                                                <td>{{ $coupon->coupon_name }}</td>
                                                <td>{{ ucfirst($coupon->type) }}</td>
                                                <td>{{ $coupon->value }}</td>
                                                <td>{{ $coupon->is_active ? 'Active' : 'Inactive' }}</td>
                                                <td>
                                                    {{ $coupon->start_date ? \Carbon\Carbon::parse($coupon->start_date)->format('d-M-Y') : '' }}
                                                    {{ $coupon->end_date ?  'to' : '' }}
                                                    {{ $coupon->end_date ? \Carbon\Carbon::parse($coupon->end_date)->format('d-M-Y') : '' }}
                                                </td>

                                                <td>
                                                    <button id="" class="btn btn-sm btn-warning editBtn"
                                                        data-id = "{{ $coupon->id }}" data-code = "{{ $coupon->code }}"
                                                        data-coupon_name = "{{ $coupon->coupon_name }}"
                                                        data-type = "{{ $coupon->type }}"
                                                        data-value = "{{ $coupon->value }}"
                                                        data-min_order_amount = "{{ $coupon->min_order_amount }}"
                                                        data-coupon_status = "{{ $coupon->is_active }}"
                                                        data-max_uses = "{{ $coupon->max_uses }}"
                                                        data-max_uses_per_user = "{{ $coupon->max_uses_per_user }}"
                                                        data-start_date = "{{ $coupon->start_date }}"
                                                        data-end_date = "{{ $coupon->end_date }}">Edit</button>
                                                    <button class="btn btn-sm btn-danger BtnDelete"
                                                        data-id = "{{ $coupon->id }}"
                                                        data-code = "{{ $coupon->code }}">Delete</button>
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

        {{-- Create coupon modal start --}}
        <div class="modal fade" id="CouponModal" tabindex="-1" aria-labelledby="CouponModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold" id="CouponModalLabel">Create New Coupon</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <form id="create-coupon">
                        <div class="modal-body">
                            <input type="hidden" name="hidden_id" id="hidden_id">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="coupon_code" class="form-label">Coupon Code <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="code" id="coupon_code" class="form-control"
                                        autocomplete="off" placeholder="e.g. SAVE10" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="coupon_name" class="form-label">Coupon Name</label>
                                    <input type="text" name="coupon_name" id="coupon_name" class="form-control"
                                        autocomplete="off" placeholder="e.g. 10% Off Offer">
                                </div>

                                <div class="col-md-6">
                                    <label for="type" class="form-label">Discount Type<span
                                            class="text-danger">*</span></label>
                                    <select name="type" id="type" class="form-select">
                                        <option value="fixed">Fixed Amount</option>
                                        <option value="percentage">Percentage</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="value" class="form-label">Discount Value<span
                                            class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="value" id="value" class="form-control"
                                        autocomplete="off" placeholder="e.g. 100 or 10%" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="min_order_amount" class="form-label">Minimum Order Amount</label>
                                    <input type="number" step="0.01" name="min_order_amount" id="min_order_amount"
                                        autocomplete="off" class="form-control" placeholder="e.g. 500">
                                </div>

                                <div class="col-md-6">
                                    <label for="coupon_status" class="form-label">Status</label>
                                    <select name="is_active" id="coupon_status" class="form-select">
                                        <option value="1" selected>Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="max_uses" class="form-label">Maximum Uses (Overall)</label>
                                    <input type="number" name="max_uses" id="max_uses" class="form-control"
                                        autocomplete="off" placeholder="e.g. 100">
                                </div>

                                <div class="col-md-6">
                                    <label for="max_uses_per_user" class="form-label">Max Uses per User</label>
                                    <input type="number" name="max_uses_per_user" id="max_uses_per_user"
                                        autocomplete="off" class="form-control" placeholder="e.g. 1">
                                </div>

                                <div class="col-md-6">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary fw-semibold">
                                <i class="bi bi-save me-1"></i> Save Coupon
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- end modal -->
        <x-delete-modal id="DeleteCouponModal" form_id="deleteCouponForm" />
        <x-footer />
    </div>
@endsection

@push('page-scripts')
    <script src="{{ asset('assets/js/coupon.js') }}"></script>
@endpush

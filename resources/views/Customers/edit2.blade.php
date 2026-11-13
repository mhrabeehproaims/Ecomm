@extends('layouts.app')
@push('styles')
    <link href="{{ asset('assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{ asset('assets/css/upload_product_image.css') }}">
@endpush
@section('title', 'Vantara | Edit Customer')
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
                                    <h4 class="mb-1 font-size-24">Update Customer</h4>
                                    <span class="text-muted">Edit customer details</span>
                                </div>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                                        <li class="breadcrumb-item">Update Customer</li>
                                    </ol>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="row my-2">
                        <div class="col-xl-6">
                            <form action="{{ route('customers.update', $customer->customer_id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="border border-dark-subtle rounded-3 mb-4">
                                    <div class="bg-light rounded-top-3 p-3">
                                        <h6 class="fs-5 fw-bold mb-0">Basic Information</h6>
                                    </div>
                                    <div class="p-3">
                                        <!-- Product Name -->
                                        <div class="mb-3">
                                            <label for="customer_first_name" class="form-label">Customer First Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text"
                                                class="form-control @error('customer_first_name') is-invalid @enderror"
                                                id="customer_first_name" name="customer_first_name"
                                                value="{{ old('customer_first_name', $customer->first_name) }}" required>
                                            @error('customer_first_name')
                                                <span class="text-danger">
                                                    {{ $message }}
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="customer_last_name" class="form-label">Customer Last Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text"
                                                class="form-control @error('customer_last_name') is-invalid @enderror"
                                                id="customer_last_name" name="customer_last_name"
                                                value="{{ old('customer_last_name', $customer->last_name) }}" required>
                                            @error('customer_last_name')
                                                <span class="text-danger">
                                                    {{ $message }}
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="customer_name" class="form-label">Mobile <span
                                                    class="text-danger">*</span></label>
                                            <input type="phone" class="form-control" name="phone"
                                                value="{{ old('phone', $customer->phone) }}">

                                        </div>
                                        <div class="mb-3">
                                            <label for="customer_name" class="form-label">Email <span
                                                    class="text-danger">*</span></label>
                                            <input type="email" class="form-control" name="email"
                                                value="{{ old('email', $customer->email) }}">

                                        </div>

                                    </div>
                                </div>




                        </div>
                        <!-- end col -->

                        <div class="col-xl-6">
                            <div class="border border-dark-subtle rounded-3 mb-3">
                                <div class="bg-light rounded-top-3 rounded-top p-3">
                                    <h6 class="fs-5 fw-bold mb-0">Location & Address</h6>
                                </div>

                                <div class="row p-3">
                                    <div class="col-md-12 row">
                                        <div class="mb-3 col-6">
                                            <label for="formrow-email-input" class="form-label">Address<span
                                                    class="text-danger">*</span></label>
                                            <input type="number" value="{{ old('customer_address', $customer->address) }}"
                                                class="form-control  @error('customer_address') is-invalid @enderror"
                                                id="customer_address" name="customer_address">
                                            @error('customer_address')
                                                <span class="text-danger">
                                                    {{ $message }}
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="mb-0 col-6">
                                            <label for="formrow-email-input" class="form-label">City</label>
                                            <input type="number" class="form-control" id="city"
                                                value="{{ old('city', $customer->city) }}" name="city">
                                        </div>
                                    </div>
                                    <div class="col-md-12 row">
                                        <div class="mb-3 col-6">
                                            <label for="formrow-email-input" class="form-label">State</label>
                                            <select name="state" class="form-control select2" id="state">
                                                <option value="">-- Select State --</option>
                                                <option value="Andhra Pradesh">Andhra Pradesh</option>
                                                <option value="Arunachal Pradesh">Arunachal Pradesh</option>
                                                <option value="Assam">Assam</option>
                                                <option value="Bihar">Bihar</option>
                                                <option value="Chhattisgarh">Chhattisgarh</option>
                                                <option value="Goa">Goa</option>
                                                <option value="Gujarat">Gujarat</option>
                                                <option value="Haryana">Haryana</option>
                                                <option value="Himachal Pradesh">Himachal Pradesh</option>
                                                <option value="Jharkhand">Jharkhand</option>
                                                <option value="Karnataka">Karnataka</option>
                                                <option value="Kerala">Kerala</option>
                                                <option value="Madhya Pradesh">Madhya Pradesh</option>
                                                <option value="Maharashtra">Maharashtra</option>
                                                <option value="Manipur">Manipur</option>
                                                <option value="Meghalaya">Meghalaya</option>
                                                <option value="Mizoram">Mizoram</option>
                                                <option value="Nagaland">Nagaland</option>
                                                <option value="Odisha">Odisha</option>
                                                <option value="Punjab">Punjab</option>
                                                <option value="Rajasthan">Rajasthan</option>
                                                <option value="Sikkim">Sikkim</option>
                                                <option value="Tamil Nadu">Tamil Nadu</option>
                                                <option value="Telangana">Telangana</option>
                                                <option value="Tripura">Tripura</option>
                                                <option value="Uttar Pradesh">Uttar Pradesh</option>
                                                <option value="Uttarakhand">Uttarakhand</option>
                                                <option value="West Bengal">West Bengal</option>
                                            </select>
                                            @error('wholesale_price')
                                                <span class="text-danger">
                                                    {{ $message }}
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="mb-0 col-6">
                                            <label for="formrow-email-input" class="form-label">Country</label>
                                            <input type="text" class="form-control" id="coutry"
                                                value="{{ old('coutry', $customer->country) }}" name="coutry" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-12 row mb-3">
                                        <div class="mb-0 col-6">
                                            <label for="formrow-email-input" class="form-label">Pin Code</label>
                                            <input type="number" class="form-control" id="pin_code"
                                                value="{{ old('pin_code', $customer->zip) }}" name="pin_code">
                                        </div>

                                        <div class="mb-0 col-6">
                                            <label for="formrow-email-input" class="form-label">Status</label>
                                            <select class="form-control select2-search-disable" class="customer_status"
                                                name="customer_status" id="customer_status">
                                                <option value=1
                                                    {{ old('customer_status', $customer->status) == 1 ? 'selected' : '' }}>
                                                    Active
                                                </option>
                                                <option value=0
                                                    {{ old('customer_status', $customer->status) == 0 ? 'selected' : '' }}>
                                                    Inactive
                                                </option>
                                        </div>
                                    </div>
                                    <div class="col-md-12 row mb-3">

                                    </div>
                                    {{-- <div class="col-md-12 row">
                                        <div class="mb-0 col-6">
                                            <label for="formrow-email-input" class="form-label">Pin Code</label>
                                            <input type="number" class="form-control" id="pin_code"
                                                value="{{ old('pin_code', $customer->zip) }}" name="pin_code">
                                        </div>


                                        <div class="mb-0 col-6">
                                            <label for="formrow-email-input" class="form-label">Net Primary
                                                price
                                                <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="product_netPrice"
                                                value="{{ old('product_netPrice') }}" name="product_netPrice" readonly>
                                        </div>
                                    </div> --}}
                                </div>
                            </div>
                        </div>
                        </form>
                    </div>
                    <!-- end card body -->
                </div>
            </div>

        </div> <!-- container-fluid -->
    </div>
    <!-- End Page-content -->

    <x-footer />



@endsection

@push('page-scripts')
    <script>
        let state = "{{ $customer->state }}";
    </script>
    <script src="{{ asset('assets/libs/select2/js/select2.min.js') }}"></script>
    <!-- form advanced init -->
    <script src="{{ asset('assets/js/pages/form-advanced.init.js') }}"></script>
    <script src="{{ asset('assets/js/customer.js') }}"></script>
@endpush

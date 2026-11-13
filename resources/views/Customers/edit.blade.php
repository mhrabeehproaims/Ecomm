@extends('layouts.app')
@push('styles')
    <link href="{{ asset('assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{ asset('assets/css/upload_product_image.css') }}">
@endpush
@section('title', 'Vantara | Create Product')
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
                                    <h4 class="mb-1 font-size-24">Add New Product</h4>
                                    <span class="text-muted">Add a new product to your store</span>
                                </div>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                                        <li class="breadcrumb-item">Create Product</li>
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
                                @method('PUT')
                                <div class="border border-dark-subtle rounded-3 mb-4">
                                    <div class="bg-light rounded-top-3 p-3">
                                        <h6 class="fs-5 fw-bold mb-0">Basic Information</h6>
                                    </div>
                                    <div class="p-3">
                                        <!-- Product Name -->
                                        <label for="profile_pic" class="col-sm-3 col-form-label">
                                            Customer Image <span class="text-danger">*</span>
                                        </label>
                                        <div class="mb-3 row align-items-center">

                                            <!-- Current Profile Image -->
                                            <div class="col-sm-6 col-md-3">
                                                @if ($customer->profile_pic)
                                                    <img src="{{ asset('storage/CustomerImage/' . $customer->profile_pic) }}"
                                                        alt="Profile Picture" class="img-thumbnail"
                                                        style="width: 80px; height: 80px; object-fit: cover;">
                                                @else
                                                    <img src="{{ asset('assets/images/users/user-dummy-img.jpg') }}"
                                                        alt="No Image" class="img-thumbnail"
                                                        style="width: 80px; height: 80px; object-fit: cover;">
                                                @endif
                                            </div>

                                            <!-- Upload New Image -->
                                            <div class="col-sm-12 col-md-9">
                                                <input type="file" class="form-control" id="profile_pic"
                                                    name="profile_pic" accept="image/*">
                                                <small class="text-muted font-weight-300">Max size 2MB. Allowed types: jpg, png,
                                                    jpeg.</small>
                                            </div>
                                        </div>


                                        <div class="mb-3">
                                            <label for="first_name" class="form-label">Customer First Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text"
                                                class="form-control @error('first_name') is-invalid @enderror"
                                                id="first_name" name="first_name"
                                                value="{{ old('first_name', $customer->first_name) }}" required>
                                            @error('first_name')
                                                <span class="text-danger">
                                                    {{ $message }}
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="last_name" class="form-label">Customer Last Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text"
                                                class="form-control @error('last_name') is-invalid @enderror" id="last_name"
                                                name="last_name" value="{{ old('last_name', $customer->last_name) }}"
                                                required>
                                            @error('last_name')
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
                                    <h6 class="fs-5 fw-bold mb-0">Address & Location</h6>
                                </div>

                                <div class="row p-3">
                                    <div class="col-md-12 row">
                                        <div class="mb-3 col-12">
                                            <label for="formrow-email-input" class="form-label">Address<span
                                                    class="text-danger">*</span></label>

                                            <textarea name="address" rows="4" class="form-control @error('address') is-invalid @enderror" id="address">{{ old('address', $customer->address) }}</textarea>
                                            @error('address')
                                                <span class="text-danger">
                                                    {{ $message }}
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-12 row">
                                        <div class="mb-3 col-6">
                                            <label for="formrow-email-input" class="form-label">State</label>
                                            <select name="state" class="form-select select2" id="state">
                                                <option value="">-- Select State --</option>
                                                <option value="Andhra Pradesh" @selected($customer->state == 'Andhra Pradesh')>Andhra Pradesh
                                                </option>
                                                <option value="Arunachal Pradesh" @selected($customer->state == 'Arunachal Pradesh')>Arunachal
                                                    Pradesh</option>
                                                <option value="Assam" @selected($customer->state == 'Assam')>Assam</option>
                                                <option value="Bihar" @selected($customer->state == 'Bihar')>Bihar</option>
                                                <option value="Chhattisgarh" @selected($customer->state == 'Chhattisgarh')>Chhattisgarh
                                                </option>
                                                <option value="Goa" @selected($customer->state == 'Goa')>Goa</option>
                                                <option value="Gujarat" @selected($customer->state == 'Gujarat')>Gujarat</option>
                                                <option value="Haryana" @selected($customer->state == 'Haryana')>Haryana</option>
                                                <option value="Himachal Pradesh" @selected($customer->state == 'Himachal Pradesh')>Himachal
                                                    Pradesh</option>
                                                <option value="Jharkhand" @selected($customer->state == 'Jharkhand')>Jharkhand</option>
                                                <option value="Karnataka" @selected($customer->state == 'Karnataka')>Karnataka</option>
                                                <option value="Kerala" @selected($customer->state == 'Kerala')>Kerala</option>
                                                <option value="Madhya Pradesh" @selected($customer->state == 'Madhya Pradesh')>Madhya Pradesh
                                                </option>
                                                <option value="Maharashtra" @selected($customer->state == 'Maharashtra')>Maharashtra
                                                </option>
                                                <option value="Manipur" @selected($customer->state == 'Manipur')>Manipur</option>
                                                <option value="Meghalaya" @selected($customer->state == 'Meghalaya')>Meghalaya</option>
                                                <option value="Mizoram" @selected($customer->state == 'Mizoram')>Mizoram</option>
                                                <option value="Nagaland" @selected($customer->state == 'Nagaland')>Nagaland</option>
                                                <option value="Odisha" @selected($customer->state == 'Odisha')>Odisha</option>
                                                <option value="Punjab" @selected($customer->state == 'Punjab')>Punjab</option>
                                                <option value="Rajasthan" @selected($customer->state == 'Rajasthan')>Rajasthan</option>
                                                <option value="Sikkim" @selected($customer->state == 'Sikkim')>Sikkim</option>
                                                <option value="Tamil Nadu" @selected($customer->state == 'Tamil Nadu')>Tamil Nadu</option>
                                                <option value="Telangana" @selected($customer->state == 'Telangana')>Telangana</option>
                                                <option value="Tripura" @selected($customer->state == 'Tripura')>Tripura</option>
                                                <option value="Uttar Pradesh" @selected($customer->state == 'Uttar Pradesh')>Uttar Pradesh</option>
                                                <option value="Uttarakhand" @selected($customer->state == 'Uttarakhand')>Uttarakhand</option>
                                                <option value="West Bengal" @selected($customer->state == 'West Bengal')>West Bengal</option>
                                            </select>
                                            @error('wholesale_price')
                                                <span class="text-danger">
                                                    {{ $message }}
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="mb-0 col-6">
                                            <label for="formrow-email-input" class="form-label">Country</label>
                                            <input type="text" class="form-control" id="country"
                                                value="{{ old('country', $customer->country) }}" name="country">
                                        </div>
                                    </div>

                                    <div class="col-md-12 row">
                                        <div class="mb-3 col-6">
                                            <label for="formrow-email-input" class="form-label">Pin Code</label>
                                            <input type="number" class="form-control" id="zip"
                                                value="{{ old('zip', $customer->zip) }}" name="zip">
                                        </div>
                                        <div class="mb-3 col-6">
                                            <label for="formrow-email-input" class="form-label">City</label>
                                            <input type="text" class="form-control" id="city"
                                                value="{{ old('city', $customer->city) }}" name="city">

                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="mb-3 col-12">
                                            <label for="formrow-email-input" class="form-label">Status</label>
                                            <select class="form-select select2-search-disable" class="status"
                                                name="status" id="">
                                                <option value=1
                                                    {{ old('status', $customer->status) == 1 ? 'selected' : '' }}>
                                                    Active
                                                </option>
                                                <option value=0
                                                    {{ old('status', $customer->status) == 0 ? 'selected' : '' }}>
                                                    Inactive
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-outline-secondary w-md fw-medium"> <i
                                        class="fas fa-save"></i> &nbsp; Update Customer</button>
                            </div>
                            </form>
                        </div>
                        <!-- end card body -->
                    </div>
                </div>

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

        <x-delete-modal id="DeleteCategoryModal" form_id="delete-categ" />

        <x-footer />
    </div>


@endsection

@push('page-scripts')

    <script>
        let oldSubcategory = "{{ old('subcategory') }}";
    </script>
    <script src="{{ asset('assets/libs/select2/js/select2.min.js') }}"></script>
    <!-- form advanced init -->
    <script src="{{ asset('assets/js/pages/form-advanced.init.js') }}"></script>
    <script src="{{ asset('assets/js/upload_product_image.js') }}"></script>
    <script src="{{ asset('assets/js/product_calculations.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if (session('message'))
        <script>
            Swal.fire({
                title: 'Product Created!',
                text: "Do you want to add more?",
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: 'No, Go Back',
                cancelButtonText: 'Yes, Add More'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('products.index') }}";
                }
                // else {
                //     window.location.href = "{{ route('products.create') }}";
                // }
            });
        </script>
    @endif
@endpush

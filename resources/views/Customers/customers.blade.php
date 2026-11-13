@extends('layouts.app')
@section('title', 'Customers')
@section('content')

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Customer Management</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Customers</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->
                {{-- <div class="create-btn py-2">
                    <button class="btn btn-light" id="create_size">Create Size</button>
                </div> --}}

                <div class="row flex justify-content-center">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="container-fluid">
                                    <div class="row py-1">
                                        <form action="{{ route('customers.index') }}" method="get" class="row">
                                            {{-- @csrf is NOT needed for GET requests --}}
                                            <div class="col-lg-3 col-md-6">
                                                <input type="text" class="form-control" name="user_name_phone"
                                                    placeholder="Search by name or phone"
                                                    value="{{ request('user_name_phone') }}" required>
                                            </div>
                                            <div class="col-2 col-md-3 col-sm-6">
                                                <input type="submit" class="btn btn-light" value="Search">

                                                <a href="{{ route('customers.index') }}" class="btn btn-light">Clear</a>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <table id="" class="table table-bordered dt-responsive  nowrap w-100">
                                    <thead>
                                        <tr class="text-center">
                                            <th class="text-center">#</th>
                                            <th class="text-center">Image</th>
                                            <th class="text-center">Name</th>
                                            <th class="text-center">Email</th>
                                            <th class="text-center">Phone</th>
                                            <th class="text-center">Address</th>
                                            <th class="text-center">City</th>
                                            <th class="text-center">State</th>
                                            <th class="text-center">Pin</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($customers as $customer)
                                            <tr class="text-center align-middle">
                                                <td>{{ $loop->index + $customers->firstItem() }}</td>

                                                <td>
                                                    @if ($customer->profile_pic)
                                                        <img src="{{ asset('storage/CustomerImage/' . $customer->profile_pic) }}"
                                                            alt="Profile Picture" width="50" height="50">
                                                    @else
                                                        No Image
                                                    @endif
                                                </td>
                                                <td>{{ $customer->first_name . ' ' . $customer->last_name ?? '' }}</td>
                                                <td>{{ $customer->email ?? '' }}</td>
                                                <td>{{ $customer->phone ?? '' }}</td>
                                                <td>{{ $customer->address ?? '' }}</td>
                                                <td>{{ $customer->city ?? '' }}</td>
                                                <td>{{ $customer->state ?? '' }}</td>
                                                <td>{{ $customer->zip ?? '' }}</td>
                                                <td>
                                                    <div class="form-check form-switch d-flex justify-content-center">
                                                        <input class="form-check-input switchStatus text-center"
                                                            type="checkbox" role="switch"
                                                            data-id="{{ $customer->customer_id }}"
                                                            data-url="{{ route('customer.update.status', $customer->customer_id) }}"
                                                            name="switchStatus" style="font-size: 1.1rem"
                                                            {{ $customer->status == 1 ? 'checked' : '' }}>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('customers.purchaseHistory', encrypt($customer->customer_id)) }}"
                                                        class="btn m-0 p-1" data-name="{{ $customer->first_name }}"
                                                        title="Purchase history">
                                                        <i class="fa fa-history" aria-hidden="true"></i>
                                                    </a>

                                                    <a href="{{ route('customers.show', $customer->customer_id) }}"
                                                        class="btn editBtn m-0 m-0 p-1"
                                                        data-id={{ $customer->customer_id }}>
                                                        <i class="fas fa-pencil-alt"></i></a>
                                                    <button class="btn BtnDelete m-0 m-0 p-1"
                                                        data-name="{{ $customer->first_name }}"
                                                        data-id="{{ $customer->customer_id }}">
                                                        <i class="fa fa-trash" aria-hidden="true"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                {{ $customers->links() }}
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
        <x-delete-modal id="DeleteCustomerModal" form_id="deleteCustomerForm" />
        <x-footer />
    </div>
@endsection

@push('page-scripts')
    <script src="{{ asset('assets/js/toggleStatus.js') }}"></script>
    <script src="{{ asset('assets/js/customer.js') }}"></script>
@endpush

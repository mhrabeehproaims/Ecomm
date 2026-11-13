@extends('layouts.app')
@section('title', 'Delivery Partners')
@section('content')

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Delivery Partners</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Delivery Partners</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->
                {{-- <div class="create-btn py-2">
                    <button class="btn btn-light" id="create_size">Create Size</button>
                </div> --}}
                <div class="create-btn py-2">
                    <button class="btn btn-secondary" id="create_delivery_partner">Create Delivery Agent</button>
                </div>
                <div class="row flex justify-content-center">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                {{-- <div class="container-fluid">
                                    <div class="row py-1">
                                        <form action="{{ route('delivery_partners.index') }}" method="get" class="row"> --}}
                                {{-- @csrf is NOT needed for GET requests --}}
                                {{-- <div class="col-lg-3 col-md-6">
                                                <input type="text" class="form-control" name="user_name_phone"
                                                    placeholder="Search by name or phone"
                                                    value="{{ request('user_name_phone') }}" required>
                                            </div>
                                            <div class="col-2 col-md-3 col-sm-6">
                                                <input type="submit" class="btn btn-light" value="Search">

                                                <a href="{{ route('delivery_partners.index') }}"
                                                    class="btn btn-light">Clear</a>
                                            </div>
                                        </form>
                                    </div>
                                </div> --}}
                                <table id="datatable" class="table table-bordered dt-responsive  nowrap w-100">
                                    <thead>
                                        <tr class="text-center">
                                            <th class="text-center">#</th>
                                            <th class="text-center">Name</th>
                                            <th class="text-center">Phone</th>
                                            <th class="text-center">Contact Person</th>
                                            <th class="text-center">City</th>
                                            <th class="text-center">State</th>
                                            {{-- <th class="text-center">Country</th> --}}
                                            <th class="text-center">Pin</th>
                                            <th class="text-center" style="width: 20%">Email</th>
                                            <th class="text-center">Address</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($delivery_partners as $partner)
                                            <tr class="text-center align-middle">
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $partner->partner_name ?? '' }}</td>
                                                <td>{{ $partner->contact_phone ?? '' }}</td>
                                                <td>{{ $partner->contact_person ?? '' }}</td>
                                                <td>{{ $partner->city ?? '' }}</td>
                                                <td>{{ $partner->state ?? '' }}</td>
                                                <td>{{ $partner->pincode ?? '' }}</td>
                                                <td>{{ $partner->contact_email ?? '' }}</td>
                                                <td style="width: 20%">{{ $partner->address_line ?? '' }}</td>
                                                {{-- <td>{{ $partner->country ?? '' }}</td> --}}
                                                <td>
                                                    <div class="form-check form-switch d-flex justify-content-center">
                                                        <input class="form-check-input switchStatus text-center"
                                                            type="checkbox" role="switch"
                                                            data-id="{{ $partner->partner_id }}" {{-- data-url="{{ route('partner.update.status', $partner->partner_id) }}" --}}
                                                            name="switchStatus" style="font-size: 1.1rem"
                                                            {{ $partner->status == 'active' ? 'checked' : '' }}>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn editBtn m-0" data-id={{ $partner->partner_id }}
                                                        data-partner_name="{{ $partner->partner_name }}"
                                                        data-contact_person="{{ $partner->contact_person }}"
                                                        data-contact_email="{{ $partner->contact_email }}"
                                                        data-contact_phone="{{ $partner->contact_phone }}"
                                                        data-address_line="{{ $partner->address_line }}"
                                                        data-city="{{ $partner->city }}"
                                                        data-state="{{ $partner->state }}"
                                                        data-country="{{ $partner->country }}"
                                                        data-pincode="{{ $partner->pincode }}"
                                                        data-tracking_url="{{ $partner->tracking_url }}"
                                                        data-per_km_rate="{{ $partner->per_km_rate }}"
                                                        data-base_charge="{{ $partner->base_charge }}"
                                                        data-parnter_status="{{ $partner->status }}"
                                                        data-website="{{ $partner->website }}">
                                                        <i class="fas fa-pencil-alt"></i></button>
                                                    <button class="btn BtnDelete m-0 m-0 p-1"
                                                        data-name="{{ $partner->partner_name }}"
                                                        data-id="{{ $partner->partner_id }}">
                                                        <i class="fa fa-trash" aria-hidden="true"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                {{-- {{ $delivery_partners->links() }} --}}
                            </div>
                        </div>
                    </div> <!-- end col -->
                </div> <!-- end row -->

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

        {{-- Create size modal start --}}
        <div class="modal fade modal-lg" id="createPartnerModal" tabindex="-1" aria-labelledby="partnerModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <!-- Partner Details Modal -->
                    <form id="createPartnerForm">
                        <div class="modal-header">
                            <h5 class="modal-title" id="partnerModalLabel">Add Partner Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <!-- hidden input for ID -->
                        <input type="hidden" name="hidden_id" id="hidden_id">

                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="partner_name" class="form-label">Partner Name</label>
                                    <input type="text" name="partner_name" id="partner_name" class="form-control"
                                        placeholder="Enter partner name" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="contact_person" class="form-label">Contact Person</label>
                                    <input type="text" name="contact_person" id="contact_person" class="form-control"
                                        placeholder="Enter contact person" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="contact_email" class="form-label">Contact Email</label>
                                    <input type="email" name="contact_email" id="contact_email" class="form-control"
                                        placeholder="Enter contact email">
                                </div>

                                <div class="col-md-6">
                                    <label for="contact_phone" class="form-label">Contact Phone</label>
                                    <input type="text" name="contact_phone" id="contact_phone" class="form-control"
                                        placeholder="Enter phone number" required>
                                </div>

                                <div class="col-md-12">
                                    <label for="address_line1" class="form-label">Address</label>
                                    <input type="text" name="address_line" id="address_line" class="form-control"
                                        placeholder="Enter address" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" name="city" id="city" class="form-control"
                                        placeholder="Enter city" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="state" class="form-label">State</label>
                                    <input type="text" name="state" id="state" class="form-control"
                                        placeholder="Enter state" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="country" class="form-label">Country</label>
                                    <input type="text" name="country" id="country" class="form-control"
                                        placeholder="Enter country" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="pincode" class="form-label">Pincode</label>
                                    <input type="text" name="pincode" id="pincode" class="form-control"
                                        placeholder="Enter pincode" required>
                                </div>

                                {{-- <div class="col-md-12">
                                    <label for="tracking_url" class="form-label">Tracking URL</label>
                                    <input type="url" name="tracking_url" id="tracking_url" class="form-control"
                                        placeholder="https://example.com/tracking">
                                </div>

                                <div class="col-md-6">
                                    <label for="per_km_rate" class="form-label">Per KM Rate</label>
                                    <input type="number" step="0.01" name="per_km_rate" id="per_km_rate"
                                        class="form-control" placeholder="Enter rate per KM">
                                </div>

                                <div class="col-md-6">
                                    <label for="base_charge" class="form-label">Base Charge</label>
                                    <input type="number" step="0.01" name="base_charge" id="base_charge"
                                        class="form-control" placeholder="Enter base charge">
                                </div> --}}

                                <div class="col-md-6">
                                    <label for="status" class="form-label">Status</label>
                                    <select name="parnter_status" id="parnter_status" class="form-select">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="website" class="form-label">Tracking link</label>
                                    <input type="url" name="website" id="website" class="form-control"
                                        placeholder="https://tracking_link..">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer mt-3">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary"></button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
        <!-- end modal -->
        <x-delete-modal id="DeleteDeliveryPartnerModal" form_id="deleteDeliveryPartnerForm" />
        <x-footer />
    </div>
@endsection

@push('page-scripts')
    <script src="{{ asset('assets/js/delivery_parntner.js') }}"></script>
@endpush

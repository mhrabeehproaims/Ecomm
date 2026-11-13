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
@section('title', 'Wholesale Enquiry')
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Wholesale Enquiry</h4>

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
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <table id="datatable" class="table table-bordered dt-responsive w-100">
                                    <thead>
                                        <tr class="text-center">
                                            <th class="text-start" style="width: 5%">#</th>
                                            <th class="text-start" style="width: 10%">Date</th>
                                            <th class="text-start" style="width: 12%">Name</th>
                                            <th class="text-start" style="width: 15%">Email</th>
                                            <th class="text-start" style="width: 12%">Phone</th>
                                            <th class="text-start" style="width: 15%">Company</th>
                                            <th class="text-start" style="width: 20%">Message</th>
                                            {{-- <th class="text-start" style="width: 12%">Status</th> --}}
                                            <th class="text-start" style="width: 8%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($WholesaleEnquiry as $enquiry)
                                            <tr>
                                                <td class="text-center">
                                                    {{ $loop->iteration }}
                                                </td>
                                                <td class="text-start">{{ $enquiry->created_at?->format('d-m-Y') }}</td>
                                                <td class="text-start">{{ $enquiry->name }}</td>
                                                <td class="text-start">{{ $enquiry?->email }}</td>
                                                <td class="text-start">{{ $enquiry->phone }}</td>
                                                <td class="text-start">{{ $enquiry->company_name }}</td>
                                                <td class="text-start">{{ $enquiry->message }}</td>
                                                {{-- <td class="text-start">
                                                    <select class="form-select bg-light changeStatus"
                                                        data-id="{{ $enquiry->id }}">
                                                        <option value="pending"
                                                            {{ $enquiry->status == 'pending' ? 'selected' : '' }}>Pending
                                                        </option>
                                                        <option value="reviewed"
                                                            {{ $enquiry->status == 'reviewed' ? 'selected' : '' }}>Reviewed
                                                        </option>
                                                        <option value="resolved"
                                                            {{ $enquiry->status == 'resolved' ? 'selected' : '' }}>
                                                            Resolved</option>
                                                    </select>
                                                </td> --}}
                                                <td class="text-center"> <button class="btn BtnDelete m-0"
                                                        data-id="{{ encrypt($enquiry->id) }}" data-name="{{ $enquiry->name }}">
                                                        <i class="fa fa-trash" aria-hidden="true"></i></button></td>
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


        <x-delete-modal id="DeleteEnquiryModal" form_id="delete-enquiry" />

        <x-footer />
    </div>
@endsection

@push('page-scripts')
    s
    {{-- image view script  --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.3/viewer.min.js"></script>

    <script>
        document.querySelectorAll('.catgimage').forEach(img => {
            new Viewer(img);
        });
    </script>

    <script src="{{ asset('assets/js/enquiry.js') }}"></script>
@endpush

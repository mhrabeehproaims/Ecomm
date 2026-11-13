@extends('layouts.app')
@push('styles')
    {{-- image view lib --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.3/viewer.min.css" />

    {{-- to adjust image viwer bg color --}}
    <style>
        .viewer-backdrop {
            background-color: rgba(0, 0, 0, 0.7) !important;
        }

        .info-value {
            color: #2d3748;
            font-size: 15px;
            font-weight: 600;
        }

        .info-label {
            color: #718096;
            font-size: 14px;
            font-weight: 500;
        }
    </style>
@endpush
@section('title', 'Sales Return Management')
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Sales Return Management</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Sales Return</li>
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
                                <table id="" class="table table-bordered dt-responsive w-100">
                                    <thead>
                                        <tr class="text-center">
                                            <th class="text-start" style="width: 5%">#</th>
                                            <th class="text-start" style="width: 10%">Date</th>
                                            <th class="text-start" style="width: 10%">Ref. Order</th>
                                            <th class="text-start" style="width: 15%">Customer Name</th>
                                            <th class="text-start" style="width: 15%">Customer Contact</th>
                                            <th class="text-start" style="width: 15%">Demand</th>
                                            {{-- <th class="text-start" style="width: 12%">Item Count</th> --}}
                                            <th class="text-start" style="width: 12%">Status</th>
                                            <th class="text-start" style="width: 13%">Action</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($returns as $return)
                                            <tr>
                                                <td class="text-center">
                                                    {{ $loop->index + $returns->firstItem() }}
                                                </td>
                                                <td class="text-start">{{ $return->created_at?->format('d-m-Y') }}</td>
                                                <td class="text-start">
                                                    @if ($return->reforder)
                                                        <span class="badge bg-light text-dark fw-bold">
                                                            #VAN{{ str_pad($return->reforder->order_id ?? $return->reference_order_id, 7, '0', STR_PAD_LEFT) }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary text-white">None</span>
                                                    @endif
                                                </td>
                                                <td class="text-start">
                                                    {{ $return->customer?->first_name ?? ('' . ' ' . $return->customer?->last_name ?? '') }}
                                                </td>
                                                <td class="text-start">{{ $return->customer->phone ?? '' }}</td>
                                                <td class="text-start">{{ ucwords($return->return_type ?? '') }}</td>
                                                {{-- <td class="text-start">{{ $return->items_count ?? '' }}</td> --}}
                                                <td class="text-start">
                                                    <button class="btn btn-light btn-sm statusBtn"
                                                        data-curstatus="{{ $return->status ?? '' }}"
                                                        data-id="{{ $return->return_id ?? '' }}"
                                                        data-return_type="{{ $return->return_type ?? '' }}"
                                                        href="#"><strong>{{ \Illuminate\Support\Str::headline($return->status ?? '') }}</strong></button>
                                                </td>
                                                <td class="text-start">
                                                    <a href="{{ route('sales-return.show', encrypt($return->return_id)) }}"
                                                        class="btn btn-light btn-sm">
                                                        Return details
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                {{ $returns->links() }}
                            </div>
                        </div>
                    </div> <!-- end col -->
                </div> <!-- end row -->

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->
        <x-change_return_status_component />
        <x-footer />
    </div>
@endsection

@push('page-scripts')
    <script src="{{ asset('assets/js/return.js') }}"></script>
@endpush

@extends('layouts.app')
@section('title', 'Vantara | Stock History')
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Stock Management</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Stock Management</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->
                {{-- <div class="create-btn py-2"> --}}
                {{-- <button class="btn btn-secondary" id="create_brand">Create New Brand</button> --}}
                {{-- </div> --}}

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="py-2 mb-2">
                                    <h5>Product Name:
                                        {{ $product->product_name }}
                                    </h5>
                                </div>
                                <table id="datatable" class="table table-bordered dt-responsive  nowrap w-100">
                                    <thead>
                                        <tr class="text-center">
                                            <th class="text-center">#</th>
                                            <th class="text-center">Date</th>
                                            <th class="text-center">Quantity</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Stock</th>
                                            <th class="text-center">Notes</th>
                                        </tr>
                                    </thead>
                                    @php
                                        $totalQuantity = 0;
                                    @endphp
                                    <tbody>
                                        @forelse ($history as $stock)
                                            @php
                                                if ($stock->formatted_status == 'In') {
                                                    $totalQuantity += $stock->quantity;
                                                } else {
                                                    $totalQuantity -= $stock->quantity;
                                                }
                                            @endphp
                                            <tr>
                                                <td class="text-center align-middle">
                                                    {{ $loop->index + $history->firstItem() }}</td>
                                                <td class="text-center align-middle">{{ $stock->formatted_date }}</td>
                                                <td class="text-center align-middle">{{ $stock->quantity ?? '' }}
                                                </td>
                                                <td class="text-center align-middle">
                                                    {{ $stock->formatted_status ?? '' }}</td>
                                                <td class="text-center align-middle">{{ $totalQuantity }}</td>
                                                <td class="text-center align-middle">{{ $stock->notes ?? '' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No products found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                <div class="mt-3">
                                    {{ $history->links() }}
                                </div>
                            </div>
                        </div>
                    </div> <!-- end col -->
                </div> <!-- end row -->

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

        <x-delete-modal id="DeleteStockModal" form_id="delete-brand" />

        <x-footer />
    </div>
@endsection
@push('page-scripts')
    <script>
        $('#datatable').DataTable({
            paging: false, // Disable pagination
            info: false, // Disable "Showing x of y entries"
            searching: true, // Keep search if needed
            ordering: true, // Keep sorting
            lengthChange: false // Hide "Show X entries"
        });
    </script>
@endpush

@extends('layouts.app')
@section('title', 'Notifications | Vantara')
@push('styles')
    <style>
        .notification-row:hover {
            background-color: #f8f9fa;
            transition: background-color 0.2s;
        }

        .notification-row.table-warning:hover {
            background-color: #fff3cd;
        }
    </style>
@endpush
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Notifications</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Notifications</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->
                <div class="row flex justify-content-center">

                    <div class="col-12">
                        <div class="card">
                            <div class="col-sm-12 col-md-6">
                                @if (session()->has('message'))
                                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                        <strong>{{ session('title') }} ! &nbsp;</strong>{{ session('message') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body">

                                <table id="" class="table table-bordered dt-responsive  nowrap w-100">
                                    <thead class="text-center">
                                        <tr>
                                            <th>#</th>
                                            <th>Type</th>
                                            <th>Name</th>
                                            <th>Title</th>
                                            <th>Message</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($notifications as $notification)
                                            @php
                                                if ($notification->notifiable_type === 'App\Models\Order') {
                                                    $url = route('order.show', encrypt($notification->notifiable_id));
                                                } elseif ($notification->notifiable_type === 'App\Models\Product') {
                                                    $url = route('stocks.index');
                                                } elseif ($notification->notifiable_type === 'App\Models\SalesReturn') {
                                                    $url = route(
                                                        'sales-return.show',
                                                        encrypt($notification->notifiable_id),
                                                    );
                                                } elseif ($notification->notifiable_type === 'App\Models\Customer') {
                                                    $url = route('customers.show', $notification->notifiable_id);
                                                } else {
                                                    $url = '#';
                                                }
                                            @endphp

                                            <tr
                                                class="align-middle text-center notification-row {{ $notification->is_read ? '' : 'table-warning' }}">
                                                <td>{{ $loop->index + $notifications->firstItem() }}</td>
                                                <td class="text-capitalize">{{ $notification->type }}</td>

                                                {{-- Customer name based on notifiable_type --}}
                                                <td>
                                                    @if (
                                                        $notification->notifiable_type === 'App\Models\Order' ||
                                                            $notification->notifiable_type === 'App\Models\SalesReturn' ||
                                                            $notification->notifiable_type === 'App\Models\Customer')
                                                        {{ $notification->notifiable->customer->full_name ?? 'N/A' }}
                                                    @elseif($notification->notifiable_type === 'App\Models\Product')
                                                        {{ $notification->notifiable->product_name ?? 'N/A' }}
                                                    @else
                                                        {{ $notification->data['customer_name'] ?? 'N/A' }}
                                                    @endif
                                                </td>

                                                <td>{{ $notification->title }}</td>
                                                <td class="text-start btnRead" style="cursor: pointer;"
                                                    data-id="{{ $notification->id }}"
                                                    data-status="{{ $notification->is_read }}"
                                                    onclick="window.location='{{ $url }}'">
                                                    {{ $notification->message }}</td>
                                                <td onclick="event.stopPropagation();">
                                                    <button class="btn btn-sm btn-primary btnRead" title="Mark as read"
                                                        data-id="{{ $notification->id }}"
                                                        data-status="{{ $notification->is_read }}">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger btnDelete"
                                                        data-id="{{ $notification->id }}" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                {{ $notifications->links() }}
                            </div>
                        </div>
                    </div> <!-- end col -->
                </div> <!-- end row -->

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->


    @endsection
    @push('page-scripts')
        <script>
            $('.btnRead').on('click', function() {
                let id = $(this).data('id');
                $.ajax({
                    url: `/notification/${id}`, // dynamic JS string
                    type: 'patch',
                    data: {
                        _token: '{{ csrf_token() }}' // CSRF token required for Laravel
                    },
                    dataType: 'json',
                    success: function(response) {
                        console.log('Notification status updated:', response);
                        // Optional: visually mark it as read
                        $(`button[data-id="${id}"]`).closest('tr').removeClass('table-warning');
                    },
                    error: function(xhr) {
                        console.error('Error updating notification:', xhr.responseText);
                    }
                });
            });
        </script>
        <script>
            $('.btnDelete').on('click', function() {
                let id = $(this).data('id');
                $.ajax({
                    url: `/notification/${id}`, // dynamic JS string 
                    type: 'delete',
                    data: {
                        _token: '{{ csrf_token() }}' // CSRF token required for Laravel
                    },
                    dataType: 'json',
                    success: function(response) {
                        console.log('Notification status updated:', response);
                        // Optional: visually mark it as read
                        $(`button[data-id="${id}"]`).closest('tr').hide();
                    },
                    error: function(xhr) {
                        console.error('Error updating notification:', xhr.responseText);
                    }
                });
            });
        </script>
    @endpush

@extends('layouts.app')
@section('title', 'Deliveries')
@section('content')

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Today's Deliveries</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Today's Deliveries</li>
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
                                        <form action="{{ route('orders.today_deliveries') }}" method="get" class="row">
                                            {{-- @csrf is NOT needed for GET requests --}}
                                            <div class="col-lg-3 col-md-6">
                                                <input type="text" class="form-control" name="user_name_phone"
                                                    placeholder="Search by name or phone"
                                                    value="{{ request('user_name_phone') }}" required>
                                            </div>
                                            <div class="col-2 col-md-3 col-sm-6">
                                                <input type="submit" class="btn btn-light" value="Search">

                                                <a href="{{ route('orders.today_deliveries') }}"
                                                    class="btn btn-light">Clear</a>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <table id="" class="table table-bordered dt-responsive  nowrap w-100">
                                    <thead>
                                        <tr class="text-center">
                                            <th class="text-center">#</th>
                                            <th class="text-center">Name</th>
                                            <th class="text-center">Delivery Date</th>
                                            <th class="text-center">Phone</th>
                                            <th class="text-center">City</th>
                                            <th class="text-center">State</th>
                                            {{-- <th class="text-center">Zip Code</th> --}}
                                            <th class="text-center">Order Amount</th>
                                            {{-- <th class="text-center">State</th>
                                            <th class="text-center">Pin</th>
                                            <th class="text-center">Status</th> --}}
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($today_deliveries as $delivery)
                                            <tr class="text-center align-middle">
                                                <td>{{ $loop->index + $today_deliveries->firstItem() }}</td>
                                                <td>{{ $delivery->customer?->first_name . ' ' . $delivery->customer?->last_name ?? '' }}
                                                </td>
                                                <td><strong>
                                                        {{ date('d-m-Y', strtotime($delivery->delivery_date)) }}</strong>
                                                </td>
                                                <td>{{ $delivery->customer?->phone ?? '' }}</td>
                                                {{-- <td>{{ $delivery->customer->address ?? '' }}</td> --}}
                                                <td>{{ $delivery->customer?->city ?? '' }}</td>
                                                <td>{{ $delivery->customer?->state ?? '' }}</td>
                                                {{-- <td>{{ $delivery->customer->zip ?? '' }}</td> --}}
                                                <td>
                                                    <strong> ₹ {{ number_format($delivery->total_amount, 2) }}</strong>
                                                </td>

                                                <td class="gap-4">
                                                    <button class="btn btn-light btn-sm delivery_status"
                                                        data-bs-toggle="modal" data-bs-target="#addFollowupModal"
                                                        title="Add Follow-up"
                                                        data-user_email="{{ $delivery->customer?->email }}"
                                                        data-cur_status="{{ $delivery->latestDeliveryStatus?->delivery_status ?? 'call' }}"
                                                        data-order-id="{{ $delivery->order_id }}"
                                                        @if ($delivery->latestDeliveryStatus?->delivery_status == 'delivered') disabled @endif>
                                                        <strong>{{ ucwords($delivery->latestDeliveryStatus?->delivery_status ?? 'Call') }}</strong>
                                                    </button>

                                                    <button
                                                        class="btn btn-light m-0 py-1 ms-1 mt-1 mt-lg-0 showFollowupsHistory"
                                                        data-order-id="{{ $delivery->order_id }}" data-bs-toggle="modal"
                                                        title="Follow-up History">
                                                        <i class="fa fa-history" aria-hidden="true"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr class="text-center align-middle">
                                                <td colspan='8'>No deliveries scheduled for today.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                {{ $today_deliveries->links() }}
                            </div>
                        </div>
                    </div> <!-- end col -->
                </div> <!-- end row -->

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

        {{-- Create size modal start --}}
        <div class="modal fade modal-lg" id="showFollowupsModal" tabindex="-1" aria-labelledby="showFollowupsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="showFollowupsModalLabel">Follow-up History</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Status</th>
                                    <th>Note</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody id="followupsTableBody">
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- end modal -->
        <x-DeliveryStatusUpdateModal />
        <x-footer />
    </div>
@endsection

@push('page-scripts')
    <script>
        $('#addFollowupModal').on('show.bs.modal', function(event) {
            let button = $(event.relatedTarget);
            $('.note').val('');
            let cur_status = button.data('cur_status');
            $('.delivery_status').val(cur_status);
            let orderId = button.data('order-id');
            let user_email = button.data('user_email');
            $('#user_email').val(user_email);
            $('#order_id').val(orderId);
        });

        // When modal opens, store orderId in a hidden input or a variable
        // $('#addFollowupModal').on('show.bs.modal', function(event) {
        //     const button = $(event.relatedTarget); // Button that triggered the modal
        //     const orderId = button.data('order-id'); // Extract order ID from data attribute
        //     $('#followupForm').find('input[name="order_id"]').val(orderId); // store it in form
        // });

        // Handle form submit
        $('#followupForm').on('submit', function(e) {
            e.preventDefault();

            const orderId = $(this).find('input[name="order_id"]').val(); // retrieve the orderId
            const form = $(this);

            $.ajax({
                url: 'today_deliveries/add_followup',
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    // Update the text dynamically
                    const newStatus = response.status.toLowerCase();
                    const btn = $(".delivery_status[data-order-id='" + orderId + "']");
                    btn.find("strong").text(newStatus.charAt(0).toUpperCase() + newStatus.slice(1));
                    if (newStatus == "delivered") {
                        btn.attr('disabled', true);
                    }
                    // 🔧 Update the button's data attribute
                    btn.data('cur_status', newStatus);
                    btn.attr('data-cur_status', newStatus);

                    $(".delivery_status[data-order-id='" + orderId + "'] strong")
                        .text(newStatus.charAt(0).toUpperCase() + newStatus.slice(1));
                    $('#addFollowupModal').find('select.delivery_status').val('delivered');
                    $('#addFollowupModal').modal('hide');
                },
                error: function() {
                    alert('Failed to save follow-up. Please try again.');
                }
            });
        });

        $('.showFollowupsHistory').on('click', function(e) {
            e.preventDefault();
            let id = $(this).data('order-id');
            $.ajax({
                url: 'today_deliveries/show_history/' + id,
                method: 'GET',
                success: function(response) {
                    if (response.status === "success") {
                        let followups = response.data;
                        let tbody = $("#followupsTableBody");
                        tbody.empty(); // clear old data

                        if (followups.length > 0) {
                            $.each(followups, function(index, item) {
                                tbody.append(`
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${item.delivery_status}</td>
                                    <td>${item.note ?? '-'}</td>
                                    <td>${new Date(item.created_at).toLocaleString()}</td>
                                </tr>
                            `);
                            });
                        } else {
                            tbody.append(
                                '<tr><td colspan="4" class="text-center text-muted">No follow-ups found</td></tr>'
                            );
                        }

                        $("#showFollowupsModal").modal('show');
                    } else {
                        alert('Failed to fetch followups!');
                    }
                },
                error: function() {
                    alert('Failed to save follow-up. Please try again.');
                }
            });
        });
    </script>
@endpush

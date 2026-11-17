@extends('layouts.app')
@section('title', 'Dashboard | Vantara')
@section('content')
    @push('styles')
        <style>
            .dashboard-stat-carousel { position: relative; overflow: hidden; }
            .dashboard-stat-carousel-track { flex-wrap: nowrap; overflow-x: auto; margin-inline: -12px; padding-inline: 12px 4px; scroll-snap-type: x mandatory; scrollbar-width: thin; }
            .dashboard-stat-carousel-track > [class*="col-"] { flex: 0 0 33.333333%; max-width: 33.333333%; scroll-snap-align: start; }
            .dashboard-stat-carousel-track .card { height: calc(100% - 24px); }
            @media (max-width: 767.98px) { .dashboard-stat-carousel-track > [class*="col-"] { flex-basis: 86%; max-width: 86%; } }
        </style>
    @endpush
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Dashboard</h4>

                            {{-- <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboards</a></li>
                                    <li class="breadcrumb-item active">Dashboard</li>
                                </ol>
                            </div> --}}

                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row">
                    {{-- <div class="col-xl-4"> --}}
                    {{-- <div class="card overflow-hidden">
                            <div class="bg-primary-subtle">
                                <div class="row">
                                    <div class="col-7">
                                        <div class="text-primary p-3">
                                            <h5 class="text-primary">Welcome Back !</h5>
                                            <p>Skote Dashboard</p>
                                        </div>
                                    </div>
                                    <div class="col-5 align-self-end">
                                        <img src="assets/images/profile-img.png" alt="" class="img-fluid">
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="avatar-md profile-user-wid mb-4">
                                            <img src="assets/images/users/avatar-1.jpg" alt=""
                                                class="img-thumbnail rounded-circle">
                                        </div>
                                        <h5 class="font-size-15 text-truncate">Henry Price</h5>
                                        <p class="text-muted mb-0 text-truncate">UI/UX Designer</p>
                                    </div>

                                    <div class="col-sm-8">
                                        <div class="pt-4">

                                            <div class="row">
                                                <div class="col-6">
                                                    <h5 class="font-size-15">125</h5>
                                                    <p class="text-muted mb-0">Projects</p>
                                                </div>
                                                <div class="col-6">
                                                    <h5 class="font-size-15">$1245</h5>
                                                    <p class="text-muted mb-0">Revenue</p>
                                                </div>
                                            </div>
                                            <div class="mt-4">
                                                <a href="javascript: void(0);"
                                                    class="btn btn-primary waves-effect waves-light btn-sm">View
                                                    Profile <i class="mdi mdi-arrow-right ms-1"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> --}}
                    {{-- <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4">Monthly Earning</h4>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <p class="text-muted">This month</p>
                                        <h3>$34,252</h3>
                                        <p class="text-muted"><span class="text-success me-2"> 12% <i
                                                    class="mdi mdi-arrow-up"></i> </span> From previous period</p>

                                        <div class="mt-4">
                                            <a href="javascript: void(0);"
                                                class="btn btn-primary waves-effect waves-light btn-sm">View More
                                                <i class="mdi mdi-arrow-right ms-1"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mt-4 mt-sm-0">
                                            <div id="radialBar-chart" data-colors='["--bs-primary"]' class="apex-charts">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-muted mb-0">We craft digital, graphic and dimensional thinking.</p>
                            </div>
                        </div> --}}
                    {{-- </div> --}}
                    <div class="col-xl-12">
                        <div class="dashboard-stat-carousel" aria-label="Dashboard statistics carousel">
                        <div class="dashboard-stat-carousel-track row">
                            <div class="col-md-4">
                                <div class="card mini-stats-wid">
                                    <div class="card-body">
                                        <div class="d-flex">
                                            <div class="flex-grow-1">
                                                <p class="text-muted fw-medium">Sales</p>
                                                <h4 class="mb-0">$35, 723</h4>
                                            </div>

                                            <div class="flex-shrink-0 align-self-center ">
                                                <div class="avatar-sm rounded-circle bg-primary mini-stat-icon">
                                                    <span class="avatar-title rounded-circle bg-primary">
                                                        <i class="bx bx-archive-in font-size-24"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card mini-stats-wid">
                                    <div class="card-body">
                                        <div class="d-flex">
                                            <div class="flex-grow-1">
                                                <p class="text-muted fw-medium">Total Orders</p>
                                                <h4 class="mb-0">{{ $total_orders ?? '' }}</h4>

                                            </div>

                                            <div class="flex-shrink-0 align-self-center">
                                                <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                                                    <span class="avatar-title">
                                                        <i class="bx bx-copy-alt font-size-24"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card mini-stats-wid">
                                    <div class="card-body">
                                        <div class="d-flex">
                                            <div class="flex-grow-1">
                                                <p class="text-muted fw-medium">Total Returns</p>
                                                <h4 class="mb-0">{{ $total_return ?? '' }}</h4>
                                            </div>

                                            <div class="flex-shrink-0 align-self-center">
                                                <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                                                    <span class="avatar-title">
                                                        <i class="bx bx-copy-alt font-size-24"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card mini-stats-wid">
                                    <div class="card-body">
                                        <div class="d-flex">
                                            <div class="flex-grow-1">
                                                <p class="text-muted fw-medium">Monthly Earnings</p>
                                                <h4 class="mb-0">$16.2</h4>
                                            </div>

                                            <div class="flex-shrink-0 align-self-center">
                                                <div class="avatar-sm rounded-circle bg-primary mini-stat-icon">
                                                    <span class="avatar-title rounded-circle bg-primary">
                                                        <i class="bx bx-purchase-tag-alt font-size-24"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                        <!-- end row -->

                        {{-- <div class="card">
                            <div class="card-body">
                                <div class="d-sm-flex flex-wrap">
                                    <h4 class="card-title mb-4">Email Sent</h4>
                                    <div class="ms-auto">
                                        <ul class="nav nav-pills">
                                            <li class="nav-item">
                                                <a class="nav-link" href="#">Week</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" href="#">Month</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link active" href="#">Year</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div id="stacked-column-chart" class="apex-charts"
                                    data-colors='["--bs-primary", "--bs-warning", "--bs-success"]' dir="ltr">
                                </div>
                            </div>
                        </div> --}}
                    </div>
                </div>
                <!-- end row -->

                {{-- <div class="row">
                    <div class="col-xl-4">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4">Social Source</h4>
                                <div class="text-center">
                                    <div class="avatar-sm mx-auto mb-4">
                                        <span class="avatar-title rounded-circle bg-primary-subtle font-size-24">
                                            <i class="mdi mdi-facebook text-primary"></i>
                                        </span>
                                    </div>
                                    <p class="font-16 text-muted mb-2"></p>
                                    <h5><a href="javascript: void(0);" class="text-dark">Facebook - <span
                                                class="text-muted font-16">125 sales</span> </a></h5>
                                    <p class="text-muted">Maecenas nec odio et ante tincidunt tempus. Donec vitae
                                        sapien ut libero venenatis faucibus tincidunt.</p>
                                    <a href="javascript: void(0);" class="text-primary font-16">Learn more <i
                                            class="mdi mdi-chevron-right"></i></a>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-4">
                                        <div class="social-source text-center mt-3">
                                            <div class="avatar-xs mx-auto mb-3">
                                                <span class="avatar-title rounded-circle bg-primary font-size-16">
                                                    <i class="mdi mdi-facebook text-white"></i>
                                                </span>
                                            </div>
                                            <h5 class="font-size-15">Facebook</h5>
                                            <p class="text-muted mb-0">125 sales</p>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="social-source text-center mt-3">
                                            <div class="avatar-xs mx-auto mb-3">
                                                <span class="avatar-title rounded-circle bg-info font-size-16">
                                                    <i class="mdi mdi-twitter text-white"></i>
                                                </span>
                                            </div>
                                            <h5 class="font-size-15">Twitter</h5>
                                            <p class="text-muted mb-0">112 sales</p>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="social-source text-center mt-3">
                                            <div class="avatar-xs mx-auto mb-3">
                                                <span class="avatar-title rounded-circle bg-pink font-size-16">
                                                    <i class="mdi mdi-instagram text-white"></i>
                                                </span>
                                            </div>
                                            <h5 class="font-size-15">Instagram</h5>
                                            <p class="text-muted mb-0">104 sales</p>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-5">Activity</h4>
                                <ul class="verti-timeline list-unstyled">
                                    <li class="event-list">
                                        <div class="event-timeline-dot">
                                            <i class="bx bx-right-arrow-circle font-size-18"></i>
                                        </div>
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 me-3">
                                                <h5 class="font-size-14">22 Nov <i
                                                        class="bx bx-right-arrow-alt font-size-16 text-primary align-middle ms-2"></i>
                                                </h5>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div>
                                                    Responded to need “Volunteer Activities
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="event-list">
                                        <div class="event-timeline-dot">
                                            <i class="bx bx-right-arrow-circle font-size-18"></i>
                                        </div>
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 me-3">
                                                <h5 class="font-size-14">17 Nov <i
                                                        class="bx bx-right-arrow-alt font-size-16 text-primary align-middle ms-2"></i>
                                                </h5>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div>
                                                    Everyone realizes why a new common language would be
                                                    desirable... <a href="javascript: void(0);">Read more</a>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="event-list active">
                                        <div class="event-timeline-dot">
                                            <i class="bx bxs-right-arrow-circle font-size-18 bx-fade-right"></i>
                                        </div>
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 me-3">
                                                <h5 class="font-size-14">15 Nov <i
                                                        class="bx bx-right-arrow-alt font-size-16 text-primary align-middle ms-2"></i>
                                                </h5>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div>
                                                    Joined the group “Boardsmanship Forum”
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="event-list">
                                        <div class="event-timeline-dot">
                                            <i class="bx bx-right-arrow-circle font-size-18"></i>
                                        </div>
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 me-3">
                                                <h5 class="font-size-14">12 Nov <i
                                                        class="bx bx-right-arrow-alt font-size-16 text-primary align-middle ms-2"></i>
                                                </h5>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div>
                                                    Responded to need “In-Kind Opportunity”
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                                <div class="text-center mt-4"><a href="javascript: void(0);"
                                        class="btn btn-primary waves-effect waves-light btn-sm">View More <i
                                            class="mdi mdi-arrow-right ms-1"></i></a></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4">Top Cities Selling Product</h4>

                                <div class="text-center">
                                    <div class="mb-4">
                                        <i class="bx bx-map-pin text-primary display-4"></i>
                                    </div>
                                    <h3>1,456</h3>
                                    <p>San Francisco</p>
                                </div>

                                <div class="table-responsive mt-4">
                                    <table class="table align-middle table-nowrap">
                                        <tbody>
                                            <tr>
                                                <td style="width: 30%">
                                                    <p class="mb-0">San Francisco</p>
                                                </td>
                                                <td style="width: 25%">
                                                    <h5 class="mb-0">1,456</h5>
                                                </td>
                                                <td>
                                                    <div class="progress bg-transparent progress-sm">
                                                        <div class="progress-bar bg-primary rounded" role="progressbar"
                                                            style="width: 94%" aria-valuenow="94" aria-valuemin="0"
                                                            aria-valuemax="100"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <p class="mb-0">Los Angeles</p>
                                                </td>
                                                <td>
                                                    <h5 class="mb-0">1,123</h5>
                                                </td>
                                                <td>
                                                    <div class="progress bg-transparent progress-sm">
                                                        <div class="progress-bar bg-success rounded" role="progressbar"
                                                            style="width: 82%" aria-valuenow="82" aria-valuemin="0"
                                                            aria-valuemax="100"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <p class="mb-0">San Diego</p>
                                                </td>
                                                <td>
                                                    <h5 class="mb-0">1,026</h5>
                                                </td>
                                                <td>
                                                    <div class="progress bg-transparent progress-sm">
                                                        <div class="progress-bar bg-warning rounded" role="progressbar"
                                                            style="width: 70%" aria-valuenow="70" aria-valuemin="0"
                                                            aria-valuemax="100"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> --}}
                <!-- end row -->

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4">Latest 5 Orders</h4>
                                <div class="table-responsive">
                                    <table class="table align-middle table-nowrap mb-0">
                                        <thead class="table-light">
                                            <tr class="text-center">
                                                <th class="text-start" style="width: 5%">#</th>
                                                <th class="text-start" style="width: 10%">Order ID</th>
                                                <th class="text-start" style="width: 10%">Order Ref.</th>
                                                <th class="text-start" style="width: 10%">Date and Time</th>
                                                <th class="text-start" style="width: 15%">Customer Name</th>
                                                <th class="text-start" style="width: 10%">Contact</th>
                                                <th class="text-start" style="width: 10%">Total Amount</th>
                                                <th class="text-start" style="width: 8%">Payment Status</th>
                                                {{-- <th class="text-start" style="width: 12%">Item Count</th> --}}
                                                <th class="text-start" style="width: 12%">Order Status</th>
                                                <th class="text-start" style="width: 20%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($last_five_orders as $order)
                                                <tr>
                                                    <td class="text-center">
                                                        {{ $loop->index + 1 }}
                                                    </td>
                                                    <td>#VAN{{ str_pad($order->order_id, 7, '0', STR_PAD_LEFT) }}</td>



                                                    <td>{!! $order->reference_order_id
                                                        ? '<span class="badge bg-warning text-dark fw-bold">#VAN' .
                                                            str_pad($order->reference_order_id, 7, '0', STR_PAD_LEFT) .
                                                            '</span>'
                                                        : '<span class="badge bg-success text-white">New Order</span>' !!}</td>



                                                    <td class="text-start">{{ $order->created_at?->format('d-m-Y') }}</td>
                                                    <td class="text-start">
                                                        {{ $order->customer?->first_name ?? ('' . ' ' . $order->customer?->last_name ?? '') }}
                                                    </td>
                                                    <td class="text-start">{{ $order->customer->phone ?? '' }}</td>
                                                    <td class="text-center">₹{{ $order->total_amount }}</td>
                                                    <td class="text-start">{{ $order->payment_status ?? '' }}</td>
                                                    {{-- <td class="text-start">{{ $order->items_count ?? '' }}</td> --}}
                                                    <td class="text-start">
                                                        {{ ucwords($order->status) ?? '' }}
                                                    </td>
                                                    <td class="text-start">
                                                        {{-- <button class="btn BtnDelete m-0"
                                                        data-id="{{ encrypt($order->id) }}"
                                                        data-name="{{ $order->name }}">
                                                        <i class="fa fa-trash" aria-hidden="true"></i></button> --}}
                                                        {{-- <button class="btn btn-sm">Generate Invoice</button> --}}
                                                        <a href="{{ route('order.show', encrypt($order->order_id)) }}"
                                                            class="btn btn-light btn-sm">
                                                            Order details
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <!-- end table-responsive -->
                                <a href="{{ route('orders.index') }}" class="text-end pt-1">View more..</a>

                            </div>
                        </div>
                    </div>
                </div>
                <!-- end row -->
            </div>
            <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

        <!-- Transaction Modal -->
        <div class="modal fade transaction-detailModal" tabindex="-1" role="dialog"
            aria-labelledby="transaction-detailModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="transaction-detailModalLabel">Order Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">Product id: <span class="text-primary">#SK2540</span></p>
                        <p class="mb-4">Billing Name: <span class="text-primary">Neal Matthews</span></p>

                        <div class="table-responsive">
                            <table class="table align-middle table-nowrap">
                                <thead>
                                    <tr>
                                        <th scope="col">Product</th>
                                        <th scope="col">Product Name</th>
                                        <th scope="col">Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th scope="row">
                                            <div>
                                                <img src="assets/images/product/img-7.png" alt=""
                                                    class="avatar-sm">
                                            </div>
                                        </th>
                                        <td>
                                            <div>
                                                <h5 class="text-truncate font-size-14">Wireless Headphone (Black)
                                                </h5>
                                                <p class="text-muted mb-0">$ 225 x 1</p>
                                            </div>
                                        </td>
                                        <td>$ 255</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <div>
                                                <img src="assets/images/product/img-4.png" alt=""
                                                    class="avatar-sm">
                                            </div>
                                        </th>
                                        <td>
                                            <div>
                                                <h5 class="text-truncate font-size-14">Phone patterned cases</h5>
                                                <p class="text-muted mb-0">$ 145 x 1</p>
                                            </div>
                                        </td>
                                        <td>$ 145</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <h6 class="m-0 text-right">Sub Total:</h6>
                                        </td>
                                        <td>
                                            $ 400
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <h6 class="m-0 text-right">Shipping:</h6>
                                        </td>
                                        <td>
                                            Free
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <h6 class="m-0 text-right">Total:</h6>
                                        </td>
                                        <td>
                                            $ 400
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- end modal -->
        <x-footer />
    </div>
@endsection

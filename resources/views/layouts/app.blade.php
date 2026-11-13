<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8" />
    <title> @yield('title', 'Vantara E-Commerce') </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- App favicon -->

    <link rel="shortcut icon" href="{{ asset('assets/logo/vantara_favicon.png') }}">
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-X6ECTXQ74P"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());

        gtag('config', 'G-X6ECTXQ74P');
    </script>
    @stack('styles')
    <link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css') }}" rel="stylesheet"
        type="text/css" />
    <!-- Responsive datatable examples -->
    <link href="{{ asset('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}"
        rel="stylesheet" type="text/css" />

    <!-- Bootstrap Css -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    {{-- id="bootstrap-style" //bootstrap.min.css. its id was the problem for slashed routeing issue --}}
    <!-- Icons Css -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App js -->
    <script src="{{ asset('assets/js/plugin.js') }}"></script>

    {{-- Pusher --}}
    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
    <audio id="notifSound" src="{{ asset('assets/audio/notification_sound_1.wav') }}" preload="auto"></audio>
</head>

<body data-sidebar="dark1122" data-topbar="dark1122">

    <!-- Begin page -->
    <div id="layout-wrapper">


        <header id="page-topbar">
            <div class="navbar-header">
                <div class="d-flex">
                    <!-- LOGO -->
                    <div class="navbar-brand-box">
                        <a href="{{ route('home') }}" class="logo logo-dark">
                            <span class="logo-sm">
                                <img src="{{ asset('assets/logo/vantara_favicon.png') }}" alt="" height="22">
                            </span>
                            <span class="logo-lg">
                                <img src="{{ asset('assets/logo/vantara_logo.png') }}" alt="" height="35">
                            </span>
                        </a>

                        <a href="index.html" class="logo logo-light">
                            <span class="logo-sm">
                                <img src="{{ asset('assets/logo/vantara_favicon.png') }}" alt="" height="22">
                            </span>
                            <span class="logo-lg">
                                <img src="{{ asset('assets/logo/vantara_logo.png') }}" alt="" height="35">
                            </span>
                        </a>
                    </div>

                    <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect"
                        id="vertical-menu-btn">
                        <i class="fa fa-fw fa-bars"></i>
                    </button>

                    <!-- App Search-->

                </div>

                <div class="d-flex">

                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item noti-icon waves-effect"
                            id="page-header-notifications-dropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false">
                            <i class="bx bx-bell bx-tada"></i>
                            <span class="badge bg-danger rounded-pill">{{ $notificationCount }}</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                            aria-labelledby="page-header-notifications-dropdown">
                            <div class="p-3">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="m-0"> Notifications </h6>
                                    </div>
                                    <div class="col-auto">
                                        <a href="#!" class="small text-reset text-decoration-underline">
                                            Unread
                                            ( {{ $notificationCount }} )</a>
                                    </div>
                                </div>
                            </div>
                            <div data-simplebar style="max-height: 230px; background-color: #fcf8e3">
                                @foreach ($unread_nofications as $notifs)
                                    <a href="{{ route('notification.index') }}" class="text-reset notification-item">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 me-3">
                                                {{-- <img src="{{ asset('assets/images/users/avatar-3.jpg') }}"
                                                    class="rounded-circle avatar-sm" alt="user-pic"> --}}
                                                <i class='bx  bx-bell-check'></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">{{ $notifs->title }}</h6>
                                                <div class="font-size-13 text-muted">
                                                    {{-- <p class="mb-1">{{ $notifs->body }}.</p> --}}
                                                    <p class="mb-0"><i class="mdi mdi-clock-outline"></i>
                                                        <span>{{ $notifs->created_at->diffForHumans() }}</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                            <div class="p-2 border-top d-grid">
                                <a class="btn btn-sm btn-link font-size-14 text-center"
                                    href="{{ route('notification.index') }}">
                                    <i class="mdi mdi-arrow-right-circle me-1"></i> <span>View More..</span>
                                </a>
                            </div>
                        </div>
                        <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img class="rounded-circle header-profile-user"
                                src="{{ asset('assets/images/users/user-dummy-img.jpg') }}" alt="Header Avatar">
                            <span class="d-none d-xl-inline-block ms-1" key="t-henry">{{ auth()->user()->name }}</span>
                            <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <!-- item-->
                            <a class="dropdown-item" href="{{ route('admin.profile') }}"><i
                                    class="bx bx-user font-size-16 align-middle me-1"></i> <span
                                    key="t-profile">Profile</span></a>
                            <a class="dropdown-item text-danger" href="{{ route('logout') }}"><i
                                    class="bx bx-power-off font-size-16 align-middle me-1 text-danger"></i> <span
                                    key="t-logout">Logout</span></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="toast-container top-0 end-0 p-3 rounded-4">
                <div id="orderToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header" style="background-color:#e2f1b6">
                        {{-- <img src="..." class=me-2" alt="..."> --}}
                        <i class="fa fa-fw fa fa-bell"></i>
                        <strong class="me-auto"> New Order</strong>
                        <small class="text-body-secondary">just now</small>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <a href="{{ route('orders.index') }}" style="text-decoration:none; color: inherit;">
                        <div class="toast-body" style="background-color:#f5f5e5">
                            <p class="mb-1 fw-semibold text-success">🛒 YOU HAVE A NEW ORDER!</p>
                            <p class="mb-2 text-muted">
                                New order from <strong id="customer_name" class="text-dark"></strong>
                                with a total of <strong id="order_amount" class="text-dark"></strong>.
                            </p>
                            <a href="{{ route('orders.index') }}"
                                class="text-decoration-none text-success fw-medium">View
                                Order →</a>
                        </div>

                    </a>
                </div>
            </div>
            <div class="toast-container top-0 end-0 p-3 rounded-4">
                <div id="returnToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header" style="background-color:#e2f1b6">
                        {{-- <img src="..." class=me-2" alt="..."> --}}
                        <i class="fa fa-fw fa fa-bell"></i>
                        <strong class="me-auto"> New Sales Return!</strong>
                        <small class="text-body-secondary">just now</small>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <a href="{{ route('sales-return.index') }}" style="text-decoration:none; color: inherit;">
                        <div class="toast-body" style="background-color:#f5f5e5">
                            <p class="mb-1 fw-semibold text-success">🛒 YOU HAVE NEW SALES RETURN!</p>
                            <p class="mb-2 text-muted">
                                New return from <strong id="return_customer_name" class="text-dark"></strong>
                                with a amount of <strong id="return_order_amount" class="text-dark"></strong>.
                            </p>
                            <a href="{{ route('sales-return.index') }}"
                                class="text-decoration-none text-success fw-medium">View
                                Order →</a>
                        </div>
                    </a>
                </div>
            </div>
        </header>

        <!-- ========== Left Sidebar Start ========== -->
        <div class="vertical-menu">

            <div data-simplebar class="h-100">

                <!--- Sidemenu -->
                <div id="sidebar-menu">
                    <!-- Left Menu Start -->
                    <ul class="metismenu list-unstyled" id="side-menu">
                        <li class="menu-title" key="t-menu">Menu</li>

                        <li>
                            <a href="{{ route('home') }}" class="has-arrodw waves-effect">
                                <i class="bx bx-home-circle"></i>
                                <span key="t-dashboards">Dashboard</span>
                            </a>
                        </li>

                        <li class="menu-title" key="t-apps">Masters</li>

                        <li>
                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                <i class="bx bx-receipt"></i>
                                <span key="t-ecommerce">Orders</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li><a href="{{ route('orders.index') }}" key="t-orders">Orders</a></li>
                                <li><a href="{{ route('sales-return.index') }}" key="t-orders">Sales Returns</a></li>
                                <li>
                                    <a href="{{ route('policies.index') }}" class="">
                                        <span key="t-dashboards">Retun Policy</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('delivery_partners.index') }}" class="">
                                        <span key="t-dashboards">Courier Services</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('orders.today_deliveries') }}" class="">
                                        <span key="t-dashboards">Todays Deliveries</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                <i class="bx bx-store"></i>
                                <span key="t-ecommerce">Products</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li><a href="{{ route('categories.index') }}" key="t-products">Categories</a></li>
                                <li><a href="{{ route('sub-categories.index') }}" key="t-products">Sub-Categories</a>
                                </li>
                                <li><a href="{{ route('brands.index') }}" key="t-products">Brand</a></li>
                                <li><a href="{{ route('units.index') }}" key="t-products">Unit</a></li>
                                <li><a href="{{ route('packages.index') }}" key="t-products">Package</a></li>
                                <li><a href="{{ route('sizes.index') }}" key="t-products">Size</a></li>
                                {{-- <li><a href="{{ route('weight.index') }}" key="t-products">Weight</a></li> --}}
                                <li><a href="{{ route('tax-master.index') }}" key="t-products">Tax Master</a></li>
                                <li>
                                    <a href="{{ route('stocks.index') }}" class="">
                                        <span key="t-dashboards">Product Stock</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('featured-products.index') }}" class="">
                                        <span key="t-dashboards">Featured Products</span>
                                    </a>
                                </li>
                                <li><a href="{{ route('products.index') }}" key="t-products">Products</a></li>
                            </ul>
                        </li>

                        <li>
                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                <i class="bx bxs-user-detail"></i>
                                <span key="t-ecommerce">Customer</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li> <a href="{{ route('customers.index') }}" class="">
                                        <span key="t-dashboards">Customers</span>
                                    </a></li>
                                <li>
                                    <a href="{{ route('web.wishlist.index') }}" class="">
                                        <span key="t-dashboards">Wishlisted Items</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('wholsale.enquiry') }}" class="">
                                        <span key="t-dashboards">Wholsale Enquiry</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                <i class="bx bx-calendar"></i>
                                <span key="t-ecommerce">Contents & Offers</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li>
                                    <a href="{{ route('coupons.index') }}" class="">
                                        <i class="bx bx-calendar"></i>
                                        <span key="t-dashboards">Coupon</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('banner.index') }}" class="">
                                        <i class="bx bx-calendar"></i>
                                        <span key="t-dashboards">Banner</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('offers.index') }}" class="">
                                        <i class="bx bx-calendar"></i>
                                        <span key="t-dashboards">Offer Zone</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li>
                            <a href="{{ route('admin.analytics') }}" class="">
                                <i class="bx bx-detail"></i>
                                <span key="t-dashboards">Analytics</span>
                            </a>
                        <li>
                            <a href="https://www.vanthara.com" class="" target="_blank">
                                <i class="bx bx-store"></i>
                                <span key="t-dashboards">Go to Store</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('notification.index') }}" class="">
                                <i class="bx bx-chat"></i>
                                <span key="t-dashboards">Notifications</span>
                            </a>
                        </li>
                </div>
                <!-- Sidebar -->
            </div>
        </div>
        <!-- Left Sidebar End -->

        <!-- main content start-->

        @yield('content')

        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->

    <!-- Right bar overlay-->
    <div class="rightbar-overlay"></div>

    <!-- JAVASCRIPT -->

    <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/metismenu/metisMenu.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>

    <!-- apexcharts -->
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>

    <!-- dashboard init -->
    <script src="{{ asset('assets/js/pages/dashboard.init.js') }}"></script>

    <!-- App js -->
    <script src="{{ asset('assets/js/app.js') }}"></script>

    {{-- @stack('datatable-scripts') --}}
    <!-- Required datatable js -->
    <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <!-- Buttons examples -->
    <script src="{{ asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/libs/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('assets/libs/pdfmake/build/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/libs/pdfmake/build/vfs_fonts.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-buttons/js/buttons.colVis.min.js') }}"></script>

    <!-- Responsive examples -->
    <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js') }}"></script>

    <!-- Datatable init js -->
    <script src="{{ asset('assets/js/pages/datatables.init.js') }}"></script>

    {{-- // Enable pusher --}}
    <script>
        const adminPusherKey = @json(config('broadcasting.connections.pusher.key'));
        if (adminPusherKey) {
        var pusher = new Pusher(adminPusherKey, {
            cluster: @json(config('broadcasting.connections.pusher.options.cluster')),
            forceTLS: true,
            channelAuthorization: {
                endpoint: @json(url('/broadcasting/auth')),
                transport: 'ajax',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            }
        });

        //Sales Order Notification
        var salesChannel = pusher.subscribe('private-my-channel');
        salesChannel.bind('my-event', function (data) {
            // alert(JSON.stringify(data));
            // console.log(data);

            var audio = document.getElementById('notifSound');
            if (audio) {
                audio.play().catch(function (error) {
                    console.log('Audio play was prevented:', error);
                });
            }
            $('#customer_name').text(data.cus_name);
            $('#order_amount').text(data.total_amount);
            $('#orderToast').toast({
                autohide: false
            }).toast('show');

        });

        // /Sales Return Notification
        var returnChannel = pusher.subscribe('private-sales-return');
        returnChannel.bind('sales-return-event', function (data) {
            // alert(JSON.stringify(data));
            // console.log(data);

            var audio = document.getElementById('notifSound');
            if (audio) {
                audio.play().catch(function (error) {
                    console.log('Audio play was prevented:', error);
                });
            }
            $('#return_customer_name').text(data.cus_name);
            $('#return_order_amount').text(data.return.total_amount);
            $('#returnToast').toast({
                autohide: false
            }).toast('show');
        });
        }
    </script>
    @stack('page-scripts')
</body>

</html>

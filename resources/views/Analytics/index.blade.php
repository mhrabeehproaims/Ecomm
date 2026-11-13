@extends('layouts.app')
@push('styles')
@push('styles')
    <style>
        .analytics-page,
        .analytics-page * {
            box-sizing: border-box;
        }

        .analytics-page {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #333;
            background: #f2f4f8;
            border-radius: 30px;
            /* padding: 40px 32px; */
        }

        .analytics-page .dashboard-container {
            position: relative;
            display: flex;
            width: 100%;
            min-height: 560px;
            padding: 32px 30px;
            border-radius: 32px;
            overflow: hidden;
            background: #f6f7fb;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.5),
                0 18px 45px rgba(15, 23, 42, 0.12);
        }

        .analytics-page .dashboard-container::after {
            content: "";
            position: absolute;
            top: -80px;
            right: -230px;
            width: 1000px;
            height: 1000px;
            background-image: url({{ asset('assets/images/world.webp') }});
            background-repeat: no-repeat;
            background-size: contain;
            background-position: center right;
            opacity: 0.98;
            filter: drop-shadow(0 25px 45px rgba(22, 78, 99, 0.22));
        }

        .analytics-page .dashboard-container::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(246, 247, 251, 0.95) 0%, rgba(246, 247, 251, 0.65) 54%, rgba(246, 247, 251, 0) 72%);
            z-index: 1;
        }

        .analytics-page .left-panel {
            position: relative;
            width: 550px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            z-index: 2;
        }

        .analytics-page .right-panel-controls {
            position: relative;
            flex-grow: 1;
            display: flex;
            justify-content: flex-end;
            align-items: flex-start;
            gap: 12px;
            z-index: 2;
            padding-left: 20px;
        }

        .analytics-page .card {
            background: #fff;
            padding: 20px;
            border-radius: 14px;
            box-shadow: 0 12px 24px rgba(76, 107, 136, 0.12);
            border: 1px solid rgba(226, 232, 240, 0.65);
        }

        .analytics-page h3 {
            font-size: 14px;
            font-weight: 600;
            color: #666;
            margin-bottom: 15px;
        }

        .analytics-page .metric-value {
            font-size: 26px;
            font-weight: 700;
            color: #222;
        }

        .analytics-page .header-title {
            display: flex;
            align-items: center;
            font-size: 20px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .analytics-page .header-icon {
            margin-right: 8px;
            font-size: 20px;
        }

        .analytics-page .status-dot {
            height: 8px;
            width: 8px;
            background-color: #007bff;
            border-radius: 50%;
            display: inline-block;
            margin: 0 10px;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.2);
        }

        .analytics-page .status-text {
            font-size: 14px;
            font-weight: 400;
            color: #666;
        }

        .analytics-page .metrics-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .analytics-page .trend {
            color: #999;
            font-weight: 400;
            font-size: 20px;
            margin-left: 5px;
        }

        .analytics-page .mini-chart-line {
            height: 2px;
            width: 50px;
            background-color: #a0d3f0;
            margin-top: 15px;
            float: right;
        }

        .analytics-page .behavior-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .analytics-page .behavior-grid h4 {
            font-size: 13px;
            font-weight: 500;
            color: #666;
            margin-bottom: 10px;
        }

        .analytics-page .behavior-card {
            flex-grow: 1;
        }

        .analytics-page .search-bar {
            background: #fff;
            padding: 12px 20px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            width: 320px;
            box-shadow: 0 10px 28px rgba(148, 163, 184, 0.28);
            color: #94a3b8;
        }

        .analytics-page .search-bar input {
            border: none;
            outline: none;
            margin-left: 10px;
            width: 100%;
            font-size: 14px;
            color: #333;
        }

        .analytics-page .search-bar input::placeholder {
            color: #999;
        }

        .analytics-page .icon-btn {
            background: #fff;
            border: none;
            height: 40px;
            width: 40px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 16px;
            color: #475569;
            box-shadow: 0 10px 18px rgba(15, 23, 42, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .analytics-page .icon-btn:hover {
            background-color: rgba(241, 245, 249, 0.9);
        }
    </style>
@endpush
@section('title', 'Vantara | Create Product')
@section('content')
    <div class="main-content">
        <div class="page-content" style="background-color: rgb(254, 253, 253)">
            <div class="page-content-wrapper">
                {{-- <div class="container-fluid"> --}}
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">

                                <div class="page-title-left">
                                    <h4 class="mb-1 font-size-24">Analytics</h4>
                                    {{-- <span class="text-muted">Add a new product to your store</span> --}}
                                </div>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                                        <li class="breadcrumb-item">Analytics</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="casrd shadow-none">
                        <div class="card-bodsy p-0">
                            <div class="analytics-page">
                                <div class="dashboard-container">
                                    <div class="left-panel">
                                        <div class="header-title">
                                            <i class="mdi mdi-earth header-icon"></i>
                                            Live View
                                            <span class="status-dot"></span>
                                            <span class="status-text">Just now</span>
                                        </div>

                                        <div class="metrics-grid">
                                            <div class="card">
                                                <h3>Today's Visitors</h3>
                                                <p class="metric-value">{{ $todayVisitors ?? 0}}</p>
                                            </div>
                                            <div class="card">
                                                <h3>Total Visitors</h3>
                                                <p class="metric-value">{{ $totalVisitors ?? 0}}</p>
                                                {{-- <div class="mini-chart-line"></div> --}}
                                            </div>
                                            <div class="card">
                                                <h3>Active Visitors</h3>
                                                <p class="metric-value">{{ $activeVisitors ?? 0}}</p>
                                                {{-- <div class="mini-chart-line"></div> --}}
                                            </div>
                                            <div class="card">
                                                <h3>Active Customers</h3>
                                                <p class="metric-value">{{ $activeCustomers ?? 0}}</p>
                                                {{-- <div class="mini-chart-line"></div> --}}
                                            </div>
                                        </div>

                                        <div class="card behavior-card">
                                            <h3>Customer behavior</h3>
                                            <div class="behavior-grid">
                                                <a href="{{ route('web.carts.index') }}" class="behavior-item-link">
                                                    <div>
                                                        <h4>Active carts</h4>
                                                        <p class="metric-value">{{ $cartCount ?? 0 }}</p>
                                                    </div>
                                                </a>
                                                <a href="{{ route('admin.checkoutHistory') }}" class="behavior-item-link">
                                                    <div>
                                                        <h4>Checking out</h4>
                                                        <p class="metric-value">{{ $abandonedCheckouts ?? 0 }}</p>
                                                    </div>
                                                </a>
                                                <a href="{{ route('orders.index') }}" class="behavior-item-link">
                                                    <div>
                                                        <h4>Purchased</h4>
                                                        <p class="metric-value">{{ $purchaseCount ?? 0 }}</p>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>

                                        {{-- <div class="card">
                                            <h3>Sessions by location</h3>
                                        </div> --}}
                                    </div>

                                    {{-- <div class="right-panel-controls">
                                        <div class="search-bar">
                                            <i class="mdi mdi-magnify"></i>
                                            <input type="text" placeholder="Search location">
                                        </div>
                                        <button class="icon-btn"><i class="mdi mdi-eye-outline"></i></button>
                                        <button class="icon-btn"><i class="mdi mdi-map-outline"></i></button>
                                        <button class="icon-btn"><i class="mdi mdi-fullscreen"></i></button>
                                    </div> --}}
                                </div>
                            </div>
                        </div>
                    </div>
                    {{--
                </div> --}}
            </div>
        </div>
        <x-footer></x-footer>
@endsection
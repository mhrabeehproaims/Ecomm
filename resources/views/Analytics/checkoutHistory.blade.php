@extends('layouts.app')
@push('styles')
    <link href="{{ asset('assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .modal-content {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 24px 28px;
            border: none;
        }

        .modal-title {
            font-size: 22px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .modal-title i {
            font-size: 24px;
        }

        .btn-close {
            filter: brightness(0) invert(1);
            opacity: 0.8;
        }

        .btn-close:hover {
            opacity: 1;
        }

        .modal-body {
            padding: 32px 28px;
            background: #f8f9fa;
        }

        .detail-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .detail-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }

        .detail-card:last-child {
            margin-bottom: 0;
        }

        .detail-label {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            color: #6c757d;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }

        .detail-label i {
            color: #667eea;
            font-size: 16px;
        }

        .detail-value {
            font-size: 16px;
            color: #212529;
            font-weight: 500;
            padding-left: 26px;
        }

        .product-list {
            padding-left: 26px;
        }

        .product-item {
            background: #f8f9fa;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 3px solid #667eea;
        }

        .product-item:last-child {
            margin-bottom: 0;
        }

        .product-name {
            font-weight: 600;
            color: #495057;
            flex: 1;
        }

        .product-qty {
            background: #667eea;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .empty-state {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-style: italic;
        }

        @media (max-width: 768px) {
            .modal-body {
                padding: 24px 20px;
            }

            .detail-card {
                padding: 16px;
            }
        }
    </style>
@endpush

@section('title', 'Cart Product Management')
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Checkout History</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Checkout History</li>
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
                                <table id="datatabsle" class="table table-bordered dt-responsive w-100">
                                    <thead>
                                        <tr class="text-center">
                                            <th class="text-start" style="width: 5%">#</th>
                                            <th class="text-start" style="width: 15%">Customer Name</th>
                                            <th class="text-start" style="width: 15%">Phone</th>
                                            <th class="text-start" style="width: 15%">Location</th>
                                            <th class="text-start" style="width: 15%">Clicked Time</th>
                                            <th class="text-center" style="width: 15%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($allCheckouts as $checkout)
                                            <tr>
                                                <td class="text-center">
                                                    {{ $loop->index + 1 }}
                                                </td>
                                                <td class="text-start">
                                                    {{ $checkout->customer?->first_name ?? ('' . ' ' . $checkout->customer?->last_name ?? '') }}
                                                </td>
                                                @php
                                                    $phone = preg_replace('/\D/', '', $checkout->customer->phone ?? '');
                                                    // Add Indian country code if not present
                                                    if (!empty($phone) && !Str::startsWith($phone, '91')) {
                                                        $phone = '91' . $phone;
                                                    }
                                                @endphp

                                                <td class="text-start">
                                                    @if (!empty($checkout->customer->phone))
                                                        {{ $checkout->customer->phone }}
                                                        <a href="https://wa.me/{{ $phone }}?text={{ 'Hi! You recently added some items to your cart on Vantara. Just checking if you need any help with the products or if you’d like to place an order. Happy to assist!' }}"
                                                            target="_blank" class="ms-2 text-success" title="Chat on WhatsApp">
                                                            <i class="fab fa-whatsapp"></i>
                                                        </a>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="text-start">
                                                    {{ $checkout->customer?->city ?? '' }}
                                                </td>
                                                <td class="text-start">
                                                    {{ $checkout->clicked_at->format('d-m-Y H:i A') ?? $checkout->created_at->format('d-m-Y H:i A') }}
                                                </td>
                                                <td class="d-flex justify-content-center">
                                                    <button class="btn btn-outline-primary btn-sm btnShowCart"
                                                        data-id="{{ $checkout->id }}">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    {{ $allCheckouts->links() }}
                                </table>

                            </div>
                        </div>
                    </div> <!-- end col -->
                </div> <!-- end row -->

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

        <div class="modal fade modal-md" id="showStatusModal" tabindex="-1" aria-labelledby="showStatusModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form id="changeStatusForm">
                        <div class="modal-header">
                            <h5 class="modal-title" id="showStatusModalLabel">Update Order Status</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        {{-- hidden input (inside form now) --}}
                        <input type="hidden" name="hidden_id" id="hidden_id">

                        <div class="modal-body">
                            <div class="mb-3 text-center">
                                <span class="fw-semibold">Current Status:</span>
                                <span class="ms-2 info-value"></span>
                            </div>

                            <div class="mb-3">
                                <label for="statusSelect" class="form-label">Change Status</label>
                                <select name="statusSelect" id="statusSelect" class="form-select">
                                    <option value="pending">Pending</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="processing">Processing</option>
                                    <option value="shipped">Shipped</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="refunded">Refunded</option>
                                </select>
                            </div>
                        </div>


                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Confirm</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        {{-- Modal Start --}}
        <!-- Checkout Details Modal -->
        <div class="modal fade" id="cartDetailsModal" tabindex="-1" aria-labelledby="cartDetailsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title" id="cartDetailsModalLabel">
                            <i class="fas fa-shopping-cart"></i>
                            Checkout Details
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        <div class="detail-card">
                            <div class="detail-label">
                                <i class="fas fa-fingerprint"></i>
                                Session ID
                            </div>
                            <div class="detail-value" id="cd_session_id">ABC123XYZ789</div>
                        </div>

                        <div class="detail-card">
                            <div class="detail-label">
                                <i class="fas fa-clock"></i>
                                Clicked At
                            </div>
                            <div class="detail-value" id="cd_clicked_at">2025-11-27 14:30:45</div>
                        </div>

                        <div class="detail-card">
                            <div class="detail-label">
                                <i class="fas fa-user"></i>
                                Customer Name
                            </div>
                            <div class="detail-value" id="cd_customer_name">John Doe</div>
                        </div>

                        <div class="detail-card">
                            <div class="detail-label">
                                <i class="fas fa-box"></i>
                                Products
                            </div>
                            <div class="product-list" id="cd_products">
                                <div class="product-item">
                                    <span class="product-name">Wireless Headphones</span>
                                    <span class="product-qty">x2</span>
                                </div>
                                <div class="product-item">
                                    <span class="product-name">USB-C Cable</span>
                                    <span class="product-qty">x1</span>
                                </div>
                                <div class="product-item">
                                    <span class="product-name">Phone Case</span>
                                    <span class="product-qty">x3</span>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>

        {{-- Modal End --}}
        <x-footer />
    </div>
@endsection

@push('page-scripts')
    {{-- //for select2 start --}}
    <script src="{{ asset('assets/libs/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form-advanced.init.js') }}"></script>
    {{-- //for select2 end --}}
    <script>
        $('.select2').select2({
            placeholder: "--Select Customer--",
            allowClear: true
        });

        $('.btnShowCart').on('click', function () {
            let id = $(this).data('id');
            let url = "{{ route('web.carts.show', 0) }}".replace('/0', '/' + id);

            $.ajax({
                url: url,
                type: 'GET',
                success: function (response) {

                    // Fill modal fields safely
                    $('#cd_session_id').text(response.session_id ?? '-');
                    $('#cd_clicked_at').text(response.clicked_at ?? '-');
                    $('#cd_customer_name').text(response.customer_name ?? '-');

                    // Format product list
                    let productList = `<ul class="mb-0">`;

                    if (response.products && response.products.length > 0) {
                        response.products.forEach(item => {
                            productList += `<li>${item.name} - ${item.qty} Nos</li>`;
                        });
                    } else {
                        productList += `<li>The user has cleared all items from the cart.</li>`
                    }

                    productList += `</ul>`;

                    $('#cd_products').html(productList);

                    // Show modal
                    $('#cartDetailsModal').modal('show');
                },
                error: function (xhr) {
                    console.error('Error fetching cart details:', xhr.responseText);
                    alert('Failed to fetch cart details.');
                }
            });
        });
    </script>

@endpush
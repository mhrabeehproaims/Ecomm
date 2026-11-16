@extends('layouts.app')
@section('title', 'Customer Cart Items')
@push('styles')
    <link href="{{ asset('assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
@endpush
@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Cart Items</h4>
                <ol class="breadcrumb m-0"><li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li><li class="breadcrumb-item active">Cart Items</li></ol>
            </div>
            <p class="text-muted">See the products customers currently have in their shopping bags.</p>
            @if ($errors->any())
                <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <div class="card"><div class="card-body">
                <form action="{{ route('web.carts.index') }}" method="get" aria-label="Filter customer carts">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-4">
                            <label for="cart-customer" class="form-label">Customer</label>
                            <select class="form-select select2" id="cart-customer" name="customer">
                                <option value="">All customers</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->customer_id }}" @selected((string) ($filters['customer'] ?? '') === (string) $customer->customer_id)>{{ trim($customer->first_name.' '.$customer->last_name) }}{{ $customer->phone ? ' · '.$customer->phone : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="cart-product" class="form-label">Product</label>
                            <select class="form-select select2" id="cart-product" name="product">
                                <option value="">All products</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->product_id }}" @selected((string) ($filters['product'] ?? '') === (string) $product->product_id)>{{ $product->product_name }}{{ (int) $product->status === 1 ? '' : ' (inactive)' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4 d-flex gap-2">
                            <button class="btn btn-primary" type="submit">Filter</button>
                            <a class="btn btn-outline-secondary" href="{{ route('web.carts.index') }}">Clear filters</a>
                        </div>
                    </div>
                </form>
            </div></div>
            <div class="card"><div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h5 class="card-title mb-0">Customer shopping bags</h5>
                    <span class="text-muted">{{ $cart_products->total() }} cart {{ $cart_products->total() === 1 ? 'entry' : 'entries' }}</span>
                </div>
                <p class="text-muted font-size-12">Saved unit prices are the prices stored in the cart, not final checkout totals. Inactive or unavailable products remain visible here.</p>
                <div class="table-responsive">
                    <table id="customer-cart-table" class="table table-bordered table-hover align-middle w-100">
                        <thead><tr><th scope="col">#</th><th scope="col">Product</th><th scope="col">Customer</th><th scope="col">Contact</th><th scope="col">Quantity</th><th scope="col">Saved unit price</th><th scope="col">Availability</th><th scope="col">Added</th><th scope="col">Last updated</th></tr></thead>
                        <tbody>
                        @forelse ($cart_products as $cart)
                            <tr data-cart-id="{{ $cart->cart_id }}">
                                <td>{{ $cart_products->firstItem() + $loop->index }}</td>
                                <td><div class="d-flex align-items-center gap-3" style="min-width:200px">
                                    @if ($cart->product?->primaryImage?->image_path)
                                        <img src="{{ asset('storage/'.ltrim($cart->product->primaryImage->image_path, '/')) }}" alt="{{ $cart->product->product_name }}" width="52" height="68" loading="lazy" style="object-fit:contain;background:#f7f5f1" onerror="this.hidden=true;this.nextElementSibling.hidden=false">
                                        <span hidden class="text-muted font-size-12">No image</span>
                                    @else
                                        <span class="text-muted font-size-12">No image</span>
                                    @endif
                                    <div><span class="fw-medium">{{ $cart->product?->product_name ?? 'Product unavailable' }}</span>@if ($cart->product?->size?->size_name)<div class="text-muted font-size-12">Size: {{ $cart->product->size->size_name }}</div>@endif</div>
                                </div></td>
                                <td>{{ $cart->customer ? trim($cart->customer->first_name.' '.$cart->customer->last_name) : 'Guest / customer unavailable' }}</td>
                                <td class="text-nowrap">{{ $cart->customer?->phone ?: '—' }}@if ($cart->customer?->email)<div class="text-muted font-size-12">{{ $cart->customer->email }}</div>@endif</td>
                                <td class="text-end">{{ $cart->quantity }}</td>
                                <td class="text-end text-nowrap">₹{{ number_format((float) $cart->price, 2) }}</td>
                                <td>@if (!$cart->product)<span class="badge bg-secondary">Unavailable</span>@elseif ((int) $cart->product->status !== 1)<span class="badge bg-secondary">Inactive</span>@elseif ($cart->product->stock < 1)<span class="badge bg-warning text-dark">Out of stock</span>@else<span class="badge bg-success">In stock</span>@endif</td>
                                <td class="text-nowrap">{{ $cart->created_at?->format('d M Y, H:i') ?? '—' }}</td>
                                <td class="text-nowrap">{{ $cart->updated_at?->format('d M Y, H:i') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4">No cart items found{{ $filters ? ' for these filters' : '' }}.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $cart_products->links('pagination::bootstrap-5') }}
            </div></div>
        </div>
    </div>
    <x-footer />
</div>
@endsection
@push('page-scripts')
    <script src="{{ asset('assets/libs/select2/js/select2.min.js') }}"></script>
    <script>$('.select2').select2({ width: '100%' });</script>
@endpush

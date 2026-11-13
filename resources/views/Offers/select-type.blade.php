@extends('layouts.app')

@section('content')
<div class="card p-3">
    <h4>Select Offer Type</h4>
    <ul class="list-group mt-3">
        <li class="list-group-item d-flex align-items-center">
            <i class='bx bx-purchase-tag fs-3 me-3'></i>
            <div class="flex-grow-1">
                <strong>Amount off products</strong><br>
                <small>Discount specific products or categories.</small>
            </div>
            <a href="{{ route('offers.create', ['type' => 'amount_off_products']) }}">
                <i class='bx bx-chevron-right fs-2'></i>
            </a>
        </li>

        <li class="list-group-item d-flex align-items-center">
            <i class='bx bx-gift fs-3 me-3'></i>
            <div class="flex-grow-1">
                <strong>Buy X Get Y</strong><br>
                <small>Discount specific products or collections of products.</small>
            </div>
            <a href="{{ route('offers.create', ['type' => 'buy_x_get_y']) }}">
                <i class='bx bx-chevron-right fs-2'></i>
            </a>
        </li>

        <li class="list-group-item d-flex align-items-center">
            <i class='bx bx-truck fs-3 me-3'></i>
            <div class="flex-grow-1">
                <strong>Free shipping</strong><br>
                <small>Offer free shipping for products or for entire cart.</small>
            </div>
            <a href="{{ route('offers.create', ['type' => 'free_shipping']) }}">
                <i class='bx bx-chevron-right fs-2'></i>
            </a>
        </li>
    </ul>
</div>
@endsection

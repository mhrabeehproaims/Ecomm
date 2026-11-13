<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #fafafa;
            color: #333;
        }

        .invoice-box {
            max-width: 95vw;
            margin: auto;
            background: #fff;
            padding: 20px 25px;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
        }

        /* --- TOP SECTION --- */
        .top-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }


        .company {
            font-size: 22px;
            font-weight: 700;
            color: #222;
        }

        .invoice-title {
            font-size: 30px;
            font-weight: 700;
            color: #222;
            text-align: right;
        }

        .small {
            font-size: 13px;
            color: #777;
            line-height: 1.4;
        }

        /* --- CUSTOMER SECTIONS --- */
        .billing-shipping {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .section {
            flex: 1;
        }

        .section-title {
            font-size: 13px;
            text-transform: uppercase;
            color: #777;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 4px;
        }

        .section-content {
            font-size: 14px;
            color: #333;
            line-height: 1.6;
        }

        /* --- TABLE STYLING --- */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        thead {
            background: #222;
            color: #fff;
        }

        th,
        td {
            padding: 10px;
            font-size: 14px;
        }

        th {
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        td {
            border-bottom: 1px solid #eaeaea;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* --- TOTALS SECTION --- */
        .totals {
            width: 100%;
            margin-top: 20px;
            border-top: 2px solid #222;
            padding-top: 10px;
            text-align: right;
        }

        .totals div {
            display: flex;
            justify-content: flex-end;
            gap: 20px;
            font-size: 14px;
            margin: 4px 0;
        }

        .totals span:first-child {
            min-width: 120px;
            text-align: left;
        }

        .totals span:last-child {
            min-width: 100px;
            text-align: right;
        }

        .grand-total {
            font-weight: 700;
            font-size: 16px;
            border-top: 1px solid #ccc;
            margin-top: 8px;
            padding-top: 8px;
        }

        /* --- FOOTER --- */
        .footer {
            text-align: center;
            margin-top: 50px;
            font-size: 12px;
            color: #999;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .invoice-box {
                box-shadow: none;
                padding: 10px;
            }
        }
    </style>
</head>

@php
    $logo = base64_encode(file_get_contents(public_path('assets/logo/vantara_favicon.png')));
@endphp

<body>
    <div class="invoice-box">
        <div class="top-section">
            <table style="width:100%; border-bottom:2px solid #222; margin-bottom:20px;">
                <tr>
                    <td style="width:60%; vertical-align:top;">
                        <div style="font-size:22px; font-weight:700;">Vanthara
                            <img src="data:image/png;base64,{{ $logo }}" alt="Logo"
                                style="width:22px; height:22px; vertical-align:middle; margin-left:6px;">
                        </div>
                        <div style="font-size:13px; color:#777;">Wayanadan Gandhigram Products</div>
                        <div style="font-size:13px; color:#777;">Phone: +91-98765-43210<br>Email: support@vanthara.in
                        </div>
                    </td>
                    <td style="width:40%; text-align:right; vertical-align:top;">
                        <div style="font-size:30px; font-weight:700;">INVOICE</div>
                        <div style="font-size:13px;">#INV0000001</div>
                        <div style="font-size:13px;">Date: 04 Nov 2025</div>
                    </td>
                </tr>
            </table>
        </div>

        @php
            $address = $invoice->order?->customer?->defaultDeliveryAddress;
            $customer = $invoice->order?->customer;
        @endphp

        <!-- Billed & Shipped side by side -->
        <div class="billing-shipping">
            <table style="width:100%; border-collapse:collapse; margin-bottom:20px;">
                <tr>
                    <!-- Billed To -->
                    <td style="width:50%; vertical-align:top; padding-right:10px;">
                        <div
                            style="font-size:13px; text-transform:uppercase; color:#777; font-weight:600; margin-bottom:5px; border-bottom:1px solid #ddd; padding-bottom:3px;">
                            Billed To
                        </div>
                        <div style="font-size:14px; line-height:1.6; color:#333;">
                            {{ $customer?->first_name ?? 'John' }} {{ $customer?->last_name ?? '' }}<br>
                            {{ $customer?->address ?? '' }}<br>
                            {{ $customer?->city ?? '' }}, {{ $customer?->state ?? '' }}<br>
                            Pin: {{ $customer?->zip ?? '' }}<br>
                            Phone: {{ $customer?->phone ?? '' }}
                        </div>
                    </td>

                    <!-- Shipped To -->
                    <td style="width:50%; vertical-align:top; padding-left:10px;">
                        <div
                            style="font-size:13px; text-transform:uppercase; color:#777; font-weight:600; margin-bottom:5px; border-bottom:1px solid #ddd; padding-bottom:3px;">
                            Shipped To
                        </div>
                        <div style="font-size:14px; line-height:1.6; color:#333;">
                            @if ($address)
                                {{ $address->name ?? $customer?->first_name . ' ' . $customer?->last_name }}<br>
                                @if ($address->address_line1)
                                    {{ $address->address_line1 ?? '' }}<br>
                                @endif
                                @if ($address->address_line2)
                                    {{ $address->address_line2 ?? '' }}<br>
                                @endif
                                @if ($address->city)
                                    {{ $address->city ?? '' }},
                                @endif
                                {{ \Illuminate\Support\Str::headline($address->state ?? '') }}<br>
                                Pin: {{ $address->postal_code ?? '' }}<br>
                                Phone: {{ $address->phone ?? '' }}<br>
                                @if ($address->delivery_instructions)
                                    Delivery Instructions: {{ $address->delivery_instructions }}
                                @endif
                            @else
                                {{ $customer?->first_name ?? '' }} {{ $customer?->last_name ?? '' }}<br>
                                @if ($customer?->address)
                                    {{ $customer?->address ?? '' }}<br>
                                @endif
                                @if ($customer?->city)
                                    {{ $customer?->city ?? '' }},
                                @endif
                                {{ \Illuminate\Support\Str::headline($customer?->state ?? '') }}<br>
                                Pin: {{ $customer?->zip ?? '' }}<br>
                                Phone: {{ $customer?->phone ?? '' }}
                            @endif
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th class="text-center">Qty</th>
                    <th class="text-center">Unit</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">TAX(%)</th>
                    @if (Str::contains(strtolower($invoice->order->customer->state), strtolower('Kerala (KL)')))
                        <th class="text-right">CGST</th>
                        <th class="text-right">SGST</th>
                    @else
                        <th class="text-right">IGST</th>
                    @endif
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            @php
                $total_amount = 0;
                $total_discounted_amount = 0; // Initialize this variable
                $total_tax = 0;
            @endphp
            <tbody>
                @foreach ($invoice->order->items ?? [] as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->product?->product_name }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-center">{{ $item->product?->unit?->unit_name ?? '' }}</td>
                        <td class="text-right">₹{{ number_format($item->actual_price ?? 0, 2) }}</td>
                        <td class="text-right">₹{{ number_format($item->tax_percent ?? 0, 2) }}</td>
                        @if (Str::contains(strtolower($invoice->order->customer->state), strtolower('Kerala (KL)')))
                            <td class="text-right">₹{{ number_format($item->cgst_amount ?? 0, 2) }}</td>
                            <td class="text-right">₹{{ number_format($item->sgst_amount ?? 0, 2) }}</td>
                            @php $item_tax = ($item->cgst_amount ?? 0) + ($item->sgst_amount ?? 0); @endphp
                        @else
                            <td class="text-right">₹{{ number_format($item->igst_amount ?? 0, 2) }}</td>
                            @php $item_tax = $item->igst_amount ?? 0; @endphp
                        @endif
                        <td class="text-right">₹{{ number_format($item->actual_price * $item->quantity ?? 0, 2) }}</td>
                        @php
                            $total_amount += $item->actual_price * $item->quantity;
                            $total_discounted_amount += $item->total;
                            $total_tax += $item_tax;
                        @endphp
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            @php
                $delivery_charge = $invoice->order->delivery_charge ?? 0;
                $coupon_discount = $invoice->order->coupon_discount ?? 0;
                $grand_total = $total_discounted_amount + $delivery_charge;
            @endphp

            <div><span>Subtotal (Before Discount) </span><span>₹{{ number_format($total_amount, 2) }}</span></div>

            @if ($coupon_discount > 0)
                <div><span>Coupon Discount </span><span>- ₹{{ number_format($coupon_discount, 2) }}</span></div>
            @endif

            <div><span>Total Tax </span><span>₹{{ number_format($total_tax, 2) }}</span></div>

            <div style="border-top: 1px solid #ccc; margin-top: 8px; padding-top: 8px;">
                <span>Subtotal (Incl. Tax & Discount) </span>
                <span>₹{{ number_format($total_discounted_amount, 2) }}</span>
            </div>

            @if ($delivery_charge > 0)
                <div><span>Delivery Charge </span><span>₹{{ number_format($delivery_charge, 2) }}</span></div>
            @endif

            <div class="grand-total">
                <span>Grand Total </span>
                <span>₹{{ number_format($grand_total, 2) }}</span>
            </div>
        </div>

        <div class="footer">
            This is a system-generated invoice — no signature required.<br>
            Thank you for choosing <strong>Vanthara</strong>
            <img src="data:image/png;base64,{{ $logo }}" alt="Vanthara Logo"
                style="width:16px; height:16px; vertical-align:middle; margin-left:4px;">
        </div>
    </div>
</body>

</html>
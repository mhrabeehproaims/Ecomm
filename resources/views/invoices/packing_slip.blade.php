@php
    $order = $invoice->order;
    $address = $order?->customer?->defaultDeliveryAddress;
    $customer = $order?->customer;
    
    if ($address) {
        $shipName = $address->name ?? $customer?->first_name . ' ' . $customer?->last_name;
        $shipLine1 = $address->address_line1 ?? '';
        $shipLine2 = $address->address_line2 ?? '';
        $shipCity = $address->city ?? '';
        $shipState = $address->state ?? '';
        $shipPin = $address->postal_code ?? '';
        $shipPhone = $address->phone ?? ($customer?->phone ?? '');
    } else {
        $shipName = ($customer?->first_name ?? '') . ' ' . ($customer?->last_name ?? '');
        $shipLine1 = $customer?->address ?? '';
        $shipLine2 = '';
        $shipCity = $customer?->city ?? '';
        $shipState = $customer?->state ?? '';
        $shipPin = $customer?->zip ?? '';
        $shipPhone = $customer?->phone ?? '';
    }
    
    $orderNumber = 'VAN' . str_pad($invoice->order_id, 7, '0', STR_PAD_LEFT);
    $awbNumber = '500' . str_pad($invoice->order_id, 10, '0', STR_PAD_LEFT);
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: A4;
            margin: 5mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10pt;
            line-height: 1.3;
            color: #000;
            background: #fff;
        }
        
        .shipping-label {
            width: 100%;
            max-width: 200mm;
            margin: 0 auto;
            border: 1px solid #000;
            background: #fff;
        }
        
        .top-section {
            display: flex;
            border-bottom: 1px solid #000;
        }
        
        .ship-to {
            flex: 1;
            padding: 8px 12px;
            border-right: 1px solid #000;
        }
        
        .company-logo {
            width: 120px;
            padding: 8px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            font-weight: bold;
            font-size: 9pt;
            line-height: 1.2;
        }
        
        .ship-to-title {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 6px;
        }
        
        .ship-to-content {
            font-size: 10pt;
            line-height: 1.4;
        }
        
        .barcode-section {
            text-align: center;
            padding: 12px;
            border-bottom: 1px solid #000;
            background: #fff;
        }
        
        .courier-name {
            font-size: 12pt;
            font-weight: bold;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }
        
        .barcode {
            margin: 10px 0;
        }
        
        .barcode-bars {
            font-family: 'Libre Barcode 128', cursive;
            font-size: 60pt;
            letter-spacing: 0;
            line-height: 1;
        }
        
        .barcode-number {
            font-size: 14pt;
            font-weight: bold;
            letter-spacing: 3px;
            margin-top: 5px;
        }
        
        .routing-code {
            font-size: 10pt;
            margin-top: 8px;
        }
        
        .middle-section {
            display: table;
            width: 100%;
            border-bottom: 1px solid #000;
        }
        
        .shipped-by {
            display: table-cell;
            width: 50%;
            padding: 8px 12px;
            border-right: 1px solid #000;
            vertical-align: top;
        }
        
        .order-details {
            display: table-cell;
            width: 50%;
            padding: 8px 12px;
            vertical-align: top;
        }
        
        .section-title {
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 6px;
        }
        
        .shipped-by-content {
            font-size: 9pt;
            line-height: 1.4;
        }
        
        .order-details-content {
            font-size: 9pt;
            line-height: 1.5;
        }
        
        .products-section {
            padding: 8px 12px;
        }
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        
        .products-table th,
        .products-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
            font-size: 9pt;
        }
        
        .products-table th {
            background: #000;
            color: #fff;
            font-weight: bold;
        }
        
        .products-table td:nth-child(2),
        .products-table td:nth-child(3),
        .products-table th:nth-child(2),
        .products-table th:nth-child(3) {
            text-align: center;
            width: 70px;
        }
        
        @media print {
            body {
                margin: 0;
            }
            .shipping-label {
                border: 1px solid #000;
            }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+128&display=swap" rel="stylesheet">
</head>
<body>
    <div class="shipping-label">
        <!-- Top Section -->
        <div class="top-section">
            <div class="ship-to">
                <div class="ship-to-title">Ship To</div>
                <div class="ship-to-content">
                    <strong>{{ $shipName }}</strong><br>
                    @if($shipLine1){{ $shipLine1 }}<br>@endif
                    @if($shipLine2){{ $shipLine2 }}<br>@endif
                    @if($shipCity){{ $shipCity }}, @endif
                    @if($shipState){{ strtoupper(\Illuminate\Support\Str::headline($shipState)) }}, India<br>@endif
                    @if($shipPin){{ $shipPin }}@endif
                </div>
            </div>
        </div>
        
        <!-- Barcode Section -->
        {{-- <div class="barcode-section">
            <div class="courier-name">{{ strtoupper($order->deliveryAgent?->partner_name ?? 'DELHIVERYSURFACE') }}</div>
            <div class="barcode">
                <div class="barcode-bars">*{{ $awbNumber }}*</div>
                <div class="barcode-number">{{ $awbNumber }}</div>
            </div>
            <div class="routing-code">
                Routing Code: {{ strtoupper(substr($shipState ?? '', 0, 3)) }}/CEN | RTO:
            </div>
        </div> --}}
        
        <!-- Middle Section -->
        <div class="middle-section">
            <div class="shipped-by">
                <div class="section-title">Shipped By (If undelivered, return to)</div>
                <div class="shipped-by-content">
                    <strong>VLFCL Gurugram</strong><br>
                    Vanthara Wayanadan Gandhigram Products Pvt Ltd.<br>
                    Wayanad, Kerala, India<br>
                    673577<br>
                    GSTIN: 32XXXXX1234X1ZX<br>
                    Phone: +91-98765-43210
                </div>
            </div>
            <div class="order-details">
                <div class="section-title">Order Details</div>
                <div class="order-details-content">
                    Payment: <strong>{{ strtoupper($order->payment_method ?? 'PREPAID') }}</strong><br>
                    Amount: <strong>{{ number_format($order->total_amount ?? 0, 2) }} INR</strong><br>
                    {{-- AWB No.: <strong>{{ $awbNumber }}</strong><br> --}}
                    Courier: <strong>{{ strtoupper($order->deliveryAgent?->partner_name ?? 'DELHIVERYSURFACE') }}</strong><br>
                    Order Id: <strong>{{ $orderNumber }}</strong><br>
                    Invoice No.: <strong>{{ $invoice->invoice_number ?? 'N/A' }}</strong><br>
                    Invoice Date: <strong>{{ $invoice->created_at?->format('d/m/Y') ?? '' }}</strong>
                </div>
            </div>
        </div>
        
        <!-- Products Section -->
        <div class="products-section">
            <div class="section-title">Product Name & SKU</div>
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Product Name & SKU</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items ?? [] as $item)
                    <tr>
                        <td>{{ $item->product?->product_name ?? 'N/A' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->price ?? 0, 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
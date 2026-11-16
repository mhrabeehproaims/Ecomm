<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Dispatched</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 40px 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .status-badge {
            display: inline-block;
            background-color: #10b981;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            margin-top: 15px;
        }

        .content {
            padding: 40px 30px;
        }

        .greeting {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
        }

        .message {
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .order-summary {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .order-summary h3 {
            color: #333;
            font-size: 18px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }

        .order-table {
            width: 100%;
            border-collapse: collapse;
        }

        .order-table thead {
            background-color: #667eea;
            color: white;
        }

        .order-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .order-table th:last-child {
            text-align: right;
        }

        .order-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #e5e7eb;
            color: #4b5563;
        }

        .order-table td:last-child {
            text-align: right;
            font-weight: 600;
        }

        .product-name {
            font-weight: 500;
            color: #1f2937;
        }

        .total-row {
            background-color: #f3f4f6;
            font-weight: bold;
        }

        .total-row td {
            padding: 15px 12px;
            color: #1f2937;
            font-size: 16px;
            border-bottom: none;
        }

        .tracking-section {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
        }

        .tracking-section h4 {
            margin-bottom: 10px;
            font-size: 16px;
        }

        .tracking-button {
            display: inline-block;
            background-color: white;
            color: #f59e0b;
            padding: 12px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 10px;
        }

        .footer {
            background-color: #1f2937;
            color: #9ca3af;
            padding: 30px;
            text-align: center;
        }

        .footer p {
            margin-bottom: 10px;
            font-size: 14px;
        }

        .footer a {
            color: #667eea;
            text-decoration: none;
        }

        .social-links {
            margin-top: 20px;
        }

        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #667eea;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>🚚 Order Dispatched!</h1>
            <p>Your order is on its way</p>
            <span class="status-badge">✓ Shipped</span>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">Hi {{ $name }},</div>

            <div class="message">
                <p>
                    Great news! Your order has been dispatched and is now on its way to you.
                    @if ($delivery_date) Expected delivery: {{ $delivery_date }}. @endif<br><br>

                    Your invoice is attached. You can also download it after signing in to your Vanthara account.
                </p>
            </div>


            <!-- Tracking Section -->
            {{-- <div class="tracking-section">
                <h4>Track Your Order</h4>
                <p>Stay updated on your delivery status</p>
                <a href="#" class="tracking-button">Track Package →</a>
            </div> --}}

            <!-- Order Summary -->
            <div class="order-summary">
                <h3>Order Summary</h3>
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cartItems as $item)
                            <tr>
                                <td class="product-name">{{ $item->product->product_name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>₹{{ number_format($item->price, 2) }}</td>
                            </tr>
                        @endforeach

                        @if ($delivery_charge > 0)
                            <tr>
                                <td>Delivery Charge</td>
                                <td></td>
                                <td>
                                    ₹{{ number_format($delivery_charge, 2) }}
                                </td>
                            </tr>
                        @endif

                        @if ($discount > 0)
                            <tr><td>Discount</td><td></td><td>-&#8377;{{ number_format($discount, 2) }}</td></tr>
                        @endif

                        <tr class="total-row">
                            <td colspan="2">Total Amount</td>
                            <td>₹{{ number_format($total_amount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="message">
                <p>If you have any questions about your order, feel free to reach out to our customer support team.
                    We're here to help!</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for shopping with us!</p>
            <p>Need help? <a href="#">Contact Support</a></p>
            <div class="social-links">
                <a href="#">Facebook</a> |
                <a href="#">Instagram</a> |
                <a href="#">Twitter</a>
            </div>
            <p style="margin-top: 20px; font-size: 12px;">
                &copy; {{ date('Y') }} Vanthara. All rights reserved.
            </p>
        </div>
    </div>
</body>

</html>

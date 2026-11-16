<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Delivered</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            padding: 20px;
            line-height: 1.6;
        }

        .email-container {
            max-width: 650px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            padding: 50px 30px;
            text-align: center;
            position: relative;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            /* background-color: rgba(255, 255, 255, 0.2); */
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            animation: scaleIn 0.5s ease-out;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }

            to {
                transform: scale(1);
            }
        }

        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .header p {
            font-size: 16px;
            opacity: 0.95;
        }

        .status-badge {
            display: inline-block;
            background-color: rgba(255, 255, 255, 0.3);
            color: white;
            padding: 10px 25px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: bold;
            margin-top: 15px;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }

        .content {
            padding: 40px 30px;
        }

        .greeting {
            font-size: 20px;
            color: #1f2937;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .message {
            color: #6b7280;
            line-height: 1.8;
            margin-bottom: 30px;
            font-size: 15px;
        }

        .delivery-info {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            border-left: 4px solid #10b981;
        }

        .delivery-info h3 {
            color: #1f2937;
            font-size: 18px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #d1d5db;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #6b7280;
            font-size: 14px;
            font-weight: 500;
        }

        .info-value {
            color: #1f2937;
            font-weight: 600;
            font-size: 14px;
        }

        .order-summary {
            background-color: #fafafa;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .order-summary h3 {
            color: #1f2937;
            font-size: 18px;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #10b981;
        }

        .order-table {
            width: 100%;
            border-collapse: collapse;
        }

        .order-table thead {
            background-color: #10b981;
            color: white;
        }

        .order-table th {
            padding: 14px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .order-table th:last-child {
            text-align: right;
        }

        .order-table td {
            padding: 16px 12px;
            border-bottom: 1px solid #e5e7eb;
            color: #4b5563;
            font-size: 14px;
        }

        .order-table td:last-child {
            text-align: right;
            font-weight: 600;
            color: #1f2937;
        }

        .product-name {
            font-weight: 600;
            color: #1f2937;
        }

        .total-row {
            background-color: #f3f4f6;
            font-weight: bold;
        }

        .total-row td {
            padding: 18px 12px;
            color: #1f2937;
            font-size: 16px;
            border-bottom: none;
        }

        .feedback-section {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }

        .feedback-section h4 {
            margin-bottom: 12px;
            font-size: 20px;
        }

        .feedback-section p {
            margin-bottom: 20px;
            opacity: 0.95;
            font-size: 14px;
        }

        .feedback-button {
            display: inline-block;
            background-color: white;
            color: #2563eb;
            padding: 14px 35px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 15px;
            transition: transform 0.2s;
        }

        .feedback-button:hover {
            transform: translateY(-2px);
        }

        .rating-stars {
            font-size: 30px;
            margin-bottom: 15px;
            letter-spacing: 8px;
        }

        .support-section {
            text-align: center;
            padding: 25px;
            background-color: #f9fafb;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .support-section p {
            color: #6b7280;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .support-button {
            display: inline-block;
            background-color: #6b7280;
            color: white;
            padding: 12px 30px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }

        .footer {
            background-color: #1f2937;
            color: #9ca3af;
            padding: 35px 30px;
            text-align: center;
        }

        .footer p {
            margin-bottom: 12px;
            font-size: 14px;
        }

        .footer a {
            color: #10b981;
            text-decoration: none;
            font-weight: 500;
        }

        .social-links {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #374151;
        }

        .social-links a {
            display: inline-block;
            margin: 0 12px;
            color: #10b981;
            text-decoration: none;
            font-size: 14px;
        }

        .thank-you-note {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
            border: 2px solid #fbbf24;
        }

        .thank-you-note p {
            color: #92400e;
            font-size: 15px;
            font-weight: 500;
            margin: 0;
        }

        .thank-you-note .emoji {
            font-size: 24px;
            margin-bottom: 8px;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="success-icon">
                ✓
            </div>
            <h1>Order Delivered!</h1>
            <p>Your package has been successfully delivered</p>
            <span class="status-badge">✓ DELIVERED</span>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">Hi {{ $name }},</div>

            <div class="message">
                <p>Great news! Your order has been successfully delivered. We hope you're excited to use your new
                    products!</p>
            </div>

            <!-- Thank You Note -->
            <div class="thank-you-note">
                <div class="emoji">🎉</div>
                <p>Thank you for choosing us! We hope you love your purchase.</p>
            </div>

            <!-- Delivery Information -->
            <div class="delivery-info">
                <h3>📦 Delivery Details</h3>

                <div class="info-row">
                    <span class="info-label">Order Number:</span>&nbsp;
                    <span
                        class="info-value">#{{ 'VAN' . str_pad($order_number, 7, '0', STR_PAD_LEFT) ?? 'N/A' }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Delivered On:</span>&nbsp;
                    <span class="info-value">{{ $delivered_date ?? 'Not recorded' }}</span>
                </div>
                @if ($delivery_address)
                    <div class="info-row address-block">
                        <span class="info-label">Delivered To:</span>&nbsp;
                        <div class="info-value address">
                            {{ $delivery_address->address_line1 ?? '' }}<br>
                            {{ $delivery_address->address_line2 ?? '' }}<br>
                            {{ $delivery_address->city ?? '' }}, {{ $delivery_address->state ?? '' }}<br>
                            Pin: {{ $delivery_address->postal_code ?? '' }}
                        </div>
                    </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Delivery Partner:</span>&nbsp;
                    <span class="info-value">{{ $delivery_partner ?? 'Express Shipping' }}</span>
                </div>
            </div>

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
                        <tr class="total-row">
                            <td colspan="2">Total Amount Paid</td>
                            <td>₹{{ number_format($total_amount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Feedback Section -->
            {{-- <div class="feedback-section">
                <div class="rating-stars">⭐⭐⭐⭐⭐</div>
                <h4>How was your experience?</h4>
                <p>We'd love to hear your feedback! Your opinion helps us improve.</p>
                <a href="#" class="feedback-button">Rate Your Purchase</a>
            </div> --}}

            <!-- Support Section -->
            <div class="support-section">
                <p>Have any questions or concerns about your order?</p>
                <a href="#" class="support-button">Contact Support</a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for shopping with us! 🛍️</p>
            <p>Need help? <a href="#">Contact Support</a> | <a href="#">Track Orders</a> | <a
                    href="#">Return Policy</a></p>
            <div class="social-links">
                <a href="#">Facebook</a>
                <a href="#">Instagram</a>
                <a href="#">Twitter</a>
                <a href="#">YouTube</a>
            </div>
            <p style="margin-top: 25px; font-size: 12px; color: #6b7280;">
                © 2024 Your Company Name. All rights reserved.<br>
                123 Business Street, City, Country
            </p>
        </div>
    </div>
</body>

</html>

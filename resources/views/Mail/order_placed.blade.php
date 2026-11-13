<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Vantara</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
            padding: 40px 20px;
            line-height: 1.6;
        }

        .email-wrapper {
            max-width: 650px;
            margin: 0 auto;
        }

        .email-container {
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
        }

        .header {
            background: linear-gradient(135deg, #0a6847 0%, #0d8f5f 100%);
            padding: 50px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: pulse 8s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.3; }
        }

        .header-content {
            position: relative;
            z-index: 1;
        }

        .checkmark {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            margin: 0 auto 20px;
            backdrop-filter: blur(10px);
            border: 3px solid rgba(255, 255, 255, 0.3);
            font-size: 48px;
            color: #fff;
            line-height: 80px;
            text-align: center;
        }

        .header h1 {
            color: #fff;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header p {
            color: rgba(255, 255, 255, 0.95);
            font-size: 16px;
            font-weight: 300;
        }

        .content {
            padding: 40px 35px;
        }

        .content h2 {
            color: #0a6847;
            font-size: 24px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .content h2::before {
            content: '📦';
            font-size: 20px;
        }

        .order-info {
            background: #f1f5f9;
            border-radius: 12px;
            padding: 20px;
            margin: 25px 0;
        }

        .order-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            font-size: 14px;
        }

        .order-meta .meta-item {
            margin: 0;
        }

        .meta-label {
            color: #64748b;
            margin-bottom: 5px;
        }

        .meta-value {
            color: #334155;
            font-weight: 600;
        }

        .order-summary {
            margin: 25px 0;
            border-collapse: collapse;
            width: 100%;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .order-summary th,
        .order-summary td {
            padding: 15px 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .order-summary th {
            background-color: #f1f5f9;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        .order-summary td {
            color: #334155;
        }

        .order-summary tbody tr:last-child th,
        .order-summary tbody tr:last-child td {
            border-bottom: none;
            font-weight: 600;
            background-color: #f8fafc;
            color: #0a6847;
        }

        .order-summary .product-name {
            font-weight: 500;
        }

        .notification {
            background: #ecfdf5;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 15px;
            margin: 25px 0;
            text-align: center;
            color: #166534;
        }

        .notification::before {
            content: '🚚';
            font-size: 20px;
            display: block;
            margin-bottom: 5px;
        }

        .notification p {
            margin: 0;
        }

        .cta-section {
            text-align: center;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #0a6847 0%, #1e9a6b 100%);
            color: #fff !important;
            padding: 14px 28px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin: 10px 0;
            transition: transform 0.2s ease;
            box-shadow: 0 4px 12px rgba(10, 104, 71, 0.3);
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(10, 104, 71, 0.4);
            color: #fff !important;
        }

        .btn:visited {
            color: #fff !important;
        }

        .footer {
            background: #f8f9fa;
            text-align: center;
            padding: 30px;
            border-top: 1px solid #e9ecef;
        }

        .footer-content {
            max-width: 500px;
            margin: 0 auto;
        }

        .social-links {
            margin: 20px 0;
        }

        .social-links a {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: #0a6847;
            color: white;
            border-radius: 50%;
            margin: 0 8px;
            text-decoration: none;
            line-height: 40px;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background: #0d8f5f;
            transform: translateY(-3px);
        }

        .footer p {
            color: #6c757d;
            font-size: 13px;
            margin: 10px 0;
        }

        .footer-links {
            margin: 15px 0;
        }

        .footer-links a {
            color: #0a6847;
            text-decoration: none;
            margin: 0 10px;
            font-size: 13px;
            font-weight: 500;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            body {
                padding: 20px 10px;
            }

            .header h1 {
                font-size: 24px;
            }

            .content {
                padding: 30px 20px;
            }

            .order-meta {
                grid-template-columns: 1fr;
            }

            .order-summary th,
            .order-summary td {
                padding: 12px 8px;
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="header">
                <div class="header-content">
                    <div class="checkmark">✓</div>
                    <h1>Order Placed! 🎉</h1>
                    <p>Thank you for shopping with us, {{ $full_name }}</p>
                </div>
            </div>

            <div class="content">
                <h2>Order ID #{{ 'VAN' . str_pad($newOrder->order_id, 7, '0', STR_PAD_LEFT) }}</h2>

                <div class="order-info">
                    <div class="order-meta">
                        <div class="meta-item">
                            <div class="meta-label">Order Placed On: <span class="meta-value">{{ $newOrder->created_at->format('d M Y, h:i A') }}</span></div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Order Status: <span class="meta-value">Confirmed</span></div>
                        </div>
                    </div>
                </div>

                <table class="order-summary">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            {{-- <th>Price</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cartItems as $item)
                            <tr>
                                <td class="product-name">{{ $item->product->product_name }}</td>
                                <td>{{ $item->quantity }}</td>
                                {{-- <td>₹{{ number_format($item->price, 2) }}</td> --}}
                            </tr>
                        @endforeach
                        {{-- <tr>
                            <th colspan="2">Total</th>
                            <th>₹{{ number_format($total_amount, 2) }}</th>
                        </tr> --}}
                    </tbody>
                </table>

                <div class="notification">
                    <p>We'll notify you once your items are shipped. Expect an update soon!</p>
                </div>

                <div class="cta-section">
                    <a href="#" class="btn">View Order Details</a>
                </div>
            </div>

            <div class="footer">
                <div class="footer-content">
                    <div class="social-links">
                        <a href="#">f</a>
                        <a href="#">𝕏</a>
                        <a href="#">in</a>
                        <a href="#">📷</a>
                    </div>
                    <div class="footer-links">
                        <a href="#">Contact Us</a>
                        <a href="#">FAQs</a>
                        <a href="#">Returns</a>
                    </div>
                    <p><strong>Vantara</strong></p>
                    <p>© {{ date('Y') }} Vantara. All rights reserved.</p>
                    <p style="font-size: 11px; margin-top: 15px; color: #adb5bd;">
                        You're receiving this email because you placed an order with us.<br>
                        Need help? Reply to this email or contact our support team.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
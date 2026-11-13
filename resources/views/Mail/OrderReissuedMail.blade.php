<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Reissued</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            background-color: #f6f6f6;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .email-container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .header {
            background-color: #f59e0b;
            color: white;
            padding: 30px 25px;
            text-align: center;
        }

        .header-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }

        .header h1 {
            margin: 0 0 8px 0;
            font-size: 28px;
            font-weight: 600;
        }

        .header p {
            margin: 0;
            font-size: 15px;
            opacity: 0.95;
        }

        .status-badge {
            display: inline-block;
            background-color: rgba(255, 255, 255, 0.25);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-top: 12px;
            border: 2px solid rgba(255, 255, 255, 0.4);
        }

        .content {
            padding: 35px 30px;
        }

        .greeting {
            font-size: 20px;
            color: #1f2937;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .message {
            font-size: 15px;
            line-height: 1.7;
            color: #4b5563;
            margin-bottom: 25px;
        }

        .alert-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 6px;
            padding: 18px;
            margin-bottom: 25px;
        }

        .alert-box h3 {
            color: #92400e;
            font-size: 15px;
            margin: 0 0 8px 0;
            font-weight: 600;
        }

        .alert-box p {
            color: #78350f;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
        }

        .info-section {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #e5e7eb;
        }

        .info-section h3 {
            color: #1f2937;
            font-size: 16px;
            margin: 0 0 15px 0;
            font-weight: 600;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #6b7280;
            font-size: 14px;
        }

        .info-value {
            color: #1f2937;
            font-weight: 600;
            font-size: 14px;
        }

        .items-section {
            background-color: #eff6ff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #bfdbfe;
        }

        .items-section h3 {
            color: #1e40af;
            font-size: 16px;
            margin: 0 0 15px 0;
            font-weight: 600;
        }

        .items-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .items-list li {
            background-color: white;
            padding: 12px 15px;
            margin-bottom: 8px;
            border-radius: 6px;
            color: #1f2937;
            font-size: 14px;
            border-left: 3px solid #3b82f6;
        }

        .items-list li:last-child {
            margin-bottom: 0;
        }

        .reason-section {
            background-color: #fee2e2;
            border-left: 4px solid #ef4444;
            border-radius: 6px;
            padding: 18px;
            margin-bottom: 25px;
        }

        .reason-section h4 {
            color: #991b1b;
            font-size: 15px;
            margin: 0 0 8px 0;
            font-weight: 600;
        }

        .reason-section p {
            color: #7f1d1d;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
        }

        .apology-note {
            background-color: #e0e7ff;
            padding: 18px;
            border-radius: 6px;
            margin-bottom: 25px;
            text-align: center;
            border: 2px solid #a5b4fc;
        }

        .apology-note .emoji {
            font-size: 28px;
            margin-bottom: 8px;
        }

        .apology-note p {
            color: #3730a3;
            font-size: 14px;
            font-weight: 500;
            margin: 0;
        }

        .button {
            display: inline-block;
            background-color: #7c3aed;
            color: #fff !important;
            text-decoration: none;
            padding: 14px 30px;
            margin-top: 15px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            text-align: center;
        }

        .button:hover {
            background-color: #6d28d9;
            color: #fff !important;
        }

        .button:visited {
            color: #fff !important;
        }

        .support-section {
            text-align: center;
            padding: 20px;
            background-color: #f9fafb;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .support-section h4 {
            color: #1f2937;
            margin: 0 0 10px 0;
            font-size: 16px;
            font-weight: 600;
        }

        .support-section p {
            color: #6b7280;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .support-button {
            display: inline-block;
            background-color: #374151;
            color: #fff !important;
            text-decoration: none;
            padding: 10px 25px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
        }

        .support-button:hover {
            background-color: #1f2937;
            color: #fff !important;
        }

        .support-button:visited {
            color: #fff !important;
        }

        .footer {
            background: #f0f0f0;
            text-align: center;
            padding: 20px 15px;
            font-size: 13px;
            color: #777;
        }

        .footer p {
            margin: 5px 0;
        }

        .footer a {
            color: #f59e0b;
            text-decoration: none;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="header-icon">🔄</div>
            <h1>Order Reissued</h1>
            <p>We're sending your items again</p>
            <span class="status-badge">REISSUED</span>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">Hello {{ ucwords($customer_name) }}</div>

            <div class="message">
                <p>
                    We wanted to inform you that your order has been reissued. We understand this may cause some
                    inconvenience, and we sincerely apologize for any disruption.
                </p>
            </div>

            <!-- Apology Note -->
            <div class="apology-note">
                <div class="emoji">🙏</div>
                <p>We apologize for the inconvenience and appreciate your patience and understanding.</p>
            </div>

            <!-- Alert Box -->
            <div class="alert-box">
                <h3>⚠️ What This Means</h3>
                <p>
                    Your original order encountered an issue, so we've created a new shipment with the same items.
                    Your new order will be processed and shipped as soon as possible.
                </p>
            </div>

            <!-- Delivery Information -->
            <div class="info-section">
                <h3>📋 Delivery Information</h3>
                <div class="info-row">
                    <span class="info-label">Customer Name</span>&nbsp;
                    <span class="info-value">{{ ucwords($customer_name) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Delivery Status</span>&nbsp;
                    <span class="info-value">Delivery date will be informed later</span>
                </div>
            </div>

            <!-- Reason Section (Optional) -->
            @if (isset($reissue_reason))
                <div class="reason-section">
                    <h4>Why was this reissued?</h4>
                    <p>{{ $reissue_reason }}</p>
                </div>
            @endif

            <!-- Items Section -->
            <div class="items-section">
                <h3>📦 Reissued Item</h3>
                <ul class="items-list">
                    <li>{{ $item . ' ' . $quantity }}</li>
                </ul>
            </div>

            <!-- Tracking Button -->
            <div style="text-align: center;">
                <a href="#" class="button">Track Your Order</a>
            </div>

            <!-- Support Section -->
            <div class="support-section">
                <h4>Need Assistance?</h4>
                <p>If you have any questions or concerns about your reissued order, we're here to help.</p>
                <a href="#" class="support-button">Contact Support</a>
            </div>

            <div class="message">
                <p>
                    Thank you for your continued trust in us. We're committed to ensuring you receive your order as
                    quickly as possible.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>© {{ date('Y') }} Vanthara. All rights reserved.</p>
            <p>
                <a href="#">Order History</a> |
                <a href="#">Contact Support</a> |
                <a href="#">Return Policy</a>
            </p>
            <p style="margin-top: 12px;">We appreciate your patience and understanding 💛</p>
        </div>
    </div>
</body>

</html>

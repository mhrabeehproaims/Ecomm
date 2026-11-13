<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Request Accepted</title>
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
            background-color: #10b981;
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

        .success-box {
            background-color: #d1fae5;
            border-left: 4px solid #10b981;
            border-radius: 6px;
            padding: 18px;
            margin-bottom: 25px;
        }

        .success-box h3 {
            color: #065f46;
            font-size: 15px;
            margin: 0 0 8px 0;
            font-weight: 600;
        }

        .success-box p {
            color: #047857;
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
            background-color: #fef3c7;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #fde68a;
        }

        .items-section h3 {
            color: #92400e;
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
            border-left: 3px solid #f59e0b;
        }

        .items-list li:last-child {
            margin-bottom: 0;
        }

        .steps-section {
            background-color: #eff6ff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #bfdbfe;
        }

        .steps-section h3 {
            color: #1e40af;
            font-size: 16px;
            margin: 0 0 15px 0;
            font-weight: 600;
        }

        .step {
            background-color: white;
            padding: 12px 15px;
            margin-bottom: 10px;
            border-radius: 6px;
            color: #1f2937;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .step:last-child {
            margin-bottom: 0;
        }

        .step-number {
            background-color: #3b82f6;
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            flex-shrink: 0;
        }

        .thank-you-note {
            background-color: #e0e7ff;
            padding: 18px;
            border-radius: 6px;
            margin-bottom: 25px;
            text-align: center;
            border: 2px solid #a5b4fc;
        }

        .thank-you-note .emoji {
            font-size: 28px;
            margin-bottom: 8px;
        }

        .thank-you-note p {
            color: #3730a3;
            font-size: 14px;
            font-weight: 500;
            margin: 0;
        }

        .button {
            display: inline-block;
            background-color: #10b981;
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
            background-color: #059669;
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
            color: #10b981;
            text-decoration: none;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="header-icon">✓</div>
            <h1>Return Request Accepted</h1>
            <p>Your return has been approved</p>
            <span class="status-badge">ACCEPTED</span>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">Hello {{ ucwords($customer_name) }}</div>

            <div class="message">
                <p>
                    Good news! Your return request has been accepted. We're sorry the product didn't meet your
                    expectations, and we're here to make the return process as smooth as possible.
                </p>
            </div>

            <!-- Success Box -->
            <div class="success-box">
                <h3>✓ Request Approved</h3>
                <p>
                    Your return request has been reviewed and approved. Please follow the next steps below to complete
                    the return process.
                </p>
            </div>

            <!-- Return Information -->
            <div class="info-section">
                <h3>📋 Return Information</h3>
                <div class="info-row">
                    <span class="info-label">Customer Name</span>&nbsp;
                    <span class="info-value">{{ ucwords($customer_name) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Return Status</span>&nbsp;
                    <span class="info-value">Pickup details will be shared soon</span>
                </div>
            </div>

            <!-- Items Section -->
            <div class="items-section">
                <h3>📦 Item to be Returned</h3>
                <ul class="items-list">
                    <li>{{ $item }}</li>
                </ul>
            </div>

            <!-- Next Steps -->
            <div class="steps-section">
                <h3>📝 Next Steps</h3>
                <div class="step">
                    <span class="step-number">1</span>
                    <span>Pack the item securely in its original packaging</span>
                </div>
                <div class="step">
                    <span class="step-number">2</span>
                    <span>Hand over the package to our delivery partner</span>
                </div>
                <div class="step">
                    <span class="step-number">3</span>
                    <span>Exchange/Refund will be processed after quality check</span>
                </div>
            </div>

            <!-- Thank You Note -->
            <div class="thank-you-note">
                <div class="emoji">🙏</div>
                <p>Thank you for your understanding. We appreciate your business!</p>
            </div>

            <!-- Track Button -->
            {{-- <div style="text-align: center;">
                <a href="#" class="button">View Return Status</a>
            </div> --}}

            <!-- Support Section -->
            <div class="support-section">
                <h4>Need Help?</h4>
                <p>If you have any questions about your return, feel free to reach out to us.</p>
                <a href="#" class="support-button">Contact Support</a>
            </div>

            <div class="message">
                <p>
                    We value your feedback and are constantly working to improve our products and services.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>© {{ date('Y') }} Vanthara. All rights reserved.</p>
            <p>
                <a href="#">Return Policy</a> |
                <a href="#">Contact Support</a> |
                <a href="#">FAQs</a>
            </p>
            <p style="margin-top: 12px;">Thank you for shopping with us 💚</p>
        </div>
    </div>
</body>

</html>
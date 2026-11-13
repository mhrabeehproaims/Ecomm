<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>New Sales Enquiry</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            max-width: 650px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #2c3e50;
        }

        p {
            line-height: 1.5;
        }

        .details {
            background-color: #f1f1f1;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }

        .footer {
            font-size: 12px;
            color: #999999;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <p>Hello,</p>
        <p>You have received a new enquiry from a customer. Here are the details:</p>

        <div class="details">
            <p><strong>Name:</strong> {{ $name }}</p>
            @if (isset($email))
                <p><strong>Email:</strong> {{ $email }}</p>
            @endif
            @if (isset($phone))
                <p><strong>Phone:</strong> {{ $phone }}</p>
            @endif
            <p><strong>Message:</strong></p>
            <p>{{ $customer_message }}</p>
        </div>

        <p>Please respond to the customer as soon as possible.</p>

        <div class="footer">
            &copy; {{ date('Y') }} Your Company Name. All rights reserved.
        </div>
    </div>
</body>

</html>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Welcome to Vantara</title>
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
            background-color: #157347;
            color: white;
            padding: 25px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 26px;
        }

        .content {
            padding: 30px;
            text-align: center;
        }

        .content h2 {
            color: #157347;
            margin-bottom: 15px;
        }

        .content p {
            font-size: 16px;
            line-height: 1.6;
        }

        .button {
            display: inline-block;
            background-color: #157347;
            color: #fff !important;
            text-decoration: none;
            padding: 12px 25px;
            margin-top: 20px;
            border-radius: 8px;
            font-weight: 600;
        }

        .button:hover {
            background-color: #0d5f3a;
            color: #fff !important;
        }

        .button:visited {
            color: #fff !important;
        }

        .footer {
            background: #f0f0f0;
            text-align: center;
            padding: 15px;
            font-size: 13px;
            color: #777;
        }
    </style>
</head>

<body>

    <div class="email-container">
        <div class="header">
            <h1>Welcome to Vantara 🌿</h1>
        </div>

        <div class="content">
            <h2>Hello {{ ucwords($full_name) }}</h2>
            <p>
                Thank you for joining <strong>Vantara</strong> — your trusted destination for authentic
                <strong>Wayanadan Gandhigram</strong> products.
            </p>
            <p>
                We're thrilled to have you with us! Explore our collection of natural and handmade products
                crafted with love from Wayanad's beautiful hills.
            </p>

            <a href="#" class="button">Visit Our Store</a>
        </div>

        <div class="footer">
            © {{ date('Y') }} Vantara. All rights reserved.
            <br> Crafted with ❤️ in Wayanad.
        </div>
    </div>

</body>

</html>

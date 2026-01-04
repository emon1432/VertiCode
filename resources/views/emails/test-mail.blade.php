<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $data['subject'] ?? 'Test Email' }}</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .email-container {
            max-width: 600px;
            background: #fff;
            margin: 50px auto;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .email-header {
            background-color: #4f46e5;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .email-body {
            padding: 30px;
            font-size: 16px;
            line-height: 1.6;
            color: #333;
        }

        .email-footer {
            background-color: #f0f0f0;
            padding: 15px;
            text-align: center;
            font-size: 14px;
            color: #888;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="email-header">
            <h2>{{ $data['subject'] ?? 'Test Email' }}</h2>
        </div>
        <div class="email-body">
            <p>{{ $data['body'] ?? 'This is a test email.' }}</p>
        </div>
        <div class="email-footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>

</html>

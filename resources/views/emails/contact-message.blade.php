<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>New Contact Message</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f7f7fb;
            margin: 0;
            padding: 24px;
            color: #1f2937;
        }

        .card {
            max-width: 680px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 18px 24px;
        }

        .body {
            padding: 24px;
        }

        .label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 6px;
        }

        .value {
            font-size: 15px;
            margin-bottom: 18px;
        }

        .message {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 14px;
            white-space: pre-wrap;
            line-height: 1.6;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="header">
            <h2 style="margin: 0;">New Contact Us Submission</h2>
        </div>
        <div class="body">
            <div class="label">Name</div>
            <div class="value">{{ $contactMessage->name }}</div>

            <div class="label">Email</div>
            <div class="value">{{ $contactMessage->email }}</div>

            <div class="label">Subject</div>
            <div class="value">{{ $contactMessage->subject }}</div>

            <div class="label">Message</div>
            <div class="message">{{ $contactMessage->message }}</div>
        </div>
    </div>
</body>

</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background-color: #1a1a2e; padding: 24px 32px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; }
        .body { padding: 32px; color: #333333; }
        .body p { font-size: 14px; line-height: 1.6; }
        .btn-wrapper { text-align: center; margin: 32px 0; }
        .btn { display: inline-block; background-color: #FF6F61; color: #ffffff !important; text-decoration: none; padding: 14px 32px; border-radius: 6px; font-size: 15px; font-weight: bold; }
        .footer { background-color: #f9f9f9; padding: 20px 32px; text-align: center; font-size: 12px; color: #999999; border-top: 1px solid #eeeeee; }
        .footer a { color: #1a1a2e; text-decoration: none; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
        </div>

        <div class="body">
            <p>Hi <strong>{{ $name }}</strong>,</p>
            <p>
                We received a request to reset the password for your {{ config('app.name') }} account
                associated with this email address: <strong>{{ $email }}</strong>.
            </p>
            <p>If you made this request, you can reset your password by clicking the button below:</p>

            <div class="btn-wrapper">
                <a href="{{ $resetUrl }}" class="btn">Reset Password</a>
            </div>

            <p>
                If you didn't request a password reset, please ignore this email.
                Your password will remain unchanged.
            </p>
            <p>
                If you have any questions or need assistance, contact us at
                <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a>.
            </p>
            <p>Thank you for using {{ config('app.name') }}. We are here to help you!</p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
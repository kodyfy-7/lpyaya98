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
        .body p { font-size: 15px; line-height: 1.6; }
        .footer { background-color: #f9f9f9; padding: 20px 32px; text-align: center; font-size: 12px; color: #999999; border-top: 1px solid #eeeeee; }
        .footer a { color: #1a1a2e; text-decoration: none; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Welcome to LP98YAYA</h1>
        </div>

        <div class="body">
            <p>Dear <strong>{{ $name }}</strong>,</p>
            <p>Welcome to <strong>LP98YAYA</strong>!</p>
            <p>
                Your account has been successfully created. To get started,
                please log in and complete your profile.
            </p>

            <p><strong>Need Help?</strong></p>
            <p>
                If you have any questions or need support, reach out to us at
                <a href="mailto:yaya@lagosprovince98.site">yaya@lagosprovince98.site</a>.
            </p>
            <p>
                To ensure you receive our emails, please add
                <strong>yaya@lagosprovince98.site</strong> to your contacts.
            </p>
            <p>
                Best Regards,<br>
                <strong>LP98YAYA Team</strong>
            </p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} LP98YAYA. All rights reserved.
        </div>
    </div>
</body>
</html>
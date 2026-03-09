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
        table { width: 100%; border-collapse: collapse; margin: 24px 0; }
        th { background-color: #1a1a2e; color: #ffffff; padding: 10px 14px; text-align: left; font-size: 13px; }
        td { padding: 10px 14px; border-bottom: 1px solid #eeeeee; font-size: 14px; }
        tr:last-child td { border-bottom: none; }
        .footer { background-color: #f9f9f9; padding: 20px 32px; text-align: center; font-size: 12px; color: #999999; border-top: 1px solid #eeeeee; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>LP98YAYA — New Registration</h1>
        </div>

        <div class="body">
            <p>A new user has registered on <strong>LP98YAYA</strong>!</p>
            <p>Details:</p>

            <table>
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Name</strong></td>
                        <td>{{ $user['name'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Email</strong></td>
                        <td>{{ $user['email'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Registration Date</strong></td>
                        <td>{{ now()->toDateTimeString() }}</td>
                    </tr>
                </tbody>
            </table>

            <p>This is an automated notification. No action is required unless follow-up is needed.</p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} LP98YAYA. All rights reserved.
        </div>
    </div>
</body>
</html>
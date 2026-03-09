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
        .footer a { color: #1a1a2e; text-decoration: none; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>LP98 YAYA</h1>
        </div>

        <div class="body">
            <p>Dear <strong>{{ $name }}</strong>,</p>
            <p>
                Congratulations! Your registration for <strong>{{ $eventDetails['name'] }}</strong>
                has been successfully confirmed.
            </p>

            <table>
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Registration ID</strong></td>
                        <td>{{ $regNumber }}</td>
                    </tr>
                    <tr>
                        <td><strong>Event Name</strong></td>
                        <td>{{ $eventDetails['name'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Date</strong></td>
                        <td>{{ $eventDetails['date'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Time</strong></td>
                        <td>{{ $eventDetails['time'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Location</strong></td>
                        <td>{{ $eventDetails['location'] }}</td>
                    </tr>
                </tbody>
            </table>

            <p><strong>Need Help?</strong></p>
            <p>
                If you have any questions or need assistance, feel free to contact us at
                <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a>.
            </p>
            <p>
                To ensure you receive our emails, please add
                <strong>{{ config('mail.from.address') }}</strong> to your contacts.
            </p>
            <p>
                We look forward to seeing you at the <strong>{{ $eventDetails['name'] }}</strong>
                LP 98 YAYA {{ date('Y') }} Convention!<br><br>
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
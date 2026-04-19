<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Finzo</title>
    <style>
        /* Reset & Base */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f3f6; color: #1a1c1e; padding: 20px 12px; -webkit-text-size-adjust: 100%; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background-color: #002645; color: #ffffff; padding: 30px 24px; text-align: center; }
        .header h1 { margin: 0; font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, sans-serif; font-size: 28px; font-weight: 800; letter-spacing: -0.02em; }
        .content { padding: 40px 30px; }
        .content h2 { color: #002645; margin: 0 0 16px 0; font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, sans-serif; font-size: 24px; font-weight: 700; }
        .content p { margin: 0 0 16px 0; font-size: 15px; line-height: 1.6; color: #1a1c1e; }
        .btn { display: inline-block; background-color: #002645; color: #ffffff; padding: 14px 32px; text-decoration: none; border-radius: 8px; font-weight: 700; font-size: 15px; font-family: 'Inter', -apple-system, sans-serif; text-align: center; }
        .btn:hover { background-color: #003a6b; }
        .btn-wrap { text-align: center; padding: 8px 0 0; }
        .footer { background-color: #f4f3f6; padding: 20px 24px; text-align: center; font-size: 12px; color: #73777f; line-height: 1.5; }

        /* Responsive */
        @media only screen and (max-width: 620px) {
            body { padding: 16px 8px; }
            .content { padding: 28px 20px; }
            .header { padding: 24px 20px; }
            .footer { padding: 16px 20px; }
            .btn { padding: 14px 28px; font-size: 15px; width: 100%; display: block; }
            .content h2 { font-size: 22px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Finzo</h1>
        </div>
        <div class="content">
            <h2>Welcome to Finzo! 🚀</h2>
            <p>Hi there,</p>
            <p>Thank you for registering with Finzo. We are thrilled to have you onboard! To start banking with us and access all our core features, you just need to complete your profile.</p>
            <p style="margin-bottom: 24px;">It takes less than 2 minutes to submit your details and identity documents.</p>
            <div class="btn-wrap">
                <a href="http://localhost:3000/views/user/complete-profile.html" class="btn">Complete My Profile</a>
            </div>
            <p style="margin-top: 28px;">If you have any questions, our support team is always ready to help.</p>
            <p style="margin-bottom: 0;">Best Regards,<br><strong>The Finzo Team</strong></p>
        </div>
        <div class="footer">
            &copy; 2026 Finzo Banking. All rights reserved.<br>
            If you did not create this account, please ignore this email or contact support.
        </div>
    </div>
</body>
</html>

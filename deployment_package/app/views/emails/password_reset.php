
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Request</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #ffc107, #fd7e14); color: #212529; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
        .reset-button { display: inline-block; background: #ffc107; color: #212529; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: bold; margin: 20px 0; border: 2px solid #ffc107; }
        .warning { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔑 Password Reset Request</h1>
            <p>Reset your APS Dream Home account password</p>
        </div>

        <div class="content">
            <h2>Hi Valued Customer!</h2>

            <p>We received a request to reset your password for your APS Dream Home account.</p>

            <div class="warning">
                <p><strong>Security Notice:</strong> This reset link will expire in 24 hours for your security.</p>
            </div>

            <p><strong>Reset your password:</strong></p>
            <p style="text-align: center;">
                <a href="http://localhost/apsdreamhome/reset-password?token=RESET_TOKEN_HERE" class="reset-button">Reset Password Now</a>
            </p>

            <p><strong>Didn't request this reset?</strong></p>
            <p>If you didn't request a password reset, please ignore this email. Your password will remain unchanged.</p>

            <p>For security reasons, this link can only be used once and will expire automatically.</p>

            <p>If you continue to have trouble accessing your account, please contact our support team:</p>
            <p>📧 Email: support@apsdreamhome.com<br>
               📞 Phone: +91-9876543210</p>
        </div>

        <div class="footer">
            <p>This password reset request was made from IP: Unknown</p>
            <p>Request time: 2025-10-20 22:24:36</p>
            <p>If you did not request this reset, your account is still secure.</p>
            <p>&copy; 2025 APS Dream Home. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
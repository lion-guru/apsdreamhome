<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="x-apple-disable-message-reformatting">
<title>KYC Approved</title>
<style>
body,table,td,a{-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%}
table,td{mso-table-lspace:0;mso-table-rspace:0}
img{-ms-interpolation-mode:bicubic;border:0;height:auto;line-height:100%;outline:none;text-decoration:none}
body{margin:0;padding:0;width:100%!important;height:100%!important;font-family:Arial,Helvetica,sans-serif;background-color:#f4f6fb;color:#333}
@media screen and (max-width:600px){
  .container{width:100%!important;max-width:100%!important}
  .px-mobile{padding-left:20px!important;padding-right:20px!important}
  .h1-mobile{font-size:24px!important;line-height:30px!important}
  .button-mobile{width:100%!important;display:block!important;box-sizing:border-box!important}
}
</style>
</head>
<body style="margin:0;padding:0;background-color:#f4f6fb;">
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#f4f6fb">
  <tr>
    <td align="center" style="padding:30px 15px;">
      <table role="presentation" class="container" border="0" cellpadding="0" cellspacing="0" width="600" style="max-width:600px;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
        <!-- Header -->
        <tr>
          <td bgcolor="#10b981" align="center" style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);padding:40px 30px;">
            <div style="width:64px;height:64px;background:rgba(255,255,255,0.2);border-radius:50%;margin:0 auto 16px;line-height:64px;font-size:36px;color:#fff;text-align:center;">&#10003;</div>
            <h1 class="h1-mobile" style="color:#ffffff;margin:0;font-size:28px;font-weight:700;line-height:34px;">KYC Verified Successfully</h1>
            <p style="color:rgba(255,255,255,0.9);margin:8px 0 0;font-size:15px;">Your identity has been verified</p>
          </td>
        </tr>
        <!-- Body -->
        <tr>
          <td class="px-mobile" style="padding:40px 40px 20px;">
            <h2 style="margin:0 0 18px;color:#1a202c;font-size:22px;font-weight:600;">Hi {{user_name}},</h2>
            <p style="margin:0 0 16px;font-size:16px;line-height:24px;color:#4a5568;">
              Great news! Your KYC (Know Your Customer) verification has been <strong style="color:#10b981;">approved</strong>.
            </p>
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;margin:0 0 24px;">
              <tr>
                <td style="padding:16px 18px;font-size:14px;color:#166534;line-height:22px;">
                  <strong>Verification Details:</strong><br>
                  PAN: {{pan_number}}<br>
                  Aadhaar: {{aadhaar_last4}}<br>
                  Verified on: {{verified_date}}
                </td>
              </tr>
            </table>
            <p style="margin:0 0 16px;font-size:16px;line-height:24px;color:#4a5568;">
              You can now proceed with property bookings, payments, and other services without restrictions.
            </p>
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td align="center" style="padding:10px 0 30px;">
                  <a href="{{dashboard_url}}" class="button-mobile" style="display:inline-block;padding:14px 36px;background:#10b981;color:#ffffff;text-decoration:none;border-radius:6px;font-size:16px;font-weight:600;">Go to Dashboard</a>
                </td>
              </tr>
            </table>
            <p style="margin:0;font-size:15px;line-height:22px;color:#4a5568;">
              Best regards,<br><strong>The APS Dream Home Team</strong>
            </p>
          </td>
        </tr>
        <!-- Footer -->
        <tr>
          <td bgcolor="#f7fafc" style="padding:24px 30px;text-align:center;border-top:1px solid #e2e8f0;">
            <p style="margin:0 0 6px;font-size:13px;color:#718096;">APS Dream Home &nbsp;|&nbsp; {{company_phone}} &nbsp;|&nbsp; {{company_email}}</p>
            <p style="margin:10px 0 0;font-size:11px;color:#cbd5e0;">&copy; {{year}} APS Dream Home. All rights reserved.</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>

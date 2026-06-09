<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="x-apple-disable-message-reformatting">
<title>Property Approved</title>
<style>
body,table,td,a{-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%}
table,td{mso-table-lspace:0;mso-table-rspace:0}
img{-ms-interpolation-mode:bicubic;border:0;height:auto;line-height:100%;outline:none;text-decoration:none}
body{margin:0;padding:0;width:100%!important;height:100%!important;font-family:Arial,Helvetica,sans-serif;background-color:#f4f6fb;color:#333}
@media screen and (max-width:600px){
  .container{width:100%!important;max-width:100%!important}
  .px-mobile{padding-left:20px!important;padding-right:20px!important}
  .h1-mobile{font-size:24px!important;line-height:30px!important}
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
            <h1 class="h1-mobile" style="color:#ffffff;margin:0;font-size:28px;font-weight:700;line-height:34px;">Property Approved!</h1>
            <p style="color:rgba(255,255,255,0.9);margin:8px 0 0;font-size:15px;">Your listing is now live</p>
          </td>
        </tr>
        <!-- Body -->
        <tr>
          <td class="px-mobile" style="padding:40px 40px 20px;">
            <h2 style="margin:0 0 18px;color:#1a202c;font-size:22px;font-weight:600;">Hi {{user_name}},</h2>
            <p style="margin:0 0 24px;font-size:16px;line-height:24px;color:#4a5568;">
              Congratulations! Your property has been <strong style="color:#10b981;">approved</strong> by our team and is now visible to thousands of potential buyers.
            </p>
            <!-- Property Card -->
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background:#f7fafc;border:1px solid #e2e8f0;border-radius:8px;margin:0 0 24px;overflow:hidden;">
              <tr>
                <td style="padding:0;">
                  <img src="{{property_image}}" alt="{{property_name}}" width="600" style="display:block;width:100%;height:auto;max-height:240px;object-fit:cover;">
                </td>
              </tr>
              <tr>
                <td style="padding:20px 24px;">
                  <h3 style="margin:0 0 8px;font-size:18px;font-weight:600;color:#1a202c;">{{property_name}}</h3>
                  <p style="margin:0 0 12px;font-size:14px;color:#718096;">&#128205; {{property_location}}</p>
                  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                      <td style="padding:4px 0;font-size:13px;color:#4a5568;">{{property_type}} &middot; {{property_area}} sq.ft.</td>
                      <td align="right" style="padding:4px 0;font-size:18px;color:#10b981;font-weight:700;">&#8377;{{property_price}}</td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
            <!-- What's Next -->
            <h3 style="margin:0 0 12px;font-size:16px;font-weight:600;color:#1a202c;">What happens next?</h3>
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin:0 0 24px;">
              <tr><td style="padding:6px 0;font-size:14px;color:#4a5568;line-height:22px;">&bull; Your listing is now searchable on APS Dream Home</td></tr>
              <tr><td style="padding:6px 0;font-size:14px;color:#4a5568;line-height:22px;">&bull; Interested buyers can contact you directly</td></tr>
              <tr><td style="padding:6px 0;font-size:14px;color:#4a5568;line-height:22px;">&bull; You'll receive email notifications for new inquiries</td></tr>
              <tr><td style="padding:6px 0;font-size:14px;color:#4a5568;line-height:22px;">&bull; Track views, inquiries and leads from your dashboard</td></tr>
            </table>
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td align="center" style="padding:16px 0 30px;">
                  <a href="{{property_url}}" style="display:inline-block;padding:14px 36px;background:#10b981;color:#ffffff;text-decoration:none;border-radius:6px;font-size:16px;font-weight:600;">View Your Listing</a>
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
            <p style="margin:6px 0 0;font-size:12px;color:#a0aec0;">
              <a href="{{unsubscribe_url}}" style="color:#718096;">Unsubscribe</a>
            </p>
            <p style="margin:10px 0 0;font-size:11px;color:#cbd5e0;">&copy; {{year}} APS Dream Home. All rights reserved.</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>

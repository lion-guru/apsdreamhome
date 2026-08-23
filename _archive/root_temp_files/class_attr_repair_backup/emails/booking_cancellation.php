<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="x-apple-disable-message-reformatting">
<title>Booking Cancelled</title>
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
<body class="style-51511">
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#f4f6fb">
  <tr>
    <td align="center" class="style-56039">
      <table role="presentation" class="container" border="0" cellpadding="0" cellspacing="0" width="600" class="style-99000">
        <!-- Header -->
        <tr>
          <td bgcolor="#ef4444" align="center" class="style-59428">
            <div class="style-90702">&#10060;</div>
            <h1 class="h1-mobile" class="style-56865">Booking Cancelled</h1>
            <p class="style-9233">Your booking has been cancelled</p>
          </td>
        </tr>
        <!-- Body -->
        <tr>
          <td class="px-mobile" class="style-68782">
            <h2 class="style-80102">Hi {{user_name}},</h2>
            <p class="style-28596">
              Your booking has been cancelled as per your request.
            </p>
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="style-87013">
              <tr>
                <td class="style-6557">
                  <strong>Cancellation Details:</strong><br>
                  Booking: {{booking_number}}<br>
                  Plot: {{plot_number}}, {{colony_name}}<br>
                  Reason: {{cancellation_reason}}<br><br>
                  <strong>Refund Amount:</strong> &#8377;{{refund_amount}}<br>
                  <strong>Cancellation Charge:</strong> &#8377;{{cancellation_charge}}<br>
                  <strong>Refund Method:</strong> {{refund_method}}
                </td>
              </tr>
            </table>
            <p class="style-28596">
              Your refund will be processed within <strong>7-10 business days</strong> to your original payment method.
            </p>
            <p class="style-28596">
              If you have any questions about the cancellation or refund, please contact our support team.
            </p>
            <p class="style-25661">
              Best regards,<br><strong>The APS Dream Home Team</strong>
            </p>
          </td>
        </tr>
        <!-- Footer -->
        <tr>
          <td bgcolor="#f7fafc" class="style-89423">
            <p class="style-40082">APS Dream Home &nbsp;|&nbsp; {{company_phone}} &nbsp;|&nbsp; {{company_email}}</p>
            <p class="style-75003">&copy; {{year}} APS Dream Home. All rights reserved.</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>

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
<body >
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#f4f6fb">
  <tr>
    <td align="center" >
      <table role="presentation" class="container" border="0" cellpadding="0" cellspacing="0" width="600" >
        <!-- Header -->
        <tr>
          <td bgcolor="#ef4444" align="center" >
            <div >&#10060;</div>
            <h1 class="h1-mobile">Booking Cancelled</h1>
            <p >Your booking has been cancelled</p>
          </td>
        </tr>
        <!-- Body -->
        <tr>
          <td class="px-mobile">
            <h2 >Hi {{user_name}},</h2>
            <p >
              Your booking has been cancelled as per your request.
            </p>
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" >
              <tr>
                <td >
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
            <p >
              Your refund will be processed within <strong>7-10 business days</strong> to your original payment method.
            </p>
            <p >
              If you have any questions about the cancellation or refund, please contact our support team.
            </p>
            <p >
              Best regards,<br><strong>The APS Dream Home Team</strong>
            </p>
          </td>
        </tr>
        <!-- Footer -->
        <tr>
          <td bgcolor="#f7fafc" >
            <p >APS Dream Home &nbsp;|&nbsp; {{company_phone}} &nbsp;|&nbsp; {{company_email}}</p>
            <p >&copy; {{year}} APS Dream Home. All rights reserved.</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="x-apple-disable-message-reformatting">
<title>EMI Payment Reminder</title>
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
<body >
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#f4f6fb">
  <tr>
    <td align="center" >
      <table role="presentation" class="container" border="0" cellpadding="0" cellspacing="0" width="600" >
        <!-- Header -->
        <tr>
          <td bgcolor="#f59e0b" align="center" >
            <div >&#128179;</div>
            <h1 class="h1-mobile">EMI Payment Reminder</h1>
            <p >Your upcoming installment is due soon</p>
          </td>
        </tr>
        <!-- Body -->
        <tr>
          <td class="px-mobile">
            <h2 >Hi {{user_name}},</h2>
            <p >
              This is a friendly reminder that your next EMI installment is due in <strong >{{days_until_due}} days</strong>.
            </p>
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" >
              <tr>
                <td >
                  <strong >Installment #{{installment_no}}</strong><br><br>
                  <strong>Amount:</strong> &#8377;{{emi_amount}}<br>
                  <strong>Due Date:</strong> {{due_date}}<br>
                  <strong>Booking:</strong> {{booking_number}}<br>
                  <strong>Plot:</strong> {{plot_number}}, {{colony_name}}
                </td>
              </tr>
            </table>
            <p >
              Pay now to avoid late fees. You can pay online via UPI, Card, Netbanking, or Wallet.
            </p>
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td align="center" >
                  <a href="{{pay_url}}" class="button-mobile">Pay Now</a>
                </td>
              </tr>
            </table>
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

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="x-apple-disable-message-reformatting">
<title>Referral Commission Credited</title>
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
<body class="style-51511">
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#f4f6fb">
  <tr>
    <td align="center" class="style-56039">
      <table role="presentation" class="container" border="0" cellpadding="0" cellspacing="0" width="600" class="style-99000">
        <!-- Header -->
        <tr>
          <td bgcolor="#10b981" align="center" class="style-87574">
            <div class="style-90702">&#127873;</div>
            <h1 class="h1-mobile style-56865">Referral Commission!</h1>
            <p class="style-9233">You've earned a referral reward</p>
          </td>
        </tr>
        <!-- Body -->
        <tr>
          <td class="px-mobile style-68782">
            <h2 class="style-80102">Hi {{user_name}},</h2>
            <p class="style-28596">
              Great news! Your referred friend <strong>{{referred_name}}</strong> has made a booking, and you've earned a commission!
            </p>
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="style-77691">
              <tr>
                <td class="style-65704">
                  <strong class="style-92359">Commission Earned: &#8377;{{commission_amount}}</strong><br><br>
                  <strong>Referred User:</strong> {{referred_name}}<br>
                  <strong>Booking:</strong> {{booking_number}}<br>
                  <strong>Plot:</strong> {{plot_number}}, {{colony_name}}<br>
                  <strong>Booking Value:</strong> &#8377;{{booking_value}}<br>
                  <strong>Commission Rate:</strong> {{commission_rate}}%<br>
                  <strong>Credited On:</strong> {{credited_date}}
                </td>
              </tr>
            </table>
            <p class="style-28596">
              The commission has been credited to your wallet. You can use it towards future payments or request a withdrawal.
            </p>
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td align="center" class="style-43583">
                  <a href="{{referral_url}}" class="button-mobile style-59532">View Referral Dashboard</a>
                </td>
              </tr>
            </table>
            <p class="style-3736">
              Keep referring friends and earn more! Share your referral code: <strong class="style-54781">{{referral_code}}</strong>
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

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="x-apple-disable-message-reformatting">
<title>Booking Confirmed - APS Dream Home</title>
<style>
body,table,td,a{-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%}
table,td{mso-table-lspace:0;mso-table-rspace:0}
img{-ms-interpolation-mode:bicubic;border:0;height:auto;line-height:100%;outline:none;text-decoration:none}
body{margin:0;padding:0;width:100%!important;height:100%!important;font-family:Arial,Helvetica,sans-serif;background-color:#f4f6fb;color:#333}
@media screen and (max-width:600px){
  .container{width:100%!important;max-width:100%!important}
  .px-mobile{padding-left:20px!important;padding-right:20px!important}
  .h1-mobile{font-size:24px!important;line-height:30px!important}
  .stack-mobile{display:block!important;width:100%!important}
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
            <div class="style-90702">&#10003;</div>
            <h1 class="h1-mobile" class="style-56865">Booking Confirmed!</h1>
            <p class="style-9233">Your property is reserved</p>
          </td>
        </tr>
        <!-- Body -->
        <tr>
          <td class="px-mobile" class="style-68782">
            <p class="style-31228">Hi <strong>{{customer_name}}</strong>,</p>
            <p class="style-118">
              Great news! Your booking has been <strong class="style-54781">confirmed</strong>. Below are the details of your reservation.
            </p>
            <!-- Booking Details Card -->
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="style-1712">
              <tr>
                <td class="style-41466">
                  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                      <td class="style-29531">Booking ID</td>
                      <td align="right" class="style-80037">{{booking_id}}</td>
                    </tr>
                    <tr>
                      <td colspan="2" class="style-97767"><div class="style-11642"></div></td>
                    </tr>
                    <tr>
                      <td class="style-29531">Property</td>
                      <td align="right" class="style-80037">{{property_name}}</td>
                    </tr>
                    <tr>
                      <td colspan="2" class="style-97767"><div class="style-11642"></div></td>
                    </tr>
                    <tr>
                      <td class="style-29531">Location</td>
                      <td align="right" class="style-79385">{{property_location}}</td>
                    </tr>
                    <tr>
                      <td colspan="2" class="style-97767"><div class="style-11642"></div></td>
                    </tr>
                    <tr>
                      <td class="style-29531">Booking Date</td>
                      <td align="right" class="style-79385">{{booking_date}}</td>
                    </tr>
                    <tr>
                      <td colspan="2" class="style-97767"><div class="style-11642"></div></td>
                    </tr>
                    <tr>
                      <td class="style-13332">Total Amount</td>
                      <td align="right" class="style-90391">&#8377;{{amount}}</td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
            <p class="style-3736">
              Our team will contact you within 24 hours to guide you through the next steps. Please keep your booking ID handy for any future reference.
            </p>
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td align="center" class="style-27587">
                  <a href="{{booking_url}}" class="style-59532">View Booking Details</a>
                </td>
              </tr>
            </table>
            <p class="style-25661">
              Best regards,<br><strong>The APS Dream Home Team</strong>
            </p>
          </td>
        </tr>
        <!-- Footer -->
        <tr>
          <td bgcolor="#f7fafc" class="style-89423">
            <p class="style-40082">APS Dream Home &nbsp;|&nbsp; {{company_phone}} &nbsp;|&nbsp; {{company_email}}</p>
            <p class="style-31566">
              <a href="{{unsubscribe_url}}" class="style-66032">Unsubscribe</a>
            </p>
            <p class="style-75003">&copy; {{year}} APS Dream Home. All rights reserved.</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>

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
<body >
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#f4f6fb">
  <tr>
    <td align="center" >
      <table role="presentation" class="container" border="0" cellpadding="0" cellspacing="0" width="600" >
        <!-- Header -->
        <tr>
          <td bgcolor="#10b981" align="center" >
            <div >&#10003;</div>
            <h1 class="h1-mobile">Booking Confirmed!</h1>
            <p >Your property is reserved</p>
          </td>
        </tr>
        <!-- Body -->
        <tr>
          <td class="px-mobile">
            <p >Hi <strong>{{customer_name}}</strong>,</p>
            <p >
              Great news! Your booking has been <strong >confirmed</strong>. Below are the details of your reservation.
            </p>
            <!-- Booking Details Card -->
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" >
              <tr>
                <td >
                  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                      <td >Booking ID</td>
                      <td align="right" >{{booking_id}}</td>
                    </tr>
                    <tr>
                      <td colspan="2" ><div ></div></td>
                    </tr>
                    <tr>
                      <td >Property</td>
                      <td align="right" >{{property_name}}</td>
                    </tr>
                    <tr>
                      <td colspan="2" ><div ></div></td>
                    </tr>
                    <tr>
                      <td >Location</td>
                      <td align="right" >{{property_location}}</td>
                    </tr>
                    <tr>
                      <td colspan="2" ><div ></div></td>
                    </tr>
                    <tr>
                      <td >Booking Date</td>
                      <td align="right" >{{booking_date}}</td>
                    </tr>
                    <tr>
                      <td colspan="2" ><div ></div></td>
                    </tr>
                    <tr>
                      <td >Total Amount</td>
                      <td align="right" >&#8377;{{amount}}</td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
            <p >
              Our team will contact you within 24 hours to guide you through the next steps. Please keep your booking ID handy for any future reference.
            </p>
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td align="center" >
                  <a href="{{booking_url}}" >View Booking Details</a>
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
            <p >
              <a href="{{unsubscribe_url}}" >Unsubscribe</a>
            </p>
            <p >&copy; {{year}} APS Dream Home. All rights reserved.</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>

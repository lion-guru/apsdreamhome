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
<body class="style-51511">
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#f4f6fb">
  <tr>
    <td align="center" class="style-56039">
      <table role="presentation" class="container" border="0" cellpadding="0" cellspacing="0" width="600" class="style-99000">
        <!-- Header -->
        <tr>
          <td bgcolor="#10b981" align="center" class="style-87574">
            <div class="style-90702">&#10003;</div>
            <h1 class="h1-mobile" class="style-56865">Property Approved!</h1>
            <p class="style-9233">Your listing is now live</p>
          </td>
        </tr>
        <!-- Body -->
        <tr>
          <td class="px-mobile" class="style-68782">
            <h2 class="style-80102">Hi {{user_name}},</h2>
            <p class="style-118">
              Congratulations! Your property has been <strong class="style-54781">approved</strong> by our team and is now visible to thousands of potential buyers.
            </p>
            <!-- Property Card -->
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="style-53154">
              <tr>
                <td class="style-97767">
                  <img src="{{property_image}}" alt="{{property_name}}" width="600" class="style-56447">
                </td>
              </tr>
              <tr>
                <td class="style-55697">
                  <h3 class="style-59170">{{property_name}}</h3>
                  <p class="style-65386">&#128205; {{property_location}}</p>
                  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                      <td class="style-91260">{{property_type}} &middot; {{property_area}} sq.ft.</td>
                      <td align="right" class="style-9527">&#8377;{{property_price}}</td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
            <!-- What's Next -->
            <h3 class="style-90574">What happens next?</h3>
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="style-95815">
              <tr><td class="style-87698">&bull; Your listing is now searchable on APS Dream Home</td></tr>
              <tr><td class="style-87698">&bull; Interested buyers can contact you directly</td></tr>
              <tr><td class="style-87698">&bull; You'll receive email notifications for new inquiries</td></tr>
              <tr><td class="style-87698">&bull; Track views, inquiries and leads from your dashboard</td></tr>
            </table>
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td align="center" class="style-27587">
                  <a href="{{property_url}}" class="style-59532">View Your Listing</a>
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

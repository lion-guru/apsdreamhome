<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="x-apple-disable-message-reformatting">
<title>Reset Your Password</title>
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
          <td bgcolor="#ef4444" align="center" class="style-59428">
            <div class="style-90702">&#128274;</div>
            <h1 class="h1-mobile style-56865">Password Reset Request</h1>
            <p class="style-9233">Secure your account</p>
          </td>
        </tr>
        <!-- Body -->
        <tr>
          <td class="px-mobile style-68782">
            <h2 class="style-80102">Hi {{user_name}},</h2>
            <p class="style-28596">
              We received a request to reset the password for your APS Dream Home account. Click the button below to choose a new password.
            </p>
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td align="center" class="style-14543">
                  <a href="{{reset_url}}" class="button-mobile style-72281">Reset My Password</a>
                </td>
              </tr>
            </table>
            <!-- Expiry Notice -->
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="style-80870">
              <tr>
                <td class="style-86588">
                  <strong>&#9888;&#65039; This link expires in {{expires_in}}.</strong> If you don't reset your password before then, you'll need to request a new link.
                </td>
              </tr>
            </table>
            <p class="style-3736">
              If the button above doesn't work, copy and paste this URL into your browser:
            </p>
            <p class="style-49428">
              {{reset_url}}
            </p>
            <!-- Security Notice -->
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="style-42991">
              <tr>
                <td class="style-30011">
                  <strong class="style-52648">Didn't request this?</strong><br>
                  If you didn't make this request, you can safely ignore this email. Your password will remain unchanged. For security concerns, please contact our support team.
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
            <p class="style-75003">&copy; {{year}} APS Dream Home. All rights reserved.</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>

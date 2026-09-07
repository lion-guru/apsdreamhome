<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="x-apple-disable-message-reformatting">
<title>New Reply on Your Ticket</title>
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
          <td bgcolor="#6366f1" align="center" >
            <div >&#9993;</div>
            <h1 class="h1-mobile">New Reply on Ticket</h1>
            <p >{{agent_name}} replied</p>
          </td>
        </tr>
        <!-- Body -->
        <tr>
          <td class="px-mobile">
            <h2 >Hi {{user_name}},</h2>
            <p >
              <strong>{{agent_name}}</strong> has replied to your support ticket.
            </p>
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" >
              <tr>
                <td >
                  <strong >Ticket #{{ticket_id}} — {{subject}}</strong><br><br>
                  <div >
                    {{reply_message}}
                  </div>
                  <br>
                  <span >Replied on {{reply_date}}</span>
                </td>
              </tr>
            </table>
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td align="center" >
                  <a href="{{ticket_url}}" class="button-mobile">View & Reply</a>
                </td>
              </tr>
            </table>
            <p >
              Best regards,<br><strong>The APS Dream Home Support Team</strong>
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

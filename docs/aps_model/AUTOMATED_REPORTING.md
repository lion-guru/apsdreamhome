# APS AI Analytics – Automated Reporting Guide

This guide explains how to set up, configure, and maintain automated analytics reporting for the APS Dream Homes AI system.

---

## 1. Configure Admin Emails & SMTP
- Edit `admin/send_ai_analytics_report.php`:
  - Update the `$admin_emails` array with the real admin email addresses.
  - Set the SMTP host and sender details as needed (see `$mail->Host`, `$mail->setFrom`).
- Make sure your SMTP server allows sending from the web server (localhost or your mail relay).

## 2. Manual Report Sending
- Run the script in your browser or via command line:
  - Browser: `http://yourdomain.com/admin/send_ai_analytics_report.php`
  - Command line: `php c:/xampp/htdocs/march2025apssite/admin/send_ai_analytics_report.php`
- The script will generate a CSV of the last 7 days and email it to all configured admins.

## 3. Scheduling Automated Reports
- **Windows Task Scheduler:**
  1. Open Task Scheduler > Create Task
  2. Action: `Start a program`
  3. Program/script: `php`
  4. Add arguments: `c:/xampp/htdocs/march2025apssite/admin/send_ai_analytics_report.php`
  5. Set your preferred schedule (e.g., weekly, every Monday at 8am)
- **Linux (cron):**
  - Add to crontab: `0 8 * * 1 php /path/to/send_ai_analytics_report.php`

## 4. Troubleshooting
- If emails are not sent:
  - Check SMTP settings and server logs.
  - Ensure firewall/antivirus is not blocking outbound mail.
  - Make sure PHPMailer is installed and included.
- If the script fails:
  - Check PHP error logs and permissions.
  - Test manual run before scheduling.

## 5. Best Practices
- Do not hardcode sensitive SMTP credentials in public repos.
- Rotate admin emails and SMTP passwords regularly.
- Monitor your scheduled task for failures and delivery issues.
- Document/report any changes to the script or schedule for your team.

---

For further help, see other docs in `aps_model/` or contact your devops/admin team.

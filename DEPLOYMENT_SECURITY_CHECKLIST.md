# Deployment Security Checklist for APS Dream Homes MLM System

This checklist will help you ensure your PHP-based MLM system is secure and production-ready before going live.

---

## 1. **HTTPS Everywhere**
- [ ] Obtain and install a valid SSL certificate for your domain.
- [ ] Force HTTPS in `.htaccess` or server config.
- [ ] Update all internal links and resources to use `https://`.

## 2. **Session and Cookie Security**
- [ ] Set session cookies to `secure`, `httponly`, and `SameSite=Strict` in PHP:
  ```php
  session_set_cookie_params([
      'secure' => true,
      'httponly' => true,
      'samesite' => 'Strict',
  ]);
  ```
- [ ] Regenerate session IDs after login.
- [ ] Destroy sessions on logout.

## 3. **Environment Variables & Configuration**
- [ ] Move all sensitive credentials (DB, SMTP, API keys) to environment variables or a config file **outside webroot**.
- [ ] Never commit real credentials to version control.
- [ ] Use different credentials for development and production.

## 4. **Error Handling**
- [ ] Disable error display on production (`display_errors = Off` in `php.ini`).
- [ ] Log errors to a file outside webroot.
- [ ] Never show sensitive error messages to users.

## 5. **Input Validation & Output Escaping**
- [ ] Validate and sanitize all user input (server-side).
- [ ] Escape all output in HTML, JS, and SQL contexts.
- [ ] Use prepared statements for all SQL queries.

## 6. **CSRF, CAPTCHA, and Rate Limiting**
- [ ] All forms (login, register, password reset, etc.) have CSRF protection.
- [ ] CAPTCHA is enabled on all sensitive forms.
- [ ] Rate limiting is enforced on login, registration, and password reset.

## 7. **Password Security**
- [ ] All passwords are hashed using `password_hash()`.
- [ ] Password reset tokens are single-use and expire after a short time.
- [ ] Password reset links cannot be reused.

## 8. **Backups & Monitoring**
- [ ] Take a full backup of code and database before launch.
- [ ] Set up automated regular backups.
- [ ] Enable server and application logging/monitoring.

## 9. **Final Manual Testing**
- [ ] Test all user and admin flows (login, register, reset, etc.)
- [ ] Try invalid CSRF, CAPTCHA, and rate limiting scenarios.
- [ ] Test for XSS, SQL injection, and file upload vulnerabilities.
- [ ] Test dynamic header/footer and branding.

## 10. **Other Recommendations**
- [ ] Remove unused files and scripts from the server.
- [ ] Use strong admin passwords and enable 2FA if possible.
- [ ] Review third-party libraries for vulnerabilities.
- [ ] Keep PHP and all dependencies up to date.

---

**Ready to go live? Double-check every box! If you need help with any item, ask your developer/security expert.**

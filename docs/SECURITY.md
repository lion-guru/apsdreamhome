# APS Dream Home - Security Guide

> **Last updated:** 2026-06-05
> **Scope:** Application, infrastructure, and operational security practices for the APS Dream Home platform.

This document describes the security controls, hardening measures, and operational practices in place to protect the APS Dream Home platform and its users. It covers application-level security (CSRF, XSS, SQL injection), infrastructure hardening (TLS, network), and processes (secret management, incident response).

---

## 1. Security Headers

We apply a defense-in-depth set of HTTP security headers via `.htaccess` and `docker/nginx/conf.d/app.conf`. Every response from the application includes:

| Header | Value | Purpose |
|--------|-------|---------|
| `Strict-Transport-Security` | `max-age=63072000; includeSubDomains; preload` | Force HTTPS for 2 years; eligible for browser preload list |
| `X-Frame-Options` | `SAMEORIGIN` | Prevent clickjacking (no iframes from other domains) |
| `X-Content-Type-Options` | `nosniff` | Block MIME-type sniffing |
| `X-XSS-Protection` | `1; mode=block` | Enable browser XSS filter (legacy IE/Edge) |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | Limit referrer leakage |
| `Content-Security-Policy` | `default-src 'self'; ...` | Whitelist allowed script/style/image sources |
| `Permissions-Policy` | `geolocation=(), microphone=(), camera=(), payment=()` | Disable unused browser APIs |
| `X-Permitted-Cross-Domain-Policies` | `none` | Disable Adobe Flash / PDF cross-domain access |

### Verifying

```bash
curl -I https://apsdreamhome.com/ | grep -iE "strict|x-frame|x-content|content-security|referrer|permissions"
```

### Reporting Missing Headers

Open an issue or PR against `.htaccess` and `docker/nginx/conf.d/app.conf`.

---

## 2. CSRF Protection

All state-changing requests (POST, PUT, PATCH, DELETE) must include a valid CSRF token.

### 2.1 Token Generation

Tokens are generated per-session in `app/Core/Security/CSRF.php`:
- 32 random bytes from `random_bytes(16)`
- Stored in `$_SESSION['csrf_token']` with a 4-hour TTL
- HMAC-SHA256 signed with `APP_KEY` to prevent tampering

### 2.2 Token Verification

Every form includes a hidden field:

```html
<input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
```

For AJAX requests, the token is passed via the `X-CSRF-Token` header (set automatically by `assets/admin/js/admin.js`).

### 2.3 Same-Site Cookies

Session cookies use `SameSite=Lax` to prevent CSRF on top-level navigations.

### 2.4 Per-Form Tokens (Sensitive Forms)

For login, password reset, and 2FA forms, we generate a per-form token with a 10-minute TTL — stolen tokens expire quickly.

---

## 3. SQL Injection Prevention

We use **PDO prepared statements exclusively** for all database access. User input is NEVER concatenated into SQL strings.

### 3.1 Code Pattern (Safe)

```php
$stmt = $db->prepare("SELECT * FROM users WHERE email = :email AND status = :status LIMIT 1");
$stmt->execute([
    'email'  => $email,
    'status' => 'active',
]);
$user = $stmt->fetch();
```

### 3.2 Anti-Pattern (Never Do This)

```php
// DANGEROUS - never do this
$query = "SELECT * FROM users WHERE email = '$email'";
$result = $db->query($query);
```

### 3.3 Audit Tools

- **Pre-commit hook** scans for `->query(`, `mysqli_query(`, and concatenation patterns in `app/`
- **Code review** checklist requires approval from a second developer for raw SQL
- **Monthly** `composer audit` and `npm audit` runs

### 3.4 Dangerous Functions (Banned)

The following functions are banned by code review:
- `mysql_query()`, `mysql_real_escape_string()` (use PDO instead)
- `mysqli_query()` with string concatenation
- `unserialize()` on user input (use `json_decode` instead)
- `eval()`, `assert()`, `create_function()`

---

## 4. XSS Prevention

We follow a strict **output encoding** policy: data is rendered with the right encoder for the context (HTML, attribute, JavaScript, URL).

### 4.1 The `e()` Helper

All view files use the `e()` helper (alias for `htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`):

```php
<!-- Safe -->
<h1><?= e($user->name) ?></h1>
<input value="<?= e($user->email) ?>">

<!-- DANGEROUS - never do this -->
<h1><?= $user->name ?></h1>
```

### 4.2 Rich Text

For user-generated HTML (blog posts, property descriptions, reviews), we use HTMLPurifier with a strict whitelist:

```php
$cleanHtml = HTMLPurifier::purify($userInput, [
    'p', 'br', 'strong', 'em', 'ul', 'ol', 'li', 'a'
]);
```

### 4.3 Content Security Policy

Our CSP header whitelists only known-good sources:

```
default-src 'self';
script-src 'self' cdn.jsdelivr.net 'nonce-{random}';
style-src  'self' cdn.jsdelivr.net fonts.googleapis.com 'unsafe-inline';
img-src    'self' data: https:;
font-src   'self' fonts.gstatic.com;
connect-src 'self' wss://apsdreamhome.com;
frame-ancestors 'none';
object-src 'none';
base-uri 'self';
```

### 4.4 URL Validation

User-submitted URLs (in profiles, blog comments) are validated against an allowlist of schemes:

```php
if (!preg_match('#^https?://[a-z0-9.-]+#i', $url)) {
    throw new InvalidArgumentException('Invalid URL');
}
```

---

## 5. Session Security

### 5.1 Cookie Flags

Session cookies are set with these flags in `php.ini` and `.htaccess`:

```
session.cookie_httponly = 1     # no JavaScript access (defense vs XSS)
session.cookie_secure   = 1     # HTTPS only in production
session.cookie_samesite = Lax   # CSRF protection
session.use_strict_mode = 1     # reject uninitialized session IDs
```

### 5.2 Session ID Rotation

We rotate the session ID on:
- Login (`session_regenerate_id(true)`)
- Privilege escalation (e.g., 2FA verify)
- Logout
- Every 30 minutes (configurable)

This prevents **session fixation** attacks.

### 5.3 Session Storage

Sessions are stored in **Redis** (not the filesystem) so:
- They survive across multiple PHP-FPM workers
- They can be invalidated centrally (logout-everywhere)
- They don't accumulate as files in `/tmp`

### 5.4 Idle Timeout

Sessions expire after **2 hours of inactivity**. The TTL is set in Redis, so a closed browser or inactive tab auto-logs-out the user.

### 5.5 Concurrent Session Detection

If a user logs in from a new device, the old session is invalidated and they receive an email: *"You were logged in from a new device. If this wasn't you, change your password immediately."*

---

## 6. Two-Factor Authentication (2FA)

All admin and privileged accounts **must** enable 2FA.

### 6.1 Implementation

- **TOTP** (RFC 6238) — 6-digit codes, 30-second window
- **Compatible** with Google Authenticator, Authy, Microsoft Authenticator, 1Password
- **Backup codes** — 8 single-use codes generated on 2FA setup
- **No SMS 2FA** — vulnerable to SIM swap

### 6.2 Recovery

If a user loses their authenticator:
1. Click "Lost access to your authenticator?"
2. Enter one of the 8 backup codes
3. If no backup codes left, contact support with ID verification

### 6.3 Enforcement

The `require2FA()` middleware is applied to:
- `/admin/*` (all admin pages)
- `/user/profile` (profile changes)
- `/user/bank-details` (bank account changes)
- `/api/v2/*/write/*` (all write APIs)

---

## 7. Rate Limiting

We rate-limit per-IP and per-user to prevent:
- Brute-force login attacks
- Credential stuffing
- API abuse
- Scraping

### 7.1 Configuration (Nginx)

In `docker/nginx/conf.d/app.conf`:

```nginx
# API endpoints: 10 requests/sec per IP
limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
location /api/ {
    limit_req zone=api burst=20 nodelay;
    ...
}

# Login: 5 requests/min per IP
limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;
location ~ ^/(login|admin/login|register)$ {
    limit_req zone=login burst=5 nodelay;
    ...
}
```

### 7.2 Application-Level

For sensitive endpoints, we also limit at the PHP level:

```php
$rateLimiter = new RateLimiter('login', 5, 60);  // 5 attempts per 60s
if ($rateLimiter->tooManyAttempts($ip)) {
    $retryAfter = $rateLimiter->availableIn($ip);
    return $this->json(['error' => 'Too many attempts'], 429, ['Retry-After' => $retryAfter]);
}
```

### 7.3 Failed Login Tracking

After **5 failed logins** in 15 minutes from the same IP, the IP is blocked for 1 hour. Tracked in the `failed_login_attempts` table.

---

## 8. IP Blocking

Suspicious IPs are added to the `blocked_ips` table:

- After 10 failed logins in 24h
- After detecting SQL injection probes
- After DDoS patterns (manual + automated)
- Geographically restricted for admin (optional, e.g., India-only)

### 8.1 Block Check

The `SecurityService::isIpBlocked($ip)` is called at:
- Web request start (middleware)
- WebSocket connection start
- API request start

### 8.2 Bypass for Trusted IPs

Add to `trusted_ips` table:
- Office public IP
- CI/CD runners
- Monitoring systems

---

## 9. Audit Logging

All security-relevant events are logged to the `audit_log` table:

| Event | Logged Fields |
|-------|---------------|
| `login_success` | user_id, user_role, ip, user_agent, timestamp |
| `login_failed` | email, ip, user_agent, reason, timestamp |
| `logout` | user_id, ip, timestamp |
| `password_changed` | user_id, ip, timestamp |
| `2fa_enabled` / `2fa_disabled` | user_id, ip, timestamp |
| `admin_action` | user_id, action, entity_type, entity_id, changes (JSON) |
| `permission_denied` | user_id, url, ip, timestamp |
| `rate_limit_exceeded` | ip, endpoint, timestamp |
| `ip_blocked` | ip, reason, timestamp |

### 9.1 Retention

- Hot: 90 days (in MySQL)
- Cold: 2 years (in S3, gzip-compressed, AES-256 encrypted)

### 9.2 Access

- Admin can view last 200 events at `/admin/audit-log`
- Full export available to Super Admin only
- Real-time alerts to Slack on suspicious patterns (e.g., 100+ failed logins from one IP)

---

## 10. Backup Encryption

Database backups are encrypted **at rest** with AES-256-CBC:

```bash
# In scripts/backup_before_deploy.sh
openssl enc -aes-256-cbc -salt -pbkdf2 -iter 100000 \
  -in backup.sql.gz \
  -out backup.sql.gz.enc \
  -pass "pass:${BACKUP_ENCRYPTION_KEY}"
```

### 10.1 Key Management

- `BACKUP_ENCRYPTION_KEY` stored in:
  - Production: HashiCorp Vault / AWS Secrets Manager
  - Local dev: `.env` (gitignored)
- Key rotation: yearly
- Lost key = lost backups (no recovery)

### 10.2 Restore

```bash
openssl enc -d -aes-256-cbc -pbkdf2 -iter 100000 \
  -in backup.sql.gz.enc \
  -out backup.sql.gz \
  -pass "pass:${BACKUP_ENCRYPTION_KEY}"
gunzip -c backup.sql.gz | mysql ...
```

### 10.3 Test Restores

We run a **monthly restore drill**:
1. Pick a random backup from the last 30 days
2. Decrypt and load into a staging MySQL
3. Run E2E tests against it
4. Verify the drill in `audit_log`

---

## 11. Secret Management

### 11.1 What Counts as a Secret

- Database passwords
- API keys (Twilio, Sentry, Google OAuth, Stripe, etc.)
- JWT signing keys
- Backup encryption keys
- Session encryption keys
- Webhook signing secrets

### 11.2 Storage

| Environment | Where |
|-------------|-------|
| **Local dev** | `.env` (gitignored) |
| **CI/CD** | GitHub Encrypted Secrets |
| **Production** | `/opt/apsdreamhome/production.env` (chmod 600, owned by `deploy:deploy`) |
| **Highly sensitive** | HashiCorp Vault or AWS Secrets Manager |

### 11.3 Rotation Policy

| Secret | Rotation |
|--------|----------|
| DB passwords | Quarterly |
| API keys (external) | Annually or on compromise |
| `APP_KEY` | Annually (invalidates all sessions — plan downtime) |
| SSH keys | Annually |
| Backup encryption key | Annually (re-encrypt all backups) |

### 11.4 Detection of Leaked Secrets

- **TruffleHog** runs on every PR (`.github/workflows/php.yml`)
- **GitGuardian** monitors the public GitHub mirror
- **Pre-commit hook** blocks commits containing patterns matching API key formats

---

## 12. Operational Security

### 12.1 Access Control

- **Principle of least privilege** — admin roles are scoped (CEO, CFO, Builder, Agent, etc.)
- **2FA mandatory** for all admin accounts
- **No shared admin accounts** — every action is attributable to a real user
- **Quarterly access review** — inactive admin accounts are disabled

### 12.2 Server Hardening

- **SSH**: Key-only auth (passwords disabled)
- **Firewall**: Only 22, 80, 443 open
- **Fail2ban**: Auto-bans IPs with 3+ failed SSH attempts
- **Auto-updates**: `unattended-upgrades` enabled for security patches
- **No root login** — `PermitRootLogin no` in `sshd_config`
- **No password auth** — `PasswordAuthentication no`

### 12.3 Container Security

- All images run as **non-root user** (`www-data` in PHP, `nginx` in nginx)
- **No secrets baked into images** — all secrets via env vars or mounted files
- **Read-only filesystems** where possible (`/var/www/html:ro`)
- **Image scanning** with Trivy on every PR
- **Minimal base images** (`php:8.2-apache`, `alpine:3.19`)

### 12.4 Dependency Management

- **Composer** for PHP — `composer audit` on every CI run
- **NPM** for JS — `npm audit` on every CI run
- **Dependabot** enabled for automated PR creation on new CVEs
- **Monthly manual review** of `composer outdated` and `npm outdated`

---

## 13. Incident Response

### 13.1 Severity Levels

| Level | Examples | Response Time |
|-------|----------|---------------|
| **P0** | Active breach, data exfiltration | < 1 hour |
| **P1** | Vulnerability disclosed, exploit public | < 4 hours |
| **P2** | Suspicious activity, no confirmed impact | < 24 hours |
| **P3** | Best-practice improvement | < 1 week |

### 13.2 Contacts

- **Security Lead**: security@apsdreamhome.com
- **On-call rotation**: PagerDuty
- **External IR firm**: [TBD]
- **Legal/PR**: For P0/P1 only

### 13.3 Playbooks

Detailed playbooks for common incidents are in the `runbooks/` directory (private repo):
- `01-data-breach.md`
- `02-ransomware.md`
- `03-ddos.md`
- `04-credential-leak.md`
- `05-supply-chain-attack.md`

### 13.4 Post-Mortem

Every P0/P1 incident requires a **blameless post-mortem** within 7 days, with:
- Timeline of events
- Root cause analysis
- Action items with owners and deadlines
- Updated detection/prevention controls

---

## 14. Compliance

### 14.1 Data We Collect

- Account info: name, email, phone, hashed password
- Property data: addresses, photos, financial info
- Payment data: **never stored on our servers** (handled by Stripe/Razorpay)
- Behavioral data: page views, search queries (anonymized after 90 days)

### 14.2 User Rights

Users can:
- **Access** their data (GDPR Art. 15) — `GET /api/v2/user/export`
- **Delete** their data (GDPR Art. 17) — `POST /api/v2/user/delete` (Super Admin confirmation)
- **Rectify** inaccurate data — edit profile
- **Object** to processing — opt out of marketing in `user_dashboard.php`
- **Port** their data — JSON export

### 14.3 Data Retention

| Data Type | Retention |
|-----------|-----------|
| Active user accounts | Indefinite |
| Inactive accounts (> 2 years) | Anonymized |
| Logs (access, error) | 90 days hot, 1 year cold |
| Backups | 30 days local, 90 days S3 |
| Payment records | 7 years (tax compliance) |
| User-uploaded images | Account lifetime + 30 days after deletion |

### 14.4 Indian IT Act, 2000

We comply with the **Information Technology (Reasonable Security Practices and Procedures and Sensitive Personal Data or Information) Rules, 2011**:
- Consent obtained before data collection
- Clear privacy policy
- Data Protection Officer appointed
- Grievance officer contact published on the site

---

## 15. Reporting Vulnerabilities

We welcome reports from security researchers.

### 15.1 How to Report

- **Email**: security@apsdreamhome.com (PGP key on request)
- **Subject**: `[SECURITY] Brief description`
- **Include**: Steps to reproduce, impact assessment, suggested fix (optional)

### 15.2 Response SLA

- **Acknowledgment**: within 24 hours
- **Triage**: within 72 hours
- **Fix timeline**: depends on severity (P0: 24h, P1: 7d, P2: 30d, P3: 90d)
- **Public disclosure**: 90 days after fix, or by mutual agreement

### 15.3 Bounty Program

We do not currently run a paid bug bounty program, but we do:
- Publicly credit reporters (with permission)
- Send thank-you swag for valid reports
- Maintain a **Hall of Fame** on our website

### 15.4 Out of Scope

- Denial-of-service attacks
- Social engineering
- Physical attacks
- Recently-disclosed 0-days (< 30 days)
- Issues requiring physical access to a user's device
- Self-XSS

---

## Appendix: Security Checklist (Quarterly Review)

- [ ] All dependencies up to date (`composer outdated`, `npm outdated`)
- [ ] No secrets in Git (`gitleaks detect`)
- [ ] All admin accounts have 2FA
- [ ] Failed login tracking shows no unusual spikes
- [ ] Backups are encrypted and tested (monthly restore drill)
- [ ] SSL certificate valid for > 30 days
- [ ] Security headers verified (HSTS, CSP, X-Frame-Options)
- [ ] Rate limits are appropriate (check `audit_log` for 429s)
- [ ] Firewall rules still minimal (only 22, 80, 443)
- [ ] SSH access only via key
- [ ] Server OS patches up to date
- [ ] Sentry shows no recurring errors
- [ ] Penetration test scheduled (annually, external firm)

---

**Questions?** Contact security@apsdreamhome.com

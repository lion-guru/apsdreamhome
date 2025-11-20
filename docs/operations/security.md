# Security Operations Playbook

Consolidated runbook for maintaining APS Dream Home's security posture. Derived from `SECURITY_IMPLEMENTATION_COMPLETE.md` and related security scripts to capture ongoing procedures, ownership, and monitoring.

## Roles & Responsibilities

| Role | Responsibilities |
| ---- | ---------------- |
| Security Lead | Owns security roadmap, approvals for high-risk changes, and monthly review meetings. |
| DevOps / Infrastructure | Manages HTTPS/SSL, server hardening, cron-based monitoring, and patch cycles. |
| Application Engineering | Maintains secure coding practices, reviews PRs for security regressions, updates `security-test-suite`. |
| QA / Automation | Runs security regression suites before releases, tracks remediation of failed tests. |
| Support & Incident Response | Monitors alerts, coordinates response to security events, maintains communication templates. |

## Core Controls Implemented

- **Database**: Prepared statements on all queries, PDO emulation disabled, strict input validation.
- **Authentication**: Session + token validation, Argon2id/Bcrypt hashing, brute force lockout, full CSRF coverage.
- **File Uploads**: MIME/size validation, virus scanning hooks, storage outside web root, permission hardening.
- **Application**: Security headers (CSP, HSTS, X-Frame-Options), HTTPS-ready configuration, XSS safeguards.
- **Monitoring**: Real-time security monitor, alerting on failed logins and anomalies, comprehensive log trail.

## Daily / Weekly Operations

| Cadence | Task | Owner |
| ------- | ---- | ----- |
| Daily | Review `security-monitor.php` alerts and failed login anomalies. | Support / Security Lead |
| Daily | Check web server error logs for new security warnings. | DevOps |
| Weekly | Run `php scripts/security-test-suite.php` and address regressions. | QA / Engineering |
| Weekly | Reconcile security logs with incident tracker; ensure alerts closed. | Security Lead |
| Weekly | Verify backups and retention for security logs and audits. | DevOps |

## Monthly & Quarterly Tasks

- Patch PHP, dependencies, and OS packages; document in change log.
- Rotate credentials/config secrets as per policy; validate `.env` alignment.
- Execute `php scripts/security-validation.php` and `php scripts/final-security-audit.php`; archive reports.
- Review CSP/Permissions-Policy headers to ensure new assets are whitelisted.
- Perform tabletop incident response drill once per quarter.

## HTTPS & TLS Management

1. Install or renew SSL certificate (Let's Encrypt or approved CA).
2. Enforce HTTPS redirects and update `.env` `APP_URL` to `https://`.
3. Validate HSTS header and certificate chain via SSL Labs or equivalent.
4. Regression-test key flows (login, file uploads, admin operations) over HTTPS.

## Security Test Suite

Available scripts under `scripts/`:

- `security-test-suite.php` – full regression (45 tests). Run before each release.
- `security-validation.php` – quick validation, use post-deploy.
- `final-security-audit.php` – comprehensive report for compliance sign-off.
- `security-monitor.php` – cron-based monitoring; ensure job runs every minute.
- `deploy-security.php` – helper for secure deployment steps.

### Cron Recommendations

```cron
* * * * * php /path/to/security-monitor.php
0 2 * * * php /path/to/security-audit.php
0 3 * * 0 php /path/to/security-test-suite.php
```

Ensure cron output is logged, and set up alerting for failures.

## Incident Response

| Scenario | Immediate Actions | Follow-up |
| -------- | ---------------- | --------- |
| Suspected intrusion | Activate incident bridge, isolate affected systems, rotate credentials. | Forensic analysis, post-mortem, update defenses. |
| Brute-force attack | Lock offending accounts/IPs, enable rate limiting/captcha, notify stakeholders. | Tune lockout thresholds, review WAF/IP blocklists. |
| CSRF/XSS regression | Roll back offending deployment, communicate to users, hotfix. | Expand automated tests, update secure coding checklist. |
| File upload abuse | Disable uploads, scan storage for malicious files, notify affected users. | Strengthen validation, enable antivirus scanning integration. |
| TLS certificate expiry | Switch to backup cert, renew and redeploy. | Automate renewal reminders, audit certificate inventory. |

## Metrics & KPIs

| KPI | Target | Measurement |
| --- | ------ | ----------- |
| Security test pass rate | ≥ 98% | `security-test-suite.php` reports. |
| Mean time to acknowledge alerts | < 30 minutes | Monitoring dashboard stats. |
| Time to patch critical CVEs | ≤ 7 days | Change management log. |
| Number of unresolved security tickets | 0 | Security backlog review. |
| HTTPS coverage | 100% traffic | Web analytics / server config audits. |

## Documentation & References

- Source report: `SECURITY_IMPLEMENTATION_COMPLETE.md`
- Deployment alignment: `docs/deployment/README.md`
- Monitoring guidance: `docs/operations/colonizer.md` (for shared commission/HR systems), `docs/operations/crm.md`
- Checklist references: `SECURITY_CHECKLIST.md`, `MAINTENANCE_MONITORING_GUIDE.md`, `PRODUCTION_DEPLOYMENT_GUIDE.md`

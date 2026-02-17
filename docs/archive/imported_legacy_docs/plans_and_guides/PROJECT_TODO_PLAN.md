# APS Dream Home — Operational TODO & Planning Guide

## Current Status
- Application loads correctly at `http://localhost/apsdreamhomefinal/` via `index.php` dispatching to `HomeControllerSimple::index()`.
- Global PHP lint (`php -l`) passes on all project files.
- Legacy database constants are guarded in `config/database.php` preventing redefinition warnings.
- Controllers use explicit `use Exception;` imports where required.

## Immediate TODOs (This Week)
- **Homepage Verification**: Confirm homepage renders through Apache after the new `HomeControllerSimple::index()` bridge. Validate dynamic sections (`featured_properties`, `locations`) against live data.
- **Browser Smoke Test**: Walk core routes configured in `app/core/Router.php` (`home`, `about`, `contact`, `projects`, `monitor`) to ensure no unexpected 404s.
- **Session & Security Audit**: Review session/cookie settings in `SystemIntegration::setupSession()` and `SystemIntegration::setupSecurity()` for HTTPS deployment readiness.
- **Database Connection Review**: Confirm credentials in `.env` or environment variables align with guarded constants in `config/database.php` to avoid silent fallbacks.

## Near-Term Enhancements (1–2 Weeks)
- **Routing Coverage**: Map high-impact routes to controller actions and document expected view templates to ease future QA cycles.
- **Cache Strategy Verification**: Exercise `App\Core\Cache::getInstance()` across File/Redis drivers; define environment toggles in `.env` for production/staging.
- **Monitoring Dashboard Polish**: Validate data feeds in `monitor_dashboard.php` and API responses from `MonitorApiController` (status, health, performance, errors).
- **Backup Automation**: Dry run `BackupManager::createFullBackup()` and confirm storage path rotation limits.

## Medium-Term Roadmap (2–4 Weeks)
- **Performance Regression Tests**: Add automated scripts that leverage `PerformanceMonitor::getInstance()` to capture baselines after deployments.
- **Security Hardening**: Implement CSP headers and review rate-limiting data files under `app/cache/` for rotation/cleanup tasks.
- **Documentation Refresh**: Sync `PRODUCTION_DEPLOYMENT_GUIDE.md` with the latest configuration changes (session hardening, database constant guards, new homepage flow).
- **IDE Stability**: Maintain `.vscode/settings.json` overrides to suppress third-party stub noise and evaluate migrating to Intelephense workspace settings.

## Maintenance Checklist
- **Weekly**: Run project-wide `php -l` lint, inspect `storage/logs` and Apache `error.log` for anomalies.
- **Monthly**: Validate backups, refresh SSL certificates, review user activity logs for anomalies.
- **Quarterly**: Reassess feature toggles in `SystemIntegration::initializeFeatures()` and retire experimental flags if unused.

## Notes & References
- Homepage controller: `app/controllers/HomeControllerSimple.php`
- System bootstrap: `config/bootstrap.php`
- Router definitions: `app/core/Router.php`
- Database configuration: `config/database.php`

*Last updated: Oct 21, 2025*

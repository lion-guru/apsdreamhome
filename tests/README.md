# Tests

This directory hosts the CI test infrastructure for APS Dream Home.

## Layout

| Path | Purpose |
|---|---|
| `migrate.php` | Run the project's migration scripts against a fresh DB |
| `run_all_tests.php` | Discover and run `testing/test_*.php`, write JUnit XML |
| `reports/` | JUnit XML + run/migrate logs (CI artifacts) |
| `comprehensive_test_runner.php` | Legacy full-coverage runner (dev use) |
| `deep_system_validator.php` | Deep system sanity sweep (dev use) |
| `schema_validator.php` | Schema-only validation (dev use) |
| `duplicate_checker.php` | Duplicate-table / column detector (dev use) |
| `validate_new_features.php` | Smoke test for new feature toggles (dev use) |

## Local usage

```powershell
# Run all unit + integration tests (skips load tests by default)
php tests/run_all_tests.php

# Run a single suite
php tests/run_all_tests.php --suite=unit
php tests/run_all_tests.php --suite=integration
php tests/run_all_tests.php --suite=load

# Run database migrations against the test DB
php tests/migrate.php

# Single test file
php testing/test_translations.php
```

## CI usage (GitHub Actions)

The CI workflows call these scripts directly. See `.github/workflows/ci.yml`
for the canonical reference. The pattern is:

```yaml
- name: Run migrations
  run: php tests/migrate.php

- name: Run test suite
  run: php tests/run_all_tests.php
```

The TestRunner writes a JUnit XML at `tests/reports/junit.xml` that the
`EnricoMi/publish-unit-test-result-action` step (in `ci.yml`) uploads for
the GitHub Actions Checks UI.

## Exit codes

- `0` – all scripts passed
- `1` – at least one script failed
- `2` – script opted to skip (returned 2 explicitly, not treated as failure)

## Test scripts

There are 39+ test files in `testing/test_*.php` covering:

- Translations (EN/HI parity)
- Email templates
- Push notifications
- PDF service
- Image gallery / uploader
- Maintenance mode
- Hot path cache
- WebSocket (basic, integration, E2E, full)
- Communication gateway (Twilio, Razorpay, SMTP)
- S3 storage + CORS
- Mobile API (JWT)
- 2FA / TOTP
- A/B testing
- API docs (OpenAPI)
- Audit log
- And more

Each script is standalone - it includes its own bootstrap and reports via
exit code. The TestRunner collects them, captures their output, and emits
JUnit XML for CI.

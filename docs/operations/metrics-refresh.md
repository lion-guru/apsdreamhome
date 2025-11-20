# MLM Metrics & Leaderboard Refresh Runbook

This document explains how to use the Phase 5 engagement refresh utilities to keep associate metrics and leaderboards up to date.

## Overview
- **Script:** `tools/mlm_metrics_refresh.php`
- **Purpose:** Aggregates monthly associate metrics (`mlm_associate_metrics`) and generates leaderboard snapshots (`mlm_leaderboard_snapshots`, `mlm_leaderboard_runs`).
- **Inputs:** Commission ledger, referral log, network tree, and existing metrics table.

## Usage
```bash
php tools/mlm_metrics_refresh.php [options]
```

### Options
| Flag | Description | Example |
|------|-------------|---------|
| `--period=YYYY-MM` | Refresh for a specific month. Defaults to current month. | `--period=2025-01` |
| `--start=YYYY-MM-DD --end=YYYY-MM-DD` | Explicit date range override (requires both). | `--start=2024-12-01 --end=2024-12-31` |
| `--metrics-only` | Only update `mlm_associate_metrics`. | `--metrics-only` |
| `--leaderboard-only` | Only rebuild leaderboard snapshots. | `--leaderboard-only` |
| `--dry-run` | Preview stats without writing to the DB. | `--dry-run` |

## Scheduling
### Linux (cron)
Add to `/etc/cron.d/mlm-metrics`:
```cron
# Refresh metrics nightly at 02:10
10 2 * * * www-data /usr/bin/php /var/www/apsdreamhome/tools/mlm_metrics_refresh.php >> /var/www/apsdreamhome/storage/logs/mlm_metrics.log 2>&1
```

### Windows Task Scheduler
1. Create Basic Task → Daily → pick time (e.g. 2:10 AM).
2. Action: "Start a program" → Program/script: `php.exe`.
3. Add arguments: `"C:\xampp\htdocs\apsdreamhome\tools\mlm_metrics_refresh.php"`.
4. Start in: `C:\xampp\htdocs\apsdreamhome`.
5. Optional: Redirect output by appending `>> C:\xampp\htdocs\apsdreamhome\storage\logs\mlm_metrics.log 2>>&1`.

## Verification Checklist
1. Confirm `mlm_associate_metrics` has rows for the intended period (check `period_start`, `period_end`).
2. Review `mlm_leaderboard_runs` for a new entry with `status=complete`.
3. Inspect `mlm_leaderboard_snapshots` – records should exist for each metric type.
4. Load `/admin/mlm_engagement.php` and verify cards/leaderboard reflect latest data.

## Troubleshooting
| Symptom | Likely Cause | Resolution |
|---------|--------------|------------|
| Script exits with DB error | Missing connection or wrong credentials | Verify `.env` / config and rerun |
| No leaderboard rows generated | No metrics available for period | Ensure commission/referral data exists for the range |
| Cron job silent failures | Permissions/log redirection issues | Check cron logs (`/var/log/syslog`) or Task Scheduler history |
| Dashboard still showing stale data | Browser cache or API failure | Refresh page, inspect console, confirm `/admin/engagement/*` endpoints return data |

## Related Scripts
- `tools/mlm_rank_recalculate.php` – recalculates rank labels based on lifetime sales.
- `tools/mlm_digest.php` – daily digest email with key MLM metrics.

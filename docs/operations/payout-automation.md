# MLM Payout Automation Runbook

This runbook documents the automated payout workflow introduced in MLM Phase 5.

## Overview
- **Purpose:** Automatically bundle approved commissions into payout batches on a fixed cadence, ensure multi-approver sign-off, and capture audit trails.
- **Entry Point:** `tools/mlm_payout_automation.php`
- **Dependencies:**
  - `mlm_commission_ledger`
  - `mlm_payout_batches`, `mlm_payout_batch_items`, `mlm_payout_batch_approvals`
  - `app/services/PayoutService.php`
  - `app/services/MlmSettings.php`
  - `NotificationService`

## Configuration Keys (`mlm_settings`)
| Key | Type | Default | Description |
| --- | --- | --- | --- |
| `payout_automation_enabled` | int | `0` | Enables automation when set to `1`. |
| `payout_automation_interval_hours` | int | `24` | Minimum hours between automatic runs. |
| `payout_automation_lookback_days` | int | `7` | Number of days to consider if `--date-from` not supplied. |
| `payout_automation_min_amount` | float | `0` | Minimum total commission required to create a batch. |
| `payout_automation_max_items` | int | `0` (disabled) | Max ledger rows per batch. |
| `payout_automation_required_approvals` | int | `1` | Default approver threshold for new batches. |
| `payout_automation_last_run` | datetime | — | Managed automatically after a successful run. |
| `payout_automation_last_attempt` | datetime | — | Timestamp of the most recent execution (success or fail). |
| `payout_automation_last_result` | json | — | JSON summary of the last successful batch creation. |
| `payout_automation_last_error` | text | — | Most recent error message. |
| `payout_automation_last_context` | json | — | Filters/options used for the last attempt. |

Use `MlmSettings::set('<key>', <value>)` or update directly via SQL.

## Script Usage
```bash
php tools/mlm_payout_automation.php [options]
```

### Common Flags
- `--dry-run` – Inspect eligible commissions without committing changes.
- `--force` – Override interval/enablement checks (useful for urgent payouts).
- `--date-from="2025-01-01"` – Restrict ledger window start.
- `--date-to="2025-01-31"` – Restrict ledger window end.
- `--lookback-days=3` – Override lookback window (ignored if `--date-from` supplied).
- `--min-amount=100000` – Enforce minimum total amount.
- `--max-items=250` – Cap ledger rows pulled into the batch.
- `--required-approvals=2` – Define approver threshold for this run.
- `--batch-reference="AUTO-202501"` – Custom batch identifier.
- `--interval-hours=12` – Override interval guard just for this execution.

### Dry Run Output
Provides:
- Eligible commission count & total amount
- Earliest/latest commission timestamps
- Whether minimum amount is met
No database writes occur.

### Success Output
On success the script prints batch ID, reference, record count, total amount, and required approvals. The following keys are updated in `mlm_settings`:
- `payout_automation_last_run`
- `payout_automation_last_attempt`
- `payout_automation_last_result`
- `payout_automation_last_context`

If no batch is created (e.g., not enough commissions) `payout_automation_last_error` stores the reason.

### Failure Handling
- Exceptions capture the message, stored in `payout_automation_last_error` and surfaced to STDERR (exit code 2).
- The script exits with code 0 for both successful batches and benign skips (e.g., automation disabled or insufficient data).

## Scheduling
### Cron Example (Linux)
```cron
# Run every day at 02:00
0 2 * * * /usr/bin/php /path/to/apsdreamhome/tools/mlm_payout_automation.php >> /var/log/mlm_payout_automation.log 2>&1
```

### Windows Task Scheduler
1. Create Basic Task → Daily → specify time.
2. Action: "Start a Program" → Program/script: `php.exe`
3. Add arguments: `"C:\xampp\htdocs\apsdreamhome\tools\mlm_payout_automation.php"`
4. Start in: `C:\xampp\htdocs\apsdreamhome`
5. Configure log file redirection if needed.

## Approval Workflow
- Batches created via automation start in `pending_approval` with the configured `required_approvals` count.
- Admins use `/admin/payouts` to review.
- Approvals recorded through the UI (or `/admin/payouts/approve` endpoint) update `mlm_payout_batch_approvals` and `approval_count`.
- Rejection cancels the batch and logs notes; success moves to `processing` and sets `approved_at`.

## Operational Checklist
1. **Before scheduling:**
   - Ensure schema migrations for Phase 5 are applied.
   - Set `payout_automation_enabled = 1` when ready.
   - Configure interval/lookback/min thresholds per finance policy.
2. **Daily review:**
   - Check `/admin/payouts` for new `pending_approval` batches.
   - Confirm approvers record decisions promptly.
   - Monitor `mlm_logs` or system logs for automation errors.
   - Verify digest emails or notification alerts if linked.
3. **Monthly audit:**
   - Export batch data (CSV/PDF once available) for reconciliation.
   - Review `mlm_settings` history fields for anomaly detection.
   - Spot check ledger entries against disbursement references.

## Troubleshooting
| Symptom | Likely Cause | Resolution |
| --- | --- | --- |
| Script prints "Automation disabled" | `payout_automation_enabled = 0` | Enable setting or use `--force`. |
| Script skips due to interval | Last run within `interval_hours` | Use `--force` or adjust interval. |
| "No batch created" | Not enough approved commissions or below min threshold | Lower `min_amount`, expand lookback window, or approve more commissions. |
| Exception messages logged | DB connectivity, SQL errors, or schema mismatch | Check server logs, verify schema updates, rerun after fix. |

## Related Documentation
- `docs/operations/network-ux.md` (Phase 4 network tooling)
- `docs/operations/notifications.md`
- Admin UI handbook: `app/views/admin/mlm_payouts.php`
- Service logic: `app/services/PayoutService.php`

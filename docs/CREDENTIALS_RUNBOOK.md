# Credentials Runbook - AWS S3 + Twilio (2026-11-14)

**Status of exposure (verified 2026-11-14, all code-side work done):**

| Where | What | State |
|---|---|---|
| `database/migrations/configure_aws_twilio_s419.php` | AWS secret + Twilio SID/token + `test_mode=1` | Untracked, **gitignored**; **untracked-file literals REMOVED 2026-11-14** (env reads + fail-closed, commit `597e26442`) |
| `database/migrations/update_twilio_number_s419.php` | Same Twilio SID/token + `+18126684585` + `test_mode=0` | Untracked, **gitignored**; literals REMOVED 2026-11-14 (same commit) |
| `service_configs` DB rows (`ai_voice.voice_twilio`, `communications.twilio`) | Same SID/token, **stored as legacy plaintext** (not `enc:v1:`) | **ENCRYPTED AND VERIFIED 2026-11-14** (migration S524: 14 rows `enc:v1:`, 13 empty skipped, scratch-restore byte-exact 25/25, commit `e33b430c7`+`d95e62850`) |
| `service_config_audit` | 15 plaintext rows, **only this copy remained after encryption** | **REDACTED 2026-11-14** — 10 secret payloads (3 is_secret-tied incl. real AWS secret `RuwAzOAqOXBR...` + 7 voice seeds) → `[redacted]`, metadata kept; 3 non-secret rows untouched (commit `d95e62850`) |
| git history | All the above values permanent in history | **Cannot be erased** — rotation required (below) |

**Conclusion: rotation still required.** The tracked files are clean and every stored value is now
ciphertext, but the values themselves are still public on `origin/main` history. Rotate at both providers — there is no way to know who else has cloned.

**Conclusion: the credentials are no longer safe.** They were pasted in plaintext into two files, and those same values are inside the live production DB table in plaintext. Rotate both providers — there is no way to know who else has read the DB rows (backups, dumps, anyone with DB access).

## Step-by-step (owner)

### 1. Rotate the providers first
- **AWS**: IAM → the `AKIA33LN6...` user → Security credentials → **Create new access key** → save the new pair → **Delete** the old access key.
- **Twilio**: Console → Security → **Rotate auth token** (or create a new API key and delete the old one). Deleting the old auth token kills anything using it, including the now-encrypted rows.
- **OpenCode Zen keys (techguru + apsdreamhomes44)**: opencode console → API keys → rotate/regenerate both → update `.env` (`OPENCODE_ZEN_KEY_APS`, `OPENCODE_ZEN_KEY_TECHGURU`) → use `switch_opencode_key.ps1 techguru|aps` (env-sourced, 2026-11-14). Old keys are public in history; rotation is the only fix.
- **Phone number**: note `+18126684585` belongs to the Twilio account — leave it alone unless you want a new number.

### 2. Re-enter the new credentials via the admin panel
Go to the live site → `/admin/service-configs` → "Twilio" and "AWS S3" cards → paste new values. The panel stores secrets as `enc:v1:` AES-256-CBC (key `storage/keys/service_config.key`), never as plaintext. No file needs to be written; no migration needs to run again.

Values needed:
- Twilio: `twilio_sid` (new), `twilio_token` (new), `twilio_number` = keep `+18126684585` or new number, `test_mode` = **1** until you deliberately go live, plus `account_sid`, `auth_token`, `from_number`, `whatsapp_number`, and `voice_*` JSON Config.
- AWS S3: `access_key`, `secret_key`, `region`, `bucket`, `use_path_style`.

Note: `voice_twilio` is stored as a JSON blob inside one `config_value` row (group `ai_voice`); the admin GUI handles the whole JSON object for you. Selectively re-saving only one nested field through raw SQL is how the plaintext rows got created in the first place — don't do it.

### 3. Delete the two one-off scripts
After step 2 verifies:
```powershell
Remove-Item database\migrations\configure_aws_twilio_s419.php
Remove-Item database\migrations\update_twilio_number_s419.php
```
Then also delete equivalent temp copies anywhere else (desktop, Downloads, OneDrive). The `.gitignore` rules can stay — they're harmless.

### 4. Verify
- `/admin/service-configs` loads, Twilio + AWS S3 cards show masked/verified state.
- Trigger one outgoing call/SMS in **test mode** (`test_mode=1`) first. Only then set test mode to 0.
- Confirm the two affected rows now begin with `enc:v1:`:
  ```sql
  SELECT group_name, config_key, LEFT(config_value, 12) FROM service_configs
  WHERE (group_name = 'voice' OR group_name = 'ai_voice' OR group_name = 'communications')
    AND service_name IN ('twilio', 'voice_twilio', 's3', 'aws');
  ```

## Hard rules learned from this incident
1. **One-off credential migrations are never the path.** The `service_configs` GUI exists, encrypts on write, and lets rotation happen without a file ever existing.
2. **Any one-off `.php` that writes credentials to the DB is trash the moment it runs.** It bypasses encryption and leaves plaintext in the DB.
3. **CI/local lint cannot catch this class of problem.** The only proof is the gitignore lists + the provider-side rotation + `enc:v1:` spot-checks.

Written 2026-11-14 after finding the two scripts already executed. Values intentionally NOT reproduced here — rotate at the provider, don't copy them again anywhere.

## Addendum 1 (2026-11-14, later same day): the AES-256 key itself was committed to git

- `storage/keys/service_config.key` (the 32-byte key behind `enc:v1:`) was committed in `bee73667e` (Session 348, "OpenCode Zen multi-key switcher …") and sat on `origin/main`, byte-identical to the working-tree copy (SHA256 `21896032…`). Anyone with a clone had the full encryption primitive.
- **Verification before rotation**: `SELECT COUNT(*) FROM service_configs WHERE config_value LIKE 'enc:v1:%'` → **0**. No ciphertext depended on the compromised key, so the rotation destroys nothing.
- **What was done**: key was untracked from git (`git rm --cached`), a fresh 32-byte random key was written, `storage/keys/` added to `.gitignore`. Roundtrip through `ServiceConfigService::encryptValue`/`decryptValue` passed on the new key.
- **Backup of the old key**: `backups/keys/service_config.key.2026-11-14.bak` (gitignored; not in the repo).
- **What history holds**: the compromised key remains in git history (force-push was declined). Anyone who cloned before this point must be treated as having it. The correct response — rotating every secret ever written with it — is equivalent to rotating every `enc:v1:` secret in the DB, of which there are currently **zero**. The live (plaintext) SMTP/Twilio/AWS/Razorpay values already require provider-side rotation per the original runbook; this exposure does not enlarge that set, it just removes the last theoretical protection of future writes.

Rules added:
4. **Encryption keys belong in gitignored storage, never in the repo** — and they must be treated as compromised from the moment they land anywhere shared. A key leak is total: no rotation of ciphertexts is possible after the fact.
5. **A feature whose entire point is "encrypted at rest" is only as safe as its key file.** When adopting `enc:v1:` for a new secret, first check `git ls-files storage/keys` is empty and `git check-ignore storage/keys` matches — in this repo both failed for months.

## Addendum 2 (2026-11-14, later same day): the encryption feature was fake at EVERY layer

While probing the key rotation end-to-end, the app's own code shows the entire "encrypted at rest" story was bypassed:

1. **`set()` INSERT branch stored secrets PLAINTEXT** — `$storeValue` (the encrypted string) was computed but never bound; the INSERT used `(string) $value`. Every freshly-entered secret was stored raw. Only UPDATEs of existing rows ever encrypted — and only with the key that was already compromised.
   **FIXED** (2026-11-14, Session 526): INSERT now binds `$storeValue`; sacrificial probe through the real service confirms `stored_is_enc=true`, `decrypt_roundtrip=true`, `audit_redacted=true`.
2. **`set()` audit hook logged every plaintext secret** into `service_config_audit.new_value` in clear. Measured: **15 audit rows, 11 in plaintext, 3 tied to `is_secret=1` parents** (exotel/twilio/aws vintages).
   **FIXED** (Session 526): `set()` now records `[secret updated]` for `is_secret` writes (old_value likewise), while preserving who/when/ip. `auditManual()` callers pass non-secret payloads only (referral tiers, wallet package %) — untouched.
3. **`service_config_audit` still contains the legacy plaintext entries (11 rows)** — rewritten or deleted by owner decision only; rotate the underlying Exotel/Twilio/AWS credentials at the provider, as per Addendum 1.

What that means in practice, for the owner:
- Every secret entered through the admin panel SINCE S348 was stored in **plaintext** in `service_configs` (UPDATE path excepted when EDITING an existing row — rare) AND was ALSO copied to `service_config_audit` in plaintext by design.
- The "encrypted" section of the runbook above was an honorary label. Any credential entered previously must be rotated regardless of what's stored.
- The parked migration `db2da2923:database/migrations/encrypt_service_config_secrets_s524.php` (env-gated, single-transaction, per-row decrypt assertion) encrypts the **storage** table once announced; it does NOT clean the audit table — that is a separate owner decision.

Rules added:
6. **Smoke-test the actual write path with a sacrificial probe, not the unit of the feature.** A service that "uses encryption" at class level can enqueue PLAINTEXT through its INSERT — one line of commented intent (\`$storeValue\` computed-but-unused) slipped past four reviews of the file.
7. **Secrets have three layers to audit: storage, transport, and audit/logs. Fixing only the storage layer leaves the audit table as a parallel plaintext copy.** All three were fake here.

## Addendum 3 (2026-11-14): exact plaintext token manifest - rotate at providers, then delete rows

`scripts/secret_audit.php` (committed, exit 1 on findings) flags **22 STRICTLY token-shaped plaintext rows** (signature + length pattern). A looser prefix-only scan returns 31 (test/short keys included); the committed audit number is authoritative. Every `api_keys` row audited: `usage_count = 0`, `last_used_at = NULL` - the app reads credentials from `.env` / `service_configs`, never from these tables.

### `settings` (11 flagged; loose prefix shows 14)
| id | key |
|----|-----|
| 423 | `api_groq_key` |
| 426 | `api_openrouter_key_backup` |
| 429 | `api_openai_key_test` |
| 430 | `api_gemini_key` |
| 431 | `api_google_speech_key` |
| 432 | `api_opencode_key` |
| 434 | `api_huggingface_key` |
| 435 | `api_kimi_key` |
| 440 | `api_supabase_auth_token` |
| 458 | `api_opencode_key_techguru` |
| 459 | `api_opencode_key_aps` |

### `api_keys` (11 flagged; loose prefix shows 17 - IDs 10, 23, 26, 27, 28, 30 are short/test values not flagged)
| id | service_name | key_name |
|----|--------------|----------|
| 4 | OpenRouter | OPENROUTER_API_KEY |
| 7 | OpenAI | OPENAI_API_KEY |
| 11 | Hugging Face | HUGGING_FACE_API_KEY |
| 19 | groq | GROQ_API_KEY |
| 20 | openrouter | OPENROUTER_API_KEY_BACKUP |
| 22 | opencode | OPENCODE_ZEN_KEY_TECHGURU |
| 25 | (empty) | GROQ_API_KEY |
| 29 | (empty) | OPENCODE_API_KEY |
| 32 | (empty) | GOOGLE_SPEECH_API_KEY |
| 33 | opencode | OPENCODE_ZEN_KEY_TECHGURU |
| 34 | opencode | OPENCODE_ZEN_KEY_APS |

### What the owner must do (in order)
1. **Rotate at the provider console**: OpenCode Zen (apsdreamhomes44@gmail.com + techguruabhay@gmail.com), OpenAI, OpenRouter, Groq, Anthropic, HuggingFace, Kimi/Zenmux (if they exist), GCP (`api_gemini_key`, `api_google_speech_key`).
   The live app keeps working - none of these rows feed any code path (all API reads go through `.env` / `service_configs`; live keys live there).
2. **Re-enter the rotated values** through `/admin/service-configs` (stored `enc:v1:` + audit-redacted per Session 526), or into `.env` as env keys.
3. **Delete the 22 stale rows** once rotation is confirmed, with the committed one-shot tool (safety dump taken automatically, single transaction, token-shape re-verified per row):
   `php scripts/secret_purge_rotated.php --execute`
   or, for the loose-prefix leftovers too: `php scripts/secret_purge_rotated.php --execute --include-loose`
   Do NOT run this before rotation is announced - the rows are the owner's only local copy of the keys. Ten short/test-prefix rows remain outside both lists by design (all confirmed NOT token-shaped).
4. **Verify**: `php scripts/secret_audit.php` -> `RESULT: OK`; `php testing/master_test_runner.php` -> 11/11.
5. **`service_config_audit`** still holds 11 legacy plaintext rows (Exotel/Twilio/AWS, Addendum 2) - same rotation class applies; purging audit rows is a separate explicit owner decision.

Rules added:
8. **A plaintext credential is compromised the instant it exists in a shared sink.** The fix is deletion + provider-rotation, never "rename to `_backup`" or "move to another table".
9. **Audit tables are sinks too.** Any audit/logging that stores secret payloads must record `[redacted]` for `is_secret` rows - see Session 526. Rotation cures the copy; deletion cleans the audit; rotation does not fix the audit.

## Addendum 5 (2026-11-15): SMTP is also silently dead — every send since forever has failed
The 2025-04 addition of `SMTP_PASS=xuvv-bzvz-updj-wcbn` to `.env` is **stale** — Google rejects it. Verified Nov-14 log: **255 EmailService failures** across 24 hours (every 3-10 minutes around the clock).
`EmailService` uses ONLY these `.env` lines (in priority order):
```
SMTP_HOST=smtp.gmail.com
SMTP_USER=apdreamhomes44@gmail.com
SMTP_PASS=xuvv-bzvz-updj-wcbn
```
`service_configs` rows for `smtp` (`host`, `username`, `password`, `from_email`, `from_name`) are all **empty strings** — never filled in.

**Impact:** Every automatic email the app sends is silently dropped — welcome on registration, EMI reminders, newsletter/search alerts, password resets, payment confirmations. `email_queue.sent` shows only 12 historical rows (May-October 2026), one from October.

**Owner action:**
1. Go to https://myaccount.google.com/apppasswords (2FA required on `apdreamhomes44@gmail.com`)
2. Generate new 16-char app password for "Mail".
3. Replace line 92 in `.env` `SMTP_PASS=xxxx-xxxx-xxxx-xxxx` (lowercase, no spaces surrounding `=`).
4. **Verify immediately**: `curl -b "same phpunit cookie jar" -X POST "http://localhost/apsdreamhome/admin/settings/email-test"` (or by visiting `/admin/settings/email` -> click "Send Test Email"; it uses the same `.env` config so the test result tells you `ready` vs `Could not authenticate`).

**Weekly scorecard already reports this** via `scripts/weekly_scorecard.php --quiet` -> exit 1 — it labels the row `secret audit`. Consider making it an explicit `SMTP probe` as soon as `service_configs` actually has non-zero SMTP values (then `$check('SMTP reachable', ...)` fits naturally).

## Addendum 4 (2026-11-14): log retention was silently dead; now one daily run + owner registration

- `ab_events` had 42,584 rows older than the 90-day rule (never executed: no OS scheduled task invoked
  `scripts/run_all_crons.php`; `logs/` shows no cleanup run). `visitor_page_views` held 5,837 overdue;
  `csp_violations` 637.
- **Purged** on 2026-11-14 (safety dump `backups/db/retention_purge_pre_2026-11-14.sql`, 25 MB):
  49,058 rows across 5 rules; post-purge `older_*` == 0.
- `scripts/cron_cleanup_logs.php` now has `--dry-run` (counts only, zero deletes) and prints
  per-table would-delete counts; `scripts/run_retention_purge.bat` wraps the standalone CLI
  (`php scripts/cron_cleanup_logs.php`) for scheduler use.
- **Owner action (interactive, elevated shell required)** - the current shell token lacks admin rights
  (schtasks returned E_ACCESSDENIED, consistent with the S174 salary-grant block):

  `schtasks /create /tn "APS_LogRetentionPurge" /tr "C:\xampp\htdocs\apsdreamhome\scripts\run_retention_purge.bat" /sc daily /st 03:30 /rl HIGHEST /f`

- Until that task exists, retention rules drift at ~12 ab_events rows/day. Run the bat manually or
  accept growth; do NOT wire web-request-triggered purges (deleting on page views is unacceptable).

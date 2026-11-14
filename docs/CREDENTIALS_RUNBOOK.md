# Credentials Runbook — AWS S3 + Twilio (2026-11-14)

**Status of exposure (verified 2026-11-14, nobody touched any values):**

| Where | What | State |
|---|---|---|
| `database/migrations/configure_aws_twilio_s419.php` | AWS secret + Twilio SID/token + `test_mode=1` | Untracked, **gitignored** (rule added 2026-11-14) |
| `database/migrations/update_twilio_number_s419.php` | Same Twilio SID/token + `+18126684585` + `test_mode=0` | Untracked, **gitignored** (rule added 2026-11-14) |
| `service_configs` DB rows (`ai_voice.voice_twilio`, `communications.twilio`) | Same SID/token, **stored as legacy plaintext** (not `enc:v1:`) | Live |
| `git` history (all branches) | None of the four values appears anywhere | Clean (verified with pickaxe) |

**Conclusion: the credentials are no longer safe.** They were pasted in plaintext into two files, and those same values are inside the live production DB table in plaintext. Rotate both providers — there is no way to know who else has read the DB rows (backups, dumps, anyone with DB access).

## Step-by-step (owner)

### 1. Rotate the providers first
- **AWS**: IAM → the `AKIA33LN6...` user → Security credentials → **Create new access key** → save the new pair → **Delete** the old access key.
- **Twilio**: Console → Security → **Rotate auth token** (or create a new API key and delete the old one). Deleting the old auth token kills anything using it, including the plaintext rows.
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

## Addendum (2026-11-14, later same day): the AES-256 key itself was committed to git

- `storage/keys/service_config.key` (the 32-byte key behind `enc:v1:`) was committed in `bee73667e` (Session 348, "OpenCode Zen multi-key switcher …") and sat on `origin/main`, byte-identical to the working-tree copy (SHA256 `21896032…`). Anyone with a clone had the full encryption primitive.
- **Verification before rotation**: `SELECT COUNT(*) FROM service_configs WHERE config_value LIKE 'enc:v1:%'` → **0**. No ciphertext depended on the compromised key, so the rotation destroys nothing.
- **What was done**: key was untracked from git (`git rm --cached`), a fresh 32-byte random key was written, `storage/keys/` added to `.gitignore`. Roundtrip through `ServiceConfigService::encryptValue`/`decryptValue` passed on the new key.
- **Backup of the old key**: `backups/keys/service_config.key.2026-11-14.bak` (gitignored; not in the repo).
- **What history holds**: the compromised key remains in git history (force-push was declined). Anyone who cloned before this point must be treated as having it. The correct response — rotating every secret ever written with it — is equivalent to rotating every `enc:v1:` secret in the DB, of which there are currently **zero**. The live (plaintext) SMTP/Twilio/AWS/Razorpay values already require provider-side rotation per the original runbook; this exposure does not enlarge that set, it just removes the last theoretical protection of future writes.

Rules added:
4. **Encryption keys belong in gitignored storage, never in the repo** — and they must be treated as compromised from the moment they land anywhere shared. A key leak is total: no rotation of ciphertexts is possible after the fact.
5. **A feature whose entire point is "encrypted at rest" is only as safe as its key file.** When adopting `enc:v1:` for a new secret, first check `git ls-files storage/keys` is empty and `git check-ignore storage/keys` matches — in this repo both failed for months.

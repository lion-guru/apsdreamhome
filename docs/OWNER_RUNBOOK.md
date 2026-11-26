# APS Dream Home — Owner's Runbook (One Page)

> Everything agent-side is done and verified. The codebase is green:
> master suite 12/12 · smoke 38/38 · PHPStan [OK] · DB baseline byte-identical (users 82 / bookings 23 / leads 994).
> This page is the only document you need to get the project from "works on this machine" to "live".
> Do the steps in order. Each step has an exact command or link — no guessing.
>
> **Updated 2026-11-26 (S770):** SMTP is now FIXED (valid Gmail App Password in `.env` + DB, verified live delivery) — Step 1 below is historical, keep it as the diagnostic reference. The REAL blocker is now: **9 commits are local-only (HEAD ahead of origin/main) AND they are rejected by GitHub Push Protection** — see Step 0b.

---

## 0 · Before anything (2 minutes)
- [ ] C: drive: **21 GB free** — do not install anything except what this page says (D: has 393 GB and is where backups and Flutter SDK live).
- [ ] Confirm the master cron is wired: `php scripts/run_all_crons.php --mode=daily --dry-run` prints 19 DAILY tasks (preview output above in S730/S735). It runs automatically at **01:00** as the Windows task `APS_MasterCron`.

---

## 0b · Push the local commits first (NEW — the real blocker)
As of 2026-11-26, `HEAD` is **9 commits ahead of `origin/main`** — including the SMTP fix, the Zen/OpenCode key resolver, the site-settings hardening, the RAG honesty fixes, and the AI-settings fixes. None of that has been CI-verified on the remote and none of it exists on the deploy server.

> **⚠️ A real `git push origin main` was observed rejected by GitHub Push Protection.** `git push --dry-run` succeeds — the secret scanner runs only on a real push, so the dry run is NOT evidence you can push.
>
> **Root cause (S770, verified with masked output):** the 9 local commits ADD full credential literals to `AGENTS.md` — 13 × `oc_sk_…` OpenCode keys, 3 × `AQ.Ab8…` Gemini keys, 3 × the Gmail App Password. They are copied into every later commit diff, so history cleanup is required. The worktree copy was redacted (prefix + `<REDACTED>`, 0 residual); **the commit objects still contain the secrets**, so redaction alone does not unblock the push.

**Choose one — all three are your decision, agents will not rewrite history or bypass protection unasked:**
1. **Rewrite the 9 local-only commits** to redact the secrets (they are ahead, never pushed — a rewrite + normal push is possible, but it IS history editing), then push;
2. **GitHub's Push Protection "allow secret" flow** for these already-known credentials, then push;
3. **Rotate every exposed credential at the provider** (the durable fix — OpenCode keys, Gmail App Password, Gemini key), then rewrite/push.

1. Check: `git status -sb` → `## main...origin/main [ahead 9]`
2. Verify local gates: `php testing/master_test_runner.php` (12/12) and the push hook's lint.
3. **Resolve the Push Protection block using one of the 3 options above**, then push: `git push origin main`.
4. Confirm CI: `gh run list --limit 3` — all green on the new HEAD SHA. **Do not deploy until this is green.**

> Until this is done, every deploy step below ships the OLD code, not the fixes.

---

## 1 · SMTP — FIXED (S759/S761) — historical diagnostic kept (5 min)
The old log showed **149 "EmailService: Send failed" in 24 h** (Gmail rejected the plain password / revoked app-password). Fixed: valid 16-char App Password written to `.env` (`SMTP_PASS`) and DB (`site_content`/`app_settings`), `EmailService` now falls back to DB config, app-password whitespace normalized, admin email tab writes back to `.env`, and test-email errors surface in the AJAX response. Live delivery verified to `apsdreamhomes44@gmail.com`.

If mail ever stops again:
1. `/admin/settings/smtp` → re-save the App Password (Google may revoke on security events)
2. Send one test from the same page — the error text now shows the real cause
3. Confirm the log has zero `Send failed` lines: `logs/php_error.log`

---

## 2 · Staging deploy — prove it off your own machine first (30 min)

1. `wire.ps1 -Stage staging` — this script does: git sanity → changed-file lint → master suite (12/12) → HTTP health probe (`/` 200, `/api/health` 200) → then refuses to deploy without `APS_DEPLOY_HOST`, `APS_DEPLOY_USER`, `APS_DEPLOY_PATH`.
2. Export the three variables (host + SSH user + absolute path on the staging server).
3. Re-run `wire.ps1 -Stage staging`.
4. Click through: `/` → colonies → a property → EMI calculator → contact — should render identically to local.
5. Check `/api/health` returns `{"status":"healthy","database":{"connected":true,...}}` (114 B).

**Rollback**: staging only, so the worst case is a bad deploy on a server nobody else uses. Fix forward or restore the previous commit; nothing on your local machine is touched.

---

## 3 · Production deploy (30 min, after staging passes)

One of these — your choice, pick one and stop:

**A. GitHub Actions (manual, owner-gated — the documented path)**
`docs/DEPLOYMENT.md` Appendix F is the exact runbook: Actions → **Deploy Production** → Run workflow. Requires `PROD_HOST`, `PROD_USER`, `PROD_SSH_KEY`, `PROD_DEPLOY_PATH`, `PROD_APP_URL`, `DOCKER_USERNAME`, `DOCKER_TOKEN` in repo secrets. Rollback = re-run the workflow against the previous SHA.

**B. Manual SSH (the 814-line guide's path)**
`docs/DEPLOYMENT.md` covers this from the top — Nginx + SSL + Docker Compose. Valid if A's secrets aren't ready yet.

**Verify**: `curl -f https://apsdreamhome.com/health` → 200. Then `/` byte-probe the same as staging.

---

## 4 · Play Console — the mobile app reaches real associates (15 min)

File is built and signed: `public/downloads/apsdreamhome-v1.2.10-release.aab` (1.2.10+13).

1. Play Console → Release → Production → Create new release
2. Upload the AAB
3. Release notes: mention S723/S733/S734 wins (associate portal bank accounts, compare screen, live lead-intent AI answers)
4. Submit for review (~1-3 days typical)

---

## 5 · Colony GPS coordinates (one half-day, any order)

The 5 colonies' coordinates in `colonies` are wrong (seeded, never walked). Punch-in GPS distances are meaningless until fixed.

1. Open `/admin/hrm/work-zones` on your phone
2. For each of the 5 active colonies, stand at the site and tap **Use Current Location**
3. Save. The mobile page reflects it immediately.

This cannot be done from a desk; it will take a site visit per colony.

---

## 6 · Legal desk — RERA numbers (1 conversation)

All 8 colony `rera_number` columns hold `PENDING-VERIFICATION` (intentionally). Until the Legal Desk provides real NOCs, the pages/receipts honestly say "RERA verification on request" — nothing fabricated. One email to the Legal Desk requesting colony-wise registration certificates unblocks every piece of public RERA copy at once.

---

## 7 · The one deferred decision

**Analytics-v2 TEST-HIST spikes** (20 synthetic backfilled users on 2026-11-05 creating a fake peak in the 30-day chart; `plot_bookings_created=20` on 2026-11-14). The honest options are:
- **A.** Keep the backfill as-is (the chart shows the raw truth and you know why the spike exists);
- **B.** Hand me a rule (e.g. "LP rows tagged TEST-HIST-* are excluded") and I'll filter them into a parallel view.
- **Do not silently delete the rows.** The S522/S523 restore rehearsal lives on those rows' existence.

---

## What is deliberately NOT on this page
- No new features. No visual refresh. No refactoring. The code is honest; adding surface area before launch is the fastest way to introduce a regression.
- No switching to a different shop before this proves itself on a real server.
- No babysitting `01:00 APS_MasterCron` — it works, and Task 18 will tell you in its log if it ever doesn't.

---

## If you read only one line
**Step 0b (resolve Push Protection, push the 9 local commits) — then Step 2 (staging).** SMTP (Step 1) is already fixed and verified; the urgent thing is that the fixes are still only on this machine, and a real push is currently blocked by GitHub Push Protection on secrets inside the commits (see Step 0b). Resolve that first → push → confirm CI green → stage → then production or Play Console. The rest can wait.

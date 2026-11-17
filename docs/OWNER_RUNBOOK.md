# APS Dream Home — Owner's Runbook (One Page)

> Everything agent-side is done and verified. The codebase is green:
> master suite 12/12 · all six CI workflows green · DB baseline byte-identical to S725's drop-snapshot.
> This page is the only document you need to get the project from "works on this machine" to "live".
> Do the steps in order. Each step has an exact command or link — no guessing.

---

## 0 · Before anything (2 minutes)
- [ ] C: drive: **21 GB free** — do not install anything except what this page says (D: has 393 GB and is where backups and Flutter SDK live).
- [ ] Confirm the master cron is wired: `php scripts/run_all_crons.php --mode=daily --dry-run` prints 19 DAILY tasks (preview output above in S730/S735). It runs automatically at **01:00** as the Windows task `APS_MasterCron`.

---

## 1 · SMTP — all outbound mail is silently failing today (5 min)
The log shows **149 "EmailService: Send failed" in 24 h**. Every EMI reminder, receipt, and lead alert is either lost or in spam.

1. `/admin/settings/smtp` in your browser
2. Re-save credentials (most likely: the Gmail/Workspace **app-password** was revoked or never filled)
3. Send one test from the same page
4. Confirm the log has zero `Send failed` lines for the next 24 h: `logs/php_error.log`

> **This fixes more user-visible functionality than any other 5 minutes you can spend.**

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
**Step 1 (SMTP).** Until that is fixed, nothing the app says in an email is trustworthy. After that, Step 2 (staging). After that, Step 3 or Step 4. The rest can wait.

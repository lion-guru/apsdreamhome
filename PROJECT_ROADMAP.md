# PROJECT ROADMAP — APS Dream Home (Overnight Autonomous Session 78)

> Created: 2026-08-26, Session 78. CEO/Senior-Arch level deep scan.
> **Rule: Work top-to-bottom. Check off items as completed. Never lose this file.**
> Run E2E (`node testing/visual_tests/E2E_MASTER_TEST.mjs` → 153/153) after every batch.

---

## ✅ COMPLETED THIS SESSION (before roadmap)

| # | Item | Commit |
|---|------|--------|
| 1 | SmartAI `/api/ai/chat` JSON body fix (Request::createFromGlobals content_type lowercase) | `9076e55d9` |
| 2 | PageController facade refactor — removed 84 duplicate methods, 7 sub-controllers | `9076e55d9` |
| 3 | Colonies view keys, news table name, helpers.php deprecation | `8536053a0` |
| 4 | Flutter legal pages (terms/legal-services/disclaimer/cancellation-policy) + resell-properties + user-agreements + tools-hub links | `7d8d9dc0d` |
| 5 | Google OAuth: skipCsrfProtection + COMPANY_REFERRAL_CODE → role-selection view; redirect flow verified live (302 → accounts.google.com) | `51612f879` |
| 6 | AI Voice health check verified: DB✅ Gemini✅ WhatsApp✅; Asterisk/Ollama/Whisper ❌ expected (Docker down) | verified |
| 7 | Release APK v1.2.1 built & deployed | `4eab17732` |
| 8 | Backend API gaps closed: Chat 5 endpoints, Agent Portal 11 endpoints (**MobileAgentApiController** NEW), Voice 8 aliases, Telecaller 2 (**MobileTelecallerApiController** NEW) | `e8c047b4e` |
| 9 | app_constants.dart: +100 endpoint constants, deduped | `e8c047b4e` |
| 10 | 8 agent Flutter pages (analytics/bookings/documents/follow-ups/properties/site-visits/my-team/rank-progress), zero analyzer errors | `ca385dd75` |
| 11 | Router wiring: 9 GoRoutes for agent portal | `b11ec36ef` |
| 12 | APK v1.2.2 rebuild blocker fixed: callLogEndpoint restored, colony_model freezed codegen fixed; APK 88.6 MB deployed | `7a6bf2f67` |

---

## PHASE 1 — SYSTEM-WIDE SMOKE & WORKFLOW VERIFICATION (tonight)

### 1A. AI surface smoke tests (script exists: temp `smoke_all_ai.php`)
- [ ] Recreate smoke script in `testing/smoke_all_ai.php` testing all 7 surfaces:
      SmartAI chat / WidgetBot / GeminiBot / VoiceAssistant / AsstChat / Recommendations / Analyze
- [ ] Fix any failures found (engine=groq/gemini expected)
- [ ] Verify `/api/ai/chat` still returns real AI reply after Request.php change

### 1B. Full business workflow E2E (register → payout)
- [ ] Customer register via API → login → browse properties → favorite → inquiry → booking → payment token → EMI schedule visible
- [ ] Associate register with referral → lead create → lead status pipeline → commission entry in `mlm_commission_ledger`
- [ ] Verify commission auto-trigger on payment (Session 44 fix) still works
- [ ] Air Login OTP flow end-to-end (email mode)
- [ ] Google OAuth complete-registration POST flow (role=customer path)

### 1C. Public page render audit (Playwright MCP or curl)
- [ ] All ~40 public pages return 200 and contain expected markers (no blank pages)
- [ ] Check homepage hero stats render, colonies grid renders 5 colonies
- [ ] Mobile-app page shows v1.2.2 / 88.6 MB

---

## PHASE 2 — DATABASE HEALTH AUDIT

- [ ] Row counts for core tables: users(191+), leads, plots(456), colonies(5), mlm_commission_ledger, bookings
- [ ] Orphaned FK check: bookings→plots, leads→users, ledger→users
- [ ] Verify tenant_id column exists on all 429 scoped tables (spot-check 20 random)
- [ ] Index check: leads(assigned_to,status), plots(colony_id,status), ledger(user_id,type)
- [ ] news table has published rows for public News page
- [ ] faqs.display_order populated (FAQ fix earlier relies on it)
- [ ] ai_settings row intact (Gemini/Groq/OpenRouter keys) — DO NOT print values

---

## PHASE 3 — CODE QUALITY SWEEPS (batch-fixable)

### 3A. PHP error log triage
- [ ] Sweep `logs/php_error.log`: categorize warnings/deprecations by file
- [ ] Fix top recurring deprecations (strpos null etc.) — pattern from helpers.php fix
- [ ] Target: zero new warnings during E2E run

### 3B. Dead route/view spot audit
- [ ] Pick 30 random admin routes → confirm view exists + controller method exists
- [ ] Any broken → fix or archive per 7-step checklist

### 3C. Flutter analyzer debt (pre-existing, NOT ours)
- [ ] 51 analyzer errors in legacy models: booking_model, daily_caller_model,
      emi_automation_model (freezed classes need same redirecting-factory fix as colony_model)
- [ ] Apply `_preprocessXJson` helper pattern OR add missing `.g.dart` parts
- [ ] Re-run build_runner → target <10 total errors

---

## PHASE 4 — FEATURE POLISH (high user value)

- [ ] Agent dashboard quick-actions grid: link the 8 new pages (/agent/analytics etc.) so they're tappable
- [ ] Profile page "More Features": add My Team / Rank Progress links if missing
- [ ] Tools Hub: verify all 22+ tool tiles resolve (no dead taps)
- [ ] Notification bell unread count syncs across portals (admin/employee/agent layouts)

---

## PHASE 5 — INFRA & LOCAL TOOLS

- [ ] `agentic_dev_system/py_agentic/main.py --cycles 1 --skip-e2e` runs without crash (local Ollama qwen2.5-coder)
- [ ] cron scripts syntax pass: `for f in scripts/cron_*.php; php -l`
- [ ] WebSocket server file exists + port config sane (websocket_server.php)
- [ ] Docker telephony compose file valid YAML (docker/asterisk/) — no runtime test needed

---

## PHASE 6 — DOCUMENTATION

- [ ] Update AGENTS.md: Session 78 summary (API gap closure, agent pages, APK fixes)
- [ ] This file: mark completed phases, carry-forward list

---

## CARRY-FORWARD (explicitly deferred — do NOT silently drop)

| Item | Why deferred | Where recorded |
|------|--------------|----------------|
| Controller tenant_id scoping (379 ops / 105 files) | Mitigated by enforceTenantStatus(); needs per-file analysis | AGENTS.md Session 59 |
| Asterisk/Ollama/Whisper Docker stack | Requires Docker Desktop running + USB modem hardware | Session 53 docs |
| Groq orpheus TTS terms acceptance | Needs org-admin click at console.groq.com | AGENTS.md AI section |
| OpenRouter 50 req/day limit | Chain falls through to Groq/Gemini automatically | AGENTS.md AI section |
| iOS Flutter app | Out of scope | mobile_app.php FAQ |

---

## COMMANDS QUICK REFERENCE

```bash
# E2E gate (MUST be 153/153)
node testing/visual_tests/E2E_MASTER_TEST.mjs

# PHP lint changed file
php -l <file>

# DB query (PowerShell-safe quoting)
$env:MYSQL_PWD='...'; & 'C:\xampp\mysql\bin\mysql.exe' -h 127.0.0.1 -P 3307 -u root apsdreamhome --batch -e "..."

# Flutter analyze only errors
flutter analyze lib/ 2>&1 | Select-String " error "

# Build + deploy APK
cd mobile/apsdreamhome_app_v2; flutter build apk --release
Copy-Item android\app\build\outputs\apk\release\app-release.apk ..\..\public\downloads\apsdreamhome.apk

# Codegen models
dart run build_runner build
```

## KEY SESSION-78 LESSONS (append to AGENTS.md later)

1. **ParameterBag headers are case-sensitive** — getHeaders() lowercases HTTP_* keys;
   must read `$request->headers->get('content_type')` not 'CONTENT_TYPE'. Root cause of
   SmartAI empty-body bug.
2. **freezed requires redirecting factory for FromJson** — a full-body `fromJson`
   factory blocks _$XxxFromJson generation. Extract preprocessing into a top-level
   helper and use `factory X.fromJson(json) => _$XFromJson(_preprocess(json));`
3. **Deduplicating constants can break silent dependents** — always grep
   `AppConstants.<name>` across lib/ BEFORE removing any constant.
4. **Dart records `(String, int, IconData, Color)` beat helper classes** for local
   widget data lists — no duplicate class definitions possible.

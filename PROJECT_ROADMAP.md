# PROJECT ROADMAP — APS Dream Home (Overnight Autonomous Session 78–79)

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

## PHASE 1 — SYSTEM-WIDE SMOKE & WORKFLOW VERIFICATION (tonight) ✅ DONE

### 1A. AI surface smoke tests ✅
- [x] `testing/smoke_all_ai.php` — **7/7 PASS** (SmartAI engine=rag / WidgetBot / GeminiBot / VoiceAssistant real answer / AsstChat Hindi reply / Recos count=8 / Analyze property_id=1)

### 1B. Full business workflow E2E ✅
- [x] `testing/workflow_probe.php` — **15/15 PASS**: login → properties(10) → favorite add+list → inquiry persisted(id=5) → colonies(5) → dashboard → notifications → payment-history → profile; DB: customers=55, colonies=5, plots=425, ledger=332, orphaned FKs=0

### 1C. Public page render audit ✅ (covered by E2E Step 5 + manual /colonies probe with zero new warnings)

---

## PHASE 2 — DATABASE HEALTH AUDIT ✅ DONE
- [x] published_news=5, active_faqs=6, leads=29121, bookings=8
- [x] Orphaned: leads→users=0, plots→colonies=0, bookings→plots=0
- [x] users missing tenant_id = 0
- [x] ai_settings keys intact (verified in earlier session probes)

---

## PHASE 3 — CODE QUALITY SWEEPS

### 3A. PHP error log triage ✅ DONE
- [x] Top offender fixed: colonies.php `$colony_stats` undefined (211 warnings/load) → stats block restored in PropertyPageController; /colonies now logs ZERO bytes
- [x] strpos null deprecation in colonies.php image path → guarded
- [x] Remaining: helpers.php strpos deprecations are low-frequency (guarded at source already); acceptable

### 3B. Dead route/view spot audit — covered by E2E (90 admin routes 200 OK) ✅

### 3C. Flutter analyzer debt ✅ DONE (Session 78 night, commit `756acd96c`)
- [x] Root cause identified: freezed 3.x requires `abstract class` for the `_$X` mixin pattern
- [x] Added `abstract` to 43 @freezed classes across 8 legacy models (booking, daily_caller,
      emi_automation, emi_collection, gamification, notification, property_listing, site_visit)
- [x] Fixed chat_service.dart dynamic-list conversion + live_chat_page.dart null-safe date parse
- [x] build_runner regenerated 24 outputs; codegen synced
- [x] **flutter analyze lib/: ZERO errors** (was 51), 286 style infos only
- [x] APK rebuilt + deployed; all gates re-verified green

---

## PHASE 4 — FEATURE POLISH ✅ DONE (Session 80)
- [x] Agent dashboard quick-actions: 3-row grid wiring all 8 new pages
- [x] Profile page links — `_AgentFeaturesSection` 8 items (Analytics/Bookings/Documents/Follow-ups/Properties/Site Visits/My Team/Rank) in `profile_page.dart:396`
- [x] Tools Hub tiles verified via router analysis earlier
- [x] Notification bell sync — polling 30s (`notification-system.js`) + WebSocket `ws://localhost:8080` daemon doc `scripts/run_websocket_daemon.bat` (optional in prod, health_check pass)

---

## PHASE 5 — INFRA & LOCAL TOOLS ✅ DONE
- [x] Cron lint: scripts/cron_*.php + cron/*.php → 0 errors
- [x] Agentic system (E:\coding-assistant): FIXED Windows cp1252 UnicodeDecodeError (shell.py subprocess encoding='utf-8', errors='replace'); verified cycle 1 completes cleanly
- [x] docker/asterisk compose YAML valid
- [x] Ollama offline = documented expected state

---

## PHASE 6 — DOCUMENTATION
- [x] This file updated with results
- [x] AGENTS.md Session 78 entry added
- [x] Session 79: schema sweep 261→2 + tracking fix + tenant gap 63→3 + cross-port verify + pubspec 1.2.2 + health_check.php (AGENTS lessons 172-179)
- [x] Session 80: lib restore cd9489e99 (0-byte fix), MySQL db.MAD retore, health_check CRLF, profile Agent section, analyzer 297→131, APK 92.9MB (lessons 180-184)

---

## ✅ SESSION 79 COMPLETED (2026-08-26)

| # | Item | Commit |
|---|------|--------|
| 13 | Schema scanner NEW `testing/scan_schema_mismatches.php` — 261 mismatches (627 tables) | `c374ae205` |
| 14 | Batch 1: 220 fixes (111 files) + Batch 2: 39 fixes (31 files) → 261→2 false positives | `c374ae205`+`7eaa40dc2` |
| 15 | Release APK v1.2.2 rebuilt 92.9 MB | local |
| 16 | Visitor tracking CSRF fix `skipCsrfProtection()` + `/track/` prefix | `e9c3b8021` |
| 17 | Tracking tables created `visitor_sessions`+`visitor_page_views` + migration | `e9c3b8021`/`6d65b4b8c` |
| 18 | `trackInterest` 500 fix (service method added) | `e9c3b8021` |
| 19 | `whatsapp_click_log` table + SMS graceful skip (DLT) | `8ad38cf2b` |
| 20 | Tenant gap sweep: 1403 ops → 63 → 3 (9 controllers, 20 writes) | `5ffb20f94` |
| 21 | WebSocket daemon `scripts/run_websocket_daemon.bat` | `5c8ff604d` |
| 22 | pubspec `1.0.0+1→1.2.2+1` + doc sync 626/458/483/3766 + health_check.php | `cd9489e99`+`7a10fac01`+`e8feea68a` |
| 23 | E2E 153/153, AI 7/7, Workflow 15/15, Associate 12/12, scanner CLEAN, midfile-use CLEAN | verified |

## ✅ SESSION 80 COMPLETED (2026-08-28)

| # | Item | Commit |
|---|------|--------|
| 24 | Flutter lib restore — HEAD 0-byte (PowerShell `>` UTF-16LE) → `git checkout cd9489e99 -- lib/ pubspec.yaml` 309 files 140k insert | `c7c70bc34` |
| 25 | widget_test fix `aps_dream_home/app.dart` → `apsdreamhome_app_v2/app.dart` | `c7c70bc34` |
| 26 | Release APK v1.2.2 92.9 MB rebuilt (485s) `flutter-apk/` + `apk/release/` → `public/downloads` | local |
| 27 | MySQL Aria `db.MAD Incorrect file format` → `backup/mysql/db.*` restore + `mysqladmin password 2jcePXuNaOfEyo6I5wJVkG` 628 tables | — |
| 28 | health_check CRLF `1.2.2+1\r` fix `trim($m[1])` → `ok:true` | `1c0a6a15b` |
| 29 | Analyzer `dart fix --apply` 143 fixes 297→131 (0 errors) | local |
| 30 | Profile `_AgentFeaturesSection` 8 items (profile_page.dart:396) + bell sync doc | local |
| 31 | E2E 153/153, smoke 7/7, workflow 15/15, health ok:true | verified |

---

## CARRY-FORWARD (explicitly deferred — do NOT silently drop)

| Item | Why deferred | Where recorded |
|------|--------------|----------------|
| Controller tenant_id scoping (379 ops / 105 files) | **DONE Session 79**: recount 1403→63→3 (9 controllers, 20 writes) — system tables correctly skipped | AGENTS.md Session 79 |
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

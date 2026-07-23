# APS Dream Home — Mobile × Website Sync Tracker

## Session: 2026-06-20 — Multi-Agent Parallel Sprint

### Current State Summary
- **Mobile App**: `apsdreamhome_app_v2/` — Flutter 3.44, Riverpod, GoRouter, SQLite offline, Dio API, 58+ screens
- **Website**: Custom PHP MVC — 130+ admin controllers, 492+ views, 767 DB tables, 1700+ routes
- **Native Android**: WebView wrapper alternative exists (commit `f1a96b98a`)

### Priority Queue (Overall Project)

#### 🔴 P0 — Must Do This Week
| # | Task | Status | Agent | Notes |
|---|------|--------|-------|-------|
| 1 | Firebase Cloud Messaging (FCM) — Mobile FCM + Web VAPID | IN_PROGRESS | agent-fcm-1 | firebase_messaging plugin, VAPID keys, PushNotificationService.php |
| 2 | Admin Mobile App — 20 condensed screens from 130 website controllers | IN_PROGRESS | agent-admin-2 | Analytics, Approvals, UserMgmt, ColonyMgmt, Reports |
| 3 | Agent/Associate Mobile — Deal Pipeline, Lead Kanban, Commission Approval | IN_PROGRESS | agent-agent-3 | Kanban board, deal CRUD, commission approve/reject |
| 4 | Deal Pipeline Mobile Screens | IN_PROGRESS | agent-deal-4 | Full kanban stage management for agents |

#### 🟡 P1 — This Week
| # | Task | Status | Notes |
|---|------|--------|-------|
| 5 | EMI/Booking Sync — Mobile writes match website schema | PENDING | Verify booking_payment_schedules, plot_bookings columns |
| 6 | MLM Commission Engine — Write from mobile (approve/payout) | PENDING | HybridCommissionEngine integration |
| 7 | Accounting/Finance mobile read-only → approval flows | PENDING | MoneyWorkflowService for mobile |
| 8 | Reports Export (PDF/Excel) on mobile | PENDING | Use existing ReportController |
| 9 | FCM Complete (both platforms talking) | PENDING | After agent-1 finishes |
| 10 | Biometric Auth (fingerprint/face) on mobile | PENDING | local_auth package |

#### 🟢 P2 — Next Week
| # | Task | Status | Notes |
|---|------|--------|-------|
| 11 | HR/Payroll Employee mobile app | PENDING | Attendance, Leave, Payslip |
| 12 | Marketing Campaigns mobile | PENDING | Read campaigns, basic create |
| 13 | Auction mobile screens | PENDING | View, bid, watch |
| 14 | Legal/NOC/RERA mobile | PENDING | Read-only compliance view |
| 15 | Live Chat mobile enhancement | PENDING | WebSocket integration |
| 16 | Multi-language (Hindi/English) mobile | PENDING | Use existing lang/en.php, hi.php |

#### 🔵 P3 — Future
| # | Task | Status | Notes |
|---|------|--------|-------|
| 17 | AR/VR upgrades (WebXR + ARCore) | PENDING | Already have AR plot overlay |
| 18 | Blockchain/NFT deeds mobile view | PENDING | Website has blockchain module |
| 19 | Sustainability calculator mobile | PENDING | Website has full module |
| 20 | Voice AI full integration | PENDING | Already have VoiceToLead page |

---

## Phase 1: Core Parity (Weeks 1-2)

### 1A — Firebase Cloud Messaging (FCM)
**Files to modify:**
- `mobile/apsdreamhome_app_v2/pubspec.yaml` — add firebase_messaging
- `mobile/apsdreamhome_app_v2/android/app/build.gradle` — update dependencies
- `mobile/apsdreamhome_app_v2/lib/core/services/push_notification_service.dart` — FCM init + handlers
- `app/Services/PushNotificationService.php` — VAPID keys, web push, FCM HTTP v1 API
- `app/views/layouts/base.php` — service worker registration
- `public/sw.js` — push event listener

**Verification:** Send test push from admin → receive on mobile + desktop browser

### 1B — Admin Mobile App (Condensed)
**Screens to create in `mobile/apsdreamhome_app_v2/lib/presentation/pages/admin/`:**
- `analytics_dashboard_page.dart` — 6 KPI cards + 4 Chart.js-like charts (use fl_chart)
- `booking_approvals_page.dart` — List + approve/reject buttons
- `commission_approvals_page.dart` — List + approve/reject
- `colony_management_page.dart` — Colony list + quick stats
- `plot_management_page.dart` — Plot inventory + status filters
- `employee_management_page.dart` — Employee list + actions
- `user_management_page.dart` — User list + role filter
- `reports_page.dart` — Report list + generate/export
- `crm_page.dart` — Lead pipeline + activity log
- `bulk_marketing_page.dart` — Campaign create + send

**API endpoints needed (in MobileApiController.php):**
- `reports/summary` — aggregated admin stats
- `bookings/pending-approvals` — bookings awaiting approval
- `commissions/pending-approvals` — commissions awaiting approval
- `employees/list` — employee directory
- `users/list` — user directory with roles
- `analytics/dashboard` — real-time admin dashboard stats

### 1C — Agent/Associate Mobile Enhancement
**Screens to create/update:**
- `deal_pipeline_page.dart` — Kanban board with drag-and-drop stages
- `lead_kanban_page.dart` — Lead Kanban with filters
- `commission_approval_page.dart` — Approve/reject commissions
- `genealogy_page.dart` — Enhanced tree view with search

**API endpoints:**
- `deals/list` — agent deals list
- `deals/update-stage` — move deal between stages
- `leads/list` — agent lead list with kanban stages
- `leads/update-stage` — move lead between stages
- `commissions/list` — commission ledger
- `commissions/approve` — approve commission
- `commissions/reject` — reject commission

---

## Phase 2: Role-Based Mobile (Week 3-4)

### 2A — Employee Mobile App
### 2B — Telecaller Dashboard
### 2C — HR/Finance Mobile

---

## Phase 3: Advanced Features (Week 5-6)

### 3A — Live Chat WebSocket
### 3B — Video Call (WebRTC)
### 3C — AI Property Valuation
### 3D — Virtual Tour 360
### 3E — Multi-language i18n

---

## Known Conflicts / Issues Found During Analysis

### 🔴 Critical
- **MobileApiController.php (3434 lines)** — Bloated, needs refactoring into separate controllers
- **Mobile uses `assets/images/` logo paths** — Verify images exist in Flutter assets dir
- **FCM not configured** — `firebase_messaging` not in pubspec.yaml, no `google-services.json`
- **Deep link conflict** — `app_links` package incompatible with AGP 8.7+
- **PhonePe/UPI payment stubs** — Not wired to real backend Razorpay service

### 🟡 Medium
- **Riverpod providers not used in many screens** — Some screens use raw StatefulWidget instead of Provider pattern
- **GoRouter paths need verification** — Ensure all mobile routes match backend API patterns
- **Offline queue conflict resolution** — Edge cases with concurrent sync not handled
- **SQLite schema version not tracked** — No migration strategy for local DB

### 🟢 Minor
- **Dart model <> PHP model field mismatch** — Some fields differ in naming convention (snake_case vs camelCase)
- **No shared API response envelope** — MobileApiController returns various structures
- **No loading skeletons** — Many screens show CircularProgressIndicator instead of shimmer
- **Empty state design** — Some lists show nothing when empty

---

## Session Log

### 2026-06-20 Session 1: Multi-Agent Parallel Sprint
**Agents launched:**
1. `agent-fcm-1` — Firebase Cloud Messaging setup (FCM plugin, VAPID, PushNotificationService)
2. `agent-admin-2` — Admin mobile app screens + API endpoints
3. `agent-agent-3` — Agent/Associate mobile screens + API endpoints
4. `agent-deal-4` — Deal pipeline mobile screens + API endpoints

**Completed:**
- [ ] FCM configured on mobile
- [ ] FCM configured on website
- [ ] Admin analytics dashboard screen
- [ ] Admin booking approvals screen
- [ ] Admin commission approvals screen
- [ ] Admin user/employee management screens
- [ ] Agent deal pipeline screen
- [ ] Agent lead kanban screen
- [ ] Deals API endpoints
- [ ] Leads kanban API endpoints

**Pending for next session:**
- [ ] EMI/Booking schema sync verification
- [ ] MLM Commission mobile write flow
- [ ] Accounting mobile approval flows
- [ ] FCM end-to-end test
- [ ] APK build test

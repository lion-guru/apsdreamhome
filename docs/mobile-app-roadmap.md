# Mobile App — Offline Mode, Role Readiness & Freelancer Roadmap

Status: **Planning document, captured 2026-11-18.** Source of truth for what
the mobile app currently supports and what is under owner discretion before
testers/rollout.

## 1. Offline mode — current capability (audited)

The app ships a genuine offline-first architecture, not a cosmetic offline
banner. What exists today:

- `lib/core/services/database_helper.dart` — `DatabaseHelper` (SQLite) with
  read/write helpers over generic tables, including `sync_queue` and a
  cache-first data layer. `connectivity_plus` is the network source.
- `lib/core/services/sync_service.dart` — `SyncService` uploading pending
  changes from `sync_queue`, downloading latest, maintaining
  `last_sync_time`.
- `lib/core/services/async_job_queue_service.dart`, `background_sync_service.dart`
- `lib/data/repositories/property_repository.dart` — reads offline-first
  ("returns cached data immediately"), refreshes cache on reconnect, falls
  back to cache on API failure. **Verified pattern.**
- `lib/data/repositories/lead_repository.dart` — offline-first lead create
  (`isOfflineCreated: true`), syncs when online. **Telecalling works offline.**
- `lib/core/services/api_service.dart` — `isConnected()` gate; EMI fetch
  reads from cache first (`storage.cachedEmiData`).
- `OfflineEmiResult` returns `fromCache` status; `test/offline_widgets_test.dart`
  covers offline indicators/widgets.
- `lib/core/widgets/offline_indicator.dart` — UI-level offline awareness
  utilities in use across admin/associate/customer shells.
- `voice_to_lead_page.dart`, `telecaller_dashboard_page.dart` carry offline
  affordances for lead entry paths.

**What this means in one line:** a lead typed on a train with no signal
goes into the local queue and syncs on reconnect. Properties/EMI read
offline from cache. That is genuinely useful.

### Gaps (honest)

- No dedicated "work offline" UX cues beyond the EMI tracker and the EMI
  cache (cache miss → spinner → suggests connect).
- No restriction/blocking — a user making a payment offline is not the same
  story as browsing; wallet/payment paths still require connectivity.
- Behavior on mid-range devices with large caches needs testing on a real
  device, not only emulation.
- No per-sync audit channel visible to the owner yet — owner-review gate
  needed before release sign-off.

## 2. Role readiness matrix (before handing to testers)

| Role | Backend API | App screens | Device verified | Recommended |
|---|---|---|---|---|
| Admin | ✅ incl. DB-driven RBAC menu | ✅ | ✅ on-device 2026-11-17 | Ready for internal smoke |
| Associate | ✅ + full CRM | ✅ | ✅ real-time RBAC fix landed (S656-S660) | Internal smoke then field test |
| Agent | ✅ + deals/commissions | ✅ | partial (deep flows unverified) | Internal smoke |
| Employee | ✅ + self-service | ✅ | API-level verified; UI unverified | Internal smoke only |
| Customer | ✅ + passbook/EMI | ✅ | partial | After a 30-min smoke |
| Guest | ✅ | ✅ | ✅ on-device | Ready |
| Telecaller | ✅ (web) | ✅ | unverified | Internal only |
| Freelancer / student lead-gen | ❌ NOT BUILT | ❌ | — | Roadmap |

Honest verdict for release: **no public release yet.** Hand to internal
smoke testers only, with explicit briefing on current limits (wallet
requires connectivity; EMI tracker shows from-cache mode with stale badge;
some flows crash-chain closed but unverified by humans).

## 3. Website / parity note

The owner wants website and mobile to reach feature parity. The web stack
ships some features the mobile cannot ship yet (owner-signed MLM
activation, call-recording pipelines) and the app ships several things the
website has not (DB-driven RBAC menu sync, GPS check-in, voice-to-lead).
Sequence parity by customer value, not feature count. Do not retro-fit the
whole list into one sprint.

## 4. Freelancer / student lead-gen — backlog (documented, not shipped)

Features the owner asked for explicitly:

- Lead-add flow optimized for students — tap-to-add, voice-to-lead, trivial
  capture — `voice_to_lead_page.dart` exists as a starting point.
- Rewards/commission when a lead converts — reuse the MLM commission
  pathway; **do not** duplicate `mlm_commission_ledger` with a parallel
  freelancer table. Commission flow must stay single-source.
- Wallet payouts below/within threshold via a light KYC gate to enable
  small but real earnings for freelancers.
- Ad-supported free tier — partner with AdMob/AdSence when production is
  stable; vendor go/no-go stays owner's call.
- Gamification — badges on leads submitted, streaks, leaderboards — map to
  existing `wallet_points` mechanics; do not invent a separate
  `gamification_points` ledger yet.

### Implementation order

1. Lead-create API + app flow (~55% present today)
2. Reward issuance on verification (existing machinery, needs a throttle)
3. Wallet payout flow (KYC reflow, owner decision)
4. Monetization via ads (owner business decision, not engineering decision)

## 5. Next decision gates (owner)

- Release sign-off tree after a real smoke pass (owner picks testers)
- Production deploy sequence after RERA sign-off
- Freelancer program business rules (reward ladder, payout threshold)
- KYC reflow scope (how light is "enough" for the freelancer tier?)
- Play Console upload of current AAB + assetlinks SHA-256 (owner action)

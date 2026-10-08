# Marketplace System — Deployment Guide

## 1. Overview

The marketplace system adds resale/user-property trading on top of the existing
APS Dream Home platform:

| Area | What |
|------|------|
| Referral | Optional for customer, mandatory for associate/agent; auto-fill from `?ref=` / `aps_ref` cookie; sponsor name verification via `/api/user/resolve-sponsor` |
| Social linking | Profile page link/unlink Google/Facebook/LinkedIn; password recovery via social |
| Marketplace | Free listings, buyer interest tracking, shortlist, lead capture, follow-ups, deal closure with commission |
| Monetization | Property boost (₹299/499/999 via Razorpay), builder subscriptions (Starter/Pro/Enterprise), platform fee 0.75–1.5% per deal |
| Admin | Marketplace dashboard (listings, revenue, builders), verify/approve/sold workflow |
| Mobile | 8 marketplace API endpoints (saved, followups, transactions, boost payment) |
| Notifications | 18 templates (boost, transaction, followup, builder subscription) |

## 2. Database Migrations (run in order)

```bash
cd C:\xampp\htdocs\apsdreamhome

# 1. Transaction tables (resell_transactions, property_transactions + boost columns)
php database/migrations/create_resell_transactions.php

# 2. Marketplace tables (saved, followups, builder packages, revenue, verification, commissions, interest logs)
php database/migrations/create_marketplace_tables.php

# 3. Notification templates (18 marketplace templates)
php database/migrations/add_marketplace_notification_templates.php
```

Verify:

```bash
php testing/master_test_runner.php
# Expected: 7/7 PASS
```

## 3. New Tables

| Table | Purpose |
|-------|---------|
| `user_saved_properties` | Buyer shortlist (UNIQUE user+property+type) |
| `followup_schedules` | Automated follow-ups (call/whatsapp/email/sms/site_visit) |
| `builder_packages` | Starter/Professional/Enterprise (seeded) |
| `builder_subscriptions` | Active builder subscriptions |
| `platform_revenue` | All revenue (boost, transaction fees, subscriptions) |
| `property_verification_logs` | Verification audit trail |
| `associate_resale_commissions` | Associate/agent commission tracking |
| `property_interest_logs` | Granular buyer signals (view/save/share/inquire) |
| `resell_transactions` | Completed resell deals |
| `property_transactions` | Completed user_property deals |

New columns on `user_properties`: `boosted_at`, `boost_expires_at`,
`boost_amount`, `promoted_until`, `transaction_id`.
New column on `resell_properties`: `transaction_id`.
New column on `resell_commission_structure`: `platform_fee_pct`.

Default commission structure is seeded (plot/house/flat/shop/commercial,
tiered 1.0–2.0% referral + 0.75–1.5% platform fee).

## 4. New Files

### Services
- `app/Services/MarketplaceService.php` — interest tracking, save/unsave,
  lead capture, followups, verification, analytics, `getBoostAmount()`,
  `applyBoostAfterPayment()` (shared boost logic)
- `app/Services/ResellTransactionService.php` — deal closure, commission
  calculation, buyer's-referrer-only payout, `recordAssociateCommission()`
- `app/Services/BuilderSubscriptionService.php` — packages, subscriptions,
  listing limits, builder dashboard

### Controllers
- `app/Http/Controllers/Front/MarketplaceController.php` — web marketplace
  (index, detail, track-interest, toggle-save, saved, capture-lead,
  followups, complete-followup, close-deal, transactions, boost + Razorpay)
- Extended: `Api\MobileUserApiController` — 8 marketplace mobile endpoints
- Extended: `Admin\AdminMarketplaceController` — transaction/revenue stats,
  builder stats, boost transactions, top builders

### Views
- `app/views/pages/user/saved_properties.php`
- `app/views/pages/user/followups.php`
- `app/views/pages/user/transactions.php`
- Extended: `app/views/admin/marketplace/index.php` (revenue + builder panels)

### Routes
Web (`routes/web.php`):
- `GET /marketplace`, `GET /marketplace/{id}`
- `POST /marketplace/track-interest`, `POST /marketplace/toggle-save`
- `GET /user/saved-properties`, `POST /marketplace/capture-lead`
- `GET /user/followups`, `POST /marketplace/complete-followup`
- `POST /marketplace/close-deal`, `POST /marketplace/close-user-property-deal`
- `GET /user/transactions`
- `POST /marketplace/boost-property` (legacy direct)
- `POST /marketplace/boost/initiate-payment`, `POST /marketplace/boost/verify-payment`

Mobile (`routes/api.php`, all with `ApiAuthMiddleware`):
- `GET /api/v2/mobile/marketplace/saved-properties`
- `POST /api/v2/mobile/marketplace/save-property`
- `DELETE /api/v2/mobile/marketplace/saved-property/{id}`
- `GET /api/v2/mobile/marketplace/followups`
- `POST /api/v2/mobile/marketplace/followup/{id}/complete`
- `GET /api/v2/mobile/marketplace/transactions`
- `POST /api/v2/mobile/marketplace/boost/initiate-payment`
- `POST /api/v2/mobile/marketplace/boost/verify-payment`

## 5. Environment

Razorpay (uses existing `Gateway\RazorpayService` — no new keys needed):

```env
RAZORPAY_KEY_ID=rzp_test_xxxx
RAZORPAY_KEY_SECRET=xxxx
RAZORPAY_WEBHOOK_SECRET=xxxx
RAZORPAY_TEST_MODE=true   # false in production
```

The boost flow works in test mode (mock orders) without real keys.

## 6. Verification

```bash
# PHP lint (all touched files)
php -l app/Services/MarketplaceService.php
php -l app/Services/ResellTransactionService.php
php -l app/Services/BuilderSubscriptionService.php
php -l app/Http/Controllers/Front/MarketplaceController.php
php -l app/Http/Controllers/Admin/AdminMarketplaceController.php
php -l app/Http/Controllers/Api/MobileUserApiController.php
php -l routes/web.php
php -l routes/api.php

# Master suite
php testing/master_test_runner.php
# Expected: 7/7 PASS
```

Manual smoke:
- `GET /register` → 200 (role selection)
- `GET /marketplace` → 200 (listing page)
- `GET /resell-properties` → 200
- `GET /user/saved-properties` → 302 (login redirect when logged out)
- `GET /api/user/resolve-sponsor?code=INVALID123` → 200 `{"success":false,...}`

## 7. Rollback

Migrations are idempotent (`CREATE TABLE IF NOT EXISTS`, `INSERT IGNORE`,
column-exists checks). To roll back code only:

```bash
git log --oneline -5
git revert <commit-hash>
```

To drop marketplace tables (CAUTION — destroys data):

```sql
DROP TABLE IF EXISTS property_interest_logs, associate_resale_commissions,
  property_verification_logs, platform_revenue, builder_subscriptions,
  builder_packages, followup_schedules, user_saved_properties,
  property_transactions, resell_transactions;
ALTER TABLE user_properties
  DROP COLUMN boosted_at, DROP COLUMN boost_expires_at,
  DROP COLUMN boost_amount, DROP COLUMN promoted_until,
  DROP COLUMN transaction_id;
ALTER TABLE resell_properties DROP COLUMN transaction_id;
```

## 8. API Docs

Full OpenAPI 3.0 spec: `docs/marketplace-api.yaml`
- 13 web endpoints, 8 mobile endpoints, 1 referral API, 1 admin page
- Import into Swagger UI / Postman via the YAML file.

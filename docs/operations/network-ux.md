# MLM Network UX Enhancements – Phase 4

This document captures the new rank-driven user experience and the admin tooling introduced in Phase 4.

## Key Features

1. **Seven-tier Rank System**
   - Associate, Sr. Associate, BDM, Sr. BDM, Vice President, President, Site Manager.
   - Thresholds based on lifetime business volume (₹0 → ₹5 Cr+).
   - Rewards (mobile → car) displayed in user dashboards.
   - `tools/mlm_rank_recalculate.php` updates ranks; run nightly.

2. **User Network Dashboard** (`/dashboard`)
   - Rank badge with reward and plan indicator.
   - Progress bar and copy explaining how much business is left for the next rank.
   - Tree filter by search + rank, CSV/PNG export, rank-colored nodes.
   - Totals now include rank-specific stats sourced from `ReferralService`.

3. **Admin Network Inspector** (`/admin/network/inspector`)
   - Search associates, inspect trees with depth/rank filters, quick actions.
   - Manage custom commission agreements (create/update/delete) via modal.
   - CSV/PNG export of filtered tree.
   - Quick links: profile view, notification log, network rebuild trigger.

4. **Commission Agreements**
   - `mlm_commission_agreements` table stores custom percentage/flat deals.
   - Managed via admin inspector APIs (`/admin/network/agreements/*`).
   - `plan_mode` on `mlm_profiles` indicates whether rank or custom payouts apply.

## Operations Checklist

### Daily
- Run `php tools/mlm_rank_recalculate.php` (or ensure cron executes it).
- Review `mlm_notification_log` for failures (pending thresholds now tied to ranks).

### After Onboarding a Partner with Custom Deal
1. Open `/admin/network/inspector` → search associate.
2. Create a commission agreement with percentage or flat amount (optionally scoped to property ID).
3. Confirm the agreement appears in the table and that `plan_mode` switches to `custom`.

### Network Troubleshooting
- **Rebuild:** use the "Rebuild Network" button (POST `/admin/network/rebuild`).
- **Tree Export:** download CSV/PNG and share with field managers.
- **Notification Log:** link redirects to existing admin log tooling for quick diagnostics.

## Validation Steps (Phase 4)
1. Seed rank data: `php tools/mlm_rank_recalculate.php`.
2. Login as an associate → verify rank badge, reward text, progress meter.
3. Use search + rank filter on network tree → ensure nodes fade when filtered out.
4. Export CSV/PNG → confirm file contains rank info and correct hierarchy.
5. Access admin inspector → search user → ensure tree and agreements load.
6. Create custom agreement → reload → confirm entry persists.
7. Rebuild network (test on staging) → ensure success message returned.

## References
- Services: `ReferralService` (rank data), `RankService`, `CommissionAgreementService`.
- Views: `app/views/user/network_dashboard.php`, `app/views/admin/mlm_network_inspector.php`.
- Routes: see `app/core/routes.php` under admin section.

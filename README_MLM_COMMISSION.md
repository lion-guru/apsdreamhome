# MLM Commission System Documentation

## Overview
This project implements a flexible, rules-based MLM (Multi-Level Marketing) commission system. It allows a super admin to configure the company’s commission share and distribution plan, and automatically tracks and displays commission earnings for all associates.

---

## Features
- **Super Admin Plan Settings:**
  - Set company’s max commission share (e.g., 25%)
  - Set commission percent for up to 10 levels (e.g., Level 1: 10%, Level 2: 5%, ...)
  - Admin UI: `/admin/mlm_commission_settings.php`
- **Dynamic Commission Calculation:**
  - Logic in `/includes/functions/mlm_commission_logic.php` uses plan settings for every transaction
- **Commission Payout Ledger:**
  - Table: `mlm_commission_ledger` records every commission payout (by transaction, associate, level, amount)
  - Logic in `/includes/functions/mlm_commission_ledger.php`
- **Associate Reports:**
  - `/admin/associate_commission_report.php?aid=ASSOCIATE_ID` shows detailed commission history for any associate
- **Admin Dashboard Widget:**
  - `/admin/dashboard.php` includes a card showing total commission distributed and top 5 earners

---

## How It Works
1. **Configure Plan:**
   - Super admin sets company share and level-wise distribution in `/admin/mlm_commission_settings.php`.
2. **Record Transaction:**
   - When a business transaction is added, call:
     ```php
     recordCommissionPayouts($con, $transaction_id, $sale_associate_id, $amount);
     ```
   - This will calculate and record commission for all uplines as per the plan.
3. **View Earnings:**
   - Associates and admins can view commission earnings and payout history via the report and dashboard.

---

## Database Tables
- `mlm_commission_settings (level INT, percent DECIMAL)`
- `mlm_company_share (id INT, share_percent DECIMAL)`
- `mlm_commission_ledger (id, transaction_id, associate_id, level, commission_amount, created_at)`

---

## Extending/Customizing
- Update commission logic in `/includes/functions/mlm_commission_logic.php` for advanced rules (e.g., bonuses, rank-based payouts)
- Add more analytics to dashboards as needed
- Secure all admin/associate pages with authentication

---

## Security Notes
- All settings changes should be restricted to super admin only.
- Associates should only see their own commission reports.

---

## Quick Links
- **Plan Settings:** `/admin/mlm_commission_settings.php`
- **Commission Report:** `/admin/associate_commission_report.php?aid=ASSOCIATE_ID`
- **Admin Dashboard:** `/admin/dashboard.php`

---

## Contact
For further customization or support, contact your developer or system administrator.

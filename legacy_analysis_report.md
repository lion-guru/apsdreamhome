# Legacy Analysis & Project Modernization Report

## Overview

This report summarizes the analysis of the legacy media files found in `archive/legacy_media/aaaaa`, specifically focusing on `apsdreamhome (3).sql` and `website testing image/index.php`.

## Findings

### 1. MLM Structure (Legacy Plan)

The legacy database (`apsdreamhome (3).sql`) reveals a sophisticated MLM system with multiple commission structures:

- **Core Tables**:
  - `mlm_tree`: Defines the user hierarchy (parent-child relationships).
  - `mlm_commissions`: Records individual commission transactions.
  - `mlm_rank_advancements`: Tracks user rank changes and bonuses.
  - `mlm_payouts` & `mlm_withdrawal_requests`: Handles financial payouts.

- **Commission Models**:
  - **Associate Levels**: Defined in `associate_levels` table. Includes:
    - `commission_percent`: Base percentage.
    - `direct_referral_bonus`: Bonus for direct recruits.
    - `level_bonus`: Bonus for downline activity.
    - `reward_description`, `min_team_size`, `min_business`, `max_business`.
  - **Company Property Levels**: Defined in `company_property_levels`. Includes:
    - `direct_commission_percentage`, `team_commission_percentage`.
    - `level_bonus_percentage`, `matching_bonus_percentage`.
    - `leadership_bonus_percentage`.
  - **Hybrid Plans**: `hybrid_commission_plans` table suggests a mix of commission types.
  - **Resell Structure**: `resell_commission_structure` handles commissions for property resales (plots, flats, etc.) with specific percentages based on value ranges.

### 2. CRM Structure (Legacy)

The legacy CRM system includes:

- **Leads**: `leads` table tracks potential customers (`name`, `contact`, `source`, `status`, `notes`).
- **Lead Assets**: `lead_files` and `lead_notes` for detailed tracking.
- **Status Workflow**: Examples include `closed_won`, `contacted`, `negotiation`, `qualified`.

### 3. Website Structure

- **Homepage**: `website testing image/index.php` shows a modern, feature-rich real estate homepage with:
  - Property search (Buy/Rent/Plot/Commercial).
  - Dynamic sections for Properties, Projects, Team, Gallery, Testimonials.
  - Integration with `site_settings` for configuration.

## Modernization Status

### Current Progress

- **Service Modernization**: `CommissionCalculator.php`, `EngagementService.php`, `TaskService.php`, `SupportTicketService.php` have been updated to use PDO and remove legacy `AppConfig` dependencies.
- **Dual-Plan Support**: `CommissionCalculator.php` now supports both 'legacy' (rank-based) and 'standard' (5-level) commission calculations.
- **Database Schema**: `mlm_commission_ledger` has been verified and updated to include `commission_type` and `commission_percentage`.
- **RBAC**: Role-Based Access Control has been implemented in Task and Support services.

### Pending Integrations

- **Commission Rules**: The detailed rules from `associate_levels` and `company_property_levels` need to be fully ported into the `CommissionCalculator` logic or a new `CommissionRuleService`.
- **CRM Migration**: Legacy leads need to be migrated to the new CRM system if not already done.
- **Frontend Alignment**: The legacy homepage design concepts should be merged with the current frontend if desired.

## Next Steps

See `NEXT_STEPS.md` for a detailed task list for the next development session.

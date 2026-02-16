# Next Steps for APS Dream Home Project

This file outlines the tasks to be addressed in the next development session.

## High Priority
1.  **Commission Logic Integration**:
    *   Fully implement the logic from legacy tables (`associate_levels`, `company_property_levels`) into `CommissionCalculator.php`.
    *   Ensure the "Dual Plan" approach correctly handles the complex percentages found in the legacy SQL (e.g., `matching_bonus`, `leadership_bonus`).
2.  **CRM Data Migration**:
    *   Create a migration script to import data from the legacy `leads` table (found in `apsdreamhome (3).sql`) into the current `leads` or `crm_leads` table.
3.  **Database Verification**:
    *   Re-verify the `mlm_commission_ledger` schema in the live environment.
    *   Ensure all necessary columns (`commission_type`, `percentage`, etc.) are present.
4.  **Frontend Integration**:
    *   Review `archive/legacy_media/aaaaa/website testing image/index.php` and integrate any missing UI features into the current homepage.

## Medium Priority
1.  **PDF Analysis**:
    *   Attempt to extract content from `archive/legacy_media/aaaaa/selforce crm.pdf` (if possible) to understand the "Salesforce-like" CRM features the user mentioned.
2.  **Code Cleanup**:
    *   Remove any remaining `mysqli` usage in core services.
    *   Ensure strict PSR-4 compliance across all modules.
3.  **Git Repository**:
    *   Ensure the remote repository is configured and up-to-date.

## Low Priority
1.  **Documentation**:
    *   Update project documentation with the new Dual Plan architecture.

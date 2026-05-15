# MLM Modernization & API Bridge Plan

This plan aims to address the architectural fragmentation and technical debt identified in the APS Dream Home project, specifically focusing on bridging the MLM logic and securing the legacy layer for Flutter integration.

## Proposed Changes

### [MLM Service Bridge]
Modernizing the MLM calculation layer and connecting it to the existing API.

#### [MODIFY] [MLMController.php](file:///c:/xampp/htdocs/apsdreamhome/app/Http/Controllers/MLMController.php)
- Replace mocked [getMLMDashboardData()](file:///c:/xampp/htdocs/apsdreamhome/app/Http/Controllers/MLMController.php#57-91) with calls to [DifferentialCommissionCalculator](file:///c:/xampp/htdocs/apsdreamhome/app/Services/DifferentialCommissionCalculator.php#9-111) and direct database queries.
- Implement real-time rank progression logic based on the targets defined in the report (e.g., 1M for Associate, 3.5M for Sr. Associate).

#### [NEW] [PerformanceRankCalculator.php](file:///c:/xampp/htdocs/apsdreamhome/app/Services/PerformanceRankCalculator.php)
- Implement the "Team Performance Ranks" logic (Starter, Bronze, Silver, Gold, Platinum).
- Calculate team size and average performance metrics.

---

### [Security & Technical Debt]
Hardening the system against SQL injection and consolidating routing.

#### [MODIFY] [Security.php](file:///c:/xampp/htdocs/apsdreamhome/app/Core/Security.php)
- Deprecate [escapeSql()](file:///c:/xampp/htdocs/apsdreamhome/app/Core/Security.php#39-64) (regex-based sanitization).
- [IMPORTANT] Add a warning comment to [sanitize($input, 'sql')](file:///c:/xampp/htdocs/apsdreamhome/app/Core/Security.php#12-38) to encourage PDO prepared statements.

#### [MODIFY] [ApiAuthMiddleware.php](file:///c:/xampp/htdocs/apsdreamhome/app/Http/Middleware/ApiAuthMiddleware.php)
- Ensure all API routes are protected and user context is correctly injected for MLM calculations.

---

### [Flutter API Enhancement]
Ensuring the mobile app has access to all necessary business logic.

#### [MODIFY] [MobileApiController.php](file:///c:/xampp/htdocs/apsdreamhome/app/Http/Controllers/Api/MobileApiController.php)
- Add endpoints for rank-specific data and real-time commission tracking.
- Standardize response formats for the Flutter app.

## Verification Plan

### Automated Tests
- Create a test script `tests/mlm_calculation_test.php` to verify the [DifferentialCommissionCalculator](file:///c:/xampp/htdocs/apsdreamhome/app/Services/DifferentialCommissionCalculator.php#9-111) output against expected commission percentages.
- Run `curl` commands to verify that the `/api/mlm/analytics` endpoint returns real data instead of mock data.

### Manual Verification
- Log in to the associate dashboard in the web view and verify that the "Mock Data" message is gone (after bridging).
- Trigger a "Sale" via a test script and verify that commissions are recorded in the `commissions` table.

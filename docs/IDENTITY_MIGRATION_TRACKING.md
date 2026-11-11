# Identity Unification — Migration Tracking Document
# Generated: 2026-11-08

## Phase 4/7 Status: PARTIAL COMPLETE

### What's Done
- Profile tables created: customer_profiles, associate_profiles, employee_profiles, agent_profiles
- 57 rows migrated: 37 associate + 15 employee + 2 agent + 73 customer (customer_profiles uses users table directly)
- IdentityService with 14 methods (round-trip verified)
- 23 services migrated to IdentityService
- 2 additional services migrated this round (BrokerCommissionService, PipelineService)
- FK columns (user_id) added to bookings, leads, plot_bookings
- Indexes added on user_id columns
- Legacy tables marked DEPRECATED via table comments
- Missing columns added: associate_profiles.commission_type, commission_value; employee_profiles.status; agent_profiles.status

### What's NOT Safe to Migrate (36 services with complex JOINs)
These services use legacy table PKs (employees.id, associates.id) as FKs in dependent tables.
Migrating them requires FK restructuring in: salary_structures, course_assignments, helpdesk_tickets, plot_bookings, etc.

| # | Service | Legacy Table | JOINs | Dependent FKs |
|---|---------|-------------|-------|---------------|
| 1 | Backoffice\DailyOperationsService | employees, associates | 4+2 | salary_structures, attendance |
| 2 | Business\AssociateService | associates | 2 | bookings, leads |
| 3 | Business\ReportService | associates | 1 | bookings, payments |
| 4 | EmployeeGrievanceService | employees | 4 | helpdesk_tickets |
| 5 | EmployeePerformanceService | employees | 1 | performance_reviews |
| 6 | EmployeeWorkloadService | employees | 3 | task_assignments |
| 7 | EngagementSurveyService | employees | 1 | survey_responses |
| 8 | ExpenseReimbursementService | employees | 3+1 | expense_claims |
| 9 | FullAndFinalSettlementService | employees | 0+1 | fnf_settlements |
| 10 | GratuityService | employees | 0+2 | gratuity_records |
| 11 | HelpdeskService | employees | 4 | helpdesk_tickets |
| 12 | HR\PayrollService | employees | 1 | salary_payments |
| 13 | MLM\BrokerCommissionService | associates | 0+2 | commission_ledger |
| 14 | MLM\CommissionManager | associates | 1 | commission_ledger |
| 15 | MLM\LeadershipSalaryService | associates | 1 | salary_structures |
| 16 | MLM\PipelineService | associates | 0+1 | plot_bookings |
| 17 | MLM\RoyaltyPoolService | associates | 1 | royalty_pool |
| 18 | MlmPolicyGuard | associates | 2 | policy_violations |
| 19 | OrgChartService | employees | 3+1 | org_hierarchy |
| 20 | PayrollService | employees | 2+1 | salary_payments |
| 21 | PDF\PdfService | employees | 1 | documents |
| 22 | PerformanceService | employees | 4 | performance_reviews |
| 23 | SalariedAgentService | associates | 0+1 | salary_structures |
| 24 | SalaryAdvanceService | employees | 1+2 | salary_advances |
| 25 | Sales\BookingLifecycleService | associates | 2 | plot_bookings, payments |
| 26 | ShiftRosterService | employees | 4+1 | shift_rosters |
| 27 | ShiftSwapService | employees | 9+2 | shift_swaps |
| 28 | StatutoryComplianceService | employees | 0+4 | pf_esi_records |
| 29 | TrainingService | employees | 1 | course_assignments |
| 30 | WorkDistributionService | employees | 1 | work_orders |

### Migration Requirements for Remaining 30 Services
Each service needs:
1. Add `user_id` column to dependent table (e.g., salary_structures.employee_id → salary_structures.user_id)
2. Migrate data: UPDATE salary_structures SET user_id = (SELECT user_id FROM employees WHERE id = employee_id)
3. Update all JOINs: `JOIN employees e ON ...` → `JOIN employee_profiles ep ON ...` or `JOIN users u ON ...`
4. Update all WHERE clauses: `WHERE e.id = ?` → `WHERE ep.user_id = ?`
5. Update controllers that pass legacy IDs
6. Full regression testing

### Estimated Effort
- Per service: 2-4 hours (schema + data + code + test)
- Total: 60-120 hours (3-6 weeks)
- Requires: Owner approval (affects billing/payroll/bookings/HR)

### Recommendation
Treat as separate project with:
1. Safety dump + byte-identical restore test
2. Per-service FK restructuring plan
3. Full regression testing after each service
4. Owner approval for financial system changes

### Services Already Migrated (23 total)
1. SpotBonusService
2. MLM\AgentLedgerService
3. MLM\CommissionLedgerService
4. MLM\SalaryIncentiveService
5. MLM\TrackBCommissionService
6. MLM\TrackCCommissionService
7. MLM\GenerationBonusEngine
8. MLM\InfinityOverrideService
9. MLM\MatchingBonusService
10. MLM\MLMCommissionEngine
11. LeaveEncashmentService
12. EmployeeService
13. AdminMenuService
14. PortalMenuService
15. AnnouncementService
16. DepartmentService
17. PfEsiService
18. EmployeeAssetService
19. OffboardingService
20. OvertimeService
21. BrokerCommissionService (this round)
22. PipelineService (this round)
23. IdentityService (the service itself)

### IdentityService Methods (14 total)
- getAssociateProfile(int $userId, bool $withUser = false)
- getEmployeeProfile(int $userId, bool $withUser = false)
- getAgentProfile(int $userId, bool $withUser = false)
- getCustomerProfile(int $userId, bool $withUser = false)
- getUnifiedProfile(int $userId, bool $withUser = true)
- getActiveAssociatesCount(): int
- getActiveEmployeesCount(): int
- resolveAssociateUserId(int $associateId): int
- resolveAgentUserId(int $agentId): int
- resolveEmployeeUserId(int $employeeId): int
- resolveAssociateId(int $userId): int
- resolveEmployeeId(int $userId): int
- getAssociateStatus(int $userId): string
- getEmployeeStatus(int $userId): string
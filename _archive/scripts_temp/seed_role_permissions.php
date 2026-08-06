<?php
/**
 * Seed admin_role_menu_permissions for 32 missing roles
 * Directors, Managers, Team Leads, Staff, MLM roles
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../config/bootstrap.php';
require __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;
$db = Database::getInstance();
$pdo = $db->getConnection();

echo "=== SEEDING ROLE MENU PERMISSIONS ===\n\n";

// First, get all menu items for reference
$stmt = $pdo->query("SELECT id, name, section, url FROM admin_menu_items WHERE is_active=1");
$allMenus = $stmt->fetchAll(PDO::FETCH_ASSOC);
$menuById = [];
foreach ($allMenus as $m) {
    $menuById[$m['id']] = $m;
}

// Group by section
$menusBySection = [];
foreach ($allMenus as $m) {
    $menusBySection[$m['section']][] = $m['id'];
}

// Helper: insert permissions for a role with given menu IDs
function insertPermissions($pdo, $role, $menuIds, $canView = 1, $canCreate = 0, $canEdit = 0, $canDelete = 0) {
    $inserted = 0;
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO admin_role_menu_permissions (role, menu_item_id, can_view, can_create, can_edit, can_delete)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    foreach ($menuIds as $menuId) {
        $stmt->execute([$role, $menuId, $canView, $canCreate, $canEdit, $canDelete]);
        if ($stmt->rowCount() > 0) $inserted++;
    }
    return $inserted;
}

// === COMMON MENU SETS ===

// Common settings: general settings, site settings
$settingsMenu = [107, 100, 101, 115]; // General Settings, Site Settings, Site Content, Company Profile

// Common dashboard items
$erpDashboard = [1]; // ERP Overview
$reportsAnalytics = [127, 128, 129, 130]; // Reports, Analytics, PDF, Saved Searches

// User management
$userManagement = [104, 105, 106]; // Users, Roles, Registrations

// Operations basics
$operationsBasic = [181, 247, 248]; // Backoffice, OCR, OCR Templates

// =====================================================
// 1. DIRECTORS (5 roles) — Full department access + cross-functional
// =====================================================
echo "--- DIRECTORS ---\n";

// Sales Director: CRM + Bookings + Properties (sales-relevant) + Marketing
$salesDirector = array_merge(
    $erpDashboard,
    $menusBySection['bookings'] ?? [], // 5 items
    [7, 8, 9, 10, 11, 12], // Leads, Kanban, Scoring, Deals, Visits, Enquiries
    $menusBySection['crm'] ?? [], // All CRM
    [19, 20, 21, 28, 29, 33, 34], // Properties, Plots, Categories, Resell, User Props, Alerts, Projects
    $reportsAnalytics,
    [87, 88, 90, 92], // Campaigns Hub, Visits Log, Marketing Campaigns, Drip
    $userManagement,
    [164, 165, 171, 172, 173, 174, 175, 176, 177, 195], // CRM Analytics, Forms, Dedup, Custom Fields, Drip, Email, SLA, Meetings, Voice CRM, Notif Dashboard
    [41, 42, 43], // Associates, Commissions, Payouts (sales team)
    $settingsMenu
);
$n = insertPermissions($pdo, 'sales_director', $salesDirector, 1, 1, 1, 0);
echo "  sales_director: +$n items\n";

// Marketing Director: Marketing + CRM + Analytics
$marketingDirector = array_merge(
    $erpDashboard,
    $menusBySection['marketing'] ?? [], // All marketing (31 items)
    [7, 8, 9, 10, 12, 13], // Leads, Kanban, Scoring, Deals, Enquiries, Campaigns
    $reportsAnalytics,
    [164, 165, 171, 172, 173, 174, 175, 176, 177, 195], // CRM deep tools
    $userManagement,
    [93, 94, 95, 96, 97, 98, 99, 100, 101], // CMS
    [249, 250, 251, 252, 253, 254], // Communication
    $settingsMenu
);
$n = insertPermissions($pdo, 'marketing_director', $marketingDirector, 1, 1, 1, 0);
echo "  marketing_director: +$n items\n";

// Construction Director: Properties + Colony + Projects + Land + Legal
$constructionDirector = array_merge(
    $erpDashboard,
    $menusBySection['properties'] ?? [], // All properties (24 items)
    $menusBySection['locations'] ?? [], // States, Districts, Colonies
    [79, 80, 81], // Disputes, Deadlines, RERA
    [184, 185, 186, 189], // Legal Docs, Templates, Clauses, Categories
    $reportsAnalytics,
    [30, 31], // Project Progress, NOC
    $settingsMenu
);
$n = insertPermissions($pdo, 'construction_director', $constructionDirector, 1, 1, 1, 0);
echo "  construction_director: +$n items\n";

// Finance Director: Finance + Payments + Banking + TDS/GST + Loans
$financeDirector = array_merge(
    $erpDashboard,
    $menusBySection['finance'] ?? [], // All finance (20 items)
    [53, 54, 55], // Payments, Invoices, Expenses
    $reportsAnalytics,
    [63, 64, 65, 66], // E-Filing, TDS Filing, GST Filing, Calendar
    $menusBySection['commission'] ?? [], // Commission recalculations, payout batches
    [182, 183], // Company Loans, Loan Offers
    $userManagement,
    [104], // Users (for financial oversight)
    $settingsMenu
);
$n = insertPermissions($pdo, 'finance_director', $financeDirector, 1, 1, 1, 0);
echo "  finance_director: +$n items\n";

// HR Director: HRM + Employees + Training + Legal Compliance
$hrDirector = array_merge(
    $erpDashboard,
    $menusBySection['hrm'] ?? [], // All HRM (12 items)
    [196, 197], // Departments, Designations
    [136, 137, 138, 139, 140, 141, 142, 143, 144, 145], // Employee: Dashboard, Tasks, Attendance, Leaves, Payroll, Performance, Docs, Profile, Settings, Logout
    $reportsAnalytics,
    [178, 179], // Careers, Job Applications
    [79, 80], // Legal Disputes, Deadlines (compliance)
    [194], // Compliance Scorecard
    $userManagement,
    $settingsMenu
);
$n = insertPermissions($pdo, 'hr_director', $hrDirector, 1, 1, 1, 0);
echo "  hr_director: +$n items\n";

// =====================================================
// 2. MANAGERS (9 roles) — Department-specific, moderate access
// =====================================================
echo "\n--- MANAGERS ---\n";

// Department Manager: General management — overview of all departments
$deptManager = array_merge(
    $erpDashboard,
    [196, 197, 71], // Departments, Designations, Employees
    [136, 137, 138, 139, 140, 141, 142, 143], // Employee basics
    $reportsAnalytics,
    $operationsBasic,
    $settingsMenu
);
$n = insertPermissions($pdo, 'department_manager', $deptManager, 1, 1, 1, 0);
echo "  department_manager: +$n items\n";

// Project Manager: Projects + Construction + Sites
$projectManager = array_merge(
    $erpDashboard,
    [30, 34, 26, 27], // Project Progress, Projects, Sites, Colonies
    $menusBySection['properties'] ?? [],
    $menusBySection['locations'] ?? [],
    [17, 18, 150, 151, 152, 153, 154, 155], // Colony Pipeline, Feasibility, Directory
    $reportsAnalytics,
    $settingsMenu
);
$n = insertPermissions($pdo, 'project_manager', $projectManager, 1, 1, 1, 0);
echo "  project_manager: +$n items\n";

// Sales Manager: Sales team + Leads + Bookings + CRM
$salesManager = array_merge(
    $erpDashboard,
    [7, 8, 9, 10, 11, 12], // Leads, Kanban, Scoring, Deals, Visits, Enquiries
    $menusBySection['bookings'] ?? [],
    [19, 20, 28], // Properties, Plots, Resell
    $menusBySection['crm'] ?? [],
    [164, 165, 171, 172, 173, 174, 175, 176, 177, 195], // CRM Tools
    $reportsAnalytics,
    [87, 88, 90, 92], // Campaigns
    [41, 42, 43], // Associates
    $settingsMenu
);
$n = insertPermissions($pdo, 'sales_manager', $salesManager, 1, 1, 1, 0);
echo "  sales_manager: +$n items\n";

// HR Manager: Employees + Payroll + Attendance + Training
$hrManager = array_merge(
    $erpDashboard,
    $menusBySection['hrm'] ?? [],
    [196, 197, 71], // Departments, Designations, Employees
    [136, 137, 138, 139, 140, 141, 142, 143, 144, 145], // Employee basics
    [178, 179], // Careers, Applications
    $reportsAnalytics,
    $settingsMenu
);
$n = insertPermissions($pdo, 'hr_manager', $hrManager, 1, 1, 1, 0);
echo "  hr_manager: +$n items\n";

// Marketing Manager: Marketing campaigns + CRM + Content
$marketingManager = array_merge(
    $erpDashboard,
    [85, 86, 87, 88, 89, 90, 91, 92], // Marketing core
    [156, 157, 158, 159, 160], // Ads, Referrals
    [7, 8, 13], // Leads, Kanban, Campaigns
    [161, 162, 163, 164, 165], // CRM Templates, Bulk, Segments, Analytics, Forms
    [171, 172, 173, 174, 175, 176, 177], // CRM Tools
    [195], // Notification Dashboard
    [93, 94, 95, 96, 97], // CMS: Pages, Blogs, Gallery, Testimonials, FAQs
    $reportsAnalytics,
    [238], // Social Media
    $settingsMenu
);
$n = insertPermissions($pdo, 'marketing_manager', $marketingManager, 1, 1, 1, 0);
echo "  marketing_manager: +$n items\n";

// Finance Manager: Finance + Payments + Expenses + TDS/GST
$financeManager = array_merge(
    $erpDashboard,
    $menusBySection['finance'] ?? [],
    [53, 54, 55], // Payments, Invoices, Expenses
    [63, 64, 65, 66], // E-Filing
    [42, 43], // Commissions, Payouts
    $reportsAnalytics,
    [182, 183], // Company Loans
    $settingsMenu
);
$n = insertPermissions($pdo, 'finance_manager', $financeManager, 1, 1, 1, 0);
echo "  finance_manager: +$n items\n";

// Property Manager: Properties + Plots + Colony Pipeline
$propertyManager = array_merge(
    $erpDashboard,
    $menusBySection['properties'] ?? [],
    $menusBySection['locations'] ?? [],
    [7, 11], // Leads, Visits (property related)
    $reportsAnalytics,
    $settingsMenu
);
$n = insertPermissions($pdo, 'property_manager', $propertyManager, 1, 1, 1, 0);
echo "  property_manager: +$n items\n";

// IT Manager: Technology + Security + AI + System
$itManager = array_merge(
    $erpDashboard,
    $menusBySection['technology'] ?? [],
    $menusBySection['system'] ?? [],
    $menusBySection['security'] ?? [],
    [107, 108, 109], // General Settings, GodMode, Activity Log
    [110, 111, 112, 113, 114, 119, 120, 121, 122, 123, 124], // Technical settings
    [249, 250, 251, 252, 253, 254], // Communication
    $reportsAnalytics,
    $settingsMenu
);
$n = insertPermissions($pdo, 'it_manager', $itManager, 1, 1, 1, 1);
echo "  it_manager: +$n items\n";

// Operations Manager: Operations + Vendors + Backoffice + Services
$operationsManager = array_merge(
    $erpDashboard,
    $menusBySection['operations'] ?? [],
    [19, 20, 26, 27, 34], // Properties, Plots, Sites, Colonies, Projects
    $reportsAnalytics,
    [102, 103], // Services
    [249, 250, 251, 252, 253, 254], // Communication
    $settingsMenu
);
$n = insertPermissions($pdo, 'operations_manager', $operationsManager, 1, 1, 1, 0);
echo "  operations_manager: +$n items\n";

// =====================================================
// 3. TEAM LEADS (4 roles) — Small team focus
// =====================================================
echo "\n--- TEAM LEADS ---\n";

// Team Lead (generic): Team tasks + attendance + leads
$teamLead = array_merge(
    $erpDashboard,
    [137, 138, 139, 140, 141], // Tasks, Attendance, Leaves, Payroll, Performance
    [7, 8, 10], // Leads, Kanban, Deals
    [143, 144], // Profile, Settings
    $reportsAnalytics
);
$n = insertPermissions($pdo, 'team_lead', $teamLead, 1, 0, 1, 0);
echo "  team_lead: +$n items\n";

// Telecalling Lead: Telecalling + Leads + Voice
$telecallingLead = array_merge(
    $erpDashboard,
    [74], // Telecaller Overrides
    [7, 8, 9, 12], // Leads, Kanban, Scoring, Enquiries
    [88, 89], // Visits Log, Voice Scheduler
    [177], // Voice CRM
    [137, 138, 139], // Tasks, Attendance, Leaves
    [225, 226, 227, 228, 229, 230, 234], // AI Calling
    $reportsAnalytics
);
$n = insertPermissions($pdo, 'telecalling_lead', $telecallingLead, 1, 0, 1, 0);
echo "  telecalling_lead: +$n items\n";

// Sales Team Lead: Sales team + Leads + Deals
$salesTeamLead = array_merge(
    $erpDashboard,
    [7, 8, 9, 10, 11, 12], // Leads, Kanban, Scoring, Deals, Visits, Enquiries
    $menusBySection['bookings'] ?? [],
    [19, 20], // Properties, Plots
    [137, 138, 139], // Tasks, Attendance, Leaves
    [164], // CRM Analytics
    $reportsAnalytics
);
$n = insertPermissions($pdo, 'sales_team_lead', $salesTeamLead, 1, 0, 1, 0);
echo "  sales_team_lead: +$n items\n";

// Support Lead: Support Tickets + Complaints + SLA
$supportLead = array_merge(
    $erpDashboard,
    [14], // Support Tickets
    [15], // NPS Surveys
    [175], // SLA Dashboard
    [137, 138, 139], // Tasks, Attendance, Leaves
    $reportsAnalytics
);
$n = insertPermissions($pdo, 'support_lead', $supportLead, 1, 0, 1, 0);
echo "  support_lead: +$n items\n";

// =====================================================
// 4. SENIOR STAFF (4 roles)
// =====================================================
echo "\n--- SENIOR STAFF ---\n";

// Senior Accountant: Finance subset
$seniorAccountant = array_merge(
    $erpDashboard,
    [53, 54, 55], // Payments, Invoices, Expenses
    [56, 57, 58, 59, 60, 61, 62], // Cash Book, Reconciliation, TDS, GST, Vendors, Penalties, EMI
    [63, 64, 65, 66], // E-Filing
    [68, 69, 70], // Banking, Import, Cash Collections
    [182, 183], // Company Loans
    [137, 138, 139], // Tasks, Attendance, Leaves
    $reportsAnalytics
);
$n = insertPermissions($pdo, 'senior_accountant', $seniorAccountant, 1, 1, 1, 0);
echo "  senior_accountant: +$n items\n";

// Senior Developer: Technology + System + Security
$seniorDeveloper = array_merge(
    $erpDashboard,
    $menusBySection['technology'] ?? [],
    $menusBySection['system'] ?? [],
    [107, 108, 109], // Settings, GodMode, Activity Log
    [114, 119, 120], // Webhooks, AI Config, Localization
    [137, 138, 139], // Tasks, Attendance, Leaves
    $reportsAnalytics
);
$n = insertPermissions($pdo, 'senior_developer', $seniorDeveloper, 1, 1, 1, 1);
echo "  senior_developer: +$n items\n";

// Legal Advisor: Legal section
$legalAdvisor = array_merge(
    $erpDashboard,
    $menusBySection['legal'] ?? [],
    [81], // RERA
    [178, 179], // Careers, Job Applications
    [137, 138, 139], // Tasks, Attendance, Leaves
    $reportsAnalytics
);
$n = insertPermissions($pdo, 'legal_advisor', $legalAdvisor, 1, 1, 1, 0);
echo "  legal_advisor: +$n items\n";

// Chartered Accountant: Finance + Legal (compliance)
$charteredAccountant = array_merge(
    $erpDashboard,
    $menusBySection['finance'] ?? [],
    [63, 64, 65, 66], // E-Filing
    [53, 54, 55], // Payments, Invoices, Expenses
    [182, 183], // Company Loans
    [194], // Compliance
    [137, 138, 139], // Tasks, Attendance, Leaves
    $reportsAnalytics
);
$n = insertPermissions($pdo, 'chartered_accountant', $charteredAccountant, 1, 1, 1, 0);
echo "  chartered_accountant: +$n items\n";

// =====================================================
// 5. STAFF (7 roles)
// =====================================================
echo "\n--- STAFF ---\n";

// Accountant: Basic finance
$accountant = array_merge(
    $erpDashboard,
    [53, 54, 55], // Payments, Invoices, Expenses
    [56, 59, 60, 70], // Cash Book, GST, Vendors, Cash Collections
    [137, 138, 139], // Tasks, Attendance, Leaves
    $reportsAnalytics
);
$n = insertPermissions($pdo, 'accountant', $accountant, 1, 1, 0, 0);
echo "  accountant: +$n items\n";

// Developer: Technology basics
$developer = array_merge(
    $erpDashboard,
    [237, 239, 240], // IoT, Push, Document AI
    [167, 168, 169], // AI System, Lead Qualifier, Market Intelligence
    [190, 191, 192, 193], // Custom Features, Neighborhood, Investment Calc, Security Test
    [137, 138, 139], // Tasks, Attendance, Leaves
    $reportsAnalytics
);
$n = insertPermissions($pdo, 'developer', $developer, 1, 1, 0, 0);
echo "  developer: +$n items\n";

// Content Writer: CMS
$contentWriter = array_merge(
    $erpDashboard,
    [93, 94, 95, 96, 97, 99], // Pages, Blogs, Gallery, Testimonials, FAQs, News
    [137, 138, 139], // Tasks, Attendance, Leaves
    $reportsAnalytics
);
$n = insertPermissions($pdo, 'content_writer', $contentWriter, 1, 1, 0, 0);
echo "  content_writer: +$n items\n";

// Graphic Designer: CMS + Gallery
$graphicDesigner = array_merge(
    $erpDashboard,
    [93, 94, 95, 96], // Pages, Blogs, Gallery, Testimonials
    [137, 138, 139], // Tasks, Attendance, Leaves
    $reportsAnalytics
);
$n = insertPermissions($pdo, 'graphic_designer', $graphicDesigner, 1, 0, 0, 0);
echo "  graphic_designer: +$n items\n";

// Data Entry Operator: Basic operations
$dataEntryOp = array_merge(
    $erpDashboard,
    [7, 12, 29], // Leads, Enquiries, User Properties
    [113], // Bulk Import
    [137, 138, 139], // Tasks, Attendance, Leaves
);
$n = insertPermissions($pdo, 'data_entry_operator', $dataEntryOp, 1, 1, 0, 0);
echo "  data_entry_operator: +$n items\n";

// Backoffice Staff: Operations + basic tasks
$backofficeStaff = array_merge(
    $erpDashboard,
    $menusBySection['operations'] ?? [],
    [7, 12], // Leads, Enquiries
    [137, 138, 139], // Tasks, Attendance, Leaves
    $reportsAnalytics
);
$n = insertPermissions($pdo, 'backoffice_staff', $backofficeStaff, 1, 1, 0, 0);
echo "  backoffice_staff: +$n items\n";

// =====================================================
// 6. TELECALLING (2 roles)
// =====================================================
echo "\n--- TELECALLING ---\n";

// Telecalling Executive: Calls + Leads + Tasks
$telecallingExec = array_merge(
    $erpDashboard,
    [7, 8, 9, 12], // Leads, Kanban, Scoring, Enquiries
    [74], // Telecaller Overrides
    [177], // Voice CRM
    [88, 89], // Visits Log, Voice Scheduler
    [137, 138, 139], // Tasks, Attendance, Leaves
    [225, 226, 227, 230, 234] // AI Calling basics
);
$n = insertPermissions($pdo, 'telecalling_executive', $telecallingExec, 1, 0, 1, 0);
echo "  telecalling_executive: +$n items\n";

// Support Executive: Tickets + Complaints
$supportExec = array_merge(
    $erpDashboard,
    [14], // Support Tickets
    [15], // NPS
    [137, 138, 139], // Tasks, Attendance, Leaves
    $reportsAnalytics
);
$n = insertPermissions($pdo, 'support_executive', $supportExec, 1, 0, 1, 0);
echo "  support_executive: +$n items\n";

// =====================================================
// 7. MLM (4 roles)
// =====================================================
echo "\n--- MLM ---\n";

// Senior Associate: MLM + Commissions + Team
$seniorAssociate = array_merge(
    $erpDashboard,
    [39, 40, 41, 42], // MLM Dashboard, Tree, Associates, Commissions
    [7, 8, 10], // Leads, Kanban, Deals
    [19, 20], // Properties, Plots
    [137, 138, 139], // Tasks, Attendance, Leaves
    $reportsAnalytics
);
$n = insertPermissions($pdo, 'senior_associate', $seniorAssociate, 1, 0, 0, 0);
echo "  senior_associate: +$n items\n";

// Associate Team Lead: MLM + Team Management
$associateTeamLead = array_merge(
    $erpDashboard,
    [39, 40, 41, 42, 43], // MLM, Tree, Associates, Commissions, Payouts
    [7, 8, 10], // Leads, Kanban, Deals
    [19, 20], // Properties, Plots
    [137, 138, 139], // Tasks, Attendance, Leaves
    $reportsAnalytics
);
$n = insertPermissions($pdo, 'associate_team_lead', $associateTeamLead, 1, 0, 1, 0);
echo "  associate_team_lead: +$n items\n";

// Senior Agent: Agent tools + Properties + Leads
$seniorAgent = array_merge(
    $erpDashboard,
    [19, 20, 21, 28], // Properties, Plots, Categories, Resell
    [7, 8, 10, 11, 12], // Leads, Kanban, Deals, Visits, Enquiries
    [137, 138, 139], // Tasks, Attendance, Leaves
    $reportsAnalytics
);
$n = insertPermissions($pdo, 'senior_agent', $seniorAgent, 1, 0, 0, 0);
echo "  senior_agent: +$n items\n";

// Franchise Owner: Full access (similar to admin but without system/settings)
$franchiseOwner = array_merge(
    $erpDashboard,
    $menusBySection['bookings'] ?? [],
    $menusBySection['properties'] ?? [],
    $menusBySection['crm'] ?? [],
    $menusBySection['marketing'] ?? [],
    $menusBySection['mlm'] ?? [],
    $menusBySection['finance'] ?? [],
    [53, 54, 55], // Payments, Invoices, Expenses
    $reportsAnalytics,
    [71, 196, 197], // Employees, Depts, Designations
    [104], // Users
    [137, 138, 139], // Tasks, Attendance, Leaves
    $settingsMenu
);
$n = insertPermissions($pdo, 'franchise_owner', $franchiseOwner, 1, 1, 1, 0);
echo "  franchise_owner: +$n items\n";

// =====================================================
// SUMMARY
// =====================================================
echo "\n=== VERIFICATION ===\n";
$summary = $pdo->query("SELECT role, COUNT(*) as cnt FROM admin_role_menu_permissions WHERE can_view=1 GROUP BY role ORDER BY role")->fetchAll(PDO::FETCH_ASSOC);
foreach ($summary as $s) {
    $bar = str_repeat('█', min($s['cnt'], 50));
    echo sprintf("  %-30s %3d %s\n", $s['role'], $s['cnt'], $bar);
}
echo "\nTotal: " . count($summary) . " roles with permissions\n";
echo "Done!\n";

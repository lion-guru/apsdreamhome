<?php

namespace App\Http\Middleware;

use App\Core\Database\Database;
use Exception;

/**
 * RBAC Manager - Enterprise Role-Based Access Control
 * Complete Role and Permission Management
 */
class RBACManager
{
    // ============================================
    // EXECUTIVE LEVEL (C-Suite)
    // ============================================
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_CEO = 'ceo';
    const ROLE_CFO = 'cfo';
    const ROLE_COO = 'coo';
    const ROLE_CTO = 'cto';
    const ROLE_CMO = 'cmo';
    const ROLE_CHRO = 'chro';

    // ============================================
    // MANAGEMENT LEVEL
    // ============================================
    const ROLE_DIRECTOR = 'director';
    const ROLE_SALES_DIRECTOR = 'sales_director';
    const ROLE_MARKETING_DIRECTOR = 'marketing_director';
    const ROLE_CONSTRUCTION_DIRECTOR = 'construction_director';

    // ============================================
    // DEPARTMENTAL LEVEL
    // ============================================
    const ROLE_DEPARTMENT_MANAGER = 'department_manager';
    const ROLE_PROJECT_MANAGER = 'project_manager';
    const ROLE_SALES_MANAGER = 'sales_manager';
    const ROLE_HR_MANAGER = 'hr_manager';
    const ROLE_MARKETING_MANAGER = 'marketing_manager';
    const ROLE_FINANCE_MANAGER = 'finance_manager';
    const ROLE_PROPERTY_MANAGER = 'property_manager';
    const ROLE_IT_MANAGER = 'it_manager';
    const ROLE_OPERATIONS_MANAGER = 'operations_manager';

    // ============================================
    // TEAM LEAD LEVEL
    // ============================================
    const ROLE_TEAM_LEAD = 'team_lead';
    const ROLE_TELECALLING_LEAD = 'telecalling_lead';
    const ROLE_SALES_TEAM_LEAD = 'sales_team_lead';
    const ROLE_SUPPORT_LEAD = 'support_lead';

    // ============================================
    // SENIOR STAFF LEVEL
    // ============================================
    const ROLE_SENIOR_ACCOUNTANT = 'senior_accountant';
    const ROLE_SENIOR_DEVELOPER = 'senior_developer';
    const ROLE_LEGAL_ADVISOR = 'legal_advisor';
    const ROLE_CHARTERED_ACCOUNTANT = 'chartered_accountant';

    // ============================================
    // STAFF/EMPLOYEE LEVEL
    // ============================================
    const ROLE_ACCOUNTANT = 'accountant';
    const ROLE_DEVELOPER = 'developer';
    const ROLE_CONTENT_WRITER = 'content_writer';
    const ROLE_GRAPHIC_DESIGNER = 'graphic_designer';
    const ROLE_DATA_ENTRY_OPERATOR = 'data_entry_operator';
    const ROLE_BACKOFFICE_STAFF = 'backoffice_staff';

    // ============================================
    // TELECALLING & SUPPORT LEVEL
    // ============================================
    const ROLE_TELECALLER = 'telecaller';
    const ROLE_TELECALLING_EXECUTIVE = 'telecalling_executive';
    const ROLE_SUPPORT_EXECUTIVE = 'support_executive';

    // ============================================
    // SALES & MLM LEVEL
    // ============================================
    const ROLE_SENIOR_ASSOCIATE = 'senior_associate';
    const ROLE_ASSOCIATE_TEAM_LEAD = 'associate_team_lead';
    const ROLE_ASSOCIATE = 'associate';
    const ROLE_SENIOR_AGENT = 'senior_agent';
    const ROLE_AGENT = 'agent';
    const ROLE_FRANCHISE_OWNER = 'franchise_owner';

    // ============================================
    // CUSTOMER LEVEL
    // ============================================
    const ROLE_PREMIUM_CUSTOMER = 'premium_customer';
    const ROLE_VERIFIED_CUSTOMER = 'verified_customer';
    const ROLE_GUEST_CUSTOMER = 'guest_customer';

    // ============================================
    // LEAD LEVEL
    // ============================================
    const ROLE_HOT_LEAD = 'hot_lead';
    const ROLE_WARM_LEAD = 'warm_lead';
    const ROLE_COLD_LEAD = 'cold_lead';

    // ============================================
    // GUEST LEVEL
    // ============================================
    const ROLE_GUEST = 'guest';

    // ============================================
    // LEGACY ALIASES (for backward compatibility)
    // ============================================
    const ROLE_ADMIN = 'admin';
    const ROLE_MANAGER = 'manager';
    const ROLE_USER = 'user';
    const ROLE_ASSOCOCIATE = 'associate';

    // ============================================
    // PERMISSION CATEGORIES
    // ============================================

    // Dashboard Permissions
    const PERM_DASHBOARD_VIEW = 'dashboard.view';
    const PERM_DASHBOARD_CUSTOMIZE = 'dashboard.customize';
    const PERM_DASHBOARD_WIDGETS = 'dashboard.widgets';

    // User Management Permissions
    const PERM_USERS_VIEW_ALL = 'users.view.all';
    const PERM_USERS_VIEW_TEAM = 'users.view.team';
    const PERM_USERS_CREATE = 'users.create';
    const PERM_USERS_EDIT = 'users.edit';
    const PERM_USERS_DELETE = 'users.delete';
    const PERM_USERS_ROLES = 'users.roles';

    // Property Permissions
    const PERM_PROPERTY_VIEW = 'property.view';
    const PERM_PROPERTY_VIEW_ALL = 'property.view.all';
    const PERM_PROPERTY_CREATE = 'property.create';
    const PERM_PROPERTY_EDIT = 'property.edit';
    const PERM_PROPERTY_DELETE = 'property.delete';
    const PERM_PROPERTY_PRICING = 'property.pricing';

    // Lead & CRM Permissions
    const PERM_LEADS_VIEW_ALL = 'leads.view.all';
    const PERM_LEADS_VIEW_TEAM = 'leads.view.team';
    const PERM_LEADS_VIEW_OWN = 'leads.view.own';
    const PERM_LEADS_CREATE = 'leads.create';
    const PERM_LEADS_EDIT = 'leads.edit';
    const PERM_LEADS_DELETE = 'leads.delete';
    const PERM_LEADS_ASSIGN = 'leads.assign';

    // Sales & MLM Permissions
    const PERM_SALES_VIEW = 'sales.view';
    const PERM_SALES_VIEW_ALL = 'sales.view.all';
    const PERM_SALES_VIEW_TEAM = 'sales.view.team';
    const PERM_SALES_CREATE = 'sales.create';
    const PERM_COMMISSION_VIEW = 'commission.view';
    const PERM_COMMISSION_MANAGE = 'commission.manage';
    const PERM_MLM_TREE_VIEW = 'mlm.tree.view';
    const PERM_MLM_DOWNLINE_VIEW = 'mlm.downline.view';

    // Financial Permissions
    const PERM_FINANCIAL_VIEW = 'financial.view';
    const PERM_FINANCIAL_VIEW_ALL = 'financial.view.all';
    const PERM_INVOICE_CREATE = 'invoice.create';
    const PERM_INVOICE_VIEW = 'invoice.view';
    const PERM_PAYMENT_PROCESS = 'payment.process';
    const PERM_EXPENSE_CREATE = 'expense.create';
    const PERM_EXPENSE_VIEW = 'expense.view';
    const PERM_EMI_MANAGE = 'emi.manage';
    const PERM_PAYROLL_VIEW = 'payroll.view';
    const PERM_PAYROLL_MANAGE = 'payroll.manage';
    const PERM_TAX_MANAGE = 'tax.manage';

    // HR & Employee Permissions
    const PERM_EMPLOYEE_VIEW_ALL = 'employee.view.all';
    const PERM_EMPLOYEE_VIEW_TEAM = 'employee.view.team';
    const PERM_EMPLOYEE_ADD = 'employee.add';
    const PERM_EMPLOYEE_EDIT = 'employee.edit';
    const PERM_ATTENDANCE_VIEW = 'attendance.view';
    const PERM_ATTENDANCE_MANAGE = 'attendance.manage';
    const PERM_LEAVE_VIEW = 'leave.view';
    const PERM_LEAVE_APPROVE = 'leave.approve';
    const PERM_LEAVE_MANAGE = 'leave.manage';
    const PERM_PERFORMANCE_VIEW = 'performance.view';
    const PERM_PERFORMANCE_MANAGE = 'performance.manage';
    const PERM_TRAINING_MANAGE = 'training.manage';

    // Marketing Permissions
    const PERM_MARKETING_VIEW = 'marketing.view';
    const PERM_MARKETING_VIEW_ALL = 'marketing.view.all';
    const PERM_CAMPAIGN_VIEW = 'campaign.view';
    const PERM_CAMPAIGN_CREATE = 'campaign.create';
    const PERM_CAMPAIGN_EXECUTE = 'campaign.execute';
    const PERM_EMAIL_MARKETING = 'email.marketing';
    const PERM_SMS_MARKETING = 'sms.marketing';
    const PERM_SOCIAL_MEDIA = 'social.media';
    const PERM_ANALYTICS_VIEW = 'analytics.view';

    // Content Permissions
    const PERM_CONTENT_VIEW = 'content.view';
    const PERM_PAGES_MANAGE = 'pages.manage';
    const PERM_MEDIA_MANAGE = 'media.manage';
    const PERM_BLOG_MANAGE = 'blog.manage';
    const PERM_FAQ_MANAGE = 'faq.manage';

    // AI & Tools Permissions
    const PERM_AI_DASHBOARD = 'ai.dashboard';
    const PERM_AI_SETTINGS = 'ai.settings';
    const PERM_AI_PROPERTY_VALUATION = 'ai.property.valuation';
    const PERM_AI_LEAD_SCORING = 'ai.lead.scoring';
    const PERM_CHATBOT_SETTINGS = 'chatbot.settings';

    // Report Permissions
    const PERM_REPORTS_VIEW = 'reports.view';
    const PERM_REPORTS_GENERATE = 'reports.generate';
    const PERM_REPORTS_EXPORT = 'reports.export';
    const PERM_REPORTS_SCHEDULE = 'reports.schedule';

    // System Permissions
    const PERM_SYSTEM_SETTINGS = 'system.settings';
    const PERM_BACKUP_MANAGE = 'backup.manage';
    const PERM_SECURITY_SETTINGS = 'security.settings';
    const PERM_API_MANAGE = 'api.manage';
    const PERM_LOG_VIEWER = 'log.viewer';
    const PERM_CACHE_MANAGE = 'cache.manage';

    // Communication Permissions
    const PERM_NOTIFICATIONS_SEND = 'notifications.send';
    const PERM_EMAIL_TEMPLATES = 'email.templates';
    const PERM_SMS_TEMPLATES = 'sms.templates';
    const PERM_WHATSAPP_TEMPLATES = 'whatsapp.templates';

    // Support Permissions
    const PERM_SUPPORT_VIEW = 'support.view';
    const PERM_SUPPORT_REPLY = 'support.reply';
    const PERM_SUPPORT_ESCALATE = 'support.escalate';

    // Task Permissions
    const PERM_TASKS_VIEW = 'tasks.view';
    const PERM_TASKS_CREATE = 'tasks.create';
    const PERM_TASKS_ASSIGN = 'tasks.assign';
    const PERM_TASKS_COMPLETE = 'tasks.complete';

    // ============================================
    // ALL ROLES ARRAY
    // ============================================
    private static $roles = [
        // Executive Level
        'super_admin' => ['name' => 'Super Admin', 'level' => 1, 'category' => 'Executive'],
        'ceo' => ['name' => 'CEO', 'level' => 2, 'category' => 'Executive'],
        'cfo' => ['name' => 'CFO', 'level' => 3, 'category' => 'Executive'],
        'coo' => ['name' => 'COO', 'level' => 3, 'category' => 'Executive'],
        'cto' => ['name' => 'CTO', 'level' => 3, 'category' => 'Executive'],
        'cmo' => ['name' => 'CMO', 'level' => 3, 'category' => 'Executive'],
        'chro' => ['name' => 'CHRO', 'level' => 3, 'category' => 'Executive'],

        // Management Level
        'director' => ['name' => 'Director', 'level' => 4, 'category' => 'Management'],
        'sales_director' => ['name' => 'Sales Director', 'level' => 4, 'category' => 'Management'],
        'marketing_director' => ['name' => 'Marketing Director', 'level' => 4, 'category' => 'Management'],
        'construction_director' => ['name' => 'Construction Director', 'level' => 4, 'category' => 'Management'],

        // Departmental Level
        'department_manager' => ['name' => 'Department Manager', 'level' => 5, 'category' => 'Departmental'],
        'project_manager' => ['name' => 'Project Manager', 'level' => 5, 'category' => 'Departmental'],
        'sales_manager' => ['name' => 'Sales Manager', 'level' => 5, 'category' => 'Departmental'],
        'hr_manager' => ['name' => 'HR Manager', 'level' => 5, 'category' => 'Departmental'],
        'marketing_manager' => ['name' => 'Marketing Manager', 'level' => 5, 'category' => 'Departmental'],
        'finance_manager' => ['name' => 'Finance Manager', 'level' => 5, 'category' => 'Departmental'],
        'property_manager' => ['name' => 'Property Manager', 'level' => 5, 'category' => 'Departmental'],
        'it_manager' => ['name' => 'IT Manager', 'level' => 5, 'category' => 'Departmental'],
        'operations_manager' => ['name' => 'Operations Manager', 'level' => 5, 'category' => 'Departmental'],

        // Team Lead Level
        'team_lead' => ['name' => 'Team Lead', 'level' => 6, 'category' => 'Team Lead'],
        'telecalling_lead' => ['name' => 'Telecalling Lead', 'level' => 6, 'category' => 'Team Lead'],
        'sales_team_lead' => ['name' => 'Sales Team Lead', 'level' => 6, 'category' => 'Team Lead'],
        'support_lead' => ['name' => 'Support Lead', 'level' => 6, 'category' => 'Team Lead'],

        // Senior Staff Level
        'senior_accountant' => ['name' => 'Senior Accountant', 'level' => 7, 'category' => 'Senior Staff'],
        'senior_developer' => ['name' => 'Senior Developer', 'level' => 7, 'category' => 'Senior Staff'],
        'legal_advisor' => ['name' => 'Legal Advisor', 'level' => 7, 'category' => 'Senior Staff'],
        'chartered_accountant' => ['name' => 'Chartered Accountant', 'level' => 7, 'category' => 'Senior Staff'],

        // Staff Level
        'accountant' => ['name' => 'Accountant', 'level' => 8, 'category' => 'Staff'],
        'developer' => ['name' => 'Developer', 'level' => 8, 'category' => 'Staff'],
        'content_writer' => ['name' => 'Content Writer', 'level' => 8, 'category' => 'Staff'],
        'graphic_designer' => ['name' => 'Graphic Designer', 'level' => 8, 'category' => 'Staff'],
        'data_entry_operator' => ['name' => 'Data Entry Operator', 'level' => 8, 'category' => 'Staff'],
        'backoffice_staff' => ['name' => 'Back Office Staff', 'level' => 8, 'category' => 'Staff'],

        // Telecalling & Support Level
        'telecaller' => ['name' => 'Telecaller', 'level' => 9, 'category' => 'Telecalling'],
        'telecalling_executive' => ['name' => 'Telecalling Executive', 'level' => 9, 'category' => 'Telecalling'],
        'support_executive' => ['name' => 'Support Executive', 'level' => 9, 'category' => 'Support'],

        // Sales & MLM Level
        'senior_associate' => ['name' => 'Senior Associate', 'level' => 10, 'category' => 'MLM'],
        'associate_team_lead' => ['name' => 'Associate Team Lead', 'level' => 10, 'category' => 'MLM'],
        'associate' => ['name' => 'Associate', 'level' => 10, 'category' => 'MLM'],
        'senior_agent' => ['name' => 'Senior Agent', 'level' => 10, 'category' => 'Agent'],
        'agent' => ['name' => 'Agent', 'level' => 10, 'category' => 'Agent'],
        'franchise_owner' => ['name' => 'Franchise Owner', 'level' => 9, 'category' => 'Franchise'],

        // Customer Level
        'premium_customer' => ['name' => 'Premium Customer', 'level' => 11, 'category' => 'Customer'],
        'verified_customer' => ['name' => 'Verified Customer', 'level' => 11, 'category' => 'Customer'],
        'guest_customer' => ['name' => 'Guest Customer', 'level' => 11, 'category' => 'Customer'],

        // Lead Level
        'hot_lead' => ['name' => 'Hot Lead', 'level' => 12, 'category' => 'Lead'],
        'warm_lead' => ['name' => 'Warm Lead', 'level' => 12, 'category' => 'Lead'],
        'cold_lead' => ['name' => 'Cold Lead', 'level' => 12, 'category' => 'Lead'],

        // Guest Level
        'guest' => ['name' => 'Guest', 'level' => 13, 'category' => 'Guest'],

        // Legacy Aliases
        'admin' => ['name' => 'Admin', 'level' => 2, 'category' => 'Legacy'],
        'manager' => ['name' => 'Manager', 'level' => 5, 'category' => 'Legacy'],
        'user' => ['name' => 'User', 'level' => 11, 'category' => 'Legacy'],
    ];

    // ============================================
    // PERMISSION DEFINITIONS
    // ============================================
    private static $permissions = [
        // Dashboard
        'dashboard.view' => 'View Dashboard',
        'dashboard.customize' => 'Customize Dashboard',
        'dashboard.widgets' => 'Manage Dashboard Widgets',

        // Users
        'users.view.all' => 'View All Users',
        'users.view.team' => 'View Team Members',
        'users.create' => 'Create Users',
        'users.edit' => 'Edit Users',
        'users.delete' => 'Delete Users',
        'users.roles' => 'Manage User Roles',

        // Properties
        'property.view' => 'View Properties',
        'property.view.all' => 'View All Properties',
        'property.create' => 'Create Properties',
        'property.edit' => 'Edit Properties',
        'property.delete' => 'Delete Properties',
        'property.pricing' => 'Manage Property Pricing',

        // Leads
        'leads.view.all' => 'View All Leads',
        'leads.view.team' => 'View Team Leads',
        'leads.view.own' => 'View Own Leads',
        'leads.create' => 'Create Leads',
        'leads.edit' => 'Edit Leads',
        'leads.delete' => 'Delete Leads',
        'leads.assign' => 'Assign Leads',

        // Sales
        'sales.view' => 'View Sales',
        'sales.view.all' => 'View All Sales',
        'sales.view.team' => 'View Team Sales',
        'sales.create' => 'Create Sales',
        'commission.view' => 'View Commissions',
        'commission.manage' => 'Manage Commissions',
        'mlm.tree.view' => 'View MLM Network Tree',
        'mlm.downline.view' => 'View Downline',

        // Financial
        'financial.view' => 'View Financials',
        'financial.view.all' => 'View All Financials',
        'invoice.create' => 'Create Invoices',
        'invoice.view' => 'View Invoices',
        'payment.process' => 'Process Payments',
        'expense.create' => 'Create Expenses',
        'expense.view' => 'View Expenses',
        'emi.manage' => 'Manage EMI',
        'payroll.view' => 'View Payroll',
        'payroll.manage' => 'Manage Payroll',
        'tax.manage' => 'Manage Tax',

        // HR
        'employee.view.all' => 'View All Employees',
        'employee.view.team' => 'View Team Employees',
        'employee.add' => 'Add Employees',
        'employee.edit' => 'Edit Employees',
        'attendance.view' => 'View Attendance',
        'attendance.manage' => 'Manage Attendance',
        'leave.view' => 'View Leave',
        'leave.approve' => 'Approve Leave',
        'leave.manage' => 'Manage Leave',
        'performance.view' => 'View Performance',
        'performance.manage' => 'Manage Performance',
        'training.manage' => 'Manage Training',

        // Marketing
        'marketing.view' => 'View Marketing',
        'marketing.view.all' => 'View All Marketing',
        'campaign.view' => 'View Campaigns',
        'campaign.create' => 'Create Campaigns',
        'campaign.execute' => 'Execute Campaigns',
        'email.marketing' => 'Email Marketing',
        'sms.marketing' => 'SMS Marketing',
        'social.media' => 'Social Media Management',
        'analytics.view' => 'View Analytics',

        // Content
        'content.view' => 'View Content',
        'pages.manage' => 'Manage Pages',
        'media.manage' => 'Manage Media',
        'blog.manage' => 'Manage Blog',
        'faq.manage' => 'Manage FAQ',

        // AI
        'ai.dashboard' => 'AI Dashboard Access',
        'ai.settings' => 'AI Settings',
        'ai.property.valuation' => 'AI Property Valuation',
        'ai.lead.scoring' => 'AI Lead Scoring',
        'chatbot.settings' => 'Chatbot Settings',

        // Reports
        'reports.view' => 'View Reports',
        'reports.generate' => 'Generate Reports',
        'reports.export' => 'Export Reports',
        'reports.schedule' => 'Schedule Reports',

        // System
        'system.settings' => 'System Settings',
        'backup.manage' => 'Manage Backup',
        'security.settings' => 'Security Settings',
        'api.manage' => 'Manage API',
        'log.viewer' => 'View Logs',
        'cache.manage' => 'Manage Cache',

        // Communication
        'notifications.send' => 'Send Notifications',
        'email.templates' => 'Email Templates',
        'sms.templates' => 'SMS Templates',
        'whatsapp.templates' => 'WhatsApp Templates',

        // Support
        'support.view' => 'View Support Tickets',
        'support.reply' => 'Reply to Tickets',
        'support.escalate' => 'Escalate Tickets',

        // Tasks
        'tasks.view' => 'View Tasks',
        'tasks.create' => 'Create Tasks',
        'tasks.assign' => 'Assign Tasks',
        'tasks.complete' => 'Complete Tasks',
    ];

    // ============================================
    // ROLE PERMISSIONS MATRIX
    // ============================================
    private static $rolePermissions = [
        // EXECUTIVE LEVEL - C-SUITE
        'super_admin' => [
            // Full Access to Everything
        ],
        'ceo' => [
            'dashboard.view',
            'dashboard.widgets',
            'users.view.all',
            'employee.view.all',
            'leads.view.all',
            'sales.view.all',
            'commission.view',
            'mlm.tree.view',
            'financial.view',
            'payroll.view',
            'employee.view.team',
            'leads.view.team',
            'sales.view.team',
            'property.view.all',
            'marketing.view.all',
            'analytics.view',
            'reports.view',
            'reports.generate',
            'reports.export',
        ],
        'cfo' => [
            'dashboard.view',
            'users.view.all',
            'employee.view.all',
            'sales.view.all',
            'commission.view',
            'financial.view.all',
            'financial.view',
            'invoice.create',
            'invoice.view',
            'payment.process',
            'expense.create',
            'expense.view',
            'emi.manage',
            'payroll.view',
            'payroll.manage',
            'tax.manage',
            'reports.view',
            'reports.generate',
            'reports.export',
            'leads.view.team',
            'sales.view.team',
        ],
        'coo' => [
            'dashboard.view',
            'users.view.all',
            'employee.view.all',
            'employee.view.team',
            'leads.view.all',
            'leads.view.team',
            'leads.create',
            'leads.edit',
            'leads.assign',
            'sales.view.all',
            'sales.view.team',
            'financial.view',
            'expense.create',
            'expense.view',
            'property.view.all',
            'property.create',
            'property.edit',
            'marketing.view.all',
            'analytics.view',
            'reports.view',
            'reports.generate',
            'tasks.view',
            'tasks.create',
            'tasks.assign',
        ],
        'cto' => [
            'dashboard.view',
            'dashboard.customize',
            'dashboard.widgets',
            'users.view.all',
            'leads.view.all',
            'sales.view.all',
            'marketing.view.all',
            'analytics.view',
            'reports.view',
            'reports.generate',
            'reports.export',
            'reports.schedule',
            'ai.dashboard',
            'ai.settings',
            'ai.property.valuation',
            'ai.lead.scoring',
            'chatbot.settings',
            'system.settings',
            'backup.manage',
            'security.settings',
            'api.manage',
            'log.viewer',
            'cache.manage',
            'tasks.view',
            'tasks.create',
        ],
        'cmo' => [
            'dashboard.view',
            'users.view.all',
            'employee.view.all',
            'leads.view.all',
            'leads.create',
            'sales.view.all',
            'marketing.view.all',
            'campaign.view',
            'campaign.create',
            'campaign.execute',
            'email.marketing',
            'sms.marketing',
            'social.media',
            'analytics.view',
            'reports.view',
            'reports.generate',
            'property.view.all',
            'property.create',
            'property.edit',
            'content.view',
            'pages.manage',
            'media.manage',
            'blog.manage',
            'faq.manage',
            'notifications.send',
            'email.templates',
            'sms.templates',
            'whatsapp.templates',
        ],
        'chro' => [
            'dashboard.view',
            'users.view.all',
            'employee.view.all',
            'employee.view.team',
            'employee.add',
            'employee.edit',
            'attendance.view',
            'attendance.manage',
            'leave.view',
            'leave.approve',
            'leave.manage',
            'performance.view',
            'performance.manage',
            'training.manage',
            'payroll.view',
            'payroll.manage',
            'reports.view',
            'reports.generate',
            'employee.add',
        ],

        // MANAGEMENT LEVEL
        'director' => [
            'dashboard.view',
            'users.view.team',
            'employee.view.team',
            'leads.view.team',
            'leads.create',
            'leads.edit',
            'sales.view.team',
            'commission.view',
            'mlm.tree.view',
            'financial.view',
            'expense.view',
            'marketing.view.all',
            'reports.view',
            'reports.generate',
            'tasks.view',
            'tasks.create',
            'tasks.assign',
        ],
        'sales_director' => [
            'dashboard.view',
            'users.view.team',
            'employee.view.team',
            'property.view.all',
            'property.view',
            'leads.view.team',
            'leads.create',
            'leads.edit',
            'leads.assign',
            'sales.view.all',
            'sales.view.team',
            'sales.create',
            'commission.view',
            'commission.manage',
            'mlm.tree.view',
            'reports.view',
            'reports.generate',
            'tasks.view',
            'tasks.create',
            'tasks.assign',
        ],
        'marketing_director' => [
            'dashboard.view',
            'users.view.team',
            'employee.view.team',
            'property.view.all',
            'leads.view.team',
            'leads.create',
            'sales.view.team',
            'marketing.view.all',
            'campaign.view',
            'campaign.create',
            'campaign.execute',
            'email.marketing',
            'sms.marketing',
            'social.media',
            'analytics.view',
            'reports.view',
            'reports.generate',
            'content.view',
            'pages.manage',
            'media.manage',
            'blog.manage',
            'faq.manage',
            'notifications.send',
            'email.templates',
            'sms.templates',
            'whatsapp.templates',
            'tasks.view',
            'tasks.create',
            'tasks.assign',
        ],
        'construction_director' => [
            'dashboard.view',
            'users.view.team',
            'employee.view.team',
            'property.view.all',
            'property.create',
            'property.edit',
            'leads.view.team',
            'leads.create',
            'sales.view.team',
            'financial.view',
            'expense.view',
            'expense.create',
            'reports.view',
            'reports.generate',
            'tasks.view',
            'tasks.create',
            'tasks.assign',
        ],

        // DEPARTMENTAL LEVEL
        'department_manager' => [
            'dashboard.view',
            'users.view.team',
            'employee.view.team',
            'leads.view.team',
            'leads.create',
            'leads.edit',
            'sales.view.team',
            'financial.view',
            'expense.view',
            'reports.view',
            'tasks.view',
            'tasks.create',
            'tasks.assign',
            'attendance.view',
            'leave.approve',
        ],
        'project_manager' => [
            'dashboard.view',
            'users.view.team',
            'employee.view.team',
            'property.view.all',
            'property.create',
            'property.edit',
            'leads.view.team',
            'leads.create',
            'leads.edit',
            'tasks.view',
            'tasks.create',
            'tasks.assign',
            'tasks.complete',
            'reports.view',
        ],
        'sales_manager' => [
            'dashboard.view',
            'users.view.team',
            'employee.view.team',
            'property.view.all',
            'property.view',
            'leads.view.team',
            'leads.create',
            'leads.edit',
            'leads.assign',
            'sales.view.team',
            'sales.create',
            'commission.view',
            'commission.manage',
            'reports.view',
            'reports.generate',
            'tasks.view',
            'tasks.create',
            'tasks.assign',
            'attendance.view',
            'leave.approve',
        ],
        'hr_manager' => [
            'dashboard.view',
            'users.view.all',
            'employee.view.all',
            'employee.view.team',
            'employee.add',
            'employee.edit',
            'attendance.view',
            'attendance.manage',
            'leave.view',
            'leave.approve',
            'leave.manage',
            'performance.view',
            'performance.manage',
            'training.manage',
            'reports.view',
            'reports.generate',
            'tasks.view',
            'tasks.create',
            'tasks.assign',
        ],
        'marketing_manager' => [
            'dashboard.view',
            'users.view.team',
            'employee.view.team',
            'property.view.all',
            'leads.view.team',
            'leads.create',
            'leads.edit',
            'marketing.view.all',
            'campaign.view',
            'campaign.create',
            'campaign.execute',
            'email.marketing',
            'sms.marketing',
            'social.media',
            'analytics.view',
            'reports.view',
            'content.view',
            'pages.manage',
            'media.manage',
            'blog.manage',
            'faq.manage',
            'notifications.send',
            'email.templates',
            'sms.templates',
            'tasks.view',
            'tasks.create',
            'tasks.assign',
        ],
        'finance_manager' => [
            'dashboard.view',
            'users.view.team',
            'sales.view.team',
            'financial.view',
            'financial.view.all',
            'invoice.create',
            'invoice.view',
            'expense.create',
            'expense.view',
            'emi.manage',
            'payroll.view',
            'reports.view',
            'reports.generate',
            'tasks.view',
            'tasks.create',
        ],
        'property_manager' => [
            'dashboard.view',
            'users.view.team',
            'employee.view.team',
            'property.view.all',
            'property.view',
            'property.create',
            'property.edit',
            'property.delete',
            'property.pricing',
            'leads.view.team',
            'leads.create',
            'leads.edit',
            'sales.view.team',
            'reports.view',
            'tasks.view',
            'tasks.create',
            'tasks.assign',
        ],
        'it_manager' => [
            'dashboard.view',
            'dashboard.customize',
            'dashboard.widgets',
            'users.view.team',
            'employee.view.team',
            'ai.dashboard',
            'ai.settings',
            'chatbot.settings',
            'analytics.view',
            'reports.view',
            'system.settings',
            'backup.manage',
            'security.settings',
            'api.manage',
            'log.viewer',
            'cache.manage',
            'tasks.view',
            'tasks.create',
            'tasks.assign',
        ],
        'operations_manager' => [
            'dashboard.view',
            'users.view.team',
            'employee.view.team',
            'property.view.all',
            'leads.view.team',
            'leads.create',
            'leads.edit',
            'sales.view.team',
            'expense.create',
            'expense.view',
            'reports.view',
            'reports.generate',
            'tasks.view',
            'tasks.create',
            'tasks.assign',
            'attendance.view',
            'leave.approve',
        ],

        // TEAM LEAD LEVEL
        'team_lead' => [
            'dashboard.view',
            'users.view.team',
            'employee.view.team',
            'leads.view.team',
            'leads.create',
            'leads.edit',
            'tasks.view',
            'tasks.create',
            'tasks.assign',
            'tasks.complete',
            'attendance.view',
            'leave.view',
            'reports.view',
        ],
        'telecalling_lead' => [
            'dashboard.view',
            'users.view.team',
            'employee.view.team',
            'leads.view.team',
            'leads.create',
            'leads.edit',
            'leads.assign',
            'tasks.view',
            'tasks.create',
            'tasks.assign',
            'tasks.complete',
            'attendance.view',
            'leave.view',
            'reports.view',
        ],
        'sales_team_lead' => [
            'dashboard.view',
            'users.view.team',
            'employee.view.team',
            'property.view',
            'leads.view.team',
            'leads.create',
            'leads.edit',
            'sales.view.team',
            'sales.create',
            'commission.view',
            'tasks.view',
            'tasks.create',
            'tasks.assign',
            'tasks.complete',
            'attendance.view',
            'leave.view',
            'reports.view',
        ],
        'support_lead' => [
            'dashboard.view',
            'users.view.team',
            'employee.view.team',
            'support.view',
            'support.reply',
            'support.escalate',
            'tasks.view',
            'tasks.create',
            'tasks.assign',
            'tasks.complete',
            'attendance.view',
            'leave.view',
            'reports.view',
        ],

        // SENIOR STAFF LEVEL
        'senior_accountant' => [
            'dashboard.view',
            'financial.view',
            'financial.view.all',
            'invoice.create',
            'invoice.view',
            'expense.create',
            'expense.view',
            'emi.manage',
            'payroll.view',
            'reports.view',
            'reports.generate',
        ],
        'senior_developer' => [
            'dashboard.view',
            'tasks.view',
            'tasks.create',
            'tasks.complete',
            'analytics.view',
            'api.manage',
        ],
        'legal_advisor' => [
            'dashboard.view',
            'reports.view',
            'tasks.view',
            'tasks.create',
        ],
        'chartered_accountant' => [
            'dashboard.view',
            'financial.view',
            'financial.view.all',
            'invoice.view',
            'emi.manage',
            'payroll.view',
            'tax.manage',
            'reports.view',
            'reports.generate',
            'reports.export',
        ],

        // STAFF LEVEL
        'accountant' => [
            'dashboard.view',
            'financial.view',
            'invoice.create',
            'invoice.view',
            'expense.create',
            'expense.view',
            'reports.view',
        ],
        'developer' => [
            'dashboard.view',
            'tasks.view',
            'tasks.complete',
        ],
        'content_writer' => [
            'dashboard.view',
            'property.view',
            'leads.view.own',
            'content.view',
            'blog.manage',
            'tasks.view',
            'tasks.complete',
        ],
        'graphic_designer' => [
            'dashboard.view',
            'property.view',
            'content.view',
            'media.manage',
            'tasks.view',
            'tasks.complete',
        ],
        'data_entry_operator' => [
            'dashboard.view',
            'tasks.view',
            'tasks.complete',
        ],
        'backoffice_staff' => [
            'dashboard.view',
            'tasks.view',
            'tasks.complete',
        ],

        // TELECALLING LEVEL
        'telecaller' => [
            'dashboard.view',
            'leads.view.own',
            'leads.create',
            'tasks.view',
            'tasks.complete',
            'attendance.view',
        ],
        'telecalling_executive' => [
            'dashboard.view',
            'leads.view.own',
            'leads.create',
            'tasks.view',
            'tasks.complete',
            'attendance.view',
            'reports.view',
        ],
        'support_executive' => [
            'dashboard.view',
            'leads.view.own',
            'support.view',
            'support.reply',
            'tasks.view',
            'tasks.complete',
            'attendance.view',
        ],

        // MLM/ASSOCIATE LEVEL
        'senior_associate' => [
            'dashboard.view',
            'property.view',
            'leads.view.own',
            'leads.create',
            'sales.create',
            'commission.view',
            'mlm.tree.view',
            'mlm.downline.view',
            'tasks.view',
            'tasks.complete',
            'reports.view',
        ],
        'associate_team_lead' => [
            'dashboard.view',
            'property.view',
            'leads.view.own',
            'leads.create',
            'sales.create',
            'commission.view',
            'commission.manage',
            'mlm.tree.view',
            'mlm.downline.view',
            'tasks.view',
            'tasks.complete',
            'reports.view',
        ],
        'associate' => [
            'dashboard.view',
            'property.view',
            'leads.view.own',
            'leads.create',
            'sales.create',
            'commission.view',
            'mlm.tree.view',
            'tasks.view',
            'tasks.complete',
        ],

        // AGENT LEVEL
        'senior_agent' => [
            'dashboard.view',
            'users.view.team',
            'property.view',
            'leads.view.own',
            'leads.create',
            'sales.create',
            'sales.view',
            'commission.view',
            'tasks.view',
            'tasks.complete',
            'reports.view',
        ],
        'agent' => [
            'dashboard.view',
            'property.view',
            'leads.view.own',
            'leads.create',
            'sales.create',
            'sales.view',
            'commission.view',
            'tasks.view',
            'tasks.complete',
        ],

        // FRANCHISE LEVEL
        'franchise_owner' => [
            'dashboard.view',
            'users.view.team',
            'property.view.all',
            'property.create',
            'property.edit',
            'leads.view.team',
            'leads.create',
            'leads.edit',
            'sales.view.team',
            'sales.create',
            'commission.view',
            'reports.view',
            'reports.generate',
            'tasks.view',
            'tasks.create',
        ],

        // CUSTOMER LEVEL
        'premium_customer' => [
            'dashboard.view',
            'property.view',
            'support.view',
            'support.reply',
            'reports.view',
        ],
        'verified_customer' => [
            'dashboard.view',
            'property.view',
            'support.view',
            'support.reply',
        ],
        'guest_customer' => [
            'dashboard.view',
            'property.view',
            'support.view',
        ],

        // LEAD LEVEL
        'hot_lead' => [
            'property.view',
            'support.view',
        ],
        'warm_lead' => [
            'property.view',
        ],
        'cold_lead' => [
            'property.view',
        ],

        // GUEST LEVEL
        'guest' => [
            'property.view',
        ],

        // LEGACY ALIASES
        'admin' => [
            'dashboard.view',
            'dashboard.widgets',
            'users.view.all',
            'users.create',
            'users.edit',
            'property.view.all',
            'property.create',
            'property.edit',
            'property.delete',
            'leads.view.all',
            'leads.create',
            'leads.edit',
            'leads.delete',
            'leads.assign',
            'sales.view.all',
            'commission.view',
            'commission.manage',
            'financial.view',
            'invoice.create',
            'invoice.view',
            'employee.view.all',
            'employee.add',
            'employee.edit',
            'attendance.view',
            'attendance.manage',
            'leave.approve',
            'marketing.view.all',
            'campaign.view',
            'campaign.create',
            'campaign.execute',
            'content.view',
            'pages.manage',
            'media.manage',
            'blog.manage',
            'ai.dashboard',
            'ai.property.valuation',
            'ai.lead.scoring',
            'reports.view',
            'reports.generate',
            'reports.export',
            'notifications.send',
            'email.templates',
            'sms.templates',
            'whatsapp.templates',
            'support.view',
            'support.reply',
            'support.escalate',
            'tasks.view',
            'tasks.create',
            'tasks.assign',
        ],
        'manager' => [
            'dashboard.view',
            'users.view.team',
            'employee.view.team',
            'property.view.all',
            'property.view',
            'leads.view.team',
            'leads.create',
            'leads.edit',
            'leads.assign',
            'sales.view.team',
            'commission.view',
            'financial.view',
            'expense.view',
            'attendance.view',
            'leave.approve',
            'reports.view',
            'reports.generate',
            'tasks.view',
            'tasks.create',
            'tasks.assign',
        ],
        'user' => [
            'dashboard.view',
            'property.view',
            'leads.view.own',
            'support.view',
        ],
    ];

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Get all roles
     */
    public static function getRoles(): array
    {
        return self::$roles;
    }

    /**
     * Get role info
     */
    public static function getRoleInfo(string $role): ?array
    {
        return self::$roles[$role] ?? null;
    }

    /**
     * Get role name
     */
    public static function getRoleName(string $role): string
    {
        return self::$roles[$role]['name'] ?? ucfirst(str_replace('_', ' ', $role));
    }

    /**
     * Get role level
     */
    public static function getRoleLevel(string $role): int
    {
        return self::$roles[$role]['level'] ?? 99;
    }

    /**
     * Get role category
     */
    public static function getRoleCategory(string $role): string
    {
        return self::$roles[$role]['category'] ?? 'Unknown';
    }

    /**
     * Get all permissions
     */
    public static function getPermissions(): array
    {
        return self::$permissions;
    }

    /**
     * Get permission name
     */
    public static function getPermissionName(string $permission): string
    {
        return self::$permissions[$permission] ?? ucfirst(str_replace('.', ' ', $permission));
    }

    /**
     * Get permissions for a role
     */
    public static function getRolePermissions(string $role): array
    {
        // Super admin has all permissions
        if ($role === self::ROLE_SUPER_ADMIN) {
            return array_keys(self::$permissions);
        }

        return self::$rolePermissions[$role] ?? [];
    }

    /**
     * Check if user has permission
     */
    public static function hasPermission(string $permission, ?string $userId = null): bool
    {
        $userRole = self::getUserRole($userId);

        if (!$userRole) {
            return false;
        }

        // Super admin has all permissions
        if ($userRole === self::ROLE_SUPER_ADMIN) {
            return true;
        }

        $permissions = self::getRolePermissions($userRole);
        return in_array($permission, $permissions);
    }

    /**
     * Check if user has any permission from array
     */
    public static function hasAnyPermission(array $permissions, ?string $userId = null): bool
    {
        foreach ($permissions as $permission) {
            if (self::hasPermission($permission, $userId)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has all permissions from array
     */
    public static function hasAllPermissions(array $permissions, ?string $userId = null): bool
    {
        foreach ($permissions as $permission) {
            if (!self::hasPermission($permission, $userId)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get user role from session or database
     */
    public static function getUserRole(?string $userId = null): ?string
    {
        if ($userId) {
            try {
                $db = Database::getInstance();
                $user = $db->fetch("SELECT role FROM users WHERE id = ?", [$userId]);
                return $user ? $user['role'] : null;
            } catch (Exception $e) {
                error_log("RBAC getUserRole error: " . $e->getMessage());
                return null;
            }
        }

        return $_SESSION['admin_role'] ?? $_SESSION['user_role'] ?? null;
    }

    /**
     * Set user role in session
     */
    public static function setUserRole(string $role, ?string $userId = null): void
    {
        $_SESSION['admin_role'] = $role;
        $_SESSION['user_role'] = $role;

        if ($userId) {
            try {
                $db = Database::getInstance();
                $db->query("UPDATE users SET role = ? WHERE id = ?", [$role, $userId]);
            } catch (Exception $e) {
                error_log("RBAC setUserRole error: " . $e->getMessage());
            }
        }
    }

    /**
     * Check if role has higher or equal level
     */
    public static function hasRoleLevel(string $userRole, string $requiredRole): bool
    {
        return self::getRoleLevel($userRole) <= self::getRoleLevel($requiredRole);
    }

    /**
     * Get roles by category
     */
    public static function getRolesByCategory(string $category): array
    {
        $result = [];
        foreach (self::$roles as $role => $info) {
            if ($info['category'] === $category) {
                $result[$role] = $info;
            }
        }
        return $result;
    }

    /**
     * Get all categories
     */
    public static function getCategories(): array
    {
        $categories = [];
        foreach (self::$roles as $role => $info) {
            $categories[$info['category']] = $info['category'];
        }
        return $categories;
    }

    /**
     * Get dashboard type for role
     */
    public static function getDashboardType(string $role): string
    {
        $category = self::getRoleCategory($role);

        return match ($category) {
            'Executive' => 'executive',
            'Management', 'Departmental' => 'manager',
            'Team Lead' => 'team_lead',
            'Senior Staff', 'Staff' => 'employee',
            'MLM', 'Agent' => 'associate',
            'Franchise' => 'franchise',
            'Customer' => 'customer',
            'Lead' => 'lead',
            'Guest' => 'guest',
            default => 'default',
        };
    }

    /**
     * Check if role can access admin panel
     */
    public static function canAccessAdmin(string $role): bool
    {
        $level = self::getRoleLevel($role);
        return $level <= 10; // Level 10 and below can access
    }

    /**
     * Check if role can manage users
     */
    public static function canManageUsers(string $role): bool
    {
        return self::hasPermission('users.create', self::getUserRole()) ||
            self::hasPermission('users.edit', self::getUserRole()) ||
            self::hasPermission('users.delete', self::getUserRole());
    }

    /**
     * Check if role can view financial data
     */
    public static function canViewFinancials(string $role): bool
    {
        return self::hasPermission('financial.view', self::getUserRole());
    }

    /**
     * Check if role can manage properties
     */
    public static function canManageProperties(string $role): bool
    {
        return self::hasPermission('property.create', self::getUserRole()) ||
            self::hasPermission('property.edit', self::getUserRole()) ||
            self::hasPermission('property.delete', self::getUserRole());
    }

    /**
     * Check if role can access system settings
     */
    public static function canAccessSystemSettings(string $role): bool
    {
        return self::hasPermission('system.settings', self::getUserRole());
    }

    /**
     * Validate role string
     */
    public static function isValidRole(string $role): bool
    {
        return isset(self::$roles[$role]);
    }

    /**
     * Get all valid roles
     */
    public static function getAllRoles(): array
    {
        return array_keys(self::$roles);
    }

    /**
     * Get users by role (from database)
     */
    public static function getUsersByRole(string $role): array
    {
        try {
            $db = Database::getInstance();
            // PERFORMANCE: Select only needed columns instead of SELECT *
            return $db->fetchAll("SELECT id, name, email, role, status FROM users WHERE role = ?", [$role]);
        } catch (Exception $e) {
            error_log("RBAC getUsersByRole error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get role statistics
     */
    public static function getRoleStats(): array
    {
        $stats = [];
        foreach (self::$roles as $role => $info) {
            $count = count(self::getUsersByRole($role));
            $stats[$role] = [
                'name' => $info['name'],
                'count' => $count,
                'category' => $info['category'],
                'level' => $info['level']
            ];
        }
        return $stats;
    }
}

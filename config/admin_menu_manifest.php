<?php
/**
 * Admin menu manifest — future-proof static snapshot of admin_menu_items.
 * Generated: 2026-09-23 18:05:17 | items: 289
 *
 * Used for: (1) sidebar fallback when DB menu is unreachable/empty,
 * (2) self-heal re-seeding of missing menu rows + role permissions.
 * Keyed by URL (stable) — never by auto-increment id.
 */
return array (
  '/admin/erp' => 
  array (
    'name' => 'ERP Overview',
    'icon' => 'fas fa-th-large',
    'section' => 'dashboards',
    'parent_url' => NULL,
    'order' => 1,
    'perm' => 'dashboard.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'department_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'operations_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'telecalling_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'support_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'legal_advisor' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'content_writer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'graphic_designer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'data_entry_operator' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'backoffice_staff' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecalling_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'support_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/leads' => 
  array (
    'name' => 'Leads Manager',
    'icon' => 'fas fa-user-plus',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 1,
    'perm' => 'leads.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'telecalling_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'data_entry_operator' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'backoffice_staff' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecalling_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/properties' => 
  array (
    'name' => 'All Properties',
    'icon' => 'fas fa-building',
    'section' => 'properties',
    'parent_url' => NULL,
    'order' => 1,
    'perm' => 'property.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'operations_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/plots' => 
  array (
    'name' => 'Plots Inventory',
    'icon' => 'fas fa-th-large',
    'section' => 'plots',
    'parent_url' => NULL,
    'order' => 1,
    'perm' => 'plots.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'operations_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/land-inventory/acquisitions' => 
  array (
    'name' => 'Land Acquisitions',
    'icon' => 'fas fa-handshake',
    'section' => 'land',
    'parent_url' => NULL,
    'order' => 1,
    'perm' => 'land.acquisitions.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/colonies' => 
  array (
    'name' => 'Colonies Board',
    'icon' => 'fa-city',
    'section' => 'locations',
    'parent_url' => '/admin/locations/states',
    'order' => 3,
    'perm' => 'locations.colonies',
    'roles' => 
    array (
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/projects' => 
  array (
    'name' => 'Projects List',
    'icon' => 'fas fa-city',
    'section' => 'projects',
    'parent_url' => NULL,
    'order' => 1,
    'perm' => 'projects.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'operations_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/mlm' => 
  array (
    'name' => 'MLM Dashboard',
    'icon' => 'fas fa-chart-pie',
    'section' => 'mlm',
    'parent_url' => NULL,
    'order' => 1,
    'perm' => 'mlm.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'senior_associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/payments' => 
  array (
    'name' => 'Payments Ledger',
    'icon' => 'fas fa-credit-card',
    'section' => 'finance',
    'parent_url' => NULL,
    'order' => 1,
    'perm' => 'financial.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/legal/disputes' => 
  array (
    'name' => 'Disputes Board',
    'icon' => 'fas fa-balance-scale',
    'section' => 'legal',
    'parent_url' => NULL,
    'order' => 1,
    'perm' => 'legal.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'legal_advisor' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/marketing/strategies' => 
  array (
    'name' => 'Marketing Strategies',
    'icon' => 'fas fa-chess',
    'section' => 'marketing',
    'parent_url' => NULL,
    'order' => 1,
    'perm' => 'marketing.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/pages' => 
  array (
    'name' => 'Pages Content',
    'icon' => 'fas fa-file',
    'section' => 'cms',
    'parent_url' => NULL,
    'order' => 1,
    'perm' => 'pages.manage',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'content_writer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'graphic_designer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/services' => 
  array (
    'name' => 'Service Enquiries',
    'icon' => 'fas fa-concierge-bell',
    'section' => 'services',
    'parent_url' => NULL,
    'order' => 1,
    'perm' => 'services.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'operations_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/users' => 
  array (
    'name' => 'All Users List',
    'icon' => 'fas fa-users',
    'section' => 'users',
    'parent_url' => NULL,
    'order' => 1,
    'perm' => 'users.view.all',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/settings' => 
  array (
    'name' => 'General Settings',
    'icon' => 'fas fa-sliders-h',
    'section' => 'settings',
    'parent_url' => NULL,
    'order' => 1,
    'perm' => 'system.settings',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'department_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'operations_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/reports' => 
  array (
    'name' => 'Reports Engine',
    'icon' => 'fas fa-file-alt',
    'section' => 'reports',
    'parent_url' => NULL,
    'order' => 1,
    'perm' => 'reports.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'department_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'operations_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'telecalling_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'support_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'legal_advisor' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'content_writer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'graphic_designer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'backoffice_staff' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'support_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/features/security' => 
  array (
    'name' => 'Security Center Guard',
    'icon' => 'fas fa-shield-alt',
    'section' => 'system',
    'parent_url' => NULL,
    'order' => 1,
    'perm' => 'system.settings',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/directory' => 
  array (
    'name' => 'Services Directory',
    'icon' => 'fas fa-store',
    'section' => 'services',
    'parent_url' => NULL,
    'order' => 1,
    'perm' => 'properties.directory',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/backoffice' => 
  array (
    'name' => 'Backoffice Dashboard',
    'icon' => 'fas fa-clipboard-list',
    'section' => 'operations',
    'parent_url' => NULL,
    'order' => 1,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'department_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'operations_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'backoffice_staff' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/legal/dashboard' => 
  array (
    'name' => 'Legal Documentation',
    'icon' => 'fas fa-file-contract',
    'section' => 'legal',
    'parent_url' => NULL,
    'order' => 1,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'legal_advisor' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/hrm/departments' => 
  array (
    'name' => 'Departments',
    'icon' => 'fas fa-building',
    'section' => 'hrm',
    'parent_url' => NULL,
    'order' => 1,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'department_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/commission/recalculations' => 
  array (
    'name' => 'Recalculations',
    'icon' => 'fas fa-calculator',
    'section' => 'commission',
    'parent_url' => '/admin/mlm',
    'order' => 1,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/communication/automation' => 
  array (
    'name' => 'Communication Automation',
    'icon' => 'fas fa-bolt',
    'section' => 'communication',
    'parent_url' => NULL,
    'order' => 1,
    'perm' => 'communication.automation',
    'roles' => 
    array (
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'operations_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/tenants/dashboard' => 
  array (
    'name' => 'Tenant Dashboard',
    'icon' => 'fas fa-cloud',
    'section' => 'saas',
    'parent_url' => NULL,
    'order' => 1,
    'perm' => 'super_admin',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/property-valuations' => 
  array (
    'name' => 'Valuation Reports',
    'icon' => 'fas fa-file-contract',
    'section' => 'valuations',
    'parent_url' => NULL,
    'order' => 1,
    'perm' => 'property_valuation_view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/site-visits' => 
  array (
    'name' => 'Site Visits',
    'icon' => 'fas fa-calendar-alt',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 5,
    'perm' => 'visits.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/locations/states' => 
  array (
    'name' => 'Locations',
    'icon' => 'fa-map',
    'section' => 'locations',
    'parent_url' => NULL,
    'order' => 10,
    'perm' => 'locations.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/lead-kanban' => 
  array (
    'name' => 'Lead Kanban',
    'icon' => 'fas fa-columns',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 2,
    'perm' => 'leads.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'telecalling_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'telecalling_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/colony-pipeline' => 
  array (
    'name' => 'Colony Pipeline',
    'icon' => 'fas fa-stream',
    'section' => 'colonies',
    'parent_url' => NULL,
    'order' => 2,
    'perm' => 'property.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/plots/categories' => 
  array (
    'name' => 'Plot Categories',
    'icon' => 'fas fa-tags',
    'section' => 'plots',
    'parent_url' => NULL,
    'order' => 2,
    'perm' => 'plots.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/land/records' => 
  array (
    'name' => 'Land Records',
    'icon' => 'fas fa-file-invoice',
    'section' => 'land',
    'parent_url' => NULL,
    'order' => 2,
    'perm' => 'land.records.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/resell-properties' => 
  array (
    'name' => 'Resell Properties',
    'icon' => 'fas fa-exchange-alt',
    'section' => 'properties',
    'parent_url' => NULL,
    'order' => 2,
    'perm' => 'resell.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/projects/progress' => 
  array (
    'name' => 'Project Progress',
    'icon' => 'fas fa-tasks',
    'section' => 'projects',
    'parent_url' => NULL,
    'order' => 2,
    'perm' => 'projects.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/mlm/genealogy' => 
  array (
    'name' => 'Genealogy Tree',
    'icon' => 'fas fa-sitemap',
    'section' => 'mlm',
    'parent_url' => NULL,
    'order' => 2,
    'perm' => 'mlm.tree.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/invoices' => 
  array (
    'name' => 'Invoices Billing',
    'icon' => 'fas fa-file-invoice',
    'section' => 'finance',
    'parent_url' => NULL,
    'order' => 2,
    'perm' => 'invoice.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/payroll' => 
  array (
    'name' => 'Payroll Management',
    'icon' => 'fas fa-calculator',
    'section' => 'hrm',
    'parent_url' => NULL,
    'order' => 2,
    'perm' => 'payroll.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/legal/deadlines' => 
  array (
    'name' => 'Legal Deadlines',
    'icon' => 'fas fa-clock',
    'section' => 'legal',
    'parent_url' => NULL,
    'order' => 2,
    'perm' => 'legal.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'legal_advisor' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/marketing/marketplace' => 
  array (
    'name' => 'Marketplace Listings',
    'icon' => 'fas fa-store',
    'section' => 'marketing',
    'parent_url' => NULL,
    'order' => 2,
    'perm' => 'marketing.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/blog' => 
  array (
    'name' => 'Blogs Manager',
    'icon' => 'fas fa-newspaper',
    'section' => 'cms',
    'parent_url' => NULL,
    'order' => 2,
    'perm' => 'blog.manage',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'content_writer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'graphic_designer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/service-configs' => 
  array (
    'name' => 'Service Configuration',
    'icon' => 'fas fa-cogs',
    'section' => 'services',
    'parent_url' => NULL,
    'order' => 2,
    'perm' => 'system.settings',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'operations_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/roles' => 
  array (
    'name' => 'Role Settings',
    'icon' => 'fas fa-user-tag',
    'section' => 'users',
    'parent_url' => NULL,
    'order' => 2,
    'perm' => 'roles.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/godmode' => 
  array (
    'name' => 'SuperAdmin Console',
    'icon' => 'fas fa-user-secret',
    'section' => 'settings',
    'parent_url' => NULL,
    'order' => 2,
    'perm' => 'system.settings',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/analytics' => 
  array (
    'name' => 'Analytics Dashboard',
    'icon' => 'fas fa-chart-line',
    'section' => 'reports',
    'parent_url' => NULL,
    'order' => 2,
    'perm' => 'analytics.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'department_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'operations_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'telecalling_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'support_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'legal_advisor' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'content_writer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'graphic_designer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'backoffice_staff' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'support_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/audit-log' => 
  array (
    'name' => 'Audit logs Tracker',
    'icon' => 'fas fa-clipboard-list',
    'section' => 'system',
    'parent_url' => NULL,
    'order' => 2,
    'perm' => 'system.settings',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/directory/categories' => 
  array (
    'name' => 'Directory Categories',
    'icon' => 'fas fa-folder-open',
    'section' => 'services',
    'parent_url' => NULL,
    'order' => 2,
    'perm' => 'properties.directory',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/legal/templates' => 
  array (
    'name' => 'Document Templates',
    'icon' => 'fas fa-file',
    'section' => 'legal',
    'parent_url' => NULL,
    'order' => 2,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'legal_advisor' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/hrm/designations' => 
  array (
    'name' => 'Designations',
    'icon' => 'fas fa-user-tag',
    'section' => 'hrm',
    'parent_url' => NULL,
    'order' => 2,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'department_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/communication/whatsapp-setup' => 
  array (
    'name' => 'WhatsApp Webhook',
    'icon' => 'fab fa-whatsapp',
    'section' => 'communication',
    'parent_url' => NULL,
    'order' => 2,
    'perm' => 'communication.whatsapp',
    'roles' => 
    array (
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'operations_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/tenants' => 
  array (
    'name' => 'Tenant Management',
    'icon' => 'fas fa-building',
    'section' => 'saas',
    'parent_url' => NULL,
    'order' => 2,
    'perm' => 'super_admin',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/property-valuations/generate' => 
  array (
    'name' => 'Generate Valuation',
    'icon' => 'fas fa-calculator',
    'section' => 'valuations',
    'parent_url' => NULL,
    'order' => 2,
    'perm' => 'property_valuation_generate',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/locations/districts' => 
  array (
    'name' => 'Districts Board',
    'icon' => 'fa-map-marked-alt',
    'section' => 'locations',
    'parent_url' => '/admin/locations/states',
    'order' => 2,
    'perm' => 'locations.districts',
    'roles' => 
    array (
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/dashboard/ceo' => 
  array (
    'name' => 'CEO Dashboard',
    'icon' => 'fas fa-user-tie',
    'section' => 'dashboards',
    'parent_url' => NULL,
    'order' => 3,
    'perm' => 'dashboard.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/leads/scoring' => 
  array (
    'name' => 'Lead Scoring',
    'icon' => 'fas fa-star',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 3,
    'perm' => 'leads.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'telecalling_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'telecalling_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/colony-feasibility' => 
  array (
    'name' => 'Colony Feasibility',
    'icon' => 'fas fa-chart-pie',
    'section' => 'colonies',
    'parent_url' => NULL,
    'order' => 3,
    'perm' => 'property.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/land-inventory/leads' => 
  array (
    'name' => 'Land Leads',
    'icon' => 'fas fa-bullseye',
    'section' => 'land',
    'parent_url' => NULL,
    'order' => 3,
    'perm' => 'land.leads.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/sites' => 
  array (
    'name' => 'Sites Management',
    'icon' => 'fas fa-map-marked-alt',
    'section' => 'projects',
    'parent_url' => NULL,
    'order' => 3,
    'perm' => 'sites.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'operations_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/user-properties' => 
  array (
    'name' => 'User Properties',
    'icon' => 'fas fa-user-check',
    'section' => 'properties',
    'parent_url' => NULL,
    'order' => 3,
    'perm' => 'user.properties.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'data_entry_operator' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/bulk/property-import' => 
  array (
    'name' => 'Bulk Property Import',
    'icon' => 'fas fa-file-import',
    'section' => 'plots',
    'parent_url' => NULL,
    'order' => 3,
    'perm' => 'property.import',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/mlm/associates' => 
  array (
    'name' => 'All Associates',
    'icon' => 'fas fa-user-tie',
    'section' => 'mlm',
    'parent_url' => NULL,
    'order' => 3,
    'perm' => 'mlm.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/expense' => 
  array (
    'name' => 'Expenses Tracking',
    'icon' => 'fas fa-receipt',
    'section' => 'finance',
    'parent_url' => NULL,
    'order' => 3,
    'perm' => 'expense.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/backoffice/attendance' => 
  array (
    'name' => 'Attendance Register',
    'icon' => 'fas fa-user-clock',
    'section' => 'hrm',
    'parent_url' => NULL,
    'order' => 3,
    'perm' => 'hrm.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/sales/rera' => 
  array (
    'name' => 'RERA Compliance',
    'icon' => 'fas fa-gavel',
    'section' => 'legal',
    'parent_url' => NULL,
    'order' => 3,
    'perm' => 'legal.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'legal_advisor' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/gallery' => 
  array (
    'name' => 'Gallery Images',
    'icon' => 'fas fa-images',
    'section' => 'cms',
    'parent_url' => NULL,
    'order' => 3,
    'perm' => 'media.manage',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'content_writer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'graphic_designer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/activity-log' => 
  array (
    'name' => 'Activity History Log',
    'icon' => 'fas fa-history',
    'section' => 'settings',
    'parent_url' => NULL,
    'order' => 3,
    'perm' => 'system.settings',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/pdfs' => 
  array (
    'name' => 'Document Generator',
    'icon' => 'fas fa-file-pdf',
    'section' => 'reports',
    'parent_url' => NULL,
    'order' => 3,
    'perm' => 'reports.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'department_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'operations_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'telecalling_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'support_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'legal_advisor' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'content_writer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'graphic_designer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'backoffice_staff' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'support_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/system-health' => 
  array (
    'name' => 'System Health Monitor',
    'icon' => 'fas fa-heartbeat',
    'section' => 'system',
    'parent_url' => NULL,
    'order' => 3,
    'perm' => 'system.settings',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/directory/listings' => 
  array (
    'name' => 'Manage Listings',
    'icon' => 'fas fa-clipboard-list',
    'section' => 'services',
    'parent_url' => NULL,
    'order' => 3,
    'perm' => 'properties.directory',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/legal/clauses' => 
  array (
    'name' => 'Clause Library',
    'icon' => 'fas fa-list',
    'section' => 'legal',
    'parent_url' => NULL,
    'order' => 3,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'legal_advisor' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/communication/telegram-setup' => 
  array (
    'name' => 'Telegram Bot',
    'icon' => 'fab fa-telegram',
    'section' => 'communication',
    'parent_url' => NULL,
    'order' => 3,
    'perm' => 'communication.telegram',
    'roles' => 
    array (
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'operations_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/billing' => 
  array (
    'name' => 'Billing & Subscriptions',
    'icon' => 'fas fa-credit-card',
    'section' => 'saas',
    'parent_url' => NULL,
    'order' => 3,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/dashboard/cfo' => 
  array (
    'name' => 'CFO Dashboard',
    'icon' => 'fas fa-wallet',
    'section' => 'dashboards',
    'parent_url' => NULL,
    'order' => 4,
    'perm' => 'dashboard.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/deals' => 
  array (
    'name' => 'Deals Board',
    'icon' => 'fas fa-handshake',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 4,
    'perm' => 'deals.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/land-inventory/brokers' => 
  array (
    'name' => 'Land Brokers',
    'icon' => 'fas fa-user-friends',
    'section' => 'land',
    'parent_url' => NULL,
    'order' => 4,
    'perm' => 'land.brokers.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/commission' => 
  array (
    'name' => 'Commissions Ledger',
    'icon' => 'fas fa-coins',
    'section' => 'mlm',
    'parent_url' => NULL,
    'order' => 4,
    'perm' => 'commission.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/finance/cash-book' => 
  array (
    'name' => 'Cash Book',
    'icon' => 'fas fa-book',
    'section' => 'finance',
    'parent_url' => NULL,
    'order' => 4,
    'perm' => 'financial.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/telecaller' => 
  array (
    'name' => 'Telecaller Overrides',
    'icon' => 'fas fa-phone-volume',
    'section' => 'hrm',
    'parent_url' => NULL,
    'order' => 4,
    'perm' => 'telecallers.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'telecalling_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'telecalling_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/testimonials' => 
  array (
    'name' => 'Testimonials Manager',
    'icon' => 'fas fa-quote-left',
    'section' => 'cms',
    'parent_url' => NULL,
    'order' => 4,
    'perm' => 'testimonials.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'content_writer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'graphic_designer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/features/registrations' => 
  array (
    'name' => 'User Registrations',
    'icon' => 'fas fa-user-plus',
    'section' => 'users',
    'parent_url' => NULL,
    'order' => 4,
    'perm' => 'users.view.all',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/settings/email' => 
  array (
    'name' => 'Email SMTP Settings',
    'icon' => 'fas fa-envelope',
    'section' => 'settings',
    'parent_url' => NULL,
    'order' => 4,
    'perm' => 'system.settings',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/saved-searches' => 
  array (
    'name' => 'Saved Searches Query',
    'icon' => 'fas fa-search-plus',
    'section' => 'reports',
    'parent_url' => NULL,
    'order' => 4,
    'perm' => 'reports.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'department_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'operations_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'telecalling_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'support_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'legal_advisor' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'content_writer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'graphic_designer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'backoffice_staff' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'support_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/backup' => 
  array (
    'name' => 'Database Backup Utility',
    'icon' => 'fas fa-database',
    'section' => 'system',
    'parent_url' => NULL,
    'order' => 4,
    'perm' => 'system.settings',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/directory/reviews' => 
  array (
    'name' => 'Review Moderation',
    'icon' => 'fas fa-star-half-alt',
    'section' => 'services',
    'parent_url' => NULL,
    'order' => 4,
    'perm' => 'properties.directory',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/company-loans/offers' => 
  array (
    'name' => 'Loan Offers',
    'icon' => 'fas fa-tags',
    'section' => 'legal',
    'parent_url' => NULL,
    'order' => 4,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'legal_advisor' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/legal/ai-composer' => 
  array (
    'name' => 'AI Document Composer',
    'icon' => 'fas fa-robot',
    'section' => 'legal',
    'parent_url' => NULL,
    'order' => 4,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'legal_advisor' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/communication/sms-setup' => 
  array (
    'name' => 'SMS Gateway',
    'icon' => 'fas fa-sms',
    'section' => 'communication',
    'parent_url' => NULL,
    'order' => 4,
    'perm' => 'communication.sms',
    'roles' => 
    array (
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'operations_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/legal-colony-pipeline' => 
  array (
    'name' => 'Legal Colony Pipeline',
    'icon' => 'fas fa-balance-scale',
    'section' => 'colonies',
    'parent_url' => NULL,
    'order' => 4,
    'perm' => 'admin',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/billing/plans' => 
  array (
    'name' => 'Manage Plans',
    'icon' => 'fas fa-tags',
    'section' => 'saas',
    'parent_url' => NULL,
    'order' => 4,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/listing-settings/inquiries' => 
  array (
    'name' => 'Property Inquiries',
    'icon' => 'fas fa-comments',
    'section' => 'properties',
    'parent_url' => NULL,
    'order' => 4,
    'perm' => 'listing_inquiries',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/tools/landmarks' => 
  array (
    'name' => 'Landmarks & Distances',
    'icon' => 'fa-map-marker-alt',
    'section' => 'locations',
    'parent_url' => '/admin/locations/states',
    'order' => 4,
    'perm' => 'tools.landmarks',
    'roles' => 
    array (
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/dashboard/finance' => 
  array (
    'name' => 'Finance Dashboard',
    'icon' => 'fas fa-piggy-bank',
    'section' => 'dashboards',
    'parent_url' => NULL,
    'order' => 5,
    'perm' => 'dashboard.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/property-alerts' => 
  array (
    'name' => 'Property Alerts',
    'icon' => 'fas fa-bell',
    'section' => 'properties',
    'parent_url' => NULL,
    'order' => 5,
    'perm' => 'property.alerts.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/payouts' => 
  array (
    'name' => 'Payouts Manager',
    'icon' => 'fas fa-money-bill-wave',
    'section' => 'mlm',
    'parent_url' => NULL,
    'order' => 5,
    'perm' => 'payouts.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'associate_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/finance/reconciliation' => 
  array (
    'name' => 'Bank Reconciliation',
    'icon' => 'fas fa-exchange-alt',
    'section' => 'finance',
    'parent_url' => NULL,
    'order' => 5,
    'perm' => 'financial.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/training/courses' => 
  array (
    'name' => 'Training Courses',
    'icon' => 'fas fa-graduation-cap',
    'section' => 'hrm',
    'parent_url' => NULL,
    'order' => 5,
    'perm' => 'training.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/voice-scheduler' => 
  array (
    'name' => 'Voice Scheduler',
    'icon' => 'fas fa-microphone',
    'section' => 'marketing',
    'parent_url' => NULL,
    'order' => 5,
    'perm' => 'marketing.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'telecalling_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'telecalling_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/faqs' => 
  array (
    'name' => 'FAQs Manager',
    'icon' => 'fas fa-question-circle',
    'section' => 'cms',
    'parent_url' => NULL,
    'order' => 5,
    'perm' => 'faq.manage',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'content_writer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/settings/sms' => 
  array (
    'name' => 'SMS Gateway Settings',
    'icon' => 'fas fa-sms',
    'section' => 'settings',
    'parent_url' => NULL,
    'order' => 5,
    'perm' => 'system.settings',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/cache' => 
  array (
    'name' => 'Clear Cache Tool',
    'icon' => 'fas fa-broom',
    'section' => 'system',
    'parent_url' => NULL,
    'order' => 5,
    'perm' => 'system.settings',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/directory/jobs' => 
  array (
    'name' => 'Jobs',
    'icon' => 'fas fa-briefcase',
    'section' => 'services',
    'parent_url' => NULL,
    'order' => 5,
    'perm' => 'properties.directory',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/careers' => 
  array (
    'name' => 'Career Management',
    'icon' => 'fas fa-briefcase',
    'section' => 'hrm',
    'parent_url' => NULL,
    'order' => 5,
    'perm' => 'careers',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'legal_advisor' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/legal/ai-prompts' => 
  array (
    'name' => 'AI Prompt Templates',
    'icon' => 'fas fa-brain',
    'section' => 'legal',
    'parent_url' => NULL,
    'order' => 5,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'legal_advisor' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/communication/email-templates' => 
  array (
    'name' => 'Email Templates',
    'icon' => 'fas fa-envelope',
    'section' => 'communication',
    'parent_url' => NULL,
    'order' => 5,
    'perm' => 'communication.email',
    'roles' => 
    array (
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'operations_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/legal-colony-pipeline/health' => 
  array (
    'name' => 'Colony Health',
    'icon' => 'fas fa-heartbeat',
    'section' => 'colonies',
    'parent_url' => NULL,
    'order' => 5,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/dashboard/sales' => 
  array (
    'name' => 'Sales Dashboard',
    'icon' => 'fas fa-chart-line',
    'section' => 'dashboards',
    'parent_url' => NULL,
    'order' => 6,
    'perm' => 'dashboard.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/inquiries' => 
  array (
    'name' => 'Enquiries',
    'icon' => 'fas fa-envelope',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 6,
    'perm' => 'inquiries.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'telecalling_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'data_entry_operator' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'backoffice_staff' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecalling_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/mlm/clawbacks' => 
  array (
    'name' => 'Commission Clawbacks',
    'icon' => 'fas fa-undo',
    'section' => 'mlm',
    'parent_url' => NULL,
    'order' => 6,
    'perm' => 'commission.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/finance/tds' => 
  array (
    'name' => 'TDS Register',
    'icon' => 'fas fa-percent',
    'section' => 'finance',
    'parent_url' => NULL,
    'order' => 6,
    'perm' => 'financial.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/training/enrollments' => 
  array (
    'name' => 'Course Enrollments',
    'icon' => 'fas fa-user-graduate',
    'section' => 'hrm',
    'parent_url' => NULL,
    'order' => 6,
    'perm' => 'training.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/marketing-campaigns' => 
  array (
    'name' => 'Marketing Campaigns',
    'icon' => 'fas fa-mail-bulk',
    'section' => 'marketing',
    'parent_url' => NULL,
    'order' => 6,
    'perm' => 'marketing.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/legal-pages' => 
  array (
    'name' => 'Legal Pages Content',
    'icon' => 'fas fa-file-alt',
    'section' => 'cms',
    'parent_url' => NULL,
    'order' => 6,
    'perm' => 'pages.manage',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/settings/payment' => 
  array (
    'name' => 'Payment Gateway Settings',
    'icon' => 'fas fa-credit-card',
    'section' => 'settings',
    'parent_url' => NULL,
    'order' => 6,
    'perm' => 'system.settings',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/directory/materials' => 
  array (
    'name' => 'Material Prices',
    'icon' => 'fas fa-cubes',
    'section' => 'services',
    'parent_url' => NULL,
    'order' => 6,
    'perm' => 'properties.directory',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/careers/manage' => 
  array (
    'name' => 'Job Applications',
    'icon' => 'fas fa-file-alt',
    'section' => 'hrm',
    'parent_url' => NULL,
    'order' => 6,
    'perm' => 'careers',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'legal_advisor' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/legal/categories' => 
  array (
    'name' => 'Document Categories',
    'icon' => 'fas fa-tags',
    'section' => 'legal',
    'parent_url' => NULL,
    'order' => 6,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'legal_advisor' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/communication/logs' => 
  array (
    'name' => 'Automation Logs',
    'icon' => 'fas fa-history',
    'section' => 'communication',
    'parent_url' => NULL,
    'order' => 6,
    'perm' => 'communication.logs',
    'roles' => 
    array (
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'operations_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/legal-colony-pipeline/analytics-all' => 
  array (
    'name' => 'Colony Analytics',
    'icon' => 'fas fa-chart-line',
    'section' => 'colonies',
    'parent_url' => '/admin/legal-colony-pipeline',
    'order' => 6,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/listing-settings' => 
  array (
    'name' => 'Listing Settings',
    'icon' => 'fas fa-sliders-h',
    'section' => 'properties',
    'parent_url' => NULL,
    'order' => 6,
    'perm' => 'listing_settings',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/campaigns' => 
  array (
    'name' => 'Campaigns',
    'icon' => 'fas fa-bullhorn',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 7,
    'perm' => 'campaigns.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/mlm/associate-ranks' => 
  array (
    'name' => 'Rank Promotion',
    'icon' => 'fas fa-arrow-up',
    'section' => 'mlm',
    'parent_url' => NULL,
    'order' => 7,
    'perm' => 'mlm.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/gst' => 
  array (
    'name' => 'GST Invoices',
    'icon' => 'fas fa-file-invoice-dollar',
    'section' => 'finance',
    'parent_url' => NULL,
    'order' => 7,
    'perm' => 'financial.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/training/certificates' => 
  array (
    'name' => 'Certificates Issued',
    'icon' => 'fas fa-certificate',
    'section' => 'hrm',
    'parent_url' => NULL,
    'order' => 7,
    'perm' => 'training.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/property-comparison' => 
  array (
    'name' => 'Property Comparison',
    'icon' => 'fas fa-exchange-alt',
    'section' => 'marketing',
    'parent_url' => NULL,
    'order' => 7,
    'perm' => 'marketing.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/news' => 
  array (
    'name' => 'News Feed Manager',
    'icon' => 'fas fa-newspaper',
    'section' => 'cms',
    'parent_url' => NULL,
    'order' => 7,
    'perm' => 'news.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'content_writer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/bulk-operations' => 
  array (
    'name' => 'Bulk Import & Export',
    'icon' => 'fas fa-file-export',
    'section' => 'settings',
    'parent_url' => NULL,
    'order' => 7,
    'perm' => 'system.settings',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'data_entry_operator' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/legal-colony-pipeline/milestones/2' => 
  array (
    'name' => 'RERA Milestone Tracker',
    'icon' => 'fas fa-landmark',
    'section' => 'colonies',
    'parent_url' => '/admin/legal-colony-pipeline',
    'order' => 7,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/support-tickets' => 
  array (
    'name' => 'Support Tickets',
    'icon' => 'fas fa-ticket-alt',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 8,
    'perm' => 'tickets.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'support_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'support_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/mlm/rank-benefits' => 
  array (
    'name' => 'Rank Benefits',
    'icon' => 'fas fa-trophy',
    'section' => 'mlm',
    'parent_url' => NULL,
    'order' => 8,
    'perm' => 'mlm.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/finance/vendors' => 
  array (
    'name' => 'Vendor Payments',
    'icon' => 'fas fa-truck-loading',
    'section' => 'finance',
    'parent_url' => NULL,
    'order' => 8,
    'perm' => 'financial.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/training/modules' => 
  array (
    'name' => 'Training Modules',
    'icon' => 'fas fa-book-open',
    'section' => 'hrm',
    'parent_url' => NULL,
    'order' => 8,
    'perm' => 'training.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/drip-campaigns' => 
  array (
    'name' => 'Drip Campaigns',
    'icon' => 'fas fa-tint',
    'section' => 'marketing',
    'parent_url' => NULL,
    'order' => 8,
    'perm' => 'marketing.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/site-settings' => 
  array (
    'name' => 'Site Settings Manager',
    'icon' => 'fas fa-window-restore',
    'section' => 'cms',
    'parent_url' => NULL,
    'order' => 8,
    'perm' => 'system.settings',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'department_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'operations_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/webhooks' => 
  array (
    'name' => 'Webhooks Manager',
    'icon' => 'fas fa-link',
    'section' => 'settings',
    'parent_url' => NULL,
    'order' => 8,
    'perm' => 'system.settings',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/nps' => 
  array (
    'name' => 'NPS Surveys',
    'icon' => 'fas fa-poll',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 9,
    'perm' => 'nps.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'support_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'support_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/mlm/withdrawals' => 
  array (
    'name' => 'Withdrawals Request',
    'icon' => 'fas fa-wallet',
    'section' => 'mlm',
    'parent_url' => NULL,
    'order' => 9,
    'perm' => 'withdrawals.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/finance/penalties' => 
  array (
    'name' => 'EMI Penalties',
    'icon' => 'fas fa-exclamation-triangle',
    'section' => 'finance',
    'parent_url' => NULL,
    'order' => 9,
    'perm' => 'emi.manage',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/site-content' => 
  array (
    'name' => 'Site Content Editor',
    'icon' => 'fas fa-edit',
    'section' => 'cms',
    'parent_url' => NULL,
    'order' => 9,
    'perm' => 'system.settings',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'department_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'operations_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/company/settings' => 
  array (
    'name' => 'Company Profile Settings',
    'icon' => 'fas fa-building',
    'section' => 'settings',
    'parent_url' => NULL,
    'order' => 9,
    'perm' => 'system.settings',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'department_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'operations_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/ads' => 
  array (
    'name' => 'Ad Manager',
    'icon' => 'fas fa-ad',
    'section' => 'marketing',
    'parent_url' => NULL,
    'order' => 9,
    'perm' => 'marketing.ads',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/referrals' => 
  array (
    'name' => 'Customer Referrals',
    'icon' => 'fas fa-share-alt',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 10,
    'perm' => 'referrals.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/noc-registry' => 
  array (
    'name' => 'NOC & Registry',
    'icon' => 'fas fa-stamp',
    'section' => 'legal',
    'parent_url' => NULL,
    'order' => 10,
    'perm' => 'noc.registry.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/mlm/rewards' => 
  array (
    'name' => 'Reward History',
    'icon' => 'fas fa-gift',
    'section' => 'mlm',
    'parent_url' => NULL,
    'order' => 10,
    'perm' => 'rewards.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/finance/emi-auto-pay' => 
  array (
    'name' => 'EMI Auto-Pay',
    'icon' => 'fas fa-sync',
    'section' => 'finance',
    'parent_url' => NULL,
    'order' => 10,
    'perm' => 'emi.manage',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/api/integrations' => 
  array (
    'name' => 'API Integrations Engine',
    'icon' => 'fas fa-network-wired',
    'section' => 'settings',
    'parent_url' => NULL,
    'order' => 10,
    'perm' => 'system.settings',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/employee/dashboard' => 
  array (
    'name' => 'Dashboard',
    'icon' => 'fas fa-tachometer-alt',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 10,
    'perm' => 'employee',
    'roles' => 
    array (
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_finance_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_finance_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_sales_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_sales_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_hr_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_hr_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_it_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_it_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_legal_advisor' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_legal_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_land_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_land_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_project_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_site_engineer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_marketing_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_marketing_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_ops_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_ops_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_cs_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_cs_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_telecaller_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'department_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/ads/settings' => 
  array (
    'name' => 'AdSense Settings',
    'icon' => 'fab fa-google',
    'section' => 'marketing',
    'parent_url' => NULL,
    'order' => 10,
    'perm' => 'marketing.ads',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/iot' => 
  array (
    'name' => 'IoT Smart Property',
    'icon' => 'fas fa-microchip',
    'section' => 'ai_tech',
    'parent_url' => NULL,
    'order' => 10,
    'perm' => NULL,
    'roles' => 
    array (
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/agent-commission' => 
  array (
    'name' => 'Agent Commission',
    'icon' => 'fas fa-hand-holding-usd',
    'section' => 'commission',
    'parent_url' => NULL,
    'order' => 10,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/bookings' => 
  array (
    'name' => 'Bookings List',
    'icon' => 'fas fa-calendar-check',
    'section' => 'sales',
    'parent_url' => NULL,
    'order' => 11,
    'perm' => 'bookings.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/commission-plans' => 
  array (
    'name' => 'Commission Plans',
    'icon' => 'fas fa-sliders-h',
    'section' => 'mlm',
    'parent_url' => NULL,
    'order' => 11,
    'perm' => 'commission.plans.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/api/developers' => 
  array (
    'name' => 'API Developer Sandbox',
    'icon' => 'fas fa-code',
    'section' => 'settings',
    'parent_url' => NULL,
    'order' => 11,
    'perm' => 'system.settings',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/employee/finance-dashboard' => 
  array (
    'name' => 'Finance Dashboard',
    'icon' => 'fas fa-chart-pie',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 11,
    'perm' => NULL,
    'roles' => 
    array (
      'employee_finance_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/hrm/leave' => 
  array (
    'name' => 'Leave Management',
    'icon' => 'fas fa-calendar-times',
    'section' => 'hrm',
    'parent_url' => NULL,
    'order' => 11,
    'perm' => 'hrm.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/agent-agreements' => 
  array (
    'name' => 'Agent Agreements',
    'icon' => 'fas fa-file-signature',
    'section' => 'commission',
    'parent_url' => NULL,
    'order' => 11,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/agreements' => 
  array (
    'name' => 'Agreements',
    'icon' => 'fas fa-file-contract',
    'section' => 'sales',
    'parent_url' => NULL,
    'order' => 12,
    'perm' => 'agreements.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/mlm-settings/rules' => 
  array (
    'name' => 'Commission Rules',
    'icon' => 'fas fa-gavel',
    'section' => 'mlm',
    'parent_url' => NULL,
    'order' => 12,
    'perm' => 'commission.rules.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/efiling' => 
  array (
    'name' => 'Tax E-Filing',
    'icon' => 'fas fa-file-upload',
    'section' => 'finance',
    'parent_url' => NULL,
    'order' => 12,
    'perm' => 'efiling.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/api-docs' => 
  array (
    'name' => 'API Developer Docs',
    'icon' => 'fas fa-book',
    'section' => 'settings',
    'parent_url' => NULL,
    'order' => 12,
    'perm' => 'system.settings',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/employee/reports' => 
  array (
    'name' => 'Reports',
    'icon' => 'fas fa-file-alt',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 12,
    'perm' => NULL,
    'roles' => 
    array (
      'employee_finance_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_finance_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/backoffice/payslips' => 
  array (
    'name' => 'Payslips',
    'icon' => 'fas fa-file-invoice-dollar',
    'section' => 'hrm',
    'parent_url' => NULL,
    'order' => 12,
    'perm' => 'hrm.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/mlm-settings/levels' => 
  array (
    'name' => 'MLM Settings Levels',
    'icon' => 'fas fa-sliders-h',
    'section' => 'mlm',
    'parent_url' => NULL,
    'order' => 12,
    'perm' => 'mlm_settings_levels',
    'roles' => 
    array (
    ),
  ),
  '/admin/registry' => 
  array (
    'name' => 'Registry Management',
    'icon' => 'fas fa-signature',
    'section' => 'sales',
    'parent_url' => NULL,
    'order' => 13,
    'perm' => 'registry.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/associate-extensions' => 
  array (
    'name' => 'Associate Extensions',
    'icon' => 'fas fa-plus-circle',
    'section' => 'mlm',
    'parent_url' => NULL,
    'order' => 13,
    'perm' => 'mlm.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/efiling/tds' => 
  array (
    'name' => 'TDS Filing',
    'icon' => 'fas fa-percentage',
    'section' => 'finance',
    'parent_url' => NULL,
    'order' => 13,
    'perm' => 'efiling.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/ai_settings' => 
  array (
    'name' => 'AI Neural Configurations',
    'icon' => 'fas fa-robot',
    'section' => 'settings',
    'parent_url' => NULL,
    'order' => 13,
    'perm' => 'ai.settings',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/employee/tax' => 
  array (
    'name' => 'TDS & GST',
    'icon' => 'fas fa-percentage',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 13,
    'perm' => NULL,
    'roles' => 
    array (
      'employee_finance_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_finance_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/department-requests' => 
  array (
    'name' => 'Department Requests',
    'icon' => 'fas fa-project-diagram',
    'section' => 'hrm',
    'parent_url' => NULL,
    'order' => 13,
    'perm' => 'department_requests',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/possession' => 
  array (
    'name' => 'Possession Handover',
    'icon' => 'fas fa-key',
    'section' => 'sales',
    'parent_url' => NULL,
    'order' => 14,
    'perm' => 'possession.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/mlm-settings/evaluate' => 
  array (
    'name' => 'Rank Management',
    'icon' => 'fas fa-users-cog',
    'section' => 'mlm',
    'parent_url' => NULL,
    'order' => 14,
    'perm' => 'mlm.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/efiling/gst' => 
  array (
    'name' => 'GST Filing',
    'icon' => 'fas fa-receipt',
    'section' => 'finance',
    'parent_url' => NULL,
    'order' => 14,
    'perm' => 'efiling.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/localization' => 
  array (
    'name' => 'Localization & Language',
    'icon' => 'fas fa-globe-asia',
    'section' => 'settings',
    'parent_url' => NULL,
    'order' => 14,
    'perm' => 'system.settings',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/employee/sales-dashboard' => 
  array (
    'name' => 'Sales Dashboard',
    'icon' => 'fas fa-chart-line',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 14,
    'perm' => NULL,
    'roles' => 
    array (
      'employee_sales_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/department-requests/my-requests' => 
  array (
    'name' => 'My Requests',
    'icon' => 'fas fa-user-clock',
    'section' => 'hrm',
    'parent_url' => NULL,
    'order' => 14,
    'perm' => 'department_requests',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/efiling/calendar' => 
  array (
    'name' => 'Filing Calendar',
    'icon' => 'fas fa-calendar-alt',
    'section' => 'finance',
    'parent_url' => NULL,
    'order' => 15,
    'perm' => 'efiling.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/communication/queue' => 
  array (
    'name' => 'Communication Queue',
    'icon' => 'fas fa-paper-plane',
    'section' => 'settings',
    'parent_url' => NULL,
    'order' => 15,
    'perm' => 'system.settings',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/finance/cash-flow' => 
  array (
    'name' => 'Cash Flow Forecast',
    'icon' => 'fa-chart-line',
    'section' => 'finance',
    'parent_url' => NULL,
    'order' => 15,
    'perm' => 'admin',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/sales/approvals' => 
  array (
    'name' => 'Booking Approvals',
    'icon' => 'fas fa-clipboard-check',
    'section' => 'sales',
    'parent_url' => NULL,
    'order' => 15,
    'perm' => 'booking_approvals',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/employee/leads' => 
  array (
    'name' => 'My Leads',
    'icon' => 'fas fa-user-friends',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 15,
    'perm' => NULL,
    'roles' => 
    array (
      'employee_sales_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_sales_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_telecaller_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/push-notifications' => 
  array (
    'name' => 'Push Notifications',
    'icon' => 'fas fa-bell',
    'section' => 'ai_tech',
    'parent_url' => NULL,
    'order' => 15,
    'perm' => NULL,
    'roles' => 
    array (
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/business/associates' => 
  array (
    'name' => 'Business Associates',
    'icon' => 'fas fa-handshake',
    'section' => 'mlm',
    'parent_url' => NULL,
    'order' => 15,
    'perm' => 'mlm.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/mlm-rewards' => 
  array (
    'name' => 'MLM Rewards',
    'icon' => 'fas fa-gift',
    'section' => 'mlm',
    'parent_url' => NULL,
    'order' => 15,
    'perm' => 'mlm_rewards',
    'roles' => 
    array (
    ),
  ),
  '/admin/plot-costs' => 
  array (
    'name' => 'Plot Costs',
    'icon' => 'fas fa-calculator',
    'section' => 'finance',
    'parent_url' => NULL,
    'order' => 16,
    'perm' => 'plot.costs.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/whatsapp/settings' => 
  array (
    'name' => 'WhatsApp Config',
    'icon' => 'fab fa-whatsapp',
    'section' => 'communication',
    'parent_url' => NULL,
    'order' => 104,
    'perm' => NULL,
    'roles' => 
    array (
    ),
  ),
  '/employee/deals' => 
  array (
    'name' => 'Deals Pipeline',
    'icon' => 'fas fa-handshake',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 16,
    'perm' => NULL,
    'roles' => 
    array (
      'employee_sales_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_telecaller_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/mlm-rewards/rank-criteria' => 
  array (
    'name' => 'Rank Criteria',
    'icon' => 'fas fa-trophy',
    'section' => 'mlm',
    'parent_url' => NULL,
    'order' => 16,
    'perm' => 'mlm_rewards_rank_criteria',
    'roles' => 
    array (
    ),
  ),
  '/admin/banking' => 
  array (
    'name' => 'Banking Transactions',
    'icon' => 'fas fa-university',
    'section' => 'finance',
    'parent_url' => NULL,
    'order' => 17,
    'perm' => 'financial.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/gateways' => 
  array (
    'name' => 'Bank Gateway Manager',
    'icon' => 'fas fa-university',
    'section' => 'settings',
    'parent_url' => NULL,
    'order' => 17,
    'perm' => 'system.settings',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/employee/hr-dashboard' => 
  array (
    'name' => 'HR Dashboard',
    'icon' => 'fas fa-users-cog',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 17,
    'perm' => NULL,
    'roles' => 
    array (
      'employee_hr_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/mlm-rewards/rewards' => 
  array (
    'name' => 'Rewards',
    'icon' => 'fas fa-medal',
    'section' => 'mlm',
    'parent_url' => NULL,
    'order' => 17,
    'perm' => 'mlm_rewards_rewards',
    'roles' => 
    array (
    ),
  ),
  '/admin/bank-import' => 
  array (
    'name' => 'Bank Import',
    'icon' => 'fas fa-file-import',
    'section' => 'finance',
    'parent_url' => NULL,
    'order' => 18,
    'perm' => 'financial.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/company-credentials' => 
  array (
    'name' => 'Company Credentials',
    'icon' => 'fas fa-id-card',
    'section' => 'settings',
    'parent_url' => NULL,
    'order' => 18,
    'perm' => 'system.settings',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/employee/employees' => 
  array (
    'name' => 'Employees',
    'icon' => 'fas fa-user-tie',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 18,
    'perm' => NULL,
    'roles' => 
    array (
      'employee_hr_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_hr_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/mlm-rewards/upgrades' => 
  array (
    'name' => 'Upgrades',
    'icon' => 'fas fa-arrow-up',
    'section' => 'mlm',
    'parent_url' => NULL,
    'order' => 18,
    'perm' => 'mlm_rewards_upgrades',
    'roles' => 
    array (
    ),
  ),
  '/admin/finance/collections' => 
  array (
    'name' => 'Cash Collections',
    'icon' => 'fas fa-piggy-bank',
    'section' => 'finance',
    'parent_url' => NULL,
    'order' => 19,
    'perm' => 'financial.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/production-checklist' => 
  array (
    'name' => 'Production Checklist',
    'icon' => 'fas fa-clipboard-check',
    'section' => 'settings',
    'parent_url' => NULL,
    'order' => 19,
    'perm' => 'system.settings',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/employee/recruitment' => 
  array (
    'name' => 'Recruitment',
    'icon' => 'fas fa-user-plus',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 19,
    'perm' => NULL,
    'roles' => 
    array (
      'employee_hr_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/mlm-rewards/withdrawals' => 
  array (
    'name' => 'Withdrawals',
    'icon' => 'fas fa-money-bill-wave',
    'section' => 'mlm',
    'parent_url' => NULL,
    'order' => 19,
    'perm' => 'mlm_rewards_withdrawals',
    'roles' => 
    array (
    ),
  ),
  '/admin/menu-permissions' => 
  array (
    'name' => 'Menu Permissions RBAC',
    'icon' => 'fas fa-user-lock',
    'section' => 'settings',
    'parent_url' => NULL,
    'order' => 20,
    'perm' => 'system.settings',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/employee/tasks' => 
  array (
    'name' => 'My Tasks',
    'icon' => 'fas fa-tasks',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 20,
    'perm' => 'employee',
    'roles' => 
    array (
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_finance_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_finance_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_sales_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_sales_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_hr_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_hr_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_it_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_it_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_legal_advisor' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_legal_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_land_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_land_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_project_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_site_engineer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_marketing_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_marketing_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_ops_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_ops_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_cs_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_cs_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_telecaller_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'department_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'telecalling_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'support_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'legal_advisor' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'content_writer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'graphic_designer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'data_entry_operator' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'backoffice_staff' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecalling_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'support_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/kyc' => 
  array (
    'name' => 'KYC Verification',
    'icon' => 'fa-id-card',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 20,
    'perm' => 'admin',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/company-loans' => 
  array (
    'name' => 'Company Loans',
    'icon' => 'fas fa-hand-holding-usd',
    'section' => 'finance',
    'parent_url' => NULL,
    'order' => 20,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/employee/it-dashboard' => 
  array (
    'name' => 'IT Dashboard',
    'icon' => 'fas fa-server',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 20,
    'perm' => NULL,
    'roles' => 
    array (
      'employee_it_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/employee/infrastructure' => 
  array (
    'name' => 'Infrastructure',
    'icon' => 'fas fa-network-wired',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 21,
    'perm' => NULL,
    'roles' => 
    array (
      'employee_it_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_it_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/live-chat' => 
  array (
    'name' => 'Live Chat',
    'icon' => 'fas fa-comments',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 21,
    'perm' => 'crm.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/employee/legal-dashboard' => 
  array (
    'name' => 'Legal Dashboard',
    'icon' => 'fas fa-gavel',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 22,
    'perm' => NULL,
    'roles' => 
    array (
      'employee_legal_advisor' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/employee/compliance' => 
  array (
    'name' => 'Compliance',
    'icon' => 'fas fa-shield-alt',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 23,
    'perm' => NULL,
    'roles' => 
    array (
      'employee_legal_advisor' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_legal_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/employee/land-dashboard' => 
  array (
    'name' => 'Land Dashboard',
    'icon' => 'fas fa-map',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 24,
    'perm' => NULL,
    'roles' => 
    array (
      'employee_land_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/employee/surveys' => 
  array (
    'name' => 'Site Surveys',
    'icon' => 'fas fa-compass',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 25,
    'perm' => NULL,
    'roles' => 
    array (
      'employee_land_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_land_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/employee/construction-dashboard' => 
  array (
    'name' => 'Construction Dashboard',
    'icon' => 'fas fa-hard-hat',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 26,
    'perm' => NULL,
    'roles' => 
    array (
      'employee_project_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/employee/projects' => 
  array (
    'name' => 'Projects',
    'icon' => 'fas fa-project-diagram',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 27,
    'perm' => NULL,
    'roles' => 
    array (
      'employee_project_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/employee/quality' => 
  array (
    'name' => 'Quality Control',
    'icon' => 'fas fa-check-double',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 28,
    'perm' => NULL,
    'roles' => 
    array (
      'employee_project_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_site_engineer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/employee/marketing-dashboard' => 
  array (
    'name' => 'Marketing Dashboard',
    'icon' => 'fas fa-bullhorn',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 29,
    'perm' => NULL,
    'roles' => 
    array (
      'employee_marketing_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/employee/attendance' => 
  array (
    'name' => 'Attendance',
    'icon' => 'fas fa-calendar-check',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 30,
    'perm' => 'employee',
    'roles' => 
    array (
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_finance_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_finance_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_sales_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_sales_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_hr_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_hr_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_it_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_it_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_legal_advisor' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_legal_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_land_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_land_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_project_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_site_engineer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_marketing_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_marketing_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_ops_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_ops_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_cs_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_cs_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_telecaller_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'department_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'telecalling_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'support_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'legal_advisor' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'content_writer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'graphic_designer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'data_entry_operator' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'backoffice_staff' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecalling_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'support_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/employee/campaigns' => 
  array (
    'name' => 'Campaigns',
    'icon' => 'fas fa-mail-bulk',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 30,
    'perm' => NULL,
    'roles' => 
    array (
      'employee_marketing_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_marketing_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/employee/ops-dashboard' => 
  array (
    'name' => 'Operations Dashboard',
    'icon' => 'fas fa-cogs',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 31,
    'perm' => NULL,
    'roles' => 
    array (
      'employee_ops_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/employee/vendors' => 
  array (
    'name' => 'Vendors',
    'icon' => 'fas fa-truck',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 32,
    'perm' => NULL,
    'roles' => 
    array (
      'employee_ops_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_ops_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/employee/cs-dashboard' => 
  array (
    'name' => 'Customer Success',
    'icon' => 'fas fa-headset',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 33,
    'perm' => NULL,
    'roles' => 
    array (
      'employee_cs_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/employee/complaints' => 
  array (
    'name' => 'Complaints',
    'icon' => 'fas fa-exclamation-triangle',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 34,
    'perm' => NULL,
    'roles' => 
    array (
      'employee_cs_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_cs_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/employee/leaves' => 
  array (
    'name' => 'Leaves',
    'icon' => 'fas fa-umbrella-beach',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 40,
    'perm' => 'employee',
    'roles' => 
    array (
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_finance_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_finance_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_sales_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_sales_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_hr_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_hr_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_it_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_it_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_legal_advisor' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_legal_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_land_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_land_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_project_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_site_engineer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_marketing_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_marketing_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_ops_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_ops_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_cs_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_cs_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_telecaller_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'department_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'telecalling_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'support_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'legal_advisor' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'content_writer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'graphic_designer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'data_entry_operator' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'backoffice_staff' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecalling_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'support_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/employee/payroll' => 
  array (
    'name' => 'Payroll',
    'icon' => 'fas fa-money-check-alt',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 50,
    'perm' => 'employee',
    'roles' => 
    array (
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_finance_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_finance_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_sales_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_sales_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_hr_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_hr_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_it_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_it_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_legal_advisor' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_legal_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_land_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_land_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_project_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_site_engineer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_marketing_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_marketing_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_ops_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_ops_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_cs_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_cs_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_telecaller_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'department_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/messages' => 
  array (
    'name' => 'Messages',
    'icon' => 'fas fa-envelope',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 50,
    'perm' => 'crm.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/crm/templates' => 
  array (
    'name' => 'Email/SMS Templates',
    'icon' => 'fas fa-file-alt',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 55,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/ocr' => 
  array (
    'name' => 'Document OCR',
    'icon' => 'fas fa-file-pdf',
    'section' => 'ai_tech',
    'parent_url' => NULL,
    'order' => 103,
    'perm' => NULL,
    'roles' => 
    array (
    ),
  ),
  '/admin/crm/bulk-send' => 
  array (
    'name' => 'Bulk Outreach',
    'icon' => 'fas fa-paper-plane',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 56,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/ocr/templates' => 
  array (
    'name' => 'OCR Templates',
    'icon' => 'fas fa-cogs',
    'section' => 'operations',
    'parent_url' => NULL,
    'order' => 56,
    'perm' => NULL,
    'roles' => 
    array (
      'department_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'operations_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'backoffice_staff' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/crm/segments' => 
  array (
    'name' => 'Lead Segments',
    'icon' => 'fas fa-layer-group',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 57,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/crm/analytics' => 
  array (
    'name' => 'CRM Analytics',
    'icon' => 'fas fa-chart-line',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 58,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/crm/forms' => 
  array (
    'name' => 'Lead Forms',
    'icon' => 'fas fa-wpforms',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 59,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/employee/performance' => 
  array (
    'name' => 'Performance',
    'icon' => 'fas fa-chart-line',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 60,
    'perm' => 'employee',
    'roles' => 
    array (
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_finance_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_finance_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_sales_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_sales_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_hr_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_hr_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_it_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_it_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_legal_advisor' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_legal_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_land_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_land_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_project_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_site_engineer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_marketing_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_marketing_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_ops_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_ops_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_cs_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_cs_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_telecaller_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'department_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/employee/documents' => 
  array (
    'name' => 'Documents',
    'icon' => 'fas fa-folder-open',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 70,
    'perm' => 'employee',
    'roles' => 
    array (
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_finance_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_finance_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_sales_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_sales_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_hr_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_hr_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_it_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_it_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_legal_advisor' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_legal_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_land_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_land_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_project_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_site_engineer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_marketing_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_marketing_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_ops_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_ops_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_cs_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_cs_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_telecaller_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'department_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/employee/profile' => 
  array (
    'name' => 'My Profile',
    'icon' => 'fas fa-user',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 80,
    'perm' => 'employee',
    'roles' => 
    array (
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_finance_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_finance_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_sales_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_sales_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_hr_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_hr_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_it_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_it_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_legal_advisor' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_legal_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_land_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_land_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_project_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_site_engineer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_marketing_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_marketing_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_ops_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_ops_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_cs_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_cs_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_telecaller_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'department_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/employee/settings' => 
  array (
    'name' => 'Settings',
    'icon' => 'fas fa-cog',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 90,
    'perm' => 'employee',
    'roles' => 
    array (
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_finance_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_finance_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_sales_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_sales_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_hr_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_hr_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_it_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_it_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_legal_advisor' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_legal_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_land_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_land_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_project_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_site_engineer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_marketing_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_marketing_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_ops_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_ops_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_cs_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_cs_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_telecaller_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'team_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/tools/emi-calculator' => 
  array (
    'name' => 'EMI Calculator',
    'icon' => 'fas fa-calculator',
    'section' => 'finance',
    'parent_url' => NULL,
    'order' => 90,
    'perm' => 'emi_calculator',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/referrals/leaderboard' => 
  array (
    'name' => 'Referral Leaderboard',
    'icon' => 'fas fa-trophy',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 95,
    'perm' => 'manage_referrals',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/tools/document-extraction' => 
  array (
    'name' => 'Document AI Review',
    'icon' => 'fas fa-robot',
    'section' => 'ai_tech',
    'parent_url' => NULL,
    'order' => 95,
    'perm' => 'admin_tools',
    'roles' => 
    array (
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/referrals/share-analytics' => 
  array (
    'name' => 'Share Analytics',
    'icon' => 'fas fa-share-alt',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 96,
    'perm' => 'manage_referrals',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/custom-features' => 
  array (
    'name' => 'Custom Features',
    'icon' => 'fas fa-cubes',
    'section' => 'ai_tech',
    'parent_url' => NULL,
    'order' => 96,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/referrals/tiers' => 
  array (
    'name' => 'Referral Tiers',
    'icon' => 'fas fa-layer-group',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 97,
    'perm' => 'manage_referrals',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/custom-features/neighborhood' => 
  array (
    'name' => 'Neighborhood Analytics',
    'icon' => 'fas fa-map-marked-alt',
    'section' => 'ai_tech',
    'parent_url' => NULL,
    'order' => 97,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/crm/agentic' => 
  array (
    'name' => 'Agentic CRM AI',
    'icon' => 'fas fa-robot',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 98,
    'perm' => 'crm',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/custom-features/investment-calculator' => 
  array (
    'name' => 'Investment Calculator',
    'icon' => 'fas fa-calculator',
    'section' => 'ai_tech',
    'parent_url' => NULL,
    'order' => 98,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/crm/settings' => 
  array (
    'name' => 'CRM Settings',
    'icon' => 'fas fa-cogs',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 98,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/ai-system' => 
  array (
    'name' => 'AI System Dashboard',
    'icon' => 'fas fa-brain',
    'section' => 'ai_tech',
    'parent_url' => NULL,
    'order' => 99,
    'perm' => 'crm',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/crm/role-dashboard' => 
  array (
    'name' => 'CRM Role Dashboard',
    'icon' => 'fas fa-users-cog',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 99,
    'perm' => 'crm',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/compliance-scorecard' => 
  array (
    'name' => 'Compliance Scorecard',
    'icon' => 'fas fa-shield-alt',
    'section' => 'security',
    'parent_url' => NULL,
    'order' => 99,
    'perm' => 'dashboard.view',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/notification-dashboard' => 
  array (
    'name' => 'Notification Dashboard',
    'icon' => 'fas fa-bell',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 99,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/tools/whatsapp-templates' => 
  array (
    'name' => 'WhatsApp Templates',
    'icon' => 'fab fa-whatsapp',
    'section' => 'marketing',
    'parent_url' => NULL,
    'order' => 99,
    'perm' => 'admin_tools',
    'roles' => 
    array (
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/crm/routing' => 
  array (
    'name' => 'Lead Routing',
    'icon' => 'fas fa-route',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 99,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/leads/trash' => 
  array (
    'name' => 'Lead Trash',
    'icon' => 'fas fa-trash-alt',
    'section' => 'marketing',
    'parent_url' => NULL,
    'order' => 99,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/employee/logout' => 
  array (
    'name' => 'Logout',
    'icon' => 'fas fa-sign-out-alt',
    'section' => 'employee',
    'parent_url' => NULL,
    'order' => 100,
    'perm' => 'employee',
    'roles' => 
    array (
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_finance_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_finance_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_sales_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_sales_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_hr_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_hr_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_it_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_it_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_legal_advisor' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_legal_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_land_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_land_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_project_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_site_engineer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_marketing_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_marketing_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_ops_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_ops_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_cs_manager' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_cs_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'employee_telecaller_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/ai-system/qualifier' => 
  array (
    'name' => 'Lead Qualifier',
    'icon' => 'fas fa-magnet',
    'section' => 'ai_tech',
    'parent_url' => NULL,
    'order' => 100,
    'perm' => 'crm',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/crm/dedup' => 
  array (
    'name' => 'Lead Deduplication',
    'icon' => 'fas fa-copy',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 100,
    'perm' => 'crm',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/crm/assignments' => 
  array (
    'name' => 'Assignment Approvals',
    'icon' => 'fas fa-user-check',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 100,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/leads/export/csv' => 
  array (
    'name' => 'Export Leads',
    'icon' => 'fas fa-file-csv',
    'section' => 'sales',
    'parent_url' => NULL,
    'order' => 100,
    'perm' => 'leads_export',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/contracts-amc' => 
  array (
    'name' => 'Contracts & AMC',
    'icon' => 'fas fa-file-contract',
    'section' => 'finance',
    'parent_url' => NULL,
    'order' => 100,
    'perm' => NULL,
    'roles' => 
    array (
      'accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'associate_team_lead' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'backoffice_staff' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chartered_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'construction_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'content_writer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'data_entry_operator' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'department_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'employee_cs_executive' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'employee_cs_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'employee_finance_executive' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'employee_finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'employee_hr_executive' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'employee_hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'employee_it_executive' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'employee_it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'employee_land_executive' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'employee_land_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'employee_legal_advisor' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'employee_legal_executive' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'employee_marketing_executive' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'employee_marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'employee_ops_executive' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'employee_ops_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'employee_project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'employee_sales_executive' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'employee_sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'employee_site_engineer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'employee_telecaller' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'employee_telecaller_lead' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'graphic_designer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'hr_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'legal_advisor' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'operations_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'project_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'property_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_team_lead' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_accountant' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_agent' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_associate' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'support_executive' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'support_lead' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'team_lead' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'telecalling_executive' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'telecalling_lead' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/ai-system/market-report' => 
  array (
    'name' => 'Market Intelligence',
    'icon' => 'fas fa-chart-line',
    'section' => 'ai_tech',
    'parent_url' => NULL,
    'order' => 101,
    'perm' => 'crm',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/leads/import' => 
  array (
    'name' => 'Import Leads',
    'icon' => 'fas fa-file-import',
    'section' => 'sales',
    'parent_url' => NULL,
    'order' => 101,
    'perm' => 'leads_import',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/crm/custom-fields' => 
  array (
    'name' => 'Custom Fields',
    'icon' => 'fas fa-sliders-h',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 102,
    'perm' => '',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/security-test' => 
  array (
    'name' => 'Security Test Suite',
    'icon' => 'fas fa-shield-alt',
    'section' => 'ai_tech',
    'parent_url' => NULL,
    'order' => 102,
    'perm' => 'security_test',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 0,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/push-notifications/templates' => 
  array (
    'name' => 'Push Templates',
    'icon' => 'fas fa-file-alt',
    'section' => 'communication',
    'parent_url' => NULL,
    'order' => 102,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/leads/commission-heatmap' => 
  array (
    'name' => 'Commission Heatmap',
    'icon' => 'fas fa-chart-bar',
    'section' => 'marketing',
    'parent_url' => NULL,
    'order' => 102,
    'perm' => 'commission_heatmap',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/leads/property-comparison' => 
  array (
    'name' => 'Property Comparison',
    'icon' => 'fas fa-balance-scale',
    'section' => 'sales',
    'parent_url' => NULL,
    'order' => 102,
    'perm' => 'property_comparison',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/crm/drip' => 
  array (
    'name' => 'Drip Campaigns',
    'icon' => 'fas fa-robot',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 103,
    'perm' => '',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/push-notifications/campaigns' => 
  array (
    'name' => 'Push Campaigns',
    'icon' => 'fas fa-paper-plane',
    'section' => 'communication',
    'parent_url' => NULL,
    'order' => 103,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/leads/telecaller-performance' => 
  array (
    'name' => 'Telecoder Performance',
    'icon' => 'fas fa-phone-alt',
    'section' => 'sales',
    'parent_url' => NULL,
    'order' => 103,
    'perm' => 'telecaller_performance',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/crm/email-tracking/stats' => 
  array (
    'name' => 'Email Tracking',
    'icon' => 'fas fa-envelope-open',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 104,
    'perm' => '',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/knowledge-base-new' => 
  array (
    'name' => 'Knowledge Base',
    'icon' => 'fas fa-book',
    'section' => 'ai_tech',
    'parent_url' => NULL,
    'order' => 104,
    'perm' => NULL,
    'roles' => 
    array (
    ),
  ),
  '/admin/crm/sla' => 
  array (
    'name' => 'SLA Dashboard',
    'icon' => 'fas fa-clock',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 105,
    'perm' => '',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'support_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/meetings' => 
  array (
    'name' => 'Meetings',
    'icon' => 'fas fa-calendar-alt',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 106,
    'perm' => '',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/crm/voice' => 
  array (
    'name' => 'Voice CRM',
    'icon' => 'fas fa-microphone',
    'section' => 'crm',
    'parent_url' => NULL,
    'order' => 107,
    'perm' => '',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'sales_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'telecalling_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'telecalling_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/sustainable' => 
  array (
    'name' => 'Sustainable Tech',
    'icon' => 'fas fa-leaf',
    'section' => 'marketing',
    'parent_url' => NULL,
    'order' => 110,
    'perm' => NULL,
    'roles' => 
    array (
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/social-media' => 
  array (
    'name' => 'Social Media',
    'icon' => 'fab fa-facebook',
    'section' => 'marketing',
    'parent_url' => NULL,
    'order' => 115,
    'perm' => NULL,
    'roles' => 
    array (
      'marketing_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'marketing_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'franchise_owner' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/ai/executive-assistant' => 
  array (
    'name' => 'AI Executive Assistant',
    'icon' => 'fas fa-brain',
    'section' => 'ai_tech',
    'parent_url' => NULL,
    'order' => 168,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/smart-registration' => 
  array (
    'name' => 'Smart Registration',
    'icon' => 'fas fa-user-plus',
    'section' => 'ai_tech',
    'parent_url' => NULL,
    'order' => 180,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/payout-batches' => 
  array (
    'name' => 'Payout Batches',
    'icon' => 'fas fa-money-check-alt',
    'section' => 'commission',
    'parent_url' => NULL,
    'order' => 181,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'finance_director' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/commission/telecaller/commissions' => 
  array (
    'name' => 'Telecaller Commissions',
    'icon' => 'fas fa-phone-volume',
    'section' => 'commission',
    'parent_url' => NULL,
    'order' => 182,
    'perm' => 'commission_telecaller',
    'roles' => 
    array (
    ),
  ),
  '/admin/commission/calculations' => 
  array (
    'name' => 'Commission Calculations',
    'icon' => 'fas fa-calculator',
    'section' => 'commission',
    'parent_url' => NULL,
    'order' => 183,
    'perm' => 'commission_calculations',
    'roles' => 
    array (
    ),
  ),
  '/admin/ai-calling' => 
  array (
    'name' => 'AI Calling System',
    'icon' => 'fas fa-phone-alt',
    'section' => 'ai_tech',
    'parent_url' => NULL,
    'order' => 189,
    'perm' => 'ai_calling',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/ai-calling/dashboard' => 
  array (
    'name' => 'AI Calling Dashboard',
    'icon' => 'fas fa-phone-alt',
    'section' => 'ai_tech',
    'parent_url' => NULL,
    'order' => 190,
    'perm' => 'ai_calling',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'telecalling_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'telecalling_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/sim-calling' => 
  array (
    'name' => 'SIM Calling',
    'icon' => 'fas fa-sim-card',
    'section' => 'ai_tech',
    'parent_url' => NULL,
    'order' => 191,
    'perm' => 'sim_calling',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'telecalling_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'telecalling_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/voice-agents' => 
  array (
    'name' => 'Voice Agents',
    'icon' => 'fas fa-robot',
    'section' => 'ai_tech',
    'parent_url' => NULL,
    'order' => 192,
    'perm' => 'voice_agents',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'telecalling_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'telecalling_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/ai-calling/schedule' => 
  array (
    'name' => 'Auto Dialer Cron',
    'icon' => 'fas fa-clock',
    'section' => 'ai_tech',
    'parent_url' => NULL,
    'order' => 193,
    'perm' => 'ai_calling',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'telecalling_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/ai-calling/sessions' => 
  array (
    'name' => 'Call Sessions',
    'icon' => 'fas fa-list-alt',
    'section' => 'ai_tech',
    'parent_url' => NULL,
    'order' => 194,
    'perm' => 'ai_calling',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'telecalling_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/ai-calling/extracted-leads' => 
  array (
    'name' => 'Extracted Leads',
    'icon' => 'fas fa-headset',
    'section' => 'ai_tech',
    'parent_url' => NULL,
    'order' => 195,
    'perm' => 'ai_calling',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'telecalling_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'telecalling_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/ai-calling/call-logs' => 
  array (
    'name' => 'Voice Call Logs',
    'icon' => 'fas fa-headset',
    'section' => 'ai_tech',
    'parent_url' => NULL,
    'order' => 195,
    'perm' => 'ai_calling',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'telecalling_lead' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'telecalling_executive' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 1,
        'delete' => 0,
      ),
    ),
  ),
  '/admin/tools/esign' => 
  array (
    'name' => 'eSign Management',
    'icon' => 'fas fa-file-signature',
    'section' => 'legal',
    'parent_url' => NULL,
    'order' => 195,
    'perm' => 'admin_tools',
    'roles' => 
    array (
      'legal_advisor' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/ai-calling/health' => 
  array (
    'name' => 'Telephony Health',
    'icon' => 'fas fa-heartbeat',
    'section' => 'ai_tech',
    'parent_url' => NULL,
    'order' => 196,
    'perm' => 'ai_calling',
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/tools/stamp-duty' => 
  array (
    'name' => 'Stamp Duty Config',
    'icon' => 'fas fa-rupee-sign',
    'section' => 'legal',
    'parent_url' => NULL,
    'order' => 196,
    'perm' => 'admin_tools',
    'roles' => 
    array (
      'legal_advisor' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/ai-calling/auto-dialer' => 
  array (
    'name' => 'Auto Dialer',
    'icon' => 'fas fa-phone-volume',
    'section' => 'ai_tech',
    'parent_url' => NULL,
    'order' => 197,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
  '/admin/ai-calling/call-analytics' => 
  array (
    'name' => 'Call Analytics',
    'icon' => 'fas fa-chart-pie',
    'section' => 'ai_tech',
    'parent_url' => NULL,
    'order' => 198,
    'perm' => NULL,
    'roles' => 
    array (
      'admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'employee' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'associate' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'agent' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'super_admin' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'customer' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'telecaller' => 
      array (
        'view' => 1,
        'create' => 0,
        'edit' => 0,
        'delete' => 0,
      ),
      'ceo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cfo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'coo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cto' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'cmo' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'chro' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 0,
      ),
      'it_manager' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
      'senior_developer' => 
      array (
        'view' => 1,
        'create' => 1,
        'edit' => 1,
        'delete' => 1,
      ),
    ),
  ),
);

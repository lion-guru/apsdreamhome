import 'package:flutter/material.dart';

import 'api_service.dart';

/// One admin sidebar entry straight from the web database
/// (admin_menu_items), already permission-resolved (can_view = 1)
/// for the signed-in role by the server.
class AdminMenuEntry {
  final String id;
  final String name;
  final String url;
  final String section;
  final int orderIndex;

  const AdminMenuEntry({
    required this.id,
    required this.name,
    required this.url,
    required this.section,
    required this.orderIndex,
  });

  factory AdminMenuEntry.fromJson(Map<String, dynamic> json) => AdminMenuEntry(
        id: '${json['id']}',
        name: (json['name'] ?? '').toString(),
        url: (json['url'] ?? '').toString(),
        section: (json['section'] ?? 'main').toString(),
        orderIndex: (json['order_index'] as num?)?.toInt() ?? 0,
      );
}

/// A screen the mobile app implements, keyed by the web DB menu URL.
/// Items absent from this map are never rendered - the app never shows
/// a dead link even if the server grants can_view on it.
class AdminScreenAlias {
  final String route;
  final IconData icon;

  const AdminScreenAlias(this.route, this.icon);
}

/// Web admin URL -> app-native screen. Everything the mobile app really
/// has a page for, keyed the same way as the web admin sidebar.
const Map<String, AdminScreenAlias> adminScreenAliases = {
  '/admin': AdminScreenAlias('/admin', Icons.dashboard_outlined),
  '/admin/crm': AdminScreenAlias('/admin/crm', Icons.campaign_outlined),
  '/admin/leads': AdminScreenAlias('/admin/crm', Icons.person_search_outlined),
  '/admin/bookings': AdminScreenAlias('/admin/bookings', Icons.book_online_outlined),
  '/admin/customers': AdminScreenAlias('/admin/customers', Icons.people_outline),
  '/admin/reports': AdminScreenAlias('/admin/reports', Icons.assessment_outlined),
  '/admin/colonies': AdminScreenAlias('/admin/colonies', Icons.location_city_outlined),
  '/admin/plots': AdminScreenAlias('/admin/plots', Icons.map_outlined),
  '/admin/employees': AdminScreenAlias('/admin/employees', Icons.group_outlined),
  '/admin/commissions': AdminScreenAlias('/admin/commissions', Icons.account_balance_wallet_outlined),
  '/admin/payouts': AdminScreenAlias('/admin/payouts', Icons.payments_outlined),
  '/admin/accounts': AdminScreenAlias('/admin/accounts', Icons.account_balance_outlined),
  '/admin/settings': AdminScreenAlias('/admin/settings', Icons.settings_outlined),
  '/admin/analytics': AdminScreenAlias('/admin/analytics', Icons.analytics_outlined),
  '/admin/marketing': AdminScreenAlias('/admin/marketing', Icons.campaign_rounded),
  '/admin/bulk-marketing': AdminScreenAlias('/admin/bulk-marketing', Icons.mail_outline),
  '/admin/invoices': AdminScreenAlias('/admin/invoices', Icons.receipt_long_outlined),
  '/admin/ledger': AdminScreenAlias('/admin/ledger', Icons.menu_book_outlined),
  '/admin/emi': AdminScreenAlias('/admin/emi', Icons.request_page_outlined),
  '/admin/tools': AdminScreenAlias('/admin/tools', Icons.build_outlined),
  '/admin/payout-batches': AdminScreenAlias('/admin/payout-batches', Icons.outbox_outlined),
  '/admin/colony-progress': AdminScreenAlias('/admin/colony-progress', Icons.timeline_outlined),
  '/admin/material-inventory': AdminScreenAlias('/admin/material-inventory', Icons.inventory_2_outlined),
  '/admin/auto-payout': AdminScreenAlias('/admin/auto-payout', Icons.autorenew_outlined),
};

/// Icon used for DB entries that match an alias; kept for readability.
IconData iconForSection(String section) => switch (section) {
      'finance' => Icons.account_balance_wallet_outlined,
      'hrm' => Icons.badge_outlined,
      'crm' => Icons.campaign_outlined,
      'sales' => Icons.sell_outlined,
      'colonies' => Icons.location_city_outlined,
      'properties' => Icons.villa_outlined,
      'projects' => Icons.view_kanban_outlined,
      'township_ops' => Icons.engineering_outlined,
      'mlm' => Icons.hub_outlined,
      'marketing' => Icons.campaign_rounded,
      'ai' => Icons.psychology_alt_outlined,
      'ai_tech' => Icons.smart_toy_outlined,
      'legal' => Icons.gavel_outlined,
      'security' => Icons.verified_user_outlined,
      'reports' => Icons.assessment_outlined,
      'settings' => Icons.settings_outlined,
      'dashboards' => Icons.dashboard_outlined,
      'communication' => Icons.forum_outlined,
      'operations' => Icons.work_outline,
      'system' => Icons.storage_outlined,
      'cms' => Icons.article_outlined,
      'saas' => Icons.business_outlined,
      'users' => Icons.manage_accounts_outlined,
      'services' => Icons.support_agent_outlined,
      'valuations' => Icons.price_change_outlined,
      _ => Icons.circle_outlined,
    };

/// Human-readable section label, keyed off the DB token.
const Map<String, String> _sectionLabels = {
  'main': 'MAIN',
  'dashboards': 'DASHBOARDS',
  'crm': 'CRM',
  'sales': 'SALES',
  'finance': 'FINANCE',
  'hrm': 'HR',
  'mlm': 'MLM NETWORK',
  'colonies': 'COLONIES',
  'properties': 'PROPERTIES',
  'projects': 'PROJECTS',
  'township_ops': 'TOWNSHIP OPS',
  'marketing': 'MARKETING',
  'ai': 'AI',
  'ai_tech': 'AI & TECH',
  'legal': 'LEGAL',
  'security': 'SECURITY',
  'reports': 'REPORTS',
  'settings': 'SETTINGS',
  'communication': 'COMMUNICATION',
  'operations': 'OPERATIONS',
  'system': 'SYSTEM',
  'cms': 'CONTENT',
  'saas': 'TENANTS',
  'users': 'USERS',
  'services': 'SERVICES',
  'valuations': 'VALUATIONS',
};

String adminSectionLabel(String section) =>
    _sectionLabels[section] ?? section.replaceAll('_', ' ').toUpperCase();

/// Only entries whose screens the mobile app implements, DB order preserved.
List<AdminMenuEntry> filterAppMenuEntries(List<AdminMenuEntry> entries) =>
    entries.where((e) => adminScreenAliases.containsKey(e.url)).toList();

/// Concrete icon for an entry.
IconData iconForEntry(AdminMenuEntry entry) =>
    adminScreenAliases[entry.url]?.icon ?? iconForSection(entry.section);

/// Native route the entry opens.
String routeForEntry(AdminMenuEntry entry) =>
    adminScreenAliases[entry.url]?.route ?? '/admin';

class AdminMenuService {
  AdminMenuService._();
  static final AdminMenuService instance = AdminMenuService._();

  List<AdminMenuEntry>? _cache;

  /// Returns the permission-resolved DB menu for this role, filtered to
  /// screens the app implements. Null on ANY failure (offline, 401, 403,
  /// timeout) - the shell falls back to its built-in role baseline.
  Future<List<AdminMenuEntry>?> fetchMenu({bool forceRefresh = false}) async {
    if (!forceRefresh && _cache != null) {
      return _cache;
    }
    try {
      final response = await ApiService().get('/admin/menu');
      if (response['success'] != true) {
        return null;
      }
      final raw = (response['menu'] as List?) ?? const [];
      final entries = raw
          .whereType<Map<String, dynamic>>()
          .map(AdminMenuEntry.fromJson)
          .toList();
      final filtered = filterAppMenuEntries(entries);
      _cache = filtered;
      return filtered;
    } catch (_) {
      return null;
    }
  }

  /// Test hook: inject a canned menu without hitting the network.
  @visibleForTesting
  void setCacheForTest(List<AdminMenuEntry>? entries) => _cache = entries;
}

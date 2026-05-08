import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../data/services/auth_service.dart';

/// Admin Shell - Main Layout for Admin Panel (Web/Desktop optimized)
class AdminShell extends ConsumerStatefulWidget {
  final Widget child;

  const AdminShell({
    super.key,
    required this.child,
  });

  @override
  ConsumerState<AdminShell> createState() => _AdminShellState();
}

class _AdminShellState extends ConsumerState<AdminShell> {
  bool _isSidebarExpanded = true;

  @override
  Widget build(BuildContext context) {
    final userAsync = ref.watch(currentUserDataProvider);
    final screenWidth = MediaQuery.of(context).size.width;
    final isMobile = screenWidth < 768;

    return userAsync.when(
      data: (user) {
        if (user == null || user.role != 'admin') {
          return const Scaffold(
            body: Center(
              child: Text('Access Denied - Admin Only'),
            ),
          );
        }

        return Scaffold(
          body: Row(
            children: [
              // Sidebar
              if (!isMobile || _isSidebarExpanded)
                AnimatedContainer(
                  duration: const Duration(milliseconds: 300),
                  width: _isSidebarExpanded ? 260 : 70,
                  child: _buildSidebar(context, user),
                ),

              // Main Content
              Expanded(
                child: Column(
                  children: [
                    // Top App Bar
                    _buildTopBar(context, user, isMobile),

                    // Content
                    Expanded(
                      child: widget.child,
                    ),
                  ],
                ),
              ),
            ],
          ),
        );
      },
      loading: () => const Scaffold(
        body: Center(child: CircularProgressIndicator()),
      ),
      error: (error, stack) => Scaffold(
        body: Center(child: Text('Error: $error')),
      ),
    );
  }

  Widget _buildSidebar(BuildContext context, dynamic user) {
    final menuItems = _getMenuItems((user.subRole as String?) ?? 'director');

    return Container(
      color: const Color(0xFF1E293B), // Dark blue-gray
      child: Column(
        children: [
          // Logo Section
          Container(
            padding: const EdgeInsets.all(20),
            decoration: const BoxDecoration(
              border: Border(
                bottom: BorderSide(
                  color: Color(0xFF334155),
                  width: 1,
                ),
              ),
            ),
            child: Row(
              children: [
                Container(
                  width: 40,
                  height: 40,
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
                    ),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Icon(
                    Icons.home_work,
                    color: Colors.white,
                  ),
                ),
                if (_isSidebarExpanded) ...[
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'APS Dream Home',
                          style: TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.bold,
                            fontSize: 16,
                          ),
                          overflow: TextOverflow.ellipsis,
                        ),
                        Text(
                          'Admin Panel',
                          style: TextStyle(
                            color: Colors.white.withValues(alpha: 0.6),
                            fontSize: 12,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ],
            ),
          ),

          // Menu Items
          Expanded(
            child: ListView.builder(
              padding: const EdgeInsets.symmetric(vertical: 12),
              itemCount: menuItems.length,
              itemBuilder: (context, index) {
                final item = menuItems[index];
                final isSelected = _isCurrentRoute(item['route'] as String);

                return _buildMenuItem(
                  icon: item['icon'] as IconData,
                  label: item['label'] as String,
                  route: item['route'] as String,
                  isSelected: isSelected,
                  badge: item['badge'] as int?,
                );
              },
            ),
          ),

          // User Profile at Bottom
          Container(
            padding: const EdgeInsets.all(16),
            decoration: const BoxDecoration(
              border: Border(
                top: BorderSide(
                  color: Color(0xFF334155),
                  width: 1,
                ),
              ),
            ),
            child: Row(
              children: [
                CircleAvatar(
                  backgroundColor: AppTheme.primaryColor,
                  child: Text(
                    (user.name as String).substring(0, 1).toUpperCase(),
                    style: const TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
                if (_isSidebarExpanded) ...[
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          user.name as String,
                          style: const TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w500,
                          ),
                          overflow: TextOverflow.ellipsis,
                        ),
                        Text(
                          (user.subRole as String?)?.toUpperCase() ?? 'ADMIN',
                          style: TextStyle(
                            color: Colors.white.withValues(alpha: 0.6),
                            fontSize: 11,
                          ),
                        ),
                      ],
                    ),
                  ),
                  IconButton(
                    onPressed: () => _logout(),
                    icon: const Icon(
                      Icons.logout,
                      color: Colors.white70,
                      size: 20,
                    ),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMenuItem({
    required IconData icon,
    required String label,
    required String route,
    required bool isSelected,
    int? badge,
  }) {
    return InkWell(
      onTap: () => context.go(route),
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        decoration: BoxDecoration(
          color: isSelected
              ? AppTheme.primaryColor.withValues(alpha: 0.2)
              : Colors.transparent,
          borderRadius: BorderRadius.circular(8),
          border: isSelected
              ? Border.all(
                  color: AppTheme.primaryColor.withValues(alpha: 0.5),
                  width: 1,
                )
              : null,
        ),
        child: Row(
          children: [
            Icon(
              icon,
              color: isSelected
                  ? AppTheme.primaryColor
                  : Colors.white.withValues(alpha: 0.7),
              size: 22,
            ),
            if (_isSidebarExpanded) ...[
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  label,
                  style: TextStyle(
                    color: isSelected
                        ? Colors.white
                        : Colors.white.withValues(alpha: 0.7),
                    fontWeight:
                        isSelected ? FontWeight.bold : FontWeight.normal,
                    fontSize: 14,
                  ),
                ),
              ),
              if (badge != null && badge > 0)
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.red,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    badge.toString(),
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildTopBar(BuildContext context, dynamic user, bool isMobile) {
    return Container(
      height: 70,
      padding: const EdgeInsets.symmetric(horizontal: 24),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
          ),
        ],
      ),
      child: Row(
        children: [
          if (isMobile)
            IconButton(
              onPressed: () {
                setState(() {
                  _isSidebarExpanded = !_isSidebarExpanded;
                });
              },
              icon: const Icon(Icons.menu),
            ),
          if (!isMobile)
            IconButton(
              onPressed: () {
                setState(() {
                  _isSidebarExpanded = !_isSidebarExpanded;
                });
              },
              icon: Icon(
                _isSidebarExpanded ? Icons.chevron_left : Icons.chevron_right,
              ),
            ),
          const SizedBox(width: 16),

          // Search Bar
          Expanded(
            child: Container(
              height: 44,
              constraints: const BoxConstraints(maxWidth: 400),
              child: TextField(
                decoration: InputDecoration(
                  hintText: 'Search...',
                  prefixIcon: const Icon(Icons.search),
                  filled: true,
                  fillColor: Colors.grey.shade100,
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(8),
                    borderSide: BorderSide.none,
                  ),
                ),
              ),
            ),
          ),

          const Spacer(),

          // Notifications
          Stack(
            children: [
              IconButton(
                onPressed: () {},
                icon: const Icon(Icons.notifications_outlined),
              ),
              Positioned(
                right: 8,
                top: 8,
                child: Container(
                  width: 8,
                  height: 8,
                  decoration: const BoxDecoration(
                    color: Colors.red,
                    shape: BoxShape.circle,
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(width: 8),

          // Quick Actions
          PopupMenuButton<String>(
            onSelected: (value) {
              if (value == 'profile') context.push('/admin/profile');
              if (value == 'settings') context.push('/admin/settings');
            },
            itemBuilder: (context) => [
              const PopupMenuItem(
                value: 'profile',
                child: Row(
                  children: [
                    Icon(Icons.person_outline),
                    SizedBox(width: 8),
                    Text('Profile'),
                  ],
                ),
              ),
              const PopupMenuItem(
                value: 'settings',
                child: Row(
                  children: [
                    Icon(Icons.settings_outlined),
                    SizedBox(width: 8),
                    Text('Settings'),
                  ],
                ),
              ),
            ],
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              decoration: BoxDecoration(
                border: Border.all(color: Colors.grey.shade300),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 16,
                    backgroundColor: AppTheme.primaryColor,
                    child: Text(
                      ((user['name'] as String?) ?? 'U')
                          .substring(0, 1)
                          .toUpperCase(),
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Text(
                    ((user['name'] as String?) ?? 'User').split(' ')[0],
                    style: const TextStyle(fontWeight: FontWeight.w500),
                  ),
                  const Icon(Icons.arrow_drop_down),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  List<Map<String, dynamic>> _getMenuItems(String subRole) {
    final commonItems = [
      {
        'icon': Icons.dashboard_outlined,
        'label': 'Dashboard',
        'route': '/admin',
        'badge': 0
      },
      {
        'icon': Icons.campaign_outlined,
        'label': 'CRM',
        'route': '/admin/crm',
        'badge': 28
      },
      {
        'icon': Icons.book_online_outlined,
        'label': 'Bookings',
        'route': '/admin/bookings',
        'badge': 5
      },
      {
        'icon': Icons.people_outline,
        'label': 'Customers',
        'route': '/admin/customers',
        'badge': 0
      },
      {
        'icon': Icons.assessment_outlined,
        'label': 'Reports',
        'route': '/admin/reports',
        'badge': 0
      },
    ];

    final directorItems = [
      {
        'icon': Icons.location_city_outlined,
        'label': 'Colonies',
        'route': '/admin/colonies',
        'badge': 0
      },
      {
        'icon': Icons.map_outlined,
        'label': 'Plots',
        'route': '/admin/plots',
        'badge': 12
      },
      {
        'icon': Icons.group_outlined,
        'label': 'Employees',
        'route': '/admin/employees',
        'badge': 0
      },
      {
        'icon': Icons.account_balance_wallet_outlined,
        'label': 'Commissions',
        'route': '/admin/commissions',
        'badge': 8
      },
      {
        'icon': Icons.payments_outlined,
        'label': 'Payouts',
        'route': '/admin/payouts',
        'badge': 3
      },
      {
        'icon': Icons.account_balance_outlined,
        'label': 'Accounts',
        'route': '/admin/accounts',
        'badge': 0
      },
      {
        'icon': Icons.settings_outlined,
        'label': 'Settings',
        'route': '/admin/settings',
        'badge': 0
      },
    ];

    final accountantItems = [
      {
        'icon': Icons.receipt_outlined,
        'label': 'Invoices',
        'route': '/admin/invoices',
        'badge': 0
      },
      {
        'icon': Icons.account_balance_outlined,
        'label': 'Ledger',
        'route': '/admin/ledger',
        'badge': 0
      },
      {
        'icon': Icons.trending_up_outlined,
        'label': 'EMI Collections',
        'route': '/admin/emi',
        'badge': 0
      },
    ];

    final salesItems = [
      {
        'icon': Icons.person_add_outlined,
        'label': 'Leads',
        'route': '/admin/leads',
        'badge': 15
      },
      {
        'icon': Icons.campaign_outlined,
        'label': 'Marketing',
        'route': '/admin/marketing',
        'badge': 0
      },
    ];

    switch (subRole.toLowerCase()) {
      case 'director':
      case 'md':
        return [...commonItems, ...directorItems];
      case 'accountant':
      case 'cmd':
        return [...commonItems, ...accountantItems];
      case 'sales':
      case 'sales_manager':
        return [...commonItems, ...salesItems];
      default:
        return commonItems;
    }
  }

  bool _isCurrentRoute(String route) {
    final currentRoute = GoRouterState.of(context).uri.path;
    return currentRoute == route || currentRoute.startsWith('$route/');
  }

  void _logout() {
    ref.read(authServiceProvider).logout();
    context.go('/login');
  }
}

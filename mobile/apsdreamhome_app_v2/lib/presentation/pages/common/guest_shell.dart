import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';

/// Guest Shell — bottom navigation for unauthenticated users.
/// Wraps public pages so guests can browse Colonies, Plots, Properties, Tools.
class GuestShell extends StatelessWidget {
  final Widget child;
  const GuestShell({super.key, required this.child});

  static int _calculateSelectedIndex(BuildContext context) {
    final String location = GoRouterState.of(context).uri.toString();
    if (location.startsWith('/colonies') || location.startsWith('/colony')) return 0;
    if (location.startsWith('/plots') || location.startsWith('/plot')) return 1;
    if (location.startsWith('/properties') || location.startsWith('/property')) return 2;
    if (location.startsWith('/emi-calculator') || location.startsWith('/tools')) return 3;
    return 0; // Default to Colonies
  }

  @override
  Widget build(BuildContext context) {
    final selectedIndex = _calculateSelectedIndex(context);

    return Scaffold(
      body: child,
      bottomNavigationBar: NavigationBar(
        selectedIndex: selectedIndex,
        onDestinationSelected: (index) {
          switch (index) {
            case 0:
              context.go('/colonies');
            case 1:
              context.go('/plots');
            case 2:
              context.go('/properties');
            case 3:
              context.go('/emi-calculator');
          }
        },
        backgroundColor: Colors.white,
        indicatorColor: AppTheme.primaryColor.withValues(alpha: 0.1),
        labelBehavior: NavigationDestinationLabelBehavior.alwaysShow,
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.location_city_outlined),
            selectedIcon: Icon(Icons.location_city, color: AppTheme.primaryColor),
            label: 'Colonies',
          ),
          NavigationDestination(
            icon: Icon(Icons.grid_view_outlined),
            selectedIcon: Icon(Icons.grid_view, color: AppTheme.primaryColor),
            label: 'Plots',
          ),
          NavigationDestination(
            icon: Icon(Icons.home_outlined),
            selectedIcon: Icon(Icons.home, color: AppTheme.primaryColor),
            label: 'Properties',
          ),
          NavigationDestination(
            icon: Icon(Icons.calculate_outlined),
            selectedIcon: Icon(Icons.calculate, color: AppTheme.primaryColor),
            label: 'Tools',
          ),
        ],
      ),
    );
  }
}

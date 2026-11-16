import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';

/// Associate Shell - Bottom Navigation for the Associate / MLM Portal.
/// Wraps the associate role routes so partners always see the same five
/// destinations: Dashboard, Leads, Team, Bookings, Profile.
class AssociateShell extends StatefulWidget {
  final Widget child;

  const AssociateShell({super.key, required this.child});

  @override
  State<AssociateShell> createState() => _AssociateShellState();
}

class _AssociateShellState extends State<AssociateShell> {
  final _tabs = const [
    '/associate/dashboard',
    '/associate/leads',
    '/associate/team',
    '/associate/offline-booking',
    '/associate/profile',
  ];

  int _indexForLocation(String location) {
    if (location.startsWith('/associate/leads')) return 1;
    if (location.startsWith('/associate/team') ||
        location.startsWith('/associate/my-team') ||
        location.startsWith('/associate/genealogy')) {
      return 2;
    }
    if (location.startsWith('/associate/offline-booking')) return 3;
    if (location.startsWith('/associate/profile')) return 4;
    return 0;
  }

  @override
  Widget build(BuildContext context) {
    final location = GoRouterState.of(context).uri.path;
    final currentIndex = _indexForLocation(location);

    return Scaffold(
      body: widget.child,
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: currentIndex,
        onTap: (index) => context.go(_tabs[index]),
        type: BottomNavigationBarType.fixed,
        selectedItemColor: AppTheme.primaryColor,
        unselectedItemColor: Colors.grey.shade500,
        selectedFontSize: 11,
        unselectedFontSize: 11,
        items: const [
          BottomNavigationBarItem(
            icon: Icon(Icons.dashboard_outlined),
            activeIcon: Icon(Icons.dashboard),
            label: 'Home',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.person_search_outlined),
            activeIcon: Icon(Icons.person_search),
            label: 'Leads',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.groups_outlined),
            activeIcon: Icon(Icons.groups),
            label: 'Team',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.event_available_outlined),
            activeIcon: Icon(Icons.event_available),
            label: 'Bookings',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.person_outline),
            activeIcon: Icon(Icons.person),
            label: 'Profile',
          ),
        ],
      ),
    );
  }
}

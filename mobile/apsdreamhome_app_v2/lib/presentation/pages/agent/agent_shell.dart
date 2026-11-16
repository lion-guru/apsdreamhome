import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';

/// Agent Shell - Bottom Navigation for the Agent Partner Portal.
/// Wraps the agent role routes so agents always see the same five
/// destinations: Dashboard, Leads, Deals, Bookings, Profile.
class AgentShell extends StatefulWidget {
  final Widget child;

  const AgentShell({super.key, required this.child});

  @override
  State<AgentShell> createState() => _AgentShellState();
}

class _AgentShellState extends State<AgentShell> {
  final _tabs = const [
    '/agent/dashboard',
    '/agent/leads',
    '/agent/deals',
    '/agent/bookings',
    '/agent/profile',
  ];

  int _indexForLocation(String location) {
    if (location.startsWith('/agent/leads')) return 1;
    if (location.startsWith('/agent/deals')) return 2;
    if (location.startsWith('/agent/bookings')) return 3;
    if (location.startsWith('/agent/profile')) return 4;
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
            icon: Icon(Icons.view_kanban_outlined),
            activeIcon: Icon(Icons.view_kanban),
            label: 'Leads',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.filter_alt_outlined),
            activeIcon: Icon(Icons.filter_alt),
            label: 'Deals',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.book_online_outlined),
            activeIcon: Icon(Icons.book_online),
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

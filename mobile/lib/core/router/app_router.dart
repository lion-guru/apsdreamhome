import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../presentation/pages/auth/login_page.dart';
import '../presentation/pages/home/home_page.dart';
import '../presentation/pages/properties/property_list_page.dart';
import '../presentation/pages/leads/lead_list_page.dart';
import '../presentation/pages/mlm/mlm_dashboard_page.dart';
import '../presentation/pages/mlm/genealogy_page.dart';
import '../presentation/pages/mlm/incentive_dashboard_page.dart';
import '../presentation/pages/mlm/document_locker_page.dart';
import '../presentation/pages/site_visit/site_visit_page.dart';
import '../presentation/pages/properties/property_swipe_page.dart';
import '../presentation/pages/mlm/auto_payout_page.dart';
import '../presentation/pages/customer/customer_bookings_page.dart';
import '../presentation/pages/customer/emi_schedule_page.dart';
import '../presentation/pages/properties/sell_property_page.dart';
import '../presentation/pages/profile/profile_page.dart';
import '../presentation/providers/auth_provider.dart';

final appRouterProvider = Provider<GoRouter>((ref) {
  final authState = ref.watch(authProvider);
  
  return GoRouter(
    initialLocation: '/login',
    redirect: (context, state) {
      final isAuthenticated = authState.maybeWhen(
        data: (user) => user != null,
        orElse: () => false,
      );
      
      final isLoginPage = state.location == '/login';
      
      if (!isAuthenticated && !isLoginPage) {
        return '/login';
      }
      
      if (isAuthenticated && isLoginPage) {
        return '/home';
      }
      
      return null;
    },
    routes: [
      GoRoute(
        path: '/login',
        builder: (context, state) => const LoginPage(),
      ),
      ShellRoute(
        builder: (context, state, child) {
          return MainScaffold(child: child);
        },
        routes: [
          GoRoute(
            path: '/home',
            builder: (context, state) => const HomePage(),
          ),
          GoRoute(
            path: '/properties',
            builder: (context, state) => const PropertyListPage(),
            routes: [
              GoRoute(
                path: 'discover',
                builder: (context, state) => const PropertySwipePage(),
              ),
              GoRoute(
                path: 'sell',
                builder: (context, state) => const SellPropertyPage(),
              ),
            ],
          ),
          GoRoute(
            path: '/leads',
            builder: (context, state) => const LeadListPage(),
          ),
          GoRoute(
            path: '/mlm',
            builder: (context, state) => const MLMDashboardPage(),
            routes: [
              GoRoute(
                path: 'genealogy',
                builder: (context, state) => const GenealogyPage(),
              ),
              GoRoute(
                path: 'incentives',
                builder: (context, state) => const IncentiveDashboardPage(),
              ),
              GoRoute(
                path: 'documents',
                builder: (context, state) => const DocumentLockerPage(),
              ),
              GoRoute(
                path: 'site-visit',
                builder: (context, state) {
                  final extra = state.extra as Map<String, dynamic>?;
                  return SiteVisitPage(
                    visitId: extra?['visit_id'] as int?,
                    propertyName: extra?['property_name'] as String?,
                    destLat: extra?['dest_lat'] as double?,
                    destLng: extra?['dest_lng'] as double?,
                  );
                },
              ),
              GoRoute(
                path: 'auto-payout',
                builder: (context, state) => const AutoPayoutPage(),
              ),
            ],
          ),
          
          // Customer Routes
          GoRoute(
            path: '/customer/bookings',
            builder: (context, state) => const CustomerBookingsPage(),
          ),
          GoRoute(
            path: '/customer/emi-schedule',
            builder: (context, state) {
              final bookingId = state.extra as int;
              return EmiSchedulePage(bookingId: bookingId);
            },
          ),

          GoRoute(
            path: '/profile',
            builder: (context, state) => const ProfilePage(),
          ),
        ],
      ),
    ],
    errorBuilder: (context, state) => ErrorPage(error: state.error),
  );
});

class MainScaffold extends ConsumerWidget {
  const MainScaffold({super.key, required this.child});
  
  final Widget child;
  
  @override
  Widget build(BuildContext context, WidgetRef ref) {
    int selectedIndex = 0;
    
    // Determine selected index based on current route
    final location = GoRouterState.of(context).location;
    if (location.startsWith('/properties')) {
      selectedIndex = 1;
    } else if (location.startsWith('/leads')) {
      selectedIndex = 2;
    } else if (location.startsWith('/mlm')) {
      selectedIndex = 3;
    } else if (location.startsWith('/profile')) {
      selectedIndex = 4;
    } else {
      selectedIndex = 0;
    }
    
    return Scaffold(
      body: child,
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: selectedIndex,
        onTap: (index) {
          switch (index) {
            case 0:
              context.go('/home');
              break;
            case 1:
              context.go('/properties');
              break;
            case 2:
              context.go('/leads');
              break;
            case 3:
              context.go('/mlm');
              break;
            case 4:
              context.go('/profile');
              break;
          }
        },
        type: BottomNavigationBarType.fixed,
        selectedItemColor: Theme.of(context).colorScheme.primary,
        unselectedItemColor: Colors.grey,
        items: const [
          BottomNavigationBarItem(
            icon: Icon(Icons.home_outlined),
            activeIcon: Icon(Icons.home),
            label: 'Home',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.apartment_outlined),
            activeIcon: Icon(Icons.apartment),
            label: 'Properties',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.people_outline),
            activeIcon: Icon(Icons.people),
            label: 'Leads',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.trending_up_outlined),
            activeIcon: Icon(Icons.trending_up),
            label: 'MLM',
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

class ErrorPage extends StatelessWidget {
  const ErrorPage({super.key, this.error});
  
  final dynamic error;
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Error')),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error_outline, size: 64, color: Colors.red),
            const SizedBox(height: 16),
            const Text('An error occurred', style: TextStyle(fontSize: 24)),
            const SizedBox(height: 8),
            Text(error?.toString() ?? 'Unknown error'),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: () => context.go('/home'),
              child: const Text('Go Home'),
            ),
          ],
        ),
      ),
    );
  }
}

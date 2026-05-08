import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../presentation/pages/auth/login_page.dart';
import '../../presentation/pages/customer/home_page.dart';
import '../../presentation/pages/property/property_marketplace_page.dart';
import '../../presentation/pages/common/notifications_page.dart';
import '../../presentation/pages/customer/colonies_page.dart';
import '../../presentation/pages/customer/plots_page.dart';
import '../../presentation/pages/customer/booking_page.dart';
import '../../presentation/pages/customer/my_bookings_page.dart';
import '../../presentation/pages/tools/emi_calculator_page.dart';
import '../../presentation/pages/tools/property_valuation_page.dart';
import '../../presentation/pages/ai/advanced_ai_chat_page.dart';
import '../../presentation/pages/common/profile_page.dart';
import '../../presentation/pages/payment/payment_page.dart';
import '../../presentation/pages/customer/favorites_page.dart';
import '../../presentation/pages/customer/kyc_verification_page.dart';
import '../../presentation/pages/customer/kyc_status_page.dart';
import '../../presentation/pages/associate/leads_page.dart';
import '../../presentation/pages/associate/associate_dashboard_page.dart';
import '../../core/providers/auth_provider.dart';

final appRouterProvider = Provider<GoRouter>((ref) {
  final authState = ref.watch(authProvider);

  return GoRouter(
    initialLocation: '/login',
    redirect: (context, state) {
      final isAuthenticated = authState.maybeWhen(
        data: (user) => user != null,
        orElse: () => false,
      );

      final isLoginPage = state.uri.toString() == '/login';

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
      // Main Routes
      GoRoute(
        path: '/home',
        builder: (context, state) => const HomePage(),
      ),
      GoRoute(
        path: '/properties',
        builder: (context, state) => const PropertyMarketplacePage(),
      ),
      GoRoute(
        path: '/colonies',
        builder: (context, state) => const ColoniesPage(),
      ),
      GoRoute(
        path: '/plots',
        builder: (context, state) => const PlotsPage(),
      ),
      GoRoute(
        path: '/booking/:plotId',
        builder: (context, state) => BookingPage(
          plotId: state.pathParameters['plotId']!,
        ),
      ),
      GoRoute(
        path: '/my-bookings',
        builder: (context, state) => const MyBookingsPage(),
      ),
      GoRoute(
        path: '/emi-calculator',
        builder: (context, state) => const EMICalculatorPage(),
      ),
      GoRoute(
        path: '/property-valuation',
        builder: (context, state) => const PropertyValuationPage(),
      ),
      GoRoute(
        path: '/notifications',
        builder: (context, state) => const NotificationsPage(),
      ),
      GoRoute(
        path: '/profile',
        builder: (context, state) => const ProfilePage(),
      ),

      // AI Chat
      GoRoute(
        path: '/ai-chat',
        builder: (context, state) => const AdvancedAIChatPage(),
      ),

      // Payment Route
      GoRoute(
        path: '/payment',
        builder: (context, state) {
          final extra = state.extra as Map<String, dynamic>?;
          return PaymentPage(
            amount: (extra?['amount'] as num?)?.toDouble() ?? 0.0,
            description: extra?['description'] as String? ?? 'Payment',
            entityType: extra?['entity_type'] as String? ?? 'misc',
            entityId: extra?['entity_id'] as int? ?? 0,
            entityName: extra?['entity_name'] as String?,
          );
        },
      ),

      // KYC Routes
      GoRoute(
        path: '/kyc-verification',
        builder: (context, state) => const KYCVerificationPage(),
      ),
      GoRoute(
        path: '/kyc-status',
        builder: (context, state) => const KYCStatusPage(),
      ),

      // Favorites Route
      GoRoute(
        path: '/favorites',
        builder: (context, state) => const FavoritesPage(),
      ),

      // Leads Route
      GoRoute(
        path: '/leads',
        builder: (context, state) => const LeadsPage(),
      ),

      // MLM Dashboard Route
      GoRoute(
        path: '/mlm',
        builder: (context, state) => const AssociateDashboardPage(),
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
    final location = GoRouterState.of(context).uri.toString();
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

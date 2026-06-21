import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

// Auth
import '../../presentation/pages/auth/login_page.dart';
import '../../presentation/pages/auth/register_page.dart';
import '../../presentation/pages/auth/forgot_password_page.dart';
import '../../presentation/pages/auth/otp_page.dart';

// Customer
import '../../presentation/pages/customer/home_page.dart';
import '../../presentation/pages/customer/colonies_page.dart';
import '../../presentation/pages/customer/colony_detail_page.dart';
import '../../presentation/pages/customer/plots_page.dart';
import '../../presentation/pages/customer/booking_page.dart';
import '../../presentation/pages/customer/my_bookings_page.dart';
import '../../presentation/pages/customer/favorites_page.dart';
import '../../presentation/pages/customer/kyc_verification_page.dart';
import '../../presentation/pages/customer/kyc_status_page.dart';
import '../../presentation/pages/customer/emi_schedule_page.dart';
import '../../presentation/pages/customer/documents_page.dart';
import '../../presentation/pages/customer/colony_plot_grid_page.dart';
import '../../presentation/pages/customer/plot_detail_page.dart';
import '../../presentation/pages/customer/customer_bookings_page.dart';

// Property
import '../../presentation/pages/property/property_marketplace_page.dart';
import '../../presentation/pages/property/property_detail_page.dart';

// Common
import '../../presentation/pages/common/notifications_page.dart';
import '../../presentation/pages/common/profile_page.dart';
import '../../presentation/pages/common/settings_page.dart';

// Tools
import '../../presentation/pages/tools/emi_calculator_page.dart';
import '../../presentation/pages/tools/property_valuation_page.dart';
import '../../presentation/pages/tools/site_visit_scheduler_page.dart';
import '../../presentation/pages/tools/map_view_page.dart';
import '../../presentation/pages/tools/live_chat_page.dart';

// AI
import '../../presentation/pages/ai/advanced_ai_chat_page.dart';

// Payment
import '../../presentation/pages/payment/payment_page.dart';

// Associate
import '../../presentation/pages/associate/associate_dashboard_page.dart';
import '../../presentation/pages/associate/leads_page.dart';
import '../../presentation/pages/associate/offline_booking_page.dart';
import '../../presentation/pages/associate/commission_page.dart';
import '../../presentation/pages/associate/payout_page.dart';
import '../../presentation/pages/associate/my_team_page.dart';
import '../../presentation/pages/associate/genealogy_page.dart';

// Agent
import '../../presentation/pages/agent/agent_dashboard_page.dart';
import '../../presentation/pages/agent/lead_kanban_page.dart';
import '../../presentation/pages/agent/deal_pipeline_page.dart';
import '../../presentation/pages/agent/commission_approval_page.dart';

// Employee
import '../../presentation/pages/employee/check_in_page.dart';

// Admin
import '../../presentation/pages/admin/admin_shell.dart';
import '../../presentation/pages/admin/admin_dashboard_page.dart';
import '../../presentation/pages/admin/crm_page.dart';
import '../../presentation/pages/admin/booking_approvals_page.dart';
import '../../presentation/pages/admin/user_management_page.dart';
import '../../presentation/pages/admin/reports_page.dart';
import '../../presentation/pages/admin/colony_management_page.dart';
import '../../presentation/pages/admin/plot_management_page.dart';
import '../../presentation/pages/admin/employee_management_page.dart';
import '../../presentation/pages/admin/commission_approvals_page.dart';
import '../../presentation/pages/admin/accounts_page.dart';
import '../../presentation/pages/admin/analytics_dashboard_page.dart';
import '../../presentation/pages/admin/campaign_management_page.dart';
import '../../presentation/pages/admin/bulk_marketing_page.dart';
import '../../presentation/pages/admin/admin_tools_page.dart';
import '../../presentation/pages/admin/dev_tools_page.dart';

// MLM
import '../../presentation/pages/mlm/mlm_dashboard_page.dart';

// Auth provider
import '../providers/auth_provider.dart';
// User model
import '../../data/models/user_model.dart';

final appRouterProvider = Provider<GoRouter>((ref) {
  final authState = ref.watch(authProvider);

  return GoRouter(
    initialLocation: '/login',
    redirect: (context, state) {
      final isAuthenticated = authState != null;
      final uri = state.uri.toString();
      final isLoginPage = uri == '/login';
      final isPublicRoute = isLoginPage ||
          uri == '/register' ||
          uri.startsWith('/otp') ||
          uri.startsWith('/forgot-password');

      if (!isAuthenticated && !isPublicRoute) {
        return '/login';
      }

      if (isAuthenticated && isLoginPage) {
        return _defaultRouteForRole(authState);
      }

      return null;
    },
    routes: [
      // ─── Auth Routes ───
      GoRoute(
        path: '/login',
        builder: (context, state) => const LoginPage(),
      ),
      GoRoute(
        path: '/register',
        builder: (context, state) => const RegisterPage(),
      ),
      GoRoute(
        path: '/forgot-password',
        builder: (context, state) => const ForgotPasswordPage(),
      ),
      GoRoute(
        path: '/otp',
        builder: (context, state) => const OTPPage(),
      ),

      // ─── Customer Routes ───
      GoRoute(
        path: '/home',
        builder: (context, state) => const HomePage(),
      ),
      GoRoute(
        path: '/properties',
        builder: (context, state) => const PropertyMarketplacePage(),
      ),
      GoRoute(
        path: '/property-detail/:propertyId',
        builder: (context, state) {
          final extra = state.extra as Map<String, dynamic>?;
          return PropertyDetailPage(
            propertyId: state.pathParameters['propertyId']!,
            title: extra?['title'] as String? ?? '',
            price: (extra?['price'] as num?)?.toDouble() ?? 0,
            location: extra?['location'] as String? ?? '',
            area: (extra?['area'] as num?)?.toDouble() ?? 0,
            type: extra?['type'] as String? ?? '',
            description: extra?['description'] as String? ?? '',
            image: extra?['image'] as String? ?? '',
          );
        },
      ),
      GoRoute(
        path: '/colonies',
        builder: (context, state) => const ColoniesPage(),
      ),
      GoRoute(
        path: '/colony-detail/:colonyId',
        builder: (context, state) {
          return ColonyDetailPage(
            colonyId: state.pathParameters['colonyId']!,
          );
        },
      ),
      GoRoute(
        path: '/plots',
        builder: (context, state) => const PlotsPage(),
      ),
      GoRoute(
        path: '/colony-plots/:colonyId',
        builder: (context, state) {
          final colonyId = int.parse(state.pathParameters['colonyId']!);
          final extra = state.extra as Map<String, dynamic>?;
          return ColonyPlotGridPage(
            colonyId: colonyId,
            colonyName: extra?['colonyName'] as String? ?? 'Colony',
          );
        },
      ),
      GoRoute(
        path: '/plot-detail/:plotId',
        builder: (context, state) {
          return PlotDetailPage(
            plotId: state.pathParameters['plotId']!,
          );
        },
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
        path: '/customer-bookings',
        builder: (context, state) => const CustomerBookingsPage(),
      ),
      GoRoute(
        path: '/emi-schedule',
        builder: (context, state) => const EmiSchedulePage(),
      ),
      GoRoute(
        path: '/favorites',
        builder: (context, state) => const FavoritesPage(),
      ),
      GoRoute(
        path: '/documents',
        builder: (context, state) => const DocumentsPage(),
      ),
      GoRoute(
        path: '/kyc-verification',
        builder: (context, state) => const KYCVerificationPage(),
      ),
      GoRoute(
        path: '/kyc-status',
        builder: (context, state) => const KYCStatusPage(),
      ),

      // ─── Tools Routes ───
      GoRoute(
        path: '/emi-calculator',
        builder: (context, state) => const EMICalculatorPage(),
      ),
      GoRoute(
        path: '/property-valuation',
        builder: (context, state) => const PropertyValuationPage(),
      ),
      GoRoute(
        path: '/site-visit',
        builder: (context, state) => const SiteVisitSchedulerPage(),
      ),
      GoRoute(
        path: '/map',
        builder: (context, state) => const MapViewPage(),
      ),
      GoRoute(
        path: '/live-chat',
        builder: (context, state) => const LiveChatPage(),
      ),

      // ─── Common Routes ───
      GoRoute(
        path: '/notifications',
        builder: (context, state) => const NotificationsPage(),
      ),
      GoRoute(
        path: '/profile',
        builder: (context, state) => const ProfilePage(),
      ),
      GoRoute(
        path: '/settings',
        builder: (context, state) => const SettingsPage(),
      ),

      // ─── AI ───
      GoRoute(
        path: '/ai-chat',
        builder: (context, state) => const AdvancedAIChatPage(),
      ),

      // ─── Payment ───
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

      // ─── Associate / MLM Routes ───
      GoRoute(
        path: '/associate/dashboard',
        builder: (context, state) => const AssociateDashboardPage(),
      ),
      GoRoute(
        path: '/associate/leads',
        builder: (context, state) => const LeadsPage(),
      ),
      GoRoute(
        path: '/associate/offline-booking',
        builder: (context, state) => const OfflineBookingPage(),
      ),
      GoRoute(
        path: '/associate/commission',
        builder: (context, state) => const CommissionPage(),
      ),
      GoRoute(
        path: '/associate/payout',
        builder: (context, state) => const PayoutPage(),
      ),
      GoRoute(
        path: '/associate/team',
        builder: (context, state) => const MyTeamPage(),
      ),
      GoRoute(
        path: '/associate/genealogy',
        builder: (context, state) => const GenealogyPage(),
      ),

      // Legacy MLM routes (redirect to associate routes)
      GoRoute(
        path: '/mlm',
        builder: (context, state) => const AssociateDashboardPage(),
      ),
      GoRoute(
        path: '/leads',
        builder: (context, state) => const LeadsPage(),
      ),
      GoRoute(
        path: '/offline-booking',
        builder: (context, state) => const OfflineBookingPage(),
      ),
      GoRoute(
        path: '/mlm-dashboard',
        builder: (context, state) => const MLMDashboardPage(),
      ),

      // ─── Agent Routes ───
      GoRoute(
        path: '/agent/dashboard',
        builder: (context, state) => const AgentDashboardPage(),
      ),
      GoRoute(
        path: '/agent/leads',
        builder: (context, state) => const LeadKanbanPage(),
      ),
      GoRoute(
        path: '/agent/deals',
        builder: (context, state) => const DealPipelinePage(),
      ),
      GoRoute(
        path: '/agent/commissions',
        builder: (context, state) => const CommissionApprovalPage(),
      ),

      // ─── Employee Routes ───
      GoRoute(
        path: '/employee/check-in',
        builder: (context, state) => const CheckInPage(),
      ),

      // ─── Admin Shell (wraps all /admin sub-routes) ───
      ShellRoute(
        builder: (context, state, child) => AdminShell(child: child),
        routes: [
          GoRoute(
            path: '/admin',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: AdminDashboardPage(),
            ),
          ),
          GoRoute(
            path: '/admin/crm',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: CRMPage(),
            ),
          ),
          GoRoute(
            path: '/admin/bookings',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: BookingApprovalsPage(),
            ),
          ),
          GoRoute(
            path: '/admin/customers',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: UserManagementPage(),
            ),
          ),
          GoRoute(
            path: '/admin/reports',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: ReportsPage(),
            ),
          ),
          GoRoute(
            path: '/admin/colonies',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: ColonyManagementPage(),
            ),
          ),
          GoRoute(
            path: '/admin/plots',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: PlotManagementPage(),
            ),
          ),
          GoRoute(
            path: '/admin/employees',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: EmployeeManagementPage(),
            ),
          ),
          GoRoute(
            path: '/admin/commissions',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: CommissionApprovalsPage(),
            ),
          ),
          GoRoute(
            path: '/admin/accounts',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: AccountsPage(),
            ),
          ),
          GoRoute(
            path: '/admin/analytics',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: AnalyticsDashboardPage(),
            ),
          ),
          GoRoute(
            path: '/admin/marketing',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: CampaignManagementPage(),
            ),
          ),
          GoRoute(
            path: '/admin/bulk-marketing',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: BulkMarketingPage(),
            ),
          ),
          GoRoute(
            path: '/admin/tools',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: AdminToolsPage(),
            ),
          ),
          GoRoute(
            path: '/admin/dev-tools',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: DevToolsPage(),
            ),
          ),
          GoRoute(
            path: '/admin/payouts',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: AccountsPage(),
            ),
          ),
          GoRoute(
            path: '/admin/invoices',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: AccountsPage(),
            ),
          ),
          GoRoute(
            path: '/admin/ledger',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: AccountsPage(),
            ),
          ),
          GoRoute(
            path: '/admin/emi',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: AccountsPage(),
            ),
          ),
          GoRoute(
            path: '/admin/leads',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: CRMPage(),
            ),
          ),
          GoRoute(
            path: '/admin/settings',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: SettingsPage(),
            ),
          ),
          GoRoute(
            path: '/admin/profile',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: ProfilePage(),
            ),
          ),
        ],
      ),
    ],
    errorBuilder: (context, state) => ErrorPage(error: state.error),
  );
});

String _defaultRouteForRole(User? user) {
  if (user == null) return '/home';
  if (user.isAdmin) return '/admin';
  if (user.isAgent) return '/agent/dashboard';
  if (user.isAssociate) return '/associate/dashboard';
  if (user.isEmployee) return '/employee/check-in';
  return '/home';
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

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
import '../../presentation/pages/customer/post_property_page.dart';

// Property
import '../../presentation/pages/property/property_marketplace_page.dart';
import '../../presentation/pages/property/property_detail_page.dart';

// Splash
import '../../presentation/pages/common/splash_page.dart';

// Common
import '../../presentation/pages/common/notifications_page.dart';
import '../../presentation/pages/common/profile_page.dart';
import '../../presentation/pages/common/settings_page.dart';
import '../../presentation/pages/common/notifications_center_page.dart';
import '../../presentation/pages/common/faq_page.dart';
import '../../presentation/pages/common/testimonials_page.dart';

// Customer Features
import '../../presentation/pages/customer/saved_searches_page.dart';
import '../../presentation/pages/customer/property_alerts_page.dart';
import '../../presentation/pages/customer/compare_properties_page.dart';
import '../../presentation/pages/customer/referral_page.dart';
import '../../presentation/pages/customer/language_page.dart';
import '../../presentation/pages/customer/support_tickets_page.dart';

// Tools
import '../../presentation/pages/tools/stamp_duty_calculator_page.dart';
import '../../presentation/pages/tools/plot_converter_page.dart';

// Customer Shell
import '../../presentation/pages/customer/customer_shell.dart';

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
import '../../presentation/pages/agent/agent_crm_page.dart';

// Employee
import '../../presentation/pages/employee/employee_shell.dart';
import '../../presentation/pages/employee/employee_dashboard_page.dart';
import '../../presentation/pages/employee/employee_tasks_page.dart';
import '../../presentation/pages/employee/check_in_page.dart';
import '../../presentation/pages/employee/employee_profile_page.dart';
import '../../presentation/pages/employee/employee_crm_page.dart';

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
  final router = GoRouter(
    initialLocation: '/splash',
    redirect: (context, state) {
      User? authState;
      try {
        authState = ref.read(authProvider);
      } catch (_) {
        authState = null;
      }
      final isAuthenticated = authState != null;
      final uri = state.uri.toString();

      // Auth pages
      final isLoginPage = uri == '/login';
      final isRegisterPage = uri == '/register';
      final isForgotPassword = uri.startsWith('/forgot-password');
      final isOtp = uri.startsWith('/otp');

      // Public pages — accessible without login
      final isSplash = uri == '/splash';
      final isHomePage = uri == '/home';
      final isColonies = uri == '/colonies';
      final isColonyDetail = uri.startsWith('/colony-detail');
      final isColonyPlots = uri.startsWith('/colony-plots');
      final isPlots = uri == '/plots';
      final isPlotDetail = uri.startsWith('/plot-detail');
      final isProperties = uri == '/properties';
      final isPropertyDetail = uri.startsWith('/property-detail');
      final isEmiCalc = uri == '/emi-calculator';
      final isValuation = uri == '/property-valuation';
      final isSiteVisit = uri == '/site-visit';
      final isMap = uri == '/map';
      final isLiveChat = uri == '/live-chat';
      final isAiChat = uri == '/ai-chat';

      final isPublicRoute = isSplash || isLoginPage || isRegisterPage ||
          isForgotPassword || isOtp || isHomePage || isColonies ||
          isColonyDetail || isColonyPlots || isPlots || isPlotDetail ||
          isProperties || isPropertyDetail || isEmiCalc || isValuation ||
          isSiteVisit || isMap || isLiveChat || isAiChat;

      // Allow all public and auth routes
      if (isPublicRoute) {
        // If logged in and on splash, redirect to role home
        if (isAuthenticated && isSplash) {
          return _defaultRouteForRole(authState);
        }
        // If logged in and on login/register, redirect to role home
        if (isAuthenticated && (isLoginPage || isRegisterPage)) {
          return _defaultRouteForRole(authState);
        }
        return null;
      }

      // Protected routes — redirect to login if not authenticated
      if (!isAuthenticated) {
        return '/login';
      }

      return null;
    },
    routes: [
      // ─── Splash ───
      GoRoute(
        path: '/splash',
        builder: (context, state) => const SplashPage(),
      ),

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

      // ─── Customer Shell (bottom nav for authenticated customers) ───
      ShellRoute(
        builder: (context, state, child) => CustomerShell(child: child),
        routes: [
          GoRoute(
            path: '/home',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: HomePage(),
            ),
          ),
          GoRoute(
            path: '/properties',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: PropertyMarketplacePage(),
            ),
          ),
          GoRoute(
            path: '/colonies',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: ColoniesPage(),
            ),
          ),
          GoRoute(
            path: '/plots',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: PlotsPage(),
            ),
          ),
          GoRoute(
            path: '/profile',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: ProfilePage(),
            ),
          ),
        ],
      ),

      // ─── Customer Detail Routes (full-screen, with slide-up transition) ───
      GoRoute(
        path: '/property-detail/:propertyId',
        pageBuilder: (context, state) {
          final extra = state.extra as Map<String, dynamic>?;
          return CustomTransitionPage<void>(
            child: PropertyDetailPage(
              propertyId: state.pathParameters['propertyId']!,
              title: extra?['title'] as String? ?? '',
              price: (extra?['price'] as num?)?.toDouble() ?? 0,
              location: extra?['location'] as String? ?? '',
              area: (extra?['area'] as num?)?.toDouble() ?? 0,
              type: extra?['type'] as String? ?? '',
              description: extra?['description'] as String? ?? '',
              image: extra?['image'] as String? ?? '',
            ),
            transitionsBuilder: (context, animation, secondaryAnimation, child) {
              return FadeTransition(
                opacity: CurvedAnimation(parent: animation, curve: Curves.easeOut),
                child: SlideTransition(
                  position: Tween<Offset>(
                    begin: const Offset(0, 0.05),
                    end: Offset.zero,
                  ).animate(CurvedAnimation(parent: animation, curve: Curves.easeOutCubic)),
                  child: child,
                ),
              );
            },
          );
        },
      ),
      GoRoute(
        path: '/colony-detail/:colonyId',
        pageBuilder: (context, state) {
          return CustomTransitionPage<void>(
            child: ColonyDetailPage(
              colonyId: state.pathParameters['colonyId']!,
            ),
            transitionsBuilder: (context, animation, secondaryAnimation, child) {
              return FadeTransition(
                opacity: CurvedAnimation(parent: animation, curve: Curves.easeOut),
                child: SlideTransition(
                  position: Tween<Offset>(
                    begin: const Offset(0, 0.05),
                    end: Offset.zero,
                  ).animate(CurvedAnimation(parent: animation, curve: Curves.easeOutCubic)),
                  child: child,
                ),
              );
            },
          );
        },
      ),
      GoRoute(
        path: '/colony-plots/:colonyId',
        pageBuilder: (context, state) {
          final colonyId = int.parse(state.pathParameters['colonyId']!);
          final extra = state.extra as Map<String, dynamic>?;
          return CustomTransitionPage<void>(
            child: ColonyPlotGridPage(
              colonyId: colonyId,
              colonyName: extra?['colonyName'] as String? ?? 'Colony',
            ),
            transitionsBuilder: (context, animation, secondaryAnimation, child) {
              return FadeTransition(
                opacity: CurvedAnimation(parent: animation, curve: Curves.easeOut),
                child: SlideTransition(
                  position: Tween<Offset>(
                    begin: const Offset(0, 0.05),
                    end: Offset.zero,
                  ).animate(CurvedAnimation(parent: animation, curve: Curves.easeOutCubic)),
                  child: child,
                ),
              );
            },
          );
        },
      ),
      GoRoute(
        path: '/plot-detail/:plotId',
        pageBuilder: (context, state) {
          return CustomTransitionPage<void>(
            child: PlotDetailPage(
              plotId: state.pathParameters['plotId']!,
            ),
            transitionsBuilder: (context, animation, secondaryAnimation, child) {
              return FadeTransition(
                opacity: CurvedAnimation(parent: animation, curve: Curves.easeOut),
                child: SlideTransition(
                  position: Tween<Offset>(
                    begin: const Offset(0, 0.05),
                    end: Offset.zero,
                  ).animate(CurvedAnimation(parent: animation, curve: Curves.easeOutCubic)),
                  child: child,
                ),
              );
            },
          );
        },
      ),
      GoRoute(
        path: '/booking/:plotId',
        pageBuilder: (context, state) {
          return CustomTransitionPage<void>(
            child: BookingPage(
              plotId: state.pathParameters['plotId']!,
            ),
            transitionsBuilder: (context, animation, secondaryAnimation, child) {
              return SlideTransition(
                position: Tween<Offset>(
                  begin: const Offset(0, 0.08),
                  end: Offset.zero,
                ).animate(CurvedAnimation(parent: animation, curve: Curves.easeOutCubic)),
                child: FadeTransition(
                  opacity: CurvedAnimation(parent: animation, curve: Curves.easeOut),
                  child: child,
                ),
              );
            },
          );
        },
      ),
      GoRoute(
        path: '/my-bookings',
        pageBuilder: (context, state) => const CustomTransitionPage<void>(child: MyBookingsPage(), transitionsBuilder: _slideTransition),
      ),
      GoRoute(
        path: '/customer-bookings',
        pageBuilder: (context, state) => const CustomTransitionPage<void>(child: CustomerBookingsPage(), transitionsBuilder: _slideTransition),
      ),
      GoRoute(
        path: '/emi-schedule',
        pageBuilder: (context, state) => const CustomTransitionPage<void>(child: EmiSchedulePage(), transitionsBuilder: _slideTransition),
      ),
      GoRoute(
        path: '/favorites',
        pageBuilder: (context, state) => const CustomTransitionPage<void>(child: FavoritesPage(), transitionsBuilder: _slideTransition),
      ),
      GoRoute(
        path: '/documents',
        pageBuilder: (context, state) => const CustomTransitionPage<void>(child: DocumentsPage(), transitionsBuilder: _slideTransition),
      ),
      GoRoute(
        path: '/kyc-verification',
        pageBuilder: (context, state) => const CustomTransitionPage<void>(child: KYCVerificationPage(), transitionsBuilder: _slideTransition),
      ),
      GoRoute(
        path: '/kyc-status',
        pageBuilder: (context, state) => const CustomTransitionPage<void>(child: KYCStatusPage(), transitionsBuilder: _slideTransition),
      ),
      GoRoute(
        path: '/post-property',
        pageBuilder: (context, state) => const CustomTransitionPage<void>(child: PostPropertyPage(), transitionsBuilder: _slideTransition),
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
        path: '/settings',
        builder: (context, state) => const SettingsPage(),
      ),
      GoRoute(
        path: '/notifications-center',
        builder: (context, state) => const NotificationsCenterPage(),
      ),
      GoRoute(
        path: '/faq',
        builder: (context, state) => const FaqPage(),
      ),
      GoRoute(
        path: '/testimonials',
        builder: (context, state) => const TestimonialsPage(),
      ),

      // ─── Customer Feature Routes ───
      GoRoute(
        path: '/saved-searches',
        builder: (context, state) => const SavedSearchesPage(),
      ),
      GoRoute(
        path: '/property-alerts',
        builder: (context, state) => const PropertyAlertsPage(),
      ),
      GoRoute(
        path: '/compare',
        builder: (context, state) => const ComparePropertiesPage(),
      ),
      GoRoute(
        path: '/referral',
        builder: (context, state) => const ReferralPage(),
      ),
      GoRoute(
        path: '/language',
        builder: (context, state) => const LanguagePage(),
      ),
      GoRoute(
        path: '/support-tickets',
        builder: (context, state) => const SupportTicketsPage(),
      ),

      // ─── Tools Routes (extended) ───
      GoRoute(
        path: '/stamp-duty-calculator',
        builder: (context, state) => const StampDutyCalculatorPage(),
      ),
      GoRoute(
        path: '/plot-converter',
        builder: (context, state) => const PlotConverterPage(),
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
      GoRoute(
        path: '/associate/crm',
        builder: (context, state) => const AgentCRMPage(),
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
      GoRoute(
        path: '/agent/crm',
        builder: (context, state) => const AgentCRMPage(),
      ),

      // ─── Employee Shell ───
      ShellRoute(
        builder: (context, state, child) => EmployeeShell(child: child),
        routes: [
          GoRoute(
            path: '/employee/dashboard',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: EmployeeDashboardPage(),
            ),
          ),
          GoRoute(
            path: '/employee/tasks',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: EmployeeTasksPage(),
            ),
          ),
          GoRoute(
            path: '/employee/check-in',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: CheckInPage(),
            ),
          ),
          GoRoute(
            path: '/employee/profile',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: EmployeeProfilePage(),
            ),
          ),
          GoRoute(
            path: '/employee/crm',
            pageBuilder: (context, state) => const NoTransitionPage(
              child: EmployeeCRMPage(),
            ),
          ),
        ],
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

  return router;
});

String _defaultRouteForRole(User? user) {
  if (user == null) return '/home';
  if (user.isAdmin) return '/admin';
  if (user.isAgent) return '/agent/dashboard';
  if (user.isAssociate) return '/associate/dashboard';
  if (user.isEmployee) return '/employee/dashboard';
  return '/home';
}

Widget _slideTransition(
  BuildContext context,
  Animation<double> animation,
  Animation<double> secondaryAnimation,
  Widget child,
) {
  return FadeTransition(
    opacity: CurvedAnimation(parent: animation, curve: Curves.easeOut),
    child: SlideTransition(
      position: Tween<Offset>(
        begin: const Offset(0, 0.04),
        end: Offset.zero,
      ).animate(CurvedAnimation(parent: animation, curve: Curves.easeOutCubic)),
      child: child,
    ),
  );
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

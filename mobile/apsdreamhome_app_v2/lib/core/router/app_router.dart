import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

// Auth
import '../../core/providers/auth_provider.dart';
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
import '../../presentation/pages/property/property_gallery_page.dart';

// Splash
import '../../presentation/pages/common/splash_page.dart';

// Common
import '../../presentation/pages/common/notifications_page.dart';
import '../../presentation/pages/common/profile_page.dart';
import '../../presentation/pages/common/settings_page.dart';
import '../../presentation/pages/common/notifications_center_page.dart';
import '../../presentation/pages/common/faq_page.dart';
import '../../presentation/pages/common/testimonials_page.dart';
import '../../presentation/pages/common/about_page.dart';
import '../../presentation/pages/common/blog_page.dart';
import '../../presentation/pages/common/blog_detail_page.dart';
import '../../presentation/pages/common/careers_page.dart';
import '../../presentation/pages/common/career_detail_page.dart';
import '../../presentation/pages/common/how_it_works_page.dart';
import '../../presentation/pages/common/insurance_page.dart';
import '../../presentation/pages/common/nach_mandate_page.dart';
import '../../presentation/pages/common/agreement_page.dart';
import '../../presentation/pages/common/services_directory_page.dart';
import '../../presentation/pages/common/tools_hub_page.dart';
import '../../presentation/pages/common/projects_page.dart';
import '../../presentation/pages/common/legal_documents_page.dart';
import '../../presentation/pages/common/legal_document_detail_page.dart';
import '../../presentation/pages/common/legal_document_preview_page.dart';
import '../../presentation/pages/common/document_esign_page.dart';
import '../../presentation/pages/common/document_esign_detail_page.dart';
import '../../presentation/pages/common/contact_page.dart';
import '../../presentation/pages/common/team_page.dart';
import '../../presentation/pages/common/privacy_policy_page.dart';
import '../../presentation/pages/common/terms_conditions_page.dart';
import '../../presentation/pages/common/legal_services_page.dart';
import '../../presentation/pages/common/disclaimer_page.dart';
import '../../presentation/pages/common/cancellation_policy_page.dart';
import '../../presentation/pages/common/buy_page.dart';
import '../../presentation/pages/common/sell_page.dart';
import '../../presentation/pages/common/resell_properties_page.dart';
import '../../presentation/pages/common/rent_page.dart';
import '../../presentation/pages/common/invest_page.dart';
import '../../presentation/pages/common/gallery_page.dart';
import '../../presentation/pages/common/welcome_screen_page.dart';
import '../../presentation/pages/common/inbox_page.dart';
import '../../presentation/pages/common/chat_detail_page.dart';
import '../../presentation/pages/customer/payment_history_page.dart';

// Customer Features
import '../../presentation/pages/customer/saved_searches_page.dart';
import '../../presentation/pages/customer/property_alerts_page.dart';
import '../../presentation/pages/property/comparison_page.dart';
import '../../presentation/pages/customer/referral_page.dart';
import '../../presentation/pages/customer/language_page.dart';
import '../../presentation/pages/customer/support_tickets_page.dart';
import '../../presentation/pages/customer/user_agreements_page.dart';

// Tools
import '../../presentation/pages/tools/stamp_duty_calculator_page.dart';
import '../../presentation/pages/tools/plot_converter_page.dart';
import '../../presentation/pages/tools/capital_gains_page.dart';
import '../../presentation/pages/tools/construction_cost_page.dart';
import '../../presentation/pages/tools/rental_yield_page.dart';
import '../../presentation/pages/tools/rent_vs_buy_page.dart';
import '../../presentation/pages/tools/property_tax_page.dart';
import '../../presentation/pages/tools/sip_vs_realestate_page.dart';
import '../../presentation/pages/tools/gst_calculator_page.dart';
import '../../presentation/pages/tools/rera_lookup_page.dart';
import '../../presentation/pages/tools/title_protection_page.dart';
import '../../presentation/pages/tools/property_verification_page.dart';
import '../../presentation/pages/tools/investment_calculator_page.dart';
import '../../presentation/pages/tools/neighborhood_page.dart';
import '../../presentation/pages/tools/virtual_tour_page.dart';
import '../../presentation/pages/tools/news_page.dart';
import '../../presentation/pages/common/colony_health_page.dart';

// Customer Shell
import '../../presentation/pages/customer/customer_shell.dart';
import '../../presentation/pages/customer/customer_dashboard_page.dart';

// Tools
import '../../presentation/pages/tools/emi_calculator_page.dart';
import '../../presentation/pages/tools/home_loan_eligibility_page.dart';
import '../../presentation/pages/tools/property_valuation_page.dart';
import '../../presentation/pages/tools/site_visit_scheduler_page.dart';
import '../../presentation/pages/tools/my_site_visits_page.dart';
import '../../presentation/pages/tools/map_view_page.dart';
import '../../presentation/pages/tools/live_chat_page.dart';

// AI
import '../../presentation/pages/ai/advanced_ai_chat_page.dart';
import '../../presentation/pages/ai/ai_agent_dashboard_page.dart';

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
import '../../presentation/pages/agent/lead_create_page.dart';
import '../../presentation/pages/agent/agent_analytics_page.dart';
import '../../presentation/pages/agent/agent_bookings_page.dart';
import '../../presentation/pages/agent/agent_documents_page.dart';
import '../../presentation/pages/agent/agent_follow_ups_page.dart';
import '../../presentation/pages/agent/agent_properties_page.dart';
import '../../presentation/pages/agent/agent_site_visits_page.dart';
import '../../presentation/pages/agent/agent_my_team_page.dart';
import '../../presentation/pages/agent/agent_rank_progress_page.dart';

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

// Orphaned pages (wired in Session 52)
import '../../presentation/pages/properties/sell_property_page.dart';
import '../../presentation/pages/voice/voice_to_lead_page.dart';
import '../../presentation/pages/mlm/document_locker_page.dart';
import '../../presentation/pages/mlm/incentive_dashboard_page.dart';
import '../../presentation/pages/receipt/receipt_view_page.dart';
import '../../presentation/pages/site_visit/site_visit_page.dart';
import '../../presentation/pages/telecaller/telecaller_dashboard_page.dart';
import '../../presentation/pages/telecaller/auto_dialer_dashboard_page.dart';
import '../../presentation/pages/telecaller/templates_page.dart';
import '../../presentation/pages/telecaller/bulk_operations_page.dart';
import '../../presentation/pages/telecaller/voice_call_page.dart';

// User model
import '../../data/models/user_model.dart';

/// Global auth state bridge — ValueNotifier avoids Riverpod ↔ InheritedModel conflict in GoRouter redirect
class AuthBridge {
  AuthBridge._();
  static final AuthBridge instance = AuthBridge._();
  final ValueNotifier<User?> currentUser = ValueNotifier<User?>(null);
}

/// Global router instance — NOT a Riverpod Provider to avoid InheritedModel assertion
GoRouter? _globalRouter;

GoRouter getRouter() {
  _globalRouter ??= createRouter();
  return _globalRouter!;
}

void refreshRouter() {
  _globalRouter = createRouter();
}

/// Centralized logout — clears everything and navigates to /login.
/// Pass BuildContext + optional WidgetRef to clear Riverpod state too.
Future<void> appLogout(BuildContext context, [dynamic ref]) async {
  try {
    // Clear Riverpod auth state (full token + DB cleanup)
    if (ref != null) {
      await ref.read(authProvider.notifier).logout();
    }
  } catch (_) {}
  // Clear GoRouter auth bridge
  AuthBridge.instance.currentUser.value = null;
  // Navigate to login (replaces entire nav stack)
  if (context.mounted) {
    context.go('/login');
  }
}

GoRouter createRouter() {
  final router = GoRouter(
    initialLocation: '/splash',
    redirect: (context, state) {
      final authUser = AuthBridge.instance.currentUser.value;
      final isAuthenticated = authUser != null;
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
      final isColonyHealth = uri == '/colony-health';
      final isColonyDetail = uri.startsWith('/colony-detail');
      final isColonyPlots = uri.startsWith('/colony-plots');
      final isPlots = uri == '/plots';
      final isPlotDetail = uri.startsWith('/plot-detail');
      final isProperties = uri == '/properties';
      final isPropertyDetail = uri.startsWith('/property-detail');
      final isPropertyGallery = uri.startsWith('/property-gallery');
      final isEmiCalc = uri == '/emi-calculator';
      final isValuation = uri == '/property-valuation';
      final isSiteVisit = uri == '/site-visit';
      final isMap = uri == '/map';
      final isLiveChat = uri == '/live-chat';
      final isAiChat = uri == '/ai-chat';
      final isAiAgent = uri == '/ai-agent';
      final isHowItWorks = uri == '/how-it-works';
      final isInsurance = uri == '/insurance';
      final isNachMandate = uri == '/nach-mandate';
      final isAgreements = uri == '/agreements';
      final isServices = uri == '/services';
      final isToolsHub = uri == '/tools-hub';
      final isProjects = uri == '/projects';
      final isHomeLoanEligibility = uri == '/home-loan-eligibility';
      final isBlog = uri == '/blog';
      final isBlogDetail = uri.startsWith('/blog/');
      final isLegalDocs = uri == '/legal-documents';
      final isLegalDocDetail = uri.startsWith('/legal-documents/');
      final isDocEsign = uri == '/document-esign';
      final isDocEsignDetail = uri.startsWith('/document-esign/');
      final isContact = uri == '/contact';
      final isTeam = uri == '/team';
      final isPrivacy = uri == '/privacy';
      final isTerms = uri == '/terms';
      final isLegalServices = uri == '/legal/services';
      final isDisclaimer = uri == '/disclaimer';
      final isCancellationPolicy = uri == '/cancellation-policy';
      final isBuy = uri == '/buy';
      final isSell = uri == '/sell';
      final isResellProperties = uri == '/resell-properties';
      final isRent = uri == '/rent';
      final isInvest = uri == '/invest';
      final isGallery = uri == '/gallery';
      final isCapitalGains = uri == '/capital-gains-calculator';
      final isConstructionCost = uri == '/construction-cost-estimator';
      final isRentalYield = uri == '/rental-yield-calculator';
      final isRentVsBuy = uri == '/rent-vs-buy';
      final isPropertyTax = uri == '/property-tax-calculator';
      final isSipVsRealestate = uri == '/sip-vs-realestate';
      final isGst = uri == '/gst-calculator';
      final isReraLookup = uri == '/rera-lookup';
      final isTitleProtection = uri == '/title-protection';
      final isPropertyVerification = uri == '/property-verification';
      final isInvestmentCalculator = uri == '/investment-calculator';
      final isNeighborhood = uri == '/neighborhood';
      final isVirtualTour = uri == '/virtual-tour';
      final isNews = uri == '/news';
      final isWelcome = uri == '/welcome';
      final isSellProperty = uri == '/sell-property';
      final isFaq = uri == '/faq';
      final isAbout = uri == '/about';
      final isCareers = uri == '/careers' || uri.startsWith('/careers/');
      final isTestimonials = uri == '/testimonials';
      final isStampDuty = uri == '/stamp-duty-calculator';
      final isPlotConverter = uri == '/plot-converter';
      final isCompare = uri == '/compare';

final isPublicRoute =
          isSplash ||
          isLoginPage ||
          isRegisterPage ||
          isForgotPassword ||
          isOtp ||
          isHomePage ||
          isColonies ||
          isColonyHealth ||
          isColonyDetail ||
          isColonyPlots ||
          isPlots ||
          isPlotDetail ||
          isProperties ||
          isPropertyDetail ||
          isPropertyGallery ||
          isEmiCalc ||
          isValuation ||
          isSiteVisit ||
          isMap ||
          isLiveChat ||
          isAiChat ||
          isAiAgent ||
          isHowItWorks ||
          isInsurance ||
          isNachMandate ||
          isAgreements ||
          isServices ||
          isToolsHub ||
          isProjects ||
          isHomeLoanEligibility ||
          isContact ||
          isTeam ||
          isPrivacy ||
          isTerms ||
          isLegalServices ||
          isDisclaimer ||
          isCancellationPolicy ||
          isBuy ||
          isSell ||
          isResellProperties ||
          isRent ||
          isInvest ||
          isGallery ||
          isCapitalGains ||
          isConstructionCost ||
          isRentalYield ||
          isRentVsBuy ||
          isPropertyTax ||
          isSipVsRealestate ||
          isGst ||
          isBlog ||
          isBlogDetail ||
          isReraLookup ||
          isTitleProtection ||
          isPropertyVerification ||
          isInvestmentCalculator ||
          isNeighborhood ||
          isVirtualTour ||
          isNews ||
          isWelcome ||
          isSellProperty ||
          isLegalDocs ||
          isLegalDocDetail ||
          isFaq ||
          isAbout ||
          isCareers ||
          isTestimonials ||
          isStampDuty ||
          isPlotConverter ||
          isCompare ||
          isDocEsign ||
          isDocEsignDetail;

      // Allow all public and auth routes
      if (isPublicRoute) {
        // If logged in and on splash, redirect to role home
        if (isAuthenticated && isSplash) {
          return defaultRouteForRole(authUser);
        }
        // If logged in and on login/register, redirect to role home
        if (isAuthenticated && (isLoginPage || isRegisterPage)) {
          return defaultRouteForRole(authUser);
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
      GoRoute(path: '/splash', builder: (context, state) => const SplashPage()),

      // ─── Public Routes ───
      GoRoute(
        path: '/how-it-works',
        builder: (context, state) => const HowItWorksPage(),
      ),
      GoRoute(
        path: '/insurance',
        builder: (context, state) => const InsurancePage(),
      ),
      GoRoute(
        path: '/nach-mandate',
        builder: (context, state) => const NACHMandatePage(),
      ),
      GoRoute(
        path: '/agreements',
        builder: (context, state) => const AgreementPage(),
      ),
      GoRoute(
        path: '/services',
        builder: (context, state) => const ServicesDirectoryPage(),
      ),
      GoRoute(
        path: '/tools-hub',
        builder: (context, state) => const ToolsHubPage(),
      ),
      GoRoute(
        path: '/home-loan-eligibility',
        builder: (context, state) => const HomeLoanEligibilityPage(),
      ),
      GoRoute(
        path: '/projects',
        builder: (context, state) => const ProjectsPage(),
      ),
      GoRoute(
        path: '/contact',
        builder: (context, state) => const ContactPage(),
      ),
      GoRoute(path: '/team', builder: (context, state) => const TeamPage()),
      GoRoute(
        path: '/privacy',
        builder: (context, state) => const PrivacyPolicyPage(),
      ),
      GoRoute(
        path: '/terms',
        builder: (context, state) => const TermsConditionsPage(),
      ),
      GoRoute(
        path: '/legal/services',
        builder: (context, state) => const LegalServicesPage(),
      ),
      GoRoute(
        path: '/disclaimer',
        builder: (context, state) => const DisclaimerPage(),
      ),
      GoRoute(
        path: '/cancellation-policy',
        builder: (context, state) => const CancellationPolicyPage(),
      ),
      GoRoute(path: '/buy', builder: (context, state) => const BuyPage()),
      GoRoute(path: '/sell', builder: (context, state) => const SellPage()),
      GoRoute(
        path: '/resell-properties',
        builder: (context, state) => const ResellPropertiesPage(),
      ),
      GoRoute(path: '/rent', builder: (context, state) => const RentPage()),
      GoRoute(path: '/invest', builder: (context, state) => const InvestPage()),
      GoRoute(
        path: '/gallery',
        builder: (context, state) => const GalleryPage(),
      ),

      // ─── Auth Routes ───
      GoRoute(path: '/login', builder: (context, state) => const LoginPage()),
      GoRoute(
        path: '/register',
        builder: (context, state) => const RegisterPage(),
      ),
      GoRoute(
        path: '/forgot-password',
        builder: (context, state) => const ForgotPasswordPage(),
      ),
      GoRoute(path: '/otp', builder: (context, state) => const OTPPage()),
      GoRoute(
        path: '/welcome',
        builder: (context, state) {
          final extra = state.extra as Map<String, dynamic>? ?? {};
          return WelcomeScreenPage(
            userName: (extra['userName'] as String?) ?? 'User',
            role: (extra['role'] as String?) ?? 'customer',
            registeredOnMobile: (extra['registeredOnMobile'] as bool?) ?? true,
          );
        },
      ),

      // ─── Customer Shell (bottom nav for authenticated customers) ───
      ShellRoute(
        builder: (context, state, child) => CustomerShell(child: child),
        routes: [
          GoRoute(
            path: '/home',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: HomePage()),
          ),
          GoRoute(
            path: '/customer-dashboard',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: CustomerDashboardPage()),
          ),
          GoRoute(
            path: '/properties',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: PropertyMarketplacePage()),
          ),
          GoRoute(
            path: '/colonies',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: ColoniesPage()),
          ),
          GoRoute(
            path: '/colony-health',
            builder: (context, state) => const ColonyHealthPage(),
          ),
          GoRoute(
            path: '/plots',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: PlotsPage()),
          ),
          GoRoute(
            path: '/profile',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: ProfilePage()),
          ),
          GoRoute(
            path: '/user/agreements',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: UserAgreementsPage()),
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
              images:
                  (extra?['images'] as List<dynamic>?)
                      ?.map((e) => e.toString())
                      .toList() ??
                  [],
            ),
            transitionsBuilder:
                (context, animation, secondaryAnimation, child) {
                  return FadeTransition(
                    opacity: CurvedAnimation(
                      parent: animation,
                      curve: Curves.easeOut,
                    ),
                    child: SlideTransition(
                      position:
                          Tween<Offset>(
                            begin: const Offset(0, 0.05),
                            end: Offset.zero,
                          ).animate(
                            CurvedAnimation(
                              parent: animation,
                              curve: Curves.easeOutCubic,
                            ),
                          ),
                      child: child,
                    ),
                  );
                },
          );
        },
      ),

      // ─── Property Gallery ───
      GoRoute(
        path: '/property-gallery/:propertyId',
        pageBuilder: (context, state) {
          final extra = state.extra as Map<String, dynamic>?;
          return CustomTransitionPage<void>(
            child: PropertyGalleryPage(
              propertyId: state.pathParameters['propertyId']!,
              title: extra?['title'] as String? ?? 'Gallery',
            ),
            transitionsBuilder:
                (context, animation, secondaryAnimation, child) {
              return FadeTransition(
                opacity: CurvedAnimation(
                  parent: animation,
                  curve: Curves.easeOut,
                ),
                child: child,
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
            transitionsBuilder:
                (context, animation, secondaryAnimation, child) {
                  return FadeTransition(
                    opacity: CurvedAnimation(
                      parent: animation,
                      curve: Curves.easeOut,
                    ),
                    child: SlideTransition(
                      position:
                          Tween<Offset>(
                            begin: const Offset(0, 0.05),
                            end: Offset.zero,
                          ).animate(
                            CurvedAnimation(
                              parent: animation,
                              curve: Curves.easeOutCubic,
                            ),
                          ),
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
            transitionsBuilder:
                (context, animation, secondaryAnimation, child) {
                  return FadeTransition(
                    opacity: CurvedAnimation(
                      parent: animation,
                      curve: Curves.easeOut,
                    ),
                    child: SlideTransition(
                      position:
                          Tween<Offset>(
                            begin: const Offset(0, 0.05),
                            end: Offset.zero,
                          ).animate(
                            CurvedAnimation(
                              parent: animation,
                              curve: Curves.easeOutCubic,
                            ),
                          ),
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
            child: PlotDetailPage(plotId: state.pathParameters['plotId']!),
            transitionsBuilder:
                (context, animation, secondaryAnimation, child) {
                  return FadeTransition(
                    opacity: CurvedAnimation(
                      parent: animation,
                      curve: Curves.easeOut,
                    ),
                    child: SlideTransition(
                      position:
                          Tween<Offset>(
                            begin: const Offset(0, 0.05),
                            end: Offset.zero,
                          ).animate(
                            CurvedAnimation(
                              parent: animation,
                              curve: Curves.easeOutCubic,
                            ),
                          ),
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
            child: BookingPage(plotId: state.pathParameters['plotId']!),
            transitionsBuilder:
                (context, animation, secondaryAnimation, child) {
                  return SlideTransition(
                    position:
                        Tween<Offset>(
                          begin: const Offset(0, 0.08),
                          end: Offset.zero,
                        ).animate(
                          CurvedAnimation(
                            parent: animation,
                            curve: Curves.easeOutCubic,
                          ),
                        ),
                    child: FadeTransition(
                      opacity: CurvedAnimation(
                        parent: animation,
                        curve: Curves.easeOut,
                      ),
                      child: child,
                    ),
                  );
                },
          );
        },
      ),
      GoRoute(
        path: '/my-bookings',
        pageBuilder: (context, state) => const CustomTransitionPage<void>(
          child: MyBookingsPage(),
          transitionsBuilder: _slideTransition,
        ),
      ),
      GoRoute(
        path: '/customer-bookings',
        pageBuilder: (context, state) => const CustomTransitionPage<void>(
          child: CustomerBookingsPage(),
          transitionsBuilder: _slideTransition,
        ),
      ),
      GoRoute(
        path: '/emi-schedule',
        pageBuilder: (context, state) => const CustomTransitionPage<void>(
          child: EmiSchedulePage(),
          transitionsBuilder: _slideTransition,
        ),
      ),
      GoRoute(
        path: '/favorites',
        pageBuilder: (context, state) => const CustomTransitionPage<void>(
          child: FavoritesPage(),
          transitionsBuilder: _slideTransition,
        ),
      ),
      GoRoute(
        path: '/documents',
        pageBuilder: (context, state) => const CustomTransitionPage<void>(
          child: DocumentsPage(),
          transitionsBuilder: _slideTransition,
        ),
      ),
      GoRoute(
        path: '/kyc-verification',
        pageBuilder: (context, state) => const CustomTransitionPage<void>(
          child: KYCVerificationPage(),
          transitionsBuilder: _slideTransition,
        ),
      ),
      GoRoute(
        path: '/kyc-status',
        pageBuilder: (context, state) => const CustomTransitionPage<void>(
          child: KYCStatusPage(),
          transitionsBuilder: _slideTransition,
        ),
      ),
      GoRoute(
        path: '/post-property',
        pageBuilder: (context, state) => const CustomTransitionPage<void>(
          child: PostPropertyPage(),
          transitionsBuilder: _slideTransition,
        ),
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
        path: '/site-visits',
        builder: (context, state) => const MySiteVisitsPage(),
      ),
      GoRoute(path: '/map', builder: (context, state) => const MapViewPage()),
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
      GoRoute(path: '/faq', builder: (context, state) => const FaqPage()),
      GoRoute(
        path: '/testimonials',
        builder: (context, state) => const TestimonialsPage(),
      ),
      GoRoute(path: '/about', builder: (context, state) => const AboutPage()),
      GoRoute(path: '/blog', builder: (context, state) => const BlogPage()),
      GoRoute(
        path: '/blog/:slug',
        builder: (context, state) =>
            BlogDetailPage(slug: state.pathParameters['slug']!),
      ),
      GoRoute(
        path: '/careers',
        builder: (context, state) => const CareersPage(),
      ),
      GoRoute(
        path: '/careers/:jobId',
        builder: (context, state) =>
            CareerDetailPage(jobId: state.pathParameters['jobId']!),
      ),
      GoRoute(
        path: '/payment-history',
        builder: (context, state) => const PaymentHistoryPage(),
      ),

      // ─── In-App Messaging Routes ───
      GoRoute(path: '/inbox', builder: (context, state) => const InboxPage()),
      GoRoute(
        path: '/inbox/chat/:userId',
        builder: (context, state) {
          final userId = int.parse(state.pathParameters['userId']!);
          final extra = state.extra as Map<String, dynamic>?;
          return ChatDetailPage(
            otherUserId: userId,
            otherUserName: extra?['userName'] as String? ?? 'User',
            otherUserRole: extra?['userRole'] as String? ?? '',
          );
        },
      ),

      // ─── Legal Document Routes ───
      GoRoute(
        path: '/legal-documents',
        builder: (context, state) => const LegalDocumentsPage(),
      ),
      GoRoute(
        path: '/legal-documents/:id',
        builder: (context, state) => LegalDocumentDetailPage(
          documentId: int.parse(state.pathParameters['id']!),
        ),
      ),
      GoRoute(
        path: '/legal-documents/:id/preview',
        builder: (context, state) => LegalDocumentPreviewPage(
          documentId: int.parse(state.pathParameters['id']!),
        ),
      ),

      // ─── Document E-Sign Routes ───
      GoRoute(
        path: '/document-esign',
        builder: (context, state) => const DocumentEsignPage(),
      ),
      GoRoute(
        path: '/document-esign/:id',
        builder: (context, state) => DocumentEsignDetailPage(
          documentId: int.parse(state.pathParameters['id']!),
        ),
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
        builder: (context, state) => const ComparisonPage(),
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
      GoRoute(
        path: '/capital-gains-calculator',
        builder: (context, state) => const CapitalGainsPage(),
      ),
      GoRoute(
        path: '/construction-cost-estimator',
        builder: (context, state) => const ConstructionCostPage(),
      ),
      GoRoute(
        path: '/rental-yield-calculator',
        builder: (context, state) => const RentalYieldPage(),
      ),
      GoRoute(
        path: '/rent-vs-buy',
        builder: (context, state) => const RentVsBuyPage(),
      ),
      GoRoute(
        path: '/property-tax-calculator',
        builder: (context, state) => const PropertyTaxPage(),
      ),
      GoRoute(
        path: '/sip-vs-realestate',
        builder: (context, state) => const SipVsRealestatePage(),
      ),
      GoRoute(
        path: '/gst-calculator',
        builder: (context, state) => const GstCalculatorPage(),
      ),
      GoRoute(
        path: '/rera-lookup',
        builder: (context, state) => const ReraLookupPage(),
      ),
      GoRoute(
        path: '/title-protection',
        builder: (context, state) => const TitleProtectionPage(),
      ),
      GoRoute(
        path: '/property-verification',
        builder: (context, state) => const PropertyVerificationPage(),
      ),
      GoRoute(
        path: '/investment-calculator',
        builder: (context, state) => const InvestmentCalculatorPage(),
      ),
      GoRoute(
        path: '/neighborhood',
        builder: (context, state) => const NeighborhoodPage(),
      ),
      GoRoute(
        path: '/virtual-tour',
        builder: (context, state) => const VirtualTourPage(),
      ),
      GoRoute(path: '/news', builder: (context, state) => const NewsPage()),

      // ─── AI ───
      GoRoute(
        path: '/ai-agent',
        builder: (context, state) => const AIAgentDashboardPage(),
      ),
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
      GoRoute(path: '/leads', builder: (context, state) => const LeadsPage()),
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

      // ─── Agent Portal Extended Routes (Session 78) ───
      GoRoute(
        path: '/agent/analytics',
        builder: (context, state) => const AgentAnalyticsPage(),
      ),
      GoRoute(
        path: '/agent/bookings',
        builder: (context, state) => const AgentBookingsPage(),
      ),
      GoRoute(
        path: '/agent/bookings/:bookingId',
        builder: (context, state) => const AgentBookingsPage(),
      ),
      GoRoute(
        path: '/agent/documents',
        builder: (context, state) => const AgentDocumentsPage(),
      ),
      GoRoute(
        path: '/agent/follow-ups',
        builder: (context, state) => const AgentFollowUpsPage(),
      ),
      GoRoute(
        path: '/agent/properties',
        builder: (context, state) => const AgentPropertiesPage(),
      ),
      GoRoute(
        path: '/agent/site-visits',
        builder: (context, state) => const AgentSiteVisitsPage(),
      ),
      GoRoute(
        path: '/agent/my-team',
        builder: (context, state) => const AgentMyTeamPage(),
      ),
      GoRoute(
        path: '/agent/rank-progress',
        builder: (context, state) => const AgentRankProgressPage(),
      ),

      // Lead creation (full-page form for agents/associates)
      GoRoute(
        path: '/agent/leads/create',
        builder: (context, state) => const LeadCreatePage(),
      ),
      GoRoute(
        path: '/leads/add',
        builder: (context, state) => const LeadCreatePage(),
      ),

      // ─── Orphaned Pages (wired Session 52) ───
      GoRoute(
        path: '/sell-property',
        builder: (context, state) => const SellPropertyPage(),
      ),
      GoRoute(
        path: '/voice-to-lead',
        builder: (context, state) => const VoiceToLeadPage(),
      ),
      GoRoute(
        path: '/document-locker',
        builder: (context, state) => const DocumentLockerPage(),
      ),
      GoRoute(
        path: '/incentive-dashboard',
        builder: (context, state) => const IncentiveDashboardPage(),
      ),
      GoRoute(
        path: '/receipt-view',
        builder: (context, state) {
          final extra = state.extra as Map<String, dynamic>?;
          return ReceiptViewPage(
            receiptData: extra?['receiptData'] as Map<String, dynamic>?,
            receiptType: extra?['receiptType'] as String?,
          );
        },
      ),
      GoRoute(
        path: '/site-visit-tracker',
        builder: (context, state) => const SiteVisitPage(),
      ),
      GoRoute(
        path: '/telecaller/dashboard',
        builder: (context, state) => const TelecallerDashboardPage(),
      ),
      GoRoute(
        path: '/auto-dialer',
        builder: (context, state) => const AutoDialerDashboardPage(),
      ),
      GoRoute(
        path: '/auto-dialer/templates',
        builder: (context, state) => const TemplatesPage(),
      ),
      GoRoute(
        path: '/auto-dialer/bulk',
        builder: (context, state) => const BulkOperationsPage(),
      ),
      GoRoute(
        path: '/auto-dialer/voice',
        builder: (context, state) => VoiceCallPage(
          leadId: state.uri.queryParameters['leadId'] != null
              ? int.tryParse(state.uri.queryParameters['leadId']!)
              : null,
        ),
      ),

      // ─── Employee Shell ───
      ShellRoute(
        builder: (context, state, child) => EmployeeShell(child: child),
        routes: [
          GoRoute(
            path: '/employee/dashboard',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: EmployeeDashboardPage()),
          ),
          GoRoute(
            path: '/employee/tasks',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: EmployeeTasksPage()),
          ),
          GoRoute(
            path: '/employee/check-in',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: CheckInPage()),
          ),
          GoRoute(
            path: '/employee/profile',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: EmployeeProfilePage()),
          ),
          GoRoute(
            path: '/employee/crm',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: EmployeeCRMPage()),
          ),
        ],
      ),

      // ─── Admin Shell (wraps all /admin sub-routes) ───
      ShellRoute(
        builder: (context, state, child) => AdminShell(child: child),
        routes: [
          GoRoute(
            path: '/admin',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: AdminDashboardPage()),
          ),
          GoRoute(
            path: '/admin/crm',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: CRMPage()),
          ),
          GoRoute(
            path: '/admin/bookings',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: BookingApprovalsPage()),
          ),
          GoRoute(
            path: '/admin/customers',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: UserManagementPage()),
          ),
          GoRoute(
            path: '/admin/reports',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: ReportsPage()),
          ),
          GoRoute(
            path: '/admin/colonies',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: ColonyManagementPage()),
          ),
          GoRoute(
            path: '/admin/plots',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: PlotManagementPage()),
          ),
          GoRoute(
            path: '/admin/employees',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: EmployeeManagementPage()),
          ),
          GoRoute(
            path: '/admin/commissions',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: CommissionApprovalsPage()),
          ),
          GoRoute(
            path: '/admin/accounts',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: AccountsPage()),
          ),
          GoRoute(
            path: '/admin/analytics',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: AnalyticsDashboardPage()),
          ),
          GoRoute(
            path: '/admin/marketing',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: CampaignManagementPage()),
          ),
          GoRoute(
            path: '/admin/bulk-marketing',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: BulkMarketingPage()),
          ),
          GoRoute(
            path: '/admin/tools',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: AdminToolsPage()),
          ),
          GoRoute(
            path: '/admin/dev-tools',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: DevToolsPage()),
          ),
          GoRoute(
            path: '/admin/payouts',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: AccountsPage()),
          ),
          GoRoute(
            path: '/admin/invoices',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: AccountsPage()),
          ),
          GoRoute(
            path: '/admin/ledger',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: AccountsPage()),
          ),
          GoRoute(
            path: '/admin/emi',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: AccountsPage()),
          ),
          GoRoute(
            path: '/admin/leads',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: CRMPage()),
          ),
          GoRoute(
            path: '/admin/settings',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: SettingsPage()),
          ),
          GoRoute(
            path: '/admin/profile',
            pageBuilder: (context, state) =>
                const NoTransitionPage(child: ProfilePage()),
          ),
        ],
      ),
    ],
    errorBuilder: (context, state) => ErrorPage(error: state.error),
  );

  return router;
}

String defaultRouteForRole(User? user) {
  if (user == null) return '/home';
  if (user.isAdmin) return '/admin';
  if (user.isAgent) return '/agent/dashboard';
  if (user.isTelecaller) return '/employee/dashboard';
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

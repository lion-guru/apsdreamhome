import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';

class AppConstants {
  // API Configuration
  // Uses --dart-define=API_BASE_URL=... at build time, or platform detection
  static String baseUrl = const String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: '',
  );

  static void initBaseUrl() {
    if (baseUrl.isNotEmpty) return; // --dart-define was set

    // Platform detection fallback
    if (kIsWeb) {
      baseUrl = 'http://localhost/apsdreamhome';
    } else {
      // Mobile: use ngrok URL (works from any network)
      baseUrl =
          'https://unforced-willena-seclusively.ngrok-free.dev/apsdreamhome';
    }
  }

  static const String apiVersion = 'api/v2/mobile';

  // Endpoints
  static const String loginEndpoint = '/auth/login';
  static const String googleLoginEndpoint = '/auth/google-login';
  static const String airLoginEndpoint = '/auth/air-login';
  static const String airLoginVerifyEndpoint = '/auth/air-login/verify';
  static const String propertiesEndpoint = '/properties';
  static const String updatesEndpoint = '/updates';
  static const String leadsEndpoint = '/leads';
  static const String commissionsEndpoint = '/mlm/payouts';
  static const String mlmSummaryEndpoint = '/mlm/summary';
  static const String incentivesEndpoint = '/mlm/incentives';
  static const String uploadDocumentEndpoint = '/upload-document';
  static const String profileEndpoint = '/user/profile';
  static const String syncEndpoint = '/sync';
  static const String parseLeadEndpoint = '/ai/parse-lead';
  static const String genealogyEndpoint = '/mlm/genealogy';
  static const String businessBreakdownEndpoint = '/mlm/business-breakdown';
  static const String requestPayoutEndpoint = '/mlm/request-payout';
  static const String notificationsRegisterEndpoint = '/notifications/register';
  static const String coloniesEndpoint = '/colonies';
  static const String colonyHealthEndpoint = '/colonies/health/all';
  static const String plotsEndpoint = '/plots';
  static const String crmPrefix = '/crm';

// Referral
  static const String referralTrackEndpoint = '/referral/track';
  static const String referralDashboardEndpoint = '/referral/dashboard';
  static const String referralListEndpoint = '/referral/list';
  static const String referralStatsEndpoint = '/referral/stats';

  // Agent Portal
  static const String agentMyTeamEndpoint = '/my-team';
  static const String agentRankProgressEndpoint = '/rank-progress';
  static const String agentLeadsEndpoint = '/agent/leads';
  static const String agentCommissionsEndpoint = '/agent/commissions';
  static const String agentPayoutsEndpoint = '/agent/payouts';
  static const String agentPropertyListingsEndpoint = '/agent/properties';
  static const String agentBookingsEndpoint = '/agent/bookings';
  static const String agentDocumentsEndpoint = '/agent/documents';
  static const String agentSiteVisitsEndpoint = '/agent/site-visits';
  static const String agentFollowUpsEndpoint = '/agent/follow-ups';
  static const String agentAnalyticsEndpoint = '/agent/analytics';

// Admin Mobile
  static const String adminDashboardStatsEndpoint = '/admin/dashboard-stats';
  static const String adminSalesTrendEndpoint = '/admin/sales-trend';
  static const String adminTopAssociatesEndpoint = '/admin/top-associates';
  static const String adminColonyPerformanceEndpoint = '/admin/colony-performance';
  static const String adminLeadConversionEndpoint = '/admin/lead-conversion';
  static const String adminDailySalesEndpoint = '/admin/daily-sales';

  // AI Agent
  static const String aiAgentAnalyzePropertyEndpoint = '/ai-agent/analyze-property';
  static const String aiAgentDecideEndpoint = '/ai-agent/decide';
  static const String aiAgentFeedbackEndpoint = '/ai-agent/feedback';
  static const String aiAgentStatsEndpoint = '/ai-agent/stats';
  static const String aiAgentAnalyticsEndpoint = '/ai-agent/analytics';

  // Auth
  static const String authChangePasswordEndpoint = '/auth/change-password';
  static const String authCheckUserEndpoint = '/auth/check-user';
  static const String authFirebaseLoginEndpoint = '/auth/firebase-login';
  static const String authForgotPasswordEndpoint = '/auth/forgot-password';
  static const String authLogoutEndpoint = '/auth/logout';
  static const String authRefreshEndpoint = '/auth/refresh';
  static const String authRegisterEndpoint = '/auth/register';
  static const String authResendOtpEndpoint = '/auth/resend-otp';
  static const String authResetPasswordEndpoint = '/auth/reset-password';
  static const String authVerifyOtpEndpoint = '/auth/verify-otp';

  // Auto-Dialer
  static const String autoDialerScheduleEndpoint = '/auto-dialer/schedule';
  static const String autoDialerBulkScheduleEndpoint =
      '/auto-dialer/bulk-schedule';
  static const String autoDialerCancelEndpoint = '/auto-dialer/cancel';
  static const String autoDialerRescheduleEndpoint = '/auto-dialer/reschedule';
  static const String autoDialerStatsEndpoint = '/auto-dialer/stats';
  static const String autoDialerHistoryEndpoint = '/auto-dialer/history';
  static const String autoDialerProcessEndpoint = '/auto-dialer/process';
  static const String autoDialerSendSmsEndpoint = '/auto-dialer/send-sms';
  static const String autoDialerSendWhatsAppEndpoint =
      '/auto-dialer/send-whatsapp';
  static const String autoDialerBulkSmsEndpoint = '/auto-dialer/bulk-sms';
  static const String autoDialerBulkWhatsAppEndpoint =
      '/auto-dialer/bulk-whatsapp';
  static const String voiceChatEndpoint = '/voice-chat';
  static const String aiScheduleEndpoint = '/auto-dialer/ai-schedule';

  // Voice Agent
  static const String voiceStartCallEndpoint = '/voice/start-call';
  static const String voiceProcessResponseEndpoint = '/voice/process-response';
  static const String voiceSessionEndpoint = '/voice/session';
  static const String voiceEndCallEndpoint = '/voice/end-call';
  static const String voiceScheduleEndpoint = '/voice/schedule';
  static const String voiceStatsEndpoint = '/voice/stats';
  static const String voiceCallHistoryEndpoint = '/voice/call-history';

  // Voice Assistant
  static const String voiceAssistantQueryEndpoint = '/voice-assistant/query';

  // Telecaller
  static const String telecallerDashboardEndpoint = '/telecaller/dashboard';
  static const String telecallerReportEndpoint = '/telecaller/report';

  // Admin
  static const String adminEmiCollectionEndpoint = '/admin/emi-collection';

  // CRM
  static const String crmAnalyticsEndpoint = '/crm/analytics';
  static const String crmTeamPerformanceEndpoint = '/crm/team-performance';

  // Referral

  // Property Marketplace
  static const String propertyInquiryEndpoint = '/properties/inquiry';
  static const String propertyMessageEndpoint = '/properties/message';
  static const String propertyMessagesEndpoint = '/properties';
  static const String myListingsEndpoint = '/my-listings';
  static const String listingPackagesEndpoint = '/listing-packages';
  static const String propertyBoostEndpoint = '/properties/boost';
  static const String propertySimilarEndpoint = '/properties/similar';
  static const String propertyFavoritesEndpoint = '/properties/favorite';
  static const String colonyPropertiesEndpoint = '/colonies/properties';

  // Listing Upgrade Payment
  static const String listingCreateOrderEndpoint = '/listing/create-order';
  static const String listingVerifyPaymentEndpoint = '/listing/verify-payment';
  static const String listingActivateFreeEndpoint = '/listing/activate-free';
  static const String listingUpgradeEndpoint = '/properties/boost';

  // Favorites
  static const String favoritesEndpoint = '/user/favorites';
  static const String favoritesCheckEndpoint = '/user/favorites/check';
  static const String favoritesStatsEndpoint = '/user/favorites/stats';

  // Documents
  static const String documentsEndpoint = '/user/documents';

  // Notifications
  static const String notificationsEndpoint = '/user/notifications';
  static const String notificationRegisterEndpoint = '/notifications/register';

  // Chat
  static const String chatStartEndpoint = '/api/v2/mobile/chat/start';
  static const String chatSendEndpoint = '/api/v2/mobile/chat/send';
  static const String chatPollEndpoint = '/api/v2/mobile/chat/poll';
  static const String chatWidgetEndpoint = '/api/v2/mobile/chat/widget';
  static const String chatHistoryEndpoint = '/api/v2/mobile/chat/history';

  // In-App Messaging
  static const String conversationsEndpoint = '/messages/conversations';
  static const String messagesEndpoint = '/messages';
  static const String sendMessageEndpoint = '/messages/send';
  static const String markReadEndpoint = '/messages/read';
  static const String unreadCountEndpoint = '/messages/unread/count';

  // Database
  static const String databaseName = 'aps_dream_home.db';
  static const int databaseVersion = 2;

  // Tables
  static const String usersTable = 'users';
  static const String propertiesTable = 'properties';
  static const String leadsTable = 'leads';
  static const String commissionsTable = 'commissions';
  static const String incentivesTable = 'incentives';
  static const String syncQueueTable = 'sync_queue';

  // Storage Keys
  static const String tokenKey = 'auth_token';
  static const String userIdKey = 'user_id';
  static const String userProfileKey = 'user_profile';
  static const String lastSyncTimeKey = 'last_sync_time';
  static const String offlineBoxName = 'offline_data';

  // Table Names (for sync)
  static const String usersCollection = 'users';
  static const String leadsCollection = 'leads';
  static const String propertiesCollection = 'properties';
  static const String coloniesCollection = 'colonies';
  static const String plotsCollection = 'plots';
  static const String commissionsCollection = 'commissions';
  static const String payoutsCollection = 'payouts';

  // User Roles
  static const String roleCustomer = 'customer';
  static const String roleAssociate = 'associate';

  // Currency
  static const String currencySymbol = '₹';
  static const String roleAdmin = 'admin';

  // Lead Status
  static const String leadStatusNew = 'new';
  static const String leadStatusContacted = 'contacted';
  static const String leadStatusQualified = 'qualified';
  static const String leadStatusConverted = 'converted';
  static const String leadStatusLost = 'lost';

  // Rank Constants
  static const String rankAssociate = 'Associate';
  static const String rankSrAssociate = 'Sr. Associate';
  static const String rankBDM = 'BDM';
  static const String rankSrBDM = 'Sr. BDM';
  static const String rankVicePresident = 'Vice President';
  static const String rankPresident = 'President';
  static const String rankSiteManager = 'Site Manager';

  // Sync Settings
  static const Duration syncInterval = Duration(minutes: 5);
  static const int maxRetryAttempts = 3;

  // UI Constants
  static const double defaultPadding = 16.0;
  static const double cardRadius = 12.0;
  static const double buttonRadius = 8.0;

  // Colors
  static const Color primaryColor = Color(0xFF1A237E); // Deep Royal Blue
  static const Color accentColor = Color(0xFFFFD700); // Gold
  static const Color successColor = Color(0xFF4CAF50);
  static const Color errorColor = Color(0xFFF44336);
  static const Color warningColor = Color(0xFFFF9800);

  // MLM Business Rules
  static const Map<String, double> commissionRates = {
    'Associate': 6.0,
    'Sr. Associate': 8.0,
    'BDM': 10.0,
    'Sr. BDM': 12.0,
    'Vice President': 15.0,
    'President': 18.0,
    'Site Manager': 20.0,
  };

  static const Map<String, double> targets = {
    'Associate': 1000000.0,
    'Sr. Associate': 3500000.0,
    'BDM': 7000000.0,
    'Sr. BDM': 15000000.0,
    'Vice President': 30000000.0,
    'President': 50000000.0,
    'Site Manager': 100000000.0,
  };

  // MLM Commission Status
  static const String commissionStatusPending = 'pending';
  static const String commissionStatusPaid = 'paid';
  static const String commissionStatusHold = 'hold';

  // Payout Status
  static const String payoutStatusRequested = 'requested';
  static const String payoutStatusProcessing = 'processing';
  static const String payoutStatusCompleted = 'completed';
  static const String payoutStatusRejected = 'rejected';

  // MLM Config
  static const int mlmMaxLevels = 10;

  // Rank Order (for hierarchy)
  static const List<String> rankOrder = [
    'Associate',
    'Sr. Associate',
    'BDM',
    'Sr. BDM',
    'Vice President',
    'President',
    'Site Manager',
  ];

  // Rank Commission Percentages (alias for commissionRates)
  static Map<String, double> get rankCommissionPercentages => commissionRates;

  // Rank Targets (alias for targets)
  static Map<String, double> get rankTargets => targets;

  // App Info
  static const String appName = 'APS Dream Home';
  static const String supportPhone = '7007444842';
  static const String version = '1.2.0';

  // Validation Constants
  static const int minPasswordLength = 6;
}

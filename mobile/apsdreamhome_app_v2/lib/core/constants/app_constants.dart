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
  
  // Wallet Activation Packages
  static const String walletActivationPackagesEndpoint = '/wallet/activation/packages';
  static const String walletActivationMyWalletEndpoint = '/wallet/activation/my-wallet';
  static const String walletActivationPurchaseEndpoint = '/wallet/activation/purchase';
  static const String walletActivationVerifyPaymentEndpoint = '/wallet/activation/verify-payment';
  static const String walletBalanceEndpoint = '/wallet/balance';
  
  // Referral Earnings
  static const String referralEarningsEndpoint = '/referral/earnings';
  static const String referralLeaderboardEndpoint = '/referral/leaderboard';
  static const String referralShareUrlEndpoint = '/referral/share-url';
  
  // Wallet Activation
  static const String walletActivationPackagesEndpointV2 = '/api/v2/mobile/wallet/activation/packages';
  static const String walletActivationMyWalletEndpointV2 = '/api/v2/mobile/wallet/activation/my-wallet';
  static const String walletActivationPurchaseEndpointV2 = '/api/v2/mobile/wallet/activation/purchase';
  static const String walletActivationVerifyPaymentEndpointV2 = '/api/v2/mobile/wallet/activation/verify-payment';
  static const String walletBalanceEndpointV2 = '/api/v2/mobile/wallet/balance';
  
  // Referral Earnings V2
  static const String referralEarningsEndpointV2 = '/api/v2/mobile/referral/earnings';
  static const String referralLeaderboardEndpointV2 = '/api/v2/mobile/referral/leaderboard';
  static const String referralShareUrlEndpointV2 = '/api/v2/mobile/referral/share-url';

  // Associate / Agent Offers V2
  static const String associateOffersEndpointV2 = '/api/v2/mobile/associate/offers';
  static const String agentOffersEndpointV2 = '/api/v2/mobile/agent/offers';
  static const String agentSalaryEndpointV2 = '/api/v2/mobile/agent/salary';
  static const String promotionalOffersEndpointV2 = '/api/v2/mobile/promotional-offers';

  // Referral

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

  // Associate Portal
  static const String associateBookingsEndpoint = '/associate/bookings';
  static const String associateEmiTrackerEndpoint = '/associate/emi-tracker';

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

  // Calls
  static const String callLogEndpoint = '/calls/log';
  static const String callStatsEndpoint = '/calls/stats';

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

  // Campaign Templates
  static const String campaignTemplatesEndpoint = '/api/v2/mobile/campaign-templates';
  static const String campaignTemplateDetailEndpoint = '/api/v2/mobile/campaign-templates/';

  // Voice Uploads
  static const String voiceUploadsEndpoint = '/api/v2/mobile/voice-uploads';
  static const String voiceUploadDetailEndpoint = '/api/v2/mobile/voice-uploads/';

  // App Feedback
  static const String appFeedbackEndpoint = '/api/v2/mobile/app-feedback';
  static const String appFeedbackDetailEndpoint = '/api/v2/mobile/app-feedback/';

  // Search History
  static const String searchHistoryEndpoint = '/api/v2/mobile/search-history';

  // Registry Timeline (customer registry journey + possession certificate)
  static const String registryTimelineEndpoint = '/api/v2/mobile/registry/timeline/';
  static const String possessionCertificateEndpoint = '/api/v2/mobile/registry/possession-certificate/';

  // Payout Batches (staff bulk payout list/detail/CSV export)
  static const String payoutBatchesEndpoint = '/api/v2/mobile/payout-batches';
  static const String payoutBatchDetailEndpoint = '/api/v2/mobile/payout-batches/';
  static const String payoutBatchExportSuffix = '/export-bank-csv';

  // Legal Kit (download legal kit ZIP for bookings)
  static const String legalKitEndpoint = '/api/v2/mobile/legal-kit/';
  static const String legalKitAdminSuffix = '/admin';
  static const String legalKitSalesSuffix = '/sales';

  // Commission Recalculation (staff)
  static const String commissionRecalculationsEndpoint = '/api/v2/mobile/commission-recalculations';
  static const String commissionRecalculationDetailEndpoint = '/api/v2/mobile/commission-recalculations/';
  static const String commissionRecalculationRequestEndpoint = '/api/v2/mobile/commission-recalculations/request';
  static const String commissionRecalculationBulkRequestEndpoint = '/api/v2/mobile/commission-recalculations/bulk-request';

  // Site Visit Dispatch (staff assign/outcome/send-pin)
  static const String siteVisitDispatchEndpoint = '/api/v2/mobile/site-visits/';
  static const String siteVisitAssignSuffix = '/assign';
  static const String siteVisitOutcomeSuffix = '/outcome';
  static const String siteVisitSendPinSuffix = '/send-pin';

  // Demand Letters (Phase 6 — customer + staff)
  static const String demandLettersEndpoint = '/demand-letters';
  static const String demandLetterDetailSuffix = '/';

  // Construction Progress & Material Inventory (Phase 6)
  static const String constructionColoniesEndpoint = '/construction/colonies';
  static const String constructionMilestonesSuffix = '/milestones';
  static const String constructionMaterialsEndpoint = '/construction/materials';
  static const String constructionMaterialUsageEndpoint = '/construction/materials/usage';

  // Colony Pipeline Mobile
  static const String colonyPipelineDashboardEndpoint = '/colony-pipeline/dashboard';
  static const String colonyPipelineDetailEndpoint = '/colony-pipeline/detail';
  static const String colonyPipelineLayoutEndpoint = '/colony-pipeline/layout';
  static const String colonyPipelineGeneratePlotsEndpoint = '/colony-pipeline/generate-plots';
  static const String colonyPipelineSaveLayoutEndpoint = '/colony-pipeline/save-layout';
  static const String colonyPipelineDeletePlotsEndpoint = '/colony-pipeline/delete-plots';
  static const String colonyPipelinePricingEndpoint = '/colony-pipeline/pricing';
  static const String colonyPipelineCalculatePricingEndpoint = '/colony-pipeline/calculate-pricing';
  static const String colonyPipelineApplyPricingEndpoint = '/colony-pipeline/apply-pricing';
  static const String colonyPipelineCostsEndpoint = '/colony-pipeline/costs';
  static const String colonyPipelineStoreCostEndpoint = '/colony-pipeline/costs/store';
  static const String colonyPipelinePlotsEndpoint = '/colony-pipeline/plots';
  static const String colonyPipelinePlotsStatsEndpoint = '/colony-pipeline/plots/stats';
  static const String colonyPipelineMapEndpoint = '/colony-pipeline/map';
  static const String colonyPipelineMapGeoJsonEndpoint = '/colony-pipeline/map/geojson';
  static const String colonyPipelinePricingPlanSaveEndpoint = '/colony-pipeline/pricing-plan/save';
  static const String colonyPipelinePricingPlanActivateEndpoint = '/colony-pipeline/pricing-plan/activate';
  static const String colonyPipelinePricingPlanApplyEndpoint = '/colony-pipeline/pricing-plan/apply';
  static const String colonyPipelinePricingPlanHistoryEndpoint = '/colony-pipeline/pricing-plan/history';
  static const String colonyPipelinePricingPlanApplicationsEndpoint = '/colony-pipeline/pricing-plan/applications';
  static const String colonyPipelineCostsStoreEndpoint = '/colony-pipeline/costs/store';
  static const String colonyPipelinePlotMapEndpoint = '/colony-pipeline/map';
  static const String colonyPipelinePlotMapGeoJsonEndpoint = '/colony-pipeline/map/geojson';

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

  // App Info — keep in sync with pubspec.yaml:4 version: 1.2.2+1
  static const String appName = 'APS Dream Home';
  static const String supportPhone = '7007444842';
  static const String version = '1.2.2+1';

  // Validation Constants
  static const int minPasswordLength = 6;

  // ============================================================
  // SELF-SERVICE PORTAL (Employee)
  // ============================================================
  // Tax Regime
  static const String taxRegimeEndpoint = '/api/v2/mobile/self-service/tax-regime';

  // Investment Declaration
  static const String investmentDeclarationEndpoint = '/api/v2/mobile/self-service/investment-declaration';
  static const String uploadInvestmentProofEndpoint = '/api/v2/mobile/self-service/investment-declaration/upload-proof';

  // Form 16
  static const String form16Endpoint = '/api/v2/mobile/self-service/form16';
  static const String form16GenerateEndpoint = '/api/v2/mobile/self-service/form16/generate';
  static const String form16DownloadEndpoint = '/api/v2/mobile/self-service/form16/download/';

  // Payslips
  static const String payslipsEndpoint = '/api/v2/mobile/self-service/payslips';
  static const String payslipDownloadEndpoint = '/api/v2/mobile/self-service/payslips/';
  static const String payslipDownloadSuffix = '/download';

  // Leave
  static const String leaveBalancesEndpoint = '/api/v2/mobile/self-service/leave';
  static const String leaveTypesEndpoint = '/api/v2/mobile/self-service/leave-types';
  static const String leaveApplyEndpoint = '/api/v2/mobile/self-service/leave/apply';
  static const String leaveHistoryEndpoint = '/api/v2/mobile/self-service/leave/history';

  // Reimbursement
  static const String reimbursementsEndpoint = '/api/v2/mobile/self-service/reimbursement';

  // Profile (self-service)
  static const String selfServiceProfileEndpoint = '/api/v2/mobile/self-service/profile';
  static const String changePasswordEndpoint = '/api/v2/mobile/self-service/profile/change-password';
  static const String selfServiceDashboardEndpoint = '/api/v2/mobile/self-service/dashboard';

  // Attendance
  static const String attendanceEndpoint = '/api/v2/mobile/self-service/attendance';
  static const String attendanceStatsEndpoint = '/api/v2/mobile/self-service/attendance/stats';

  // Gratuity
  static const String gratuityCalculatorEndpoint = '/api/v2/mobile/gratuity/calculator';
  static const String gratuityReportEndpoint = '/api/v2/mobile/gratuity/report';
  static const String gratuityDetailEndpoint = '/api/v2/mobile/gratuity/detail/';

  // F&F Settlement
  static const String fnfCalculatorEndpoint = '/api/v2/mobile/fnf/calculator';
  static const String fnfProcessEndpoint = '/api/v2/mobile/fnf/process';

  // Shift Roster & OT
  static const String shiftTypesEndpoint = '/api/v2/mobile/shift-roster/shift-types';
  static const String rosterEndpoint = '/api/v2/mobile/shift-roster/roster';
  static const String assignShiftEndpoint = '/api/v2/mobile/shift-roster/assign-shift';
  static const String overtimeRequestsEndpoint = '/api/v2/mobile/shift-roster/overtime-requests';
  static const String overtimeRequestEndpoint = '/api/v2/mobile/shift-roster/overtime-request';
  static const String processOvertimeEndpoint = '/api/v2/mobile/shift-roster/overtime-requests/';
  static const String overtimeReportsEndpoint = '/api/v2/mobile/shift-roster/overtime-reports';
  static const String shiftCoverageEndpoint = '/api/v2/mobile/shift-roster/shift-coverage';

  // Investment (Customer)
  static const String investmentPlansEndpoint = '/investment-plans';
  static const String userInvestmentsEndpoint = '/user/investments';
  static const String investmentCreateEndpoint = '/user/invest';
  static const String investmentCancelEndpoint = '/user/investment/cancel';
}

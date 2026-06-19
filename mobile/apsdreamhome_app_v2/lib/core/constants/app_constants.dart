import 'package:flutter/material.dart';

class AppConstants {
  // API Configuration
  // For Android Emulator: use 10.0.2.2 (points to host localhost)
  // For Physical Device via ngrok: use ngrok URL
  // For Physical Device on same WiFi: use PC IP
  static const String baseUrl = 'http://10.0.2.2/apsdreamhome';

  static const String apiVersion = 'api/v2/mobile';

  // Endpoints
  static const String loginEndpoint = '/auth/login';
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
  static const String plotsEndpoint = '/plots';

  // V2 Mobile Attendance
  static const String attendancePunchInEndpoint = '/attendance/punch-in';
  static const String attendancePunchOutEndpoint = '/attendance/punch-out';
  static const String attendanceStatusEndpoint = '/attendance/status';

  // Referral
  static const String referralTrackEndpoint = '/referral/track';

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
  static const String version = '1.0.0';

  // Demo Mode
  static const bool demoMode = true;

  // Validation Constants
  static const int minPasswordLength = 6;
}

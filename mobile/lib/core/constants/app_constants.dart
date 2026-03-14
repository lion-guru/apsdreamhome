import 'package:flutter/material.dart';

class AppConstants {
  // API Configuration
  static const String baseUrl = 'http://localhost/apsdreamhome';
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
  
  // App Info
  static const String appName = 'APS Dream Home';
  static const String supportPhone = '7007444842';
  static const String version = '1.0.0';
}

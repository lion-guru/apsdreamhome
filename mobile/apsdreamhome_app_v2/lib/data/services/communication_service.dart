import 'package:apsdreamhome_app_v2/core/utils/logger.dart';
import 'package:apsdreamhome_app_v2/data/services/auth_service.dart';
import 'package:apsdreamhome_app_v2/core/services/database_helper.dart';

/// Communication Service — console-only (Firebase Messaging removed)
/// Push notifications and email/SMS are handled server-side by PHP backend.
class CommunicationService {
  static CommunicationService? _instance;
  final AuthService _authService;
  final DatabaseHelper _databaseHelper;

  CommunicationService._(this._authService, this._databaseHelper);

  factory CommunicationService() {
    _instance ??= CommunicationService._(AuthService(), DatabaseHelper());
    return _instance!;
  }

  bool _initialized = false;

  bool get isInitialized => _initialized;

  /// Initialize — no-op (Firebase Messaging removed)
  Future<void> initialize() async {
    if (_initialized) return;
    _initialized = true;
    AppLogger.info('Communication service initialized (console-only mode)');
  }

  /// Register device token for push notifications (no-op without Firebase)
  Future<void> registerDeviceToken() async {
    AppLogger.info('registerDeviceToken called (no-op without Firebase)');
  }

  /// Update FCM token (no-op)
  Future<void> updateFCMToken(String token) async {
    AppLogger.info('updateFCMToken called (no-op without Firebase)');
  }

  Future<void> sendPushNotification({
    required String userId,
    required String title,
    required String body,
    Map<String, dynamic>? data,
  }) async {
    AppLogger.info('Push notification requested: $title (no-op without Firebase)');
  }

  Future<void> sendSMS({
    required String phoneNumber,
    required String message,
  }) async {
    AppLogger.info('SMS requested to $phoneNumber (no-op without Firebase)');
  }

  Future<void> sendEmail({
    required String toEmail,
    required String subject,
    required String body,
    bool isHtml = false,
  }) async {
    AppLogger.info('Email requested to $toEmail (no-op without Firebase)');
  }

  Future<void> sendInAppNotification({
    required String userId,
    required String title,
    required String message,
    String type = 'info',
    Map<String, dynamic>? metadata,
  }) async {
    AppLogger.info('In-app notification: $title (console only)');
  }

  Future<void> showLocalNotification({
    required String title,
    required String body,
    Map<String, dynamic>? data,
  }) async {
    AppLogger.info('Local notification: $title (console only)');
  }

  Future<void> sendEmergencyNotification({
    required String message,
    String priority = 'high',
    List<String>? targetRoles,
  }) async {
    AppLogger.info('Emergency notification: $message (console only)');
  }

  /// Cleanup resources
  void dispose() {
    _initialized = false;
  }
}

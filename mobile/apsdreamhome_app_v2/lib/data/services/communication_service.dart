import 'package:apsdreamhome_app_v2/core/utils/logger.dart';
import 'package:apsdreamhome_app_v2/core/services/notification_service.dart';

/// Communication Service — push notifications via FCM, SMS/email are server-side
class CommunicationService {
  static CommunicationService? _instance;

  CommunicationService._();

  factory CommunicationService() {
    _instance ??= CommunicationService._();
    return _instance!;
  }

  final NotificationService _notificationService = NotificationService();

  bool _initialized = false;

  bool get isInitialized => _initialized;

  /// Initialize — starts FCM notification service
  Future<void> initialize() async {
    if (_initialized) return;
    await _notificationService.initialize();
    _initialized = true;
    AppLogger.info('Communication service initialized with FCM');
  }

  /// Register device token for push notifications
  Future<void> registerDeviceToken() async {
    final token = await _notificationService.getToken();
    if (token != null) {
      await _notificationService.saveTokenToBackend(token);
    } else {
      AppLogger.warning('No FCM token available to register');
    }
  }

  /// Update FCM token
  Future<void> updateFCMToken(String token) async {
    await _notificationService.saveTokenToBackend(token);
  }

  /// Send push notification (server-side, logs locally)
  Future<void> sendPushNotification({
    required String userId,
    required String title,
    required String body,
    Map<String, dynamic>? data,
  }) async {
    // Push notifications are sent from the server using the stored FCM token.
    // This client method triggers a local notification for immediate feedback.
    _notificationService.showLocalNotification(
      title: title,
      body: body,
      payload: data,
    );
  }

  Future<void> sendSMS({
    required String phoneNumber,
    required String message,
  }) async {
    AppLogger.info('SMS requested to $phoneNumber (handled server-side)');
  }

  Future<void> sendEmail({
    required String toEmail,
    required String subject,
    required String body,
    bool isHtml = false,
  }) async {
    AppLogger.info('Email requested to $toEmail (handled server-side)');
  }

  Future<void> sendInAppNotification({
    required String userId,
    required String title,
    required String message,
    String type = 'info',
    Map<String, dynamic>? metadata,
  }) async {
    _notificationService.showLocalNotification(
      title: title,
      body: message,
      payload: metadata,
    );
  }

  Future<void> showLocalNotification({
    required String title,
    required String body,
    Map<String, dynamic>? data,
  }) async {
    _notificationService.showLocalNotification(
      title: title,
      body: body,
      payload: data,
    );
  }

  Future<void> sendEmergencyNotification({
    required String message,
    String priority = 'high',
    List<String>? targetRoles,
  }) async {
    _notificationService.showLocalNotification(
      title: 'Emergency',
      body: message,
    );
  }

  /// Subscribe to a topic
  Future<void> subscribeToTopic(String topic) async {
    await _notificationService.subscribeToTopic(topic);
  }

  /// Unsubscribe from a topic
  Future<void> unsubscribeFromTopic(String topic) async {
    await _notificationService.unsubscribeFromTopic(topic);
  }

  /// Cleanup resources
  void dispose() {
    _initialized = false;
    _notificationService.dispose();
  }
}

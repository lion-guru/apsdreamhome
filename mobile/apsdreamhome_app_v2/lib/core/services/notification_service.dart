import 'dart:convert';
import 'package:apsdreamhome_app_v2/core/utils/logger.dart';

/// Notification Service — local console logging only (Firebase removed)
/// Server handles push notifications via PHP backend.
/// This service only handles in-app notification display.
class NotificationService {
  static NotificationService? _instance;

  NotificationService._();

  factory NotificationService() {
    _instance ??= NotificationService._();
    return _instance!;
  }

  bool _initialized = false;
  bool get isInitialized => _initialized;

  final List<Map<String, dynamic>> _notifications = [];
  List<Map<String, dynamic>> get notifications =>
      List.unmodifiable(_notifications);

  int get unreadCount =>
      _notifications.where((n) => n['read'] == false).length;

  /// Initialize — no-op (Firebase removed)
  Future<void> initialize() async {
    if (_initialized) return;
    _initialized = true;
    AppLogger.info('Notification service initialized (local mode)');
  }

  /// Setup push notification listeners — no-op (Firebase removed)
  Future<void> setupPushNotifications({
    required Function(String token) onTokenRefresh,
    required Function(Map<String, dynamic> data) onNotificationTap,
  }) async {
    AppLogger.info('Push notification setup skipped (Firebase removed)');
  }

  /// Save token to backend via API
  Future<void> saveTokenToBackend(String token) async {
    AppLogger.info('Token save requested (no-op without Firebase): $token');
  }

  /// Request notification permissions — no-op (no Firebase)
  Future<void> requestPermission() async {
    AppLogger.info('Notification permission request skipped (no Firebase)');
  }

  void showLocalNotification({
    required String title,
    required String body,
    Map<String, dynamic>? payload,
  }) {
    _notifications.insert(0, {
      'id': DateTime.now().millisecondsSinceEpoch,
      'title': title,
      'body': body,
      'payload': payload,
      'read': false,
      'created_at': DateTime.now().toIso8601String(),
    });
    AppLogger.info('Local notification shown: $title');
  }

  void markAsRead(String notificationId) {
    final index = _notifications.indexWhere(
        (n) => n['id'].toString() == notificationId);
    if (index != -1) {
      _notifications[index]['read'] = true;
    }
  }

  void markAllAsRead() {
    for (var notification in _notifications) {
      notification['read'] = true;
    }
  }

  void clearAll() {
    _notifications.clear();
  }

  void dispose() {
    _initialized = false;
    _notifications.clear();
  }
}

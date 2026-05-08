import 'dart:developer' as developer;
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:awesome_notifications/awesome_notifications.dart';
import 'package:flutter/material.dart';

/// Push Notification Service
/// Handles Firebase Cloud Messaging and local notifications
class NotificationService {
  static final NotificationService _instance = NotificationService._internal();
  factory NotificationService() => _instance;
  NotificationService._internal();

  final FirebaseMessaging _firebaseMessaging = FirebaseMessaging.instance;
  bool _isInitialized = false;

  /// Callback for notification tap
  Function(String type, Map<String, dynamic> data)? onNotificationTap;

  /// Initialize notification service
  Future<void> initialize({
    Function(String type, Map<String, dynamic> data)? onTap,
  }) async {
    if (_isInitialized) return;

    onNotificationTap = onTap;

    try {
      // Initialize Awesome Notifications
      await AwesomeNotifications().initialize(
        'resource://drawable/ic_launcher',
        [
          NotificationChannel(
            channelKey: 'aps_basic_channel',
            channelName: 'APS Dream Home',
            channelDescription: 'Notifications for APS Dream Home app',
            defaultColor: const Color(0xFF4285F4),
            ledColor: Colors.white,
            importance: NotificationImportance.High,
            playSound: true,
            enableVibration: true,
          ),
          NotificationChannel(
            channelKey: 'aps_payment_channel',
            channelName: 'Payment Notifications',
            channelDescription: 'Payment related notifications',
            defaultColor: const Color(0xFF34A853),
            ledColor: Colors.white,
            importance: NotificationImportance.High,
          ),
          NotificationChannel(
            channelKey: 'aps_lead_channel',
            channelName: 'Lead Notifications',
            channelDescription: 'Lead assignment and updates',
            defaultColor: const Color(0xFFEA4335),
            ledColor: Colors.white,
            importance: NotificationImportance.High,
          ),
        ],
      );

      // Request permission
      await _firebaseMessaging.requestPermission(
        alert: true,
        badge: true,
        sound: true,
        provisional: false,
      );

      // Get FCM token
      final token = await _firebaseMessaging.getToken();
      developer.log('FCM Token: $token', name: 'NotificationService');

      // Save token to backend
      if (token != null) {
        await _saveTokenToBackend(token);
      }

      // Listen for token refresh
      _firebaseMessaging.onTokenRefresh.listen(_saveTokenToBackend);

      // Handle foreground messages
      FirebaseMessaging.onMessage.listen(_handleForegroundMessage);

      // Handle notification tap when app is in background
      FirebaseMessaging.onMessageOpenedApp.listen(_handleBackgroundMessage);

      // Check if app was opened from notification (terminated state)
      final initialMessage = await _firebaseMessaging.getInitialMessage();
      if (initialMessage != null) {
        _handleBackgroundMessage(initialMessage);
      }

      // Awesome Notifications tap handler
      AwesomeNotifications().setListeners(
        onActionReceivedMethod: _onNotificationActionReceived,
      );

      _isInitialized = true;
      developer.log('Notification service initialized',
          name: 'NotificationService');
    } catch (e) {
      developer.log('Notification init error: $e', name: 'NotificationService');
    }
  }

  /// Handle foreground messages
  void _handleForegroundMessage(RemoteMessage message) {
    developer.log('Foreground message: ${message.notification?.title}',
        name: 'NotificationService');

    final notification = message.notification;
    final data = message.data;

    if (notification != null) {
      _showLocalNotification(
        title: notification.title ?? 'APS Dream Home',
        body: notification.body ?? '',
        data: data,
        channelKey: _getChannelKey(data['type'] as String?),
      );
    }
  }

  /// Handle background/terminated messages
  void _handleBackgroundMessage(RemoteMessage message) {
    developer.log('Background message: ${message.data}',
        name: 'NotificationService');

    final data = message.data;
    final type = (data['type'] ?? 'general') as String;

    // Notify the app
    onNotificationTap?.call(type, data);
  }

  /// Show local notification
  Future<void> _showLocalNotification({
    required String title,
    required String body,
    required Map<String, dynamic> data,
    String channelKey = 'aps_basic_channel',
  }) async {
    await AwesomeNotifications().createNotification(
      content: NotificationContent(
        id: DateTime.now().millisecondsSinceEpoch.remainder(100000),
        channelKey: channelKey,
        title: title,
        body: body,
        payload:
            data.map((key, value) => MapEntry(key, value?.toString() ?? '')),
        notificationLayout: NotificationLayout.Default,
        category: NotificationCategory.Message,
      ),
      actionButtons: _getActionButtons(data['type'] as String?),
    );
  }

  /// Get action buttons based on notification type
  List<NotificationActionButton>? _getActionButtons(String? type) {
    switch (type) {
      case 'lead_assigned':
        return [
          NotificationActionButton(
            key: 'VIEW_LEAD',
            label: 'View Lead',
          ),
        ];
      case 'payment_success':
        return [
          NotificationActionButton(
            key: 'VIEW_RECEIPT',
            label: 'View Receipt',
          ),
        ];
      case 'site_visit_reminder':
        return [
          NotificationActionButton(
            key: 'VIEW_DETAILS',
            label: 'View Details',
          ),
          NotificationActionButton(
            key: 'NAVIGATE',
            label: 'Navigate',
          ),
        ];
      default:
        return null;
    }
  }

  /// Handle notification action (static method for Awesome Notifications)
  static Future<void> _onNotificationActionReceived(
    ReceivedAction receivedAction,
  ) async {
    final payload = receivedAction.payload ?? {};
    final buttonKey = receivedAction.buttonKeyPressed;

    developer.log('Notification action: $buttonKey, payload: $payload',
        name: 'NotificationService');

    // Handle button presses
    switch (buttonKey) {
      case 'VIEW_LEAD':
        // Navigate to lead details
        break;
      case 'VIEW_RECEIPT':
        // Navigate to receipt
        break;
      case 'NAVIGATE':
        // Open maps
        break;
    }
  }

  /// Get channel key based on notification type
  String _getChannelKey(String? type) {
    switch (type) {
      case 'payment':
      case 'payment_success':
      case 'payment_failed':
        return 'aps_payment_channel';
      case 'lead':
      case 'lead_assigned':
        return 'aps_lead_channel';
      default:
        return 'aps_basic_channel';
    }
  }

  /// Save FCM token to backend
  Future<void> _saveTokenToBackend(String token) async {
    // TODO: Implement API call to save token
    developer.log('Saving FCM token to backend: $token',
        name: 'NotificationService');
  }

  /// Schedule local notification
  Future<void> scheduleNotification({
    required int id,
    required String title,
    required String body,
    required DateTime scheduledDate,
    Map<String, String>? payload,
  }) async {
    await AwesomeNotifications().createNotification(
      content: NotificationContent(
        id: id,
        channelKey: 'aps_basic_channel',
        title: title,
        body: body,
        payload: payload,
      ),
      schedule: NotificationCalendar.fromDate(date: scheduledDate),
    );
  }

  /// Cancel notification
  Future<void> cancelNotification(int id) async {
    await AwesomeNotifications().cancel(id);
  }

  /// Cancel all notifications
  Future<void> cancelAllNotifications() async {
    await AwesomeNotifications().cancelAll();
  }

  /// Show simple notification
  Future<void> showNotification({
    required String title,
    required String body,
    String? type,
    Map<String, dynamic>? data,
  }) async {
    await _showLocalNotification(
      title: title,
      body: body,
      data: data ?? {},
      channelKey: _getChannelKey(type),
    );
  }

  /// Get FCM token
  Future<String?> getToken() async {
    return await _firebaseMessaging.getToken();
  }

  /// Subscribe to topic
  Future<void> subscribeToTopic(String topic) async {
    await _firebaseMessaging.subscribeToTopic(topic);
    developer.log('Subscribed to topic: $topic', name: 'NotificationService');
  }

  /// Unsubscribe from topic
  Future<void> unsubscribeFromTopic(String topic) async {
    await _firebaseMessaging.unsubscribeFromTopic(topic);
    developer.log('Unsubscribed from topic: $topic',
        name: 'NotificationService');
  }
}

/// Background message handler (must be top-level function)
@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  developer.log('Background handler: ${message.messageId}',
      name: 'NotificationService');

  // Handle background message
  final notification = message.notification;
  final data = message.data;

  if (notification != null) {
    await AwesomeNotifications().createNotification(
      content: NotificationContent(
        id: DateTime.now().millisecondsSinceEpoch.remainder(100000),
        channelKey: 'aps_basic_channel',
        title: notification.title ?? 'APS Dream Home',
        body: notification.body ?? '',
        payload: data.map((key, value) => MapEntry(key, value.toString())),
      ),
    );
  }
}

import 'dart:convert';
import 'dart:io';

import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';

import '../utils/logger.dart';
import 'api_service.dart';

/// Background message handler — must be a top-level function
@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  AppLogger.info('Background message received: ${message.messageId}');
}

/// Notification Service — handles FCM push + local in-app notifications
class NotificationService {
  static NotificationService? _instance;

  NotificationService._();

  factory NotificationService() {
    _instance ??= NotificationService._();
    return _instance!;
  }

  bool _initialized = false;
  bool get isInitialized => _initialized;

  FirebaseMessaging? _messaging;
  final FlutterLocalNotificationsPlugin _localNotifications =
      FlutterLocalNotificationsPlugin();

  String? _fcmToken;
  String? get fcmToken => _fcmToken;

  final List<Map<String, dynamic>> _notifications = [];
  List<Map<String, dynamic>> get notifications =>
      List.unmodifiable(_notifications);

  int get unreadCount => _notifications.where((n) => n['read'] == false).length;

  /// Initialize FCM: request permission, get token, set up listeners
  Future<void> initialize() async {
    if (_initialized) return;

    // Firebase may be unavailable (emulator without Google Play Services)
    try {
      _messaging = FirebaseMessaging.instance;
    } catch (e) {
      AppLogger.warning('FirebaseMessaging unavailable: $e');
      return;
    }

    // Register background handler
    FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);

    // Request permission
    await requestPermission();

    // Get initial token
    _fcmToken = await _messaging!.getToken();
    if (_fcmToken != null) {
      AppLogger.info('FCM token obtained: ${_fcmToken!.substring(0, 20)}...');
      await saveTokenToBackend(_fcmToken!);
    }

    // Listen for token refresh
    _messaging!.onTokenRefresh.listen((newToken) {
      _fcmToken = newToken;
      AppLogger.info('FCM token refreshed: ${newToken.substring(0, 20)}...');
      saveTokenToBackend(newToken);
    });

    // Handle foreground messages
    FirebaseMessaging.onMessage.listen(_handleForegroundMessage);

    // Handle notification tap when app is in background
    FirebaseMessaging.onMessageOpenedApp.listen(_handleNotificationTap);

    // Check if app was opened from a notification (cold start)
    final initialMessage = await _messaging!.getInitialMessage();
    if (initialMessage != null) {
      _handleNotificationTap(initialMessage);
    }

    _initialized = true;
    AppLogger.info('Notification service initialized with FCM');
  }

  /// Setup push notification listeners with external callbacks
  Future<void> setupPushNotifications({
    required Function(String token) onTokenRefresh,
    required Function(Map<String, dynamic> data) onNotificationTap,
  }) async {
    if (!_initialized) await initialize();

    // Forward token refresh to caller
    _messaging?.onTokenRefresh.listen((newToken) {
      _fcmToken = newToken;
      onTokenRefresh(newToken);
    });

    // Forward notification taps to caller
    FirebaseMessaging.onMessageOpenedApp.listen((message) {
      onNotificationTap(_messageToMap(message));
    });

    AppLogger.info('Push notification callbacks registered');
  }

  /// Save FCM token to backend API
  Future<void> saveTokenToBackend(String token) async {
    try {
      final apiService = ApiService();
      // ApiService might not be initialized yet (e.g. called before main())
      try {
        apiService.getToken(); // Will throw if not initialized
      } catch (_) {
        AppLogger.warning(
          'FCM token save skipped — ApiService not initialized',
        );
        return;
      }

      final platform = Platform.isAndroid ? 'android' : 'ios';

      await apiService.request(
        method: 'POST',
        endpoint: 'fcm/register',
        data: {'token': token, 'platform': platform},
      );
      AppLogger.info('FCM token saved to backend');
    } catch (e) {
      AppLogger.warning('Failed to save FCM token to backend: $e');
    }
  }

  /// Request notification permissions from the user
  Future<void> requestPermission() async {
    if (_messaging == null) return;
    final settings = await _messaging!.requestPermission(
      alert: true,
      badge: true,
      sound: true,
      provisional: false,
      criticalAlert: true,
    );

    AppLogger.info(
      'Notification permission status: ${settings.authorizationStatus}',
    );
  }

  /// Subscribe to FCM topics for targeted notifications
  Future<void> subscribeToTopics(int userId, String role) async {
    if (_messaging == null) return;
    try {
      // Subscribe to user-specific topic
      await _messaging!.subscribeToTopic('user_$userId');
      AppLogger.info('Subscribed to topic: user_$userId');

      // Subscribe to role-specific topic
      await _messaging!.subscribeToTopic('role_$role');
      AppLogger.info('Subscribed to topic: role_$role');

      // Subscribe to all-users topic
      await _messaging!.subscribeToTopic('all_users');
      AppLogger.info('Subscribed to topic: all_users');
    } catch (e) {
      AppLogger.warning('Failed to subscribe to topics: $e');
    }
  }

  /// Unsubscribe from FCM topics
  Future<void> unsubscribeFromTopics(int userId, String role) async {
    if (_messaging == null) return;
    try {
      await _messaging!.unsubscribeFromTopic('user_$userId');
      await _messaging!.unsubscribeFromTopic('role_$role');
      await _messaging!.unsubscribeFromTopic('all_users');
      AppLogger.info('Unsubscribed from topics');
    } catch (e) {
      AppLogger.warning('Failed to unsubscribe from topics: $e');
    }
  }

  /// Handle incoming foreground messages
  void _handleForegroundMessage(RemoteMessage message) {
    AppLogger.info('Foreground message: ${message.notification?.title}');

    // Store in-memory
    _notifications.insert(0, _messageToMap(message));

    // Show local notification overlay
    final notification = message.notification;
    if (notification != null) {
      showLocalNotification(
        title: notification.title ?? 'APS Dream Home',
        body: notification.body ?? '',
        payload: message.data,
      );
    }
  }

  /// Handle notification tap (app in background or cold start)
  void _handleNotificationTap(RemoteMessage message) {
    AppLogger.info('Notification tapped: ${message.data}');
    _notifications.insert(0, {..._messageToMap(message), 'read': true});
    // Navigation logic can be added here based on message.data
  }

  /// Show a local notification using flutter_local_notifications
  void showLocalNotification({
    required String title,
    required String body,
    Map<String, dynamic>? payload,
  }) async {
    // Initialize local notifications if not already done
    const androidSettings = AndroidInitializationSettings(
      '@mipmap/ic_launcher',
    );
    const initSettings = InitializationSettings(android: androidSettings);
    await _localNotifications.initialize(initSettings);

    const androidDetails = AndroidNotificationDetails(
      'aps_dreamhome_channel',
      'APS Dream Home Notifications',
      channelDescription: 'Push notifications from APS Dream Home',
      importance: Importance.high,
      priority: Priority.high,
      icon: '@mipmap/ic_launcher',
    );

    const details = NotificationDetails(android: androidDetails);

    await _localNotifications.show(
      DateTime.now().millisecondsSinceEpoch ~/ 1000,
      title,
      body,
      details,
      payload: payload != null ? jsonEncode(payload) : null,
    );

    // Also store in-memory list
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

  /// Subscribe to a topic for targeted notifications
  Future<void> subscribeToTopic(String topic) async {
    await _messaging?.subscribeToTopic(topic);
    AppLogger.info('Subscribed to topic: $topic');
  }

  /// Unsubscribe from a topic
  Future<void> unsubscribeFromTopic(String topic) async {
    await _messaging?.unsubscribeFromTopic(topic);
    AppLogger.info('Unsubscribed from topic: $topic');
  }

  /// Get current FCM token
  Future<String?> getToken() async {
    _fcmToken = await _messaging?.getToken();
    return _fcmToken;
  }

  /// Convert RemoteMessage to a Map for storage
  Map<String, dynamic> _messageToMap(RemoteMessage message) {
    return {
      'id':
          message.messageId ?? DateTime.now().millisecondsSinceEpoch.toString(),
      'title': message.notification?.title ?? '',
      'body': message.notification?.body ?? '',
      'data': message.data,
      'read': false,
      'created_at': DateTime.now().toIso8601String(),
    };
  }

  void markAsRead(String notificationId) {
    final index = _notifications.indexWhere(
      (n) => n['id'].toString() == notificationId,
    );
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

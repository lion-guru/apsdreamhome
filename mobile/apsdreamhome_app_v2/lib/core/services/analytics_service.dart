import 'dart:developer' as developer;
import 'package:firebase_analytics/firebase_analytics.dart';
import 'package:flutter/material.dart';

/// Firebase Analytics Service
/// Tracks user behavior and app usage
class AnalyticsService {
  static final AnalyticsService _instance = AnalyticsService._internal();
  factory AnalyticsService() => _instance;
  AnalyticsService._internal();

  final FirebaseAnalytics _analytics = FirebaseAnalytics.instance;
  bool _isEnabled = true;

  /// Initialize analytics
  Future<void> initialize() async {
    await _analytics.setAnalyticsCollectionEnabled(true);
    developer.log('Analytics initialized', name: 'AnalyticsService');
  }

  /// Enable/disable analytics
  Future<void> setEnabled(bool enabled) async {
    _isEnabled = enabled;
    await _analytics.setAnalyticsCollectionEnabled(enabled);
  }

  /// Log screen view
  Future<void> logScreenView({
    required String screenName,
    String? screenClass,
  }) async {
    if (!_isEnabled) return;

    await _analytics.logScreenView(
      screenName: screenName,
      screenClass: screenClass ?? screenName,
    );

    developer.log('Screen view: $screenName', name: 'AnalyticsService');
  }

  /// Log login event
  Future<void> logLogin({
    required String method,
    String? userId,
  }) async {
    if (!_isEnabled) return;

    await _analytics.logLogin(loginMethod: method);

    if (userId != null) {
      await _analytics.setUserId(id: userId);
    }

    developer.log('Login: $method', name: 'AnalyticsService');
  }

  /// Log sign up
  Future<void> logSignUp({
    required String method,
    required String userType,
  }) async {
    if (!_isEnabled) return;

    await _analytics.logSignUp(signUpMethod: method);
    await _analytics.setUserProperty(name: 'user_type', value: userType);

    developer.log('Sign up: $method, Type: $userType', name: 'AnalyticsService');
  }

  /// Log property view
  Future<void> logPropertyView({
    required String propertyId,
    required String propertyName,
    String? propertyType,
    double? price,
  }) async {
    if (!_isEnabled) return;

    await _analytics.logEvent(
      name: 'view_property',
      parameters: {
        'property_id': propertyId,
        'property_name': propertyName,
        if (propertyType != null) 'property_type': propertyType,
        if (price != null) 'price': price,
      },
    );

    developer.log('Property view: $propertyName', name: 'AnalyticsService');
  }

  /// Log booking initiated
  Future<void> logBookingInitiated({
    required String propertyId,
    required String propertyName,
    required double amount,
    required String paymentPlan,
  }) async {
    if (!_isEnabled) return;

    await _analytics.logEvent(
      name: 'begin_checkout',
      parameters: {
        'property_id': propertyId,
        'property_name': propertyName,
        'value': amount,
        'currency': 'INR',
        'payment_plan': paymentPlan,
      },
    );

    developer.log('Booking initiated: $propertyName', name: 'AnalyticsService');
  }

  /// Log payment
  Future<void> logPayment({
    required String propertyId,
    required String propertyName,
    required double amount,
    required String method,
    required bool success,
    String? transactionId,
  }) async {
    if (!_isEnabled) return;

    if (success) {
      await _analytics.logPurchase(
        currency: 'INR',
        value: amount,
        transactionId: transactionId,
        items: [
          AnalyticsEventItem(
            itemId: propertyId,
            itemName: propertyName,
            itemCategory: 'property',
            price: amount,
          ),
        ],
      );
    }

    await _analytics.logEvent(
      name: 'payment',
      parameters: {
        'property_id': propertyId,
        'property_name': propertyName,
        'amount': amount,
        'method': method,
        'success': success,
        if (transactionId != null) 'transaction_id': transactionId,
      },
    );

    developer.log(
      'Payment: $method, Amount: $amount, Success: $success',
      name: 'AnalyticsService',
    );
  }

  /// Log lead interaction
  Future<void> logLeadAction({
    required String action,
    String? leadId,
    Map<String, dynamic>? properties,
  }) async {
    if (!_isEnabled) return;

    await _analytics.logEvent(
      name: 'lead_$action',
      parameters: {
        if (leadId != null) 'lead_id': leadId,
        ...?properties?.map((key, value) => MapEntry(key, value.toString())),
      },
    );

    developer.log('Lead action: $action', name: 'AnalyticsService');
  }

  /// Log search
  Future<void> logSearch({
    required String searchTerm,
    String? location,
    String? propertyType,
    double? minPrice,
    double? maxPrice,
    int? resultsCount,
  }) async {
    if (!_isEnabled) return;

    await _analytics.logSearch(
      searchTerm: searchTerm,
      origin: 'mobile_app',
    );

    await _analytics.logEvent(
      name: 'property_search',
      parameters: {
        'search_term': searchTerm,
        if (location != null) 'location': location,
        if (propertyType != null) 'property_type': propertyType,
        if (minPrice != null) 'min_price': minPrice,
        if (maxPrice != null) 'max_price': maxPrice,
        if (resultsCount != null) 'results_count': resultsCount,
      },
    );
  }

  /// Log share
  Future<void> logShare({
    required String contentType,
    required String itemId,
    required String method,
  }) async {
    if (!_isEnabled) return;

    await _analytics.logShare(
      contentType: contentType,
      itemId: itemId,
      method: method,
    );
  }

  /// Log error
  Future<void> logError({
    required String errorType,
    required String errorMessage,
    String? screenName,
  }) async {
    if (!_isEnabled) return;

    await _analytics.logEvent(
      name: 'app_error',
      parameters: {
        'error_type': errorType,
        'error_message': errorMessage.substring(0, errorMessage.length > 100 ? 100 : errorMessage.length),
        if (screenName != null) 'screen_name': screenName,
      },
    );
  }

  /// Set user properties
  Future<void> setUserProperties({
    String? userId,
    String? userType,
    String? city,
    String? associateLevel,
  }) async {
    if (!_isEnabled) return;

    if (userId != null) {
      await _analytics.setUserId(id: userId);
    }

    if (userType != null) {
      await _analytics.setUserProperty(name: 'user_type', value: userType);
    }

    if (city != null) {
      await _analytics.setUserProperty(name: 'city', value: city);
    }

    if (associateLevel != null) {
      await _analytics.setUserProperty(name: 'associate_level', value: associateLevel);
    }
  }

  /// Log app update
  Future<void> logAppUpdate({
    required String previousVersion,
    required String newVersion,
  }) async {
    if (!_isEnabled) return;

    await _analytics.logEvent(
      name: 'app_update',
      parameters: {
        'previous_version': previousVersion,
        'new_version': newVersion,
      },
    );
  }

  /// Get analytics observer for navigation
  NavigatorObserver get navigatorObserver {
    return FirebaseAnalyticsObserver(analytics: _analytics);
  }
}

import 'dart:developer' as developer;
import 'package:flutter/material.dart';

/// Analytics Service — logs to console (Firebase Analytics removed)
/// All methods are no-ops that log via dart:developer
class AnalyticsService {
  static final AnalyticsService _instance = AnalyticsService._internal();
  factory AnalyticsService() => _instance;
  AnalyticsService._internal();

  bool _isEnabled = true;

  Future<void> initialize() async {
    developer.log('Analytics initialized (console only)', name: 'AnalyticsService');
  }

  Future<void> setEnabled(bool enabled) async {
    _isEnabled = enabled;
  }

  Future<void> logScreenView({
    required String screenName,
    String? screenClass,
  }) async {
    if (!_isEnabled) return;
    developer.log('Screen view: $screenName', name: 'AnalyticsService');
  }

  Future<void> logLogin({
    required String method,
    String? userId,
  }) async {
    if (!_isEnabled) return;
    developer.log('Login: $method', name: 'AnalyticsService');
  }

  Future<void> logSignUp({
    required String method,
    required String userType,
  }) async {
    if (!_isEnabled) return;
    developer.log('Sign up: $method, Type: $userType', name: 'AnalyticsService');
  }

  Future<void> logPropertyView({
    required String propertyId,
    required String propertyName,
    String? propertyType,
    double? price,
  }) async {
    if (!_isEnabled) return;
    developer.log('Property view: $propertyName', name: 'AnalyticsService');
  }

  Future<void> logBookingInitiated({
    required String propertyId,
    required String propertyName,
    required double amount,
    required String paymentPlan,
  }) async {
    if (!_isEnabled) return;
    developer.log('Booking initiated: $propertyName', name: 'AnalyticsService');
  }

  Future<void> logPayment({
    required String propertyId,
    required String propertyName,
    required double amount,
    required String method,
    required bool success,
    String? transactionId,
  }) async {
    if (!_isEnabled) return;
    developer.log('Payment: $method, Amount: $amount, Success: $success', name: 'AnalyticsService');
  }

  Future<void> logLeadAction({
    required String action,
    String? leadId,
    Map<String, dynamic>? properties,
  }) async {
    if (!_isEnabled) return;
    developer.log('Lead action: $action', name: 'AnalyticsService');
  }

  Future<void> logSearch({
    required String searchTerm,
    String? location,
    String? propertyType,
    double? minPrice,
    double? maxPrice,
    int? resultsCount,
  }) async {
    if (!_isEnabled) return;
    developer.log('Search: $searchTerm', name: 'AnalyticsService');
  }

  Future<void> logShare({
    required String contentType,
    required String itemId,
    required String method,
  }) async {
    if (!_isEnabled) return;
    developer.log('Share: $contentType, Item: $itemId', name: 'AnalyticsService');
  }

  Future<void> logError({
    required String errorType,
    required String errorMessage,
    String? screenName,
  }) async {
    if (!_isEnabled) return;
    developer.log('Error: $errorType - $errorMessage', name: 'AnalyticsService');
  }

  Future<void> setUserProperties({
    String? userId,
    String? userType,
    String? city,
    String? associateLevel,
  }) async {
    if (!_isEnabled) return;
    developer.log('User properties set', name: 'AnalyticsService');
  }

  Future<void> logAppUpdate({
    required String previousVersion,
    required String newVersion,
  }) async {
    if (!_isEnabled) return;
    developer.log('App update: $previousVersion -> $newVersion', name: 'AnalyticsService');
  }

  NavigatorObserver get navigatorObserver {
    return NavigatorObserver();
  }
}

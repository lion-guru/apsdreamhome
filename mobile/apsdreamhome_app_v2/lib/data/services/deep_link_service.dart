import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:apsdreamhome_app_v2/core/utils/logger.dart';

/// Deep Link Service — no-op stub (Firebase Dynamic Links removed)
/// When backend supports deep links, replace this implementation.
class DeepLinkService extends ChangeNotifier {
  static DeepLinkService? _instance;

  DeepLinkService._();

  factory DeepLinkService() {
    _instance ??= DeepLinkService._();
    return _instance!;
  }

  final StreamController<String> _linkStreamController =
      StreamController<String>.broadcast();

  Stream<String> get linkStream => _linkStreamController.stream;

  bool _initialized = false;

  bool get isInitialized => _initialized;

  Future<void> initialize() async {
    if (_initialized) return;
    _initialized = true;
    AppLogger.info('Deep link service initialized (stub mode)');
  }

  Future<void> handleInitialLink() async {
    AppLogger.info('Deep link: handleInitialLink called (stub)');
  }

  Future<String?> generateDynamicLink({
    required String propertyId,
    required String propertyName,
    String? imageUrl,
    String? description,
  }) async {
    AppLogger.info('Deep link generation requested for property: $propertyId (stub)');
    // Return null — no deep link available without Firebase
    return null;
  }

  Future<String?> generateReferralLink({
    required String associateId,
    required String associateName,
  }) async {
    AppLogger.info('Referral link generation requested (stub)');
    return null;
  }

  @override
  void dispose() {
    _linkStreamController.close();
    super.dispose();
  }
}

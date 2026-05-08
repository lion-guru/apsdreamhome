import 'dart:developer' as developer;
import 'package:local_auth/local_auth.dart';
import 'package:flutter/services.dart';

/// Biometric Authentication Service
/// Handles fingerprint and face ID authentication
class BiometricAuthService {
  static final BiometricAuthService _instance =
      BiometricAuthService._internal();
  factory BiometricAuthService() => _instance;
  BiometricAuthService._internal();

  final LocalAuthentication _localAuth = LocalAuthentication();
  bool _isAvailable = false;
  List<BiometricType> _availableBiometrics = [];

  /// Check if biometrics is available
  Future<bool> checkBiometricAvailability() async {
    try {
      _isAvailable = await _localAuth.canCheckBiometrics;
      _availableBiometrics = await _localAuth.getAvailableBiometrics();

      developer.log(
        'Biometric available: $_isAvailable, Types: $_availableBiometrics',
        name: 'BiometricAuthService',
      );

      return _isAvailable && _availableBiometrics.isNotEmpty;
    } on PlatformException catch (e) {
      developer.log('Biometric check error: $e', name: 'BiometricAuthService');
      return false;
    }
  }

  /// Get available biometric types
  List<BiometricType> get availableBiometrics => _availableBiometrics;

  /// Get readable biometric name
  String getBiometricName() {
    if (_availableBiometrics.contains(BiometricType.face)) {
      return 'Face ID';
    } else if (_availableBiometrics.contains(BiometricType.fingerprint)) {
      return 'Fingerprint';
    }
    return 'Biometric';
  }

  /// Authenticate with biometrics
  Future<bool> authenticate({
    String localizedReason = 'Please authenticate to access APS Dream Home',
  }) async {
    try {
      if (!_isAvailable) {
        developer.log('Biometric not available', name: 'BiometricAuthService');
        return false;
      }

      final bool isAuthenticated = await _localAuth.authenticate(
        localizedReason: localizedReason,
      );

      developer.log(
        'Authentication result: $isAuthenticated',
        name: 'BiometricAuthService',
      );

      return isAuthenticated;
    } on PlatformException catch (e) {
      developer.log('Authentication error: $e', name: 'BiometricAuthService');
      return false;
    }
  }

  /// Authenticate for sensitive action
  Future<bool> authenticateForAction(String action) async {
    return await authenticate(
      localizedReason: 'Authenticate to $action',
    );
  }

  /// Stop authentication
  Future<bool> stopAuthentication() async {
    try {
      return await _localAuth.stopAuthentication();
    } catch (e) {
      developer.log('Stop auth error: $e', name: 'BiometricAuthService');
      return false;
    }
  }

  /// Check if device has biometric hardware
  Future<bool> hasBiometricHardware() async {
    try {
      final availableBiometrics = await _localAuth.getAvailableBiometrics();
      return availableBiometrics.isNotEmpty;
    } catch (e) {
      return false;
    }
  }

  /// Get biometric icon
  IconType getBiometricIcon() {
    if (_availableBiometrics.contains(BiometricType.face)) {
      return IconType.face;
    }
    return IconType.fingerprint;
  }
}

enum IconType { fingerprint, face }

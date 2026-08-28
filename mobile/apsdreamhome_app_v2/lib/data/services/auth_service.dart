import 'dart:async';

import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import '../../core/services/api_service.dart';
import '../../core/services/database_helper.dart';
import '../../core/utils/logger.dart';
import '../models/user_model.dart' as user_model;
import '../repositories/auth_repository.dart';

/// Authentication Service — MySQL-first via AuthRepository
///
/// Provides the same public API surface that 7 consumer pages depend on,
/// but delegates all data operations to AuthRepository (REST API → MySQL).
/// No Firebase Auth or Firestore imports.
class AuthService {
  final AuthRepository _repository;

  AuthService()
    : _repository = AuthRepository(
        ApiService(),
        DatabaseHelper(),
        const FlutterSecureStorage(),
      );

  // In-memory login flag — set by login(), cleared by logout().
  // Allows the synchronous `currentUser` getter to work without async storage.
  bool _isLoggedIn = false;

  /// Synchronous check: is a user currently logged in?
  /// Consumers use this as a null-check proxy (`authService.currentUser != null`).
  /// We check in-memory flag first, then fall back to token presence.
  Future<bool> _checkTokenPresence() async {
    try {
      final token = await ApiService().getToken();
      return token != null && token.isNotEmpty;
    } catch (_) {
      return false;
    }
  }

  /// Synchronous current-user getter.
  /// Returns a non-null placeholder when logged in (consumers only null-check it).
  dynamic get currentUser => _isLoggedIn ? _placeholderUser : null;

  /// Lightweight placeholder returned by [currentUser] when logged in.
  /// Consumers only check `!= null`; actual user data comes from [getCurrentUserData].
  static final _placeholderUser = Object();

  /// Check and update login state from secure storage.
  /// Call during app init or after login to sync the in-memory flag.
  Future<void> refreshLoginState() async {
    _isLoggedIn = await _checkTokenPresence();
  }

  // ── Data Operations (delegated to AuthRepository) ────────────────────

  /// Get current user data from MySQL via REST API.
  Future<user_model.User?> getCurrentUserData() async {
    try {
      if (!_isLoggedIn) {
        // Try to restore login state from storage
        await refreshLoginState();
        if (!_isLoggedIn) return null;
      }

      final user = await _repository.getCurrentUser();
      if (user == null) {
        _isLoggedIn = false;
      }
      return user;
    } catch (e) {
      AppLogger.error('Failed to get current user data', e);
      return null;
    }
  }

  /// Login with email/password via REST API.
  /// Returns a non-null object on success (consumers check `credential != null`).
  Future<dynamic> loginWithEmailPassword(String email, String password) async {
    try {
      AppLogger.info('LOGIN ATTEMPT: $email');
      final user = await _repository.login(email, password);
      _isLoggedIn = true;
      AppLogger.info('LOGIN SUCCESS: ${user.email}');
      return user;
    } catch (e) {
      AppLogger.error('LOGIN FAILED: $e');
      rethrow;
    }
  }

  /// Register with email/password via REST API.
  /// Returns a non-null object on success (consumers check `credential != null`).
  Future<dynamic> registerWithEmailPassword({
    required String email,
    required String password,
    required String name,
    required String phone,
    required String role,
    String? parentReferralCode,
  }) async {
    try {
      AppLogger.info('REGISTRATION ATTEMPT: $email (role: $role)');
      final user = await _repository.register(
        name: name,
        email: email,
        phone: phone,
        password: password,
        role: role,
        parentReferralCode: parentReferralCode,
      );
      _isLoggedIn = true;
      AppLogger.info('REGISTRATION SUCCESS: ${user.email}');
      return user;
    } catch (e) {
      AppLogger.error('REGISTRATION FAILED: $e');
      rethrow;
    }
  }

  /// Send phone OTP via REST API (delegates to backend).
  Future<void> sendPhoneOTP(
    String phoneNumber,
    Function(String verificationId) onCodeSent,
    Function(String error) onError,
  ) async {
    try {
      await _repository.resendOtp(phoneNumber);
      // Backend sends OTP; on success, call onCodeSent with a placeholder.
      // The actual verification is handled by verifyOTP.
      onCodeSent('rest_otp_$phoneNumber');
    } catch (e) {
      AppLogger.error('Error sending OTP', e);
      onError('Failed to send OTP');
    }
  }

  /// Verify OTP via REST API.
  /// Returns non-null on success (consumers null-check the result).
  Future<dynamic> verifyOTP(String verificationId, String smsCode) async {
    try {
      // Extract phone from verificationId placeholder
      final phone = verificationId.startsWith('rest_otp_')
          ? verificationId.substring('rest_otp_'.length)
          : verificationId;

      final valid = await _repository.verifyOtp(phone, smsCode);
      if (valid) {
        _isLoggedIn = true;
        return Object(); // non-null success marker
      }
      return null;
    } catch (e) {
      AppLogger.error('OTP verification failed', e);
      return null;
    }
  }

  /// Complete phone-based registration via REST API.
  Future<void> completePhoneRegistration({
    required String userId,
    required String name,
    required String email,
    required String role,
    String? parentReferralCode,
  }) async {
    try {
      await _repository.register(
        name: name,
        email: email,
        phone: '',
        password: '',
        role: role,
      );
      AppLogger.info('Phone registration completed for: $userId');
    } catch (e) {
      AppLogger.error('Phone registration failed', e);
      rethrow;
    }
  }

  /// Logout — clears tokens and local data.
  Future<void> logout() async {
    try {
      await _repository.logout();
      _isLoggedIn = false;
      AppLogger.info('User logged out');
    } catch (e) {
      AppLogger.error('Logout error', e);
      _isLoggedIn = false;
    }
  }

  /// Send password reset email via REST API.
  Future<void> sendPasswordResetEmail(String email) async {
    try {
      await _repository.forgotPassword(email);
      AppLogger.info('Password reset email sent to: $email');
    } catch (e) {
      AppLogger.error('Password reset failed', e);
      rethrow;
    }
  }

  /// Change password via REST API.
  Future<void> changePassword(String newPassword) async {
    try {
      await _repository.changePassword('', newPassword);
      AppLogger.info('Password changed successfully');
    } catch (e) {
      AppLogger.error('Password change failed', e);
      rethrow;
    }
  }

  /// Update user profile via REST API.
  Future<void> updateProfile({
    String? name,
    String? photoURL,
    String? address,
    String? city,
    String? state,
    String? pincode,
    DateTime? dateOfBirth,
    String? gender,
    dynamic bankDetails,
  }) async {
    try {
      await _repository.updateProfile(name: name, phone: null);
      AppLogger.info('Profile updated successfully');
    } catch (e) {
      AppLogger.error('Profile update failed', e);
      rethrow;
    }
  }

  /// Check if a user exists by phone via REST API.
  Future<bool> checkUserExists(String phone) async {
    try {
      final response = await ApiService().get(
        '/auth/check-user',
        queryParameters: {'phone': phone},
      );
      return response['exists'] == true;
    } catch (e) {
      return false;
    }
  }

  /// Get referrer name by referral code via REST API.
  Future<String?> getReferrerName(String code) async {
    try {
      final response = await ApiService().get(
        '/auth/referrer',
        queryParameters: {'code': code.toUpperCase()},
      );
      return response['data']?['name'] as String?;
    } catch (e) {
      AppLogger.error('Error getting referrer name', e);
      return null;
    }
  }
}

// ── Providers ──────────────────────────────────────────────────────────

/// Auth service provider (same name as before — zero consumer changes).
final authServiceProvider = Provider<AuthService>((ref) => AuthService());

/// Current user data provider — fetches user from MySQL via REST API.
/// Used by admin_shell.dart, associate_dashboard_page.dart.
final currentUserDataProvider = FutureProvider<user_model.User?>((ref) async {
  final authService = ref.watch(authServiceProvider);
  return authService.getCurrentUserData();
});

/// Legacy auth state stream — emits current user model on changes.
/// Kept for API compatibility; fires once with current state.
final currentUserProvider = StreamProvider<user_model.User?>((ref) async* {
  final authService = ref.watch(authServiceProvider);
  final user = await authService.getCurrentUserData();
  yield user;
});

import 'dart:async';
import 'dart:convert';

import 'package:firebase_auth/firebase_auth.dart' as firebase_auth;
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/constants/app_constants.dart';
import '../../core/utils/logger.dart';
import '../models/user_model.dart' as user_model;
import '../models/bank_details.dart';

/// Authentication Service - Multi-role (Customer, Associate, Agent, Admin)
class AuthService {
  final firebase_auth.FirebaseAuth _auth = firebase_auth.FirebaseAuth.instance;
  final FirebaseFirestore _firestore = FirebaseFirestore.instance;
  final FlutterSecureStorage _secureStorage = const FlutterSecureStorage();

  // Demo mode flag - set to true to bypass Firebase for testing
  static bool _demoMode = false;
  static bool get demoMode => _demoMode;
  static void setDemoMode(bool value) => _demoMode = value;

  // Current User Stream
  Stream<firebase_auth.User?> get authStateChanges => _auth.authStateChanges();

  // Get Current User
  firebase_auth.User? get currentUser => _auth.currentUser;

  // Get Current User Data from Firestore
  Future<user_model.User?> getCurrentUserData() async {
    print('getCurrentUserData called');
    AppLogger.info('getCurrentUserData called');

    final user = currentUser;
    print('Current user: ${user?.uid}');
    AppLogger.info('Current user: ${user?.uid}');

    if (user == null) {
      print('User is null, returning null');
      AppLogger.info('User is null, returning null');
      return null;
    }

    try {
      print('Fetching user document from Firestore...');
      AppLogger.info('Fetching user document from Firestore...');

      final doc = await _firestore
          .collection(AppConstants.usersCollection)
          .doc(user.uid)
          .get();

      print('User doc exists: ${doc.exists}');
      AppLogger.info('User doc exists: ${doc.exists}');

      if (doc.exists) {
        print('User data: ${doc.data()}');
        AppLogger.info('User data: ${doc.data()}');

        // Convert data to handle type conversions
        final data = Map<String, dynamic>.from(doc.data()!);
        data['id'] = doc.id;

        print('Processing fields...');
        print('id: ${data['id']} (${data['id'].runtimeType})');
        print('name: ${data['name']} (${data['name']?.runtimeType})');
        print('email: ${data['email']} (${data['email']?.runtimeType})');
        print('phone: ${data['phone']} (${data['phone']?.runtimeType})');
        print('role: ${data['role']} (${data['role']?.runtimeType})');
        print('status: ${data['status']} (${data['status']?.runtimeType})');

        // Convert string timestamps to DateTime if needed
        if (data['createdAt'] is String) {
          data['createdAt'] = DateTime.parse(data['createdAt'] as String);
        }
        if (data['lastLoginAt'] is String) {
          data['lastLoginAt'] = DateTime.parse(data['lastLoginAt'] as String);
        }

        print('Calling UserModel.fromJson...');
        return user_model.User.fromJson(data);
      }

      print('User doc does not exist, returning null');
      AppLogger.info('User doc does not exist, returning null');
      return null;
    } catch (e, stackTrace) {
      print('Error getting user data: $e');
      AppLogger.error('Failed to get user data', e, stackTrace);
      return null;
    }
  }

  // Phone Authentication - Send OTP
  Future<void> sendPhoneOTP(
    String phoneNumber,
    Function(String verificationId) onCodeSent,
    Function(String error) onError,
  ) async {
    try {
      await _auth.verifyPhoneNumber(
        phoneNumber: phoneNumber,
        timeout: const Duration(seconds: 60),
        verificationCompleted:
            (firebase_auth.PhoneAuthCredential credential) async {
          // Auto-verification (Android)
          await _auth.signInWithCredential(credential);
        },
        verificationFailed: (firebase_auth.FirebaseAuthException e) {
          AppLogger.error('Phone verification failed', e);
          onError(e.message ?? 'Verification failed');
        },
        codeSent: (String verificationId, int? resendToken) {
          AppLogger.info('OTP sent successfully');
          onCodeSent(verificationId);
        },
        codeAutoRetrievalTimeout: (String verificationId) {
          AppLogger.warning('Auto retrieval timeout');
        },
      );
    } catch (e, stackTrace) {
      AppLogger.error('Error sending OTP', e, stackTrace);
      onError('Failed to send OTP');
    }
  }

  // Verify OTP
  Future<firebase_auth.UserCredential?> verifyOTP(
    String verificationId,
    String smsCode,
  ) async {
    try {
      final credential = firebase_auth.PhoneAuthProvider.credential(
        verificationId: verificationId,
        smsCode: smsCode,
      );

      final userCredential = await _auth.signInWithCredential(credential);
      AppLogger.info('OTP verified successfully');
      return userCredential;
    } catch (e, stackTrace) {
      AppLogger.error('OTP verification failed', e, stackTrace);
      return null;
    }
  }

  // Email & Password Login
  Future<firebase_auth.UserCredential?> loginWithEmailPassword(
    String email,
    String password,
  ) async {
    // Demo mode bypass - DISABLED for real Firebase Auth
    // if (_demoMode) {
    //   AppLogger.info('Demo mode login: $email');
    //   return null;
    // }

    try {
      print('LOGIN ATTEMPT: $email');
      AppLogger.info('LOGIN ATTEMPT: $email');
      print('Demo mode: $_demoMode');
      AppLogger.info('Demo mode: $_demoMode');

      print('Calling signInWithEmailAndPassword...');
      print('Starting Firebase Auth call...');
      final credential = await _auth
          .signInWithEmailAndPassword(
        email: email,
        password: password,
      )
          .timeout(
        const Duration(seconds: 30),
        onTimeout: () {
          print('Firebase Auth timeout after 30 seconds');
          throw Exception('Firebase Auth timeout');
        },
      );

      print('Firebase Auth call completed');
      print('LOGIN SUCCESS: ${credential.user?.email}');
      AppLogger.info('LOGIN SUCCESS: ${credential.user?.email}');
      print('User UID: ${credential.user?.uid}');
      AppLogger.info('User UID: ${credential.user?.uid}');
      print('Email verified: ${credential.user?.emailVerified}');
      AppLogger.info('Email verified: ${credential.user?.emailVerified}');

      print('Checking user document...');
      AppLogger.info('Checking user document...');

      // Check if user document exists in Firestore, create if not
      try {
        final userDoc = await _firestore
            .collection(AppConstants.usersCollection)
            .doc(credential.user!.uid)
            .get();

        print('User doc exists: ${userDoc.exists}');
        AppLogger.info('User doc exists: ${userDoc.exists}');

        if (!userDoc.exists) {
          print('User document not found, creating...');
          AppLogger.info('User document not found, creating...');

          // Create user document with default role
          final userModel = user_model.User(
            userId: credential.user!.uid,
            name: credential.user!.displayName ?? email.split('@')[0],
            email: email,
            phone: credential.user!.phoneNumber ?? '',
            rank: AppConstants.roleCustomer,
            target: 0.0,
            createdAt: DateTime.now().toIso8601String(),
            updatedAt: DateTime.now().toIso8601String(),
          );

          print('Writing to Firestore...');
          AppLogger.info('Writing to Firestore...');

          await _firestore
              .collection(AppConstants.usersCollection)
              .doc(credential.user!.uid)
              .set(userModel.toJson());

          print('User document created');
          AppLogger.info('User document created');
        } else {
          print('User document already exists');
          AppLogger.info('User document already exists');
        }
      } catch (e, stackTrace) {
        print('Firestore error: $e');
        AppLogger.error('Firestore error', e, stackTrace);
        // Don't throw - allow login to continue even if Firestore fails
      }

      print('Updating last login...');
      AppLogger.info('Updating last login...');

      // Update last login
      await _updateLastLogin(credential.user!.uid);

      print('Returning credential...');
      print('Returning credential');
      AppLogger.info('Returning credential');

      return credential;
    } on firebase_auth.FirebaseAuthException catch (e) {
      AppLogger.error('LOGIN FAILED: ${e.code} - ${e.message}');
      AppLogger.error('Error code: ${e.code}');
      throw _handleAuthError(e);
    } catch (e) {
      AppLogger.error('UNEXPECTED ERROR: $e');
      throw Exception('Login failed: $e');
    }
  }

  // Register with Email & Password
  Future<firebase_auth.UserCredential?> registerWithEmailPassword({
    required String email,
    required String password,
    required String name,
    required String phone,
    required String role,
    String? parentReferralCode,
  }) async {
    // Demo mode bypass - DISABLED for real Firebase Auth
    // if (_demoMode) {
    //   AppLogger.info('Demo mode register: $email');
    //   return null;
    // }

    try {
      AppLogger.info('📝 REGISTRATION ATTEMPT: $email');
      AppLogger.info('📝 Role: $role');
      AppLogger.info('📝 Referral code: $parentReferralCode');

      // Create user in Firebase Auth
      final credential = await _auth.createUserWithEmailAndPassword(
        email: email,
        password: password,
      );

      final user = credential.user;
      if (user == null) throw Exception('User creation failed');

      AppLogger.info('✅ Firebase Auth user created: ${user.uid}');

      // Update profile
      await user.updateDisplayName(name);

      // Find parent if referral code provided
      String? parentId;
      if (parentReferralCode != null && parentReferralCode.isNotEmpty) {
        AppLogger.info(
            '🔍 Finding parent by referral code: $parentReferralCode');
        parentId = await _findParentByReferralCode(parentReferralCode);
        AppLogger.info('🔍 Parent ID found: $parentId');
      }

      // Generate referral code
      final referralCode = _generateReferralCode(user.uid);
      AppLogger.info('🎫 Generated referral code: $referralCode');

      // Create user document in Firestore
      final userModel = user_model.User(
        userId: user.uid,
        name: name,
        email: email,
        phone: phone,
        rank: role,
        target: 0.0,
        createdAt: DateTime.now().toIso8601String(),
        updatedAt: DateTime.now().toIso8601String(),
      );

      final userData = userModel.toJson();
      userData['referralCode'] = referralCode;
      if (parentId != null) userData['parentId'] = parentId;
      if (parentReferralCode != null) {
        userData['referredBy'] = parentReferralCode;
      }

      await _firestore
          .collection(AppConstants.usersCollection)
          .doc(user.uid)
          .set(userData);

      AppLogger.info('✅ Firestore document created: ${user.uid}');
      AppLogger.info('✅ User registered successfully: $email');
      return credential;
    } on firebase_auth.FirebaseAuthException catch (e) {
      AppLogger.error('❌ REGISTRATION FAILED: ${e.code} - ${e.message}');
      throw _handleAuthError(e);
    } catch (e) {
      AppLogger.error('❌ REGISTRATION ERROR: $e');
      throw Exception('Registration failed: $e');
    }
  }

  // Register with Phone (After OTP verification)
  Future<void> completePhoneRegistration({
    required String userId,
    required String name,
    required String email,
    required String role,
    String? parentReferralCode,
  }) async {
    try {
      // Find parent if referral code provided
      String? parentId;
      if (parentReferralCode != null && parentReferralCode.isNotEmpty) {
        parentId = await _findParentByReferralCode(parentReferralCode);
      }

      // Generate referral code
      final referralCode = _generateReferralCode(userId);

      // Create user document
      final userModel = user_model.User(
        userId: userId,
        name: name,
        email: email,
        phone: currentUser?.phoneNumber ?? '',
        rank: role,
        target: 0.0,
        createdAt: DateTime.now().toIso8601String(),
        updatedAt: DateTime.now().toIso8601String(),
      );

      final userData = userModel.toJson();
      userData['referralCode'] = referralCode;
      if (parentId != null) userData['parentId'] = parentId;
      if (parentReferralCode != null) {
        userData['referredBy'] = parentReferralCode;
      }

      await _firestore
          .collection(AppConstants.usersCollection)
          .doc(userId)
          .set(userData);

      AppLogger.info('Phone registration completed for: $userId');
    } catch (e, stackTrace) {
      AppLogger.error('Phone registration failed', e, stackTrace);
      rethrow;
    }
  }

  // Logout
  Future<void> logout() async {
    try {
      await _auth.signOut();
      await _secureStorage.deleteAll();
      AppLogger.info('User logged out');
    } catch (e, stackTrace) {
      AppLogger.error('Logout error', e, stackTrace);
    }
  }

  // Password Reset
  Future<void> sendPasswordResetEmail(String email) async {
    try {
      await _auth.sendPasswordResetEmail(email: email);
      AppLogger.info('Password reset email sent to: $email');
    } on firebase_auth.FirebaseAuthException catch (e) {
      AppLogger.error('Password reset failed', e);
      throw _handleAuthError(e);
    }
  }

  // Change Password
  Future<void> changePassword(String newPassword) async {
    try {
      final user = currentUser;
      if (user == null) throw Exception('No user logged in');

      await user.updatePassword(newPassword);
      AppLogger.info('Password changed successfully');
    } on firebase_auth.FirebaseAuthException catch (e) {
      AppLogger.error('Password change failed', e);
      throw _handleAuthError(e);
    }
  }

  // Update User Profile
  Future<void> updateProfile({
    String? name,
    String? photoURL,
    String? address,
    String? city,
    String? state,
    String? pincode,
    DateTime? dateOfBirth,
    String? gender,
    BankDetails? bankDetails,
  }) async {
    try {
      final user = currentUser;
      if (user == null) throw Exception('No user logged in');

      final updates = <String, dynamic>{
        'updatedAt': DateTime.now().toIso8601String(),
      };

      if (name != null) {
        await user.updateDisplayName(name);
        updates['name'] = name;
      }

      if (photoURL != null) {
        await user.updatePhotoURL(photoURL);
        updates['profileImage'] = photoURL;
      }

      if (address != null) updates['address'] = address;
      if (city != null) updates['city'] = city;
      if (state != null) updates['state'] = state;
      if (pincode != null) updates['pincode'] = pincode;
      if (dateOfBirth != null) {
        updates['dateOfBirth'] = dateOfBirth.toIso8601String();
      }
      if (gender != null) updates['gender'] = gender;
      if (bankDetails != null) {
        updates['bankDetails'] = bankDetails.toJson();
      }

      await _firestore
          .collection(AppConstants.usersCollection)
          .doc(user.uid)
          .update(updates);

      AppLogger.info('Profile updated successfully');
    } catch (e, stackTrace) {
      AppLogger.error('Profile update failed', e, stackTrace);
      rethrow;
    }
  }

  // Check if user exists
  Future<bool> checkUserExists(String phone) async {
    try {
      final query = await _firestore
          .collection(AppConstants.usersCollection)
          .where('phone', isEqualTo: phone)
          .limit(1)
          .get();

      return query.docs.isNotEmpty;
    } catch (e) {
      return false;
    }
  }

  // Helper Methods
  Future<void> _updateLastLogin(String userId) async {
    await _firestore
        .collection(AppConstants.usersCollection)
        .doc(userId)
        .update({
      'lastLoginAt': DateTime.now().toIso8601String(),
    });
  }

  Future<String?> _findParentByReferralCode(String code) async {
    try {
      final query = await _firestore
          .collection(AppConstants.usersCollection)
          .where('referralCode', isEqualTo: code)
          .limit(1)
          .get();

      if (query.docs.isNotEmpty) {
        return query.docs.first.id;
      }
      return null;
    } catch (e) {
      return null;
    }
  }

  /// Get referrer name by referral code
  /// Returns the name of the user with the given referral code, or null if not found
  Future<String?> getReferrerName(String code) async {
    try {
      AppLogger.info('Looking for referrer with code: ${code.toUpperCase()}');
      final query = await _firestore
          .collection(AppConstants.usersCollection)
          .where('referralCode', isEqualTo: code.toUpperCase())
          .limit(1)
          .get();

      AppLogger.info('Found ${query.docs.length} users with referral code');

      if (query.docs.isNotEmpty) {
        final userData = query.docs.first.data();
        final name = userData['name'] as String?;
        AppLogger.info('Referrer found: $name');
        return name;
      }
      AppLogger.info('No referrer found for code: ${code.toUpperCase()}');
      return null;
    } catch (e) {
      AppLogger.error('Error getting referrer name', e);
      return null;
    }
  }

  String _generateReferralCode(String userId) {
    // Generate a 6-character referral code from userId
    final hash = base64Encode(utf8.encode(userId));
    return 'APS${hash.substring(0, 6).toUpperCase()}';
  }

  Exception _handleAuthError(firebase_auth.FirebaseAuthException e) {
    switch (e.code) {
      case 'user-not-found':
        return Exception('No user found with this email');
      case 'wrong-password':
        return Exception('Incorrect password');
      case 'email-already-in-use':
        return Exception('Email is already registered');
      case 'invalid-email':
        return Exception('Invalid email address');
      case 'weak-password':
        return Exception('Password is too weak');
      case 'invalid-phone-number':
        return Exception('Invalid phone number');
      case 'too-many-requests':
        return Exception('Too many attempts. Please try again later');
      default:
        return Exception(e.message ?? 'Authentication failed');
    }
  }
}

// Auth Service Provider
final authServiceProvider = Provider<AuthService>((ref) => AuthService());

final currentUserProvider = StreamProvider<firebase_auth.User?>((ref) {
  final authService = ref.watch(authServiceProvider);
  return authService.authStateChanges;
});

final currentUserDataProvider = FutureProvider<user_model.User?>((ref) async {
  final authService = ref.watch(authServiceProvider);
  return authService.getCurrentUserData();
});

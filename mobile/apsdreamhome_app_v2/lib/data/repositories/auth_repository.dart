import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../../core/services/api_service.dart';
import '../../core/services/database_helper.dart';
import '../models/user_model.dart';

/// Auth Repository - Handles authentication & user data
class AuthRepository {
  final ApiService _apiService;
  final DatabaseHelper _dbHelper;
  final FlutterSecureStorage _secureStorage;

  AuthRepository(
    this._apiService,
    this._dbHelper,
    this._secureStorage,
  );

  /// Login with email/password
  Future<UserModel> login(String email, String password) async {
    try {
      final response = await _apiService.login(email, password);

      if (response['success'] != true) {
        throw Exception(response['message'] ?? 'Login failed');
      }

      final userData = response['data']['user'] as Map<String, dynamic>;
      final token = response['data']['token'] as String;

      // Save token
      await _apiService.saveToken(token);
      await _secureStorage.write(key: 'auth_token', value: token);

      // Create user model
      final user = UserModel.fromJson(userData);

      // Save user to local DB
      await _dbHelper.saveUser(user.toJson());
      await _secureStorage.write(
        key: 'current_user',
        value: user.toJson().toString(),
      );

      return user;
    } catch (e) {
      throw Exception('Login failed: $e');
    }
  }

  /// Login with Firebase (Phone OTP)
  Future<UserModel> loginWithFirebase(String firebaseUid, String phone) async {
    try {
      final response = await _apiService.post(
        '/auth/firebase-login',
        data: {
          'firebase_uid': firebaseUid,
          'phone': phone,
        },
      );

      if (response['success'] != true) {
        throw Exception(response['message'] ?? 'Firebase login failed');
      }

      final userData = response['data']['user'] as Map<String, dynamic>;
      final token = response['data']['token'] as String;

      await _apiService.saveToken(token);

      final user = UserModel.fromJson(userData);
      await _dbHelper.saveUser(user.toJson());

      return user;
    } catch (e) {
      throw Exception('Firebase login failed: $e');
    }
  }

  /// Register new user
  Future<UserModel> register({
    required String name,
    required String email,
    required String phone,
    required String password,
    String? role,
  }) async {
    try {
      final response = await _apiService.post(
        '/auth/register',
        data: {
          'name': name,
          'email': email,
          'phone': phone,
          'password': password,
          'role': role ?? 'customer',
        },
      );

      if (response['success'] != true) {
        throw Exception(response['message'] ?? 'Registration failed');
      }

      // Auto login after registration
      return await login(email, password);
    } catch (e) {
      throw Exception('Registration failed: $e');
    }
  }

  /// Logout user
  Future<void> logout() async {
    try {
      // Call API logout (if online)
      if (await _apiService.isConnected()) {
        await _apiService.post('/auth/logout');
      }
    } catch (e) {
      // Ignore API errors on logout
    } finally {
      // Clear local data
      await _apiService.logout();
      await _secureStorage.deleteAll();
      await _dbHelper.clearUserData();
    }
  }

  /// Get current user
  Future<UserModel?> getCurrentUser() async {
    // Try local SQLite first
    final localUser = await _dbHelper.getCurrentUser();
    if (localUser != null) {
      return UserModel.fromJson(localUser);
    }

    // Check for token — skip API if no token or demo token
    final token = await _apiService.getToken();
    if (token == null || token.isEmpty || token.startsWith('demo_')) {
      return null;
    }

    // If online, fetch from API (real token only)
    if (await _apiService.isConnected()) {
      try {
        final response = await _apiService.getProfile();
        final user =
            UserModel.fromJson(response['data'] as Map<String, dynamic>);
        await _dbHelper.saveUser(user.toJson());
        return user;
      } catch (e) {
        return null;
      }
    }

    return null;
  }

  /// Update user profile
  Future<UserModel> updateProfile({
    String? name,
    String? email,
    String? phone,
    String? profileImage,
  }) async {
    final data = <String, dynamic>{};
    if (name != null) data['name'] = name;
    if (email != null) data['email'] = email;
    if (phone != null) data['phone'] = phone;
    if (profileImage != null) data['profile_image'] = profileImage;

    try {
      final response = await _apiService.put(
        '/auth/profile',
        data: data,
      );

      final user = UserModel.fromJson(response['data'] as Map<String, dynamic>);
      await _dbHelper.saveUser(user.toJson());

      return user;
    } catch (e) {
      // Queue for sync if offline
      if (!(await _apiService.isConnected())) {
        await _dbHelper.addToSyncQueue(
          entityType: 'user_profile',
          entityId: 'current',
          action: 'update',
          data: data,
        );
      }
      throw Exception('Update failed: $e');
    }
  }

  /// Quick demo login (no API needed)
  Future<UserModel> demoLogin(String role) async {
    final now = DateTime.now().toIso8601String();
    final demoUsers = {
      'customer': {
        'userId': '3',
        'name': 'Customer One',
        'email': 'customer1@apsdreamhome.com',
        'phone': '9999999991',
        'rank': 'Customer',
        'target': 0.0,
        'avatar': null,
        'createdAt': now,
        'updatedAt': now,
      },
      'associate': {
        'userId': '9',
        'name': 'Test Emp',
        'email': 'associate@apsdreamhome.com',
        'phone': '9999999992',
        'rank': 'Associate',
        'target': 1000000.0,
        'avatar': null,
        'createdAt': now,
        'updatedAt': now,
      },
      'admin': {
        'userId': '1',
        'name': 'Admin',
        'email': 'admin@apsdreamhome.com',
        'phone': '9999999990',
        'rank': 'Admin',
        'target': 0.0,
        'avatar': null,
        'createdAt': now,
        'updatedAt': now,
      },
    };

    final userData = demoUsers[role] ?? demoUsers['customer']!;
    final user = UserModel.fromJson(userData);

    await _secureStorage.write(key: 'auth_token', value: 'demo_token_$role');
    await _secureStorage.write(key: 'current_user', value: user.toJson().toString());
    await _dbHelper.saveUser(user.toJson());

    return user;
  }

  /// Check if user is logged in
  Future<bool> isLoggedIn() async {
    final token = await _apiService.getToken();
    return token != null && token.isNotEmpty;
  }

  /// Refresh token
  Future<String?> refreshToken() async {
    try {
      final response = await _apiService.post('/auth/refresh');
      final newToken = response['data']['token'] as String;
      await _apiService.saveToken(newToken);
      return newToken;
    } catch (e) {
      return null;
    }
  }

  /// Change password
  Future<void> changePassword(
    String currentPassword,
    String newPassword,
  ) async {
    await _apiService.post(
      '/auth/change-password',
      data: {
        'current_password': currentPassword,
        'new_password': newPassword,
      },
    );
  }

  /// Forgot password
  Future<void> forgotPassword(String email) async {
    await _apiService.post(
      '/auth/forgot-password',
      data: {'email': email},
    );
  }

  /// Verify OTP
  Future<bool> verifyOtp(String phone, String otp) async {
    final response = await _apiService.post(
      '/auth/verify-otp',
      data: {
        'phone': phone,
        'otp': otp,
      },
    );
    return response['success'] == true;
  }

  /// Resend OTP
  Future<void> resendOtp(String phone) async {
    await _apiService.post(
      '/auth/resend-otp',
      data: {'phone': phone},
    );
  }

  /// Get user role
  Future<String?> getUserRole() async {
    final user = await getCurrentUser();
    return user?.role;
  }

  /// Check if user has role
  Future<bool> hasRole(String role) async {
    final userRole = await getUserRole();
    return userRole == role;
  }
}

/// Provider for AuthRepository
final authRepositoryProvider = Provider<AuthRepository>((ref) {
  final apiService = ApiService();
  final dbHelper = DatabaseHelper();
  const secureStorage = FlutterSecureStorage();
  return AuthRepository(apiService, dbHelper, secureStorage);
});

/// Provider for current user
final currentUserProvider = FutureProvider.autoDispose<UserModel?>((ref) async {
  final repository = ref.watch(authRepositoryProvider);
  return await repository.getCurrentUser();
});

/// Provider for auth state
final authStateProvider = StateNotifierProvider<AuthStateNotifier, AuthState>(
  (ref) => AuthStateNotifier(ref.watch(authRepositoryProvider)),
);

/// Auth State
class AuthState {
  final UserModel? user;
  final bool isLoading;
  final String? error;

  const AuthState({
    this.user,
    this.isLoading = false,
    this.error,
  });

  bool get isAuthenticated => user != null;
}

/// Auth State Notifier
class AuthStateNotifier extends StateNotifier<AuthState> {
  final AuthRepository _repository;

  AuthStateNotifier(this._repository) : super(const AuthState()) {
    _init();
  }

  Future<void> _init() async {
    state = const AuthState(isLoading: true);
    try {
      final user = await _repository.getCurrentUser();
      state = AuthState(user: user);
    } catch (e) {
      state = AuthState(error: e.toString());
    }
  }

  Future<void> login(String email, String password) async {
    state = const AuthState(isLoading: true);
    try {
      final user = await _repository.login(email, password);
      state = AuthState(user: user);
    } catch (e) {
      state = AuthState(error: e.toString());
    }
  }

  Future<void> logout() async {
    state = const AuthState(isLoading: true);
    await _repository.logout();
    state = const AuthState();
  }

  Future<void> updateProfile({
    String? name,
    String? email,
    String? phone,
  }) async {
    if (state.user == null) return;

    state = AuthState(user: state.user, isLoading: true);
    try {
      final user = await _repository.updateProfile(
        name: name,
        email: email,
        phone: phone,
      );
      state = AuthState(user: user);
    } catch (e) {
      state = AuthState(user: state.user, error: e.toString());
    }
  }
}

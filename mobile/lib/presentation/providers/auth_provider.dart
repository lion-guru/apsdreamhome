import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../core/services/api_service.dart';
import '../core/services/database_helper.dart';
import '../core/constants/app_constants.dart';
import '../core/errors/failures.dart';
import '../data/models/user_model.dart';

// Providers
final secureStorageProvider = Provider<FlutterSecureStorage>((ref) => const FlutterSecureStorage());

final authProvider = StateNotifierProvider<AuthNotifier, AsyncValue<User?>>((ref) {
  return AuthNotifier(ref.watch(secureStorageProvider));
});

class AuthNotifier extends StateNotifier<AsyncValue<User?>> {
  AuthNotifier(this._secureStorage) : super(const AsyncValue.loading()) {
    _initializeAuth();
  }
  
  final FlutterSecureStorage _secureStorage;
  final ApiService _apiService = ApiService();
  
  Future<void> _initializeAuth() async {
    try {
      final token = await _secureStorage.read(key: AppConstants.tokenKey);
      final userId = await _secureStorage.read(key: AppConstants.userIdKey);
      
      if (token != null && userId != null) {
        // Load user from local database
        final users = await DatabaseHelper.query(
          AppConstants.usersTable,
          where: 'user_id = ?',
          whereArgs: [userId],
        );
        
        if (users.isNotEmpty) {
          final user = User.fromJson(users.first);
          state = AsyncValue.data(user);
        } else {
          // Try to fetch from server
          await _refreshUserProfile();
        }
      } else {
        state = const AsyncValue.data(null);
      }
    } catch (e) {
      state = AsyncValue.error(e, StackTrace.current);
    }
  }
  
  Future<void> login(String email, String password) async {
    state = const AsyncValue.loading();
    
    try {
      final response = await _apiService.login(email, password);
      
      if (response['success'] == true && response['data'] != null) {
        final userData = response['data'] as Map<String, dynamic>;
        final token = response['token'] as String;
        final user = User.fromJson(userData);
        
        // Save token and user data
        await _secureStorage.write(key: AppConstants.tokenKey, value: token);
        await _secureStorage.write(key: AppConstants.userIdKey, value: user.userId);
        await _secureStorage.write(key: AppConstants.userProfileKey, value: user.toJson().toString());
        
        // Save user to local database
        await DatabaseHelper.insert(AppConstants.usersTable, {
          'user_id': user.userId,
          'name': user.name,
          'email': user.email,
          'phone': user.phone,
          'rank': user.rank,
          'target': user.target,
          'avatar': user.avatar,
          'created_at': user.createdAt,
          'updated_at': user.updatedAt,
        });
        
        state = AsyncValue.data(user);
      } else {
        throw AuthenticationFailure(response['message'] ?? 'Login failed');
      }
    } catch (e) {
      state = AsyncValue.error(e, StackTrace.current);
    }
  }
  
  Future<void> logout() async {
    try {
      await _apiService.logout();
    } catch (e) {
      // Continue with logout even if API call fails
      print('Logout API call failed: $e');
    }
    
    // Clear local storage
    await _secureStorage.delete(key: AppConstants.tokenKey);
    await _secureStorage.delete(key: AppConstants.userIdKey);
    await _secureStorage.delete(key: AppConstants.userProfileKey);
    
    // Clear local database
    await DatabaseHelper.delete(AppConstants.usersTable);
    
    state = const AsyncValue.data(null);
  }
  
  Future<void> _refreshUserProfile() async {
    try {
      final response = await _apiService.getProfile();
      
      if (response['success'] == true && response['data'] != null) {
        final userData = response['data'] as Map<String, dynamic>;
        final user = User.fromJson(userData);
        
        // Update local database
        await DatabaseHelper.insert(AppConstants.usersTable, {
          'user_id': user.userId,
          'name': user.name,
          'email': user.email,
          'phone': user.phone,
          'rank': user.rank,
          'target': user.target,
          'avatar': user.avatar,
          'created_at': user.createdAt,
          'updated_at': user.updatedAt,
        });
        
        state = AsyncValue.data(user);
      }
    } catch (e) {
      // If refresh fails, keep current state
      print('Failed to refresh user profile: $e');
    }
  }
  
  Future<void> updateProfile(User user) async {
    try {
      // Update local database
      await DatabaseHelper.update(
        AppConstants.usersTable,
        {
          'name': user.name,
          'email': user.email,
          'phone': user.phone,
          'avatar': user.avatar,
          'updated_at': DateTime.now().toIso8601String(),
        },
        where: 'user_id = ?',
        whereArgs: [user.userId],
      );
      
      // Update state
      state = AsyncValue.data(user);
      
      // TODO: Update on server when API is available
    } catch (e) {
      state = AsyncValue.error(e, StackTrace.current);
    }
  }
  
  bool get isAuthenticated => state.maybeWhen(
    data: (user) => user != null,
    orElse: () => false,
  );
  
  User? get currentUser => state.maybeWhen(
    data: (user) => user,
    orElse: () => null,
  );
}

// Theme provider
final themeProvider = StateProvider<bool>((ref) => false); // false = light theme

// Connectivity provider
final connectivityProvider = StreamProvider<bool>((ref) async* {
  final connectivity = Connectivity();
  await for (final result in connectivity.onConnectivityChanged) {
    yield result != ConnectivityResult.none;
  }
});

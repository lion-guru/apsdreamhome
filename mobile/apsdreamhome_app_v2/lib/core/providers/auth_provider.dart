import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_riverpod/legacy.dart';
import '../../core/utils/logger.dart';
import '../../data/repositories/auth_repository.dart';
import '../../data/models/user_model.dart';

class AuthNotifier extends StateNotifier<User?> {
  final AuthRepository _repository;

  AuthNotifier(this._repository) : super(null) {
    _loadInitialUser();
  }

  Future<void> _loadInitialUser() async {
    try {
      final user = await _repository.getCurrentUser();
      if (mounted) state = user;
    } catch (_) {
      if (mounted) state = null;
    }
  }

  Future<String?> getToken() async {
    return await _repository.refreshToken();
  }

  Future<User> login(String email, String password) async {
    state = null;
    try {
      AppLogger.info('[AuthNotifier] Starting login...');
      final user = await _repository.login(email, password);
      state = user;
      AppLogger.info(
        '[AuthNotifier] Login successful: userId=${user.userId}, rank=${user.rank}',
      );
      return user;
    } catch (e, stackTrace) {
      state = null;
      AppLogger.error('[AuthNotifier] Login failed', e, stackTrace);
      rethrow;
    }
  }

  Future<void> requestAirLoginOtp(String identifier) async {
    try {
      await _repository.requestAirLoginOtp(identifier);
    } catch (e) {
      rethrow;
    }
  }

  Future<UserModel> verifyAirLoginOtp(String otp) async {
    state = null;
    try {
      final token = await _repository.verifyAirLoginOtp(otp);
      final user = await _repository.getCurrentUser();
      if (user != null) {
        state = user;
      }
      return user!;
    } catch (e) {
      rethrow;
    }
  }

  Future<void> logout() async {
    await _repository.logout();
    state = null;
    AppLogger.info('[AuthNotifier] Logged out');
  }
}

final authProvider = StateNotifierProvider<AuthNotifier, User?>((ref) {
  final repository = ref.watch(authRepositoryProvider);
  return AuthNotifier(repository);
});

final userDataProvider = FutureProvider<User?>((ref) async {
  final repository = ref.watch(authRepositoryProvider);
  return await repository.getCurrentUser();
});

final currentUserDataProvider = FutureProvider<User?>((ref) async {
  final repository = ref.watch(authRepositoryProvider);
  return await repository.getCurrentUser();
});

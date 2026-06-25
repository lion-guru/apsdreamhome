import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/utils/logger.dart';
import '../../data/repositories/auth_repository.dart';
import '../../data/models/user_model.dart';

class AuthNotifier extends StateNotifier<User?> {
  final AuthRepository _repository;

  AuthNotifier(this._repository, Ref ref) : super(null) {
    // Read initial state directly (no fireImmediately — it fires during
    // provider creation and triggers _dependents.isEmpty assertion when
    // appRouterProvider tries to rebuild while widgets still depend on it).
    try {
      final initial = ref.read(authStateProvider);
      state = initial.user;
    } catch (_) {
      state = null;
    }

    // Listen for future changes only (no fireImmediately)
    ref.listen<AuthState>(authStateProvider, (previous, next) {
      if (mounted) state = next.user;
    });
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
      AppLogger.info('[AuthNotifier] Login successful: userId=${user.userId}, rank=${user.rank}');
      return user;
    } catch (e, stackTrace) {
      state = null;
      AppLogger.error('[AuthNotifier] Login failed', e, stackTrace);
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
  return AuthNotifier(repository, ref);
});

final userDataProvider = FutureProvider<User?>((ref) async {
  final repository = ref.watch(authRepositoryProvider);
  return await repository.getCurrentUser();
});

final currentUserDataProvider = FutureProvider<User?>((ref) async {
  final repository = ref.watch(authRepositoryProvider);
  return await repository.getCurrentUser();
});

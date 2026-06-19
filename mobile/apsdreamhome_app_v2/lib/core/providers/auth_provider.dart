import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../data/repositories/auth_repository.dart';
import '../../data/models/user_model.dart';

class AuthNotifier extends StateNotifier<User?> {
  final AuthRepository _repository;

  AuthNotifier(this._repository, Ref ref) : super(null) {
    // Listen to AuthState updates from auth_repository.dart
    ref.listen<AuthState>(authStateProvider, (previous, next) {
      state = next.user;
    }, fireImmediately: true);
  }

  Future<String?> getToken() async {
    return await _repository.refreshToken();
  }

  Future<void> logout() async {
    await _repository.logout();
    state = null;
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

import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../data/services/auth_service.dart';
import '../../data/models/user_model.dart';

class AuthNotifier extends StateNotifier<User?> {
  final AuthService _authService;

  AuthNotifier(this._authService) : super(null) {
    _authService.authStateChanges.listen((firebaseUser) {
      if (firebaseUser != null) {
        _authService.getCurrentUserData().then((user) {
          state = user;
        });
      } else {
        state = null;
      }
    });
  }

  Future<String?> getToken() async {
    try {
      final user = _authService.currentUser;
      final token = await user?.getIdToken();
      return token;
    } catch (e) {
      return null;
    }
  }

  Future<void> logout() async {
    await _authService.logout();
    state = null;
  }
}

final authProvider = StateNotifierProvider<AuthNotifier, User?>((ref) {
  final authService = ref.watch(authServiceProvider);
  return AuthNotifier(authService);
});

final userDataProvider = FutureProvider<User?>((ref) async {
  final authService = ref.watch(authServiceProvider);
  return authService.getCurrentUserData();
});

import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../data/services/auth_service.dart';

final authProvider = StreamProvider((ref) {
  final authService = ref.watch(authServiceProvider);
  return authService.authStateChanges;
});

final userDataProvider = FutureProvider((ref) async {
  final authService = ref.watch(authServiceProvider);
  return authService.getCurrentUserData();
});

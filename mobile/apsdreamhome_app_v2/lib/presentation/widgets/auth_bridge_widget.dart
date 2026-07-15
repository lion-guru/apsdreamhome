import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/providers/auth_provider.dart';
import '../../core/router/app_router.dart';
import '../../data/models/user_model.dart';

/// Bridges Riverpod authProvider → AuthBridge (ValueNotifier) for GoRouter.
/// This is the ONLY widget that depends on Riverpod auth state.
/// APSDreamHomeApp itself is Riverpod-free, avoiding the InheritedModel assertion.
class AuthBridgeWidget extends ConsumerStatefulWidget {
  final Widget child;
  const AuthBridgeWidget({super.key, required this.child});

  @override
  ConsumerState<AuthBridgeWidget> createState() => _AuthBridgeWidgetState();
}

class _AuthBridgeWidgetState extends ConsumerState<AuthBridgeWidget> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      final user = ref.read(authProvider);
      if (user != null) {
        AuthBridge.instance.currentUser.value = user;
      }

      ref.listen<User?>(authProvider, (previous, next) {
        if (AuthBridge.instance.currentUser.value == null && next != null) {
          return;
        }
        AuthBridge.instance.currentUser.value = next;
      });
    });
  }

  @override
  Widget build(BuildContext context) => widget.child;
}

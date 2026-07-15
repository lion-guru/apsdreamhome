import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import 'core/router/app_router.dart';
import 'core/services/notification_service.dart';
import 'core/theme/app_theme.dart';

/// Global navigator key for deep-link navigation from notifications
final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

/// Timestamp of last back press for double-tap-to-exit
DateTime? _lastBackPress;

class APSDreamHomeApp extends ConsumerStatefulWidget {
  const APSDreamHomeApp({super.key});

  @override
  ConsumerState<APSDreamHomeApp> createState() => _APSDreamHomeAppState();
}

class _APSDreamHomeAppState extends ConsumerState<APSDreamHomeApp> {
  @override
  void initState() {
    super.initState();
    setNavigatorKey(navigatorKey);
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp.router(
      key: navigatorKey,
      title: 'APS Dream Home',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.lightTheme,
      darkTheme: AppTheme.darkTheme,
      themeMode: ThemeMode.light,
      routerConfig: getRouter(),
      builder: (context, child) {
        return PopScope(
          canPop: false,
          onPopInvokedWithResult: (didPop, result) {
            if (didPop) return;

            final router = getRouter();
            final currentRoute = router.routerDelegate.currentConfiguration.uri
                .toString();

            // If on /home or /splash or /login, double-tap to exit
            if (currentRoute == '/home' ||
                currentRoute == '/splash' ||
                currentRoute == '/login') {
              final now = DateTime.now();
              if (_lastBackPress != null &&
                  now.difference(_lastBackPress!) <
                      const Duration(seconds: 2)) {
                SystemNavigator.pop();
              } else {
                _lastBackPress = now;
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('Press back again to exit'),
                    duration: Duration(seconds: 1),
                  ),
                );
              }
            } else {
              // Navigate back using GoRouter
              context.pop();
            }
          },
          child: child ?? const SizedBox.shrink(),
        );
      },
    );
  }
}

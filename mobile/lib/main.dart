import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import 'core/router/app_router.dart';
import 'core/theme/app_theme.dart';
import 'core/services/sync_service.dart';
import 'presentation/providers/auth_provider.dart';
import 'presentation/providers/sync_provider.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Initialize secure storage
  const secureStorage = FlutterSecureStorage();
  
  // Initialize sync service
  await SyncService.initialize();
  
  runApp(
    ProviderScope(
      overrides: [
        secureStorageProvider.overrideWithValue(secureStorage),
      ],
      child: const APSDreamHomeApp(),
    ),
  );
}

class APSDreamHomeApp extends ConsumerWidget {
  const APSDreamHomeApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final router = ref.watch(appRouterProvider);
    final isDarkMode = ref.watch(themeProvider);
    
    return MaterialApp.router(
      title: 'APS Dream Home',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.lightTheme,
      darkTheme: AppTheme.darkTheme,
      themeMode: isDarkMode ? ThemeMode.dark : ThemeMode.light,
      routerConfig: router,
    );
  }
}

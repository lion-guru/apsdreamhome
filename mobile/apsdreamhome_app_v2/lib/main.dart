import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import 'app.dart';
import 'core/utils/logger.dart';
import 'data/services/auth_service.dart';
import 'core/services/api_service.dart';
import 'core/services/database_helper.dart';
import 'data/repositories/property_repository.dart';
import 'data/repositories/auth_repository.dart';
import 'presentation/providers/property_providers.dart';


void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Initialize logging
  AppLogger.setup();

  // Demo mode enabled for testing
  AuthService.setDemoMode(true);
  AppLogger.info('Demo mode enabled for testing');

  try {
    // Initialize services
    final apiService = ApiService();
    final dbHelper = DatabaseHelper();
    await apiService.initialize();

    // Auto-demo-login if no stored user (first launch in demo mode)
    const secureStorage = FlutterSecureStorage();
    final existingToken = await secureStorage.read(key: 'auth_token');
    if (existingToken == null || existingToken.isEmpty) {
      AppLogger.info('Demo mode: no stored user, auto-logging in as customer');
      final authRepo = AuthRepository(apiService, dbHelper, secureStorage);
      await authRepo.demoLogin('customer');
    }

    // Run app with Riverpod and repository overrides
    runApp(
      ProviderScope(
        overrides: [
          propertyRepositoryProvider.overrideWithValue(
            PropertyRepository(apiService, dbHelper),
          ),
          authRepositoryProvider.overrideWithValue(
            AuthRepository(apiService, dbHelper, secureStorage),
          ),
        ],
        child: const APSDreamHomeApp(),
      ),
    );
  } catch (e, stackTrace) {
    AppLogger.error('App initialization failed', e, stackTrace);
    runApp(
      MaterialApp(
        home: Scaffold(
          body: Center(
            child: Text('Failed to initialize app: $e'),
          ),
        ),
      ),
    );
  }
}

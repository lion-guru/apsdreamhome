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

  // Demo mode disabled - using real API Auth
  AuthService.setDemoMode(false);
  AppLogger.info('Demo mode disabled - using PHP API Auth');

  try {
    // Initialize services
    final apiService = ApiService();
    final dbHelper = DatabaseHelper();
    await apiService.initialize();

    // Run app with Riverpod and repository overrides
    const secureStorage = FlutterSecureStorage();
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

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:flutter_dotenv/flutter_dotenv.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
// import 'package:hive_flutter/hive_flutter.dart'; // Incompatible with Flutter 3.41.6

import 'app.dart';
import 'core/utils/logger.dart';
import 'firebase_options.dart';
import 'data/services/auth_service.dart';
import 'core/services/api_service.dart';
import 'core/services/database_helper.dart';
import 'data/repositories/property_repository.dart';
import 'data/repositories/auth_repository.dart';
import 'data/repositories/booking_repository.dart';
import 'data/repositories/lead_repository.dart';
import 'data/repositories/mlm_repository.dart';
import 'presentation/providers/property_providers.dart';


void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Load environment variables
  try {
    await dotenv.load(fileName: '.env');
    AppLogger.info('Environment variables loaded');
  } catch (e) {
    AppLogger.warning(
        'No .env file found - running without environment variables');
  }

  // Initialize logging
  AppLogger.setup();

  // Demo mode disabled - using real Firebase Auth
  AuthService.setDemoMode(false);
  AppLogger.info('Demo mode disabled - using Firebase Auth');

  try {
    // Initialize Firebase
    await Firebase.initializeApp(
      options: DefaultFirebaseOptions.currentPlatform,
    );
    AppLogger.info('Firebase initialized successfully');

    // Initialize Hive for offline storage - Disabled due to Flutter 3.41.6 incompatibility
    // await Hive.initFlutter();
    // await Hive.openBox('app_cache');
    // await Hive.openBox('offline_data');
    // AppLogger.info('Hive initialized successfully');

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

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:firebase_core/firebase_core.dart';

import 'app.dart';
import 'firebase_options.dart';
import 'core/utils/logger.dart';
import 'core/services/api_service.dart';
import 'core/services/database_helper.dart';
import 'core/services/notification_service.dart';
import 'data/repositories/property_repository.dart';
import 'data/repositories/auth_repository.dart';
import 'presentation/providers/property_providers.dart';


void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Initialize Firebase — graceful fallback when Google Play Services unavailable (emulators)
  try {
    await Firebase.initializeApp(options: DefaultFirebaseOptions.currentPlatform);
  } catch (e) {
    AppLogger.warning('Firebase init failed (emulator/GMS missing): $e — continuing without Firebase');
  }

  // Initialize logging
  AppLogger.setup();

  try {
    // Initialize services
    final apiService = ApiService();
    final dbHelper = DatabaseHelper();
    await apiService.initialize();

    // Pre-initialize database so providers don't block on first access
    await dbHelper.database;

    // Initialize FCM notification service (after ApiService so token can be saved)
    // Skip if Firebase is unavailable (emulator without GMS)
    try {
      final notificationService = NotificationService();
      await notificationService.initialize();
    } catch (e) {
      AppLogger.warning('NotificationService init failed (FCM unavailable): $e');
    }

    const secureStorage = FlutterSecureStorage();

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

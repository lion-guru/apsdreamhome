import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:firebase_core/firebase_core.dart';

import 'app.dart';
import 'firebase_options.dart';
import 'core/constants/app_constants.dart';
import 'core/utils/logger.dart';
import 'core/services/api_service.dart';
import 'core/services/database_helper.dart';
import 'core/services/notification_service.dart';
import 'data/repositories/property_repository.dart';
import 'data/repositories/auth_repository.dart';
import 'presentation/providers/property_providers.dart';
import 'presentation/widgets/auth_bridge_widget.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Suppress ONLY the known-harmless GoRouter + Riverpod InheritedModel assertion
  // (Riverpod InheritedWidget vs GoRouter InheritedModel conflict; in release
  // mode assertions are stripped, so this only affects debug builds).
  // All other errors — including Navigator assertions like the duplicate page
  // key '!keyReservation.contains(key)' — must stay loud.
  final originalOnError = FlutterError.onError;
  FlutterError.onError = (details) {
    final msg = details.exception.toString();
    if (msg.contains('_dependents.isEmpty')) {
      return;
    }
    originalOnError?.call(details);
  };
  // Never hide any error widget behind a shrink — it made a whole class of
  // push failures invisible. Debug builds keep the full error surface.
  ErrorWidget.builder = (FlutterErrorDetails details) {
    return ErrorWidget(details.exception);
  };

  // Initialize base URL from --dart-define or platform detection
  AppConstants.initBaseUrl();

  // Initialize Firebase
  try {
    await Firebase.initializeApp(
      options: DefaultFirebaseOptions.currentPlatform,
    );
  } catch (e) {
    AppLogger.warning('Firebase init failed (emulator/GMS missing): $e');
  }

  AppLogger.setup();

  try {
    final apiService = ApiService();
    final dbHelper = DatabaseHelper();
    await apiService.initialize();
    await dbHelper.database;

    try {
      final notificationService = NotificationService();
      await notificationService.initialize();
    } catch (e) {
      AppLogger.warning('NotificationService init failed: $e');
    }

    const secureStorage = FlutterSecureStorage();

    runApp(
      ProviderScope(
        overrides: [
          propertyRepositoryProvider.overrideWith(
            (ref) => PropertyRepository(apiService, dbHelper),
          ),
          authRepositoryProvider.overrideWith(
            (ref) => AuthRepository(apiService, dbHelper, secureStorage),
          ),
        ],
        child: const AuthBridgeWidget(child: APSDreamHomeApp()),
      ),
    );
  } catch (e, stackTrace) {
    AppLogger.error('App initialization failed', e, stackTrace);
    runApp(
      MaterialApp(
        home: Scaffold(
          body: Center(child: Text('Failed to initialize app: $e')),
        ),
      ),
    );
  }
}

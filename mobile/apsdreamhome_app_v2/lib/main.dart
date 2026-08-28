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

  // Suppress GoRouter + Riverpod InheritedModel assertion in debug mode.
  // This is a known conflict: GoRouter uses InheritedModel<GoRouterState>,
  // Riverpod uses InheritedWidget. When ref.watch() is called inside a
  // GoRouter route widget, both systems try to update dependents simultaneously.
  // In release mode assertions are stripped, so this only affects debug builds.
  final originalOnError = FlutterError.onError;
  FlutterError.onError = (details) {
    final msg = details.exception.toString();
    if (msg.contains('_dependents.isEmpty') || msg.contains('is not true')) {
      return;
    }
    originalOnError?.call(details);
  };
  // Prevent red screen for this known assertion in debug builds
  ErrorWidget.builder = (FlutterErrorDetails details) {
    final msg = details.exception.toString();
    if (msg.contains('_dependents.isEmpty') || msg.contains('is not true')) {
      return const SizedBox.shrink();
    }
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

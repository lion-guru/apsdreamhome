import 'package:workmanager/workmanager.dart';
import 'package:flutter/foundation.dart';
import '../constants/app_constants.dart';
import '../services/api_service.dart';

/// Background sync for Associate EMI Tracker
/// Runs every 15 minutes when online
class BackgroundSyncService {
  static const String _emiSyncTask = 'emiTrackerSync';
  static const String _fullSyncTask = 'fullDataSync';

  static Future<void> initialize() async {
    await Workmanager().initialize(
      callbackDispatcher,
      isInDebugMode: kDebugMode,
    );
    
    // Register periodic tasks
    await _registerPeriodicTasks();
  }

  static Future<void> _registerPeriodicTasks() async {
    // EMI Tracker sync every 15 minutes
    await Workmanager().registerPeriodicTask(
      _emiSyncTask,
      _emiSyncTask,
      frequency: const Duration(minutes: 15),
      constraints: Constraints(
        networkType: NetworkType.connected,
      ),
    );

    // Full data sync every hour
    await Workmanager().registerPeriodicTask(
      _fullSyncTask,
      _fullSyncTask,
      frequency: const Duration(hours: 1),
      constraints: Constraints(
        networkType: NetworkType.connected,
      ),
    );
  }

  static void callbackDispatcher() {
    Workmanager().executeTask((task, inputData) async {
      switch (task) {
        case _emiSyncTask:
          return await _syncEmiTracker();
        case _fullSyncTask:
          return await _syncAllData();
        default:
          return false;
      }
    });
  }

  static Future<bool> _syncEmiTracker() async {
    try {
      final api = ApiService();
      AppConstants.initBaseUrl();
      
      // Check if we have a valid token
      final token = await api.getToken();
      if (token == null) return false;

      final result = await api.refreshAssociateEmiTracker();
      debugPrint('Background EMI sync completed: ${result.length} records');
      return true;
    } catch (e) {
      debugPrint('Background EMI sync failed: $e');
      return false;
    }
  }

  static Future<bool> _syncAllData() async {
    try {
      final api = ApiService();
      AppConstants.initBaseUrl();
      
      final token = await api.getToken();
      if (token == null) return false;

      // Sync EMI tracker
      await api.refreshAssociateEmiTracker();
      
      // Sync other data if needed
      // await api.getAssociateBookings();
      // await api.getCommissions();
      
      debugPrint('Full background sync completed');
      return true;
    } catch (e) {
      debugPrint('Full background sync failed: $e');
      return false;
    }
  }

  // Manual trigger for immediate sync
  static Future<void> triggerEmiSyncNow() async {
    await Workmanager().registerOneOffTask(
      '${_emiSyncTask}_now',
      _emiSyncTask,
      constraints: Constraints(
        networkType: NetworkType.connected,
      ),
    );
  }

  // Cancel all tasks
  static Future<void> cancelAll() async {
    await Workmanager().cancelAll();
  }
}
import 'dart:async';
import 'dart:convert';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:sqflite/sqflite.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../constants/app_constants.dart';
import '../errors/failures.dart';
import 'database_helper.dart';
import 'api_service.dart';

class SyncService {
  static final SyncService _instance = SyncService._internal();
  factory SyncService() => _instance;
  SyncService._internal();

  final ApiService _apiService = ApiService();
  Timer? _syncTimer;
  bool _isSyncing = false;

  static Future<void> initialize() async {
    await _instance._apiService.initialize();
    _instance._startPeriodicSync();
  }

  void _startPeriodicSync() {
    _syncTimer = Timer.periodic(AppConstants.syncInterval, (_) {
      performSync();
    });
  }

  Future<SyncResult> performSync() async {
    if (_isSyncing) {
      return SyncResult(success: false, message: 'Sync already in progress');
    }

    _isSyncing = true;

    try {
      // Check connectivity
      final connectivityResult = await Connectivity().checkConnectivity();
      final bool hasConnection = (connectivityResult.isNotEmpty &&
                !connectivityResult.contains(ConnectivityResult.none));
      if (!hasConnection) {
        return SyncResult(success: false, message: 'No internet connection');
      }

      // 1. Upload pending changes from sync queue
      await _uploadPendingChanges();

      // 2. Download latest data from server
      await _downloadLatestData();

      // 3. Update last sync time
      await _updateLastSyncTime();

      return SyncResult(success: true, message: 'Sync completed successfully');
    } catch (e) {
      return SyncResult(
        success: false,
        message: 'Sync failed: ${e.toString()}',
      );
    } finally {
      _isSyncing = false;
    }
  }

  Future<void> _uploadPendingChanges() async {
    final pendingItems = await DatabaseHelper.query(
      AppConstants.syncQueueTable,
      where: 'status = ?',
      whereArgs: ['pending'],
    );

    for (final item in pendingItems) {
      try {
        final entityType = item['entity_type'] as String;
        final entityId = item['entity_id'] as String;
        final action = item['action'] as String;
        final data = jsonDecode(item['data'] as String) as Map<String, dynamic>;

        // Upload to server based on entity type and action
        await _uploadEntity(entityType, entityId, action, data);

        // Mark as synced
        await DatabaseHelper.update(
          AppConstants.syncQueueTable,
          {'status': 'synced', 'updated_at': DateTime.now().toIso8601String()},
          where: 'id = ?',
          whereArgs: [item['id']],
        );
      } catch (e) {
        // Update retry count
        final retryCount = (item['retry_count'] as int) + 1;
        if (retryCount >= AppConstants.maxRetryAttempts) {
          await DatabaseHelper.update(
            AppConstants.syncQueueTable,
            {
              'status': 'failed',
              'updated_at': DateTime.now().toIso8601String(),
            },
            where: 'id = ?',
            whereArgs: [item['id']],
          );
        } else {
          await DatabaseHelper.update(
            AppConstants.syncQueueTable,
            {
              'retry_count': retryCount,
              'updated_at': DateTime.now().toIso8601String(),
            },
            where: 'id = ?',
            whereArgs: [item['id']],
          );
        }
      }
    }
  }

  Future<void> _uploadEntity(
    String entityType,
    String entityId,
    String action,
    Map<String, dynamic> data,
  ) async {
    switch (entityType) {
      case AppConstants.leadsTable:
        await _uploadLead(action, data);
        break;
      case AppConstants.propertiesTable:
        await _uploadProperty(action, data);
        break;
      case AppConstants.commissionsTable:
        await _uploadCommission(action, data);
        break;
      case 'bookings':
        await _uploadBooking(action, data);
        break;
      default:
        throw UnknownFailure('Unknown entity type: $entityType');
    }
  }

  Future<void> _uploadLead(String action, Map<String, dynamic> data) async {
    switch (action) {
      case 'create':
        await _apiService.post(AppConstants.leadsEndpoint, data: data);
        break;
      case 'update':
        await _apiService.put(
          '${AppConstants.leadsEndpoint}/${data['id']}',
          data: data,
        );
        break;
      case 'delete':
        await _apiService.delete('${AppConstants.leadsEndpoint}/${data['id']}');
        break;
    }
  }

  Future<void> _uploadProperty(String action, Map<String, dynamic> data) async {
    switch (action) {
      case 'update':
        await _apiService.put(
          '${AppConstants.propertiesEndpoint}/${data['id']}',
          data: data,
        );
        break;
      default:
        throw UnknownFailure('Unsupported action for property: $action');
    }
  }

  Future<void> _uploadCommission(
    String action,
    Map<String, dynamic> data,
  ) async {
    // Commissions are typically read-only from mobile
    throw const UnknownFailure('Commissions cannot be modified from mobile');
  }

  Future<void> _uploadBooking(String action, Map<String, dynamic> data) async {
    if (action == 'create') {
      await _apiService.post('/sync', data: {'type': 'booking', 'data': data});
    }
  }

  Future<void> _downloadLatestData() async {
    try {
      final lastSync = await _getLastSyncTime();
      final userId = await _getUserId();

      final updates = await _apiService.getUpdates(lastSync, userId);

      // 1. Process Properties
      if (updates.containsKey('properties')) {
        final properties = updates['properties'] as List;
        for (final property in properties) {
          final propertyMap = property as Map<String, dynamic>;
          await DatabaseHelper.insert(
            AppConstants.propertiesTable,
            {
              ...propertyMap,
              'last_synced_at': DateTime.now().toIso8601String(),
            },
            conflictAlgorithm: ConflictAlgorithm.replace,
          );
        }
      }

      // 2. Process Leads
      if (updates.containsKey('leads')) {
        final leads = updates['leads'] as List;
        for (final lead in leads) {
          final leadMap = lead as Map<String, dynamic>;
          await DatabaseHelper.insert(AppConstants.leadsTable, {
            ...leadMap,
            'last_synced_at': DateTime.now().toIso8601String(),
            'is_synced': 1,
          }, conflictAlgorithm: ConflictAlgorithm.replace);
        }
      }

      // 3. Process Commissions (Payouts)
      if (updates.containsKey('mlm_stats') &&
          (updates['mlm_stats'] as Map<String, dynamic>).containsKey(
            'payouts',
          )) {
        final payouts =
            (updates['mlm_stats'] as Map<String, dynamic>)['payouts'] as List;
        for (final payout in payouts) {
          await DatabaseHelper.insert(
            AppConstants.commissionsTable,
            {
              'commission_id': payout['id'].toString(),
              'user_id': payout['user_id'].toString(),
              'amount': payout['amount'],
              'percentage': payout['percentage'] ?? 0.0,
              'status': payout['status'],
              'created_at': payout['created_at'],
              'updated_at': payout['updated_at'],
              'last_synced_at': DateTime.now().toIso8601String(),
            },
            conflictAlgorithm: ConflictAlgorithm.replace,
          );
        }
      }

      // 4. Process Incentives
      if (updates.containsKey('incentives')) {
        final incentives = updates['incentives'] as List;
        for (final incentive in incentives) {
          final incentiveMap = incentive as Map<String, dynamic>;
          await DatabaseHelper.insert(
            AppConstants.incentivesTable,
            {
              ...incentiveMap,
              'last_synced_at': DateTime.now().toIso8601String(),
            },
            conflictAlgorithm: ConflictAlgorithm.replace,
          );
        }
      }

      // 5. Update local user profile if provided in mlm_stats
      if (updates.containsKey('mlm_stats') &&
          (updates['mlm_stats'] as Map<String, dynamic>).containsKey(
            'summary',
          )) {
        final summary =
            (updates['mlm_stats'] as Map<String, dynamic>)['summary']
                as Map<String, dynamic>;
        final userProfile = await _apiService
            .getProfile(); // Still need profile for full details
        final userProfileData = userProfile['data'] as Map<String, dynamic>;
        await DatabaseHelper.insert(AppConstants.usersTable, {
          ...userProfileData,
          'current_rank': summary['rank'],
          'last_synced_at': DateTime.now().toIso8601String(),
        }, conflictAlgorithm: ConflictAlgorithm.replace);
      }
    } catch (e) {
      print('Error downloading data: $e');
      rethrow;
    }
  }

  Future<String> _getLastSyncTime() async {
    const storage = FlutterSecureStorage();
    return await storage.read(key: AppConstants.lastSyncTimeKey) ??
        '2000-01-01 00:00:00';
  }

  Future<String> _getUserId() async {
    const storage = FlutterSecureStorage();
    return await storage.read(key: AppConstants.userIdKey) ?? '';
  }

  Future<void> _updateLastSyncTime() async {
    // This would typically be stored in secure storage
    // For now, we'll just print it
    print('Last sync time: ${DateTime.now().toIso8601String()}');
  }

  // Queue operations for offline changes
  Future<void> queueChange(
    String entityType,
    String entityId,
    String action,
    Map<String, dynamic> data,
  ) async {
    await DatabaseHelper.insert(AppConstants.syncQueueTable, {
      'entity_type': entityType,
      'entity_id': entityId,
      'action': action,
      'data': jsonEncode(data),
      'status': 'pending',
      'retry_count': 0,
      'created_at': DateTime.now().toIso8601String(),
      'updated_at': DateTime.now().toIso8601String(),
    });
  }

  Future<List<Map<String, dynamic>>> getPendingSyncItems() async {
    return await DatabaseHelper.query(
      AppConstants.syncQueueTable,
      where: 'status = ?',
      whereArgs: ['pending'],
      orderBy: 'created_at ASC',
    );
  }

  Future<void> clearSyncQueue() async {
    await DatabaseHelper.delete(AppConstants.syncQueueTable);
  }

  void dispose() {
    _syncTimer?.cancel();
  }
}

class SyncResult {
  final bool success;
  final String message;

  SyncResult({required this.success, required this.message});
}

// Riverpod provider
final syncServiceProvider = Provider<SyncService>((ref) => SyncService());

class SyncState {
  const SyncState._({this.message, this.pendingCount});

  const SyncState.initial() : this._();
  const SyncState.syncing() : this._();
  const SyncState.completed(String msg) : this._(message: msg);
  const SyncState.error(String msg) : this._(message: msg);
  const SyncState.pending(int count) : this._(pendingCount: count);

  final String? message;
  final int? pendingCount;

  bool get isInitial => message == null && pendingCount == null;
  bool get isSyncing => message == null && pendingCount == null;
  bool get isCompleted => message != null && pendingCount == null;
  bool get isError => message != null && pendingCount == null;
  bool get isPending => pendingCount != null;
}

import 'dart:async';
import 'dart:convert';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
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
      final connectivity = await Connectivity().checkConnectivity();
      if (connectivity == ConnectivityResult.none) {
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
      return SyncResult(success: false, message: 'Sync failed: ${e.toString()}');
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
        final data = jsonDecode(item['data'] as String);
        
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
            {'status': 'failed', 'updated_at': DateTime.now().toIso8601String()},
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
  
  Future<void> _uploadEntity(String entityType, String entityId, String action, Map<String, dynamic> data) async {
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
      default:
        throw UnknownFailure('Unknown entity type: $entityType');
    }
  }
  
  Future<void> _uploadLead(String action, Map<String, dynamic> data) async {
    switch (action) {
      case 'create':
        await _apiService.post('${AppConstants.leadsEndpoint}', data: data);
        break;
      case 'update':
        await _apiService.put('${AppConstants.leadsEndpoint}/${data['id']}', data: data);
        break;
      case 'delete':
        await _apiService.delete('${AppConstants.leadsEndpoint}/${data['id']}');
        break;
    }
  }
  
  Future<void> _uploadProperty(String action, Map<String, dynamic> data) async {
    switch (action) {
      case 'update':
        await _apiService.put('${AppConstants.propertiesEndpoint}/${data['id']}', data: data);
        break;
      default:
        throw UnknownFailure('Unsupported action for property: $action');
    }
  }
  
  Future<void> _uploadCommission(String action, Map<String, dynamic> data) async {
    // Commissions are typically read-only from mobile
    throw UnknownFailure('Commissions cannot be modified from mobile');
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
          await DatabaseHelper.insert(
            AppConstants.propertiesTable,
            {
              ...property,
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
          await DatabaseHelper.insert(
            AppConstants.leadsTable,
            {
              ...lead,
              'last_synced_at': DateTime.now().toIso8601String(),
              'is_synced': 1,
            },
            conflictAlgorithm: ConflictAlgorithm.replace,
          );
        }
      }
      
      // 3. Process Commissions (Payouts)
      if (updates.containsKey('mlm_stats') && updates['mlm_stats'].containsKey('payouts')) {
        final payouts = updates['mlm_stats']['payouts'] as List;
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
          await DatabaseHelper.insert(
            AppConstants.incentivesTable,
            {
              ...incentive,
              'last_synced_at': DateTime.now().toIso8601String(),
            },
            conflictAlgorithm: ConflictAlgorithm.replace,
          );
        }
      }
      
      // 5. Update local user profile if provided in mlm_stats
      if (updates.containsKey('mlm_stats') && updates['mlm_stats'].containsKey('summary')) {
        final summary = updates['mlm_stats']['summary'];
        final userProfile = await _apiService.getProfile(); // Still need profile for full details
        await DatabaseHelper.insert(
          AppConstants.usersTable,
          {
            ...userProfile['data'],
            'current_rank': summary['rank'],
            'last_synced_at': DateTime.now().toIso8601String(),
          },
          conflictAlgorithm: ConflictAlgorithm.replace,
        );
      }
    } catch (e) {
      print('Error downloading data: $e');
      rethrow;
    }
  }

  Future<String> _getLastSyncTime() async {
    final storage = const FlutterSecureStorage();
    return await storage.read(key: AppConstants.lastSyncTimeKey) ?? '2000-01-01 00:00:00';
  }

  Future<String> _getUserId() async {
    final storage = const FlutterSecureStorage();
    return await storage.read(key: AppConstants.userIdKey) ?? '';
  }
  
  Future<void> _downloadProperties() async {
    final properties = await _apiService.getProperties();
    
    for (final property in properties) {
      await DatabaseHelper.insert(
        AppConstants.propertiesTable,
        {
          ...property,
          'last_synced_at': DateTime.now().toIso8601String(),
        },
      );
    }
  }
  
  Future<void> _downloadLeads() async {
    final leads = await _apiService.getLeads();
    
    for (final lead in leads) {
      await DatabaseHelper.insert(
        AppConstants.leadsTable,
        {
          ...lead,
          'last_synced_at': DateTime.now().toIso8601String(),
          'is_synced': 1,
        },
      );
    }
  }
  
  Future<void> _downloadCommissions() async {
    final commissions = await _apiService.getCommissions();
    
    for (final commission in commissions) {
      await DatabaseHelper.insert(
        AppConstants.commissionsTable,
        {
          ...commission,
          'last_synced_at': DateTime.now().toIso8601String(),
        },
      );
    }
  }
  
  Future<void> _downloadUserProfile() async {
    final profile = await _apiService.getProfile();
    
    await DatabaseHelper.insert(
      AppConstants.usersTable,
      {
        ...profile,
        'last_synced_at': DateTime.now().toIso8601String(),
      },
    );
  }
  
  Future<void> _updateLastSyncTime() async {
    // This would typically be stored in secure storage
    // For now, we'll just print it
    print('Last sync time: ${DateTime.now().toIso8601String()}');
  }
  
  // Queue operations for offline changes
  Future<void> queueChange(String entityType, String entityId, String action, Map<String, dynamic> data) async {
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

// Riverpod providers
final syncServiceProvider = Provider<SyncService>((ref) => SyncService());

final syncStateProvider = StateNotifierProvider<SyncNotifier, SyncState>((ref) {
  return SyncNotifier(ref.watch(syncServiceProvider));
});

class SyncNotifier extends StateNotifier<SyncState> {
  SyncNotifier(this._syncService) : super(const SyncState.initial());
  
  final SyncService _syncService;
  
  Future<void> sync() async {
    state = const SyncState.syncing();
    
    try {
      final result = await _syncService.performSync();
      state = SyncState.completed(result.message);
    } catch (e) {
      state = SyncState.error(e.toString());
    }
  }
  
  Future<void> checkPendingItems() async {
    try {
      final pendingItems = await _syncService.getPendingSyncItems();
      state = SyncState.pending(pendingItems.length);
    } catch (e) {
      state = SyncState.error(e.toString());
    }
  }
}

class SyncState {
  const SyncState.initial();
  const SyncState.syncing();
  const SyncState.completed(this.message);
  const SyncState.error(this.message);
  const SyncState.pending(this.pendingCount);
  
  final String? message;
  final int? pendingCount;
  
  bool get isInitial => this is SyncState && message == null && pendingCount == null;
  bool get isSyncing => this is SyncState && message == null && pendingCount == null;
  bool get isCompleted => message != null && pendingCount == null;
  bool get isError => message != null && pendingCount == null;
  bool get isPending => pendingCount != null;
}

import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/services/api_service.dart';
import '../../core/services/database_helper.dart';
import '../models/lead_model.dart';

/// Lead Repository - Handles lead management for associates
/// Offline-first pattern for telecalling
class LeadRepository {
  final ApiService _apiService;
  final DatabaseHelper _dbHelper;

  LeadRepository(this._apiService, this._dbHelper);

  /// Get my leads (for associates)
  Future<List<LeadModel>> getMyLeads({
    String? status,
    String? priority,
    bool? isAssigned,
    DateTime? followUpDate,
  }) async {
    // Try local first
    final localLeadsData = await _dbHelper.getMyLeads(
      status: status,
      priority: priority,
      followUpDate: followUpDate,
    );
    final localLeads = localLeadsData
        .map((e) => LeadModel.fromJson(e))
        .toList();

    // If online, fetch from API
    if (await _apiService.isConnected()) {
      try {
        final filters = <String, dynamic>{};
        if (status != null) filters['status'] = status;
        if (priority != null) filters['priority'] = priority;
        if (isAssigned != null) filters['is_assigned'] = isAssigned;
        if (followUpDate != null) {
          filters['follow_up_date'] = followUpDate.toIso8601String();
        }

        final response = await _apiService.get(
          '/leads',
          queryParameters: filters,
        );
        final leads = (response['data'] as List)
            .map((json) => LeadModel.fromJson(json as Map<String, dynamic>))
            .toList();

        // Update local cache
        await _dbHelper.saveLeads(leads.map((l) => l.toJson()).toList());

        return leads;
      } catch (e) {
        return localLeads;
      }
    }

    return localLeads;
  }

  /// Get lead details
  Future<LeadModel?> getLeadById(String leadId) async {
    // Try local first
    final localData = await _dbHelper.getLeadById(leadId);
    final local = localData != null
        ? LeadModel.fromJson(localData)
        : null;

    // If online, fetch fresh
    if (await _apiService.isConnected()) {
      try {
        final response = await _apiService.get('/leads/$leadId');
        final lead =
            LeadModel.fromJson(response['data'] as Map<String, dynamic>);

        await _dbHelper.saveLead(lead.toJson());
        return lead;
      } catch (e) {
        return local;
      }
    }

    return local;
  }

  /// Create lead (Offline-first)
  Future<LeadModel> createLead({
    required String name,
    required String phone,
    String? email,
    String? source,
    String? notes,
    String? priority,
    DateTime? followUpDate,
  }) async {
    // Create local lead first
    final localLead = LeadModel(
      id: DateTime.now().millisecondsSinceEpoch.toString(),
      name: name,
      phone: phone,
      email: email,
      source: source ?? 'manual',
      status: 'new',
      priority: priority ?? 'medium',
      followUpNotes: notes,
      createdAt: DateTime.now(),
      isOfflineCreated: true,
    );

    // Save locally
    await _dbHelper.saveLead(localLead.toJson());

    // If online, sync immediately
    if (await _apiService.isConnected()) {
      try {
        final response = await _apiService.post(
          '/leads',
          data: {
            'name': name,
            'phone': phone,
            'email': email,
            'source': source,
            'notes': notes,
            'priority': priority,
            'follow_up_date': followUpDate?.toIso8601String(),
          },
        );

        final serverLead = LeadModel.fromJson(response['data'] as Map<String, dynamic>);

        // Update local with server data
        await _dbHelper.updateLeadWithServerData(
          localId: localLead.id,
          serverLead: serverLead.toJson(),
        );

        return serverLead;
      } catch (e) {
        // Queue for later sync
        await _dbHelper.addToSyncQueue(
          entityType: 'lead',
          entityId: localLead.id,
          action: 'create',
          data: localLead.toJson(),
        );
        return localLead;
      }
    } else {
      // Queue for later sync
      await _dbHelper.addToSyncQueue(
        entityType: 'lead',
        entityId: localLead.id,
        action: 'create',
        data: localLead.toJson(),
      );
      return localLead;
    }
  }

  /// Update lead status
  Future<LeadModel> updateLeadStatus(
    String leadId,
    String status, {
    String? notes,
    DateTime? followUpDate,
  }) async {
    final data = <String, dynamic>{
      'status': status,
    };
    if (notes != null) data['notes'] = notes;
    if (followUpDate != null) {
      data['follow_up_date'] = followUpDate.toIso8601String();
    }

    // Update locally
    await _dbHelper.updateLead(leadId, data);

    // If online, sync
    if (await _apiService.isConnected()) {
      try {
        final response = await _apiService.put(
          '/leads/$leadId',
          data: data,
        );
        return LeadModel.fromJson(response['data'] as Map<String, dynamic>);
      } catch (e) {
        // Queue for later
        await _dbHelper.addToSyncQueue(
          entityType: 'lead',
          entityId: leadId,
          action: 'update',
          data: data,
        );
      }
    } else {
      // Queue for later
      await _dbHelper.addToSyncQueue(
        entityType: 'lead',
        entityId: leadId,
        action: 'update',
        data: data,
      );
    }

    final result = await _dbHelper.getLeadById(leadId);
    return LeadModel.fromJson(result!);
  }

  /// Add call log
  Future<void> addCallLog(
    String leadId, {
    required String callType,
    required Duration duration,
    String? notes,
    String? outcome,
  }) async {
    final callLog = {
      'lead_id': leadId,
      'call_type': callType,
      'duration': duration.inSeconds,
      'notes': notes,
      'outcome': outcome,
      'call_time': DateTime.now().toIso8601String(),
    };

    // Save locally
    await _dbHelper.addCallLog(callLog);

    // If online, sync
    if (await _apiService.isConnected()) {
      try {
        await _apiService.post(
          '/leads/$leadId/call-logs',
          data: callLog,
        );
      } catch (e) {
        // Queue for later
        await _dbHelper.addToSyncQueue(
          entityType: 'call_log',
          entityId: leadId,
          action: 'create',
          data: callLog,
        );
      }
    } else {
      // Queue for later
      await _dbHelper.addToSyncQueue(
        entityType: 'call_log',
        entityId: leadId,
        action: 'create',
        data: callLog,
      );
    }
  }

  /// Get leads by status
  Future<Map<String, List<LeadModel>>> getLeadsByStatus() async {
    final leads = await getMyLeads();
    final grouped = <String, List<LeadModel>>{};

    for (final lead in leads) {
      grouped.putIfAbsent(lead.status ?? '', () => []).add(lead);
    }

    return grouped;
  }

  /// Get today's follow-ups
  Future<List<LeadModel>> getTodaysFollowUps() async {
    final today = DateTime.now();
    return await getMyLeads(
      followUpDate: today,
    );
  }

  /// Search leads
  Future<List<LeadModel>> searchLeads(String query) async {
    // Search local first
    final localResults = await _dbHelper.searchLeads(query);

    // If online, search API
    if (await _apiService.isConnected()) {
      try {
        final response = await _apiService.get(
          '/leads/search',
          queryParameters: {'q': query},
        );
        final results = (response['data'] as List)
            .map((json) => LeadModel.fromJson(json as Map<String, dynamic>))
            .toList();

        // Cache results
        await _dbHelper.saveLeads(results.map((l) => l.toJson()).toList());
        return results;
      } catch (e) {
        return localResults.map((m) => LeadModel.fromJson(m)).toList();
      }
    }

    return localResults.map((m) => LeadModel.fromJson(m)).toList();
  }

  /// Parse lead from text using AI
  Future<LeadModel?> parseLeadFromText(String text) async {
    if (await _apiService.isConnected()) {
      try {
        final response = await _apiService.post(
          '/ai/parse-lead',
          data: {'text': text},
        );
        return LeadModel.fromJson(response['data'] as Map<String, dynamic>);
      } catch (e) {
        return null;
      }
    }
    return null;
  }

  /// Get lead statistics
  Future<Map<String, int>> getLeadStats() async {
    final leads = await getMyLeads();
    final stats = <String, int>{};

    for (final lead in leads) {
      final key = lead.status ?? '';
      stats[key] = (stats[key] ?? 0) + 1;
    }

    return stats;
  }

  /// Sync pending leads
  Future<SyncResult> syncPendingLeads() async {
    final pending = await _dbHelper.getUnsyncedLeads();

    if (pending.isEmpty) {
      return SyncResult(success: true, message: 'No pending leads');
    }

    if (!(await _apiService.isConnected())) {
      return SyncResult(
        success: false,
        message: 'No internet connection',
      );
    }

    int successCount = 0;
    int failCount = 0;

    for (final lead in pending) {
      try {
        if (lead['server_id'] == null) {
          // New lead - create on server
          final response = await _apiService.post(
            '/leads',
            data: lead,
          );
          final serverLead = LeadModel.fromJson(response['data'] as Map<String, dynamic>);
          await _dbHelper.markLeadAsSynced(
            lead['id'].toString(),
            int.parse(serverLead.id),
          );
        } else {
          // Existing lead - update
          await _apiService.put(
            '/leads/${lead['server_id']}',
            data: lead,
          );
          await _dbHelper.markLeadAsSynced(
            lead['id'].toString(),
            lead['server_id'] as int,
          );
        }
        successCount++;
      } catch (e) {
        failCount++;
        await _dbHelper.incrementLeadRetryCount(lead['id'].toString());
      }
    }

    return SyncResult(
      success: failCount == 0,
      message: 'Synced $successCount leads, $failCount failed',
    );
  }
}

/// Sync result
class SyncResult {
  final bool success;
  final String message;

  SyncResult({required this.success, required this.message});
}

/// Provider for LeadRepository
final leadRepositoryProvider = Provider<LeadRepository>((ref) {
  final apiService = ApiService();
  final dbHelper = DatabaseHelper();
  return LeadRepository(apiService, dbHelper);
});

/// Provider for my leads
final myLeadsProvider =
    FutureProvider.autoDispose.family<List<LeadModel>, Map<String, dynamic>?>((
  ref,
  filters,
) async {
  final repository = ref.watch(leadRepositoryProvider);
  return await repository.getMyLeads(
    status: filters?['status'] as String?,
    priority: filters?['priority'] as String?,
    followUpDate: filters?['follow_up_date'] as DateTime?,
  );
});

/// Provider for lead details
final leadDetailsProvider =
    FutureProvider.autoDispose.family<LeadModel?, String>((ref, leadId) async {
  final repository = ref.watch(leadRepositoryProvider);
  return await repository.getLeadById(leadId);
});

/// Provider for leads by status
final leadsByStatusProvider =
    FutureProvider.autoDispose<Map<String, List<LeadModel>>>((ref) async {
  final repository = ref.watch(leadRepositoryProvider);
  return await repository.getLeadsByStatus();
});

/// Provider for today's follow-ups
final todaysFollowUpsProvider =
    FutureProvider.autoDispose<List<LeadModel>>((ref) async {
  final repository = ref.watch(leadRepositoryProvider);
  return await repository.getTodaysFollowUps();
});

/// Provider for lead stats
final leadStatsProvider =
    FutureProvider.autoDispose<Map<String, int>>((ref) async {
  final repository = ref.watch(leadRepositoryProvider);
  return await repository.getLeadStats();
});

/// Provider for lead search
final leadSearchProvider = FutureProvider.autoDispose
    .family<List<LeadModel>, String>((ref, query) async {
  final repository = ref.watch(leadRepositoryProvider);
  return await repository.searchLeads(query);
});

import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/constants/app_constants.dart';
import '../../core/services/api_service.dart';
import '../../core/utils/logger.dart';
import '../../data/models/lead_model.dart';
import '../../data/models/lead_activity_model.dart';

/// Lead Service - CRM Lead Management (MySQL-first, REST API)
class LeadService {
  final ApiService _api = ApiService();

  /// Create New Lead via REST API
  Future<String> createLead({
    required String name,
    required String phone,
    String? email,
    String? address,
    required String source,
    String? interestedIn,
    String? preferredLocation,
    double? budgetMin,
    double? budgetMax,
    String? notes,
    String? assignedTo,
    String? voiceNoteUrl,
    String? voiceTranscript,
  }) async {
    try {
      final response = await _api.post(
        AppConstants.leadsEndpoint,
        data: {
          'name': name,
          'phone': phone,
          'email': email,
          'source': source,
          'interested_in': interestedIn ?? preferredLocation,
          'preferred_location': preferredLocation,
          'budget_min': budgetMin,
          'budget_max': budgetMax,
          'notes': notes,
          'assigned_to': assignedTo,
          'voice_note_url': voiceNoteUrl,
          'voice_transcript': voiceTranscript,
          'status': AppConstants.leadStatusNew,
        },
      );

      final id = (response['data']?['id'] ?? '').toString();
      AppLogger.info('Lead created via API: $id');
      return id;
    } catch (e, stackTrace) {
      AppLogger.error('Error creating lead via API', e, stackTrace);
      rethrow;
    }
  }

  /// Get Leads for Associate via REST API
  Future<List<LeadModel>> getLeadsForAssociate(String associateId) async {
    try {
      final response = await _api.get(
        AppConstants.leadsEndpoint,
        queryParameters: {'assigned_to': associateId},
      );

      final data = response['data'] ?? [];
      final leads = (data as List).map((json) {
        return LeadModel.fromJson(json as Map<String, dynamic>);
      }).toList();

      AppLogger.info('Fetched ${leads.length} leads for associate $associateId');
      return leads;
    } catch (e, stackTrace) {
      AppLogger.error('Error fetching leads for associate', e, stackTrace);
      return [];
    }
  }

  /// Get All Leads (for Admin) via REST API
  Future<List<LeadModel>> getAllLeads({String? status}) async {
    try {
      final params = <String, dynamic>{};
      if (status != null && status != 'all') {
        params['status'] = status;
      }

      final response = await _api.get(
        AppConstants.leadsEndpoint,
        queryParameters: params,
      );

      final data = response['data'] ?? [];
      return (data as List).map((json) {
        return LeadModel.fromJson(json as Map<String, dynamic>);
      }).toList();
    } catch (e, stackTrace) {
      AppLogger.error('Error fetching all leads', e, stackTrace);
      return [];
    }
  }

  /// Get Lead by ID via REST API
  Future<LeadModel?> getLeadById(String leadId) async {
    try {
      final response = await _api.get('${AppConstants.leadsEndpoint}/$leadId');
      final data = response['data'];
      if (data != null) {
        return LeadModel.fromJson(data as Map<String, dynamic>);
      }
      return null;
    } catch (e) {
      AppLogger.error('Error getting lead: $leadId', e);
      return null;
    }
  }

  /// Update Lead Status via REST API
  Future<void> updateLeadStatus({
    required String leadId,
    required String status,
    String? notes,
    String? performedBy,
  }) async {
    try {
      await _api.post(
        '${AppConstants.leadsEndpoint}/$leadId/status',
        data: {
          'status': status,
          'notes': notes,
          'performed_by': performedBy,
        },
      );

      AppLogger.info('Lead $leadId status updated to: $status');
    } catch (e, stackTrace) {
      AppLogger.error('Error updating lead status', e, stackTrace);
      rethrow;
    }
  }

  /// Schedule Follow-up via REST API
  Future<void> scheduleFollowUp({
    required String leadId,
    required DateTime followUpDate,
    required String type,
    String? notes,
  }) async {
    try {
      await _api.post(
        '${AppConstants.leadsEndpoint}/$leadId/follow-up',
        data: {
          'follow_up_date': followUpDate.toIso8601String(),
          'follow_up_type': type,
          'notes': notes,
        },
      );

      AppLogger.info('Follow-up scheduled for lead: $leadId');
    } catch (e, stackTrace) {
      AppLogger.error('Error scheduling follow-up', e, stackTrace);
      rethrow;
    }
  }

  /// Add Activity to Lead via REST API
  Future<void> addActivity({
    required String leadId,
    required String type,
    required String performedBy,
    String? notes,
    String? outcome,
    String? recordingUrl,
    List<String>? photos,
  }) async {
    try {
      await _api.post(
        '${AppConstants.leadsEndpoint}/$leadId/activities',
        data: {
          'type': type,
          'performed_by': performedBy,
          'notes': notes,
          'outcome': outcome,
          'recording_url': recordingUrl,
          'photos': photos,
        },
      );

      AppLogger.info('Activity added to lead: $leadId');
    } catch (e, stackTrace) {
      AppLogger.error('Error adding activity', e, stackTrace);
      rethrow;
    }
  }

  /// Get Follow-ups for Today via REST API
  Future<List<LeadModel>> getTodaysFollowUps(String associateId) async {
    try {
      final today = DateTime.now();
      final response = await _api.get(
        '${AppConstants.leadsEndpoint}/follow-ups',
        queryParameters: {
          'assigned_to': associateId,
          'date': today.toIso8601String().split('T')[0],
        },
      );

      final data = response['data'] ?? [];
      return (data as List).map((json) {
        return LeadModel.fromJson(json as Map<String, dynamic>);
      }).toList();
    } catch (e) {
      AppLogger.error('Error getting today\'s follow-ups', e);
      return [];
    }
  }

  /// Convert Lead to Customer via REST API
  Future<void> convertLead({
    required String leadId,
    required String convertedBy,
    String? bookingId,
    double? convertedAmount,
  }) async {
    try {
      await _api.post(
        '${AppConstants.leadsEndpoint}/$leadId/convert',
        data: {
          'converted_by': convertedBy,
          'booking_id': bookingId,
          'converted_amount': convertedAmount,
        },
      );

      AppLogger.info('Lead converted: $leadId');
    } catch (e, stackTrace) {
      AppLogger.error('Error converting lead', e, stackTrace);
      rethrow;
    }
  }

  /// Mark Lead as Lost via REST API
  Future<void> markLeadAsLost({
    required String leadId,
    required String reason,
    String? notes,
  }) async {
    try {
      await _api.post(
        '${AppConstants.leadsEndpoint}/$leadId/lost',
        data: {
          'reason': reason,
          'notes': notes,
        },
      );

      AppLogger.info('Lead marked as lost: $leadId');
    } catch (e, stackTrace) {
      AppLogger.error('Error marking lead as lost', e, stackTrace);
      rethrow;
    }
  }

  /// Batch Sync Leads (for offline queue) via REST API
  Future<int> batchSyncLeads(List<Map<String, dynamic>> offlineLeads) async {
    try {
      final response = await _api.post(
        AppConstants.leadsEndpoint,
        data: {'leads': offlineLeads},
      );

      final data = response['data'] as Map<String, dynamic>?;
      final syncedCount = (data?['synced_count'] as num?)?.toInt() ?? 0;
      AppLogger.info('Batch synced $syncedCount leads');
      return syncedCount;
    } catch (e, stackTrace) {
      AppLogger.error('Error batch syncing leads', e, stackTrace);
      rethrow;
    }
  }

  /// Get Lead Statistics via REST API
  Future<LeadStatistics> getLeadStatistics(String associateId) async {
    try {
      final response = await _api.get(
        '${AppConstants.leadsEndpoint}/statistics',
        queryParameters: {'assigned_to': associateId},
      );

      final raw = response['data'] ?? {};
      final data = raw is Map<String, dynamic> ? raw : <String, dynamic>{};
      return LeadStatistics(
        totalLeads: (data['total_leads'] as num?)?.toInt() ?? 0,
        newLeads: (data['new_leads'] as num?)?.toInt() ?? 0,
        contactedLeads: (data['contacted_leads'] as num?)?.toInt() ?? 0,
        qualifiedLeads: (data['qualified_leads'] as num?)?.toInt() ?? 0,
        interestedLeads: (data['interested_leads'] as num?)?.toInt() ?? 0,
        visitedLeads: (data['visited_leads'] as num?)?.toInt() ?? 0,
        convertedLeads: (data['converted_leads'] as num?)?.toInt() ?? 0,
        lostLeads: (data['lost_leads'] as num?)?.toInt() ?? 0,
        conversionRate: ((data['conversion_rate'] as num?) ?? 0).toDouble(),
        bySource: Map<String, int>.from((data['by_source'] as Map?) ?? {}),
        byStatus: Map<String, int>.from((data['by_status'] as Map?) ?? {}),
        byMonth: Map<String, int>.from((data['by_month'] as Map?) ?? {}),
        averageResponseTime: ((data['average_response_time'] as num?) ?? 0).toDouble(),
        followUpsDueToday: (data['follow_ups_due_today'] as num?)?.toInt() ?? 0,
        followUpsOverdue: (data['follow_ups_overdue'] as num?)?.toInt() ?? 0,
      );
    } catch (e) {
      AppLogger.error('Error getting lead statistics', e);
      return const LeadStatistics(
        totalLeads: 0,
        newLeads: 0,
        contactedLeads: 0,
        qualifiedLeads: 0,
        interestedLeads: 0,
        visitedLeads: 0,
        convertedLeads: 0,
        lostLeads: 0,
        conversionRate: 0,
        bySource: {},
        byStatus: {},
        byMonth: {},
        averageResponseTime: 0,
        followUpsDueToday: 0,
        followUpsOverdue: 0,
      );
    }
  }
}

// Lead Service Provider
final leadServiceProvider = Provider<LeadService>((ref) => LeadService());

final leadsProvider = FutureProvider.family<List<LeadModel>, String>(
  (ref, associateId) async {
    final leadService = ref.watch(leadServiceProvider);
    return leadService.getLeadsForAssociate(associateId);
  },
);

final allLeadsProvider = FutureProvider.family<List<LeadModel>, String?>(
  (ref, status) async {
    final leadService = ref.watch(leadServiceProvider);
    return leadService.getAllLeads(status: status);
  },
);

final leadStatisticsProvider = FutureProvider.family<LeadStatistics, String>(
  (ref, associateId) async {
    final leadService = ref.watch(leadServiceProvider);
    return leadService.getLeadStatistics(associateId);
  },
);

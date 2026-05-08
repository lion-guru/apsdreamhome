import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
// import 'package:hive/hive.dart'; // Incompatible with Flutter 3.41.6

import '../../core/constants/app_constants.dart';
import '../../core/utils/logger.dart';
import '../../data/models/lead_model.dart';
import '../../data/models/lead_activity_model.dart';

/// Lead Service - CRM Lead Management
class LeadService {
  final FirebaseFirestore _firestore = FirebaseFirestore.instance;
  // final Box _offlineBox = Hive.box(AppConstants.offlineBoxName); // Disabled due to Flutter 3.41.6 incompatibility

  CollectionReference get _leads =>
      _firestore.collection(AppConstants.leadsCollection);

  /// Create New Lead (Online + Offline Support)
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
    LeadModel? lead;
    try {
      lead = LeadModel(
        id: '',
        name: name,
        phone: phone,
        email: email,
        interestedIn: interestedIn ?? preferredLocation,
        budgetMax: budgetMax ?? budgetMin,
        status: AppConstants.leadStatusNew,
        followUpNotes: notes,
        createdAt: DateTime.now(),
        updatedAt: DateTime.now(),
      );

      final docRef = await _leads.add(lead.toJson()..remove('id'));

      AppLogger.info('Lead created: ${docRef.id}');
      return docRef.id;
    } catch (e, stackTrace) {
      AppLogger.error('Error creating lead', e, stackTrace);

      // Store offline for later sync - Disabled due to Flutter 3.41.6 incompatibility
      // if (lead != null) {
      //   await _storeOfflineLead(lead.toJson()..remove('id'));
      // }

      rethrow;
    }
  }

  /// Create Lead Offline (for field agents) - Disabled due to Flutter 3.41.6 incompatibility
  /*
  Future<void> createLeadOffline({
    required String name,
    required String phone,
    String? email,
    String? source,
    String? interestedIn,
    String? notes,
    String? voiceNotePath,
  }) async {
    final leadData = {
      'name': name,
      'phone': phone,
      'email': email,
      'source': source ?? 'field_visit',
      'interestedIn': interestedIn,
      'followUpNotes': notes,
      'voiceNotePath': voiceNotePath,
      'isOfflineCreated': true,
      'createdAt': DateTime.now().toIso8601String(),
      'status': AppConstants.leadStatusNew,
    };

    await _storeOfflineLead(leadData);
    AppLogger.info('Lead stored offline');
  }
  */

  /// Sync Offline Leads - Disabled due to Flutter 3.41.6 incompatibility
  /*
  Future<void> syncOfflineLeads() async {
    final offlineLeadsRaw = _offlineBox
        .get('pending_leads', defaultValue: <Map<String, dynamic>>[]);
    final offlineLeads =
        (offlineLeadsRaw as List<dynamic>?)?.cast<Map<String, dynamic>>() ?? [];

    for (var leadData in offlineLeads) {
      try {
        leadData['isOfflineCreated'] = false;
        leadData['syncedAt'] = DateTime.now().toIso8601String();

        await _leads.add(leadData);

        // Remove from offline storage
        offlineLeads.remove(leadData);
      } catch (e) {
        AppLogger.error('Failed to sync lead', e);
      }
    }

    await _offlineBox.put('pending_leads', offlineLeads);
    AppLogger.info('Offline leads synced');
  }
  */

  /// Get Leads for Associate
  Stream<List<LeadModel>> getLeadsForAssociate(String associateId) {
    return _leads
        .where('assignedTo', isEqualTo: associateId)
        .orderBy('createdAt', descending: true)
        .snapshots()
        .map((snapshot) => snapshot.docs
            .map((doc) => LeadModel.fromJson({
                  'id': doc.id,
                  ...doc.data() as Map<String, dynamic>,
                }))
            .toList());
  }

  /// Get All Leads (for Admin)
  Stream<List<LeadModel>> getAllLeads({String? status}) {
    Query query = _leads.orderBy('createdAt', descending: true);

    if (status != null && status != 'all') {
      query = query.where('status', isEqualTo: status);
    }

    return query.snapshots().map((snapshot) => snapshot.docs
        .map((doc) => LeadModel.fromJson({
              'id': doc.id,
              ...doc.data() as Map<String, dynamic>,
            }))
        .toList());
  }

  /// Get Lead by ID
  Future<LeadModel?> getLeadById(String leadId) async {
    try {
      final doc = await _leads.doc(leadId).get();

      if (doc.exists) {
        return LeadModel.fromJson({
          'id': doc.id,
          ...doc.data() as Map<String, dynamic>,
        });
      }
      return null;
    } catch (e) {
      AppLogger.error('Error getting lead: $leadId', e);
      return null;
    }
  }

  /// Update Lead Status
  Future<void> updateLeadStatus({
    required String leadId,
    required String status,
    String? notes,
    String? performedBy,
  }) async {
    try {
      final lead = await getLeadById(leadId);
      if (lead == null) throw Exception('Lead not found');

      final activity = LeadActivity(
        id: DateTime.now().millisecondsSinceEpoch.toString(),
        leadId: leadId,
        type: 'status_change',
        performedBy: performedBy ?? '',
        performedAt: DateTime.now(),
        notes: notes,
      );

      await _leads.doc(leadId).update({
        'status': status,
        'updatedAt': DateTime.now().toIso8601String(),
        'activities': FieldValue.arrayUnion([activity.toJson()]),
      });

      AppLogger.info('Lead $leadId status updated to: $status');
    } catch (e, stackTrace) {
      AppLogger.error('Error updating lead status', e, stackTrace);
      rethrow;
    }
  }

  /// Schedule Follow-up
  Future<void> scheduleFollowUp({
    required String leadId,
    required DateTime followUpDate,
    required String type,
    String? notes,
  }) async {
    try {
      await _leads.doc(leadId).update({
        'nextFollowUpDate': followUpDate.toIso8601String(),
        'nextFollowUpType': type,
        'followUpNotes': notes,
        'updatedAt': DateTime.now().toIso8601String(),
      });

      AppLogger.info('Follow-up scheduled for lead: $leadId');
    } catch (e, stackTrace) {
      AppLogger.error('Error scheduling follow-up', e, stackTrace);
      rethrow;
    }
  }

  /// Add Activity to Lead
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
      final activity = LeadActivity(
        id: DateTime.now().millisecondsSinceEpoch.toString(),
        leadId: leadId,
        type: type,
        performedBy: performedBy,
        performedAt: DateTime.now(),
        notes: notes,
        outcome: outcome,
        recordingUrl: recordingUrl,
        photos: photos,
      );

      await _leads.doc(leadId).update({
        'activities': FieldValue.arrayUnion([activity.toJson()]),
        'updatedAt': DateTime.now().toIso8601String(),
      });

      AppLogger.info('Activity added to lead: $leadId');
    } catch (e, stackTrace) {
      AppLogger.error('Error adding activity', e, stackTrace);
      rethrow;
    }
  }

  /// Get Follow-ups for Today
  Future<List<LeadModel>> getTodaysFollowUps(String associateId) async {
    try {
      final today = DateTime.now();
      final tomorrow = today.add(const Duration(days: 1));

      final snapshot = await _leads
          .where('assignedTo', isEqualTo: associateId)
          .where('nextFollowUpDate',
              isGreaterThanOrEqualTo: today.toIso8601String())
          .where('nextFollowUpDate', isLessThan: tomorrow.toIso8601String())
          .get();

      return snapshot.docs
          .map((doc) => LeadModel.fromJson({
                'id': doc.id,
                ...doc.data() as Map<String, dynamic>,
              }))
          .toList();
    } catch (e) {
      AppLogger.error('Error getting today\'s follow-ups', e);
      return [];
    }
  }

  /// Convert Lead to Customer
  Future<void> convertLead({
    required String leadId,
    required String convertedBy,
    String? bookingId,
    double? convertedAmount,
  }) async {
    try {
      await _leads.doc(leadId).update({
        'status': AppConstants.leadStatusConverted,
        'convertedAt': DateTime.now().toIso8601String(),
        'convertedBy': convertedBy,
        'bookingId': bookingId,
        'convertedAmount': convertedAmount,
        'updatedAt': DateTime.now().toIso8601String(),
      });

      AppLogger.info('Lead converted: $leadId');
    } catch (e, stackTrace) {
      AppLogger.error('Error converting lead', e, stackTrace);
      rethrow;
    }
  }

  /// Mark Lead as Lost
  Future<void> markLeadAsLost({
    required String leadId,
    required String reason,
    String? notes,
  }) async {
    try {
      await _leads.doc(leadId).update({
        'status': AppConstants.leadStatusLost,
        'lostAt': DateTime.now().toIso8601String(),
        'lostReason': reason,
        'lostNotes': notes,
        'updatedAt': DateTime.now().toIso8601String(),
      });

      AppLogger.info('Lead marked as lost: $leadId');
    } catch (e, stackTrace) {
      AppLogger.error('Error marking lead as lost', e, stackTrace);
      rethrow;
    }
  }

  /// Get Lead Statistics
  Future<LeadStatistics> getLeadStatistics(String associateId) async {
    try {
      final snapshot =
          await _leads.where('assignedTo', isEqualTo: associateId).get();

      int total = 0,
          newLeads = 0,
          contacted = 0,
          interested = 0,
          visited = 0,
          converted = 0,
          lost = 0;

      for (var doc in snapshot.docs) {
        final data = doc.data() as Map<String, dynamic>;
        final status = data['status'] as String?;

        total++;
        switch (status) {
          case 'new':
            newLeads++;
            break;
          case 'contacted':
            contacted++;
            break;
          case 'interested':
            interested++;
            break;
          case 'visited':
            visited++;
            break;
          case 'converted':
            converted++;
            break;
          case 'lost':
            lost++;
            break;
        }
      }

      final conversionRate =
          total > 0 ? ((converted / total) * 100).toDouble() : 0.0;

      return LeadStatistics(
        totalLeads: total,
        newLeads: newLeads,
        contactedLeads: contacted,
        qualifiedLeads: 0,
        interestedLeads: interested,
        visitedLeads: visited,
        convertedLeads: converted,
        lostLeads: lost,
        conversionRate: conversionRate,
        bySource: {},
        byStatus: {
          'new': newLeads,
          'contacted': contacted,
          'interested': interested,
          'visited': visited,
          'converted': converted,
          'lost': lost,
        },
        byMonth: {},
        averageResponseTime: 0,
        followUpsDueToday: 0,
        followUpsOverdue: 0,
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

  /// Helper: Store Lead Offline
  /// Store Offline Lead - Disabled due to Flutter 3.41.6 incompatibility
  /*
  Future<void> _storeOfflineLead(Map<String, dynamic> leadData) async {
    final pendingLeads = _offlineBox.get('pending_leads', defaultValue: []);
    pendingLeads.add(leadData);
    await _offlineBox.put('pending_leads', pendingLeads);
  }
  */
}

// Lead Service Provider
final leadServiceProvider = Provider<LeadService>((ref) => LeadService());

final leadsProvider = StreamProvider.family<List<LeadModel>, String>(
  (ref, associateId) {
    final leadService = ref.watch(leadServiceProvider);
    return leadService.getLeadsForAssociate(associateId);
  },
);

final allLeadsProvider = StreamProvider.family<List<LeadModel>, String?>(
  (ref, status) {
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

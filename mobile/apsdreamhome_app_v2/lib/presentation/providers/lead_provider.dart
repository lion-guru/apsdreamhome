import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_riverpod/legacy.dart';
import '../../core/services/database_helper.dart';
import '../../core/services/sync_service.dart';
import '../../core/constants/app_constants.dart';
import '../../data/models/lead_model.dart';

final leadsProvider = StateNotifierProvider<LeadNotifier, AsyncValue<List<Lead>>>((ref) {
  return LeadNotifier();
});

class LeadNotifier extends StateNotifier<AsyncValue<List<Lead>>> {
  LeadNotifier() : super(const AsyncValue.loading()) {
    _loadLeads();
  }
  
  Future<void> _loadLeads() async {
    try {
      final leads = await DatabaseHelper.query(AppConstants.leadsTable);
      final leadList = leads.map((json) => Lead.fromJson(json)).toList();
      state = AsyncValue.data(leadList);
    } catch (e) {
      state = AsyncValue.error(e, StackTrace.current);
    }
  }
  
  Future<void> refreshLeads() async {
    state = const AsyncValue.loading();
    await _loadLeads();
  }
  
  Future<void> updateLeadStatus(Lead lead, String newStatus) async {
    try {
      // Update local database
      await DatabaseHelper.update(
        AppConstants.leadsTable,
        {
          'status': newStatus,
          'updated_at': DateTime.now().toIso8601String(),
        },
        where: 'id = ?',
        whereArgs: [lead.id],
      );
      
      // Queue for sync
      await SyncService().queueChange(
        AppConstants.leadsTable,
        lead.id,
        'update',
        {
          'id': lead.id,
          'status': newStatus,
        },
      );
      
      // Update local state
      final currentList = state.value ?? [];
      final updatedList = currentList.map((l) {
        if (l.id == lead.id) {
          return l.copyWith(status: newStatus);
        }
        return l;
      }).toList();
      
      state = AsyncValue.data(updatedList);
    } catch (e) {
      state = AsyncValue.error(e, StackTrace.current);
    }
  }
  
  Future<void> addLead(Lead lead) async {
    try {
      // Add to local database
      await DatabaseHelper.insert(AppConstants.leadsTable, {
        'id': lead.id,
        'name': lead.name,
        'email': lead.email,
        'phone': lead.phone,
        'interestedIn': lead.interestedIn,
        'budgetMin': lead.budgetMin,
        'status': lead.status,
        'followUpNotes': lead.followUpNotes,
        'created_at': lead.createdAt?.toIso8601String(),
        'updated_at': lead.updatedAt?.toIso8601String(),
        'is_synced': 0,
      });
      
      // Queue for sync
      await SyncService().queueChange(
        AppConstants.leadsTable,
        lead.id,
        'create',
        {...lead.toJson(), 'is_synced': 0},
      );
      
      // Update local state
      final currentList = state.value ?? [];
      final updatedList = [...currentList, lead];
      state = AsyncValue.data(updatedList);
    } catch (e) {
      state = AsyncValue.error(e, StackTrace.current);
    }
  }
  
  Future<void> deleteLead(Lead lead) async {
    try {
      // Remove from local database
      await DatabaseHelper.delete(
        AppConstants.leadsTable,
        where: 'id = ?',
        whereArgs: [lead.id],
      );
      
      // Queue for sync
      await SyncService().queueChange(
        AppConstants.leadsTable,
        lead.id,
        'delete',
        {'id': lead.id},
      );
      
      // Update local state
      final currentList = state.value ?? [];
      final updatedList = currentList.where((l) => l.id != lead.id).toList();
      state = AsyncValue.data(updatedList);
    } catch (e) {
      state = AsyncValue.error(e, StackTrace.current);
    }
  }
}

import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/crm_models.dart';
import '../../core/services/api_service.dart';
import '../../core/utils/logger.dart';

class CRMService {
  final ApiService _api = ApiService();

  // ─── Dashboard ────────────────────────────────────────────────────

  Future<Map<String, dynamic>> getDashboard() async {
    try {
      final res = await _api.get('/crm/dashboard');
      final data = res['data'];
      return (data is Map<String, dynamic>) ? data : res;
    } catch (e, st) {
      AppLogger.error('CRM dashboard error', e, st);
      return {};
    }
  }

  Future<Map<String, dynamic>> getAdminOverview() async {
    try {
      final res = await _api.get('/crm/admin-overview');
      final data = res['data'];
      return (data is Map<String, dynamic>) ? data : res;
    } catch (e, st) {
      AppLogger.error('CRM admin overview error', e, st);
      return {};
    }
  }

  // ─── Pipeline ─────────────────────────────────────────────────────

  Future<Map<String, dynamic>> getPipeline({String? search, String? source, int? assignedTo}) async {
    try {
      final params = <String, dynamic>{};
      if (search != null && search.isNotEmpty) params['search'] = search;
      if (source != null) params['source'] = source;
      if (assignedTo != null) params['assigned_to'] = assignedTo;
      final res = await _api.get('/crm/pipeline', queryParameters: params);
      final data = res['data'];
      return (data is Map<String, dynamic>) ? data : res;
    } catch (e, st) {
      AppLogger.error('CRM pipeline error', e, st);
      return {};
    }
  }

  Future<bool> moveLeadToStage(int leadId, String stage) async {
    try {
      await _api.post('/crm/pipeline/move-stage', data: {'lead_id': leadId, 'stage': stage});
      return true;
    } catch (e, st) {
      AppLogger.error('CRM move stage error', e, st);
      return false;
    }
  }

  // ─── Leads CRUD ───────────────────────────────────────────────────

  Future<Map<String, dynamic>> getLeads({
    String? search,
    String? status,
    String? source,
    String? priority,
    String? category,
    int? page,
    int? perPage,
  }) async {
    try {
      final params = <String, dynamic>{};
      if (search != null && search.isNotEmpty) params['search'] = search;
      if (status != null) params['status'] = status;
      if (source != null) params['source'] = source;
      if (priority != null) params['priority'] = priority;
      if (category != null) params['category'] = category;
      if (page != null) params['page'] = page;
      if (perPage != null) params['per_page'] = perPage;
      final res = await _api.get('/crm/leads', queryParameters: params);
      final data = res['data'];
      return (data is Map<String, dynamic>) ? data : res;
    } catch (e, st) {
      AppLogger.error('CRM leads error', e, st);
      return {};
    }
  }

  Future<CRMLead?> getLeadDetail(int id) async {
    try {
      final res = await _api.get('/crm/leads/$id');
      final data = res['data'];
      if (data != null) return CRMLead.fromJson(data as Map<String, dynamic>);
      return null;
    } catch (e, st) {
      AppLogger.error('CRM lead detail error', e, st);
      return null;
    }
  }

  Future<CRMLead?> createLead(Map<String, dynamic> data) async {
    try {
      final res = await _api.post('/crm/leads', data: data);
      final leadData = res['data'];
      if (leadData != null) return CRMLead.fromJson(leadData as Map<String, dynamic>);
      return null;
    } catch (e, st) {
      AppLogger.error('CRM create lead error', e, st);
      return null;
    }
  }

  Future<bool> updateLead(int id, Map<String, dynamic> data) async {
    try {
      await _api.put('/crm/leads/$id', data: data);
      return true;
    } catch (e, st) {
      AppLogger.error('CRM update lead error', e, st);
      return false;
    }
  }

  // ─── Interactions ─────────────────────────────────────────────────

  Future<List<CRMInteraction>> getInteractions(int leadId) async {
    try {
      final res = await _api.get('/crm/leads/$leadId/interactions');
      final list = res['data']?['interactions'] as List? ?? [];
      return list.map((j) => CRMInteraction.fromJson(j as Map<String, dynamic>)).toList();
    } catch (e, st) {
      AppLogger.error('CRM interactions error', e, st);
      return [];
    }
  }

  Future<bool> addInteraction(int leadId, Map<String, dynamic> data) async {
    try {
      await _api.post('/crm/leads/$leadId/interact', data: data);
      return true;
    } catch (e, st) {
      AppLogger.error('CRM add interaction error', e, st);
      return false;
    }
  }

  // ─── Tasks ────────────────────────────────────────────────────────

  Future<List<CRMTask>> getMyTasks({String status = 'pending'}) async {
    try {
      final res = await _api.get('/crm/tasks', queryParameters: {'status': status});
      final list = res['data']?['tasks'] as List? ?? [];
      return list.map((j) => CRMTask.fromJson(j as Map<String, dynamic>)).toList();
    } catch (e, st) {
      AppLogger.error('CRM tasks error', e, st);
      return [];
    }
  }

  Future<CRMTask?> createTask(Map<String, dynamic> data) async {
    try {
      final res = await _api.post('/crm/tasks', data: data);
      final taskData = res['data'];
      if (taskData != null) return CRMTask.fromJson(taskData as Map<String, dynamic>);
      return null;
    } catch (e, st) {
      AppLogger.error('CRM create task error', e, st);
      return null;
    }
  }

  Future<bool> completeTask(int taskId) async {
    try {
      await _api.put('/crm/tasks/$taskId/complete');
      return true;
    } catch (e, st) {
      AppLogger.error('CRM complete task error', e, st);
      return false;
    }
  }

  // ─── Assignment ───────────────────────────────────────────────────

  Future<bool> assignLead(int leadId, int assignTo, {String? reason}) async {
    try {
      await _api.post('/crm/leads/$leadId/assign', data: {
        'assigned_to': assignTo,
        if (reason != null) 'reason': reason,
      });
      return true;
    } catch (e, st) {
      AppLogger.error('CRM assign lead error', e, st);
      return false;
    }
  }

  Future<int> autoAssign() async {
    try {
      final res = await _api.post('/crm/auto-assign');
      return res['data']?['assigned'] as int? ?? 0;
    } catch (e, st) {
      AppLogger.error('CRM auto-assign error', e, st);
      return 0;
    }
  }

  // ─── Search ───────────────────────────────────────────────────────

  Future<List<Map<String, dynamic>>> search(String query) async {
    try {
      final res = await _api.get('/crm/search', queryParameters: {'q': query});
      final data = res['data'];
      if (data is List) return List<Map<String, dynamic>>.from(data);
      if (data is Map) return List<Map<String, dynamic>>.from((data['results'] as List?) ?? []);
      return [];
    } catch (e) {
      return [];
    }
  }

  // ─── Admin Employees ─────────────────────────────────────────────

  Future<Map<String, dynamic>> getAdminEmployees({
    String? search, String? role, String? status, int? offset, int? limit,
  }) async {
    try {
      final params = <String, dynamic>{};
      if (search != null && search.isNotEmpty) params['search'] = search;
      if (role != null && role.isNotEmpty) params['role'] = role;
      if (status != null && status.isNotEmpty) params['status'] = status;
      if (offset != null) params['offset'] = offset.toString();
      if (limit != null) params['limit'] = limit.toString();
      final res = await _api.get('/crm/admin-employees', queryParameters: params);
      final data = res['data'];
      return (data is Map<String, dynamic>) ? data : res;
    } catch (e, st) {
      AppLogger.error('CRM admin employees error', e, st);
      return {};
    }
  }

  // ─── Admin Finance Overview ──────────────────────────────────────

  Future<Map<String, dynamic>> getFinanceOverview() async {
    try {
      final res = await _api.get('/crm/finance-overview');
      final data = res['data'];
      return (data is Map<String, dynamic>) ? data : res;
    } catch (e, st) {
      AppLogger.error('CRM finance overview error', e, st);
      return {};
    }
  }
}

final crmServiceProvider = Provider<CRMService>((ref) => CRMService());

final crmDashboardProvider = FutureProvider<Map<String, dynamic>>((ref) async {
  return ref.watch(crmServiceProvider).getDashboard();
});

final crmPipelineProvider = FutureProvider.family<Map<String, dynamic>, Map<String, String?>>((ref, filters) async {
  return ref.watch(crmServiceProvider).getPipeline(search: filters['search'], source: filters['source']);
});

final crmLeadsProvider = FutureProvider.family<Map<String, dynamic>, Map<String, dynamic>>((ref, filters) async {
  return ref.watch(crmServiceProvider).getLeads(
    search: filters['search']?.toString(),
    status: filters['status']?.toString(),
    source: filters['source']?.toString(),
    priority: filters['priority']?.toString(),
    category: filters['category']?.toString(),
    page: filters['page'] is int ? filters['page'] as int : null,
  );
});

final crmAdminEmployeesProvider = FutureProvider.family<Map<String, dynamic>, Map<String, String?>>((ref, filters) async {
  return ref.watch(crmServiceProvider).getAdminEmployees(
    search: filters['search'],
    role: filters['role'],
    status: filters['status'],
  );
});

final crmFinanceOverviewProvider = FutureProvider<Map<String, dynamic>>((ref) async {
  return ref.watch(crmServiceProvider).getFinanceOverview();
});

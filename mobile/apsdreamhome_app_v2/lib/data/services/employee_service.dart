import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/services/api_service.dart';
import '../../core/utils/logger.dart';

class EmployeeService {
  final ApiService _api = ApiService();

  Future<Map<String, dynamic>> getDashboard() async {
    try {
      final res = await _api.get('/employee/dashboard');
      return (res['data'] is Map<String, dynamic>) ? res['data'] as Map<String, dynamic> : {};
    } catch (e, st) {
      AppLogger.error('Employee dashboard error', e, st);
      return {};
    }
  }

  Future<List<Map<String, dynamic>>> getTasks({String? status}) async {
    try {
      final params = <String, dynamic>{};
      if (status != null) params['status'] = status;
      final res = await _api.get('/employee/tasks', queryParameters: params);
      final list = res['data']?['tasks'] as List? ?? [];
      return List<Map<String, dynamic>>.from(list);
    } catch (e, st) {
      AppLogger.error('Employee tasks error', e, st);
      return [];
    }
  }

  Future<Map<String, dynamic>> getAttendance({int days = 30}) async {
    try {
      final res = await _api.get('/employee/attendance', queryParameters: {'days': days});
      return (res['data'] is Map<String, dynamic>) ? res['data'] as Map<String, dynamic> : {};
    } catch (e, st) {
      AppLogger.error('Employee attendance error', e, st);
      return {};
    }
  }
}

final employeeServiceProvider = Provider<EmployeeService>((ref) => EmployeeService());

final employeeDashboardProvider = FutureProvider<Map<String, dynamic>>((ref) async {
  return ref.watch(employeeServiceProvider).getDashboard();
});

final employeeTasksProvider = FutureProvider.family<List<Map<String, dynamic>>, String?>((ref, status) async {
  return ref.watch(employeeServiceProvider).getTasks(status: status);
});

final employeeAttendanceProvider = FutureProvider.family<Map<String, dynamic>, int>((ref, days) async {
  return ref.watch(employeeServiceProvider).getAttendance(days: days);
});

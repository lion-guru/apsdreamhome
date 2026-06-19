import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/services/api_service.dart';
import '../../core/utils/logger.dart';

/// Analytics Provider - Business Intelligence
/// Now uses REST API (MySQL) instead of Firestore
final analyticsProvider = Provider<AnalyticsService>((ref) {
  return AnalyticsService();
});

class AnalyticsService {
  /// Get Dashboard Stats for Admin
  Future<Map<String, dynamic>> getAdminDashboardStats() async {
    try {
      final response = await ApiService().get('/admin/dashboard-stats');
      if (response['success'] == true && response['data'] != null) {
        final data = response['data'] as Map<String, dynamic>;
        return {
          'totalSales': data['totalSales'] ?? 0,
          'totalBookings': data['totalBookings'] ?? 0,
          'activeAssociates': data['activeAssociates'] ?? 0,
          'pendingApprovals': data['pendingApprovals'] ?? 0,
          'monthlyCollection': data['monthlyCollection'] ?? 0,
          'totalPlots': data['totalPlots'] ?? 0,
          'lastUpdated': DateTime.now(),
        };
      }
      return {};
    } catch (e) {
      AppLogger.error('Error fetching dashboard stats', e);
      return {};
    }
  }

  /// Get Monthly Sales Trend
  Future<List<Map<String, dynamic>>> getMonthlySalesTrend({int months = 6}) async {
    try {
      final response = await ApiService().get(
        '/admin/sales-trend',
        queryParameters: {'months': months},
      );
      if (response['success'] == true && response['data'] != null) {
        final list = response['data'] as List<dynamic>;
        return list.map((item) => item as Map<String, dynamic>).toList();
      }
      return [];
    } catch (e) {
      AppLogger.error('Error fetching sales trend', e);
      return [];
    }
  }

  /// Get Top Performing Associates
  Future<List<Map<String, dynamic>>> getTopAssociates({int limit = 10}) async {
    try {
      final response = await ApiService().get(
        '/admin/top-associates',
        queryParameters: {'limit': limit},
      );
      if (response['success'] == true && response['data'] != null) {
        final list = response['data'] as List<dynamic>;
        return list.map((item) => item as Map<String, dynamic>).toList();
      }
      return [];
    } catch (e) {
      AppLogger.error('Error fetching top associates', e);
      return [];
    }
  }

  /// Get Colony Performance
  Future<List<Map<String, dynamic>>> getColonyPerformance() async {
    try {
      final response = await ApiService().get('/admin/colony-performance');
      if (response['success'] == true && response['data'] != null) {
        final list = response['data'] as List<dynamic>;
        return list.map((item) => item as Map<String, dynamic>).toList();
      }
      return [];
    } catch (e) {
      AppLogger.error('Error fetching colony performance', e);
      return [];
    }
  }

  /// Get EMI Collection Status
  Future<Map<String, dynamic>> getEMICollectionStatus() async {
    try {
      final response = await ApiService().get('/admin/emi-collection');
      if (response['success'] == true && response['data'] != null) {
        return response['data'] as Map<String, dynamic>;
      }
      return {'totalEMI': 0, 'collected': 0, 'pending': 0, 'collectionRate': '0', 'overdueCount': 0};
    } catch (e) {
      AppLogger.error('Error fetching EMI collection status', e);
      return {'totalEMI': 0, 'collected': 0, 'pending': 0, 'collectionRate': '0', 'overdueCount': 0};
    }
  }

  /// Get Lead Conversion Analytics
  Future<Map<String, dynamic>> getLeadConversionStats() async {
    try {
      final response = await ApiService().get('/admin/lead-conversion');
      if (response['success'] == true && response['data'] != null) {
        return response['data'] as Map<String, dynamic>;
      }
      return {'totalLeads': 0, 'converted': 0, 'conversionRate': '0', 'averageResponseTime': 'N/A'};
    } catch (e) {
      AppLogger.error('Error fetching lead conversion stats', e);
      return {'totalLeads': 0, 'converted': 0, 'conversionRate': '0', 'averageResponseTime': 'N/A'};
    }
  }

  /// Get Daily Sales Chart Data
  Future<List<Map<String, dynamic>>> getDailySalesChart({int days = 7}) async {
    try {
      final response = await ApiService().get(
        '/admin/daily-sales',
        queryParameters: {'days': days},
      );
      if (response['success'] == true && response['data'] != null) {
        final list = response['data'] as List<dynamic>;
        return list.map((item) => item as Map<String, dynamic>).toList();
      }
      return [];
    } catch (e) {
      AppLogger.error('Error fetching daily sales chart', e);
      return [];
    }
  }
}

// Provider for dashboard stats
final dashboardStatsProvider = FutureProvider<Map<String, dynamic>>((ref) async {
  final analytics = ref.watch(analyticsProvider);
  return analytics.getAdminDashboardStats();
});

// Provider for monthly trends
final monthlyTrendsProvider = FutureProvider<List<Map<String, dynamic>>>((ref) async {
  final analytics = ref.watch(analyticsProvider);
  return analytics.getMonthlySalesTrend();
});

// Provider for top associates
final topAssociatesProvider = FutureProvider<List<Map<String, dynamic>>>((ref) async {
  final analytics = ref.watch(analyticsProvider);
  return analytics.getTopAssociates();
});

// Provider for colony performance
final colonyPerformanceProvider = FutureProvider<List<Map<String, dynamic>>>((ref) async {
  final analytics = ref.watch(analyticsProvider);
  return analytics.getColonyPerformance();
});

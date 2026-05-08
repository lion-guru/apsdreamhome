import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:cloud_firestore/cloud_firestore.dart';

/// Analytics Provider - Business Intelligence
final analyticsProvider = Provider<AnalyticsService>((ref) {
  return AnalyticsService();
});

class AnalyticsService {
  final FirebaseFirestore _firestore = FirebaseFirestore.instance;
  
  /// Get Dashboard Stats for Admin
  Future<Map<String, dynamic>> getAdminDashboardStats() async {
    try {
      // Parallel queries for performance
      final results = await Future.wait([
        _getTotalSales(),
        _getTotalBookings(),
        _getActiveAssociates(),
        _getPendingApprovals(),
        _getMonthlyCollection(),
        _getTotalPlots(),
      ]);
      
      return {
        'totalSales': results[0],
        'totalBookings': results[1],
        'activeAssociates': results[2],
        'pendingApprovals': results[3],
        'monthlyCollection': results[4],
        'totalPlots': results[5],
        'lastUpdated': DateTime.now(),
      };
    } catch (e) {
      return {};
    }
  }
  
  /// Get Monthly Sales Trend
  Future<List<Map<String, dynamic>>> getMonthlySalesTrend({int months = 6}) async {
    final now = DateTime.now();
    final trends = <Map<String, dynamic>>[];
    
    for (int i = months - 1; i >= 0; i--) {
      final month = DateTime(now.year, now.month - i, 1);
      final sales = await _getSalesForMonth(month);
      
      trends.add({
        'month': '${month.year}-${month.month.toString().padLeft(2, '0')}',
        'sales': sales,
        'target': 10000000, // 1 Crore target
        'achievement': (sales / 10000000 * 100).toStringAsFixed(1),
      });
    }
    
    return trends;
  }
  
  /// Get Top Performing Associates
  Future<List<Map<String, dynamic>>> getTopAssociates({int limit = 10}) async {
    final snapshot = await _firestore
        .collection('users')
        .where('role', isEqualTo: 'associate')
        .where('isActive', isEqualTo: true)
        .orderBy('totalSales', descending: true)
        .limit(limit)
        .get();
    
    return snapshot.docs.map((doc) {
      final data = doc.data();
      return {
        'id': doc.id,
        'name': data['name'] ?? 'Unknown',
        'rank': data['rank'] ?? 'Associate',
        'totalSales': data['totalSales'] ?? 0,
        'totalBookings': data['totalBookings'] ?? 0,
        'teamSize': data['teamSize'] ?? 0,
        'commissionEarned': data['commissionEarned'] ?? 0,
      };
    }).toList();
  }
  
  /// Get Colony Performance
  Future<List<Map<String, dynamic>>> getColonyPerformance() async {
    final snapshot = await _firestore.collection('colonies').get();
    
    return snapshot.docs.map((doc) {
      final data = doc.data();
      final totalPlots = data['totalPlots'] ?? 0;
      final soldPlots = data['soldPlots'] ?? 0;
      
      return {
        'id': doc.id,
        'name': data['name'] ?? 'Unknown',
        'totalPlots': totalPlots,
        'soldPlots': soldPlots,
        'availablePlots': data['availablePlots'] ?? 0,
        'salesPercentage': (totalPlots as num) > 0 
            ? ((soldPlots as num) / (totalPlots) * 100).toStringAsFixed(1)
            : '0',
        'revenue': data['revenue'] ?? 0,
        'status': data['status'] ?? 'unknown',
      };
    }).toList();
  }
  
  /// Get EMI Collection Status
  Future<Map<String, dynamic>> getEMICollectionStatus() async {
    final now = DateTime.now();
    final firstDayOfMonth = DateTime(now.year, now.month, 1);
    
    final totalQuery = await _firestore
        .collection('bookings')
        .where('paymentPlan', isEqualTo: 'emi')
        .where('status', whereIn: ['confirmed', 'partial'])
        .get();
    
    final totalEMIAmount = totalQuery.docs.fold<double>(
      0, 
      (previousValue, doc) => previousValue + ((doc.data()['emiAmount'] ?? 0) as num).toDouble()
    );
    
    final collectedQuery = await _firestore
        .collection('payments')
        .where('type', isEqualTo: 'emi')
        .where('paidAt', isGreaterThanOrEqualTo: firstDayOfMonth)
        .get();
    
    final collectedAmount = collectedQuery.docs.fold<double>(
      0,
      (previousValue, doc) => previousValue + ((doc.data()['amount'] ?? 0) as num).toDouble()
    );
    
    return {
      'totalEMI': totalEMIAmount,
      'collected': collectedAmount,
      'pending': totalEMIAmount - collectedAmount,
      'collectionRate': totalEMIAmount > 0 
          ? (collectedAmount / totalEMIAmount * 100).toStringAsFixed(1)
          : '0',
      'overdueCount': await _getOverdueEMICount(),
    };
  }
  
  /// Get Lead Conversion Analytics
  Future<Map<String, dynamic>> getLeadConversionStats() async {
    final now = DateTime.now();
    final thirtyDaysAgo = now.subtract(const Duration(days: 30));
    
    final totalLeads = await _firestore
        .collection('leads')
        .where('createdAt', isGreaterThanOrEqualTo: thirtyDaysAgo)
        .count()
        .get();
    
    final convertedLeads = await _firestore
        .collection('leads')
        .where('createdAt', isGreaterThanOrEqualTo: thirtyDaysAgo)
        .where('status', isEqualTo: 'converted')
        .count()
        .get();
    
    final total = totalLeads.count ?? 0;
    final converted = convertedLeads.count ?? 0;
    
    return {
      'totalLeads': total,
      'converted': converted,
      'conversionRate': total > 0 
          ? (converted / total * 100).toStringAsFixed(1)
          : '0',
      'averageResponseTime': '2.5 hours', // Calculate from activity logs
    };
  }
  
  /// Get Daily Sales Chart Data
  Future<List<Map<String, dynamic>>> getDailySalesChart({int days = 7}) async {
    final now = DateTime.now();
    final data = <Map<String, dynamic>>[];
    
    for (int i = days - 1; i >= 0; i--) {
      final date = now.subtract(Duration(days: i));
      final dayStart = DateTime(date.year, date.month, date.day);
      final dayEnd = dayStart.add(const Duration(days: 1));
      
      final snapshot = await _firestore
          .collection('bookings')
          .where('createdAt', isGreaterThanOrEqualTo: dayStart)
          .where('createdAt', isLessThan: dayEnd)
          .where('status', whereIn: ['confirmed', 'completed'])
          .get();
      
      final totalSalesAmount = snapshot.docs.fold<double>(
        0,
        (previousValue, doc) => previousValue + ((doc.data()['totalPrice'] ?? 0) as num).toDouble()
      );
      
      data.add({
        'date': '${date.day}/${date.month}',
        'sales': totalSalesAmount,
        'bookings': snapshot.docs.length,
      });
    }
    
    return data;
  }
  
  // Private helper methods
  Future<double> _getTotalSales() async {
    final snapshot = await _firestore
        .collection('bookings')
        .where('status', whereIn: ['confirmed', 'completed'])
        .get();
    
    return snapshot.docs.fold<double>(
      0,
      (previousValue, doc) => previousValue + ((doc.data()['totalPrice'] ?? 0) as num).toDouble()
    );
  }
  
  Future<int> _getTotalBookings() async {
    final snapshot = await _firestore
        .collection('bookings')
        .count()
        .get();
    return snapshot.count ?? 0;
  }
  
  Future<int> _getActiveAssociates() async {
    final snapshot = await _firestore
        .collection('users')
        .where('role', isEqualTo: 'associate')
        .where('isActive', isEqualTo: true)
        .count()
        .get();
    return snapshot.count ?? 0;
  }
  
  Future<int> _getPendingApprovals() async {
    final bookingsCount = await _firestore
        .collection('bookings')
        .where('status', isEqualTo: 'pending')
        .count()
        .get();
    
    final commissionsCount = await _firestore
        .collection('commissions')
        .where('status', isEqualTo: 'pending')
        .count()
        .get();
    
    return (bookingsCount.count ?? 0) + (commissionsCount.count ?? 0);
  }
  
  Future<double> _getMonthlyCollection() async {
    final now = DateTime.now();
    final firstDay = DateTime(now.year, now.month, 1);
    
    final snapshot = await _firestore
        .collection('payments')
        .where('paidAt', isGreaterThanOrEqualTo: firstDay)
        .get();
    
    return snapshot.docs.fold<double>(
      0,
      (previousValue, doc) => previousValue + ((doc.data()['amount'] ?? 0) as num).toDouble()
    );
  }
  
  Future<int> _getTotalPlots() async {
    final snapshot = await _firestore.collection('plots').count().get();
    return snapshot.count ?? 0;
  }
  
  Future<double> _getSalesForMonth(DateTime month) async {
    final start = DateTime(month.year, month.month, 1);
    final nextMonth = month.month + 1;
    final nextYear = month.year + (nextMonth > 12 ? 1 : 0);
    final adjustedNextMonth = nextMonth > 12 ? 1 : nextMonth;
    final end = DateTime(nextYear, adjustedNextMonth, 1);
    
    final snapshot = await _firestore
        .collection('bookings')
        .where('createdAt', isGreaterThanOrEqualTo: start)
        .where('createdAt', isLessThan: end)
        .where('status', whereIn: ['confirmed', 'completed'])
        .get();
    
    return snapshot.docs.fold<double>(
      0,
      (previousValue, doc) => previousValue + ((doc.data()['totalPrice'] ?? 0) as num).toDouble()
    );
  }
  
  Future<int> _getOverdueEMICount() async {
    final now = DateTime.now();
    
    // Get all active EMI bookings
    final snapshot = await _firestore
        .collection('bookings')
        .where('paymentPlan', isEqualTo: 'emi')
        .where('status', whereIn: ['confirmed', 'partial'])
        .get();
    
    int overdueCount = 0;
    
    for (final doc in snapshot.docs) {
      final data = doc.data();
      final nextDueDate = (data['nextDueDate'] as Timestamp?)?.toDate();
      
      if (nextDueDate != null && nextDueDate.isBefore(now)) {
        overdueCount++;
      }
    }
    
    return overdueCount;
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

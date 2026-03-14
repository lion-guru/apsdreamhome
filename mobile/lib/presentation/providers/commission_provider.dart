import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:sqflite/sqflite.dart';
import '../../core/services/database_helper.dart';
import '../../core/constants/app_constants.dart';
import '../../data/models/commission_model.dart';

final commissionsProvider = StateNotifierProvider<CommissionNotifier, AsyncValue<List<Commission>>>((ref) {
  return CommissionNotifier();
});

class CommissionNotifier extends StateNotifier<AsyncValue<List<Commission>>> {
  CommissionNotifier() : super(const AsyncValue.loading()) {
    _loadCommissions();
  }
  
  Future<void> _loadCommissions() async {
    try {
      final commissions = await DatabaseHelper.query(AppConstants.commissionsTable);
      final commissionList = commissions.map((json) => Commission.fromJson(json)).toList();
      
      // Sort by creation date (newest first)
      commissionList.sort((a, b) => b.createdAt.compareTo(a.createdAt));
      
      state = AsyncValue.data(commissionList);
    } catch (e) {
      state = AsyncValue.error(e, StackTrace.current);
    }
  }
  
  Future<void> refreshCommissions() async {
    state = const AsyncValue.loading();
    await _loadCommissions();
  }
  
  Future<void> addCommission(Commission commission) async {
    try {
      // Add to local database
      await DatabaseHelper.insert(AppConstants.commissionsTable, {
        'commission_id': commission.commissionId,
        'user_id': commission.userId,
        'source_id': commission.sourceId,
        'source_type': commission.sourceType,
        'amount': commission.amount,
        'percentage': commission.percentage,
        'rank': commission.rank,
        'status': commission.status,
        'created_at': commission.createdAt,
        'updated_at': commission.updatedAt,
      });
      
      // Update local state
      final currentList = state.value ?? [];
      final updatedList = [commission, ...currentList];
      state = AsyncValue.data(updatedList);
    } catch (e) {
      state = AsyncValue.error(e, StackTrace.current);
    }
  }
  
  Future<void> updateCommissionStatus(Commission commission, String newStatus) async {
    try {
      // Update local database
      await DatabaseHelper.update(
        AppConstants.commissionsTable,
        {
          'status': newStatus,
          'updated_at': DateTime.now().toIso8601String(),
        },
        where: 'commission_id = ?',
        whereArgs: [commission.commissionId],
      );
      
      // Update local state
      final currentList = state.value ?? [];
      final updatedList = currentList.map((c) {
        if (c.commissionId == commission.commissionId) {
          return c.copyWith(status: newStatus);
        }
        return c;
      }).toList();
      
      state = AsyncValue.data(updatedList);
    } catch (e) {
      state = AsyncValue.error(e, StackTrace.current);
    }
  }
  
  // Calculate differential commission
  double calculateDifferentialCommission(String seniorRank, String juniorRank, double saleAmount) {
    final seniorRate = AppConstants.commissionRates[seniorRank] ?? 0.0;
    final juniorRate = AppConstants.commissionRates[juniorRank] ?? 0.0;
    final differential = seniorRate - juniorRate;
    
    if (differential > 0) {
      return saleAmount * (differential / 100);
    }
    
    return 0.0;
  }
  
  // Get commission statistics
  Map<String, double> getCommissionStats() {
    final commissions = state.value ?? [];
    
    final totalEarned = commissions
        .where((c) => c.isPaid)
        .fold<double>(0, (sum, c) => sum + c.amount);
    
    final pendingAmount = commissions
        .where((c) => c.isPending)
        .fold<double>(0, (sum, c) => sum + c.amount);
    
    final thisMonth = commissions
        .where((c) {
          final date = DateTime.parse(c.createdAt);
          final now = DateTime.now();
          return date.month == now.month && date.year == now.year;
        })
        .fold<double>(0, (sum, c) => sum + c.amount);
    
    return {
      'totalEarned': totalEarned,
      'pending': pendingAmount,
      'thisMonth': thisMonth,
    };
  }
}

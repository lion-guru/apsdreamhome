import '../models/commission_model.dart';
import '../models/payout_model.dart';
import '../models/bank_details.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/constants/app_constants.dart';
import '../../core/services/api_service.dart';
import '../../core/utils/logger.dart';

/// MLM Service - Differential Commission Calculation
/// Matches PHP DifferentialCommissionCalculator Logic
/// Now uses REST API (MySQL) instead of Firestore
class MLMService {
  /// Get Commission Percentage for Rank
  double getCommissionPercentage(String rank) {
    return AppConstants.rankCommissionPercentages[rank] ?? 6.0;
  }

  /// Get Rank Target
  double getRankTarget(String rank) {
    return AppConstants.rankTargets[rank] ?? 1000000;
  }

  /// Calculate Direct Commission (Level 1)
  double calculateDirectCommission(double saleAmount, String rank) {
    final percentage = getCommissionPercentage(rank);
    return (saleAmount * percentage) / 100;
  }

  /// Calculate Differential Commission
  /// Core Logic from PHP: Senior gets (Senior% - Already Distributed%)
  double calculateDifferentialCommission({
    required double saleAmount,
    required String ancestorRank,
    required double maxDistributedPercentage,
  }) {
    final ancestorPercentage = getCommissionPercentage(ancestorRank);
    if (ancestorPercentage > maxDistributedPercentage) {
      final diffPercentage = ancestorPercentage - maxDistributedPercentage;
      return (saleAmount * diffPercentage) / 100;
    }
    return 0.0;
  }

  /// Process Sale & Calculate Commissions for Entire Genealogy Chain
  Future<void> processSaleCommission({
    required String bookingId,
    required String plotId,
    required String customerId,
    required String sellerId,
    required double saleAmount,
  }) async {
    try {
      AppLogger.info('Processing sale commission for booking: $bookingId');
      final response = await ApiService().post(
        '/mlm/process-sale',
        data: {
          'bookingId': bookingId,
          'plotId': plotId,
          'customerId': customerId,
          'sellerId': sellerId,
          'saleAmount': saleAmount,
        },
      );
      if (response['success'] != true) {
        AppLogger.error('Sale commission failed: ${response['error']}', null);
      }
    } catch (e, stackTrace) {
      AppLogger.error('Error processing sale commission', e, stackTrace);
      rethrow;
    }
  }

  /// Get Associate's Commission Summary
  Future<CommissionSummary> getCommissionSummary(String associateId) async {
    try {
      final response = await ApiService().get('/mlm/summary');
      if (response['success'] == true && response['data'] != null) {
        final data = response['data'] as Map<String, dynamic>;
        return CommissionSummary(
          associateId: associateId,
          totalEarned: ((data['totalEarned'] ?? data['total_commission'] ?? 0) as num).toDouble(),
          totalPaid: ((data['totalPaid'] ?? data['paid_commission'] ?? 0) as num).toDouble(),
          totalPending: ((data['totalPending'] ?? data['pending_commission'] ?? 0) as num).toDouble(),
          totalHold: ((data['totalHold'] ?? 0) as num).toDouble(),
          count: (data['count'] ?? data['total_sales'] ?? 0) as int,
          totalSales: (data['totalSales'] ?? data['total_sales'] ?? 0) as int,
          directSales: (data['directSales'] ?? data['direct_sales'] ?? 0) as int,
          indirectSales: (data['indirectSales'] ?? data['indirect_sales'] ?? 0) as int,
          byLevel: (data['byLevel'] as Map<String, dynamic>?)?.map(
                (k, v) => MapEntry(k, (v as num).toDouble()),
              ) ??
              {},
          byMonth: (data['byMonth'] as Map<String, dynamic>?)?.map(
                (k, v) => MapEntry(k, (v as num).toDouble()),
              ) ??
              {},
        );
      }
      // Fallback: try to derive from payouts endpoint
      return _buildSummaryFromPayouts(associateId);
    } catch (e, stackTrace) {
      AppLogger.error('Error getting commission summary', e, stackTrace);
      return CommissionSummary(
        associateId: associateId,
        totalEarned: 0,
        totalPaid: 0,
        totalPending: 0,
        totalHold: 0,
        count: 0,
        totalSales: 0,
        directSales: 0,
        indirectSales: 0,
        byLevel: {},
        byMonth: {},
      );
    }
  }

  /// Build summary by querying payout history
  Future<CommissionSummary> _buildSummaryFromPayouts(String associateId) async {
    try {
      final response = await ApiService().get('/mlm/payouts');
      if (response['success'] != true || response['data'] == null) {
        return CommissionSummary(
          associateId: associateId,
          totalEarned: 0,
          totalPaid: 0,
          totalPending: 0,
          totalHold: 0,
          count: 0,
          totalSales: 0,
          directSales: 0,
          indirectSales: 0,
          byLevel: {},
          byMonth: {},
        );
      }

      final list = response['data'] as List<dynamic>;
      double totalEarned = 0;
      double totalPaid = 0;
      double totalPending = 0;
      double totalHold = 0;
      int directSales = 0;
      int indirectSales = 0;
      final Map<String, double> byLevel = {};

      for (final item in list) {
        final data = item as Map<String, dynamic>;
        final amount = ((data['commissionAmount'] ?? data['amount'] ?? 0) as num).toDouble();
        final status = data['status'] as String?;
        final level = (data['level'] as num?)?.toInt() ?? 1;

        totalEarned += amount;
        if (status == AppConstants.commissionStatusPaid) {
          totalPaid += amount;
        } else if (status == AppConstants.commissionStatusPending) {
          totalPending += amount;
        } else if (status == AppConstants.commissionStatusHold) {
          totalHold += amount;
        }

        if (level == 1) {
          directSales++;
        } else {
          indirectSales++;
        }

        final levelKey = 'Level $level';
        byLevel[levelKey] = (byLevel[levelKey] ?? 0) + amount;
      }

      return CommissionSummary(
        associateId: associateId,
        totalEarned: totalEarned,
        totalPaid: totalPaid,
        totalPending: totalPending,
        totalHold: totalHold,
        count: list.length,
        totalSales: list.length,
        directSales: directSales,
        indirectSales: indirectSales,
        byLevel: byLevel,
        byMonth: {},
      );
    } catch (e) {
      AppLogger.error('Error building summary from payouts', e);
      return CommissionSummary(
        associateId: associateId,
        totalEarned: 0,
        totalPaid: 0,
        totalPending: 0,
        totalHold: 0,
        count: 0,
        totalSales: 0,
        directSales: 0,
        indirectSales: 0,
        byLevel: {},
        byMonth: {},
      );
    }
  }

  /// Get Associate's Commissions (now returns Future instead of Stream)
  Future<List<CommissionModel>> getAssociateCommissions(String associateId) async {
    try {
      final response = await ApiService().get('/mlm/payouts');
      if (response['success'] == true && response['data'] != null) {
        final list = response['data'] as List<dynamic>;
        return list
            .map((doc) => CommissionModel.fromJson({
                  'id': doc['id']?.toString() ?? '',
                  ...doc as Map<String, dynamic>,
                }))
            .toList();
      }
      return [];
    } catch (e, stackTrace) {
      AppLogger.error('Error getting associate commissions', e, stackTrace);
      return [];
    }
  }

  /// Get Genealogy Tree
  Future<List<Map<String, dynamic>>> getGenealogyTree(
    String associateId, {
    int maxLevels = 7,
  }) async {
    try {
      final response = await ApiService().get('/mlm/genealogy');
      if (response['success'] == true && response['data'] != null) {
        final data = response['data'];
        if (data is List) {
          return List<Map<String, dynamic>>.from(data);
        }
        if (data is Map<String, dynamic>) {
          // Might be wrapped in a tree structure
          final downline = data['downline'] as List<dynamic>? ?? [];
          return List<Map<String, dynamic>>.from(downline);
        }
      }
      return [];
    } catch (e, stackTrace) {
      AppLogger.error('Error getting genealogy tree', e, stackTrace);
      return [];
    }
  }

  /// Request Payout
  Future<void> requestPayout({
    required String associateId,
    required double amount,
    required String paymentMethod,
    required BankDetails bankDetails,
    List<String>? commissionIds,
  }) async {
    try {
      AppLogger.info('Requesting payout: ₹$amount for $associateId');
      final response = await ApiService().post(
        '/mlm/request-payout',
        data: {
          'amount': amount,
          'payment_method': paymentMethod,
          'account_number': bankDetails.accountNumber,
          'ifsc_code': bankDetails.ifscCode,
          'bank_name': bankDetails.bankName,
          'upi_id': bankDetails.upiId,
          'remarks': 'Mobile app request',
        },
      );
      if (response['success'] != true) {
        throw Exception(response['error'] ?? response['message'] ?? 'Payout request failed');
      }
    } catch (e, stackTrace) {
      AppLogger.error('Payout request failed', e, stackTrace);
      rethrow;
    }
  }

  /// Check if User Can Upgrade Rank
  Future<bool> canUpgradeRank(String userId) async {
    try {
      final summary = await getCommissionSummary(userId);
      // Check each rank tier against total sales
      for (final rank in AppConstants.rankOrder) {
        final target = getRankTarget(rank);
        if (summary.totalEarned < target) {
          return false;
        }
      }
      return false; // Already at max rank if all targets met
    } catch (e) {
      return false;
    }
  }

  /// Upgrade User Rank (delegates to server)
  Future<void> upgradeRank(String userId) async {
    try {
      final response = await ApiService().post(
        '/mlm/upgrade-rank',
        data: {'userId': userId},
      );
      if (response['success'] != true) {
        throw Exception(response['error'] ?? 'Rank upgrade failed');
      }
    } catch (e, stackTrace) {
      AppLogger.error('Rank upgrade failed', e, stackTrace);
      rethrow;
    }
  }
}

// MLM Service Provider
final mlmServiceProvider = Provider<MLMService>((ref) => MLMService());

final commissionSummaryProvider =
    FutureProvider.family<CommissionSummary, String>(
  (ref, associateId) async {
    final mlmService = ref.watch(mlmServiceProvider);
    return mlmService.getCommissionSummary(associateId);
  },
);

/// Now FutureProvider instead of StreamProvider (Firestore streams removed)
final associateCommissionsProvider =
    FutureProvider.family<List<CommissionModel>, String>(
  (ref, associateId) async {
    final mlmService = ref.watch(mlmServiceProvider);
    return mlmService.getAssociateCommissions(associateId);
  },
);

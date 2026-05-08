import '../models/commission_model.dart';
import '../models/payout_model.dart';
import '../models/bank_details.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/constants/app_constants.dart';
import '../../core/utils/logger.dart';

/// MLM Service - Differential Commission Calculation
/// Matches PHP DifferentialCommissionCalculator Logic
class MLMService {
  final FirebaseFirestore _firestore = FirebaseFirestore.instance;

  // Collection References
  CollectionReference get _users =>
      _firestore.collection(AppConstants.usersCollection);
  CollectionReference get _commissions =>
      _firestore.collection(AppConstants.commissionsCollection);
  CollectionReference get _payouts =>
      _firestore.collection(AppConstants.payoutsCollection);

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

    // If ancestor has higher percentage than distributed
    if (ancestorPercentage > maxDistributedPercentage) {
      final diffPercentage = ancestorPercentage - maxDistributedPercentage;
      return (saleAmount * diffPercentage) / 100;
    }

    return 0.0;
  }

  /// Process Sale & Calculate Commissions for Entire Genealogy Chain
  /// This is the main method that handles differential commission
  Future<void> processSaleCommission({
    required String bookingId,
    required String plotId,
    required String customerId,
    required String sellerId, // Associate who sold the plot
    required double saleAmount,
  }) async {
    try {
      AppLogger.info('Processing sale commission for booking: $bookingId');

      // Get seller details
      final sellerDoc = await _users.doc(sellerId).get();
      if (!sellerDoc.exists) {
        throw Exception('Seller not found: $sellerId');
      }

      final sellerData = sellerDoc.data() as Map<String, dynamic>;
      final sellerRank =
          sellerData['rank'] as String? ?? AppConstants.rankAssociate;
      final sellerName = sellerData['name'] as String? ?? 'Unknown';

      // Track maximum percentage distributed in this chain
      double maxDistributedPercentage = getCommissionPercentage(sellerRank);

      // 1. Direct Commission to Seller (Level 1)
      final directCommission =
          calculateDirectCommission(saleAmount, sellerRank);

      await _createCommission(
        CommissionModel(
          id: '', // Firestore will generate
          associateId: sellerId,
          associateName: sellerName,
          associateRank: sellerRank,
          bookingId: bookingId,
          plotId: plotId,
          plotNumber: '', // Will be fetched
          colonyName: '', // Will be fetched
          customerName: '', // Will be fetched
          saleAmount: saleAmount,
          saleDate: DateTime.now(),
          commissionAmount: directCommission,
          percentage: getCommissionPercentage(sellerRank),
          level: 1,
          commissionType: 'direct_sale',
          sellerId: sellerId,
          sellerName: sellerName,
          sellerRank: sellerRank,
          relationship: 'direct_sale',
          status: AppConstants.commissionStatusPending,
          createdAt: DateTime.now(),
        ),
      );

      AppLogger.info(
          'Direct commission created: ₹$directCommission for $sellerName');

      // 2. Process Up the Chain (Levels 2-7)
      String? currentParentId = sellerData['parentId'] as String?;
      int currentLevel = 2;

      while (currentParentId != null &&
          currentLevel <= AppConstants.mlmMaxLevels) {
        final parentDoc = await _users.doc(currentParentId).get();
        if (!parentDoc.exists) break;

        final parentData = parentDoc.data() as Map<String, dynamic>;
        final parentRank =
            parentData['rank'] as String? ?? AppConstants.rankAssociate;
        final parentName = parentData['name'] as String? ?? 'Unknown';

        // Calculate differential commission
        final diffCommission = calculateDifferentialCommission(
          saleAmount: saleAmount,
          ancestorRank: parentRank,
          maxDistributedPercentage: maxDistributedPercentage,
        );

        if (diffCommission > 0) {
          // Determine relationship
          String relationship;
          switch (currentLevel) {
            case 2:
              relationship = 'parent';
              break;
            case 3:
              relationship = 'grandparent';
              break;
            case 4:
              relationship = 'great_grandparent';
              break;
            default:
              relationship = 'ancestor_level_$currentLevel';
          }

          await _createCommission(
            CommissionModel(
              id: '',
              associateId: currentParentId,
              associateName: parentName,
              associateRank: parentRank,
              bookingId: bookingId,
              plotId: plotId,
              plotNumber: '',
              colonyName: '',
              customerName: '',
              saleAmount: saleAmount,
              saleDate: DateTime.now(),
              commissionAmount: diffCommission,
              percentage: getCommissionPercentage(parentRank) -
                  maxDistributedPercentage,
              level: currentLevel,
              commissionType: 'differential',
              sellerId: sellerId,
              sellerName: sellerName,
              sellerRank: sellerRank,
              maxDistributedPercentage: maxDistributedPercentage,
              calculationBreakdown:
                  'Ancestor: ${getCommissionPercentage(parentRank)}% - '
                  'Distributed: ${maxDistributedPercentage.toStringAsFixed(2)}% = '
                  '${(getCommissionPercentage(parentRank) - maxDistributedPercentage).toStringAsFixed(2)}%',
              relationship: relationship,
              status: AppConstants.commissionStatusPending,
              createdAt: DateTime.now(),
            ),
          );

          AppLogger.info(
              'Differential commission: ₹$diffCommission for $parentName at level $currentLevel');

          // Update max distributed percentage
          final parentPercentage = getCommissionPercentage(parentRank);
          if (parentPercentage > maxDistributedPercentage) {
            maxDistributedPercentage = parentPercentage;
          }
        }

        // Move up to next parent
        currentParentId = parentData['parentId'] as String?;
        currentLevel++;
      }

      AppLogger.info(
          'Sale commission processing completed for booking: $bookingId');
    } catch (e, stackTrace) {
      AppLogger.error('Error processing sale commission', e, stackTrace);
      rethrow;
    }
  }

  /// Get Associate's Commission Summary
  Future<CommissionSummary> getCommissionSummary(String associateId) async {
    try {
      final commissions =
          await _commissions.where('associateId', isEqualTo: associateId).get();

      double totalEarned = 0;
      double totalPaid = 0;
      double totalPending = 0;
      double totalHold = 0;
      int directSales = 0;
      int indirectSales = 0;
      final Map<String, double> byLevel = {};

      for (var doc in commissions.docs) {
        final data = doc.data() as Map<String, dynamic>;
        final amount = ((data['commissionAmount'] ?? 0) as num).toDouble();
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

        // By level
        final levelKey = 'Level $level';
        byLevel[levelKey] = ((byLevel[levelKey] ?? 0) as num) + amount;
      }

      return CommissionSummary(
        associateId: associateId,
        totalEarned: totalEarned,
        totalPaid: totalPaid,
        totalPending: totalPending,
        totalHold: totalHold,
        count: commissions.docs.length,
        totalSales: commissions.docs.length,
        directSales: directSales,
        indirectSales: indirectSales,
        byLevel: byLevel,
        byMonth: {}, // TODO: Calculate by month
      );
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

  /// Get Associate's Commissions Stream
  Stream<List<CommissionModel>> getAssociateCommissions(String associateId) {
    return _commissions
        .where('associateId', isEqualTo: associateId)
        .orderBy('createdAt', descending: true)
        .snapshots()
        .map((snapshot) => snapshot.docs
            .map((doc) => CommissionModel.fromJson({
                  'id': doc.id,
                  ...doc.data() as Map<String, dynamic>,
                }))
            .toList());
  }

  /// Get Genealogy Tree
  Future<List<Map<String, dynamic>>> getGenealogyTree(
    String associateId, {
    int maxLevels = 7,
  }) async {
    final List<Map<String, dynamic>> genealogy = [];
    final Set<String> processedIds = {associateId};

    await _buildGenealogyLevel(
      associateId,
      1,
      maxLevels,
      genealogy,
      processedIds,
    );

    return genealogy;
  }

  /// Recursively Build Genealogy
  Future<void> _buildGenealogyLevel(
    String parentId,
    int currentLevel,
    int maxLevels,
    List<Map<String, dynamic>> genealogy,
    Set<String> processedIds,
  ) async {
    if (currentLevel > maxLevels) return;

    final children = await _users.where('parentId', isEqualTo: parentId).get();

    for (var child in children.docs) {
      final childId = child.id;

      // Avoid circular references
      if (processedIds.contains(childId)) continue;
      processedIds.add(childId);

      final childData = child.data() as Map<String, dynamic>;

      // Get downline stats
      final downlineCount = await _getDownlineCount(childId);
      final totalSales = await _getTotalSales(childId);

      genealogy.add({
        'userId': childId,
        'name': childData['name'] ?? '',
        'rank': childData['rank'] ?? AppConstants.rankAssociate,
        'level': currentLevel,
        'phone': childData['phone'] ?? '',
        'email': childData['email'] ?? '',
        'profileImage': childData['profileImage'],
        'downlineCount': downlineCount,
        'totalSales': totalSales,
        'joinedAt': childData['createdAt'],
      });

      // Recursively get next level
      await _buildGenealogyLevel(
        childId,
        currentLevel + 1,
        maxLevels,
        genealogy,
        processedIds,
      );
    }
  }

  /// Get Downline Count
  Future<int> _getDownlineCount(String userId) async {
    final snapshot =
        await _users.where('parentId', isEqualTo: userId).count().get();
    return snapshot.count ?? 0;
  }

  /// Get Total Sales
  Future<double> _getTotalSales(String userId) async {
    final commissions = await _commissions
        .where('associateId', isEqualTo: userId)
        .where('level', isEqualTo: 1) // Direct sales only
        .get();

    double total = 0;
    for (var doc in commissions.docs) {
      final data = doc.data() as Map<String, dynamic>;
      total += ((data['saleAmount'] ?? 0) as num).toDouble();
    }
    return total;
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
      final userDoc = await _users.doc(associateId).get();
      final userData = userDoc.data() as Map<String, dynamic>?;

      final payout = PayoutModel(
        id: '',
        userId: associateId,
        userName: (userData?['name'] as String?) ?? '',
        amount: amount,
        status: AppConstants.payoutStatusRequested,
        requestedAt: DateTime.now(),
        requestedVia: 'app',
        paymentMethod: paymentMethod,
        commissionIds: commissionIds,
        bankAccountNumber: bankDetails.accountNumber,
        bankIfscCode: bankDetails.ifscCode,
        bankName: bankDetails.bankName,
        upiId: bankDetails.upiId,
      );

      await _payouts.add(payout.toJson());

      AppLogger.info('Payout requested: ₹$amount for $associateId');
    } catch (e, stackTrace) {
      AppLogger.error('Payout request failed', e, stackTrace);
      rethrow;
    }
  }

  /// Check if User Can Upgrade Rank
  Future<bool> canUpgradeRank(String userId) async {
    final userDoc = await _users.doc(userId).get();
    if (!userDoc.exists) return false;

    final userData = userDoc.data() as Map<String, dynamic>;
    final currentRank =
        userData['rank'] as String? ?? AppConstants.rankAssociate;

    // Get current rank index
    final currentIndex = AppConstants.rankOrder.indexOf(currentRank);
    if (currentIndex == -1 ||
        currentIndex >= AppConstants.rankOrder.length - 1) {
      return false; // Already at max rank
    }

    // Calculate total sales
    final totalSales = await _getTotalSales(userId);

    // Get target for next rank
    final nextRank = AppConstants.rankOrder[currentIndex + 1];
    final targetAmount = getRankTarget(nextRank);

    return totalSales >= targetAmount;
  }

  /// Upgrade User Rank
  Future<void> upgradeRank(String userId) async {
    final userDoc = await _users.doc(userId).get();
    if (!userDoc.exists) return;

    final userData = userDoc.data() as Map<String, dynamic>;
    final currentRank =
        userData['rank'] as String? ?? AppConstants.rankAssociate;

    final currentIndex = AppConstants.rankOrder.indexOf(currentRank);
    if (currentIndex == -1 ||
        currentIndex >= AppConstants.rankOrder.length - 1) {
      return; // Already at max rank
    }

    final nextRank = AppConstants.rankOrder[currentIndex + 1];

    await _users.doc(userId).update({
      'rank': nextRank,
      'rankUpgradedAt': DateTime.now().toIso8601String(),
      'updatedAt': DateTime.now().toIso8601String(),
    });

    AppLogger.info('User $userId upgraded from $currentRank to $nextRank');
  }

  /// Create Commission Document
  Future<void> _createCommission(CommissionModel commission) async {
    final data = commission.toJson()..remove('id');
    await _commissions.add(data);
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

final associateCommissionsProvider =
    StreamProvider.family<List<CommissionModel>, String>(
  (ref, associateId) {
    final mlmService = ref.watch(mlmServiceProvider);
    return mlmService.getAssociateCommissions(associateId);
  },
);

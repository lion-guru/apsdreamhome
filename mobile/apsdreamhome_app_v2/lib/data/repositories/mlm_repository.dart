import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/services/api_service.dart';
import '../../core/services/database_helper.dart';

/// MLM Repository - Handles associate/MLM features
/// Genealogy, commissions, payouts, incentives
class MlmRepository {
  final ApiService _apiService;
  final DatabaseHelper _dbHelper;

  MlmRepository(this._apiService, this._dbHelper);

  /// Get MLM dashboard summary
  Future<MlmSummary> getSummary() async {
    // Try local first
    final localData = await _dbHelper.getMlmSummary();
    final local = localData != null ? MlmSummary.fromJson(localData) : null;
    
    // If online, fetch fresh
    if (await _apiService.isConnected()) {
      try {
        final response = await _apiService.get('/mlm/summary');
        final summary = MlmSummary.fromJson(response['data'] as Map<String, dynamic>);
        
        // Cache locally
        await _dbHelper.saveMlmSummary(summary.toJson());
        return summary;
      } catch (e) {
        return local ?? MlmSummary.empty();
      }
    }
    
    return local ?? MlmSummary.empty();
  }

  /// Get genealogy tree
  Future<GenealogyTree> getGenealogyTree({int? depth}) async {
    if (await _apiService.isConnected()) {
      try {
        final response = await _apiService.get(
          '/mlm/genealogy',
          queryParameters: depth != null ? {'depth': depth} : null,
        );
        return GenealogyTree.fromJson(response['data'] as Map<String, dynamic>);
      } catch (e) {
        return GenealogyTree.empty();
      }
    }
    return GenealogyTree.empty();
  }

  /// Get direct referrals
  Future<List<Referral>> getDirectReferrals() async {
    if (await _apiService.isConnected()) {
      try {
        final response = await _apiService.get('/mlm/referrals');
        return (response['data'] as List)
            .map((json) => Referral.fromJson(json as Map<String, dynamic>))
            .toList();
      } catch (e) {
        return [];
      }
    }
    return [];
  }

  /// Get commission history
  Future<List<Commission>> getCommissions({
    DateTime? fromDate,
    DateTime? toDate,
    String? status,
  }) async {
    // Try local first
    final local = await _dbHelper.getCommissions(
      fromDate: fromDate,
      toDate: toDate,
      status: status,
    );

    // If online, fetch fresh
    if (await _apiService.isConnected()) {
      try {
        final filters = <String, dynamic>{};
        if (fromDate != null) filters['from_date'] = fromDate.toIso8601String();
        if (toDate != null) filters['to_date'] = toDate.toIso8601String();
        if (status != null) filters['status'] = status;

        final response = await _apiService.get(
          '/mlm/commissions',
          queryParameters: filters,
        );
        final commissions = (response['data'] as List)
            .map((json) => Commission.fromJson(json as Map<String, dynamic>))
            .toList();

        // Cache locally
        await _dbHelper.saveCommissions(commissions.map((c) => c.toJson()).toList());
        return commissions;
      } catch (e) {
        return local.map((json) => Commission.fromJson(json)).toList();
      }
    }

    return local.map((json) => Commission.fromJson(json)).toList();
  }

  /// Get payout history
  Future<List<Payout>> getPayouts() async {
    // Try local first
    final local = await _dbHelper.getPayouts();

    // If online, fetch fresh
    if (await _apiService.isConnected()) {
      try {
        final response = await _apiService.get('/mlm/payouts');
        final payouts = (response['data'] as List)
            .map((json) => Payout.fromJson(json as Map<String, dynamic>))
            .toList();

        // Cache locally
        await _dbHelper.savePayouts(payouts.map((p) => p.toJson()).toList());
        return payouts;
      } catch (e) {
        return local.map((json) => Payout.fromJson(json)).toList();
      }
    }

    return local.map((json) => Payout.fromJson(json)).toList();
  }

  /// Request payout
  Future<bool> requestPayout(double amount, String method) async {
    if (await _apiService.isConnected()) {
      try {
        final response = await _apiService.post(
          '/mlm/request-payout',
          data: {
            'amount': amount,
            'method': method,
          },
        );
        return response['success'] == true;
      } catch (e) {
        return false;
      }
    }
    return false;
  }

  /// Get incentives/rewards
  Future<List<Incentive>> getIncentives() async {
    // Try local first
    final local = await _dbHelper.getIncentives();

    // If online, fetch fresh
    if (await _apiService.isConnected()) {
      try {
        final response = await _apiService.get('/mlm/incentives');
        final incentives = (response['data'] as List)
            .map((json) => Incentive.fromJson(json as Map<String, dynamic>))
            .toList();

        // Cache locally
        await _dbHelper.saveIncentives(incentives.map((i) => i.toJson()).toList());
        return incentives;
      } catch (e) {
        return local.map((json) => Incentive.fromJson(json)).toList();
      }
    }

    return local.map((json) => Incentive.fromJson(json)).toList();
  }

  /// Get MLM documents
  Future<List<MlmDocument>> getDocuments() async {
    if (await _apiService.isConnected()) {
      try {
        final response = await _apiService.get('/mlm/documents');
        return (response['data'] as List)
            .map((json) => MlmDocument.fromJson(json as Map<String, dynamic>))
            .toList();
      } catch (e) {
        return [];
      }
    }
    return [];
  }

  /// Upload KYC document
  Future<bool> uploadDocument(String filePath, String documentType) async {
    if (await _apiService.isConnected()) {
      try {
        final response = await _apiService.uploadDocument(
          filePath,
          documentType,
        );
        return response['success'] == true;
      } catch (e) {
        return false;
      }
    }
    return false;
  }

  /// Get business breakdown
  Future<BusinessBreakdown> getBusinessBreakdown() async {
    if (await _apiService.isConnected()) {
      try {
        final response = await _apiService.get('/mlm/business-breakdown');
        return BusinessBreakdown.fromJson(response['data'] as Map<String, dynamic>);
      } catch (e) {
        return BusinessBreakdown.empty();
      }
    }
    return BusinessBreakdown.empty();
  }

  /// Get team performance
  Future<TeamPerformance> getTeamPerformance() async {
    if (await _apiService.isConnected()) {
      try {
        final response = await _apiService.get('/mlm/team-performance');
        return TeamPerformance.fromJson(response['data'] as Map<String, dynamic>);
      } catch (e) {
        return TeamPerformance.empty();
      }
    }
    return TeamPerformance.empty();
  }

  /// Get next rank progress
  Future<RankProgress> getNextRankProgress() async {
    final summary = await getSummary();
    return summary.nextRankProgress ?? RankProgress.empty();
  }

  /// Get my team data (direct referrals + stats)
  Future<Map<String, dynamic>> getMyTeam() async {
    if (await _apiService.isConnected()) {
      try {
        final response = await _apiService.get('/mlm/my-team');
        return response['data'] as Map<String, dynamic>;
      } catch (e) {
        return {
          'direct_referrals': <Referral>[],
          'stats': {
            'total_team_size': 0,
            'direct_referrals': 0,
            'active_members': 0,
            'inactive_members': 0,
            'recent_joinings_30d': 0,
            'total_team_business': 0.0,
          }
        };
      }
    }
    return {
      'direct_referrals': <Referral>[],
      'stats': {
        'total_team_size': 0,
        'direct_referrals': 0,
        'active_members': 0,
        'inactive_members': 0,
        'recent_joinings_30d': 0,
        'total_team_business': 0.0,
      }
    };
  }

  /// Get rank progress
  Future<Map<String, dynamic>> getRankProgress() async {
    if (await _apiService.isConnected()) {
      try {
        final response = await _apiService.get('/mlm/rank-progress');
        return response['data'] as Map<String, dynamic>;
      } catch (e) {
        return {
          'current_rank': 'Associate',
          'total_sales': 0.0,
          'total_commission': 0.0,
          'direct_count': 0,
          'next_rank': null,
          'sales_progress_pct': 0.0,
          'commission_progress_pct': 0.0,
          'directs_progress_pct': 0.0,
          'overall_progress_pct': 0.0,
          'sales_remaining': 0.0,
          'commission_remaining': 0.0,
          'directs_remaining': 0,
        };
      }
    }
    return {
      'current_rank': 'Associate',
      'total_sales': 0.0,
      'total_commission': 0.0,
      'direct_count': 0,
      'next_rank': null,
      'sales_progress_pct': 0.0,
      'commission_progress_pct': 0.0,
      'directs_progress_pct': 0.0,
      'overall_progress_pct': 0.0,
      'sales_remaining': 0.0,
      'commission_remaining': 0.0,
      'directs_remaining': 0,
    };
  }
}

/// MLM Summary Model
class MlmSummary {
  final double totalEarnings;
  final double currentBalance;
  final double thisMonthEarnings;
  final int totalReferrals;
  final int activeReferrals;
  final String currentRank;
  final RankProgress? nextRankProgress;

  MlmSummary({
    required this.totalEarnings,
    required this.currentBalance,
    required this.thisMonthEarnings,
    required this.totalReferrals,
    required this.activeReferrals,
    required this.currentRank,
    this.nextRankProgress,
  });

  factory MlmSummary.fromJson(Map<String, dynamic> json) {
    return MlmSummary(
      totalEarnings: (json['total_earnings'] as num?)?.toDouble()
          ?? (json['business_volume'] as num?)?.toDouble() ?? 0.0,
      currentBalance: (json['current_balance'] as num?)?.toDouble() ?? 0.0,
      thisMonthEarnings: (json['this_month_earnings'] as num?)?.toDouble() ?? 0.0,
      totalReferrals: (json['total_referrals'] as int?)
          ?? (json['team_size'] as int?) ?? 0,
      activeReferrals: (json['active_referrals'] as int?)
          ?? (json['active_members'] as int?) ?? 0,
      currentRank: (json['current_rank'] as String?)
          ?? (json['rank'] as String?) ?? 'Member',
      nextRankProgress: (json['next_rank_progress'] ?? json['next_rank_info']) != null
          ? RankProgress.fromJson(
              (json['next_rank_progress'] ?? json['next_rank_info']) as Map<String, dynamic>)
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'total_earnings': totalEarnings,
      'current_balance': currentBalance,
      'this_month_earnings': thisMonthEarnings,
      'total_referrals': totalReferrals,
      'active_referrals': activeReferrals,
      'current_rank': currentRank,
      'next_rank_progress': nextRankProgress?.toJson(),
    };
  }

  factory MlmSummary.empty() {
    return MlmSummary(
      totalEarnings: 0,
      currentBalance: 0,
      thisMonthEarnings: 0,
      totalReferrals: 0,
      activeReferrals: 0,
      currentRank: 'Member',
    );
  }
}

/// Rank Progress
class RankProgress {
  final String nextRank;
  final double currentPoints;
  final double requiredPoints;
  final double progressPercentage;

  RankProgress({
    required this.nextRank,
    required this.currentPoints,
    required this.requiredPoints,
    required this.progressPercentage,
  });

  factory RankProgress.fromJson(Map<String, dynamic> json) {
    return RankProgress(
      nextRank: (json['next_rank'] as String?) ?? '',
      currentPoints: (json['current_points'] as num?)?.toDouble()
          ?? (json['total_sales'] as num?)?.toDouble() ?? 0.0,
      requiredPoints: (json['required_points'] as num?)?.toDouble()
          ?? (json['required_bv'] as num?)?.toDouble() ?? 1.0,
      progressPercentage: (json['progress_percentage'] as num?)?.toDouble() ?? 0.0,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'next_rank': nextRank,
      'current_points': currentPoints,
      'required_points': requiredPoints,
      'progress_percentage': progressPercentage,
    };
  }

  factory RankProgress.empty() {
    return RankProgress(
      nextRank: '',
      currentPoints: 0,
      requiredPoints: 1,
      progressPercentage: 0,
    );
  }
}

/// Genealogy Tree
class GenealogyTree {
  final List<GenealogyNode> nodes;
  final int totalLevels;

  GenealogyTree({
    required this.nodes,
    required this.totalLevels,
  });

  factory GenealogyTree.fromJson(Map<String, dynamic> json) {
    return GenealogyTree(
      nodes: (json['nodes'] as List? ?? [])
          .map((n) => GenealogyNode.fromJson(n as Map<String, dynamic>))
          .toList(),
      totalLevels: (json['total_levels'] as int?) ?? 0,
    );
  }

  factory GenealogyTree.empty() {
    return GenealogyTree(nodes: [], totalLevels: 0);
  }
}

/// Genealogy Node
class GenealogyNode {
  final String id;
  final String name;
  final String? parentId;
  final int level;
  final String rank;
  final bool isActive;
  final int directCount;
  final int totalCount;
  final double totalCommission;
  final double monthlyCommission;
  final DateTime? joinedAt;

  GenealogyNode({
    required this.id,
    required this.name,
    this.parentId,
    required this.level,
    required this.rank,
    required this.isActive,
    this.directCount = 0,
    this.totalCount = 0,
    this.totalCommission = 0.0,
    this.monthlyCommission = 0.0,
    this.joinedAt,
  });

  factory GenealogyNode.fromJson(Map<String, dynamic> json) {
    return GenealogyNode(
      id: (json['id'] as String?) ?? '',
      name: (json['name'] as String?) ?? '',
      parentId: json['parent_id'] as String?,
      level: (json['level'] as int?) ?? 0,
      rank: (json['rank'] as String?) ?? 'Member',
      isActive: (json['is_active'] as bool?) ?? false,
      directCount: (json['direct_count'] as int?) ?? 0,
      totalCount: (json['total_count'] as int?) ?? 0,
      totalCommission: (json['total_commission'] as num?)?.toDouble() ?? 0.0,
      monthlyCommission: (json['monthly_commission'] as num?)?.toDouble() ?? 0.0,
      joinedAt: json['joined_at'] != null ? DateTime.tryParse(json['joined_at'] as String) : null,
    );
  }
}

/// Referral
class Referral {
  final String id;
  final String name;
  final String phone;
  final DateTime joinDate;
  final String status;
  final double totalBusiness;

  Referral({
    required this.id,
    required this.name,
    required this.phone,
    required this.joinDate,
    required this.status,
    required this.totalBusiness,
  });

  factory Referral.fromJson(Map<String, dynamic> json) {
    return Referral(
      id: (json['id'] as String?) ?? '',
      name: (json['name'] as String?) ?? '',
      phone: (json['phone'] as String?) ?? '',
      joinDate: DateTime.parse((json['join_date'] as String?) ?? DateTime.now().toIso8601String()),
      status: (json['status'] as String?) ?? 'pending',
      totalBusiness: (json['total_business'] as num?)?.toDouble() ?? 0.0,
    );
  }
}

/// Commission
class Commission {
  final String id;
  final String type;
  final double amount;
  final String description;
  final String status;
  final DateTime date;
  final String? notes;

  Commission({
    required this.id,
    required this.type,
    required this.amount,
    required this.description,
    required this.status,
    required this.date,
    this.notes,
  });

  factory Commission.fromJson(Map<String, dynamic> json) {
    return Commission(
      id: '${json['id'] ?? ''}',
      type: (json['type'] as String?) ?? '',
      amount: (json['amount'] as num?)?.toDouble() ?? 0.0,
      description: (json['description'] as String?) ?? '',
      status: (json['status'] as String?) ?? 'pending',
      date: DateTime.tryParse((json['date'] as String?) ?? '') ?? DateTime.now(),
      notes: json['notes'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'type': type,
      'amount': amount,
      'description': description,
      'status': status,
      'date': date.toIso8601String(),
      'notes': notes,
    };
  }
}

/// Payout
class Payout {
  final String id;
  final double amount;
  final String method;
  final String status;
  final DateTime requestedAt;
  final DateTime? processedAt;

  Payout({
    required this.id,
    required this.amount,
    required this.method,
    required this.status,
    required this.requestedAt,
    this.processedAt,
  });

  factory Payout.fromJson(Map<String, dynamic> json) {
    return Payout(
      id: (json['id'] as String?) ?? '',
      amount: (json['amount'] as num?)?.toDouble() ?? 0.0,
      method: (json['method'] as String?) ?? '',
      status: (json['status'] as String?) ?? 'pending',
      requestedAt: DateTime.parse((json['requested_at'] as String?) ?? DateTime.now().toIso8601String()),
      processedAt: json['processed_at'] != null
          ? DateTime.parse(json['processed_at'] as String)
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'amount': amount,
      'method': method,
      'status': status,
      'requested_at': requestedAt.toIso8601String(),
      'processed_at': processedAt?.toIso8601String(),
    };
  }
}

/// Incentive
class Incentive {
  final String id;
  final String name;
  final String description;
  final double value;
  final DateTime? achievedAt;
  final String status;

  Incentive({
    required this.id,
    required this.name,
    required this.description,
    required this.value,
    this.achievedAt,
    required this.status,
  });

  factory Incentive.fromJson(Map<String, dynamic> json) {
    return Incentive(
      id: (json['id'] as String?) ?? '',
      name: (json['name'] as String?) ?? '',
      description: (json['description'] as String?) ?? '',
      value: (json['value'] as num?)?.toDouble() ?? 0.0,
      achievedAt: json['achieved_at'] != null
          ? DateTime.parse(json['achieved_at'] as String)
          : null,
      status: (json['status'] as String?) ?? 'pending',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'description': description,
      'value': value,
      'achieved_at': achievedAt?.toIso8601String(),
      'status': status,
    };
  }
}

/// MLM Document
class MlmDocument {
  final String id;
  final String name;
  final String type;
  final String url;
  final DateTime uploadedAt;
  final String status;

  MlmDocument({
    required this.id,
    required this.name,
    required this.type,
    required this.url,
    required this.uploadedAt,
    required this.status,
  });

  factory MlmDocument.fromJson(Map<String, dynamic> json) {
    return MlmDocument(
      id: (json['id'] as String?) ?? '',
      name: (json['name'] as String?) ?? '',
      type: (json['type'] as String?) ?? '',
      url: (json['url'] as String?) ?? '',
      uploadedAt: DateTime.parse((json['uploaded_at'] as String?) ?? DateTime.now().toIso8601String()),
      status: (json['status'] as String?) ?? 'pending',
    );
  }
}

/// Business Breakdown
class BusinessBreakdown {
  final double directBusiness;
  final double teamBusiness;
  final double selfPurchase;
  final Map<String, double> monthlyBreakdown;

  BusinessBreakdown({
    required this.directBusiness,
    required this.teamBusiness,
    required this.selfPurchase,
    required this.monthlyBreakdown,
  });

  factory BusinessBreakdown.fromJson(Map<String, dynamic> json) {
    return BusinessBreakdown(
      directBusiness: (json['direct_business'] as num?)?.toDouble() ?? 0.0,
      teamBusiness: (json['team_business'] as num?)?.toDouble() ?? 0.0,
      selfPurchase: (json['self_purchase'] as num?)?.toDouble() ?? 0.0,
      monthlyBreakdown: Map<String, double>.from(
        (json['monthly_breakdown'] as Map<String, dynamic>?) ?? {},
      ),
    );
  }

  factory BusinessBreakdown.empty() {
    return BusinessBreakdown(
      directBusiness: 0,
      teamBusiness: 0,
      selfPurchase: 0,
      monthlyBreakdown: {},
    );
  }
}

/// Team Performance
class TeamPerformance {
  final int activeMembers;
  final int newJoiningsThisMonth;
  final double teamAverageBusiness;
  final List<TopPerformer> topPerformers;

  TeamPerformance({
    required this.activeMembers,
    required this.newJoiningsThisMonth,
    required this.teamAverageBusiness,
    required this.topPerformers,
  });

  factory TeamPerformance.fromJson(Map<String, dynamic> json) {
    return TeamPerformance(
      activeMembers: (json['active_members'] as int?) ?? 0,
      newJoiningsThisMonth: (json['new_joinings_this_month'] as int?) ?? 0,
      teamAverageBusiness: (json['team_average_business'] as num?)?.toDouble() ?? 0.0,
      topPerformers: (json['top_performers'] as List? ?? [])
          .map((p) => TopPerformer.fromJson(p as Map<String, dynamic>))
          .toList(),
    );
  }

  factory TeamPerformance.empty() {
    return TeamPerformance(
      activeMembers: 0,
      newJoiningsThisMonth: 0,
      teamAverageBusiness: 0,
      topPerformers: [],
    );
  }
}

/// Top Performer
class TopPerformer {
  final String id;
  final String name;
  final double business;
  final int rank;

  TopPerformer({
    required this.id,
    required this.name,
    required this.business,
    required this.rank,
  });

  factory TopPerformer.fromJson(Map<String, dynamic> json) {
    return TopPerformer(
      id: (json['id'] as String?) ?? '',
      name: (json['name'] as String?) ?? '',
      business: (json['business'] as num?)?.toDouble() ?? 0.0,
      rank: (json['rank'] as int?) ?? 0,
    );
  }
}

/// Provider for MlmRepository
final mlmRepositoryProvider = Provider<MlmRepository>((ref) {
  final apiService = ApiService();
  final dbHelper = DatabaseHelper();
  return MlmRepository(apiService, dbHelper);
});

/// Provider for MLM summary
final mlmSummaryProvider = FutureProvider.autoDispose<MlmSummary>((ref) async {
  final repository = ref.watch(mlmRepositoryProvider);
  return await repository.getSummary();
});

/// Provider for genealogy tree
final genealogyTreeProvider = FutureProvider.autoDispose
    .family<GenealogyTree, int?>((ref, depth) async {
  final repository = ref.watch(mlmRepositoryProvider);
  return await repository.getGenealogyTree(depth: depth);
});

/// Provider for commissions
final commissionsProvider = FutureProvider.autoDispose
    .family<List<Commission>, Map<String, dynamic>?>((
  ref,
  filters,
) async {
  final repository = ref.watch(mlmRepositoryProvider);
  return await repository.getCommissions(
    fromDate: filters?['from_date'] as DateTime?,
    toDate: filters?['to_date'] as DateTime?,
    status: filters?['status'] as String?,
  );
});

/// Provider for payouts
final payoutsProvider = FutureProvider.autoDispose<List<Payout>>((ref) async {
  final repository = ref.watch(mlmRepositoryProvider);
  return await repository.getPayouts();
});

/// Provider for incentives
final incentivesProvider = FutureProvider.autoDispose<List<Incentive>>((ref) async {
  final repository = ref.watch(mlmRepositoryProvider);
  return await repository.getIncentives();
});

/// Provider for MLM documents
final mlmDocumentsProvider = FutureProvider.autoDispose<List<MlmDocument>>((ref) async {
  final repository = ref.watch(mlmRepositoryProvider);
  return await repository.getDocuments();
});

/// Provider for business breakdown
final businessBreakdownProvider = FutureProvider.autoDispose<BusinessBreakdown>((ref) async {
  final repository = ref.watch(mlmRepositoryProvider);
  return await repository.getBusinessBreakdown();
});

/// Provider for team performance
final teamPerformanceProvider = FutureProvider.autoDispose<TeamPerformance>((ref) async {
  final repository = ref.watch(mlmRepositoryProvider);
  return await repository.getTeamPerformance();
});

/// Provider for direct referrals
final directReferralsProvider = FutureProvider.autoDispose<List<Referral>>((ref) async {
  final repository = ref.watch(mlmRepositoryProvider);
  return await repository.getDirectReferrals();
});

/// Provider for my team data
final myTeamProvider = FutureProvider.autoDispose<Map<String, dynamic>>((ref) async {
  final repository = ref.watch(mlmRepositoryProvider);
  return await repository.getMyTeam();
});

/// Provider for rank progress
final rankProgressProvider = FutureProvider.autoDispose<Map<String, dynamic>>((ref) async {
  final repository = ref.watch(mlmRepositoryProvider);
  return await repository.getRankProgress();
});

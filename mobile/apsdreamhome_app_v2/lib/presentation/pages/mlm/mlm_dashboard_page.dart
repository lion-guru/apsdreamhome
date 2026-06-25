import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/theme/app_theme.dart';
import '../../../data/models/commission_model.dart';
import '../../../data/models/user_model.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/providers/connectivity_provider.dart';
import '../../providers/commission_provider.dart';
import '../../../data/repositories/mlm_repository.dart' hide commissionsProvider;
import '../../widgets/common_widgets.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/payout_request_dialog.dart';
import '../../widgets/status_badge.dart';

class MLMDashboardPage extends ConsumerWidget {
  const MLMDashboardPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(authProvider);
    final commissionsAsync = ref.watch(commissionsProvider);
    final connectivity = ref.watch(connectivityProvider);
    final mlmSummaryAsync = ref.watch(mlmSummaryProvider);
    final rankProgressAsync = ref.watch(rankProgressProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('MLM Dashboard'),
        actions: [
          IconButton(
            onPressed: connectivity.value ?? false
                ? () =>
                    ref.read(commissionsProvider.notifier).refreshCommissions()
                : null,
            icon: const Icon(Icons.refresh),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () =>
            ref.read(commissionsProvider.notifier).refreshCommissions(),
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(AppConstants.defaultPadding),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // User Rank Card
              if (user != null)
                _buildRankCard(context, user, rankProgressAsync.value),

              const SizedBox(height: 16),

              // Genealogy Tree Button
              ElevatedButton.icon(
                onPressed: () => context.push('/mlm/genealogy'),
                icon: const Icon(Icons.account_tree),
                label: const Text('View Team Genealogy'),
                style: ElevatedButton.styleFrom(
                  minimumSize: const Size(double.infinity, 50),
                  backgroundColor: AppTheme.primaryColor,
                  foregroundColor: Colors.white,
                ),
              ),

              const SizedBox(height: 12),

              OutlinedButton.icon(
                onPressed: () => context.push('/mlm/incentives'),
                icon: const Icon(Icons.stars),
                label: const Text('Monthly Rewards Dashboard'),
                style: OutlinedButton.styleFrom(
                  minimumSize: const Size(double.infinity, 50),
                  side: const BorderSide(color: AppTheme.primaryColor),
                  foregroundColor: AppTheme.primaryColor,
                ),
              ),

              const SizedBox(height: 12),

              OutlinedButton.icon(
                onPressed: () => context.push('/mlm/documents'),
                icon: const Icon(Icons.folder_shared),
                label: const Text('Digital Document Locker'),
                style: OutlinedButton.styleFrom(
                  minimumSize: const Size(double.infinity, 50),
                  side: const BorderSide(color: AppTheme.accentColor),
                  foregroundColor: AppTheme.accentColor,
                ),
              ),

              const SizedBox(height: 12),

              OutlinedButton.icon(
                onPressed: () => context.push('/mlm/site-visit'),
                icon: const Icon(Icons.map_outlined),
                label: const Text('GPS Site Visit Tracker'),
                style: OutlinedButton.styleFrom(
                  minimumSize: const Size(double.infinity, 50),
                  side: const BorderSide(color: Colors.greenAccent),
                  foregroundColor: Colors.greenAccent,
                ),
              ),

              const SizedBox(height: 12),

              OutlinedButton.icon(
                onPressed: () => context.push('/mlm/auto-payout'),
                icon: const Icon(Icons.payments_rounded),
                label: const Text('Auto Payout Panel'),
                style: OutlinedButton.styleFrom(
                  minimumSize: const Size(double.infinity, 50),
                  side: const BorderSide(color: Colors.orangeAccent),
                  foregroundColor: Colors.orangeAccent,
                ),
              ),

              const SizedBox(height: 12),

              OutlinedButton.icon(
                onPressed: () {
                  final pendingAmount = mlmSummaryAsync.value?.currentBalance ?? 0.0;
                  showDialog(
                    context: context,
                    builder: (context) =>
                        PayoutRequestDialog(maxAmount: pendingAmount),
                  );
                },
                icon: const Icon(Icons.request_quote),
                label: const Text('Request Payout'),
                style: OutlinedButton.styleFrom(
                  minimumSize: const Size(double.infinity, 50),
                  side: const BorderSide(color: Colors.lightBlueAccent),
                  foregroundColor: Colors.lightBlueAccent,
                ),
              ),

              const SizedBox(height: 16),

              // Commission Stats
              commissionsAsync.when(
                data: (commissions) =>
                    _buildCommissionStats(context, commissions, user),
                loading: () => const ShimmerCard(height: 120),
                error: (error, stack) => GlassCard(
                  child: Text('Error: ${error.toString()}'),
                ),
              ),

              const SizedBox(height: 24),

              // Recent Commissions
              Text(
                'Recent Commissions',
                style: Theme.of(context).textTheme.titleLarge?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
              ),
              const SizedBox(height: 16),

              commissionsAsync.when(
                data: (commissions) {
                  if (commissions.isEmpty) {
                    return const EmptyStateWidget(
                      title: 'No Commissions Yet',
                      subtitle: 'Start making sales to earn commissions',
                    );
                  }

                  return Column(
                    children: commissions.take(5).map((commission) {
                      return CommissionCard(commission: commission);
                    }).toList(),
                  );
                },
                loading: () => const Column(
                  children: [
                    ShimmerCard(height: 80),
                    SizedBox(height: 12),
                    ShimmerCard(height: 80),
                    SizedBox(height: 12),
                    ShimmerCard(height: 80),
                  ],
                ),
                error: (error, stack) => GlassCard(
                  child: Text('Error: ${error.toString()}'),
                ),
              ),

              const SizedBox(height: 24),

              // MLM Business Rules Info
              _buildBusinessRulesCard(context),

              const SizedBox(height: 20),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildRankCard(BuildContext context, User user, Map<String, dynamic>? rankData) {
    final progress = _calculateRankProgress(user, rankData);

    return GlassCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppTheme.accentColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(
                  Icons.emoji_events,
                  size: 28,
                  color: AppTheme.accentColor,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      user.rank,
                      style: Theme.of(context).textTheme.titleLarge?.copyWith(
                            fontWeight: FontWeight.bold,
                            color: AppTheme.primaryColor,
                          ),
                    ),
                    Text(
                      'Commission Rate: ${user.commissionRate.toStringAsFixed(1)}%',
                      style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                            color: AppTheme.accentColor,
                            fontWeight: FontWeight.w600,
                          ),
                    ),
                  ],
                ),
              ),
            ],
          ),

          const SizedBox(height: 16),

          // Progress Bar
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Target Progress',
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                          fontWeight: FontWeight.w600,
                        ),
                  ),
                  Text(
                    '${(progress * 100).toStringAsFixed(1)}%',
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                          color: AppTheme.primaryColor,
                          fontWeight: FontWeight.w600,
                        ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              LinearProgressIndicator(
                value: progress,
                backgroundColor: Colors.grey.shade300,
                valueColor:
                    const AlwaysStoppedAnimation<Color>(AppTheme.primaryColor),
              ),
              const SizedBox(height: 8),
              Text(
                'Target: ${_formatTarget(user.target)}',
                style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      color: Colors.grey.shade600,
                    ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildCommissionStats(
      BuildContext context, List<CommissionModel> commissions, User? user) {
    final totalCommission = commissions.fold<double>(0, (sum, commission) {
      return commission.status == 'paid' ? sum + commission.amount : sum;
    });

    final pendingCommission = commissions.fold<double>(0, (sum, commission) {
      return commission.status == 'pending' ? sum + commission.amount : sum;
    });

    final thisMonthCommission = commissions.where((commission) {
      if (commission.createdAt == null) return false;
      final now = DateTime.now();
      return commission.createdAt!.month == now.month &&
          commission.createdAt!.year == now.year;
    }).fold<double>(0, (sum, commission) => sum + commission.amount);

    return GlassCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Commission Overview',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.bold,
                ),
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Expanded(
                child: _buildStatItem(
                  context,
                  'Total Earned',
                  _formatAmount(totalCommission),
                  Colors.green,
                  Icons.trending_up,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: _buildStatItem(
                  context,
                  'Pending',
                  _formatAmount(pendingCommission),
                  Colors.orange,
                  Icons.hourglass_empty,
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          _buildStatItem(
            context,
            'This Month',
            _formatAmount(thisMonthCommission),
            AppTheme.primaryColor,
            Icons.calendar_today,
          ),
        ],
      ),
    );
  }

  Widget _buildStatItem(
    BuildContext context,
    String label,
    String value,
    Color color,
    IconData icon,
  ) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, size: 20, color: color),
              const SizedBox(width: 8),
              Text(
                label,
                style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      color: color,
                      fontWeight: FontWeight.w600,
                    ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            value,
            style: Theme.of(context).textTheme.titleLarge?.copyWith(
                  color: color,
                  fontWeight: FontWeight.bold,
                ),
          ),
        ],
      ),
    );
  }

  Widget _buildBusinessRulesCard(BuildContext context) {
    return GlassCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'MLM Business Rules',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.bold,
                ),
          ),
          const SizedBox(height: 16),

          Text(
            'Differential Commission System',
            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                  fontWeight: FontWeight.w600,
                  color: AppTheme.primaryColor,
                ),
          ),
          const SizedBox(height: 8),

          Text(
            'Your commission = (Your Rank %) - (Downline Rank %)',
            style: Theme.of(context).textTheme.bodySmall?.copyWith(
                  color: Colors.grey.shade600,
                ),
          ),

          const SizedBox(height: 16),

          // Rank Tiers
          ...AppConstants.commissionRates.entries.map((entry) {
            return Padding(
              padding: const EdgeInsets.only(bottom: 8),
              child: Row(
                children: [
                  Container(
                    width: 8,
                    height: 8,
                    decoration: BoxDecoration(
                      color: _getRankColor(entry.key),
                      borderRadius: BorderRadius.circular(4),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      '${entry.key}: ${entry.value.toStringAsFixed(1)}%',
                      style: Theme.of(context).textTheme.bodySmall,
                    ),
                  ),
                  Text(
                    'Target: ${_formatTarget(AppConstants.targets[entry.key] ?? 0)}',
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: Colors.grey.shade600,
                        ),
                  ),
                ],
              ),
            );
          }),
        ],
      ),
    );
  }

  double _calculateRankProgress(User user, Map<String, dynamic>? rankData) {
    if (rankData != null) {
      final overallProgress = (rankData['overall_progress_pct'] as num?)?.toDouble();
      if (overallProgress != null) {
        return (overallProgress / 100).clamp(0.0, 1.0);
      }
    }
    // Fallback: calculate from user target vs current sales
    if (user.target > 0) {
      return (user.totalSales / user.target).clamp(0.0, 1.0);
    }
    return 0.0;
  }

  String _formatTarget(double target) {
    if (target >= 10000000) {
      return '${(target / 10000000).toStringAsFixed(1)} Cr';
    } else if (target >= 100000) {
      return '${(target / 100000).toStringAsFixed(0)} L';
    } else {
      return target.toStringAsFixed(0);
    }
  }

  String _formatAmount(double amount) {
    if (amount >= 10000000) {
      return '₹${(amount / 10000000).toStringAsFixed(2)} Cr';
    } else if (amount >= 100000) {
      return '₹${(amount / 100000).toStringAsFixed(1)} L';
    } else if (amount >= 1000) {
      return '₹${(amount / 1000).toStringAsFixed(0)} K';
    } else {
      return '₹${amount.toStringAsFixed(0)}';
    }
  }

  Color _getRankColor(String rank) {
    switch (rank) {
      case 'Associate':
        return Colors.blue;
      case 'Sr. Associate':
        return Colors.green;
      case 'BDM':
        return Colors.orange;
      case 'Sr. BDM':
        return Colors.purple;
      case 'Vice President':
        return Colors.red;
      case 'President':
        return Colors.brown;
      case 'Site Manager':
        return Colors.black;
      default:
        return Colors.grey;
    }
  }
}

class CommissionCard extends StatelessWidget {
  const CommissionCard({
    super.key,
    required this.commission,
  });

  final CommissionModel commission;

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: _getStatusColor(commission.status).withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Icon(
                _getStatusIcon(commission.status),
                size: 20,
                color: _getStatusColor(commission.status),
              ),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    commission.type,
                    style: Theme.of(context).textTheme.titleSmall?.copyWith(
                          fontWeight: FontWeight.w600,
                        ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    '${commission.percentage.toStringAsFixed(1)}% commission',
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: Colors.grey.shade600,
                        ),
                  ),
                  Text(
                    commission.createdAt != null
                        ? _formatDate(commission.createdAt!.toIso8601String())
                        : '',
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: Colors.grey.shade500,
                        ),
                  ),
                ],
              ),
            ),
            Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Text(
                  _formatAmount(commission.amount),
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                        color: AppTheme.primaryColor,
                      ),
                ),
                const SizedBox(height: 4),
                StatusBadge(
                  status: commission.status,
                  color: _getStatusColor(commission.status),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return Colors.orange;
      case 'approved':
        return Colors.blue;
      case 'paid':
        return Colors.green;
      default:
        return Colors.grey;
    }
  }

  IconData _getStatusIcon(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return Icons.hourglass_empty;
      case 'approved':
        return Icons.check_circle_outline;
      case 'paid':
        return Icons.payments;
      default:
        return Icons.help_outline;
    }
  }

  String _formatDate(String dateString) {
    final date = DateTime.parse(dateString);
    return '${date.day}/${date.month}/${date.year}';
  }

  String _formatAmount(double amount) {
    if (amount >= 10000000) {
      return '₹${(amount / 10000000).toStringAsFixed(2)} Cr';
    } else if (amount >= 100000) {
      return '₹${(amount / 100000).toStringAsFixed(1)} L';
    } else if (amount >= 1000) {
      return '₹${(amount / 1000).toStringAsFixed(0)} K';
    } else {
      return '₹${amount.toStringAsFixed(0)}';
    }
  }
}

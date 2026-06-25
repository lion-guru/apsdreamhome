import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/utils/responsive_helper.dart';
import '../../../data/services/auth_service.dart';
import '../../../data/repositories/mlm_repository.dart';
import '../../../data/models/user_model.dart';
import '../../widgets/app_widgets.dart';

class AssociateDashboardPage extends ConsumerWidget {
  const AssociateDashboardPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final userAsync = ref.watch(currentUserDataProvider);

    return Scaffold(
      body: userAsync.when(
        data: (user) {
          if (user == null) {
            return AppWidgets.errorWidget(
              message: 'User not found',
              onRetry: () => ref.refresh(currentUserDataProvider),
            );
          }

          final commissionAsync = ref.watch(mlmSummaryProvider);
          final rankProgressAsync = ref.watch(rankProgressProvider);
          final recentCommissionsAsync = ref.watch(commissionsProvider(null));

          return CustomScrollView(
            slivers: [
              // App Bar
              SliverToBoxAdapter(
                child: _buildAppBar(context, user),
              ),

              // Stats Cards
              commissionAsync.when(
                data: (summary) => SliverToBoxAdapter(
                  child: _buildStatsCards(context, summary),
                ),
                loading: () => SliverToBoxAdapter(
                  child: AppWidgets.shimmerLoading(
                    child: Container(
                      height: 120,
                      margin: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(16),
                      ),
                    ),
                  ),
                ),
                error: (error, stack) => SliverToBoxAdapter(
                  child: AppWidgets.errorWidget(
                    message: error.toString(),
                    onRetry: () =>
                        ref.refresh(mlmSummaryProvider),
                  ),
                ),
              ),

              // Quick Actions
              SliverToBoxAdapter(
                child: _buildQuickActions(context),
              ),

              // Rank Progress (from API)
              rankProgressAsync.when(
                data: (rankData) => SliverToBoxAdapter(
                  child: _buildRankProgressFromAPI(context, rankData),
                ),
                loading: () => SliverToBoxAdapter(
                  child: AppWidgets.shimmerLoading(
                    child: Container(
                      height: 160,
                      margin: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(16),
                      ),
                    ),
                  ),
                ),
                error: (error, stack) => SliverToBoxAdapter(
                  child: _buildRankProgressFallback(context),
                ),
              ),

              // Recent Commissions Header
              SliverToBoxAdapter(
                child: AppWidgets.sectionHeader(
                  title: 'Recent Commissions',
                  onSeeAll: () => context.push('/associate/commission'),
                ),
              ),

              // Recent Commissions List
              recentCommissionsAsync.when(
                data: (commissions) {
                  if (commissions.isEmpty) {
                    return SliverToBoxAdapter(
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: AppWidgets.emptyState(
                          title: 'No Commissions Yet',
                          subtitle: 'Start making sales to earn commissions',
                          icon: Icons.account_balance_wallet_outlined,
                        ),
                      ),
                    );
                  }
                  return SliverToBoxAdapter(
                    child: _buildRecentCommissionsFromAPI(context, commissions),
                  );
                },
                loading: () => SliverToBoxAdapter(
                  child: ListView.builder(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    itemCount: 3,
                    itemBuilder: (context, index) {
                      return AppWidgets.shimmerLoading(
                        child: Container(
                          height: 80,
                          margin: const EdgeInsets.symmetric(
                            horizontal: 16,
                            vertical: 8,
                          ),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                      );
                    },
                  ),
                ),
                error: (error, stack) => SliverToBoxAdapter(
                  child: AppWidgets.errorWidget(
                    message: 'Failed to load commissions',
                    onRetry: () => ref.refresh(commissionsProvider(null)),
                  ),
                ),
              ),

              // Bottom Padding
              const SliverToBoxAdapter(
                child: SizedBox(height: 32),
              ),
            ],
          );
        },
        loading: () => const Center(
          child: CircularProgressIndicator(),
        ),
        error: (error, stack) => AppWidgets.errorWidget(
          message: error.toString(),
          onRetry: () => ref.refresh(currentUserDataProvider),
        ),
      ),
    );
  }

  Widget _buildAppBar(BuildContext context, User user) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
        ),
        borderRadius: BorderRadius.vertical(
          bottom: Radius.circular(24),
        ),
      ),
      child: SafeArea(
        child: Column(
          children: [
            // Top Row
            Row(
              children: [
                // Profile Image
                Container(
                  width: 60,
                  height: 60,
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(30),
                    border: Border.all(color: Colors.white, width: 3),
                  ),
                  child: user.profileImage != null
                      ? ClipRRect(
                          borderRadius: BorderRadius.circular(30),
                          child: Image.network(
                            user.profileImage!,
                            fit: BoxFit.cover,
                          ),
                        )
                      : const Icon(
                          Icons.person,
                          color: AppTheme.primaryColor,
                          size: 32,
                        ),
                ),

                const SizedBox(width: 16),

                // Name & Rank
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        user.name,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 4,
                        ),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.2),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text(
                          user.rank,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 12,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),

                // Notification
                IconButton(
                  onPressed: () => context.push('/notifications'),
                  icon: const Icon(
                    Icons.notifications_outlined,
                    color: Colors.white,
                  ),
                ),

                // Menu
                IconButton(
                  onPressed: () => context.push('/profile'),
                  icon: const Icon(
                    Icons.settings_outlined,
                    color: Colors.white,
                  ),
                ),
              ],
            ),

            const SizedBox(height: 24),

            // Referral Code
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.15),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: Colors.white.withValues(alpha: 0.3),
                ),
              ),
              child: Row(
                children: [
                  const Icon(
                    Icons.card_giftcard,
                    color: Colors.white,
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Your Referral Code',
                          style: TextStyle(
                            color: Colors.white70,
                            fontSize: 12,
                          ),
                        ),
                        Text(
                          user.referralCode ?? 'APSXXXXXX',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            letterSpacing: 1,
                          ),
                        ),
                      ],
                    ),
                  ),
                  ElevatedButton.icon(
                    onPressed: () {
                      // Copy referral code
                      AppWidgets.showSuccessSnackBar(
                        context,
                        'Referral code copied!',
                      );
                    },
                    icon: const Icon(Icons.copy, size: 16),
                    label: const Text('Copy'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.white,
                      foregroundColor: AppTheme.primaryColor,
                      padding: const EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 8,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatsCards(BuildContext context, MlmSummary summary) {
    final stats = [
      {
        'title': 'Total Earnings',
        'amount': summary.totalEarnings,
        'icon': Icons.account_balance_wallet_outlined,
        'color': AppTheme.primaryColor,
      },
      {
        'title': 'Pending (Balance)',
        'amount': summary.currentBalance,
        'icon': Icons.pending_actions_outlined,
        'color': AppTheme.warningColor,
      },
      {
        'title': 'Paid Out',
        'amount': summary.totalEarnings - summary.currentBalance,
        'icon': Icons.check_circle_outline,
        'color': AppTheme.successColor,
      },
      {
        'title': 'Total Referrals',
        'count': summary.totalReferrals,
        'icon': Icons.trending_up,
        'color': AppTheme.accentColor,
      },
    ];

    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      padding: const EdgeInsets.all(16),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        childAspectRatio: 1.3,
        crossAxisSpacing: 12,
        mainAxisSpacing: 12,
      ),
      itemCount: stats.length,
      itemBuilder: (context, index) {
        final stat = stats[index];
        final isAmount = stat.containsKey('amount');

        return Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            boxShadow: [
              BoxShadow(
                color: (stat['color'] as Color).withValues(alpha: 0.1),
                blurRadius: 10,
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: (stat['color'] as Color).withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(
                  stat['icon'] as IconData,
                  color: stat['color'] as Color,
                  size: 24,
                ),
              ),
              const Spacer(),
              if (isAmount)
                AppWidgets.priceTag(
                  amount: stat['amount'] as double,
                  prefix: '₹',
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                    color: stat['color'] as Color,
                  ),
                )
              else
                Text(
                  stat['count'].toString(),
                  style: TextStyle(
                    fontSize: ResponsiveHelper.fontSize(context, 24),
                    fontWeight: FontWeight.bold,
                    color: stat['color'] as Color,
                  ),
                ),
              const SizedBox(height: 4),
              Text(
                stat['title'] as String,
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.grey.shade600,
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildQuickActions(BuildContext context) {
    final actions = [
      {
        'icon': Icons.people_outline,
        'label': 'My Team',
        'route': '/associate/my-team',
        'color': AppTheme.primaryColor,
      },
      {
        'icon': Icons.account_tree_outlined,
        'label': 'Genealogy',
        'route': '/associate/genealogy',
        'color': AppTheme.secondaryColor,
      },
      {
        'icon': Icons.monetization_on_outlined,
        'label': 'Commissions',
        'route': '/associate/commission',
        'color': AppTheme.successColor,
      },
      {
        'icon': Icons.account_balance_outlined,
        'label': 'Payouts',
        'route': '/associate/payout',
        'color': AppTheme.warningColor,
      },
    ];

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Quick Actions',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.bold,
                ),
          ),
          const SizedBox(height: 12),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: actions.map((action) {
              return GestureDetector(
                onTap: () => context.push(action['route'] as String),
                child: Column(
                  children: [
                    Container(
                      width: 70,
                      height: 70,
                      decoration: BoxDecoration(
                        color:
                            (action['color'] as Color).withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Icon(
                        action['icon'] as IconData,
                        color: action['color'] as Color,
                        size: 32,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      action['label'] as String,
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              );
            }).toList(),
          ),
        ],
      ),
    );
  }

  Widget _buildRecentCommissionsFromAPI(BuildContext context, List<Commission> commissions) {
    return ListView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      padding: const EdgeInsets.symmetric(horizontal: 16),
      itemCount: commissions.length > 5 ? 5 : commissions.length,
      itemBuilder: (context, index) {
        final commission = commissions[index];
        final typeLabel = commission.type.isNotEmpty ? commission.type : 'Commission';
        final dateLabel = _formatDate(commission.date);

        return AppWidgets.customCard(
          child: Row(
            children: [
              Container(
                width: 50,
                height: 50,
                decoration: BoxDecoration(
                  color: AppTheme.successColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(
                  Icons.add_chart,
                  color: AppTheme.successColor,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      commission.description.isNotEmpty ? commission.description : typeLabel,
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      typeLabel,
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade600,
                      ),
                    ),
                  ],
                ),
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  AppWidgets.priceTag(
                    amount: commission.amount,
                    prefix: '+₹',
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: AppTheme.successColor,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    dateLabel,
                    style: TextStyle(
                      fontSize: 11,
                      color: Colors.grey.shade500,
                    ),
                  ),
                ],
              ),
            ],
          ),
        );
      },
    );
  }

  String _formatDate(DateTime date) {
    final now = DateTime.now();
    final diff = now.difference(date);
    if (diff.inDays == 0) return 'Today';
    if (diff.inDays == 1) return 'Yesterday';
    if (diff.inDays < 7) return '${diff.inDays} days ago';
    return '${date.day}/${date.month}/${date.year}';
  }

  String _formatCurrency(double amount) {
    if (amount >= 10000000) {
      return '${(amount / 10000000).toStringAsFixed(1)} Cr';
    } else if (amount >= 100000) {
      return '${(amount / 100000).toStringAsFixed(1)} L';
    } else if (amount >= 1000) {
      return '${(amount / 1000).toStringAsFixed(1)} K';
    }
    return amount.toStringAsFixed(0);
  }

  Widget _buildRankProgressFromAPI(BuildContext context, Map<String, dynamic> rankData) {
    final currentRank = rankData['current_rank'] as String? ?? 'Associate';
    final nextRank = rankData['next_rank'] as String?;
    final overallProgress = (rankData['overall_progress_pct'] as num?)?.toDouble() ?? 0.0;
    final salesRemaining = (rankData['sales_remaining'] as num?)?.toDouble() ?? 0.0;
    final commissionRemaining = (rankData['commission_remaining'] as num?)?.toDouble() ?? 0.0;
    final directsRemaining = (rankData['directs_remaining'] as int?) ?? 0;

    if (nextRank == null || overallProgress >= 100) {
      return AppWidgets.customCard(
        child: Column(
          children: [
            const Icon(Icons.emoji_events, color: AppTheme.successColor, size: 48),
            const SizedBox(height: 12),
            Text('Congratulations!', style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold)),
            const SizedBox(height: 4),
            Text('You have reached the highest rank - $currentRank!', textAlign: TextAlign.center),
          ],
        ),
      );
    }

    return AppWidgets.customCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('Rank Progress', style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold)),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(color: AppTheme.accentColor.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(20)),
                child: Text('${overallProgress.toStringAsFixed(0)}%', style: const TextStyle(color: AppTheme.accentColor, fontWeight: FontWeight.bold)),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(currentRank, style: const TextStyle(fontWeight: FontWeight.bold, color: AppTheme.primaryColor)),
              Text(nextRank, style: TextStyle(fontWeight: FontWeight.bold, color: Colors.grey.shade600)),
            ],
          ),
          const SizedBox(height: 8),
          ClipRRect(
            borderRadius: BorderRadius.circular(8),
            child: LinearProgressIndicator(
              value: (overallProgress / 100).clamp(0.0, 1.0),
              backgroundColor: Colors.grey.shade200,
              valueColor: const AlwaysStoppedAnimation<Color>(AppTheme.primaryColor),
              minHeight: 10,
            ),
          ),
          const SizedBox(height: 12),
          Text('₹${_formatCurrency(salesRemaining)} sales • ₹${_formatCurrency(commissionRemaining)} commission • $directsRemaining directs to reach $nextRank',
              style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
        ],
      ),
    );
  }

  Widget _buildRankProgressFallback(BuildContext context) {
    return AppWidgets.customCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('Rank Progress', style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold)),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(color: AppTheme.accentColor.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(20)),
                child: const Text('--', style: TextStyle(color: AppTheme.accentColor, fontWeight: FontWeight.bold)),
              ),
            ],
          ),
          const SizedBox(height: 8),
          ClipRRect(
            borderRadius: BorderRadius.circular(8),
            child: LinearProgressIndicator(value: 0, backgroundColor: Colors.grey.shade200, valueColor: const AlwaysStoppedAnimation<Color>(AppTheme.primaryColor), minHeight: 10),
          ),
          const SizedBox(height: 8),
          Text('Load your rank data to see progress', style: TextStyle(fontSize: 12, color: Colors.grey.shade500)),
        ],
      ),
    );
  }
}

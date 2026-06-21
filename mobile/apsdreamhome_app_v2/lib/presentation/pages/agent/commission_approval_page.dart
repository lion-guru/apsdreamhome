import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/app_theme.dart';
import '../../../data/repositories/mlm_repository.dart';
import '../../widgets/app_widgets.dart';

/// Commission Approvals - list of commissions earned/pending
class CommissionApprovalPage extends ConsumerStatefulWidget {
  const CommissionApprovalPage({super.key});

  @override
  ConsumerState<CommissionApprovalPage> createState() => _CommissionApprovalPageState();
}

class _CommissionApprovalPageState extends ConsumerState<CommissionApprovalPage>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final commissionAsync = ref.watch(mlmSummaryProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('My Earnings'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: Colors.white,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white60,
          tabs: const [
            Tab(text: 'Overview'),
            Tab(text: 'Commissions'),
            Tab(text: 'Payouts'),
            Tab(text: 'Incentives'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          _buildOverviewTab(commissionAsync),
          _buildCommissionsTab(),
          _buildPayoutsTab(),
          _buildIncentivesTab(),
        ],
      ),
    );
  }

  Widget _buildOverviewTab(AsyncValue<MlmSummary> summaryAsync) {
    return RefreshIndicator(
      onRefresh: () async => ref.refresh(mlmSummaryProvider),
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16),
        child: summaryAsync.when(
          data: (summary) => _buildOverviewContent(summary),
          loading: () => AppWidgets.shimmerLoading(
            child: Container(
              height: 400,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
              ),
            ),
          ),
          error: (error, stack) => AppWidgets.errorWidget(
            message: error.toString(),
            onRetry: () => ref.refresh(mlmSummaryProvider),
          ),
        ),
      ),
    );
  }

  Widget _buildOverviewContent(MlmSummary summary) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Summary cards
        Row(
          children: [
            Expanded(
              child: _summaryCard(
                'Total Earned',
                summary.totalEarnings,
                Icons.account_balance_wallet,
                AppTheme.primaryColor,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _summaryCard(
                'Pending',
                summary.currentBalance,
                Icons.pending_actions,
                AppTheme.warningColor,
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: _summaryCard(
                'Paid Out',
                summary.totalEarnings - summary.currentBalance,
                Icons.check_circle,
                AppTheme.successColor,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _summaryCard(
                'Referrals',
                summary.totalReferrals.toDouble(),
                Icons.people,
                AppTheme.accentColor,
                isCount: true,
              ),
            ),
          ],
        ),

        const SizedBox(height: 24),

        // Earnings Breakdown
        ...[
          const Text(
            'Earnings Breakdown',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 12),
          _breakdownRow('Total Earned', summary.totalEarnings, 100),
          if (summary.currentBalance > 0)
            _breakdownRow('Pending Balance', summary.currentBalance,
                summary.totalEarnings > 0 ? (summary.currentBalance / summary.totalEarnings * 100) : 0),
          _breakdownRow('This Month', summary.thisMonthEarnings,
              summary.totalEarnings > 0 ? (summary.thisMonthEarnings / summary.totalEarnings * 100) : 0),
          const SizedBox(height: 24),
        ],

        // Quick stats
        const Text(
          'Activity',
          style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 12),
        _activityRow(Icons.trending_up, 'Active Referrals', '${summary.activeReferrals}'),
        _activityRow(Icons.star, 'Rank', summary.currentRank),
        _activityRow(Icons.card_giftcard, 'Total Referrals', '${summary.totalReferrals}'),
      ],
    );
  }

  Widget _summaryCard(String title, double value, IconData icon, Color color, {bool isCount = false}) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: color.withValues(alpha: 0.1),
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
              color: color.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: color, size: 22),
          ),
          const SizedBox(height: 12),
          if (isCount)
            Text(
              '${value.toInt()}',
              style: TextStyle(
                fontSize: 22,
                fontWeight: FontWeight.bold,
                color: color,
              ),
            )
          else
            AppWidgets.priceTag(
              amount: value,
              prefix: '₹',
              style: TextStyle(
                fontSize: 22,
                fontWeight: FontWeight.bold,
                color: color,
              ),
            ),
          const SizedBox(height: 4),
          Text(
            title,
            style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
          ),
        ],
      ),
    );
  }

  Widget _breakdownRow(String label, double amount, double percent) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                label.replaceAll('_', ' ').toUpperCase(),
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  color: Colors.grey.shade700,
                ),
              ),
              AppWidgets.priceTag(
                amount: amount,
                prefix: '₹',
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.bold,
                  color: AppTheme.primaryColor,
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          ClipRRect(
            borderRadius: BorderRadius.circular(4),
            child: LinearProgressIndicator(
              value: percent / 100,
              backgroundColor: Colors.grey.shade200,
              valueColor: const AlwaysStoppedAnimation<Color>(AppTheme.primaryColor),
              minHeight: 6,
            ),
          ),
        ],
      ),
    );
  }

  Widget _activityRow(IconData icon, String label, String value) {
    return AppWidgets.infoRow(icon: icon, label: label, value: value);
  }

  Widget _buildCommissionsTab() {
    final commissionsAsync = ref.watch(commissionsProvider(null));

    return RefreshIndicator(
      onRefresh: () async => ref.refresh(commissionsProvider(null)),
      child: commissionsAsync.when(
        data: (commissions) {
          if (commissions.isEmpty) {
            return AppWidgets.emptyState(
              title: 'No commissions yet',
              subtitle: 'Commissions will appear here when you make sales',
              icon: Icons.monetization_on_outlined,
            );
          }

          return ListView.builder(
            padding: const EdgeInsets.all(12),
            itemCount: commissions.length,
            itemBuilder: (context, index) {
              final c = commissions[index];
              return _buildCommissionItem(c);
            },
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, stack) => AppWidgets.errorWidget(
          message: error.toString(),
          onRetry: () => ref.refresh(commissionsProvider(null)),
        ),
      ),
    );
  }

  Widget _buildCommissionItem(dynamic commission) {
    final double amount = ((commission.amount ?? commission.commissionAmount ?? 0) as num).toDouble();
    final String status = (commission.status ?? 'pending').toString();
    final statusColor = status == 'paid'
        ? AppTheme.successColor
        : status == 'hold'
            ? AppTheme.errorColor
            : AppTheme.warningColor;
    final String colony = (commission.colonyName ?? commission.plotNumber ?? '').toString();
    final String type = (commission.type ?? commission.commissionType ?? 'direct').toString();
    final date = commission.createdAt;

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
            child: Icon(
              status == 'paid' ? Icons.check_circle : Icons.pending,
              color: statusColor,
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  colony.isNotEmpty ? colony : 'Commission',
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: AppTheme.infoColor.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        type.toUpperCase(),
                        style: const TextStyle(
                          fontSize: 9,
                          fontWeight: FontWeight.bold,
                          color: AppTheme.infoColor,
                        ),
                      ),
                    ),
                    const SizedBox(width: 6),
                    Text(
                      '${(date != null ? DateTime.tryParse(date.toString())?.day : 0) ?? 0}/${(date != null ? DateTime.tryParse(date.toString())?.month : 0) ?? 0}',
                      style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                    ),
                  ],
                ),
              ],
            ),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              AppWidgets.priceTag(
                amount: amount,
                prefix: '+₹',
                style: const TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: AppTheme.successColor,
                ),
              ),
              const SizedBox(height: 4),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: statusColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Text(
                  status.toUpperCase(),
                  style: TextStyle(
                    fontSize: 9,
                    fontWeight: FontWeight.bold,
                    color: statusColor,
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildPayoutsTab() {
    final payoutsAsync = ref.watch(payoutsProvider);

    return RefreshIndicator(
      onRefresh: () async => ref.refresh(payoutsProvider),
      child: payoutsAsync.when(
        data: (payouts) {
          if (payouts.isEmpty) {
            return AppWidgets.emptyState(
              title: 'No payouts yet',
              subtitle: 'Payout history will appear here',
              icon: Icons.account_balance_outlined,
            );
          }

          return ListView.builder(
            padding: const EdgeInsets.all(12),
            itemCount: payouts.length,
            itemBuilder: (context, index) {
              final p = payouts[index];
              return _buildPayoutItem(p);
            },
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, stack) => AppWidgets.errorWidget(
          message: error.toString(),
          onRetry: () => ref.refresh(payoutsProvider),
        ),
      ),
    );
  }

  Widget _buildPayoutItem(dynamic payout) {
    final double amount = ((payout.amount ?? 0) as num).toDouble();
    final String status = (payout.status ?? 'pending').toString();
    final statusColor = status == 'completed' || status == 'paid'
        ? AppTheme.successColor
        : status == 'rejected'
            ? AppTheme.errorColor
            : AppTheme.warningColor;

    return AppWidgets.customCard(
      child: Row(
        children: [
          Container(
            width: 50,
            height: 50,
            decoration: BoxDecoration(
              color: statusColor.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(
              status == 'completed' || status == 'paid'
                  ? Icons.check_circle
                  : status == 'rejected'
                      ? Icons.cancel
                      : Icons.hourglass_empty,
              color: statusColor,
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Payout #${payout.id ?? ''}',
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                ),
                const SizedBox(height: 4),
                Text(
                  'Requested: ${_formatDate(payout.requestedAt ?? payout.createdAt)}',
                  style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                ),
              ],
            ),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              AppWidgets.priceTag(
                amount: amount,
                prefix: '₹',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: statusColor,
                ),
              ),
              const SizedBox(height: 4),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: statusColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Text(
                  status.toUpperCase(),
                  style: TextStyle(
                    fontSize: 9,
                    fontWeight: FontWeight.bold,
                    color: statusColor,
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildIncentivesTab() {
    final incentivesAsync = ref.watch(incentivesProvider);

    return RefreshIndicator(
      onRefresh: () async => ref.refresh(incentivesProvider),
      child: incentivesAsync.when(
        data: (incentives) {
          if (incentives.isEmpty) {
            return AppWidgets.emptyState(
              title: 'No incentives yet',
              subtitle: 'Special incentives and bonuses appear here',
              icon: Icons.emoji_events_outlined,
            );
          }

          return ListView.builder(
            padding: const EdgeInsets.all(12),
            itemCount: incentives.length,
            itemBuilder: (context, index) {
              final incentive = incentives[index];
              return _buildIncentiveItem(incentive);
            },
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, stack) => AppWidgets.errorWidget(
          message: error.toString(),
          onRetry: () => ref.refresh(incentivesProvider),
        ),
      ),
    );
  }

  Widget _buildIncentiveItem(dynamic incentive) {
    final double amount = ((incentive.amount ?? 0) as num).toDouble();
    final String status = (incentive.status ?? 'pending').toString();
    final statusColor = status == 'earned' || status == 'paid'
        ? AppTheme.successColor
        : AppTheme.warningColor;
    final String title = (incentive.title ?? incentive.name ?? 'Incentive').toString();
    final String description = (incentive.description ?? '').toString();

    return AppWidgets.customCard(
      child: Row(
        children: [
          Container(
            width: 50,
            height: 50,
            decoration: BoxDecoration(
              color: AppTheme.accentColor.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(Icons.emoji_events, color: AppTheme.accentColor),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                ),
                if (description.isNotEmpty) ...[
                  const SizedBox(height: 4),
                  Text(
                    description,
                    style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ],
            ),
          ),
          AppWidgets.priceTag(
            amount: amount,
            prefix: '₹',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: statusColor,
            ),
          ),
        ],
      ),
    );
  }

  String _formatDate(dynamic date) {
    if (date == null) return 'N/A';
    try {
      final d = DateTime.parse(date.toString());
      return '${d.day}/${d.month}/${d.year}';
    } catch (_) {
      return date.toString();
    }
  }
}

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/app_constants.dart';
import '../../core/theme/app_theme.dart';
import '../providers/incentive_provider.dart';
import '../widgets/glass_card.dart';
import '../widgets/common_widgets.dart';

class IncentiveDashboardPage extends ConsumerWidget {
  const IncentiveDashboardPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final incentivesAsync = ref.watch(incentiveProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Performance Rewards'),
        actions: [
          IconButton(
            onPressed: () => ref.read(incentiveProvider.notifier).refresh(),
            icon: const Icon(Icons.refresh),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () => ref.read(incentiveProvider.notifier).refresh(),
        child: incentivesAsync.when(
          data: (incentives) {
            if (incentives.isEmpty) {
              return const Center(
                child: EmptyStateWidget(
                  title: 'No Incentive Data',
                  subtitle: 'Reach your monthly rank targets to earn extra rewards!',
                ),
              );
            }

            final currentIncentive = incentives.first;

            return SingleChildScrollView(
              padding: const EdgeInsets.all(AppConstants.defaultPadding),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildHeader(context, currentIncentive),
                  const SizedBox(height: 24),
                  Text(
                    'Monthly History',
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 16),
                  ...incentives.map((incentive) => _buildIncentiveCard(context, incentive)),
                  const SizedBox(height: 20),
                ],
              ),
            );
          },
          loading: () => const Center(child: CircularProgressIndicator()),
          error: (error, stack) => Center(child: Text('Error: $error')),
        ),
      ),
    );
  }

  Widget _buildHeader(BuildContext context, dynamic incentive) {
    return GlassCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    '${incentive.monthName} ${incentive.year}',
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      color: AppTheme.primaryColor,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  Text(
                    'Active Rank: ${incentive.rankAtTime}',
                    style: Theme.of(context).textTheme.bodySmall,
                  ),
                ],
              ),
              _buildStatusBadge(incentive.status),
            ],
          ),
          const SizedBox(height: 20),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              _buildMetric('Target', '₹${_formatLargeNumber(incentive.targetBusiness)}'),
              _buildMetric('Achieved', '₹${_formatLargeNumber(incentive.achievedBusiness)}'),
            ],
          ),
          const SizedBox(height: 20),
          LinearProgressIndicator(
            value: incentive.progress,
            backgroundColor: Colors.grey.shade200,
            valueColor: AlwaysStoppedAnimation<Color>(
              incentive.isAchieved ? Colors.green : AppTheme.accentColor,
            ),
            minHeight: 12,
            borderRadius: BorderRadius.circular(6),
          ),
          const SizedBox(height: 12),
          Text(
            incentive.isAchieved 
              ? 'Congratulations! You hit this month\'s target.' 
              : 'You need ₹${_formatLargeNumber(incentive.targetBusiness - incentive.achievedBusiness)} more to hit your target.',
            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
              fontWeight: FontWeight.w500,
              color: incentive.isAchieved ? Colors.green : Colors.black87,
            ),
          ),
          if (incentive.isAchieved) ...[
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.green.withOpacity(0.1),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                children: [
                  const Icon(Icons.stars, color: Colors.green),
                  const SizedBox(width: 12),
                  Text(
                    'Potential Reward: ₹${_formatLargeNumber(incentive.incentiveAmount)}',
                    style: const TextStyle(
                      fontWeight: FontWeight.bold,
                      color: Colors.green,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildIncentiveCard(BuildContext context, dynamic incentive) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: ListTile(
        title: Text('${incentive.monthName} ${incentive.year}'),
        subtitle: Text('Target: ₹${_formatLargeNumber(incentive.targetBusiness)}'),
        trailing: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            Text(
              '₹${_formatLargeNumber(incentive.incentiveAmount)}',
              style: TextStyle(
                fontWeight: FontWeight.bold,
                color: incentive.isAchieved ? Colors.green : Colors.grey,
              ),
            ),
            Text(
              incentive.status.toUpperCase(),
              style: TextStyle(
                fontSize: 10,
                color: _getStatusColor(incentive.status),
                fontWeight: FontWeight.bold,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMetric(String label, String value) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(fontSize: 12, color: Colors.grey),
        ),
        Text(
          value,
          style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
        ),
      ],
    );
  }

  Widget _buildStatusBadge(String status) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: _getStatusColor(status).withOpacity(0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: _getStatusColor(status)),
      ),
      child: Text(
        status.toUpperCase(),
        style: TextStyle(
          fontSize: 10,
          color: _getStatusColor(status),
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'paid': return Colors.green;
      case 'approved': return Colors.blue;
      case 'pending': return Colors.orange;
      case 'failed': return Colors.red;
      default: return Colors.grey;
    }
  }

  String _formatLargeNumber(double value) {
    if (value >= 10000000) return '${(value / 10000000).toStringAsFixed(1)} Cr';
    if (value >= 100000) return '${(value / 100000).toStringAsFixed(1)} L';
    return value.toStringAsFixed(0);
  }
}

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/providers/auth_provider.dart';
import '../../widgets/app_widgets.dart';

/// Agent Analytics Page - Detailed analytics and performance metrics
class AgentAnalyticsPage extends ConsumerWidget {
  const AgentAnalyticsPage({super.key});

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
          return _buildBody(context, ref, user);
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, stack) => AppWidgets.errorWidget(
          message: error.toString(),
          onRetry: () => ref.refresh(currentUserDataProvider),
        ),
      ),
    );
  }

  Widget _buildBody(BuildContext context, WidgetRef ref, dynamic user) {
    final userId = (user.id ?? user['id'] ?? 0) as int;
    return RefreshIndicator(
      onRefresh: () async {
        ref.invalidate(_agentAnalyticsProvider(userId));
        await Future.delayed(const Duration(milliseconds: 500));
      },
      color: AppTheme.primaryColor,
      child: CustomScrollView(
        slivers: [
          // App Bar
          SliverToBoxAdapter(child: _buildAppBar(context)),

          // Summary Stats
          SliverToBoxAdapter(
            child: _buildSummaryStats(context, ref),
          ),

          // Conversion Funnel
          SliverToBoxAdapter(
            child: AppWidgets.sectionHeader(
              title: 'Conversion Funnel',
              subtitle: 'Lead to sale conversion pipeline',
              onSeeAll: () => context.push('/agent/analytics/funnel'),
            ),
          ),

          // Funnel Stages
          SliverToBoxAdapter(
            child: _buildFunnelStages(context),
          ),

          // Lead Source Performance
          SliverToBoxAdapter(
            child: AppWidgets.sectionHeader(
              title: 'Lead Source Performance',
              subtitle: 'Performance by lead source',
              onSeeAll: () => context.push('/agent/analytics/sources'),
            ),
          ),

          // Source Performance Cards
          SliverToBoxAdapter(
            child: _buildSourcePerformance(context),
          ),

          // Monthly Trends
          SliverToBoxAdapter(
            child: AppWidgets.sectionHeader(
              title: 'Monthly Performance',
              subtitle: '6-month trend',
              onSeeAll: () => context.push('/agent/analytics/trends'),
            ),
          ),

          // Monthly Trends Chart
          SliverToBoxAdapter(
            child: _buildMonthlyTrends(context),
          ),

          // Bottom Padding
          const SliverToBoxAdapter(child: SizedBox(height: 32)),
        ],
      ),
    );
  }

  Widget _buildAppBar(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
        ),
        borderRadius: BorderRadius.vertical(bottom: Radius.circular(24)),
      ),
      child: SafeArea(
        child: Column(
          children: [
            Row(
              children: [
                Container(
                  width: 60,
                  height: 60,
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: const Icon(
                    Icons.analytics_rounded,
                    color: Colors.white,
                    size: 30,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Analytics',
                        style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                          color: Colors.white,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                      Text(
                        'Track your performance metrics',
                        style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                          color: Colors.white.withValues(alpha: 0.8),
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(
                    Icons.filter_list_rounded,
                    color: Colors.white,
                    size: 22,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
          ],
        ),
      ),
    );
  }

  Widget _buildSummaryStats(BuildContext context, WidgetRef ref) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 20, 16, 8),
      child: ref.watch(_agentAnalyticsProvider(0)).when(
        data: (data) {
          final stats = data['stats'] ?? {};
          return _buildStatsGrid(context, stats);
        },
        loading: () => _buildStatsGrid(context, {}),
        error: (error, stack) => _buildStatsGrid(context, {}),
      ),
    );
  }

  Widget _buildStatsGrid(BuildContext context, Map<String, dynamic> stats) {
    final items = [
      _StatItem(
        'Total Leads',
        stats['total_leads']?.toString() ?? '0',
        Icons.person_search_rounded,
        AppTheme.primaryColor,
      ),
      _StatItem(
        'Converted',
        stats['converted']?.toString() ?? '0',
        Icons.check_circle_rounded,
        AppTheme.successColor,
      ),
      _StatItem(
        'Conversion Rate',
        _calculateConversionRate(stats),
        Icons.trending_up_rounded,
        AppTheme.accentColor,
      ),
      _StatItem(
        'Avg. Deal Value',
        _formatCurrency(stats['avg_deal_value']?.toString() ?? '0'),
        Icons.currency_rupee_rounded,
        AppTheme.warningColor,
      ),
    ];

    return Row(
      children: items.map((item) => Expanded(child: _buildStatCard(context, item))).toList(),
    );
  }

  String _calculateConversionRate(Map<String, dynamic> stats) {
    final total = (stats['total_leads'] as int?) ?? 0;
    final converted = (stats['converted'] as int?) ?? 0;
    if (total == 0) return '0%';
    return '${((converted / total) * 100).toStringAsFixed(1)}%';
  }

  String _formatCurrency(String value) {
    final num = double.tryParse(value) ?? 0;
    if (num >= 10000000) {
      return '₹${(num / 10000000).toStringAsFixed(1)}Cr';
    } else if (num >= 100000) {
      return '₹${(num / 100000).toStringAsFixed(1)}L';
    }
    return '₹${num.toStringAsFixed(0)}';
  }

  Widget _buildStatCard(BuildContext context, _StatItem item) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 4),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            item.color.withValues(alpha: 0.15),
            item.color.withValues(alpha: 0.05),
          ],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: item.color.withValues(alpha: 0.3),
          width: 1,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(item.icon, color: item.color, size: 24),
          const SizedBox(height: 12),
          Text(
            item.value,
            style: Theme.of(context).textTheme.headlineSmall?.copyWith(
              fontWeight: FontWeight.w800,
              color: item.color,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            item.label,
            style: Theme.of(context).textTheme.bodySmall?.copyWith(
              color: Colors.grey[600],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFunnelStages(BuildContext context) {
    final stages = [
      _FunnelStage('New Leads', '156', Icons.person_add_rounded, AppTheme.infoColor, 1.0),
      _FunnelStage('Contacted', '134', Icons.phone_rounded, AppTheme.primaryColor, 0.86),
      _FunnelStage('Qualified', '98', Icons.verified_rounded, AppTheme.warningColor, 0.63),
      _FunnelStage('Proposal', '67', Icons.description_rounded, AppTheme.accentColor, 0.43),
      _FunnelStage('Closed Won', '42', Icons.celebration_rounded, AppTheme.successColor, 0.27),
    ];

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        children: stages.asMap().entries.map<Widget>((entry) {
          final index = entry.key;
          final stage = entry.value;
          final isLast = index == stages.length - 1;
          return Column(
            children: [
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.05),
                      blurRadius: 8,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Row(
                  children: [
                    Container(
                      width: 48,
                      height: 48,
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          colors: [
                            stage.color.withValues(alpha: 0.2),
                            stage.color.withValues(alpha: 0.1),
                          ],
                        ),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Icon(stage.icon, color: stage.color, size: 24),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            stage.title,
                            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                          Text(
                            '${stage.count} leads',
                            style: Theme.of(context).textTheme.bodySmall?.copyWith(
                              color: Colors.grey[600],
                            ),
                          ),
                        ],
                      ),
                    ),
                    Text(
                      stage.count,
                      style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                        fontWeight: FontWeight.w800,
                        color: stage.color,
                      ),
                    ),
                  ],
                ),
              ),
              if (!isLast) ...[
                const SizedBox(height: 8),
                Center(
                  child: Container(
                    width: 2,
                    height: 16,
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [
                          stages[index].color.withValues(alpha: 0.3),
                          stages[index + 1].color.withValues(alpha: 0.1),
                        ],
                      ),
                    ),
                  ),
                const SizedBox(height: 8),
              ],
            },
          ).toList(),
        ),
      ),
    );
  }

  Widget _buildSourcePerformance(BuildContext context) {
    final sources = [
      _SourceStat('Website', '45', '32%', Icons.language_rounded, AppTheme.primaryColor),
      _SourceStat('Referral', '38', '42%', Icons.people_rounded, AppTheme.successColor),
      _SourceStat('Social Media', '32', '25%', Icons.share_rounded, AppTheme.accentColor),
      _SourceStat('Walk-in', '28', '35%', Icons.directions_walk_rounded, AppTheme.warningColor),
      _SourceStat('Cold Call', '25', '18%', Icons.phone_rounded, AppTheme.infoColor),
      _SourceStat('Event', '18', '28%', Icons.event_rounded, AppTheme.primaryColor),
    ];

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        children: sources.map<Widget>((source) => Container(
          margin: const EdgeInsets.only(bottom: 12),
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: source.color.withValues(alpha: 0.2)),
          ),
          child: Row(
            children: [
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: source.color.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(source.icon, color: source.color, size: 22),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      source.name,
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    Text(
                      '${source.count} leads',
                      style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color: Colors.grey[600],
                      ),
                    ),
                  ],
                ),
              ),
              Text(
                source.rate,
                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.w700,
                  color: source.color,
                ),
              ),
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: source.color.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  'View',
                  style: TextStyle(
                    color: source.color,
                    fontWeight: FontWeight.w600,
                    fontSize: 12,
                  ),
                ),
              ),
            ],
          ),
        )).toList(),
      ),
    );
  }

  Widget _buildMonthlyTrends(BuildContext context) {
    final months = [
      _MonthData('Jan', 45, 12, 3),
      _MonthData('Feb', 52, 18, 4),
      _MonthData('Mar', 48, 15, 5),
      _MonthData('Apr', 61, 22, 6),
      _MonthData('May', 58, 20, 7),
      _MonthData('Jun', 65, 25, 8),
    ];

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                '6-Month Trend',
                style: Theme.of(context).textTheme.titleLarge?.copyWith(
                  fontWeight: FontWeight.w700,
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: AppTheme.primaryColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  'Last 6 Months',
                  style: TextStyle(
                    color: AppTheme.primaryColor,
                    fontWeight: FontWeight.w600,
                    fontSize: 12,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 20),
          SizedBox(
            height: 200,
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.end,
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: months.map<Widget>((month) {
                final maxLeads = 70.0;
                final barHeight = (month.leads / maxLeads) * 150;
                final convertedHeight = (month.converted / maxLeads) * 150;

                return Expanded(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      Stack(
                        children: [
                          Container(
                            width: 30,
                            height: barHeight,
                            decoration: BoxDecoration(
                              color: AppTheme.primaryColor.withValues(alpha: 0.2),
                              borderRadius: BorderRadius.circular(6),
                            ),
                          ),
                          Container(
                            width: 30,
                            height: convertedHeight,
                            decoration: BoxDecoration(
                              gradient: LinearGradient(
                                colors: [AppTheme.successColor, AppTheme.primaryColor],
                                begin: Alignment.bottomCenter,
                                end: Alignment.topCenter,
                              ),
                              borderRadius: BorderRadius.circular(6),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Text(
                        month.label,
                        style: TextStyle(
                          color: Colors.grey[600],
                          fontSize: 12,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ],
                  ),
                )).toList(),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// Providers
final _agentAnalyticsProvider = FutureProvider.family<Map<String, dynamic>, int>((ref, userId) async {
  try {
    final api = ApiService();
    AppConstants.initBaseUrl();
    final response = await api.get('${AppConstants.apiVersion}/agent/analytics');
    if (response['success'] == true && response['data'] != null) {
      return response['data'] as Map<String, dynamic>;
    }
  } catch (_) {}
  // Mock data fallback
  return {
    'stats': {
      'total_leads': 156,
      'converted': 42,
      'avg_deal_value': 2500000,
    },
    'funnel': [
      {'stage': 'New', 'count': 156},
      {'stage': 'Contacted', 'count': 134},
      {'stage': 'Qualified', 'count': 98},
      {'stage': 'Proposal', 'count': 67},
      {'stage': 'Won', 'count': 42},
    ],
    'sources': [
      {'source': 'Website', 'count': 45, 'rate': '32%'},
      {'source': 'Referral', 'count': 38, 'rate': '42%'},
      {'source': 'Social Media', 'count': 32, 'rate': '25%'},
      {'source': 'Walk-in', 'count': 28, 'rate': '35%'},
    ],
  };
});

// Helper Classes
class _StatItem {
  final String label;
  final String value;
  final IconData icon;
  final Color color;
  const _StatItem(this.label, this.value, this.icon, this.color);
}

class _FunnelStage {
  final String title;
  final String count;
  final IconData icon;
  final Color color;
  final double progress;
  const _FunnelStage(this.title, this.count, this.icon, this.color, this.progress);
}

class _SourceStat {
  final String name;
  final String count;
  final String rate;
  final IconData icon;
  final Color color;
  const _SourceStat(this.name, this.count, this.rate, this.icon, this.color);
}

class _MonthData {
  final String label;
  final int leads;
  final int contacted;
  final int converted;
  const _MonthData(this.label, this.leads, this.contacted, this.converted);
}
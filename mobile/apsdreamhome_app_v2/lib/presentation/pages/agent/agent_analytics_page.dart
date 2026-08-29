import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

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
          return _buildBody(context, ref);
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, stack) => AppWidgets.errorWidget(
          message: error.toString(),
          onRetry: () => ref.refresh(currentUserDataProvider),
        ),
      ),
    );
  }

  Widget _buildBody(BuildContext context, WidgetRef ref) {
    final analyticsAsync = ref.watch(_agentAnalyticsProvider);

    return RefreshIndicator(
      onRefresh: () async {
        ref.invalidate(_agentAnalyticsProvider);
        await Future.delayed(const Duration(milliseconds: 500));
      },
      color: AppTheme.primaryColor,
      child: CustomScrollView(
        slivers: [
          SliverToBoxAdapter(child: _buildAppBar(context)),
          SliverToBoxAdapter(
            child: analyticsAsync.when(
              data: (stats) => _buildStatsGrid(context, stats),
              loading: () => _buildStatsGrid(context, {}),
              error: (_, _) => _buildStatsGrid(context, {}),
            ),
          ),
          SliverToBoxAdapter(
            child: AppWidgets.sectionHeader(
              title: 'Conversion Funnel',
              subtitle: 'Lead to sale conversion pipeline',
              onSeeAll: () {},
            ),
          ),
          SliverToBoxAdapter(child: _buildFunnelStages(context)),
          SliverToBoxAdapter(
            child: AppWidgets.sectionHeader(
              title: 'Lead Source Performance',
              subtitle: 'Performance by lead source',
              onSeeAll: () {},
            ),
          ),
          SliverToBoxAdapter(child: _buildSourcePerformance(context)),
          SliverToBoxAdapter(
            child: AppWidgets.sectionHeader(
              title: 'Monthly Performance',
              subtitle: '6-month trend',
              onSeeAll: () {},
            ),
          ),
          SliverToBoxAdapter(child: _buildMonthlyTrends(context)),
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
        child: Row(
          children: [
            Container(
              width: 60,
              height: 60,
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.2),
                borderRadius: BorderRadius.circular(16),
              ),
              child: const Icon(Icons.analytics_rounded,
                  color: Colors.white, size: 30),
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
          ],
        ),
      ),
    );
  }

  Widget _buildStatsGrid(BuildContext context, Map<String, dynamic> stats) {
    final totalLeads = _toInt(stats['total_leads']);
    final converted = _toInt(stats['converted']);
    final rate =
        totalLeads > 0 ? ((converted / totalLeads) * 100).toStringAsFixed(1) : '0';

    final items = [
      ('Total Leads', '$totalLeads', Icons.person_search_rounded, AppTheme.primaryColor),
      ('Converted', '$converted', Icons.check_circle_rounded, AppTheme.successColor),
      ('Conv. Rate', '$rate%', Icons.trending_up_rounded, AppTheme.accentColor),
      (
        'Avg. Deal',
        '\u20B9${_formatCurrency(_toDouble(stats['avg_deal_value']))}',
        Icons.currency_rupee_rounded,
        AppTheme.warningColor
      ),
    ];

    Widget card((String, String, IconData, Color) item) {
      final color = item.$4;
      return Expanded(
        child: Container(
          margin: const EdgeInsets.symmetric(horizontal: 4),
          padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 8),
          decoration: BoxDecoration(
            gradient: LinearGradient(
              colors: [
                color.withValues(alpha: 0.15),
                color.withValues(alpha: 0.05),
              ],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: color.withValues(alpha: 0.3)),
          ),
          child: Column(
            children: [
              Icon(item.$3, color: color, size: 22),
              const SizedBox(height: 8),
              Text(item.$2,
                  style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15)),
              const SizedBox(height: 2),
              Text(item.$1,
                  textAlign: TextAlign.center,
                  style: TextStyle(fontSize: 10, color: Colors.grey[600])),
            ],
          ),
        ),
      );
    }

    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 20, 16, 8),
      child: Row(children: items.map(card).toList()),
    );
  }

  Widget _buildFunnelStages(BuildContext context) {
    final stages = <(String, int, IconData, Color)>[
      ('New Leads', 156, Icons.person_add_rounded, AppTheme.infoColor),
      ('Contacted', 134, Icons.phone_rounded, AppTheme.primaryColor),
      ('Qualified', 98, Icons.verified_rounded, AppTheme.warningColor),
      ('Proposal', 67, Icons.description_rounded, AppTheme.accentColor),
      ('Closed Won', 42, Icons.celebration_rounded, AppTheme.successColor),
    ];

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        children: List.generate(stages.length, (index) {
          final stage = stages[index];
          final isLast = index == stages.length - 1;
          return Column(
            children: [
              Container(
                padding: const EdgeInsets.all(14),
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
                      width: 44,
                      height: 44,
                      decoration: BoxDecoration(
                        color: stage.$4.withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Icon(stage.$3, color: stage.$4, size: 22),
                    ),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Text(stage.$1,
                          style: const TextStyle(
                              fontWeight: FontWeight.w700, fontSize: 14)),
                    ),
                    Text('${stage.$2} leads',
                        style: TextStyle(
                            fontWeight: FontWeight.w800,
                            fontSize: 15,
                            color: stage.$4)),
                  ],
                ),
              ),
              if (!isLast)
                Center(
                  child: Container(width: 2, height: 14, color: Colors.grey[300]),
                ),
            ],
          );
        }),
      ),
    );
  }

  Widget _buildSourcePerformance(BuildContext context) {
    final sources = <(String, int, String, IconData, Color)>[
      ('Website', 45, '32%', Icons.language_rounded, AppTheme.primaryColor),
      ('Referral', 38, '42%', Icons.people_rounded, AppTheme.successColor),
      ('Social Media', 32, '25%', Icons.share_rounded, AppTheme.accentColor),
      ('Walk-in', 28, '35%', Icons.directions_walk_rounded, AppTheme.warningColor),
      ('Cold Call', 25, '18%', Icons.phone_rounded, AppTheme.infoColor),
      ('Event', 18, '28%', Icons.event_rounded, AppTheme.primaryColor),
    ];

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        children: sources.map((source) {
          return Container(
            margin: const EdgeInsets.only(bottom: 10),
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              border:
                  Border.all(color: source.$5.withValues(alpha: 0.25)),
            ),
            child: Row(
              children: [
                Container(
                  width: 40,
                  height: 40,
                  decoration: BoxDecoration(
                    color: source.$5.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Icon(source.$4, color: source.$5, size: 20),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(source.$1,
                      style: const TextStyle(
                          fontWeight: FontWeight.w600, fontSize: 14)),
                ),
                Text('${source.$2} leads',
                    style:
                        TextStyle(color: Colors.grey[600], fontSize: 12)),
                const SizedBox(width: 12),
                Text(source.$3,
                    style: TextStyle(
                        fontWeight: FontWeight.w800,
                        fontSize: 14,
                        color: source.$5)),
              ],
            ),
          );
        }).toList(),
      ),
    );
  }

  Widget _buildMonthlyTrends(BuildContext context) {
    final months = <(String, int)>[
      ('Jan', 45),
      ('Feb', 52),
      ('Mar', 48),
      ('Apr', 61),
      ('May', 58),
      ('Jun', 65),
    ];
    const maxLeads = 70.0;

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
          const Text('6-Month Trend',
              style: TextStyle(fontWeight: FontWeight.w700, fontSize: 15)),
          const SizedBox(height: 20),
          SizedBox(
            height: 180,
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.end,
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: months.map((month) {
                final barHeight = (month.$2 / maxLeads) * 140;
                return Expanded(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      Container(
                        width: 26,
                        height: barHeight.clamp(6.0, 140.0),
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            colors: [
                              AppTheme.primaryColor.withValues(alpha: 0.35),
                              AppTheme.primaryColor,
                            ],
                            begin: Alignment.topCenter,
                            end: Alignment.bottomCenter,
                          ),
                          borderRadius: const BorderRadius.only(
                            topLeft: Radius.circular(6),
                            topRight: Radius.circular(6),
                          ),
                        ),
                      ),
                      const SizedBox(height: 6),
                      Text(month.$1,
                          style: TextStyle(
                              color: Colors.grey[600],
                              fontSize: 11,
                              fontWeight: FontWeight.w500)),
                    ],
                  ),
                );
              }).toList(),
            ),
          ),
        ],
      ),
    );
  }

  // Helpers
  int _toInt(dynamic v) =>
      v is int ? v : int.tryParse(v?.toString() ?? '') ?? 0;
  double _toDouble(dynamic v) =>
      v is num ? v.toDouble() : double.tryParse(v?.toString() ?? '') ?? 0.0;

  String _formatCurrency(double value) {
    if (value >= 10000000) {
      return '${(value / 10000000).toStringAsFixed(1)} Cr';
    } else if (value >= 100000) {
      return '${(value / 100000).toStringAsFixed(1)} L';
    }
    return value.toStringAsFixed(0);
  }
}

// Provider
final _agentAnalyticsProvider =
    FutureProvider<Map<String, dynamic>>((ref) async {
  try {
    final api = ApiService();
    AppConstants.initBaseUrl();
    final response = await api.get('${AppConstants.apiVersion}/agent/analytics');
    if (response['success'] == true && response['data'] is Map) {
      return Map<String, dynamic>.from(response['data'] as Map);
    }
  } catch (_) {}
  // Mock fallback
  return {'total_leads': 156, 'converted': 42, 'avg_deal_value': 2500000};
});

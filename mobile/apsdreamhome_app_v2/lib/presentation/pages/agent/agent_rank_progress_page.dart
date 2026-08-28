import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/providers/auth_provider.dart';
import '../../widgets/app_widgets.dart';
import '../../widgets/glass_card.dart';

/// Agent Rank Progress Page - Track rank achievement progress
class AgentRankProgressPage extends ConsumerWidget {
  const AgentRankProgressPage({super.key});

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
    final progressAsync = ref.watch(_agentRankProgressProvider(userId));

    return RefreshIndicator(
      onRefresh: () async {
        ref.invalidate(_agentRankProgressProvider(userId));
        await Future.delayed(const Duration(milliseconds: 500));
      },
      color: AppTheme.primaryColor,
      child: CustomScrollView(
        slivers: [
          SliverToBoxAdapter(child: _buildAppBar(context)),
          SliverToBoxAdapter(
            child: progressAsync.when(
              data: (progress) =>
                  _buildProgressContent(context, progress),
              loading: () => const Center(
                child: Padding(
                  padding: EdgeInsets.all(48),
                  child:
                      CircularProgressIndicator(color: AppTheme.primaryColor),
                ),
              ),
              error: (error, stack) => AppWidgets.errorWidget(
                message: error.toString(),
                onRetry: () =>
                    ref.invalidate(_agentRankProgressProvider(userId)),
              ),
            ),
          ),
          SliverToBoxAdapter(
            child: AppWidgets.sectionHeader(
              title: 'Rank Ladder',
              subtitle: 'All commission ranks and requirements',
              onSeeAll: () {},
            ),
          ),
          SliverToBoxAdapter(child: _buildRankLadder(context)),
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
              child: const Icon(Icons.military_tech_rounded,
                  color: Colors.white, size: 30),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Rank Progress',
                    style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                          color: Colors.white,
                          fontWeight: FontWeight.w800,
                        ),
                  ),
                  Text(
                    'Track your journey to the top',
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

  Widget _buildProgressContent(BuildContext context, Map<String, dynamic> progress) {
    final currentRank =
        progress['current_rank']?.toString() ?? 'Associate';
    final currentGbv = _toDouble(progress['current_gbv']);
    final nextGbv = _toDouble(progress['next_rank_gbv']);

    // Find the next target from the static ladder
    double target = _nextTargetFor(currentGbv);
    final pct = target > 0 ? (currentGbv / target).clamp(0.0, 1.0) : 0.0;

    final remaining = (target - currentGbv).clamp(0.0, double.infinity);

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 20),
      child: Column(
        children: [
          GlassCard(
            padding: const EdgeInsets.all(24),
            opacity: 0.12,
            blur: 10,
            child: Column(
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Current Rank',
                            style: TextStyle(
                                color: Colors.white70, fontSize: 13)),
                        const SizedBox(height: 4),
                        Text(currentRank,
                            style: TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.w800,
                                fontSize: 22)),
                      ],
                    ),
                    Container(
                      width: 64,
                      height: 64,
                      decoration: BoxDecoration(
                        gradient: LinearGradient(colors: [
                          AppTheme.accentColor,
                          Color(0xFFFFB300)
                        ]),
                        borderRadius: BorderRadius.circular(16),
                        boxShadow: [
                          BoxShadow(
                            color:
                                AppTheme.accentColor.withValues(alpha: 0.3),
                            blurRadius: 16,
                            offset: Offset(0, 6),
                          ),
                        ],
                      ),
                      child: Icon(Icons.military_tech_rounded,
                          color: Colors.white, size: 34),
                    ),
                  ],
                ),
                const SizedBox(height: 24),
                // Progress bar
                Stack(
                  children: [
                    Container(
                      height: 14,
                      decoration: BoxDecoration(
                        color: Colors.white24,
                        borderRadius: BorderRadius.circular(7),
                      ),
                    ),
                    FractionallySizedBox(
                      widthFactor: pct,
                      child: Container(
                        height: 14,
                        decoration: BoxDecoration(
                          gradient: LinearGradient(colors: [
                            AppTheme.successColor,
                            AppTheme.accentColor
                          ]),
                          borderRadius: BorderRadius.circular(7),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text('\u20B9${_formatCurrency(currentGbv)} earned',
                        style: TextStyle(
                            color: Colors.white70, fontSize: 12)),
                    Text('\u20B9${_formatCurrency(target)} target',
                        style: TextStyle(
                            color: Colors.white70, fontSize: 12)),
                  ],
                ),
                if (remaining > 0 && target > 0) ...[
                  const SizedBox(height: 16),
                  Container(
                    width: double.infinity,
                    padding: EdgeInsets.symmetric(vertical: 12),
                    decoration: BoxDecoration(
                      color: AppTheme.primaryColor.withValues(alpha: 0.25),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                          color: AppTheme.primaryColor.withValues(alpha: 0.5)),
                    ),
                    child: Center(
                      child: Text.rich(
                        TextSpan(children: [
                          TextSpan(
                              text: '\u20B9${_formatCurrency(remaining)}',
                              style: TextStyle(
                                  color: AppTheme.successColor,
                                  fontWeight: FontWeight.w800,
                                  fontSize: 15)),
                          TextSpan(
                              text:
                                  ' more GBV to reach the next rank',
                              style: TextStyle(
                                  color: Colors.white70, fontSize: 13)),
                        ]),
                      ),
                    ),
                  ),
                ],
              ],
            ),
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Expanded(
                child: SizedBox(
                  height: 44,
                  child: OutlinedButton.icon(
                    onPressed: () => context.push('/associate/team'),
                    icon: Icon(Icons.groups_rounded, size: 18),
                    label: Text('View Team'),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: Colors.white,
                      side: BorderSide(color: Colors.white38),
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                ),
              ),
              SizedBox(width: 12),
              Expanded(
                child: SizedBox(
                  height: 44,
                  child: DecoratedBox(
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(12),
                      gradient: LinearGradient(
                          colors: [AppTheme.primaryColor, AppTheme.secondaryColor]),
                    ),
                    child: ElevatedButton.icon(
                      onPressed: () => context.push('/referral'),
                      icon:
                          Icon(Icons.share_rounded, size: 18, color: Colors.white),
                      label: Text('Grow Network',
                          style: TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.w600)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.transparent,
                        shadowColor: Colors.transparent,
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildRankLadder(BuildContext context) {
    final ranks = <Map<String, dynamic>>[
      {'name': 'Associate', 'target': 1000000.0, 'rate': 5.0},
      {'name': 'Sr. Associate', 'target': 3500000.0, 'rate': 7.0},
      {'name': 'BDM', 'target': 7000000.0, 'rate': 10.0},
      {'name': 'Sr. BDM', 'target': 15000000.0, 'rate': 12.0},
      {'name': 'Vice President', 'target': 30000000.0, 'rate': 15.0},
      {'name': 'President', 'target': 50000000.0, 'rate': 18.0},
      {'name': 'Site Manager', 'target': 100000000.0, 'rate': 20.0},
    ];

    return Container(
      margin: EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        children: List.generate(ranks.length, (i) {
          final r = ranks[i];
          final isLast = i == ranks.length - 1;
          final color = Color.lerp(AppTheme.infoColor, AppTheme.successColor,
              i / (ranks.length - 1))!;
          return Column(
            children: [
              Container(
                padding: EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: color.withValues(alpha: 0.35)),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.04),
                      blurRadius: 8,
                      offset: Offset(0, 2),
                    ),
                  ],
                ),
                child: Row(
                  children: [
                    Container(
                      width: 42,
                      height: 42,
                      decoration: BoxDecoration(
                        color: color.withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(11),
                      ),
                      child: Icon(Icons.workspace_premium_rounded,
                          color: color, size: 22),
                    ),
                    SizedBox(width: 14),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(r['name'] as String,
                              style: TextStyle(
                                  fontWeight: FontWeight.w700,
                                  fontSize: 14)),
                          SizedBox(height: 2),
                          Text(
                            '\u20B9${_formatCurrency((r['target'] as num).toDouble())}+ GBV',
                            style: TextStyle(
                                color: Colors.grey[600], fontSize: 12),
                          ),
                        ],
                      ),
                    ),
                    Container(
                      padding:
                          EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                      decoration: BoxDecoration(
                        color: color.withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        '${r['rate']}%',
                        style: TextStyle(
                            color: color,
                            fontWeight: FontWeight.w800,
                            fontSize: 14),
                      ),
                    ),
                  ],
                ),
              ),
              if (!isLast)
                Center(
                  child: Container(
                    width: 2,
                    height: 14,
                    color: Colors.grey[300],
                  ),
                ),
            ],
          );
        }),
      ),
    );
  }

  // Helpers
  double _toDouble(dynamic v) {
    if (v is num) return v.toDouble();
    return double.tryParse(v?.toString() ?? '') ?? 0.0;
  }

  String _formatCurrency(double value) {
    if (value >= 10000000) {
      return '${(value / 10000000).toStringAsFixed(1)} Cr';
    } else if (value >= 100000) {
      return '${(value / 100000).toStringAsFixed(1)} L';
    } else if (value >= 1000) {
      return '${(value / 1000).toStringAsFixed(0)} K';
    }
    return value.toStringAsFixed(0);
  }

  double _nextTargetFor(double gbv) {
    if (gbv < 1000000) return 1000000;
    if (gbv < 3500000) return 3500000;
    if (gbv < 7000000) return 7000000;
    if (gbv < 15000000) return 15000000;
    if (gbv < 30000000) return 30000000;
    if (gbv < 50000000) return 50000000;
    if (gbv < 100000000) return 100000000;
    return 0; // max rank reached
  }
}

// Provider
final _agentRankProgressProvider =
    FutureProvider.family<Map<String, dynamic>, int>((ref, userId) async {
  try {
    final api = ApiService();
    AppConstants.initBaseUrl();
    final response =
        await api.get('${AppConstants.apiVersion}/agent/rank-progress');
    if (response['success'] == true && response['data'] is Map) {
      return Map<String, dynamic>.from(response['data'] as Map);
    }
  } catch (_) {}
  // Mock data fallback
  return {
    'current_rank': 'Sr. Associate',
    'current_gbv': 2100000,
    'next_rank_gbv': 3500000,
  };
});

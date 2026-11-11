import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/providers/connectivity_provider.dart';
import '../../providers/sync_provider.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/sync_indicator.dart';

class HomePage extends ConsumerWidget {
  const HomePage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(authProvider);
    final syncState = ref.watch(syncStateProvider);
    final isOnline = ref.watch(isOnlineProvider);

    return Scaffold(
      body: RefreshIndicator(
        onRefresh: () async {
          await ref.read(syncStateProvider.notifier).sync();
        },
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(AppConstants.defaultPadding),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: 20),

              // Header with sync status
              Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Welcome back,',
                          style:
                              Theme.of(context).textTheme.bodyLarge?.copyWith(
                                    color: Colors.grey.shade600,
                                  ),
                        ),
                        Text(
                          user?.name ?? 'User',
                          style: Theme.of(context)
                              .textTheme
                              .headlineSmall
                              ?.copyWith(
                                fontWeight: FontWeight.bold,
                              ),
                        ),
                      ],
                    ),
                  ),
                  const SyncIndicator(),
                ],
              ),

              const SizedBox(height: 24),

              // User Stats Card
              GlassCard(
                child: Column(
                  children: [
                    Row(
                      children: [
                        CircleAvatar(
                          radius: 30,
                          backgroundColor:
                              AppTheme.primaryColor.withValues(alpha: 0.1),
                          backgroundImage: user?.avatar != null
                              ? CachedNetworkImageProvider(user!.avatar!)
                              : null,
                          child: user?.avatar == null
                              ? const Icon(
                                  Icons.person,
                                  size: 30,
                                  color: AppTheme.primaryColor,
                                )
                              : null,
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                user?.rank ?? 'Associate',
                                style: Theme.of(context)
                                    .textTheme
                                    .titleLarge
                                    ?.copyWith(
                                      fontWeight: FontWeight.bold,
                                      color: AppTheme.primaryColor,
                                    ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                'Target: ${_formatTarget(user?.target ?? 0)}',
                                style: Theme.of(context)
                                    .textTheme
                                    .bodyMedium
                                    ?.copyWith(
                                      color: Colors.grey.shade600,
                                    ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                'Commission: ${user?.commissionRate.toStringAsFixed(1)}%',
                                style: Theme.of(context)
                                    .textTheme
                                    .bodyMedium
                                    ?.copyWith(
                                      color: AppTheme.accentColor,
                                      fontWeight: FontWeight.w600,
                                    ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 24),

              // Quick Actions Grid
              _buildQuickActions(context, isOnline),

              const SizedBox(height: 24),

              // Recent Activity
              const Text(
                'Recent Activity',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w800,
                  color: AppTheme.textPrimaryLight,
                ),
              ),
              const SizedBox(height: 16),

              GlassCard(
                padding: const EdgeInsets.all(16),
                child: Column(
                  children: [
                    _buildActivityItem(
                      context,
                      icon: Icons.person_add,
                      title: 'New Lead Added',
                      subtitle: 'Rahul Sharma - 3 BHK Interest',
                      time: '2 hours ago',
                      color: AppTheme.successColor,
                    ),
                    const Divider(height: 1),
                    _buildActivityItem(
                      context,
                      icon: Icons.trending_up,
                      title: 'Commission Credited',
                      subtitle: '₹50,000 from Property Sale',
                      time: '1 day ago',
                      color: AppTheme.accentColor,
                    ),
                    const Divider(height: 1),
                    _buildActivityItem(
                      context,
                      icon: Icons.apartment,
                      title: 'Property Status Updated',
                      subtitle: 'Plot A-12 marked as Sold',
                      time: '2 days ago',
                      color: AppTheme.infoColor,
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 24),

              // Sync Status
              if (syncState.isPending)
                GlassCard(
                  child: Column(
                    children: [
                      Row(
                        children: [
                          const Icon(
                            Icons.sync_problem,
                            color: Colors.orange,
                            size: 20,
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Text(
                              '${syncState.pendingCount} items pending sync',
                              style: const TextStyle(
                                color: Colors.orange,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                          ),
                          TextButton(
                            onPressed: () {
                              ref.read(syncStateProvider.notifier).sync();
                            },
                            child: const Text('Sync Now'),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),

              const SizedBox(height: 20),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildQuickActions(BuildContext context, bool isConnected) {
    final actions = [
      QuickAction(
        icon: Icons.add_circle_outline,
        title: 'Add Lead',
        subtitle: 'Create new lead',
        color: AppTheme.successColor,
        onTap: () => _navigateToLeads(context),
      ),
      QuickAction(
        icon: Icons.apartment_outlined,
        title: 'Properties',
        subtitle: 'Browse properties',
        color: AppTheme.primaryColor,
        onTap: () => _navigateToProperties(context),
      ),
      QuickAction(
        icon: Icons.trending_up_outlined,
        title: 'MLM Dashboard',
        subtitle: 'View commissions',
        color: AppTheme.accentColor,
        onTap: () => _navigateToMLM(context),
      ),
      QuickAction(
        icon: Icons.call_outlined,
        title: 'Quick Call',
        subtitle: 'Contact leads',
        color: AppTheme.infoColor,
        onTap: () => _makeQuickCall(context),
      ),
    ];

    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        childAspectRatio: 1.1,
        crossAxisSpacing: 12,
        mainAxisSpacing: 12,
      ),
      itemCount: actions.length,
      itemBuilder: (context, index) {
        final action = actions[index];
        return GlassCard(
          padding: const EdgeInsets.all(14),
          child: InkWell(
            onTap: !isConnected && action.requiresNetwork ? null : action.onTap,
            borderRadius: BorderRadius.circular(16),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Container(
                  width: 56,
                  height: 56,
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [
                        action.color.withValues(alpha: 0.15),
                        action.color.withValues(alpha: 0.05),
                      ],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(
                      color: action.color.withValues(alpha: 0.3),
                      width: 1,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: action.color.withValues(alpha: 0.12),
                        blurRadius: 8,
                        spreadRadius: 0,
                        offset: const Offset(0, 3),
                      ),
                    ],
                  ),
                  child: Icon(
                    action.icon,
                    size: 26,
                    color: !isConnected && action.requiresNetwork
                        ? Colors.grey
                        : action.color,
                  ),
                ),
                const SizedBox(height: 12),
                Text(
                  action.title,
                  style: TextStyle(
                    fontWeight: FontWeight.bold,
                    color: !isConnected && action.requiresNetwork
                        ? Colors.grey
                        : AppTheme.textPrimaryLight,
                    fontSize: 13,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 4),
                Text(
                  action.subtitle,
                  style: TextStyle(
                    color: Colors.grey.shade600,
                    fontSize: 11,
                    fontWeight: FontWeight.w500,
                  ),
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildActivityItem(
    BuildContext context, {
    required IconData icon,
    required String title,
    required String subtitle,
    required String time,
    required Color color,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 10),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(
                color: color.withValues(alpha: 0.3),
                width: 1,
              ),
            ),
            child: Icon(icon, size: 20, color: color),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    fontWeight: FontWeight.w700,
                    fontSize: 14,
                    color: AppTheme.textPrimaryLight,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  subtitle,
                  style: const TextStyle(
                    color: AppTheme.textSecondaryLight,
                    fontSize: 13,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          ),
          Text(
            time,
            style: const TextStyle(
              color: AppTheme.textSecondaryLight,
              fontSize: 12,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
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

  void _navigateToLeads(BuildContext context) {
    context.go('/leads');
  }

  void _navigateToProperties(BuildContext context) {
    context.go('/properties');
  }

  void _navigateToMLM(BuildContext context) {
    context.go('/mlm');
  }

  void _makeQuickCall(BuildContext context) {
    // TODO: Implement quick call functionality
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Quick call feature coming soon!')),
    );
  }
}

class QuickAction {
  final IconData icon;
  final String title;
  final String subtitle;
  final Color color;
  final VoidCallback onTap;
  final bool requiresNetwork;

  QuickAction({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.color,
    required this.onTap,
    this.requiresNetwork = false,
  });
}

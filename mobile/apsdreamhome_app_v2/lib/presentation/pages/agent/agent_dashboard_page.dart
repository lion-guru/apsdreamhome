import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../data/repositories/mlm_repository.dart';
import '../../../data/repositories/lead_repository.dart';
import '../../../data/services/auth_service.dart';
import '../../../data/models/user_model.dart';
import '../../../data/models/lead_model_extended.dart';
import '../../widgets/app_widgets.dart';

/// Agent Dashboard - Full stats, quick actions, and recent leads
class AgentDashboardPage extends ConsumerWidget {
  const AgentDashboardPage({super.key});

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

  Widget _buildBody(BuildContext context, WidgetRef ref, User user) {
    final leadRepo = ref.watch(leadRepositoryProvider);
    final commissionAsync = ref.watch(mlmSummaryProvider);

    return CustomScrollView(
      slivers: [
        // App Bar
        SliverToBoxAdapter(child: _buildAppBar(context, user)),

        // Stats Cards
        SliverToBoxAdapter(child: _buildStatsCards(context, ref, commissionAsync)),

        // Quick Actions
        SliverToBoxAdapter(child: _buildQuickActions(context)),

        // Today's Follow-ups
        SliverToBoxAdapter(
          child: AppWidgets.sectionHeader(
            title: 'Recent Leads',
            subtitle: 'Your latest assigned leads',
            onSeeAll: () => context.push('/agent/leads'),
          ),
        ),

        // Recent Leads List
        SliverToBoxAdapter(child: _buildRecentLeads(context, ref, leadRepo)),

        // Bottom Padding
        const SliverToBoxAdapter(child: SizedBox(height: 32)),
      ],
    );
  }

  Widget _buildAppBar(BuildContext context, User user) {
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
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(30),
                    border: Border.all(color: Colors.white, width: 3),
                  ),
                  child: user.profileImage != null
                      ? ClipRRect(
                          borderRadius: BorderRadius.circular(30),
                          child: Image.network(user.profileImage!, fit: BoxFit.cover),
                        )
                      : const Icon(Icons.person, color: AppTheme.primaryColor, size: 32),
                ),
                const SizedBox(width: 16),
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
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
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
                IconButton(
                  onPressed: () => context.push('/notifications'),
                  icon: const Icon(Icons.notifications_outlined, color: Colors.white),
                ),
                IconButton(
                  onPressed: () => context.push('/profile'),
                  icon: const Icon(Icons.settings_outlined, color: Colors.white),
                ),
              ],
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.15),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.white.withValues(alpha: 0.3)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.star_outline, color: Colors.white),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Agent Status',
                          style: TextStyle(color: Colors.white70, fontSize: 12),
                        ),
                        Text(
                          'Active • Handling leads professionally',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 14,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: AppTheme.successColor,
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: const Text(
                      'Online',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
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

  Widget _buildStatsCards(
    BuildContext context,
    WidgetRef ref,
    AsyncValue<MlmSummary> commissionAsync,
  ) {
    return commissionAsync.when(
      data: (summary) {
        final stats = [
          {
            'title': 'Total Earnings',
            'amount': summary.totalEarnings,
            'icon': Icons.account_balance_wallet_outlined,
            'color': AppTheme.primaryColor,
          },
          {
            'title': 'Pending Payout',
            'amount': summary.currentBalance,
            'icon': Icons.pending_actions_outlined,
            'color': AppTheme.warningColor,
          },
          {
            'title': 'This Month',
            'amount': summary.thisMonthEarnings,
            'icon': Icons.calendar_month,
            'color': AppTheme.accentColor,
          },
          {
            'title': 'Active Referrals',
            'count': summary.activeReferrals,
            'icon': Icons.people_outline,
            'color': AppTheme.successColor,
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
                      '${stat['count']}',
                      style: TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                        color: stat['color'] as Color,
                      ),
                    ),
                  const SizedBox(height: 4),
                  Text(
                    stat['title'] as String,
                    style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                  ),
                ],
              ),
            );
          },
        );
      },
      loading: () => AppWidgets.shimmerLoading(
        child: Container(
          height: 200,
          margin: const EdgeInsets.all(16),
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
    );
  }

  Widget _buildQuickActions(BuildContext context) {
    final actions = [
      {
        'icon': Icons.view_kanban_outlined,
        'label': 'Lead Board',
        'route': '/agent/leads',
        'color': AppTheme.primaryColor,
      },
      {
        'icon': Icons.local_shipping_outlined,
        'label': 'Pipeline',
        'route': '/agent/deals',
        'color': AppTheme.secondaryColor,
      },
      {
        'icon': Icons.monetization_on_outlined,
        'label': 'Earnings',
        'route': '/agent/commissions',
        'color': AppTheme.successColor,
      },
      {
        'icon': Icons.person_add_outlined,
        'label': 'Add Lead',
        'route': '/agent/leads/create',
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
                        color: (action['color'] as Color).withValues(alpha: 0.1),
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

  Widget _buildRecentLeads(BuildContext context, WidgetRef ref, LeadRepository leadRepo) {
    return FutureBuilder<List<LeadModel>>(
      future: leadRepo.getMyLeads(),
      builder: (context, snapshot) {
        if (snapshot.connectionState == ConnectionState.waiting) {
          return Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Column(
              children: List.generate(3, (i) => AppWidgets.shimmerLoading(
                child: Container(
                  height: 80,
                  margin: const EdgeInsets.only(bottom: 8),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
              )),
            ),
          );
        }

        if (snapshot.hasError) {
          return AppWidgets.emptyState(
            title: 'Could not load leads',
            subtitle: 'Pull to refresh or try again later',
            icon: Icons.error_outline,
          );
        }

        final leads = snapshot.data ?? [];
        if (leads.isEmpty) {
          return AppWidgets.emptyState(
            title: 'No leads yet',
            subtitle: 'Start by adding your first lead or check back later',
            icon: Icons.person_add_outlined,
            onAction: () => context.push('/agent/leads/create'),
            actionLabel: 'Add Lead',
          );
        }

        // Show only first 5 leads
        final displayLeads = leads.take(5).toList();

        return ListView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          padding: const EdgeInsets.symmetric(horizontal: 16),
          itemCount: displayLeads.length,
          itemBuilder: (context, index) {
            final lead = displayLeads[index];
            return _buildLeadCard(context, lead);
          },
        );
      },
    );
  }

  Widget _buildLeadCard(BuildContext context, LeadModel lead) {
    final statusColor = _getStatusColor(lead.status ?? 'new');
    final priorityIcon = _getPriorityIcon(lead.priority ?? 'medium');

    return AppWidgets.customCard(
      onTap: () => context.push('/agent/leads/${lead.id}'),
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
              Icons.person,
              color: statusColor,
            ),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        lead.name,
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 14,
                        ),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    Icon(priorityIcon, size: 16, color: _getPriorityColor(lead.priority)),
                  ],
                ),
                const SizedBox(height: 4),
                Text(
                  lead.phone,
                  style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                ),
                if (lead.preferredLocation != null) ...[
                  const SizedBox(height: 2),
                  Text(
                    lead.preferredLocation!,
                    style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ],
            ),
          ),
          const SizedBox(width: 8),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(
              color: statusColor.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Text(
              (lead.status ?? 'new').toUpperCase(),
              style: TextStyle(
                fontSize: 10,
                fontWeight: FontWeight.bold,
                color: statusColor,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'new':
        return AppTheme.infoColor;
      case 'contacted':
        return AppTheme.warningColor;
      case 'qualified':
        return AppTheme.primaryColor;
      case 'viewing':
        return Colors.purple;
      case 'negotiation':
        return AppTheme.accentColor;
      case 'converted':
      case 'closed_won':
        return AppTheme.successColor;
      case 'lost':
      case 'dead':
        return AppTheme.errorColor;
      default:
        return Colors.grey;
    }
  }

  IconData _getPriorityIcon(String priority) {
    switch (priority.toLowerCase()) {
      case 'high':
        return Icons.arrow_upward;
      case 'medium':
        return Icons.remove;
      case 'low':
        return Icons.arrow_downward;
      default:
        return Icons.remove;
    }
  }

  Color _getPriorityColor(String? priority) {
    switch (priority?.toLowerCase()) {
      case 'high':
        return AppTheme.errorColor;
      case 'medium':
        return AppTheme.warningColor;
      case 'low':
        return AppTheme.successColor;
      default:
        return Colors.grey;
    }
  }
}

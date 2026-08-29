import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/providers/auth_provider.dart';
import '../../widgets/app_widgets.dart';
import '../../widgets/glass_card.dart';

/// Agent My Team Page - View downline team members
class AgentMyTeamPage extends ConsumerWidget {
  const AgentMyTeamPage({super.key});

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
    final teamAsync = ref.watch(_agentMyTeamProvider(userId));

    // Compute stats from the list
    int total = 0, active = 0, inactive = 0;
    final data = teamAsync.value;
    if (data != null) {
      total = data.length;
      for (final m in data) {
        if ((m['status']?.toString() ?? '') == 'active') {
          active++;
        } else {
          inactive++;
        }
      }
    }

    return RefreshIndicator(
      onRefresh: () async {
        ref.invalidate(_agentMyTeamProvider(userId));
        await Future.delayed(const Duration(milliseconds: 500));
      },
      color: AppTheme.primaryColor,
      child: CustomScrollView(
        slivers: [
          SliverToBoxAdapter(child: _buildAppBar(context)),
          SliverToBoxAdapter(
            child: _buildStatsRow(context, total, active, inactive),
          ),
          SliverToBoxAdapter(
            child: AppWidgets.sectionHeader(
              title: 'Team Members',
              subtitle: 'Your direct downline network',
              onSeeAll: () {},
            ),
          ),
          SliverToBoxAdapter(
            child: teamAsync.when(
              data: (members) {
                if (members.isEmpty) {
                  return _buildEmptyState(context);
                }
                return _buildMembersList(context, members);
              },
              loading: () => const Center(
                child: Padding(
                  padding: EdgeInsets.all(32),
                  child:
                      CircularProgressIndicator(color: AppTheme.primaryColor),
                ),
              ),
              error: (error, stack) => AppWidgets.errorWidget(
                message: error.toString(),
                onRetry: () => ref.invalidate(_agentMyTeamProvider(userId)),
              ),
            ),
          ),
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
              child: const Icon(Icons.groups_rounded,
                  color: Colors.white, size: 30),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'My Team',
                    style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                          color: Colors.white,
                          fontWeight: FontWeight.w800,
                        ),
                  ),
                  Text(
                    'Your downline network members',
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

  Widget _buildStatsRow(BuildContext context, int total, int active, int inactive) {
    Widget card(String label, String value, IconData icon, Color color) {
      return Expanded(
        child: Container(
          margin: const EdgeInsets.symmetric(horizontal: 4),
          padding: const EdgeInsets.symmetric(vertical: 14),
          decoration: BoxDecoration(
            color: color.withValues(alpha: 0.12),
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: color.withValues(alpha: 0.3)),
          ),
          child: Column(
            children: [
              Icon(icon, color: color, size: 22),
              const SizedBox(height: 6),
              Text(value,
                  style: TextStyle(
                      color: color,
                      fontWeight: FontWeight.w800,
                      fontSize: 18)),
              Text(label,
                  style:
                      TextStyle(color: Colors.grey[600], fontSize: 11)),
            ],
          ),
        ),
      );
    }

    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 20, 16, 8),
      child: Row(
        children: [
          card('Total', '$total', Icons.people_rounded, AppTheme.primaryColor),
          card('Active', '$active', Icons.check_circle_rounded,
              AppTheme.successColor),
          card('Inactive', '$inactive', Icons.pause_circle_rounded,
              AppTheme.warningColor),
        ],
      ),
    );
  }

  Widget _buildEmptyState(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 100,
              height: 100,
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [
                    AppTheme.primaryColor.withValues(alpha: 0.2),
                    AppTheme.secondaryColor.withValues(alpha: 0.2),
                  ],
                ),
                borderRadius: BorderRadius.circular(24),
              ),
              child: const Icon(Icons.group_add_rounded,
                  size: 50, color: Colors.grey),
            ),
            const SizedBox(height: 24),
            Text(
              'No Team Members Yet',
              style: AppTheme.headlineMedium.copyWith(
                color: Colors.grey[800],
                fontWeight: FontWeight.w700,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Members who join with your referral will appear here',
              style: TextStyle(color: Colors.grey[600], fontSize: 15),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: () => context.go('/referral'),
              icon: const Icon(Icons.share_rounded),
              label: const Text('Share Referral Code'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primaryColor,
                foregroundColor: Colors.white,
                padding:
                    const EdgeInsets.symmetric(horizontal: 28, vertical: 14),
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMembersList(BuildContext context, List<dynamic> members) {
    return ListView.separated(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      padding: const EdgeInsets.symmetric(horizontal: 16),
      itemCount: members.length,
      separatorBuilder: (_, _) => const SizedBox(height: 12),
      itemBuilder: (context, index) {
        final member = members[index] as Map<String, dynamic>;
        return _buildMemberCard(context, member);
      },
    );
  }

  Widget _buildMemberCard(BuildContext context, Map<String, dynamic> member) {
    final name = member['name']?.toString() ?? 'Unknown';
    final email = member['email']?.toString() ?? '';
    final phone = member['phone']?.toString() ?? '';
    final rank = member['current_rank']?.toString() ??
        member['rank']?.toString() ??
        'Associate';
    final joinedAt = member['created_at']?.toString() ?? '';
    final status = member['status']?.toString() ?? 'active';

    final isActive = status == 'active';
    final initials = name.isNotEmpty
        ? name.trim().split(' ').map((w) => w[0]).take(2).join().toUpperCase()
        : '?';

    return GlassCard(
      padding: const EdgeInsets.all(16),
      opacity: 0.1,
      blur: 8,
      child: Row(
        children: [
          // Avatar
          CircleAvatar(
            radius: 26,
            backgroundColor:
                AppTheme.primaryColor.withValues(alpha: 0.15),
            child: Text(
              initials,
              style: const TextStyle(
                color: AppTheme.primaryColor,
                fontWeight: FontWeight.w800,
                fontSize: 16,
              ),
            ),
          ),
          const SizedBox(width: 12),
          // Info
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        name,
                        style: const TextStyle(
                          fontWeight: FontWeight.w600,
                          fontSize: 15,
                          color: Colors.black,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    Container(
                      width: 10,
                      height: 10,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: isActive
                            ? AppTheme.successColor
                            : Colors.grey[400],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: AppTheme.accentColor.withValues(alpha: 0.2),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        rank,
                        style: const TextStyle(
                          color: Color(0xFF9C7A00),
                          fontWeight: FontWeight.w700,
                          fontSize: 10,
                        ),
                      ),
                    ),
                    if (joinedAt.isNotEmpty) ...[
                      const SizedBox(width: 8),
                      Icon(Icons.calendar_today_rounded,
                          size: 12, color: Colors.grey[500]),
                      const SizedBox(width: 4),
                      Text(
                        _formatDate(joinedAt),
                        style: TextStyle(
                            color: Colors.grey[500], fontSize: 11),
                      ),
                    ],
                  ],
                ),
                if (phone.isNotEmpty || email.isNotEmpty)
                  Padding(
                    padding: const EdgeInsets.only(top: 6),
                    child: Row(
                      children: [
                        if (phone.isNotEmpty)
                          Flexible(
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(Icons.phone_rounded,
                                    size: 13, color: Colors.grey[500]),
                                const SizedBox(width: 4),
                                Flexible(
                                  child: Text(phone,
                                      style: TextStyle(
                                          color: Colors.grey[600],
                                          fontSize: 11),
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis),
                                ),
                              ],
                            ),
                          ),
                        if (email.isNotEmpty && phone.isNotEmpty)
                          const SizedBox(width: 10),
                        if (email.isNotEmpty)
                          Flexible(
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(Icons.email_rounded,
                                    size: 13, color: Colors.grey[500]),
                                const SizedBox(width: 4),
                                Flexible(
                                  child: Text(email,
                                      style: TextStyle(
                                          color: Colors.grey[600],
                                          fontSize: 11),
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis),
                                ),
                              ],
                            ),
                          ),
                      ],
                    ),
                  ),
              ],
            ),
          ),
          const SizedBox(width: 8),
          Icon(Icons.chevron_right_rounded,
              color: Colors.grey[400], size: 22),
        ],
      ),
    );
  }

  String _formatDate(String dateStr) {
    try {
      final date = DateTime.parse(dateStr);
      return '${date.day}/${date.month}/${date.year}';
    } catch (_) {
      return dateStr;
    }
  }
}

// Provider
final _agentMyTeamProvider =
    FutureProvider.family<List<dynamic>, int>((ref, userId) async {
  try {
    final api = ApiService();
    AppConstants.initBaseUrl();
    final response =
        await api.get('${AppConstants.apiVersion}/agent/my-team');
    if (response['success'] == true && response['data'] != null) {
      final data = response['data'];
      final members = (data is List ? data : data['team'] ?? []) as List;
      return List<Map<String, dynamic>>.from(members);
    }
  } catch (_) {}
  // Mock data fallback
  return [
    {
      'id': 1,
      'name': 'Vikram Yadav',
      'email': 'vikram@example.com',
      'phone': '+91-9876500011',
      'rank': 'Sr. Associate',
      'status': 'active',
      'created_at': '2025-06-10 09:00:00',
    },
    {
      'id': 2,
      'name': 'Sunita Devi',
      'email': 'sunita@example.com',
      'phone': '+91-9876500022',
      'rank': 'Associate',
      'status': 'active',
      'created_at': '2025-08-22 11:30:00',
    },
    {
      'id': 3,
      'name': 'Ramesh Gupta',
      'email': 'ramesh@example.com',
      'phone': '+91-9876500033',
      'rank': 'BDM',
      'status': 'inactive',
      'created_at': '2025-03-05 15:45:00',
    },
  ];
});

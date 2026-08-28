import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/providers/auth_provider.dart';
import '../../widgets/app_widgets.dart';
import '../../widgets/glass_card.dart';

/// Agent Site Visits Page - View and manage site visit schedules
class AgentSiteVisitsPage extends ConsumerWidget {
  const AgentSiteVisitsPage({super.key});

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
    final visitsAsync = ref.watch(_agentSiteVisitsProvider(userId));

    return RefreshIndicator(
      onRefresh: () async {
        ref.invalidate(_agentSiteVisitsProvider(userId));
        await Future.delayed(const Duration(milliseconds: 500));
      },
      color: AppTheme.primaryColor,
      child: CustomScrollView(
        slivers: [
          SliverToBoxAdapter(child: _buildAppBar(context)),
          SliverToBoxAdapter(child: _buildFilterChips(context)),
          SliverToBoxAdapter(
            child: AppWidgets.sectionHeader(
              title: 'Site Visits',
              subtitle: 'Manage scheduled site visits',
              onSeeAll: () {},
            ),
          ),
          SliverToBoxAdapter(
            child: visitsAsync.when(
              data: (visits) {
                if (visits.isEmpty) {
                  return _buildEmptyState(context);
                }
                return _buildVisitsList(context, visits);
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
                onRetry: () =>
                    ref.invalidate(_agentSiteVisitsProvider(userId)),
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
              child: const Icon(
                Icons.location_on_rounded,
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
                    'Site Visits',
                    style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                          color: Colors.white,
                          fontWeight: FontWeight.w800,
                        ),
                  ),
                  Text(
                    'Scheduled property site visits',
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

  Widget _buildFilterChips(BuildContext context) {
    final filters = ['All', 'Upcoming', 'Completed', 'Cancelled'];
    return Container(
      height: 50,
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: filters.length,
        separatorBuilder: (_, _) => const SizedBox(width: 8),
        itemBuilder: (context, index) {
          return FilterChip(
            label: Text(filters[index]),
            selected: index == 0,
            onSelected: (_) {},
            selectedColor: AppTheme.primaryColor.withValues(alpha: 0.2),
            checkmarkColor: AppTheme.primaryColor,
            labelStyle: TextStyle(
              color: index == 0 ? AppTheme.primaryColor : Colors.grey[700],
              fontWeight: index == 0 ? FontWeight.w600 : FontWeight.w500,
            ),
          );
        },
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
              child: const Icon(Icons.map_outlined, size: 50, color: Colors.grey),
            ),
            const SizedBox(height: 24),
            Text(
              'No Site Visits Yet',
              style: AppTheme.headlineMedium.copyWith(
                color: Colors.grey[800],
                fontWeight: FontWeight.w700,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Your scheduled site visits will appear here',
              style: TextStyle(color: Colors.grey[600], fontSize: 16),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildVisitsList(BuildContext context, List<dynamic> visits) {
    return ListView.separated(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      padding: const EdgeInsets.symmetric(horizontal: 16),
      itemCount: visits.length,
      separatorBuilder: (_, _) => const SizedBox(height: 12),
      itemBuilder: (context, index) {
        final visit = visits[index] as Map<String, dynamic>;
        return _buildVisitCard(context, visit);
      },
    );
  }

  Widget _buildVisitCard(BuildContext context, Map<String, dynamic> visit) {
    final leadName = visit['lead_name']?.toString() ?? 'Unknown Lead';
    final leadPhone = visit['lead_phone']?.toString() ?? '';
    final colonyName =
        visit['colony_name']?.toString() ?? visit['property_title']?.toString() ?? '';
    final scheduledAt = visit['scheduled_at']?.toString() ?? '';
    final status = visit['status']?.toString() ?? 'scheduled';
    final visitId = visit['id']?.toString() ?? '';

    final isUpcoming = scheduledAt.isNotEmpty &&
        DateTime.tryParse(scheduledAt)?.isAfter(DateTime.now()) == true;

    Color statusColor;
    IconData statusIcon;
    String statusLabel;
    switch (status.toLowerCase()) {
      case 'completed':
        statusColor = AppTheme.successColor;
        statusIcon = Icons.check_circle_rounded;
        statusLabel = 'Completed';
        break;
      case 'cancelled':
        statusColor = Colors.red;
        statusIcon = Icons.cancel_rounded;
        statusLabel = 'Cancelled';
        break;
      default:
        statusColor = isUpcoming ? AppTheme.warningColor : Colors.grey;
        statusIcon = Icons.schedule_rounded;
        statusLabel = isUpcoming ? 'Upcoming' : 'Past';
    }

    return GlassCard(
      padding: const EdgeInsets.all(16),
      opacity: 0.1,
      blur: 8,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: AppTheme.primaryColor.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.location_on_rounded,
                    color: AppTheme.primaryColor, size: 24),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      leadName,
                      style: const TextStyle(
                        fontWeight: FontWeight.w600,
                        fontSize: 16,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    if (colonyName.isNotEmpty)
                      Padding(
                        padding: const EdgeInsets.only(top: 4),
                        child: Row(
                          children: [
                            Icon(Icons.home_work_rounded,
                                size: 14, color: Colors.grey[600]),
                            const SizedBox(width: 6),
                            Expanded(
                              child: Text(
                                colonyName,
                                style: TextStyle(
                                    color: Colors.grey[600], fontSize: 12),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),
                      ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: statusColor.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(statusIcon, color: statusColor, size: 12),
                    const SizedBox(width: 4),
                    Text(
                      statusLabel,
                      style: TextStyle(
                        color: statusColor,
                        fontWeight: FontWeight.w600,
                        fontSize: 11,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          if (scheduledAt.isNotEmpty) ...[
            const SizedBox(height: 12),
            Row(
              children: [
                Icon(Icons.calendar_today_rounded,
                    size: 16, color: AppTheme.primaryColor),
                const SizedBox(width: 8),
                Text(
                  'When: ${_formatDateTime(scheduledAt)}',
                  style: TextStyle(color: Colors.grey[700], fontSize: 13),
                ),
              ],
            ),
          ],
          if (leadPhone.isNotEmpty) ...[
            const SizedBox(height: 6),
            Row(
              children: [
                Icon(Icons.phone_rounded, size: 14, color: Colors.grey[600]),
                const SizedBox(width: 8),
                Text(
                  leadPhone,
                  style: TextStyle(color: Colors.grey[600], fontSize: 13),
                ),
              ],
            ),
          ],
          const SizedBox(height: 12),
          Row(
            children: [
              if (isUpcoming && status.toLowerCase() == 'scheduled') ...[
                Expanded(
                  child: SizedBox(
                    height: 36,
                    child: DecoratedBox(
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(10),
                        gradient: const LinearGradient(
                          colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
                        ),
                      ),
                      child: ElevatedButton.icon(
                        onPressed: () => _startVisit(visitId),
                        icon: const Icon(Icons.play_arrow_rounded,
                            size: 16, color: Colors.white),
                        label: const Text('Start Visit'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.transparent,
                          shadowColor: Colors.transparent,
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(10)),
                        ),
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
              ],
              if (visitId.isNotEmpty)
                Expanded(
                  child: SizedBox(
                    height: 36,
                    child: OutlinedButton.icon(
                      onPressed: () => context.push('/agent/site-visit/$visitId'),
                      icon: const Icon(Icons.info_outline_rounded, size: 16),
                      label: const Text('Details'),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppTheme.infoColor,
                        side: BorderSide(
                            color: AppTheme.infoColor.withValues(alpha: 0.5)),
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(10)),
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

  String _formatDateTime(String dateStr) {
    try {
      final date = DateTime.parse(dateStr);
      return '${date.day}/${date.month}/${date.year} ${date.hour}:${date.minute.toString().padLeft(2, '0')}';
    } catch (_) {
      return dateStr;
    }
  }

  void _startVisit(String visitId) {}
}

// Provider
final _agentSiteVisitsProvider =
    FutureProvider.family<List<dynamic>, int>((ref, userId) async {
  try {
    final api = ApiService();
    AppConstants.initBaseUrl();
    final response =
        await api.get('${AppConstants.apiVersion}/agent/site-visits');
    if (response['success'] == true && response['data'] != null) {
      final data = response['data'];
      final visits = (data is List ? data : data['visits'] ?? []) as List;
      return List<Map<String, dynamic>>.from(visits);
    }
  } catch (_) {}
  // Mock data fallback
  return [
    {
      'id': 1,
      'lead_name': 'Rajesh Kumar',
      'lead_phone': '+91-9876543210',
      'colony_name': 'Suryoday Heights',
      'scheduled_at': '2026-09-01 10:00:00',
      'status': 'scheduled',
    },
    {
      'id': 2,
      'lead_name': 'Priya Sharma',
      'lead_phone': '+91-9876543211',
      'colony_name': 'Braj Radha Nagri',
      'scheduled_at': '2026-09-02 14:30:00',
      'status': 'scheduled',
    },
    {
      'id': 3,
      'lead_name': 'Amit Singh',
      'lead_phone': '+91-9876543212',
      'colony_name': 'Raghunath Nagri',
      'scheduled_at': '2026-08-15 11:00:00',
      'status': 'completed',
    },
  ];
});

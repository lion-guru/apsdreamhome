import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/providers/auth_provider.dart';
import '../../widgets/app_widgets.dart';
import '../../widgets/glass_card.dart';

/// Agent Follow-ups Page - View and manage lead follow-ups
class AgentFollowUpsPage extends ConsumerWidget {
  const AgentFollowUpsPage({super.key});

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
    final followUpsAsync = ref.watch(_agentFollowUpsProvider(userId));

    return RefreshIndicator(
      onRefresh: () async {
        ref.invalidate(_agentFollowUpsProvider(userId));
        await Future.delayed(const Duration(milliseconds: 500));
      },
      color: AppTheme.primaryColor,
      child: CustomScrollView(
        slivers: [
          SliverToBoxAdapter(child: _buildAppBar(context)),
          SliverToBoxAdapter(child: _buildFilterChips(context)),
          SliverToBoxAdapter(
            child: AppWidgets.sectionHeader(
              title: 'Follow-ups',
              subtitle: 'Manage your lead follow-ups',
              onSeeAll: () {},
            ),
          ),
          SliverToBoxAdapter(
            child: followUpsAsync.when(
              data: (followUps) {
                if (followUps.isEmpty) {
                  return _buildEmptyState(context);
                }
                return _buildFollowUpsList(context, followUps);
              },
              loading: () => const Center(
                child: Padding(
                  padding: EdgeInsets.all(32),
                  child: CircularProgressIndicator(color: AppTheme.primaryColor),
                ),
              ),
              error: (error, stack) => AppWidgets.errorWidget(
                message: error.toString(),
                onRetry: () => ref.invalidate(_agentFollowUpsProvider(userId)),
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
                Icons.flag_rounded,
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
                    'Follow-ups',
                    style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                      color: Colors.white,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                  Text(
                    'Track and manage your follow-ups',
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
    final filters = ['All', 'Overdue', 'Due Today', 'Upcoming', 'Completed'];
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
              child: const Icon(Icons.flag_outlined, size: 50, color: Colors.grey),
            ),
            const SizedBox(height: 24),
            Text(
              'No Follow-ups Yet',
              style: AppTheme.headlineMedium.copyWith(
                color: Colors.grey[800],
                fontWeight: FontWeight.w700,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Your follow-ups will appear here',
              style: TextStyle(color: Colors.grey[600], fontSize: 16),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFollowUpsList(BuildContext context, List<dynamic> followUps) {
    return ListView.separated(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      padding: const EdgeInsets.symmetric(horizontal: 16),
      itemCount: followUps.length,
      separatorBuilder: (_, _) => const SizedBox(height: 12),
      itemBuilder: (context, index) {
        final followUp = followUps[index] as Map<String, dynamic>;
        return _buildFollowUpCard(context, followUp);
      },
    );
  }

  Widget _buildFollowUpCard(BuildContext context, Map<String, dynamic> followUp) {
    final leadName = followUp['lead_name']?.toString() ?? 'Unknown Lead';
    final leadPhone = followUp['lead_phone']?.toString() ?? '';
    final scheduledAt = followUp['scheduled_at']?.toString() ?? '';
    final type = followUp['type_label']?.toString() ?? followUp['type']?.toString() ?? 'Call';
    final status = followUp['status']?.toString() ?? 'pending';
    final notes = followUp['notes']?.toString() ?? '';
    final leadId = followUp['lead_id']?.toString() ?? '';

    final isOverdue = scheduledAt.isNotEmpty &&
        DateTime.tryParse(scheduledAt)?.isBefore(DateTime.now()) == true &&
        status != 'completed';

    final statusColor = status == 'completed'
        ? AppTheme.successColor
        : (isOverdue ? Colors.red : AppTheme.warningColor);
    final statusLabel =
        status == 'completed' ? 'Completed' : (isOverdue ? 'Overdue' : 'Pending');
    final statusIcon = status == 'completed'
        ? Icons.check_circle_rounded
        : (isOverdue ? Icons.warning_rounded : Icons.schedule_rounded);

    IconData typeIcon;
    Color typeColor;
    switch (type.toLowerCase()) {
      case 'call':
        typeIcon = Icons.phone_rounded;
        typeColor = AppTheme.primaryColor;
        break;
      case 'meeting':
        typeIcon = Icons.meeting_room_rounded;
        typeColor = AppTheme.successColor;
        break;
      case 'whatsapp':
        typeIcon = Icons.chat_rounded;
        typeColor = Colors.green;
        break;
      case 'email':
        typeIcon = Icons.email_rounded;
        typeColor = AppTheme.infoColor;
        break;
      case 'site_visit':
        typeIcon = Icons.location_on_rounded;
        typeColor = AppTheme.warningColor;
        break;
      default:
        typeIcon = Icons.flag_rounded;
        typeColor = AppTheme.primaryColor;
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
                  color: typeColor.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(typeIcon, color: typeColor, size: 24),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      leadName,
                      style: const TextStyle(
                        color: Colors.black,
                        fontWeight: FontWeight.w600,
                        fontSize: 16,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        Icon(typeIcon, color: typeColor, size: 14),
                        const SizedBox(width: 6),
                        Text(
                          type,
                          style: TextStyle(
                            color: typeColor,
                            fontWeight: FontWeight.w600,
                            fontSize: 12,
                          ),
                        ),
                        if (leadPhone.isNotEmpty) ...[
                          const SizedBox(width: 12),
                          Icon(Icons.phone_rounded, size: 14, color: Colors.grey[600]),
                          const SizedBox(width: 4),
                          Text(
                            leadPhone,
                            style: TextStyle(color: Colors.grey[600], fontSize: 12),
                          ),
                        ],
                      ],
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
                Icon(
                  Icons.calendar_today_rounded,
                  size: 16,
                  color: isOverdue ? Colors.red : AppTheme.primaryColor,
                ),
                const SizedBox(width: 8),
                Text(
                  'Scheduled: ${_formatDateTime(scheduledAt)}',
                  style: TextStyle(
                    color: isOverdue ? Colors.red : Colors.grey[600],
                    fontSize: 13,
                    fontWeight: isOverdue ? FontWeight.w600 : FontWeight.w500,
                  ),
                ),
              ],
            ),
          ],
          if (notes.isNotEmpty) ...[
            const SizedBox(height: 12),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.grey[50],
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: Colors.grey[200]!),
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Icon(Icons.notes_rounded, size: 18, color: Colors.grey[600]),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      notes,
                      style: TextStyle(
                        color: Colors.grey[800],
                        fontSize: 13,
                        height: 1.4,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
          const SizedBox(height: 16),
          Row(
            children: [
              if (leadId.isNotEmpty)
                Expanded(
                  child: SizedBox(
                    height: 36,
                    child: OutlinedButton.icon(
                      onPressed: () => context.push('/agent/leads/$leadId'),
                      icon: const Icon(Icons.person_rounded, size: 16),
                      label: const Text('View Lead'),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppTheme.primaryColor,
                        side: BorderSide(
                            color: AppTheme.primaryColor.withValues(alpha: 0.5)),
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(10)),
                      ),
                    ),
                  ),
                ),
              if (leadId.isNotEmpty && status != 'completed')
                const SizedBox(width: 8),
              if (status != 'completed')
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
                        onPressed: () => _markCompleted(followUp),
                        icon: const Icon(Icons.check_circle_rounded,
                            size: 16, color: Colors.white),
                        label: const Text('Mark Done'),
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
              Expanded(
                child: SizedBox(
                  height: 36,
                  child: OutlinedButton.icon(
                    onPressed: () => _reschedule(followUp),
                    icon: const Icon(Icons.schedule_rounded, size: 16),
                    label: const Text('Reschedule'),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppTheme.infoColor,
                      side:
                          BorderSide(color: AppTheme.infoColor.withValues(alpha: 0.5)),
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

  void _markCompleted(Map<String, dynamic> followUp) {
    // TODO: Call API to mark as completed
  }

  void _reschedule(Map<String, dynamic> followUp) {
    // TODO: Navigate to reschedule screen
  }
}

// Provider
final _agentFollowUpsProvider =
    FutureProvider.family<List<dynamic>, int>((ref, userId) async {
  try {
    final api = ApiService();
    AppConstants.initBaseUrl();
    final response = await api.get('${AppConstants.apiVersion}/agent/follow-ups');
    if (response['success'] == true && response['data'] != null) {
      final data = response['data'];
      final followUps = (data is List ? data : data['follow_ups'] ?? []) as List;
      return List<Map<String, dynamic>>.from(followUps);
    }
  } catch (_) {}
  // Mock data fallback
  return [
    {
      'id': 1,
      'lead_id': 1,
      'lead_name': 'Rajesh Kumar',
      'lead_phone': '+91-9876543210',
      'type': 'call',
      'type_label': 'Call',
      'scheduled_at': '2026-01-20 10:00:00',
      'status': 'pending',
      'notes': 'Interested in 2BHK flat in Suryoday Colony.',
    },
    {
      'id': 2,
      'lead_id': 2,
      'lead_name': 'Priya Sharma',
      'lead_phone': '+91-9876543211',
      'type': 'meeting',
      'type_label': 'Meeting',
      'scheduled_at': '2026-01-21 14:30:00',
      'status': 'pending',
      'notes': 'Wants to discuss payment plans for Plot B-205',
    },
    {
      'id': 3,
      'lead_id': 3,
      'lead_name': 'Amit Singh',
      'lead_phone': '+91-9876543212',
      'type': 'site_visit',
      'type_label': 'Site Visit',
      'scheduled_at': '2026-01-22 10:00:00',
      'status': 'completed',
      'notes': 'Site visit completed. Client liked Plot C-303.',
    },
  ];
});

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../data/repositories/lead_repository.dart';
import '../../../data/models/lead_model_extended.dart';
import '../../widgets/app_widgets.dart';

/// Lead Kanban Board - drag-and-drop lead stages
class LeadKanbanPage extends ConsumerStatefulWidget {
  const LeadKanbanPage({super.key});

  @override
  ConsumerState<LeadKanbanPage> createState() => _LeadKanbanPageState();
}

class _LeadKanbanPageState extends ConsumerState<LeadKanbanPage>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  static const _stages = [
    {'key': 'new', 'label': 'New', 'icon': Icons.fiber_new, 'color': AppTheme.infoColor},
    {'key': 'contacted', 'label': 'Contacted', 'icon': Icons.phone, 'color': AppTheme.warningColor},
    {'key': 'qualified', 'label': 'Qualified', 'icon': Icons.verified, 'color': AppTheme.primaryColor},
    {'key': 'viewing', 'label': 'Viewing', 'icon': Icons.visibility, 'color': Colors.purple},
    {'key': 'negotiation', 'label': 'Negotiation', 'icon': Icons.handshake, 'color': AppTheme.accentColor},
    {'key': 'converted', 'label': 'Won', 'icon': Icons.check_circle, 'color': AppTheme.successColor},
  ];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: _stages.length, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Lead Board'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        bottom: TabBar(
          controller: _tabController,
          isScrollable: true,
          indicatorColor: Colors.white,
          indicatorWeight: 3,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white60,
          tabs: _stages.map((stage) {
            return Tab(
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(stage['icon'] as IconData, size: 16),
                  const SizedBox(width: 6),
                  Text(stage['label'] as String, style: const TextStyle(fontSize: 12)),
                ],
              ),
            );
          }).toList(),
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: _stages.map((stage) {
          return _buildStageView(stage['key'] as String, stage['color'] as Color);
        }).toList(),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push('/agent/leads/create'),
        backgroundColor: AppTheme.primaryColor,
        icon: const Icon(Icons.person_add, color: Colors.white),
        label: const Text('Add Lead', style: TextStyle(color: Colors.white)),
      ),
    );
  }

  Widget _buildStageView(String stage, Color stageColor) {
    final leadRepo = ref.watch(leadRepositoryProvider);

    return FutureBuilder<List<LeadModel>>(
      future: leadRepo.getMyLeads(status: stage),
      builder: (context, snapshot) {
        if (snapshot.connectionState == ConnectionState.waiting) {
          return Center(
            child: AppWidgets.shimmerLoading(
              child: Column(
                children: List.generate(4, (i) => Container(
                  height: 80,
                  margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                  ),
                )),
              ),
            ),
          );
        }

        if (snapshot.hasError) {
          return AppWidgets.errorWidget(
            message: snapshot.error.toString(),
            onRetry: () => setState(() {}),
          );
        }

        final leads = snapshot.data ?? [];

        if (leads.isEmpty) {
          return AppWidgets.emptyState(
            title: 'No leads in this stage',
            subtitle: 'Leads will appear here as they progress',
            icon: stageColor == AppTheme.successColor
                ? Icons.celebration
                : Icons.inbox_outlined,
          );
        }

        return Column(
          children: [
            // Stage summary
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              color: stageColor.withValues(alpha: 0.05),
              child: Row(
                children: [
                  Container(
                    width: 8,
                    height: 8,
                    decoration: BoxDecoration(
                      color: stageColor,
                      shape: BoxShape.circle,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Text(
                    '${leads.length} lead${leads.length != 1 ? 's' : ''}',
                    style: TextStyle(
                      fontWeight: FontWeight.w600,
                      color: stageColor,
                    ),
                  ),
                  const Spacer(),
                  Text(
                    'Drag to reorder',
                    style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                  ),
                ],
              ),
            ),

            // Lead list
            Expanded(
              child: ReorderableListView.builder(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                itemCount: leads.length,
                onReorder: (oldIndex, newIndex) {
                  // Reorder within stage (visual only for now)
                },
                itemBuilder: (context, index) {
                  final lead = leads[index];
                  return _buildKanbanCard(context, lead, stageColor);
                },
              ),
            ),
          ],
        );
      },
    );
  }

  Widget _buildKanbanCard(BuildContext context, LeadModel lead, Color stageColor) {
    final priorityColor = _getPriorityColor(lead.priority);
    final createdAt = lead.createdAt;

    return Card(
      key: ValueKey(lead.id),
      margin: const EdgeInsets.symmetric(horizontal: 4, vertical: 4),
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: () => _showLeadDetail(context, lead),
        onLongPress: () => _showLeadActions(context, lead),
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Name + Priority
              Row(
                children: [
                  CircleAvatar(
                    radius: 16,
                    backgroundColor: stageColor.withValues(alpha: 0.1),
                    child: Text(
                      lead.name.isNotEmpty ? lead.name[0].toUpperCase() : '?',
                      style: TextStyle(
                        color: stageColor,
                        fontWeight: FontWeight.bold,
                        fontSize: 14,
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          lead.name,
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 14,
                          ),
                          overflow: TextOverflow.ellipsis,
                        ),
                        Text(
                          lead.phone,
                          style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                        ),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                    decoration: BoxDecoration(
                      color: priorityColor.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      (lead.priority ?? 'M').toUpperCase(),
                      style: TextStyle(
                        fontSize: 9,
                        fontWeight: FontWeight.bold,
                        color: priorityColor,
                      ),
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 8),

              // Details chips
              Wrap(
                spacing: 6,
                runSpacing: 4,
                children: [
                  if (lead.source != null)
                    _chip(Icons.source, lead.source!, Colors.grey),
                  if (lead.preferredLocation != null)
                    _chip(Icons.location_on, lead.preferredLocation!, AppTheme.infoColor),
                  if (lead.budgetMax != null && lead.budgetMax! > 0)
                    _chip(Icons.currency_rupee, _formatBudget(lead.budgetMin, lead.budgetMax), AppTheme.successColor),
                ],
              ),

              // Follow-up note
              if (lead.followUpNotes != null && lead.followUpNotes!.isNotEmpty) ...[
                const SizedBox(height: 8),
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: Colors.grey.shade50,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Row(
                    children: [
                      Icon(Icons.notes, size: 14, color: Colors.grey.shade500),
                      const SizedBox(width: 6),
                      Expanded(
                        child: Text(
                          lead.followUpNotes!,
                          style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                ),
              ],

              // Bottom: created date
              if (createdAt != null) ...[
                const SizedBox(height: 8),
                Row(
                  children: [
                    Icon(Icons.access_time, size: 12, color: Colors.grey.shade400),
                    const SizedBox(width: 4),
                    Text(
                      _timeAgo(createdAt),
                      style: TextStyle(fontSize: 10, color: Colors.grey.shade400),
                    ),
                  ],
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _chip(IconData icon, String text, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(6),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 10, color: color),
          const SizedBox(width: 3),
          Text(
            text,
            style: TextStyle(fontSize: 10, color: color, fontWeight: FontWeight.w500),
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }

  void _showLeadDetail(BuildContext context, LeadModel lead) {
    context.push('/agent/leads/${lead.id}');
  }

  void _showLeadActions(BuildContext context, LeadModel lead) {
    showModalBottomSheet(
      context: context,
      builder: (context) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              leading: const Icon(Icons.edit),
              title: const Text('Edit Lead'),
              onTap: () {
                Navigator.pop(context);
                context.push('/agent/leads/${lead.id}/edit');
              },
            ),
            ListTile(
              leading: const Icon(Icons.phone),
              title: const Text('Call Lead'),
              onTap: () => Navigator.pop(context),
            ),
            ListTile(
              leading: const Icon(Icons.message),
              title: const Text('Send Message'),
              onTap: () => Navigator.pop(context),
            ),
            ListTile(
              leading: const Icon(Icons.arrow_forward, color: AppTheme.successColor),
              title: const Text('Advance Stage', style: TextStyle(color: AppTheme.successColor)),
              onTap: () {
                Navigator.pop(context);
                _showAdvanceStageDialog(context, lead);
              },
            ),
            ListTile(
              leading: const Icon(Icons.close, color: AppTheme.errorColor),
              title: const Text('Mark as Lost', style: TextStyle(color: AppTheme.errorColor)),
              onTap: () => Navigator.pop(context),
            ),
          ],
        ),
      ),
    );
  }

  void _showAdvanceStageDialog(BuildContext context, LeadModel lead) {
    final stages = ['new', 'contacted', 'qualified', 'viewing', 'negotiation', 'converted'];
    final currentIndex = stages.indexOf(lead.status ?? 'new');

    if (currentIndex >= stages.length - 1) {
      AppWidgets.showSuccessSnackBar(context, 'Lead is already at final stage');
      return;
    }

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Advance Lead Stage'),
        content: Text(
          'Move "${lead.name}" from "${stages[currentIndex]}" to "${stages[currentIndex + 1]}"?',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(context);
              try {
                await ref.read(leadRepositoryProvider).updateLeadStatus(
                      lead.id,
                      stages[currentIndex + 1],
                    );
                if (mounted) {
                  AppWidgets.showSuccessSnackBar(
                    context,
                    'Lead advanced to ${stages[currentIndex + 1]}',
                  );
                  setState(() {});
                }
              } catch (e) {
                if (mounted) {
                  AppWidgets.showErrorSnackBar(context, 'Failed: $e');
                }
              }
            },
            child: const Text('Advance'),
          ),
        ],
      ),
    );
  }

  String _formatBudget(double? min, double? max) {
    if (max == null || max <= 0) return '';
    final formatted = max >= 10000000
        ? '${(max / 10000000).toStringAsFixed(1)} Cr'
        : max >= 100000
            ? '${(max / 100000).toStringAsFixed(1)} L'
            : '${(max / 1000).toStringAsFixed(0)} K';
    if (min != null && min > 0) {
      final minFormatted = min >= 100000
          ? '${(min / 100000).toStringAsFixed(1)} L'
          : '${(min / 1000).toStringAsFixed(0)} K';
      return '$minFormatted - $formatted';
    }
    return 'Up to $formatted';
  }

  String _timeAgo(DateTime dateTime) {
    final diff = DateTime.now().difference(dateTime);
    if (diff.inMinutes < 60) return '${diff.inMinutes}m ago';
    if (diff.inHours < 24) return '${diff.inHours}h ago';
    if (diff.inDays < 7) return '${diff.inDays}d ago';
    return '${(diff.inDays / 7).floor()}w ago';
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

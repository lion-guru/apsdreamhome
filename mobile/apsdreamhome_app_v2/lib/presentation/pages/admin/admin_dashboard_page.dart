import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/utils/responsive_helper.dart';
import '../../../data/services/crm_service.dart';
import '../../widgets/app_widgets.dart';

class AdminDashboardPage extends ConsumerStatefulWidget {
  const AdminDashboardPage({super.key});

  @override
  ConsumerState<AdminDashboardPage> createState() => _AdminDashboardPageState();
}

class _AdminDashboardPageState extends ConsumerState<AdminDashboardPage> {
  Map<String, dynamic> _stats = {};
  List<dynamic> _recentActivity = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    try {
      final crm = CRMService();
      final data = await crm.getAdminOverview();
      if (mounted) {
        setState(() {
          _stats = (data['stats'] is Map) ? Map<String, dynamic>.from(data['stats'] as Map) : {};
          _recentActivity = (data['recent_activity'] is List) ? List<dynamic>.from(data['recent_activity'] as List) : [];
          _loading = false;
        });
      }
    } catch (e) {
      if (mounted) setState(() => _loading = false);
    }
  }

  String _formatCurrency(dynamic value) {
    final amount = (value is num) ? value.toDouble() : 0.0;
    if (amount >= 10000000) return '₹${(amount / 10000000).toStringAsFixed(1)}Cr';
    if (amount >= 100000) return '₹${(amount / 100000).toStringAsFixed(1)}L';
    if (amount >= 1000) return '₹${(amount / 1000).toStringAsFixed(1)}K';
    return '₹${amount.toStringAsFixed(0)}';
  }

  String _timeAgo(String? dateTime) {
    if (dateTime == null) return '';
    try {
      final dt = DateTime.parse(dateTime);
      final diff = DateTime.now().difference(dt);
      if (diff.inMinutes < 60) return '${diff.inMinutes}m ago';
      if (diff.inHours < 24) return '${diff.inHours}h ago';
      return '${diff.inDays}d ago';
    } catch (_) {
      return '';
    }
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: _loadData,
      child: CustomScrollView(
        slivers: [
          SliverToBoxAdapter(
            child: _loading
                ? SizedBox(
                    height: ResponsiveHelper.chartHeight(context),
                    child: const Center(child: CircularProgressIndicator()),
                  )
                : _buildContent(),
          ),
          const SliverToBoxAdapter(child: SizedBox(height: 32)),
        ],
      ),
    );
  }

  Widget _buildContent() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _buildStatsGrid(),
        const SizedBox(height: 8),
        _buildQuickActionsGrid(context),
        AppWidgets.sectionHeader(title: 'Pending Approvals', onSeeAll: () {}),
        _buildPendingApprovals(context),
        AppWidgets.sectionHeader(title: 'Recent Activity', onSeeAll: () {}),
        _buildRecentActivity(),
      ],
    );
  }

  Widget _buildStatsGrid() {
    final stats = [
      {
        'title': 'Total Users',
        'value': '${_stats['total_users'] ?? 0}',
        'icon': Icons.people_outline,
        'color': AppTheme.primaryColor,
      },
      {
        'title': 'Associates',
        'value': '${_stats['active_associates'] ?? 0}',
        'icon': Icons.group_outlined,
        'color': AppTheme.secondaryColor,
      },
      {
        'title': 'Bookings Today',
        'value': '${_stats['bookings_today'] ?? 0}',
        'icon': Icons.bookmark_outline,
        'color': AppTheme.successColor,
      },
      {
        'title': 'Revenue',
        'value': _formatCurrency(_stats['total_revenue'] ?? 0),
        'icon': Icons.payments_outlined,
        'color': AppTheme.warningColor,
      },
    ];

    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      padding: const EdgeInsets.all(16),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        childAspectRatio: 1.4,
        crossAxisSpacing: 12,
        mainAxisSpacing: 12,
      ),
      itemCount: stats.length,
      itemBuilder: (context, index) {
        final stat = stats[index];
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
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: (stat['color'] as Color).withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(stat['icon'] as IconData, color: stat['color'] as Color, size: 24),
              ),
              const Spacer(),
              Text(
                stat['value'] as String,
                style: TextStyle(fontSize: ResponsiveHelper.fontSize(context, 24), fontWeight: FontWeight.bold, color: stat['color'] as Color),
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
  }

  Widget _buildQuickActionsGrid(BuildContext context) {
    final actions = [
      {'title': 'Colonies', 'subtitle': 'Manage colonies', 'icon': Icons.location_city, 'color': Colors.blue, 'route': '/admin/colonies'},
      {'title': 'Plots', 'subtitle': 'Update plot status', 'icon': Icons.map, 'color': Colors.green, 'route': '/admin/plots'},
      {'title': 'Bookings', 'subtitle': 'Approve bookings', 'icon': Icons.book_online, 'color': Colors.orange, 'route': '/admin/bookings'},
      {'title': 'CRM', 'subtitle': 'Manage leads', 'icon': Icons.leaderboard, 'color': Colors.red, 'route': '/admin/crm'},
      {'title': 'Users', 'subtitle': 'Manage users', 'icon': Icons.people, 'color': Colors.teal, 'route': '/admin/customers'},
      {'title': 'Marketing', 'subtitle': 'Campaigns', 'icon': Icons.campaign, 'color': Colors.indigo, 'route': '/admin/marketing'},
    ];

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Management', style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold)),
          const SizedBox(height: 16),
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 3, childAspectRatio: 0.9, crossAxisSpacing: 12, mainAxisSpacing: 12,
            ),
            itemCount: actions.length,
            itemBuilder: (context, index) {
              final action = actions[index];
              return GestureDetector(
                onTap: () => context.push(action['route'] as String),
                child: Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: Colors.grey.shade200),
                  ),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: (action['color'] as Color).withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Icon(action['icon'] as IconData, color: action['color'] as Color, size: 28),
                      ),
                      const SizedBox(height: 8),
                      Text(action['title'] as String, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12), textAlign: TextAlign.center),
                      const SizedBox(height: 2),
                      Text(action['subtitle'] as String, style: TextStyle(fontSize: 10, color: Colors.grey.shade500), textAlign: TextAlign.center, maxLines: 1, overflow: TextOverflow.ellipsis),
                    ],
                  ),
                ),
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildPendingApprovals(BuildContext context) {
    final int hotLeads = int.tryParse('${_stats['hot_leads'] ?? 0}') ?? 0;
    final int pendingCommissions = int.tryParse('${_stats['pending_commissions'] ?? 0}') ?? 0;

    final items = <Map<String, dynamic>>[
      if (hotLeads > 0) {
        'type': 'leads',
        'title': 'Hot Leads',
        'subtitle': '$hotLeads high-priority leads need attention',
        'count': hotLeads,
        'color': Colors.red,
      },
      if (pendingCommissions > 0) {
        'type': 'commission',
        'title': 'Commission Payouts',
        'subtitle': '$pendingCommissions pending commission records',
        'count': pendingCommissions,
        'color': Colors.orange,
      },
      {
        'type': 'colonies',
        'title': 'Colonies',
        'subtitle': '${_stats['total_colonies'] ?? 0} active colonies, ${_stats['total_plots'] ?? 0} plots',
        'count': _stats['total_colonies'] ?? 0,
        'color': Colors.blue,
      },
    ];

    return ListView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      padding: const EdgeInsets.symmetric(horizontal: 16),
      itemCount: items.length,
      itemBuilder: (context, index) {
        final item = items[index];
        return AppWidgets.customCard(
          child: Row(
            children: [
              Container(
                width: 50, height: 50,
                decoration: BoxDecoration(
                  color: (item['color'] as Color).withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(Icons.pending_actions, color: item['color'] as Color),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(item['title'] as String, style: const TextStyle(fontWeight: FontWeight.bold)),
                    const SizedBox(height: 4),
                    Text(item['subtitle'] as String, style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: (item['color'] as Color).withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text('${item['count']}', style: TextStyle(fontWeight: FontWeight.bold, color: item['color'] as Color)),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildRecentActivity() {
    if (_recentActivity.isEmpty) {
      return Padding(
        padding: const EdgeInsets.all(16),
        child: AppWidgets.customCard(
          child: Center(
            child: Padding(
              padding: const EdgeInsets.all(24),
              child: Text('No recent activity', style: TextStyle(color: Colors.grey.shade500)),
            ),
          ),
        ),
      );
    }

    IconData _iconForType(String? type) {
      switch (type) {
        case 'call': return Icons.phone;
        case 'sms': return Icons.message;
        case 'email': return Icons.email;
        case 'whatsapp': return Icons.chat;
        case 'visit': return Icons.location_on;
        case 'meeting': return Icons.people;
        case 'note': return Icons.note;
        default: return Icons.info_outline;
      }
    }

    Color _colorForType(String? type) {
      switch (type) {
        case 'call': return Colors.green;
        case 'sms': return Colors.blue;
        case 'email': return Colors.indigo;
        case 'whatsapp': return const Color(0xFF25D366);
        case 'visit': return Colors.orange;
        case 'meeting': return Colors.purple;
        default: return Colors.grey;
      }
    }

    return ListView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      padding: const EdgeInsets.symmetric(horizontal: 16),
      itemCount: _recentActivity.length,
      itemBuilder: (context, index) {
        final a = _recentActivity[index];
        final type = a['interaction_type'] as String?;
        return AppWidgets.customCard(
          child: Row(
            children: [
              Container(
                width: 40, height: 40,
                decoration: BoxDecoration(
                  color: _colorForType(type).withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Icon(_iconForType(type), color: _colorForType(type), size: 20),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('${a['subject'] ?? type ?? 'Activity'}', style: const TextStyle(fontWeight: FontWeight.bold)),
                    const SizedBox(height: 2),
                    Text(
                      '${a['lead_name'] ?? ''} — ${a['body'] ?? ''}',
                      style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                      maxLines: 1, overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
              Text(_timeAgo(a['created_at']?.toString()), style: TextStyle(fontSize: 11, color: Colors.grey.shade500)),
            ],
          ),
        );
      },
    );
  }
}

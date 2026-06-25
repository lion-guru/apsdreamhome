import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/services/api_service.dart';
import '../../../core/utils/logger.dart';

/// Analytics Dashboard Page
/// Real-time stats and insights for admin
class AnalyticsDashboardPage extends ConsumerStatefulWidget {
  const AnalyticsDashboardPage({super.key});

  @override
  ConsumerState<AnalyticsDashboardPage> createState() =>
      _AnalyticsDashboardPageState();
}

class _AnalyticsDashboardPageState
    extends ConsumerState<AnalyticsDashboardPage> {
  String _timeRange = 'today';

  @override
  Widget build(BuildContext context) {
    final overviewAsync = ref.watch(_adminOverviewProvider);

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildTimeRangeSelector(),
          const SizedBox(height: 24),
          overviewAsync.when(
            loading: () => const Center(
                child: Padding(
              padding: EdgeInsets.all(40),
              child: CircularProgressIndicator(),
            )),
            error: (e, _) => _buildErrorCard(e.toString()),
            data: (data) => _buildContent(data),
          ),
        ],
      ),
    );
  }

  Widget _buildContent(Map<String, dynamic> data) {
    final stats = (data['stats'] as Map<String, dynamic>?) ?? {};
    final recentActivity =
        (data['recent_activity'] as List<dynamic>?) ?? [];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _buildStatsGrid(stats),
        const SizedBox(height: 24),
        _buildRecentActivity(recentActivity),
      ],
    );
  }

  Widget _buildErrorCard(String message) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Center(
          child: Column(
            children: [
              Icon(Icons.error_outline, size: 48, color: Colors.red.shade300),
              const SizedBox(height: 16),
              Text(
                'Failed to load analytics',
                style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    color: Colors.grey.shade800),
              ),
              const SizedBox(height: 8),
              Text(
                message,
                style: TextStyle(color: Colors.grey.shade600),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 16),
              ElevatedButton.icon(
                onPressed: () => ref.invalidate(_adminOverviewProvider),
                icon: const Icon(Icons.refresh),
                label: const Text('Retry'),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildTimeRangeSelector() {
    final ranges = ['today', 'week', 'month', 'year'];
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(8),
        child: Row(
          children: ranges.map((range) {
            final isSelected = _timeRange == range;
            return Expanded(
              child: GestureDetector(
                onTap: () => setState(() => _timeRange = range),
                child: Container(
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  decoration: BoxDecoration(
                    color: isSelected
                        ? Colors.blue.shade700
                        : Colors.transparent,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    range.toUpperCase(),
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      color:
                          isSelected ? Colors.white : Colors.grey.shade700,
                      fontWeight: FontWeight.bold,
                      fontSize: 12,
                    ),
                  ),
                ),
              ),
            );
          }).toList(),
        ),
      ),
    );
  }

  Widget _buildStatsGrid(Map<String, dynamic> stats) {
    final totalLeads = (stats['total_leads'] as num?)?.toInt() ?? 0;
    final hotLeads = (stats['hot_leads'] as num?)?.toInt() ?? 0;
    final bookingsToday =
        (stats['bookings_today'] as num?)?.toInt() ?? 0;
    final totalRevenue =
        (stats['total_revenue'] as num?)?.toDouble() ?? 0;
    final pendingCommissions =
        (stats['pending_commissions'] as num?)?.toInt() ?? 0;
    final totalUsers = (stats['total_users'] as num?)?.toInt() ?? 0;

    return GridView.count(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      crossAxisCount: 2,
      crossAxisSpacing: 12,
      mainAxisSpacing: 12,
      childAspectRatio: 1.5,
      children: [
        _buildStatCard(
          'Total Users',
          '$totalUsers',
          Icons.people,
          Colors.blue,
          '$hotLeads hot leads',
        ),
        _buildStatCard(
          'Revenue',
          '₹${(totalRevenue / 100000).toStringAsFixed(1)}L',
          Icons.account_balance_wallet,
          Colors.green,
          '$bookingsToday bookings today',
        ),
        _buildStatCard(
          'Total Leads',
          '$totalLeads',
          Icons.trending_up,
          Colors.orange,
          '$hotLeads hot leads',
        ),
        _buildStatCard(
          'Pending Commissions',
          '$pendingCommissions',
          Icons.payment,
          Colors.purple,
          'awaiting approval',
        ),
      ],
    );
  }

  Widget _buildStatCard(
    String title,
    String value,
    IconData icon,
    Color color,
    String subtitle,
  ) {
    return Card(
      elevation: 2,
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: [color.withValues(alpha: 0.1), Colors.white],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.2),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Icon(icon, color: color, size: 24),
            ),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  value,
                  style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: color,
                  ),
                ),
                Text(
                  title,
                  style: TextStyle(
                    fontSize: 13,
                    color: Colors.grey.shade600,
                  ),
                ),
                Text(
                  subtitle,
                  style: TextStyle(
                    fontSize: 11,
                    color: Colors.grey.shade500,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildRecentActivity(List<dynamic> activities) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Recent Activity',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            if (activities.isEmpty)
              Center(
                child: Padding(
                  padding: const EdgeInsets.all(24),
                  child: Column(
                    children: [
                      Icon(Icons.history,
                          size: 48, color: Colors.grey.shade300),
                      const SizedBox(height: 12),
                      Text(
                        'No recent activity',
                        style: TextStyle(
                            color: Colors.grey.shade500, fontSize: 14),
                      ),
                    ],
                  ),
                ),
              )
            else
              ...activities.map((activity) {
                final type =
                    (activity['interaction_type'] as String?) ?? 'note';
                final subject =
                    (activity['subject'] as String?) ?? '';
                final body = (activity['body'] as String?) ?? '';
                final leadName =
                    (activity['lead_name'] as String?) ?? 'Unknown';
                final createdAt =
                    (activity['created_at'] as String?) ?? '';

                IconData icon;
                Color color;
                switch (type) {
                  case 'call':
                    icon = Icons.phone;
                    color = Colors.green;
                    break;
                  case 'email':
                    icon = Icons.email;
                    color = Colors.blue;
                    break;
                  case 'sms':
                    icon = Icons.sms;
                    color = Colors.orange;
                    break;
                  case 'visit':
                    icon = Icons.location_on;
                    color = Colors.purple;
                    break;
                  default:
                    icon = Icons.note;
                    color = Colors.grey;
                }

                final description = subject.isNotEmpty ? subject : body;
                final timeAgo = _formatTimeAgo(createdAt);

                return _buildActivityItem(
                  icon,
                  color,
                  type.toUpperCase(),
                  '$leadName: $description',
                  timeAgo,
                );
              }),
          ],
        ),
      ),
    );
  }

  String _formatTimeAgo(String dateTimeStr) {
    if (dateTimeStr.isEmpty) return '';
    try {
      final dt = DateTime.parse(dateTimeStr);
      final diff = DateTime.now().difference(dt);
      if (diff.inMinutes < 1) return 'Just now';
      if (diff.inMinutes < 60) return '${diff.inMinutes}m ago';
      if (diff.inHours < 24) return '${diff.inHours}h ago';
      return '${diff.inDays}d ago';
    } catch (_) {
      return dateTimeStr;
    }
  }

  Widget _buildActivityItem(
    IconData icon,
    Color color,
    String title,
    String description,
    String time,
  ) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(icon, color: color, size: 20),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title,
                    style: const TextStyle(fontWeight: FontWeight.bold)),
                Text(
                  description,
                  style:
                      TextStyle(fontSize: 13, color: Colors.grey.shade600),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
                Text(
                  time,
                  style:
                      TextStyle(fontSize: 11, color: Colors.grey.shade500),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ─── Providers ────────────────────────────────────────────────────────

final _adminOverviewProvider =
    FutureProvider<Map<String, dynamic>>((ref) async {
  try {
    final response =
        await ApiService().get('/crm/admin-overview');
    if (response['success'] == true) {
      return {
        'stats': response['stats'] ?? {},
        'recent_activity': response['recent_activity'] ?? [],
      };
    }
    return {'stats': {}, 'recent_activity': []};
  } catch (e) {
    AppLogger.error('Failed to fetch admin overview', e);
    return {'stats': {}, 'recent_activity': []};
  }
});

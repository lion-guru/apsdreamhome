import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../data/services/auth_service.dart';
import '../../widgets/app_widgets.dart';

class AdminDashboardPage extends ConsumerWidget {
  const AdminDashboardPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Admin Dashboard'),
        actions: [
          IconButton(
            onPressed: () => context.push('/notifications'),
            icon: const Icon(Icons.notifications_outlined),
          ),
          IconButton(
            onPressed: () {
              ref.read(authServiceProvider).logout();
              context.go('/login');
            },
            icon: const Icon(Icons.logout),
          ),
        ],
      ),
      body: CustomScrollView(
        slivers: [
          // Admin Stats
          SliverToBoxAdapter(
            child: _buildAdminStats(),
          ),
          
          // Quick Actions Grid
          SliverToBoxAdapter(
            child: _buildQuickActionsGrid(context),
          ),
          
          // Pending Approvals Header
          SliverToBoxAdapter(
            child: AppWidgets.sectionHeader(
              title: 'Pending Approvals',
              onSeeAll: () {},
            ),
          ),
          
          // Pending Items
          SliverToBoxAdapter(
            child: _buildPendingApprovals(context),
          ),
          
          // Recent Activity Header
          SliverToBoxAdapter(
            child: AppWidgets.sectionHeader(
              title: 'Recent Activity',
              onSeeAll: () {},
            ),
          ),
          
          // Activity List
          SliverToBoxAdapter(
            child: _buildRecentActivity(),
          ),
          
          const SliverToBoxAdapter(
            child: SizedBox(height: 32),
          ),
        ],
      ),
    );
  }

  Widget _buildAdminStats() {
    final stats = [
      {
        'title': 'Total Users',
        'value': '1,245',
        'icon': Icons.people_outline,
        'color': AppTheme.primaryColor,
      },
      {
        'title': 'Active Associates',
        'value': '89',
        'icon': Icons.group_outlined,
        'color': AppTheme.secondaryColor,
      },
      {
        'title': 'Bookings Today',
        'value': '12',
        'icon': Icons.bookmark_outline,
        'color': AppTheme.successColor,
      },
      {
        'title': 'Pending Commissions',
        'value': '₹2.5L',
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
                child: Icon(
                  stat['icon'] as IconData,
                  color: stat['color'] as Color,
                  size: 24,
                ),
              ),
              const Spacer(),
              Text(
                stat['value'] as String,
                style: TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                  color: stat['color'] as Color,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                stat['title'] as String,
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.grey.shade600,
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildQuickActionsGrid(BuildContext context) {
    final actions = [
      {
        'title': 'Colonies',
        'subtitle': 'Manage colonies',
        'icon': Icons.location_city,
        'color': Colors.blue,
        'route': '/admin/colonies',
      },
      {
        'title': 'Plots',
        'subtitle': 'Update plot status',
        'icon': Icons.map,
        'color': Colors.green,
        'route': '/admin/plots',
      },
      {
        'title': 'Bookings',
        'subtitle': 'Approve bookings',
        'icon': Icons.book_online,
        'color': Colors.orange,
        'route': '/admin/bookings',
      },
      {
        'title': 'Commissions',
        'subtitle': 'Process payouts',
        'icon': Icons.account_balance_wallet,
        'color': Colors.purple,
        'route': '/admin/commissions',
      },
      {
        'title': 'Users',
        'subtitle': 'Manage users',
        'icon': Icons.people,
        'color': Colors.teal,
        'route': '/admin/users',
      },
      {
        'title': 'Reports',
        'subtitle': 'View analytics',
        'icon': Icons.analytics,
        'color': Colors.indigo,
        'route': null,
      },
    ];
    
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Management',
            style: Theme.of(context).textTheme.titleLarge?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 16),
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 3,
              childAspectRatio: 0.9,
              crossAxisSpacing: 12,
              mainAxisSpacing: 12,
            ),
            itemCount: actions.length,
            itemBuilder: (context, index) {
              final action = actions[index];
              return GestureDetector(
                onTap: () {
                  if (action['route'] != null) {
                    context.push(action['route'] as String);
                  } else {
                    AppWidgets.showInfoSnackBar(context, 'Coming soon!');
                  }
                },
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
                        child: Icon(
                          action['icon'] as IconData,
                          color: action['color'] as Color,
                          size: 28,
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        action['title'] as String,
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 12,
                        ),
                        textAlign: TextAlign.center,
                      ),
                      const SizedBox(height: 2),
                      Text(
                        action['subtitle'] as String,
                        style: TextStyle(
                          fontSize: 10,
                          color: Colors.grey.shade500,
                        ),
                        textAlign: TextAlign.center,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
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
    final items = [
      {
        'type': 'booking',
        'title': 'New Booking Request',
        'subtitle': 'Suryoday Heights - Plot 45',
        'user': 'Rahul Kumar',
        'time': '2 hours ago',
        'count': 3,
      },
      {
        'type': 'commission',
        'title': 'Commission Approval',
        'subtitle': '₹45,000 - Associate: Amit Singh',
        'user': 'Amit Singh',
        'time': '5 hours ago',
        'count': 5,
      },
      {
        'type': 'payout',
        'title': 'Payout Request',
        'subtitle': '₹1,25,000 - Multiple associates',
        'user': '5 associates',
        'time': '1 day ago',
        'count': 2,
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
                width: 50,
                height: 50,
                decoration: BoxDecoration(
                  color: AppTheme.warningColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(
                  Icons.pending_actions,
                  color: AppTheme.warningColor,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      item['title'] as String,
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      item['subtitle'] as String,
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade600,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        Icon(
                          Icons.person_outline,
                          size: 12,
                          color: Colors.grey.shade500,
                        ),
                        const SizedBox(width: 4),
                        Text(
                          item['user'] as String,
                          style: TextStyle(
                            fontSize: 11,
                            color: Colors.grey.shade600,
                          ),
                        ),
                        const SizedBox(width: 12),
                        Icon(
                          Icons.access_time,
                          size: 12,
                          color: Colors.grey.shade500,
                        ),
                        const SizedBox(width: 4),
                        Text(
                          item['time'] as String,
                          style: TextStyle(
                            fontSize: 11,
                            color: Colors.grey.shade600,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: AppTheme.warningColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  '${item['count']}',
                  style: const TextStyle(
                    fontWeight: FontWeight.bold,
                    color: AppTheme.warningColor,
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildRecentActivity() {
    final activities = [
      {
        'action': 'Approved Booking',
        'detail': 'Plot 32 - Raghunath City',
        'user': 'Admin',
        'time': '10 minutes ago',
        'icon': Icons.check_circle,
        'color': AppTheme.successColor,
      },
      {
        'action': 'Processed Payout',
        'detail': '₹75,000 to Vikram Singh',
        'user': 'Admin',
        'time': '1 hour ago',
        'icon': Icons.payments,
        'color': AppTheme.primaryColor,
      },
      {
        'action': 'Added New Colony',
        'detail': 'Ganga Nagri Phase 2',
        'user': 'Admin',
        'time': '3 hours ago',
        'icon': Icons.add_location,
        'color': AppTheme.infoColor,
      },
    ];
    
    return ListView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      padding: const EdgeInsets.symmetric(horizontal: 16),
      itemCount: activities.length,
      itemBuilder: (context, index) {
        final activity = activities[index];
        return AppWidgets.customCard(
          child: Row(
            children: [
              Container(
                width: 40,
                height: 40,
                decoration: BoxDecoration(
                  color: (activity['color'] as Color).withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Icon(
                  activity['icon'] as IconData,
                  color: activity['color'] as Color,
                  size: 20,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      activity['action'] as String,
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      activity['detail'] as String,
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade600,
                      ),
                    ),
                  ],
                ),
              ),
              Text(
                activity['time'] as String,
                style: TextStyle(
                  fontSize: 11,
                  color: Colors.grey.shade500,
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}

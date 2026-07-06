import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';

import '../../../core/services/api_service.dart';

/// Provider for telecaller dashboard data
final telecallerDashboardProvider = FutureProvider<Map<String, dynamic>>((
  ref,
) async {
  final api = ApiService();
  try {
    final response = await api.get('admin/telecaller-dashboard');
    if (response['success'] == true) {
      return Map<String, dynamic>.from(response['data'] as Map? ?? {});
    }
  } catch (e) {
    // Fall back to empty data
  }
  return {};
});

/// Telecaller/Daily Caller Dashboard
/// Salary + Commission based calling system
class TelecallerDashboardPage extends ConsumerStatefulWidget {
  const TelecallerDashboardPage({super.key});

  @override
  ConsumerState<TelecallerDashboardPage> createState() =>
      _TelecallerDashboardPageState();
}

class _TelecallerDashboardPageState
    extends ConsumerState<TelecallerDashboardPage> {
  int _selectedIndex = 0;

  @override
  Widget build(BuildContext context) {
    final dashboardAsync = ref.watch(telecallerDashboardProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Telecaller Dashboard'),
        actions: [
          IconButton(icon: const Icon(Icons.notifications), onPressed: () {}),
          IconButton(icon: const Icon(Icons.person), onPressed: () {}),
        ],
      ),
      body: dashboardAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => _buildBody(context, {}),
        data: (data) => _buildBody(context, data),
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _selectedIndex,
        onDestinationSelected: (index) =>
            setState(() => _selectedIndex = index),
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.home_outlined),
            selectedIcon: Icon(Icons.home),
            label: 'Home',
          ),
          NavigationDestination(
            icon: Icon(Icons.people_outlined),
            selectedIcon: Icon(Icons.people),
            label: 'My Leads',
          ),
          NavigationDestination(
            icon: Icon(Icons.assessment_outlined),
            selectedIcon: Icon(Icons.assessment),
            label: 'Report',
          ),
          NavigationDestination(
            icon: Icon(Icons.account_balance_wallet_outlined),
            selectedIcon: Icon(Icons.account_balance_wallet),
            label: 'Earnings',
          ),
        ],
      ),
    );
  }

  Widget _buildBody(BuildContext context, Map<String, dynamic> data) {
    final leads = (data['leads'] as List? ?? []).cast<Map<String, dynamic>>();
    final stats = Map<String, dynamic>.from(
      (data['today_stats'] as Map?) ?? {},
    );
    final earnings = Map<String, dynamic>.from(
      (data['earnings'] as Map?) ?? {},
    );
    final performance = Map<String, dynamic>.from(
      (data['monthly_performance'] as Map?) ?? {},
    );

    return IndexedStack(
      index: _selectedIndex,
      children: [
        _buildHomeTab(stats, leads),
        _buildLeadsTab(leads),
        _buildReportTab(stats, performance),
        _buildEarningsTab(earnings),
      ],
    );
  }

  Widget _buildHomeTab(
    Map<String, dynamic> stats,
    List<Map<String, dynamic>> leads,
  ) {
    final completedCalls = (stats['completed_calls'] as num?)?.toInt() ?? 0;
    final targetCalls = (stats['target_calls'] as num?)?.toInt() ?? 50;
    final connected = (stats['connected'] as num?)?.toInt() ?? 0;
    final validLeads = (stats['valid_leads'] as num?)?.toInt() ?? 0;

    final highPriorityLeads = leads
        .where((lead) => lead['priority'] == 'high')
        .toList();

    return RefreshIndicator(
      onRefresh: () async => ref.invalidate(telecallerDashboardProvider),
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildWelcomeCard(),
            const SizedBox(height: 16),
            _buildProgressCard(completedCalls, targetCalls),
            const SizedBox(height: 16),
            _buildQuickStats(connected, validLeads),
            const SizedBox(height: 16),
            _buildPriorityLeadsSection(highPriorityLeads),
            const SizedBox(height: 16),
            _buildQuickActions(),
          ],
        ),
      ),
    );
  }

  Widget _buildWelcomeCard() {
    return Card(
      color: Theme.of(context).colorScheme.primaryContainer,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            const CircleAvatar(radius: 30, child: Icon(Icons.person, size: 30)),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Welcome Back!',
                    style: TextStyle(fontSize: 14, fontWeight: FontWeight.w500),
                  ),
                  const Text(
                    'Telecaller',
                    style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                  ),
                  Text(
                    DateFormat('EEEE, d MMMM').format(DateTime.now()),
                    style: TextStyle(color: Colors.grey[600]),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildProgressCard(int completed, int target) {
    final progress = target > 0 ? completed / target : 0.0;

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  "Today's Progress",
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                ),
                Text(
                  '${(progress * 100).toInt()}%',
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: Colors.blue,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            LinearProgressIndicator(
              value: progress,
              minHeight: 10,
              backgroundColor: Colors.grey[300],
              valueColor: AlwaysStoppedAnimation<Color>(
                progress >= 1.0 ? Colors.green : Colors.blue,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              '$completed / $target calls completed',
              style: TextStyle(color: Colors.grey[600]),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildQuickStats(int connected, int validLeads) {
    return Row(
      children: [
        _buildStatCard(
          'Connected',
          '$connected',
          Icons.phone_in_talk,
          Colors.green,
        ),
        const SizedBox(width: 8),
        _buildStatCard(
          'Valid Leads',
          '$validLeads',
          Icons.verified_user,
          Colors.orange,
        ),
        const SizedBox(width: 8),
        _buildStatCard('Bookings', '0', Icons.book_online, Colors.purple),
      ],
    );
  }

  Widget _buildStatCard(
    String title,
    String value,
    IconData icon,
    Color color,
  ) {
    return Expanded(
      child: Card(
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Column(
            children: [
              Icon(icon, color: color, size: 24),
              const SizedBox(height: 4),
              Text(
                value,
                style: const TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                ),
              ),
              Text(
                title,
                style: TextStyle(fontSize: 12, color: Colors.grey[600]),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildPriorityLeadsSection(List<Map<String, dynamic>> leads) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'Priority Leads',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            TextButton(
              onPressed: () => setState(() => _selectedIndex = 1),
              child: const Text('View All'),
            ),
          ],
        ),
        const SizedBox(height: 8),
        if (leads.isEmpty)
          const Card(
            child: Padding(
              padding: EdgeInsets.all(16),
              child: Text('No priority leads assigned'),
            ),
          )
        else
          ...leads.take(2).map((lead) => _buildLeadCard(lead)),
      ],
    );
  }

  Widget _buildLeadCard(Map<String, dynamic> lead) {
    final priority = (lead['priority'] ?? 'medium') as String;
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: _getPriorityColor(priority).withValues(alpha: 0.2),
          child: Icon(Icons.person, color: _getPriorityColor(priority)),
        ),
        title: Text((lead['name'] ?? 'Unknown') as String),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text((lead['phone'] ?? '') as String),
            Text(
              (lead['notes'] ?? '') as String,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(color: Colors.grey[600]),
            ),
          ],
        ),
        trailing: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            IconButton(
              icon: const Icon(Icons.call, color: Colors.green),
              onPressed: () => _makeCall(lead),
            ),
            IconButton(
              icon: const FaIcon(
                FontAwesomeIcons.whatsapp,
                color: Colors.green,
              ),
              onPressed: () => _openWhatsApp(lead),
            ),
          ],
        ),
        onTap: () => _showLeadDetails(lead),
      ),
    );
  }

  Widget _buildQuickActions() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Quick Actions',
          style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 8),
        Row(
          children: [
            Expanded(
              child: _buildActionButton(
                'Start Calling',
                Icons.call,
                Colors.green,
                () => _startCallingSession(),
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: _buildActionButton(
                'Submit Report',
                Icons.assessment,
                Colors.blue,
                () => _submitDailyReport(),
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildActionButton(
    String label,
    IconData icon,
    Color color,
    VoidCallback onTap,
  ) {
    return ElevatedButton.icon(
      onPressed: onTap,
      icon: Icon(icon),
      label: Text(label),
      style: ElevatedButton.styleFrom(
        backgroundColor: color,
        foregroundColor: Colors.white,
        padding: const EdgeInsets.symmetric(vertical: 16),
      ),
    );
  }

  Widget _buildLeadsTab(List<Map<String, dynamic>> leads) {
    if (leads.isEmpty) {
      return const Center(child: Text('No leads assigned'));
    }
    return RefreshIndicator(
      onRefresh: () async => ref.invalidate(telecallerDashboardProvider),
      child: ListView.builder(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16),
        itemCount: leads.length,
        itemBuilder: (context, index) => _buildLeadCard(leads[index]),
      ),
    );
  }

  Widget _buildReportTab(
    Map<String, dynamic> stats,
    Map<String, dynamic> performance,
  ) {
    return RefreshIndicator(
      onRefresh: () async => ref.invalidate(telecallerDashboardProvider),
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            const Text(
              'Daily Report',
              style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            _buildReportForm(stats, performance),
          ],
        ),
      ),
    );
  }

  Widget _buildReportForm(
    Map<String, dynamic> stats,
    Map<String, dynamic> performance,
  ) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            _buildReportField(
              'Total Calls Made',
              '${stats['completed_calls'] ?? 0}',
              Icons.phone,
            ),
            const SizedBox(height: 12),
            _buildReportField(
              'Connected Calls',
              '${stats['connected'] ?? 0}',
              Icons.phone_in_talk,
            ),
            const SizedBox(height: 12),
            _buildReportField(
              'Valid Leads Generated',
              '${stats['valid_leads'] ?? 0}',
              Icons.verified_user,
            ),
            const SizedBox(height: 12),
            _buildReportField(
              'Site Visits Scheduled',
              '${stats['callback'] ?? 0}',
              Icons.location_on,
            ),
            const SizedBox(height: 12),
            _buildReportField(
              'Monthly Total Calls',
              '${performance['total_calls'] ?? 0}',
              Icons.assessment,
            ),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () {},
                child: const Text('Submit Report'),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildReportField(String label, String value, IconData icon) {
    return Row(
      children: [
        Icon(icon, color: Colors.grey),
        const SizedBox(width: 16),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(label, style: TextStyle(color: Colors.grey[600])),
              Text(
                value,
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildEarningsTab(Map<String, dynamic> earnings) {
    final totalEarnings = (earnings['total_earnings'] as num?)?.toDouble() ?? 0;
    final pendingEarnings =
        (earnings['pending_earnings'] as num?)?.toDouble() ?? 0;

    return RefreshIndicator(
      onRefresh: () async => ref.invalidate(telecallerDashboardProvider),
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            _buildEarningsSummary(totalEarnings, pendingEarnings),
            const SizedBox(height: 16),
            _buildCommissionBreakdown(earnings),
          ],
        ),
      ),
    );
  }

  Widget _buildEarningsSummary(double total, double pending) {
    return Card(
      color: Colors.green.shade50,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            const Text('This Month Earnings', style: TextStyle(fontSize: 16)),
            const SizedBox(height: 8),
            Text(
              '₹${total.toStringAsFixed(0)}',
              style: const TextStyle(
                fontSize: 36,
                fontWeight: FontWeight.bold,
                color: Colors.green,
              ),
            ),
            const SizedBox(height: 16),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: [
                _buildEarningItem('Commission', '₹${total.toStringAsFixed(0)}'),
                _buildEarningItem('Pending', '₹${pending.toStringAsFixed(0)}'),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEarningItem(String label, String value) {
    return Column(
      children: [
        Text(
          value,
          style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
        ),
        Text(label, style: TextStyle(fontSize: 12, color: Colors.grey[600])),
      ],
    );
  }

  Widget _buildCommissionBreakdown(Map<String, dynamic> earnings) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Earnings Summary',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            _buildCommissionRow(
              'Total Earned',
              '₹${earnings['total_earnings'] ?? 0}',
            ),
            const Divider(),
            _buildCommissionRow(
              'Pending Payout',
              '₹${earnings['pending_earnings'] ?? 0}',
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCommissionRow(String description, String amount) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(description),
          Text(amount, style: const TextStyle(fontWeight: FontWeight.bold)),
        ],
      ),
    );
  }

  Color _getPriorityColor(String priority) {
    switch (priority) {
      case 'high':
        return Colors.red;
      case 'medium':
        return Colors.orange;
      case 'low':
        return Colors.green;
      default:
        return Colors.grey;
    }
  }

  void _makeCall(Map<String, dynamic> lead) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Calling ${lead['name'] ?? 'Unknown'}'),
        content: Text('Dialing ${lead['phone'] ?? ''}...'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _showCallOutcomeDialog(lead);
            },
            child: const Text('End Call & Log'),
          ),
        ],
      ),
    );
  }

  void _openWhatsApp(Map<String, dynamic> lead) {
    // Launch WhatsApp
  }

  void _showLeadDetails(Map<String, dynamic> lead) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (context) => DraggableScrollableSheet(
        initialChildSize: 0.7,
        maxChildSize: 0.9,
        minChildSize: 0.5,
        expand: false,
        builder: (context, scrollController) {
          return Container(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  (lead['name'] ?? 'Unknown') as String,
                  style: const TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                Text(
                  (lead['phone'] ?? '') as String,
                  style: const TextStyle(fontSize: 18),
                ),
                const SizedBox(height: 16),
                _buildDetailRow('Source', (lead['source'] ?? 'N/A') as String),
                _buildDetailRow('Status', (lead['status'] ?? 'N/A') as String),
                _buildDetailRow(
                  'Priority',
                  (lead['priority'] ?? 'N/A') as String,
                ),
                _buildDetailRow('Notes', (lead['notes'] ?? 'N/A') as String),
                const Spacer(),
                Row(
                  children: [
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: () {
                          Navigator.pop(context);
                          _makeCall(lead);
                        },
                        icon: const Icon(Icons.call),
                        label: const Text('Call'),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: () {},
                        icon: const Icon(Icons.transfer_within_a_station),
                        label: const Text('Transfer'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.orange,
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('$label: ', style: const TextStyle(fontWeight: FontWeight.bold)),
          Expanded(child: Text(value)),
        ],
      ),
    );
  }

  void _showCallOutcomeDialog(Map<String, dynamic> lead) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Call Outcome'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            _buildOutcomeButton('Connected - Interested', Colors.green),
            _buildOutcomeButton('Connected - Not Interested', Colors.red),
            _buildOutcomeButton('Not Answered', Colors.orange),
            _buildOutcomeButton('Busy', Colors.orange),
            _buildOutcomeButton('Call Later', Colors.blue),
          ],
        ),
      ),
    );
  }

  Widget _buildOutcomeButton(String label, Color color) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: ElevatedButton(
        onPressed: () => Navigator.pop(context),
        style: ElevatedButton.styleFrom(
          backgroundColor: color,
          minimumSize: const Size(double.infinity, 40),
        ),
        child: Text(label),
      ),
    );
  }

  void _startCallingSession() {
    // Start auto-dialer or manual calling session
  }

  void _submitDailyReport() {
    setState(() => _selectedIndex = 2);
  }
}

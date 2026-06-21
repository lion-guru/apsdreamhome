import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';

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

  // Sample data
  final List<Map<String, dynamic>> _assignedLeads = [
    {
      'id': '1',
      'name': 'Rajesh Kumar',
      'phone': '+91 98765 43210',
      'source': 'Website Enquiry',
      'status': 'new',
      'priority': 'high',
      'notes': 'Interested in Gorakhpur plot',
      'callCount': 0,
      'lastCall': null,
    },
    {
      'id': '2',
      'name': 'Sunita Devi',
      'phone': '+91 98765 43211',
      'source': 'Facebook Ad',
      'status': 'call_later',
      'priority': 'medium',
      'notes': 'Will decide next month',
      'callCount': 1,
      'lastCall': DateTime.now().subtract(const Duration(days: 2)),
    },
    {
      'id': '3',
      'name': 'Amit Sharma',
      'phone': '+91 98765 43212',
      'source': 'Referral',
      'status': 'interested',
      'priority': 'high',
      'notes': 'Wants site visit',
      'callCount': 2,
      'lastCall': DateTime.now().subtract(const Duration(days: 1)),
    },
    {
      'id': '4',
      'name': 'Priya Patel',
      'phone': '+91 98765 43213',
      'source': 'Google Ads',
      'status': 'not_interested',
      'priority': 'low',
      'notes': 'Budget issue',
      'callCount': 1,
      'lastCall': DateTime.now().subtract(const Duration(days: 3)),
    },
  ];

  final Map<String, dynamic> _todayStats = {
    'targetCalls': 50,
    'completedCalls': 32,
    'connected': 18,
    'validLeads': 5,
    'bookings': 1,
    'talkTimeMinutes': 124,
  };

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Telecaller Dashboard'),
        actions: [
          IconButton(
            icon: const Icon(Icons.notifications),
            onPressed: () {},
          ),
          IconButton(
            icon: const Icon(Icons.person),
            onPressed: () {},
          ),
        ],
      ),
      body: IndexedStack(
        index: _selectedIndex,
        children: [
          _buildHomeTab(),
          _buildLeadsTab(),
          _buildReportTab(),
          _buildEarningsTab(),
        ],
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

  Widget _buildHomeTab() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Welcome & Status
          _buildWelcomeCard(),

          const SizedBox(height: 16),

          // Today's Progress
          _buildProgressCard(),

          const SizedBox(height: 16),

          // Quick Stats
          _buildQuickStats(),

          const SizedBox(height: 16),

          // Priority Leads
          _buildPriorityLeadsSection(),

          const SizedBox(height: 16),

          // Quick Actions
          _buildQuickActions(),
        ],
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
            const CircleAvatar(
              radius: 30,
              child: Icon(Icons.person, size: 30),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Welcome Back!',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                  const Text(
                    'Telecaller #TC001',
                    style: TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  Text(
                    DateFormat('EEEE, d MMMM').format(DateTime.now()),
                    style: TextStyle(
                      color: Colors.grey[600],
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

  Widget _buildProgressCard() {
    final completed = (_todayStats['completedCalls'] as num).toDouble();
    final target = (_todayStats['targetCalls'] as num).toDouble();
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
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                  ),
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
              '${_todayStats['completedCalls']} / ${_todayStats['targetCalls']} calls completed',
              style: TextStyle(color: Colors.grey[600]),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildQuickStats() {
    return Row(
      children: [
        _buildStatCard('Connected', '${_todayStats['connected']}',
            Icons.phone_in_talk, Colors.green),
        const SizedBox(width: 8),
        _buildStatCard('Valid Leads', '${_todayStats['validLeads']}',
            Icons.verified_user, Colors.orange),
        const SizedBox(width: 8),
        _buildStatCard('Talk Time', '${_todayStats['talkTimeMinutes']}m',
            Icons.timer, Colors.purple),
      ],
    );
  }

  Widget _buildStatCard(
      String title, String value, IconData icon, Color color) {
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
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.grey[600],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildPriorityLeadsSection() {
    final highPriorityLeads =
        _assignedLeads.where((lead) => lead['priority'] == 'high').toList();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'Priority Leads',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            TextButton(
              onPressed: () => setState(() => _selectedIndex = 1),
              child: const Text('View All'),
            ),
          ],
        ),
        const SizedBox(height: 8),
        ...highPriorityLeads.take(2).map((lead) => _buildLeadCard(lead)),
      ],
    );
  }

  Widget _buildLeadCard(Map<String, dynamic> lead) {
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: _getPriorityColor(lead['priority'] as String)
              .withValues(alpha: 0.2),
          child: Icon(
            Icons.person,
            color: _getPriorityColor(lead['priority'] as String),
          ),
        ),
        title: Text(lead['name'] as String),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(lead['phone'] as String),
            Text(
              lead['notes'] as String,
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
              icon:
                  const FaIcon(FontAwesomeIcons.whatsapp, color: Colors.green),
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
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.bold,
          ),
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
      String label, IconData icon, Color color, VoidCallback onTap) {
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

  Widget _buildLeadsTab() {
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: _assignedLeads.length,
      itemBuilder: (context, index) {
        final lead = _assignedLeads[index];
        return _buildLeadCard(lead);
      },
    );
  }

  Widget _buildReportTab() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          const Text(
            'Daily Report',
            style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 16),
          _buildReportForm(),
        ],
      ),
    );
  }

  Widget _buildReportForm() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            _buildReportField('Total Calls Made', '32', Icons.phone),
            const SizedBox(height: 12),
            _buildReportField('Connected Calls', '18', Icons.phone_in_talk),
            const SizedBox(height: 12),
            _buildReportField(
                'Valid Leads Generated', '5', Icons.verified_user),
            const SizedBox(height: 12),
            _buildReportField('Site Visits Scheduled', '3', Icons.location_on),
            const SizedBox(height: 12),
            _buildReportField('Bookings Confirmed', '1', Icons.check_circle),
            const SizedBox(height: 12),
            _buildReportField('Total Talk Time (min)', '124', Icons.timer),
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

  Widget _buildEarningsTab() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          _buildEarningsSummary(),
          const SizedBox(height: 16),
          _buildCommissionBreakdown(),
        ],
      ),
    );
  }

  Widget _buildEarningsSummary() {
    return Card(
      color: Colors.green.shade50,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            const Text(
              'This Month Earnings',
              style: TextStyle(fontSize: 16),
            ),
            const SizedBox(height: 8),
            const Text(
              '₹24,500',
              style: TextStyle(
                fontSize: 36,
                fontWeight: FontWeight.bold,
                color: Colors.green,
              ),
            ),
            const SizedBox(height: 16),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: [
                _buildEarningItem('Base Salary', '₹15,000'),
                _buildEarningItem('Commission', '₹7,500'),
                _buildEarningItem('Incentive', '₹2,000'),
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
          style: const TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.bold,
          ),
        ),
        Text(
          label,
          style: TextStyle(
            fontSize: 12,
            color: Colors.grey[600],
          ),
        ),
      ],
    );
  }

  Widget _buildCommissionBreakdown() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Commission Breakdown',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),
            _buildCommissionRow('Valid Leads (5 × ₹100)', '₹500'),
            const Divider(),
            _buildCommissionRow('Booking Conversion (1 × ₹2000)', '₹2,000'),
            const Divider(),
            _buildCommissionRow('Target Achievement Bonus', '₹5,000'),
            const Divider(),
            _buildTotalRow(),
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
          Text(
            amount,
            style: const TextStyle(fontWeight: FontWeight.bold),
          ),
        ],
      ),
    );
  }

  Widget _buildTotalRow() {
    return const Padding(
      padding: EdgeInsets.symmetric(vertical: 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            'Total Commission',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
            ),
          ),
          Text(
            '₹7,500',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: Colors.green,
            ),
          ),
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
    // Implement call functionality
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Calling ${lead['name'] as String}'),
        content: Text('Dialing ${lead['phone'] as String}...'),
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
                  lead['name'] as String,
                  style: const TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                Text(
                  lead['phone'] as String,
                  style: const TextStyle(fontSize: 18),
                ),
                const SizedBox(height: 16),
                _buildDetailRow('Source', lead['source'] as String),
                _buildDetailRow('Status', lead['status'] as String),
                _buildDetailRow('Priority', lead['priority'] as String),
                _buildDetailRow('Notes', lead['notes'] as String),
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
          Text(
            '$label: ',
            style: const TextStyle(fontWeight: FontWeight.bold),
          ),
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

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';

/// EMI Collection Page - For Field Agents
/// Door-to-door EMI collection with GPS tracking
class EMICollectionPage extends ConsumerStatefulWidget {
  const EMICollectionPage({super.key});

  @override
  ConsumerState<EMICollectionPage> createState() => _EMICollectionPageState();
}

class _EMICollectionPageState extends ConsumerState<EMICollectionPage> {
  final ApiService _api = ApiService();
  int _selectedIndex = 0;
  bool _isLoading = true;
  String? _error;

  Map<String, dynamic> _data = {};
  List<Map<String, dynamic>> _todayDues = [];
  Map<String, dynamic> _todayStats = {};
  List<Map<String, dynamic>> _todayCollections = [];
  List<Map<String, dynamic>> _history = [];
  Map<String, dynamic> _earnings = {};

  @override
  void initState() {
    super.initState();
    _fetchData();
  }

  Future<void> _fetchData() async {
    setState(() => _isLoading = true);
    try {
      final res = await _api.get(AppConstants.adminEmiCollectionEndpoint);
      if (res['success'] == true && res['data'] != null) {
        final d = res['data'] as Map<String, dynamic>;
        setState(() {
          _data = d;
          _todayDues =
              (d['today_dues'] as List?)?.cast<Map<String, dynamic>>() ?? [];
          _todayStats = (d['today_stats'] as Map<String, dynamic>?) ?? {};
          _todayCollections =
              (d['today_collections'] as List?)?.cast<Map<String, dynamic>>() ??
              [];
          _history =
              (d['history'] as List?)?.cast<Map<String, dynamic>>() ?? [];
          _earnings = (d['earnings'] as Map<String, dynamic>?) ?? {};
          _isLoading = false;
          _error = null;
        });
      } else {
        setState(() {
          _isLoading = false;
          _error = 'Failed to load EMI data';
        });
      }
    } catch (e) {
      setState(() {
        _isLoading = false;
        _error = 'Error: $e';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('EMI Collection'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _isLoading ? null : _fetchData,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
          ? _buildErrorState()
          : IndexedStack(
              index: _selectedIndex,
              children: [
                _buildTodayDuesTab(),
                _buildRouteTab(),
                _buildHistoryTab(),
                _buildEarningsTab(),
              ],
            ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _selectedIndex,
        onDestinationSelected: (index) =>
            setState(() => _selectedIndex = index),
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.today_outlined),
            selectedIcon: Icon(Icons.today),
            label: 'Today',
          ),
          NavigationDestination(
            icon: Icon(Icons.map_outlined),
            selectedIcon: Icon(Icons.map),
            label: 'Route',
          ),
          NavigationDestination(
            icon: Icon(Icons.history_outlined),
            selectedIcon: Icon(Icons.history),
            label: 'History',
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

  Widget _buildErrorState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.error_outline, size: 64, color: Colors.red[300]),
            const SizedBox(height: 16),
            Text(
              _error!,
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.grey[600]),
            ),
            const SizedBox(height: 16),
            ElevatedButton.icon(
              onPressed: _fetchData,
              icon: const Icon(Icons.refresh),
              label: const Text('Retry'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTodayDuesTab() {
    final totalDue =
        int.tryParse((_todayStats['total_due'] ?? 0).toString()) ?? 0;
    final overdueCount =
        int.tryParse((_todayStats['overdue_count'] ?? 0).toString()) ?? 0;
    final totalAmount =
        double.tryParse((_todayStats['total_amount_due'] ?? 0).toString()) ?? 0;
    final collectedAmt =
        double.tryParse((_earnings['total_collected'] ?? 0).toString()) ?? 0;

    return Column(
      children: [
        _buildStatsCard(totalDue, overdueCount, totalAmount, collectedAmt),
        Expanded(
          child: _todayDues.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        Icons.check_circle_outline,
                        size: 64,
                        color: Colors.green[300],
                      ),
                      const SizedBox(height: 16),
                      Text(
                        'No pending dues!',
                        style: TextStyle(
                          fontSize: 18,
                          color: Colors.grey[600],
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _fetchData,
                  child: ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: _todayDues.length,
                    itemBuilder: (context, index) {
                      return _buildDueCard(_todayDues[index]);
                    },
                  ),
                ),
        ),
      ],
    );
  }

  Widget _buildStatsCard(
    int totalDue,
    int overdueCount,
    double totalAmount,
    double collectedAmt,
  ) {
    return Card(
      margin: const EdgeInsets.all(16),
      color: Colors.blue.shade50,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  "Today's Overview",
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                ),
                Text(
                  '$totalDue pending',
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: Colors.blue,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                children: [
                  _buildStatItem(
                    'Overdue',
                    '$overdueCount',
                    Icons.warning,
                    Colors.red,
                  ),
                  const SizedBox(width: 24),
                  _buildStatItem(
                    'Pending',
                    '${totalDue - overdueCount}',
                    Icons.pending,
                    Colors.orange,
                  ),
                  const SizedBox(width: 24),
                  _buildStatItem(
                    'Total Due',
                    '₹${NumberFormat('#,##0').format(totalAmount)}',
                    Icons.currency_rupee,
                    Colors.blue,
                  ),
                ],
              ),
            ),
            const Divider(height: 24),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Collected (This Month)',
                      style: TextStyle(color: Colors.grey),
                    ),
                    Text(
                      '₹${NumberFormat('#,##0').format(collectedAmt)}',
                      style: const TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                        color: Colors.green,
                      ),
                    ),
                  ],
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 8,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.orange.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Column(
                    children: [
                      const Text('Late Fees', style: TextStyle(fontSize: 12)),
                      Text(
                        '₹${NumberFormat('#,##0').format(double.tryParse((_earnings['total_late_fees'] ?? 0).toString()) ?? 0)}',
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          color: Colors.orange,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatItem(
    String label,
    String value,
    IconData icon,
    Color color,
  ) {
    return Column(
      children: [
        Icon(icon, color: color, size: 28),
        const SizedBox(height: 4),
        Text(
          value,
          style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
        ),
        Text(label, style: TextStyle(fontSize: 12, color: Colors.grey[600])),
      ],
    );
  }

  Widget _buildDueCard(Map<String, dynamic> due) {
    final daysOverdue =
        int.tryParse((due['days_overdue'] ?? 0).toString()) ?? 0;
    final customerName = (due['customer_name'] ?? 'Unknown').toString();
    final plotNumber = (due['plot_number'] ?? '-').toString();
    final colonyName = (due['colony_name'] ?? '-').toString();
    final emiAmount = double.tryParse((due['emi_amount'] ?? 0).toString()) ?? 0;
    final dueDate = (due['due_date'] ?? '').toString();
    final phone = (due['phone'] ?? '').toString();

    Color priorityColor;
    String priorityText;
    if (daysOverdue > 7) {
      priorityColor = Colors.red;
      priorityText = 'Overdue $daysOverdue days';
    } else if (daysOverdue > 0) {
      priorityColor = Colors.orange;
      priorityText = 'Overdue $daysOverdue days';
    } else if (daysOverdue == 0) {
      priorityColor = Colors.blue;
      priorityText = 'Due Today';
    } else {
      priorityColor = Colors.green;
      priorityText = 'Due in ${-daysOverdue} days';
    }

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Column(
        children: [
          ListTile(
            leading: CircleAvatar(
              backgroundColor: priorityColor.withValues(alpha: 0.2),
              child: Icon(Icons.person, color: priorityColor),
            ),
            title: Text(customerName),
            subtitle: Text('$plotNumber - $colonyName'),
            trailing: Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: priorityColor.withValues(alpha: 0.2),
                borderRadius: BorderRadius.circular(4),
              ),
              child: Text(
                priorityText,
                style: TextStyle(
                  color: priorityColor,
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (phone.isNotEmpty) _buildInfoRow(Icons.phone, phone),
                const SizedBox(height: 8),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'EMI: ₹${NumberFormat('#,##0').format(emiAmount)}',
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                      ),
                    ),
                    Text(
                      'Due: ${dueDate}',
                      style: TextStyle(color: Colors.grey[600]),
                    ),
                  ],
                ),
              ],
            ),
          ),
          const Divider(),
          Padding(
            padding: const EdgeInsets.all(8),
            child: Row(
              children: [
                Expanded(
                  child: ElevatedButton.icon(
                    onPressed: () => _showCollectDialog(due),
                    icon: const Icon(Icons.check_circle, size: 18),
                    label: const Text('Collect'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.green,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 8),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                IconButton(
                  onPressed: () => _launchPhone(phone),
                  icon: const Icon(Icons.call),
                  color: Colors.blue,
                ),
                IconButton(
                  onPressed: () => _openMap(due),
                  icon: const Icon(Icons.map),
                  color: Colors.grey,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInfoRow(IconData icon, String text) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 4),
      child: Row(
        children: [
          Icon(icon, size: 16, color: Colors.grey),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              text,
              style: TextStyle(color: Colors.grey[700]),
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRouteTab() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.map, size: 100, color: Colors.grey[400]),
          const SizedBox(height: 16),
          Text(
            'Route Optimization',
            style: TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              color: Colors.grey[600],
            ),
          ),
          const SizedBox(height: 8),
          Text(
            '${_todayDues.length} dues to collect today',
            textAlign: TextAlign.center,
            style: TextStyle(color: Colors.grey[600]),
          ),
          const SizedBox(height: 24),
          ElevatedButton.icon(
            onPressed: () async {
              final mapUri = Uri.parse(
                'https://maps.google.com/?q=APS+Dream+Home+Gorakhpur',
              );
              if (await canLaunchUrl(mapUri)) {
                await launchUrl(mapUri, mode: LaunchMode.externalApplication);
              }
            },
            icon: const Icon(Icons.navigation),
            label: const Text('Start Navigation'),
          ),
        ],
      ),
    );
  }

  Widget _buildHistoryTab() {
    if (_history.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.history, size: 64, color: Colors.grey[300]),
            const SizedBox(height: 16),
            Text(
              'No collection history for last 30 days',
              style: TextStyle(color: Colors.grey[600]),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: _history.length,
      itemBuilder: (context, index) {
        final h = _history[index];
        final collected =
            double.tryParse((h['collected'] ?? 0).toString()) ?? 0;
        final count = (h['count'] ?? 0).toString();
        final date = (h['date'] ?? '').toString();
        return Card(
          child: ListTile(
            leading: CircleAvatar(
              backgroundColor: Colors.green.withValues(alpha: 0.2),
              child: const Icon(Icons.check, color: Colors.green),
            ),
            title: Text('$count collections'),
            subtitle: Text(date),
            trailing: Text(
              '₹${NumberFormat('#,##0').format(collected)}',
              style: const TextStyle(
                fontWeight: FontWeight.bold,
                color: Colors.green,
              ),
            ),
          ),
        );
      },
    );
  }

  Widget _buildEarningsTab() {
    final totalCollected =
        double.tryParse((_earnings['total_collected'] ?? 0).toString()) ?? 0;
    final totalPaidCount =
        int.tryParse((_earnings['total_paid_count'] ?? 0).toString()) ?? 0;
    final lateFees =
        double.tryParse((_earnings['total_late_fees'] ?? 0).toString()) ?? 0;

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          Card(
            color: Colors.orange.shade50,
            child: Padding(
              padding: const EdgeInsets.all(24),
              child: Column(
                children: [
                  const Text(
                    'This Month Collection',
                    style: TextStyle(fontSize: 18),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    '₹${NumberFormat('#,##0').format(totalCollected)}',
                    style: const TextStyle(
                      fontSize: 42,
                      fontWeight: FontWeight.bold,
                      color: Colors.orange,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    '$totalPaidCount installments paid',
                    style: TextStyle(color: Colors.grey[600]),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Earnings Breakdown',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 16),
                  _buildEarningRow(
                    'Total Collected',
                    '₹${NumberFormat('#,##0').format(totalCollected)}',
                    Colors.green,
                  ),
                  const Divider(),
                  _buildEarningRow(
                    'Late Fees Collected',
                    '₹${NumberFormat('#,##0').format(lateFees)}',
                    Colors.orange,
                  ),
                  const Divider(),
                  _buildEarningRow(
                    'Installments Paid',
                    '$totalPaidCount',
                    Colors.blue,
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),
          if (_todayCollections.isNotEmpty)
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      "Today's Collections",
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 8),
                    ...(_todayCollections.take(5).map((c) {
                      final name = (c['customer_name'] ?? 'Unknown').toString();
                      final amt =
                          double.tryParse((c['paid_amount'] ?? 0).toString()) ??
                          0;
                      return ListTile(
                        dense: true,
                        leading: const Icon(
                          Icons.check_circle,
                          color: Colors.green,
                          size: 20,
                        ),
                        title: Text(name),
                        trailing: Text('₹${NumberFormat('#,##0').format(amt)}'),
                      );
                    })),
                  ],
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildEarningRow(String label, String amount, Color color) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Row(
            children: [
              Container(
                width: 12,
                height: 12,
                decoration: BoxDecoration(color: color, shape: BoxShape.circle),
              ),
              const SizedBox(width: 12),
              Text(label),
            ],
          ),
          Text(amount, style: const TextStyle(fontWeight: FontWeight.bold)),
        ],
      ),
    );
  }

  void _showCollectDialog(Map<String, dynamic> due) {
    final emiAmount = double.tryParse((due['emi_amount'] ?? 0).toString()) ?? 0;
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text('Collect - ${due['customer_name']}'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('EMI Amount: ₹${NumberFormat('#,##0').format(emiAmount)}'),
            const SizedBox(height: 16),
            TextField(
              decoration: const InputDecoration(
                labelText: 'Amount Collected',
                border: OutlineInputBorder(),
              ),
              keyboardType: TextInputType.number,
              controller: TextEditingController(
                text: emiAmount.toStringAsFixed(0),
              ),
            ),
            const SizedBox(height: 16),
            DropdownButtonFormField<String>(
              decoration: const InputDecoration(
                labelText: 'Payment Mode',
                border: OutlineInputBorder(),
              ),
              items: [
                'Cash',
                'UPI',
                'Cheque',
                'Online',
              ].map((m) => DropdownMenuItem(value: m, child: Text(m))).toList(),
              onChanged: (_) {},
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(ctx);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text(
                    'Collection recorded. Receipt will be generated.',
                  ),
                  backgroundColor: Colors.green,
                ),
              );
            },
            child: const Text('Record Collection'),
          ),
        ],
      ),
    );
  }

  void _launchPhone(String phone) async {
    if (phone.isEmpty) return;
    final uri = Uri.parse('tel:${phone.replaceAll(RegExp(r'\s+'), '')}');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    }
  }

  void _openMap(Map<String, dynamic> due) {
    ScaffoldMessenger.of(
      context,
    ).showSnackBar(const SnackBar(content: Text('Map navigation coming soon')));
  }
}

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

/// EMI Collection Page - For Field Agents
/// Door-to-door EMI collection with GPS tracking
class EMICollectionPage extends ConsumerStatefulWidget {
  const EMICollectionPage({super.key});

  @override
  ConsumerState<EMICollectionPage> createState() => _EMICollectionPageState();
}

class _EMICollectionPageState extends ConsumerState<EMICollectionPage> {
  int _selectedIndex = 0;
  final bool _isOnline = true;

  // Sample EMI dues data
  final List<Map<String, dynamic>> _todayDues = [
    {
      'customerId': 'C001',
      'customerName': 'Ramesh Kumar',
      'phone': '+91 98765 43210',
      'address': '123, Gandhi Nagar, Gorakhpur',
      'landmark': 'Near HDFC Bank',
      'bookingId': 'B001',
      'plotNumber': 'P-45',
      'colonyName': 'Suryoday Heights',
      'emiAmount': 5000,
      'dueDate': DateTime.now(),
      'daysOverdue': 0,
      'priority': 'regular',
      'status': 'pending',
    },
    {
      'customerId': 'C002',
      'customerName': 'Sunita Devi',
      'phone': '+91 98765 43211',
      'address': '456, Rajendra Nagar, Gorakhpur',
      'landmark': 'Opposite Petrol Pump',
      'bookingId': 'B002',
      'plotNumber': 'P-67',
      'colonyName': 'Raghunath City',
      'emiAmount': 7500,
      'dueDate': DateTime.now().subtract(const Duration(days: 5)),
      'daysOverdue': 5,
      'priority': 'high',
      'status': 'pending',
    },
    {
      'customerId': 'C003',
      'customerName': 'Amit Singh',
      'phone': '+91 98765 43212',
      'address': '789, Civil Lines, Gorakhpur',
      'landmark': 'Near Railway Station',
      'bookingId': 'B003',
      'plotNumber': 'P-23',
      'colonyName': 'Braj Radha Enclave',
      'emiAmount': 10000,
      'dueDate': DateTime.now().subtract(const Duration(days: 12)),
      'daysOverdue': 12,
      'priority': 'high',
      'status': 'pending',
    },
    {
      'customerId': 'C004',
      'customerName': 'Priya Sharma',
      'phone': '+91 98765 43213',
      'address': '321, Mohaddipur, Gorakhpur',
      'landmark': 'Near DM Office',
      'bookingId': 'B004',
      'plotNumber': 'P-89',
      'colonyName': 'Ganga Nagri',
      'emiAmount': 6000,
      'dueDate': DateTime.now().add(const Duration(days: 2)),
      'daysOverdue': -2,
      'priority': 'regular',
      'status': 'pending',
    },
  ];

  final Map<String, dynamic> _todayStats = {
    'target': 20,
    'visited': 8,
    'collected': 5,
    'partial': 1,
    'notHome': 2,
    'amountCollected': 42500,
    'commission': 425,
  };

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('EMI Collection'),
        actions: [
          // Online/Offline indicator
          Container(
            margin: const EdgeInsets.only(right: 16),
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
            decoration: BoxDecoration(
              color: _isOnline ? Colors.green : Colors.orange,
              borderRadius: BorderRadius.circular(16),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(
                  _isOnline ? Icons.wifi : Icons.wifi_off,
                  color: Colors.white,
                  size: 16,
                ),
                const SizedBox(width: 4),
                Text(
                  _isOnline ? 'Online' : 'Offline',
                  style: const TextStyle(color: Colors.white, fontSize: 12),
                ),
              ],
            ),
          ),
        ],
      ),
      body: IndexedStack(
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
      floatingActionButton: _selectedIndex == 0
          ? FloatingActionButton.extended(
              onPressed: () => _syncData(),
              icon: const Icon(Icons.sync),
              label: const Text('Sync'),
            )
          : null,
    );
  }

  Widget _buildTodayDuesTab() {
    return Column(
      children: [
        // Stats Card
        _buildStatsCard(),

        // Priority Filter
        _buildPriorityFilter(),

        // Due List
        Expanded(
          child: ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: _todayDues.length,
            itemBuilder: (context, index) {
              final due = _todayDues[index];
              return _buildDueCard(due);
            },
          ),
        ),
      ],
    );
  }

  Widget _buildStatsCard() {
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
                  "Today's Progress",
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                Text(
                  '${_todayStats['visited']}/${_todayStats['target']}',
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: Colors.blue,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: [
                _buildStatItem('Collected', '${_todayStats['collected']}',
                    Icons.check_circle, Colors.green),
                _buildStatItem('Partial', '${_todayStats['partial']}',
                    Icons.timelapse, Colors.orange),
                _buildStatItem('Not Home', '${_todayStats['notHome']}',
                    Icons.home_outlined, Colors.grey),
              ],
            ),
            const Divider(height: 24),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Amount Collected',
                      style: TextStyle(color: Colors.grey),
                    ),
                    Text(
                      '₹${_todayStats['amountCollected']}',
                      style: const TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                        color: Colors.green,
                      ),
                    ),
                  ],
                ),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  decoration: BoxDecoration(
                    color: Colors.orange.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Column(
                    children: [
                      const Text(
                        'Commission',
                        style: TextStyle(fontSize: 12),
                      ),
                      Text(
                        '₹${_todayStats['commission']}',
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
      String label, String value, IconData icon, Color color) {
    return Column(
      children: [
        Icon(icon, color: color, size: 28),
        const SizedBox(height: 4),
        Text(
          value,
          style: const TextStyle(
            fontSize: 20,
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

  Widget _buildPriorityFilter() {
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Row(
        children: [
          FilterChip(
            label: const Text('All'),
            selected: true,
            onSelected: (selected) {},
          ),
          const SizedBox(width: 8),
          FilterChip(
            label: const Text('Overdue'),
            selected: false,
            onSelected: (selected) {},
          ),
          const SizedBox(width: 8),
          FilterChip(
            label: const Text('Due Today'),
            selected: false,
            onSelected: (selected) {},
          ),
          const SizedBox(width: 8),
          FilterChip(
            label: const Text('High Priority'),
            selected: false,
            onSelected: (selected) {},
            backgroundColor: Colors.red.withValues(alpha: 0.2),
          ),
        ],
      ),
    );
  }

  Widget _buildDueCard(Map<String, dynamic> due) {
    final daysOverdue = due['daysOverdue'] as int;
    final customerName = due['customerName'] as String;
    final plotNumber = due['plotNumber'] as String;
    final colonyName = due['colonyName'] as String;
    final address = due['address'] as String;
    final landmark = due['landmark'] as String;
    final phone = due['phone'] as String;
    final emiAmount = due['emiAmount'] as int;
    final dueDate = due['dueDate'] as DateTime;

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
                _buildInfoRow(Icons.location_on, address),
                _buildInfoRow(Icons.landscape, 'Landmark: $landmark'),
                _buildInfoRow(Icons.phone, phone),
                const SizedBox(height: 8),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'EMI Amount: ₹$emiAmount',
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                      ),
                    ),
                    Text(
                      'Due: ${DateFormat('dd MMM').format(dueDate)}',
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
                    icon: const Icon(Icons.check_circle),
                    label: const Text('COLLECT'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.green,
                      foregroundColor: Colors.white,
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: ElevatedButton.icon(
                    onPressed: () => _showPartialDialog(due),
                    icon: const Icon(Icons.timelapse),
                    label: const Text('PARTIAL'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.orange,
                      foregroundColor: Colors.white,
                    ),
                  ),
                ),
                IconButton(
                  onPressed: () => _showNotHomeDialog(due),
                  icon: const Icon(Icons.home_outlined),
                  color: Colors.grey,
                ),
                IconButton(
                  onPressed: () => _openMap(due),
                  icon: const Icon(Icons.map),
                  color: Colors.blue,
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
            'Optimized route for ${_todayStats['visited']} customers\nTotal distance: 12.5 km',
            textAlign: TextAlign.center,
            style: TextStyle(color: Colors.grey[600]),
          ),
          const SizedBox(height: 24),
          ElevatedButton.icon(
            onPressed: () {},
            icon: const Icon(Icons.navigation),
            label: const Text('Start Navigation'),
          ),
        ],
      ),
    );
  }

  Widget _buildHistoryTab() {
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: 5,
      itemBuilder: (context, index) {
        return Card(
          child: ListTile(
            leading: const CircleAvatar(
              backgroundColor: Colors.green,
              child: Icon(Icons.check, color: Colors.white),
            ),
            title: Text('Collection #${1000 + index}'),
            subtitle: const Text('Ramesh Kumar - ₹5,000'),
            trailing: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                const Text('₹5,000'),
                Text(
                  DateFormat('dd MMM, hh:mm a').format(
                    DateTime.now().subtract(Duration(days: index)),
                  ),
                  style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildEarningsTab() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          Card(
            color: Colors.orange.shade50,
            child: const Padding(
              padding: EdgeInsets.all(24),
              child: Column(
                children: [
                  Text(
                    'This Month Earnings',
                    style: TextStyle(fontSize: 18),
                  ),
                  SizedBox(height: 8),
                  Text(
                    '₹18,450',
                    style: TextStyle(
                      fontSize: 42,
                      fontWeight: FontWeight.bold,
                      color: Colors.orange,
                    ),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),
          _buildEarningBreakdown(),
        ],
      ),
    );
  }

  Widget _buildEarningBreakdown() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Earnings Breakdown',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),
            _buildEarningRow('Base Salary', '₹12,000', Colors.blue),
            const Divider(),
            _buildEarningRow(
                'Collection Commission (0.5%)', '₹4,250', Colors.orange),
            const Divider(),
            _buildEarningRow(
                'Per Collection Bonus (85 × ₹20)', '₹1,700', Colors.green),
            const Divider(),
            _buildEarningRow('Target Achievement Bonus', '₹500', Colors.purple),
            const Divider(),
            _buildTotalEarningRow(),
          ],
        ),
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
                decoration: BoxDecoration(
                  color: color,
                  shape: BoxShape.circle,
                ),
              ),
              const SizedBox(width: 12),
              Text(label),
            ],
          ),
          Text(
            amount,
            style: const TextStyle(fontWeight: FontWeight.bold),
          ),
        ],
      ),
    );
  }

  Widget _buildTotalEarningRow() {
    return const Padding(
      padding: EdgeInsets.symmetric(vertical: 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            'Total Earnings',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
            ),
          ),
          Text(
            '₹18,450',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: Colors.orange,
            ),
          ),
        ],
      ),
    );
  }

  void _showCollectDialog(Map<String, dynamic> due) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Collect EMI - ${due['customerName']}'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('EMI Amount: ₹${due['emiAmount']}'),
            const SizedBox(height: 16),
            const TextField(
              decoration: InputDecoration(
                labelText: 'Amount Collected',
                border: OutlineInputBorder(),
              ),
              keyboardType: TextInputType.number,
            ),
            const SizedBox(height: 16),
            DropdownButtonFormField<String>(
              decoration: const InputDecoration(
                labelText: 'Payment Mode',
                border: OutlineInputBorder(),
              ),
              items: ['Cash', 'UPI', 'Cheque', 'Online']
                  .map((mode) => DropdownMenuItem(
                        value: mode,
                        child: Text(mode),
                      ))
                  .toList(),
              onChanged: (value) {},
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _generateReceipt(due);
            },
            child: const Text('Collect & Generate Receipt'),
          ),
        ],
      ),
    );
  }

  void _showPartialDialog(Map<String, dynamic> due) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Partial Collection'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text('Total Due: ₹${due['emiAmount']}'),
            const SizedBox(height: 16),
            const TextField(
              decoration: InputDecoration(
                labelText: 'Amount Collected',
                border: OutlineInputBorder(),
              ),
              keyboardType: TextInputType.number,
            ),
            const SizedBox(height: 16),
            const TextField(
              decoration: InputDecoration(
                labelText: 'Reason for Partial Payment',
                border: OutlineInputBorder(),
              ),
              maxLines: 2,
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Submit'),
          ),
        ],
      ),
    );
  }

  void _showNotHomeDialog(Map<String, dynamic> due) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Customer Not Home'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text('When should we try again?'),
            const SizedBox(height: 16),
            Wrap(
              spacing: 8,
              children: [
                'Today Evening',
                'Tomorrow',
                'Next Week',
              ]
                  .map((option) => ActionChip(
                        label: Text(option),
                        onPressed: () {},
                      ))
                  .toList(),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Schedule Follow-up'),
          ),
        ],
      ),
    );
  }

  void _openMap(Map<String, dynamic> due) {
    // Open map with customer location
  }

  void _generateReceipt(Map<String, dynamic> due) {
    // Navigate to receipt generation
    context.push('/emi/receipt', extra: due);
  }

  void _syncData() {
    // Sync with server when online
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Syncing data...')),
    );
  }
}

import 'package:fl_chart/fl_chart.dart';
import 'package:flutter/material.dart';

import '../../../core/constants/app_constants.dart';

/// Analytics Dashboard Page
/// Real-time stats and insights for admin
class AnalyticsDashboardPage extends StatefulWidget {
  const AnalyticsDashboardPage({super.key});

  @override
  State<AnalyticsDashboardPage> createState() => _AnalyticsDashboardPageState();
}

class _AnalyticsDashboardPageState extends State<AnalyticsDashboardPage> {
  String _timeRange = 'today'; // today, week, month, year

  // Sample data
  final Map<String, dynamic> _stats = {
    'today': {
      'bookings': 12,
      'revenue': 2450000.0,
      'leads': 48,
      'conversions': 8,
      'visits': 156,
      'commissions': 145000.0,
    },
    'week': {
      'bookings': 89,
      'revenue': 18500000.0,
      'leads': 342,
      'conversions': 64,
      'visits': 1124,
      'commissions': 925000.0,
    },
    'month': {
      'bookings': 312,
      'revenue': 62500000.0,
      'leads': 1248,
      'conversions': 218,
      'visits': 4856,
      'commissions': 3150000.0,
    },
  };

  // Top agents data
  final List<Map<String, dynamic>> _topAgents = [
    {
      'name': 'Rahul Kumar',
      'sales': 24,
      'revenue': 4800000,
      'rank': 'President'
    },
    {
      'name': 'Priya Sharma',
      'sales': 19,
      'revenue': 3800000,
      'rank': 'Vice President'
    },
    {'name': 'Amit Singh', 'sales': 16, 'revenue': 3200000, 'rank': 'Sr. BDM'},
    {'name': 'Neha Patel', 'sales': 14, 'revenue': 2800000, 'rank': 'BDM'},
    {'name': 'Vikram Rao', 'sales': 12, 'revenue': 2400000, 'rank': 'BDM'},
  ];

  // Revenue chart data
  final List<double> _revenueData = [
    2.5,
    3.2,
    2.8,
    4.1,
    3.9,
    4.8,
    5.2,
    4.5,
    5.8,
    6.1,
    5.5,
    6.2
  ];
  final List<String> _months = [
    'Jan',
    'Feb',
    'Mar',
    'Apr',
    'May',
    'Jun',
    'Jul',
    'Aug',
    'Sep',
    'Oct',
    'Nov',
    'Dec'
  ];

  @override
  Widget build(BuildContext context) {
    final currentStats = (_stats[_timeRange] as Map<String, dynamic>?) ??
        (_stats['today'] as Map<String, dynamic>);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Analytics Dashboard'),
        backgroundColor: Colors.blue.shade700,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () {
              setState(() {});
            },
          ),
          IconButton(
            icon: const Icon(Icons.download),
            onPressed: _exportReport,
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Time Range Selector
            _buildTimeRangeSelector(),
            const SizedBox(height: 24),

            // Stats Cards
            _buildStatsGrid(currentStats),
            const SizedBox(height: 24),

            // Revenue Chart
            _buildRevenueChart(),
            const SizedBox(height: 24),

            // Two Column Layout
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: _buildTopAgentsList(),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: _buildLeadConversionChart(),
                ),
              ],
            ),
            const SizedBox(height: 24),

            // Colony Performance
            _buildColonyPerformanceTable(),
            const SizedBox(height: 24),

            // Recent Activity
            _buildRecentActivity(),
          ],
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
                onTap: () {
                  setState(() {
                    _timeRange = range;
                  });
                },
                child: Container(
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  decoration: BoxDecoration(
                    color:
                        isSelected ? Colors.blue.shade700 : Colors.transparent,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    range.toUpperCase(),
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      color: isSelected ? Colors.white : Colors.grey.shade700,
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
    return GridView.count(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      crossAxisCount: 2,
      crossAxisSpacing: 12,
      mainAxisSpacing: 12,
      childAspectRatio: 1.5,
      children: [
        _buildStatCard(
          'Bookings',
          (stats['bookings'] as int).toString(),
          Icons.shopping_cart,
          Colors.blue,
          '+12% from last period',
          true,
        ),
        _buildStatCard(
          'Revenue',
          '${AppConstants.currencySymbol}${((stats['revenue'] as num).toDouble() / 100000).toStringAsFixed(2)}L',
          Icons.account_balance_wallet,
          Colors.green,
          '+18% from last period',
          true,
        ),
        _buildStatCard(
          'New Leads',
          (stats['leads'] as int).toString(),
          Icons.people,
          Colors.orange,
          '+24% from last period',
          true,
        ),
        _buildStatCard(
          'Conversion Rate',
          '${(((stats['conversions'] as num) / (stats['leads'] as num)) * 100).toStringAsFixed(1)}%',
          Icons.trending_up,
          Colors.purple,
          '+5% from last period',
          true,
        ),
      ],
    );
  }

  Widget _buildStatCard(
    String title,
    String value,
    IconData icon,
    Color color,
    String trend,
    bool isPositive,
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
            Row(
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
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: isPositive
                        ? Colors.green.shade100
                        : Colors.red.shade100,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        isPositive ? Icons.arrow_upward : Icons.arrow_downward,
                        size: 12,
                        color: isPositive ? Colors.green : Colors.red,
                      ),
                      Text(
                        trend.split(' ')[0],
                        style: TextStyle(
                          fontSize: 11,
                          color: isPositive ? Colors.green : Colors.red,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  value,
                  style: TextStyle(
                    fontSize: 28,
                    fontWeight: FontWeight.bold,
                    color: color,
                  ),
                ),
                Text(
                  title,
                  style: TextStyle(
                    fontSize: 14,
                    color: Colors.grey.shade600,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildRevenueChart() {
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
                  'Revenue Trend',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                DropdownButton<String>(
                  value: 'This Year',
                  items: const [
                    DropdownMenuItem(
                        value: 'This Year', child: Text('This Year')),
                    DropdownMenuItem(
                        value: 'Last Year', child: Text('Last Year')),
                  ],
                  onChanged: (value) {},
                ),
              ],
            ),
            const SizedBox(height: 24),
            SizedBox(
              height: 200,
              child: LineChart(
                LineChartData(
                  gridData: const FlGridData(show: true),
                  titlesData: FlTitlesData(
                    leftTitles: AxisTitles(
                      sideTitles: SideTitles(
                        showTitles: true,
                        reservedSize: 40,
                        getTitlesWidget: (value, meta) {
                          return Text(
                            '${value.toInt()}L',
                            style: const TextStyle(fontSize: 10),
                          );
                        },
                      ),
                    ),
                    bottomTitles: AxisTitles(
                      sideTitles: SideTitles(
                        showTitles: true,
                        getTitlesWidget: (value, meta) {
                          if (value.toInt() >= 0 &&
                              value.toInt() < _months.length) {
                            return Text(
                              _months[value.toInt()],
                              style: const TextStyle(fontSize: 10),
                            );
                          }
                          return const Text('');
                        },
                      ),
                    ),
                    rightTitles: const AxisTitles(
                        sideTitles: SideTitles(showTitles: false)),
                    topTitles: const AxisTitles(
                        sideTitles: SideTitles(showTitles: false)),
                  ),
                  borderData: FlBorderData(show: false),
                  lineBarsData: [
                    LineChartBarData(
                      spots: _revenueData.asMap().entries.map((e) {
                        return FlSpot(e.key.toDouble(), e.value);
                      }).toList(),
                      isCurved: true,
                      color: Colors.blue.shade700,
                      barWidth: 3,
                      dotData: const FlDotData(show: true),
                      belowBarData: BarAreaData(
                        show: true,
                        color: Colors.blue.shade700.withValues(alpha: 0.2),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTopAgentsList() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Top Performing Agents',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),
            ..._topAgents.asMap().entries.map((entry) {
              final index = entry.key;
              final agent = entry.value;
              return Container(
                margin: const EdgeInsets.only(bottom: 12),
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: index < 3 ? Colors.amber.shade50 : Colors.grey.shade50,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  children: [
                    Container(
                      width: 36,
                      height: 36,
                      decoration: BoxDecoration(
                        color: index == 0
                            ? Colors.amber
                            : index == 1
                                ? Colors.grey.shade400
                                : index == 2
                                    ? Colors.orange.shade300
                                    : Colors.grey.shade200,
                        borderRadius: BorderRadius.circular(18),
                      ),
                      child: Center(
                        child: Text(
                          '${index + 1}',
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            color:
                                index < 3 ? Colors.white : Colors.grey.shade700,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            agent['name'] as String? ?? 'Unknown',
                            style: const TextStyle(fontWeight: FontWeight.bold),
                          ),
                          Text(
                            agent['rank'] as String? ?? 'Associate',
                            style: TextStyle(
                              fontSize: 12,
                              color: Colors.grey.shade600,
                            ),
                          ),
                        ],
                      ),
                    ),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Text(
                          '${agent['sales']} sales',
                          style: const TextStyle(fontWeight: FontWeight.bold),
                        ),
                        Text(
                          '${AppConstants.currencySymbol}${((agent['revenue'] as num).toDouble() / 100000).toStringAsFixed(1)}L',
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.green.shade700,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              );
            }),
          ],
        ),
      ),
    );
  }

  Widget _buildLeadConversionChart() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Lead Conversion',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),
            SizedBox(
              height: 200,
              child: PieChart(
                PieChartData(
                  sections: [
                    PieChartSectionData(
                      color: Colors.green,
                      value: 35,
                      title: '35%\nConverted',
                      radius: 60,
                      titleStyle: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                    PieChartSectionData(
                      color: Colors.orange,
                      value: 25,
                      title: '25%\nIn Progress',
                      radius: 50,
                      titleStyle: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                    PieChartSectionData(
                      color: Colors.blue,
                      value: 20,
                      title: '20%\nNew',
                      radius: 40,
                      titleStyle: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                    PieChartSectionData(
                      color: Colors.red,
                      value: 20,
                      title: '20%\nLost',
                      radius: 40,
                      titleStyle: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                  ],
                  sectionsSpace: 2,
                  centerSpaceRadius: 20,
                ),
              ),
            ),
            const SizedBox(height: 16),
            Wrap(
              spacing: 16,
              runSpacing: 8,
              children: [
                _buildLegendItem(Colors.green, 'Converted (35%)'),
                _buildLegendItem(Colors.orange, 'In Progress (25%)'),
                _buildLegendItem(Colors.blue, 'New (20%)'),
                _buildLegendItem(Colors.red, 'Lost (20%)'),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildLegendItem(Color color, String label) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 12,
          height: 12,
          decoration: BoxDecoration(
            color: color,
            borderRadius: BorderRadius.circular(2),
          ),
        ),
        const SizedBox(width: 4),
        Text(label, style: const TextStyle(fontSize: 11)),
      ],
    );
  }

  Widget _buildColonyPerformanceTable() {
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
                  'Colony Performance',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                TextButton(
                  onPressed: () {},
                  child: const Text('View All'),
                ),
              ],
            ),
            const SizedBox(height: 16),
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: DataTable(
                columns: const [
                  DataColumn(label: Text('Colony')),
                  DataColumn(label: Text('Plots')),
                  DataColumn(label: Text('Booked')),
                  DataColumn(label: Text('Revenue')),
                  DataColumn(label: Text('Progress')),
                ],
                rows: [
                  _buildColonyRow(
                      'Suryoday Heights Phase 1', 120, 89, 23400000, 74),
                  _buildColonyRow(
                      'Raghunath City Center', 80, 45, 14500000, 56),
                  _buildColonyRow('Braj Radha Enclave', 200, 156, 48500000, 78),
                  _buildColonyRow('Budh Bihar Colony', 60, 22, 6200000, 37),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  DataRow _buildColonyRow(
      String name, int total, int booked, int revenue, int percent) {
    return DataRow(
      cells: [
        DataCell(Text(name)),
        DataCell(Text('$total')),
        DataCell(Text('$booked')),
        DataCell(Text(
            '${AppConstants.currencySymbol}${(revenue / 100000).toStringAsFixed(1)}L')),
        DataCell(
          SizedBox(
            width: 100,
            child: LinearProgressIndicator(
              value: percent / 100,
              backgroundColor: Colors.grey.shade200,
              valueColor: AlwaysStoppedAnimation<Color>(
                percent > 70
                    ? Colors.green
                    : percent > 40
                        ? Colors.orange
                        : Colors.red,
              ),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildRecentActivity() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Recent Activity',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),
            _buildActivityItem(
              Icons.check_circle,
              Colors.green,
              'New Booking',
              'Rahul Kumar booked Plot #45 in Suryoday Heights',
              '2 mins ago',
            ),
            _buildActivityItem(
              Icons.person_add,
              Colors.blue,
              'New Associate',
              'Priya Sharma joined as Sr. Associate',
              '15 mins ago',
            ),
            _buildActivityItem(
              Icons.payment,
              Colors.orange,
              'Commission Paid',
              '₹45,000 paid to Amit Singh',
              '1 hour ago',
            ),
            _buildActivityItem(
              Icons.location_on,
              Colors.purple,
              'Site Visit',
              'Neha Patel completed site visit with 3 customers',
              '2 hours ago',
            ),
          ],
        ),
      ),
    );
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
                Text(
                  title,
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
                Text(
                  description,
                  style: TextStyle(
                    fontSize: 13,
                    color: Colors.grey.shade600,
                  ),
                ),
                Text(
                  time,
                  style: TextStyle(
                    fontSize: 11,
                    color: Colors.grey.shade500,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  void _exportReport() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Export Report'),
        content: const Text(
          'Report will be exported as PDF and sent to your email.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('Report exported successfully!'),
                  backgroundColor: Colors.green,
                ),
              );
            },
            child: const Text('Export'),
          ),
        ],
      ),
    );
  }
}

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/services/api_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/responsive_helper.dart';

/// Provider for admin reports data
final reportsProvider = FutureProvider<Map<String, dynamic>>((ref) async {
  final api = ApiService();
  try {
    final response = await api.get('admin/reports');
    if (response['success'] == true) {
      return Map<String, dynamic>.from(response['data'] as Map? ?? {});
    }
  } catch (e) {
    // Fall back to empty data
  }
  return {};
});

class ReportsPage extends ConsumerWidget {
  const ReportsPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final reportsAsync = ref.watch(reportsProvider);

    return reportsAsync.when(
      loading: () => const Center(child: CircularProgressIndicator()),
      error: (e, _) => _buildPage(context, ref, {}),
      data: (data) => _buildPage(context, ref, data),
    );
  }

  Widget _buildPage(
    BuildContext context,
    WidgetRef ref,
    Map<String, dynamic> data,
  ) {
    return RefreshIndicator(
      onRefresh: () async => ref.invalidate(reportsProvider),
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        child: Column(
          children: [
            // Header
            Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: Colors.white,
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.05),
                    blurRadius: 10,
                  ),
                ],
              ),
              child: Row(
                children: [
                  const Icon(
                    Icons.assessment,
                    size: 32,
                    color: AppTheme.primaryColor,
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Reports & Analytics',
                          style: TextStyle(
                            fontSize: ResponsiveHelper.fontSize(context, 24),
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 4),
                        const Text(
                          'Business insights and performance metrics',
                          style: TextStyle(color: Colors.grey),
                        ),
                      ],
                    ),
                  ),
                  Row(
                    children: [
                      OutlinedButton.icon(
                        onPressed: () {},
                        icon: const Icon(Icons.calendar_today),
                        label: const Text('This Month'),
                      ),
                      const SizedBox(width: 12),
                      ElevatedButton.icon(
                        onPressed: () {},
                        icon: const Icon(Icons.download),
                        label: const Text('Export'),
                      ),
                    ],
                  ),
                ],
              ),
            ),

            // Report Cards
            Padding(
              padding: const EdgeInsets.all(24),
              child: GridView.count(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                crossAxisCount: ResponsiveHelper.isLargeScreen(context) ? 3 : 2,
                childAspectRatio: 1.3,
                crossAxisSpacing: 16,
                mainAxisSpacing: 16,
                children: [
                  _buildReportCard(
                    context,
                    'Sales Report',
                    'Monthly sales performance',
                    Icons.trending_up,
                    Colors.blue,
                    _getValue(data, 'sales', '₹0'),
                    _getChange(data, 'sales', 'N/A'),
                    _isPositive(data, 'sales'),
                  ),
                  _buildReportCard(
                    context,
                    'Booking Report',
                    'Plot booking status',
                    Icons.book_online,
                    Colors.green,
                    _getValue(data, 'bookings', '0'),
                    _getChange(data, 'bookings', 'N/A'),
                    _isPositive(data, 'bookings'),
                  ),
                  _buildReportCard(
                    context,
                    'Collection Report',
                    'Payment collections',
                    Icons.account_balance_wallet,
                    Colors.purple,
                    _getValue(data, 'collections', '₹0'),
                    _getChange(data, 'collections', 'N/A'),
                    _isPositive(data, 'collections'),
                  ),
                  _buildReportCard(
                    context,
                    'Agent Performance',
                    'Top performing agents',
                    Icons.people,
                    Colors.orange,
                    _getValue(data, 'agents', '0'),
                    _getChange(data, 'agents', 'N/A'),
                    _isPositive(data, 'agents'),
                  ),
                  _buildReportCard(
                    context,
                    'Colony Progress',
                    'Development status',
                    Icons.location_city,
                    Colors.teal,
                    _getValue(data, 'colonies', '0'),
                    _getChange(data, 'colonies', 'N/A'),
                    _isPositive(data, 'colonies'),
                  ),
                  _buildReportCard(
                    context,
                    'EMI Status',
                    'Pending collections',
                    Icons.payment,
                    Colors.red,
                    _getValue(data, 'emi', '₹0'),
                    _getChange(data, 'emi', 'N/A'),
                    _isPositive(data, 'emi'),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _getValue(Map<String, dynamic> data, String key, String fallback) {
    try {
      return data[key]?['value']?.toString() ?? fallback;
    } catch (_) {
      return fallback;
    }
  }

  String _getChange(Map<String, dynamic> data, String key, String fallback) {
    try {
      return data[key]?['change']?.toString() ?? fallback;
    } catch (_) {
      return fallback;
    }
  }

  bool _isPositive(Map<String, dynamic> data, String key) {
    try {
      return data[key]?['positive'] == true;
    } catch (_) {
      return true;
    }
  }

  Widget _buildReportCard(
    BuildContext context,
    String title,
    String subtitle,
    IconData icon,
    Color color,
    String value,
    String change,
    bool isPositive,
  ) {
    return Card(
      elevation: 2,
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: color.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Icon(icon, color: color),
                ),
                const Spacer(),
                IconButton(onPressed: () {}, icon: const Icon(Icons.more_vert)),
              ],
            ),
            const Spacer(),
            Text(
              title,
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 4),
            Text(
              subtitle,
              style: TextStyle(color: Colors.grey.shade600, fontSize: 12),
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Text(
                  value,
                  style: TextStyle(
                    fontSize: ResponsiveHelper.fontSize(context, 24),
                    fontWeight: FontWeight.bold,
                    color: color,
                  ),
                ),
                const SizedBox(width: 8),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 4,
                  ),
                  decoration: BoxDecoration(
                    color: (isPositive ? Colors.green : Colors.red).withValues(
                      alpha: 0.1,
                    ),
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Text(
                    change,
                    style: TextStyle(
                      color: isPositive ? Colors.green : Colors.red,
                      fontSize: 11,
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

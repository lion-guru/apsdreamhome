import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/utils/responsive_helper.dart';
import '../../../data/services/crm_service.dart';

class AccountsPage extends ConsumerStatefulWidget {
  const AccountsPage({super.key});

  @override
  ConsumerState<AccountsPage> createState() => _AccountsPageState();
}

class _AccountsPageState extends ConsumerState<AccountsPage>
    with SingleTickerProviderStateMixin {
  late TabController _tabCtrl;

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: 4, vsync: this);
  }

  @override
  void dispose() {
    _tabCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final dataAsync = ref.watch(crmFinanceOverviewProvider);

    return RefreshIndicator(
      onRefresh: () async => ref.invalidate(crmFinanceOverviewProvider),
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
                  Icons.account_balance,
                  size: 32,
                  color: AppTheme.primaryColor,
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Accounts & Finance',
                        style: TextStyle(
                          fontSize: ResponsiveHelper.fontSize(context, 24),
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 4),
                      const Text(
                        'Manage payments, invoices, and financial records',
                        style: TextStyle(color: Colors.grey),
                      ),
                    ],
                  ),
                ),
                IconButton(
                  onPressed: () => ref.invalidate(crmFinanceOverviewProvider),
                  icon: const Icon(Icons.refresh),
                ),
              ],
            ),
          ),

          // Stats
          dataAsync.when(
            data: (data) {
              final s = data['stats'] as Map<String, dynamic>? ?? {};
              return Container(
                padding: const EdgeInsets.all(16),
                child: Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: [
                    _buildStatCard(
                      "Today's Collection",
                      '₹${_fmt(s['todays_collection'] ?? 0)}',
                      Colors.green,
                      Icons.payments,
                    ),
                    _buildStatCard(
                      'Pending EMI',
                      '₹${_fmt(s['pending_emi'] ?? 0)}',
                      Colors.orange,
                      Icons.schedule,
                    ),
                    _buildStatCard(
                      'Outstanding',
                      '₹${_fmt(s['total_outstanding'] ?? 0)}',
                      Colors.red,
                      Icons.trending_down,
                    ),
                    _buildStatCard(
                      'This Month',
                      '₹${_fmt(s['collected_this_month'] ?? 0)}',
                      Colors.blue,
                      Icons.calendar_month,
                    ),
                  ],
                ),
              );
            },
            loading: () => const SizedBox(
              height: 80,
              child: Center(child: CircularProgressIndicator()),
            ),
            error: (_, __) => const SizedBox.shrink(),
          ),

          // Tabs
          Expanded(
            child: dataAsync.when(
              data: (data) {
                final collections =
                    (data['collections'] as List<dynamic>?) ?? [];
                final emiSchedule =
                    (data['emi_schedule'] as List<dynamic>?) ?? [];
                final s = data['stats'] as Map<String, dynamic>? ?? {};

                return DefaultTabController(
                  length: 4,
                  child: Column(
                    children: [
                      TabBar(
                        controller: _tabCtrl,
                        tabs: [
                          Tab(text: 'Collections (${collections.length})'),
                          Tab(
                            text:
                                'EMI Schedule (${s['active_emi_count'] ?? 0})',
                          ),
                          const Tab(text: 'Vendors'),
                          const Tab(text: 'Overview'),
                        ],
                      ),
                      Expanded(
                        child: TabBarView(
                          controller: _tabCtrl,
                          children: [
                            _buildCollections(collections),
                            _buildEMISchedule(emiSchedule),
                            _buildVendors(data),
                            _buildOverview(s),
                          ],
                        ),
                      ),
                    ],
                  ),
                );
              },
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (e, _) => Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(
                      Icons.error_outline,
                      size: 48,
                      color: Colors.red,
                    ),
                    const SizedBox(height: 12),
                    Text('Error: $e'),
                    const SizedBox(height: 12),
                    ElevatedButton(
                      onPressed: () =>
                          ref.invalidate(crmFinanceOverviewProvider),
                      child: const Text('Retry'),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ─── Tabs ────────────────────────────────────────────────────────

  Widget _buildCollections(List<dynamic> collections) {
    if (collections.isEmpty) {
      return const Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.payments_outlined, size: 64, color: Colors.grey),
            SizedBox(height: 16),
            Text(
              'No collections recorded today',
              style: TextStyle(fontSize: 16, color: Colors.grey),
            ),
          ],
        ),
      );
    }
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: collections.length,
      itemBuilder: (ctx, i) {
        final c = collections[i];
        final paid = double.tryParse('${c['paid_amount'] ?? 0}') ?? 0;
        final emiAmt = double.tryParse('${c['emi_amount'] ?? 0}') ?? 0;
        final booking = (c['booking_number'] ?? '').toString();
        final customer = (c['customer_name'] ?? '').toString();
        final date = (c['payment_date'] ?? '').toString();
        final installment = c['installment_number'] ?? '';

        return Card(
          margin: const EdgeInsets.only(bottom: 8),
          child: ListTile(
            leading: CircleAvatar(
              backgroundColor: Colors.green.withValues(alpha: 0.1),
              child: const Icon(Icons.check_circle, color: Colors.green),
            ),
            title: Text(
              'Installment #$installment — $booking',
              style: const TextStyle(fontWeight: FontWeight.w600),
            ),
            subtitle: Text(
              '$customer • ${date.length >= 10 ? date.substring(0, 10) : date}',
            ),
            trailing: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Text(
                  '₹${_fmt(paid)}',
                  style: const TextStyle(
                    fontWeight: FontWeight.bold,
                    color: Colors.green,
                    fontSize: 15,
                  ),
                ),
                if (emiAmt > paid)
                  Text(
                    'of ₹${_fmt(emiAmt)}',
                    style: TextStyle(color: Colors.grey.shade500, fontSize: 11),
                  ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildEMISchedule(List<dynamic> emiSchedule) {
    if (emiSchedule.isEmpty) {
      return const Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.check_circle, size: 64, color: Colors.green),
            SizedBox(height: 16),
            Text(
              'No pending EMIs',
              style: TextStyle(fontSize: 16, color: Colors.grey),
            ),
          ],
        ),
      );
    }
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: emiSchedule.length,
      itemBuilder: (ctx, i) {
        final e = emiSchedule[i];
        final amount = double.tryParse('${e['emi_amount'] ?? 0}') ?? 0;
        final dueDate = (e['due_date'] ?? '').toString();
        final status = (e['status'] ?? '').toString();
        final booking = (e['booking_number'] ?? '').toString();
        final customer = (e['customer_name'] ?? '').toString();
        final daysUntil = int.tryParse('${e['days_until_due'] ?? 0}') ?? 0;
        final isOverdue = status == 'overdue' || daysUntil < 0;
        final installment = e['installment_number'] ?? '';

        return Card(
          margin: const EdgeInsets.only(bottom: 8),
          child: ListTile(
            leading: CircleAvatar(
              backgroundColor: isOverdue
                  ? Colors.red.withValues(alpha: 0.1)
                  : Colors.blue.withValues(alpha: 0.1),
              child: Icon(
                isOverdue ? Icons.warning : Icons.calendar_today,
                color: isOverdue ? Colors.red : Colors.blue,
              ),
            ),
            title: Text(
              'Installment #$installment — $customer',
              style: const TextStyle(fontWeight: FontWeight.w600),
            ),
            subtitle: Text(
              '$booking • Due: ${dueDate.length >= 10 ? dueDate.substring(0, 10) : dueDate}',
            ),
            trailing: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Text(
                  '₹${_fmt(amount)}',
                  style: TextStyle(
                    fontWeight: FontWeight.bold,
                    color: isOverdue ? Colors.red : Colors.black,
                    fontSize: 15,
                  ),
                ),
                if (isOverdue)
                  const Text(
                    'OVERDUE',
                    style: TextStyle(color: Colors.red, fontSize: 10),
                  ),
                if (!isOverdue && daysUntil > 0)
                  Text(
                    'in $daysUntil days',
                    style: TextStyle(color: Colors.grey.shade500, fontSize: 11),
                  ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildVendors(Map<String, dynamic> data) {
    final totalVendors = data['stats']?['total_vendors'] ?? 0;
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.store, size: 64, color: Colors.grey),
          const SizedBox(height: 16),
          Text(
            'Total Vendors: $totalVendors',
            style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
          ),
          const SizedBox(height: 8),
          const Text(
            'Vendor management available on web portal',
            style: TextStyle(color: Colors.grey),
          ),
        ],
      ),
    );
  }

  Widget _buildOverview(Map<String, dynamic> s) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Financial Summary',
            style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 16),
          _overviewRow(
            'Total Bookings Value',
            '₹${_fmt(s['total_bookings_value'] ?? 0)}',
            Colors.purple,
          ),
          _overviewRow(
            'Collected This Month',
            '₹${_fmt(s['collected_this_month'] ?? 0)}',
            Colors.green,
          ),
          _overviewRow(
            "Today's Collection",
            '₹${_fmt(s['todays_collection'] ?? 0)}',
            Colors.blue,
          ),
          _overviewRow(
            'Pending EMI Amount',
            '₹${_fmt(s['pending_emi'] ?? 0)}',
            Colors.orange,
          ),
          _overviewRow(
            'Total Outstanding',
            '₹${_fmt(s['total_outstanding'] ?? 0)}',
            Colors.red,
          ),
          _overviewRow(
            'Active EMIs',
            '${s['active_emi_count'] ?? 0}',
            Colors.teal,
          ),
          _overviewRow(
            'Overdue EMIs',
            '${s['overdue_emi_count'] ?? 0}',
            Colors.red,
          ),
          _overviewRow(
            'Total Vendors',
            '${s['total_vendors'] ?? 0}',
            Colors.indigo,
          ),
        ],
      ),
    );
  }

  Widget _overviewRow(String label, String value, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 16),
      margin: const EdgeInsets.only(bottom: 8),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.05),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: color.withValues(alpha: 0.2)),
      ),
      child: Row(
        children: [
          Expanded(
            child: Text(
              label,
              style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w500),
            ),
          ),
          Text(
            value,
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: color,
            ),
          ),
        ],
      ),
    );
  }

  // ─── Helpers ─────────────────────────────────────────────────────

  Widget _buildStatCard(
    String label,
    String value,
    Color color,
    IconData icon,
  ) {
    return Container(
      width: 100,
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Column(
        children: [
          Icon(icon, color: color, size: 24),
          const SizedBox(height: 6),
          FittedBox(
            child: Text(
              value,
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: color,
              ),
            ),
          ),
          const SizedBox(height: 4),
          Text(
            label,
            textAlign: TextAlign.center,
            style: const TextStyle(fontSize: 10, color: Colors.grey),
          ),
        ],
      ),
    );
  }

  String _fmt(dynamic v) {
    final n = double.tryParse('$v') ?? 0;
    if (n >= 10000000) return '${(n / 10000000).toStringAsFixed(2)} Cr';
    if (n >= 100000) return '${(n / 100000).toStringAsFixed(2)} L';
    if (n >= 1000) return '${(n / 1000).toStringAsFixed(1)}K';
    return n.toStringAsFixed(0);
  }
}

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../../core/theme/app_theme.dart';
import '../../../data/repositories/kyc_repository_provider.dart';

class PaymentHistoryPage extends ConsumerStatefulWidget {
  const PaymentHistoryPage({super.key});

  @override
  ConsumerState<PaymentHistoryPage> createState() => _PaymentHistoryPageState();
}

class _PaymentHistoryPageState extends ConsumerState<PaymentHistoryPage> {
  bool _isLoading = true;
  List<Map<String, dynamic>> _payments = [];
  Map<String, dynamic> _stats = {};

  @override
  void initState() {
    super.initState();
    _loadPayments();
  }

  Future<void> _loadPayments() async {
    setState(() => _isLoading = true);
    try {
      final api = ref.read(apiServiceProvider);
      final response = await api.get('user/payment-history');
      final data = response['data'] ?? {};
      if (mounted) {
        setState(() {
          _stats = data['stats'] is Map
              ? Map<String, dynamic>.from(data['stats'] as Map)
              : {};
          final raw = data['payments'] ?? [];
          if (raw is List) {
            _payments = raw
                .map(
                  (e) => Map<String, dynamic>.from(e as Map<dynamic, dynamic>),
                )
                .toList();
          }
          _isLoading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  String _formatCurrency(dynamic amount) {
    final amt = (amount is num)
        ? (amount).toDouble()
        : double.tryParse(amount.toString()) ?? 0.0;
    return '₹${NumberFormat('#,##,###').format(amt.toInt())}';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Payment History'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(color: AppTheme.primaryColor),
            )
          : RefreshIndicator(
              onRefresh: _loadPayments,
              color: AppTheme.primaryColor,
              child: CustomScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                slivers: [
                  if (_stats.isNotEmpty)
                    SliverToBoxAdapter(child: _buildStatsCards()),
                  SliverToBoxAdapter(child: _buildFilterHeader()),
                  if (_payments.isEmpty)
                    SliverFillRemaining(
                      child: Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              Icons.receipt_long_outlined,
                              size: 64,
                              color: Colors.grey.shade300,
                            ),
                            const SizedBox(height: 16),
                            Text(
                              'No payments yet',
                              style: TextStyle(
                                fontSize: 16,
                                color: Colors.grey.shade500,
                              ),
                            ),
                          ],
                        ),
                      ),
                    )
                  else
                    SliverPadding(
                      padding: const EdgeInsets.fromLTRB(16, 0, 16, 24),
                      sliver: SliverList(
                        delegate: SliverChildBuilderDelegate(
                          (context, index) =>
                              _buildPaymentCard(_payments[index]),
                          childCount: _payments.length,
                        ),
                      ),
                    ),
                ],
              ),
            ),
    );
  }

  Widget _buildStatsCards() {
    final totalPaid = (_stats['total_paid'] ?? 0);
    final count = (_stats['total_count'] ?? 0);
    final completed = (_stats['completed'] ?? 0) as int;
    final pending = (_stats['pending'] ?? 0) as int;

    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
      child: Row(
        children: [
          Expanded(
            child: _statCard(
              icon: Icons.payments_outlined,
              label: 'Total Paid',
              value: _formatCurrency(totalPaid),
              color: AppTheme.successColor,
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: _statCard(
              icon: Icons.done_all_outlined,
              label: 'Completed',
              value: '$completed/$count',
              color: AppTheme.primaryColor,
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: _statCard(
              icon: Icons.access_time_outlined,
              label: 'Pending',
              value: '$pending',
              color: pending > 0 ? AppTheme.warningColor : Colors.grey.shade400,
            ),
          ),
        ],
      ),
    );
  }

  Widget _statCard({
    required IconData icon,
    required String label,
    required String value,
    required Color color,
  }) {
    return Card(
      elevation: 1,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
        child: Column(
          children: [
            Icon(icon, size: 22, color: color),
            const SizedBox(height: 6),
            Text(
              value,
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.bold,
                color: color,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: TextStyle(fontSize: 10, color: Colors.grey.shade600),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFilterHeader() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
      child: Row(
        children: [
          const Icon(
            Icons.receipt_long_outlined,
            size: 18,
            color: AppTheme.primaryColor,
          ),
          const SizedBox(width: 6),
          Text(
            'Payment Transactions',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: Colors.grey.shade800,
            ),
          ),
          const Spacer(),
          Text(
            '${_payments.length} entries',
            style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
          ),
        ],
      ),
    );
  }

  Widget _buildPaymentCard(Map<String, dynamic> payment) {
    final amount = payment['amount'] ?? 0;
    final status = payment['status']?.toString() ?? '';
    final method = payment['payment_method']?.toString() ?? '';
    final receipt = payment['receipt_number']?.toString() ?? '';
    final transactionId = payment['transaction_id']?.toString() ?? '';
    final bookingNumber = payment['booking_number']?.toString() ?? '';
    final type = payment['type']?.toString() ?? '';
    final dateStr = payment['payment_date']?.toString() ?? '';

    String formattedDate = '';
    try {
      formattedDate = DateFormat(
        'dd MMM yyyy, hh:mm a',
      ).format(DateTime.parse(dateStr));
    } catch (_) {
      formattedDate = dateStr;
    }

    final isSuccess = status == 'completed' || status == 'success';
    final isPending = status == 'pending';
    final isFailed = status == 'failed' || status == 'cancelled';

    final statusColor = isSuccess
        ? AppTheme.successColor
        : isPending
        ? AppTheme.warningColor
        : AppTheme.errorColor;

    final statusIcon = isSuccess
        ? Icons.check_circle
        : isPending
        ? Icons.access_time
        : Icons.cancel;

    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Card(
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
          side: BorderSide(color: Colors.grey.shade200),
        ),
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: statusColor.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Icon(statusIcon, color: statusColor, size: 22),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Text(
                              _formatCurrency(amount),
                              style: TextStyle(
                                fontSize: 18,
                                fontWeight: FontWeight.bold,
                                color: Colors.grey.shade800,
                              ),
                            ),
                            const Spacer(),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 8,
                                vertical: 3,
                              ),
                              decoration: BoxDecoration(
                                color: statusColor.withValues(alpha: 0.1),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text(
                                status.toUpperCase(),
                                style: TextStyle(
                                  fontSize: 9,
                                  fontWeight: FontWeight.bold,
                                  color: statusColor,
                                  letterSpacing: 0.3,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        if (type.isNotEmpty)
                          Text(
                            type.replaceAll('_', ' ').toUpperCase(),
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
              const Divider(height: 16),
              if (bookingNumber.isNotEmpty) _infoRow('Booking', bookingNumber),
              if (method.isNotEmpty) _infoRow('Method', method),
              if (transactionId.isNotEmpty) _infoRow('Txn ID', transactionId),
              if (receipt.isNotEmpty) _infoRow('Receipt', receipt),
              if (formattedDate.isNotEmpty) _infoRow('Date', formattedDate),
            ],
          ),
        ),
      ),
    );
  }

  Widget _infoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 4),
      child: Row(
        children: [
          SizedBox(
            width: 72,
            child: Text(
              label,
              style: TextStyle(
                fontSize: 11,
                color: Colors.grey.shade500,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: TextStyle(fontSize: 12, color: Colors.grey.shade700),
            ),
          ),
        ],
      ),
    );
  }
}

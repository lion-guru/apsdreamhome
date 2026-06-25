import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../../core/services/api_service.dart';
import '../../../core/utils/logger.dart';

final _commissionsProvider = FutureProvider.autoDispose<List<Map<String, dynamic>>>((ref) async {
  try {
    final api = ApiService();
    final response = await api.get('/admin/sales/commissions');
    if (response['success'] == true && response['data'] != null) {
      return (response['data'] as List).cast<Map<String, dynamic>>();
    }
    if (response['data'] is List) {
      return (response['data'] as List).cast<Map<String, dynamic>>();
    }
    return [];
  } catch (e) {
    AppLogger.error('Error fetching commissions', e);
    return [];
  }
});

class CommissionApprovalsPage extends ConsumerStatefulWidget {
  const CommissionApprovalsPage({super.key});

  @override
  ConsumerState<CommissionApprovalsPage> createState() => _CommissionApprovalsPageState();
}

class _CommissionApprovalsPageState extends ConsumerState<CommissionApprovalsPage> {
  String _filterStatus = 'all';
  String _filterType = 'all';

  @override
  Widget build(BuildContext context) {
    final commissionsAsync = ref.watch(_commissionsProvider);

    return Column(
      children: [
        _buildHeader(),
        _buildStatsRow(commissionsAsync),
        _buildFilters(),
        Expanded(
          child: commissionsAsync.when(
            data: (commissions) {
              var filtered = commissions;
              if (_filterStatus != 'all') {
                filtered = filtered.where((c) => c['status'] == _filterStatus).toList();
              }
              if (_filterType != 'all') {
                filtered = filtered.where((c) => c['commission_type'] == _filterType).toList();
              }
              if (filtered.isEmpty) return _buildEmptyState();
              return _buildCommissionsList(filtered);
            },
            loading: () => const Center(child: CircularProgressIndicator()),
            error: (e, _) => Center(child: Text('Error: $e')),
          ),
        ),
      ],
    );
  }

  Widget _buildHeader() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 8),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Commission Approvals',
                  style: Theme.of(context).textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.bold)),
                const SizedBox(height: 4),
                Text('Review agent commissions and payouts',
                  style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Colors.grey[600])),
              ],
            ),
          ),
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.invalidate(_commissionsProvider),
            tooltip: 'Refresh',
          ),
        ],
      ),
    );
  }

  Widget _buildStatsRow(AsyncValue<List<Map<String, dynamic>>> commissionsAsync) {
    return commissionsAsync.when(
      data: (commissions) {
        final total = commissions.length;
        final pending = commissions.where((c) => c['status'] == 'pending').length;
        final approved = commissions.where((c) => c['status'] == 'approved').length;
    final totalAmount = commissions.fold<double>(0, (sum, c) => sum + ((c['amount'] as num?)?.toDouble() ?? 0));
        return Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
          child: Row(
            children: [
              _buildStatCard('Total', '$total', Colors.blue),
              const SizedBox(width: 8),
              _buildStatCard('Pending', '$pending', Colors.orange),
              const SizedBox(width: 8),
              _buildStatCard('Approved', '$approved', Colors.green),
              const SizedBox(width: 8),
              _buildStatCard('Paid', '₹${_formatAmount(totalAmount)}', Colors.purple),
            ],
          ),
        );
      },
      loading: () => const SizedBox.shrink(),
      error: (_, __) => const SizedBox.shrink(),
    );
  }

  Widget _buildStatCard(String label, String value, Color color) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: color.withValues(alpha: 0.2)),
        ),
        child: Column(
          children: [
            Text(value, style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: color)),
            const SizedBox(height: 2),
            Text(label, style: TextStyle(fontSize: 11, color: color.withValues(alpha: 0.7))),
          ],
        ),
      ),
    );
  }

  Widget _buildFilters() {
    return SizedBox(
      height: 48,
      child: ListView(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
        children: [
          _buildFilterChip('All', 'all', 'status'),
          _buildFilterChip('Pending', 'pending', 'status'),
          _buildFilterChip('Approved', 'approved', 'status'),
          _buildFilterChip('Paid', 'paid', 'status'),
          const SizedBox(width: 12),
          _buildFilterChip('Direct Sale', 'direct_sale', 'type'),
          _buildFilterChip('Team Bonus', 'team_bonus', 'type'),
          _buildFilterChip('Performance', 'performance_bonus', 'type'),
          _buildFilterChip('Escrow', 'milestone_escrow', 'type'),
        ],
      ),
    );
  }

  Widget _buildFilterChip(String label, String value, String group) {
    final current = group == 'status' ? _filterStatus : _filterType;
    final selected = current == value;
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: FilterChip(
        label: Text(label, style: TextStyle(fontSize: 12, color: selected ? Colors.white : Colors.grey[700])),
        selected: selected,
        onSelected: (_) => setState(() {
          if (group == 'status') _filterStatus = value;
          else _filterType = value;
        }),
        selectedColor: Colors.blue,
        backgroundColor: Colors.grey[100],
        materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
        visualDensity: VisualDensity.compact,
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.account_balance_wallet_outlined, size: 64, color: Colors.grey[400]),
          const SizedBox(height: 16),
          Text('No commissions found', style: TextStyle(fontSize: 16, color: Colors.grey[600])),
          const SizedBox(height: 8),
          Text('Commissions will appear here once bookings are processed', style: TextStyle(fontSize: 13, color: Colors.grey[500])),
        ],
      ),
    );
  }

  Widget _buildCommissionsList(List<Map<String, dynamic>> commissions) {
    return ListView.builder(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      itemCount: commissions.length,
      itemBuilder: (ctx, i) => _buildCommissionCard(commissions[i]),
    );
  }

  Widget _buildCommissionCard(Map<String, dynamic> commission) {
    final status = commission['status']?.toString() ?? 'unknown';
    final statusColor = _statusColor(status);
    final type = (commission['commission_type']?.toString() ?? 'N/A').replaceAll('_', ' ');
    final agent = commission['agent_name']?.toString() ?? commission['beneficiary_name']?.toString() ?? 'N/A';
    final amount = (commission['amount'] as num?)?.toDouble() ?? 0;
    final booking = commission['booking_number']?.toString() ?? '';
    final createdAt = commission['created_at']?.toString();

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 1,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: statusColor.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: statusColor.withValues(alpha: 0.3)),
                  ),
                  child: Text(status.toUpperCase(),
                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: statusColor)),
                ),
                const SizedBox(width: 8),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: Colors.blueGrey.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(15),
                  ),
                  child: Text(type, style: const TextStyle(fontSize: 11, color: Colors.blueGrey)),
                ),
                const Spacer(),
                if (createdAt != null)
                  Text(_formatDate(createdAt), style: TextStyle(fontSize: 12, color: Colors.grey[500])),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Icon(Icons.person_outline, size: 18, color: Colors.grey[600]),
                const SizedBox(width: 8),
                Expanded(child: Text(agent, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 15))),
              ],
            ),
            if (booking.isNotEmpty) ...[
              const SizedBox(height: 6),
              Row(
                children: [
                  Icon(Icons.receipt_long_outlined, size: 18, color: Colors.grey[600]),
                  const SizedBox(width: 8),
                  Text('Booking: $booking', style: const TextStyle(fontSize: 13)),
                ],
              ),
            ],
            const SizedBox(height: 6),
            Row(
              children: [
                Icon(Icons.currency_rupee, size: 18, color: Colors.green[600]),
                const SizedBox(width: 8),
                Text('₹${_formatAmount(amount)}',
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.green)),
              ],
            ),
            if (status == 'pending') ...[
              const Divider(height: 24),
              Row(
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  OutlinedButton.icon(
                    onPressed: () => _handleCommissionAction(commission, 'rejected'),
                    icon: const Icon(Icons.close, size: 16),
                    label: const Text('Reject'),
                    style: OutlinedButton.styleFrom(foregroundColor: Colors.red),
                  ),
                  const SizedBox(width: 12),
                  ElevatedButton.icon(
                    onPressed: () => _handleCommissionAction(commission, 'approved'),
                    icon: const Icon(Icons.check, size: 16),
                    label: const Text('Approve'),
                    style: ElevatedButton.styleFrom(backgroundColor: Colors.green, foregroundColor: Colors.white),
                  ),
                ],
              ),
            ],
          ],
        ),
      ),
    );
  }

  Future<void> _handleCommissionAction(Map<String, dynamic> commission, String action) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(action == 'rejected' ? 'Reject Commission?' : 'Approve Commission?'),
        content: Text('Commission of ₹${_formatAmount((commission['amount'] as num?)?.toDouble() ?? 0)} for ${commission['agent_name'] ?? 'N/A'}'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancel')),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: TextButton.styleFrom(foregroundColor: action == 'rejected' ? Colors.red : Colors.green),
            child: Text(action == 'rejected' ? 'Reject' : 'Approve'),
          ),
        ],
      ),
    );
    if (confirmed != true) return;

    try {
      final api = ApiService();
      await api.post('/admin/commission/action', data: {'commission_id': commission['id'], 'action': action});
      ref.invalidate(_commissionsProvider);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Commission ${action == 'rejected' ? 'rejected' : 'approved'}')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
      }
    }
  }

  Color _statusColor(String status) {
    switch (status) {
      case 'pending': return Colors.orange;
      case 'approved': return Colors.blue;
      case 'paid': return Colors.green;
      case 'rejected': return Colors.red;
      default: return Colors.grey;
    }
  }

  String _formatDate(String dateStr) {
    try {
      return DateFormat('dd MMM yyyy').format(DateTime.parse(dateStr));
    } catch (_) {
      return dateStr;
    }
  }

  String _formatAmount(double amount) {
    if (amount >= 10000000) return '${(amount / 10000000).toStringAsFixed(2)} Cr';
    if (amount >= 100000) return '${(amount / 100000).toStringAsFixed(2)} L';
    if (amount >= 1000) return '${(amount / 1000).toStringAsFixed(1)}K';
    return amount.toStringAsFixed(0);
  }
}

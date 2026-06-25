import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../../core/services/api_service.dart';
import '../../../core/utils/logger.dart';

final _bookingsProvider = FutureProvider.autoDispose<List<Map<String, dynamic>>>((ref) async {
  try {
    final api = ApiService();
    final response = await api.get('/admin/sales/bookings');
    if (response['success'] == true && response['data'] != null) {
      return (response['data'] as List).cast<Map<String, dynamic>>();
    }
    if (response['data'] is List) {
      return (response['data'] as List).cast<Map<String, dynamic>>();
    }
    return [];
  } catch (e) {
    AppLogger.error('Error fetching bookings', e);
    return [];
  }
});

class BookingApprovalsPage extends ConsumerStatefulWidget {
  const BookingApprovalsPage({super.key});

  @override
  ConsumerState<BookingApprovalsPage> createState() => _BookingApprovalsPageState();
}

class _BookingApprovalsPageState extends ConsumerState<BookingApprovalsPage> {
  String _filterStatus = 'all';
  String _searchQuery = '';

  @override
  Widget build(BuildContext context) {
    final bookingsAsync = ref.watch(_bookingsProvider);

    return Column(
      children: [
        _buildHeader(),
        _buildStatsRow(bookingsAsync),
        _buildFilters(),
        Expanded(
          child: bookingsAsync.when(
            data: (bookings) {
              var filtered = bookings;
              if (_filterStatus != 'all') {
                filtered = filtered.where((b) => b['status'] == _filterStatus).toList();
              }
              if (_searchQuery.isNotEmpty) {
                final q = _searchQuery.toLowerCase();
                filtered = filtered.where((b) =>
                  (b['customer_name']?.toString().toLowerCase().contains(q) ?? false) ||
                  (b['plot_number']?.toString().toLowerCase().contains(q) ?? false) ||
                  (b['booking_number']?.toString().toLowerCase().contains(q) ?? false)
                ).toList();
              }
              if (filtered.isEmpty) return _buildEmptyState();
              return _buildBookingsList(filtered);
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
                Text(
                  'Booking Approvals',
                  style: Theme.of(context).textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 4),
                Text(
                  'Review and manage plot bookings',
                  style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Colors.grey[600]),
                ),
              ],
            ),
          ),
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.invalidate(_bookingsProvider),
            tooltip: 'Refresh',
          ),
        ],
      ),
    );
  }

  Widget _buildStatsRow(AsyncValue<List<Map<String, dynamic>>> bookingsAsync) {
    return bookingsAsync.when(
      data: (bookings) {
        final total = bookings.length;
        final pending = bookings.where((b) => b['status'] == 'token_paid' || b['status'] == 'pending').length;
        final active = bookings.where((b) => b['status'] == 'emi_active' || b['status'] == 'agreement_signed').length;
        final completed = bookings.where((b) => b['status'] == 'fully_paid' || b['status'] == 'registration_done').length;
        return Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          child: Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              _buildStatCard('Total', '$total', Colors.blue),
              _buildStatCard('Pending', '$pending', Colors.orange),
              _buildStatCard('Active', '$active', Colors.green),
              _buildStatCard('Completed', '$completed', Colors.purple),
            ],
          ),
        );
      },
      loading: () => const SizedBox.shrink(),
      error: (_, __) => const SizedBox.shrink(),
    );
  }

  Widget _buildStatCard(String label, String value, Color color) {
    return Container(
      width: 100,
      padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: color.withValues(alpha: 0.2)),
      ),
      child: Column(
        children: [
          FittedBox(
            child: Text(value, style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: color)),
          ),
          const SizedBox(height: 2),
          Text(label, style: TextStyle(fontSize: 10, color: color.withValues(alpha: 0.7))),
        ],
      ),
    );
  }

  Widget _buildFilters() {
    final statuses = ['all', 'token_paid', 'agreement_signed', 'emi_active', 'partially_paid', 'fully_paid', 'cancelled'];
    final labels = {
      'all': 'All', 'token_paid': 'Token Paid', 'agreement_signed': 'Agreement',
      'emi_active': 'EMI Active', 'partially_paid': 'Partial', 'fully_paid': 'Paid', 'cancelled': 'Cancelled',
    };
    return SizedBox(
      height: 48,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
        itemCount: statuses.length,
        separatorBuilder: (_, __) => const SizedBox(width: 8),
        itemBuilder: (ctx, i) {
          final s = statuses[i];
          final selected = _filterStatus == s;
          return FilterChip(
            label: Text(labels[s] ?? s, style: TextStyle(fontSize: 12, color: selected ? Colors.white : Colors.grey[700])),
            selected: selected,
            onSelected: (_) => setState(() => _filterStatus = s),
            selectedColor: Colors.blue,
            backgroundColor: Colors.grey[100],
            materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
            visualDensity: VisualDensity.compact,
          );
        },
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.event_busy, size: 64, color: Colors.grey[400]),
          const SizedBox(height: 16),
          Text('No bookings found', style: TextStyle(fontSize: 16, color: Colors.grey[600])),
          const SizedBox(height: 8),
          Text('Try adjusting your filters', style: TextStyle(fontSize: 13, color: Colors.grey[500])),
        ],
      ),
    );
  }

  Widget _buildBookingsList(List<Map<String, dynamic>> bookings) {
    return ListView.builder(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      itemCount: bookings.length,
      itemBuilder: (ctx, i) => _buildBookingCard(bookings[i]),
    );
  }

  Widget _buildBookingCard(Map<String, dynamic> booking) {
    final status = booking['status']?.toString() ?? 'unknown';
    final statusColor = _statusColor(status);
    final customer = booking['customer_name']?.toString() ?? booking['name']?.toString() ?? 'N/A';
    final plot = booking['plot_number']?.toString() ?? 'N/A';
    final colony = booking['colony_name']?.toString() ?? '';
    final amount = (booking['total_amount'] as num?)?.toDouble() ?? 0;
    final createdAt = booking['created_at']?.toString();

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
                  child: Text(status.replaceAll('_', ' ').toUpperCase(),
                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: statusColor)),
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
                Expanded(child: Text(customer, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 15))),
              ],
            ),
            const SizedBox(height: 6),
            Row(
              children: [
                Icon(Icons.landscape_outlined, size: 18, color: Colors.grey[600]),
                const SizedBox(width: 8),
                Text('Plot $plot', style: const TextStyle(fontSize: 14)),
                if (colony.isNotEmpty) ...[
                  const SizedBox(width: 8),
                  Container(width: 4, height: 4, decoration: BoxDecoration(color: Colors.grey[400], shape: BoxShape.circle)),
                  const SizedBox(width: 8),
                  Text(colony, style: TextStyle(fontSize: 13, color: Colors.grey[600])),
                ],
              ],
            ),
            const SizedBox(height: 6),
            Row(
              children: [
                Icon(Icons.currency_rupee, size: 18, color: Colors.grey[600]),
                const SizedBox(width: 8),
                Text('₹${_formatAmount(amount)}', style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14, color: Colors.green)),
              ],
            ),
            if (status == 'token_paid' || status == 'pending') ...[
              const Divider(height: 24),
              Row(
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  OutlinedButton.icon(
                    onPressed: () => _handleBookingAction(booking, 'rejected'),
                    icon: const Icon(Icons.close, size: 16),
                    label: const Text('Reject'),
                    style: OutlinedButton.styleFrom(foregroundColor: Colors.red),
                  ),
                  const SizedBox(width: 12),
                  ElevatedButton.icon(
                    onPressed: () => _handleBookingAction(booking, 'emi_active'),
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

  Future<void> _handleBookingAction(Map<String, dynamic> booking, String newStatus) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(newStatus == 'rejected' ? 'Reject Booking?' : 'Approve Booking?'),
        content: Text('Booking for ${booking['plot_number']} by ${booking['customer_name'] ?? 'N/A'}'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancel')),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: TextButton.styleFrom(foregroundColor: newStatus == 'rejected' ? Colors.red : Colors.green),
            child: Text(newStatus == 'rejected' ? 'Reject' : 'Approve'),
          ),
        ],
      ),
    );
    if (confirmed != true) return;

    try {
      final api = ApiService();
      await api.post('/admin/sales/bookings/${booking['id']}/update', data: {'status': newStatus});
      ref.invalidate(_bookingsProvider);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Booking ${newStatus == 'rejected' ? 'rejected' : 'approved'}')),
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
      case 'token_paid': return Colors.orange;
      case 'agreement_signed': return Colors.blue;
      case 'emi_active': return Colors.teal;
      case 'partially_paid': return Colors.amber;
      case 'fully_paid': return Colors.green;
      case 'registration_done': return Colors.purple;
      case 'cancelled': return Colors.red;
      default: return Colors.grey;
    }
  }

  String _formatDate(String dateStr) {
    try {
      final date = DateTime.parse(dateStr);
      return DateFormat('dd MMM yyyy').format(date);
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

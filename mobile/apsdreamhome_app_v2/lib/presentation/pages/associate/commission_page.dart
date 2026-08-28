import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../data/repositories/mlm_repository.dart';
import '../../widgets/app_widgets.dart';

/// Commission Page - Connected to MlmRepository
class CommissionPage extends ConsumerWidget {
  const CommissionPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final commissionsAsync = ref.watch(commissionsProvider(null));

    return Scaffold(
      appBar: AppBar(
        title: const Text('My Commission'),
        actions: [
          IconButton(
            onPressed: () { ref.refresh(commissionsProvider(null)); }, // ignore: unused_result
            icon: const Icon(Icons.refresh),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async { ref.refresh(commissionsProvider(null)); }, // ignore: unused_result
        child: commissionsAsync.when(
          loading: () => const Center(child: CircularProgressIndicator()),
          error: (error, stack) => AppWidgets.errorWidget(
            message: error.toString(),
            onRetry: () { ref.refresh(commissionsProvider(null)); }, // ignore: unused_result
          ),
          data: (commissions) {
            if (commissions.isEmpty) {
              return Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.attach_money, size: 80, color: Colors.grey.shade300),
                    const SizedBox(height: 16),
                    Text(
                      'No commissions yet',
                      style: TextStyle(
                        fontSize: 18,
                        color: Colors.grey.shade600,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'Start earning by closing deals',
                      style: TextStyle(
                        fontSize: 14,
                        color: Colors.grey.shade500,
                      ),
                    ),
                  ],
                ),
              );
            }

            return ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: commissions.length,
              itemBuilder: (context, index) {
                final commission = commissions[index];
                return _buildCommissionCard(commission);
              },
            );
          },
        ),
      ),
    );
  }

  Widget _buildCommissionCard(Commission commission) {
    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header
            Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        commission.type,
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        commission.description,
                        style: TextStyle(
                          fontSize: 14,
                          color: Colors.grey.shade600,
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: _getStatusColor(commission.status).withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                      color: _getStatusColor(commission.status).withValues(alpha: 0.3),
                    ),
                  ),
                  child: Text(
                    commission.status.toUpperCase(),
                    style: TextStyle(
                      fontSize: 12,
                      color: _getStatusColor(commission.status),
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
              ],
            ),
            
            const SizedBox(height: 12),
            
            // Amount and Date
            Row(
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Amount',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade500,
                      ),
                    ),
                    Text(
                      '₹${commission.amount.toStringAsFixed(2)}',
                      style: const TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                        color: Colors.green,
                      ),
                    ),
                  ],
                ),
                const Spacer(),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(
                      'Date',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade500,
                      ),
                    ),
                    Text(
                      _getFormattedDate(commission.date),
                      style: TextStyle(
                        fontSize: 14,
                        color: Colors.grey.shade600,
                      ),
                    ),
                  ],
                ),
              ],
            ),
            
            if (commission.notes != null && commission.notes!.isNotEmpty) ...[
              const SizedBox(height: 12),
              Text(
                'Notes',
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.grey.shade500,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                commission.notes!,
                style: TextStyle(
                  fontSize: 14,
                  color: Colors.grey.shade700,
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return Colors.orange;
      case 'approved':
        return Colors.blue;
      case 'paid':
        return Colors.green;
      case 'rejected':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }

  String _getFormattedDate(DateTime date) {
    final now = DateTime.now();
    final difference = now.difference(date);
    
    if (difference.inDays == 0) {
      return 'today';
    } else if (difference.inDays == 1) {
      return 'yesterday';
    } else if (difference.inDays < 7) {
      return '${difference.inDays} days ago';
    } else if (difference.inDays < 30) {
      return '${(difference.inDays / 7).floor()} weeks ago';
    } else {
      return '${(difference.inDays / 30).floor()} months ago';
    }
  }
}
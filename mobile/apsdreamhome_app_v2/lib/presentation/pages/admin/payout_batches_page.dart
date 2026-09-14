import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

final _batchesProvider = FutureProvider<List<dynamic>>((ref) async {
  final api = ApiService();
  AppConstants.initBaseUrl();
  try {
    return await api.getPayoutBatches();
  } catch (_) {
    return [];
  }
});

class PayoutBatchesPage extends ConsumerWidget {
  const PayoutBatchesPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final async = ref.watch(_batchesProvider);
    return Scaffold(
      appBar: AppBar(
        title: const Text('Payout Batches'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
      ),
      body: async.when(
        data: (items) {
          if (items.isEmpty) return _empty(context, ref);
          return RefreshIndicator(
            onRefresh: () async => ref.invalidate(_batchesProvider),
            color: AppTheme.primaryColor,
            child: ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: items.length,
              separatorBuilder: (_, _) => const SizedBox(height: 12),
              itemBuilder: (context, i) => _card(context, items[i] as Map<String, dynamic>),
            ),
          );
        },
        loading: () => const Center(child: CircularProgressIndicator(color: AppTheme.primaryColor)),
        error: (e, _) => _error(ref, e.toString()),
      ),
    );
  }

  Widget _card(BuildContext context, Map<String, dynamic> b) {
    final id = b['id']?.toString() ?? '';
    final name = b['batch_name']?.toString() ?? 'Batch $id';
    final status = b['status']?.toString() ?? 'draft';
    final total = (b['total_amount'] as num?)?.toDouble() ?? 0;
    final entries = (b['total_entries'] as num?)?.toInt() ?? 0;
    final created = b['created_at']?.toString() ?? '';
    final map = {
      'draft': Colors.grey,
      'pending_approval': Colors.orange,
      'approved': Colors.blue,
      'processing': Colors.teal,
      'completed': Colors.green,
      'rejected': Colors.red,
    };
    final color = map[status] ?? Colors.grey;
    return InkWell(
      onTap: () => context.push('/admin/payout-batches/$id'),
      borderRadius: BorderRadius.circular(16),
      child: GlassCard(
        opacity: 0.08,
        blur: 8,
        padding: const EdgeInsets.all(16),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(color: AppTheme.primaryColor.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(12)),
              child: const Icon(Icons.account_balance_wallet_rounded, color: AppTheme.primaryColor),
            ),
            const SizedBox(width: 12),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(name, style: const TextStyle(fontWeight: FontWeight.w700), maxLines: 1, overflow: TextOverflow.ellipsis),
              Text('ID $id • $entries entries${created.isNotEmpty ? ' • ${created.split(' ').first}' : ''}', style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
            ])),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              decoration: BoxDecoration(color: color.withValues(alpha: 0.15), borderRadius: BorderRadius.circular(20)),
              child: Text(status.replaceAll('_', ' '), style: TextStyle(color: color, fontWeight: FontWeight.w700, fontSize: 11)),
            ),
          ]),
          const SizedBox(height: 10),
          Text('₹${NumberFormat('#,##,###').format(total)}', style: const TextStyle(fontWeight: FontWeight.w700, color: AppTheme.primaryColor)),
        ]),
      ),
    );
  }

  Widget _empty(BuildContext context, WidgetRef ref) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
          Icon(Icons.receipt_long, size: 56, color: Colors.grey.shade400),
          const SizedBox(height: 12),
          const Text('No payout batches'),
          const SizedBox(height: 12),
          ElevatedButton(onPressed: () => ref.invalidate(_batchesProvider), child: const Text('Refresh')),
        ]),
      ),
    );
  }

  Widget _error(WidgetRef ref, String e) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
          Text(e, textAlign: TextAlign.center),
          const SizedBox(height: 12),
          ElevatedButton(onPressed: () => ref.invalidate(_batchesProvider), child: const Text('Retry')),
        ]),
      ),
    );
  }
}

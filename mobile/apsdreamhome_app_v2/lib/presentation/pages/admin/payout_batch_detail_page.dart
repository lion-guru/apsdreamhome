import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

final _detailProvider = FutureProvider.family<Map<String, dynamic>, String>((ref, id) async {
  final api = ApiService();
  AppConstants.initBaseUrl();
  return api.getPayoutBatchDetail(id);
});

class PayoutBatchDetailPage extends ConsumerWidget {
  final String batchId;
  const PayoutBatchDetailPage({super.key, required this.batchId});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final async = ref.watch(_detailProvider(batchId));
    return Scaffold(
      appBar: AppBar(
        title: Text('Batch $batchId'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        actions: [
          IconButton(onPressed: () => _exportCsv(context), icon: const Icon(Icons.download), tooltip: 'Export CSV'),
        ],
      ),
      body: async.when(
        data: (data) => _buildBody(context, ref, data),
        loading: () => const Center(child: CircularProgressIndicator(color: AppTheme.primaryColor)),
        error: (e, _) => Center(child: Padding(padding: const EdgeInsets.all(24), child: Text(e.toString(), textAlign: TextAlign.center))),
      ),
    );
  }

  Widget _buildBody(BuildContext context, WidgetRef ref, Map<String, dynamic> data) {
    final batch = (data['batch'] as Map<String, dynamic>?) ?? {};
    final entries = (data['entries'] as List<dynamic>? ?? data['items'] as List<dynamic>? ?? []);
    final name = batch['batch_name']?.toString() ?? 'Batch $batchId';
    final status = batch['status']?.toString() ?? '-';
    final total = (batch['total_amount'] as num?)?.toDouble() ?? 0;
    final canExport = status == 'approved' || status == 'processing' || status == 'completed';

    return RefreshIndicator(
      onRefresh: () async => ref.invalidate(_detailProvider(batchId)),
      color: AppTheme.primaryColor,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          GlassCard(
            opacity: 0.08,
            blur: 8,
            padding: const EdgeInsets.all(16),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(name, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16)),
              const SizedBox(height: 6),
              Row(children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(color: AppTheme.primaryColor.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(20)),
                  child: Text(status.replaceAll('_', ' '), style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 12, color: AppTheme.primaryColor)),
                ),
                const SizedBox(width: 8),
                Text('₹${NumberFormat('#,##,###').format(total)}', style: const TextStyle(fontWeight: FontWeight.w700)),
              ]),
              if (canExport) ...[
                const SizedBox(height: 12),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: () => _exportCsv(context),
                    icon: const Icon(Icons.file_download),
                    label: const Text('Export Bank CSV'),
                    style: ElevatedButton.styleFrom(backgroundColor: AppTheme.primaryColor, foregroundColor: Colors.white),
                  ),
                ),
              ],
            ]),
          ),
          const SizedBox(height: 16),
          Text('Entries (${entries.length})', style: const TextStyle(fontWeight: FontWeight.w700)),
          const SizedBox(height: 8),
          if (entries.isEmpty)
            GlassCard(
              opacity: 0.06,
              blur: 6,
              padding: const EdgeInsets.all(16),
              child: Text('No entries in this batch.', style: TextStyle(color: Colors.grey.shade600)),
            )
          else
            for (final e in entries)
              Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: _entryCard(e as Map<String, dynamic>),
              ),
        ],
      ),
    );
  }

  Widget _entryCard(Map<String, dynamic> e) {
    final name = e['beneficiary_name']?.toString() ?? e['user_name']?.toString() ?? 'Beneficiary ${e['beneficiary_user_id'] ?? ''}';
    final net = (e['net_amount'] as num?)?.toDouble() ?? 0;
    final st = e['status']?.toString() ?? 'pending';
    final utr = e['utr_number']?.toString() ?? e['payment_reference']?.toString() ?? '';
    return GlassCard(
      opacity: 0.06,
      blur: 6,
      padding: const EdgeInsets.all(12),
      child: Row(children: [
        Expanded(
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(name, style: const TextStyle(fontWeight: FontWeight.w600), maxLines: 1, overflow: TextOverflow.ellipsis),
            Text('Net ₹${NumberFormat('#,##,###').format(net)} • $st${utr.isNotEmpty ? ' • $utr' : ''}', style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
          ]),
        ),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
          decoration: BoxDecoration(color: st == 'completed' ? Colors.green.withValues(alpha: 0.12) : Colors.orange.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(20)),
          child: Text(st, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: st == 'completed' ? Colors.green : Colors.orange)),
        ),
      ]),
    );
  }

  Future<void> _exportCsv(BuildContext context) async {
    final url = ApiService().payoutBatchExportUrl(batchId, format: 'generic');
    final uri = Uri.parse(url);
    try {
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Open: $url')));
      }
    } catch (e) {
      if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Export failed: $e')));
    }
  }
}

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:intl/intl.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

final _demandLettersProvider = FutureProvider<List<dynamic>>((ref) async {
  final api = ApiService();
  AppConstants.initBaseUrl();
  try {
    return await api.getDemandLetters();
  } catch (_) {
    return [];
  }
});

class DemandLettersPage extends ConsumerWidget {
  const DemandLettersPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final async = ref.watch(_demandLettersProvider);
    return Scaffold(
      appBar: AppBar(
        title: const Text('Demand Letters'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
      ),
      body: async.when(
        data: (items) {
          if (items.isEmpty) return _empty(ref);
          return RefreshIndicator(
            onRefresh: () async => ref.invalidate(_demandLettersProvider),
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

  Widget _card(BuildContext context, Map<String, dynamic> d) {
    final id = d['id']?.toString() ?? '';
    final letter = d['letter_number']?.toString() ?? 'DL-$id';
    final booking = d['booking_number']?.toString() ?? '';
    final colony = d['colony_name']?.toString() ?? '';
    final amount = (d['amount'] as num?)?.toDouble() ?? 0;
    final status = d['status']?.toString() ?? 'drafted';
    final due = d['due_date']?.toString() ?? '';
    final overdue = (d['overdue_days_calc'] as num?)?.toInt() ?? 0;
    final map = {'paid': Colors.green, 'sent': Colors.blue, 'overdue': Colors.red, 'drafted': Colors.grey, 'viewed': Colors.teal};
    final color = map[status] ?? Colors.grey;
    final overdueLabel = status != 'paid' && overdue > 0 ? '$overdue d overdue' : (status != 'paid' && overdue == 0 ? 'Due today' : '');
    return GlassCard(
      opacity: 0.08,
      blur: 8,
      padding: const EdgeInsets.all(16),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          Container(width: 44, height: 44, decoration: BoxDecoration(color: AppTheme.primaryColor.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(12)), child: const Icon(Icons.request_page_rounded, color: AppTheme.primaryColor)),
          const SizedBox(width: 12),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(letter, style: const TextStyle(fontWeight: FontWeight.w700)),
            Text('$booking${colony.isNotEmpty ? ' • $colony' : ''}', style: TextStyle(fontSize: 12, color: Colors.grey.shade600), maxLines: 1, overflow: TextOverflow.ellipsis),
          ])),
          Container(padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4), decoration: BoxDecoration(color: color.withValues(alpha: 0.15), borderRadius: BorderRadius.circular(20)), child: Text(status.toUpperCase(), style: TextStyle(color: color, fontWeight: FontWeight.w700, fontSize: 11))),
        ]),
        const SizedBox(height: 10),
        Row(children: [
          Text('₹${NumberFormat('#,##,###').format(amount)}', style: const TextStyle(fontWeight: FontWeight.w700, color: AppTheme.primaryColor)),
          const Spacer(),
          if (overdueLabel.isNotEmpty) Container(padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4), decoration: BoxDecoration(color: Colors.red.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(8)), child: Text(overdueLabel, style: const TextStyle(fontSize: 11, color: Colors.red, fontWeight: FontWeight.w600))),
        ]),
        if (due.isNotEmpty) Padding(padding: const EdgeInsets.only(top: 6), child: Text('Due: $due', style: TextStyle(fontSize: 12, color: Colors.grey.shade600))),
        const SizedBox(height: 10),
        SizedBox(width: double.infinity, child: OutlinedButton.icon(onPressed: () async {
          final url = ApiService().demandLetterPdfUrl(id);
          final uri = Uri.parse(url);
          if (await canLaunchUrl(uri)) await launchUrl(uri, mode: LaunchMode.externalApplication);
        }, icon: const Icon(Icons.picture_as_pdf, size: 18), label: const Text('View PDF'))),
      ]),
    );
  }

  Widget _empty(WidgetRef ref) => Center(child: Padding(padding: const EdgeInsets.all(24), child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [Icon(Icons.mail_outline, size: 56, color: Colors.grey.shade400), const SizedBox(height: 12), const Text('No demand letters'), const SizedBox(height: 12), ElevatedButton(onPressed: () => ref.invalidate(_demandLettersProvider), child: const Text('Refresh'))])));
  Widget _error(WidgetRef ref, String e) => Center(child: Padding(padding: const EdgeInsets.all(24), child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [Text(e, textAlign: TextAlign.center), const SizedBox(height: 12), ElevatedButton(onPressed: () => ref.invalidate(_demandLettersProvider), child: const Text('Retry'))])));
}

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

final _coloniesProgressProvider = FutureProvider<List<dynamic>>((ref) async {
  final api = ApiService();
  AppConstants.initBaseUrl();
  try { return await api.getConstructionColonies(); } catch (_) { return []; }
});

class ColonyProgressPage extends ConsumerWidget {
  const ColonyProgressPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final async = ref.watch(_coloniesProgressProvider);
    return Scaffold(
      appBar: AppBar(title: const Text('Colony Progress'), backgroundColor: AppTheme.primaryColor, foregroundColor: Colors.white),
      body: async.when(
        data: (items) {
          if (items.isEmpty) return _empty(ref);
          return RefreshIndicator(onRefresh: () async => ref.invalidate(_coloniesProgressProvider), color: AppTheme.primaryColor, child: ListView.separated(padding: const EdgeInsets.all(16), itemCount: items.length, separatorBuilder: (_, _) => const SizedBox(height: 12), itemBuilder: (c, i) => _card(c, items[i] as Map<String, dynamic>)));
        },
        loading: () => const Center(child: CircularProgressIndicator(color: AppTheme.primaryColor)),
        error: (e, _) => _error(ref, e.toString()),
      ),
    );
  }

  Widget _card(BuildContext context, Map<String, dynamic> col) {
    final id = col['id']?.toString() ?? '';
    final name = col['name']?.toString() ?? 'Colony $id';
    final avg = (col['avg_progress'] as num?)?.toDouble() ?? 0;
    final total = (col['total_milestones'] as num?)?.toInt() ?? 0;
    final done = (col['completed_milestones'] as num?)?.toInt() ?? 0;
    final status = col['colony_status']?.toString() ?? '';
    return GlassCard(opacity: 0.08, blur: 8, padding: const EdgeInsets.all(16), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Row(children: [
        Container(width: 44, height: 44, decoration: BoxDecoration(color: Colors.orange.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(12)), child: const Icon(Icons.construction_rounded, color: Colors.orange)),
        const SizedBox(width: 12),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(name, style: const TextStyle(fontWeight: FontWeight.w700)),
          Text('$total milestones • $done done${status.isNotEmpty ? ' • $status' : ''}', style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
        ])),
        Text('${avg.toStringAsFixed(1)}%', style: const TextStyle(fontWeight: FontWeight.w700, color: AppTheme.primaryColor)),
      ]),
      const SizedBox(height: 10),
      ClipRRect(borderRadius: BorderRadius.circular(8), child: LinearProgressIndicator(value: (avg / 100).clamp(0, 1), minHeight: 8, backgroundColor: Colors.grey.shade200, valueColor: AlwaysStoppedAnimation(avg >= 100 ? Colors.green : (avg >= 50 ? Colors.blue : Colors.orange)))),
      const SizedBox(height: 8),
      Align(alignment: Alignment.centerRight, child: TextButton(onPressed: () => _openMilestones(context, id, name), child: const Text('View Milestones'))),
    ]));
  }

  void _openMilestones(BuildContext context, String colonyId, String name) {
    showModalBottomSheet(context: context, isScrollControlled: true, builder: (ctx) => _MilestonesSheet(colonyId: colonyId, colonyName: name));
  }

  Widget _empty(WidgetRef ref) => Center(child: Padding(padding: const EdgeInsets.all(24), child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [Icon(Icons.maps_home_work, size: 56, color: Colors.grey.shade400), const SizedBox(height: 12), const Text('No colonies'), const SizedBox(height: 12), ElevatedButton(onPressed: () => ref.invalidate(_coloniesProgressProvider), child: const Text('Refresh'))])));
  Widget _error(WidgetRef ref, String e) => Center(child: Padding(padding: const EdgeInsets.all(24), child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [Text(e, textAlign: TextAlign.center), const SizedBox(height: 12), ElevatedButton(onPressed: () => ref.invalidate(_coloniesProgressProvider), child: const Text('Retry'))])));
}

class _MilestonesSheet extends ConsumerWidget {
  final String colonyId;
  final String colonyName;
  const _MilestonesSheet({required this.colonyId, required this.colonyName});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final provider = FutureProvider<List<dynamic>>((ref2) async {
      final api = ApiService();
      return await api.getColonyMilestones(colonyId);
    });
    final async = ref.watch(provider);
    return DraggableScrollableSheet(expand: false, initialChildSize: 0.7, maxChildSize: 0.9, builder: (ctx, ctrl) => Container(decoration: BoxDecoration(color: Theme.of(context).scaffoldBackgroundColor, borderRadius: const BorderRadius.vertical(top: Radius.circular(16))), child: Column(children: [
      Padding(padding: const EdgeInsets.all(16), child: Row(children: [Expanded(child: Text(colonyName, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 16))), IconButton(onPressed: () => Navigator.pop(context), icon: const Icon(Icons.close))])) ,
      Expanded(child: async.when(data: (items) {
        if (items.isEmpty) return Center(child: Text('No milestones for $colonyName'));
        return ListView.separated(controller: ctrl, padding: const EdgeInsets.all(16), itemCount: items.length, separatorBuilder: (_, _) => const SizedBox(height: 10), itemBuilder: (c, i) {
          final m = items[i] as Map<String, dynamic>;
          final pct = (m['progress_pct'] as num?)?.toDouble() ?? 0;
          final cat = m['category']?.toString() ?? '';
          final st = m['status']?.toString() ?? '';
          final photo = m['site_photo_url']?.toString() ?? m['site_photo_path']?.toString() ?? '';
          return GlassCard(opacity: 0.06, blur: 6, padding: const EdgeInsets.all(12), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Row(children: [Expanded(child: Text(m['milestone_name']?.toString() ?? '', style: const TextStyle(fontWeight: FontWeight.w600))), Container(padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4), decoration: BoxDecoration(color: Colors.blue.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(20)), child: Text(cat, style: const TextStyle(fontSize: 11, color: Colors.blue)))]),
            const SizedBox(height: 6),
            Text('Status: $st • ${pct.toStringAsFixed(0)}%', style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
            const SizedBox(height: 6),
            ClipRRect(borderRadius: BorderRadius.circular(6), child: LinearProgressIndicator(value: (pct/100).clamp(0,1), minHeight: 6)),
            if (photo.isNotEmpty) Padding(padding: const EdgeInsets.only(top: 8), child: ClipRRect(borderRadius: BorderRadius.circular(8), child: Image.network(photo.startsWith('http') ? photo : '${AppConstants.baseUrl}/$photo', height: 120, width: double.infinity, fit: BoxFit.cover, errorBuilder: (_, __, ___) => const SizedBox()))),
          ]));
        });
      }, loading: () => const Center(child: CircularProgressIndicator()), error: (e, _) => Center(child: Text(e.toString())))),
    ])));
  }
}

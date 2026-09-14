import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

final _materialsProvider = FutureProvider<Map<String, dynamic>>((ref) async {
  final api = ApiService();
  AppConstants.initBaseUrl();
  try { return await api.getConstructionMaterials(); } catch (_) { return {'materials': [], 'usage_logs': []}; }
});

class MaterialInventoryPage extends ConsumerWidget {
  const MaterialInventoryPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final async = ref.watch(_materialsProvider);
    return Scaffold(
      appBar: AppBar(title: const Text('Material Inventory'), backgroundColor: AppTheme.primaryColor, foregroundColor: Colors.white),
      body: async.when(
        data: (data) {
          final mats = (data['materials'] as List?) ?? [];
          final logs = (data['usage_logs'] as List?) ?? [];
          final low = mats.where((m) => (m as Map)['status'] == 'low_stock' || m['status'] == 'out_of_stock').length;
          return RefreshIndicator(onRefresh: () async => ref.invalidate(_materialsProvider), color: AppTheme.primaryColor, child: ListView(padding: const EdgeInsets.all(16), children: [
            if (low > 0) Container(padding: const EdgeInsets.all(12), decoration: BoxDecoration(color: Colors.orange.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(12), border: Border.all(color: Colors.orange.withValues(alpha: 0.3))), child: Row(children: [const Icon(Icons.warning_amber_rounded, color: Colors.orange), const SizedBox(width: 8), Expanded(child: Text('$low material(s) low / out of stock — restock needed', style: const TextStyle(color: Colors.orange, fontWeight: FontWeight.w600))) ])),
            if (low > 0) const SizedBox(height: 12),
            Text('Materials (${mats.length})', style: const TextStyle(fontWeight: FontWeight.w700)),
            const SizedBox(height: 8),
            ...mats.map((e) => _matCard(e as Map<String, dynamic>)),
            const SizedBox(height: 16),
            Text('Recent Usage (${logs.length})', style: const TextStyle(fontWeight: FontWeight.w700)),
            const SizedBox(height: 8),
            ...logs.take(10).map((e) => _logCard(e as Map<String, dynamic>)),
            const SizedBox(height: 12),
            SizedBox(width: double.infinity, child: ElevatedButton.icon(onPressed: () => _showLogUsage(context, ref), icon: const Icon(Icons.add), label: const Text('Log Usage'))),
          ]));
        },
        loading: () => const Center(child: CircularProgressIndicator(color: AppTheme.primaryColor)),
        error: (e, _) => Center(child: Text(e.toString())),
      ),
    );
  }

  Widget _matCard(Map<String, dynamic> m) {
    final name = m['material_name']?.toString() ?? m['name']?.toString() ?? '';
    final cat = m['material_category']?.toString() ?? '';
    final stock = (m['current_stock'] as num?)?.toDouble() ?? 0;
    final status = m['status']?.toString() ?? 'in_stock';
    final unit = m['unit']?.toString() ?? 'qty';
    final color = status == 'in_stock' ? Colors.green : (status == 'low_stock' ? Colors.orange : Colors.red);
    return Padding(padding: const EdgeInsets.only(bottom: 10), child: GlassCard(opacity: 0.06, blur: 6, padding: const EdgeInsets.all(12), child: Row(children: [
      Container(width: 40, height: 40, decoration: BoxDecoration(color: color.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(10)), child: Icon(Icons.inventory_2, color: color, size: 20)),
      const SizedBox(width: 12),
      Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text(name, style: const TextStyle(fontWeight: FontWeight.w600)),
        Text('$cat • $stock $unit', style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
      ])),
      Container(padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4), decoration: BoxDecoration(color: color.withValues(alpha: 0.15), borderRadius: BorderRadius.circular(20)), child: Text(status.replaceAll('_', ' '), style: TextStyle(color: color, fontSize: 11, fontWeight: FontWeight.w700))),
    ])));
  }

  Widget _logCard(Map<String, dynamic> l) {
    final mat = l['material_name']?.toString() ?? '';
    final colony = l['colony_name']?.toString() ?? '—';
    final qty = (l['quantity'] as num?)?.toDouble() ?? 0;
    final date = l['usage_date']?.toString() ?? '';
    return Padding(padding: const EdgeInsets.only(bottom: 8), child: GlassCard(opacity: 0.04, blur: 4, padding: const EdgeInsets.all(12), child: Row(children: [
      Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text(mat, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
        Text('$colony • $qty', style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
      ])),
      Text(date.split(' ').first, style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
    ])));
  }

  void _showLogUsage(BuildContext context, WidgetRef ref) {
    final qtyCtrl = TextEditingController(text: '1');
    String? matId;
    showModalBottomSheet(context: context, isScrollControlled: true, builder: (ctx) => Padding(padding: EdgeInsets.only(bottom: MediaQuery.of(ctx).viewInsets.bottom), child: Container(padding: const EdgeInsets.all(16), child: Column(mainAxisSize: MainAxisSize.min, crossAxisAlignment: CrossAxisAlignment.start, children: [
      const Text('Log Material Usage', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 16)),
      const SizedBox(height: 12),
      TextField(decoration: const InputDecoration(labelText: 'Material ID *', border: OutlineInputBorder()), onChanged: (v) => matId = v),
      const SizedBox(height: 10),
      TextField(controller: qtyCtrl, decoration: const InputDecoration(labelText: 'Quantity *', border: OutlineInputBorder()), keyboardType: TextInputType.number),
      const SizedBox(height: 12),
      SizedBox(width: double.infinity, child: ElevatedButton(onPressed: () async {
        if (matId == null || matId!.isEmpty) return;
        try {
          await ApiService().logMaterialUsage({'material_id': int.parse(matId!), 'quantity': double.tryParse(qtyCtrl.text) ?? 1, 'unit': 'qty'});
          if (ctx.mounted) Navigator.pop(ctx);
          ref.invalidate(_materialsProvider);
          if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Usage logged')));
        } catch (e) { if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Failed: $e'))); }
      }, child: const Text('Submit'))),
    ]))));
  }
}

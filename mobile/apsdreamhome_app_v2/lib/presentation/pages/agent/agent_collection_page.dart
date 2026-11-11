import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

final agentCollectionsProvider = FutureProvider<Map<String, dynamic>>((ref) async {
  final api = ApiService();
  AppConstants.initBaseUrl();
  try {
    final res = await api.get('/api/v2/mobile/agent/collections');
    if (res['success'] == true) {
      return res['data'] as Map<String, dynamic>;
    }
  } catch (e) {
    debugPrint('Error loading agent collections: $e');
  }
  return {
    'today_collected': 0.0,
    'pending_verification_count': 0,
    'recent_collections': []
  };
});

class AgentCollectionPage extends ConsumerStatefulWidget {
  const AgentCollectionPage({super.key});

  @override
  ConsumerState<AgentCollectionPage> createState() => _AgentCollectionPageState();
}

class _AgentCollectionPageState extends ConsumerState<AgentCollectionPage> {
  void _openCollectionModal(BuildContext context) {
    final nameCtrl = TextEditingController();
    final phoneCtrl = TextEditingController();
    final amountCtrl = TextEditingController();
    final chequeCtrl = TextEditingController();
    final remarksCtrl = TextEditingController();
    String paymentMode = 'cash';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) => StatefulBuilder(
        builder: (modalCtx, setModalState) => Padding(
          padding: EdgeInsets.only(left: 20, right: 20, top: 20, bottom: MediaQuery.of(modalCtx).viewInsets.bottom + 20),
          child: SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Record Field Collection', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 17, color: Color(0xFF0B194C))),
                    IconButton(icon: const Icon(Icons.close), onPressed: () => Navigator.pop(modalCtx)),
                  ],
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: nameCtrl,
                  decoration: InputDecoration(labelText: 'Customer Full Name *', border: OutlineInputBorder(borderRadius: BorderRadius.circular(10))),
                ),
                const SizedBox(height: 10),
                TextField(
                  controller: phoneCtrl,
                  keyboardType: TextInputType.phone,
                  decoration: InputDecoration(labelText: 'Customer Mobile Number *', border: OutlineInputBorder(borderRadius: BorderRadius.circular(10))),
                ),
                const SizedBox(height: 10),
                TextField(
                  controller: amountCtrl,
                  keyboardType: TextInputType.number,
                  decoration: InputDecoration(labelText: 'Collection Amount (₹) *', border: OutlineInputBorder(borderRadius: BorderRadius.circular(10))),
                ),
                const SizedBox(height: 10),
                DropdownButtonFormField<String>(
                  value: paymentMode,
                  decoration: InputDecoration(labelText: 'Payment Mode', border: OutlineInputBorder(borderRadius: BorderRadius.circular(10))),
                  items: const [
                    DropdownMenuItem(value: 'cash', child: Text('Cash in Hand')),
                    DropdownMenuItem(value: 'cheque', child: Text('Bank Cheque')),
                    DropdownMenuItem(value: 'dd', child: Text('Demand Draft (DD)')),
                  ],
                  onChanged: (val) {
                    if (val != null) setModalState(() => paymentMode = val);
                  },
                ),
                if (paymentMode != 'cash') ...[
                  const SizedBox(height: 10),
                  TextField(
                    controller: chequeCtrl,
                    decoration: InputDecoration(labelText: 'Cheque / DD Number *', border: OutlineInputBorder(borderRadius: BorderRadius.circular(10))),
                  ),
                ],
                const SizedBox(height: 10),
                TextField(
                  controller: remarksCtrl,
                  decoration: InputDecoration(labelText: 'Plot / Colony / Remarks', border: OutlineInputBorder(borderRadius: BorderRadius.circular(10))),
                ),
                const SizedBox(height: 16),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF0B194C),
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      padding: const EdgeInsets.symmetric(vertical: 14),
                    ),
                    onPressed: () async {
                      final amount = double.tryParse(amountCtrl.text.trim()) ?? 0.0;
                      if (nameCtrl.text.trim().isEmpty || amount <= 0) {
                        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Please enter valid customer name and amount')));
                        return;
                      }
                      Navigator.pop(modalCtx);
                      try {
                        final api = ApiService();
                        final res = await api.post('/api/v2/mobile/agent/collections/create', data: {
                          'customer_name': nameCtrl.text.trim(),
                          'customer_phone': phoneCtrl.text.trim(),
                          'amount': amount,
                          'payment_mode': paymentMode,
                          'cheque_number': chequeCtrl.text.trim(),
                          'remarks': remarksCtrl.text.trim(),
                        });
                        if (mounted) {
                          final msg = ((res as Map<String, dynamic>?)?['message'] ?? 'Collection logged successfully!').toString();
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(content: Text(msg)),
                          );
                          ref.invalidate(agentCollectionsProvider);
                        }
                      } catch (e) {
                        if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                      }
                    },
                    child: const Text('Log Collection & Issue Receipt', style: TextStyle(fontWeight: FontWeight.w700)),
                  ),
                )
              ],
            ),
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final colAsync = ref.watch(agentCollectionsProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F0),
      appBar: AppBar(
        title: const Text('Field Collections Desk', style: TextStyle(fontWeight: FontWeight.w700, color: Color(0xFF0B194C))),
        backgroundColor: Colors.white,
        elevation: 0.5,
        iconTheme: const IconThemeData(color: Color(0xFF0B194C)),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.invalidate(agentCollectionsProvider),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        backgroundColor: const Color(0xFF0B194C),
        foregroundColor: const Color(0xFFD4AF37),
        icon: const Icon(Icons.add),
        label: const Text('New Collection', style: TextStyle(fontWeight: FontWeight.w700)),
        onPressed: () => _openCollectionModal(context),
      ),
      body: colAsync.when(
        data: (data) => _buildBody(context, data),
        loading: () => const Center(child: CircularProgressIndicator(color: AppTheme.primaryColor)),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }

  Widget _buildBody(BuildContext context, Map<String, dynamic> data) {
    final today = (data['today_collected'] as num?)?.toDouble() ?? 0.0;
    final pendingCount = data['pending_verification_count'] ?? 0;
    final collections = (data['recent_collections'] as List<dynamic>?) ?? [];

    return RefreshIndicator(
      onRefresh: () async => ref.invalidate(agentCollectionsProvider),
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Stat Summary Card
          GlassCard(
            padding: const EdgeInsets.all(20),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text("Today's Field Collection", style: TextStyle(color: Colors.grey, fontSize: 12)),
                    const SizedBox(height: 4),
                    Text('₹${today.toStringAsFixed(0)}', style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF0B194C), fontSize: 24)),
                  ],
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  decoration: BoxDecoration(color: Colors.orange.shade50, borderRadius: BorderRadius.circular(10), border: Border.all(color: Colors.orange.shade200)),
                  child: Column(
                    children: [
                      Text('$pendingCount', style: TextStyle(fontWeight: FontWeight.w800, color: Colors.orange.shade800, fontSize: 16)),
                      Text('Pending H.O. Audit', style: TextStyle(color: Colors.orange.shade800, fontSize: 10)),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),

          // Recent Collections Header
          const Text('Recent Collections History', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 16, color: Color(0xFF0B194C))),
          const SizedBox(height: 12),

          if (collections.isEmpty)
            Container(
              padding: const EdgeInsets.all(32),
              decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12)),
              child: Column(
                children: [
                  Icon(Icons.receipt_long, size: 48, color: Colors.grey.shade400),
                  const SizedBox(height: 12),
                  const Text('No collections recorded today', style: TextStyle(fontWeight: FontWeight.w600, color: Color(0xFF0B194C))),
                  const SizedBox(height: 6),
                  const Text('Record cash or cheque collections from customers to generate official receipts.', textAlign: TextAlign.center, style: TextStyle(color: Colors.grey, fontSize: 12)),
                ],
              ),
            )
          else
            ...collections.map((item) {
              final c = item as Map<String, dynamic>;
              final mode = (c['payment_mode'] ?? 'cash').toString().toUpperCase();
              final isCash = mode == 'CASH';

              return Container(
                margin: const EdgeInsets.only(bottom: 12),
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12), border: Border.all(color: Colors.grey.shade200)),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(c['customer_name']?.toString() ?? 'Customer', style: const TextStyle(fontWeight: FontWeight.w700, color: Color(0xFF0B194C), fontSize: 14)),
                        Text('Phone: ${c['customer_phone'] ?? '-'}', style: TextStyle(color: Colors.grey.shade600, fontSize: 12)),
                        Text('Receipt: ${c['receipt_number'] ?? '-'}', style: TextStyle(color: Colors.grey.shade500, fontSize: 11)),
                      ],
                    ),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Text('₹${(c['amount'] as num?)?.toDouble().toStringAsFixed(0) ?? '0'}', style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 16, color: Color(0xFF0B194C))),
                        const SizedBox(height: 4),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                          decoration: BoxDecoration(
                            color: isCash ? Colors.green.shade50 : Colors.blue.shade50,
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(mode, style: TextStyle(color: isCash ? Colors.green.shade700 : Colors.blue.shade700, fontSize: 10, fontWeight: FontWeight.w700)),
                        ),
                      ],
                    )
                  ],
                ),
              );
            }),
          const SizedBox(height: 60),
        ],
      ),
    );
  }
}

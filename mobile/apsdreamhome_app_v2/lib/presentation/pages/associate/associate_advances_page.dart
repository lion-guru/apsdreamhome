import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:apsdreamhome_app_v2/core/services/api_service.dart';

final associateAdvancesProvider = FutureProvider.autoDispose<Map<String, dynamic>>((ref) async {
  final api = ApiService();
  final res = await api.get('/api/v2/mobile/associate/advances');
  return res;
});

class AssociateAdvancesPage extends ConsumerStatefulWidget {
  const AssociateAdvancesPage({super.key});

  @override
  ConsumerState<AssociateAdvancesPage> createState() => _AssociateAdvancesPageState();
}

class _AssociateAdvancesPageState extends ConsumerState<AssociateAdvancesPage> {
  final _amountCtrl = TextEditingController();
  final _purposeCtrl = TextEditingController();
  int _tenure = 3;
  bool _submitting = false;

  @override
  void dispose() {
    _amountCtrl.dispose();
    _purposeCtrl.dispose();
    super.dispose();
  }

  void _showApplySheet(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (modalCtx) {
        return StatefulBuilder(
          builder: (ctx, setModalState) {
            return Container(
              padding: EdgeInsets.only(
                top: 24,
                left: 20,
                right: 20,
                bottom: MediaQuery.of(modalCtx).viewInsets.bottom + 24,
              ),
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
              ),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Center(
                      child: Container(
                        width: 48,
                        height: 4,
                        decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(2)),
                      ),
                    ),
                    const SizedBox(height: 16),
                    const Text('Request Commission Advance', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: Color(0xFF0B194C))),
                    const SizedBox(height: 6),
                    const Text('0% interest emergency fund recovered automatically from future commission payouts.', style: TextStyle(fontSize: 12, color: Colors.grey)),
                    const SizedBox(height: 16),
                    TextField(
                      controller: _amountCtrl,
                      keyboardType: TextInputType.number,
                      decoration: InputDecoration(
                        labelText: 'Advance Amount (₹)',
                        hintText: 'e.g. 25000',
                        prefixIcon: const Icon(Icons.currency_rupee, color: Color(0xFF0B194C)),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                    const SizedBox(height: 14),
                    const Text('Repayment Tenure', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
                    const SizedBox(height: 6),
                    Row(
                      children: [1, 2, 3, 6].map((m) {
                        final isSel = _tenure == m;
                        return Padding(
                          padding: const EdgeInsets.only(right: 8),
                          child: ChoiceChip(
                            label: Text('$m Months'),
                            selected: isSel,
                            selectedColor: const Color(0xFF0B194C),
                            labelStyle: TextStyle(color: isSel ? Colors.white : const Color(0xFF0B194C), fontWeight: FontWeight.w700),
                            onSelected: (val) {
                              if (val) setModalState(() => _tenure = m);
                            },
                          ),
                        );
                      }).toList(),
                    ),
                    const SizedBox(height: 14),
                    TextField(
                      controller: _purposeCtrl,
                      decoration: InputDecoration(
                        labelText: 'Purpose',
                        hintText: 'e.g. Medical / Festival / Family Event',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                    const SizedBox(height: 20),
                    SizedBox(
                      width: double.infinity,
                      height: 48,
                      child: ElevatedButton(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF0B194C),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        onPressed: _submitting
                            ? null
                            : () async {
                                final amt = double.tryParse(_amountCtrl.text.trim()) ?? 0.0;
                                if (amt <= 0 || amt > 100000) {
                                  ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Please enter an amount up to ₹1,00,000')));
                                  return;
                                }
                                Navigator.pop(modalCtx);
                                setState(() => _submitting = true);
                                try {
                                  final api = ApiService();
                                  final res = await api.post('/api/v2/mobile/associate/advances/apply', data: {
                                    'requested_amount': amt,
                                    'tenure_months': _tenure,
                                    'purpose': _purposeCtrl.text.trim(),
                                  });
                                  if (mounted) {
                                    final msg = ((res as Map<String, dynamic>?)?['message'] ?? 'Advance request submitted!').toString();
                                    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
                                    ref.invalidate(associateAdvancesProvider);
                                  }
                                } catch (e) {
                                  if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                                } finally {
                                  if (mounted) setState(() => _submitting = false);
                                }
                              },
                        child: const Text('Submit Advance Application', style: TextStyle(fontWeight: FontWeight.w700, color: Colors.white)),
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final advAsync = ref.watch(associateAdvancesProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F0),
      appBar: AppBar(
        title: const Text('Commission Advances & Loans', style: TextStyle(fontWeight: FontWeight.w700, color: Color(0xFF0B194C))),
        backgroundColor: Colors.white,
        elevation: 0.5,
        iconTheme: const IconThemeData(color: Color(0xFF0B194C)),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.invalidate(associateAdvancesProvider),
          ),
        ],
      ),
      body: advAsync.when(
        loading: () => const Center(child: CircularProgressIndicator(color: Color(0xFFD4AF37))),
        error: (err, _) => Center(child: Text('Error: $err')),
        data: (data) {
          final activeAdv = (data['active_advance'] as Map<String, dynamic>?);
          final advances = (data['advances'] as List<dynamic>?) ?? [];
          final rules = (data['rules'] as List<dynamic>?) ?? [];

          return RefreshIndicator(
            color: const Color(0xFFD4AF37),
            onRefresh: () async => ref.invalidate(associateAdvancesProvider),
            child: ListView(
              padding: const EdgeInsets.all(16),
              children: [
                // Hero Banner
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [Color(0xFF0B194C), Color(0xFF1E3A8A)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(18),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text('Emergency Welfare Advance', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 16)),
                              SizedBox(height: 4),
                              Text('Interest-Free (0% APR) for Associates', style: TextStyle(color: Color(0xFFD4AF37), fontSize: 12, fontWeight: FontWeight.w700)),
                            ],
                          ),
                          ElevatedButton.icon(
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFFD4AF37),
                              foregroundColor: const Color(0xFF0B194C),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                            ),
                            icon: const Icon(Icons.add_circle, size: 16),
                            label: const Text('Apply', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 12)),
                            onPressed: () => _showApplySheet(context),
                          ),
                        ],
                      ),
                      const Divider(color: Colors.white24, height: 28),
                      Row(
                        children: [
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text('Max Eligible Limit', style: TextStyle(color: Colors.white60, fontSize: 11)),
                                Text('₹${data['max_eligible_advance']?.toString() ?? '100000'}', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 16)),
                              ],
                            ),
                          ),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.end,
                              children: [
                                const Text('Total Recovered', style: TextStyle(color: Colors.white60, fontSize: 11)),
                                Text('₹${data['total_recovered']?.toString() ?? '0'}', style: const TextStyle(color: Color(0xFFD4AF37), fontWeight: FontWeight.w800, fontSize: 16)),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 20),

                // Active Loan Tracker
                if (activeAdv != null) ...[
                  const Text('Active Repayment Tracker', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: Color(0xFF0B194C))),
                  const SizedBox(height: 10),
                  Card(
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text('Disbursed: ₹${activeAdv['approved_amount']?.toString() ?? '0'}', style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14)),
                              Text('Tenure: ${activeAdv['tenure_months']?.toString() ?? '3'} Mo', style: const TextStyle(fontSize: 12, color: Colors.grey)),
                            ],
                          ),
                          const SizedBox(height: 10),
                          LinearProgressIndicator(
                            value: (((activeAdv['recovered_amount'] as num?)?.toDouble() ?? 0.0) /
                                    (((activeAdv['approved_amount'] as num?)?.toDouble() ?? 1.0))),
                            minHeight: 6,
                            backgroundColor: Colors.grey.shade100,
                            valueColor: const AlwaysStoppedAnimation<Color>(Color(0xFFD4AF37)),
                          ),
                          const SizedBox(height: 8),
                          Text('Recovered: ₹${activeAdv['recovered_amount']?.toString() ?? '0'} / ₹${activeAdv['approved_amount']?.toString() ?? '0'}', style: const TextStyle(fontSize: 12, color: Colors.grey)),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 20),
                ],

                // Advance History
                const Text('Advance History', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: Color(0xFF0B194C))),
                const SizedBox(height: 10),
                if (advances.isEmpty)
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: Colors.grey.shade200),
                    ),
                    child: const Text('No advance applications submitted yet.', style: TextStyle(color: Colors.grey, fontSize: 13)),
                  )
                else
                  ...advances.map((a) {
                    final item = a as Map<String, dynamic>;
                    return Card(
                      margin: const EdgeInsets.only(bottom: 10),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      child: ListTile(
                        title: Text('₹${item['requested_amount']?.toString() ?? '0'} - ${item['purpose']?.toString() ?? 'Advance'}', style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14)),
                        subtitle: Text('Status: ${(item['status']?.toString() ?? 'pending').toUpperCase()}', style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                        trailing: const Icon(Icons.chevron_right, color: Colors.grey),
                      ),
                    );
                  }),
                const SizedBox(height: 20),

                // Rules
                const Text('Advance Policy Guidelines', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: Color(0xFF0B194C))),
                const SizedBox(height: 10),
                ...rules.map((r) => Padding(
                      padding: const EdgeInsets.only(bottom: 8),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Icon(Icons.shield, color: Color(0xFFD4AF37), size: 16),
                          const SizedBox(width: 8),
                          Expanded(child: Text(r?.toString() ?? '', style: const TextStyle(fontSize: 12, height: 1.4))),
                        ],
                      ),
                    )),
              ],
            ),
          );
        },
      ),
    );
  }
}

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

final plotTransfersProvider = FutureProvider<Map<String, dynamic>>((ref) async {
  final api = ApiService();
  AppConstants.initBaseUrl();
  try {
    final res = await api.get('/api/v2/mobile/plot-transfers');
    if (res['success'] == true) {
      return res['data'] as Map<String, dynamic>;
    }
  } catch (e) {
    debugPrint('Error loading plot transfers: $e');
  }
  return {
    'transfers': [],
    'eligible_bookings': [],
    'transfer_fee_rules': 'Standard Transfer Fee: ₹25,000 + 18% GST per transfer request.'
  };
});

class PlotTransfersPage extends ConsumerStatefulWidget {
  const PlotTransfersPage({super.key});

  @override
  ConsumerState<PlotTransfersPage> createState() => _PlotTransfersPageState();
}

class _PlotTransfersPageState extends ConsumerState<PlotTransfersPage> {
  void _openApplyModal(BuildContext context, List<dynamic> eligibleBookings) {
    if (eligibleBookings.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('No eligible active plots found for ownership transfer.')),
      );
      return;
    }

    final nameCtrl = TextEditingController();
    final phoneCtrl = TextEditingController();
    final aadhaarCtrl = TextEditingController();
    final panCtrl = TextEditingController();
    final reasonCtrl = TextEditingController(text: 'Plot resale to family member');
    final amountCtrl = TextEditingController();
    int? selectedBookingId = (eligibleBookings.first as Map<String, dynamic>)['id'] as int?;

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
                    const Text('Apply for Plot Transfer (NOC)', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 17, color: Color(0xFF0B194C))),
                    IconButton(icon: const Icon(Icons.close), onPressed: () => Navigator.pop(modalCtx)),
                  ],
                ),
                const SizedBox(height: 12),
                const Text('Select Plot for Transfer:', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                const SizedBox(height: 6),
                DropdownButtonFormField<int>(
                  value: selectedBookingId,
                  decoration: InputDecoration(
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  ),
                  items: eligibleBookings.map((b) {
                    final item = b as Map<String, dynamic>;
                    return DropdownMenuItem<int>(
                      value: item['id'] as int?,
                      child: Text('Plot #${item['plot_number']} — ${item['colony_name']}', style: const TextStyle(fontSize: 13)),
                    );
                  }).toList(),
                  onChanged: (val) => setModalState(() => selectedBookingId = val),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: nameCtrl,
                  decoration: InputDecoration(labelText: 'New Buyer Full Name *', border: OutlineInputBorder(borderRadius: BorderRadius.circular(10))),
                ),
                const SizedBox(height: 10),
                TextField(
                  controller: phoneCtrl,
                  keyboardType: TextInputType.phone,
                  decoration: InputDecoration(labelText: 'New Buyer Mobile Number *', border: OutlineInputBorder(borderRadius: BorderRadius.circular(10))),
                ),
                const SizedBox(height: 10),
                Row(
                  children: [
                    Expanded(
                      child: TextField(
                        controller: aadhaarCtrl,
                        keyboardType: TextInputType.number,
                        decoration: InputDecoration(labelText: 'Aadhaar (12 digits)', border: OutlineInputBorder(borderRadius: BorderRadius.circular(10))),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: TextField(
                        controller: panCtrl,
                        textCapitalization: TextCapitalization.characters,
                        decoration: InputDecoration(labelText: 'PAN Card', border: OutlineInputBorder(borderRadius: BorderRadius.circular(10))),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                TextField(
                  controller: amountCtrl,
                  keyboardType: TextInputType.number,
                  decoration: InputDecoration(labelText: 'Agreed Resale Value (₹)', border: OutlineInputBorder(borderRadius: BorderRadius.circular(10))),
                ),
                const SizedBox(height: 10),
                TextField(
                  controller: reasonCtrl,
                  decoration: InputDecoration(labelText: 'Reason for Transfer', border: OutlineInputBorder(borderRadius: BorderRadius.circular(10))),
                ),
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(color: const Color(0xFFFAF7F0), borderRadius: BorderRadius.circular(8)),
                  child: const Text('ℹ️ Applicable transfer fee: ₹25,000 + 18% GST. Upon legal verification, the Transfer NOC will be generated.', style: TextStyle(fontSize: 11, color: Colors.black87)),
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
                      if (nameCtrl.text.isEmpty || phoneCtrl.text.isEmpty || selectedBookingId == null) {
                        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Please fill required fields')));
                        return;
                      }
                      Navigator.pop(modalCtx);
                      try {
                        final api = ApiService();
                        final res = await api.post('/api/v2/mobile/plot-transfers/apply', data: {
                          'booking_id': selectedBookingId,
                          'new_buyer_name': nameCtrl.text.trim(),
                          'new_buyer_phone': phoneCtrl.text.trim(),
                          'new_buyer_aadhaar': aadhaarCtrl.text.trim(),
                          'new_buyer_pan': panCtrl.text.trim(),
                          'transfer_reason': reasonCtrl.text.trim(),
                          'transfer_amount': double.tryParse(amountCtrl.text.trim()) ?? 0.0,
                        });
                        if (mounted) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(content: Text((res as Map<String, dynamic>?)?['message']?.toString() ?? 'Transfer application submitted!')),
                          );
                          ref.invalidate(plotTransfersProvider);
                        }
                      } catch (e) {
                        if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                      }
                    },
                    child: const Text('Submit Transfer Request', style: TextStyle(fontWeight: FontWeight.w700)),
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
    final transfersAsync = ref.watch(plotTransfersProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F0),
      appBar: AppBar(
        title: const Text('Plot Transfer & NOC Desk', style: TextStyle(fontWeight: FontWeight.w700, color: Color(0xFF0B194C))),
        backgroundColor: Colors.white,
        elevation: 0.5,
        iconTheme: const IconThemeData(color: Color(0xFF0B194C)),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.invalidate(plotTransfersProvider),
          ),
        ],
      ),
      floatingActionButton: transfersAsync.when(
        data: (data) => FloatingActionButton.extended(
          backgroundColor: const Color(0xFF0B194C),
          foregroundColor: const Color(0xFFD4AF37),
          icon: const Icon(Icons.add),
          label: const Text('New Transfer', style: TextStyle(fontWeight: FontWeight.w700)),
          onPressed: () => _openApplyModal(context, (data['eligible_bookings'] as List<dynamic>?) ?? []),
        ),
        loading: () => null,
        error: (_, __) => null,
      ),
      body: transfersAsync.when(
        data: (data) => _buildBody(context, data),
        loading: () => const Center(child: CircularProgressIndicator(color: AppTheme.primaryColor)),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }

  Widget _buildBody(BuildContext context, Map<String, dynamic> data) {
    final transfers = (data['transfers'] as List<dynamic>?) ?? [];
    final eligible = (data['eligible_bookings'] as List<dynamic>?) ?? [];

    return RefreshIndicator(
      onRefresh: () async => ref.invalidate(plotTransfersProvider),
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Informational Header
          GlassCard(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                const Icon(Icons.assignment_turned_in_outlined, color: Color(0xFFD4AF37), size: 36),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Official Ownership Transfer & NOC', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: Color(0xFF0B194C))),
                      const SizedBox(height: 4),
                      Text(data['transfer_fee_rules']?.toString() ?? '', style: TextStyle(color: Colors.grey.shade600, fontSize: 11)),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),

          // Requests List
          const Text('My Transfer Requests', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 16, color: Color(0xFF0B194C))),
          const SizedBox(height: 12),
          if (transfers.isEmpty)
            Container(
              padding: const EdgeInsets.all(32),
              decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12)),
              child: Column(
                children: [
                  Icon(Icons.swap_horiz, size: 48, color: Colors.grey.shade400),
                  const SizedBox(height: 12),
                  const Text('No plot transfer applications yet.', style: TextStyle(fontWeight: FontWeight.w600, color: Color(0xFF0B194C))),
                  const SizedBox(height: 6),
                  Text('You can transfer ownership of your verified plots to another buyer or family member.', textAlign: TextAlign.center, style: TextStyle(color: Colors.grey.shade600, fontSize: 12)),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF0B194C), foregroundColor: Colors.white),
                    onPressed: () => _openApplyModal(context, eligible),
                    child: const Text('Apply for Transfer'),
                  )
                ],
              ),
            )
          else
            ...transfers.map((item) {
              final t = item as Map<String, dynamic>;
              final status = (t['status'] ?? 'pending').toString().toLowerCase();
              final isApproved = status == 'approved' || status == 'completed';

              return Container(
                margin: const EdgeInsets.only(bottom: 12),
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12), border: Border.all(color: Colors.grey.shade200)),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text('Plot #${t['plot_number'] ?? '-'} (${t['colony_name'] ?? '-'})', style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15, color: Color(0xFF0B194C))),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: isApproved ? Colors.green.shade50 : Colors.amber.shade50,
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(color: isApproved ? Colors.green.shade200 : Colors.amber.shade200),
                          ),
                          child: Text(
                            status.toUpperCase(),
                            style: TextStyle(color: isApproved ? Colors.green.shade700 : Colors.amber.shade800, fontSize: 10, fontWeight: FontWeight.w700),
                          ),
                        )
                      ],
                    ),
                    const SizedBox(height: 8),
                    Text('New Transferee: ${t['new_buyer_name'] ?? '-'} (${t['new_buyer_phone'] ?? '-'})', style: TextStyle(color: Colors.grey.shade700, fontSize: 13, fontWeight: FontWeight.w500)),
                    Text('Reason: ${t['transfer_reason'] ?? '-'}', style: TextStyle(color: Colors.grey.shade600, fontSize: 12)),
                    const Divider(height: 20),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text('Fee: ₹${t['transfer_fee'] ?? '25,000'}', style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 12, color: Color(0xFF0B194C))),
                        if (isApproved && t['noc_number'] != null)
                          Text('NOC: ${t['noc_number']}', style: TextStyle(color: Colors.green.shade700, fontWeight: FontWeight.w700, fontSize: 12))
                        else
                          const Text('Legal Verification Underway', style: TextStyle(color: Colors.grey, fontSize: 11)),
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

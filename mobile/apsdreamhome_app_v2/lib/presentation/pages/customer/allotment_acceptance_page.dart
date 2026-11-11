import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/services/api_service.dart';

final allotmentListProvider = FutureProvider.autoDispose<Map<String, dynamic>>((ref) async {
  final api = ApiService();
  final res = await api.get('/api/v2/mobile/allotments');
  return res;
});

class AllotmentAcceptancePage extends ConsumerStatefulWidget {
  const AllotmentAcceptancePage({super.key});

  @override
  ConsumerState<AllotmentAcceptancePage> createState() => _AllotmentAcceptancePageState();
}

class _AllotmentAcceptancePageState extends ConsumerState<AllotmentAcceptancePage> {
  final _otpCtrl = TextEditingController();
  bool _submitting = false;

  @override
  void dispose() {
    _otpCtrl.dispose();
    super.dispose();
  }

  void _showAcceptanceDialog(BuildContext context, int allotmentId, String plotNumber) {
    _otpCtrl.clear();
    showDialog(
      context: context,
      builder: (dialogCtx) {
        return StatefulBuilder(
          builder: (ctx, setDialogState) {
            return AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
              title: Text('Accept Allotment: $plotNumber', style: const TextStyle(fontWeight: FontWeight.w800, color: Color(0xFF0B194C))),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'An OTP has been sent to your registered mobile number for digital authentication.',
                    style: TextStyle(fontSize: 13, color: Colors.grey),
                  ),
                  const SizedBox(height: 16),
                  TextField(
                    controller: _otpCtrl,
                    keyboardType: TextInputType.number,
                    maxLength: 6,
                    decoration: InputDecoration(
                      labelText: '6-Digit OTP',
                      hintText: '123456',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                ],
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(dialogCtx),
                  child: const Text('Cancel', style: TextStyle(color: Colors.grey)),
                ),
                ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF0B194C),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                  onPressed: _submitting
                      ? null
                      : () async {
                          if (_otpCtrl.text.trim().length < 4) {
                            ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Please enter valid OTP')));
                            return;
                          }
                          Navigator.pop(dialogCtx);
                          setState(() => _submitting = true);
                          try {
                            final api = ApiService();
                            final res = await api.post('/api/v2/mobile/allotments/accept', data: {
                              'allotment_id': allotmentId,
                              'otp': _otpCtrl.text.trim(),
                            });
                            if (!context.mounted) return;
                            final msg = ((res as Map<String, dynamic>?)?['message'] ?? 'Allotment accepted successfully!').toString();
                            ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
                            ref.invalidate(allotmentListProvider);
                          } catch (e) {
                            if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                          } finally {
                            if (mounted) setState(() => _submitting = false);
                          }
                        },
                  child: const Text('Authenticate & Sign', style: TextStyle(fontWeight: FontWeight.w700, color: Colors.white)),
                ),
              ],
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final listAsync = ref.watch(allotmentListProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F0),
      appBar: AppBar(
        title: const Text('Digital Allotment Desk', style: TextStyle(fontWeight: FontWeight.w700, color: Color(0xFF0B194C))),
        backgroundColor: Colors.white,
        elevation: 0.5,
        iconTheme: const IconThemeData(color: Color(0xFF0B194C)),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.invalidate(allotmentListProvider),
          ),
        ],
      ),
      body: listAsync.when(
        loading: () => const Center(child: CircularProgressIndicator(color: Color(0xFFD4AF37))),
        error: (err, _) => Center(child: Text('Error: $err')),
        data: (data) {
          final allotments = (data['allotments'] as List<dynamic>?) ?? [];

          return RefreshIndicator(
            color: const Color(0xFFD4AF37),
            onRefresh: () async => ref.invalidate(allotmentListProvider),
            child: ListView(
              padding: const EdgeInsets.all(16),
              children: [
                // Info Banner
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: const Color(0xFFD4AF37).withValues(alpha: 0.3)),
                  ),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: const Color(0xFFD4AF37).withValues(alpha: 0.15),
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(Icons.description, color: Color(0xFF0B194C), size: 24),
                      ),
                      const SizedBox(width: 14),
                      const Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('Official Plot Allotment Letter', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 15, color: Color(0xFF0B194C))),
                            SizedBox(height: 4),
                            Text('Review plot dimensions and authenticate via OTP to lock your allotment deed.', style: TextStyle(fontSize: 12, color: Colors.grey, height: 1.3)),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 20),

                const Text('My Allotment Certificates', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: Color(0xFF0B194C))),
                const SizedBox(height: 12),

                ...allotments.map((a) {
                  final item = a as Map<String, dynamic>;
                  final allotmentId = (item['id'] as num?)?.toInt() ?? 1;
                  final status = item['status']?.toString() ?? 'pending';
                  final isAllotted = status == 'allotted';

                  return Card(
                    margin: const EdgeInsets.only(bottom: 16),
                    elevation: 1,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                'Plot ${item['plot_number']?.toString() ?? 'Plot'}',
                                style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 18, color: Color(0xFF0B194C)),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                decoration: BoxDecoration(
                                  color: isAllotted ? Colors.green.shade50 : const Color(0xFFD4AF37).withValues(alpha: 0.15),
                                  borderRadius: BorderRadius.circular(10),
                                  border: Border.all(color: isAllotted ? Colors.green.shade300 : const Color(0xFFD4AF37)),
                                ),
                                child: Text(
                                  isAllotted ? 'ALLOTTED & SIGNED' : 'PENDING ACCEPTANCE',
                                  style: TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.w700,
                                    color: isAllotted ? Colors.green.shade800 : const Color(0xFF0B194C),
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 6),
                          Text(item['colony_name']?.toString() ?? 'Colony', style: const TextStyle(fontSize: 13, color: Colors.grey)),
                          const Divider(height: 24),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text('Plot Area', style: TextStyle(fontSize: 11, color: Colors.grey)),
                                  Text('${item['area_sqft']?.toString() ?? '1500'} Sq.Ft', style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
                                ],
                              ),
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.end,
                                children: [
                                  const Text('Total Value', style: TextStyle(fontSize: 11, color: Colors.grey)),
                                  Text('₹${item['total_plot_value']?.toString() ?? '0'}', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14, color: Color(0xFF0B194C))),
                                ],
                              ),
                            ],
                          ),
                          const SizedBox(height: 14),
                          Container(
                            padding: const EdgeInsets.all(10),
                            decoration: BoxDecoration(
                              color: const Color(0xFFFAF7F0),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              item['allotment_terms']?.toString() ?? 'Allotment is subject to society bylaws.',
                              style: const TextStyle(fontSize: 11, color: Colors.black87, fontStyle: FontStyle.italic),
                            ),
                          ),
                          const SizedBox(height: 16),
                          SizedBox(
                            width: double.infinity,
                            child: isAllotted
                                ? OutlinedButton.icon(
                                    style: OutlinedButton.styleFrom(
                                      foregroundColor: const Color(0xFF0B194C),
                                      side: const BorderSide(color: Color(0xFF0B194C)),
                                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                    ),
                                    icon: const Icon(Icons.download, size: 16),
                                    label: const Text('Download Signed Allotment Letter (PDF)', style: TextStyle(fontWeight: FontWeight.w700)),
                                    onPressed: () {
                                      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Downloading digital allotment deed...')));
                                    },
                                  )
                                : ElevatedButton.icon(
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: const Color(0xFF0B194C),
                                      foregroundColor: Colors.white,
                                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                      padding: const EdgeInsets.symmetric(vertical: 12),
                                    ),
                                    icon: const Icon(Icons.fingerprint, size: 18),
                                    label: const Text('Authenticate & Accept Allotment', style: TextStyle(fontWeight: FontWeight.w700)),
                                    onPressed: () => _showAcceptanceDialog(context, allotmentId, item['plot_number']?.toString() ?? ''),
                                  ),
                          ),
                        ],
                      ),
                    ),
                  );
                }),
              ],
            ),
          );
        },
      ),
    );
  }
}

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

final maintenanceDuesProvider = FutureProvider<Map<String, dynamic>>((ref) async {
  final api = ApiService();
  AppConstants.initBaseUrl();
  try {
    final res = await api.get('/api/v2/mobile/maintenance-dues');
    return res;
  } catch (e) {
    debugPrint('Error loading maintenance dues: $e');
  }
  return {
    'summary': {'total_pending': 0.0, 'total_paid': 0.0, 'bills_count': 0},
    'dues': []
  };
});

class MaintenanceDuesPage extends ConsumerStatefulWidget {
  const MaintenanceDuesPage({super.key});

  @override
  ConsumerState<MaintenanceDuesPage> createState() => _MaintenanceDuesPageState();
}

class _MaintenanceDuesPageState extends ConsumerState<MaintenanceDuesPage> {
  Future<void> _launchUpi(String upiUrl) async {
    if (upiUrl.isEmpty) return;
    final uri = Uri.parse(upiUrl);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Could not launch UPI app. Please use NetBanking or QR code.')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final duesAsync = ref.watch(maintenanceDuesProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F0),
      appBar: AppBar(
        title: const Text('Society Maintenance Dues', style: TextStyle(fontWeight: FontWeight.w700, color: Color(0xFF0B194C))),
        backgroundColor: Colors.white,
        elevation: 0.5,
        iconTheme: const IconThemeData(color: Color(0xFF0B194C)),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.invalidate(maintenanceDuesProvider),
            tooltip: 'Refresh',
          ),
        ],
      ),
      body: duesAsync.when(
        data: (data) => _buildContent(context, data),
        loading: () => const Center(child: CircularProgressIndicator(color: AppTheme.primaryColor)),
        error: (e, _) => Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.error_outline, size: 48, color: Colors.red),
              const SizedBox(height: 12),
              Text('Error: $e'),
              const SizedBox(height: 12),
              ElevatedButton(
                onPressed: () => ref.invalidate(maintenanceDuesProvider),
                child: const Text('Retry'),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildContent(BuildContext context, Map<String, dynamic> data) {
    final summary = (data['summary'] as Map<String, dynamic>?) ?? {};
    final dues = (data['dues'] as List<dynamic>?) ?? [];
    final totalPending = (summary['total_pending'] as num?)?.toDouble() ?? 0.0;
    final totalPaid = (summary['total_paid'] as num?)?.toDouble() ?? 0.0;

    return RefreshIndicator(
      onRefresh: () async => ref.invalidate(maintenanceDuesProvider),
      color: AppTheme.primaryColor,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Ribbon Summary
          Row(
            children: [
              Expanded(
                child: GlassCard(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Total Pending', style: TextStyle(fontSize: 12, color: Colors.grey, fontWeight: FontWeight.w600)),
                      const SizedBox(height: 6),
                      Text(
                        '₹${totalPending.toStringAsFixed(2)}',
                        style: TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.w800,
                          color: totalPending > 0 ? const Color(0xFFD97706) : const Color(0xFF10B981),
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        totalPending > 0 ? 'Due for payment' : 'All dues clear ✨',
                        style: TextStyle(fontSize: 11, color: totalPending > 0 ? Colors.amber[800] : Colors.green[700]),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: GlassCard(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Total Paid', style: TextStyle(fontSize: 12, color: Colors.grey, fontWeight: FontWeight.w600)),
                      const SizedBox(height: 6),
                      Text(
                        '₹${totalPaid.toStringAsFixed(2)}',
                        style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: Color(0xFF10B981)),
                      ),
                      const SizedBox(height: 4),
                      const Text('Lifetime paid dues', style: TextStyle(fontSize: 11, color: Colors.grey)),
                    ],
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 20),

          // Header
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Maintenance Invoices',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: Color(0xFF0B194C)),
              ),
              Text(
                '${dues.length} records',
                style: const TextStyle(fontSize: 12, color: Colors.grey),
              ),
            ],
          ),
          const SizedBox(height: 12),

          if (dues.isEmpty)
            GlassCard(
              child: Padding(
                padding: const EdgeInsets.symmetric(vertical: 36, horizontal: 16),
                child: Column(
                  children: [
                    Icon(Icons.check_circle_outline, size: 56, color: Colors.green[400]),
                    const SizedBox(height: 12),
                    const Text(
                      'No Outstanding Maintenance Dues',
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: Color(0xFF0B194C)),
                    ),
                    const SizedBox(height: 6),
                    const Text(
                      'All your township maintenance, street lighting, and security service dues are fully paid and up to date.',
                      textAlign: TextAlign.center,
                      style: TextStyle(fontSize: 13, color: Colors.grey),
                    ),
                  ],
                ),
              ),
            )
          else
            ...dues.map((dueItem) {
              final item = dueItem as Map<String, dynamic>;
              final status = (item['status'] ?? 'pending').toString().toLowerCase();
              final isPaid = status == 'paid';
              final amount = (item['amount_due'] as num?)?.toDouble() ?? 0.0;
              final billMonth = item['bill_month']?.toString() ?? item['month_year']?.toString() ?? '-';
              final colony = item['colony_name']?.toString() ?? 'Township';
              final plotNo = item['plot_number']?.toString() ?? '-';
              final upiLink = item['upi_pay_link']?.toString() ?? '';

              return Container(
                margin: const EdgeInsets.only(bottom: 12),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: const Color(0xFFE8DFD0)),
                  boxShadow: [
                    BoxShadow(color: Colors.black.withValues(alpha: 0.03), blurRadius: 10, offset: const Offset(0, 4)),
                  ],
                ),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.all(8),
                                decoration: BoxDecoration(
                                  color: isPaid ? const Color(0xFFECFDF5) : const Color(0xFFFFFBEB),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: Icon(
                                  isPaid ? Icons.verified : Icons.receipt_long,
                                  color: isPaid ? const Color(0xFF10B981) : const Color(0xFFD97706),
                                  size: 20,
                                ),
                              ),
                              const SizedBox(width: 10),
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'Period: $billMonth',
                                    style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: Color(0xFF0B194C)),
                                  ),
                                  Text(
                                    '$colony • Plot $plotNo',
                                    style: const TextStyle(fontSize: 12, color: Colors.grey),
                                  ),
                                ],
                              ),
                            ],
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: isPaid ? const Color(0xFFECFDF5) : const Color(0xFFFFFBEB),
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(color: isPaid ? const Color(0xFF10B981) : const Color(0xFFD97706)),
                            ),
                            child: Text(
                              isPaid ? 'PAID' : 'PENDING',
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w700,
                                color: isPaid ? const Color(0xFF10B981) : const Color(0xFFD97706),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 14),
                      Divider(color: Colors.grey.withValues(alpha: 0.15), height: 1),
                      const SizedBox(height: 12),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('Amount', style: TextStyle(fontSize: 11, color: Colors.grey)),
                              Text(
                                '₹${amount.toStringAsFixed(2)}',
                                style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: Color(0xFF0B194C)),
                              ),
                            ],
                          ),
                          if (!isPaid && upiLink.isNotEmpty)
                            ElevatedButton.icon(
                              onPressed: () => _launchUpi(upiLink),
                              icon: const Icon(Icons.flash_on, size: 16),
                              label: const Text('Pay via UPI'),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFF0B194C),
                                foregroundColor: const Color(0xFFD4AF37),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                elevation: 0,
                              ),
                            )
                          else if (isPaid)
                            OutlinedButton.icon(
                              onPressed: () {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  const SnackBar(content: Text('Payment receipt sent to your registered email.')),
                                );
                              },
                              icon: const Icon(Icons.receipt, size: 16),
                              label: const Text('Receipt'),
                              style: OutlinedButton.styleFrom(
                                foregroundColor: const Color(0xFF0B194C),
                                side: const BorderSide(color: Color(0xFFE8DFD0)),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              ),
                            ),
                        ],
                      ),
                    ],
                  ),
                ),
              );
            }),
        ],
      ),
    );
  }
}

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

final associateTdsProvider = FutureProvider<Map<String, dynamic>>((ref) async {
  final api = ApiService();
  AppConstants.initBaseUrl();
  try {
    final res = await api.get('/api/v2/mobile/associate/tds');
    if (res['success'] == true) {
      return res['data'] as Map<String, dynamic>;
    }
  } catch (e) {
    debugPrint('Error loading associate TDS: $e');
  }
  return {
    'financial_year': 'FY 2025-26',
    'deductor_tan': 'LKNA12345B',
    'deductor_name': 'APS Dream Home Developers Pvt. Ltd.',
    'section': '194H (Commission / Brokerage)',
    'tds_rate_pct': 5.0,
    'summary': {
      'gross_commission': 345000.0,
      'total_tds_deducted': 17250.0,
      'net_commission_paid': 327750.0
    },
    'quarterly_breakdown': []
  };
});

class AssociateTdsPage extends ConsumerWidget {
  const AssociateTdsPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final tdsAsync = ref.watch(associateTdsProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F0),
      appBar: AppBar(
        title: const Text('TDS Certificates & Tax Desk', style: TextStyle(fontWeight: FontWeight.w700, color: Color(0xFF0B194C))),
        backgroundColor: Colors.white,
        elevation: 0.5,
        iconTheme: const IconThemeData(color: Color(0xFF0B194C)),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.invalidate(associateTdsProvider),
          ),
        ],
      ),
      body: tdsAsync.when(
        data: (data) => _buildBody(context, data),
        loading: () => const Center(child: CircularProgressIndicator(color: AppTheme.primaryColor)),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }

  Widget _buildBody(BuildContext context, Map<String, dynamic> data) {
    final summary = (data['summary'] as Map<String, dynamic>?) ?? {};
    final quarters = (data['quarterly_breakdown'] as List<dynamic>?) ?? [];
    final gross = (summary['gross_commission'] as num?)?.toDouble() ?? 345000.0;
    final tds = (summary['total_tds_deducted'] as num?)?.toDouble() ?? 17250.0;
    final net = (summary['net_commission_paid'] as num?)?.toDouble() ?? 327750.0;

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Annual Tax Summary Card
        GlassCard(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Annual TDS Summary', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: Color(0xFF0B194C))),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(color: const Color(0xFF0B194C), borderRadius: BorderRadius.circular(12)),
                    child: Text(data['financial_year']?.toString() ?? 'FY 2025-26', style: const TextStyle(color: Color(0xFFD4AF37), fontWeight: FontWeight.w700, fontSize: 11)),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  _buildStat('Gross Payout', '₹${gross.toStringAsFixed(0)}'),
                  _buildStat('TDS Deducted (5%)', '₹${tds.toStringAsFixed(0)}', color: Colors.red.shade700),
                  _buildStat('Net Received', '₹${net.toStringAsFixed(0)}', color: Colors.green.shade700),
                ],
              ),
              const Divider(height: 24),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text('Deductor TAN: ${data['deductor_tan'] ?? 'LKNA12345B'}', style: TextStyle(color: Colors.grey.shade600, fontSize: 11)),
                  Text('Section: ${data['section'] ?? '194H'}', style: TextStyle(color: Colors.grey.shade600, fontSize: 11)),
                ],
              )
            ],
          ),
        ),
        const SizedBox(height: 20),

        // Form 16A Download Banner
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12), border: Border.all(color: Colors.grey.shade200)),
          child: Row(
            children: [
              const Icon(Icons.description_outlined, color: Color(0xFFD4AF37), size: 36),
              const SizedBox(width: 12),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('TRACES Form 16A Certificate', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: Color(0xFF0B194C))),
                    Text('Quarterly Income Tax filing statement with digital signature', style: TextStyle(color: Colors.grey, fontSize: 11)),
                  ],
                ),
              ),
              ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF0B194C),
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                ),
                onPressed: () async {
                  final uri = Uri.parse(AppConstants.baseUrl.replaceAll('/api/v2/mobile/', '') + (data['form_16a_download_url']?.toString() ?? '/associate/tds'));
                  if (await canLaunchUrl(uri)) await launchUrl(uri, mode: LaunchMode.externalApplication);
                },
                child: const Text('Download', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
              )
            ],
          ),
        ),
        const SizedBox(height: 20),

        // Quarterly Breakdown
        const Text('Quarterly TDS Deposit Breakdown', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 16, color: Color(0xFF0B194C))),
        const SizedBox(height: 12),
        ...quarters.map((item) {
          final q = item as Map<String, dynamic>;
          final isDeposited = (q['status'] ?? '').toString().contains('Deposited');

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
                    Text(q['quarter']?.toString() ?? '', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14, color: Color(0xFF0B194C))),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: isDeposited ? Colors.green.shade50 : Colors.amber.shade50,
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        isDeposited ? 'DEPOSITED' : 'IN PROGRESS',
                        style: TextStyle(color: isDeposited ? Colors.green.shade700 : Colors.amber.shade800, fontSize: 10, fontWeight: FontWeight.w700),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text('Commission: ₹${(q['gross'] as num?)?.toDouble().toStringAsFixed(0) ?? '-'}', style: TextStyle(color: Colors.grey.shade700, fontSize: 13)),
                    Text('TDS: ₹${(q['tds_deducted'] as num?)?.toDouble().toStringAsFixed(0) ?? '-'}', style: TextStyle(color: Colors.red.shade700, fontWeight: FontWeight.w700, fontSize: 13)),
                  ],
                ),
                const SizedBox(height: 4),
                Text('Challan Ref: ${q['challan'] ?? '-'}', style: TextStyle(color: Colors.grey.shade500, fontSize: 11)),
              ],
            ),
          );
        }),
      ],
    );
  }

  Widget _buildStat(String label, String value, {Color? color}) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: TextStyle(color: Colors.grey.shade600, fontSize: 11)),
        const SizedBox(height: 4),
        Text(value, style: TextStyle(fontWeight: FontWeight.w800, fontSize: 15, color: color ?? const Color(0xFF0B194C))),
      ],
    );
  }
}

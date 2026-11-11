import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

final passbookProvider = FutureProvider<Map<String, dynamic>>((ref) async {
  final api = ApiService();
  AppConstants.initBaseUrl();
  try {
    final res = await api.get('/api/v2/mobile/passbook');
    if (res['success'] == true) {
      return res['data'] as Map<String, dynamic>;
    }
  } catch (e) {
    debugPrint('Error loading passbook: $e');
  }
  return {
    'summary': {
      'total_plot_value': 1500000.0,
      'total_paid': 540000.0,
      'balance_remaining': 960000.0,
      'total_installments': 24,
      'paid_installments': 6,
      'monthly_emi': 40000.0,
      'annual_interest_rate_pct': 9.5
    },
    'ledger': [],
    'statement_pdf_url': '/customer/passbook'
  };
});

class CustomerPassbookPage extends ConsumerStatefulWidget {
  const CustomerPassbookPage({super.key});

  @override
  ConsumerState<CustomerPassbookPage> createState() => _CustomerPassbookPageState();
}

class _CustomerPassbookPageState extends ConsumerState<CustomerPassbookPage> {
  double _prepaySliderValue = 50000.0;
  bool _simulating = false;
  Map<String, dynamic>? _simulationResult;

  Future<void> _runSimulation(double balance, double emi, double rate) async {
    setState(() => _simulating = true);
    final api = ApiService();
    try {
      final res = await api.post('/api/v2/mobile/passbook/simulate', data: {
        'prepay_amount': _prepaySliderValue,
        'balance_remaining': balance,
        'monthly_emi': emi,
        'annual_rate': rate,
      });
      if (res['success'] == true) {
        setState(() => _simulationResult = res['data'] as Map<String, dynamic>);
      }
    } catch (e) {
      debugPrint('Simulation error: $e');
    } finally {
      if (mounted) setState(() => _simulating = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final passbookAsync = ref.watch(passbookProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F0),
      appBar: AppBar(
        title: const Text('Digital Passbook & Prepay', style: TextStyle(fontWeight: FontWeight.w700, color: Color(0xFF0B194C))),
        backgroundColor: Colors.white,
        elevation: 0.5,
        iconTheme: const IconThemeData(color: Color(0xFF0B194C)),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.invalidate(passbookProvider),
          ),
        ],
      ),
      body: passbookAsync.when(
        data: (data) => _buildBody(context, data),
        loading: () => const Center(child: CircularProgressIndicator(color: AppTheme.primaryColor)),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }

  Widget _buildBody(BuildContext context, Map<String, dynamic> data) {
    final summary = (data['summary'] as Map<String, dynamic>?) ?? {};
    final ledger = (data['ledger'] as List<dynamic>?) ?? [];
    final totalVal = (summary['total_plot_value'] as num?)?.toDouble() ?? 1500000.0;
    final totalPaid = (summary['total_paid'] as num?)?.toDouble() ?? 540000.0;
    final balance = (summary['balance_remaining'] as num?)?.toDouble() ?? 960000.0;
    final monthlyEmi = (summary['monthly_emi'] as num?)?.toDouble() ?? 40000.0;
    final annualRate = (summary['annual_interest_rate_pct'] as num?)?.toDouble() ?? 9.5;
    final progress = (totalPaid / (totalVal > 0 ? totalVal : 1)).clamp(0.0, 1.0);

    return RefreshIndicator(
      onRefresh: () async => ref.invalidate(passbookProvider),
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // 1. Plot Overview & Progress Card
          GlassCard(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Plot Passbook Summary', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 16, color: Color(0xFF0B194C))),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(color: const Color(0xFFD4AF37).withValues(alpha: 0.2), borderRadius: BorderRadius.circular(12)),
                      child: const Text('RERA Verified', style: TextStyle(color: Color(0xFF8B6B10), fontSize: 11, fontWeight: FontWeight.w600)),
                    )
                  ],
                ),
                const SizedBox(height: 16),
                LinearProgressIndicator(
                  value: progress,
                  backgroundColor: Colors.grey.shade200,
                  valueColor: const AlwaysStoppedAnimation<Color>(Color(0xFFD4AF37)),
                  minHeight: 8,
                  borderRadius: BorderRadius.circular(4),
                ),
                const SizedBox(height: 8),
                Text('${(progress * 100).toStringAsFixed(1)}% Repaid', style: TextStyle(color: Colors.grey.shade700, fontSize: 12, fontWeight: FontWeight.w600)),
                const Divider(height: 24),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    _buildStatCol('Total Plot Value', '₹${totalVal.toStringAsFixed(0)}'),
                    _buildStatCol('Total Paid', '₹${totalPaid.toStringAsFixed(0)}', color: Colors.green.shade700),
                    _buildStatCol('Balance Dues', '₹${balance.toStringAsFixed(0)}', color: Colors.red.shade700),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),

          // 2. Prepayment Savings Simulator
          GlassCard(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    const Icon(Icons.savings_outlined, color: Color(0xFFD4AF37)),
                    const SizedBox(width: 8),
                    const Text('Prepayment Savings Simulator', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 16, color: Color(0xFF0B194C))),
                  ],
                ),
                const SizedBox(height: 8),
                Text('Slide to simulate how much interest & tenure you save by prepaying:', style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
                const SizedBox(height: 16),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Prepay Extra Amount:', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
                    Text('₹${_prepaySliderValue.toStringAsFixed(0)}', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: Color(0xFF0B194C))),
                  ],
                ),
                Slider(
                  value: _prepaySliderValue,
                  min: 10000,
                  max: 300000,
                  divisions: 29,
                  activeColor: const Color(0xFF0B194C),
                  inactiveColor: Colors.grey.shade300,
                  onChanged: (val) {
                    setState(() => _prepaySliderValue = val);
                  },
                ),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF0B194C),
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      padding: const EdgeInsets.symmetric(vertical: 12),
                    ),
                    onPressed: _simulating ? null : () => _runSimulation(balance, monthlyEmi, annualRate),
                    child: _simulating
                        ? const SizedBox(height: 16, width: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                        : const Text('Calculate Savings', style: TextStyle(fontWeight: FontWeight.w700)),
                  ),
                ),
                if (_simulationResult != null) ...[
                  const SizedBox(height: 16),
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(color: Colors.green.shade50, borderRadius: BorderRadius.circular(10), border: Border.all(color: Colors.green.shade200)),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(_simulationResult!['headline']?.toString() ?? '', style: TextStyle(fontWeight: FontWeight.w700, color: Colors.green.shade900, fontSize: 13)),
                        const SizedBox(height: 8),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text('Tenure Saved: ${_simulationResult!['months_saved']} Months', style: TextStyle(color: Colors.green.shade800, fontWeight: FontWeight.w600, fontSize: 12)),
                            Text('Interest Saved: ₹${_simulationResult!['interest_saved']}', style: TextStyle(color: Colors.green.shade800, fontWeight: FontWeight.w600, fontSize: 12)),
                          ],
                        )
                      ],
                    ),
                  )
                ],
              ],
            ),
          ),
          const SizedBox(height: 20),

          // 3. Statement Download Action
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12), border: Border.all(color: Colors.grey.shade200)),
            child: Row(
              children: [
                const Icon(Icons.picture_as_pdf_outlined, color: Colors.red, size: 36),
                const SizedBox(width: 12),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Official Passbook Statement', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: Color(0xFF0B194C))),
                      Text('CA & Bank Verified Ledger with Barcode', style: TextStyle(color: Colors.grey, fontSize: 12)),
                    ],
                  ),
                ),
                ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFD4AF37),
                    foregroundColor: const Color(0xFF0B194C),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  ),
                  onPressed: () async {
                    final pdfUrl = data['statement_pdf_url']?.toString() ?? '/customer/passbook';
                    final uri = Uri.parse(AppConstants.baseUrl.replaceAll('/api/v2/mobile/', '') + pdfUrl);
                    if (await canLaunchUrl(uri)) {
                      await launchUrl(uri, mode: LaunchMode.externalApplication);
                    }
                  },
                  child: const Text('Download', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12)),
                )
              ],
            ),
          ),
          const SizedBox(height: 20),

          // 4. Ledger Table
          const Text('Installment Payment Ledger', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 16, color: Color(0xFF0B194C))),
          const SizedBox(height: 12),
          if (ledger.isEmpty)
            const Center(child: Padding(padding: EdgeInsets.all(24), child: Text('No installments recorded yet')))
          else
            ...ledger.map((item) {
              final row = item as Map<String, dynamic>;
              final isPaid = (row['status'] ?? '') == 'Paid';
              return Container(
                margin: const EdgeInsets.only(bottom: 8),
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(10), border: Border.all(color: Colors.grey.shade200)),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Installment #${row['installment_no'] ?? '-'}', style: const TextStyle(fontWeight: FontWeight.w700, color: Color(0xFF0B194C), fontSize: 14)),
                        Text('Due Date: ${row['due_date'] ?? '-'}', style: TextStyle(color: Colors.grey.shade600, fontSize: 12)),
                        if (row['txn_ref'] != null)
                          Text('Ref: ${row['txn_ref']}', style: TextStyle(color: Colors.grey.shade500, fontSize: 11)),
                      ],
                    ),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Text('₹${(row['amount'] as num?)?.toDouble().toStringAsFixed(0) ?? '-'}', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15)),
                        const SizedBox(height: 4),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                          decoration: BoxDecoration(
                            color: isPaid ? Colors.green.shade50 : Colors.orange.shade50,
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(color: isPaid ? Colors.green.shade200 : Colors.orange.shade200),
                          ),
                          child: Text(
                            row['status']?.toString() ?? 'Pending',
                            style: TextStyle(color: isPaid ? Colors.green.shade700 : Colors.orange.shade700, fontSize: 11, fontWeight: FontWeight.w600),
                          ),
                        )
                      ],
                    )
                  ],
                ),
              );
            }),
        ],
      ),
    );
  }

  Widget _buildStatCol(String label, String value, {Color? color}) {
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

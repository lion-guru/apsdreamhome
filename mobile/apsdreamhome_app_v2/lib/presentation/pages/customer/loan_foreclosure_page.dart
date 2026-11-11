import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

final foreclosureProvider = FutureProvider<Map<String, dynamic>>((ref) async {
  final api = ApiService();
  AppConstants.initBaseUrl();
  try {
    final res = await api.get('/api/v2/mobile/foreclosure/preview');
    if (res['success'] == true) {
      return res['data'] as Map<String, dynamic>;
    }
  } catch (e) {
    debugPrint('Error loading foreclosure quote: $e');
  }
  return {
    'total_plot_value': 1500000.0,
    'amount_paid': 540000.0,
    'principal_outstanding': 960000.0,
    'foreclosure_rebate_pct': 5.0,
    'foreclosure_discount': 48000.0,
    'net_settlement_amount': 912000.0,
    'upi_pay_link': 'upi://pay?pa=apsdreamhome@icici&pn=APS+Dream+Home&am=912000&cu=INR',
    'terms': [
      '5% waiver on remaining principal balance upon full one-time closure.',
      'Zero foreclosure penalty charges.',
      'NOC & Clear Title Deed issued within 7 business days post settlement.'
    ]
  };
});

class LoanForeclosurePage extends ConsumerStatefulWidget {
  const LoanForeclosurePage({super.key});

  @override
  ConsumerState<LoanForeclosurePage> createState() => _LoanForeclosurePageState();
}

class _LoanForeclosurePageState extends ConsumerState<LoanForeclosurePage> {
  final _utrCtrl = TextEditingController();
  bool _submitting = false;

  Future<void> _launchUpi(String upiUrl) async {
    if (upiUrl.isEmpty) return;
    final uri = Uri.parse(upiUrl);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Could not launch UPI app. Please use IMPS/NEFT or QR.')),
        );
      }
    }
  }

  Future<void> _submitSettlement(double settlementAmount, double discount) async {
    if (_utrCtrl.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Please enter UPI/Bank UTR reference number.')));
      return;
    }

    setState(() => _submitting = true);
    final api = ApiService();
    try {
      final res = await api.post('/api/v2/mobile/foreclosure/request', data: {
        'booking_id': 1,
        'settlement_amount': settlementAmount,
        'discount_applied': discount,
        'utr_number': _utrCtrl.text.trim(),
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(((res as Map<String, dynamic>?)?['message'] ?? 'Settlement request submitted!').toString())),
        );
        _utrCtrl.clear();
      }
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final fcAsync = ref.watch(foreclosureProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F0),
      appBar: AppBar(
        title: const Text('Loan Foreclosure & Settlement', style: TextStyle(fontWeight: FontWeight.w700, color: Color(0xFF0B194C))),
        backgroundColor: Colors.white,
        elevation: 0.5,
        iconTheme: const IconThemeData(color: Color(0xFF0B194C)),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.invalidate(foreclosureProvider),
          ),
        ],
      ),
      body: fcAsync.when(
        data: (data) => _buildContent(context, data),
        loading: () => const Center(child: CircularProgressIndicator(color: AppTheme.primaryColor)),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }

  Widget _buildContent(BuildContext context, Map<String, dynamic> data) {
    final totalVal = (data['total_plot_value'] as num?)?.toDouble() ?? 1500000.0;
    final paid = (data['amount_paid'] as num?)?.toDouble() ?? 540000.0;
    final outstanding = (data['principal_outstanding'] as num?)?.toDouble() ?? 960000.0;
    final discount = (data['foreclosure_discount'] as num?)?.toDouble() ?? 48000.0;
    final netAmount = (data['net_settlement_amount'] as num?)?.toDouble() ?? 912000.0;
    final upiLink = data['upi_pay_link'] as String? ?? '';
    final terms = (data['terms'] as List<dynamic>?) ?? [];

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Discount Banner
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            gradient: const LinearGradient(colors: [Color(0xFF0B194C), Color(0xFF1E3A8A)]),
            borderRadius: BorderRadius.circular(16),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Early Foreclosure Offer', style: TextStyle(color: Color(0xFFD4AF37), fontWeight: FontWeight.w700, fontSize: 16)),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(color: const Color(0xFFD4AF37), borderRadius: BorderRadius.circular(12)),
                    child: const Text('SAVE 5%', style: TextStyle(color: Color(0xFF0B194C), fontWeight: FontWeight.w800, fontSize: 11)),
                  )
                ],
              ),
              const SizedBox(height: 8),
              const Text('Close your plot loan today in 1-step and get an instant 5% waiver on outstanding balance.', style: TextStyle(color: Colors.white70, fontSize: 12)),
              const Divider(color: Colors.white24, height: 24),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  _buildWhiteCol('Outstanding', '₹${outstanding.toStringAsFixed(0)}'),
                  _buildWhiteCol('Instant Discount', '-₹${discount.toStringAsFixed(0)}', color: const Color(0xFFD4AF37)),
                  _buildWhiteCol('Net Settlement', '₹${netAmount.toStringAsFixed(0)}', color: Colors.greenAccent),
                ],
              ),
            ],
          ),
        ),
        const SizedBox(height: 20),

        // Breakdown Card
        GlassCard(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('Settlement Breakdown', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 15, color: Color(0xFF0B194C))),
              const SizedBox(height: 12),
              _buildRow('Original Plot Value', '₹${totalVal.toStringAsFixed(0)}'),
              _buildRow('Total Paid So Far', '₹${paid.toStringAsFixed(0)}'),
              _buildRow('Remaining Principal', '₹${outstanding.toStringAsFixed(0)}'),
              _buildRow('Foreclosure Rebate (5%)', '-₹${discount.toStringAsFixed(0)}', isGreen: true),
              const Divider(height: 20),
              _buildRow('Net Payable for Full Settlement', '₹${netAmount.toStringAsFixed(0)}', isBold: true),
            ],
          ),
        ),
        const SizedBox(height: 20),

        // Pay via UPI Action
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12), border: Border.all(color: Colors.grey.shade200)),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('1. Instant Settlement via UPI', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: Color(0xFF0B194C))),
              const SizedBox(height: 6),
              const Text('Launch GPay, PhonePe, or Paytm with pre-filled amount and settlement note.', style: TextStyle(color: Colors.grey, fontSize: 12)),
              const SizedBox(height: 12),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFD4AF37),
                    foregroundColor: const Color(0xFF0B194C),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                  ),
                  icon: const Icon(Icons.payment),
                  label: const Text('Pay Settlement via UPI App', style: TextStyle(fontWeight: FontWeight.w700)),
                  onPressed: () => _launchUpi(upiLink),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),

        // Record Bank / UTR Reference
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12), border: Border.all(color: Colors.grey.shade200)),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('2. Submit Payment Reference (UTR)', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: Color(0xFF0B194C))),
              const SizedBox(height: 6),
              const Text('Paid via NetBanking/IMPS/UPI? Enter the 12-digit transaction UTR to issue your Zero-Dues Certificate.', style: TextStyle(color: Colors.grey, fontSize: 12)),
              const SizedBox(height: 12),
              TextField(
                controller: _utrCtrl,
                decoration: InputDecoration(
                  labelText: 'Bank Reference / UTR Number *',
                  hintText: 'e.g. 612093847291',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                ),
              ),
              const SizedBox(height: 12),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF0B194C),
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                  ),
                  onPressed: _submitting ? null : () => _submitSettlement(netAmount, discount),
                  child: _submitting
                      ? const SizedBox(height: 16, width: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                      : const Text('Confirm Settlement Request', style: TextStyle(fontWeight: FontWeight.w700)),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 20),

        // Terms & Conditions
        const Text('Settlement Terms & Guarantees', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: Color(0xFF0B194C))),
        const SizedBox(height: 8),
        ...terms.map((t) => Padding(
              padding: const EdgeInsets.only(bottom: 6),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Icon(Icons.check_circle_outline, color: Colors.green, size: 16),
                  const SizedBox(width: 8),
                  Expanded(child: Text(t.toString(), style: TextStyle(color: Colors.grey.shade700, fontSize: 12))),
                ],
              ),
            )),
      ],
    );
  }

  Widget _buildRow(String label, String value, {bool isGreen = false, bool isBold = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: TextStyle(color: Colors.grey.shade700, fontSize: 13, fontWeight: isBold ? FontWeight.w700 : FontWeight.w400)),
          Text(value, style: TextStyle(fontWeight: isBold ? FontWeight.w800 : FontWeight.w600, fontSize: isBold ? 15 : 13, color: isGreen ? Colors.green.shade700 : const Color(0xFF0B194C))),
        ],
      ),
    );
  }

  Widget _buildWhiteCol(String label, String value, {Color? color}) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: const TextStyle(color: Colors.white60, fontSize: 11)),
        const SizedBox(height: 4),
        Text(value, style: TextStyle(fontWeight: FontWeight.w800, fontSize: 14, color: color ?? Colors.white)),
      ],
    );
  }
}

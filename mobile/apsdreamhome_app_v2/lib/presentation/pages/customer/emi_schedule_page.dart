import '../../../core/services/api_service.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/utils/logger.dart';
import '../../widgets/glass_card.dart';

class EmiSchedulePage extends ConsumerStatefulWidget {
  final int? bookingId;
  const EmiSchedulePage({super.key, this.bookingId});

  @override
  ConsumerState<EmiSchedulePage> createState() => _EmiSchedulePageState();
}

class _EmiSchedulePageState extends ConsumerState<EmiSchedulePage> {
  List<dynamic> _installments = [];
  bool _isLoading = true;

  String _formatCurrency(num amount) {
    final fixed = amount.toStringAsFixed(0);
    return '₹${fixed.replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (Match m) => '${m[1]},')}';
  }

  @override
  void initState() {
    super.initState();
    _fetchSchedule();
  }

  Future<void> _fetchSchedule() async {
    try {
      final api = ApiService();
      final response = await api.get('/customer/emi-schedule', queryParameters: {'booking_id': widget.bookingId});
      if (mounted) {
        setState(() {
          _installments = (response['data'] as List<dynamic>?) ?? [];
          _isLoading = false;
        });
      }
    } catch (e) {
      AppLogger.error('Error fetching EMI schedule', e);
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('EMI Schedule - Booking #${widget.bookingId}'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _installments.isEmpty
          ? RefreshIndicator(
              onRefresh: _fetchSchedule,
              child: ListView(
                children: const [
                  SizedBox(height: 100),
                  Center(
                    child: Text('No EMI schedule found for this booking.'),
                  ),
                ],
              ),
            )
          : RefreshIndicator(
              onRefresh: _fetchSchedule,
              child: ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: _installments.length + 1,
                itemBuilder: (context, index) {
                  if (index == 0) return _buildProgressHeader();
                  final emi = _installments[index - 1] as Map<String, dynamic>;
                  return _buildEmiTile(emi);
                },
              ),
            ),
    );
  }

  Widget _buildProgressHeader() {
    final total = _installments.length;
    final paidCount = _installments
        .where((e) => (e as Map<String, dynamic>)['status'] == 'paid')
        .length;
    final progress = total > 0 ? paidCount / total : 0.0;

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            AppTheme.primaryColor,
            AppTheme.primaryColor.withValues(alpha: 0.8),
          ],
        ),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                '$paidCount of $total EMIs paid',
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.bold,
                  fontSize: 16,
                ),
              ),
              Text(
                '${(progress * 100).toStringAsFixed(0)}%',
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.bold,
                  fontSize: 16,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          ClipRRect(
            borderRadius: BorderRadius.circular(6),
            child: LinearProgressIndicator(
              value: progress,
              backgroundColor: Colors.white.withValues(alpha: 0.3),
              valueColor: const AlwaysStoppedAnimation<Color>(Colors.white),
              minHeight: 8,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEmiTile(Map<String, dynamic> emi) {
    final String status = emi['status'] as String;
    final bool isPaid = status == 'paid';
    final bool isOverdue = status == 'overdue';

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      child: GlassCard(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              Container(
                width: 40,
                height: 40,
                decoration: BoxDecoration(
                  color: isPaid
                      ? Colors.green.withValues(alpha: 0.1)
                      : Colors.blue.withValues(alpha: 0.1),
                  shape: BoxShape.circle,
                ),
                child: Center(
                  child: Text(
                    (emi['emi_number'] as num).toString(),
                    style: TextStyle(
                      fontWeight: FontWeight.bold,
                      color: isPaid ? Colors.green : Colors.blue,
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      _formatCurrency(emi['amount'] as num),
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                      ),
                    ),
                    Text(
                      'Due: ${emi['due_date'] as String}',
                      style: TextStyle(
                        color: Colors.grey.shade600,
                        fontSize: 13,
                      ),
                    ),
                  ],
                ),
              ),
              if (isPaid)
                const Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Icon(Icons.check_circle, color: Colors.green),
                    Text(
                      'Paid',
                      style: TextStyle(color: Colors.green, fontSize: 11),
                    ),
                  ],
                )
              else
                ElevatedButton(
                  onPressed: () => _processPayment(emi),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: isOverdue
                        ? Colors.red
                        : AppTheme.primaryColor,
                    minimumSize: const Size(80, 32),
                    padding: const EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 8,
                    ),
                  ),
                  child: const Text('Pay Now'),
                ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _processPayment(Map<String, dynamic> emi) async {
    final emiAmount = (emi['amount'] as num).toDouble();
    final emiNumber = (emi['emi_number'] as num).toInt();
    final emiId = (emi['id'] as num).toInt();
    const vpa = 'apsdreamhome@icici';
    final note = 'EMI_BK${widget.bookingId ?? 0}_INST$emiNumber';
    final upiUri = 'upi://pay?pa=$vpa&pn=APS+Dream+Home&am=${emiAmount.toStringAsFixed(2)}&tn=$note&cu=INR';

    showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (sheetContext) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: Colors.grey.shade300,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Pay EMI #$emiNumber',
                    style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                  ),
                  Text(
                    _formatCurrency(emiAmount),
                    style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: AppTheme.primaryColor),
                  ),
                ],
              ),
              const SizedBox(height: 6),
              Text(
                'Due Date: ${emi['due_date'] ?? 'N/A'}',
                style: TextStyle(color: Colors.grey.shade600, fontSize: 13),
              ),
              const Divider(height: 28),

              // Option 1: Instant UPI Apps
              ElevatedButton.icon(
                onPressed: () async {
                  Navigator.pop(sheetContext);
                  final uri = Uri.parse(upiUri);
                  try {
                    await launchUrl(uri, mode: LaunchMode.externalApplication);
                  } catch (e) {
                    if (mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('No supported UPI app found. Please copy UPI ID below.')),
                      );
                    }
                  }
                },
                icon: const Icon(Icons.flash_on, color: Colors.amber),
                label: const Text('Pay via Any UPI App (GPay / PhonePe / Paytm)'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.primaryColor,
                  foregroundColor: Colors.white,
                  minimumSize: const Size(double.infinity, 50),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
              ),
              const SizedBox(height: 12),

              // Option 2: Copy UPI ID
              OutlinedButton.icon(
                onPressed: () {
                  Clipboard.setData(const ClipboardData(text: vpa));
                  if (mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('UPI ID ($vpa) copied to clipboard!')),
                    );
                  }
                },
                icon: const Icon(Icons.copy, size: 18),
                label: const Text('Copy UPI ID: $vpa'),
                style: OutlinedButton.styleFrom(
                  minimumSize: const Size(double.infinity, 46),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
              ),
              const SizedBox(height: 12),

              // Option 3: Submit UTR
              TextButton.icon(
                onPressed: () {
                  Navigator.pop(sheetContext);
                  _showUtrSubmissionDialog(emiId, emiNumber, emiAmount);
                },
                icon: const Icon(Icons.receipt_long, size: 18, color: Colors.green),
                label: const Text(
                  'Already Paid? Submit 12-Digit UTR Reference',
                  style: TextStyle(color: Colors.green, fontWeight: FontWeight.bold),
                ),
                style: TextButton.styleFrom(
                  minimumSize: const Size(double.infinity, 46),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _showUtrSubmissionDialog(int emiId, int emiNumber, double amount) {
    final controller = TextEditingController();
    showDialog<void>(
      context: context,
      builder: (dialogCtx) => AlertDialog(
        title: Text('Submit UTR for EMI #$emiNumber'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Amount: ${_formatCurrency(amount)}', style: const TextStyle(fontWeight: FontWeight.bold)),
            const SizedBox(height: 12),
            TextField(
              controller: controller,
              decoration: const InputDecoration(
                labelText: '12-digit UPI UTR / Ref No.',
                hintText: 'e.g. 432156789012',
                border: OutlineInputBorder(),
              ),
              keyboardType: TextInputType.text,
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogCtx),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () async {
              final utr = controller.text.trim();
              if (utr.length < 6) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Please enter a valid UTR number (at least 6 characters)')),
                );
                return;
              }
              Navigator.pop(dialogCtx);
              setState(() => _isLoading = true);
              try {
                final api = ApiService();
                await api.post('/customer/pay-emi', data: {
                  'emi_id': emiId,
                  'amount': amount,
                  'utr_number': utr,
                  'payment_method': 'upi',
                });
                _fetchSchedule();
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('UTR submitted successfully! Our accounts team will verify shortly.'),
                      backgroundColor: Colors.green,
                    ),
                  );
                }
              } catch (e) {
                AppLogger.error('Error submitting UTR', e);
                if (mounted) setState(() => _isLoading = false);
              }
            },
            child: const Text('Submit UTR'),
          ),
        ],
      ),
    );
  }
}

// Note: styling handled inline above

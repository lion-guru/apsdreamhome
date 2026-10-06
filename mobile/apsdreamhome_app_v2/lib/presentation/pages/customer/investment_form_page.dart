import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../../core/services/api_service.dart';
import '../../widgets/glass_card.dart';

class InvestmentFormPage extends StatefulWidget {
  final String planId;

  const InvestmentFormPage({super.key, required this.planId});

  @override
  State<InvestmentFormPage> createState() => _InvestmentFormPageState();
}

class _InvestmentFormPageState extends State<InvestmentFormPage> {
  bool _isLoading = true;
  bool _isSubmitting = false;
  String? _error;
  Map<String, dynamic>? _plan;
  final TextEditingController _amountCtrl = TextEditingController();
  String _paymentMode = 'wallet';

  @override
  void initState() {
    super.initState();
    _fetchPlans();
  }

  @override
  void dispose() {
    _amountCtrl.dispose();
    super.dispose();
  }

  Future<void> _fetchPlans() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final response = await ApiService().get('/investment-plans');
      final data = response['data'];
      final List<Map<String, dynamic>> plans = (data is List)
          ? data.map((e) => Map<String, dynamic>.from(e as Map)).toList()
          : <Map<String, dynamic>>[];
      if (!mounted) return;
      Map<String, dynamic>? found;
      for (final p in plans) {
        if ((p['id']?.toString() ?? '') == widget.planId) {
          found = p;
          break;
        }
      }
      setState(() {
        _plan = found;
        _isLoading = false;
        if (found == null) {
          _error = 'Plan not found';
        } else {
          final minAmt = ((found['min_amount'] as num?) ?? 0).toDouble();
          _amountCtrl.text = minAmt.toStringAsFixed(0);
        }
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  Future<void> _submit() async {
    final amount = double.tryParse(_amountCtrl.text.trim()) ?? 0;
    if (amount <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Enter a valid amount')),
      );
      return;
    }
    final minAmt = ((_plan?['min_amount'] as num?) ?? 0).toDouble();
    if (amount < minAmt) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
            content: Text(
                'Minimum investment is \u20B9${minAmt.toStringAsFixed(0)}')),
      );
      return;
    }
    setState(() => _isSubmitting = true);
    try {
      final result = await ApiService().post(
        '/user/invest',
        data: {
          'plan_id': int.tryParse(widget.planId) ?? 0,
          'amount': amount,
          'payment_mode': _paymentMode,
        },
      );
      if (!mounted) return;
      final ok = (result['success'] as bool?) ?? false;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(ok
              ? 'Investment successful!'
              : (result['error']?.toString() ?? 'Investment failed')),
        ),
      );
      if (ok) context.go('/user/investments');
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $e')),
      );
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Invest'), centerTitle: true),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null || _plan == null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.error_outline,
                          size: 64, color: Colors.red),
                      const SizedBox(height: 16),
                      Text('Error: ${_error ?? 'Plan not found'}'),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: () => context.pop(),
                        child: const Text('Go Back'),
                      ),
                    ],
                  ),
                )
              : SingleChildScrollView(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      GlassCard(
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                _plan!['plan_name']?.toString() ??
                                    'Investment Plan',
                                style: const TextStyle(
                                    fontSize: 18,
                                    fontWeight: FontWeight.bold),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                _plan!['description']?.toString() ?? '',
                                style: TextStyle(
                                    color: Colors.grey[700], fontSize: 13),
                              ),
                              const SizedBox(height: 12),
                              Row(
                                children: [
                                  Expanded(
                                    child: _fact(
                                        'Expected Return',
                                        '${((_plan!['expected_return_pct'] as num?) ?? 0).toStringAsFixed(1)}% p.a.'),
                                  ),
                                  Expanded(
                                    child: _fact('Tenure',
                                        '${(_plan!['tenure_months']?.toString() ?? '-')} months'),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),
                      const Text('Investment Amount (\u20B9)',
                          style: TextStyle(fontWeight: FontWeight.bold)),
                      const SizedBox(height: 8),
                      TextField(
                        controller: _amountCtrl,
                        keyboardType:
                            const TextInputType.numberWithOptions(decimal: true),
                        decoration: const InputDecoration(
                          border: OutlineInputBorder(),
                          prefixText: '\u20B9 ',
                          hintText: 'Enter amount',
                        ),
                      ),
                      const SizedBox(height: 16),
                      const Text('Payment Mode',
                          style: TextStyle(fontWeight: FontWeight.bold)),
                      const SizedBox(height: 8),
                      DropdownButtonFormField<String>(
                        initialValue: _paymentMode,
                        decoration: const InputDecoration(
                            border: OutlineInputBorder()),
                        items: const [
                          DropdownMenuItem(
                              value: 'wallet',
                              child: Text('Wallet Balance')),
                          DropdownMenuItem(
                              value: 'razorpay',
                              child: Text('Razorpay (UPI/Card)')),
                          DropdownMenuItem(
                              value: 'bank_transfer',
                              child: Text('Bank Transfer')),
                        ],
                        onChanged: (v) =>
                            setState(() => _paymentMode = v ?? 'wallet'),
                      ),
                      const SizedBox(height: 24),
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton.icon(
                          onPressed: _isSubmitting ? null : _submit,
                          icon: _isSubmitting
                              ? const SizedBox(
                                  width: 18,
                                  height: 18,
                                  child: CircularProgressIndicator(
                                      strokeWidth: 2, color: Colors.white),
                                )
                              : const Icon(Icons.check),
                          label: Text(_isSubmitting
                              ? 'Processing...'
                              : 'Confirm Investment'),
                        ),
                      ),
                      const SizedBox(height: 40),
                    ],
                  ),
                ),
    );
  }

  Widget _fact(String label, String value) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label,
            style: TextStyle(color: Colors.grey[600], fontSize: 12)),
        Text(value,
            style: const TextStyle(
                fontSize: 15, fontWeight: FontWeight.bold)),
      ],
    );
  }
}

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../providers/mlm_provider.dart';
import '../../../core/theme/app_theme.dart';

class PayoutRequestDialog extends ConsumerStatefulWidget {
  final double maxAmount;
  const PayoutRequestDialog({super.key, required this.maxAmount});

  @override
  ConsumerState<PayoutRequestDialog> createState() => _PayoutRequestDialogState();
}

class _PayoutRequestDialogState extends ConsumerState<PayoutRequestDialog> {
  final _amountController = TextEditingController();
  final _remarksController = TextEditingController();
  bool _isLoading = false;

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: const Text('Request Payout'),
      content: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              'Available Balance: ₹${widget.maxAmount.toStringAsFixed(2)}',
              style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.green),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: _amountController,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(
                labelText: 'Amount (₹)',
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _remarksController,
              decoration: const InputDecoration(
                labelText: 'Remarks',
                border: OutlineInputBorder(),
              ),
            ),
          ],
        ),
      ),
      actions: [
        TextButton(
          onPressed: _isLoading ? null : () => Navigator.pop(context),
          child: const Text('Cancel'),
        ),
        ElevatedButton(
          onPressed: _isLoading ? null : _submitRequest,
          style: ElevatedButton.styleFrom(backgroundColor: AppTheme.primaryColor),
          child: _isLoading 
              ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2))
              : const Text('Submit Request'),
        ),
      ],
    );
  }

  Future<void> _submitRequest() async {
    final amountStr = _amountController.text.trim();
    if (amountStr.isEmpty) return;
    
    final amount = double.tryParse(amountStr) ?? 0;
    if (amount <= 0 || amount > widget.maxAmount) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Invalid amount. Max allowed: ₹${widget.maxAmount}')),
      );
      return;
    }

    setState(() => _isLoading = true);
    
    try {
      final result = await ref.read(mlmProvider.notifier).requestPayout(amount, _remarksController.text);
      if (mounted) {
        Navigator.pop(context);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(result['message'] ?? 'Request submitted!')),
        );
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e')),
        );
      }
    }
  }
}

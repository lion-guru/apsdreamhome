import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/services/api_service.dart';
import '../../../core/theme/app_theme.dart';


final pendingPayoutsProvider = FutureProvider.autoDispose<List<dynamic>>((ref) async {
  final apiService = ApiService();
  final response = await apiService.get('payouts/pending');
  return (response['data'] as List<dynamic>?) ?? [];
});

/// Auto Payout Dashboard Page
/// Admin view for reviewing pending commissions and triggering bulk payouts.
class AutoPayoutPage extends ConsumerStatefulWidget {
  const AutoPayoutPage({super.key});

  @override
  ConsumerState<AutoPayoutPage> createState() => _AutoPayoutPageState();
}

class _AutoPayoutPageState extends ConsumerState<AutoPayoutPage> {
  bool _isProcessing = false;
  bool _payoutDone = false;
  double _lastProcessedAmount = 0.0;
  int _lastProcessedAgents = 0;

  @override
  Widget build(BuildContext context) {
    final pendingPayoutsAsync = ref.watch(pendingPayoutsProvider);

    return Scaffold(
      backgroundColor: const Color(0xFF0A1628),
      appBar: AppBar(
        backgroundColor: const Color(0xFF0A1628),
        title: const Text('Auto Payout Center', style: TextStyle(color: Colors.white)),
        iconTheme: const IconThemeData(color: Colors.white),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.invalidate(pendingPayoutsProvider),
          ),
        ],
      ),
      body: _payoutDone 
          ? _buildSuccessView() 
          : pendingPayoutsAsync.when(
              data: (payouts) => _buildPayoutView(payouts),
              loading: () => const Center(child: CircularProgressIndicator(color: AppTheme.accentColor)),
              error: (err, stack) => Center(
                child: Text(
                  'Error loading pending payouts: $err',
                  style: const TextStyle(color: Colors.redAccent),
                ),
              ),
            ),
    );
  }

  Widget _buildPayoutView(List<dynamic> payouts) {
    final double totalAmount = payouts.fold(0.0, (sum, p) {
      final val = p['total_pending'];
      return sum + (val is num ? val.toDouble() : double.tryParse(val.toString()) ?? 0.0);
    });

    return Column(
      children: [
        // Summary Banner
        Container(
          width: double.infinity,
          margin: const EdgeInsets.all(16),
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [Color(0xFF1A237E), Color(0xFF283593)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(16),
          ),
          child: Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Total Pending Payout', style: TextStyle(color: Colors.white70, fontSize: 13)),
                    const SizedBox(height: 4),
                    Text(
                      '₹${_formatAmount(totalAmount)}',
                      style: const TextStyle(color: Colors.white, fontSize: 28, fontWeight: FontWeight.bold),
                    ),
                    Text(
                      '${payouts.length} agents eligible',
                      style: const TextStyle(color: Colors.white60, fontSize: 13),
                    ),
                  ],
                ),
              ),
              const Icon(Icons.account_balance_wallet, color: Colors.white30, size: 56),
            ],
          ),
        ),

        // Agent List
        Expanded(
          child: payouts.isEmpty
              ? const Center(
                  child: Text(
                    'No pending payouts found',
                    style: TextStyle(color: Colors.white60, fontSize: 16),
                  ),
                )
              : ListView.builder(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  itemCount: payouts.length,
                  itemBuilder: (context, index) {
                    final payout = payouts[index] as Map<String, dynamic>;
                    return _buildAgentPayoutCard(payout);
                  },
                ),
        ),

        // Process Button
        if (payouts.isNotEmpty)
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(16),
            child: _isProcessing
                ? const Column(
                    children: [
                      CircularProgressIndicator(color: Colors.green),
                      SizedBox(height: 12),
                      Text('Processing payouts...', style: TextStyle(color: Colors.white70)),
                    ],
                  )
                : ElevatedButton.icon(
                    onPressed: () => _processPayouts(totalAmount, payouts.length),
                    icon: const Icon(Icons.send_rounded, size: 24),
                    label: const Text('Process All Payouts Now', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.green.shade700,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      minimumSize: const Size(double.infinity, 56),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      shadowColor: Colors.green.withValues(alpha: 0.4),
                      elevation: 8,
                    ),
                  ),
          ),
      ],
    );
  }

  Widget _buildAgentPayoutCard(Map<String, dynamic> payout) {
    final amount = payout['total_pending'] is num 
        ? (payout['total_pending'] as num).toDouble() 
        : double.tryParse(payout['total_pending'].toString()) ?? 0.0;
    final name = (payout['name'] as String?) ?? 'Unknown';
    final email = (payout['email'] as String?) ?? '';
    final count = payout['pending_count'] ?? 0;

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFF1C2840),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.white.withValues(alpha: 0.05)),
      ),
      child: Row(
        children: [
          CircleAvatar(
            backgroundColor: Colors.blueAccent.withValues(alpha: 0.2),
            child: Text(
              name.isNotEmpty ? name.substring(0, 1) : 'U',
              style: const TextStyle(color: Colors.blueAccent, fontWeight: FontWeight.bold),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(name,
                    style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600)),
                Text(email,
                    style: const TextStyle(color: Colors.blueAccent, fontSize: 12)),
                Text('$count commission(s)',
                    style: const TextStyle(color: Colors.white38, fontSize: 11)),
              ],
            ),
          ),
          Text(
            '₹${_formatAmount(amount)}',
            style: const TextStyle(color: Colors.greenAccent, fontWeight: FontWeight.bold, fontSize: 16),
          ),
        ],
      ),
    );
  }

  Widget _buildSuccessView() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(28),
              decoration: BoxDecoration(
                color: Colors.green.withValues(alpha: 0.2),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.check_circle, color: Colors.green, size: 72),
            ),
            const SizedBox(height: 24),
            const Text(
              'Payouts Processed! 🎉',
              style: TextStyle(color: Colors.white, fontSize: 24, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 12),
            Text(
              '₹${_formatAmount(_lastProcessedAmount)} distributed to $_lastProcessedAgents agents.',
              style: const TextStyle(color: Colors.white60, fontSize: 16),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 32),
            ElevatedButton(
              onPressed: () {
                setState(() {
                  _payoutDone = false;
                });
                ref.invalidate(pendingPayoutsProvider);
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.green.shade700,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 14),
              ),
              child: const Text('Done'),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _processPayouts(double totalAmount, int totalAgents) async {
    setState(() => _isProcessing = true);
    
    try {
      final apiService = ApiService();
      final response = await apiService.post('payouts/process');
      if (response['success'] == true) {
        setState(() {
          _lastProcessedAmount = totalAmount;
          _lastProcessedAgents = totalAgents;
          _payoutDone = true;
        });
      } else if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to process payouts: ${response['message'] ?? 'Unknown error'}')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e')),
        );
      }
    } finally {
      setState(() => _isProcessing = false);
    }
  }

  String _formatAmount(double amount) {
    if (amount >= 100000) {
      return '${(amount / 100000).toStringAsFixed(2)} L';
    } else if (amount >= 1000) {
      return '${(amount / 1000).toStringAsFixed(1)}K';
    }
    return amount.toStringAsFixed(0);
  }
}

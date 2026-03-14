import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

/// Auto Payout Dashboard Page
/// Admin view for reviewing pending commissions and triggering bulk payouts.
class AutoPayoutPage extends ConsumerStatefulWidget {
  const AutoPayoutPage({Key? key}) : super(key: key);

  @override
  ConsumerState<AutoPayoutPage> createState() => _AutoPayoutPageState();
}

class _AutoPayoutPageState extends ConsumerState<AutoPayoutPage> {
  bool _isProcessing = false;
  bool _payoutDone = false;

  // Simulated pending payout data
  final List<Map<String, dynamic>> _pendingPayouts = [
    {'name': 'Rahul Sharma', 'rank': 'BDM', 'amount': 24500.0, 'count': 3},
    {'name': 'Priya Singh', 'rank': 'Sr. Associate', 'amount': 18200.0, 'count': 2},
    {'name': 'Amit Kumar', 'rank': 'Vice President', 'amount': 52000.0, 'count': 5},
    {'name': 'Sunita Verma', 'rank': 'Associate', 'amount': 9800.0, 'count': 1},
    {'name': 'Deepak Gupta', 'rank': 'Sr. BDM', 'amount': 35600.0, 'count': 4},
  ];

  double get _totalAmount =>
      _pendingPayouts.fold(0, (sum, p) => sum + (p['amount'] as double));

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0A1628),
      appBar: AppBar(
        backgroundColor: const Color(0xFF0A1628),
        title: const Text('Auto Payout Center', style: TextStyle(color: Colors.white)),
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      body: _payoutDone ? _buildSuccessView() : _buildPayoutView(),
    );
  }

  Widget _buildPayoutView() {
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
                      '₹${_formatAmount(_totalAmount)}',
                      style: const TextStyle(color: Colors.white, fontSize: 28, fontWeight: FontWeight.bold),
                    ),
                    Text(
                      '${_pendingPayouts.length} agents eligible',
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
          child: ListView.builder(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            itemCount: _pendingPayouts.length,
            itemBuilder: (context, index) {
              final payout = _pendingPayouts[index];
              return _buildAgentPayoutCard(payout);
            },
          ),
        ),

        // Process Button
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(16),
          child: _isProcessing
              ? Column(
                  children: [
                    const CircularProgressIndicator(color: Colors.green),
                    const SizedBox(height: 12),
                    const Text('Processing payouts...', style: TextStyle(color: Colors.white70)),
                  ],
                )
              : ElevatedButton.icon(
                  onPressed: _processPayouts,
                  icon: const Icon(Icons.send_rounded, size: 24),
                  label: const Text('Process All Payouts Now', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.green.shade700,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    minimumSize: const Size(double.infinity, 56),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    shadowColor: Colors.green.withOpacity(0.4),
                    elevation: 8,
                  ),
                ),
        ),
      ],
    );
  }

  Widget _buildAgentPayoutCard(Map<String, dynamic> payout) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFF1C2840),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.white.withOpacity(0.05)),
      ),
      child: Row(
        children: [
          CircleAvatar(
            backgroundColor: Colors.blueAccent.withOpacity(0.2),
            child: Text(
              (payout['name'] as String).substring(0, 1),
              style: const TextStyle(color: Colors.blueAccent, fontWeight: FontWeight.bold),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(payout['name'] as String,
                    style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600)),
                Text(payout['rank'] as String,
                    style: const TextStyle(color: Colors.blueAccent, fontSize: 12)),
                Text('${payout['count']} commission(s)',
                    style: const TextStyle(color: Colors.white38, fontSize: 11)),
              ],
            ),
          ),
          Text(
            '₹${_formatAmount(payout['amount'] as double)}',
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
                color: Colors.green.withOpacity(0.2),
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
              '₹${_formatAmount(_totalAmount)} distributed to ${_pendingPayouts.length} agents.',
              style: const TextStyle(color: Colors.white60, fontSize: 16),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 32),
            ElevatedButton(
              onPressed: () => Navigator.pop(context),
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

  Future<void> _processPayouts() async {
    setState(() => _isProcessing = true);
    // Simulate API call
    await Future.delayed(const Duration(seconds: 2));
    // TODO: Call ApiService.processPayouts()
    setState(() {
      _isProcessing = false;
      _payoutDone = true;
    });
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

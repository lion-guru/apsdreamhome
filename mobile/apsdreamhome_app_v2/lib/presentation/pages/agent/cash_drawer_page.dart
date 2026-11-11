import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:apsdreamhome_app_v2/core/services/api_service.dart';

final cashDrawerProvider = FutureProvider.autoDispose<Map<String, dynamic>>((ref) async {
  final api = ApiService();
  final res = await api.get('/api/v2/mobile/cash-drawer/today');
  return res;
});

class CashDrawerPage extends ConsumerStatefulWidget {
  const CashDrawerPage({super.key});

  @override
  ConsumerState<CashDrawerPage> createState() => _CashDrawerPageState();
}

class _CashDrawerPageState extends ConsumerState<CashDrawerPage> {
  final Map<int, TextEditingController> _denomCtrls = {
    500: TextEditingController(),
    200: TextEditingController(),
    100: TextEditingController(),
    50: TextEditingController(),
    20: TextEditingController(),
    10: TextEditingController(),
  };
  final _coinsCtrl = TextEditingController();
  final _depositCtrl = TextEditingController();
  final _notesCtrl = TextEditingController();

  double _physicalCount = 0.0;
  bool _submitting = false;

  @override
  void initState() {
    super.initState();
    for (final entry in _denomCtrls.entries) {
      entry.value.addListener(_calculateTotal);
    }
    _coinsCtrl.addListener(_calculateTotal);
  }

  void _calculateTotal() {
    double total = 0.0;
    for (final entry in _denomCtrls.entries) {
      final count = int.tryParse(entry.value.text.trim()) ?? 0;
      total += count * entry.key;
    }
    final coins = double.tryParse(_coinsCtrl.text.trim()) ?? 0.0;
    total += coins;
    setState(() => _physicalCount = total);
  }

  @override
  void dispose() {
    for (final c in _denomCtrls.values) {
      c.dispose();
    }
    _coinsCtrl.dispose();
    _depositCtrl.dispose();
    _notesCtrl.dispose();
    super.dispose();
  }

  Future<void> _submitClose(double systemClosing) async {
    final deposit = double.tryParse(_depositCtrl.text.trim()) ?? 0.0;

    setState(() => _submitting = true);
    try {
      final api = ApiService();
      final res = await api.post('/api/v2/mobile/cash-drawer/close', data: {
        'denom_500': int.tryParse(_denomCtrls[500]?.text.trim() ?? '0') ?? 0,
        'denom_200': int.tryParse(_denomCtrls[200]?.text.trim() ?? '0') ?? 0,
        'denom_100': int.tryParse(_denomCtrls[100]?.text.trim() ?? '0') ?? 0,
        'denom_50': int.tryParse(_denomCtrls[50]?.text.trim() ?? '0') ?? 0,
        'denom_20': int.tryParse(_denomCtrls[20]?.text.trim() ?? '0') ?? 0,
        'denom_10': int.tryParse(_denomCtrls[10]?.text.trim() ?? '0') ?? 0,
        'denom_coins': double.tryParse(_coinsCtrl.text.trim()) ?? 0.0,
        'bank_deposit': deposit,
        'notes': _notesCtrl.text.trim(),
      });

      if (mounted) {
        final msg = ((res as Map<String, dynamic>?)?['message'] ?? 'Day closing recorded!').toString();
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
        ref.invalidate(cashDrawerProvider);
      }
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final drawerAsync = ref.watch(cashDrawerProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F0),
      appBar: AppBar(
        title: const Text('Cash Drawer & Safe Closing', style: TextStyle(fontWeight: FontWeight.w700, color: Color(0xFF0B194C))),
        backgroundColor: Colors.white,
        elevation: 0.5,
        iconTheme: const IconThemeData(color: Color(0xFF0B194C)),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.invalidate(cashDrawerProvider),
          ),
        ],
      ),
      body: drawerAsync.when(
        loading: () => const Center(child: CircularProgressIndicator(color: Color(0xFFD4AF37))),
        error: (err, _) => Center(child: Text('Error: $err')),
        data: (data) {
          final isClosed = data['is_closed'] == true;
          final systemClosing = (data['system_closing'] as num?)?.toDouble() ?? 5000.0;
          final openingCash = (data['opening_cash'] as num?)?.toDouble() ?? 5000.0;
          final cashReceipts = (data['cash_receipts'] as num?)?.toDouble() ?? 0.0;
          final deposit = double.tryParse(_depositCtrl.text.trim()) ?? 0.0;
          final diff = _physicalCount - (systemClosing - deposit);

          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              // System Summary Hero
              Container(
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFF0B194C), Color(0xFF1E3A8A)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('System Expected Cash', style: TextStyle(color: Colors.white70, fontSize: 13)),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: isClosed ? Colors.green.withValues(alpha: 0.2) : Colors.orange.withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            isClosed ? 'REGISTER CLOSED' : 'REGISTER OPEN',
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.w800,
                              color: isClosed ? Colors.greenAccent : const Color(0xFFD4AF37),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 6),
                    Text('₹${systemClosing.toStringAsFixed(2)}', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 24)),
                    const Divider(color: Colors.white24, height: 24),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text('Opening Cash: ₹${openingCash.toStringAsFixed(0)}', style: const TextStyle(color: Colors.white60, fontSize: 11)),
                        Text("Today's Receipts: ₹${cashReceipts.toStringAsFixed(0)}", style: const TextStyle(color: Color(0xFFD4AF37), fontWeight: FontWeight.w700, fontSize: 11)),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),

              // Physical Denomination Counter
              const Text('Physical Currency Count', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: Color(0xFF0B194C))),
              const SizedBox(height: 10),

              Card(
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    children: [
                      Row(
                        children: [
                          Expanded(child: _buildDenomField(500)),
                          const SizedBox(width: 12),
                          Expanded(child: _buildDenomField(200)),
                        ],
                      ),
                      const SizedBox(height: 10),
                      Row(
                        children: [
                          Expanded(child: _buildDenomField(100)),
                          const SizedBox(width: 12),
                          Expanded(child: _buildDenomField(50)),
                        ],
                      ),
                      const SizedBox(height: 10),
                      Row(
                        children: [
                          Expanded(child: _buildDenomField(20)),
                          const SizedBox(width: 12),
                          Expanded(child: _buildDenomField(10)),
                        ],
                      ),
                      const SizedBox(height: 10),
                      TextField(
                        controller: _coinsCtrl,
                        keyboardType: TextInputType.number,
                        decoration: InputDecoration(
                          labelText: 'Coins Total (₹)',
                          prefixIcon: const Icon(Icons.circle, size: 16),
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                          contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),

              // Comparison Card
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: diff == 0 ? Colors.green.shade200 : Colors.red.shade200),
                ),
                child: Column(
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Counted Physical Cash:', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
                        Text('₹${_physicalCount.toStringAsFixed(2)}', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15, color: Color(0xFF0B194C))),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Discrepancy:', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
                        Text(
                          '₹${diff.toStringAsFixed(2)}',
                          style: TextStyle(
                            fontWeight: FontWeight.w800,
                            fontSize: 15,
                            color: diff == 0 ? Colors.green : Colors.red,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),

              // Bank Deposit
              Card(
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      TextField(
                        controller: _depositCtrl,
                        keyboardType: TextInputType.number,
                        decoration: InputDecoration(
                          labelText: 'Bank Deposit Amount (₹)',
                          prefixIcon: const Icon(Icons.account_balance, color: Color(0xFF0B194C)),
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                      ),
                      const SizedBox(height: 12),
                      TextField(
                        controller: _notesCtrl,
                        decoration: InputDecoration(
                          labelText: 'Closing Remarks / Vault Notes',
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 20),

              SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton.icon(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF0B194C),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  icon: _submitting
                      ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                      : const Icon(Icons.lock, size: 18),
                  label: Text(_submitting ? 'Freezing...' : 'Freeze Day Closing & Lock Drawer', style: const TextStyle(fontWeight: FontWeight.w700, color: Colors.white)),
                  onPressed: _submitting ? null : () => _submitClose(systemClosing),
                ),
              ),
              const SizedBox(height: 24),
            ],
          );
        },
      ),
    );
  }

  Widget _buildDenomField(int val) {
    return TextField(
      controller: _denomCtrls[val],
      keyboardType: TextInputType.number,
      decoration: InputDecoration(
        labelText: '₹$val Notes ×',
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
        contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
      ),
    );
  }
}

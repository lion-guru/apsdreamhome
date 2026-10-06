import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../../core/services/api_service.dart';
import '../../widgets/glass_card.dart';

class CustomerInvestmentsPage extends StatefulWidget {
  const CustomerInvestmentsPage({super.key});

  @override
  State<CustomerInvestmentsPage> createState() =>
      _CustomerInvestmentsPageState();
}

class _CustomerInvestmentsPageState extends State<CustomerInvestmentsPage> {
  bool _isLoading = true;
  String? _error;
  List<Map<String, dynamic>> _investments = [];
  Map<String, dynamic> _stats = {};

  @override
  void initState() {
    super.initState();
    _fetch();
  }

  Future<void> _fetch() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final response = await ApiService().get('/user/investments');
      final data = response['data'];
      if (!mounted) return;
      setState(() {
        if (data is Map) {
          final list = data['investments'];
          _investments = (list is List)
              ? list.map((e) => Map<String, dynamic>.from(e as Map)).toList()
              : <Map<String, dynamic>>[];
          final stats = data['stats'];
          _stats = stats is Map
              ? Map<String, dynamic>.from(stats)
              : <String, dynamic>{};
        }
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  Future<void> _cancel(int investmentId) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Cancel Investment'),
        content: const Text(
            'Are you sure? Early cancellation may incur charges as per plan terms.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Keep it'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Yes, Cancel',
                style: TextStyle(color: Colors.red)),
          ),
        ],
      ),
    );
    if (confirmed != true) return;
    try {
      final result = await ApiService().post(
        '/user/investment/cancel',
        data: {'investment_id': investmentId, 'reason': 'Cancelled from app'},
      );
      if (!mounted) return;
      final ok = (result['success'] as bool?) ?? false;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(ok
              ? 'Investment cancelled'
              : (result['error']?.toString() ?? 'Cancellation failed')),
        ),
      );
      if (ok) _fetch();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $e')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('My Investments'), centerTitle: true),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.error_outline,
                          size: 64, color: Colors.red),
                      const SizedBox(height: 16),
                      Text('Error: $_error'),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: _fetch,
                        child: const Text('Retry'),
                      ),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _fetch,
                  child: ListView(
                    padding: const EdgeInsets.all(16),
                    children: [
                      _statsCard(),
                      const SizedBox(height: 16),
                      if (_investments.isEmpty)
                        const Center(
                          child: Padding(
                            padding: EdgeInsets.symmetric(vertical: 40),
                            child: Column(
                              children: [
                                Icon(Icons.savings_outlined,
                                    size: 64, color: Colors.grey),
                                SizedBox(height: 16),
                                Text('No investments yet'),
                                SizedBox(height: 8),
                                Text(
                                  'Start your investment journey today!',
                                  style: TextStyle(color: Colors.grey),
                                ),
                              ],
                            ),
                          ),
                        )
                      else
                        ..._investments.map(_investmentCard),
                    ],
                  ),
                ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push('/investment-plans'),
        icon: const Icon(Icons.add),
        label: const Text('New Investment'),
      ),
    );
  }

  Widget _statsCard() {
    final invested =
        ((_stats['total_invested'] as num?) ?? 0).toDouble();
    final value = ((_stats['total_value'] as num?) ?? 0).toDouble();
    final returns = value - invested;
    return GlassCard(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            Expanded(child: _stat('Invested', invested, Colors.blue)),
            Expanded(child: _stat('Current', value, Colors.green)),
            Expanded(
                child: _stat(
                    'Returns', returns, returns >= 0 ? Colors.teal : Colors.red)),
          ],
        ),
      ),
    );
  }

  Widget _stat(String label, double value, Color color) {
    return Column(
      children: [
        Text(
          '\u20B9${value.toStringAsFixed(0)}',
          style: TextStyle(
              fontSize: 16, fontWeight: FontWeight.bold, color: color),
        ),
        Text(label,
            style: TextStyle(color: Colors.grey[600], fontSize: 11)),
      ],
    );
  }

  Widget _investmentCard(Map<String, dynamic> inv) {
    final int id = (inv['id'] as num?)?.toInt() ?? 0;
    final String planName =
        inv['plan_name']?.toString() ?? 'Investment';
    final String ref = inv['investment_ref']?.toString() ?? '';
    final String status = inv['status']?.toString() ?? 'active';
    final double principal =
        ((inv['principal_amount'] as num?) ?? 0).toDouble();
    final double current =
        ((inv['current_value'] as num?) ?? principal).toDouble();
    final double gain = current - principal;
    final double gainPct =
        principal > 0 ? (gain / principal) * 100 : 0;

    return GlassCard(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(planName,
                          style: const TextStyle(
                              fontSize: 16, fontWeight: FontWeight.bold)),
                      Text('Ref: $ref',
                          style: TextStyle(
                              color: Colors.grey[600], fontSize: 12)),
                    ],
                  ),
                ),
                _statusChip(status),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                    child: _stat(
                        'Invested', principal, Colors.blue)),
                Expanded(
                    child: _stat('Current', current, Colors.green)),
                Expanded(
                  child: Column(
                    children: [
                      Text(
                        '${gain >= 0 ? '+' : ''}\u20B9${gain.toStringAsFixed(0)}',
                        style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                            color: gain >= 0 ? Colors.teal : Colors.red),
                      ),
                      Text('${gainPct.toStringAsFixed(1)}%',
                          style: TextStyle(
                              color: Colors.grey[600], fontSize: 11)),
                    ],
                  ),
                ),
              ],
            ),
            if (status == 'active') ...[
              const SizedBox(height: 12),
              SizedBox(
                width: double.infinity,
                child: OutlinedButton.icon(
                  onPressed: () => _cancel(id),
                  icon: const Icon(Icons.cancel_outlined, size: 18),
                  label: const Text('Cancel Investment'),
                  style: OutlinedButton.styleFrom(
                      foregroundColor: Colors.red),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _statusChip(String status) {
    final Color color;
    final IconData icon;
    switch (status) {
      case 'active':
        color = Colors.green;
        icon = Icons.check_circle;
        break;
      case 'matured':
        color = Colors.blue;
        icon = Icons.event_available;
        break;
      case 'cancelled':
        color = Colors.red;
        icon = Icons.cancel;
        break;
      default:
        color = Colors.grey;
        icon = Icons.help_outline;
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 13, color: color),
          const SizedBox(width: 4),
          Text(status.toUpperCase(),
              style: TextStyle(
                  color: color,
                  fontWeight: FontWeight.bold,
                  fontSize: 11)),
        ],
      ),
    );
  }
}

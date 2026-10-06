import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../../core/services/api_service.dart';
import '../../widgets/glass_card.dart';

class InvestmentPlansPage extends StatefulWidget {
  const InvestmentPlansPage({super.key});

  @override
  State<InvestmentPlansPage> createState() => _InvestmentPlansPageState();
}

class _InvestmentPlansPageState extends State<InvestmentPlansPage> {
  bool _isLoading = true;
  String? _error;
  List<Map<String, dynamic>> _plans = [];

  @override
  void initState() {
    super.initState();
    _fetchPlans();
  }

  Future<void> _fetchPlans() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final response = await ApiService().get('/investment-plans');
      final data = response['data'];
      if (!mounted) return;
      setState(() {
        _plans = (data is List)
            ? data.map((e) => Map<String, dynamic>.from(e as Map)).toList()
            : <Map<String, dynamic>>[];
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Investment Plans'), centerTitle: true),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.error_outline, size: 64, color: Colors.red),
                      const SizedBox(height: 16),
                      Text('Error: $_error'),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: _fetchPlans,
                        child: const Text('Retry'),
                      ),
                    ],
                  ),
                )
              : _plans.isEmpty
                  ? const Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.inventory_2_outlined, size: 64, color: Colors.grey),
                          SizedBox(height: 16),
                          Text('No investment plans available'),
                        ],
                      ),
                    )
                  : RefreshIndicator(
                      onRefresh: _fetchPlans,
                      child: ListView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: _plans.length,
                        itemBuilder: (context, index) =>
                            _buildPlanCard(context, _plans[index]),
                      ),
                    ),
    );
  }

  Widget _buildPlanCard(BuildContext context, Map<String, dynamic> plan) {
    final int id = (plan['id'] as num?)?.toInt() ?? 0;
    final String name = plan['plan_name']?.toString() ?? 'Investment Plan';
    final String description = plan['description']?.toString() ?? '';
    final double minAmount = (plan['min_amount'] as num?)?.toDouble() ?? 0;
    final double expectedReturn =
        (plan['expected_return_pct'] as num?)?.toDouble() ?? 0;
    final int tenure = (plan['tenure_months'] as num?)?.toInt() ?? 0;
    final String category =
        (plan['plan_category']?.toString() ?? 'general').toLowerCase();
    final String riskLevel =
        (plan['risk_level']?.toString() ?? 'low').toLowerCase();
    final bool featured = plan['is_featured'] == 1 || plan['is_featured'] == true;

    return GlassCard(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: _categoryColor(category).withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(
                    _categoryIcon(category),
                    color: _categoryColor(category),
                    size: 24,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        name,
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      Text(
                        category.toUpperCase(),
                        style: TextStyle(color: Colors.grey[600], fontSize: 12),
                      ),
                    ],
                  ),
                ),
                if (featured)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.amber.withValues(alpha: 0.2),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Text(
                      'FEATURED',
                      style: TextStyle(
                        color: Colors.amber,
                        fontSize: 10,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                Container(
                  margin: const EdgeInsets.only(left: 8),
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: _riskColor(riskLevel).withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    riskLevel.toUpperCase(),
                    style: TextStyle(
                      color: _riskColor(riskLevel),
                      fontSize: 10,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ],
            ),
            if (description.isNotEmpty) ...[
              const SizedBox(height: 8),
              Text(
                description,
                style: TextStyle(color: Colors.grey[700], fontSize: 13),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
            ],
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: _infoChip(
                    Icons.currency_rupee,
                    '\u20B9${minAmount.toStringAsFixed(0)} min',
                    Colors.green,
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: _infoChip(
                    Icons.percent,
                    '${expectedReturn.toStringAsFixed(1)}% p.a.',
                    Colors.blue,
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: _infoChip(
                    Icons.calendar_today,
                    '$tenure mo',
                    Colors.orange,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: () => context.push('/user/investments/new/$id'),
                icon: const Icon(Icons.arrow_forward, size: 18),
                label: const Text('View & Invest'),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _infoChip(IconData icon, String label, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 8),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icon, size: 14, color: color),
          const SizedBox(width: 4),
          Flexible(
            child: Text(
              label,
              style: TextStyle(
                color: color,
                fontWeight: FontWeight.bold,
                fontSize: 12,
              ),
              overflow: TextOverflow.ellipsis,
            ),
          ),
        ],
      ),
    );
  }

  Color _categoryColor(String category) {
    switch (category) {
      case 'sip':
        return Colors.blue;
      case 'lumpsum':
        return Colors.green;
      case 'real_estate_fund':
        return Colors.orange;
      case 'gold':
        return Colors.amber;
      default:
        return const Color(0xFF1A237E);
    }
  }

  IconData _categoryIcon(String category) {
    switch (category) {
      case 'sip':
        return Icons.sync_alt;
      case 'lumpsum':
        return Icons.account_balance_wallet;
      case 'real_estate_fund':
        return Icons.apartment;
      case 'gold':
        return Icons.monetization_on;
      default:
        return Icons.savings;
    }
  }

  Color _riskColor(String risk) {
    switch (risk) {
      case 'low':
        return Colors.green;
      case 'medium':
        return Colors.orange;
      case 'high':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }
}

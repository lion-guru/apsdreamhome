import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

import '../../../core/theme/app_theme.dart';
import '../../../data/repositories/kyc_repository_provider.dart';
import '../../widgets/app_widgets.dart';
import '../../widgets/glass_card.dart';

final _inr = NumberFormat('#,##,###');

/// Agent Salary Page - fixed salary structure + current month payroll.
/// Read-only; structures are managed by HR.
class AgentSalaryPage extends ConsumerWidget {
  const AgentSalaryPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final salaryAsync = ref.watch(_agentSalaryProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('My Salary'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded),
          onPressed: () => context.pop(),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            onPressed: () => ref.invalidate(_agentSalaryProvider),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async => ref.invalidate(_agentSalaryProvider),
        color: AppTheme.primaryColor,
        child: salaryAsync.when(
          data: (data) => (data['is_salaried'] != true)
              ? ListView(
                  children: const [
                    Padding(
                      padding: EdgeInsets.all(48),
                      child: Center(
                          child: Text(
                              'You are on a commission-only plan. Your fixed salary will appear here if HR moves you to salary.')),
                    ),
                  ],
                )
              : ListView(
                  padding: const EdgeInsets.all(16),
                  children: [
                    _StructureCard(
                        structure: Map<String, dynamic>.from(
                            data['structure'] as Map? ?? {})),
                    _PayrollCard(
                        payroll: Map<String, dynamic>.from(
                            data['payroll'] as Map? ?? {})),
                  ],
                ),
          loading: () => const Center(child: CircularProgressIndicator()),
          error: (error, stack) => AppWidgets.errorWidget(
            message: error.toString(),
            onRetry: () => ref.invalidate(_agentSalaryProvider),
          ),
        ),
      ),
    );
  }
}

String _money(dynamic v, {bool decimals = false}) {
  final n = double.tryParse('$v') ?? 0;
  return '₹${_inr.format(decimals ? (n * 100).round() / 100 : n.round())}';
}

class _StructureCard extends StatelessWidget {
  final Map<String, dynamic> structure;
  const _StructureCard({required this.structure});

  @override
  Widget build(BuildContext context) {
    return GlassCard(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Active Structure',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            _row('Basic salary', _money(structure['basic_salary'])),
            _row('HRA', _money(structure['hra'])),
            _row('TA/DA', _money(structure['ta_da'])),
            _row('Other allowance', _money(structure['other_allowance'])),
            _row(
                'Incentive',
                '${structure['incentive_type'] ?? ''} ${structure['incentive_value'] ?? ''}'),
            _row('Effective from', '${structure['effective_from'] ?? ''}'),
          ],
        ),
      ),
    );
  }
}

class _PayrollCard extends StatelessWidget {
  final Map<String, dynamic> payroll;
  const _PayrollCard({required this.payroll});

  @override
  Widget build(BuildContext context) {
    return GlassCard(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text("This Month's Payroll",
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            _row('Fixed gross', _money(payroll['gross_fixed'], decimals: true)),
            _row('Plots sold', '${payroll['plots_sold'] ?? 0}'),
            _row('Sale incentive',
                _money(payroll['total_incentive'], decimals: true)),
            _row('TDS deducted',
                _money(payroll['tds_deducted'], decimals: true)),
            const Divider(),
            _row('Net payable',
                _money(payroll['net_payable'], decimals: true),
                bold: true),
          ],
        ),
      ),
    );
  }
}

Widget _row(String label, String value, {bool bold = false}) {
  return Padding(
    padding: const EdgeInsets.symmetric(vertical: 4),
    child: Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label, style: const TextStyle(fontSize: 13)),
        Text(value,
            style: TextStyle(
                fontSize: 13,
                fontWeight: bold ? FontWeight.bold : FontWeight.w600)),
      ],
    ),
  );
}

final _agentSalaryProvider =
    FutureProvider<Map<String, dynamic>>((ref) async {
  final api = ref.watch(apiServiceProvider);
  return api.getAgentSalary();
});

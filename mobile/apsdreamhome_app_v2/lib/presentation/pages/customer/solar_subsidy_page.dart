import 'package:flutter/material.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

/// Result of the PM Surya Ghar feasibility calculation.
///
/// Mirrors `App\Services\SolarSubsidyService::calculateFeasibility()` exactly so
/// the mobile and web figures never disagree.
class _SolarResult {
  const _SolarResult({
    required this.capacityKw,
    required this.requiredRoofSqft,
    required this.availableRoofSqft,
    required this.areaFeasible,
    required this.monthlyUnits,
    required this.annualUnits,
    required this.projectCost,
    required this.centralSubsidy,
    required this.stateSubsidy,
    required this.totalSubsidy,
    required this.netPayable,
    required this.monthlySavings,
    required this.annualSavings,
    required this.paybackYears,
    required this.twentyFiveYearSavings,
  });

  final double capacityKw;
  final double requiredRoofSqft;
  final double availableRoofSqft;
  final bool areaFeasible;
  final int monthlyUnits;
  final int annualUnits;
  final double projectCost;
  final double centralSubsidy;
  final double stateSubsidy;
  final double totalSubsidy;
  final double netPayable;
  final double monthlySavings;
  final double annualSavings;
  final double paybackYears;
  final double twentyFiveYearSavings;

  static _SolarResult calculate(double plotSqft, double monthlyBill,
      {double? customKw}) {
    double capacityKw;
    if (customKw != null && customKw > 0) {
      capacityKw = (customKw * 10).round() / 10;
    } else if (monthlyBill <= 1500) {
      capacityKw = 1.0;
    } else if (monthlyBill <= 3000) {
      capacityKw = 2.0;
    } else if (monthlyBill <= 5500) {
      capacityKw = 3.0;
    } else {
      capacityKw = 5.0;
    }

    final reqRoofArea = capacityKw * 100.0;
    final estRoofArea = plotSqft * 0.45 < 200.0 ? 200.0 : plotSqft * 0.45;
    final areaFeasible = estRoofArea >= reqRoofArea;

    final monthlyUnits = (capacityKw * 120).round();
    final annualUnits = monthlyUnits * 12;

    const costPerKw = 60000.0;
    final projectCost = capacityKw * costPerKw;

    double centralSubsidy;
    if (capacityKw <= 1.0) {
      centralSubsidy = 33000.0 * capacityKw;
    } else if (capacityKw <= 2.0) {
      centralSubsidy = 33000.0 + ((capacityKw - 1.0) * 27000.0);
    } else {
      centralSubsidy = 78000.0;
    }

    final stateSubsidy = capacityKw <= 1.0 ? 15000.0 * capacityKw : 30000.0;

    final totalSubsidy = centralSubsidy + stateSubsidy;
    final netPayable = projectCost - totalSubsidy < 0
        ? 0.0
        : projectCost - totalSubsidy;

    const tariffRate = 7.00;
    final monthlySavings = monthlyUnits * tariffRate;
    final annualSavings = monthlySavings * 12;
    final paybackYears = annualSavings > 0 ? netPayable / annualSavings : 0.0;
    final twentyFiveYearSavings = (annualSavings * 25) - netPayable;

    return _SolarResult(
      capacityKw: capacityKw,
      requiredRoofSqft: reqRoofArea,
      availableRoofSqft: estRoofArea,
      areaFeasible: areaFeasible,
      monthlyUnits: monthlyUnits,
      annualUnits: annualUnits,
      projectCost: projectCost,
      centralSubsidy: centralSubsidy,
      stateSubsidy: stateSubsidy,
      totalSubsidy: totalSubsidy,
      netPayable: netPayable,
      monthlySavings: monthlySavings,
      annualSavings: annualSavings,
      paybackYears: paybackYears,
      twentyFiveYearSavings: twentyFiveYearSavings,
    );
  }
}

class SolarSubsidyPage extends StatefulWidget {
  const SolarSubsidyPage({super.key});

  @override
  State<SolarSubsidyPage> createState() => _SolarSubsidyPageState();
}

class _SolarSubsidyPageState extends State<SolarSubsidyPage> {
  final _plotController = TextEditingController(text: '2000');
  final _billController = TextEditingController(text: '2500');
  final _capacityController = TextEditingController();

  _SolarResult? _result;
  bool _loadingRequests = true;
  List<Map<String, dynamic>> _requests = [];

  @override
  void initState() {
    super.initState();
    _calculate();
    _loadRequests();
  }

  @override
  void dispose() {
    _plotController.dispose();
    _billController.dispose();
    _capacityController.dispose();
    super.dispose();
  }

  Future<void> _loadRequests() async {
    try {
      AppConstants.initBaseUrl();
      final res = await ApiService().get('/api/v2/mobile/solar/requests');
      if (res['success'] == true) {
        final list = (res['requests'] ?? []) as List;
        setState(() {
          _requests = list
              .map((e) => Map<String, dynamic>.from(e as Map))
              .toList();
        });
      }
    } catch (e) {
      debugPrint('solar requests load failed: $e');
    } finally {
      if (mounted) setState(() => _loadingRequests = false);
    }
  }

  void _calculate() {
    final plotSqft = double.tryParse(_plotController.text) ?? 0;
    final monthlyBill = double.tryParse(_billController.text) ?? 0;
    final customKw = double.tryParse(_capacityController.text);

    if (plotSqft <= 0 && monthlyBill <= 0 && customKw == null) {
      setState(() => _result = null);
      return;
    }

    setState(() {
      _result = _SolarResult.calculate(
        plotSqft > 0 ? plotSqft : 0,
        monthlyBill,
        customKw: customKw,
      );
    });
  }

  String _money(double v) {
    final s = v.round().toString();
    final buf = StringBuffer();
    for (var i = 0; i < s.length; i++) {
      if (i > 0 && (s.length - i) % 3 == 0) buf.write(',');
      buf.write(s[i]);
    }
    return '₹$buf';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.backgroundColor,
      appBar: AppBar(
        title: const Text(
          'PM Surya Ghar Solar',
          style: TextStyle(
            fontWeight: FontWeight.w700,
            color: AppTheme.primaryColor,
          ),
        ),
        backgroundColor: Colors.white,
        elevation: 0.5,
        iconTheme: const IconThemeData(color: AppTheme.primaryColor),
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
        children: [
          GlassCard(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      width: 44,
                      height: 44,
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [AppTheme.accentColor, AppTheme.goldColor],
                        ),
                        borderRadius: BorderRadius.circular(13),
                      ),
                      child: const Icon(Icons.wb_sunny_outlined,
                          color: Colors.white, size: 24),
                    ),
                    const SizedBox(width: 12),
                    const Expanded(
                      child: Text(
                        'Solar Subsidy Calculator',
                        style: TextStyle(
                          fontWeight: FontWeight.w800,
                          fontSize: 16,
                          color: AppTheme.primaryColor,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 6),
                const Text(
                  'Central subsidy up to ₹78,000 + UP state subsidy up to ₹30,000',
                  style: TextStyle(color: Colors.black54, fontSize: 12.5),
                ),
                const SizedBox(height: 18),
                _input('Rooftop / plot area (sq.ft)', _plotController),
                const SizedBox(height: 12),
                _input('Current monthly electricity bill (₹)', _billController),
                const SizedBox(height: 12),
                _input(
                  'Custom capacity (kW) — optional',
                  _capacityController,
                  hint: 'Leave blank for auto',
                ),
                const SizedBox(height: 16),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: _calculate,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppTheme.primaryColor,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    child: const Text(
                      'Calculate Savings',
                      style: TextStyle(
                        fontWeight: FontWeight.w700,
                        color: Colors.white,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),
          if (_result != null) _resultCard(_result!),
          const SizedBox(height: 24),
          const Text(
            'My Solar Requests',
            style: TextStyle(
              fontWeight: FontWeight.w800,
              fontSize: 16,
              color: AppTheme.primaryColor,
            ),
          ),
          const SizedBox(height: 12),
          _requestsCard(),
        ],
      ),
    );
  }

  Widget _input(String label, TextEditingController controller,
      {String? hint}) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label,
            style: const TextStyle(
                color: Colors.black54, fontSize: 12.5)),
        const SizedBox(height: 6),
        TextField(
          controller: controller,
          keyboardType:
              const TextInputType.numberWithOptions(decimal: true),
          onChanged: (_) => _calculate(),
          decoration: InputDecoration(
            hintText: hint,
            filled: true,
            fillColor: Colors.white,
            contentPadding:
                const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: AppTheme.goldBorder),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: AppTheme.goldBorder),
            ),
          ),
        ),
      ],
    );
  }

  Widget _resultCard(_SolarResult r) {
    return GlassCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.bolt_outlined,
                  color: AppTheme.accentColor, size: 22),
              const SizedBox(width: 8),
              Text(
                '${r.capacityKw.toStringAsFixed(1)} kW system recommended',
                style: const TextStyle(
                  fontWeight: FontWeight.w800,
                  fontSize: 15,
                  color: AppTheme.primaryColor,
                ),
              ),
            ],
          ),
          if (!r.areaFeasible) ...[
            const SizedBox(height: 10),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: AppTheme.warningColor.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Text(
                'Estimated rooftop (${r.availableRoofSqft.round()} sq.ft) is below the '
                '${r.requiredRoofSqft.round()} sq.ft this capacity needs. '
                'Consider a smaller system.',
                style: const TextStyle(
                    color: Color(0xFF92400E), fontSize: 12),
              ),
            ),
          ],
          const SizedBox(height: 16),
          _moneyRow('Project cost', _money(r.projectCost)),
          _moneyRow('Central subsidy (PM Surya Ghar)',
              '- ${_money(r.centralSubsidy)}'),
          _moneyRow('UP state subsidy', '- ${_money(r.stateSubsidy)}'),
          _moneyRow('Total subsidy', '- ${_money(r.totalSubsidy)}'),
          const Divider(height: 20, color: AppTheme.goldBorder),
          _moneyRow('Net payable', _money(r.netPayable), strong: true),
          const Divider(height: 20, color: AppTheme.goldBorder),
          _moneyRow('Est. monthly generation',
              '${r.monthlyUnits} units / month'),
          _moneyRow('Est. monthly savings', _money(r.monthlySavings)),
          _moneyRow('Est. annual savings', _money(r.annualSavings)),
          _moneyRow(
              'Payback period', '${r.paybackYears.toStringAsFixed(1)} years'),
          _moneyRow('25-year net savings',
              _money(r.twentyFiveYearSavings)),
        ],
      ),
    );
  }

  Widget _moneyRow(String label, String value, {bool strong = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 5),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Expanded(
            child: Text(
              label,
              style: TextStyle(
                color: strong ? AppTheme.primaryColor : Colors.black54,
                fontSize: 13,
                fontWeight: strong ? FontWeight.w700 : FontWeight.w400,
              ),
            ),
          ),
          const SizedBox(width: 12),
          Text(
            value,
            style: TextStyle(
              fontWeight: strong ? FontWeight.w800 : FontWeight.w600,
              color: strong ? AppTheme.successColor : AppTheme.primaryColor,
              fontSize: strong ? 15 : 13,
            ),
          ),
        ],
      ),
    );
  }

  Widget _requestsCard() {
    if (_loadingRequests) {
      return const GlassCard(
        child: Center(
          child: Padding(
            padding: EdgeInsets.all(16),
            child: CircularProgressIndicator(),
          ),
        ),
      );
    }

    if (_requests.isEmpty) {
      return const GlassCard(
        child: Column(
          children: [
            Icon(Icons.solar_power_outlined,
                size: 44, color: Colors.black26),
            SizedBox(height: 12),
            Text(
              'No solar subsidy applications yet',
              style: TextStyle(
                  fontWeight: FontWeight.w700,
                  color: AppTheme.primaryColor),
            ),
            SizedBox(height: 6),
            Text(
              'Calculate your savings above, then apply from the web portal or '
              'ask your executive to raise a request.',
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.black54, fontSize: 12.5),
            ),
          ],
        ),
      );
    }

    return Column(
      children: _requests.map(_requestTile).toList(),
    );
  }

  Widget _requestTile(Map<String, dynamic> r) {
    final status = (r['status'] ?? 'inquiry').toString();
    final token = (r['request_token'] ?? '').toString();
    final capacity = (r['system_capacity_kw'] ?? 0).toString();

    Color statusColor;
    switch (status) {
      case 'installed':
        statusColor = AppTheme.successColor;
        break;
      case 'rejected':
        statusColor = AppTheme.errorColor;
        break;
      case 'subsidy_approved':
      case 'discom_sanctioned':
        statusColor = AppTheme.infoColor;
        break;
      default:
        statusColor = AppTheme.warningColor;
    }

    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: GlassCard(
        child: Row(
          children: [
            Container(
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                color: statusColor.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(11),
              ),
              child: Icon(Icons.solar_power_outlined,
                  color: statusColor, size: 22),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    '$capacity kW · ${token.isEmpty ? '#${r['id'] ?? ''}' : token}',
                    style: const TextStyle(
                      fontWeight: FontWeight.w800,
                      fontSize: 13,
                      color: AppTheme.primaryColor,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    status.replaceAll('_', ' ').toUpperCase(),
                    style: TextStyle(
                      color: statusColor,
                      fontSize: 11.5,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

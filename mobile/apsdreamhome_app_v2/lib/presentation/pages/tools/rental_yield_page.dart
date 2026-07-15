import 'package:flutter/material.dart';
import 'package:share_plus/share_plus.dart';
import '../../../core/theme/app_theme.dart';

class RentalYieldPage extends StatefulWidget {
  const RentalYieldPage({super.key});

  @override
  State<RentalYieldPage> createState() => _RentalYieldPageState();
}

class _RentalYieldPageState extends State<RentalYieldPage> {
  final _propertyValueController = TextEditingController();
  final _monthlyRentController = TextEditingController();
  final _expensesController = TextEditingController(text: '15');
  final _annualMaintenanceController = TextEditingController(text: '0');

  bool _calculated = false;
  double _grossYield = 0,
      _netYield = 0,
      _annualRent = 0,
      _annualExpenses = 0,
      _netIncome = 0;

  @override
  void dispose() {
    _propertyValueController.dispose();
    _monthlyRentController.dispose();
    _expensesController.dispose();
    _annualMaintenanceController.dispose();
    super.dispose();
  }

  void _calculate() {
    final pv = double.tryParse(_propertyValueController.text) ?? 0;
    final rent = double.tryParse(_monthlyRentController.text) ?? 0;
    final expPct = double.tryParse(_expensesController.text) ?? 15;
    final maint = double.tryParse(_annualMaintenanceController.text) ?? 0;

    if (pv <= 0 || rent <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text('Enter property value and monthly rent'),
          backgroundColor: AppTheme.errorColor,
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    _annualRent = rent * 12;
    _grossYield = (_annualRent / pv) * 100;
    _annualExpenses = _annualRent * (expPct / 100) + maint;
    _netIncome = _annualRent - _annualExpenses;
    _netYield = (_netIncome / pv) * 100;
    setState(() => _calculated = true);
  }

  void _share() {
    Share.share(
      'Rental Yield Calculation\n'
      'Property Value: ₹${_format(_propertyValueController.text)}\n'
      'Gross Yield: ${_grossYield.toStringAsFixed(2)}%\n'
      'Net Yield: ${_netYield.toStringAsFixed(2)}%',
    );
  }

  String _format(String raw) {
    final n = double.tryParse(raw) ?? 0;
    if (n >= 10000000) return '${(n / 10000000).toStringAsFixed(2)} Cr';
    if (n >= 100000) return '${(n / 100000).toStringAsFixed(2)} L';
    return n.toStringAsFixed(0);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Rental Yield Calculator'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        actions: [
          _calculated
              ? IconButton(icon: const Icon(Icons.share), onPressed: _share)
              : const SizedBox(),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildHeader(),
            const SizedBox(height: 24),
            _buildInputFields(),
            const SizedBox(height: 20),
            _buildCalculateButton(),
            if (_calculated) ...[
              const SizedBox(height: 24),
              _buildResults(),
              const SizedBox(height: 16),
              _buildYieldMeter(),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF8E24AA), Color(0xFFBA68C8)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(Icons.trending_up_rounded, size: 40, color: Colors.white),
          const SizedBox(height: 12),
          const Text(
            'Rental Yield',
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Calculate ROI on rental property',
            style: TextStyle(
              fontSize: 13,
              color: Colors.white.withValues(alpha: 0.85),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInputFields() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Property Details',
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _propertyValueController,
            keyboardType: TextInputType.number,
            decoration: const InputDecoration(
              labelText: 'Property Value (₹)',
              prefixIcon: Icon(Icons.home_rounded),
              border: OutlineInputBorder(),
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _monthlyRentController,
            keyboardType: TextInputType.number,
            decoration: const InputDecoration(
              labelText: 'Monthly Rent (₹)',
              prefixIcon: Icon(Icons.currency_rupee_rounded),
              border: OutlineInputBorder(),
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _expensesController,
            keyboardType: TextInputType.number,
            decoration: const InputDecoration(
              labelText: 'Expenses (% of rent)',
              prefixIcon: Icon(Icons.payments_rounded),
              border: OutlineInputBorder(),
              suffixText: '%',
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _annualMaintenanceController,
            keyboardType: TextInputType.number,
            decoration: const InputDecoration(
              labelText: 'Annual Maintenance (₹)',
              prefixIcon: Icon(Icons.build_rounded),
              border: OutlineInputBorder(),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCalculateButton() {
    return SizedBox(
      width: double.infinity,
      height: 50,
      child: ElevatedButton(
        onPressed: _calculate,
        style: ElevatedButton.styleFrom(
          backgroundColor: AppTheme.primaryColor,
          foregroundColor: Colors.white,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
        child: const Text(
          'Calculate Yield',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
        ),
      ),
    );
  }

  Widget _buildResults() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [const Color(0xFF8E24AA), const Color(0xFF6A1B9A)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Yield Analysis',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w700,
              color: Colors.white,
            ),
          ),
          const Divider(color: Colors.white24, height: 24),
          _resultRow(
            'Annual Rent',
            '₹${_annualRent.toStringAsFixed(0)}',
            Colors.white,
          ),
          const SizedBox(height: 8),
          _resultRow(
            'Annual Expenses',
            '₹${_annualExpenses.toStringAsFixed(0)}',
            Colors.redAccent.shade200,
          ),
          const SizedBox(height: 8),
          _resultRow(
            'Net Income',
            '₹${_netIncome.toStringAsFixed(0)}',
            Colors.greenAccent,
          ),
          const Divider(color: Colors.white24, height: 20),
          _resultRow(
            'Gross Yield',
            '${_grossYield.toStringAsFixed(2)}%',
            Colors.amberAccent,
          ),
          const SizedBox(height: 8),
          _resultRow(
            'Net Yield',
            '${_netYield.toStringAsFixed(2)}%',
            Colors.lightBlueAccent,
          ),
        ],
      ),
    );
  }

  Widget _buildYieldMeter() {
    final pct = _netYield.clamp(0, 15);
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Yield Rating',
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: Colors.grey.shade700,
            ),
          ),
          const SizedBox(height: 10),
          ClipRRect(
            borderRadius: BorderRadius.circular(6),
            child: LinearProgressIndicator(
              value: pct / 15,
              minHeight: 10,
              backgroundColor: Colors.grey.shade200,
              valueColor: AlwaysStoppedAnimation(
                _netYield >= 8
                    ? Colors.green
                    : _netYield >= 4
                    ? Colors.orange
                    : Colors.red,
              ),
            ),
          ),
          const SizedBox(height: 8),
          Text(
            _netYield >= 8
                ? 'Excellent Yield'
                : _netYield >= 4
                ? 'Good Yield'
                : 'Below Average Yield',
            style: TextStyle(color: Colors.grey.shade600, fontSize: 12),
          ),
        ],
      ),
    );
  }

  Widget _resultRow(String label, String value, Color color) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: TextStyle(
            color: Colors.white.withValues(alpha: 0.8),
            fontSize: 14,
          ),
        ),
        Text(
          value,
          style: TextStyle(
            color: color,
            fontSize: 15,
            fontWeight: FontWeight.w700,
          ),
        ),
      ],
    );
  }
}

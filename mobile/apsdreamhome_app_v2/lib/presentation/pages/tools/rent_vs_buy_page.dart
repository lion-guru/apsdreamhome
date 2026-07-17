import 'dart:math';
import 'package:flutter/material.dart';
import 'package:share_plus/share_plus.dart';
import '../../../core/theme/app_theme.dart';

class RentVsBuyPage extends StatefulWidget {
  const RentVsBuyPage({super.key});

  @override
  State<RentVsBuyPage> createState() => _RentVsBuyPageState();
}

class _RentVsBuyPageState extends State<RentVsBuyPage> {
  final _priceController = TextEditingController();
  final _monthlyRentController = TextEditingController();
  final _downPaymentController = TextEditingController();
  final _loanRateController = TextEditingController(text: '8.5');
  final _maintenanceController = TextEditingController(text: '2000');

  double _rentGrowth = 5;
  double _appreciation = 6;
  int _tenure = 10;

  bool _calculated = false;
  double _totalRentPaid = 0,
      _futurePropertyValue = 0,
      _totalBuyCost = 0,
      _netWealthRent = 0,
      _netWealthBuy = 0;

  @override
  void dispose() {
    _priceController.dispose();
    _monthlyRentController.dispose();
    _downPaymentController.dispose();
    _loanRateController.dispose();
    _maintenanceController.dispose();
    super.dispose();
  }

  void _calculate() {
    final price = double.tryParse(_priceController.text) ?? 0;
    final rent = double.tryParse(_monthlyRentController.text) ?? 0;
    final down = double.tryParse(_downPaymentController.text) ?? (price * 0.2);
    final rate = double.tryParse(_loanRateController.text) ?? 8.5;
    final maint = double.tryParse(_maintenanceController.text) ?? 2000;

    if (price <= 0 || rent <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Enter property price and monthly rent'),
          backgroundColor: AppTheme.errorColor,
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    final monthlyRate = (rate / 12) / 100;
    final totalMonths = _tenure * 12;
    final loanAmount = price - down;

    double emi = 0;
    if (loanAmount > 0 && monthlyRate > 0) {
      emi =
          loanAmount *
          monthlyRate *
          pow(1 + monthlyRate, totalMonths) /
          (pow(1 + monthlyRate, totalMonths) - 1);
    }
    _totalBuyCost = down + (emi * totalMonths) + (maint * totalMonths);

    _futurePropertyValue = price * pow(1 + _appreciation / 100, _tenure);
    _netWealthBuy = _futurePropertyValue - _totalBuyCost;

    _totalRentPaid = 0;
    double currentRent = rent;
    for (int y = 0; y < _tenure; y++) {
      _totalRentPaid += currentRent * 12;
      currentRent *= (1 + _rentGrowth / 100);
    }
    _netWealthRent = -_totalRentPaid;

    setState(() => _calculated = true);
  }

  void _share() {
    Share.share(
      'Rent vs Buy Analysis\n'
      'Tenure: $_tenure years\n'
      'Rent Total: ₹${_format(_totalRentPaid)}\n'
      'Future Value: ₹${_format(_futurePropertyValue)}\n'
      'Buy Net Wealth: ₹${_format(_netWealthBuy)}\n'
      'Rent Net Wealth: ₹${_format(_netWealthRent)}',
    );
  }

  String _format(double n) {
    if (n >= 10000000) return '${(n / 10000000).toStringAsFixed(2)} Cr';
    if (n >= 100000) return '${(n / 100000).toStringAsFixed(2)} L';
    return n.toStringAsFixed(0);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Rent vs Buy'),
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
            const SizedBox(height: 16),
            _buildSliders(),
            const SizedBox(height: 20),
            _buildCalculateButton(),
            if (_calculated) ...[const SizedBox(height: 24), _buildResults()],
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
          colors: [Color(0xFFE53935), Color(0xFFEF5350)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(
            Icons.compare_arrows_rounded,
            size: 40,
            color: Colors.white,
          ),
          const SizedBox(height: 12),
          const Text(
            'Rent vs Buy',
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Which option is better for you?',
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
            'Financial Details',
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _priceController,
            keyboardType: TextInputType.number,
            decoration: const InputDecoration(
              labelText: 'Property Price (₹)',
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
            controller: _downPaymentController,
            keyboardType: TextInputType.number,
            decoration: const InputDecoration(
              labelText: 'Down Payment (₹)',
              prefixIcon: Icon(Icons.account_balance_rounded),
              border: OutlineInputBorder(),
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _loanRateController,
            keyboardType: TextInputType.number,
            decoration: const InputDecoration(
              labelText: 'Loan Interest Rate (%)',
              prefixIcon: Icon(Icons.percent_rounded),
              border: OutlineInputBorder(),
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _maintenanceController,
            keyboardType: TextInputType.number,
            decoration: const InputDecoration(
              labelText: 'Monthly Maintenance (₹)',
              prefixIcon: Icon(Icons.build_rounded),
              border: OutlineInputBorder(),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSliders() {
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
          _buildSlider(
            'Tenure (years)',
            _tenure,
            1,
            30,
            (v) => setState(() => _tenure = v),
          ),
          const SizedBox(height: 8),
          _buildSlider(
            'Rent Growth (% p.a.)',
            _rentGrowth.round(),
            0,
            15,
            (v) => setState(() => _rentGrowth = v.toDouble()),
          ),
          const SizedBox(height: 8),
          _buildSlider(
            'Property Appreciation (% p.a.)',
            _appreciation.round(),
            0,
            20,
            (v) => setState(() => _appreciation = v.toDouble()),
          ),
        ],
      ),
    );
  }

  Widget _buildSlider(
    String label,
    int value,
    int min,
    int max,
    ValueChanged<int> onChanged,
  ) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              label,
              style: TextStyle(fontSize: 13, color: Colors.grey.shade700),
            ),
            Text(
              '$value',
              style: const TextStyle(
                fontWeight: FontWeight.w700,
                color: AppTheme.primaryColor,
              ),
            ),
          ],
        ),
        Slider(
          value: value.toDouble(),
          min: min.toDouble(),
          max: max.toDouble(),
          divisions: max - min,
          activeColor: AppTheme.primaryColor,
          onChanged: (v) => onChanged(v.round()),
        ),
      ],
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
          'Compare',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
        ),
      ),
    );
  }

  Widget _buildResults() {
    final buyWins = _netWealthBuy > _netWealthRent;
    return Column(
      children: [
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [Color(0xFFE53935), Color(0xFFC62828)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(16),
          ),
          child: Column(
            children: [
              Icon(
                buyWins ? Icons.home_rounded : Icons.meeting_room_rounded,
                size: 48,
                color: Colors.white,
              ),
              const SizedBox(height: 8),
              Text(
                buyWins ? 'Buying is Better' : 'Renting is Better',
                style: const TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.w800,
                  color: Colors.white,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                buyWins
                    ? 'Over $_tenure years, buying builds more wealth'
                    : 'Over $_tenure years, renting costs less',
                style: TextStyle(
                  color: Colors.white.withValues(alpha: 0.8),
                  fontSize: 13,
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),
        Container(
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
            children: [
              _resultRow(
                'Total Rent Paid',
                '₹${_format(_totalRentPaid)}',
                Colors.redAccent,
              ),
              const Divider(height: 20),
              _resultRow(
                'Property Future Value',
                '₹${_format(_futurePropertyValue)}',
                Colors.greenAccent.shade700,
              ),
              _resultRow(
                'Total Buying Cost',
                '₹${_format(_totalBuyCost)}',
                Colors.orangeAccent,
              ),
              const Divider(height: 20),
              _resultRow(
                'Net Wealth (Rent)',
                '₹${_format(_netWealthRent)}',
                Colors.redAccent,
              ),
              _resultRow(
                'Net Wealth (Buy)',
                '₹${_format(_netWealthBuy)}',
                Colors.greenAccent.shade700,
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _resultRow(String label, String value, Color color) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: TextStyle(color: Colors.grey.shade700, fontSize: 13),
          ),
          Text(
            value,
            style: TextStyle(
              color: color,
              fontSize: 14,
              fontWeight: FontWeight.w700,
            ),
          ),
        ],
      ),
    );
  }
}

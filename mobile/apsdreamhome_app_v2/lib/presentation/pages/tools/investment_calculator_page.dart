import 'package:flutter/material.dart';
import 'dart:math';

import '../../../core/constants/app_constants.dart';

/// Investment Calculator Page
/// Calculate property investment returns including EMI, ROI, break-even analysis
class InvestmentCalculatorPage extends StatefulWidget {
  const InvestmentCalculatorPage({super.key});

  @override
  State<InvestmentCalculatorPage> createState() =>
      _InvestmentCalculatorPageState();
}

class _InvestmentCalculatorPageState extends State<InvestmentCalculatorPage> {
  final _priceController = TextEditingController(text: '5000000');
  final _rentController = TextEditingController(text: '15000');

  double _propertyPrice = 5000000;
  double _downPaymentPct = 20;
  int _loanTermYears = 20;
  double _interestRate = 8.5;
  double _monthlyRent = 15000;
  double _appreciationRate = 5;

  // Results
  double _downPaymentAmount = 0;
  double _loanAmount = 0;
  double _monthlyPayment = 0;
  double _totalInterest = 0;
  double _totalPayment = 0;
  double _annualRentIncome = 0;
  double _grossRentalYield = 0;
  double _netRentalYield = 0;
  double _breakEvenYears = 0;
  double _fiveYearROI = 0;
  double _tenYearROI = 0;
  double _propertyValueIn10Yr = 0;

  @override
  void initState() {
    super.initState();
    _calculate();
  }

  @override
  void dispose() {
    _priceController.dispose();
    _rentController.dispose();
    super.dispose();
  }

  void _calculate() {
    _propertyPrice = double.tryParse(_priceController.text) ?? 5000000;
    _monthlyRent = double.tryParse(_rentController.text) ?? 15000;

    _downPaymentAmount = _propertyPrice * (_downPaymentPct / 100);
    _loanAmount = _propertyPrice - _downPaymentAmount;

    // EMI calculation (reducing balance)
    if (_loanAmount > 0 && _interestRate > 0) {
      double monthlyRate = _interestRate / 100 / 12;
      int months = _loanTermYears * 12;
      _monthlyPayment =
          _loanAmount *
          monthlyRate *
          pow(1 + monthlyRate, months) /
          (pow(1 + monthlyRate, months) - 1);
      _totalPayment = _monthlyPayment * months;
      _totalInterest = _totalPayment - _loanAmount;
    } else {
      _monthlyPayment = _loanAmount / (_loanTermYears * 12);
      _totalPayment = _loanAmount;
      _totalInterest = 0;
    }

    // Rental yield
    _annualRentIncome = _monthlyRent * 12;
    _grossRentalYield = _propertyPrice > 0
        ? (_annualRentIncome / _propertyPrice) * 100
        : 0;
    _netRentalYield = _grossRentalYield * 0.85; // ~15% expenses

    // Break-even (years to recover down payment via rent)
    _breakEvenYears = _monthlyRent > 0
        ? _downPaymentAmount / (_monthlyRent * 12)
        : 999;

    // Property appreciation
    _propertyValueIn10Yr =
        _propertyPrice * pow(1 + _appreciationRate / 100, 10);
    double capitalGain10Yr = _propertyValueIn10Yr - _propertyPrice;
    double totalRent10Yr = _annualRentIncome * 10;
    double totalCost10Yr = _downPaymentAmount + (_monthlyPayment * 120);
    double totalReturn10Yr = capitalGain10Yr + totalRent10Yr;
    _tenYearROI = totalCost10Yr > 0
        ? ((totalReturn10Yr / totalCost10Yr) * 100)
        : 0;

    // 5-year ROI
    double propertyValue5Yr =
        _propertyPrice * pow(1 + _appreciationRate / 100, 5);
    double capitalGain5Yr = propertyValue5Yr - _propertyPrice;
    double totalRent5Yr = _annualRentIncome * 5;
    double totalCost5Yr = _downPaymentAmount + (_monthlyPayment * 60);
    double totalReturn5Yr = capitalGain5Yr + totalRent5Yr;
    _fiveYearROI = totalCost5Yr > 0
        ? ((totalReturn5Yr / totalCost5Yr) * 100)
        : 0;

    setState(() {});
  }

  String _formatINR(double amount) {
    if (amount >= 10000000) {
      return '₹${(amount / 10000000).toStringAsFixed(2)} Cr';
    } else if (amount >= 100000) {
      return '₹${(amount / 100000).toStringAsFixed(2)} L';
    }
    return '₹${amount.toStringAsFixed(0)}';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0F0F23),
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios, color: Colors.white70),
          onPressed: () => Navigator.pop(context),
        ),
        title: ShaderMask(
          shaderCallback: (bounds) => const LinearGradient(
            colors: [Color(0xFFFFD700), Color(0xFFFF8C00)],
          ).createShader(bounds),
          child: const Text(
            'Investment Calculator',
            style: TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.bold,
              fontSize: 20,
            ),
          ),
        ),
        centerTitle: true,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Property Price
            _buildInputCard(
              'Property Price (₹)',
              _priceController,
              Icons.home,
              onChange: (v) {
                _propertyPrice = double.tryParse(v) ?? 5000000;
                _calculate();
              },
            ),

            const SizedBox(height: 16),

            // Down Payment Slider
            _buildSliderCard(
              'Down Payment',
              _downPaymentPct,
              10,
              50,
              '%',
              const Color(0xFF4FC3F7),
              (v) {
                _downPaymentPct = v;
                _calculate();
              },
            ),

            const SizedBox(height: 16),

            // Loan Term Slider
            _buildSliderCard(
              'Loan Term',
              _loanTermYears.toDouble(),
              5,
              30,
              ' years',
              const Color(0xFF81C784),
              (v) {
                _loanTermYears = v.toInt();
                _calculate();
              },
            ),

            const SizedBox(height: 16),

            // Interest Rate Slider
            _buildSliderCard(
              'Interest Rate',
              _interestRate,
              5,
              15,
              '% p.a.',
              const Color(0xFFFFB74D),
              (v) {
                _interestRate = v;
                _calculate();
              },
              isDouble: true,
            ),

            const SizedBox(height: 16),

            // Monthly Rent
            _buildInputCard(
              'Expected Monthly Rent (₹)',
              _rentController,
              Icons.apartment,
              onChange: (v) {
                _monthlyRent = double.tryParse(v) ?? 15000;
                _calculate();
              },
            ),

            const SizedBox(height: 16),

            // Appreciation Rate Slider
            _buildSliderCard(
              'Appreciation Rate',
              _appreciationRate,
              0,
              20,
              '% p.a.',
              const Color(0xFFCE93D8),
              (v) {
                _appreciationRate = v;
                _calculate();
              },
              isDouble: true,
            ),

            const SizedBox(height: 24),

            // Results
            _buildResultsSection(),

            const SizedBox(height: 16),

            // ROI Analysis
            _buildROISection(),

            const SizedBox(height: 16),

            // Break-even
            _buildBreakEvenSection(),

            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }

  Widget _buildInputCard(
    String label,
    TextEditingController controller,
    IconData icon, {
    Function(String)? onChange,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.05),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.white.withOpacity(0.1)),
      ),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Row(
        children: [
          Icon(icon, color: const Color(0xFFFFD700), size: 20),
          const SizedBox(width: 12),
          Expanded(
            child: TextField(
              controller: controller,
              keyboardType: TextInputType.number,
              style: const TextStyle(color: Colors.white, fontSize: 16),
              decoration: InputDecoration(
                labelText: label,
                labelStyle: TextStyle(color: Colors.white.withOpacity(0.5)),
                border: InputBorder.none,
              ),
              onChanged: onChange,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSliderCard(
    String label,
    double value,
    double min,
    double max,
    String suffix,
    Color color,
    Function(double) onChanged, {
    bool isDouble = false,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.05),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.white.withOpacity(0.1)),
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                label,
                style: TextStyle(
                  color: Colors.white.withOpacity(0.7),
                  fontSize: 14,
                ),
              ),
              Text(
                isDouble
                    ? '${value.toStringAsFixed(1)}$suffix'
                    : '${value.toInt()}$suffix',
                style: TextStyle(
                  color: color,
                  fontWeight: FontWeight.bold,
                  fontSize: 16,
                ),
              ),
            ],
          ),
          SliderTheme(
            data: SliderThemeData(
              activeTrackColor: color,
              inactiveTrackColor: color.withOpacity(0.2),
              thumbColor: color,
              overlayColor: color.withOpacity(0.1),
            ),
            child: Slider(
              value: value,
              min: min,
              max: max,
              divisions: isDouble ? 100 : (max - min).toInt(),
              onChanged: onChanged,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildResultsSection() {
    return Container(
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            const Color(0xFFFFD700).withOpacity(0.15),
            const Color(0xFFFF8C00).withOpacity(0.15),
          ],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFFFD700).withOpacity(0.3)),
      ),
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Investment Summary',
            style: TextStyle(
              color: Color(0xFFFFD700),
              fontSize: 18,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 16),
          _buildResultRow(
            'Monthly EMI',
            _formatINR(_monthlyPayment),
            const Color(0xFF4FC3F7),
          ),
          _buildResultRow(
            'Down Payment',
            _formatINR(_downPaymentAmount),
            const Color(0xFF81C784),
          ),
          _buildResultRow(
            'Loan Amount',
            _formatINR(_loanAmount),
            const Color(0xFFFFB74D),
          ),
          _buildResultRow(
            'Total Interest',
            _formatINR(_totalInterest),
            const Color(0xFFE57373),
          ),
          const Divider(color: Colors.white24, height: 24),
          _buildResultRow(
            'Total Cost (EMI + Down)',
            _formatINR(_downPaymentAmount + _totalPayment),
            Colors.white,
          ),
        ],
      ),
    );
  }

  Widget _buildROISection() {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.05),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.white.withOpacity(0.1)),
      ),
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'ROI Analysis',
            style: TextStyle(
              color: Color(0xFF81C784),
              fontSize: 18,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Expanded(
                child: _buildROICard(
                  '5-Year ROI',
                  '${_fiveYearROI.toStringAsFixed(1)}%',
                  _fiveYearROI > 0
                      ? const Color(0xFF81C784)
                      : const Color(0xFFE57373),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _buildROICard(
                  '10-Year ROI',
                  '${_tenYearROI.toStringAsFixed(1)}%',
                  _tenYearROI > 0
                      ? const Color(0xFF4FC3F7)
                      : const Color(0xFFE57373),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: _buildROICard(
                  'Gross Yield',
                  '${_grossRentalYield.toStringAsFixed(1)}%',
                  const Color(0xFFFFB74D),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _buildROICard(
                  'Net Yield',
                  '${_netRentalYield.toStringAsFixed(1)}%',
                  const Color(0xFFCE93D8),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          _buildResultRow(
            'Property Value in 10 Years',
            _formatINR(_propertyValueIn10Yr),
            const Color(0xFF81C784),
          ),
          _buildResultRow(
            'Annual Rent Income',
            _formatINR(_annualRentIncome),
            const Color(0xFF4FC3F7),
          ),
        ],
      ),
    );
  }

  Widget _buildROICard(String label, String value, Color color) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: color.withOpacity(0.3)),
      ),
      child: Column(
        children: [
          Text(
            value,
            style: TextStyle(
              color: color,
              fontSize: 18,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            label,
            style: TextStyle(
              color: Colors.white.withOpacity(0.6),
              fontSize: 12,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBreakEvenSection() {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.05),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.white.withOpacity(0.1)),
      ),
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Break-Even Analysis',
            style: TextStyle(
              color: Color(0xFFCE93D8),
              fontSize: 18,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 16),
          Center(
            child: Column(
              children: [
                Text(
                  _breakEvenYears < 999
                      ? '${_breakEvenYears.toStringAsFixed(1)} Years'
                      : 'N/A',
                  style: TextStyle(
                    color: _breakEvenYears < 30
                        ? const Color(0xFF81C784)
                        : const Color(0xFFE57373),
                    fontSize: 36,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  _breakEvenYears < 999
                      ? 'Time to recover down payment via rent'
                      : 'Add monthly rent to calculate break-even',
                  style: TextStyle(
                    color: Colors.white.withOpacity(0.5),
                    fontSize: 13,
                  ),
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),
          _buildResultRow(
            'Down Payment',
            _formatINR(_downPaymentAmount),
            Colors.white70,
          ),
          _buildResultRow(
            'Monthly Rent',
            _formatINR(_monthlyRent),
            Colors.white70,
          ),
          _buildResultRow(
            'Annual Rent',
            _formatINR(_annualRentIncome),
            Colors.white70,
          ),
        ],
      ),
    );
  }

  Widget _buildResultRow(String label, String value, Color valueColor) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: TextStyle(
              color: Colors.white.withOpacity(0.6),
              fontSize: 14,
            ),
          ),
          Text(
            value,
            style: TextStyle(
              color: valueColor,
              fontWeight: FontWeight.bold,
              fontSize: 15,
            ),
          ),
        ],
      ),
    );
  }
}

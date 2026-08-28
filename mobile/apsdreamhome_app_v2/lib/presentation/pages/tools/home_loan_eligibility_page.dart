import 'package:flutter/material.dart';
import 'dart:math';
import '../../../core/theme/app_theme.dart';

class HomeLoanEligibilityPage extends StatefulWidget {
  const HomeLoanEligibilityPage({super.key});

  @override
  State<HomeLoanEligibilityPage> createState() =>
      _HomeLoanEligibilityPageState();
}

class _HomeLoanEligibilityPageState extends State<HomeLoanEligibilityPage> {
  final _monthlyIncomeController = TextEditingController();
  final _existingEmiController = TextEditingController(text: '0');
  final _interestRateController = TextEditingController(text: '8.5');

  double _monthlyIncome = 50000;
  double _existingEmi = 0;
  double _interestRate = 8.5;
  int _tenureYears = 20;
  double _maxLoanAmount = 0;
  double _maxEmi = 0;
  double _emiForSuggested = 0;
  double _suggestedLoan = 0;
  bool _calculated = false;

  final Map<String, double> _bankRates = {
    'SBI': 8.45,
    'HDFC': 8.50,
    'ICICI': 8.55,
    'Axis': 8.60,
    'PNB': 8.65,
    'BOB': 8.50,
    'Custom': 0.0,
  };
  String _selectedBank = 'SBI';

  @override
  void initState() {
    super.initState();
    _monthlyIncomeController.text = _monthlyIncome.toStringAsFixed(0);
    _existingEmiController.text = _existingEmi.toStringAsFixed(0);
  }

  @override
  void dispose() {
    _monthlyIncomeController.dispose();
    _existingEmiController.dispose();
    _interestRateController.dispose();
    super.dispose();
  }

  void _calculate() {
    final double income = double.tryParse(_monthlyIncomeController.text) ?? 0;
    final double existing = double.tryParse(_existingEmiController.text) ?? 0;
    final double rate = double.tryParse(_interestRateController.text) ?? 8.5;

    // Typically 50% of monthly income can go toward EMI (FOIR norm)
    final double maxEmi = (income * 0.50) - existing;
    final int totalMonths = _tenureYears * 12;
    final double monthlyRate = (rate / 12) / 100;

    double maxLoan = 0;
    if (maxEmi > 0 && monthlyRate > 0) {
      maxLoan =
          maxEmi *
          (pow(1 + monthlyRate, totalMonths) - 1) /
          (monthlyRate * pow(1 + monthlyRate, totalMonths));
    }

    // Suggested loan = 80% of max (conservative)
    final double suggested = maxLoan * 0.80;
    double suggestedEmi = 0;
    if (suggested > 0 && monthlyRate > 0) {
      suggestedEmi =
          suggested *
          monthlyRate *
          pow(1 + monthlyRate, totalMonths) /
          (pow(1 + monthlyRate, totalMonths) - 1);
    }

    setState(() {
      _monthlyIncome = income;
      _existingEmi = existing;
      _interestRate = rate;
      _maxLoanAmount = maxLoan;
      _maxEmi = maxEmi;
      _suggestedLoan = suggested;
      _emiForSuggested = suggestedEmi;
      _calculated = true;
    });
  }

  void _selectBank(String bank) {
    setState(() {
      _selectedBank = bank;
      if (bank != 'Custom') {
        _interestRate = _bankRates[bank]!;
        _interestRateController.text = _interestRate.toStringAsFixed(2);
      }
    });
  }

  String _formatAmount(double amt) {
    if (amt >= 10000000) return '₹${(amt / 10000000).toStringAsFixed(2)} Cr';
    if (amt >= 100000) return '₹${(amt / 100000).toStringAsFixed(2)} L';
    return '₹${amt.toStringAsFixed(0)}';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Home Loan Eligibility'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildHeader(),
            const SizedBox(height: 24),
            _buildBankSelection(),
            const SizedBox(height: 24),
            _buildInputFields(),
            const SizedBox(height: 24),
            _buildTenureSlider(),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              height: 52,
              child: ElevatedButton(
                onPressed: _calculate,
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.accentColor,
                  foregroundColor: Colors.black,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  elevation: 2,
                ),
                child: const Text(
                  'Check Eligibility',
                  style: TextStyle(fontSize: 17, fontWeight: FontWeight.w600),
                ),
              ),
            ),
            const SizedBox(height: 24),
            if (_calculated) ...[
              _buildResultsCard(),
              const SizedBox(height: 16),
              _buildSuggestedCard(),
              const SizedBox(height: 24),
              _buildEligibilityMeter(),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF1A237E), Color(0xFF3949AB)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(
            Icons.account_balance_rounded,
            size: 48,
            color: Colors.white,
          ),
          const SizedBox(height: 12),
          const Text(
            'Home Loan Eligibility Calculator',
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Know how much loan you qualify for based on your income',
            style: TextStyle(
              fontSize: 14,
              color: Colors.white.withValues(alpha: 0.9),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBankSelection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Select Bank',
          style: TextStyle(fontSize: 15, fontWeight: FontWeight.w600),
        ),
        const SizedBox(height: 10),
        SizedBox(
          height: 42,
          child: ListView(
            scrollDirection: Axis.horizontal,
            children: _bankRates.keys.map((bank) {
              final selected = _selectedBank == bank;
              return Padding(
                padding: const EdgeInsets.only(right: 8),
                child: ChoiceChip(
                  label: Text(
                    bank,
                    style: TextStyle(
                      fontSize: 13,
                      color: selected ? Colors.white : Colors.black87,
                    ),
                  ),
                  selected: selected,
                  selectedColor: AppTheme.primaryColor,
                  onSelected: (_) => _selectBank(bank),
                ),
              );
            }).toList(),
          ),
        ),
      ],
    );
  }

  Widget _buildInputFields() {
    return Column(
      children: [
        _buildInputField(
          controller: _monthlyIncomeController,
          label: 'Monthly Net Income',
          icon: Icons.currency_rupee,
          suffix: '/month',
        ),
        const SizedBox(height: 16),
        _buildInputField(
          controller: _existingEmiController,
          label: 'Existing EMI (if any)',
          icon: Icons.payments,
          suffix: '/month',
        ),
        const SizedBox(height: 16),
        _buildInputField(
          controller: _interestRateController,
          label: 'Interest Rate (% p.a.)',
          icon: Icons.percent,
          suffix: '%',
        ),
      ],
    );
  }

  Widget _buildInputField({
    required TextEditingController controller,
    required String label,
    required IconData icon,
    String? suffix,
  }) {
    return TextField(
      controller: controller,
      keyboardType: TextInputType.number,
      decoration: InputDecoration(
        labelText: label,
        prefixIcon: Icon(icon, color: AppTheme.primaryColor),
        suffixText: suffix,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
        filled: true,
        fillColor: Colors.grey.shade50,
      ),
    );
  }

  Widget _buildTenureSlider() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'Loan Tenure',
              style: TextStyle(fontSize: 15, fontWeight: FontWeight.w600),
            ),
            Text(
              '$_tenureYears years',
              style: const TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.bold,
                color: Color(0xFF1A237E),
              ),
            ),
          ],
        ),
        Slider(
          value: _tenureYears.toDouble(),
          min: 1,
          max: 30,
          divisions: 29,
          activeColor: AppTheme.primaryColor,
          label: '$_tenureYears years',
          onChanged: (v) => setState(() => _tenureYears = v.round()),
        ),
        const Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text('1 yr', style: TextStyle(fontSize: 12, color: Colors.grey)),
            Text('30 yrs', style: TextStyle(fontSize: 12, color: Colors.grey)),
          ],
        ),
      ],
    );
  }

  Widget _buildResultsCard() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [Colors.green.shade700, Colors.green.shade500],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.green.shade200,
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        children: [
          const Text(
            'Maximum Loan Eligibility',
            style: TextStyle(fontSize: 14, color: Colors.white70),
          ),
          const SizedBox(height: 8),
          Text(
            _formatAmount(_maxLoanAmount),
            style: const TextStyle(
              fontSize: 36,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 12),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceEvenly,
            children: [
              _buildStatColumn(
                'Max EMI',
                '₹${_maxEmi.toStringAsFixed(0)}',
                Icons.monetization_on,
              ),
              _buildStatColumn(
                'Tenure',
                '$_tenureYears yrs',
                Icons.calendar_today,
              ),
              _buildStatColumn('Rate', '$_interestRate%', Icons.percent),
            ],
          ),
          if (_existingEmi > 0)
            Padding(
              padding: const EdgeInsets.only(top: 12),
              child: Text(
                'Includes existing EMI of ₹${_existingEmi.toStringAsFixed(0)}',
                style: const TextStyle(fontSize: 12, color: Colors.white60),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildStatColumn(String label, String value, IconData icon) {
    return Column(
      children: [
        Icon(icon, color: Colors.white70, size: 20),
        const SizedBox(height: 4),
        Text(
          value,
          style: const TextStyle(
            fontSize: 15,
            fontWeight: FontWeight.bold,
            color: Colors.white,
          ),
        ),
        Text(
          label,
          style: const TextStyle(fontSize: 11, color: Colors.white70),
        ),
      ],
    );
  }

  Widget _buildSuggestedCard() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.blue.shade50,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.blue.shade200),
      ),
      child: Row(
        children: [
          Icon(Icons.lightbulb_outline, color: Colors.blue.shade700, size: 36),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Suggested Loan Amount',
                  style: TextStyle(fontSize: 13, color: Colors.black54),
                ),
                Text(
                  _formatAmount(_suggestedLoan),
                  style: const TextStyle(
                    fontSize: 22,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF1A237E),
                  ),
                ),
                Text(
                  'EMI: ₹${_emiForSuggested.toStringAsFixed(0)}/mo',
                  style: const TextStyle(fontSize: 13, color: Colors.black54),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEligibilityMeter() {
    final double affordabilityRatio = _monthlyIncome > 0
        ? ((_maxEmi + _existingEmi) / _monthlyIncome * 100).clamp(0, 100)
        : 0;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey.shade200),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Affordability Analysis',
            style: TextStyle(fontSize: 15, fontWeight: FontWeight.w600),
          ),
          const SizedBox(height: 12),
          ClipRRect(
            borderRadius: BorderRadius.circular(8),
            child: LinearProgressIndicator(
              value: affordabilityRatio / 100,
              minHeight: 10,
              backgroundColor: Colors.grey.shade200,
              valueColor: AlwaysStoppedAnimation(
                affordabilityRatio > 50
                    ? Colors.red
                    : (affordabilityRatio > 35 ? Colors.orange : Colors.green),
              ),
            ),
          ),
          const SizedBox(height: 8),
          Text(
            '${affordabilityRatio.toStringAsFixed(0)}% of income goes toward EMI',
            style: TextStyle(
              fontSize: 13,
              color: affordabilityRatio > 50 ? Colors.red : Colors.black87,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            affordabilityRatio > 50
                ? '⚠️ Consider a lower loan amount or longer tenure'
                : affordabilityRatio > 35
                ? '📊 Moderate — within acceptable range'
                : '✅ Healthy — well within your repayment capacity',
            style: TextStyle(fontSize: 13, color: Colors.grey.shade600),
          ),
        ],
      ),
    );
  }
}

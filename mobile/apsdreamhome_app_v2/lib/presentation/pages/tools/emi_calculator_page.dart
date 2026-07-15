import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'dart:math';

import '../../../core/constants/app_constants.dart';
import '../../../core/router/app_router.dart';

/// EMI Calculator Page
/// Calculate EMI for plot purchases with different bank rates
class EMICalculatorPage extends ConsumerStatefulWidget {
  const EMICalculatorPage({super.key});

  @override
  ConsumerState<EMICalculatorPage> createState() => _EMICalculatorPageState();
}

class _EMICalculatorPageState extends ConsumerState<EMICalculatorPage> {
  // Input controllers
  final _plotPriceController = TextEditingController();
  final _downPaymentController = TextEditingController();
  final _interestRateController = TextEditingController(text: '8.5');
  final _loanAmountController = TextEditingController();
  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();

  // State variables
  double _plotPrice = 1000000;
  double _downPayment = 200000;
  double _interestRate = 8.5;
  int _tenureYears = 10;
  double _loanAmount = 800000;
  double _emiAmount = 0;
  double _totalInterest = 0;
  double _totalPayment = 0;

  // Selected bank
  String _selectedBank = 'SBI';

  // Bank interest rates
  final Map<String, double> _bankRates = {
    'SBI': 8.45,
    'HDFC': 8.50,
    'ICICI': 8.55,
    'Axis': 8.60,
    'PNB': 8.65,
    'BOB': 8.50,
    'Custom': 0.0,
  };

  @override
  void initState() {
    super.initState();
    _plotPriceController.text = _plotPrice.toStringAsFixed(0);
    _downPaymentController.text = _downPayment.toStringAsFixed(0);
    // Auto-fill name/phone from logged-in user
    final user = AuthBridge.instance.currentUser.value;
    if (user != null) {
      _nameController.text = user.name;
      _phoneController.text = user.phone ?? '';
    }
    _calculateEMI();
  }

  @override
  void dispose() {
    _plotPriceController.dispose();
    _downPaymentController.dispose();
    _interestRateController.dispose();
    _loanAmountController.dispose();
    super.dispose();
  }

  void _calculateEMI() {
    // Loan amount = Plot price - Down payment
    _loanAmount = _plotPrice - _downPayment;

    if (_loanAmount <= 0 || _interestRate <= 0 || _tenureYears <= 0) {
      setState(() {
        _emiAmount = 0;
        _totalInterest = 0;
        _totalPayment = 0;
      });
      return;
    }

    // Monthly interest rate
    final double monthlyRate = _interestRate / 12 / 100;
    // Total number of months
    final int totalMonths = _tenureYears * 12;

    // EMI Formula: [P × R × (1+R)^N] / [(1+R)^N-1]
    final double emi =
        (_loanAmount * monthlyRate * pow(1 + monthlyRate, totalMonths)) /
        (pow(1 + monthlyRate, totalMonths) - 1);

    final double totalPayment = emi * totalMonths;
    final double totalInterest = totalPayment - _loanAmount;

    setState(() {
      _emiAmount = emi;
      _totalInterest = totalInterest;
      _totalPayment = totalPayment;
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
    _calculateEMI();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('EMI Calculator'),
        backgroundColor: Colors.blue.shade700,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header
            _buildHeader(),
            const SizedBox(height: 24),

            // Bank Selection
            _buildBankSelection(),
            const SizedBox(height: 24),

            // Input Fields
            _buildInputFields(),
            const SizedBox(height: 24),

            // Tenure Slider
            _buildTenureSlider(),
            const SizedBox(height: 32),

            // Results Card
            _buildResultsCard(),
            const SizedBox(height: 24),

            // Amortization Schedule Button
            _buildAmortizationButton(),
            const SizedBox(height: 24),

            // Compare Banks
            _buildCompareBanks(),
            const SizedBox(height: 32),

            // Apply for Loan Button
            _buildApplyButton(),
          ],
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [Colors.blue.shade700, Colors.blue.shade500],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(Icons.calculate, size: 48, color: Colors.white),
          const SizedBox(height: 12),
          const Text(
            'Plot EMI Calculator',
            style: TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Calculate your monthly EMI for plot purchase with top banks',
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
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 12),
        Wrap(
          spacing: 8,
          runSpacing: 8,
          children: _bankRates.keys.map((bank) {
            final isSelected = _selectedBank == bank;
            final rate = _bankRates[bank];
            return ChoiceChip(
              label: Text(bank == 'Custom' ? 'Custom' : '$bank $rate%'),
              selected: isSelected,
              onSelected: (_) => _selectBank(bank),
              selectedColor: Colors.blue.shade100,
              backgroundColor: Colors.grey.shade100,
              labelStyle: TextStyle(
                color: isSelected ? Colors.blue.shade700 : Colors.black,
                fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
              ),
            );
          }).toList(),
        ),
      ],
    );
  }

  Widget _buildInputFields() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            // Plot Price
            TextField(
              controller: _plotPriceController,
              keyboardType: TextInputType.number,
              decoration: InputDecoration(
                labelText: 'Plot Price (${AppConstants.currencySymbol})',
                prefixIcon: const Icon(Icons.home),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              onChanged: (value) {
                setState(() {
                  _plotPrice = double.tryParse(value) ?? 0;
                });
                _calculateEMI();
              },
            ),
            const SizedBox(height: 16),

            // Down Payment
            TextField(
              controller: _downPaymentController,
              keyboardType: TextInputType.number,
              decoration: InputDecoration(
                labelText: 'Down Payment (${AppConstants.currencySymbol})',
                prefixIcon: const Icon(Icons.account_balance_wallet),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                helperText: 'Min 20% recommended',
              ),
              onChanged: (value) {
                setState(() {
                  _downPayment = double.tryParse(value) ?? 0;
                });
                _calculateEMI();
              },
            ),
            const SizedBox(height: 16),

            // Interest Rate (only for Custom)
            if (_selectedBank == 'Custom')
              TextField(
                controller: _interestRateController,
                keyboardType: TextInputType.number,
                decoration: InputDecoration(
                  labelText: 'Interest Rate (%)',
                  prefixIcon: const Icon(Icons.percent),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                onChanged: (value) {
                  setState(() {
                    _interestRate = double.tryParse(value) ?? 0;
                  });
                  _calculateEMI();
                },
              ),

            // Loan Amount Display
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.blue.shade50,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Loan Amount:',
                    style: TextStyle(fontWeight: FontWeight.bold),
                  ),
                  Text(
                    '${AppConstants.currencySymbol}${_formatNumber(_loanAmount)}',
                    style: TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 18,
                      color: Colors.blue.shade700,
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

  Widget _buildTenureSlider() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Loan Tenure',
                  style: TextStyle(fontWeight: FontWeight.bold),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 6,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.blue.shade100,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    '$_tenureYears Years',
                    style: TextStyle(
                      fontWeight: FontWeight.bold,
                      color: Colors.blue.shade700,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Slider(
              value: _tenureYears.toDouble(),
              min: 5,
              max: 20,
              divisions: 15,
              label: '$_tenureYears Years',
              onChanged: (value) {
                setState(() {
                  _tenureYears = value.round();
                });
                _calculateEMI();
              },
            ),
            const Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [Text('5 Years'), Text('20 Years')],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildResultsCard() {
    return Card(
      elevation: 4,
      child: Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: [Colors.green.shade50, Colors.white],
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
          ),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Column(
          children: [
            // Monthly EMI
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: Colors.green.shade100,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Column(
                children: [
                  const Text(
                    'Monthly EMI',
                    style: TextStyle(fontSize: 14, color: Colors.green),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    '${AppConstants.currencySymbol}${_formatNumber(_emiAmount)}',
                    style: const TextStyle(
                      fontSize: 36,
                      fontWeight: FontWeight.bold,
                      color: Colors.green,
                    ),
                  ),
                  const Text('per month', style: TextStyle(color: Colors.grey)),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // Breakdown
            Row(
              children: [
                Expanded(
                  child: _buildResultItem(
                    'Principal',
                    '${AppConstants.currencySymbol}${_formatNumber(_loanAmount)}',
                    Colors.blue,
                  ),
                ),
                Expanded(
                  child: _buildResultItem(
                    'Interest',
                    '${AppConstants.currencySymbol}${_formatNumber(_totalInterest)}',
                    Colors.orange,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            Divider(color: Colors.grey.shade300),
            const SizedBox(height: 16),
            _buildResultItem(
              'Total Payment',
              '${AppConstants.currencySymbol}${_formatNumber(_totalPayment)}',
              Colors.purple,
              isBold: true,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildResultItem(
    String label,
    String value,
    Color color, {
    bool isBold = false,
  }) {
    return Column(
      children: [
        Text(
          label,
          style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
        ),
        const SizedBox(height: 4),
        Text(
          value,
          style: TextStyle(
            fontSize: isBold ? 20 : 16,
            fontWeight: isBold ? FontWeight.bold : FontWeight.w600,
            color: color,
          ),
          textAlign: TextAlign.center,
        ),
      ],
    );
  }

  Widget _buildAmortizationButton() {
    return OutlinedButton.icon(
      onPressed: () {
        _showAmortizationSchedule();
      },
      icon: const Icon(Icons.table_chart),
      label: const Text('View Amortization Schedule'),
      style: OutlinedButton.styleFrom(
        minimumSize: const Size(double.infinity, 48),
      ),
    );
  }

  Widget _buildCompareBanks() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Compare Banks',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            ..._bankRates.entries.where((e) => e.key != 'Custom').map((entry) {
              final bank = entry.key;
              final rate = entry.value;

              // Calculate EMI for this bank
              final double monthlyRate = rate / 12 / 100;
              final int totalMonths = _tenureYears * 12;
              final double emi =
                  (_loanAmount *
                      monthlyRate *
                      pow(1 + monthlyRate, totalMonths)) /
                  (pow(1 + monthlyRate, totalMonths) - 1);

              return Container(
                margin: const EdgeInsets.only(bottom: 8),
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: bank == _selectedBank
                      ? Colors.blue.shade50
                      : Colors.grey.shade50,
                  borderRadius: BorderRadius.circular(8),
                  border: bank == _selectedBank
                      ? Border.all(color: Colors.blue.shade300)
                      : null,
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          bank,
                          style: const TextStyle(fontWeight: FontWeight.bold),
                        ),
                        Text(
                          '$rate% p.a.',
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey.shade600,
                          ),
                        ),
                      ],
                    ),
                    Text(
                      '${AppConstants.currencySymbol}${_formatNumber(emi)}/mo',
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        color: bank == _selectedBank
                            ? Colors.blue.shade700
                            : Colors.black,
                      ),
                    ),
                  ],
                ),
              );
            }),
          ],
        ),
      ),
    );
  }

  Widget _buildApplyButton() {
    return ElevatedButton.icon(
      onPressed: () {
        _showLoanApplicationDialog();
      },
      icon: const Icon(Icons.send),
      label: const Text('Apply for Home Loan'),
      style: ElevatedButton.styleFrom(
        backgroundColor: Colors.blue.shade700,
        foregroundColor: Colors.white,
        minimumSize: const Size(double.infinity, 54),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      ),
    );
  }

  void _showAmortizationSchedule() {
    // Generate amortization schedule
    final List<Map<String, dynamic>> schedule = [];
    double balance = _loanAmount;
    final double monthlyRate = _interestRate / 12 / 100;
    final int totalMonths = _tenureYears * 12;

    for (int month = 1; month <= totalMonths && month <= 12; month++) {
      final double interest = balance * monthlyRate;
      final double principal = _emiAmount - interest;
      balance -= principal;

      schedule.add({
        'month': month,
        'emi': _emiAmount,
        'principal': principal,
        'interest': interest,
        'balance': balance > 0 ? balance : 0,
      });
    }

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (context) => DraggableScrollableSheet(
        initialChildSize: 0.7,
        minChildSize: 0.5,
        maxChildSize: 0.9,
        expand: false,
        builder: (context, scrollController) {
          return Container(
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [
                Text(
                  'Amortization Schedule (First 12 Months)',
                  style: Theme.of(context).textTheme.titleLarge,
                ),
                const SizedBox(height: 16),
                Expanded(
                  child: SingleChildScrollView(
                    controller: scrollController,
                    child: DataTable(
                      columns: const [
                        DataColumn(label: Text('Month')),
                        DataColumn(label: Text('EMI')),
                        DataColumn(label: Text('Principal')),
                        DataColumn(label: Text('Interest')),
                        DataColumn(label: Text('Balance')),
                      ],
                      rows: schedule.map((row) {
                        final double emi = (row['emi'] as num).toDouble();
                        final double principal = (row['principal'] as num)
                            .toDouble();
                        final double interest = (row['interest'] as num)
                            .toDouble();
                        final double balance = (row['balance'] as num)
                            .toDouble();
                        return DataRow(
                          cells: [
                            DataCell(Text('${row['month']}')),
                            DataCell(Text(_formatNumber(emi))),
                            DataCell(Text(_formatNumber(principal))),
                            DataCell(Text(_formatNumber(interest))),
                            DataCell(Text(_formatNumber(balance))),
                          ],
                        );
                      }).toList(),
                    ),
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  void _showLoanApplicationDialog() {
    final nameCtl = TextEditingController(text: _nameController.text);
    final phoneCtl = TextEditingController(text: _phoneController.text);
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Apply for Home Loan'),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              TextField(
                controller: nameCtl,
                decoration: const InputDecoration(
                  labelText: 'Full Name',
                  border: OutlineInputBorder(),
                  isDense: true,
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: phoneCtl,
                keyboardType: TextInputType.phone,
                decoration: const InputDecoration(
                  labelText: 'Phone Number',
                  prefixText: '+91 ',
                  border: OutlineInputBorder(),
                  isDense: true,
                ),
              ),
              const SizedBox(height: 16),
              Text(
                'Bank: $_selectedBank',
                style: const TextStyle(fontWeight: FontWeight.w500),
              ),
              Text(
                'Loan Amount: ${AppConstants.currencySymbol}${_formatNumber(_loanAmount)}',
              ),
              Text(
                'EMI: ${AppConstants.currencySymbol}${_formatNumber(_emiAmount)}/month',
              ),
              Text('Tenure: $_tenureYears years'),
              const SizedBox(height: 12),
              const Text(
                'Documents needed: Aadhar, PAN, Income Proof, Bank Statements',
                style: TextStyle(fontSize: 11, color: Colors.grey),
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text(
                    'Loan application submitted! We will contact you soon.',
                  ),
                  backgroundColor: Colors.green,
                ),
              );
            },
            child: const Text('Submit Application'),
          ),
        ],
      ),
    );
  }

  String _formatNumber(double number) {
    if (number >= 10000000) {
      return '${(number / 10000000).toStringAsFixed(2)} Cr';
    } else if (number >= 100000) {
      return '${(number / 100000).toStringAsFixed(2)} Lakh';
    } else if (number >= 1000) {
      return '${(number / 1000).toStringAsFixed(1)}K';
    }
    return number.toStringAsFixed(0);
  }
}

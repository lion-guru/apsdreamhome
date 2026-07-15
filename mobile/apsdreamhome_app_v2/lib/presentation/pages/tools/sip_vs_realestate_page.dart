import 'dart:math';
import 'package:flutter/material.dart';
import 'package:share_plus/share_plus.dart';
import '../../../core/theme/app_theme.dart';

class SipVsRealestatePage extends StatefulWidget {
  const SipVsRealestatePage({super.key});

  @override
  State<SipVsRealestatePage> createState() => _SipVsRealestatePageState();
}

class _SipVsRealestatePageState extends State<SipVsRealestatePage> {
  final _investmentController = TextEditingController();
  final _lumpsumController = TextEditingController();

  double _sipReturn = 12;
  double _propertyAppreciation = 6;
  int _tenure = 10;

  bool _calculated = false;
  double _sipFinalValue = 0, _sipTotalInvested = 0, _sipGain = 0;
  double _propertyFinalValue = 0, _propertyGain = 0;

  @override
  void dispose() {
    _investmentController.dispose();
    _lumpsumController.dispose();
    super.dispose();
  }

  void _calculate() {
    final sipMonthly = double.tryParse(_investmentController.text) ?? 0;
    final lumpsum = double.tryParse(_lumpsumController.text) ?? 0;

    if (sipMonthly <= 0 && lumpsum <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text('Enter investment amount'),
          backgroundColor: AppTheme.errorColor,
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    _sipTotalInvested = sipMonthly * 12 * _tenure;
    final monthlyRate = (_sipReturn / 12) / 100;
    final totalMonths = _tenure * 12;
    if (monthlyRate > 0) {
      _sipFinalValue =
          sipMonthly *
          ((pow(1 + monthlyRate, totalMonths) - 1) / monthlyRate) *
          (1 + monthlyRate);
    } else {
      _sipFinalValue = _sipTotalInvested;
    }
    _sipGain = _sipFinalValue - _sipTotalInvested;

    _propertyFinalValue =
        lumpsum * pow(1 + _propertyAppreciation / 100, _tenure);
    _propertyGain = _propertyFinalValue - lumpsum;

    setState(() => _calculated = true);
  }

  void _share() {
    Share.share(
      'SIP vs Real Estate Comparison\n'
      'Tenure: $_tenure years\n'
      'SIP Final: ₹${_format(_sipFinalValue)}\n'
      'Property Final: ₹${_format(_propertyFinalValue)}',
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
        title: const Text('SIP vs Real Estate'),
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
          colors: [Color(0xFF43A047), Color(0xFF66BB6A)],
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
            'SIP vs Real Estate',
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Compare investment returns over time',
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
            'Investment Details',
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _investmentController,
            keyboardType: TextInputType.number,
            decoration: const InputDecoration(
              labelText: 'Monthly SIP Amount (₹)',
              prefixIcon: Icon(Icons.trending_up_rounded),
              border: OutlineInputBorder(),
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _lumpsumController,
            keyboardType: TextInputType.number,
            decoration: const InputDecoration(
              labelText: 'Lumpsum for Property (₹)',
              prefixIcon: Icon(Icons.home_rounded),
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
            'SIP Return (% p.a.)',
            _sipReturn.round(),
            1,
            25,
            (v) => setState(() => _sipReturn = v.toDouble()),
          ),
          const SizedBox(height: 8),
          _buildSlider(
            'Property Appreciation (% p.a.)',
            _propertyAppreciation.round(),
            1,
            20,
            (v) => setState(() => _propertyAppreciation = v.toDouble()),
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
              '$value%',
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
          'Compare Returns',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
        ),
      ),
    );
  }

  Widget _buildResults() {
    final sipWins = _sipFinalValue > _propertyFinalValue;
    return Column(
      children: [
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            gradient: LinearGradient(
              colors: [const Color(0xFF43A047), const Color(0xFF2E7D32)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(16),
          ),
          child: Column(
            children: [
              Icon(
                sipWins ? Icons.trending_up_rounded : Icons.home_rounded,
                size: 48,
                color: Colors.white,
              ),
              const SizedBox(height: 8),
              Text(
                sipWins ? 'SIP Wins!' : 'Real Estate Wins!',
                style: const TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.w800,
                  color: Colors.white,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                sipWins
                    ? 'Over $_tenure years, SIP returns more'
                    : 'Over $_tenure years, property appreciates more',
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
              const Text(
                'SIP Investment',
                style: TextStyle(fontWeight: FontWeight.w700, fontSize: 14),
              ),
              const SizedBox(height: 8),
              _resultRow(
                'Total Invested',
                '₹${_format(_sipTotalInvested)}',
                Colors.grey.shade700,
              ),
              _resultRow(
                'Final Value',
                '₹${_format(_sipFinalValue)}',
                Colors.greenAccent.shade700,
              ),
              _resultRow(
                'Total Gain',
                '₹${_format(_sipGain)}',
                Colors.blueAccent,
              ),
              const Divider(height: 20),
              const Text(
                'Real Estate Investment',
                style: TextStyle(fontWeight: FontWeight.w700, fontSize: 14),
              ),
              const SizedBox(height: 8),
              _resultRow(
                'Initial Investment',
                '₹${_format(double.tryParse(_lumpsumController.text) ?? 0)}',
                Colors.grey.shade700,
              ),
              _resultRow(
                'Final Value',
                '₹${_format(_propertyFinalValue)}',
                Colors.orangeAccent,
              ),
              _resultRow(
                'Total Gain',
                '₹${_format(_propertyGain)}',
                Colors.redAccent,
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _resultRow(String label, String value, Color color) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
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

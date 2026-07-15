import 'package:flutter/material.dart';
import 'package:share_plus/share_plus.dart';
import '../../../core/theme/app_theme.dart';

class CapitalGainsPage extends StatefulWidget {
  const CapitalGainsPage({super.key});

  @override
  State<CapitalGainsPage> createState() => _CapitalGainsPageState();
}

class _CapitalGainsPageState extends State<CapitalGainsPage> {
  final _salePriceController = TextEditingController();
  final _purchasePriceController = TextEditingController();
  final _improvementController = TextEditingController(text: '0');

  String _holdingPeriod = 'Long-term';
  String _indexationOption = 'With Indexation';
  int _purchaseYear = 2020;
  int _saleYear = 2025;
  bool _calculated = false;

  double _capitalGain = 0,
      _taxLiability = 0,
      _netProceeds = 0,
      _effectiveRate = 0;

  static const Map<int, double> _cpi = {
    2001: 100,
    2002: 105,
    2003: 109,
    2004: 113,
    2005: 117,
    2006: 122,
    2007: 129,
    2008: 137,
    2009: 148,
    2010: 167,
    2011: 184,
    2012: 200,
    2013: 220,
    2014: 240,
    2015: 254,
    2016: 264,
    2017: 272,
    2018: 280,
    2019: 289,
    2020: 301,
    2021: 317,
    2022: 331,
    2023: 348,
    2024: 363,
    2025: 376,
  };

  @override
  void dispose() {
    _salePriceController.dispose();
    _purchasePriceController.dispose();
    _improvementController.dispose();
    super.dispose();
  }

  void _calculate() {
    final salePrice = double.tryParse(_salePriceController.text) ?? 0;
    final purchasePrice = double.tryParse(_purchasePriceController.text) ?? 0;
    final improvement = double.tryParse(_improvementController.text) ?? 0;

    if (salePrice <= 0 || purchasePrice <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text('Enter sale & purchase prices'),
          backgroundColor: AppTheme.errorColor,
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    double indexedCost = purchasePrice;
    double indexedImprovement = improvement;
    if (_holdingPeriod == 'Long-term' &&
        _indexationOption == 'With Indexation') {
      final factor = (_cpi[_saleYear] ?? 376) / (_cpi[_purchaseYear] ?? 100);
      indexedCost = purchasePrice * factor;
      indexedImprovement = improvement * factor;
    }

    _capitalGain = (salePrice - indexedCost - indexedImprovement).clamp(
      0,
      double.infinity,
    );

    if (_holdingPeriod == 'Short-term') {
      _taxLiability = _capitalGain * 0.30;
      _effectiveRate = 30;
    } else if (_indexationOption == 'With Indexation') {
      _taxLiability = _capitalGain * 0.20;
      _effectiveRate = 20;
    } else {
      _taxLiability = _capitalGain * 0.125;
      _effectiveRate = 12.5;
    }
    _netProceeds = salePrice - _taxLiability;
    setState(() => _calculated = true);
  }

  void _share() {
    Share.share(
      'Capital Gains Tax Calculation\n'
      'Holding Period: $_holdingPeriod\n'
      'Capital Gain: ₹${_format(_capitalGain)}\n'
      'Tax Liability: ₹${_format(_taxLiability)}\n'
      'Net Proceeds: ₹${_format(_netProceeds)}',
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
        title: const Text('Capital Gains Calculator'),
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
            _buildTypeSelector(),
            const SizedBox(height: 16),
            _buildInputFields(),
            const SizedBox(height: 16),
            if (_holdingPeriod == 'Long-term') _buildIndexationOptions(),
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
          colors: [Color(0xFF26A69A), Color(0xFF00897B)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(Icons.savings_rounded, size: 40, color: Colors.white),
          const SizedBox(height: 12),
          const Text(
            'Capital Gains Tax',
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Estimate tax on property sale profits',
            style: TextStyle(
              fontSize: 13,
              color: Colors.white.withValues(alpha: 0.85),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTypeSelector() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Holding Period',
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 10),
          Row(
            children: ['Short-term', 'Long-term'].map((p) {
              final sel = _holdingPeriod == p;
              return Expanded(
                child: GestureDetector(
                  onTap: () => setState(() => _holdingPeriod = p),
                  child: Container(
                    margin: const EdgeInsets.symmetric(horizontal: 4),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    decoration: BoxDecoration(
                      color: sel ? AppTheme.primaryColor : Colors.grey.shade100,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Text(
                      p,
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        color: sel ? Colors.white : Colors.black54,
                        fontWeight: FontWeight.w600,
                        fontSize: 13,
                      ),
                    ),
                  ),
                ),
              );
            }).toList(),
          ),
          const SizedBox(height: 6),
          Text(
            _holdingPeriod == 'Short-term'
                ? 'Held ≤ 24 months: taxed at slab rate (up to 30%)'
                : 'Held > 24 months: 20% with indexation or 12.5% without',
            style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
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
            offset: const Offset(0, 2),
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
            controller: _salePriceController,
            keyboardType: TextInputType.number,
            decoration: const InputDecoration(
              labelText: 'Sale Price (₹)',
              prefixIcon: Icon(Icons.currency_rupee_rounded),
              border: OutlineInputBorder(),
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _purchasePriceController,
            keyboardType: TextInputType.number,
            decoration: const InputDecoration(
              labelText: 'Purchase Price (₹)',
              prefixIcon: Icon(Icons.shopping_cart_rounded),
              border: OutlineInputBorder(),
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _improvementController,
            keyboardType: TextInputType.number,
            decoration: const InputDecoration(
              labelText: 'Improvement Cost (₹)',
              prefixIcon: Icon(Icons.construction_rounded),
              border: OutlineInputBorder(),
            ),
          ),
          if (_holdingPeriod == 'Long-term') ...[
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: _buildYearDropdown(
                    'Purchase Year',
                    _purchaseYear,
                    (v) => setState(() => _purchaseYear = v!),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _buildYearDropdown(
                    'Sale Year',
                    _saleYear,
                    (v) => setState(() => _saleYear = v!),
                  ),
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildYearDropdown(
    String label,
    int value,
    ValueChanged<int?> onChanged,
  ) {
    return DropdownButtonFormField<int>(
      value: value,
      decoration: InputDecoration(
        labelText: label,
        border: const OutlineInputBorder(),
      ),
      items: List.generate(25, (i) => 2001 + i)
          .map((y) => DropdownMenuItem(value: y, child: Text(y.toString())))
          .toList(),
      onChanged: onChanged,
    );
  }

  Widget _buildIndexationOptions() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Indexation',
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 10),
          Row(
            children: ['With Indexation', 'Without Indexation'].map((o) {
              final sel = _indexationOption == o;
              return Expanded(
                child: GestureDetector(
                  onTap: () => setState(() => _indexationOption = o),
                  child: Container(
                    margin: const EdgeInsets.symmetric(horizontal: 4),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    decoration: BoxDecoration(
                      color: sel ? AppTheme.primaryColor : Colors.grey.shade100,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Text(
                      o.replaceAll(' Indexation', ''),
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        color: sel ? Colors.white : Colors.black54,
                        fontWeight: FontWeight.w600,
                        fontSize: 12,
                      ),
                    ),
                  ),
                ),
              );
            }).toList(),
          ),
          const SizedBox(height: 4),
          Text(
            'With indexation = 20% tax. Without = 12.5% flat.',
            style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
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
          'Calculate Tax',
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
          colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Tax Summary',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w700,
              color: Colors.white,
            ),
          ),
          const Divider(color: Colors.white24, height: 24),
          _resultRow(
            'Capital Gain',
            '₹${_format(_capitalGain)}',
            Colors.greenAccent,
          ),
          const SizedBox(height: 8),
          _resultRow(
            'Tax Liability',
            '₹${_format(_taxLiability)}',
            Colors.redAccent.shade200,
          ),
          const SizedBox(height: 8),
          _resultRow(
            'Effective Rate',
            '${_effectiveRate.toStringAsFixed(1)}%',
            Colors.amberAccent,
          ),
          const SizedBox(height: 8),
          _resultRow(
            'Net Proceeds',
            '₹${_format(_netProceeds)}',
            Colors.lightBlueAccent,
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

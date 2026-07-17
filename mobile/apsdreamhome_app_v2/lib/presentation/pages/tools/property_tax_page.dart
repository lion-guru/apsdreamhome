import 'package:flutter/material.dart';
import 'package:share_plus/share_plus.dart';
import '../../../core/theme/app_theme.dart';

class PropertyTaxPage extends StatefulWidget {
  const PropertyTaxPage({super.key});

  @override
  State<PropertyTaxPage> createState() => _PropertyTaxPageState();
}

class _PropertyTaxPageState extends State<PropertyTaxPage> {
  final _valueController = TextEditingController();

  String _propertyType = 'Residential';
  String _cityCategory = 'Metro';
  bool _calculated = false;

  double _taxAmount = 0, _effectiveRate = 0;

  static const Map<String, Map<String, double>> _rates = {
    'Residential': {'Metro': 0.002, 'City': 0.0015, 'Town': 0.001},
    'Commercial': {'Metro': 0.004, 'City': 0.003, 'Town': 0.002},
    'Industrial': {'Metro': 0.003, 'City': 0.0025, 'Town': 0.0015},
  };

  @override
  void dispose() {
    _valueController.dispose();
    super.dispose();
  }

  void _calculate() {
    final value = double.tryParse(_valueController.text) ?? 0;
    if (value <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Enter property value'),
          backgroundColor: AppTheme.errorColor,
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }
    final rate = _rates[_propertyType]![_cityCategory]!;
    _effectiveRate = rate * 100;
    _taxAmount = (value * rate).clamp(500, double.infinity);
    setState(() => _calculated = true);
  }

  void _share() {
    Share.share(
      'Property Tax Calculation\n'
      'Type: $_propertyType\n'
      'Annual Tax: ₹${_taxAmount.toStringAsFixed(0)}',
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Property Tax Calculator'),
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
            _buildOptions(),
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
          colors: [Color(0xFF5C6BC0), Color(0xFF7986CB)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(Icons.receipt_long_rounded, size: 40, color: Colors.white),
          const SizedBox(height: 12),
          const Text(
            'Property Tax',
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Calculate annual property tax',
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
            'Property Value',
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _valueController,
            keyboardType: TextInputType.number,
            decoration: const InputDecoration(
              labelText: 'Property Value (₹)',
              prefixIcon: Icon(Icons.currency_rupee_rounded),
              border: OutlineInputBorder(),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildOptions() {
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
            'Property Type',
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 10),
          Row(
            children: _rates.keys.map((t) {
              final sel = _propertyType == t;
              return Expanded(
                child: GestureDetector(
                  onTap: () => setState(() => _propertyType = t),
                  child: Container(
                    margin: const EdgeInsets.symmetric(horizontal: 3),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    decoration: BoxDecoration(
                      color: sel ? AppTheme.primaryColor : Colors.grey.shade100,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Text(
                      t,
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
          const SizedBox(height: 14),
          const Text(
            'City Category',
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 10),
          Row(
            children: ['Metro', 'City', 'Town'].map((c) {
              final sel = _cityCategory == c;
              return Expanded(
                child: GestureDetector(
                  onTap: () => setState(() => _cityCategory = c),
                  child: Container(
                    margin: const EdgeInsets.symmetric(horizontal: 3),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    decoration: BoxDecoration(
                      color: sel ? AppTheme.primaryColor : Colors.grey.shade100,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Text(
                      c,
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        color: sel ? Colors.white : Colors.black54,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ),
              );
            }).toList(),
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
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF5C6BC0), Color(0xFF3F51B5)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        children: [
          const Icon(
            Icons.check_circle_rounded,
            size: 48,
            color: Colors.greenAccent,
          ),
          const SizedBox(height: 12),
          const Text(
            'Annual Property Tax',
            style: TextStyle(color: Colors.white70, fontSize: 14),
          ),
          const SizedBox(height: 4),
          Text(
            '₹${_taxAmount.toStringAsFixed(0)}',
            style: const TextStyle(
              fontSize: 32,
              fontWeight: FontWeight.w800,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Rate: ${_effectiveRate.toStringAsFixed(3)}% of property value (min ₹500)',
            style: TextStyle(
              color: Colors.white.withValues(alpha: 0.7),
              fontSize: 12,
            ),
          ),
        ],
      ),
    );
  }
}

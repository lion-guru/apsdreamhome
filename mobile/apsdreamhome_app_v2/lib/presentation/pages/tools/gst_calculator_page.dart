import 'package:flutter/material.dart';
import 'package:share_plus/share_plus.dart';
import '../../../core/theme/app_theme.dart';

class GstCalculatorPage extends StatefulWidget {
  const GstCalculatorPage({super.key});

  @override
  State<GstCalculatorPage> createState() => _GstCalculatorPageState();
}

class _GstCalculatorPageState extends State<GstCalculatorPage> {
  final _valueController = TextEditingController();

  String _propertyType = 'Affordable';
  String _itcOption = 'Without ITC';
  bool _calculated = false;

  double _gstAmount = 0, _totalWithGst = 0, _gstRate = 0;

  void _calculate() {
    final value = double.tryParse(_valueController.text) ?? 0;
    if (value <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text('Enter property value'),
          backgroundColor: AppTheme.errorColor,
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    if (_propertyType == 'Affordable') {
      _gstRate = _itcOption == 'With ITC' ? 1 : 1;
    } else if (_propertyType == 'Under-construction') {
      _gstRate = _itcOption == 'With ITC' ? 1 : 5;
    } else if (_propertyType == 'Commercial') {
      _gstRate = 12;
    } else {
      _gstRate = 0;
    }

    _gstAmount = value * _gstRate / 100;
    _totalWithGst = value + _gstAmount;
    setState(() => _calculated = true);
  }

  void _share() {
    Share.share(
      'GST on Property\n'
      'Property Type: $_propertyType\n'
      'GST Rate: $_gstRate%\n'
      'GST Amount: ₹${_gstAmount.toStringAsFixed(0)}\n'
      'Total: ₹${_totalWithGst.toStringAsFixed(0)}',
    );
  }

  @override
  void dispose() {
    _valueController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('GST Calculator'),
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
          colors: [Color(0xFF7B1FA2), Color(0xFF9C27B0)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(Icons.percent_rounded, size: 40, color: Colors.white),
          const SizedBox(height: 12),
          const Text(
            'GST Calculator',
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Calculate GST on property transactions',
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
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children:
                [
                  'Affordable',
                  'Under-construction',
                  'Commercial',
                  'Ready-to-move',
                ].map((t) {
                  final sel = _propertyType == t;
                  return GestureDetector(
                    onTap: () => setState(() => _propertyType = t),
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 16,
                        vertical: 10,
                      ),
                      decoration: BoxDecoration(
                        color: sel
                            ? AppTheme.primaryColor
                            : Colors.grey.shade100,
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        t,
                        style: TextStyle(
                          color: sel ? Colors.white : Colors.black54,
                          fontWeight: FontWeight.w600,
                          fontSize: 12,
                        ),
                      ),
                    ),
                  );
                }).toList(),
          ),
          if (_propertyType != 'Ready-to-move') ...[
            const SizedBox(height: 14),
            const Text(
              'ITC Option',
              style: TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w600,
                color: Colors.black87,
              ),
            ),
            const SizedBox(height: 10),
            Row(
              children: ['With ITC', 'Without ITC'].map((o) {
                final sel = _itcOption == o;
                return Expanded(
                  child: GestureDetector(
                    onTap: () => setState(() => _itcOption = o),
                    child: Container(
                      margin: const EdgeInsets.symmetric(horizontal: 4),
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      decoration: BoxDecoration(
                        color: sel
                            ? AppTheme.primaryColor
                            : Colors.grey.shade100,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Text(
                        o,
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
          ],
          const SizedBox(height: 10),
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.blue.shade50,
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: Colors.blue.shade200),
            ),
            child: Row(
              children: [
                Icon(
                  Icons.info_outline_rounded,
                  size: 16,
                  color: Colors.blue.shade700,
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    _propertyType == 'Affordable'
                        ? 'Affordable housing: 1% GST (with or without ITC)'
                        : _propertyType == 'Under-construction'
                        ? 'Under-construction: 1% with ITC, 5% without ITC'
                        : _propertyType == 'Commercial'
                        ? 'Commercial property: 12% GST'
                        : 'Ready-to-move: 0% GST (no ITC applicable)',
                    style: TextStyle(fontSize: 11, color: Colors.blue.shade800),
                  ),
                ),
              ],
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
          'Calculate GST',
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
        gradient: LinearGradient(
          colors: [const Color(0xFF7B1FA2), const Color(0xFF6A1B9A)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        children: [
          Container(
            width: 80,
            height: 80,
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.15),
              borderRadius: BorderRadius.circular(40),
            ),
            child: Center(
              child: Text(
                '$_gstRate%',
                style: const TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.w800,
                  color: Colors.white,
                ),
              ),
            ),
          ),
          const SizedBox(height: 16),
          _resultRow(
            'GST Amount',
            '₹${_gstAmount.toStringAsFixed(0)}',
            Colors.amberAccent,
          ),
          const SizedBox(height: 8),
          _resultRow('Base Value', '₹${_valueController.text}', Colors.white70),
          const SizedBox(height: 8),
          _resultRow(
            'Total with GST',
            '₹${_totalWithGst.toStringAsFixed(0)}',
            Colors.white,
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
            fontSize: 16,
            fontWeight: FontWeight.w700,
          ),
        ),
      ],
    );
  }
}

import 'package:flutter/material.dart';
import 'package:share_plus/share_plus.dart';
import '../../../core/theme/app_theme.dart';

class ConstructionCostPage extends StatefulWidget {
  const ConstructionCostPage({super.key});

  @override
  State<ConstructionCostPage> createState() => _ConstructionCostPageState();
}

class _ConstructionCostPageState extends State<ConstructionCostPage> {
  final _areaController = TextEditingController();
  final _floorsController = TextEditingController(text: '1');

  String _finishLevel = 'Standard';
  String _locationType = 'City';
  bool _calculated = false;

  double _totalCost = 0,
      _materialCost = 0,
      _laborCost = 0,
      _miscCost = 0,
      _perSqftCost = 0;

  static const Map<String, double> _baseRates = {
    'Basic': 1400,
    'Standard': 1800,
    'Premium': 2400,
    'Luxury': 3200,
  };
  static const Map<String, double> _locationFactors = {
    'City': 1.2,
    'Suburb': 1.0,
    'Rural': 0.85,
  };

  @override
  void dispose() {
    _areaController.dispose();
    _floorsController.dispose();
    super.dispose();
  }

  void _calculate() {
    final area = double.tryParse(_areaController.text) ?? 0;
    final floors = double.tryParse(_floorsController.text) ?? 1;
    if (area <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text('Enter a valid area'),
          backgroundColor: AppTheme.errorColor,
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }
    final rate = _baseRates[_finishLevel]! * _locationFactors[_locationType]!;
    _perSqftCost = rate;
    _totalCost = area * floors * rate;
    _materialCost = _totalCost * 0.50;
    _laborCost = _totalCost * 0.40;
    _miscCost = _totalCost * 0.10;
    setState(() => _calculated = true);
  }

  void _share() {
    Share.share(
      'Construction Cost Estimate\n'
      'Area: ${_areaController.text} sqft\n'
      'Finish: $_finishLevel\n'
      'Total Cost: ₹${_format(_totalCost)}\n'
      'Per SqFt: ₹${_perSqftCost.toStringAsFixed(0)}',
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
        title: const Text('Construction Cost Estimator'),
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
            _buildInputSection(),
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
          colors: [Color(0xFFFB8C00), Color(0xFFFFA726)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(Icons.construction_rounded, size: 40, color: Colors.white),
          const SizedBox(height: 12),
          const Text(
            'Construction Cost',
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Estimate building costs for your plot',
            style: TextStyle(
              fontSize: 13,
              color: Colors.white.withValues(alpha: 0.85),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInputSection() {
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
            'Building Details',
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _areaController,
            keyboardType: TextInputType.number,
            decoration: const InputDecoration(
              labelText: 'Built-up Area (sq ft)',
              prefixIcon: Icon(Icons.straighten_rounded),
              border: OutlineInputBorder(),
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _floorsController,
            keyboardType: TextInputType.number,
            decoration: const InputDecoration(
              labelText: 'Number of Floors',
              prefixIcon: Icon(Icons.layers_rounded),
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
            'Finish Level',
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
            children: _baseRates.keys.map((l) {
              final sel = _finishLevel == l;
              return GestureDetector(
                onTap: () => setState(() => _finishLevel = l),
                child: Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 10,
                  ),
                  decoration: BoxDecoration(
                    color: sel ? AppTheme.primaryColor : Colors.grey.shade100,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    l,
                    style: TextStyle(
                      color: sel ? Colors.white : Colors.black54,
                      fontWeight: FontWeight.w600,
                      fontSize: 13,
                    ),
                  ),
                ),
              );
            }).toList(),
          ),
          const SizedBox(height: 14),
          const Text(
            'Location',
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 10),
          Row(
            children: _locationFactors.keys.map((l) {
              final sel = _locationType == l;
              return Expanded(
                child: GestureDetector(
                  onTap: () => setState(() => _locationType = l),
                  child: Container(
                    margin: const EdgeInsets.symmetric(horizontal: 4),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    decoration: BoxDecoration(
                      color: sel ? AppTheme.primaryColor : Colors.grey.shade100,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Text(
                      l,
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
          'Estimate Cost',
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
          colors: [const Color(0xFFFB8C00), const Color(0xFFFFA726)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Cost Breakdown',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w700,
              color: Colors.white,
            ),
          ),
          const Divider(color: Colors.white24, height: 24),
          _resultRow('Total Cost', '₹${_format(_totalCost)}', Colors.white),
          const SizedBox(height: 8),
          _resultRow(
            'Per SqFt',
            '₹${_perSqftCost.toStringAsFixed(0)}',
            Colors.amberAccent,
          ),
          const SizedBox(height: 8),
          _resultRow(
            'Material (50%)',
            '₹${_format(_materialCost)}',
            Colors.lightBlueAccent,
          ),
          const SizedBox(height: 8),
          _resultRow(
            'Labor (40%)',
            '₹${_format(_laborCost)}',
            Colors.greenAccent,
          ),
          const SizedBox(height: 8),
          _resultRow(
            'Misc (10%)',
            '₹${_format(_miscCost)}',
            Colors.orangeAccent,
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

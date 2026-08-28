import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../core/theme/app_theme.dart';

class PlotConverterPage extends StatefulWidget {
  const PlotConverterPage({super.key});

  @override
  State<PlotConverterPage> createState() => _PlotConverterPageState();
}

class _PlotConverterPageState extends State<PlotConverterPage>
    with SingleTickerProviderStateMixin {
  final _inputController = TextEditingController();
  String _fromUnit = 'Square Feet';
  String _toUnit = 'Square Yard (Gaj)';
  String _region = 'UP';
  late AnimationController _swapAnimController;
  late Animation<double> _swapRotation;

  static const List<String> _units = [
    'Square Feet',
    'Square Meter',
    'Square Yard (Gaj)',
    'Bigha',
    'Katha',
    'Marla',
    'Acre',
    'Hectare',
  ];

  static const List<String> _regions = ['UP', 'Bihar', 'Bengal'];

  static const Map<String, double> _toSqFt = {
    'Square Feet': 1.0,
    'Square Meter': 10.7639,
    'Square Yard (Gaj)': 9.0,
    'Acre': 43560.0,
    'Hectare': 107639.0,
  };

  static const Map<String, Map<String, double>> _regionBasedToSqFt = {
    'Katha': {
      'UP': 720.0,
      'Bihar': 1361.25,
      'Bengal': 720.0,
    },
    'Marla': {
      'UP': 272.25,
      'Bihar': 272.25,
      'Bengal': 272.25,
    },
    'Bigha': {
      'UP': 27000.0,
      'Bihar': 27000.0,
      'Bengal': 14400.0,
    },
  };

  static const List<Map<String, String>> _quickRef = [
    {'label': '1 Bigha', 'value': '27,000 sq ft'},
    {'label': '1 Katha (UP)', 'value': '720 sq ft'},
    {'label': '1 Katha (Bihar)', 'value': '1,361 sq ft'},
    {'label': '1 Marla', 'value': '272.25 sq ft'},
    {'label': '1 Acre', 'value': '43,560 sq ft'},
    {'label': '1 Hectare', 'value': '1,07,639 sq ft'},
    {'label': '1 Gaj', 'value': '9 sq ft'},
    {'label': '1 Acre', 'value': '4,840 sq yard'},
  ];

  @override
  void initState() {
    super.initState();
    _swapAnimController = AnimationController(
      duration: const Duration(milliseconds: 300),
      vsync: this,
    );
    _swapRotation = Tween<double>(begin: 0, end: 3.14159).animate(
      CurvedAnimation(parent: _swapAnimController, curve: Curves.easeInOut),
    );
  }

  @override
  void dispose() {
    _inputController.dispose();
    _swapAnimController.dispose();
    super.dispose();
  }

  double _getConversionFactor(String unit) {
    if (_regionBasedToSqFt.containsKey(unit)) {
      return _regionBasedToSqFt[unit]![_region]!;
    }
    return _toSqFt[unit]!;
  }

  double _convert(double value) {
    if (value <= 0) return 0;
    final fromFactor = _getConversionFactor(_fromUnit);
    final toFactor = _getConversionFactor(_toUnit);
    final inSqFt = value * fromFactor;
    return inSqFt / toFactor;
  }

  void _swapUnits() {
    _swapAnimController.forward().then((_) {
      _swapAnimController.reset();
    });
    setState(() {
      final temp = _fromUnit;
      _fromUnit = _toUnit;
      _toUnit = temp;
    });
  }

  String _formatResult(double value) {
    if (value == 0) return '0';
    if (value >= 10000000) {
      return '${(value / 10000000).toStringAsFixed(2)} Cr';
    } else if (value >= 100000) {
      return '${(value / 100000).toStringAsFixed(2)} Lakh';
    } else if (value >= 1000) {
      return value.toStringAsFixed(2);
    } else if (value == value.roundToDouble() && value < 1000) {
      return value.toStringAsFixed(0);
    }
    return value.toStringAsFixed(4);
  }

  @override
  Widget build(BuildContext context) {
    final inputValue = double.tryParse(_inputController.text) ?? 0;
    final result = _convert(inputValue);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Plot Converter'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildHeader(),
            const SizedBox(height: 24),
            _buildConverterCard(inputValue, result),
            const SizedBox(height: 24),
            _buildRegionSelector(),
            const SizedBox(height: 24),
            _buildQuickReference(),
            const SizedBox(height: 32),
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
          colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(Icons.swap_horiz, size: 40, color: Colors.white),
          const SizedBox(height: 12),
          const Text(
            'Plot Converter',
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Convert between Indian and metric land measurement units',
            style: TextStyle(
              fontSize: 13,
              color: Colors.white.withValues(alpha: 0.85),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildConverterCard(double inputValue, double result) {
    return Card(
      elevation: 4,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            _buildUnitDropdown(
              label: 'From',
              value: _fromUnit,
              excludeUnit: _toUnit,
              onChanged: (val) => setState(() => _fromUnit = val!),
            ),
            const SizedBox(height: 16),
            Center(
              child: AnimatedBuilder(
                animation: _swapRotation,
                builder: (context, child) {
                  return Transform.rotate(
                    angle: _swapRotation.value,
                    child: child,
                  );
                },
                child: GestureDetector(
                  onTap: _swapUnits,
                  child: Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: AppTheme.primaryColor.withValues(alpha: 0.1),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(
                      Icons.swap_vert,
                      color: AppTheme.primaryColor,
                      size: 28,
                    ),
                  ),
                ),
              ),
            ),
            const SizedBox(height: 16),
            _buildUnitDropdown(
              label: 'To',
              value: _toUnit,
              excludeUnit: _fromUnit,
              onChanged: (val) => setState(() => _toUnit = val!),
            ),
            const SizedBox(height: 24),
            TextField(
              controller: _inputController,
              keyboardType:
                  const TextInputType.numberWithOptions(decimal: true),
              inputFormatters: [
                FilteringTextInputFormatter.allow(RegExp(r'^\d*\.?\d{0,4}')),
              ],
              onChanged: (_) => setState(() {}),
              style: const TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w600,
              ),
              decoration: InputDecoration(
                labelText: 'Enter value',
                prefixIcon: const Icon(Icons.numbers,
                    size: 20, color: AppTheme.primaryColor),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
                focusedBorder: const OutlineInputBorder(
                  borderSide:
                      BorderSide(color: AppTheme.primaryColor, width: 2),
                ),
              ),
            ),
            const SizedBox(height: 24),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppTheme.primaryColor.withValues(alpha: 0.06),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: AppTheme.primaryColor.withValues(alpha: 0.2),
                ),
              ),
              child: Column(
                children: [
                  Text(
                    _formatResult(result),
                    style: const TextStyle(
                      fontSize: 36,
                      fontWeight: FontWeight.bold,
                      color: AppTheme.primaryColor,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 4),
                  Text(
                    _toUnit,
                    style: TextStyle(
                      fontSize: 14,
                      color: Colors.grey.shade600,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    '$inputValue $_fromUnit = ${_formatResult(result)} $_toUnit',
                    style: TextStyle(
                      fontSize: 13,
                      color: Colors.grey.shade500,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildUnitDropdown({
    required String label,
    required String value,
    required String excludeUnit,
    required ValueChanged<String?> onChanged,
  }) {
    final filteredUnits =
        _units.where((u) => u != excludeUnit).toList();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w600,
            color: AppTheme.textPrimaryLight,
          ),
        ),
        const SizedBox(height: 8),
        DropdownButtonFormField<String>(
          initialValue: filteredUnits.contains(value) ? value : filteredUnits.first,
          isExpanded: true,
          icon:
              const Icon(Icons.keyboard_arrow_down, color: AppTheme.primaryColor),
          decoration: InputDecoration(
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(10),
            ),
            focusedBorder: const OutlineInputBorder(
              borderSide: BorderSide(color: AppTheme.primaryColor, width: 2),
            ),
            contentPadding:
                const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
          ),
          items: filteredUnits.map((String unit) {
            return DropdownMenuItem<String>(value: unit, child: Text(unit));
          }).toList(),
          onChanged: onChanged,
        ),
      ],
    );
  }

  Widget _buildRegionSelector() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(Icons.map_outlined, size: 20, color: AppTheme.primaryColor),
                SizedBox(width: 8),
                Text(
                  'Region',
                  style: TextStyle(
                    fontSize: 15,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.textPrimaryLight,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 4),
            Text(
              'Katha and Marla values vary by region',
              style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
            ),
            const SizedBox(height: 12),
            Row(
              children: _regions.map((region) {
                final isSelected = _region == region;
                return Expanded(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 4),
                    child: GestureDetector(
                      onTap: () => setState(() => _region = region),
                      child: Container(
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        decoration: BoxDecoration(
                          color: isSelected
                              ? AppTheme.primaryColor
                              : Colors.transparent,
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(
                            color: isSelected
                                ? AppTheme.primaryColor
                                : Colors.grey.shade300,
                          ),
                        ),
                        child: Center(
                          child: Text(
                            region,
                            style: TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.w600,
                              color: isSelected
                                  ? Colors.white
                                  : AppTheme.textPrimaryLight,
                            ),
                          ),
                        ),
                      ),
                    ),
                  ),
                );
              }).toList(),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildQuickReference() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(Icons.info_outline,
                    size: 20, color: AppTheme.primaryColor),
                SizedBox(width: 8),
                Text(
                  'Quick Reference',
                  style: TextStyle(
                    fontSize: 15,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.textPrimaryLight,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            ...List.generate(_quickRef.length, (index) {
              final item = _quickRef[index];
              return Container(
                margin: const EdgeInsets.only(bottom: 8),
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                decoration: BoxDecoration(
                  color: index.isEven
                      ? AppTheme.primaryColor.withValues(alpha: 0.04)
                      : Colors.transparent,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      item['label']!,
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                        color: AppTheme.textPrimaryLight,
                      ),
                    ),
                    Text(
                      item['value']!,
                      style: const TextStyle(
                        fontSize: 13,
                        color: AppTheme.primaryColor,
                        fontWeight: FontWeight.w500,
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
}

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:share_plus/share_plus.dart';

import '../../../core/theme/app_theme.dart';

class StampDutyCalculatorPage extends StatefulWidget {
  const StampDutyCalculatorPage({super.key});

  @override
  State<StampDutyCalculatorPage> createState() =>
      _StampDutyCalculatorPageState();
}

class _StampDutyCalculatorPageState extends State<StampDutyCalculatorPage> {
  final _valueController = TextEditingController();
  String _selectedState = 'Uttar Pradesh';
  String _propertyType = 'Residential';
  String _buyerType = 'First-time buyer';
  bool _calculated = false;

  double _stampDuty = 0;
  double _registrationFee = 0;
  double _gstAmount = 0;
  double _totalCost = 0;
  double _propertyValue = 0;

  static const Map<String, Map<String, dynamic>> _stateRates = {
    'Uttar Pradesh': {
      'stampDuty': 0.05,
      'stampDutyMale': 0.07,
      'registration': 0.01,
      'gst': false,
      'nriSurcharge': 0.0,
    },
    'Maharashtra': {
      'stampDuty': 0.06,
      'registration': 0.01,
      'gst': false,
      'nriSurcharge': 0.0,
    },
    'Karnataka': {
      'stampDuty': 0.056,
      'registration': 0.01,
      'gst': false,
      'nriSurcharge': 0.0,
    },
    'Delhi': {
      'stampDuty': 0.04,
      'registration': 0.01,
      'gst': false,
      'nriSurcharge': 0.0,
    },
    'Gujarat': {
      'stampDuty': 0.049,
      'registration': 0.01,
      'gst': false,
      'nriSurcharge': 0.0,
    },
    'Tamil Nadu': {
      'stampDuty': 0.07,
      'registration': 0.01,
      'gst': false,
      'nriSurcharge': 0.0,
    },
    'Rajasthan': {
      'stampDuty': 0.05,
      'registration': 0.01,
      'gst': false,
      'nriSurcharge': 0.0,
    },
    'Madhya Pradesh': {
      'stampDuty': 0.05,
      'registration': 0.01,
      'gst': false,
      'nriSurcharge': 0.0,
    },
    'Bihar': {
      'stampDuty': 0.06,
      'registration': 0.01,
      'gst': false,
      'nriSurcharge': 0.0,
    },
    'West Bengal': {
      'stampDuty': 0.06,
      'registration': 0.01,
      'gst': false,
      'nriSurcharge': 0.0,
    },
  };

  static const List<String> _states = [
    'Uttar Pradesh',
    'Maharashtra',
    'Karnataka',
    'Delhi',
    'Gujarat',
    'Tamil Nadu',
    'Rajasthan',
    'Madhya Pradesh',
    'Bihar',
    'West Bengal',
  ];

  static const List<String> _propertyTypes = [
    'Residential',
    'Commercial',
    'Industrial',
  ];

  static const List<String> _buyerTypes = [
    'First-time buyer',
    'Second property',
    'NRI',
  ];

  @override
  void dispose() {
    _valueController.dispose();
    super.dispose();
  }

  void _calculate() {
    final value = double.tryParse(_valueController.text) ?? 0;
    if (value <= 0) return;

    final rates = _stateRates[_selectedState]!;
    double stampRate = rates['stampDuty'] as double;

    if (_selectedState == 'Uttar Pradesh' && _buyerType == 'First-time buyer') {
      stampRate = rates['stampDutyMale'] as double;
    }

    if (_buyerType == 'NRI') {
      stampRate += rates['nriSurcharge'] as double;
    }

    _stampDuty = value * stampRate;
    _registrationFee = value * (rates['registration'] as double);

    if (rates['gst'] == true && _propertyType == 'Commercial') {
      _gstAmount = value * 0.18;
    } else {
      _gstAmount = 0;
    }

    _totalCost = _stampDuty + _registrationFee + _gstAmount;
    _propertyValue = value;

    setState(() => _calculated = true);
  }

  void _shareResult() {
    final text = 'Stamp Duty Calculation\n'
        'State: $_selectedState\n'
        'Property Type: $_propertyType\n'
        'Property Value: ₹${_formatIndian(_propertyValue)}\n'
        'Stamp Duty: ₹${_formatIndian(_stampDuty)}\n'
        'Registration Fee: ₹${_formatIndian(_registrationFee)}\n'
        '${_gstAmount > 0 ? 'GST: ₹${_formatIndian(_gstAmount)}\n' : ''}'
        'Total Cost: ₹${_formatIndian(_totalCost)}';

    Share.share(text);
  }

  void _saveCalculation() {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: const Text('Calculation saved successfully'),
        backgroundColor: AppTheme.successColor,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
        margin: const EdgeInsets.all(16),
      ),
    );
  }

  String _formatIndian(double number) {
    if (number == 0) return '0';
    final fixed = number.toStringAsFixed(2);
    final parts = fixed.split('.');
    String intPart = parts[0];
    final decPart = parts[1];

    if (intPart.length > 3) {
      final lastThree = intPart.substring(intPart.length - 3);
      final remaining = intPart.substring(0, intPart.length - 3);
      final formatted =
          remaining.replaceAllMapped(RegExp(r'(\d{2})(?=\d)'), (m) => '${m[1]},');
      intPart = '$formatted,$lastThree';
    }

    if (decPart == '00') return intPart;
    return '$intPart.$decPart';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Stamp Duty Calculator'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildHeader(),
            const SizedBox(height: 24),
            _buildInputSection(),
            const SizedBox(height: 24),
            _buildDropdowns(),
            const SizedBox(height: 24),
            _buildCalculateButton(),
            if (_calculated) ...[
              const SizedBox(height: 24),
              _buildResultCard(),
              const SizedBox(height: 16),
              _buildActionButtons(),
            ],
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
          const Icon(Icons.receipt_long, size: 40, color: Colors.white),
          const SizedBox(height: 12),
          const Text(
            'Stamp Duty Calculator',
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Calculate stamp duty, registration fee and total cost for your property',
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
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Property Value',
              style: TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.w600,
                color: AppTheme.textPrimaryLight,
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _valueController,
              keyboardType: TextInputType.number,
              inputFormatters: [FilteringTextInputFormatter.digitsOnly],
              style: const TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w600,
              ),
              decoration: InputDecoration(
                prefixIcon: const Padding(
                  padding: EdgeInsets.only(left: 12, right: 4),
                  child: Text(
                    '₹',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: AppTheme.primaryColor,
                    ),
                  ),
                ),
                prefixIconConstraints:
                    const BoxConstraints(minWidth: 36, minHeight: 0),
                hintText: 'e.g. 5000000',
                hintStyle: TextStyle(color: Colors.grey.shade400),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
                focusedBorder: const OutlineInputBorder(
                  borderSide:
                      BorderSide(color: AppTheme.primaryColor, width: 2),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDropdowns() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            _buildDropdown(
              label: 'State',
              value: _selectedState,
              items: _states,
              icon: Icons.map_outlined,
              onChanged: (val) => setState(() {
                _selectedState = val!;
                _calculated = false;
              }),
            ),
            const SizedBox(height: 16),
            _buildDropdown(
              label: 'Property Type',
              value: _propertyType,
              items: _propertyTypes,
              icon: Icons.home_outlined,
              onChanged: (val) => setState(() {
                _propertyType = val!;
                _calculated = false;
              }),
            ),
            const SizedBox(height: 16),
            _buildDropdown(
              label: 'Buyer Type',
              value: _buyerType,
              items: _buyerTypes,
              icon: Icons.person_outline,
              onChanged: (val) => setState(() {
                _buyerType = val!;
                _calculated = false;
              }),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDropdown({
    required String label,
    required String value,
    required List<String> items,
    required IconData icon,
    required ValueChanged<String?> onChanged,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w600,
            color: AppTheme.textPrimaryLight,
          ),
        ),
        const SizedBox(height: 8),
        DropdownButtonFormField<String>(
          initialValue: value,
          isExpanded: true,
          icon: const Icon(Icons.keyboard_arrow_down, color: AppTheme.primaryColor),
          decoration: InputDecoration(
            prefixIcon: Icon(icon, size: 20, color: AppTheme.primaryColor),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(8),
            ),
            focusedBorder: const OutlineInputBorder(
              borderSide: BorderSide(color: AppTheme.primaryColor, width: 2),
            ),
            contentPadding:
                const EdgeInsets.symmetric(horizontal: 12, vertical: 14),
          ),
          items: items.map((String item) {
            return DropdownMenuItem<String>(value: item, child: Text(item));
          }).toList(),
          onChanged: onChanged,
        ),
      ],
    );
  }

  Widget _buildCalculateButton() {
    return SizedBox(
      width: double.infinity,
      height: 52,
      child: ElevatedButton(
        onPressed: _calculate,
        style: ElevatedButton.styleFrom(
          backgroundColor: AppTheme.primaryColor,
          foregroundColor: Colors.white,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          elevation: 2,
        ),
        child: const Text(
          'Calculate',
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
    );
  }

  Widget _buildResultCard() {
    return Card(
      elevation: 4,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Container(
        width: double.infinity,
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(16),
          gradient: LinearGradient(
            colors: [
              AppTheme.primaryColor.withValues(alpha: 0.05),
              Colors.white,
            ],
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
          ),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(Icons.summarize_outlined,
                    size: 22, color: AppTheme.primaryColor),
                SizedBox(width: 10),
                Text(
                  'Breakdown',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.textPrimaryLight,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 6),
            Text(
              'Property Value: ₹${_formatIndian(_propertyValue)}',
              style: TextStyle(fontSize: 13, color: Colors.grey.shade600),
            ),
            const SizedBox(height: 4),
            Text(
              '$_selectedState | $_propertyType | $_buyerType',
              style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
            ),
            const SizedBox(height: 20),
            _buildBreakdownRow(
              'Stamp Duty',
              '₹${_formatIndian(_stampDuty)}',
              AppTheme.primaryColor,
              Icons.receipt,
            ),
            const SizedBox(height: 14),
            _buildBreakdownRow(
              'Registration Fee',
              '₹${_formatIndian(_registrationFee)}',
              AppTheme.infoColor,
              Icons.app_registration,
            ),
            if (_gstAmount > 0) ...[
              const SizedBox(height: 14),
              _buildBreakdownRow(
                'GST (18%)',
                '₹${_formatIndian(_gstAmount)}',
                AppTheme.warningColor,
                Icons.receipt_long,
              ),
            ],
            const SizedBox(height: 16),
            Divider(color: Colors.grey.shade300),
            const SizedBox(height: 16),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Total Cost',
                  style: TextStyle(
                    fontSize: 17,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.textPrimaryLight,
                  ),
                ),
                Text(
                  '₹${_formatIndian(_totalCost)}',
                  style: const TextStyle(
                    fontSize: 22,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.successColor,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBreakdownRow(
      String label, String value, Color color, IconData icon) {
    return Row(
      children: [
        Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: color.withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(icon, size: 18, color: color),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Text(
            label,
            style: const TextStyle(
              fontSize: 14,
              color: AppTheme.textPrimaryLight,
            ),
          ),
        ),
        Text(
          value,
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w600,
            color: color,
          ),
        ),
      ],
    );
  }

  Widget _buildActionButtons() {
    return Row(
      children: [
        Expanded(
          child: OutlinedButton.icon(
            onPressed: _saveCalculation,
            icon: const Icon(Icons.bookmark_outline, size: 18),
            label: const Text('Save'),
            style: OutlinedButton.styleFrom(
              foregroundColor: AppTheme.primaryColor,
              side: const BorderSide(color: AppTheme.primaryColor),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(10),
              ),
              padding: const EdgeInsets.symmetric(vertical: 14),
            ),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: OutlinedButton.icon(
            onPressed: _shareResult,
            icon: const Icon(Icons.share_outlined, size: 18),
            label: const Text('Share'),
            style: OutlinedButton.styleFrom(
              foregroundColor: AppTheme.secondaryColor,
              side: const BorderSide(color: AppTheme.secondaryColor),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(10),
              ),
              padding: const EdgeInsets.symmetric(vertical: 14),
            ),
          ),
        ),
      ],
    );
  }
}

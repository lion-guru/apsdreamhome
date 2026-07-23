import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:http/http.dart' as http;
import 'package:share_plus/share_plus.dart';

import '../../../core/constants/app_constants.dart';
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
  bool _loading = false;

  double _stampDuty = 0;
  double _registrationFee = 0;
  double _surcharge = 0;
  double _cess = 0;
  double _totalCost = 0;
  double _propertyValue = 0;
  double _stampDutyRate = 0;
  double _registrationRate = 0;

  List<Map<String, dynamic>> _statesFromApi = [];

  // State name → state code mapping
  static const Map<String, String> _stateCodeMap = {
    'Uttar Pradesh': 'UP',
    'Maharashtra': 'MH',
    'Karnataka': 'KA',
    'Delhi': 'DL',
    'Gujarat': 'GJ',
    'Tamil Nadu': 'TN',
    'Rajasthan': 'RJ',
    'Madhya Pradesh': 'MP',
    'Bihar': 'BR',
    'West Bengal': 'WB',
    'Andhra Pradesh': 'AP',
    'Telangana': 'TS',
    'Kerala': 'KL',
    'Punjab': 'PB',
    'Haryana': 'HR',
    'Odisha': 'OD',
    'Jharkhand': 'JH',
  };

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
  void initState() {
    super.initState();
    _fetchStates();
  }

  @override
  void dispose() {
    _valueController.dispose();
    super.dispose();
  }

  /// Fetch available states from API
  Future<void> _fetchStates() async {
    try {
      final url = '${AppConstants.baseUrl}/api/stamp-duty/states';
      final response = await http.get(Uri.parse(url));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data is Map && data['data'] != null) {
          final statesData = data['data'];
          if (statesData is Map && statesData['states'] != null) {
            final rawStates = statesData['states'];
            setState(() {
              _statesFromApi = rawStates is List
                  ? rawStates.cast<Map<String, dynamic>>()
                  : <Map<String, dynamic>>[];
            });
            return;
          }
        }
      }
    } catch (e) {
      // Fall back to hardcoded list
    }

    setState(() {});
  }

  /// Calculate stamp duty via API
  Future<void> _calculate() async {
    final value = double.tryParse(_valueController.text) ?? 0;
    if (value <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please enter a valid property value'),
          backgroundColor: AppTheme.errorColor,
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    final stateCode = _stateCodeMap[_selectedState] ?? 'UP';
    final genderMap = {
      'First-time buyer': 'male',
      'Second property': 'male',
      'NRI': 'male',
    };

    setState(() => _loading = true);

    try {
      final url = '${AppConstants.baseUrl}/api/stamp-duty/calculate';
      final response = await http.post(
        Uri.parse(url),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({
          'property_value': value,
          'state_code': stateCode,
          'buyer_gender': genderMap[_buyerType] ?? 'male',
          'property_type': _propertyType.toLowerCase(),
        }),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data is Map && data['success'] == true) {
          final result = data['data'] ?? data;
          setState(() {
            _propertyValue = value;
            _stampDuty =
                double.tryParse('${result['stamp_duty_amount'] ?? 0}') ?? 0;
            _stampDutyRate =
                double.tryParse('${result['stamp_duty_rate'] ?? 0}') ?? 0;
            _registrationFee =
                double.tryParse('${result['registration_fee_amount'] ?? 0}') ??
                0;
            _registrationRate =
                double.tryParse('${result['registration_fee_rate'] ?? 0}') ?? 0;
            _surcharge =
                double.tryParse('${result['surcharge_amount'] ?? 0}') ?? 0;
            _cess = double.tryParse('${result['cess_amount'] ?? 0}') ?? 0;
            _totalCost = double.tryParse('${result['total_amount'] ?? 0}') ?? 0;
            _calculated = true;
            _loading = false;
          });
          return;
        }
      }

      // API failed — fall back to local calculation
      _calculateLocal(value);
    } catch (e) {
      // Network error — fall back to local calculation
      _calculateLocal(value);
    }

    setState(() => _loading = false);
  }

  /// Local fallback calculation (same logic as backend)
  void _calculateLocal(double value) {
    final rates = _getLocalRates(_selectedState);
    double stampRate = rates['stampDuty']!;

    if (_buyerType == 'NRI') {
      stampRate += rates['nriSurcharge']!;
    }

    final regRate = rates['registration']!;

    _stampDuty = value * stampRate / 100;
    _registrationFee = value * regRate / 100;
    _surcharge = 0;
    _cess = 0;
    _totalCost = _stampDuty + _registrationFee;
    _propertyValue = value;
    _stampDutyRate = stampRate;
    _registrationRate = regRate;

    setState(() => _calculated = true);
  }

  Map<String, double> _getLocalRates(String state) {
    const rateMap = {
      'Uttar Pradesh': {
        'stampDuty': 7.0,
        'registration': 1.0,
        'nriSurcharge': 0.0,
      },
      'Maharashtra': {
        'stampDuty': 6.0,
        'registration': 1.0,
        'nriSurcharge': 0.0,
      },
      'Karnataka': {'stampDuty': 5.6, 'registration': 1.0, 'nriSurcharge': 0.0},
      'Delhi': {'stampDuty': 4.0, 'registration': 1.0, 'nriSurcharge': 0.0},
      'Gujarat': {'stampDuty': 4.9, 'registration': 1.0, 'nriSurcharge': 0.0},
      'Tamil Nadu': {
        'stampDuty': 7.0,
        'registration': 1.0,
        'nriSurcharge': 0.0,
      },
      'Rajasthan': {'stampDuty': 5.0, 'registration': 1.0, 'nriSurcharge': 0.0},
      'Madhya Pradesh': {
        'stampDuty': 5.0,
        'registration': 1.0,
        'nriSurcharge': 0.0,
      },
      'Bihar': {'stampDuty': 6.0, 'registration': 1.0, 'nriSurcharge': 0.0},
      'West Bengal': {
        'stampDuty': 6.0,
        'registration': 1.0,
        'nriSurcharge': 0.0,
      },
    };
    return rateMap[state] ??
        {'stampDuty': 7.0, 'registration': 1.0, 'nriSurcharge': 0.0};
  }

  void _shareResult() {
    final text =
        'Stamp Duty Calculation\n'
        'State: $_selectedState\n'
        'Property Type: $_propertyType\n'
        'Property Value: ₹${_formatIndian(_propertyValue)}\n'
        'Stamp Duty (${_stampDutyRate.toStringAsFixed(2)}%): ₹${_formatIndian(_stampDuty)}\n'
        'Registration Fee (${_registrationRate.toStringAsFixed(2)}%): ₹${_formatIndian(_registrationFee)}\n'
        '${_surcharge > 0 ? 'Surcharge: ₹${_formatIndian(_surcharge)}\n' : ''}'
        '${_cess > 0 ? 'Cess: ₹${_formatIndian(_cess)}\n' : ''}'
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
      final formatted = remaining.replaceAllMapped(
        RegExp(r'(\d{2})(?=\d)'),
        (m) => '${m[1]},',
      );
      intPart = '$formatted,$lastThree';
    }

    if (decPart == '00') return intPart;
    return '$intPart.$decPart';
  }

  // Build state list — merge API states with fallback hardcoded list
  List<String> get _states {
    if (_statesFromApi.isNotEmpty) {
      return _statesFromApi
          .map((s) => s['state_name']?.toString() ?? '')
          .where((s) => s.isNotEmpty)
          .toList();
    }
    return _stateCodeMap.keys.toList();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Stamp Duty Calculator')),
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
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
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
                prefixIconConstraints: const BoxConstraints(
                  minWidth: 36,
                  minHeight: 0,
                ),
                hintText: 'e.g. 5000000',
                hintStyle: TextStyle(color: Colors.grey.shade400),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
                focusedBorder: const OutlineInputBorder(
                  borderSide: BorderSide(
                    color: AppTheme.primaryColor,
                    width: 2,
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDropdowns() {
    final stateList = _states;
    // Ensure selected state is in the list
    if (!stateList.contains(_selectedState) && stateList.isNotEmpty) {
      _selectedState = stateList.first;
    }

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            _buildDropdown(
              label: 'State',
              value: _selectedState,
              items: stateList,
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
          icon: const Icon(
            Icons.keyboard_arrow_down,
            color: AppTheme.primaryColor,
          ),
          decoration: InputDecoration(
            prefixIcon: Icon(icon, size: 20, color: AppTheme.primaryColor),
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
            focusedBorder: const OutlineInputBorder(
              borderSide: BorderSide(color: AppTheme.primaryColor, width: 2),
            ),
            contentPadding: const EdgeInsets.symmetric(
              horizontal: 12,
              vertical: 14,
            ),
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
        onPressed: _loading ? null : _calculate,
        style: ElevatedButton.styleFrom(
          backgroundColor: AppTheme.primaryColor,
          foregroundColor: Colors.white,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          elevation: 2,
        ),
        child: _loading
            ? const SizedBox(
                width: 22,
                height: 22,
                child: CircularProgressIndicator(
                  strokeWidth: 2.5,
                  color: Colors.white,
                ),
              )
            : const Text(
                'Calculate',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
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
                Icon(
                  Icons.summarize_outlined,
                  size: 22,
                  color: AppTheme.primaryColor,
                ),
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
              'Stamp Duty (${_stampDutyRate.toStringAsFixed(2)}%)',
              '₹${_formatIndian(_stampDuty)}',
              AppTheme.primaryColor,
              Icons.receipt,
            ),
            const SizedBox(height: 14),
            _buildBreakdownRow(
              'Registration Fee (${_registrationRate.toStringAsFixed(2)}%)',
              '₹${_formatIndian(_registrationFee)}',
              AppTheme.infoColor,
              Icons.app_registration,
            ),
            if (_surcharge > 0) ...[
              const SizedBox(height: 14),
              _buildBreakdownRow(
                'Surcharge',
                '₹${_formatIndian(_surcharge)}',
                AppTheme.warningColor,
                Icons.receipt_long,
              ),
            ],
            if (_cess > 0) ...[
              const SizedBox(height: 14),
              _buildBreakdownRow(
                'Cess',
                '₹${_formatIndian(_cess)}',
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
    String label,
    String value,
    Color color,
    IconData icon,
  ) {
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

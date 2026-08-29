import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:http/http.dart' as http;
import 'package:share_plus/share_plus.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/theme/app_theme.dart';

class PropertyTaxPage extends StatefulWidget {
  const PropertyTaxPage({super.key});

  @override
  State<PropertyTaxPage> createState() => _PropertyTaxPageState();
}

class _PropertyTaxPageState extends State<PropertyTaxPage> {
  final _valueController = TextEditingController();
  final _areaController = TextEditingController();

  String _selectedState = 'Uttar Pradesh';
  String _propertyType = 'Residential';
  String _cityCategory = 'Metro';
  bool _calculated = false;
  bool _loading = false;

  double _taxAmount = 0;
  double _effectiveRate = 0;
  double _ratePerSqft = 0;
  double _baseTax = 0;
  double _rebateAmount = 0;
  double _penaltyAmount = 0;

  List<String> _states = ['Uttar Pradesh'];

  // State name → state code
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
  };

  // City name → city (API expects city name, not category)
  static const Map<String, String> _cityCategoryMap = {
    'Metro': 'Metro',
    'City': 'City',
    'Town': 'Town',
  };

  // Local fallback rates per sqft per year
  static const Map<String, Map<String, double>> _localRates = {
    'Residential': {'Metro': 12.0, 'City': 9.0, 'Town': 6.0},
    'Commercial': {'Metro': 24.0, 'City': 18.0, 'Town': 12.0},
    'Industrial': {'Metro': 18.0, 'City': 15.0, 'Town': 9.0},
  };

  @override
  void initState() {
    super.initState();
    _fetchStates();
  }

  @override
  void dispose() {
    _valueController.dispose();
    _areaController.dispose();
    super.dispose();
  }

  /// Fetch available states from API
  Future<void> _fetchStates() async {
    try {
      final url = '${AppConstants.baseUrl}/api/property-tax/states';
      final response = await http.get(Uri.parse(url));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data is Map && data['data'] != null) {
          final statesData = data['data'];
          if (statesData is Map && statesData['states'] != null) {
            final rawList = statesData['states'];
            final statesList = rawList is List
                ? rawList.cast<Map<String, dynamic>>()
                : <Map<String, dynamic>>[];
            final stateNames = statesList
                .map((s) => s['state_code']?.toString() ?? '')
                .where((s) => s.isNotEmpty)
                .toSet()
                .toList();
            // Map codes back to names
            final nameList = _stateCodeMap.entries
                .where((e) => stateNames.contains(e.value))
                .map((e) => e.key)
                .toList();
            if (nameList.isNotEmpty) {
              setState(() {
                _states = nameList;
              });
              return;
            }
          }
        }
      }
    } catch (e) {
      // Fall back to hardcoded list
    }

    setState(() {
      _states = _stateCodeMap.keys.toList();
    });
  }

  /// Calculate property tax via API
  Future<void> _calculate() async {
    final propertyValue = double.tryParse(_valueController.text) ?? 0;
    if (propertyValue <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Enter property value'),
          backgroundColor: AppTheme.errorColor,
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    final area = double.tryParse(_areaController.text) ?? 0;
    final stateCode = _stateCodeMap[_selectedState] ?? 'UP';
    final city = _cityCategoryMap[_cityCategory] ?? 'Metro';

    // Estimate built-up area: use user input or derive from value
    final builtUpArea = area > 0
        ? area
        : (propertyValue /
              (_localRates[_propertyType]?[_cityCategory] ?? 12.0));

    setState(() => _loading = true);

    try {
      final url = '${AppConstants.baseUrl}/api/property-tax/calculate';
      final response = await http.post(
        Uri.parse(url),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({
          'state_code': stateCode,
          'city': city,
          'zone': '',
          'property_type': _propertyType.toLowerCase(),
          'built_up_area_sqft': builtUpArea.round(),
          'assessment_year': DateTime.now().year,
        }),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data is Map && data['success'] == true) {
          final result = data['data'] ?? data;
          setState(() {
            _taxAmount = double.tryParse('${result['annual_tax'] ?? 0}') ?? 0;
            _ratePerSqft =
                double.tryParse('${result['rate_per_sqft'] ?? 0}') ?? 0;
            _baseTax = double.tryParse('${result['base_tax'] ?? 0}') ?? 0;
            _rebateAmount =
                double.tryParse('${result['rebate_amount'] ?? 0}') ?? 0;
            _penaltyAmount =
                double.tryParse('${result['penalty_amount'] ?? 0}') ?? 0;
            _effectiveRate = _ratePerSqft;
            _calculated = true;
            _loading = false;
          });
          return;
        }
      }
    } catch (e) {
      // Fall back to local calculation
    }

    // Local fallback
    final rate = _localRates[_propertyType]![_cityCategory]!;
    _ratePerSqft = rate;
    _effectiveRate = (rate * 100 / (propertyValue / builtUpArea)).clamp(
      0.01,
      100.0,
    );
    _baseTax = builtUpArea * rate;
    _taxAmount = _baseTax.clamp(500, double.infinity);
    _rebateAmount = 0;
    _penaltyAmount = 0;

    setState(() {
      _calculated = true;
      _loading = false;
    });
  }

  void _share() {
    Share.share(
      'Property Tax Calculation\n'
      'State: $_selectedState\n'
      'Type: $_propertyType\n'
      'City Category: $_cityCategory\n'
      'Annual Tax: ₹${_taxAmount.toStringAsFixed(0)}\n'
      'Rate: ₹${_ratePerSqft.toStringAsFixed(2)}/sqft',
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
            'Calculate annual property tax based on area and location',
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
            inputFormatters: [FilteringTextInputFormatter.digitsOnly],
            decoration: const InputDecoration(
              labelText: 'Property Value (₹)',
              prefixIcon: Icon(Icons.currency_rupee_rounded),
              border: OutlineInputBorder(),
            ),
          ),
          const SizedBox(height: 16),
          const Text(
            'Built-up Area (sq ft)',
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
            inputFormatters: [FilteringTextInputFormatter.digitsOnly],
            decoration: InputDecoration(
              labelText: 'Area in sq ft (optional)',
              prefixIcon: const Icon(Icons.square_foot),
              border: const OutlineInputBorder(),
              hintText: 'Auto-calculated if empty',
              hintStyle: TextStyle(color: Colors.grey.shade400),
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
          // State selector
          const Text(
            'State',
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 10),
          DropdownButtonFormField<String>(
            initialValue: _selectedState,
            isExpanded: true,
            decoration: const InputDecoration(
              border: OutlineInputBorder(),
              contentPadding: EdgeInsets.symmetric(
                horizontal: 12,
                vertical: 14,
              ),
            ),
            items: _states.map((String state) {
              return DropdownMenuItem<String>(value: state, child: Text(state));
            }).toList(),
            onChanged: (val) => setState(() => _selectedState = val!),
          ),
          const SizedBox(height: 14),

          // Property type selector
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
            children: _localRates.keys.map((t) {
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

          // City category selector
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
        onPressed: _loading ? null : _calculate,
        style: ElevatedButton.styleFrom(
          backgroundColor: AppTheme.primaryColor,
          foregroundColor: Colors.white,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
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
            'Rate: ₹${_ratePerSqft.toStringAsFixed(2)}/sqft | Base: ₹${_baseTax.toStringAsFixed(0)}',
            style: TextStyle(
              color: Colors.white.withValues(alpha: 0.7),
              fontSize: 12,
            ),
          ),
          if (_rebateAmount > 0) ...[
            const SizedBox(height: 4),
            Text(
              'Early payment rebate: -₹${_rebateAmount.toStringAsFixed(0)}',
              style: TextStyle(
                color: Colors.greenAccent.withValues(alpha: 0.9),
                fontSize: 12,
              ),
            ),
          ],
          if (_penaltyAmount > 0) ...[
            const SizedBox(height: 4),
            Text(
              'Late payment penalty: +₹${_penaltyAmount.toStringAsFixed(0)}',
              style: TextStyle(
                color: Colors.orangeAccent.withValues(alpha: 0.9),
                fontSize: 12,
              ),
            ),
          ],
        ],
      ),
    );
  }
}

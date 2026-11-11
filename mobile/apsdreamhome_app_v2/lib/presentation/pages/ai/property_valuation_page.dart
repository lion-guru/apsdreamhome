import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/services/api_service.dart';
import '../../../core/utils/logger.dart';

class PropertyValuationPage extends ConsumerStatefulWidget {
  final String? propertyId;

  const PropertyValuationPage({
    super.key,
    this.propertyId,
  });

  @override
  ConsumerState<PropertyValuationPage> createState() =>
      _PropertyValuationPageState();
}

class _PropertyValuationPageState extends ConsumerState<PropertyValuationPage> {
  static const Color primaryColor = Color(0xFF0B194C);
  static const Color accentColor = Color(0xFFF58220);

  Map<String, dynamic>? _valuationResult;
  bool _isProcessing = false;

  final TextEditingController _areaController = TextEditingController();
  final TextEditingController _roadWidthController = TextEditingController();
  String _selectedColony = 'Suryoday Colony';
  String _selectedPropertyType = 'Residential';
  String _selectedFacing = 'East';
  String _selectedRoadType = 'Public';
  bool _hasCornerPlot = false;
  bool _hasParkFacing = false;
  bool _hasWiderRoad = false;

  final List<String> _colonies = [
    'Suryoday Colony',
    'Braj Radha Nagri',
    'Raghunath Nagri',
    'Budh Bihar',
    'Awadh Vihar',
    'Chandra Green',
    'Shivpuri Enclave',
  ];

  /// Colony name → backend colony_id (fetched live; fallback to global avg)
  final Map<String, int> _colonyIds = {};

  Future<void> _loadColonyIds() async {
    try {
      final api = ApiService();
      final res = await api.get('/colonies');
      final data = res['data'] ?? res['colonies'] ?? res;
      if (data is List) {
        for (final c in data) {
          if (c is Map && c['id'] != null && c['name'] != null) {
            _colonyIds[c['name'].toString()] = (c['id'] as num).toInt();
          }
        }
      }
    } catch (e) {
      // fallback: global-average valuation still works (colony_id optional)
    }
  }

  final List<String> _propertyTypes = [
    'Residential',
    'Commercial',
    'Mixed Use',
    'Industrial',
  ];

  final List<String> _facings = ['East', 'West', 'North', 'South', 'North-East', 'North-West', 'South-East', 'South-West'];

  final List<String> _roadTypes = ['Public', 'Private', 'Gated'];

  @override
  void initState() {
    super.initState();
    _loadColonyIds();
  }

  @override
  void dispose() {
    _areaController.dispose();
    _roadWidthController.dispose();
    super.dispose();
  }

  Future<void> _calculateValuation() async {
    if (_areaController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please enter plot area')),
      );
      return;
    }

    setState(() => _isProcessing = true);
    try {
      final api = ApiService();
      final response = await api.post('/api/ai-valuation/calculator', data: {
        'colony_id': _colonyIds[_selectedColony] ?? 0,
        'area': double.tryParse(_areaController.text) ?? 0,
        'location': _selectedColony,
        'type': _selectedPropertyType,
        'facing': _selectedFacing.toLowerCase(),
        'road_width': double.tryParse(_roadWidthController.text) ?? 0,
        'corner_plot': _hasCornerPlot ? 1 : 0,
        'park_facing': _hasParkFacing ? 1 : 0,
      });

      if (mounted && response['success'] == true) {
        final v = response['valuation'] ?? response['data'] ?? {};
        setState(() {
          _valuationResult = (v is Map) ? Map<String, dynamic>.from(v) : {};
        });
      } else if (mounted) {
        final err = response['error'] as String?;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(err ?? 'Valuation failed'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
        );
      }
    } finally {
      if (mounted) setState(() => _isProcessing = false);
    }
  }

  Widget _buildInputField({
    required String label,
    required TextEditingController controller,
    required TextInputType keyboardType,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: primaryColor)),
        const SizedBox(height: 8),
        TextFormField(
          controller: controller,
          keyboardType: keyboardType,
          decoration: InputDecoration(
            filled: true,
            fillColor: Colors.grey.shade50,
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide(color: Colors.grey.shade300),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: accentColor),
            ),
          ),
        ),
        const SizedBox(height: 16),
      ],
    );
  }

  Widget _buildDropdownField({
    required String label,
    required String value,
    required List<String> items,
    required ValueChanged<String?> onChanged,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: primaryColor)),
        const SizedBox(height: 8),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 12),
          decoration: BoxDecoration(
            color: Colors.grey.shade50,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: Colors.grey.shade300),
          ),
          child: DropdownButtonHideUnderline(
            child: DropdownButton<String>(
              value: value,
              isExpanded: true,
              items: items.map((item) => DropdownMenuItem(value: item, child: Text(item))).toList(),
              onChanged: onChanged,
            ),
          ),
        ),
        const SizedBox(height: 16),
      ],
    );
  }

  Widget _buildBoolChip({
    required String label,
    required bool value,
    required ValueChanged<bool> onChanged,
    required Color color,
  }) {
    return FilterChip(
      label: Text(label),
      selected: value,
      onSelected: (v) => onChanged(v ?? false),
      selectedColor: color,
      checkmarkColor: Colors.white,
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F0),
      appBar: AppBar(
        title: const Text('AI Property Valuation'),
        foregroundColor: primaryColor,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => context.pop(),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Get AI-powered property valuation',
              style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: primaryColor),
            ),
            const SizedBox(height: 8),
            const Text(
              'Enter plot details to get instant AI valuation with confidence score',
              style: TextStyle(fontSize: 14, color: Colors.grey),
            ),
            const SizedBox(height: 24),

            // Input Form
            Card(
              elevation: 2,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildInputField(
                      label: 'Plot Area (sq ft)',
                      controller: _areaController,
                      keyboardType: TextInputType.number,
                    ),
                    _buildInputField(
                      label: 'Road Width (ft)',
                      controller: _roadWidthController,
                      keyboardType: TextInputType.number,
                    ),
                    _buildDropdownField(
                      label: 'Colony',
                      value: _selectedColony,
                      items: _colonies,
                      onChanged: (v) => setState(() => _selectedColony = v!),
                    ),
                    _buildDropdownField(
                      label: 'Property Type',
                      value: _selectedPropertyType,
                      items: _propertyTypes,
                      onChanged: (v) => setState(() => _selectedPropertyType = v!),
                    ),
                    _buildDropdownField(
                      label: 'Facing',
                      value: _selectedFacing,
                      items: _facings,
                      onChanged: (v) => setState(() => _selectedFacing = v!),
                    ),
                    _buildDropdownField(
                      label: 'Road Type',
                      value: _selectedRoadType,
                      items: _roadTypes,
                      onChanged: (v) => setState(() => _selectedRoadType = v!),
                    ),
                    const Text('Additional Features', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: primaryColor)),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Expanded(
                          child: _buildBoolChip(
                            label: 'Corner Plot',
                            value: _hasCornerPlot,
                            onChanged: (v) => setState(() => _hasCornerPlot = v ?? false),
                            color: accentColor,
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: _buildBoolChip(
                            label: 'Park Facing',
                            value: _hasParkFacing,
                            onChanged: (v) => setState(() => _hasParkFacing = v ?? false),
                            color: accentColor,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    _buildBoolChip(
                      label: 'Wider Road',
                      value: _hasWiderRoad,
                      onChanged: (v) => setState(() => _hasWiderRoad = v ?? false),
                      color: accentColor,
                    ),
                    const SizedBox(height: 20),
                    SizedBox(
                      width: double.infinity,
                      height: 52,
                      child: ElevatedButton(
                        onPressed: _isProcessing ? null : _calculateValuation,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: primaryColor,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        child: _isProcessing
                            ? const SizedBox(
                                height: 20,
                                width: 20,
                                child: CircularProgressIndicator(strokeWidth: 2),
                              )
                            : const Text('Calculate Valuation', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                      ),
                    ),
                  ],
                ),
              ),
            ),

            // Results
            if (_valuationResult != null) ...[
              const SizedBox(height: 24),
              Card(
                elevation: 2,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Valuation Result',
                        style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: primaryColor),
                      ),
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          Expanded(
                            child: _buildResultCard(
                              title: 'Estimated Value',
                              value: '₹${(_valuationResult!['estimated_value'] as num? ?? 0).toStringAsFixed(0)}',
                              color: primaryColor,
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: _buildResultCard(
                              title: 'Price per Sq Ft',
                              value: '₹${(_valuationResult!['price_per_sqft'] as num? ?? 0).toStringAsFixed(0)}',
                              color: accentColor,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          Expanded(
                            child: _buildResultCard(
                              title: 'Confidence',
                              value: '${(_valuationResult!['confidence'] as num? ?? 0) * 100}%',
                              color: (_valuationResult!['confidence'] as num? ?? 0) >= 0.8 ? Colors.green : Colors.orange,
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: _buildResultCard(
                              title: 'Factors',
                              value: '${(_valuationResult!['factors'] as List? ?? []).length}',
                              color: Colors.blue,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      if (_valuationResult!['key_factors'] != null &&
                          (_valuationResult!['key_factors'] as List).isNotEmpty) ...[
                        const Text(
                          'Key Factors',
                          style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: primaryColor),
                        ),
                        const SizedBox(height: 8),
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          children: (_valuationResult!['key_factors'] as List).map((factor) => Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                            decoration: BoxDecoration(
                              color: Colors.green.shade50,
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(color: Colors.green.shade200),
                            ),
                            child: Text(factor.toString(), style: const TextStyle(fontSize: 12, color: Colors.green)),
                          )).toList(),
                        ),
                      ],
                    ],
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildResultCard({required String title, required String value, required Color color}) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey.shade200),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: TextStyle(fontSize: 12, color: Colors.grey.shade600, fontWeight: FontWeight.w600)),
          const SizedBox(height: 6),
          Text(value, style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: color)),
        ],
      ),
    );
  }
}

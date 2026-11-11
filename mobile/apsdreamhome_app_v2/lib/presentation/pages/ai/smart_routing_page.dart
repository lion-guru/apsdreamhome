import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/services/api_service.dart';
import '../../../core/utils/logger.dart';

class SmartRoutingPage extends ConsumerStatefulWidget {
  const SmartRoutingPage({super.key});

  @override
  ConsumerState<SmartRoutingPage> createState() => _SmartRoutingPageState();
}

class _SmartRoutingPageState extends ConsumerState<SmartRoutingPage> {
  bool _isLoading = false;
  List<Map<String, dynamic>> _associates = [];
  List<Map<String, dynamic>> _routingResults = [];
  String _selectedAssociateId = '';
  final TextEditingController _leadIdController = TextEditingController();
  String _taskType = 'lead';

  @override
  void initState() {
    super.initState();
    _loadAssociates();
  }

  @override
  void dispose() {
    _leadIdController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F0),
      appBar: AppBar(
        title: const Text('Smart Lead Routing'),
        foregroundColor: const Color(0xFF0B194C),
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
              'Smart Lead Routing',
              style: TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF0B194C)),
            ),
            const SizedBox(height: 8),
            const Text(
              'Automatically assign leads to the best associate based on specialization, location, and workload.',
              style: TextStyle(fontSize: 14, color: Colors.grey),
            ),
            const SizedBox(height: 24),
            _buildTextField(
              label: 'Lead ID',
              hint: 'Enter lead ID to route',
              controller: _leadIdController,
              keyboardType: TextInputType.number,
            ),
            const SizedBox(height: 16),
            _buildDropdownField(
              label: 'Task Type',
              value: _taskType,
              items: ['lead', 'task', 'visit'],
              onChanged: (v) => setState(() => _taskType = v ?? 'lead'),
            ),
            const SizedBox(height: 16),
            const Text(
              'Available Associates',
              style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF0B194C)),
            ),
            const SizedBox(height: 8),
            _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _associates.isEmpty
                    ? const Text('No associates available',
                        style: TextStyle(color: Colors.grey))
                    : ListView.builder(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        itemCount: _associates.length,
                        itemBuilder: (context, index) {
                          final assoc = _associates[index];
                          final assocId = (assoc['id'] ?? '').toString();
                          final isSelected = assocId == _selectedAssociateId;
                          final name = (assoc['name'] ?? 'Associate').toString();
                          final spec = (assoc['specialization'] ?? 'General').toString();
                          final load = (assoc['current_load'] ?? 0).toString();
                          return ListTile(
                            leading: CircleAvatar(
                              backgroundColor: isSelected
                                  ? const Color(0xFF0B194C)
                                  : Colors.grey.shade200,
                              child: Icon(Icons.person,
                                  color:
                                      isSelected ? Colors.white : Colors.grey),
                            ),
                            title: Text(name,
                                style: const TextStyle(
                                    fontWeight: FontWeight.w600)),
                            subtitle: Text(spec,
                                style:
                                    TextStyle(color: Colors.grey.shade600)),
                            trailing:
                                Text('$load active', style: const TextStyle(fontSize: 12)),
                            selected: isSelected,
                            onTap: () =>
                                setState(() => _selectedAssociateId = assocId),
                          );
                        },
                      ),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              height: 52,
              child: ElevatedButton(
                onPressed:
                    _isLoading || _selectedAssociateId.isEmpty ? null : _routeLead,
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF0B194C),
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12)),
                ),
                child: _isLoading
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : const Text('Route Lead',
                        style: TextStyle(
                            fontSize: 16, fontWeight: FontWeight.bold)),
              ),
            ),
            if (_routingResults.isNotEmpty) ...[
              const SizedBox(height: 24),
              Card(
                elevation: 2,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16)),
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Routing Result',
                        style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            color: Color(0xFF0B194C)),
                      ),
                      const SizedBox(height: 16),
                      ..._routingResults.map((r) => _buildResultItem(r)),
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

  Widget _buildTextField({
    required String label,
    required String hint,
    required TextEditingController controller,
    required TextInputType keyboardType,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label,
            style: const TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w600,
                color: Color(0xFF0B194C))),
        const SizedBox(height: 8),
        TextFormField(
          controller: controller,
          keyboardType: keyboardType,
          decoration: InputDecoration(
            hintText: hint,
            filled: true,
            fillColor: Colors.grey.shade50,
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide(color: Colors.grey.shade300),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: Color(0xFFF58220)),
            ),
          ),
        ),
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
        Text(label,
            style: const TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w600,
                color: Color(0xFF0B194C))),
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
              items: items
                  .map((item) =>
                      DropdownMenuItem(value: item, child: Text(item)))
                  .toList(),
              onChanged: onChanged,
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildResultItem(Map<String, dynamic> result) {
    final assignedTo = (result['assigned_to'] ?? '-').toString();
    final reason = (result['reason'] ?? '-').toString();
    final confidence = (result['confidence'] as num? ?? 0) * 100;

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.green.shade50,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.green.shade200),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            children: [
              Icon(Icons.check_circle, color: Colors.green, size: 20),
              SizedBox(width: 8),
              Expanded(
                child: Text(
                  'Lead routed successfully',
                  style: TextStyle(
                      fontWeight: FontWeight.bold, color: Colors.green),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          _buildResultRow('Assigned To', assignedTo),
          _buildResultRow('Reason', reason),
          _buildResultRow('Confidence', '${confidence.toStringAsFixed(0)}%'),
        ],
      ),
    );
  }

  Widget _buildResultRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 100,
            child: Text(label,
                style: TextStyle(fontSize: 13, color: Colors.grey.shade600)),
          ),
          Expanded(
            child: Text(value,
                style:
                    const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
          ),
        ],
      ),
    );
  }

  Future<void> _loadAssociates() async {
    setState(() => _isLoading = true);
    try {
      final api = ApiService();
      final response = await api.webDio.get('ai/routing/associates');
      if (mounted && response.statusCode == 200) {
        final raw = response.data;
        setState(() {
          if (raw is List) {
            _associates = List<Map<String, dynamic>>.from(
              raw.map((e) => Map<String, dynamic>.from(e as Map)),
            );
          }
        });
      }
    } catch (e) {
      if (mounted) {
        AppLogger.error('Failed to load associates', e);
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _routeLead() async {
    setState(() => _isLoading = true);
    try {
      final api = ApiService();
      final response = await api.webDio.post('ai/routing/lead', data: {
        'lead_id': _leadIdController.text.trim(),
        'task_type': _taskType,
        'associate_id': _selectedAssociateId,
      });

      if (mounted) {
        final responseData = response.data as Map<String, dynamic>;
        if (responseData['success'] == true) {
          final raw = responseData['data'];
          setState(() {
            if (raw is List) {
              _routingResults = List<Map<String, dynamic>>.from(
                raw.map((e) => Map<String, dynamic>.from(e as Map)),
              );
            } else if (raw is Map) {
              _routingResults = [Map<String, dynamic>.from(raw)];
            }
          });
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
                content: Text('Lead routed successfully'),
                backgroundColor: Colors.green),
          );
        } else {
        final err = responseData['error'] as String?;
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
                content: Text(err ?? 'Routing failed'),
                backgroundColor: Colors.red),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }
}

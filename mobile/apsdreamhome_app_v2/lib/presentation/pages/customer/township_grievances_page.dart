import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

final townshipGrievancesProvider = FutureProvider<List<dynamic>>((ref) async {
  final api = ApiService();
  AppConstants.initBaseUrl();
  try {
    final res = await api.get('/api/v2/mobile/township-grievances');
    if (res['grievances'] is List) {
      return (res['grievances'] as List).toList();
    }
  } catch (e) {
    debugPrint('Error loading grievances: $e');
  }
  return [];
});

class TownshipGrievancesPage extends ConsumerStatefulWidget {
  const TownshipGrievancesPage({super.key});

  @override
  ConsumerState<TownshipGrievancesPage> createState() => _TownshipGrievancesPageState();
}

class _TownshipGrievancesPageState extends ConsumerState<TownshipGrievancesPage> {
  final _titleController = TextEditingController();
  final _descController = TextEditingController();
  String _selectedCategory = 'water_supply';
  String _selectedPriority = 'medium';
  bool _isSubmitting = false;

  final Map<String, String> _categories = {
    'water_supply': 'Water Supply & STP',
    'street_lighting': 'Street Lighting & Power',
    'drainage_sanitation': 'Drainage & Waste Collection',
    'security_gate': 'Security & Boom Barrier',
    'road_civil_work': 'Roads & Civil Maintenance',
    'park_horticulture': 'Parks & Greenery',
    'other': 'Other Township Matter',
  };

  @override
  void dispose() {
    _titleController.dispose();
    _descController.dispose();
    super.dispose();
  }

  void _showLodgeDialog() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setModalState) => Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          ),
          padding: EdgeInsets.only(
            left: 20,
            right: 20,
            top: 20,
            bottom: MediaQuery.of(ctx).viewInsets.bottom + 24,
          ),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'Lodge Township Grievance',
                      style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: Color(0xFF0B194C)),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close),
                      onPressed: () => Navigator.pop(ctx),
                    ),
                  ],
                ),
                const Text(
                  'Guaranteed 48-hour resolution SLA by APS Field Engineers',
                  style: TextStyle(fontSize: 12, color: Colors.grey),
                ),
                const SizedBox(height: 16),

                // Category
                const Text('Category', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13, color: Color(0xFF0B194C))),
                const SizedBox(height: 6),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14),
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFE8DFD0)),
                  ),
                  child: DropdownButtonHideUnderline(
                    child: DropdownButton<String>(
                      isExpanded: true,
                      value: _selectedCategory,
                      items: _categories.entries.map((e) {
                        return DropdownMenuItem(value: e.key, child: Text(e.value, style: const TextStyle(fontSize: 14)));
                      }).toList(),
                      onChanged: (val) {
                        if (val != null) setModalState(() => _selectedCategory = val);
                      },
                    ),
                  ),
                ),
                const SizedBox(height: 14),

                // Priority
                const Text('Priority', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13, color: Color(0xFF0B194C))),
                const SizedBox(height: 6),
                Row(
                  children: ['low', 'medium', 'high', 'emergency'].map((p) {
                    final isSel = _selectedPriority == p;
                    return Expanded(
                      child: Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 3),
                        child: InkWell(
                          onTap: () => setModalState(() => _selectedPriority = p),
                          borderRadius: BorderRadius.circular(8),
                          child: Container(
                            padding: const EdgeInsets.symmetric(vertical: 8),
                            decoration: BoxDecoration(
                              color: isSel ? const Color(0xFF0B194C) : const Color(0xFFFAF7F0),
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(color: isSel ? const Color(0xFF0B194C) : const Color(0xFFE8DFD0)),
                            ),
                            alignment: Alignment.center,
                            child: Text(
                              p.toUpperCase(),
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w700,
                                color: isSel ? const Color(0xFFD4AF37) : const Color(0xFF1E293B),
                              ),
                            ),
                          ),
                        ),
                      ),
                    );
                  }).toList(),
                ),
                const SizedBox(height: 14),

                // Title
                const Text('Complaint Title', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13, color: Color(0xFF0B194C))),
                const SizedBox(height: 6),
                TextField(
                  controller: _titleController,
                  decoration: InputDecoration(
                    hintText: 'e.g. Street lamp not working near Plot 42',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE8DFD0))),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                  ),
                ),
                const SizedBox(height: 14),

                // Description
                const Text('Detailed Description', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13, color: Color(0xFF0B194C))),
                const SizedBox(height: 6),
                TextField(
                  controller: _descController,
                  maxLines: 3,
                  decoration: InputDecoration(
                    hintText: 'Please provide exact location, landmark, and problem details...',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE8DFD0))),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                  ),
                ),
                const SizedBox(height: 20),

                // Submit Button
                SizedBox(
                  width: double.infinity,
                  height: 48,
                  child: ElevatedButton(
                    onPressed: _isSubmitting ? null : () => _submitGrievance(ctx, setModalState),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF0B194C),
                      foregroundColor: const Color(0xFFD4AF37),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      elevation: 0,
                    ),
                    child: _isSubmitting
                        ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFFD4AF37)))
                        : const Text('Submit Grievance', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 15)),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Future<void> _submitGrievance(BuildContext modalCtx, StateSetter setModalState) async {
    final title = _titleController.text.trim();
    final desc = _descController.text.trim();
    if (title.isEmpty || desc.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please fill in both title and description')),
      );
      return;
    }

    setModalState(() => _isSubmitting = true);
    final api = ApiService();
    try {
      final res = await api.post('/api/v2/mobile/township-grievances', data: {
        'category': _selectedCategory,
        'priority': _selectedPriority,
        'title': title,
        'description': desc,
      });

      if (mounted) {
        Navigator.pop(modalCtx);
        _titleController.clear();
        _descController.clear();
        ref.invalidate(townshipGrievancesProvider);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text((res['message'] as String?) ?? 'Grievance submitted successfully! Token: ${res['ticket_token']}'),
            backgroundColor: const Color(0xFF0B194C),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Submission failed: $e')));
      }
    } finally {
      setModalState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final grievancesAsync = ref.watch(townshipGrievancesProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F0),
      appBar: AppBar(
        title: const Text('Township Resident Helpdesk', style: TextStyle(fontWeight: FontWeight.w700, color: Color(0xFF0B194C))),
        backgroundColor: Colors.white,
        elevation: 0.5,
        iconTheme: const IconThemeData(color: Color(0xFF0B194C)),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.invalidate(townshipGrievancesProvider),
            tooltip: 'Refresh',
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _showLodgeDialog,
        backgroundColor: const Color(0xFF0B194C),
        foregroundColor: const Color(0xFFD4AF37),
        icon: const Icon(Icons.add_circle_outline),
        label: const Text('Lodge Complaint', style: TextStyle(fontWeight: FontWeight.w700)),
      ),
      body: grievancesAsync.when(
        data: (list) => _buildList(context, list),
        loading: () => const Center(child: CircularProgressIndicator(color: AppTheme.primaryColor)),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }

  Widget _buildList(BuildContext context, List<dynamic> list) {
    if (list.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.thumb_up_alt_outlined, size: 64, color: Colors.green[400]),
              const SizedBox(height: 16),
              const Text(
                'No Complaints Lodged',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700, color: Color(0xFF0B194C)),
              ),
              const SizedBox(height: 8),
              const Text(
                'Everything in your township is running smoothly! If you ever face any issues with water, electricity, security, or roads, tap below to lodge a complaint.',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 13, color: Colors.grey),
              ),
              const SizedBox(height: 24),
              ElevatedButton.icon(
                onPressed: _showLodgeDialog,
                icon: const Icon(Icons.add),
                label: const Text('Lodge First Grievance'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF0B194C),
                  foregroundColor: const Color(0xFFD4AF37),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
              ),
            ],
          ),
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: list.length,
      itemBuilder: (context, index) {
        final g = list[index] as Map<String, dynamic>;
        final token = g['ticket_token']?.toString() ?? '#$index';
        final title = g['title']?.toString() ?? 'Grievance';
        final desc = g['description']?.toString() ?? '';
        final status = (g['status'] ?? 'open').toString().toLowerCase();
        final category = g['category']?.toString() ?? 'other';
        final catLabel = _categories[category] ?? category;

        Color statusColor = const Color(0xFFD97706);
        if (status == 'resolved' || status == 'closed') {
          statusColor = const Color(0xFF10B981);
        } else if (status == 'in_progress') {
          statusColor = const Color(0xFF0284C7);
        }

        return Container(
          margin: const EdgeInsets.only(bottom: 12),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFE8DFD0)),
            boxShadow: [
              BoxShadow(color: Colors.black.withValues(alpha: 0.03), blurRadius: 10, offset: const Offset(0, 4)),
            ],
          ),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      token,
                      style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: Colors.grey),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                      decoration: BoxDecoration(
                        color: statusColor.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: statusColor),
                      ),
                      child: Text(
                        status.toUpperCase().replaceAll('_', ' '),
                        style: TextStyle(fontSize: 10, fontWeight: FontWeight.w800, color: statusColor),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Text(
                  title,
                  style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: Color(0xFF0B194C)),
                ),
                const SizedBox(height: 4),
                Text(
                  desc,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(fontSize: 13, color: Color(0xFF4B5563)),
                ),
                const SizedBox(height: 12),
                Divider(color: Colors.grey.withValues(alpha: 0.15), height: 1),
                const SizedBox(height: 8),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      catLabel,
                      style: const TextStyle(fontSize: 11, color: Colors.grey, fontWeight: FontWeight.w500),
                    ),
                    const Row(
                      children: [
                        Icon(Icons.timer_outlined, size: 14, color: Colors.grey),
                        SizedBox(width: 4),
                        Text('SLA: 48h', style: TextStyle(fontSize: 11, color: Colors.grey)),
                      ],
                    ),
                  ],
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}

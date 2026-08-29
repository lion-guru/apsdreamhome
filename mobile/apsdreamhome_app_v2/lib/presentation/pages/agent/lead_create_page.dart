import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/utils/logger.dart';
import '../../../data/services/crm_service.dart';
import '../../widgets/app_widgets.dart';

class LeadCreatePage extends ConsumerStatefulWidget {
  const LeadCreatePage({super.key});

  @override
  ConsumerState<LeadCreatePage> createState() => _LeadCreatePageState();
}

class _LeadCreatePageState extends ConsumerState<LeadCreatePage> {
  final _formKey = GlobalKey<FormState>();
  final _nameCtrl = TextEditingController();
  final _phoneCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _notesCtrl = TextEditingController();

  String? _selectedSource;
  String? _selectedProperty;
  String? _selectedBudget;
  String _selectedPriority = 'medium';
  bool _saving = false;

  static const _sources = [
    'Walk-in',
    'Phone Call',
    'WhatsApp',
    'Website',
    'Facebook',
    'Instagram',
    'Google Ads',
    'Referral',
    'JustDial',
    '99acres',
    'MagicBricks',
    'IndiaMART',
    'Other',
  ];

  static const _properties = [
    'Plot',
    'House',
    'Flat',
    'Shop',
    'Farmhouse',
    'Commercial Land',
  ];

  static const _budgets = [
    'Under 10L',
    '10-25L',
    '25-50L',
    '50L-1Cr',
    '1-2Cr',
    '2-5Cr',
    '5Cr+',
  ];

  @override
  void dispose() {
    _nameCtrl.dispose();
    _phoneCtrl.dispose();
    _emailCtrl.dispose();
    _notesCtrl.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);

    try {
      final data = <String, dynamic>{
        'name': _nameCtrl.text.trim(),
        'phone': _phoneCtrl.text.trim(),
      };
      if (_emailCtrl.text.trim().isNotEmpty) {
        data['email'] = _emailCtrl.text.trim();
      }
      if (_selectedSource != null) data['source'] = _selectedSource;
      if (_selectedProperty != null) {
        data['property_interest'] = _selectedProperty;
      }
      if (_selectedBudget != null) data['budget_range'] = _selectedBudget;
      data['priority'] = _selectedPriority;
      if (_notesCtrl.text.trim().isNotEmpty) {
        data['notes'] = _notesCtrl.text.trim();
      }

      final crm = CRMService();
      final lead = await crm.createLead(data);

      if (mounted) {
        if (lead != null) {
          AppWidgets.showSuccessSnackBar(
            context,
            'Lead created! #${lead.leadNumber ?? lead.id}',
          );
          // Reset form
          _nameCtrl.clear();
          _phoneCtrl.clear();
          _emailCtrl.clear();
          _notesCtrl.clear();
          setState(() {
            _selectedSource = null;
            _selectedProperty = null;
            _selectedBudget = null;
            _selectedPriority = 'medium';
          });
          // Go back to previous page
          Future.delayed(const Duration(seconds: 1), () {
            if (mounted) context.pop();
          });
        } else {
          AppWidgets.showErrorSnackBar(
            context,
            'Failed to create lead. Please try again.',
          );
        }
      }
    } catch (e) {
      AppLogger.error('Lead creation error', e);
      if (mounted) AppWidgets.showErrorSnackBar(context, 'Error: $e');
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Create Lead'),
        leading: IconButton(
          onPressed: () => context.pop(),
          icon: const Icon(Icons.arrow_back),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Header info
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppTheme.primaryColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Row(
                  children: [
                    Icon(Icons.person_add, color: AppTheme.primaryColor),
                    SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        'Add a new lead to track and follow up. Fill in what you know — phone and name are required.',
                        style: TextStyle(
                          color: AppTheme.primaryColor,
                          fontSize: 13,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),

              // Name
              TextFormField(
                controller: _nameCtrl,
                textCapitalization: TextCapitalization.words,
                decoration: InputDecoration(
                  labelText: 'Lead Name *',
                  hintText: 'Enter full name',
                  prefixIcon: const Icon(Icons.person_outline),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                validator: (v) =>
                    (v == null || v.trim().isEmpty) ? 'Name is required' : null,
              ),
              const SizedBox(height: 16),

              // Phone
              TextFormField(
                controller: _phoneCtrl,
                keyboardType: TextInputType.phone,
                maxLength: 10,
                decoration: InputDecoration(
                  labelText: 'Phone Number *',
                  hintText: '10-digit mobile number',
                  prefixIcon: const Icon(Icons.phone_outlined),
                  prefixText: '+91 ',
                  counterText: '',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                validator: (v) {
                  if (v == null || v.trim().isEmpty) return 'Phone is required';
                  if (v.trim().length != 10) return 'Enter 10-digit number';
                  return null;
                },
              ),
              const SizedBox(height: 16),

              // Email
              TextFormField(
                controller: _emailCtrl,
                keyboardType: TextInputType.emailAddress,
                decoration: InputDecoration(
                  labelText: 'Email (Optional)',
                  hintText: 'email@example.com',
                  prefixIcon: const Icon(Icons.email_outlined),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
              ),
              const SizedBox(height: 16),

              // Source dropdown
              DropdownButtonFormField<String>(
                initialValue: _selectedSource,
                decoration: InputDecoration(
                  labelText: 'Lead Source',
                  prefixIcon: const Icon(Icons.source_outlined),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                items: _sources
                    .map((s) => DropdownMenuItem(value: s, child: Text(s)))
                    .toList(),
                onChanged: (v) => setState(() => _selectedSource = v),
              ),
              const SizedBox(height: 16),

              // Property interest
              DropdownButtonFormField<String>(
                initialValue: _selectedProperty,
                decoration: InputDecoration(
                  labelText: 'Interested In',
                  prefixIcon: const Icon(Icons.home_outlined),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                items: _properties
                    .map((p) => DropdownMenuItem(value: p, child: Text(p)))
                    .toList(),
                onChanged: (v) => setState(() => _selectedProperty = v),
              ),
              const SizedBox(height: 16),

              // Budget
              DropdownButtonFormField<String>(
                initialValue: _selectedBudget,
                decoration: InputDecoration(
                  labelText: 'Budget Range',
                  prefixIcon: const Icon(Icons.currency_rupee_outlined),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                items: _budgets
                    .map((b) => DropdownMenuItem(value: b, child: Text(b)))
                    .toList(),
                onChanged: (v) => setState(() => _selectedBudget = v),
              ),
              const SizedBox(height: 16),

              // Priority
              const Text(
                'Priority',
                style: TextStyle(fontSize: 14, fontWeight: FontWeight.w500),
              ),
              const SizedBox(height: 8),
              SegmentedButton<String>(
                segments: const [
                  ButtonSegment(
                    value: 'low',
                    label: Text('Low'),
                    icon: Icon(Icons.arrow_downward, size: 16),
                  ),
                  ButtonSegment(
                    value: 'medium',
                    label: Text('Medium'),
                    icon: Icon(Icons.remove, size: 16),
                  ),
                  ButtonSegment(
                    value: 'high',
                    label: Text('High'),
                    icon: Icon(Icons.arrow_upward, size: 16),
                  ),
                ],
                selected: {_selectedPriority},
                onSelectionChanged: (v) =>
                    setState(() => _selectedPriority = v.first),
              ),
              const SizedBox(height: 16),

              // Notes
              TextFormField(
                controller: _notesCtrl,
                maxLines: 3,
                decoration: InputDecoration(
                  labelText: 'Notes (Optional)',
                  hintText: 'Any additional details about this lead...',
                  alignLabelWithHint: true,
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
              ),
              const SizedBox(height: 32),

              // Submit button
              SizedBox(
                height: 52,
                child: ElevatedButton(
                  onPressed: _saving ? null : _submit,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTheme.primaryColor,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  child: _saving
                      ? const SizedBox(
                          width: 24,
                          height: 24,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.white,
                          ),
                        )
                      : const Text(
                          'Create Lead',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

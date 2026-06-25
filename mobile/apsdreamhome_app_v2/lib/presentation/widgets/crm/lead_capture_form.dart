import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:share_plus/share_plus.dart';

import '../../../core/theme/app_theme.dart';
import '../../../data/models/crm_models.dart';
import '../../../data/services/crm_service.dart';

/// Shows a modal bottom sheet for quick lead capture.
/// Returns the created [CRMLead] on success, or null if dismissed/failed.
Future<CRMLead?> showLeadCaptureSheet(
  BuildContext context,
  WidgetRef ref, {
  String? prefillPhone,
  String? prefillName,
  String? source,
}) {
  return showModalBottomSheet<CRMLead>(
    context: context,
    isScrollControlled: true,
    useSafeArea: true,
    shape: const RoundedRectangleBorder(
      borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
    ),
    builder: (_) => LeadCaptureForm(
      prefillPhone: prefillPhone,
      prefillName: prefillName,
      source: source,
    ),
  );
}

/// Shows a share sheet for a lead referral link.
void showShareLeadSheet({
  required BuildContext context,
  String? phone,
  String? name,
  String? source,
}) {
  final nameText = name ?? '';
  final phoneText = phone ?? '';
  final sourceText = source ?? 'referral';
  final link = Uri.encodeFull(
    'https://apsdreamhome.com/leads/capture'
    '?name=$nameText&phone=$phoneText&source=$sourceText',
  );

  showModalBottomSheet(
    context: context,
    shape: const RoundedRectangleBorder(
      borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
    ),
    builder: (ctx) => SafeArea(
      child: Padding(
        padding: const EdgeInsets.fromLTRB(20, 12, 20, 16),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Center(
              child: Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),
            const SizedBox(height: 16),
            const Text(
              'Share Lead',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
            ),
            const SizedBox(height: 4),
            Text(
              'Share this lead capture link',
              style: TextStyle(fontSize: 13, color: Colors.grey.shade600),
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.grey.shade100,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                children: [
                  const Icon(Icons.link, size: 18, color: AppTheme.primaryColor),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      link,
                      style: const TextStyle(fontSize: 12),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: _ShareButton(
                    icon: Icons.chat,
                    label: 'WhatsApp',
                    color: const Color(0xFF25D366),
                    onTap: () {
                      final msg = _buildShareMessage(nameText, phoneText, sourceText, link);
                      Share.share(msg, sharePositionOrigin: null);
                      Navigator.pop(ctx);
                    },
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _ShareButton(
                    icon: Icons.sms,
                    label: 'SMS',
                    color: Colors.blue,
                    onTap: () {
                      final msg = _buildShareMessage(nameText, phoneText, sourceText, link);
                      Share.share(msg, sharePositionOrigin: null);
                      Navigator.pop(ctx);
                    },
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _ShareButton(
                    icon: Icons.share,
                    label: 'Share',
                    color: AppTheme.primaryColor,
                    onTap: () {
                      final msg = _buildShareMessage(nameText, phoneText, sourceText, link);
                      Share.share(msg, sharePositionOrigin: null);
                      Navigator.pop(ctx);
                    },
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    ),
  );
}

String _buildShareMessage(String name, String phone, String source, String link) {
  final parts = <String>[
    'New Lead Capture',
    if (name.isNotEmpty) 'Name: $name',
    if (phone.isNotEmpty) 'Phone: $phone',
    'Source: $source',
    '',
    'Capture this lead:',
    link,
  ];
  return parts.join('\n');
}

class _ShareButton extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;

  const _ShareButton({
    required this.icon,
    required this.label,
    required this.color,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(10),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 12),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: color.withValues(alpha: 0.3)),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, color: color, size: 22),
            const SizedBox(height: 4),
            Text(label, style: TextStyle(fontSize: 12, color: color, fontWeight: FontWeight.w500)),
          ],
        ),
      ),
    );
  }
}

// ─── Inline / Bottom-sheet form widget ──────────────────────────────

class LeadCaptureForm extends ConsumerStatefulWidget {
  final String? prefillPhone;
  final String? prefillName;
  final String? source;

  const LeadCaptureForm({
    super.key,
    this.prefillPhone,
    this.prefillName,
    this.source,
  });

  @override
  ConsumerState<LeadCaptureForm> createState() => _LeadCaptureFormState();
}

class _LeadCaptureFormState extends ConsumerState<LeadCaptureForm> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _nameCtrl;
  late final TextEditingController _phoneCtrl;
  late final TextEditingController _emailCtrl;
  late final TextEditingController _notesCtrl;

  String? _propertyInterest;
  String? _budgetRange;
  String? _source;
  String _priority = 'medium';
  bool _saving = false;

  static const _propertyOptions = ['Plot', 'House', 'Flat', 'Shop', 'Farmhouse'];
  static const _budgetOptions = ['Under 10L', '10-25L', '25-50L', '50L-1Cr', '1Cr+'];
  static const _sourceOptions = [
    'Website', 'WhatsApp', 'Walk-in', 'Facebook',
    'Instagram', 'Google Ads', 'Referral', 'Other',
  ];

  @override
  void initState() {
    super.initState();
    _nameCtrl = TextEditingController(text: widget.prefillName ?? '');
    _phoneCtrl = TextEditingController(text: widget.prefillPhone ?? '');
    _emailCtrl = TextEditingController();
    _notesCtrl = TextEditingController();
    _source = widget.source;
  }

  @override
  void dispose() {
    _nameCtrl.dispose();
    _phoneCtrl.dispose();
    _emailCtrl.dispose();
    _notesCtrl.dispose();
    super.dispose();
  }

  InputDecoration _decoration(String label, {IconData? icon, String? hint}) {
    return InputDecoration(
      labelText: label,
      hintText: hint,
      prefixIcon: icon != null ? Icon(icon, size: 20) : null,
      border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(10),
        borderSide: const BorderSide(color: AppTheme.primaryColor, width: 2),
      ),
      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      isDense: true,
      filled: true,
      fillColor: Theme.of(context).colorScheme.surface,
    );
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _saving = true);

    final data = <String, dynamic>{
      'name': _nameCtrl.text.trim(),
      'phone': _phoneCtrl.text.trim(),
    };
    if (_emailCtrl.text.trim().isNotEmpty) data['email'] = _emailCtrl.text.trim();
    if (_propertyInterest != null) data['property_interest'] = _propertyInterest;
    if (_budgetRange != null) data['budget_range'] = _budgetRange;
    if (_source != null) data['source'] = _source;
    data['priority'] = _priority;
    if (_notesCtrl.text.trim().isNotEmpty) data['notes'] = _notesCtrl.text.trim();

    try {
      final crm = ref.read(crmServiceProvider);
      final lead = await crm.createLead(data);

      if (!mounted) return;

      if (lead != null) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Lead "${lead.name}" created successfully'),
            backgroundColor: AppTheme.successColor,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
          ),
        );
        Navigator.of(context).pop(lead);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: const Text('Failed to create lead. Please try again.'),
            backgroundColor: AppTheme.errorColor,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
          ),
        );
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Error: $e'),
          backgroundColor: AppTheme.errorColor,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
        ),
      );
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final bottomPadding = MediaQuery.of(context).viewInsets.bottom;

    return Padding(
      padding: EdgeInsets.fromLTRB(20, 12, 20, bottomPadding + 16),
      child: Form(
        key: _formKey,
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // ── Handle + close ────────────────────────────────
              Row(
                children: [
                  const Expanded(
                    child: Text(
                      'Capture Lead',
                      style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700),
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close),
                    onPressed: () => Navigator.of(context).pop(),
                    visualDensity: VisualDensity.compact,
                  ),
                ],
              ),
              const SizedBox(height: 4),

              // ── Name ──────────────────────────────────────────
              TextFormField(
                controller: _nameCtrl,
                textCapitalization: TextCapitalization.words,
                decoration: _decoration('Name *', icon: Icons.person_outline),
                validator: (v) {
                  if (v == null || v.trim().isEmpty) return 'Name is required';
                  return null;
                },
              ),
              const SizedBox(height: 12),

              // ── Phone ─────────────────────────────────────────
              TextFormField(
                controller: _phoneCtrl,
                keyboardType: TextInputType.phone,
                maxLength: 10,
                decoration: _decoration('Phone *', icon: Icons.phone_outlined, hint: '10-digit mobile number'),
                validator: (v) {
                  if (v == null || v.trim().isEmpty) return 'Phone is required';
                  final digits = v.replaceAll(RegExp(r'\D'), '');
                  if (digits.length != 10) return 'Enter a valid 10-digit number';
                  return null;
                },
              ),
              const SizedBox(height: 12),

              // ── Email ─────────────────────────────────────────
              TextFormField(
                controller: _emailCtrl,
                keyboardType: TextInputType.emailAddress,
                decoration: _decoration('Email (optional)', icon: Icons.email_outlined),
              ),
              const SizedBox(height: 12),

              // ── Row: Property Interest + Budget ───────────────
              Row(
                children: [
                  Expanded(
                    child: DropdownButtonFormField<String>(
                      initialValue: _propertyInterest,
                      isExpanded: true,
                      decoration: _decoration('Property Interest'),
                      items: _propertyOptions
                          .map((o) => DropdownMenuItem(value: o, child: Text(o, style: const TextStyle(fontSize: 14))))
                          .toList(),
                      onChanged: (v) => setState(() => _propertyInterest = v),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: DropdownButtonFormField<String>(
                      initialValue: _budgetRange,
                      isExpanded: true,
                      decoration: _decoration('Budget Range'),
                      items: _budgetOptions
                          .map((o) => DropdownMenuItem(value: o, child: Text(o, style: const TextStyle(fontSize: 14))))
                          .toList(),
                      onChanged: (v) => setState(() => _budgetRange = v),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),

              // ── Source ────────────────────────────────────────
              DropdownButtonFormField<String>(
                initialValue: _source,
                isExpanded: true,
                decoration: _decoration('Source'),
                items: _sourceOptions
                    .map((o) => DropdownMenuItem(value: o, child: Text(o, style: const TextStyle(fontSize: 14))))
                    .toList(),
                onChanged: (v) => setState(() => _source = v),
              ),
              const SizedBox(height: 12),

              // ── Priority ──────────────────────────────────────
              const Align(
                alignment: Alignment.centerLeft,
                child: Text('Priority', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w500)),
              ),
              const SizedBox(height: 6),
              SegmentedButton<String>(
                segments: const [
                  ButtonSegment(value: 'low', label: Text('Low'), icon: Icon(Icons.arrow_downward, size: 16)),
                  ButtonSegment(value: 'medium', label: Text('Med'), icon: Icon(Icons.remove, size: 16)),
                  ButtonSegment(value: 'high', label: Text('High'), icon: Icon(Icons.arrow_upward, size: 16)),
                ],
                selected: {_priority},
                onSelectionChanged: (v) => setState(() => _priority = v.first),
                style: SegmentedButton.styleFrom(
                  selectedBackgroundColor: _priorityColor.withValues(alpha: 0.15),
                  selectedForegroundColor: _priorityColor,
                  visualDensity: VisualDensity.compact,
                  textStyle: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500),
                ),
              ),
              const SizedBox(height: 12),

              // ── Notes ─────────────────────────────────────────
              TextFormField(
                controller: _notesCtrl,
                maxLines: 3,
                decoration: _decoration('Notes', icon: Icons.notes_outlined),
              ),
              const SizedBox(height: 16),

              // ── Save button ───────────────────────────────────
              SizedBox(
                height: 48,
                child: ElevatedButton(
                  onPressed: _saving ? null : _submit,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTheme.primaryColor,
                    foregroundColor: Colors.white,
                    disabledBackgroundColor: AppTheme.primaryColor.withValues(alpha: 0.5),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    elevation: 0,
                  ),
                  child: _saving
                      ? const SizedBox(
                          width: 22,
                          height: 22,
                          child: CircularProgressIndicator(
                            strokeWidth: 2.5,
                            color: Colors.white,
                          ),
                        )
                      : const Text(
                          'Save Lead',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
                        ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Color get _priorityColor {
    switch (_priority) {
      case 'high':
        return AppTheme.errorColor;
      case 'medium':
        return AppTheme.warningColor;
      case 'low':
        return AppTheme.successColor;
      default:
        return AppTheme.primaryColor;
    }
  }
}

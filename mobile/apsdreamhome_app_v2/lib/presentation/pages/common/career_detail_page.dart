import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/app_theme.dart';
import '../../../data/repositories/kyc_repository_provider.dart';

class CareerDetailPage extends ConsumerStatefulWidget {
  final String jobId;
  const CareerDetailPage({super.key, required this.jobId});

  @override
  ConsumerState<CareerDetailPage> createState() => _CareerDetailPageState();
}

class _CareerDetailPageState extends ConsumerState<CareerDetailPage> {
  bool _isLoading = true;
  Map<String, dynamic>? _job;
  bool _showForm = false;
  bool _isSubmitting = false;
  bool _submitted = false;

  final _nameCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _phoneCtrl = TextEditingController();
  final _coverCtrl = TextEditingController();
  final _expCtrl = TextEditingController();
  final _companyCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadJob();
  }

  @override
  void dispose() {
    _nameCtrl.dispose();
    _emailCtrl.dispose();
    _phoneCtrl.dispose();
    _coverCtrl.dispose();
    _expCtrl.dispose();
    _companyCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadJob() async {
    setState(() => _isLoading = true);
    try {
      final api = ref.read(apiServiceProvider);
      final response = await api.get('careers/${widget.jobId}');
      final data = response['data'];
      if (mounted && data is Map<String, dynamic>) {
        setState(() {
          _job = data;
          _isLoading = false;
        });
        _expCtrl.text = data['experience_required']?.toString() ?? '';
      }
    } catch (_) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _submitApplication() async {
    final name = _nameCtrl.text.trim();
    final email = _emailCtrl.text.trim();
    final phone = _phoneCtrl.text.trim();
    if (name.isEmpty || email.isEmpty || phone.isEmpty) {
      _showSnackBar('Name, email and phone are required');
      return;
    }
    setState(() => _isSubmitting = true);
    try {
      final api = ref.read(apiServiceProvider);
      await api.post(
        'careers/apply',
        data: {
          'job_id': widget.jobId,
          'name': name,
          'email': email,
          'phone': phone,
          'cover_letter': _coverCtrl.text.trim(),
          'experience': int.tryParse(_expCtrl.text) ?? 0,
          'current_company': _companyCtrl.text.trim(),
        },
      );
      if (mounted) {
        setState(() {
          _submitted = true;
          _isSubmitting = false;
          _showForm = false;
        });
        _showSnackBar('Application submitted successfully!');
      }
    } catch (_) {
      if (mounted) {
        setState(() => _isSubmitting = false);
        _showSnackBar('Failed to submit application');
      }
    }
  }

  void _showSnackBar(String msg) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(msg),
        backgroundColor: AppTheme.primaryColor,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
        margin: const EdgeInsets.all(16),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(_job?['title']?.toString() ?? 'Job Details'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(color: AppTheme.primaryColor),
            )
          : _job == null
          ? const Center(child: Text('Job not found'))
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildJobHeader(),
                  const SizedBox(height: 20),
                  _buildSection(
                    'Description',
                    _job!['description']?.toString() ??
                        'No description available',
                  ),
                  const SizedBox(height: 16),
                  _buildSection(
                    'Requirements',
                    _job!['requirements']?.toString() ?? '',
                  ),
                  if (_submitted)
                    _buildSuccessCard()
                  else if (_showForm)
                    _buildApplicationForm()
                  else
                    Padding(
                      padding: const EdgeInsets.symmetric(vertical: 20),
                      child: SizedBox(
                        width: double.infinity,
                        child: ElevatedButton.icon(
                          onPressed: () => setState(() => _showForm = true),
                          icon: const Icon(Icons.send_outlined),
                          label: const Text('Apply Now'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppTheme.primaryColor,
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                        ),
                      ),
                    ),
                ],
              ),
            ),
    );
  }

  Widget _buildJobHeader() {
    final job = _job!;
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: AppTheme.primaryColor.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: const Icon(
                    Icons.work_outline,
                    color: AppTheme.primaryColor,
                    size: 32,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        job['title']?.toString() ?? '',
                        style: const TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        job['department']?.toString() ?? '',
                        style: TextStyle(
                          fontSize: 14,
                          color: Colors.grey.shade600,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const Divider(height: 24),
            Wrap(
              spacing: 16,
              runSpacing: 10,
              children: [
                if (job['location']?.toString().isNotEmpty == true)
                  _detailChip(
                    Icons.location_on_outlined,
                    job['location'].toString(),
                  ),
                if (job['employment_type']?.toString().isNotEmpty == true)
                  _detailChip(
                    Icons.badge_outlined,
                    job['employment_type'].toString(),
                  ),
                if (job['experience_required']?.toString().isNotEmpty == true)
                  _detailChip(
                    Icons.timeline_outlined,
                    job['experience_required'].toString(),
                  ),
                if (job['salary_range']?.toString().isNotEmpty == true)
                  _detailChip(
                    Icons.currency_rupee,
                    job['salary_range'].toString(),
                  ),
                if (job['vacancies']?.toString().isNotEmpty == true)
                  _detailChip(
                    Icons.people_outline,
                    '${job['vacancies']} Vacancies',
                  ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _detailChip(IconData icon, String text) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 16, color: AppTheme.primaryColor),
        const SizedBox(width: 6),
        Text(text, style: TextStyle(fontSize: 13, color: Colors.grey.shade700)),
      ],
    );
  }

  Widget _buildSection(String title, String content) {
    if (content.isEmpty) return const SizedBox.shrink();
    return Card(
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: BorderSide(color: Colors.grey.shade200),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              title,
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 12),
            Text(
              content,
              style: TextStyle(
                fontSize: 14,
                color: Colors.grey.shade700,
                height: 1.6,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildApplicationForm() {
    return Card(
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: BorderSide(color: Colors.grey.shade200),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(Icons.person_outline, color: AppTheme.primaryColor),
                SizedBox(width: 8),
                Text(
                  'Apply for this Position',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              ],
            ),
            const Divider(height: 20),
            TextField(
              controller: _nameCtrl,
              decoration: _input('Full Name *', Icons.person_outline),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _emailCtrl,
              decoration: _input('Email *', Icons.email_outlined),
              keyboardType: TextInputType.emailAddress,
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _phoneCtrl,
              decoration: _input('Phone *', Icons.phone_outlined),
              keyboardType: TextInputType.phone,
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _companyCtrl,
              decoration: _input('Current Company', Icons.business_outlined),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _expCtrl,
              decoration: _input(
                'Years of Experience',
                Icons.timeline_outlined,
              ),
              keyboardType: TextInputType.number,
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _coverCtrl,
              decoration: _input(
                'Cover Letter (optional)',
                Icons.description_outlined,
              ),
              maxLines: 4,
            ),
            const SizedBox(height: 20),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => setState(() => _showForm = false),
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                    child: const Text('Cancel'),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  flex: 2,
                  child: ElevatedButton(
                    onPressed: _isSubmitting ? null : _submitApplication,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppTheme.primaryColor,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                    child: _isSubmitting
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              color: Colors.white,
                            ),
                          )
                        : const Text(
                            'Submit Application',
                            style: TextStyle(fontWeight: FontWeight.w600),
                          ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSuccessCard() {
    return Card(
      elevation: 0,
      color: AppTheme.successColor.withValues(alpha: 0.1),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: BorderSide(color: AppTheme.successColor.withValues(alpha: 0.3)),
      ),
      child: const Padding(
        padding: EdgeInsets.all(24),
        child: Column(
          children: [
            Icon(
              Icons.check_circle_outline,
              size: 48,
              color: AppTheme.successColor,
            ),
            SizedBox(height: 12),
            Text(
              'Application Submitted!',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: AppTheme.successColor,
              ),
            ),
            SizedBox(height: 8),
            Text(
              'We will review your application and get back to you.',
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.grey, fontSize: 14),
            ),
          ],
        ),
      ),
    );
  }

  InputDecoration _input(String label, IconData icon) {
    return InputDecoration(
      labelText: label,
      prefixIcon: Icon(icon, size: 20, color: AppTheme.primaryColor),
      border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: AppTheme.primaryColor, width: 2),
      ),
      filled: true,
      fillColor: Colors.grey.shade50,
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
    );
  }
}

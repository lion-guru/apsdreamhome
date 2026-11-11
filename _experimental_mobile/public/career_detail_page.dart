import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/services/api_service.dart';

class CareerDetailPage extends ConsumerStatefulWidget {
  final String id;
  const CareerDetailPage({super.key, required this.id});

  @override
  ConsumerState<CareerDetailPage> createState() => _CareerDetailPageState();
}

class _CareerDetailPageState extends ConsumerState<CareerDetailPage> {
  Map<String, dynamic>? _job;
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadJob();
  }

  Future<void> _loadJob() async {
    try {
      final api = ApiService();
      final response = await api.get('/careers/jobs/${widget.id}');
      if (mounted) {
        setState(() {
          if (response['success'] == true && response['data'] != null) {
            _job = Map<String, dynamic>.from(response['data'] as Map<dynamic, dynamic>);
          } else {
            _error = 'Job not found';
          }
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = e.toString();
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios, color: Colors.white),
          onPressed: () => context.pop(),
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.error_outline, size: 64, color: Colors.red),
                      const SizedBox(height: 16),
                      Text(_error!),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: () => context.pop(),
                        child: const Text('Go Back'),
                      ),
                    ],
                  ),
                )
              : _job == null
                  ? const Center(child: Text('Job not found'))
                  : CustomScrollView(
                      slivers: [
                        SliverToBoxAdapter(
                          child: _buildJobContent(),
                        ),
                      ],
                    ),
    );
  }

  Widget _buildJobContent() {
    final title = (_job!['title'] ?? 'Untitled Position').toString();
    final department = (_job!['department'] ?? _job!['category'] ?? '').toString();
    final location = (_job!['location'] ?? '').toString();
    final type = (_job!['employment_type'] ?? _job!['type'] ?? 'Full-time').toString();
    final experience = (_job!['experience'] ?? _job!['experience_required'] ?? '').toString();
    final salary = (_job!['salary'] ?? _job!['salary_range'] ?? '').toString();
    final description = (_job!['description'] ?? _job!['content'] ?? '').toString();
    final requirements = (_job!['requirements'] ?? []) as List;
    final responsibilities = (_job!['responsibilities'] ?? []) as List;
    final benefits = (_job!['benefits'] ?? []) as List;
    final postedDate = _job!['created_at'] ?? _job!['posted_date'];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Hero Header
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(32),
          decoration: BoxDecoration(
            gradient: LinearGradient(
              colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              if (department.isNotEmpty)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.2),
                    borderRadius: BorderRadius.circular(24),
                  ),
                  child: Text(
                    department,
                    style: const TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.w600,
                      fontSize: 14,
                    ),
                  ),
                const SizedBox(height: 16),
              Text(
                title,
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 32,
                  fontWeight: FontWeight.w800,
                  height: 1.2,
                ),
              ),
              const SizedBox(height: 16),
              Wrap(
                spacing: 12,
                runSpacing: 8,
                children: [
                  if (location.isNotEmpty)
                    _buildDetailChip(Icons.location_on, location, Colors.white),
                  _buildDetailChip(Icons.work_outline, type, Colors.white),
                  if (experience.isNotEmpty)
                    _buildDetailChip(Icons.timeline, experience, Colors.white),
                  if (salary.isNotEmpty)
                    _buildDetailChip(Icons.attach_money, salary, Colors.white),
                ],
              ),
              if (postedDate != null) ...[
                const SizedBox(height: 12),
                Text(
                  'Posted ${_formatDate(postedDate)}',
                  style: TextStyle(color: Colors.white.withOpacity(0.8), fontSize: 14),
                ),
              ],
            ],
          ),
        ),

        // Content
        Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Quick Actions
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: () => _shareJob(),
                      icon: const Icon(Icons.share, size: 18),
                      label: const Text('Share'),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppTheme.primaryColor,
                        side: BorderSide(color: AppTheme.primaryColor),
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: () => context.go('/careers/apply/${widget.id}'),
                      icon: const Icon(Icons.send, size: 18),
                      label: const Text('Apply Now'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTheme.primaryColor,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 24),

              // About the Role
              _buildSectionHeader('About the Role'),
              const SizedBox(height: 12),
              Text(
                _stripHtml(description),
                style: const TextStyle(fontSize: 16, height: 1.8, color: Color(0xFF212121)),
              ),
              const SizedBox(height: 24),

              // Requirements
              if (requirements is List && requirements.isNotEmpty) ...[
                _buildSectionHeader('Requirements'),
                const SizedBox(height: 12),
                _buildBulletList(requirements as List),
                const SizedBox(height: 24),
              ],

              // Responsibilities
              if (responsibilities is List && responsibilities.isNotEmpty) ...[
                _buildSectionHeader('Responsibilities'),
                const SizedBox(height: 12),
                _buildBulletList(responsibilities as List),
                const SizedBox(height: 24),
              ],

              // Benefits
              if (benefits is List && benefits.isNotEmpty) ...[
                _buildSectionHeader('Benefits & Perks'),
                const SizedBox(height: 12),
                Wrap(
                  spacing: 12,
                  runSpacing: 12,
                  children: (benefits as List).map((benefit) {
                    return Container(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                      decoration: BoxDecoration(
                        color: Colors.green.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: Colors.green.withOpacity(0.3)),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.check_circle, color: Colors.green, size: 18),
                          const SizedBox(width: 8),
                          Text(
                            benefit.toString(),
                            style: const TextStyle(
                              color: Colors.green,
                              fontWeight: FontWeight.w600,
                              fontSize: 14,
                            ),
                          ),
                        ],
                      ),
                    ),
                  }).toList(),
                ),
                const SizedBox(height: 24),
              ],

              // Apply CTA
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
                  ),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Column(
                  children: [
                    Text(
                      'Ready to Join Our Team?',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 22,
                        fontWeight: FontWeight.w800,
                      ),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'Apply now and start your journey with APS Dream Home',
                      style: TextStyle(
                        color: Colors.white.withOpacity(0.9),
                        fontSize: 15,
                      ),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 16),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton.icon(
                        onPressed: () => context.go('/careers/apply/${widget.id}'),
                        icon: const Icon(Icons.send, size: 20),
                        label: const Text(
                          'Apply Now',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
                        ),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.white,
                          foregroundColor: AppTheme.primaryColor,
                          padding: const EdgeInsets.symmetric(vertical: 16),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 32),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildSectionHeader(String title) {
    return Row(
      children: [
        Container(
          width: 4,
          height: 24,
          decoration: BoxDecoration(
            color: AppTheme.primaryColor,
            borderRadius: BorderRadius.circular(2),
          ),
        ),
        const SizedBox(width: 12),
        Text(
          title,
          style: TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.w800,
            color: AppTheme.primaryColor,
          ),
        ),
      ],
    );
  }

  Widget _buildBulletList(List items) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: items.map((item) {
        return Padding(
          padding: const EdgeInsets.only(bottom: 10),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                margin: const EdgeInsets.only(top: 6),
                width: 8,
                height: 8,
                decoration: BoxDecoration(
                  color: AppTheme.primaryColor,
                  borderRadius: BorderRadius.circular(4),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  _stripHtml(item.toString()),
                  style: const TextStyle(fontSize: 15, height: 1.6, color: Color(0xFF212121)),
                ),
              ),
            ],
          ),
        );
      }).toList(),
    );
  }

  Widget _buildDetailChip(IconData icon, String label, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: color.withOpacity(0.2),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color.withOpacity(0.5)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: color),
          const SizedBox(width: 6),
          Text(
            label,
            style: TextStyle(
              color: color,
              fontWeight: FontWeight.w600,
              fontSize: 12,
            ),
          ),
        ],
      ),
    );
  }

  String _formatDate(dynamic date) {
    if (date == null) return '';
    try {
      final dt = date is String ? DateTime.parse(date) : date as DateTime;
      return '${dt.day} ${_monthName(dt.month)} ${dt.year}';
    } catch (_) {
      return date.toString();
    }
  }

  String _monthName(int month) {
    const months = [
      'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
      'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
    ];
    return months[month - 1];
  }

  String _stripHtml(String html) {
    return html.replaceAll(RegExp(r'<[^>]*>'), '').replaceAll('&nbsp;', ' ').trim();
  }

  void _shareJob() {
    // TODO: Implement share
  }
}
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/services/api_service.dart';

class CareersPage extends ConsumerStatefulWidget {
  const CareersPage({super.key});

  @override
  ConsumerState<CareersPage> createState() => _CareersPageState();
}

class _CareersPageState extends ConsumerState<CareersPage> {
  List<Map<String, dynamic>> _jobs = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadJobs();
  }

  Future<void> _loadJobs() async {
    try {
      final api = ApiService();
      final response = await api.get('/careers/jobs', queryParameters: {'limit': 20});
      if (mounted) {
        setState(() {
          if (response['success'] == true && response['data'] is List) {
            _jobs = (response['data'] as List)
                .map((e) => Map<String, dynamic>.from(e as Map<dynamic, dynamic>))
                .toList();
          } else {
            _jobs = [];
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
        title: const Text('Careers'),
        backgroundColor: Colors.transparent,
        elevation: 0,
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
                      Text('Failed to load jobs: $_error'),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: _loadJobs,
                        child: const Text('Retry'),
                      ),
                    ],
                  ),
                )
              : _jobs.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.work_outline, size: 64, color: Colors.grey[300]),
                          const SizedBox(height: 16),
                          Text('No open positions at the moment', style: TextStyle(color: Colors.grey[600], fontSize: 18)),
                          const SizedBox(height: 16),
                          Text('Check back later for new opportunities', style: TextStyle(color: Colors.grey[500])),
                        ],
                      ),
                    )
                  : RefreshIndicator(
                      onRefresh: _loadJobs,
                      child: ListView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: _jobs.length,
                        itemBuilder: (context, index) {
                          final job = _jobs[index];
                          return _buildJobCard(job);
                        },
                      ),
                    ),
    );
  }

  Widget _buildJobCard(Map<String, dynamic> job) {
    final title = (job['title'] ?? 'Untitled Position').toString();
    final department = (job['department'] ?? job['category'] ?? '').toString();
    final location = (job['location'] ?? '').toString();
    final type = (job['employment_type'] ?? job['type'] ?? 'Full-time').toString();
    final experience = (job['experience'] ?? job['experience_required'] ?? '').toString();
    final salary = (job['salary'] ?? job['salary_range'] ?? '').toString();
    final postedDate = job['created_at'] ?? job['posted_date'];
    final id = (job['id'] ?? job['slug'] ?? job['id'])?.toString();

    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: InkWell(
        onTap: id != null ? () => context.go('/careers/$id') : null,
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          title,
                          style: const TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.w700,
                          ),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                        if (department.isNotEmpty) ...[
                          const SizedBox(height: 4),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: AppTheme.primaryColor.withValues(alpha: 0.1),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(
                              department,
                              style: TextStyle(
                                color: AppTheme.primaryColor,
                                fontWeight: FontWeight.w600,
                                fontSize: 12,
                              ),
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              Wrap(
                spacing: 12,
                runSpacing: 8,
                children: [
                  if (location.isNotEmpty)
                    _buildDetailChip(Icons.location_on, location),
                  if (type.isNotEmpty)
                    _buildDetailChip(Icons.work_outline, type),
                  if (experience.isNotEmpty)
                    _buildDetailChip(Icons.timeline, experience),
                  if (salary.isNotEmpty)
                    _buildDetailChip(Icons.attach_money, salary),
                ],
              ),
              if (postedDate != null)
                Padding(
                  padding: const EdgeInsets.only(top: 12),
                  child: Text(
                    'Posted ${_formatDate(postedDate)}',
                    style: TextStyle(color: Colors.grey[500], fontSize: 12),
                  ),
                )
              else
                const SizedBox.shrink(),
              const SizedBox(height: 16),
              Row(
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  OutlinedButton.icon(
                    onPressed: () => _shareJob(id ?? ''),
                    icon: const Icon(Icons.share, size: 18),
                    label: const Text('Share'),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppTheme.primaryColor,
                      side: BorderSide(color: AppTheme.primaryColor),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                  const SizedBox(width: 12),
                  ElevatedButton.icon(
                    onPressed: () => context.go('/careers/apply/$id'),
                    icon: const Icon(Icons.send, size: 18),
                    label: const Text('Apply Now'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppTheme.primaryColor,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
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

  Widget _buildDetailChip(IconData icon, String label) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: Colors.grey[100],
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey[200]!),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: Colors.grey[600]),
          const SizedBox(width: 6),
          Text(
            label,
            style: TextStyle(
              fontSize: 12,
              color: Colors.grey[700],
              fontWeight: FontWeight.w500,
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
      final now = DateTime.now();
      final diff = now.difference(dt);
      if (diff.inDays == 0) return 'Today';
      if (diff.inDays == 1) return 'Yesterday';
      if (diff.inDays < 7) return '${diff.inDays} days ago';
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

  void _shareJob(String id) {
    // TODO: Implement share functionality
  }
}
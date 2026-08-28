import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

import '../../../core/theme/app_theme.dart';
import '../../../data/repositories/kyc_repository_provider.dart';

class CareersPage extends ConsumerStatefulWidget {
  const CareersPage({super.key});

  @override
  ConsumerState<CareersPage> createState() => _CareersPageState();
}

class _CareersPageState extends ConsumerState<CareersPage> {
  bool _isLoading = true;
  List<Map<String, dynamic>> _jobs = [];
  String _selectedDepartment = 'All';

  List<String> get _departments {
    final depts =
        _jobs
            .map((j) => j['department']?.toString() ?? 'Other')
            .toSet()
            .toList()
          ..sort();
    return ['All', ...depts];
  }

  List<Map<String, dynamic>> get _filteredJobs {
    if (_selectedDepartment == 'All') return _jobs;
    return _jobs
        .where((j) => j['department']?.toString() == _selectedDepartment)
        .toList();
  }

  @override
  void initState() {
    super.initState();
    _loadJobs();
  }

  Future<void> _loadJobs() async {
    setState(() => _isLoading = true);
    try {
      final api = ref.read(apiServiceProvider);
      final response = await api.get('careers');
      final data = response['data'] ?? [];
      if (mounted && data is List) {
        setState(() {
          _jobs = data.map((e) => Map<String, dynamic>.from(e as Map)).toList();
          _isLoading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Careers'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(color: AppTheme.primaryColor),
            )
          : RefreshIndicator(
              onRefresh: _loadJobs,
              color: AppTheme.primaryColor,
              child: CustomScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                slivers: [
                  SliverToBoxAdapter(child: _buildHeader()),
                  if (_departments.length > 1)
                    SliverToBoxAdapter(child: _buildFilterChips()),
                  if (_filteredJobs.isEmpty)
                    SliverFillRemaining(
                      child: Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              Icons.work_off_outlined,
                              size: 64,
                              color: Colors.grey.shade300,
                            ),
                            const SizedBox(height: 16),
                            Text(
                              'No open positions',
                              style: TextStyle(
                                fontSize: 16,
                                color: Colors.grey.shade500,
                              ),
                            ),
                          ],
                        ),
                      ),
                    )
                  else
                    SliverPadding(
                      padding: const EdgeInsets.fromLTRB(16, 0, 16, 24),
                      sliver: SliverList(
                        delegate: SliverChildBuilderDelegate(
                          (context, index) =>
                              _buildJobCard(_filteredJobs[index]),
                          childCount: _filteredJobs.length,
                        ),
                      ),
                    ),
                ],
              ),
            ),
    );
  }

  Widget _buildHeader() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(24, 24, 24, 24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Join Our Team',
            style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 8),
          Text(
            'Be part of India\'s growing real estate success story. We\'re looking for talented individuals.',
            style: TextStyle(
              fontSize: 14,
              color: Colors.grey.shade600,
              height: 1.4,
            ),
          ),
          const SizedBox(height: 16),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            decoration: BoxDecoration(
              color: AppTheme.primaryColor.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(
                  Icons.work_outline,
                  size: 18,
                  color: AppTheme.primaryColor,
                ),
                const SizedBox(width: 6),
                Text(
                  '${_jobs.length} Open Positions',
                  style: const TextStyle(
                    fontWeight: FontWeight.w600,
                    color: AppTheme.primaryColor,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChips() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 0, 16, 12),
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: Row(
          children: _departments.map((dept) {
            final selected = dept == _selectedDepartment;
            return Padding(
              padding: const EdgeInsets.only(right: 8),
              child: ChoiceChip(
                label: Text(dept),
                selected: selected,
                onSelected: (_) => setState(() => _selectedDepartment = dept),
                selectedColor: AppTheme.primaryColor,
                labelStyle: TextStyle(
                  color: selected ? Colors.white : Colors.grey.shade700,
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                ),
                backgroundColor: Colors.grey.shade100,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(20),
                ),
              ),
            );
          }).toList(),
        ),
      ),
    );
  }

  Widget _buildJobCard(Map<String, dynamic> job) {
    final id = job['id'];
    final title = job['title']?.toString() ?? '';
    final department = job['department']?.toString() ?? '';
    final location = job['location']?.toString() ?? '';
    final type = job['employment_type']?.toString() ?? '';
    final experience = job['experience_required']?.toString() ?? '';
    final salary = job['salary_range']?.toString() ?? '';
    final vacancies = job['vacancies']?.toString() ?? '';
    final createdAt = job['created_at']?.toString() ?? '';

    String formattedDate = '';
    try {
      formattedDate = DateFormat(
        'MMM dd, yyyy',
      ).format(DateTime.parse(createdAt));
    } catch (_) {}

    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Card(
        elevation: 1,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        child: InkWell(
          onTap: () => context.push('/careers/$id'),
          borderRadius: BorderRadius.circular(14),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: AppTheme.primaryColor.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: const Icon(
                        Icons.work_outline,
                        color: AppTheme.primaryColor,
                        size: 24,
                      ),
                    ),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            title,
                            style: const TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                          ),
                          const SizedBox(height: 4),
                          Text(
                            department,
                            style: TextStyle(
                              fontSize: 13,
                              color: Colors.grey.shade600,
                            ),
                          ),
                        ],
                      ),
                    ),
                    Icon(Icons.chevron_right, color: Colors.grey.shade400),
                  ],
                ),
                const Divider(height: 20),
                Wrap(
                  spacing: 12,
                  runSpacing: 8,
                  children: [
                    if (location.isNotEmpty)
                      _infoChip(Icons.location_on_outlined, location),
                    if (type.isNotEmpty) _infoChip(Icons.badge_outlined, type),
                    if (experience.isNotEmpty)
                      _infoChip(Icons.timeline_outlined, experience),
                    if (salary.isNotEmpty)
                      _infoChip(Icons.currency_rupee, salary),
                    if (vacancies.isNotEmpty)
                      _infoChip(Icons.people_outline, '$vacancies openings'),
                  ],
                ),
                if (formattedDate.isNotEmpty) ...[
                  const SizedBox(height: 8),
                  Text(
                    'Posted $formattedDate',
                    style: TextStyle(fontSize: 11, color: Colors.grey.shade400),
                  ),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _infoChip(IconData icon, String text) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: Colors.grey.shade50,
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: Colors.grey.shade200),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 13, color: Colors.grey.shade600),
          const SizedBox(width: 4),
          Text(
            text,
            style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
          ),
        ],
      ),
    );
  }
}

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';

import '../../../core/providers/auth_provider.dart';
import '../../../core/theme/app_theme.dart';
import '../../../data/services/employee_service.dart';

/// Employee Dashboard — real API data for tasks, attendance, and announcements
class EmployeeDashboardPage extends ConsumerStatefulWidget {
  const EmployeeDashboardPage({super.key});

  @override
  ConsumerState<EmployeeDashboardPage> createState() => _EmployeeDashboardPageState();
}

class _EmployeeDashboardPageState extends ConsumerState<EmployeeDashboardPage> {
  bool _loading = true;
  Map<String, dynamic>? _dashboardData;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    try {
      final svc = ref.read(employeeServiceProvider);
      final data = await svc.getDashboard();
      if (mounted) {
        setState(() => _dashboardData = data);
      }
    } catch (e) {
      if (mounted) setState(() => _error = 'Failed to load: $e');
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  String _greeting() {
    final h = DateTime.now().hour;
    if (h < 12) return 'Good Morning';
    if (h < 17) return 'Good Afternoon';
    return 'Good Evening';
  }

  @override
  Widget build(BuildContext context) {
    final user = ref.watch(authProvider);
    final name = (user?.name ?? 'Employee').split(' ').first;

    final att = _dashboardData?['attendance'] as Map<String, dynamic>?;
    final tasks = _dashboardData?['tasks'] as Map<String, dynamic>?;
    final announcements = (_dashboardData?['announcements'] as List?)?.cast<Map<String, dynamic>>();

    final isPunchedIn = att?['punched_in'] == true;
    final isPunchedOut = att?['punched_out'] == true;
    final hoursWorked = att?['hours_worked'];
    final pendingTasks = tasks?['pending'] as int? ?? 0;
    final overdueTasks = tasks?['overdue'] as int? ?? 0;

    if (_loading && _dashboardData == null) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_error != null && _dashboardData == null) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.error_outline, size: 48, color: AppTheme.errorColor),
            const SizedBox(height: 12),
            Text(_error!, style: const TextStyle(color: AppTheme.errorColor)),
            const SizedBox(height: 16),
            ElevatedButton(onPressed: _load, child: const Text('Retry')),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _load,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildWelcomeCard(name, isPunchedIn, hoursWorked),
            const SizedBox(height: 16),
            _buildStatsGrid(
              pendingTasks: pendingTasks,
              overdueTasks: overdueTasks,
              isPunchedIn: isPunchedIn,
              hoursWorked: hoursWorked,
              att: att,
            ),
            const SizedBox(height: 20),

            _buildSectionTitle('Attendance', () => context.push('/employee/check-in')),
            const SizedBox(height: 8),
            _buildAttendanceCard(isPunchedIn, isPunchedOut, hoursWorked, att),
            const SizedBox(height: 20),

            _buildSectionTitle('Quick Actions', () {}),
            const SizedBox(height: 8),
            _buildQuickActions(),
            const SizedBox(height: 20),

            _buildSectionTitle('Announcements', () {}),
            const SizedBox(height: 8),
            _buildAnnouncements(announcements),
            const SizedBox(height: 32),
          ],
        ),
      ),
    );
  }

  Widget _buildWelcomeCard(String name, bool isPunchedIn, dynamic hoursWorked) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [AppTheme.primaryColor, Color(0xFF4338CA)],
        ),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            '${_greeting()}, $name!',
            style: GoogleFonts.outfit(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 4),
          Text(
            _dashboardData != null
                ? (isPunchedIn ? 'Checked in today${hoursWorked != null ? ' \u2014 ${hoursWorked}h worked' : ''}' : 'Not checked in yet')
                : 'Loading...',
            style: TextStyle(color: Colors.white.withValues(alpha: 0.8), fontSize: 13),
          ),
          const SizedBox(height: 12),
          SizedBox(
            height: 32,
            child: ElevatedButton.icon(
              onPressed: () => context.push('/employee/check-in'),
              icon: Icon(isPunchedIn ? Icons.logout : Icons.fingerprint, size: 16),
              label: Text(isPunchedIn ? 'Check Out' : 'Check In', style: const TextStyle(fontSize: 12)),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.white,
                foregroundColor: AppTheme.primaryColor,
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 0),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatsGrid({
    required int pendingTasks,
    required int overdueTasks,
    required bool isPunchedIn,
    required dynamic hoursWorked,
    required Map<String, dynamic>? att,
  }) {
    final stats = [
      {
        'label': 'Tasks Pending',
        'value': '$pendingTasks',
        'icon': Icons.pending_outlined,
        'color': AppTheme.warningColor,
        'onTap': () => context.push('/employee/tasks'),
      },
      {
        'label': 'Overdue',
        'value': '$overdueTasks',
        'icon': Icons.warning_amber_outlined,
        'color': overdueTasks > 0 ? AppTheme.errorColor : AppTheme.successColor,
        'onTap': () => context.push('/employee/tasks'),
      },
      {
        'label': 'Status',
        'value': isPunchedIn ? 'In' : 'Out',
        'icon': isPunchedIn ? Icons.check_circle_outline : Icons.access_time,
        'color': isPunchedIn ? AppTheme.successColor : Colors.grey,
        'onTap': () => context.push('/employee/check-in'),
      },
      {
        'label': 'Hours',
        'value': hoursWorked?.toString() ?? '0',
        'icon': Icons.timer_outlined,
        'color': AppTheme.infoColor,
        'onTap': () => context.push('/employee/check-in'),
      },
    ];

    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        childAspectRatio: 1.6,
        crossAxisSpacing: 10,
        mainAxisSpacing: 10,
      ),
      itemCount: stats.length,
      itemBuilder: (context, index) {
        final stat = stats[index];
        final color = stat['color'] as Color;
        return GestureDetector(
          onTap: stat['onTap'] as VoidCallback?,
          child: Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              boxShadow: [BoxShadow(color: color.withValues(alpha: 0.08), blurRadius: 8)],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(stat['icon'] as IconData, color: color, size: 20),
                const SizedBox(height: 8),
                Text(
                  stat['value'] as String,
                  style: GoogleFonts.outfit(fontSize: 20, fontWeight: FontWeight.bold, color: color),
                ),
                Text(
                  stat['label'] as String,
                  style: GoogleFonts.inter(fontSize: 11, color: Colors.grey.shade600),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildAttendanceCard(bool isPunchedIn, bool isPunchedOut, dynamic hoursWorked, Map<String, dynamic>? att) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 6)],
      ),
      child: Row(
        children: [
          Container(
            width: 42,
            height: 42,
            decoration: BoxDecoration(
              color: (isPunchedIn ? AppTheme.successColor : Colors.grey).withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(
              isPunchedIn ? Icons.check_circle : Icons.access_time,
              color: isPunchedIn ? AppTheme.successColor : Colors.grey,
              size: 20,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  isPunchedIn ? (isPunchedOut ? 'Day Complete' : 'Clocked In') : 'Not Checked In',
                  style: GoogleFonts.outfit(fontWeight: FontWeight.w600, fontSize: 13),
                ),
                if (att != null) ...[
                  const SizedBox(height: 2),
                  Text(
                    [
                      if (att['punch_in_time'] != null) 'In: ${att['punch_in_time']}',
                      if (att['punch_out_time'] != null) 'Out: ${att['punch_out_time']}',
                      if (hoursWorked != null) '${hoursWorked}h worked',
                    ].join(' \u00b7 '),
                    style: GoogleFonts.inter(fontSize: 11, color: Colors.grey.shade500),
                  ),
                ],
              ],
            ),
          ),
          Icon(Icons.chevron_right, color: Colors.grey.shade400, size: 20),
        ],
      ),
    );
  }

  Widget _buildQuickActions() {
    final actions = [
      {'icon': Icons.task_alt, 'label': 'My Tasks', 'color': AppTheme.warningColor, 'route': '/employee/tasks'},
      {'icon': Icons.fingerprint, 'label': 'Check In', 'color': AppTheme.successColor, 'route': '/employee/check-in'},
      {'icon': Icons.person, 'label': 'Profile', 'color': AppTheme.infoColor, 'route': '/employee/profile'},
    ];

    return Row(
      children: actions.map((a) {
        final color = a['color'] as Color;
        return Expanded(
          child: GestureDetector(
            onTap: () => context.push(a['route'] as String),
            child: Container(
              margin: const EdgeInsets.symmetric(horizontal: 4),
              padding: const EdgeInsets.symmetric(vertical: 14),
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.08),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Column(
                children: [
                  Icon(a['icon'] as IconData, color: color, size: 22),
                  const SizedBox(height: 6),
                  Text(
                    a['label'] as String,
                    style: GoogleFonts.inter(fontSize: 11, fontWeight: FontWeight.w500, color: color),
                  ),
                ],
              ),
            ),
          ),
        );
      }).toList(),
    );
  }

  Widget _buildSectionTitle(String title, VoidCallback onSeeAll) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(title, style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.bold)),
        TextButton(onPressed: onSeeAll, child: const Text('See All', style: TextStyle(fontSize: 12))),
      ],
    );
  }

  Widget _buildAnnouncements(List<Map<String, dynamic>>? announcements) {
    final items = announcements ?? [];

    if (items.isEmpty) {
      return Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(10),
          boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.03), blurRadius: 4)],
        ),
        child: Row(
          children: [
            Icon(Icons.campaign_outlined, color: Colors.grey.shade400, size: 20),
            const SizedBox(width: 12),
            Text('No recent announcements', style: GoogleFonts.inter(fontSize: 13, color: Colors.grey.shade500)),
          ],
        ),
      );
    }

    return Column(
      children: items.map((a) => Container(
        margin: const EdgeInsets.only(bottom: 8),
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(10),
          boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.03), blurRadius: 4)],
        ),
        child: Row(
          children: [
            Container(
              width: 36,
              height: 36,
              decoration: BoxDecoration(
                color: AppTheme.infoColor.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Icon(Icons.campaign_outlined, color: AppTheme.infoColor, size: 18),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(a['title'] as String? ?? '', style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 13)),
                  Text(a['subtitle'] as String? ?? '', style: GoogleFonts.inter(fontSize: 11, color: Colors.grey.shade500)),
                ],
              ),
            ),
            Text(a['time'] as String? ?? '', style: GoogleFonts.inter(fontSize: 10, color: Colors.grey.shade400)),
          ],
        ),
      )).toList(),
    );
  }
}

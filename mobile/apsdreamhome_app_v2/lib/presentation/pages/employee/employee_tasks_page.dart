import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';

import '../../../core/theme/app_theme.dart';
import '../../../data/repositories/kyc_repository_provider.dart';

/// Employee Tasks Page — fetches from backend API
class EmployeeTasksPage extends ConsumerStatefulWidget {
  const EmployeeTasksPage({super.key});

  @override
  ConsumerState<EmployeeTasksPage> createState() => _EmployeeTasksPageState();
}

class _EmployeeTasksPageState extends ConsumerState<EmployeeTasksPage> {
  bool _loading = true;
  List<Map<String, dynamic>> _tasks = [];
  String? _error;
  String _filter = 'all'; // all, upcoming, pending, completed

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    try {
      final api = ref.read(apiServiceProvider);
      final res = await api.get('/admin/tasks');
      if (res['success'] == true && mounted) {
        final raw = res['tasks'] ?? res['data'] ?? [];
        setState(() {
          _tasks = List<Map<String, dynamic>>.from(
            (raw as List).map((e) => Map<String, dynamic>.from(e as Map)),
          );
        });
      } else if (mounted) {
        // Fallback: try employee-specific endpoint
        try {
          final res2 = await api.get('/employee/api/tasks');
          if (res2['success'] == true && mounted) {
            final raw = res2['tasks'] ?? res2['data'] ?? [];
            setState(() {
              _tasks = List<Map<String, dynamic>>.from(
                (raw as List).map((e) => Map<String, dynamic>.from(e as Map)),
              );
            });
          }
        } catch (_) {
          // Keep empty tasks
        }
      }
    } catch (e) {
      if (mounted) setState(() => _error = 'Failed to load tasks: $e');
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  List<Map<String, dynamic>> get _filteredTasks {
    if (_filter == 'all') return _tasks;
    return _tasks.where((t) {
      final status = (t['status'] ?? '').toString().toLowerCase();
      if (_filter == 'completed') return status == 'completed' || status == 'done';
      if (_filter == 'pending') return status == 'pending' || status == 'in_progress';
      if (_filter == 'upcoming') return status == 'upcoming' || status == 'todo';
      return true;
    }).toList();
  }

  Color _priorityColor(String priority) {
    switch (priority.toLowerCase()) {
      case 'high':
        return AppTheme.warningColor;
      case 'medium':
        return AppTheme.primaryColor;
      case 'low':
        return AppTheme.successColor;
      default:
        return Colors.grey;
    }
  }

  IconData _statusIcon(String status) {
    switch (status.toLowerCase()) {
      case 'completed':
      case 'done':
        return Icons.check_circle;
      case 'in_progress':
      case 'in-progress':
        return Icons.play_circle_outline;
      case 'pending':
        return Icons.pending_outlined;
      case 'upcoming':
      case 'todo':
        return Icons.radio_button_unchecked;
      default:
        return Icons.help_outline;
    }
  }

  Future<void> _updateTaskStatus(int taskId, String newStatus) async {
    try {
      final api = ref.read(apiServiceProvider);
      await api.post('/admin/tasks/update/$taskId', data: {'status': newStatus});
      await _load();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to update: $e'), backgroundColor: AppTheme.errorColor),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        // Filter chips
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 0),
          child: Row(
            children: [
              _buildFilterChip('All', 'all'),
              const SizedBox(width: 6),
              _buildFilterChip('Pending', 'pending'),
              const SizedBox(width: 6),
              _buildFilterChip('Done', 'completed'),
            ],
          ),
        ),
        const SizedBox(height: 8),

        // Task list
        Expanded(
          child: _loading
              ? const Center(child: CircularProgressIndicator())
              : _error != null
                  ? Center(
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.error_outline, size: 48, color: AppTheme.errorColor.withValues(alpha: 0.5)),
                          const SizedBox(height: 12),
                          Text(_error!, style: GoogleFonts.inter(color: AppTheme.errorColor), textAlign: TextAlign.center),
                          const SizedBox(height: 16),
                          ElevatedButton.icon(
                            onPressed: _load,
                            icon: const Icon(Icons.refresh, size: 16),
                            label: const Text('Retry'),
                          ),
                        ],
                      ),
                    )
                  : _filteredTasks.isEmpty
                      ? Center(
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.task_alt, size: 48, color: Colors.grey.shade300),
                              const SizedBox(height: 12),
                              Text(
                                _filter == 'all' ? 'No tasks assigned' : 'No $_filter tasks',
                                style: GoogleFonts.inter(color: Colors.grey.shade500, fontSize: 14),
                              ),
                            ],
                          ),
                        )
                      : RefreshIndicator(
                          onRefresh: _load,
                          child: ListView.builder(
                            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                            itemCount: _filteredTasks.length,
                            itemBuilder: (context, index) => _buildTaskCard(_filteredTasks[index]),
                          ),
                        ),
        ),
      ],
    );
  }

  Widget _buildFilterChip(String label, String value) {
    final isSelected = _filter == value;
    return GestureDetector(
      onTap: () => setState(() => _filter = value),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
        decoration: BoxDecoration(
          color: isSelected ? AppTheme.primaryColor : Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isSelected ? AppTheme.primaryColor : Colors.grey.shade300,
          ),
        ),
        child: Text(
          label,
          style: GoogleFonts.inter(
            fontSize: 12,
            fontWeight: FontWeight.w500,
            color: isSelected ? Colors.white : Colors.grey.shade700,
          ),
        ),
      ),
    );
  }

  Widget _buildTaskCard(Map<String, dynamic> task) {
    final title = task['title'] ?? task['name'] ?? 'Untitled Task';
    final description = task['description'] ?? task['desc'] ?? '';
    final priority = (task['priority'] ?? 'medium').toString();
    final status = (task['status'] ?? 'pending').toString();
    final dueDate = task['due_date'] ?? task['deadline'] ?? '';
    final taskId = task['id'];
    final color = _priorityColor(priority);

    final isCompleted = status.toLowerCase() == 'completed' || status.toLowerCase() == 'done';

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 6)],
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Status icon
          GestureDetector(
            onTap: taskId != null && !isCompleted
                ? () => _updateTaskStatus(taskId is int ? taskId : int.tryParse('$taskId') ?? 0, 'completed')
                : null,
            child: Container(
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                color: (isCompleted ? AppTheme.successColor : color).withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(
                _statusIcon(status),
                color: isCompleted ? AppTheme.successColor : color,
                size: 20,
              ),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  '$title',
                  style: GoogleFonts.inter(
                    fontWeight: FontWeight.w600,
                    fontSize: 13,
                    decoration: isCompleted ? TextDecoration.lineThrough : null,
                    color: isCompleted ? Colors.grey.shade500 : null,
                  ),
                ),
                if ('$description'.isNotEmpty) ...[
                  const SizedBox(height: 2),
                  Text(
                    '$description',
                    style: GoogleFonts.inter(fontSize: 11, color: Colors.grey.shade500),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
                const SizedBox(height: 4),
                Row(
                  children: [
                    if ('$dueDate'.isNotEmpty) ...[
                      Icon(Icons.access_time, size: 11, color: Colors.grey.shade400),
                      const SizedBox(width: 3),
                      Text('$dueDate', style: GoogleFonts.inter(fontSize: 10, color: Colors.grey.shade400)),
                    ],
                  ],
                ),
              ],
            ),
          ),
          // Priority badge
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Text(
              priority.toUpperCase(),
              style: GoogleFonts.inter(fontSize: 9, fontWeight: FontWeight.bold, color: color),
            ),
          ),
        ],
      ),
    );
  }
}

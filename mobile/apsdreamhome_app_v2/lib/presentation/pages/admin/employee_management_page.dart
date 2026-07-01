import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/utils/responsive_helper.dart';
import '../../../data/services/crm_service.dart';

class EmployeeManagementPage extends ConsumerStatefulWidget {
  const EmployeeManagementPage({super.key});

  @override
  ConsumerState<EmployeeManagementPage> createState() =>
      _EmployeeManagementPageState();
}

class _EmployeeManagementPageState
    extends ConsumerState<EmployeeManagementPage> {
  String? _selectedRole;
  String? _selectedStatus;
  String _search = '';
  final _searchCtrl = TextEditingController();
  Timer? _debounce;

  @override
  void dispose() {
    _debounce?.cancel();
    _searchCtrl.dispose();
    super.dispose();
  }

  Map<String, String?> get _filters => {
        if (_search.isNotEmpty) 'search': _search,
        if (_selectedRole != null) 'role': _selectedRole,
        if (_selectedStatus != null) 'status': _selectedStatus,
      };

  @override
  Widget build(BuildContext context) {
    final dataAsync = ref.watch(crmAdminEmployeesProvider(_filters));

    return Column(
      children: [
        // Header
        Container(
          padding: const EdgeInsets.all(24),
          decoration: BoxDecoration(
            color: Colors.white,
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.05),
                blurRadius: 10,
              ),
            ],
          ),
          child: Row(
            children: [
              const Icon(Icons.group, size: 32, color: AppTheme.primaryColor),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Employee Management',
                        style: TextStyle(
                            fontSize: ResponsiveHelper.fontSize(context, 24), fontWeight: FontWeight.bold)),
                    const SizedBox(height: 4),
                    const Text('Manage staff, roles, and permissions',
                        style: TextStyle(color: Colors.grey)),
                  ],
                ),
              ),
              OutlinedButton.icon(
                onPressed: () => ref.invalidate(crmAdminEmployeesProvider),
                icon: const Icon(Icons.refresh),
                label: const Text('Refresh'),
              ),
            ],
          ),
        ),

        // Stats
        dataAsync.when(
          data: (data) {
            final stats = data['stats'] as Map<String, dynamic>? ?? {};
            return Container(
              padding: const EdgeInsets.all(16),
              child: Wrap(
                spacing: 8,
                runSpacing: 8,
                children: [
                  _buildStatCard(
                      'Total', '${stats['total'] ?? 0}', Colors.blue),
                  _buildStatCard(
                      'Active', '${stats['active'] ?? 0}', Colors.green),
                  _buildStatCard('Inactive', '${stats['inactive'] ?? 0}',
                      Colors.red),
                  _buildStatCard(
                      'Employee',
                      '${(stats['by_role'] as Map<String, dynamic>?)?['employee'] ?? 0}',
                      Colors.purple),
                  _buildStatCard(
                      'Agent',
                      '${(stats['by_role'] as Map<String, dynamic>?)?['agent'] ?? 0}',
                      Colors.orange),
                  _buildStatCard(
                      'Associate',
                      '${(stats['by_role'] as Map<String, dynamic>?)?['associate'] ?? 0}',
                      Colors.teal),
                ],
              ),
            );
          },
          loading: () => const SizedBox(
              height: 80,
              child: Center(child: CircularProgressIndicator())),
          error: (_, __) => const SizedBox.shrink(),
        ),

        // Filters
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 8),
          child: Row(
            children: [
              Expanded(
                flex: 2,
                child: TextField(
                  controller: _searchCtrl,
                  decoration: InputDecoration(
                    hintText: 'Search employees...',
                    prefixIcon: const Icon(Icons.search),
                    filled: true,
                    fillColor: Colors.grey.shade100,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: BorderSide.none,
                    ),
                  ),
                  onChanged: (v) {
                    _debounce?.cancel();
                    _debounce =
                        Timer(const Duration(milliseconds: 500), () {
                      setState(() => _search = v);
                    });
                  },
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: DropdownButtonFormField<String>(
                  initialValue: _selectedRole,
                  hint: const Text('Role'),
                  decoration: InputDecoration(
                    filled: true,
                    fillColor: Colors.grey.shade100,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: BorderSide.none,
                    ),
                  ),
                  items: const [
                    DropdownMenuItem(value: null, child: Text('All Roles')),
                    DropdownMenuItem(value: 'employee', child: Text('Employee')),
                    DropdownMenuItem(value: 'agent', child: Text('Agent')),
                    DropdownMenuItem(
                        value: 'associate', child: Text('Associate')),
                  ],
                  onChanged: (v) => setState(() {
                    _selectedRole = v;
                    ref.invalidate(crmAdminEmployeesProvider);
                  }),
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: DropdownButtonFormField<String>(
                  initialValue: _selectedStatus,
                  hint: const Text('Status'),
                  decoration: InputDecoration(
                    filled: true,
                    fillColor: Colors.grey.shade100,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: BorderSide.none,
                    ),
                  ),
                  items: const [
                    DropdownMenuItem(value: null, child: Text('All Status')),
                    DropdownMenuItem(value: 'active', child: Text('Active')),
                    DropdownMenuItem(
                        value: 'inactive', child: Text('Inactive')),
                  ],
                  onChanged: (v) => setState(() {
                    _selectedStatus = v;
                    ref.invalidate(crmAdminEmployeesProvider);
                  }),
                ),
              ),
            ],
          ),
        ),

        // Employee List
        Expanded(
          child: dataAsync.when(
            data: (data) {
              final employees =
                  (data['employees'] as List<dynamic>?) ?? [];
              if (employees.isEmpty) {
                return const Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.group_off, size: 64, color: Colors.grey),
                      SizedBox(height: 16),
                      Text('No employees found',
                          style: TextStyle(
                              fontSize: 18, color: Colors.grey)),
                    ],
                  ),
                );
              }
              return _buildEmployeeTable(employees);
            },
            loading: () =>
                const Center(child: CircularProgressIndicator()),
            error: (e, _) => Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(Icons.error_outline,
                      size: 48, color: Colors.red),
                  const SizedBox(height: 12),
                  Text('Error loading employees: $e'),
                  const SizedBox(height: 12),
                  ElevatedButton(
                    onPressed: () =>
                        ref.invalidate(crmAdminEmployeesProvider),
                    child: const Text('Retry'),
                  ),
                ],
              ),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildStatCard(String label, String value, Color color) {
    return Container(
      width: 100,
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Column(
        children: [
          FittedBox(
            child: Text(value,
                style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                    color: color)),
          ),
          const SizedBox(height: 4),
          Text(label,
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 11, color: Colors.grey)),
        ],
      ),
    );
  }

  Widget _buildEmployeeTable(List<dynamic> employees) {
    return Container(
      margin: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
          ),
        ],
      ),
      child: SingleChildScrollView(
        child: DataTable(
          columns: const [
            DataColumn(label: Text('Employee')),
            DataColumn(label: Text('Role')),
            DataColumn(label: Text('Department')),
            DataColumn(label: Text('Contact')),
            DataColumn(label: Text('Status')),
            DataColumn(label: Text('Joined')),
            DataColumn(label: Text('Actions')),
          ],
          rows: employees.map((e) {
            final emp = Map<String, dynamic>.from(e as Map);
            final role = (emp['role'] ?? '').toString();
            final status = (emp['status'] ?? 'active').toString();
            final name = (emp['name'] ?? 'Unknown').toString();
            final email = (emp['email'] ?? '').toString();
            final phone = (emp['phone'] ?? '').toString();
            final dept = (emp['department'] ?? '').toString();
            final created = (emp['created_at'] ?? '').toString();
            final joinDate = created.length >= 10
                ? created.substring(0, 10)
                : created;

            return DataRow(cells: [
              DataCell(Row(
                children: [
                  CircleAvatar(
                    backgroundColor:
                        _roleColor(role).withValues(alpha: 0.15),
                    child: Text(name.isNotEmpty ? name[0].toUpperCase() : '?',
                        style: TextStyle(
                            color: _roleColor(role),
                            fontWeight: FontWeight.bold)),
                  ),
                  const SizedBox(width: 12),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(name,
                          style: const TextStyle(
                              fontWeight: FontWeight.w600)),
                      Text(email,
                          style: TextStyle(
                              color: Colors.grey.shade600, fontSize: 12)),
                    ],
                  ),
                ],
              )),
              DataCell(_buildRoleBadge(role)),
              DataCell(Text(dept.isNotEmpty ? dept : '-')),
              DataCell(Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(phone.isNotEmpty ? phone : '-'),
                  Text(email,
                      style: TextStyle(
                          color: Colors.grey.shade600, fontSize: 11)),
                ],
              )),
              DataCell(_buildStatusBadge(status)),
              DataCell(Text(joinDate,
                  style: const TextStyle(fontSize: 12))),
              DataCell(Row(
                children: [
                  IconButton(
                    onPressed: () => _showEmployeeDetail(emp),
                    icon:
                        const Icon(Icons.visibility, size: 20, color: Colors.blue),
                    tooltip: 'View',
                  ),
                  IconButton(
                    onPressed: () {},
                    icon: const Icon(Icons.edit, size: 20, color: Colors.orange),
                    tooltip: 'Edit',
                  ),
                ],
              )),
            ]);
          }).toList(),
        ),
      ),
    );
  }

  Widget _buildRoleBadge(String role) {
    final color = _roleColor(role);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(role.toUpperCase(),
          style: TextStyle(
              color: color, fontWeight: FontWeight.bold, fontSize: 11)),
    );
  }

  Widget _buildStatusBadge(String status) {
    final isActive = status == 'active';
    final color = isActive ? Colors.green : Colors.red;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(status.toUpperCase(),
          style: TextStyle(
              color: color, fontWeight: FontWeight.bold, fontSize: 12)),
    );
  }

  Color _roleColor(String role) {
    switch (role) {
      case 'employee':
        return Colors.purple;
      case 'agent':
        return Colors.orange;
      case 'associate':
        return Colors.teal;
      default:
        return Colors.blue;
    }
  }

  void _showEmployeeDetail(Map<String, dynamic> emp) {
    final name = (emp['name'] ?? '').toString();
    final role = (emp['role'] ?? '').toString();
    final email = (emp['email'] ?? '').toString();
    final phone = (emp['phone'] ?? '').toString();
    final dept = (emp['department'] ?? '').toString();
    final designation = (emp['designation'] ?? '').toString();
    final empCode = (emp['emp_code'] ?? '').toString();
    final level = (emp['associate_level'] ?? '').toString();
    final status = (emp['status'] ?? '').toString();
    final created = (emp['created_at'] ?? '').toString();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => DraggableScrollableSheet(
        initialChildSize: 0.6,
        minChildSize: 0.3,
        maxChildSize: 0.9,
        expand: false,
        builder: (_, scrollCtrl) => ListView(
          controller: scrollCtrl,
          padding: const EdgeInsets.all(24),
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
            const SizedBox(height: 20),
            Center(
              child: CircleAvatar(
                radius: 36,
                backgroundColor: _roleColor(role).withValues(alpha: 0.15),
                child: Text(
                    name.isNotEmpty ? name[0].toUpperCase() : '?',
                    style: TextStyle(
                        fontSize: ResponsiveHelper.fontSize(context, 28),
                        fontWeight: FontWeight.bold,
                        color: _roleColor(role))),
              ),
            ),
            const SizedBox(height: 12),
            Center(
              child: Text(name,
                  style: const TextStyle(
                      fontSize: 22, fontWeight: FontWeight.bold)),
            ),
            const SizedBox(height: 4),
            Center(child: _buildRoleBadge(role)),
            const SizedBox(height: 24),
            _detailRow(Icons.email, 'Email', email),
            _detailRow(Icons.phone, 'Phone', phone),
            _detailRow(Icons.business, 'Department', dept.isNotEmpty ? dept : '-'),
            _detailRow(Icons.work, 'Designation', designation.isNotEmpty ? designation : '-'),
            if (empCode.isNotEmpty) _detailRow(Icons.badge, 'Emp Code', empCode),
            if (level.isNotEmpty) _detailRow(Icons.star, 'Level', level),
            _detailRow(Icons.circle, 'Status', status.toUpperCase()),
            _detailRow(Icons.calendar_today, 'Joined', created.length >= 10 ? created.substring(0, 10) : created),
          ],
        ),
      ),
    );
  }

  Widget _detailRow(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        children: [
          Icon(icon, size: 20, color: Colors.grey.shade600),
          const SizedBox(width: 12),
          SizedBox(
            width: 110,
            child: Text(label,
                style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
          ),
          Expanded(
            child: Text(value,
                style: const TextStyle(
                    fontWeight: FontWeight.w600, fontSize: 14)),
          ),
        ],
      ),
    );
  }
}

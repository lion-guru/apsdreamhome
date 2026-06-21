import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/app_theme.dart';

class EmployeeManagementPage extends ConsumerStatefulWidget {
  const EmployeeManagementPage({super.key});

  @override
  ConsumerState<EmployeeManagementPage> createState() =>
      _EmployeeManagementPageState();
}

class _EmployeeManagementPageState
    extends ConsumerState<EmployeeManagementPage> {
  String? _selectedDepartment;
  String? _selectedStatus;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Column(
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
                const Icon(
                  Icons.group,
                  size: 32,
                  color: AppTheme.primaryColor,
                ),
                const SizedBox(width: 16),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Employee Management',
                        style: TextStyle(
                          fontSize: 24,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      SizedBox(height: 4),
                      Text(
                        'Manage staff, roles, and permissions',
                        style: TextStyle(
                          color: Colors.grey,
                        ),
                      ),
                    ],
                  ),
                ),
                Row(
                  children: [
                    OutlinedButton.icon(
                      onPressed: () {},
                      icon: const Icon(Icons.assignment_ind),
                      label: const Text('Roles'),
                    ),
                    const SizedBox(width: 12),
                    ElevatedButton.icon(
                      onPressed: () => _showAddEmployeeDialog(),
                      icon: const Icon(Icons.person_add),
                      label: const Text('Add Employee'),
                    ),
                  ],
                ),
              ],
            ),
          ),

          // Stats
          Container(
            padding: const EdgeInsets.all(24),
            child: Row(
              children: [
                _buildStatCard('Total Employees', '156', Colors.blue),
                _buildStatCard('Active', '142', Colors.green),
                _buildStatCard('On Leave', '8', Colors.orange),
                _buildStatCard('Inactive', '6', Colors.red),
              ],
            ),
          ),

          // Filters
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 8),
            child: Row(
              children: [
                Expanded(
                  flex: 2,
                  child: TextField(
                    onChanged: (value) {},
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
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: DropdownButtonFormField<String>(
                    initialValue: _selectedDepartment,
                    hint: const Text('Department'),
                    decoration: InputDecoration(
                      filled: true,
                      fillColor: Colors.grey.shade100,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                        borderSide: BorderSide.none,
                      ),
                    ),
                    items: const [
                      DropdownMenuItem(value: null, child: Text('All Depts')),
                      DropdownMenuItem(value: 'sales', child: Text('Sales')),
                      DropdownMenuItem(
                          value: 'accounts', child: Text('Accounts')),
                      DropdownMenuItem(
                          value: 'marketing', child: Text('Marketing')),
                      DropdownMenuItem(
                          value: 'operations', child: Text('Operations')),
                    ],
                    onChanged: (value) =>
                        setState(() => _selectedDepartment = value),
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
                          value: 'on_leave', child: Text('On Leave')),
                      DropdownMenuItem(
                          value: 'inactive', child: Text('Inactive')),
                    ],
                    onChanged: (value) =>
                        setState(() => _selectedStatus = value),
                  ),
                ),
              ],
            ),
          ),

          // Employee Table
          Expanded(
            child: _buildEmployeeTable(),
          ),
        ],
      ),
    );
  }

  Widget _buildStatCard(String label, String value, Color color) {
    return Expanded(
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 8),
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: color.withValues(alpha: 0.3)),
        ),
        child: Column(
          children: [
            Text(
              value,
              style: TextStyle(
                fontSize: 28,
                fontWeight: FontWeight.bold,
                color: color,
              ),
            ),
            const SizedBox(height: 4),
            Text(label),
          ],
        ),
      ),
    );
  }

  Widget _buildEmployeeTable() {
    // Mock employee data
    final employees = [
      {
        'name': 'Rahul Sharma',
        'id': 'EMP001',
        'role': 'Sales Manager',
        'department': 'Sales',
        'phone': '+91 98765 43210',
        'email': 'rahul@aps.com',
        'status': 'Active',
        'joinDate': '15 Jan 2023',
      },
      {
        'name': 'Priya Patel',
        'id': 'EMP002',
        'role': 'Accountant',
        'department': 'Accounts',
        'phone': '+91 98765 43211',
        'email': 'priya@aps.com',
        'status': 'Active',
        'joinDate': '22 Feb 2023',
      },
      {
        'name': 'Amit Kumar',
        'id': 'EMP003',
        'role': 'Field Agent',
        'department': 'Sales',
        'phone': '+91 98765 43212',
        'email': 'amit@aps.com',
        'status': 'On Leave',
        'joinDate': '10 Mar 2023',
      },
      {
        'name': 'Sneha Gupta',
        'id': 'EMP004',
        'role': 'Marketing Head',
        'department': 'Marketing',
        'phone': '+91 98765 43213',
        'email': 'sneha@aps.com',
        'status': 'Active',
        'joinDate': '05 Apr 2023',
      },
    ];

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
            DataColumn(label: Text('ID')),
            DataColumn(label: Text('Role')),
            DataColumn(label: Text('Department')),
            DataColumn(label: Text('Contact')),
            DataColumn(label: Text('Status')),
            DataColumn(label: Text('Join Date')),
            DataColumn(label: Text('Actions')),
          ],
          rows: employees.map((emp) {
            return DataRow(
              cells: [
                DataCell(
                  Row(
                    children: [
                      CircleAvatar(
                        child: Text(emp['name']!.substring(0, 1)),
                      ),
                      const SizedBox(width: 12),
                      Text(emp['name']!),
                    ],
                  ),
                ),
                DataCell(Text(emp['id']!)),
                DataCell(Text(emp['role']!)),
                DataCell(Text(emp['department']!)),
                DataCell(
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(emp['phone']!),
                      Text(
                        emp['email']!,
                        style: TextStyle(
                          color: Colors.grey.shade600,
                          fontSize: 12,
                        ),
                      ),
                    ],
                  ),
                ),
                DataCell(
                  Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: emp['status'] == 'Active'
                          ? Colors.green.withValues(alpha: 0.1)
                          : emp['status'] == 'On Leave'
                              ? Colors.orange.withValues(alpha: 0.1)
                              : Colors.red.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      emp['status']!,
                      style: TextStyle(
                        color: emp['status'] == 'Active'
                            ? Colors.green
                            : emp['status'] == 'On Leave'
                                ? Colors.orange
                                : Colors.red,
                        fontWeight: FontWeight.bold,
                        fontSize: 12,
                      ),
                    ),
                  ),
                ),
                DataCell(Text(emp['joinDate']!)),
                DataCell(
                  Row(
                    children: [
                      IconButton(
                        onPressed: () {},
                        icon: const Icon(Icons.edit, size: 20),
                      ),
                      IconButton(
                        onPressed: () {},
                        icon: const Icon(Icons.visibility, size: 20),
                      ),
                      IconButton(
                        onPressed: () {},
                        icon: const Icon(Icons.delete,
                            size: 20, color: Colors.red),
                      ),
                    ],
                  ),
                ),
              ],
            );
          }).toList(),
        ),
      ),
    );
  }

  void _showAddEmployeeDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Add New Employee'),
        content: const Text('Employee creation form will be here'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Save'),
          ),
        ],
      ),
    );
  }
}

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/services/api_service.dart';
import '../../../core/utils/logger.dart';

final _usersProvider = FutureProvider.autoDispose<List<Map<String, dynamic>>>((ref) async {
  try {
    final api = ApiService();
    final response = await api.get('/admin/users');
    if (response['success'] == true && response['data'] != null) {
      return (response['data'] as List).cast<Map<String, dynamic>>();
    }
    if (response['data'] is List) {
      return (response['data'] as List).cast<Map<String, dynamic>>();
    }
    return [];
  } catch (e) {
    AppLogger.error('Error fetching users', e);
    return [];
  }
});

class UserManagementPage extends ConsumerStatefulWidget {
  const UserManagementPage({super.key});

  @override
  ConsumerState<UserManagementPage> createState() => _UserManagementPageState();
}

class _UserManagementPageState extends ConsumerState<UserManagementPage> {
  String _filterRole = 'all';
  String _searchQuery = '';

  @override
  Widget build(BuildContext context) {
    final usersAsync = ref.watch(_usersProvider);

    return Column(
      children: [
        _buildHeader(),
        _buildStatsRow(usersAsync),
        _buildSearchAndFilters(),
        Expanded(
          child: usersAsync.when(
            data: (users) {
              var filtered = users;
              if (_filterRole != 'all') {
                filtered = filtered.where((u) =>
                  (u['role']?.toString() ?? u['user_type']?.toString()) == _filterRole
                ).toList();
              }
              if (_searchQuery.isNotEmpty) {
                final q = _searchQuery.toLowerCase();
                filtered = filtered.where((u) =>
                  (u['name']?.toString().toLowerCase().contains(q) ?? false) ||
                  (u['email']?.toString().toLowerCase().contains(q) ?? false) ||
                  (u['phone']?.toString().contains(q) ?? false)
                ).toList();
              }
              if (filtered.isEmpty) return _buildEmptyState();
              return _buildUsersList(filtered);
            },
            loading: () => const Center(child: CircularProgressIndicator()),
            error: (e, _) => Center(child: Text('Error: $e')),
          ),
        ),
      ],
    );
  }

  Widget _buildHeader() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 8),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('User Management',
                  style: Theme.of(context).textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.bold)),
                const SizedBox(height: 4),
                Text('View and manage all platform users',
                  style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Colors.grey[600])),
              ],
            ),
          ),
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.invalidate(_usersProvider),
            tooltip: 'Refresh',
          ),
        ],
      ),
    );
  }

  Widget _buildStatsRow(AsyncValue<List<Map<String, dynamic>>> usersAsync) {
    return usersAsync.when(
      data: (users) {
        final total = users.length;
        final customers = users.where((u) => (u['role']?.toString() ?? u['user_type']?.toString()) == 'customer').length;
        final associates = users.where((u) => (u['role']?.toString() ?? u['user_type']?.toString()) == 'associate').length;
        final agents = users.where((u) => (u['role']?.toString() ?? u['user_type']?.toString()) == 'agent').length;
        final employees = users.where((u) => (u['role']?.toString() ?? u['user_type']?.toString()) == 'employee').length;
        return Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          child: Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              _buildStatCard('Total', '$total', Colors.blue),
              _buildStatCard('Customers', '$customers', Colors.green),
              _buildStatCard('Agents', '$agents', Colors.orange),
              _buildStatCard('Staff', '${associates + employees}', Colors.purple),
            ],
          ),
        );
      },
      loading: () => const SizedBox.shrink(),
      error: (_, __) => const SizedBox.shrink(),
    );
  }

  Widget _buildStatCard(String label, String value, Color color) {
    return Container(
      width: 100,
      padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: color.withValues(alpha: 0.2)),
      ),
      child: Column(
        children: [
          FittedBox(
            child: Text(value, style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: color)),
          ),
          const SizedBox(height: 2),
          Text(label, style: TextStyle(fontSize: 10, color: color.withValues(alpha: 0.7))),
        ],
      ),
    );
  }

  Widget _buildSearchAndFilters() {
    final roles = ['all', 'customer', 'associate', 'agent', 'employee', 'admin'];
    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 4),
          child: TextField(
            decoration: InputDecoration(
              hintText: 'Search by name, email, or phone...',
              prefixIcon: const Icon(Icons.search, size: 20),
              suffixIcon: _searchQuery.isNotEmpty
                ? IconButton(
                    icon: const Icon(Icons.clear, size: 18),
                    onPressed: () => setState(() => _searchQuery = ''),
                  )
                : null,
              contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
              filled: true,
              fillColor: Colors.grey[50],
            ),
            onChanged: (v) => setState(() => _searchQuery = v),
          ),
        ),
        SizedBox(
          height: 44,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 6),
            itemCount: roles.length,
            separatorBuilder: (_, __) => const SizedBox(width: 8),
            itemBuilder: (ctx, i) {
              final r = roles[i];
              final selected = _filterRole == r;
              final label = r[0].toUpperCase() + r.substring(1);
              return FilterChip(
                label: Text(label, style: TextStyle(fontSize: 12, color: selected ? Colors.white : Colors.grey[700])),
                selected: selected,
                onSelected: (_) => setState(() => _filterRole = r),
                selectedColor: Colors.blue,
                backgroundColor: Colors.grey[100],
                materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                visualDensity: VisualDensity.compact,
              );
            },
          ),
        ),
      ],
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.people_outline, size: 64, color: Colors.grey[400]),
          const SizedBox(height: 16),
          Text('No users found', style: TextStyle(fontSize: 16, color: Colors.grey[600])),
          const SizedBox(height: 8),
          Text('Try adjusting your filters', style: TextStyle(fontSize: 13, color: Colors.grey[500])),
        ],
      ),
    );
  }

  Widget _buildUsersList(List<Map<String, dynamic>> users) {
    return ListView.builder(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      itemCount: users.length,
      itemBuilder: (ctx, i) => _buildUserCard(users[i]),
    );
  }

  Widget _buildUserCard(Map<String, dynamic> user) {
    final role = user['role']?.toString() ?? user['user_type']?.toString() ?? 'customer';
    final roleColor = _roleColor(role);
    final name = user['name']?.toString() ?? 'N/A';
    final email = user['email']?.toString() ?? '';
    final phone = user['phone']?.toString() ?? '';
    final initials = name.split(' ').map((w) => w.isNotEmpty ? w[0].toUpperCase() : '').join().substring(0, 2.clamp(0, name.length));

    return Card(
      margin: const EdgeInsets.only(bottom: 10),
      elevation: 1,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Row(
          children: [
            CircleAvatar(
              radius: 22,
              backgroundColor: roleColor.withValues(alpha: 0.15),
              child: Text(initials, style: TextStyle(fontWeight: FontWeight.bold, color: roleColor, fontSize: 14)),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(name, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 15),
                          overflow: TextOverflow.ellipsis),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: roleColor.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(15),
                          border: Border.all(color: roleColor.withValues(alpha: 0.3)),
                        ),
                        child: Text(role[0].toUpperCase() + role.substring(1),
                          style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: roleColor)),
                      ),
                    ],
                  ),
                  if (email.isNotEmpty) ...[
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        Icon(Icons.email_outlined, size: 14, color: Colors.grey[500]),
                        const SizedBox(width: 6),
                        Expanded(child: Text(email,
                          style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                          overflow: TextOverflow.ellipsis)),
                      ],
                    ),
                  ],
                  if (phone.isNotEmpty) ...[
                    const SizedBox(height: 2),
                    Row(
                      children: [
                        Icon(Icons.phone_outlined, size: 14, color: Colors.grey[500]),
                        const SizedBox(width: 6),
                        Text(phone, style: TextStyle(fontSize: 12, color: Colors.grey[600])),
                      ],
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Color _roleColor(String role) {
    switch (role) {
      case 'admin': return Colors.red;
      case 'agent': return Colors.blue;
      case 'associate': return Colors.orange;
      case 'employee': return Colors.purple;
      case 'customer': return Colors.green;
      default: return Colors.grey;
    }
  }
}

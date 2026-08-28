import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/providers/auth_provider.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/app_theme.dart';

/// Employee Profile Page
class EmployeeProfilePage extends ConsumerWidget {
  const EmployeeProfilePage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(authProvider);

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          // Avatar
          Container(
            width: 80,
            height: 80,
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
              ),
              shape: BoxShape.circle,
            ),
            child: Center(
              child: Text(
                (user?.name ?? 'E')[0].toUpperCase(),
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 30,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
          ),
          const SizedBox(height: 12),
          Text(
            user?.name ?? 'Employee',
            style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 4),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
            decoration: BoxDecoration(
              color: AppTheme.primaryColor.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Text(
              (user?.role ?? 'employee').toUpperCase(),
              style: const TextStyle(
                fontSize: 10,
                fontWeight: FontWeight.bold,
                color: AppTheme.primaryColor,
              ),
            ),
          ),
          const SizedBox(height: 24),

          // Profile Options
          _buildOption(Icons.person_outline, 'Edit Profile', () {}),
          _buildOption(Icons.lock_outline, 'Change Password', () {}),
          _buildOption(Icons.history, 'Attendance History', () {}),
          _buildOption(Icons.description_outlined, 'Payslips', () {}),
          _buildOption(Icons.help_outline, 'Help & Support', () {}),
          const SizedBox(height: 16),

          // Logout
          SizedBox(
            width: double.infinity,
            child: OutlinedButton.icon(
              onPressed: () async {
                await ref.read(authProvider.notifier).logout();
                AuthBridge.instance.currentUser.value = null;
                if (context.mounted) context.go('/login');
              },
              icon: const Icon(Icons.logout, color: Colors.red),
              label: const Text('Logout', style: TextStyle(color: Colors.red)),
              style: OutlinedButton.styleFrom(
                side: const BorderSide(color: Colors.red),
                padding: const EdgeInsets.symmetric(vertical: 12),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildOption(IconData icon, String label, VoidCallback onTap) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        leading: Icon(icon, color: AppTheme.primaryColor, size: 20),
        title: Text(label, style: const TextStyle(fontSize: 14)),
        trailing: Icon(
          Icons.chevron_right,
          color: Colors.grey.shade400,
          size: 20,
        ),
        onTap: onTap,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        tileColor: Colors.white,
        contentPadding: const EdgeInsets.symmetric(horizontal: 14),
      ),
    );
  }
}

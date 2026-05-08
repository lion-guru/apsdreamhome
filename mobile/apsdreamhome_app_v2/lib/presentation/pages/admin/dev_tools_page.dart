import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/utils/logger.dart';
import '../../../data/services/firebase_seeder.dart';

/// Developer Tools Page
/// For admins to seed demo data, clear data, etc.
class DevToolsPage extends ConsumerWidget {
  const DevToolsPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final seeder = FirebaseSeeder();

    return Scaffold(
      appBar: AppBar(
        title: const Text('Developer Tools'),
        backgroundColor: Colors.purple,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Warning Card
            Card(
              color: Colors.orange.shade50,
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Row(
                  children: [
                    Icon(Icons.warning, color: Colors.orange.shade700),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        'These tools are for development only. Use with caution on production data.',
                        style: TextStyle(color: Colors.orange.shade700),
                      ),
                    ),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 24),

            // Data Seeding Section
            Text(
              'Data Seeding',
              style: Theme.of(context).textTheme.titleLarge,
            ),
            const SizedBox(height: 8),
            const Text(
              'Seed demo data for testing purposes.',
              style: TextStyle(color: Colors.grey),
            ),
            const SizedBox(height: 16),

            // Seed All Data
            _buildActionCard(
              context,
              icon: Icons.playlist_add,
              title: 'Seed All Demo Data',
              subtitle: 'Creates colonies, users, leads, listings, agents',
              color: Colors.blue,
              onTap: () => _showConfirmationDialog(
                context,
                title: 'Seed All Data?',
                message:
                    'This will create demo data in your Firebase database. Existing data will be preserved.',
                onConfirm: () async {
                  try {
                    await seeder.seedAllData();
                    _showSuccess(context, 'All demo data seeded successfully!');
                  } catch (e) {
                    _showError(context, 'Failed to seed data: $e');
                  }
                },
              ),
            ),

            const SizedBox(height: 12),

            // Seed Specific Data
            _buildActionCard(
              context,
              icon: Icons.add_circle_outline,
              title: 'Seed Specific Data',
              subtitle: 'Choose which data to seed',
              color: Colors.green,
              onTap: () => _showSpecificSeedingDialog(context, seeder),
            ),

            const SizedBox(height: 32),

            // Data Management Section
            Text(
              'Data Management',
              style: Theme.of(context).textTheme.titleLarge,
            ),
            const SizedBox(height: 8),
            const Text(
              'Manage existing data. Be careful!',
              style: TextStyle(color: Colors.grey),
            ),
            const SizedBox(height: 16),

            // Clear All Data
            _buildActionCard(
              context,
              icon: Icons.delete_forever,
              title: 'Clear All Demo Data',
              subtitle: '⚠️ Deletes all seeded data (colonies, leads, etc.)',
              color: Colors.red,
              onTap: () => _showConfirmationDialog(
                context,
                title: '⚠️ Clear All Data?',
                message:
                    'This will DELETE all demo data from Firebase. This action cannot be undone!',
                isDestructive: true,
                onConfirm: () async {
                  try {
                    await seeder.clearAllData();
                    _showSuccess(context, 'All demo data cleared!');
                  } catch (e) {
                    _showError(context, 'Failed to clear data: $e');
                  }
                },
              ),
            ),

            const SizedBox(height: 32),

            // App Info Section
            Text(
              'App Information',
              style: Theme.of(context).textTheme.titleLarge,
            ),
            const SizedBox(height: 16),

            _buildInfoCard(
              context,
              icon: Icons.info,
              title: 'Build Info',
              content: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildInfoRow('Version', '2.0.0'),
                  _buildInfoRow('Build', 'Release'),
                  _buildInfoRow('Flutter', '3.41.6'),
                  _buildInfoRow('Dart', '3.11.4'),
                ],
              ),
            ),

            const SizedBox(height: 12),

            _buildInfoCard(
              context,
              icon: Icons.storage,
              title: 'Firebase Collections',
              content: const Wrap(
                spacing: 8,
                runSpacing: 8,
                children: [
                  Chip(label: Text('users')),
                  Chip(label: Text('colonies')),
                  Chip(label: Text('plots')),
                  Chip(label: Text('bookings')),
                  Chip(label: Text('commissions')),
                  Chip(label: Text('leads')),
                  Chip(label: Text('payouts')),
                  Chip(label: Text('property_listings')),
                  Chip(label: Text('emi_agents')),
                  Chip(label: Text('daily_callers')),
                ],
              ),
            ),

            const SizedBox(height: 32),

            // Testing Section
            Text(
              'Testing Tools',
              style: Theme.of(context).textTheme.titleLarge,
            ),
            const SizedBox(height: 16),

            _buildActionCard(
              context,
              icon: Icons.bug_report,
              title: 'Test Error Reporting',
              subtitle: 'Simulate an error to test crash handling',
              color: Colors.orange,
              onTap: () {
                try {
                  throw Exception('Test error from DevTools');
                } catch (e, stackTrace) {
                  AppLogger.error('Test error', e, stackTrace);
                  _showSuccess(context, 'Test error logged! Check console.');
                }
              },
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildActionCard(
    BuildContext context, {
    required IconData icon,
    required String title,
    required String subtitle,
    required Color color,
    required VoidCallback onTap,
  }) {
    return Card(
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(icon, color: color, size: 28),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      subtitle,
                      style: TextStyle(
                        color: Colors.grey.shade600,
                        fontSize: 13,
                      ),
                    ),
                  ],
                ),
              ),
              Icon(Icons.chevron_right, color: Colors.grey.shade400),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildInfoCard(
    BuildContext context, {
    required IconData icon,
    required String title,
    required Widget content,
  }) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(icon, size: 20, color: Colors.grey),
                const SizedBox(width: 8),
                Text(
                  title,
                  style: const TextStyle(
                    fontWeight: FontWeight.bold,
                    color: Colors.grey,
                  ),
                ),
              ],
            ),
            const Divider(height: 24),
            content,
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(color: Colors.grey)),
          Text(value, style: const TextStyle(fontWeight: FontWeight.w500)),
        ],
      ),
    );
  }

  void _showConfirmationDialog(
    BuildContext context, {
    required String title,
    required String message,
    required VoidCallback onConfirm,
    bool isDestructive = false,
  }) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(title),
        content: Text(message),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              onConfirm();
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: isDestructive ? Colors.red : null,
              foregroundColor: isDestructive ? Colors.white : null,
            ),
            child: Text(isDestructive ? 'Delete' : 'Confirm'),
          ),
        ],
      ),
    );
  }

  void _showSpecificSeedingDialog(BuildContext context, FirebaseSeeder seeder) {
    final options = {
      'Colonies': false,
      'Users': false,
      'Leads': false,
      'Property Listings': false,
      'EMI Agents': false,
      'Daily Callers': false,
      'EMI Rules': false,
    };

    showDialog(
      context: context,
      builder: (context) => StatefulBuilder(
        builder: (context, setState) => AlertDialog(
          title: const Text('Select Data to Seed'),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: options.keys.map((key) {
                return CheckboxListTile(
                  title: Text(key),
                  value: options[key],
                  onChanged: (value) {
                    setState(() {
                      options[key] = value ?? false;
                    });
                  },
                );
              }).toList(),
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Cancel'),
            ),
            ElevatedButton(
              onPressed: () {
                Navigator.pop(context);
                seeder.seedSpecific(
                  colonies: options['Colonies']!,
                  users: options['Users']!,
                  leads: options['Leads']!,
                  propertyListings: options['Property Listings']!,
                  emiAgents: options['EMI Agents']!,
                  dailyCallers: options['Daily Callers']!,
                  emiRules: options['EMI Rules']!,
                );
                _showSuccess(context, 'Selected data seeded!');
              },
              child: const Text('Seed Selected'),
            ),
          ],
        ),
      ),
    );
  }

  void _showSuccess(BuildContext context, String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.check_circle, color: Colors.white),
            const SizedBox(width: 8),
            Text(message),
          ],
        ),
        backgroundColor: Colors.green,
      ),
    );
  }

  void _showError(BuildContext context, String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.error, color: Colors.white),
            const SizedBox(width: 8),
            Expanded(child: Text(message)),
          ],
        ),
        backgroundColor: Colors.red,
      ),
    );
  }
}

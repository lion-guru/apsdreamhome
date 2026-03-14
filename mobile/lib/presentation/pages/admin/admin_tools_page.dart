import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/app_constants.dart';
import '../../core/theme/app_theme.dart';
import '../../data/models/property_model.dart';
import '../providers/property_provider.dart';
import '../widgets/glass_card.dart';
import '../widgets/common_widgets.dart';

class AdminToolsPage extends ConsumerWidget {
  const AdminToolsPage({super.key});
  
  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final connectivity = ref.watch(connectivityProvider);
    
    return Scaffold(
      appBar: AppBar(
        title: const Text('Admin Tools'),
        backgroundColor: Colors.red,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(AppConstants.defaultPadding),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Admin Header
            GlassCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Icon(
                        Icons.admin_panel_settings,
                        size: 32,
                        color: Colors.red,
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Admin Control Panel',
                              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                                fontWeight: FontWeight.bold,
                                color: Colors.red,
                              ),
                            ),
                            Text(
                              'Manage properties and system settings',
                              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                                color: Colors.grey.shade600,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            
            const SizedBox(height: 24),
            
            // Quick Stats
            _buildQuickStats(context, ref),
            
            const SizedBox(height: 24),
            
            // Plot Management Tools
            Text(
              'Plot Management',
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),
            
            GridView.count(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              crossAxisCount: 2,
              crossAxisSpacing: 12,
              mainAxisSpacing: 12,
              childAspectRatio: 1.2,
              children: [
                _buildAdminToolCard(
                  context,
                  'Update Status',
                  'Change plot status',
                  Icons.edit,
                  Colors.blue,
                  connectivity.value ?? false ? _showStatusUpdateDialog : null,
                ),
                _buildAdminToolCard(
                  context,
                  'Bulk Update',
                  'Update multiple plots',
                  Icons.batch_prediction,
                  Colors.green,
                  connectivity.value ?? false ? _showBulkUpdateDialog : null,
                ),
                _buildAdminToolCard(
                  context,
                  'Add Property',
                  'Add new property',
                  Icons.add_circle,
                  AppTheme.primaryColor,
                  connectivity.value ?? false ? _showAddPropertyDialog : null,
                ),
                _buildAdminToolCard(
                  context,
                  'Delete Property',
                  'Remove property',
                  Icons.delete,
                  Colors.red,
                  connectivity.value ?? false ? _showDeletePropertyDialog : null,
                ),
              ],
            ),
            
            const SizedBox(height: 24),
            
            // System Tools
            Text(
              'System Tools',
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),
            
            GlassCard(
              child: Column(
                children: [
                  _buildSystemTool(
                    context,
                    'Force Sync All',
                    'Synchronize all pending data',
                    Icons.sync,
                    Colors.blue,
                    connectivity.value ?? false ? _forceSyncAll : null,
                  ),
                  const Divider(height: 1),
                  _buildSystemTool(
                    context,
                    'Clear Sync Queue',
                    'Remove all pending sync items',
                    Icons.clear_all,
                    Colors.orange,
                    _clearSyncQueue,
                  ),
                  const Divider(height: 1),
                  _buildSystemTool(
                    context,
                    'Export Data',
                    'Export database to CSV',
                    Icons.download,
                    Colors.green,
                    _exportData,
                  ),
                  const Divider(height: 1),
                  _buildSystemTool(
                    context,
                    'System Health',
                    'Check system status',
                    Icons.health_and_safety,
                    Colors.purple,
                    _showSystemHealth,
                  ),
                ],
              ),
            ),
            
            const SizedBox(height: 24),
            
            // Recent Activity Log
            Text(
              'Recent Activity',
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),
            
            GlassCard(
              child: _buildActivityLog(context),
            ),
            
            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }
  
  Widget _buildQuickStats(BuildContext context, WidgetRef ref) {
    final propertiesAsync = ref.watch(propertiesProvider);
    
    return GlassCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Quick Statistics',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 16),
          
          propertiesAsync.when(
            data: (properties) {
              final available = properties.where((p) => p.isAvailable).length;
              final booked = properties.where((p) => p.isBooked).length;
              final sold = properties.where((p) => p.isSold).length;
              final onHold = properties.where((p) => p.isOnHold).length;
              
              return Row(
                children: [
                  Expanded(
                    child: _buildStatItem('Available', available, Colors.green),
                  ),
                  Expanded(
                    child: _buildStatItem('Booked', booked, Colors.orange),
                  ),
                  Expanded(
                    child: _buildStatItem('Sold', sold, Colors.red),
                  ),
                  Expanded(
                    child: _buildStatItem('On Hold', onHold, Colors.grey),
                  ),
                ],
              );
            },
            loading: () => const Center(child: CircularProgressIndicator()),
            error: (error, stack) => Text('Error: ${error.toString()}'),
          ),
        ],
      ),
    );
  }
  
  Widget _buildStatItem(String label, int count, Color color) {
    return Column(
      children: [
        Container(
          width: 40,
          height: 40,
          decoration: BoxDecoration(
            color: color.withOpacity(0.1),
            borderRadius: BorderRadius.circular(20),
          ),
          child: Center(
            child: Text(
              count.toString(),
              style: TextStyle(
                color: color,
                fontWeight: FontWeight.bold,
                fontSize: 16,
              ),
            ),
          ),
        ),
        const SizedBox(height: 8),
        Text(
          label,
          style: TextStyle(
            fontSize: 12,
            color: Colors.grey.shade600,
            fontWeight: FontWeight.w600,
          ),
        ),
      ],
    );
  }
  
  Widget _buildAdminToolCard(
    BuildContext context,
    String title,
    String subtitle,
    IconData icon,
    Color color,
    VoidCallback? onTap,
  ) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: GlassCard(
        padding: const EdgeInsets.all(16),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: color.withOpacity(0.1),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(
                icon,
                size: 28,
                color: onTap != null ? color : Colors.grey,
              ),
            ),
            const SizedBox(height: 12),
            Text(
              title,
              style: Theme.of(context).textTheme.titleSmall?.copyWith(
                fontWeight: FontWeight.bold,
                color: onTap != null ? null : Colors.grey,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 4),
            Text(
              subtitle,
              style: Theme.of(context).textTheme.bodySmall?.copyWith(
                color: Colors.grey.shade600,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
  
  Widget _buildSystemTool(
    BuildContext context,
    String title,
    String subtitle,
    IconData icon,
    Color color,
    VoidCallback? onTap,
  ) {
    return ListTile(
      leading: Container(
        padding: const EdgeInsets.all(8),
        decoration: BoxDecoration(
          color: color.withOpacity(0.1),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Icon(icon, color: onTap != null ? color : Colors.grey),
      ),
      title: Text(title),
      subtitle: Text(subtitle),
      trailing: onTap != null
          ? Icon(Icons.arrow_forward_ios, color: Colors.grey.shade400)
          : null,
      onTap: onTap,
    );
  }
  
  Widget _buildActivityLog(BuildContext context) {
    final activities = [
      {'action': 'Property A-12 status changed to Sold', 'time': '2 mins ago', 'type': 'success'},
      {'action': 'Bulk update: 5 properties marked as Available', 'time': '1 hour ago', 'type': 'info'},
      {'action': 'New property added: Plot B-05', 'time': '3 hours ago', 'type': 'success'},
      {'action': 'Sync completed for 23 items', 'time': '5 hours ago', 'type': 'info'},
    ];
    
    return Column(
      children: activities.map((activity) {
        return Padding(
          padding: const EdgeInsets.symmetric(vertical: 8),
          child: Row(
            children: [
              Container(
                width: 8,
                height: 8,
                decoration: BoxDecoration(
                  color: activity['type'] == 'success' ? Colors.green : Colors.blue,
                  borderRadius: BorderRadius.circular(4),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  activity['action'] as String,
                  style: Theme.of(context).textTheme.bodySmall,
                ),
              ),
              Text(
                activity['time'] as String,
                style: Theme.of(context).textTheme.bodySmall?.copyWith(
                  color: Colors.grey.shade500,
                ),
              ),
            ],
          ),
        );
      }).toList(),
    );
  }
  
  void _showStatusUpdateDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Update Property Status'),
        content: const Text('Select a property to update its status'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.of(context).pop();
              // TODO: Navigate to property selection
            },
            child: const Text('Select Property'),
          ),
        ],
      ),
    );
  }
  
  void _showBulkUpdateDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Bulk Update Properties'),
        content: const Text('Update multiple properties at once'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.of(context).pop();
              // TODO: Implement bulk update
            },
            child: const Text('Start Bulk Update'),
          ),
        ],
      ),
    );
  }
  
  void _showAddPropertyDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Add New Property'),
        content: const Text('Add a new property to the system'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.of(context).pop();
              // TODO: Navigate to add property form
            },
            child: const Text('Add Property'),
          ),
        ],
      ),
    );
  }
  
  void _showDeletePropertyDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Delete Property'),
        content: const Text('Remove a property from the system'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.of(context).pop();
              // TODO: Navigate to property selection for deletion
            },
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
            child: const Text('Select Property'),
          ),
        ],
      ),
    );
  }
  
  void _forceSyncAll(BuildContext context) async {
    // TODO: Implement force sync all
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Force sync initiated')),
    );
  }
  
  void _clearSyncQueue(BuildContext context) async {
    // TODO: Implement clear sync queue
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Sync queue cleared')),
    );
  }
  
  void _exportData(BuildContext context) async {
    // TODO: Implement data export
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Data export started')),
    );
  }
  
  void _showSystemHealth(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('System Health'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildHealthItem('Database', 'Healthy', Colors.green),
            _buildHealthItem('API Connection', 'Connected', Colors.green),
            _buildHealthItem('Sync Queue', '3 items pending', Colors.orange),
            _buildHealthItem('Storage', '45 MB used', Colors.blue),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Close'),
          ),
        ],
      ),
    );
  }
  
  Widget _buildHealthItem(String label, String status, Color color) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        children: [
          Icon(
            Icons.circle,
            size: 12,
            color: color,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(label),
          ),
          Text(
            status,
            style: TextStyle(
              color: color,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }
}

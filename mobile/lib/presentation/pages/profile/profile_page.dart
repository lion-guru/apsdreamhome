import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:image_picker/image_picker.dart';
import '../../core/constants/app_constants.dart';
import '../../core/theme/app_theme.dart';
import '../providers/auth_provider.dart';
import '../widgets/glass_card.dart';
import '../widgets/common_widgets.dart';

class ProfilePage extends ConsumerWidget {
  const ProfilePage({super.key});
  
  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(authProvider).value;
    final connectivity = ref.watch(connectivityProvider);
    
    return Scaffold(
      appBar: AppBar(
        title: const Text('Profile'),
        actions: [
          IconButton(
            onPressed: connectivity.value ?? false ? _editProfile : null,
            icon: const Icon(Icons.edit),
          ),
        ],
      ),
      body: user == null
          ? const EmptyStateWidget(
              title: 'No User Data',
              subtitle: 'Please log in to view your profile',
            )
          : SingleChildScrollView(
              padding: const EdgeInsets.all(AppConstants.defaultPadding),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Profile Header
                  _buildProfileHeader(context, user),
                  
                  const SizedBox(height: 24),
                  
                  // User Information
                  _buildUserInfo(context, user),
                  
                  const SizedBox(height: 24),
                  
                  // Rank Information
                  _buildRankInfo(context, user),
                  
                  const SizedBox(height: 24),
                  
                  // Quick Actions
                  _buildQuickActions(context, user),
                  
                  const SizedBox(height: 24),
                  
                  // App Information
                  _buildAppInfo(context),
                  
                  const SizedBox(height: 24),
                  
                  // Logout Button
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      onPressed: () => _showLogoutDialog(context, ref),
                      icon: const Icon(Icons.logout),
                      label: const Text('Logout'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.red,
                        foregroundColor: Colors.white,
                      ),
                    ),
                  ),
                ],
              ),
            ),
    );
  }
  
  Widget _buildProfileHeader(BuildContext context, user) {
    return GlassCard(
      child: Column(
        children: [
          Row(
            children: [
              // Avatar
              Container(
                width: 80,
                height: 80,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: AppTheme.primaryColor.withOpacity(0.1),
                ),
                child: user.avatar != null
                    ? ClipOval(
                        child: CachedNetworkImage(
                          imageUrl: user.avatar!,
                          fit: BoxFit.cover,
                          placeholder: (context, url) => Center(
                            child: CircularProgressIndicator(
                              color: AppTheme.primaryColor,
                            ),
                          ),
                          errorWidget: (context, url, error) => Icon(
                            Icons.person,
                            size: 40,
                            color: AppTheme.primaryColor,
                          ),
                        ),
                      )
                    : Icon(
                        Icons.person,
                        size: 40,
                        color: AppTheme.primaryColor,
                      ),
              ),
              
              const SizedBox(width: 20),
              
              // Name and Rank
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      user.name,
                      style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                      decoration: BoxDecoration(
                        color: AppTheme.accentColor.withOpacity(0.2),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        user.rank,
                        style: TextStyle(
                          color: AppTheme.primaryColor,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
  
  Widget _buildUserInfo(BuildContext context, user) {
    return GlassCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Contact Information',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 16),
          
          _buildInfoRow(
            context,
            'Email',
            user.email,
            Icons.email,
          ),
          
          const SizedBox(height: 12),
          
          _buildInfoRow(
            context,
            'Phone',
            user.phone ?? 'Not provided',
            Icons.phone,
          ),
          
          const SizedBox(height: 12),
          
          _buildInfoRow(
            context,
            'User ID',
            user.userId,
            Icons.badge,
          ),
        ],
      ),
    );
  }
  
  Widget _buildRankInfo(BuildContext context, user) {
    return GlassCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'MLM Information',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 16),
          
          _buildInfoRow(
            context,
            'Commission Rate',
            '${user.commissionRate.toStringAsFixed(1)}%',
            Icons.percent,
          ),
          
          const SizedBox(height: 12),
          
          _buildInfoRow(
            context,
            'Target',
            _formatTarget(user.target),
            Icons.trending_up,
          ),
          
          const SizedBox(height: 12),
          
          _buildInfoRow(
            context,
            'Member Since',
            _formatDate(user.createdAt),
            Icons.calendar_today,
          ),
        ],
      ),
    );
  }
  
  Widget _buildQuickActions(BuildContext context, user) {
    return GlassCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Quick Actions',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 16),
          
          Row(
            children: [
              Expanded(
                child: _buildActionButton(
                  context,
                  'Call Support',
                  Icons.call,
                  Colors.green,
                  () => _callSupport(),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _buildActionButton(
                  context,
                  'Share Profile',
                  Icons.share,
                  AppTheme.primaryColor,
                  () => _shareProfile(user),
                ),
              ),
            ],
          ),
          
          const SizedBox(height: 12),
          
          Row(
            children: [
              Expanded(
                child: _buildActionButton(
                  context,
                  'Change Photo',
                  Icons.camera_alt,
                  AppTheme.accentColor,
                  () => _changePhoto(),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _buildActionButton(
                  context,
                  'Settings',
                  Icons.settings,
                  Colors.grey,
                  () => _openSettings(),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
  
  Widget _buildAppInfo(BuildContext context) {
    return GlassCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'App Information',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 16),
          
          _buildInfoRow(
            context,
            'App Name',
            AppConstants.appName,
            Icons.home,
          ),
          
          const SizedBox(height: 12),
          
          _buildInfoRow(
            context,
            'Version',
            AppConstants.version,
            Icons.info,
          ),
          
          const SizedBox(height: 12),
          
          _buildInfoRow(
            context,
            'Support',
            AppConstants.supportPhone,
            Icons.support_agent,
          ),
        ],
      ),
    );
  }
  
  Widget _buildInfoRow(
    BuildContext context,
    String label,
    String value,
    IconData icon,
  ) {
    return Row(
      children: [
        Icon(
          icon,
          size: 20,
          color: Colors.grey.shade600,
        ),
        const SizedBox(width: 12),
        Text(
          '$label:',
          style: Theme.of(context).textTheme.bodyMedium?.copyWith(
            color: Colors.grey.shade600,
            fontWeight: FontWeight.w500,
          ),
        ),
        const SizedBox(width: 8),
        Expanded(
          child: Text(
            value,
            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
              fontWeight: FontWeight.w600,
            ),
          ),
        ),
      ],
    );
  }
  
  Widget _buildActionButton(
    BuildContext context,
    String label,
    IconData icon,
    Color color,
    VoidCallback onTap,
  ) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: color.withOpacity(0.1),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Column(
          children: [
            Icon(
              icon,
              size: 24,
              color: color,
            ),
            const SizedBox(height: 8),
            Text(
              label,
              style: TextStyle(
                color: color,
                fontWeight: FontWeight.w600,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
  
  String _formatTarget(double target) {
    if (target >= 10000000) {
      return '${(target / 10000000).toStringAsFixed(1)} Cr';
    } else if (target >= 100000) {
      return '${(target / 100000).toStringAsFixed(0)} L';
    } else {
      return target.toStringAsFixed(0);
    }
  }
  
  String _formatDate(String dateString) {
    final date = DateTime.parse(dateString);
    return '${date.day}/${date.month}/${date.year}';
  }
  
  void _editProfile() {
    // TODO: Implement edit profile functionality
  }
  
  void _callSupport() async {
    final Uri phoneUri = Uri(
      scheme: 'tel',
      path: AppConstants.supportPhone,
    );
    
    if (await canLaunchUrl(phoneUri)) {
      await launchUrl(phoneUri);
    }
  }
  
  void _shareProfile(user) {
    // TODO: Implement share profile functionality
  }
  
  void _changePhoto() async {
    final ImagePicker picker = ImagePicker();
    final XFile? image = await picker.pickImage(source: ImageSource.gallery);
    // TODO: Handle image upload
  }
  
  void _openSettings() {
    // TODO: Implement settings functionality
  }
  
  void _showLogoutDialog(BuildContext context, WidgetRef ref) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Logout'),
        content: const Text('Are you sure you want to logout?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.of(context).pop();
              ref.read(authProvider.notifier).logout();
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red,
              foregroundColor: Colors.white,
            ),
            child: const Text('Logout'),
          ),
        ],
      ),
    );
  }
}

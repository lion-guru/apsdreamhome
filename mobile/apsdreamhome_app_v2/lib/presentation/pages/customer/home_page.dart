import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/theme/app_theme.dart';
import '../../../data/services/colony_service.dart';
import '../../../data/models/colony_model.dart';
import '../../widgets/app_widgets.dart';
import '../../widgets/ai/floating_ai_button.dart';
import '../../widgets/glass_card.dart';

class HomePage extends ConsumerWidget {
  const HomePage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final coloniesAsync = ref.watch(coloniesProvider);
    
    return Scaffold(
      floatingActionButton: const FloatingAIButton(),
      floatingActionButtonLocation: FloatingActionButtonLocation.endFloat,
      body: GradientBackground(
        child: SafeArea(
          child: CustomScrollView(
            slivers: [
            // App Bar
            SliverToBoxAdapter(
              child: _buildAppBar(context),
            ),
            
            // Banner
            SliverToBoxAdapter(
              child: _buildBanner(context),
            ),
            
            // Quick Actions
            SliverToBoxAdapter(
              child: _buildQuickActions(context),
            ),
            
            // Featured Colonies Header
            SliverToBoxAdapter(
              child: AppWidgets.sectionHeader(
                title: 'Featured Colonies',
                subtitle: 'Explore our premium developments',
                onSeeAll: () => context.push('/colonies'),
              ),
            ),
            
            // Colonies List
            coloniesAsync.when(
              data: (colonies) {
                if (colonies.isEmpty) {
                  return SliverToBoxAdapter(
                    child: AppWidgets.emptyState(
                      title: 'No Colonies Available',
                      subtitle: 'Check back later for new developments',
                    ),
                  );
                }
                
                return SliverList(
                  delegate: SliverChildBuilderDelegate(
                    (context, index) {
                      final colony = colonies[index];
                      return _buildColonyCard(context, colony);
                    },
                    childCount: colonies.length > 5 ? 5 : colonies.length,
                  ),
                );
              },
              loading: () => SliverToBoxAdapter(
                child: AppWidgets.shimmerLoading(
                  child: Container(
                    height: 200,
                    margin: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                    ),
                  ),
                ),
              ),
              error: (error, stack) => SliverToBoxAdapter(
                child: AppWidgets.errorWidget(
                  message: error.toString(),
                  onRetry: () => ref.refresh(coloniesProvider),
                ),
              ),
            ),
            
            // Why Choose Us
            SliverToBoxAdapter(
              child: _buildWhyChooseUs(context),
            ),
            
            // Bottom Padding
            const SliverToBoxAdapter(
              child: SizedBox(height: 32),
            ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildAppBar(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Row(
        children: [
          // Logo
          Container(
            width: 50,
            height: 50,
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
              ),
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(
              Icons.home_work,
              color: Colors.white,
              size: 28,
            ),
          ),
          
          const SizedBox(width: 12),
          
          // Title
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  AppConstants.appName,
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
                ),
                Text(
                  'Premium Plots & Colonies',
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: Colors.grey,
                  ),
                ),
              ],
            ),
          ),
          
          // Notification Icon
          IconButton(
            onPressed: () => context.push('/notifications'),
            icon: Stack(
              children: [
                const Icon(Icons.notifications_outlined),
                Positioned(
                  right: 0,
                  top: 0,
                  child: Container(
                    width: 8,
                    height: 8,
                    decoration: const BoxDecoration(
                      color: Colors.red,
                      shape: BoxShape.circle,
                    ),
                  ),
                ),
              ],
            ),
          ),
          
          // Profile Icon
          IconButton(
            onPressed: () => context.push('/profile'),
            icon: const Icon(Icons.person_outline),
          ),
        ],
      ),
    );
  }

  Widget _buildBanner(BuildContext context) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      height: 180,
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: AppTheme.primaryColor.withValues(alpha: 0.3),
            blurRadius: 15,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Stack(
        children: [
          // Background Pattern
          Positioned(
            right: -30,
            top: -30,
            child: Container(
              width: 150,
              height: 150,
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.1),
                shape: BoxShape.circle,
              ),
            ),
          ),
          
          // Content
          Padding(
            padding: const EdgeInsets.all(24),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: const Text(
                    'NEW LAUNCH',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 10,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
                
                const SizedBox(height: 12),
                
                const Text(
                  'Suryoday Heights',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                
                const SizedBox(height: 4),
                
                const Text(
                  'Premium Plots in Gorakhpur\nStarting from ₹3,000/sqft',
                  style: TextStyle(
                    color: Colors.white70,
                    fontSize: 14,
                  ),
                ),
                
                const SizedBox(height: 16),
                
                ElevatedButton(
                  onPressed: () => context.push('/colonies'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.white,
                    foregroundColor: AppTheme.primaryColor,
                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                  ),
                  child: const Text(
                    'Explore Now',
                    style: TextStyle(fontWeight: FontWeight.bold),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildQuickActions(BuildContext context) {
    final actions = [
      {
        'icon': Icons.map_outlined,
        'label': 'Colonies',
        'route': '/colonies',
      },
      {
        'icon': Icons.bookmark_border,
        'label': 'My Bookings',
        'route': '/my-bookings',
      },
      {
        'icon': Icons.description_outlined,
        'label': 'Documents',
        'route': '/documents',
      },
      {
        'icon': Icons.support_agent_outlined,
        'label': 'Support',
        'route': null,
      },
    ];
    
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceAround,
        children: actions.map((action) {
          return GestureDetector(
            onTap: () {
              if (action['route'] != null) {
                context.push(action['route'] as String);
              } else {
                // Show coming soon or support dialog
                AppWidgets.showInfoSnackBar(context, 'Coming Soon!');
              }
            },
            child: Column(
              children: [
                Container(
                  width: 60,
                  height: 60,
                  decoration: BoxDecoration(
                    color: AppTheme.primaryColor.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Icon(
                    action['icon'] as IconData,
                    color: AppTheme.primaryColor,
                    size: 28,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  action['label'] as String,
                  style: const TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          );
        }).toList(),
      ),
    );
  }

  Widget _buildColonyCard(BuildContext context, ColonyModel colony) {
    return AppWidgets.customCard(
      onTap: () => context.push('/colony/${colony.id}'),
      child: Row(
        children: [
          // Image
          ClipRRect(
            borderRadius: BorderRadius.circular(12),
            child: Container(
              width: 100,
              height: 100,
              color: Colors.grey.shade200,
              child: colony.images != null && colony.images!.isNotEmpty
                  ? Image.network(
                      colony.images!.first,
                      fit: BoxFit.cover,
                      errorBuilder: (context, error, stackTrace) {
                        return const Icon(
                          Icons.image_not_supported_outlined,
                          color: Colors.grey,
                        );
                      },
                    )
                  : const Icon(
                      Icons.home_work_outlined,
                      color: Colors.grey,
                      size: 40,
                    ),
            ),
          ),
          
          const SizedBox(width: 16),
          
          // Info
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  colony.name,
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
                ),
                
                const SizedBox(height: 4),
                
                Row(
                  children: [
                    Icon(
                      Icons.location_on_outlined,
                      size: 14,
                      color: Colors.grey.shade500,
                    ),
                    const SizedBox(width: 4),
                    Expanded(
                      child: Text(
                        '${colony.location}, ${colony.district}',
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.grey.shade500,
                        ),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
                
                const SizedBox(height: 8),
                
                Row(
                  children: [
                    AppWidgets.statusBadge(
                      status: colony.status,
                      color: colony.isActive
                          ? AppTheme.successColor
                          : AppTheme.warningColor,
                    ),
                    const SizedBox(width: 8),
                    Text(
                      '${colony.availablePlots} plots available',
                      style: TextStyle(
                        fontSize: 11,
                        color: Colors.grey.shade600,
                      ),
                    ),
                  ],
                ),
                
                const SizedBox(height: 8),
                
                AppWidgets.priceTag(
                  amount: colony.pricePerSqft,
                  prefix: '₹',
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.primaryColor,
                  ),
                ),
              ],
            ),
          ),
          
          // Arrow
          Icon(
            Icons.arrow_forward_ios,
            size: 16,
            color: Colors.grey.shade400,
          ),
        ],
      ),
    );
  }

  Widget _buildWhyChooseUs(BuildContext context) {
    final features = [
      {
        'icon': Icons.verified_outlined,
        'title': 'RERA Registered',
        'description': 'All projects are RERA approved',
      },
      {
        'icon': Icons.security_outlined,
        'title': 'Secure Investment',
        'description': 'Legal verification of all plots',
      },
      {
        'icon': Icons.location_city_outlined,
        'title': 'Prime Locations',
        'description': 'Best locations in Gorakhpur & beyond',
      },
      {
        'icon': Icons.support_agent_outlined,
        'title': '24/7 Support',
        'description': 'Dedicated customer service',
      },
    ];
    
    return Column(
      children: [
        AppWidgets.sectionHeader(
          title: 'Why Choose Us',
          subtitle: 'Trusted by 1000+ families',
        ),
        
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              childAspectRatio: 1.2,
              crossAxisSpacing: 12,
              mainAxisSpacing: 12,
            ),
            itemCount: features.length,
            itemBuilder: (context, index) {
              final feature = features[index];
              return Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: Colors.grey.shade200),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: AppTheme.primaryColor.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Icon(
                        feature['icon'] as IconData,
                        color: AppTheme.primaryColor,
                        size: 24,
                      ),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      feature['title'] as String,
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 14,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      feature['description'] as String,
                      style: TextStyle(
                        fontSize: 11,
                        color: Colors.grey.shade600,
                      ),
                    ),
                  ],
                ),
              );
            },
          ),
        ),
      ],
    );
  }
}

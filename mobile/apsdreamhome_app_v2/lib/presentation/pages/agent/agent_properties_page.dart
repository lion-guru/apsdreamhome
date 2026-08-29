import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/providers/auth_provider.dart';
import '../../widgets/app_widgets.dart';
import '../../widgets/glass_card.dart';

/// Agent Properties Page - View and manage agent property listings
class AgentPropertiesPage extends ConsumerWidget {
  const AgentPropertiesPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final userAsync = ref.watch(currentUserDataProvider);

    return Scaffold(
      body: userAsync.when(
        data: (user) {
          if (user == null) {
            return AppWidgets.errorWidget(
              message: 'User not found',
              onRetry: () => ref.refresh(currentUserDataProvider),
            );
          }
          return _buildBody(context, ref, user);
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, stack) => AppWidgets.errorWidget(
          message: error.toString(),
          onRetry: () => ref.refresh(currentUserDataProvider),
        ),
      ),
    );
  }

  Widget _buildBody(BuildContext context, WidgetRef ref, dynamic user) {
    final userId = (user.id ?? user['id'] ?? 0) as int;
    final propertiesAsync = ref.watch(_agentPropertiesProvider(userId));

    return RefreshIndicator(
      onRefresh: () async {
        ref.invalidate(_agentPropertiesProvider(userId));
        await Future.delayed(const Duration(milliseconds: 500));
      },
      color: AppTheme.primaryColor,
      child: CustomScrollView(
        slivers: [
          SliverToBoxAdapter(child: _buildAppBar(context)),
          SliverToBoxAdapter(child: _buildFilterChips(context)),
          SliverToBoxAdapter(
            child: AppWidgets.sectionHeader(
              title: 'My Listings',
              subtitle: 'Manage your property listings',
              onSeeAll: () {},
            ),
          ),
          SliverToBoxAdapter(
            child: propertiesAsync.when(
              data: (properties) {
                if (properties.isEmpty) {
                  return _buildEmptyState(context);
                }
                return _buildPropertiesList(context, properties);
              },
              loading: () => const Center(
                child: Padding(
                  padding: EdgeInsets.all(32),
                  child:
                      CircularProgressIndicator(color: AppTheme.primaryColor),
                ),
              ),
              error: (error, stack) => AppWidgets.errorWidget(
                message: error.toString(),
                onRetry: () =>
                    ref.invalidate(_agentPropertiesProvider(userId)),
              ),
            ),
          ),
          const SliverToBoxAdapter(child: SizedBox(height: 32)),
        ],
      ),
    );
  }

  Widget _buildAppBar(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
        ),
        borderRadius: BorderRadius.vertical(bottom: Radius.circular(24)),
      ),
      child: SafeArea(
        child: Row(
          children: [
            Container(
              width: 60,
              height: 60,
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.2),
                borderRadius: BorderRadius.circular(16),
              ),
              child: const Icon(
                Icons.home_work_rounded,
                color: Colors.white,
                size: 30,
              ),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'My Properties',
                    style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                          color: Colors.white,
                          fontWeight: FontWeight.w800,
                        ),
                  ),
                  Text(
                    'Manage your property listings',
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                          color: Colors.white.withValues(alpha: 0.8),
                        ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFilterChips(BuildContext context) {
    final filters = ['All', 'Available', 'Booked', 'Sold'];
    return Container(
      height: 50,
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: filters.length,
        separatorBuilder: (_, _) => const SizedBox(width: 8),
        itemBuilder: (context, index) {
          return FilterChip(
            label: Text(filters[index]),
            selected: index == 0,
            onSelected: (_) {},
            selectedColor: AppTheme.primaryColor.withValues(alpha: 0.2),
            checkmarkColor: AppTheme.primaryColor,
            labelStyle: TextStyle(
              color: index == 0 ? AppTheme.primaryColor : Colors.grey[700],
              fontWeight: index == 0 ? FontWeight.w600 : FontWeight.w500,
            ),
          );
        },
      ),
    );
  }

  Widget _buildEmptyState(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 100,
              height: 100,
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [
                    AppTheme.primaryColor.withValues(alpha: 0.2),
                    AppTheme.secondaryColor.withValues(alpha: 0.2),
                  ],
                ),
                borderRadius: BorderRadius.circular(24),
              ),
              child: const Icon(Icons.home_outlined, size: 50, color: Colors.grey),
            ),
            const SizedBox(height: 24),
            Text(
              'No Properties Yet',
              style: AppTheme.headlineMedium.copyWith(
                color: Colors.grey[800],
                fontWeight: FontWeight.w700,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Your property listings will appear here',
              style: TextStyle(color: Colors.grey[600], fontSize: 16),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPropertiesList(BuildContext context, List<dynamic> properties) {
    return ListView.separated(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      padding: const EdgeInsets.symmetric(horizontal: 16),
      itemCount: properties.length,
      separatorBuilder: (_, _) => const SizedBox(height: 12),
      itemBuilder: (context, index) {
        final property = properties[index] as Map<String, dynamic>;
        return _buildPropertyCard(context, property);
      },
    );
  }

  Widget _buildPropertyCard(BuildContext context, Map<String, dynamic> property) {
    final title = property['title']?.toString() ?? 'Untitled Property';
    final location = property['location']?.toString() ??
        property['city']?.toString() ??
        '';
    final price = property['price']?.toString() ?? '';
    final type = property['property_type']?.toString() ?? '';
    final status = property['status']?.toString() ?? 'available';

    Color statusColor;
    switch (status.toLowerCase()) {
      case 'available':
        statusColor = Colors.green;
        break;
      case 'booked':
        statusColor = AppTheme.warningColor;
        break;
      case 'sold':
        statusColor = Colors.red;
        break;
      default:
        statusColor = Colors.grey;
    }

    return GlassCard(
      padding: const EdgeInsets.all(16),
      opacity: 0.1,
      blur: 8,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 60,
                height: 60,
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [
                      AppTheme.primaryColor.withValues(alpha: 0.2),
                      AppTheme.secondaryColor.withValues(alpha: 0.2),
                    ],
                  ),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.home_rounded,
                    color: AppTheme.primaryColor, size: 28),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: const TextStyle(
                        fontWeight: FontWeight.w600,
                        fontSize: 16,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    if (location.isNotEmpty)
                      Padding(
                        padding: const EdgeInsets.only(top: 4),
                        child: Row(
                          children: [
                            Icon(Icons.location_on_rounded,
                                size: 14, color: Colors.grey[600]),
                            const SizedBox(width: 4),
                            Expanded(
                              child: Text(
                                location,
                                style: TextStyle(
                                    color: Colors.grey[600], fontSize: 12),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),
                      ),
                    if (type.isNotEmpty)
                      Padding(
                        padding: const EdgeInsets.only(top: 4),
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 8, vertical: 2),
                          decoration: BoxDecoration(
                            color:
                                AppTheme.infoColor.withValues(alpha: 0.15),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            type.toUpperCase(),
                            style: const TextStyle(
                              color: AppTheme.infoColor,
                              fontWeight: FontWeight.w600,
                              fontSize: 10,
                            ),
                          ),
                        ),
                      ),
                  ],
                ),
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  if (price.isNotEmpty)
                    Text(
                      '\u20B9${_formatPrice(price)}',
                      style: const TextStyle(
                        color: AppTheme.successColor,
                        fontWeight: FontWeight.w700,
                        fontSize: 15,
                      ),
                    ),
                  const SizedBox(height: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: statusColor.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      status.toUpperCase(),
                      style: TextStyle(
                        color: statusColor,
                        fontWeight: FontWeight.w600,
                        fontSize: 11,
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: SizedBox(
                  height: 36,
                  child: OutlinedButton.icon(
                    onPressed: () => _viewDetails(property),
                    icon: const Icon(Icons.visibility_rounded, size: 16),
                    label: const Text('View'),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppTheme.primaryColor,
                      side: BorderSide(
                          color: AppTheme.primaryColor.withValues(alpha: 0.5)),
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10)),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: SizedBox(
                  height: 36,
                  child: OutlinedButton.icon(
                    onPressed: () => _editProperty(property),
                    icon: const Icon(Icons.edit_rounded, size: 16),
                    label: const Text('Edit'),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppTheme.infoColor,
                      side: BorderSide(
                          color: AppTheme.infoColor.withValues(alpha: 0.5)),
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10)),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: SizedBox(
                  height: 36,
                  child: OutlinedButton.icon(
                    onPressed: () => _shareProperty(property),
                    icon: const Icon(Icons.share_rounded, size: 16),
                    label: const Text('Share'),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppTheme.successColor,
                      side: BorderSide(
                          color: AppTheme.successColor.withValues(alpha: 0.5)),
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10)),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  String _formatPrice(String value) {
    final num = double.tryParse(value) ?? 0;
    if (num >= 10000000) {
      return '${(num / 10000000).toStringAsFixed(2)} Cr';
    } else if (num >= 100000) {
      return '${(num / 100000).toStringAsFixed(2)} L';
    }
    return num.toStringAsFixed(0);
  }

  void _viewDetails(Map<String, dynamic> property) {}
  void _editProperty(Map<String, dynamic> property) {}
  void _shareProperty(Map<String, dynamic> property) {}
}

// Provider
final _agentPropertiesProvider =
    FutureProvider.family<List<dynamic>, int>((ref, userId) async {
  try {
    final api = ApiService();
    AppConstants.initBaseUrl();
    final response =
        await api.get('${AppConstants.apiVersion}/agent/properties');
    if (response['success'] == true && response['data'] != null) {
      final data = response['data'];
      final properties = (data is List ? data : data['properties'] ?? []) as List;
      return List<Map<String, dynamic>>.from(properties);
    }
  } catch (_) {}
  // Mock data fallback
  return [
    {
      'id': 1,
      'title': 'Suryoday Heights - Plot A-101',
      'location': 'Gorakhpur, UP',
      'price': '2500000',
      'property_type': 'Plot',
      'status': 'available',
    },
    {
      'id': 2,
      'title': 'Braj Radha - Plot B-205',
      'location': 'Mathura, UP',
      'price': '1800000',
      'property_type': 'Plot',
      'status': 'booked',
    },
    {
      'id': 3,
      'title': 'Raghunath Nagri - Villa C-303',
      'location': 'Varanasi, UP',
      'price': '4500000',
      'property_type': 'Villa',
      'status': 'sold',
    },
  ];
});

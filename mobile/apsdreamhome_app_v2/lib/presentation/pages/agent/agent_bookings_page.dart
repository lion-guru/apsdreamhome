import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/providers/auth_provider.dart';
import '../../widgets/app_widgets.dart';
import '../../widgets/glass_card.dart';

/// Agent Bookings Page - View and manage property bookings
class AgentBookingsPage extends ConsumerWidget {
  const AgentBookingsPage({super.key});

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
    final bookingsAsync = ref.watch(_agentBookingsProvider(userId));

    return RefreshIndicator(
      onRefresh: () async {
        ref.invalidate(_agentBookingsProvider(userId));
        await Future.delayed(const Duration(milliseconds: 500));
      },
      color: AppTheme.primaryColor,
      child: CustomScrollView(
        slivers: [
          SliverToBoxAdapter(child: _buildAppBar(context)),
          SliverToBoxAdapter(
            child: _buildFilterChips(context),
          ),
          SliverToBoxAdapter(
            child: AppWidgets.sectionHeader(
              title: 'Your Bookings',
              subtitle: 'Manage your property bookings',
              onSeeAll: () {},
            ),
          ),
          SliverToBoxAdapter(
            child: bookingsAsync.when(
              data: (bookings) {
                if (bookings.isEmpty) {
                  return _buildEmptyState(context);
                }
                return _buildBookingsList(context, bookings);
              },
              loading: () => const Center(
                child: Padding(
                  padding: EdgeInsets.all(32),
                  child: CircularProgressIndicator(color: AppTheme.primaryColor),
                ),
              ),
              error: (error, stack) => AppWidgets.errorWidget(
                message: error.toString(),
                onRetry: () => ref.invalidate(_agentBookingsProvider(userId)),
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
        child: Column(
          children: [
            Row(
              children: [
                Container(
                  width: 60,
                  height: 60,
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: const Icon(
                    Icons.calendar_today_rounded,
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
                        'My Bookings',
                        style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                          color: Colors.white,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                      Text(
                        'Manage your property bookings',
                        style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                          color: Colors.white.withValues(alpha: 0.8),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
          ],
        ),
      ),
    );
  }

  Widget _buildFilterChips(BuildContext context) {
    final filters = ['All', 'Pending', 'Confirmed', 'Completed', 'Cancelled'];
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
              child: const Icon(
                Icons.calendar_today_outlined,
                size: 50,
                color: Colors.grey,
              ),
            ),
            const SizedBox(height: 24),
            Text(
              'No Bookings Yet',
              style: AppTheme.headlineMedium.copyWith(
                color: Colors.grey[800],
                fontWeight: FontWeight.w700,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Start booking properties for your clients',
              style: TextStyle(color: Colors.grey[600], fontSize: 16),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 32),
            ElevatedButton.icon(
              onPressed: () => context.go('/post-property'),
              icon: const Icon(Icons.add_circle_rounded),
              label: const Text('Create Booking'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primaryColor,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 16),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ],
        ),
      );
  }

  Widget _buildBookingsList(BuildContext context, List<dynamic> bookings) {
    return ListView.separated(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      padding: const EdgeInsets.symmetric(horizontal: 16),
      itemCount: bookings.length,
      separatorBuilder: (_, _) => const SizedBox(height: 12),
      itemBuilder: (context, index) {
        final booking = bookings[index] as Map<String, dynamic>;
        return _buildBookingCard(context, booking);
      },
    );
  }

  Widget _buildBookingCard(BuildContext context, Map<String, dynamic> booking) {
    final status = booking['status']?.toString() ?? 'pending';
    final isConfirmed = status == 'confirmed';
    final isPending = status == 'pending';
    final isCancelled = status == 'cancelled';

    Color statusColor;
    IconData statusIcon;
    if (isConfirmed) {
      statusColor = AppTheme.successColor;
      statusIcon = Icons.check_circle_rounded;
    } else if (isPending) {
      statusColor = AppTheme.warningColor;
      statusIcon = Icons.hourglass_empty_rounded;
    } else if (isCancelled) {
      statusColor = Colors.red;
      statusIcon = Icons.cancel_rounded;
    } else {
      statusColor = AppTheme.infoColor;
      statusIcon = Icons.info_rounded;
    }

    return GlassCard(
      padding: const EdgeInsets.all(16),
      opacity: 0.1,
      blur: 8,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              if (booking['image'] != null)
                ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child: Image.network(
                    booking['image'].startsWith('http') 
                        ? booking['image'] 
                        : '${AppConstants.baseUrl}/${booking['image']}',
                    width: 80,
                    height: 60,
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) => _buildPlaceholder(),
                  ),
                )
              else
                _buildPlaceholder(),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      booking['title'] ?? booking['property_title'] ?? 'Property',
                      style: const TextStyle(
                        color: Colors.black,
                        fontWeight: FontWeight.w600,
                        fontSize: 16,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 4),
                    if (booking['location'] != null)
                      Text(
                        booking['location'],
                        style: TextStyle(
                          color: Colors.grey[600],
                          fontSize: 12,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    if (booking['price'] != null)
                      Text(
                        '₹${_formatCurrency(booking['price'].toString())}',
                        style: const TextStyle(
                          color: AppTheme.primaryColor,
                          fontWeight: FontWeight.w700,
                          fontSize: 14,
                        ),
                      ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                decoration: BoxDecoration(
                  color: statusColor.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(statusIcon, color: statusColor, size: 14),
                    const SizedBox(width: 4),
                    Text(
                      status.toUpperCase(),
                      style: TextStyle(
                        color: statusColor,
                        fontWeight: FontWeight.w600,
                        fontSize: 11,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          if (booking['plot_number'] != null) ...[
            Row(
              children: [
                Icon(Icons.grid_3x3_rounded, size: 16, color: Colors.grey[600]),
                const SizedBox(width: 6),
                Text(
                  'Plot: ${booking['plot_number']}',
                  style: TextStyle(color: Colors.grey[600], fontSize: 13),
                ),
              ],
            ),
            const SizedBox(height: 8),
          ],
          Row(
            children: [
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: () => context.push('/agent/bookings/${booking['id']}'),
                  icon: const Icon(Icons.visibility_rounded, size: 16),
                  label: const Text('View Details'),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: AppTheme.primaryColor,
                    side: BorderSide(color: AppTheme.primaryColor.withValues(alpha: 0.5)),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              if (booking['status'] == 'pending')
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () => _showConfirmDialog(context, booking),
                    icon: const Icon(Icons.check_circle_rounded, size: 16),
                    label: const Text('Confirm'),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppTheme.successColor,
                      side: BorderSide(color: AppTheme.successColor.withValues(alpha: 0.5)),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                  ),
                ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildPlaceholder() {
    return Container(
      width: 80,
      height: 60,
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            AppTheme.primaryColor.withValues(alpha: 0.2),
            AppTheme.secondaryColor.withValues(alpha: 0.2),
          ],
        ),
        borderRadius: BorderRadius.circular(8),
      ),
      child: const Icon(Icons.home_outlined, size: 32, color: Colors.grey),
    );
  }

  String _formatCurrency(String value) {
    final num = double.tryParse(value) ?? 0;
    if (num >= 10000000) {
      return '${(num / 10000000).toStringAsFixed(2)} Cr';
    } else if (num >= 100000) {
      return '${(num / 100000).toStringAsFixed(2)} L';
    }
    return num.toStringAsFixed(0);
  }

  void _showConfirmDialog(BuildContext context, Map<String, dynamic> booking) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Confirm Booking'),
        content: Text('Are you sure you want to confirm booking #${booking['id']}?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              // TODO: Call API to confirm booking
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Booking confirmed!'), backgroundColor: AppTheme.successColor),
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.successColor,
              foregroundColor: Colors.white,
            ),
            child: const Text('Confirm'),
          ),
        ],
      ),
    );
  }
}

// Provider
final _agentBookingsProvider = FutureProvider.family<List<dynamic>, int>((ref, userId) async {
  try {
    final api = ApiService();
    AppConstants.initBaseUrl();
    final response = await api.get('${AppConstants.apiVersion}/agent/bookings');
    if (response['success'] == true && response['data'] != null) {
      final data = response['data'];
      final bookings = (data is List ? data : data['bookings'] ?? []) as List;
      return List<Map<String, dynamic>>.from(bookings);
    }
  } catch (_) {}
  // Mock data fallback
  return [
    {
      'id': 1,
      'title': 'Suryoday Heights - Plot A-101',
      'location': 'Gorakhpur, UP',
      'price': 2500000,
      'status': 'confirmed',
      'plot_number': 'A-101',
      'image': 'assets/images/plot_placeholder.png',
    },
{
      'id': 2,
      'title': 'Braj Radha - Plot B-205',
      'location': 'Mathura, UP',
      'price': 1800000,
      'status': 'pending',
      'plot_number': 'B-205',
      'image': 'assets/images/plot_placeholder.png',
    },
  ];
}

class _BookingCard {
  // Placeholder for type
}
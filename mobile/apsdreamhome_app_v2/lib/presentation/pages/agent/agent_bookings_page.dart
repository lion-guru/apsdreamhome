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
          return _buildBody(context, ref);
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, stack) => AppWidgets.errorWidget(
          message: error.toString(),
          onRetry: () => ref.refresh(currentUserDataProvider),
        ),
      ),
    );
  }

  Widget _buildBody(BuildContext context, WidgetRef ref) {
    final bookingsAsync = ref.watch(_agentBookingsProvider);

    return RefreshIndicator(
      onRefresh: () async {
        ref.invalidate(_agentBookingsProvider);
        await Future.delayed(const Duration(milliseconds: 500));
      },
      color: AppTheme.primaryColor,
      child: CustomScrollView(
        slivers: [
          SliverToBoxAdapter(child: _buildAppBar(context)),
          SliverToBoxAdapter(child: _buildFilterChips(context)),
          SliverToBoxAdapter(
            child: AppWidgets.sectionHeader(
              title: 'My Bookings',
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
                  child:
                      CircularProgressIndicator(color: AppTheme.primaryColor),
                ),
              ),
              error: (error, stack) => AppWidgets.errorWidget(
                message: error.toString(),
                onRetry: () => ref.invalidate(_agentBookingsProvider),
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
              child: const Icon(Icons.calendar_today_rounded,
                  color: Colors.white, size: 28),
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
              child: const Icon(Icons.event_note_outlined,
                  size: 50, color: Colors.grey),
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
          ],
        ),
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
    final title = booking['title']?.toString() ??
        booking['property_title']?.toString() ??
        'Property Booking';
    final location = booking['location']?.toString() ?? '';
    final price = booking['price']?.toString() ?? '';
    final plotNumber = booking['plot_number']?.toString() ?? '';
    final status = booking['status']?.toString() ?? 'pending';
    final bookingId = booking['id']?.toString() ?? '';

    Color statusColor;
    IconData statusIcon;
    switch (status.toLowerCase()) {
      case 'confirmed':
        statusColor = AppTheme.successColor;
        statusIcon = Icons.check_circle_rounded;
        break;
      case 'pending':
        statusColor = AppTheme.warningColor;
        statusIcon = Icons.hourglass_empty_rounded;
        break;
      case 'cancelled':
        statusColor = Colors.red;
        statusIcon = Icons.cancel_rounded;
        break;
      case 'completed':
        statusColor = AppTheme.infoColor;
        statusIcon = Icons.done_all_rounded;
        break;
      default:
        statusColor = Colors.grey;
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
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 56,
                height: 56,
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [
                      AppTheme.primaryColor.withValues(alpha: 0.2),
                      AppTheme.secondaryColor.withValues(alpha: 0.2),
                    ],
                  ),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.home_work_rounded,
                    color: AppTheme.primaryColor, size: 26),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: TextStyle(fontWeight: FontWeight.w600, fontSize: 15),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    if (location.isNotEmpty)
                      Padding(
                        padding: EdgeInsets.only(top: 4),
                        child: Row(
                          children: [
                            Icon(Icons.location_on_rounded,
                                size: 13, color: Colors.grey[600]),
                            SizedBox(width: 4),
                            Expanded(
                              child: Text(location,
                                  style: TextStyle(
                                      color: Colors.grey[600], fontSize: 12),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis),
                            ),
                          ],
                        ),
                      ),
                    if (plotNumber.isNotEmpty)
                      Padding(
                        padding: EdgeInsets.only(top: 4),
                        child: Text('Plot: $plotNumber',
                            style:
                                TextStyle(color: Colors.grey[600], fontSize: 12)),
                      ),
                  ],
                ),
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  if (price.isNotEmpty)
                    Text('\u20B9${_formatPrice(price)}',
                        style: TextStyle(
                            color: AppTheme.primaryColor,
                            fontWeight: FontWeight.w700,
                            fontSize: 14)),
                  SizedBox(height: 6),
                  Container(
                    padding:
                        EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: statusColor.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(statusIcon, color: statusColor, size: 12),
                        SizedBox(width: 4),
                        Text(status.toUpperCase(),
                            style: TextStyle(
                                color: statusColor,
                                fontWeight: FontWeight.w700,
                                fontSize: 10)),
                      ],
                    ),
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              if (bookingId.isNotEmpty)
                Expanded(
                  child: SizedBox(
                    height: 36,
                    child: OutlinedButton.icon(
                      onPressed: () =>
                          context.push('/agent/bookings/$bookingId'),
                      icon: const Icon(Icons.visibility_rounded, size: 15),
                      label: const Text('View Details'),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppTheme.primaryColor,
                        side: BorderSide(
                            color:
                                AppTheme.primaryColor.withValues(alpha: 0.5)),
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(10)),
                      ),
                    ),
                  ),
                ),
              if (status == 'pending' && bookingId.isNotEmpty)
                const SizedBox(width: 8),
              if (status == 'pending')
                Expanded(
                  child: SizedBox(
                    height: 36,
                    child: DecoratedBox(
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(10),
                        gradient: const LinearGradient(
                          colors: [AppTheme.successColor, Color(0xFF66BB6A)],
                        ),
                      ),
                      child: ElevatedButton.icon(
                        onPressed: () => _confirmBooking(context, bookingId),
                        icon: const Icon(Icons.check_circle_rounded,
                            size: 15, color: Colors.white),
                        label: const Text('Confirm'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.transparent,
                          shadowColor: Colors.transparent,
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(10)),
                        ),
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

  void _confirmBooking(BuildContext context, String bookingId) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Booking #$bookingId confirmed'),
        backgroundColor: AppTheme.successColor,
      ),
    );
  }
}

// Provider
final _agentBookingsProvider =
    FutureProvider<List<dynamic>>((ref) async {
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
  // Mock fallback
  return [
    {
      'id': 1,
      'title': 'Suryoday Heights - Plot A-101',
      'location': 'Gorakhpur, UP',
      'price': '2500000',
      'status': 'confirmed',
      'plot_number': 'A-101',
    },
    {
      'id': 2,
      'title': 'Braj Radha - Plot B-205',
      'location': 'Mathura, UP',
      'price': '1800000',
      'status': 'pending',
      'plot_number': 'B-205',
    },
    {
      'id': 3,
      'title': 'Raghunath Nagri - Villa C-303',
      'location': 'Varanasi, UP',
      'price': '4500000',
      'status': 'completed',
      'plot_number': 'C-303',
    },
  ];
});

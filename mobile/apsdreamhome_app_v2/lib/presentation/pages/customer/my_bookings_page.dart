import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/providers/auth_provider.dart';
import '../../widgets/app_widgets.dart';
import 'package:dio/dio.dart';

/// Provider to fetch customer bookings from API
final myBookingsProvider = FutureProvider<List<dynamic>>((ref) async {
  final dio = Dio(BaseOptions(baseUrl: AppConstants.baseUrl));
  final token = await ref.read(authProvider.notifier).getToken();

  try {
    final response = await dio.get(
      '${AppConstants.apiVersion}/customer/bookings',
      options: Options(headers: {'Authorization': 'Bearer $token'}),
    );
    return response.data['data'] as List<dynamic>? ?? [];
  } catch (e) {
    return [];
  }
});

class MyBookingsPage extends ConsumerStatefulWidget {
  const MyBookingsPage({super.key});

  @override
  ConsumerState<MyBookingsPage> createState() => _MyBookingsPageState();
}

class _MyBookingsPageState extends ConsumerState<MyBookingsPage>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final bookingsAsync = ref.watch(myBookingsProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('My Bookings'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: Colors.white,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          tabs: const [
            Tab(text: 'Active'),
            Tab(text: 'Completed'),
            Tab(text: 'All'),
          ],
        ),
      ),
      body: bookingsAsync.when(
        data: (bookings) {
          if (bookings.isEmpty) return _buildEmptyState();
          return TabBarView(
            controller: _tabController,
            children: [
              _buildBookingsList(bookings, 'active'),
              _buildBookingsList(bookings, 'completed'),
              _buildBookingsList(bookings, 'all'),
            ],
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (err, _) => Center(
          child: AppWidgets.errorWidget(
            message: 'Failed to load bookings',
            onRetry: () => ref.refresh(myBookingsProvider),
          ),
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.home_work_outlined, size: 64, color: Colors.grey.shade400),
          const SizedBox(height: 16),
          Text(
            'No bookings found',
            style: TextStyle(color: Colors.grey.shade600, fontSize: 18),
          ),
          const SizedBox(height: 8),
          const Text('Book a plot to get started'),
          const SizedBox(height: 24),
          ElevatedButton.icon(
            onPressed: () => context.push('/colonies'),
            icon: const Icon(Icons.explore),
            label: const Text('Browse Colonies'),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.primaryColor,
              foregroundColor: Colors.white,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBookingsList(List<dynamic> bookings, String filter) {
    final filtered = bookings.where((b) {
      final status = (b['status'] as String?) ?? '';
      if (filter == 'active') {
        return status == 'confirmed' ||
            status == 'pending' ||
            status == 'token_paid' ||
            status == 'emi_active';
      } else if (filter == 'completed') {
        return status == 'completed' || status == 'registration_done';
      }
      return true;
    }).toList();

    if (filtered.isEmpty) {
      return AppWidgets.emptyState(
        title:
            'No ${filter == 'active'
                ? 'Active'
                : filter == 'completed'
                ? 'Completed'
                : ''} Bookings',
        subtitle: filter == 'active'
            ? 'You don\'t have any active bookings'
            : 'Completed bookings will appear here',
        icon: Icons.home_work_outlined,
        onAction: () => context.push('/colonies'),
        actionLabel: 'Browse Colonies',
      );
    }

    return RefreshIndicator(
      onRefresh: () async => ref.refresh(myBookingsProvider),
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: filtered.length,
        itemBuilder: (context, index) {
          final booking = filtered[index] as Map<String, dynamic>;
          return _buildBookingCard(booking);
        },
      ),
    );
  }

  Widget _buildBookingCard(Map<String, dynamic> booking) {
    final status = (booking['status'] as String?) ?? 'pending';
    final totalAmount = (booking['amount'] as num?)?.toDouble() ?? 0;
    final propertyName =
        (booking['property_name'] as String?) ?? 'Plot Booking';
    final createdAt = (booking['created_at'] as String?) ?? '';
    final bookingId = booking['id'];

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 6),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header row
          Row(
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: AppTheme.primaryColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(
                  Icons.home_work,
                  color: AppTheme.primaryColor,
                  size: 24,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      propertyName,
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 15,
                      ),
                    ),
                    const SizedBox(height: 2),
                    if (createdAt.isNotEmpty)
                      Text(
                        'Booked on: ${createdAt.split('T').first}',
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.grey.shade500,
                        ),
                      ),
                  ],
                ),
              ),
              _buildStatusChip(status),
            ],
          ),

          const SizedBox(height: 14),
          const Divider(height: 1),
          const SizedBox(height: 14),

          // Price row
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Amount',
                    style: TextStyle(fontSize: 11, color: Colors.grey),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    '₹${_formatPrice(totalAmount)}',
                    style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: AppTheme.primaryColor,
                    ),
                  ),
                ],
              ),
              if (status != 'completed' && status != 'registration_done')
                ElevatedButton.icon(
                  onPressed: () {
                    if (bookingId != null) {
                      context.push('/customer/emi-schedule', extra: bookingId);
                    }
                  },
                  icon: const Icon(Icons.payment, size: 16),
                  label: const Text('Pay Now', style: TextStyle(fontSize: 12)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTheme.primaryColor,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(
                      horizontal: 14,
                      vertical: 8,
                    ),
                  ),
                ),
              if (status == 'completed' || status == 'registration_done')
                OutlinedButton.icon(
                  onPressed: () {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text(
                          'Receipt will be available after final payment confirmation',
                        ),
                      ),
                    );
                  },
                  icon: const Icon(Icons.download, size: 16),
                  label: const Text('Receipt', style: TextStyle(fontSize: 12)),
                  style: OutlinedButton.styleFrom(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 14,
                      vertical: 8,
                    ),
                  ),
                ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildStatusChip(String status) {
    Color color;
    String label;
    switch (status) {
      case 'confirmed':
      case 'token_paid':
      case 'emi_active':
        color = AppTheme.successColor;
        label = 'Active';
        break;
      case 'pending':
        color = AppTheme.warningColor;
        label = 'Pending';
        break;
      case 'completed':
      case 'registration_done':
        color = AppTheme.infoColor;
        label = 'Completed';
        break;
      case 'cancelled':
        color = AppTheme.errorColor;
        label = 'Cancelled';
        break;
      default:
        color = Colors.grey;
        label = status.toUpperCase();
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Text(
        label,
        style: TextStyle(
          color: color,
          fontSize: 10,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }

  String _formatPrice(double price) {
    if (price >= 10000000) {
      return '${(price / 10000000).toStringAsFixed(2)} Cr';
    } else if (price >= 100000) {
      return '${(price / 100000).toStringAsFixed(2)} L';
    }
    return NumberFormat('#,##,###').format(price);
  }
}

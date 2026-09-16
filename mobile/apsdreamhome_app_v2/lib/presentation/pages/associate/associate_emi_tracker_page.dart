import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/services/storage_service.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/providers/connectivity_provider.dart';
import '../../widgets/app_widgets.dart';
import '../../widgets/glass_card.dart';

/// Associate EMI Tracker — upcoming + overdue EMIs of my customers' bookings.
/// Data: GET /api/v2/mobile/associate/emi-tracker (mirrors web /associate/emi-tracker).
/// Offline-first: shows cached data instantly, refreshes in background.
class AssociateEmiTrackerPage extends ConsumerWidget {
  const AssociateEmiTrackerPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final userAsync = ref.watch(currentUserDataProvider);
    final isOnline = ref.watch(isOnlineProvider);

    return Scaffold(
      body: userAsync.when(
        data: (user) {
          if (user == null) {
            return AppWidgets.errorWidget(
              message: 'User not found',
              onRetry: () => ref.refresh(currentUserDataProvider),
            );
          }
          return _buildBody(context, ref, isOnline);
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, stack) => AppWidgets.errorWidget(
          message: error.toString(),
          onRetry: () => ref.refresh(currentUserDataProvider),
        ),
      ),
    );
  }

  Widget _buildBody(BuildContext context, WidgetRef ref, bool isOnline) {
    final emisAsync = ref.watch(_associateEmisProvider);
    final storage = StorageService();
    final cacheInfo = storage.getCacheInfo();

    return RefreshIndicator(
      onRefresh: () async {
        ref.invalidate(_associateEmisProvider);
        await Future.delayed(const Duration(milliseconds: 500));
      },
      color: AppTheme.primaryColor,
      child: CustomScrollView(
        slivers: [
          SliverToBoxAdapter(child: _buildAppBar(context)),
          // Offline banner
          if (!isOnline)
            SliverToBoxAdapter(
              child: _buildOfflineBanner(context, cacheInfo),
            ),
          // Stale cache indicator
          if (isOnline && cacheInfo['isStale'] == true)
            SliverToBoxAdapter(
              child: _buildStaleBanner(context, cacheInfo),
            ),
          SliverToBoxAdapter(
            child: emisAsync.when(
              data: (result) {
                final OfflineEmiResult emiResult = result;
                final emis = emiResult.emis;
                
                if (emis.isEmpty) return _buildEmptyState(context);
                
                return Column(
                  children: [
                    if (emiResult.fromCache) _buildCacheIndicator(context, emiResult),
                    _buildSummaryHeader(context, emis),
                    _buildEmiList(context, emis),
                  ],
                );
              },
              loading: () => const Center(
                child: Padding(
                  padding: EdgeInsets.all(32),
                  child: CircularProgressIndicator(
                      color: AppTheme.primaryColor),
                ),
              ),
              error: (error, stack) => AppWidgets.errorWidget(
                message: error.toString(),
                onRetry: () => ref.invalidate(_associateEmisProvider),
              ),
            ),
          ),
          const SliverToBoxAdapter(child: SizedBox(height: 32)),
        ],
      ),
    );
  }

  Widget _buildOfflineBanner(BuildContext context, Map<String, dynamic> cacheInfo) {
    final hasCache = cacheInfo['hasData'] == true;
    final count = cacheInfo['count'] ?? 0;
    
    return Container(
      margin: const EdgeInsets.all(16),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.orange.shade50,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.orange.shade200),
      ),
      child: Row(
        children: [
          Icon(Icons.cloud_off_rounded, color: Colors.orange.shade700, size: 20),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'You\'re offline',
                  style: TextStyle(
                    color: Colors.orange.shade800,
                    fontWeight: FontWeight.w700,
                    fontSize: 13,
                  ),
                ),
                Text(
                  hasCache
                      ? 'Showing $count cached EMI records. Will sync when online.'
                      : 'No cached data available. Connect to internet to load.',
                  style: TextStyle(
                    color: Colors.orange.shade700,
                    fontSize: 11,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStaleBanner(BuildContext context, Map<String, dynamic> cacheInfo) {
    final timestamp = cacheInfo['timestamp'] as String?;
    final formattedTime = timestamp != null 
        ? DateTime.parse(timestamp).toLocal().toString().split('.')[0]
        : 'unknown time';
    
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: Colors.blue.shade50,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: Colors.blue.shade200),
      ),
      child: Row(
        children: [
          Icon(Icons.info_outline_rounded, color: Colors.blue.shade700, size: 18),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              'Showing cached data from $formattedTime. Pull to refresh.',
              style: TextStyle(
                color: Colors.blue.shade800,
                fontSize: 12,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCacheIndicator(BuildContext context, OfflineEmiResult result) {
    final timestamp = result.cacheTimestamp;
    final formattedTime = timestamp != null 
        ? '${timestamp.toLocal().hour.toString().padLeft(2, '0')}:${timestamp.toLocal().minute.toString().padLeft(2, '0')}'
        : 'cached';
    
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: Colors.grey.shade100,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.cached_rounded, size: 14, color: Colors.grey[600]),
          const SizedBox(width: 6),
          Text(
            'Loaded from cache at $formattedTime${result.isStale ? ' (stale)' : ''}',
            style: TextStyle(
              color: Colors.grey[600],
              fontSize: 11,
              fontStyle: FontStyle.italic,
            ),
          ),
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
              child: const Icon(Icons.schedule_rounded,
                  color: Colors.white, size: 28),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'EMI Tracker',
                    style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                          color: Colors.white,
                          fontWeight: FontWeight.w800,
                        ),
                  ),
                  Text(
                    'Customer EMIs — upcoming & overdue',
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

  Widget _buildSummaryHeader(
      BuildContext context, List<Map<String, dynamic>> emis) {
    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    double pending = 0;
    int overdue = 0;
    for (final e in emis) {
      pending += double.tryParse('${e['amount'] ?? 0}') ?? 0;
      final due = DateTime.tryParse('${e['due_date'] ?? ''}');
      if (due != null && due.isBefore(today)) overdue++;
    }

    return Container(
      margin: const EdgeInsets.all(16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            AppTheme.primaryColor,
            AppTheme.primaryColor.withValues(alpha: 0.8),
          ],
        ),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceAround,
        children: [
          _summaryItem('${emis.length}', 'Upcoming'),
          _summaryItem('₹${_formatShort(pending)}', 'Pending'),
          _summaryItem('$overdue', 'Overdue', highlight: overdue > 0),
        ],
      ),
    );
  }

  Widget _summaryItem(String value, String label, {bool highlight = false}) {
    return Column(
      children: [
        Text(
          value,
          style: TextStyle(
            color: highlight ? Colors.red.shade200 : Colors.white,
            fontWeight: FontWeight.w800,
            fontSize: 20,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          label,
          style: TextStyle(
            color: Colors.white.withValues(alpha: 0.8),
            fontSize: 12,
          ),
        ),
      ],
    );
  }

  Widget _buildEmiList(BuildContext context, List<Map<String, dynamic>> emis) {
    return ListView.separated(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      padding: const EdgeInsets.symmetric(horizontal: 16),
      itemCount: emis.length,
      separatorBuilder: (_, _) => const SizedBox(height: 12),
      itemBuilder: (context, index) => _buildEmiCard(context, emis[index]),
    );
  }

  Widget _buildEmiCard(BuildContext context, Map<String, dynamic> emi) {
    final customer = '${emi['customer_name'] ?? 'Customer'}';
    final plot = '${emi['plot_number'] ?? ''}';
    final bookingNo = '${emi['booking_number'] ?? ''}';
    final dueDate = '${emi['due_date'] ?? ''}';
    final amount = double.tryParse('${emi['amount'] ?? 0}') ?? 0;
    final status = '${emi['status'] ?? 'pending'}';
    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    final due = DateTime.tryParse(dueDate);
    final isOverdue = due != null && due.isBefore(today);
    final chipColor = isOverdue
        ? Colors.red
        : (status == 'partial' ? AppTheme.warningColor : AppTheme.infoColor);

    return GlassCard(
      padding: const EdgeInsets.all(16),
      opacity: 0.1,
      blur: 8,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Text(
                  customer,
                  style: const TextStyle(
                      fontWeight: FontWeight.w700, fontSize: 15),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: chipColor.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  isOverdue ? 'OVERDUE' : status.toUpperCase(),
                  style: TextStyle(
                    color: chipColor,
                    fontWeight: FontWeight.w700,
                    fontSize: 10,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          if (plot.isNotEmpty)
            Text('Plot: $plot${bookingNo.isNotEmpty ? '  •  $bookingNo' : ''}',
                style: TextStyle(color: Colors.grey[600], fontSize: 12)),
          const SizedBox(height: 8),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Icon(Icons.event_rounded,
                      size: 14, color: Colors.grey[600]),
                  const SizedBox(width: 4),
                  Text('Due: $dueDate',
                      style: TextStyle(
                          color: isOverdue ? Colors.red : Colors.grey[700],
                          fontSize: 12,
                          fontWeight:
                              isOverdue ? FontWeight.w700 : FontWeight.w500)),
                ],
              ),
              Text(
                '₹${_formatShort(amount)}',
                style: const TextStyle(
                  color: AppTheme.primaryColor,
                  fontWeight: FontWeight.w800,
                  fontSize: 16,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          children: [
            const SizedBox(height: 40),
            Container(
              width: 100,
              height: 100,
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [
                    AppTheme.successColor.withValues(alpha: 0.2),
                    AppTheme.primaryColor.withValues(alpha: 0.2),
                  ],
                ),
                borderRadius: BorderRadius.circular(24),
              ),
              child: const Icon(Icons.check_circle_outline_rounded,
                  size: 50, color: AppTheme.successColor),
            ),
            const SizedBox(height: 24),
            const Text(
              'All Clear!',
              style: TextStyle(fontWeight: FontWeight.w700, fontSize: 18),
            ),
            const SizedBox(height: 8),
            Text(
              'No upcoming or overdue EMIs for your customers',
              style: TextStyle(color: Colors.grey[600], fontSize: 14),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  String _formatShort(double value) {
    if (value >= 10000000) {
      return '${(value / 10000000).toStringAsFixed(2)} Cr';
    } else if (value >= 100000) {
      return '${(value / 100000).toStringAsFixed(2)} L';
    }
    return value.toStringAsFixed(0);
  }
}

// Provider for offline-aware EMI fetching
final _associateEmisProvider = FutureProvider<OfflineEmiResult>((ref) async {
  try {
    final api = ApiService();
    AppConstants.initBaseUrl();
    final result = await api.getAssociateEmiTrackerOffline();
    return result;
  } catch (_) {
    // Return empty result on error
    return OfflineEmiResult(emis: [], fromCache: false);
  }
});
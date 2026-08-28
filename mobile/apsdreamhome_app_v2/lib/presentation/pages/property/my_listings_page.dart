import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:dio/dio.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/providers/auth_provider.dart';

/// Provider to fetch user's posted properties
final myListingsProvider = FutureProvider<List<Map<String, dynamic>>>((
  ref,
) async {
  final dio = Dio(BaseOptions(baseUrl: AppConstants.baseUrl));
  final token = await ref.read(authProvider.notifier).getToken();

  try {
    final response = await dio.get(
      '${AppConstants.apiVersion}${AppConstants.myListingsEndpoint}',
      options: Options(headers: {'Authorization': 'Bearer $token'}),
    );
    final data = response.data['data'];
    if (data is Map && data.containsKey('listings')) {
      return (data['listings'] as List<dynamic>? ?? [])
          .cast<Map<String, dynamic>>();
    }
    if (data is List) {
      return data.cast<Map<String, dynamic>>();
    }
    return [];
  } catch (e) {
    return [];
  }
});

class MyListingsPage extends ConsumerStatefulWidget {
  const MyListingsPage({super.key});

  @override
  ConsumerState<MyListingsPage> createState() => _MyListingsPageState();
}

class _MyListingsPageState extends ConsumerState<MyListingsPage> {
  @override
  Widget build(BuildContext context) {
    final listingsAsync = ref.watch(myListingsProvider);
    final user = ref.watch(authProvider);

    if (user == null) {
      return _buildLoginRequired();
    }

    return Scaffold(
      backgroundColor: const Color(0xFF0f172a),
      appBar: AppBar(
        title: const Text('My Listings'),
        backgroundColor: const Color(0xFF0f172a),
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          listingsAsync.when(
            data: (listings) => Center(
              child: Padding(
                padding: const EdgeInsets.only(right: 16),
                child: Text(
                  '${listings.length} total',
                  style: const TextStyle(color: Colors.white54, fontSize: 14),
                ),
              ),
            ),
        loading: () => const SizedBox.shrink(),
        error: (_, _) => const SizedBox.shrink(),
          ),
        ],
      ),
      body: listingsAsync.when(
        data: (listings) {
          if (listings.isEmpty) return _buildEmptyState();
          return RefreshIndicator(
            onRefresh: () async => ref.refresh(myListingsProvider),
            color: AppTheme.accentColor,
            backgroundColor: const Color(0xFF1e293b),
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: listings.length,
              itemBuilder: (context, index) =>
                  _ListingCard(listing: listings[index]),
            ),
          );
        },
        loading: () => const Center(
          child: CircularProgressIndicator(color: AppTheme.accentColor),
        ),
        error: (err, _) => Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.error_outline, size: 48, color: Colors.red),
              const SizedBox(height: 12),
              const Text(
                'Failed to load listings',
                style: TextStyle(color: Colors.white70, fontSize: 16),
              ),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () => ref.refresh(myListingsProvider),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.accentColor,
                  foregroundColor: Colors.black,
                ),
                child: const Text('Retry'),
              ),
            ],
          ),
        ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push('/post-property'),
        backgroundColor: AppTheme.accentColor,
        foregroundColor: Colors.black,
        icon: const Icon(Icons.add),
        label: const Text(
          'Post Property',
          style: TextStyle(fontWeight: FontWeight.w600),
        ),
      ),
    );
  }

  Widget _buildLoginRequired() {
    return Scaffold(
      backgroundColor: const Color(0xFF0f172a),
      appBar: AppBar(
        title: const Text('My Listings'),
        backgroundColor: const Color(0xFF0f172a),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  color: const Color(0xFF1e293b),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: const Color(0xFF334155)),
                ),
                child: Column(
                  children: [
                    const Icon(
                      Icons.lock_outline,
                      size: 56,
                      color: AppTheme.accentColor,
                    ),
                    const SizedBox(height: 20),
                    const Text(
                      'Login Required',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 22,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 8),
                    const Text(
                      'Please login to view and manage your property listings',
                      textAlign: TextAlign.center,
                      style: TextStyle(color: Colors.white54, fontSize: 14),
                    ),
                    const SizedBox(height: 28),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: () => context.push('/login'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppTheme.accentColor,
                          foregroundColor: Colors.black,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                        child: const Text(
                          'Login',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: const Color(0xFF1e293b),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: const Color(0xFF334155)),
              ),
              child: Column(
                children: [
                  const Icon(
                    Icons.home_work_outlined,
                    size: 64,
                    color: Colors.white38,
                  ),
                  const SizedBox(height: 20),
                  const Text(
                    'No Listings Yet',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 22,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 8),
                  const Text(
                    'You haven\'t posted any properties yet.\nStart listing your properties to reach thousands of buyers.',
                    textAlign: TextAlign.center,
                    style: TextStyle(color: Colors.white54, fontSize: 14),
                  ),
                  const SizedBox(height: 28),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      onPressed: () => context.push('/post-property'),
                      icon: const Icon(Icons.add),
                      label: const Text(
                        'Post Your First Property',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTheme.accentColor,
                        foregroundColor: Colors.black,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
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
}

class _ListingCard extends StatelessWidget {
  final Map<String, dynamic> listing;

  const _ListingCard({required this.listing});

  String _timeAgo(String? dateStr) {
    if (dateStr == null || dateStr.isEmpty) return '';
    try {
      final date = DateTime.parse(dateStr);
      final diff = DateTime.now().difference(date);
      if (diff.inDays > 365) return '${(diff.inDays / 365).floor()}y ago';
      if (diff.inDays > 30) return '${(diff.inDays / 30).floor()}mo ago';
      if (diff.inDays > 0) return '${diff.inDays}d ago';
      if (diff.inHours > 0) return '${diff.inHours}h ago';
      if (diff.inMinutes > 0) return '${diff.inMinutes}m ago';
      return 'Just now';
    } catch (_) {
      return '';
    }
  }

  String _formatPrice(dynamic price) {
    if (price == null) return 'Price on request';
    final p = double.tryParse(price.toString()) ?? 0;
    if (p >= 10000000) return '\u20B9${(p / 10000000).toStringAsFixed(2)} Cr';
    if (p >= 100000) return '\u20B9${(p / 100000).toStringAsFixed(2)} L';
    if (p >= 1000) return '\u20B9${(p / 1000).toStringAsFixed(0)} K';
    return '\u20B9${p.toStringAsFixed(0)}';
  }

  @override
  Widget build(BuildContext context) {
    final title = listing['title']?.toString() ?? 'Untitled Property';
    final location = listing['location']?.toString() ??
        listing['address']?.toString() ??
        '';
    final price = listing['price'];
    final isFeatured = listing['is_featured'] == true ||
        listing['is_featured'] == 1;
    final isPremium =
        listing['is_premium'] == true || listing['is_premium'] == 1;
    final isUrgent =
        listing['is_urgent'] == true || listing['is_urgent'] == 1;
    final createdAt = listing['created_at']?.toString();
    final imageUrl = listing['main_image']?.toString() ??
        listing['image']?.toString() ??
        listing['image_url']?.toString() ??
        '';
    final propertyId = listing['id']?.toString() ?? '';
    final status = listing['status']?.toString() ?? 'active';
    final views = listing['views'] ?? 0;

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: const Color(0xFF1e293b),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFF334155)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Image + badges row
          Stack(
            children: [
              ClipRRect(
                borderRadius:
                    const BorderRadius.vertical(top: Radius.circular(16)),
                child: imageUrl.isNotEmpty
                    ? Image.network(
                        imageUrl,
                        height: 180,
                        width: double.infinity,
                        fit: BoxFit.cover,
                        errorBuilder: (_, _, _) => Container(
                          height: 180,
                          color: const Color(0xFF0f172a),
                          child: const Center(
                            child: Icon(
                              Icons.home_outlined,
                              size: 48,
                              color: Colors.white24,
                            ),
                          ),
                        ),
                      )
                    : Container(
                        height: 180,
                        color: const Color(0xFF0f172a),
                        child: const Center(
                          child: Icon(
                            Icons.home_outlined,
                            size: 48,
                            color: Colors.white24,
                          ),
                        ),
                      ),
              ),
              // Status badge
              Positioned(
                top: 12,
                left: 12,
                child: Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: status.toLowerCase() == 'active'
                        ? AppTheme.successColor
                        : status.toLowerCase() == 'sold'
                            ? Colors.grey
                            : AppTheme.warningColor,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    status.toUpperCase(),
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ),
              // Views
              Positioned(
                top: 12,
                right: 12,
                child: Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.black.withValues(alpha: 0.6),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.visibility_outlined,
                          size: 14, color: Colors.white70),
                      const SizedBox(width: 4),
                      Text(
                        '$views',
                        style: const TextStyle(
                            color: Colors.white70, fontSize: 12),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
          // Content
          Padding(
            padding: const EdgeInsets.all(14),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Title
                Text(
                  title,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 17,
                    fontWeight: FontWeight.w600,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 6),
                // Location
                if (location.isNotEmpty)
                  Row(
                    children: [
                      const Icon(Icons.location_on_outlined,
                          size: 14, color: Colors.white38),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          location,
                          style: const TextStyle(
                              color: Colors.white54, fontSize: 13),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                const SizedBox(height: 10),
                // Price
                Text(
                  _formatPrice(price),
                  style: const TextStyle(
                    color: AppTheme.accentColor,
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 10),
                // Badges row
                Wrap(
                  spacing: 8,
                  runSpacing: 6,
                  children: [
                    if (isFeatured)
                      const _Badge(
                        label: 'Featured',
                        color: Color(0xFFf59e0b),
                        icon: Icons.trending_up,
                      ),
                    if (isPremium)
                      const _Badge(
                        label: 'Premium',
                        color: Color(0xFF8b5cf6),
                        icon: Icons.star,
                      ),
                    if (isUrgent)
                      const _Badge(
                        label: 'Urgent',
                        color: Color(0xFFef4444),
                        icon: Icons.priority_high,
                      ),
                  ],
                ),
                const SizedBox(height: 12),
                // Time + action row
                Row(
                  children: [
                    if (createdAt != null)
                      Text(
                        'Posted ${_timeAgo(createdAt)}',
                        style: const TextStyle(
                            color: Colors.white38, fontSize: 12),
                      ),
                    const Spacer(),
                    // Boost button
                    OutlinedButton.icon(
                      onPressed: () {
                        if (propertyId.isNotEmpty) {
                          context.push('/listing-packages/$propertyId');
                        }
                      },
                      icon: const Icon(Icons.rocket_launch_outlined,
                          size: 16, color: AppTheme.accentColor),
                      label: const Text(
                        'Boost',
                        style: TextStyle(
                          color: AppTheme.accentColor,
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      style: OutlinedButton.styleFrom(
                        side: const BorderSide(
                            color: AppTheme.accentColor, width: 1),
                        padding: const EdgeInsets.symmetric(
                            horizontal: 14, vertical: 8),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _Badge extends StatelessWidget {
  final String label;
  final Color color;
  final IconData icon;

  const _Badge({
    required this.label,
    required this.color,
    required this.icon,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 12, color: color),
          const SizedBox(width: 4),
          Text(
            label,
            style: TextStyle(
              color: color,
              fontSize: 11,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }
}

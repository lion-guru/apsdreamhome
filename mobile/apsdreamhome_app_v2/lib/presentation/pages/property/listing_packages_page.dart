import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:dio/dio.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../data/services/payment_service.dart';

/// Provider to fetch listing packages
final listingPackagesProvider =
    FutureProvider<List<Map<String, dynamic>>>((ref) async {
  final dio = Dio(BaseOptions(baseUrl: AppConstants.baseUrl));
  final token = await ref.read(authProvider.notifier).getToken();

  try {
    final response = await dio.get(
      '${AppConstants.apiVersion}${AppConstants.listingPackagesEndpoint}',
      options: Options(headers: {'Authorization': 'Bearer $token'}),
    );
    final data = response.data['data'];
    if (data is List) return data.cast<Map<String, dynamic>>();
    return [];
  } catch (e) {
    // Return default packages if API unavailable
    return _defaultPackages();
  }
});

List<Map<String, dynamic>> _defaultPackages() {
  return [
    {
      'id': 'free',
      'name': 'Free',
      'price': 0,
      'duration_days': 0,
      'boost_score': 0,
      'features': ['Basic listing visibility', 'Standard placement'],
      'color': 'grey',
    },
    {
      'id': 'featured',
      'name': 'Featured',
      'price': 499,
      'duration_days': 30,
      'boost_score': 10,
      'features': [
        '30 days boost',
        'Featured badge',
        'Priority in search results',
        'Higher visibility',
      ],
      'color': 'amber',
    },
    {
      'id': 'premium',
      'name': 'Premium',
      'price': 1499,
      'duration_days': 30,
      'boost_score': 25,
      'features': [
        '30 days boost',
        'Premium badge',
        'Top search placement',
        'Profile trust badge',
        'Priority support',
      ],
      'color': 'purple',
    },
    {
      'id': 'urgent',
      'name': 'Urgent',
      'price': 999,
      'duration_days': 14,
      'boost_score': 15,
      'features': [
        '14 days boost',
        'Urgent sale badge',
        'Highlighted listing',
        'Push notification to buyers',
      ],
      'color': 'red',
    },
    {
      'id': 'premium_urgent',
      'name': 'Premium + Urgent',
      'price': 1999,
      'duration_days': 30,
      'boost_score': 35,
      'features': [
        '30 days boost',
        'Premium + Urgent badges',
        'Top placement in all searches',
        'Profile trust badge',
        'Push notifications to buyers',
        'Priority support',
      ],
      'color': 'gradient',
    },
  ];
}

class ListingPackagesPage extends ConsumerStatefulWidget {
  const ListingPackagesPage({super.key, required this.propertyId});

  final String propertyId;

  @override
  ConsumerState<ListingPackagesPage> createState() =>
      _ListingPackagesPageState();
}

class _ListingPackagesPageState extends ConsumerState<ListingPackagesPage> {
  int _selectedIndex = -1;
  bool _isUpgrading = false;

  @override
  Widget build(BuildContext context) {
    final packagesAsync = ref.watch(listingPackagesProvider);

    return Scaffold(
      backgroundColor: const Color(0xFF0f172a),
      appBar: AppBar(
        title: const Text('Boost Your Listing'),
        backgroundColor: const Color(0xFF0f172a),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: packagesAsync.when(
        data: (packages) => _buildContent(packages),
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
                'Failed to load packages',
                style: TextStyle(color: Colors.white70, fontSize: 16),
              ),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () => ref.refresh(listingPackagesProvider),
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
      bottomNavigationBar: _selectedIndex >= 0
          ? _buildUpgradeButton()
          : const SizedBox.shrink(),
    );
  }

  Widget _buildContent(List<Map<String, dynamic>> packages) {
    return Column(
      children: [
        // Header
        Container(
          width: double.infinity,
          padding: const EdgeInsets.fromLTRB(16, 20, 16, 24),
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              colors: [Color(0xFF1e293b), Color(0xFF0f172a)],
              begin: Alignment.topCenter,
              end: Alignment.bottomCenter,
            ),
          ),
          child: Column(
            children: [
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppTheme.accentColor.withValues(alpha: 0.1),
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.rocket_launch,
                  color: AppTheme.accentColor,
                  size: 36,
                ),
              ),
              const SizedBox(height: 16),
              const Text(
                'Boost Your Listing',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                'Property ID: ${widget.propertyId}',
                style: const TextStyle(
                  color: Colors.white54,
                  fontSize: 13,
                ),
              ),
              const SizedBox(height: 6),
              const Text(
                'Choose a package to get more views and inquiries',
                style: TextStyle(
                  color: Colors.white38,
                  fontSize: 14,
                ),
              ),
            ],
          ),
        ),
        // Package list
        Expanded(
          child: ListView.builder(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 100),
            itemCount: packages.length,
            itemBuilder: (context, index) =>
                _PackageCard(
                  pkg: packages[index],
                  isSelected: _selectedIndex == index,
                  onTap: () => setState(() => _selectedIndex = index),
                ),
          ),
        ),
      ],
    );
  }

  Widget _buildUpgradeButton() {
    return Container(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 24),
      decoration: const BoxDecoration(
        color: Color(0xFF0f172a),
        border: Border(top: BorderSide(color: Color(0xFF334155), width: 0.5)),
      ),
      child: SizedBox(
        width: double.infinity,
        child: ElevatedButton(
          onPressed: _isUpgrading ? null : _handleUpgrade,
          style: ElevatedButton.styleFrom(
            backgroundColor: AppTheme.accentColor,
            foregroundColor: Colors.black,
            padding: const EdgeInsets.symmetric(vertical: 16),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
            disabledBackgroundColor: AppTheme.accentColor.withValues(alpha: 0.5),
          ),
          child: _isUpgrading
              ? const SizedBox(
                  height: 20,
                  width: 20,
                  child: CircularProgressIndicator(
                    strokeWidth: 2,
                    color: Colors.black,
                  ),
                )
              : const Text(
                  'Upgrade Now',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w700,
                  ),
                ),
        ),
      ),
    );
  }

  Future<void> _handleUpgrade() async {
    if (_selectedIndex < 0) return;

    final dio = Dio(BaseOptions(baseUrl: AppConstants.baseUrl));
    final token = await ref.read(authProvider.notifier).getToken();
    final packages = ref.read(listingPackagesProvider).value;
    if (packages == null || _selectedIndex >= packages.length) return;

    final selectedPkg = packages[_selectedIndex];
    final packageId = selectedPkg['id']?.toString() ?? '';
    final price = (selectedPkg['price'] ?? 0) as num;
    final packageName = selectedPkg['name']?.toString() ?? 'Unknown';

    if (packageId == 'free') {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('You are already on the Free plan'),
          backgroundColor: Colors.grey,
        ),
      );
      return;
    }

    setState(() => _isUpgrading = true);

    try {
      if (price <= 0) {
        final response = await dio.post(
          '${AppConstants.apiVersion}${AppConstants.listingActivateFreeEndpoint}',
          data: {
            'property_id': widget.propertyId,
            'package_id': packageId,
          },
          options: Options(headers: {'Authorization': 'Bearer $token'}),
        );

        if (mounted) {
          final success = response.data['success'] == true;
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(
                success
                    ? 'Listing upgraded to $packageName!'
                    : response.data['message']?.toString() ??
                        'Upgrade failed',
              ),
              backgroundColor: success ? AppTheme.successColor : Colors.red,
            ),
          );
          if (success) context.go('/my-listings');
        }
      } else {
        await _startRazorpayPayment(
          dio: dio,
          token: token ?? '',
          packageId: int.tryParse(packageId) ?? 0,
          packageName: packageName,
          amount: price.toDouble(),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Upgrade failed: ${e.toString()}'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _isUpgrading = false);
    }
  }

  Future<void> _startRazorpayPayment({
    required Dio dio,
    required String token,
    required int packageId,
    required String packageName,
    required double amount,
  }) async {
    // Keep a reference for cleanup
    PaymentService? paymentService;

    paymentService = PaymentService(
      onPaymentSuccess: (result) async {
        paymentService?.dispose();
        if (!mounted) return;

        setState(() => _isUpgrading = true);

        final verified = await paymentService!.verifyPayment(
          dio: dio,
          token: token,
          orderId: (result['order_id'] ?? '').toString(),
          paymentId: (result['payment_id'] ?? '').toString(),
          signature: (result['signature'] ?? '').toString(),
        );

        if (mounted) {
          setState(() => _isUpgrading = false);
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(
                verified
                    ? 'Listing upgraded to $packageName!'
                    : 'Payment received but verification pending. Contact support.',
              ),
              backgroundColor: verified ? AppTheme.successColor : Colors.orange,
            ),
          );
          if (verified) context.go('/my-listings');
        }
      },
      onPaymentError: (error) {
        paymentService?.dispose();
        if (mounted) {
          setState(() => _isUpgrading = false);
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('Payment failed: $error'),
              backgroundColor: Colors.red,
            ),
          );
        }
      },
      onPaymentCancelled: () {
        paymentService?.dispose();
        if (mounted) {
          setState(() => _isUpgrading = false);
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Payment cancelled'),
              backgroundColor: Colors.grey,
            ),
          );
        }
      },
    );

    await paymentService.startPayment(
      dio: dio,
      token: token,
      propertyId: int.tryParse(widget.propertyId) ?? 0,
      packageId: packageId,
      packageName: packageName,
      amount: amount,
    );
  }
}

class _PackageCard extends StatelessWidget {
  final Map<String, dynamic> pkg;
  final bool isSelected;
  final VoidCallback onTap;

  const _PackageCard({
    required this.pkg,
    required this.isSelected,
    required this.onTap,
  });

  Color _badgeColor() {
    final color = pkg['color']?.toString() ?? 'grey';
    switch (color) {
      case 'amber':
        return const Color(0xFFf59e0b);
      case 'purple':
        return const Color(0xFF8b5cf6);
      case 'red':
        return const Color(0xFFef4444);
      case 'gradient':
        return const Color(0xFF8b5cf6);
      default:
        return Colors.grey;
    }
  }

  IconData _badgeIcon() {
    final color = pkg['color']?.toString() ?? 'grey';
    switch (color) {
      case 'amber':
        return Icons.trending_up;
      case 'purple':
        return Icons.star;
      case 'red':
        return Icons.priority_high;
      case 'gradient':
        return Icons.diamond;
      default:
        return Icons.free_cancellation;
    }
  }

  String _formatPrice(dynamic price) {
    final p = double.tryParse(price?.toString() ?? '0') ?? 0;
    if (p == 0) return 'FREE';
    return '\u20B9${p.toStringAsFixed(0)}';
  }

  @override
  Widget build(BuildContext context) {
    final badgeCol = _badgeColor();
    final price = (pkg['price'] ?? 0) as num;
    final duration = (pkg['duration_days'] ?? 0) as num;
    final boostScore = (pkg['boost_score'] ?? 0) as num;
    final features = (pkg['features'] as List<dynamic>? ?? [])
        .map((f) => f.toString())
        .toList();
    final name = pkg['name']?.toString() ?? 'Unknown';

    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        margin: const EdgeInsets.only(bottom: 12),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: const Color(0xFF1e293b),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: isSelected ? const Color(0xFF3b82f6) : const Color(0xFF334155),
            width: isSelected ? 2 : 1,
          ),
          boxShadow: isSelected
              ? [
                  BoxShadow(
                    color: const Color(0xFF3b82f6).withValues(alpha: 0.15),
                    blurRadius: 12,
                    spreadRadius: 2,
                  ),
                ]
              : null,
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header row: badge + price
            Row(
              children: [
                // Badge
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                  decoration: BoxDecoration(
                    color: badgeCol.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: badgeCol.withValues(alpha: 0.3)),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(_badgeIcon(), size: 14, color: badgeCol),
                      const SizedBox(width: 6),
                      Text(
                        name,
                        style: TextStyle(
                          color: badgeCol,
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ],
                  ),
                ),
                const Spacer(),
                // Price
                Text(
                  _formatPrice(price),
                  style: TextStyle(
                    color: price == 0 ? Colors.white54 : AppTheme.accentColor,
                    fontSize: 22,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 14),

            // Duration + Boost score
            Row(
              children: [
                _InfoChip(
                  icon: Icons.access_time,
                  label: duration > 0 ? '$duration days' : 'No boost',
                ),
                const SizedBox(width: 10),
                _InfoChip(
                  icon: Icons.speed,
                  label: boostScore > 0 ? '+$boostScore boost' : 'Standard',
                ),
              ],
            ),
            const SizedBox(height: 14),

            // Features checklist
            ...features.map(
              (f) => Padding(
                padding: const EdgeInsets.only(bottom: 6),
                child: Row(
                  children: [
                    Icon(
                      Icons.check_circle,
                      size: 16,
                      color: price == 0 ? Colors.white38 : badgeCol,
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        f,
                        style: const TextStyle(
                          color: Colors.white70,
                          fontSize: 13,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),

            // Selection indicator
            if (isSelected)
              const Padding(
                padding: EdgeInsets.only(top: 10),
                child: Row(
                  children: [
                    Icon(Icons.check_circle, size: 18, color: Color(0xFF3b82f6)),
                    SizedBox(width: 6),
                    Text(
                      'Selected',
                      style: TextStyle(
                        color: Color(0xFF3b82f6),
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
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

class _InfoChip extends StatelessWidget {
  final IconData icon;
  final String label;

  const _InfoChip({required this.icon, required this.label});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: const Color(0xFF0f172a),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: const Color(0xFF334155)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 13, color: Colors.white54),
          const SizedBox(width: 5),
          Text(
            label,
            style: const TextStyle(
              color: Colors.white54,
              fontSize: 12,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }
}

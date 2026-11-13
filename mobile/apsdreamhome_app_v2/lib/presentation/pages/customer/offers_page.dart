import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../data/repositories/kyc_repository_provider.dart';
import '../../widgets/app_widgets.dart';
import '../../widgets/glass_card.dart';

/// Customer Offers Page - live festival promotional discounts on properties.
/// Shows real promo codes from the backend with 1-tap copy + server-validated
/// discounts that are consumed on the booking page.
class OffersPage extends ConsumerWidget {
  const OffersPage({super.key});

  static String _discountBadge(Map<String, dynamic> o) {
    final type = '${o['discount_type'] ?? 'percentage'}';
    final value =
        double.tryParse('${o['discount_value'] ?? ''}') ?? 0.0;
    final pct = double.tryParse('${o['discount_percentage'] ?? ''}') ?? 0.0;
    switch (type) {
      case 'flat_amount':
        return '₹${value.toStringAsFixed(0)}\nOFF';
      case 'per_sqft':
        return '₹${value.toStringAsFixed(0)}\n/sqft';
      case 'cashback_wallet':
        return '₹${value.toStringAsFixed(0)}\nCashback';
      case 'gift_item':
        return '🎁';
      case 'percentage':
      default:
        final v = pct > 0 ? pct : value;
        return '${v.toStringAsFixed(0)}%\nOFF';
    }
  }

  static int? _daysLeft(dynamic validUntil) {
    final raw = '$validUntil'.trim();
    if (raw.isEmpty || raw == 'null') return null;
    final expiry = DateTime.tryParse(raw);
    if (expiry == null) return null;
    final now = DateTime.now();
    final day = DateTime(expiry.year, expiry.month, expiry.day);
    final today = DateTime(now.year, now.month, now.day);
    return day.difference(today).inDays;
  }

  static String _expiryLabel(dynamic validUntil) {
    final raw = '$validUntil'.trim();
    final days = _daysLeft(validUntil);
    if (days == null) return raw.isEmpty || raw == 'null' ? 'No expiry date' : 'Valid till $raw';
    if (days < 0) return 'Expired';
    if (days == 0) return 'Ends today!';
    if (days == 1) return 'Ends tomorrow';
    return 'Ends in $days days';
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final offersAsync = ref.watch(_promotionalOffersProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Festive Offers'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded),
          onPressed: () => context.canPop() ? context.pop() : context.go('/home'),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            onPressed: () => ref.invalidate(_promotionalOffersProvider),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async => ref.invalidate(_promotionalOffersProvider),
        color: AppTheme.primaryColor,
        child: offersAsync.when(
          data: (offers) => offers.isEmpty
              ? ListView(
                  children: const [
                    Padding(
                      padding: EdgeInsets.all(48),
                      child: Center(
                        child: Text(
                          'No live festive offers right now -\ncheck back soon.',
                          textAlign: TextAlign.center,
                        ),
                      ),
                    ),
                  ],
                )
              : ListView(
                  padding: const EdgeInsets.all(16),
                  children: [
                    const _FestivalHeroBanner(),
                    const SizedBox(height: 12),
                    ...offers.map((o) => _OfferCard(offer: o)),
                  ],
                ),
          loading: () => const Center(child: CircularProgressIndicator()),
          error: (error, stack) => AppWidgets.errorWidget(
            message: error.toString(),
            onRetry: () => ref.invalidate(_promotionalOffersProvider),
          ),
        ),
      ),
    );
  }
}

class _FestivalHeroBanner extends StatelessWidget {
  const _FestivalHeroBanner();

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 4),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFFB33B0F), Color(0xFFE08A1E)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(14),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.18),
              borderRadius: BorderRadius.circular(10),
            ),
            child: const Icon(Icons.celebration,
                color: Colors.white, size: 26),
          ),
          const SizedBox(width: 12),
          const Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Festival Special Deals',
                  style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.bold,
                    fontSize: 16,
                  ),
                ),
                SizedBox(height: 2),
                Text(
                  'Share the code with family - booking-time concessions up to ₹21,000+',
                  style: TextStyle(color: Colors.white70, fontSize: 11),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _OfferCard extends StatelessWidget {
  final Map<String, dynamic> offer;

  const _OfferCard({required this.offer});

  void _copyCode(BuildContext context, String code) {
    Clipboard.setData(ClipboardData(text: code));
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Promo code "$code" copied - use it at booking!'),
        behavior: SnackBarBehavior.floating,
        duration: const Duration(seconds: 2),
        action: SnackBarAction(
          label: 'BOOK',
          onPressed: () => context.go('/colonies'),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final code = '${offer['promo_code'] ?? ''}'.trim();
    final minBooking = double.tryParse('${offer['min_booking_amount'] ?? ''}') ?? 0.0;
    final festival = '${offer['festival_name'] ?? 'Festival Special'}'.trim();
    final redeemed = int.tryParse('${offer['times_redeemed'] ?? ''}') ?? 0;
    final maxRedemptions = int.tryParse('${offer['max_redemptions'] ?? ''}') ?? 0;

    return GlassCard(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            Container(
              width: 64,
              height: 64,
              decoration: BoxDecoration(
                color: AppTheme.primaryColor.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Center(
                child: Text(
                  OffersPage._discountBadge(offer),
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.bold,
                    height: 1.05,
                    color: AppTheme.primaryColor,
                  ),
                ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          '${offer['title'] ?? ''}',
                          style: const TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                      Container(
                        padding:
                            const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                        decoration: BoxDecoration(
                          color: AppTheme.secondaryColor.withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Text(
                          festival,
                          style: const TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            color: AppTheme.secondaryColor,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  if ('${offer['description'] ?? ''}'.trim().isNotEmpty)
                    Text(
                      '${offer['description']}',
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        color: Colors.grey.shade600,
                        fontSize: 12,
                      ),
                    ),
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      Icon(
                        Icons.schedule,
                        size: 12,
                        color: (OffersPage._daysLeft(offer['valid_until']) ?? 99) <= 3
                            ? Colors.red
                            : AppTheme.primaryColor,
                      ),
                      const SizedBox(width: 4),
                      Flexible(
                        child: Text(
                          OffersPage._expiryLabel(offer['valid_until']),
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                            color: (OffersPage._daysLeft(offer['valid_until']) ?? 99) <= 3
                                ? Colors.red
                                : Colors.grey.shade600,
                          ),
                        ),
                      ),
                      if (minBooking > 0) ...[
                        const SizedBox(width: 8),
                        Icon(Icons.currency_rupee,
                            size: 12, color: Colors.grey.shade500),
                        Text(
                          'Min ₹${minBooking.toStringAsFixed(0)}',
                          style: TextStyle(
                              fontSize: 11, color: Colors.grey.shade600),
                        ),
                      ],
                    ],
                  ),
                  if (maxRedemptions > 0)
                    Padding(
                      padding: const EdgeInsets.only(top: 4),
                      child: Text(
                        '$redeemed / $maxRedemptions redeemed',
                        style: TextStyle(
                            fontSize: 10, color: Colors.grey.shade500),
                      ),
                    ),
                  const SizedBox(height: 8),
                  if (code.isNotEmpty)
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 10, vertical: 6),
                          decoration: BoxDecoration(
                            color: AppTheme.primaryColor.withValues(alpha: 0.08),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(
                              color: AppTheme.primaryColor.withValues(alpha: 0.4),
                              style: BorderStyle.solid,
                            ),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.confirmation_number_outlined,
                                  size: 14, color: AppTheme.primaryColor),
                              const SizedBox(width: 6),
                              Text(
                                code,
                                style: const TextStyle(
                                  fontFamily: 'monospace',
                                  fontWeight: FontWeight.bold,
                                  letterSpacing: 1.2,
                                  fontSize: 13,
                                  color: AppTheme.primaryColor,
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 8),
                        InkWell(
                          onTap: () => _copyCode(context, code),
                          borderRadius: BorderRadius.circular(8),
                          child: Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 10, vertical: 6),
                            decoration: BoxDecoration(
                              color: AppTheme.primaryColor,
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: const Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(Icons.copy,
                                    size: 14, color: Colors.white),
                                SizedBox(width: 4),
                                Text(
                                  'COPY',
                                  style: TextStyle(
                                    color: Colors.white,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 11,
                                  ),
                                ),
                              ],
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
      ),
    );
  }
}

final _promotionalOffersProvider =
    FutureProvider<List<Map<String, dynamic>>>((ref) async {
  final api = ref.watch(apiServiceProvider);
  return api.getPromotionalOffers();
});

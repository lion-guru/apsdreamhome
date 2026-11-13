import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:share_plus/share_plus.dart';

import '../../../core/theme/app_theme.dart';
import '../../../data/repositories/kyc_repository_provider.dart';
import '../../widgets/app_widgets.dart';
import '../../widgets/glass_card.dart';

final _inr = NumberFormat('#,##,###');

/// Associate Offers Page - live company offer campaigns with personal progress.
class AssociateOffersPage extends ConsumerWidget {
  const AssociateOffersPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final offersAsync = ref.watch(_associateOffersProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Company Offers'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded),
          onPressed: () => context.pop(),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            onPressed: () => ref.invalidate(_associateOffersProvider),
          ),
        ],
      ),
      body: Column(
        children: [
          // Customer promo codes the associate can share to drive bookings
          const _CustomerPromoSection(),
          Expanded(
            child: RefreshIndicator(
              onRefresh: () async => ref.invalidate(_associateOffersProvider),
              color: AppTheme.primaryColor,
              child: offersAsync.when(
          data: (offers) => offers.isEmpty
              ? ListView(
                  children: const [
                    Padding(
                      padding: EdgeInsets.all(48),
                      child: Center(
                          child: Text('No live offers right now ΓÇö check back soon.')),
                    ),
                  ],
                )
              : ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: offers.length,
                  itemBuilder: (context, i) => _OfferCard(offer: offers[i]),
                ),
          loading: () => const Center(child: CircularProgressIndicator()),
          error: (error, stack) => AppWidgets.errorWidget(
            message: error.toString(),
            onRetry: () => ref.invalidate(_associateOffersProvider),
          ),
        ),
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

  @override
  Widget build(BuildContext context) {
    final target = double.tryParse('${offer['criteria_value'] ?? 0}') ?? 0;
    final progress = offer['progress'] as Map? ?? {};
    final val = double.tryParse('${progress['value'] ?? 0}') ?? 0;
    final isCount = (offer['criteria_type'] ?? '') == 'booking_count';
    final pct = target > 0 ? (val / target).clamp(0.0, 1.0) : 0.0;
    final achieved = offer['achieved'] == true;
    final rewardType = '${offer['reward_type'] ?? ''}';
    final rewardText = rewardType == 'commission_boost_pct'
        ? '+${offer['reward_value']} % commission'
        : rewardType == 'gift'
            ? 'Gift: ${offer['reward_value']}'
            : 'Γé╣${_inr.format(double.tryParse('${offer['reward_value'] ?? 0}') ?? 0)} bonus';

    return GlassCard(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    '${offer['title'] ?? ''}',
                    style: const TextStyle(
                        fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                ),
                if (achieved)
                  Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.green,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Text('Achieved',
                        style: TextStyle(color: Colors.white, fontSize: 12)),
                  ),
              ],
            ),
            if ('${offer['description'] ?? ''}'.isNotEmpty)
              Padding(
                padding: const EdgeInsets.only(top: 4),
                child: Text('${offer['description']}',
                    style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
              ),
            const SizedBox(height: 8),
            Text('Reward: $rewardText',
                style: const TextStyle(fontWeight: FontWeight.w600)),
            Text(
              'Valid ${offer['starts_at'] ?? ''} ΓåÆ ${offer['ends_at'] ?? ''}',
              style: TextStyle(color: Colors.grey.shade600, fontSize: 12),
            ),
            const SizedBox(height: 8),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text('Your progress',
                    style: TextStyle(fontSize: 12)),
                Text(
                  isCount
                      ? '${val.toInt()} / ${target.toInt()} bookings'
                      : 'Γé╣${_inr.format(val)} / Γé╣${_inr.format(target)}',
                  style: const TextStyle(
                      fontSize: 12, fontWeight: FontWeight.bold),
                ),
              ],
            ),
            const SizedBox(height: 4),
            ClipRRect(
              borderRadius: BorderRadius.circular(8),
              child: LinearProgressIndicator(
                value: pct,
                minHeight: 10,
                backgroundColor: Colors.grey.shade200,
                valueColor: AlwaysStoppedAnimation<Color>(
                    achieved ? Colors.green : AppTheme.primaryColor),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

final _associateOffersProvider =
    FutureProvider<List<Map<String, dynamic>>>((ref) async {
  final api = ref.watch(apiServiceProvider);
  return api.getAssociateOffers();
});

final _customerPromoProvider =
    FutureProvider<List<Map<String, dynamic>>>((ref) async {
  final api = ref.watch(apiServiceProvider);
  return api.getPromotionalOffers();
});

/// Customer promo codes the associate can share - drives bookings with the
/// customer's festival concession (server-validated at booking time).
class _CustomerPromoSection extends ConsumerWidget {
  const _CustomerPromoSection();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final promosAsync = ref.watch(_customerPromoProvider);
    return promosAsync.when(
      data: (promos) {
        final withCodes = promos
            .where((p) => '${p['promo_code'] ?? ''}'.trim().isNotEmpty)
            .toList();
        if (withCodes.isEmpty) return const SizedBox.shrink();
        return Container(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  const Icon(Icons.confirmation_number,
                      size: 16, color: AppTheme.secondaryColor),
                  const SizedBox(width: 6),
                  Text(
                    'CUSTOMER PROMO CODES - Share & Earn',
                    style: Theme.of(context).textTheme.labelSmall?.copyWith(
                          fontWeight: FontWeight.bold,
                          letterSpacing: 0.8,
                          color: AppTheme.secondaryColor,
                        ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              SizedBox(
                height: 132,
                child: ListView.separated(
                  scrollDirection: Axis.horizontal,
                  itemCount: withCodes.length,
                  separatorBuilder: (context, i) =>
                      const SizedBox(width: 10),
                  itemBuilder: (context, i) =>
                      _PromoShareCard(offer: withCodes[i]),
                ),
              ),
              const SizedBox(height: 4),
              const Divider(),
            ],
          ),
        );
      },
      loading: () => const SizedBox(
        height: 120,
        child: Center(child: CircularProgressIndicator()),
      ),
      error: (error, stack) => const SizedBox.shrink(),
    );
  }
}

class _PromoShareCard extends StatelessWidget {
  final Map<String, dynamic> offer;

  const _PromoShareCard({required this.offer});

  static String _discountText(Map<String, dynamic> o) {
    final type = '${o['discount_type'] ?? 'percentage'}';
    final value = double.tryParse('${o['discount_value'] ?? ''}') ?? 0.0;
    final pct = double.tryParse('${o['discount_percentage'] ?? ''}') ?? 0.0;
    switch (type) {
      case 'flat_amount':
        return '₹${value.toStringAsFixed(0)} OFF';
      case 'per_sqft':
        return '₹${value.toStringAsFixed(0)}/sqft';
      case 'cashback_wallet':
        return '₹${value.toStringAsFixed(0)} cashback';
      case 'gift_item':
        return 'Gift with booking';
      case 'percentage':
      default:
        final v = pct > 0 ? pct : value;
        return '${v.toStringAsFixed(0)}% OFF';
    }
  }

  @override
  Widget build(BuildContext context) {
    final code = '${offer['promo_code'] ?? ''}'.trim();
    final discount = _discountText(offer);
    final festival = '${offer['festival_name'] ?? 'Festival Special'}'.trim();
    final minBooking =
        double.tryParse('${offer['min_booking_amount'] ?? ''}') ?? 0.0;

    final shareText = '🎁 APS Dream Homes - $festival\n\n'
        'Use code $code to save ($discount) on your plot booking at any APS township.\n'
        '${minBooking > 0 ? 'Min booking ₹${minBooking.toStringAsFixed(0)}. ' : ''}'
        'Offers: https://apsdreamhome.com/offers';

    return Container(
      width: 240,
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF1B3A2F), Color(0xFF2E5E4E)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(14),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            discount,
            style: const TextStyle(
              color: Colors.amber,
              fontWeight: FontWeight.bold,
              fontSize: 15,
            ),
          ),
          Text(
            '${offer['title'] ?? ''}',
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(color: Colors.white, fontSize: 11),
          ),
          const Spacer(),
          Row(
            children: [
              Expanded(
                child: Container(
                  padding: const EdgeInsets.symmetric(
                      horizontal: 8, vertical: 6),
                  decoration: BoxDecoration(
                    color: Colors.white10,
                    borderRadius: BorderRadius.circular(6),
                    border:
                        Border.all(color: Colors.amber.withValues(alpha: 0.6)),
                  ),
                  child: Text(
                    code,
                    textAlign: TextAlign.center,
                    style: const TextStyle(
                      fontFamily: 'monospace',
                      color: Colors.white,
                      fontWeight: FontWeight.bold,
                      letterSpacing: 1.5,
                      fontSize: 12,
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 6),
              IconButton(
                visualDensity: VisualDensity.compact,
                icon: const Icon(Icons.copy, size: 18, color: Colors.white70),
                onPressed: () {
                  Clipboard.setData(ClipboardData(text: code));
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text('Code $code copied'),
                      duration: const Duration(milliseconds: 1500),
                      behavior: SnackBarBehavior.floating,
                    ),
                  );
                },
              ),
              IconButton(
                visualDensity: VisualDensity.compact,
                icon: const Icon(Icons.share, size: 18, color: Colors.white),
                onPressed: () => SharePlus.instance.share(ShareParams(text: shareText)),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

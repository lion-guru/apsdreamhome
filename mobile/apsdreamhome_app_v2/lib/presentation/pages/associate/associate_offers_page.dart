import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

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
      body: RefreshIndicator(
        onRefresh: () async => ref.invalidate(_associateOffersProvider),
        color: AppTheme.primaryColor,
        child: offersAsync.when(
          data: (offers) => offers.isEmpty
              ? ListView(
                  children: const [
                    Padding(
                      padding: EdgeInsets.all(48),
                      child: Center(
                          child: Text('No live offers right now — check back soon.')),
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
            : '₹${_inr.format(double.tryParse('${offer['reward_value'] ?? 0}') ?? 0)} bonus';

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
              'Valid ${offer['starts_at'] ?? ''} → ${offer['ends_at'] ?? ''}',
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
                      : '₹${_inr.format(val)} / ₹${_inr.format(target)}',
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

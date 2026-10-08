import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../data/repositories/kyc_repository_provider.dart';
import '../../widgets/app_widgets.dart';
import '../../widgets/glass_card.dart';

/// Customer Offers Page - live promotional discounts on properties.
class OffersPage extends ConsumerWidget {
  const OffersPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final offersAsync = ref.watch(_promotionalOffersProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Offers'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded),
          onPressed: () => context.pop(),
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
                              'No live offers right now — check back soon.')),
                    ),
                  ],
                )
              : ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: offers.length,
                  itemBuilder: (context, i) {
                    final o = offers[i];
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
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Text(
                                    '${o['discount_percentage'] ?? 0}%',
                                    style: TextStyle(
                                      fontSize: 18,
                                      fontWeight: FontWeight.bold,
                                      color: AppTheme.primaryColor,
                                    ),
                                  ),
                                  const Text('OFF',
                                      style: TextStyle(fontSize: 11)),
                                ],
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text('${o['title'] ?? ''}',
                                      style: const TextStyle(
                                          fontSize: 15,
                                          fontWeight: FontWeight.bold)),
                                  if ('${o['description'] ?? ''}'.isNotEmpty)
                                    Text('${o['description']}',
                                        maxLines: 2,
                                        overflow: TextOverflow.ellipsis,
                                        style: TextStyle(
                                            color: Colors.grey.shade600,
                                            fontSize: 12)),
                                  Text(
                                    'Valid till ${o['valid_until'] ?? ''}',
                                    style: TextStyle(
                                        color: Colors.grey.shade600,
                                        fontSize: 12),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    );
                  },
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

final _promotionalOffersProvider =
    FutureProvider<List<Map<String, dynamic>>>((ref) async {
  final api = ref.watch(apiServiceProvider);
  return api.getPromotionalOffers();
});

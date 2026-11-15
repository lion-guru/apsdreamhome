import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../data/services/colony_service.dart';
import '../../widgets/app_widgets.dart';
import '../../widgets/glass_card.dart';

class PlotsPage extends ConsumerStatefulWidget {
  const PlotsPage({super.key});

  @override
  ConsumerState<PlotsPage> createState() => _PlotsPageState();
}

class _PlotsPageState extends ConsumerState<PlotsPage> {

  Widget _buildBody() {
    return RefreshIndicator(
      onRefresh: () async {
        ref.invalidate(coloniesProvider);
      },
      child: Column(
        children: [
          _buildLegend(),
          Expanded(child: _buildAllPlotsList()),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    // Shell tab: body only (shell provides Scaffold/AppBar)
    return _buildBody();
  }

  Widget _buildLegend() {
    final items = [
      {'color': AppTheme.plotAvailable, 'label': 'Available'},
      {'color': AppTheme.plotHold, 'label': 'Hold'},
      {'color': AppTheme.plotBooked, 'label': 'Booked'},
      {'color': AppTheme.plotSold, 'label': 'Sold'},
    ];

    return GlassCard(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceAround,
        children: items.map((item) {
          return Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 14,
                height: 14,
                decoration: BoxDecoration(
                  color: item['color'] as Color,
                  borderRadius: BorderRadius.circular(4),
                  border: Border.all(color: Colors.white, width: 1),
                ),
              ),
              const SizedBox(width: 6),
              Text(
                item['label'] as String,
                style: const TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w600,
                  color: AppTheme.textSecondaryLight,
                ),
              ),
            ],
          );
        }).toList(),
      ),
    );
  }

  Widget _buildAllPlotsList() {
    final coloniesAsync = ref.watch(coloniesProvider);
    return coloniesAsync.when(
      data: (colonies) {
        if (colonies.isEmpty) {
          return AppWidgets.emptyState(
            title: 'No Colonies Found',
            subtitle: 'No colonies available at the moment',
          );
        }
        return RefreshIndicator(
          onRefresh: () async => ref.invalidate(coloniesProvider),
          child: ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: colonies.length,
            itemBuilder: (context, index) {
              final colony = colonies[index];
              return GestureDetector(
                onTap: () => context.push('/plots?colonyId=${colony.id}'),
                child: Container(
                  margin: const EdgeInsets.only(bottom: 12),
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: const Color(0xFFE8DFD0), width: 1.2),
                    boxShadow: [
                      BoxShadow(
                        color: const Color(0xFF0B194C).withValues(alpha: 0.05),
                        blurRadius: 10,
                        offset: const Offset(0, 3),
                      ),
                    ],
                  ),
                  child: Row(
                    children: [
                      ClipRRect(
                        borderRadius: BorderRadius.circular(12),
                        child: SizedBox(
                          width: 68,
                          height: 68,
                          child: Image.network(
                            colony.displayImages.first,
                            fit: BoxFit.cover,
                            errorBuilder: (_, _, _) => Image.network(
                              colony.luxuryHeroImage,
                              fit: BoxFit.cover,
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              colony.name,
                              style: const TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 15,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              '${colony.totalPlots} plots · ${colony.availablePlots} available',
                              style: TextStyle(
                                color: Colors.grey.shade600,
                                fontSize: 13,
                              ),
                            ),
                            if (colony.district.isNotEmpty)
                              Text(
                                colony.district,
                                style: TextStyle(
                                  color: Colors.grey.shade500,
                                  fontSize: 12,
                                ),
                              ),
                          ],
                        ),
                      ),
                      Icon(
                        Icons.arrow_forward_ios,
                        size: 16,
                        color: Colors.grey.shade400,
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
        );
      },
      loading: () => const Center(child: CircularProgressIndicator()),
      error: (error, _) => AppWidgets.emptyState(
        title: 'Error loading colonies',
        subtitle: '$error',
      ),
    );
  }


}

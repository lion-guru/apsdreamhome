import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../data/services/colony_service.dart';
import '../../../data/models/plot_model.dart';
import '../../widgets/app_widgets.dart';

/// Provider to fetch plot details by ID
final plotDetailProvider = FutureProvider.family<PlotModel?, String>((ref, plotId) async {
  final service = ref.read(colonyServiceProvider);
  return service.getPlotById(plotId);
});

class PlotDetailPage extends ConsumerWidget {
  final String plotId;

  const PlotDetailPage({super.key, required this.plotId});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final plotAsync = ref.watch(plotDetailProvider(plotId));

    return Scaffold(
      appBar: AppBar(
        title: const Text('Plot Details'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
      ),
      body: plotAsync.when(
        data: (plot) {
          if (plot == null) {
            return _buildNotFound();
          }
          return _buildPlotDetail(context, plot);
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (err, _) => Center(
          child: AppWidgets.errorWidget(
            message: 'Failed to load plot details',
            onRetry: () => ref.refresh(plotDetailProvider(plotId)),
          ),
        ),
      ),
    );
  }

  Widget _buildNotFound() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.landscape, size: 80, color: Colors.grey.shade400),
          const SizedBox(height: 16),
          Text('Plot not found', style: TextStyle(color: Colors.grey.shade600, fontSize: 18)),
          const SizedBox(height: 8),
          Text('This plot may no longer exist.', style: TextStyle(color: Colors.grey.shade500)),
        ],
      ),
    );
  }

  Widget _buildPlotDetail(BuildContext context, PlotModel plot) {
    final displayPrice = plot.finalPrice ?? plot.totalPrice;

    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Plot Image Placeholder + Status Badge
          Container(
            width: double.infinity,
            height: 200,
            color: AppTheme.primaryColor.withValues(alpha: 0.08),
            child: Stack(
              children: [
                Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.landscape, size: 60, color: AppTheme.primaryColor.withValues(alpha: 0.4)),
                      const SizedBox(height: 8),
                      Text(
                        'Plot ${plot.plotNumber}',
                        style: TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                          color: AppTheme.primaryColor.withValues(alpha: 0.6),
                        ),
                      ),
                    ],
                  ),
                ),
                Positioned(
                  top: 12,
                  right: 12,
                  child: _buildStatusBadge(plot.status),
                ),
                if (plot.hasPremiumLocation)
                  Positioned(
                    top: 12,
                    left: 12,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: AppTheme.accentColor,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.star, color: Colors.white, size: 12),
                          SizedBox(width: 4),
                          Text('Premium', style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold)),
                        ],
                      ),
                    ),
                  ),
              ],
            ),
          ),

          // Price Card
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(20),
            color: Colors.white,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          '₹${_formatPrice(displayPrice)}',
                          style: const TextStyle(fontSize: 28, fontWeight: FontWeight.bold, color: AppTheme.primaryColor),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          '₹${plot.pricePerSqft.toStringAsFixed(0)}/sqft',
                          style: TextStyle(fontSize: 13, color: Colors.grey.shade600),
                        ),
                      ],
                    ),
                    Text(
                      '${plot.areaSqft.toStringAsFixed(0)} sqft',
                      style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
                    ),
                  ],
                ),
              ],
            ),
          ),

          const SizedBox(height: 8),

          // Colony & Location
          _buildInfoSection('Location', [
            _buildInfoRow(Icons.location_on_outlined, 'Colony', plot.colonyName),
            _buildInfoRow(Icons.tag, 'Plot No.', plot.plotNumber),
          ]),

          // Plot Specifications
          _buildInfoSection('Specifications', [
            _buildInfoRow(Icons.straighten, 'Area', '${plot.areaSqft.toStringAsFixed(0)} sqft'),
            if (plot.frontWidth != null)
              _buildInfoRow(Icons.swap_horiz, 'Front Width', '${plot.frontWidth!.toStringAsFixed(0)} ft'),
            if (plot.depth != null)
              _buildInfoRow(Icons.swap_vert, 'Depth', '${plot.depth!.toStringAsFixed(0)} ft'),
            _buildInfoRow(Icons.explore, 'Facing', plot.facing),
            if (plot.shape != null && plot.shape!.isNotEmpty)
              _buildInfoRow(Icons.category, 'Shape', plot.shape!),
          ]),

          // Features
          _buildFeaturesSection(plot),

          // Pricing Breakdown
          _buildPricingBreakdown(plot, displayPrice),

          // Action Buttons
          if (plot.isAvailable)
            Padding(
              padding: const EdgeInsets.all(16),
              child: SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton.icon(
                  onPressed: () => context.push('/booking/${plot.id}'),
                  icon: const Icon(Icons.shopping_cart_outlined),
                  label: const Text('Book This Plot', style: TextStyle(fontSize: 16)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTheme.primaryColor,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
              ),
            ),

          if (plot.isBooked)
            Padding(
              padding: const EdgeInsets.all(16),
              child: Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppTheme.infoColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppTheme.infoColor.withValues(alpha: 0.3)),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.info_outline, color: AppTheme.infoColor),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Plot is Booked', style: TextStyle(fontWeight: FontWeight.bold, color: AppTheme.infoColor)),
                          if (plot.bookedByName != null)
                            Text('Booked by: ${plot.bookedByName}', style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),

          const SizedBox(height: 32),
        ],
      ),
    );
  }

  Widget _buildStatusBadge(String status) {
    Color color;
    String label;
    switch (status) {
      case 'available':
        color = AppTheme.successColor;
        label = 'Available';
        break;
      case 'hold':
        color = AppTheme.warningColor;
        label = 'On Hold';
        break;
      case 'booked':
        color = AppTheme.infoColor;
        label = 'Booked';
        break;
      case 'sold':
        color = AppTheme.errorColor;
        label = 'Sold';
        break;
      default:
        color = Colors.grey;
        label = status.toUpperCase();
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Text(label, style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold)),
    );
  }

  Widget _buildInfoSection(String title, List<Widget> children) {
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.symmetric(horizontal: 0, vertical: 4),
      padding: const EdgeInsets.all(16),
      color: Colors.white,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
          const SizedBox(height: 12),
          ...children,
        ],
      ),
    );
  }

  Widget _buildInfoRow(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Row(
        children: [
          Icon(icon, size: 18, color: Colors.grey.shade500),
          const SizedBox(width: 10),
          Text('$label: ', style: TextStyle(fontSize: 13, color: Colors.grey.shade600)),
          Expanded(
            child: Text(value, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
          ),
        ],
      ),
    );
  }

  Widget _buildFeaturesSection(PlotModel plot) {
    final features = <MapEntry<String, bool>>[
      MapEntry('Corner Plot', plot.isCorner ?? false),
      MapEntry('Park Facing', plot.isParkFacing ?? false),
      MapEntry('Main Road Facing', plot.isMainRoadFacing ?? false),
    ];

    final activeFeatures = features.where((f) => f.value).toList();
    if (activeFeatures.isEmpty) return const SizedBox.shrink();

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      color: Colors.white,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Features', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
          const SizedBox(height: 12),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: activeFeatures.map((f) {
              return Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: AppTheme.successColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: AppTheme.successColor.withValues(alpha: 0.3)),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.check_circle, size: 14, color: AppTheme.successColor),
                    const SizedBox(width: 6),
                    Text(f.key, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppTheme.successColor)),
                  ],
                ),
              );
            }).toList(),
          ),
        ],
      ),
    );
  }

  Widget _buildPricingBreakdown(PlotModel plot, double displayPrice) {
    final premiums = <MapEntry<String, double>>[];
    if (plot.isCorner == true && plot.cornerPremium != null) {
      premiums.add(MapEntry('Corner Premium', plot.cornerPremium!));
    }
    if (plot.isParkFacing == true && plot.parkFacingPremium != null) {
      premiums.add(MapEntry('Park Facing Premium', plot.parkFacingPremium!));
    }
    if (plot.isMainRoadFacing == true && plot.mainRoadPremium != null) {
      premiums.add(MapEntry('Road Facing Premium', plot.mainRoadPremium!));
    }

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      color: Colors.white,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Price Breakdown', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
          const SizedBox(height: 12),
          _buildPriceRow('Base Price', plot.basePrice),
          ...premiums.map((p) => _buildPriceRow(p.key, p.value)),
          const Divider(height: 24),
          _buildPriceRow('Total Price', displayPrice, isBold: true, color: AppTheme.primaryColor),
        ],
      ),
    );
  }

  Widget _buildPriceRow(String label, double amount, {bool isBold = false, Color? color}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Flexible(child: Text(label, style: TextStyle(fontSize: isBold ? 14 : 13, fontWeight: isBold ? FontWeight.bold : FontWeight.normal, color: color ?? Colors.black87))),
          Text('₹${_formatPrice(amount)}', style: TextStyle(fontSize: isBold ? 16 : 14, fontWeight: isBold ? FontWeight.bold : FontWeight.w600, color: color ?? Colors.black87)),
        ],
      ),
    );
  }

  String _formatPrice(double price) {
    if (price >= 10000000) {
      return '${(price / 10000000).toStringAsFixed(2)} Cr';
    } else if (price >= 100000) {
      return '${(price / 100000).toStringAsFixed(2)} L';
    }
    return price.toStringAsFixed(0);
  }
}

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/constants/app_constants.dart';
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
      backgroundColor: const Color(0xFFFAF7F0),
      appBar: AppBar(
        title: const Text('Plot Details'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        actions: [
          plotAsync.maybeWhen(
            data: (plot) {
              if (plot == null) return const SizedBox.shrink();
              return IconButton(
                icon: const Icon(Icons.picture_as_pdf_outlined),
                tooltip: 'Cost Sheet (PDF)',
                onPressed: () {
                  final pdfUrl = '${AppConstants.baseUrl}/plots/${plot.id}/quotation-pdf';
                  launchUrl(Uri.parse(pdfUrl), mode: LaunchMode.externalApplication);
                },
              );
            },
            orElse: () => const SizedBox.shrink(),
          ),
        ],
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
      bottomNavigationBar: plotAsync.when(
        data: (plot) {
          if (plot == null) return const SizedBox.shrink();
          return Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            decoration: BoxDecoration(
              color: Colors.white,
              border: const Border(
                top: BorderSide(color: Color(0xFFE8DFD0), width: 1.2),
              ),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF0B194C).withValues(alpha: 0.08),
                  blurRadius: 10,
                  offset: const Offset(0, -3),
                ),
              ],
            ),
            child: SafeArea(
              child: Row(
                children: [
                  Expanded(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Total Deal Price',
                          style: TextStyle(
                            fontSize: 11,
                            color: Colors.grey.shade600,
                          ),
                        ),
                        Text(
                          plot.formattedPrice,
                          style: const TextStyle(
                            fontSize: 20,
                            fontWeight: FontWeight.bold,
                            color: AppTheme.primaryColor,
                          ),
                        ),
                      ],
                    ),
                  ),
                  OutlinedButton.icon(
                    onPressed: () {
                      final message = Uri.encodeComponent(
                        'Namaste APS Dream Home! I am interested in Plot #${plot.plotNumber} in ${plot.colonyName} (${plot.formattedPrice}, ${plot.areaSqft.toStringAsFixed(0)} sqft). Please share deal terms & site visit.',
                      );
                      launchUrl(
                        Uri.parse('https://wa.me/917007444842?text=$message'),
                        mode: LaunchMode.externalApplication,
                      );
                    },
                    icon: const Icon(
                      Icons.chat,
                      color: Color(0xFF25D366),
                      size: 18,
                    ),
                    label: const Text(
                      'WhatsApp',
                      style: TextStyle(
                        color: Color(0xFF0B194C),
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    style: OutlinedButton.styleFrom(
                      side: const BorderSide(
                        color: Color(0xFF25D366),
                        width: 1.5,
                      ),
                      padding: const EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 12,
                      ),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  IconButton(
                    onPressed: () {
                      final pdfUrl = '${AppConstants.baseUrl}/plots/${plot.id}/quotation-pdf';
                      launchUrl(Uri.parse(pdfUrl), mode: LaunchMode.externalApplication);
                    },
                    icon: const Icon(Icons.picture_as_pdf, color: Color(0xFF0284C7)),
                    tooltip: 'Cost Sheet (PDF)',
                    style: IconButton.styleFrom(
                      side: const BorderSide(color: Color(0xFF0284C7), width: 1.2),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      padding: const EdgeInsets.all(12),
                    ),
                  ),
                  const SizedBox(width: 8),
                  ElevatedButton(
                    onPressed: () {
                      if (plot.isAvailable) {
                        context.push(
                          '/booking',
                          extra: {
                            'plotId': plot.id,
                            'colonyId': plot.colonyId,
                            'plotNumber': plot.plotNumber,
                            'amount': plot.finalPrice ?? plot.totalPrice,
                          },
                        );
                      } else {
                        launchUrl(Uri.parse('tel:+917007444842'));
                      }
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppTheme.primaryColor,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(
                        horizontal: 16,
                        vertical: 12,
                      ),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                    child: Text(
                      plot.isAvailable ? 'Book Plot' : 'Enquire',
                      style: const TextStyle(fontWeight: FontWeight.bold),
                    ),
                  ),
                ],
              ),
            ),
          );
        },
        loading: () => const SizedBox.shrink(),
        error: (_, _) => const SizedBox.shrink(),
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
          // Luxury Plot Hero Image + Colony & Status Badges
          SizedBox(
            width: double.infinity,
            height: 240,
            child: Stack(
              fit: StackFit.expand,
              children: [
                Image.network(
                  plot.plotHeroImage,
                  fit: BoxFit.cover,
                  errorBuilder: (_, _, _) => Container(
                    color: AppTheme.primaryColor,
                    child: const Center(
                      child: Icon(Icons.apartment, size: 60, color: Colors.white54),
                    ),
                  ),
                ),
                Container(
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      begin: Alignment.topCenter,
                      end: Alignment.bottomCenter,
                      colors: [
                        Colors.black.withValues(alpha: 0.3),
                        Colors.black.withValues(alpha: 0.8),
                      ],
                    ),
                  ),
                ),
                Positioned(
                  bottom: 16,
                  left: 16,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF58220),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          plot.colonyName.toUpperCase(),
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            letterSpacing: 0.8,
                          ),
                        ),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        'Plot ${plot.plotNumber}',
                        style: const TextStyle(
                          fontSize: 26,
                          fontWeight: FontWeight.bold,
                          color: Colors.white,
                        ),
                      ),
                    ],
                  ),
                ),
                Positioned(
                  top: 14,
                  right: 14,
                  child: _buildStatusBadge(plot.status),
                ),
                if (plot.hasPremiumLocation)
                  Positioned(
                    top: 14,
                    left: 14,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                      decoration: BoxDecoration(
                        color: const Color(0xFFE5A93C),
                        borderRadius: BorderRadius.circular(8),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.2),
                            blurRadius: 6,
                          ),
                        ],
                      ),
                      child: const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.star, color: Colors.white, size: 13),
                          SizedBox(width: 4),
                          Text('Premium Location', style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold)),
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
                          AppWidgets.formatCurrency(displayPrice),
                          style: const TextStyle(fontSize: 28, fontWeight: FontWeight.bold, color: AppTheme.primaryColor),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          '${AppWidgets.formatCurrency(plot.pricePerSqft)}/sqft',
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

          // Smart EMI Calculator Card
          Container(
            width: double.infinity,
            margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: const Color(0xFFE8DFD0), width: 1.2),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF0B194C).withValues(alpha: 0.04),
                  blurRadius: 8,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Row(
                  children: [
                    Icon(Icons.calculate, color: Color(0xFFF58220), size: 20),
                    SizedBox(width: 8),
                    Text(
                      'Smart EMI & Payment Plan',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF0B194C),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                Text(
                  'Flexible 12 to 60 month in-house installment plans available with 0% interest on initial booking.',
                  style: TextStyle(fontSize: 13, color: Colors.grey.shade700, height: 1.4),
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(
                      child: Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: const Color(0xFFFAF7F0),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('Est. Monthly EMI', style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
                            const SizedBox(height: 2),
                            Text(
                              '₹${((displayPrice * 0.7) / 36).toStringAsFixed(0)} / mo',
                              style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFF0B194C)),
                            ),
                            Text('(for 36 months)', style: TextStyle(fontSize: 10, color: Colors.grey.shade500)),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: const Color(0xFFFAF7F0),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('Min. Down Payment', style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
                            const SizedBox(height: 2),
                            Text(
                              '₹${(displayPrice * 0.3).toStringAsFixed(0)}',
                              style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFF10B981)),
                            ),
                            Text('(30% booking token)', style: TextStyle(fontSize: 10, color: Colors.grey.shade500)),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          // APS AI Assistant Real Estate Consultation Card
          Container(
            width: double.infinity,
            margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF0B194C), Color(0xFF1E3A8A)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(14),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF0B194C).withValues(alpha: 0.15),
                  blurRadius: 10,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Row(
                  children: [
                    Icon(Icons.smart_toy, color: Color(0xFFE5A93C), size: 20),
                    SizedBox(width: 8),
                    Text(
                      'APS AI Property Advisor',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Text(
                  'Have questions about Plot #${plot.plotNumber} legal 143 mutation status, road connectivity, or price negotiation?',
                  style: const TextStyle(color: Colors.white70, fontSize: 13, height: 1.4),
                ),
                const SizedBox(height: 12),
                ElevatedButton.icon(
                  onPressed: () => context.push('/ai-chat'),
                  icon: const Icon(Icons.forum, size: 16),
                  label: const Text('Consult AI Assistant Now'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFE5A93C),
                    foregroundColor: const Color(0xFF0B194C),
                    textStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  ),
                ),
              ],
            ),
          ),

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

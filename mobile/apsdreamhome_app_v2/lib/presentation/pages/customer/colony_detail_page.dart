import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:share_plus/share_plus.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/theme/app_theme.dart';
import '../../../data/services/colony_service.dart';
import '../../../data/models/colony_model.dart';
import '../../widgets/app_widgets.dart';

class ColonyDetailPage extends ConsumerWidget {
  final String colonyId;

  const ColonyDetailPage({super.key, required this.colonyId});

  void _shareColony(ColonyModel colony) {
    final locationParts = [
      if (colony.district.isNotEmpty) colony.district,
      if (colony.state.isNotEmpty) colony.state,
    ];
    final locationText = locationParts.isNotEmpty
        ? locationParts.join(', ')
        : 'APS Dream Home';
    final message =
        'Check out ${colony.name} by APS Dream Home!\n'
        '$locationText\n'
        '${colony.pricePerSqft > 0 ? "Price from ₹${colony.pricePerSqft.toStringAsFixed(0)}/sqft" : ""}\n'
        'Plots: ${colony.availablePlots} available out of ${colony.totalPlots}\n'
        'View details: https://apsdreamhome.com/colonies/$colonyId';
    Share.share(message, subject: colony.name);
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final colonyAsync = ref.watch(colonyProvider(colonyId));

    return Scaffold(
      body: colonyAsync.when(
        data: (colony) {
          if (colony == null) {
            return AppWidgets.emptyState(
              title: 'Colony Not Found',
              subtitle: 'The requested colony does not exist',
            );
          }

          return CustomScrollView(
            slivers: [
              // App Bar with Image
              SliverAppBar(
                expandedHeight: 300,
                floating: false,
                pinned: true,
                actions: [
                  IconButton(
                    onPressed: () => _shareColony(colony),
                    icon: const Icon(Icons.share, color: Colors.white),
                    tooltip: 'Share Colony',
                  ),
                ],
                flexibleSpace: FlexibleSpaceBar(
                  background: Stack(
                    fit: StackFit.expand,
                    children: [
                      // Background Image
                      colony.displayImages.isNotEmpty
                          ? Image.network(
                              colony.displayImages.first,
                              fit: BoxFit.cover,
                              errorBuilder: (context, error, stackTrace) {
                                return Container(
                                  color: Colors.grey.shade300,
                                  child: const Icon(
                                    Icons.image_not_supported_outlined,
                                    size: 80,
                                    color: Colors.grey,
                                  ),
                                );
                              },
                            )
                          : Container(
                              color: Colors.grey.shade300,
                              child: const Icon(
                                Icons.home_work_outlined,
                                size: 80,
                                color: Colors.grey,
                              ),
                            ),

                      // Gradient Overlay
                      Container(
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            begin: Alignment.topCenter,
                            end: Alignment.bottomCenter,
                            colors: [
                              Colors.transparent,
                              Colors.black.withValues(alpha: 0.7),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                  title: Text(colony.name),
                ),
              ),

              // Content
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Header
                      Row(
                        children: [
                          AppWidgets.statusBadge(status: colony.status),
                          const SizedBox(width: 8),
                          if (colony.reraNumber != null)
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 8,
                                vertical: 4,
                              ),
                              decoration: BoxDecoration(
                                color: AppTheme.successColor.withValues(
                                  alpha: 0.1,
                                ),
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: const Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(
                                    Icons.verified,
                                    size: 12,
                                    color: AppTheme.successColor,
                                  ),
                                  SizedBox(width: 4),
                                  Text(
                                    'RERA',
                                    style: TextStyle(
                                      fontSize: 10,
                                      color: AppTheme.successColor,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                        ],
                      ),

                      const SizedBox(height: 16),

                      // Location
                      AppWidgets.infoRow(
                        icon: Icons.location_on_outlined,
                        label: 'Location',
                        value: [
                          if (colony.district.isNotEmpty) colony.district,
                          if (colony.state.isNotEmpty) colony.state,
                        ].join(', '),
                      ),

                      const SizedBox(height: 12),

                      // Price
                      AppWidgets.infoRow(
                        icon: Icons.currency_rupee,
                        label: 'Price',
                        value:
                            '₹${colony.pricePerSqft.toStringAsFixed(0)} per sqft',
                      ),

                      const SizedBox(height: 24),

                      // Description
                      Text(
                        'About This Colony',
                        style: Theme.of(context).textTheme.titleLarge?.copyWith(
                          fontWeight: FontWeight.bold,
                        ),
                      ),

                      const SizedBox(height: 8),

                      Text(
                        colony.description ?? 'No description available',
                        style: TextStyle(
                          fontSize: 14,
                          color: Colors.grey.shade700,
                          height: 1.6,
                        ),
                      ),

                      const SizedBox(height: 24),

                      // Plot Statistics
                      Text(
                        'Plot Availability',
                        style: Theme.of(context).textTheme.titleLarge?.copyWith(
                          fontWeight: FontWeight.bold,
                        ),
                      ),

                      const SizedBox(height: 16),

                      _buildPlotStats(context, colony),

                      const SizedBox(height: 24),

                      // Amenities
                      if (colony.amenities != null &&
                          colony.amenities!.isNotEmpty) ...[
                        Text(
                          'Amenities',
                          style: Theme.of(context).textTheme.titleLarge
                              ?.copyWith(fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 16),
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          children: colony.amenities!.map((amenity) {
                            return Chip(
                              avatar: const Icon(
                                Icons.check_circle,
                                color: AppTheme.successColor,
                                size: 18,
                              ),
                              label: Text(amenity),
                              backgroundColor: AppTheme.successColor.withValues(
                                alpha: 0.1,
                              ),
                            );
                          }).toList(),
                        ),
                        const SizedBox(height: 24),
                      ],

                      // Gallery Images
                      if (colony.galleryImagesData != null &&
                          colony.galleryImagesData!.isNotEmpty) ...[
                        Text(
                          'Gallery',
                          style: Theme.of(context).textTheme.titleLarge
                              ?.copyWith(fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 12),
                        SizedBox(
                          height: 150,
                          child: ListView.separated(
                            scrollDirection: Axis.horizontal,
                            itemCount: colony.galleryImagesData!.length,
                            separatorBuilder: (_, __) =>
                                const SizedBox(width: 12),
                            itemBuilder: (context, index) {
                              final imgUrl = colony.galleryImagesData![index];
                              return GestureDetector(
                                onTap: () => _showFullScreenImage(
                                    context, colony.galleryImagesData!, index),
                                child: ClipRRect(
                                  borderRadius: BorderRadius.circular(12),
                                  child: Image.network(
                                    imgUrl,
                                    width: 200,
                                    height: 150,
                                    fit: BoxFit.cover,
                                    errorBuilder: (_, __, ___) => Container(
                                      width: 200,
                                      height: 150,
                                      color: Colors.grey.shade200,
                                      child: const Icon(Icons.image,
                                          color: Colors.grey),
                                    ),
                                  ),
                                ),
                              );
                            },
                          ),
                        ),
                        const SizedBox(height: 24),
                      ],

                      // Layout / Master Plan
                      if (colony.displayLayoutImage != null) ...[
                        Text(
                          'Master Plan / Layout',
                          style: Theme.of(context).textTheme.titleLarge
                              ?.copyWith(fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 12),
                        GestureDetector(
                          onTap: () => _showFullScreenImage(
                              context, [colony.displayLayoutImage!], 0),
                          child: ClipRRect(
                            borderRadius: BorderRadius.circular(12),
                            child: Image.network(
                              colony.displayLayoutImage!,
                              height: 250,
                              width: double.infinity,
                              fit: BoxFit.cover,
                              errorBuilder: (context, error, stackTrace) {
                                return Container(
                                  height: 250,
                                  color: Colors.grey.shade200,
                                  child: const Center(
                                    child: Icon(Icons.map_outlined,
                                        size: 60, color: Colors.grey),
                                  ),
                                );
                              },
                            ),
                          ),
                        ),
                        const SizedBox(height: 24),
                      ],

                      // Master Plan (fallback from old field)
                      if (colony.masterPlanImage != null &&
                          colony.displayLayoutImage == null) ...[
                        Text(
                          'Master Plan',
                          style: Theme.of(context).textTheme.titleLarge
                              ?.copyWith(fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 16),
                        GestureDetector(
                          onTap: () {
                            // Show full screen master plan
                          },
                          child: ClipRRect(
                            borderRadius: BorderRadius.circular(12),
                            child: Image.network(
                              colony.masterPlanImage!,
                              height: 200,
                              width: double.infinity,
                              fit: BoxFit.cover,
                              errorBuilder: (context, error, stackTrace) {
                                return Container(
                                  height: 200,
                                  color: Colors.grey.shade200,
                                  child: const Center(
                                    child: Icon(
                                      Icons.map_outlined,
                                      size: 60,
                                      color: Colors.grey,
                                    ),
                                  ),
                                );
                              },
                            ),
                          ),
                        ),
                        const SizedBox(height: 8),
                        Center(
                          child: TextButton.icon(
                            onPressed: () =>
                                context.push('/plots?colonyId=$colonyId'),
                            icon: const Icon(Icons.grid_view),
                            label: const Text('View Plot Grid'),
                          ),
                        ),
                        const SizedBox(height: 24),
                      ],

                      // Video Tour
                      if (colony.youtubeVideoUrl != null &&
                          colony.youtubeVideoUrl!.isNotEmpty) ...[
                        Text(
                          'Video Tour',
                          style: Theme.of(context).textTheme.titleLarge
                              ?.copyWith(fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 12),
                        GestureDetector(
                          onTap: () => launchUrl(
                            Uri.parse(colony.youtubeVideoUrl!),
                            mode: LaunchMode.externalApplication,
                          ),
                          child: Container(
                            height: 200,
                            decoration: BoxDecoration(
                              color: Colors.black87,
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: const Center(
                              child: Column(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(Icons.play_circle_fill,
                                      size: 64, color: Colors.white),
                                  SizedBox(height: 8),
                                  Text('Watch Video Tour',
                                      style: TextStyle(
                                          color: Colors.white, fontSize: 16)),
                                ],
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(height: 24),
                      ],

                      // Virtual Tour
                      if (colony.virtualTourUrl != null &&
                          colony.virtualTourUrl!.isNotEmpty) ...[
                        SizedBox(
                          width: double.infinity,
                          child: OutlinedButton.icon(
                            onPressed: () => launchUrl(
                              Uri.parse(colony.virtualTourUrl!),
                              mode: LaunchMode.externalApplication,
                            ),
                            icon: const Icon(Icons.view_in_ar),
                            label: const Text('Explore Virtual Tour'),
                            style: OutlinedButton.styleFrom(
                              padding: const EdgeInsets.all(16),
                            ),
                          ),
                        ),
                        const SizedBox(height: 24),
                      ],

                      // Documents
                      if (colony.brochurePath != null &&
                          colony.brochurePath!.isNotEmpty) ...[
                        Text(
                          'Documents',
                          style: Theme.of(context).textTheme.titleLarge
                              ?.copyWith(fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 12),
                        _buildDocumentTile(
                          context,
                          icon: Icons.picture_as_pdf,
                          title: 'Download Brochure',
                          onTap: () => launchUrl(
                            Uri.parse(colony.brochurePath!),
                            mode: LaunchMode.externalApplication,
                          ),
                        ),
                        const SizedBox(height: 24),
                      ],

                      // Location on Map
                      if (colony.directionsUrl != null) ...[
                        Text(
                          'Location',
                          style: Theme.of(context).textTheme.titleLarge
                              ?.copyWith(fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 12),
                        SizedBox(
                          width: double.infinity,
                          child: OutlinedButton.icon(
                            onPressed: () => launchUrl(
                              Uri.parse(colony.directionsUrl!),
                              mode: LaunchMode.externalApplication,
                            ),
                            icon: const Icon(Icons.directions),
                            label: const Text('Get Directions on Google Maps'),
                            style: OutlinedButton.styleFrom(
                              padding: const EdgeInsets.all(16),
                            ),
                          ),
                        ),
                        const SizedBox(height: 24),
                      ],

                      // Nearby Places
                      if (colony.nearbyPlacesList.isNotEmpty) ...[
                        Text(
                          'Nearby Places',
                          style: Theme.of(context).textTheme.titleLarge
                              ?.copyWith(fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 12),
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          children: colony.nearbyPlacesList.map((place) {
                            return Chip(
                              avatar: const Icon(
                                Icons.place_outlined,
                                color: AppTheme.primaryColor,
                                size: 18,
                              ),
                              label: Text(place),
                              backgroundColor: AppTheme.primaryColor.withValues(
                                alpha: 0.1,
                              ),
                            );
                          }).toList(),
                        ),
                        const SizedBox(height: 24),
                      ],

                      // Contact
                      Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: AppTheme.primaryColor.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                const Icon(
                                  Icons.support_agent,
                                  color: AppTheme.primaryColor,
                                ),
                                const SizedBox(width: 8),
                                Text(
                                  'Need Help?',
                                  style: Theme.of(context).textTheme.titleMedium
                                      ?.copyWith(
                                        fontWeight: FontWeight.bold,
                                        color: AppTheme.primaryColor,
                                      ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 8),
                            Text(
                              'Our team is available 24/7 to help you with plot selection and booking.',
                              style: TextStyle(color: Colors.grey.shade700),
                            ),
                            const SizedBox(height: 12),
                            Row(
                              children: [
                                Expanded(
                                  child: OutlinedButton.icon(
                                    onPressed: () => launchUrl(
                                      Uri.parse('tel:+917007444842'),
                                    ),
                                    icon: const Icon(Icons.phone_outlined),
                                    label: const Text('Call'),
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: ElevatedButton.icon(
                                    onPressed: () => launchUrl(
                                      Uri.parse(
                                        'https://wa.me/917007444842?text=Hi, I\'m interested in your colonies',
                                      ),
                                      mode: LaunchMode.externalApplication,
                                    ),
                                    icon: const Icon(Icons.chat_outlined),
                                    label: const Text('WhatsApp'),
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
              ),

              // Bottom Padding
              const SliverToBoxAdapter(child: SizedBox(height: 100)),
            ],
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, stack) => AppWidgets.errorWidget(
          message: error.toString(),
          onRetry: () => ref.refresh(colonyProvider(colonyId)),
        ),
      ),

      // Bottom Action Bar
      bottomNavigationBar: colonyAsync.when(
        data: (colony) {
          if (colony == null) return const SizedBox.shrink();

          return Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.1),
                  blurRadius: 10,
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
                          'Starting from',
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey.shade600,
                          ),
                        ),
                        AppWidgets.priceTag(
                          amount: colony.pricePerSqft,
                          prefix: '₹',
                          style: const TextStyle(
                            fontSize: 20,
                            fontWeight: FontWeight.bold,
                            color: AppTheme.primaryColor,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 16),
                  SizedBox(
                    height: 50,
                    child: ElevatedButton.icon(
                      onPressed: colony.availablePlots > 0
                          ? () => context.push('/plots?colonyId=$colonyId')
                          : null,
                      icon: const Icon(Icons.grid_view),
                      label: Text(
                        colony.availablePlots > 0
                            ? 'View ${colony.availablePlots} Plots'
                            : 'Sold Out',
                      ),
                    ),
                  ),
                ],
              ),
            ),
          );
        },
        loading: () => const SizedBox.shrink(),
        error: (error, stack) => const SizedBox.shrink(),
      ),
    );
  }

  void _showFullScreenImage(
      BuildContext context, List<String> images, int initialIndex) {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => _FullScreenImageViewer(
          images: images,
          initialIndex: initialIndex,
        ),
      ),
    );
  }

  Widget _buildDocumentTile(
    BuildContext context, {
    required IconData icon,
    required String title,
    required VoidCallback onTap,
  }) {
    return ListTile(
      leading: Icon(icon, color: AppTheme.primaryColor),
      title: Text(title),
      trailing: const Icon(Icons.download_outlined),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(8),
        side: BorderSide(color: Colors.grey.shade300),
      ),
      onTap: onTap,
    );
  }

  Widget _buildPlotStats(BuildContext context, ColonyModel colony) {
    final stats = [
      {
        'label': 'Available',
        'count': colony.availablePlots,
        'color': AppTheme.plotAvailable,
      },
      {'label': 'Hold', 'count': colony.holdPlots, 'color': AppTheme.plotHold},
      {
        'label': 'Booked',
        'count': colony.bookedPlots,
        'color': AppTheme.plotBooked,
      },
      {'label': 'Sold', 'count': colony.soldPlots, 'color': AppTheme.plotSold},
    ];

    return Row(
      children: stats.map((stat) {
        return Expanded(
          child: Container(
            margin: const EdgeInsets.only(right: 8),
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: (stat['color'] as Color).withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(
                color: (stat['color'] as Color).withValues(alpha: 0.3),
              ),
            ),
            child: Column(
              children: [
                Text(
                  stat['count'].toString(),
                  style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: stat['color'] as Color,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  stat['label'] as String,
                  style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                ),
              ],
            ),
          ),
        );
      }).toList(),
    );
  }
}

class _FullScreenImageViewer extends StatefulWidget {
  final List<String> images;
  final int initialIndex;

  const _FullScreenImageViewer({
    required this.images,
    this.initialIndex = 0,
  });

  @override
  State<_FullScreenImageViewer> createState() => _FullScreenImageViewerState();
}

class _FullScreenImageViewerState extends State<_FullScreenImageViewer> {
  late PageController _pageController;
  late int _currentIndex;

  @override
  void initState() {
    super.initState();
    _currentIndex = widget.initialIndex;
    _pageController = PageController(initialPage: widget.initialIndex);
  }

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
        title: Text(
          '${_currentIndex + 1} / ${widget.images.length}',
          style: const TextStyle(fontSize: 16),
        ),
      ),
      body: PageView.builder(
        controller: _pageController,
        itemCount: widget.images.length,
        onPageChanged: (index) {
          setState(() => _currentIndex = index);
        },
        itemBuilder: (context, index) {
          return InteractiveViewer(
            minScale: 0.5,
            maxScale: 4.0,
            child: Center(
              child: Image.network(
                widget.images[index],
                fit: BoxFit.contain,
                errorBuilder: (_, __, ___) => const Icon(
                  Icons.broken_image,
                  color: Colors.white54,
                  size: 80,
                ),
              ),
            ),
          );
        },
      ),
    );
  }
}

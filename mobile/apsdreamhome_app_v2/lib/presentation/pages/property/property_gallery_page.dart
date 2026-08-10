import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:share_plus/share_plus.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../data/services/property_listing_service.dart';
import '../../../core/theme/app_theme.dart';

class PropertyGalleryPage extends ConsumerStatefulWidget {
  const PropertyGalleryPage({
    super.key,
    required this.propertyId,
    this.title = 'Gallery',
  });

  final String propertyId;
  final String title;

  @override
  ConsumerState<PropertyGalleryPage> createState() => _PropertyGalleryPageState();
}

class _PropertyGalleryPageState extends ConsumerState<PropertyGalleryPage>
    with TickerProviderStateMixin {
  late AnimationController _fadeController;
  late Animation<double> _fadeAnimation;
  List<String> _allImages = [];
  bool _isLoading = true;
  String _errorMessage = '';

  @override
  void initState() {
    super.initState();
    _fadeController = AnimationController(
      duration: const Duration(milliseconds: 300),
      vsync: this,
    );
    _fadeAnimation = CurvedAnimation(
      parent: _fadeController,
      curve: Curves.easeOut,
    );
    _loadPropertyImages();
  }

  @override
  void dispose() {
    _fadeController.dispose();
    super.dispose();
  }

  Future<void> _loadPropertyImages() async {
    final service = PropertyListingService();
    try {
      final property = await service.getPropertyById(widget.propertyId);
      if (property != null) {
        final images = property.images.where((img) => img.isNotEmpty).toList();
        if (!mounted) return;
        setState(() {
          _allImages = images;
          _isLoading = false;
          _errorMessage = '';
        });
        _fadeController.forward();
      } else if (!mounted) return;
      {
        setState(() {
          _allImages = [];
          _isLoading = false;
          _errorMessage = 'Property not found.';
        });
      }
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _allImages = [];
        _isLoading = false;
        _errorMessage = 'Failed to load gallery.';
      });
    }
  }

  void _openFullScreen(int index) {
    if (_allImages.isEmpty) return;
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => _FullScreenGallery(
          images: _allImages,
          initialIndex: index,
          title: widget.title,
        ),
      ),
    );
  }

  Widget _buildImageGrid() {
    if (_allImages.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(
              Icons.photo_library_outlined,
              size: 64,
              color: Colors.grey,
            ),
            const SizedBox(height: 16),
            Text(
              _errorMessage.isEmpty
                  ? 'No photos available for this property'
                  : _errorMessage,
              style: const TextStyle(
                color: Colors.grey,
                fontSize: 16,
              ),
            ),
          ],
        ),
      );
    }

    return GridView.builder(
      padding: const EdgeInsets.all(12),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        crossAxisSpacing: 8,
        mainAxisSpacing: 8,
        childAspectRatio: 1.0,
      ),
      itemCount: _allImages.length,
      itemBuilder: (context, index) {
        return FadeTransition(
          opacity: _fadeAnimation,
          child: GestureDetector(
            onTap: () => _openFullScreen(index),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(12),
              child: Stack(
                children: [
                  Hero(
                    tag: 'property-image-gallery-$index-${widget.propertyId}',
                    child: CachedNetworkImage(
                      imageUrl: _allImages[index],
                      fit: BoxFit.cover,
                      width: double.infinity,
                      height: double.infinity,
                      placeholder: (context, url) => Container(
                        color: Colors.grey[300],
                        child: const Center(
                          child: SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(strokeWidth: 2),
                          ),
                        ),
                      ),
                      errorWidget: (context, url, error) => Container(
                        color: Colors.grey[300],
                        child: const Center(
                          child: Icon(
                            Icons.broken_image_rounded,
                            size: 32,
                            color: Colors.grey,
                          ),
                        ),
                      ),
                    ),
                  ),
                  Positioned(
                    top: 8,
                    right: 8,
                    child: Container(
                      width: 24,
                      height: 24,
                      decoration: BoxDecoration(
                        color: Colors.black45,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(
                        Icons.fullscreen_rounded,
                        size: 14,
                        color: Colors.white,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        title: Text(widget.title),
        backgroundColor: Colors.white,
        foregroundColor: AppTheme.textPrimaryLight,
        elevation: 0,
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(1),
          child: Container(
            height: 1,
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [
                  Colors.transparent,
                  AppTheme.primaryColor.withOpacity(0.3),
                  Colors.transparent,
                ],
              ),
            ),
          ),
        ),
        actions: [
          if (_allImages.isNotEmpty)
            Padding(
              padding: const EdgeInsets.only(right: 16),
              child: Center(
                child: Text(
                  '${_allImages.length} photo${_allImages.length != 1 ? 's' : ''}',
                  style: TextStyle(
                    fontSize: 14,
                    color: AppTheme.textSecondaryLight,
                  ),
                ),
              ),
            ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _buildImageGrid(),
    );
  }
}

class _FullScreenGallery extends StatefulWidget {
  final List<String> images;
  final int initialIndex;
  final String title;

  const _FullScreenGallery({
    required this.images,
    required this.initialIndex,
    required this.title,
  });

  @override
  State<_FullScreenGallery> createState() => _FullScreenGalleryState();
}

class _FullScreenGalleryState extends State<_FullScreenGallery> {
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

  void _shareCurrent() {
    Share.share(widget.images[_currentIndex]);
  }

  void _downloadCurrent() async {
    final url = widget.images[_currentIndex];
    final uri = Uri.parse(url);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
        title: Text(widget.title),
        actions: [
          Center(
            child: Padding(
              padding: const EdgeInsets.only(right: 16),
              child: Text(
                '${_currentIndex + 1}/${widget.images.length}',
                style: const TextStyle(
                  fontSize: 14,
                  color: Colors.white70,
                ),
              ),
            ),
          ),
          IconButton(
            icon: const Icon(Icons.share_rounded),
            onPressed: _shareCurrent,
          ),
          IconButton(
            icon: const Icon(Icons.download_rounded),
            onPressed: _downloadCurrent,
          ),
        ],
      ),
      body: PageView.builder(
        controller: _pageController,
        itemCount: widget.images.length,
        onPageChanged: (i) => setState(() => _currentIndex = i),
        itemBuilder: (context, index) {
          return InteractiveViewer(
            minScale: 0.8,
            maxScale: 5,
            child: Center(
              child: CachedNetworkImage(
                imageUrl: widget.images[index],
                fit: BoxFit.contain,
                placeholder: (context, url) => const Center(
                  child: SizedBox(
                    width: 24,
                    height: 24,
                    child: CircularProgressIndicator(
                      color: Colors.white70,
                      strokeWidth: 2,
                    ),
                  ),
                ),
                errorWidget: (context, url, error) => const Center(
                  child: Icon(
                    Icons.broken_image_rounded,
                    size: 64,
                    color: Colors.white38,
                  ),
                ),
              ),
            ),
          );
        },
      ),
      bottomNavigationBar: Container(
        color: Colors.black,
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
          children: [
            TextButton.icon(
              onPressed: _currentIndex > 0
                  ? () => _pageController.previousPage(
                        duration: const Duration(milliseconds: 300),
                        curve: Curves.easeOut,
                      )
                  : null,
              icon: const Icon(Icons.chevron_left_rounded,
                  color: Colors.white70),
              label: const Text('Previous',
                  style: TextStyle(color: Colors.white70)),
            ),
            TextButton.icon(
              onPressed: _currentIndex < widget.images.length - 1
                  ? () => _pageController.nextPage(
                        duration: const Duration(milliseconds: 300),
                        curve: Curves.easeOut,
                      )
                  : null,
              icon: const Text('Next',
                  style: TextStyle(color: Colors.white70)),
              label: const Icon(Icons.chevron_right_rounded,
                  color: Colors.white70),
            ),
          ],
        ),
      ),
    );
  }
}

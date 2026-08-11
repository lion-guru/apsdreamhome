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
  List<PropertyImageInfo> _allImages = [];
  bool _isLoading = true;
  String _errorMessage = '';
  String _selectedFilter = 'all';

  static const Map<String, String> _filterLabels = {
    'all': 'All',
    'gallery': 'Gallery',
    'interior': 'Interior',
    'exterior': 'Exterior',
    'aerial': 'Aerial',
    'floor_plan': 'Floor Plan',
    'living_room': 'Living Room',
    'kitchen': 'Kitchen',
    'bathroom': 'Bathroom',
    'master_bedroom': 'Master Bedroom',
  };

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
        final images = property.imageDetails.where((img) => img.url.isNotEmpty).toList();
        if (!mounted) return;
        setState(() {
          _allImages = images;
          _isLoading = false;
          _errorMessage = '';
        });
        _fadeController.forward();
      } else {
        if (!mounted) return;
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

  List<PropertyImageInfo> get _filteredImages {
    if (_selectedFilter == 'all') return _allImages;
    return _allImages.where((img) => img.type == _selectedFilter).toList();
  }

  List<String> _getFilterableTypes() {
    final types = _allImages.map((e) => e.type).toSet().toList()..sort();
    return types;
  }

  void _openFullScreen(int index) {
    if (_filteredImages.isEmpty) return;
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => _FullScreenGallery(
          images: _filteredImages,
          initialIndex: index,
          title: widget.title,
        ),
      ),
    );
  }

  Widget _buildFilterBar() {
    final types = _getFilterableTypes();
    if (types.isEmpty) return const SizedBox.shrink();

    final allFilters = ['all', ...types];
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      child: Row(
        children: allFilters.map((type) {
          final label = _filterLabels[type] ?? type;
          final isSelected = _selectedFilter == type;
          final imageCount = type == 'all'
              ? _allImages.length
              : _allImages.where((img) => img.type == type).length;
          return Container(
            margin: const EdgeInsets.only(right: 8),
            child: FilterChip(
              label: Row(
                children: [
                  Text(label),
                  const SizedBox(width: 4),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                    decoration: BoxDecoration(
                      color: isSelected
                          ? Colors.white.withValues(alpha: 0.2)
                          : Colors.grey.withValues(alpha: 0.2),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Text(
                      imageCount.toString(),
                      style: TextStyle(
                        fontSize: 11,
                        color: isSelected ? Colors.white : Colors.grey[700],
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ],
              ),
              selected: isSelected,
              onSelected: (_) {
                setState(() => _selectedFilter = type);
              },
        backgroundColor: Colors.grey[100],
      selectedColor: AppTheme.primaryColor,
      labelStyle: TextStyle(
        color: isSelected ? Colors.white : AppTheme.textPrimaryLight,
        fontSize: 13,
        fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
      ),
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              visualDensity: VisualDensity.compact,
            ),
          );
        }).toList(),
      ),
    );
  }

  Widget _buildImageGrid() {
    final images = _filteredImages;

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

    if (images.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(
              Icons.filter_alt_outlined,
              size: 64,
              color: Colors.grey,
            ),
            const SizedBox(height: 16),
            Text(
              'No "${_filterLabels[_selectedFilter] ?? _selectedFilter}" photos available',
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
      itemCount: images.length,
      itemBuilder: (context, index) {
        final img = images[index];
        final globalIndex = _allImages.indexOf(img);
        return FadeTransition(
          opacity: _fadeAnimation,
          child: GestureDetector(
            onTap: () => _openFullScreen(index),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(12),
              child: Stack(
                children: [
                  Hero(
                    tag: 'property-image-gallery-$globalIndex-${widget.propertyId}',
                    child: CachedNetworkImage(
                      imageUrl: img.url,
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
                  if (img.type != 'gallery' && img.type != 'all')
                    Positioned(
                      top: 8,
                      left: 8,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: _imageTypeColor(img.type),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          _filterLabels[img.type] ?? img.type,
                          style: const TextStyle(
                            fontSize: 10,
                            color: Colors.white,
                            fontWeight: FontWeight.w600,
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

  Color _imageTypeColor(String type) {
    switch (type) {
      case 'interior':
        return Colors.blue;
      case 'exterior':
        return Colors.green;
      case 'aerial':
        return Colors.orange;
      case 'floor_plan':
        return Colors.purple;
      case 'living_room':
        return Colors.red;
      case 'kitchen':
        return Colors.amber;
      case 'bathroom':
        return Colors.teal;
      case 'master_bedroom':
        return Colors.indigo;
      default:
        return Colors.black45;
    }
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
          preferredSize: Size.fromHeight(_allImages.isNotEmpty ? 58 : 2),
          child: Column(
            children: [
              Container(
                height: 1,
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [
                      Colors.transparent,
                      AppTheme.primaryColor.withValues(alpha: 0.3),
                      Colors.transparent,
                    ],
                  ),
                ),
              ),
              if (_allImages.isNotEmpty) _buildFilterBar(),
            ],
          ),
        ),
        actions: [
          if (_filteredImages.isNotEmpty)
            Padding(
              padding: const EdgeInsets.only(right: 16),
              child: Center(
                child: Text(
                  '${_filteredImages.length} photo${_filteredImages.length != 1 ? 's' : ''}',
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
  final List<PropertyImageInfo> images;
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
    Share.share(widget.images[_currentIndex].url);
  }

  void _downloadCurrent() async {
    final url = widget.images[_currentIndex].url;
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
          final img = widget.images[index];
          return InteractiveViewer(
            minScale: 0.8,
            maxScale: 5,
            child: Center(
              child: CachedNetworkImage(
                imageUrl: img.url,
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

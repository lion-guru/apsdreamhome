import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/services/api_service.dart';

class GalleryPage extends ConsumerStatefulWidget {
  const GalleryPage({super.key});

  @override
  ConsumerState<GalleryPage> createState() => _GalleryPageState();
}

class _GalleryPageState extends ConsumerState<GalleryPage> {
  List<Map<String, dynamic>> _images = [];
  bool _isLoading = true;
  String? _error;
  int _currentPage = 1;
  bool _hasMore = true;

  @override
  void initState() {
    super.initState();
    _loadImages();
  }

  Future<void> _loadImages({bool loadMore = false}) async {
    if (loadMore) {
      _currentPage++;
    } else {
      _currentPage = 1;
    }

    try {
      final api = ApiService();
      final response = await api.get('/gallery', queryParameters: {
        'page': _currentPage,
        'limit': 20,
      });

      if (mounted) {
        setState(() {
          if (response['success'] == true && response['data'] is List) {
            final newImages = (response['data'] as List)
                .map((e) => Map<String, dynamic>.from(e as Map<dynamic, dynamic>))
                .toList();
            if (loadMore) {
              _images.addAll(newImages);
            } else {
              _images = newImages;
            }
            _hasMore = newImages.length >= 20;
            _isLoading = false;
          } else {
            _hasMore = false;
            _isLoading = false;
          }
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = e.toString();
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Gallery'),
        backgroundColor: Colors.transparent,
        elevation: 0,
      ),
      body: _isLoading && _images.isEmpty
          ? const Center(child: CircularProgressIndicator())
          : _error != null && _images.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.error_outline, size: 64, color: Colors.red),
                      const SizedBox(height: 16),
                      Text('Failed to load gallery: $_error'),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: _loadImages,
                        child: const Text('Retry'),
                      ),
                    ],
                  ),
                )
              : _images.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.photo_library_outlined, size: 64, color: Colors.grey[300]),
                          const SizedBox(height: 16),
                          Text('No images available', style: TextStyle(color: Colors.grey[600], fontSize: 18)),
                        ],
                      ),
                    )
                  : RefreshIndicator(
                      onRefresh: () => _loadImages(),
                      child: NotificationListener<ScrollNotification>(
                        onNotification: (scroll) {
                          if (scroll.metrics.pixels >= scroll.metrics.maxScrollExtent - 200 &&
                              _hasMore &&
                              !_isLoading) {
                            _loadImages(loadMore: true);
                          }
                          return false;
                        },
                        child: GridView.builder(
                          padding: const EdgeInsets.all(16),
                          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                            crossAxisCount: 2,
                            crossAxisSpacing: 12,
                            mainAxisSpacing: 12,
                            childAspectRatio: 0.75,
                          ),
                          itemCount: _images.length + (_hasMore ? 1 : 0),
                          itemBuilder: (context, index) {
                            if (index >= _images.length) {
                              return const Center(
                                child: Padding(
                                  padding: EdgeInsets.all(16),
                                  child: CircularProgressIndicator(),
                                ),
                              );
                            }
                            final image = _images[index];
                            return _buildImageCard(image);
                          },
                        ),
                      ),
    );
  }

  Widget _buildImageCard(Map<String, dynamic> image) {
    final url = (image['image'] ?? image['url'] ?? image['thumb'])?.toString();
    final title = (image['title'] ?? image['caption'] ?? '').toString();
    final type = (image['type'] ?? image['category'] ?? '').toString();

    return Card(
      clipBehavior: Clip.antiAlias,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      elevation: 2,
      child: InkWell(
        onTap: () => _showFullScreenImage(image),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Expanded(
              child: url != null
                  ? Image.network(
                      url,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => Container(
                        color: AppTheme.primaryColor.withOpacity(0.1),
                        child: const Center(child: Icon(Icons.image, size: 48, color: Colors.grey)),
                      ),
                    )
                  : Container(
                      color: AppTheme.primaryColor.withOpacity(0.1),
                      child: const Center(child: Icon(Icons.image, size: 48, color: Colors.grey)),
                    ),
            ),
            if (title.isNotEmpty || type.isNotEmpty)
              Padding(
                padding: const EdgeInsets.all(8),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    if (title.isNotEmpty)
                      Text(
                        title,
                        style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 12),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    if (type.isNotEmpty)
                      Text(
                        type,
                        style: TextStyle(color: Colors.grey[500], fontSize: 10),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                  ],
                ),
              ),
          ],
        ),
      ),
    );
  }

  void _showFullScreenImage(Map<String, dynamic> image) {
    final url = image['image'] ?? image['url'];
    if (url == null) return;

    showDialog(
      context: context,
      builder: (context) => Dialog(
        backgroundColor: Colors.black,
        insetPadding: EdgeInsets.zero,
        child: Stack(
          children: [
            GestureDetector(
              onTap: () => Navigator.pop(context),
              child: InteractiveViewer(
                panEnabled: true,
                boundaryMargin: EdgeInsets.all(20),
                minScale: 0.5,
                maxScale: 4.0,
                child: Center(
                  child: Image.network(
                    url,
                    fit: BoxFit.contain,
                    errorBuilder: (_, __, ___) => const Icon(Icons.error, size: 64, color: Colors.white),
                  ),
                ),
              ),
            ),
            Positioned(
              top: MediaQuery.of(context).padding.top + 16,
              right: 16,
              child: IconButton(
                onPressed: () => Navigator.pop(context),
                icon: const Icon(Icons.close, color: Colors.white, size: 28),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
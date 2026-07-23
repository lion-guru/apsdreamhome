import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import '../../../core/constants/app_constants.dart';

class GalleryPage extends StatefulWidget {
  const GalleryPage({super.key});

  @override
  State<GalleryPage> createState() => _GalleryPageState();
}

class _GalleryPageState extends State<GalleryPage> {
  List<_AlbumData> _albums = [];
  bool _loading = true;

  // Unsplash photo pools by category
  static const _colonyPhotos = [
    'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=400',
    'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=400',
    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=400',
    'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=400',
    'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=400',
  ];
  static const _interiorPhotos = [
    'https://images.unsplash.com/photo-1600573472591-ee6b68d14c68?w=400',
    'https://images.unsplash.com/photo-1600566752355-35792bedcfea?w=400',
    'https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?w=400',
    'https://images.unsplash.com/photo-1600585153490-76fb20a32601?w=400',
  ];
  static const _landscapePhotos = [
    'https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?w=400',
    'https://images.unsplash.com/photo-1600573472581-1ee6b68d14c68?w=400',
    'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=400',
    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=400',
  ];
  static const _clubPhotos = [
    'https://images.unsplash.com/photo-1600566752355-35792bedcfea?w=400',
    'https://images.unsplash.com/photo-1600573472592-401b489a3cdc?w=400',
    'https://images.unsplash.com/photo-1600585154084-4e5fe7c39198?w=400',
    'https://images.unsplash.com/photo-1600573472550-8090b5e0745e?w=400',
  ];

  @override
  void initState() {
    super.initState();
    _loadAlbums();
  }

  Future<void> _loadAlbums() async {
    try {
      AppConstants.initBaseUrl();
      final url = '${AppConstants.baseUrl}/api/v2/mobile/colonies';
      final resp = await http
          .get(Uri.parse(url))
          .timeout(const Duration(seconds: 10));
      if (resp.statusCode == 200) {
        final data = jsonDecode(resp.body);
        if (data['success'] == true && data['data'] is List) {
          final colonies = data['data'] as List;
          final albums = <_AlbumData>[];

          for (int i = 0; i < colonies.length; i++) {
            final c = colonies[i] as Map<String, dynamic>;
            final name = c['name'] ?? 'Colony ${i + 1}';
            final totalPlots = c['total_plots'] ?? 0;
            // Cycle through photo pools
            final photos = (i % 2 == 0) ? _colonyPhotos : _interiorPhotos;
            albums.add(
              _AlbumData(
                name.toString(),
                '$totalPlots photos',
                photos.first,
                photos,
              ),
            );
          }

          // Add static album categories
          albums.add(
            _AlbumData(
              'Club & Amenities',
              '20 photos',
              _clubPhotos.first,
              _clubPhotos,
            ),
          );
          albums.add(
            _AlbumData(
              'Landscape & Parks',
              '18 photos',
              _landscapePhotos.first,
              _landscapePhotos,
            ),
          );
          albums.add(
            _AlbumData(
              'Interior Designs',
              '15 photos',
              _interiorPhotos.first,
              _interiorPhotos,
            ),
          );

          setState(() {
            _albums = albums;
            _loading = false;
          });
          return;
        }
      }
    } catch (_) {}

    // Fallback to static albums
    setState(() {
      _albums = _mockAlbums;
      _loading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Photo Gallery'),
        centerTitle: true,
        flexibleSpace: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              colors: [Color(0xFF1A237E), Color(0xFF283593)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
          ),
        ),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : GridView.builder(
              padding: const EdgeInsets.all(16),
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 2,
                childAspectRatio: 0.85,
                crossAxisSpacing: 12,
                mainAxisSpacing: 12,
              ),
              itemCount: _albums.length,
              itemBuilder: (context, index) {
                final album = _albums[index];
                final colorTones = [
                  const Color(0xFF1A237E),
                  const Color(0xFF2E7D32),
                  const Color(0xFFE65100),
                  const Color(0xFF6A1B9A),
                  const Color(0xFFC62828),
                  const Color(0xFF00838F),
                ];
                final color = colorTones[index % colorTones.length];

                return GestureDetector(
                  onTap: () => _showAlbum(context, album),
                  child: Card(
                    elevation: 3,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                    clipBehavior: Clip.antiAlias,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Expanded(
                          child: Stack(
                            fit: StackFit.expand,
                            children: [
                              Image.network(
                                album.photos.first,
                                fit: BoxFit.cover,
                                errorBuilder: (_, _, _) => Container(
                                  decoration: BoxDecoration(
                                    gradient: LinearGradient(
                                      colors: [
                                        color.withValues(alpha: 0.3),
                                        color.withValues(alpha: 0.1),
                                      ],
                                    ),
                                  ),
                                  child: Center(
                                    child: Icon(
                                      Icons.photo_library_outlined,
                                      size: 48,
                                      color: color.withValues(alpha: 0.4),
                                    ),
                                  ),
                                ),
                                loadingBuilder: (_, child, progress) {
                                  if (progress == null) return child;
                                  return Container(
                                    decoration: BoxDecoration(
                                      gradient: LinearGradient(
                                        colors: [
                                          color.withValues(alpha: 0.2),
                                          color.withValues(alpha: 0.05),
                                        ],
                                      ),
                                    ),
                                    child: Center(
                                      child: CircularProgressIndicator(
                                        strokeWidth: 2,
                                        value:
                                            progress.expectedTotalBytes != null
                                            ? progress.cumulativeBytesLoaded /
                                                  progress.expectedTotalBytes!
                                            : null,
                                        color: color,
                                      ),
                                    ),
                                  );
                                },
                              ),
                              Positioned(
                                top: 8,
                                right: 8,
                                child: Container(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 8,
                                    vertical: 4,
                                  ),
                                  decoration: BoxDecoration(
                                    color: Colors.black.withValues(alpha: 0.5),
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      const Icon(
                                        Icons.photo,
                                        size: 12,
                                        color: Colors.white,
                                      ),
                                      const SizedBox(width: 3),
                                      Text(
                                        '${album.photos.length}',
                                        style: const TextStyle(
                                          fontSize: 10,
                                          color: Colors.white,
                                          fontWeight: FontWeight.w600,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                        Padding(
                          padding: const EdgeInsets.all(10),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                album.title,
                                style: const TextStyle(
                                  fontWeight: FontWeight.w600,
                                  fontSize: 13,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 2),
                              Row(
                                children: [
                                  Icon(
                                    Icons.photo,
                                    size: 14,
                                    color: Colors.grey[500],
                                  ),
                                  const SizedBox(width: 4),
                                  Text(
                                    album.count,
                                    style: TextStyle(
                                      color: Colors.grey[500],
                                      fontSize: 11,
                                    ),
                                  ),
                                  const Spacer(),
                                  Icon(
                                    Icons.arrow_forward_ios,
                                    size: 12,
                                    color: Colors.grey[400],
                                  ),
                                ],
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
    );
  }

  void _showAlbum(BuildContext context, _AlbumData album) {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => _AlbumViewerPage(album: album)),
    );
  }

  static final _mockAlbums = [
    _AlbumData(
      'Suryoday Colony',
      '12 photos',
      _colonyPhotos.first,
      _colonyPhotos,
    ),
    _AlbumData(
      'Braj Radha Vihar',
      '8 photos',
      _interiorPhotos.first,
      _interiorPhotos,
    ),
    _AlbumData(
      'Raghunath Nagri',
      '15 photos',
      _colonyPhotos.first,
      _colonyPhotos,
    ),
    _AlbumData(
      'Budh Bihar',
      '10 photos',
      _interiorPhotos.first,
      _interiorPhotos,
    ),
    _AlbumData('Club & Amenities', '20 photos', _clubPhotos.first, _clubPhotos),
    _AlbumData(
      'Landscape & Parks',
      '18 photos',
      _landscapePhotos.first,
      _landscapePhotos,
    ),
  ];
}

class _AlbumViewerPage extends StatefulWidget {
  final _AlbumData album;
  const _AlbumViewerPage({required this.album});

  @override
  State<_AlbumViewerPage> createState() => _AlbumViewerPageState();
}

class _AlbumViewerPageState extends State<_AlbumViewerPage> {
  int _selectedIndex = 0;

  @override
  Widget build(BuildContext context) {
    final album = widget.album;
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        title: Text(album.title),
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
        actions: [
          Center(
            child: Padding(
              padding: const EdgeInsets.only(right: 16),
              child: Text(
                '${_selectedIndex + 1}/${album.photos.length}',
                style: const TextStyle(fontSize: 14, color: Colors.white70),
              ),
            ),
          ),
        ],
      ),
      body: Column(
        children: [
          Expanded(
            child: PageView.builder(
              itemCount: album.photos.length,
              onPageChanged: (i) => setState(() => _selectedIndex = i),
              itemBuilder: (_, i) => InteractiveViewer(
                maxScale: 4,
                child: Center(
                  child: Image.network(
                    album.photos[i],
                    fit: BoxFit.contain,
                    loadingBuilder: (_, child, progress) {
                      if (progress == null) return child;
                      return Center(
                        child: CircularProgressIndicator(
                          value: progress.expectedTotalBytes != null
                              ? progress.cumulativeBytesLoaded /
                                    progress.expectedTotalBytes!
                              : null,
                          color: Colors.white,
                        ),
                      );
                    },
                    errorBuilder: (_, _, _) => const Center(
                      child: Icon(
                        Icons.broken_image_rounded,
                        size: 64,
                        color: Colors.white38,
                      ),
                    ),
                  ),
                ),
              ),
            ),
          ),
          Container(
            height: 100,
            padding: const EdgeInsets.symmetric(vertical: 8),
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 12),
              itemCount: album.photos.length,
              separatorBuilder: (_, _) => const SizedBox(width: 8),
              itemBuilder: (_, i) {
                final isSelected = i == _selectedIndex;
                return GestureDetector(
                  onTap: () => setState(() => _selectedIndex = i),
                  child: Container(
                    width: 72,
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(
                        color: isSelected ? Colors.white : Colors.transparent,
                        width: 2,
                      ),
                    ),
                    clipBehavior: Clip.antiAlias,
                    child: Image.network(
                      album.photos[i],
                      fit: BoxFit.cover,
                      errorBuilder: (_, _, _) => Container(
                        color: Colors.grey.shade800,
                        child: const Icon(
                          Icons.broken_image,
                          color: Colors.white38,
                        ),
                      ),
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}

class _AlbumData {
  final String title;
  final String count;
  final String cover;
  final List<String> photos;
  const _AlbumData(this.title, this.count, this.cover, this.photos);
}

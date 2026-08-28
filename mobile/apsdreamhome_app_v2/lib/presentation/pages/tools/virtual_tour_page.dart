import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import '../../../core/constants/app_constants.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class VirtualTourPage extends StatefulWidget {
  const VirtualTourPage({super.key});

  @override
  State<VirtualTourPage> createState() => _VirtualTourPageState();
}

class _VirtualTourPageState extends State<VirtualTourPage> {
  List<_TourItem> _tours = [];
  _TourItem? _featuredTour;
  bool _isLoading = true;

  static const _tourIcons = [
    Icons.videocam_rounded,
    Icons.store_rounded,
    Icons.view_in_ar_rounded,
    Icons.airplanemode_active_rounded,
    Icons.celebration_rounded,
    Icons.streetview_rounded,
    Icons.landscape_rounded,
    Icons.location_city_rounded,
  ];

  static const _tourDescriptions = [
    'Complete 4K drone overview + street-level walkthrough',
    'Explore the gated community with landscaped gardens',
    '360° tour of the commercial zone with shops and offices',
    'Aerial footage of the township under development',
    'Swimming pool, gym, community hall in 360°',
    'Walk around colony streets, markets, and parks',
    'See how your plot looks with sample home designs',
    'Neighborhood overview with nearby amenities',
  ];

  @override
  void initState() {
    super.initState();
    _loadTours();
  }

  Future<void> _loadTours() async {
    try {
      AppConstants.initBaseUrl();
      final url = '${AppConstants.baseUrl}/api/v2/mobile/colonies?limit=10';
      final resp = await http
          .get(Uri.parse(url))
          .timeout(const Duration(seconds: 10));
      if (resp.statusCode == 200) {
        final data = jsonDecode(resp.body);
        if (data['success'] == true && data['data'] is List) {
          final colonies = (data['data'] as List).cast<Map<String, dynamic>>();
          if (colonies.isNotEmpty) {
            final tours = colonies.asMap().entries.map((e) {
              final i = e.key;
              final c = e.value;
              final name = (c['name'] ?? 'Colony').toString();
              final district = (c['district_name'] ?? '').toString();
              final plots = (c['total_plots'] ?? 0) is int
                  ? (c['total_plots'] ?? 0) as int
                  : int.tryParse('${c['total_plots']}') ?? 0;
              return _TourItem(
                id: (c['id'] ?? i) is int
                    ? (c['id'] ?? i) as int
                    : int.tryParse('${c['id']}') ?? i,
                title: '$name Walkthrough',
                description: _tourDescriptions[i % _tourDescriptions.length],
                duration: '${5 + (i * 2)} min',
                views: 400 + (i * 200),
                icon: _tourIcons[i % _tourIcons.length],
                colonyName: name,
                district: district,
                totalPlots: plots,
              );
            }).toList();

            setState(() {
              _tours = tours;
              _featuredTour = tours.first;
              _isLoading = false;
            });
            return;
          }
        }
      }
    } catch (_) {}

    // Fallback tours
    setState(() {
      _tours = [
        _TourItem(
          id: 1,
          title: 'Braj Radha Enclave Walkthrough',
          description:
              'Explore the gated community with landscaped gardens and wide avenues',
          duration: '8 min',
          views: 892,
          icon: Icons.videocam_rounded,
          colonyName: 'Braj Radha',
          district: 'Gorakhpur',
          totalPlots: 40,
        ),
        _TourItem(
          id: 2,
          title: 'Raghunath Nagri — Commercial Zone',
          description:
              '360° tour of the commercial district with shops, offices, and plazas',
          duration: '6 min',
          views: 654,
          icon: Icons.store_rounded,
          colonyName: 'Raghunath Nagri',
          district: 'Lucknow',
          totalPlots: 262,
        ),
        _TourItem(
          id: 3,
          title: 'Plot Interior 3D Walkthrough',
          description:
              'See how your plot looks with a sample 3-BHK home design superimposed',
          duration: '4 min',
          views: 2103,
          icon: Icons.view_in_ar_rounded,
          colonyName: 'General',
          district: '',
          totalPlots: 0,
        ),
        _TourItem(
          id: 4,
          title: 'Budh Bihar — Drone Overview',
          description:
              'Aerial footage of the affordable housing township under development',
          duration: '5 min',
          views: 445,
          icon: Icons.airplanemode_active_rounded,
          colonyName: 'Budh Bihar',
          district: 'Gorakhpur',
          totalPlots: 12,
        ),
        _TourItem(
          id: 5,
          title: 'Clubhouse & Amenities Tour',
          description:
              'Swimming pool, gym, community hall, children\'s play area in 360°',
          duration: '7 min',
          views: 756,
          icon: Icons.celebration_rounded,
          colonyName: 'All Colonies',
          district: '',
          totalPlots: 0,
        ),
        _TourItem(
          id: 6,
          title: 'Neighborhood 360° Street View',
          description:
              'Walk around the colony streets, nearby markets, and parks virtually',
          duration: '10 min',
          views: 1123,
          icon: Icons.streetview_rounded,
          colonyName: 'Neighborhood',
          district: '',
          totalPlots: 0,
        ),
      ];
      _featuredTour = _tours.first;
      _isLoading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Virtual Tour'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
      ),
      body: _isLoading
          ? const Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  CircularProgressIndicator(),
                  SizedBox(height: 16),
                  Text('Loading virtual tours...'),
                ],
              ),
            )
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildHeader(),
                  const SizedBox(height: 20),
                  if (_featuredTour != null)
                    _buildFeaturedTour(context, _featuredTour!),
                  const SizedBox(height: 24),
                  const Text(
                    'All Tours',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 12),
                  ..._tours.map(
                    (t) => Padding(
                      padding: const EdgeInsets.only(bottom: 14),
                      child: _buildTourCard(context, t),
                    ),
                  ),
                  const SizedBox(height: 30),
                ],
              ),
            ),
    );
  }

  Widget _buildHeader() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF6A1B9A), Color(0xFF9C27B0)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(Icons.videocam_rounded, size: 36, color: Colors.white),
          const SizedBox(height: 12),
          const Text(
            '360° Virtual Walkthrough',
            style: TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Explore properties from anywhere with immersive virtual tours',
            style: TextStyle(
              fontSize: 13,
              color: Colors.white.withValues(alpha: 0.85),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFeaturedTour(BuildContext context, _TourItem tour) {
    return GestureDetector(
      onTap: () => _showTourPreview(context, tour),
      child: Container(
        height: 220,
        width: double.infinity,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(16),
          gradient: const LinearGradient(
            colors: [Color(0xFF1A237E), Color(0xFF4A148C)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          boxShadow: [
            BoxShadow(
              color: const Color(0xFF1A237E).withValues(alpha: 0.3),
              blurRadius: 20,
              offset: const Offset(0, 8),
            ),
          ],
        ),
        child: Stack(
          children: [
            Positioned(
              right: -30,
              top: -30,
              child: Icon(
                Icons.videocam_rounded,
                size: 160,
                color: Colors.white.withValues(alpha: 0.08),
              ),
            ),
            Positioned(
              bottom: -20,
              left: -20,
              child: Icon(
                Icons.explore_rounded,
                size: 120,
                color: Colors.white.withValues(alpha: 0.06),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 10,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      color: Colors.amber.withValues(alpha: 0.2),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.star_rounded, size: 14, color: Colors.amber),
                        SizedBox(width: 4),
                        Text(
                          'Featured Tour',
                          style: TextStyle(
                            fontSize: 11,
                            color: Colors.amber,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const Spacer(),
                  Text(
                    tour.title,
                    style: const TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                      height: 1.3,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    '${tour.colonyName}${tour.district.isNotEmpty ? " • ${tour.district}" : ""}  •  ${tour.duration}',
                    style: TextStyle(
                      fontSize: 12,
                      color: Colors.white.withValues(alpha: 0.7),
                    ),
                  ),
                  const SizedBox(height: 12),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 20,
                      vertical: 10,
                    ),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.2),
                      borderRadius: BorderRadius.circular(24),
                      border: Border.all(
                        color: Colors.white.withValues(alpha: 0.3),
                      ),
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          Icons.play_arrow_rounded,
                          color: Colors.white,
                          size: 20,
                        ),
                        SizedBox(width: 6),
                        Text(
                          'Start Tour',
                          style: TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w600,
                            fontSize: 13,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTourCard(BuildContext context, _TourItem tour) {
    final colors = <Color>[
      const Color(0xFF1565C0),
      const Color(0xFF2E7D32),
      const Color(0xFFE65100),
      const Color(0xFF6A1B9A),
      const Color(0xFFC62828),
      const Color(0xFF00838F),
    ];

    return GestureDetector(
      onTap: () => _showTourPreview(context, tour),
      child: GlassCard(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            Container(
              width: 64,
              height: 64,
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [
                    colors[tour.id % colors.length],
                    colors[(tour.id + 1) % colors.length],
                  ],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Center(
                child: Icon(tour.icon, color: Colors.white, size: 28),
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          tour.title,
                          style: const TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 3,
                        ),
                        decoration: BoxDecoration(
                          color: Colors.grey.withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Text(
                          tour.duration,
                          style: TextStyle(
                            fontSize: 10,
                            color: Colors.grey.shade600,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text(
                    tour.description,
                    style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Icon(
                        Icons.visibility_rounded,
                        size: 14,
                        color: Colors.grey.shade500,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        '${tour.views} views',
                        style: TextStyle(
                          fontSize: 11,
                          color: Colors.grey.shade500,
                        ),
                      ),
                      if (tour.totalPlots > 0) ...[
                        const SizedBox(width: 12),
                        Icon(
                          Icons.landscape_rounded,
                          size: 14,
                          color: Colors.grey.shade500,
                        ),
                        const SizedBox(width: 4),
                        Text(
                          '${tour.totalPlots} plots',
                          style: TextStyle(
                            fontSize: 11,
                            color: Colors.grey.shade500,
                          ),
                        ),
                      ],
                      const Spacer(),
                      const Icon(
                        Icons.play_circle_fill_rounded,
                        size: 18,
                        color: AppTheme.primaryColor,
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
  }

  void _showTourPreview(BuildContext context, _TourItem tour) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => _TourPreviewSheet(tour: tour),
    );
  }
}

class _TourItem {
  final int id;
  final String title;
  final String description;
  final String duration;
  final int views;
  final IconData icon;
  final String colonyName;
  final String district;
  final int totalPlots;

  const _TourItem({
    required this.id,
    required this.title,
    required this.description,
    required this.duration,
    required this.views,
    required this.icon,
    this.colonyName = '',
    this.district = '',
    this.totalPlots = 0,
  });
}

class _TourPreviewSheet extends StatelessWidget {
  final _TourItem tour;

  const _TourPreviewSheet({required this.tour});

  @override
  Widget build(BuildContext context) {
    return Container(
      height: MediaQuery.of(context).size.height * 0.6,
      decoration: const BoxDecoration(
        color: Color(0xFF1A1A2E),
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: Column(
        children: [
          Container(
            margin: const EdgeInsets.only(top: 12),
            width: 40,
            height: 4,
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.3),
              borderRadius: BorderRadius.circular(2),
            ),
          ),
          const SizedBox(height: 24),
          Expanded(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Container(
                    width: 120,
                    height: 120,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      gradient: LinearGradient(
                        colors: [
                          AppTheme.primaryColor,
                          AppTheme.primaryColor.withValues(alpha: 0.5),
                        ],
                      ),
                    ),
                    child: const Icon(
                      Icons.videocam_rounded,
                      size: 56,
                      color: Colors.white,
                    ),
                  ),
                  const SizedBox(height: 24),
                  Text(
                    tour.title,
                    style: const TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 8),
                  Text(
                    tour.description,
                    style: TextStyle(
                      fontSize: 13,
                      color: Colors.white.withValues(alpha: 0.7),
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 12),
                  Text(
                    'Duration: ${tour.duration}  •  ${tour.views}+ views',
                    style: TextStyle(
                      fontSize: 12,
                      color: Colors.white.withValues(alpha: 0.5),
                    ),
                  ),
                  const SizedBox(height: 30),
                  SizedBox(
                    width: double.infinity,
                    height: 50,
                    child: ElevatedButton.icon(
                      onPressed: () {
                        Navigator.pop(context);
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            content: Text(
                              'Tour playback started — 360° view loading...',
                            ),
                            behavior: SnackBarBehavior.floating,
                          ),
                        );
                      },
                      icon: const Icon(Icons.play_arrow_rounded),
                      label: const Text('Start Virtual Tour'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTheme.accentColor,
                        foregroundColor: Colors.black,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(16),
                        ),
                        textStyle: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

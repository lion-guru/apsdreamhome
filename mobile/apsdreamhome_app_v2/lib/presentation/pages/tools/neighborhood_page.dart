import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

import '../../../core/constants/app_constants.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class NeighborhoodPage extends StatefulWidget {
  const NeighborhoodPage({super.key, this.colonyId, this.colonyName});

  final String? colonyId;
  final String? colonyName;

  @override
  State<NeighborhoodPage> createState() => _NeighborhoodPageState();
}

class _NeighborhoodPageState extends State<NeighborhoodPage> {
  bool _loading = true;
  String? _error;

  int _walkScore = 0;
  int _transitScore = 0;
  int _lifestyleScore = 0;

  Map<String, List<_AmenityItem>> _landmarksByType = {};

  // Type → icon/color mapping
  static const Map<String, Map<String, dynamic>> _typeConfig = {
    'school': {
      'icon': Icons.school_rounded,
      'color': Color(0xFF1565C0),
      'label': 'Education',
    },
    'hospital': {
      'icon': Icons.local_hospital_rounded,
      'color': Color(0xFFE53935),
      'label': 'Healthcare',
    },
    'mall': {
      'icon': Icons.store_mall_directory_rounded,
      'color': Color(0xFFFB8C00),
      'label': 'Shopping & Dining',
    },
    'market': {
      'icon': Icons.storefront_rounded,
      'color': Color(0xFFFB8C00),
      'label': 'Shopping & Dining',
    },
    'metro_station': {
      'icon': Icons.subway_rounded,
      'color': Color(0xFF6A1B9A),
      'label': 'Transport',
    },
    'railway_station': {
      'icon': Icons.train_rounded,
      'color': Color(0xFF6A1B9A),
      'label': 'Transport',
    },
    'bus_stand': {
      'icon': Icons.directions_bus_rounded,
      'color': Color(0xFF6A1B9A),
      'label': 'Transport',
    },
    'airport': {
      'icon': Icons.flight_rounded,
      'color': Color(0xFF6A1B9A),
      'label': 'Transport',
    },
    'bank': {
      'icon': Icons.account_balance_rounded,
      'color': Color(0xFF1565C0),
      'label': 'Banking & ATMs',
    },
    'park': {
      'icon': Icons.park_rounded,
      'color': Color(0xFF2E7D32),
      'label': 'Recreation',
    },
    'university': {
      'icon': Icons.school_rounded,
      'color': Color(0xFF1565C0),
      'label': 'Education',
    },
    'temple': {
      'icon': Icons.temple_hindu_rounded,
      'color': Color(0xFF795548),
      'label': 'Recreation',
    },
    'police_station': {
      'icon': Icons.local_police_rounded,
      'color': Color(0xFF455A64),
      'label': 'Safety',
    },
    'fire_station': {
      'icon': Icons.fire_truck_rounded,
      'color': Color(0xFFD84315),
      'label': 'Safety',
    },
    'post_office': {
      'icon': Icons.markunread_mailbox_rounded,
      'color': Color(0xFF0277BD),
      'label': 'Services',
    },
    'court': {
      'icon': Icons.gavel_rounded,
      'color': Color(0xFF4E342E),
      'label': 'Services',
    },
    'government_office': {
      'icon': Icons.account_balance_rounded,
      'color': Color(0xFF37474F),
      'label': 'Services',
    },
  };

  // Display order for sections
  static const List<String> _sectionOrder = [
    'school',
    'university',
    'hospital',
    'mall',
    'market',
    'metro_station',
    'railway_station',
    'bus_stand',
    'airport',
    'bank',
    'park',
    'temple',
    'police_station',
    'fire_station',
    'post_office',
    'court',
    'government_office',
  ];

  @override
  void initState() {
    super.initState();
    _fetchLandmarks();
  }

  Future<void> _fetchLandmarks() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      // Try colony-based endpoint first
      if (widget.colonyId != null && widget.colonyId!.isNotEmpty) {
        final url =
            '${AppConstants.baseUrl}/api/landmarks/colony/${widget.colonyId}';
        final response = await http.get(Uri.parse(url));

        if (response.statusCode == 200) {
          final decoded = json.decode(response.body);
          if (decoded is Map && decoded['success'] == true) {
            final data = Map<String, dynamic>.from(decoded);
            _parseColonyResponse(data);
            setState(() => _loading = false);
            return;
          }
        }
      }

      // Fallback: fetch all landmarks
      final url = '${AppConstants.baseUrl}/api/landmarks/list?limit=30';
      final response = await http.get(Uri.parse(url));

      if (response.statusCode == 200) {
        final decoded = json.decode(response.body);
        if (decoded is Map && decoded['success'] == true) {
          final data = Map<String, dynamic>.from(decoded);
          _parseListResponse(data);
          setState(() => _loading = false);
          return;
        }
      }

      // If API fails, use mock data as last resort
      _loadMockData();
      setState(() => _loading = false);
    } catch (e) {
      // Network error — use mock data
      _loadMockData();
      setState(() => _loading = false);
    }
  }

  void _parseColonyResponse(Map<String, dynamic> data) {
    final scores = data['scores'];
    if (scores is Map) {
      _walkScore = int.tryParse('${scores['walk'] ?? 0}') ?? 0;
      _transitScore = int.tryParse('${scores['transit'] ?? 0}') ?? 0;
      _lifestyleScore = int.tryParse('${scores['lifestyle'] ?? 0}') ?? 0;
    }

    final byType = data['by_type'];
    if (byType is Map) {
      _landmarksByType = {};
      byType.forEach((type, landmarks) {
        if (landmarks is List) {
          _landmarksByType[type.toString()] = landmarks
              .map(
                (lm) => _AmenityItem.fromApi(
                  Map<String, dynamic>.from(lm as Map),
                  type.toString(),
                ),
              )
              .toList();
        }
      });
    }
  }

  void _parseListResponse(Map<String, dynamic> data) {
    final landmarks = data['landmarks'];
    if (landmarks is List) {
      _landmarksByType = {};
      for (final lm in landmarks) {
        final type = lm['type']?.toString() ?? 'other';
        _landmarksByType[type] ??= [];
        _landmarksByType[type]!.add(
          _AmenityItem.fromApi(Map<String, dynamic>.from(lm as Map), type),
        );
      }
      // Calculate approximate scores from landmark count
      final total = landmarks.length;
      _walkScore = (total * 2.5).clamp(0, 100).toInt();
      _transitScore = (total * 2.0).clamp(0, 100).toInt();
      _lifestyleScore = (total * 2.8).clamp(0, 100).toInt();
    }
  }

  void _loadMockData() {
    _walkScore = 82;
    _transitScore = 74;
    _lifestyleScore = 91;

    _landmarksByType = {
      'school': [
        _AmenityItem(
          name: 'DPS School',
          distance: '0.8 km',
          icon: Icons.school_rounded,
          color: const Color(0xFF1565C0),
          rating: 4.5,
        ),
        _AmenityItem(
          name: 'Kendriya Vidyalaya',
          distance: '1.8 km',
          icon: Icons.school_rounded,
          color: const Color(0xFF1565C0),
          rating: 3.8,
        ),
      ],
      'hospital': [
        _AmenityItem(
          name: 'District Hospital',
          distance: '1.5 km',
          icon: Icons.local_hospital_rounded,
          color: const Color(0xFFE53935),
          rating: 4.2,
        ),
      ],
      'bank': [
        _AmenityItem(
          name: 'State Bank of India',
          distance: '0.5 km',
          icon: Icons.account_balance_rounded,
          color: const Color(0xFF1565C0),
          rating: 4.2,
        ),
      ],
      'park': [
        _AmenityItem(
          name: 'City Park',
          distance: '0.6 km',
          icon: Icons.park_rounded,
          color: const Color(0xFF2E7D32),
          rating: 4.6,
        ),
      ],
    };
  }

  @override
  Widget build(BuildContext context) {
    final displayTitle = widget.colonyName ?? 'Neighborhood';

    return Scaffold(
      appBar: AppBar(
        title: Text(displayTitle),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
          ? _buildErrorWidget()
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildHeader(displayTitle),
                  const SizedBox(height: 20),
                  _buildScoreRow(),
                  const SizedBox(height: 24),
                  ..._buildLandmarkSections(),
                  const SizedBox(height: 30),
                ],
              ),
            ),
    );
  }

  Widget _buildErrorWidget() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.error_outline, size: 48, color: Colors.red),
          const SizedBox(height: 16),
          Text(_error ?? 'Failed to load landmarks'),
          const SizedBox(height: 16),
          ElevatedButton(
            onPressed: _fetchLandmarks,
            child: const Text('Retry'),
          ),
        ],
      ),
    );
  }

  Widget _buildHeader(String title) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF1565C0), Color(0xFF42A5F5)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(Icons.explore_rounded, size: 40, color: Colors.white),
          const SizedBox(height: 12),
          const Text(
            'Neighborhood Analysis',
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Everything around $title',
            style: TextStyle(
              fontSize: 13,
              color: Colors.white.withValues(alpha: 0.85),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildScoreRow() {
    return Row(
      children: [
        Expanded(
          child: _buildScoreCard('Walk Score', _walkScore, Colors.green),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: _buildScoreCard('Transit Score', _transitScore, Colors.orange),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: _buildScoreCard('Lifestyle', _lifestyleScore, Colors.blue),
        ),
      ],
    );
  }

  Widget _buildScoreCard(String label, int score, Color color) {
    return GlassCard(
      padding: const EdgeInsets.all(14),
      child: Column(
        children: [
          SizedBox(
            width: 50,
            height: 50,
            child: Stack(
              alignment: Alignment.center,
              children: [
                SizedBox(
                  width: 50,
                  height: 50,
                  child: CircularProgressIndicator(
                    value: score / 100,
                    strokeWidth: 4,
                    backgroundColor: Colors.grey.withValues(alpha: 0.2),
                    valueColor: AlwaysStoppedAnimation(color),
                  ),
                ),
                Text(
                  '$score',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    color: color,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 8),
          Text(
            label,
            style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w500),
          ),
        ],
      ),
    );
  }

  List<Widget> _buildLandmarkSections() {
    final sections = <Widget>[];
    final seenLabels = <String>{};

    for (final type in _sectionOrder) {
      final landmarks = _landmarksByType[type];
      if (landmarks == null || landmarks.isEmpty) continue;

      final config = _typeConfig[type];
      final label = (config?['label'] ?? type).toString();

      // Skip duplicate section labels
      if (seenLabels.contains(label)) continue;
      seenLabels.add(label);

      sections.add(
        _buildSectionTitle(
          config?['icon'] as IconData? ?? Icons.location_on_rounded,
          label,
        ),
      );
      sections.add(const SizedBox(height: 12));
      sections.addAll(landmarks.map(_buildAmenityCard));
      sections.add(const SizedBox(height: 20));
    }

    // Add any remaining types not in section order
    for (final entry in _landmarksByType.entries) {
      if (_sectionOrder.contains(entry.key)) continue;
      if (entry.value.isEmpty) continue;

      final config = _typeConfig[entry.key];
      final label = (config?['label'] ?? entry.key).toString();

      if (seenLabels.contains(label)) continue;
      seenLabels.add(label);

      sections.add(
        _buildSectionTitle(
          config?['icon'] as IconData? ?? Icons.location_on_rounded,
          label,
        ),
      );
      sections.add(const SizedBox(height: 12));
      sections.addAll(entry.value.map(_buildAmenityCard));
      sections.add(const SizedBox(height: 20));
    }

    return sections;
  }

  Widget _buildSectionTitle(IconData icon, String title) {
    return Row(
      children: [
        Icon(icon, size: 20, color: AppTheme.primaryColor),
        const SizedBox(width: 8),
        Text(
          title,
          style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
        ),
      ],
    );
  }

  Widget _buildAmenityCard(_AmenityItem item) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: GlassCard(
        padding: const EdgeInsets.all(14),
        child: Row(
          children: [
            Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                color: item.color.withValues(alpha: 0.15),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(item.icon, color: item.color, size: 22),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    item.name,
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    item.distance,
                    style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                  ),
                ],
              ),
            ),
            if (item.rating > 0)
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 4,
                ),
                decoration: BoxDecoration(
                  color: item.rating >= 4.0
                      ? Colors.green.withValues(alpha: 0.15)
                      : item.rating >= 3.0
                      ? Colors.orange.withValues(alpha: 0.15)
                      : Colors.red.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      Icons.star_rounded,
                      size: 14,
                      color: item.rating >= 4.0
                          ? Colors.green
                          : item.rating >= 3.0
                          ? Colors.orange
                          : Colors.red,
                    ),
                    const SizedBox(width: 2),
                    Text(
                      item.rating.toStringAsFixed(1),
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: item.rating >= 4.0
                            ? Colors.green
                            : item.rating >= 3.0
                            ? Colors.orange
                            : Colors.red,
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
}

class _AmenityItem {
  final String name;
  final String distance;
  final IconData icon;
  final Color color;
  final double rating;

  const _AmenityItem({
    required this.name,
    required this.distance,
    required this.icon,
    required this.color,
    required this.rating,
  });

  factory _AmenityItem.fromApi(Map<String, dynamic> json, String type) {
    final config = _NeighborhoodPageState._typeConfig[type];
    final icon = config?['icon'] as IconData? ?? Icons.location_on_rounded;
    final color = config?['color'] as Color? ?? Colors.blue;

    final distKm = json['distance_km'];
    String distanceStr;
    if (distKm != null) {
      final km = double.tryParse('$distKm') ?? 0;
      distanceStr = '${km.toStringAsFixed(1)} km';
    } else {
      distanceStr = 'Nearby';
    }

    return _AmenityItem(
      name: json['name']?.toString() ?? 'Unknown',
      distance: distanceStr,
      icon: icon,
      color: color,
      rating: double.tryParse('${json['rating'] ?? 0}') ?? 0,
    );
  }
}

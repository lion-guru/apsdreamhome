import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:http/http.dart' as http;

import '../../../core/constants/app_constants.dart';

/// Tinder-Style Property Discovery Page
/// Users swipe right (interested) or left (skip) on property cards.
class PropertySwipePage extends ConsumerStatefulWidget {
  const PropertySwipePage({super.key});

  @override
  ConsumerState<PropertySwipePage> createState() => _PropertySwipePageState();
}

class _PropertySwipePageState extends ConsumerState<PropertySwipePage>
    with TickerProviderStateMixin {
  late AnimationController _swipeController;

  Offset _dragOffset = Offset.zero;
  int _currentIndex = 0;
  final List<Map<String, dynamic>> _savedProperties = [];
  List<Map<String, dynamic>> _properties = [];
  bool _isLoading = true;
  String? _error;

  static const _gradients = [
    [0xFF1A237E, 0xFF283593],
    [0xFF1B5E20, 0xFF2E7D32],
    [0xFF4A148C, 0xFF6A1B9A],
    [0xFF880E4F, 0xFFAD1457],
    [0xFF006064, 0xFF00838F],
    [0xFFE65100, 0xFFF57C00],
    [0xFF283593, 0xFF3949AB],
  ];

  static const _icons = [
    Icons.landscape,
    Icons.home,
    Icons.apartment,
    Icons.store,
    Icons.villa,
    Icons.domain,
    Icons.location_city,
  ];

  @override
  void initState() {
    super.initState();
    _swipeController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 300),
    );
    _loadColonies();
  }

  Future<void> _loadColonies() async {
    try {
      AppConstants.initBaseUrl();
      final url = '${AppConstants.baseUrl}/api/v2/mobile/colonies?limit=20';
      final resp = await http
          .get(Uri.parse(url))
          .timeout(const Duration(seconds: 10));
      if (resp.statusCode == 200) {
        final data = jsonDecode(resp.body);
        if (data['success'] == true && data['data'] is List) {
          final colonies = (data['data'] as List).cast<Map<String, dynamic>>();
          if (colonies.isNotEmpty) {
            setState(() {
              _properties = colonies.asMap().entries.map((e) {
                final i = e.key;
                final c = e.value;
                final price = (c['starting_price'] as num?)?.toDouble() ?? 0;
                final total = c['total_plots'] ?? 0;
                final available = c['available_plots'] ?? 0;
                final district = c['district_name'] ?? 'UP';
                final featured =
                    c['is_featured'] == true || c['is_featured'] == 1;
                return {
                  'id': c['id'] ?? i,
                  'title': c['name'] ?? 'Colony',
                  'location': '$district, Uttar Pradesh',
                  'price': _formatPrice(price),
                  'size': '$total plots ($available available)',
                  'type': 'Residential',
                  'badge': featured ? '🏆 Featured' : '🏗️ New Colony',
                  'color': _gradients[i % _gradients.length],
                  'icon': _icons[i % _icons.length],
                };
              }).toList();
              _isLoading = false;
            });
            return;
          }
        }
      }
    } catch (_) {}
    // Fallback to mock data
    setState(() {
      _properties = [
        {
          'id': 1,
          'title': 'Luxury 3BHK Apartment',
          'location': 'Gomti Nagar, Lucknow',
          'price': '₹85 Lakhs',
          'size': '1450 sq. ft.',
          'type': 'Apartment',
          'badge': '🏆 Featured',
          'color': [0xFF1A237E, 0xFF283593],
          'icon': Icons.apartment,
        },
        {
          'id': 2,
          'title': 'Premium Villa',
          'location': 'Hazratganj, Lucknow',
          'price': '₹1.8 Crore',
          'size': '3200 sq. ft.',
          'type': 'Villa',
          'badge': '🌿 Premium',
          'color': [0xFF1B5E20, 0xFF2E7D32],
          'icon': Icons.home,
        },
        {
          'id': 3,
          'title': 'Commercial Shop',
          'location': 'Vibhuti Khand, Lucknow',
          'price': '₹45 Lakhs',
          'size': '650 sq. ft.',
          'type': 'Commercial',
          'badge': '📈 High ROI',
          'color': [0xFF4A148C, 0xFF6A1B9A],
          'icon': Icons.store,
        },
      ];
      _isLoading = false;
    });
  }

  String _formatPrice(double price) {
    if (price >= 10000000) {
      return '₹${(price / 10000000).toStringAsFixed(2)} Cr';
    } else if (price >= 100000) {
      return '₹${(price / 100000).toStringAsFixed(1)} Lakh';
    } else if (price >= 1000) {
      return '₹${(price / 1000).toStringAsFixed(0)}K';
    }
    return '₹${price.toStringAsFixed(0)}';
  }

  @override
  void dispose() {
    _swipeController.dispose();
    super.dispose();
  }

  void _onDragUpdate(DragUpdateDetails details) {
    setState(() {
      _dragOffset += details.delta;
    });
  }

  void _onDragEnd(DragEndDetails details) {
    const threshold = 120.0;

    if (_dragOffset.dx > threshold) {
      _swipeLike();
    } else if (_dragOffset.dx < -threshold) {
      _swipeDislike();
    } else {
      // Snap back
      setState(() {
        _dragOffset = Offset.zero;
      });
    }
  }

  void _swipeLike() {
    if (_currentIndex < _properties.length) {
      setState(() {
        _savedProperties.add(_properties[_currentIndex]);
      });
    }
    _nextCard();
  }

  void _swipeDislike() {
    _nextCard();
  }

  void _nextCard() {
    setState(() {
      _dragOffset = Offset.zero;
      if (_currentIndex < _properties.length) {
        _currentIndex++;
      }
    });
  }

  double get _swipeAngle => (_dragOffset.dx / 300.0) * 0.2;
  bool get _isLiking => _dragOffset.dx > 60;
  bool get _isDisliking => _dragOffset.dx < -60;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0A1628),
      appBar: AppBar(
        backgroundColor: const Color(0xFF0A1628),
        title: const Text(
          'Discover Properties',
          style: TextStyle(color: Colors.white),
        ),
        iconTheme: const IconThemeData(color: Colors.white),
        actions: [
          if (_savedProperties.isNotEmpty)
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 8.0),
              child: Center(
                child: Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 6,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.green.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: Colors.green),
                  ),
                  child: Text(
                    '❤️ ${_savedProperties.length} Saved',
                    style: const TextStyle(
                      color: Colors.green,
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ),
            ),
        ],
      ),
      body: _isLoading
          ? const Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  CircularProgressIndicator(color: Colors.white),
                  SizedBox(height: 16),
                  Text(
                    'Loading colonies...',
                    style: TextStyle(color: Colors.white70),
                  ),
                ],
              ),
            )
          : _error != null
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.error_outline, color: Colors.red, size: 48),
                  const SizedBox(height: 12),
                  Text(_error!, style: const TextStyle(color: Colors.white70)),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: () {
                      setState(() {
                        _isLoading = true;
                        _error = null;
                      });
                      _loadColonies();
                    },
                    child: const Text('Retry'),
                  ),
                ],
              ),
            )
          : Column(
              children: [
                const SizedBox(height: 20),
                Expanded(
                  child: _currentIndex >= _properties.length
                      ? _buildAllDoneView()
                      : Stack(
                          alignment: Alignment.center,
                          children: [
                            if (_currentIndex + 1 < _properties.length)
                              Transform.scale(
                                scale: 0.94,
                                child: _buildCard(
                                  _properties[_currentIndex + 1],
                                  false,
                                ),
                              ),
                            GestureDetector(
                              onPanUpdate: _onDragUpdate,
                              onPanEnd: _onDragEnd,
                              child: Transform.translate(
                                offset: _dragOffset,
                                child: Transform.rotate(
                                  angle: _swipeAngle,
                                  child: Stack(
                                    children: [
                                      _buildCard(
                                        _properties[_currentIndex],
                                        true,
                                      ),
                                      if (_isLiking)
                                        Positioned(
                                          top: 40,
                                          left: 20,
                                          child: _buildSwipeBadge(
                                            '❤️ LIKE',
                                            Colors.green,
                                          ),
                                        ),
                                      if (_isDisliking)
                                        Positioned(
                                          top: 40,
                                          right: 20,
                                          child: _buildSwipeBadge(
                                            '✖ SKIP',
                                            Colors.red,
                                          ),
                                        ),
                                    ],
                                  ),
                                ),
                              ),
                            ),
                          ],
                        ),
                ),
                Padding(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 40,
                    vertical: 20,
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                    children: [
                      _buildActionBtn(Icons.close, Colors.red, _swipeDislike),
                      _buildActionBtn(Icons.info_outline, Colors.blue, () {}),
                      _buildActionBtn(Icons.favorite, Colors.green, _swipeLike),
                    ],
                  ),
                ),
              ],
            ),
    );
  }

  Widget _buildCard(Map<String, dynamic> property, bool isTop) {
    final colors = (property['color'] as List).cast<int>();
    return Container(
      width: MediaQuery.of(context).size.width - 48,
      height: MediaQuery.of(context).size.height * 0.58,
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [Color(colors[0]), Color(colors[1])],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.3),
            blurRadius: 15,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.2),
                borderRadius: BorderRadius.circular(20),
              ),
              child: Text(
                property['badge'] as String,
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
            const Spacer(),
            Icon(
              property['icon'] as IconData,
              color: Colors.white.withValues(alpha: 0.3),
              size: 80,
            ),
            const Spacer(),
            Text(
              property['title'] as String,
              style: const TextStyle(
                color: Colors.white,
                fontSize: 22,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 4),
            Row(
              children: [
                const Icon(Icons.location_on, color: Colors.white70, size: 16),
                const SizedBox(width: 4),
                Text(
                  property['location'] as String,
                  style: const TextStyle(color: Colors.white70, fontSize: 14),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                _buildPill('💰 ${property['price']}'),
                _buildPill('📐 ${property['size']}'),
                _buildPill('🏷️ ${property['type']}'),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPill(String text) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: Colors.black.withValues(alpha: 0.25),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        text,
        style: const TextStyle(color: Colors.white, fontSize: 12),
      ),
    );
  }

  Widget _buildSwipeBadge(String text, Color color) {
    return Transform.rotate(
      angle: text.contains('LIKE') ? -0.3 : 0.3,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.9),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: Colors.white, width: 3),
        ),
        child: Text(
          text,
          style: const TextStyle(
            color: Colors.white,
            fontSize: 22,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
    );
  }

  Widget _buildActionBtn(IconData icon, Color color, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: 60,
        height: 60,
        decoration: BoxDecoration(
          shape: BoxShape.circle,
          color: color.withValues(alpha: 0.15),
          border: Border.all(color: color, width: 2),
          boxShadow: [
            BoxShadow(color: color.withValues(alpha: 0.3), blurRadius: 8),
          ],
        ),
        child: Icon(icon, color: color, size: 30),
      ),
    );
  }

  Widget _buildAllDoneView() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Text('🎉', style: TextStyle(fontSize: 72)),
          const SizedBox(height: 16),
          const Text(
            'You\'ve seen all properties!',
            style: TextStyle(
              color: Colors.white,
              fontSize: 20,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            '${_savedProperties.length} properties saved ❤️',
            style: const TextStyle(color: Colors.white54, fontSize: 16),
          ),
          const SizedBox(height: 24),
          ElevatedButton(
            onPressed: () => setState(() {
              _currentIndex = 0;
              _savedProperties.clear();
            }),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF1A237E),
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 14),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
            ),
            child: const Text('Restart Discovery'),
          ),
        ],
      ),
    );
  }
}

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// Tinder-Style Property Discovery Page
/// Users swipe right (interested) or left (skip) on property cards.
class PropertySwipePage extends ConsumerStatefulWidget {
  const PropertySwipePage({Key? key}) : super(key: key);

  @override
  ConsumerState<PropertySwipePage> createState() => _PropertySwipePageState();
}

class _PropertySwipePageState extends ConsumerState<PropertySwipePage>
    with TickerProviderStateMixin {
  late AnimationController _swipeController;
  late Animation<Offset> _swipeAnimation;
  late Animation<double> _rotationAnimation;

  Offset _dragOffset = Offset.zero;
  bool _isDragging = false;
  int _currentIndex = 0;
  List<Map<String, dynamic>> _savedProperties = [];

  // Dummy property data for demonstration
  final List<Map<String, dynamic>> _properties = [
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
    {
      'id': 4,
      'title': 'Residential Plot',
      'location': 'Indiranagar, Lucknow',
      'price': '₹35 Lakhs',
      'size': '1800 sq. ft.',
      'type': 'Plot',
      'badge': '🔥 New Launch',
      'color': [0xFF880E4F, 0xFFAD1457],
      'icon': Icons.landscape,
    },
    {
      'id': 5,
      'title': 'Modern 2BHK Flat',
      'location': 'Mahanagar, Lucknow',
      'price': '₹55 Lakhs',
      'size': '1100 sq. ft.',
      'type': 'Apartment',
      'badge': '✨ Ready to Move',
      'color': [0xFF006064, 0xFF00838F],
      'icon': Icons.apartment,
    },
  ];

  @override
  void initState() {
    super.initState();
    _swipeController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 300),
    );
    _swipeAnimation = Tween<Offset>(
      begin: Offset.zero,
      end: Offset.zero,
    ).animate(_swipeController);
    _rotationAnimation = Tween<double>(begin: 0, end: 0).animate(_swipeController);
  }

  @override
  void dispose() {
    _swipeController.dispose();
    super.dispose();
  }

  void _onDragUpdate(DragUpdateDetails details) {
    setState(() {
      _isDragging = true;
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
        _isDragging = false;
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
      _isDragging = false;
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
        title: const Text('Discover Properties', style: TextStyle(color: Colors.white)),
        iconTheme: const IconThemeData(color: Colors.white),
        actions: [
          if (_savedProperties.isNotEmpty)
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 8.0),
              child: Center(
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: Colors.green.withOpacity(0.2),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: Colors.green),
                  ),
                  child: Text(
                    '❤️ ${_savedProperties.length} Saved',
                    style: const TextStyle(color: Colors.green, fontSize: 13, fontWeight: FontWeight.bold),
                  ),
                ),
              ),
            ),
        ],
      ),
      body: Column(
        children: [
          const SizedBox(height: 20),

          // Stack of Cards
          Expanded(
            child: _currentIndex >= _properties.length
                ? _buildAllDoneView()
                : Stack(
                    alignment: Alignment.center,
                    children: [
                      // Background card (next)
                      if (_currentIndex + 1 < _properties.length)
                        Transform.scale(
                          scale: 0.94,
                          child: _buildCard(_properties[_currentIndex + 1], false),
                        ),

                      // Front card (draggable)
                      GestureDetector(
                        onPanUpdate: _onDragUpdate,
                        onPanEnd: _onDragEnd,
                        child: Transform.translate(
                          offset: _dragOffset,
                          child: Transform.rotate(
                            angle: _swipeAngle,
                            child: Stack(
                              children: [
                                _buildCard(_properties[_currentIndex], true),
                                // LIKE badge
                                if (_isLiking)
                                  Positioned(
                                    top: 40,
                                    left: 20,
                                    child: _buildSwipeBadge('❤️ LIKE', Colors.green),
                                  ),
                                // NOPE badge
                                if (_isDisliking)
                                  Positioned(
                                    top: 40,
                                    right: 20,
                                    child: _buildSwipeBadge('✖ SKIP', Colors.red),
                                  ),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
          ),

          // Action Buttons
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 40, vertical: 20),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: [
                _buildActionBtn(Icons.close, Colors.red, _swipeDislike),
                _buildActionBtn(Icons.info_outline, Colors.blue, () {
                  // Show details
                }),
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
            color: Colors.black.withOpacity(0.3),
            blurRadius: 15,
            offset: const Offset(0, 8),
          )
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
                color: Colors.white.withOpacity(0.2),
                borderRadius: BorderRadius.circular(20),
              ),
              child: Text(
                property['badge'] as String,
                style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.w600),
              ),
            ),
            const Spacer(),
            Icon(property['icon'] as IconData, color: Colors.white.withOpacity(0.3), size: 80),
            const Spacer(),
            Text(
              property['title'] as String,
              style: const TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 4),
            Row(
              children: [
                const Icon(Icons.location_on, color: Colors.white70, size: 16),
                const SizedBox(width: 4),
                Text(property['location'] as String, style: const TextStyle(color: Colors.white70, fontSize: 14)),
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
        color: Colors.black.withOpacity(0.25),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(text, style: const TextStyle(color: Colors.white, fontSize: 12)),
    );
  }

  Widget _buildSwipeBadge(String text, Color color) {
    return Transform.rotate(
      angle: text.contains('LIKE') ? -0.3 : 0.3,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        decoration: BoxDecoration(
          color: color.withOpacity(0.9),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: Colors.white, width: 3),
        ),
        child: Text(text, style: const TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.bold)),
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
          color: color.withOpacity(0.15),
          border: Border.all(color: color, width: 2),
          boxShadow: [BoxShadow(color: color.withOpacity(0.3), blurRadius: 8)],
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
            style: TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold),
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
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            child: const Text('Restart Discovery'),
          ),
        ],
      ),
    );
  }
}

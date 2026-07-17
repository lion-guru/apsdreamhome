import 'package:flutter/material.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class NeighborhoodPage extends StatelessWidget {
  const NeighborhoodPage({super.key, this.colonyId, this.colonyName});

  final String? colonyId;
  final String? colonyName;

  static const _colonyName = 'Suryoday Colony';

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(colonyName ?? _colonyName),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildHeader(),
            const SizedBox(height: 20),
            _buildScoreRow(),
            const SizedBox(height: 24),
            _buildSectionTitle(Icons.school_rounded, 'Education'),
            const SizedBox(height: 12),
            ..._schools.map(_buildAmenityCard),
            const SizedBox(height: 20),
            _buildSectionTitle(Icons.local_hospital_rounded, 'Healthcare'),
            const SizedBox(height: 12),
            ..._hospitals.map(_buildAmenityCard),
            const SizedBox(height: 20),
            _buildSectionTitle(Icons.shopping_bag_rounded, 'Shopping & Dining'),
            const SizedBox(height: 12),
            ..._shopping.map(_buildAmenityCard),
            const SizedBox(height: 20),
            _buildSectionTitle(Icons.directions_bus_rounded, 'Transport'),
            const SizedBox(height: 12),
            ..._transport.map(_buildAmenityCard),
            const SizedBox(height: 20),
            _buildSectionTitle(Icons.account_balance_rounded, 'Banking & ATMs'),
            const SizedBox(height: 12),
            ..._banks.map(_buildAmenityCard),
            const SizedBox(height: 20),
            _buildSectionTitle(Icons.park_rounded, 'Recreation'),
            const SizedBox(height: 12),
            ..._recreation.map(_buildAmenityCard),
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
            colonyName != null
                ? 'Everything around ${colonyName!}'
                : 'Everything around $colonyName',
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
        Expanded(child: _buildScoreCard('Walk Score', 82, Colors.green)),
        const SizedBox(width: 12),
        Expanded(child: _buildScoreCard('Transit Score', 74, Colors.orange)),
        const SizedBox(width: 12),
        Expanded(child: _buildScoreCard('Lifestyle', 91, Colors.blue)),
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
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
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
}

const _schools = [
  _AmenityItem(
    name: 'DPS School',
    distance: '0.8 km',
    icon: Icons.school_rounded,
    color: Color(0xFF1565C0),
    rating: 4.5,
  ),
  _AmenityItem(
    name: 'St. Mary\'s Academy',
    distance: '1.2 km',
    icon: Icons.school_rounded,
    color: Color(0xFF1565C0),
    rating: 4.2,
  ),
  _AmenityItem(
    name: 'Kendriya Vidyalaya',
    distance: '1.8 km',
    icon: Icons.school_rounded,
    color: Color(0xFF1565C0),
    rating: 3.8,
  ),
  _AmenityItem(
    name: 'Little Scholars Montessori',
    distance: '0.5 km',
    icon: Icons.child_care_rounded,
    color: Color(0xFF7B1FA2),
    rating: 4.0,
  ),
];

const _hospitals = [
  _AmenityItem(
    name: 'Fortis Hospital',
    distance: '1.5 km',
    icon: Icons.local_hospital_rounded,
    color: Color(0xFFE53935),
    rating: 4.6,
  ),
  _AmenityItem(
    name: 'Apollo Clinic',
    distance: '0.9 km',
    icon: Icons.local_hospital_rounded,
    color: Color(0xFFE53935),
    rating: 4.3,
  ),
  _AmenityItem(
    name: 'MedPlus Pharmacy',
    distance: '0.3 km',
    icon: Icons.medication_rounded,
    color: Color(0xFF43A047),
    rating: 4.1,
  ),
];

const _shopping = [
  _AmenityItem(
    name: 'MGF Metropolitan Mall',
    distance: '2.1 km',
    icon: Icons.store_mall_directory_rounded,
    color: Color(0xFFFB8C00),
    rating: 4.4,
  ),
  _AmenityItem(
    name: 'Big Bazaar',
    distance: '0.7 km',
    icon: Icons.store_rounded,
    color: Color(0xFFFB8C00),
    rating: 4.0,
  ),
  _AmenityItem(
    name: 'Sector 56 Market',
    distance: '0.4 km',
    icon: Icons.storefront_rounded,
    color: Color(0xFFFB8C00),
    rating: 3.9,
  ),
  _AmenityItem(
    name: 'Dominos & Pizza Hut',
    distance: '0.6 km',
    icon: Icons.restaurant_rounded,
    color: Color(0xFFFF6D00),
    rating: 4.2,
  ),
];

const _transport = [
  _AmenityItem(
    name: 'Huda City Centre Metro',
    distance: '1.2 km',
    icon: Icons.subway_rounded,
    color: Color(0xFF6A1B9A),
    rating: 4.5,
  ),
  _AmenityItem(
    name: 'Sector 55-56 Bus Stop',
    distance: '0.3 km',
    icon: Icons.directions_bus_rounded,
    color: Color(0xFF6A1B9A),
    rating: 4.0,
  ),
  _AmenityItem(
    name: 'Gurgaon Railway Station',
    distance: '3.5 km',
    icon: Icons.train_rounded,
    color: Color(0xFF6A1B9A),
    rating: 3.5,
  ),
  _AmenityItem(
    name: 'NH-8 Access Point',
    distance: '1.0 km',
    icon: Icons.route_rounded,
    color: Color(0xFF6A1B9A),
    rating: 4.3,
  ),
];

const _banks = [
  _AmenityItem(
    name: 'State Bank of India',
    distance: '0.5 km',
    icon: Icons.account_balance_rounded,
    color: Color(0xFF1565C0),
    rating: 4.2,
  ),
  _AmenityItem(
    name: 'HDFC Bank ATM',
    distance: '0.2 km',
    icon: Icons.credit_card_rounded,
    color: Color(0xFFE65100),
    rating: 4.0,
  ),
  _AmenityItem(
    name: 'ICICI Bank',
    distance: '0.8 km',
    icon: Icons.account_balance_rounded,
    color: Color(0xFF1565C0),
    rating: 4.1,
  ),
  _AmenityItem(
    name: 'Canara Bank ATM',
    distance: '0.4 km',
    icon: Icons.credit_card_rounded,
    color: Color(0xFFE65100),
    rating: 3.8,
  ),
];

const _recreation = [
  _AmenityItem(
    name: 'Leisure Valley Park',
    distance: '0.6 km',
    icon: Icons.park_rounded,
    color: Color(0xFF2E7D32),
    rating: 4.6,
  ),
  _AmenityItem(
    name: 'Sector 56 Gym & Pool',
    distance: '0.5 km',
    icon: Icons.fitness_center_rounded,
    color: Color(0xFF2E7D32),
    rating: 4.3,
  ),
  _AmenityItem(
    name: 'PVR Cinemas',
    distance: '2.0 km',
    icon: Icons.movie_rounded,
    color: Color(0xFF2E7D32),
    rating: 4.4,
  ),
  _AmenityItem(
    name: 'Golf Course Road Club',
    distance: '2.5 km',
    icon: Icons.golf_course_rounded,
    color: Color(0xFF2E7D32),
    rating: 3.9,
  ),
];

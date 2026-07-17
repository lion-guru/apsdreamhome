import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class ServicesDirectoryPage extends StatelessWidget {
  const ServicesDirectoryPage({super.key});

  static const _categories = [
    _CategoryData(
      'Real Estate Agents',
      Icons.people_alt_rounded,
      Color(0xFF1A237E),
      1250,
    ),
    _CategoryData(
      'Home Loans',
      Icons.account_balance_rounded,
      Color(0xFF4CAF50),
      890,
    ),
    _CategoryData(
      'Legal Services',
      Icons.gavel_rounded,
      Color(0xFFFF6F00),
      456,
    ),
    _CategoryData(
      'Interior Design',
      Icons.design_services_rounded,
      Color(0xFFE91E63),
      678,
    ),
    _CategoryData(
      'Construction',
      Icons.construction_rounded,
      Color(0xFF00897B),
      1102,
    ),
    _CategoryData(
      'Property Valuation',
      Icons.assessment_rounded,
      Color(0xFF6A1B9A),
      334,
    ),
    _CategoryData(
      'Insurance',
      Icons.health_and_safety_rounded,
      Color(0xFF1565C0),
      567,
    ),
    _CategoryData(
      'Moving & Packing',
      Icons.local_shipping_rounded,
      Color(0xFF43A047),
      789,
    ),
    _CategoryData(
      'Architects',
      Icons.architecture_rounded,
      Color(0xFF9C27B0),
      445,
    ),
    _CategoryData(
      'Surveyors',
      Icons.straighten_rounded,
      Color(0xFFD32F2F),
      234,
    ),
    _CategoryData('Brokers', Icons.handshake_rounded, Color(0xFFD32F2F), 1876),
    _CategoryData(
      'Maintenance',
      Icons.handyman_rounded,
      Color(0xFF006064),
      892,
    ),
  ];

  static const _featuredListings = [
    _ListingData(
      'ABC Realty',
      'Real Estate Agents',
      '⭐ 4.8',
      '124 reviews',
      'Sector 15, Gurgaon',
      Icons.verified,
      Color(0xFF1A237E),
    ),
    _ListingData(
      'HomeLoan India',
      'Home Loans',
      '⭐ 4.6',
      '89 reviews',
      'Online Service',
      Icons.verified,
      Color(0xFF4CAF50),
    ),
    _ListingData(
      'LegalEase Property',
      'Legal Services',
      '⭐ 4.9',
      '203 reviews',
      'Connaught Place, Delhi',
      Icons.verified,
      Color(0xFFFF6F00),
    ),
    _ListingData(
      'Design Studio Pro',
      'Interior Design',
      '⭐ 4.7',
      '156 reviews',
      'Bandra West, Mumbai',
      Icons.verified,
      Color(0xFFE91E63),
    ),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: MeshGradientBackground(
        child: SafeArea(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildHeader(context),
                const SizedBox(height: 24),
                _buildSearchBar(),
                const SizedBox(height: 24),
                _buildSectionTitle('Categories'),
                const SizedBox(height: 16),
                _buildCategoriesGrid(),
                const SizedBox(height: 24),
                _buildSectionTitle('Featured Providers'),
                const SizedBox(height: 16),
                _buildFeaturedListings(),
                const SizedBox(height: 24),
                _buildSectionTitle('Jobs in Real Estate'),
                const SizedBox(height: 12),
                _buildJobsSection(context),
                const SizedBox(height: 24),
                _buildCTASection(context),
                const SizedBox(height: 40),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildHeader(BuildContext context) {
    return Column(
      children: [
        GestureDetector(
          onTap: () => context.pop(),
          child: Align(
            alignment: Alignment.centerLeft,
            child: Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(
                Icons.arrow_back,
                color: Colors.white,
                size: 22,
              ),
            ),
          ),
        ),
        const SizedBox(height: 20),
        Container(
          width: 80,
          height: 80,
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [Color(0xFF6A1B9A), Color(0xFF9C27B0)],
            ),
            borderRadius: BorderRadius.circular(20),
            boxShadow: [
              BoxShadow(
                color: const Color(0xFF6A1B9A).withValues(alpha: 0.3),
                blurRadius: 20,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          child: const Icon(
            Icons.storefront_rounded,
            size: 40,
            color: Colors.white,
          ),
        ),
        const SizedBox(height: 16),
        ShaderMask(
          shaderCallback: (bounds) => const LinearGradient(
            colors: [AppTheme.primaryColor, Color(0xFF6A1B9A)],
          ).createShader(bounds),
          child: Text(
            'Services Directory',
            style: Theme.of(context).textTheme.headlineLarge?.copyWith(
              color: Colors.white,
              fontWeight: FontWeight.w800,
            ),
          ),
        ),
        const SizedBox(height: 8),
        Text(
          'Find verified real estate professionals and services near you',
          style: Theme.of(
            context,
          ).textTheme.bodyMedium?.copyWith(color: Colors.white70),
          textAlign: TextAlign.center,
        ),
      ],
    );
  }

  Widget _buildSearchBar() {
    return GlassCard(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      opacity: 0.12,
      blur: 10,
      child: Row(
        children: [
          Icon(
            Icons.search_rounded,
            color: Colors.white.withValues(alpha: 0.7),
            size: 22,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              'Search for agents, lawyers, designers, contractors...',
              style: TextStyle(
                color: Colors.white.withValues(alpha: 0.6),
                fontSize: 14,
              ),
            ),
          ),
          Container(
            padding: const EdgeInsets.all(6),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.15),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(
              Icons.tune_rounded,
              color: Colors.white.withValues(alpha: 0.7),
              size: 18,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSectionTitle(String title) {
    return Text(
      title,
      style: AppTheme.titleLarge.copyWith(
        color: Colors.white,
        fontWeight: FontWeight.w700,
      ),
    );
  }

  Widget _buildCategoriesGrid() {
    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 3,
        childAspectRatio: 1.1,
        crossAxisSpacing: 12,
        mainAxisSpacing: 12,
      ),
      itemCount: _categories.length,
      itemBuilder: (context, index) {
        final cat = _categories[index];
        return GlassCard(
          padding: const EdgeInsets.all(12),
          opacity: 0.08,
          blur: 6,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: cat.color.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Icon(cat.icon, color: cat.color, size: 24),
              ),
              const SizedBox(height: 10),
              Text(
                cat.name,
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w600,
                  fontSize: 12,
                ),
                textAlign: TextAlign.center,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
              const SizedBox(height: 4),
              Text(
                '${cat.count} providers',
                style: TextStyle(
                  color: Colors.white.withValues(alpha: 0.5),
                  fontSize: 10,
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildFeaturedListings() {
    return SizedBox(
      height: 160,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: _featuredListings.length,
        separatorBuilder: (_, _) => const SizedBox(width: 12),
        itemBuilder: (context, index) {
          final listing = _featuredListings[index];
          return GestureDetector(
            onTap: () {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text('${listing.name} — Contact for more details'),
                  backgroundColor: AppTheme.primaryColor,
                ),
              );
            },
            child: GlassCard(
              width: 260,
              padding: const EdgeInsets.all(16),
              opacity: 0.1,
              blur: 8,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        width: 44,
                        height: 44,
                        decoration: BoxDecoration(
                          color: listing.color.withValues(alpha: 0.2),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Icon(
                          Icons.store_rounded,
                          color: listing.color,
                          size: 22,
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              listing.name,
                              style: const TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.w600,
                                fontSize: 14,
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                            Text(
                              listing.category,
                              style: TextStyle(
                                color: Colors.white.withValues(alpha: 0.5),
                                fontSize: 11,
                              ),
                            ),
                          ],
                        ),
                      ),
                      Icon(
                        listing.verifiedIcon,
                        color: AppTheme.accentColor,
                        size: 18,
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Text(
                    listing.rating,
                    style: const TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.w700,
                      fontSize: 16,
                    ),
                  ),
                  Text(
                    listing.reviews,
                    style: TextStyle(
                      color: Colors.white.withValues(alpha: 0.5),
                      fontSize: 11,
                    ),
                  ),
                  const Spacer(),
                  Row(
                    children: [
                      Icon(
                        Icons.location_on_rounded,
                        color: Colors.white.withValues(alpha: 0.5),
                        size: 14,
                      ),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          listing.location,
                          style: TextStyle(
                            color: Colors.white.withValues(alpha: 0.7),
                            fontSize: 12,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildJobsSection(BuildContext context) {
    return GlassCard(
      padding: const EdgeInsets.all(16),
      opacity: 0.1,
      blur: 8,
      child: Column(
        children: [
          _buildJobRow(
            'Sales Executive',
            'ABC Realty',
            'Gurgaon',
            '₹25K-40K/month',
            Icons.sell_rounded,
            const Color(0xFF1A237E),
          ),
          const Divider(color: Colors.white12, height: 24),
          _buildJobRow(
            'Property Manager',
            'HomeFirst',
            'Delhi NCR',
            '₹30K-50K/month',
            Icons.apartment_rounded,
            const Color(0xFF4CAF50),
          ),
          const Divider(color: Colors.white12, height: 24),
          _buildJobRow(
            'Legal Advisor',
            'LegalEase',
            'Mumbai',
            '₹50K-80K/month',
            Icons.gavel_rounded,
            const Color(0xFFFF6F00),
          ),
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            height: 40,
            child: OutlinedButton(
              onPressed: () {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('View all jobs on our careers page'),
                    backgroundColor: AppTheme.primaryColor,
                  ),
                );
              },
              style: OutlinedButton.styleFrom(
                foregroundColor: Colors.white,
                side: BorderSide(color: Colors.white.withValues(alpha: 0.3)),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
              child: const Text(
                'View All Jobs',
                style: TextStyle(fontWeight: FontWeight.w600),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildJobRow(
    String title,
    String company,
    String location,
    String salary,
    IconData icon,
    Color color,
  ) {
    return Row(
      children: [
        Container(
          width: 36,
          height: 36,
          decoration: BoxDecoration(
            color: color.withValues(alpha: 0.2),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(icon, color: color, size: 18),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w600,
                  fontSize: 13,
                ),
              ),
              Text(
                '$company • $location',
                style: TextStyle(
                  color: Colors.white.withValues(alpha: 0.5),
                  fontSize: 11,
                ),
              ),
            ],
          ),
        ),
        Text(
          salary,
          style: const TextStyle(
            color: AppTheme.accentColor,
            fontWeight: FontWeight.w600,
            fontSize: 12,
          ),
        ),
      ],
    );
  }

  Widget _buildCTASection(BuildContext context) {
    return GlassCard(
      padding: const EdgeInsets.all(20),
      opacity: 0.15,
      blur: 10,
      child: Row(
        children: [
          const Icon(
            Icons.add_business_rounded,
            color: AppTheme.accentColor,
            size: 28,
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'List Your Service',
                  style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w700,
                    fontSize: 15,
                  ),
                ),
                Text(
                  'Get discovered by thousands of property seekers',
                  style: TextStyle(
                    color: Colors.white.withValues(alpha: 0.6),
                    fontSize: 12,
                  ),
                ),
              ],
            ),
          ),
          SizedBox(
            height: 36,
            child: ElevatedButton(
              onPressed: () {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text(
                      'List your business by contacting our team at 7007444842',
                    ),
                    backgroundColor: AppTheme.successColor,
                  ),
                );
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.accentColor,
                foregroundColor: AppTheme.primaryColor,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
              child: const Text(
                'List Now',
                style: TextStyle(fontWeight: FontWeight.w600),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _CategoryData {
  final String name;
  final IconData icon;
  final Color color;
  final int count;
  const _CategoryData(this.name, this.icon, this.color, this.count);
}

class _ListingData {
  final String name;
  final String category;
  final String rating;
  final String reviews;
  final String location;
  final IconData verifiedIcon;
  final Color color;
  const _ListingData(
    this.name,
    this.category,
    this.rating,
    this.reviews,
    this.location,
    this.verifiedIcon,
    this.color,
  );
}

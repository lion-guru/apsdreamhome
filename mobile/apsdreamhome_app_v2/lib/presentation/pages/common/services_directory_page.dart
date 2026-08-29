import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:http/http.dart' as http;
import '../../../core/constants/app_constants.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class ServicesDirectoryPage extends StatefulWidget {
  const ServicesDirectoryPage({super.key});

  @override
  State<ServicesDirectoryPage> createState() => _ServicesDirectoryPageState();
}

class _ServicesDirectoryPageState extends State<ServicesDirectoryPage> {
  List<Map<String, dynamic>> _categories = [];
  List<Map<String, dynamic>> _featuredListings = [];
  List<Map<String, dynamic>> _jobs = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    try {
      AppConstants.initBaseUrl();
      final baseUrl = AppConstants.baseUrl;

      // Fetch all 3 endpoints in parallel
      final results = await Future.wait([
        http
            .get(Uri.parse('$baseUrl/api/v2/mobile/directory/categories'))
            .timeout(const Duration(seconds: 10)),
        http
            .get(Uri.parse('$baseUrl/api/v2/mobile/directory/featured'))
            .timeout(const Duration(seconds: 10)),
        http
            .get(Uri.parse('$baseUrl/api/v2/mobile/directory/jobs'))
            .timeout(const Duration(seconds: 10)),
      ]);

      List<Map<String, dynamic>> cats = [];
      List<Map<String, dynamic>> listings = [];
      List<Map<String, dynamic>> jobs = [];

      // Parse categories
      if (results[0].statusCode == 200) {
        final data = jsonDecode(results[0].body);
        if (data['success'] == true && data['data'] is List) {
          cats = List<Map<String, dynamic>>.from(data['data'] as List);
        }
      }

      // Parse featured listings
      if (results[1].statusCode == 200) {
        final data = jsonDecode(results[1].body);
        if (data['success'] == true && data['data'] is List) {
          listings = List<Map<String, dynamic>>.from(data['data'] as List);
        }
      }

      // Parse jobs
      if (results[2].statusCode == 200) {
        final data = jsonDecode(results[2].body);
        if (data['success'] == true && data['data'] is List) {
          jobs = List<Map<String, dynamic>>.from(data['data'] as List);
        }
      }

      setState(() {
        _categories = cats;
        _featuredListings = listings;
        _jobs = jobs;
        _loading = false;
      });
      return;
    } catch (_) {}

    // Fallback to mock data
    setState(() {
      _categories = _mockCategories;
      _featuredListings = _mockListings;
      _jobs = _mockJobs;
      _loading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: MeshGradientBackground(
        child: SafeArea(
          child: _loading
              ? const Center(
                  child: CircularProgressIndicator(color: Colors.white),
                )
              : SingleChildScrollView(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _buildHeader(context),
                      const SizedBox(height: 24),
                      _buildSearchBar(),
                      const SizedBox(height: 24),
                      _buildSectionTitle('Categories (${_categories.length})'),
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
    if (_categories.isEmpty) {
      return const Center(
        child: Text(
          'No categories available',
          style: TextStyle(color: Colors.white70),
        ),
      );
    }

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
        final color = _catColor(index);
        final iconData = _catIcon(cat['icon']?.toString() ?? '');

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
                  color: color.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Icon(iconData, color: color, size: 24),
              ),
              const SizedBox(height: 10),
              Text(
                '${cat['name'] ?? ''}',
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
                '${cat['listing_count'] ?? 0} providers',
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
    if (_featuredListings.isEmpty) {
      return const SizedBox(
        height: 120,
        child: Center(
          child: Text(
            'No featured providers yet',
            style: TextStyle(color: Colors.white70),
          ),
        ),
      );
    }

    return SizedBox(
      height: 160,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: _featuredListings.length,
        separatorBuilder: (_, _) => const SizedBox(width: 12),
        itemBuilder: (context, index) {
          final listing = _featuredListings[index];
          final color = _catColor(index);

          return GlassCard(
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
                        color: color.withValues(alpha: 0.2),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Icon(Icons.store_rounded, color: color, size: 22),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            '${listing['business_name'] ?? ''}',
                            style: const TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.w600,
                              fontSize: 14,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                          Text(
                            '${listing['category_name'] ?? ''}',
                            style: TextStyle(
                              color: Colors.white.withValues(alpha: 0.5),
                              fontSize: 11,
                            ),
                          ),
                        ],
                      ),
                    ),
                    if (listing['is_verified'] == true)
                      const Icon(
                        Icons.verified,
                        color: AppTheme.accentColor,
                        size: 18,
                      ),
                  ],
                ),
                const SizedBox(height: 12),
                Text(
                  '⭐ ${(listing['rating'] ?? 0).toString()} (${listing['review_count'] ?? 0} reviews)',
                  style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w700,
                    fontSize: 14,
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
                        '${listing['city'] ?? ''}',
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
          );
        },
      ),
    );
  }

  Widget _buildJobsSection(BuildContext context) {
    if (_jobs.isEmpty) {
      return const GlassCard(
        padding: EdgeInsets.all(16),
        opacity: 0.1,
        blur: 8,
        child: Center(
          child: Text(
            'No jobs available yet',
            style: TextStyle(color: Colors.white70),
          ),
        ),
      );
    }

    return GlassCard(
      padding: const EdgeInsets.all(16),
      opacity: 0.1,
      blur: 8,
      child: Column(
        children: [
          for (int i = 0; i < _jobs.length && i < 5; i++) ...[
            if (i > 0) const Divider(color: Colors.white12, height: 24),
            _buildJobRow(
              '${_jobs[i]['title'] ?? ''}',
              '${_jobs[i]['company'] ?? ''}',
              '${_jobs[i]['location'] ?? ''}',
              _formatSalary(
                _jobs[i]['salary_min'] ?? 0,
                _jobs[i]['salary_max'] ?? 0,
              ),
              Icons.work_rounded,
              _catColor(i),
            ),
          ],
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
                      'List your business by contacting 7007444842',
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

  Color _catColor(int index) {
    const colors = [
      Color(0xFF1A237E),
      Color(0xFF4CAF50),
      Color(0xFFFF6F00),
      Color(0xFFE91E63),
      Color(0xFF00897B),
      Color(0xFF6A1B9A),
      Color(0xFF1565C0),
      Color(0xFF43A047),
      Color(0xFF9C27B0),
      Color(0xFFD32F2F),
      Color(0xFF006064),
      Color(0xFFD32F2F),
    ];
    return colors[index % colors.length];
  }

  IconData _catIcon(String iconName) {
    if (iconName.contains('people')) return Icons.people_alt_rounded;
    if (iconName.contains('account')) return Icons.account_balance_rounded;
    if (iconName.contains('gavel') || iconName.contains('legal')) {
      return Icons.gavel_rounded;
    }
    if (iconName.contains('design')) return Icons.design_services_rounded;
    if (iconName.contains('construct')) return Icons.construction_rounded;
    if (iconName.contains('assess') || iconName.contains('valuation')) {
      return Icons.assessment_rounded;
    }
    if (iconName.contains('health') || iconName.contains('safety')) {
      return Icons.health_and_safety_rounded;
    }
    if (iconName.contains('truck') || iconName.contains('shipping')) {
      return Icons.local_shipping_rounded;
    }
    if (iconName.contains('arch')) return Icons.architecture_rounded;
    if (iconName.contains('strai') || iconName.contains('survey')) {
      return Icons.straighten_rounded;
    }
    if (iconName.contains('hand')) return Icons.handshake_rounded;
    if (iconName.contains('hammer') || iconName.contains('handyman')) {
      return Icons.handyman_rounded;
    }
    return Icons.store_rounded;
  }

  String _formatSalary(dynamic min, dynamic max) {
    final minVal = min is int ? min : int.tryParse('${min ?? 0}') ?? 0;
    final maxVal = max is int ? max : int.tryParse('${max ?? 0}') ?? 0;
    if (minVal == 0 && maxVal == 0) return 'Negotiable';
    if (minVal > 0 && maxVal > 0) {
      return '₹${(minVal / 1000).toStringAsFixed(0)}K-${(maxVal / 1000).toStringAsFixed(0)}K/mo';
    }
    if (maxVal > 0) return 'Up to ₹${(maxVal / 1000).toStringAsFixed(0)}K/mo';
    return '₹${(minVal / 1000).toStringAsFixed(0)}K+/mo';
  }

  static const _mockCategories = [
    {
      'name': 'Real Estate Agents',
      'icon': 'fas fa-users',
      'listing_count': 1250,
    },
    {'name': 'Home Loans', 'icon': 'fas fa-university', 'listing_count': 890},
    {'name': 'Legal Services', 'icon': 'fas fa-gavel', 'listing_count': 456},
    {
      'name': 'Interior Design',
      'icon': 'fas fa-paint-brush',
      'listing_count': 678,
    },
    {'name': 'Construction', 'icon': 'fas fa-hard-hat', 'listing_count': 1102},
    {
      'name': 'Property Valuation',
      'icon': 'fas fa-chart-line',
      'listing_count': 334,
    },
    {'name': 'Insurance', 'icon': 'fas fa-shield-alt', 'listing_count': 567},
    {'name': 'Moving & Packing', 'icon': 'fas fa-truck', 'listing_count': 789},
    {
      'name': 'Architects',
      'icon': 'fas fa-drafting-compass',
      'listing_count': 445,
    },
    {'name': 'Surveyors', 'icon': 'fas fa-ruler', 'listing_count': 234},
    {'name': 'Brokers', 'icon': 'fas fa-handshake', 'listing_count': 1876},
    {'name': 'Maintenance', 'icon': 'fas fa-tools', 'listing_count': 892},
  ];

  static const _mockListings = [
    {
      'business_name': 'ABC Realty',
      'category_name': 'Real Estate Agents',
      'rating': 4.8,
      'review_count': 124,
      'city': 'Gorakhpur',
      'is_verified': true,
    },
    {
      'business_name': 'HomeLoan India',
      'category_name': 'Home Loans',
      'rating': 4.6,
      'review_count': 89,
      'city': 'Online',
      'is_verified': true,
    },
    {
      'business_name': 'LegalEase Property',
      'category_name': 'Legal Services',
      'rating': 4.9,
      'review_count': 203,
      'city': 'Lucknow',
      'is_verified': true,
    },
    {
      'business_name': 'Design Studio Pro',
      'category_name': 'Interior Design',
      'rating': 4.7,
      'review_count': 156,
      'city': 'Gorakhpur',
      'is_verified': true,
    },
  ];

  static const _mockJobs = [
    {
      'title': 'Sales Executive',
      'company': 'ABC Realty',
      'location': 'Gorakhpur',
      'salary_min': 25000,
      'salary_max': 40000,
    },
    {
      'title': 'Property Manager',
      'company': 'HomeFirst',
      'location': 'Lucknow',
      'salary_min': 30000,
      'salary_max': 50000,
    },
    {
      'title': 'Legal Advisor',
      'company': 'LegalEase',
      'location': 'Varanasi',
      'salary_min': 50000,
      'salary_max': 80000,
    },
  ];
}

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_theme.dart';
import '../../../data/repositories/kyc_repository_provider.dart';
import '../../widgets/glass_card.dart';

class ProjectsPage extends ConsumerStatefulWidget {
  const ProjectsPage({super.key});

  @override
  ConsumerState<ProjectsPage> createState() => _ProjectsPageState();
}

class _ProjectsPageState extends ConsumerState<ProjectsPage> {
  bool _isLoading = true;
  List<Map<String, dynamic>> _colonies = [];

  static const _mockColonies = [
    {
      'name': 'Suryoday Colony',
      'location': 'Sector 89, Gurgaon',
      'type': 'Residential Plots',
      'price_range': '₹25L - ₹85L',
      'status': 'Ready to Move',
      'completion': 95,
      'features': [
        'RERA Registered',
        'Water & Electricity',
        'Wide Roads',
        'Park & Clubhouse',
      ],
    },
    {
      'name': 'Braj Radha Enclave',
      'location': 'Sector 92, Gurgaon',
      'type': 'Residential Plots',
      'price_range': '₹30L - ₹1.2Cr',
      'status': 'Under Development (40%)',
      'completion': 65,
      'features': [
        'RERA Registered',
        'Gated Community',
        '24x7 Security',
        'Temple Inside',
      ],
    },
    {
      'name': 'Raghunath Nagri',
      'location': 'Sector 37C, Gurgaon',
      'type': 'Residential + Commercial',
      'price_range': '₹35L - ₹2Cr',
      'status': 'Ready to Move',
      'completion': 90,
      'features': [
        'Mixed Use',
        'Metro Connectivity',
        'Shopping Complex',
        'School Nearby',
      ],
    },
    {
      'name': 'Budh Bihar Township',
      'location': 'Sector 103, Gurgaon',
      'type': 'Affordable Housing',
      'price_range': '₹15L - ₹45L',
      'status': 'Pre-Launch',
      'completion': 30,
      'features': [
        'PMAY Eligible',
        'Near Highway',
        'Future Metro',
        'Green Area',
      ],
    },
  ];

  @override
  void initState() {
    super.initState();
    _loadColonies();
  }

  Future<void> _loadColonies() async {
    setState(() => _isLoading = true);
    try {
      final api = ref.read(apiServiceProvider);
      final response = await api.get('colonies');
      final data = response['data'] ?? [];
      if (mounted && data is List && data.isNotEmpty) {
        setState(() {
          _colonies = data
              .map(
                (e) => _mapColonyToProject(Map<String, dynamic>.from(e as Map)),
              )
              .toList();
          _isLoading = false;
        });
        return;
      }
    } catch (_) {}
    if (mounted) {
      setState(() {
        _colonies = _mockColonies
            .map((e) => Map<String, dynamic>.from(e))
            .toList();
        _isLoading = false;
      });
    }
  }

  Map<String, dynamic> _mapColonyToProject(Map<String, dynamic> colony) {
    final name = colony['name']?.toString() ?? '';
    final location = colony['description']?.toString() ?? '';
    final sp = colony['starting_price'];
    final num startingPrice = sp is num ? sp : 0;
    final tp = colony['total_plots'];
    final num totalPlots = tp is num ? tp : 0;
    final ap = colony['available_plots'];
    final num availablePlots = ap is num ? ap : 0;

    String priceRange;
    if (startingPrice > 0) {
      if (startingPrice >= 10000000) {
        priceRange = '₹${(startingPrice / 10000000).toStringAsFixed(1)}Cr+';
      } else if (startingPrice >= 100000) {
        priceRange = '₹${(startingPrice / 100000).toStringAsFixed(0)}L+';
      } else {
        priceRange = '₹${startingPrice.toStringAsFixed(0)}';
      }
    } else {
      priceRange = 'Contact for Price';
    }

    final num sold = totalPlots - availablePlots;
    final int completion = totalPlots > 0
        ? ((sold / totalPlots) * 100).round()
        : 0;

    final status = colony['is_active'] == true ? 'Active' : 'Coming Soon';
    final district = colony['district_name']?.toString() ?? '';
    final shortLocation = district.isNotEmpty
        ? district
        : location.split(',').first;

    final features = <String>[];
    if (totalPlots > 0) features.add('${totalPlots.toInt()} Plots');
    if (availablePlots > 0) features.add('${availablePlots.toInt()} Available');
    if (colony['is_featured'] == true) features.add('Featured');

    return {
      'name': name,
      'location': shortLocation,
      'type': 'Residential Plots',
      'price_range': priceRange,
      'status': status,
      'completion': completion,
      'features': features,
      'image_url': colony['image_url']?.toString() ?? '',
    };
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: MeshGradientBackground(
        child: SafeArea(
          child: _isLoading
              ? const Center(
                  child: CircularProgressIndicator(
                    color: AppTheme.primaryColor,
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _loadColonies,
                  color: AppTheme.primaryColor,
                  child: SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.all(20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _buildHeader(context),
                        const SizedBox(height: 24),
                        _buildFilterChips(),
                        const SizedBox(height: 20),
                        ..._colonies.map(
                          (p) => Padding(
                            padding: const EdgeInsets.only(bottom: 16),
                            child: _buildProjectCard(context, p),
                          ),
                        ),
                        const SizedBox(height: 24),
                        _buildCTASection(context),
                        const SizedBox(height: 40),
                      ],
                    ),
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
              colors: [Color(0xFF1565C0), Color(0xFF42A5F5)],
            ),
            borderRadius: BorderRadius.circular(20),
            boxShadow: [
              BoxShadow(
                color: const Color(0xFF1565C0).withValues(alpha: 0.3),
                blurRadius: 20,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          child: const Icon(
            Icons.business_rounded,
            size: 40,
            color: Colors.white,
          ),
        ),
        const SizedBox(height: 16),
        ShaderMask(
          shaderCallback: (bounds) => const LinearGradient(
            colors: [AppTheme.primaryColor, Color(0xFF1565C0)],
          ).createShader(bounds),
          child: Text(
            'Our Projects',
            style: Theme.of(context).textTheme.headlineLarge?.copyWith(
              color: Colors.white,
              fontWeight: FontWeight.w800,
            ),
          ),
        ),
        const SizedBox(height: 8),
        Text(
          'Explore our premium residential and commercial developments',
          style: Theme.of(
            context,
          ).textTheme.bodyMedium?.copyWith(color: Colors.white70),
          textAlign: TextAlign.center,
        ),
      ],
    );
  }

  Widget _buildFilterChips() {
    final filters = ['All', 'Active', 'Plots', 'Residential', 'Featured'];
    return SizedBox(
      height: 40,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: filters.length,
        separatorBuilder: (_, _) => const SizedBox(width: 8),
        itemBuilder: (context, index) {
          final isSelected = index == 0;
          return FilterChip(
            label: Text(filters[index]),
            selected: isSelected,
            onSelected: (_) {},
            backgroundColor: Colors.white.withValues(alpha: 0.1),
            selectedColor: AppTheme.primaryColor.withValues(alpha: 0.3),
            labelStyle: TextStyle(
              color: isSelected ? Colors.white : Colors.white70,
              fontWeight: isSelected ? FontWeight.w600 : FontWeight.w400,
            ),
            side: BorderSide(color: Colors.white.withValues(alpha: 0.2)),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(20),
            ),
          );
        },
      ),
    );
  }

  Widget _buildProjectCard(BuildContext context, Map<String, dynamic> project) {
    final name = project['name']?.toString() ?? '';
    final location = project['location']?.toString() ?? '';
    final type = project['type']?.toString() ?? '';
    final priceRange = project['price_range']?.toString() ?? '';
    final status = project['status']?.toString() ?? '';
    final completion = project['completion'] as int? ?? 0;
    final features = project['features'] as List<dynamic>? ?? [];

    final iconColors = [
      const Color(0xFF4CAF50),
      const Color(0xFF2196F3),
      const Color(0xFFFF9800),
      const Color(0xFF9C27B0),
      const Color(0xFFE91E63),
    ];
    final color = iconColors[_colonies.indexOf(project) % iconColors.length];

    final icons = [
      Icons.landscape_rounded,
      Icons.location_city_rounded,
      Icons.apartment_rounded,
      Icons.home_rounded,
      Icons.business_rounded,
    ];
    final icon = icons[_colonies.indexOf(project) % icons.length];

    return GlassCard(
      padding: const EdgeInsets.all(16),
      opacity: 0.1,
      blur: 8,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 56,
                height: 56,
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Icon(icon, color: color, size: 28),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      name,
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w700,
                        fontSize: 17,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Row(
                      children: [
                        Icon(
                          Icons.location_on_rounded,
                          color: Colors.white.withValues(alpha: 0.6),
                          size: 14,
                        ),
                        const SizedBox(width: 4),
                        Text(
                          location,
                          style: TextStyle(
                            color: Colors.white.withValues(alpha: 0.7),
                            fontSize: 13,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 5,
                ),
                decoration: BoxDecoration(
                  color: _getStatusColor(status).withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  status,
                  style: TextStyle(
                    color: _getStatusColor(status),
                    fontWeight: FontWeight.w600,
                    fontSize: 11,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: _buildInfoChip(Icons.category_rounded, type, color),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: _buildInfoChip(
                  Icons.currency_rupee_rounded,
                  priceRange,
                  AppTheme.accentColor,
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: _buildInfoChip(
                  Icons.percent_rounded,
                  '$completion% Sold',
                  const Color(0xFF4CAF50),
                ),
              ),
            ],
          ),
          if (features.isNotEmpty) ...[
            const SizedBox(height: 16),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: features
                  .map(
                    (f) => Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 10,
                        vertical: 5,
                      ),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.08),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(
                          color: Colors.white.withValues(alpha: 0.15),
                        ),
                      ),
                      child: Text(
                        f.toString(),
                        style: TextStyle(
                          color: Colors.white.withValues(alpha: 0.8),
                          fontSize: 11,
                        ),
                      ),
                    ),
                  )
                  .toList(),
            ),
          ],
          const SizedBox(height: 16),
          Row(
            children: [
              Expanded(
                child: OutlinedButton(
                  onPressed: () => context.push(
                    '/colony-detail/${name.toLowerCase().replaceAll(' ', '-')}',
                  ),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: Colors.white,
                    side: BorderSide(
                      color: Colors.white.withValues(alpha: 0.3),
                    ),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                  ),
                  child: const Text(
                    'View Details',
                    style: TextStyle(fontWeight: FontWeight.w600),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: ElevatedButton(
                  onPressed: () => context.push(
                    '/colony-plots/${name.toLowerCase().replaceAll(' ', '-')}',
                  ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTheme.primaryColor,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                  ),
                  child: const Text(
                    'View Plots',
                    style: TextStyle(fontWeight: FontWeight.w600),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildInfoChip(IconData icon, String label, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icon, color: color, size: 14),
          const SizedBox(width: 6),
          Flexible(
            child: Text(
              label,
              style: TextStyle(
                color: color,
                fontWeight: FontWeight.w600,
                fontSize: 11,
              ),
              overflow: TextOverflow.ellipsis,
            ),
          ),
        ],
      ),
    );
  }

  Color _getStatusColor(String status) {
    if (status == 'Active' || status.contains('Ready')) {
      return const Color(0xFF4CAF50);
    }
    if (status.contains('Under') || status.contains('Development')) {
      return const Color(0xFFFF9800);
    }
    if (status == 'Coming Soon' || status.contains('Launch')) {
      return const Color(0xFF2196F3);
    }
    return AppTheme.primaryColor;
  }

  Widget _buildCTASection(BuildContext context) {
    return GlassCard(
      padding: const EdgeInsets.all(24),
      opacity: 0.15,
      blur: 10,
      child: Column(
        children: [
          const Icon(
            Icons.assignment_rounded,
            color: AppTheme.accentColor,
            size: 40,
          ),
          const SizedBox(height: 12),
          const Text(
            'Want to Partner with Us?',
            style: TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.w700,
              fontSize: 18,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'We are always looking for quality land parcels and development partners.',
            style: TextStyle(
              color: Colors.white.withValues(alpha: 0.6),
              fontSize: 13,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 16),
          SizedBox(
            width: double.infinity,
            height: 48,
            child: DecoratedBox(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(14),
                gradient: const LinearGradient(
                  colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
                ),
                boxShadow: [
                  BoxShadow(
                    color: AppTheme.primaryColor.withValues(alpha: 0.4),
                    blurRadius: 12,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: ElevatedButton(
                onPressed: () {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text(
                        'Contact our team at 7007444842 or visit the office to submit a land proposal',
                      ),
                      backgroundColor: AppTheme.successColor,
                    ),
                  );
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.transparent,
                  shadowColor: Colors.transparent,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14),
                  ),
                ),
                child: const Text(
                  'Submit Land Proposal',
                  style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w600,
                    fontSize: 15,
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/services/api_service.dart';
import '../../../presentation/pages/auth/login_page.dart';
import '../../../presentation/pages/auth/register_page.dart';
import '../../../presentation/pages/customer/colonies_page.dart';
import '../../../presentation/pages/customer/plots_page.dart';

class PublicHomePage extends ConsumerStatefulWidget {
  const PublicHomePage({super.key});

  @override
  ConsumerState<PublicHomePage> createState() => _PublicHomePageState();
}

class _PublicHomePageState extends ConsumerState<PublicHomePage> {
  final PageController _pageController = PageController();
  int _currentPage = 0;
  List<Map<String, dynamic>> _featuredColonies = [];
  List<Map<String, dynamic>> _featuredPlots = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadFeaturedData();
    _startAutoScroll();
  }

  Future<void> _loadFeaturedData() async {
    try {
      final api = ApiService();
      final response = await api.get('/blog', queryParameters: {'limit': 10, 'page': 1});
      if (mounted) {
        setState(() {
          if (response['success'] == true && response['data'] is List) {
            _featuredColonies = (response['data'] as List)
                .map((e) => Map<String, dynamic>.from(e as Map<dynamic, dynamic>))
                .toList();
          } else {
            _featuredColonies = [];
          }
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  void _startAutoScroll() {
    Future.delayed(const Duration(seconds: 5), () {
      if (mounted && _pageController.hasClients) {
        _pageController.nextPage(
          duration: const Duration(milliseconds: 500),
          curve: Curves.easeInOut,
        );
        _startAutoScroll();
      }
    });
  }

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.backgroundColor,
      body: CustomScrollView(
        slivers: [
          _buildHeroSection(),
          _buildTrustIndicators(),
          _buildFeaturedColonies(),
          _buildFeaturedPlots(),
          _buildToolsSection(),
          _buildTestimonials(),
          _buildCTASection(),
          _buildFooter(),
        ],
      ),
    );
  }

  Widget _buildHeroSection() {
    return SliverToBoxAdapter(
      child: Stack(
        children: [
          Container(
            height: MediaQuery.of(context).size.height * 0.75,
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [
                  AppTheme.primaryColor,
                  AppTheme.primaryColor.withValues(alpha: 0.8),
                  AppTheme.secondaryColor,
                ],
              ),
            ),
            child: Stack(
              children: [
                Positioned.fill(
                  child: Image.asset(
                    'assets/images/hero_bg.jpg',
                    fit: BoxFit.cover,
                    color: Colors.black.withValues(alpha: 0.3),
                    colorBlendMode: BlendMode.darken,
                    errorBuilder: (_, __, ___) => Container(),
                  ),
                ),
                SafeArea(
                  child: Padding(
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Image.asset(
                              'assets/images/app_logo_luxury.png',
                              height: 48,
                              errorBuilder: (_, __, ___) => const Icon(
                                Icons.apartment,
                                color: Colors.white,
                                size: 48,
                              ),
                            ),
                            Row(
                              children: [
                                TextButton(
                                  onPressed: () => context.go('/login'),
                                  child: const Text(
                                    'Login',
                                    style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600),
                                  ),
                                ),
                                const SizedBox(width: 16),
                                ElevatedButton(
                                  onPressed: () => context.go('/register'),
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: AppTheme.goldColor,
                                    foregroundColor: AppTheme.primaryColor,
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                                  ),
                                  child: const Text('Get Started'),
                                ),
                              ],
                            ),
                          ],
                        ),
                        const Spacer(),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                              decoration: BoxDecoration(
                                color: AppTheme.goldColor.withValues(alpha: 0.2),
                                borderRadius: BorderRadius.circular(24),
                                border: Border.all(color: AppTheme.goldColor, width: 1.5),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(Icons.verified, color: AppTheme.goldColor, size: 18),
                                  const SizedBox(width: 8),
                                  Text(
                                    'RERA APPROVED \u2022 GORAKHPUR \u2022 LUCKNOW',
                                    style: TextStyle(
                                      color: AppTheme.goldColor,
                                      fontWeight: FontWeight.w700,
                                      fontSize: 13,
                                      letterSpacing: 1.2,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(height: 24),
                            Text(
                              'Find Your Dream Home\nwith APS Dream Home',
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 42,
                                fontWeight: FontWeight.w800,
                                height: 1.1,
                                letterSpacing: -1,
                              ),
                            ),
                            const SizedBox(height: 16),
                            Text(
                              'Verified plots, transparent pricing, and seamless booking.\nJoin 50,000+ happy homeowners in premium townships.',
                              style: TextStyle(
                                color: Colors.white.withValues(alpha: 0.9),
                                fontSize: 18,
                                height: 1.5,
                              ),
                            ),
                            const SizedBox(height: 32),
                            Row(
                              children: [
                                Expanded(
                                  child: ElevatedButton.icon(
                                    onPressed: () => context.go('/colonies'),
                                    icon: const Icon(Icons.explore, size: 24),
                                    label: const Text(
                                      'Explore Townships',
                                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
                                    ),
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: Colors.white,
                                      foregroundColor: AppTheme.primaryColor,
                                      padding: const EdgeInsets.symmetric(vertical: 18),
                                      shape: RoundedRectangleBorder(
                                        borderRadius: BorderRadius.circular(16),
                                      ),
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 16),
                                Expanded(
                                  child: OutlinedButton.icon(
                                    onPressed: () => context.go('/contact'),
                                    icon: const Icon(Icons.phone, size: 24),
                                    label: const Text(
                                      'Call Us',
                                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
                                    ),
                                    style: OutlinedButton.styleFrom(
                                      foregroundColor: Colors.white,
                                      side: const BorderSide(color: Colors.white, width: 2),
                                      padding: const EdgeInsets.symmetric(vertical: 18),
                                      shape: RoundedRectangleBorder(
                                        borderRadius: BorderRadius.circular(16),
                                      ),
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
          _buildScrollIndicators(),
        ],
      ),
    );
  }

  Widget _buildScrollIndicators() {
    return Positioned(
      bottom: 40,
      left: 0,
      right: 0,
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: List.generate(3, (index) {
          return AnimatedContainer(
            duration: const Duration(milliseconds: 300),
            margin: const EdgeInsets.symmetric(horizontal: 4),
            width: _currentPage == index ? 24 : 8,
            height: 8,
            decoration: BoxDecoration(
              color: _currentPage == index ? AppTheme.goldColor : Colors.white.withValues(alpha: 0.5),
              borderRadius: BorderRadius.circular(4),
            ),
          );
        }),
      ),
    );
  }

  Widget _buildTrustIndicators() {
    return SliverToBoxAdapter(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 32),
        color: Colors.white,
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
          children: [
            _buildTrustItem(Icons.verified, 'RERA Verified', 'All projects approved'),
            _buildTrustItem(Icons.shield, 'Legal Clear', 'Clean titles guaranteed'),
            _buildTrustItem(Icons.payment, 'Easy EMI', 'Flexible payment plans'),
            _buildTrustItem(Icons.support_agent, '24/7 Support', 'Dedicated assistance'),
          ],
        ),
      ),
    );
  }

  Widget _buildTrustItem(IconData icon, String title, String subtitle) {
    return Column(
      children: [
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: AppTheme.primaryColor.withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(16),
          ),
          child: Icon(icon, color: AppTheme.primaryColor, size: 28),
        ),
        const SizedBox(height: 12),
        Text(title, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 16)),
        const SizedBox(height: 4),
        Text(subtitle, style: TextStyle(color: Colors.grey[600], fontSize: 13)),
      ],
    );
  }

  Widget _buildFeaturedColonies() {
    return SliverToBoxAdapter(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.all(24),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Featured Townships',
                  style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.w800,
                    color: AppTheme.primaryColor,
                  ),
                ),
                TextButton(
                  onPressed: () => context.go('/colonies'),
                  child: Text('View All', style: TextStyle(color: AppTheme.goldColor, fontWeight: FontWeight.w600)),
                ),
              ],
            ),
          ),
          _isLoading
              ? const Center(child: CircularProgressIndicator())
              : _featuredColonies.isEmpty
                  ? _buildEmptyState('No townships available')
                  : SizedBox(
                      height: 280,
                      child: ListView.builder(
                        scrollDirection: Axis.horizontal,
                        padding: const EdgeInsets.symmetric(horizontal: 24),
                        itemCount: _featuredColonies.length,
                        itemBuilder: (context, index) {
                          final colony = _featuredColonies[index];
                          return _buildColonyCard(colony);
                        },
                      ),
                    ),
        ],
      ),
    );
  }

  Widget _buildColonyCard(Map<String, dynamic> colony) {
    final name = (colony['name'] ?? 'Unknown Colony').toString();
    final location = (colony['location'] ?? colony['city'] ?? 'Gorakhpur').toString();
    final price = colony['min_price'] ?? colony['starting_price'] ?? 0;
    final image = (colony['image'] ?? colony['banner_image'])?.toString();
    final plotsAvailable = colony['plots_available'] ?? colony['available_plots'] ?? 0;
    final reraNumber = colony['rera_number'];

    return Container(
      width: 280,
      margin: const EdgeInsets.only(right: 16),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(20),
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.08),
            blurRadius: 20,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Stack(
            children: [
              ClipRRect(
                borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
                child: image != null
                    ? Image.network(
                        image,
                        height: 160,
                        width: double.infinity,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => _buildPlaceholderImage(),
                      )
                    : _buildPlaceholderImage(),
              ),
              if (reraNumber != null)
                Positioned(
                  top: 12,
                  right: 12,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                    decoration: BoxDecoration(
                      color: AppTheme.goldColor,
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.verified, color: Colors.white, size: 14),
                        const SizedBox(width: 4),
                        Text(
                          'RERA',
                          style: TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w700,
                            fontSize: 11,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
            ],
          ),
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  name,
                  style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 17),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    Icon(Icons.location_on, size: 14, color: Colors.grey[600]),
                    const SizedBox(width: 4),
                    Text(location, style: TextStyle(color: Colors.grey[600], fontSize: 13)),
                  ],
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Text(
                      '₹${_formatPrice(price)}',
                      style: TextStyle(
                        fontWeight: FontWeight.w800,
                        fontSize: 18,
                        color: AppTheme.primaryColor,
                      ),
                    ),
                    const SizedBox(width: 8),
                    Text(
                      'Starting',
                      style: TextStyle(color: Colors.grey[500], fontSize: 12),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton(
                        onPressed: () => context.go('/colony-detail/${colony['id'].toString()}'),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: AppTheme.primaryColor,
                          side: BorderSide(color: AppTheme.primaryColor),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        child: const Text('View Details'),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: ElevatedButton(
                        onPressed: () => context.go('/colony-plots/${colony['id'].toString()}'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppTheme.primaryColor,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        child: Text('${(colony['plots_available'] ?? colony['available_plots'] ?? 0)} Plots'),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPlaceholderImage() {
    return Container(
      height: 160,
      width: double.infinity,
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [AppTheme.primaryColor.withValues(alpha: 0.3), AppTheme.secondaryColor.withValues(alpha: 0.3)],
        ),
      ),
      child: const Center(
        child: Icon(Icons.apartment, size: 48, color: Colors.white70),
      ),
    );
  }

  Widget _buildEmptyState(String message) {
    return Container(
      height: 200,
      alignment: Alignment.center,
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.apartment_outlined, size: 48, color: Colors.grey[300]),
          const SizedBox(height: 12),
          Text(message, style: TextStyle(color: Colors.grey[500])),
        ],
      ),
    );
  }

  Widget _buildFeaturedPlots() {
    return SliverToBoxAdapter(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.all(24),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Available Plots',
                  style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.w800,
                    color: AppTheme.primaryColor,
                  ),
                ),
                TextButton(
                  onPressed: () => context.go('/plots'),
                  child: Text('View All', style: TextStyle(color: AppTheme.goldColor, fontWeight: FontWeight.w600)),
                ),
              ],
            ),
          ),
          _featuredPlots.isEmpty
              ? _buildEmptyState('No plots available')
              : GridView.builder(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  padding: const EdgeInsets.symmetric(horizontal: 24),
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 2,
                    childAspectRatio: 0.75,
                    crossAxisSpacing: 16,
                    mainAxisSpacing: 16,
                  ),
                  itemCount: _featuredPlots.length > 4 ? 4 : _featuredPlots.length,
                  itemBuilder: (context, index) {
                    final plot = _featuredPlots[index];
                    return _buildPlotCard(plot);
                  },
                ),
          Padding(
            padding: const EdgeInsets.all(24),
            child: Center(
              child: TextButton(
                onPressed: () => context.go('/plots'),
                child: Text('View All Plots', style: TextStyle(color: AppTheme.goldColor, fontWeight: FontWeight.w600, fontSize: 16)),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPlotCard(Map<String, dynamic> plot) {
    final name = (plot['plot_number'] ?? plot['name'] ?? 'Plot ${plot['id']}').toString();
    final colony = (plot['colony_name'] ?? plot['colony'] ?? '').toString();
    final price = plot['total_price'] ?? plot['price'] ?? 0;
    final area = (plot['area'] ?? plot['plot_area'] ?? 0) as num;
    final facing = (plot['facing'] ?? '').toString();
    final image = plot['image']?.toString();

    return Container(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(16),
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.06),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Stack(
            children: [
              ClipRRect(
                borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                child: image != null
                    ? Image.network(
                        image,
                        height: 120,
                        width: double.infinity,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => _buildPlaceholderImage(),
                      )
                    : _buildPlaceholderImage(),
              ),
              if (facing.isNotEmpty)
                Positioned(
                  top: 8,
                  left: 8,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: AppTheme.goldColor,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      facing,
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w700,
                        fontSize: 10,
                      ),
                    ),
                  ),
                ),
            ],
          ),
          Padding(
            padding: const EdgeInsets.all(12),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  name,
                  style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                if (colony.isNotEmpty) ...[
                  const SizedBox(height: 2),
                  Row(
                    children: [
                      Icon(Icons.location_on, size: 11, color: Colors.grey[500]),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          colony,
                          style: TextStyle(color: Colors.grey[600], fontSize: 11),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                ],
                const SizedBox(height: 8),
                Row(
                  children: [
                    Text(
                      '₹${_formatPrice(price)}',
                      style: TextStyle(
                        fontWeight: FontWeight.w800,
                        fontSize: 15,
                        color: AppTheme.primaryColor,
                      ),
                    ),
                    const Spacer(),
                    if (area > 0)
                      Text(
                        '${area.toInt()} sq ft',
                        style: TextStyle(color: Colors.grey[600], fontSize: 11),
                      ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildToolsSection() {
    final tools = [
      {'icon': Icons.calculate, 'title': 'EMI Calculator', 'route': '/emi-calculator', 'color': Colors.blue},
      {'icon': Icons.gavel, 'title': 'Stamp Duty', 'route': '/stamp-duty-calculator', 'color': Colors.orange},
      {'icon': Icons.account_balance, 'title': 'Home Loan', 'route': '/home-loan-eligibility', 'color': Colors.green},
      {'icon': Icons.gps_fixed, 'title': 'Plot Converter', 'route': '/plot-converter', 'color': Colors.purple},
      {'icon': Icons.trending_up, 'title': 'Property Value', 'route': '/property-appreciation-calculator', 'color': Colors.red},
      {'icon': Icons.receipt_long, 'title': 'Rental Yield', 'route': '/rental-yield-calculator', 'color': Colors.teal},
    ];

    return SliverToBoxAdapter(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.all(24),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Smart Tools',
                  style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.w800,
                    color: AppTheme.primaryColor,
                  ),
                ),
                TextButton(
                  onPressed: () => context.go('/tools-hub'),
                  child: Text('View All', style: TextStyle(color: AppTheme.goldColor, fontWeight: FontWeight.w600)),
                ),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 24),
            child: GridView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 3,
                childAspectRatio: 1,
                crossAxisSpacing: 12,
                mainAxisSpacing: 12,
              ),
              itemCount: tools.length,
              itemBuilder: (context, index) {
                final tool = tools[index];
                return _buildToolCard(tool);
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildToolCard(Map<String, dynamic> tool) {
    return InkWell(
      onTap: () => context.go(tool['route'] as String),
      borderRadius: BorderRadius.circular(16),
      child: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(16),
          color: Colors.white,
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.05),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: (tool['color'] as Color).withValues(alpha: 0.15),
                borderRadius: BorderRadius.circular(16),
              ),
              child: Icon(
                tool['icon'] as IconData,
                color: tool['color'] as Color,
                size: 32,
              ),
            ),
            const SizedBox(height: 12),
            Text(
              tool['title'] as String,
              style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTestimonials() {
    final testimonials = [
      {'name': 'Rajesh Kumar', 'location': 'Gorakhpur', 'text': 'Booking my plot was seamless. The team guided me through every step - from site visit to registry. Highly recommended!', 'rating': 5},
      {'name': 'Priya Sharma', 'location': 'Lucknow', 'text': 'The EMI calculator helped me plan my finances perfectly. Transparent pricing and no hidden charges.', 'rating': 5},
      {'name': 'Amit Singh', 'location': 'Gorakhpur', 'text': 'Best decision of my life. RERA verified, legal clear title, and the team supported me till possession.', 'rating': 5},
    ];

    return SliverToBoxAdapter(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.all(24),
            child: Text(
              'Happy Homeowners',
              style: TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.w800,
                color: AppTheme.primaryColor,
              ),
            ),
          ),
          SizedBox(
            height: 200,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 24),
              itemCount: testimonials.length,
              itemBuilder: (context, index) {
                final t = testimonials[index];
                return _buildTestimonialCard(t);
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTestimonialCard(Map<String, dynamic> t) {
    return Container(
      width: 300,
      margin: const EdgeInsets.only(right: 16),
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(20),
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.06),
            blurRadius: 16,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: List.generate(5, (i) => Icon(
              Icons.star,
              color: i < (t['rating'] as int) ? AppTheme.goldColor : Colors.grey[300],
              size: 20,
            )),
          ),
          const SizedBox(height: 16),
          Text(
            t['text'] as String,
            style: TextStyle(
              fontSize: 15,
              height: 1.6,
              color: Colors.grey[700],
            ),
          ),
          const SizedBox(height: 16),
          Text(
            t['name'] as String,
            style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 16),
          ),
          Text(
            t['location'] as String,
            style: TextStyle(color: Colors.grey[500], fontSize: 13),
          ),
        ],
      ),
    );
  }

  Widget _buildCTASection() {
    return SliverToBoxAdapter(
      child: Container(
        margin: const EdgeInsets.all(24),
        padding: const EdgeInsets.all(32),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(24),
          gradient: LinearGradient(
            colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
          ),
        ),
        child: Column(
          children: [
            Text(
              'Ready to Find Your Dream Home?',
              style: TextStyle(
                color: Colors.white,
                fontSize: 28,
                fontWeight: FontWeight.w800,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 12),
            Text(
              'Explore 500+ verified plots across premium townships.\nBook a free site visit today!',
              style: TextStyle(
                color: Colors.white.withValues(alpha: 0.9),
                fontSize: 16,
                height: 1.5,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 24),
            Row(
              children: [
                Expanded(
                  child: ElevatedButton.icon(
                    onPressed: () => context.go('/colonies'),
                    icon: const Icon(Icons.explore),
                    label: const Text('Explore Townships'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.white,
                      foregroundColor: AppTheme.primaryColor,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                    ),
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () => context.go('/contact'),
                    icon: const Icon(Icons.phone),
                    label: const Text('Schedule Visit'),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: Colors.white,
                      side: const BorderSide(color: Colors.white, width: 2),
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFooter() {
    return SliverToBoxAdapter(
      child: Container(
        padding: const EdgeInsets.all(32),
        color: AppTheme.primaryColor,
        child: Column(
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: [
                _buildFooterColumn('Quick Links', <Map<String, String>>[
                  {'title': 'Explore Townships', 'route': '/colonies'},
                  {'title': 'Available Plots', 'route': '/plots'},
                  {'title': 'EMI Calculator', 'route': '/emi-calculator'},
                  {'title': 'Stamp Duty', 'route': '/stamp-duty-calculator'},
                ]),
                _buildFooterColumn('Support', <Map<String, String>>[
                  {'title': 'Contact Us', 'route': '/contact'},
                  {'title': 'FAQ', 'route': '/faq'},
                  {'title': 'Privacy Policy', 'route': '/privacy'},
                  {'title': 'Terms & Conditions', 'route': '/terms'},
                ]),
                _buildFooterColumn('Company', <Map<String, String>>[
                  {'title': 'About Us', 'route': '/about'},
                  {'title': 'Careers', 'route': '/careers'},
                  {'title': 'Blog', 'route': '/blog'},
                  {'title': 'Press', 'route': '/press'},
                ]),
                _buildFooterColumn('Contact', <Map<String, String>>[
                  {'title': '+91 70074 44842', 'route': ''},
                  {'title': 'techguruabhay@gmail.com', 'route': ''},
                  {'title': 'Gorakhpur, UP', 'route': ''},
                  {'title': 'www.apsdreamhomes.co.in', 'route': ''},
                ]),
              ],
            ),
            const SizedBox(height: 32),
            Divider(color: Colors.white.withValues(alpha: 0.2)),
            const SizedBox(height: 16),
            Text(
              '© 2026 APS Dream Homes Pvt. Ltd. All rights reserved.',
              style: TextStyle(color: Colors.white.withValues(alpha: 0.6), fontSize: 13),
            ),
            const SizedBox(height: 8),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                _buildSocialIcon(Icons.facebook),
                const SizedBox(width: 16),
                _buildSocialIcon(Icons.alternate_email),
                const SizedBox(width: 16),
                _buildSocialIcon(Icons.video_library),
                const SizedBox(width: 16),
                _buildSocialIcon(Icons.link),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFooterColumn(String title, List<Map<String, String>> links) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title,
          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 16),
        ),
        const SizedBox(height: 16),
        ...links.map((link) => Padding(
          padding: const EdgeInsets.only(bottom: 10),
          child: InkWell(
            onTap: link['route'] != null && link['route']!.isNotEmpty 
                ? () => context.go(link['route']!) 
                : null,
            child: Text(
              link['title'] ?? '',
              style: TextStyle(color: Colors.white.withValues(alpha: 0.7), fontSize: 13),
            ),
          ),
        )),
      ],
    );
  }

  Widget _buildSocialIcon(IconData icon) {
    return Container(
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Icon(icon, color: Colors.white, size: 20),
    );
  }

  String _formatPrice(dynamic price) {
    if (price == null) return '0';
    final numValue = price is num ? price : double.tryParse(price.toString()) ?? 0;
    if (numValue >= 10000000) {
      return '${(numValue / 10000000).toStringAsFixed(1)} Cr';
    } else if (numValue >= 100000) {
      return '${(numValue / 100000).toStringAsFixed(1)} L';
    }
    return numValue.toStringAsFixed(0);
  }
}
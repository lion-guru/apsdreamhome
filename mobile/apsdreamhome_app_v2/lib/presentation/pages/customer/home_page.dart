import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../../data/services/colony_service.dart';
import '../../../data/services/property_listing_service.dart';
import '../../../data/models/colony_model.dart';
import '../../../data/repositories/kyc_repository_provider.dart';
import '../../widgets/app_widgets.dart';
import '../../widgets/ai/floating_ai_button.dart';
import '../../widgets/support/support_fab.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/shimmer_skeletons.dart';

/// Global plots provider — fetches all plots across colonies via colony-scoped endpoints
final allPlotsProvider = FutureProvider<List<Map<String, dynamic>>>((
  ref,
) async {
  try {
    final api = ref.read(apiServiceProvider);
    // First get colonies, then fetch plots per colony
    final coloniesRes = await api.get('/colonies');
    final coloniesData = coloniesRes['data'] ?? [];
    if (coloniesData is! List || coloniesData.isEmpty) return [];

    final allPlots = <Map<String, dynamic>>[];
    for (final colony in coloniesData) {
      final colonyId = colony['id'];
      if (colonyId == null) continue;
      try {
        final plotsRes = await api.get('/colonies/$colonyId/plots');
        final plotsData = plotsRes['data'] ?? [];
        if (plotsData is List) {
          for (final plot in plotsData) {
            if (plot is Map<String, dynamic>) {
              plot['colony_name'] = colony['name'];
              allPlots.add(plot);
            }
          }
        }
      } catch (_) {
        // Colony plots endpoint may not exist, skip
      }
    }
    return allPlots;
  } catch (e) {
    return [];
  }
});

/// AI Recommendations provider — fetches personalized property recommendations
final _aiRecommendationsProvider = FutureProvider<List<Map<String, dynamic>>?>((ref) async {
  try {
    final api = ref.read(apiServiceProvider);
    final res = await api.get('/ai/property-recommendations/me', queryParameters: {'limit': '10'});
    if (res['success'] != true) return null;
    final data = res['data'];
    if (data is Map && data['recommendations'] is List) {
      return List<Map<String, dynamic>>.from(
        (data['recommendations'] as List).map((e) => Map<String, dynamic>.from(e as Map)),
      );
    }
    return null;
  } catch (_) {
    return null; // Guest users / no auth → hide section
  }
});

class HomePage extends ConsumerStatefulWidget {
  const HomePage({super.key});

  @override
  ConsumerState<HomePage> createState() => _HomePageState();
}

class _HomePageState extends ConsumerState<HomePage>
    with SingleTickerProviderStateMixin {
  late final AnimationController _heroAnimController;
  late final Animation<double> _heroScaleAnimation;

  @override
  void initState() {
    super.initState();
    _heroAnimController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 800),
    );
    _heroScaleAnimation = CurvedAnimation(
      parent: _heroAnimController,
      curve: Curves.easeOutBack,
    );
    _heroAnimController.forward();
  }

  @override
  void dispose() {
    _heroAnimController.dispose();
    super.dispose();
  }

  Future<void> _onRefresh() async {
    ref.invalidate(coloniesProvider);
    ref.invalidate(allPlotsProvider);
    ref.invalidate(propertyListingsProvider);
    await Future.delayed(const Duration(milliseconds: 400));
  }

  @override
  Widget build(BuildContext context) {
    final coloniesAsync = ref.watch(coloniesProvider);
    final plotsAsync = ref.watch(allPlotsProvider);
    final propertiesAsync = ref.watch(propertyListingsProvider);

    return Stack(
      children: [
        GradientBackground(
          child: SafeArea(
            child: RefreshIndicator(
              onRefresh: _onRefresh,
              color: AppTheme.primaryColor,
              backgroundColor: Colors.white,
              displacement: 60,
              strokeWidth: 3,
              child: CustomScrollView(
                physics: const AlwaysScrollableScrollPhysics(
                  parent: BouncingScrollPhysics(),
                ),
                slivers: [
                  SliverToBoxAdapter(child: _buildAppBar(context)),
                  SliverToBoxAdapter(
                    child: _buildHeroBanner(context, coloniesAsync),
                  ),
                  SliverToBoxAdapter(child: _buildSearchBar(context)),
                  SliverToBoxAdapter(child: _buildQuickActions(context)),
                  SliverToBoxAdapter(child: _buildAIRecommendations(context)),
                  SliverToBoxAdapter(child: _buildSiteVisitVIPBanner(context)),
                  SliverToBoxAdapter(child: _buildToolsSection(context)),
                  _buildFeaturedColoniesSection(context, ref, coloniesAsync),
                  _buildAvailablePlotsSection(context, ref, plotsAsync),
                  _buildPremiumPropertiesSection(context, ref),
                  _buildPropertiesSection(context, ref, propertiesAsync),
                  SliverToBoxAdapter(child: _buildStatsCounter(context)),
                  SliverToBoxAdapter(child: _buildWhyChooseUs(context)),
                  SliverToBoxAdapter(child: _buildTestimonialsSection(context)),
                  SliverToBoxAdapter(child: _buildContactCTA(context)),
                  const SliverToBoxAdapter(child: SizedBox(height: 32)),
                ],
              ),
            ),
          ),
        ),
        // AI Assistant Button (above support FAB, positioned to avoid overlap)
        const Positioned(bottom: 160, right: 12, child: FloatingAIButton(isMini: true)),
        // WhatsApp-style Support FAB
        const Positioned(bottom: 16, right: 8, child: SupportFAB()),
      ],
    );
  }

  // ───────────────────────────── APP BAR ─────────────────────────────

  Widget _buildAppBar(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
      child: Row(
        children: [
          Container(
            width: 48,
            height: 48,
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(
                color: AppTheme.accentColor.withValues(alpha: 0.4),
                width: 1.5,
              ),
              boxShadow: [
                BoxShadow(
                  color: AppTheme.primaryColor.withValues(alpha: 0.25),
                  blurRadius: 10,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(12),
              child: Image.asset(
                'assets/images/app_logo_luxury.png',
                fit: BoxFit.cover,
                errorBuilder: (context, error, stackTrace) => const Icon(
                  Icons.home_work_rounded,
                  color: AppTheme.primaryColor,
                  size: 26,
                ),
              ),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  AppConstants.appName,
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(
                    fontWeight: FontWeight.w900,
                    color: AppTheme.primaryColor,
                    fontSize: 20,
                    letterSpacing: 0.5,
                  ),
                ),
                Text(
                  'Premium Plots & Townships • Gorakhpur',
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: const Color(0xFF64748B),
                    fontWeight: FontWeight.w600,
                    fontSize: 11,
                  ),
                ),
              ],
            ),
          ),
          _NotificationBell(context: context),
          const SizedBox(width: 4),
          GestureDetector(
            onTap: () => context.push('/live-chat'),
            child: Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(
                  color: const Color(0xFFE8DFD0),
                  width: 1.2,
                ),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF0B194C).withValues(alpha: 0.04),
                    blurRadius: 6,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: const Icon(
                Icons.headset_mic_rounded,
                color: AppTheme.primaryColor,
                size: 20,
              ),
            ),
          ),
          const SizedBox(width: 4),
          _ProfileAvatar(context: context),
        ],
      ),
    );
  }

  // ───────────────────────────── HERO BANNER ─────────────────────────────

  Widget _buildHeroBanner(
    BuildContext context,
    AsyncValue<List<ColonyModel>> coloniesAsync,
  ) {
    return coloniesAsync.when(
      data: (colonies) {
        final active = colonies.where((c) => c.status.toLowerCase() == 'active').toList();
        if (active.isEmpty) {
          return const SizedBox.shrink();
        }
        final featured = active.first;
        final name = featured.name;
        final location = featured.district.isNotEmpty
            ? '${featured.district}${featured.state.isNotEmpty ? ', ${featured.state}' : ''}'
            : (featured.location.isNotEmpty
                ? featured.location
                : 'Premium Location');
        final tagline = 'From ₹${_formatLargeNumber(featured.pricePerSqft)}/sqft';
        final colonyId = featured.id;

        return ScaleTransition(
          scale: _heroScaleAnimation,
          child: GestureDetector(
            onTap: () {
              context.push('/colony-detail/$colonyId');
            },
            child: Container(
              margin: const EdgeInsets.fromLTRB(16, 12, 16, 4),
              constraints: const BoxConstraints(minHeight: 175),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                  colors: [
                    Color(0xFF0D47A1),
                    AppTheme.primaryColor,
                    AppTheme.secondaryColor,
                  ],
                ),
                borderRadius: BorderRadius.circular(20),
                boxShadow: [
                  BoxShadow(
                    color: AppTheme.primaryColor.withValues(alpha: 0.4),
                    blurRadius: 24,
                    offset: const Offset(0, 10),
                  ),
                ],
              ),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(20),
                child: Stack(
                  children: [
                    // Decorative circles
                    Positioned(
                      right: -30,
                      top: -30,
                      child: Container(
                        width: 160,
                        height: 160,
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.07),
                          shape: BoxShape.circle,
                        ),
                      ),
                    ),
                    Positioned(
                      left: -20,
                      bottom: -20,
                      child: Container(
                        width: 100,
                        height: 100,
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.05),
                          shape: BoxShape.circle,
                        ),
                      ),
                    ),
                    // Diagonal decorative stripe
                    Positioned(
                      right: -40,
                      bottom: 0,
                      top: 0,
                      width: 120,
                      child: Transform.rotate(
                        angle: 0.3,
                        child: Container(
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.04),
                          ),
                        ),
                      ),
                    ),
                    Padding(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 20,
                        vertical: 18,
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisAlignment: MainAxisAlignment.center,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 10,
                                  vertical: 4,
                                ),
                                decoration: BoxDecoration(
                                  color: AppTheme.accentColor,
                                  borderRadius: BorderRadius.circular(20),
                                ),
                                child: Text(
                                  featured.status.toUpperCase(),
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 9,
                                    fontWeight: FontWeight.bold,
                                    letterSpacing: 0.8,
                                  ),
                                ),
                              ),
                              const SizedBox(width: 8),
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 8,
                                  vertical: 4,
                                ),
                                decoration: BoxDecoration(
                                  color: Colors.white.withValues(alpha: 0.18),
                                  borderRadius: BorderRadius.circular(20),
                                  border: Border.all(
                                    color: Colors.white.withValues(alpha: 0.35),
                                    width: 1,
                                  ),
                                ),
                                child: const Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Icon(
                                      Icons.verified_rounded,
                                      size: 12,
                                      color: Color(0xFFFDE68A),
                                    ),
                                    SizedBox(width: 4),
                                    Text(
                                      'RERA VERIFIED',
                                      style: TextStyle(
                                        color: Colors.white,
                                        fontSize: 9,
                                        fontWeight: FontWeight.w700,
                                        letterSpacing: 0.5,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 12),
                          Text(
                            name,
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 26,
                              fontWeight: FontWeight.w800,
                              height: 1.15,
                              shadows: [
                                Shadow(
                                  blurRadius: 6,
                                  color: Colors.black26,
                                  offset: Offset(0, 2),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 6),
                          Row(
                            children: [
                              Icon(
                                Icons.location_on_rounded,
                                color: Colors.white.withValues(alpha: 0.8),
                                size: 14,
                              ),
                              const SizedBox(width: 4),
                              Expanded(
                                child: Text(
                                  '$location  •  $tagline',
                                  style: TextStyle(
                                    color: Colors.white.withValues(alpha: 0.85),
                                    fontSize: 13,
                                    fontWeight: FontWeight.w400,
                                    shadows: const [
                                      Shadow(
                                        blurRadius: 4,
                                        color: Colors.black26,
                                        offset: Offset(0, 1),
                                      ),
                                    ],
                                  ),
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 16),
                          Container(
                            height: 38,
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(20),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.15),
                                  blurRadius: 8,
                                  offset: const Offset(0, 3),
                                ),
                              ],
                            ),
                            child: Material(
                              color: Colors.transparent,
                              child: InkWell(
                                onTap: () => context.push('/colonies'),
                                borderRadius: BorderRadius.circular(20),
                                child: const Padding(
                                  padding: EdgeInsets.symmetric(horizontal: 22),
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      Text(
                                        'Explore Now',
                                        style: TextStyle(
                                          fontWeight: FontWeight.bold,
                                          fontSize: 13,
                                          color: AppTheme.primaryColor,
                                        ),
                                      ),
                                      SizedBox(width: 6),
                                      Icon(
                                        Icons.arrow_forward_rounded,
                                        size: 16,
                                        color: AppTheme.primaryColor,
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        );
      },
      loading: () => ShimmerSkeletons.heroBanner(),
      error: (_, _) => ShimmerSkeletons.heroBanner(),
    );
  }

  // ───────────────────────────── SEARCH BAR ─────────────────────────────

  Widget _buildSearchBar(BuildContext context) {
    return GestureDetector(
      onTap: () => context.push('/properties'),
      child: Container(
        margin: const EdgeInsets.fromLTRB(16, 12, 16, 4),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: const Color(0xFFE8DFD0),
            width: 1.2,
          ),
          boxShadow: [
            BoxShadow(
              color: const Color(0xFF0B194C).withValues(alpha: 0.04),
              blurRadius: 10,
              offset: const Offset(0, 3),
            ),
          ],
        ),
        child: Row(
          children: [
            const Icon(
              Icons.search_rounded,
              color: AppTheme.primaryColor,
              size: 22,
            ),
            const SizedBox(width: 12),
            const Expanded(
              child: Text(
                'Search colonies, plots, locations...',
                style: TextStyle(
                  color: Color(0xFF94A3B8),
                  fontSize: 14,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ),
            Container(
              padding: const EdgeInsets.all(6),
              decoration: BoxDecoration(
                color: const Color(0xFFFAF7F0),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: const Color(0xFFE8DFD0)),
              ),
              child: const Icon(
                Icons.tune_rounded,
                color: AppTheme.primaryColor,
                size: 18,
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ───────────────────────────── QUICK ACTIONS ─────────────────────────────

  Widget _buildQuickActions(BuildContext context) {
    final actions = [
      const _QuickAction(
        icon: Icons.map_outlined,
        label: 'Colonies',
        route: '/colonies',
        gradient: LinearGradient(
          colors: [Color(0xFF43A047), Color(0xFF66BB6A)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      const _QuickAction(
        icon: Icons.grid_view_rounded,
        label: 'Plots',
        route: '/plots',
        gradient: LinearGradient(
          colors: [Color(0xFF1E88E5), Color(0xFF42A5F5)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      const _QuickAction(
        icon: Icons.apartment_rounded,
        label: 'Properties',
        route: '/properties',
        gradient: LinearGradient(
          colors: [Color(0xFFFB8C00), Color(0xFFFFB74D)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      const _QuickAction(
        icon: Icons.calculate_outlined,
        label: 'EMI Calc',
        route: '/emi-calculator',
        gradient: LinearGradient(
          colors: [Color(0xFF8E24AA), Color(0xFFBA68C8)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      const _QuickAction(
        icon: Icons.add_home_outlined,
        label: 'Post',
        route: '/post-property',
        gradient: LinearGradient(
          colors: [Color(0xFFD81B60), Color(0xFFF06292)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
    ];

    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceAround,
        children: actions.map((action) {
          return _QuickActionWidget(action: action);
        }).toList(),
      ),
    );
  }

  // ───────────────────────────── AI RECOMMENDATIONS ─────────────────────────────

  Widget _buildAIRecommendations(BuildContext context) {
    return Consumer(
      builder: (context, ref, _) {
        final recsAsync = ref.watch(_aiRecommendationsProvider);
        return recsAsync.when(
          loading: () => const SizedBox.shrink(),
          error: (_, _) => const SizedBox.shrink(),
          data: (recs) {
            if (recs == null || recs.isEmpty) return const SizedBox.shrink();
            return Padding(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 4),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      const Icon(Icons.auto_awesome, color: Color(0xFF9C27B0), size: 20),
                      const SizedBox(width: 8),
                      const Text(
                        'Recommended For You',
                        style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                      ),
                      const Spacer(),
                      TextButton(
                        onPressed: () => context.push('/user/ai-suggestions'),
                        child: const Text('See All', style: TextStyle(fontSize: 12)),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  SizedBox(
                    height: 150,
                    child: ListView.builder(
                      scrollDirection: Axis.horizontal,
                      itemCount: recs.length,
                      itemBuilder: (context, index) {
                        final rec = recs[index];
                        return _buildRecommendationCard(rec);
                      },
                    ),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildRecommendationCard(Map<String, dynamic> rec) {
    final name = (rec['name'] ?? rec['plot_number'] ?? 'Plot').toString();
    final colony = (rec['colony_name'] ?? rec['city'] ?? '').toString();
    final price = rec['total_price'] ?? rec['price'];
    final reason = (rec['reason'] ?? '').toString();
    final confidence = (rec['confidence'] as num? ?? 0) * 100;

    return Container(
      width: 180,
      margin: const EdgeInsets.only(right: 12),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFFF3E5F5), Color(0xFFFFFFFF)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.purple.shade100),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.recommend, color: Color(0xFF9C27B0), size: 16),
              const SizedBox(width: 4),
              Expanded(
                child: Text(
                  name,
                  style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Text(colony, style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
          if (price != null) ...[
            const SizedBox(height: 6),
            Text(
              '₹${price.toString()}',
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0B194C)),
            ),
          ],
          if (reason.isNotEmpty) ...[
            const SizedBox(height: 6),
            Text(
              reason,
              style: TextStyle(fontSize: 11, color: Colors.purple.shade700, fontStyle: FontStyle.italic),
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
          ],
          if (confidence > 0) ...[
            const SizedBox(height: 6),
            LinearProgressIndicator(
              value: confidence / 100,
              backgroundColor: Colors.purple.shade100,
              valueColor: const AlwaysStoppedAnimation<Color>(Color(0xFF9C27B0)),
              minHeight: 4,
              borderRadius: BorderRadius.circular(2),
            ),
            const SizedBox(height: 2),
            Text(
              '${confidence.toStringAsFixed(0)}% match',
              style: TextStyle(fontSize: 10, color: Colors.grey.shade600),
            ),
          ],
        ],
      ),
    );
  }

  // ───────────────────────────── VIP SITE VISIT BANNER ─────────────────────────────

  Widget _buildSiteVisitVIPBanner(BuildContext context) {
    return Container(
      margin: const EdgeInsets.fromLTRB(16, 4, 16, 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF0B194C), Color(0xFF1E3A8A)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(18),
        border: Border.all(
          color: const Color(0xFFD4AF37),
          width: 1.5,
        ),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0B194C).withValues(alpha: 0.25),
            blurRadius: 16,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: const Color(0xFFD4AF37),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: const Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.directions_car_rounded, size: 14, color: Color(0xFF0B194C)),
                    SizedBox(width: 4),
                    Text(
                      'VIP COMPLIMENTARY',
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.w900,
                        color: Color(0xFF0B194C),
                        letterSpacing: 0.5,
                      ),
                    ),
                  ],
                ),
              ),
              const Spacer(),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Text(
                  'Gorakhpur Townships',
                  style: TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.w600),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          const Text(
            'Free AC Car Pickup & Site Visit',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w800,
              color: Colors.white,
              letterSpacing: 0.2,
            ),
          ),
          const SizedBox(height: 4),
          const Text(
            'Pickup available from Railway Station, Airport, Mohaddipur or Home. Experience your future plot in person.',
            style: TextStyle(
              fontSize: 12,
              color: Color(0xFFCBD5E1),
              height: 1.4,
            ),
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              Expanded(
                child: ElevatedButton.icon(
                  onPressed: () => context.push('/book-site-visit'),
                  icon: const Icon(Icons.calendar_month_rounded, size: 16),
                  label: const Text(
                    'Book Free Visit',
                    style: TextStyle(fontWeight: FontWeight.w700, fontSize: 13),
                  ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFD4AF37),
                    foregroundColor: const Color(0xFF0B194C),
                    elevation: 0,
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 10),
              OutlinedButton.icon(
                onPressed: () => context.push('/maintenance-dues'),
                icon: const Icon(Icons.receipt_long_rounded, size: 16, color: Colors.white),
                label: const Text(
                  'Maintenance',
                  style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 12),
                ),
                style: OutlinedButton.styleFrom(
                  side: const BorderSide(color: Colors.white38),
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  // ───────────────────────────── TOOLS & MORE ─────────────────────────────

  Widget _buildToolsSection(BuildContext context) {
    final tools = [
      const _ToolItem(
        Icons.directions_car_rounded,
        'Site Visit',
        '/book-site-visit',
        Color(0xFFE65100),
      ),
      const _ToolItem(
        Icons.receipt_long_rounded,
        'Maintenance',
        '/maintenance-dues',
        Color(0xFF0D9488),
      ),
      const _ToolItem(
        Icons.support_agent_rounded,
        'Helpdesk',
        '/township-grievances',
        Color(0xFF1E88E5),
      ),
      const _ToolItem(
        Icons.domain_outlined,
        'Township Ops',
        '/township-operations',
        Color(0xFF7C3AED),
      ),
      const _ToolItem(
        Icons.wb_sunny_outlined,
        'Solar Subsidy',
        '/solar-subsidy',
        Color(0xFFF59E0B),
      ),
      const _ToolItem(
        Icons.event_available_rounded,
        'Registry Appt',
        '/registry-appointments',
        Color(0xFF6D28D9),
      ),
      const _ToolItem(
        Icons.calculate,
        'Stamp Duty',
        '/stamp-duty-calculator',
        Color(0xFF43A047),
      ),
      const _ToolItem(
        Icons.straighten,
        'Plot Convert',
        '/plot-converter',
        Color(0xFF00897B),
      ),
      const _ToolItem(
        Icons.question_answer,
        'FAQs',
        '/faq',
        AppTheme.infoColor,
      ),
      const _ToolItem(
        Icons.star_border,
        'Reviews',
        '/testimonials',
        AppTheme.accentColor,
      ),
      const _ToolItem(
        Icons.search,
        'Saved Search',
        '/saved-searches',
        AppTheme.primaryColor,
      ),
      const _ToolItem(
        Icons.compare_arrows,
        'Compare',
        '/compare',
        AppTheme.secondaryColor,
      ),
      const _ToolItem(
        Icons.health_and_safety_outlined,
        'Insurance',
        '/insurance',
        Color(0xFF4CAF50),
      ),
      const _ToolItem(
        Icons.receipt_long_outlined,
        'E-Mandate',
        '/nach-mandate',
        Color(0xFF00897B),
      ),
      const _ToolItem(
        Icons.auto_stories_outlined,
        'Agreements',
        '/agreements',
        Color(0xFF1565C0),
      ),
      const _ToolItem(
        Icons.info_outline,
        'About',
        '/about',
        AppTheme.infoColor,
      ),
      const _ToolItem(
        Icons.article_outlined,
        'Blog',
        '/blog',
        AppTheme.secondaryColor,
      ),
      const _ToolItem(
        Icons.work_outline,
        'Careers',
        '/careers',
        AppTheme.accentColor,
      ),
      const _ToolItem(
        Icons.explore_outlined,
        'How It Works',
        '/how-it-works',
        AppTheme.primaryColor,
      ),
      const _ToolItem(
        Icons.storefront_outlined,
        'Services',
        '/services',
        Color(0xFF6A1B9A),
      ),
      const _ToolItem(
        Icons.build_circle_outlined,
        'Tools Hub',
        '/tools-hub',
        Color(0xFF4CAF50),
      ),
      const _ToolItem(
        Icons.business_outlined,
        'Projects',
        '/projects',
        Color(0xFF1565C0),
      ),
      const _ToolItem(
        Icons.shopping_cart_outlined,
        'Buy',
        '/buy',
        Color(0xFF43A047),
      ),
      const _ToolItem(Icons.sell_outlined, 'Sell', '/sell', Color(0xFFE65100)),
      const _ToolItem(
        Icons.home_work_outlined,
        'Rent',
        '/rent',
        Color(0xFF00897B),
      ),
      const _ToolItem(
        Icons.trending_up_outlined,
        'Invest',
        '/invest',
        Color(0xFF6A1B9A),
      ),
      const _ToolItem(
        Icons.card_giftcard_outlined,
        'Referral Earnings',
        '/referral-earnings',
        Color(0xFFF59E0B),
      ),
      const _ToolItem(
        Icons.local_offer_outlined,
        'Offers',
        '/offers',
        Color(0xFFE91E63),
      ),
      const _ToolItem(
        Icons.account_balance_wallet_outlined,
        'Activate Wallet',
        '/wallet-activation',
        Color(0xFF0D9488),
      ),
      const _ToolItem(
        Icons.photo_library_outlined,
        'Gallery',
        '/gallery',
        Color(0xFF1565C0),
      ),
      const _ToolItem(
        Icons.contact_phone_outlined,
        'Contact',
        '/contact',
        Color(0xFF4CAF50),
      ),
      const _ToolItem(
        Icons.group_outlined,
        'Team',
        '/team',
        AppTheme.infoColor,
      ),
      const _ToolItem(
        Icons.privacy_tip_outlined,
        'Privacy',
        '/privacy',
        AppTheme.secondaryColor,
      ),
      const _ToolItem(
        Icons.explore_outlined,
        'Neighborhood',
        '/neighborhood',
        Color(0xFF1565C0),
      ),
      const _ToolItem(
        Icons.videocam_outlined,
        'Virtual Tour',
        '/virtual-tour',
        Color(0xFF6A1B9A),
      ),
      const _ToolItem(Icons.newspaper, 'News', '/news', Color(0xFFE65100)),
      const _ToolItem(
        Icons.chat_bubble_outline_rounded,
        'Messages',
        '/inbox',
        Color(0xFF4F46E5),
      ),
      const _ToolItem(
        Icons.verified_outlined,
        'Registry',
        '/my-bookings',
        Color(0xFF0d9488),
      ),
      const _ToolItem(
        Icons.account_balance_wallet_outlined,
        'Payouts',
        '/admin/payout-batches',
        Color(0xFF7c3aed),
      ),
      const _ToolItem(
        Icons.receipt_long_rounded,
        'Demand',
        '/demand-letters',
        Color(0xFFe11d48),
      ),
      const _ToolItem(
        Icons.construction_rounded,
        'Site Work',
        '/admin/colony-progress',
        Color(0xFFea580c),
      ),
      const _ToolItem(
        Icons.inventory_2_rounded,
        'Stock',
        '/admin/material-inventory',
        Color(0xFF0d9488),
      ),
    ];

    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 4, 16, 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            children: [
              Icon(Icons.build_circle_outlined, size: 20, color: AppTheme.accentColor),
              SizedBox(width: 8),
              Text(
                'Tools & Quick Services',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w800,
                  color: AppTheme.primaryColor,
                  letterSpacing: 0.3,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          SizedBox(
            height: 80,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              itemCount: tools.length,
              separatorBuilder: (_, _) => const SizedBox(width: 10),
              itemBuilder: (context, index) {
                final tool = tools[index];
                return GestureDetector(
                  onTap: () => context.push(tool.route),
                  child: Container(
                    width: 78,
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(
                        color: const Color(0xFFE8DFD0),
                        width: 1.2,
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFF0B194C).withValues(alpha: 0.04),
                          blurRadius: 6,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(tool.icon, color: tool.color, size: 24),
                        const SizedBox(height: 6),
                        Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 4),
                          child: Text(
                            tool.label,
                            style: const TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.w700,
                              color: AppTheme.primaryColor,
                            ),
                            textAlign: TextAlign.center,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
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

  // ───────────────────────────── FEATURED COLONIES ─────────────────────────────

  Widget _buildFeaturedColoniesSection(
    BuildContext context,
    WidgetRef ref,
    AsyncValue<List<ColonyModel>> coloniesAsync,
  ) {
    return SliverToBoxAdapter(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          AppWidgets.sectionHeader(
            title: 'Featured Colonies',
            subtitle: 'Explore our premium developments',
            onSeeAll: () => context.push('/colonies'),
          ),
          coloniesAsync.when(
            data: (colonies) {
              if (colonies.isEmpty) {
                return AppWidgets.emptyState(
                  title: 'No Colonies Available',
                  subtitle: 'Check back later for new developments',
                  icon: Icons.location_city_outlined,
                  onAction: () => ref.refresh(coloniesProvider),
                  actionLabel: 'Refresh',
                );
              }
              return SizedBox(
                height: 210,
                child: ListView.separated(
                  scrollDirection: Axis.horizontal,
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  itemCount: colonies.length,
                  separatorBuilder: (_, _) => const SizedBox(width: 12),
                  itemBuilder: (context, index) {
                    return _buildColonyHorizontalCard(context, colonies[index]);
                  },
                ),
              );
            },
            loading: () =>
                ShimmerSkeletons.horizontalCards(height: 210, cardWidth: 220),
            error: (error, stack) => AppWidgets.errorWidget(
              message: 'Failed to load colonies',
              onRetry: () => ref.refresh(coloniesProvider),
            ),
          ),
        ],
      ),
    );
  }

  static const _luxuryColonyImages = [
    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1613977257363-707ba9348227?w=800&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?w=800&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1524230572899-a752b3835840?w=800&auto=format&fit=crop&q=80',
  ];

  String _getFallbackColonyImage(int id) {
    final idx = (id > 0 ? id - 1 : 0) % _luxuryColonyImages.length;
    return _luxuryColonyImages[idx];
  }

  Widget _colonyPlaceholder([int id = 0]) {
    final imgUrl = _getFallbackColonyImage(id);
    return Image.network(
      imgUrl,
      fit: BoxFit.cover,
      errorBuilder: (_, _, _) => Container(
        color: const Color(0xFFF5EFE3),
        child: const Center(
          child: Icon(Icons.home_work_rounded, color: AppTheme.accentColor, size: 36),
        ),
      ),
    );
  }

  Widget _buildColonyHorizontalCard(BuildContext context, ColonyModel colony) {
    final imgUrl = colony.displayImages.isNotEmpty
        ? colony.displayImages.first
        : _getFallbackColonyImage(colony.id);

    return GestureDetector(
      onTap: () => context.push('/colony-detail/${colony.id}'),
      child: Container(
        width: 220,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: const Color(0xFFE8DFD0),
            width: 1.2,
          ),
          boxShadow: [
            BoxShadow(
              color: const Color(0xFF0B194C).withValues(alpha: 0.06),
              blurRadius: 14,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            ClipRRect(
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(16),
              ),
              child: SizedBox(
                height: 120,
                width: double.infinity,
                child: Stack(
                  fit: StackFit.expand,
                  children: [
                    Image.network(
                      imgUrl,
                      fit: BoxFit.cover,
                      errorBuilder: (_, _, _) => _colonyPlaceholder(colony.id),
                    ),
                    // Gradient overlay at bottom
                    Positioned(
                      bottom: 0,
                      left: 0,
                      right: 0,
                      height: 50,
                      child: Container(
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            begin: Alignment.topCenter,
                            end: Alignment.bottomCenter,
                            colors: [
                              Colors.transparent,
                              Colors.black.withValues(alpha: 0.65),
                            ],
                          ),
                        ),
                      ),
                    ),
                    // Price on image
                    Positioned(
                      bottom: 8,
                      left: 10,
                      child: Text(
                        '₹${_formatNumber(colony.pricePerSqft)}/sqft',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 12,
                          fontWeight: FontWeight.w800,
                          shadows: [
                            Shadow(blurRadius: 4, color: Colors.black87),
                          ],
                        ),
                      ),
                    ),
                    // Status badge
                    Positioned(
                      top: 8,
                      right: 8,
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 3,
                        ),
                        decoration: BoxDecoration(
                          color: const Color(0xFF10B981),
                          borderRadius: BorderRadius.circular(12),
                          boxShadow: const [
                            BoxShadow(
                              color: Colors.black26,
                              blurRadius: 4,
                            ),
                          ],
                        ),
                        child: Text(
                          colony.status.toUpperCase(),
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 9,
                            fontWeight: FontWeight.w800,
                            letterSpacing: 0.4,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    colony.name,
                    style: const TextStyle(
                      fontWeight: FontWeight.w800,
                      color: AppTheme.primaryColor,
                      fontSize: 14,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      const Icon(
                        Icons.location_on_outlined,
                        size: 13,
                        color: AppTheme.accentColor,
                      ),
                      const SizedBox(width: 3),
                      Expanded(
                        child: Text(
                          colony.district.isNotEmpty
                              ? '${colony.district}${colony.state.isNotEmpty ? ', ${colony.state}' : ''}'
                              : colony.location,
                          style: const TextStyle(
                            fontSize: 11,
                            color: Color(0xFF64748B),
                            fontWeight: FontWeight.w500,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: const Color(0xFFFAF7F0),
                          borderRadius: BorderRadius.circular(6),
                          border: Border.all(color: const Color(0xFFE8DFD0)),
                        ),
                        child: Text(
                          '${colony.availablePlots} plots avail.',
                          style: const TextStyle(
                            fontSize: 10,
                            color: AppTheme.primaryColor,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ),
                      const Text(
                        'Instant Registry',
                        style: TextStyle(
                          fontSize: 9,
                          color: Color(0xFF10B981),
                          fontWeight: FontWeight.w700,
                        ),
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

  // ───────────────────────────── AVAILABLE PLOTS ─────────────────────────────

  Widget _buildAvailablePlotsSection(
    BuildContext context,
    WidgetRef ref,
    AsyncValue<List<Map<String, dynamic>>> plotsAsync,
  ) {
    return SliverToBoxAdapter(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          AppWidgets.sectionHeader(
            title: 'Available Plots',
            subtitle: 'Find your perfect plot',
            onSeeAll: () => context.push('/plots'),
          ),
          plotsAsync.when(
            data: (plots) {
              if (plots.isEmpty) {
                return AppWidgets.emptyState(
                  title: 'No Plots Listed',
                  subtitle: 'Plots will appear here once colonies are launched',
                  icon: Icons.grid_view_rounded,
                  onAction: () => ref.refresh(allPlotsProvider),
                  actionLabel: 'Refresh',
                );
              }
              final display = plots.length > 10 ? plots.sublist(0, 10) : plots;
              return SizedBox(
                height: 160,
                child: ListView.separated(
                  scrollDirection: Axis.horizontal,
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  itemCount: display.length,
                  separatorBuilder: (_, _) => const SizedBox(width: 12),
                  itemBuilder: (context, index) {
                    return _buildPlotHorizontalCard(context, display[index]);
                  },
                ),
              );
            },
            loading: () =>
                ShimmerSkeletons.horizontalCards(height: 160, cardWidth: 170),
            error: (error, stack) => AppWidgets.errorWidget(
              message: 'Failed to load plots',
              onRetry: () => ref.refresh(allPlotsProvider),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPlotHorizontalCard(
    BuildContext context,
    Map<String, dynamic> plot,
  ) {
    final plotNumber =
        plot['plot_number']?.toString() ??
        plot['plotNumber']?.toString() ??
        '—';
    final area = _parsePlotArea(plot);
    final price = _parsePlotPrice(plot);
    final status = (plot['status']?.toString() ?? 'available').toLowerCase();
    final colony =
        plot['colony_name']?.toString() ?? plot['colonyName']?.toString() ?? '';
    final id = plot['id']?.toString() ?? '';
    final block = plot['block']?.toString() ?? '';

    return GestureDetector(
      onTap: () {
        if (id.isNotEmpty) context.push('/plot-detail/$id');
      },
      child: Container(
        width: 170,
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [Colors.white, _statusGradientStart(status)],
          ),
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: _statusShadowColor(status).withValues(alpha: 0.12),
              blurRadius: 12,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            Row(
              children: [
                Flexible(
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      color: AppTheme.primaryColor.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      block.isNotEmpty
                          ? '$block · $plotNumber'
                          : 'Plot $plotNumber',
                      style: const TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                        color: AppTheme.primaryColor,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ),
                const SizedBox(width: 6),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 6,
                    vertical: 3,
                  ),
                  decoration: BoxDecoration(
                    color: _statusBadgeColor(status).withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Text(
                    status.toUpperCase(),
                    style: TextStyle(
                      fontSize: 8,
                      fontWeight: FontWeight.w700,
                      color: _statusBadgeColor(status),
                      letterSpacing: 0.3,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            if (colony.isNotEmpty)
              Padding(
                padding: const EdgeInsets.only(bottom: 6),
                child: Text(
                  colony,
                  style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            const Spacer(),
            Text(
              '$area sqft',
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w500,
                color: Colors.grey.shade600,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              '₹${_formatLargeNumber(price)}',
              style: const TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.bold,
                color: AppTheme.primaryColor,
              ),
            ),
          ],
        ),
      ),
    );
  }

  static Color _statusBadgeColor(String status) {
    switch (status) {
      case 'available':
      case 'open':
        return const Color(0xFF388E3C);
      case 'booked':
      case 'reserved':
        return const Color(0xFF1565C0);
      case 'sold':
        return const Color(0xFF9E9E9E);
      case 'hold':
        return const Color(0xFFF57C00);
      default:
        return const Color(0xFF616161);
    }
  }

  static Color _statusGradientStart(String status) {
    switch (status) {
      case 'available':
      case 'open':
        return const Color(0xFFF1F8E9);
      case 'booked':
      case 'reserved':
        return const Color(0xFFE3F2FD);
      case 'sold':
        return const Color(0xFFF5F5F5);
      case 'hold':
        return const Color(0xFFFFF3E0);
      default:
        return Colors.white;
    }
  }

  static Color _statusShadowColor(String status) {
    switch (status) {
      case 'available':
        return const Color(0xFF388E3C);
      case 'booked':
      case 'reserved':
        return const Color(0xFF1565C0);
      case 'sold':
        return const Color(0xFF9E9E9E);
      case 'hold':
        return const Color(0xFFF57C00);
      default:
        return Colors.black;
    }
  }

  // ───────────────────────────── PROPERTIES FOR SALE ─────────────────────────────

  Widget _buildPremiumPropertiesSection(BuildContext context, WidgetRef ref) {
    return SliverToBoxAdapter(
      child: FutureBuilder<List<PropertyListing>>(
        future: _fetchPremiumProperties(),
        builder: (context, snapshot) {
          if (!snapshot.hasData || snapshot.data!.isEmpty) {
            return const SizedBox.shrink();
          }
          final premium = snapshot.data!;
          return Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              AppWidgets.sectionHeader(
                title: '⭐ Premium Listings',
                subtitle: 'Exclusive handpicked properties',
                onSeeAll: () => context.push('/properties'),
              ),
              const SizedBox(height: 12),
              SizedBox(
                height: 200,
                child: ListView.builder(
                  scrollDirection: Axis.horizontal,
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  itemCount: premium.length > 5 ? 5 : premium.length,
                  itemBuilder: (context, index) {
                    final p = premium[index];
                    return Container(
                      width: 200,
                      margin: const EdgeInsets.only(right: 12),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.08),
                            blurRadius: 8,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(12),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Stack(
                              children: [
                                Container(
                                  height: 100,
                                  width: double.infinity,
                                  color: Colors.grey.shade200,
                                  child: p.imageUrl != null
                                      ? Image.network(
                                          p.imageUrl!,
                                          fit: BoxFit.cover,
                                          errorBuilder: (_, _, _) => Icon(
                                            Icons.image,
                                            size: 40,
                                            color: Colors.grey.shade400,
                                          ),
                                        )
                                      : Icon(
                                          Icons.image,
                                          size: 40,
                                          color: Colors.grey.shade400,
                                        ),
                                ),
                                Positioned(
                                  top: 6,
                                  left: 6,
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 6,
                                      vertical: 3,
                                    ),
                                    decoration: BoxDecoration(
                                      gradient: const LinearGradient(
                                        colors: [
                                          Color(0xFFFFD700),
                                          Color(0xFFFFA000),
                                        ],
                                      ),
                                      borderRadius: BorderRadius.circular(4),
                                    ),
                                    child: const Row(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        Icon(
                                          Icons.star,
                                          color: Colors.white,
                                          size: 12,
                                        ),
                                        SizedBox(width: 2),
                                        Text(
                                          'Premium',
                                          style: TextStyle(
                                            color: Colors.white,
                                            fontSize: 10,
                                            fontWeight: FontWeight.bold,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              ],
                            ),
                            Padding(
                              padding: const EdgeInsets.all(10),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    p.title,
                                    style: const TextStyle(
                                      fontWeight: FontWeight.bold,
                                      fontSize: 13,
                                    ),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    '₹${NumberFormat('#,##,###').format(p.price.toInt())}',
                                    style: TextStyle(
                                      color: Colors.blue.shade700,
                                      fontWeight: FontWeight.bold,
                                      fontSize: 13,
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    p.location,
                                    style: TextStyle(
                                      color: Colors.grey.shade600,
                                      fontSize: 11,
                                    ),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
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
              ),
              const SizedBox(height: 16),
            ],
          );
        },
      ),
    );
  }

  Future<List<PropertyListing>> _fetchPremiumProperties() async {
    try {
      final api = ApiService();
      final response = await api.get('/api/v2/mobile/properties/browse', queryParameters: {'limit': 5, 'featured': true});
      final data = response;
      if (data['success'] == true) {
        final inner = data['data'];
        final List<dynamic> properties;
        if (inner is Map && inner.containsKey('properties')) {
          properties = (inner['properties'] as List<dynamic>?) ?? [];
        } else if (inner is List) {
          properties = inner;
        } else {
          properties = [];
        }
        return properties
            .map((j) => PropertyListing.fromJson(j as Map<String, dynamic>))
            .take(5)
            .toList();
      }
    } catch (_) {}
    return [];
  }

  Widget _buildPropertiesSection(
    BuildContext context,
    WidgetRef ref,
    AsyncValue<List<PropertyListing>> propertiesAsync,
  ) {
    return SliverToBoxAdapter(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          AppWidgets.sectionHeader(
            title: 'Properties for Sale',
            subtitle: 'Handpicked listings for you',
            onSeeAll: () {
              if (GoRouterState.of(context).uri.path != '/properties') {
                context.go('/properties');
              }
            },
          ),
          propertiesAsync.when(
            data: (listings) {
              if (listings.isEmpty) {
                return AppWidgets.emptyState(
                  title: 'No Properties Listed',
                  subtitle: 'Be the first to list your property',
                  icon: Icons.apartment_rounded,
                  onAction: () => ref.refresh(propertyListingsProvider),
                  actionLabel: 'Refresh',
                );
              }
              final display = listings.length > 3
                  ? listings.sublist(0, 3)
                  : listings;
              return Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: Column(
                  children: display.map((listing) {
                    return Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: _buildPropertyCard(context, listing),
                    );
                  }).toList(),
                ),
              );
            },
            loading: () => ShimmerSkeletons.verticalCards(),
            error: (error, stack) => AppWidgets.errorWidget(
              message: 'Failed to load properties',
              onRetry: () => ref.refresh(propertyListingsProvider),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPropertyCard(BuildContext context, PropertyListing listing) {
    return GestureDetector(
      onTap: () => context.push('/property-detail/${listing.id}'),
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.06),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Row(
          children: [
            ClipRRect(
              borderRadius: const BorderRadius.horizontal(
                left: Radius.circular(16),
              ),
              child: Container(
                width: 110,
                height: 110,
                color: Colors.grey.shade100,
                child: listing.imageUrl != null && listing.imageUrl!.isNotEmpty
                    ? Image.network(
                        listing.imageUrl!,
                        fit: BoxFit.cover,
                        errorBuilder: (_, _, _) => _propertyPlaceholder(),
                      )
                    : _propertyPlaceholder(),
              ),
            ),
            Expanded(
              child: Padding(
                padding: const EdgeInsets.all(12),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            listing.title,
                            style: const TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 14,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        AppWidgets.statusBadge(status: listing.purposeLabel),
                      ],
                    ),
                    const SizedBox(height: 6),
                    Row(
                      children: [
                        Icon(
                          Icons.location_on_outlined,
                          size: 13,
                          color: Colors.grey.shade500,
                        ),
                        const SizedBox(width: 3),
                        Expanded(
                          child: Text(
                            listing.location,
                            style: TextStyle(
                              fontSize: 12,
                              color: Colors.grey.shade500,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          '₹${listing.formattedPrice}',
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 16,
                            color: AppTheme.primaryColor,
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 3,
                          ),
                          decoration: BoxDecoration(
                            color: AppTheme.infoColor.withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            listing.type.toUpperCase(),
                            style: const TextStyle(
                              fontSize: 9,
                              fontWeight: FontWeight.w600,
                              color: AppTheme.infoColor,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            Icon(
              Icons.arrow_forward_ios,
              size: 14,
              color: Colors.grey.shade400,
            ),
            const SizedBox(width: 8),
          ],
        ),
      ),
    );
  }

  Widget _propertyPlaceholder() {
    return Center(
      child: Icon(
        Icons.apartment_rounded,
        color: Colors.grey.shade300,
        size: 36,
      ),
    );
  }

  // ───────────────────────────── STATS COUNTER ─────────────────────────────

  Widget _buildStatsCounter(BuildContext context) {
    final stats = [
      const _StatData('7+', 'Colonies', Icons.location_city_rounded, Color(0xFF43A047)),
      const _StatData('750+', 'Plots', Icons.grid_on_rounded, Color(0xFF1E88E5)),
      const _StatData('1000+', 'Families', Icons.people_rounded, Color(0xFFFF9800)),
      const _StatData('12+', 'Years', Icons.workspace_premium_rounded, Color(0xFF9C27B0)),
    ];

    return Container(
      margin: const EdgeInsets.fromLTRB(16, 4, 16, 16),
      padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: const Color(0xFFE8DFD0),
          width: 1.2,
        ),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0B194C).withValues(alpha: 0.05),
            blurRadius: 14,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceAround,
        children: stats.map((stat) {
          return Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: stat.color.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(stat.icon, color: stat.color, size: 22),
              ),
              const SizedBox(height: 8),
              Text(
                stat.value,
                style: const TextStyle(
                  color: AppTheme.primaryColor,
                  fontSize: 20,
                  fontWeight: FontWeight.w900,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                stat.label,
                style: const TextStyle(
                  color: Color(0xFF64748B),
                  fontSize: 11,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ],
          );
        }).toList(),
      ),
    );
  }

  // ───────────────────────────── TESTIMONIALS ─────────────────────────────

  Widget _buildTestimonialsSection(BuildContext context) {
    final testimonials = [
      const _TestimonialData(
        name: 'Rajesh Kumar',
        role: 'Plot Owner, Suryoday Colony',
        text: 'Excellent experience! The plots are well-located and the team helped me through every step of the purchase.',
        rating: 5,
      ),
      const _TestimonialData(
        name: 'Priya Singh',
        role: 'Home Buyer, Braj Radha Nagri',
        text: 'APS Dream Home made my dream of owning a plot come true. Very transparent and professional.',
        rating: 5,
      ),
      const _TestimonialData(
        name: 'Amit Verma',
        role: 'Investor, Raghunath Nagri',
        text: 'Great investment returns! The colonies are well-planned and the ROI has been fantastic.',
        rating: 4,
      ),
    ];

    return Column(
      children: [
        AppWidgets.sectionHeader(
          title: 'What Our Clients Say',
          subtitle: 'Trusted by families across India',
        ),
        SizedBox(
          height: 170,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 16),
            itemCount: testimonials.length,
            separatorBuilder: (_, _) => const SizedBox(width: 12),
            itemBuilder: (context, index) {
              final t = testimonials[index];
              return Container(
                width: 280,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: Colors.grey.shade200),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.04),
                      blurRadius: 8,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: List.generate(
                        5,
                        (i) => Icon(
                          i < t.rating
                              ? Icons.star_rounded
                              : Icons.star_border_rounded,
                          color: const Color(0xFFFFB300),
                          size: 18,
                        ),
                      ),
                    ),
                    const SizedBox(height: 8),
                    Expanded(
                      child: Text(
                        t.text,
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.grey.shade700,
                          height: 1.4,
                        ),
                        maxLines: 3,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        CircleAvatar(
                          radius: 16,
                          backgroundColor: AppTheme.primaryColor.withValues(alpha: 0.1),
                          child: Text(
                            t.name[0],
                            style: const TextStyle(
                              color: AppTheme.primaryColor,
                              fontWeight: FontWeight.bold,
                              fontSize: 14,
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                t.name,
                                style: const TextStyle(
                                  fontWeight: FontWeight.w600,
                                  fontSize: 12,
                                ),
                              ),
                              Text(
                                t.role,
                                style: TextStyle(
                                  fontSize: 10,
                                  color: Colors.grey.shade500,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              );
            },
          ),
        ),
        const SizedBox(height: 8),
      ],
    );
  }

  // ───────────────────────────── CONTACT CTA ─────────────────────────────

  Widget _buildContactCTA(BuildContext context) {
    return Container(
      margin: const EdgeInsets.fromLTRB(16, 8, 16, 16),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF25D366), Color(0xFF128C7E)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF25D366).withValues(alpha: 0.3),
            blurRadius: 16,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Need Help?',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  'Chat with our support team instantly',
                  style: TextStyle(
                    color: Colors.white.withValues(alpha: 0.9),
                    fontSize: 13,
                  ),
                ),
                const SizedBox(height: 12),
                GestureDetector(
                  onTap: () => context.push('/live-chat'),
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 8,
                    ),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: const Text(
                      'Start Chat',
                      style: TextStyle(
                        color: Color(0xFF128C7E),
                        fontWeight: FontWeight.w600,
                        fontSize: 13,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
          Icon(
            Icons.headset_mic_rounded,
            color: Colors.white.withValues(alpha: 0.3),
            size: 64,
          ),
        ],
      ),
    );
  }

  // ───────────────────────────── WHY CHOOSE US ─────────────────────────────

  Widget _buildWhyChooseUs(BuildContext context) {
    final features = [
      const _FeatureData(
        icon: Icons.verified_outlined,
        title: 'RERA Registered',
        description: 'All projects are RERA approved',
        color: Color(0xFF4CAF50),
      ),
      const _FeatureData(
        icon: Icons.security_outlined,
        title: 'Secure Investment',
        description: 'Legal verification of all plots',
        color: Color(0xFF2196F3),
      ),
      const _FeatureData(
        icon: Icons.location_city_outlined,
        title: 'Prime Locations',
        description: 'Best locations in Gorakhpur & beyond',
        color: Color(0xFFFF9800),
      ),
      const _FeatureData(
        icon: Icons.support_agent_outlined,
        title: '24/7 Support',
        description: 'Dedicated customer service',
        color: Color(0xFF9C27B0),
      ),
    ];

    return Column(
      children: [
        AppWidgets.sectionHeader(
          title: 'Why Choose Us',
          subtitle: 'Trusted by 1000+ families',
        ),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              childAspectRatio: 0.95,
              crossAxisSpacing: 12,
              mainAxisSpacing: 12,
            ),
            itemCount: features.length,
            itemBuilder: (context, index) {
              final feature = features[index];
              return Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: const Color(0xFFE8DFD0), width: 1.2),
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xFF0B194C).withValues(alpha: 0.04),
                      blurRadius: 10,
                      offset: const Offset(0, 3),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: feature.color.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Icon(feature.icon, color: feature.color, size: 24),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      feature.title,
                      style: const TextStyle(
                        fontWeight: FontWeight.w800,
                        color: AppTheme.primaryColor,
                        fontSize: 13,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      feature.description,
                      style: const TextStyle(
                        fontSize: 11,
                        color: Color(0xFF64748B),
                        fontWeight: FontWeight.w500,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  // ───────────────────────────── HELPERS ─────────────────────────────

  static String _formatNumber(double value) {
    if (value >= 1000) {
      return '${(value / 1000).toStringAsFixed(value >= 10000 ? 0 : 1)}K';
    }
    return value.toStringAsFixed(0);
  }

  static String _formatLargeNumber(double value) {
    if (value >= 10000000) {
      return '${(value / 10000000).toStringAsFixed(2)} Cr';
    } else if (value >= 100000) {
      return '${(value / 100000).toStringAsFixed(2)} L';
    } else if (value >= 1000) {
      return '${(value / 1000).toStringAsFixed(1)} K';
    }
    return value.toStringAsFixed(0);
  }

  static double _parsePlotArea(Map<String, dynamic> plot) {
    final v = plot['area_sqft'] ?? plot['areaSqft'] ?? plot['area'];
    if (v == null) return 0;
    if (v is num) return v.toDouble();
    return double.tryParse(v.toString()) ?? 0;
  }

  static double _parsePlotPrice(Map<String, dynamic> plot) {
    final v =
        plot['total_price'] ??
        plot['totalPrice'] ??
        plot['base_price'] ??
        plot['basePrice'] ??
        plot['price'];
    if (v == null) return 0;
    if (v is num) return v.toDouble();
    return double.tryParse(v.toString()) ?? 0;
  }
}

// ───────────────────────────── MODELS ─────────────────────────────

class _QuickAction {
  final IconData icon;
  final String label;
  final String route;
  final Gradient gradient;

  const _QuickAction({
    required this.icon,
    required this.label,
    required this.route,
    required this.gradient,
  });
}

class _ToolItem {
  final IconData icon;
  final String label;
  final String route;
  final Color color;
  const _ToolItem(this.icon, this.label, this.route, this.color);
}

class _FeatureData {
  final IconData icon;
  final String title;
  final String description;
  final Color color;

  const _FeatureData({
    required this.icon,
    required this.title,
    required this.description,
    required this.color,
  });
}

class _StatData {
  final String value;
  final String label;
  final IconData icon;
  final Color color;
  const _StatData(this.value, this.label, this.icon, this.color);
}

class _TestimonialData {
  final String name;
  final String role;
  final String text;
  final int rating;
  const _TestimonialData({
    required this.name,
    required this.role,
    required this.text,
    required this.rating,
  });
}

// ───────────────────────────── WIDGETS ─────────────────────────────

class _QuickActionWidget extends StatefulWidget {
  final _QuickAction action;

  const _QuickActionWidget({required this.action});

  @override
  State<_QuickActionWidget> createState() => _QuickActionWidgetState();
}

class _QuickActionWidgetState extends State<_QuickActionWidget>
    with SingleTickerProviderStateMixin {
  late final AnimationController _pressController;
  late final Animation<double> _scaleAnimation;

  @override
  void initState() {
    super.initState();
    _pressController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 120),
      reverseDuration: const Duration(milliseconds: 200),
    );
    _scaleAnimation = Tween<double>(begin: 1.0, end: 0.92).animate(
      CurvedAnimation(parent: _pressController, curve: Curves.easeInOut),
    );
  }

  @override
  void dispose() {
    _pressController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTapDown: (_) => _pressController.forward(),
      onTapUp: (_) {
        _pressController.reverse();
        context.push(widget.action.route);
      },
      onTapCancel: () => _pressController.reverse(),
      child: ScaleTransition(
        scale: _scaleAnimation,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 60,
              height: 60,
              decoration: BoxDecoration(
                gradient: widget.action.gradient,
                shape: BoxShape.circle,
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.15),
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Icon(widget.action.icon, color: Colors.white, size: 28),
            ),
            const SizedBox(height: 8),
            Text(
              widget.action.label,
              style: const TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w700,
                color: AppTheme.primaryColor,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _NotificationBell extends StatelessWidget {
  const _NotificationBell({required this.context});

  final BuildContext context;

  @override
  Widget build(BuildContext context) {
    return IconButton(
      onPressed: () => context.push('/notifications'),
      icon: Stack(
        clipBehavior: Clip.none,
        children: [
          const Icon(
            Icons.notifications_outlined,
            color: AppTheme.primaryColor,
            size: 26,
          ),
          Positioned(
            right: 2,
            top: 2,
            child: Container(
              width: 10,
              height: 10,
              decoration: BoxDecoration(
                color: AppTheme.errorColor,
                shape: BoxShape.circle,
                border: Border.all(color: Colors.white, width: 1.5),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _ProfileAvatar extends StatelessWidget {
  const _ProfileAvatar({required this.context});

  final BuildContext context;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () => context.push('/profile'),
      child: Container(
        width: 38,
        height: 38,
        decoration: BoxDecoration(
          color: Colors.white,
          shape: BoxShape.circle,
          border: Border.all(
            color: const Color(0xFFE8DFD0),
            width: 1.5,
          ),
          boxShadow: [
            BoxShadow(
              color: const Color(0xFF0B194C).withValues(alpha: 0.05),
              blurRadius: 6,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: const Icon(
          Icons.person_outline,
          color: AppTheme.primaryColor,
          size: 22,
        ),
      ),
    );
  }
}

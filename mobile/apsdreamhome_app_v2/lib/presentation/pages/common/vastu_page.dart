import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:http/http.dart' as http;
import 'package:url_launcher/url_launcher.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class VastuPage extends StatefulWidget {
  const VastuPage({super.key});

  @override
  State<VastuPage> createState() => _VastuPageState();
}

class _VastuPageState extends State<VastuPage> {
  List<Map<String, dynamic>> _colonies = [];
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

      final response = await http
          .get(Uri.parse('$baseUrl/api/v2/mobile/colonies'))
          .timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true && data['data'] is List) {
          final colonies = (data['data'] as List)
              .cast<Map<String, dynamic>>()
              .where((c) => (c['vastu_compliant'] == true || c['vastu_verified'] == true))
              .toList();
          if (mounted) {
            setState(() {
              _colonies = colonies;
              _loading = false;
            });
          }
          return;
        }
      }
    } catch (_) {}

    // Fallback to mock data
    if (mounted) {
      setState(() {
        _colonies = _mockColonies;
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: MeshGradientBackground(
        colors: const [Color(0xFF1A237E), Color(0xFF4A148C), Color(0xFF6A1B9A)],
        child: SafeArea(
          child: _loading
              ? const Center(child: CircularProgressIndicator(color: Colors.white))
              : SingleChildScrollView(
                  child: Column(
                    children: [
                      _buildHero(),
                      _buildIntro(),
                      _buildPrinciples(),
                      _buildColonies(),
                      _buildCTA(),
                      _buildFAQ(),
                      const SizedBox(height: 40),
                    ],
                  ),
                ),
        ),
      ),
    );
  }

  Widget _buildHero() {
    return Container(
      padding: const EdgeInsets.fromLTRB(20, 40, 20, 30),
      child: Column(
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
                child: const Icon(Icons.arrow_back, color: Colors.white, size: 22),
              ),
            ),
          ),
          const SizedBox(height: 20),
          Container(
            width: 90,
            height: 90,
            decoration: BoxDecoration(
              gradient: const LinearGradient(colors: [Color(0xFF6A1B9A), Color(0xFF9C27B0)]),
              borderRadius: BorderRadius.circular(22),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF6A1B9A).withValues(alpha: 0.3),
                  blurRadius: 24,
                  offset: const Offset(0, 10),
                ),
              ],
            ),
            child: const Icon(Icons.compass_calibration_rounded, size: 44, color: Colors.white),
          ),
          const SizedBox(height: 20),
          ShaderMask(
            shaderCallback: (bounds) => const LinearGradient(
              colors: [Colors.white, Color(0xFFE1BEE7)],
            ).createShader(bounds),
            child: Text(
              'Vastu-Compliant Homes',
              style: Theme.of(context).textTheme.headlineLarge?.copyWith(
                color: Colors.white,
                fontWeight: FontWeight.w800,
                height: 1.1,
              ),
              textAlign: TextAlign.center,
            ),
          ),
          const SizedBox(height: 10),
          Text(
            'Harmony, prosperity & positive energy — ancient wisdom for modern living',
            style: Theme.of(context).textTheme.bodyLarge?.copyWith(color: Colors.white70),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 24),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              _ctaButton('Consult Expert', Icons.phone_rounded, AppTheme.warningColor, () => _launchPhone()),
              const SizedBox(width: 12),
              _ctaButton('View Colonies', Icons.apartment_rounded, Colors.white.withValues(alpha: 0.2), () => context.go('/colonies')),
            ],
          ),
        ],
      ),
    );
  }

  Widget _ctaButton(String label, IconData icon, Color bgColor, VoidCallback onTap) {
    return Expanded(
      child: SizedBox(
        height: 52,
        child: ElevatedButton.icon(
          onPressed: onTap,
          icon: Icon(icon, size: 20),
          label: Text(label, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15)),
          style: ElevatedButton.styleFrom(
            backgroundColor: bgColor,
            foregroundColor: bgColor == Colors.white.withValues(alpha: 0.2) ? Colors.white : AppTheme.primaryColor,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            side: bgColor == Colors.white.withValues(alpha: 0.2)
                ? BorderSide(color: Colors.white.withValues(alpha: 0.3))
                : BorderSide.none,
          ),
        ),
      ),
    );
  }

  Widget _buildIntro() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 30),
      child: GlassCard(
        padding: const EdgeInsets.all(20),
        opacity: 0.1,
        blur: 8,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  width: 48,
                  height: 48,
                  decoration: BoxDecoration(
                    color: AppTheme.accentColor.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(Icons.auto_awesome_rounded, color: AppTheme.accentColor, size: 24),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Text(
                    'Why Vastu Matters',
                    style: AppTheme.titleLarge.copyWith(color: Colors.white, fontWeight: FontWeight.w700),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            Text(
              'Vastu Shastra is the ancient Indian science of architecture that harmonizes buildings with natural forces. '
              'Our colonies are designed with Vastu principles to ensure:',
              style: TextStyle(color: Colors.white70, fontSize: 14, height: 1.6),
            ),
            const SizedBox(height: 16),
            ...[
              {'icon': Icons.wb_sunny_rounded, 'title': 'Positive Energy', 'desc': 'Optimal sunlight & airflow in every room'},
              {'icon': Icons.balance_rounded, 'title': 'Element Balance', 'desc': 'Five elements (earth, water, fire, air, space) in harmony'},
              {'icon': Icons.trending_up_rounded, 'title': 'Prosperity', 'desc': 'Wealth-attracting layouts & placements'},
              {'icon': Icons.psychology_rounded, 'title': 'Well-being', 'desc': 'Health, happiness & peace of mind'},
            ].map((item) => Padding(
              padding: const EdgeInsets.only(bottom: 12),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    width: 36,
                    height: 36,
                    decoration: BoxDecoration(
                      color: AppTheme.accentColor.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Icon(item['icon'] as IconData, color: AppTheme.accentColor, size: 18),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          item['title'] as String,
                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 13),
                        ),
                        Text(
                          item['desc'] as String,
                          style: TextStyle(color: Colors.white54, fontSize: 11),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            )).toList(),
          ],
        ),
      ),
    );
  }

  Widget _buildPrinciples() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 30),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Core Vastu Principles', style: AppTheme.titleLarge.copyWith(color: Colors.white, fontWeight: FontWeight.w700)),
          const SizedBox(height: 8),
          Text('Fundamental guidelines applied in all our projects', style: TextStyle(color: Colors.white70, fontSize: 14)),
          const SizedBox(height: 20),
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              childAspectRatio: 1.1,
              crossAxisSpacing: 14,
              mainAxisSpacing: 14,
            ),
            itemCount: _principles.length,
            itemBuilder: (context, index) {
              final p = _principles[index] as Map<String, dynamic>;
              return GlassCard(
                padding: const EdgeInsets.all(16),
                opacity: 0.1,
                blur: 8,
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Container(
                      width: 56,
                      height: 56,
                      decoration: BoxDecoration(
                        color: (p['color'] as Color).withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: Icon(p['icon'] as IconData, color: p['color'] as Color, size: 28),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      p['title'] as String,
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 14),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 6),
                    Text(
                      p['desc'] as String,
                      style: TextStyle(color: Colors.white.withValues(alpha: 0.6), fontSize: 11),
                      textAlign: TextAlign.center,
                    ),
                  ],
                ),
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildColonies() {
    if (_colonies.isEmpty) {
      return Padding(
        padding: const EdgeInsets.fromLTRB(20, 0, 20, 30),
        child: GlassCard(
          padding: const EdgeInsets.all(20),
          opacity: 0.1,
          blur: 8,
          child: Column(
            children: [
              Icon(Icons.construction_rounded, size: 48, color: Colors.white30),
              const SizedBox(height: 12),
              Text('Vastu-Compliant Colonies', style: AppTheme.titleLarge.copyWith(color: Colors.white, fontWeight: FontWeight.w700)),
              const SizedBox(height: 8),
              Text('Our Vastu-verified colonies are coming soon. Check back later!', style: TextStyle(color: Colors.white54, fontSize: 13), textAlign: TextAlign.center),
            ],
          ),
        ),
      );
    }

    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 30),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('Vastu-Compliant Colonies', style: AppTheme.titleLarge.copyWith(color: Colors.white, fontWeight: FontWeight.w700)),
              TextButton(
                onPressed: () => context.go('/colonies'),
                child: Text('View All', style: TextStyle(color: AppTheme.accentColor, fontWeight: FontWeight.w600)),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text('Projects designed with Vastu principles for harmonious living', style: TextStyle(color: Colors.white70, fontSize: 14)),
          const SizedBox(height: 20),
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              childAspectRatio: 0.85,
              crossAxisSpacing: 14,
              mainAxisSpacing: 14,
            ),
            itemCount: _colonies.length > 4 ? 4 : _colonies.length,
            itemBuilder: (context, index) {
              final colony = _colonies[index] as Map<String, dynamic>;
              final imgUrl = (colony['image_path'] ?? colony['image'] ?? '').toString();
              final isUrl = imgUrl.startsWith('http');

              return GlassCard(
                padding: EdgeInsets.zero,
                opacity: 0.1,
                blur: 8,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    ClipRRect(
                      borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                      child: Stack(
                        children: [
                          isUrl
                              ? Image.network(imgUrl, height: 140, width: double.infinity, fit: BoxFit.cover,
                                  errorBuilder: (_, _, _) => _colonyPlaceholder())
                              : _colonyPlaceholder(),
                          Positioned(
                            top: 8,
                            right: 8,
                            child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                              decoration: BoxDecoration(
                                color: AppTheme.warningColor.withValues(alpha: 0.9),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(Icons.verified_rounded, size: 10, color: Colors.white),
                                  const SizedBox(width: 3),
                                  Text(
                                    'Vastu Verified',
                                    style: const TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.w600),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    Padding(
                      padding: const EdgeInsets.all(12),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            colony['name']?.toString() ?? 'Colony',
                            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 13),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                          const SizedBox(height: 4),
                          Row(
                            children: [
                              Icon(Icons.location_on_rounded, size: 11, color: Colors.white54),
                              const SizedBox(width: 4),
                              Expanded(
                                child: Text(
                                  colony['location']?.toString() ?? '',
                                  style: TextStyle(color: Colors.white54, fontSize: 10),
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
                                colony['starting_price'] != null
                                    ? '₹${_formatPrice(colony['starting_price'])}'
                                    : 'Contact Us',
                                style: const TextStyle(color: AppTheme.accentColor, fontWeight: FontWeight.w700, fontSize: 13),
                              ),
                              InkWell(
                                onTap: () => context.go('/colony-detail/${colony['id'] ?? colony['slug'] ?? index}'),
                                child: Text(
                                  'View Details',
                                  style: TextStyle(color: AppTheme.accentColor.withValues(alpha: 0.8), fontSize: 11, fontWeight: FontWeight.w600),
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
            },
          ),
        ],
      ),
    );
  }

  Widget _colonyPlaceholder() {
    return Container(
      height: 140,
      width: double.infinity,
      color: Colors.grey.shade800,
      child: const Center(child: Icon(Icons.apartment_rounded, size: 40, color: Colors.white30)),
    );
  }

  Widget _buildCTA() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 30),
      child: GlassCard(
        padding: const EdgeInsets.all(24),
        opacity: 0.15,
        blur: 12,
        child: Column(
          children: [
            Icon(Icons.support_agent_rounded, size: 40, color: AppTheme.accentColor),
            const SizedBox(height: 12),
            Text(
              'Need Vastu Consultation?',
              style: AppTheme.titleLarge.copyWith(color: Colors.white, fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 8),
            Text(
              'Our Vastu experts can help you choose the perfect plot in our Vastu-compliant colonies.',
              style: TextStyle(color: Colors.white70, fontSize: 13),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 20),
            Row(
              children: [
                Expanded(child: _ctaButton('Call Now', Icons.phone_rounded, AppTheme.warningColor, () => _launchPhone())),
                const SizedBox(width: 12),
                Expanded(child: _ctaButton('WhatsApp', Icons.chat_rounded, Colors.white.withValues(alpha: 0.2), () => _launchWhatsApp())),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFAQ() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Vastu FAQs', style: AppTheme.titleLarge.copyWith(color: Colors.white, fontWeight: FontWeight.w700)),
          const SizedBox(height: 8),
          Text('Common questions about Vastu compliance', style: TextStyle(color: Colors.white70, fontSize: 14)),
          const SizedBox(height: 16),
          ..._faqs.asMap().entries.map((entry) {
            final index = entry.key;
            final faq = entry.value;
            return Padding(
              padding: const EdgeInsets.only(bottom: 10),
              child: GlassCard(
                padding: EdgeInsets.zero,
                opacity: 0.08,
                blur: 6,
                child: ExpansionTile(
                  tilePadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                  iconColor: AppTheme.accentColor,
                  collapsedIconColor: Colors.white70,
                  title: Text(
                    faq['question']?.toString() ?? '',
                    style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 13),
                  ),
                  children: [
                    Padding(
                      padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                      child: Text(
                        faq['answer']?.toString() ?? '',
                        style: TextStyle(color: Colors.white70, fontSize: 12, height: 1.5),
                      ),
                    ),
                  ],
                ),
              ),
            );
          }),
        ],
      ),
    );
  }

  Future<void> _launchPhone() async {
    await launchUrl(Uri.parse('tel:7007444842'));
  }

  Future<void> _launchWhatsApp() async {
    await launchUrl(Uri.parse('https://wa.me/917007444842'));
  }

  String _formatPrice(dynamic price) {
    final p = price is int ? price : int.tryParse('${price ?? 0}') ?? 0;
    if (p >= 10000000) {
      return '${(p / 10000000).toStringAsFixed(1)} Cr';
    } else if (p >= 100000) {
      return '${(p / 100000).toStringAsFixed(1)} L';
    }
    return p.toString();
  }

  static final _principles = [
    {'icon': Icons.explore_rounded, 'color': Color(0xFF6A1B9A), 'title': 'Direction & Orientation', 'desc': 'Main entrance, rooms & kitchen aligned with cardinal directions'},
    {'icon': Icons.crop_free_rounded, 'color': Color(0xFFE91E63), 'title': 'Plot Shape & Slope', 'desc': 'Square/rectangular plots with NE-SW slope for prosperity'},
    {'icon': Icons.balance_rounded, 'color': Color(0xFF4CAF50), 'title': 'Five Elements Balance', 'desc': 'Earth, Water, Fire, Air, Space in correct zones'},
    {'icon': Icons.kitchen_rounded, 'color': Color(0xFFFF6F00), 'title': 'Kitchen in SE (Agni)', 'desc': 'Fire element placement for health & prosperity'},
    {'icon': Icons.bedroom_parent_rounded, 'color': Color(0xFF2979FF), 'title': 'Master Bedroom SW', 'desc': 'Stability, restful sleep & authority for head of family'},
    {'icon': Icons.water_drop_rounded, 'color': Color(0xFF00BCD4), 'title': 'Water in NE (Ishan)', 'desc': 'Underground tank, borewell & drainage in northeast'},
    {'icon': Icons.wc_rounded, 'color': Color(0xFF795548), 'title': 'Toilets in NW/SE', 'desc': 'Avoid NE & SW; proper ventilation & slope'},
    {'icon': Icons.park_rounded, 'color': Color(0xFF8BC34A), 'title': 'Open Space in NE', 'desc': 'Gardens, lawns & water bodies in northeast for positivity'},
  ];

  static const _faqs = [
    {
      'question': 'Is Vastu necessary for property purchase?',
      'answer': 'While not mandatory, Vastu compliance brings peace, prosperity, and positive energy to your home and life. Many buyers prefer Vastu-compliant properties for long-term well-being.',
    },
    {
      'question': 'Can you modify an existing property for Vastu?',
      'answer': 'Yes, simple remedies like adjusting furniture placement, colors, mirror positions, and element balance can correct Vastu issues without major structural changes.',
    },
    {
      'question': 'Which direction is best for main entrance?',
      'answer': 'North, East, or Northeast are considered most auspicious for main entrances as they welcome positive energy and sunlight.',
    },
    {
      'question': 'What if my plot is not perfectly rectangular?',
      'answer': 'Irregular plots can be corrected with Vastu remedies like pyramids, mirrors, plants, and color therapy. Our experts provide plot-specific solutions.',
    },
    {
      'question': 'Do all your colonies follow Vastu?',
      'answer': 'Yes, all APS Dream Home colonies are planned with Vastu principles — from plot orientation to road layout, water bodies, and green spaces.',
    },
  ];

  static const _mockColonies = [
    {
      'id': 1,
      'name': 'Suryoday Colony',
      'location': 'Gorakhpur, UP',
      'starting_price': 2500000,
      'image_path': 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=600&h=400&q=80',
      'vastu_compliant': true,
    },
    {
      'id': 2,
      'name': 'Braj Radha Nagri',
      'location': 'Gorakhpur, UP',
      'starting_price': 3200000,
      'image_path': 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=600&h=400&q=80',
      'vastu_compliant': true,
    },
    {
      'id': 3,
      'name': 'Raghunath Nagri',
      'location': 'Gorakhpur, UP',
      'starting_price': 4500000,
      'image_path': 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=600&h=400&q=80',
      'vastu_compliant': true,
    },
    {
      'id': 4,
      'name': 'Budh Bihar Township',
      'location': 'Gorakhpur, UP',
      'starting_price': 1800000,
      'image_path': 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?auto=format&fit=crop&w=600&h=400&q=80',
      'vastu_compliant': true,
    },
  ];
}
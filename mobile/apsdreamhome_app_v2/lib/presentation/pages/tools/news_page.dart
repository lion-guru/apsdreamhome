import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import '../../../core/constants/app_constants.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class NewsPage extends StatefulWidget {
  const NewsPage({super.key});

  @override
  State<NewsPage> createState() => _NewsPageState();
}

class _NewsPageState extends State<NewsPage> {
  int _selectedTab = 0;
  List<Map<String, dynamic>> _allPosts = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadPosts();
  }

  Future<void> _loadPosts() async {
    try {
      AppConstants.initBaseUrl();
      final url = '${AppConstants.baseUrl}/api/v2/mobile/blog';
      final resp = await http
          .get(Uri.parse(url))
          .timeout(const Duration(seconds: 10));
      if (resp.statusCode == 200) {
        final data = jsonDecode(resp.body);
        if (data['success'] == true && data['data'] is List) {
          final posts = (data['data'] as List).cast<Map<String, dynamic>>();
          setState(() {
            _allPosts = posts;
            _loading = false;
          });
          return;
        }
      }
    } catch (_) {}
    // Fallback to mock data
    setState(() {
      _allPosts = _mockNews
          .map(
            (n) => {
              'title': n.title,
              'excerpt': n.excerpt,
              'category': n.category,
              'created_at': n.date,
              'reading_time': n.readTime,
              'views': n.views,
              'featured_image': '',
            },
          )
          .toList();
      _loading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    final newsItems = _selectedTab == 0
        ? _allPosts
        : _selectedTab == 1
        ? _allPosts
              .where(
                (p) =>
                    (p['category'] ?? '').toString().toLowerCase() == 'company',
              )
              .toList()
        : _allPosts
              .where(
                (p) =>
                    (p['category'] ?? '').toString().toLowerCase().contains(
                      'policy',
                    ) ||
                    (p['category'] ?? '').toString().toLowerCase().contains(
                      'regulatory',
                    ),
              )
              .toList();

    return Scaffold(
      appBar: AppBar(
        title: const Text('News & Updates'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
      ),
      body: Column(
        children: [
          _buildSearchBar(),
          _buildTabBar(),
          Expanded(
            child: _loading
                ? const Center(
                    child: CircularProgressIndicator(
                      color: AppTheme.primaryColor,
                    ),
                  )
                : newsItems.isEmpty
                ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.article_outlined,
                          size: 64,
                          color: Colors.grey.shade400,
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'No news found',
                          style: TextStyle(
                            fontSize: 16,
                            color: Colors.grey.shade600,
                          ),
                        ),
                      ],
                    ),
                  )
                : SingleChildScrollView(
                    padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: newsItems
                          .map((item) => _buildNewsCard(item))
                          .toList(),
                    ),
                  ),
          ),
        ],
      ),
    );
  }

  Widget _buildSearchBar() {
    return Container(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
      child: TextField(
        decoration: InputDecoration(
          hintText: 'Search news & updates...',
          hintStyle: TextStyle(color: Colors.grey.shade500, fontSize: 14),
          prefixIcon: Icon(
            Icons.search_rounded,
            color: Colors.grey.shade500,
            size: 22,
          ),
          filled: true,
          fillColor: Colors.grey.withValues(alpha: 0.1),
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(14),
            borderSide: BorderSide.none,
          ),
          contentPadding: const EdgeInsets.symmetric(vertical: 12),
        ),
      ),
    );
  }

  Widget _buildTabBar() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Row(
        children: [
          _buildTab('News', 0),
          const SizedBox(width: 8),
          _buildTab('Company', 1),
          const SizedBox(width: 8),
          _buildTab('Regulatory', 2),
        ],
      ),
    );
  }

  Widget _buildTab(String label, int index) {
    final isSelected = _selectedTab == index;
    return GestureDetector(
      onTap: () => setState(() => _selectedTab = index),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        decoration: BoxDecoration(
          color: isSelected
              ? AppTheme.primaryColor
              : Colors.grey.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isSelected
                ? AppTheme.primaryColor
                : Colors.grey.withValues(alpha: 0.2),
          ),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 13,
            fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
            color: isSelected ? Colors.white : Colors.grey.shade700,
          ),
        ),
      ),
    );
  }

  Widget _buildNewsCard(Map<String, dynamic> item) {
    final categoryColors = {
      'Company': const Color(0xFF1565C0),
      'Market': const Color(0xFFE65100),
      'Project': const Color(0xFF2E7D32),
      'Policy': const Color(0xFF6A1B9A),
      'Legal': const Color(0xFF37474F),
      'Finance': const Color(0xFF00838F),
      'Launch': const Color(0xFFC62828),
      'Event': const Color(0xFF00838F),
      'Achievement': const Color(0xFFF9A825),
      'Regulatory': const Color(0xFF37474F),
    };

    final category = (item['category'] ?? '').toString();
    final catColor = categoryColors[category] ?? Colors.grey;

    String dateStr = '';
    if (item['created_at'] != null &&
        item['created_at'].toString().isNotEmpty) {
      try {
        final dt = DateTime.parse(item['created_at'].toString());
        dateStr = '${dt.day} ${_monthName(dt.month)} ${dt.year}';
      } catch (_) {
        dateStr = item['created_at'].toString();
      }
    }

    return Padding(
      padding: const EdgeInsets.only(bottom: 14),
      child: GlassCard(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical: 4,
                  ),
                  decoration: BoxDecoration(
                    color: catColor.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Text(
                    category.isNotEmpty ? category : 'News',
                    style: TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.w600,
                      color: catColor,
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Text(
                  dateStr,
                  style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                ),
                const Spacer(),
                Icon(
                  Icons.schedule_rounded,
                  size: 13,
                  color: Colors.grey.shade500,
                ),
                const SizedBox(width: 3),
                Text(
                  '${item['reading_time'] ?? 5} min read',
                  style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                ),
              ],
            ),
            const SizedBox(height: 10),
            Text(
              (item['title'] ?? '').toString(),
              style: const TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.w600,
                height: 1.3,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              (item['excerpt'] ?? '').toString(),
              style: TextStyle(
                fontSize: 12,
                color: Colors.grey.shade600,
                height: 1.4,
              ),
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
            const SizedBox(height: 10),
            Row(
              children: [
                Icon(
                  Icons.remove_red_eye_rounded,
                  size: 14,
                  color: Colors.grey.shade500,
                ),
                const SizedBox(width: 4),
                Text(
                  '${item['views'] ?? 0} views',
                  style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                ),
                const Spacer(),
                const Text(
                  'Read more',
                  style: TextStyle(
                    fontSize: 12,
                    color: AppTheme.primaryColor,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const Icon(
                  Icons.arrow_forward_rounded,
                  size: 14,
                  color: AppTheme.primaryColor,
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  String _monthName(int m) {
    const months = [
      '',
      'Jan',
      'Feb',
      'Mar',
      'Apr',
      'May',
      'Jun',
      'Jul',
      'Aug',
      'Sep',
      'Oct',
      'Nov',
      'Dec',
    ];
    return months[m];
  }
}

class _NewsItem {
  final String category;
  final String date;
  final String readTime;
  final String title;
  final String excerpt;
  final int views;
  const _NewsItem({
    required this.category,
    required this.date,
    required this.readTime,
    required this.title,
    required this.excerpt,
    required this.views,
  });
}

const _mockNews = [
  _NewsItem(
    category: 'Market',
    date: '8 Jul 2026',
    readTime: '3 min',
    title: 'Gorakhpur Real Estate Market Update Q2 2026',
    excerpt:
        'Residential colonies in Gorakhpur have witnessed steady price growth driven by infrastructure upgrades.',
    views: 1245,
  ),
  _NewsItem(
    category: 'Project',
    date: '6 Jul 2026',
    readTime: '2 min',
    title: 'Budh Bihar Township Reaches 40% Construction Milestone',
    excerpt:
        'The affordable housing project near Sector 103 is on track for December 2027 delivery.',
    views: 892,
  ),
  _NewsItem(
    category: 'Company',
    date: '4 Jul 2026',
    readTime: '4 min',
    title: 'APS Dream Home Launches Customer Referral Program 2.0',
    excerpt:
        'New tiered rewards system offers up to ₹5,000 per successful referral.',
    views: 2103,
  ),
  _NewsItem(
    category: 'Policy',
    date: '1 Jul 2026',
    readTime: '5 min',
    title: 'RERA Compliance Deadline Extended — What Homebuyers Should Know',
    excerpt:
        'UP RERA extends compliance deadline for ongoing projects. Key implications for buyers.',
    views: 1567,
  ),
  _NewsItem(
    category: 'Project',
    date: '28 Jun 2026',
    readTime: '2 min',
    title: 'Raghunath Nagri Commercial Plaza Now Open for Booking',
    excerpt:
        '12 premium commercial shops in the heart of Gorakhpur with metro connectivity.',
    views: 734,
  ),
  _NewsItem(
    category: 'Regulatory',
    date: '7 Jul 2026',
    readTime: '4 min',
    title: 'UP Government Revises Circle Rates — Impact on Property Prices',
    excerpt:
        'New circle rates effective August 2026 may increase property registration costs by 8-12%.',
    views: 2890,
  ),
];

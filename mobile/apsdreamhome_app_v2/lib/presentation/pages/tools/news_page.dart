import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class NewsPage extends StatefulWidget {
  const NewsPage({super.key});

  @override
  State<NewsPage> createState() => _NewsPageState();
}

class _NewsPageState extends State<NewsPage> {
  int _selectedTab = 0;

  @override
  Widget build(BuildContext context) {
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
            child: SingleChildScrollView(
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (_selectedTab == 0) ..._buildNewsFeed(),
                  if (_selectedTab == 1) ..._buildAnnouncements(),
                  if (_selectedTab == 2) ..._buildRegulatory(),
                ],
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
          _buildTab('Announcements', 1),
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

  List<Widget> _buildNewsFeed() {
    return _newsItems.map((item) => _buildNewsCard(item)).toList();
  }

  List<Widget> _buildAnnouncements() {
    return _announcements.map((item) => _buildNewsCard(item)).toList();
  }

  List<Widget> _buildRegulatory() {
    return _regulatoryItems.map((item) => _buildNewsCard(item)).toList();
  }

  Widget _buildNewsCard(_NewsItem item) {
    final categoryColors = {
      'Company': const Color(0xFF1565C0),
      'Market': const Color(0xFFE65100),
      'Project': const Color(0xFF2E7D32),
      'Policy': const Color(0xFF6A1B9A),
      'Launch': const Color(0xFFC62828),
      'Event': const Color(0xFF00838F),
      'Achievement': const Color(0xFFF9A825),
      'Regulatory': const Color(0xFF37474F),
    };

    final catColor = categoryColors[item.category] ?? Colors.grey;

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
                    item.category,
                    style: TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.w600,
                      color: catColor,
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Text(
                  item.date,
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
                  item.readTime,
                  style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                ),
              ],
            ),
            const SizedBox(height: 10),
            Text(
              item.title,
              style: const TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.w600,
                height: 1.3,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              item.excerpt,
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
                  '${item.views} views',
                  style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                ),
                const Spacer(),
                Text(
                  'Read more',
                  style: TextStyle(
                    fontSize: 12,
                    color: AppTheme.primaryColor,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                Icon(
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

const _newsItems = [
  _NewsItem(
    category: 'Market',
    date: '8 Jul 2026',
    readTime: '3 min read',
    title: 'Gurgaon Real Estate Sees 23% Price Appreciation in Q2 2026',
    excerpt:
        'Top residential colonies in Gurgaon have witnessed significant price growth driven by infrastructure upgrades and new metro corridors.',
    views: 1245,
  ),
  _NewsItem(
    category: 'Project',
    date: '6 Jul 2026',
    readTime: '2 min read',
    title: 'Budh Bihar Township Reaches 40% Construction Milestone',
    excerpt:
        'The affordable housing project near Sector 103 is on track for December 2027 delivery with 200+ units already booked.',
    views: 892,
  ),
  _NewsItem(
    category: 'Company',
    date: '4 Jul 2026',
    readTime: '4 min read',
    title: 'APS Dream Home Launches Customer Referral Program 2.0',
    excerpt:
        'New tiered rewards system offers up to ₹5,000 per successful referral with Platinum tier benefits for top referrers.',
    views: 2103,
  ),
  _NewsItem(
    category: 'Market',
    date: '1 Jul 2026',
    readTime: '5 min read',
    title: 'RERA Compliance Deadline Extended — What Homebuyers Should Know',
    excerpt:
        'Haryana RERA extends compliance deadline for ongoing projects. Key implications for buyers and developers explained.',
    views: 1567,
  ),
  _NewsItem(
    category: 'Project',
    date: '28 Jun 2026',
    readTime: '2 min read',
    title: 'Raghunath Nagri Commercial Plaza Now Open for Booking',
    excerpt:
        '12 premium commercial shops in the heart of Sector 37C with metro connectivity and high foot traffic.',
    views: 734,
  ),
  _NewsItem(
    category: 'Event',
    date: '25 Jun 2026',
    readTime: '3 min read',
    title: 'APS Dream Home Hosts Property Expo — Record 500+ Visitors',
    excerpt:
        'One-day expo at Leela Ambience saw 45+ on-the-spot bookings with special discounts and flexible payment plans.',
    views: 1890,
  ),
];

const _announcements = [
  _NewsItem(
    category: 'Company',
    date: '9 Jul 2026',
    readTime: '1 min read',
    title: 'Holiday Notice: Office Closed on 15 July 2026',
    excerpt:
        'All APS Dream Home offices will remain closed on 15th July on account of local festival. Regular operations resume 16 July.',
    views: 445,
  ),
  _NewsItem(
    category: 'Achievement',
    date: '5 Jul 2026',
    readTime: '2 min read',
    title: 'APS Dream Home Crosses ₹100 Crore Sales Milestone',
    excerpt:
        'A historic achievement driven by our loyal customers, dedicated associates, and world-class project quality.',
    views: 3210,
  ),
  _NewsItem(
    category: 'Launch',
    date: '2 Jul 2026',
    readTime: '2 min read',
    title: 'New Mobile App v1.2 Released — Virtual Tours & RERA Lookup',
    excerpt:
        'Our updated mobile app now features virtual property tours, RERA compliance lookup, and 7 new financial calculators.',
    views: 1876,
  ),
  _NewsItem(
    category: 'Company',
    date: '29 Jun 2026',
    readTime: '1 min read',
    title: 'New Associate Training Program — Batch of July 2026',
    excerpt:
        'Enroll in our comprehensive 3-day training program covering sales, documentation, and customer handling. Free for all associates.',
    views: 567,
  ),
];

const _regulatoryItems = [
  _NewsItem(
    category: 'Policy',
    date: '7 Jul 2026',
    readTime: '4 min read',
    title:
        'Haryana Government Revises Circle Rates — Impact on Property Prices',
    excerpt:
        'New circle rates effective August 2026 may increase property registration costs by 8-12% across Gurgaon districts.',
    views: 2890,
  ),
  _NewsItem(
    category: 'Regulatory',
    date: '3 Jul 2026',
    readTime: '3 min read',
    title: 'GST on Under-Construction Properties — Current Rates Explained',
    excerpt:
        'Updated GST rates for affordable (1%) and premium (5%) housing with input tax credit eligibility criteria.',
    views: 1456,
  ),
  _NewsItem(
    category: 'Policy',
    date: '30 Jun 2026',
    readTime: '5 min read',
    title: 'PM Awas Yojana — CLSS Beneficiary List Expanded',
    excerpt:
        'Credit Linked Subsidy Scheme now includes middle-income groups up to ₹18L annual income. Check your eligibility.',
    views: 2234,
  ),
  _NewsItem(
    category: 'Regulatory',
    date: '26 Jun 2026',
    readTime: '3 min read',
    title: 'New NOC Requirements for Property Registration in Haryana',
    excerpt:
        'Updated documentation requirements include mandatory No Objection Certificate from local municipal corporation for all transactions.',
    views: 1678,
  ),
];

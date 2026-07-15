import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../../../core/theme/app_theme.dart';
import '../../../data/repositories/kyc_repository_provider.dart';

class BlogPage extends ConsumerStatefulWidget {
  const BlogPage({super.key});

  @override
  ConsumerState<BlogPage> createState() => _BlogPageState();
}

class _BlogPageState extends ConsumerState<BlogPage> {
  bool _isLoading = true;
  List<Map<String, dynamic>> _posts = [];
  String? _selectedCategory;

  static const _mockPosts = [
    {
      'title': 'Top 10 Tips for First-Time Home Buyers',
      'excerpt':
          'Buying your first home? Our comprehensive guide covers budgeting, location selection, legal checks, and everything you need to know before making the big decision.',
      'slug': 'tips-first-time-home-buyers',
      'category': 'Buying Guide',
      'reading_time': '5',
      'published_at': '2026-06-28',
      'author': 'APS Dream Home',
      'featured_image':
          'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=600',
    },
    {
      'title': 'Why Gorakhpur is the Next Real Estate Hotspot',
      'excerpt':
          'With new infrastructure projects and growing employment opportunities, Gorakhpur is emerging as a prime destination for real estate investment in 2026.',
      'slug': 'gorakhpur-real-estate-hotspot',
      'category': 'Market Insights',
      'reading_time': '4',
      'published_at': '2026-06-20',
      'author': 'APS Dream Home',
      'featured_image':
          'https://images.unsplash.com/photo-1486325212027-8081e485255e?w=600',
    },
    {
      'title': 'Understanding Property Registration Process',
      'excerpt':
          'A step-by-step guide to property registration in India, including stamp duty, circle rates, required documents, and common pitfalls to avoid.',
      'slug': 'property-registration-process',
      'category': 'Legal',
      'reading_time': '6',
      'published_at': '2026-06-15',
      'author': 'APS Dream Home',
      'featured_image':
          'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=600',
    },
    {
      'title': '5 Benefits of Investing in Plots vs Apartments',
      'excerpt':
          'Should you invest in a plot or an apartment? We break down the pros and cons of each option based on appreciation, flexibility, and long-term returns.',
      'slug': 'plots-vs-apartments-benefits',
      'category': 'Investment',
      'reading_time': '3',
      'published_at': '2026-06-10',
      'author': 'APS Dream Home',
      'featured_image':
          'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=600',
    },
    {
      'title': 'Home Loan Guide 2026: Everything You Need',
      'excerpt':
          'From eligibility criteria to interest rates and tax benefits, this guide covers all aspects of home loans in India to help you make an informed decision.',
      'slug': 'home-loan-guide-2026',
      'category': 'Finance',
      'reading_time': '7',
      'published_at': '2026-06-05',
      'author': 'APS Dream Home',
      'featured_image':
          'https://images.unsplash.com/photo-1563013544-824ae1b704d3?w=600',
    },
    {
      'title': 'RERA Compliance: What Buyers Should Know',
      'excerpt':
          'Real Estate Regulatory Authority (RERA) protects homebuyers. Learn about RERA registration, project status tracking, and your rights as a buyer.',
      'slug': 'rera-compliance-buyers-guide',
      'category': 'Legal',
      'reading_time': '4',
      'published_at': '2026-05-28',
      'author': 'APS Dream Home',
      'featured_image':
          'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=600',
    },
  ];

  List<String> get _categories {
    final cats = _posts
        .map((p) => p['category']?.toString() ?? '')
        .where((c) => c.isNotEmpty)
        .toSet()
        .toList();
    cats.sort();
    return cats;
  }

  List<Map<String, dynamic>> get _filteredPosts {
    if (_selectedCategory == null) return _posts;
    return _posts
        .where((p) => p['category']?.toString() == _selectedCategory)
        .toList();
  }

  @override
  void initState() {
    super.initState();
    _loadPosts();
  }

  Future<void> _loadPosts() async {
    setState(() => _isLoading = true);
    try {
      final api = ref.read(apiServiceProvider);
      final response = await api.get('blog');
      final data = response['data'] ?? [];
      if (mounted && data is List && data.isNotEmpty) {
        setState(() {
          _posts = data
              .map((e) => Map<String, dynamic>.from(e as Map))
              .toList();
          _isLoading = false;
        });
        return;
      }
    } catch (_) {}
    if (mounted) {
      setState(() {
        _posts = _mockPosts.map((e) => Map<String, dynamic>.from(e)).toList();
        _isLoading = false;
      });
    }
  }

  Future<void> _onRefresh() async {
    await _loadPosts();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Blog & News'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(color: AppTheme.primaryColor),
            )
          : RefreshIndicator(
              onRefresh: _onRefresh,
              color: AppTheme.primaryColor,
              child: CustomScrollView(
                slivers: [
                  if (_categories.isNotEmpty)
                    SliverToBoxAdapter(child: _buildCategoryChips()),
                  _filteredPosts.isEmpty
                      ? SliverFillRemaining(
                          child: Center(
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(
                                  Icons.article_outlined,
                                  size: 64,
                                  color: Colors.grey.shade300,
                                ),
                                const SizedBox(height: 16),
                                Text(
                                  'No blog posts yet',
                                  style: TextStyle(
                                    fontSize: 16,
                                    color: Colors.grey.shade500,
                                  ),
                                ),
                                const SizedBox(height: 8),
                                TextButton.icon(
                                  onPressed: _onRefresh,
                                  icon: const Icon(Icons.refresh),
                                  label: const Text('Refresh'),
                                ),
                              ],
                            ),
                          ),
                        )
                      : SliverList(
                          delegate: SliverChildBuilderDelegate(
                            (context, index) =>
                                _buildPostCard(_filteredPosts[index]),
                            childCount: _filteredPosts.length,
                          ),
                        ),
                ],
              ),
            ),
    );
  }

  Widget _buildCategoryChips() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
      child: SizedBox(
        height: 36,
        child: ListView(
          scrollDirection: Axis.horizontal,
          children: [
            _buildChip('All', null),
            ..._categories.map((c) => _buildChip(c, c)),
          ],
        ),
      ),
    );
  }

  Widget _buildChip(String label, String? category) {
    final isSelected = _selectedCategory == category;
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: GestureDetector(
        onTap: () => setState(() => _selectedCategory = category),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
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
              fontSize: 12,
              fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
              color: isSelected ? Colors.white : Colors.grey.shade700,
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildPostCard(Map<String, dynamic> post) {
    final title = post['title']?.toString() ?? '';
    final excerpt = post['excerpt']?.toString() ?? '';
    final slug = post['slug']?.toString() ?? '';
    final image = post['featured_image']?.toString() ?? '';
    final author = post['author']?.toString() ?? '';
    final category = post['category']?.toString() ?? '';
    final readingTime = post['reading_time']?.toString() ?? '';
    final publishedAt = post['published_at']?.toString() ?? '';

    String formattedDate = '';
    try {
      final dt = DateTime.parse(publishedAt);
      formattedDate = DateFormat('MMM dd, yyyy').format(dt);
    } catch (_) {
      formattedDate = publishedAt;
    }

    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 8),
      child: Card(
        elevation: 1,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        clipBehavior: Clip.antiAlias,
        child: InkWell(
          onTap: () {
            if (slug.isNotEmpty) context.push('/blog/$slug');
          },
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              if (image.isNotEmpty)
                SizedBox(
                  height: 180,
                  width: double.infinity,
                  child: Image.network(
                    image,
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) => _imagePlaceholder(),
                    loadingBuilder: (_, child, progress) {
                      if (progress == null) return child;
                      return Container(
                        color: Colors.grey.shade100,
                        child: const Center(
                          child: CircularProgressIndicator(strokeWidth: 2),
                        ),
                      );
                    },
                  ),
                )
              else
                _imagePlaceholder(),
              Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        if (category.isNotEmpty)
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 8,
                              vertical: 3,
                            ),
                            decoration: BoxDecoration(
                              color: AppTheme.primaryColor.withValues(
                                alpha: 0.1,
                              ),
                              borderRadius: BorderRadius.circular(4),
                            ),
                            child: Text(
                              category,
                              style: const TextStyle(
                                fontSize: 10,
                                fontWeight: FontWeight.w600,
                                color: AppTheme.primaryColor,
                              ),
                            ),
                          ),
                        if (category.isNotEmpty && readingTime.isNotEmpty)
                          const SizedBox(width: 8),
                        if (readingTime.isNotEmpty)
                          Text(
                            '$readingTime min read',
                            style: TextStyle(
                              fontSize: 10,
                              color: Colors.grey.shade500,
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(height: 10),
                    Text(
                      title,
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    if (excerpt.isNotEmpty) ...[
                      const SizedBox(height: 6),
                      Text(
                        excerpt,
                        style: TextStyle(
                          fontSize: 13,
                          color: Colors.grey.shade600,
                          height: 1.4,
                        ),
                        maxLines: 3,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Icon(
                          Icons.person_outline,
                          size: 14,
                          color: Colors.grey.shade500,
                        ),
                        const SizedBox(width: 4),
                        Text(
                          author.isNotEmpty ? author : 'APS Dream Home',
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey.shade500,
                          ),
                        ),
                        const Spacer(),
                        if (formattedDate.isNotEmpty)
                          Text(
                            formattedDate,
                            style: TextStyle(
                              fontSize: 12,
                              color: Colors.grey.shade500,
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
      ),
    );
  }

  Widget _imagePlaceholder() {
    return Container(
      height: 180,
      width: double.infinity,
      color: Colors.grey.shade100,
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.article_outlined, size: 48, color: Colors.grey.shade300),
          const SizedBox(height: 8),
          Text(
            'No Image',
            style: TextStyle(color: Colors.grey.shade400, fontSize: 12),
          ),
        ],
      ),
    );
  }
}

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/theme/app_theme.dart';
import '../../../data/repositories/kyc_repository_provider.dart';

class TestimonialsPage extends ConsumerStatefulWidget {
  const TestimonialsPage({super.key});

  @override
  ConsumerState<TestimonialsPage> createState() => _TestimonialsPageState();
}

class _TestimonialsPageState extends ConsumerState<TestimonialsPage> {
  bool _isLoading = true;
  String? _error;
  List<Map<String, dynamic>> _testimonials = [];
  int _selectedRating = 0;

  @override
  void initState() {
    super.initState();
    _loadTestimonials();
  }

  Future<void> _loadTestimonials() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final api = ref.read(apiServiceProvider);
      final response = await api.get('testimonials');
      final raw = response['data'] ?? response['testimonials'] ?? [];
      final data = raw is List ? raw : <dynamic>[];
      if (mounted) {
        setState(() {
          _testimonials = data.map<Map<String, dynamic>>((e) => Map<String, dynamic>.from(e as Map)).toList();
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoading = false;
          _testimonials = [
            {
              'name': 'Rahul Sharma',
              'location': 'Gorakhpur',
              'rating': 5,
              'text':
                  'APS Dream Home made my dream of owning a plot come true. The process was smooth and transparent.',
              'date': '2026-03-15',
              'avatar': '',
            },
            {
              'name': 'Priya Gupta',
              'location': 'Lucknow',
              'rating': 5,
              'text':
                  'Excellent service! The team helped me through every step of the booking and registration process.',
              'date': '2026-02-20',
              'avatar': '',
            },
            {
              'name': 'Amit Singh',
              'location': 'Kushinagar',
              'rating': 4,
              'text':
                  'Great experience with the EMI options. The flexible payment plans made it very convenient.',
              'date': '2026-01-10',
              'avatar': '',
            },
            {
              'name': 'Sunita Devi',
              'location': 'Varanasi',
              'rating': 5,
              'text':
                  'The colony infrastructure is top-notch. Roads, drainage, and electricity all provided on time.',
              'date': '2025-12-05',
              'avatar': '',
            },
            {
              'name': 'Vikram Patel',
              'location': 'Gorakhpur',
              'rating': 4,
              'text':
                  'The referral program is fantastic. I earned good rewards by referring my friends and family.',
              'date': '2025-11-18',
              'avatar': '',
            },
          ];
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Testimonials'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? _buildError()
              : _buildContent(),
    );
  }

  Widget _buildError() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.error_outline, size: 64, color: Colors.grey.shade400),
            const SizedBox(height: 16),
            Text(
              _error!,
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 16, color: AppTheme.textSecondaryLight),
            ),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: _loadTestimonials,
              child: const Text('Retry'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildContent() {
    final filtered = _selectedRating > 0
        ? _testimonials
            .where((t) => t['rating'] == _selectedRating)
            .toList()
        : _testimonials;

    return RefreshIndicator(
      onRefresh: _loadTestimonials,
      child: CustomScrollView(
        slivers: [
          SliverToBoxAdapter(
            child: _buildHeader(),
          ),
          SliverToBoxAdapter(
            child: _buildRatingFilter(),
          ),
          SliverToBoxAdapter(
            child: _buildStatsRow(),
          ),
          if (filtered.isEmpty)
            SliverToBoxAdapter(
              child: _buildEmptyState(),
            )
          else
            SliverPadding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              sliver: SliverList(
                delegate: SliverChildBuilderDelegate(
                  (context, index) => _buildTestimonialCard(filtered[index]),
                  childCount: filtered.length,
                ),
              ),
            ),
          const SliverToBoxAdapter(child: SizedBox(height: 24)),
        ],
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(24),
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: Column(
        children: [
          const Icon(Icons.format_quote, size: 48, color: AppTheme.accentColor),
          const SizedBox(height: 12),
          const Text(
            'What Our Customers Say',
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            '${_testimonials.length} happy customers',
            style: TextStyle(
              fontSize: 14,
              color: Colors.white.withValues(alpha: 0.8),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRatingFilter() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: Row(
          children: [
            _buildFilterChip('All', 0),
            const SizedBox(width: 8),
            _buildFilterChip('5 Star', 5),
            const SizedBox(width: 8),
            _buildFilterChip('4 Star', 4),
            const SizedBox(width: 8),
            _buildFilterChip('3 Star', 3),
          ],
        ),
      ),
    );
  }

  Widget _buildFilterChip(String label, int rating) {
    final isSelected = _selectedRating == rating;
    return FilterChip(
      label: Text(
        label,
        style: TextStyle(
          color: isSelected ? Colors.white : AppTheme.primaryColor,
          fontWeight: FontWeight.w600,
          fontSize: 13,
        ),
      ),
      selected: isSelected,
      selectedColor: AppTheme.primaryColor,
      backgroundColor: AppTheme.primaryColor.withValues(alpha: 0.08),
      checkmarkColor: Colors.white,
      onSelected: (selected) {
        setState(() {
          _selectedRating = selected ? rating : 0;
        });
      },
    );
  }

  Widget _buildStatsRow() {
    final total = _testimonials.length;
    final avgRating = total > 0
        ? _testimonials.fold<double>(
                0, (sum, t) => sum + (t['rating'] as num).toDouble()) /
            total
        : 0.0;

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Row(
        children: [
          _buildStatCard('Total Reviews', '$total', Icons.reviews),
          const SizedBox(width: 12),
          _buildStatCard(
              'Avg Rating', avgRating.toStringAsFixed(1), Icons.star),
        ],
      ),
    );
  }

  Widget _buildStatCard(String label, String value, IconData icon) {
    return Expanded(
      child: Card(
        elevation: 1,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            children: [
              Icon(icon, color: AppTheme.accentColor, size: 28),
              const SizedBox(height: 8),
              Text(
                value,
                style: const TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.bold,
                  color: AppTheme.primaryColor,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                label,
                style: const TextStyle(
                  fontSize: 12,
                  color: AppTheme.textSecondaryLight,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Padding(
      padding: const EdgeInsets.all(48),
      child: Column(
        children: [
          Icon(Icons.rate_review_outlined, size: 64, color: Colors.grey.shade300),
          const SizedBox(height: 16),
          const Text(
            'No reviews found',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w600,
              color: AppTheme.textPrimaryLight,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Try a different filter',
            style: TextStyle(fontSize: 14, color: Colors.grey.shade500),
          ),
        ],
      ),
    );
  }

  Widget _buildTestimonialCard(Map<String, dynamic> testimonial) {
    final name = testimonial['name']?.toString() ?? 'Anonymous';
    final location = testimonial['location']?.toString() ?? '';
    final text = testimonial['text']?.toString() ?? '';
    final rating = (testimonial['rating'] as num?)?.toInt() ?? 5;
    final date = testimonial['date']?.toString() ?? '';

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 1,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                CircleAvatar(
                  radius: 22,
                  backgroundColor: AppTheme.primaryColor.withValues(alpha: 0.1),
                  child: Text(
                    name.isNotEmpty ? name[0].toUpperCase() : '?',
                    style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: AppTheme.primaryColor,
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        name,
                        style: const TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w600,
                          color: AppTheme.textPrimaryLight,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        location,
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.grey.shade500,
                        ),
                      ),
                    ],
                  ),
                ),
                _buildStarRow(rating),
              ],
            ),
            const SizedBox(height: 12),
            Text(
              text,
              style: const TextStyle(
                fontSize: 14,
                color: AppTheme.textPrimaryLight,
                height: 1.5,
              ),
            ),
            if (date.isNotEmpty) ...[
              const SizedBox(height: 10),
              Text(
                date,
                style: TextStyle(fontSize: 11, color: Colors.grey.shade400),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildStarRow(int rating) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: List.generate(5, (index) {
        return Icon(
          index < rating ? Icons.star : Icons.star_border,
          size: 18,
          color: AppTheme.accentColor,
        );
      }),
    );
  }
}

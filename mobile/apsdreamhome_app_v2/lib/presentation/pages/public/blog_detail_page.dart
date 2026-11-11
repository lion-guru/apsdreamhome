import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/services/api_service.dart';

class BlogDetailPage extends ConsumerStatefulWidget {
  final String slug;
  const BlogDetailPage({super.key, required this.slug});

  @override
  ConsumerState<BlogDetailPage> createState() => _BlogDetailPageState();
}

class _BlogDetailPageState extends ConsumerState<BlogDetailPage> {
  Map<String, dynamic>? _post;
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadPost();
  }

  Future<void> _loadPost() async {
    try {
      final api = ApiService();
      final response = await api.get('/blog/${widget.slug}');
      if (mounted) {
        setState(() {
          if (response['success'] == true && response['data'] != null) {
            _post = Map<String, dynamic>.from(response['data'] as Map<dynamic, dynamic>);
          } else {
            _error = 'Post not found';
          }
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = e.toString();
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.error_outline, size: 64, color: Colors.red),
                      const SizedBox(height: 16),
                      Text(_error!),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: () => context.pop(),
                        child: const Text('Go Back'),
                      ),
                    ],
                  ),
                )
              : _post == null
                  ? const Center(child: Text('Post not found'))
                  : CustomScrollView(
                      slivers: [
                        SliverToBoxAdapter(
                          child: _buildPostContent(),
                        ),
                      ],
                    ),
    );
  }

  Widget _buildPostContent() {
    final title = (_post!['title'] ?? 'Untitled').toString();
    final content = (_post!['content'] ?? '').toString();
    final image = (_post!['featured_image'] ?? _post!['image'])?.toString();
    final date = _post!['published_at'] ?? _post!['created_at'];
    final author = (_post!['author'] ?? _post!['author_name'])?.toString();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        if (image != null)
          Image.network(
            image,
            width: double.infinity,
            height: 300,
            fit: BoxFit.cover,
            errorBuilder: (_, __, ___) => Container(
              height: 300,
              color: AppTheme.primaryColor.withValues(alpha: 0.1),
              child: const Center(child: Icon(Icons.article, size: 64, color: Colors.grey)),
            ),
          )
        else
          Container(
            height: 300,
            width: double.infinity,
            decoration: BoxDecoration(
              color: AppTheme.primaryColor.withValues(alpha: 0.1),
            ),
            child: const Center(child: Icon(Icons.article, size: 64, color: Colors.grey)),
          ),
        Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              if (date != null)
                Text(
                  _formatDate(date),
                  style: TextStyle(color: Colors.grey[500], fontSize: 14),
                ),
              const SizedBox(height: 12),
              Text(
                title,
                style: const TextStyle(
                  fontSize: 28,
                  fontWeight: FontWeight.w800,
                  height: 1.2,
                ),
              ),
              if (author != null)
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const SizedBox(height: 16),
                    Row(
                      children: [
                        CircleAvatar(
                          radius: 16,
                          backgroundColor: AppTheme.primaryColor.withValues(alpha: 0.1),
                          child: Text(
                            author[0].toUpperCase(),
                            style: TextStyle(
                              color: AppTheme.primaryColor,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Text(
                          author,
                          style: TextStyle(
                            color: Colors.grey[600],
                            fontSize: 14,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              const SizedBox(height: 24),
              Text(
                _stripHtml((_post!['content'] ?? '').toString()),
                style: const TextStyle(
                  fontSize: 16,
                  height: 1.8,
                  color: Color(0xFF212121),
                ),
              ),
              const SizedBox(height: 32),
              Divider(color: Colors.grey[200], thickness: 1),
              const SizedBox(height: 16),
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  _buildShareButton(Icons.share, 'Share', () {}),
                  const SizedBox(width: 16),
                  _buildShareButton(Icons.bookmark_border, 'Save', () {}),
                  const SizedBox(width: 16),
                  _buildShareButton(Icons.favorite_border, 'Like', () {}),
                ],
              ),
              const SizedBox(height: 32),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildShareButton(IconData icon, String label, VoidCallback onTap) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
        decoration: BoxDecoration(
          color: AppTheme.primaryColor.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppTheme.primaryColor.withValues(alpha: 0.3)),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 18, color: AppTheme.primaryColor),
            const SizedBox(width: 8),
            Text(
              label,
              style: TextStyle(
                color: AppTheme.primaryColor,
                fontWeight: FontWeight.w600,
                fontSize: 14,
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _formatDate(dynamic date) {
    if (date == null) return '';
    try {
      final dt = date is String ? DateTime.parse(date) : date as DateTime;
      return '${dt.day} ${_monthName(dt.month)} ${dt.year}';
    } catch (_) {
      return date.toString();
    }
  }

  String _monthName(int month) {
    const months = [
      'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
      'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
    ];
    return months[month - 1];
  }

  String _stripHtml(String html) {
    return html.replaceAll(RegExp(r'<[^>]*>'), '').replaceAll('&nbsp;', ' ').trim();
  }
}
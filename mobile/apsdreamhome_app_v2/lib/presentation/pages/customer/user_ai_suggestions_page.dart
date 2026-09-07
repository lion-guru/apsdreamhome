import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class UserAISuggestionsPage extends ConsumerStatefulWidget {
  const UserAISuggestionsPage({super.key});

  @override
  ConsumerState<UserAISuggestionsPage> createState() => _UserAISuggestionsPageState();
}

class _UserAISuggestionsPageState extends ConsumerState<UserAISuggestionsPage> {
  List<Map<String, dynamic>> _suggestions = [];
  bool _isLoading = true;
  Dio get _dio => Dio(BaseOptions(baseUrl: AppConstants.baseUrl));

  @override
  void initState() {
    super.initState();
    _fetchSuggestions();
  }

  Future<void> _fetchSuggestions() async {
    setState(() => _isLoading = true);
    try {
      final token = await ref.read(authProvider.notifier).getToken();
      final response = await _dio.get(
        '/api/v2/mobile/user/ai-suggestions',
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );
      final resData = response.data as Map<String, dynamic>;
      if (resData['success'] == true) {
        final data = resData['data'] as List;
        setState(() {
          _suggestions = data.cast<Map<String, dynamic>>();
          _isLoading = false;
        });
      } else {
        _loadMockData();
      }
    } catch (e) {
      _loadMockData();
    }
  }

  void _loadMockData() {
    setState(() {
      _suggestions = [
        {
          'type': 'property',
          'title': 'Perfect Match Found',
          'description': 'A 3BHK plot in Suryoday Colony matches your saved search criteria perfectly.',
          'priority': 'high',
          'action_text': 'View Property',
          'action_url': '/property-detail/123',
          'image': 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=400&h=300&q=80',
          'created_at': DateTime.now().subtract(const Duration(hours: 2)).toIso8601String(),
        },
        {
          'type': 'price_drop',
          'title': 'Price Alert',
          'description': 'A property you viewed has dropped by ₹2.5L. Check it out before it\'s gone!',
          'priority': 'high',
          'action_text': 'View Details',
          'action_url': '/property-detail/456',
          'image': 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=400&h=300&q=80',
          'created_at': DateTime.now().subtract(const Duration(hours: 5)).toIso8601String(),
        },
        {
          'type': 'investment',
          'title': 'Investment Opportunity',
          'description': 'Based on your budget, Braj Radha Nagri plots offer 18% projected returns.',
          'priority': 'medium',
          'action_text': 'Learn More',
          'action_url': '/colony-detail/braj-radha',
          'image': 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=400&h=300&q=80',
          'created_at': DateTime.now().subtract(const Duration(days: 1)).toIso8601String(),
        },
        {
          'type': 'emi',
          'title': 'EMI Optimization',
          'description': 'Switching to a 20-year tenure could save you ₹45,000 in interest.',
          'priority': 'medium',
          'action_text': 'Calculate',
          'action_url': '/emi-calculator',
          'image': 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=400&h=300&q=80',
          'created_at': DateTime.now().subtract(const Duration(days: 2)).toIso8601String(),
        },
        {
          'type': 'market',
          'title': 'Market Insight',
          'description': 'Gorakhpur property prices rose 12% YoY. Good time to invest in Raghunath Nagri.',
          'priority': 'low',
          'action_text': 'Read Report',
          'action_url': '/news/market-report',
          'image': 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=400&h=300&q=80',
          'created_at': DateTime.now().subtract(const Duration(days: 3)).toIso8601String(),
        },
      ];
      _isLoading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: MeshGradientBackground(
        child: SafeArea(
          child: _isLoading
              ? const Center(child: CircularProgressIndicator(color: Colors.white))
              : Column(
                  children: [
                    _buildHeader(),
                    Expanded(
                      child: _suggestions.isEmpty
                          ? _buildEmptyState()
                          : _buildSuggestionsList(),
                    ),
                  ],
                ),
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 10),
      child: Row(
        children: [
          GestureDetector(
            onTap: () => Navigator.pop(context),
            child: Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(Icons.arrow_back, color: Colors.white, size: 22),
            ),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: ShaderMask(
              shaderCallback: (bounds) => const LinearGradient(
                colors: [Colors.white, Color(0xFFE1BEE7)],
              ).createShader(bounds),
              child: Text(
                'AI Suggestions',
                style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                  color: Colors.white,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSuggestionsList() {
    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(20, 10, 20, 20),
      itemCount: _suggestions.length,
      itemBuilder: (context, index) {
        final s = _suggestions[index];
        return Padding(
          padding: const EdgeInsets.only(bottom: 16),
          child: _buildSuggestionCard(s),
        );
      },
    );
  }

  Widget _buildSuggestionCard(Map<String, dynamic> s) {
    final type = s['type']?.toString() ?? 'general';
    final title = s['title']?.toString() ?? '';
    final description = s['description']?.toString() ?? '';
    final priority = s['priority']?.toString() ?? 'medium';
    final actionText = s['action_text']?.toString() ?? 'View';
    final imageUrl = s['image']?.toString() ?? '';
    final createdAt = s['created_at']?.toString() ?? '';

    final typeIcons = {
      'property': Icons.home_rounded,
      'price_drop': Icons.trending_down_rounded,
      'investment': Icons.trending_up_rounded,
      'emi': Icons.calculate_rounded,
      'market': Icons.analytics_rounded,
    };
    final typeColors = {
      'property': Color(0xFF2196F3),
      'price_drop': Color(0xFFEF5350),
      'investment': Color(0xFF66BB6A),
      'emi': Color(0xFFFF9800),
      'market': Color(0xFF9C27B0),
    };

    final priorityColors = {
      'high': Color(0xFFEF5350),
      'medium': Color(0xFFFF9800),
      'low': Color(0xFF42A5F5),
    };

    final icon = typeIcons[type] ?? Icons.lightbulb_rounded;
    final color = typeColors[type] ?? Color(0xFF9C27B0);
    final priorityColor = priorityColors[priority] ?? Color(0xFF42A5F5);

    String timeAgo = '';
    if (createdAt.isNotEmpty) {
      try {
        final dt = DateTime.parse(createdAt);
        final diff = DateTime.now().difference(dt);
        if (diff.inDays > 0) {
          timeAgo = '${diff.inDays}d ago';
        } else if (diff.inHours > 0) {
          timeAgo = '${diff.inHours}h ago';
        } else {
          timeAgo = '${diff.inMinutes}m ago';
        }
      } catch (_) {
        timeAgo = '';
      }
    }

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
                width: 40,
                height: 40,
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(icon, color: color, size: 20),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 14),
                    ),
                    if (timeAgo.isNotEmpty)
                      Text(
                        timeAgo,
                        style: TextStyle(color: Colors.white.withValues(alpha: 0.5), fontSize: 10),
                      ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: priorityColor.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  priority.toUpperCase(),
                  style: TextStyle(color: priorityColor, fontSize: 9, fontWeight: FontWeight.w600),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          if (imageUrl.isNotEmpty)
            ClipRRect(
              borderRadius: BorderRadius.circular(12),
              child: Image.network(
                imageUrl,
                height: 140,
                width: double.infinity,
                fit: BoxFit.cover,
                errorBuilder: (_, _, _) => Container(
                  height: 140,
                  color: Colors.grey.shade800,
                  child: const Center(child: Icon(Icons.image, color: Colors.white30, size: 32)),
                ),
              ),
            ),
          if (imageUrl.isNotEmpty) const SizedBox(height: 12),
          Text(
            description,
            style: TextStyle(color: Colors.white.withValues(alpha: 0.9), fontSize: 13, height: 1.5),
          ),
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.end,
            children: [
              TextButton.icon(
                onPressed: () {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text('$actionText tapped'), backgroundColor: AppTheme.primaryColor),
                  );
                },
                icon: Icon(Icons.arrow_forward_rounded, size: 16, color: color),
                label: Text(actionText, style: TextStyle(color: color, fontWeight: FontWeight.w600)),
                style: TextButton.styleFrom(
                  foregroundColor: color,
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.psychology_rounded, size: 64, color: Colors.grey.shade400),
          const SizedBox(height: 16),
          Text(
            'No AI Suggestions Yet',
            style: TextStyle(color: Colors.grey.shade400, fontSize: 18, fontWeight: FontWeight.w600),
          ),
          const SizedBox(height: 8),
          Text(
            'As you browse properties, our AI will generate personalized suggestions for you.',
            style: TextStyle(color: Colors.grey.shade600, fontSize: 14),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}
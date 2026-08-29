import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:http/http.dart' as http;
import '../../../core/constants/app_constants.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class ResellPropertiesPage extends ConsumerStatefulWidget {
  const ResellPropertiesPage({super.key});

  @override
  ConsumerState<ResellPropertiesPage> createState() => _ResellPropertiesPageState();
}

class _ResellPropertiesPageState extends ConsumerState<ResellPropertiesPage> {
  List<Map<String, dynamic>> _properties = [];
  bool _loading = true;
  String? _error;
  int _currentPage = 1;

  @override
  void initState() {
    super.initState();
    _loadProperties();
  }

  Future<void> _loadProperties() async {
    if (!mounted) return;
    setState(() => _loading = true);
    try {
      AppConstants.initBaseUrl();
      final baseUrl = AppConstants.baseUrl;
      final response = await http
          .get(Uri.parse('$baseUrl/api/v2/resell/public?page=$_currentPage'))
          .timeout(const Duration(seconds: 15));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true && data['data'] is List) {
          final newProperties = List<Map<String, dynamic>>.from(data['data'] as List);
          if (mounted) {
            setState(() {
              _properties = newProperties;
              _loading = false;
              _error = null;
            });
          }
        } else if (data['success'] == true && data['data']['data'] is List) {
          final newProperties = List<Map<String, dynamic>>.from(data['data']['data'] as List);
          if (mounted) {
            setState(() {
              _properties = newProperties;
              _loading = false;
              _error = null;
            });
          }
        } else {
          if (mounted) {
            setState(() {
              _loading = false;
              _error = (data['message'] as String?) ?? 'Failed to load properties';
            });
          }
        }
      } else {
        if (mounted) {
          setState(() {
            _loading = false;
            _error = 'Failed to load properties';
          });
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _loading = false;
          _error = 'Error loading properties: ${e.toString()}';
        });
      }
    }
  }

  Future<void> _refresh() async {
    _currentPage = 1;
    await _loadProperties();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Resell Properties'),
        centerTitle: true,
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            onPressed: _refresh,
            icon: const Icon(Icons.refresh_rounded),
          ),
        ],
      ),
      body: MeshGradientBackground(
        child: SafeArea(
          child: _loading
              ? const Center(child: CircularProgressIndicator(color: Colors.white))
              : _error != null
                  ? _buildErrorState()
                  : _properties.isEmpty
                      ? _buildEmptyState()
                      : RefreshIndicator(
                          onRefresh: _refresh,
                          child: ListView.separated(
                            padding: const EdgeInsets.all(20),
                            itemCount: _properties.length,
                            separatorBuilder: (_, _) => const SizedBox(height: 16),
                            itemBuilder: (context, index) =>
                                _buildPropertyCard(context, _properties[index]),
                          ),
                        ),
        ),
      ),
    );
  }

  Widget _buildErrorState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error_outline_rounded, size: 48, color: Colors.white54),
            const SizedBox(height: 16),
            Text(
              _error!,
              style: const TextStyle(color: Colors.white70),
              textAlign: TextAlign.center,
            ),
const SizedBox(height: 16),
            ElevatedButton.icon(
              onPressed: _refresh,
              icon: const Icon(Icons.refresh_rounded),
              label: const Text('Retry'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primaryColor,
                foregroundColor: Colors.white,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 80,
              height: 80,
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [
                    AppTheme.primaryColor.withValues(alpha: 0.3),
                    AppTheme.secondaryColor.withValues(alpha: 0.3),
                  ],
                ),
                borderRadius: BorderRadius.circular(20),
              ),
              child: const Icon(
                Icons.home_outlined,
                size: 40,
                color: Colors.white54,
              ),
            ),
            const SizedBox(height: 16),
            Text(
              'No Resell Properties Yet',
              style: AppTheme.headlineMedium.copyWith(
                color: Colors.white,
                fontWeight: FontWeight.w700,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Be the first to list your property for resale!',
              style: TextStyle(color: Colors.white.withValues(alpha: 0.6), fontSize: 14),
              textAlign: TextAlign.center,
            ),
const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: () => context.push('/resell-properties/submit'),
              icon: const Icon(Icons.add_circle),
              label: const Text('List Your Property'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primaryColor,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
              ),
            ),
          ],
        ),
      )
    );
  }

  Widget _buildPropertyCard(BuildContext context, Map<String, dynamic> property) {
    final title = property['title']?.toString() ?? 'Untitled Property';
    final location = property['location']?.toString() ?? property['city']?.toString() ?? '';
    final price = property['price']?.toString() ?? property['total_price']?.toString() ?? '';
    final area = property['area_sqft']?.toString() ?? property['plot_area']?.toString() ?? '';
    final type = property['property_type']?.toString() ?? property['type']?.toString() ?? '';
    final image = property['image']?.toString() ?? property['image_path']?.toString() ?? '';
    final status = property['status']?.toString() ?? 'available';
    final featured = property['featured'] == true || property['is_featured'] == 1;

    return GlassCard(
      padding: const EdgeInsets.all(16),
      opacity: 0.1,
      blur: 8,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              if (image.isNotEmpty)
                ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child: Image.network(
                    image.startsWith('http') ? image : '${AppConstants.baseUrl}/$image',
                    width: 80,
                    height: 80,
                    fit: BoxFit.cover,
                    errorBuilder: (_, _, _) => _buildPlaceholder(),
                  ),
                )
              else
                _buildPlaceholder(),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            title,
                            style: const TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.w600,
                              fontSize: 14,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        if (featured)
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                            decoration: BoxDecoration(
                              color: AppTheme.accentColor.withValues(alpha: 0.2),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: const Text(
                              'FEATURED',
                              style: TextStyle(
                                color: AppTheme.accentColor,
                                fontWeight: FontWeight.w600,
                                fontSize: 10,
                              ),
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    if (location.isNotEmpty)
                      Text(
                        location,
                        style: TextStyle(
                          color: Colors.white.withValues(alpha: 0.5),
                          fontSize: 12,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        if (type.isNotEmpty) ...[
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                            decoration: BoxDecoration(
                              color: AppTheme.primaryColor.withValues(alpha: 0.2),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              type,
                              style: const TextStyle(
                                color: AppTheme.primaryColor,
                                fontWeight: FontWeight.w600,
                                fontSize: 10,
                              ),
                            ),
                          ),
                          const SizedBox(width: 6),
                        ],
                        if (area.isNotEmpty) ...[
                          const Icon(Icons.square_foot, size: 12, color: Colors.white54),
                          const SizedBox(width: 4),
                          Text(
                            '$area sqft',
                            style: TextStyle(color: Colors.white.withValues(alpha: 0.6), fontSize: 11),
                          ),
                          const SizedBox(width: 8),
                        ],
                      ],
                    ),
                  ],
                ),
              ),
              if (price.isNotEmpty)
                Text(
                  '₹${_formatPrice(price)}',
                  style: const TextStyle(
                    color: AppTheme.accentColor,
                    fontWeight: FontWeight.w700,
                    fontSize: 16,
                  ),
                ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: (status == 'sold' ? Colors.red : Colors.green).withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  status.toUpperCase(),
                  style: TextStyle(
                    color: status == 'sold' ? Colors.red : Colors.green,
                    fontWeight: FontWeight.w600,
                    fontSize: 10,
                  ),
                ),
              ),
              const Spacer(),
              TextButton.icon(
                onPressed: () => _viewProperty(context, property),
                icon: const Icon(Icons.visibility_rounded, size: 16),
                label: const Text('View Details'),
                style: TextButton.styleFrom(foregroundColor: AppTheme.primaryColor),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildPlaceholder() {
    return Container(
      width: 80,
      height: 80,
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            AppTheme.primaryColor.withValues(alpha: 0.3),
            AppTheme.secondaryColor.withValues(alpha: 0.3),
          ],
        ),
        borderRadius: BorderRadius.circular(8),
      ),
      child: const Icon(Icons.home_outlined, size: 32, color: Colors.white38),
    );
  }

  String _formatPrice(String price) {
    final num = double.tryParse(price) ?? 0;
    if (num >= 10000000) {
      return '${(num / 10000000).toStringAsFixed(2)} Cr';
    } else if (num >= 100000) {
      return '${(num / 100000).toStringAsFixed(2)} L';
    }
    return num.toStringAsFixed(0);
  }

  void _viewProperty(BuildContext context, Map<String, dynamic> property) {
    final propertyId = property['id']?.toString() ?? '';
    context.push('/resell-properties/$propertyId');
  }
}
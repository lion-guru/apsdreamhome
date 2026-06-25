import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_theme.dart';
import '../../../data/repositories/kyc_repository_provider.dart';

class ComparePropertiesPage extends ConsumerStatefulWidget {
  const ComparePropertiesPage({super.key});

  @override
  ConsumerState<ComparePropertiesPage> createState() =>
      _ComparePropertiesPageState();
}

class _ComparePropertiesPageState extends ConsumerState<ComparePropertiesPage> {
  List<Map<String, dynamic>> _properties = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadComparison();
  }

  Future<void> _loadComparison() async {
    if (!mounted) return;
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final api = ref.read(apiServiceProvider);
      final response = await api.get('user/compare');
      if (!mounted) return;
      final data = response['data'];
      if (data is List) {
        _properties = data.cast<Map<String, dynamic>>();
      } else if (data is Map && data.containsKey('properties')) {
        final list = data['properties'];
        if (list is List) {
          _properties = list.cast<Map<String, dynamic>>();
        } else {
          _properties = [];
        }
      } else {
        _properties = [];
      }
    } catch (e) {
      if (mounted) {
        setState(() => _error = e.toString());
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  Future<void> _removeProperty(int id) async {
    try {
      final api = ref.read(apiServiceProvider);
      await api.delete('user/compare/remove/$id');
      if (mounted) {
        setState(() {
          _properties.removeWhere((p) => _getInt(p, 'id') == id);
        });
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Property removed from comparison'),
            duration: Duration(seconds: 2),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to remove: $e'),
            backgroundColor: AppTheme.errorColor,
            duration: const Duration(seconds: 3),
          ),
        );
      }
    }
  }

  int _getInt(Map<String, dynamic> map, String key) {
    final val = map[key];
    if (val is int) return val;
    if (val is String) return int.tryParse(val) ?? 0;
    return 0;
  }

  String _getString(Map<String, dynamic> map, String key, [String fallback = '']) {
    final val = map[key];
    return val?.toString() ?? fallback;
  }

  double _getDouble(Map<String, dynamic> map, String key, [double fallback = 0.0]) {
    final val = map[key];
    if (val is double) return val;
    if (val is int) return val.toDouble();
    if (val is String) return double.tryParse(val) ?? fallback;
    return fallback;
  }

  String _formatPrice(double price) {
    if (price >= 10000000) {
      return '₹${(price / 10000000).toStringAsFixed(2)} Cr';
    } else if (price >= 100000) {
      return '₹${(price / 100000).toStringAsFixed(2)} L';
    }
    return '₹${price.toStringAsFixed(0)}';
  }

  Color _typeColor(String type) {
    switch (type.toLowerCase()) {
      case 'plot':
        return const Color(0xFF4CAF50);
      case 'house':
        return const Color(0xFF2196F3);
      case 'flat':
        return const Color(0xFF9C27B0);
      case 'shop':
        return const Color(0xFFFF9800);
      default:
        return AppTheme.primaryColor;
    }
  }

  IconData _typeIcon(String type) {
    switch (type.toLowerCase()) {
      case 'plot':
        return Icons.landscape;
      case 'house':
        return Icons.home;
      case 'flat':
        return Icons.apartment;
      case 'shop':
        return Icons.store;
      default:
        return Icons.location_on;
    }
  }

  bool _isBestValue(String field) {
    if (_properties.length < 2) return false;
    final values = _properties.map((p) {
      if (field == 'price_per_sqft') return _getDouble(p, 'price_per_sqft');
      if (field == 'area') return _getDouble(p, 'area_sqft');
      if (field == 'price') return _getDouble(p, 'price');
      return 0.0;
    }).toList();
    final nonZero = values.where((v) => v > 0).toList();
    if (nonZero.isEmpty) return false;
    if (field == 'price' || field == 'price_per_sqft') {
      final minVal = nonZero.reduce((a, b) => a < b ? a : b);
      return _getDouble(_properties[0], field) == minVal &&
          values.where((v) => v == minVal).length == 1;
    }
    if (field == 'area') {
      final maxVal = nonZero.reduce((a, b) => a > b ? a : b);
      return _getDouble(_properties[0], field) == maxVal &&
          values.where((v) => v == maxVal).length == 1;
    }
    return false;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.surfaceColor,
      appBar: AppBar(
        title: const Text('Compare Properties'),
        centerTitle: true,
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios),
          onPressed: () {
            if (context.canPop()) {
              context.pop();
            } else {
              context.go('/');
            }
          },
        ),
        actions: [
          if (_properties.isNotEmpty)
            IconButton(
              icon: const Icon(Icons.share),
              onPressed: _shareComparison,
            ),
        ],
      ),
      body: _isLoading
          ? Center(
              child: CircularProgressIndicator(color: AppTheme.primaryColor),
            )
          : _error != null
              ? _buildError()
              : _properties.isEmpty
                  ? _buildEmpty()
                  : _buildContent(),
    );
  }

  Widget _buildError() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppTheme.errorColor.withValues(alpha: 0.1),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.error_outline,
                size: 56,
                color: AppTheme.errorColor,
              ),
            ),
            const SizedBox(height: 20),
            Text(
              'Something went wrong',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w600,
                color: Colors.grey.shade800,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              _error!,
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 13,
                color: Colors.grey.shade600,
              ),
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: _loadComparison,
              icon: const Icon(Icons.refresh),
              label: const Text('Retry'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primaryColor,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 12),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmpty() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 160,
              height: 160,
              padding: const EdgeInsets.all(32),
              decoration: BoxDecoration(
                color: AppTheme.primaryColor.withValues(alpha: 0.06),
                shape: BoxShape.circle,
              ),
              child: Stack(
                alignment: Alignment.center,
                children: [
                  Icon(
                    Icons.compare_arrows,
                    size: 80,
                    color: AppTheme.primaryColor.withValues(alpha: 0.2),
                  ),
                  Positioned(
                    right: 10,
                    top: 10,
                    child: Container(
                      padding: const EdgeInsets.all(6),
                      decoration: BoxDecoration(
                        color: AppTheme.primaryColor.withValues(alpha: 0.15),
                        shape: BoxShape.circle,
                      ),
                      child: Icon(
                        Icons.add,
                        size: 24,
                        color: AppTheme.primaryColor.withValues(alpha: 0.5),
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 28),
            Text(
              'Add properties to compare',
              style: TextStyle(
                fontSize: 22,
                fontWeight: FontWeight.w700,
                color: Colors.grey.shade800,
              ),
            ),
            const SizedBox(height: 10),
            Text(
              'Select up to 4 properties to compare\nside by side',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 14,
                color: Colors.grey.shade500,
                height: 1.5,
              ),
            ),
            const SizedBox(height: 32),
            ElevatedButton.icon(
              onPressed: () => context.push('/properties'),
              icon: const Icon(Icons.add),
              label: const Text('Browse Properties'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primaryColor,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 36, vertical: 14),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                elevation: 2,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildContent() {
    return Column(
      children: [
        Container(
          width: double.infinity,
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
          color: Colors.white,
          child: Row(
            children: [
              Text(
                '${_properties.length} ${_properties.length == 1 ? "property" : "properties"} selected',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: Colors.grey.shade700,
                ),
              ),
              const Spacer(),
              if (_properties.length < 4)
                TextButton.icon(
                  onPressed: () => context.push('/properties'),
                  icon: const Icon(Icons.add, size: 18),
                  label: const Text('Add'),
                  style: TextButton.styleFrom(
                    foregroundColor: AppTheme.primaryColor,
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  ),
                ),
            ],
          ),
        ),
        Expanded(
          child: RefreshIndicator(
            onRefresh: _loadComparison,
            color: AppTheme.primaryColor,
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.all(16),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: _properties.map((property) {
                  return _buildPropertyCard(property);
                }).toList(),
              ),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildPropertyCard(Map<String, dynamic> property) {
    final id = _getInt(property, 'id');
    final name = _getString(property, 'title', 'Untitled');
    final type = _getString(property, 'property_type', _getString(property, 'type', 'plot'));
    final price = _getDouble(property, 'price');
    final location = _getString(property, 'location', _getString(property, 'area', ''));
    final area = _getDouble(property, 'area_sqft', _getDouble(property, 'size'));
    final bedrooms = _getInt(property, 'bedrooms');
    final bathrooms = _getInt(property, 'bathrooms');
    final status = _getString(property, 'status', 'Available');
    final yearBuilt = _getInt(property, 'year_built');
    final pricePerSqft = _getDouble(property, 'price_per_sqft');
    final imageUrl = _getString(property, 'image_url', _getString(property, 'image', _getString(property, 'thumbnail', '')));

    return Container(
      width: 220,
      margin: const EdgeInsets.only(right: 16),
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
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Stack(
            children: [
              ClipRRect(
                borderRadius: const BorderRadius.vertical(top: Radius.circular(12)),
                child: imageUrl.isNotEmpty
                    ? Image.network(
                        imageUrl,
                        width: 220,
                        height: 140,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => _imagePlaceholder(type),
                      )
                    : _imagePlaceholder(type),
              ),
              Positioned(
                top: 8,
                left: 8,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.92),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(_typeIcon(type), size: 12, color: _typeColor(type)),
                      const SizedBox(width: 3),
                      Text(
                        type.toUpperCase(),
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.w700,
                          color: _typeColor(type),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              Positioned(
                top: 8,
                right: 8,
                child: GestureDetector(
                  onTap: () => _showRemoveDialog(id, name),
                  child: Container(
                    padding: const EdgeInsets.all(6),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.92),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(
                      Icons.close,
                      size: 16,
                      color: AppTheme.errorColor,
                    ),
                  ),
                ),
              ),
              Positioned(
                bottom: 0,
                left: 0,
                right: 0,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      begin: Alignment.topCenter,
                      end: Alignment.bottomCenter,
                      colors: [
                        Colors.transparent,
                        Colors.black.withValues(alpha: 0.7),
                      ],
                    ),
                  ),
                  child: Text(
                    _formatPrice(price),
                    style: const TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.w700,
                      color: Colors.white,
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
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.textPrimaryLight,
                    height: 1.3,
                  ),
                ),
                const SizedBox(height: 8),
                if (location.isNotEmpty)
                  Row(
                    children: [
                      Icon(Icons.location_on_outlined, size: 14, color: Colors.grey.shade500),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          location,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey.shade600,
                          ),
                        ),
                      ),
                    ],
                  ),
                const SizedBox(height: 12),
                _buildCompareRow('Area', '${area.toStringAsFixed(0)} sqft', _isBestValue('area')),
                const SizedBox(height: 8),
                _buildCompareRow('Price/sqft', pricePerSqft > 0 ? '₹${pricePerSqft.toStringAsFixed(0)}' : '-', _isBestValue('price_per_sqft')),
                const SizedBox(height: 8),
                if (bedrooms > 0) ...[
                  _buildCompareRow('Bedrooms', '$bedrooms BHK', false),
                  const SizedBox(height: 8),
                ],
                if (bathrooms > 0) ...[
                  _buildCompareRow('Bathrooms', '$bathrooms', false),
                  const SizedBox(height: 8),
                ],
                if (yearBuilt > 0) ...[
                  _buildCompareRow('Year Built', '$yearBuilt', false),
                  const SizedBox(height: 8),
                ],
                _buildStatusBadge(status),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCompareRow(String label, String value, bool isBest) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: TextStyle(
            fontSize: 12,
            color: Colors.grey.shade600,
          ),
        ),
        Container(
          padding: isBest
              ? const EdgeInsets.symmetric(horizontal: 8, vertical: 2)
              : null,
          decoration: isBest
              ? BoxDecoration(
                  color: AppTheme.accentColor.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(4),
                )
              : null,
          child: Text(
            value,
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w600,
              color: isBest ? AppTheme.primaryColor : AppTheme.textPrimaryLight,
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildStatusBadge(String status) {
    Color bgColor;
    Color textColor;
    switch (status.toLowerCase()) {
      case 'available':
        bgColor = AppTheme.successColor.withValues(alpha: 0.1);
        textColor = AppTheme.successColor;
        break;
      case 'booked':
        bgColor = AppTheme.warningColor.withValues(alpha: 0.1);
        textColor = AppTheme.warningColor;
        break;
      case 'sold':
        bgColor = AppTheme.errorColor.withValues(alpha: 0.1);
        textColor = AppTheme.errorColor;
        break;
      default:
        bgColor = Colors.grey.shade100;
        textColor = Colors.grey.shade600;
    }

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(
        status[0].toUpperCase() + status.substring(1),
        style: TextStyle(
          fontSize: 12,
          fontWeight: FontWeight.w600,
          color: textColor,
        ),
        textAlign: TextAlign.center,
      ),
    );
  }

  Widget _imagePlaceholder(String type) {
    return Container(
      width: 220,
      height: 140,
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            _typeColor(type).withValues(alpha: 0.3),
            _typeColor(type).withValues(alpha: 0.6),
          ],
        ),
        borderRadius: const BorderRadius.vertical(top: Radius.circular(12)),
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            _typeIcon(type),
            size: 40,
            color: Colors.white.withValues(alpha: 0.9),
          ),
          const SizedBox(height: 6),
          Text(
            type.toUpperCase(),
            style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w700,
              color: Colors.white.withValues(alpha: 0.85),
            ),
          ),
        ],
      ),
    );
  }

  void _showRemoveDialog(int id, String name) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Remove Property'),
        content: Text('Remove "$name" from comparison?'),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(ctx);
              _removeProperty(id);
            },
            style: TextButton.styleFrom(
              foregroundColor: AppTheme.errorColor,
            ),
            child: const Text('Remove'),
          ),
        ],
      ),
    );
  }

  void _shareComparison() {
    final names = _properties.map((p) => _getString(p, 'title', 'Property')).join(' vs ');
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Comparison: $names'),
        duration: const Duration(seconds: 3),
      ),
    );
  }
}

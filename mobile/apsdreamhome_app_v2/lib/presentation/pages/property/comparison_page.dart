import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../../data/services/comparison_service.dart';
import '../../../data/services/property_listing_service.dart';

class ComparisonPage extends StatefulWidget {
  const ComparisonPage({super.key});

  @override
  State<ComparisonPage> createState() => _ComparisonPageState();
}

class _ComparisonPageState extends State<ComparisonPage> {
  final ComparisonService _service = ComparisonService();

  @override
  void initState() {
    super.initState();
    _service.addListener(_onChanged);
  }

  @override
  void dispose() {
    _service.removeListener(_onChanged);
    super.dispose();
  }

  void _onChanged() {
    if (mounted) setState(() {});
  }

  @override
  Widget build(BuildContext context) {
    final items = _service.items;

    return Scaffold(
      backgroundColor: const Color(0xFF0f172a),
      appBar: AppBar(
        title: const Text('Compare Properties'),
        backgroundColor: const Color(0xFF0f172a),
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
          if (items.isNotEmpty)
            TextButton(
              onPressed: () {
                _service.clear();
              },
              child: const Text(
                'Clear All',
                style: TextStyle(color: Color(0xFFf59e0b), fontSize: 14),
              ),
            ),
        ],
      ),
      body: items.isEmpty
          ? _buildEmptyState()
          : items.length == 1
              ? _buildOnePropertyHint()
              : _buildComparisonGrid(items),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 120,
              height: 120,
              decoration: BoxDecoration(
                color: const Color(0xFF1e293b),
                shape: BoxShape.circle,
                border: Border.all(
                  color: const Color(0xFF334155),
                  width: 2,
                ),
              ),
              child: const Icon(
                Icons.compare_arrows,
                size: 56,
                color: Color(0xFF64748b),
              ),
            ),
            const SizedBox(height: 24),
            const Text(
              'No properties to compare',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.w700,
                color: Colors.white,
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              'Add properties from the marketplace to\ncompare them side by side',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 14,
                color: Color(0xFF94a3b8),
                height: 1.5,
              ),
            ),
            const SizedBox(height: 28),
            ElevatedButton.icon(
              onPressed: () => context.push('/properties'),
              icon: const Icon(Icons.search, size: 18),
              label: const Text('Browse Properties'),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFFf59e0b),
                foregroundColor: const Color(0xFF0f172a),
                padding: const EdgeInsets.symmetric(
                  horizontal: 28,
                  vertical: 14,
                ),
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

  Widget _buildOnePropertyHint() {
    final p = _service.items.first;
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 100,
              height: 100,
              decoration: BoxDecoration(
                color: const Color(0xFF1e293b),
                shape: BoxShape.circle,
                border: Border.all(
                  color: const Color(0xFF334155),
                  width: 2,
                ),
              ),
              child: const Icon(
                Icons.add,
                size: 48,
                color: Color(0xFF64748b),
              ),
            ),
            const SizedBox(height: 20),
            Text(
              p.title,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w700,
                color: Colors.white,
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              'Add at least 1 more property to compare',
              style: TextStyle(
                fontSize: 14,
                color: Color(0xFF94a3b8),
              ),
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: () => context.push('/properties'),
              icon: const Icon(Icons.add, size: 18),
              label: const Text('Add Property'),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFFf59e0b),
                foregroundColor: const Color(0xFF0f172a),
                padding: const EdgeInsets.symmetric(
                  horizontal: 28,
                  vertical: 14,
                ),
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

  Widget _buildComparisonGrid(List<PropertyListing> items) {
    return Column(
      children: [
        // Header row
        Container(
          width: double.infinity,
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          color: const Color(0xFF1e293b),
          child: Row(
            children: [
              const Icon(Icons.compare_arrows, color: Color(0xFFf59e0b), size: 18),
              const SizedBox(width: 8),
              Text(
                '${items.length} properties selected',
                style: const TextStyle(
                  color: Colors.white70,
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                ),
              ),
              const Spacer(),
              if (items.length < 3)
                TextButton.icon(
                  onPressed: () => context.push('/properties'),
                  icon: const Icon(Icons.add, size: 16, color: Color(0xFFf59e0b)),
                  label: const Text(
                    'Add More',
                    style: TextStyle(color: Color(0xFFf59e0b), fontSize: 13),
                  ),
                ),
            ],
          ),
        ),
        const Divider(height: 1, color: Color(0xFF334155)),
        // Comparison body
        Expanded(
          child: SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.all(12),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: items.map((p) => _buildPropertyColumn(p)).toList(),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildPropertyColumn(PropertyListing property) {
    final locationText = property.city != null
        ? '${property.city}, ${property.state ?? ''}'
        : property.location;
    final typeIcon = _typeIcon(property.type);
    final typeColor = _typeColor(property.type);

    return Container(
      width: 220,
      margin: const EdgeInsets.only(right: 12),
      decoration: BoxDecoration(
        color: const Color(0xFF1e293b),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFF334155)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header with image + remove
          Stack(
            children: [
              // Image
              ClipRRect(
                borderRadius: const BorderRadius.vertical(
                  top: Radius.circular(12),
                ),
                child: SizedBox(
                  width: 220,
                  height: 130,
                  child: property.imageUrl != null &&
                          property.imageUrl!.isNotEmpty
                      ? Image.network(
                          property.imageUrl!,
                          fit: BoxFit.cover,
                          errorBuilder: (_, _, _) =>
                              _imagePlaceholder(property.type),
                        )
                      : _imagePlaceholder(property.type),
                ),
              ),
              // Type badge
              Positioned(
                top: 8,
                left: 8,
                child: Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 4,
                  ),
                  decoration: BoxDecoration(
                    color: typeColor,
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(typeIcon, size: 12, color: Colors.white),
                      const SizedBox(width: 4),
                      Text(
                        property.type.toUpperCase(),
                        style: const TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.w700,
                          color: Colors.white,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              // Remove button
              Positioned(
                top: 8,
                right: 8,
                child: GestureDetector(
                  onTap: () => _service.remove(property.id),
                  child: Container(
                    padding: const EdgeInsets.all(5),
                    decoration: BoxDecoration(
                      color: Colors.black54,
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(
                      Icons.close,
                      size: 14,
                      color: Colors.white,
                    ),
                  ),
                ),
              ),
              // Price overlay
              Positioned(
                bottom: 0,
                left: 0,
                right: 0,
                child: Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical: 8,
                  ),
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      begin: Alignment.topCenter,
                      end: Alignment.bottomCenter,
                      colors: [
                        Colors.transparent,
                        Colors.black.withValues(alpha: 0.8),
                      ],
                    ),
                  ),
                  child: Text(
                    '₹${property.formattedPrice}',
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFFf59e0b),
                    ),
                  ),
                ),
              ),
            ],
          ),

          // Title + Location
          Padding(
            padding: const EdgeInsets.all(10),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  property.title,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                    color: Colors.white,
                    height: 1.3,
                  ),
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    const Icon(
                      Icons.location_on_outlined,
                      size: 12,
                      color: Color(0xFF64748b),
                    ),
                    const SizedBox(width: 3),
                    Expanded(
                      child: Text(
                        locationText,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontSize: 11,
                          color: Color(0xFF94a3b8),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          // Comparison rows
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 10),
            child: Column(
              children: [
                _compareRow(
                  'Area',
                  property.area != null
                      ? '${property.area!.toStringAsFixed(0)} sqft'
                      : 'N/A',
                  _isBestArea(property),
                ),
                _compareRow(
                  'Price/sqft',
                  property.area != null && property.area! > 0
                      ? '₹${(property.price / property.area!).toStringAsFixed(0)}'
                      : 'N/A',
                  _isBestPricePerSqft(property),
                ),
                _compareRow(
                  'Type',
                  property.type[0].toUpperCase() + property.type.substring(1),
                  false,
                ),
                _compareRow('Purpose', property.purposeLabel, false),
                _compareRow('Status', property.status, false),
                _compareRow(
                  'Featured',
                  property.isFeatured ? '✓ Yes' : '✗ No',
                  false,
                ),
                _compareRow(
                  'Premium',
                  property.isPremium ? '✓ Yes' : '✗ No',
                  false,
                ),
              ],
            ),
          ),

          const SizedBox(height: 10),

          // Status badge
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 10),
            child: _statusBadge(property.status),
          ),

          const SizedBox(height: 10),

          // View button
          Padding(
            padding: const EdgeInsets.fromLTRB(10, 0, 10, 12),
            child: SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () =>
                    context.push('/property-detail/${property.id}'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFFf59e0b),
                  foregroundColor: const Color(0xFF0f172a),
                  padding: const EdgeInsets.symmetric(vertical: 10),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(8),
                  ),
                  elevation: 0,
                ),
                child: const Text(
                  'View Details',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _compareRow(String label, String value, bool isBest) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 5),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: const TextStyle(
              fontSize: 11,
              color: Color(0xFF64748b),
            ),
          ),
          Container(
            padding: isBest
                ? const EdgeInsets.symmetric(horizontal: 8, vertical: 2)
                : null,
            decoration: isBest
                ? BoxDecoration(
                    color: const Color(0xFF22c55e).withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(4),
                  )
                : null,
            child: Text(
              value,
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w600,
                color: isBest
                    ? const Color(0xFF22c55e)
                    : Colors.white,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _statusBadge(String status) {
    Color bgColor;
    Color textColor;
    switch (status.toLowerCase()) {
      case 'available':
        bgColor = const Color(0xFF22c55e).withValues(alpha: 0.15);
        textColor = const Color(0xFF22c55e);
        break;
      case 'booked':
        bgColor = const Color(0xFFf59e0b).withValues(alpha: 0.15);
        textColor = const Color(0xFFf59e0b);
        break;
      case 'sold':
        bgColor = const Color(0xFFef4444).withValues(alpha: 0.15);
        textColor = const Color(0xFFef4444);
        break;
      default:
        bgColor = const Color(0xFF334155);
        textColor = const Color(0xFF94a3b8);
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
          fontSize: 11,
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
      height: 130,
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            _typeColor(type).withValues(alpha: 0.3),
            _typeColor(type).withValues(alpha: 0.6),
          ],
        ),
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            _typeIcon(type),
            size: 40,
            color: Colors.white.withValues(alpha: 0.8),
          ),
          const SizedBox(height: 4),
          Text(
            type.toUpperCase(),
            style: TextStyle(
              fontSize: 10,
              fontWeight: FontWeight.w700,
              color: Colors.white.withValues(alpha: 0.7),
            ),
          ),
        ],
      ),
    );
  }

  // ── Best value helpers ──

  bool _isBestArea(PropertyListing current) {
    final items = _service.items;
    if (items.length < 2) return false;
    final areas = items
        .where((p) => p.area != null && p.area! > 0)
        .map((p) => p.area!)
        .toList();
    if (areas.length < 2) return false;
    final maxArea = areas.reduce((a, b) => a > b ? a : b);
    return current.area != null &&
        current.area! == maxArea &&
        areas.where((a) => a == maxArea).length == 1;
  }

  bool _isBestPricePerSqft(PropertyListing current) {
    final items = _service.items;
    if (items.length < 2) return false;
    final ppsList = items
        .where((p) => p.area != null && p.area! > 0)
        .map((p) => p.price / p.area!)
        .toList();
    if (ppsList.length < 2) return false;
    final minPps = ppsList.reduce((a, b) => a < b ? a : b);
    final currentPps =
        current.area != null && current.area! > 0
            ? current.price / current.area!
            : 0.0;
    return currentPps > 0 &&
        currentPps == minPps &&
        ppsList.where((v) => v == minPps).length == 1;
  }

  // ── Type helpers ──

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
      case 'farmhouse':
        return const Color(0xFF00796B);
      default:
        return const Color(0xFF1A237E);
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
      case 'farmhouse':
        return Icons.agriculture;
      default:
        return Icons.location_on;
    }
  }
}

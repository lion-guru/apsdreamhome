import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/property_providers.dart';
import '../../../data/models/property_model.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/app_widgets.dart';

/// Properties Page - Connected to Repository
class PropertiesPage extends ConsumerStatefulWidget {
  final String? type;
  final String? location;
  final String? sortBy;
  
  const PropertiesPage({
    super.key, 
    this.type, 
    this.location, 
    this.sortBy
  });

  @override
  ConsumerState<PropertiesPage> createState() => _PropertiesPageState();
}

class _PropertiesPageState extends ConsumerState<PropertiesPage> {
  String _selectedType = 'all';
  String _selectedSortBy = 'latest';
  double? _minPrice;
  double? _maxPrice;
  String _searchQuery = '';
  final TextEditingController _searchController = TextEditingController();
  
  final List<String> _types = [
    'all', 'plot', 'house', 'flat', 'shop', 'farmhouse'
  ];
  
  final List<String> _sortOptions = [
    'latest', 'price_low', 'price_high', 'size_low', 'size_high'
  ];

  @override
  void initState() {
    super.initState();
    _selectedType = widget.type ?? 'all';
    _selectedSortBy = widget.sortBy ?? 'latest';
    _searchController.addListener(() {
      setState(() {
        _searchQuery = _searchController.text;
      });
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final filters = {
      'type': _selectedType == 'all' ? null : _selectedType,
      'location': widget.location,
      'sort_by': _selectedSortBy,
      'min_price': _minPrice,
      'max_price': _maxPrice,
    };

    final propertiesAsync = ref.watch(propertiesProvider(filters));

    return Scaffold(
      appBar: AppBar(
        title: Text(_getPageTitle()),
        elevation: 0,
        backgroundColor: Colors.white,
        foregroundColor: Colors.black,
        actions: [
          IconButton(
            onPressed: _showFilterBottomSheet,
            icon: const Icon(Icons.tune),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push('/compare'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.compare_arrows),
        label: const Text('Compare'),
      ),
      body: Column(
        children: [
          // Search Bar
          _buildSearchBar(),
          
          // Filter Chips
          _buildFilterChips(),
          
          // Properties List
          Expanded(
            child: propertiesAsync.when(
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (error, stack) => AppWidgets.errorWidget(
                message: error.toString(),
                onRetry: () => ref.refresh(propertiesProvider(filters)),
              ),
              data: (properties) => _buildPropertiesList(properties),
            ),
          ),
        ],
      ),
    );
  }

  String _getPageTitle() {
    if (widget.type != null) {
      return '${widget.type!.toUpperCase()} Properties';
    }
    if (widget.location != null) {
      return 'Properties in ${widget.location}';
    }
    return 'All Properties';
  }

  Widget _buildSearchBar() {
    return Container(
      padding: const EdgeInsets.all(16),
      child: TextField(
        controller: _searchController,
        decoration: InputDecoration(
          hintText: 'Search properties...',
          prefixIcon: const Icon(Icons.search),
          suffixIcon: _searchQuery.isNotEmpty
              ? IconButton(
                  onPressed: () {
                    _searchController.clear();
                    setState(() {
                      _searchQuery = '';
                    });
                  },
                  icon: const Icon(Icons.clear),
                )
              : null,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: BorderSide(color: Colors.grey.shade300),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: Colors.blue),
          ),
          filled: true,
          fillColor: Colors.grey.shade50,
        ),
      ),
    );
  }

  Widget _buildFilterChips() {
    return Container(
      height: 60,
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        itemCount: _types.length,
        itemBuilder: (context, index) {
          final type = _types[index];
          final isSelected = _selectedType == type;
          
          return Container(
            margin: const EdgeInsets.only(right: 8),
            child: FilterChip(
              label: Text(type.toUpperCase()),
              selected: isSelected,
              onSelected: (selected) {
                setState(() {
                  _selectedType = type;
                });
              },
              backgroundColor: Colors.grey.shade200,
              selectedColor: Colors.blue.shade100,
              labelStyle: TextStyle(
                color: isSelected ? Colors.blue.shade700 : Colors.grey.shade700,
                fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildPropertiesList(List<PropertyModel> properties) {
    if (_searchQuery.isNotEmpty) {
      properties = properties.where((property) =>
        property.title.toLowerCase().contains(_searchQuery.toLowerCase()) ||
        property.location.toLowerCase().contains(_searchQuery.toLowerCase()) ||
        property.type.toLowerCase().contains(_searchQuery.toLowerCase())
      ).toList();
    }

    if (properties.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.search_off, size: 80, color: Colors.grey.shade300),
            const SizedBox(height: 16),
            Text(
              'No properties found',
              style: TextStyle(
                fontSize: 18,
                color: Colors.grey.shade600,
                fontWeight: FontWeight.w500,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Try adjusting your filters or search query',
              style: TextStyle(
                fontSize: 14,
                color: Colors.grey.shade500,
              ),
            ),
            const SizedBox(height: 24),
            ElevatedButton(
              onPressed: _showFilterBottomSheet,
              child: const Text('Adjust Filters'),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: () => ref.refresh(propertiesProvider(null).future),
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: properties.length,
        itemBuilder: (context, index) {
          final property = properties[index];
          return _buildPropertyCard(property);
        },
      ),
    );
  }

  Widget _buildPropertyCard(PropertyModel property) {
    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: InkWell(
        onTap: () => context.go('/properties/${property.id}'),
        borderRadius: BorderRadius.circular(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Property Image
            ClipRRect(
              borderRadius: const BorderRadius.vertical(top: Radius.circular(12)),
              child: property.imageUrl != null && property.imageUrl!.isNotEmpty
                  ? Image.network(
                      property.imageUrl!,
                      height: 200,
                      width: double.infinity,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => _buildPlaceholder(),
                    )
                  : _buildPlaceholder(),
            ),
            
            // Property Details
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Title and Favorite Button
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          property.title,
                          style: const TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      const SizedBox(width: 8),
                      FavoriteButton(propertyId: property.id),
                    ],
                  ),
                  
                  const SizedBox(height: 8),
                  
                  // Price
                  Text(
                    '₹${property.price.toStringAsFixed(0)}',
                    style: TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                      color: Colors.green.shade700,
                    ),
                  ),
                  
                  const SizedBox(height: 8),
                  
                  // Location and Type
                  Row(
                    children: [
                      Icon(Icons.location_on_outlined, 
                           size: 16, color: Colors.grey.shade600),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          property.location,
                          style: TextStyle(
                            fontSize: 14,
                            color: Colors.grey.shade600,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.blue.shade50,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          property.type.toUpperCase(),
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.blue.shade700,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ),
                    ],
                  ),
                  
                  if (property.size != null) ...[
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Icon(Icons.square_foot, 
                             size: 16, color: Colors.grey.shade600),
                        const SizedBox(width: 4),
                        Text(
                          '${property.size!.toStringAsFixed(0)} sq ft',
                          style: TextStyle(
                            fontSize: 14,
                            color: Colors.grey.shade600,
                          ),
                        ),
                      ],
                    ),
                  ],
                  
                  // Status Badge and Date
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      _buildStatusBadge(property.status),
                      const Spacer(),
                      Text(
                        'Listed ${_getFormattedDate(property.createdAt)}',
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
    );
  }

  Widget _buildPlaceholder() {
    return Container(
      height: 200,
      width: double.infinity,
      color: Colors.grey.shade200,
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.image_outlined, 
               size: 48, color: Colors.grey.shade400),
          const SizedBox(height: 8),
          Text('No Image',
               style: TextStyle(color: Colors.grey.shade400)),
        ],
      ),
    );
  }

  Widget _buildStatusBadge(String status) {
    Color color;
    String text;
    
    switch (status.toLowerCase()) {
      case 'available':
        color = Colors.green;
        text = 'Available';
        break;
      case 'sold':
        color = Colors.red;
        text = 'Sold';
        break;
      case 'reserved':
        color = Colors.orange;
        text = 'Reserved';
        break;
      default:
        color = Colors.grey;
        text = status;
    }
    
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Text(
        text,
        style: TextStyle(
          fontSize: 12,
          color: color,
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }

  String _getFormattedDate(String? dateString) {
    if (dateString == null) return 'recently';
    
    try {
      final date = DateTime.parse(dateString);
      final now = DateTime.now();
      final difference = now.difference(date);
      
      if (difference.inDays == 0) {
        return 'today';
      } else if (difference.inDays == 1) {
        return 'yesterday';
      } else if (difference.inDays < 7) {
        return '${difference.inDays} days ago';
      } else if (difference.inDays < 30) {
        return '${(difference.inDays / 7).floor()} weeks ago';
      } else {
        return '${(difference.inDays / 30).floor()} months ago';
      }
    } catch (e) {
      return 'recently';
    }
  }

  void _showFilterBottomSheet() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => StatefulBuilder(
        builder: (context, setModalState) => DraggableScrollableSheet(
          initialChildSize: 0.7,
          maxChildSize: 0.9,
          minChildSize: 0.5,
          builder: (context, scrollController) => Container(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Handle
                Center(
                  child: Container(
                    width: 40,
                    height: 4,
                    decoration: BoxDecoration(
                      color: Colors.grey.shade300,
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                ),
                
                const SizedBox(height: 20),
                
                // Title
                const Text(
                  'Filter Properties',
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                
                const SizedBox(height: 24),
                
                Expanded(
                  child: ListView(
                    controller: scrollController,
                    children: [
                      // Sort By
                      const Text(
                        'Sort By',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      const SizedBox(height: 12),
                      ..._sortOptions.map((option) => RadioListTile<String>(
                        title: Text(_getSortLabel(option)),
                        value: option,
                        groupValue: _selectedSortBy,
                        onChanged: (value) {
                          setModalState(() {
                            _selectedSortBy = value!;
                          });
                          setState(() {
                            _selectedSortBy = value!;
                          });
                        },
                      )),
                      
                      const SizedBox(height: 24),
                      
                      // Price Range
                      const Text(
                        'Price Range',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      const SizedBox(height: 12),
                      Row(
                        children: [
                          Expanded(
                            child: TextField(
                              decoration: const InputDecoration(
                                labelText: 'Min Price',
                                prefixText: '₹',
                                border: OutlineInputBorder(),
                              ),
                              keyboardType: TextInputType.number,
                              onChanged: (value) {
                                setModalState(() {
                                  _minPrice = double.tryParse(value);
                                });
                                setState(() {
                                  _minPrice = double.tryParse(value);
                                });
                              },
                            ),
                          ),
                          const SizedBox(width: 16),
                          Expanded(
                            child: TextField(
                              decoration: const InputDecoration(
                                labelText: 'Max Price',
                                prefixText: '₹',
                                border: OutlineInputBorder(),
                              ),
                              keyboardType: TextInputType.number,
                              onChanged: (value) {
                                setModalState(() {
                                  _maxPrice = double.tryParse(value);
                                });
                                setState(() {
                                  _maxPrice = double.tryParse(value);
                                });
                              },
                            ),
                          ),
                        ],
                      ),
                      
                      const SizedBox(height: 24),
                      
                      // Apply Button
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: () => Navigator.of(context).pop(),
                          style: ElevatedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 16),
                            backgroundColor: Colors.blue,
                            foregroundColor: Colors.white,
                          ),
                          child: const Text('Apply Filters'),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  String _getSortLabel(String value) {
    switch (value) {
      case 'latest':
        return 'Latest First';
      case 'price_low':
        return 'Price: Low to High';
      case 'price_high':
        return 'Price: High to Low';
      case 'size_low':
        return 'Size: Small to Large';
      case 'size_high':
        return 'Size: Large to Small';
      default:
        return value;
    }
  }
}

/// Favorite Button Widget
class FavoriteButton extends ConsumerWidget {
  final String propertyId;

  const FavoriteButton({super.key, required this.propertyId});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final favoritesAsync = ref.watch(favoritesProvider);
    
    return favoritesAsync.when(
      data: (favorites) {
        final isFavorite = favorites.any((p) => p.id == propertyId);
        
        return IconButton(
          onPressed: () => _toggleFavorite(ref, propertyId),
          icon: Icon(
            isFavorite ? Icons.favorite : Icons.favorite_border,
            color: isFavorite ? Colors.red : Colors.grey,
          ),
        );
      },
      loading: () => const IconButton(
        onPressed: null,
        icon: Icon(Icons.favorite_border, color: Colors.grey),
      ),
      error: (_, __) => const IconButton(
        onPressed: null,
        icon: Icon(Icons.favorite_border, color: Colors.grey),
      ),
    );
  }

  void _toggleFavorite(WidgetRef ref, String propertyId) {
    // Toggle favorite logic would go here
    // This would call the repository to toggle favorite status
    ScaffoldMessenger.of(ref.context).showSnackBar(
      const SnackBar(content: Text('Favorite toggled')),
    );
  }
}
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:go_router/go_router.dart';
import '../../core/constants/app_constants.dart';
import '../../core/theme/app_theme.dart';
import '../../data/models/property_model.dart';
import '../providers/property_provider.dart';
import '../widgets/glass_card.dart';
import '../widgets/common_widgets.dart';

class PropertyListPage extends ConsumerStatefulWidget {
  const PropertyListPage({super.key});
  
  @override
  ConsumerState<PropertyListPage> createState() => _PropertyListPageState();
}

class _PropertyListPageState extends ConsumerState<PropertyListPage> {
  final TextEditingController _searchController = TextEditingController();
  String _selectedType = 'All';
  String _selectedStatus = 'All';
  String _sortBy = 'price';
  
  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }
  
  @override
  Widget build(BuildContext context) {
    final propertiesAsync = ref.watch(propertiesProvider);
    final connectivity = ref.watch(connectivityProvider);
    
    return Scaffold(
      appBar: AppBar(
        title: const Text('Properties'),
        actions: [
          IconButton(
            onPressed: () => context.push('/properties/discover'),
            icon: const Icon(Icons.auto_awesome, color: Colors.amber),
            tooltip: 'Discover Mode (Swipe)',
          ),
          IconButton(
            onPressed: () => _showFilterDialog(context),
            icon: const Icon(Icons.filter_list),
          ),
          IconButton(
            onPressed: () => _refreshProperties(),
            icon: const Icon(Icons.refresh),
          ),
        ],
      ),
      body: Column(
        children: [
          // Search Bar
          Container(
            padding: const EdgeInsets.all(AppConstants.defaultPadding),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Search properties...',
                prefixIcon: const Icon(Icons.search),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        onPressed: () {
                          _searchController.clear();
                          setState(() {});
                        },
                        icon: const Icon(Icons.clear),
                      )
                    : null,
              ),
              onChanged: (value) {
                setState(() {});
              },
            ),
          ),
          
          // Filter Chips
          Container(
            padding: const EdgeInsets.symmetric(horizontal: AppConstants.defaultPadding),
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  _buildFilterChip('All', _selectedType == 'All', (value) {
                    setState(() {
                      _selectedType = value;
                    });
                  }),
                  const SizedBox(width: 8),
                  _buildFilterChip('Residential', _selectedType == 'Residential', (value) {
                    setState(() {
                      _selectedType = value;
                    });
                  }),
                  const SizedBox(width: 8),
                  _buildFilterChip('Commercial', _selectedType == 'Commercial', (value) {
                    setState(() {
                      _selectedType = value;
                    });
                  }),
                  const SizedBox(width: 8),
                  _buildFilterChip('Plot', _selectedType == 'Plot', (value) {
                    setState(() {
                      _selectedType = value;
                    });
                  }),
                ],
              ),
            ),
          ),
          
          const SizedBox(height: 8),
          
          // Properties List
          Expanded(
            child: propertiesAsync.when(
              data: (properties) {
                final filteredProperties = _filterProperties(properties);
                
                if (filteredProperties.isEmpty) {
                  return const EmptyStateWidget(
                    title: 'No Properties Found',
                    subtitle: 'Try adjusting your filters or search terms',
                  );
                }
                
                return RefreshIndicator(
                  onRefresh: () => _refreshProperties(),
                  child: ListView.builder(
                    padding: const EdgeInsets.all(AppConstants.defaultPadding),
                    itemCount: filteredProperties.length,
                    itemBuilder: (context, index) {
                      final property = filteredProperties[index];
                      return PropertyCard(
                        property: property,
                        onTap: () => _showPropertyDetails(property),
                        onStatusChange: connectivity.value ?? false
                            ? (newStatus) => _updatePropertyStatus(property, newStatus)
                            : null,
                      );
                    },
                  ),
                );
              },
              loading: () => const Padding(
                padding: EdgeInsets.all(AppConstants.defaultPadding),
                child: Column(
                  children: [
                    ShimmerCard(height: 200),
                    SizedBox(height: 16),
                    ShimmerCard(height: 200),
                    SizedBox(height: 16),
                    ShimmerCard(height: 200),
                  ],
                ),
              ),
              error: (error, stack) => EmptyStateWidget(
                title: 'Error Loading Properties',
                subtitle: error.toString(),
                action: ElevatedButton(
                  onPressed: () => _refreshProperties(),
                  child: const Text('Retry'),
                ),
              ),
            ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: connectivity.value ?? false ? _addProperty : null,
        child: const Icon(Icons.add),
      ),
    );
  }
  
  Widget _buildFilterChip(String label, bool isSelected, Function(String) onSelected) {
    return FilterChip(
      label: Text(label),
      selected: isSelected,
      onSelected: (selected) {
        if (selected) {
          onSelected(label);
        }
      },
      backgroundColor: Colors.grey.shade200,
      selectedColor: AppTheme.primaryColor.withOpacity(0.2),
      labelStyle: TextStyle(
        color: isSelected ? AppTheme.primaryColor : Colors.black87,
        fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
      ),
    );
  }
  
  List<Property> _filterProperties(List<Property> properties) {
    var filtered = properties.where((property) {
      // Search filter
      final searchQuery = _searchController.text.toLowerCase();
      final matchesSearch = searchQuery.isEmpty ||
          property.title.toLowerCase().contains(searchQuery) ||
          property.location.toLowerCase().contains(searchQuery) ||
          property.description.toLowerCase().contains(searchQuery);
      
      // Type filter
      final matchesType = _selectedType == 'All' || property.type == _selectedType;
      
      return matchesSearch && matchesType;
    }).toList();
    
    // Sort
    switch (_sortBy) {
      case 'price':
        filtered.sort((a, b) => a.price.compareTo(b.price));
        break;
      case 'title':
        filtered.sort((a, b) => a.title.compareTo(b.title));
        break;
      case 'location':
        filtered.sort((a, b) => a.location.compareTo(b.location));
        break;
    }
    
    return filtered;
  }
  
  void _showFilterDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Filter & Sort'),
        content: StatefulBuilder(
          builder: (context, setDialogState) {
            return Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Status Filter
                const Text('Status', style: TextStyle(fontWeight: FontWeight.bold)),
                const SizedBox(height: 8),
                Wrap(
                  spacing: 8,
                  children: ['All', 'Available', 'Booked', 'Sold', 'Hold'].map((status) {
                    return ChoiceChip(
                      label: Text(status),
                      selected: _selectedStatus == status,
                      onSelected: (selected) {
                        setDialogState(() {
                          _selectedStatus = status;
                        });
                      },
                    );
                  }).toList(),
                ),
                
                const SizedBox(height: 16),
                
                // Sort By
                const Text('Sort By', style: TextStyle(fontWeight: FontWeight.bold)),
                const SizedBox(height: 8),
                Wrap(
                  spacing: 8,
                  children: ['price', 'title', 'location'].map((sort) {
                    return ChoiceChip(
                      label: Text(sort.capitalize()),
                      selected: _sortBy == sort,
                      onSelected: (selected) {
                        setDialogState(() {
                          _sortBy = sort;
                        });
                      },
                    );
                  }).toList(),
                ),
              ],
            );
          },
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              setState(() {});
              Navigator.of(context).pop();
            },
            child: const Text('Apply'),
          ),
        ],
      ),
    );
  }
  
  void _refreshProperties() {
    ref.refresh(propertiesProvider);
  }
  
  void _showPropertyDetails(Property property) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (context) => PropertyDetailsSheet(property: property),
    );
  }
  
  void _updatePropertyStatus(Property property, String newStatus) async {
    try {
      await ref.read(propertyProvider.notifier).updatePropertyStatus(property, newStatus);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Property status updated')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: ${e.toString()}')),
        );
      }
    }
  }
  
  void _addProperty() {
    // TODO: Implement add property functionality
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Add property feature coming soon!')),
    );
  }
}

extension StringExtension on String {
  String capitalize() {
    return "${this[0].toUpperCase()}${substring(1)}";
  }
}

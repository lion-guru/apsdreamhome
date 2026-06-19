import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/providers/auth_provider.dart';

/// Property Marketplace Page - Buy/Sell/Rent Properties
/// Customers, Associates, Agents, Employees - Sab post kar sakte hain
class PropertyMarketplacePage extends ConsumerStatefulWidget {
  const PropertyMarketplacePage({super.key});

  @override
  ConsumerState<PropertyMarketplacePage> createState() => _PropertyMarketplacePageState();
}

class _PropertyMarketplacePageState extends ConsumerState<PropertyMarketplacePage> {
  String _selectedTab = 'buy'; // buy, rent, sell
  String _selectedType = 'all'; // all, plot, house, flat, shop
  String _selectedLocation = 'all';
  double _minPrice = 0;
  double _maxPrice = 5000000;
  final bool _isLoading = false;

  final List<Map<String, dynamic>> _sampleProperties = [
    {
      'id': '1',
      'title': '100 Sq Yd Plot in Gorakhpur',
      'description': 'Prime location near highway',
      'price': 2500000,
      'type': 'plot',
      'purpose': 'sell',
      'location': 'Gorakhpur, UP',
      'area': 100,
      'images': ['https://via.placeholder.com/300x200'],
      'ownerType': 'associate',
      'ownerName': 'Rahul Sharma',
      'isVerified': true,
      'views': 45,
      'inquiries': 8,
    },
    {
      'id': '2',
      'title': '3 BHK House in Lucknow',
      'description': 'Ready to move, furnished',
      'price': 8500000,
      'type': 'house',
      'purpose': 'rent',
      'location': 'Lucknow, UP',
      'area': 1800,
      'images': ['https://via.placeholder.com/300x200'],
      'ownerType': 'customer',
      'ownerName': 'Amit Kumar',
      'isVerified': true,
      'views': 120,
      'inquiries': 15,
    },
    {
      'id': '3',
      'title': 'Commercial Shop in Varanasi',
      'description': 'Main market location',
      'price': 4500000,
      'type': 'shop',
      'purpose': 'sell',
      'location': 'Varanasi, UP',
      'area': 400,
      'images': ['https://via.placeholder.com/300x200'],
      'ownerType': 'agent',
      'ownerName': 'Priya Singh',
      'isVerified': false,
      'views': 23,
      'inquiries': 3,
    },
  ];

  @override
  Widget build(BuildContext context) {
    final userAsync = ref.watch(authProvider);
    final isLoggedIn = userAsync != null;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Property Marketplace'),
        actions: [
          IconButton(
            icon: const Icon(Icons.filter_list),
            onPressed: _showFilterBottomSheet,
          ),
          if (isLoggedIn)
            IconButton(
              icon: const Icon(Icons.add_circle),
              onPressed: () => context.push('/property/post'),
            ),
        ],
      ),
      body: Column(
        children: [
          // Tab Bar: Buy | Rent | Sell
          _buildTabBar(),
          
          // Quick Filters
          _buildQuickFilters(),
          
          // Property List
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _buildPropertyList(),
          ),
        ],
      ),
      floatingActionButton: isLoggedIn
          ? FloatingActionButton.extended(
              onPressed: () => context.push('/property/post'),
              icon: const Icon(Icons.add),
              label: const Text('Post Property'),
            )
          : null,
    );
  }

  Widget _buildTabBar() {
    return Container(
      padding: const EdgeInsets.all(8),
      child: SegmentedButton<String>(
        segments: const [
          ButtonSegment(value: 'buy', label: Text('Buy')),
          ButtonSegment(value: 'rent', label: Text('Rent')),
          ButtonSegment(value: 'sell', label: Text('Sell')),
        ],
        selected: {_selectedTab},
        onSelectionChanged: (set) {
          setState(() => _selectedTab = set.first);
        },
      ),
    );
  }

  Widget _buildQuickFilters() {
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Row(
        children: [
          _buildFilterChip('All', 'all', Icons.apps),
          _buildFilterChip('Plot', 'plot', Icons.landscape),
          _buildFilterChip('House', 'house', Icons.home),
          _buildFilterChip('Flat', 'flat', Icons.apartment),
          _buildFilterChip('Shop', 'shop', Icons.store),
          _buildFilterChip('Farm', 'farmhouse', Icons.agriculture),
        ],
      ),
    );
  }

  Widget _buildFilterChip(String label, String value, IconData icon) {
    final isSelected = _selectedType == value;
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: FilterChip(
        avatar: Icon(icon, size: 18),
        label: Text(label),
        selected: isSelected,
        onSelected: (selected) {
          setState(() => _selectedType = selected ? value : 'all');
        },
      ),
    );
  }

  Widget _buildPropertyList() {
    if (_sampleProperties.isEmpty) {
      return const Center(
        child: Text('No properties found'),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: _sampleProperties.length,
      itemBuilder: (context, index) {
        final property = _sampleProperties[index];
        return _PropertyCard(
          property: property,
          onTap: () => context.push(
            '/property-detail/${property['id']}',
            extra: {
              'title': property['title'] as String,
              'price': (property['price'] as num).toDouble(),
              'location': property['location'] as String,
              'area': (property['area'] as num).toDouble(),
              'type': property['type'] as String,
              'description': property['description'] as String,
              'image': (property['images'] as List).isNotEmpty
                  ? property['images'][0] as String
                  : '',
            },
          ),
        );
      },
    );
  }

  void _showFilterBottomSheet() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (context) => DraggableScrollableSheet(
        initialChildSize: 0.7,
        maxChildSize: 0.9,
        minChildSize: 0.5,
        expand: false,
        builder: (context, scrollController) {
          return Container(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'Filters',
                      style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                    ),
                    TextButton(
                      onPressed: () {
                        setState(() {
                          _selectedType = 'all';
                          _minPrice = 0;
                          _maxPrice = 5000000;
                        });
                        Navigator.pop(context);
                      },
                      child: const Text('Reset'),
                    ),
                  ],
                ),
                const Divider(),
                
                // Price Range
                Text(
                  'Price Range: ₹${_minPrice.toInt()} - ₹${_maxPrice.toInt()}',
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
                RangeSlider(
                  values: RangeValues(_minPrice, _maxPrice),
                  min: 0,
                  max: 10000000,
                  divisions: 100,
                  labels: RangeLabels(
                    '₹${_minPrice.toInt()}',
                    '₹${_maxPrice.toInt()}',
                  ),
                  onChanged: (values) {
                    setState(() {
                      _minPrice = values.start;
                      _maxPrice = values.end;
                    });
                  },
                ),
                
                const SizedBox(height: 16),
                
                // Location
                const Text(
                  'Location',
                  style: TextStyle(fontWeight: FontWeight.bold),
                ),
                Wrap(
                  spacing: 8,
                  children: [
                    'All Locations',
                    'Gorakhpur',
                    'Lucknow',
                    'Varanasi',
                    'Kanpur',
                    'Noida',
                  ].map((loc) => ChoiceChip(
                    label: Text(loc),
                    selected: _selectedLocation == loc,
                    onSelected: (selected) {
                      setState(() => _selectedLocation = selected ? loc : 'all');
                    },
                  )).toList(),
                ),
                
                const Spacer(),
                
                // Apply Button
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () {
                      Navigator.pop(context);
                      // Apply filters
                    },
                    child: const Text('Apply Filters'),
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}

class _PropertyCard extends StatelessWidget {
  final Map<String, dynamic> property;
  final VoidCallback onTap;

  const _PropertyCard({
    required this.property,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final isVerified = property['isVerified'] as bool;
    final ownerType = property['ownerType'] as String;
    
    String ownerBadge = '';
    Color badgeColor = Colors.grey;
    switch (ownerType) {
      case 'associate':
        ownerBadge = 'APS Associate';
        badgeColor = Colors.blue;
        break;
      case 'agent':
        ownerBadge = 'Agent';
        badgeColor = Colors.orange;
        break;
      case 'customer':
        ownerBadge = 'Owner';
        badgeColor = Colors.green;
        break;
    }

    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: onTap,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Image
            Stack(
              children: [
                Image.network(
                  property['images'][0] as String,
                  height: 200,
                  width: double.infinity,
                  fit: BoxFit.cover,
                ),
                if (isVerified)
                  Positioned(
                    top: 8,
                    left: 8,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.green,
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.verified, color: Colors.white, size: 14),
                          SizedBox(width: 4),
                          Text(
                            'Verified',
                            style: TextStyle(color: Colors.white, fontSize: 12),
                          ),
                        ],
                      ),
                    ),
                  ),
                Positioned(
                  top: 8,
                  right: 8,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: badgeColor,
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: Text(
                      ownerBadge,
                      style: const TextStyle(color: Colors.white, fontSize: 12),
                    ),
                  ),
                ),
              ],
            ),
            
            // Details
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    property['title'] as String,
                    style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    property['location'] as String,
                    style: TextStyle(
                      color: Colors.grey[600],
                    ),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      _buildFeatureChip('${property['area']} sq yd'),
                      const SizedBox(width: 8),
                      _buildFeatureChip(property['type'].toString().toUpperCase()),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        '₹${_formatPrice((property['price'] as num).toDouble())}',
                        style: const TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                          color: Colors.blue,
                        ),
                      ),
                      Row(
                        children: [
                          Icon(Icons.visibility, size: 16, color: Colors.grey[600]),
                          const SizedBox(width: 4),
                          Text('${property['views']}'),
                          const SizedBox(width: 16),
                          Icon(Icons.message, size: 16, color: Colors.grey[600]),
                          const SizedBox(width: 4),
                          Text('${property['inquiries']}'),
                        ],
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Posted by: ${property['ownerName']}',
                    style: TextStyle(
                      color: Colors.grey[600],
                      fontSize: 12,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFeatureChip(String label) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: Colors.grey[200],
        borderRadius: BorderRadius.circular(4),
      ),
      child: Text(
        label,
        style: TextStyle(
          fontSize: 12,
          color: Colors.grey[800],
        ),
      ),
    );
  }

  String _formatPrice(double price) {
    if (price >= 10000000) {
      return '${(price / 10000000).toStringAsFixed(1)} Cr';
    } else if (price >= 100000) {
      return '${(price / 100000).toStringAsFixed(1)} L';
    } else if (price >= 1000) {
      return '${(price / 1000).toStringAsFixed(0)} K';
    }
    return price.toStringAsFixed(0);
  }
}

import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

/// Map View Page
/// Interactive map showing all colonies with markers
class MapViewPage extends StatefulWidget {
  const MapViewPage({super.key});

  @override
  State<MapViewPage> createState() => _MapViewPageState();
}

class _MapViewPageState extends State<MapViewPage> {
  // Sample colony data with coordinates
  final List<Map<String, dynamic>> _colonies = [
    {
      'id': '1',
      'name': 'Suryoday Heights Phase 1',
      'location': 'Gorakhpur',
      'lat': 26.7606,
      'lng': 83.3732,
      'plots': 120,
      'price': '₹3,000/sqft',
      'amenities': ['Park', 'Security', 'Water'],
      'status': 'active',
    },
    {
      'id': '2',
      'name': 'Raghunath City Center',
      'location': 'Gorakhpur',
      'lat': 26.7324,
      'lng': 83.3603,
      'plots': 80,
      'price': '₹3,500/sqft',
      'amenities': ['Mall', 'Hospital', 'School'],
      'status': 'active',
    },
    {
      'id': '3',
      'name': 'Braj Radha Enclave',
      'location': 'Lucknow',
      'lat': 26.8467,
      'lng': 80.9462,
      'plots': 200,
      'price': '₹4,200/sqft',
      'amenities': ['Pool', 'Club', 'Gym'],
      'status': 'active',
    },
    {
      'id': '4',
      'name': 'Budh Bihar Colony',
      'location': 'Kushinagar',
      'lat': 27.1339,
      'lng': 83.9023,
      'plots': 60,
      'price': '₹2,800/sqft',
      'amenities': ['Park', 'Temple'],
      'status': 'active',
    },
    {
      'id': '5',
      'name': 'Ganga Nagri',
      'location': 'Varanasi',
      'lat': 25.3176,
      'lng': 83.0100,
      'plots': 150,
      'price': '₹3,800/sqft',
      'amenities': ['River View', 'Garden'],
      'status': 'active',
    },
  ];

  String? _selectedColonyId;
  String _filter = 'all'; // all, active, upcoming
  bool _showList = false;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Colony Map'),
        backgroundColor: Colors.blue.shade700,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: Icon(_showList ? Icons.map : Icons.list),
            onPressed: () {
              setState(() {
                _showList = !_showList;
              });
            },
          ),
        ],
      ),
      body: _showList ? _buildListView() : _buildMapView(),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () {
          _showFilterSheet();
        },
        icon: const Icon(Icons.filter_list),
        label: const Text('Filter'),
        backgroundColor: Colors.blue.shade700,
        foregroundColor: Colors.white,
      ),
    );
  }

  Widget _buildMapView() {
    return Stack(
      children: [
        // Simulated Map Background
        Container(
          color: Colors.grey.shade200,
          child: Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(
                  Icons.map,
                  size: 120,
                  color: Colors.grey.shade400,
                ),
                const SizedBox(height: 24),
                Text(
                  'Interactive Map',
                  style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: Colors.grey.shade600,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  'Google Maps integration\n(Requires API key configuration)',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    color: Colors.grey.shade500,
                  ),
                ),
                const SizedBox(height: 32),
                ElevatedButton.icon(
                  onPressed: () {
                    _showColonyMarkers();
                  },
                  icon: const Icon(Icons.location_on),
                  label: const Text('Show Colony Locations'),
                ),
              ],
            ),
          ),
        ),
        
        // Colony Markers (simulated)
        ..._buildSimulatedMarkers(),
        
        // Selected Colony Card
        if (_selectedColonyId != null)
          Positioned(
            bottom: 80,
            left: 16,
            right: 16,
            child: _buildSelectedColonyCard(),
          ),
      ],
    );
  }

  List<Widget> _buildSimulatedMarkers() {
    // In production, these would be positioned based on actual map coordinates
    // For demo, we'll show floating position indicators
    return _colonies.asMap().entries.map((entry) {
      final int index = entry.key;
      final Map<String, dynamic> colony = entry.value;
      final isSelected = _selectedColonyId == (colony['id'] as String);
      return Positioned(
        left: 50 + (index * 70).toDouble(),
        top: 100 + (index * 50).toDouble(),
        child: GestureDetector(
          onTap: () {
            setState(() {
              _selectedColonyId = colony['id'] as String;
            });
          },
          child: AnimatedContainer(
            duration: const Duration(milliseconds: 200),
            transform: isSelected
                ? (Matrix4.identity()..scale(1.2, 1.2, 1.2))
                : Matrix4.identity(),
            child: Column(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: isSelected ? Colors.blue.shade700 : Colors.white,
                    borderRadius: BorderRadius.circular(8),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.2),
                        blurRadius: 4,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Text(
                    colony['name'] as String,
                    style: TextStyle(
                      color: isSelected ? Colors.white : Colors.black,
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
                Icon(
                  Icons.location_on,
                  size: 40,
                  color: isSelected ? Colors.blue.shade700 : Colors.red,
                ),
              ],
            ),
          ),
        ),
      );
    }).toList();
  }

  Widget _buildSelectedColonyCard() {
    final colony = _colonies.firstWhere((c) => (c['id'] as String) == _selectedColonyId);
    
    return Card(
      elevation: 8,
      child: Container(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  width: 80,
                  height: 80,
                  decoration: BoxDecoration(
                    color: Colors.blue.shade100,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Icon(
                    Icons.location_city,
                    size: 40,
                    color: Colors.blue.shade700,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        colony['name'] as String,
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          Icon(Icons.location_on, size: 14, color: Colors.grey.shade600),
                          const SizedBox(width: 4),
                          Text(
                            colony['location'] as String,
                            style: TextStyle(color: Colors.grey.shade600),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Text(
                        colony['price'] as String,
                        style: TextStyle(
                          fontWeight: FontWeight.bold,
                          color: Colors.green.shade700,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            
            // Amenities
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: (colony['amenities'] as List<String>)
                  .map((amenity) => Chip(
                        label: Text(
                          amenity,
                          style: const TextStyle(fontSize: 12),
                        ),
                        backgroundColor: Colors.grey.shade100,
                        padding: EdgeInsets.zero,
                      ))
                  .toList(),
            ),
            const SizedBox(height: 16),
            
            // Action Buttons
            Row(
              children: [
                Expanded(
                  child: ElevatedButton.icon(
                    onPressed: () {
                      context.push('/colony-detail/${colony['id']}');
                    },
                    icon: const Icon(Icons.visibility, size: 18),
                    label: const Text('View Details'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.blue.shade700,
                      foregroundColor: Colors.white,
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () {
                      _showDirections(colony);
                    },
                    icon: const Icon(Icons.directions, size: 18),
                    label: const Text('Get Directions'),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: () {
                  context.push('/site-visit', extra: colony);
                },
                icon: const Icon(Icons.calendar_today, size: 18),
                label: const Text('Book Site Visit'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.green,
                  foregroundColor: Colors.white,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildListView() {
    final filteredColonies = _filter == 'all'
        ? _colonies
        : _colonies.where((c) => c['status'] == _filter).toList();
    
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: filteredColonies.length,
      itemBuilder: (context, index) {
        final colony = filteredColonies[index];
        return Card(
          margin: const EdgeInsets.only(bottom: 12),
          child: InkWell(
            onTap: () {
              setState(() {
                _selectedColonyId = colony['id'] as String?;
                _showList = false;
              });
            },
            borderRadius: BorderRadius.circular(12),
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Row(
                children: [
                  Container(
                    width: 80,
                    height: 80,
                    decoration: BoxDecoration(
                      color: Colors.blue.shade100,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Icon(
                      Icons.location_city,
                      size: 40,
                      color: Colors.blue.shade700,
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          colony['name'] as String,
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 16,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Row(
                          children: [
                            Icon(Icons.location_on, size: 14, color: Colors.grey.shade600),
                            const SizedBox(width: 4),
                            Text(
                              colony['location'] as String,
                              style: TextStyle(
                                fontSize: 13,
                                color: Colors.grey.shade600,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: Colors.green.shade100,
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Text(
                                colony['price'] as String,
                                style: TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.bold,
                                  color: Colors.green.shade700,
                                ),
                              ),
                            ),
                            const SizedBox(width: 8),
                            Text(
                              '${colony['plots']} plots available',
                              style: TextStyle(
                                fontSize: 12,
                                color: Colors.grey.shade600,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  Icon(Icons.chevron_right, color: Colors.grey.shade400),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  void _showFilterSheet() {
    showModalBottomSheet(
      context: context,
      builder: (context) => Container(
        padding: const EdgeInsets.all(16),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Filter Colonies',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),
            
            ListTile(
              leading: const Icon(Icons.all_inclusive),
              title: const Text('All Colonies'),
              trailing: _filter == 'all' ? const Icon(Icons.check) : null,
              onTap: () {
                setState(() {
                  _filter = 'all';
                });
                Navigator.pop(context);
              },
            ),
            ListTile(
              leading: const Icon(Icons.check_circle, color: Colors.green),
              title: const Text('Active Projects'),
              trailing: _filter == 'active' ? const Icon(Icons.check) : null,
              onTap: () {
                setState(() {
                  _filter = 'active';
                });
                Navigator.pop(context);
              },
            ),
            ListTile(
              leading: const Icon(Icons.upcoming, color: Colors.orange),
              title: const Text('Upcoming Projects'),
              trailing: _filter == 'upcoming' ? const Icon(Icons.check) : null,
              onTap: () {
                setState(() {
                  _filter = 'upcoming';
                });
                Navigator.pop(context);
              },
            ),
            const SizedBox(height: 16),
            
            // Price Range Filter
            const Text(
              'Price Range',
              style: TextStyle(fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            Wrap(
              spacing: 8,
              children: [
                FilterChip(label: const Text('Under ₹30L'), onSelected: (_) {}),
                FilterChip(label: const Text('₹30-50L'), onSelected: (_) {}),
                FilterChip(label: const Text('₹50L-1Cr'), onSelected: (_) {}),
                FilterChip(label: const Text('Above ₹1Cr'), onSelected: (_) {}),
              ],
            ),
          ],
        ),
      ),
    );
  }

  void _showColonyMarkers() {
    // In production, this would open Google Maps with markers
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Map View'),
        content: const Text(
          'Google Maps integration requires:\n\n'
          '1. Google Maps API Key\n'
          '2. Enable Maps SDK for Android/iOS\n'
          '3. Add billing account\n\n'
          'Contact developer to enable full map functionality.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('OK'),
          ),
        ],
      ),
    );
  }

  void _showDirections(Map<String, dynamic> colony) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Directions to ${colony['name'] as String}'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text('Location: ${colony['location'] as String}'),
            const SizedBox(height: 8),
            Text('Coordinates: ${colony['lat']}, ${colony['lng']}'),
            const SizedBox(height: 16),
            const Text(
              'In production, this will open Google Maps or Apple Maps for navigation.',
              style: TextStyle(color: Colors.grey, fontSize: 12),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Close'),
          ),
          ElevatedButton.icon(
            onPressed: () {
              Navigator.pop(context);
              // Would launch external maps app
            },
            icon: const Icon(Icons.open_in_new),
            label: const Text('Open in Maps'),
          ),
        ],
      ),
    );
  }
}

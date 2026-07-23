import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:http/http.dart' as http;
import 'package:url_launcher/url_launcher.dart';
import '../../../core/constants/app_constants.dart';

class MapViewPage extends StatefulWidget {
  const MapViewPage({super.key});

  @override
  State<MapViewPage> createState() => _MapViewPageState();
}

class _MapViewPageState extends State<MapViewPage> {
  List<Map<String, dynamic>> _colonies = [];
  bool _loading = true;
  String? _selectedColonyId;
  String _filter = 'all';
  bool _showList = false;

  @override
  void initState() {
    super.initState();
    _loadColonies();
  }

  Future<void> _loadColonies() async {
    try {
      AppConstants.initBaseUrl();
      final url = '${AppConstants.baseUrl}/api/v2/mobile/colonies';
      final resp = await http
          .get(Uri.parse(url))
          .timeout(const Duration(seconds: 10));
      if (resp.statusCode == 200) {
        final data = jsonDecode(resp.body);
        if (data['success'] == true && data['data'] is List) {
          final colonies = List<Map<String, dynamic>>.from(
            data['data'] as List,
          );
          setState(() {
            _colonies = colonies.map((c) {
              final int plots = c['total_plots'] is int
                  ? c['total_plots'] as int
                  : int.tryParse('${c['total_plots']}') ?? 0;
              final int available = c['available_plots'] is int
                  ? c['available_plots'] as int
                  : int.tryParse('${c['available_plots']}') ?? 0;
              final double price = c['starting_price'] is double
                  ? c['starting_price'] as double
                  : double.tryParse('${c['starting_price']}') ?? 0;
              return {
                'id': (c['id'] ?? '').toString(),
                'name': c['name'] ?? '',
                'location': c['district_name'] ?? '',
                'plots': plots,
                'available_plots': available,
                'price': price > 0
                    ? '₹${(price / 100000).toStringAsFixed(1)}L+'
                    : 'Contact',
                'status': c['is_active'] == true ? 'active' : 'upcoming',
                'featured': c['is_featured'] == true,
                'image_url': c['image_url'],
              };
            }).toList();
            _loading = false;
          });
          return;
        }
      }
    } catch (_) {}
    // Fallback to mock data
    setState(() {
      _colonies = _mockColonies;
      _loading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    final filteredColonies = _filter == 'all'
        ? _colonies
        : _colonies.where((c) => c['status'] == _filter).toList();

    return Scaffold(
      appBar: AppBar(
        title: const Text('Colony Map'),
        backgroundColor: Colors.blue.shade700,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: Icon(_showList ? Icons.map : Icons.list),
            onPressed: () => setState(() => _showList = !_showList),
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _showList
          ? _buildListView(filteredColonies)
          : _buildMapView(filteredColonies),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _showFilterSheet(),
        icon: const Icon(Icons.filter_list),
        label: const Text('Filter'),
        backgroundColor: Colors.blue.shade700,
        foregroundColor: Colors.white,
      ),
    );
  }

  Widget _buildMapView(List<Map<String, dynamic>> colonies) {
    return Stack(
      children: [
        Container(
          color: Colors.grey.shade200,
          child: Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.map, size: 120, color: Colors.grey.shade400),
                const SizedBox(height: 24),
                Text(
                  'Colony Locations',
                  style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: Colors.grey.shade600,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  '${colonies.length} colonies found\nTap list view for details',
                  textAlign: TextAlign.center,
                  style: TextStyle(color: Colors.grey.shade500),
                ),
                const SizedBox(height: 32),
                ElevatedButton.icon(
                  onPressed: () => setState(() => _showList = true),
                  icon: const Icon(Icons.list),
                  label: const Text('Show List View'),
                ),
              ],
            ),
          ),
        ),
        ..._buildSimulatedMarkers(colonies),
        if (_selectedColonyId != null)
          Positioned(
            bottom: 80,
            left: 16,
            right: 16,
            child: _buildSelectedColonyCard(colonies),
          ),
      ],
    );
  }

  List<Widget> _buildSimulatedMarkers(List<Map<String, dynamic>> colonies) {
    return colonies.asMap().entries.map((entry) {
      final int index = entry.key;
      final Map<String, dynamic> colony = entry.value;
      final isSelected = _selectedColonyId == (colony['id'] as String);
      return Positioned(
        left: 50 + (index * 70).toDouble(),
        top: 100 + (index * 50).toDouble(),
        child: GestureDetector(
          onTap: () =>
              setState(() => _selectedColonyId = colony['id'] as String),
          child: AnimatedContainer(
            duration: const Duration(milliseconds: 200),
            transform: isSelected
                ? (Matrix4.identity()..scale(1.2, 1.2, 1.2))
                : Matrix4.identity(),
            child: Column(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 4,
                  ),
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

  Widget _buildSelectedColonyCard(List<Map<String, dynamic>> colonies) {
    final colony = colonies.firstWhere(
      (c) => (c['id'] as String) == _selectedColonyId,
      orElse: () => colonies.first,
    );

    return Card(
      elevation: 8,
      child: Padding(
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
                          Icon(
                            Icons.location_on,
                            size: 14,
                            color: Colors.grey.shade600,
                          ),
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
            Row(
              children: [
                Expanded(
                  child: ElevatedButton.icon(
                    onPressed: () =>
                        context.push('/colony-detail/${colony['id']}'),
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
                    onPressed: () async {
                      final name = '${colony['name'] ?? ''}';
                      final location = '${colony['location'] ?? ''}';
                      final query = Uri.encodeComponent(
                        '$name $location Gorakhpur',
                      );
                      final url =
                          'https://www.google.com/maps/search/?api=1&query=$query';
                      final uri = Uri.parse(url);
                      if (await canLaunchUrl(uri)) {
                        await launchUrl(
                          uri,
                          mode: LaunchMode.externalApplication,
                        );
                      }
                    },
                    icon: const Icon(Icons.directions, size: 18),
                    label: const Text('Directions'),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildListView(List<Map<String, dynamic>> colonies) {
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: colonies.length,
      itemBuilder: (context, index) {
        final colony = colonies[index];
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
                            Icon(
                              Icons.location_on,
                              size: 14,
                              color: Colors.grey.shade600,
                            ),
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
                              padding: const EdgeInsets.symmetric(
                                horizontal: 8,
                                vertical: 4,
                              ),
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
                              '${colony['plots'] ?? 0} plots',
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
      builder: (context) => Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Filter Colonies',
              style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            ListTile(
              leading: const Icon(Icons.all_inclusive),
              title: const Text('All Colonies'),
              trailing: _filter == 'all' ? const Icon(Icons.check) : null,
              onTap: () {
                setState(() => _filter = 'all');
                Navigator.pop(context);
              },
            ),
            ListTile(
              leading: const Icon(Icons.check_circle, color: Colors.green),
              title: const Text('Active Projects'),
              trailing: _filter == 'active' ? const Icon(Icons.check) : null,
              onTap: () {
                setState(() => _filter = 'active');
                Navigator.pop(context);
              },
            ),
            ListTile(
              leading: const Icon(Icons.upcoming, color: Colors.orange),
              title: const Text('Upcoming Projects'),
              trailing: _filter == 'upcoming' ? const Icon(Icons.check) : null,
              onTap: () {
                setState(() => _filter = 'upcoming');
                Navigator.pop(context);
              },
            ),
          ],
        ),
      ),
    );
  }

  static final List<Map<String, dynamic>> _mockColonies = [
    {
      'id': '1',
      'name': 'Suryoday Heights Phase 1',
      'location': 'Gorakhpur',
      'plots': 120,
      'price': '₹3,000/sqft',
      'status': 'active',
    },
    {
      'id': '2',
      'name': 'Raghunath City Center',
      'location': 'Gorakhpur',
      'plots': 80,
      'price': '₹3,500/sqft',
      'status': 'active',
    },
    {
      'id': '3',
      'name': 'Braj Radha Enclave',
      'location': 'Lucknow',
      'plots': 200,
      'price': '₹4,200/sqft',
      'status': 'active',
    },
    {
      'id': '4',
      'name': 'Budh Bihar Colony',
      'location': 'Kushinagar',
      'plots': 60,
      'price': '₹2,800/sqft',
      'status': 'active',
    },
    {
      'id': '5',
      'name': 'Ganga Nagri',
      'location': 'Varanasi',
      'plots': 150,
      'price': '₹3,800/sqft',
      'status': 'active',
    },
  ];
}

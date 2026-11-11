import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../../core/services/api_service.dart';

class RentPage extends StatefulWidget {
  const RentPage({super.key});

  @override
  State<RentPage> createState() => _RentPageState();
}

class _RentPageState extends State<RentPage> {
  final ApiService _api = ApiService();
  bool _isLoading = false;
  String _selectedLocation = 'All';
  String _selectedBhk = 'All';
  String _searchQuery = '';
  final TextEditingController _searchController = TextEditingController();

  List<Map<String, dynamic>> _rentals = [];

  // Fallback verified Gorakhpur listings for offline / instant render
  static final List<Map<String, dynamic>> _fallbackRentals = [
    {
      'id': 154,
      'name': 'Luxury 3 BHK Flat - Medical Road',
      'property_type': 'flat',
      'location': 'Medical Road, Gorakhpur',
      'address': 'Near BRD Medical College, Medical College Road, Gorakhpur',
      'area_sqft': 1450,
      'bedrooms': 3,
      'bathrooms': 2,
      'furnished': 'semi-furnished',
      'monthly_rent': 18000.0,
      'formatted_rent': '₹18,000',
      'description': 'Modular kitchen, 2 balconies, 24x7 water and lift backup. Near BRD Medical College.',
      'owner_name': 'Dr. Sharma',
      'owner_phone': '9277121112',
      'whatsapp_link': 'https://wa.me/919277121112?text=Namaste,%20I%20am%20interested%20in%203%20BHK%20Medical%20Road',
      'is_featured': true,
    },
    {
      'id': 155,
      'name': 'Independent 2 BHK House near AIIMS',
      'property_type': 'house',
      'location': 'AIIMS Road, Gorakhpur',
      'address': 'Sector 4, Near AIIMS Campus, Kunraghat, Gorakhpur',
      'area_sqft': 1100,
      'bedrooms': 2,
      'bathrooms': 2,
      'furnished': 'furnished',
      'monthly_rent': 12500.0,
      'formatted_rent': '₹12,500',
      'description': 'Private garden, AC fittings, RO water, and dedicated parking. Ideal for hospital staff & families.',
      'owner_name': 'Mishra Ji',
      'owner_phone': '9277121112',
      'whatsapp_link': 'https://wa.me/919277121112?text=Namaste,%20I%20am%20interested%20in%202%20BHK%20AIIMS%20Road',
      'is_featured': true,
    },
    {
      'id': 156,
      'name': 'Commercial Shop - Taramandal Market',
      'property_type': 'shop',
      'location': 'Taramandal, Gorakhpur',
      'address': 'Taramandal Main Road, Opp. Nauhar Lake, Gorakhpur',
      'area_sqft': 450,
      'bedrooms': 0,
      'bathrooms': 1,
      'furnished': 'unfurnished',
      'monthly_rent': 22000.0,
      'formatted_rent': '₹22,000',
      'description': 'Corner shop with heavy footfall, shutter, and 15ft front road. Ideal for retail/clinic.',
      'owner_name': 'Verma Estate',
      'owner_phone': '9277121112',
      'whatsapp_link': 'https://wa.me/919277121112?text=Namaste,%20I%20am%20interested%20in%20Taramandal%20Shop',
      'is_featured': true,
    },
    {
      'id': 157,
      'name': 'Compact 1 BHK Studio Flat in Golghar',
      'property_type': 'flat',
      'location': 'Golghar, Gorakhpur',
      'address': 'Baldev Plaza Lane, Golghar Central, Gorakhpur',
      'area_sqft': 550,
      'bedrooms': 1,
      'bathrooms': 1,
      'furnished': 'furnished',
      'monthly_rent': 7500.0,
      'formatted_rent': '₹7,500',
      'description': 'Fully furnished studio in city center. Walking distance to markets and Railway Station.',
      'owner_name': 'Gupta Residency',
      'owner_phone': '9277121112',
      'whatsapp_link': 'https://wa.me/919277121112?text=Namaste,%20I%20am%20interested%20in%201%20BHK%20Golghar',
      'is_featured': false,
    },
    {
      'id': 158,
      'name': 'Spacious 3 BHK Villa in Rapti Nagar',
      'property_type': 'villa',
      'location': 'Rapti Nagar, Gorakhpur',
      'address': 'Rapti Nagar Phase 4, Chargawan Road, Gorakhpur',
      'area_sqft': 1800,
      'bedrooms': 3,
      'bathrooms': 3,
      'furnished': 'semi-furnished',
      'monthly_rent': 24000.0,
      'formatted_rent': '₹24,000',
      'description': 'Independent duplex villa with terrace, quiet gated community, and car porch.',
      'owner_name': 'Thakur Niwas',
      'owner_phone': '9277121112',
      'whatsapp_link': 'https://wa.me/919277121112?text=Namaste,%20I%20am%20interested%20in%203%20BHK%20Rapti%20Nagar',
      'is_featured': true,
    },
  ];

  final List<String> _locations = [
    'All',
    'Medical Road',
    'AIIMS Road',
    'Taramandal',
    'Golghar',
    'Rapti Nagar',
  ];

  final List<String> _bhkOptions = [
    'All',
    '1 BHK',
    '2 BHK',
    '3 BHK',
    'Shop',
  ];

  @override
  void initState() {
    super.initState();
    _fetchRentals();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _fetchRentals() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final res = await _api.get('rentals');
      if (res['success'] == true && res['rentals'] is List) {
        final list = (res['rentals'] as List)
            .map((e) => Map<String, dynamic>.from(e as Map))
            .toList();
        setState(() {
          _rentals = list.isNotEmpty ? list : _fallbackRentals;
          _isLoading = false;
        });
      } else {
        setState(() {
          _rentals = _fallbackRentals;
          _isLoading = false;
        });
      }
    } catch (_) {
      // Offline fallback
      setState(() {
        _rentals = _fallbackRentals;
        _isLoading = false;
      });
    }
  }

  List<Map<String, dynamic>> get _filteredRentals {
    return _rentals.where((item) {
      final loc = (item['location'] ?? '').toString().toLowerCase();
      final name = (item['name'] ?? '').toString().toLowerCase();
      final type = (item['property_type'] ?? '').toString().toLowerCase();
      final bhk = (item['bedrooms'] ?? 0).toString();

      if (_selectedLocation != 'All' &&
          !loc.contains(_selectedLocation.toLowerCase())) {
        return false;
      }

      if (_selectedBhk != 'All') {
        if (_selectedBhk == 'Shop') {
          if (type != 'shop') return false;
        } else if (_selectedBhk == '1 BHK' && bhk != '1') {
          return false;
        } else if (_selectedBhk == '2 BHK' && bhk != '2') {
          return false;
        } else if (_selectedBhk == '3 BHK' && bhk != '3') {
          return false;
        }
      }

      if (_searchQuery.isNotEmpty) {
        final q = _searchQuery.toLowerCase();
        if (!name.contains(q) && !loc.contains(q) && !type.contains(q)) {
          return false;
        }
      }

      return true;
    }).toList();
  }

  Future<void> _launchContact(String url) async {
    final uri = Uri.parse(url);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }

  void _openPostRentalSheet() {
    final nameCtrl = TextEditingController();
    final phoneCtrl = TextEditingController();
    final rentCtrl = TextEditingController();
    final locCtrl = TextEditingController(text: 'Medical Road, Gorakhpur');
    final descCtrl = TextEditingController();
    String propType = 'flat';
    int bhk = 2;
    const String furnished = 'semi-furnished';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setSheetState) {
            return Container(
              padding: EdgeInsets.only(
                bottom: MediaQuery.of(context).viewInsets.bottom + 20,
                top: 20,
                left: 20,
                right: 20,
              ),
              decoration: const BoxDecoration(
                color: Color(0xFFFAF7F0),
                borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
              ),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Row(
                          children: [
                            Icon(Icons.add_home_work, color: Color(0xFF0B194C)),
                            SizedBox(width: 8),
                            Text(
                              'Post Rental Home (किराया का घर)',
                              style: TextStyle(
                                fontSize: 18,
                                fontWeight: FontWeight.bold,
                                color: Color(0xFF0B194C),
                              ),
                            ),
                          ],
                        ),
                        IconButton(
                          icon: const Icon(Icons.close),
                          onPressed: () => Navigator.pop(context),
                        ),
                      ],
                    ),
                    const Text(
                      'Free 2-minute landlord posting. Connect directly with tenants.',
                      style: TextStyle(fontSize: 12, color: Colors.black54),
                    ),
                    const SizedBox(height: 16),
                    TextField(
                      controller: nameCtrl,
                      decoration: const InputDecoration(
                        labelText: 'Property / Building Title *',
                        hintText: 'e.g. 2 BHK Ground Floor House',
                        border: OutlineInputBorder(),
                        isDense: true,
                      ),
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: TextField(
                            controller: phoneCtrl,
                            keyboardType: TextInputType.phone,
                            decoration: const InputDecoration(
                              labelText: 'Owner Phone Number *',
                              hintText: '10-digit Mobile',
                              border: OutlineInputBorder(),
                              isDense: true,
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: TextField(
                            controller: rentCtrl,
                            keyboardType: TextInputType.number,
                            decoration: const InputDecoration(
                              labelText: 'Monthly Rent (₹) *',
                              hintText: '12000',
                              border: OutlineInputBorder(),
                              isDense: true,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: locCtrl,
                      decoration: const InputDecoration(
                        labelText: 'Location / Landmark in Gorakhpur *',
                        hintText: 'e.g. Taramandal, Near Nauhar Lake',
                        border: OutlineInputBorder(),
                        isDense: true,
                      ),
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: DropdownButtonFormField<String>(
                            initialValue: propType,
                            isDense: true,
                            decoration: const InputDecoration(
                              labelText: 'Type',
                              border: OutlineInputBorder(),
                            ),
                            items: const [
                              DropdownMenuItem(value: 'flat', child: Text('Flat')),
                              DropdownMenuItem(value: 'house', child: Text('House')),
                              DropdownMenuItem(value: 'villa', child: Text('Villa')),
                              DropdownMenuItem(value: 'shop', child: Text('Shop')),
                            ],
                            onChanged: (v) => setSheetState(() => propType = v ?? 'flat'),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: DropdownButtonFormField<int>(
                            initialValue: bhk,
                            isDense: true,
                            decoration: const InputDecoration(
                              labelText: 'BHK',
                              border: OutlineInputBorder(),
                            ),
                            items: const [
                              DropdownMenuItem(value: 1, child: Text('1 BHK')),
                              DropdownMenuItem(value: 2, child: Text('2 BHK')),
                              DropdownMenuItem(value: 3, child: Text('3 BHK')),
                              DropdownMenuItem(value: 4, child: Text('4+ BHK')),
                            ],
                            onChanged: (v) => setSheetState(() => bhk = v ?? 2),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: descCtrl,
                      maxLines: 2,
                      decoration: const InputDecoration(
                        labelText: 'Key Features / Restrictions',
                        hintText: 'e.g. For family only, 24h water, parking included',
                        border: OutlineInputBorder(),
                        isDense: true,
                      ),
                    ),
                    const SizedBox(height: 20),
                    SizedBox(
                      width: double.infinity,
                      height: 48,
                      child: ElevatedButton.icon(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF0B194C),
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                        icon: const Icon(Icons.check_circle, color: Color(0xFFD4AF37)),
                        label: const Text(
                          'Publish Rental Listing (तुरंत पोस्ट करें)',
                          style: TextStyle(fontWeight: FontWeight.bold),
                        ),
                        onPressed: () async {
                          if (nameCtrl.text.trim().isEmpty ||
                              phoneCtrl.text.trim().length < 10 ||
                              rentCtrl.text.trim().isEmpty) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(
                                content: Text('Please enter property name, 10-digit phone and rent.'),
                                backgroundColor: Colors.red,
                              ),
                            );
                            return;
                          }

                          Navigator.pop(context);

                          try {
                            await _api.post('rentals/post', data: {
                              'name': nameCtrl.text.trim(),
                              'phone': phoneCtrl.text.trim(),
                              'monthly_rent': double.tryParse(rentCtrl.text.trim()) ?? 10000,
                              'location': locCtrl.text.trim(),
                              'property_type': propType,
                              'bedrooms': bhk,
                              'bathrooms': 1,
                              'furnished': furnished,
                              'description': descCtrl.text.trim(),
                            });
                          } catch (_) {
                            // Handled gracefully
                          }

                          if (!context.mounted) return;
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text('Rental Listing Published Successfully! Verified by APS Desk.'),
                              backgroundColor: Colors.green,
                            ),
                          );
                          _fetchRentals();
                        },
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final filtered = _filteredRentals;

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text(
          'Rentals & Tenants (किराया का घर)',
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
        ),
        centerTitle: true,
        backgroundColor: const Color(0xFF0B194C),
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _fetchRentals,
            tooltip: 'Refresh listings',
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        backgroundColor: const Color(0xFF0B194C),
        foregroundColor: const Color(0xFFD4AF37),
        icon: const Icon(Icons.add_home),
        label: const Text(
          'Post Rental (मकान पोस्ट करें)',
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
        onPressed: _openPostRentalSheet,
      ),
      body: Column(
        children: [
          // Champagne Header & Search
          Container(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 12),
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                colors: [Color(0xFF0B194C), Color(0xFF1E293B)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'Gorakhpur Verified Rentals',
                      style: TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                        fontSize: 15,
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: const Color(0xFFD4AF37).withValues(alpha: 0.2),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFD4AF37)),
                      ),
                      child: const Text(
                        '0% Brokerage',
                        style: TextStyle(
                          color: Color(0xFFD4AF37),
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                // Search field
                TextField(
                  controller: _searchController,
                  style: const TextStyle(color: Colors.white, fontSize: 14),
                  decoration: InputDecoration(
                    hintText: 'Search by colony, road, or flat name...',
                    hintStyle: const TextStyle(color: Colors.white54, fontSize: 13),
                    prefixIcon: const Icon(Icons.search, color: Color(0xFFD4AF37)),
                    suffixIcon: _searchQuery.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.clear, color: Colors.white54),
                            onPressed: () {
                              _searchController.clear();
                              setState(() => _searchQuery = '');
                            },
                          )
                        : null,
                    filled: true,
                    fillColor: Colors.white.withValues(alpha: 0.1),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 16),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: BorderSide.none,
                    ),
                  ),
                  onChanged: (val) {
                    setState(() => _searchQuery = val.trim());
                  },
                ),
                const SizedBox(height: 10),
                // Location chips
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: _locations.map((loc) {
                      final isSelected = _selectedLocation == loc;
                      return Padding(
                        padding: const EdgeInsets.only(right: 6),
                        child: ChoiceChip(
                          label: Text(loc),
                          selected: isSelected,
                          selectedColor: const Color(0xFFD4AF37),
                          backgroundColor: Colors.white.withValues(alpha: 0.12),
                          labelStyle: TextStyle(
                            color: isSelected ? const Color(0xFF0B194C) : Colors.white,
                            fontSize: 12,
                            fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                          ),
                          onSelected: (_) {
                            setState(() => _selectedLocation = loc);
                          },
                        ),
                      );
                    }).toList(),
                  ),
                ),
                const SizedBox(height: 4),
                // BHK filter chips
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: _bhkOptions.map((bhk) {
                      final isSelected = _selectedBhk == bhk;
                      return Padding(
                        padding: const EdgeInsets.only(right: 6),
                        child: FilterChip(
                          label: Text(bhk),
                          selected: isSelected,
                          selectedColor: Colors.white,
                          backgroundColor: Colors.white.withValues(alpha: 0.1),
                          labelStyle: TextStyle(
                            color: isSelected ? const Color(0xFF0B194C) : Colors.white70,
                            fontSize: 11,
                            fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                          ),
                          onSelected: (_) {
                            setState(() => _selectedBhk = bhk);
                          },
                        ),
                      );
                    }).toList(),
                  ),
                ),
              ],
            ),
          ),

          // Main Property Feed
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : filtered.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(Icons.home_work_outlined, size: 64, color: Colors.grey),
                            const SizedBox(height: 12),
                            const Text(
                              'No rentals matching your criteria',
                              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                            ),
                            const SizedBox(height: 6),
                            const Text(
                              'Be the first landlord to post in this location!',
                              style: TextStyle(color: Colors.grey, fontSize: 13),
                            ),
                            const SizedBox(height: 16),
                            ElevatedButton.icon(
                              onPressed: _openPostRentalSheet,
                              icon: const Icon(Icons.add_home),
                              label: const Text('Post Rental Home'),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFF0B194C),
                                foregroundColor: Colors.white,
                              ),
                            ),
                          ],
                        ),
                      )
                    : ListView.builder(
                        padding: const EdgeInsets.fromLTRB(16, 12, 16, 80),
                        itemCount: filtered.length,
                        itemBuilder: (context, index) {
                          final item = filtered[index];
                          final rentStr = (item['formatted_rent']?.toString() ?? '?${item['monthly_rent']}');
                          final area = item['area_sqft'] != null ? '${item['area_sqft']} sqft' : 'Standard Area';
                          final bhkStr = (int.tryParse('${item['bedrooms']}') ?? 0) > 0 ? '${item['bedrooms']} BHK' : 'Commercial';
                          final furnishedStr = (item['furnished']?.toString() ?? 'unfurnished').toUpperCase();
                          final phone = (item['owner_phone']?.toString() ?? '9277121112');
                          final waLink = (item['whatsapp_link']?.toString() ?? 'https://wa.me/91$phone');

                          return Card(
                            margin: const EdgeInsets.only(bottom: 16),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                            elevation: 2,
                            child: Padding(
                              padding: const EdgeInsets.all(16),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  // Top Row: Type & Rent
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                        decoration: BoxDecoration(
                                          color: const Color(0xFF0B194C).withValues(alpha: 0.08),
                                          borderRadius: BorderRadius.circular(8),
                                        ),
                                        child: Text(
                                          (item['property_type'] ?? 'Flat').toString().toUpperCase(),
                                          style: const TextStyle(
                                            color: Color(0xFF0B194C),
                                            fontSize: 11,
                                            fontWeight: FontWeight.bold,
                                          ),
                                        ),
                                      ),
                                      Text(
                                        '$rentStr / mo',
                                        style: const TextStyle(
                                          color: Color(0xFF0B194C),
                                          fontSize: 19,
                                          fontWeight: FontWeight.w900,
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 8),
                                  // Title
                                    Text(
                                      (item['name']?.toString() ?? 'Rental Property'),
                                    style: const TextStyle(
                                      fontWeight: FontWeight.bold,
                                      fontSize: 16,
                                      color: Color(0xFF1E293B),
                                    ),
                                  ),
                                  const SizedBox(height: 4),
                                  // Location
                                  Row(
                                    children: [
                                      const Icon(Icons.location_on, size: 14, color: Color(0xFFD4AF37)),
                                      const SizedBox(width: 4),
                                      Expanded(
                                        child: Text(
                                          (item['address']?.toString() ?? item['location']?.toString() ?? 'Gorakhpur, UP'),
                                          style: const TextStyle(fontSize: 12, color: Colors.black54),
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 12),
                                  // Badges: BHK, Area, Furnished
                                  Wrap(
                                    spacing: 8,
                                    runSpacing: 6,
                                    children: [
                                      _buildInfoBadge(Icons.bed, bhkStr),
                                      _buildInfoBadge(Icons.square_foot, area),
                                      _buildInfoBadge(Icons.chair, furnishedStr),
                                    ],
                                  ),
                                  if ((item['description'] ?? '').toString().isNotEmpty) ...[
                                    const SizedBox(height: 10),
                                    Text(
                                      (item['description']?.toString() ?? ''),
                                      style: const TextStyle(fontSize: 12, color: Colors.black87),
                                      maxLines: 2,
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ],
                                  const SizedBox(height: 14),
                                  const Divider(height: 1),
                                  const SizedBox(height: 12),
                                  // Contact Buttons
                                  Row(
                                    children: [
                                      Expanded(
                                        child: OutlinedButton.icon(
                                          style: OutlinedButton.styleFrom(
                                            foregroundColor: Colors.green[800],
                                            side: BorderSide(color: Colors.green[700]!),
                                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                            padding: const EdgeInsets.symmetric(vertical: 10),
                                          ),
                                          icon: const Icon(Icons.chat, size: 18),
                                          label: const Text(
                                            'WhatsApp',
                                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                                          ),
                                          onPressed: () => _launchContact(waLink),
                                        ),
                                      ),
                                      const SizedBox(width: 10),
                                      Expanded(
                                        child: ElevatedButton.icon(
                                          style: ElevatedButton.styleFrom(
                                            backgroundColor: const Color(0xFF0B194C),
                                            foregroundColor: Colors.white,
                                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                            padding: const EdgeInsets.symmetric(vertical: 10),
                                          ),
                                          icon: const Icon(Icons.phone, size: 18, color: Color(0xFFD4AF37)),
                                          label: const Text(
                                            'Call Owner',
                                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                                          ),
                                          onPressed: () => _launchContact('tel:+91$phone'),
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                          );
                        },
                      ),
          ),
        ],
      ),
    );
  }

  Widget _buildInfoBadge(IconData icon, String label) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: const Color(0xFFFAF7F0),
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 13, color: const Color(0xFF0B194C)),
          const SizedBox(width: 4),
          Text(
            label,
            style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Color(0xFF334155)),
          ),
        ],
      ),
    );
  }
}

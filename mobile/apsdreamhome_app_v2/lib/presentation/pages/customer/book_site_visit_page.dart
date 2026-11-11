import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';

class BookSiteVisitPage extends StatefulWidget {
  final int? initialColonyId;
  const BookSiteVisitPage({super.key, this.initialColonyId});

  @override
  State<BookSiteVisitPage> createState() => _BookSiteVisitPageState();
}

class _BookSiteVisitPageState extends State<BookSiteVisitPage> {
  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  final _pickupAddressController = TextEditingController();
  final _notesController = TextEditingController();

  int _selectedColonyId = 1;
  DateTime _selectedDate = DateTime.now().add(const Duration(days: 1));
  String _selectedSlot = '10:00 AM';
  bool _pickupRequired = true;
  String _selectedPickupPoint = 'Gorakhpur Railway Station';
  bool _isSubmitting = false;

  final List<Map<String, dynamic>> _colonies = [
    {'id': 1, 'name': 'Suryoday Colony (Medical Road)'},
    {'id': 2, 'name': 'Braj Radha Nagari (Vrindavan Heritage)'},
    {'id': 3, 'name': 'Raghunath Nagari (City Center)'},
    {'id': 4, 'name': 'Budh Bihar (Kushinagar Highway)'},
    {'id': 5, 'name': 'Awadh Vihar (Deoria Bypass)'},
    {'id': 6, 'name': 'Chandra Green (Forest View)'},
    {'id': 7, 'name': 'Shivpuri Enclave (Gated Enclave)'},
  ];

  final List<String> _timeSlots = ['10:00 AM', '12:00 PM', '02:30 PM', '04:30 PM'];
  final List<String> _pickupPoints = [
    'Gorakhpur Railway Station',
    'Gorakhpur Airport (GOP)',
    'Mohaddipur Chauraha',
    'Asuran Chowk',
    'Karamha / Bargadwa',
    'Custom Address',
  ];

  @override
  void initState() {
    super.initState();
    if (widget.initialColonyId != null) {
      _selectedColonyId = widget.initialColonyId!;
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _pickupAddressController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _submitBooking() async {
    final name = _nameController.text.trim();
    final phone = _phoneController.text.trim();

    if (name.isEmpty || phone.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please enter your Name and Mobile Number')),
      );
      return;
    }

    setState(() => _isSubmitting = true);
    final api = ApiService();
    AppConstants.initBaseUrl();

    final pickupLocation = _selectedPickupPoint == 'Custom Address'
        ? _pickupAddressController.text.trim()
        : _selectedPickupPoint;

    try {
      final dateStr = DateFormat('yyyy-MM-dd').format(_selectedDate);
      final res = await api.post('/api/v2/mobile/site-visits/book', data: {
        'colony_id': _selectedColonyId,
        'visitor_name': name,
        'visitor_phone': phone,
        'visit_date': dateStr,
        'visit_time': _selectedSlot,
        'pickup_required': _pickupRequired ? 1 : 0,
        'pickup_location': _pickupRequired ? pickupLocation : '',
        'notes': _notesController.text.trim(),
      });

      if (mounted) {
        showDialog(
          context: context,
          builder: (ctx) => AlertDialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
            title: const Row(
              children: [
                Icon(Icons.check_circle, color: Color(0xFF10B981), size: 28),
                SizedBox(width: 10),
                Text('Visit Scheduled!', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 18)),
              ],
            ),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  (res['message'] as String?) ?? 'Your free site visit has been scheduled.',
                  style: const TextStyle(fontSize: 14),
                ),
                const SizedBox(height: 12),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFAF7F0),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFE8DFD0)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('📅 Date: $dateStr at $_selectedSlot', style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                      if (_pickupRequired) ...[
                        const SizedBox(height: 4),
                        Text('🚗 Pickup: $pickupLocation', style: const TextStyle(fontSize: 13, color: Color(0xFF0B194C))),
                      ],
                    ],
                  ),
                ),
              ],
            ),
            actions: [
              TextButton(
                onPressed: () {
                  Navigator.pop(ctx);
                  Navigator.pop(context);
                },
                child: const Text('OK', style: TextStyle(fontWeight: FontWeight.w700, color: Color(0xFF0B194C))),
              ),
            ],
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Booking error: $e')));
      }
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F0),
      appBar: AppBar(
        title: const Text('Book Free Site Visit', style: TextStyle(fontWeight: FontWeight.w700, color: Color(0xFF0B194C))),
        backgroundColor: Colors.white,
        elevation: 0.5,
        iconTheme: const IconThemeData(color: Color(0xFF0B194C)),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Trust Banner
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF0B194C), Color(0xFF1E3A8A)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(16),
                boxShadow: [
                  BoxShadow(color: const Color(0xFF0B194C).withValues(alpha: 0.2), blurRadius: 10, offset: const Offset(0, 4)),
                ],
              ),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: const Color(0xFFD4AF37).withValues(alpha: 0.2),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.directions_car, color: Color(0xFFD4AF37), size: 28),
                  ),
                  const SizedBox(width: 14),
                  const Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Complimentary AC Car Pickup',
                          style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 16),
                        ),
                        SizedBox(height: 4),
                        Text(
                          'Doorstep pickup & drop from Station, Airport, or Home. Free guided township walkthrough with plot advisor.',
                          style: TextStyle(color: Colors.white70, fontSize: 12),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // Select Township
            _buildSectionHeader('1. Select Township / Colony'),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFE8DFD0)),
              ),
              child: DropdownButtonHideUnderline(
                child: DropdownButton<int>(
                  isExpanded: true,
                  value: _selectedColonyId,
                  items: _colonies.map((c) {
                    return DropdownMenuItem<int>(
                      value: c['id'] as int,
                      child: Text(c['name'] as String, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: Color(0xFF0B194C))),
                    );
                  }).toList(),
                  onChanged: (val) {
                    if (val != null) setState(() => _selectedColonyId = val);
                  },
                ),
              ),
            ),
            const SizedBox(height: 20),

            // Date & Time
            _buildSectionHeader('2. Preferred Date & Time Slot'),
            Row(
              children: [
                Expanded(
                  child: InkWell(
                    onTap: () async {
                      final picked = await showDatePicker(
                        context: context,
                        initialDate: _selectedDate,
                        firstDate: DateTime.now(),
                        lastDate: DateTime.now().add(const Duration(days: 60)),
                      );
                      if (picked != null) setState(() => _selectedDate = picked);
                    },
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFE8DFD0)),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(DateFormat('dd MMM yyyy').format(_selectedDate), style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14)),
                          const Icon(Icons.calendar_today, size: 18, color: Color(0xFF0B194C)),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            Wrap(
              spacing: 8,
              children: _timeSlots.map((slot) {
                final isSel = _selectedSlot == slot;
                return ChoiceChip(
                  label: Text(slot),
                  selected: isSel,
                  onSelected: (val) {
                    if (val) setState(() => _selectedSlot = slot);
                  },
                  selectedColor: const Color(0xFF0B194C),
                  backgroundColor: Colors.white,
                  labelStyle: TextStyle(
                    color: isSel ? const Color(0xFFD4AF37) : const Color(0xFF1E293B),
                    fontWeight: FontWeight.w700,
                    fontSize: 12,
                  ),
                  side: BorderSide(color: isSel ? const Color(0xFF0B194C) : const Color(0xFFE8DFD0)),
                );
              }).toList(),
            ),
            const SizedBox(height: 20),

            // Pickup Option
            _buildSectionHeader('3. AC Vehicle Pickup Options'),
            Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFE8DFD0)),
              ),
              child: Material(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                child: SwitchListTile(
                  title: const Text('Require Free Car Pickup & Drop', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: Color(0xFF0B194C))),
                  subtitle: const Text('We provide comfortable sanitized vehicle pickup for you & family', style: TextStyle(fontSize: 11, color: Colors.grey)),
                  value: _pickupRequired,
                  activeThumbColor: const Color(0xFF0B194C),
                  onChanged: (val) => setState(() => _pickupRequired = val),
                ),
              ),
            ),
            if (_pickupRequired) ...[
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 14),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFE8DFD0)),
                ),
                child: DropdownButtonHideUnderline(
                  child: DropdownButton<String>(
                    isExpanded: true,
                    value: _selectedPickupPoint,
                    items: _pickupPoints.map((pt) {
                      return DropdownMenuItem<String>(
                        value: pt,
                        child: Text(pt, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500)),
                      );
                    }).toList(),
                    onChanged: (val) {
                      if (val != null) setState(() => _selectedPickupPoint = val);
                    },
                  ),
                ),
              ),
              if (_selectedPickupPoint == 'Custom Address') ...[
                const SizedBox(height: 10),
                TextField(
                  controller: _pickupAddressController,
                  decoration: InputDecoration(
                    hintText: 'Enter your pickup house / landmark address in Gorakhpur',
                    filled: true,
                    fillColor: Colors.white,
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE8DFD0))),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                  ),
                ),
              ],
            ],
            const SizedBox(height: 20),

            // Visitor Information
            _buildSectionHeader('4. Visitor Contact Details'),
            TextField(
              controller: _nameController,
              decoration: InputDecoration(
                labelText: 'Full Name *',
                filled: true,
                fillColor: Colors.white,
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE8DFD0))),
                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              ),
            ),
            const SizedBox(height: 10),
            TextField(
              controller: _phoneController,
              keyboardType: TextInputType.phone,
              decoration: InputDecoration(
                labelText: 'Mobile Number (WhatsApp) *',
                filled: true,
                fillColor: Colors.white,
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE8DFD0))),
                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              ),
            ),
            const SizedBox(height: 24),

            // Submit Button
            SizedBox(
              width: double.infinity,
              height: 52,
              child: ElevatedButton.icon(
                onPressed: _isSubmitting ? null : _submitBooking,
                icon: const Icon(Icons.directions_car_filled),
                label: _isSubmitting
                    ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Color(0xFFD4AF37), strokeWidth: 2))
                    : const Text('Confirm Free Site Visit', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF0B194C),
                  foregroundColor: const Color(0xFFD4AF37),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  elevation: 2,
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Instant WhatsApp Hotline Callout
            Center(
              child: InkWell(
                onTap: () async {
                  final uri = Uri.parse('https://wa.me/917007444842?text=Hello%20APS%20Dream%20Home,%20I%20want%20to%20book%20a%20site%20visit.');
                  if (await canLaunchUrl(uri)) await launchUrl(uri, mode: LaunchMode.externalApplication);
                },
                child: const Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.chat, color: Color(0xFF25D366), size: 18),
                    SizedBox(width: 6),
                    Text(
                      'Need urgent booking? WhatsApp +91 7007444842',
                      style: TextStyle(color: Color(0xFF0B194C), fontWeight: FontWeight.w600, fontSize: 13),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }

  Widget _buildSectionHeader(String title) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Text(
        title,
        style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: Color(0xFF0B194C)),
      ),
    );
  }
}

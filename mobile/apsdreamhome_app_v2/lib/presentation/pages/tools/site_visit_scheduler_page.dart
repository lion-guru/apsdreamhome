import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

/// Site Visit Scheduler Page
/// Book site visits with automatic agent assignment
class SiteVisitSchedulerPage extends StatefulWidget {
  const SiteVisitSchedulerPage({super.key});

  @override
  State<SiteVisitSchedulerPage> createState() => _SiteVisitSchedulerPageState();
}

class _SiteVisitSchedulerPageState extends State<SiteVisitSchedulerPage> {
  // Form controllers
  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  final _pickupAddressController = TextEditingController();
  final _notesController = TextEditingController();

  // State
  DateTime? _selectedDate;
  String? _selectedTimeSlot;
  String? _selectedColony;
  int _guestCount = 1;
  bool _needPickup = false;
  bool _isSubmitting = false;

  // Sample colonies (would come from database)
  final List<Map<String, dynamic>> _colonies = [
    {
      'id': '1',
      'name': 'Suryoday Heights Phase 1',
      'location': 'Gorakhpur',
      'distance': '5 km'
    },
    {
      'id': '2',
      'name': 'Raghunath City Center',
      'location': 'Gorakhpur',
      'distance': '8 km'
    },
    {
      'id': '3',
      'name': 'Braj Radha Enclave',
      'location': 'Lucknow',
      'distance': '120 km'
    },
    {
      'id': '4',
      'name': 'Budh Bihar Colony',
      'location': 'Kushinagar',
      'distance': '45 km'
    },
    {
      'id': '5',
      'name': 'Ganga Nagri',
      'location': 'Varanasi',
      'distance': '180 km'
    },
  ];

  // Time slots
  final List<String> _timeSlots = [
    '09:00 AM - 11:00 AM',
    '11:00 AM - 01:00 PM',
    '02:00 PM - 04:00 PM',
    '04:00 PM - 06:00 PM',
  ];

  // Sample assigned agent
  final Map<String, dynamic> _assignedAgent = {
    'name': 'Amit Sharma',
    'phone': '+91 98765 43210',
    'photo': null,
    'rating': 4.8,
    'visits': 156,
    'experience': '3 years',
  };

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _pickupAddressController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _selectDate() async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: DateTime.now().add(const Duration(days: 1)),
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 30)),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: ColorScheme.light(
              primary: Colors.blue.shade700,
            ),
          ),
          child: child!,
        );
      },
    );

    if (picked != null) {
      setState(() {
        _selectedDate = picked;
      });
    }
  }

  Future<void> _submitBooking() async {
    if (!_validateForm()) return;

    setState(() {
      _isSubmitting = true;
    });

    // Simulate API call
    await Future.delayed(const Duration(seconds: 2));

    setState(() {
      _isSubmitting = false;
    });

    _showBookingConfirmation();
  }

  bool _validateForm() {
    if (_nameController.text.isEmpty) {
      _showError('Please enter your name');
      return false;
    }
    if (_phoneController.text.length != 10) {
      _showError('Please enter valid 10-digit phone number');
      return false;
    }
    if (_selectedColony == null) {
      _showError('Please select a colony to visit');
      return false;
    }
    if (_selectedDate == null) {
      _showError('Please select a date');
      return false;
    }
    if (_selectedTimeSlot == null) {
      _showError('Please select a time slot');
      return false;
    }
    if (_needPickup && _pickupAddressController.text.isEmpty) {
      _showError('Please enter pickup address');
      return false;
    }
    return true;
  }

  void _showBookingConfirmation() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        title: const Column(
          children: [
            Icon(Icons.check_circle, color: Colors.green, size: 64),
            SizedBox(height: 16),
            Text('Booking Confirmed!'),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              'Your site visit has been scheduled for ${DateFormat('dd MMM yyyy').format(_selectedDate!)} at $_selectedTimeSlot',
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.blue.shade50,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Column(
                children: [
                  const Text(
                    'Assigned Agent',
                    style: TextStyle(fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 8),
                  Text(_assignedAgent['name'] as String),
                  Text(
                    _assignedAgent['phone'] as String,
                    style: TextStyle(color: Colors.grey.shade600),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.green.shade50,
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.qr_code, color: Colors.green),
                  SizedBox(width: 8),
                  Text('Visit QR Code Generated'),
                ],
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              Navigator.pop(context);
            },
            child: const Text('Done'),
          ),
          ElevatedButton.icon(
            onPressed: () {
              // Add to calendar
              Navigator.pop(context);
              _showSuccess('Added to calendar!');
            },
            icon: const Icon(Icons.calendar_today),
            label: const Text('Add to Calendar'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Book Site Visit'),
        backgroundColor: Colors.blue.shade700,
        foregroundColor: Colors.white,
      ),
      body: _isSubmitting
          ? const Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  CircularProgressIndicator(),
                  SizedBox(height: 16),
                  Text('Confirming your visit...'),
                ],
              ),
            )
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Header
                  _buildHeader(),
                  const SizedBox(height: 24),

                  // Personal Details
                  _buildPersonalDetails(),
                  const SizedBox(height: 24),

                  // Colony Selection
                  _buildColonySelection(),
                  const SizedBox(height: 24),

                  // Date & Time Selection
                  _buildDateTimeSelection(),
                  const SizedBox(height: 24),

                  // Pickup Option
                  _buildPickupOption(),
                  const SizedBox(height: 24),

                  // Additional Notes
                  _buildNotesSection(),
                  const SizedBox(height: 24),

                  // Visit Summary
                  _buildVisitSummary(),
                  const SizedBox(height: 32),

                  // Submit Button
                  _buildSubmitButton(),
                  const SizedBox(height: 32),
                ],
              ),
            ),
    );
  }

  Widget _buildHeader() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [Colors.blue.shade700, Colors.blue.shade500],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(
                  Icons.directions_car,
                  color: Colors.white,
                  size: 32,
                ),
              ),
              const SizedBox(width: 16),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Free Site Visit',
                      style: TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                    SizedBox(height: 4),
                    Text(
                      'Visit any colony with our expert agent',
                      style: TextStyle(
                        fontSize: 14,
                        color: Colors.white70,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 20),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              _buildFeatureItem(Icons.local_taxi, 'Free Pickup'),
              _buildFeatureItem(Icons.person, 'Expert Agent'),
              _buildFeatureItem(Icons.schedule, 'Flexible Timing'),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildFeatureItem(IconData icon, String text) {
    return Column(
      children: [
        Icon(icon, color: Colors.white70, size: 24),
        const SizedBox(height: 4),
        Text(
          text,
          style: const TextStyle(color: Colors.white70, fontSize: 12),
        ),
      ],
    );
  }

  Widget _buildPersonalDetails() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Your Details',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),

            // Name
            TextField(
              controller: _nameController,
              decoration: InputDecoration(
                labelText: 'Full Name *',
                prefixIcon: const Icon(Icons.person),
                border:
                    OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
            const SizedBox(height: 16),

            // Phone
            TextField(
              controller: _phoneController,
              keyboardType: TextInputType.phone,
              maxLength: 10,
              decoration: InputDecoration(
                labelText: 'Phone Number *',
                prefixIcon: const Icon(Icons.phone),
                prefixText: '+91 ',
                border:
                    OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                counterText: '',
              ),
            ),
            const SizedBox(height: 16),

            // Guest Count
            Row(
              children: [
                const Text(
                  'Number of Guests:',
                  style: TextStyle(fontWeight: FontWeight.w500),
                ),
                const SizedBox(width: 16),
                Container(
                  decoration: BoxDecoration(
                    color: Colors.grey.shade100,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Row(
                    children: [
                      IconButton(
                        icon: const Icon(Icons.remove),
                        onPressed: _guestCount > 1
                            ? () => setState(() => _guestCount--)
                            : null,
                      ),
                      Text(
                        '$_guestCount',
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      IconButton(
                        icon: const Icon(Icons.add),
                        onPressed: _guestCount < 5
                            ? () => setState(() => _guestCount++)
                            : null,
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildColonySelection() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Select Colony to Visit *',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),
            ..._colonies.map((colony) {
              final isSelected = _selectedColony == colony['id'];
              final String colonyName = colony['name'] as String;
              final String location = colony['location'] as String;
              final String distance = colony['distance'] as String;
              return Container(
                margin: const EdgeInsets.only(bottom: 12),
                child: InkWell(
                  onTap: () {
                    setState(() {
                      _selectedColony = colony['id'] as String;
                    });
                  },
                  borderRadius: BorderRadius.circular(12),
                  child: Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: isSelected
                          ? Colors.blue.shade50
                          : Colors.grey.shade50,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: isSelected
                            ? Colors.blue.shade300
                            : Colors.grey.shade300,
                        width: isSelected ? 2 : 1,
                      ),
                    ),
                    child: Row(
                      children: [
                        Container(
                          width: 60,
                          height: 60,
                          decoration: BoxDecoration(
                            color: isSelected
                                ? Colors.blue.shade100
                                : Colors.grey.shade200,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Icon(
                            Icons.location_city,
                            color:
                                isSelected ? Colors.blue.shade700 : Colors.grey,
                            size: 32,
                          ),
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                colonyName,
                                style: const TextStyle(
                                    fontWeight: FontWeight.bold),
                              ),
                              const SizedBox(height: 4),
                              Row(
                                children: [
                                  Icon(Icons.location_on,
                                      size: 14, color: Colors.grey.shade600),
                                  const SizedBox(width: 4),
                                  Text(
                                    '$location • $distance away',
                                    style: TextStyle(
                                      fontSize: 13,
                                      color: Colors.grey.shade600,
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                        if (isSelected)
                          Icon(Icons.check_circle, color: Colors.blue.shade700),
                      ],
                    ),
                  ),
                ),
              );
            }),
          ],
        ),
      ),
    );
  }

  Widget _buildDateTimeSelection() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Select Date & Time *',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),

            // Date Picker
            InkWell(
              onTap: _selectDate,
              child: Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.grey.shade50,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.grey.shade300),
                ),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.blue.shade100,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Icon(Icons.calendar_today,
                          color: Colors.blue.shade700),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Visit Date',
                            style: TextStyle(fontWeight: FontWeight.bold),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            _selectedDate != null
                                ? DateFormat('EEEE, dd MMM yyyy')
                                    .format(_selectedDate!)
                                : 'Select a date',
                            style: TextStyle(
                              color: _selectedDate != null
                                  ? Colors.black
                                  : Colors.grey,
                            ),
                          ),
                        ],
                      ),
                    ),
                    Icon(Icons.chevron_right, color: Colors.grey.shade400),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Time Slots
            if (_selectedDate != null)
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Available Time Slots',
                    style: TextStyle(fontWeight: FontWeight.w500),
                  ),
                  const SizedBox(height: 12),
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: _timeSlots.map((slot) {
                      final isSelected = _selectedTimeSlot == slot;
                      return ChoiceChip(
                        label: Text(slot),
                        selected: isSelected,
                        onSelected: (selected) {
                          setState(() {
                            _selectedTimeSlot = selected ? slot : null;
                          });
                        },
                        selectedColor: Colors.blue.shade100,
                        backgroundColor: Colors.grey.shade100,
                        labelStyle: TextStyle(
                          color:
                              isSelected ? Colors.blue.shade700 : Colors.black,
                          fontWeight:
                              isSelected ? FontWeight.bold : FontWeight.normal,
                        ),
                      );
                    }).toList(),
                  ),
                ],
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildPickupOption() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                const Icon(Icons.local_taxi),
                const SizedBox(width: 8),
                const Text(
                  'Need Pickup?',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const Spacer(),
                Switch(
                  value: _needPickup,
                  onChanged: (value) {
                    setState(() {
                      _needPickup = value;
                    });
                  },
                  activeThumbColor: Colors.blue.shade700,
                ),
              ],
            ),
            if (_needPickup) ...[
              const SizedBox(height: 16),
              const Text(
                'We offer free pickup service within city limits',
                style: TextStyle(color: Colors.grey, fontSize: 13),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _pickupAddressController,
                maxLines: 2,
                decoration: InputDecoration(
                  labelText: 'Pickup Address *',
                  prefixIcon: const Icon(Icons.location_on),
                  border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12)),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildNotesSection() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Additional Notes',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: _notesController,
              maxLines: 3,
              decoration: InputDecoration(
                hintText: 'Any special requirements or questions...',
                border:
                    OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildVisitSummary() {
    if (_selectedColony == null ||
        _selectedDate == null ||
        _selectedTimeSlot == null) {
      return const SizedBox.shrink();
    }

    final selectedColony =
        _colonies.firstWhere((c) => c['id'] == _selectedColony);
    final String colonyName = selectedColony['name'] as String;

    return Card(
      color: Colors.blue.shade50,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(Icons.summarize, color: Colors.blue),
                SizedBox(width: 8),
                Text(
                  'Visit Summary',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            _buildSummaryRow('Colony', colonyName),
            _buildSummaryRow(
                'Date', DateFormat('dd MMM yyyy').format(_selectedDate!)),
            _buildSummaryRow('Time', _selectedTimeSlot!),
            _buildSummaryRow('Guests',
                '$_guestCount ${_guestCount == 1 ? 'person' : 'people'}'),
            if (_needPickup) _buildSummaryRow('Pickup', 'Yes'),
            const Divider(height: 24),
            _buildSummaryRow(
              'Assigned Agent',
              '${_assignedAgent['name']} (${_assignedAgent['phone']})',
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSummaryRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 100,
            child: Text(
              '$label:',
              style: TextStyle(
                color: Colors.grey.shade600,
                fontSize: 13,
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(
                fontWeight: FontWeight.w500,
                fontSize: 13,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSubmitButton() {
    return ElevatedButton.icon(
      onPressed: _submitBooking,
      icon: const Icon(Icons.check_circle),
      label: const Text('Confirm Site Visit'),
      style: ElevatedButton.styleFrom(
        backgroundColor: Colors.blue.shade700,
        foregroundColor: Colors.white,
        minimumSize: const Size(double.infinity, 54),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
        ),
      ),
    );
  }

  void _showSuccess(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.check_circle, color: Colors.white),
            const SizedBox(width: 8),
            Text(message),
          ],
        ),
        backgroundColor: Colors.green,
      ),
    );
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.error, color: Colors.white),
            const SizedBox(width: 8),
            Text(message),
          ],
        ),
        backgroundColor: Colors.red,
      ),
    );
  }
}

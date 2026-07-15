import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/providers/auth_provider.dart';

class SiteVisitSchedulerPage extends ConsumerStatefulWidget {
  const SiteVisitSchedulerPage({super.key});

  @override
  ConsumerState<SiteVisitSchedulerPage> createState() =>
      _SiteVisitSchedulerPageState();
}

class _SiteVisitSchedulerPageState
    extends ConsumerState<SiteVisitSchedulerPage> {
  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  final _emailController = TextEditingController();
  final _pickupAddressController = TextEditingController();
  final _notesController = TextEditingController();

  DateTime? _selectedDate;
  String? _selectedTimeSlot;
  String? _selectedColony;
  int _guestCount = 1;
  bool _needPickup = false;
  bool _isSubmitting = false;
  bool _isLoadingColonies = true;
  bool _isLoadingSlots = false;

  List<Map<String, dynamic>> _colonies = [];
  List<Map<String, dynamic>> _timeSlots = [];

  Dio get _dio => Dio(BaseOptions(baseUrl: AppConstants.baseUrl));

  @override
  void initState() {
    super.initState();
    _fetchColonies();
    _prefillUserData();
  }

  void _prefillUserData() {
    final user = ref.read(authProvider);
    if (user != null) {
      _nameController.text = user.name ?? '';
      _phoneController.text = user.phone ?? '';
      _emailController.text = user.email ?? '';
    }
  }

  Future<void> _fetchColonies() async {
    try {
      final response = await _dio.get('/api/v2/mobile/colonies');
      if (response.data['success'] == true) {
        final data = response.data['data'] as List;
        setState(() {
          _colonies = data.cast<Map<String, dynamic>>();
          _isLoadingColonies = false;
        });
      } else {
        setState(() => _isLoadingColonies = false);
      }
    } catch (e) {
      setState(() => _isLoadingColonies = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to load colonies: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  Future<void> _fetchTimeSlots() async {
    if (_selectedDate == null) return;
    setState(() => _isLoadingSlots = true);
    try {
      final dateStr = DateFormat('yyyy-MM-dd').format(_selectedDate!);
      final response = await _dio.get(
        '/api/v2/mobile/site-visit/slots',
        queryParameters: {
          'date': dateStr,
          if (_selectedColony != null) 'colony_id': _selectedColony,
        },
      );
      if (response.data['success'] == true) {
        final slots = response.data['data']['slots'] as List;
        setState(() {
          _timeSlots = slots.cast<Map<String, dynamic>>();
          _isLoadingSlots = false;
          _selectedTimeSlot = null;
        });
      } else {
        setState(() => _isLoadingSlots = false);
      }
    } catch (e) {
      setState(() => _isLoadingSlots = false);
    }
  }

  Future<void> _selectDate() async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: DateTime.now().add(const Duration(days: 1)),
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 30)),
      builder: (context, child) => Theme(
        data: Theme.of(context).copyWith(
          colorScheme: ColorScheme.light(primary: Colors.blue.shade700),
        ),
        child: child!,
      ),
    );
    if (picked != null && picked != _selectedDate) {
      setState(() => _selectedDate = picked);
      _fetchTimeSlots();
    }
  }

  bool _validateForm() {
    if (_nameController.text.trim().isEmpty) {
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
    if (_needPickup && _pickupAddressController.text.trim().isEmpty) {
      _showError('Please enter pickup address');
      return false;
    }
    return true;
  }

  Future<void> _submitBooking() async {
    if (!_validateForm()) return;
    setState(() => _isSubmitting = true);

    try {
      final token = await ref.read(authProvider.notifier).getToken();
      final response = await _dio.post(
        '/api/v2/mobile/site-visit/book',
        data: {
          'colony_id': int.tryParse(_selectedColony ?? '') ?? 0,
          'visit_date': DateFormat('yyyy-MM-dd').format(_selectedDate!),
          'visit_time': _selectedTimeSlot!,
          'name': _nameController.text.trim(),
          'phone': _phoneController.text.trim(),
          'email': _emailController.text.trim(),
          'need_pickup': _needPickup,
          'pickup_address': _pickupAddressController.text.trim(),
          'guest_count': _guestCount,
          'notes': _notesController.text.trim(),
        },
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );

      final resData = response.data as Map<String, dynamic>;
      if (resData['success'] == true) {
        setState(() => _isSubmitting = false);
        _showBookingConfirmation(resData['data'] as Map<String, dynamic>);
      } else {
        setState(() => _isSubmitting = false);
        _showError((resData['message'] as String?) ?? 'Booking failed');
      }
    } catch (e) {
      setState(() => _isSubmitting = false);
      _showError('Booking failed: $e');
    }
  }

  void _showBookingConfirmation(Map<String, dynamic> data) {
    final colonyMatch = _colonies.firstWhere(
      (c) => c['id'].toString() == _selectedColony,
      orElse: () => {'name': 'N/A'},
    );
    final String colonyName = (colonyMatch['name'] as String?) ?? 'N/A';
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
              'Your site visit has been scheduled for ${DateFormat('dd MMM yyyy').format(_selectedDate!)} at ${data['visit_time'] ?? _selectedTimeSlot}',
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.blue.shade50,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Column(
                children: [
                  Text(
                    colonyName,
                    style: const TextStyle(fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Visit ID: #${data['visit_id']}',
                    style: TextStyle(color: Colors.grey.shade600, fontSize: 12),
                  ),
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
              Navigator.pop(context);
              Navigator.pushReplacementNamed(context, '/site-visits');
            },
            icon: const Icon(Icons.list),
            label: const Text('View My Visits'),
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
        actions: [
          IconButton(
            icon: const Icon(Icons.history),
            tooltip: 'My Visits',
            onPressed: () => Navigator.pushNamed(context, '/site-visits'),
          ),
        ],
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
                  _buildHeader(),
                  const SizedBox(height: 24),
                  _buildPersonalDetails(),
                  const SizedBox(height: 24),
                  _buildColonySelection(),
                  const SizedBox(height: 24),
                  _buildDateTimeSelection(),
                  const SizedBox(height: 24),
                  _buildPickupOption(),
                  const SizedBox(height: 24),
                  _buildNotesSection(),
                  const SizedBox(height: 24),
                  _buildVisitSummary(),
                  const SizedBox(height: 32),
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
                      style: TextStyle(fontSize: 14, color: Colors.white70),
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
        Text(text, style: const TextStyle(color: Colors.white70, fontSize: 12)),
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
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: _nameController,
              decoration: InputDecoration(
                labelText: 'Full Name *',
                prefixIcon: const Icon(Icons.person),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: _phoneController,
              keyboardType: TextInputType.phone,
              maxLength: 10,
              decoration: InputDecoration(
                labelText: 'Phone Number *',
                prefixIcon: const Icon(Icons.phone),
                prefixText: '+91 ',
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                counterText: '',
              ),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: _emailController,
              keyboardType: TextInputType.emailAddress,
              decoration: InputDecoration(
                labelText: 'Email (optional)',
                prefixIcon: const Icon(Icons.email),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
            const SizedBox(height: 16),
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
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            if (_isLoadingColonies)
              const Center(child: CircularProgressIndicator())
            else if (_colonies.isEmpty)
              const Text(
                'No colonies available',
                style: TextStyle(color: Colors.grey),
              )
            else
              ..._colonies.map((colony) {
                final isSelected = _selectedColony == colony['id'].toString();
                return Container(
                  margin: const EdgeInsets.only(bottom: 12),
                  child: InkWell(
                    onTap: () {
                      setState(() => _selectedColony = colony['id'].toString());
                      if (_selectedDate != null) _fetchTimeSlots();
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
                              color: isSelected
                                  ? Colors.blue.shade700
                                  : Colors.grey,
                              size: 32,
                            ),
                          ),
                          const SizedBox(width: 16),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  (colony['name'] as String?) ?? '',
                                  style: const TextStyle(
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
                                      (colony['district_name'] as String?) ??
                                          (colony['location'] as String?) ??
                                          '',
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
                            Icon(
                              Icons.check_circle,
                              color: Colors.blue.shade700,
                            ),
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
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
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
                      child: Icon(
                        Icons.calendar_today,
                        color: Colors.blue.shade700,
                      ),
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
                                ? DateFormat(
                                    'EEEE, dd MMM yyyy',
                                  ).format(_selectedDate!)
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
            if (_selectedDate != null) ...[
              Row(
                children: [
                  const Text(
                    'Available Time Slots',
                    style: TextStyle(fontWeight: FontWeight.w500),
                  ),
                  if (_isLoadingSlots) ...[
                    const SizedBox(width: 8),
                    const SizedBox(
                      width: 16,
                      height: 16,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    ),
                  ],
                ],
              ),
              const SizedBox(height: 12),
              if (_timeSlots.isEmpty && !_isLoadingSlots)
                const Text(
                  'No slots available for this date',
                  style: TextStyle(color: Colors.grey),
                )
              else
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: _timeSlots.map((slot) {
                    final String displayTime =
                        (slot['display_time'] as String?) ??
                        (slot['time_slot'] as String?) ??
                        '';
                    final bool isSelected =
                        _selectedTimeSlot == slot['time_slot'];
                    final int remaining = (slot['remaining'] is int)
                        ? slot['remaining'] as int
                        : int.tryParse('${slot['remaining']}') ?? 0;
                    return ChoiceChip(
                      label: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            displayTime,
                            style: TextStyle(
                              color: isSelected
                                  ? Colors.blue.shade700
                                  : Colors.black,
                              fontWeight: isSelected
                                  ? FontWeight.bold
                                  : FontWeight.normal,
                            ),
                          ),
                          if (remaining > 0)
                            Text(
                              '$remaining left',
                              style: TextStyle(
                                fontSize: 10,
                                color: Colors.grey.shade600,
                              ),
                            ),
                        ],
                      ),
                      selected: isSelected,
                      onSelected: (selected) {
                        setState(
                          () => _selectedTimeSlot = selected
                              ? (slot['time_slot'] as String?)
                              : null,
                        );
                      },
                      selectedColor: Colors.blue.shade100,
                      backgroundColor: Colors.grey.shade100,
                    );
                  }).toList(),
                ),
            ],
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
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                ),
                const Spacer(),
                Switch(
                  value: _needPickup,
                  onChanged: (value) => setState(() => _needPickup = value),
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
                    borderRadius: BorderRadius.circular(12),
                  ),
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
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: _notesController,
              maxLines: 3,
              decoration: InputDecoration(
                hintText: 'Any special requirements or questions...',
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
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
    final selectedColony = _colonies.firstWhere(
      (c) => c['id'].toString() == _selectedColony,
      orElse: () => {'name': 'N/A'},
    );
    final String displayTime =
        (_timeSlots.firstWhere(
              (s) => s['time_slot'] == _selectedTimeSlot,
              orElse: () => {'display_time': _selectedTimeSlot},
            )['display_time']
            as String?) ??
        _selectedTimeSlot ??
        '';

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
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              ],
            ),
            const SizedBox(height: 16),
            _buildSummaryRow(
              'Colony',
              (selectedColony['name'] as String?) ?? '',
            ),
            _buildSummaryRow(
              'Date',
              DateFormat('dd MMM yyyy').format(_selectedDate!),
            ),
            _buildSummaryRow('Time', displayTime),
            _buildSummaryRow(
              'Guests',
              '$_guestCount ${_guestCount == 1 ? 'person' : 'people'}',
            ),
            if (_needPickup) _buildSummaryRow('Pickup', 'Yes'),
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
              style: TextStyle(color: Colors.grey.shade600, fontSize: 13),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(fontWeight: FontWeight.w500, fontSize: 13),
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
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
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

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _emailController.dispose();
    _pickupAddressController.dispose();
    _notesController.dispose();
    super.dispose();
  }
}

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:apsdreamhome_app_v2/core/services/api_service.dart';

final registryDeskProvider = FutureProvider.autoDispose<Map<String, dynamic>>((ref) async {
  final api = ApiService();
  final res = await api.get('/api/v2/mobile/registry/desk');
  return res;
});

class RegistryDeskPage extends ConsumerStatefulWidget {
  const RegistryDeskPage({super.key});

  @override
  ConsumerState<RegistryDeskPage> createState() => _RegistryDeskPageState();
}

class _RegistryDeskPageState extends ConsumerState<RegistryDeskPage> {
  final _w1NameCtrl = TextEditingController();
  final _w1PhoneCtrl = TextEditingController();
  final _w2NameCtrl = TextEditingController();
  final _w2PhoneCtrl = TextEditingController();
  final _notesCtrl = TextEditingController();

  String _selectedSro = 'SRO Sadar Gorakhpur (Near Collectorate)';
  bool _submitting = false;

  @override
  void dispose() {
    _w1NameCtrl.dispose();
    _w1PhoneCtrl.dispose();
    _w2NameCtrl.dispose();
    _w2PhoneCtrl.dispose();
    _notesCtrl.dispose();
    super.dispose();
  }

  void _showBookingSheet(BuildContext context, List<dynamic> bookings, List<dynamic> sroList) {
    if (sroList.isNotEmpty) {
      _selectedSro = sroList.first?.toString() ?? _selectedSro;
    }

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (modalCtx) {
        return StatefulBuilder(
          builder: (ctx, setModalState) {
            return Container(
              padding: EdgeInsets.only(
                top: 24,
                left: 20,
                right: 20,
                bottom: MediaQuery.of(modalCtx).viewInsets.bottom + 24,
              ),
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
              ),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Center(
                      child: Container(
                        width: 48,
                        height: 4,
                        decoration: BoxDecoration(
                          color: Colors.grey.shade300,
                          borderRadius: BorderRadius.circular(2),
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    const Text(
                      'Book Sub-Registrar Slot',
                      style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: Color(0xFF0B194C)),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      'Select your Gorakhpur SRO office and provide witness details.',
                      style: TextStyle(fontSize: 13, color: Colors.grey.shade600),
                    ),
                    const SizedBox(height: 16),
                    const Text('Sub-Registrar Office', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
                    const SizedBox(height: 6),
                    DropdownButtonFormField<String>(
                      initialValue: _selectedSro,
                      isExpanded: true,
                      decoration: InputDecoration(
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      ),
                      items: sroList.map((s) {
                        final str = s?.toString() ?? '';
                        return DropdownMenuItem(value: str, child: Text(str, style: const TextStyle(fontSize: 13)));
                      }).toList(),
                      onChanged: (val) {
                        if (val != null) setModalState(() => _selectedSro = val);
                      },
                    ),
                    const SizedBox(height: 14),
                    const Text('Witness 1 Details', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
                    const SizedBox(height: 6),
                    Row(
                      children: [
                        Expanded(
                          child: TextField(
                            controller: _w1NameCtrl,
                            decoration: InputDecoration(
                              hintText: 'Full Name',
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: TextField(
                            controller: _w1PhoneCtrl,
                            keyboardType: TextInputType.phone,
                            decoration: InputDecoration(
                              hintText: 'Mobile No.',
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),
                    const Text('Witness 2 Details', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
                    const SizedBox(height: 6),
                    Row(
                      children: [
                        Expanded(
                          child: TextField(
                            controller: _w2NameCtrl,
                            decoration: InputDecoration(
                              hintText: 'Full Name',
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: TextField(
                            controller: _w2PhoneCtrl,
                            keyboardType: TextInputType.phone,
                            decoration: InputDecoration(
                              hintText: 'Mobile No.',
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 20),
                    SizedBox(
                      width: double.infinity,
                      height: 48,
                      child: ElevatedButton(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF0B194C),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        onPressed: _submitting
                            ? null
                            : () async {
                                if (_w1NameCtrl.text.trim().isEmpty || _w1PhoneCtrl.text.trim().isEmpty) {
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    const SnackBar(content: Text('Please enter Witness 1 Name and Mobile Number')),
                                  );
                                  return;
                                }
                                Navigator.pop(modalCtx);
                                setState(() => _submitting = true);
                                try {
                                  final api = ApiService();
                                  final res = await api.post('/api/v2/mobile/registry/book', data: {
                                    'sro_office': _selectedSro,
                                    'witness_1_name': _w1NameCtrl.text.trim(),
                                    'witness_1_phone': _w1PhoneCtrl.text.trim(),
                                    'witness_2_name': _w2NameCtrl.text.trim(),
                                    'witness_2_phone': _w2PhoneCtrl.text.trim(),
                                    'notes': _notesCtrl.text.trim(),
                                  });
                                  if (mounted) {
                                    final msg = ((res as Map<String, dynamic>?)?['message'] ?? 'Appointment slot requested!').toString();
                                    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
                                    ref.invalidate(registryDeskProvider);
                                  }
                                } catch (e) {
                                  if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                                } finally {
                                  if (mounted) setState(() => _submitting = false);
                                }
                              },
                        child: const Text('Confirm SRO Appointment Slot', style: TextStyle(fontWeight: FontWeight.w700, color: Colors.white)),
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
    final deskAsync = ref.watch(registryDeskProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F0),
      appBar: AppBar(
        title: const Text('Registry & Deed Execution Desk', style: TextStyle(fontWeight: FontWeight.w700, color: Color(0xFF0B194C))),
        backgroundColor: Colors.white,
        elevation: 0.5,
        iconTheme: const IconThemeData(color: Color(0xFF0B194C)),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.invalidate(registryDeskProvider),
          ),
        ],
      ),
      body: deskAsync.when(
        loading: () => const Center(child: CircularProgressIndicator(color: Color(0xFFD4AF37))),
        error: (err, _) => Center(child: Text('Error: $err')),
        data: (data) {
          final appointments = (data['appointments'] as List<dynamic>?) ?? [];
          final eligible = (data['eligible_bookings'] as List<dynamic>?) ?? [];
          final sroOffices = (data['sro_offices'] as List<dynamic>?) ?? [];
          final guidelines = (data['guidelines'] as List<dynamic>?) ?? [];

          return RefreshIndicator(
            color: const Color(0xFFD4AF37),
            onRefresh: () async => ref.invalidate(registryDeskProvider),
            child: ListView(
              padding: const EdgeInsets.all(16),
              children: [
                // Champagne Banner
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [Color(0xFF0B194C), Color(0xFF1E3A8A)],
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
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(
                              color: const Color(0xFFD4AF37).withValues(alpha: 0.2),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: const Icon(Icons.verified_user, color: Color(0xFFD4AF37), size: 24),
                          ),
                          const SizedBox(width: 12),
                          const Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text('Official SRO Registry Desk', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 16)),
                                Text('Government of Uttar Pradesh Registered Sale Deed', style: TextStyle(color: Colors.white70, fontSize: 12)),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const Divider(color: Colors.white24, height: 24),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('Stamp Duty Rate', style: TextStyle(color: Colors.white60, fontSize: 11)),
                              Text(data['stamp_duty_rate']?.toString() ?? '7% Male, 6% Female', style: const TextStyle(color: Color(0xFFD4AF37), fontWeight: FontWeight.w700, fontSize: 12)),
                            ],
                          ),
                          ElevatedButton.icon(
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFFD4AF37),
                              foregroundColor: const Color(0xFF0B194C),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                            ),
                            icon: const Icon(Icons.calendar_month, size: 16),
                            label: const Text('Book Slot', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12)),
                            onPressed: () => _showBookingSheet(context, eligible, sroOffices),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 20),

                // Active Appointments
                const Text('My Registry Appointments', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: Color(0xFF0B194C))),
                const SizedBox(height: 10),
                if (appointments.isEmpty)
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: Colors.grey.shade200),
                    ),
                    child: Row(
                      children: [
                        Icon(Icons.info_outline, color: Colors.grey.shade400, size: 28),
                        const SizedBox(width: 12),
                        const Expanded(
                          child: Text(
                            'No pending SRO appointment. Tap "Book Slot" to schedule your registry appointment.',
                            style: TextStyle(fontSize: 13, color: Colors.grey),
                          ),
                        ),
                      ],
                    ),
                  )
                else
                  ...appointments.map((a) {
                    final item = a as Map<String, dynamic>;
                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(
                                  item['appointment_token']?.toString() ?? 'REG-TOKEN',
                                  style: const TextStyle(fontWeight: FontWeight.w800, color: Color(0xFF0B194C)),
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: Colors.green.shade50,
                                    borderRadius: BorderRadius.circular(8),
                                    border: Border.all(color: Colors.green.shade300),
                                  ),
                                  child: Text(
                                    (item['status']?.toString() ?? 'requested').toUpperCase(),
                                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Colors.green.shade800),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 8),
                            Text('Office: ${item['sro_office']?.toString() ?? 'SRO Sadar'}', style: const TextStyle(fontSize: 13)),
                            Text('Preferred Date: ${item['preferred_date_1']?.toString() ?? 'TBD'}', style: const TextStyle(fontSize: 12, color: Colors.grey)),
                          ],
                        ),
                      ),
                    );
                  }),
                const SizedBox(height: 20),

                // Guidelines
                const Text('Required Documentation & Guidelines', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: Color(0xFF0B194C))),
                const SizedBox(height: 10),
                ...guidelines.map((g) {
                  return Padding(
                    padding: const EdgeInsets.only(bottom: 8),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Icon(Icons.check_circle, color: Color(0xFFD4AF37), size: 18),
                        const SizedBox(width: 8),
                        Expanded(child: Text(g?.toString() ?? '', style: const TextStyle(fontSize: 13, height: 1.4))),
                      ],
                    ),
                  );
                }),
              ],
            ),
          );
        },
      ),
    );
  }
}

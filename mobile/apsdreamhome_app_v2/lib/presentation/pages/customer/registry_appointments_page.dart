import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

/// Customer-facing Sub-Registrar Office (SRO) Registry Appointment desk.
///
/// Mirrors the web `/customer/registry-appointments` flow:
/// list existing appointments → book a new slot (with stamp-duty + reg fee
/// calculation handled by the backend) → view/download the appointment slip.
class RegistryAppointmentsPage extends ConsumerStatefulWidget {
  const RegistryAppointmentsPage({super.key});

  @override
  ConsumerState<RegistryAppointmentsPage> createState() =>
      _RegistryAppointmentsPageState();
}

class _RegistryAppointmentsPageState
    extends ConsumerState<RegistryAppointmentsPage> {
  bool _loading = true;
  String? _error;
  List<Map<String, dynamic>> _appointments = [];

  final _bookingIdController = TextEditingController();
  final _sroController = TextEditingController(text: 'Tehsil Sadar Gorakhpur');
  final _prefDate1Controller = TextEditingController();
  final _prefDate2Controller = TextEditingController();
  final _witness1NameController = TextEditingController();
  final _witness1PhoneController = TextEditingController();
  final _witness1AadhaarController = TextEditingController();
  final _witness2NameController = TextEditingController();
  final _witness2PhoneController = TextEditingController();
  final _witness2AadhaarController = TextEditingController();
  final _notesController = TextEditingController();

  bool _booking = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _bookingIdController.dispose();
    _sroController.dispose();
    _prefDate1Controller.dispose();
    _prefDate2Controller.dispose();
    _witness1NameController.dispose();
    _witness1PhoneController.dispose();
    _witness1AadhaarController.dispose();
    _witness2NameController.dispose();
    _witness2PhoneController.dispose();
    _witness2AadhaarController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      AppConstants.initBaseUrl();
      final res = await ApiService()
          .get('/api/v2/mobile/registry-appointments');
      if (res['success'] == true) {
        final list = (res['appointments'] ?? []) as List;
        setState(() {
          _appointments = list
              .map((e) => Map<String, dynamic>.from(e as Map))
              .toList();
          _loading = false;
        });
      } else {
        setState(() {
          _error = (res['message'] ?? 'Could not load appointments.').toString();
          _loading = false;
        });
      }
    } catch (e) {
      debugPrint('registry appointments load failed: $e');
      setState(() {
        _error = 'Network error. Please pull down to retry.';
        _loading = false;
      });
    }
  }

  Future<void> _bookAppointment() async {
    final bookingId = int.tryParse(_bookingIdController.text.trim());
    if (bookingId == null || bookingId <= 0) {
      _showSnack('Please enter a valid Booking ID.', error: true);
      return;
    }

    setState(() => _booking = true);
    try {
      AppConstants.initBaseUrl();
      final res = await ApiService().post(
        '/api/v2/mobile/registry-appointments/book',
        data: {
          'booking_id': bookingId,
          'sro_office': _sroController.text.trim(),
          'preferred_date_1': _prefDate1Controller.text.trim(),
          'preferred_date_2': _prefDate2Controller.text.trim().isEmpty
              ? null
              : _prefDate2Controller.text.trim(),
          'witness_1_name': _witness1NameController.text.trim(),
          'witness_1_phone': _witness1PhoneController.text.trim(),
          'witness_1_aadhaar': _witness1AadhaarController.text.trim(),
          'witness_2_name': _witness2NameController.text.trim(),
          'witness_2_phone': _witness2PhoneController.text.trim(),
          'witness_2_aadhaar': _witness2AadhaarController.text.trim(),
          'notes': _notesController.text.trim(),
        },
      );

      if (res['success'] == true) {
        _showSnack('Appointment requested: ${res['appointment_token'] ?? ''}',
            error: false);
        _bookingIdController.clear();
        _notesController.clear();
        await _load();
      } else {
        _showSnack(
            (res['message'] ?? 'Appointment request failed.').toString(), error: true);
      }
    } catch (e) {
      debugPrint('registry booking failed: $e');
      _showSnack('Network error. Please try again.', error: true);
    } finally {
      if (mounted) setState(() => _booking = false);
    }
  }

  void _showSnack(String message, {required bool error}) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: error ? AppTheme.errorColor : AppTheme.successColor,
        duration: const Duration(seconds: 4),
      ),
    );
  }

  Color _statusChipColor(String? status) {
    switch (status) {
      case 'slot_confirmed':
        return AppTheme.successColor;
      case 'eligible':
        return AppTheme.infoColor;
      case 'cancelled':
        return AppTheme.errorColor;
      default:
        return AppTheme.warningColor;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.backgroundColor,
      appBar: AppBar(
        title: const Text(
          'Registry Appointment',
          style: TextStyle(
            fontWeight: FontWeight.w700,
            color: AppTheme.primaryColor,
          ),
        ),
        backgroundColor: Colors.white,
        elevation: 0.5,
        iconTheme: const IconThemeData(color: AppTheme.primaryColor),
      ),
      body: RefreshIndicator(
        onRefresh: _load,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
          children: [
            // --- Book new appointment ---
            GlassCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        width: 40,
                        height: 40,
                        decoration: BoxDecoration(
                          color: AppTheme.primaryColor.withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(11),
                        ),
                        child: const Icon(Icons.event_available,
                            color: AppTheme.primaryColor, size: 22),
                      ),
                      const SizedBox(width: 10),
                      const Expanded(
                        child: Text(
                          'Book SRO Registry Slot',
                          style: TextStyle(
                            fontWeight: FontWeight.w800,
                            fontSize: 16,
                            color: AppTheme.primaryColor,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),
                  _input('Booking ID', _bookingIdController,
                      hint: 'Enter your plot booking ID',
                      keyboard: TextInputType.number),
                  const SizedBox(height: 12),
                  _input('SRO Office', _sroController,
                      hint: 'Tehsil Sadar Gorakhpur'),
                  const SizedBox(height: 12),
                  _input('Preferred Date 1 (YYYY-MM-DD)', _prefDate1Controller,
                      hint: 'e.g. 2026-10-25'),
                  const SizedBox(height: 12),
                  _input('Preferred Date 2 (YYYY-MM-DD)', _prefDate2Controller,
                      hint: 'optional'),
                  const SizedBox(height: 12),
                  _input('Witness 1 Name', _witness1NameController),
                  const SizedBox(height: 12),
                  _input('Witness 1 Phone', _witness1PhoneController),
                  const SizedBox(height: 12),
                  _input('Witness 1 Aadhaar', _witness1AadhaarController),
                  const SizedBox(height: 12),
                  _input('Witness 2 Name', _witness2NameController),
                  const SizedBox(height: 12),
                  _input('Witness 2 Phone', _witness2PhoneController),
                  const SizedBox(height: 12),
                  _input('Witness 2 Aadhaar', _witness2AadhaarController),
                  const SizedBox(height: 12),
                  _input('Notes', _notesController),
                  const SizedBox(height: 16),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: _booking ? null : _bookAppointment,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTheme.primaryColor,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                      child: _booking
                          ? const SizedBox(
                              width: 20,
                              height: 20,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                valueColor: AlwaysStoppedAnimation(
                                    Colors.white),
                              ),
                            )
                          : const Text(
                              'Request Registry Appointment',
                              style: TextStyle(
                                fontWeight: FontWeight.w700,
                                color: Colors.white,
                              ),
                            ),
                    ),
                  ),
                  const SizedBox(height: 10),
                  const Text(
                    'Stamp duty & registration fee are calculated automatically '
                    'from your booking value. You will receive an appointment '
                    'token once the request is accepted.',
                    style: TextStyle(color: Colors.black54, fontSize: 12),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),
            // --- Existing appointments ---
            Row(
              children: [
                const Text(
                  'My Appointments',
                  style: TextStyle(
                    fontWeight: FontWeight.w800,
                    fontSize: 16,
                    color: AppTheme.primaryColor,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            if (_loading)
              const Center(
                child: Padding(
                  padding: EdgeInsets.all(24),
                  child: CircularProgressIndicator(),
                ),
              )
            else if (_error != null)
              ListView(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                children: [
                  const SizedBox(height: 24),
                  const Icon(Icons.cloud_off_outlined,
                      size: 56, color: Colors.black26),
                  const SizedBox(height: 16),
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 32),
                    child: Text(
                      _error!,
                      textAlign: TextAlign.center,
                      style: const TextStyle(color: Colors.black54),
                    ),
                  ),
                ],
              )
            else if (_appointments.isEmpty)
              GlassCard(
                child: Column(
                  children: [
                    const Icon(Icons.event_busy_outlined,
                        size: 44, color: Colors.black26),
                    const SizedBox(height: 12),
                    const Text(
                      'No registry appointments yet',
                      style: TextStyle(
                          fontWeight: FontWeight.w700,
                          color: AppTheme.primaryColor),
                    ),
                    const SizedBox(height: 6),
                    const Text(
                      'Book your first SRO registry slot above. '
                      'You will need your plot Booking ID.',
                      textAlign: TextAlign.center,
                      style: TextStyle(color: Colors.black54, fontSize: 12.5),
                    ),
                  ],
                ),
              )
            else
              ..._appointments.map(_appointmentCard),
          ],
        ),
      ),
    );
  }

  Widget _input(String label, TextEditingController controller,
      {String? hint, TextInputType? keyboard}) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label,
            style: const TextStyle(
                color: Colors.black54, fontSize: 12.5)),
        const SizedBox(height: 6),
        TextField(
          controller: controller,
          keyboardType: keyboard,
          onChanged: (_) {},
          decoration: InputDecoration(
            hintText: hint,
            filled: true,
            fillColor: Colors.white,
            contentPadding:
                const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: AppTheme.goldBorder),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: AppTheme.goldBorder),
            ),
          ),
        ),
      ],
    );
  }

  Widget _appointmentCard(Map<String, dynamic> a) {
    final token = (a['appointment_token'] ?? '').toString();
    final status = (a['status'] ?? 'requested').toString();
    final colony = (a['colony_name'] ?? '—').toString();
    final plot = (a['plot_number'] ?? '—').toString();
    final created = (a['created_at'] ?? '').toString();
    final confirmed = (a['confirmed_date'] ?? '').toString();
    final stampDuty = (a['stamp_duty_amount'] ?? 0).toString();
    final regFee = (a['reg_fee_amount'] ?? 0).toString();
    final tint = _statusChipColor(status);

    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: GlassCard(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  width: 40,
                  height: 40,
                  decoration: BoxDecoration(
                    color: tint.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(11),
                  ),
                  child: Icon(Icons.insert_drive_file,
                      color: tint, size: 22),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        token,
                        style: const TextStyle(
                          fontWeight: FontWeight.w800,
                          fontSize: 13,
                          color: AppTheme.primaryColor,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        '$colony • Plot $plot',
                        style: const TextStyle(
                            color: Colors.black54, fontSize: 12),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                      horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: tint.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    status.replaceAll('_', ' ').toUpperCase(),
                    style: TextStyle(
                      color: tint,
                      fontSize: 9.5,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            const Divider(color: AppTheme.goldBorder),
            const SizedBox(height: 8),
            Row(
              children: [
                _infoChip('Created', created.isEmpty ? '—' : created),
                _infoChip('Confirmed', confirmed.isEmpty ? '—' : confirmed),
                _infoChip('Stamp Duty', '₹${stampDuty == '0' ? '—' : stampDuty}'),
                _infoChip('Reg Fee', '₹${regFee == '0' ? '—' : regFee}'),
              ],
            ),
            const SizedBox(height: 10),
            SizedBox(
              width: double.infinity,
              child: OutlinedButton(
                onPressed: () => context.go(
                  '/registry-appointments/${a['id']}/slip',
                ),
                style: OutlinedButton.styleFrom(
                  side: const BorderSide(color: AppTheme.goldBorder),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
                child: const Text(
                  'View Slip',
                  style: TextStyle(
                    fontWeight: FontWeight.w700,
                    color: AppTheme.primaryColor,
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _infoChip(String label, String value) {
    return Expanded(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label,
              style: const TextStyle(
                  color: Colors.black38, fontSize: 10)),
          const SizedBox(height: 2),
          Text(
            value,
            style: const TextStyle(
              fontWeight: FontWeight.w600,
              color: AppTheme.primaryColor,
              fontSize: 12.5,
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }
}

import 'package:flutter/material.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

/// Registry Appointment Slip / Gatepass.
///
/// Mirrors the web admin slip view (`registry_appointment_slip.php`) but for
/// the customer's own appointment, ownership-checked by the backend.
class RegistryAppointmentSlipPage extends StatefulWidget {
  final String appointmentId;

  const RegistryAppointmentSlipPage({super.key, required this.appointmentId});

  @override
  State<RegistryAppointmentSlipPage> createState() =>
      _RegistryAppointmentSlipPageState();
}

class _RegistryAppointmentSlipPageState
    extends State<RegistryAppointmentSlipPage> {
  bool _loading = true;
  String? _error;
  Map<String, dynamic> _a = {};

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      AppConstants.initBaseUrl();
      final res = await ApiService().get(
          '/api/v2/mobile/registry-appointments/${widget.appointmentId}/slip');
      if (res['success'] == true) {
        setState(() {
          _a = Map<String, dynamic>.from(
              (res['appointment'] ?? {}) as Map);
          _loading = false;
        });
      } else {
        setState(() {
          _error = (res['message'] ?? 'Could not load slip.').toString();
          _loading = false;
        });
      }
    } catch (e) {
      debugPrint('registry slip load failed: $e');
      setState(() {
        _error = 'Network error. Please pull down to retry.';
        _loading = false;
      });
    }
  }

  String _money(dynamic v) {
    final n = (v is num) ? v.toDouble() : double.tryParse(v.toString()) ?? 0;
    final s = n.round().toString();
    final buf = StringBuffer();
    for (var i = 0; i < s.length; i++) {
      if (i > 0 && (s.length - i) % 3 == 0) buf.write(',');
      buf.write(s[i]);
    }
    return '₹$buf';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.backgroundColor,
      appBar: AppBar(
        title: const Text(
          'Registry Slip',
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
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : _error != null
                ? ListView(
                    children: [
                      const SizedBox(height: 80),
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
                : _slipView(),
      ),
    );
  }

  Widget _slipView() {
    final token = (_a['appointment_token'] ?? '—').toString();
    final status = (_a['status'] ?? 'requested').toString();
    final bookingNo = (_a['booking_number'] ?? '—').toString();
    final colony = (_a['colony_name'] ?? '—').toString();
    final plot = (_a['plot_number'] ?? '—').toString();
    final customer = (_a['customer_name'] ?? '—').toString();
    final phone = (_a['customer_phone'] ?? '—').toString();
    final sro = (_a['sro_office'] ?? '—').toString();
    final pref1 = (_a['preferred_date_1'] ?? '—').toString();
    final pref2 = (_a['preferred_date_2'] ?? '—').toString();
    final confirmed = (_a['confirmed_date'] ?? '—').toString();
    final executive = (_a['assigned_executive_name'] ?? '—').toString();
    final execPhone = (_a['assigned_executive_phone'] ?? '—').toString();
    final deed = (_a['deed_registration_number'] ?? '—').toString();
    final stampDuty = _money(_a['stamp_duty_amount']);
    final regFee = _money(_a['reg_fee_amount']);
    final total = _money(
        ((_a['stamp_duty_amount'] ?? 0) + (_a['reg_fee_amount'] ?? 0)));

    final statusColor =
        status == 'slot_confirmed'
            ? AppTheme.successColor
            : status == 'cancelled'
                ? AppTheme.errorColor
                : AppTheme.warningColor;

    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
      children: [
        // Header band
        GlassCard(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    width: 48,
                    height: 48,
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
                      ),
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: const Icon(Icons.receipt_long,
                        color: Colors.white, size: 26),
                  ),
                  const SizedBox(width: 12),
                  const Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Registry Appointment Slip',
                          style: TextStyle(
                            fontWeight: FontWeight.w800,
                            fontSize: 17,
                            color: AppTheme.primaryColor,
                          ),
                        ),
                        SizedBox(height: 4),
                        Text(
                          'APS Dream Home • Sub-Registrar Office',
                          style: TextStyle(
                              color: Colors.black54, fontSize: 12.5),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 14),
              Row(
                children: [
                  Expanded(
                    child: _slipField('Appointment Token', token,
                        isValueBold: true),
                  ),
                  Expanded(
                    child: _slipField('Status',
                        status.replaceAll('_', ' ').toUpperCase(),
                        valueColor: statusColor, isValueBold: true),
                  ),
                ],
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),
        // Booking details
        GlassCard(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Booking Details',
                style: TextStyle(
                  fontWeight: FontWeight.w800,
                  fontSize: 14,
                  color: AppTheme.primaryColor,
                ),
              ),
              const SizedBox(height: 10),
              _slipField('Booking Number', bookingNo),
              _slipField('Colony', colony),
              _slipField('Plot Number', plot),
              _slipField('Customer Name', customer),
              _slipField('Customer Phone', phone),
            ],
          ),
        ),
        const SizedBox(height: 16),
        // Appointment details
        GlassCard(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Appointment Details',
                style: TextStyle(
                  fontWeight: FontWeight.w800,
                  fontSize: 14,
                  color: AppTheme.primaryColor,
                ),
              ),
              const SizedBox(height: 10),
              _slipField('SRO Office', sro),
              _slipField('Preferred Date 1', pref1),
              _slipField('Preferred Date 2', pref2),
              _slipField('Confirmed Date', confirmed),
              _slipField('Assigned Executive', executive),
              _slipField('Executive Phone', execPhone),
              _slipField('Deed Registration Number', deed),
            ],
          ),
        ),
        const SizedBox(height: 16),
        // Fees
        GlassCard(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Fees',
                style: TextStyle(
                  fontWeight: FontWeight.w800,
                  fontSize: 14,
                  color: AppTheme.primaryColor,
                ),
              ),
              const SizedBox(height: 10),
              _slipField('Stamp Duty', stampDuty),
              _slipField('Registration Fee', regFee),
              const Divider(height: 18, color: AppTheme.goldBorder),
              _slipField('Total Payable', total,
                  isValueBold: true, valueColor: AppTheme.successColor),
            ],
          ),
        ),
        const SizedBox(height: 20),
        // Action bar
        Row(
          children: [
            Expanded(
              child: OutlinedButton(
                onPressed: () {
                  // Print via browser print dialog
                  if (mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Use browser print to save PDF'),
                        duration: Duration(seconds: 2),
                      ),
                    );
                  }
                },
                style: OutlinedButton.styleFrom(
                  side: const BorderSide(color: AppTheme.goldBorder),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                child: const Text(
                  'Print Slip',
                  style: TextStyle(
                    fontWeight: FontWeight.w700,
                    color: AppTheme.primaryColor,
                  ),
                ),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: ElevatedButton(
                onPressed: () {},
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.primaryColor,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                child: const Text(
                  'Share via WhatsApp',
                  style: TextStyle(
                    fontWeight: FontWeight.w700,
                    color: Colors.white,
                  ),
                ),
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _slipField(String label, String value,
      {bool isValueBold = false, Color? valueColor}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 5),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 150,
            child: Text(
              label,
              style: const TextStyle(
                color: Colors.black54,
                fontSize: 13,
              ),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              value,
              style: TextStyle(
                fontWeight: isValueBold ? FontWeight.w800 : FontWeight.w600,
                color: valueColor ?? AppTheme.primaryColor,
                fontSize: 13,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

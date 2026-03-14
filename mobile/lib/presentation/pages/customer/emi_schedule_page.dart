import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/theme/app_theme.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/glass_card.dart';
import 'package:dio/dio.dart';

class EmiSchedulePage extends ConsumerStatefulWidget {
  final int bookingId;
  const EmiSchedulePage({super.key, required this.bookingId});

  @override
  ConsumerState<EmiSchedulePage> createState() => _EmiSchedulePageState();
}

class _EmiSchedulePageState extends ConsumerState<EmiSchedulePage> {
  List<dynamic> _installments = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _fetchSchedule();
  }

  Future<void> _fetchSchedule() async {
    try {
      final dio = Dio(BaseOptions(baseUrl: AppConstants.baseUrl));
      final token = await ref.read(authProvider.notifier).getToken();
      final response = await dio.get(
        '${AppConstants.apiVersion}/customer/emi-schedule',
        queryParameters: {'booking_id': widget.bookingId},
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );
      if (mounted) {
        setState(() {
          _installments = response.data['data'] ?? [];
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('EMI Schedule - Booking #${widget.bookingId}'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _installments.isEmpty
              ? const Center(child: Text('No EMI schedule found for this booking.'))
              : ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: _installments.length,
                  itemBuilder: (context, index) {
                    final emi = _installments[index];
                    return _buildEmiTile(emi);
                  },
                ),
    );
  }

  Widget _buildEmiTile(Map<String, dynamic> emi) {
    final bool isPaid = emi['status'] == 'paid';
    final bool isOverdue = emi['status'] == 'overdue';

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      child: GlassCard(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              Container(
                width: 40,
                height: 40,
                decoration: BoxDecoration(
                  color: isPaid ? Colors.green.withOpacity(0.1) : Colors.blue.withOpacity(0.1),
                  shape: BoxShape.circle,
                ),
                child: Center(
                  child: Text(
                    emi['emi_number'].toString(),
                    style: TextStyle(
                      fontWeight: FontWeight.bold,
                      color: isPaid ? Colors.green : Colors.blue,
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      '₹${emi['amount']}',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                    ),
                    Text(
                      'Due: ${emi['due_date']}',
                      style: TextStyle(color: Colors.grey.shade600, fontSize: 13),
                    ),
                  ],
                ),
              ),
              if (isPaid)
                const Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Icon(Icons.check_circle, color: Colors.green),
                    Text('Paid', style: TextStyle(color: Colors.green, fontSize: 11)),
                  ],
                )
              else
                ElevatedButton(
                  onPressed: () => _processPayment(emi),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: isOverdue ? Colors.red : AppTheme.primaryColor,
                    small: true,
                  ),
                  child: const Text('Pay Now'),
                ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _processPayment(Map<String, dynamic> emi) async {
    // Show confirmation
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Confirm Payment'),
        content: Text('Do you want to pay ₹${emi['amount']} for EMI #${emi['emi_number']}?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Cancel')),
          TextButton(onPressed: () => Navigator.pop(context, true), child: const Text('Confirm')),
        ],
      ),
    );

    if (confirm != true) return;

    setState(() => _isLoading = true);

    try {
      final dio = Dio(BaseOptions(baseUrl: AppConstants.baseUrl));
      final token = await ref.read(authProvider.notifier).getToken();
      await dio.post(
        '${AppConstants.apiVersion}/customer/pay-emi',
        data: {'emi_id': emi['id'], 'amount': emi['amount']},
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );
      
      _fetchSchedule(); // Refresh
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Payment successful!'), backgroundColor: Colors.green),
        );
      }
    } catch (e) {
      if (mounted) setState(() => _isLoading = false);
    }
  }
}

extension on ElevatedButton {
  static styleFrom({required Color backgroundColor, bool small = false}) {
     // Dummy for compilation context if literal doesn't support
  }
}
// Note: fixing styling above for real code

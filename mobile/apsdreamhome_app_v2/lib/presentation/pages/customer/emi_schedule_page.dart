import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/providers/auth_provider.dart';
import '../../widgets/glass_card.dart';

class EmiSchedulePage extends ConsumerStatefulWidget {
  final int? bookingId;
  const EmiSchedulePage({super.key, this.bookingId});

  @override
  ConsumerState<EmiSchedulePage> createState() => _EmiSchedulePageState();
}

class _EmiSchedulePageState extends ConsumerState<EmiSchedulePage> {
  List<dynamic> _installments = [];
  bool _isLoading = true;

  String _formatCurrency(num amount) {
    final fixed = amount.toStringAsFixed(0);
    return '₹${fixed.replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (Match m) => '${m[1]},')}';
  }

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
          _installments = (response.data['data'] as List<dynamic>?) ?? [];
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
          ? RefreshIndicator(
              onRefresh: _fetchSchedule,
              child: ListView(
                children: const [
                  SizedBox(height: 100),
                  Center(
                    child: Text('No EMI schedule found for this booking.'),
                  ),
                ],
              ),
            )
          : RefreshIndicator(
              onRefresh: _fetchSchedule,
              child: ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: _installments.length + 1,
                itemBuilder: (context, index) {
                  if (index == 0) return _buildProgressHeader();
                  final emi = _installments[index - 1] as Map<String, dynamic>;
                  return _buildEmiTile(emi);
                },
              ),
            ),
    );
  }

  Widget _buildProgressHeader() {
    final total = _installments.length;
    final paidCount = _installments
        .where((e) => (e as Map<String, dynamic>)['status'] == 'paid')
        .length;
    final progress = total > 0 ? paidCount / total : 0.0;

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            AppTheme.primaryColor,
            AppTheme.primaryColor.withValues(alpha: 0.8),
          ],
        ),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                '$paidCount of $total EMIs paid',
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.bold,
                  fontSize: 16,
                ),
              ),
              Text(
                '${(progress * 100).toStringAsFixed(0)}%',
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.bold,
                  fontSize: 16,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          ClipRRect(
            borderRadius: BorderRadius.circular(6),
            child: LinearProgressIndicator(
              value: progress,
              backgroundColor: Colors.white.withValues(alpha: 0.3),
              valueColor: const AlwaysStoppedAnimation<Color>(Colors.white),
              minHeight: 8,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEmiTile(Map<String, dynamic> emi) {
    final String status = emi['status'] as String;
    final bool isPaid = status == 'paid';
    final bool isOverdue = status == 'overdue';

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
                  color: isPaid
                      ? Colors.green.withValues(alpha: 0.1)
                      : Colors.blue.withValues(alpha: 0.1),
                  shape: BoxShape.circle,
                ),
                child: Center(
                  child: Text(
                    (emi['emi_number'] as num).toString(),
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
                      _formatCurrency(emi['amount'] as num),
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                      ),
                    ),
                    Text(
                      'Due: ${emi['due_date'] as String}',
                      style: TextStyle(
                        color: Colors.grey.shade600,
                        fontSize: 13,
                      ),
                    ),
                  ],
                ),
              ),
              if (isPaid)
                const Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Icon(Icons.check_circle, color: Colors.green),
                    Text(
                      'Paid',
                      style: TextStyle(color: Colors.green, fontSize: 11),
                    ),
                  ],
                )
              else
                ElevatedButton(
                  onPressed: () => _processPayment(emi),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: isOverdue
                        ? Colors.red
                        : AppTheme.primaryColor,
                    minimumSize: const Size(80, 32),
                    padding: const EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 8,
                    ),
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
        content: Text(
          'Do you want to pay ${_formatCurrency(emi['amount'] as num)} for EMI #${emi['emi_number'] as num}?',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Confirm'),
          ),
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
        data: {'emi_id': emi['id'] as num, 'amount': emi['amount'] as num},
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );

      _fetchSchedule(); // Refresh
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Payment successful!'),
            backgroundColor: Colors.green,
          ),
        );
      }
    } catch (e) {
      if (mounted) setState(() => _isLoading = false);
    }
  }
}

// Note: styling handled inline above

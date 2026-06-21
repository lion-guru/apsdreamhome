import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

import '../../../core/theme/app_theme.dart';
import '../../../data/models/booking_model.dart';
import '../../widgets/app_widgets.dart';

class MyBookingsPage extends ConsumerStatefulWidget {
  const MyBookingsPage({super.key});

  @override
  ConsumerState<MyBookingsPage> createState() => _MyBookingsPageState();
}

class _MyBookingsPageState extends ConsumerState<MyBookingsPage>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('My Bookings'),
        bottom: TabBar(
          controller: _tabController,
          tabs: const [
            Tab(text: 'Active'),
            Tab(text: 'Completed'),
            Tab(text: 'All'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          _buildBookingsList('active'),
          _buildBookingsList('completed'),
          _buildBookingsList('all'),
        ],
      ),
    );
  }

  Widget _buildBookingsList(String filter) {
    // Mock data - replace with actual provider
    final bookings = _getMockBookings().where((b) {
      if (filter == 'active') {
        return b.status == 'confirmed' || b.status == 'pending';
      } else if (filter == 'completed') {
        return b.status == 'completed' || b.status == 'registry_done';
      }
      return true;
    }).toList();

    if (bookings.isEmpty) {
      return AppWidgets.emptyState(
        title: 'No ${filter.capitalize()} Bookings',
        subtitle: filter == 'active'
            ? 'You don\'t have any active bookings'
            : 'Book a plot to get started',
        icon: Icons.home_work_outlined,
        onAction: () => context.push('/colonies'),
        actionLabel: 'Browse Colonies',
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: bookings.length,
      itemBuilder: (context, index) {
        final booking = bookings[index];
        return _buildBookingCard(booking);
      },
    );
  }

  Widget _buildBookingCard(BookingModel booking) {
    return AppWidgets.customCard(
      onTap: () => _showBookingDetails(booking),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header
          Row(
            children: [
              Container(
                width: 60,
                height: 60,
                decoration: BoxDecoration(
                  color: AppTheme.primaryColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(
                  Icons.home_work,
                  color: AppTheme.primaryColor,
                  size: 32,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      booking.colonyName,
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Plot ${booking.plotNumber}',
                      style: TextStyle(
                        fontSize: 13,
                        color: Colors.grey.shade600,
                      ),
                    ),
                  ],
                ),
              ),
              AppWidgets.statusBadge(status: booking.status),
            ],
          ),

          const SizedBox(height: 16),

          // Payment Summary
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.grey.shade50,
              borderRadius: BorderRadius.circular(8),
            ),
            child: Column(
              children: [
                _buildPaymentRow('Total Price', booking.totalAmount,
                    isBold: true),
                const Divider(height: 12),
                _buildPaymentRow('Paid Amount', booking.totalPaid ?? 0),
                _buildPaymentRow(
                  'Remaining',
                  booking.remainingAmount ?? 0,
                  color: (booking.remainingAmount ?? 0) > 0
                      ? AppTheme.warningColor
                      : AppTheme.successColor,
                ),
              ],
            ),
          ),

          const SizedBox(height: 12),

          // EMI Details (if applicable)
          if (booking.paymentPlan == 'emi' && booking.emiAmount != null) ...[
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppTheme.infoColor.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(
                  color: AppTheme.infoColor.withValues(alpha: 0.3),
                ),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Row(
                    children: [
                      Icon(
                        Icons.calendar_today,
                        size: 16,
                        color: AppTheme.infoColor,
                      ),
                      SizedBox(width: 8),
                      Text(
                        'EMI Schedule',
                        style: TextStyle(
                          fontWeight: FontWeight.bold,
                          color: AppTheme.infoColor,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  _buildEMIDetails(booking),
                ],
              ),
            ),
            const SizedBox(height: 12),
          ],

          // Progress Bar
          if (booking.status != 'completed') ...[
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'Payment Progress',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade600,
                      ),
                    ),
                    Text(
                      '${booking.paidPercentage.toStringAsFixed(0)}%',
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: AppTheme.primaryColor,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                ClipRRect(
                  borderRadius: BorderRadius.circular(4),
                  child: LinearProgressIndicator(
                    value: booking.paidPercentage / 100,
                    backgroundColor: Colors.grey.shade200,
                    valueColor: const AlwaysStoppedAnimation<Color>(
                      AppTheme.primaryColor,
                    ),
                    minHeight: 8,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
          ],

          // Action Buttons
          Row(
            children: [
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: () => _showPaymentHistory(booking),
                  icon: const Icon(Icons.history, size: 18),
                  label: const Text('History'),
                ),
              ),
              const SizedBox(width: 8),
              if ((booking.remainingAmount ?? 0) > 0)
                Expanded(
                  child: ElevatedButton.icon(
                    onPressed: () => _showPaymentOptions(booking),
                    icon: const Icon(Icons.payment, size: 18),
                    label: const Text('Pay Now'),
                  ),
                ),
              if ((booking.remainingAmount ?? 0) <= 0)
                Expanded(
                  child: ElevatedButton.icon(
                    onPressed: () => _downloadDocuments(booking),
                    icon: const Icon(Icons.download, size: 18),
                    label: const Text('Documents'),
                  ),
                ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildPaymentRow(String label, double amount,
      {bool isBold = false, Color? color}) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: TextStyle(
            fontSize: 13,
            color: Colors.grey.shade700,
          ),
        ),
        AppWidgets.priceTag(
          amount: amount,
          prefix: '₹',
          style: TextStyle(
            fontSize: isBold ? 16 : 14,
            fontWeight: isBold ? FontWeight.bold : FontWeight.w500,
            color: color ?? Colors.black87,
          ),
        ),
      ],
    );
  }

  Widget _buildEMIDetails(BookingModel booking) {
    return Column(
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            _buildEMIInfoItem('Monthly EMI', '₹${booking.emiAmount}'),
            _buildEMIInfoItem('Tenure', '${booking.emiMonths} months'),
            _buildEMIInfoItem(
                'Status', booking.isFullyPaid ? 'Paid' : 'Active'),
          ],
        ),
      ],
    );
  }

  Widget _buildEMIInfoItem(String label, String value) {
    return Column(
      children: [
        Text(
          value,
          style: const TextStyle(
            fontWeight: FontWeight.bold,
            fontSize: 14,
          ),
        ),
        const SizedBox(height: 2),
        Text(
          label,
          style: TextStyle(
            fontSize: 10,
            color: Colors.grey.shade600,
          ),
        ),
      ],
    );
  }

  void _showBookingDetails(BookingModel booking) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) {
        return DraggableScrollableSheet(
          initialChildSize: 0.9,
          minChildSize: 0.5,
          maxChildSize: 0.95,
          expand: false,
          builder: (context, scrollController) {
            return Container(
              padding: const EdgeInsets.all(24),
              child: ListView(
                controller: scrollController,
                children: [
                  // Header
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Booking Details',
                        style:
                            Theme.of(context).textTheme.headlineSmall?.copyWith(
                                  fontWeight: FontWeight.bold,
                                ),
                      ),
                      IconButton(
                        onPressed: () => Navigator.pop(context),
                        icon: const Icon(Icons.close),
                      ),
                    ],
                  ),

                  const SizedBox(height: 16),

                  // Booking ID
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.grey.shade100,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Booking ID',
                              style: TextStyle(
                                fontSize: 12,
                                color: Colors.grey.shade600,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              booking.id.substring(0, 8).toUpperCase(),
                              style: const TextStyle(
                                fontWeight: FontWeight.bold,
                                letterSpacing: 1,
                              ),
                            ),
                          ],
                        ),
                        AppWidgets.statusBadge(status: booking.status),
                      ],
                    ),
                  ),

                  const SizedBox(height: 24),

                  // Plot Details
                  _buildSectionTitle('Plot Information'),
                  _buildDetailItem('Colony', booking.colonyName),
                  _buildDetailItem('Plot Number', booking.plotNumber),

                  const SizedBox(height: 24),

                  // Payment Summary
                  _buildSectionTitle('Payment Summary'),
                  _buildPriceRow('Total Price', booking.totalAmount),
                  _buildPriceRow('Token Amount', booking.tokenAmount),
                  _buildPriceRow('Paid Amount', booking.totalPaid ?? 0,
                      isPositive: true),
                  _buildPriceRow('Remaining', booking.remainingAmount ?? 0,
                      isHighlighted: (booking.remainingAmount ?? 0) > 0),

                  const Divider(height: 32),

                  // EMI Schedule
                  if (booking.paymentPlan == 'emi') ...[
                    _buildSectionTitle('EMI Schedule'),
                    _buildEMIScheduleTable(booking),
                    const SizedBox(height: 24),
                  ],

                  // Payment History
                  _buildSectionTitle('Payment History'),
                  _buildPaymentHistoryList(booking),

                  const SizedBox(height: 24),

                  // Documents
                  _buildSectionTitle('Documents'),
                  _buildDocumentsList(booking),

                  const SizedBox(height: 100),
                ],
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildSectionTitle(String title) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Text(
        title,
        style: const TextStyle(
          fontSize: 16,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }

  Widget _buildDetailItem(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: TextStyle(
              color: Colors.grey.shade600,
            ),
          ),
          Text(
            value,
            style: const TextStyle(
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPriceRow(String label, double amount,
      {bool isPositive = false, bool isHighlighted = false}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label),
          AppWidgets.priceTag(
            amount: amount,
            prefix: '₹',
            style: TextStyle(
              fontWeight: isPositive || isHighlighted
                  ? FontWeight.bold
                  : FontWeight.normal,
              color: isPositive
                  ? AppTheme.successColor
                  : isHighlighted
                      ? AppTheme.warningColor
                      : Colors.black87,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEMIScheduleTable(BookingModel booking) {
    // Mock EMI schedule
    final schedule = [
      {'month': 1, 'dueDate': '2026-05-01', 'amount': 25000, 'status': 'paid'},
      {'month': 2, 'dueDate': '2026-06-01', 'amount': 25000, 'status': 'paid'},
      {
        'month': 3,
        'dueDate': '2026-07-01',
        'amount': 25000,
        'status': 'upcoming'
      },
      {
        'month': 4,
        'dueDate': '2026-08-01',
        'amount': 25000,
        'status': 'pending'
      },
    ];

    return Column(
      children: schedule.map((emi) {
        final status = emi['status'] as String;
        final isPaid = status == 'paid';
        final isUpcoming = status == 'upcoming';

        return Container(
          margin: const EdgeInsets.only(bottom: 8),
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: isPaid
                ? AppTheme.successColor.withValues(alpha: 0.1)
                : isUpcoming
                    ? AppTheme.infoColor.withValues(alpha: 0.1)
                    : Colors.grey.shade50,
            borderRadius: BorderRadius.circular(8),
            border: Border.all(
              color: isPaid
                  ? AppTheme.successColor.withValues(alpha: 0.3)
                  : isUpcoming
                      ? AppTheme.infoColor.withValues(alpha: 0.3)
                      : Colors.grey.shade200,
            ),
          ),
          child: Row(
            children: [
              Container(
                width: 40,
                height: 40,
                decoration: BoxDecoration(
                  color: isPaid
                      ? AppTheme.successColor
                      : isUpcoming
                          ? AppTheme.infoColor
                          : Colors.grey,
                  shape: BoxShape.circle,
                ),
                child: Icon(
                  isPaid ? Icons.check : Icons.schedule,
                  color: Colors.white,
                  size: 20,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Month ${emi['month']}',
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    Text(
                      'Due: ${emi['dueDate']}',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade600,
                      ),
                    ),
                  ],
                ),
              ),
              AppWidgets.priceTag(
                amount: emi['amount'] as double,
                prefix: '₹',
                style: const TextStyle(
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
        );
      }).toList(),
    );
  }

  Widget _buildPaymentHistoryList(BookingModel booking) {
    final payments = booking.payments ?? [];

    if (payments.isEmpty) {
      return const Text('No payments recorded');
    }

    return Column(
      children: payments.map((payment) {
        return ListTile(
          leading: Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: AppTheme.successColor.withValues(alpha: 0.1),
              shape: BoxShape.circle,
            ),
            child: const Icon(
              Icons.check_circle,
              color: AppTheme.successColor,
              size: 20,
            ),
          ),
          title: Text(payment.method.toUpperCase()),
          subtitle: Text(
            payment.paidAt != null
                ? DateFormat('dd MMM yyyy').format(payment.paidAt!)
                : 'N/A',
          ),
          trailing: AppWidgets.priceTag(
            amount: payment.amount,
            prefix: '₹',
            style: const TextStyle(
              fontWeight: FontWeight.bold,
              color: AppTheme.successColor,
            ),
          ),
        );
      }).toList(),
    );
  }

  Widget _buildDocumentsList(BookingModel booking) {
    final documents = [
      {'name': 'Booking Agreement', 'status': 'available'},
      {'name': 'Payment Receipt', 'status': 'available'},
      {'name': 'Plot Allotment Letter', 'status': 'pending'},
      {'name': 'Registry Document', 'status': 'pending'},
    ];

    return Wrap(
      spacing: 8,
      runSpacing: 8,
      children: documents.map((doc) {
        final isAvailable = doc['status'] == 'available';
        return ActionChip(
          avatar: Icon(
            isAvailable ? Icons.download : Icons.lock,
            size: 18,
            color: isAvailable ? AppTheme.primaryColor : Colors.grey,
          ),
          label: Text(doc['name']!),
          onPressed: isAvailable
              ? () {
                  AppWidgets.showSuccessSnackBar(
                    context,
                    'Downloading ${doc['name']}...',
                  );
                }
              : null,
        );
      }).toList(),
    );
  }

  void _showPaymentHistory(BookingModel booking) {
    _showBookingDetails(booking);
  }

  void _showPaymentOptions(BookingModel booking) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) {
        return Container(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                'Pay Remaining Amount',
                style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
              ),
              const SizedBox(height: 16),
              AppWidgets.priceTag(
                amount: booking.remainingAmount ?? 0,
                prefix: '₹',
                style: const TextStyle(
                  fontSize: 32,
                  fontWeight: FontWeight.bold,
                  color: AppTheme.primaryColor,
                ),
              ),
              const SizedBox(height: 24),
              ListTile(
                leading: const Icon(Icons.credit_card),
                title: const Text('Card Payment'),
                subtitle: const Text('Credit/Debit card'),
                onTap: () {
                  Navigator.pop(context);
                  _processPayment(booking, 'card');
                },
              ),
              ListTile(
                leading: const Icon(Icons.account_balance),
                title: const Text('Bank Transfer'),
                subtitle: const Text('NEFT/RTGS/IMPS'),
                onTap: () {
                  Navigator.pop(context);
                  _processPayment(booking, 'bank');
                },
              ),
              ListTile(
                leading: const Icon(Icons.account_balance_wallet),
                title: const Text('UPI'),
                subtitle: const Text('Google Pay, PhonePe, etc.'),
                onTap: () {
                  Navigator.pop(context);
                  _processPayment(booking, 'upi');
                },
              ),
              if (booking.paymentPlan == 'emi')
                ListTile(
                  leading: const Icon(Icons.calendar_today),
                  title: const Text('Pay EMI Only'),
                  subtitle: const Text('Pay monthly installment'),
                  onTap: () {
                    Navigator.pop(context);
                    _processPayment(booking, 'emi');
                  },
                ),
            ],
          ),
        );
      },
    );
  }

  void _processPayment(BookingModel booking, String method) {
    // Show payment processing
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) {
        return const AlertDialog(
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              CircularProgressIndicator(),
              SizedBox(height: 16),
              Text('Processing payment...'),
            ],
          ),
        );
      },
    );

    // Simulate payment
    Future.delayed(const Duration(seconds: 2), () {
      Navigator.pop(context);
      AppWidgets.showSuccessSnackBar(
        context,
        'Payment successful!',
      );
    });
  }

  void _downloadDocuments(BookingModel booking) {
    _showBookingDetails(booking);
  }

  List<BookingModel> _getMockBookings() {
    return [
      BookingModel(
        id: 'booking_001',
        plotId: 'plot_001',
        customerId: 'cust_001',
        customerName: 'John Doe',
        customerPhone: '9876543210',
        colonyId: 'colony_001',
        colonyName: 'Suryoday Heights',
        plotNumber: 'A-45',
        plotPrice: 450000,
        tokenAmount: 45000,
        totalAmount: 450000,
        totalPaid: 150000,
        remainingAmount: 300000,
        paymentPlan: 'emi',
        status: 'approved',
        emiAmount: 25000,
        emiMonths: 12,
        payments: [
          PaymentModel(
            id: 'pay_001',
            bookingId: 'booking_001',
            amount: 45000,
            type: 'token',
            method: 'cash',
            paidAt: DateTime.now().subtract(const Duration(days: 30)),
          ),
          PaymentModel(
            id: 'pay_002',
            bookingId: 'booking_001',
            amount: 25000,
            type: 'installment',
            method: 'upi',
            paidAt: DateTime.now().subtract(const Duration(days: 15)),
          ),
        ],
        createdAt: DateTime.now().subtract(const Duration(days: 30)),
      ),
      BookingModel(
        id: 'booking_002',
        plotId: 'plot_002',
        customerId: 'cust_001',
        customerName: 'John Doe',
        customerPhone: '9876543210',
        colonyId: 'colony_002',
        colonyName: 'Raghunath City',
        plotNumber: 'B-12',
        plotPrice: 320000,
        tokenAmount: 32000,
        totalAmount: 320000,
        totalPaid: 320000,
        remainingAmount: 0,
        paymentPlan: 'full',
        status: 'completed',
        payments: [
          PaymentModel(
            id: 'pay_003',
            bookingId: 'booking_002',
            amount: 320000,
            type: 'full',
            method: 'bank_transfer',
            paidAt: DateTime.now().subtract(const Duration(days: 60)),
          ),
        ],
        createdAt: DateTime.now().subtract(const Duration(days: 60)),
      ),
    ];
  }
}

// Extension for capitalize
extension StringExtension on String {
  String capitalize() {
    return '${this[0].toUpperCase()}${substring(1)}';
  }
}

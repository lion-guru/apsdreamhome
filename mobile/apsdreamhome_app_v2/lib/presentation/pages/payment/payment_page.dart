import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../providers/payment_provider.dart';

/// Payment Page - UPI & PhonePe Payment Integration
/// Supports: GPay, PhonePe, Paytm, Amazon Pay, BHIM
class PaymentPage extends ConsumerStatefulWidget {
  final double amount;
  final String description;
  final String entityType;
  final int entityId;
  final String? entityName;

  const PaymentPage({
    super.key,
    required this.amount,
    required this.description,
    required this.entityType,
    required this.entityId,
    this.entityName,
  });

  @override
  ConsumerState<PaymentPage> createState() => _PaymentPageState();
}

class _PaymentPageState extends ConsumerState<PaymentPage> {
  bool _isInitializing = true;

  @override
  void initState() {
    super.initState();
    _initializePayment();
  }

  Future<void> _initializePayment() async {
    await ref.read(paymentProvider.notifier).initialize();
    if (mounted) {
      setState(() => _isInitializing = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final paymentState = ref.watch(paymentProvider);

    return Scaffold(
      backgroundColor: Colors.grey.shade50,
      appBar: AppBar(
        title: const Text('Payment'),
        elevation: 0,
        backgroundColor: Colors.white,
        foregroundColor: Colors.black,
      ),
      body: _isInitializing
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Amount Card
                  _buildAmountCard(),

                  // Payment Methods
                  _buildPaymentMethodsSection(),

                  // UPI Apps
                  _buildUpiAppsSection(),

                  // Other Methods
                  _buildOtherMethodsSection(),

                  // Error Display
                  if (paymentState.error != null)
                    _buildErrorCard(paymentState.error!),

                  const SizedBox(height: 100),
                ],
              ),
            ),
      bottomNavigationBar: _buildBottomBar(paymentState),
    );
  }

  Widget _buildAmountCard() {
    return Container(
      margin: const EdgeInsets.all(16),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF4285F4), Color(0xFF34A853)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.blue.withValues(alpha: 0.3),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Total Amount',
                style: TextStyle(color: Colors.white70, fontSize: 14),
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 12,
                  vertical: 4,
                ),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: const Text(
                  'Secure',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 12,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            '₹${widget.amount.toStringAsFixed(2)}',
            style: const TextStyle(
              color: Colors.white,
              fontSize: 36,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 12),
          Text(
            widget.description,
            style: const TextStyle(color: Colors.white70, fontSize: 14),
          ),
          if (widget.entityName != null) ...[
            const SizedBox(height: 4),
            Text(
              widget.entityName!,
              style: const TextStyle(
                color: Colors.white,
                fontSize: 14,
                fontWeight: FontWeight.w500,
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildPaymentMethodsSection() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Select Payment Method',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 4),
          Text(
            'Choose your preferred UPI app',
            style: TextStyle(fontSize: 14, color: Colors.grey.shade600),
          ),
        ],
      ),
    );
  }

  Widget _buildUpiAppsSection() {
    final paymentState = ref.watch(paymentProvider);
    final availableApps = paymentState.availableUpiApps;

    if (availableApps.isEmpty) {
      return Container(
        margin: const EdgeInsets.all(16),
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: Colors.orange.shade50,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.orange.shade200),
        ),
        child: Row(
          children: [
            Icon(Icons.info_outline, color: Colors.orange.shade700),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                'No UPI apps found. Please install Google Pay, PhonePe, or Paytm.',
                style: TextStyle(color: Colors.orange.shade700, fontSize: 14),
              ),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      padding: const EdgeInsets.symmetric(horizontal: 16),
      itemCount: availableApps.length,
      itemBuilder: (context, index) {
        final app = availableApps[index];
        final method = _getPaymentMethodFromPackage(app.packageName);
        final isSelected = paymentState.selectedMethod == method;

        return _buildPaymentMethodTile(
          icon: _getAppIcon(app.packageName),
          name: app.name,
          subtitle: 'Pay via UPI',
          isSelected: isSelected,
          onTap: () => ref.read(paymentProvider.notifier).selectMethod(method),
        );
      },
    );
  }

  Widget _buildOtherMethodsSection() {
    return Container(
      margin: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Other Methods',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 12),
          _buildPaymentMethodTile(
            icon: Icons.credit_card,
            name: 'Card / Net Banking',
            subtitle: 'Razorpay',
            iconColor: Colors.purple,
            isSelected:
                ref.watch(paymentProvider).selectedMethod ==
                PaymentMethod.razorpay,
            onTap: () => ref
                .read(paymentProvider.notifier)
                .selectMethod(PaymentMethod.razorpay),
          ),
          const SizedBox(height: 8),
          _buildPaymentMethodTile(
            icon: Icons.money,
            name: 'Cash Payment',
            subtitle: 'Pay at office',
            iconColor: Colors.green,
            isSelected:
                ref.watch(paymentProvider).selectedMethod == PaymentMethod.cash,
            onTap: () => ref
                .read(paymentProvider.notifier)
                .selectMethod(PaymentMethod.cash),
          ),
        ],
      ),
    );
  }

  Widget _buildPaymentMethodTile({
    required String name,
    required String subtitle,
    IconData? icon,
    Widget? iconWidget,
    Color? iconColor,
    required bool isSelected,
    required VoidCallback onTap,
  }) {
    return Card(
      elevation: isSelected ? 2 : 0,
      color: isSelected ? Colors.blue.shade50 : Colors.white,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: BorderSide(
          color: isSelected ? Colors.blue : Colors.grey.shade200,
          width: isSelected ? 2 : 1,
        ),
      ),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              iconWidget ??
                  Container(
                    width: 48,
                    height: 48,
                    decoration: BoxDecoration(
                      color: (iconColor ?? Colors.blue).withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Icon(
                      icon ?? Icons.payment,
                      color: iconColor ?? Colors.blue,
                      size: 24,
                    ),
                  ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      name,
                      style: const TextStyle(
                        fontWeight: FontWeight.w600,
                        fontSize: 16,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      subtitle,
                      style: TextStyle(
                        color: Colors.grey.shade600,
                        fontSize: 12,
                      ),
                    ),
                  ],
                ),
              ),
              Icon(
                isSelected ? Icons.check_circle : Icons.radio_button_unchecked,
                color: isSelected ? Colors.blue : Colors.grey,
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildErrorCard(String error) {
    return Container(
      margin: const EdgeInsets.all(16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.red.shade50,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.red.shade200),
      ),
      child: Row(
        children: [
          Icon(Icons.error_outline, color: Colors.red.shade700),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              error,
              style: TextStyle(color: Colors.red.shade700, fontSize: 14),
            ),
          ),
          IconButton(
            icon: const Icon(Icons.close),
            onPressed: () => ref.read(paymentProvider.notifier).clearError(),
            color: Colors.red.shade700,
          ),
        ],
      ),
    );
  }

  Widget _buildBottomBar(PaymentState paymentState) {
    final selectedMethod = paymentState.selectedMethod;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, -4),
          ),
        ],
      ),
      child: SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            if (selectedMethod != null)
              Padding(
                padding: const EdgeInsets.only(bottom: 12),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(
                      'Pay with ${selectedMethod.displayName}',
                      style: TextStyle(
                        color: Colors.grey.shade600,
                        fontSize: 14,
                      ),
                    ),
                  ],
                ),
              ),
            SizedBox(
              width: double.infinity,
              height: 50,
              child: ElevatedButton(
                onPressed: paymentState.isLoading || selectedMethod == null
                    ? null
                    : () => _processPayment(selectedMethod),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF4285F4),
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  elevation: 0,
                ),
                child: paymentState.isLoading
                    ? const SizedBox(
                        width: 24,
                        height: 24,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          valueColor: AlwaysStoppedAnimation<Color>(
                            Colors.white,
                          ),
                        ),
                      )
                    : Text(
                        'Pay ₹${widget.amount.toStringAsFixed(2)}',
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _processPayment(PaymentMethod method) async {
    // Get user info from auth provider (implement as needed)
    const userId = 'current_user_id';
    const userType = 'customer';

    bool success = false;

    switch (method) {
      case PaymentMethod.gpay:
      case PaymentMethod.phonepe:
      case PaymentMethod.paytm:
      case PaymentMethod.amazonPay:
      case PaymentMethod.bhim:
        success = await ref
            .read(paymentProvider.notifier)
            .initiateUpiPayment(
              method: method,
              amount: widget.amount,
              description: widget.description,
              userId: userId,
              userType: userType,
              entityType: widget.entityType,
              entityId: widget.entityId,
            );
        break;
      case PaymentMethod.razorpay:
        // Handle Razorpay
        _showRazorpayNotImplemented();
        return;
      case PaymentMethod.cash:
        // Handle Cash
        _showCashPaymentDialog();
        return;
    }

    if (success && mounted) {
      _showSuccessDialog();
    }
  }

  void _showRazorpayNotImplemented() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Use UPI Instead'),
        content: const Text(
          'Card and Net Banking will be available soon. '
          'Please use one of the UPI options (GPay, PhonePe, Paytm) above to complete your payment instantly.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('OK, Use UPI'),
          ),
        ],
      ),
    );
  }

  void _showCashPaymentDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Cash Payment'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.store, size: 64, color: Colors.green),
            const SizedBox(height: 16),
            const Text(
              'Please visit our office to complete the payment:',
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            const Text(
              'APS Dream Homes Pvt Ltd\nPlot No. 123, Main Road\nGorakhpur, UP - 273001',
              textAlign: TextAlign.center,
              style: TextStyle(fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            Text(
              'Amount: ₹${widget.amount.toStringAsFixed(2)}',
              style: const TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: Colors.green,
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              context.go('/my-bookings');
            },
            child: const Text('Confirm'),
          ),
        ],
      ),
    );
  }

  void _showSuccessDialog() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        title: const Text('Payment Successful!'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.check_circle, size: 64, color: Colors.green),
            const SizedBox(height: 16),
            const Text(
              'Your payment has been processed successfully.',
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            Text(
              'Transaction ID: ${ref.read(currentTransactionProvider)?.transactionRefId ?? 'N/A'}',
              style: const TextStyle(fontSize: 12, color: Colors.grey),
            ),
          ],
        ),
        actions: [
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              context.go('/my-bookings');
            },
            child: const Text('View Bookings'),
          ),
        ],
      ),
    );
  }

  PaymentMethod _getPaymentMethodFromPackage(String packageName) {
    switch (packageName) {
      case 'com.google.android.apps.nbu.paisa.user':
        return PaymentMethod.gpay;
      case 'com.phonepe.app':
        return PaymentMethod.phonepe;
      case 'net.one97.paytm':
        return PaymentMethod.paytm;
      case 'in.amazon.mShop.android.shopping':
        return PaymentMethod.amazonPay;
      case 'in.org.npci.upiapp':
        return PaymentMethod.bhim;
      default:
        return PaymentMethod.gpay;
    }
  }

  IconData _getAppIcon(String packageName) {
    // Return icon data based on package
    switch (packageName) {
      case 'com.google.android.apps.nbu.paisa.user':
        return Icons.account_balance_wallet;
      case 'com.phonepe.app':
        return Icons.phone_android;
      case 'net.one97.paytm':
        return Icons.payment;
      default:
        return Icons.account_balance;
    }
  }
}

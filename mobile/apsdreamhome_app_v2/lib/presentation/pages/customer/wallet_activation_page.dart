import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:razorpay_flutter/razorpay_flutter.dart';

import '../../../core/theme/app_theme.dart';
import '../../../data/repositories/kyc_repository_provider.dart';

final _inr = NumberFormat('#,##,###');

/// Wallet activation packages: Basic / Pro / Premium.
///
/// Flow: fetch packages + my-wallet status -> Activate -> backend purchase
/// (Razorpay order) -> razorpay_flutter checkout -> backend verify -> refresh.
class WalletActivationPage extends ConsumerStatefulWidget {
  const WalletActivationPage({super.key});

  @override
  ConsumerState<WalletActivationPage> createState() =>
      _WalletActivationPageState();
}

class _WalletActivationPageState extends ConsumerState<WalletActivationPage> {
  bool _isLoading = true;
  String? _error;
  List<Map<String, dynamic>> _packages = [];
  Map<String, dynamic>? _currentPackage;
  bool _isActivated = false;
  bool _purchasingId = false;
  int? _purchasingPackageId;
  int? _pendingPurchaseId;

  late final Razorpay _razorpay;

  static const _featureLabels = <String, String>{
    'emi_payment': 'Pay EMI from Wallet',
    'referral_earnings_view': 'View Referral Earnings',
    'withdrawal_request': 'Request Bank Withdrawal',
    'booking_adjustment': 'Apply Wallet to New Booking',
    'priority_support': 'Priority Customer Support',
    'auto_emi_deduction': 'Auto EMI Deduction',
    'detailed_analytics': 'Detailed Earnings Analytics',
    'dedicated_manager': 'Dedicated Relationship Manager',
    'vip_offers': 'Exclusive VIP Offers',
    'zero_withdrawal_fee': 'Zero Withdrawal Fees',
  };

  @override
  void initState() {
    super.initState();
    _razorpay = Razorpay();
    _razorpay.on(Razorpay.EVENT_PAYMENT_SUCCESS, _onPaymentSuccess);
    _razorpay.on(Razorpay.EVENT_PAYMENT_ERROR, _onPaymentError);
    _razorpay.on(Razorpay.EVENT_EXTERNAL_WALLET, _onExternalWallet);
    _fetchData();
  }

  @override
  void dispose() {
    _razorpay.clear();
    super.dispose();
  }

  Future<void> _fetchData() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final api = ref.read(apiServiceProvider);
      final results = await Future.wait([
        api.getWalletActivationPackages(),
        api.getMyWalletActivation(),
      ]);
      if (!mounted) return;
      final pkgs = (results[0] as List)
          .map((e) => Map<String, dynamic>.from(e as Map))
          .toList();
      final wallet =
          (results[1] as Map<String, dynamic>? ?? <String, dynamic>{});
      setState(() {
        _packages = pkgs;
        _isActivated = wallet['is_activated'] == true;
        final cp = wallet['current_package'];
        _currentPackage = cp is Map ? Map<String, dynamic>.from(cp) : null;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  Future<void> _purchase(Map<String, dynamic> pkg) async {
    final packageId = (pkg['id'] as num?)?.toInt() ?? 0;
    if (packageId <= 0 || _purchasingId) return;
    setState(() {
      _purchasingId = true;
      _purchasingPackageId = packageId;
    });
    try {
      final api = ref.read(apiServiceProvider);
      final res = await api.purchaseWalletActivation(packageId: packageId);
      if (!mounted) return;
      if (res['success'] != true) {
        throw Exception(res['message']?.toString() ?? 'Failed to start payment');
      }
      final purchaseId = (res['purchase_id'] as num?)?.toInt() ?? 0;
      final orderId = res['order_id']?.toString() ??
          res['payment_ref']?.toString() ??
          '';
      final keyId = res['key_id']?.toString() ?? '';
      final amount = (res['amount'] as num?)?.toDouble() ?? 0;
      if (purchaseId <= 0 || orderId.isEmpty || keyId.isEmpty) {
        throw Exception('Payment order incomplete. Please try again.');
      }
      _pendingPurchaseId = purchaseId;
      _razorpay.open({
        'key': keyId,
        'amount': (amount * 100).round(),
        'name': 'APS Dream Home',
        'description': 'Wallet Activation: ${pkg['name']}',
        'order_id': orderId,
        'theme': {'color': '#0D9488'},
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _purchasingId = false;
        _purchasingPackageId = null;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Payment failed to start: $e')),
      );
    }
  }

  Future<void> _onPaymentSuccess(PaymentSuccessResponse resp) async {
    final purchaseId = _pendingPurchaseId;
    if (purchaseId == null) return;
    try {
      final api = ref.read(apiServiceProvider);
      final res = await api.verifyWalletActivationPayment(
        purchaseId: purchaseId,
        razorpayPaymentId: resp.paymentId ?? '',
        razorpayOrderId: resp.orderId ?? '',
        razorpaySignature: resp.signature ?? '',
      );
      if (!mounted) return;
      setState(() {
        _purchasingId = false;
        _purchasingPackageId = null;
        _pendingPurchaseId = null;
      });
      if (res['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Wallet activated successfully!')),
        );
        await _fetchData();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
              content: Text(
                  'Activation failed: ${res['message'] ?? 'unknown error'}')),
        );
      }
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _purchasingId = false;
        _purchasingPackageId = null;
        _pendingPurchaseId = null;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Verification failed: $e')),
      );
    }
  }

  void _onPaymentError(PaymentFailureResponse resp) {
    if (!mounted) return;
    setState(() {
      _purchasingId = false;
      _purchasingPackageId = null;
      _pendingPurchaseId = null;
    });
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
          content: Text(
              'Payment failed: ${resp.message ?? 'cancelled'} (code ${resp.code ?? '-'})')),
    );
  }

  void _onExternalWallet(ExternalWalletResponse resp) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
          content:
              Text('External wallet selected: ${resp.walletName ?? ''}')),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.surfaceColor,
      appBar: AppBar(
        title: const Text('Activate Wallet'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new, size: 20),
          onPressed: () => context.pop(),
        ),
      ),
      body: _isLoading
          ? const Center(
              child:
                  CircularProgressIndicator(color: AppTheme.primaryColor),
            )
          : _error != null
              ? _buildError()
              : RefreshIndicator(
                  onRefresh: _fetchData,
                  color: AppTheme.primaryColor,
                  child: SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _buildHeader(),
                        const SizedBox(height: 16),
                        if (_isActivated && _currentPackage != null)
                          _buildCurrentPlan(),
                        ..._packages.map(_buildPackageCard),
                        const SizedBox(height: 8),
                        _buildReferralNote(),
                        const SizedBox(height: 24),
                      ],
                    ),
                  ),
                ),
    );
  }

  Widget _buildError() => Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.error_outline, size: 56, color: Colors.red),
              const SizedBox(height: 12),
              Text(_error ?? 'Something went wrong',
                  textAlign: TextAlign.center),
              const SizedBox(height: 12),
              ElevatedButton(
                onPressed: _fetchData,
                child: const Text('Retry'),
              ),
            ],
          ),
        ),
      );

  Widget _buildHeader() => Container(
        width: double.infinity,
        padding: const EdgeInsets.symmetric(vertical: 28, horizontal: 24),
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [
              Color(0xFF0D47A1),
              AppTheme.primaryColor,
              AppTheme.secondaryColor,
            ],
          ),
          borderRadius: BorderRadius.circular(20),
          boxShadow: [
            BoxShadow(
              color: AppTheme.primaryColor.withValues(alpha: 0.3),
              blurRadius: 20,
              offset: const Offset(0, 8),
            ),
          ],
        ),
        child: const Column(
          children: [
            Icon(Icons.account_balance_wallet_rounded,
                color: Colors.white, size: 44),
            SizedBox(height: 12),
            Text(
              'Unlock Your Wallet',
              style: TextStyle(
                  color: Colors.white,
                  fontSize: 22,
                  fontWeight: FontWeight.w800),
            ),
            SizedBox(height: 8),
            Text(
              'Pay EMI from wallet, withdraw to bank, adjust on new bookings and track every referral rupee.',
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.white70, fontSize: 13),
            ),
          ],
        ),
      );

  Widget _buildCurrentPlan() {
    final pkg = _currentPackage!;
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.green.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.green.withValues(alpha: 0.3)),
      ),
      child: Row(
        children: [
          const Icon(Icons.check_circle, color: Colors.green, size: 28),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Active: ${pkg['name'] ?? 'Wallet'}',
                  style: const TextStyle(fontWeight: FontWeight.w700),
                ),
                if ((pkg['activated_at']?.toString() ?? '').isNotEmpty)
                  Text(
                    'Activated ${pkg['activated_at']}',
                    style: TextStyle(
                        fontSize: 12, color: Colors.grey.shade600),
                  ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPackageCard(Map<String, dynamic> pkg) {
    final id = (pkg['id'] as num?)?.toInt() ?? 0;
    final name = pkg['name']?.toString() ?? 'Package';
    final slug = pkg['slug']?.toString() ?? '';
    final desc = pkg['description']?.toString() ?? '';
    final price = (pkg['price'] as num?)?.toDouble() ?? 0;
    final reward = (pkg['referral_reward'] as num?)?.toDouble() ?? 0;
    final pctL1 = (pkg['referral_pct_l1'] as num?)?.toDouble() ?? 0;
    final pctL2 = (pkg['referral_pct_l2'] as num?)?.toDouble() ?? 0;
    String fmtPct(double v) =>
        v.toStringAsFixed(v.truncateToDouble() == v ? 0 : 2);
    final features = Map<String, dynamic>.from(pkg['features'] as Map? ?? {});
    final isPopular = slug == 'pro';
    final isOwned =
        _currentPackage != null && _currentPackage!['slug'] == slug;
    final isBusy = _purchasingId && _purchasingPackageId == id;
    final icon = slug == 'basic'
        ? Icons.wallet_rounded
        : (slug == 'pro' ? Icons.diamond_rounded : Icons.workspace_premium_rounded);
    final accent = slug == 'basic'
        ? Colors.blue
        : (slug == 'pro' ? Colors.purple : Colors.amber.shade800);

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: isPopular ? accent.withValues(alpha: 0.5) : Colors.grey.shade200,
          width: isPopular ? 2 : 1,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.06),
            blurRadius: 16,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (isPopular)
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(vertical: 6),
              decoration: BoxDecoration(
                color: accent,
                borderRadius: const BorderRadius.only(
                  topLeft: Radius.circular(18),
                  topRight: Radius.circular(18),
                ),
              ),
              child: const Text(
                'MOST POPULAR',
                textAlign: TextAlign.center,
                style: TextStyle(
                    color: Colors.white,
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    letterSpacing: 1.5),
              ),
            ),
          Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      width: 52,
                      height: 52,
                      decoration: BoxDecoration(
                        color: accent.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(14),
                      ),
                      child: Icon(icon, color: accent, size: 26),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(name,
                              style: const TextStyle(
                                  fontWeight: FontWeight.w800, fontSize: 17)),
                          Text('₹${_inr.format(price)} / year',
                              style: const TextStyle(
                                  fontWeight: FontWeight.w700,
                                  fontSize: 15,
                                  color: AppTheme.primaryColor)),
                        ],
                      ),
                    ),
                  ],
                ),
                if (desc.isNotEmpty) ...[
                  const SizedBox(height: 8),
                  Text(desc,
                      style: TextStyle(
                          fontSize: 13, color: Colors.grey.shade600)),
                ],
                const SizedBox(height: 12),
                ..._featureLabels.entries.map((e) {
                  final on = features[e.key] == true;
                  return Padding(
                    padding: const EdgeInsets.symmetric(vertical: 3),
                    child: Row(
                      children: [
                        Icon(
                          on ? Icons.check_circle : Icons.cancel,
                          size: 16,
                          color: on ? Colors.green : Colors.grey.shade400,
                        ),
                        const SizedBox(width: 8),
                        Text(
                          e.value,
                          style: TextStyle(
                            fontSize: 13,
                            color: on
                                ? Colors.black87
                                : Colors.grey.shade400,
                          ),
                        ),
                      ],
                    ),
                  );
                }),
                if (reward > 0) ...[
                  const SizedBox(height: 12),
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: Colors.amber.withValues(alpha: 0.12),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                          color: Colors.amber.withValues(alpha: 0.4)),
                    ),
                    child: Text(
                      'Refer a friend — earn ₹${reward.toStringAsFixed(2)} (${fmtPct(pctL1)}%${pctL2 > 0 ? ' + ${fmtPct(pctL2)}% L2' : ''}) when they activate',
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: Color(0xFF92400E)),
                    ),
                  ),
                ],
                const SizedBox(height: 14),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: (isOwned || _purchasingId)
                        ? null
                        : () => _purchase(pkg),
                    icon: isBusy
                        ? const SizedBox(
                            width: 18,
                            height: 18,
                            child: CircularProgressIndicator(
                                strokeWidth: 2, color: Colors.white),
                          )
                        : Icon(isOwned
                            ? Icons.check_circle
                            : Icons.lock_open_rounded),
                    label: Text(isOwned
                        ? 'Already Active'
                        : isBusy
                            ? 'Processing...'
                            : 'Activate for ₹${_inr.format(price)}'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: accent,
                      foregroundColor: Colors.white,
                      padding:
                          const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildReferralNote() => Container(
        width: double.infinity,
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: AppTheme.primaryColor.withValues(alpha: 0.06),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
              color: AppTheme.primaryColor.withValues(alpha: 0.2)),
        ),
        child: const Row(
          children: [
            Icon(Icons.info_outline, color: AppTheme.primaryColor),
            SizedBox(width: 12),
            Expanded(
              child: Text(
                'Wallet activation unlocks EMI payments, withdrawals and referral tracking. Your referrer earns a reward when you activate.',
                style: TextStyle(fontSize: 12, color: Colors.black87),
              ),
            ),
          ],
        ),
      );
}

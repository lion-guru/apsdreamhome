import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../data/repositories/kyc_repository_provider.dart';
import '../../../data/services/colony_service.dart';
import '../../../data/models/plot_model.dart';
import '../../widgets/app_widgets.dart';

class BookingPage extends ConsumerStatefulWidget {
  final String plotId;
  final String? promoCode;

  const BookingPage({super.key, required this.plotId, this.promoCode});

  @override
  ConsumerState<BookingPage> createState() => _BookingPageState();
}

class _BookingPageState extends ConsumerState<BookingPage> {
  final formKey = GlobalKey<FormState>();
  final nameController = TextEditingController();
  final phoneController = TextEditingController();
  final emailController = TextEditingController();
  final addressController = TextEditingController();
  final promoCodeController = TextEditingController();

  String selectedPaymentPlan = 'token';
  bool agreeToTerms = false;
  bool isLoading = false;

  bool _applyingPromo = false;
  Map<String, dynamic>? _appliedPromo;
  String? _promoMessage;
  bool _promoValid = false;
  bool _autoApplyScheduled = false;

  @override
  void initState() {
    super.initState();
    final prefill = widget.promoCode?.trim() ?? '';
    if (prefill.isNotEmpty) {
      promoCodeController.text = prefill.toUpperCase();
    }
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = ref.read(authProvider);
      if (user != null && mounted) {
        nameController.text = user.name;
        emailController.text = user.email;
        phoneController.text = user.phone ?? '';
      }
    });
  }

  double _promoDiscount() {
    if (_appliedPromo == null) return 0.0;
    final d = _appliedPromo!['discount_amount'];
    if (d is num) return d.toDouble();
    return double.tryParse('$d') ?? 0.0;
  }

  double _netPrice(PlotModel plot) =>
      (plot.totalPrice - _promoDiscount()).clamp(0.0, double.infinity).toDouble();

  double _paymentAmount(PlotModel plot) {
    final net = _netPrice(plot);
    return selectedPaymentPlan == 'full' ? net : net * 0.1;
  }

  Future<void> _applyPromo(PlotModel plot) async {
    final code = promoCodeController.text.trim().toUpperCase();
    if (code.isEmpty) {
      setState(() {
        _promoMessage = 'Please enter a promo code';
        _promoValid = false;
        _appliedPromo = null;
      });
      return;
    }
    setState(() {
      _applyingPromo = true;
      _promoMessage = null;
    });
    try {
      final api = ref.read(apiServiceProvider);
      final colonyId = int.tryParse(plot.colonyId);
      final result = await api.validatePromoCode(
        promoCode: code,
        plotValue: plot.totalPrice,
        colonyId: (colonyId != null && colonyId > 0) ? colonyId : null,
      );
      if (!mounted) return;
      final valid = result['valid'] == true;
      setState(() {
        _promoValid = valid;
        _appliedPromo = valid ? result : null;
        _promoMessage = (result['message'] ??
                (valid
                    ? 'Promo code applied!'
                    : 'Invalid promo code'))
            .toString();
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _promoValid = false;
        _appliedPromo = null;
        _promoMessage = 'Could not verify promo code. Check connection and retry.';
      });
    } finally {
      if (mounted) {
        setState(() => _applyingPromo = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final plotAsync = ref
        .watch(colonyServiceProvider)
        .getPlotById(widget.plotId);

    return Scaffold(
      appBar: AppBar(title: const Text('Book Plot')),
      body: FutureBuilder<PlotModel?>(
        future: plotAsync,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return Center(
              child: AppWidgets.shimmerLoading(
                child: Container(
                  height: 200,
                  margin: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                  ),
                ),
              ),
            );
          }

          final plot = snapshot.data;
          if (plot == null) {
            return AppWidgets.errorWidget(
              message: 'Plot not found',
              onRetry: () => setState(() {}),
            );
          }

          if (widget.promoCode != null &&
              widget.promoCode!.trim().isNotEmpty &&
              !_autoApplyScheduled) {
            _autoApplyScheduled = true;
            WidgetsBinding.instance.addPostFrameCallback((_) {
              if (mounted) _applyPromo(plot);
            });
          }

          return Column(
            children: [
              // Plot Summary Card
              buildPlotSummary(plot),

              // Booking Form
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(16),
                  child: Form(
                    key: formKey,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Customer Details',
                          style: Theme.of(context).textTheme.titleLarge
                              ?.copyWith(fontWeight: FontWeight.bold),
                        ),

                        const SizedBox(height: 16),

                        // Name
                        TextFormField(
                          controller: nameController,
                          decoration: const InputDecoration(
                            labelText: 'Full Name *',
                            prefixIcon: Icon(Icons.person_outline),
                          ),
                          validator: (value) {
                            if (value == null || value.isEmpty) {
                              return 'Please enter your name';
                            }
                            return null;
                          },
                        ),

                        const SizedBox(height: 16),

                        // Phone
                        TextFormField(
                          controller: phoneController,
                          keyboardType: TextInputType.phone,
                          maxLength: 10,
                          decoration: const InputDecoration(
                            labelText: 'Phone Number *',
                            prefixIcon: Icon(Icons.phone_outlined),
                            prefixText: '+91 ',
                            counterText: '',
                          ),
                          validator: (value) {
                            if (value == null || value.isEmpty) {
                              return 'Please enter phone number';
                            }
                            if (value.length != 10) {
                              return 'Please enter valid 10-digit number';
                            }
                            return null;
                          },
                        ),

                        const SizedBox(height: 16),

                        // Email
                        TextFormField(
                          controller: emailController,
                          keyboardType: TextInputType.emailAddress,
                          decoration: const InputDecoration(
                            labelText: 'Email Address',
                            prefixIcon: Icon(Icons.email_outlined),
                          ),
                        ),

                        const SizedBox(height: 16),

                        // Address
                        TextFormField(
                          controller: addressController,
                          maxLines: 2,
                          decoration: const InputDecoration(
                            labelText: 'Address',
                            prefixIcon: Icon(Icons.home_outlined),
                            alignLabelWithHint: true,
                          ),
                        ),

                        const SizedBox(height: 24),

                        // Festival Promo Code
                        _buildPromoSection(plot),

                        const SizedBox(height: 24),

                        // Payment Plan
                        Text(
                          'Payment Plan',
                          style: Theme.of(context).textTheme.titleLarge
                              ?.copyWith(fontWeight: FontWeight.bold),
                        ),

                        const SizedBox(height: 16),

                        // NOTE (S453): Radio.groupValue / Radio.onChanged were
                        // deprecated in Flutter 3.32 - a RadioGroup ancestor
                        // now owns the group value.
                        RadioGroup<String>(
                          groupValue: selectedPaymentPlan,
                          onChanged: (v) {
                            if (v != null) {
                              setState(() => selectedPaymentPlan = v);
                            }
                          },
                          child: Column(
                            children: [
                              buildPaymentPlanOption(
                          value: 'token',
                          title: 'Token Amount',
                          subtitle: 'Pay 10% to block the plot',
                          amount: _netPrice(plot) * 0.1,
                          selected: selectedPaymentPlan == 'token',
                          onSelect: () =>
                              setState(() => selectedPaymentPlan = 'token'),
                        ),

                        const SizedBox(height: 12),

                              buildPaymentPlanOption(
                                value: 'full',
                                title: 'Full Payment',
                                subtitle: 'Pay 100% and get extra benefits',
                                amount: _netPrice(plot),
                                selected: selectedPaymentPlan == 'full',
                                onSelect: () =>
                                    setState(() => selectedPaymentPlan = 'full'),
                              ),
                            ],
                          ),
                        ),

                        const SizedBox(height: 24),

                        // Terms
                        CheckboxListTile(
                          value: agreeToTerms,
                          onChanged: (value) {
                            setState(() => agreeToTerms = value ?? false);
                          },
                          title: const Text(
                            'I agree to the terms and conditions',
                            style: TextStyle(fontSize: 14),
                          ),
                          subtitle: Text(
                            'By booking, you agree to our booking policy and cancellation terms.',
                            style: TextStyle(
                              fontSize: 12,
                              color: Colors.grey.shade600,
                            ),
                          ),
                        ),

                        const SizedBox(height: 100),
                      ],
                    ),
                  ),
                ),
              ),
            ],
          );
        },
      ),

      // Bottom Action Bar
      bottomNavigationBar: FutureBuilder<PlotModel?>(
        future: plotAsync,
        builder: (context, snapshot) {
          final plot = snapshot.data;
          if (plot == null) return const SizedBox.shrink();

          final amount = _paymentAmount(plot);

          return Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.1),
                  blurRadius: 10,
                ),
              ],
            ),
            child: SafeArea(
              child: Row(
                children: [
                          Expanded(
                            child: Column(
                              mainAxisSize: MainAxisSize.min,
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  selectedPaymentPlan == 'token'
                                      ? 'Token Amount'
                                      : 'Total Amount',
                                  style: TextStyle(
                                    fontSize: 12,
                                    color: Colors.grey.shade600,
                                  ),
                                ),
                                if (_appliedPromo != null)
                                  Text(
                                    'After promo ₹${_promoDiscount().toStringAsFixed(0)} OFF',
                                    style: const TextStyle(
                                      fontSize: 10,
                                      color: Colors.green,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                        AppWidgets.priceTag(
                          amount: amount,
                          prefix: '₹',
                          style: const TextStyle(
                            fontSize: 24,
                            fontWeight: FontWeight.bold,
                            color: AppTheme.primaryColor,
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(width: 16),

                  SizedBox(
                    height: 56,
                    child: ElevatedButton.icon(
                      onPressed: isLoading || !agreeToTerms
                          ? null
                          : () => processBooking(plot),
                      icon: isLoading
                          ? const SizedBox(
                              width: 20,
                              height: 20,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                color: Colors.white,
                              ),
                            )
                          : const Icon(Icons.payment),
                      label: Text(
                        isLoading ? 'Processing...' : 'Pay Now',
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
        },
      ),
    );
  }

  Widget buildPlotSummary(PlotModel plot) {
    return Container(
      padding: const EdgeInsets.all(16),
      margin: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
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
                  color: Colors.white.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Icon(Icons.home_work, color: Colors.white),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Plot ${plot.plotNumber}',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    Text(
                      plot.colonyName,
                      style: TextStyle(
                        color: Colors.white.withValues(alpha: 0.8),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),

          const SizedBox(height: 16),

          const Divider(color: Colors.white24),

          const SizedBox(height: 16),

          Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              buildPlotInfo('Area', '${plot.areaSqft.toStringAsFixed(0)} sqft'),
              buildPlotInfo('Facing', plot.facing),
              buildPlotInfo(
                'Price',
                _appliedPromo != null
                    ? '₹${_netPrice(plot).toStringAsFixed(0)}'
                    : '₹${plot.totalPrice.toStringAsFixed(0)}',
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget buildPlotInfo(String label, String value) {
    return Column(
      children: [
        Text(
          label,
          style: TextStyle(
            color: Colors.white.withValues(alpha: 0.7),
            fontSize: 12,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          value,
          style: const TextStyle(
            color: Colors.white,
            fontWeight: FontWeight.bold,
          ),
        ),
      ],
    );
  }

  Widget _buildPromoSection(PlotModel plot) {
    final net = _netPrice(plot);
    final discount = _promoDiscount();
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppTheme.primaryColor.withValues(alpha: 0.04),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: _promoValid
              ? Colors.green.withValues(alpha: 0.6)
              : AppTheme.primaryColor.withValues(alpha: 0.25),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.confirmation_number_outlined,
                  color: AppTheme.primaryColor, size: 20),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  'Festival Promo Code',
                  style: Theme.of(context)
                      .textTheme
                      .titleMedium
                      ?.copyWith(fontWeight: FontWeight.bold),
                ),
              ),
              if (_promoValid)
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                  decoration: BoxDecoration(
                    color: Colors.green.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Text(
                    'APPLIED',
                    style: TextStyle(
                      color: Colors.green,
                      fontSize: 10,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
            ],
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              Expanded(
                child: TextFormField(
                  controller: promoCodeController,
                  textCapitalization: TextCapitalization.characters,
                  decoration: const InputDecoration(
                    hintText: 'e.g. FESTIVE21',
                    prefixIcon: Icon(Icons.local_offer_outlined),
                    isDense: true,
                  ),
                  onChanged: (_) {
                    // Typing again resets the previous validation state.
                    if (_appliedPromo != null || _promoMessage != null) {
                      setState(() {
                        _appliedPromo = null;
                        _promoValid = false;
                        _promoMessage = null;
                      });
                    }
                  },
                  onFieldSubmitted: (_) => _applyPromo(plot),
                ),
              ),
              const SizedBox(width: 8),
              SizedBox(
                height: 48,
                child: ElevatedButton(
                  onPressed: _applyingPromo ? null : () => _applyPromo(plot),
                  child: _applyingPromo
                      ? const SizedBox(
                          width: 18,
                          height: 18,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : const Text('Apply'),
                ),
              ),
            ],
          ),
          if (_promoMessage != null) ...[
            const SizedBox(height: 8),
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Icon(
                  _promoValid
                      ? Icons.check_circle_outline
                      : Icons.info_outline,
                  size: 16,
                  color: _promoValid ? Colors.green : Colors.red,
                ),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(
                    _promoMessage!,
                    style: TextStyle(
                      fontSize: 12,
                      color: _promoValid ? Colors.green : Colors.red,
                    ),
                  ),
                ),
              ],
            ),
          ],
          if (_promoValid && discount > 0) ...[
            const SizedBox(height: 8),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
              decoration: BoxDecoration(
                color: Colors.green.withValues(alpha: 0.08),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'You save ₹${discount.toStringAsFixed(0)} on this booking!',
                    style: const TextStyle(
                      color: Colors.green,
                      fontWeight: FontWeight.bold,
                      fontSize: 12,
                    ),
                  ),
                  Text(
                    'Agreement value: ₹${plot.totalPrice.toStringAsFixed(0)} - '
                    '₹${discount.toStringAsFixed(0)} = ₹${net.toStringAsFixed(0)}',
                    style: TextStyle(
                      color: Colors.green.shade800,
                      fontSize: 11,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget buildPaymentPlanOption({
    required String value,
    required String title,
    required String subtitle,
    required double amount,
    required bool selected,
    required VoidCallback onSelect,
  }) {
    return GestureDetector(
      onTap: onSelect,
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: selected
              ? AppTheme.primaryColor.withValues(alpha: 0.1)
              : Colors.grey.shade50,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: selected ? AppTheme.primaryColor : Colors.grey.shade300,
            width: selected ? 2 : 1,
          ),
        ),
        child: Row(
          children: [
            Radio<String>(
              value: value,
              activeColor: AppTheme.primaryColor,
            ),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: const TextStyle(fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    subtitle,
                    style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                  ),
                ],
              ),
            ),
            AppWidgets.priceTag(
              amount: amount,
              prefix: '₹',
              style: const TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: AppTheme.primaryColor,
              ),
            ),
          ],
        ),
      ),
    );
  }

  double getPaymentAmount(PlotModel plot) => _paymentAmount(plot);

  Future<void> processBooking(PlotModel plot) async {
    if (!formKey.currentState!.validate()) return;
    if (!agreeToTerms) {
      AppWidgets.showErrorSnackBar(context, 'Please agree to terms');
      return;
    }

    setState(() => isLoading = true);

    try {
      // Hold the plot first
      final user = ref.read(authProvider);
      final held = await ref
          .read(colonyServiceProvider)
          .holdPlot(
            plotId: plot.id,
            userId: user?.userId ?? '',
            holdDuration: const Duration(hours: 24),
          );

      if (!held) {
        throw Exception('Plot is no longer available');
      }

      // Calculate amount based on payment plan (net of any festival promo)
      final double amount = getPaymentAmount(plot);
      final double discount = _promoDiscount();

      // Navigate to payment page
      if (mounted) {
        context.push(
          '/payment',
          extra: {
            'amount': amount,
            'description': 'Plot Booking - ${plot.plotNumber}'
                '${_appliedPromo != null ? ' (Promo applied)' : ''}',
            'entity_type': 'plot',
            'entity_id': plot.id,
            'entity_name': '${plot.plotNumber} - ${plot.colonyName}',
            if (_appliedPromo != null && discount > 0) ...{
              'promo_code': _appliedPromo!['promo_code'] ?? '',
              'discount_amount': discount,
              'net_payable': _netPrice(plot),
            },
          },
        );
      }
    } catch (e) {
      if (!mounted) return;
      AppWidgets.showErrorSnackBar(context, 'Booking failed: $e');
    } finally {
      if (mounted) {
        setState(() => isLoading = false);
      }
    }
  }
}

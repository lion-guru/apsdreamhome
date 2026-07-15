import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../data/services/colony_service.dart';
import '../../../data/models/plot_model.dart';
import '../../widgets/app_widgets.dart';

class BookingPage extends ConsumerStatefulWidget {
  final String plotId;

  const BookingPage({super.key, required this.plotId});

  @override
  ConsumerState<BookingPage> createState() => _BookingPageState();
}

class _BookingPageState extends ConsumerState<BookingPage> {
  final formKey = GlobalKey<FormState>();
  final nameController = TextEditingController();
  final phoneController = TextEditingController();
  final emailController = TextEditingController();
  final addressController = TextEditingController();

  String selectedPaymentPlan = 'token';
  bool agreeToTerms = false;
  bool isLoading = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final user = ref.read(authProvider);
      if (user != null && mounted) {
        nameController.text = user.name;
        emailController.text = user.email;
        phoneController.text = user.phone ?? '';
      }
    });
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
            return const Center(child: CircularProgressIndicator());
          }

          final plot = snapshot.data;
          if (plot == null) {
            return AppWidgets.errorWidget(
              message: 'Plot not found',
              onRetry: () => setState(() {}),
            );
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

                        // Payment Plan
                        Text(
                          'Payment Plan',
                          style: Theme.of(context).textTheme.titleLarge
                              ?.copyWith(fontWeight: FontWeight.bold),
                        ),

                        const SizedBox(height: 16),

                        buildPaymentPlanOption(
                          value: 'token',
                          title: 'Token Amount',
                          subtitle: 'Pay 10% to block the plot',
                          amount: plot.totalPrice * 0.1,
                          selected: selectedPaymentPlan == 'token',
                          onSelect: () =>
                              setState(() => selectedPaymentPlan = 'token'),
                        ),

                        const SizedBox(height: 12),

                        buildPaymentPlanOption(
                          value: 'full',
                          title: 'Full Payment',
                          subtitle: 'Pay 100% and get extra benefits',
                          amount: plot.totalPrice,
                          selected: selectedPaymentPlan == 'full',
                          onSelect: () =>
                              setState(() => selectedPaymentPlan = 'full'),
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

          final amount = selectedPaymentPlan == 'token'
              ? plot.totalPrice * 0.1
              : plot.totalPrice;

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
              buildPlotInfo('Price', '₹${plot.totalPrice.toStringAsFixed(0)}'),
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
              groupValue: selectedPaymentPlan,
              onChanged: (v) => onSelect(),
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

  double getPaymentAmount(PlotModel plot) {
    switch (selectedPaymentPlan) {
      case 'token':
        return plot.totalPrice * 0.1;
      case 'full':
        return plot.totalPrice;
      default:
        return plot.totalPrice * 0.1;
    }
  }

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

      // Calculate amount based on payment plan
      final double amount = getPaymentAmount(plot);

      // Navigate to payment page
      if (mounted) {
        context.push(
          '/payment',
          extra: {
            'amount': amount,
            'description': 'Plot Booking - ${plot.plotNumber}',
            'entity_type': 'plot',
            'entity_id': plot.id,
            'entity_name': '${plot.plotNumber} - ${plot.colonyName}',
          },
        );
      }
    } catch (e) {
      AppWidgets.showErrorSnackBar(context, 'Booking failed: $e');
    } finally {
      if (mounted) {
        setState(() => isLoading = false);
      }
    }
  }
}

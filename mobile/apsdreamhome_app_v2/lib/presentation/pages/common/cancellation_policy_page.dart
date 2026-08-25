import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class CancellationPolicyPage extends StatelessWidget {
  const CancellationPolicyPage({super.key});

  static const _sections = [
    _LegalSection(
      'Booking Cancellation Policy',
      'This Cancellation Policy applies to all property and plot bookings made through APS Dream Home platform. By making a booking, you agree to the terms outlined below. Specific booking agreements may contain additional or modified cancellation terms that take precedence over this general policy.',
    ),
    _LegalSection(
      'Cancellation by Customer',
      'You may cancel your booking at any time by submitting a written cancellation request through our platform or by contacting our customer support. The refund amount depends on the timing of cancellation relative to the booking date and the type of property:\n\n• Plot Bookings: Cancellations within 7 days of booking - 90% refund; 8-30 days - 75% refund; 31-60 days - 50% refund; After 60 days - 25% refund (subject to deduction of administrative charges).\n• Property Bookings: As per the specific booking agreement and RERA guidelines.\n• Service Bookings: Cancellable up to 24 hours before scheduled service for full refund.',
    ),
    _LegalSection(
      'Cancellation by APS Dream Home',
      'We reserve the right to cancel any booking under the following circumstances:\n\n• Failure to complete payment within the stipulated time.\n• Discovery of fraudulent information or misrepresentation.\n• Legal or regulatory restrictions preventing the transaction.\n• Force majeure events (natural disasters, government orders, etc.).\n• Project cancellation or regulatory non-compliance by developer.\n\nIn such cases, a full refund of all amounts paid will be processed within 30 business days.',
    ),
    _LegalSection(
      'Refund Processing',
      'Refunds will be processed to the original payment method within 15-30 business days from the date of cancellation approval. Bank processing times may add additional 5-10 business days. Refund amounts are subject to deduction of:\n\n• Payment gateway charges (as applicable).\n• Administrative charges (up to 2% of booking amount).\n• Any statutory charges already paid to government authorities.\n• TDS deducted at source (if applicable).',
    ),
    _LegalSection(
      'RERA Compliance',
      'For properties registered under RERA, cancellation and refund policies shall comply with the Real Estate (Regulation and Development) Act, 2016 and the respective state RERA rules. In case of any conflict between this policy and RERA provisions, RERA provisions shall prevail.',
    ),
    _LegalSection(
      'Non-Refundable Components',
      'The following components are generally non-refundable:\n\n• Stamp duty and registration charges already paid to government.\n• GST/taxes paid on booking amount.\n• Legal documentation charges incurred.\n• Site visit and inspection charges (if conducted).\n• Any customization or upgrade charges for under-construction properties.',
    ),
    _LegalSection(
      'Cancellation Process',
      'To initiate cancellation:\n\n1. Log in to your account and navigate to "My Bookings".\n2. Select the booking you wish to cancel and click "Request Cancellation".\n3. Fill in the cancellation reason and submit.\n4. Our team will review and process within 3-5 business days.\n5. You will receive confirmation via email and SMS.\n\nAlternatively, email cancellations@apsdreamhome.com with your booking reference number.',
    ),
    _LegalSection(
      'Dispute Resolution',
      'Any disputes arising out of cancellation or refund shall first be attempted to be resolved through mutual discussion. If unresolved, the matter shall be referred to arbitration under the Arbitration and Conciliation Act, 1996, with the seat of arbitration at Gorakhpur, Uttar Pradesh. The language of arbitration shall be English.',
    ),
    _LegalSection(
      'Contact for Cancellations',
      'For cancellation-related queries or assistance:\n\nEmail: cancellations@apsdreamhome.com\nPhone: +91 70074 44842\nWhatsApp: +91 70074 44842\nOffice Hours: Monday-Saturday, 10:00 AM - 6:00 PM',
    ),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: MeshGradientBackground(
        child: SafeArea(
          child: CustomScrollView(
            slivers: [
              SliverToBoxAdapter(child: _buildHeader(context)),
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(20, 0, 20, 40),
                sliver: SliverList.separated(
                  itemCount: _sections.length,
                  separatorBuilder: (_, _) => const SizedBox(height: 16),
                  itemBuilder: (context, index) => GlassCard(
                    padding: const EdgeInsets.all(20),
                    opacity: 0.08,
                    blur: 8,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          _sections[index].title,
                          style: AppTheme.titleLarge.copyWith(
                            color: AppTheme.accentColor,
                            fontWeight: FontWeight.w700,
                            fontSize: 16,
                          ),
                        ),
                        const SizedBox(height: 12),
                        Text(
                          _sections[index].content,
                          style: TextStyle(
                            color: Colors.white.withValues(alpha: 0.8),
                            fontSize: 14,
                            height: 1.6,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeader(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(20),
      child: Column(
        children: [
          GestureDetector(
            onTap: () => context.pop(),
            child: Align(
              alignment: Alignment.centerLeft,
              child: Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(
                  Icons.arrow_back,
                  color: Colors.white,
                  size: 22,
                ),
              ),
            ),
          ),
          const SizedBox(height: 16),
          Container(
            width: 80,
            height: 80,
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
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
            child: const Icon(
              Icons.cancel_rounded,
              size: 40,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 16),
          ShaderMask(
            shaderCallback: (bounds) => const LinearGradient(
              colors: [AppTheme.primaryColor, AppTheme.accentColor],
            ).createShader(bounds),
            child: Text(
              'Cancellation Policy',
              style: Theme.of(context).textTheme.headlineLarge?.copyWith(
                color: Colors.white,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Understand your rights and refund eligibility.',
            style: Theme.of(
              context,
            ).textTheme.bodyMedium?.copyWith(color: Colors.white70),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}

class _LegalSection {
  final String title;
  final String content;
  const _LegalSection(this.title, this.content);
}
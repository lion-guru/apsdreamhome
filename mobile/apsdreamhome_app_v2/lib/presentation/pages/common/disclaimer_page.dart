import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class DisclaimerPage extends StatelessWidget {
  const DisclaimerPage({super.key});

  static const _sections = [
    _LegalSection(
      'General Disclaimer',
      'The information provided on APS Dream Home website, mobile application, and associated services ("Platform") is for general informational purposes only. While we endeavor to keep the information up-to-date and correct, we make no representations or warranties of any kind, express or implied, about the completeness, accuracy, reliability, suitability, or availability of the Platform or the information, products, services, or related graphics contained on the Platform for any purpose.',
    ),
    _LegalSection(
      'Property Information',
      'Property listings, prices, specifications, floor plans, amenities, and availability are provided by property owners, developers, and agents. APS Dream Home does not independently verify all information and disclaims any liability for inaccuracies, omissions, or changes. All property-related decisions should be based on independent verification and professional advice.',
    ),
    _LegalSection(
      'Financial Calculators & Tools',
      'The financial calculators (EMI, stamp duty, rental yield, etc.) provided on the Platform are for estimation purposes only. Results are based on standard formulas and assumptions and may not reflect actual costs, taxes, or returns. Actual figures may vary based on location, lender policies, government regulations, and individual circumstances. Consult a qualified financial advisor before making financial decisions.',
    ),
    _LegalSection(
      'Legal & Regulatory Information',
      'Legal content including RERA information, stamp duty rates, registration procedures, and compliance requirements is provided for general guidance only. Laws and regulations change frequently and vary by jurisdiction. This information does not constitute legal advice. You should consult a qualified legal professional for advice specific to your situation.',
    ),
    _LegalSection(
      'Third-Party Links & Services',
      'The Platform may contain links to third-party websites, services, and service providers (banks, legal firms, inspection agencies, etc.). We do not endorse, control, or assume responsibility for the content, privacy policies, or practices of any third-party websites or services. Your use of third-party services is at your own risk.',
    ),
    _LegalSection(
      'No Professional Advice',
      'Nothing on the Platform constitutes professional legal, financial, tax, engineering, or real estate advice. The Platform is not a substitute for consultation with qualified professionals. Any reliance you place on information from the Platform is strictly at your own risk.',
    ),
    _LegalSection(
      'Limitation of Liability',
      'In no event shall APS Dream Home, its directors, employees, agents, or affiliates be liable for any direct, indirect, incidental, special, consequential, or punitive damages arising out of or in connection with your use of the Platform, including but not limited to loss of profits, data, goodwill, or business opportunities, even if advised of the possibility of such damages.',
    ),
    _LegalSection(
      'Changes to Platform',
      'We reserve the right to modify, suspend, or discontinue any aspect of the Platform at any time without prior notice. We shall not be liable for any modification, price change, suspension, or discontinuance of the Platform or any part thereof.',
    ),
    _LegalSection(
      'Contact Us',
      'If you have any questions about this Disclaimer, please contact us at:\n\nEmail: legal@apsdreamhome.com\nPhone: +91 70074 44842\nAddress: APS Dream Home, Gorakhpur, Uttar Pradesh',
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
              Icons.warning_amber_rounded,
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
              'Disclaimer',
              style: Theme.of(context).textTheme.headlineLarge?.copyWith(
                color: Colors.white,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Please read before using our services.',
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
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class PrivacyPolicyPage extends StatelessWidget {
  const PrivacyPolicyPage({super.key});

  static const _sections = [
    _PrivacySection(
      'Information We Collect',
      'We collect information you provide directly to us, such as when you create an account, submit a property inquiry, schedule a site visit, or contact us for support. This may include your name, email address, phone number, address, property preferences, and financial information for booking purposes.',
    ),
    _PrivacySection(
      'How We Use Your Information',
      'We use your information to provide and improve our services, process property bookings and payments, send you updates about your inquiries and bookings, communicate about new properties and offers, and comply with legal obligations. We may also use aggregated data for analytics and service improvement.',
    ),
    _PrivacySection(
      'Information Sharing',
      'We do not sell your personal information. We may share your information with: (1) our trusted partners and service providers who assist in delivering our services, (2) property sellers/agents when you inquire about their listings, (3) financial institutions for payment processing, (4) legal authorities when required by law, and (5) in connection with a business transfer or merger.',
    ),
    _PrivacySection(
      'Data Security',
      'We implement appropriate technical and organizational security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. This includes encryption, secure servers, access controls, and regular security assessments. However, no method of transmission over the Internet is 100% secure.',
    ),
    _PrivacySection(
      'Your Rights',
      'You have the right to access, update, or delete your personal information. You can manage your preferences in your account settings or contact us to exercise your data protection rights. You may also opt-out of marketing communications at any time.',
    ),
    _PrivacySection(
      'Cookies & Tracking',
      'Our website and app use cookies and similar technologies to enhance your experience, analyze usage, and provide personalized content. You can control cookies through your browser settings. Disabling cookies may affect some functionality.',
    ),
    _PrivacySection(
      'Third-Party Links',
      'Our services may contain links to third-party websites. We are not responsible for the privacy practices or content of these external sites. We encourage you to review their privacy policies.',
    ),
    _PrivacySection(
      'Children\'s Privacy',
      'Our services are not directed to children under 18. We do not knowingly collect personal information from children. If you believe we have collected information from a child, please contact us immediately.',
    ),
    _PrivacySection(
      'Changes to This Policy',
      'We may update this Privacy Policy from time to time. We will notify you of any material changes by posting the new policy on this page and updating the effective date. Your continued use of our services constitutes acceptance of the updated policy.',
    ),
    _PrivacySection(
      'Contact Us',
      'If you have questions about this Privacy Policy or our data practices, please contact us at:\n\nEmail: privacy@apsdreamhome.com\nPhone: +91 70074 44842\nAddress: APS Dream Home, Gorakhpur, Uttar Pradesh',
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
                  separatorBuilder: (_, __) => const SizedBox(height: 16),
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
              Icons.privacy_tip_rounded,
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
              'Privacy Policy',
              style: Theme.of(context).textTheme.headlineLarge?.copyWith(
                color: Colors.white,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Last updated: July 2026\nYour privacy matters to us.',
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

class _PrivacySection {
  final String title;
  final String content;
  const _PrivacySection(this.title, this.content);
}

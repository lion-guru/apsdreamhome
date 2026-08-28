import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class TermsConditionsPage extends StatelessWidget {
  const TermsConditionsPage({super.key});

  static const _sections = [
    _LegalSection(
      'Acceptance of Terms',
      'By accessing and using APS Dream Home website, mobile application, and services ("Services"), you accept and agree to be bound by the terms and conditions of this agreement. If you do not agree to these terms, please do not use our Services.',
    ),
    _LegalSection(
      'Description of Services',
      'APS Dream Home provides a comprehensive real estate platform including property listings, plot bookings, colony information, financial calculators, legal document management, and associated services. We reserve the right to modify, suspend, or discontinue any part of the Services at any time without prior notice.',
    ),
    _LegalSection(
      'User Accounts & Registration',
      'To access certain features, you must register for an account. You agree to provide accurate, current, and complete information during registration and to update such information to keep it accurate. You are responsible for safeguarding your password and for all activities that occur under your account. You must notify us immediately of any unauthorized use of your account.',
    ),
    _LegalSection(
      'Property Listings & Information',
      'All property listings, prices, availability, and specifications are provided for informational purposes only. While we strive for accuracy, we do not warrant that all information is complete, accurate, or up-to-date. Property prices, availability, and terms are subject to change without notice. All bookings and purchases are subject to separate agreements.',
    ),
    _LegalSection(
      'Bookings & Payments',
      'Property bookings require payment of booking amounts as specified. Payment terms, cancellation policies, and refund procedures are governed by the specific booking agreement. All payments are processed through secure payment gateways. We are not responsible for payment failures due to technical issues beyond our control.',
    ),
    _LegalSection(
      'Intellectual Property',
      'All content on our Services, including text, graphics, logos, images, software, and trademarks, is the property of APS Dream Home or its licensors and is protected by Indian and international copyright laws. You may not reproduce, distribute, or create derivative works without our prior written consent.',
    ),
    _LegalSection(
      'User Conduct',
      'You agree not to: (1) use the Services for any unlawful purpose, (2) interfere with or disrupt the Services, (3) attempt to gain unauthorized access to any system or data, (3) transmit any viruses or malicious code, (4) scrape or extract data without permission, (5) post false or misleading information, or (6) harass other users.',
    ),
    _LegalSection(
      'Privacy & Data Protection',
      'Your use of our Services is also governed by our Privacy Policy, which is incorporated into these Terms by reference. We collect and process your personal information in accordance with applicable data protection laws. By using our Services, you consent to such collection and processing.',
    ),
    _LegalSection(
      'Disclaimer of Warranties',
      'THE SERVICES ARE PROVIDED "AS IS" AND "AS AVAILABLE" WITHOUT WARRANTIES OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO IMPLIED WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, AND NON-INFRINGEMENT. WE DO NOT WARRANT THAT THE SERVICES WILL BE UNINTERRUPTED, ERROR-FREE, OR SECURE.',
    ),
    _LegalSection(
      'Limitation of Liability',
      'TO THE MAXIMUM EXTENT PERMITTED BY LAW, APS DREAM HOME SHALL NOT BE LIABLE FOR ANY INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL, OR PUNITIVE DAMAGES, INCLUDING LOSS OF PROFITS, DATA, OR GOODWILL, ARISING OUT OF OR RELATED TO YOUR USE OF THE SERVICES. OUR TOTAL LIABILITY SHALL NOT EXCEED THE AMOUNT PAID BY YOU IN THE 12 MONTHS PRECEDING THE CLAIM.',
    ),
    _LegalSection(
      'Indemnification',
      'You agree to indemnify, defend, and hold harmless APS Dream Home and its officers, directors, employees, and agents from any claims, damages, losses, and expenses (including reasonable attorney fees) arising out of your use of the Services, violation of these Terms, or infringement of any third-party rights.',
    ),
    _LegalSection(
      'Governing Law & Jurisdiction',
      'These Terms shall be governed by and construed in accordance with the laws of India. Any disputes arising out of or relating to these Terms shall be subject to the exclusive jurisdiction of the courts in Gorakhpur, Uttar Pradesh.',
    ),
    _LegalSection(
      'Changes to Terms',
      'We may modify these Terms at any time by posting the updated version on this page with a revised effective date. Your continued use of the Services after any changes constitutes acceptance of the new Terms. We encourage you to review these Terms periodically.',
    ),
    _LegalSection(
      'Contact Information',
      'For questions about these Terms, please contact us at:\n\nEmail: legal@apsdreamhome.com\nPhone: +91 70074 44842\nAddress: APS Dream Home, Gorakhpur, Uttar Pradesh',
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
              Icons.gavel_rounded,
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
              'Terms & Conditions',
              style: Theme.of(context).textTheme.headlineLarge?.copyWith(
                color: Colors.white,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Last updated: July 2026\nPlease read carefully before using our services.',
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
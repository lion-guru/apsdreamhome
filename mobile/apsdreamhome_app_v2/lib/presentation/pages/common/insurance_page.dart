import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class InsurancePage extends StatelessWidget {
  const InsurancePage({super.key});

  static const _insurancePlans = [
    _InsurancePlan(
      'Property Shield',
      'Comprehensive coverage for your property against fire, theft, and natural disasters.',
      '₹5,999/yr',
      Icons.shield_rounded,
      Color(0xFF4CAF50),
      90,
    ),
    _InsurancePlan(
      'Construction Guard',
      'Coverage during construction phase including material theft and structural damage.',
      '₹3,499/yr',
      Icons.construction_rounded,
      Color(0xFFFF9800),
      75,
    ),
    _InsurancePlan(
      'Title Protect',
      'Legal title insurance protecting your ownership rights against disputes.',
      '₹7,999/yr',
      Icons.description_rounded,
      Color(0xFF2196F3),
      85,
    ),
    _InsurancePlan(
      'Earthquake Cover',
      'Specialized coverage for earthquake and seismic event damage.',
      '₹2,999/yr',
      Icons.landslide_rounded,
      Color(0xFF9C27B0),
      60,
    ),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: MeshGradientBackground(
        child: SafeArea(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildHeader(context),
                const SizedBox(height: 24),
                _buildSectionTitle('Insurance Plans'),
                const SizedBox(height: 16),
                ..._insurancePlans.map(
                  (p) => Padding(
                    padding: const EdgeInsets.only(bottom: 12),
                    child: _buildPlanCard(context, p),
                  ),
                ),
                const SizedBox(height: 24),
                _buildSectionTitle('Benefits'),
                const SizedBox(height: 12),
                ..._benefits.map(
                  (b) => Padding(
                    padding: const EdgeInsets.only(bottom: 8),
                    child: _buildBenefitRow(b),
                  ),
                ),
                const SizedBox(height: 24),
                _buildCTASection(context),
                const SizedBox(height: 40),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildHeader(BuildContext context) {
    return Column(
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
        const SizedBox(height: 20),
        Container(
          width: 80,
          height: 80,
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [Color(0xFF4CAF50), Color(0xFF66BB6A)],
            ),
            borderRadius: BorderRadius.circular(20),
            boxShadow: [
              BoxShadow(
                color: const Color(0xFF4CAF50).withValues(alpha: 0.3),
                blurRadius: 20,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          child: const Icon(
            Icons.health_and_safety_rounded,
            size: 40,
            color: Colors.white,
          ),
        ),
        const SizedBox(height: 16),
        ShaderMask(
          shaderCallback: (bounds) => const LinearGradient(
            colors: [AppTheme.primaryColor, Color(0xFF4CAF50)],
          ).createShader(bounds),
          child: Text(
            'Property Insurance',
            style: Theme.of(context).textTheme.headlineLarge?.copyWith(
              color: Colors.white,
              fontWeight: FontWeight.w800,
            ),
          ),
        ),
        const SizedBox(height: 8),
        Text(
          'Protect your property investment with comprehensive insurance plans',
          style: Theme.of(
            context,
          ).textTheme.bodyMedium?.copyWith(color: Colors.white70),
          textAlign: TextAlign.center,
        ),
      ],
    );
  }

  Widget _buildSectionTitle(String title) {
    return Text(
      title,
      style: AppTheme.titleLarge.copyWith(
        color: Colors.white,
        fontWeight: FontWeight.w700,
      ),
    );
  }

  Widget _buildPlanCard(BuildContext context, _InsurancePlan plan) {
    return GlassCard(
      padding: const EdgeInsets.all(16),
      opacity: 0.1,
      blur: 8,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: plan.color.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(plan.icon, color: plan.color, size: 24),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      plan.name,
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w600,
                        fontSize: 15,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      plan.price,
                      style: const TextStyle(
                        color: AppTheme.accentColor,
                        fontWeight: FontWeight.w700,
                        fontSize: 14,
                      ),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: plan.color.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  '${plan.coverage}%',
                  style: TextStyle(
                    color: plan.color,
                    fontWeight: FontWeight.w700,
                    fontSize: 12,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            plan.description,
            style: TextStyle(
              color: Colors.white.withValues(alpha: 0.6),
              fontSize: 13,
            ),
          ),
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            height: 38,
            child: DecoratedBox(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(10),
                gradient: LinearGradient(
                  colors: [plan.color, plan.color.withValues(alpha: 0.7)],
                ),
              ),
              child: ElevatedButton(
                onPressed: () {
                  showDialog(
                    context: context,
                    builder: (_) => AlertDialog(
                      backgroundColor: const Color(0xFF1A237E),
                      title: Text(
                        plan.name,
                        style: const TextStyle(color: Colors.white),
                      ),
                      content: Text(
                        plan.description,
                        style: const TextStyle(color: Colors.white70),
                      ),
                      actions: [
                        TextButton(
                          onPressed: () => Navigator.pop(context),
                          child: const Text(
                            'Close',
                            style: TextStyle(color: Colors.white54),
                          ),
                        ),
                      ],
                    ),
                  );
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.transparent,
                  shadowColor: Colors.transparent,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
                child: const Text(
                  'View Details',
                  style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w600,
                    fontSize: 13,
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBenefitRow(_BenefitData benefit) {
    return GlassCard(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      opacity: 0.06,
      blur: 6,
      child: Row(
        children: [
          Icon(benefit.icon, color: const Color(0xFF4CAF50), size: 20),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              benefit.text,
              style: TextStyle(
                color: Colors.white.withValues(alpha: 0.8),
                fontSize: 13,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCTASection(BuildContext context) {
    return GlassCard(
      padding: const EdgeInsets.all(24),
      opacity: 0.15,
      blur: 10,
      child: Column(
        children: [
          const Icon(
            Icons.support_agent_rounded,
            color: AppTheme.accentColor,
            size: 40,
          ),
          const SizedBox(height: 12),
          const Text(
            'Need Help Choosing?',
            style: TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.w700,
              fontSize: 18,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Our insurance advisors can help you pick the right plan.',
            style: TextStyle(
              color: Colors.white.withValues(alpha: 0.6),
              fontSize: 13,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 16),
          SizedBox(
            width: double.infinity,
            height: 48,
            child: DecoratedBox(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(14),
                gradient: const LinearGradient(
                  colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
                ),
                boxShadow: [
                  BoxShadow(
                    color: AppTheme.primaryColor.withValues(alpha: 0.4),
                    blurRadius: 12,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: ElevatedButton(
                onPressed: () async {
                  final phoneUri = Uri.parse(
                    'tel:${AppConstants.supportPhone}',
                  );
                  if (await canLaunchUrl(phoneUri)) {
                    await launchUrl(
                      phoneUri,
                      mode: LaunchMode.externalApplication,
                    );
                  }
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.transparent,
                  shadowColor: Colors.transparent,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14),
                  ),
                ),
                child: const Text(
                  'Contact Advisor',
                  style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w600,
                    fontSize: 15,
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _InsurancePlan {
  final String name;
  final String description;
  final String price;
  final IconData icon;
  final Color color;
  final int coverage;
  const _InsurancePlan(
    this.name,
    this.description,
    this.price,
    this.icon,
    this.color,
    this.coverage,
  );
}

final _benefits = [
  const _BenefitData(
    Icons.check_circle_rounded,
    'Coverage up to ₹50L for structural damage',
  ),
  const _BenefitData(
    Icons.check_circle_rounded,
    '24/7 claim support and quick settlement',
  ),
  const _BenefitData(
    Icons.check_circle_rounded,
    'Zero depreciation cover on select plans',
  ),
  const _BenefitData(Icons.check_circle_rounded, 'Transferable if property is sold'),
  const _BenefitData(
    Icons.check_circle_rounded,
    'Add-on covers for natural disasters',
  ),
];

class _BenefitData {
  final IconData icon;
  final String text;
  const _BenefitData(this.icon, this.text);
}

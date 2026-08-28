import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class HowItWorksPage extends StatelessWidget {
  const HowItWorksPage({super.key});

  static const _steps = [
    _StepData(
      Icons.search_rounded,
      'Browse Properties',
      'Explore our wide range of plots, houses, and commercial properties with advanced filters. Use the map view to find properties near your preferred locations.',
    ),
    _StepData(
      Icons.favorite_border_rounded,
      'Shortlist & Compare',
      'Save your favorite properties and compare them side by side. Set up alerts for new listings that match your criteria.',
    ),
    _StepData(
      Icons.calendar_today_rounded,
      'Schedule Site Visit',
      'Book a site visit directly from the app at a time that suits you. Get reminders and directions to the property location.',
    ),
    _StepData(
      Icons.handshake_rounded,
      'Book & Pay',
      'Complete your booking online with secure payment options. Track your EMI schedule and download receipts instantly.',
    ),
    _StepData(
      Icons.description_rounded,
      'Documentation',
      'Upload and manage all your property documents in the document locker. Track KYC status and download agreements.',
    ),
    _StepData(
      Icons.key_rounded,
      'Possession',
      'Get possession of your dream property with complete support from our team. Rate and review your experience.',
    ),
  ];

  static const _tools = [
    _ToolData(Icons.calculate_rounded, 'EMI Calculator', 'Plan your finances'),
    _ToolData(
      Icons.monetization_on_rounded,
      'Stamp Duty Calculator',
      'Estimate costs',
    ),
    _ToolData(
      Icons.assessment_rounded,
      'Property Valuation',
      'Know your property worth',
    ),
    _ToolData(
      Icons.square_foot_rounded,
      'Area Converter',
      'Convert units easily',
    ),
  ];

  static const _features = [
    _FeatureData(
      Icons.people_rounded,
      'For Buyers',
      'Browse, compare, and book properties with complete transparency. Get expert advice and personalized recommendations.',
    ),
    _FeatureData(
      Icons.trending_up_rounded,
      'For Associates',
      'Earn commissions by referring buyers. Track your network, team performance, and payouts in real-time.',
    ),
    _FeatureData(
      Icons.admin_panel_settings_rounded,
      'For Agents',
      'Manage leads, deals, and commissions. Access CRM tools, track performance, and grow your business.',
    ),
    _FeatureData(
      Icons.business_rounded,
      'For Employees',
      'Attendance tracking, task management, CRM access, and performance dashboards for daily operations.',
    ),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: MeshGradientBackground(
        child: SafeArea(
          child: SingleChildScrollView(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildHeader(context),
                  const SizedBox(height: 24),
                  _buildSectionTitle('Your Journey in 6 Steps'),
                  const SizedBox(height: 16),
                  ...List.generate(
                    _steps.length,
                    (i) => Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: _buildStepCard(_steps[i], i + 1),
                    ),
                  ),
                  const SizedBox(height: 28),
                  _buildSectionTitle('Useful Tools'),
                  const SizedBox(height: 16),
                  _buildToolsGrid(),
                  const SizedBox(height: 28),
                  _buildSectionTitle('Who Can Use This App'),
                  const SizedBox(height: 16),
                  ..._features.map(
                    (f) => Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: _buildFeatureCard(f),
                    ),
                  ),
                  const SizedBox(height: 28),
                  _buildCTASection(context),
                  const SizedBox(height: 40),
                ],
              ),
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
            Icons.explore_rounded,
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
            'How It Works',
            style: Theme.of(context).textTheme.headlineLarge?.copyWith(
              color: Colors.white,
              fontWeight: FontWeight.w800,
            ),
          ),
        ),
        const SizedBox(height: 8),
        Text(
          'Your complete guide to finding and owning your dream property',
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

  Widget _buildStepCard(_StepData step, int number) {
    return GlassCard(
      padding: const EdgeInsets.all(16),
      opacity: 0.1,
      blur: 8,
      child: Row(
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
              ),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Center(
              child: Text(
                '$number',
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w800,
                  fontSize: 18,
                ),
              ),
            ),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  step.title,
                  style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w600,
                    fontSize: 15,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  step.description,
                  style: TextStyle(
                    color: Colors.white.withValues(alpha: 0.6),
                    fontSize: 13,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildToolsGrid() {
    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        childAspectRatio: 1.4,
        crossAxisSpacing: 12,
        mainAxisSpacing: 12,
      ),
      itemCount: _tools.length,
      itemBuilder: (context, index) {
        final tool = _tools[index];
        return GlassCard(
          padding: const EdgeInsets.all(14),
          opacity: 0.08,
          blur: 6,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(tool.icon, color: AppTheme.accentColor, size: 28),
              const SizedBox(height: 8),
              Text(
                tool.title,
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w600,
                  fontSize: 13,
                ),
                textAlign: TextAlign.center,
              ),
              Text(
                tool.subtitle,
                style: TextStyle(
                  color: Colors.white.withValues(alpha: 0.5),
                  fontSize: 11,
                ),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildFeatureCard(_FeatureData feature) {
    return GlassCard(
      padding: const EdgeInsets.all(16),
      opacity: 0.1,
      blur: 8,
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: AppTheme.accentColor.withValues(alpha: 0.15),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(feature.icon, color: AppTheme.accentColor, size: 22),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  feature.title,
                  style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w600,
                    fontSize: 14,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  feature.description,
                  style: TextStyle(
                    color: Colors.white.withValues(alpha: 0.6),
                    fontSize: 12,
                  ),
                ),
              ],
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
            Icons.rocket_launch_rounded,
            color: AppTheme.accentColor,
            size: 40,
          ),
          const SizedBox(height: 12),
          const Text(
            'Ready to Find Your Dream Home?',
            style: TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.w700,
              fontSize: 18,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Start exploring properties and begin your journey today.',
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
                onPressed: () => context.go('/home'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.transparent,
                  shadowColor: Colors.transparent,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14),
                  ),
                ),
                child: const Text(
                  'Browse Properties',
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

class _StepData {
  final IconData icon;
  final String title;
  final String description;
  const _StepData(this.icon, this.title, this.description);
}

class _ToolData {
  final IconData icon;
  final String title;
  final String subtitle;
  const _ToolData(this.icon, this.title, this.subtitle);
}

class _FeatureData {
  final IconData icon;
  final String title;
  final String description;
  const _FeatureData(this.icon, this.title, this.description);
}

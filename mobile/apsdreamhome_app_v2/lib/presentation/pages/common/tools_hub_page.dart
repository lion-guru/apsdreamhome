import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class ToolsHubPage extends StatelessWidget {
  const ToolsHubPage({super.key});

  static const _tools = [
    _ToolCategory(
      'Financial Calculators',
      Icons.calculate_rounded,
      Color(0xFF4CAF50),
      [
        _ToolItem(
          'EMI Calculator',
          'Calculate monthly loan payments',
          Icons.monetization_on_rounded,
          '/emi-calculator',
        ),
        _ToolItem(
          'Home Loan Eligibility',
          'Check how much you can borrow',
          Icons.account_balance_rounded,
          '/home-loan-eligibility',
        ),
        _ToolItem(
          'Stamp Duty Calculator',
          'Estimate registration costs',
          Icons.receipt_long_rounded,
          '/stamp-duty-calculator',
        ),
        _ToolItem(
          'Property Tax Calculator',
          'Calculate annual property tax',
          Icons.receipt_long_rounded,
          '/property-tax-calculator',
        ),
        _ToolItem(
          'Rental Yield Calculator',
          'ROI on rental property',
          Icons.trending_up_rounded,
          '/rental-yield-calculator',
        ),
        _ToolItem(
          'Capital Gains Calculator',
          'Tax on property sale profit',
          Icons.savings_rounded,
          '/capital-gains-calculator',
        ),
        _ToolItem(
          'Property Appreciation',
          'Future value with appreciation + rent',
          Icons.trending_up_rounded,
          '/property-appreciation-calculator',
        ),
        _ToolItem(
          'GST Calculator',
          'GST on property transactions',
          Icons.percent_rounded,
          '/gst-calculator',
        ),
        _ToolItem(
          'SIP vs Real Estate',
          'Compare investment returns',
          Icons.compare_arrows_rounded,
          '/sip-vs-realestate',
        ),
      ],
    ),
    _ToolCategory(
      'Property Tools',
      Icons.home_work_rounded,
      Color(0xFF2196F3),
      [
        _ToolItem(
          'Property Valuation',
          'AI-powered price estimation',
          Icons.assessment_rounded,
          '/property-valuation',
        ),
        _ToolItem(
          'Plot Converter',
          'Convert area units instantly',
          Icons.straighten_rounded,
          '/plot-converter',
        ),
        _ToolItem(
          'Construction Cost Estimator',
          'Estimate building costs',
          Icons.construction_rounded,
          '/construction-cost-estimator',
        ),
        _ToolItem(
          'Rent vs Buy Calculator',
          'Which is better for you?',
          Icons.home_rounded,
          '/rent-vs-buy',
        ),
        _ToolItem(
          'RERA Lookup',
          'Verify project registration',
          Icons.verified_rounded,
          '/rera-lookup',
        ),
        _ToolItem(
          'Neighborhood Analysis',
          'Schools, hospitals & more',
          Icons.explore_rounded,
          '/neighborhood',
        ),
        _ToolItem(
          'Virtual Tour',
          '360° property walkthrough',
          Icons.videocam_rounded,
          '/virtual-tour',
        ),
      ],
    ),
    _ToolCategory(
      'Documentation',
      Icons.description_rounded,
      Color(0xFFFF9800),
      [
        _ToolItem(
          'Document Gallery',
          'Sample agreements & forms',
          Icons.folder_open_rounded,
          '/documents',
        ),
        _ToolItem(
          'Agreement Generator',
          'Create legal documents',
          Icons.auto_stories_rounded,
          '/agreements',
        ),
        _ToolItem(
          'Document E-Sign',
          'Sign documents digitally',
          Icons.draw_rounded,
          '/document-esign',
        ),
        _ToolItem(
          'NACH Mandate Setup',
          'Auto EMI payments',
          Icons.receipt_long_rounded,
          '/nach-mandate',
        ),
        _ToolItem(
          'Terms & Conditions',
          'Read our terms of use',
          Icons.gavel_rounded,
          '/terms',
        ),
        _ToolItem(
          'Legal Services',
          'Property legal assistance',
          Icons.balance_rounded,
          '/legal/services',
        ),
        _ToolItem(
          'Disclaimer',
          'Read our disclaimer',
          Icons.warning_amber_rounded,
          '/disclaimer',
        ),
        _ToolItem(
          'Cancellation Policy',
          'Booking cancellation rules',
          Icons.cancel_rounded,
          '/cancellation-policy',
        ),
      ],
    ),
    _ToolCategory(
      'Insurance & Protection',
      Icons.health_and_safety_rounded,
      Color(0xFF9C27B0),
      [
        _ToolItem(
          'Property Insurance',
          'Compare & buy insurance',
          Icons.shield_rounded,
          '/insurance',
        ),
        _ToolItem(
          'Title Protection',
          'Legal title insurance',
          Icons.security_rounded,
          '/title-protection',
        ),
      ],
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
                _buildSearchBar(),
                const SizedBox(height: 24),
                ..._tools.map(
                  (cat) => Padding(
                    padding: const EdgeInsets.only(bottom: 24),
                    child: _buildCategorySection(cat),
                  ),
                ),
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
            Icons.build_circle_rounded,
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
            'Tools Hub',
            style: Theme.of(context).textTheme.headlineLarge?.copyWith(
              color: Colors.white,
              fontWeight: FontWeight.w800,
            ),
          ),
        ),
        const SizedBox(height: 8),
        Text(
          'All calculators, converters & utilities in one place',
          style: Theme.of(
            context,
          ).textTheme.bodyMedium?.copyWith(color: Colors.white70),
          textAlign: TextAlign.center,
        ),
      ],
    );
  }

  Widget _buildSearchBar() {
    return GlassCard(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      opacity: 0.12,
      blur: 10,
      child: Row(
        children: [
          Icon(
            Icons.search_rounded,
            color: Colors.white.withValues(alpha: 0.7),
            size: 22,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              'Search tools... (e.g. "EMI", "Stamp Duty", "Area")',
              style: TextStyle(
                color: Colors.white.withValues(alpha: 0.6),
                fontSize: 14,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCategorySection(_ToolCategory category) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Container(
              width: 36,
              height: 36,
              decoration: BoxDecoration(
                color: category.color.withValues(alpha: 0.2),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(category.icon, color: category.color, size: 20),
            ),
            const SizedBox(width: 10),
            Text(
              category.name,
              style: AppTheme.titleLarge.copyWith(
                color: Colors.white,
                fontWeight: FontWeight.w700,
              ),
            ),
            const Spacer(),
            Text(
              '${category.tools.length} tools',
              style: TextStyle(
                color: Colors.white.withValues(alpha: 0.5),
                fontSize: 12,
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        GridView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 2,
            childAspectRatio: 1.5,
            crossAxisSpacing: 10,
            mainAxisSpacing: 10,
          ),
          itemCount: category.tools.length,
          itemBuilder: (context, index) {
            final tool = category.tools[index];
            return GestureDetector(
              onTap: () => context.push(tool.route),
              child: GlassCard(
                padding: const EdgeInsets.all(14),
                opacity: 0.08,
                blur: 6,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Icon(tool.icon, color: category.color, size: 24),
                    const SizedBox(height: 8),
                    Text(
                      tool.title,
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w600,
                        fontSize: 13,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 2),
                    Text(
                      tool.description,
                      style: TextStyle(
                        color: Colors.white.withValues(alpha: 0.5),
                        fontSize: 11,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const Spacer(),
                    Row(
                      children: [
                        const Spacer(),
                        Icon(
                          Icons.arrow_forward_ios_rounded,
                          color: Colors.white.withValues(alpha: 0.4),
                          size: 14,
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            );
          },
        ),
      ],
    );
  }
}

class _ToolCategory {
  final String name;
  final IconData icon;
  final Color color;
  final List<_ToolItem> tools;
  const _ToolCategory(this.name, this.icon, this.color, this.tools);
}

class _ToolItem {
  final String title;
  final String description;
  final IconData icon;
  final String route;
  const _ToolItem(this.title, this.description, this.icon, this.route);
}

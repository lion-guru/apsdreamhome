import 'package:flutter/material.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class PropertyVerificationPage extends StatelessWidget {
  const PropertyVerificationPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Property Verification'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildHeader(),
            const SizedBox(height: 20),
            _buildHowItWorks(),
            const SizedBox(height: 24),
            _buildVerificationLevels(),
            const SizedBox(height: 24),
            _buildBenefits(),
            const SizedBox(height: 24),
            _buildFAQ(),
            const SizedBox(height: 30),
          ],
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF1B5E20), Color(0xFF43A047)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(Icons.verified_rounded, size: 40, color: Colors.white),
          const SizedBox(height: 12),
          const Text(
            'Property Verification',
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Trust verified properties with complete legal clarity',
            style: TextStyle(
              fontSize: 13,
              color: Colors.white.withValues(alpha: 0.85),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHowItWorks() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'How It Works',
          style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 12),
        _buildStepCard(
          '1',
          'Owner Submits Documents',
          'Title deed, tax receipts, encumbrance certificate, approved plan',
          Icons.upload_file_rounded,
          const Color(0xFF1565C0),
        ),
        const SizedBox(height: 8),
        _buildStepCard(
          '2',
          'Legal Team Verifies',
          'Our experts verify all documents against government records',
          Icons.verified_user_rounded,
          const Color(0xFF2E7D32),
        ),
        const SizedBox(height: 8),
        _buildStepCard(
          '3',
          'On-Site Inspection',
          'Physical verification of property condition, boundaries, construction',
          Icons.location_on_rounded,
          const Color(0xFFE65100),
        ),
        const SizedBox(height: 8),
        _buildStepCard(
          '4',
          'Badge Awarded',
          'Property gets a verified badge visible on all listings',
          Icons.verified_rounded,
          const Color(0xFF6A1B9A),
        ),
      ],
    );
  }

  Widget _buildStepCard(
    String step,
    String title,
    String description,
    IconData icon,
    Color color,
  ) {
    return GlassCard(
      padding: const EdgeInsets.all(14),
      child: Row(
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [color, color.withValues(alpha: 0.7)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Center(
              child: Text(
                step,
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                ),
              ),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  description,
                  style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                ),
              ],
            ),
          ),
          Icon(icon, size: 24, color: color),
        ],
      ),
    );
  }

  Widget _buildVerificationLevels() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Verification Levels',
          style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 12),
        _buildLevelCard(
          'Basic Verified',
          'Document verification & ownership check',
          [
            'Title deed verified',
            'Owner identity confirmed',
            'No pending disputes',
          ],
          const Color(0xFF4CAF50),
          Icons.check_circle_rounded,
          'Free',
        ),
        const SizedBox(height: 10),
        _buildLevelCard(
          'Premium Verified',
          'Full legal + physical verification',
          [
            'All Basic features',
            'On-site inspection',
            'Measurement verification',
            'Encumbrance check',
          ],
          const Color(0xFF1565C0),
          Icons.shield_rounded,
          '₹999',
        ),
        const SizedBox(height: 10),
        _buildLevelCard(
          'Gold Verified',
          'Comprehensive due diligence + report',
          [
            'All Premium features',
            'Title search report',
            'Approved plan check',
            'Tax compliance audit',
            'Neighborhood analysis',
          ],
          const Color(0xFFF9A825),
          Icons.verified_rounded,
          '₹2,499',
        ),
        const SizedBox(height: 10),
        _buildLevelCard(
          'Platinum Verified',
          'Complete assurance + legal cover',
          [
            'All Gold features',
            'Legal indemnity cover',
            'RERA compliance check',
            'Structural engineer report',
            'Title insurance worth ₹10L',
          ],
          const Color(0xFF6A1B9A),
          Icons.diamond_rounded,
          '₹4,999',
        ),
      ],
    );
  }

  Widget _buildLevelCard(
    String title,
    String subtitle,
    List<String> features,
    Color color,
    IconData icon,
    String price,
  ) {
    return GlassCard(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(icon, color: color, size: 24),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: const TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    Text(
                      subtitle,
                      style: TextStyle(
                        fontSize: 11,
                        color: Colors.grey.shade600,
                      ),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 12,
                  vertical: 6,
                ),
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Text(
                  price,
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.bold,
                    color: color,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          const Divider(height: 1),
          const SizedBox(height: 10),
          ...features.map(
            (f) => Padding(
              padding: const EdgeInsets.only(bottom: 4),
              child: Row(
                children: [
                  Icon(Icons.check_rounded, size: 16, color: color),
                  const SizedBox(width: 8),
                  Text(
                    f,
                    style: TextStyle(fontSize: 12, color: Colors.grey.shade700),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBenefits() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Benefits',
          style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: _buildBenefitCard(
                Icons.verified_user_rounded,
                'Zero Fraud Risk',
                'Every document verified',
                const Color(0xFF2E7D32),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _buildBenefitCard(
                Icons.timer_rounded,
                'Fast Processing',
                '48hr turnaround',
                const Color(0xFF1565C0),
              ),
            ),
          ],
        ),
        const SizedBox(height: 10),
        Row(
          children: [
            Expanded(
              child: _buildBenefitCard(
                Icons.gavel_rounded,
                'Legal Protection',
                'Indemnity cover available',
                const Color(0xFF6A1B9A),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _buildBenefitCard(
                Icons.trending_up_rounded,
                'Higher Resale',
                'Verified = better price',
                const Color(0xFFE65100),
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildBenefitCard(
    IconData icon,
    String title,
    String subtitle,
    Color color,
  ) {
    return GlassCard(
      padding: const EdgeInsets.all(14),
      child: Column(
        children: [
          Icon(icon, size: 32, color: color),
          const SizedBox(height: 8),
          Text(
            title,
            style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 2),
          Text(
            subtitle,
            style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildFAQ() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Frequently Asked Questions',
          style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 12),
        _buildFaqItem(
          'What documents are needed?',
          'Title deed, sale deed, tax receipts, encumbrance certificate, approved building plan, and identity proof.',
        ),
        const SizedBox(height: 8),
        _buildFaqItem(
          'How long does verification take?',
          'Basic verification is completed within 24 hours. Premium and Gold take 2-3 working days. Platinum takes up to 5 working days.',
        ),
        const SizedBox(height: 8),
        _buildFaqItem(
          'Is verification mandatory?',
          'No, but verified properties get priority listing, a verified badge, and higher buyer trust, leading to faster sales at better prices.',
        ),
        const SizedBox(height: 8),
        _buildFaqItem(
          'What if verification fails?',
          'We provide a detailed report of issues found. You can address them and re-apply. If the property has serious legal issues, we recommend consulting a lawyer.',
        ),
      ],
    );
  }

  Widget _buildFaqItem(String question, String answer) {
    return GlassCard(
      padding: const EdgeInsets.all(14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(
                Icons.help_outline_rounded,
                size: 18,
                color: AppTheme.primaryColor,
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  question,
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            answer,
            style: TextStyle(
              fontSize: 12,
              color: Colors.grey.shade600,
              height: 1.4,
            ),
          ),
        ],
      ),
    );
  }
}

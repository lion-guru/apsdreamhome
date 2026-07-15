import 'package:flutter/material.dart';
import 'package:share_plus/share_plus.dart';
import '../../../core/theme/app_theme.dart';

class TitleProtectionPage extends StatelessWidget {
  const TitleProtectionPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Title Protection'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildHeader(),
            const SizedBox(height: 24),
            _buildDescription(),
            const SizedBox(height: 20),
            _buildCoverageSection(),
            const SizedBox(height: 20),
            _buildPlans(),
            const SizedBox(height: 20),
            _buildFAQ(),
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
          colors: [Color(0xFFE65100), Color(0xFFFF8F00)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(Icons.security_rounded, size: 40, color: Colors.white),
          const SizedBox(height: 12),
          const Text(
            'Title Protection',
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Protect your property from legal disputes and title defects',
            style: TextStyle(
              fontSize: 13,
              color: Colors.white.withValues(alpha: 0.85),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDescription() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                Icons.info_outline_rounded,
                color: Colors.orange.shade700,
                size: 20,
              ),
              const SizedBox(width: 8),
              const Text(
                'What is Title Protection?',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Text(
            'Title Protection insurance safeguards property buyers and owners against financial loss '
            'due to defects in the property title. It covers legal costs to defend against title claims '
            'and compensates for loss of property value if the title is found invalid.',
            style: TextStyle(
              color: Colors.grey.shade700,
              fontSize: 14,
              height: 1.6,
            ),
          ),
          const SizedBox(height: 16),
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: Colors.orange.shade50,
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: Colors.orange.shade200),
            ),
            child: Row(
              children: [
                Icon(
                  Icons.lightbulb_rounded,
                  color: Colors.orange.shade700,
                  size: 22,
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    'All APS Dream Home properties come with clear title guarantee. '
                    'Optional title protection insurance offers additional peace of mind.',
                    style: TextStyle(
                      color: Colors.orange.shade800,
                      fontSize: 12,
                      height: 1.4,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCoverageSection() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'What\'s Covered',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
          ),
          const SizedBox(height: 16),
          _coverageItem(
            Icons.check_circle_rounded,
            'Forgery or fraud in previous transactions',
            Colors.green,
          ),
          _coverageItem(
            Icons.check_circle_rounded,
            'Undisclosed heirs claiming ownership',
            Colors.green,
          ),
          _coverageItem(
            Icons.check_circle_rounded,
            'Errors in public records or surveys',
            Colors.green,
          ),
          _coverageItem(
            Icons.check_circle_rounded,
            'Improperly recorded documents',
            Colors.green,
          ),
          _coverageItem(
            Icons.check_circle_rounded,
            'Missing signatures on earlier deeds',
            Colors.green,
          ),
          _coverageItem(
            Icons.check_circle_rounded,
            'Legal defense costs for covered claims',
            Colors.green,
          ),
          const SizedBox(height: 16),
          const Divider(),
          const SizedBox(height: 12),
          const Text(
            'Not Covered',
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w600,
              color: Colors.redAccent,
            ),
          ),
          const SizedBox(height: 10),
          _coverageItem(
            Icons.cancel_rounded,
            'Title defects you created or knew about',
            Colors.red,
          ),
          _coverageItem(
            Icons.cancel_rounded,
            'Environmental protection laws',
            Colors.red,
          ),
          _coverageItem(
            Icons.cancel_rounded,
            'Eminent domain actions before policy date',
            Colors.red,
          ),
          _coverageItem(
            Icons.cancel_rounded,
            'Zoning or building code violations',
            Colors.red,
          ),
        ],
      ),
    );
  }

  Widget _coverageItem(IconData icon, String text, Color color) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: color, size: 20),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              text,
              style: TextStyle(color: Colors.grey.shade700, fontSize: 13),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPlans() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Protection Plans',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
          ),
          const SizedBox(height: 16),
          _planCard('Basic', '₹5,000', 'Coverage up to ₹25 Lakhs', [
            'Coverage for title defects',
            'Legal defense up to ₹5 Lakhs',
            'Valid for 10 years',
          ], Colors.blue),
          const SizedBox(height: 12),
          _planCard('Standard', '₹10,000', 'Coverage up to ₹50 Lakhs', [
            'Everything in Basic',
            'Legal defense up to ₹10 Lakhs',
            'Valid for 15 years',
            'Document verification included',
          ], Colors.orange),
          const SizedBox(height: 12),
          _planCard('Premium', '₹20,000', 'Coverage up to ₹1 Crore', [
            'Everything in Standard',
            'Legal defense up to ₹25 Lakhs',
            'Valid for 20 years',
            'Document verification & storage',
            'Priority claim processing',
          ], Colors.green),
        ],
      ),
    );
  }

  Widget _planCard(
    String title,
    String price,
    String subtitle,
    List<String> features,
    Color color,
  ) {
    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        border: Border.all(color: color.withValues(alpha: 0.3)),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        children: [
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.08),
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(11),
              ),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: TextStyle(
                        fontWeight: FontWeight.w700,
                        fontSize: 16,
                        color: color,
                      ),
                    ),
                    Text(
                      subtitle,
                      style: TextStyle(
                        color: Colors.grey.shade600,
                        fontSize: 11,
                      ),
                    ),
                  ],
                ),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(
                      price,
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.w800,
                        color: color,
                      ),
                    ),
                    const Text(
                      'one-time',
                      style: TextStyle(fontSize: 11, color: Colors.grey),
                    ),
                  ],
                ),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(14),
            child: Column(
              children: features
                  .map(
                    (f) => Padding(
                      padding: const EdgeInsets.only(bottom: 6),
                      child: Row(
                        children: [
                          Icon(Icons.check_rounded, color: color, size: 18),
                          const SizedBox(width: 8),
                          Text(
                            f,
                            style: TextStyle(
                              color: Colors.grey.shade700,
                              fontSize: 13,
                            ),
                          ),
                        ],
                      ),
                    ),
                  )
                  .toList(),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFAQ() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Frequently Asked Questions',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
          ),
          const SizedBox(height: 16),
          _faqItem(
            'Is title protection mandatory?',
            'No, but it is highly recommended. All APS Dream Home properties have clear verified titles.',
          ),
          const SizedBox(height: 12),
          _faqItem(
            'How long does coverage last?',
            'Plans cover you for 10-20 years depending on the plan selected. Coverage lasts for the entire policy term.',
          ),
          const SizedBox(height: 12),
          _faqItem(
            'Can I transfer the policy?',
            'Yes, title protection is transferable. If you sell the property, the new owner can take over the remaining coverage.',
          ),
          const SizedBox(height: 12),
          _faqItem(
            'When should I buy?',
            'Ideally at the time of property purchase. Premium is a one-time payment at closing.',
          ),
        ],
      ),
    );
  }

  Widget _faqItem(String question, String answer) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          question,
          style: const TextStyle(
            fontWeight: FontWeight.w600,
            fontSize: 13,
            color: Colors.black87,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          answer,
          style: TextStyle(
            color: Colors.grey.shade600,
            fontSize: 12,
            height: 1.4,
          ),
        ),
      ],
    );
  }
}

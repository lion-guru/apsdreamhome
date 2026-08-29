import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class LegalServicesPage extends StatelessWidget {
  const LegalServicesPage({super.key});

  static const _services = [
    _ServiceItem(
      'Property Due Diligence',
      'Comprehensive legal verification of property titles, ownership history, encumbrances, and regulatory compliance before purchase.',
      Icons.search_rounded,
    ),
    _ServiceItem(
      'Sale Deed Drafting & Registration',
      'End-to-end drafting, review, and registration of sale deeds, conveyance deeds, and transfer documents with stamp duty calculation.',
      Icons.description_rounded,
    ),
    _ServiceItem(
      'RERA Compliance & Filing',
      'Registration of projects under RERA, quarterly compliance filings, and dispute resolution representation before RERA authorities.',
      Icons.gavel_rounded,
    ),
    _ServiceItem(
      'Property Mutation & Transfer',
      'Legal assistance for mutation of property records, transfer of ownership in revenue records, and updating municipal records.',
      Icons.swap_horiz_rounded,
    ),
    _ServiceItem(
      'Lease & Rental Agreements',
      'Drafting and registration of residential and commercial lease agreements, leave & license agreements, and rent review clauses.',
      Icons.home_work_rounded,
    ),
    _ServiceItem(
      'Home Loan Documentation',
      'Legal verification for home loans, mortgage deed preparation, and coordination with banks for smooth loan disbursement.',
      Icons.account_balance_rounded,
    ),
    _ServiceItem(
      'Property Dispute Resolution',
      'Legal representation in property disputes including title disputes, boundary disputes, eviction proceedings, and partition suits.',
      Icons.balance_rounded,
    ),
    _ServiceItem(
      'Builder-Buyer Agreements',
      'Review and negotiation of builder-buyer agreements, allotment letters, and possession certificates with buyer protection clauses.',
      Icons.handshake_rounded,
    ),
    _ServiceItem(
      'E-Stamping & E-Registration',
      'Digital stamping and online registration services through government portals for faster, paperless property transactions.',
      Icons.verified_rounded,
    ),
    _ServiceItem(
      'Legal Notices & Documentation',
      'Drafting and serving legal notices for possession, eviction, breach of contract, and other property-related matters.',
      Icons.mail_rounded,
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
                sliver: SliverGrid(
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 2,
                    crossAxisSpacing: 16,
                    mainAxisSpacing: 16,
                    childAspectRatio: 0.85,
                  ),
                  delegate: SliverChildBuilderDelegate(
                    (context, index) => _buildServiceCard(context, _services[index]),
                    childCount: _services.length,
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
              Icons.balance_rounded,
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
              'Legal Services',
              style: Theme.of(context).textTheme.headlineLarge?.copyWith(
                color: Colors.white,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Expert legal assistance for all your property needs',
            style: Theme.of(
              context,
            ).textTheme.bodyMedium?.copyWith(color: Colors.white70),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildServiceCard(BuildContext context, _ServiceItem service) {
    return GestureDetector(
      onTap: () {
        _showServiceDetail(context, service);
      },
      child: GlassCard(
        padding: const EdgeInsets.all(20),
        opacity: 0.1,
        blur: 10,
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 60,
              height: 60,
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [
                    AppTheme.primaryColor.withValues(alpha: 0.3),
                    AppTheme.secondaryColor.withValues(alpha: 0.3),
                  ],
                ),
                borderRadius: BorderRadius.circular(16),
              ),
              child: Icon(
                service.icon,
                size: 28,
                color: AppTheme.accentColor,
              ),
            ),
            const SizedBox(height: 16),
            Text(
              service.title,
              style: AppTheme.titleMedium.copyWith(
                color: Colors.white,
                fontWeight: FontWeight.w700,
                fontSize: 14,
              ),
              textAlign: TextAlign.center,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
            const SizedBox(height: 8),
            Text(
              service.description,
              style: TextStyle(
                color: Colors.white.withValues(alpha: 0.7),
                fontSize: 11,
                height: 1.4,
              ),
              textAlign: TextAlign.center,
              maxLines: 3,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }

  void _showServiceDetail(BuildContext context, _ServiceItem service) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => DraggableScrollableSheet(
        initialChildSize: 0.6,
        maxChildSize: 0.9,
        minChildSize: 0.4,
        builder: (context, scrollController) => Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topCenter,
              end: Alignment.bottomCenter,
              colors: [
                Color(0xFF0F172A),
                Color(0xFF1E293B),
              ],
            ),
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          ),
          child: SingleChildScrollView(
            controller: scrollController,
            padding: const EdgeInsets.all(24),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(
                  child: Container(
                    width: 40,
                    height: 4,
                    margin: const EdgeInsets.only(bottom: 20),
                    decoration: BoxDecoration(
                      color: Colors.white24,
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                ),
                Container(
                  width: 70,
                  height: 70,
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [
                        AppTheme.primaryColor.withValues(alpha: 0.3),
                        AppTheme.secondaryColor.withValues(alpha: 0.3),
                      ],
                    ),
                    borderRadius: BorderRadius.circular(18),
                  ),
                  child: Icon(
                    service.icon,
                    size: 32,
                    color: AppTheme.accentColor,
                  ),
                ),
                const SizedBox(height: 20),
                Text(
                  service.title,
                  style: AppTheme.headlineMedium.copyWith(
                    color: Colors.white,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 16),
                Text(
                  service.description,
                  style: TextStyle(
                    color: Colors.white.withValues(alpha: 0.85),
                    fontSize: 15,
                    height: 1.6,
                  ),
                ),
                const SizedBox(height: 24),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () => context.pop(),
                        icon: const Icon(Icons.close_rounded),
                        label: const Text('Close'),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: Colors.white70,
                          side: const BorderSide(color: Colors.white24),
                          padding: const EdgeInsets.symmetric(vertical: 14),
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: () {
                          context.pop();
                          context.go('/contact');
                        },
                        icon: const Icon(Icons.contact_mail_rounded),
                        label: const Text('Contact Us'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppTheme.primaryColor,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 20),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _ServiceItem {
  final String title;
  final String description;
  final IconData icon;
  const _ServiceItem(this.title, this.description, this.icon);
}
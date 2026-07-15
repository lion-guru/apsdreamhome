import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class BuyPage extends StatelessWidget {
  const BuyPage({super.key});

  static const _steps = [
    _StepData(
      'Search',
      Icons.search,
      'Browse properties by location, budget, size, or type',
    ),
    _StepData(
      'Visit',
      Icons.visibility,
      'Schedule site visits to shortlisted properties',
    ),
    _StepData(
      'Select',
      Icons.favorite_border,
      'Choose your dream property and book online',
    ),
    _StepData(
      'Own',
      Icons.key,
      'Complete registration and get your property papers',
    ),
  ];

  static const _features = [
    _FeatureData(
      'Verified Properties',
      'All properties verified for clear title and approvals',
      Icons.verified,
      Colors.green,
    ),
    _FeatureData(
      'Best Price Guarantee',
      'Get the best market price with no hidden charges',
      Icons.currency_rupee,
      Colors.amber,
    ),
    _FeatureData(
      'Legal Assistance',
      'Complete legal support for registration and documentation',
      Icons.gavel,
      Colors.indigo,
    ),
    _FeatureData(
      'Home Loan Help',
      'Easy financing options with partner banks',
      Icons.account_balance,
      Colors.blue,
    ),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Buy Property'),
        centerTitle: true,
        flexibleSpace: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              colors: [Color(0xFF1A237E), Color(0xFF283593)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            GlassCard(
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Find Your Dream Home',
                      style: TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'Thousands of verified properties across India',
                      style: TextStyle(color: Colors.grey[600], fontSize: 14),
                    ),
                    const SizedBox(height: 20),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton.icon(
                        onPressed: () => context.push('/properties'),
                        icon: const Icon(Icons.search),
                        label: const Text('Browse Properties'),
                        style: ElevatedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),
            const Text(
              'How It Works',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            ..._steps.map(
              (s) => Padding(
                padding: const EdgeInsets.only(bottom: 12),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: const Color(0xFF1A237E).withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Icon(s.icon, color: const Color(0xFF1A237E)),
                    ),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            s.title,
                            style: const TextStyle(fontWeight: FontWeight.w600),
                          ),
                          Text(
                            s.desc,
                            style: TextStyle(
                              color: Colors.grey[600],
                              fontSize: 13,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),
            const Text(
              'Why Buy With Us',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            ..._features.map(
              (f) => Padding(
                padding: const EdgeInsets.only(bottom: 12),
                child: GlassCard(
                  child: ListTile(
                    leading: Icon(f.icon, color: f.color, size: 28),
                    title: Text(
                      f.title,
                      style: const TextStyle(fontWeight: FontWeight.w600),
                    ),
                    subtitle: Text(
                      f.desc,
                      style: TextStyle(color: Colors.grey[600], fontSize: 13),
                    ),
                  ),
                ),
              ),
            ),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: () => context.push('/properties'),
                icon: const Icon(Icons.arrow_forward),
                label: const Text('Start Your Search'),
                style: ElevatedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  backgroundColor: const Color(0xFF1A237E),
                  foregroundColor: Colors.white,
                ),
              ),
            ),
            const SizedBox(height: 40),
          ],
        ),
      ),
    );
  }
}

class _StepData {
  final String title;
  final IconData icon;
  final String desc;
  const _StepData(this.title, this.icon, this.desc);
}

class _FeatureData {
  final String title;
  final String desc;
  final IconData icon;
  final Color color;
  const _FeatureData(this.title, this.desc, this.icon, this.color);
}

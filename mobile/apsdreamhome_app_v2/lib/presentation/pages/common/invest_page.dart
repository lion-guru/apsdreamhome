import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../widgets/glass_card.dart';

class InvestPage extends StatelessWidget {
  const InvestPage({super.key});

  static const _options = [
    _InvestOption(
      'Plot Investment',
      'Buy land in developing colonies with high appreciation potential',
      Icons.map,
      Colors.green,
      '12-18%',
    ),
    _InvestOption(
      'Under-Construction',
      'Book early and benefit from lower prices',
      Icons.construction,
      Colors.orange,
      '15-25%',
    ),
    _InvestOption(
      'Rental Income',
      'Earn regular rental income from ready properties',
      Icons.currency_rupee,
      Colors.blue,
      '8-10%',
    ),
    _InvestOption(
      'Commercial',
      'Invest in commercial spaces for higher returns',
      Icons.business,
      Colors.purple,
      '10-15%',
    ),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Invest in Real Estate'),
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
                      'Grow Your Wealth',
                      style: TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'Real estate investment with guaranteed returns and complete transparency',
                      style: TextStyle(color: Colors.grey[600], fontSize: 14),
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        _buildStat('12-25%', 'Annual Returns'),
                        const SizedBox(width: 24),
                        _buildStat('500+', 'Investors'),
                        const SizedBox(width: 24),
                        _buildStat('₹50Cr+', 'Portfolio'),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),
            const Text(
              'Investment Options',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            ..._options.map(
              (o) => Padding(
                padding: const EdgeInsets.only(bottom: 12),
                child: GlassCard(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: o.color.withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Icon(o.icon, color: o.color, size: 28),
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                o.title,
                                style: const TextStyle(
                                  fontWeight: FontWeight.w600,
                                  fontSize: 15,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                o.desc,
                                style: TextStyle(
                                  color: Colors.grey[600],
                                  fontSize: 13,
                                ),
                              ),
                            ],
                          ),
                        ),
                        Column(
                          children: [
                            Text(
                              o.returnRate,
                              style: TextStyle(
                                color: Colors.green[700],
                                fontWeight: FontWeight.bold,
                                fontSize: 16,
                              ),
                            ),
                            Text(
                              'returns',
                              style: TextStyle(
                                color: Colors.grey[500],
                                fontSize: 11,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
            const SizedBox(height: 24),
            const Text(
              'Why Invest With Us',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 12),
            GlassCard(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildBullet(
                      'Guaranteed property registration within 30 days',
                    ),
                    _buildBullet('Transparent pricing with no hidden charges'),
                    _buildBullet(
                      'Legal title verification before every purchase',
                    ),
                    _buildBullet('Flexible payment plans and easy financing'),
                    _buildBullet('Buyback guarantee on select projects'),
                    _buildBullet(
                      'Dedicated investment advisor for each client',
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: () => context.push('/contact'),
                icon: const Icon(Icons.arrow_forward),
                label: const Text('Talk to Investment Advisor'),
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

  Widget _buildStat(String value, String label) {
    return Column(
      children: [
        Text(
          value,
          style: const TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.bold,
            color: Color(0xFF1A237E),
          ),
        ),
        Text(label, style: TextStyle(color: Colors.grey[600], fontSize: 12)),
      ],
    );
  }

  Widget _buildBullet(String text) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(Icons.check_circle, size: 18, color: Colors.green[600]),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              text,
              style: TextStyle(color: Colors.grey[700], fontSize: 14),
            ),
          ),
        ],
      ),
    );
  }
}

class _InvestOption {
  final String title;
  final String desc;
  final IconData icon;
  final Color color;
  final String returnRate;
  const _InvestOption(
    this.title,
    this.desc,
    this.icon,
    this.color,
    this.returnRate,
  );
}

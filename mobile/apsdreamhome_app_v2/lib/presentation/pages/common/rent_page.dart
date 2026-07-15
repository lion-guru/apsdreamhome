import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class RentPage extends StatelessWidget {
  const RentPage({super.key});

  static const _benefits = [
    _BenefitData(
      'Wide Selection',
      'Apartments, houses, villas, and commercial spaces',
      Icons.apartment,
      Colors.blue,
    ),
    _BenefitData(
      'Verified Owners',
      'Direct contact with verified property owners',
      Icons.verified,
      Colors.green,
    ),
    _BenefitData(
      'Zero Brokerage',
      'No middleman — connect directly with landlords',
      Icons.money_off,
      Colors.orange,
    ),
    _BenefitData(
      'Flexible Terms',
      'Short-term and long-term rental options available',
      Icons.calendar_today,
      Colors.purple,
    ),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Rent Property'),
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
                      'Rent Your Perfect Space',
                      style: TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'Thousands of rental properties across top locations',
                      style: TextStyle(color: Colors.grey[600], fontSize: 14),
                    ),
                    const SizedBox(height: 20),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton.icon(
                        onPressed: () => context.push('/properties'),
                        icon: const Icon(Icons.search),
                        label: const Text('Browse Rentals'),
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
              'Why Rent With Us',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            ..._benefits.map(
              (b) => Padding(
                padding: const EdgeInsets.only(bottom: 12),
                child: GlassCard(
                  child: ListTile(
                    leading: Icon(b.icon, color: b.color, size: 28),
                    title: Text(
                      b.title,
                      style: const TextStyle(fontWeight: FontWeight.w600),
                    ),
                    subtitle: Text(
                      b.desc,
                      style: TextStyle(color: Colors.grey[600], fontSize: 13),
                    ),
                  ),
                ),
              ),
            ),
            const SizedBox(height: 24),
            const Text(
              'Popular Rental Locations',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 12),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children:
                  [
                        'Sector 62',
                        'Indirapuram',
                        'Vasundhara',
                        'Vaishali',
                        'Raj Nagar',
                        'Crossings Republik',
                        'Gaur City',
                        'Noida Extension',
                      ]
                      .map(
                        (l) => ActionChip(
                          avatar: const Icon(Icons.location_on, size: 16),
                          label: Text(l),
                          onPressed: () => context.push('/properties'),
                        ),
                      )
                      .toList(),
            ),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: () => context.push('/properties'),
                icon: const Icon(Icons.arrow_forward),
                label: const Text('Find Rental Properties'),
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

class _BenefitData {
  final String title;
  final String desc;
  final IconData icon;
  final Color color;
  const _BenefitData(this.title, this.desc, this.icon, this.color);
}

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

final nriDeskProvider = FutureProvider<Map<String, dynamic>>((ref) async {
  final api = ApiService();
  AppConstants.initBaseUrl();
  try {
    final res = await api.get('/api/v2/mobile/nri-desk');
    if (res['success'] == true) {
      return res['data'] as Map<String, dynamic>;
    }
  } catch (e) {
    debugPrint('Error loading NRI desk: $e');
  }
  return {
    'nri_hotline': '+91-9450000000',
    'nri_whatsapp': 'https://wa.me/919450000000?text=Hi+APS+Dream+Home,+I+am+an+NRI+interested+in+property+investment',
    'currency_rates': [],
    'faqs': []
  };
});

class NriDeskPage extends ConsumerStatefulWidget {
  const NriDeskPage({super.key});

  @override
  ConsumerState<NriDeskPage> createState() => _NriDeskPageState();
}

class _NriDeskPageState extends ConsumerState<NriDeskPage> {
  final _countryCtrl = TextEditingController(text: 'UAE (Dubai)');
  final _passportCtrl = TextEditingController();
  final _notesCtrl = TextEditingController();
  bool _submitting = false;

  Future<void> _submitInquiry() async {
    setState(() => _submitting = true);
    final api = ApiService();
    try {
      final res = await api.post('/api/v2/mobile/nri-desk/inquiry', data: {
        'country_of_residence': _countryCtrl.text.trim(),
        'passport_number': _passportCtrl.text.trim(),
        'notes': _notesCtrl.text.trim(),
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text((res as Map<String, dynamic>?)?['message']?.toString() ?? 'Inquiry registered! Our liaison officer will reach out.')),
        );
        _notesCtrl.clear();
      }
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final nriAsync = ref.watch(nriDeskProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F0),
      appBar: AppBar(
        title: const Text('NRI Global Investor Desk', style: TextStyle(fontWeight: FontWeight.w700, color: Color(0xFF0B194C))),
        backgroundColor: Colors.white,
        elevation: 0.5,
        iconTheme: const IconThemeData(color: Color(0xFF0B194C)),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.invalidate(nriDeskProvider),
          ),
        ],
      ),
      body: nriAsync.when(
        data: (data) => _buildContent(context, data),
        loading: () => const Center(child: CircularProgressIndicator(color: AppTheme.primaryColor)),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }

  Widget _buildContent(BuildContext context, Map<String, dynamic> data) {
    final rates = (data['currency_rates'] as List<dynamic>?) ?? [];
    final faqs = (data['faqs'] as List<dynamic>?) ?? [];
    final whatsappUrl = data['nri_whatsapp'] as String? ?? 'https://wa.me/919450000000';

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Hero Card
        Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            gradient: const LinearGradient(colors: [Color(0xFF0B194C), Color(0xFF1E3A8A)]),
            borderRadius: BorderRadius.circular(16),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  const Icon(Icons.public, color: Color(0xFFD4AF37), size: 28),
                  const SizedBox(width: 10),
                  const Text('Invest from Anywhere in the World', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 16)),
                ],
              ),
              const SizedBox(height: 8),
              const Text('100% RBI & FEMA Compliant Freehold Property Ownership in Purvanchal & Gorakhpur for NRIs, PIOs & OCIs.', style: TextStyle(color: Colors.white70, fontSize: 12)),
              const SizedBox(height: 16),
              ElevatedButton.icon(
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF25D366),
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                icon: const Icon(Icons.chat),
                label: const Text('24x7 NRI WhatsApp Liaison', style: TextStyle(fontWeight: FontWeight.w700)),
                onPressed: () async {
                  final uri = Uri.parse(whatsappUrl);
                  if (await canLaunchUrl(uri)) await launchUrl(uri, mode: LaunchMode.externalApplication);
                },
              ),
            ],
          ),
        ),
        const SizedBox(height: 20),

        // Live Exchange Rates
        const Text('Live Remittance Conversion Rates', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 15, color: Color(0xFF0B194C))),
        const SizedBox(height: 10),
        SizedBox(
          height: 90,
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            itemCount: rates.length,
            itemBuilder: (ctx, i) {
              final r = rates[i] as Map<String, dynamic>;
              return Container(
                width: 140,
                margin: const EdgeInsets.only(right: 10),
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12), border: Border.all(color: Colors.grey.shade200)),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text('1 ${r['currency'] ?? ''}', style: const TextStyle(fontWeight: FontWeight.w800, color: Color(0xFF0B194C), fontSize: 14)),
                    const SizedBox(height: 4),
                    Text('≈ ₹${r['rate_inr'] ?? '-'}', style: const TextStyle(fontWeight: FontWeight.w800, color: Colors.green, fontSize: 16)),
                    Text(r['country']?.toString() ?? '', style: TextStyle(color: Colors.grey.shade500, fontSize: 10), maxLines: 1, overflow: TextOverflow.ellipsis),
                  ],
                ),
              );
            },
          ),
        ),
        const SizedBox(height: 20),

        // Power of Attorney & Remote Registry Steps
        GlassCard(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('Remote Registry via Power of Attorney (POA)', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 15, color: Color(0xFF0B194C))),
              const SizedBox(height: 10),
              _buildStep('1', 'Select Your Plot online via 3D Virtual Tour & CAD layout.'),
              _buildStep('2', 'Embassy Attestation: Download POA draft and get attested by Indian Embassy / Consulate.'),
              _buildStep('3', 'Tehsil Adjudication: Our legal team adjudicates the POA at Gorakhpur District Court.'),
              _buildStep('4', 'Sale Deed Registry: Land registry completed on your behalf with original deed couriered to your overseas address.'),
            ],
          ),
        ),
        const SizedBox(height: 20),

        // Request Priority Remote Consultation
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12), border: Border.all(color: Colors.grey.shade200)),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('Request Priority NRI Video Consultation', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 15, color: Color(0xFF0B194C))),
              const SizedBox(height: 6),
              const Text('Schedule a 1-on-1 Zoom/Google Meet call with our VP of International Sales.', style: TextStyle(color: Colors.grey, fontSize: 12)),
              const SizedBox(height: 12),
              TextField(
                controller: _countryCtrl,
                decoration: InputDecoration(labelText: 'Country of Residence', border: OutlineInputBorder(borderRadius: BorderRadius.circular(10))),
              ),
              const SizedBox(height: 10),
              TextField(
                controller: _passportCtrl,
                decoration: InputDecoration(labelText: 'Passport / OCI Number (Optional)', border: OutlineInputBorder(borderRadius: BorderRadius.circular(10))),
              ),
              const SizedBox(height: 10),
              TextField(
                controller: _notesCtrl,
                maxLines: 2,
                decoration: InputDecoration(labelText: 'Preferred Colony / Budget / Query', border: OutlineInputBorder(borderRadius: BorderRadius.circular(10))),
              ),
              const SizedBox(height: 12),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF0B194C),
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                  ),
                  onPressed: _submitting ? null : _submitInquiry,
                  child: _submitting
                      ? const SizedBox(height: 16, width: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                      : const Text('Book Priority NRI Call', style: TextStyle(fontWeight: FontWeight.w700)),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 20),

        // FAQs
        const Text('Frequently Asked Questions (FEMA & Tax)', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 15, color: Color(0xFF0B194C))),
        const SizedBox(height: 10),
        ...faqs.map((f) {
          final item = f as Map<String, dynamic>;
          return Container(
            margin: const EdgeInsets.only(bottom: 8),
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(10)),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(item['q']?.toString() ?? '', style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13, color: Color(0xFF0B194C))),
                const SizedBox(height: 4),
                Text(item['a']?.toString() ?? '', style: TextStyle(color: Colors.grey.shade700, fontSize: 12)),
              ],
            ),
          );
        }),
      ],
    );
  }

  Widget _buildStep(String num, String text) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          CircleAvatar(radius: 10, backgroundColor: const Color(0xFFD4AF37), child: Text(num, style: const TextStyle(color: Color(0xFF0B194C), fontSize: 11, fontWeight: FontWeight.w800))),
          const SizedBox(width: 8),
          Expanded(child: Text(text, style: TextStyle(color: Colors.grey.shade700, fontSize: 12))),
        ],
      ),
    );
  }
}

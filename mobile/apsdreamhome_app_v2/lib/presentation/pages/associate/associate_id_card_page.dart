import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

final associateIdCardProvider = FutureProvider<Map<String, dynamic>>((ref) async {
  final api = ApiService();
  AppConstants.initBaseUrl();
  try {
    final res = await api.get('/api/v2/mobile/associate/id-card');
    if (res['success'] == true) {
      return res['data'] as Map<String, dynamic>;
    }
  } catch (e) {
    debugPrint('Error loading associate ID card: $e');
  }
  return {
    'associate_name': 'Authorized Associate',
    'associate_code': 'APS001',
    'designation': 'Authorized Channel Partner',
    'rank': 'Gold Crown Partner',
    'phone': '+91 9450000000',
    'email': 'partner@apsdreamhome.com',
    'joined_date': '01 Jan 2025',
    'valid_till': '31 Dec 2027',
    'qr_code_url': 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=https://apsdreamhome.com/ref/APS001'
  };
});

class AssociateIdCardPage extends ConsumerStatefulWidget {
  const AssociateIdCardPage({super.key});

  @override
  ConsumerState<AssociateIdCardPage> createState() => _AssociateIdCardPageState();
}

class _AssociateIdCardPageState extends ConsumerState<AssociateIdCardPage> {
  bool _showBack = false;

  @override
  Widget build(BuildContext context) {
    final cardAsync = ref.watch(associateIdCardProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F0),
      appBar: AppBar(
        title: const Text('Official Associate ID Card', style: TextStyle(fontWeight: FontWeight.w700, color: Color(0xFF0B194C))),
        backgroundColor: Colors.white,
        elevation: 0.5,
        iconTheme: const IconThemeData(color: Color(0xFF0B194C)),
        actions: [
          IconButton(
            icon: const Icon(Icons.flip),
            tooltip: 'Flip Card',
            onPressed: () => setState(() => _showBack = !_showBack),
          ),
        ],
      ),
      body: cardAsync.when(
        data: (data) => _buildBody(context, data),
        loading: () => const Center(child: CircularProgressIndicator(color: AppTheme.primaryColor)),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }

  Widget _buildBody(BuildContext context, Map<String, dynamic> data) {
    final name = data['associate_name'] as String? ?? 'Associate Name';
    final code = data['associate_code'] as String? ?? 'APS001';
    final rank = data['rank'] as String? ?? 'Gold Partner';
    final designation = data['designation'] as String? ?? 'Channel Partner';
    final phone = data['phone'] as String? ?? '+91 9450000000';
    final validTill = data['valid_till'] as String? ?? '31 Dec 2027';
    final joined = data['joined_date'] as String? ?? '01 Jan 2025';
    final qrUrl = data['qr_code_url'] as String? ?? '';

    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        Center(
          child: InkWell(
            onTap: () => setState(() => _showBack = !_showBack),
            child: AnimatedSwitcher(
              duration: const Duration(milliseconds: 400),
              child: _showBack
                  ? _buildCardBack(data, qrUrl, code)
                  : _buildCardFront(name, code, rank, designation, phone, validTill, joined),
            ),
          ),
        ),
        const SizedBox(height: 16),
        Center(
          child: TextButton.icon(
            icon: const Icon(Icons.touch_app, size: 16, color: Color(0xFF0B194C)),
            label: Text(_showBack ? 'Tap to view Front side' : 'Tap card to flip to QR / Back side', style: const TextStyle(color: Color(0xFF0B194C), fontSize: 13, fontWeight: FontWeight.w600)),
            onPressed: () => setState(() => _showBack = !_showBack),
          ),
        ),
        const SizedBox(height: 20),

        // Action Buttons
        Row(
          children: [
            Expanded(
              child: ElevatedButton.icon(
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF25D366),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                icon: const Icon(Icons.share),
                label: const Text('Share on WhatsApp', style: TextStyle(fontWeight: FontWeight.w700)),
                onPressed: () async {
                  final text = "👋 Hello! I am *$name*, authorized real estate channel partner at *APS Dream Home Developers Gorakhpur*.\n\n🆔 Associate Code: *$code*\n🌟 Rank: *$rank*\n📞 Call/WhatsApp: $phone\n\nExplore our RERA & Mutation-verified townships: https://apsdreamhome.com/ref/$code";
                  final uri = Uri.parse("https://wa.me/?text=${Uri.encodeComponent(text)}");
                  if (await canLaunchUrl(uri)) await launchUrl(uri, mode: LaunchMode.externalApplication);
                },
              ),
            ),
          ],
        ),
        const SizedBox(height: 20),

        // Verification & Authenticity Notice
        GlassCard(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Row(
                children: [
                  Icon(Icons.verified, color: Colors.green, size: 20),
                  SizedBox(width: 8),
                  Text('Authorized Representation Certificate', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: Color(0xFF0B194C))),
                ],
              ),
              const SizedBox(height: 6),
              Text('This digital identity card confirms that $name ($code) is an authorized representative of APS Dream Home Developers Pvt. Ltd. Clients can verify authenticity by scanning the QR code on the reverse.', style: TextStyle(color: Colors.grey.shade600, fontSize: 12)),
            ],
          ),
        )
      ],
    );
  }

  Widget _buildCardFront(String name, String code, String rank, String designation, String phone, String validTill, String joined) {
    return Container(
      key: const ValueKey('front'),
      width: 340,
      height: 480,
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF0B194C), Color(0xFF13256B)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFD4AF37), width: 2),
        boxShadow: [
          BoxShadow(color: Colors.black.withValues(alpha: 0.2), blurRadius: 15, offset: const Offset(0, 8)),
        ],
      ),
      child: Stack(
        children: [
          // Background Gold Accents
          Positioned(
            top: -30,
            right: -30,
            child: Container(
              width: 120,
              height: 120,
              decoration: BoxDecoration(shape: BoxShape.circle, color: const Color(0xFFD4AF37).withValues(alpha: 0.15)),
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              children: [
                // Header Brand
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('APS DREAM HOME', style: TextStyle(fontWeight: FontWeight.w900, color: Color(0xFFD4AF37), fontSize: 15, letterSpacing: 1.2)),
                        Text('DEVELOPERS PVT. LTD.', style: TextStyle(fontWeight: FontWeight.w600, color: Colors.white70, fontSize: 9, letterSpacing: 1.0)),
                      ],
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(color: const Color(0xFFD4AF37), borderRadius: BorderRadius.circular(6)),
                      child: const Text('OFFICIAL ID', style: TextStyle(color: Color(0xFF0B194C), fontWeight: FontWeight.w900, fontSize: 9)),
                    ),
                  ],
                ),
                const SizedBox(height: 20),

                // Avatar / Photo
                Container(
                  padding: const EdgeInsets.all(4),
                  decoration: const BoxDecoration(shape: BoxShape.circle, color: Color(0xFFD4AF37)),
                  child: CircleAvatar(
                    radius: 46,
                    backgroundColor: Colors.white,
                    child: Text(name.isNotEmpty ? name[0] : 'A', style: const TextStyle(fontSize: 36, fontWeight: FontWeight.w800, color: Color(0xFF0B194C))),
                  ),
                ),
                const SizedBox(height: 14),

                // Associate Name & Designation
                Text(name, textAlign: TextAlign.center, style: const TextStyle(fontWeight: FontWeight.w800, color: Colors.white, fontSize: 18)),
                const SizedBox(height: 4),
                Text(designation, textAlign: TextAlign.center, style: const TextStyle(color: Color(0xFFD4AF37), fontWeight: FontWeight.w600, fontSize: 12)),
                const SizedBox(height: 6),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                  decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(12)),
                  child: Text('🌟 $rank', style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w600)),
                ),
                const Spacer(),

                // Bottom Meta
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.08), borderRadius: BorderRadius.circular(12)),
                  child: Column(
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text('Associate Code:', style: TextStyle(color: Colors.white70, fontSize: 11)),
                          Text(code, style: const TextStyle(color: Color(0xFFD4AF37), fontWeight: FontWeight.w800, fontSize: 12)),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text('Mobile:', style: TextStyle(color: Colors.white70, fontSize: 11)),
                          Text(phone, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 11)),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text('Valid Till:', style: TextStyle(color: Colors.white70, fontSize: 11)),
                          Text(validTill, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 11)),
                        ],
                      ),
                    ],
                  ),
                )
              ],
            ),
          )
        ],
      ),
    );
  }

  Widget _buildCardBack(Map<String, dynamic> data, String qrUrl, String code) {
    return Container(
      key: const ValueKey('back'),
      width: 340,
      height: 480,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFD4AF37), width: 2),
        boxShadow: [
          BoxShadow(color: Colors.black.withValues(alpha: 0.2), blurRadius: 15, offset: const Offset(0, 8)),
        ],
      ),
      padding: const EdgeInsets.all(20),
      child: Column(
        children: [
          const Text('OFFICIAL VERIFICATION QR', style: TextStyle(fontWeight: FontWeight.w800, color: Color(0xFF0B194C), fontSize: 13, letterSpacing: 1.0)),
          const SizedBox(height: 4),
          const Text('Scan to verify authorization & view inventory', style: TextStyle(color: Colors.grey, fontSize: 11)),
          const SizedBox(height: 16),

          // QR Code
          if (qrUrl.isNotEmpty)
            Image.network(qrUrl, width: 140, height: 140, errorBuilder: (_, __, ___) => const Icon(Icons.qr_code_2, size: 120, color: Color(0xFF0B194C)))
          else
            const Icon(Icons.qr_code_2, size: 120, color: Color(0xFF0B194C)),
          const SizedBox(height: 8),
          Text('Code: $code', style: const TextStyle(fontWeight: FontWeight.w700, color: Color(0xFF0B194C), fontSize: 13)),
          const Divider(height: 24),

          // Office Address & Terms
          const Text('APS DREAM HOME DEVELOPERS PVT. LTD.', textAlign: TextAlign.center, style: TextStyle(fontWeight: FontWeight.w700, color: Color(0xFF0B194C), fontSize: 11)),
          const SizedBox(height: 4),
          const Text('Medical College Road, Mohaddipur, Gorakhpur, UP - 273008', textAlign: TextAlign.center, style: TextStyle(color: Colors.grey, fontSize: 10)),
          const SizedBox(height: 4),
          const Text('Helpline: +91 9450000000 | info@apsdreamhome.com', textAlign: TextAlign.center, style: TextStyle(color: Colors.grey, fontSize: 10)),
          const Spacer(),
          const Text('Strictly non-transferable. Misuse is a punishable legal offense.', textAlign: TextAlign.center, style: TextStyle(color: Colors.redAccent, fontSize: 9, fontStyle: FontStyle.italic)),
        ],
      ),
    );
  }
}

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:share_plus/share_plus.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../widgets/glass_card.dart';
import '../../../data/services/referral_service.dart';

class PropertyDetailPage extends ConsumerStatefulWidget {
  const PropertyDetailPage({
    super.key,
    required this.propertyId,
    this.title = '',
    this.price = 0,
    this.location = '',
    this.area = 0,
    this.type = '',
    this.description = '',
    this.image = '',
  });

  final String propertyId;
  final String title;
  final double price;
  final String location;
  final double area;
  final String type;
  final String description;
  final String image;

  @override
  ConsumerState<PropertyDetailPage> createState() => _PropertyDetailPageState();
}

class _PropertyDetailPageState extends ConsumerState<PropertyDetailPage> {
  String _referralCode = '';

  @override
  void initState() {
    super.initState();
    _loadReferralCode();
  }

  Future<void> _loadReferralCode() async {
    try {
      final referralService = ref.read(referralServiceProvider);
      final code = await referralService.getReferralCode();
      if (mounted && code != null) {
        setState(() => _referralCode = code);
      }
    } catch (_) {}
  }

  Future<void> _shareViaWhatsApp() async {
    final referralService = ref.read(referralServiceProvider);
    final code = _referralCode.isNotEmpty ? _referralCode : 'GUEST';

    final message = referralService.generateShareMessage(
      propertyName: widget.title,
      referralCode: code,
      propertyId: int.tryParse(widget.propertyId),
      price: widget.price > 0 ? '₹${widget.price.toStringAsFixed(0)}' : null,
      location: widget.location,
    );

    final encodedMessage = Uri.encodeComponent(message);
    final whatsappUrl = 'https://wa.me/?text=$encodedMessage';

    try {
      if (await canLaunchUrl(Uri.parse(whatsappUrl))) {
        await launchUrl(Uri.parse(whatsappUrl), mode: LaunchMode.externalApplication);
        // Track referral
        referralService.shareViaWhatsApp(
          propertyName: widget.title,
          referralCode: code,
          propertyId: int.tryParse(widget.propertyId),
          price: widget.price > 0 ? '₹${widget.price.toStringAsFixed(0)}' : null,
          location: widget.location,
        );
      } else {
        // Fallback to system share
        await Share.share(message);
      }
    } catch (e) {
      await Share.share(message);
    }
  }

  @override
  Widget build(BuildContext context) {
    return GradientBackground(
      child: Scaffold(
        backgroundColor: Colors.transparent,
        appBar: AppBar(
          title: const Text('Property Details'),
          backgroundColor: Colors.transparent,
          elevation: 0,
          actions: [
            if (_referralCode.isNotEmpty)
              IconButton(
                onPressed: _shareViaWhatsApp,
                icon: const Icon(Icons.share, color: Colors.white),
                tooltip: 'Share via WhatsApp',
              ),
          ],
        ),
        floatingActionButton: FloatingActionButton.extended(
          onPressed: _shareViaWhatsApp,
          backgroundColor: const Color(0xFF25D366),
          foregroundColor: Colors.white,
          icon: const Icon(Icons.chat),
          label: const Text('Share on WhatsApp'),
        ),
        body: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Property Image
              if (widget.image.isNotEmpty)
                ClipRRect(
                  borderRadius: BorderRadius.circular(16),
                  child: Image.network(
                    widget.image,
                    height: 220,
                    width: double.infinity,
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) => Container(
                      height: 220,
                      width: double.infinity,
                      decoration: BoxDecoration(
                        color: Colors.white10,
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: const Icon(Icons.home, size: 64, color: Colors.white38),
                    ),
                  ),
                ),

              const SizedBox(height: 16),

              // Title & Type
              GlassCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            widget.title.isNotEmpty ? widget.title : 'Property #${widget.propertyId}',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 22,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: const Color(0xFFFFD700).withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Text(
                            widget.type.toUpperCase(),
                            style: const TextStyle(
                              color: Color(0xFFFFD700),
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    if (widget.location.isNotEmpty)
                      Row(
                        children: [
                          const Icon(Icons.location_on, color: Colors.white54, size: 18),
                          const SizedBox(width: 4),
                          Text(
                            widget.location,
                            style: const TextStyle(color: Colors.white70, fontSize: 14),
                          ),
                        ],
                      ),
                  ],
                ),
              ),

              const SizedBox(height: 12),

              // Price & Area
              GlassCard(
                child: Row(
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Price', style: TextStyle(color: Colors.white54, fontSize: 12)),
                          Text(
                            widget.price > 0 ? '₹${widget.price.toStringAsFixed(0)}' : 'Price on request',
                            style: const TextStyle(
                              color: Color(0xFFFFD700),
                              fontSize: 24,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                    ),
                    Container(width: 1, height: 40, color: Colors.white24),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Area', style: TextStyle(color: Colors.white54, fontSize: 12)),
                          Text(
                            widget.area > 0 ? '${widget.area.toStringAsFixed(0)} sqft' : 'N/A',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 20,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 12),

              // Description
              if (widget.description.isNotEmpty)
                GlassCard(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Description',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        widget.description,
                        style: const TextStyle(color: Colors.white70, fontSize: 14, height: 1.5),
                      ),
                    ],
                  ),
                ),

              const SizedBox(height: 12),

              // Features
              GlassCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Features',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        _featureChip(Icons.square_foot, 'Area'),
                        const SizedBox(width: 8),
                        _featureChip(Icons.aspect_ratio, 'Dimensions'),
                        const SizedBox(width: 8),
                        _featureChip(Icons.location_on, 'Location'),
                      ],
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 12),

              // Referral info
              if (_referralCode.isNotEmpty)
                GlassCard(
                  opacity: 0.1,
                  child: Row(
                    children: [
                      const Icon(Icons.card_giftcard, color: Color(0xFFFFD700), size: 24),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Share & Earn Referral Benefits',
                              style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600),
                            ),
                            Text(
                              'Your code: $_referralCode',
                              style: const TextStyle(color: Colors.white70, fontSize: 12),
                            ),
                          ],
                        ),
                      ),
                      const Icon(Icons.arrow_forward_ios, color: Colors.white54, size: 16),
                    ],
                  ),
                ),

              const SizedBox(height: 80),
            ],
          ),
        ),
      ),
    );
  }

  Widget _featureChip(IconData icon, String label) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: Colors.white10,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: Colors.white24),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, color: Colors.white54, size: 16),
          const SizedBox(width: 4),
          Text(label, style: const TextStyle(color: Colors.white70, fontSize: 12)),
        ],
      ),
    );
  }
}

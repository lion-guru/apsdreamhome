import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:share_plus/share_plus.dart';
import 'package:url_launcher/url_launcher.dart';

import 'dart:convert';
import 'package:http/http.dart' as http;

import '../../widgets/glass_card.dart';
import '../../../data/services/property_listing_service.dart';
import '../../../data/services/referral_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/constants/app_constants.dart';

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
    this.images = const [],
  });

  final String propertyId;
  final String title;
  final double price;
  final String location;
  final double area;
  final String type;
  final String description;
  final String image;
  final List<String> images;

  @override
  ConsumerState<PropertyDetailPage> createState() => _PropertyDetailPageState();
}

class _PropertyDetailPageState extends ConsumerState<PropertyDetailPage> {
  String _referralCode = '';
  PropertyListing? _property;
  bool _isLoading = true;
  String? _loadError;
  int _galleryIndex = 0;
  bool _inquiryExpanded = false;
  bool _inquirySubmitting = false;
  final _inquiryNameController = TextEditingController();
  final _inquiryPhoneController = TextEditingController();
  final _inquiryMessageController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadReferralCode();
    _loadProperty();
  }

  Future<void> _loadProperty() async {
    try {
      final service = ref.read(propertyListingServiceProvider);
      final prop = await service.getPropertyById(widget.propertyId);
      if (mounted) {
        setState(() {
          _property = prop;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _loadError = e.toString();
          _isLoading = false;
        });
      }
    }
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

  List<String> get _allImages {
    if (_property != null && _property!.images.isNotEmpty) {
      return _property!.images;
    }
    if (widget.images.isNotEmpty) {
      return widget.images;
    }
    if (widget.image.isNotEmpty) {
      return [widget.image];
    }
    return [];
  }

  String get _displayTitle =>
      (_property?.title.isNotEmpty == true) ? _property!.title : widget.title;

  double get _displayPrice =>
      _property != null ? _property!.price : widget.price;

  String get _displayLocation => (_property?.location.isNotEmpty == true)
      ? _property!.location
      : widget.location;

  double get _displayArea => _property?.area ?? widget.area;

  String get _displayType =>
      (_property?.type.isNotEmpty == true) ? _property!.type : widget.type;

  String get _displayDescription => (_property?.description.isNotEmpty == true)
      ? _property!.description
      : widget.description;

  bool get _isDisplayVerified => _property?.isVerified ?? false;

  Future<void> _shareViaWhatsApp() async {
    final referralService = ref.read(referralServiceProvider);
    final code = _referralCode.isNotEmpty ? _referralCode : 'GUEST';

    final message = referralService.generateShareMessage(
      propertyName: _displayTitle,
      referralCode: code,
      propertyId: int.tryParse(widget.propertyId),
      price: _displayPrice > 0 ? '₹${_displayPrice.toStringAsFixed(0)}' : null,
      location: _displayLocation,
    );

    final encodedMessage = Uri.encodeComponent(message);
    final whatsappUrl = 'https://wa.me/?text=$encodedMessage';

    try {
      if (await canLaunchUrl(Uri.parse(whatsappUrl))) {
        await launchUrl(
          Uri.parse(whatsappUrl),
          mode: LaunchMode.externalApplication,
        );
        referralService.shareViaWhatsApp(
          propertyName: _displayTitle,
          referralCode: code,
          propertyId: int.tryParse(widget.propertyId),
          price: _displayPrice > 0
              ? '₹${_displayPrice.toStringAsFixed(0)}'
              : null,
          location: _displayLocation,
        );
      } else {
        await Share.share(message);
      }
    } catch (e) {
      await Share.share(message);
    }
  }

  void _openFullScreen(int index) {
    final images = _allImages;
    if (images.isEmpty) return;
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => _PropertyImageViewer(
          images: images,
          initialIndex: index,
          title: _displayTitle,
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return _buildLoading();
    }

    final images = _allImages;

    return GradientBackground(
      child: Scaffold(
        backgroundColor: Colors.transparent,
        appBar: AppBar(
          title: Text(
            _displayTitle.isNotEmpty ? _displayTitle : 'Property Details',
            overflow: TextOverflow.ellipsis,
          ),
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
              // ─── Gallery Carousel ───
              if (images.isNotEmpty) _buildGalleryCarousel(images),

              if (images.isNotEmpty) const SizedBox(height: 16),

              // ─── Title & Type ───
              GlassCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            _displayTitle.isNotEmpty
                                ? _displayTitle
                                : 'Property #${widget.propertyId}',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 22,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                        Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            if (_isDisplayVerified)
                              Container(
                                margin: const EdgeInsets.only(right: 6),
                                padding: const EdgeInsets.all(4),
                                decoration: const BoxDecoration(
                                  color: Color(0xFF4CAF50),
                                  shape: BoxShape.circle,
                                ),
                                child: const Icon(
                                  Icons.verified,
                                  color: Colors.white,
                                  size: 14,
                                ),
                              ),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 10,
                                vertical: 4,
                              ),
                              decoration: BoxDecoration(
                                color: const Color(
                                  0xFFFFD700,
                                ).withValues(alpha: 0.2),
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Text(
                                _displayType.toUpperCase(),
                                style: const TextStyle(
                                  color: Color(0xFFFFD700),
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    if (_displayLocation.isNotEmpty)
                      Row(
                        children: [
                          const Icon(
                            Icons.location_on,
                            color: Colors.white54,
                            size: 18,
                          ),
                          const SizedBox(width: 4),
                          Expanded(
                            child: Text(
                              _displayLocation,
                              style: const TextStyle(
                                color: Colors.white70,
                                fontSize: 14,
                              ),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                  ],
                ),
              ),

              const SizedBox(height: 12),

              // ─── Price & Area ───
              GlassCard(
                child: Row(
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Price',
                            style: TextStyle(
                              color: Colors.white54,
                              fontSize: 12,
                            ),
                          ),
                          Text(
                            _displayPrice > 0
                                ? '₹${_displayPrice.toStringAsFixed(0)}'
                                : 'Price on request',
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
                          const Text(
                            'Area',
                            style: TextStyle(
                              color: Colors.white54,
                              fontSize: 12,
                            ),
                          ),
                          Text(
                            _displayArea > 0
                                ? '${_displayArea.toStringAsFixed(0)} sqft'
                                : 'N/A',
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

              // ─── Description ───
              if (_displayDescription.isNotEmpty)
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
                        _displayDescription,
                        style: const TextStyle(
                          color: Colors.white70,
                          fontSize: 14,
                          height: 1.5,
                        ),
                      ),
                    ],
                  ),
                ),

              const SizedBox(height: 12),

              // ─── Image Type Grid ───
              if (images.length > 1) _buildImageTypeGrid(images),

              // ─── Features ───
              const SizedBox(height: 12),
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
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: [
                        _featureChip(Icons.square_foot, '$_displayArea sqft'),
                        _featureChip(Icons.aspect_ratio, _displayType),
                        _featureChip(Icons.location_on, _displayLocation),
                        if (_isDisplayVerified)
                          _featureChip(Icons.verified, 'Verified'),
                        if (_property?.purpose != null)
                          _featureChip(Icons.sell, _property!.purposeLabel),
                      ],
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 12),

              // ─── Referral info ───
              if (_referralCode.isNotEmpty)
                GlassCard(
                  opacity: 0.1,
                  child: Row(
                    children: [
                      const Icon(
                        Icons.card_giftcard,
                        color: Color(0xFFFFD700),
                        size: 24,
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Share & Earn Referral Benefits',
                              style: TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            Text(
                              'Your code: $_referralCode',
                              style: const TextStyle(
                                color: Colors.white70,
                                fontSize: 12,
                              ),
                            ),
                          ],
                        ),
                      ),
                      const Icon(
                        Icons.arrow_forward_ios,
                        color: Colors.white54,
                        size: 16,
                      ),
                    ],
                  ),
                ),

              const SizedBox(height: 12),

              // ─── Listing Badges (Featured / Premium / Urgent) ───
              if (_property != null &&
                  (_property!.isFeatured ||
                      _property!.isPremium ||
                      _property!.isUrgent))
                Padding(
                  padding: const EdgeInsets.only(bottom: 12),
                  child: Row(
                    children: [
                      if (_property!.isFeatured)
                        _listingBadge(
                          'Featured',
                          Icons.star,
                          Colors.amber.shade700,
                        ),
                      if (_property!.isFeatured) const SizedBox(width: 8),
                      if (_property!.isPremium)
                        _listingBadge(
                          'Premium',
                          Icons.diamond,
                          Colors.purple.shade700,
                        ),
                      if (_property!.isPremium) const SizedBox(width: 8),
                      if (_property!.isUrgent)
                        _listingBadge(
                          'Urgent Sale',
                          Icons.bolt,
                          Colors.red.shade700,
                        ),
                    ],
                  ),
                ),

              // ─── Property Stats ───
              GlassCard(
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceAround,
                  children: [
                    _statItem(
                      Icons.visibility,
                      '${_property?.views ?? 0}',
                      'Views',
                    ),
                    _statItem(
                      Icons.help_outline,
                      '${_property?.inquiries ?? 0}',
                      'Inquiries',
                    ),
                    _statItem(Icons.share, '', 'Share'),
                  ],
                ),
              ),

              const SizedBox(height: 12),

              // ─── Send Inquiry (inline) ───
              if (_property != null) _buildInquirySection(),

              const SizedBox(height: 12),

              // ─── Contact Agent ───
              _buildContactButtons(),

              const SizedBox(height: 12),

              // ─── Boost Listing (if owner) ───
              if (_property != null)
                GlassCard(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Icon(
                            Icons.rocket_launch,
                            color: Colors.purple.shade300,
                            size: 24,
                          ),
                          const SizedBox(width: 12),
                          const Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Want more visibility?',
                                  style: TextStyle(
                                    color: Colors.white,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 15,
                                  ),
                                ),
                                Text(
                                  'Boost your listing to reach more buyers',
                                  style: TextStyle(
                                    color: Colors.white70,
                                    fontSize: 12,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      SizedBox(
                        width: double.infinity,
                        child: OutlinedButton.icon(
                          onPressed: () {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(
                                content: Text('Listing packages coming soon'),
                              ),
                            );
                          },
                          icon: const Icon(Icons.inventory_2, size: 18),
                          label: const Text('View Packages'),
                          style: OutlinedButton.styleFrom(
                            foregroundColor: Colors.purple.shade300,
                            side: BorderSide(color: Colors.purple.shade300),
                            padding: const EdgeInsets.symmetric(vertical: 12),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),

              const SizedBox(height: 12),

              // ─── Set Alert action ───
              GlassCard(
                opacity: 0.08,
                child: InkWell(
                  onTap: () {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Alert set for this property type'),
                        backgroundColor: AppTheme.successColor,
                      ),
                    );
                  },
                  borderRadius: BorderRadius.circular(12),
                  child: const Row(
                    children: [
                      Icon(
                        Icons.notifications_active,
                        color: Color(0xFFFFD700),
                        size: 24,
                      ),
                      SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Set Property Alert',
                              style: TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            Text(
                              'Get notified for similar properties',
                              style: TextStyle(
                                color: Colors.white70,
                                fontSize: 12,
                              ),
                            ),
                          ],
                        ),
                      ),
                      Icon(
                        Icons.arrow_forward_ios,
                        color: Colors.white54,
                        size: 16,
                      ),
                    ],
                  ),
                ),
              ),

              const SizedBox(height: 80),
            ],
          ),
        ),
      ),
    );
  }

  Widget _listingBadge(String label, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.2),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color.withValues(alpha: 0.5)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, color: color, size: 14),
          const SizedBox(width: 4),
          Text(
            label,
            style: TextStyle(
              color: color,
              fontSize: 12,
              fontWeight: FontWeight.bold,
            ),
          ),
        ],
      ),
    );
  }

  Widget _statItem(IconData icon, String value, String label) {
    return Column(
      children: [
        Icon(icon, color: Colors.white54, size: 20),
        const SizedBox(height: 4),
        Text(
          value.isNotEmpty ? value : '-',
          style: const TextStyle(
            color: Colors.white,
            fontSize: 16,
            fontWeight: FontWeight.bold,
          ),
        ),
        Text(
          label,
          style: const TextStyle(color: Colors.white54, fontSize: 11),
        ),
      ],
    );
  }

  Widget _buildInquirySection() {
    return GlassCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          InkWell(
            onTap: () => setState(() => _inquiryExpanded = !_inquiryExpanded),
            borderRadius: BorderRadius.circular(12),
            child: Row(
              children: [
                Icon(
                  Icons.question_answer,
                  color: Colors.amber.shade700,
                  size: 24,
                ),
                const SizedBox(width: 12),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Interested in this property?',
                        style: TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                          fontSize: 15,
                        ),
                      ),
                      Text(
                        'Tap to send an inquiry',
                        style: TextStyle(
                          color: Colors.white70,
                          fontSize: 12,
                        ),
                      ),
                    ],
                  ),
                ),
                Icon(
                  _inquiryExpanded
                      ? Icons.keyboard_arrow_up
                      : Icons.keyboard_arrow_down,
                  color: Colors.white54,
                ),
              ],
            ),
          ),
          AnimatedCrossFade(
            firstChild: const SizedBox.shrink(),
            secondChild: Padding(
              padding: const EdgeInsets.only(top: 16),
              child: Column(
                children: [
                  TextField(
                    controller: _inquiryNameController,
                    style: const TextStyle(color: Colors.white),
                    textCapitalization: TextCapitalization.words,
                    decoration: InputDecoration(
                      hintText: 'Your Name *',
                      hintStyle: const TextStyle(color: Colors.white38),
                      filled: true,
                      fillColor: Colors.white10,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(10),
                        borderSide: BorderSide.none,
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: _inquiryPhoneController,
                    style: const TextStyle(color: Colors.white),
                    keyboardType: TextInputType.phone,
                    decoration: InputDecoration(
                      hintText: 'Phone Number *',
                      hintStyle: const TextStyle(color: Colors.white38),
                      filled: true,
                      fillColor: Colors.white10,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(10),
                        borderSide: BorderSide.none,
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: _inquiryMessageController,
                    style: const TextStyle(color: Colors.white),
                    maxLines: 3,
                    decoration: InputDecoration(
                      hintText: 'Message (optional)',
                      hintStyle: const TextStyle(color: Colors.white38),
                      filled: true,
                      fillColor: Colors.white10,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(10),
                        borderSide: BorderSide.none,
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      onPressed: _inquirySubmitting ? null : _submitInquiry,
                      icon: _inquirySubmitting
                          ? const SizedBox(
                              height: 18,
                              width: 18,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                color: Colors.black,
                              ),
                            )
                          : const Icon(Icons.send, size: 18),
                      label: Text(
                        _inquirySubmitting ? 'Sending...' : 'Send Inquiry',
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFFFFD700),
                        foregroundColor: Colors.black,
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
            crossFadeState: _inquiryExpanded
                ? CrossFadeState.showSecond
                : CrossFadeState.showFirst,
            duration: const Duration(milliseconds: 300),
          ),
        ],
      ),
    );
  }

  Future<void> _submitInquiry() async {
    if (_inquiryNameController.text.trim().isEmpty ||
        _inquiryPhoneController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Name and phone are required'),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }
    setState(() => _inquirySubmitting = true);
    try {
      final url = Uri.parse(
        '${AppConstants.baseUrl}${AppConstants.apiVersion}${AppConstants.propertyInquiryEndpoint}',
      );
      await http.post(
        url,
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'property_id': widget.propertyId,
          'name': _inquiryNameController.text.trim(),
          'phone': _inquiryPhoneController.text.trim(),
          'message': _inquiryMessageController.text.trim(),
        }),
      );
      _inquiryNameController.clear();
      _inquiryPhoneController.clear();
      _inquiryMessageController.clear();
      setState(() {
        _inquirySubmitting = false;
        _inquiryExpanded = false;
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Inquiry sent successfully!'),
            backgroundColor: Colors.green,
          ),
        );
      }
    } catch (e) {
      setState(() => _inquirySubmitting = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to send: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  Widget _buildContactButtons() {
    const phone = AppConstants.supportPhone;
    final message = Uri.encodeComponent(
      'Hi, I\'m interested in ${_displayTitle.isNotEmpty ? _displayTitle : "a property"}.',
    );

    return GlassCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Contact Agent',
            style: TextStyle(
              color: Colors.white,
              fontSize: 16,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 4),
          const Text(
            'Get expert guidance on this property',
            style: TextStyle(color: Colors.white70, fontSize: 12),
          ),
          const SizedBox(height: 16),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: () async {
                final url = Uri.parse('https://wa.me/91$phone?text=$message');
                if (await canLaunchUrl(url)) {
                  await launchUrl(url, mode: LaunchMode.externalApplication);
                }
              },
              icon: const Icon(Icons.chat, size: 20),
              label: const Text('WhatsApp'),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF25D366),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
          ),
          const SizedBox(height: 10),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: () async {
                final url = Uri.parse('tel:+91$phone');
                if (await canLaunchUrl(url)) {
                  await launchUrl(url, mode: LaunchMode.externalApplication);
                }
              },
              icon: const Icon(Icons.call, size: 20),
              label: const Text('Call Now'),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF3b82f6),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLoading() {
    return GradientBackground(
      child: Scaffold(
        backgroundColor: Colors.transparent,
        appBar: AppBar(
          title: const Text('Property Details'),
          backgroundColor: Colors.transparent,
          elevation: 0,
        ),
        body: const Center(
          child: CircularProgressIndicator(color: Colors.white),
        ),
      ),
    );
  }

  Widget _buildGalleryCarousel(List<String> images) {
    return Column(
      children: [
        ClipRRect(
          borderRadius: BorderRadius.circular(16),
          child: SizedBox(
            height: 260,
            width: double.infinity,
            child: Stack(
              children: [
                PageView.builder(
                  itemCount: images.length,
                  onPageChanged: (i) => setState(() => _galleryIndex = i),
                  itemBuilder: (_, i) => GestureDetector(
                    onTap: () => _openFullScreen(i),
                    child: Image.network(
                      images[i],
                      height: 260,
                      width: double.infinity,
                      fit: BoxFit.cover,
                      loadingBuilder: (_, child, progress) {
                        if (progress == null) return child;
                        return Container(
                          height: 260,
                          color: Colors.white10,
                          child: Center(
                            child: CircularProgressIndicator(
                              value: progress.expectedTotalBytes != null
                                  ? progress.cumulativeBytesLoaded /
                                        progress.expectedTotalBytes!
                                  : null,
                              color: Colors.white54,
                              strokeWidth: 2,
                            ),
                          ),
                        );
                      },
                      errorBuilder: (_, _, _) => Container(
                        height: 260,
                        decoration: BoxDecoration(
                          color: Colors.white10,
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: const Icon(
                          Icons.broken_image_rounded,
                          size: 64,
                          color: Colors.white38,
                        ),
                      ),
                    ),
                  ),
                ),

                // Counter badge
                if (images.length > 1)
                  Positioned(
                    top: 12,
                    right: 12,
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 10,
                        vertical: 4,
                      ),
                      decoration: BoxDecoration(
                        color: Colors.black54,
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        '${_galleryIndex + 1}/${images.length}',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ),

                // Full-screen hint
                Positioned(
                  bottom: 12,
                  left: 0,
                  right: 0,
                  child: Center(
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 6,
                      ),
                      decoration: BoxDecoration(
                        color: Colors.black45,
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            Icons.fullscreen,
                            color: Colors.white70,
                            size: 14,
                          ),
                          SizedBox(width: 4),
                          Text(
                            'Tap for full screen',
                            style: TextStyle(
                              color: Colors.white70,
                              fontSize: 11,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 8),
        // Dot indicators
        if (images.length > 1)
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: List.generate(
              images.length,
              (i) => AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                margin: const EdgeInsets.symmetric(horizontal: 3),
                width: _galleryIndex == i ? 20 : 8,
                height: 8,
                decoration: BoxDecoration(
                  color: _galleryIndex == i
                      ? const Color(0xFFFFD700)
                      : Colors.white38,
                  borderRadius: BorderRadius.circular(4),
                ),
              ),
            ),
          ),
        // View Full Gallery button
        if (images.length > 1)
          Padding(
            padding: const EdgeInsets.only(top: 12),
            child: Center(
              child: TextButton.icon(
                onPressed: () {
                  GoRouter.of(context).push(
                    '/property-gallery/${widget.propertyId}',
                    extra: {'title': _displayTitle},
                  );
                },
                icon: const Icon(Icons.photo_library_outlined, size: 18),
                label: Text(
                  'View Full Gallery',
                  style: TextStyle(
                    color: AppTheme.primaryColor,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ),
          ),
      ],
    );
  }

  Widget _buildImageTypeGrid(List<String> images) {
    return GlassCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Photos',
            style: TextStyle(
              color: Colors.white,
              fontSize: 16,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 10),
          SizedBox(
            height: 72,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              itemCount: images.length,
              separatorBuilder: (_, _) => const SizedBox(width: 8),
              itemBuilder: (_, i) {
                final isSelected = i == _galleryIndex;
                return GestureDetector(
                  onTap: () => _openFullScreen(i),
                  child: Container(
                    width: 72,
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(
                        color: isSelected
                            ? const Color(0xFFFFD700)
                            : Colors.white24,
                        width: isSelected ? 2 : 1,
                      ),
                    ),
                    clipBehavior: Clip.antiAlias,
                    child: Image.network(
                      images[i],
                      fit: BoxFit.cover,
                      errorBuilder: (_, _, _) => Container(
                        color: Colors.white10,
                        child: const Icon(
                          Icons.broken_image,
                          color: Colors.white38,
                          size: 24,
                        ),
                      ),
                    ),
                  ),
                );
              },
            ),
          ),
        ],
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
          Text(
            label,
            style: const TextStyle(color: Colors.white70, fontSize: 12),
          ),
        ],
      ),
    );
  }
}

/// Full-screen image viewer with pinch-zoom and thumbnail filmstrip
class _PropertyImageViewer extends StatefulWidget {
  final List<String> images;
  final int initialIndex;
  final String title;

  const _PropertyImageViewer({
    required this.images,
    required this.initialIndex,
    required this.title,
  });

  @override
  State<_PropertyImageViewer> createState() => _PropertyImageViewerState();
}

class _PropertyImageViewerState extends State<_PropertyImageViewer> {
  late int _selectedIndex;

  @override
  void initState() {
    super.initState();
    _selectedIndex = widget.initialIndex;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        title: Text(widget.title),
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
        actions: [
          Center(
            child: Padding(
              padding: const EdgeInsets.only(right: 16),
              child: Text(
                '${_selectedIndex + 1}/${widget.images.length}',
                style: const TextStyle(fontSize: 14, color: Colors.white70),
              ),
            ),
          ),
        ],
      ),
      body: Column(
        children: [
          Expanded(
            child: PageView.builder(
              itemCount: widget.images.length,
              onPageChanged: (i) => setState(() => _selectedIndex = i),
              itemBuilder: (_, i) => InteractiveViewer(
                maxScale: 4,
                child: Center(
                  child: Image.network(
                    widget.images[i],
                    fit: BoxFit.contain,
                    loadingBuilder: (_, child, progress) {
                      if (progress == null) return child;
                      return Center(
                        child: CircularProgressIndicator(
                          value: progress.expectedTotalBytes != null
                              ? progress.cumulativeBytesLoaded /
                                    progress.expectedTotalBytes!
                              : null,
                          color: Colors.white,
                        ),
                      );
                    },
                    errorBuilder: (_, _, _) => const Center(
                      child: Icon(
                        Icons.broken_image_rounded,
                        size: 64,
                        color: Colors.white38,
                      ),
                    ),
                  ),
                ),
              ),
            ),
          ),
          Container(
            height: 100,
            padding: const EdgeInsets.symmetric(vertical: 8),
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 12),
              itemCount: widget.images.length,
              separatorBuilder: (_, _) => const SizedBox(width: 8),
              itemBuilder: (_, i) {
                final isSelected = i == _selectedIndex;
                return GestureDetector(
                  onTap: () => setState(() => _selectedIndex = i),
                  child: Container(
                    width: 72,
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(
                        color: isSelected ? Colors.white : Colors.transparent,
                        width: 2,
                      ),
                    ),
                    clipBehavior: Clip.antiAlias,
                    child: Image.network(
                      widget.images[i],
                      fit: BoxFit.cover,
                      errorBuilder: (_, _, _) => Container(
                        color: Colors.grey.shade800,
                        child: const Icon(
                          Icons.broken_image,
                          color: Colors.white38,
                        ),
                      ),
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}

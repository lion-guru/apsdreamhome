import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/providers/auth_provider.dart';
import '../../widgets/app_widgets.dart';
import '../../../data/services/property_listing_service.dart';

/// Detail screen for a direct-from-owner verified listing.
/// Seller's contact is shared only as a masked version; the buyer expresses
/// interest via an inquiry, which pings the seller's account.
class OwnerPropertyDetailPage extends ConsumerStatefulWidget {
  final int listingId;

  const OwnerPropertyDetailPage({super.key, required this.listingId});

  @override
  ConsumerState<OwnerPropertyDetailPage> createState() =>
      _OwnerPropertyDetailPageState();
}

class _OwnerPropertyDetailPageState extends ConsumerState<OwnerPropertyDetailPage> {
  PropertyListing? _listing;
  bool _loading = true;
  bool _submitting = false;
  final _formKey = GlobalKey<FormState>();
  final _phoneController = TextEditingController();
  final _messageController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _phoneController.dispose();
    _messageController.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    final service = ref.read(propertyListingServiceProvider);
    final l = await service.getOwnerListing(widget.listingId);
    if (mounted) {
      final user = ref.read(authProvider);
      setState(() {
        _listing = l;
        _loading = false;
      });
      _messageController.text =
          'Namaste! I am interested in your listing "${l?.title ?? ''}". Please share full details.';
      _phoneController.text = user?.phone ?? '';
    }
  }

  Future<void> _submitInquiry() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _submitting = true);
    try {
      final user = ref.read(authProvider);
      final service = ref.read(propertyListingServiceProvider);
      final res = await service.submitPropertyInquiry(
        propertyId: widget.listingId,
        listingType: 'user',
        listingId: widget.listingId,
        name: user?.name ?? 'Interested Buyer',
        phone: _phoneController.text.trim(),
        message: _messageController.text.trim(),
      );
      if (!mounted) return;
      final ok = res['success'] == true;
      setState(() => _submitting = false);
      AppWidgets.showSuccessSnackBar(
        context,
        ok
            ? 'Inquiry sent! The seller will get in touch with you soon.'
            : ((res['error'] ?? 'Could not send inquiry') as String),
      );
      if (ok && mounted) context.pop();
    } catch (e) {
      if (!mounted) return;
      setState(() => _submitting = false);
      AppWidgets.showErrorSnackBar(context, 'Inquiry failed. Please try again.');
    }
  }

  @override
  Widget build(BuildContext context) {
    final listing = _listing;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Owner Listing'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : listing == null
              ? _buildNotFound(context)
              : ListView(
                  padding: const EdgeInsets.all(16),
                  children: [
                    _buildHero(listing),
                    const SizedBox(height: 16),
                    _buildHeadlinePrice(listing),
                    const SizedBox(height: 16),
                    _buildSpecGrid(listing),
                    const SizedBox(height: 16),
                    if (listing.description.isNotEmpty) ...[
                      Text(
                        'About this property',
                        style: Theme.of(context)
                            .textTheme
                            .titleMedium
                            ?.copyWith(fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        listing.description,
                        style: Theme.of(context).textTheme.bodyMedium,
                      ),
                      const SizedBox(height: 16),
                    ],
                    _buildSellerCallout(listing),
                    const SizedBox(height: 20),
                    _buildInquiryForm(listing),
                    const SizedBox(height: 24),
                  ],
                ),
    );
  }

  Widget _buildNotFound(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.home_work_outlined, size: 72, color: Colors.grey),
            const SizedBox(height: 16),
            const Text(
              'Listing not available',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
            ),
            const SizedBox(height: 8),
            const Text(
              'It may be pending verification or already sold.',
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 20),
            ElevatedButton(
              onPressed: () => context.pop(),
              child: const Text('Back to listings'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildHero(PropertyListing listing) {
    return Container(
      height: 190,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(16),
        gradient: const LinearGradient(
          colors: [Color(0xFF0B194C), Color(0xFF1B3A8B)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: Stack(
        children: [
          Positioned.fill(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.villa_outlined, color: Color(0xFFE5A93C), size: 56),
                  const SizedBox(height: 10),
                  Text(
                    listing.type.toUpperCase(),
                    style: const TextStyle(
                      color: Color(0xFFE5A93C),
                      fontWeight: FontWeight.w700,
                      letterSpacing: 2,
                    ),
                  ),
                  Text(
                    (listing.listingType ?? 'sell').toUpperCase(),
                    style: const TextStyle(color: Colors.white70, fontSize: 13),
                  ),
                ],
              ),
            ),
          ),
          Positioned(
            top: 12,
            right: 12,
            child: Row(
              children: [
                if (listing.isUrgent)
                  _miniBadge('URGENT', const Color(0xFFE11D48)),
                const SizedBox(width: 6),
                if (listing.isFeatured)
                  _miniBadge('FEATURED', const Color(0xFF16A34A)),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _miniBadge(String text, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        text,
        style: const TextStyle(
          color: Colors.white,
          fontSize: 11,
          fontWeight: FontWeight.w800,
        ),
      ),
    );
  }

  Widget _buildHeadlinePrice(PropertyListing listing) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                listing.title,
                style: const TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF0B194C),
                ),
              ),
              const SizedBox(height: 6),
              Row(
                children: [
                  const Icon(Icons.location_on_outlined,
                      size: 16, color: Colors.grey),
                  const SizedBox(width: 4),
                  Expanded(
                    child: Text(
                      listing.location.isNotEmpty ? listing.location : 'Gorakhpur',
                      style: const TextStyle(color: Colors.grey),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
        const SizedBox(width: 12),
        Column(
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            Text(
              '₹ ${listing.formattedPrice}',
              style: const TextStyle(
                fontSize: 22,
                fontWeight: FontWeight.w800,
                color: Color(0xFF0B194C),
              ),
            ),
            const Text(
              'Owner price',
              style: TextStyle(fontSize: 11, color: Colors.grey),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildSpecGrid(PropertyListing listing) {
    final specs = <Map<String, dynamic>>[
      {'icon': Icons.straighten_outlined, 'label': 'Area', 'value': listing.formattedArea},
      {'icon': Icons.bed_outlined, 'label': 'Bedrooms', 'value': listing.bedrooms != null && listing.bedrooms! > 0 ? '${listing.bedrooms}' : '-'},
      {'icon': Icons.bathtub_outlined, 'label': 'Bathrooms', 'value': listing.bathrooms != null && listing.bathrooms! > 0 ? '${listing.bathrooms}' : '-'},
      {'icon': Icons.chair_outlined, 'label': 'Furnished', 'value': listing.furnished ?? 'Unfurnished'},
    ];

    return Wrap(
      spacing: 10,
      runSpacing: 10,
      children: specs.map((s) {
        return Container(
          width: (MediaQuery.of(context).size.width - 52) / 2,
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: Colors.grey.shade200),
          ),
          child: Row(
            children: [
              Icon(s['icon'] as IconData, color: const Color(0xFF0B194C), size: 22),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      s['label'] as String,
                      style: const TextStyle(fontSize: 11, color: Colors.grey),
                    ),
                    Text(
                      s['value']?.toString() ?? '-',
                      style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13),
                    ),
                  ],
                ),
              ),
            ],
          ),
        );
      }).toList(),
    );
  }

  Widget _buildSellerCallout(PropertyListing listing) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: const Color(0xFFFFFBEB),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFFDE68A)),
      ),
      child: Row(
        children: [
          const Icon(Icons.person_outline, color: Color(0xFFB45309), size: 28),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Direct from owner',
                  style: TextStyle(fontWeight: FontWeight.w700, color: Color(0xFFB45309)),
                ),
                Text(
                  listing.maskedPhone != null
                      ? 'Seller phone: ${listing.maskedPhone}. Contact unlocked after inquiry.'
                      : 'Contact unlocked after inquiry.',
                  style: const TextStyle(fontSize: 12, color: Colors.grey),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInquiryForm(PropertyListing listing) {
    return Form(
      key: _formKey,
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: Colors.grey.shade200),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Send Inquiry to Seller',
              style: TextStyle(fontSize: 17, fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 4),
            const Text(
              'The seller gets a push notification and will reach out to you.',
              style: TextStyle(fontSize: 12, color: Colors.grey),
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _phoneController,
              keyboardType: TextInputType.phone,
              decoration: InputDecoration(
                labelText: 'Your phone',
                prefixIcon: const Icon(Icons.phone_outlined),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
              validator: (v) {
                final digits = (v ?? '').replaceAll(RegExp(r'\D'), '');
                if (digits.length < 10) return 'Enter a valid phone number';
                return null;
              },
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _messageController,
              minLines: 3,
              maxLines: 5,
              decoration: InputDecoration(
                labelText: 'Your message',
                prefixIcon: const Icon(Icons.message_outlined),
                alignLabelWithHint: true,
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
              validator: (v) => (v ?? '').trim().isEmpty ? 'Write a short message' : null,
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: _submitting ? null : _submitInquiry,
                icon: _submitting
                    ? const SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : const Icon(Icons.send_outlined),
                label: Text(_submitting ? 'Sending...' : 'Send Inquiry'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/theme/app_theme.dart';

final associatePostersProvider = FutureProvider<Map<String, dynamic>>((ref) async {
  final api = ApiService();
  AppConstants.initBaseUrl();
  try {
    final res = await api.get('/api/v2/mobile/associate/marketing-posters');
    if (res['success'] == true) {
      return res['data'] as Map<String, dynamic>;
    }
  } catch (e) {
    debugPrint('Error loading associate posters: $e');
  }
  return {
    'associate': {'name': 'Authorized Associate', 'phone': '+91 9450000000', 'referral_code': 'APS001'},
    'posters': []
  };
});

class AssociatePostersPage extends ConsumerStatefulWidget {
  const AssociatePostersPage({super.key});

  @override
  ConsumerState<AssociatePostersPage> createState() => _AssociatePostersPageState();
}

class _AssociatePostersPageState extends ConsumerState<AssociatePostersPage> {
  String _selectedCategory = 'All';

  Future<void> _sharePoster(String caption) async {
    final uri = Uri.parse("https://wa.me/?text=${Uri.encodeComponent(caption)}");
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }

  @override
  Widget build(BuildContext context) {
    final postersAsync = ref.watch(associatePostersProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F0),
      appBar: AppBar(
        title: const Text('Auto-Branded WhatsApp Posters', style: TextStyle(fontWeight: FontWeight.w700, color: Color(0xFF0B194C))),
        backgroundColor: Colors.white,
        elevation: 0.5,
        iconTheme: const IconThemeData(color: Color(0xFF0B194C)),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.invalidate(associatePostersProvider),
          ),
        ],
      ),
      body: postersAsync.when(
        data: (data) => _buildBody(context, data),
        loading: () => const Center(child: CircularProgressIndicator(color: AppTheme.primaryColor)),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }

  Widget _buildBody(BuildContext context, Map<String, dynamic> data) {
    final associate = (data['associate'] as Map<String, dynamic>?) ?? {};
    final posters = (data['posters'] as List<dynamic>?) ?? [];
    final name = associate['name'] ?? 'Associate';
    final phone = associate['phone'] ?? '';

    final categories = ['All', 'Township Launch', 'Site Visit Offer', 'Green Energy', 'Festive Special'];
    final filtered = _selectedCategory == 'All'
        ? posters
        : posters.where((p) => (p as Map<String, dynamic>)['category'] == _selectedCategory).toList();

    return Column(
      children: [
        // Top Filter Bar
        Container(
          color: Colors.white,
          height: 52,
          child: ListView.builder(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            itemCount: categories.length,
            itemBuilder: (ctx, i) {
              final cat = categories[i];
              final isSel = cat == _selectedCategory;
              return Padding(
                padding: const EdgeInsets.only(right: 8),
                child: FilterChip(
                  label: Text(cat),
                  selected: isSel,
                  selectedColor: const Color(0xFF0B194C),
                  backgroundColor: const Color(0xFFFAF7F0),
                  labelStyle: TextStyle(
                    color: isSel ? Colors.white : const Color(0xFF0B194C),
                    fontWeight: isSel ? FontWeight.w700 : FontWeight.w500,
                    fontSize: 12,
                  ),
                  onSelected: (val) {
                    setState(() => _selectedCategory = cat);
                  },
                ),
              );
            },
          ),
        ),

        // Posters Feed
        Expanded(
          child: ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: filtered.length,
            itemBuilder: (ctx, i) {
              final p = filtered[i] as Map<String, dynamic>;
              return Container(
                margin: const EdgeInsets.only(bottom: 20),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 10, offset: const Offset(0, 4)),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Image with Category Badge & Auto-branding overlay
                    Stack(
                      children: [
                        ClipRRect(
                          borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                          child: Image.network(
                            p['image_url']?.toString() ?? '',
                            height: 200,
                            width: double.infinity,
                            fit: BoxFit.cover,
                            errorBuilder: (_, __, ___) => Container(
                              height: 200,
                              color: const Color(0xFF0B194C),
                              child: const Center(child: Icon(Icons.apartment, size: 60, color: Color(0xFFD4AF37))),
                            ),
                          ),
                        ),
                        Positioned(
                          top: 12,
                          left: 12,
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(color: const Color(0xFF0B194C), borderRadius: BorderRadius.circular(12)),
                            child: Text(p['badge']?.toString() ?? '', style: const TextStyle(color: Color(0xFFD4AF37), fontWeight: FontWeight.w800, fontSize: 11)),
                          ),
                        ),
                        // Pre-stamped Associate Badge
                        Positioned(
                          bottom: 10,
                          right: 10,
                          left: 10,
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                            decoration: BoxDecoration(
                              color: Colors.black.withValues(alpha: 0.75),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Row(
                              children: [
                                const Icon(Icons.verified, color: Color(0xFFD4AF37), size: 16),
                                const SizedBox(width: 6),
                                Expanded(
                                  child: Text(
                                    'Contact: $name • $phone',
                                    style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 12),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),

                    // Details & Caption
                    Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(p['title']?.toString() ?? '', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: Color(0xFF0B194C))),
                          const SizedBox(height: 4),
                          Text(p['headline']?.toString() ?? '', style: TextStyle(color: Colors.grey.shade700, fontSize: 13, fontWeight: FontWeight.w500)),
                          const SizedBox(height: 16),
                          SizedBox(
                            width: double.infinity,
                            child: ElevatedButton.icon(
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFF25D366),
                                foregroundColor: Colors.white,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                padding: const EdgeInsets.symmetric(vertical: 12),
                              ),
                              icon: const Icon(Icons.share),
                              label: const Text('Share on WhatsApp with Client', style: TextStyle(fontWeight: FontWeight.w700)),
                              onPressed: () => _sharePoster(p['caption']?.toString() ?? ''),
                            ),
                          )
                        ],
                      ),
                    )
                  ],
                ),
              );
            },
          ),
        ),
      ],
    );
  }
}

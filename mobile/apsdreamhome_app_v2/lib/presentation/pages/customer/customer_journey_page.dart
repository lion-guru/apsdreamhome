import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

final customerJourneyProvider = FutureProvider.family<Map<String, dynamic>, int>((ref, bookingId) async {
  final api = ApiService();
  AppConstants.initBaseUrl();
  try {
    final res = await api.get('/api/v2/mobile/customer-journey/$bookingId');
    if (res['success'] == true) {
      return res['data'] as Map<String, dynamic>;
    }
  } catch (e) {
    debugPrint('Error loading customer journey: $e');
  }
  return {
    'overall_progress_pct': 58,
    'current_stage_number': 4,
    'stages': []
  };
});

class CustomerJourneyPage extends ConsumerWidget {
  final int bookingId;
  const CustomerJourneyPage({super.key, required this.bookingId});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final journeyAsync = ref.watch(customerJourneyProvider(bookingId));

    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F0),
      appBar: AppBar(
        title: const Text('Customer Milestone Journey', style: TextStyle(fontWeight: FontWeight.w700, color: Color(0xFF0B194C))),
        backgroundColor: Colors.white,
        elevation: 0.5,
        iconTheme: const IconThemeData(color: Color(0xFF0B194C)),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.invalidate(customerJourneyProvider(bookingId)),
          ),
        ],
      ),
      body: journeyAsync.when(
        data: (data) => _buildContent(context, data),
        loading: () => const Center(child: CircularProgressIndicator(color: AppTheme.primaryColor)),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }

  Widget _buildContent(BuildContext context, Map<String, dynamic> data) {
    final booking = data['booking'] as Map<String, dynamic>? ?? {};
    final stages = (data['stages'] as List<dynamic>?) ?? [];
    final progressPct = (data['overall_progress_pct'] as num?)?.toDouble() ?? 58.0;

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Overall Milestone Banner
        GlassCard(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text('Plot #${booking['plot_number'] ?? '12'} (${booking['colony_name'] ?? 'Ganga Township'})', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: Color(0xFF0B194C))),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(color: const Color(0xFFD4AF37), borderRadius: BorderRadius.circular(12)),
                    child: Text('${progressPct.toStringAsFixed(0)}% Done', style: const TextStyle(fontWeight: FontWeight.w800, color: Color(0xFF0B194C), fontSize: 11)),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              LinearProgressIndicator(
                value: progressPct / 100,
                backgroundColor: Colors.grey.shade200,
                valueColor: const AlwaysStoppedAnimation<Color>(Color(0xFF0B194C)),
                minHeight: 8,
                borderRadius: BorderRadius.circular(4),
              ),
              const SizedBox(height: 8),
              const Text('From token booking to official possession & dakhil kharij mutation.', style: TextStyle(color: Colors.grey, fontSize: 12)),
            ],
          ),
        ),
        const SizedBox(height: 20),

        const Text('6-Stage Progress Milestones', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 16, color: Color(0xFF0B194C))),
        const SizedBox(height: 12),

        ...stages.asMap().entries.map((entry) {
          final idx = entry.key;
          final s = entry.value as Map<String, dynamic>;
          final status = s['status'] as String? ?? 'upcoming';
          final isCompleted = status == 'completed';
          final isInProgress = status == 'in_progress';
          final isLast = idx == stages.length - 1;

          return Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Stepper Line & Dot
              Column(
                children: [
                  Container(
                    width: 32,
                    height: 32,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: isCompleted
                          ? Colors.green.shade600
                          : (isInProgress ? const Color(0xFFD4AF37) : Colors.grey.shade300),
                    ),
                    child: Center(
                      child: isCompleted
                          ? const Icon(Icons.check, color: Colors.white, size: 18)
                          : Text('${s['stage_number'] ?? idx + 1}', style: TextStyle(fontWeight: FontWeight.w800, color: isInProgress ? const Color(0xFF0B194C) : Colors.grey.shade600, fontSize: 13)),
                    ),
                  ),
                  if (!isLast)
                    Container(
                      width: 2,
                      height: 75,
                      color: isCompleted ? Colors.green.shade600 : Colors.grey.shade300,
                    )
                ],
              ),
              const SizedBox(width: 14),

              // Content Card
              Expanded(
                child: Container(
                  margin: const EdgeInsets.only(bottom: 16),
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                      color: isInProgress ? const Color(0xFFD4AF37) : Colors.grey.shade200,
                      width: isInProgress ? 1.5 : 1,
                    ),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Text(
                              s['name']?.toString() ?? '',
                              style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: Color(0xFF0B194C)),
                            ),
                          ),
                          if (isCompleted)
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(color: Colors.green.shade50, borderRadius: BorderRadius.circular(4)),
                              child: Text('Completed', style: TextStyle(color: Colors.green.shade700, fontSize: 10, fontWeight: FontWeight.w700)),
                            )
                          else if (isInProgress)
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(color: const Color(0xFFD4AF37).withValues(alpha: 0.2), borderRadius: BorderRadius.circular(4)),
                              child: const Text('In Progress', style: TextStyle(color: Color(0xFF8B6B10), fontSize: 10, fontWeight: FontWeight.w700)),
                            ),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Text(s['description']?.toString() ?? '', style: TextStyle(color: Colors.grey.shade600, fontSize: 12)),
                      if (s['completed_on'] != null) ...[
                        const SizedBox(height: 6),
                        Text('Date: ${s['completed_on']}', style: TextStyle(color: Colors.grey.shade500, fontSize: 11)),
                      ],
                      if (s['doc_url'] != null) ...[
                        const SizedBox(height: 8),
                        InkWell(
                          onTap: () async {
                            final uri = Uri.parse(AppConstants.baseUrl.replaceAll('/api/v2/mobile/', '') + (s['doc_url']?.toString() ?? ''));
                            if (await canLaunchUrl(uri)) await launchUrl(uri, mode: LaunchMode.externalApplication);
                          },
                          child: const Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.download, size: 14, color: Color(0xFF0B194C)),
                              SizedBox(width: 4),
                              Text('View Stage Document', style: TextStyle(color: Color(0xFF0B194C), fontSize: 12, fontWeight: FontWeight.w700, decoration: TextDecoration.underline)),
                            ],
                          ),
                        )
                      ]
                    ],
                  ),
                ),
              )
            ],
          );
        }),
      ],
    );
  }
}

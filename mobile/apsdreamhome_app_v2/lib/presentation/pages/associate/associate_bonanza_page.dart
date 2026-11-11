import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

final bonanzaProvider = FutureProvider<Map<String, dynamic>>((ref) async {
  final api = ApiService();
  AppConstants.initBaseUrl();
  try {
    final res = await api.get('/api/v2/mobile/associate/bonanza-races');
    if (res['success'] == true) {
      return res['data'] as Map<String, dynamic>;
    }
  } catch (e) {
    debugPrint('Error loading bonanza races: $e');
  }
  return {
    'races': [],
    'leaderboard': []
  };
});

class AssociateBonanzaPage extends ConsumerWidget {
  const AssociateBonanzaPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final bonanzaAsync = ref.watch(bonanzaProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F0),
      appBar: AppBar(
        title: const Text('Bonanza Contests & Leaderboard', style: TextStyle(fontWeight: FontWeight.w700, color: Color(0xFF0B194C))),
        backgroundColor: Colors.white,
        elevation: 0.5,
        iconTheme: const IconThemeData(color: Color(0xFF0B194C)),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.invalidate(bonanzaProvider),
          ),
        ],
      ),
      body: bonanzaAsync.when(
        data: (data) => _buildBody(context, data),
        loading: () => const Center(child: CircularProgressIndicator(color: AppTheme.primaryColor)),
        error: (e, _) => Center(child: Text('Error: $e')),
      ),
    );
  }

  Widget _buildBody(BuildContext context, Map<String, dynamic> data) {
    final races = (data['races'] as List<dynamic>?) ?? [];
    final leaderboard = (data['leaderboard'] as List<dynamic>?) ?? [];

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Header Banner
        Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            gradient: const LinearGradient(colors: [Color(0xFF0B194C), Color(0xFF1E3A8A)]),
            borderRadius: BorderRadius.circular(16),
          ),
          child: const Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Icon(Icons.emoji_events, color: Color(0xFFD4AF37), size: 28),
                  SizedBox(width: 10),
                  Text('Active Bonanza Races 2026', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 17)),
                ],
              ),
              SizedBox(height: 8),
              Text('Achieve your plot booking targets and win luxury national & international tours, bikes, and cash awards.', style: TextStyle(color: Colors.white70, fontSize: 12)),
            ],
          ),
        ),
        const SizedBox(height: 20),

        // Active Races List
        const Text('My Bonanza Target Progress', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 16, color: Color(0xFF0B194C))),
        const SizedBox(height: 12),
        ...races.map((item) {
          final r = item as Map<String, dynamic>;
          final target = (r['target_bookings'] as num?)?.toInt() ?? 10;
          final current = (r['my_booked_count'] as num?)?.toInt() ?? 3;
          final progress = (current / (target > 0 ? target : 1)).clamp(0.0, 1.0);
          final daysLeft = r['days_remaining'] ?? 30;

          return Container(
            margin: const EdgeInsets.only(bottom: 14),
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(14), border: Border.all(color: Colors.grey.shade200)),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      child: Text(
                        r['race_name']?.toString() ?? 'Bonanza Contest',
                        style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15, color: Color(0xFF0B194C)),
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(color: Colors.red.shade50, borderRadius: BorderRadius.circular(6)),
                      child: Text('$daysLeft Days Left', style: TextStyle(color: Colors.red.shade700, fontSize: 11, fontWeight: FontWeight.w700)),
                    )
                  ],
                ),
                const SizedBox(height: 6),
                Text('🎁 Prize: ${r['prize_text'] ?? ''}', style: TextStyle(color: Colors.grey.shade700, fontSize: 12, fontWeight: FontWeight.w600)),
                const SizedBox(height: 14),
                LinearProgressIndicator(
                  value: progress,
                  backgroundColor: Colors.grey.shade200,
                  valueColor: const AlwaysStoppedAnimation<Color>(Color(0xFFD4AF37)),
                  minHeight: 10,
                  borderRadius: BorderRadius.circular(5),
                ),
                const SizedBox(height: 8),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text('Achieved: $current / $target Plots', style: const TextStyle(fontWeight: FontWeight.w700, color: Color(0xFF0B194C), fontSize: 12)),
                    Text('${(progress * 100).toStringAsFixed(0)}% Completed', style: const TextStyle(fontWeight: FontWeight.w700, color: Colors.green, fontSize: 12)),
                  ],
                )
              ],
            ),
          );
        }),
        const SizedBox(height: 20),

        // Leaderboard
        const Text('🏆 Top 5 Associate Sales Leaderboard', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 16, color: Color(0xFF0B194C))),
        const SizedBox(height: 12),
        GlassCard(
          padding: const EdgeInsets.all(12),
          child: Column(
            children: leaderboard.map((item) {
              final lb = item as Map<String, dynamic>;
              final int rank = (lb['rank'] as num?)?.toInt() ?? 1;
              final bool isTop3 = rank <= 3;

              return Padding(
                padding: const EdgeInsets.symmetric(vertical: 8),
                child: Row(
                  children: [
                    CircleAvatar(
                      radius: 14,
                      backgroundColor: isTop3 ? const Color(0xFFD4AF37) : Colors.grey.shade300,
                      child: Text('$rank', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 12, color: isTop3 ? const Color(0xFF0B194C) : Colors.grey.shade700)),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(lb['name']?.toString() ?? '', style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: Color(0xFF0B194C))),
                          Text('City: ${lb['city'] ?? 'Gorakhpur'}', style: TextStyle(color: Colors.grey.shade500, fontSize: 11)),
                        ],
                      ),
                    ),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Text('${lb['plots_booked'] ?? 0} Plots', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14, color: Color(0xFF0B194C))),
                        Text('${lb['points'] ?? 0} pts', style: const TextStyle(color: Colors.green, fontSize: 11, fontWeight: FontWeight.w600)),
                      ],
                    )
                  ],
                ),
              );
            }).toList(),
          ),
        ),
      ],
    );
  }
}

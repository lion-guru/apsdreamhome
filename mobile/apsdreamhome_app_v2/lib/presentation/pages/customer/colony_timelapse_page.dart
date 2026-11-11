import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:apsdreamhome_app_v2/core/services/api_service.dart';

final colonyTimelapseProvider = FutureProvider.autoDispose.family<Map<String, dynamic>, int>((ref, colonyId) async {
  final api = ApiService();
  final res = await api.get('/api/v2/mobile/colony/$colonyId/timelapse');
  return res;
});

class ColonyTimelapsePage extends ConsumerStatefulWidget {
  final int colonyId;
  const ColonyTimelapsePage({super.key, this.colonyId = 1});

  @override
  ConsumerState<ColonyTimelapsePage> createState() => _ColonyTimelapsePageState();
}

class _ColonyTimelapsePageState extends ConsumerState<ColonyTimelapsePage> {
  int _selectedColonyId = 1;

  @override
  void initState() {
    super.initState();
    _selectedColonyId = widget.colonyId;
  }

  Future<void> _launchDroneVideo(String url) async {
    final uri = Uri.parse(url);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }

  @override
  Widget build(BuildContext context) {
    final timelapseAsync = ref.watch(colonyTimelapseProvider(_selectedColonyId));

    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F0),
      appBar: AppBar(
        title: const Text('Colony Drone & Site Timelapse', style: TextStyle(fontWeight: FontWeight.w700, color: Color(0xFF0B194C))),
        backgroundColor: Colors.white,
        elevation: 0.5,
        iconTheme: const IconThemeData(color: Color(0xFF0B194C)),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.invalidate(colonyTimelapseProvider(_selectedColonyId)),
          ),
        ],
      ),
      body: timelapseAsync.when(
        loading: () => const Center(child: CircularProgressIndicator(color: Color(0xFFD4AF37))),
        error: (err, _) => Center(child: Text('Error: $err')),
        data: (data) {
          final colony = (data['colony'] as Map<String, dynamic>?) ?? {};
          final overallProgress = (data['overall_progress'] as num?)?.toInt() ?? 80;
          final milestones = (data['milestones'] as List<dynamic>?) ?? [];

          return RefreshIndicator(
            color: const Color(0xFFD4AF37),
            onRefresh: () async => ref.invalidate(colonyTimelapseProvider(_selectedColonyId)),
            child: ListView(
              padding: const EdgeInsets.all(16),
              children: [
                // Colony Selector Pills
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: [
                      {'id': 1, 'name': 'Ayodhya Enclave'},
                      {'id': 2, 'name': 'Gorakhpur Smart City'},
                      {'id': 3, 'name': 'Gautam Buddha Heights'},
                      {'id': 4, 'name': 'Kushinagar Highway Greens'},
                    ].map((c) {
                      final isSelected = _selectedColonyId == c['id'];
                      return Padding(
                        padding: const EdgeInsets.only(right: 8),
                        child: ChoiceChip(
                          label: Text(c['name'] as String),
                          selected: isSelected,
                          selectedColor: const Color(0xFF0B194C),
                          backgroundColor: Colors.white,
                          labelStyle: TextStyle(
                            color: isSelected ? Colors.white : const Color(0xFF0B194C),
                            fontWeight: FontWeight.w700,
                            fontSize: 12,
                          ),
                          onSelected: (val) {
                            if (val) {
                              setState(() => _selectedColonyId = c['id'] as int);
                            }
                          },
                        ),
                      );
                    }).toList(),
                  ),
                ),
                const SizedBox(height: 16),

                // Overall Progress Hero Card
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [Color(0xFF0B194C), Color(0xFF1E3A8A)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(18),
                    boxShadow: [
                      BoxShadow(color: const Color(0xFF0B194C).withValues(alpha: 0.2), blurRadius: 10, offset: const Offset(0, 4)),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  colony['name']?.toString() ?? 'Ayodhya Enclave',
                                  style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 18),
                                ),
                                const SizedBox(height: 4),
                                Row(
                                  children: [
                                    const Icon(Icons.location_on, color: Color(0xFFD4AF37), size: 14),
                                    const SizedBox(width: 4),
                                    Text(
                                      colony['location']?.toString() ?? 'Gorakhpur, UP',
                                      style: const TextStyle(color: Colors.white70, fontSize: 12),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                          Stack(
                            alignment: Alignment.center,
                            children: [
                              SizedBox(
                                width: 56,
                                height: 56,
                                child: CircularProgressIndicator(
                                  value: overallProgress / 100.0,
                                  strokeWidth: 5,
                                  backgroundColor: Colors.white24,
                                  valueColor: const AlwaysStoppedAnimation<Color>(Color(0xFFD4AF37)),
                                ),
                              ),
                              Text(
                                '$overallProgress%',
                                style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 14),
                              ),
                            ],
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Row(
                          children: [
                            Icon(Icons.videocam, color: Color(0xFFD4AF37), size: 18),
                            SizedBox(width: 8),
                            Text('4K Drone Survey Verified by Site Engineer', style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600)),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 20),

                const Text('Construction Milestone Stages', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: Color(0xFF0B194C))),
                const SizedBox(height: 12),

                ...milestones.map((m) {
                  final item = m as Map<String, dynamic>;
                  final pct = (item['completion_pct'] as num?)?.toInt() ?? 0;
                  final isDone = pct >= 100;

                  return Container(
                    margin: const EdgeInsets.only(bottom: 14),
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: isDone ? Colors.green.shade200 : Colors.grey.shade200),
                      boxShadow: [
                        BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 6, offset: const Offset(0, 2)),
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Container(
                              padding: const EdgeInsets.all(8),
                              decoration: BoxDecoration(
                                color: isDone ? Colors.green.shade50 : const Color(0xFFFAF7F0),
                                shape: BoxShape.circle,
                              ),
                              child: Icon(
                                isDone ? Icons.check_circle : Icons.construction,
                                color: isDone ? Colors.green : const Color(0xFF0B194C),
                                size: 20,
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    item['title']?.toString() ?? '',
                                    style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: Color(0xFF0B194C)),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    item['description']?.toString() ?? '',
                                    style: TextStyle(fontSize: 12, color: Colors.grey.shade600, height: 1.3),
                                  ),
                                ],
                              ),
                            ),
                            Text(
                              '$pct%',
                              style: TextStyle(
                                fontWeight: FontWeight.w800,
                                fontSize: 13,
                                color: isDone ? Colors.green : const Color(0xFFD4AF37),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),
                        ClipRRect(
                          borderRadius: BorderRadius.circular(6),
                          child: LinearProgressIndicator(
                            value: pct / 100.0,
                            minHeight: 6,
                            backgroundColor: Colors.grey.shade100,
                            valueColor: AlwaysStoppedAnimation<Color>(isDone ? Colors.green : const Color(0xFFD4AF37)),
                          ),
                        ),
                        const SizedBox(height: 12),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              isDone ? 'Completed' : 'Target: ${item['target_date']?.toString() ?? 'Q2 2026'}',
                              style: TextStyle(fontSize: 11, color: isDone ? Colors.green : Colors.grey.shade500),
                            ),
                            if (item['drone_video_url'] != null)
                              InkWell(
                                onTap: () => _launchDroneVideo(item['drone_video_url']?.toString() ?? ''),
                                child: const Row(
                                  children: [
                                    Icon(Icons.play_circle_fill, color: Color(0xFF0B194C), size: 16),
                                    SizedBox(width: 4),
                                    Text('Drone Clip', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12, color: Color(0xFF0B194C))),
                                  ],
                                ),
                              ),
                          ],
                        ),
                      ],
                    ),
                  );
                }),
              ],
            ),
          );
        },
      ),
    );
  }
}

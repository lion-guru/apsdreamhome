import 'package:flutter/material.dart';
import 'dart:math' as math;

import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/theme/app_theme.dart';

class ColonyHealthPage extends StatefulWidget {
  const ColonyHealthPage({super.key});

  @override
  State<ColonyHealthPage> createState() => _ColonyHealthPageState();
}

class _ColonyHealthPageState extends State<ColonyHealthPage>
    with SingleTickerProviderStateMixin {
  bool _isLoading = true;
  String? _error;
  List<Map<String, dynamic>> _colonies = [];
  late AnimationController _animController;

  @override
  void initState() {
    super.initState();
    _animController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 800),
    )..forward();
    _fetchHealth();
  }

  @override
  void dispose() {
    _animController.dispose();
    super.dispose();
  }

  Future<void> _fetchHealth() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final api = ApiService();
      final response = await api.get(AppConstants.colonyHealthEndpoint);
      if (response['success'] == true) {
        final data = response['colonies'] ?? [];
        setState(() {
          _colonies = (data as List)
              .map((e) => Map<String, dynamic>.from(e as Map))
              .toList();
          _isLoading = false;
        });
      } else {
        setState(() {
          _error = 'Failed to load health data';
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  Color _gradeColor(String? grade) {
    switch (grade) {
      case 'A+':
      case 'A':
        return AppTheme.successColor;
      case 'B+':
      case 'B':
        return Colors.teal;
      case 'C+':
      case 'C':
        return AppTheme.warningColor;
      case 'D':
        return Colors.deepOrange;
      case 'F':
        return AppTheme.errorColor;
      default:
        return Colors.grey;
    }
  }

  Color _scoreColor(int score) {
    if (score >= 80) return AppTheme.successColor;
    if (score >= 60) return Colors.teal;
    if (score >= 40) return AppTheme.warningColor;
    return AppTheme.errorColor;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [Color(0xFF0D1B2A), Color(0xFF1B2838), Color(0xFF0D1B2A)],
          ),
        ),
        child: SafeArea(
          child: Column(
            children: [
              _buildAppBar(),
              Expanded(
                child: _isLoading
                    ? const Center(
                        child: CircularProgressIndicator(
                          color: AppTheme.accentColor,
                        ),
                      )
                    : _error != null
                    ? _buildError()
                    : _colonies.isEmpty
                    ? _buildEmpty()
                    : _buildContent(),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildAppBar() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      child: Row(
        children: [
          IconButton(
            onPressed: () => Navigator.of(context).pop(),
            icon: const Icon(
              Icons.arrow_back_ios,
              color: Colors.white,
              size: 20,
            ),
          ),
          const Expanded(
            child: Text(
              'Colony Health',
              style: TextStyle(
                color: Colors.white,
                fontSize: 20,
                fontWeight: FontWeight.bold,
              ),
            ),
          ),
          IconButton(
            onPressed: _fetchHealth,
            icon: const Icon(
              Icons.refresh,
              color: AppTheme.accentColor,
              size: 20,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildError() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(
              Icons.error_outline,
              color: AppTheme.errorColor,
              size: 48,
            ),
            const SizedBox(height: 16),
            Text(
              _error!,
              textAlign: TextAlign.center,
              style: const TextStyle(color: Colors.white70, fontSize: 14),
            ),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: _fetchHealth,
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.accentColor,
              ),
              child: const Text('Retry', style: TextStyle(color: Colors.black)),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmpty() {
    return const Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            Icons.health_and_safety_outlined,
            color: Colors.white24,
            size: 64,
          ),
          SizedBox(height: 16),
          Text(
            'No colony data available',
            style: TextStyle(color: Colors.white54, fontSize: 16),
          ),
        ],
      ),
    );
  }

  Widget _buildContent() {
    // Sort by score descending
    _colonies.sort(
      (a, b) => ((b['overall_score'] ?? 0) as num).compareTo(
        (a['overall_score'] ?? 0) as num,
      ),
    );

    final avgScore = _colonies.isEmpty
        ? 0
        : (_colonies
                  .map((c) => (c['overall_score'] ?? 0) as num)
                  .reduce((a, b) => a + b) ~/
              _colonies.length);

    return RefreshIndicator(
      onRefresh: _fetchHealth,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Summary card
          _buildSummaryCard(avgScore),
          const SizedBox(height: 20),

          // Section header
          const Text(
            'All Colonies',
            style: TextStyle(
              color: Colors.white,
              fontSize: 18,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 12),

          // Colony cards
          ...List.generate(_colonies.length, (i) {
            final colony = _colonies[i];
            return AnimatedBuilder(
              animation: _animController,
              builder: (context, child) {
                final delay = i * 0.1;
                final t = (_animController.value - delay).clamp(0.0, 1.0);
                final opacity = Curves.easeOut.transform(t);
                final slideY = (1 - t) * 20;
                return Opacity(
                  opacity: opacity,
                  child: Transform.translate(
                    offset: Offset(0, slideY),
                    child: child,
                  ),
                );
              },
              child: _buildColonyCard(colony),
            );
          }),
        ],
      ),
    );
  }

  Widget _buildSummaryCard(int avgScore) {
    final grade = _getGradeLabel(avgScore);
    final color = _scoreColor(avgScore);

    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [color.withValues(alpha: 0.2), color.withValues(alpha: 0.05)],
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Row(
        children: [
          // Score ring
          SizedBox(
            width: 80,
            height: 80,
            child: CustomPaint(
              painter: _ScoreRingPainter(
                score: avgScore / 100,
                color: color,
                strokeWidth: 8,
              ),
              child: Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      '$avgScore%',
                      style: TextStyle(
                        color: color,
                        fontSize: 22,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    Text(
                      grade,
                      style: TextStyle(
                        color: color.withValues(alpha: 0.7),
                        fontSize: 10,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
          const SizedBox(width: 20),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Portfolio Health',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  '${_colonies.length} colonies tracked',
                  style: const TextStyle(color: Colors.white54, fontSize: 13),
                ),
                const SizedBox(height: 8),
                // Grade legend
                Row(
                  children: [
                    _gradeLegend('A+', const Color(0xFF4CAF50)),
                    _gradeLegend('B+', Colors.teal),
                    _gradeLegend('C+', AppTheme.warningColor),
                    _gradeLegend('D', Colors.deepOrange),
                    _gradeLegend('F', AppTheme.errorColor),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _gradeLegend(String label, Color color) {
    return Container(
      margin: const EdgeInsets.only(right: 6),
      padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(4),
      ),
      child: Text(
        label,
        style: TextStyle(
          color: color,
          fontSize: 9,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }

  Widget _buildColonyCard(Map<String, dynamic> colony) {
    final name = (colony['name'] ?? 'Unknown').toString();
    final score = (colony['overall_score'] ?? 0) as int;
    final grade = (colony['grade'] ?? 'F').toString();
    final riskCount = (colony['risk_count'] ?? 0) as int;
    final topRisk = colony['top_risk'] as String?;
    final color = _scoreColor(score);

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: const Color(0xFF1A2332).withValues(alpha: 0.8),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: color.withValues(alpha: 0.2), width: 1),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            // Score circle
            SizedBox(
              width: 56,
              height: 56,
              child: CustomPaint(
                painter: _ScoreRingPainter(
                  score: score / 100,
                  color: color,
                  strokeWidth: 5,
                ),
                child: Center(
                  child: Text(
                    '$score',
                    style: TextStyle(
                      color: color,
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ),
            ),
            const SizedBox(width: 14),

            // Colony info
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          name,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 15,
                            fontWeight: FontWeight.w600,
                          ),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 3,
                        ),
                        decoration: BoxDecoration(
                          color: color.withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          grade,
                          style: TextStyle(
                            color: color,
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  if (riskCount > 0 && topRisk != null)
                    Row(
                      children: [
                        const Icon(
                          Icons.warning_amber_rounded,
                          size: 14,
                          color: AppTheme.warningColor,
                        ),
                        const SizedBox(width: 4),
                        Expanded(
                          child: Text(
                            '$riskCount risk${riskCount > 1 ? 's' : ''} — $topRisk',
                            style: const TextStyle(
                              color: AppTheme.warningColor,
                              fontSize: 11,
                            ),
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                    )
                  else
                    const Row(
                      children: [
                        Icon(
                          Icons.check_circle_outline,
                          size: 14,
                          color: AppTheme.successColor,
                        ),
                        SizedBox(width: 4),
                        Text(
                          'No risks detected',
                          style: TextStyle(
                            color: AppTheme.successColor,
                            fontSize: 11,
                          ),
                        ),
                      ],
                    ),
                ],
              ),
            ),

            // Arrow
            const Icon(Icons.chevron_right, color: Colors.white24, size: 20),
          ],
        ),
      ),
    );
  }

  String _getGradeLabel(int score) {
    if (score >= 90) return 'A+';
    if (score >= 80) return 'A';
    if (score >= 70) return 'B+';
    if (score >= 60) return 'B';
    if (score >= 50) return 'C+';
    if (score >= 40) return 'C';
    if (score >= 25) return 'D';
    return 'F';
  }
}

class _ScoreRingPainter extends CustomPainter {
  final double score;
  final Color color;
  final double strokeWidth;

  _ScoreRingPainter({
    required this.score,
    required this.color,
    this.strokeWidth = 6,
  });

  @override
  void paint(Canvas canvas, Size size) {
    final center = Offset(size.width / 2, size.height / 2);
    final radius = (math.min(size.width, size.height) / 2) - strokeWidth;

    // Background ring
    final bgPaint = Paint()
      ..color = Colors.white.withValues(alpha: 0.08)
      ..style = PaintingStyle.stroke
      ..strokeWidth = strokeWidth
      ..strokeCap = StrokeCap.round;

    canvas.drawCircle(center, radius, bgPaint);

    // Score arc
    final scorePaint = Paint()
      ..color = color
      ..style = PaintingStyle.stroke
      ..strokeWidth = strokeWidth
      ..strokeCap = StrokeCap.round;

    final sweepAngle = 2 * math.pi * score.clamp(0.0, 1.0);
    canvas.drawArc(
      Rect.fromCircle(center: center, radius: radius),
      -math.pi / 2,
      sweepAngle,
      false,
      scorePaint,
    );
  }

  @override
  bool shouldRepaint(covariant _ScoreRingPainter oldDelegate) {
    return oldDelegate.score != score || oldDelegate.color != color;
  }
}

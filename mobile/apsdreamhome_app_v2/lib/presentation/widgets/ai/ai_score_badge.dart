import 'package:flutter/material.dart';

enum AIScoreBadgeSize {
  small,
  medium,
  large,
}

Color _getScoreColor(double score) {
  if (score >= 80) return Colors.green;
  if (score >= 60) return Colors.lightGreen;
  if (score >= 40) return Colors.amber;
  if (score >= 20) return Colors.orange;
  return Colors.red;
}

Color _getLabelColor(double score) {
  if (score >= 80) return Colors.green.shade700;
  if (score >= 60) return Colors.lightGreen.shade700;
  if (score >= 40) return Colors.amber.shade800;
  if (score >= 20) return Colors.orange.shade700;
  return Colors.red.shade700;
}

String _getScoreLabel(double score) {
  if (score >= 80) return 'Hot';
  if (score >= 60) return 'Warm';
  if (score >= 40) return 'Lukewarm';
  if (score >= 20) return 'Cool';
  return 'Cold';
}

class AIScoreBadge extends StatelessWidget {
  final double? score;
  final bool showLabel;
  final AIScoreBadgeSize size;
  final Color? customColor;
  final VoidCallback? onTap;

  const AIScoreBadge({
    super.key,
    this.score,
    this.showLabel = false,
    this.size = AIScoreBadgeSize.medium,
    this.customColor,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    if (score == null) {
      return const SizedBox.shrink();
    }

    final color = _getScoreColor(score!);
    final label = _getScoreLabel(score!);

    final double borderRadius;

    switch (size) {
      case AIScoreBadgeSize.small:
        borderRadius = 8;
        break;
      case AIScoreBadgeSize.medium:
        borderRadius = 10;
        break;
      case AIScoreBadgeSize.large:
        borderRadius = 12;
        break;
    }

    Widget badge = Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(borderRadius),
        border: Border.all(color: color.withValues(alpha: 0.3), width: 1),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            Icons.auto_awesome,
            size: 10,
            color: _getLabelColor(score!),
          ),
          const SizedBox(width: 4),
          Text(
            '${score!.toStringAsFixed(0)}',
            style: TextStyle(
              fontSize: 10,
              fontWeight: FontWeight.w700,
              color: _getLabelColor(score!),
            ),
          ),
          if (showLabel) ...[
            const SizedBox(width: 2),
            Text(
              label,
              style: TextStyle(
                fontSize: 10,
                fontWeight: FontWeight.w500,
                color: _getLabelColor(score!),
              ),
            ),
          ],
        ],
      ),
    );

    if (onTap != null) {
      badge = InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(borderRadius),
        child: badge,
      );
    }

    return badge;
  }
}

class AIScoreBadgeLarge extends StatelessWidget {
  final double? score;
  final String? label;
  final VoidCallback? onTap;

  const AIScoreBadgeLarge({
    super.key,
    this.score,
    this.label,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    if (score == null) {
      return const SizedBox.shrink();
    }

    final color = _getScoreColor(score!);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      decoration: BoxDecoration(
        color: score! >= 80
            ? Colors.green.shade50
            : score! >= 60
                ? Colors.lightGreen.shade50
                : score! >= 40
                    ? Colors.amber.shade50
                    : score! >= 20
                        ? Colors.orange.shade50
                        : Colors.red.shade50,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: score! >= 80
              ? Colors.green.shade200
              : score! >= 60
                  ? Colors.lightGreen.shade200
                  : score! >= 40
                      ? Colors.amber.shade200
                      : score! >= 20
                          ? Colors.orange.shade200
                          : Colors.red.shade200,
          width: 1.5,
        ),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            Icons.auto_awesome,
            size: 16,
            color: _getLabelColor(score!),
          ),
          const SizedBox(width: 8),
          Text(
            '${score!.toStringAsFixed(1)}%',
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w700,
              color: _getLabelColor(score!),
            ),
          ),
          if (label != null) ...[
            const SizedBox(width: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(
                label!,
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w600,
                  color: _getLabelColor(score!),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class AIScoreDistributionChart extends StatelessWidget {
  final Map<String, int> distribution;

  const AIScoreDistributionChart({
    super.key,
    required this.distribution,
  });

  @override
  Widget build(BuildContext context) {
    final total = distribution.values.fold(0, (a, b) => a + b);
    if (total == 0) return const SizedBox.shrink();

    final buckets = <Map<String, dynamic>>[
      {'range': '80-100', 'label': 'Hot', 'color': Colors.green},
      {'range': '60-79', 'label': 'Warm', 'color': Colors.lightGreen},
      {'range': '40-59', 'label': 'Lukewarm', 'color': Colors.amber},
      {'range': '20-39', 'label': 'Cool', 'color': Colors.orange},
      {'range': '0-19', 'label': 'Cold', 'color': Colors.red},
    ];

    final totalCount = distribution.values.fold(0, (a, b) => a + b);
    if (totalCount == 0) return const SizedBox.shrink();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'AI Score Distribution',
          style: TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w600,
          ),
        ),
        const SizedBox(height: 12),
        ...buckets.map((bucket) {
          final range = bucket['range'] as String;
          final label = bucket['label'] as String;
          final color = bucket['color'] as Color;
          final count = distribution[range] ?? 0;
          final percentage = totalCount > 0 ? (count / totalCount * 100).round() : 0;

          return Padding(
            padding: const EdgeInsets.only(bottom: 8),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        Container(
                          width: 12,
                          height: 12,
                          decoration: BoxDecoration(
                            color: color,
                            borderRadius: BorderRadius.circular(2),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Text(
                          '${bucket['range']} - ${bucket['label']}',
                          style: const TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ),
                    Text(
                      '$count ($percentage%)',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade600,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                LinearProgressIndicator(
                  value: totalCount > 0 ? count / totalCount : 0,
                  backgroundColor: Colors.grey.shade200,
                  valueColor: AlwaysStoppedAnimation<Color>(color),
                  minHeight: 6,
                  borderRadius: BorderRadius.circular(3),
                ),
              ],
            ),
          );
        }).toList(),
      ],
    );
  }
}
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/services/api_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

/// Township Operations & Utilities — the mobile surface for the utility desks
/// that previously existed only on the web ERP (STP, water, power, security,
/// CCTV, waste, horticulture, lighting, rainwater harvesting, fire safety).
class TownshipOperationsPage extends StatefulWidget {
  const TownshipOperationsPage({super.key});

  @override
  State<TownshipOperationsPage> createState() =>
      _TownshipOperationsPageState();
}

class _TownshipOperationsPageState extends State<TownshipOperationsPage> {
  bool _loading = true;
  String? _error;
  List<Map<String, dynamic>> _desks = [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      AppConstants.initBaseUrl();
      final res = await ApiService().get('/api/v2/mobile/township/operations');
      if (res['success'] == true) {
        final desks = (res['desks'] ?? []) as List;
        setState(() {
          _desks = desks
              .map((e) => Map<String, dynamic>.from(e as Map))
              .toList();
          _loading = false;
        });
      } else {
        setState(() {
          _error = 'Could not load township operations.';
          _loading = false;
        });
      }
    } catch (e) {
      debugPrint('township operations load failed: $e');
      setState(() {
        _error = 'Network error. Please pull down to retry.';
        _loading = false;
      });
    }
  }

  IconData _iconFor(String? name) {
    switch (name) {
      case 'water_drop':
        return Icons.water_drop_outlined;
      case 'electrical_services':
        return Icons.electrical_services;
      case 'security':
        return Icons.security_outlined;
      case 'report':
        return Icons.report_outlined;
      case 'videocam':
        return Icons.videocam_outlined;
      case 'delete':
        return Icons.delete_outline;
      case 'park':
        return Icons.park_outlined;
      case 'light_mode':
        return Icons.light_mode_outlined;
      case 'water_damage':
        return Icons.water_damage_outlined;
      case 'local_fire_department':
        return Icons.local_fire_department_outlined;
      case 'water':
        return Icons.water;
      default:
        return Icons.insights_outlined;
    }
  }

  Color _tintFor(int count) {
    if (count > 0) return AppTheme.successColor;
    return AppTheme.goldColor;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.backgroundColor,
      appBar: AppBar(
        title: const Text(
          'Township Operations',
          style: TextStyle(
            fontWeight: FontWeight.w700,
            color: AppTheme.primaryColor,
          ),
        ),
        backgroundColor: Colors.white,
        elevation: 0.5,
        iconTheme: const IconThemeData(color: AppTheme.primaryColor),
      ),
      body: RefreshIndicator(
        onRefresh: _load,
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : _error != null
                ? ListView(
                    children: [
                      const SizedBox(height: 80),
                      Icon(Icons.cloud_off_outlined,
                          size: 56, color: AppTheme.primaryColor.withValues(alpha: 0.3)),
                      const SizedBox(height: 16),
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 32),
                        child: Text(
                          _error!,
                          textAlign: TextAlign.center,
                          style: const TextStyle(color: Colors.black54),
                        ),
                      ),
                    ],
                  )
                : _buildBody(),
      ),
    );
  }

  Widget _buildBody() {
    if (_desks.isEmpty) {
      return ListView(
        children: const [
          SizedBox(height: 80),
          Icon(Icons.domain_outlined,
              size: 56, color: Colors.black26),
          SizedBox(height: 16),
          Padding(
            padding: EdgeInsets.symmetric(horizontal: 32),
            child: Text(
              'No utility desks are configured yet.',
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.black54),
            ),
          ),
        ],
      );
    }

    final totalRecords =
        _desks.fold<int>(0, (sum, d) => sum + ((d['record_count'] ?? 0) as num).toInt());
    final liveDesks =
        _desks.where((d) => ((d['record_count'] ?? 0) as num) > 0).length;

    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
      children: [
        GlassCard(
          child: Row(
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
                  ),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: const Icon(Icons.verified_outlined,
                    color: Colors.white, size: 26),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Utility Desks',
                      style: TextStyle(
                        fontWeight: FontWeight.w800,
                        fontSize: 16,
                        color: AppTheme.primaryColor,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '$liveDesks of ${_desks.length} desks reporting · $totalRecords records',
                      style: const TextStyle(
                          color: Colors.black54, fontSize: 12.5),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),
        GridView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 2,
            mainAxisSpacing: 12,
            crossAxisSpacing: 12,
            childAspectRatio: 1.05,
          ),
          itemCount: _desks.length,
          itemBuilder: (context, index) => _deskCard(_desks[index]),
        ),
      ],
    );
  }

  Widget _deskCard(Map<String, dynamic> desk) {
    final count = ((desk['record_count'] ?? 0) as num).toInt();
    final headline = (desk['latest_headline'] ?? '').toString();
    final label = (desk['label'] ?? 'Utility').toString();
    final unit = (desk['unit'] ?? '').toString();
    final tint = _tintFor(count);

    return GlassCard(
      padding: const EdgeInsets.all(14),
      child: InkWell(
        borderRadius: BorderRadius.circular(16),
        onTap: () => context.push(
          '/township-operations/${desk['type']}',
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 42,
              height: 42,
              decoration: BoxDecoration(
                color: tint.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(_iconFor(desk['icon']?.toString()),
                  color: tint, size: 24),
            ),
            const Spacer(),
            Text(
              label,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(
                fontWeight: FontWeight.w700,
                fontSize: 13.5,
                color: AppTheme.primaryColor,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              count > 0 ? '$count $unit' : 'No data yet',
              style: TextStyle(
                color: count > 0 ? tint : Colors.black38,
                fontWeight: FontWeight.w700,
                fontSize: 12,
              ),
            ),
            if (headline.isNotEmpty) ...[
              const SizedBox(height: 6),
              Text(
                headline,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(color: Colors.black45, fontSize: 11),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

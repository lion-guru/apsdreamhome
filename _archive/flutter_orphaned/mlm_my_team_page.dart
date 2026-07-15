import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../../data/repositories/kyc_repository_provider.dart';
import '../../../core/theme/app_theme.dart';

class MyTeamPage extends ConsumerStatefulWidget {
  const MyTeamPage({super.key});

  @override
  ConsumerState<MyTeamPage> createState() => _MyTeamPageState();
}

class _MyTeamPageState extends ConsumerState<MyTeamPage> {
  bool _loading = true;
  String? _error;
  List<Map<String, dynamic>> _teamMembers = [];
  String _searchQuery = '';

  Map<String, dynamic> _stats = {
    'total': 0,
    'active': 0,
    'totalSales': 0.0,
  };

  @override
  void initState() {
    super.initState();
    _loadAll();
  }

  Future<void> _loadAll() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final api = ref.read(apiServiceProvider);

      final results = await Future.wait([
        api.get('mlm/team'),
        api.get('mlm/team/stats'),
      ]);

      final teamRes = results[0];
      final statsRes = results[1];

      if (!mounted) return;

      if (teamRes['success'] == true) {
        final raw = teamRes['team'] ?? teamRes['members'] ?? teamRes['data'] ?? [];
        _teamMembers = List<Map<String, dynamic>>.from(
          (raw as List).map((e) => Map<String, dynamic>.from(e as Map)),
        );
      }

      if (statsRes['success'] == true) {
        final s = statsRes['stats'] ?? statsRes['data'] ?? statsRes;
        _stats = {
          'total': s['total'] ?? s['total_members'] ?? _teamMembers.length,
          'active': s['active'] ?? s['active_members'] ?? 0,
          'totalSales': _parseDouble(s['totalSales'] ?? s['total_sales'] ?? 0),
        };
      } else {
        _stats['total'] = _teamMembers.length;
        _stats['active'] = _teamMembers
            .where((m) => (m['status']?.toString().toLowerCase() ?? '') == 'active')
            .length;
      }
    } catch (e) {
      if (mounted) {
        setState(() => _error = 'Failed to load team: $e');
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  List<Map<String, dynamic>> get _filteredMembers {
    if (_searchQuery.isEmpty) return _teamMembers;
    final q = _searchQuery.toLowerCase();
    return _teamMembers.where((m) {
      final name = (m['name']?.toString() ?? '').toLowerCase();
      final email = (m['email']?.toString() ?? '').toLowerCase();
      return name.contains(q) || email.contains(q);
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.surfaceColor,
      appBar: AppBar(
        title: const Text('My Team'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        centerTitle: true,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadAll,
          ),
        ],
      ),
      body: _loading ? _buildShimmer() : (_error != null ? _buildError() : _buildContent()),
    );
  }

  Widget _buildContent() {
    return RefreshIndicator(
      onRefresh: _loadAll,
      child: CustomScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        slivers: [
          SliverToBoxAdapter(child: _buildStatsHeader()),
          SliverToBoxAdapter(child: _buildSearchBar()),
          if (_filteredMembers.isEmpty)
            SliverFillRemaining(child: _buildEmptyState())
          else
            SliverPadding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              sliver: SliverList.separated(
                itemCount: _filteredMembers.length,
                separatorBuilder: (_, __) => const SizedBox(height: 8),
                itemBuilder: (context, index) => _buildTeamCard(_filteredMembers[index]),
              ),
            ),
          const SliverToBoxAdapter(child: SizedBox(height: 24)),
        ],
      ),
    );
  }

  Widget _buildStatsHeader() {
    return Container(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
      child: Row(
        children: [
          _buildStatCard(
            label: 'Total Team',
            value: '${_stats['total']}',
            icon: Icons.people_outline,
            color: AppTheme.primaryColor,
          ),
          const SizedBox(width: 8),
          _buildStatCard(
            label: 'Active',
            value: '${_stats['active']}',
            icon: Icons.person_add_alt_1,
            color: AppTheme.successColor,
          ),
          const SizedBox(width: 8),
          _buildStatCard(
            label: 'Total Sales',
            value: _formatCurrency(_stats['totalSales']),
            icon: Icons.trending_up,
            color: AppTheme.accentColor,
          ),
        ],
      ),
    );
  }

  Widget _buildStatCard({
    required String label,
    required String value,
    required IconData icon,
    required Color color,
  }) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 10),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          boxShadow: [
            BoxShadow(
              color: color.withValues(alpha: 0.1),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          children: [
            Icon(icon, color: color, size: 22),
            const SizedBox(height: 6),
            Text(
              value,
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: color,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: TextStyle(
                fontSize: 10,
                color: Colors.grey.shade600,
                fontWeight: FontWeight.w500,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSearchBar() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 4),
      child: TextField(
        onChanged: (v) => setState(() => _searchQuery = v),
        decoration: InputDecoration(
          hintText: 'Search team members...',
          prefixIcon: const Icon(Icons.search, color: AppTheme.primaryColor),
          suffixIcon: _searchQuery.isNotEmpty
              ? IconButton(
                  icon: const Icon(Icons.clear, size: 20),
                  onPressed: () => setState(() => _searchQuery = ''),
                )
              : null,
          filled: true,
          fillColor: Colors.white,
          contentPadding: const EdgeInsets.symmetric(vertical: 0, horizontal: 16),
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: BorderSide.none,
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: BorderSide(color: Colors.grey.shade200),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: AppTheme.primaryColor, width: 1.5),
          ),
        ),
      ),
    );
  }

  Widget _buildTeamCard(Map<String, dynamic> member) {
    final name = member['name']?.toString() ?? 'Unknown';
    final email = member['email']?.toString() ?? '';
    final rank = member['rank']?.toString() ?? 'Associate';
    final sales = _parseDouble(member['sales'] ?? member['total_sales'] ?? member['team_sales'] ?? 0);
    final joinDate = member['joined_at']?.toString() ?? member['created_at']?.toString() ?? '';
    final isActive = (member['status']?.toString().toLowerCase() ?? '') == 'active';

    return GestureDetector(
      onTap: () => _showDetailSheet(member),
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.05),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Row(
          children: [
            CircleAvatar(
              radius: 24,
              backgroundColor: _getRankColor(rank).withValues(alpha: 0.15),
              child: Text(
                name.isNotEmpty ? name[0].toUpperCase() : '?',
                style: TextStyle(
                  color: _getRankColor(rank),
                  fontWeight: FontWeight.bold,
                  fontSize: 18,
                ),
              ),
            ),
            const SizedBox(width: 12),
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
                            fontWeight: FontWeight.w600,
                            fontSize: 14,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: isActive
                              ? AppTheme.successColor.withValues(alpha: 0.1)
                              : Colors.grey.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          isActive ? 'Active' : 'Inactive',
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.w600,
                            color: isActive ? AppTheme.successColor : Colors.grey,
                          ),
                        ),
                      ),
                    ],
                  ),
                  if (email.isNotEmpty) ...[
                    const SizedBox(height: 2),
                    Text(
                      email,
                      style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: _getRankColor(rank).withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          rank,
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.w600,
                            color: _getRankColor(rank),
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Text(
                        '₹${_formatCurrency(sales)}',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: Colors.grey.shade700,
                        ),
                      ),
                      if (joinDate.isNotEmpty) ...[
                        const Spacer(),
                        Text(
                          _formatJoinDate(joinDate),
                          style: TextStyle(fontSize: 10, color: Colors.grey.shade400),
                        ),
                      ],
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(width: 4),
            Icon(Icons.chevron_right, color: Colors.grey.shade300, size: 20),
          ],
        ),
      ),
    );
  }

  void _showDetailSheet(Map<String, dynamic> member) {
    final name = member['name']?.toString() ?? 'Unknown';
    final email = member['email']?.toString() ?? '';
    final phone = member['phone']?.toString() ?? member['mobile']?.toString() ?? '';
    final rank = member['rank']?.toString() ?? 'Associate';
    final sales = _parseDouble(member['sales'] ?? member['total_sales'] ?? member['team_sales'] ?? 0);
    final joinDate = member['joined_at']?.toString() ?? member['created_at']?.toString() ?? '';
    final downlineCount = member['downline_count'] ?? member['team_size'] ?? 0;
    final isActive = (member['status']?.toString().toLowerCase() ?? '') == 'active';

    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) {
        return Container(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: Colors.grey.shade300,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 20),
              Center(
                child: CircleAvatar(
                  radius: 32,
                  backgroundColor: _getRankColor(rank).withValues(alpha: 0.15),
                  child: Text(
                    name.isNotEmpty ? name[0].toUpperCase() : '?',
                    style: TextStyle(
                      color: _getRankColor(rank),
                      fontWeight: FontWeight.bold,
                      fontSize: 26,
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 12),
              Center(
                child: Text(
                  name,
                  style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                ),
              ),
              const SizedBox(height: 4),
              Center(
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                  decoration: BoxDecoration(
                    color: _getRankColor(rank).withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    rank,
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                      color: _getRankColor(rank),
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 20),
              _detailRow(Icons.email_outlined, 'Email', email.isNotEmpty ? email : 'Not available'),
              _detailRow(Icons.phone_outlined, 'Phone', phone.isNotEmpty ? phone : 'Not available'),
              _detailRow(Icons.attach_money, 'Total Sales', '₹${_formatCurrency(sales)}'),
              _detailRow(Icons.group_outlined, 'Downline', '$downlineCount members'),
              if (joinDate.isNotEmpty)
                _detailRow(Icons.calendar_today_outlined, 'Joined', _formatJoinDate(joinDate)),
              _detailRow(
                Icons.circle,
                'Status',
                isActive ? 'Active' : 'Inactive',
                valueColor: isActive ? AppTheme.successColor : Colors.red,
              ),
              const SizedBox(height: 16),
              if (phone.isNotEmpty)
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: () {
                      Navigator.pop(ctx);
                    },
                    icon: const Icon(Icons.phone, size: 18),
                    label: const Text('Contact Member'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppTheme.primaryColor,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                  ),
                ),
              const SizedBox(height: 8),
            ],
          ),
        );
      },
    );
  }

  Widget _detailRow(IconData icon, String label, String value, {Color? valueColor}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        children: [
          Icon(icon, size: 18, color: Colors.grey.shade500),
          const SizedBox(width: 12),
          Text(
            label,
            style: TextStyle(fontSize: 13, color: Colors.grey.shade600),
          ),
          const Spacer(),
          Flexible(
            child: Text(
              value,
              style: TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w600,
                color: valueColor ?? Colors.grey.shade800,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              textAlign: TextAlign.end,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(40),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: AppTheme.primaryColor.withValues(alpha: 0.08),
                shape: BoxShape.circle,
              ),
              child: Icon(
                Icons.people_outline,
                size: 56,
                color: AppTheme.primaryColor.withValues(alpha: 0.4),
              ),
            ),
            const SizedBox(height: 24),
            Text(
              _searchQuery.isNotEmpty ? 'No matching members' : 'No team members yet',
              style: const TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w600,
                color: Colors.grey,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              _searchQuery.isNotEmpty
                  ? 'Try a different search term'
                  : 'Start referring people to build your team',
              style: TextStyle(fontSize: 14, color: Colors.grey.shade500),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildError() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(40),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.error_outline, size: 56, color: AppTheme.errorColor.withValues(alpha: 0.5)),
            const SizedBox(height: 16),
            Text(
              _error!,
              style: const TextStyle(fontSize: 14, color: Colors.grey),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 20),
            ElevatedButton.icon(
              onPressed: _loadAll,
              icon: const Icon(Icons.refresh),
              label: const Text('Retry'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primaryColor,
                foregroundColor: Colors.white,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildShimmer() {
    return SingleChildScrollView(
      physics: const NeverScrollableScrollPhysics(),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            Row(
              children: List.generate(3, (_) => Expanded(
                child: _shimmerBox(
                  height: 90,
                  margin: const EdgeInsets.symmetric(horizontal: 4),
                ),
              )),
            ),
            const SizedBox(height: 16),
            _shimmerBox(height: 48),
            const SizedBox(height: 16),
            ...List.generate(4, (_) => Column(
              children: [
                _shimmerBox(height: 80),
                const SizedBox(height: 8),
              ],
            )),
          ],
        ),
      ),
    );
  }

  Widget _shimmerBox({required double height, EdgeInsets? margin}) {
    return Container(
      height: height,
      margin: margin,
      decoration: BoxDecoration(
        color: Colors.grey.shade200,
        borderRadius: BorderRadius.circular(12),
      ),
      child: const _ShimmerOverlay(),
    );
  }

  Color _getRankColor(String rank) {
    switch (rank.toLowerCase()) {
      case 'president':
        return const Color(0xFFD32F2F);
      case 'vice president':
      case 'vp':
        return const Color(0xFF7B1FA2);
      case 'sr. bdm':
      case 'sr bdm':
        return const Color(0xFF1565C0);
      case 'bdm':
        return const Color(0xFF0277BD);
      case 'sr. associate':
      case 'sr associate':
        return const Color(0xFF2E7D32);
      case 'gold':
        return const Color(0xFFF9A825);
      case 'platinum':
        return const Color(0xFF5C6BC0);
      case 'diamond':
        return const Color(0xFF00ACC1);
      case 'silver':
        return const Color(0xFF78909C);
      default:
        return AppTheme.primaryColor;
    }
  }

  String _formatCurrency(dynamic amount) {
    final val = _parseDouble(amount);
    if (val >= 10000000) {
      return '${(val / 10000000).toStringAsFixed(2)} Cr';
    } else if (val >= 100000) {
      return '${(val / 100000).toStringAsFixed(1)} L';
    } else if (val >= 1000) {
      return '${(val / 1000).toStringAsFixed(1)}K';
    }
    return val.toStringAsFixed(0);
  }

  String _formatJoinDate(String dateStr) {
    try {
      final date = DateTime.parse(dateStr);
      return DateFormat('dd MMM yyyy').format(date);
    } catch (_) {
      return dateStr;
    }
  }

  double _parseDouble(dynamic value) {
    if (value is double) return value;
    if (value is int) return value.toDouble();
    if (value is num) return value.toDouble();
    return double.tryParse(value.toString()) ?? 0.0;
  }
}

class _ShimmerOverlay extends StatefulWidget {
  const _ShimmerOverlay();

  @override
  State<_ShimmerOverlay> createState() => _ShimmerOverlayState();
}

class _ShimmerOverlayState extends State<_ShimmerOverlay>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1500),
    )..repeat();
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _controller,
      builder: (context, child) {
        return Container(
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(12),
            gradient: LinearGradient(
              colors: [
                Colors.grey.shade200,
                Colors.grey.shade100,
                Colors.grey.shade200,
              ],
              stops: [
                (_controller.value - 0.3).clamp(0.0, 1.0),
                _controller.value,
                (_controller.value + 0.3).clamp(0.0, 1.0),
              ],
            ),
          ),
        );
      },
    );
  }
}

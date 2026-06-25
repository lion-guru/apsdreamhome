import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../../data/repositories/kyc_repository_provider.dart';
import '../../../core/theme/app_theme.dart';

class PayoutPage extends ConsumerStatefulWidget {
  const PayoutPage({super.key});

  @override
  ConsumerState<PayoutPage> createState() => _PayoutPageState();
}

class _PayoutPageState extends ConsumerState<PayoutPage> {
  bool _loading = true;
  String? _error;
  List<Map<String, dynamic>> _payouts = [];
  String _activeFilter = 'all';

  Map<String, dynamic> _summary = {
    'total_earned': 0.0,
    'pending': 0.0,
    'paid': 0.0,
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
        api.get('mlm/earnings'),
        api.get('mlm/earnings/summary'),
      ]);

      final earningsRes = results[0];
      final summaryRes = results[1];

      if (!mounted) return;

      if (earningsRes['success'] == true) {
        final raw = earningsRes['earnings'] ?? earningsRes['payouts'] ?? earningsRes['data'] ?? [];
        _payouts = List<Map<String, dynamic>>.from(
          (raw as List).map((e) => Map<String, dynamic>.from(e as Map)),
        );
      }

      if (summaryRes['success'] == true) {
        final s = summaryRes['summary'] ?? summaryRes['data'] ?? summaryRes;
        _summary = {
          'total_earned': _parseDouble(s['total_earned'] ?? s['total_earned_amount'] ?? 0),
          'pending': _parseDouble(s['pending'] ?? s['pending_amount'] ?? 0),
          'paid': _parseDouble(s['paid'] ?? s['paid_amount'] ?? 0),
        };
      } else {
        _computeSummaryFromList();
      }
    } catch (e) {
      if (mounted) {
        setState(() => _error = 'Failed to load earnings: $e');
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _computeSummaryFromList() {
    double total = 0;
    double pending = 0;
    double paid = 0;

    for (final p in _payouts) {
      final amount = _parseDouble(p['amount'] ?? p['commission_amount'] ?? 0);
      total += amount;
      final status = (p['status']?.toString().toLowerCase() ?? '');
      if (status == 'paid' || status == 'completed') {
        paid += amount;
      } else if (status == 'pending' || status == 'processing') {
        pending += amount;
      }
    }

    _summary = {'total_earned': total, 'pending': pending, 'paid': paid};
  }

  List<Map<String, dynamic>> get _filteredPayouts {
    if (_activeFilter == 'all') return _payouts;
    return _payouts.where((p) {
      final status = (p['status']?.toString().toLowerCase() ?? '');
      return status == _activeFilter;
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.surfaceColor,
      appBar: AppBar(
        title: const Text('My Earnings'),
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
          SliverToBoxAdapter(child: _buildSummaryCard()),
          SliverToBoxAdapter(child: _buildFilterChips()),
          if (_filteredPayouts.isEmpty)
            SliverFillRemaining(child: _buildEmptyState())
          else
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
              sliver: SliverList.separated(
                itemCount: _filteredPayouts.length,
                separatorBuilder: (_, __) => const SizedBox(height: 8),
                itemBuilder: (context, index) => _buildPayoutCard(_filteredPayouts[index]),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildSummaryCard() {
    final totalEarned = _parseDouble(_summary['total_earned']);
    final pending = _parseDouble(_summary['pending']);
    final paid = _parseDouble(_summary['paid']);

    return Container(
      margin: const EdgeInsets.fromLTRB(16, 16, 16, 4),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [AppTheme.primaryColor, Color(0xFF3949AB)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: AppTheme.primaryColor.withValues(alpha: 0.3),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.account_balance_wallet, color: Colors.white, size: 22),
              ),
              const SizedBox(width: 12),
              const Text(
                'Earnings Overview',
                style: TextStyle(
                  color: Colors.white70,
                  fontSize: 14,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),
          const SizedBox(height: 20),
          Text(
            '₹${_formatCurrency(totalEarned)}',
            style: const TextStyle(
              color: Colors.white,
              fontSize: 32,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 4),
          const Text(
            'Total Earned',
            style: TextStyle(color: Colors.white60, fontSize: 13),
          ),
          const SizedBox(height: 20),
          Row(
            children: [
              _buildSummarySub(
                label: 'Pending Payout',
                value: '₹${_formatCurrency(pending)}',
                color: const Color(0xFFFFD54F),
              ),
              const SizedBox(width: 16),
              _buildSummarySub(
                label: 'Paid Out',
                value: '₹${_formatCurrency(paid)}',
                color: const Color(0xFF81C784),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildSummarySub({
    required String label,
    required String value,
    required Color color,
  }) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: Colors.white.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(10),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              label,
              style: const TextStyle(color: Colors.white60, fontSize: 11),
            ),
            const SizedBox(height: 4),
            Text(
              value,
              style: TextStyle(
                color: color,
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFilterChips() {
    final filters = [
      {'label': 'All', 'value': 'all'},
      {'label': 'Paid', 'value': 'paid'},
      {'label': 'Pending', 'value': 'pending'},
      {'label': 'Processing', 'value': 'processing'},
    ];

    return SizedBox(
      height: 52,
      child: ListView.separated(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        scrollDirection: Axis.horizontal,
        itemCount: filters.length,
        separatorBuilder: (_, __) => const SizedBox(width: 8),
        itemBuilder: (context, index) {
          final f = filters[index];
          final isActive = _activeFilter == f['value'];
          return FilterChip(
            label: Text(
              f['label']!,
              style: TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w500,
                color: isActive ? Colors.white : AppTheme.primaryColor,
              ),
            ),
            selected: isActive,
            onSelected: (_) => setState(() => _activeFilter = f['value']!),
            backgroundColor: Colors.white,
            selectedColor: AppTheme.primaryColor,
            checkmarkColor: Colors.white,
            side: BorderSide(
              color: isActive ? AppTheme.primaryColor : Colors.grey.shade300,
            ),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(20),
            ),
            padding: const EdgeInsets.symmetric(horizontal: 4),
          );
        },
      ),
    );
  }

  Widget _buildPayoutCard(Map<String, dynamic> payout) {
    final amount = _parseDouble(payout['amount'] ?? payout['commission_amount'] ?? 0);
    final status = (payout['status']?.toString() ?? 'pending').toLowerCase();
    final type = (payout['type'] ?? payout['commission_type'] ?? 'commission').toString();
    final date = payout['date']?.toString() ?? payout['created_at']?.toString() ?? '';
    final reference = payout['reference_no']?.toString() ?? payout['reference']?.toString() ?? '';
    final isCredit = status != 'penalty' && amount >= 0;

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              color: _getTypeColor(type).withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(
              _getTypeIcon(type),
              color: _getTypeColor(type),
              size: 22,
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
                        _formatType(type),
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
                        color: _getStatusColor(status).withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        status[0].toUpperCase() + status.substring(1),
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.w600,
                          color: _getStatusColor(status),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    if (date.isNotEmpty) ...[
                      Text(
                        _formatDate(date),
                        style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                      ),
                      const SizedBox(width: 8),
                    ],
                    if (reference.isNotEmpty)
                      Text(
                        'Ref: $reference',
                        style: TextStyle(fontSize: 11, color: Colors.grey.shade400),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(width: 8),
          Text(
            '${isCredit ? '+' : '-'}₹${_formatCurrency(amount.abs())}',
            style: TextStyle(
              fontWeight: FontWeight.bold,
              fontSize: 15,
              color: isCredit ? AppTheme.successColor : AppTheme.errorColor,
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
                color: AppTheme.accentColor.withValues(alpha: 0.08),
                shape: BoxShape.circle,
              ),
              child: Icon(
                Icons.account_balance_wallet_outlined,
                size: 56,
                color: AppTheme.accentColor.withValues(alpha: 0.5),
              ),
            ),
            const SizedBox(height: 24),
            const Text(
              'No earnings yet',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w600,
                color: Colors.grey,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              _activeFilter != 'all'
                  ? 'No ${_activeFilter} payouts found'
                  : 'Start making sales to earn commissions',
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
            _shimmerBox(height: 200),
            const SizedBox(height: 16),
            Row(
              children: List.generate(4, (_) => Expanded(
                child: _shimmerBox(
                  height: 36,
                  margin: const EdgeInsets.symmetric(horizontal: 4),
                ),
              )),
            ),
            const SizedBox(height: 16),
            ...List.generate(5, (_) => Column(
              children: [
                _shimmerBox(height: 72),
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
    );
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'paid':
      case 'completed':
        return AppTheme.successColor;
      case 'pending':
        return AppTheme.warningColor;
      case 'processing':
        return AppTheme.infoColor;
      case 'rejected':
      case 'failed':
        return AppTheme.errorColor;
      default:
        return Colors.grey;
    }
  }

  Color _getTypeColor(String type) {
    switch (type.toLowerCase()) {
      case 'commission':
      case 'direct_sale':
      case 'team_bonus':
        return AppTheme.primaryColor;
      case 'bonus':
      case 'performance_bonus':
      case 'milestone':
        return AppTheme.accentColor;
      case 'penalty':
      case 'clawback':
        return AppTheme.errorColor;
      case 'incentive':
        return AppTheme.successColor;
      default:
        return Colors.grey;
    }
  }

  IconData _getTypeIcon(String type) {
    switch (type.toLowerCase()) {
      case 'commission':
      case 'direct_sale':
        return Icons.trending_up;
      case 'team_bonus':
        return Icons.group;
      case 'bonus':
      case 'performance_bonus':
        return Icons.emoji_events;
      case 'milestone':
        return Icons.flag;
      case 'penalty':
      case 'clawback':
        return Icons.gavel;
      case 'incentive':
        return Icons.stars;
      default:
        return Icons.receipt_long;
    }
  }

  String _formatType(String type) {
    switch (type.toLowerCase()) {
      case 'direct_sale':
        return 'Direct Sale Commission';
      case 'team_bonus':
        return 'Team Bonus';
      case 'performance_bonus':
        return 'Performance Bonus';
      case 'milestone':
        return 'Milestone Reward';
      case 'clawback':
        return 'Clawback';
      default:
        return type[0].toUpperCase() + type.substring(1);
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

  String _formatDate(String dateStr) {
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

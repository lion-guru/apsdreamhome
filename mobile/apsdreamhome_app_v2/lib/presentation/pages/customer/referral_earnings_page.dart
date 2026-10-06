import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:share_plus/share_plus.dart';

import '../../../core/theme/app_theme.dart';
import '../../../data/repositories/kyc_repository_provider.dart';

final _inr = NumberFormat('#,##,###');

/// Referral earnings dashboard: summary, breakdown by source,
/// recent activity, share card and leaderboard.
class ReferralEarningsPage extends ConsumerStatefulWidget {
  const ReferralEarningsPage({super.key});

  @override
  ConsumerState<ReferralEarningsPage> createState() =>
      _ReferralEarningsPageState();
}

class _ReferralEarningsPageState extends ConsumerState<ReferralEarningsPage> {
  bool _isLoading = true;
  String? _error;

  Map<String, dynamic> _summary = {};
  List<Map<String, dynamic>> _byType = [];
  List<Map<String, dynamic>> _recent = [];
  List<Map<String, dynamic>> _leaderboard = [];
  Map<String, dynamic> _myRank = {};
  String _referralCode = '';
  String _shareUrl = '';

  @override
  void initState() {
    super.initState();
    _fetchAll();
  }

  Future<void> _fetchAll() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final api = ref.read(apiServiceProvider);
      final results = await Future.wait([
        api.getReferralEarnings(),
        api.getReferralShareUrl(),
        api.getReferralLeaderboard(),
      ]);
      if (!mounted) return;
      final earnings = results[0] as Map<String, dynamic>? ?? {};
      final share = results[1] as Map<String, dynamic>? ?? {};
      final board = results[2] as Map<String, dynamic>? ?? {};
      setState(() {
        _summary =
            Map<String, dynamic>.from(earnings['summary'] as Map? ?? {});
        _byType = ((earnings['by_type'] as List?) ?? [])
            .map((e) => Map<String, dynamic>.from(e as Map))
            .toList();
        _recent = ((earnings['recent'] as List?) ?? [])
            .map((e) => Map<String, dynamic>.from(e as Map))
            .toList();
        _referralCode = share['referral_code']?.toString() ?? '';
        _shareUrl = share['share_url']?.toString() ?? '';
        _leaderboard = ((board['leaderboard'] as List?) ?? [])
            .map((e) => Map<String, dynamic>.from(e as Map))
            .toList();
        _myRank = Map<String, dynamic>.from(
            board['my_rank'] as Map? ?? {});
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  Color _typeColor(String type) {
    if (type.contains('customer')) return Colors.amber.shade800;
    if (type.contains('associate')) return Colors.green;
    if (type.contains('wallet')) return Colors.blue;
    if (type.contains('signup')) return Colors.indigo;
    return Colors.teal;
  }

  IconData _typeIcon(String type) {
    if (type.contains('customer')) return Icons.person_rounded;
    if (type.contains('associate')) return Icons.badge_rounded;
    if (type.contains('wallet')) return Icons.account_balance_wallet_rounded;
    if (type.contains('signup')) return Icons.person_add_rounded;
    return Icons.calendar_month_rounded;
  }

  Future<void> _share() async {
    final msg = _shareUrl.isNotEmpty
        ? 'Join APS Dream Home using my referral code $_referralCode!\n$_shareUrl'
        : 'Join APS Dream Home using my referral code $_referralCode!';
    try {
      await SharePlus.instance.share(ShareParams(text: msg));
    } catch (_) {}
  }

  Future<void> _copyLink() async {
    final link = _shareUrl.isNotEmpty ? _shareUrl : _referralCode;
    await Clipboard.setData(ClipboardData(text: link));
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Referral link copied to clipboard')),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.surfaceColor,
      appBar: AppBar(
        title: const Text('Referral Earnings'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new, size: 20),
          onPressed: () => context.pop(),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.wallet_rounded),
            tooltip: 'Activate Wallet',
            onPressed: () => context.push('/wallet-activation'),
          ),
        ],
      ),
      body: _isLoading
          ? const Center(
              child:
                  CircularProgressIndicator(color: AppTheme.primaryColor),
            )
          : _error != null
              ? _buildError()
              : RefreshIndicator(
                  onRefresh: _fetchAll,
                  color: AppTheme.primaryColor,
                  child: SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _buildShareCard(),
                        const SizedBox(height: 16),
                        _buildSummaryGrid(),
                        const SizedBox(height: 16),
                        _buildBreakdown(),
                        const SizedBox(height: 16),
                        _buildRecent(),
                        const SizedBox(height: 16),
                        _buildLeaderboard(),
                        const SizedBox(height: 24),
                      ],
                    ),
                  ),
                ),
    );
  }

  Widget _buildError() => Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.error_outline, size: 56, color: Colors.red),
              const SizedBox(height: 12),
              Text(_error ?? 'Something went wrong',
                  textAlign: TextAlign.center),
              const SizedBox(height: 12),
              ElevatedButton(
                onPressed: _fetchAll,
                child: const Text('Retry'),
              ),
            ],
          ),
        ),
      );

  Widget _buildShareCard() => Container(
        width: double.infinity,
        padding:
            const EdgeInsets.symmetric(vertical: 28, horizontal: 24),
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [
              Color(0xFFB45309),
              Color(0xFFF59E0B),
              AppTheme.secondaryColor,
            ],
          ),
          borderRadius: BorderRadius.circular(20),
          boxShadow: [
            BoxShadow(
              color: Colors.amber.withValues(alpha: 0.3),
              blurRadius: 20,
              offset: const Offset(0, 8),
            ),
          ],
        ),
        child: Column(
          children: [
            const Text(
              'YOUR REFERRAL CODE',
              style: TextStyle(
                  color: Colors.white70,
                  fontSize: 10,
                  fontWeight: FontWeight.w700,
                  letterSpacing: 1.2),
            ),
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.symmetric(
                  horizontal: 24, vertical: 14),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.15),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                    color: Colors.white.withValues(alpha: 0.3)),
              ),
              child: Text(
                _referralCode.isNotEmpty ? _referralCode : '------',
                style: const TextStyle(
                    color: Colors.white,
                    fontSize: 30,
                    fontWeight: FontWeight.w800,
                    letterSpacing: 5),
              ),
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: ElevatedButton.icon(
                    onPressed: _share,
                    icon: const Icon(Icons.share_rounded, size: 18),
                    label: const Text('Share'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.white,
                      foregroundColor: const Color(0xFFB45309),
                      padding:
                          const EdgeInsets.symmetric(vertical: 12),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: _copyLink,
                    icon: const Icon(Icons.copy_rounded, size: 18),
                    label: const Text('Copy Link'),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: Colors.white,
                      side: const BorderSide(color: Colors.white),
                      padding:
                          const EdgeInsets.symmetric(vertical: 12),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      );

  Widget _buildSummaryGrid() {
    final cards = [
      {
        'label': 'Total Referrals',
        'value': '${_summary['total_referrals'] ?? 0}',
        'icon': Icons.people_rounded,
        'color': Colors.amber.shade800,
      },
      {
        'label': 'Active (Booked)',
        'value': '${_summary['active_referrals'] ?? 0}',
        'icon': Icons.verified_user_rounded,
        'color': Colors.green,
      },
      {
        'label': 'Total Earned',
        'value':
            '₹${_inr.format((_summary['total_earned'] as num?)?.toDouble() ?? 0)}',
        'icon': Icons.savings_rounded,
        'color': Colors.blue,
      },
      {
        'label': 'This Month',
        'value':
            '₹${_inr.format((_summary['this_month'] as num?)?.toDouble() ?? 0)}',
        'icon': Icons.calendar_month_rounded,
        'color': Colors.purple,
      },
    ];
    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        mainAxisSpacing: 12,
        crossAxisSpacing: 12,
        childAspectRatio: 1.35,
      ),
      itemCount: cards.length,
      itemBuilder: (context, i) {
        final c = cards[i];
        final color = c['color'] as Color;
        return Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.05),
                blurRadius: 12,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                width: 38,
                height: 38,
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(c['icon'] as IconData,
                    color: color, size: 20),
              ),
              const SizedBox(height: 8),
              Text('${c['value']}',
                  style: const TextStyle(
                      fontWeight: FontWeight.w800, fontSize: 17)),
              Text('${c['label']}',
                  style: TextStyle(
                      fontSize: 11, color: Colors.grey.shade600)),
            ],
          ),
        );
      },
    );
  }

  Widget _buildBreakdown() => Container(
        width: double.infinity,
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.05),
              blurRadius: 12,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(Icons.pie_chart_rounded,
                    color: AppTheme.primaryColor, size: 20),
                SizedBox(width: 8),
                Text('Earnings by Source',
                    style:
                        TextStyle(fontWeight: FontWeight.w700, fontSize: 15)),
              ],
            ),
            const SizedBox(height: 12),
            if (_byType.isEmpty)
              const Padding(
                padding: EdgeInsets.symmetric(vertical: 16),
                child: Center(
                    child: Text('No referral earnings yet.\nStart sharing your code!',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                            fontSize: 13, color: Colors.grey))),
              )
            else
              ..._byType.map((t) {
                final type = t['type']?.toString() ?? '';
                final label = t['label']?.toString() ?? type;
                final count = (t['count'] as num?)?.toInt() ?? 0;
                final amount =
                    (t['amount'] as num?)?.toDouble() ?? 0;
                final color = _typeColor(type);
                return Padding(
                  padding: const EdgeInsets.symmetric(vertical: 7),
                  child: Row(
                    children: [
                      Container(
                        width: 36,
                        height: 36,
                        decoration: BoxDecoration(
                          color: color.withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Icon(_typeIcon(type),
                            color: color, size: 18),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(label,
                                style: const TextStyle(
                                    fontWeight: FontWeight.w600,
                                    fontSize: 13)),
                            Text('$count earning${count == 1 ? '' : 's'}',
                                style: TextStyle(
                                    fontSize: 11,
                                    color: Colors.grey.shade600)),
                          ],
                        ),
                      ),
                      Text('₹${_inr.format(amount)}',
                          style: const TextStyle(
                              fontWeight: FontWeight.w800,
                              fontSize: 14,
                              color: Colors.green)),
                    ],
                  ),
                );
              }),
          ],
        ),
      );

  Widget _buildRecent() => Container(
        width: double.infinity,
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.05),
              blurRadius: 12,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(Icons.history_rounded,
                    color: AppTheme.primaryColor, size: 20),
                SizedBox(width: 8),
                Text('Recent Activity',
                    style:
                        TextStyle(fontWeight: FontWeight.w700, fontSize: 15)),
              ],
            ),
            const SizedBox(height: 12),
            if (_recent.isEmpty)
              const Padding(
                padding: EdgeInsets.symmetric(vertical: 16),
                child: Center(
                    child: Text('No recent referral activity',
                        style: TextStyle(
                            fontSize: 13, color: Colors.grey))),
              )
            else
              ..._recent.take(5).map((r) {
                final type = r['type']?.toString() ?? 'referral';
                final amount =
                    (r['amount'] as num?)?.toDouble() ?? 0;
                final status = r['status']?.toString() ?? 'pending';
                final paid = status == 'paid';
                return Padding(
                  padding: const EdgeInsets.symmetric(vertical: 7),
                  child: Row(
                    children: [
                      Container(
                        width: 36,
                        height: 36,
                        decoration: BoxDecoration(
                          color: (paid ? Colors.green : Colors.amber)
                              .withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Icon(
                          paid
                              ? Icons.arrow_downward_rounded
                              : Icons.hourglass_bottom_rounded,
                          color: paid ? Colors.green : Colors.amber.shade800,
                          size: 18,
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              type
                                  .replaceAll('_', ' ')
                                  .split(' ')
                                  .map((w) => w.isEmpty
                                      ? w
                                      : '${w[0].toUpperCase()}${w.substring(1)}')
                                  .join(' '),
                              style: const TextStyle(
                                  fontWeight: FontWeight.w600,
                                  fontSize: 13),
                            ),
                            Text(
                              (r['created_at']?.toString() ?? '')
                                  .split(' ')
                                  .first,
                              style: TextStyle(
                                  fontSize: 11,
                                  color: Colors.grey.shade600),
                            ),
                          ],
                        ),
                      ),
                      Text(
                        '${paid ? '+' : '⏳'}₹${_inr.format(amount)}',
                        style: TextStyle(
                            fontWeight: FontWeight.w800,
                            fontSize: 14,
                            color: paid
                                ? Colors.green
                                : Colors.amber.shade800),
                      ),
                    ],
                  ),
                );
              }),
          ],
        ),
      );

  Widget _buildLeaderboard() {
    final myRank = (_myRank['rank'] as num?)?.toInt() ?? 0;
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
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
              const Icon(Icons.emoji_events_rounded,
                  color: Colors.amber, size: 20),
              const SizedBox(width: 8),
              const Text('Leaderboard',
                  style: TextStyle(
                      fontWeight: FontWeight.w700, fontSize: 15)),
              const Spacer(),
              if (myRank > 0)
                Container(
                  padding: const EdgeInsets.symmetric(
                      horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppTheme.primaryColor
                        .withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    'My Rank #$myRank',
                    style: const TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                        color: AppTheme.primaryColor),
                  ),
                ),
            ],
          ),
          const SizedBox(height: 12),
          if (_leaderboard.isEmpty)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 16),
              child: Center(
                  child: Text('Leaderboard coming soon',
                      style:
                          TextStyle(fontSize: 13, color: Colors.grey))),
            )
          else
            ..._leaderboard.take(5).map((e) {
              final rank = (e['rank'] as num?)?.toInt() ?? 0;
              final medal = rank == 1
                  ? '🥇'
                  : (rank == 2
                      ? '🥈'
                      : (rank == 3 ? '🥉' : '#$rank'));
              return Padding(
                padding: const EdgeInsets.symmetric(vertical: 6),
                child: Row(
                  children: [
                    SizedBox(
                        width: 40,
                        child: Text(medal,
                            style:
                                const TextStyle(fontSize: 16))),
                    Expanded(
                      child: Text(
                        e['name']?.toString() ?? 'User',
                        style: const TextStyle(
                            fontWeight: FontWeight.w600,
                            fontSize: 13),
                      ),
                    ),
                    Text(
                      '₹${_inr.format((e['total_earned'] as num?)?.toDouble() ?? 0)}',
                      style: const TextStyle(
                          fontWeight: FontWeight.w700, fontSize: 13),
                    ),
                  ],
                ),
              );
            }),
        ],
      ),
    );
  }
}

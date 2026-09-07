import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class UserNetworkPage extends ConsumerStatefulWidget {
  const UserNetworkPage({super.key});

  @override
  ConsumerState<UserNetworkPage> createState() => _UserNetworkPageState();
}

class _UserNetworkPageState extends ConsumerState<UserNetworkPage> {
  Map<String, dynamic>? _networkData;
  List<Map<String, dynamic>> _directReferrals = [];
  bool _isLoading = true;
  int _currentTab = 0;
  Dio get _dio => Dio(BaseOptions(baseUrl: AppConstants.baseUrl));

  @override
  void initState() {
    super.initState();
    _fetchNetworkData();
  }

  Future<void> _fetchNetworkData() async {
    setState(() => _isLoading = true);
    try {
      final token = await ref.read(authProvider.notifier).getToken();
      final results = await Future.wait([
        _dio.get('/api/v2/mobile/user/network-summary', options: Options(headers: {'Authorization': 'Bearer $token'})),
        _dio.get('/api/v2/mobile/user/direct-referrals', options: Options(headers: {'Authorization': 'Bearer $token'})),
      ]);

      if (results[0].data['success'] == true) {
        setState(() {
          _networkData = results[0].data['data'] as Map<String, dynamic>;
        });
      }

      if (results[1].data['success'] == true) {
        final data = results[1].data['data'] as List;
        setState(() {
          _directReferrals = data.cast<Map<String, dynamic>>();
          _isLoading = false;
        });
      } else {
        _loadMockData();
      }
    } catch (e) {
      _loadMockData();
    }
  }

  void _loadMockData() {
    setState(() {
      _networkData = {
        'total_team': 42,
        'direct_referrals': 8,
        'active_members': 35,
        'team_business_volume': 12500000,
        'my_rank': 'Sr. Associate',
        'next_rank': 'BDM',
        'rank_progress': 0.65,
      };
      _directReferrals = [
        {'id': 1, 'name': 'Rajesh Kumar', 'rank': 'Associate', 'join_date': '2025-01-15', 'business': 1200000, 'status': 'active'},
        {'id': 2, 'name': 'Priya Sharma', 'rank': 'Associate', 'join_date': '2025-02-20', 'business': 850000, 'status': 'active'},
        {'id': 3, 'name': 'Amit Singh', 'rank': 'Associate', 'join_date': '2025-03-10', 'business': 450000, 'status': 'inactive'},
        {'id': 4, 'name': 'Sunita Devi', 'rank': 'Associate', 'join_date': '2025-04-05', 'business': 2100000, 'status': 'active'},
        {'id': 5, 'name': 'Vikash Gupta', 'rank': 'Associate', 'join_date': '2025-05-12', 'business': 670000, 'status': 'active'},
        {'id': 6, 'name': 'Anita Singh', 'rank': 'Associate', 'join_date': '2025-06-01', 'business': 320000, 'status': 'active'},
        {'id': 7, 'name': 'Rohit Verma', 'rank': 'Associate', 'join_date': '2025-07-18', 'business': 150000, 'status': 'inactive'},
        {'id': 8, 'name': 'Meera Patel', 'rank': 'Associate', 'join_date': '2025-08-10', 'business': 890000, 'status': 'active'},
      ];
      _isLoading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: MeshGradientBackground(
        child: SafeArea(
          child: _isLoading
              ? const Center(child: CircularProgressIndicator(color: Colors.white))
              : Column(
                  children: [
                    _buildHeader(),
                    _buildTabBar(),
                    Expanded(
                      child: _currentTab == 0 ? _buildOverviewTab() : _buildReferralsTab(),
                    ),
                  ],
                ),
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 10),
      child: Row(
        children: [
          GestureDetector(
            onTap: () => Navigator.pop(context),
            child: Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(Icons.arrow_back, color: Colors.white, size: 22),
            ),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: ShaderMask(
              shaderCallback: (bounds) => const LinearGradient(
                colors: [Colors.white, Color(0xFFE1BEE7)],
              ).createShader(bounds),
              child: Text(
                'My Network',
                style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                  color: Colors.white,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTabBar() {
    return Container(
      height: 50,
      margin: const EdgeInsets.symmetric(horizontal: 20),
      child: Row(
        children: [
          Expanded(
            child: GestureDetector(
              onTap: () => setState(() => _currentTab = 0),
              child: Container(
                alignment: Alignment.center,
                decoration: BoxDecoration(
                  color: _currentTab == 0 ? AppTheme.accentColor.withValues(alpha: 0.3) : Colors.white.withValues(alpha: 0.05),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: _currentTab == 0 ? AppTheme.accentColor : Colors.white.withValues(alpha: 0.1),
                  ),
                ),
                padding: const EdgeInsets.symmetric(vertical: 10),
                child: Text(
                  'Overview',
                  style: TextStyle(
                    color: _currentTab == 0 ? AppTheme.accentColor : Colors.white.withValues(alpha: 0.7),
                    fontWeight: _currentTab == 0 ? FontWeight.w600 : FontWeight.normal,
                  ),
                ),
              ),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: GestureDetector(
              onTap: () => setState(() => _currentTab = 1),
              child: Container(
                alignment: Alignment.center,
                decoration: BoxDecoration(
                  color: _currentTab == 1 ? AppTheme.accentColor.withValues(alpha: 0.3) : Colors.white.withValues(alpha: 0.05),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: _currentTab == 1 ? AppTheme.accentColor : Colors.white.withValues(alpha: 0.1),
                  ),
                ),
                padding: const EdgeInsets.symmetric(vertical: 10),
                child: Text(
                  'Direct Referrals',
                  style: TextStyle(
                    color: _currentTab == 1 ? AppTheme.accentColor : Colors.white.withValues(alpha: 0.7),
                    fontWeight: _currentTab == 1 ? FontWeight.w600 : FontWeight.normal,
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildOverviewTab() {
    return SingleChildScrollView(
      padding: const EdgeInsets.fromLTRB(20, 10, 20, 20),
      child: Column(
        children: [
          _buildRankCard(),
          const SizedBox(height: 20),
          _buildStatsGrid(),
          const SizedBox(height: 20),
          _buildTeamVolumeCard(),
        ],
      ),
    );
  }

  Widget _buildRankCard() {
    final rank = _networkData!['my_rank']?.toString() ?? 'Associate';
    final nextRank = _networkData!['next_rank']?.toString() ?? 'Sr. Associate';
    final progress = (_networkData!['rank_progress'] as num?)?.toDouble() ?? 0.0;

    return GlassCard(
      padding: const EdgeInsets.all(20),
      opacity: 0.15,
      blur: 12,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 56,
                height: 56,
                decoration: BoxDecoration(
                  gradient: const LinearGradient(colors: [AppTheme.accentColor, Color(0xFFFFD700)]),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: const Icon(Icons.emoji_events_rounded, color: Colors.white, size: 28),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Current Rank',
                      style: TextStyle(color: Colors.white.withValues(alpha: 0.6), fontSize: 12),
                    ),
                    Text(
                      rank,
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 24),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: AppTheme.accentColor.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  'Next: $nextRank',
                  style: TextStyle(color: AppTheme.accentColor, fontWeight: FontWeight.w600, fontSize: 12),
                ),
              ),
            ],
          ),
          const SizedBox(height: 20),
          Text(
            'Progress to $nextRank',
            style: TextStyle(color: Colors.white.withValues(alpha: 0.7), fontSize: 13),
          ),
          const SizedBox(height: 8),
          ClipRRect(
            borderRadius: BorderRadius.circular(6),
            child: LinearProgressIndicator(
              value: progress,
              minHeight: 8,
              backgroundColor: Colors.white.withValues(alpha: 0.1),
              valueColor: AlwaysStoppedAnimation<Color>(AppTheme.accentColor),
            ),
          ),
          const SizedBox(height: 8),
          Text(
            '${(progress * 100).toInt()}% Complete',
            style: TextStyle(color: AppTheme.accentColor, fontWeight: FontWeight.w600, fontSize: 12),
          ),
        ],
      ),
    );
  }

  Widget _buildStatsGrid() {
    final stats = [
      {'label': 'Total Team', 'value': _networkData!['total_team']?.toString() ?? '0', 'icon': Icons.people_rounded, 'color': Color(0xFF2196F3)},
      {'label': 'Direct Referrals', 'value': _networkData!['direct_referrals']?.toString() ?? '0', 'icon': Icons.person_add_rounded, 'color': Color(0xFF4CAF50)},
      {'label': 'Active Members', 'value': _networkData!['active_members']?.toString() ?? '0', 'icon': Icons.check_circle_rounded, 'color': Color(0xFF66BB6A)},
      {'label': 'Team Business', 'value': _formatCurrency(_networkData!['team_business_volume'] as num? ?? 0), 'icon': Icons.trending_up_rounded, 'color': Color(0xFFFF9800)},
    ];

    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        childAspectRatio: 1.2,
        crossAxisSpacing: 14,
        mainAxisSpacing: 14,
      ),
      itemCount: stats.length,
      itemBuilder: (context, index) {
        final stat = stats[index];
        final color = stat['color'] as Color;
        return GlassCard(
          padding: const EdgeInsets.all(16),
          opacity: 0.1,
          blur: 8,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Icon(stat['icon'] as IconData, color: color, size: 24),
              ),
              const SizedBox(height: 12),
              Text(
                stat['value'] as String,
                style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 18),
              ),
              const SizedBox(height: 4),
              Text(
                stat['label'] as String,
                style: TextStyle(color: Colors.white.withValues(alpha: 0.6), fontSize: 11),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildTeamVolumeCard() {
    final volume = _networkData!['team_business_volume'] as num? ?? 0;
    return GlassCard(
      padding: const EdgeInsets.all(20),
      opacity: 0.1,
      blur: 8,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: const Color(0xFFFF9800).withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.attach_money_rounded, color: Color(0xFFFF9800), size: 22),
              ),
              const SizedBox(width: 12),
              Text(
                'Team Business Volume',
                style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 15),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Text(
            _formatCurrency(volume),
            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 28),
          ),
          const SizedBox(height: 4),
          Text(
            'Total business generated by your downline',
            style: TextStyle(color: Colors.white.withValues(alpha: 0.6), fontSize: 12),
          ),
        ],
      ),
    );
  }

  Widget _buildReferralsTab() {
    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(20, 10, 20, 0),
          child: Row(
            children: [
              Text(
                'Direct Referrals (${_directReferrals.length})',
                style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 16),
              ),
              const Spacer(),
              Text(
                '${_directReferrals.where((r) => r['status'] == 'active').length} Active',
                style: TextStyle(color: AppTheme.successColor, fontWeight: FontWeight.w500, fontSize: 13),
              ),
            ],
          ),
        ),
        Expanded(
          child: _directReferrals.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.people_outline_rounded, size: 64, color: Colors.grey.shade400),
                      const SizedBox(height: 16),
                      Text(
                        'No Direct Referrals Yet',
                        style: TextStyle(color: Colors.grey.shade400, fontSize: 18, fontWeight: FontWeight.w600),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'Share your referral code to build your team',
                        style: TextStyle(color: Colors.grey.shade600, fontSize: 14),
                      ),
                    ],
                  ),
                )
              : ListView.builder(
                  padding: const EdgeInsets.fromLTRB(20, 10, 20, 20),
                  itemCount: _directReferrals.length,
                  itemBuilder: (context, index) {
                    final ref = _directReferrals[index];
                    return Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: _buildReferralCard(ref),
                    );
                  },
                ),
        ),
      ],
    );
  }

  Widget _buildReferralCard(Map<String, dynamic> ref) {
    final name = ref['name']?.toString() ?? '';
    final rank = ref['rank']?.toString() ?? 'Associate';
    final joinDate = ref['join_date']?.toString() ?? '';
    final business = ref['business'] as num? ?? 0;
    final status = ref['status']?.toString() ?? 'inactive';

    final statusColor = status == 'active' ? AppTheme.successColor : Colors.grey;
    final statusIcon = status == 'active' ? Icons.check_circle_rounded : Icons.radio_button_unchecked_rounded;

    return GlassCard(
      padding: const EdgeInsets.all(16),
      opacity: 0.1,
      blur: 8,
      child: Row(
        children: [
          CircleAvatar(
            radius: 24,
            backgroundColor: AppTheme.accentColor.withValues(alpha: 0.2),
            child: Text(
              name.isNotEmpty ? name[0].toUpperCase() : '?',
              style: TextStyle(color: AppTheme.accentColor, fontWeight: FontWeight.w600, fontSize: 18),
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  name,
                  style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 14),
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        rank,
                        style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w500),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Text(
                      'Joined: ${_formatDate(joinDate)}',
                      style: TextStyle(color: Colors.white.withValues(alpha: 0.5), fontSize: 10),
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(width: 12),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                _formatCurrency(business),
                style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 13),
              ),
              const SizedBox(height: 4),
              Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(statusIcon, size: 12, color: statusColor),
                  const SizedBox(width: 4),
                  Text(
                    status.toUpperCase(),
                    style: TextStyle(color: statusColor, fontSize: 10, fontWeight: FontWeight.w600),
                  ),
                ],
              ),
            ],
          ),
        ],
      ),
    );
  }

  String _formatCurrency(num value) {
    if (value >= 10000000) {
      return '₹${(value / 10000000).toStringAsFixed(1)} Cr';
    } else if (value >= 100000) {
      return '₹${(value / 100000).toStringAsFixed(1)} L';
    }
    return '₹${value.toString()}';
  }

  String _formatDate(String dateStr) {
    try {
      final dt = DateTime.parse(dateStr);
      const months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
      return '${dt.day} ${months[dt.month]} ${dt.year}';
    } catch (_) {
      return dateStr;
    }
  }
}
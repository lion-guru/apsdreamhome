import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../data/repositories/kyc_repository_provider.dart';

class AboutPage extends ConsumerStatefulWidget {
  const AboutPage({super.key});

  @override
  ConsumerState<AboutPage> createState() => _AboutPageState();
}

class _AboutPageState extends ConsumerState<AboutPage> {
  bool _isLoading = true;
  Map<String, String> _content = {};
  List<Map<String, dynamic>> _team = [];
  Map<String, dynamic> _stats = {};

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    try {
      final api = ref.read(apiServiceProvider);
      final response = await api.get('about');
      final data = response['data'] ?? {};
      if (mounted) {
        setState(() {
          final rawContent = data['content'] ?? {};
          if (rawContent is Map) {
            _content = (rawContent).map(
              (k, v) => MapEntry(k.toString(), v.toString()),
            );
          }
          final rawTeam = data['team'] ?? [];
          if (rawTeam is List) {
            _team = rawTeam
                .map((e) => Map<String, dynamic>.from(e as Map))
                .toList();
          }
          _stats = data['stats'] is Map
              ? Map<String, dynamic>.from(data['stats'] as Map)
              : {};
          _isLoading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('About Us'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(color: AppTheme.primaryColor),
            )
          : RefreshIndicator(
              onRefresh: _loadData,
              color: AppTheme.primaryColor,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                child: Column(
                  children: [
                    _buildHeader(),
                    if (_stats.isNotEmpty) _buildStatsRow(),
                    _buildMissionSection(),
                    if (_team.isNotEmpty) _buildTeamSection(),
                    _buildValuesSection(),
                    const SizedBox(height: 32),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildHeader() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(24, 32, 24, 32),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            AppTheme.primaryColor,
            AppTheme.secondaryColor.withValues(alpha: 0.8),
          ],
        ),
      ),
      child: Column(
        children: [
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.15),
              shape: BoxShape.circle,
            ),
            child: const Icon(Icons.home_work, size: 48, color: Colors.white),
          ),
          const SizedBox(height: 20),
          Text(
            _content['company_name'] ?? 'APS Dream Home',
            style: const TextStyle(
              fontSize: 28,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            _content['tagline'] ?? 'Building Dreams, Creating Communities',
            style: TextStyle(
              fontSize: 14,
              color: Colors.white.withValues(alpha: 0.85),
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildStatsRow() {
    final items = [
      {
        'icon': Icons.location_city,
        'value': '${_stats['projects'] ?? 4}',
        'label': 'Projects',
      },
      {
        'icon': Icons.grid_view,
        'value': '${_stats['plots'] ?? 5000}',
        'label': 'Plots',
      },
      {
        'icon': Icons.people,
        'value': '${_stats['families'] ?? 500}',
        'label': 'Families',
      },
      {
        'icon': Icons.flag,
        'value': '${_stats['colonies'] ?? 4}',
        'label': 'Colonies',
      },
    ];

    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
      child: Card(
        elevation: 4,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 20),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceEvenly,
            children: items.map((item) {
              return Column(
                children: [
                  Icon(
                    item['icon'] as IconData,
                    color: AppTheme.primaryColor,
                    size: 28,
                  ),
                  const SizedBox(height: 8),
                  Text(
                    item['value'] as String,
                    style: const TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                      color: AppTheme.textPrimaryLight,
                    ),
                  ),
                  Text(
                    item['label'] as String,
                    style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                  ),
                ],
              );
            }).toList(),
          ),
        ),
      ),
    );
  }

  Widget _buildMissionSection() {
    final description =
        _content['description'] ??
        _content['mission'] ??
        'APS Dream Home is a premier real estate development company based in Gorakhpur, committed to delivering exceptional residential and commercial properties. With a focus on quality, transparency, and customer satisfaction, we have been transforming landscapes and building communities that stand the test of time.';

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Card(
        elevation: 1,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Row(
                children: [
                  Icon(
                    Icons.flag_outlined,
                    color: AppTheme.primaryColor,
                    size: 20,
                  ),
                  SizedBox(width: 8),
                  Text(
                    'Our Mission',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                ],
              ),
              const Divider(height: 24),
              Text(
                description,
                style: TextStyle(
                  fontSize: 14,
                  color: Colors.grey.shade700,
                  height: 1.6,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildTeamSection() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Padding(
            padding: EdgeInsets.only(left: 4, bottom: 12),
            child: Row(
              children: [
                Icon(
                  Icons.group_outlined,
                  color: AppTheme.primaryColor,
                  size: 20,
                ),
                SizedBox(width: 8),
                Text(
                  'Our Team',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                ),
              ],
            ),
          ),
          SizedBox(
            height: 200,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              itemCount: _team.length,
              separatorBuilder: (_, _) => const SizedBox(width: 12),
              itemBuilder: (context, index) {
                final member = _team[index];
                return Container(
                  width: 150,
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.06),
                        blurRadius: 8,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Container(
                        width: 64,
                        height: 64,
                        decoration: BoxDecoration(
                          color: AppTheme.primaryColor.withValues(alpha: 0.1),
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(
                          Icons.person,
                          color: AppTheme.primaryColor,
                          size: 32,
                        ),
                      ),
                      const SizedBox(height: 12),
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 8),
                        child: Text(
                          member['name']?.toString() ?? '',
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 13,
                          ),
                          textAlign: TextAlign.center,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        member['designation']?.toString() ?? '',
                        style: TextStyle(
                          fontSize: 11,
                          color: Colors.grey.shade600,
                        ),
                        textAlign: TextAlign.center,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildValuesSection() {
    final values = [
      {
        'icon': Icons.verified,
        'title': 'Trust & Transparency',
        'desc': 'Clear communication and honest dealings',
      },
      {
        'icon': Icons.verified,
        'title': 'Quality First',
        'desc': 'Premium construction and materials',
      },
      {
        'icon': Icons.access_time,
        'title': 'Timely Delivery',
        'desc': 'On-time project completion guaranteed',
      },
      {
        'icon': Icons.support_agent,
        'title': 'Customer Focus',
        'desc': 'Your satisfaction is our priority',
      },
    ];

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Padding(
            padding: EdgeInsets.only(left: 4, bottom: 12),
            child: Row(
              children: [
                Icon(
                  Icons.star_outlined,
                  color: AppTheme.primaryColor,
                  size: 20,
                ),
                SizedBox(width: 8),
                Text(
                  'Our Values',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                ),
              ],
            ),
          ),
          ...values.map(
            (v) => Padding(
              padding: const EdgeInsets.only(bottom: 12),
              child: Card(
                elevation: 0,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                  side: BorderSide(color: Colors.grey.shade200),
                ),
                child: ListTile(
                  leading: Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: AppTheme.primaryColor.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Icon(
                      v['icon'] as IconData,
                      color: AppTheme.primaryColor,
                      size: 22,
                    ),
                  ),
                  title: Text(
                    v['title'] as String,
                    style: const TextStyle(fontWeight: FontWeight.w600),
                  ),
                  subtitle: Text(
                    v['desc'] as String,
                    style: TextStyle(color: Colors.grey.shade600, fontSize: 12),
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

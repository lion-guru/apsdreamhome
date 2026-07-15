import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/services/ai_agent_service.dart';

/// AI Agent Dashboard — main hub for AI features
/// Shows stats, quick actions, agent roles, analytics, recent activity
class AIAgentDashboardPage extends ConsumerStatefulWidget {
  const AIAgentDashboardPage({super.key});

  @override
  ConsumerState<AIAgentDashboardPage> createState() =>
      _AIAgentDashboardPageState();
}

class _AIAgentDashboardPageState extends ConsumerState<AIAgentDashboardPage> {
  Map<String, dynamic> _stats = {};
  Map<String, dynamic> _analytics = {};
  final List<Map<String, dynamic>> _recentActivity = [];
  bool _isLoading = true;
  Timer? _refreshTimer;

  @override
  void initState() {
    super.initState();
    _loadData();
    // Auto-refresh every 30 seconds
    _refreshTimer = Timer.periodic(const Duration(seconds: 30), (_) {
      if (mounted) _loadData(silent: true);
    });
  }

  @override
  void dispose() {
    _refreshTimer?.cancel();
    super.dispose();
  }

  Future<void> _loadData({bool silent = false}) async {
    if (!silent) setState(() => _isLoading = true);

    try {
      final agent = AIAgentService();
      final statsFuture = agent.getAgentStats(agentId: 'mobile_dashboard');
      final analyticsFuture = agent.getAnalytics();
      final voiceStatsFuture = agent.getVoiceStats();

      final results = await Future.wait([
        statsFuture,
        analyticsFuture,
        voiceStatsFuture,
      ]);

      if (mounted) {
        setState(() {
          _stats = results[0];
          _analytics = results[1];
          // Merge voice stats
          if (results[2].isNotEmpty) {
            _stats = {..._stats, ...results[2]};
          }
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(6),
              decoration: BoxDecoration(
                color: Colors.deepPurple.shade100,
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Icon(
                Icons.smart_toy,
                size: 20,
                color: Colors.deepPurple,
              ),
            ),
            const SizedBox(width: 10),
            const Text('AI Agent'),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.phone_in_talk),
            tooltip: 'Auto-Dialer',
            onPressed: () => context.push('/auto-dialer'),
          ),
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => _loadData(),
          ),
          IconButton(
            icon: const Icon(Icons.settings),
            onPressed: () => context.push('/ai-chat'),
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadData,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildStatusBanner(theme),
                    const SizedBox(height: 16),
                    _buildStatsGrid(),
                    const SizedBox(height: 16),
                    _buildQuickActions(theme),
                    const SizedBox(height: 16),
                    _buildAgentRoles(theme),
                    const SizedBox(height: 16),
                    _buildAnalyticsCard(theme),
                    const SizedBox(height: 16),
                    _buildRecentActivity(theme),
                    const SizedBox(height: 80),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildStatusBanner(ThemeData theme) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [Colors.deepPurple.shade700, Colors.indigo.shade600],
        ),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.2),
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(Icons.smart_toy, color: Colors.white, size: 28),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'AI Agent Active',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  'Powered by AIGateway • All engines free',
                  style: TextStyle(
                    color: Colors.white.withValues(alpha: 0.8),
                    fontSize: 13,
                  ),
                ),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            decoration: BoxDecoration(
              color: Colors.green.shade400,
              borderRadius: BorderRadius.circular(20),
            ),
            child: const Text(
              'ONLINE',
              style: TextStyle(
                color: Colors.white,
                fontSize: 12,
                fontWeight: FontWeight.bold,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatsGrid() {
    final totalInteractions = _stats['totalInteractions'] ?? 0;
    final leadsProcessed = _stats['leadsProcessed'] ?? 0;
    final callsHandled = _stats['callsHandled'] ?? 0;
    final satisfaction = _stats['satisfactionScore'] ?? 4.2;
    final totalChats = _analytics['total_chats'] ?? 0;
    final avgConfidence = _analytics['avg_confidence'] ?? 0;

    return GridView.count(
      crossAxisCount: 3,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      mainAxisSpacing: 8,
      crossAxisSpacing: 8,
      childAspectRatio: 1.1,
      children: [
        _buildStatCard(
          'AI Chats',
          '$totalChats',
          Icons.chat_bubble_outline,
          Colors.blue,
        ),
        _buildStatCard(
          'Leads Scored',
          '$leadsProcessed',
          Icons.analytics_outlined,
          Colors.orange,
        ),
        _buildStatCard(
          'Calls Handled',
          '$callsHandled',
          Icons.phone_in_talk,
          Colors.green,
        ),
        _buildStatCard(
          'Interactions',
          '$totalInteractions',
          Icons.smart_toy_outlined,
          Colors.purple,
        ),
        _buildStatCard(
          'Confidence',
          '${avgConfidence.toStringAsFixed(0)}%',
          Icons.check_circle_outline,
          Colors.teal,
        ),
        _buildStatCard(
          'Satisfaction',
          (satisfaction as num).toStringAsFixed(1),
          Icons.thumb_up_outlined,
          Colors.amber,
        ),
      ],
    );
  }

  Widget _buildStatCard(
    String title,
    String value,
    IconData icon,
    Color color,
  ) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(10),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, color: color, size: 22),
            const SizedBox(height: 6),
            Text(
              value,
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 2),
            Text(
              title,
              style: TextStyle(fontSize: 10, color: Colors.grey[600]),
              textAlign: TextAlign.center,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildQuickActions(ThemeData theme) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Quick Actions',
          style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: _buildActionCard(
                'AI Chat',
                Icons.chat,
                Colors.blue,
                () => context.push('/ai-chat'),
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: _buildActionCard(
                'Score Lead',
                Icons.analytics,
                Colors.orange,
                () => _showScoreLeadDialog(),
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: _buildActionCard(
                'Analyze Property',
                Icons.home_work,
                Colors.green,
                () => _showAnalyzePropertyDialog(),
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildActionCard(
    String label,
    IconData icon,
    Color color,
    VoidCallback onTap,
  ) {
    return Card(
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 8),
          child: Column(
            children: [
              Icon(icon, color: color, size: 28),
              const SizedBox(height: 8),
              Text(
                label,
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                ),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildAgentRoles(ThemeData theme) {
    final roles = [
      {
        'name': 'Customer Support',
        'icon': Icons.support_agent,
        'color': Colors.blue,
        'role': AIAgentRole.customerSupport,
        'desc': 'Handle inquiries & complaints',
      },
      {
        'name': 'Sales Assistant',
        'icon': Icons.trending_up,
        'color': Colors.green,
        'role': AIAgentRole.salesAssistant,
        'desc': 'Convert leads & close deals',
      },
      {
        'name': 'Property Expert',
        'icon': Icons.home,
        'color': Colors.orange,
        'role': AIAgentRole.propertyExpert,
        'desc': 'Property analysis & valuation',
      },
      {
        'name': 'Investment Advisor',
        'icon': Icons.show_chart,
        'color': Colors.purple,
        'role': AIAgentRole.investmentAdvisor,
        'desc': 'ROI & investment insights',
      },
    ];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'AI Agents',
          style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 12),
        ...roles.map((r) => _buildAgentCard(r)),
      ],
    );
  }

  Widget _buildAgentCard(Map<String, dynamic> agent) {
    final color = agent['color'] as Color;

    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        leading: Container(
          padding: const EdgeInsets.all(10),
          decoration: BoxDecoration(
            color: color.withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(10),
          ),
          child: Icon(agent['icon'] as IconData, color: color, size: 24),
        ),
        title: Text(
          agent['name'] as String,
          style: const TextStyle(fontWeight: FontWeight.w600),
        ),
        subtitle: Text(
          agent['desc'] as String,
          style: TextStyle(fontSize: 12, color: Colors.grey[600]),
        ),
        trailing: Container(
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
          decoration: BoxDecoration(
            color: Colors.green.withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(12),
          ),
          child: const Text(
            'Active',
            style: TextStyle(fontSize: 11, color: Colors.green),
          ),
        ),
        onTap: () {
          // Navigate to chat with this role
          context.push('/ai-chat');
        },
      ),
    );
  }

  Widget _buildAnalyticsCard(ThemeData theme) {
    final rawDist = _analytics['engine_distribution'];
    final engineDist = (rawDist is Map)
        ? rawDist.cast<String, dynamic>()
        : <String, dynamic>{};
    final rawIntents = _analytics['top_intents'];
    final topIntents = (rawIntents is List)
        ? rawIntents.cast<Map<String, dynamic>>()
        : <Map<String, dynamic>>[];

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                const Icon(Icons.bar_chart, color: Colors.deepPurple),
                const SizedBox(width: 8),
                const Text(
                  'AI Analytics',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
                const Spacer(),
                Text(
                  'Last 7 days',
                  style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                ),
              ],
            ),
            const SizedBox(height: 16),
            if (engineDist.isNotEmpty) ...[
              const Text(
                'Engine Usage',
                style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
              ),
              const SizedBox(height: 8),
              ...engineDist.entries.take(5).map((e) {
                final vals = engineDist.values
                    .map((v) => (v as num).toInt())
                    .toList();
                final total = vals.fold<int>(0, (a, b) => a + b);
                final pct = total > 0 ? (e.value as num).toInt() / total : 0.0;
                return Padding(
                  padding: const EdgeInsets.only(bottom: 6),
                  child: Row(
                    children: [
                      SizedBox(
                        width: 100,
                        child: Text(
                          e.key.toString(),
                          style: const TextStyle(fontSize: 12),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: LinearProgressIndicator(
                          value: pct.toDouble(),
                          backgroundColor: Colors.grey[200],
                          valueColor: AlwaysStoppedAnimation<Color>(
                            Colors.deepPurple.shade300,
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Text('${e.value}', style: const TextStyle(fontSize: 12)),
                    ],
                  ),
                );
              }),
            ] else
              const Center(
                child: Padding(
                  padding: EdgeInsets.all(20),
                  child: Text(
                    'No analytics data yet',
                    style: TextStyle(color: Colors.grey),
                  ),
                ),
              ),
            if (topIntents.isNotEmpty) ...[
              const SizedBox(height: 16),
              const Text(
                'Top Intents',
                style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
              ),
              const SizedBox(height: 8),
              Wrap(
                spacing: 8,
                runSpacing: 4,
                children: (topIntents as List).take(5).map((i) {
                  final map = i as Map<String, dynamic>;
                  return Chip(
                    label: Text(
                      '${map['intent']} (${map['count']})',
                      style: const TextStyle(fontSize: 11),
                    ),
                    backgroundColor: Colors.deepPurple.shade50,
                    materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                    visualDensity: VisualDensity.compact,
                  );
                }).toList(),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildRecentActivity(ThemeData theme) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(Icons.history, color: Colors.teal),
                SizedBox(width: 8),
                Text(
                  'Recent Activity',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              ],
            ),
            const SizedBox(height: 16),
            if (_recentActivity.isEmpty)
              Center(
                child: Padding(
                  padding: const EdgeInsets.all(24),
                  child: Column(
                    children: [
                      Icon(
                        Icons.smart_toy_outlined,
                        size: 48,
                        color: Colors.grey[300],
                      ),
                      const SizedBox(height: 12),
                      const Text(
                        'No activity yet',
                        style: TextStyle(color: Colors.grey),
                      ),
                      const SizedBox(height: 4),
                      const Text(
                        'Start a conversation with AI Agent',
                        style: TextStyle(fontSize: 12, color: Colors.grey),
                      ),
                    ],
                  ),
                ),
              )
            else
              ..._recentActivity.take(5).map((a) => _buildActivityTile(a)),
          ],
        ),
      ),
    );
  }

  Widget _buildActivityTile(Map<String, dynamic> activity) {
    final type = activity['type'] ?? 'chat';
    final icon = type == 'chat'
        ? Icons.chat_bubble_outline
        : type == 'lead'
        ? Icons.analytics_outlined
        : Icons.smart_toy_outlined;
    final color = type == 'chat'
        ? Colors.blue
        : type == 'lead'
        ? Colors.orange
        : Colors.purple;

    return ListTile(
      contentPadding: EdgeInsets.zero,
      leading: CircleAvatar(
        backgroundColor: color.withValues(alpha: 0.1),
        child: Icon(icon, color: color, size: 18),
      ),
      title: Text(
        (activity['summary'] ?? 'AI interaction').toString(),
        style: const TextStyle(fontSize: 13),
        maxLines: 1,
        overflow: TextOverflow.ellipsis,
      ),
      subtitle: Text(
        (activity['time'] ?? '').toString(),
        style: TextStyle(fontSize: 11, color: Colors.grey[600]),
      ),
      dense: true,
    );
  }

  void _showScoreLeadDialog() {
    final nameController = TextEditingController();
    final phoneController = TextEditingController();
    final budgetController = TextEditingController();

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Score Lead with AI'),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: nameController,
                decoration: const InputDecoration(
                  labelText: 'Lead Name',
                  border: OutlineInputBorder(),
                  isDense: true,
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: phoneController,
                decoration: const InputDecoration(
                  labelText: 'Phone',
                  border: OutlineInputBorder(),
                  isDense: true,
                ),
                keyboardType: TextInputType.phone,
              ),
              const SizedBox(height: 12),
              TextField(
                controller: budgetController,
                decoration: const InputDecoration(
                  labelText: 'Budget (₹)',
                  border: OutlineInputBorder(),
                  isDense: true,
                ),
                keyboardType: TextInputType.number,
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(context);
              await _scoreLead(
                nameController.text,
                phoneController.text,
                budgetController.text,
              );
            },
            child: const Text('Score'),
          ),
        ],
      ),
    );
  }

  Future<void> _scoreLead(String name, String phone, String budget) async {
    try {
      final agent = AIAgentService();
      final result = await agent.processLead(
        await agent.createAgent(role: AIAgentRole.leadScorer),
        {'name': name, 'phone': phone, 'budget': int.tryParse(budget) ?? 0},
      );

      if (mounted) {
        showDialog(
          context: context,
          builder: (context) => AlertDialog(
            title: const Text('Lead Score'),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildScoreRow('Score', '${result['score']}/100'),
                _buildScoreRow('Priority', '${result['priority']}'),
                _buildScoreRow('Category', '${result['category']}'),
                _buildScoreRow('Next Action', '${result['nextAction']}'),
              ],
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context),
                child: const Text('OK'),
              ),
            ],
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text('Scoring failed: $e')));
      }
    }
  }

  Widget _buildScoreRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(fontWeight: FontWeight.w600)),
          Text(value),
        ],
      ),
    );
  }

  void _showAnalyzePropertyDialog() {
    final priceController = TextEditingController();
    final areaController = TextEditingController();
    final locationController = TextEditingController();

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Analyze Property'),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: priceController,
                decoration: const InputDecoration(
                  labelText: 'Price (₹)',
                  border: OutlineInputBorder(),
                  isDense: true,
                ),
                keyboardType: TextInputType.number,
              ),
              const SizedBox(height: 12),
              TextField(
                controller: areaController,
                decoration: const InputDecoration(
                  labelText: 'Area (sqft)',
                  border: OutlineInputBorder(),
                  isDense: true,
                ),
                keyboardType: TextInputType.number,
              ),
              const SizedBox(height: 12),
              TextField(
                controller: locationController,
                decoration: const InputDecoration(
                  labelText: 'Location',
                  border: OutlineInputBorder(),
                  isDense: true,
                ),
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(context);
              await _analyzeProperty(
                priceController.text,
                areaController.text,
                locationController.text,
              );
            },
            child: const Text('Analyze'),
          ),
        ],
      ),
    );
  }

  Future<void> _analyzeProperty(
    String price,
    String area,
    String location,
  ) async {
    try {
      final agent = AIAgentService();
      final result = await agent.analyzeProperty(
        await agent.createAgent(role: AIAgentRole.propertyExpert),
        {
          'price': int.tryParse(price) ?? 0,
          'area': int.tryParse(area) ?? 0,
          'location': location,
        },
      );

      if (mounted) {
        showDialog(
          context: context,
          builder: (context) => AlertDialog(
            title: const Text('Property Analysis'),
            content: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildScoreRow(
                    'Estimated Value',
                    '₹${result['estimatedValue']}',
                  ),
                  _buildScoreRow('Market Trend', '${result['marketTrend']}'),
                  _buildScoreRow(
                    'Investment Potential',
                    '${result['investmentPotential']}',
                  ),
                  _buildScoreRow('Price/sqft', '₹${result['pricePerSqft']}'),
                  _buildScoreRow(
                    'Expected Appreciation',
                    '${result['expectedAppreciation']}',
                  ),
                  _buildScoreRow(
                    'Recommendation',
                    '${result['recommendation']}',
                  ),
                ],
              ),
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context),
                child: const Text('OK'),
              ),
            ],
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text('Analysis failed: $e')));
      }
    }
  }
}

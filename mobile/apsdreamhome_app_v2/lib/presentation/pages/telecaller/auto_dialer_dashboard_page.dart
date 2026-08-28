import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../../core/services/auto_dialer_service.dart';

class AutoDialerDashboardPage extends ConsumerStatefulWidget {
  const AutoDialerDashboardPage({super.key});

  @override
  ConsumerState<AutoDialerDashboardPage> createState() =>
      _AutoDialerDashboardPageState();
}

class _AutoDialerDashboardPageState
    extends ConsumerState<AutoDialerDashboardPage> {
  final AutoDialerService _dialerService = AutoDialerService();
  bool _isLoading = true;
  Map<String, dynamic> _stats = {};
  List<Map<String, dynamic>> _scheduledCalls = [];
  List<Map<String, dynamic>> _callHistory = [];
  Map<String, dynamic> _callAnalytics = {};
  int _selectedTab = 0;
  bool _isProcessing = false;
  bool _isAiScheduling = false;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    try {
      final statsResult = await _dialerService.getStats();
      final scheduleResult = await _dialerService.getSchedule();
      final historyResult = await _dialerService.getCallHistory();
      final analyticsResult = await _dialerService.callStats(days: 30);
      if (!mounted) return;
      setState(() {
        _stats = (statsResult['data'] as Map<String, dynamic>?) ?? {};
        _callAnalytics =
            (analyticsResult['data'] as Map<String, dynamic>?) ?? {};
        _scheduledCalls = scheduleResult
            .map((e) => Map<String, dynamic>.from(e))
            .toList();
        _callHistory = historyResult
            .map((e) => Map<String, dynamic>.from(e))
            .toList();
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() => _isLoading = false);
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text('Failed to load data: $e')));
    }
  }

  Future<void> _aiAutoSchedule() async {
    setState(() => _isAiScheduling = true);
    try {
      final result = await _dialerService.aiSchedule(minScore: 70, limit: 10);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              (result['message'] ?? 'AI scheduling done').toString(),
            ),
            backgroundColor: (result['success'] == true)
                ? Colors.green
                : Colors.red,
          ),
        );
        _loadData();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
        );
      }
    } finally {
      if (mounted) setState(() => _isAiScheduling = false);
    }
  }

  Future<void> _processQueue() async {
    setState(() => _isProcessing = true);
    try {
      final result = await _dialerService.processQueue();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text((result['message'] ?? 'Queue processed').toString()),
            backgroundColor: (result['success'] == true)
                ? Colors.green
                : Colors.red,
          ),
        );
        _loadData();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
        );
      }
    } finally {
      if (mounted) setState(() => _isProcessing = false);
    }
  }

  Future<void> _dialPhone(String phone, String name) async {
    final uri = Uri(scheme: 'tel', path: phone);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
      if (mounted) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text('Calling $name...')));
      }
    } else if (mounted) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('Cannot open phone dialer')));
    }
  }

  Future<void> _openWhatsApp(String phone, String name) async {
    final cleanPhone = phone.replaceAll(RegExp(r'[^0-9]'), '');
    final uri = Uri.parse('https://wa.me/$cleanPhone');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    } else if (mounted) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('WhatsApp not installed')));
    }
  }

  Future<void> _sendSms(String phone, String name) async {
    final uri = Uri(scheme: 'sms', path: phone);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
      if (mounted) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text('Opening SMS for $name...')));
      }
    }
  }

  Future<void> _cancelCall(int id) async {
    try {
      final result = await _dialerService.cancelSchedule(id);
      if (mounted && result['success'] == true) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(const SnackBar(content: Text('Call cancelled')));
        _loadData();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text('Failed to cancel: $e')));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0D1B2A),
      appBar: AppBar(
        backgroundColor: const Color(0xFF1B2838),
        title: const Text('Auto-Dialer'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => Navigator.of(context).pop(),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.message),
            tooltip: 'Templates',
            onPressed: () => context.push('/auto-dialer/templates'),
          ),
          IconButton(
            icon: const Icon(Icons.playlist_add),
            tooltip: 'Bulk Operations',
            onPressed: () => context.push('/auto-dialer/bulk'),
          ),
          IconButton(
            icon: const Icon(Icons.graphic_eq),
            tooltip: 'Voice AI Agent',
            onPressed: () => context.push('/auto-dialer/voice'),
          ),
          IconButton(
            icon: Icon(
              _isProcessing ? Icons.hourglass_empty : Icons.play_arrow,
            ),
            tooltip: 'Process Queue',
            onPressed: _isProcessing ? null : _processQueue,
          ),
          IconButton(
            icon: const Icon(Icons.refresh),
            tooltip: 'Refresh',
            onPressed: _loadData,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: Colors.amber))
          : Column(
              children: [
                _buildStatsCards(),
                _buildAnalyticsCard(),
                _buildTabBar(),
                Expanded(
                  child: _selectedTab == 0
                      ? _buildScheduledCalls()
                      : _buildCallHistory(),
                ),
              ],
            ),
      bottomNavigationBar: _buildBottomActions(),
    );
  }

  Widget _buildStatsCards() {
    return Container(
      padding: const EdgeInsets.all(12),
      child: Row(
        children: [
          _statCard(
            'Scheduled',
            '${_stats['pending_today'] ?? 0}',
            Icons.schedule,
            Colors.blue,
          ),
          _statCard(
            'Completed',
            '${_stats['completed_today'] ?? 0}',
            Icons.check_circle,
            Colors.green,
          ),
          _statCard(
            'Failed',
            '${_stats['failed_today'] ?? 0}',
            Icons.error,
            Colors.red,
          ),
          _statCard(
            'Connected',
            '${_stats['connected'] ?? 0}',
            Icons.phone,
            Colors.amber,
          ),
        ],
      ),
    );
  }

  Widget _buildAnalyticsCard() {
    final totals = (_callAnalytics['totals'] as Map<String, dynamic>?) ?? {};
    final total = int.tryParse('${totals['total'] ?? 0}') ?? 0;
    final connected = int.tryParse('${totals['connected'] ?? 0}') ?? 0;
    final whatsapp = int.tryParse('${totals['whatsapp'] ?? 0}') ?? 0;
    final sms = int.tryParse('${totals['sms'] ?? 0}') ?? 0;
    final outcomes = (_callAnalytics['outcomes'] as List?) ?? [];

    return Container(
      margin: const EdgeInsets.fromLTRB(12, 0, 12, 8),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: const Color(0xFF1B2838),
        borderRadius: BorderRadius.circular(14),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Call Analytics (30d)',
                style: TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.bold,
                  fontSize: 14,
                ),
              ),
              GestureDetector(
                onTap: () => context.push('/auto-dialer/voice'),
                child: const Text(
                  'Voice AI',
                  style: TextStyle(color: Colors.lightBlueAccent, fontSize: 12),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              _miniStat('Total', total, Colors.blue),
              _miniStat('Connected', connected, Colors.green),
              _miniStat('WhatsApp', whatsapp, Colors.teal),
              _miniStat('SMS', sms, Colors.orange),
            ],
          ),
          if (outcomes.isNotEmpty) ...[
            const SizedBox(height: 12),
            const Text(
              'Outcomes',
              style: TextStyle(color: Colors.white70, fontSize: 12),
            ),
            const SizedBox(height: 6),
            Wrap(
              spacing: 6,
              runSpacing: 6,
              children: outcomes.map((o) {
                final name = '${o['outcome'] ?? ''}';
                final count = int.tryParse('${o['total'] ?? 0}') ?? 0;
                return Chip(
                  label: Text(
                    '${_prettyOutcome(name)}: $count',
                    style: const TextStyle(color: Colors.white, fontSize: 11),
                  ),
                  backgroundColor: _outcomeColor(name).withValues(alpha: 0.25),
                  side: BorderSide(color: _outcomeColor(name)),
                );
              }).toList(),
            ),
          ],
        ],
      ),
    );
  }

  Widget _miniStat(String label, int value, Color color) {
    return Column(
      children: [
        Text(
          '$value',
          style: TextStyle(
            color: color,
            fontSize: 20,
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(height: 2),
        Text(
          label,
          style: const TextStyle(color: Colors.white70, fontSize: 11),
        ),
      ],
    );
  }

  String _prettyOutcome(String o) {
    return o
        .replaceAll('_', ' ')
        .split(' ')
        .map((w) => w.isEmpty ? w : w[0].toUpperCase() + w.substring(1))
        .join(' ');
  }

  Widget _statCard(String label, String value, IconData icon, Color color) {
    return Expanded(
      child: Card(
        color: const Color(0xFF1B2838),
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 6),
          child: Column(
            children: [
              Icon(icon, color: color, size: 20),
              const SizedBox(height: 4),
              Text(
                value,
                style: TextStyle(
                  color: color,
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                ),
              ),
              Text(
                label,
                style: const TextStyle(color: Colors.white70, fontSize: 10),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildTabBar() {
    return Container(
      color: const Color(0xFF1B2838),
      child: Row(
        children: [_tabButton('Scheduled', 0), _tabButton('History', 1)],
      ),
    );
  }

  Widget _tabButton(String label, int index) {
    final isSelected = _selectedTab == index;
    return Expanded(
      child: GestureDetector(
        onTap: () => setState(() => _selectedTab = index),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 12),
          decoration: BoxDecoration(
            border: Border(
              bottom: BorderSide(
                color: isSelected ? Colors.amber : Colors.transparent,
                width: 2,
              ),
            ),
          ),
          child: Text(
            label,
            textAlign: TextAlign.center,
            style: TextStyle(
              color: isSelected ? Colors.amber : Colors.white70,
              fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildScheduledCalls() {
    if (_scheduledCalls.isEmpty) {
      return const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.schedule, size: 48, color: Colors.white30),
            SizedBox(height: 16),
            Text(
              'No scheduled calls',
              style: TextStyle(color: Colors.white54, fontSize: 16),
            ),
          ],
        ),
      );
    }
    return RefreshIndicator(
      onRefresh: _loadData,
      child: ListView.builder(
        padding: const EdgeInsets.all(12),
        itemCount: _scheduledCalls.length,
        itemBuilder: (context, index) =>
            _buildCallCard(_scheduledCalls[index], isScheduled: true),
      ),
    );
  }

  Widget _buildCallHistory() {
    if (_callHistory.isEmpty) {
      return const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.history, size: 48, color: Colors.white30),
            SizedBox(height: 16),
            Text(
              'No call history',
              style: TextStyle(color: Colors.white54, fontSize: 16),
            ),
          ],
        ),
      );
    }
    return ListView.builder(
      padding: const EdgeInsets.all(12),
      itemCount: _callHistory.length,
      itemBuilder: (context, index) =>
          _buildCallCard(_callHistory[index], isScheduled: false),
    );
  }

  Widget _buildCallCard(
    Map<String, dynamic> call, {
    required bool isScheduled,
  }) {
    final name = (call['lead_name'] ?? call['name'] ?? 'Unknown').toString();
    final phone = (call['lead_phone'] ?? call['phone'] ?? '').toString();
    final status = (call['status'] ?? 'pending').toString();
    final priority = (call['priority'] ?? 'medium').toString();
    final scheduledDate = (call['scheduled_date'] ?? '').toString();
    final scheduledTime = (call['scheduled_time'] ?? '').toString();
    final outcome = (call['call_outcome'] ?? '').toString();

    Color statusColor;
    IconData statusIcon;
    switch (status) {
      case 'completed':
        statusColor = Colors.green;
        statusIcon = Icons.check_circle;
        break;
      case 'failed':
        statusColor = Colors.red;
        statusIcon = Icons.error;
        break;
      case 'cancelled':
        statusColor = Colors.grey;
        statusIcon = Icons.cancel;
        break;
      default:
        statusColor = Colors.amber;
        statusIcon = Icons.schedule;
    }

    return Card(
      color: const Color(0xFF1B2838),
      margin: const EdgeInsets.only(bottom: 8),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                CircleAvatar(
                  backgroundColor: statusColor.withValues(alpha: 0.2),
                  child: Icon(statusIcon, color: statusColor, size: 18),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        name,
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      Text(
                        phone,
                        style: const TextStyle(
                          color: Colors.white70,
                          fontSize: 13,
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 3,
                  ),
                  decoration: BoxDecoration(
                    color: _priorityColor(priority).withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    priority.toUpperCase(),
                    style: TextStyle(
                      color: _priorityColor(priority),
                      fontSize: 10,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ],
            ),
            if (scheduledDate.isNotEmpty) ...[
              const SizedBox(height: 8),
              Row(
                children: [
                  const Icon(
                    Icons.calendar_today,
                    size: 14,
                    color: Colors.white54,
                  ),
                  const SizedBox(width: 6),
                  Text(
                    '$scheduledDate $scheduledTime',
                    style: const TextStyle(color: Colors.white70, fontSize: 12),
                  ),
                  if (outcome.isNotEmpty) ...[
                    const SizedBox(width: 12),
                    const Icon(
                      Icons.info_outline,
                      size: 14,
                      color: Colors.white54,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      outcome,
                      style: TextStyle(
                        color: _outcomeColor(outcome),
                        fontSize: 12,
                      ),
                    ),
                  ],
                ],
              ),
            ],
            if (isScheduled) ...[
              const SizedBox(height: 8),
              Row(
                children: [
                  _actionButton('Call', Icons.phone, Colors.green, () {
                    _dialPhone(phone, name);
                  }),
                  const SizedBox(width: 8),
                  _actionButton(
                    'WhatsApp',
                    Icons.chat,
                    Colors.green.shade700,
                    () {
                      _openWhatsApp(phone, name);
                    },
                  ),
                  const SizedBox(width: 8),
                  _actionButton('SMS', Icons.sms, Colors.blue, () {
                    _sendSms(phone, name);
                  }),
                  const Spacer(),
                  if (status == 'pending')
                    _actionButton('Cancel', Icons.close, Colors.red, () {
                      final id = call['id'];
                      if (id is int) _cancelCall(id);
                    }),
                ],
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _actionButton(
    String label,
    IconData icon,
    Color color,
    VoidCallback onPressed,
  ) {
    return SizedBox(
      height: 32,
      child: ElevatedButton.icon(
        onPressed: onPressed,
        icon: Icon(icon, size: 14),
        label: Text(label, style: const TextStyle(fontSize: 11)),
        style: ElevatedButton.styleFrom(
          backgroundColor: color.withValues(alpha: 0.2),
          foregroundColor: color,
          padding: const EdgeInsets.symmetric(horizontal: 10),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
        ),
      ),
    );
  }

  Widget _buildBottomActions() {
    return Container(
      color: const Color(0xFF1B2838),
      padding: const EdgeInsets.all(12),
      child: Row(
        children: [
          Expanded(
            child: ElevatedButton.icon(
              onPressed: _isAiScheduling ? null : _aiAutoSchedule,
              icon: _isAiScheduling
                  ? const SizedBox(
                      width: 16,
                      height: 16,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        color: Colors.white,
                      ),
                    )
                  : const Icon(Icons.auto_awesome),
              label: Text(_isAiScheduling ? 'Scoring...' : 'AI Auto-Schedule'),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.deepPurple.shade700,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 12),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: ElevatedButton.icon(
              onPressed: _isProcessing ? null : _processQueue,
              icon: _isProcessing
                  ? const SizedBox(
                      width: 16,
                      height: 16,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        color: Colors.white,
                      ),
                    )
                  : const Icon(Icons.play_arrow),
              label: Text(_isProcessing ? 'Processing...' : 'Process Queue'),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.amber.shade700,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 12),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Color _priorityColor(String priority) {
    switch (priority) {
      case 'high':
        return Colors.red;
      case 'medium':
        return Colors.amber;
      case 'low':
        return Colors.green;
      default:
        return Colors.white70;
    }
  }

  Color _outcomeColor(String outcome) {
    switch (outcome) {
      case 'connected':
        return Colors.green;
      case 'not_answered':
        return Colors.orange;
      case 'busy':
        return Colors.red;
      case 'call_later':
        return Colors.blue;
      default:
        return Colors.white70;
    }
  }
}

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/services/api_service.dart';
import '../../../core/utils/logger.dart';
import '../../../core/theme/app_theme.dart';

/// Campaign Management - Marketing Campaigns, UTM Tracking, ROI
class CampaignManagementPage extends ConsumerStatefulWidget {
  const CampaignManagementPage({super.key});

  @override
  ConsumerState<CampaignManagementPage> createState() =>
      _CampaignManagementPageState();
}

class _CampaignManagementPageState
    extends ConsumerState<CampaignManagementPage> {
  String _filterStatus = 'all';

  @override
  Widget build(BuildContext context) {
    final campaignsAsync = ref.watch(_campaignsProvider);

    return RefreshIndicator(
      onRefresh: () async => ref.invalidate(_campaignsProvider),
      child: Stack(
        children: [
          Column(
            children: [
              _buildHeader(),
              _buildCampaignStats(campaignsAsync),
              Expanded(
                child: campaignsAsync.when(
                  loading: () =>
                      const Center(child: CircularProgressIndicator()),
                  error: (e, _) => _buildErrorState(e.toString()),
                  data: (campaigns) => _buildCampaignBody(campaigns),
                ),
              ),
            ],
          ),
          Positioned(
            right: 16,
            bottom: 16,
            child: FloatingActionButton.extended(
              onPressed: () => _showCreateCampaignDialog(),
              icon: const Icon(Icons.add),
              label: const Text('New Campaign'),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
          ),
        ],
      ),
      child: const Row(
        children: [
          Icon(Icons.campaign, size: 32, color: AppTheme.primaryColor),
          SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Campaign Management',
                  style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
                ),
                SizedBox(height: 4),
                Text(
                  'Create, track and analyze marketing campaigns',
                  style: TextStyle(color: Colors.grey),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCampaignStats(AsyncValue<List<Map<String, dynamic>>> asyncData) {
    final campaigns = asyncData.value ?? [];
    final activeCount = campaigns.where((c) => c['status'] == 'active').length;
    final totalLeads = campaigns.fold<int>(
      0,
      (sum, c) => sum + ((c['total_leads'] as num?)?.toInt() ?? 0),
    );
    final totalConversions = campaigns.fold<int>(
      0,
      (sum, c) => sum + ((c['total_conversions'] as num?)?.toInt() ?? 0),
    );

    final conversionRate = totalLeads > 0
        ? ((totalConversions / totalLeads) * 100)
        : 0.0;

    return Container(
      padding: const EdgeInsets.all(24),
      child: Row(
        children: [
          _buildStatCard('Active', '$activeCount', Icons.campaign, Colors.blue),
          _buildStatCard(
            'Total Leads',
            '$totalLeads',
            Icons.people,
            Colors.green,
          ),
          _buildStatCard(
            'Conversions',
            '$totalConversions',
            Icons.trending_up,
            Colors.purple,
          ),
          _buildStatCard(
            'Conv. Rate',
            '${conversionRate.toStringAsFixed(1)}%',
            Icons.analytics,
            Colors.orange,
          ),
        ],
      ),
    );
  }

  Widget _buildStatCard(
    String title,
    String value,
    IconData icon,
    Color color,
  ) {
    return Expanded(
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 8),
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: [
              color.withValues(alpha: 0.1),
              color.withValues(alpha: 0.05),
            ],
          ),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: color.withValues(alpha: 0.3)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(icon, color: color, size: 28),
            const SizedBox(height: 16),
            Text(
              value,
              style: TextStyle(
                fontSize: 28,
                fontWeight: FontWeight.bold,
                color: color,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              title,
              style: TextStyle(
                color: Colors.grey.shade600,
                fontWeight: FontWeight.w500,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCampaignBody(List<Map<String, dynamic>> allCampaigns) {
    var campaigns = allCampaigns;
    if (_filterStatus != 'all') {
      campaigns = campaigns.where((c) => c['status'] == _filterStatus).toList();
    }

    return Container(
      margin: const EdgeInsets.only(left: 24, right: 24, bottom: 24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.all(20),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Campaigns (${campaigns.length})',
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                SegmentedButton<String>(
                  segments: const [
                    ButtonSegment(value: 'all', label: Text('All')),
                    ButtonSegment(value: 'active', label: Text('Active')),
                    ButtonSegment(value: 'completed', label: Text('Completed')),
                    ButtonSegment(value: 'draft', label: Text('Draft')),
                  ],
                  selected: {_filterStatus},
                  onSelectionChanged: (value) {
                    setState(() => _filterStatus = value.first);
                  },
                ),
              ],
            ),
          ),
          Expanded(
            child: campaigns.isEmpty
                ? _buildEmptyState()
                : ListView.builder(
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    itemCount: campaigns.length,
                    itemBuilder: (context, index) =>
                        _buildCampaignCard(campaigns[index]),
                  ),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.campaign_outlined, size: 64, color: Colors.grey.shade300),
          const SizedBox(height: 16),
          Text(
            _filterStatus == 'all'
                ? 'No campaigns yet'
                : 'No $_filterStatus campaigns',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: Colors.grey.shade700,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Create your first campaign to start tracking',
            style: TextStyle(color: Colors.grey.shade500),
          ),
        ],
      ),
    );
  }

  Widget _buildErrorState(String message) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.error_outline, size: 48, color: Colors.red.shade300),
          const SizedBox(height: 16),
          Text(
            'Failed to load campaigns',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: Colors.grey.shade800,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            message,
            style: TextStyle(color: Colors.grey.shade600),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 16),
          ElevatedButton.icon(
            onPressed: () => ref.invalidate(_campaignsProvider),
            icon: const Icon(Icons.refresh),
            label: const Text('Retry'),
          ),
        ],
      ),
    );
  }

  Widget _buildCampaignCard(Map<String, dynamic> campaign) {
    final name = (campaign['name'] as String?) ?? 'Untitled';
    final campaignType = (campaign['campaign_type'] as String?) ?? 'other';
    final status = (campaign['status'] as String?) ?? 'draft';
    final leads = (campaign['total_leads'] as num?)?.toInt() ?? 0;
    final conversions = (campaign['total_conversions'] as num?)?.toInt() ?? 0;
    final impressions = (campaign['total_impressions'] as num?)?.toInt() ?? 0;
    final clicks = (campaign['total_clicks'] as num?)?.toInt() ?? 0;
    final budget = (campaign['budget'] as num?)?.toDouble() ?? 0;
    final startDate = (campaign['start_date'] as String?) ?? '';
    final endDate = (campaign['end_date'] as String?) ?? '';

    final isActive = status == 'active';
    final ctr = impressions > 0 ? ((clicks / impressions) * 100) : 0.0;
    final conversionRate = leads > 0 ? ((conversions / leads) * 100) : 0.0;

    final duration = startDate.isNotEmpty && endDate.isNotEmpty
        ? '$startDate - $endDate'
        : startDate.isNotEmpty
        ? 'From $startDate'
        : 'Not scheduled';

    final sourceColors = {
      'google_ads': Colors.orange,
      'facebook_ads': Colors.blue,
      'instagram_ads': Colors.pink,
      'whatsapp_broadcast': Colors.green,
      'sms_blast': Colors.teal,
      'email_blast': Colors.indigo,
      'referral': Colors.green,
      'organic': Colors.blueGrey,
      'event': Colors.purple,
      'other': Colors.grey,
    };

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: isActive ? Colors.white : Colors.grey.shade50,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(
          color: isActive
              ? AppTheme.primaryColor.withValues(alpha: 0.3)
              : Colors.grey.shade200,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: (sourceColors[campaignType] ?? Colors.grey).withValues(
                    alpha: 0.1,
                  ),
                  borderRadius: BorderRadius.circular(4),
                ),
                child: Text(
                  campaignType.replaceAll('_', ' ').toUpperCase(),
                  style: TextStyle(
                    color: sourceColors[campaignType] ?? Colors.grey,
                    fontSize: 10,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
              const Spacer(),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: isActive
                      ? Colors.green.withValues(alpha: 0.1)
                      : Colors.grey.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  status.toUpperCase(),
                  style: TextStyle(
                    color: isActive ? Colors.green : Colors.grey,
                    fontSize: 10,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Text(
            name,
            style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 4),
          Text(
            duration,
            style: TextStyle(color: Colors.grey.shade600, fontSize: 13),
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Expanded(child: _buildMiniStat('Leads', '$leads', Icons.people)),
              Expanded(
                child: _buildMiniStat(
                  'Conv.',
                  '${conversionRate.toStringAsFixed(0)}%',
                  Icons.trending_up,
                ),
              ),
              Expanded(
                child: _buildMiniStat(
                  'CTR',
                  '${ctr.toStringAsFixed(1)}%',
                  Icons.mouse,
                ),
              ),
              Expanded(
                child: _buildMiniStat(
                  'Budget',
                  '₹${(budget / 1000).toStringAsFixed(0)}K',
                  Icons.attach_money,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildMiniStat(String label, String value, IconData icon) {
    return Row(
      children: [
        Icon(icon, size: 16, color: Colors.grey),
        const SizedBox(width: 4),
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              value,
              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
            ),
            Text(
              label,
              style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
            ),
          ],
        ),
      ],
    );
  }

  void _showCreateCampaignDialog() {
    final nameController = TextEditingController();
    final budgetController = TextEditingController();
    String selectedType = 'other';

    showDialog(
      context: context,
      builder: (context) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          title: const Text('Create New Campaign'),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextField(
                  controller: nameController,
                  decoration: const InputDecoration(
                    labelText: 'Campaign Name',
                    hintText: 'e.g., Summer Offer 2026',
                  ),
                ),
                const SizedBox(height: 16),
                DropdownButtonFormField<String>(
                  initialValue: selectedType,
                  decoration: const InputDecoration(labelText: 'Campaign Type'),
                  items: const [
                    DropdownMenuItem(
                      value: 'google_ads',
                      child: Text('Google Ads'),
                    ),
                    DropdownMenuItem(
                      value: 'facebook_ads',
                      child: Text('Facebook Ads'),
                    ),
                    DropdownMenuItem(
                      value: 'instagram_ads',
                      child: Text('Instagram Ads'),
                    ),
                    DropdownMenuItem(
                      value: 'whatsapp_broadcast',
                      child: Text('WhatsApp'),
                    ),
                    DropdownMenuItem(
                      value: 'sms_blast',
                      child: Text('SMS Blast'),
                    ),
                    DropdownMenuItem(
                      value: 'email_blast',
                      child: Text('Email Blast'),
                    ),
                    DropdownMenuItem(
                      value: 'referral',
                      child: Text('Referral'),
                    ),
                    DropdownMenuItem(value: 'organic', child: Text('Organic')),
                    DropdownMenuItem(value: 'event', child: Text('Event')),
                    DropdownMenuItem(value: 'other', child: Text('Other')),
                  ],
                  onChanged: (v) =>
                      setDialogState(() => selectedType = v ?? 'other'),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: budgetController,
                  decoration: const InputDecoration(
                    labelText: 'Budget (₹)',
                    hintText: '25000',
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
                if (nameController.text.trim().isEmpty) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('Please enter a campaign name'),
                    ),
                  );
                  return;
                }
                Navigator.pop(context);
                await _createCampaign(
                  nameController.text.trim(),
                  selectedType,
                  double.tryParse(budgetController.text) ?? 0,
                );
              },
              child: const Text('Create'),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _createCampaign(String name, String type, double budget) async {
    try {
      final response = await ApiService().post(
        '/crm/campaigns',
        data: {
          'name': name,
          'campaign_type': type,
          'budget': budget,
          'status': 'draft',
        },
      );
      if (response['success'] == true) {
        ref.invalidate(_campaignsProvider);
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Campaign created successfully'),
              backgroundColor: Colors.green,
            ),
          );
        }
      }
    } catch (e) {
      AppLogger.error('Failed to create campaign', e);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to create campaign: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }
}

// ─── Providers ────────────────────────────────────────────────────────

final _campaignsProvider = FutureProvider<List<Map<String, dynamic>>>((
  ref,
) async {
  try {
    final response = await ApiService().get('/crm/campaigns');
    if (response['success'] == true) {
      final campaigns = (response['campaigns'] as List<dynamic>?) ?? [];
      return campaigns.map((c) => Map<String, dynamic>.from(c as Map)).toList();
    }
    return [];
  } catch (e) {
    AppLogger.error('Failed to fetch campaigns', e);
    return [];
  }
});

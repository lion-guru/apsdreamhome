import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:qr_flutter/qr_flutter.dart';

import '../../../core/theme/app_theme.dart';
import '../../widgets/app_widgets.dart';

/// Campaign Management - Marketing Campaigns, UTM Tracking, ROI
class CampaignManagementPage extends ConsumerStatefulWidget {
  const CampaignManagementPage({super.key});

  @override
  ConsumerState<CampaignManagementPage> createState() =>
      _CampaignManagementPageState();
}

class _CampaignManagementPageState
    extends ConsumerState<CampaignManagementPage> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Column(
        children: [
          // Header
          _buildHeader(),

          // Campaign Stats
          _buildCampaignStats(),

          // Active Campaigns
          Expanded(
            child: Row(
              children: [
                // Campaign List
                Expanded(
                  flex: 2,
                  child: _buildCampaignList(),
                ),

                // Campaign Details / QR Code
                Expanded(
                  child: _buildCampaignDetails(),
                ),
              ],
            ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _createCampaign(),
        icon: const Icon(Icons.add),
        label: const Text('New Campaign'),
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
      child: Row(
        children: [
          const Icon(
            Icons.campaign,
            size: 32,
            color: AppTheme.primaryColor,
          ),
          const SizedBox(width: 16),
          const Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Campaign Management',
                  style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                SizedBox(height: 4),
                Text(
                  'Create, track and analyze marketing campaigns',
                  style: TextStyle(
                    color: Colors.grey,
                  ),
                ),
              ],
            ),
          ),
          Row(
            children: [
              OutlinedButton.icon(
                onPressed: () => _showUTMBuilder(),
                icon: const Icon(Icons.link),
                label: const Text('UTM Builder'),
              ),
              const SizedBox(width: 12),
              OutlinedButton.icon(
                onPressed: () => _showQRGenerator(),
                icon: const Icon(Icons.qr_code),
                label: const Text('QR Codes'),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildCampaignStats() {
    return Container(
      padding: const EdgeInsets.all(24),
      child: Row(
        children: [
          _buildStatCard(
            'Active Campaigns',
            '12',
            Icons.campaign,
            Colors.blue,
            '4 ending this week',
          ),
          _buildStatCard(
            'Total Leads',
            '3,450',
            Icons.people,
            Colors.green,
            '+28% this month',
          ),
          _buildStatCard(
            'Conversion Rate',
            '24.5%',
            Icons.trending_up,
            Colors.purple,
            '305 conversions',
          ),
          _buildStatCard(
            'Campaign ROI',
            '342%',
            Icons.attach_money,
            Colors.orange,
            '₹12.5L revenue',
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
    String subtitle,
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
                fontSize: 32,
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
            const SizedBox(height: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.2),
                borderRadius: BorderRadius.circular(4),
              ),
              child: Text(
                subtitle,
                style: TextStyle(
                  color: color,
                  fontSize: 11,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCampaignList() {
    final campaigns = [
      _CampaignMock(
        'Summer Offer 2026',
        'facebook',
        'active',
        1200,
        45,
        '₹25,000',
        '₹5,50,000',
        '15 Mar - 15 Jun',
      ),
      _CampaignMock(
        'Gorakhpur Launch',
        'google',
        'active',
        850,
        62,
        '₹40,000',
        '₹8,20,000',
        '1 Apr - 30 Apr',
      ),
      _CampaignMock(
        'Referral Program',
        'referral',
        'active',
        420,
        78,
        '₹0',
        '₹12,50,000',
        'Ongoing',
      ),
      _CampaignMock(
        'Lucknow Property Fair',
        'event',
        'completed',
        650,
        35,
        '₹1,50,000',
        '₹4,80,000',
        '10-12 Feb 2026',
      ),
      _CampaignMock(
        'Instagram Reels',
        'instagram',
        'active',
        320,
        18,
        '₹15,000',
        '₹2,10,000',
        '1 Mar - 1 Jun',
      ),
    ];

    return Container(
      margin: const EdgeInsets.only(left: 24, right: 12, bottom: 24),
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
                const Text(
                  'Campaigns',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                SegmentedButton<String>(
                  segments: const [
                    ButtonSegment(value: 'all', label: Text('All')),
                    ButtonSegment(value: 'active', label: Text('Active')),
                    ButtonSegment(value: 'completed', label: Text('Past')),
                  ],
                  selected: const {'all'},
                  onSelectionChanged: (value) {},
                ),
              ],
            ),
          ),
          Expanded(
            child: ListView.builder(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              itemCount: campaigns.length,
              itemBuilder: (context, index) {
                return _buildCampaignCard(campaigns[index]);
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCampaignCard(_CampaignMock campaign) {
    final sourceColors = {
      'facebook': Colors.blue,
      'google': Colors.orange,
      'instagram': Colors.pink,
      'referral': Colors.green,
      'event': Colors.purple,
      'website': Colors.teal,
    };

    final isActive = campaign.status == 'active';
    final roi = ((double.parse(campaign.revenue.replaceAll('₹', '').replaceAll(',', '')) /
            double.parse(campaign.spent.replaceAll('₹', '').replaceAll(',', ''))) *
        100);

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: isActive ? Colors.white : Colors.grey.shade50,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(
          color: isActive ? AppTheme.primaryColor.withValues(alpha: 0.3) : Colors.grey.shade200,
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
                  color: sourceColors[campaign.source]?.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(4),
                ),
                child: Text(
                  campaign.source.toUpperCase(),
                  style: TextStyle(
                    color: sourceColors[campaign.source],
                    fontSize: 10,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
              const Spacer(),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: isActive ? Colors.green.withValues(alpha: 0.1) : Colors.grey.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  isActive ? '● ACTIVE' : 'COMPLETED',
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
            campaign.name,
            style: const TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            campaign.duration,
            style: TextStyle(
              color: Colors.grey.shade600,
              fontSize: 13,
            ),
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Expanded(
                child: _buildMiniStat('Leads', '${campaign.leads}', Icons.people),
              ),
              Expanded(
                child: _buildMiniStat(
                    'Conversion', '${campaign.conversionRate}%', Icons.trending_up),
              ),
              Expanded(
                child: _buildMiniStat(
                    'ROI', '${roi.toStringAsFixed(0)}%', Icons.attach_money),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            mainAxisAlignment: MainAxisAlignment.end,
            children: [
              TextButton.icon(
                onPressed: () => _viewCampaignDetails(campaign),
                icon: const Icon(Icons.visibility, size: 18),
                label: const Text('Details'),
              ),
              const SizedBox(width: 8),
              OutlinedButton.icon(
                onPressed: () => _editCampaign(campaign),
                icon: const Icon(Icons.edit, size: 18),
                label: const Text('Edit'),
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
              style: const TextStyle(
                fontWeight: FontWeight.bold,
                fontSize: 14,
              ),
            ),
            Text(
              label,
              style: TextStyle(
                fontSize: 11,
                color: Colors.grey.shade600,
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildCampaignDetails() {
    return Container(
      margin: const EdgeInsets.only(right: 24, bottom: 24),
      padding: const EdgeInsets.all(24),
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
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          const Text(
            'Campaign QR Code',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Scan to open landing page',
            style: TextStyle(
              color: Colors.grey.shade600,
              fontSize: 13,
            ),
          ),
          const SizedBox(height: 24),
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.1),
                  blurRadius: 20,
                ),
              ],
            ),
            child: QrImageView(
              data: 'https://apsdreamhome.com/campaign/summer2026?utm_source=qr&utm_medium=print&utm_campaign=summer2026',
              version: QrVersions.auto,
              size: 180,
              backgroundColor: Colors.white,
            ),
          ),
          const SizedBox(height: 24),
          const Divider(),
          const SizedBox(height: 24),
          const Text(
            'UTM Parameters',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 16),
          _buildUTMParameter('utm_source', 'facebook'),
          _buildUTMParameter('utm_medium', 'social'),
          _buildUTMParameter('utm_campaign', 'summer2026'),
          _buildUTMParameter('utm_content', 'ad1_variant_b'),
          const SizedBox(height: 24),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: () {},
              icon: const Icon(Icons.copy),
              label: const Text('Copy Campaign URL'),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildUTMParameter(String key, String value) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: Colors.grey.shade50,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        children: [
          Text(
            key,
            style: TextStyle(
              color: Colors.grey.shade600,
              fontSize: 12,
            ),
          ),
          const Spacer(),
          Text(
            value,
            style: const TextStyle(
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }

  void _createCampaign() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Create New Campaign'),
        content: const SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                decoration: InputDecoration(
                  labelText: 'Campaign Name',
                  hintText: 'e.g., Summer Offer 2026',
                ),
              ),
              SizedBox(height: 16),
              TextField(
                decoration: InputDecoration(
                  labelText: 'Source',
                  hintText: 'facebook, google, etc.',
                ),
              ),
              SizedBox(height: 16),
              TextField(
                decoration: InputDecoration(
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
            onPressed: () => Navigator.pop(context),
            child: const Text('Create'),
          ),
        ],
      ),
    );
  }

  void _showUTMBuilder() {
    AppWidgets.showInfoSnackBar(context, 'UTM Link Builder - Create tracking URLs');
  }

  void _showQRGenerator() {
    AppWidgets.showInfoSnackBar(context, 'QR Code Generator for campaigns');
  }

  void _viewCampaignDetails(_CampaignMock campaign) {
    // Show detailed analytics
  }

  void _editCampaign(_CampaignMock campaign) {
    // Edit campaign
  }
}

class _CampaignMock {
  final String name;
  final String source;
  final String status;
  final int leads;
  final int conversionRate;
  final String spent;
  final String revenue;
  final String duration;

  _CampaignMock(
    this.name,
    this.source,
    this.status,
    this.leads,
    this.conversionRate,
    this.spent,
    this.revenue,
    this.duration,
  );
}

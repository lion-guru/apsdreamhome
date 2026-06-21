import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

import '../../../core/theme/app_theme.dart';
import '../../widgets/app_widgets.dart';

/// Full CRM System for Admin Panel
/// Includes: Leads, Customers, Follow-ups, Enquiries, Analytics
class CRMPage extends ConsumerStatefulWidget {
  const CRMPage({super.key});

  @override
  ConsumerState<CRMPage> createState() => _CRMPageState();
}

class _CRMPageState extends ConsumerState<CRMPage>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  String? _selectedStatus;
  String? _selectedSource;
  DateTimeRange? _dateRange;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 5, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Column(
        children: [
          // Header
          _buildHeader(),

          // CRM Stats
          _buildCRMStats(),

          // Tabs
          TabBar(
            controller: _tabController,
            isScrollable: true,
            tabs: const [
              Tab(icon: Icon(Icons.people), text: 'All Leads'),
              Tab(icon: Icon(Icons.person_add), text: 'New Enquiries'),
              Tab(icon: Icon(Icons.phone_callback), text: 'Follow-ups'),
              Tab(icon: Icon(Icons.check_circle), text: 'Converted'),
              Tab(icon: Icon(Icons.analytics), text: 'Analytics'),
            ],
          ),

          // Filters
          _buildFilters(),

          // Tab Content
          Expanded(
            child: TabBarView(
              controller: _tabController,
              children: [
                _buildAllLeadsTab(),
                _buildNewEnquiriesTab(),
                _buildFollowUpsTab(),
                _buildConvertedTab(),
                _buildAnalyticsTab(),
              ],
            ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _showAddLeadDialog(),
        icon: const Icon(Icons.person_add),
        label: const Text('Add Lead'),
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
                  'Customer Relationship Management',
                  style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                SizedBox(height: 4),
                Text(
                  'Manage leads, enquiries, follow-ups and conversions',
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
                onPressed: () => _importLeads(),
                icon: const Icon(Icons.upload_file),
                label: const Text('Import'),
              ),
              const SizedBox(width: 12),
              OutlinedButton.icon(
                onPressed: () => _exportLeads(),
                icon: const Icon(Icons.download),
                label: const Text('Export'),
              ),
              const SizedBox(width: 12),
              ElevatedButton.icon(
                onPressed: () => _showBulkActions(),
                icon: const Icon(Icons.auto_fix_high),
                label: const Text('Bulk Actions'),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildCRMStats() {
    return Container(
      padding: const EdgeInsets.all(24),
      child: Row(
        children: [
          _buildStatCard(
            'Total Leads',
            '1,245',
            Icons.people,
            Colors.blue,
            '+12% this month',
          ),
          _buildStatCard(
            'New Today',
            '28',
            Icons.person_add,
            Colors.green,
            '5 from website',
          ),
          _buildStatCard(
            'Pending Follow-up',
            '156',
            Icons.phone_callback,
            Colors.orange,
            '42 overdue',
          ),
          _buildStatCard(
            'Conversion Rate',
            '24.5%',
            Icons.trending_up,
            Colors.purple,
            '+3.2% vs last month',
          ),
          _buildStatCard(
            'Converted',
            '305',
            Icons.check_circle,
            Colors.teal,
            '₹12.5 Cr value',
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
            Row(
              children: [
                Icon(icon, color: color, size: 28),
                const Spacer(),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: color.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(20),
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
          ],
        ),
      ),
    );
  }

  Widget _buildFilters() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.grey.shade50,
        border: Border(
          bottom: BorderSide(color: Colors.grey.shade200),
        ),
      ),
      child: Row(
        children: [
          // Search
          Expanded(
            flex: 2,
            child: TextField(
              onChanged: (value) {},
              decoration: InputDecoration(
                hintText: 'Search by name, phone, email...',
                prefixIcon: const Icon(Icons.search),
                filled: true,
                fillColor: Colors.white,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                  borderSide: BorderSide.none,
                ),
              ),
            ),
          ),
          const SizedBox(width: 16),

          // Status Filter
          Expanded(
            child: DropdownButtonFormField<String>(
              initialValue: _selectedStatus,
              hint: const Text('Status'),
              decoration: _dropdownDecoration(),
              items: const [
                DropdownMenuItem(value: null, child: Text('All Status')),
                DropdownMenuItem(value: 'new', child: Text('🆕 New')),
                DropdownMenuItem(
                    value: 'contacted', child: Text('📞 Contacted')),
                DropdownMenuItem(
                    value: 'interested', child: Text('⭐ Interested')),
                DropdownMenuItem(
                    value: 'follow_up', child: Text('🔄 Follow-up')),
                DropdownMenuItem(
                    value: 'not_interested', child: Text('❌ Not Interested')),
                DropdownMenuItem(
                    value: 'converted', child: Text('✅ Converted')),
                DropdownMenuItem(value: 'lost', child: Text('💔 Lost')),
              ],
              onChanged: (value) => setState(() => _selectedStatus = value),
            ),
          ),
          const SizedBox(width: 16),

          // Source Filter
          Expanded(
            child: DropdownButtonFormField<String>(
              initialValue: _selectedSource,
              hint: const Text('Source'),
              decoration: _dropdownDecoration(),
              items: const [
                DropdownMenuItem(value: null, child: Text('All Sources')),
                DropdownMenuItem(value: 'website', child: Text('🌐 Website')),
                DropdownMenuItem(value: 'facebook', child: Text('📘 Facebook')),
                DropdownMenuItem(
                    value: 'instagram', child: Text('📸 Instagram')),
                DropdownMenuItem(value: 'google', child: Text('🔍 Google Ads')),
                DropdownMenuItem(value: 'referral', child: Text('👥 Referral')),
                DropdownMenuItem(
                    value: 'site_visit', child: Text('🏗️ Site Visit')),
                DropdownMenuItem(value: 'event', child: Text('🎪 Event')),
                DropdownMenuItem(value: 'walk_in', child: Text('🚶 Walk-in')),
                DropdownMenuItem(
                    value: 'associate', child: Text('🤝 Associate')),
              ],
              onChanged: (value) => setState(() => _selectedSource = value),
            ),
          ),
          const SizedBox(width: 16),

          // Date Range
          OutlinedButton.icon(
            onPressed: () => _selectDateRange(),
            icon: const Icon(Icons.calendar_today),
            label: Text(_dateRange == null
                ? 'Date Range'
                : '${DateFormat('dd MMM').format(_dateRange!.start)} - ${DateFormat('dd MMM').format(_dateRange!.end)}'),
          ),
        ],
      ),
    );
  }

  InputDecoration _dropdownDecoration() {
    return InputDecoration(
      filled: true,
      fillColor: Colors.white,
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: BorderSide.none,
      ),
    );
  }

  Widget _buildAllLeadsTab() {
    return _buildLeadsList([
      _LeadMock('Rajesh Kumar', '+91 98765 43210', 'rajesh@email.com', 'new',
          'website', '2 hours ago', '₹0', null, 'Not assigned'),
      _LeadMock(
          'Priya Singh',
          '+91 87654 32109',
          'priya@email.com',
          'contacted',
          'facebook',
          '1 day ago',
          '₹0',
          'Amit Sharma',
          'Follow-up tomorrow'),
      _LeadMock(
          'Amit Patel',
          '+91 76543 21098',
          'amit@email.com',
          'interested',
          'referral',
          '3 days ago',
          '₹50,000',
          'Suresh Kumar',
          'Wants to visit site'),
      _LeadMock(
          'Sneha Gupta',
          '+91 65432 10987',
          'sneha@email.com',
          'follow_up',
          'google',
          '1 week ago',
          '₹25,000',
          'Rahul Verma',
          'EMI discussion pending'),
      _LeadMock(
          'Vikram Reddy',
          '+91 54321 09876',
          'vikram@email.com',
          'converted',
          'site_visit',
          '2 weeks ago',
          '₹12,50,000',
          'Self',
          'Plot A-45 booked'),
    ]);
  }

  Widget _buildNewEnquiriesTab() {
    return _buildLeadsList([
      _LeadMock('Rajesh Kumar', '+91 98765 43210', 'rajesh@email.com', 'new',
          'website', '2 hours ago', '₹0', null, 'Not assigned'),
      _LeadMock('Anita Sharma', '+91 43210 98765', 'anita@email.com', 'new',
          'facebook', '4 hours ago', '₹0', null, 'Not assigned'),
      _LeadMock('Kiran Rao', '+91 32109 87654', 'kiran@email.com', 'new',
          'google', '6 hours ago', '₹0', null, 'Not assigned'),
    ]);
  }

  Widget _buildFollowUpsTab() {
    return _buildLeadsList([
      _LeadMock(
          'Priya Singh',
          '+91 87654 32109',
          'priya@email.com',
          'contacted',
          'facebook',
          '1 day ago',
          '₹0',
          'Amit Sharma',
          'Due today'),
      _LeadMock(
          'Sneha Gupta',
          '+91 65432 10987',
          'sneha@email.com',
          'follow_up',
          'google',
          '1 week ago',
          '₹25,000',
          'Rahul Verma',
          'Overdue by 2 days'),
      _LeadMock(
          'Manoj Tiwari',
          '+91 21098 76543',
          'manoj@email.com',
          'interested',
          'referral',
          '2 days ago',
          '₹0',
          'Amit Sharma',
          'Due tomorrow'),
    ]);
  }

  Widget _buildConvertedTab() {
    return _buildLeadsList([
      _LeadMock(
          'Vikram Reddy',
          '+91 54321 09876',
          'vikram@email.com',
          'converted',
          'site_visit',
          '2 weeks ago',
          '₹12,50,000',
          'Self',
          'Plot A-45 booked'),
      _LeadMock(
          'Deepak Shah',
          '+91 10987 65432',
          'deepak@email.com',
          'converted',
          'walk_in',
          '1 month ago',
          '₹8,75,000',
          'Suresh Kumar',
          'Plot B-12 booked'),
    ]);
  }

  Widget _buildLeadsList(List<_LeadMock> leads) {
    return ListView.builder(
      padding: const EdgeInsets.all(24),
      itemCount: leads.length,
      itemBuilder: (context, index) {
        final lead = leads[index];
        return _buildLeadCard(lead);
      },
    );
  }

  Widget _buildLeadCard(_LeadMock lead) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
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
      child: ExpansionTile(
        leading: _buildStatusAvatar(lead.status),
        title: Row(
          children: [
            Text(
              lead.name,
              style: const TextStyle(
                fontWeight: FontWeight.bold,
                fontSize: 16,
              ),
            ),
            const SizedBox(width: 8),
            _buildSourceChip(lead.source),
          ],
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 4),
            Row(
              children: [
                Icon(Icons.phone, size: 14, color: Colors.grey.shade600),
                const SizedBox(width: 4),
                Text(lead.phone),
                const SizedBox(width: 16),
                Icon(Icons.access_time, size: 14, color: Colors.grey.shade600),
                const SizedBox(width: 4),
                Text(lead.timeAgo),
              ],
            ),
            if (lead.assignedTo != null) ...[
              const SizedBox(height: 4),
              Row(
                children: [
                  Icon(Icons.person_outline,
                      size: 14, color: Colors.grey.shade600),
                  const SizedBox(width: 4),
                  Text('Assigned: ${lead.assignedTo}'),
                ],
              ),
            ],
          ],
        ),
        trailing: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            if (lead.value != '₹0')
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: AppTheme.successColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  lead.value,
                  style: const TextStyle(
                    color: AppTheme.successColor,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            const SizedBox(width: 8),
            _buildStatusBadge(lead.status),
          ],
        ),
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Divider(),
                Row(
                  children: [
                    Expanded(
                      child: _buildActionButton(
                        'Call',
                        Icons.phone,
                        Colors.green,
                        () => _callLead(lead.phone),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: _buildActionButton(
                        'WhatsApp',
                        Icons.message,
                        Colors.teal,
                        () => _whatsappLead(lead.phone),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: _buildActionButton(
                        'Email',
                        Icons.email,
                        Colors.blue,
                        () => _emailLead(lead.email),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: _buildActionButton(
                        'Convert',
                        Icons.check_circle,
                        AppTheme.successColor,
                        () => _convertLead(lead),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                if (lead.remarks != null)
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.grey.shade50,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Row(
                      children: [
                        Icon(Icons.notes, color: Colors.grey.shade600),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            lead.remarks!,
                            style: TextStyle(color: Colors.grey.shade700),
                          ),
                        ),
                      ],
                    ),
                  ),
                const SizedBox(height: 12),
                Row(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    TextButton.icon(
                      onPressed: () => _scheduleFollowUp(lead),
                      icon: const Icon(Icons.calendar_today),
                      label: const Text('Schedule Follow-up'),
                    ),
                    const SizedBox(width: 8),
                    TextButton.icon(
                      onPressed: () => _assignLead(lead),
                      icon: const Icon(Icons.person_add),
                      label: const Text('Reassign'),
                    ),
                    const SizedBox(width: 8),
                    OutlinedButton.icon(
                      onPressed: () => _viewLeadDetails(lead),
                      icon: const Icon(Icons.visibility),
                      label: const Text('View Details'),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatusAvatar(String status) {
    final colors = {
      'new': Colors.blue,
      'contacted': Colors.orange,
      'interested': Colors.purple,
      'follow_up': Colors.amber,
      'not_interested': Colors.grey,
      'converted': Colors.green,
      'lost': Colors.red,
    };

    return CircleAvatar(
      backgroundColor: colors[status]?.withValues(alpha: 0.2) ?? Colors.grey,
      child: Icon(
        _getStatusIcon(status),
        color: colors[status] ?? Colors.grey,
        size: 20,
      ),
    );
  }

  IconData _getStatusIcon(String status) {
    switch (status) {
      case 'new':
        return Icons.fiber_new;
      case 'contacted':
        return Icons.phone;
      case 'interested':
        return Icons.star;
      case 'follow_up':
        return Icons.sync;
      case 'not_interested':
        return Icons.thumb_down;
      case 'converted':
        return Icons.check_circle;
      case 'lost':
        return Icons.heart_broken;
      default:
        return Icons.person;
    }
  }

  Widget _buildSourceChip(String source) {
    final icons = {
      'website': '🌐',
      'facebook': '📘',
      'instagram': '📸',
      'google': '🔍',
      'referral': '👥',
      'site_visit': '🏗️',
      'event': '🎪',
      'walk_in': '🚶',
      'associate': '🤝',
    };

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: Colors.grey.shade100,
        borderRadius: BorderRadius.circular(4),
      ),
      child: Text(
        '${icons[source] ?? '📌'} ${source.replaceAll('_', ' ').toUpperCase()}',
        style: TextStyle(
          fontSize: 10,
          color: Colors.grey.shade700,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }

  Widget _buildStatusBadge(String status) {
    final labels = {
      'new': '🆕 NEW',
      'contacted': '📞 CONTACTED',
      'interested': '⭐ INTERESTED',
      'follow_up': '🔄 FOLLOW-UP',
      'not_interested': '❌ NOT INTERESTED',
      'converted': '✅ CONVERTED',
      'lost': '💔 LOST',
    };

    final colors = {
      'new': Colors.blue,
      'contacted': Colors.orange,
      'interested': Colors.purple,
      'follow_up': Colors.amber,
      'not_interested': Colors.grey,
      'converted': Colors.green,
      'lost': Colors.red,
    };

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: colors[status]?.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
            color: colors[status]?.withValues(alpha: 0.3) ?? Colors.grey),
      ),
      child: Text(
        labels[status] ?? status.toUpperCase(),
        style: TextStyle(
          color: colors[status] ?? Colors.grey,
          fontSize: 11,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }

  Widget _buildActionButton(
    String label,
    IconData icon,
    Color color,
    VoidCallback onTap,
  ) {
    return InkWell(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 12),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Column(
          children: [
            Icon(icon, color: color),
            const SizedBox(height: 4),
            Text(
              label,
              style: TextStyle(
                color: color,
                fontSize: 12,
                fontWeight: FontWeight.bold,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildAnalyticsTab() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Lead Source Analysis',
            style: TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 16),
          _buildSourceAnalytics(),
          const SizedBox(height: 32),
          const Text(
            'Conversion Funnel',
            style: TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 16),
          _buildConversionFunnel(),
          const SizedBox(height: 32),
          const Text(
            'Agent Performance',
            style: TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 16),
          _buildAgentPerformance(),
        ],
      ),
    );
  }

  Widget _buildSourceAnalytics() {
    final sources = [
      {'name': 'Website', 'count': 450, 'percentage': 36, 'color': Colors.blue},
      {
        'name': 'Facebook',
        'count': 280,
        'percentage': 22,
        'color': Colors.indigo
      },
      {
        'name': 'Referrals',
        'count': 200,
        'percentage': 16,
        'color': Colors.green
      },
      {
        'name': 'Google Ads',
        'count': 150,
        'percentage': 12,
        'color': Colors.orange
      },
      {
        'name': 'Site Visit',
        'count': 100,
        'percentage': 8,
        'color': Colors.purple
      },
      {'name': 'Others', 'count': 65, 'percentage': 5, 'color': Colors.grey},
    ];

    return Container(
      padding: const EdgeInsets.all(20),
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
        children: sources.map((source) {
          return Padding(
            padding: const EdgeInsets.symmetric(vertical: 8),
            child: Row(
              children: [
                Container(
                  width: 12,
                  height: 12,
                  decoration: BoxDecoration(
                    color: source['color'] as Color,
                    shape: BoxShape.circle,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(source['name'] as String),
                ),
                Text(
                  '${source['count']} leads',
                  style: TextStyle(color: Colors.grey.shade600),
                ),
                const SizedBox(width: 16),
                SizedBox(
                  width: 100,
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(4),
                    child: LinearProgressIndicator(
                      value: (source['percentage'] as int) / 100,
                      backgroundColor: Colors.grey.shade200,
                      valueColor: AlwaysStoppedAnimation<Color>(
                        source['color'] as Color,
                      ),
                      minHeight: 8,
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Text(
                  '${source['percentage']}%',
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
              ],
            ),
          );
        }).toList(),
      ),
    );
  }

  Widget _buildConversionFunnel() {
    final stages = [
      {
        'name': 'Total Leads',
        'count': 1245,
        'width': 1.0,
        'color': Colors.blue
      },
      {
        'name': 'Contacted',
        'count': 890,
        'width': 0.75,
        'color': Colors.blue.shade400
      },
      {
        'name': 'Interested',
        'count': 520,
        'width': 0.55,
        'color': Colors.blue.shade300
      },
      {
        'name': 'Site Visits',
        'count': 380,
        'width': 0.40,
        'color': Colors.blue.shade200
      },
      {'name': 'Converted', 'count': 305, 'width': 0.30, 'color': Colors.green},
    ];

    return Container(
      padding: const EdgeInsets.all(20),
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
        children: stages.asMap().entries.map((entry) {
          final index = entry.key;
          final stage = entry.value;
          return Container(
            margin: const EdgeInsets.symmetric(vertical: 4),
            child: Row(
              children: [
                SizedBox(
                  width: 120,
                  child: Text(
                    stage['name'] as String,
                    style: const TextStyle(fontWeight: FontWeight.w500),
                  ),
                ),
                Expanded(
                  child: Center(
                    child: FractionallySizedBox(
                      widthFactor: stage['width'] as double,
                      child: Container(
                        height: 40,
                        decoration: BoxDecoration(
                          color: stage['color'] as Color,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Center(
                          child: Text(
                            '${stage['count']}',
                            style: const TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 16),
                if (index < stages.length - 1)
                  Text(
                    '${((stages[index + 1]['count'] as int) / (stage['count'] as int) * 100).toStringAsFixed(0)}%',
                    style: TextStyle(
                      color: Colors.grey.shade600,
                      fontSize: 12,
                    ),
                  ),
              ],
            ),
          );
        }).toList(),
      ),
    );
  }

  Widget _buildAgentPerformance() {
    final agents = [
      {
        'name': 'Amit Sharma',
        'leads': 145,
        'converted': 42,
        'rate': 29,
        'revenue': '₹5.2 Cr'
      },
      {
        'name': 'Suresh Kumar',
        'leads': 128,
        'converted': 38,
        'rate': 30,
        'revenue': '₹4.8 Cr'
      },
      {
        'name': 'Rahul Verma',
        'leads': 112,
        'converted': 31,
        'rate': 28,
        'revenue': '₹3.9 Cr'
      },
      {
        'name': 'Priya Patel',
        'leads': 98,
        'converted': 28,
        'rate': 29,
        'revenue': '₹3.5 Cr'
      },
      {
        'name': 'Vikram Singh',
        'leads': 87,
        'converted': 24,
        'rate': 28,
        'revenue': '₹3.1 Cr'
      },
    ];

    return Container(
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
      child: DataTable(
        columns: const [
          DataColumn(label: Text('Agent')),
          DataColumn(label: Text('Leads'), numeric: true),
          DataColumn(label: Text('Converted'), numeric: true),
          DataColumn(label: Text('Conversion'), numeric: true),
          DataColumn(label: Text('Revenue'), numeric: true),
        ],
        rows: agents.map((agent) {
          return DataRow(
            cells: [
              DataCell(
                Row(
                  children: [
                    CircleAvatar(
                      radius: 16,
                      child: Text((agent['name'] as String).substring(0, 1)),
                    ),
                    const SizedBox(width: 12),
                    Text(agent['name'] as String),
                  ],
                ),
              ),
              DataCell(Text('${agent['leads']}')),
              DataCell(Text('${agent['converted']}')),
              DataCell(
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.green.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Text(
                    '${agent['rate']}%',
                    style: const TextStyle(
                      color: Colors.green,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ),
              DataCell(
                Text(
                  agent['revenue'] as String,
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
              ),
            ],
          );
        }).toList(),
      ),
    );
  }

  void _selectDateRange() async {
    final picked = await showDateRangePicker(
      context: context,
      firstDate: DateTime(2020),
      lastDate: DateTime.now(),
    );
    if (picked != null) {
      setState(() => _dateRange = picked);
    }
  }

  void _showAddLeadDialog() {
    // Add lead dialog
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Add New Lead'),
        content: const Text('Lead creation form with all fields'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Save'),
          ),
        ],
      ),
    );
  }

  void _importLeads() {
    AppWidgets.showInfoSnackBar(context, 'Import leads from Excel/CSV');
  }

  void _exportLeads() {
    AppWidgets.showInfoSnackBar(context, 'Export leads to CSV/PDF');
  }

  void _showBulkActions() {
    AppWidgets.showInfoSnackBar(
        context, 'Bulk assign, update status, send messages');
  }

  void _callLead(String phone) {
    // Launch phone dialer
    AppWidgets.showInfoSnackBar(context, 'Calling $phone...');
  }

  void _whatsappLead(String phone) {
    // Open WhatsApp
    AppWidgets.showInfoSnackBar(context, 'Opening WhatsApp...');
  }

  void _emailLead(String email) {
    // Open email
    AppWidgets.showInfoSnackBar(context, 'Sending email to $email...');
  }

  void _convertLead(_LeadMock lead) {
    // Convert to customer/booking
    AppWidgets.showInfoSnackBar(
        context, 'Converting ${lead.name} to customer...');
  }

  void _scheduleFollowUp(_LeadMock lead) {
    // Schedule follow-up
    AppWidgets.showInfoSnackBar(context, 'Schedule follow-up for ${lead.name}');
  }

  void _assignLead(_LeadMock lead) {
    // Reassign to another agent
    AppWidgets.showInfoSnackBar(context, 'Reassign ${lead.name}');
  }

  void _viewLeadDetails(_LeadMock lead) {
    // View full lead history
    context.push('/admin/crm/leads/${lead.hashCode}');
  }
}

// Mock lead class for demo
class _LeadMock {
  final String name;
  final String phone;
  final String email;
  final String status;
  final String source;
  final String timeAgo;
  final String value;
  final String? assignedTo;
  final String? remarks;

  _LeadMock(
    this.name,
    this.phone,
    this.email,
    this.status,
    this.source,
    this.timeAgo,
    this.value,
    this.assignedTo,
    this.remarks,
  );
}

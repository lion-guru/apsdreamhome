import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/utils/responsive_helper.dart';
import '../../../data/models/crm_models.dart';
import '../../../data/services/crm_service.dart';

// ─── Agent CRM Page ─────────────────────────────────────────────────────
// Body-only widget (no Scaffold) — designed for nesting inside AgentShell
// or EmployeeShell. Shows MY leads only, not all leads.

class AgentCRMPage extends ConsumerStatefulWidget {
  const AgentCRMPage({super.key});

  @override
  ConsumerState<AgentCRMPage> createState() => _AgentCRMPageState();
}

class _AgentCRMPageState extends ConsumerState<AgentCRMPage> {
  final CRMService _crm = CRMService();
  final ScrollController _scrollCtrl = ScrollController();
  final TextEditingController _searchCtrl = TextEditingController();

  bool _isLoading = true;
  String? _error;

  // Data
  CRMDashboardStats _stats = const CRMDashboardStats();
  List<CRMLead> _leads = [];
  bool _loadingMore = false;
  bool _hasMore = true;
  int _currentPage = 1;
  static const int _perPage = 25;

  // Filters
  String _searchQuery = '';
  String? _filterCategory;
  String _selectedChip = 'all';

  // Category chip data
  static const _chips = [
    ('all', 'All', AppTheme.primaryColor),
    ('hot', 'Hot', Color(0xFFEF4444)),
    ('warm', 'Warm', Color(0xFFF59E0B)),
    ('lukewarm', 'Lukewarm', Color(0xFFF97316)),
    ('cold', 'Cold', Color(0xFF3B82F6)),
    ('new', 'New', Color(0xFF8B5CF6)),
  ];

  @override
  void initState() {
    super.initState();
    _loadData();
    _scrollCtrl.addListener(_onScroll);
  }

  @override
  void dispose() {
    _scrollCtrl.dispose();
    _searchCtrl.dispose();
    super.dispose();
  }

  // ─── Data Loading ──────────────────────────────────────────────────

  Future<void> _loadData() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final dashboard = await _crm.getDashboard();
      final statsData = dashboard['stats'];
      _stats = CRMDashboardStats.fromJson(
        (statsData is Map<String, dynamic>) ? statsData : dashboard,
      );
      await _loadLeads(reset: true);
      if (mounted) setState(() => _isLoading = false);
    } catch (e) {
      if (mounted) setState(() { _error = e.toString(); _isLoading = false; });
    }
  }

  Future<void> _loadLeads({bool reset = false}) async {
    if (reset) {
      _currentPage = 1;
      _hasMore = true;
      _leads = [];
    }
    setState(() => _loadingMore = true);
    try {
      final res = await _crm.getLeads(
        search: _searchQuery.isNotEmpty ? _searchQuery : null,
        category: _filterCategory,
        page: _currentPage,
        perPage: _perPage,
      );
      final list = (res['leads'] as List?)
              ?.map((j) => CRMLead.fromJson(j as Map<String, dynamic>))
              .toList() ??
          [];
      final total = res['total'] as int? ?? 0;
      if (mounted) {
        setState(() {
          _leads = reset ? list : [..._leads, ...list];
          _hasMore = _leads.length < total;
          _loadingMore = false;
        });
      }
    } catch (e) {
      if (mounted) setState(() => _loadingMore = false);
    }
  }

  void _onScroll() {
    if (_scrollCtrl.position.pixels >= _scrollCtrl.position.maxScrollExtent - 200 &&
        !_loadingMore &&
        _hasMore) {
      _currentPage++;
      _loadLeads();
    }
  }

  // ─── Actions ───────────────────────────────────────────────────────

  Future<void> _onRefresh() async {
    await _loadData();
  }

  void _applyChipFilter(String chipLabel) {
    setState(() {
      _selectedChip = chipLabel;
      _filterCategory = chipLabel == 'all' ? null : chipLabel;
    });
    _loadLeads(reset: true);
  }

  void _onSearchChanged(String value) {
    _searchQuery = value;
    _loadLeads(reset: true);
  }

  Future<void> _callLead(String phone) async {
    final uri = Uri(scheme: 'tel', path: phone);
    if (await canLaunchUrl(uri)) await launchUrl(uri);
  }

  Future<void> _smsLead(String phone) async {
    final uri = Uri(scheme: 'sms', path: phone);
    if (await canLaunchUrl(uri)) await launchUrl(uri);
  }

  Future<void> _whatsappLead(String phone) async {
    final cleaned = phone.replaceAll(RegExp(r'[^\d]'), '');
    final uri = Uri.parse('https://wa.me/$cleaned');
    if (await canLaunchUrl(uri)) await launchUrl(uri, mode: LaunchMode.externalApplication);
  }

  Future<void> _advanceStage(CRMLead lead) async {
    final next = _nextStage(lead.status ?? 'new');
    if (next == (lead.status ?? '')) return;
    final ok = await _crm.moveLeadToStage(lead.id, next);
    if (ok) {
      _loadLeads(reset: true);
      _loadData();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Lead moved to ${next.replaceAll('_', ' ').toUpperCase()}'),
            backgroundColor: AppTheme.successColor,
          ),
        );
      }
    }
  }

  // ─── Build ─────────────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    if (_isLoading) return _buildShimmer();
    if (_error != null) return _buildError();
    return _buildBody();
  }

  // ─── Shimmer Skeleton ──────────────────────────────────────────────

  Widget _buildShimmer() {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        _shimmerBar(200, 28),
        const SizedBox(height: 16),
        Row(children: [
          _shimmerBox(110, 80),
          const SizedBox(width: 10),
          _shimmerBox(110, 80),
          const SizedBox(width: 10),
          _shimmerBox(110, 80),
        ]),
        const SizedBox(height: 16),
        _shimmerBar(double.infinity, 36),
        const SizedBox(height: 16),
        _shimmerCard(),
        const SizedBox(height: 10),
        _shimmerCard(),
        const SizedBox(height: 10),
        _shimmerCard(),
      ],
    );
  }

  Widget _shimmerBox(double w, double h) {
    return Container(
      width: w,
      height: h,
      decoration: BoxDecoration(
        color: Colors.grey.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(12),
      ),
    );
  }

  Widget _shimmerBar(double w, double h) {
    return Container(
      width: w,
      height: h,
      decoration: BoxDecoration(
        color: Colors.grey.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(8),
      ),
    );
  }

  Widget _shimmerCard() {
    return Container(
      height: 120,
      decoration: BoxDecoration(
        color: Colors.grey.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(12),
      ),
    );
  }

  // ─── Error State ───────────────────────────────────────────────────

  Widget _buildError() {
    return Center(
      child: Padding(
        padding: ResponsiveHelper.padding(context, all: 32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.error_outline, size: 48, color: AppTheme.errorColor),
            const SizedBox(height: 12),
            Text(_error!, style: const TextStyle(color: Colors.grey), textAlign: TextAlign.center),
            const SizedBox(height: 16),
            ElevatedButton.icon(
              onPressed: _loadData,
              icon: const Icon(Icons.refresh),
              label: const Text('Retry'),
            ),
          ],
        ),
      ),
    );
  }

  // ─── Main Body ─────────────────────────────────────────────────────

  Widget _buildBody() {
    return RefreshIndicator(
      onRefresh: _onRefresh,
      child: CustomScrollView(
        controller: _scrollCtrl,
        physics: const AlwaysScrollableScrollPhysics(parent: BouncingScrollPhysics()),
        slivers: [
          // ── Header ──
          SliverToBoxAdapter(child: _buildHeader()),

          // ── Stats Row ──
          SliverToBoxAdapter(child: _buildStatsRow()),

          // ── Search ──
          SliverToBoxAdapter(child: _buildSearchField()),

          // ── Filter Chips ──
          SliverToBoxAdapter(child: _buildFilterChips()),

          // ── Leads List or Empty ──
          if (_leads.isEmpty && !_loadingMore)
            SliverFillRemaining(child: _buildEmptyState())
          else ...[
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 0),
              sliver: SliverList.separated(
                itemCount: _leads.length,
                separatorBuilder: (_, __) => const SizedBox(height: 10),
                itemBuilder: (ctx, i) => _buildLeadCard(_leads[i]),
              ),
            ),
            if (_loadingMore)
              const SliverToBoxAdapter(
                child: Padding(
                  padding: EdgeInsets.all(16),
                  child: Center(child: CircularProgressIndicator(strokeWidth: 2)),
                ),
              ),
            if (!_hasMore && _leads.isNotEmpty)
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Center(
                    child: Text('All leads loaded (${_leads.length})',
                        style: TextStyle(fontSize: 12, color: Colors.grey.shade500)),
                  ),
                ),
              ),
          ],
        ],
      ),
    );
  }

  // ─── Header ────────────────────────────────────────────────────────

  Widget _buildHeader() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 4),
      child: Row(
        children: [
          const Icon(Icons.people_alt, color: AppTheme.primaryColor, size: 26),
          const SizedBox(width: 10),
          const Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('My Leads',
                    style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
                Text('Your assigned leads & follow-ups',
                    style: TextStyle(fontSize: 12, color: Colors.grey)),
              ],
            ),
          ),
          IconButton(
            onPressed: _onRefresh,
            icon: const Icon(Icons.refresh, size: 22),
            tooltip: 'Refresh',
          ),
          const SizedBox(width: 4),
          ElevatedButton.icon(
            onPressed: _showCreateLeadDialog,
            icon: const Icon(Icons.add, size: 18),
            label: const Text('New Lead'),
            style: ElevatedButton.styleFrom(backgroundColor: AppTheme.primaryColor),
          ),
        ],
      ),
    );
  }

  // ─── Stats Row ─────────────────────────────────────────────────────

  Widget _buildStatsRow() {
    final cards = [
      _StatData('Total Leads', _stats.totalLeads, Icons.people, AppTheme.primaryColor),
      _StatData('Hot Leads', _stats.wonLeads + _stats.qualifiedLeads, Icons.local_fire_department, const Color(0xFFEF4444)),
      _StatData('Follow-ups', _stats.pendingTasks + _stats.overdueTasks, Icons.schedule, AppTheme.warningColor),
    ];

    return SizedBox(
      height: 90,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        itemCount: cards.length,
        separatorBuilder: (_, __) => const SizedBox(width: 10),
        itemBuilder: (_, i) {
          final c = cards[i];
          return Container(
            width: 120,
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: c.color.withValues(alpha: 0.08),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: c.color.withValues(alpha: 0.15)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(c.icon, color: c.color, size: 18),
                const SizedBox(height: 6),
                Text('${c.value}',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: c.color)),
                const SizedBox(height: 2),
                Text(c.label,
                    style: const TextStyle(fontSize: 10, color: Colors.grey),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis),
              ],
            ),
          );
        },
      ),
    );
  }

  // ─── Search Field ──────────────────────────────────────────────────

  Widget _buildSearchField() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
      child: TextField(
        controller: _searchCtrl,
        decoration: InputDecoration(
          hintText: 'Search leads by name, phone...',
          prefixIcon: const Icon(Icons.search, size: 20),
          suffixIcon: _searchQuery.isNotEmpty
              ? IconButton(
                  icon: const Icon(Icons.clear, size: 18),
                  onPressed: () {
                    _searchCtrl.clear();
                    _searchQuery = '';
                    _loadLeads(reset: true);
                  },
                )
              : null,
          border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
          contentPadding: const EdgeInsets.symmetric(vertical: 0, horizontal: 12),
          isDense: true,
        ),
        onChanged: _onSearchChanged,
      ),
    );
  }

  // ─── Filter Chips ──────────────────────────────────────────────────

  Widget _buildFilterChips() {
    return SizedBox(
      height: 48,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        itemCount: _chips.length,
        separatorBuilder: (_, __) => const SizedBox(width: 6),
        itemBuilder: (_, i) {
          final (value, label, color) = _chips[i];
          final isSelected = _selectedChip == value;
          return FilterChip(
            label: Text(label,
                style: TextStyle(
                    fontSize: 12,
                    color: isSelected ? Colors.white : color,
                    fontWeight: FontWeight.w500)),
            selected: isSelected,
            selectedColor: color,
            backgroundColor: color.withValues(alpha: 0.08),
            side: BorderSide(color: color.withValues(alpha: isSelected ? 0.0 : 0.3)),
            onSelected: (_) => _applyChipFilter(value),
            visualDensity: VisualDensity.compact,
            materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
          );
        },
      ),
    );
  }

  // ─── Empty State ───────────────────────────────────────────────────

  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(40),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 100,
              height: 100,
              decoration: BoxDecoration(
                color: AppTheme.primaryColor.withValues(alpha: 0.08),
                shape: BoxShape.circle,
              ),
              child: Icon(Icons.person_search, size: 48, color: AppTheme.primaryColor.withValues(alpha: 0.4)),
            ),
            const SizedBox(height: 20),
            const Text('No leads assigned yet',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
            const SizedBox(height: 8),
            const Text(
              'New leads will appear here once they are assigned to you by your manager.',
              style: TextStyle(fontSize: 13, color: Colors.grey),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 20),
            ElevatedButton.icon(
              onPressed: _onRefresh,
              icon: const Icon(Icons.refresh, size: 18),
              label: const Text('Refresh'),
            ),
          ],
        ),
      ),
    );
  }

  // ─── Lead Card ─────────────────────────────────────────────────────

  Widget _buildLeadCard(CRMLead lead) {
    return Dismissible(
      key: ValueKey('agent_lead_${lead.id}'),
      direction: DismissDirection.endToStart,
      confirmDismiss: (_) async {
        _advanceStage(lead);
        return false; // don't actually dismiss, just advance
      },
      background: Container(
        alignment: Alignment.centerRight,
        padding: const EdgeInsets.only(right: 20),
        decoration: BoxDecoration(
          color: AppTheme.successColor,
          borderRadius: BorderRadius.circular(12),
        ),
        child: const Icon(Icons.arrow_forward, color: Colors.white),
      ),
      child: GestureDetector(
        onTap: () => _showLeadDetail(lead),
        child: Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: Colors.grey.withValues(alpha: 0.12)),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.04),
                blurRadius: 6,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // ── Row 1: Avatar + Name + Score ──
              Row(
                children: [
                  CircleAvatar(
                    radius: 18,
                    backgroundColor: lead.categoryColor.withValues(alpha: 0.15),
                    child: Text(
                      lead.name.isNotEmpty ? lead.name[0].toUpperCase() : '?',
                      style: TextStyle(
                        color: lead.categoryColor,
                        fontWeight: FontWeight.bold,
                        fontSize: 14,
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(lead.name,
                            style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis),
                        const SizedBox(height: 2),
                        if (lead.phone != null)
                          Row(
                            children: [
                              const Icon(Icons.phone, size: 11, color: Colors.grey),
                              const SizedBox(width: 4),
                              Text(lead.phone!,
                                  style: const TextStyle(fontSize: 12, color: Colors.grey)),
                            ],
                          ),
                      ],
                    ),
                  ),
                  _buildScoreCircle(lead.leadScore ?? 0),
                ],
              ),

              const SizedBox(height: 10),

              // ── Row 2: Status badge + Category badge ──
              Row(
                children: [
                  _statusBadge(lead.status, lead.statusColor),
                  const SizedBox(width: 6),
                  if (lead.leadCategory != null)
                    _categoryBadge(lead.leadCategory!, lead.categoryColor),
                  const Spacer(),
                  if (lead.source != null) _sourceBadge(lead.source!),
                ],
              ),

              // ── Row 3: Property interest + Budget ──
              if (lead.propertyInterest != null || lead.budgetRange != null) ...[
                const SizedBox(height: 8),
                Wrap(
                  spacing: 6,
                  runSpacing: 4,
                  children: [
                    if (lead.propertyInterest != null)
                      _infoTag(Icons.home, lead.propertyInterest!, Colors.blue),
                    if (lead.budgetRange != null)
                      _infoTag(Icons.currency_rupee, '₹${lead.budgetRange}', Colors.green),
                  ],
                ),
              ],

              const SizedBox(height: 8),

              // ── Row 4: Last activity + Action buttons ──
              Row(
                children: [
                  if (lead.lastActivityDate != null) ...[
                    Icon(Icons.access_time, size: 12, color: Colors.grey.shade500),
                    const SizedBox(width: 4),
                    Text(_timeAgo(lead.lastActivityDate!),
                        style: TextStyle(fontSize: 11, color: Colors.grey.shade500)),
                  ] else ...[
                    Icon(Icons.access_time, size: 12, color: Colors.grey.shade400),
                    const SizedBox(width: 4),
                    Text('No activity',
                        style: TextStyle(fontSize: 11, color: Colors.grey.shade400)),
                  ],
                  const Spacer(),
                  if (lead.phone != null) ...[
                    _miniActionBtn(Icons.call, AppTheme.successColor, () => _callLead(lead.phone!)),
                    const SizedBox(width: 6),
                    _miniActionBtn(Icons.sms, Colors.blue, () => _smsLead(lead.phone!)),
                    const SizedBox(width: 6),
                    _miniActionBtn(Icons.chat, const Color(0xFF25D366), () => _whatsappLead(lead.phone!)),
                    const SizedBox(width: 6),
                  ],
                  _miniActionBtn(Icons.note_add, AppTheme.warningColor, () => _showAddInteraction(lead, 'note')),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ─── Score Circle ──────────────────────────────────────────────────

  Widget _buildScoreCircle(int score) {
    Color color;
    if (score >= 80) {
      color = AppTheme.successColor;
    } else if (score >= 60) {
      color = AppTheme.warningColor;
    } else if (score >= 40) {
      color = Colors.blue;
    } else {
      color = Colors.grey;
    }

    return SizedBox(
      width: 38,
      height: 38,
      child: Stack(
        alignment: Alignment.center,
        children: [
          SizedBox(
            width: 38,
            height: 38,
            child: CircularProgressIndicator(
              value: score / 100,
              strokeWidth: 3,
              backgroundColor: color.withValues(alpha: 0.15),
              valueColor: AlwaysStoppedAnimation<Color>(color),
            ),
          ),
          Text('$score',
              style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: color)),
        ],
      ),
    );
  }

  // ─── Badges & Tags ─────────────────────────────────────────────────

  Widget _statusBadge(String? status, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(
        (status ?? 'unknown').replaceAll('_', ' ').toUpperCase(),
        style: TextStyle(fontSize: 10, color: color, fontWeight: FontWeight.w600),
      ),
    );
  }

  Widget _categoryBadge(String category, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(
        category.toUpperCase(),
        style: TextStyle(fontSize: 10, color: color, fontWeight: FontWeight.w600),
      ),
    );
  }

  Widget _sourceBadge(String source) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
      decoration: BoxDecoration(
        color: Colors.purple.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(4),
      ),
      child: Text(source, style: const TextStyle(fontSize: 10, color: Colors.purple)),
    );
  }

  Widget _infoTag(IconData icon, String text, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 3),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(4),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 11, color: color),
          const SizedBox(width: 4),
          Text(text, style: TextStyle(fontSize: 10, color: color)),
        ],
      ),
    );
  }

  Widget _miniActionBtn(IconData icon, Color color, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: 30,
        height: 30,
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.1),
          shape: BoxShape.circle,
        ),
        child: Icon(icon, size: 14, color: color),
      ),
    );
  }

  // ─── Lead Detail Bottom Sheet ──────────────────────────────────────

  void _showLeadDetail(CRMLead lead) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => DraggableScrollableSheet(
        initialChildSize: 0.7,
        maxChildSize: 0.95,
        minChildSize: 0.4,
        expand: false,
        builder: (ctx, scrollCtrl) =>
            _AgentLeadDetailSheet(lead: lead, crm: _crm, onRefresh: _onRefresh),
      ),
    );
  }

  // ─── Add Interaction ───────────────────────────────────────────────

  void _showAddInteraction(CRMLead lead, String type) {
    final bodyCtrl = TextEditingController();
    final subjectCtrl = TextEditingController();
    String direction = 'outbound';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => Padding(
        padding: EdgeInsets.only(bottom: MediaQuery.of(ctx).viewInsets.bottom),
        child: Container(
          padding: const EdgeInsets.all(20),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Add ${type.replaceAll('_', ' ').toUpperCase()}',
                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                const SizedBox(height: 4),
                Text('Lead: ${lead.name}', style: const TextStyle(fontSize: 12, color: Colors.grey)),
                const SizedBox(height: 16),
                TextField(
                  controller: subjectCtrl,
                  decoration: const InputDecoration(
                    labelText: 'Subject',
                    border: OutlineInputBorder(),
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: bodyCtrl,
                  decoration: const InputDecoration(
                    labelText: 'Notes',
                    border: OutlineInputBorder(),
                  ),
                  maxLines: 3,
                ),
                const SizedBox(height: 12),
                SegmentedButton<String>(
                  segments: const [
                    ButtonSegment(value: 'outbound', label: Text('Outbound')),
                    ButtonSegment(value: 'inbound', label: Text('Inbound')),
                  ],
                  selected: {direction},
                  onSelectionChanged: (v) => direction = v.first,
                ),
                const SizedBox(height: 20),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () async {
                      if (bodyCtrl.text.isEmpty && subjectCtrl.text.isEmpty) {
                        if (mounted) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(content: Text('Please add a subject or notes')),
                          );
                        }
                        return;
                      }
                      await _crm.addInteraction(lead.id, {
                        'type': type,
                        'direction': direction,
                        'subject': subjectCtrl.text.isNotEmpty ? subjectCtrl.text : null,
                        'body': bodyCtrl.text.isNotEmpty ? bodyCtrl.text : null,
                      });
                      if (ctx.mounted) Navigator.pop(ctx);
                      _onRefresh();
                      if (mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            content: Text('Interaction added'),
                            backgroundColor: AppTheme.successColor,
                          ),
                        );
                      }
                    },
                    style: ElevatedButton.styleFrom(backgroundColor: AppTheme.primaryColor),
                    child: const Text('Save'),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  // ─── Create Lead Dialog ────────────────────────────────────────────

  void _showCreateLeadDialog() {
    final nameCtrl = TextEditingController();
    final phoneCtrl = TextEditingController();
    final emailCtrl = TextEditingController();
    final propertyCtrl = TextEditingController();
    final budgetCtrl = TextEditingController();
    final sourceCtrl = TextEditingController(text: 'walk-in');
    final notesCtrl = TextEditingController();
    String selectedPriority = 'medium';
    String selectedCategory = 'warm';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => Padding(
        padding: EdgeInsets.only(bottom: MediaQuery.of(ctx).viewInsets.bottom),
        child: Container(
          padding: const EdgeInsets.all(20),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Add New Lead',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                const SizedBox(height: 16),
                TextField(
                  controller: nameCtrl,
                  decoration: const InputDecoration(labelText: 'Name *', border: OutlineInputBorder()),
                  textCapitalization: TextCapitalization.words,
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: phoneCtrl,
                  decoration: const InputDecoration(labelText: 'Phone *', border: OutlineInputBorder()),
                  keyboardType: TextInputType.phone,
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: emailCtrl,
                  decoration: const InputDecoration(labelText: 'Email', border: OutlineInputBorder()),
                  keyboardType: TextInputType.emailAddress,
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: propertyCtrl,
                  decoration: const InputDecoration(
                    labelText: 'Property Interest',
                    border: OutlineInputBorder(),
                    hintText: 'e.g. 2BHK Flat in Gorakhpur',
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: budgetCtrl,
                  decoration: const InputDecoration(
                    labelText: 'Budget Range',
                    border: OutlineInputBorder(),
                    hintText: 'e.g. 25-35 Lakh',
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: sourceCtrl,
                  decoration: const InputDecoration(labelText: 'Source', border: OutlineInputBorder()),
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(
                      child: DropdownButtonFormField<String>(
                        initialValue: selectedPriority,
                        decoration: const InputDecoration(
                          labelText: 'Priority',
                          border: OutlineInputBorder(),
                        ),
                        items: ['low', 'medium', 'high']
                            .map((p) => DropdownMenuItem(value: p, child: Text(p.toUpperCase())))
                            .toList(),
                        onChanged: (v) => setState(() => selectedPriority = v ?? 'medium'),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: DropdownButtonFormField<String>(
                        initialValue: selectedCategory,
                        decoration: const InputDecoration(
                          labelText: 'Category',
                          border: OutlineInputBorder(),
                        ),
                        items: ['cold', 'lukewarm', 'warm', 'hot']
                            .map((c) => DropdownMenuItem(value: c, child: Text(c.toUpperCase())))
                            .toList(),
                        onChanged: (v) => setState(() => selectedCategory = v ?? 'warm'),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: notesCtrl,
                  decoration: const InputDecoration(
                    labelText: 'Initial Notes',
                    border: OutlineInputBorder(),
                  ),
                  maxLines: 2,
                ),
                const SizedBox(height: 20),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () async {
                      if (nameCtrl.text.isEmpty || phoneCtrl.text.isEmpty) {
                        if (mounted) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(content: Text('Name and Phone are required')),
                          );
                        }
                        return;
                      }
                      final lead = await _crm.createLead({
                        'name': nameCtrl.text,
                        'phone': phoneCtrl.text,
                        'email': emailCtrl.text.isNotEmpty ? emailCtrl.text : null,
                        'source': sourceCtrl.text,
                        'property_interest': propertyCtrl.text.isNotEmpty ? propertyCtrl.text : null,
                        'budget_range': budgetCtrl.text.isNotEmpty ? budgetCtrl.text : null,
                        'priority': selectedPriority,
                        'lead_category': selectedCategory,
                        'notes': notesCtrl.text.isNotEmpty ? notesCtrl.text : null,
                      });
                      if (ctx.mounted) Navigator.pop(ctx);
                      if (lead != null) {
                        _loadData();
                        if (mounted) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text('Lead created: ${lead.name}'),
                              backgroundColor: AppTheme.successColor,
                            ),
                          );
                        }
                      }
                    },
                    style: ElevatedButton.styleFrom(backgroundColor: AppTheme.primaryColor),
                    child: const Text('Create Lead'),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  // ─── Helpers ───────────────────────────────────────────────────────

  String _nextStage(String current) {
    const order = ['new', 'contacted', 'qualified', 'site_visit', 'proposal', 'negotiation', 'booking', 'won'];
    final idx = order.indexOf(current);
    if (idx < 0 || idx >= order.length - 1) return current;
    return order[idx + 1];
  }

  String _timeAgo(DateTime dt) {
    final diff = DateTime.now().difference(dt);
    if (diff.inMinutes < 1) return 'Just now';
    if (diff.inHours < 1) return '${diff.inMinutes}m ago';
    if (diff.inDays < 1) return '${diff.inHours}h ago';
    if (diff.inDays < 7) return '${diff.inDays}d ago';
    return DateFormat('dd MMM').format(dt);
  }
}

// ─── Stat Data ────────────────────────────────────────────────────────

class _StatData {
  final String label;
  final int value;
  final IconData icon;
  final Color color;
  const _StatData(this.label, this.value, this.icon, this.color);
}

// ─── Agent Lead Detail Bottom Sheet ────────────────────────────────────

class _AgentLeadDetailSheet extends StatefulWidget {
  final CRMLead lead;
  final CRMService crm;
  final VoidCallback onRefresh;

  const _AgentLeadDetailSheet({
    required this.lead,
    required this.crm,
    required this.onRefresh,
  });

  @override
  State<_AgentLeadDetailSheet> createState() => _AgentLeadDetailSheetState();
}

class _AgentLeadDetailSheetState extends State<_AgentLeadDetailSheet> {
  List<CRMInteraction> _interactions = [];
  bool _loadingInteractions = true;

  @override
  void initState() {
    super.initState();
    _loadInteractions();
  }

  Future<void> _loadInteractions() async {
    final interactions = await widget.crm.getInteractions(widget.lead.id);
    if (mounted) {
      setState(() {
        _interactions = interactions;
        _loadingInteractions = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final lead = widget.lead;
    return Container(
      padding: const EdgeInsets.all(20),
      child: Column(
        children: [
          // Handle
          Container(
            width: 40,
            height: 4,
            decoration: BoxDecoration(
              color: Colors.grey.withValues(alpha: 0.3),
              borderRadius: BorderRadius.circular(2),
            ),
          ),
          const SizedBox(height: 16),

          // Lead header
          Row(
            children: [
              CircleAvatar(
                radius: 24,
                backgroundColor: lead.categoryColor.withValues(alpha: 0.15),
                child: Text(
                  lead.name.isNotEmpty ? lead.name[0].toUpperCase() : '?',
                  style: TextStyle(
                    color: lead.categoryColor,
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
                    Text(lead.name,
                        style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                    Text(lead.phone ?? '', style: const TextStyle(color: Colors.grey)),
                    if (lead.email != null)
                      Text(lead.email!,
                          style: const TextStyle(color: Colors.grey, fontSize: 12)),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: lead.statusColor.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  (lead.status ?? 'unknown').replaceAll('_', ' ').toUpperCase(),
                  style: TextStyle(
                    color: lead.statusColor,
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(height: 16),

          // Info chips
          Wrap(
            spacing: 8,
            runSpacing: 6,
            children: [
              if (lead.source != null) _infoChip(Icons.source, lead.source!),
              if (lead.leadCategory != null)
                _infoChip(Icons.category, lead.leadCategory!.toUpperCase()),
              if (lead.propertyInterest != null)
                _infoChip(Icons.home, lead.propertyInterest!),
              if (lead.budgetRange != null)
                _infoChip(Icons.currency_rupee, '₹${lead.budgetRange}'),
              if (lead.assignedToName != null)
                _infoChip(Icons.person, lead.assignedToName!),
            ],
          ),

          const SizedBox(height: 16),

          // Quick actions
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceEvenly,
            children: [
              _actionBtn(Icons.call, 'Call', AppTheme.successColor, () {
                if (lead.phone != null) {
                  launchUrl(Uri(scheme: 'tel', path: lead.phone));
                }
              }),
              _actionBtn(Icons.sms, 'SMS', Colors.blue, () {
                if (lead.phone != null) {
                  launchUrl(Uri(scheme: 'sms', path: lead.phone));
                }
              }),
              _actionBtn(Icons.chat, 'WhatsApp', const Color(0xFF25D366), () {
                if (lead.phone != null) {
                  final cleaned = lead.phone!.replaceAll(RegExp(r'[^\d]'), '');
                  launchUrl(Uri.parse('https://wa.me/$cleaned'),
                      mode: LaunchMode.externalApplication);
                }
              }),
              _actionBtn(Icons.note_add, 'Note', AppTheme.warningColor,
                  () => _showAddInteraction('note')),
              _actionBtn(Icons.calendar_today, 'Follow-up', const Color(0xFF8B5CF6),
                  () => _showAddInteraction('follow_up')),
            ],
          ),

          const SizedBox(height: 16),

          // Timeline header
          const Align(
            alignment: Alignment.centerLeft,
            child: Text('Timeline', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
          ),
          const SizedBox(height: 8),

          // Interactions timeline
          Expanded(
            child: _loadingInteractions
                ? const Center(child: CircularProgressIndicator())
                : _interactions.isEmpty
                    ? const Center(
                        child: Text('No interactions yet',
                            style: TextStyle(color: Colors.grey)))
                    : ListView.separated(
                        itemCount: _interactions.length,
                        separatorBuilder: (_, __) => const SizedBox(height: 8),
                        itemBuilder: (ctx, i) => _buildInteractionItem(_interactions[i]),
                      ),
          ),
        ],
      ),
    );
  }

  Widget _infoChip(IconData icon, String text) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: Colors.grey.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(6),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 12, color: Colors.grey),
          const SizedBox(width: 4),
          Text(text, style: const TextStyle(fontSize: 11, color: Colors.grey)),
        ],
      ),
    );
  }

  Widget _actionBtn(IconData icon, String label, Color color, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Column(
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.12),
              shape: BoxShape.circle,
            ),
            child: Icon(icon, color: color, size: 20),
          ),
          const SizedBox(height: 4),
          Text(label, style: TextStyle(fontSize: 10, color: color)),
        ],
      ),
    );
  }

  Widget _buildInteractionItem(CRMInteraction interaction) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Expanded(
          child: Container(
            width: 32,
            height: 32,
            decoration: BoxDecoration(
              color: Colors.blue.withValues(alpha: 0.12),
              shape: BoxShape.circle,
            ),
            child: Icon(interaction.icon, size: 16, color: Colors.blue),
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: Colors.grey.withValues(alpha: 0.05),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Text(
                      interaction.interactionType.toUpperCase(),
                      style: const TextStyle(
                          fontSize: 11, fontWeight: FontWeight.w600, color: Colors.blue),
                    ),
                    const Spacer(),
                    Text(_formatTime(interaction.createdAt),
                        style: const TextStyle(fontSize: 10, color: Colors.grey)),
                  ],
                ),
                if (interaction.subject != null) ...[
                  const SizedBox(height: 4),
                  Text(interaction.subject!,
                      style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w500)),
                ],
                if (interaction.body != null) ...[
                  const SizedBox(height: 2),
                  Text(interaction.body!, style: const TextStyle(fontSize: 12, color: Colors.grey)),
                ],
                if (interaction.outcome != null) ...[
                  const SizedBox(height: 4),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                    decoration: BoxDecoration(
                      color: AppTheme.successColor.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: Text(interaction.outcome!,
                        style: const TextStyle(fontSize: 10, color: AppTheme.successColor)),
                  ),
                ],
              ],
            ),
          ),
        ),
      ],
    );
  }

  void _showAddInteraction(String type) {
    final bodyCtrl = TextEditingController();
    final subjectCtrl = TextEditingController();
    String direction = 'outbound';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => Padding(
        padding: EdgeInsets.only(bottom: MediaQuery.of(ctx).viewInsets.bottom),
        child: Container(
          padding: const EdgeInsets.all(20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('Add ${type.replaceAll('_', ' ').toUpperCase()}',
                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
              const SizedBox(height: 16),
              TextField(
                controller: subjectCtrl,
                decoration: const InputDecoration(
                  labelText: 'Subject',
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: bodyCtrl,
                decoration: const InputDecoration(
                  labelText: 'Notes',
                  border: OutlineInputBorder(),
                ),
                maxLines: 3,
              ),
              const SizedBox(height: 12),
              SegmentedButton<String>(
                segments: const [
                  ButtonSegment(value: 'outbound', label: Text('Outbound')),
                  ButtonSegment(value: 'inbound', label: Text('Inbound')),
                ],
                selected: {direction},
                onSelectionChanged: (v) => setState(() => direction = v.first),
              ),
              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () async {
                    await widget.crm.addInteraction(widget.lead.id, {
                      'type': type,
                      'direction': direction,
                      'subject': subjectCtrl.text.isNotEmpty ? subjectCtrl.text : null,
                      'body': bodyCtrl.text.isNotEmpty ? bodyCtrl.text : null,
                    });
                    if (ctx.mounted) Navigator.pop(ctx);
                    widget.onRefresh();
                    _loadInteractions();
                    if (mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                          content: Text('Interaction added'),
                          backgroundColor: AppTheme.successColor,
                        ),
                      );
                    }
                  },
                  style: ElevatedButton.styleFrom(backgroundColor: AppTheme.primaryColor),
                  child: const Text('Save'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  String _formatTime(DateTime dt) {
    final diff = DateTime.now().difference(dt);
    if (diff.inMinutes < 1) return 'Now';
    if (diff.inHours < 1) return '${diff.inMinutes}m ago';
    if (diff.inDays < 1) return '${diff.inHours}h ago';
    if (diff.inDays < 7) return '${diff.inDays}d ago';
    return DateFormat('dd MMM').format(dt);
  }
}

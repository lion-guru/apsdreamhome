import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/theme/app_theme.dart';
import '../../../data/models/crm_models.dart';
import '../../../data/services/crm_service.dart';

class CRMPage extends ConsumerStatefulWidget {
  const CRMPage({super.key});

  @override
  ConsumerState<CRMPage> createState() => _CRMPageState();
}

class _CRMPageState extends ConsumerState<CRMPage>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final CRMService _crm = CRMService();
  bool _isLoading = true;
  String? _error;
  CRMDashboardStats _stats = const CRMDashboardStats();
  List<CRMPipelineStage> _stages = [];
  Map<String, List<CRMLead>> _pipelineBoard = {};
  List<CRMTask> _tasks = [];
  int _selectedStageIdx = 0;
  String _searchQuery = '';
  String? _filterSource;
  String? _filterPriority;
  String? _filterCategory;
  int _currentPage = 1;
  bool _hasMore = true;
  List<CRMLead> _leadsList = [];
  bool _loadingLeads = false;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
    _loadData();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadData() async {
    setState(() { _isLoading = true; _error = null; });
    try {
      final dashboard = await _crm.getDashboard();
      final pipeline = await _crm.getPipeline();
      final tasks = await _crm.getMyTasks();
      setState(() {
        final statsData = dashboard['stats'];
        _stats = CRMDashboardStats.fromJson((statsData is Map<String, dynamic>) ? statsData : dashboard);
        _stages = (pipeline['stages'] as List?)
            ?.map((j) => CRMPipelineStage.fromJson(j as Map<String, dynamic>))
            .toList() ?? [];
        final board = pipeline['board'] as Map<String, dynamic>?;
        _pipelineBoard = {};
        board?.forEach((key, val) {
          _pipelineBoard[key] = (val as List?)
              ?.map((j) => CRMLead.fromJson(j as Map<String, dynamic>))
              .toList() ?? [];
        });
        _tasks = (tasks as List?)
            ?.map((j) => CRMTask.fromJson(j as Map<String, dynamic>))
            .toList() ?? [];
        _isLoading = false;
      });
    } catch (e) {
      setState(() { _error = e.toString(); _isLoading = false; });
    }
  }

  Future<void> _loadLeads({bool append = false}) async {
    setState(() { _loadingLeads = true; });
    try {
      final res = await _crm.getLeads(
        search: _searchQuery.isNotEmpty ? _searchQuery : null,
        source: _filterSource,
        priority: _filterPriority,
        category: _filterCategory,
        page: _currentPage,
        perPage: 25,
      );
      final leads = (res['leads'] as List?)
          ?.map((j) => CRMLead.fromJson(j as Map<String, dynamic>))
          .toList() ?? [];
      final total = res['total'] as int? ?? 0;
      setState(() {
        _leadsList = append ? [..._leadsList, ...leads] : leads;
        _hasMore = _leadsList.length < total;
        _loadingLeads = false;
      });
    } catch (e) {
      setState(() { _loadingLeads = false; });
    }
  }

  Future<void> _moveLead(int leadId, String newStage) async {
    final ok = await _crm.moveLeadToStage(leadId, newStage);
    if (ok) {
      _loadData();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Lead moved to ${newStage.replaceAll('_', ' ').toUpperCase()}'), backgroundColor: AppTheme.successColor),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }
    if (_error != null) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.error_outline, size: 48, color: Colors.red),
            const SizedBox(height: 12),
            Text(_error!, style: const TextStyle(color: Colors.red)),
            const SizedBox(height: 12),
            ElevatedButton.icon(
              onPressed: _loadData,
              icon: const Icon(Icons.refresh),
              label: const Text('Retry'),
            ),
          ],
        ),
      );
    }

    return Column(
      children: [
        _buildHeader(),
        _buildStatCards(),
        _buildTabBar(),
        Expanded(
          child: TabBarView(
            controller: _tabController,
            children: [
              _buildPipelineTab(),
              _buildLeadsListTab(),
              _buildTasksTab(),
              _buildAnalyticsTab(),
            ],
          ),
        ),
      ],
    );
  }

  // ─── Header ───────────────────────────────────────────────────────

  Widget _buildHeader() {
    return Container(
      padding: const EdgeInsets.all(16),
      child: Row(
        children: [
          const Icon(Icons.hub, color: AppTheme.primaryColor, size: 28),
          const SizedBox(width: 12),
          const Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('CRM Pipeline', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
                Text('Lead management & follow-ups', style: TextStyle(fontSize: 12, color: Colors.grey)),
              ],
            ),
          ),
          IconButton(
            onPressed: _loadData,
            icon: const Icon(Icons.refresh),
            tooltip: 'Refresh',
          ),
          const SizedBox(width: 8),
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

  // ─── Stat Cards ───────────────────────────────────────────────────

  Widget _buildStatCards() {
    final cards = [
      _StatCardData('Total Leads', _stats.totalLeads.toString(), Icons.people, AppTheme.primaryColor),
      _StatCardData('New', _stats.newLeads.toString(), Icons.fiber_new, Colors.blue),
      _StatCardData('Qualified', _stats.qualifiedLeads.toString(), Icons.verified, Colors.cyan),
      _StatCardData('Won', _stats.wonLeads.toString(), Icons.check_circle, AppTheme.successColor),
      _StatCardData('Tasks', '${_stats.pendingTasks}', Icons.task_alt, Colors.orange),
      _StatCardData('Overdue', '${_stats.overdueTasks}', Icons.warning, Colors.red),
    ];

    return SizedBox(
      height: 90,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        itemCount: cards.length,
        separatorBuilder: (_, __) => const SizedBox(width: 10),
        itemBuilder: (ctx, i) {
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
                Text(c.value, style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: c.color)),
                const SizedBox(height: 2),
                Text(c.label, style: const TextStyle(fontSize: 10, color: Colors.grey), maxLines: 1, overflow: TextOverflow.ellipsis),
              ],
            ),
          );
        },
      ),
    );
  }

  // ─── Tab Bar ──────────────────────────────────────────────────────

  Widget _buildTabBar() {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      decoration: BoxDecoration(
        color: Colors.grey.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(12),
      ),
      child: TabBar(
        controller: _tabController,
        indicator: BoxDecoration(
          color: AppTheme.primaryColor,
          borderRadius: BorderRadius.circular(10),
        ),
        labelColor: Colors.white,
        unselectedLabelColor: Colors.grey.shade600,
        labelStyle: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
        unselectedLabelStyle: const TextStyle(fontSize: 12),
        dividerColor: Colors.transparent,
        tabs: const [
          Tab(text: 'Pipeline'),
          Tab(text: 'Leads'),
          Tab(text: 'Tasks'),
          Tab(text: 'Analytics'),
        ],
      ),
    );
  }

  // ─── Pipeline Tab (Horizontal Kanban) ─────────────────────────────

  Widget _buildPipelineTab() {
    if (_stages.isEmpty) {
      return const Center(child: Text('No pipeline stages configured'));
    }

    return Column(
      children: [
        // Stage selector chips
        SizedBox(
          height: 42,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            itemCount: _stages.length,
            separatorBuilder: (_, __) => const SizedBox(width: 6),
            itemBuilder: (ctx, i) {
              final stage = _stages[i];
              final isSelected = _selectedStageIdx == i;
              return FilterChip(
                label: Text('${stage.label} (${stage.count})', style: TextStyle(fontSize: 12, color: isSelected ? Colors.white : Colors.black)),
                selected: isSelected,
                selectedColor: AppTheme.primaryColor,
                backgroundColor: Colors.grey.withValues(alpha: 0.1),
                onSelected: (_) => setState(() => _selectedStageIdx = i),
                visualDensity: VisualDensity.compact,
              );
            },
          ),
        ),

        // Kanban cards
        Expanded(
          child: _buildKanbanColumn(_stages[_selectedStageIdx]),
        ),
      ],
    );
  }

  Widget _buildKanbanColumn(CRMPipelineStage stage) {
    final leads = _pipelineBoard[stage.status] ?? [];
    final statusColor = _statusColor(stage.status);

    return Column(
      children: [
        // Column header
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
          child: Row(
            children: [
              Container(width: 4, height: 16, decoration: BoxDecoration(color: statusColor, borderRadius: BorderRadius.circular(2))),
              const SizedBox(width: 8),
              Text(stage.label, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
              const Spacer(),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(color: statusColor.withValues(alpha: 0.15), borderRadius: BorderRadius.circular(8)),
                child: Text('${leads.length}', style: TextStyle(color: statusColor, fontSize: 12, fontWeight: FontWeight.w600)),
              ),
              const SizedBox(width: 8),
              Text('₹${_formatAmount(stage.totalValue)}', style: const TextStyle(fontSize: 11, color: Colors.grey)),
            ],
          ),
        ),

        // Lead cards
        Expanded(
          child: leads.isEmpty
              ? Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.inbox, size: 48, color: Colors.grey.withValues(alpha: 0.3)),
                      const SizedBox(height: 8),
                      Text('No leads in ${stage.label}', style: TextStyle(color: Colors.grey.shade500, fontSize: 13)),
                    ],
                  ),
                )
              : ListView.separated(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  itemCount: leads.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 8),
                  itemBuilder: (ctx, i) => _buildLeadCard(leads[i], stage.status),
                ),
        ),
      ],
    );
  }

  Widget _buildLeadCard(CRMLead lead, String currentStage) {
    return Dismissible(
      key: ValueKey('lead_${lead.id}'),
      direction: DismissDirection.horizontal,
      onDismissed: (dir) {
        if (dir == DismissDirection.endToStart) {
          _moveLead(lead.id, _nextStage(currentStage));
        } else {
          _moveLead(lead.id, _prevStage(currentStage));
        }
      },
      background: Container(
        alignment: Alignment.centerLeft,
        padding: const EdgeInsets.only(left: 20),
        decoration: BoxDecoration(color: Colors.green, borderRadius: BorderRadius.circular(12)),
        child: const Icon(Icons.arrow_back, color: Colors.white),
      ),
      secondaryBackground: Container(
        alignment: Alignment.centerRight,
        padding: const EdgeInsets.only(right: 20),
        decoration: BoxDecoration(color: Colors.blue, borderRadius: BorderRadius.circular(12)),
        child: const Icon(Icons.arrow_forward, color: Colors.white),
      ),
      child: GestureDetector(
        onTap: () => _showLeadDetail(lead),
        child: Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: Colors.grey.withValues(alpha: 0.15)),
            boxShadow: [
              BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 6, offset: const Offset(0, 2)),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  CircleAvatar(
                    radius: 16,
                    backgroundColor: lead.categoryColor.withValues(alpha: 0.15),
                    child: Text(lead.name.isNotEmpty ? lead.name[0].toUpperCase() : '?',
                        style: TextStyle(color: lead.categoryColor, fontWeight: FontWeight.bold, fontSize: 13)),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(lead.name, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13), maxLines: 1, overflow: TextOverflow.ellipsis),
                        Text(lead.phone ?? 'No phone', style: const TextStyle(fontSize: 11, color: Colors.grey)),
                      ],
                    ),
                  ),
                  _buildScoreBadge(lead.leadScore ?? 0),
                ],
              ),
              if (lead.propertyInterest != null || lead.budgetRange != null) ...[
                const SizedBox(height: 8),
                Wrap(
                  spacing: 6,
                  runSpacing: 4,
                  children: [
                    if (lead.propertyInterest != null)
                      _tag(lead.propertyInterest!, Colors.blue),
                    if (lead.budgetRange != null)
                      _tag('₹${lead.budgetRange}', Colors.green),
                    if (lead.source != null)
                      _tag(lead.source!, Colors.purple),
                  ],
                ),
              ],
              const SizedBox(height: 8),
              Row(
                children: [
                  if (lead.nextFollowupDate != null) ...[
                    Icon(Icons.schedule, size: 12, color: lead.nextFollowupDate!.isBefore(DateTime.now()) ? Colors.red : Colors.orange),
                    const SizedBox(width: 4),
                    Text(_formatDate(lead.nextFollowupDate!),
                        style: TextStyle(fontSize: 10, color: lead.nextFollowupDate!.isBefore(DateTime.now()) ? Colors.red : Colors.orange)),
                  ],
                  const Spacer(),
                  if (lead.assignedToName != null)
                    Text(lead.assignedToName!, style: const TextStyle(fontSize: 10, color: Colors.grey)),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildScoreBadge(int score) {
    Color color;
    if (score >= 80) {
      color = Colors.green;
    } else if (score >= 60) {
      color = Colors.orange;
    } else if (score >= 40) {
      color = Colors.blue;
    } else {
      color = Colors.grey;
    }
    return Container(
      width: 32, height: 32,
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        shape: BoxShape.circle,
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Center(
        child: Text('$score', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: color)),
      ),
    );
  }

  Widget _tag(String text, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(4),
      ),
      child: Text(text, style: TextStyle(fontSize: 10, color: color)),
    );
  }

  // ─── Leads List Tab ───────────────────────────────────────────────

  Widget _buildLeadsListTab() {
    return Column(
      children: [
        // Search + filters
        Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            children: [
              Expanded(
                child: TextField(
                  decoration: InputDecoration(
                    hintText: 'Search leads...',
                    prefixIcon: const Icon(Icons.search, size: 20),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                    contentPadding: const EdgeInsets.symmetric(vertical: 0, horizontal: 12),
                    isDense: true,
                  ),
                  onChanged: (v) { _searchQuery = v; _currentPage = 1; _loadLeads(); },
                ),
              ),
              const SizedBox(width: 8),
              PopupMenuButton<String>(
                icon: Badge(
                  isLabelVisible: _filterSource != null || _filterPriority != null || _filterCategory != null,
                  child: const Icon(Icons.filter_list),
                ),
                onSelected: (v) {
                  if (v == 'clear') {
                    setState(() { _filterSource = null; _filterPriority = null; _filterCategory = null; });
                  } else if (v.startsWith('source:')) {
                    setState(() { _filterSource = v.substring(7); });
                  } else if (v.startsWith('priority:')) {
                    setState(() { _filterPriority = v.substring(9); });
                  } else if (v.startsWith('cat:')) {
                    setState(() { _filterCategory = v.substring(4); });
                  }
                  _currentPage = 1;
                  _loadLeads();
                },
                itemBuilder: (_) => [
                  const PopupMenuItem(value: 'clear', child: Text('Clear all')),
                  const PopupMenuDivider(),
                  const PopupMenuItem(value: 'source:website', child: Text('Source: Website')),
                  const PopupMenuItem(value: 'source:whatsapp', child: Text('Source: WhatsApp')),
                  const PopupMenuItem(value: 'source:walk-in', child: Text('Source: Walk-in')),
                  const PopupMenuDivider(),
                  const PopupMenuItem(value: 'priority:high', child: Text('Priority: High')),
                  const PopupMenuItem(value: 'priority:medium', child: Text('Priority: Medium')),
                  const PopupMenuDivider(),
                  const PopupMenuItem(value: 'cat:hot', child: Text('Hot Leads')),
                  const PopupMenuItem(value: 'cat:warm', child: Text('Warm Leads')),
                  const PopupMenuItem(value: 'cat:cold', child: Text('Cold Leads')),
                ],
              ),
            ],
          ),
        ),

        // Leads list
        Expanded(
          child: _loadingLeads && _leadsList.isEmpty
              ? const Center(child: CircularProgressIndicator())
              : _leadsList.isEmpty
                  ? const Center(child: Text('No leads found'))
                  : RefreshIndicator(
                      onRefresh: () async { _currentPage = 1; await _loadLeads(); },
                      child: ListView.separated(
                        padding: const EdgeInsets.symmetric(horizontal: 12),
                        itemCount: _leadsList.length + (_hasMore ? 1 : 0),
                        separatorBuilder: (_, __) => const SizedBox(height: 8),
                        itemBuilder: (ctx, i) {
                          if (i == _leadsList.length) {
                            _currentPage++;
                            _loadLeads(append: true);
                            return const Padding(
                              padding: EdgeInsets.all(16),
                              child: Center(child: CircularProgressIndicator()),
                            );
                          }
                          return _buildLeadListItem(_leadsList[i]);
                        },
                      ),
                    ),
        ),
      ],
    );
  }

  Widget _buildLeadListItem(CRMLead lead) {
    return GestureDetector(
      onTap: () => _showLeadDetail(lead),
      child: Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: Colors.grey.withValues(alpha: 0.12)),
        ),
        child: Row(
          children: [
            CircleAvatar(
              radius: 20,
              backgroundColor: lead.statusColor.withValues(alpha: 0.15),
              child: Text(lead.name.isNotEmpty ? lead.name[0].toUpperCase() : '?',
                  style: TextStyle(color: lead.statusColor, fontWeight: FontWeight.bold)),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(lead.name, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14),
                            maxLines: 1, overflow: TextOverflow.ellipsis),
                      ),
                      _buildScoreBadge(lead.leadScore ?? 0),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      if (lead.phone != null) ...[
                        const Icon(Icons.phone, size: 12, color: Colors.grey),
                        const SizedBox(width: 4),
                        Text(lead.phone!, style: const TextStyle(fontSize: 12, color: Colors.grey)),
                        const SizedBox(width: 8),
                      ],
                      if (lead.source != null)
                        _tag(lead.source!, Colors.purple),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: lead.statusColor.withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(_statusLabel(lead.status), style: TextStyle(fontSize: 10, color: lead.statusColor, fontWeight: FontWeight.w600)),
                      ),
                      const SizedBox(width: 6),
                      if (lead.leadCategory != null)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: lead.categoryColor.withValues(alpha: 0.12),
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: Text(lead.leadCategory!.toUpperCase(),
                              style: TextStyle(fontSize: 10, color: lead.categoryColor, fontWeight: FontWeight.w600)),
                        ),
                      const Spacer(),
                      if (lead.assignedToName != null)
                        Text(lead.assignedToName!, style: const TextStyle(fontSize: 10, color: Colors.grey)),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(width: 8),
            const Icon(Icons.chevron_right, color: Colors.grey, size: 20),
          ],
        ),
      ),
    );
  }

  // ─── Tasks Tab ────────────────────────────────────────────────────

  Widget _buildTasksTab() {
    if (_tasks.isEmpty) {
      return RefreshIndicator(
        onRefresh: _loadData,
        child: ListView(
          children: [
            SizedBox(height: MediaQuery.of(context).size.height * 0.15),
            Icon(Icons.task_alt, size: 64, color: Colors.grey.withValues(alpha: 0.3)),
            const SizedBox(height: 12),
            const Center(child: Text('No pending tasks', style: TextStyle(color: Colors.grey))),
            const SizedBox(height: 8),
            const Center(child: Text('Create follow-ups from lead detail', style: TextStyle(color: Colors.grey, fontSize: 12))),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadData,
      child: ListView.separated(
        padding: const EdgeInsets.all(12),
        itemCount: _tasks.length,
        separatorBuilder: (_, __) => const SizedBox(height: 8),
        itemBuilder: (ctx, i) => _buildTaskCard(_tasks[i]),
      ),
    );
  }

  Widget _buildTaskCard(CRMTask task) {
    final isOverdue = task.isOverdue;
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: isOverdue ? Colors.red.withValues(alpha: 0.3) : Colors.grey.withValues(alpha: 0.12)),
      ),
      child: Row(
        children: [
          GestureDetector(
            onTap: () async {
              await _crm.completeTask(task.id);
              _loadData();
              if (mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Task completed'), backgroundColor: Colors.green),
                );
              }
            },
            child: Icon(
              task.status == 'completed' ? Icons.check_circle : Icons.radio_button_unchecked,
              color: task.status == 'completed' ? Colors.green : Colors.grey,
              size: 24,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(task.title, style: TextStyle(
                  fontWeight: FontWeight.w600, fontSize: 13,
                  decoration: task.status == 'completed' ? TextDecoration.lineThrough : null,
                )),
                const SizedBox(height: 4),
                Row(
                  children: [
                    _tag(task.taskType, Colors.blue),
                    const SizedBox(width: 6),
                    _tag(task.priority, task.priority == 'high' ? Colors.red : Colors.orange),
                    if (isOverdue) ...[
                      const SizedBox(width: 6),
                      _tag('OVERDUE', Colors.red),
                    ],
                  ],
                ),
                if (task.dueDate != null) ...[
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      Icon(Icons.schedule, size: 12, color: isOverdue ? Colors.red : Colors.grey),
                      const SizedBox(width: 4),
                      Text('${_formatDate(task.dueDate!)}${task.dueTime != null ? ' ${task.dueTime}' : ''}',
                          style: TextStyle(fontSize: 11, color: isOverdue ? Colors.red : Colors.grey)),
                    ],
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }

  // ─── Analytics Tab ────────────────────────────────────────────────

  Widget _buildAnalyticsTab() {
    return RefreshIndicator(
      onRefresh: _loadData,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _analyticsCard('Conversion Rate', '${_stats.conversionRate.toStringAsFixed(1)}%', Icons.trending_up, AppTheme.successColor),
          const SizedBox(height: 12),
          _analyticsCard('Total Pipeline Value', '₹${_formatAmount(_stats.totalValue)}', Icons.monetization_on, Colors.amber),
          const SizedBox(height: 16),
          const Text('By Source', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
          const SizedBox(height: 8),
          ..._stats.bySource.entries.map((e) => _barRow(e.key, e.value, _stats.totalLeads)),
          const SizedBox(height: 16),
          const Text('By Priority', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
          const SizedBox(height: 8),
          ..._stats.byPriority.entries.map((e) => _barRow(e.key, e.value, _stats.totalLeads)),
          const SizedBox(height: 16),
          const Text('Pipeline Overview', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
          const SizedBox(height: 8),
          ..._stages.map((s) => _barRow(s.label, s.count, _stats.totalLeads)),
        ],
      ),
    );
  }

  Widget _analyticsCard(String label, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withValues(alpha: 0.15)),
      ),
      child: Row(
        children: [
          Icon(icon, color: color, size: 28),
          const SizedBox(width: 12),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(value, style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: color)),
              Text(label, style: const TextStyle(fontSize: 12, color: Colors.grey)),
            ],
          ),
        ],
      ),
    );
  }

  Widget _barRow(String label, int value, int total) {
    final pct = total > 0 ? value / total : 0.0;
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        children: [
          SizedBox(width: 100, child: Text(label, style: const TextStyle(fontSize: 12), maxLines: 1, overflow: TextOverflow.ellipsis)),
          const SizedBox(width: 8),
          Expanded(
            child: ClipRRect(
              borderRadius: BorderRadius.circular(4),
              child: LinearProgressIndicator(
                value: pct,
                backgroundColor: Colors.grey.withValues(alpha: 0.15),
                valueColor: AlwaysStoppedAnimation<Color>(AppTheme.primaryColor.withValues(alpha: 0.7)),
                minHeight: 8,
              ),
            ),
          ),
          const SizedBox(width: 8),
          SizedBox(width: 30, child: Text('$value', style: const TextStyle(fontSize: 11), textAlign: TextAlign.right)),
        ],
      ),
    );
  }

  // ─── Dialogs ──────────────────────────────────────────────────────

  void _showLeadDetail(CRMLead lead) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) => DraggableScrollableSheet(
        initialChildSize: 0.7,
        maxChildSize: 0.95,
        minChildSize: 0.4,
        expand: false,
        builder: (ctx, scrollCtrl) => _LeadDetailSheet(lead: lead, crm: _crm, onRefresh: _loadData),
      ),
    );
  }

  void _showCreateLeadDialog() {
    final nameCtrl = TextEditingController();
    final phoneCtrl = TextEditingController();
    final emailCtrl = TextEditingController();
    final sourceCtrl = TextEditingController(text: 'website');
    final propertyCtrl = TextEditingController();
    final budgetCtrl = TextEditingController();
    String selectedPriority = 'medium';
    String selectedCategory = 'warm';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) => Padding(
        padding: EdgeInsets.only(bottom: MediaQuery.of(ctx).viewInsets.bottom),
        child: Container(
          padding: const EdgeInsets.all(20),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('New Lead', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                const SizedBox(height: 16),
                TextField(controller: nameCtrl, decoration: const InputDecoration(labelText: 'Name *', border: OutlineInputBorder())),
                const SizedBox(height: 12),
                TextField(controller: phoneCtrl, decoration: const InputDecoration(labelText: 'Phone *', border: OutlineInputBorder()), keyboardType: TextInputType.phone),
                const SizedBox(height: 12),
                TextField(controller: emailCtrl, decoration: const InputDecoration(labelText: 'Email', border: OutlineInputBorder()), keyboardType: TextInputType.emailAddress),
                const SizedBox(height: 12),
                TextField(controller: sourceCtrl, decoration: const InputDecoration(labelText: 'Source', border: OutlineInputBorder())),
                const SizedBox(height: 12),
                TextField(controller: propertyCtrl, decoration: const InputDecoration(labelText: 'Property Interest', border: OutlineInputBorder())),
                const SizedBox(height: 12),
                TextField(controller: budgetCtrl, decoration: const InputDecoration(labelText: 'Budget Range', border: OutlineInputBorder()), keyboardType: TextInputType.number),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(
                      child: DropdownButtonFormField<String>(
                        initialValue: selectedPriority,
                        decoration: const InputDecoration(labelText: 'Priority', border: OutlineInputBorder()),
                        items: ['low', 'medium', 'high'].map((p) => DropdownMenuItem(value: p, child: Text(p.toUpperCase()))).toList(),
                        onChanged: (v) => setState(() => selectedPriority = v ?? 'medium'),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: DropdownButtonFormField<String>(
                        initialValue: selectedCategory,
                        decoration: const InputDecoration(labelText: 'Category', border: OutlineInputBorder()),
                        items: ['cold', 'lukewarm', 'warm', 'hot'].map((c) => DropdownMenuItem(value: c, child: Text(c.toUpperCase()))).toList(),
                        onChanged: (v) => setState(() => selectedCategory = v ?? 'warm'),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 20),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () async {
                      if (nameCtrl.text.isEmpty || phoneCtrl.text.isEmpty) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Name and Phone are required')),
                        );
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
                      });
                      if (ctx.mounted) Navigator.pop(ctx);
                      if (lead != null) {
                        _loadData();
                        if (mounted) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(content: Text('Lead created: ${lead.name}'), backgroundColor: AppTheme.successColor),
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

  // ─── Helpers ──────────────────────────────────────────────────────

  Color _statusColor(String status) {
    switch (status) {
      case 'new': return Colors.blue;
      case 'contacted': return Colors.purple;
      case 'qualified': return Colors.cyan;
      case 'site_visit': return Colors.orange;
      case 'proposal': return Colors.deepOrange;
      case 'negotiation': return Colors.pink;
      case 'booking': return Colors.indigo;
      case 'won': return Colors.green;
      case 'lost': return Colors.red;
      case 'nurture': return Colors.teal;
      default: return Colors.grey;
    }
  }

  String _statusLabel(String? status) {
    return (status ?? 'unknown').replaceAll('_', ' ').toUpperCase();
  }

  String _nextStage(String current) {
    final order = ['new', 'contacted', 'qualified', 'site_visit', 'proposal', 'negotiation', 'booking', 'won'];
    final idx = order.indexOf(current);
    if (idx < 0 || idx >= order.length - 1) return current;
    return order[idx + 1];
  }

  String _prevStage(String current) {
    final order = ['new', 'contacted', 'qualified', 'site_visit', 'proposal', 'negotiation', 'booking', 'won'];
    final idx = order.indexOf(current);
    if (idx <= 0) return current;
    return order[idx - 1];
  }

  String _formatAmount(double amount) {
    if (amount >= 10000000) return '${(amount / 10000000).toStringAsFixed(1)}Cr';
    if (amount >= 100000) return '${(amount / 100000).toStringAsFixed(1)}L';
    if (amount >= 1000) return '${(amount / 1000).toStringAsFixed(1)}K';
    return amount.toStringAsFixed(0);
  }

  String _formatDate(DateTime dt) {
    final now = DateTime.now();
    final diff = dt.difference(now).inDays;
    if (diff == 0) return 'Today';
    if (diff == 1) return 'Tomorrow';
    if (diff == -1) return 'Yesterday';
    return DateFormat('dd MMM').format(dt);
  }
}

class _StatCardData {
  final String label;
  final String value;
  final IconData icon;
  final Color color;
  _StatCardData(this.label, this.value, this.icon, this.color);
}

// ─── Lead Detail Bottom Sheet ────────────────────────────────────────

class _LeadDetailSheet extends StatefulWidget {
  final CRMLead lead;
  final CRMService crm;
  final VoidCallback onRefresh;

  const _LeadDetailSheet({required this.lead, required this.crm, required this.onRefresh});

  @override
  State<_LeadDetailSheet> createState() => _LeadDetailSheetState();
}

class _LeadDetailSheetState extends State<_LeadDetailSheet> {
  List<CRMInteraction> _interactions = [];
  bool _loadingInteractions = true;

  @override
  void initState() {
    super.initState();
    _loadInteractions();
  }

  Future<void> _loadInteractions() async {
    final interactions = await widget.crm.getInteractions(widget.lead.id);
    setState(() { _interactions = interactions; _loadingInteractions = false; });
  }

  @override
  Widget build(BuildContext context) {
    final lead = widget.lead;
    return Container(
      padding: const EdgeInsets.all(20),
      child: Column(
        children: [
          // Handle
          Container(width: 40, height: 4, decoration: BoxDecoration(color: Colors.grey.withValues(alpha: 0.3), borderRadius: BorderRadius.circular(2))),
          const SizedBox(height: 16),

          // Lead header
          Row(
            children: [
              CircleAvatar(
                radius: 24,
                backgroundColor: lead.statusColor.withValues(alpha: 0.15),
                child: Text(lead.name.isNotEmpty ? lead.name[0].toUpperCase() : '?',
                    style: TextStyle(color: lead.statusColor, fontWeight: FontWeight.bold, fontSize: 18)),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(lead.name, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                    Text(lead.phone ?? '', style: const TextStyle(color: Colors.grey)),
                    if (lead.email != null) Text(lead.email!, style: const TextStyle(color: Colors.grey, fontSize: 12)),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: lead.statusColor.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(_statusLabel(lead.status), style: TextStyle(color: lead.statusColor, fontSize: 11, fontWeight: FontWeight.w600)),
              ),
            ],
          ),

          const SizedBox(height: 16),

          // Info row
          Wrap(
            spacing: 8,
            runSpacing: 6,
            children: [
              if (lead.source != null) _infoChip(Icons.source, lead.source!),
              if (lead.leadCategory != null) _infoChip(Icons.category, lead.leadCategory!.toUpperCase()),
              if (lead.propertyInterest != null) _infoChip(Icons.home, lead.propertyInterest!),
              if (lead.budgetRange != null) _infoChip(Icons.currency_rupee, '₹${lead.budgetRange}'),
              if (lead.assignedToName != null) _infoChip(Icons.person, lead.assignedToName!),
            ],
          ),

          const SizedBox(height: 16),

          // Quick actions
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceEvenly,
            children: [
              _actionBtn(Icons.call, 'Call', Colors.green, () {
                if (lead.phone != null) {
                  launchUrl(Uri(scheme: 'tel', path: lead.phone));
                }
              }),
              _actionBtn(Icons.sms, 'SMS', Colors.blue, () {}),
              _actionBtn(Icons.chat, 'WhatsApp', Colors.teal, () {}),
              _actionBtn(Icons.note_add, 'Note', Colors.orange, () => _showAddInteraction('note')),
              _actionBtn(Icons.calendar_today, 'Follow-up', Colors.purple, () => _showAddInteraction('follow_up')),
            ],
          ),

          const SizedBox(height: 16),

          // Interactions timeline
          const Align(alignment: Alignment.centerLeft, child: Text('Timeline', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14))),
          const SizedBox(height: 8),

          Expanded(
            child: _loadingInteractions
                ? const Center(child: CircularProgressIndicator())
                : _interactions.isEmpty
                    ? const Center(child: Text('No interactions yet', style: TextStyle(color: Colors.grey)))
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
            width: 40, height: 40,
            decoration: BoxDecoration(color: color.withValues(alpha: 0.12), shape: BoxShape.circle),
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
          child: Column(
            children: [
              Container(
                width: 32, height: 32,
                decoration: BoxDecoration(
                  color: Colors.blue.withValues(alpha: 0.12),
                  shape: BoxShape.circle,
                ),
                child: Icon(interaction.icon, size: 16, color: Colors.blue),
              ),
            ],
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
                    Text(interaction.interactionType.toUpperCase(),
                        style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Colors.blue)),
                    const Spacer(),
                    Text(_formatTime(interaction.createdAt), style: const TextStyle(fontSize: 10, color: Colors.grey)),
                  ],
                ),
                if (interaction.subject != null) ...[
                  const SizedBox(height: 4),
                  Text(interaction.subject!, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w500)),
                ],
                if (interaction.body != null) ...[
                  const SizedBox(height: 2),
                  Text(interaction.body!, style: const TextStyle(fontSize: 12, color: Colors.grey)),
                ],
                if (interaction.outcome != null) ...[
                  const SizedBox(height: 4),
                  _tag(interaction.outcome!, Colors.green),
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
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
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
              TextField(controller: subjectCtrl, decoration: const InputDecoration(labelText: 'Subject', border: OutlineInputBorder())),
              const SizedBox(height: 12),
              TextField(controller: bodyCtrl, decoration: const InputDecoration(labelText: 'Notes', border: OutlineInputBorder()), maxLines: 3),
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
                        const SnackBar(content: Text('Interaction added'), backgroundColor: Colors.green),
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
    final now = DateTime.now();
    final diff = now.difference(dt);
    if (diff.inMinutes < 1) return 'Now';
    if (diff.inHours < 1) return '${diff.inMinutes}m ago';
    if (diff.inDays < 1) return '${diff.inHours}h ago';
    return '${diff.inDays}d ago';
  }

  String _statusLabel(String? status) => (status ?? 'unknown').replaceAll('_', ' ').toUpperCase();

  Widget _tag(String text, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
      decoration: BoxDecoration(color: color.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(4)),
      child: Text(text, style: TextStyle(fontSize: 10, color: color)),
    );
  }
}

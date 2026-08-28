import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/theme/app_theme.dart';
import '../../../data/models/crm_models.dart';
import '../../../data/services/crm_service.dart';

class EmployeeCRMPage extends ConsumerStatefulWidget {
  const EmployeeCRMPage({super.key});

  @override
  ConsumerState<EmployeeCRMPage> createState() => _EmployeeCRMPageState();
}

class _EmployeeCRMPageState extends ConsumerState<EmployeeCRMPage>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final CRMService _crm = CRMService();

  bool _isLoading = true;
  String? _error;
  CRMDashboardStats _stats = const CRMDashboardStats();

  // Leads tab
  String _searchQuery = '';
  String _filterStatus = 'all';
  int _currentPage = 1;
  bool _hasMore = true;
  bool _loadingLeads = false;
  List<CRMLead> _leadsList = [];
  final ScrollController _leadsScrollController = ScrollController();

  // Tasks tab
  List<CRMTask> _tasks = [];
  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _leadsScrollController.addListener(_onLeadsScroll);
    _loadData();
  }

  @override
  void dispose() {
    _tabController.dispose();
    _leadsScrollController.removeListener(_onLeadsScroll);
    _leadsScrollController.dispose();
    super.dispose();
  }

  void _onLeadsScroll() {
    if (_leadsScrollController.position.pixels >=
            _leadsScrollController.position.maxScrollExtent - 200 &&
        !_loadingLeads &&
        _hasMore) {
      _currentPage++;
      _loadLeads(append: true);
    }
  }

  Future<void> _loadData() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final dashboard = await _crm.getDashboard();
      final tasks = await _crm.getMyTasks();
      setState(() {
        final statsData = dashboard['stats'];
        _stats = CRMDashboardStats.fromJson(
            (statsData is Map<String, dynamic>) ? statsData : dashboard);
        _tasks = (tasks as List?)
                ?.map((j) => CRMTask.fromJson(j as Map<String, dynamic>))
                .toList() ??
            [];
        _isLoading = false;
      });
      _loadLeads();
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  Future<void> _loadLeads({bool append = false}) async {
    setState(() => _loadingLeads = true);
    try {
      final params = <String, dynamic>{
        'page': _currentPage,
        'per_page': 25,
      };
      if (_searchQuery.isNotEmpty) params['search'] = _searchQuery;
      if (_filterStatus != 'all') {
        final statusMap = {
          'new': 'new',
          'in_progress': 'contacted',
          'follow_up': 'qualified',
          'completed': 'won',
        };
        params['status'] = statusMap[_filterStatus] ?? _filterStatus;
      }

      final res = await _crm.getLeads(
        search: params['search'] as String?,
        status: params['status'] as String?,
        page: params['page'] as int?,
        perPage: params['per_page'] as int?,
      );
      final leads = (res['leads'] as List?)
              ?.map((j) => CRMLead.fromJson(j as Map<String, dynamic>))
              .toList() ??
          [];
      final total = res['total'] as int? ?? 0;
      setState(() {
        _leadsList = append ? [..._leadsList, ...leads] : leads;
        _hasMore = _leadsList.length < total;
        _loadingLeads = false;
      });
    } catch (e) {
      setState(() => _loadingLeads = false);
    }
  }

  Future<void> _completeTask(int taskId) async {
    final ok = await _crm.completeTask(taskId);
    if (ok) {
      await _loadData();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Task marked as complete'),
            backgroundColor: AppTheme.successColor,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return _buildShimmerSkeleton();
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
              _buildLeadsTab(),
              _buildTasksTab(),
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
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
              ),
              borderRadius: BorderRadius.circular(10),
            ),
            child: const Icon(Icons.people_alt, color: Colors.white, size: 20),
          ),
          const SizedBox(width: 12),
          const Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Employee CRM',
                    style:
                        TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
                Text('Your assigned leads & tasks',
                    style: TextStyle(fontSize: 12, color: Colors.grey)),
              ],
            ),
          ),
          IconButton(
            onPressed: _loadData,
            icon: const Icon(Icons.refresh),
            tooltip: 'Refresh',
          ),
        ],
      ),
    );
  }

  // ─── Stat Cards ───────────────────────────────────────────────────

  Widget _buildStatCards() {
    final int inProgressCount = _stats.contactedLeads + _stats.qualifiedLeads;
    final int completedToday = _tasks
        .where((t) =>
            t.status == 'completed' &&
            t.createdAt.day == DateTime.now().day &&
            t.createdAt.month == DateTime.now().month &&
            t.createdAt.year == DateTime.now().year)
        .length;

    final cards = [
      _StatCardData(
          'Total Assigned', _stats.totalLeads.toString(), Icons.assignment, AppTheme.primaryColor),
      _StatCardData(
          'In Progress', inProgressCount.toString(), Icons.autorenew, Colors.orange),
      _StatCardData(
          'Completed Today', completedToday.toString(), Icons.check_circle, AppTheme.successColor),
    ];

    return SizedBox(
      height: 96,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        itemCount: cards.length,
        separatorBuilder: (_, _) => const SizedBox(width: 10),
        itemBuilder: (ctx, i) {
          final c = cards[i];
          return Container(
            width: MediaQuery.of(context).size.width * 0.28,
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
                Container(
                  width: 28,
                  height: 28,
                  decoration: BoxDecoration(
                    color: c.color.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(7),
                  ),
                  child: Icon(c.icon, color: c.color, size: 16),
                ),
                const SizedBox(height: 8),
                Text(c.value,
                    style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: c.color)),
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
        labelStyle:
            const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
        unselectedLabelStyle: const TextStyle(fontSize: 13),
        dividerColor: Colors.transparent,
        tabs: [
          Tab(text: 'Leads (${_leadsList.length})'),
          Tab(text: 'Tasks (${_tasks.where((t) => t.status != 'completed').length})'),
        ],
      ),
    );
  }

  // ─── Leads Tab ────────────────────────────────────────────────────

  Widget _buildLeadsTab() {
    return Column(
      children: [
        // Search + status filter
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 8, 16, 8),
          child: Column(
            children: [
              // Search field
              TextField(
                decoration: InputDecoration(
                  hintText: 'Search leads...',
                  hintStyle: TextStyle(color: Colors.grey.shade400),
                  prefixIcon:
                      const Icon(Icons.search, size: 20, color: Colors.grey),
                  suffixIcon: _searchQuery.isNotEmpty
                      ? IconButton(
                          icon: const Icon(Icons.clear, size: 18),
                          onPressed: () {
                            _searchQuery = '';
                            _currentPage = 1;
                            _loadLeads();
                          },
                        )
                      : null,
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(10),
                    borderSide: BorderSide(color: Colors.grey.shade300),
                  ),
                  contentPadding:
                      const EdgeInsets.symmetric(vertical: 0, horizontal: 12),
                  isDense: true,
                  filled: true,
                  fillColor: Colors.grey.withValues(alpha: 0.05),
                ),
                onChanged: (v) {
                  _searchQuery = v;
                  _currentPage = 1;
                  _loadLeads();
                },
              ),
              const SizedBox(height: 8),
              // Status filter chips
              SizedBox(
                height: 34,
                child: ListView(
                  scrollDirection: Axis.horizontal,
                  children: [
                    _buildFilterChip('All', 'all'),
                    _buildFilterChip('New', 'new'),
                    _buildFilterChip('In Progress', 'in_progress'),
                    _buildFilterChip('Follow-up', 'follow_up'),
                    _buildFilterChip('Completed', 'completed'),
                  ],
                ),
              ),
            ],
          ),
        ),

        // Leads list
        Expanded(
          child: _loadingLeads && _leadsList.isEmpty
              ? _buildShimmerSkeleton()
              : _leadsList.isEmpty
                  ? _buildEmptyLeadsState()
                  : RefreshIndicator(
                      onRefresh: () async {
                        _currentPage = 1;
                        await _loadLeads();
                      },
                      child: ListView.separated(
                        controller: _leadsScrollController,
                        padding: const EdgeInsets.symmetric(
                            horizontal: 16, vertical: 8),
                        itemCount:
                            _leadsList.length + (_hasMore ? 1 : 0),
                        separatorBuilder: (_, _) =>
                            const SizedBox(height: 10),
                        itemBuilder: (ctx, i) {
                          if (i == _leadsList.length) {
                            if (!_loadingLeads) {
                              _currentPage++;
                              _loadLeads(append: true);
                            }
                            return const Padding(
                              padding: EdgeInsets.all(16),
                              child:
                                  Center(child: CircularProgressIndicator()),
                            );
                          }
                          return _buildLeadCard(_leadsList[i]);
                        },
                      ),
                    ),
        ),
      ],
    );
  }

  Widget _buildFilterChip(String label, String value) {
    final isSelected = _filterStatus == value;
    return Padding(
      padding: const EdgeInsets.only(right: 6),
      child: FilterChip(
        label: Text(label,
            style: TextStyle(
                fontSize: 11,
                color: isSelected ? Colors.white : Colors.grey.shade700)),
        selected: isSelected,
        selectedColor: AppTheme.primaryColor,
        backgroundColor: Colors.white,
        side: BorderSide(
            color: isSelected
                ? AppTheme.primaryColor
                : Colors.grey.withValues(alpha: 0.3)),
        onSelected: (_) {
          setState(() => _filterStatus = value);
          _currentPage = 1;
          _loadLeads();
        },
        visualDensity: VisualDensity.compact,
        padding: const EdgeInsets.symmetric(horizontal: 4),
      ),
    );
  }

  Widget _buildEmptyLeadsState() {
    return RefreshIndicator(
      onRefresh: () async {
        _currentPage = 1;
        await _loadLeads();
      },
      child: ListView(
        children: [
          SizedBox(height: MediaQuery.of(context).size.height * 0.1),
          Container(
            width: 80,
            height: 80,
            decoration: BoxDecoration(
              color: AppTheme.primaryColor.withValues(alpha: 0.08),
              shape: BoxShape.circle,
            ),
            child: Icon(Icons.people_outline,
                size: 40, color: AppTheme.primaryColor.withValues(alpha: 0.4)),
          ),
          const SizedBox(height: 16),
          const Center(
            child: Text('No leads assigned',
                style: TextStyle(color: Colors.grey, fontSize: 15)),
          ),
          const SizedBox(height: 8),
          const Center(
            child: Text('Leads assigned to you will appear here',
                style: TextStyle(color: Colors.grey, fontSize: 12)),
          ),
        ],
      ),
    );
  }

  Widget _buildLeadCard(CRMLead lead) {
    return GestureDetector(
      onTap: () => _showLeadDetail(lead),
      child: Container(
        padding: const EdgeInsets.all(14),
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
            // Top row: avatar, name, score
            Row(
              children: [
                CircleAvatar(
                  radius: 20,
                  backgroundColor:
                      lead.statusColor.withValues(alpha: 0.15),
                  child: Text(
                    lead.name.isNotEmpty
                        ? lead.name[0].toUpperCase()
                        : '?',
                    style: TextStyle(
                        color: lead.statusColor,
                        fontWeight: FontWeight.bold,
                        fontSize: 15),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(lead.name,
                          style: const TextStyle(
                              fontWeight: FontWeight.w600, fontSize: 14),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis),
                      const SizedBox(height: 2),
                      Row(
                        children: [
                          if (lead.phone != null) ...[
                            Icon(Icons.phone,
                                size: 12, color: Colors.grey.shade400),
                            const SizedBox(width: 4),
                            Text(lead.phone!,
                                style: TextStyle(
                                    fontSize: 12,
                                    color: Colors.grey.shade500)),
                          ],
                        ],
                      ),
                    ],
                  ),
                ),
                _buildScoreBadge(lead.leadScore ?? 0),
              ],
            ),

            const SizedBox(height: 10),

            // Tags row
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

            const SizedBox(height: 10),

            // Bottom row: status badge, assigned agent, actions
            Row(
              children: [
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: lead.statusColor.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(_statusLabel(lead.status),
                      style: TextStyle(
                          fontSize: 10,
                          color: lead.statusColor,
                          fontWeight: FontWeight.w600)),
                ),
                const SizedBox(width: 8),
                if (lead.assignedToName != null) ...[
                  Icon(Icons.person_outline,
                      size: 12, color: Colors.grey.shade400),
                  const SizedBox(width: 3),
                  Text(lead.assignedToName!,
                      style: TextStyle(
                          fontSize: 11, color: Colors.grey.shade500)),
                ],
                const Spacer(),
                // Quick action buttons
                _quickAction(Icons.call, Colors.green, () {
                  if (lead.phone != null) {
                    launchUrl(Uri(scheme: 'tel', path: lead.phone));
                  }
                }),
                const SizedBox(width: 6),
                _quickAction(Icons.message, Colors.blue, () {
                  if (lead.phone != null) {
                    launchUrl(Uri(scheme: 'sms', path: lead.phone));
                  }
                }),
                const SizedBox(width: 6),
                _quickAction(Icons.chat, Colors.teal, () {
                  if (lead.phone != null) {
                    final cleanPhone =
                        lead.phone!.replaceAll(RegExp(r'[^0-9]'), '');
                    final phone = cleanPhone.startsWith('91')
                        ? cleanPhone
                        : '91$cleanPhone';
                    launchUrl(Uri.parse(
                        'https://wa.me/$phone'));
                  }
                }),
                const SizedBox(width: 6),
                _quickAction(Icons.note_add, Colors.orange, () {
                  _showAddNoteSheet(lead);
                }),
              ],
            ),

            // Next follow-up
            if (lead.nextFollowupDate != null) ...[
              const SizedBox(height: 8),
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: lead.nextFollowupDate!.isBefore(DateTime.now())
                      ? Colors.red.withValues(alpha: 0.08)
                      : Colors.amber.withValues(alpha: 0.08),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.schedule,
                        size: 12,
                        color:
                            lead.nextFollowupDate!.isBefore(DateTime.now())
                                ? Colors.red
                                : Colors.amber),
                    const SizedBox(width: 4),
                    Text(
                      'Follow-up: ${_formatDate(lead.nextFollowupDate!)}',
                      style: TextStyle(
                        fontSize: 11,
                        color:
                            lead.nextFollowupDate!.isBefore(DateTime.now())
                                ? Colors.red
                                : const Color(0xFFF57F17),
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _quickAction(IconData icon, Color color, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: 30,
        height: 30,
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Icon(icon, color: color, size: 16),
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
      width: 34,
      height: 34,
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        shape: BoxShape.circle,
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Center(
        child: Text('$score',
            style: TextStyle(
                fontSize: 11, fontWeight: FontWeight.bold, color: color)),
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
            SizedBox(height: MediaQuery.of(context).size.height * 0.1),
            Container(
              width: 80,
              height: 80,
              decoration: BoxDecoration(
                color: AppTheme.primaryColor.withValues(alpha: 0.08),
                shape: BoxShape.circle,
              ),
              child: Icon(Icons.task_alt,
                  size: 40,
                  color: AppTheme.primaryColor.withValues(alpha: 0.4)),
            ),
            const SizedBox(height: 16),
            const Center(
              child: Text('No tasks assigned',
                  style: TextStyle(color: Colors.grey, fontSize: 15)),
            ),
            const SizedBox(height: 8),
            const Center(
              child: Text('Create follow-ups from lead details',
                  style: TextStyle(color: Colors.grey, fontSize: 12)),
            ),
          ],
        ),
      );
    }

    final overdueTasks =
        _tasks.where((t) => t.isOverdue && t.status != 'completed').toList();
    final todayTasks = _tasks.where((t) {
      if (t.dueDate == null || t.status == 'completed') return false;
      final now = DateTime.now();
      return t.dueDate!.year == now.year &&
          t.dueDate!.month == now.month &&
          t.dueDate!.day == now.day;
    }).toList();
    final upcomingTasks = _tasks.where((t) {
      if (t.status == 'completed') return false;
      if (t.dueDate == null) return true;
      final now = DateTime.now();
      final today = DateTime(now.year, now.month, now.day);
      return t.dueDate!.isAfter(today);
    }).toList();

    return RefreshIndicator(
      onRefresh: _loadData,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          if (overdueTasks.isNotEmpty) ...[
            _buildTaskGroupHeader(
                'Overdue', overdueTasks.length, Colors.red, Icons.warning),
            const SizedBox(height: 8),
            ...overdueTasks.map((t) => _buildTaskCard(t)),
            const SizedBox(height: 16),
          ],
          if (todayTasks.isNotEmpty) ...[
            _buildTaskGroupHeader(
                'Today', todayTasks.length, Colors.amber, Icons.today),
            const SizedBox(height: 8),
            ...todayTasks.map((t) => _buildTaskCard(t)),
            const SizedBox(height: 16),
          ],
          if (upcomingTasks.isNotEmpty) ...[
            _buildTaskGroupHeader('Upcoming', upcomingTasks.length,
                Colors.blue, Icons.upcoming),
            const SizedBox(height: 8),
            ...upcomingTasks.map((t) => _buildTaskCard(t)),
          ],
          if (overdueTasks.isEmpty &&
              todayTasks.isEmpty &&
              upcomingTasks.isEmpty)
            const Padding(
              padding: EdgeInsets.only(top: 40),
              child: Center(
                child: Text('All tasks completed!',
                    style: TextStyle(color: Colors.grey, fontSize: 15)),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildTaskGroupHeader(
      String label, int count, Color color, IconData icon) {
    return Row(
      children: [
        Container(
          width: 4,
          height: 16,
          decoration: BoxDecoration(
              color: color, borderRadius: BorderRadius.circular(2)),
        ),
        const SizedBox(width: 8),
        Icon(icon, color: color, size: 16),
        const SizedBox(width: 6),
        Text(label,
            style: TextStyle(
                fontWeight: FontWeight.w700,
                fontSize: 14,
                color: Color.lerp(color, Colors.black, 0.3)!)),
        const SizedBox(width: 6),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
          decoration: BoxDecoration(
            color: color.withValues(alpha: 0.12),
            borderRadius: BorderRadius.circular(10),
          ),
          child: Text('$count',
              style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                  color: color)),
        ),
      ],
    );
  }

  Widget _buildTaskCard(CRMTask task) {
    final isOverdue = task.isOverdue;
    final borderColor =
        isOverdue ? Colors.red.withValues(alpha: 0.3) : Colors.grey.withValues(alpha: 0.12);

    return Dismissible(
      key: ValueKey('task_${task.id}'),
      direction: DismissDirection.startToEnd,
      background: Container(
        alignment: Alignment.centerLeft,
        padding: const EdgeInsets.only(left: 20),
        margin: const EdgeInsets.only(bottom: 10),
        decoration: BoxDecoration(
          color: AppTheme.successColor,
          borderRadius: BorderRadius.circular(12),
        ),
        child: const Row(
          children: [
            Icon(Icons.check_circle, color: Colors.white),
            SizedBox(width: 8),
            Text('Mark Complete',
                style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w600)),
          ],
        ),
      ),
      onDismissed: (_) => _completeTask(task.id),
      child: GestureDetector(
        onTap: () => _showTaskDetail(task),
        child: Container(
          padding: const EdgeInsets.all(14),
          margin: const EdgeInsets.only(bottom: 10),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: borderColor),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.03),
                blurRadius: 4,
                offset: const Offset(0, 1),
              ),
            ],
          ),
          child: Row(
            children: [
              GestureDetector(
                onTap: () => _completeTask(task.id),
                child: Container(
                  width: 28,
                  height: 28,
                  decoration: BoxDecoration(
                    color: task.status == 'completed'
                        ? AppTheme.successColor.withValues(alpha: 0.12)
                        : Colors.grey.withValues(alpha: 0.1),
                    shape: BoxShape.circle,
                    border: Border.all(
                        color: task.status == 'completed'
                            ? AppTheme.successColor
                            : Colors.grey.withValues(alpha: 0.3)),
                  ),
                  child: Icon(
                    task.status == 'completed'
                        ? Icons.check
                        : Icons.radio_button_unchecked,
                    size: 16,
                    color: task.status == 'completed'
                        ? AppTheme.successColor
                        : Colors.grey,
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(task.title,
                        style: TextStyle(
                          fontWeight: FontWeight.w600,
                          fontSize: 13,
                          decoration: task.status == 'completed'
                              ? TextDecoration.lineThrough
                              : null,
                          color: task.status == 'completed'
                              ? Colors.grey
                              : Colors.black87,
                        )),
                    if (task.description != null &&
                        task.description!.isNotEmpty) ...[
                      const SizedBox(height: 3),
                      Text(task.description!,
                          style:
                              TextStyle(fontSize: 11, color: Colors.grey.shade500),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis),
                    ],
                    const SizedBox(height: 6),
                    Wrap(
                      spacing: 6,
                      runSpacing: 4,
                      children: [
                        _tag(task.taskType, Colors.blue),
                        _tag(task.priority,
                            task.priority == 'high'
                                ? Colors.red
                                : task.priority == 'medium'
                                    ? Colors.amber
                                    : Colors.green),
                        if (isOverdue) _tag('OVERDUE', Colors.red),
                      ],
                    ),
                    if (task.dueDate != null) ...[
                      const SizedBox(height: 6),
                      Row(
                        children: [
                          Icon(Icons.schedule,
                              size: 12,
                              color: isOverdue ? Colors.red : Colors.grey),
                          const SizedBox(width: 4),
                          Text(
                            '${_formatDate(task.dueDate!)}${task.dueTime != null ? ' ${task.dueTime}' : ''}',
                            style: TextStyle(
                                fontSize: 11,
                                color: isOverdue
                                    ? Colors.red
                                    : Colors.grey.shade500),
                          ),
                        ],
                      ),
                    ],
                  ],
                ),
              ),
              Icon(Icons.chevron_right,
                  color: Colors.grey.shade300, size: 20),
            ],
          ),
        ),
      ),
    );
  }

  // ─── Shimmer Loading Skeleton ─────────────────────────────────────

  Widget _buildShimmerSkeleton() {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Stat cards skeleton
        Row(
          children: List.generate(
            3,
            (_) => Expanded(
              child: Container(
                height: 80,
                margin: const EdgeInsets.symmetric(horizontal: 4),
                decoration: BoxDecoration(
                  color: Colors.grey.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
          ),
        ),
        const SizedBox(height: 16),
        // Tab skeleton
        Container(
          height: 40,
          decoration: BoxDecoration(
            color: Colors.grey.withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(12),
          ),
        ),
        const SizedBox(height: 16),
        // Lead cards skeleton
        ...List.generate(
          4,
          (_) => Container(
            height: 140,
            margin: const EdgeInsets.only(bottom: 10),
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: Colors.grey.withValues(alpha: 0.08),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      width: 40,
                      height: 40,
                      decoration: BoxDecoration(
                        color: Colors.grey.withValues(alpha: 0.15),
                        shape: BoxShape.circle,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            width: 120,
                            height: 12,
                            color: Colors.grey.withValues(alpha: 0.2),
                          ),
                          const SizedBox(height: 6),
                          Container(
                            width: 80,
                            height: 10,
                            color: Colors.grey.withValues(alpha: 0.15),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Row(
                  children: List.generate(
                    3,
                    (_) => Container(
                      width: 60,
                      height: 20,
                      margin: const EdgeInsets.only(right: 6),
                      decoration: BoxDecoration(
                        color: Colors.grey.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(4),
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Container(
                      width: 60,
                      height: 18,
                      decoration: BoxDecoration(
                        color: Colors.grey.withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(4),
                      ),
                    ),
                    const Spacer(),
                    ...List.generate(
                      3,
                      (_) => Container(
                        width: 30,
                        height: 30,
                        margin: const EdgeInsets.only(left: 6),
                        decoration: BoxDecoration(
                          color: Colors.grey.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(8),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }

  // ─── Dialogs ──────────────────────────────────────────────────────

  void _showLeadDetail(CRMLead lead) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) => DraggableScrollableSheet(
        initialChildSize: 0.7,
        maxChildSize: 0.95,
        minChildSize: 0.4,
        expand: false,
        builder: (ctx, scrollCtrl) =>
            _LeadDetailSheet(lead: lead, crm: _crm, onRefresh: _loadData),
      ),
    );
  }

  void _showAddNoteSheet(CRMLead lead) {
    final bodyCtrl = TextEditingController();
    final subjectCtrl = TextEditingController();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) => Padding(
        padding: EdgeInsets.only(bottom: MediaQuery.of(ctx).viewInsets.bottom),
        child: Container(
          padding: const EdgeInsets.all(20),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Add Note',
                    style:
                        TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                const SizedBox(height: 4),
                Text('Lead: ${lead.name}',
                    style: TextStyle(
                        fontSize: 13, color: Colors.grey.shade500)),
                const SizedBox(height: 16),
                TextField(
                  controller: subjectCtrl,
                  decoration: const InputDecoration(
                      labelText: 'Subject', border: OutlineInputBorder()),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: bodyCtrl,
                  decoration: const InputDecoration(
                      labelText: 'Notes', border: OutlineInputBorder()),
                  maxLines: 3,
                ),
                const SizedBox(height: 16),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () async {
                      if (bodyCtrl.text.isEmpty && subjectCtrl.text.isEmpty) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                              content: Text('Please enter a note')),
                        );
                        return;
                      }
                      await _crm.addInteraction(lead.id, {
                        'type': 'note',
                        'direction': 'outbound',
                        'subject': subjectCtrl.text.isNotEmpty
                            ? subjectCtrl.text
                            : null,
                        'body': bodyCtrl.text.isNotEmpty
                            ? bodyCtrl.text
                            : null,
                      });
                      if (ctx.mounted) Navigator.pop(ctx);
                      _loadData();
                      if (mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            content: Text('Note added'),
                            backgroundColor: AppTheme.successColor,
                          ),
                        );
                      }
                    },
                    style: ElevatedButton.styleFrom(
                        backgroundColor: AppTheme.primaryColor),
                    child: const Text('Save Note'),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  void _showTaskDetail(CRMTask task) {
    final notesCtrl = TextEditingController();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) => Padding(
        padding: EdgeInsets.only(bottom: MediaQuery.of(ctx).viewInsets.bottom),
        child: Container(
          padding: const EdgeInsets.all(20),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Handle bar
                Center(
                  child: Container(
                    width: 40,
                    height: 4,
                    decoration: BoxDecoration(
                      color: Colors.grey.withValues(alpha: 0.3),
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                Text(task.title,
                    style: const TextStyle(
                        fontSize: 17, fontWeight: FontWeight.bold)),
                const SizedBox(height: 12),

                // Task info
                _taskInfoRow(Icons.category, 'Type', task.taskType),
                _taskInfoRow(Icons.flag, 'Priority', task.priority),
                if (task.dueDate != null)
                  _taskInfoRow(Icons.schedule, 'Due',
                      '${_formatDate(task.dueDate!)}${task.dueTime != null ? ' ${task.dueTime}' : ''}'),
                if (task.description != null && task.description!.isNotEmpty)
                  Padding(
                    padding: const EdgeInsets.only(top: 8),
                    child: _taskInfoRow(
                        Icons.description, 'Details', task.description!),
                  ),

                const SizedBox(height: 16),

                // Completion notes
                if (task.status != 'completed') ...[
                  const Divider(),
                  const SizedBox(height: 8),
                  const Text('Completion Notes',
                      style:
                          TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
                  const SizedBox(height: 8),
                  TextField(
                    controller: notesCtrl,
                    decoration: const InputDecoration(
                        hintText: 'Add notes (optional)',
                        border: OutlineInputBorder()),
                    maxLines: 2,
                  ),
                  const SizedBox(height: 12),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      onPressed: () async {
                        await _completeTask(task.id);
                        if (ctx.mounted) Navigator.pop(ctx);
                      },
                      icon: const Icon(Icons.check_circle, size: 18),
                      label: const Text('Mark as Complete'),
                      style: ElevatedButton.styleFrom(
                          backgroundColor: AppTheme.successColor),
                    ),
                  ),
                ] else ...[
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: AppTheme.successColor.withValues(alpha: 0.08),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Row(
                      children: [
                        Icon(Icons.check_circle,
                            color: AppTheme.successColor, size: 20),
                        SizedBox(width: 8),
                        Text('Task completed',
                            style: TextStyle(
                                color: AppTheme.successColor,
                                fontWeight: FontWeight.w600)),
                      ],
                    ),
                  ),
                ],
                const SizedBox(height: 16),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _taskInfoRow(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 16, color: Colors.grey.shade500),
          const SizedBox(width: 8),
          Text('$label: ',
              style: TextStyle(
                  fontSize: 13,
                  color: Colors.grey.shade500,
                  fontWeight: FontWeight.w500)),
          Expanded(
            child: Text(value,
                style: const TextStyle(fontSize: 13, color: Colors.black87)),
          ),
        ],
      ),
    );
  }

  // ─── Helpers ──────────────────────────────────────────────────────

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

  String _statusLabel(String? status) {
    return (status ?? 'unknown').replaceAll('_', ' ').toUpperCase();
  }

  String _formatDate(DateTime dt) {
    final now = DateTime.now();
    final diff = DateTime(dt.year, dt.month, dt.day)
        .difference(DateTime(now.year, now.month, now.day))
        .inDays;
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

  const _LeadDetailSheet(
      {required this.lead, required this.crm, required this.onRefresh});

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
    setState(() {
      _interactions = interactions;
      _loadingInteractions = false;
    });
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
                backgroundColor:
                    lead.statusColor.withValues(alpha: 0.15),
                child: Text(
                  lead.name.isNotEmpty
                      ? lead.name[0].toUpperCase()
                      : '?',
                  style: TextStyle(
                      color: lead.statusColor,
                      fontWeight: FontWeight.bold,
                      fontSize: 18),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(lead.name,
                        style: const TextStyle(
                            fontSize: 16, fontWeight: FontWeight.bold)),
                    Text(lead.phone ?? '',
                        style: const TextStyle(color: Colors.grey)),
                    if (lead.email != null)
                      Text(lead.email!,
                          style: const TextStyle(
                              color: Colors.grey, fontSize: 12)),
                  ],
                ),
              ),
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: lead.statusColor.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(_statusLabel(lead.status),
                    style: TextStyle(
                        color: lead.statusColor,
                        fontSize: 11,
                        fontWeight: FontWeight.w600)),
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
              _actionBtn(Icons.call, 'Call', Colors.green, () {
                if (lead.phone != null) {
                  launchUrl(Uri(scheme: 'tel', path: lead.phone));
                }
              }),
              _actionBtn(Icons.sms, 'SMS', Colors.blue, () {
                if (lead.phone != null) {
                  launchUrl(Uri(scheme: 'sms', path: lead.phone));
                }
              }),
              _actionBtn(Icons.chat, 'WhatsApp', Colors.teal, () {
                if (lead.phone != null) {
                  final cleanPhone =
                      lead.phone!.replaceAll(RegExp(r'[^0-9]'), '');
                  final phone = cleanPhone.startsWith('91')
                      ? cleanPhone
                      : '91$cleanPhone';
                  launchUrl(Uri.parse('https://wa.me/$phone'));
                }
              }),
              _actionBtn(Icons.note_add, 'Note', Colors.orange, () {
                Navigator.pop(context);
                _showAddNoteFromDetail(lead);
              }),
            ],
          ),

          const SizedBox(height: 16),

          // Interactions timeline
          const Align(
            alignment: Alignment.centerLeft,
            child: Text('Timeline',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
          ),
          const SizedBox(height: 8),

          Expanded(
            child: _loadingInteractions
                ? const Center(child: CircularProgressIndicator())
                : _interactions.isEmpty
                    ? const Center(
                        child: Text('No interactions yet',
                            style: TextStyle(color: Colors.grey)))
                    : ListView.separated(
                        itemCount: _interactions.length,
                        separatorBuilder: (_, _) =>
                            const SizedBox(height: 8),
                        itemBuilder: (ctx, i) =>
                            _buildInteractionItem(_interactions[i]),
                      ),
          ),
        ],
      ),
    );
  }

  void _showAddNoteFromDetail(CRMLead lead) {
    final bodyCtrl = TextEditingController();
    final subjectCtrl = TextEditingController();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) => Padding(
        padding: EdgeInsets.only(bottom: MediaQuery.of(ctx).viewInsets.bottom),
        child: Container(
          padding: const EdgeInsets.all(20),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Add Note',
                    style:
                        TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                const SizedBox(height: 16),
                TextField(
                  controller: subjectCtrl,
                  decoration: const InputDecoration(
                      labelText: 'Subject', border: OutlineInputBorder()),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: bodyCtrl,
                  decoration: const InputDecoration(
                      labelText: 'Notes', border: OutlineInputBorder()),
                  maxLines: 3,
                ),
                const SizedBox(height: 16),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () async {
                      await widget.crm.addInteraction(lead.id, {
                        'type': 'note',
                        'direction': 'outbound',
                        'subject': subjectCtrl.text.isNotEmpty
                            ? subjectCtrl.text
                            : null,
                        'body': bodyCtrl.text.isNotEmpty
                            ? bodyCtrl.text
                            : null,
                      });
                      if (ctx.mounted) Navigator.pop(ctx);
                      widget.onRefresh();
                      _loadInteractions();
                      if (mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            content: Text('Note added'),
                            backgroundColor: AppTheme.successColor,
                          ),
                        );
                      }
                    },
                    style: ElevatedButton.styleFrom(
                        backgroundColor: AppTheme.primaryColor),
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

  Widget _actionBtn(
      IconData icon, String label, Color color, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Column(
        children: [
          Container(
            width: 42,
            height: 42,
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
        Column(
          children: [
            Container(
              width: 32,
              height: 32,
              decoration: BoxDecoration(
                color: Colors.blue.withValues(alpha: 0.12),
                shape: BoxShape.circle,
              ),
              child: Icon(interaction.icon, size: 16, color: Colors.blue),
            ),
          ],
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
                        style: const TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                            color: Colors.blue)),
                    const Spacer(),
                    Text(_formatTime(interaction.createdAt),
                        style:
                            const TextStyle(fontSize: 10, color: Colors.grey)),
                  ],
                ),
                if (interaction.subject != null) ...[
                  const SizedBox(height: 4),
                  Text(interaction.subject!,
                      style: const TextStyle(
                          fontSize: 12, fontWeight: FontWeight.w500)),
                ],
                if (interaction.body != null) ...[
                  const SizedBox(height: 2),
                  Text(interaction.body!,
                      style:
                          const TextStyle(fontSize: 12, color: Colors.grey)),
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

  String _formatTime(DateTime dt) {
    final now = DateTime.now();
    final diff = now.difference(dt);
    if (diff.inMinutes < 1) return 'Now';
    if (diff.inHours < 1) return '${diff.inMinutes}m ago';
    if (diff.inDays < 1) return '${diff.inHours}h ago';
    return '${diff.inDays}d ago';
  }

  String _statusLabel(String? status) =>
      (status ?? 'unknown').replaceAll('_', ' ').toUpperCase();
}

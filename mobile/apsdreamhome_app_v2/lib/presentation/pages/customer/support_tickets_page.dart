import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../data/repositories/kyc_repository_provider.dart';

enum TicketStatus { all, open, inProgress, resolved, closed }

class SupportTicketsPage extends ConsumerStatefulWidget {
  const SupportTicketsPage({super.key});

  @override
  ConsumerState<SupportTicketsPage> createState() => _SupportTicketsPageState();
}

class _SupportTicketsPageState extends ConsumerState<SupportTicketsPage> {
  bool _isLoading = true;
  String? _error;
  List<Map<String, dynamic>> _tickets = [];
  List<Map<String, dynamic>> _selectedTicketReplies = [];
  TicketStatus _statusFilter = TicketStatus.all;
  bool _isCreating = false;
  bool _isDetailLoading = false;

  final _formKey = GlobalKey<FormState>();
  final _subjectController = TextEditingController();
  final _messageController = TextEditingController();
  String _selectedCategory = 'general';
  String _selectedPriority = 'medium';

  static const List<Map<String, String>> _categories = [
    {'value': 'general', 'label': 'General Inquiry'},
    {'value': 'booking', 'label': 'Booking Issue'},
    {'value': 'payment', 'label': 'Payment Problem'},
    {'value': 'property', 'label': 'Property Related'},
    {'value': 'technical', 'label': 'Technical Issue'},
    {'value': 'feedback', 'label': 'Feedback'},
    {'value': 'other', 'label': 'Other'},
  ];

  static const List<Map<String, String>> _priorities = [
    {'value': 'low', 'label': 'Low'},
    {'value': 'medium', 'label': 'Medium'},
    {'value': 'high', 'label': 'High'},
    {'value': 'urgent', 'label': 'Urgent'},
  ];

  @override
  void initState() {
    super.initState();
    _fetchTickets();
  }

  @override
  void dispose() {
    _subjectController.dispose();
    _messageController.dispose();
    super.dispose();
  }

  Future<void> _fetchTickets() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final api = ref.read(apiServiceProvider);
      final response = await api.get('/user/support/tickets');
      final data = response['data'];
      if (!mounted) return;
      setState(() {
        if (data is List) {
          _tickets =
              data.map((e) => Map<String, dynamic>.from(e as Map)).toList();
        } else if (data is Map) {
          _tickets = (data['tickets'] as List<dynamic>? ?? [])
              .map((e) => Map<String, dynamic>.from(e as Map))
              .toList();
        }
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  List<Map<String, dynamic>> get _filteredTickets {
    if (_statusFilter == TicketStatus.all) return _tickets;
    final statusStr = _statusFilter.name;
    return _tickets
        .where((t) => t['status']?.toString().toLowerCase() == statusStr)
        .toList();
  }

  int get _openCount =>
      _tickets.where((t) => t['status']?.toString() == 'open').length;
  int get _inProgressCount =>
      _tickets.where((t) => t['status']?.toString() == 'in_progress').length;

  Future<void> _createTicket() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() {
      _isCreating = true;
    });
    try {
      final api = ref.read(apiServiceProvider);
      await api.post('/user/support/tickets', data: {
        'subject': _subjectController.text.trim(),
        'message': _messageController.text.trim(),
        'category': _selectedCategory,
        'priority': _selectedPriority,
      });
      _subjectController.clear();
      _messageController.clear();
      setState(() {
        _selectedCategory = 'general';
        _selectedPriority = 'medium';
      });
      await _fetchTickets();
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Support ticket created successfully'),
          backgroundColor: AppTheme.successColor,
        ),
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Failed to create ticket: $e'),
          backgroundColor: AppTheme.errorColor,
        ),
      );
    } finally {
      if (mounted) {
        setState(() {
          _isCreating = false;
        });
      }
    }
  }

  Future<void> _fetchTicketDetail(int ticketId) async {
    setState(() {
      _isDetailLoading = true;
      _selectedTicketReplies = [];
    });
    try {
      final api = ref.read(apiServiceProvider);
      final response = await api.get('/user/support/tickets/$ticketId');
      final data = response['data'] as Map<String, dynamic>? ?? {};
      if (!mounted) return;
      setState(() {
        _selectedTicketReplies =
            (data['replies'] as List<dynamic>? ?? [])
                .map((e) => Map<String, dynamic>.from(e as Map))
                .toList();
        _isDetailLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isDetailLoading = false;
      });
    }
  }

  void _showCreateTicketSheet() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => _buildCreateTicketSheet(ctx),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.surfaceColor,
      appBar: AppBar(
        title: const Text('Support Tickets'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new, size: 20),
          onPressed: () => context.pop(),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, size: 22),
            onPressed: _fetchTickets,
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: _showCreateTicketSheet,
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        child: const Icon(Icons.add_rounded, size: 28),
      ),
      body: Column(
        children: [
          _buildStatsBar(),
          _buildFilterBar(),
          Expanded(
            child: _isLoading
                ? const Center(
                    child: CircularProgressIndicator(
                        color: AppTheme.primaryColor),
                  )
                : _error != null
                    ? _buildErrorState()
                    : _filteredTickets.isEmpty
                        ? _buildEmptyState()
                        : RefreshIndicator(
                            onRefresh: _fetchTickets,
                            color: AppTheme.primaryColor,
                            child: ListView.builder(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 16, vertical: 8),
                              itemCount: _filteredTickets.length,
                              itemBuilder: (context, index) {
                                return _buildTicketCard(
                                    _filteredTickets[index]);
                              },
                            ),
                          ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatsBar() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      color: Colors.white,
      child: Row(
        children: [
          _buildMiniStat(
            label: 'Total',
            count: _tickets.length,
            color: AppTheme.infoColor,
          ),
          const SizedBox(width: 12),
          _buildMiniStat(
            label: 'Open',
            count: _openCount,
            color: AppTheme.successColor,
          ),
          const SizedBox(width: 12),
          _buildMiniStat(
            label: 'In Progress',
            count: _inProgressCount,
            color: AppTheme.warningColor,
          ),
        ],
      ),
    );
  }

  Widget _buildMiniStat({
    required String label,
    required int count,
    required Color color,
  }) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.06),
          borderRadius: BorderRadius.circular(10),
        ),
        child: Column(
          children: [
            Text(
              count.toString(),
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: color,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: TextStyle(
                fontSize: 11,
                color: Colors.grey.shade600,
                fontWeight: FontWeight.w500,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFilterBar() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
      color: Colors.white,
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: Row(
          children: TicketStatus.values.map((status) {
            final isActive = _statusFilter == status;
            final label = status == TicketStatus.all
                ? 'All'
                : status.name[0].toUpperCase() + status.name.substring(1);
            return Padding(
              padding: const EdgeInsets.only(right: 8),
              child: GestureDetector(
                onTap: () {
                  setState(() {
                    _statusFilter = status;
                  });
                },
                child: Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
                  decoration: BoxDecoration(
                    color: isActive
                        ? AppTheme.primaryColor.withValues(alpha: 0.1)
                        : AppTheme.surfaceColor,
                    borderRadius: BorderRadius.circular(18),
                    border: Border.all(
                      color: isActive
                          ? AppTheme.primaryColor.withValues(alpha: 0.3)
                          : Colors.grey.shade200,
                    ),
                  ),
                  child: Text(
                    label,
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w500,
                      color: isActive
                          ? AppTheme.primaryColor
                          : Colors.grey.shade600,
                    ),
                  ),
                ),
              ),
            );
          }).toList(),
        ),
      ),
    );
  }

  Widget _buildTicketCard(Map<String, dynamic> ticket) {
    final id = ticket['id'] as int? ?? 0;
    final subject = ticket['subject']?.toString() ?? '';
    final message = ticket['message']?.toString() ??
        ticket['description']?.toString() ??
        '';
    final status = ticket['status']?.toString() ?? 'open';
    final priority = ticket['priority']?.toString() ?? 'medium';
    final createdAt = ticket['created_at']?.toString() ?? '';
    final replyCount = ticket['reply_count'] as int? ??
        (ticket['replies'] as List<dynamic>?)?.length ??
        0;

    final statusColor = _getStatusColor(status);
    final priorityColor = _getPriorityColor(priority);

    return GestureDetector(
      onTap: () {
        _showTicketDetail(ticket);
      },
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.03),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    subject.isNotEmpty ? subject : 'Ticket #$id',
                    style: const TextStyle(
                      fontWeight: FontWeight.w600,
                      fontSize: 15,
                      color: AppTheme.textPrimaryLight,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: statusColor.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    _formatStatus(status),
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                      color: statusColor,
                    ),
                  ),
                ),
              ],
            ),
            if (message.isNotEmpty) ...[
              const SizedBox(height: 8),
              Text(
                message,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  fontSize: 13,
                  color: Colors.grey.shade600,
                  height: 1.4,
                ),
              ),
            ],
            const SizedBox(height: 10),
            Row(
              children: [
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: priorityColor.withValues(alpha: 0.08),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Container(
                        width: 6,
                        height: 6,
                        decoration: BoxDecoration(
                          color: priorityColor,
                          shape: BoxShape.circle,
                        ),
                      ),
                      const SizedBox(width: 4),
                      Text(
                        priority[0].toUpperCase() + priority.substring(1),
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.w600,
                          color: priorityColor,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                Icon(Icons.access_time_rounded,
                    size: 12, color: Colors.grey.shade400),
                const SizedBox(width: 4),
                Text(
                  createdAt.isNotEmpty
                      ? _formatShortDate(createdAt)
                      : '',
                  style: TextStyle(
                    fontSize: 11,
                    color: Colors.grey.shade400,
                  ),
                ),
                const Spacer(),
                if (replyCount > 0)
                  Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.chat_bubble_outline_rounded,
                          size: 14, color: Colors.grey.shade400),
                      const SizedBox(width: 4),
                      Text(
                        replyCount.toString(),
                        style: TextStyle(
                          fontSize: 11,
                          color: Colors.grey.shade500,
                        ),
                      ),
                    ],
                  ),
                const SizedBox(width: 10),
                Icon(Icons.chevron_right_rounded,
                    size: 20, color: Colors.grey.shade400),
              ],
            ),
          ],
        ),
      ),
    );
  }

  void _showTicketDetail(Map<String, dynamic> ticket) {
    _fetchTicketDetail(ticket['id'] as int);
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => _buildTicketDetailSheet(ctx, ticket),
    );
  }

  Widget _buildTicketDetailSheet(
      BuildContext context, Map<String, dynamic> ticket) {
    final subject = ticket['subject']?.toString() ?? '';
    final message = ticket['message']?.toString() ??
        ticket['description']?.toString() ??
        '';
    final status = ticket['status']?.toString() ?? 'open';
    final category = ticket['category']?.toString() ?? 'general';
    final createdAt = ticket['created_at']?.toString() ?? '';
    final statusColor = _getStatusColor(status);

    return DraggableScrollableSheet(
      initialChildSize: 0.85,
      minChildSize: 0.5,
      maxChildSize: 0.95,
      builder: (ctx, scrollController) {
        return Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
          ),
          child: Column(
            children: [
              Container(
                margin: const EdgeInsets.only(top: 10),
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              Padding(
                padding: const EdgeInsets.all(16),
                child: Row(
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            subject.isNotEmpty ? subject : 'Ticket #${ticket['id']}',
                            style: const TextStyle(
                              fontWeight: FontWeight.w600,
                              fontSize: 17,
                              color: AppTheme.textPrimaryLight,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(
                                    horizontal: 8, vertical: 3),
                                decoration: BoxDecoration(
                                  color: statusColor.withValues(alpha: 0.1),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Text(
                                  _formatStatus(status),
                                  style: TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.w600,
                                    color: statusColor,
                                  ),
                                ),
                              ),
                              const SizedBox(width: 8),
                              Text(
                                _formatCategory(category),
                                style: TextStyle(
                                  fontSize: 12,
                                  color: Colors.grey.shade500,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    IconButton(
                      onPressed: () => Navigator.pop(ctx),
                      icon: const Icon(Icons.close_rounded, size: 24),
                    ),
                  ],
                ),
              ),
              Expanded(
                child: ListView(
                  controller: scrollController,
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  children: [
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: AppTheme.surfaceColor,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Your Message',
                            style: TextStyle(
                              fontWeight: FontWeight.w600,
                              fontSize: 13,
                              color: AppTheme.textPrimaryLight,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            message.isNotEmpty ? message : 'No message provided',
                            style: TextStyle(
                              fontSize: 14,
                              color: Colors.grey.shade700,
                              height: 1.5,
                            ),
                          ),
                          if (createdAt.isNotEmpty) ...[
                            const SizedBox(height: 10),
                            Text(
                              _formatDate(createdAt),
                              style: TextStyle(
                                fontSize: 11,
                                color: Colors.grey.shade400,
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),
                    if (_isDetailLoading)
                      const Padding(
                        padding: EdgeInsets.all(24),
                        child: Center(
                          child: CircularProgressIndicator(
                              color: AppTheme.primaryColor),
                        ),
                      )
                    else if (_selectedTicketReplies.isEmpty)
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(24),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: Colors.grey.shade100),
                        ),
                        child: Column(
                          children: [
                            Icon(Icons.chat_bubble_outline_rounded,
                                size: 36, color: Colors.grey.shade300),
                            const SizedBox(height: 12),
                            Text(
                              'No replies yet',
                              style: TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.w500,
                                color: Colors.grey.shade500,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              'Our team will respond shortly',
                              style: TextStyle(
                                fontSize: 12,
                                color: Colors.grey.shade400,
                              ),
                            ),
                          ],
                        ),
                      )
                    else
                      ..._selectedTicketReplies.map(
                        (reply) => _buildReplyBubble(reply),
                      ),
                    const SizedBox(height: 24),
                  ],
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildReplyBubble(Map<String, dynamic> reply) {
    final isAdmin = reply['is_admin'] as bool? ?? false;
    final message = reply['message']?.toString() ?? '';
    final createdAt = reply['created_at']?.toString() ?? '';
    final userName = reply['user_name']?.toString() ??
        (isAdmin ? 'Support Team' : 'You');

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      alignment: isAdmin ? Alignment.centerLeft : Alignment.centerRight,
      child: Container(
        constraints: BoxConstraints(
          maxWidth: MediaQuery.of(context).size.width * 0.8,
        ),
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: isAdmin
              ? AppTheme.infoColor.withValues(alpha: 0.08)
              : AppTheme.primaryColor.withValues(alpha: 0.08),
          borderRadius: BorderRadius.only(
            topLeft: const Radius.circular(14),
            topRight: const Radius.circular(14),
            bottomLeft: Radius.circular(isAdmin ? 14 : 4),
            bottomRight: Radius.circular(isAdmin ? 4 : 14),
          ),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                CircleAvatar(
                  radius: 10,
                  backgroundColor: isAdmin
                      ? AppTheme.infoColor.withValues(alpha: 0.2)
                      : AppTheme.primaryColor.withValues(alpha: 0.2),
                  child: Icon(
                    isAdmin ? Icons.support_agent_rounded : Icons.person_rounded,
                    size: 12,
                    color: isAdmin ? AppTheme.infoColor : AppTheme.primaryColor,
                  ),
                ),
                const SizedBox(width: 6),
                Text(
                  userName,
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                    color: isAdmin ? AppTheme.infoColor : AppTheme.primaryColor,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              message,
              style: TextStyle(
                fontSize: 14,
                color: Colors.grey.shade800,
                height: 1.4,
              ),
            ),
            if (createdAt.isNotEmpty) ...[
              const SizedBox(height: 8),
              Text(
                _formatDate(createdAt),
                style: TextStyle(
                  fontSize: 10,
                  color: Colors.grey.shade400,
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildCreateTicketSheet(BuildContext context) {
    return StatefulBuilder(
      builder: (ctx, setSheetState) {
        return Container(
          height: MediaQuery.of(context).size.height * 0.85,
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
          ),
          child: Column(
            children: [
              Container(
                margin: const EdgeInsets.only(top: 10),
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              Padding(
                padding: const EdgeInsets.all(16),
                child: Row(
                  children: [
                    const Expanded(
                      child: Text(
                        'Create Support Ticket',
                        style: TextStyle(
                          fontWeight: FontWeight.w600,
                          fontSize: 17,
                          color: AppTheme.textPrimaryLight,
                        ),
                      ),
                    ),
                    IconButton(
                      onPressed: () => Navigator.pop(ctx),
                      icon: const Icon(Icons.close_rounded, size: 24),
                    ),
                  ],
                ),
              ),
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  child: Form(
                    key: _formKey,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _buildLabel('Category'),
                        const SizedBox(height: 8),
                        Container(
                          padding:
                              const EdgeInsets.symmetric(horizontal: 14),
                          decoration: BoxDecoration(
                            border: Border.all(color: Colors.grey.shade200),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: DropdownButtonHideUnderline(
                            child: DropdownButton<String>(
                              value: _selectedCategory,
                              isExpanded: true,
                              icon: Icon(Icons.keyboard_arrow_down_rounded,
                                  color: Colors.grey.shade500),
                              items: _categories
                                  .map((c) => DropdownMenuItem(
                                        value: c['value'],
                                        child: Text(
                                          c['label']!,
                                          style: const TextStyle(fontSize: 14),
                                        ),
                                      ))
                                  .toList(),
                              onChanged: (v) {
                                if (v != null) {
                                  setSheetState(() {
                                    _selectedCategory = v;
                                  });
                                }
                              },
                            ),
                          ),
                        ),
                        const SizedBox(height: 16),
                        _buildLabel('Priority'),
                        const SizedBox(height: 8),
                        Row(
                          children: _priorities.map((p) {
                            final isActive = _selectedPriority == p['value'];
                            final color = _getPriorityColor(p['value']!);
                            return Expanded(
                              child: GestureDetector(
                                onTap: () {
                                  setSheetState(() {
                                    _selectedPriority = p['value']!;
                                  });
                                },
                                child: Container(
                                  margin:
                                      const EdgeInsets.symmetric(horizontal: 4),
                                  padding:
                                      const EdgeInsets.symmetric(vertical: 10),
                                  decoration: BoxDecoration(
                                    color: isActive
                                        ? color.withValues(alpha: 0.1)
                                        : AppTheme.surfaceColor,
                                    borderRadius: BorderRadius.circular(10),
                                    border: Border.all(
                                      color: isActive
                                          ? color.withValues(alpha: 0.3)
                                          : Colors.grey.shade200,
                                    ),
                                  ),
                                  child: Text(
                                    p['label']!,
                                    textAlign: TextAlign.center,
                                    style: TextStyle(
                                      fontSize: 13,
                                      fontWeight: FontWeight.w500,
                                      color:
                                          isActive ? color : Colors.grey.shade600,
                                    ),
                                  ),
                                ),
                              ),
                            );
                          }).toList(),
                        ),
                        const SizedBox(height: 16),
                        _buildLabel('Subject'),
                        const SizedBox(height: 8),
                        TextFormField(
                          controller: _subjectController,
                          validator: (v) {
                            if (v == null || v.trim().isEmpty) {
                              return 'Please enter a subject';
                            }
                            return null;
                          },
                          decoration: InputDecoration(
                            hintText: 'Brief description of your issue',
                            hintStyle: TextStyle(
                              color: Colors.grey.shade400,
                              fontSize: 14,
                            ),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: BorderSide(color: Colors.grey.shade200),
                            ),
                            enabledBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: BorderSide(color: Colors.grey.shade200),
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: const BorderSide(
                                  color: AppTheme.primaryColor, width: 1.5),
                            ),
                            contentPadding: const EdgeInsets.all(14),
                          ),
                        ),
                        const SizedBox(height: 16),
                        _buildLabel('Message'),
                        const SizedBox(height: 8),
                        TextFormField(
                          controller: _messageController,
                          maxLines: 5,
                          validator: (v) {
                            if (v == null || v.trim().isEmpty) {
                              return 'Please enter your message';
                            }
                            return null;
                          },
                          decoration: InputDecoration(
                            hintText:
                                'Describe your issue in detail...',
                            hintStyle: TextStyle(
                              color: Colors.grey.shade400,
                              fontSize: 14,
                            ),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: BorderSide(color: Colors.grey.shade200),
                            ),
                            enabledBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: BorderSide(color: Colors.grey.shade200),
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: const BorderSide(
                                  color: AppTheme.primaryColor, width: 1.5),
                            ),
                            contentPadding: const EdgeInsets.all(14),
                          ),
                        ),
                        const SizedBox(height: 24),
                        SizedBox(
                          width: double.infinity,
                          child: ElevatedButton(
                            onPressed: _isCreating ? null : _createTicket,
                            style: ElevatedButton.styleFrom(
                              backgroundColor: AppTheme.primaryColor,
                              foregroundColor: Colors.white,
                              disabledBackgroundColor:
                                  AppTheme.primaryColor.withValues(alpha: 0.5),
                              padding:
                                  const EdgeInsets.symmetric(vertical: 14),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(12),
                              ),
                            ),
                            child: _isCreating
                                ? const SizedBox(
                                    width: 20,
                                    height: 20,
                                    child: CircularProgressIndicator(
                                      strokeWidth: 2,
                                      color: Colors.white,
                                    ),
                                  )
                                : const Text(
                                    'Submit Ticket',
                                    style: TextStyle(
                                      fontSize: 15,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                          ),
                        ),
                        const SizedBox(height: 32),
                      ],
                    ),
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildLabel(String text) {
    return Text(
      text,
      style: const TextStyle(
        fontWeight: FontWeight.w600,
        fontSize: 14,
        color: AppTheme.textPrimaryLight,
      ),
    );
  }

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
                color: AppTheme.primaryColor.withValues(alpha: 0.06),
                shape: BoxShape.circle,
              ),
              child: Icon(
                Icons.support_agent_rounded,
                size: 48,
                color: AppTheme.primaryColor.withValues(alpha: 0.3),
              ),
            ),
            const SizedBox(height: 24),
            const Text(
              'No support tickets',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w600,
                color: AppTheme.textPrimaryLight,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Tap + to create a new support ticket',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 14,
                color: Colors.grey.shade500,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildErrorState() {
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
                color: AppTheme.errorColor.withValues(alpha: 0.08),
                shape: BoxShape.circle,
              ),
              child: Icon(
                Icons.wifi_off_rounded,
                size: 48,
                color: AppTheme.errorColor.withValues(alpha: 0.5),
              ),
            ),
            const SizedBox(height: 24),
            const Text(
              'Something went wrong',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w600,
                color: AppTheme.textPrimaryLight,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Could not load tickets.\nPlease try again.',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 14,
                color: Colors.grey.shade500,
                height: 1.5,
              ),
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: _fetchTickets,
              icon: const Icon(Icons.refresh_rounded, size: 18),
              label: const Text('Retry'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primaryColor,
                foregroundColor: Colors.white,
                padding:
                    const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  static String _formatStatus(String status) {
    switch (status) {
      case 'open':
        return 'Open';
      case 'in_progress':
        return 'In Progress';
      case 'resolved':
        return 'Resolved';
      case 'closed':
        return 'Closed';
      default:
        return status[0].toUpperCase() + status.substring(1);
    }
  }

  static String _formatCategory(String category) {
    switch (category) {
      case 'general':
        return 'General';
      case 'booking':
        return 'Booking';
      case 'payment':
        return 'Payment';
      case 'property':
        return 'Property';
      case 'technical':
        return 'Technical';
      case 'feedback':
        return 'Feedback';
      case 'other':
        return 'Other';
      default:
        return category[0].toUpperCase() + category.substring(1);
    }
  }

  static Color _getStatusColor(String status) {
    switch (status) {
      case 'open':
        return AppTheme.successColor;
      case 'in_progress':
        return AppTheme.warningColor;
      case 'resolved':
        return AppTheme.infoColor;
      case 'closed':
        return Colors.grey;
      default:
        return Colors.grey;
    }
  }

  static Color _getPriorityColor(String priority) {
    switch (priority) {
      case 'urgent':
        return AppTheme.errorColor;
      case 'high':
        return const Color(0xFFFF9800);
      case 'medium':
        return AppTheme.warningColor;
      case 'low':
        return AppTheme.successColor;
      default:
        return Colors.grey;
    }
  }

  static String _formatDate(String dateStr) {
    if (dateStr.isEmpty) return '';
    try {
      final date = DateTime.parse(dateStr);
      final months = [
        '', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
      ];
      final hour = date.hour > 12 ? date.hour - 12 : date.hour;
      final amPm = date.hour >= 12 ? 'PM' : 'AM';
      final min = date.minute.toString().padLeft(2, '0');
      return '${months[date.month]} ${date.day}, ${date.year} at $hour:$min $amPm';
    } catch (_) {
      return dateStr;
    }
  }

  static String _formatShortDate(String dateStr) {
    if (dateStr.isEmpty) return '';
    try {
      final date = DateTime.parse(dateStr);
      final now = DateTime.now();
      final diff = now.difference(date);
      if (diff.inMinutes < 60) return '${diff.inMinutes}m ago';
      if (diff.inHours < 24) return '${diff.inHours}h ago';
      if (diff.inDays < 7) return '${diff.inDays}d ago';
      final months = [
        '', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
      ];
      return '${months[date.month]} ${date.day}';
    } catch (_) {
      return dateStr;
    }
  }
}

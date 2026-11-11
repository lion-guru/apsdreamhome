import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/services/ai_agent_service.dart';
import '../../../core/services/ai_employee_agent.dart';
import '../../../presentation/providers/ai_provider.dart';

/// Advanced AI Chat Page
/// Features: Multi-role AI, Employee Mode, Decision Making
class AdvancedAIChatPage extends ConsumerStatefulWidget {
  final AIAgentRole? initialRole;

  const AdvancedAIChatPage({
    super.key,
    this.initialRole,
  });

  @override
  ConsumerState<AdvancedAIChatPage> createState() => _AdvancedAIChatPageState();
}

class _AdvancedAIChatPageState extends ConsumerState<AdvancedAIChatPage> {
  final _messageController = TextEditingController();
  final _scrollController = ScrollController();
  AIAgentRole _currentRole = AIAgentRole.customerSupport;
  bool _showEmployeeMode = false;

  final List<AIAgentRole> _availableRoles = [
    AIAgentRole.customerSupport,
    AIAgentRole.salesAssistant,
    AIAgentRole.propertyExpert,
    AIAgentRole.investmentAdvisor,
    AIAgentRole.managerAssistant,
  ];

  final List<AIEmployeeTask> _quickTasks = [
    AIEmployeeTask.leadFollowUp,
    AIEmployeeTask.scoreLead,
    AIEmployeeTask.sendReminders,
    AIEmployeeTask.analyzePerformance,
  ];

  @override
  void initState() {
    super.initState();
    _currentRole = widget.initialRole ?? AIAgentRole.customerSupport;
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(aiProvider.notifier).switchRole(_currentRole);
    });
  }

  @override
  Widget build(BuildContext context) {
    final aiState = ref.watch(aiProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F0),
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'APS Real Estate AI',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
            ),
            Text(
              '${_getRoleDisplayName(_currentRole)} • Live RAG Intelligence',
              style: const TextStyle(
                fontSize: 11,
                color: Color(0xFFE5A93C),
                fontWeight: FontWeight.w500,
              ),
            ),
          ],
        ),
        elevation: 0,
        backgroundColor: const Color(0xFF0B194C),
        foregroundColor: Colors.white,
        actions: [
          // Employee Mode Toggle
          IconButton(
            onPressed: () {
              setState(() {
                _showEmployeeMode = !_showEmployeeMode;
              });
            },
            icon: Icon(
              _showEmployeeMode ? Icons.person : Icons.smart_toy,
              color: _showEmployeeMode ? Colors.amber : Colors.white,
            ),
            tooltip: _showEmployeeMode ? 'AI Mode' : 'Employee Mode',
          ),
          // Role Selector
          PopupMenuButton<AIAgentRole>(
            icon: const Icon(Icons.settings),
            onSelected: (role) {
              setState(() {
                _currentRole = role;
              });
              ref.read(aiProvider.notifier).switchRole(role);
            },
            itemBuilder: (context) => _availableRoles.map((role) {
              return PopupMenuItem(
                value: role,
                child: Row(
                  children: [
                    Icon(_getRoleIcon(role), size: 20),
                    const SizedBox(width: 8),
                    Text(_getRoleDisplayName(role)),
                  ],
                ),
              );
            }).toList(),
          ),
        ],
      ),
      body: Column(
        children: [
          // AI Stats Bar
          if (aiState.agentStats.isNotEmpty)
            _buildAIStatsBar(aiState.agentStats),

          // Employee Mode Panel
          if (_showEmployeeMode) _buildEmployeeModePanel(),

          // Chat Messages
          Expanded(
            child: aiState.conversationHistory.isEmpty
                ? _buildEmptyState()
                : _buildChatList(aiState),
          ),

          // Input Area
          _buildInputArea(aiState),
        ],
      ),
    );
  }

  Widget _buildAIStatsBar(Map<String, dynamic> stats) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border(
          bottom: BorderSide(color: Colors.grey.shade200),
        ),
      ),
      child: Row(
        children: [
          Icon(Icons.memory, size: 16, color: Colors.grey.shade600),
          const SizedBox(width: 8),
          Text(
            'Memory: ${stats['memory_entries'] ?? 0} entries',
            style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
          ),
          const SizedBox(width: 16),
          Icon(Icons.school, size: 16, color: Colors.grey.shade600),
          const SizedBox(width: 8),
          Text(
            'Learned: ${stats['learned_patterns'] ?? 0} patterns',
            style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
          ),
          const Spacer(),
          if (((stats['employee_tasks_completed'] as int?) ?? 0) > 0)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: Colors.green.shade100,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Text(
                'Tasks: ${stats['employee_tasks_completed']}',
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.green.shade700,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildEmployeeModePanel() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.amber.shade50,
        border: Border(
          bottom: BorderSide(color: Colors.amber.shade200),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(Icons.work, color: Colors.amber.shade800),
              const SizedBox(width: 8),
              Text(
                'Employee Mode - Quick Tasks',
                style: TextStyle(
                  fontWeight: FontWeight.bold,
                  color: Colors.amber.shade900,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: _quickTasks.map((task) {
              return ActionChip(
                avatar: Icon(_getTaskIcon(task), size: 18),
                label: Text(_getTaskName(task)),
                backgroundColor: Colors.white,
                side: BorderSide(color: Colors.amber.shade300),
                onPressed: () => _executeQuickTask(task),
              );
            }).toList(),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(22),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF0B194C), Color(0xFF1E3A8A)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                shape: BoxShape.circle,
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF0B194C).withValues(alpha: 0.2),
                    blurRadius: 16,
                    offset: const Offset(0, 6),
                  ),
                ],
              ),
              child: const Icon(
                Icons.smart_toy_outlined,
                size: 54,
                color: Color(0xFFE5A93C),
              ),
            ),
            const SizedBox(height: 20),
            const Text(
              'APS Real Estate AI Advisor',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 22,
                fontWeight: FontWeight.bold,
                color: Color(0xFF0B194C),
              ),
            ),
            const SizedBox(height: 8),
            Text(
              '7 टाउनशिप्स, 750+ प्लॉट्स, EMI प्लान्स और RERA लीगल पेपर्स की लाइव जानकारी',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 13,
                color: Colors.grey.shade700,
                height: 1.4,
              ),
            ),
            const SizedBox(height: 28),
            Wrap(
              spacing: 8,
              runSpacing: 10,
              alignment: WrapAlignment.center,
              children: [
                _buildSuggestionChip('🏰 Gorakhpur ki 7 Townships dikhayein'),
                _buildSuggestionChip('💰 1000 sqft Plot rate & EMI plan'),
                _buildSuggestionChip('📜 143 Mutation aur RERA registry details'),
                _buildSuggestionChip('🚗 Free Site Visit kaise book karein?'),
                _buildSuggestionChip('🤝 Associate commission aur earnings'),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSuggestionChip(String text) {
    return ActionChip(
      label: Text(
        text,
        style: const TextStyle(
          fontSize: 12,
          fontWeight: FontWeight.w600,
          color: Color(0xFF0B194C),
        ),
      ),
      backgroundColor: Colors.white,
      side: const BorderSide(color: Color(0xFFE8DFD0), width: 1.2),
      elevation: 1,
      shadowColor: const Color(0xFF0B194C).withValues(alpha: 0.05),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      onPressed: () {
        _messageController.text = text;
        _sendMessage();
      },
    );
  }

  Widget _buildChatList(AIState aiState) {
    return ListView.builder(
      controller: _scrollController,
      padding: const EdgeInsets.all(16),
      itemCount: aiState.conversationHistory.length,
      itemBuilder: (context, index) {
        final message = aiState.conversationHistory[index];
        final isUser = message['role'] == 'user';

        return Align(
          alignment: isUser ? Alignment.centerRight : Alignment.centerLeft,
          child: Container(
            margin: const EdgeInsets.only(bottom: 14),
            padding: const EdgeInsets.all(14),
            constraints: BoxConstraints(
              maxWidth: MediaQuery.of(context).size.width * 0.82,
            ),
            decoration: BoxDecoration(
              gradient: isUser
                  ? const LinearGradient(
                      colors: [Color(0xFF0B194C), Color(0xFF1E3A8A)],
                    )
                  : null,
              color: isUser ? null : Colors.white,
              borderRadius: BorderRadius.circular(16).copyWith(
                bottomRight: isUser ? const Radius.circular(4) : null,
                bottomLeft: !isUser ? const Radius.circular(4) : null,
              ),
              border: isUser
                  ? null
                  : Border.all(color: const Color(0xFFE8DFD0), width: 1.2),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF0B194C).withValues(
                    alpha: isUser ? 0.12 : 0.04,
                  ),
                  blurRadius: 8,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (!isUser) ...[
                  Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Container(
                        padding: const EdgeInsets.all(4),
                        decoration: const BoxDecoration(
                          color: Color(0xFFFAF7F0),
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(
                          Icons.smart_toy,
                          size: 14,
                          color: Color(0xFFF58220),
                        ),
                      ),
                      const SizedBox(width: 6),
                      const Text(
                        'APS AI Advisor',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                          color: Color(0xFF0B194C),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                ],
                Text(
                  message['message'] as String,
                  style: TextStyle(
                    color: isUser ? Colors.white : const Color(0xFF0B194C),
                    fontSize: 14.5,
                    height: 1.45,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  _formatTime(message['timestamp'] as String?),
                  style: TextStyle(
                    color: isUser
                        ? Colors.white.withValues(alpha: 0.7)
                        : Colors.grey.shade500,
                    fontSize: 10,
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildInputArea(AIState aiState) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        border: const Border(
          top: BorderSide(color: Color(0xFFE8DFD0), width: 1.2),
        ),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0B194C).withValues(alpha: 0.05),
            blurRadius: 8,
            offset: const Offset(0, -2),
          ),
        ],
      ),
      child: SafeArea(
        child: Row(
          children: [
            // Voice / Assistance Icon
            Container(
              decoration: const BoxDecoration(
                color: Color(0xFFFAF7F0),
                shape: BoxShape.circle,
              ),
              child: IconButton(
                onPressed: () {
                  _messageController.text = 'Gorakhpur me plots ka rate list batao';
                  _sendMessage();
                },
                icon: const Icon(Icons.flash_on, color: Color(0xFFF58220), size: 20),
                tooltip: 'Quick Query',
              ),
            ),
            const SizedBox(width: 8),

            // Text Input
            Expanded(
              child: TextField(
                controller: _messageController,
                style: const TextStyle(
                  color: Color(0xFF0B194C),
                  fontSize: 14,
                ),
                decoration: InputDecoration(
                  hintText: aiState.isLoading
                      ? 'AI is analyzing township data...'
                      : 'Ask about plots, EMI, colonies, registry...',
                  hintStyle: TextStyle(
                    color: Colors.grey.shade500,
                    fontSize: 13,
                  ),
                  filled: true,
                  fillColor: const Color(0xFFFAF7F0),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(24),
                    borderSide: const BorderSide(
                      color: Color(0xFFE8DFD0),
                      width: 1,
                    ),
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(24),
                    borderSide: const BorderSide(
                      color: Color(0xFFE8DFD0),
                      width: 1,
                    ),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(24),
                    borderSide: const BorderSide(
                      color: Color(0xFF0B194C),
                      width: 1.5,
                    ),
                  ),
                  contentPadding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 12,
                  ),
                ),
                enabled: !aiState.isLoading,
                onSubmitted: (_) => _sendMessage(),
              ),
            ),

            // Send Button
            const SizedBox(width: 8),
            aiState.isLoading
                ? const SizedBox(
                    width: 44,
                    height: 44,
                    child: Center(
                      child: SizedBox(
                        width: 22,
                        height: 22,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: Color(0xFF0B194C),
                        ),
                      ),
                    ),
                  )
                : Container(
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFF0B194C), Color(0xFF1E3A8A)],
                      ),
                      shape: BoxShape.circle,
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFF0B194C).withValues(alpha: 0.25),
                          blurRadius: 6,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    child: IconButton(
                      onPressed: _sendMessage,
                      icon: const Icon(Icons.send_rounded, size: 20),
                      color: const Color(0xFFE5A93C),
                    ),
                  ),
          ],
        ),
      ),
    );
  }

  void _sendMessage() {
    final message = _messageController.text.trim();
    if (message.isEmpty) return;

    _messageController.clear();

    ref.read(aiProvider.notifier).sendMessage(
          message: message,
          userId: 'current_user',
          role: _currentRole,
        );

    // Scroll to bottom
    Future.delayed(const Duration(milliseconds: 100), () {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    });
  }

  Future<void> _executeQuickTask(AIEmployeeTask task) async {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => const AlertDialog(
        content: Row(
          children: [
            CircularProgressIndicator(),
            SizedBox(width: 16),
            Text('AI Employee working...'),
          ],
        ),
      ),
    );

    try {
      final result = await ref.read(aiProvider.notifier).executeEmployeeTask(
        task: task,
        data: {
          // Sample data - in real app, fetch from backend
          'leads': [
            {
              'id': '1',
              'name': 'Rahul Sharma',
              'phone': '9876543210',
              'stage': 'new'
            },
            {
              'id': '2',
              'name': 'Priya Patel',
              'phone': '9876543211',
              'stage': 'contacted'
            },
          ],
        },
      );

      Navigator.pop(context);

      // Show result
      showDialog(
        context: context,
        builder: (context) => AlertDialog(
          title: Row(
            children: [
              Icon(
                result.success ? Icons.check_circle : Icons.error,
                color: result.success ? Colors.green : Colors.red,
              ),
              const SizedBox(width: 8),
              Text(result.success ? 'Task Completed' : 'Task Failed'),
            ],
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(result.summary),
              if (result.actions.isNotEmpty) ...[
                const SizedBox(height: 16),
                const Text(
                  'Suggested Actions:',
                  style: TextStyle(fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 8),
                ...result.actions.map((action) => Padding(
                      padding: const EdgeInsets.only(bottom: 4),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('• '),
                          Expanded(child: Text(action)),
                        ],
                      ),
                    )),
              ],
              if (result.requiresHumanReview) ...[
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: Colors.orange.shade50,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: Colors.orange.shade200),
                  ),
                  child: Row(
                    children: [
                      Icon(Icons.warning, color: Colors.orange.shade700),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          'Requires human review: ${result.humanReviewReason}',
                          style: TextStyle(
                            color: Colors.orange.shade900,
                            fontSize: 12,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Close'),
            ),
            if (result.requiresHumanReview)
              ElevatedButton(
                onPressed: () {
                  Navigator.pop(context);
                  // TODO: Navigate to review page
                },
                child: const Text('Review'),
              ),
          ],
        ),
      );
    } catch (e) {
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $e')),
      );
    }
  }

  String _getRoleDisplayName(AIAgentRole role) {
    switch (role) {
      case AIAgentRole.customerSupport:
        return 'Customer Support';
      case AIAgentRole.salesAssistant:
        return 'Sales Assistant';
      case AIAgentRole.propertyExpert:
        return 'Property Expert';
      case AIAgentRole.investmentAdvisor:
        return 'Investment Advisor';
      case AIAgentRole.managerAssistant:
        return 'Manager Assistant';
      default:
        return 'AI Assistant';
    }
  }

  IconData _getRoleIcon(AIAgentRole role) {
    switch (role) {
      case AIAgentRole.customerSupport:
        return Icons.support_agent;
      case AIAgentRole.salesAssistant:
        return Icons.trending_up;
      case AIAgentRole.propertyExpert:
        return Icons.home_work;
      case AIAgentRole.investmentAdvisor:
        return Icons.account_balance;
      case AIAgentRole.managerAssistant:
        return Icons.manage_accounts;
      default:
        return Icons.smart_toy;
    }
  }

  String _getTaskName(AIEmployeeTask task) {
    switch (task) {
      case AIEmployeeTask.leadFollowUp:
        return 'Lead Follow-up';
      case AIEmployeeTask.scoreLead:
        return 'Score Lead';
      case AIEmployeeTask.sendReminders:
        return 'Send Reminders';
      case AIEmployeeTask.analyzePerformance:
        return 'Analyze Performance';
      default:
        return task.name;
    }
  }

  IconData _getTaskIcon(AIEmployeeTask task) {
    switch (task) {
      case AIEmployeeTask.leadFollowUp:
        return Icons.phone;
      case AIEmployeeTask.scoreLead:
        return Icons.analytics;
      case AIEmployeeTask.sendReminders:
        return Icons.notifications_active;
      case AIEmployeeTask.analyzePerformance:
        return Icons.assessment;
      default:
        return Icons.task;
    }
  }

  String _formatTime(String? isoTime) {
    if (isoTime == null) return '';
    try {
      final time = DateTime.parse(isoTime);
      return '${time.hour.toString().padLeft(2, '0')}:${time.minute.toString().padLeft(2, '0')}';
    } catch (e) {
      return '';
    }
  }
}

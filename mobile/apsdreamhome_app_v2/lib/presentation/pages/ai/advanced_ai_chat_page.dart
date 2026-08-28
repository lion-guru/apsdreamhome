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
      backgroundColor: Colors.grey.shade50,
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('APS AI Assistant'),
            Text(
              _getRoleDisplayName(_currentRole),
              style: const TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.normal,
              ),
            ),
          ],
        ),
        elevation: 0,
        backgroundColor: const Color(0xFF4285F4),
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
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: const Color(0xFF4285F4).withValues(alpha: 0.1),
              shape: BoxShape.circle,
            ),
            child: const Icon(
              Icons.smart_toy,
              size: 64,
              color: Color(0xFF4285F4),
            ),
          ),
          const SizedBox(height: 24),
          const Text(
            'APS AI Assistant',
            style: TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'How can I help you today?',
            style: TextStyle(
              fontSize: 16,
              color: Colors.grey.shade600,
            ),
          ),
          const SizedBox(height: 32),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            alignment: WrapAlignment.center,
            children: [
              _buildSuggestionChip('Show me plots in Gorakhpur'),
              _buildSuggestionChip('What is EMI calculator?'),
              _buildSuggestionChip('How to book a plot?'),
              _buildSuggestionChip('Compare properties'),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildSuggestionChip(String text) {
    return ActionChip(
      label: Text(text),
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
            margin: const EdgeInsets.only(bottom: 12),
            padding: const EdgeInsets.all(12),
            constraints: BoxConstraints(
              maxWidth: MediaQuery.of(context).size.width * 0.8,
            ),
            decoration: BoxDecoration(
              color: isUser ? const Color(0xFF4285F4) : Colors.white,
              borderRadius: BorderRadius.circular(16).copyWith(
                bottomRight: isUser ? const Radius.circular(4) : null,
                bottomLeft: !isUser ? const Radius.circular(4) : null,
              ),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.05),
                  blurRadius: 4,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  message['message'] as String,
                  style: TextStyle(
                    color: isUser ? Colors.white : Colors.black87,
                    fontSize: 15,
                  ),
                ),
                const SizedBox(height: 4),
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
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 8,
            offset: const Offset(0, -2),
          ),
        ],
      ),
      child: SafeArea(
        child: Row(
          children: [
            // Voice Input Button
            IconButton(
              onPressed: () {
                // TODO: Implement voice input
              },
              icon: const Icon(Icons.mic),
              color: Colors.grey.shade600,
            ),

            // Text Input
            Expanded(
              child: TextField(
                controller: _messageController,
                decoration: InputDecoration(
                  hintText: aiState.isLoading
                      ? 'AI is thinking...'
                      : 'Type your message...',
                  filled: true,
                  fillColor: Colors.grey.shade100,
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(24),
                    borderSide: BorderSide.none,
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
                    width: 48,
                    height: 48,
                    child: Center(
                      child: SizedBox(
                        width: 24,
                        height: 24,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      ),
                    ),
                  )
                : IconButton.filled(
                    onPressed: _sendMessage,
                    icon: const Icon(Icons.send),
                    style: IconButton.styleFrom(
                      backgroundColor: const Color(0xFF4285F4),
                      foregroundColor: Colors.white,
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

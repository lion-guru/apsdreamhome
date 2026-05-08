import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

import '../../../data/services/ai_assistant_service.dart';
import '../../../core/utils/logger.dart';

/// AI Chat Page
/// Full-screen conversational AI interface like Gemini
class AIChatPage extends ConsumerStatefulWidget {
  const AIChatPage({super.key});

  @override
  ConsumerState<AIChatPage> createState() => _AIChatPageState();
}

class _AIChatPageState extends ConsumerState<AIChatPage> {
  final TextEditingController _messageController = TextEditingController();
  final ScrollController _scrollController = ScrollController();
  final FocusNode _focusNode = FocusNode();

  bool _isTyping = false;
  final String _userId = 'demo_user'; // Would come from auth
  final String _userRole = 'customer'; // Would come from auth

  @override
  void dispose() {
    _messageController.dispose();
    _scrollController.dispose();
    _focusNode.dispose();
    super.dispose();
  }

  void _scrollToBottom() {
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

  Future<void> _sendMessage() async {
    final message = _messageController.text.trim();
    if (message.isEmpty) return;

    // Add user message to chat
    ref.read(aiChatProvider.notifier).addMessage('user', message);
    _messageController.clear();
    _scrollToBottom();

    // Show typing indicator
    setState(() {
      _isTyping = true;
    });

    try {
      // Get AI response
      final aiService = ref.read(aiAssistantServiceProvider);
      final response = await aiService.processMessage(
        message,
        userId: _userId,
        userRole: _userRole,
      );

      // Add AI response
      ref.read(aiChatProvider.notifier).addMessage(
            'assistant',
            response.message,
            actions: response.suggestedActions,
          );
    } catch (e) {
      AppLogger.error('AI response error', e);
      ref.read(aiChatProvider.notifier).addMessage(
            'assistant',
            "I'm sorry, I encountered an error. Please try again or contact support.",
          );
    } finally {
      setState(() {
        _isTyping = false;
      });
      _scrollToBottom();
    }
  }

  @override
  Widget build(BuildContext context) {
    final chatHistory = ref.watch(aiChatProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFF8F9FA), // Gemini-like background
      appBar: _buildAppBar(),
      body: Column(
        children: [
          // Chat Messages
          Expanded(
            child: chatHistory.isEmpty
                ? _buildWelcomeScreen()
                : _buildChatList(chatHistory),
          ),

          // Typing Indicator
          if (_isTyping) _buildTypingIndicator(),

          // Suggested Prompts (when chat is empty)
          if (chatHistory.isEmpty) _buildSuggestedPrompts(),

          // Input Area
          _buildInputArea(),
        ],
      ),
    );
  }

  PreferredSizeWidget _buildAppBar() {
    return AppBar(
      backgroundColor: Colors.white,
      elevation: 0,
      leading: IconButton(
        icon: const Icon(Icons.arrow_back, color: Colors.black87),
        onPressed: () => context.pop(),
      ),
      title: Row(
        children: [
          // AI Avatar with animation
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [
                  Color(0xFF4285F4),
                  Color(0xFF34A853),
                  Color(0xFFFBBC04),
                  Color(0xFFEA4335)
                ],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(20),
            ),
            child: const Center(
              child: Icon(
                Icons.auto_awesome,
                color: Colors.white,
                size: 24,
              ),
            ),
          ),
          const SizedBox(width: 12),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'APS AI Assistant',
                style: TextStyle(
                  color: Colors.black87,
                  fontWeight: FontWeight.bold,
                  fontSize: 16,
                ),
              ),
              Row(
                children: [
                  Container(
                    width: 8,
                    height: 8,
                    decoration: BoxDecoration(
                      color: Colors.green,
                      borderRadius: BorderRadius.circular(4),
                    ),
                  ),
                  const SizedBox(width: 6),
                  Text(
                    _isTyping ? 'Typing...' : 'Online',
                    style: TextStyle(
                      color: Colors.grey.shade600,
                      fontSize: 12,
                    ),
                  ),
                ],
              ),
            ],
          ),
        ],
      ),
      actions: [
        IconButton(
          icon: const Icon(Icons.more_vert, color: Colors.black87),
          onPressed: _showOptions,
        ),
      ],
    );
  }

  Widget _buildWelcomeScreen() {
    return Center(
      child: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            // Animated AI Icon
            Container(
              width: 100,
              height: 100,
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF4285F4), Color(0xFF34A853)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(30),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF4285F4).withValues(alpha: 0.3),
                    blurRadius: 20,
                    spreadRadius: 5,
                  ),
                ],
              ),
              child: const Icon(
                Icons.psychology,
                size: 50,
                color: Colors.white,
              ),
            ),
            const SizedBox(height: 32),
            const Text(
              'Namaste! 🙏',
              style: TextStyle(
                fontSize: 28,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 12),
            Text(
              'I\'m your APS Dream Home AI assistant',
              style: TextStyle(
                fontSize: 18,
                color: Colors.grey.shade600,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Ask me anything about plots, EMI, site visits, or commissions!',
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

  Widget _buildChatList(List<Map<String, dynamic>> chatHistory) {
    return ListView.builder(
      controller: _scrollController,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      itemCount: chatHistory.length,
      itemBuilder: (context, index) {
        final message = chatHistory[index];
        final isUser = message['role'] == 'user';

        return Column(
          crossAxisAlignment:
              isUser ? CrossAxisAlignment.end : CrossAxisAlignment.start,
          children: [
            _buildMessageBubble(message, isUser),
            if (!isUser && message['actions'] != null)
              _buildActionButtons(message['actions'] as List<dynamic>),
            const SizedBox(height: 16),
          ],
        );
      },
    );
  }

  Widget _buildMessageBubble(Map<String, dynamic> message, bool isUser) {
    return Container(
      margin: EdgeInsets.only(
        left: isUser ? 64 : 0,
        right: isUser ? 0 : 64,
      ),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        color: isUser ? const Color(0xFF4285F4) : Colors.white,
        borderRadius: BorderRadius.only(
          topLeft: const Radius.circular(20),
          topRight: const Radius.circular(20),
          bottomLeft: Radius.circular(isUser ? 20 : 4),
          bottomRight: Radius.circular(isUser ? 4 : 20),
        ),
        boxShadow: isUser
            ? null
            : [
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
          // Sender icon for AI
          if (!isUser)
            Padding(
              padding: const EdgeInsets.only(bottom: 8),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    width: 24,
                    height: 24,
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFF4285F4), Color(0xFF34A853)],
                      ),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(Icons.auto_awesome,
                        size: 14, color: Colors.white),
                  ),
                  const SizedBox(width: 8),
                  const Text(
                    'APS AI',
                    style: TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 12,
                      color: Colors.black87,
                    ),
                  ),
                ],
              ),
            ),

          // Message text
          Text(
            message['message'] as String,
            style: TextStyle(
              color: isUser ? Colors.white : Colors.black87,
              fontSize: 15,
              height: 1.4,
            ),
          ),

          // Timestamp
          Padding(
            padding: const EdgeInsets.only(top: 4),
            child: Text(
              DateFormat('h:mm a').format(message['timestamp'] as DateTime),
              style: TextStyle(
                color: isUser ? Colors.white70 : Colors.grey.shade500,
                fontSize: 11,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildActionButtons(List<dynamic> actions) {
    return Container(
      margin: const EdgeInsets.only(top: 8, left: 8),
      child: Wrap(
        spacing: 8,
        runSpacing: 8,
        children: actions.map<Widget>((action) {
          final actionMap = action as Map<String, dynamic>;
          return ActionChip(
            label: Text(actionMap['label'] as String),
            onPressed: () {
              context.push(actionMap['route'] as String);
            },
            backgroundColor: const Color(0xFFE8F0FE),
            side: BorderSide.none,
            labelStyle: const TextStyle(
              color: Color(0xFF4285F4),
              fontWeight: FontWeight.w500,
            ),
          );
        }).toList(),
      ),
    );
  }

  Widget _buildTypingIndicator() {
    return Container(
      margin: const EdgeInsets.only(left: 16, bottom: 8),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(20),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.05),
                  blurRadius: 4,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                _buildDot(0),
                _buildDot(1),
                _buildDot(2),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDot(int index) {
    return AnimatedContainer(
      duration: const Duration(milliseconds: 300),
      margin: const EdgeInsets.symmetric(horizontal: 2),
      width: 8,
      height: 8,
      decoration: BoxDecoration(
        color: const Color(0xFF4285F4).withValues(alpha: 0.5 + (index * 0.2)),
        borderRadius: BorderRadius.circular(4),
      ),
    );
  }

  Widget _buildSuggestedPrompts() {
    final prompts = [
      'Show me plots in Gorakhpur',
      'Calculate EMI for 25 lakh',
      'Book a site visit tomorrow',
      'What\'s my commission?',
      'KYC status check',
      'Property value in Lucknow',
    ];

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Try asking:',
            style: TextStyle(
              color: Colors.grey.shade600,
              fontSize: 14,
              fontWeight: FontWeight.w500,
            ),
          ),
          const SizedBox(height: 8),
          SizedBox(
            height: 40,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              itemCount: prompts.length,
              itemBuilder: (context, index) {
                return Padding(
                  padding: const EdgeInsets.only(right: 8),
                  child: ActionChip(
                    label: Text(prompts[index]),
                    onPressed: () {
                      _messageController.text = prompts[index];
                      _sendMessage();
                    },
                    backgroundColor: Colors.white,
                    side: BorderSide(color: Colors.grey.shade300),
                    labelStyle: TextStyle(
                      color: Colors.grey.shade700,
                      fontSize: 12,
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInputArea() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 8,
            offset: const Offset(0, -4),
          ),
        ],
      ),
      child: SafeArea(
        child: Row(
          children: [
            // Attach button
            IconButton(
              icon: const Icon(Icons.attach_file, color: Color(0xFF4285F4)),
              onPressed: () {
                // File attachment
              },
            ),

            // Text field
            Expanded(
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                decoration: BoxDecoration(
                  color: Colors.grey.shade100,
                  borderRadius: BorderRadius.circular(24),
                ),
                child: TextField(
                  controller: _messageController,
                  focusNode: _focusNode,
                  decoration: const InputDecoration(
                    hintText: 'Message APS AI...',
                    border: InputBorder.none,
                    contentPadding: EdgeInsets.symmetric(vertical: 14),
                  ),
                  onSubmitted: (_) => _sendMessage(),
                ),
              ),
            ),

            const SizedBox(width: 8),

            // Send button or Mic
            GestureDetector(
              onTap: _sendMessage,
              child: Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFF4285F4), Color(0xFF34A853)],
                  ),
                  borderRadius: BorderRadius.circular(24),
                ),
                child: const Icon(
                  Icons.send,
                  color: Colors.white,
                  size: 20,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showOptions() {
    showModalBottomSheet(
      context: context,
      builder: (context) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              leading: const Icon(Icons.delete_outline),
              title: const Text('Clear conversation'),
              onTap: () {
                ref.read(aiChatProvider.notifier).clear();
                ref.read(aiAssistantServiceProvider).clearHistory();
                Navigator.pop(context);
              },
            ),
            ListTile(
              leading: const Icon(Icons.help_outline),
              title: const Text('What can you do?'),
              onTap: () {
                Navigator.pop(context);
                _messageController.text = 'What can you do?';
                _sendMessage();
              },
            ),
            ListTile(
              leading: const Icon(Icons.support_agent),
              title: const Text('Contact human support'),
              onTap: () {
                Navigator.pop(context);
                context.push('/tools/chat');
              },
            ),
          ],
        ),
      ),
    );
  }
}

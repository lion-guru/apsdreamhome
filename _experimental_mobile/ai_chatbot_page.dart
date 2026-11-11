import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../../core/services/api_service.dart';
import '../../../core/utils/logger.dart';

/// Simple AI Chat Page using the new AIChatbotService API
/// Uses multi-provider chat with RAG support
class AIChatbotPage extends ConsumerStatefulWidget {
  const AIChatbotPage({super.key});

  @override
  ConsumerState<AIChatbotPage> createState() => _AIChatbotPageState();
}

class _AIChatbotPageState extends ConsumerState<AIChatbotPage> {
  final TextEditingController _messageController = TextEditingController();
  final ScrollController _scrollController = ScrollController();
  final FocusNode _focusNode = FocusNode();

  bool _isLoading = false;
  String _selectedProvider = 'groq';
  final List<_ChatMessage> _messages = [];

  static const Color primaryColor = Color(0xFF0B194C);
  static const Color secondaryColor = Color(0xFFF58220);
  static const Color accentColor = Color(0xFFE5A93C);

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
    if (message.isEmpty || _isLoading) return;

    // Add user message
    setState(() {
      _messages.add(_ChatMessage(
        role: 'user',
        content: message,
        timestamp: DateTime.now(),
      ));
      _isLoading = true;
    });
    _messageController.clear();
    _scrollToBottom();

    try {
      final apiService = ref.read(apiServiceProvider);
      final response = await apiService.post('/api/ai/chat', {
        'messages': [
          ..._messages.map((m) => {'role': m.role, 'content': m.content}),
          {'role': 'user', 'content': _messageController.text.trim()},
        ],
        'provider': _selectedProvider,
        'use_rag': true,
        'temperature': 0.7,
        'max_tokens': 1000,
      });

      if (response.data['success'] == true) {
        setState(() {
          _messages.add(_ChatMessage(
            role: 'assistant',
            content: response.data['message'] as String,
            timestamp: DateTime.now(),
            provider: response.data['provider'] as String?,
            model: response.data['model'] as String?,
          ));
        });
      } else {
        _addErrorMessage(response.data['message'] as String? ?? 'Chat failed');
      }
    } catch (e) {
      AppLogger.error('AI chat error', e);
      _addErrorMessage('Connection error. Please try again.');
    } finally {
      setState(() => _isLoading = false);
      _scrollToBottom();
    }
  }

  void _addErrorMessage(String message) {
    setState(() {
      _messages.add(_ChatMessage(
        role: 'assistant',
        content: message,
        timestamp: DateTime.now(),
        isError: true,
      ));
    });
  }

  Future<void> _loadProviders() async {
    try {
      final apiService = ref.read(apiServiceProvider);
      final response = await apiService.get('/api/ai/chat/providers');
      if (response.data['success'] == true) {
        // Could update provider list here
      }
    } catch (e) {
      AppLogger.warning('Failed to load providers', e);
    }
  }

  void _showProviderSelector() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (context) => Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
        ),
        padding: const EdgeInsets.all(20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Select AI Provider',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 16),
            ...['groq', 'openrouter', 'gemini', 'ollama'].map((p) {
              final labels = {
                'groq': 'Groq (Llama 3.1 - Fast)',
                'openrouter': 'OpenRouter (Gemini/Claude)',
                'gemini': 'Gemini (Google)',
                'ollama': 'Ollama (Local)',
              };
              return ListTile(
                leading: Radio<String>(
                  value: p,
                  groupValue: _selectedProvider,
                  onChanged: (v) => setState(() => _selectedProvider = v!),
                ),
                title: Text(labels[p] ?? p),
                onTap: () {
                  setState(() => _selectedProvider = p);
                  Navigator.pop(context);
                },
              );
            }),
          ],
        ),
      ),
    );
  }

  @override
  void initState() {
    super.initState();
    _loadProviders();
    // Welcome message
    _messages.add(_ChatMessage(
      role: 'assistant',
      content: 'Hello! I'm your APS Dream Home AI assistant. Ask me about plots, bookings, EMI, site visits, or anything else!',
      timestamp: DateTime.now(),
    ));
  }

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
    if (message.isEmpty || _isLoading) return;

    // Add user message
    setState(() {
      _messages.add(_ChatMessage(
        role: 'user',
        content: message,
        timestamp: DateTime.now(),
      ));
      _isLoading = true;
    });
    _messageController.clear();
    _scrollToBottom();

    try {
      final apiService = ref.read(apiServiceProvider);
      final response = await apiService.post('/api/ai/chat', {
        'messages': [
          ..._messages.map((m) => {'role': m.role, 'content': m.content}),
          {'role': 'user', 'content': _messageController.text.trim()},
        ],
        'provider': _selectedProvider,
        'use_rag': true,
        'temperature': 0.7,
        'max_tokens': 1000,
      });

      if (response.data['success'] == true) {
        setState(() {
          _messages.add(_ChatMessage(
            role: 'assistant',
            content: response.data['message'] as String,
            timestamp: DateTime.now(),
            provider: response.data['provider'] as String?,
            model: response.data['model'] as String?,
          ));
        });
      } else {
        _addErrorMessage(response.data['message'] as String? ?? 'Chat failed');
      }
    } catch (e) {
      AppLogger.error('AI chat error', e);
      _addErrorMessage('Connection error. Please try again.');
    } finally {
      setState(() => _isLoading = false);
      _scrollToBottom();
    }
  }

  void _addErrorMessage(String message) {
    setState(() {
      _messages.add(_ChatMessage(
        role: 'assistant',
        content: message,
        timestamp: DateTime.now(),
        isError: true,
      ));
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8F9FA),
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.1),
                        blurRadius: 8,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: const Icon(
                    Icons.smart_toy_rounded,
                    color: Color(0xFF0B194C),
                    size: 24,
                  ),
                ),
                const SizedBox(width: 12),
                const Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'APS AI Assistant',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF0B194C),
                      ),
                    ),
                    Text(
                      'Multi-provider AI with RAG',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.normal,
                        color: Color(0xFF64748B),
                      ),
                    ),
                  ],
                ),
              ],
            ),
            actions: [
              IconButton(
                icon: const Icon(Icons.settings_outlined),
                onPressed: _showProviderSelector,
                tooltip: 'Change AI Provider',
              ),
              IconButton(
                icon: const Icon(Icons.delete_outline),
                onPressed: () {
                  setState(() {
                    _messages.clear();
                    _messages.add(_ChatMessage(
                      role: 'assistant',
                      content: 'Chat cleared. How can I help you?',
                      timestamp: DateTime.now(),
                    ));
                  });
                },
                tooltip: 'Clear Chat',
              ),
            ],
            elevation: 0,
            backgroundColor: Colors.white,
            foregroundColor: const Color(0xFF0B194C),
            surfaceTintColor: Colors.white,
          ),
          body: Column(
            children: [
              // Provider indicator
              Container(
                width: double.infinity,
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                color: const Color(0xFFF0F4F8),
                child: Row(
                  children: [
                    Icon(
                      Icons.circle,
                      size: 8,
                      color: _isLoading ? Colors.orange : Colors.green,
                    ),
                    const SizedBox(width: 8),
                    Text(
                      'Provider: \ ',
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                        color: Color(0xFF64748B),
                      ),
                    ),
                    const Spacer(),
                    if (_messages.any((m) => m.isError))
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                        decoration: BoxDecoration(
                          color: Colors.red.shade50,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Text(
                          'Connection Issue',
                          style: TextStyle(
                            fontSize: 10,
                            color: Colors.red,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ),
                  ],
                ),
              ),

              // Messages
              Expanded(
                child: ListView.builder(
                  controller: _scrollController,
                  padding: const EdgeInsets.all(16),
                  itemCount: _messages.length,
                  itemBuilder: (context, index) {
                    final message = _messages[index];
                    final isUser = message.role == 'user';
                    return _buildMessageBubble(message, isUser);
                  },
                ),

              // Typing indicator
              if (_isLoading)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  child: Row(
                    children: [
                      Container(
                        width: 32,
                        height: 32,
                        decoration: BoxDecoration(
                          color: primaryColor.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: const Center(
                          child: Icon(
                            Icons.smart_toy_rounded,
                            size: 16,
                            color: primaryColor,
                          ),
                        ),
                      ),
                    const SizedBox(width: 8),
                    const SizedBox(
                      width: 48,
                      height: 24,
                      child: _TypingIndicator(),
                    ),
                  ],
                ),

              // Input area
              Container(
                padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
                decoration: BoxDecoration(
                  color: Colors.white,
                  border: Border(
                    top: BorderSide(color: Colors.grey.shade200),
                  ),
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Expanded(
                      child: Container(
                        constraints: const BoxConstraints(maxHeight: 120),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF8F9FA),
                          borderRadius: BorderRadius.circular(24),
                          border: Border.all(color: Colors.grey.shade200),
                        ),
                        child: TextField(
                          controller: _messageController,
                          focusNode: _focusNode,
                          maxLines: null,
                          textCapitalization: TextCapitalization.sentences,
                          decoration: InputDecoration(
                            hintText: 'Ask about plots, bookings, EMI, site visits...',
                            hintStyle: TextStyle(
                              color: Colors.grey.shade400,
                              fontSize: 14,
                            ),
                            border: InputBorder.none,
                            contentPadding: const EdgeInsets.symmetric(
                              horizontal: 20,
                              vertical: 12,
                            ),
                          ),
                          style: const TextStyle(fontSize: 14, height: 1.4),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Container(
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [Color(0xFFF58220), Color(0xFFE5A93C)],
                        ),
                        borderRadius: BorderRadius.circular(28),
                        boxShadow: [
                          BoxShadow(
                            color: const Color(0xFFF58220).withValues(alpha: 0.4),
                            blurRadius: 8,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: Material(
                        color: Colors.transparent,
                        child: InkWell(
                          onTap: _isLoading ? null : _sendMessage,
                          borderRadius: BorderRadius.circular(28),
                          child: Padding(
                            padding: const EdgeInsets.all(14),
                            child: _isLoading
                                ? const SizedBox(
                                    width: 20,
                                    height: 20,
                                    child: CircularProgressIndicator(
                                      strokeWidth: 2,
                                      valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                                    ),
                                  )
                                : const Icon(
                                    Icons.send_rounded,
                                    color: Colors.white,
                                    size: 22,
                                  ),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
    );
  }

  Widget _buildMessageBubble(_ChatMessage message, bool isUser) {
    final time = DateFormat('HH:mm').format(message.timestamp);
    
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        mainAxisAlignment: isUser ? MainAxisAlignment.end : MainAxisAlignment.start,
        crossAxisAlignment: CrossAxisAlignment.end,
        children: [
          if (!isUser) ...[
            Container(
              width: 32,
              height: 32,
              decoration: BoxDecoration(
                color: primaryColor.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(16),
              ),
              child: const Center(
                child: Icon(
                  Icons.smart_toy_rounded,
                  size: 16,
                  color: primaryColor,
                ),
              ),
            ),
            const SizedBox(width: 8),
          ],
          Flexible(
            child: Container(
              constraints: BoxConstraints(
                maxWidth: MediaQuery.of(context).size.width * 0.75,
              ),
              decoration: BoxDecoration(
                color: isUser
                    ? const Color(0xFF0B194C)
                    : Colors.white,
                borderRadius: BorderRadius.only(
                  topLeft: const Radius.circular(18),
                  topRight: const Radius.circular(18),
                  bottomLeft: Radius.circular(isUser ? 18 : 4),
                  bottomRight: Radius.circular(isUser ? 4 : 18),
                ),
                border: isUser
                    ? null
                    : Border.all(
                        color: const Color(0xFF0B194C).withValues(alpha: 0.08),
                        width: 1,
                      ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.04),
                    blurRadius: 8,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (message.isError)
                    Container(
                      padding: const EdgeInsets.only(bottom: 8),
                      child: Row(
                        children: [
                          const Icon(
                            Icons.error_outline,
                            size: 14,
                            color: Colors.red,
                          ),
                          const SizedBox(width: 4),
                          Expanded(
                            child: Text(
                              message.content,
                              style: const TextStyle(
                                color: Colors.red,
                                fontSize: 13,
                              ),
                            ),
                          ),
                        ],
                      ),
                    )
                  else
                    Text(
                      message.content,
                      style: TextStyle(
                        fontSize: 14,
                        height: 1.5,
                        color: isUser ? Colors.white : const Color(0xFF1E293B),
                      ),
                    ),
                  const SizedBox(height: 4),
                  Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        DateFormat('HH:mm').format(message.timestamp),
                        style: TextStyle(
                          fontSize: 10,
                          color: isUser
                              ? Colors.white.withValues(alpha: 0.6)
                              : Colors.grey.shade400,
                        ),
                      ),
                      if (message.provider != null) ...[
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                          decoration: BoxDecoration(
                            color: isUser
                                ? Colors.white.withValues(alpha: 0.2)
                                : primaryColor.withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            '\ • ',
                            style: TextStyle(
                              fontSize: 9,
                              color: isUser
                                  ? Colors.white.withValues(alpha: 0.7)
                                  : primaryColor,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          if (isUser) ...[
            const SizedBox(width: 8),
            Container(
              width: 32,
              height: 32,
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFFF58220), Color(0xFFE5A93C)],
                ),
                borderRadius: BorderRadius.circular(16),
              ),
              child: const Center(
                child: Icon(
                  Icons.person,
                  size: 16,
                  color: Colors.white,
                ),
              ),
            ),
          ],
        ],
      );
    }
  }
}

class _ChatMessage {
  final String role;
  final String content;
  final DateTime timestamp;
  final String? provider;
  final String? model;
  final bool isError;

  _ChatMessage({
    required this.role,
    required this.content,
    required this.timestamp,
    this.provider,
    this.model,
    this.isError = false,
  });
}

class _TypingIndicator extends StatefulWidget {
  const _TypingIndicator();

  @override
  State<_TypingIndicator> createState() => _TypingIndicatorState();
}

class _TypingIndicatorState extends State<_TypingIndicator>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      duration: const Duration(milliseconds: 1200),
      vsync: this,
    )..repeat();
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _controller,
      builder: (context, child) {
        return Row(
          mainAxisSize: MainAxisSize.min,
          children: List.generate(3, (index) {
            final delay = index * 0.2;
            final animationValue = (_controller.value - delay).clamp(0.0, 1.0);
            return Container(
              margin: const EdgeInsets.symmetric(horizontal: 2),
              width: 6,
              height: 6,
              decoration: BoxDecoration(
                color: const Color(0xFFF58220).withValues(alpha: animationValue),
                borderRadius: BorderRadius.circular(3),
              ),
              transform: Matrix4.translationValues(
                0,
                -8 * (1 - animationValue).abs(),
                0,
              ),
            );
          }),
        );
      },
    );
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }
}

import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../../data/services/chat_service.dart';

/// Live Chat Page with real backend API + graceful mock fallback.
class LiveChatPage extends ConsumerStatefulWidget {
  const LiveChatPage({super.key});

  @override
  ConsumerState<LiveChatPage> createState() => _LiveChatPageState();
}

class _LiveChatPageState extends ConsumerState<LiveChatPage> {
  final TextEditingController _messageController = TextEditingController();
  final ScrollController _scrollController = ScrollController();
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();

  ChatSession? _session;
  List<ChatMessage> _messages = [];
  bool _isLoading = true;
  bool _isSending = false;
  bool _isTyping = false;
  String? _error;
  String _view = 'start'; // start, chat
  Timer? _pollTimer;
  bool _showNamePrompt = false;

  @override
  void initState() {
    super.initState();
    _loadWidgetSettings();
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
    _messageController.dispose();
    _scrollController.dispose();
    _nameController.dispose();
    _emailController.dispose();
    super.dispose();
  }

  Future<void> _loadWidgetSettings() async {
    try {
      final service = ref.read(chatServiceProvider);
      // Try to get widget settings to test connectivity
      // If fails, it will fall back to mock mode in startSession
      // Just show the start view regardless
      if (mounted) {
        setState(() => _isLoading = false);
      }
    } catch (_) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _startChat({String? name, String? email}) async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final service = ref.read(chatServiceProvider);
      final session = await service.startSession(
        name: name ?? 'App User',
        email: email ?? '',
        phone: '',
        message: '',
        subject: 'Mobile App Chat',
      );

      if (mounted) {
        if (session != null) {
          setState(() {
            _session = session;
            _isLoading = false;
            _view = 'chat';
            _showNamePrompt = false;
          });
          _startPolling();
          // Initial poll right away to get welcome message
          _pollMessages();
        } else {
          setState(() {
            _error = 'Could not start chat. Please try again.';
            _isLoading = false;
          });
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = 'Connection failed. Please try again.';
          _isLoading = false;
        });
      }
    }
  }

  void _startPolling() {
    _pollTimer?.cancel();
    _pollTimer = Timer.periodic(const Duration(seconds: 5), (_) {
      _pollMessages();
    });
  }

  Future<void> _pollMessages() async {
    if (_session == null) return;

    try {
      final service = ref.read(chatServiceProvider);
      final lastId = _messages.isEmpty ? 0 : _messages.last.id;
      final result = await service.pollMessages(
        _session!.token,
        lastId: lastId,
      );

      if (!mounted) return;

      final rawMessages = result['messages'] as List<dynamic>? ?? [];
      if (rawMessages.isNotEmpty) {
        final newMessages = rawMessages
            .map(
              (m) =>
                  ChatMessage.fromJson(m as Map<String, dynamic>, isMe: false),
            )
            .toList();

        setState(() {
          _messages.addAll(newMessages);
          _isTyping = false;
        });
        _scrollToBottom();
      }

      // Update session status
      final status = result['status']?.toString();
      if (status != null && _session!.status != status) {
        setState(() {
          _session = _session!.copyWith(status: status);
        });
      }
    } catch (_) {
      // Silent fail on poll
    }
  }

  Future<void> _sendMessage() async {
    final text = _messageController.text.trim();
    if (text.isEmpty || _isSending || _session == null) return;

    setState(() {
      _isSending = true;
      // Optimistically add the message
      _messages.add(
        ChatMessage(
          id: DateTime.now().millisecondsSinceEpoch,
          text: text,
          senderType: 'visitor',
          senderName: 'You',
          isMe: true,
          createdAt: DateTime.now(),
        ),
      );
      _messageController.clear();
    });
    _scrollToBottom();

    // Send via API
    final service = ref.read(chatServiceProvider);
    final success = await service.sendMessage(_session!.token, text);

    if (mounted) {
      setState(() => _isSending = false);
      if (!success) {
        // Message was at least shown optimistically
        _pollMessages();
      }
      // Poll right away to get agent reply if mock mode
      Future.delayed(const Duration(seconds: 1), _pollMessages);
    }
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: _buildAppBar(),
      backgroundColor: Colors.grey[50],
      body: _buildBody(),
    );
  }

  PreferredSizeWidget _buildAppBar() {
    if (_view == 'start') {
      return AppBar(
        title: const Text('Live Chat'),
        backgroundColor: const Color(0xFF1A237E),
        foregroundColor: Colors.white,
        elevation: 0,
      );
    }

    final isClosed = _session?.status == 'closed';
    return AppBar(
      backgroundColor: const Color(0xFF1A237E),
      foregroundColor: Colors.white,
      elevation: 0,
      leading: IconButton(
        icon: const Icon(Icons.arrow_back),
        onPressed: () {
          _pollTimer?.cancel();
          setState(() {
            _view = 'start';
            _messages = [];
          });
        },
      ),
      title: Row(
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(20),
            ),
            child: const Icon(Icons.support_agent, color: Color(0xFF1A237E)),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  _session?.agentName ?? 'APS Support',
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                  overflow: TextOverflow.ellipsis,
                ),
                Text(
                  isClosed
                      ? 'Chat ended'
                      : _isTyping
                      ? 'Typing...'
                      : 'Online',
                  style: TextStyle(
                    fontSize: 12,
                    color: isClosed ? Colors.white54 : Colors.green.shade300,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
      actions: [
        IconButton(
          icon: const Icon(Icons.close),
          onPressed: _session?.status != 'closed'
              ? () {
                  _pollTimer?.cancel();
                  ScaffoldMessenger.of(
                    context,
                  ).showSnackBar(const SnackBar(content: Text('Chat ended')));
                  setState(() {
                    _session = _session!.copyWith(status: 'closed');
                    _view = 'start';
                    _messages = [];
                  });
                }
              : null,
        ),
      ],
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(
        child: CircularProgressIndicator(color: Color(0xFF1A237E)),
      );
    }

    if (_view == 'start') {
      return _buildStartView();
    }

    return _buildChatView();
  }

  Widget _buildStartView() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(24),
      child: Column(
        children: [
          const SizedBox(height: 40),
          // Hero illustration
          Container(
            width: 100,
            height: 100,
            decoration: BoxDecoration(
              color: const Color(0xFF1A237E).withValues(alpha: 0.1),
              shape: BoxShape.circle,
            ),
            child: const Icon(
              Icons.support_agent,
              size: 50,
              color: Color(0xFF1A237E),
            ),
          ),
          const SizedBox(height: 24),
          const Text(
            'Chat with Us',
            style: TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              color: Color(0xFF1A237E),
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Have a question? Our team is ready to help you with plots, pricing, site visits, and more.',
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 14,
              color: Colors.grey[600],
              height: 1.5,
            ),
          ),
          const SizedBox(height: 32),

          // Recent conversations (if any)
          if (_session != null && _messages.isNotEmpty) _buildRecentSession(),

          // Start chat button
          SizedBox(
            width: double.infinity,
            height: 52,
            child: ElevatedButton.icon(
              onPressed: () => _startChat(),
              icon: const Icon(Icons.chat),
              label: const Text(
                'Start New Chat',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF1A237E),
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
          ),
          const SizedBox(height: 16),

          // Error display
          if (_error != null)
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.red.shade50,
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: Colors.red.shade200),
              ),
              child: Row(
                children: [
                  Icon(
                    Icons.error_outline,
                    color: Colors.red.shade700,
                    size: 20,
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      _error!,
                      style: TextStyle(
                        color: Colors.red.shade700,
                        fontSize: 13,
                      ),
                    ),
                  ),
                ],
              ),
            ),

          const SizedBox(height: 24),
          // Quick info
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.grey[100],
              borderRadius: BorderRadius.circular(12),
            ),
            child: Column(
              children: [
                _infoRow(Icons.access_time, 'Typically replies in 5 minutes'),
                const SizedBox(height: 8),
                _infoRow(Icons.access_time_filled, 'Available 10 AM - 7 PM'),
                const SizedBox(height: 8),
                _infoRow(
                  Icons.headset_mic,
                  'Get help with bookings, payments & more',
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRecentSession() {
    final lastMsg = _messages.isNotEmpty ? _messages.last.text : '';
    final time = _messages.isNotEmpty
        ? DateFormat('MMM d, h:mm a').format(_messages.last.createdAt)
        : '';
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.blue.shade50,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.blue.shade200),
      ),
      child: InkWell(
        onTap: () {
          setState(() => _view = 'chat');
          _startPolling();
          _pollMessages();
        },
        borderRadius: BorderRadius.circular(12),
        child: Row(
          children: [
            Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                color: const Color(0xFF1A237E),
                borderRadius: BorderRadius.circular(12),
              ),
              child: const Icon(
                Icons.chat_bubble,
                color: Colors.white,
                size: 22,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Continue previous chat',
                    style: TextStyle(
                      fontWeight: FontWeight.w600,
                      color: Colors.blue.shade800,
                    ),
                  ),
                  if (lastMsg.isNotEmpty)
                    Text(
                      lastMsg,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.blue.shade600,
                      ),
                    ),
                  if (time.isNotEmpty)
                    Text(
                      time,
                      style: TextStyle(fontSize: 11, color: Colors.grey[500]),
                    ),
                ],
              ),
            ),
            Icon(Icons.chevron_right, color: Colors.blue.shade400),
          ],
        ),
      ),
    );
  }

  Widget _infoRow(IconData icon, String text) {
    return Row(
      children: [
        Icon(icon, size: 18, color: Colors.grey[500]),
        const SizedBox(width: 8),
        Expanded(
          child: Text(
            text,
            style: TextStyle(fontSize: 13, color: Colors.grey[600]),
          ),
        ),
      ],
    );
  }

  Widget _buildChatView() {
    return Column(
      children: [
        // Messages list
        Expanded(
          child: _messages.isEmpty
              ? _buildEmptyChat()
              : ListView.builder(
                  controller: _scrollController,
                  padding: const EdgeInsets.all(16),
                  itemCount: _messages.length,
                  itemBuilder: (_, i) => _buildMessageBubble(_messages[i]),
                ),
        ),

        // Typing indicator
        if (_isTyping)
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            alignment: Alignment.centerLeft,
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: Colors.grey.shade200,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [_buildDot(0), _buildDot(1), _buildDot(2)],
                  ),
                ),
              ],
            ),
          ),

        // Input area
        _buildInputArea(),
      ],
    );
  }

  Widget _buildEmptyChat() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.chat_bubble_outline, size: 64, color: Colors.grey[300]),
          const SizedBox(height: 16),
          Text(
            'Start the conversation',
            style: TextStyle(
              fontSize: 16,
              color: Colors.grey[500],
              fontWeight: FontWeight.w500,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Type a message below to get started',
            style: TextStyle(fontSize: 13, color: Colors.grey[400]),
          ),
        ],
      ),
    );
  }

  Widget _buildMessageBubble(ChatMessage message) {
    return Align(
      alignment: message.isMe ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(
        margin: const EdgeInsets.only(bottom: 12),
        constraints: BoxConstraints(
          maxWidth: MediaQuery.of(context).size.width * 0.75,
        ),
        child: Column(
          crossAxisAlignment: message.isMe
              ? CrossAxisAlignment.end
              : CrossAxisAlignment.start,
          children: [
            if (!message.isMe && message.senderName.isNotEmpty)
              Padding(
                padding: const EdgeInsets.only(left: 4, bottom: 2),
                child: Text(
                  message.senderName,
                  style: TextStyle(
                    fontSize: 11,
                    color: Colors.grey[500],
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              decoration: BoxDecoration(
                color: message.isMe ? const Color(0xFF1A237E) : Colors.white,
                borderRadius: BorderRadius.only(
                  topLeft: const Radius.circular(16),
                  topRight: const Radius.circular(16),
                  bottomLeft: Radius.circular(message.isMe ? 16 : 4),
                  bottomRight: Radius.circular(message.isMe ? 4 : 16),
                ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.04),
                    blurRadius: 4,
                    offset: const Offset(0, 1),
                  ),
                ],
              ),
              child: Text(
                message.text,
                style: TextStyle(
                  color: message.isMe ? Colors.white : Colors.black87,
                  fontSize: 14,
                  height: 1.4,
                ),
              ),
            ),
            const SizedBox(height: 2),
            Text(
              DateFormat('h:mm a').format(message.createdAt),
              style: TextStyle(fontSize: 11, color: Colors.grey[500]),
            ),
          ],
        ),
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
        color: Colors.grey.shade600,
        borderRadius: BorderRadius.circular(4),
      ),
    );
  }

  Widget _buildInputArea() {
    final isClosed = _session?.status == 'closed';
    return Container(
      padding: const EdgeInsets.all(12),
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
        child: isClosed
            ? const Center(
                child: Text(
                  'This chat has ended. Start a new chat for further assistance.',
                  style: TextStyle(color: Colors.grey, fontSize: 13),
                ),
              )
            : Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: _messageController,
                      decoration: InputDecoration(
                        hintText: 'Type a message...',
                        filled: true,
                        fillColor: Colors.grey.shade100,
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(24),
                          borderSide: BorderSide.none,
                        ),
                        contentPadding: const EdgeInsets.symmetric(
                          horizontal: 20,
                          vertical: 14,
                        ),
                      ),
                      textInputAction: TextInputAction.send,
                      onSubmitted: (_) => _sendMessage(),
                    ),
                  ),
                  const SizedBox(width: 8),
                  GestureDetector(
                    onTap: _isSending ? null : _sendMessage,
                    child: Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: _isSending
                            ? Colors.grey
                            : const Color(0xFF1A237E),
                        borderRadius: BorderRadius.circular(24),
                      ),
                      child: _isSending
                          ? const SizedBox(
                              width: 20,
                              height: 20,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                color: Colors.white,
                              ),
                            )
                          : const Icon(Icons.send, color: Colors.white),
                    ),
                  ),
                ],
              ),
      ),
    );
  }
}

import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/services/api_service.dart';
import '../../../core/utils/logger.dart';

class RAGChatPage extends ConsumerStatefulWidget {
  const RAGChatPage({super.key});

  @override
  ConsumerState<RAGChatPage> createState() => _RAGChatPageState();
}

class _RAGChatPageState extends ConsumerState<RAGChatPage> {
  final TextEditingController _messageController = TextEditingController();
  final ScrollController _scrollController = ScrollController();

  bool _isLoading = false;
  final List<_ChatMessage> _messages = [];

  @override
  void initState() {
    super.initState();
    _addBotMessage(
      'Hello! I am your APS Dream Home AI assistant. I can answer questions about our colonies, properties, pricing, and more based on our knowledge base. How can I help you?',
    );
  }

  @override
  void dispose() {
    _messageController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  void _addBotMessage(String text, {bool isTyping = false}) {
    setState(() {
      _messages.add(_ChatMessage(
        text: text,
        isBot: true,
        isTyping: isTyping,
        timestamp: DateTime.now(),
      ));
    });
    _scrollToBottom();
  }

  void _addUserMessage(String text) {
    setState(() {
      _messages.add(_ChatMessage(
        text: text,
        isBot: false,
        timestamp: DateTime.now(),
      ));
    });
    _scrollToBottom();
  }

  Future<void> _sendMessage() async {
    final text = _messageController.text.trim();
    if (text.isEmpty || _isLoading) return;

    _messageController.clear();
    _addUserMessage(text);
    _addBotMessage('', isTyping: true);

    setState(() => _isLoading = true);

    try {
      final api = ApiService();
      final response = await api.post('/api/ai/chat', data: {
        'messages': [
          {'role': 'user', 'content': text},
        ],
        'use_rag': true,
      });

      if (mounted) {
        setState(() {
          if (_messages.isNotEmpty && _messages.last.isTyping) {
            _messages.removeLast();
          }
        });

        final data = response['data'] ?? {};
        if (response['success'] == true) {
          final reply = data['response'] as String? ?? 'No response received';
          if (mounted) _addBotMessage(reply);
        } else {
          final err = data['error'] as String?;
          if (mounted)
            _addBotMessage(
              'Sorry, I encountered an error: ${err ?? 'Unknown error'}',
            );
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          if (_messages.isNotEmpty && _messages.last.isTyping) {
            _messages.removeLast();
          }
        });
        _addBotMessage('Sorry, I encountered an error: $e');
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _scrollToBottom() {
    if (_scrollController.hasClients) {
      _scrollController.animateTo(
        _scrollController.position.maxScrollExtent,
        duration: const Duration(milliseconds: 300),
        curve: Curves.easeOut,
      );
    }
  }

  void _clearChat() {
    setState(() {
      _messages.clear();
    });
    _addBotMessage(
      'Hello! I am your APS Dream Home AI assistant. How can I help you?',
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F0),
      appBar: AppBar(
        title: const Text('RAG AI Assistant'),
        foregroundColor: const Color(0xFF0B194C),
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.delete, color: Colors.red),
            onPressed: _clearChat,
            tooltip: 'Clear chat',
          ),
        ],
      ),
      body: Column(
        children: [
          Expanded(
            child: _isLoading && _messages.isEmpty
                ? const Center(child: CircularProgressIndicator())
                : ListView.builder(
                    controller: _scrollController,
                    padding: const EdgeInsets.all(16),
                    itemCount: _messages.length,
                    itemBuilder: (context, index) {
                      final msg = _messages[index];
                      return _buildMessageBubble(msg);
                    },
                  ),
          ),
          _buildInputArea(),
        ],
      ),
    );
  }

  Widget _buildMessageBubble(_ChatMessage msg) {
    return Align(
      alignment: msg.isBot ? Alignment.centerLeft : Alignment.centerRight,
      child: Container(
        margin: const EdgeInsets.symmetric(vertical: 4, horizontal: 8),
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: msg.isBot ? Colors.white : const Color(0xFF0B194C),
          borderRadius: BorderRadius.circular(16),
        ),
        child: Column(
          crossAxisAlignment:
              msg.isBot ? CrossAxisAlignment.start : CrossAxisAlignment.end,
          children: [
            if (!msg.isBot)
              const SizedBox(height: 4),
            Text(
              msg.text,
              style: TextStyle(
                color: msg.isBot ? Colors.black87 : Colors.white,
                fontSize: 14,
              ),
            ),
            if (msg.isTyping) ...[
              const SizedBox(height: 8),
              Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text('Typing...',
                      style: TextStyle(
                          fontSize: 12, color: Colors.grey.shade600)),
                  const SizedBox(width: 8),
                  SizedBox(
                    height: 12,
                    width: 12,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  ),
                ],
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildInputArea() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border(top: BorderSide(color: Colors.grey.shade200)),
      ),
      child: SafeArea(
        child: Row(
          children: [
            Expanded(
              child: TextField(
                controller: _messageController,
                decoration: InputDecoration(
                  hintText: 'Ask about colonies, properties, pricing...',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(24),
                    borderSide: BorderSide(color: Colors.grey.shade300),
                  ),
                  filled: true,
                  fillColor: Colors.grey.shade50,
                  contentPadding: const EdgeInsets.symmetric(horizontal: 20),
                ),
                onSubmitted: (_) => _sendMessage(),
              ),
            ),
            const SizedBox(width: 8),
            CircleAvatar(
              backgroundColor: const Color(0xFF0B194C),
              child: IconButton(
                icon: const Icon(Icons.send, color: Colors.white),
                onPressed: _isLoading ? null : _sendMessage,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ChatMessage {
  final String text;
  final bool isBot;
  final bool isTyping;
  final DateTime timestamp;

  _ChatMessage({
    required this.text,
    required this.isBot,
    this.isTyping = false,
    required this.timestamp,
  });
}

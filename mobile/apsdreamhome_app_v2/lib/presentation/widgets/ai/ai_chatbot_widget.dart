import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:image_picker/image_picker.dart';

import '../../../core/utils/logger.dart';
import '../../../data/repositories/kyc_repository_provider.dart';

/// Floating AI Chatbot Widget
class AIChatbotWidget extends ConsumerStatefulWidget {
  final bool isMini;
  final String? initialMessage;
  final bool showPhotoButton;
  final bool showVoiceButton;

  const AIChatbotWidget({
    super.key,
    this.isMini = false,
    this.initialMessage,
    this.showPhotoButton = true,
    this.showVoiceButton = true,
  });

  @override
  ConsumerState<AIChatbotWidget> createState() => _AIChatbotWidgetState();
}

class _AIChatbotWidgetState extends ConsumerState<AIChatbotWidget> {
  bool _isOpen = false;
  bool _isLoading = false;
  final TextEditingController _messageController = TextEditingController();
  final ScrollController _scrollController = ScrollController();
  final List<_ChatMessage> _messages = [];
  final String _selectedProvider = 'groq';

  @override
  void initState() {
    super.initState();
    if (widget.initialMessage != null) {
      _messages.add(_ChatMessage(
        role: 'assistant',
        content: widget.initialMessage!,
        timestamp: DateTime.now(),
      ));
    }
  }

  @override
  void dispose() {
    _messageController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _sendMessage({String? imagePath}) async {
    final message = _messageController.text.trim();
    if (message.isEmpty && imagePath == null) return;
    if (_isLoading) return;

    setState(() {
      _messages.add(_ChatMessage(
        role: 'user',
        content: message,
        timestamp: DateTime.now(),
        hasImage: imagePath != null,
        imagePath: imagePath,
      ));
      _isLoading = true;
    });
    _messageController.clear();
    _scrollToBottom();

    try {
      final apiService = ref.read(apiServiceProvider);
      final requestData = {
        'messages': _messages.map((m) => {'role': m.role, 'content': m.content}).toList(),
        'provider': _selectedProvider,
        'use_rag': true,
        'temperature': 0.7,
        'max_tokens': 1000,
        'image_path': imagePath,
      };
      final response = await apiService.post('/api/ai/chat', data: requestData);

      if (response['success'] == true) {
        final dataMap = (response['data'] is Map)
            ? Map<String, dynamic>.from(response['data'] as Map)
            : <String, dynamic>{};
        setState(() {
          final rawMsg = dataMap['message'];
          _messages.add(_ChatMessage(
            role: 'assistant',
            content: (rawMsg is String && rawMsg.isNotEmpty)
                ? rawMsg
                : (response['message'] as String),
            timestamp: DateTime.now(),
            provider: dataMap['provider'] as String?,
            model: dataMap['model'] as String?,
          ));
        });
      } else {
        _addErrorMessage(response['message'] as String? ?? 'Chat failed');
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

  Future<void> _pickImage() async {
    final picker = ImagePicker();
    final pickedFile = await picker.pickImage(source: ImageSource.gallery, maxWidth: 1024, maxHeight: 1024, imageQuality: 80);
    if (pickedFile == null) return;

    final bytes = await File(pickedFile.path).readAsBytes();
    final base64Image = base64Encode(bytes);
    final mimeType = pickedFile.path.endsWith('.png') ? 'image/png' : 'image/jpeg';

    setState(() {
      _messages.add(_ChatMessage(
        role: 'user',
        content: 'Analyze this image',
        timestamp: DateTime.now(),
        hasImage: true,
        imagePath: pickedFile.path,
      ));
      _isLoading = true;
    });
    _scrollToBottom();

    try {
      final apiService = ref.read(apiServiceProvider);
      final requestData = {
        'messages': _messages.map((m) => {'role': m.role, 'content': m.content}).toList(),
        'provider': _selectedProvider,
        'use_rag': true,
        'temperature': 0.7,
        'max_tokens': 1000,
        'image_base64': base64Image,
        'image_mime': mimeType,
      };
      final response = await apiService.post('/api/ai/chat', data: requestData);

      if (response['success'] == true) {
        final dataMap = (response['data'] is Map)
            ? Map<String, dynamic>.from(response['data'] as Map)
            : <String, dynamic>{};
        setState(() {
          final rawMsg = dataMap['message'];
          _messages.add(_ChatMessage(
            role: 'assistant',
            content: (rawMsg is String && rawMsg.isNotEmpty)
                ? rawMsg
                : (response['message'] as String),
            timestamp: DateTime.now(),
            provider: dataMap['provider'] as String?,
            model: dataMap['model'] as String?,
          ));
        });
      } else {
        _addErrorMessage(response['message'] as String? ?? 'Image analysis failed');
      }
    } catch (e) {
      AppLogger.error('AI image analysis error', e);
      _addErrorMessage('Connection error. Please try again.');
    } finally {
      setState(() => _isLoading = false);
      _scrollToBottom();
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isOpen) {
      return _buildChatPanel();
    }

    return _buildFloatingButton();
  }

  Widget _buildFloatingButton() {
    return GestureDetector(
      onTap: () => setState(() => _isOpen = true),
      child: Container(
        width: widget.isMini ? 48 : 64,
        height: widget.isMini ? 48 : 64,
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: widget.isMini
                ? const [Color(0xFF7C3AED), Color(0xFF9333EA)]
                : const [Color(0xFF4285F4), Color(0xFF34A853)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(widget.isMini ? 24 : 32),
          boxShadow: [
            BoxShadow(
              color: (widget.isMini
                      ? const Color(0xFF7C3AED)
                      : const Color(0xFF4285F4))
                  .withValues(alpha: 0.4),
              blurRadius: 12,
              spreadRadius: 2,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Center(
          child: Icon(
            widget.isMini ? Icons.auto_awesome : Icons.chat_bubble,
            color: Colors.white,
            size: widget.isMini ? 22 : 28,
          ),
        ),
      ),
    );
  }

  Widget _buildChatPanel() {
    return Material(
      elevation: 8,
      borderRadius: BorderRadius.circular(16),
      child: Container(
        width: 320,
        height: 400,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
        ),
        child: Column(
          children: [
            // Header
            Container(
              padding: const EdgeInsets.all(12),
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  colors: [Color(0xFF4285F4), Color(0xFF34A853)],
                ),
                borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.auto_awesome, color: Colors.white),
                  const SizedBox(width: 8),
                  const Expanded(
                    child: Text(
                      'APS AI Assistant',
                      style: TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close, color: Colors.white, size: 20),
                    onPressed: () => setState(() => _isOpen = false),
                  ),
                ],
              ),
            ),
            // Messages
            Expanded(
              child: ListView.builder(
                controller: _scrollController,
                padding: const EdgeInsets.all(12),
                itemCount: _messages.length,
                itemBuilder: (context, index) {
                  final message = _messages[index];
                  return _buildMessageBubble(message);
                },
              ),
            ),
            // Input
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                border: Border(top: BorderSide(color: Colors.grey.shade200)),
              ),
              child: Row(
                children: [
                  if (widget.showPhotoButton)
                    IconButton(
                      icon: const Icon(Icons.camera_alt, size: 20),
                      onPressed: _pickImage,
                      tooltip: 'Upload photo for AI analysis',
                    ),
                  Expanded(
                    child: TextField(
                      controller: _messageController,
                      decoration: InputDecoration(
                        hintText: 'Ask me anything...',
                        filled: true,
                        fillColor: Colors.grey.shade100,
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(20),
                          borderSide: BorderSide.none,
                        ),
                        contentPadding: const EdgeInsets.symmetric(
                          horizontal: 16,
                          vertical: 12,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  GestureDetector(
                    onTap: () {
                      if (_messageController.text.isNotEmpty) {
                        _sendMessage();
                      }
                    },
                    child: Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [Color(0xFF4285F4), Color(0xFF34A853)],
                        ),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: const Icon(Icons.send, color: Colors.white, size: 20),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMessageBubble(_ChatMessage message) {
    final isUser = message.role == 'user';
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        mainAxisAlignment: isUser ? MainAxisAlignment.end : MainAxisAlignment.start,
        crossAxisAlignment: CrossAxisAlignment.end,
        children: [
          if (!isUser)
            Container(
              width: 28,
              height: 28,
              decoration: BoxDecoration(
                color: const Color(0xFF0B194C).withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(14),
              ),
              child: const Center(
                child: Icon(
                  Icons.smart_toy_rounded,
                  size: 14,
                  color: Color(0xFF0B194C),
                ),
              ),
            ),
          if (!isUser) const SizedBox(width: 8),
          Flexible(
            child: Container(
              decoration: BoxDecoration(
                color: isUser ? const Color(0xFF0B194C) : Colors.grey.shade100,
                borderRadius: BorderRadius.circular(12),
              ),
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              child: Text(
                message.content,
                style: TextStyle(
                  fontSize: 13,
                  color: isUser ? Colors.white : Colors.black87,
                ),
              ),
            ),
          ),
          if (isUser) const SizedBox(width: 8),
          if (isUser)
            Container(
              width: 28,
              height: 28,
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFFF58220), Color(0xFFE5A93C)],
                ),
                borderRadius: BorderRadius.circular(14),
              ),
              child: const Center(
                child: Icon(
                  Icons.person,
                  size: 14,
                  color: Colors.white,
                ),
              ),
            ),
        ],
      ),
    );
  }
}

class _ChatMessage {
  final String role;
  final String content;
  final DateTime timestamp;
  final bool hasImage;
  final String? imagePath;
  final bool isError;
  final String? provider;
  final String? model;

  _ChatMessage({
    required this.role,
    required this.content,
    required this.timestamp,
    this.hasImage = false,
    this.imagePath,
    this.isError = false,
    this.provider,
    this.model,
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
}
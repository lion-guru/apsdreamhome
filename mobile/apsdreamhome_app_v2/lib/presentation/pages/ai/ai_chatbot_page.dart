import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import 'package:speech_to_text/speech_to_text.dart' as stt;
import 'package:flutter_tts/flutter_tts.dart';

import '../../../core/utils/logger.dart';
import '../../../data/repositories/kyc_repository_provider.dart';

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

  // Voice: speech-to-text (mic) + text-to-speech (speaker)
  final stt.SpeechToText _speech = stt.SpeechToText();
  final FlutterTts _tts = FlutterTts();
  bool _isListening = false;
  bool _voiceEnabled = true;

  static const Color primaryColor = Color(0xFF0B194C);

  @override
  void initState() {
    super.initState();
    _loadProviders();
    _messages.add(_ChatMessage(
      role: 'assistant',
      content: 'Hello! I\'m your APS Dream Home AI assistant. Ask me about plots, bookings, EMI, site visits, or anything else!',
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

  Future<void> _loadProviders() async {
    try {
      final apiService = ref.read(apiServiceProvider);
      final response = await apiService.get('/ai-agent/providers');
      if (response['success'] == true) {
        // Could update provider list here
      }
    } catch (e) {
      AppLogger.warning('Failed to load providers: $e');
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
            RadioGroup<String>(
              groupValue: _selectedProvider,
              onChanged: (v) => setState(() => _selectedProvider = v!),
              child: Column(
                children: [
                  ...['groq', 'openrouter', 'gemini', 'ollama'].map((p) {
                    final labels = {
                      'groq': 'Groq (Llama 3.1 - Fast)',
                      'openrouter': 'OpenRouter (Gemini/Claude)',
                      'gemini': 'Gemini (Google)',
                      'ollama': 'Ollama (Local)',
                    };
                    return ListTile(
                      leading: Radio<String>(value: p),
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
          ],
        ),
      ),
    );
  }

  Future<void> _sendMessage() async {
    final message = _messageController.text.trim();
    if (message.isEmpty || _isLoading) return;

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
      final requestData = {
        'messages': _messages.map((m) => {'role': m.role, 'content': m.content}).toList(),
        'provider': _selectedProvider,
        'use_rag': true,
        'temperature': 0.7,
        'max_tokens': 1000,
      };
      final response = await apiService.post('/ai-agent/chat', data: requestData);

      if (response['success'] == true) {
        final dataMap = (response['data'] is Map)
            ? Map<String, dynamic>.from(response['data'] as Map)
            : <String, dynamic>{};
        final rawMsg = dataMap['message'];
        final assistantText = (rawMsg is String && rawMsg.isNotEmpty)
            ? rawMsg
            : (response['message'] as String);
        setState(() {
          _messages.add(_ChatMessage(
            role: 'assistant',
            content: assistantText,
            timestamp: DateTime.now(),
            provider: dataMap['provider'] as String?,
            model: dataMap['model'] as String?,
          ));
        });
        if (_voiceEnabled) {
          _speakResponse(assistantText);
        }
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

  Future<void> _toggleVoice() async {
    if (_isListening) {
      await _speech.stop();
      if (mounted) setState(() => _isListening = false);
      return;
    }
    final available = await _speech.initialize(
      onStatus: (status) {
        if (status == 'done' || status == 'notListening') {
          if (mounted) setState(() => _isListening = false);
        }
      },
      onError: (error) {
        if (mounted) setState(() => _isListening = false);
      },
    );
    if (!available) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Voice input not available')),
        );
      }
      return;
    }
    if (mounted) setState(() => _isListening = true);
    await _speech.listen(
      onResult: (result) {
        if (result.finalResult && result.recognizedWords.isNotEmpty) {
          _messageController.text = result.recognizedWords;
          _sendMessage();
        }
      },
      listenOptions: stt.SpeechListenOptions(
        listenFor: const Duration(seconds: 30),
        localeId: 'hi_IN',
      ),
    );
  }

  Future<void> _speakResponse(String text) async {
    if (!_voiceEnabled || text.isEmpty) return;
    try {
      await _tts.setLanguage('hi-IN');
      await _tts.setSpeechRate(0.5);
      await _tts.speak(text);
    } catch (e) {
      AppLogger.error('TTS failed', e);
    }
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
        'image_base64': base64Image,
        'image_mime': mimeType,
      };
      final response = await apiService.post('/ai-agent/chat', data: requestData);

      if (response['success'] == true) {
        final dataMap = (response['data'] is Map)
            ? Map<String, dynamic>.from(response['data'] as Map)
            : <String, dynamic>{};
        final rawMsg = dataMap['message'];
        final assistantText = (rawMsg is String && rawMsg.isNotEmpty)
            ? rawMsg
            : (response['message'] as String);
        setState(() {
          _messages.add(_ChatMessage(
            role: 'assistant',
            content: assistantText,
            timestamp: DateTime.now(),
            provider: dataMap['provider'] as String?,
            model: dataMap['model'] as String?,
          ));
        });
        if (_voiceEnabled) {
          _speakResponse(assistantText);
        }
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
          ],
        ),
        actions: [
          IconButton(
            icon: Icon(_voiceEnabled ? Icons.volume_up_outlined : Icons.volume_off_outlined),
            onPressed: () {
              setState(() => _voiceEnabled = !_voiceEnabled);
              if (!_voiceEnabled) {
                _tts.stop();
              }
            },
            tooltip: _voiceEnabled ? 'Voice responses on' : 'Voice responses off',
          ),
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
                  'Provider: ${_selectedProvider.toUpperCase()} ${_isLoading ? 'â¢ Thinking...' : 'â¢ Ready'}',
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
                IconButton(
                  icon: Icon(_isListening ? Icons.mic : Icons.mic_none, size: 20),
                  onPressed: _isLoading ? null : _toggleVoice,
                  tooltip: _isListening ? 'Stop voice input' : 'Voice input (Hindi)',
                  style: IconButton.styleFrom(
                    backgroundColor: _isListening ? Colors.red.shade100 : Colors.grey.shade100,
                    foregroundColor: _isListening ? Colors.red : const Color(0xFF0B194C),
                  ),
                ),
                const SizedBox(width: 8),
                IconButton(
                  icon: const Icon(Icons.camera_alt_outlined, size: 20),
                  onPressed: _isLoading ? null : _pickImage,
                  tooltip: 'Upload image for AI analysis',
                  style: IconButton.styleFrom(
                    backgroundColor: Colors.grey.shade100,
                    foregroundColor: const Color(0xFF0B194C),
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
                            ' ï¿½ ',
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
      ),
    );
  }
}

class _ChatMessage {
  final String role;
  final String content;
  final DateTime timestamp;
  final String? provider;
  final String? model;
  final bool isError;
  final bool hasImage;
  final String? imagePath;

  _ChatMessage({
    required this.role,
    required this.content,
    required this.timestamp,
    this.provider,
    this.model,
    this.isError = false,
    this.hasImage = false,
    this.imagePath,
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
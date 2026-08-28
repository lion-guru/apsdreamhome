import 'dart:developer' as developer;
import 'package:flutter/material.dart';
import 'package:flutter_tts/flutter_tts.dart';
import 'package:speech_to_text/speech_to_text.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/services/auto_dialer_service.dart';

class VoiceCallPage extends StatefulWidget {
  final int? leadId;
  const VoiceCallPage({super.key, this.leadId});

  @override
  State<VoiceCallPage> createState() => _VoiceCallPageState();
}

class _VoiceCallPageState extends State<VoiceCallPage> {
  final AutoDialerService _service = AutoDialerService();
  final SpeechToText _speech = SpeechToText();
  final FlutterTts _tts = FlutterTts();
  final List<_ChatTurn> _messages = [];
  final ScrollController _scrollController = ScrollController();

  bool _speechEnabled = false;
  bool _isListening = false;
  bool _isThinking = false;
  int? _sessionId;
  String _localeId = 'hi-IN';

  @override
  void initState() {
    super.initState();
    _initSpeech();
    _initTts();
    _addBot(
      'नमस्ते! मैं आपका AI एजेंट हूँ। बोलकर अपनी बात बताइए — प्लॉट, प्राइस, या साइट विजिट के बारे में।',
    );
  }

  Future<void> _initSpeech() async {
    try {
      _speechEnabled = await _speech.initialize(
        onStatus: (status) {
          if (status == 'notListening' && mounted) {
            setState(() => _isListening = false);
          }
        },
        onError: (error) {
          developer.log('Speech error: $error', name: 'VoiceCall');
          if (mounted) setState(() => _isListening = false);
        },
      );
      // Pick Hindi locale if available
      final locales = await _speech.locales();
      final hi = locales.where((l) => l.localeId.startsWith('hi')).toList();
      if (hi.isNotEmpty) _localeId = hi.first.localeId;
      if (mounted) setState(() {});
    } catch (e) {
      _speechEnabled = false;
    }
  }

  Future<void> _initTts() async {
    await _tts.setLanguage(_localeId);
    await _tts.setPitch(1.0);
    await _tts.setSpeechRate(0.95);
  }

  void _addUser(String text) {
    setState(() => _messages.add(_ChatTurn(text: text, isUser: true)));
    _scrollToBottom();
  }

  void _addBot(String text) {
    setState(() => _messages.add(_ChatTurn(text: text, isUser: false)));
    _scrollToBottom();
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    });
  }

  Future<void> _toggleListen() async {
    if (!_speechEnabled) {
      _initSpeech();
      if (!_speechEnabled) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('माइक्रोफोन की अनुमति नहीं है।')),
          );
        }
        return;
      }
    }

    if (_isListening) {
      await _speech.stop();
      setState(() => _isListening = false);
      return;
    }

    setState(() => _isListening = true);
    await _speech.listen(
      listenOptions: SpeechListenOptions(localeId: _localeId),
      onResult: (result) {
        if (result.finalResult && result.recognizedWords.isNotEmpty) {
          _handleSpoken(result.recognizedWords);
        }
      },
    );
  }

  Future<void> _handleSpoken(String text) async {
    _addUser(text);
    await _speech.stop();
    if (mounted) setState(() => _isListening = false);

    if (mounted) setState(() => _isThinking = true);
    final resp = await _service.voiceChat(
      message: text,
      sessionId: _sessionId,
      leadId: widget.leadId,
    );
    if (mounted) setState(() => _isThinking = false);

    if (resp['success'] == true) {
      final dynamic rawSession = resp['session_id'];
      _sessionId = rawSession is int
          ? rawSession
          : int.tryParse(rawSession?.toString() ?? '');
      final reply = resp['reply']?.toString() ?? '';
      if (reply.isNotEmpty) {
        _addBot(reply);
        await _tts.speak(reply);
      }
    } else {
      _addBot('माफ़ कीजिए, कनेक्शन में समस्या है। कृपया दोबारा कोशिश करें।');
    }
  }

  @override
  void dispose() {
    _speech.stop();
    _tts.stop();
    _scrollController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0D1B2A),
      appBar: AppBar(
        backgroundColor: const Color(0xFF0D1B2A),
        elevation: 0,
        title: const Text(
          'AI Voice Agent',
          style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
        ),
        iconTheme: const IconThemeData(color: Colors.white),
        actions: [
          IconButton(
            icon: const Icon(Icons.volume_up, color: Colors.white70),
            onPressed: () {
              final last = _messages.where((m) => !m.isUser).toList();
              if (last.isNotEmpty) _tts.speak(last.last.text);
            },
            tooltip: 'Replay',
          ),
        ],
      ),
      body: Column(
        children: [
          Expanded(
            child: ListView.builder(
              controller: _scrollController,
              padding: const EdgeInsets.all(16),
              itemCount: _messages.length + (_isThinking ? 1 : 0),
              itemBuilder: (context, index) {
                if (_isThinking && index == _messages.length) {
                  return const _TypingBubble();
                }
                final m = _messages[index];
                return _MessageBubble(turn: m);
              },
            ),
          ),
          _buildComposer(),
        ],
      ),
    );
  }

  Widget _buildComposer() {
    return Container(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 24),
      decoration: BoxDecoration(
        color: const Color(0xFF1B2838),
        borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.3),
            blurRadius: 10,
            offset: const Offset(0, -3),
          ),
        ],
      ),
      child: Row(
        children: [
          Expanded(
            child: Text(
              _isListening
                  ? 'सुन रहा हूँ… बोलिए'
                  : _speechEnabled
                  ? 'माइक दबाकर बात करें'
                  : 'माइक्रोफोन उपलब्ध नहीं',
              style: TextStyle(
                color: _isListening ? AppConstants.accentColor : Colors.white70,
              ),
            ),
          ),
          GestureDetector(
            onTap: _toggleListen,
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 200),
              width: 64,
              height: 64,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                gradient: LinearGradient(
                  colors: _isListening
                      ? [Colors.redAccent, Colors.red]
                      : [AppConstants.primaryColor, Colors.indigoAccent],
                ),
                boxShadow: [
                  BoxShadow(
                    color:
                        (_isListening ? Colors.red : AppConstants.primaryColor)
                            .withValues(alpha: 0.5),
                    blurRadius: 14,
                    spreadRadius: _isListening ? 4 : 1,
                  ),
                ],
              ),
              child: Icon(
                _isListening ? Icons.mic : Icons.mic_none,
                color: Colors.white,
                size: 30,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _ChatTurn {
  final String text;
  final bool isUser;
  _ChatTurn({required this.text, required this.isUser});
}

class _MessageBubble extends StatelessWidget {
  final _ChatTurn turn;
  const _MessageBubble({required this.turn});

  @override
  Widget build(BuildContext context) {
    final isUser = turn.isUser;
    return Align(
      alignment: isUser ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(
        margin: const EdgeInsets.symmetric(vertical: 6),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        constraints: BoxConstraints(
          maxWidth: MediaQuery.of(context).size.width * 0.78,
        ),
        decoration: BoxDecoration(
          color: isUser ? AppConstants.primaryColor : const Color(0xFF243447),
          borderRadius: BorderRadius.only(
            topLeft: const Radius.circular(16),
            topRight: const Radius.circular(16),
            bottomLeft: Radius.circular(isUser ? 16 : 4),
            bottomRight: Radius.circular(isUser ? 4 : 16),
          ),
        ),
        child: Text(
          turn.text,
          style: const TextStyle(
            color: Colors.white,
            fontSize: 15,
            height: 1.4,
          ),
        ),
      ),
    );
  }
}

class _TypingBubble extends StatelessWidget {
  const _TypingBubble();

  @override
  Widget build(BuildContext context) {
    return Align(
      alignment: Alignment.centerLeft,
      child: Container(
        margin: const EdgeInsets.symmetric(vertical: 6),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: const Color(0xFF243447),
          borderRadius: BorderRadius.circular(16),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: List.generate(
            3,
            (i) => Container(
              margin: const EdgeInsets.symmetric(horizontal: 2),
              width: 8,
              height: 8,
              decoration: const BoxDecoration(
                color: Colors.white54,
                shape: BoxShape.circle,
              ),
            ),
          ),
        ),
      ),
    );
  }
}

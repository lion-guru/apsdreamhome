import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../../core/services/api_service.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/providers/auth_provider.dart';

class ChatDetailPage extends ConsumerStatefulWidget {
  final int otherUserId;
  final String otherUserName;
  final String otherUserRole;

  const ChatDetailPage({
    super.key,
    required this.otherUserId,
    required this.otherUserName,
    this.otherUserRole = '',
  });

  @override
  ConsumerState<ChatDetailPage> createState() => _ChatDetailPageState();
}

class _ChatDetailPageState extends ConsumerState<ChatDetailPage> {
  final ApiService _api = ApiService();
  final TextEditingController _messageController = TextEditingController();
  final ScrollController _scrollController = ScrollController();
  List<Map<String, dynamic>> _messages = [];
  bool _isLoading = true;
  bool _isSending = false;
  String? _error;
  Timer? _pollTimer;

  @override
  void initState() {
    super.initState();
    _fetchMessages();
    _markAsRead();
    _pollTimer = Timer.periodic(const Duration(seconds: 5), (_) {
      _fetchMessagesSilent();
    });
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
    _messageController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _fetchMessages() async {
    try {
      final res = await _api.get(
        '${AppConstants.messagesEndpoint}/${widget.otherUserId}',
      );
      if (res['success'] == true && res['data'] != null) {
        _messages = (res['data'] as List).cast<Map<String, dynamic>>();
      }
      _error = null;
    } catch (e) {
      _error = e.toString();
    }
    if (mounted) setState(() => _isLoading = false);
    _scrollToBottom();
  }

  Future<void> _fetchMessagesSilent() async {
    try {
      final res = await _api.get(
        '${AppConstants.messagesEndpoint}/${widget.otherUserId}',
      );
      if (res['success'] == true && res['data'] != null) {
        final newMessages = (res['data'] as List).cast<Map<String, dynamic>>();
        if (newMessages.length != _messages.length) {
          if (mounted) setState(() => _messages = newMessages);
          _scrollToBottom();
        }
      }
    } catch (_) {}
  }

  Future<void> _markAsRead() async {
    try {
      await _api.post('${AppConstants.markReadEndpoint}/${widget.otherUserId}');
    } catch (_) {}
  }

  Future<void> _sendMessage() async {
    final text = _messageController.text.trim();
    if (text.isEmpty || _isSending) return;

    setState(() => _isSending = true);
    _messageController.clear();

    try {
      final res = await _api.post(
        AppConstants.sendMessageEndpoint,
        data: {'receiver_id': widget.otherUserId, 'message': text},
      );
      if (res['success'] == true && res['data'] != null) {
        _messages.add((res['data'] as Map<String, dynamic>));
        if (mounted) setState(() {});
      }
    } catch (e) {
      _messages.add({
        'id': -DateTime.now().millisecondsSinceEpoch,
        'sender_id': 0,
        'receiver_id': widget.otherUserId,
        'message': text,
        'sender_name': 'You',
        'created_at': DateTime.now().toIso8601String(),
        '_failed': true,
      });
      if (mounted) setState(() {});
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Failed to send. Tap to retry.')),
        );
      }
    }

    _isSending = false;
    _scrollToBottom();
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

  String _formatTime(String? dateStr) {
    if (dateStr == null) return '';
    try {
      final dt = DateTime.parse(dateStr);
      return DateFormat('hh:mm a').format(dt);
    } catch (_) {
      return '';
    }
  }

  String _formatDate(String? dateStr) {
    if (dateStr == null) return '';
    try {
      final dt = DateTime.parse(dateStr);
      final now = DateTime.now();
      if (dt.day == now.day && dt.month == now.month && dt.year == now.year) {
        return 'Today';
      }
      final yesterday = now.subtract(const Duration(days: 1));
      if (dt.day == yesterday.day &&
          dt.month == yesterday.month &&
          dt.year == yesterday.year) {
        return 'Yesterday';
      }
      return DateFormat('MMM d, yyyy').format(dt);
    } catch (_) {
      return '';
    }
  }

  bool _isSameDay(String? a, String? b) {
    if (a == null || b == null) return false;
    try {
      final da = DateTime.parse(a);
      final db = DateTime.parse(b);
      return da.year == db.year && da.month == db.month && da.day == db.day;
    } catch (_) {
      return false;
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final bgColor = isDark ? const Color(0xFF1A1A2E) : const Color(0xFFF5F5F5);
    final myBubble = isDark
        ? const Color(0xFF4F46E5)
        : AppConstants.primaryColor;
    final theirBubble = isDark ? const Color(0xFF2D2D44) : Colors.white;

    return Scaffold(
      appBar: AppBar(
        backgroundColor: AppConstants.primaryColor,
        foregroundColor: Colors.white,
        title: Row(
          children: [
            CircleAvatar(
              radius: 18,
              backgroundColor: Colors.white.withAlpha(51),
              child: const Icon(Icons.person_rounded, color: Colors.white, size: 20),
            ),
            const SizedBox(width: 12),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  widget.otherUserName,
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                if (widget.otherUserRole.isNotEmpty)
                  Text(
                    widget.otherUserRole[0].toUpperCase() +
                        widget.otherUserRole.substring(1),
                    style: TextStyle(
                      fontSize: 12,
                      color: Colors.white.withAlpha(179),
                    ),
                  ),
              ],
            ),
          ],
        ),
      ),
      body: Column(
        children: [
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _error != null && _messages.isEmpty
                ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.cloud_off_rounded,
                          size: 48,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(height: 12),
                        Text(
                          'Could not load messages',
                          style: TextStyle(color: Colors.grey[600]),
                        ),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: _fetchMessages,
                          child: const Text('Retry'),
                        ),
                      ],
                    ),
                  )
                : ListView.builder(
                    controller: _scrollController,
                    padding: const EdgeInsets.all(16),
                    itemCount: _messages.length,
                    itemBuilder: (context, index) {
                      final msg = _messages[index];
                      final currentUser = ref.read(authProvider);
                      final myId = currentUser != null
                          ? int.tryParse(currentUser.id) ?? 0
                          : 0;
                      final isMe = msg['sender_id'] == myId;
                      final showDate =
                          index == 0 ||
                          !_isSameDay(
                            _messages[index - 1]['created_at'] as String?,
                            msg['created_at'] as String?,
                          );
                      final isFailed = msg['_failed'] == true;

                      return Column(
                        children: [
                          if (showDate)
                            Padding(
                              padding: const EdgeInsets.symmetric(vertical: 12),
                              child: Text(
                                _formatDate(msg['created_at'] as String?),
                                style: TextStyle(
                                  color: Colors.grey[500],
                                  fontSize: 12,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                            ),
                          Align(
                            alignment: isMe
                                ? Alignment.centerRight
                                : Alignment.centerLeft,
                            child: Container(
                              constraints: BoxConstraints(
                                maxWidth:
                                    MediaQuery.of(context).size.width * 0.75,
                              ),
                              margin: const EdgeInsets.only(bottom: 4),
                              padding: const EdgeInsets.symmetric(
                                horizontal: 14,
                                vertical: 10,
                              ),
                              decoration: BoxDecoration(
                                color: isMe ? myBubble : theirBubble,
                                borderRadius: BorderRadius.only(
                                  topLeft: const Radius.circular(18),
                                  topRight: const Radius.circular(18),
                                  bottomLeft: Radius.circular(isMe ? 18 : 4),
                                  bottomRight: Radius.circular(isMe ? 4 : 18),
                                ),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withAlpha(13),
                                    blurRadius: 4,
                                    offset: const Offset(0, 1),
                                  ),
                                ],
                              ),
                              child: Column(
                                crossAxisAlignment: isMe
                                    ? CrossAxisAlignment.end
                                    : CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    msg['message'] as String? ?? '',
                                    style: TextStyle(
                                      color: isMe
                                          ? Colors.white
                                          : (isDark
                                                ? Colors.white
                                                : Colors.black87),
                                      fontSize: 15,
                                    ),
                                  ),
                                  const SizedBox(height: 4),
                                  Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      Text(
                                        _formatTime(
                                          msg['created_at'] as String?,
                                        ),
                                        style: TextStyle(
                                          color: isMe
                                              ? Colors.white.withAlpha(179)
                                              : Colors.grey[500],
                                          fontSize: 11,
                                        ),
                                      ),
                                      if (isMe) ...[
                                        const SizedBox(width: 4),
                                        Icon(
                                          isFailed
                                              ? Icons.error_outline
                                              : Icons.check,
                                          size: 14,
                                          color: isFailed
                                              ? Colors.red[300]
                                              : (isMe
                                                    ? Colors.white.withAlpha(
                                                        179,
                                                      )
                                                    : Colors.grey[500]),
                                        ),
                                      ],
                                    ],
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ],
                      );
                    },
                  ),
          ),
          Container(
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF2D2D44) : Colors.white,
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withAlpha(13),
                  blurRadius: 8,
                  offset: const Offset(0, -2),
                ),
              ],
            ),
            padding: EdgeInsets.only(
              left: 12,
              right: 8,
              top: 8,
              bottom: MediaQuery.of(context).padding.bottom + 8,
            ),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _messageController,
                    decoration: InputDecoration(
                      hintText: 'Type a message...',
                      filled: true,
                      fillColor: isDark
                          ? const Color(0xFF1A1A2E)
                          : Colors.grey[100],
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(24),
                        borderSide: BorderSide.none,
                      ),
                      contentPadding: const EdgeInsets.symmetric(
                        horizontal: 16,
                        vertical: 12,
                      ),
                    ),
                    textInputAction: TextInputAction.send,
                    onSubmitted: (_) => _sendMessage(),
                    maxLines: 4,
                    minLines: 1,
                  ),
                ),
                const SizedBox(width: 8),
                Material(
                  color: AppConstants.primaryColor,
                  borderRadius: BorderRadius.circular(24),
                  child: InkWell(
                    borderRadius: BorderRadius.circular(24),
                    onTap: _isSending ? null : _sendMessage,
                    child: Container(
                      width: 44,
                      height: 44,
                      alignment: Alignment.center,
                      child: _isSending
                          ? const SizedBox(
                              width: 20,
                              height: 20,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                color: Colors.white,
                              ),
                            )
                          : const Icon(
                              Icons.send_rounded,
                              color: Colors.white,
                              size: 20,
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
}

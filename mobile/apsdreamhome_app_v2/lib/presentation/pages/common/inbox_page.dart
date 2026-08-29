import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../../core/services/api_service.dart';
import '../../../core/constants/app_constants.dart';
import 'chat_detail_page.dart';

class InboxPage extends ConsumerStatefulWidget {
  const InboxPage({super.key});

  @override
  ConsumerState<InboxPage> createState() => _InboxPageState();
}

class _InboxPageState extends ConsumerState<InboxPage> {
  final ApiService _api = ApiService();
  List<Map<String, dynamic>> _conversations = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _fetchConversations();
  }

  Future<void> _fetchConversations() async {
    setState(() => _isLoading = true);
    try {
      final res = await _api.get(AppConstants.conversationsEndpoint);
      if (res['success'] == true && res['data'] != null) {
        _conversations = (res['data'] as List).cast<Map<String, dynamic>>();
      }
      _error = null;
    } catch (e) {
      _error = e.toString();
    }
    if (mounted) setState(() => _isLoading = false);
  }

  String _timeAgo(String? dateStr) {
    if (dateStr == null) return '';
    try {
      final dt = DateTime.parse(dateStr);
      final diff = DateTime.now().difference(dt);
      if (diff.inMinutes < 1) return 'now';
      if (diff.inMinutes < 60) return '${diff.inMinutes}m';
      if (diff.inHours < 24) return '${diff.inHours}h';
      if (diff.inDays < 7) return '${diff.inDays}d';
      return DateFormat('MMM d').format(dt);
    } catch (_) {
      return '';
    }
  }

  IconData _roleIcon(String? role) {
    switch (role) {
      case 'associate':
        return Icons.people_rounded;
      case 'agent':
        return Icons.badge_rounded;
      case 'admin':
        return Icons.admin_panel_settings_rounded;
      default:
        return Icons.person_rounded;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Messages'),
        backgroundColor: AppConstants.primaryColor,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _fetchConversations,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null && _conversations.isEmpty
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.cloud_off_rounded,
                    size: 64,
                    color: Colors.grey[400],
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'Could not load messages',
                    style: TextStyle(color: Colors.grey[600], fontSize: 16),
                  ),
                  const SizedBox(height: 24),
                  ElevatedButton(
                    onPressed: _fetchConversations,
                    child: const Text('Retry'),
                  ),
                ],
              ),
            )
          : _conversations.isEmpty
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.chat_bubble_outline_rounded,
                    size: 80,
                    color: Colors.grey[300],
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'No messages yet',
                    style: TextStyle(fontSize: 18, color: Colors.grey[600]),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Start a conversation with an agent or associate',
                    style: TextStyle(color: Colors.grey[500]),
                  ),
                ],
              ),
            )
          : RefreshIndicator(
              onRefresh: _fetchConversations,
              color: AppConstants.primaryColor,
              child: ListView.separated(
                padding: const EdgeInsets.symmetric(vertical: 8),
                itemCount: _conversations.length,
                separatorBuilder: (_, _) =>
                    const Divider(height: 1, indent: 80),
                itemBuilder: (context, index) {
                  final conv = _conversations[index];
                  final unread = conv['unread_count'] is int
                      ? conv['unread_count'] as int
                      : 0;
                  final isRead =
                      conv['is_read'] == '1' ||
                      conv['is_read'] == 1 ||
                      unread == 0;
                  final lastMsg = conv['last_message'] as String? ?? '';

                  return ListTile(
                    contentPadding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 4,
                    ),
                    leading: CircleAvatar(
                      radius: 28,
                      backgroundColor: isRead
                          ? Colors.grey[200]
                          : AppConstants.primaryColor.withAlpha(30),
                      child: Icon(
                        _roleIcon(conv['other_user_role'] as String?),
                        color: isRead
                            ? Colors.grey[600]
                            : AppConstants.primaryColor,
                      ),
                    ),
                    title: Row(
                      children: [
                        Expanded(
                          child: Text(
                            conv['other_user_name'] as String? ?? 'Unknown',
                            style: TextStyle(
                              fontWeight: unread > 0
                                  ? FontWeight.bold
                                  : FontWeight.normal,
                              fontSize: 15,
                            ),
                          ),
                        ),
                        if (unread > 0)
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 8,
                              vertical: 2,
                            ),
                            decoration: BoxDecoration(
                              color: AppConstants.errorColor,
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(
                              '$unread',
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        const SizedBox(width: 8),
                        Text(
                          _timeAgo(conv['last_message_time'] as String?),
                          style: TextStyle(
                            color: Colors.grey[500],
                            fontSize: 12,
                          ),
                        ),
                      ],
                    ),
                    subtitle: Padding(
                      padding: const EdgeInsets.only(top: 4),
                      child: Text(
                        lastMsg,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          color: Colors.grey[600],
                          fontSize: 13,
                          fontWeight: unread > 0
                              ? FontWeight.w500
                              : FontWeight.normal,
                        ),
                      ),
                    ),
                    onTap: () {
                      Navigator.of(context)
                          .push(
                            MaterialPageRoute(
                              builder: (_) => ChatDetailPage(
                                otherUserId: conv['other_user_id'] is int
                                    ? conv['other_user_id'] as int
                                    : int.parse(
                                        conv['other_user_id'].toString(),
                                      ),
                                otherUserName:
                                    conv['other_user_name'] as String? ??
                                    'User',
                                otherUserRole:
                                    conv['other_user_role'] as String? ?? '',
                              ),
                            ),
                          )
                          .then((_) => _fetchConversations());
                    },
                  );
                },
              ),
            ),
    );
  }
}

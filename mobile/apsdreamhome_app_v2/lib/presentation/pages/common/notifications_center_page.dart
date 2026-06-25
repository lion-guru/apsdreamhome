import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../data/repositories/kyc_repository_provider.dart';

enum NotificationFilter { all, unread, read }

class NotificationsCenterPage extends ConsumerStatefulWidget {
  const NotificationsCenterPage({super.key});

  @override
  ConsumerState<NotificationsCenterPage> createState() =>
      _NotificationsCenterPageState();
}

class _NotificationsCenterPageState
    extends ConsumerState<NotificationsCenterPage> {
  bool _isLoading = true;
  String? _error;
  List<Map<String, dynamic>> _notifications = [];
  NotificationFilter _filter = NotificationFilter.all;
  final Set<int> _selectedIds = {};
  bool _isSelectionMode = false;

  @override
  void initState() {
    super.initState();
    _fetchNotifications();
  }

  Future<void> _fetchNotifications() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final api = ref.read(apiServiceProvider);
      final response = await api.get('/user/notifications');
      final data = response['data'];
      if (!mounted) return;
      setState(() {
        if (data is List) {
          _notifications = data
              .map((e) => Map<String, dynamic>.from(e as Map))
              .toList();
        } else if (data is Map) {
          _notifications = (data['notifications'] as List<dynamic>? ?? [])
              .map((e) => Map<String, dynamic>.from(e as Map))
              .toList();
        }
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  List<Map<String, dynamic>> get _filteredNotifications {
    switch (_filter) {
      case NotificationFilter.unread:
        return _notifications
            .where((n) => !(n['is_read'] as bool? ?? false))
            .toList();
      case NotificationFilter.read:
        return _notifications
            .where((n) => n['is_read'] as bool? ?? false)
            .toList();
      case NotificationFilter.all:
        return _notifications;
    }
  }

  int get _unreadCount =>
      _notifications.where((n) => !(n['is_read'] as bool? ?? false)).length;

  Future<void> _markAsRead(int id) async {
    try {
      final api = ref.read(apiServiceProvider);
      await api.post('/user/notifications/$id/read');
      setState(() {
        final idx = _notifications.indexWhere((n) => n['id'] == id);
        if (idx != -1) {
          _notifications[idx]['is_read'] = true;
        }
      });
    } catch (_) {}
  }

  Future<void> _markAllAsRead() async {
    try {
      final api = ref.read(apiServiceProvider);
      await api.post('/user/notifications/read-all');
      setState(() {
        for (var n in _notifications) {
          n['is_read'] = true;
        }
      });
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('All notifications marked as read')),
      );
    } catch (_) {}
  }

  Future<void> _deleteNotification(int id) async {
    try {
      final api = ref.read(apiServiceProvider);
      await api.delete('/user/notifications/$id');
      setState(() {
        _notifications.removeWhere((n) => n['id'] == id);
        _selectedIds.remove(id);
      });
    } catch (_) {}
  }

  Future<void> _deleteSelected() async {
    if (_selectedIds.isEmpty) return;
    try {
      final api = ref.read(apiServiceProvider);
      for (final id in _selectedIds) {
        await api.delete('/user/notifications/$id');
      }
      setState(() {
        _notifications.removeWhere((n) => _selectedIds.contains(n['id']));
        _selectedIds.clear();
        _isSelectionMode = false;
      });
    } catch (_) {}
  }

  void _toggleSelection(int id) {
    setState(() {
      if (_selectedIds.contains(id)) {
        _selectedIds.remove(id);
        if (_selectedIds.isEmpty) {
          _isSelectionMode = false;
        }
      } else {
        _selectedIds.add(id);
        _isSelectionMode = true;
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.surfaceColor,
      appBar: AppBar(
        title: _isSelectionMode
            ? Text('${_selectedIds.length} selected')
            : const Text('Notifications'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        leading: _isSelectionMode
            ? IconButton(
                icon: const Icon(Icons.close_rounded, size: 22),
                onPressed: () {
                  setState(() {
                    _selectedIds.clear();
                    _isSelectionMode = false;
                  });
                },
              )
            : IconButton(
                icon: const Icon(Icons.arrow_back_ios_new, size: 20),
                onPressed: () => context.pop(),
              ),
        actions: [
          if (_isSelectionMode)
            IconButton(
              icon: const Icon(Icons.delete_rounded, size: 22),
              onPressed: _deleteSelected,
            )
          else ...[
            if (_unreadCount > 0)
              TextButton(
                onPressed: _markAllAsRead,
                child: const Text(
                  'Read All',
                  style: TextStyle(color: Colors.white, fontSize: 13),
                ),
              ),
            IconButton(
              icon: const Icon(Icons.refresh_rounded, size: 22),
              onPressed: _fetchNotifications,
            ),
          ],
        ],
      ),
      body: Column(
        children: [
          _buildFilterBar(),
          Expanded(
            child: _isLoading
                ? const Center(
                    child: CircularProgressIndicator(
                        color: AppTheme.primaryColor),
                  )
                : _error != null
                    ? _buildErrorState()
                    : _filteredNotifications.isEmpty
                        ? _buildEmptyState()
                        : RefreshIndicator(
                            onRefresh: _fetchNotifications,
                            color: AppTheme.primaryColor,
                            child: ListView.builder(
                              padding:
                                  const EdgeInsets.symmetric(vertical: 8),
                              itemCount: _filteredNotifications.length,
                              itemBuilder: (context, index) {
                                final notification =
                                    _filteredNotifications[index];
                                final id = notification['id'] as int? ?? 0;
                                final isRead =
                                    notification['is_read'] as bool? ?? false;
                                final isSelected =
                                    _selectedIds.contains(id);
                                return _buildNotificationCard(
                                  notification: notification,
                                  isRead: isRead,
                                  isSelected: isSelected,
                                  onTap: () {
                                    if (_isSelectionMode) {
                                      _toggleSelection(id);
                                    } else {
                                      _markAsRead(id);
                                    }
                                  },
                                  onLongPress: () {
                                    _toggleSelection(id);
                                  },
                                  onDelete: () {
                                    _deleteNotification(id);
                                  },
                                );
                              },
                            ),
                          ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterBar() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
      color: Colors.white,
      child: Row(
        children: [
          _buildFilterChip(
            label: 'All',
            count: _notifications.length,
            filter: NotificationFilter.all,
          ),
          const SizedBox(width: 8),
          _buildFilterChip(
            label: 'Unread',
            count: _unreadCount,
            filter: NotificationFilter.unread,
          ),
          const SizedBox(width: 8),
          _buildFilterChip(
            label: 'Read',
            count: _notifications.length - _unreadCount,
            filter: NotificationFilter.read,
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChip({
    required String label,
    required int count,
    required NotificationFilter filter,
  }) {
    final isActive = _filter == filter;
    return GestureDetector(
      onTap: () {
        setState(() {
          _filter = filter;
        });
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
        decoration: BoxDecoration(
          color: isActive
              ? AppTheme.primaryColor.withValues(alpha: 0.1)
              : AppTheme.surfaceColor,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isActive
                ? AppTheme.primaryColor.withValues(alpha: 0.3)
                : Colors.grey.shade200,
            width: 1,
          ),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              label,
              style: TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w500,
                color: isActive
                    ? AppTheme.primaryColor
                    : Colors.grey.shade600,
              ),
            ),
            if (count > 0) ...[
              const SizedBox(width: 6),
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(
                  color: isActive
                      ? AppTheme.primaryColor
                      : Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  count.toString(),
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.w600,
                    color: isActive ? Colors.white : Colors.grey.shade600,
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildNotificationCard({
    required Map<String, dynamic> notification,
    required bool isRead,
    required bool isSelected,
    required VoidCallback onTap,
    required VoidCallback onLongPress,
    required VoidCallback onDelete,
  }) {
    final title = notification['title']?.toString() ?? '';
    final body = notification['body']?.toString() ??
        notification['message']?.toString() ??
        '';
    final type = notification['type']?.toString() ?? '';
    final createdAt = notification['created_at']?.toString() ?? '';

    final icon = _getNotificationIcon(type);
    final iconColor = _getNotificationColor(type);
    final relativeTime = _formatRelativeTime(createdAt);

    return Dismissible(
      key: ValueKey(notification['id']),
      direction: DismissDirection.endToStart,
      onDismissed: (_) => onDelete(),
      background: Container(
        alignment: Alignment.centerRight,
        margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
        padding: const EdgeInsets.only(right: 20),
        decoration: BoxDecoration(
          color: AppTheme.errorColor,
          borderRadius: BorderRadius.circular(12),
        ),
        child: const Icon(Icons.delete_rounded, color: Colors.white, size: 24),
      ),
      child: GestureDetector(
        onTap: onTap,
        onLongPress: onLongPress,
        child: Container(
          margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: isSelected
                ? AppTheme.primaryColor.withValues(alpha: 0.06)
                : Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
              color: isSelected
                  ? AppTheme.primaryColor.withValues(alpha: 0.3)
                  : isRead
                      ? Colors.transparent
                      : AppTheme.primaryColor.withValues(alpha: 0.1),
              width: isSelected ? 1.5 : 1,
            ),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.03),
                blurRadius: 6,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              if (_isSelectionMode)
                Padding(
                  padding: const EdgeInsets.only(right: 12, top: 2),
                  child: Icon(
                    isSelected
                        ? Icons.check_circle_rounded
                        : Icons.circle_outlined,
                    size: 22,
                    color: isSelected
                        ? AppTheme.primaryColor
                        : Colors.grey.shade400,
                  ),
                ),
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: iconColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(icon, color: iconColor, size: 22),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            title,
                            style: TextStyle(
                              fontWeight:
                                  isRead ? FontWeight.w500 : FontWeight.w600,
                              fontSize: 14,
                              color: AppTheme.textPrimaryLight,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        if (!isRead)
                          Container(
                            width: 8,
                            height: 8,
                            margin: const EdgeInsets.only(left: 8),
                            decoration: const BoxDecoration(
                              color: AppTheme.primaryColor,
                              shape: BoxShape.circle,
                            ),
                          ),
                      ],
                    ),
                    if (body.isNotEmpty) ...[
                      const SizedBox(height: 4),
                      Text(
                        body,
                        style: TextStyle(
                          fontSize: 13,
                          color: Colors.grey.shade500,
                          height: 1.4,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Icon(
                          Icons.access_time_rounded,
                          size: 12,
                          color: Colors.grey.shade400,
                        ),
                        const SizedBox(width: 4),
                        Text(
                          relativeTime,
                          style: TextStyle(
                            fontSize: 11,
                            color: Colors.grey.shade400,
                          ),
                        ),
                        if (type.isNotEmpty) ...[
                          const SizedBox(width: 10),
                          Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 6, vertical: 2),
                            decoration: BoxDecoration(
                              color: iconColor.withValues(alpha: 0.08),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              type[0].toUpperCase() + type.substring(1),
                              style: TextStyle(
                                fontSize: 10,
                                fontWeight: FontWeight.w500,
                                color: iconColor,
                              ),
                            ),
                          ),
                        ],
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    String message;
    String subMessage;
    switch (_filter) {
      case NotificationFilter.unread:
        message = 'No unread notifications';
        subMessage = "You're all caught up!";
        break;
      case NotificationFilter.read:
        message = 'No read notifications';
        subMessage = 'Notifications you have read will appear here';
        break;
      case NotificationFilter.all:
        message = 'No notifications yet';
        subMessage = 'You\'ll receive updates about your activity here';
        break;
    }
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(40),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 100,
              height: 100,
              decoration: BoxDecoration(
                color: AppTheme.primaryColor.withValues(alpha: 0.06),
                shape: BoxShape.circle,
              ),
              child: Icon(
                _filter == NotificationFilter.unread
                    ? Icons.notifications_none_rounded
                    : Icons.notifications_off_outlined,
                size: 48,
                color: AppTheme.primaryColor.withValues(alpha: 0.3),
              ),
            ),
            const SizedBox(height: 24),
            Text(
              message,
              style: const TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w600,
                color: AppTheme.textPrimaryLight,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              subMessage,
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

  Widget _buildErrorState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(40),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 100,
              height: 100,
              decoration: BoxDecoration(
                color: AppTheme.errorColor.withValues(alpha: 0.08),
                shape: BoxShape.circle,
              ),
              child: Icon(
                Icons.wifi_off_rounded,
                size: 48,
                color: AppTheme.errorColor.withValues(alpha: 0.5),
              ),
            ),
            const SizedBox(height: 24),
            const Text(
              'Something went wrong',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w600,
                color: AppTheme.textPrimaryLight,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Could not load notifications.\nPlease try again.',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 14,
                color: Colors.grey.shade500,
                height: 1.5,
              ),
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: _fetchNotifications,
              icon: const Icon(Icons.refresh_rounded, size: 18),
              label: const Text('Retry'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primaryColor,
                foregroundColor: Colors.white,
                padding:
                    const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  static IconData _getNotificationIcon(String type) {
    switch (type.toLowerCase()) {
      case 'booking':
      case 'booking_update':
        return Icons.receipt_long_rounded;
      case 'payment':
      case 'payment_received':
        return Icons.payment_rounded;
      case 'property':
      case 'property_update':
        return Icons.home_rounded;
      case 'lead':
      case 'lead_update':
        return Icons.person_add_outlined;
      case 'commission':
        return Icons.account_balance_wallet_rounded;
      case 'reminder':
        return Icons.alarm_rounded;
      case 'system':
        return Icons.info_outline_rounded;
      case 'offer':
      case 'promotion':
        return Icons.local_offer_rounded;
      case 'document':
        return Icons.description_outlined;
      case 'kyc':
        return Icons.verified_user_outlined;
      case 'emi':
      case 'emi_reminder':
        return Icons.calendar_today_rounded;
      default:
        return Icons.notifications_none_rounded;
    }
  }

  static Color _getNotificationColor(String type) {
    switch (type.toLowerCase()) {
      case 'booking':
      case 'booking_update':
        return AppTheme.primaryColor;
      case 'payment':
      case 'payment_received':
        return AppTheme.successColor;
      case 'property':
      case 'property_update':
        return AppTheme.infoColor;
      case 'lead':
      case 'lead_update':
        return const Color(0xFFFF9800);
      case 'commission':
        return const Color(0xFF9C27B0);
      case 'reminder':
        return AppTheme.warningColor;
      case 'system':
        return Colors.grey.shade600;
      case 'offer':
      case 'promotion':
        return AppTheme.errorColor;
      case 'emi':
      case 'emi_reminder':
        return const Color(0xFF00897B);
      default:
        return AppTheme.primaryColor;
    }
  }

  static String _formatRelativeTime(String dateStr) {
    if (dateStr.isEmpty) return '';
    try {
      final date = DateTime.parse(dateStr);
      final now = DateTime.now();
      final diff = now.difference(date);
      if (diff.inSeconds < 60) return 'Just now';
      if (diff.inMinutes < 60) return '${diff.inMinutes}m ago';
      if (diff.inHours < 24) return '${diff.inHours}h ago';
      if (diff.inDays < 7) return '${diff.inDays}d ago';
      if (diff.inDays < 30) return '${(diff.inDays / 7).floor()}w ago';
      return '${(diff.inDays / 30).floor()}mo ago';
    } catch (_) {
      return dateStr;
    }
  }
}

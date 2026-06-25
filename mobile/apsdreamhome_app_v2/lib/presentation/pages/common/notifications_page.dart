import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

import '../../../core/providers/auth_provider.dart';
import '../../../core/services/api_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../../data/repositories/kyc_repository_provider.dart';

// ---------------------------------------------------------------------------
// Notification model
// ---------------------------------------------------------------------------

class AppNotification {
  final String id;
  final String title;
  final String body;
  final String type;
  final bool isRead;
  final DateTime createdAt;
  final String? referenceId;

  const AppNotification({
    required this.id,
    required this.title,
    required this.body,
    required this.type,
    required this.isRead,
    required this.createdAt,
    this.referenceId,
  });

  factory AppNotification.fromJson(Map<String, dynamic> json) {
    return AppNotification(
      id: json['id']?.toString() ?? '',
      title: json['title']?.toString() ?? '',
      body: json['body']?.toString() ?? '',
      type: json['type']?.toString() ?? 'system',
      isRead: json['is_read'] == true || json['is_read'] == 1,
      createdAt: DateTime.tryParse(json['created_at']?.toString() ?? '') ??
          DateTime.now(),
      referenceId: json['reference_id']?.toString(),
    );
  }

  AppNotification copyWith({bool? isRead}) {
    return AppNotification(
      id: id,
      title: title,
      body: body,
      type: type,
      isRead: isRead ?? this.isRead,
      createdAt: createdAt,
      referenceId: referenceId,
    );
  }
}

// ---------------------------------------------------------------------------
// Notification filter enum
// ---------------------------------------------------------------------------

enum NotificationFilter {
  all('All'),
  unread('Unread'),
  booking('Bookings'),
  payment('Payments'),
  system('System');

  final String label;
  const NotificationFilter(this.label);
}

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------

class NotificationsState {
  final List<AppNotification> notifications;
  final bool isLoading;
  final String? error;
  final NotificationFilter filter;

  const NotificationsState({
    this.notifications = const [],
    this.isLoading = false,
    this.error,
    this.filter = NotificationFilter.all,
  });

  NotificationsState copyWith({
    List<AppNotification>? notifications,
    bool? isLoading,
    String? error,
    NotificationFilter? filter,
    bool clearError = false,
  }) {
    return NotificationsState(
      notifications: notifications ?? this.notifications,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : (error ?? this.error),
      filter: filter ?? this.filter,
    );
  }

  List<AppNotification> get filteredNotifications {
    switch (filter) {
      case NotificationFilter.all:
        return notifications;
      case NotificationFilter.unread:
        return notifications.where((n) => !n.isRead).toList();
      case NotificationFilter.booking:
        return notifications
            .where((n) => n.type.toLowerCase() == 'booking')
            .toList();
      case NotificationFilter.payment:
        return notifications
            .where((n) => n.type.toLowerCase() == 'payment')
            .toList();
      case NotificationFilter.system:
        return notifications
            .where((n) =>
                n.type.toLowerCase() == 'system' ||
                n.type.toLowerCase() == 'alert')
            .toList();
    }
  }

  int get unreadCount => notifications.where((n) => !n.isRead).length;
}

// ---------------------------------------------------------------------------
// Notifier
// ---------------------------------------------------------------------------

class NotificationsNotifier extends StateNotifier<NotificationsState> {
  final Ref _ref;
  Timer? _pollTimer;

  NotificationsNotifier(this._ref) : super(const NotificationsState()) {
    final user = _ref.read(authProvider);
    if (user != null) {
      fetchNotifications();
    }
  }

  ApiService get _api => _ref.read(apiServiceProvider);

  // ---- Fetch ---------------------------------------------------------------

  Future<void> fetchNotifications() async {
    state = state.copyWith(isLoading: true, clearError: true);
    try {
      final response = await _api.request(
        method: 'GET',
        endpoint: 'notifications',
      );

      final dynamic data = response['data'];
      final List<dynamic> items;
      if (data is List) {
        items = data;
      } else if (data is Map<String, dynamic> && data['notifications'] is List) {
        items = data['notifications'] as List<dynamic>;
      } else {
        items = <dynamic>[];
      }

      final notifications = items
          .map((json) =>
              AppNotification.fromJson(json as Map<String, dynamic>))
          .toList();

      // Sort newest first
      notifications.sort((a, b) => b.createdAt.compareTo(a.createdAt));

      state = state.copyWith(
        notifications: notifications,
        isLoading: false,
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
    }
  }

  // ---- Mark single read ----------------------------------------------------

  Future<void> markAsRead(String id) async {
    // Optimistic update
    state = state.copyWith(
      notifications: state.notifications.map((n) {
        if (n.id == id) return n.copyWith(isRead: true);
        return n;
      }).toList(),
    );

    try {
      await _api.request(
        method: 'PUT',
        endpoint: 'notifications/$id/read',
      );
    } catch (_) {
      // Revert on failure
      state = state.copyWith(
        notifications: state.notifications.map((n) {
          if (n.id == id) return n.copyWith(isRead: false);
          return n;
        }).toList(),
      );
    }
  }

  // ---- Mark all read -------------------------------------------------------

  Future<void> markAllAsRead() async {
    final previous = state.notifications;
    state = state.copyWith(
      notifications: state.notifications.map((n) => n.copyWith(isRead: true)).toList(),
    );

    try {
      await _api.request(
        method: 'PUT',
        endpoint: 'notifications/read-all',
      );
    } catch (_) {
      state = state.copyWith(notifications: previous);
    }
  }

  // ---- Filter --------------------------------------------------------------

  void setFilter(NotificationFilter filter) {
    state = state.copyWith(filter: filter);
  }

  // ---- Navigation ----------------------------------------------------------

  void navigateToNotification(BuildContext context, AppNotification notification) {
    // Mark as read
    if (!notification.isRead) {
      markAsRead(notification.id);
    }

    // Navigate based on type
    final refId = notification.referenceId;
    switch (notification.type.toLowerCase()) {
      case 'booking':
        if (refId != null && refId.isNotEmpty) {
          context.push('/bookings/$refId');
        } else {
          context.push('/bookings');
        }
        break;
      case 'payment':
        if (refId != null && refId.isNotEmpty) {
          context.push('/payments/$refId');
        } else {
          context.push('/payments');
        }
        break;
      case 'lead':
        if (refId != null && refId.isNotEmpty) {
          context.push('/leads/$refId');
        } else {
          context.push('/leads');
        }
        break;
      case 'alert':
        context.push('/alerts');
        break;
      case 'system':
      default:
        // Stay on notifications page for system messages
        break;
    }
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
    super.dispose();
  }
}

// ---------------------------------------------------------------------------
// Providers
// ---------------------------------------------------------------------------

final notificationsProvider =
    StateNotifierProvider<NotificationsNotifier, NotificationsState>((ref) {
  return NotificationsNotifier(ref);
});

// ---------------------------------------------------------------------------
// Time-ago helper
// ---------------------------------------------------------------------------

String _timeAgo(DateTime dateTime) {
  final now = DateTime.now();
  final diff = now.difference(dateTime);

  if (diff.isNegative) return 'Just now';
  if (diff.inSeconds < 60) return 'Just now';
  if (diff.inMinutes < 60) {
    final m = diff.inMinutes;
    return '$m min ago';
  }
  if (diff.inHours < 24) {
    final h = diff.inHours;
    return '$h h ago';
  }
  if (diff.inHours < 48) return 'Yesterday';
  if (diff.inDays < 7) {
    return DateFormat('EEEE').format(dateTime);
  }
  return DateFormat('dd MMM yyyy').format(dateTime);
}

// ---------------------------------------------------------------------------
// Notification type helpers
// ---------------------------------------------------------------------------

IconData _iconForType(String type) {
  switch (type.toLowerCase()) {
    case 'booking':
      return Icons.bookmark_rounded;
    case 'payment':
      return Icons.payment_rounded;
    case 'lead':
      return Icons.person_add_rounded;
    case 'alert':
      return Icons.warning_rounded;
    case 'system':
    default:
      return Icons.info_rounded;
  }
}

Color _colorForType(String type) {
  switch (type.toLowerCase()) {
    case 'booking':
      return AppTheme.infoColor;
    case 'payment':
      return AppTheme.successColor;
    case 'lead':
      return AppTheme.warningColor;
    case 'alert':
      return AppTheme.errorColor;
    case 'system':
    default:
      return Colors.grey;
  }
}

// ---------------------------------------------------------------------------
// Page
// ---------------------------------------------------------------------------

class NotificationsPage extends ConsumerStatefulWidget {
  const NotificationsPage({super.key});

  @override
  ConsumerState<NotificationsPage> createState() => _NotificationsPageState();
}

class _NotificationsPageState extends ConsumerState<NotificationsPage> {
  @override
  Widget build(BuildContext context) {
    final state = ref.watch(notificationsProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Notifications'),
        actions: [
          if (state.unreadCount > 0)
            TextButton(
              onPressed: () {
                ref.read(notificationsProvider.notifier).markAllAsRead();
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('All notifications marked as read'),
                    duration: Duration(seconds: 2),
                  ),
                );
              },
              child: Text(
                'Mark All Read',
                style: TextStyle(
                  color: Colors.white.withValues(alpha: 0.9),
                  fontWeight: FontWeight.w600,
                  fontSize: 13,
                ),
              ),
            ),
        ],
      ),
      body: Column(
        children: [
          // ---- Filter chips ------------------------------------------------
          _buildFilterChips(state),

          // ---- Body --------------------------------------------------------
          Expanded(
            child: _buildBody(state),
          ),
        ],
      ),
    );
  }

  // ---- Filter chips --------------------------------------------------------

  Widget _buildFilterChips(NotificationsState state) {
    return Container(
      height: 56,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: NotificationFilter.values.length,
        separatorBuilder: (_, __) => const SizedBox(width: 8),
        itemBuilder: (context, index) {
          final filter = NotificationFilter.values[index];
          final isSelected = state.filter == filter;

          // Badge count for unread filter
          int? badgeCount;
          if (filter == NotificationFilter.unread) {
            badgeCount = state.unreadCount;
          }

          return ChoiceChip(
            label: badgeCount != null && badgeCount > 0
                ? Text('$filter.label ($badgeCount)')
                : Text(filter.label),
            selected: isSelected,
            selectedColor: AppTheme.primaryColor.withValues(alpha: 0.15),
            labelStyle: TextStyle(
              color: isSelected ? AppTheme.primaryColor : Colors.grey.shade700,
              fontWeight: isSelected ? FontWeight.w600 : FontWeight.w400,
              fontSize: 13,
            ),
            side: BorderSide(
              color: isSelected
                  ? AppTheme.primaryColor.withValues(alpha: 0.5)
                  : Colors.grey.shade300,
            ),
            onSelected: (_) {
              ref.read(notificationsProvider.notifier).setFilter(filter);
            },
          );
        },
      ),
    );
  }

  // ---- Body ----------------------------------------------------------------

  Widget _buildBody(NotificationsState state) {
    if (state.isLoading && state.notifications.isEmpty) {
      return const Center(
        child: CircularProgressIndicator(color: AppTheme.primaryColor),
      );
    }

    if (state.error != null && state.notifications.isEmpty) {
      return _buildErrorState(state.error!);
    }

    final filtered = state.filteredNotifications;

    if (filtered.isEmpty) {
      return _buildEmptyState(state.filter);
    }

    return RefreshIndicator(
      onRefresh: () =>
          ref.read(notificationsProvider.notifier).fetchNotifications(),
      color: AppTheme.primaryColor,
      child: ListView.separated(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.only(bottom: 24),
        itemCount: filtered.length,
        separatorBuilder: (_, __) => Divider(
          height: 1,
          indent: 72,
          color: Colors.grey.shade200,
        ),
        itemBuilder: (context, index) {
          final notification = filtered[index];
          return _NotificationTile(
            notification: notification,
            onTap: () {
              ref
                  .read(notificationsProvider.notifier)
                  .navigateToNotification(context, notification);
            },
          );
        },
      ),
    );
  }

  // ---- Empty state ---------------------------------------------------------

  Widget _buildEmptyState(NotificationFilter filter) {
    final isUnreadFilter = filter == NotificationFilter.unread;

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
                color: AppTheme.primaryColor.withValues(alpha: 0.08),
                shape: BoxShape.circle,
              ),
              child: Icon(
                isUnreadFilter
                    ? Icons.mark_email_read_rounded
                    : Icons.notifications_none_rounded,
                size: 48,
                color: AppTheme.primaryColor.withValues(alpha: 0.4),
              ),
            ),
            const SizedBox(height: 24),
            Text(
              isUnreadFilter
                  ? "You're all caught up!"
                  : 'No notifications yet',
              style: const TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w600,
                color: AppTheme.textPrimaryLight,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              isUnreadFilter
                  ? 'All notifications have been read'
                  : 'Notifications about your bookings, payments,\nand updates will appear here.',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 14,
                color: Colors.grey.shade500,
                height: 1.5,
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ---- Error state ---------------------------------------------------------

  Widget _buildErrorState(String error) {
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
              'Could not load notifications.\nPlease check your connection and try again.',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 14,
                color: Colors.grey.shade500,
                height: 1.5,
              ),
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: () {
                ref.read(notificationsProvider.notifier).fetchNotifications();
              },
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
}

// ---------------------------------------------------------------------------
// Notification tile (separated for performance)
// ---------------------------------------------------------------------------

class _NotificationTile extends StatelessWidget {
  final AppNotification notification;
  final VoidCallback onTap;

  const _NotificationTile({
    required this.notification,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final color = _colorForType(notification.type);
    final icon = _iconForType(notification.type);
    final unread = !notification.isRead;

    return Material(
      color: unread ? Colors.grey.shade50 : Colors.transparent,
      child: InkWell(
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // ---- Leading icon -------------------------------------------
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.12),
                  shape: BoxShape.circle,
                ),
                child: Icon(icon, color: color, size: 22),
              ),
              const SizedBox(width: 12),

              // ---- Title + body -------------------------------------------
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      notification.title,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontSize: 15,
                        fontWeight:
                            unread ? FontWeight.w600 : FontWeight.w500,
                        color: AppTheme.textPrimaryLight,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      notification.body,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontSize: 13,
                        color: Colors.grey.shade600,
                        height: 1.35,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 12),

              // ---- Trailing: time + dot -----------------------------------
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(
                    _timeAgo(notification.createdAt),
                    style: TextStyle(
                      fontSize: 12,
                      color: unread
                          ? AppTheme.primaryColor.withValues(alpha: 0.7)
                          : Colors.grey.shade400,
                    ),
                  ),
                  const SizedBox(height: 6),
                  if (unread)
                    Container(
                      width: 10,
                      height: 10,
                      decoration: const BoxDecoration(
                        color: AppTheme.primaryColor,
                        shape: BoxShape.circle,
                      ),
                    )
                  else
                    const SizedBox(height: 10),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}

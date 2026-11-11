import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:riverpod/legacy.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:crypto/crypto.dart';

/// Provider for connectivity status
final connectivityProvider = StreamProvider<List<ConnectivityResult>>((ref) {
  return Connectivity().onConnectivityChanged;
});

/// Provider for online/offline status
final isOnlineProvider = Provider<bool>((ref) {
  final connectivity = ref.watch(connectivityProvider);
  return connectivity.when(
    data: (results) => results.isNotEmpty && !results.contains(ConnectivityResult.none),
    loading: () => true,
    error: (_, __) => true,
  );
});

/// Sync status enum for UI
enum SyncStatus {
  idle,
  syncing,
  success,
  error,
  pending,
}

/// Sync status notifier
class SyncStatusNotifier extends StateNotifier<SyncStatus> {
  SyncStatusNotifier() : super(SyncStatus.idle);

  void setSyncing() => state = SyncStatus.syncing;
  void setSuccess() => state = SyncStatus.success;
  void setError() => state = SyncStatus.error;
  void setPending() => state = SyncStatus.pending;
  void setIdle() => state = SyncStatus.idle;
}

/// Provider for sync status
final syncStatusProvider = StateNotifierProvider<SyncStatusNotifier, SyncStatus>((ref) {
  return SyncStatusNotifier();
});

/// Widget that shows online/offline status indicator
class OfflineIndicator extends ConsumerWidget {
  final bool showText;
  final bool showIcon;
  final double iconSize;
  final EdgeInsetsGeometry? padding;
  final Widget? customOfflineWidget;
  final Widget? customOnlineWidget;

  const OfflineIndicator({
    super.key,
    this.showText = true,
    this.showIcon = true,
    this.iconSize = 16,
    this.padding,
    this.customOfflineWidget,
    this.customOnlineWidget,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final isOnline = ref.watch(isOnlineProvider);

    return AnimatedSwitcher(
      duration: const Duration(milliseconds: 300),
      child: Container(
        padding: padding ?? const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: isOnline
              ? Colors.green.withValues(alpha: 0.15)
              : Colors.orange.withValues(alpha: 0.15),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isOnline ? Colors.green : Colors.orange,
            width: 1,
          ),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            if (showIcon)
              Icon(
                isOnline ? Icons.wifi : Icons.wifi_off,
                size: iconSize,
                color: isOnline ? Colors.green : Colors.orange,
              ),
            if (showText) ...[
              const SizedBox(width: 6),
              Text(
                isOnline ? 'Online' : 'Offline',
                style: TextStyle(
                  color: isOnline ? Colors.green : Colors.orange,
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

/// Widget that shows sync status with manual sync button
class SyncIndicator extends ConsumerWidget {
  final VoidCallback? onSyncPressed;
  final bool showPendingCount;
  final bool compact;

  const SyncIndicator({
    super.key,
    this.onSyncPressed,
    this.showPendingCount = true,
    this.compact = false,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final syncStatus = ref.watch(syncStatusProvider);
    final isOnline = ref.watch(isOnlineProvider);
    final pendingCount = 0; // TODO: Get from sync queue

    if (compact) {
      return _buildCompact(ref);
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: _getBackgroundColor(ref, syncStatus),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: _getBorderColor()),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Status icon
          _buildStatusIcon(ref, syncStatus),
          const SizedBox(width: 8),
          // Status text
          Text(
            _getStatusText(ref, syncStatus),
            style: TextStyle(
              color: _getTextColor(ref, syncStatus),
              fontSize: 13,
              fontWeight: FontWeight.w600,
            ),
          ),
          if (_showPendingCount(pendingCount)) ...[
            const SizedBox(width: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
              decoration: BoxDecoration(
                color: Colors.orange.withValues(alpha: 0.2),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Text(
                'pending sync',
                style: TextStyle(
                  color: Colors.orange,
                  fontSize: 11,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
          ],
          const SizedBox(width: 12),
          // Sync button
          if (onSyncPressed != null)
            TextButton.icon(
              onPressed: onSyncPressed,
              icon: const Icon(Icons.sync, size: 16),
              label: const Text('Sync', style: TextStyle(fontSize: 12)),
              style: TextButton.styleFrom(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                minimumSize: Size.zero,
                tapTargetSize: MaterialTapTargetSize.shrinkWrap,
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildCompact(WidgetRef ref) {
    return _buildCompactImpl(ref);
  }

  Widget _buildCompactImpl(WidgetRef ref) {
    final syncStatus = ref.watch(syncStatusProvider);
    final isOnline = ref.watch(isOnlineProvider);
    final pendingCount = 0; // TODO: Get from sync queue

    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        // Online/Offline indicator
        const OfflineIndicator(
          showText: false,
          showIcon: true,
          iconSize: 18,
        ),
        const SizedBox(width: 8),
        // Sync status
        if (ref.watch(syncStatusProvider) != SyncStatus.idle) ...[
          _buildStatusIndicator(ref, syncStatus),
        ],
        const SizedBox(width: 8),
        // Sync button
        if (onSyncPressed != null)
          IconButton(
            onPressed: onSyncPressed,
            icon: Icon(
              _getSyncIcon(syncStatus),
              size: 20,
              color: _getSyncColor(syncStatus),
            ),
            tooltip: 'Sync now',
          ),
      ],
    );
  }

  Widget _buildStatusIndicator(WidgetRef ref, SyncStatus syncStatus) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: _getBackgroundColor(ref, syncStatus),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          _buildStatusIcon(ref, syncStatus),
          const SizedBox(width: 4),
          Text(
            _getStatusText(ref, syncStatus),
            style: TextStyle(
              color: _getTextColor(ref, syncStatus),
              fontSize: 11,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }

  Color _getBackgroundColor(WidgetRef ref, SyncStatus syncStatus) {
    switch (syncStatus) {
      case SyncStatus.syncing:
        return Colors.orange.withValues(alpha: 0.15);
      case SyncStatus.success:
        return Colors.green.withValues(alpha: 0.15);
      case SyncStatus.error:
        return Colors.red.withValues(alpha: 0.15);
      case SyncStatus.pending:
        return Colors.orange.withValues(alpha: 0.15);
      default:
        return Colors.transparent;
    }
  }

  Color _getBorderColor() {
    return Colors.orange.withValues(alpha: 0.3);
  }

  Color _getTextColor(WidgetRef ref, SyncStatus syncStatus) {
    switch (syncStatus) {
      case SyncStatus.syncing:
        return Colors.orange;
      case SyncStatus.success:
        return Colors.green;
      case SyncStatus.error:
        return Colors.red;
      case SyncStatus.pending:
        return Colors.orange;
      default:
        return Colors.orange;
    }
  }

  Widget _buildStatusIcon(WidgetRef ref, SyncStatus syncStatus) {
    switch (syncStatus) {
      case SyncStatus.syncing:
        return const SizedBox(
          width: 16,
          height: 16,
          child: CircularProgressIndicator(strokeWidth: 2, color: Colors.orange),
        );
      case SyncStatus.success:
        return const Icon(Icons.check_circle, color: Colors.green, size: 16);
      case SyncStatus.error:
        return const Icon(Icons.error, color: Colors.red, size: 16);
      case SyncStatus.pending:
        return const Icon(Icons.schedule, color: Colors.orange, size: 16);
      default:
        return const Icon(Icons.sync_disabled, color: Colors.grey, size: 16);
    }
  }

  String _getStatusText(WidgetRef ref, SyncStatus syncStatus) {
    switch (syncStatus) {
      case SyncStatus.syncing:
        return 'Syncing...';
      case SyncStatus.success:
        return 'Synced';
      case SyncStatus.error:
        return 'Sync failed';
      case SyncStatus.pending:
        return 'Pending';
      default:
        return 'Idle';
    }
  }

  bool _showPendingCount(int pendingCount) => showPendingCount && pendingCount > 0;

  Color _getSyncColor(SyncStatus syncStatus) {
    switch (syncStatus) {
      case SyncStatus.syncing:
        return Colors.orange;
      case SyncStatus.success:
        return Colors.green;
      case SyncStatus.error:
        return Colors.red;
      case SyncStatus.pending:
        return Colors.orange;
      default:
        return Colors.grey;
    }
  }

  IconData _getSyncIcon(SyncStatus syncStatus) {
    switch (syncStatus) {
      case SyncStatus.syncing:
        return Icons.sync;
      case SyncStatus.success:
        return Icons.check_circle;
      case SyncStatus.error:
        return Icons.error;
      case SyncStatus.pending:
        return Icons.schedule;
      default:
        return Icons.sync_disabled;
    }
  }
}

/// Compact sync indicator for app bars
class CompactSyncIndicator extends ConsumerWidget {
  final VoidCallback? onSyncPressed;

  const CompactSyncIndicator({super.key, this.onSyncPressed});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final syncStatus = ref.watch(syncStatusProvider);
    final isOnline = ref.watch(isOnlineProvider);
    final pendingCount = 0; // TODO: Get from sync queue

    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        // Online/Offline indicator
        const OfflineIndicator(
          showText: false,
          showIcon: true,
          iconSize: 18,
        ),
        const SizedBox(width: 8),
        // Sync status
        if (ref.watch(syncStatusProvider) != SyncStatus.idle) ...[
          _buildStatusIndicator(ref, syncStatus),
        ],
        const SizedBox(width: 8),
        // Sync button
        if (onSyncPressed != null)
          IconButton(
            onPressed: onSyncPressed,
            icon: Icon(
              _getSyncIcon(syncStatus),
              size: 20,
              color: _getSyncColor(syncStatus),
            ),
            tooltip: 'Sync now',
          ),
      ],
    );
  }

  Widget _buildStatusIndicator(WidgetRef ref, SyncStatus syncStatus) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: _getBackgroundColor(ref, syncStatus),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          _buildStatusIcon(ref, syncStatus),
          const SizedBox(width: 4),
          Text(
            _getStatusText(ref, syncStatus),
            style: TextStyle(
              color: _getTextColor(ref, syncStatus),
              fontSize: 11,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }

  Color _getBackgroundColor(WidgetRef ref, SyncStatus syncStatus) {
    switch (syncStatus) {
      case SyncStatus.syncing:
        return Colors.orange.withValues(alpha: 0.2);
      case SyncStatus.success:
        return Colors.green.withValues(alpha: 0.2);
      case SyncStatus.error:
        return Colors.red.withValues(alpha: 0.2);
      case SyncStatus.pending:
        return Colors.orange.withValues(alpha: 0.2);
      default:
        return Colors.transparent;
    }
  }

  Color _getTextColor(WidgetRef ref, SyncStatus syncStatus) {
    switch (syncStatus) {
      case SyncStatus.syncing:
        return Colors.orange;
      case SyncStatus.success:
        return Colors.green;
      case SyncStatus.error:
        return Colors.red;
      case SyncStatus.pending:
        return Colors.orange;
      default:
        return Colors.grey;
    }
  }

  Widget _buildStatusIcon(WidgetRef ref, SyncStatus syncStatus) {
    switch (syncStatus) {
      case SyncStatus.syncing:
        return const SizedBox(
          width: 12,
          height: 12,
          child: CircularProgressIndicator(strokeWidth: 1.5, color: Colors.orange),
        );
      case SyncStatus.success:
        return const Icon(Icons.check_circle, color: Colors.green, size: 14);
      case SyncStatus.error:
        return const Icon(Icons.error, color: Colors.red, size: 14);
      case SyncStatus.pending:
        return const Icon(Icons.schedule, color: Colors.orange, size: 14);
      default:
        return const Icon(Icons.sync_disabled, color: Colors.grey, size: 14);
    }
  }

  String _getStatusText(WidgetRef ref, SyncStatus syncStatus) {
    switch (syncStatus) {
      case SyncStatus.syncing:
        return 'Syncing...';
      case SyncStatus.success:
        return 'Synced';
      case SyncStatus.error:
        return 'Failed';
      case SyncStatus.pending:
        return 'Pending';
      default:
        return 'Idle';
    }
  }

  Color _getSyncColor(SyncStatus syncStatus) {
    switch (syncStatus) {
      case SyncStatus.syncing:
        return Colors.orange;
      case SyncStatus.success:
        return Colors.green;
      case SyncStatus.error:
        return Colors.red;
      case SyncStatus.pending:
        return Colors.orange;
      default:
        return Colors.grey;
    }
  }

  IconData _getSyncIcon(SyncStatus syncStatus) {
    switch (syncStatus) {
      case SyncStatus.syncing:
        return Icons.sync;
      case SyncStatus.success:
        return Icons.check_circle;
      case SyncStatus.error:
        return Icons.error;
      case SyncStatus.pending:
        return Icons.schedule;
      default:
        return Icons.sync_disabled;
    }
  }
}

/// Floating sync button for pages
class FloatingSyncButton extends ConsumerWidget {
  final VoidCallback? onPressed;
  final String? tooltip;

  const FloatingSyncButton({super.key, this.onPressed, this.tooltip});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final syncStatus = ref.watch(syncStatusProvider);
    final isOnline = ref.watch(isOnlineProvider);

    if (!isOnline) return const SizedBox.shrink();

    // Define helper functions first
    String getButtonText(SyncStatus syncStatus) {
      switch (syncStatus) {
        case SyncStatus.syncing:
          return 'Syncing...';
        case SyncStatus.success:
          return 'Synced';
        case SyncStatus.error:
          return 'Retry Sync';
        case SyncStatus.pending:
          return 'Pending Sync';
        default:
          return 'Sync Now';
      }
    }

    Color getBackgroundColor(SyncStatus syncStatus) {
      switch (syncStatus) {
        case SyncStatus.syncing:
          return Colors.orange;
        case SyncStatus.success:
          return Colors.green;
        case SyncStatus.error:
          return Colors.red;
        case SyncStatus.pending:
          return Colors.orange;
        default:
          return Theme.of(context).primaryColor;
      }
    }

    Color getForegroundColor(SyncStatus syncStatus) {
      switch (syncStatus) {
        case SyncStatus.success:
          return Colors.white;
        case SyncStatus.error:
          return Colors.white;
        default:
          return Theme.of(context).colorScheme.onPrimary;
      }
    }

    Widget buildIcon(SyncStatus syncStatus) {
      switch (syncStatus) {
        case SyncStatus.syncing:
          return const SizedBox(
            width: 20,
            height: 20,
            child: CircularProgressIndicator(
              strokeWidth: 2,
              valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
            ),
          );
        case SyncStatus.success:
          return const Icon(Icons.check_circle, size: 20);
        case SyncStatus.error:
          return const Icon(Icons.error, size: 20);
        case SyncStatus.pending:
          return const Icon(Icons.schedule, size: 20);
        default:
          return const Icon(Icons.sync, size: 20);
      }
    }

    return FloatingActionButton.extended(
      onPressed: onPressed,
      tooltip: tooltip ?? 'Sync now',
      icon: buildIcon(syncStatus),
      label: Text(getButtonText(syncStatus)),
      backgroundColor: getBackgroundColor(syncStatus),
      foregroundColor: getForegroundColor(syncStatus),
      elevation: 4,
    );
  }
}

/// Widget to show offline data banner
class OfflineDataBanner extends ConsumerWidget {
  final String message;
  final Widget? trailing;

  const OfflineDataBanner({
    super.key,
    this.message = 'Showing cached data. Pull to refresh when online.',
    this.trailing,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final isOnline = ref.watch(isOnlineProvider);

    if (isOnline) return const SizedBox.shrink();

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.orange.withValues(alpha: 0.1),
        border: Border(
          bottom: BorderSide(color: Colors.orange.withValues(alpha: 0.3)),
        ),
      ),
      child: Row(
        children: [
          const Icon(Icons.wifi_off, color: Colors.orange, size: 20),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              message,
              style: const TextStyle(
                color: Colors.orange,
                fontSize: 13,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
          if (trailing != null) trailing!,
        ],
      ),
    );
  }
}
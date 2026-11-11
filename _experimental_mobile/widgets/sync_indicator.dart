import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:connectivity_plus/connectivity_plus.dart';

import 'offline_indicator.dart';

/// Sync status enum
enum SyncStatus {
  idle,
  syncing,
  success,
  error,
  pending,
}

/// Provider for sync status
final syncStatusProvider = StateNotifierProvider<SyncStatusNotifier, SyncStatus>((ref) {
  return SyncStatusNotifier();
});

class SyncStatusNotifier extends StateNotifier<SyncStatus> {
  SyncStatusNotifier() : super(SyncStatus.idle);
  
  void setSyncing() => state = SyncStatus.syncing;
  void setSuccess() => state = SyncStatus.success;
  void setError() => state = SyncStatus.error;
  void setPending() => state = SyncStatus.pending;
  void setIdle() => state = SyncStatus.idle;
}

/// Widget showing sync status with manual sync button
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
      return _buildCompact(context, ref);
    }
    
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: _getBackgroundColor(),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: _getBorderColor()),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Status icon
          _buildStatusIcon(),
          const SizedBox(width: 8),
          // Status text
          Text(
            _getStatusText(),
            style: TextStyle(
              color: _getTextColor(),
              fontSize: 13,
              fontWeight: FontWeight.w600,
            ),
          ),
          if (_showPendingCount()) ...[
            const SizedBox(width: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
              decoration: BoxDecoration(
                color: Colors.orange.withValues(alpha: 0.2),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Text(
                '$pendingCount pending',
                style: const TextStyle(
                  color: Colors.orange,
                  fontSize: 11,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
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
    
    Color _getBackgroundColor() {
      return Colors.orange.withValues(alpha: 0.15);
    }
    
    Color _getBorderColor() {
      return Colors.orange.withValues(alpha: 0.3);
    }
    
    Color _getTextColor() {
      return Colors.orange;
    }
    
    Widget _buildStatusIcon() {
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
    
    String _getStatusText() {
      switch (syncStatus) {
        case SyncStatus.syncing: return 'Syncing...';
        case SyncStatus.success: return 'Synced';
        case SyncStatus.error: return 'Sync failed';
        case SyncStatus.pending: return 'Pending';
        default: return 'Idle';
      }
    }
    
    bool _showPendingCount() => showPendingCount && pendingCount > 0;
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
        OfflineIndicator(
          showText: false,
          showIcon: true,
          iconSize: 18,
        ),
        const SizedBox(width: 8),
        // Sync status
        if (ref.watch(syncStatusProvider) != SyncStatus.idle) ...[
          _buildStatusIndicator(),
        ],
        const SizedBox(width: 8),
        // Sync button
        if (onSyncPressed != null)
          IconButton(
            onPressed: onSyncPressed,
            icon: Icon(
              _getSyncIcon(),
              size: 20,
              color: _getSyncColor(),
            ),
            tooltip: 'Sync now',
          ),
      ],
    );
    
    Widget _buildStatusIndicator() {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
        decoration: BoxDecoration(
          color: _getBackgroundColor(),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            _buildStatusIcon(),
            const SizedBox(width: 4),
            Text(
              _getStatusText(),
              style: TextStyle(
                color: _getTextColor(),
                fontSize: 11,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ),
      );
    
    IconData _getSyncIcon() {
      final status = ref.watch(syncStatusProvider);
      switch (status) {
        case SyncStatus.syncing: return Icons.sync;
        case SyncStatus.success: return Icons.check_circle;
        case SyncStatus.error: return Icons.error;
        case SyncStatus.pending: return Icons.schedule;
        default: return Icons.sync_disabled;
      }
    }
    
    Color _getSyncColor() {
      final status = ref.watch(syncStatusProvider);
      switch (status) {
        case SyncStatus.syncing: return Colors.orange;
        case SyncStatus.success: return Colors.green;
        case SyncStatus.error: return Colors.red;
        case SyncStatus.pending: return Colors.orange;
        default: return Colors.grey;
      }
    }
    
    Color _getBackgroundColor() {
      final status = ref.watch(syncStatusProvider);
      switch (status) {
        case SyncStatus.syncing: return Colors.orange.withValues(alpha: 0.2);
        case SyncStatus.success: return Colors.green.withValues(alpha: 0.2);
        case SyncStatus.error: return Colors.red.withValues(alpha: 0.2);
        case SyncStatus.pending: return Colors.orange.withValues(alpha: 0.2);
        default: return Colors.transparent;
      }
    }
    
    Color _getTextColor() {
      final status = ref.watch(syncStatusProvider);
      switch (status) {
        case SyncStatus.syncing: return Colors.orange;
        case SyncStatus.success: return Colors.green;
        case SyncStatus.error: return Colors.red;
        case SyncStatus.pending: return Colors.orange;
        default: return Colors.grey;
      }
    }
    
    Widget _buildStatusIcon() {
      final status = ref.watch(syncStatusProvider);
      switch (status) {
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
    
    String _getStatusText() {
      switch (ref.watch(syncStatusProvider)) {
        case SyncStatus.syncing: return 'Syncing...';
        case SyncStatus.success: return 'Synced';
        case SyncStatus.error: return 'Failed';
        case SyncStatus.pending: return 'Pending';
        default: return 'Idle';
      }
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
    
    return FloatingActionButton.extended(
      onPressed: onPressed,
      tooltip: tooltip ?? 'Sync now',
      icon: _buildIcon(),
      label: Text(_getButtonText()),
      backgroundColor: _getBackgroundColor(),
      foregroundColor: _getForegroundColor(),
      elevation: 4,
      extended: true,
    );
    
    String _getButtonText() {
      switch (ref.watch(syncStatusProvider)) {
        case SyncStatus.syncing: return 'Syncing...';
        case SyncStatus.success: return 'Synced';
        case SyncStatus.error: return 'Retry Sync';
        case SyncStatus.pending: return 'Pending Sync';
        default: return 'Sync Now';
      }
    }
    
    Color _getBackgroundColor() {
      switch (ref.watch(syncStatusProvider)) {
        case SyncStatus.syncing: return Colors.orange;
        case SyncStatus.success: return Colors.green;
        case SyncStatus.error: return Colors.red;
        case SyncStatus.pending: return Colors.orange;
        default: return Theme.of(context).primaryColor;
      }
    }
    
    Color _getForegroundColor() {
      switch (ref.watch(syncStatusProvider)) {
        case SyncStatus.success: return Colors.white;
        case SyncStatus.error: return Colors.white;
        default: return Theme.of(context).colorScheme.onPrimary;
      }
    }
    
    Widget _buildIcon() {
      switch (ref.watch(syncStatusProvider)) {
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

/// Mixin for offline-first data loading
mixin OfflineFirstMixin<T extends ConsumerStatefulWidget> on ConsumerState<T> {
  bool _isOnline = true;
  bool _isLoading = false;
  Object? _error;
  dynamic _data;
  StreamSubscription? _connectivitySubscription;
  
  @override
  void initState() {
    super.initState();
    _initConnectivity();
    _loadData();
  }
  
  void _initConnectivity() {
    final connectivity = ref.read(connectivityProvider.stream);
    _connectivitySubscription = connectivity.listen((results) {
      final wasOnline = _isOnline;
      _isOnline = results.isNotEmpty && !results.contains(ConnectivityResult.none);
      
      if (mounted && !wasOnline && _isOnline) {
        _refreshData();
      }
      
      if (mounted) setState(() => _isOnline = _isOnline);
    });
    
    Connectivity().checkConnectivity().then((results) {
      if (mounted) {
        setState(() => _isOnline = results.isNotEmpty && !results.contains(ConnectivityResult.none));
      }
    });
  }
  
  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    
    try {
      final cachedData = await _loadFromCache();
      if (mounted && cachedData != null) {
        setState(() {
          _data = cachedData;
          _error = null;
        });
      }
      
      if (_isOnline) {
        await _refreshData();
      }
    } catch (e) {
      if (mounted) setState(() => _error = e);
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }
  
  Future<void> _refreshData() async {
    if (!_isOnline || _isLoading) return;
    
    try {
      final freshData = await _fetchFromServer();
      if (mounted) {
        setState(() {
          _data = freshData;
          _error = null;
        });
      }
    } catch (e) {
      if (mounted) setState(() => _error = e);
    }
  }
  
  Future<dynamic> _loadFromCache();
  Future<dynamic> _fetchFromServer();
  Future<void> _refreshData();
  
  @override
  void dispose() {
    _connectivitySubscription?.cancel();
    super.dispose();
  }
}
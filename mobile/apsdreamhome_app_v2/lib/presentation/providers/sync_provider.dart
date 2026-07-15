import 'package:flutter_riverpod/legacy.dart';

import '../../core/services/sync_service.dart';

final syncStateProvider = StateNotifierProvider<SyncNotifier, SyncState>((ref) {
  return SyncNotifier();
});

class SyncNotifier extends StateNotifier<SyncState> {
  SyncNotifier() : super(const SyncState.initial());

  Future<void> refreshStatus() async {
    try {
      final pendingItems = await SyncService().getPendingSyncItems();
      state = SyncState.pending(pendingItems.length);
    } catch (e) {
      state = SyncState.error(e.toString());
    }
  }

  Future<void> sync() async {
    try {
      state = const SyncState.syncing();
      await SyncService().performSync();
      await refreshStatus();
    } catch (e) {
      state = SyncState.error(e.toString());
    }
  }
}

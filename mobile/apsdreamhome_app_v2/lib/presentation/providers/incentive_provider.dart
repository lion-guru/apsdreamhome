import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/services/database_helper.dart';
import '../../core/services/sync_service.dart';
import '../../core/constants/app_constants.dart';
import '../../data/models/incentive_model.dart';

final incentiveProvider = StateNotifierProvider<IncentiveNotifier, AsyncValue<List<Incentive>>>((ref) {
  return IncentiveNotifier(ref.watch(syncServiceProvider));
});

class IncentiveNotifier extends StateNotifier<AsyncValue<List<Incentive>>> {
  final SyncService _syncService;

  IncentiveNotifier(this._syncService) : super(const AsyncValue.loading()) {
    _loadIncentives();
  }

  Future<void> _loadIncentives() async {
    try {
      final data = await DatabaseHelper.query(AppConstants.incentivesTable);
      final incentives = data.map((json) => Incentive.fromJson(json)).toList();
      
      // Sort by year and month (newest first)
      incentives.sort((a, b) {
        if (a.year != b.year) return b.year.compareTo(a.year);
        return b.month.compareTo(a.month);
      });
      
      state = AsyncValue.data(incentives);
    } catch (e, stack) {
      state = AsyncValue.error(e, stack);
    }
  }

  Future<void> refresh() async {
    state = const AsyncValue.loading();
    await _syncService.performSync();
    await _loadIncentives();
  }
}

import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_riverpod/legacy.dart';

import '../../core/services/api_service.dart';
import '../../core/utils/logger.dart';
import '../../core/constants/app_constants.dart';

final mlmProvider =
    StateNotifierProvider<MLMNotifier, AsyncValue<Map<String, dynamic>>>((ref) {
  return MLMNotifier(ref);
});

class MLMNotifier extends StateNotifier<AsyncValue<Map<String, dynamic>>> {

  MLMNotifier(Ref ref) : super(const AsyncValue.loading());

  Future<void> fetchMlmSummary() async {
    try {
      final api = ApiService();
      final response = await api.get(AppConstants.mlmSummaryEndpoint);

      if (response['success'] == true) {
        state = AsyncValue.data(response['data'] as Map<String, dynamic>);
      } else {
        throw Exception(response['message'] ?? 'Failed to fetch MLM summary');
      }
    } catch (e) {
      AppLogger.error('Error fetching MLM summary', e);
      state = AsyncValue.error(e, StackTrace.current);
    }
  }

  Future<List<dynamic>> fetchGenealogy() async {
    try {
      final api = ApiService();
      final response = await api.get(AppConstants.genealogyEndpoint);

      if (response['success'] == true) {
        return response['data'] as List<dynamic>;
      } else {
        throw Exception(response['message'] ?? 'Failed to fetch genealogy');
      }
    } catch (e) {
      AppLogger.error('Error fetching genealogy', e);
      rethrow;
    }
  }

  Future<List<dynamic>> fetchBusinessBreakdown() async {
    try {
      final api = ApiService();
      final response = await api.get(AppConstants.businessBreakdownEndpoint);

      if (response['success'] == true) {
        return response['data'] as List<dynamic>;
      } else {
        throw Exception(
            response['message'] ?? 'Failed to fetch business breakdown');
      }
    } catch (e) {
      AppLogger.error('Error fetching business breakdown', e);
      rethrow;
    }
  }

  Future<Map<String, dynamic>> requestPayout(
      double amount, String remarks) async {
    try {
      final api = ApiService();
      final response = await api.post(AppConstants.requestPayoutEndpoint, data: {
        'amount': amount,
        'remarks': remarks,
      });

      return response;
    } catch (e) {
      AppLogger.error('Error requesting payout', e);
      rethrow;
    }
  }
}

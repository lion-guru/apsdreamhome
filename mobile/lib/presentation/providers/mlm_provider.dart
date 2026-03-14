import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:dio/dio.dart';
import '../../../core/constants/app_constants.dart';
import '../../providers/auth_provider.dart';

final mlmProvider = StateNotifierProvider<MLMNotifier, AsyncValue<Map<String, dynamic>>>((ref) {
  return MLMNotifier(ref);
});

class MLMNotifier extends StateNotifier<AsyncValue<Map<String, dynamic>>> {
  final Ref _ref;
  final Dio _dio = Dio(BaseOptions(baseUrl: AppConstants.baseUrl));

  MLMNotifier(this._ref) : super(const AsyncValue.loading());

  Future<void> fetchMlmSummary() async {
    try {
      final token = await _ref.read(authProvider.notifier).getToken();
      final response = await _dio.get(
        '${AppConstants.apiVersion}${AppConstants.mlmSummaryEndpoint}',
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );

      if (response.data['success']) {
        state = AsyncValue.data(response.data['data']);
      } else {
        throw Exception(response.data['message'] ?? 'Failed to fetch MLM summary');
      }
    } catch (e) {
      state = AsyncValue.error(e, StackTrace.current);
    }
  }

  Future<List<dynamic>> fetchGenealogy() async {
    try {
      final token = await _ref.read(authProvider.notifier).getToken();
      final response = await _dio.get(
        '${AppConstants.apiVersion}${AppConstants.genealogyEndpoint}',
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );

      if (response.data['success']) {
        return response.data['data'] as List<dynamic>;
      } else {
        throw Exception(response.data['message'] ?? 'Failed to fetch genealogy');
      }
    } catch (e) {
      rethrow;
    }
  }

  Future<List<dynamic>> fetchBusinessBreakdown() async {
    try {
      final token = await _ref.read(authProvider.notifier).getToken();
      final response = await _dio.get(
        '${AppConstants.apiVersion}${AppConstants.businessBreakdownEndpoint}',
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );

      if (response.data['success']) {
        return response.data['data'] as List<dynamic>;
      } else {
        throw Exception(response.data['message'] ?? 'Failed to fetch business breakdown');
      }
    } catch (e) {
      rethrow;
    }
  }

  Future<Map<String, dynamic>> requestPayout(double amount, String remarks) async {
    try {
      final token = await _ref.read(authProvider.notifier).getToken();
      final response = await _dio.post(
        '${AppConstants.apiVersion}${AppConstants.requestPayoutEndpoint}',
        data: {
          'amount': amount,
          'remarks': remarks,
        },
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );

      return response.data;
    } catch (e) {
      rethrow;
    }
  }
}

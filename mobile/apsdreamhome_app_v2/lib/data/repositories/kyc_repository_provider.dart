import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/services/api_service.dart';
import 'kyc_repository.dart';

/// KYC Repository Provider
final kycRepositoryProvider = Provider<KYCRepository>((ref) {
  final apiService = ref.watch(apiServiceProvider);
  return KYCRepository(apiService);
});

/// API Service Provider
final apiServiceProvider = Provider<ApiService>((ref) {
  return ApiService();
});



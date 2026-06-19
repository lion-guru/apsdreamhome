import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/services/api_service.dart';
import '../../core/utils/logger.dart';

/// Financial Automation Service
/// Backend payout processing + TDS/GST compliance
/// Now uses REST API (MySQL) instead of Firestore
class PayoutService {
  // Tax Configuration
  static const double _tdsRate = 0.05; // 5% TDS
  static const double _minTdsThreshold = 30000; // ₹30,000 per month

  // ==================== INSTANT PAYOUTS ====================

  /// Request Withdrawal
  Future<Map<String, dynamic>> requestWithdrawal({
    required String associateId,
    required String associateName,
    required double amount,
    required String bankAccountNumber,
    required String ifscCode,
    required String accountHolderName,
    String? bankName,
    String mode = 'IMPS',
    String? upiId,
  }) async {
    try {
      AppLogger.info('Processing payout request for $associateName: ₹$amount');

      final response = await ApiService().post(
        '/payouts/process',
        data: {
          'associateId': associateId,
          'amount': amount,
          'mode': mode,
          'bank_account_number': bankAccountNumber,
          'ifsc_code': ifscCode,
          'account_holder_name': accountHolderName,
          'bank_name': bankName ?? 'Unknown',
          'upi_id': upiId,
        },
      );

      if (response['success'] == true) {
        return {
          'success': true,
          'requestId': response['data']?['requestId'] ?? '',
          'transactionId': response['data']?['transactionId'] ?? '',
          'netAmount': amount,
          'message': '₹$amount transfer initiated',
        };
      } else {
        return {'success': false, 'error': response['error'] ?? response['message'] ?? 'Payout failed'};
      }
    } catch (e) {
      AppLogger.error('Error processing payout', e);
      return {'success': false, 'error': 'Internal error: $e'};
    }
  }

  // ==================== TDS & GST COMPLIANCE ====================

  /// Record TDS Deduction (delegates to backend)
  Future<void> _recordTds({
    required String associateId,
    required String associateName,
    required double amount,
    required String payoutId,
    required DateTime month,
  }) async {
    try {
      await ApiService().post(
        '/mlm/record-tds',
        data: {
          'associateId': associateId,
          'amount': amount,
          'payoutId': payoutId,
          'month': month.toIso8601String(),
        },
      );
      AppLogger.info('TDS recorded for $associateName: ₹$amount');
    } catch (e) {
      AppLogger.error('TDS recording failed', e);
    }
  }

  /// Generate Digital Form 16 for Associate
  Future<Map<String, dynamic>> generateForm16({
    required String associateId,
    required int financialYear,
  }) async {
    try {
      final response = await ApiService().get(
        '/mlm/form16',
        queryParameters: {
          'associateId': associateId,
          'financialYear': financialYear,
        },
      );

      if (response['success'] == true) {
        return {
          'success': true,
          'formData': response['data'],
          'message': 'Form 16 generated successfully',
        };
      }
      return {'success': false, 'error': response['error'] ?? 'Form 16 generation failed'};
    } catch (e) {
      AppLogger.error('Error generating Form 16', e);
      return {'success': false, 'error': e.toString()};
    }
  }

  // ==================== PUBLIC METHODS ====================

  /// Get Payout History
  Future<List<Map<String, dynamic>>> getPayoutHistory(String associateId) async {
    try {
      final response = await ApiService().get('/payouts/history');
      if (response['success'] == true && response['data'] != null) {
        final list = response['data'] as List<dynamic>;
        return list.map((item) => item as Map<String, dynamic>).toList();
      }
      return [];
    } catch (e) {
      AppLogger.error('Error getting payout history', e);
      return [];
    }
  }

  /// Get Tax Summary
  Future<Map<String, dynamic>> getTaxSummary(String associateId) async {
    try {
      final response = await ApiService().get(
        '/mlm/tax-summary',
        queryParameters: {'associateId': associateId},
      );
      if (response['success'] == true && response['data'] != null) {
        return response['data'] as Map<String, dynamic>;
      }
      return {
        'currentYearTds': 0.0,
        'projectedAnnualTds': 0.0,
        'tdsRecords': 0,
      };
    } catch (e) {
      AppLogger.error('Error getting tax summary', e);
      return {
        'currentYearTds': 0.0,
        'projectedAnnualTds': 0.0,
        'tdsRecords': 0,
      };
    }
  }

  /// Get pending payouts summary
  Future<Map<String, dynamic>> getPendingPayouts() async {
    try {
      final response = await ApiService().get('/payouts/pending');
      if (response['success'] == true && response['data'] != null) {
        return response['data'] as Map<String, dynamic>;
      }
      return {'total_pending': 0.0, 'count': 0};
    } catch (e) {
      AppLogger.error('Error getting pending payouts', e);
      return {'total_pending': 0.0, 'count': 0};
    }
  }

  /// Process bulk payouts (admin)
  Future<Map<String, dynamic>> processBulkPayouts() async {
    try {
      final response = await ApiService().post('/payouts/process');
      if (response['success'] == true) {
        return {
          'success': true,
          'processed': response['data']?['processed'] ?? 0,
          'message': response['message'] ?? 'Payouts processed',
        };
      }
      return {'success': false, 'error': response['error'] ?? 'Bulk payout failed'};
    } catch (e) {
      AppLogger.error('Error processing bulk payouts', e);
      return {'success': false, 'error': e.toString()};
    }
  }
}

// Provider
final payoutServiceProvider = Provider<PayoutService>((ref) => PayoutService());

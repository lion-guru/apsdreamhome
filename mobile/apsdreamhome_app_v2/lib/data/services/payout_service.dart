import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/utils/logger.dart';

/// Financial Automation Service
/// RazorpayX & Cashfree Integration for Instant Payouts
/// TDS & GST Compliance
class PayoutService {
  final FirebaseFirestore _firestore = FirebaseFirestore.instance;

  // RazorpayX Configuration
  static const String _razorpayXKeyId = 'YOUR_RAZORPAYX_KEY_ID';
  static const String _razorpayXKeySecret = 'YOUR_RAZORPAYX_KEY_SECRET';
  static const String _razorpayXBaseUrl = 'https://api.razorpay.com/v1';

  // Tax Configuration
  static const double _tdsRate = 0.05; // 5% TDS
  static const double _minTdsThreshold = 30000; // ₹30,000 per month

  CollectionReference get _payoutRequests =>
      _firestore.collection('payout_requests');
  CollectionReference get _taxRecords => _firestore.collection('tax_records');
  CollectionReference get _users => _firestore.collection('users');

  // ==================== INSTANT PAYOUTS ====================

  /// Request Withdrawal - 5 Minutes to Bank
  Future<Map<String, dynamic>> requestWithdrawal({
    required String associateId,
    required String associateName,
    required double amount,
    required String bankAccountNumber,
    required String ifscCode,
    required String accountHolderName,
    String? bankName,
    String mode = 'IMPS', // IMPS, UPI, NEFT
    String? upiId,
  }) async {
    try {
      AppLogger.info('Processing payout request for $associateName: ₹$amount');

      // 1. Validate minimum balance
      final userDoc = await _users.doc(associateId).get();
      if (!userDoc.exists) {
        return {'success': false, 'error': 'Associate not found'};
      }

      final userData = userDoc.data() as Map<String, dynamic>;
      final walletBalance =
          (userData['walletBalance'] as num? ?? 0.0).toDouble();
      final pendingPayouts =
          (userData['pendingPayouts'] as num? ?? 0.0).toDouble();

      if (walletBalance - pendingPayouts < amount) {
        return {'success': false, 'error': 'Insufficient balance'};
      }

      // 2. Calculate TDS if applicable
      final monthlyEarnings = await _getMonthlyEarnings(associateId);
      final isTdsApplicable = monthlyEarnings > _minTdsThreshold;
      final tdsAmount = isTdsApplicable ? amount * _tdsRate : 0.0;
      final double netAmount = amount - tdsAmount;

      // 3. Create Payout Request
      final requestId = 'REQ${DateTime.now().millisecondsSinceEpoch}';
      final payoutRequest = {
        'id': requestId,
        'associateId': associateId,
        'associateName': associateName,
        'requestedAmount': amount,
        'tdsAmount': tdsAmount,
        'netAmount': netAmount,
        'isTdsApplicable': isTdsApplicable,
        'bankDetails': {
          'accountNumber': _maskAccountNumber(bankAccountNumber),
          'ifscCode': ifscCode,
          'accountHolderName': accountHolderName,
          'bankName': bankName ?? 'Unknown',
          'upiId': upiId,
        },
        'mode': mode,
        'status': 'pending',
        'requestedAt': DateTime.now(),
        'processedAt': null,
        'transactionId': null,
        'failureReason': null,
        'createdAt': DateTime.now(),
      };

      await _payoutRequests.doc(requestId).set(payoutRequest);

      // 4. Update pending balance
      await _users.doc(associateId).update({
        'pendingPayouts': pendingPayouts + amount,
      });

      // 5. Trigger Instant Payout via RazorpayX/Cashfree
      final payoutResult = await _processInstantPayout(
        requestId: requestId,
        accountNumber: bankAccountNumber,
        ifscCode: ifscCode,
        accountHolderName: accountHolderName,
        amount: netAmount,
        mode: mode,
        upiId: upiId,
      );

      if (payoutResult['success'] == true) {
        // Update to completed
        await _payoutRequests.doc(requestId).update({
          'status': 'completed',
          'transactionId': payoutResult['transactionId'],
          'processedAt': DateTime.now(),
        });

        // Update user wallet
        await _users.doc(associateId).update({
          'walletBalance': walletBalance - amount,
          'pendingPayouts': pendingPayouts,
          'totalWithdrawn': (userData['totalWithdrawn'] ?? 0.0) + amount,
        });

        // Record tax if TDS applicable
        if (isTdsApplicable) {
          await _recordTds(
            associateId: associateId,
            associateName: associateName,
            amount: tdsAmount,
            payoutId: requestId,
            month: DateTime.now(),
          );
        }

        return {
          'success': true,
          'requestId': requestId,
          'transactionId': payoutResult['transactionId'],
          'netAmount': netAmount,
          'tdsDeducted': tdsAmount,
          'message': '₹$netAmount transferred to your bank account',
        };
      } else {
        // Mark as failed
        await _payoutRequests.doc(requestId).update({
          'status': 'failed',
          'failureReason': payoutResult['error'],
          'processedAt': DateTime.now(),
        });

        // Revert pending balance
        await _users.doc(associateId).update({
          'pendingPayouts': pendingPayouts,
        });

        return {'success': false, 'error': payoutResult['error']};
      }
    } catch (e) {
      AppLogger.error('Error processing payout', e);
      return {'success': false, 'error': 'Internal error: $e'};
    }
  }

  /// Process Instant Payout via RazorpayX
  Future<Map<String, dynamic>> _processInstantPayout({
    required String requestId,
    required String accountNumber,
    required String ifscCode,
    required String accountHolderName,
    required double amount,
    required String mode,
    String? upiId,
  }) async {
    try {
      // Use UPI if available for instant transfer
      if (upiId != null && upiId.isNotEmpty && mode == 'UPI') {
        return await _processUpiPayout(
          upiId: upiId,
          amount: amount,
          requestId: requestId,
        );
      }

      // RazorpayX Payout
      final auth =
          base64Encode(utf8.encode('$_razorpayXKeyId:$_razorpayXKeySecret'));

      final response = await http.post(
        Uri.parse('$_razorpayXBaseUrl/payouts'),
        headers: {
          'Authorization': 'Basic $auth',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'account_number': '2323230029292929', // RazorpayX Virtual Account
          'amount': (amount * 100).toInt(), // Amount in paise
          'currency': 'INR',
          'mode': mode.toLowerCase(),
          'purpose': 'commission_payout',
          'fund_account': {
            'account_type': 'bank_account',
            'bank_account': {
              'name': accountHolderName,
              'ifsc': ifscCode,
              'account_number': accountNumber,
            },
          },
          'queue_if_low_balance': false,
          'reference_id': requestId,
          'narration': 'APS Dream Home Commission',
        }),
      );

      if (response.statusCode == 200 || response.statusCode == 201) {
        final data = jsonDecode(response.body);
        return {
          'success': true,
          'transactionId': data['id'],
          'status': data['status'],
        };
      } else {
        final error = jsonDecode(response.body);
        AppLogger.error(
            'RazorpayX payout failed: ${error['error']['description']}');
        return {
          'success': false,
          'error': error['error']['description'] ?? 'Payout failed',
        };
      }
    } catch (e) {
      AppLogger.error('Error in instant payout', e);
      return {'success': false, 'error': 'Network error: $e'};
    }
  }

  /// Process UPI Payout
  Future<Map<String, dynamic>> _processUpiPayout({
    required String upiId,
    required double amount,
    required String requestId,
  }) async {
    try {
      final auth =
          base64Encode(utf8.encode('$_razorpayXKeyId:$_razorpayXKeySecret'));

      final response = await http.post(
        Uri.parse('$_razorpayXBaseUrl/payouts'),
        headers: {
          'Authorization': 'Basic $auth',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'account_number': '2323230029292929',
          'amount': (amount * 100).toInt(),
          'currency': 'INR',
          'mode': 'upi',
          'purpose': 'commission_payout',
          'fund_account': {
            'account_type': 'vpa',
            'vpa': {
              'address': upiId,
            },
          },
          'reference_id': requestId,
          'narration': 'APS Commission',
        }),
      );

      if (response.statusCode == 200 || response.statusCode == 201) {
        final data = jsonDecode(response.body);
        return {
          'success': true,
          'transactionId': data['id'],
          'status': data['status'],
        };
      } else {
        final error = jsonDecode(response.body);
        return {
          'success': false,
          'error': error['error']['description'] ?? 'UPI payout failed',
        };
      }
    } catch (e) {
      return {'success': false, 'error': 'UPI payout error: $e'};
    }
  }

  // ==================== TDS & GST COMPLIANCE ====================

  /// Record TDS Deduction
  Future<void> _recordTds({
    required String associateId,
    required String associateName,
    required double amount,
    required String payoutId,
    required DateTime month,
  }) async {
    final tdsRecord = {
      'associateId': associateId,
      'associateName': associateName,
      'tdsAmount': amount,
      'payoutId': payoutId,
      'month': month,
      'monthString': '${month.year}-${month.month.toString().padLeft(2, '0')}',
      'createdAt': DateTime.now(),
      'form16Generated': false,
    };

    await _taxRecords.add(tdsRecord);

    AppLogger.info('TDS recorded for $associateName: ₹$amount');
  }

  /// Generate Digital Form 16 for Associate
  Future<Map<String, dynamic>> generateForm16({
    required String associateId,
    required int financialYear,
  }) async {
    try {
      final startDate = DateTime(financialYear - 1, 4, 1); // April 1
      final endDate = DateTime(financialYear, 3, 31); // March 31

      // Get all TDS records for the financial year
      final tdsSnapshot = await _taxRecords
          .where('associateId', isEqualTo: associateId)
          .where('month', isGreaterThanOrEqualTo: startDate)
          .where('month', isLessThanOrEqualTo: endDate)
          .get();

      double totalTds = 0;
      final monthlyBreakdown = <Map<String, dynamic>>[];

      for (final doc in tdsSnapshot.docs) {
        final data = doc.data() as Map<String, dynamic>;
        totalTds += (data['tdsAmount'] as num? ?? 0.0).toDouble();
        monthlyBreakdown.add({
          'month': data['monthString'],
          'amount': data['tdsAmount'],
          'payoutId': data['payoutId'],
        });
      }

      // Get associate details
      final userDoc = await _users.doc(associateId).get();
      final userData = userDoc.data() as Map<String, dynamic>?;

      final form16Data = {
        'formId': 'FORM16-${associateId.substring(0, 8)}-$financialYear',
        'associateId': associateId,
        'associateName': userData?['name'] ?? 'Unknown',
        'panNumber': userData?['panNumber'] ?? 'Not Provided',
        'financialYear':
            '$financialYear-${(financialYear + 1).toString().substring(2)}',
        'employerName': 'APS Dream Home Pvt Ltd',
        'employerPan': 'AAICS1234F',
        'employerTan': 'GRKP01234B',
        'totalCommissionPaid':
            await _getTotalCommissionForYear(associateId, startDate, endDate),
        'totalTdsDeducted': totalTds,
        'monthlyBreakdown': monthlyBreakdown,
        'generatedAt': DateTime.now(),
        'certificateUrl': null, // Will be set after PDF generation
      };

      // Save to Firestore
      await _firestore.collection('form16_certificates').add(form16Data);

      return {
        'success': true,
        'formData': form16Data,
        'message': 'Form 16 generated successfully',
      };
    } catch (e) {
      AppLogger.error('Error generating Form 16', e);
      return {'success': false, 'error': e.toString()};
    }
  }

  // ==================== HELPER METHODS ====================

  Future<double> _getMonthlyEarnings(String associateId) async {
    final now = DateTime.now();
    final startOfMonth = DateTime(now.year, now.month, 1);

    final snapshot = await _firestore
        .collection('commissions')
        .where('associateId', isEqualTo: associateId)
        .where('createdAt', isGreaterThanOrEqualTo: startOfMonth)
        .get();

    double total = 0;
    for (final doc in snapshot.docs) {
      final data = doc.data();
      total += (data['commissionAmount'] as num? ?? 0.0).toDouble();
    }

    return total;
  }

  Future<double> _getTotalCommissionForYear(
    String associateId,
    DateTime start,
    DateTime end,
  ) async {
    final snapshot = await _firestore
        .collection('commissions')
        .where('associateId', isEqualTo: associateId)
        .where('createdAt', isGreaterThanOrEqualTo: start)
        .where('createdAt', isLessThanOrEqualTo: end)
        .get();

    double total = 0;
    for (final doc in snapshot.docs) {
      final data = doc.data();
      total += (data['commissionAmount'] as num? ?? 0.0).toDouble();
    }

    return total;
  }

  String _maskAccountNumber(String accountNumber) {
    if (accountNumber.length <= 4) return accountNumber;
    return 'XXXX${accountNumber.substring(accountNumber.length - 4)}';
  }

  // ==================== PUBLIC METHODS ====================

  /// Get Payout History
  Future<List<Map<String, dynamic>>> getPayoutHistory(
      String associateId) async {
    final snapshot = await _payoutRequests
        .where('associateId', isEqualTo: associateId)
        .orderBy('requestedAt', descending: true)
        .get();

    return snapshot.docs
        .map((doc) => doc.data() as Map<String, dynamic>)
        .toList();
  }

  /// Get Tax Summary
  Future<Map<String, dynamic>> getTaxSummary(String associateId) async {
    final now = DateTime.now();
    final yearStart = DateTime(now.year, 4, 1);

    final tdsSnapshot = await _taxRecords
        .where('associateId', isEqualTo: associateId)
        .where('month', isGreaterThanOrEqualTo: yearStart)
        .get();

    double totalTds = 0;
    for (final doc in tdsSnapshot.docs) {
      final data = doc.data() as Map<String, dynamic>;
      totalTds += (data['tdsAmount'] as num? ?? 0.0).toDouble();
    }

    return {
      'currentYearTds': totalTds,
      'projectedAnnualTds': totalTds * (12 / now.month),
      'tdsRecords': tdsSnapshot.docs.length,
    };
  }
}

// Provider
final payoutServiceProvider = Provider<PayoutService>((ref) => PayoutService());

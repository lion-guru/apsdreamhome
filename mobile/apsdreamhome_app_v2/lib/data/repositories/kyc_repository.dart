import '../../core/services/api_service.dart';
import '../../domain/models/kyc_models.dart' as domain;

/// Repository for KYC (Know Your Customer) operations
/// Handles PAN and Aadhaar verification
class KYCRepository {
  final ApiService _apiService;

  KYCRepository(this._apiService);

  /// Verify PAN number
  ///
  /// Returns KYCVerificationResult with success status and data
  Future<KYCVerificationResult> verifyPAN({
    required String pan,
    String? name,
  }) async {
    try {
      final response = await _apiService.post(
        '/kyc/verify-pan',
        data: {
          'pan': pan,
          'name': name ?? '',
        },
      );

      if (response['status'] == 'success') {
        return KYCVerificationResult(
          success: true,
          message:
              response['message'] as String? ?? 'PAN verified successfully',
          data: response['data'] as Map<String, dynamic>?,
        );
      } else {
        return KYCVerificationResult(
          success: false,
          message: response['message'] as String? ?? 'PAN verification failed',
          data: response['data'] as Map<String, dynamic>?,
        );
      }
    } catch (e) {
      return KYCVerificationResult(
        success: false,
        message: 'Error verifying PAN: $e',
      );
    }
  }

  /// Verify Aadhaar number
  ///
  /// Returns KYCVerificationResult with success status and data
  Future<KYCVerificationResult> verifyAadhaar({
    required String aadhaar,
  }) async {
    try {
      // Remove any non-digit characters
      final cleanAadhaar = aadhaar.replaceAll(RegExp(r'\D'), '');

      final response = await _apiService.post(
        '/kyc/verify-aadhaar',
        data: {
          'aadhaar': cleanAadhaar,
        },
      );

      if (response['status'] == 'success') {
        return KYCVerificationResult(
          success: true,
          message:
              response['message'] as String? ?? 'Aadhaar verified successfully',
          data: response['data'] as Map<String, dynamic>?,
        );
      } else {
        return KYCVerificationResult(
          success: false,
          message:
              response['message'] as String? ?? 'Aadhaar verification failed',
          data: response['data'] as Map<String, dynamic>?,
        );
      }
    } catch (e) {
      return KYCVerificationResult(
        success: false,
        message: 'Error verifying Aadhaar: $e',
      );
    }
  }

  /// Get KYC verification status
  ///
  /// Returns KYCStatusModel with PAN and Aadhaar verification status
  Future<domain.KYCStatusModel> getKYCStatus() async {
    try {
      final response = await _apiService.get('/kyc/status');

      if (response['status'] == 'success') {
        final data = response['data'] as Map<String, dynamic>? ?? {};
        final isVerified = data['is_fully_verified'] as bool? ?? false;
        return domain.KYCStatusModel(
          status: isVerified
              ? domain.KYCStatus.verified
              : domain.KYCStatus.pending,
          details: data,
          panVerified: data['pan'] != null,
          aadhaarVerified: data['aadhaar'] != null,
        );
      } else {
        return domain.KYCStatusModel(
          status: domain.KYCStatus.pending,
          details: {},
        );
      }
    } catch (e) {
      return domain.KYCStatusModel(
        status: domain.KYCStatus.pending,
        details: {},
      );
    }
  }

  /// Validate PAN format
  ///
  /// PAN format: ABCDE1234F (5 letters + 4 digits + 1 letter)
  bool isValidPANFormat(String pan) {
    final pattern = RegExp(r'^[A-Z]{5}[0-9]{4}[A-Z]{1}$');
    return pattern.hasMatch(pan.toUpperCase());
  }

  /// Validate Aadhaar format
  ///
  /// Aadhaar must be 12 digits
  bool isValidAadhaarFormat(String aadhaar) {
    final cleanAadhaar = aadhaar.replaceAll(RegExp(r'\D'), '');
    return cleanAadhaar.length == 12;
  }

  /// Format Aadhaar with spaces for display
  ///
  /// Format: XXXX XXXX XXXX
  String formatAadhaarForDisplay(String aadhaar) {
    final clean = aadhaar.replaceAll(RegExp(r'\D'), '');
    if (clean.length != 12) return aadhaar;

    return '${clean.substring(0, 4)} ${clean.substring(4, 8)} ${clean.substring(8, 12)}';
  }

  /// Mask Aadhaar for privacy
  ///
  /// Format: XXXX XXXX 1234
  String maskAadhaar(String aadhaar) {
    final clean = aadhaar.replaceAll(RegExp(r'\D'), '');
    if (clean.length != 12) return aadhaar;

    return 'XXXX XXXX ${clean.substring(8, 12)}';
  }

  /// Mask PAN for privacy
  ///
  /// Format: XXXXX1234X
  String maskPAN(String pan) {
    final clean = pan.toUpperCase();
    if (clean.length != 10) return pan;

    return 'XXXXX${clean.substring(5, 9)}X';
  }
}

/// Result of KYC verification
class KYCVerificationResult {
  final bool success;
  final String message;
  final Map<String, dynamic>? data;

  KYCVerificationResult({
    required this.success,
    required this.message,
    this.data,
  });
}

/// KYC verification status
class KYCStatus {
  final bool panVerified;
  final Map<String, dynamic>? panData;
  final bool aadhaarVerified;
  final Map<String, dynamic>? aadhaarData;
  final bool isFullyVerified;

  KYCStatus({
    required this.panVerified,
    this.panData,
    required this.aadhaarVerified,
    this.aadhaarData,
    required this.isFullyVerified,
  });
}

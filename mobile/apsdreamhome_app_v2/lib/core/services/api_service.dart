import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
import '../constants/app_constants.dart';
import '../errors/failures.dart';
import 'storage_service.dart';

class ApiService {
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  ApiService._internal();

  late Dio _dio;
  final FlutterSecureStorage _secureStorage = const FlutterSecureStorage();

  Future<void> initialize() async {
    _dio = Dio(
      BaseOptions(
        baseUrl:
            '${AppConstants.baseUrl.endsWith('/') ? AppConstants.baseUrl : '${AppConstants.baseUrl}/'}${AppConstants.apiVersion}/',
        connectTimeout: const Duration(seconds: 30),
        receiveTimeout: const Duration(seconds: 30),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ),
    );

    // Add auth interceptor
    _dio.interceptors.add(AuthInterceptor(this));

    // Add logging interceptor for debug mode
    _dio.interceptors.add(
      LogInterceptor(
        requestBody: true,
        responseBody: true,
        logPrint: (object) => print(object),
      ),
    );
  }

  // Check network connectivity
  Future<bool> isConnected() async {
    final result = await Connectivity().checkConnectivity();
    return result.isNotEmpty && !result.contains(ConnectivityResult.none);
  }

  // Generic API request method
  Future<Map<String, dynamic>> request({
    required String method,
    required String endpoint,
    dynamic data,
    Map<String, dynamic>? queryParameters,
    Options? options,
  }) async {
    try {
      if (!await isConnected()) {
        throw const NetworkFailure('No internet connection');
      }

      String cleanEndpoint = endpoint;
      if (cleanEndpoint.startsWith('/')) {
        cleanEndpoint = cleanEndpoint.substring(1);
      }

      final response = await _dio.request(
        cleanEndpoint,
        data: data,
        queryParameters: queryParameters,
        options: Options(method: method),
      );

      if (response.statusCode == 200 || response.statusCode == 201) {
        return response.data as Map<String, dynamic>;
      } else {
        throw ServerFailure('Server error: ${response.statusCode}');
      }
    } on DioException catch (e) {
      if (e.type == DioExceptionType.connectionTimeout ||
          e.type == DioExceptionType.receiveTimeout) {
        throw const NetworkFailure('Connection timeout');
      } else if (e.type == DioExceptionType.connectionError) {
        throw const NetworkFailure('No internet connection');
      } else if (e.response?.statusCode == 401) {
        throw const AuthenticationFailure('Unauthorized access');
      } else if (e.response?.statusCode == 403) {
        throw const PermissionFailure('Access forbidden');
      } else if (e.response?.statusCode == 422) {
        throw const ValidationFailure('Validation error');
      } else if (e.response?.statusCode != null) {
        throw ServerFailure('Server error: ${e.response?.statusCode}');
      } else {
        throw UnknownFailure(e.message ?? 'Unknown error occurred');
      }
    } catch (e) {
      throw UnknownFailure(e.toString());
    }
  }

  // HTTP methods
  Future<Map<String, dynamic>> get(
    String endpoint, {
    Map<String, dynamic>? queryParameters,
  }) async {
    return request(
      method: 'GET',
      endpoint: endpoint,
      queryParameters: queryParameters,
    );
  }

  Future<Map<String, dynamic>> post(String endpoint, {dynamic data}) async {
    return request(method: 'POST', endpoint: endpoint, data: data);
  }

  Future<Map<String, dynamic>> put(
    String endpoint, {
    Map<String, dynamic>? data,
  }) async {
    return request(method: 'PUT', endpoint: endpoint, data: data);
  }

  Future<Map<String, dynamic>> delete(String endpoint) async {
    return request(method: 'DELETE', endpoint: endpoint);
  }

  // Auth methods
  Future<Map<String, dynamic>> login(String email, String password) async {
    return post(
      AppConstants.loginEndpoint,
      data: {'email': email, 'password': password},
    );
  }

  // Air Login (OTP-based login without password)
  Future<Map<String, dynamic>> requestAirLoginOtp(String identifier) async {
    return post(
      AppConstants.airLoginEndpoint,
      data: {'identifier': identifier},
    );
  }

  Future<Map<String, dynamic>> verifyAirLoginOtp(String otp) async {
    return post(
      AppConstants.airLoginVerifyEndpoint,
      data: {'otp': otp},
    );
  }

  Future<void> logout() async {
    await _secureStorage.delete(key: AppConstants.tokenKey);
    await _secureStorage.delete(key: AppConstants.userIdKey);
    await _secureStorage.delete(key: AppConstants.userProfileKey);
  }

  Future<String?> getToken() async {
    return await _secureStorage.read(key: AppConstants.tokenKey);
  }

  Future<void> saveToken(String token) async {
    await _secureStorage.write(key: AppConstants.tokenKey, value: token);
  }

  // Sync methods
  Future<Map<String, dynamic>> syncData(Map<String, dynamic> syncData) async {
    return post(AppConstants.syncEndpoint, data: syncData);
  }

  // Offline-aware EMI fetch for Associate EMI Tracker
  Future<OfflineEmiResult> getAssociateEmiTrackerOffline() async {
    final storage = StorageService();
    
    // Try to get cached data first
    final cachedData = storage.getCachedEmiData();
    final isFresh = storage.isCacheFresh();
    
    if (cachedData != null && cachedData.isNotEmpty) {
      // Return cached data immediately
      return OfflineEmiResult(
        emis: cachedData,
        fromCache: true,
        cacheTimestamp: storage.getCacheTimestamp(),
      );
    }

    // No cache, try network
    try {
      final response = await get(
        '${AppConstants.apiVersion}${AppConstants.associateEmiTrackerEndpoint}',
      );
      if (response['success'] == true && response['data'] != null) {
        final data = response['data'];
        final emis = data is List ? data : [];
        final emisList = List<Map<String, dynamic>>.from(emis);
        
        // Cache the fresh data
        await storage.cacheEmiData(emisList);
        
        return OfflineEmiResult(
          emis: emisList,
          fromCache: false,
          cacheTimestamp: DateTime.now(),
        );
      }
    } catch (e) {
      // If network fails but we have stale cache, return it
      if (cachedData != null && cachedData.isNotEmpty) {
        return OfflineEmiResult(
          emis: cachedData,
          fromCache: true,
          cacheTimestamp: storage.getCacheTimestamp(),
          isStale: true,
        );
      }
      rethrow;
    }

    return OfflineEmiResult(emis: [], fromCache: false);
  }

  // Force refresh EMI data (bypass cache)
  Future<List<Map<String, dynamic>>> refreshAssociateEmiTracker() async {
    final storage = StorageService();
    final response = await get(
      '${AppConstants.apiVersion}${AppConstants.associateEmiTrackerEndpoint}',
    );
    if (response['success'] == true && response['data'] != null) {
      final data = response['data'];
      final emis = data is List ? data : [];
      final emisList = List<Map<String, dynamic>>.from(emis);
      await storage.cacheEmiData(emisList);
      return emisList;
    }
    return [];
  }

  Future<List<Map<String, dynamic>>> getProperties({
    Map<String, dynamic>? filters,
  }) async {
    final response = await get(
      AppConstants.propertiesEndpoint,
      queryParameters: filters,
    );
    return (response['data'] ?? []) as List<Map<String, dynamic>>;
  }

  Future<List<Map<String, dynamic>>> getLeads({
    Map<String, dynamic>? filters,
  }) async {
    final response = await get(
      AppConstants.leadsEndpoint,
      queryParameters: filters,
    );
    return (response['data'] ?? []) as List<Map<String, dynamic>>;
  }

  Future<List<Map<String, dynamic>>> getCommissions({
    Map<String, dynamic>? filters,
  }) async {
    final response = await get(
      AppConstants.commissionsEndpoint,
      queryParameters: filters,
    );
    return (response['data'] ?? []) as List<Map<String, dynamic>>;
  }

  Future<List<Map<String, dynamic>>> getIncentives() async {
    final response = await get(AppConstants.incentivesEndpoint);
    return (response['data'] ?? []) as List<Map<String, dynamic>>;
  }

  Future<List<Map<String, dynamic>>> getDocuments() async {
    final response = await get('/mlm/documents');
    return (response['data'] ?? []) as List<Map<String, dynamic>>;
  }

  Future<Map<String, dynamic>> getUpdates(
    String lastSync,
    String userId,
  ) async {
    final response = await get(
      AppConstants.updatesEndpoint,
      queryParameters: {'last_sync': lastSync, 'user_id': userId},
    );
    return (response['data'] ?? {}) as Map<String, dynamic>;
  }

  Future<Map<String, dynamic>> uploadDocument(
    String filePath,
    String documentType,
  ) async {
    final fileName = filePath.split('/').last;
    final formData = FormData.fromMap({
      'document_type': documentType,
      'document': await MultipartFile.fromFile(filePath, filename: fileName),
    });

    final response = await post(
      AppConstants.uploadDocumentEndpoint,
      data: formData,
    );
    return response;
  }

  Future<Map<String, dynamic>> getProfile() async {
    return get(AppConstants.profileEndpoint);
  }

  Future<Map<String, dynamic>> parseLead(String text) async {
    final response = await post(
      AppConstants.parseLeadEndpoint,
      data: {'text': text},
    );
    return (response['data'] ?? {}) as Map<String, dynamic>;
  }

  // Campaign Templates
  Future<List<Map<String, dynamic>>> getCampaignTemplates({
    Map<String, dynamic>? queryParameters,
  }) async {
    final response = await get(
      AppConstants.campaignTemplatesEndpoint,
      queryParameters: queryParameters,
    );
    return (response['data'] ?? []) as List<Map<String, dynamic>>;
  }

  Future<Map<String, dynamic>> getCampaignTemplate(String id) async {
    final response = await get('${AppConstants.campaignTemplateDetailEndpoint}$id');
    return (response['data'] ?? {}) as Map<String, dynamic>;
  }

  // Voice Uploads
  Future<List<Map<String, dynamic>>> getVoiceUploads({
    Map<String, dynamic>? queryParameters,
  }) async {
    final response = await get(
      AppConstants.voiceUploadsEndpoint,
      queryParameters: queryParameters,
    );
    return (response['data'] ?? []) as List<Map<String, dynamic>>;
  }

  Future<Map<String, dynamic>> getVoiceUpload(String id) async {
    final response = await get('${AppConstants.voiceUploadDetailEndpoint}$id');
    return (response['data'] ?? {}) as Map<String, dynamic>;
  }

  // App Feedback
  Future<List<Map<String, dynamic>>> getAppFeedback({
    Map<String, dynamic>? queryParameters,
  }) async {
    final response = await get(
      AppConstants.appFeedbackEndpoint,
      queryParameters: queryParameters,
    );
    return (response['data'] ?? []) as List<Map<String, dynamic>>;
  }

  Future<Map<String, dynamic>> submitAppFeedback(Map<String, dynamic> data) async {
    final response = await post(AppConstants.appFeedbackEndpoint, data: data);
    return response;
  }

  Future<Map<String, dynamic>> getAppFeedbackDetail(String id) async {
    final response = await get('${AppConstants.appFeedbackDetailEndpoint}$id');
    return (response['data'] ?? {}) as Map<String, dynamic>;
  }

  // Search History
  Future<List<Map<String, dynamic>>> getSearchHistory({
    Map<String, dynamic>? queryParameters,
  }) async {
    final response = await get(
      AppConstants.searchHistoryEndpoint,
      queryParameters: queryParameters,
    );
    return (response['data'] ?? []) as List<Map<String, dynamic>>;
  }

  // Registry Timeline (customer)
  Future<Map<String, dynamic>> getRegistryTimeline(String bookingId) async {
    final response = await get('${AppConstants.registryTimelineEndpoint}$bookingId');
    return (response['data'] ?? {}) as Map<String, dynamic>;
  }

  /// Download URL for the branded possession-certificate PDF (Bearer header required).
  String possessionCertificateUrl(String bookingId) {
    return '${AppConstants.baseUrl}${AppConstants.possessionCertificateEndpoint}$bookingId';
  }

  // Payout Batches (staff)
  Future<List<Map<String, dynamic>>> getPayoutBatches({
    Map<String, dynamic>? queryParameters,
  }) async {
    final response = await get(
      AppConstants.payoutBatchesEndpoint,
      queryParameters: queryParameters,
    );
    return (response['data'] ?? []) as List<Map<String, dynamic>>;
  }

  Future<Map<String, dynamic>> getPayoutBatchDetail(String id) async {
    final response = await get('${AppConstants.payoutBatchDetailEndpoint}$id');
    return (response['data'] ?? {}) as Map<String, dynamic>;
  }

  /// Download URL for the bank bulk-upload CSV (Bearer header required).
  String payoutBatchExportUrl(String id, {String format = 'generic'}) {
    return '${AppConstants.baseUrl}${AppConstants.payoutBatchDetailEndpoint}$id${AppConstants.payoutBatchExportSuffix}?format=$format';
  }

  // Site Visit Dispatch (staff)
  Future<Map<String, dynamic>> assignSiteVisitExecutive({
    required String visitId,
    required String executiveId,
    String? cabAssigned,
    String? pickupTime,
  }) async {
    final response = await post(
      '${AppConstants.siteVisitDispatchEndpoint}$visitId${AppConstants.siteVisitAssignSuffix}',
      data: {
        'executive_id': executiveId,
        if (cabAssigned != null) 'cab_assigned': cabAssigned,
        if (pickupTime != null) 'pickup_time': pickupTime,
      },
    );
    return response;
  }

  Future<Map<String, dynamic>> markSiteVisitOutcome({
    required String visitId,
    required String outcome,
    String? plotPreference,
    String? budgetFeedback,
    String? outcomeNotes,
    String? followupDate,
  }) async {
    final response = await post(
      '${AppConstants.siteVisitDispatchEndpoint}$visitId${AppConstants.siteVisitOutcomeSuffix}',
      data: {
        'outcome': outcome,
        if (plotPreference != null) 'plot_preference': plotPreference,
        if (budgetFeedback != null) 'budget_feedback': budgetFeedback,
        if (outcomeNotes != null) 'outcome_notes': outcomeNotes,
        if (followupDate != null) 'followup_date': followupDate,
      },
    );
    return response;
  }

  Future<Map<String, dynamic>> sendSiteVisitPin(String visitId) async {
    final response = await post(
      '${AppConstants.siteVisitDispatchEndpoint}$visitId${AppConstants.siteVisitSendPinSuffix}',
    );
    return response;
  }

  // Demand Letters (Phase 6)
  Future<List<dynamic>> getDemandLetters({Map<String, dynamic>? queryParameters}) async {
    final response = await get(AppConstants.demandLettersEndpoint, queryParameters: queryParameters);
    // API returns {success, data: {letters, total, page}} or direct list
    final data = response['data'];
    if (data is Map && data['letters'] is List) return data['letters'] as List<dynamic>;
    if (data is List) return data;
    return [];
  }

  Future<Map<String, dynamic>> getDemandLetterDetail(String id) async {
    final response = await get('${AppConstants.demandLettersEndpoint}${AppConstants.demandLetterDetailSuffix}$id');
    return (response['data'] ?? {}) as Map<String, dynamic>;
  }

  String demandLetterPdfUrl(String id) {
    return '${AppConstants.baseUrl}/${AppConstants.apiVersion}${AppConstants.demandLettersEndpoint}/$id/pdf';
  }

  // Construction Progress & Material Inventory (Phase 6)
  Future<List<dynamic>> getConstructionColonies() async {
    final response = await get(AppConstants.constructionColoniesEndpoint);
    final data = response['data'];
    if (data is Map && data['colonies'] is List) return data['colonies'] as List<dynamic>;
    if (data is List) return data;
    return [];
  }

  Future<List<dynamic>> getColonyMilestones(String colonyId) async {
    final response = await get('${AppConstants.constructionColoniesEndpoint}/$colonyId${AppConstants.constructionMilestonesSuffix}');
    final data = response['data'];
    if (data is Map && data['milestones'] is List) return data['milestones'] as List<dynamic>;
    if (data is List) return data;
    return [];
  }

  Future<Map<String, dynamic>> getConstructionMaterials({Map<String, dynamic>? queryParameters}) async {
    final response = await get(AppConstants.constructionMaterialsEndpoint, queryParameters: queryParameters);
    return (response['data'] ?? response) as Map<String, dynamic>;
  }

  Future<Map<String, dynamic>> logMaterialUsage(Map<String, dynamic> data) async {
    final response = await post(AppConstants.constructionMaterialUsageEndpoint, data: data);
    return response;
  }

  Future<Map<String, dynamic>> startSiteVisit({
    required String userId,
    required String leadId,
    required String propertyId,
    required double destLat,
    required double destLng,
  }) async {
    return post(
      '/site-visit/start',
      data: {
        'user_id': userId,
        'lead_id': leadId,
        'property_id': propertyId,
        'dest_lat': destLat,
        'dest_lng': destLng,
      },
    );
  }

  Future<Map<String, dynamic>> updateSiteVisitLocation({
    required int visitId,
    required double lat,
    required double lng,
  }) async {
    return post(
      '/site-visit/update',
      data: {'visit_id': visitId, 'lat': lat, 'lng': lng},
    );
  }

  Future<Map<String, dynamic>> completeSiteVisit({required int visitId}) async {
    return post('/site-visit/complete', data: {'visit_id': visitId});
  }

  // Legal Kit (download legal kit ZIP)
  Future<Map<String, dynamic>> downloadLegalKit(int bookingId, {String type = 'auto'}) async {
    return get('${AppConstants.legalKitEndpoint}$bookingId', queryParameters: {'type': type});
  }

  /// Download URL for the legal kit ZIP (Bearer header required).
  String legalKitDownloadUrl(int bookingId, {String type = 'auto'}) {
    return '${AppConstants.baseUrl}${AppConstants.apiVersion}${AppConstants.legalKitEndpoint}$bookingId?type=$type';
  }

  // Commission Recalculation (staff)
  Future<List<Map<String, dynamic>>> getCommissionRecalculations({
    Map<String, dynamic>? queryParameters,
  }) async {
    final response = await get(
      AppConstants.commissionRecalculationsEndpoint,
      queryParameters: queryParameters,
    );
    return (response['data'] ?? []) as List<Map<String, dynamic>>;
  }

  Future<Map<String, dynamic>> getCommissionRecalculationDetail(String id) async {
    final response = await get('${AppConstants.commissionRecalculationDetailEndpoint}$id');
    return (response['data'] ?? {}) as Map<String, dynamic>;
  }

  Future<Map<String, dynamic>> requestCommissionRecalculation({
    required int ledgerId,
    required String reason,
  }) async {
    return post(
      AppConstants.commissionRecalculationRequestEndpoint,
      data: {'ledger_id': ledgerId, 'reason': reason},
    );
  }

  Future<Map<String, dynamic>> bulkRequestCommissionRecalculation({
    required String type,
    required String from,
    required String to,
    required String reason,
  }) async {
    return post(
      AppConstants.commissionRecalculationBulkRequestEndpoint,
      data: {'type': type, 'from': from, 'to': to, 'reason': reason},
    );
  }

  // ============================================================
  // EMPLOYEE SELF-SERVICE PORTAL
  // ============================================================

  Future<Map<String, dynamic>> getTaxRegime() async {
    return await request(method: 'GET', endpoint: AppConstants.taxRegimeEndpoint);
  }

  Future<Map<String, dynamic>> setTaxRegime({required String regime, required int financialYear}) async {
    return await request(
      method: 'POST',
      endpoint: AppConstants.taxRegimeEndpoint,
      data: {'regime': regime, 'financial_year': financialYear},
    );
  }

  Future<Map<String, dynamic>> getInvestmentDeclaration({int? financialYear}) async {
    return await request(
      method: 'GET',
      endpoint: AppConstants.investmentDeclarationEndpoint,
      queryParameters: {'financial_year': financialYear ?? DateTime.now().year},
    );
  }

  Future<Map<String, dynamic>> saveInvestmentDeclaration({
    required int financialYear,
    required List<Map<String, dynamic>> declarations,
  }) async {
    return await request(
      method: 'POST',
      endpoint: AppConstants.investmentDeclarationEndpoint,
      data: {'financial_year': financialYear, 'declarations': declarations},
    );
  }

  Future<Map<String, dynamic>> uploadInvestmentProof({
    required int financialYear,
    required String section,
    required String filePath,
  }) async {
    final formData = FormData.fromMap({
      'financial_year': financialYear,
      'section': section,
      'proof_file': await MultipartFile.fromFile(filePath, filename: 'proof_${DateTime.now().millisecondsSinceEpoch}.pdf'),
    });
    return await request(
      method: 'POST',
      endpoint: AppConstants.uploadInvestmentProofEndpoint,
      data: formData,
      options: Options(headers: {'Content-Type': 'multipart/form-data'}),
    );
  }

  Future<Map<String, dynamic>> getForm16List() async {
    return await request(method: 'GET', endpoint: AppConstants.form16Endpoint);
  }

  Future<Map<String, dynamic>> generateForm16({required int financialYear}) async {
    return await request(
      method: 'POST',
      endpoint: AppConstants.form16GenerateEndpoint,
      data: {'financial_year': financialYear},
    );
  }

  Future<Map<String, dynamic>> downloadForm16(int financialYear) async {
    return await request(
      method: 'GET',
      endpoint: '${AppConstants.form16DownloadEndpoint}$financialYear',
    );
  }

  Future<Map<String, dynamic>> getPayslips({int limit = 24}) async {
    return await request(
      method: 'GET',
      endpoint: AppConstants.payslipsEndpoint,
      queryParameters: {'limit': limit},
    );
  }

  Future<Map<String, dynamic>> downloadPayslip(int id) async {
    final response = await _dio.get(
      '${AppConstants.payslipDownloadEndpoint}$id${AppConstants.payslipDownloadSuffix}',
      options: Options(responseType: ResponseType.bytes, headers: {'Accept': 'application/pdf'}),
    );
    return {'success': true, 'bytes': response.data, 'filename': 'Payslip_$id.pdf'};
  }

  Future<Map<String, dynamic>> getLeaveBalances({int? year}) async {
    return await request(
      method: 'GET',
      endpoint: AppConstants.leaveBalancesEndpoint,
      queryParameters: {'year': year ?? DateTime.now().year},
    );
  }

  Future<Map<String, dynamic>> getLeaveTypes() async {
    return await request(method: 'GET', endpoint: AppConstants.leaveTypesEndpoint);
  }

  Future<Map<String, dynamic>> applyLeave({
    required int leaveTypeId,
    required String startDate,
    required String endDate,
    required String reason,
    String? emergencyContact,
    String? workCoverage,
  }) async {
    return await request(
      method: 'POST',
      endpoint: AppConstants.leaveApplyEndpoint,
      data: {
        'leave_type_id': leaveTypeId,
        'start_date': startDate,
        'end_date': endDate,
        'reason': reason,
        'emergency_contact': emergencyContact,
        'work_coverage': workCoverage,
      },
    );
  }

  Future<Map<String, dynamic>> getLeaveHistory({int limit = 50}) async {
    return await request(
      method: 'GET',
      endpoint: AppConstants.leaveHistoryEndpoint,
      queryParameters: {'limit': limit},
    );
  }

  Future<Map<String, dynamic>> getReimbursements({int limit = 50}) async {
    return await request(
      method: 'GET',
      endpoint: AppConstants.reimbursementsEndpoint,
      queryParameters: {'limit': limit},
    );
  }

  Future<Map<String, dynamic>> submitReimbursement({
    required String claimType,
    required double amount,
    required String expenseDate,
    required String description,
    String? filePath,
  }) async {
    final formData = FormData.fromMap({
      'claim_type': claimType,
      'amount': amount,
      'expense_date': expenseDate,
      'description': description,
      if (filePath != null) 'receipt_file': await MultipartFile.fromFile(filePath),
    });
    return await request(
      method: 'POST',
      endpoint: AppConstants.reimbursementsEndpoint,
      data: formData,
      options: Options(headers: {'Content-Type': 'multipart/form-data'}),
    );
  }

  Future<Map<String, dynamic>> getSelfServiceProfile() async {
    return await request(method: 'GET', endpoint: AppConstants.selfServiceProfileEndpoint);
  }

  Future<Map<String, dynamic>> updateSelfServiceProfile(Map<String, dynamic> data) async {
    return await request(method: 'POST', endpoint: AppConstants.selfServiceProfileEndpoint, data: data);
  }

  Future<Map<String, dynamic>> selfServiceChangePassword({required String currentPassword, required String newPassword}) async {
    return await request(
      method: 'POST',
      endpoint: AppConstants.changePasswordEndpoint,
      data: {'current_password': currentPassword, 'new_password': newPassword},
    );
  }

  Future<Map<String, dynamic>> getAttendance({String? month}) async {
    return await request(
      method: 'GET',
      endpoint: AppConstants.attendanceEndpoint,
      queryParameters: {'month': month ?? DateTime.now().toString().substring(0, 7)},
    );
  }

  Future<Map<String, dynamic>> getAttendanceStats({String? month}) async {
    return await request(
      method: 'GET',
      endpoint: AppConstants.attendanceStatsEndpoint,
      queryParameters: {'month': month ?? DateTime.now().toString().substring(0, 7)},
    );
  }

  Future<Map<String, dynamic>> getSelfServiceDashboard() async {
    return await request(method: 'GET', endpoint: AppConstants.selfServiceDashboardEndpoint);
  }

  // ============================================================
  // GRATUITY CALCULATOR
  // ============================================================

  Future<Map<String, dynamic>> gratuityCalculator({String? calculationDate}) async {
    return await request(
      method: 'GET',
      endpoint: AppConstants.gratuityCalculatorEndpoint,
      queryParameters: {'calculation_date': calculationDate ?? DateTime.now().toIso8601String().substring(0, 10)},
    );
  }

  Future<Map<String, dynamic>> gratuityEligibilityReport() async {
    return await request(method: 'GET', endpoint: AppConstants.gratuityReportEndpoint);
  }

  Future<Map<String, dynamic>> gratuityDetail(int id) async {
    return await request(method: 'GET', endpoint: '${AppConstants.gratuityDetailEndpoint}$id');
  }

  // ============================================================
  // FULL & FINAL SETTLEMENT
  // ============================================================

  Future<Map<String, dynamic>> fnfCalculator({
    required int employeeId,
    required String lastWorkingDay,
    String? resignationDate,
    int noticePeriodDays = 30,
    int noticeServedDays = 0,
    String exitType = 'resignation',
  }) async {
    return await request(
      method: 'POST',
      endpoint: AppConstants.fnfCalculatorEndpoint,
      data: {
        'employee_id': employeeId,
        'last_working_day': lastWorkingDay,
        'resignation_date': resignationDate ?? DateTime.now().toIso8601String().substring(0, 10),
        'notice_period_days': noticePeriodDays,
        'notice_served_days': noticeServedDays,
        'exit_type': exitType,
      },
    );
  }

  Future<Map<String, dynamic>> fnfProcess({
    required int employeeId,
    required String lastWorkingDay,
    String? resignationDate,
    int noticePeriodDays = 30,
    int noticeServedDays = 0,
    String exitType = 'resignation',
  }) async {
    return await request(
      method: 'POST',
      endpoint: AppConstants.fnfProcessEndpoint,
      data: {
        'employee_id': employeeId,
        'last_working_day': lastWorkingDay,
        'resignation_date': resignationDate ?? DateTime.now().toIso8601String().substring(0, 10),
        'notice_period_days': noticePeriodDays,
        'notice_served_days': noticeServedDays,
        'exit_type': exitType,
      },
    );
  }

  // ============================================================
  // SHIFT ROSTER & OVERTIME
  // ============================================================

  Future<Map<String, dynamic>> getShiftTypes() async {
    return await request(method: 'GET', endpoint: AppConstants.shiftTypesEndpoint);
  }

  Future<Map<String, dynamic>> getRoster({required String startDate, required String endDate, int? employeeId}) async {
    return await request(
      method: 'GET',
      endpoint: AppConstants.rosterEndpoint,
      queryParameters: {'start_date': startDate, 'end_date': endDate, if (employeeId != null) 'employee_id': employeeId},
    );
  }

  Future<Map<String, dynamic>> assignShift({
    required int employeeId,
    required int shiftTypeId,
    required String shiftDate,
    String? startTime,
    String? endTime,
    String? remarks,
  }) async {
    return await request(
      method: 'POST',
      endpoint: AppConstants.assignShiftEndpoint,
      data: {
        'employee_id': employeeId,
        'shift_type_id': shiftTypeId,
        'shift_date': shiftDate,
        'start_time': startTime,
        'end_time': endTime,
        'remarks': remarks,
      },
    );
  }

  Future<Map<String, dynamic>> getOvertimeRequests({String status = 'pending'}) async {
    return await request(
      method: 'GET',
      endpoint: AppConstants.overtimeRequestsEndpoint,
      queryParameters: {'status': status},
    );
  }

  Future<Map<String, dynamic>> requestOvertime({required String overtimeDate, required double hours, required String reason}) async {
    return await request(
      method: 'POST',
      endpoint: AppConstants.overtimeRequestEndpoint,
      data: {'overtime_date': overtimeDate, 'hours': hours, 'reason': reason},
    );
  }

  Future<Map<String, dynamic>> processOvertime({required int id, required String action, String? remarks}) async {
    return await request(
      method: 'POST',
      endpoint: '${AppConstants.processOvertimeEndpoint}$id',
      data: {'action': action, 'remarks': remarks},
    );
  }

  Future<Map<String, dynamic>> getOvertimeReports({required String startDate, required String endDate}) async {
    return await request(
      method: 'GET',
      endpoint: AppConstants.overtimeReportsEndpoint,
      queryParameters: {'start_date': startDate, 'end_date': endDate},
    );
  }

  Future<Map<String, dynamic>> getShiftCoverage({required String startDate, required String endDate}) async {
    return await request(
      method: 'GET',
      endpoint: AppConstants.shiftCoverageEndpoint,
      queryParameters: {'start_date': startDate, 'end_date': endDate},
    );
  }

  // ============================================================
  // INVESTMENT (Customer)
  // ============================================================

  Future<List<Map<String, dynamic>>> getInvestmentPlans() async {
    final response = await get(AppConstants.investmentPlansEndpoint);
    return (response['data'] ?? []) as List<Map<String, dynamic>>;
  }

  Future<List<Map<String, dynamic>>> getUserInvestments() async {
    final response = await get(AppConstants.userInvestmentsEndpoint);
    return (response['data'] ?? []) as List<Map<String, dynamic>>;
  }

  Future<Map<String, dynamic>> createInvestment({
    required int planId,
    required double amount,
    String? paymentMode,
    int? referrerUserId,
  }) async {
    return await post(
      AppConstants.investmentCreateEndpoint,
      data: {
        'plan_id': planId,
        'amount': amount,
        'payment_mode': paymentMode ?? 'wallet',
        if (referrerUserId != null) 'referrer_user_id': referrerUserId,
      },
    );
  }

  Future<Map<String, dynamic>> cancelInvestment(int investmentId, String reason) async {
    return await post(
      AppConstants.investmentCancelEndpoint,
      data: {'investment_id': investmentId, 'reason': reason},
    );
  }

  // ============================================================
  // REFERRAL (Customer / Associate)
  // ============================================================

  Future<Map<String, dynamic>> getReferralEarnings() async {
    final response = await get(AppConstants.referralEarningsEndpointV2);
    return Map<String, dynamic>.from(response['data'] as Map? ?? {});
  }

  Future<Map<String, dynamic>> getReferralShareUrl() async {
    final response = await get(AppConstants.referralShareUrlEndpointV2);
    return Map<String, dynamic>.from(response['data'] as Map? ?? {});
  }

  Future<Map<String, dynamic>> getReferralLeaderboard() async {
    final response = await get(AppConstants.referralLeaderboardEndpointV2);
    return Map<String, dynamic>.from(response['data'] as Map? ?? {});
  }

  // ============================================================
  // OFFERS (Associate / Agent campaigns with progress)
  // ============================================================

  Future<List<Map<String, dynamic>>> getAssociateOffers() async {
    final response = await get(AppConstants.associateOffersEndpointV2);
    return ((response['data'] as List?) ?? [])
        .map((e) => Map<String, dynamic>.from(e as Map))
        .toList();
  }

  Future<List<Map<String, dynamic>>> getAgentOffers() async {
    final response = await get(AppConstants.agentOffersEndpointV2);
    return ((response['data'] as List?) ?? [])
        .map((e) => Map<String, dynamic>.from(e as Map))
        .toList();
  }

  Future<Map<String, dynamic>> getAgentSalary() async {
    final response = await get(AppConstants.agentSalaryEndpointV2);
    return Map<String, dynamic>.from(response['data'] as Map? ?? {});
  }

  Future<List<Map<String, dynamic>>> getPromotionalOffers() async {
    final response = await get(AppConstants.promotionalOffersEndpointV2);
    return ((response['data'] as List?) ?? [])
        .map((e) => Map<String, dynamic>.from(e as Map))
        .toList();
  }
}

// Offline EMI Result wrapper
class OfflineEmiResult {
  final List<Map<String, dynamic>> emis;
  final bool fromCache;
  final DateTime? cacheTimestamp;
  final bool isStale;

  OfflineEmiResult({
    required this.emis,
    required this.fromCache,
    this.cacheTimestamp,
    this.isStale = false,
  });

  bool get hasData => emis.isNotEmpty;
}

class AuthInterceptor extends Interceptor {
  final ApiService _apiService;

  AuthInterceptor(this._apiService);

  @override
  void onRequest(
    RequestOptions options,
    RequestInterceptorHandler handler,
  ) async {
    final token = await _apiService.getToken();
    if (token != null) {
      options.headers['Authorization'] = 'Bearer $token';
    }
    handler.next(options);
  }

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) async {
    if (err.response?.statusCode == 401) {
      // Only logout if we actually have a token (i.e. the request was authenticated)
      final token = await _apiService.getToken();
      if (token != null) {
        // We had a token but it was rejected — token expired or invalid
        await _apiService.logout();
      }
      // If no token was sent (public endpoint), don't logout — just pass the error through
    }
    handler.next(err);
  }
}

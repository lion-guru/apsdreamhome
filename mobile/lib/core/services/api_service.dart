import 'dart:convert';
import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
import '../constants/app_constants.dart';
import '../errors/failures.dart';
import 'database_helper.dart';

class ApiService {
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  ApiService._internal();
  
  late Dio _dio;
  final FlutterSecureStorage _secureStorage = const FlutterSecureStorage();
  
  Future<void> initialize() async {
    _dio = Dio(BaseOptions(
      baseUrl: AppConstants.baseUrl,
      connectTimeout: const Duration(seconds: 30),
      receiveTimeout: const Duration(seconds: 30),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    ));
    
    // Add auth interceptor
    _dio.interceptors.add(AuthInterceptor(this));
    
    // Add logging interceptor for debug mode
    _dio.interceptors.add(LogInterceptor(
      requestBody: true,
      responseBody: true,
      logPrint: (object) => print(object),
    ));
  }
  
  // Check network connectivity
  Future<bool> isConnected() async {
    final connectivity = await Connectivity().checkConnectivity();
    return connectivity != ConnectivityResult.none;
  }
  
  // Generic API request method
  Future<Map<String, dynamic>> request({
    required String method,
    required String endpoint,
    Map<String, dynamic>? data,
    Map<String, dynamic>? queryParameters,
    Options? options,
  }) async {
    try {
      if (!await isConnected()) {
        throw NetworkFailure('No internet connection');
      }
      
      final response = await _dio.request(
        endpoint,
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
        throw NetworkFailure('Connection timeout');
      } else if (e.type == DioExceptionType.connectionError) {
        throw NetworkFailure('No internet connection');
      } else if (e.response?.statusCode == 401) {
        throw AuthenticationFailure('Unauthorized access');
      } else if (e.response?.statusCode == 403) {
        throw PermissionFailure('Access forbidden');
      } else if (e.response?.statusCode == 422) {
        throw ValidationFailure('Validation error');
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
  Future<Map<String, dynamic>> get(String endpoint, {Map<String, dynamic>? queryParameters}) async {
    return request(method: 'GET', endpoint: endpoint, queryParameters: queryParameters);
  }
  
  Future<Map<String, dynamic>> post(String endpoint, {Map<String, dynamic>? data}) async {
    return request(method: 'POST', endpoint: endpoint, data: data);
  }
  
  Future<Map<String, dynamic>> put(String endpoint, {Map<String, dynamic>? data}) async {
    return request(method: 'PUT', endpoint: endpoint, data: data);
  }
  
  Future<Map<String, dynamic>> delete(String endpoint) async {
    return request(method: 'DELETE', endpoint: endpoint);
  }
  
  // Auth methods
  Future<Map<String, dynamic>> login(String email, String password) async {
    return post(
      AppConstants.loginEndpoint,
      data: {
        'email': email,
        'password': password,
      },
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
  
  Future<List<Map<String, dynamic>>> getProperties({Map<String, dynamic>? filters}) async {
    final response = await get(AppConstants.propertiesEndpoint, queryParameters: filters);
    return (response['data'] ?? []) as List<Map<String, dynamic>>;
  }
  
  Future<List<Map<String, dynamic>>> getLeads({Map<String, dynamic>? filters}) async {
    final response = await get(AppConstants.leadsEndpoint, queryParameters: filters);
    return (response['data'] ?? []) as List<Map<String, dynamic>>;
  }
  
  Future<List<Map<String, dynamic>>> getCommissions({Map<String, dynamic>? filters}) async {
    final response = await get(AppConstants.commissionsEndpoint, queryParameters: filters);
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
  
  Future<Map<String, dynamic>> getUpdates(String lastSync, String userId) async {
    final response = await get(AppConstants.updatesEndpoint, queryParameters: {
      'last_sync': lastSync,
      'user_id': userId,
    });
    return (response['data'] ?? {}) as Map<String, dynamic>;
  }

  Future<Map<String, dynamic>> uploadDocument(String filePath, String documentType) async {
    final fileName = filePath.split('/').last;
    final formData = FormData.fromMap({
      'document_type': documentType,
      'document': await MultipartFile.fromFile(filePath, filename: fileName),
    });

    final response = await post(AppConstants.uploadDocumentEndpoint, data: formData);
    return response;
  }

  Future<Map<String, dynamic>> getProfile() async {
    return get(AppConstants.profileEndpoint);
  }

  Future<Map<String, dynamic>> parseLead(String text) async {
    final response = await post(AppConstants.parseLeadEndpoint, data: {'text': text});
    return (response['data'] ?? {}) as Map<String, dynamic>;
  }
}

class AuthInterceptor extends Interceptor {
  final ApiService _apiService;
  
  AuthInterceptor(this._apiService);
  
  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) async {
    final token = await _apiService.getToken();
    if (token != null) {
      options.headers['Authorization'] = 'Bearer $token';
    }
    handler.next(options);
  }
  
  @override
  void onError(DioException err, ErrorInterceptorHandler handler) async {
    if (err.response?.statusCode == 401) {
      // Token expired, try to refresh or logout
      await _apiService.logout();
      // You might want to navigate to login screen here
    }
    handler.next(err);
  }
}

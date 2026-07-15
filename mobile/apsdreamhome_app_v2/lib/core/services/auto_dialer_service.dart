import 'dart:async';
import 'dart:developer' as developer;
import 'package:dio/dio.dart';
import '../constants/app_constants.dart';

/// Auto-Dialer Service — manages call scheduling, queue, and bulk operations
/// Connects to backend VoiceCallService APIs
class AutoDialerService {
  static final AutoDialerService _instance = AutoDialerService._internal();
  factory AutoDialerService() => _instance;
  AutoDialerService._internal();

  final Dio _dio = Dio(
    BaseOptions(
      baseUrl: AppConstants.baseUrl,
      connectTimeout: const Duration(seconds: 30),
      receiveTimeout: const Duration(seconds: 30),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    ),
  );

  bool _useMock = false;

  Map<String, dynamic> _handleResponse(Response response) {
    final data = response.data;
    if (data is Map<String, dynamic>) return data;
    if (data is Map) return Map<String, dynamic>.from(data);
    return {'success': false, 'error': 'Invalid response'};
  }

  /// Schedule a single call
  Future<Map<String, dynamic>> scheduleCall({
    required String leadId,
    required String phone,
    required String scheduledDate,
    String scheduledTime = '10:00:00',
    String scriptTemplate = 'property_introduction',
    String priority = 'medium',
    String leadName = '',
  }) async {
    if (_useMock) {
      return {
        'success': true,
        'schedule_id': DateTime.now().millisecondsSinceEpoch,
        'message': 'Call scheduled (mock)',
      };
    }
    try {
      final response = await _dio.post(
        '${AppConstants.apiVersion}/auto-dialer/schedule',
        data: {
          'lead_id': leadId,
          'phone': phone,
          'scheduled_date': scheduledDate,
          'scheduled_time': scheduledTime,
          'script_template': scriptTemplate,
          'priority': priority,
          'lead_name': leadName,
        },
      );
      return _handleResponse(response);
    } on DioException catch (e) {
      developer.log('Schedule error: ${e.message}', name: 'AutoDialer');
      return {'success': false, 'error': e.message ?? 'Network error'};
    }
  }

  /// Bulk schedule calls
  Future<Map<String, dynamic>> bulkSchedule({
    required List<Map<String, dynamic>> leads,
    required String scheduledDate,
    String scheduledTime = '10:00:00',
    String scriptTemplate = 'property_introduction',
    String priority = 'medium',
  }) async {
    if (_useMock) {
      return {'success': true, 'scheduled': leads.length, 'failed': 0};
    }
    try {
      final response = await _dio.post(
        '${AppConstants.apiVersion}/auto-dialer/bulk-schedule',
        data: {
          'leads': leads,
          'scheduled_date': scheduledDate,
          'scheduled_time': scheduledTime,
          'script_template': scriptTemplate,
          'priority': priority,
        },
      );
      return _handleResponse(response);
    } on DioException catch (e) {
      return {'success': false, 'error': e.message ?? 'Network error'};
    }
  }

  /// Get scheduled calls
  Future<List<Map<String, dynamic>>> getSchedule({
    String? status,
    String? date,
    int limit = 50,
    int offset = 0,
  }) async {
    if (_useMock) return _mockSchedule();
    try {
      final params = <String, dynamic>{'limit': limit, 'offset': offset};
      if (status != null) params['status'] = status;
      if (date != null) params['date'] = date;

      final response = await _dio.get(
        '${AppConstants.apiVersion}/auto-dialer/schedule',
        queryParameters: params,
      );
      final data = _handleResponse(response);
      final items = data['data'];
      if (items is List) {
        return items.map((e) => Map<String, dynamic>.from(e as Map)).toList();
      }
      return [];
    } on DioException catch (e) {
      developer.log('GetSchedule error: ${e.message}', name: 'AutoDialer');
      return _mockSchedule();
    }
  }

  /// Cancel a scheduled call
  Future<Map<String, dynamic>> cancelSchedule(int scheduleId) async {
    if (_useMock) {
      return {'success': true, 'message': 'Cancelled (mock)'};
    }
    try {
      final response = await _dio.post(
        '${AppConstants.apiVersion}/auto-dialer/cancel/$scheduleId',
      );
      return _handleResponse(response);
    } on DioException catch (e) {
      return {'success': false, 'error': e.message ?? 'Network error'};
    }
  }

  /// Reschedule a call
  Future<Map<String, dynamic>> rescheduleCall({
    required int scheduleId,
    required String newDate,
    String newTime = '10:00:00',
  }) async {
    if (_useMock) {
      return {'success': true, 'message': 'Rescheduled (mock)'};
    }
    try {
      final response = await _dio.post(
        '${AppConstants.apiVersion}/auto-dialer/reschedule/$scheduleId',
        data: {'new_date': newDate, 'new_time': newTime},
      );
      return _handleResponse(response);
    } on DioException catch (e) {
      return {'success': false, 'error': e.message ?? 'Network error'};
    }
  }

  /// Get auto-dialer stats
  Future<Map<String, dynamic>> getStats() async {
    if (_useMock) return _mockStats();
    try {
      final response = await _dio.get(
        '${AppConstants.apiVersion}/auto-dialer/stats',
      );
      return _handleResponse(response);
    } on DioException catch (e) {
      developer.log('Stats error: ${e.message}', name: 'AutoDialer');
      return _mockStats();
    }
  }

  /// Get call history
  Future<List<Map<String, dynamic>>> getCallHistory({
    int limit = 20,
    int offset = 0,
  }) async {
    if (_useMock) return [];
    try {
      final response = await _dio.get(
        '${AppConstants.apiVersion}/auto-dialer/history',
        queryParameters: {'limit': limit, 'offset': offset},
      );
      final data = _handleResponse(response);
      final items = data['data'];
      if (items is List) {
        return items.map((e) => Map<String, dynamic>.from(e as Map)).toList();
      }
      return [];
    } on DioException catch (e) {
      developer.log('History error: ${e.message}', name: 'AutoDialer');
      return [];
    }
  }

  /// Process the auto-dialer queue
  Future<Map<String, dynamic>> processQueue() async {
    if (_useMock) {
      return {'success': true, 'processed': 3, 'message': 'Processed 3 (mock)'};
    }
    try {
      final response = await _dio.post(
        '${AppConstants.apiVersion}/auto-dialer/process',
      );
      return _handleResponse(response);
    } on DioException catch (e) {
      return {'success': false, 'error': e.message ?? 'Network error'};
    }
  }

  /// Send a single SMS
  Future<Map<String, dynamic>> sendSms({
    required String phone,
    required String message,
    String? templateCode,
  }) async {
    if (_useMock) {
      return {'success': true, 'message': 'SMS sent (mock)'};
    }
    try {
      final response = await _dio.post(
        '${AppConstants.apiVersion}/auto-dialer/send-sms',
        data: {
          'phone': phone,
          'message': message,
          if (templateCode != null) 'template_code': templateCode,
        },
      );
      return _handleResponse(response);
    } on DioException catch (e) {
      return {'success': false, 'error': e.message ?? 'Network error'};
    }
  }

  /// Send a single WhatsApp message
  Future<Map<String, dynamic>> sendWhatsApp({
    required String phone,
    required String message,
    String? templateName,
  }) async {
    if (_useMock) {
      return {'success': true, 'message': 'WhatsApp sent (mock)'};
    }
    try {
      final response = await _dio.post(
        '${AppConstants.apiVersion}/auto-dialer/send-whatsapp',
        data: {
          'phone': phone,
          'message': message,
          if (templateName != null) 'template_name': templateName,
        },
      );
      return _handleResponse(response);
    } on DioException catch (e) {
      return {'success': false, 'error': e.message ?? 'Network error'};
    }
  }

  /// Bulk send SMS
  Future<Map<String, dynamic>> bulkSendSms({
    required List<Map<String, dynamic>> leads,
    required String message,
    String? templateCode,
  }) async {
    if (_useMock) {
      return {'success': true, 'sent': leads.length, 'failed': 0};
    }
    try {
      final response = await _dio.post(
        '${AppConstants.apiVersion}/auto-dialer/bulk-sms',
        data: {
          'leads': leads,
          'message': message,
          if (templateCode != null) 'template_code': templateCode,
        },
      );
      return _handleResponse(response);
    } on DioException catch (e) {
      return {'success': false, 'error': e.message ?? 'Network error'};
    }
  }

  /// Bulk send WhatsApp
  Future<Map<String, dynamic>> bulkSendWhatsApp({
    required List<Map<String, dynamic>> leads,
    required String message,
    String? templateName,
  }) async {
    if (_useMock) {
      return {'success': true, 'sent': leads.length, 'failed': 0};
    }
    try {
      final response = await _dio.post(
        '${AppConstants.apiVersion}/auto-dialer/bulk-whatsapp',
        data: {
          'leads': leads,
          'message': message,
          if (templateName != null) 'template_name': templateName,
        },
      );
      return _handleResponse(response);
    } on DioException catch (e) {
      return {'success': false, 'error': e.message ?? 'Network error'};
    }
  }

  /// AI-powered auto-schedule: scores leads and auto-schedules calls for hot ones
  Future<Map<String, dynamic>> aiSchedule({
    int minScore = 70,
    int limit = 10,
    String? scheduledDate,
    String scheduledTime = '10:00:00',
  }) async {
    if (_useMock) {
      return {
        'success': true,
        'scheduled': 3,
        'failed': 0,
        'total_scored': 5,
        'message': 'AI scheduled 3 calls (mock)',
      };
    }
    try {
      final response = await _dio.post(
        '${AppConstants.apiVersion}/auto-dialer/ai-schedule',
        data: {
          'min_score': minScore,
          'limit': limit,
          'scheduled_date':
              scheduledDate ?? DateTime.now().toString().split(' ')[0],
          'scheduled_time': scheduledTime,
        },
      );
      return _handleResponse(response);
    } on DioException catch (e) {
      developer.log('AI schedule error: ${e.message}', name: 'AutoDialer');
      return {'success': false, 'error': e.message ?? 'Network error'};
    }
  }

  // ─── Mock Data ───

  /// Call analytics from calls_log (outcomes, methods, daily volume).
  Future<Map<String, dynamic>> callStats({int days = 30}) async {
    if (_useMock) {
      return {
        'success': true,
        'data': {
          'totals': {
            'total': 42,
            'connected': 18,
            'not_answered': 12,
            'busy': 5,
            'call_later': 7,
            'whatsapp': 9,
            'sms': 14,
          },
          'outcomes': [
            {'outcome': 'connected', 'total': 18},
            {'outcome': 'not_answered', 'total': 12},
            {'outcome': 'call_later', 'total': 7},
            {'outcome': 'busy', 'total': 5},
          ],
          'methods': [
            {'method': 'app', 'total': 19},
            {'method': 'whatsapp', 'total': 9},
            {'method': 'sms', 'total': 14},
          ],
          'daily': [
            {
              'day': DateTime.now().toString().split(' ')[0],
              'total': 6,
              'connected': 3,
            },
          ],
        },
      };
    }
    try {
      final response = await _dio.get(
        '${AppConstants.apiVersion}/calls/stats',
        queryParameters: {'days': days},
      );
      return _handleResponse(response);
    } on DioException catch (e) {
      developer.log('Call stats error: ${e.message}', name: 'AutoDialer');
      return {'success': false, 'error': e.message ?? 'Network error'};
    }
  }

  /// In-app AI voice conversation. [message] is the transcribed user text.
  /// Returns { success, session_id, reply, intent, sentiment, engine }
  Future<Map<String, dynamic>> voiceChat({
    required String message,
    int? sessionId,
    int? leadId,
  }) async {
    if (_useMock) {
      return {
        'success': true,
        'session_id': sessionId ?? 1,
        'reply':
            'नमस्ते! मैं आपकी मदद कर सकता हूँ। क्या आप प्लॉट या प्रॉपर्टी के बारे में जानना चाहते हैं?',
        'intent': 'greeting',
        'sentiment': 'positive',
        'engine': 'mock',
      };
    }
    try {
      final response = await _dio.post(
        '${AppConstants.apiVersion}/voice-chat',
        data: {
          'message': message,
          if (sessionId != null) 'session_id': sessionId,
          if (leadId != null) 'lead_id': leadId,
        },
      );
      return _handleResponse(response);
    } on DioException catch (e) {
      developer.log('Voice chat error: ${e.message}', name: 'AutoDialer');
      return {'success': false, 'error': e.message ?? 'Network error'};
    }
  }

  List<Map<String, dynamic>> _mockSchedule() {
    return [
      {
        'id': 1,
        'lead_name': 'Rahul Sharma',
        'phone': '+919876543210',
        'scheduled_date': DateTime.now()
            .add(const Duration(hours: 2))
            .toString()
            .split(' ')[0],
        'scheduled_time': '14:00:00',
        'status': 'pending',
        'priority': 'high',
        'script_template': 'property_introduction',
      },
      {
        'id': 2,
        'lead_name': 'Priya Singh',
        'phone': '+919876543211',
        'scheduled_date': DateTime.now()
            .add(const Duration(hours: 4))
            .toString()
            .split(' ')[0],
        'scheduled_time': '16:00:00',
        'status': 'pending',
        'priority': 'medium',
        'script_template': 'emi_followup',
      },
    ];
  }

  Map<String, dynamic> _mockStats() {
    return {
      'total_scheduled': 25,
      'completed_today': 8,
      'pending_today': 5,
      'failed_today': 1,
      'connected': 6,
      'not_answered': 2,
      'busy': 1,
      'call_later': 3,
    };
  }
}

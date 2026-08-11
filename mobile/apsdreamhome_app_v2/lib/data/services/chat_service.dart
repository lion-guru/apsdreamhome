import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/app_constants.dart';

/// Chat message model
class ChatMessage {
  final int id;
  final String text;
  final String senderType; // visitor, agent, bot, system
  final String senderName;
  final bool isMe;
  final DateTime createdAt;
  final String messageType; // text, image, file

  const ChatMessage({
    required this.id,
    required this.text,
    required this.senderType,
    required this.senderName,
    required this.isMe,
    required this.createdAt,
    this.messageType = 'text',
  });

  factory ChatMessage.fromJson(Map<String, dynamic> json, {bool isMe = false}) {
    return ChatMessage(
      id: _parseInt(json['id']),
      text: json['message']?.toString() ?? '',
      senderType: json['sender_type']?.toString() ?? 'visitor',
      senderName: json['sender_name']?.toString() ?? '',
      isMe: isMe || json['sender_type']?.toString() == 'visitor',
      createdAt: _parseDate(json['created_at']),
      messageType: json['message_type']?.toString() ?? 'text',
    );
  }

  static int _parseInt(dynamic v) {
    if (v == null) return 0;
    if (v is int) return v;
    if (v is String) return int.tryParse(v) ?? 0;
    return 0;
  }

  static DateTime _parseDate(dynamic v) {
    if (v == null) return DateTime.now();
    if (v is DateTime) return v;
    if (v is String) {
      try {
        return DateTime.parse(v);
      } catch (_) {
        // Try MySQL format: YYYY-MM-DD HH:MM:SS
        try {
          return DateTime.parse(v.replaceAll(' ', 'T'));
        } catch (_) {}
      }
    }
    return DateTime.now();
  }
}

/// Chat session model
class ChatSession {
  final int id;
  final String token;
  final String status; // open, assigned, active, closed
  final String? agentName;
  final List<ChatMessage> messages;
  final int lastId;

  const ChatSession({
    required this.id,
    required this.token,
    this.status = 'open',
    this.agentName,
    this.messages = const [],
    this.lastId = 0,
  });

  factory ChatSession.fromJson(Map<String, dynamic> json) {
    return ChatSession(
      id: _parseInt(json['id']),
      token: json['session_token']?.toString() ?? '',
      status: json['status']?.toString() ?? 'open',
      agentName: json['agent_name']?.toString(),
    );
  }

  static int _parseInt(dynamic v) {
    if (v == null) return 0;
    if (v is int) return v;
    if (v is String) return int.tryParse(v) ?? 0;
    return 0;
  }

  ChatSession copyWith({
    String? status,
    String? agentName,
    List<ChatMessage>? messages,
    int? lastId,
  }) {
    return ChatSession(
      id: id,
      token: token,
      status: status ?? this.status,
      agentName: agentName ?? this.agentName,
      messages: messages ?? this.messages,
      lastId: lastId ?? this.lastId,
    );
  }
}

/// Chat service — connects to backend live chat API
/// Falls back gracefully to mock data when API is unavailable.
class ChatService {
  final Dio _dio;
  bool _useMock = false;

  ChatService()
    : _dio = Dio(
        BaseOptions(
          baseUrl: AppConstants.baseUrl,
          connectTimeout: const Duration(seconds: 15),
          receiveTimeout: const Duration(seconds: 15),
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
        ),
      );

  /// Start a new chat session
  Future<ChatSession?> startSession({
    String name = '',
    String email = '',
    String phone = '',
    String message = '',
    String subject = '',
  }) async {
    if (_useMock) return _mockStartSession();

    try {
      final response = await _dio.post(
        AppConstants.chatStartEndpoint,
        data: {
          'name': name,
          'email': email,
          'phone': phone,
          'message': message,
          'subject': subject,
          'page_url': 'mobile-app',
          'referrer_url': '',
        },
      );

      final data = response.data;
      if (data is Map<String, dynamic> && data['session_token'] != null) {
        final session = ChatSession.fromJson(data);
        // If there was a first message, it should already be in the DB
        // Add a welcome message if no messages created yet
        return session;
      }
      return null;
    } catch (e) {
      _useMock = true;
      return _mockStartSession();
    }
  }

  /// Send a message in an existing session
  Future<bool> sendMessage(String token, String message) async {
    if (_useMock) {
      return true;
    }

    try {
      final response = await _dio.post(
        AppConstants.chatSendEndpoint,
        data: {'token': token, 'message': message},
      );
      final data = response.data;
      return data is Map<String, dynamic> && data['success'] == true;
    } catch (e) {
      _useMock = true;
      return true;
    }
  }

  /// Fetch chat history for a session
  Future<List<Map<String, dynamic>>> getChatHistory(String sessionId) async {
    if (_useMock) return [];

    try {
      final response = await _dio.get(
        AppConstants.chatHistoryEndpoint,
        queryParameters: {'session_id': sessionId},
      );
      final data = response.data;
      if (data is Map<String, dynamic> && data['success'] == true) {
        return List<Map<String, dynamic>>.from(data['messages'] ?? []);
      }
    } catch (_) {}
    return [];
  }

  /// Poll for new messages
  Future<Map<String, dynamic>> pollMessages(
    String token, {
    int lastId = 0,
  }) async {
    if (_useMock) {
      return _mockPoll(lastId);
    }

    try {
      final response = await _dio.get(
        AppConstants.chatPollEndpoint,
        queryParameters: {'token': token, 'last_id': lastId},
      );
      final data = response.data;
      if (data is Map<String, dynamic>) {
        return data;
      }
      return {'messages': [], 'last_id': lastId, 'status': 'open'};
    } catch (e) {
      _useMock = true;
      return _mockPoll(lastId);
    }
  }

  // ─── Mock Fallback ───

  int _mockMsgId = 100;

  ChatSession _mockStartSession() {
    _mockMsgId = 100;
    return ChatSession(
      id: 1,
      token: 'mock_token_${DateTime.now().millisecondsSinceEpoch}',
      status: 'open',
      agentName: 'APS Support',
    );
  }

  Map<String, dynamic> _mockPoll(int lastId) {
    if (lastId >= _mockMsgId) {
      return {'messages': [], 'last_id': lastId, 'status': 'open'};
    }

    _mockMsgId++;
    final msgId = _mockMsgId;

    final messages = [
      {
        'id': msgId,
        'message': msgId == 101
            ? 'Hello! Welcome to APS Dream Home. How can I assist you today?'
            : msgId == 102
            ? 'Great choice! We have premium plots available.'
            : msgId == 103
            ? 'Would you like to schedule a site visit?'
            : 'Thank you for your message! Our team will get back to you shortly.',
        'sender_type': 'agent',
        'sender_name': 'APS Support',
        'created_at': DateTime.now().toIso8601String(),
        'message_type': 'text',
      },
    ];

    return {'messages': messages, 'last_id': msgId, 'status': 'open'};
  }
}

// Provider
final chatServiceProvider = Provider<ChatService>((ref) {
  return ChatService();
});

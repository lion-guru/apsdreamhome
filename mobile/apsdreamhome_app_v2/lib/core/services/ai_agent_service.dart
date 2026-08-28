import 'dart:developer' as developer;
import 'package:dio/dio.dart';
import '../constants/app_constants.dart';

/// AI Agent Roles
enum AIAgentRole {
  customerSupport,
  salesAssistant,
  propertyExpert,
  investmentAdvisor,
  managerAssistant,
  telecallerCoach,
  documentAnalyzer,
  leadScorer,
}

/// AI Agent Capability
class AIAgentCapability {
  final String name;
  final String description;
  final bool requiresContext;
  final List<String> requiredData;

  AIAgentCapability({
    required this.name,
    required this.description,
    this.requiresContext = false,
    this.requiredData = const [],
  });
}

/// AI Agent Configuration
class AIAgentConfig {
  final String id;
  final String name;
  final AIAgentRole role;
  final String systemPrompt;
  final List<AIAgentCapability> capabilities;
  final Map<String, dynamic> metadata;

  AIAgentConfig({
    required this.id,
    required this.name,
    required this.role,
    required this.systemPrompt,
    required this.capabilities,
    this.metadata = const {},
  });
}

/// AI Agent Service — connects to backend AI APIs
/// Falls back gracefully to mock responses when API is unavailable.
class AIAgentService {
  static final AIAgentService _instance = AIAgentService._internal();
  factory AIAgentService() => _instance;
  AIAgentService._internal();

  final Dio _dio = Dio(
    BaseOptions(
      baseUrl: AppConstants.baseUrl,
      connectTimeout: const Duration(seconds: 30),
      receiveTimeout: const Duration(seconds: 60),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    ),
  );

  bool _useMock = false;

  /// Set auth token for authenticated requests
  void setAuthToken(String? token) {
    if (token != null) {
      _dio.options.headers['Authorization'] = 'Bearer $token';
    } else {
      _dio.options.headers.remove('Authorization');
    }
  }

  /// Initialize the AI Agent Service
  Future<void> initialize() async {
    developer.log('AI Agent Service initialized');
  }

  /// Create an AI agent for a specific role
  Future<AIAgentConfig> createAgent({
    required AIAgentRole role,
    String? customName,
    Map<String, dynamic>? context,
  }) async {
    final id = DateTime.now().millisecondsSinceEpoch.toString();

    return AIAgentConfig(
      id: id,
      name: customName ?? _getDefaultName(role),
      role: role,
      systemPrompt: _getSystemPrompt(role),
      capabilities: _getCapabilities(role),
      metadata: context ?? {},
    );
  }

  /// Send a message to the AI agent — calls backend voice-bot/chat API
  Future<String> sendMessage(
    AIAgentConfig agent,
    String message, {
    Map<String, dynamic>? context,
  }) async {
    if (_useMock) return _mockResponse(message);

    try {
      final response = await _dio.post(
        'api/voice-bot/chat',
        data: {
          'message': message,
          'agent_role': agent.role.name,
          'agent_id': agent.id,
          'context': context ?? {},
        },
      );

      final data = response.data;
      if (data is Map<String, dynamic>) {
        return data['reply']?.toString() ??
            data['response']?.toString() ??
            data['message']?.toString() ??
            data['data']?['response']?.toString() ??
            'I received your message. How can I help?';
      }
      return 'I received your message. How can I help?';
    } catch (e) {
      developer.log('AI sendMessage failed: $e');
      _useMock = true;
      return _mockResponse(message);
    }
  }

  /// Process lead with AI — calls backend AI scoring
  Future<Map<String, dynamic>> processLead(
    AIAgentConfig agent,
    Map<String, dynamic> leadData,
  ) async {
    if (_useMock) return _mockProcessLead(leadData);

    try {
      final response = await _dio.post(
        '${AppConstants.apiVersion}/ai-agent/process-lead',
        data: {'lead_data': leadData, 'agent_role': agent.role.name},
      );

      final data = response.data;
      if (data is Map<String, dynamic> && data['success'] == true) {
        final d = data['data'];
        return Map<String, dynamic>.from(d is Map ? d : {});
      }
      return _mockProcessLead(leadData);
    } catch (e) {
      developer.log('AI processLead failed: $e');
      _useMock = true;
      return _mockProcessLead(leadData);
    }
  }

  /// Analyze property — calls backend AI analysis
  Future<Map<String, dynamic>> analyzeProperty(
    AIAgentConfig agent,
    Map<String, dynamic> propertyData,
  ) async {
    if (_useMock) return _mockAnalyzeProperty(propertyData);

    try {
      final response = await _dio.post(
        '${AppConstants.apiVersion}/ai-agent/analyze-property',
        data: {'property_data': propertyData, 'agent_role': agent.role.name},
      );

      final data = response.data;
      if (data is Map<String, dynamic> && data['success'] == true) {
        final d = data['data'];
        return Map<String, dynamic>.from(d is Map ? d : {});
      }
      return _mockAnalyzeProperty(propertyData);
    } catch (e) {
      developer.log('AI analyzeProperty failed: $e');
      _useMock = true;
      return _mockAnalyzeProperty(propertyData);
    }
  }

  /// Get agent suggestions — calls backend AI recommendations
  Future<List<String>> getSuggestions(
    AIAgentConfig agent,
    String context,
  ) async {
    if (_useMock) return _mockSuggestions();

    try {
      final response = await _dio.post(
        '${AppConstants.apiVersion}/ai-agent/recommendations',
        data: {'context': context, 'agent_role': agent.role.name},
      );

      final data = response.data;
      if (data is Map<String, dynamic> && data['success'] == true) {
        final d = data['data'];
        final map = d is Map ? d : {};
        final suggestions = map['suggestions'];
        if (suggestions is List) {
          return suggestions.map((s) => s.toString()).toList();
        }
      }
      return _mockSuggestions();
    } catch (e) {
      developer.log('AI getSuggestions failed: $e');
      _useMock = true;
      return _mockSuggestions();
    }
  }

  /// Process a message with AI agent — calls backend chat API
  Future<String> processMessage({
    required String agentId,
    required String message,
    Map<String, dynamic>? context,
  }) async {
    developer.log('Processing message for agent: $agentId');

    if (_useMock) return _mockResponse(message);

    try {
      final response = await _dio.post(
        'api/voice-bot/chat',
        data: {
          'message': message,
          'agent_id': agentId,
          'context': context ?? {},
        },
      );

      final data = response.data;
      if (data is Map<String, dynamic>) {
        return data['reply']?.toString() ??
            data['response']?.toString() ??
            data['message']?.toString() ??
            data['data']?['response']?.toString() ??
            'Thank you for your message. How can I assist you today?';
      }
      return 'Thank you for your message. How can I assist you today?';
    } catch (e) {
      developer.log('AI processMessage failed: $e');
      _useMock = true;
      return _mockResponse(message);
    }
  }

  /// Make a decision based on context — calls backend AI decision engine
  Future<Map<String, dynamic>> makeDecision({
    String? agentId,
    String? decisionType,
    Map<String, dynamic>? data,
    dynamic context,
  }) async {
    developer.log('Making decision for agent: $agentId');

    if (_useMock) return _mockDecision();

    try {
      final response = await _dio.post(
        '${AppConstants.apiVersion}/ai-agent/decide',
        data: {
          'agent_id': agentId,
          'decision_type': decisionType,
          'data': data ?? {},
          'context': context,
        },
      );

      final responseData = response.data;
      if (responseData is Map<String, dynamic> &&
          responseData['success'] == true) {
        final d = responseData['data'];
        return Map<String, dynamic>.from(d is Map ? d : {});
      }
      return _mockDecision();
    } catch (e) {
      developer.log('AI makeDecision failed: $e');
      _useMock = true;
      return _mockDecision();
    }
  }

  /// Provide feedback to improve AI performance
  Future<void> provideFeedback({
    required String agentId,
    required String feedback,
    required int rating,
  }) async {
    developer.log('Providing feedback for agent: $agentId, rating: $rating');

    if (_useMock) return;

    try {
      await _dio.post(
        '${AppConstants.apiVersion}/ai-agent/feedback',
        data: {
          'agent_id': agentId,
          'feedback': feedback,
          'rating': rating,
          'timestamp': DateTime.now().toIso8601String(),
        },
      );
    } catch (e) {
      developer.log('AI feedback failed: $e');
    }
  }

  /// Get agent statistics — calls backend stats API
  Future<Map<String, dynamic>> getAgentStats({required String agentId}) async {
    developer.log('Getting stats for agent: $agentId');

    if (_useMock) return _mockStats();

    try {
      final response = await _dio.get(
        '${AppConstants.apiVersion}/ai-agent/stats',
        queryParameters: {'agent_id': agentId},
      );

      final data = response.data;
      if (data is Map<String, dynamic> && data['success'] == true) {
        final d = data['data'];
        return Map<String, dynamic>.from(d is Map ? d : {});
      }
      return _mockStats();
    } catch (e) {
      developer.log('AI getAgentStats failed: $e');
      _useMock = true;
      return _mockStats();
    }
  }

  /// Start a voice call session — calls backend voice-agent API
  Future<Map<String, dynamic>> startVoiceCall({
    required String leadId,
    required String phone,
    String? script,
  }) async {
    try {
      final response = await _dio.post(
        'api/voice-agent/start-call',
        data: {
          'lead_id': leadId,
          'phone': phone,
          'script': script ?? 'default',
        },
      );

      final data = response.data;
      if (data is Map<String, dynamic>) {
        return data;
      }
      return {'success': false, 'error': 'Invalid response'};
    } catch (e) {
      developer.log('Voice start call failed: $e');
      return {'success': false, 'error': e.toString()};
    }
  }

  /// End a voice call session
  Future<Map<String, dynamic>> endVoiceCall({
    required String sessionId,
    String? outcome,
    String? notes,
  }) async {
    try {
      final response = await _dio.post(
        'api/voice-agent/end-call',
        data: {
          'session_id': sessionId,
          'outcome': outcome ?? 'completed',
          'notes': notes,
        },
      );

      final data = response.data;
      if (data is Map<String, dynamic>) {
        return data;
      }
      return {'success': false, 'error': 'Invalid response'};
    } catch (e) {
      developer.log('Voice end call failed: $e');
      return {'success': false, 'error': e.toString()};
    }
  }

  /// Get voice call stats
  Future<Map<String, dynamic>> getVoiceStats() async {
    try {
      final response = await _dio.get('api/voice-agent/stats');
      final data = response.data;
      if (data is Map<String, dynamic>) {
        return data;
      }
      return {};
    } catch (e) {
      developer.log('Voice stats failed: $e');
      return {};
    }
  }

  /// Get voice call history
  Future<List<Map<String, dynamic>>> getVoiceCallHistory({
    int limit = 20,
    int offset = 0,
  }) async {
    try {
      final response = await _dio.get(
        'api/voice-agent/call-history',
        queryParameters: {'limit': limit, 'offset': offset},
      );
      final data = response.data;
      if (data is Map<String, dynamic> && data['data'] is List) {
        return (data['data'] as List)
            .map((e) => Map<String, dynamic>.from(e as Map))
            .toList();
      }
      return [];
    } catch (e) {
      developer.log('Voice call history failed: $e');
      return [];
    }
  }

  /// Schedule a voice call
  Future<Map<String, dynamic>> scheduleVoiceCall({
    required String leadId,
    required String phone,
    required DateTime scheduledAt,
    String? script,
  }) async {
    try {
      final response = await _dio.post(
        'api/voice-agent/schedule',
        data: {
          'lead_id': leadId,
          'phone': phone,
          'scheduled_at': scheduledAt.toIso8601String(),
          'script': script ?? 'default',
        },
      );
      final data = response.data;
      if (data is Map<String, dynamic>) {
        return data;
      }
      return {'success': false};
    } catch (e) {
      developer.log('Voice schedule failed: $e');
      return {'success': false, 'error': e.toString()};
    }
  }

  /// Get AI analytics
  Future<Map<String, dynamic>> getAnalytics() async {
    try {
      final response = await _dio.get(
        '${AppConstants.apiVersion}/ai-agent/analytics',
      );
      final data = response.data;
      if (data is Map<String, dynamic> && data['success'] == true) {
        final d = data['data'];
        return Map<String, dynamic>.from(d is Map ? d : {});
      }
      return {};
    } catch (e) {
      developer.log('AI analytics failed: $e');
      return {};
    }
  }

  // ─── Mock Fallbacks ───

  String _mockResponse(String message) {
    if (message.toLowerCase().contains('hello') ||
        message.toLowerCase().contains('hi')) {
      return 'Hello! I\'m the APS Dream Home AI assistant. How can I help you find your dream property today?';
    }
    if (message.toLowerCase().contains('price') ||
        message.toLowerCase().contains('cost')) {
      return 'Our plots start from ₹5L onwards. We have options in Suryoday, Braj Radha, Raghunath, and Budh Bihar colonies. What\'s your budget range?';
    }
    if (message.toLowerCase().contains('property') ||
        message.toLowerCase().contains('plot')) {
      return 'We have residential plots (1000-3000 sqft) and commercial properties across 4 colonies. Would you like to explore a specific location?';
    }
    if (message.toLowerCase().contains('visit') ||
        message.toLowerCase().contains('site')) {
      return 'I\'d be happy to schedule a site visit for you! Which colony are you interested in? We can arrange a free guided tour.';
    }
    if (message.toLowerCase().contains('emi') ||
        message.toLowerCase().contains('loan')) {
      return 'We offer flexible EMI options starting from ₹8,000/month. Our in-house loan system has 0% interest for the first year. Want me to calculate your EMI?';
    }
    return 'Thank you for your message! I\'m here to help with property inquiries, pricing, site visits, and more. What would you like to know?';
  }

  Map<String, dynamic> _mockProcessLead(Map<String, dynamic> leadData) {
    final phone = leadData['phone']?.toString() ?? '';
    final budget = leadData['budget'] ?? 0;
    final name = leadData['name']?.toString() ?? '';

    int score = 50;
    String priority = 'low';
    String category = 'new';
    String nextAction = 'initial_call';

    // Score based on available data
    if (name.isNotEmpty) score += 10;
    if (phone.isNotEmpty) score += 10;
    if (budget is num && budget > 0) {
      score += 15;
      if (budget > 5000000) {
        score += 15;
        priority = 'high';
        category = 'premium';
      } else if (budget > 2000000) {
        score += 10;
        priority = 'medium';
        category = 'interested';
      }
    }

    if (leadData['source']?.toString() == 'referral') {
      score += 10;
    }
    if (leadData['notes']?.toString().isNotEmpty == true) {
      score += 5;
    }

    score = score.clamp(0, 100);

    if (score >= 75) {
      priority = 'high';
      nextAction = 'schedule_visit';
    } else if (score >= 50) {
      priority = 'medium';
      nextAction = 'follow_up_call';
    } else {
      priority = 'low';
      nextAction = 'nurture_sequence';
    }

    return {
      'score': score,
      'priority': priority,
      'category': category,
      'nextAction': nextAction,
      'notes': 'Lead scored $score/100 based on available data',
    };
  }

  Map<String, dynamic> _mockAnalyzeProperty(Map<String, dynamic> propertyData) {
    return {
      'estimatedValue': propertyData['price'] ?? 0,
      'marketTrend': 'stable',
      'investmentPotential': 'good',
      'recommendation': 'consider',
      'comparableProperties': 5,
      'pricePerSqft': 3500,
      'expectedAppreciation': '12-15% annually',
    };
  }

  List<String> _mockSuggestions() {
    return [
      'Follow up within 24 hours with property details',
      'Send brochure via WhatsApp',
      'Schedule site visit for this weekend',
      'Check customer budget and EMI eligibility',
      'Share customer testimonials from similar buyers',
    ];
  }

  Map<String, dynamic> _mockDecision() {
    return {
      'decision': 'proceed',
      'confidence': 0.85,
      'reasoning':
          'Based on the provided context, this appears to be a good opportunity.',
      'recommendations': [
        'Continue with current approach',
        'Monitor progress',
        'Schedule follow-up in 3 days',
      ],
      'requiresHumanApproval': false,
    };
  }

  Map<String, dynamic> _mockStats() {
    return {
      'totalInteractions': 0,
      'averageResponseTime': '1.2s',
      'satisfactionScore': 4.2,
      'lastActive': DateTime.now().toIso8601String(),
      'feedbackCount': 0,
      'leadsProcessed': 0,
      'callsHandled': 0,
      'conversionRate': 0.0,
    };
  }

  // ─── Helper Methods ───

  String _getDefaultName(AIAgentRole role) {
    return switch (role) {
      AIAgentRole.customerSupport => 'Customer Support AI',
      AIAgentRole.salesAssistant => 'Sales Assistant AI',
      AIAgentRole.propertyExpert => 'Property Expert AI',
      AIAgentRole.investmentAdvisor => 'Investment Advisor AI',
      AIAgentRole.managerAssistant => 'Manager Assistant AI',
      AIAgentRole.telecallerCoach => 'Telecaller Coach AI',
      AIAgentRole.documentAnalyzer => 'Document Analyzer AI',
      AIAgentRole.leadScorer => 'Lead Scorer AI',
    };
  }

  String _getSystemPrompt(AIAgentRole role) {
    return switch (role) {
      AIAgentRole.customerSupport =>
        'You are a helpful customer support agent for APS Dream Home real estate platform. Help customers with inquiries, complaints, and general assistance.',
      AIAgentRole.salesAssistant =>
        'You are a sales assistant for APS Dream Home. Help convert leads into customers by providing property information, pricing, and scheduling site visits.',
      AIAgentRole.propertyExpert =>
        'You are a property expert for APS Dream Home. Provide detailed information about plots, colonies, amenities, and investment potential.',
      AIAgentRole.investmentAdvisor =>
        'You are an investment advisor for APS Dream Home. Help customers understand ROI, property appreciation, and investment strategies.',
      AIAgentRole.managerAssistant =>
        'You are a manager assistant for APS Dream Home. Help managers with team performance, reports, and operational tasks.',
      AIAgentRole.telecallerCoach =>
        'You are a telecaller coach for APS Dream Home. Help telecallers with call scripts, objection handling, and lead conversion techniques.',
      AIAgentRole.documentAnalyzer =>
        'You are a document analyzer for APS Dream Home. Help analyze property documents, agreements, and legal papers.',
      AIAgentRole.leadScorer =>
        'You are a lead scoring AI for APS Dream Home. Analyze and score leads based on their data and behavior.',
    };
  }

  List<AIAgentCapability> _getCapabilities(AIAgentRole role) {
    return switch (role) {
      AIAgentRole.customerSupport => [
        AIAgentCapability(
          name: 'chat',
          description: 'Handle customer inquiries',
        ),
        AIAgentCapability(
          name: 'complaint_resolution',
          description: 'Resolve customer complaints',
        ),
        AIAgentCapability(
          name: 'ticket_management',
          description: 'Manage support tickets',
        ),
      ],
      AIAgentRole.salesAssistant => [
        AIAgentCapability(
          name: 'lead_qualification',
          description: 'Qualify incoming leads',
        ),
        AIAgentCapability(
          name: 'property_recommendation',
          description: 'Recommend properties based on needs',
        ),
        AIAgentCapability(
          name: 'site_visit_scheduling',
          description: 'Schedule property site visits',
        ),
      ],
      AIAgentRole.propertyExpert => [
        AIAgentCapability(
          name: 'property_analysis',
          description: 'Analyze property details and value',
        ),
        AIAgentCapability(
          name: 'market_comparison',
          description: 'Compare with market rates',
        ),
      ],
      AIAgentRole.investmentAdvisor => [
        AIAgentCapability(
          name: 'roi_calculation',
          description: 'Calculate return on investment',
        ),
        AIAgentCapability(
          name: 'market_trends',
          description: 'Provide market trend analysis',
        ),
      ],
      _ => [
        AIAgentCapability(
          name: 'basic_chat',
          description: 'Basic conversation handling',
        ),
      ],
    };
  }

  /// Dispose the service
  Future<void> dispose() async {
    developer.log('AI Agent Service disposed');
  }
}

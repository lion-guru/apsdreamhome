import 'dart:convert';
import 'dart:developer' as developer;
import 'package:http/http.dart' as http;

/// AI Agent Roles
enum AIAgentRole {
  customerSupport, // Help customers with queries
  salesAssistant, // Sales and property recommendations
  propertyExpert, // Property knowledge and advice
  investmentAdvisor, // Investment guidance
  managerAssistant, // Help managers with decisions
  telecallerCoach, // Train telecallers
  documentAnalyzer, // Analyze documents and extract data
  leadScorer, // Score and prioritize leads
}

/// AI Agent Capability
class AIAgentCapability {
  final String name;
  final String description;
  final List<String> actions;
  final bool requiresApproval;

  AIAgentCapability({
    required this.name,
    required this.description,
    required this.actions,
    this.requiresApproval = false,
  });
}

/// AI Agent Configuration
class AIAgentConfig {
  final AIAgentRole role;
  final String name;
  final String personality;
  final List<AIAgentCapability> capabilities;
  final Map<String, dynamic> knowledge;
  final bool canMakeDecisions;
  final bool canLearn;
  final double confidenceThreshold;

  AIAgentConfig({
    required this.role,
    required this.name,
    required this.personality,
    required this.capabilities,
    this.knowledge = const {},
    this.canMakeDecisions = false,
    this.canLearn = true,
    this.confidenceThreshold = 0.7,
  });
}

/// AI Agent Memory Entry
class AIMemoryEntry {
  final String id;
  final String userId;
  final String conversationId;
  final String message;
  final String response;
  final List<String> topics;
  final Map<String, dynamic> metadata;
  final DateTime timestamp;
  final double userSatisfaction;

  AIMemoryEntry({
    required this.id,
    required this.userId,
    required this.conversationId,
    required this.message,
    required this.response,
    this.topics = const [],
    this.metadata = const {},
    required this.timestamp,
    this.userSatisfaction = 0.0,
  });

  Map<String, dynamic> toJson() => {
        'id': id,
        'userId': userId,
        'conversationId': conversationId,
        'message': message,
        'response': response,
        'topics': topics,
        'metadata': metadata,
        'timestamp': timestamp.toIso8601String(),
        'userSatisfaction': userSatisfaction,
      };

  factory AIMemoryEntry.fromJson(Map<String, dynamic> json) => AIMemoryEntry(
        id: json['id'] as String,
        userId: json['userId'] as String,
        conversationId: json['conversationId'] as String,
        message: json['message'] as String,
        response: json['response'] as String,
        topics: List<String>.from((json['topics'] as List?) ?? []),
        metadata: (json['metadata'] as Map?)?.cast<String, dynamic>() ?? {},
        timestamp: DateTime.parse(json['timestamp'] as String),
        userSatisfaction: (json['userSatisfaction'] as num?)?.toDouble() ?? 0.0,
      );
}

/// AI Agent Learning Data
class AILearningData {
  final String pattern;
  final String context;
  final String bestResponse;
  final int usageCount;
  final double successRate;
  final DateTime lastUsed;

  AILearningData({
    required this.pattern,
    required this.context,
    required this.bestResponse,
    this.usageCount = 0,
    this.successRate = 0.0,
    required this.lastUsed,
  });

  Map<String, dynamic> toJson() => {
        'pattern': pattern,
        'context': context,
        'bestResponse': bestResponse,
        'usageCount': usageCount,
        'successRate': successRate,
        'lastUsed': lastUsed.toIso8601String(),
      };

  factory AILearningData.fromJson(Map<String, dynamic> json) => AILearningData(
        pattern: json['pattern'] as String,
        context: json['context'] as String,
        bestResponse: json['bestResponse'] as String,
        usageCount: (json['usageCount'] as num?)?.toInt() ?? 0,
        successRate: (json['successRate'] as num?)?.toDouble() ?? 0.0,
        lastUsed: DateTime.parse(json['lastUsed'] as String),
      );
}

/// AI Decision Result
class AIDecisionResult {
  final String decision;
  final String reasoning;
  final double confidence;
  final List<String> alternatives;
  final Map<String, dynamic> metadata;
  final bool requiresHumanApproval;

  AIDecisionResult({
    required this.decision,
    required this.reasoning,
    required this.confidence,
    this.alternatives = const [],
    this.metadata = const {},
    this.requiresHumanApproval = false,
  });
}

/// Advanced AI Agent Service
/// Human-like AI with memory, learning, and decision-making
class AIAgentService {
  static final AIAgentService _instance = AIAgentService._internal();
  factory AIAgentService() => _instance;
  AIAgentService._internal();

  // API Configuration
  final String _baseUrl = 'https://apsdreamhome.com/api/v1';
  final String _geminiApiKey = 'YOUR_GEMINI_API_KEY';

  // Local Storage
  // Hive boxes disabled - using in-memory storage instead
  Map<String, dynamic> _memoryBox = {};
  Map<String, dynamic> _learningBox = {};
  Map<String, dynamic> _decisionsBox = {};

  // Current Agent State
  AIAgentConfig? _currentAgent;
  String? _currentUserId;
  String? _currentConversationId;

  // Knowledge Base
  final Map<String, dynamic> _knowledgeBase = {};

  /// Initialize the AI Agent Service
  Future<void> initialize() async {
    // Load knowledge base
    await _loadKnowledgeBase();

    developer.log('AI Agent Service initialized', name: 'AIAgent');
  }

  /// Load knowledge base from storage
  Future<void> _loadKnowledgeBase() async {
    // Property knowledge
    _knowledgeBase['property_types'] = {
      'residential': ['Plot', 'Flat', 'Villa', 'House'],
      'commercial': ['Shop', 'Office', 'Showroom'],
      'agricultural': ['Farm Land', 'Farmhouse'],
    };

    _knowledgeBase['locations'] = {
      'gorakhpur': ['Paradise Residency', 'Suryoday Heights', 'Raghunath City'],
      'lucknow': ['Braj Radha Enclave', 'Gomti Nagar'],
      'varanasi': ['Ganga Nagri', 'Shivpur'],
    };

    _knowledgeBase['pricing_factors'] = [
      'Location',
      'Connectivity',
      'Development',
      'Amenities',
      'Future Growth',
    ];

    developer.log('Knowledge base loaded', name: 'AIAgent');
  }

  /// Create and configure AI Agent
  AIAgentConfig createAgent({
    required AIAgentRole role,
    String? customName,
    bool canMakeDecisions = false,
    bool canLearn = true,
  }) {
    final config = _getDefaultConfig(role);

    _currentAgent = AIAgentConfig(
      role: role,
      name: customName ?? config.name,
      personality: config.personality,
      capabilities: config.capabilities,
      canMakeDecisions: canMakeDecisions,
      canLearn: canLearn,
      knowledge: _knowledgeBase,
    );

    developer.log('Agent created: ${_currentAgent!.name} (${role.name})',
        name: 'AIAgent');
    return _currentAgent!;
  }

  /// Get default configuration for each role
  AIAgentConfig _getDefaultConfig(AIAgentRole role) {
    switch (role) {
      case AIAgentRole.customerSupport:
        return AIAgentConfig(
          role: role,
          name: 'APS Support Assistant',
          personality: 'Friendly, patient, helpful. Speaks Hindi and English. '
              'Always polite and professional. Explains things clearly.',
          capabilities: [
            AIAgentCapability(
              name: 'Answer Queries',
              description:
                  'Answer customer questions about properties, bookings, payments',
              actions: ['search_properties', 'explain_process', 'provide_info'],
            ),
            AIAgentCapability(
              name: 'Troubleshoot',
              description: 'Help with app issues, payment problems',
              actions: ['diagnose_issue', 'suggest_solution', 'escalate'],
            ),
          ],
          canMakeDecisions: false,
        );

      case AIAgentRole.salesAssistant:
        return AIAgentConfig(
          role: role,
          name: 'APS Sales Expert',
          personality: 'Enthusiastic, knowledgeable, persuasive but not pushy. '
              'Understands customer needs and suggests best options.',
          capabilities: [
            AIAgentCapability(
              name: 'Property Recommendations',
              description:
                  'Suggest properties based on budget, location, requirements',
              actions: [
                'analyze_requirements',
                'recommend_properties',
                'compare_options'
              ],
            ),
            AIAgentCapability(
              name: 'Deal Negotiation',
              description: 'Help with pricing discussions and offers',
              actions: ['suggest_price', 'explain_emi', 'calculate_offers'],
              requiresApproval: true,
            ),
          ],
          canMakeDecisions: true,
          confidenceThreshold: 0.8,
        );

      case AIAgentRole.propertyExpert:
        return AIAgentConfig(
          role: role,
          name: 'Property Guru',
          personality: 'Expert, analytical, detail-oriented. '
              'Deep knowledge of real estate market and investment.',
          capabilities: [
            AIAgentCapability(
              name: 'Market Analysis',
              description: 'Analyze property market trends and prices',
              actions: ['analyze_market', 'predict_trends', 'compare_areas'],
            ),
            AIAgentCapability(
              name: 'Legal Guidance',
              description: 'Explain property legal terms and processes',
              actions: [
                'explain_legal',
                'check_documents',
                'suggest_precautions'
              ],
            ),
          ],
          canMakeDecisions: true,
        );

      case AIAgentRole.investmentAdvisor:
        return AIAgentConfig(
          role: role,
          name: 'Investment Advisor',
          personality: 'Professional, risk-aware, growth-focused. '
              'Helps build property portfolios and maximize returns.',
          capabilities: [
            AIAgentCapability(
              name: 'ROI Analysis',
              description: 'Calculate returns on property investments',
              actions: [
                'calculate_roi',
                'predict_appreciation',
                'analyze_cashflow'
              ],
            ),
            AIAgentCapability(
              name: 'Portfolio Planning',
              description: 'Suggest investment strategies and diversification',
              actions: ['assess_risk', 'suggest_allocation', 'plan_strategy'],
              requiresApproval: true,
            ),
          ],
          canMakeDecisions: true,
          confidenceThreshold: 0.85,
        );

      case AIAgentRole.managerAssistant:
        return AIAgentConfig(
          role: role,
          name: 'Manager AI Assistant',
          personality: 'Efficient, organized, data-driven. '
              'Helps with decision-making and team management.',
          capabilities: [
            AIAgentCapability(
              name: 'Lead Analysis',
              description: 'Analyze leads and suggest priorities',
              actions: [
                'score_leads',
                'suggest_followup',
                'identify_hot_leads'
              ],
            ),
            AIAgentCapability(
              name: 'Performance Insights',
              description: 'Generate reports and insights',
              actions: [
                'generate_report',
                'identify_trends',
                'suggest_actions'
              ],
            ),
            AIAgentCapability(
              name: 'Decision Support',
              description: 'Help with business decisions',
              actions: [
                'analyze_options',
                'predict_outcomes',
                'recommend_strategy'
              ],
              requiresApproval: true,
            ),
          ],
          canMakeDecisions: true,
          confidenceThreshold: 0.75,
        );

      case AIAgentRole.telecallerCoach:
        return AIAgentConfig(
          role: role,
          name: 'Sales Coach',
          personality: 'Supportive, encouraging, expert in sales techniques. '
              'Trains telecallers to be more effective.',
          capabilities: [
            AIAgentCapability(
              name: 'Call Script Suggestions',
              description: 'Generate effective call scripts',
              actions: [
                'generate_script',
                'suggest_rebuttals',
                'optimize_pitch'
              ],
            ),
            AIAgentCapability(
              name: 'Performance Coaching',
              description: 'Analyze calls and give improvement tips',
              actions: ['review_call', 'give_feedback', 'suggest_training'],
            ),
          ],
        );

      case AIAgentRole.documentAnalyzer:
        return AIAgentConfig(
          role: role,
          name: 'Document AI',
          personality: 'Precise, thorough, detail-oriented. '
              'Extracts information accurately from documents.',
          capabilities: [
            AIAgentCapability(
              name: 'Document Analysis',
              description: 'Analyze and extract data from documents',
              actions: ['extract_data', 'verify_info', 'identify_issues'],
            ),
          ],
        );

      case AIAgentRole.leadScorer:
        return AIAgentConfig(
          role: role,
          name: 'Lead Scoring AI',
          personality: 'Analytical, objective, data-driven. '
              'Scores leads based on multiple factors.',
          capabilities: [
            AIAgentCapability(
              name: 'Lead Scoring',
              description: 'Score leads on likelihood to convert',
              actions: ['score_lead', 'identify_factors', 'prioritize_leads'],
            ),
          ],
          canMakeDecisions: true,
        );
    }
  }

  /// Process user message with AI
  Future<String> processMessage({
    required String message,
    required String userId,
    String? conversationId,
    Map<String, dynamic>? context,
  }) async {
    _currentUserId = userId;
    _currentConversationId = conversationId ?? _generateConversationId();

    // Check memory for similar past conversations
    final similarConversations = await _searchMemory(message);

    // Check learning data for patterns
    final learnedResponse = await _checkLearningPatterns(message, context);

    if (learnedResponse != null && _shouldUseLearnedResponse(learnedResponse)) {
      // Use learned response if confident
      await _storeMemory(message, learnedResponse, context);
      return learnedResponse;
    }

    // Build context for AI
    final prompt = _buildPrompt(
      message: message,
      context: context,
      memory: similarConversations,
    );

    // Call AI API
    final response = await _callAI(prompt);

    // Store in memory
    await _storeMemory(message, response, context);

    // Learn from interaction
    if (_currentAgent?.canLearn ?? true) {
      await _learnFromInteraction(message, response, context);
    }

    return response;
  }

  /// Make a decision using AI
  Future<AIDecisionResult> makeDecision({
    required String decisionType,
    required Map<String, dynamic> data,
    List<String>? options,
    String? context,
  }) async {
    if (!(_currentAgent?.canMakeDecisions ?? false)) {
      return AIDecisionResult(
        decision: 'Agent cannot make decisions',
        reasoning:
            'Current agent configuration does not allow autonomous decisions',
        confidence: 0.0,
        requiresHumanApproval: true,
      );
    }

    final prompt = _buildDecisionPrompt(
      decisionType: decisionType,
      data: data,
      options: options,
      context: context,
    );

    final response = await _callAI(prompt);
    final parsedDecision = _parseDecisionResponse(response);

    // Check if confidence meets threshold
    final requiresApproval = parsedDecision.confidence <
            (_currentAgent?.confidenceThreshold ?? 0.7) ||
        (_currentAgent?.capabilities.any((c) => c.requiresApproval) ?? false);

    // Store decision
    await _storeDecision(decisionType, data, parsedDecision);

    return AIDecisionResult(
      decision: parsedDecision.decision,
      reasoning: parsedDecision.reasoning,
      confidence: parsedDecision.confidence,
      alternatives: parsedDecision.alternatives,
      requiresHumanApproval: requiresApproval,
      metadata: parsedDecision.metadata,
    );
  }

  /// Search memory for similar conversations
  Future<List<AIMemoryEntry>> _searchMemory(String query) async {
    final entries = <AIMemoryEntry>[];
    final keywords = query.toLowerCase().split(' ');

    for (var key in _memoryBox.keys) {
      final data = _memoryBox[key];
      if (data != null) {
        final entry =
            AIMemoryEntry.fromJson(Map<String, dynamic>.from(data as Map));

        // Check for keyword matches
        final messageLower = entry.message.toLowerCase();
        if (keywords.any((k) => messageLower.contains(k))) {
          entries.add(entry);
        }
      }
    }

    // Sort by satisfaction and recency
    entries.sort((a, b) {
      final scoreA = a.userSatisfaction +
          (DateTime.now().difference(a.timestamp).inDays * 0.1);
      final scoreB = b.userSatisfaction +
          (DateTime.now().difference(b.timestamp).inDays * 0.1);
      return scoreB.compareTo(scoreA);
    });

    return entries.take(5).toList();
  }

  /// Check learning patterns
  Future<String?> _checkLearningPatterns(
      String message, Map<String, dynamic>? context) async {
    final patterns = <AILearningData>[];

    for (var key in _learningBox.keys) {
      final data = _learningBox[key];
      if (data != null) {
        patterns.add(
            AILearningData.fromJson(Map<String, dynamic>.from(data as Map)));
      }
    }

    // Find best matching pattern
    for (var pattern in patterns) {
      if (_isPatternMatch(message, pattern.pattern)) {
        if (pattern.successRate > 0.7) {
          return pattern.bestResponse;
        }
      }
    }

    return null;
  }

  /// Check if message matches pattern
  bool _isPatternMatch(String message, String pattern) {
    final msgLower = message.toLowerCase();
    final patLower = pattern.toLowerCase();

    // Simple keyword matching (can be enhanced with NLP)
    final keywords = patLower.split(' ');
    final matchCount = keywords.where((k) => msgLower.contains(k)).length;
    return matchCount >= keywords.length * 0.6;
  }

  /// Determine if learned response should be used
  bool _shouldUseLearnedResponse(String response) {
    // Check response quality criteria
    return response.length > 20 && !response.contains('I don\'t know');
  }

  /// Build AI prompt with context
  String _buildPrompt({
    required String message,
    Map<String, dynamic>? context,
    List<AIMemoryEntry>? memory,
  }) {
    final buffer = StringBuffer();

    // Agent identity
    buffer.writeln('You are ${_currentAgent?.name ?? "AI Assistant"}.');
    buffer.writeln(
        'Personality: ${_currentAgent?.personality ?? "Helpful assistant"}');
    buffer.writeln();

    // Knowledge base
    buffer.writeln('Knowledge Base:');
    buffer.writeln(jsonEncode(_knowledgeBase));
    buffer.writeln();

    // Context
    if (context != null) {
      buffer.writeln('Current Context:');
      buffer.writeln(jsonEncode(context));
      buffer.writeln();
    }

    // Memory
    if (memory != null && memory.isNotEmpty) {
      buffer.writeln('Relevant Past Conversations:');
      for (var entry in memory.take(3)) {
        buffer.writeln('User: ${entry.message}');
        buffer.writeln('You: ${entry.response}');
        buffer.writeln();
      }
    }

    // Current message
    buffer.writeln('User Message: $message');
    buffer.writeln();
    buffer.writeln(
        'Provide a helpful, accurate response. Be professional and friendly.');

    return buffer.toString();
  }

  /// Build decision prompt
  String _buildDecisionPrompt({
    required String decisionType,
    required Map<String, dynamic> data,
    List<String>? options,
    String? context,
  }) {
    final buffer = StringBuffer();

    buffer
        .writeln('You are ${_currentAgent?.name ?? "AI Decision Assistant"}.');
    buffer.writeln('Role: Decision Maker');
    buffer.writeln();

    buffer.writeln('Decision Type: $decisionType');
    buffer.writeln('Context: ${context ?? "General"}');
    buffer.writeln();

    buffer.writeln('Data:');
    buffer.writeln(jsonEncode(data));
    buffer.writeln();

    if (options != null && options.isNotEmpty) {
      buffer.writeln('Available Options:');
      for (var i = 0; i < options.length; i++) {
        buffer.writeln('${i + 1}. ${options[i]}');
      }
      buffer.writeln();
    }

    buffer.writeln('Please provide your decision in this format:');
    buffer.writeln('DECISION: [your decision]');
    buffer.writeln('REASONING: [detailed reasoning]');
    buffer.writeln('CONFIDENCE: [0.0-1.0]');
    buffer.writeln('ALTERNATIVES: [list alternatives]');

    return buffer.toString();
  }

  /// Parse decision response
  _ParsedDecision _parseDecisionResponse(String response) {
    String decision = '';
    String reasoning = '';
    double confidence = 0.0;
    List<String> alternatives = [];
    final Map<String, dynamic> metadata = {};

    final lines = response.split('\n');
    for (var line in lines) {
      if (line.startsWith('DECISION:')) {
        decision = line.substring(9).trim();
      } else if (line.startsWith('REASONING:')) {
        reasoning = line.substring(10).trim();
      } else if (line.startsWith('CONFIDENCE:')) {
        confidence = double.tryParse(line.substring(11).trim()) ?? 0.0;
      } else if (line.startsWith('ALTERNATIVES:')) {
        alternatives =
            line.substring(13).trim().split(',').map((s) => s.trim()).toList();
      }
    }

    return _ParsedDecision(
      decision: decision.isEmpty ? response : decision,
      reasoning: reasoning,
      confidence: confidence,
      alternatives: alternatives,
      metadata: metadata,
    );
  }

  /// Call AI API (Gemini)
  Future<String> _callAI(String prompt) async {
    try {
      // First try backend API
      final backendResponse = await _callBackendAI(prompt);
      if (backendResponse != null) return backendResponse;

      // Fallback to Gemini API
      final response = await http.post(
        Uri.parse(
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=$_geminiApiKey'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'contents': [
            {
              'parts': [
                {'text': prompt}
              ]
            }
          ],
          'generationConfig': {
            'temperature': 0.7,
            'maxOutputTokens': 2048,
          }
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body) as Map<String, dynamic>;
        final candidates = data['candidates'] as List<dynamic>?;
        if (candidates != null && candidates.isNotEmpty) {
          final content = candidates[0]['content'] as Map<String, dynamic>?;
          final parts = content?['parts'] as List<dynamic>?;
          if (parts != null && parts.isNotEmpty) {
            return parts[0]['text'] as String? ??
                'Sorry, I could not generate a response.';
          }
        }
        return 'Sorry, I could not generate a response.';
      }

      return 'Sorry, the AI service is currently unavailable. Please try again later.';
    } catch (e) {
      developer.log('AI API error: $e', name: 'AIAgent');
      return 'I apologize, but I am having trouble connecting. Please try again.';
    }
  }

  /// Call backend AI endpoint
  Future<String?> _callBackendAI(String prompt) async {
    try {
      final response = await http.post(
        Uri.parse('$_baseUrl/ai/chat'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'message': prompt,
          'agent_role': _currentAgent?.role.name,
          'user_id': _currentUserId,
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body) as Map<String, dynamic>;
        return data['response'] as String?;
      }
      return null;
    } catch (e) {
      return null;
    }
  }

  /// Store conversation in memory
  Future<void> _storeMemory(
    String message,
    String response,
    Map<String, dynamic>? context,
  ) async {
    if (_memoryBox.isEmpty) return;

    final entry = AIMemoryEntry(
      id: DateTime.now().millisecondsSinceEpoch.toString(),
      userId: _currentUserId ?? 'unknown',
      conversationId: _currentConversationId ?? 'unknown',
      message: message,
      response: response,
      topics: _extractTopics(message),
      metadata: context ?? {},
      timestamp: DateTime.now(),
    );

    _memoryBox[entry.id] = entry.toJson();

    // Limit memory size
    if (_memoryBox.length > 1000) {
      final oldestKey = _memoryBox.keys.first;
      _memoryBox.remove(oldestKey);
    }
  }

  /// Learn from interaction
  Future<void> _learnFromInteraction(
    String message,
    String response,
    Map<String, dynamic>? context,
  ) async {
    final pattern = _extractPattern(message);

    // Check if similar pattern exists
    AILearningData? existingData;
    for (var key in _learningBox.keys) {
      final data = AILearningData.fromJson(
          Map<String, dynamic>.from(_learningBox[key] as Map));
      if (data.pattern == pattern) {
        existingData = data;
        break;
      }
    }

    if (existingData != null) {
      // Update existing learning
      final updated = AILearningData(
        pattern: pattern,
        context: existingData.context,
        bestResponse: response,
        usageCount: existingData.usageCount + 1,
        successRate: existingData.successRate,
        lastUsed: DateTime.now(),
      );
      _learningBox[pattern] = updated.toJson();
    } else {
      // Create new learning
      final newLearning = AILearningData(
        pattern: pattern,
        context: jsonEncode(context),
        bestResponse: response,
        usageCount: 1,
        successRate: 0.5,
        lastUsed: DateTime.now(),
      );
      _learningBox[pattern] = newLearning.toJson();
    }
  }

  /// Store decision for future reference
  Future<void> _storeDecision(
    String decisionType,
    Map<String, dynamic> data,
    _ParsedDecision decision,
  ) async {
    _decisionsBox[DateTime.now().millisecondsSinceEpoch.toString()] = {
      'decisionType': decisionType,
      'data': data,
      'decision': decision.decision,
      'reasoning': decision.reasoning,
      'confidence': decision.confidence,
      'timestamp': DateTime.now().toIso8601String(),
      'agent': _currentAgent?.name,
    };
  }

  /// Extract topics from message
  List<String> _extractTopics(String message) {
    final topics = <String>[];
    final lower = message.toLowerCase();

    if (lower.contains('price') ||
        lower.contains('cost') ||
        lower.contains('₹')) {
      topics.add('pricing');
    }
    if (lower.contains('plot') ||
        lower.contains('property') ||
        lower.contains('land')) {
      topics.add('property');
    }
    if (lower.contains('location') ||
        lower.contains('area') ||
        lower.contains('city')) {
      topics.add('location');
    }
    if (lower.contains('book') ||
        lower.contains('buy') ||
        lower.contains('purchase')) {
      topics.add('booking');
    }
    if (lower.contains('emi') ||
        lower.contains('loan') ||
        lower.contains('finance')) {
      topics.add('finance');
    }

    return topics;
  }

  /// Extract pattern from message
  String _extractPattern(String message) {
    // Simple pattern extraction - can be enhanced with NLP
    final words = message.toLowerCase().split(' ');
    final keywords = words.where((w) => w.length > 3).take(5).join(' ');
    return keywords;
  }

  /// Generate conversation ID
  String _generateConversationId() {
    return 'conv_${DateTime.now().millisecondsSinceEpoch}';
  }

  /// Provide feedback to improve learning
  Future<void> provideFeedback({
    required String message,
    required String response,
    required double satisfaction,
  }) async {
    // Update learning data with feedback
    final pattern = _extractPattern(message);
    final key = 'feedback_${DateTime.now().millisecondsSinceEpoch}';

    _learningBox[key] = {
      'pattern': pattern,
      'response': response,
      'satisfaction': satisfaction,
      'timestamp': DateTime.now().toIso8601String(),
    };

    // Update success rate for pattern
    for (var boxKey in _learningBox.keys) {
      final data = AILearningData.fromJson(
          Map<String, dynamic>.from(_learningBox[boxKey] as Map));
      if (data.pattern == pattern) {
        final newSuccessRate =
            (data.successRate * data.usageCount + satisfaction) /
                (data.usageCount + 1);

        final updated = AILearningData(
          pattern: data.pattern,
          context: data.context,
          bestResponse: data.bestResponse,
          usageCount: data.usageCount,
          successRate: newSuccessRate,
          lastUsed: data.lastUsed,
        );
        _learningBox[boxKey] = updated.toJson();
        break;
      }
    }
  }

  /// Get agent statistics
  Future<Map<String, dynamic>> getAgentStats() async {
    return {
      'memory_entries': _memoryBox.length,
      'learned_patterns': _learningBox.length,
      'decisions_made': _decisionsBox.length,
      'current_agent': _currentAgent?.name ?? 'None',
      'role': _currentAgent?.role.name ?? 'None',
      'can_make_decisions': _currentAgent?.canMakeDecisions ?? false,
      'can_learn': _currentAgent?.canLearn ?? true,
    };
  }

  /// Clear all memory
  Future<void> clearMemory() async {
    _memoryBox.clear();
    _learningBox.clear();
    _decisionsBox.clear();
  }

  /// Dispose
  void dispose() {
    _memoryBox = {};
    _learningBox = {};
    _decisionsBox = {};
  }
}

/// Parsed decision helper class
class _ParsedDecision {
  final String decision;
  final String reasoning;
  final double confidence;
  final List<String> alternatives;
  final Map<String, dynamic> metadata;

  _ParsedDecision({
    required this.decision,
    required this.reasoning,
    required this.confidence,
    this.alternatives = const [],
    this.metadata = const {},
  });
}

import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_riverpod/legacy.dart';
import '../../core/services/ai_agent_service.dart';
import '../../core/services/ai_employee_agent.dart';

/// AI Provider State
class AIState {
  final bool isLoading;
  final String? currentResponse;
  final String? currentRole;
  final List<Map<String, dynamic>> conversationHistory;
  final Map<String, dynamic>? lastDecision;
  final String? error;
  final Map<String, dynamic> agentStats;

  AIState({
    this.isLoading = false,
    this.currentResponse,
    this.currentRole,
    this.conversationHistory = const [],
    this.lastDecision,
    this.error,
    this.agentStats = const {},
  });

  AIState copyWith({
    bool? isLoading,
    String? currentResponse,
    String? currentRole,
    List<Map<String, dynamic>>? conversationHistory,
    Map<String, dynamic>? lastDecision,
    String? error,
    Map<String, dynamic>? agentStats,
  }) {
    return AIState(
      isLoading: isLoading ?? this.isLoading,
      currentResponse: currentResponse ?? this.currentResponse,
      currentRole: currentRole ?? this.currentRole,
      conversationHistory: conversationHistory ?? this.conversationHistory,
      lastDecision: lastDecision ?? this.lastDecision,
      error: error,
      agentStats: agentStats ?? this.agentStats,
    );
  }
}

/// AI Notifier
class AINotifier extends StateNotifier<AIState> {
  final AIAgentService _agentService = AIAgentService();
  final AIEmployeeAgent _employeeAgent = AIEmployeeAgent();

  AINotifier() : super(AIState());

  /// Initialize AI services
  Future<void> initialize() async {
    await _agentService.initialize();
    await _employeeAgent.initialize();
    await _updateStats();
  }

  /// Send message to AI
  Future<void> sendMessage({
    required String message,
    required String userId,
    AIAgentRole? role,
    Map<String, dynamic>? context,
  }) async {
    state = state.copyWith(isLoading: true, error: null);

    try {
      // Set role if specified
      String? agentId;
      if (role != null) {
        agentId = (await _agentService.createAgent(role: role)).id;
      }

      // Get AI response
      final response = await _agentService.processMessage(
        agentId: agentId ?? 'default',
        message: message,
        context: context,
      );

      // Update conversation history
      final newHistory = [
        ...state.conversationHistory,
        {
          'role': 'user',
          'message': message,
          'timestamp': DateTime.now().toIso8601String(),
        },
        {
          'role': 'ai',
          'message': response,
          'timestamp': DateTime.now().toIso8601String(),
        }
      ];

      state = state.copyWith(
        isLoading: false,
        currentResponse: response,
        conversationHistory: newHistory,
        currentRole: role?.name,
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: 'Failed to get AI response: $e',
      );
    }
  }

  /// Make AI decision
  Future<void> makeDecision({
    required String decisionType,
    required Map<String, dynamic> data,
    List<String>? options,
    String? context,
  }) async {
    state = state.copyWith(isLoading: true, error: null);

    try {
      final decision = await _agentService.makeDecision(
        agentId: 'default',
        context: data,
      );

      state = state.copyWith(
        isLoading: false,
        lastDecision: decision,
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: 'Decision failed: $e',
      );
    }
  }

  /// Execute employee task
  Future<AIEmployeeWorkResult> executeEmployeeTask({
    required AIEmployeeTask task,
    required Map<String, dynamic> data,
    bool highPriority = false,
  }) async {
    state = state.copyWith(isLoading: true, error: null);

    try {
      final result = await _employeeAgent.executeTask(
        task: task,
        data: data,
        highPriority: highPriority,
      );

      await _updateStats();
      state = state.copyWith(isLoading: false);

      return result;
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: 'Task execution failed: $e',
      );
      rethrow;
    }
  }

  /// Switch AI role
  Future<void> switchRole(AIAgentRole role) async {
    await _agentService.createAgent(role: role);
    state = state.copyWith(currentRole: role.name);
  }

  /// Clear conversation
  void clearConversation() {
    state = state.copyWith(
      conversationHistory: [],
      currentResponse: null,
    );
  }

  /// Provide feedback
  Future<void> provideFeedback({
    required String message,
    required String response,
    required double satisfaction,
  }) async {
    await _agentService.provideFeedback(
      agentId: 'default',
      feedback: message,
      rating: satisfaction.round(),
    );
  }

  /// Update stats
  Future<void> _updateStats() async {
    final stats = await _agentService.getAgentStats(agentId: 'default');
    final metrics = _employeeAgent.getMetrics();

    state = state.copyWith(agentStats: {
      ...stats,
      'employee_tasks_completed': metrics.tasksCompleted,
      'employee_accuracy': metrics.accuracyRate,
      'time_saved_hours': metrics.timeSaved,
      'cost_saved_rupees': metrics.costSaved,
    });
  }

  /// Get daily summary
  Future<Map<String, dynamic>> getDailySummary() async {
    return await _employeeAgent.getDailySummary();
  }

  /// Get employee metrics
  AIEmployeeMetrics getEmployeeMetrics() {
    return _employeeAgent.getMetrics();
  }

  /// Dispose
  @override
  void dispose() {
    _agentService.dispose();
    _employeeAgent.dispose();
    super.dispose();
  }
}

/// AI Provider
final aiProvider = StateNotifierProvider<AINotifier, AIState>((ref) {
  return AINotifier();
});

/// AI Agent Ready Provider
final aiAgentReadyProvider = FutureProvider<bool>((ref) async {
  final aiNotifier = ref.read(aiProvider.notifier);
  await aiNotifier.initialize();
  return true;
});

/// Current AI Role Provider
final currentAIRoleProvider = Provider<String?>((ref) {
  return ref.watch(aiProvider).currentRole;
});

/// AI Agent Stats Provider
final aiAgentStatsProvider = Provider<Map<String, dynamic>>((ref) {
  return ref.watch(aiProvider).agentStats;
});

import 'dart:convert';
import 'dart:developer' as developer;
import 'ai_agent_service.dart';

/// AI Employee Task Types
enum AIEmployeeTask {
  leadFollowUp, // Follow up with leads
  scheduleAppointment, // Schedule site visits
  sendReminders, // Send payment/booking reminders
  analyzePerformance, // Generate performance reports
  prioritizeTasks, // Prioritize daily tasks
  draftCommunications, // Draft emails/messages
  verifyDocuments, // Verify KYC documents
  scoreLead, // Score lead quality
  suggestActions, // Suggest next actions
}

/// AI Employee Work Result
class AIEmployeeWorkResult {
  final bool success;
  final String taskId;
  final AIEmployeeTask taskType;
  final String summary;
  final Map<String, dynamic> details;
  final List<String> actions;
  final bool requiresHumanReview;
  final String? humanReviewReason;
  final DateTime completedAt;

  AIEmployeeWorkResult({
    required this.success,
    required this.taskId,
    required this.taskType,
    required this.summary,
    this.details = const {},
    this.actions = const [],
    this.requiresHumanReview = false,
    this.humanReviewReason,
    required this.completedAt,
  });

  Map<String, dynamic> toJson() => {
        'success': success,
        'taskId': taskId,
        'taskType': taskType.name,
        'summary': summary,
        'details': details,
        'actions': actions,
        'requiresHumanReview': requiresHumanReview,
        'humanReviewReason': humanReviewReason,
        'completedAt': completedAt.toIso8601String(),
      };
}

/// AI Employee Performance Metrics
class AIEmployeeMetrics {
  final int tasksCompleted;
  final int tasksPendingReview;
  final double accuracyRate;
  final double timeSaved; // in hours
  final double costSaved; // in rupees
  final Map<String, int> tasksByType;
  final double userSatisfaction;

  AIEmployeeMetrics({
    required this.tasksCompleted,
    required this.tasksPendingReview,
    required this.accuracyRate,
    required this.timeSaved,
    required this.costSaved,
    required this.tasksByType,
    required this.userSatisfaction,
  });
}

/// AI Employee Agent
/// Acts as a virtual employee that can perform various tasks
class AIEmployeeAgent {
  static final AIEmployeeAgent _instance = AIEmployeeAgent._internal();
  factory AIEmployeeAgent() => _instance;
  AIEmployeeAgent._internal();

  final AIAgentService _aiService = AIAgentService();

  // Performance tracking
  final List<AIEmployeeWorkResult> _completedTasks = [];

  /// Initialize the AI Employee
  Future<void> initialize() async {
    await _aiService.initialize();

    // Create manager assistant agent
    await _aiService.createAgent(role: AIAgentRole.documentAnalyzer);
    await _aiService.createAgent(role: AIAgentRole.managerAssistant);

    developer.log('AI Employee Agent initialized', name: 'AIEmployee');
  }

  /// Execute a specific task
  Future<AIEmployeeWorkResult> executeTask({
    required AIEmployeeTask task,
    required Map<String, dynamic> data,
    String? assignedBy,
    bool highPriority = false,
  }) async {
    final taskId = 'task_${DateTime.now().millisecondsSinceEpoch}';

    developer.log('Executing task: ${task.name} [$taskId]', name: 'AIEmployee');

    try {
      AIEmployeeWorkResult result;

      switch (task) {
        case AIEmployeeTask.leadFollowUp:
          result = await _handleLeadFollowUp(taskId, data);
          break;
        case AIEmployeeTask.scheduleAppointment:
          result = await _handleScheduleAppointment(taskId, data);
          break;
        case AIEmployeeTask.sendReminders:
          result = await _handleSendReminders(taskId, data);
          break;
        case AIEmployeeTask.analyzePerformance:
          result = await _handleAnalyzePerformance(taskId, data);
          break;
        case AIEmployeeTask.prioritizeTasks:
          result = await _handlePrioritizeTasks(taskId, data);
          break;
        case AIEmployeeTask.draftCommunications:
          result = await _handleDraftCommunications(taskId, data);
          break;
        case AIEmployeeTask.verifyDocuments:
          result = await _handleVerifyDocuments(taskId, data);
          break;
        case AIEmployeeTask.scoreLead:
          result = await _handleScoreLead(taskId, data);
          break;
        case AIEmployeeTask.suggestActions:
          result = await _handleSuggestActions(taskId, data);
          break;
      }

      // Track completed task
      _completedTasks.add(result);

      // Notify if human review needed
      if (result.requiresHumanReview) {
        _notifyHumanReviewRequired(result);
      }

      return result;
    } catch (e) {
      developer.log('Task execution error: $e', name: 'AIEmployee');
      return AIEmployeeWorkResult(
        success: false,
        taskId: taskId,
        taskType: task,
        summary: 'Task failed: $e',
        completedAt: DateTime.now(),
      );
    }
  }

  /// Handle lead follow-up
  Future<AIEmployeeWorkResult> _handleLeadFollowUp(
    String taskId,
    Map<String, dynamic> data,
  ) async {
    final lead = data['lead'] as Map<String, dynamic>?;
    final lastContact = data['last_contact'] as String?;

    // Use AI to determine follow-up strategy
    final decision = await _aiService.makeDecision(
      agentId: 'employee_agent',
      context: {
        'lead': lead,
        'last_contact': lastContact,
        'days_since_contact': _calculateDaysSince(lastContact),
        'lead_stage': lead?['stage'] ?? 'new',
      },
    );

    final followUpActions = <String>[];

    if ((decision['decision'] as String).contains('call')) {
      followUpActions.add('Schedule call with ${lead?['name']}');
    }
    if ((decision['decision'] as String).contains('whatsapp')) {
      followUpActions.add('Send WhatsApp message to ${lead?['phone']}');
    }
    if ((decision['decision'] as String).contains('email')) {
      followUpActions.add('Send follow-up email to ${lead?['email']}');
    }

    return AIEmployeeWorkResult(
      success: true,
      taskId: taskId,
      taskType: AIEmployeeTask.leadFollowUp,
      summary: 'Follow-up strategy determined for ${lead?['name']}',
      details: {
        'lead_id': lead?['id'],
        'strategy': decision['decision'],
        'reasoning': (decision['reasoning'] as String?),
        'confidence': (decision['confidence'] as num),
      },
      actions: followUpActions,
      requiresHumanReview: decision['requiresHumanApproval'] == true,
      humanReviewReason: decision['requiresHumanApproval'] == true
          ? 'High-value lead requires manager approval'
          : null,
      completedAt: DateTime.now(),
    );
  }

  /// Handle appointment scheduling
  Future<AIEmployeeWorkResult> _handleScheduleAppointment(
    String taskId,
    Map<String, dynamic> data,
  ) async {
    final customer = data['customer'] as Map<String, dynamic>?;
    final preferredDate = data['preferred_date'] as String?;
    final property = data['property'] as Map<String, dynamic>?;

    // AI analyzes best time slots
    final decision = await _aiService.makeDecision(
      agentId: 'employee_agent',
      context: {
        'customer': customer,
        'preferred_date': preferredDate,
        'property_location': property?['location'],
        'travel_time': property?['distance_from_office'],
      },
    );

    return AIEmployeeWorkResult(
      success: true,
      taskId: taskId,
      taskType: AIEmployeeTask.scheduleAppointment,
      summary: 'Appointment scheduled for ${customer?['name']}',
      details: {
        'customer_id': customer?['id'],
        'suggested_time': decision['decision'],
        'reasoning': (decision['reasoning'] as String?),
        'backup_options': decision['recommendations'],
      },
      actions: [
        'Confirm appointment with customer',
        'Assign agent for site visit',
        'Send location details',
        'Set reminder 2 hours before',
      ],
      completedAt: DateTime.now(),
    );
  }

  /// Handle sending reminders
  Future<AIEmployeeWorkResult> _handleSendReminders(
    String taskId,
    Map<String, dynamic> data,
  ) async {
    final reminderType = data['type'] as String;
    final customers = data['customers'] as List<dynamic>? ?? [];

    final reminders = <Map<String, dynamic>>[];

    for (var customer in customers) {
      final decision = await _aiService.makeDecision(
        decisionType: 'reminder_content',
        data: {
          'customer': customer,
          'reminder_type': reminderType,
          'days_overdue': customer['days_overdue'],
          'payment_history': customer['payment_history'],
        },
        context: 'Craft personalized reminder message',
      );

      reminders.add({
        'customer_id': customer['id'] as String,
        'message': decision['decision'],
        'channel': _determineBestChannel(customer as Map<String, dynamic>),
        'tone':
            (customer['days_overdue'] as int? ?? 0) > 7 ? 'urgent' : 'friendly',
      });
    }

    return AIEmployeeWorkResult(
      success: true,
      taskId: taskId,
      taskType: AIEmployeeTask.sendReminders,
      summary: '${reminders.length} reminders prepared',
      details: {
        'reminder_type': reminderType,
        'customers_count': customers.length,
        'reminders': reminders,
      },
      actions: reminders
          .map((r) =>
              'Send ${r['channel']} to ${r['customer_id']}: ${r['message']}')
          .toList(),
      requiresHumanReview: true,
      humanReviewReason: 'Review reminder tone before sending',
      completedAt: DateTime.now(),
    );
  }

  /// Handle performance analysis
  Future<AIEmployeeWorkResult> _handleAnalyzePerformance(
    String taskId,
    Map<String, dynamic> data,
  ) async {
    final employeeId = data['employee_id'] as String?;
    final period = data['period'] as String? ?? 'monthly';
    final metrics = data['metrics'] as Map<String, dynamic>? ?? {};

    // AI analyzes patterns and provides insights
    final analysis = await _aiService.processMessage(
      message: 'Analyze performance metrics: ${jsonEncode(metrics)}. '
          'Provide insights, trends, and recommendations.',
      agentId: 'system',
      context: {
        'employee_id': employeeId,
        'period': period,
        'analysis_type': 'performance_review',
      },
    );

    return AIEmployeeWorkResult(
      success: true,
      taskId: taskId,
      taskType: AIEmployeeTask.analyzePerformance,
      summary: 'Performance analysis completed for $period period',
      details: {
        'employee_id': employeeId,
        'period': period,
        'analysis': analysis,
        'key_metrics': metrics.keys.toList(),
      },
      actions: [
        'Schedule review meeting',
        'Set improvement goals',
        'Identify training needs',
      ],
      completedAt: DateTime.now(),
    );
  }

  /// Handle task prioritization
  Future<AIEmployeeWorkResult> _handlePrioritizeTasks(
    String taskId,
    Map<String, dynamic> data,
  ) async {
    final tasks = data['tasks'] as List<dynamic>? ?? [];
    final constraints = data['constraints'] as Map<String, dynamic>? ?? {};

    // AI prioritizes based on urgency, impact, and constraints
    final decision = await _aiService.makeDecision(
      decisionType: 'task_prioritization',
      data: {
        'tasks': tasks,
        'constraints': constraints,
        'available_hours': constraints['available_hours'] ?? 8,
      },
      context: 'Prioritize tasks for maximum productivity',
    );

    final prioritizedTasks = <Map<String, dynamic>>[];

    // Parse AI prioritization
    for (var task in tasks) {
      final taskMap = task as Map<String, dynamic>;
      prioritizedTasks.add({
        'task': taskMap,
        'priority': _calculatePriority(taskMap, (decision['decision'] as String?) ?? ''),
        'suggested_time': _suggestTimeSlot(taskMap),
      });
    }

    // Sort by priority
    prioritizedTasks
        .sort((a, b) => (b['priority'] as int).compareTo(a['priority'] as int));

    return AIEmployeeWorkResult(
      success: true,
      taskId: taskId,
      taskType: AIEmployeeTask.prioritizeTasks,
      summary: '${tasks.length} tasks prioritized',
      details: {
        'prioritized_tasks': prioritizedTasks,
        'strategy': decision['reasoning'],
      },
      actions: prioritizedTasks
          .take(5)
          .map((t) =>
              '${t['priority']}. ${t['task']['title']} - ${t['suggested_time']}')
          .toList(),
      completedAt: DateTime.now(),
    );
  }

  /// Handle drafting communications
  Future<AIEmployeeWorkResult> _handleDraftCommunications(
    String taskId,
    Map<String, dynamic> data,
  ) async {
    final type = data['type'] as String; // email, whatsapp, sms
    final recipient = data['recipient'] as Map<String, dynamic>?;
    final purpose = data['purpose'] as String;
    final context = data['context'] as Map<String, dynamic>?;

    // AI drafts personalized message
    final draft = await _aiService.processMessage(
      message: 'Draft a $type message to ${recipient?['name']} for: $purpose. '
          'Context: ${jsonEncode(context)}',
      agentId: 'system',
      context: {
        'recipient_type': recipient?['type'],
        'communication_type': type,
        'tone': context?['tone'] ?? 'professional',
      },
    );

    return AIEmployeeWorkResult(
      success: true,
      taskId: taskId,
      taskType: AIEmployeeTask.draftCommunications,
      summary: '${type.toUpperCase()} draft prepared for ${recipient?['name']}',
      details: {
        'recipient': recipient,
        'purpose': purpose,
        'draft': draft,
        'character_count': draft.length,
      },
      actions: [
        'Review and edit draft',
        'Send to recipient',
        'Schedule follow-up',
      ],
      requiresHumanReview: true,
      humanReviewReason: 'Please review before sending',
      completedAt: DateTime.now(),
    );
  }

  /// Handle document verification
  Future<AIEmployeeWorkResult> _handleVerifyDocuments(
    String taskId,
    Map<String, dynamic> data,
  ) async {
    final documents = data['documents'] as List<dynamic>? ?? [];
    final verificationType = data['type'] as String? ?? 'kyc';

    final verificationResults = <Map<String, dynamic>>[];

    for (var doc in documents) {
      // AI checks document validity
      final decision = await _aiService.makeDecision(
        decisionType: 'document_verification',
        data: {
          'document_type': doc['type'],
          'document_data': doc['data'],
          'verification_type': verificationType,
        },
        context: 'Verify document authenticity and completeness',
      );

      verificationResults.add({
        'document_id': doc['id'],
        'status': (decision['confidence'] as num) > 0.8 ? 'verified' : 'review_needed',
        'issues':
            (decision['decision'] as String).contains('issue') ? decision['decision'] : null,
        'confidence': (decision['confidence'] as num),
      });
    }

    final allVerified =
        verificationResults.every((r) => r['status'] == 'verified');

    return AIEmployeeWorkResult(
      success: true,
      taskId: taskId,
      taskType: AIEmployeeTask.verifyDocuments,
      summary: '${documents.length} documents processed',
      details: {
        'verification_type': verificationType,
        'results': verificationResults,
        'all_verified': allVerified,
      },
      actions: verificationResults
          .where((r) => r['status'] == 'review_needed')
          .map((r) => 'Manual review needed for doc ${r['document_id']}')
          .toList(),
      requiresHumanReview: !allVerified,
      humanReviewReason: allVerified
          ? null
          : '${verificationResults.where((r) => r['status'] != 'verified').length} documents need review',
      completedAt: DateTime.now(),
    );
  }

  /// Handle lead scoring
  Future<AIEmployeeWorkResult> _handleScoreLead(
    String taskId,
    Map<String, dynamic> data,
  ) async {
    final lead = data['lead'] as Map<String, dynamic>?;

    // AI scores lead based on multiple factors
    final decision = await _aiService.makeDecision(
      decisionType: 'lead_scoring',
      data: {
        'lead': lead,
        'source': lead?['source'],
        'interactions': lead?['interactions'],
        'budget': lead?['budget'],
        'timeline': lead?['timeline'],
        'location_preference': lead?['preferred_location'],
      },
      context: 'Score lead quality and conversion probability',
    );

    final score = ((decision['confidence'] as num) * 100).round();

    String category;
    if (score >= 80) {
      category = 'Hot';
    } else if (score >= 60)
      category = 'Warm';
    else if (score >= 40)
      category = 'Cold';
    else
      category = 'Low Priority';

    return AIEmployeeWorkResult(
      success: true,
      taskId: taskId,
      taskType: AIEmployeeTask.scoreLead,
      summary: 'Lead scored: $score/100 ($category)',
      details: {
        'lead_id': lead?['id'],
        'score': score,
        'category': category,
        'factors': decision['reasoning'],
        'conversion_probability': '${(decision['confidence'] as num) * 100}%',
      },
      actions: [
        if (score >= 80) 'Immediate follow-up required',
        if (score >= 60) 'Schedule call within 24 hours',
        if (score < 60) 'Add to nurturing campaign',
        'Update CRM with score',
      ],
      completedAt: DateTime.now(),
    );
  }

  /// Handle action suggestions
  Future<AIEmployeeWorkResult> _handleSuggestActions(
    String taskId,
    Map<String, dynamic> data,
  ) async {
    final situation = data['situation'] as String;
    final context = data['context'] as Map<String, dynamic>?;

    // AI suggests best actions
    final suggestions = await _aiService.processMessage(
      message:
          'Given this situation: $situation. Suggest the best actions to take. '
          'Context: ${jsonEncode(context)}',
      agentId: 'system',
      context: {
        'situation_type': data['type'],
        'urgency': context?['urgency'] ?? 'normal',
      },
    );

    // Parse suggestions into actions
    final actions = suggestions
        .split('\n')
        .where((line) => line.trim().isNotEmpty)
        .map((line) => line.replaceAll(RegExp(r'^\d+\.\s*'), '').trim())
        .toList();

    return AIEmployeeWorkResult(
      success: true,
      taskId: taskId,
      taskType: AIEmployeeTask.suggestActions,
      summary: '${actions.length} actions suggested for: $situation',
      details: {
        'situation': situation,
        'suggested_actions': actions,
        'context': context,
      },
      actions: actions,
      completedAt: DateTime.now(),
    );
  }

  /// Batch execute multiple tasks
  Future<List<AIEmployeeWorkResult>> executeBatchTasks(
    List<Map<String, dynamic>> tasks,
  ) async {
    final results = <AIEmployeeWorkResult>[];

    for (var taskData in tasks) {
      final task = AIEmployeeTask.values.firstWhere(
        (t) => t.name == taskData['task_type'],
        orElse: () => AIEmployeeTask.suggestActions,
      );

      final result = await executeTask(
        task: task,
        data: (taskData['data'] as Map<String, dynamic>?) ?? {},
        highPriority: (taskData['high_priority'] as bool?) ?? false,
      );

      results.add(result);
    }

    return results;
  }

  /// Get daily work summary
  Future<Map<String, dynamic>> getDailySummary() async {
    final today = DateTime.now();
    final todayTasks = _completedTasks
        .where(
          (t) =>
              t.completedAt.year == today.year &&
              t.completedAt.month == today.month &&
              t.completedAt.day == today.day,
        )
        .toList();

    final byType = <String, int>{};
    for (var task in todayTasks) {
      byType[task.taskType.name] = (byType[task.taskType.name] ?? 0) + 1;
    }

    return {
      'date': today.toIso8601String(),
      'total_tasks': todayTasks.length,
      'successful': todayTasks.where((t) => t.success).length,
      'needs_review': todayTasks.where((t) => t.requiresHumanReview).length,
      'by_type': byType,
      'tasks': todayTasks.map((t) => t.toJson()).toList(),
    };
  }

  /// Get performance metrics
  AIEmployeeMetrics getMetrics() {
    final totalTasks = _completedTasks.length;
    final successfulTasks = _completedTasks.where((t) => t.success).length;
    final pendingReview =
        _completedTasks.where((t) => t.requiresHumanReview).length;

    final byType = <String, int>{};
    for (var task in _completedTasks) {
      byType[task.taskType.name] = (byType[task.taskType.name] ?? 0) + 1;
    }

    // Estimate time saved (simplified calculation)
    final timeSaved = totalTasks * 0.5; // 30 minutes per task
    final costSaved = timeSaved * 200; // ₹200 per hour

    return AIEmployeeMetrics(
      tasksCompleted: totalTasks,
      tasksPendingReview: pendingReview,
      accuracyRate: totalTasks > 0 ? successfulTasks / totalTasks : 0.0,
      timeSaved: timeSaved,
      costSaved: costSaved,
      tasksByType: byType,
      userSatisfaction: 0.85, // Can be calculated from feedback
    );
  }

  /// Helper: Calculate days since date
  int _calculateDaysSince(String? dateStr) {
    if (dateStr == null) return 999;
    try {
      final date = DateTime.parse(dateStr);
      return DateTime.now().difference(date).inDays;
    } catch (e) {
      return 999;
    }
  }

  /// Helper: Determine best communication channel
  String _determineBestChannel(Map<String, dynamic> customer) {
    final lastResponse = customer['last_response_channel'] as String?;
    if (lastResponse != null && lastResponse.isNotEmpty) return lastResponse;

    if (customer['phone'] != null) return 'whatsapp';
    if (customer['email'] != null) return 'email';
    return 'sms';
  }

  /// Helper: Calculate task priority
  int _calculatePriority(Map<String, dynamic> task, String strategy) {
    int priority = 50; // Base priority

    if (task['urgent'] == true) priority += 30;
    if (task['high_value'] == true) priority += 20;
    if (task['deadline'] != null) {
      final daysLeft = _calculateDaysSince(task['deadline'] as String?).abs();
      if (daysLeft <= 1) {
        priority += 25;
      } else if (daysLeft <= 3) {
        priority += 15;
      }
    }

    return priority.clamp(0, 100);
  }

  /// Helper: Suggest time slot
  String _suggestTimeSlot(Map<String, dynamic> task) {
    final type = task['type'] as String?;

    switch (type) {
      case 'call':
        return '10:00 AM - 12:00 PM';
      case 'meeting':
        return '2:00 PM - 4:00 PM';
      case 'documentation':
        return '4:00 PM - 6:00 PM';
      default:
        return 'Any time';
    }
  }

  /// Helper: Notify human review required
  void _notifyHumanReviewRequired(AIEmployeeWorkResult result) {
    developer.log(
      'Human review required for task ${result.taskId}: ${result.humanReviewReason}',
      name: 'AIEmployee',
    );
    // TODO: Send notification to manager
  }

  /// Dispose
  void dispose() {
    _aiService.dispose();
  }
}

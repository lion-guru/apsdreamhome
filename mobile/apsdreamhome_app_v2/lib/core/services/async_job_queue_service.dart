import 'dart:async';
import 'dart:convert';
import 'dart:math';

import 'package:rxdart/rxdart.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../services/api_service.dart';
import '../utils/logger.dart';

/// Job types for the async queue
enum JobType {
  sendNotification,
  sendEmail,
  sendSms,
  generatePdf,
  generateReport,
  syncData,
  uploadFile,
  processImage,
}

/// Job status
enum JobStatus {
  pending,
  processing,
  completed,
  failed,
  retrying,
}

/// Represents a job in the queue
class QueueJob {
  final String id;
  final JobType type;
  final Map<String, dynamic> payload;
  final int priority; // Higher = more urgent
  final int maxRetries;
  int retryCount;
  JobStatus status;
  String? error;
  DateTime createdAt;
  DateTime? startedAt;
  DateTime? completedAt;
  Map<String, dynamic>? result;

  QueueJob({
    required this.id,
    required this.type,
    required this.payload,
    this.priority = 0,
    this.maxRetries = 3,
    this.retryCount = 0,
    this.status = JobStatus.pending,
    this.error,
    DateTime? createdAt,
    this.startedAt,
    this.completedAt,
    this.result,
  }) : createdAt = createdAt ?? DateTime.now();

  Map<String, dynamic> toJson() => {
    'id': id,
    'type': type.name,
    'payload': payload,
    'priority': priority,
    'maxRetries': maxRetries,
    'retryCount': retryCount,
    'status': status.name,
    'error': error,
    'createdAt': createdAt.toIso8601String(),
    'startedAt': startedAt?.toIso8601String(),
    'completedAt': completedAt?.toIso8601String(),
    'result': result,
  };

  factory QueueJob.fromJson(Map<String, dynamic> json) => QueueJob(
    id: json['id'] as String,
    type: JobType.values.byName(json['type'] as String),
    payload: Map<String, dynamic>.from(json['payload'] as Map),
    priority: json['priority'] as int? ?? 0,
    maxRetries: json['maxRetries'] as int? ?? 3,
    retryCount: json['retryCount'] as int? ?? 0,
    status: JobStatus.values.byName(json['status'] as String),
    error: json['error'] as String?,
    createdAt: DateTime.parse(json['createdAt'] as String),
    startedAt: json['startedAt'] != null ? DateTime.parse(json['startedAt'] as String) : null,
    completedAt: json['completedAt'] != null ? DateTime.parse(json['completedAt'] as String) : null,
    result: json['result'] != null ? Map<String, dynamic>.from(json['result'] as Map) : null,
  );

  QueueJob copyWith({
    String? id,
    JobType? type,
    Map<String, dynamic>? payload,
    int? priority,
    int? maxRetries,
    int? retryCount,
    JobStatus? status,
    String? error,
    DateTime? createdAt,
    DateTime? startedAt,
    DateTime? completedAt,
    Map<String, dynamic>? result,
  }) {
    return QueueJob(
      id: id ?? this.id,
      type: type ?? this.type,
      payload: payload ?? this.payload,
      priority: priority ?? this.priority,
      maxRetries: maxRetries ?? this.maxRetries,
      retryCount: retryCount ?? this.retryCount,
      status: status ?? this.status,
      error: error ?? this.error,
      createdAt: createdAt ?? this.createdAt,
      startedAt: startedAt ?? this.startedAt,
      completedAt: completedAt ?? this.completedAt,
      result: result ?? this.result,
    );
  }
}

/// Result of job processing
class JobResult {
  final bool success;
  final Map<String, dynamic>? data;
  final String? error;

  JobResult({required this.success, this.data, this.error});

  factory JobResult.success([Map<String, dynamic>? data]) => JobResult(success: true, data: data);
  factory JobResult.failure(String error) => JobResult(success: false, error: error);
}

/// Job processor interface
abstract class JobProcessor {
  JobType get jobType;
  Future<JobResult> process(QueueJob job);
}

/// Notification job processor
class SendNotificationProcessor implements JobProcessor {
  final ApiService _apiService;

  SendNotificationProcessor(this._apiService);

  @override
  JobType get jobType => JobType.sendNotification;

  @override
  Future<JobResult> process(QueueJob job) async {
    try {
      final response = await _apiService.post('/api/v2/mobile/notifications/send', data: job.payload);
      if (response['success'] == true) {
        final data = response['data'] as Map<String, dynamic>?;
        return JobResult.success({'notificationId': data?['id'] as String?});
      }
      return JobResult.failure((response['message'] as String?) ?? 'Failed to send notification');
    } catch (e) {
      return JobResult.failure(e.toString());
    }
  }
}

/// Email job processor
class SendEmailProcessor implements JobProcessor {
  final ApiService _apiService;

  SendEmailProcessor(this._apiService);

  @override
  JobType get jobType => JobType.sendEmail;

  @override
  Future<JobResult> process(QueueJob job) async {
    try {
      final response = await _apiService.post('/api/v2/mobile/email/send', data: job.payload);
      if (response['success'] == true) {
        final data = response['data'] as Map<String, dynamic>?;
        return JobResult.success({'emailId': data?['id'] as String?});
      }
      return JobResult.failure((response['message'] as String?) ?? 'Failed to send email');
    } catch (e) {
      return JobResult.failure(e.toString());
    }
  }
}

/// SMS job processor
class SendSmsProcessor implements JobProcessor {
  final ApiService _apiService;

  SendSmsProcessor(this._apiService);

  @override
  JobType get jobType => JobType.sendSms;

  @override
  Future<JobResult> process(QueueJob job) async {
    try {
      final response = await _apiService.post('/api/v2/mobile/sms/send', data: job.payload);
      if (response['success'] == true) {
        final data = response['data'] as Map<String, dynamic>?;
        return JobResult.success({'smsId': data?['id'] as String?});
      }
      return JobResult.failure((response['message'] as String?) ?? 'Failed to send SMS');
    } catch (e) {
      return JobResult.failure(e.toString());
    }
  }
}

/// PDF generation job processor
class GeneratePdfProcessor implements JobProcessor {
  final ApiService _apiService;

  GeneratePdfProcessor(this._apiService);

  @override
  JobType get jobType => JobType.generatePdf;

  @override
  Future<JobResult> process(QueueJob job) async {
    try {
      final response = await _apiService.post('/api/v2/mobile/pdf/generate', data: job.payload);
      if (response['success'] == true) {
        final data = response['data'] as Map<String, dynamic>?;
        return JobResult.success({'pdfUrl': data?['url'] as String?});
      }
      return JobResult.failure((response['message'] as String?) ?? 'Failed to generate PDF');
    } catch (e) {
      return JobResult.failure(e.toString());
    }
  }
}

/// Report generation job processor
class GenerateReportProcessor implements JobProcessor {
  final ApiService _apiService;

  GenerateReportProcessor(this._apiService);

  @override
  JobType get jobType => JobType.generateReport;

  @override
  Future<JobResult> process(QueueJob job) async {
    try {
      final response = await _apiService.post('/api/v2/mobile/reports/generate', data: job.payload);
      if (response['success'] == true) {
        final data = response['data'] as Map<String, dynamic>?;
        return JobResult.success({'reportId': data?['id'] as String?});
      }
      return JobResult.failure((response['message'] as String?) ?? 'Failed to generate report');
    } catch (e) {
      return JobResult.failure(e.toString());
    }
  }
}

/// Async Job Queue Service
class AsyncJobQueueService {
  static final AsyncJobQueueService _instance = AsyncJobQueueService._internal();
  factory AsyncJobQueueService() => _instance;
  
  AsyncJobQueueService._internal();

  final BehaviorSubject<List<QueueJob>> _jobsSubject = BehaviorSubject.seeded([]);
  final Map<JobType, JobProcessor> _processors = {};
  SharedPreferences? _prefs;
  Timer? _processingTimer;
  bool _isProcessing = false;
  static const String _storageKey = 'async_job_queue';

  Stream<List<QueueJob>> get jobsStream => _jobsSubject.stream;
  List<QueueJob> get currentJobs => _jobsSubject.value;

  /// Initialize the queue service
  Future<void> initialize() async {
    _prefs = await SharedPreferences.getInstance();
    _loadPersistedJobs();
    _registerDefaultProcessors();
    _startProcessing();
    AppLogger.info('AsyncJobQueueService initialized');
  }

  void _registerDefaultProcessors() {
    final apiService = ApiService();
    registerProcessor(SendNotificationProcessor(apiService));
    registerProcessor(SendEmailProcessor(apiService));
    registerProcessor(SendSmsProcessor(apiService));
    registerProcessor(GeneratePdfProcessor(apiService));
    registerProcessor(GenerateReportProcessor(apiService));
  }

  void registerProcessor(JobProcessor processor) {
    _processors[processor.jobType] = processor;
    AppLogger.info('Registered processor for ${processor.jobType.name}');
  }

  void _loadPersistedJobs() {
    if (_prefs == null) return;
    final jobsJson = _prefs!.getStringList(_storageKey) ?? [];
    final jobs = jobsJson
        .map((json) => QueueJob.fromJson(jsonDecode(json) as Map<String, dynamic>))
        .where((job) => job.status == JobStatus.pending || job.status == JobStatus.retrying)
        .toList()
      ..sort((a, b) => b.priority.compareTo(a.priority)); // Higher priority first

    _jobsSubject.add(jobs);
    AppLogger.info('Loaded ${jobs.length} pending jobs from storage');
  }

  void _persistJobs() {
    if (_prefs == null) return;
    final jobsJson = currentJobs.map((job) => jsonEncode(job.toJson())).toList();
    _prefs!.setStringList(_storageKey, jobsJson);
  }

  /// Add a job to the queue
  String enqueueJob({
    required JobType type,
    required Map<String, dynamic> payload,
    int priority = 0,
    int maxRetries = 3,
  }) {
    final job = QueueJob(
      id: _generateJobId(),
      type: type,
      payload: payload,
      priority: priority,
      maxRetries: maxRetries,
    );

    final jobs = [...currentJobs, job]
      ..sort((a, b) => b.priority.compareTo(a.priority));
    
    _jobsSubject.add(jobs);
    _persistJobs();
    
    AppLogger.info('Enqueued job ${job.id} of type ${type.name} with priority $priority');
    return job.id;
  }

  /// Convenience methods for common job types
  String enqueueNotification({
    required String userId,
    required String title,
    required String message,
    String? type,
    int priority = 0,
  }) => enqueueJob(
    type: JobType.sendNotification,
    payload: {
      'userId': userId,
      'title': title,
      'message': message,
      if (type != null) 'type': type,
    },
    priority: priority,
  );

  String enqueueEmail({
    required String to,
    required String subject,
    required String body,
    String? template,
    int priority = 0,
  }) => enqueueJob(
    type: JobType.sendEmail,
    payload: {
      'to': to,
      'subject': subject,
      'body': body,
      if (template != null) 'template': template,
    },
    priority: priority,
  );

  String enqueueSms({
    required String phone,
    required String message,
    int priority = 0,
  }) => enqueueJob(
    type: JobType.sendSms,
    payload: {'phone': phone, 'message': message},
    priority: priority,
  );

  String enqueuePdf({
    required String template,
    required Map<String, dynamic> data,
    String? filename,
    int priority = 0,
  }) => enqueueJob(
    type: JobType.generatePdf,
    payload: {
      'template': template,
      'data': data,
      if (filename != null) 'filename': filename,
    },
    priority: priority,
  );

  String enqueueReport({
    required String reportType,
    required Map<String, dynamic> parameters,
    int priority = 0,
  }) => enqueueJob(
    type: JobType.generateReport,
    payload: {
      'reportType': reportType,
      'parameters': parameters,
    },
    priority: priority,
  );

  /// Get job by ID
  QueueJob? getJob(String jobId) {
    try {
      return currentJobs.firstWhere((job) => job.id == jobId);
    } catch (e) {
      return null;
    }
  }

  /// Cancel a pending job
  bool cancelJob(String jobId) {
    final job = getJob(jobId);
    if (job == null || job.status != JobStatus.pending) {
      return false;
    }

    final jobs = currentJobs
        .where((j) => j.id != jobId)
        .toList();
    
    _jobsSubject.add(jobs);
    _persistJobs();
    return true;
  }

  /// Retry a failed job
  bool retryJob(String jobId) {
    final job = getJob(jobId);
    if (job == null || job.status != JobStatus.failed) {
      return false;
    }

    if (job.retryCount >= job.maxRetries) {
      AppLogger.warning('Job ${job.id} exceeded max retries');
      return false;
    }

    final updatedJob = job.copyWith(
      status: JobStatus.retrying,
      retryCount: job.retryCount + 1,
      error: null,
    );

    final jobs = currentJobs
        .map((j) => j.id == jobId ? updatedJob : j)
        .toList();
    
    _jobsSubject.add(jobs);
    _persistJobs();
    return true;
  }

  void _startProcessing() {
    if (_processingTimer?.isActive ?? false) {
      return;
    }

    _processingTimer = Timer.periodic(const Duration(seconds: 5), (_) {
      _processQueue();
    });
    
    AppLogger.info('Job queue processing started');
  }

  Future<void> _processQueue() async {
    if (_isProcessing || currentJobs.isEmpty) return;

    _isProcessing = true;

    try {
      final pendingJobs = currentJobs
          .where((job) => job.status == JobStatus.pending || job.status == JobStatus.retrying)
          .toList();

      for (final job in pendingJobs) {
        if (!_isProcessing) break; // Allow cancellation
        await _processJob(job);
      }
    } finally {
      _isProcessing = false;
    }
  }

  Future<void> _processJob(QueueJob job) async {
    final processor = _processors[job.type];
    if (processor == null) {
      _markJobFailed(job, 'No processor registered for ${job.type.name}');
      return;
    }

    // Update job status to processing
    _updateJobStatus(job.id, JobStatus.processing, startedAt: DateTime.now());

    try {
      final result = await processor.process(job);
      
      if (result.success) {
        _updateJobStatus(
          job.id, 
          JobStatus.completed, 
          completedAt: DateTime.now(),
          result: result.data,
        );
        AppLogger.info('Job ${job.id} completed successfully');
      } else {
        _handleJobFailure(job, result.error ?? 'Unknown error');
      }
    } catch (e) {
      _handleJobFailure(job, e.toString());
    }
  }

  void _updateJobStatus(
    String jobId,
    JobStatus status, {
    DateTime? startedAt,
    DateTime? completedAt,
    Map<String, dynamic>? result,
    String? error,
  }) {
    final job = getJob(jobId);
    if (job == null) return;

    final updatedJob = job.copyWith(
      status: status,
      startedAt: startedAt ?? job.startedAt,
      completedAt: completedAt ?? job.completedAt,
      result: result ?? job.result,
      error: error ?? job.error,
    );

    final jobs = currentJobs
        .map((j) => j.id == jobId ? updatedJob : j)
        .toList();

    _jobsSubject.add(jobs);
    _persistJobs();
  }

  void _handleJobFailure(QueueJob job, String error) {
    if (job.retryCount < job.maxRetries) {
      // Schedule retry with exponential backoff
      final delay = Duration(seconds: (job.retryCount + 1) * 30);
      Future.delayed(delay, () => retryJob(job.id));
      
      _updateJobStatus(
        job.id,
        JobStatus.retrying,
        error: 'Retry ${job.retryCount + 1}/${job.maxRetries}: $error',
      );
      AppLogger.warning('Job ${job.id} failed, scheduling retry: $error');
    } else {
      _updateJobStatus(
        job.id,
        JobStatus.failed,
        completedAt: DateTime.now(),
        error: 'Max retries exceeded: $error',
      );
      AppLogger.error('Job ${job.id} failed permanently: $error');
    }
  }

  void _markJobFailed(QueueJob job, String error) {
    _updateJobStatus(
      job.id,
      JobStatus.failed,
      completedAt: DateTime.now(),
      error: error,
    );
  }

  String _generateJobId() => 'job_${DateTime.now().millisecondsSinceEpoch}_${(Random().nextDouble() * 10000).toInt()}';

  /// Get jobs by status
  List<QueueJob> getJobsByStatus(JobStatus status) {
    return currentJobs.where((job) => job.status == status).toList();
  }

  /// Get jobs by type
  List<QueueJob> getJobsByType(JobType type) {
    return currentJobs.where((job) => job.type == type).toList();
  }

  /// Clean up completed/failed jobs older than specified duration
  Future<void> cleanupOldJobs({Duration maxAge = const Duration(days: 7)}) async {
    final cutoff = DateTime.now().subtract(maxAge);
    final initialCount = currentJobs.length;
    
    final jobs = currentJobs.where((job) {
      if (job.status == JobStatus.completed || job.status == JobStatus.failed) {
        return job.completedAt == null || job.completedAt!.isAfter(cutoff);
      }
      return true; // Keep pending/processing/retrying jobs
    }).toList();

    _jobsSubject.add(jobs);
    _persistJobs();
    
    final removed = initialCount - jobs.length;
    if (removed > 0) {
      AppLogger.info('Cleaned up $removed old jobs');
    }
  }

  void dispose() {
    _processingTimer?.cancel();
    _jobsSubject.close();
  }
}
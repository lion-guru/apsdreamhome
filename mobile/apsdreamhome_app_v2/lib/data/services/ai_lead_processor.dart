import 'dart:convert';
import 'dart:io';

// import 'package:google_mlkit_text_recognition/google_mlkit_text_recognition.dart';
import 'package:http/http.dart' as http;

import '../../core/services/api_service.dart';
import '../../core/utils/logger.dart';
import '../../data/models/lead_model.dart';

/// AI Lead Processor Service
/// Extracts lead data from photos, documents, contacts
/// Validates and creates leads automatically
class AILeadProcessor {
  // final TextRecognizer _textRecognizer = TextRecognizer();

  final ApiService _api;

  AILeadProcessor({ApiService? api}) : _api = api ?? ApiService();

  // AI/ML API Endpoints
  static const String _geminiApiKey = 'YOUR_GEMINI_API_KEY';
  static const String _geminiEndpoint =
      'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';

  // ==================== PHOTO TO LEAD ====================

  /// Process Photo and Extract Lead Information
  Future<ProcessedLeadResult> processPhoto({
    required File imageFile,
    required String sourceType,
    String? uploadedBy,
    String? notes,
  }) async {
    try {
      AppLogger.info('Processing photo for lead extraction: ${imageFile.path}');

      final extractedText = await _extractTextFromImage(imageFile);
      AppLogger.info('Extracted text: $extractedText');

      final structuredData = await _aiExtractLeadData(
        rawText: extractedText,
        sourceType: sourceType,
      );

      final validation = _validateLeadData(structuredData);
      final genuinenessScore = await _calculateGenuinenessScore(structuredData);

      final lead = LeadModel(
        id: '',
        name: (structuredData['name'] as String?) ?? 'Unknown',
        phone: (structuredData['phone'] as String?) ?? '',
        email: structuredData['email'] as String?,
        source: _mapSourceType(sourceType),
        status: genuinenessScore > 70 ? 'new' : 'review_required',
        assignedTo: null,
        assignedToName: null,
        interestedIn: structuredData['requirements'] as String?,
        budgetMax: structuredData['budget'] != null
            ? double.tryParse(structuredData['budget'].toString())
            : null,
        followUpNotes: notes ??
            'Extracted from photo: $sourceType\nGenuineness: $genuinenessScore%',
        customFields: {
          'isGenuine': genuinenessScore > 60,
          'genuinenessScore': genuinenessScore,
          'extractedFromPhoto': true,
          'extractedData': structuredData,
        },
        createdAt: DateTime.now(),
        updatedAt: DateTime.now(),
      );

      String? leadId;
      if (genuinenessScore > 50) {
        leadId = await _saveLead(lead, imageFile);
      }

      return ProcessedLeadResult(
        success: true,
        lead: lead,
        leadId: leadId,
        extractedData: structuredData,
        genuinenessScore: genuinenessScore,
        validationErrors: validation.errors,
        isReadyForAssignment: genuinenessScore > 70 && validation.isValid,
      );
    } catch (e) {
      AppLogger.error('Error processing photo for lead', e);
      return ProcessedLeadResult(
        success: false,
        error: e.toString(),
      );
    }
  }

  /// Extract Text from Image using ML Kit OCR
  Future<String> _extractTextFromImage(File imageFile) async {
    try {
      // TODO: Re-enable ML Kit OCR once SDK 36 issues are resolved
      AppLogger.warning(
          'ML Kit OCR is temporarily disabled due to SDK compatibility issues.');
      return '';
    } catch (e) {
      AppLogger.error('Error extracting text from image', e);
      return '';
    }
  }

  /// AI Extraction using Gemini/GPT to structure the data
  Future<Map<String, dynamic>> _aiExtractLeadData({
    required String rawText,
    required String sourceType,
  }) async {
    try {
      final prompt = _buildExtractionPrompt(rawText, sourceType);

      final response = await http.post(
        Uri.parse('$_geminiEndpoint?key=$_geminiApiKey'),
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
            'temperature': 0.1,
            'maxOutputTokens': 500,
          },
        }),
      );

      if (response.statusCode == 200) {
        final result = jsonDecode(response.body);
        final generatedText =
            result['candidates'][0]['content']['parts'][0]['text'] as String;

        final jsonMatch = RegExp(r'\{[\s\S]*\}').firstMatch(generatedText);
        if (jsonMatch != null) {
          return jsonDecode(jsonMatch.group(0)!) as Map<String, dynamic>;
        }
      }

      return _manualExtractData(rawText);
    } catch (e) {
      AppLogger.error('Error in AI extraction', e);
      return _manualExtractData(rawText);
    }
  }

  String _buildExtractionPrompt(String rawText, String sourceType) {
    return '''
You are an AI assistant for a real estate company. Extract lead information from the following text from a $sourceType.

Text:
"$rawText"

Extract and return ONLY a JSON object with these fields:
{
  "name": "person's full name",
  "phone": "phone number with country code",
  "email": "email address if present",
  "address": "address if present",
  "profession": "job title/profession if mentioned",
  "requirements": "what they are looking for (plot/house/etc)",
  "budget": "budget range mentioned",
  "timeline": "when they want to buy",
  "source": "where this lead came from",
  "urgency": "high/medium/low",
  "confidence": "0-100 score of extraction confidence"
}

If a field is not found, use null or empty string. Return ONLY the JSON object, no other text.
''';
  }

  /// Manual extraction as fallback
  Map<String, dynamic> _manualExtractData(String rawText) {
    final data = <String, dynamic>{};

    final phoneRegex = RegExp(r'(\+91[-\s]?)?[0]?(91)?[789]\d{9}');
    final phones =
        phoneRegex.allMatches(rawText).map((m) => m.group(0)).toList();
    if (phones.isNotEmpty) {
      data['phone'] = phones.first?.replaceAll(RegExp(r'[^0-9]'), '');
    }

    final emailRegex = RegExp(r'[\w.-]+@[\w.-]+\.\w+');
    final emails =
        emailRegex.allMatches(rawText).map((m) => m.group(0)).toList();
    if (emails.isNotEmpty) {
      data['email'] = emails.first;
    }

    final lines = rawText.split('\n');
    for (final line in lines) {
      if (line.trim().isNotEmpty &&
          !line.contains(RegExp(r'\d')) &&
          line.length > 3 &&
          line.length < 50) {
        data['name'] = line.trim();
        break;
      }
    }

    data['confidence'] = 50;
    return data;
  }

  // ==================== FILE UPLOAD PROCESSING ====================

  /// Process Excel/CSV file with lead data
  Future<BulkLeadProcessResult> processBulkLeadsFile({
    required File file,
    required String fileType,
    String? uploadedBy,
    String? campaignName,
  }) async {
    try {
      AppLogger.info('Processing bulk leads file: ${file.path}');

      final leads = <LeadModel>[];
      final errors = <String>[];

      List<Map<String, dynamic>> rawData;

      switch (fileType) {
        case 'csv':
          rawData = await _parseCSV(file);
          break;
        case 'excel':
          rawData = await _parseExcel(file);
          break;
        default:
          throw Exception('Unsupported file type: $fileType');
      }

      for (int i = 0; i < rawData.length; i++) {
        try {
          final row = rawData[i];

          if (row['phone'] == null || row['phone'].toString().isEmpty) {
            errors.add('Row ${i + 1}: Missing phone number');
            continue;
          }

          final genuinenessScore = await _calculateGenuinenessScore(row);

          final isDuplicate =
              await _checkDuplicateLead(row['phone'].toString());
          if (isDuplicate) {
            errors.add('Row ${i + 1}: Duplicate phone number ${row['phone']}');
            continue;
          }

          final lead = LeadModel(
            id: '',
            name: (row['name'] as String?) ?? 'Unknown',
            phone: row['phone']?.toString() ?? '',
            email: row['email'] as String?,
            source: (row['source'] as String?) ?? 'bulk_import',
            status: genuinenessScore > 70 ? 'new' : 'review_required',
            assignedTo: null,
            assignedToName: null,
            interestedIn:
                (row['requirements'] as String?) ?? (row['notes'] as String?),
            budgetMax: row['budget'] != null
                ? double.tryParse(row['budget'].toString())
                : null,
            preferredLocation: row['location'] as String?,
            followUpNotes: 'Imported from $fileType. Campaign: $campaignName',
            customFields: {
              'isGenuine': genuinenessScore > 60,
              'genuinenessScore': genuinenessScore,
              'extractedFromPhoto': false,
              'extractedData': row,
            },
            createdAt: DateTime.now(),
            updatedAt: DateTime.now(),
          );

          leads.add(lead);
        } catch (e) {
          errors.add('Row ${i + 1}: ${e.toString()}');
        }
      }

      final savedCount = await _saveBulkLeads(leads);

      return BulkLeadProcessResult(
        success: true,
        totalProcessed: rawData.length,
        validLeads: leads.length,
        savedLeads: savedCount,
        errors: errors,
        leads: leads,
      );
    } catch (e) {
      AppLogger.error('Error processing bulk leads file', e);
      return BulkLeadProcessResult(
        success: false,
        error: e.toString(),
      );
    }
  }

  Future<List<Map<String, dynamic>>> _parseCSV(File file) async {
    final content = await file.readAsString();
    final lines = content.split('\n');

    if (lines.isEmpty) return [];

    final headers = lines[0].split(',').map((h) => h.trim()).toList();

    final data = <Map<String, dynamic>>[];

    for (int i = 1; i < lines.length; i++) {
      final values = lines[i].split(',');
      if (values.length == headers.length) {
        final row = <String, dynamic>{};
        for (int j = 0; j < headers.length; j++) {
          row[headers[j]] = values[j].trim();
        }
        data.add(row);
      }
    }

    return data;
  }

  Future<List<Map<String, dynamic>>> _parseExcel(File file) async {
    // Would use excel package here
    return [];
  }

  // ==================== AI VALIDATION ====================

  Future<int> _calculateGenuinenessScore(Map<String, dynamic> data) async {
    int score = 50;

    final phone = data['phone']?.toString() ?? '';
    if (phone.isNotEmpty) {
      if (RegExp(r'^[789]\d{9}$').hasMatch(phone)) {
        score += 15;
      }
    }

    final name = data['name']?.toString() ?? '';
    if (name.isNotEmpty && name.length > 2) {
      score += 10;
      if (!RegExp(r'\d').hasMatch(name)) {
        score += 5;
      }
    }

    final email = data['email']?.toString() ?? '';
    if (email.isNotEmpty && email.contains('@') && email.contains('.')) {
      score += 10;
    }

    if (data['requirements'] != null || data['budget'] != null) {
      score += 10;
    }

    if (_isKnownFakePattern(data)) {
      score -= 30;
    }

    return score.clamp(0, 100);
  }

  bool _isKnownFakePattern(Map<String, dynamic> data) {
    final phone = data['phone']?.toString() ?? '';
    final name = data['name']?.toString().toLowerCase() ?? '';

    if (phone.contains('000000') || phone.contains('111111')) {
      return true;
    }

    if (name.contains('test') || name.contains('xyz') || name.contains('abc')) {
      return true;
    }

    return false;
  }

  ValidationResult _validateLeadData(Map<String, dynamic> data) {
    final errors = <String>[];

    if (data['phone'] == null || data['phone'].toString().isEmpty) {
      errors.add('Phone number is required');
    }

    if (data['name'] == null || data['name'].toString().length < 2) {
      errors.add('Valid name is required');
    }

    return ValidationResult(
      isValid: errors.isEmpty,
      errors: errors,
    );
  }

  // ==================== DUPLICATE CHECK ====================

  /// Check if lead with same phone already exists via REST API
  Future<bool> _checkDuplicateLead(String phone) async {
    try {
      final normalizedPhone = phone.replaceAll(RegExp(r'[^0-9]'), '');
      final result = await _api.request(
        method: 'GET',
        endpoint: 'leads',
        queryParameters: {'phone': normalizedPhone},
      );

      final data = result['data'];
      if (data is List) {
        return data.isNotEmpty;
      }
      return false;
    } catch (e) {
      AppLogger.error('Error checking duplicate lead', e);
      return false;
    }
  }

  // ==================== LEAD ASSIGNMENT ====================

  /// Auto-assign lead to best agent via REST API
  Future<String?> autoAssignLead({
    required String leadId,
    required LeadModel lead,
  }) async {
    try {
      // Get associates via REST API
      final agentsResult = await _api.request(
        method: 'GET',
        endpoint: 'mlm/genealogy',
      );

      List<Map<String, dynamic>> agentsList = [];
      final data = agentsResult['data'];
      if (data is Map<String, dynamic> && data.containsKey('downline')) {
        final downline = data['downline'];
        if (downline is List) {
          agentsList = downline.map((e) => e as Map<String, dynamic>).toList();
        }
      } else if (data is List) {
        agentsList = data.map((e) => e as Map<String, dynamic>).toList();
      }

      if (agentsList.isEmpty) return null;

      // Score each agent
      final scoredAgents = <Map<String, dynamic>>[];

      for (final agentData in agentsList) {
        final agentId = (agentData['id'] ?? agentData['user_id'])?.toString();
        if (agentId == null) continue;

        // Get agent's lead workload via REST API
        int workloadCount = 0;
        try {
          final leadsResult = await _api.request(
            method: 'GET',
            endpoint: 'leads',
            queryParameters: {'assigned_to': agentId},
          );
          final leadsData = leadsResult['data'];
          if (leadsData is List) {
            workloadCount = leadsData.length;
          }
        } catch (_) {}

        final totalLeads = (agentData['totalLeads'] as num? ?? 0).toInt();
        final convertedLeads =
            (agentData['convertedLeads'] as num? ?? 0).toInt();
        final conversionRate =
            totalLeads > 0 ? convertedLeads / totalLeads : 0.0;

        final workloadScore = (100 - workloadCount * 5).toDouble();
        final conversionScore = (conversionRate * 100).toDouble();

        final totalScore = (workloadScore * 0.6) + (conversionScore * 0.4);

        scoredAgents.add({
          'id': agentId,
          'name': agentData['name'] as String? ?? 'Agent',
          'score': totalScore,
        });
      }

      if (scoredAgents.isEmpty) return null;

      scoredAgents.sort(
          (a, b) => (b['score'] as double).compareTo(a['score'] as double));

      final bestAgent = scoredAgents.first;

      // Update lead assignment via REST API
      await _api.request(
        method: 'POST',
        endpoint: 'leads',
        data: {
          'id': leadId,
          'assigned_to': bestAgent['id'],
          'assigned_to_name': bestAgent['name'],
          'auto_assigned': true,
        },
      );

      // Notify agent via REST API
      await _notifyAgentOfNewLead(
        agentId: bestAgent['id'] as String,
        leadId: leadId,
        leadName: lead.name,
      );

      return bestAgent['id'] as String;
    } catch (e) {
      AppLogger.error('Error auto-assigning lead', e);
      return null;
    }
  }

  Future<void> _notifyAgentOfNewLead({
    required String agentId,
    required String leadId,
    required String leadName,
  }) async {
    try {
      await _api.request(
        method: 'POST',
        endpoint: 'notification',
        data: {
          'user_id': agentId,
          'title': 'New Lead Assigned',
          'body': 'Lead "$leadName" has been assigned to you',
          'type': 'new_lead',
          'lead_id': leadId,
          'read': false,
        },
      );
    } catch (e) {
      AppLogger.error('Error notifying agent', e);
    }
  }

  // ==================== DATABASE OPERATIONS ====================

  /// Save lead via REST API (replaces Firestore leads.add)
  Future<String?> _saveLead(LeadModel lead, File? photoFile) async {
    try {
      final result = await _api.request(
        method: 'POST',
        endpoint: 'leads',
        data: {
          ...lead.toJson(),
          if (photoFile != null) 'photo_path': photoFile.path,
        },
      );

      final leadId = result['data']?['id']?.toString() ?? result['id']?.toString();
      AppLogger.info('Lead saved: $leadId');
      return leadId;
    } catch (e) {
      AppLogger.error('Error saving lead', e);
      return null;
    }
  }

  /// Save bulk leads via REST API (replaces Firestore batch)
  Future<int> _saveBulkLeads(List<LeadModel> leads) async {
    int saved = 0;

    for (final lead in leads) {
      try {
        await _api.request(
          method: 'POST',
          endpoint: 'leads',
          data: lead.toJson(),
        );
        saved++;
      } catch (e) {
        AppLogger.error('Error saving bulk lead', e);
      }
    }

    return saved;
  }

  String _mapSourceType(String sourceType) {
    final mapping = {
      'visiting_card': 'visiting_card',
      'handwritten': 'handwritten_note',
      'document': 'document',
      'contact_list': 'contact_import',
    };

    return mapping[sourceType] ?? 'other';
  }
}

// Result Classes
class ProcessedLeadResult {
  final bool success;
  final LeadModel? lead;
  final String? leadId;
  final Map<String, dynamic>? extractedData;
  final int? genuinenessScore;
  final List<String>? validationErrors;
  final bool? isReadyForAssignment;
  final String? error;

  ProcessedLeadResult({
    required this.success,
    this.lead,
    this.leadId,
    this.extractedData,
    this.genuinenessScore,
    this.validationErrors,
    this.isReadyForAssignment,
    this.error,
  });
}

class BulkLeadProcessResult {
  final bool success;
  final int? totalProcessed;
  final int? validLeads;
  final int? savedLeads;
  final List<String>? errors;
  final List<LeadModel>? leads;
  final String? error;

  BulkLeadProcessResult({
    required this.success,
    this.totalProcessed,
    this.validLeads,
    this.savedLeads,
    this.errors,
    this.leads,
    this.error,
  });
}

class ValidationResult {
  final bool isValid;
  final List<String> errors;

  ValidationResult({
    required this.isValid,
    required this.errors,
  });
}

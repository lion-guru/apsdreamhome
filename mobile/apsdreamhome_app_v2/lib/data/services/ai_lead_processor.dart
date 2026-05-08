import 'dart:convert';
import 'dart:io';

import 'package:cloud_firestore/cloud_firestore.dart';
// import 'package:google_mlkit_text_recognition/google_mlkit_text_recognition.dart';
import 'package:http/http.dart' as http;

import '../../core/utils/logger.dart';
import '../../data/models/lead_model.dart';

/// AI Lead Processor Service
/// Extracts lead data from photos, documents, contacts
/// Validates and creates leads automatically
class AILeadProcessor {
  final FirebaseFirestore _firestore = FirebaseFirestore.instance;
  // final TextRecognizer _textRecognizer = TextRecognizer();

  // AI/ML API Endpoints
  static const String _geminiApiKey = 'YOUR_GEMINI_API_KEY';
  static const String _geminiEndpoint =
      'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';

  // ==================== PHOTO TO LEAD ====================

  /// Process Photo and Extract Lead Information
  /// Handles: Visiting cards, handwritten notes, contact lists, documents
  Future<ProcessedLeadResult> processPhoto({
    required File imageFile,
    required String
        sourceType, // 'visiting_card', 'handwritten', 'document', 'contact_list'
    String? uploadedBy, // User ID who uploaded
    String? notes,
  }) async {
    try {
      AppLogger.info('Processing photo for lead extraction: ${imageFile.path}');

      // Step 1: Extract text from image using ML Kit
      final extractedText = await _extractTextFromImage(imageFile);
      AppLogger.info('Extracted text: $extractedText');

      // Step 2: AI processing to extract structured data
      final structuredData = await _aiExtractLeadData(
        rawText: extractedText,
        sourceType: sourceType,
      );

      // Step 3: Validate the extracted data
      final validation = _validateLeadData(structuredData);

      // Step 4: Check if it's a genuine lead
      final genuinenessScore = await _calculateGenuinenessScore(structuredData);

      // Step 5: Create lead object
      final lead = LeadModel(
        id: '', // Will be set by Firestore
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

      // Step 6: Save to Firestore if genuine enough
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
      // final inputImage = InputImage.fromFile(imageFile);
      // final recognizedText = await _textRecognizer.processImage(inputImage);
      // return recognizedText.text;

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
      // Prepare prompt for AI
      final prompt = _buildExtractionPrompt(rawText, sourceType);

      // Call Gemini API
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

        // Parse JSON from AI response
        final jsonMatch = RegExp(r'\{[\s\S]*\}').firstMatch(generatedText);
        if (jsonMatch != null) {
          return jsonDecode(jsonMatch.group(0)!) as Map<String, dynamic>;
        }
      }

      // Fallback: Extract manually if AI fails
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

    // Extract phone numbers
    final phoneRegex = RegExp(r'(\+91[-\s]?)?[0]?(91)?[789]\d{9}');
    final phones =
        phoneRegex.allMatches(rawText).map((m) => m.group(0)).toList();
    if (phones.isNotEmpty) {
      data['phone'] = phones.first?.replaceAll(RegExp(r'[^0-9]'), '');
    }

    // Extract email
    final emailRegex = RegExp(r'[\w.-]+@[\w.-]+\.\w+');
    final emails =
        emailRegex.allMatches(rawText).map((m) => m.group(0)).toList();
    if (emails.isNotEmpty) {
      data['email'] = emails.first;
    }

    // Extract name (first capitalized words)
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
    required String fileType, // 'excel', 'csv', 'pdf'
    String? uploadedBy,
    String? campaignName,
  }) async {
    try {
      AppLogger.info('Processing bulk leads file: ${file.path}');

      final leads = <LeadModel>[];
      final errors = <String>[];

      // Parse file based on type
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

      // Process each row
      for (int i = 0; i < rawData.length; i++) {
        try {
          final row = rawData[i];

          // Validate minimum data
          if (row['phone'] == null || row['phone'].toString().isEmpty) {
            errors.add('Row ${i + 1}: Missing phone number');
            continue;
          }

          // AI validation
          final genuinenessScore = await _calculateGenuinenessScore(row);

          // Check for duplicates
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

      // Save leads to Firestore
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

    // Parse header
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
    // For now return empty
    return [];
  }

  // ==================== AI VALIDATION ====================

  /// Calculate Genuineness Score using AI
  Future<int> _calculateGenuinenessScore(Map<String, dynamic> data) async {
    int score = 50; // Base score

    // Phone number validation
    final phone = data['phone']?.toString() ?? '';
    if (phone.isNotEmpty) {
      if (RegExp(r'^[789]\d{9}$').hasMatch(phone)) {
        score += 15;
      }
    }

    // Name validation
    final name = data['name']?.toString() ?? '';
    if (name.isNotEmpty && name.length > 2) {
      score += 10;
      if (!RegExp(r'\d').hasMatch(name)) {
        // No numbers in name
        score += 5;
      }
    }

    // Email validation
    final email = data['email']?.toString() ?? '';
    if (email.isNotEmpty && email.contains('@') && email.contains('.')) {
      score += 10;
    }

    // Requirements/budget mentioned
    if (data['requirements'] != null || data['budget'] != null) {
      score += 10;
    }

    // Check against known fake patterns
    if (_isKnownFakePattern(data)) {
      score -= 30;
    }

    return score.clamp(0, 100);
  }

  bool _isKnownFakePattern(Map<String, dynamic> data) {
    final phone = data['phone']?.toString() ?? '';
    final name = data['name']?.toString().toLowerCase() ?? '';

    // Test numbers
    if (phone.contains('000000') || phone.contains('111111')) {
      return true;
    }

    // Fake names
    if (name.contains('test') || name.contains('xyz') || name.contains('abc')) {
      return true;
    }

    return false;
  }

  /// Validate extracted lead data
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

  /// Check if lead with same phone already exists
  Future<bool> _checkDuplicateLead(String phone) async {
    final normalizedPhone = phone.replaceAll(RegExp(r'[^0-9]'), '');

    final existing = await _firestore
        .collection('leads')
        .where('phone', isEqualTo: normalizedPhone)
        .limit(1)
        .get();

    return existing.docs.isNotEmpty;
  }

  // ==================== LEAD ASSIGNMENT ====================

  /// Auto-assign lead to best agent
  Future<String?> autoAssignLead({
    required String leadId,
    required LeadModel lead,
  }) async {
    try {
      // Find best agent based on:
      // 1. Current workload (least active leads)
      // 2. Specialization (location, property type)
      // 3. Performance (conversion rate)
      // 4. Availability

      final agents = await _firestore
          .collection('users')
          .where('role', isEqualTo: 'associate')
          .where('isActive', isEqualTo: true)
          .get();

      if (agents.docs.isEmpty) return null;

      // Score each agent
      final scoredAgents = <Map<String, dynamic>>[];

      for (final agent in agents.docs) {
        final agentData = agent.data();

        // Get agent's current workload
        final workloadQuery = await _firestore
            .collection('leads')
            .where('assignedTo', isEqualTo: agent.id)
            .where('status', whereIn: ['new', 'contacted', 'follow_up'])
            .count()
            .get();
        final workloadCount = workloadQuery.count ?? 0;

        // Get agent's conversion rate
        final totalLeads = (agentData['totalLeads'] as num? ?? 0).toInt();
        final convertedLeads =
            (agentData['convertedLeads'] as num? ?? 0).toInt();
        final conversionRate =
            totalLeads > 0 ? convertedLeads / totalLeads : 0.0;

        // Calculate score (lower workload + higher conversion = better)
        final workloadScore = (100 - workloadCount * 5).toDouble();
        final conversionScore = (conversionRate * 100).toDouble();

        final totalScore = (workloadScore * 0.6) + (conversionScore * 0.4);

        scoredAgents.add({
          'id': agent.id,
          'name': agentData['name'] as String,
          'score': totalScore,
        });
      }

      // Sort by score
      scoredAgents.sort(
          (a, b) => (b['score'] as double).compareTo(a['score'] as double));

      // Assign to best agent
      final bestAgent = scoredAgents.first;

      await _firestore.collection('leads').doc(leadId).update({
        'assignedTo': bestAgent['id'],
        'assignedToName': bestAgent['name'],
        'autoAssigned': true,
        'assignedAt': FieldValue.serverTimestamp(),
      });

      // Notify agent
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
    // Send push notification
    await _firestore.collection('notifications').add({
      'userId': agentId,
      'title': 'New Lead Assigned',
      'body': 'Lead "$leadName" has been assigned to you',
      'type': 'new_lead',
      'leadId': leadId,
      'createdAt': FieldValue.serverTimestamp(),
      'read': false,
    });
  }

  // ==================== DATABASE OPERATIONS ====================

  Future<String?> _saveLead(LeadModel lead, File? photoFile) async {
    try {
      // Upload photo to storage if exists
      String? photoUrl;
      if (photoFile != null) {
        // Upload to Firebase Storage
        // photoUrl = await _uploadPhoto(photoFile);
      }

      final docRef = await _firestore.collection('leads').add({
        ...lead.toJson(),
        'photoUrl': photoUrl,
      });

      AppLogger.info('Lead saved: ${docRef.id}');
      return docRef.id;
    } catch (e) {
      AppLogger.error('Error saving lead', e);
      return null;
    }
  }

  Future<int> _saveBulkLeads(List<LeadModel> leads) async {
    int saved = 0;

    final batch = _firestore.batch();

    for (final lead in leads) {
      final docRef = _firestore.collection('leads').doc();
      batch.set(docRef, lead.toJson());
      saved++;

      // Batch limit is 500
      if (saved % 400 == 0) {
        await batch.commit();
      }
    }

    if (saved % 400 != 0) {
      await batch.commit();
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

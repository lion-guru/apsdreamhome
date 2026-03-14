import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:speech_to_text/speech_to_text.dart';
import '../../core/constants/app_constants.dart';
import '../../core/theme/app_theme.dart';
import '../../data/models/lead_model.dart';
import '../providers/lead_provider.dart';
import '../widgets/glass_card.dart';
import '../widgets/common_widgets.dart';

class VoiceToLeadPage extends ConsumerStatefulWidget {
  const VoiceToLeadPage({super.key});
  
  @override
  ConsumerState<VoiceToLeadPage> createState() => _VoiceToLeadPageState();
}

class _VoiceToLeadPageState extends ConsumerState<VoiceToLeadPage> {
  final SpeechToText _speechToText = SpeechToText();
  bool _isListening = false;
  bool _isAvailable = false;
  String _recognizedText = '';
  final TextEditingController _nameController = TextEditingController();
  final TextEditingController _phoneController = TextEditingController();
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _propertyController = TextEditingController();
  final TextEditingController _budgetController = TextEditingController();
  final TextEditingController _notesController = TextEditingController();
  
  @override
  void initState() {
    super.initState();
    _initSpeech();
  }
  
  @override
  void dispose() {
    _speechToText.stop();
    _nameController.dispose();
    _phoneController.dispose();
    _emailController.dispose();
    _propertyController.dispose();
    _budgetController.dispose();
    _notesController.dispose();
    super.dispose();
  }
  
  void _initSpeech() async {
    _isAvailable = await _speechToText.initialize(
      onError: (error) => print('Speech error: $error'),
      onStatus: (status) => print('Speech status: $status'),
    );
    setState(() {});
  }
  
  void _startListening() async {
    if (!_isAvailable) return;
    
    setState(() {
      _isListening = true;
      _recognizedText = '';
    });
    
    await _speechToText.listen(
      onResult: (result) {
        setState(() {
          _recognizedText = result.recognizedWords;
          _processVoiceInput(result.recognizedWords);
        });
      },
      listenFor: const Duration(seconds: 30),
      pauseFor: const Duration(seconds: 3),
      partialResults: true,
      localeId: 'en_IN',
      listenOptions: SpeechListenOptions(
        cancelOnError: true,
        partialResults: true,
        autoPunctuation: true,
      ),
    );
  }
  
  void _stopListening() async {
    await _speechToText.stop();
    setState(() {
      _isListening = false;
    });
  }
  
  void _processVoiceInput(String text) {
    // Simple voice processing - in production, use NLP/AI
    final lowerText = text.toLowerCase();
    
    // Extract name (simple pattern matching)
    final nameMatch = RegExp(r'(\w+)\s+(?:is|interested)').firstMatch(lowerText);
    if (nameMatch != null) {
      _nameController.text = nameMatch.group(1) ?? '';
    }
    
    // Extract phone number
    final phoneMatch = RegExp(r'(\d{10})').firstMatch(text);
    if (phoneMatch != null) {
      _phoneController.text = phoneMatch.group(1) ?? '';
    }
    
    // Extract property type
    if (lowerText.contains('3 bhk')) {
      _propertyController.text = '3 BHK Apartment';
    } else if (lowerText.contains('2 bhk')) {
      _propertyController.text = '2 BHK Apartment';
    } else if (lowerText.contains('plot')) {
      _propertyController.text = 'Residential Plot';
    }
    
    // Extract budget
    final budgetMatch = RegExp(r'(\d+)\s*(?:lakh|lakhs|l|crore|cr)').firstMatch(lowerText);
    if (budgetMatch != null) {
      final amount = int.tryParse(budgetMatch.group(1) ?? '');
      if (amount != null) {
        if (lowerText.contains('lakh') || lowerText.contains('l')) {
          _budgetController.text = (amount * 100000).toString();
        } else if (lowerText.contains('crore') || lowerText.contains('cr')) {
          _budgetController.text = (amount * 10000000).toString();
        }
      }
    }
    
    // Set notes to full recognized text
    _notesController.text = text;
  }
  
  void _createLead() {
    if (_nameController.text.trim().isEmpty || _phoneController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please fill in name and phone number')),
      );
      return;
    }
    
    final lead = Lead(
      leadId: DateTime.now().millisecondsSinceEpoch.toString(),
      name: _nameController.text.trim(),
      phone: _phoneController.text.trim(),
      email: _emailController.text.trim().isNotEmpty ? _emailController.text.trim() : null,
      propertyInterest: _propertyController.text.trim().isNotEmpty ? _propertyController.text.trim() : null,
      budget: _budgetController.text.trim().isNotEmpty ? double.tryParse(_budgetController.text.trim()) : null,
      status: 'New',
      notes: _notesController.text.trim().isNotEmpty ? 'Voice input: ${_notesController.text.trim()}' : null,
      createdAt: DateTime.now().toIso8601String(),
      updatedAt: DateTime.now().toIso8601String(),
    );
    
    ref.read(leadProvider.notifier).addLead(lead);
    
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Lead created successfully!')),
    );
    
    _clearForm();
  }
  
  void _clearForm() {
    _nameController.clear();
    _phoneController.clear();
    _emailController.clear();
    _propertyController.clear();
    _budgetController.clear();
    _notesController.clear();
    _recognizedText = '';
  }
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Voice to Lead'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(AppConstants.defaultPadding),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Voice Input Section
            GlassCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Icon(
                        Icons.mic,
                        size: 32,
                        color: _isListening ? Colors.red : AppTheme.primaryColor,
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Voice Input',
                              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            Text(
                              _isListening ? 'Listening...' : 'Tap to start recording',
                              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                                color: _isListening ? Colors.red : Colors.grey.shade600,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  
                  const SizedBox(height: 16),
                  
                  // Voice Recording Button
                  Center(
                    child: GestureDetector(
                      onTap: _isListening ? _stopListening : _startListening,
                      child: Container(
                        width: 80,
                        height: 80,
                        decoration: BoxDecoration(
                          color: _isListening ? Colors.red : AppTheme.primaryColor,
                          borderRadius: BorderRadius.circular(40),
                          boxShadow: [
                            BoxShadow(
                              color: (_isListening ? Colors.red : AppTheme.primaryColor).withOpacity(0.3),
                              blurRadius: 20,
                              spreadRadius: 5,
                            ),
                          ],
                        ),
                        child: Icon(
                          _isListening ? Icons.stop : Icons.mic,
                          size: 40,
                          color: Colors.white,
                        ),
                      ),
                    ),
                  ),
                  
                  const SizedBox(height: 16),
                  
                  // Recognized Text
                  if (_recognizedText.isNotEmpty) ...[
                    Text(
                      'Recognized:',
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.grey.shade100,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        _recognizedText,
                        style: Theme.of(context).textTheme.bodyMedium,
                      ),
                    ),
                  ],
                ],
              ),
            ),
            
            const SizedBox(height: 24),
            
            // Lead Form
            GlassCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Lead Information',
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  
                  const SizedBox(height: 16),
                  
                  // Name Field
                  TextField(
                    controller: _nameController,
                    decoration: const InputDecoration(
                      labelText: 'Name *',
                      hintText: 'Lead name',
                      prefixIcon: Icon(Icons.person),
                    ),
                  ),
                  
                  const SizedBox(height: 16),
                  
                  // Phone Field
                  TextField(
                    controller: _phoneController,
                    keyboardType: TextInputType.phone,
                    decoration: const InputDecoration(
                      labelText: 'Phone *',
                      hintText: 'Phone number',
                      prefixIcon: Icon(Icons.phone),
                    ),
                  ),
                  
                  const SizedBox(height: 16),
                  
                  // Email Field
                  TextField(
                    controller: _emailController,
                    keyboardType: TextInputType.emailAddress,
                    decoration: const InputDecoration(
                      labelText: 'Email',
                      hintText: 'Email address',
                      prefixIcon: Icon(Icons.email),
                    ),
                  ),
                  
                  const SizedBox(height: 16),
                  
                  // Property Interest Field
                  TextField(
                    controller: _propertyController,
                    decoration: const InputDecoration(
                      labelText: 'Property Interest',
                      hintText: 'e.g., 3 BHK Apartment',
                      prefixIcon: Icon(Icons.apartment),
                    ),
                  ),
                  
                  const SizedBox(height: 16),
                  
                  // Budget Field
                  TextField(
                    controller: _budgetController,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(
                      labelText: 'Budget',
                      hintText: 'Budget amount',
                      prefixIcon: Icon(Icons.currency_rupee),
                    ),
                  ),
                  
                  const SizedBox(height: 16),
                  
                  // Notes Field
                  TextField(
                    controller: _notesController,
                    maxLines: 3,
                    decoration: const InputDecoration(
                      labelText: 'Notes',
                      hintText: 'Additional notes',
                      prefixIcon: Icon(Icons.note),
                    ),
                  ),
                  
                  const SizedBox(height: 24),
                  
                  // Action Buttons
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton(
                          onPressed: _clearForm,
                          child: const Text('Clear'),
                        ),
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: ElevatedButton(
                          onPressed: _createLead,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppTheme.primaryColor,
                            foregroundColor: Colors.white,
                          ),
                          child: const Text('Create Lead'),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            
            const SizedBox(height: 24),
            
            // Voice Commands Help
            GlassCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Voice Commands',
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 12),
                  
                  _buildVoiceCommandExample('Rahul is interested in 3 BHK', 'Name: Rahul, Property: 3 BHK'),
                  _buildVoiceCommandExample('Call 9876543210 for 50 lakh budget', 'Phone: 9876543210, Budget: 50L'),
                  _buildVoiceCommandExample('New lead wants plot in Noida', 'Property: Plot, Location: Noida'),
                ],
              ),
            ),
            
            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }
  
  Widget _buildVoiceCommandExample(String command, String result) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Say: "$command"',
            style: TextStyle(
              fontWeight: FontWeight.w600,
              color: AppTheme.primaryColor,
            ),
          ),
          Text(
            'Result: $result',
            style: Theme.of(context).textTheme.bodySmall?.copyWith(
              color: Colors.grey.shade600,
            ),
          ),
        ],
      ),
    );
  }
}

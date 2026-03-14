import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:speech_to_text/speech_to_text.dart' as stt;
import '../../core/constants/app_constants.dart';
import '../../core/services/api_service.dart';

class VoiceLeadDialog extends StatefulWidget {
  const VoiceLeadDialog({Key? key}) : super(key: key);

  @override
  _VoiceLeadDialogState createState() => _VoiceLeadDialogState();
}

class _VoiceLeadDialogState extends State<VoiceLeadDialog> {
  late stt.SpeechToText _speech;
  bool _isListening = false;
  String _text = "Tap the mic and say: 'Lead name Rahul, number 9876543210, location Gomti Nagar'";
  double _confidence = 1.0;
  bool _isProcessing = false;

  @override
  void initState() {
    super.initState();
    _speech = stt.SpeechToText();
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      child: Padding(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text(
              'Voice-to-Lead AI',
              style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 20),
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.grey[100],
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: _isListening ? Colors.blue : Colors.transparent),
              ),
              child: Text(
                _text,
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 16,
                  color: _text.startsWith('Tap') ? Colors.grey : Colors.black87,
                  fontStyle: _text.startsWith('Tap') ? FontStyle.italic : FontStyle.normal,
                ),
              ),
            ),
            const SizedBox(height: 30),
            if (_isProcessing)
              const CircularProgressIndicator()
            else
              GestureDetector(
                onTap: _listen,
                child: Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: _isListening ? Colors.red : AppConstants.primaryColor,
                    shape: BoxShape.circle,
                    boxShadow: [
                      BoxShadow(
                        color: (_isListening ? Colors.red : AppConstants.primaryColor).withOpacity(0.4),
                        blurRadius: 15,
                        spreadRadius: 5,
                      )
                    ],
                  ),
                  child: Icon(
                    _isListening ? Icons.mic : Icons.mic_none,
                    size: 40,
                    color: Colors.white,
                  ),
                ),
              ),
            const SizedBox(height: 20),
            Text(
              _isListening ? 'Listening...' : 'Tap Mic to Start',
              style: TextStyle(
                color: _isListening ? Colors.red : Colors.grey,
                fontWeight: FontWeight.bold,
              ),
            ),
            if (!_isListening && _text != "Tap the mic and say: 'Lead name Rahul, number 9876543210, location Gomti Nagar'")
              Padding(
                padding: const EdgeInsets.top(20.0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                  children: [
                    TextButton(
                      onPressed: () => Navigator.pop(context),
                      child: const Text('Cancel'),
                    ),
                    ElevatedButton(
                      onPressed: _processText,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppConstants.accentColor,
                        foregroundColor: Colors.black,
                      ),
                      child: const Text('Parse Lead'),
                    ),
                  ],
                ),
              ),
          ],
        ),
      ),
    );
  }

  void _listen() async {
    if (!_isListening) {
      bool available = await _speech.initialize(
        onStatus: (val) => print('onStatus: $val'),
        onError: (val) => print('onError: $val'),
      );
      if (available) {
        setState(() => _isListening = true);
        _speech.listen(
          onResult: (val) => setState(() {
            _text = val.recognizedWords;
            if (val.hasConfidenceRating && val.confidence > 0) {
              _confidence = val.confidence;
            }
          }),
        );
      }
    } else {
      setState(() => _isListening = false);
      _speech.stop();
    }
  }

  void _processText() async {
    setState(() => _isProcessing = true);
    
    // Use Provider to send text to backend
    // For now using context.read if it's available or just Navigator.pop with data
    try {
      // In a real implementation, we call apiService.parseLead(_text)
      // and then navigate to the Lead Entry form with pre-filled fields.
      
      // Simulate API call delay
      await Future.delayed(const Duration(seconds: 1));
      
      Navigator.pop(context, _text);
    } catch (e) {
      setState(() => _isProcessing = false);
    }
  }
}

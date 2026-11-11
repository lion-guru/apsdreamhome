import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:dio/dio.dart';

import '../../../core/services/api_service.dart';

class VoiceCallPage extends ConsumerStatefulWidget {
  const VoiceCallPage({super.key});

  @override
  ConsumerState<VoiceCallPage> createState() => _VoiceCallPageState();
}

class _VoiceCallPageState extends ConsumerState<VoiceCallPage> {
  static const Color primaryColor = Color(0xFF0B194C);
  static const Color accentColor = Color(0xFFF58220);

  bool _isInCall = false;
  bool _isMuted = false;
  bool _isSpeakerOn = false;

  final TextEditingController _phoneController = TextEditingController();
  String _selectedProvider = 'exotel';
  String _callStatus = 'Idle';

  @override
  void dispose() {
    _phoneController.dispose();
    super.dispose();
  }

  Future<void> _startCall() async {
    if (_phoneController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please enter phone number')),
      );
      return;
    }

    setState(() {
      _isInCall = true;
      _callStatus = 'Connecting...';
    });

    try {
      final api = ApiService();
      final formData = FormData.fromMap({
        'phone': _phoneController.text.trim(),
        'agent_type': 'followup',
        'script_code': 'default',
      });
      final response = await api.webDio.post(
        '/voice-agent/start-call',
        data: formData,
      );

      if (mounted && response.data['success'] == true) {
        setState(() => _callStatus = 'Connected');
      } else {
        setState(() {
          _isInCall = false;
          _callStatus = 'Failed';
        });
        final err = response.data['error'] as String? ?? 'Call failed';
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(err ?? 'Call failed'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      setState(() {
        _isInCall = false;
        _callStatus = 'Error';
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
        );
      }
    }
  }

  void _endCall() {
    setState(() {
      _isInCall = false;
      _callStatus = 'Ended';
      _isMuted = false;
    });
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Call ended')),
      );
    }
  }

  void _toggleMute() {
    setState(() => _isMuted = !_isMuted);
  }

  void _toggleSpeaker() {
    setState(() => _isSpeakerOn = !_isSpeakerOn);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F0),
      appBar: AppBar(
        title: const Text('AI Voice Call'),
        foregroundColor: primaryColor,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => context.pop(),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            if (_isInCall) ...[
              Container(
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(24),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.1),
                      blurRadius: 10,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Column(
                  children: [
                    const CircleAvatar(
                      radius: 40,
                      backgroundColor: Colors.green,
                      child:
                          Icon(Icons.person, size: 40, color: Colors.white),
                    ),
                    const SizedBox(height: 16),
                    Text(
                      _phoneController.text,
                      style: const TextStyle(
                          fontSize: 20, fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      _callStatus,
                      style: const TextStyle(
                          fontSize: 14, color: Color(0xFF0B194C)),
                    ),
                    const SizedBox(height: 24),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        _buildCallButton(
                          icon: _isMuted ? Icons.mic_off : Icons.mic,
                          label: _isMuted ? 'Unmute' : 'Mute',
                          onTap: _toggleMute,
                          isActive: _isMuted,
                        ),
                        const SizedBox(width: 24),
                        _buildEndCallButton(),
                        const SizedBox(width: 24),
                        _buildCallButton(
                          icon: _isSpeakerOn
                              ? Icons.volume_down
                              : Icons.volume_up,
                          label: 'Speaker',
                          onTap: _toggleSpeaker,
                          isActive: _isSpeakerOn,
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ] else ...[
              const Icon(
                Icons.phone_in_talk,
                size: 80,
                color: Color(0xFF0B194C),
              ),
              const SizedBox(height: 16),
              const Text(
                'AI Voice Call',
                style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF0B194C)),
              ),
              const SizedBox(height: 8),
              const Text(
                'AI-powered voice calls with real-time transcription',
                style: TextStyle(fontSize: 14, color: Colors.grey),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 24),
              Card(
                elevation: 2,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16)),
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Phone Number',
                        style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w600,
                            color: Color(0xFF0B194C)),
                      ),
                      const SizedBox(height: 8),
                      TextFormField(
                        controller: _phoneController,
                        keyboardType: TextInputType.phone,
                        decoration: InputDecoration(
                          hintText: '+91 98765 43210',
                          filled: true,
                          fillColor: Colors.grey.shade50,
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide:
                                BorderSide(color: Colors.grey.shade300),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide:
                                const BorderSide(color: accentColor),
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),
                      const Text(
                        'Provider',
                        style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w600,
                            color: Color(0xFF0B194C)),
                      ),
                      const SizedBox(height: 8),
                      Wrap(
                        spacing: 8,
                        children: ['exotel', 'twilio', 'plivo', 'knowlarity']
                            .map((p) => FilterChip(
                                  label: Text(p.toUpperCase()),
                                  selected: _selectedProvider == p,
                                  onSelected: (v) =>
                                      setState(() => _selectedProvider = p),
                                  selectedColor: primaryColor,
                                  checkmarkColor: Colors.white,
                                ))
                            .toList(),
                      ),
                      const SizedBox(height: 24),
                      SizedBox(
                        width: double.infinity,
                        height: 52,
                        child: ElevatedButton.icon(
                          onPressed: _startCall,
                          icon: const Icon(Icons.call),
                          label: const Text('Start Call',
                              style: TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold)),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.green,
                            foregroundColor: Colors.white,
                            shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(12)),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),
              const Padding(
                padding: EdgeInsets.symmetric(horizontal: 24),
                child: Text(
                  'Note: Voice calls require provider credentials (Exotel/Twilio). Configure in admin panel at /admin/service-configs',
                  style: TextStyle(
                      fontSize: 11,
                      color: Colors.grey,
                      fontStyle: FontStyle.italic),
                  textAlign: TextAlign.center,
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildCallButton({
    required IconData icon,
    required String label,
    required VoidCallback onTap,
    required bool isActive,
  }) {
    return Column(
      children: [
        CircleAvatar(
          radius: 28,
          backgroundColor:
              isActive ? Colors.red.shade100 : Colors.grey.shade100,
          child: IconButton(
            icon: Icon(icon, color: isActive ? Colors.red : Colors.black),
            onPressed: onTap,
          ),
        ),
        const SizedBox(height: 4),
        Text(label, style: const TextStyle(fontSize: 11)),
      ],
    );
  }

  Widget _buildEndCallButton() {
    return Column(
      children: [
        CircleAvatar(
          radius: 32,
          backgroundColor: Colors.red,
          child: IconButton(
            icon: const Icon(Icons.call_end, color: Colors.white, size: 28),
            onPressed: _endCall,
          ),
        ),
        const SizedBox(height: 4),
        const Text('End', style: TextStyle(fontSize: 11)),
      ],
    );
  }
}

import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class UserNotificationSettingsPage extends ConsumerStatefulWidget {
  const UserNotificationSettingsPage({super.key});

  @override
  ConsumerState<UserNotificationSettingsPage> createState() => _UserNotificationSettingsPageState();
}

class _UserNotificationSettingsPageState extends ConsumerState<UserNotificationSettingsPage> {
  Map<String, dynamic> _settings = {
    'email_marketing': true,
    'email_transactions': true,
    'email_reminders': true,
    'email_security': true,
    'sms_marketing': false,
    'sms_transactions': true,
    'sms_reminders': true,
    'sms_security': true,
    'push_marketing': true,
    'push_transactions': true,
    'push_reminders': true,
    'push_security': true,
    'whatsapp_marketing': false,
    'whatsapp_transactions': true,
    'whatsapp_reminders': true,
    'whatsapp_security': true,
  };
  bool _isLoading = false;
  Dio get _dio => Dio(BaseOptions(baseUrl: AppConstants.baseUrl));

  @override
  void initState() {
    super.initState();
    _fetchSettings();
  }

  Future<void> _fetchSettings() async {
    setState(() => _isLoading = true);
    try {
      final token = await ref.read(authProvider.notifier).getToken();
      final response = await _dio.get(
        '/api/v2/mobile/user/notification-preferences',
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );
      final resData = response.data as Map<String, dynamic>;
      if (resData['success'] == true && resData['data'] != null) {
        setState(() {
          _settings = {..._settings, ...resData['data'] as Map<String, dynamic>};
          _isLoading = false;
        });
      } else {
        setState(() => _isLoading = false);
      }
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _saveSettings() async {
    setState(() => _isLoading = true);
    try {
      final token = await ref.read(authProvider.notifier).getToken();
      final response = await _dio.post(
        '/api/v2/mobile/user/notification-preferences',
        data: _settings,
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );
      final resData = response.data as Map<String, dynamic>;
      if (mounted) {
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text((resData['message'] ?? 'Notification preferences saved!').toString()),
            backgroundColor: AppTheme.successColor,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to save: $e'), backgroundColor: Colors.red),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: MeshGradientBackground(
        child: SafeArea(
          child: _isLoading
              ? const Center(child: CircularProgressIndicator(color: Colors.white))
              : SingleChildScrollView(
                  padding: const EdgeInsets.fromLTRB(20, 20, 20, 40),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _buildHeader(),
                      const SizedBox(height: 24),
                      _buildChannelSection(
                        'Email',
                        Icons.email_rounded,
                        const Color(0xFF4285F4),
                        ['email_marketing', 'email_transactions', 'email_reminders', 'email_security'],
                        ['Marketing', 'Transactions', 'Reminders', 'Security Alerts'],
                      ),
                      const SizedBox(height: 16),
                      _buildChannelSection(
                        'SMS',
                        Icons.sms_rounded,
                        const Color(0xFF34A853),
                        ['sms_marketing', 'sms_transactions', 'sms_reminders', 'sms_security'],
                        ['Marketing', 'Transactions', 'Reminders', 'Security Alerts'],
                      ),
                      const SizedBox(height: 16),
                      _buildChannelSection(
                        'Push',
                        Icons.notifications_active_rounded,
                        const Color(0xFFFF9800),
                        ['push_marketing', 'push_transactions', 'push_reminders', 'push_security'],
                        ['Marketing', 'Transactions', 'Reminders', 'Security Alerts'],
                      ),
                      const SizedBox(height: 16),
                      _buildChannelSection(
                        'WhatsApp',
                        Icons.chat_rounded,
                        const Color(0xFF25D366),
                        ['whatsapp_marketing', 'whatsapp_transactions', 'whatsapp_reminders', 'whatsapp_security'],
                        ['Marketing', 'Transactions', 'Reminders', 'Security Alerts'],
                      ),
                      const SizedBox(height: 24),
                      _buildSaveButton(),
                    ],
                  ),
                ),
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return Row(
      children: [
        GestureDetector(
          onTap: () => Navigator.pop(context),
          child: Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: const Icon(Icons.arrow_back, color: Colors.white, size: 22),
          ),
        ),
        const SizedBox(width: 16),
        Expanded(
          child: ShaderMask(
            shaderCallback: (bounds) => const LinearGradient(
              colors: [Colors.white, Color(0xFF5EEAD4)],
            ).createShader(bounds),
            child: Text(
              'Notification Settings',
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                color: Colors.white,
                fontWeight: FontWeight.w700,
              ),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildChannelSection(
    String channelName,
    IconData icon,
    Color color,
    List<String> keys,
    List<String> labels,
  ) {
    return GlassCard(
      padding: const EdgeInsets.all(20),
      opacity: 0.1,
      blur: 8,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 40,
                height: 40,
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(icon, color: color, size: 20),
              ),
              const SizedBox(width: 12),
              Text(
                '$channelName Notifications',
                style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 16),
              ),
              const Spacer(),
              Switch(
                value: keys.every((k) => (_settings[k] as bool?) == true),
                onChanged: (val) {
                  setState(() {
                    for (final k in keys) {
                      _settings[k] = val;
                    }
                  });
                },
                activeColor: color,
                inactiveThumbColor: Colors.grey,
                inactiveTrackColor: Colors.white.withValues(alpha: 0.1),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Wrap(
            spacing: 12,
            runSpacing: 12,
            children: List.generate(keys.length, (i) {
              final key = keys[i];
              return SizedBox(
                width: (MediaQuery.of(context).size.width - 56) / 2,
                child: _buildToggleItem(key, labels[i], color),
              );
            }),
          ),
        ],
      ),
    );
  }

  Widget _buildToggleItem(String key, String label, Color color) {
    return InkWell(
      onTap: () => setState(() => _settings[key] = !((_settings[key] as bool?) ?? false)),
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        decoration: BoxDecoration(
          color: ((_settings[key] as bool?) ?? false) ? color.withValues(alpha: 0.15) : Colors.white.withValues(alpha: 0.05),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: ((_settings[key] as bool?) ?? false) ? color.withValues(alpha: 0.3) : Colors.white.withValues(alpha: 0.1),
          ),
        ),
        child: Row(
          children: [
            Container(
              width: 20,
              height: 20,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: ((_settings[key] as bool?) ?? false) ? color : Colors.transparent,
                border: Border.all(
                  color: ((_settings[key] as bool?) ?? false) ? color : Colors.white.withValues(alpha: 0.3),
                  width: 2,
                ),
              ),
              child: ((_settings[key] as bool?) ?? false)
                  ? const Icon(Icons.check, size: 12, color: Colors.white)
                  : null,
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                label,
                style: TextStyle(
                  color: Colors.white.withValues(alpha: 0.9),
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSaveButton() {
    return SizedBox(
      width: double.infinity,
      height: 52,
      child: ElevatedButton(
        onPressed: _isLoading ? null : _saveSettings,
        style: ElevatedButton.styleFrom(
          backgroundColor: AppTheme.accentColor,
          foregroundColor: AppTheme.primaryColor,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        ),
        child: _isLoading
            ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: AppTheme.primaryColor))
            : const Text('Save Preferences', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 16)),
      ),
    );
  }
}
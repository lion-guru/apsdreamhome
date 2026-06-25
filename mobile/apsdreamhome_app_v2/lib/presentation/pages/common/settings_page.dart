import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:package_info_plus/package_info_plus.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../data/models/user_model.dart';
import '../../../data/repositories/kyc_repository_provider.dart';

class SettingsPage extends ConsumerStatefulWidget {
  const SettingsPage({super.key});

  @override
  ConsumerState<SettingsPage> createState() => _SettingsPageState();
}

class _SettingsPageState extends ConsumerState<SettingsPage> {
  bool _pushEnabled = true;
  bool _emailEnabled = true;
  bool _smsEnabled = false;
  String _selectedLanguage = 'en';
  bool _darkMode = false;
  bool _twoFactorEnabled = false;
  bool _biometricEnabled = false;
  String _appVersion = '2.0.0';
  String _buildNumber = '1';

  @override
  void initState() {
    super.initState();
    _loadPreferences();
    _loadAppInfo();
  }

  Future<void> _loadPreferences() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _pushEnabled = prefs.getBool('pref_push_notifications') ?? true;
      _emailEnabled = prefs.getBool('pref_email_notifications') ?? true;
      _smsEnabled = prefs.getBool('pref_sms_notifications') ?? false;
      _selectedLanguage = prefs.getString('pref_language') ?? 'en';
      _darkMode = prefs.getBool('pref_dark_mode') ?? false;
      _twoFactorEnabled = prefs.getBool('pref_two_factor') ?? false;
      _biometricEnabled = prefs.getBool('pref_biometric') ?? false;
    });
  }

  Future<void> _loadAppInfo() async {
    final info = await PackageInfo.fromPlatform();
    if (mounted) {
      setState(() {
        _appVersion = info.version;
        _buildNumber = info.buildNumber;
      });
    }
  }

  Future<void> _savePref(String key, dynamic value) async {
    final prefs = await SharedPreferences.getInstance();
    if (value is bool) {
      await prefs.setBool(key, value);
    } else if (value is String) {
      await prefs.setString(key, value);
    }
  }

  Future<void> _postNotificationPreferences() async {
    try {
      final api = ref.read(apiServiceProvider);
      await api.post('/user/notification-preferences', data: {
        'push_notifications': _pushEnabled,
        'email_notifications': _emailEnabled,
        'sms_notifications': _smsEnabled,
      });
    } catch (_) {}
  }

  Future<void> _postUserPreferences() async {
    try {
      final api = ref.read(apiServiceProvider);
      await api.post('/user/preferences', data: {
        'language': _selectedLanguage,
      });
    } catch (_) {}
  }

  void _showSnackBar(String message) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), duration: const Duration(seconds: 2)),
    );
  }

  Future<void> _clearCache() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Clear Cache'),
        content: const Text('This will clear all cached data. Continue?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(false),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(true),
            child: const Text('Clear', style: TextStyle(color: AppTheme.errorColor)),
          ),
        ],
      ),
    );
    if (confirmed == true) {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove('pref_cached_properties');
      await prefs.remove('pref_cached_leads');
      _showSnackBar('Cache cleared successfully');
    }
  }

  Future<void> _deleteAccount() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Delete Account'),
        content: const Text(
          'This action is irreversible. All your data will be permanently deleted. '
          'Are you absolutely sure?',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(false),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(true),
            child: const Text('Delete', style: TextStyle(color: AppTheme.errorColor)),
          ),
        ],
      ),
    );
    if (confirmed == true && mounted) {
      try {
        final api = ref.read(apiServiceProvider);
        await api.post('/user/account/delete');
        if (!mounted) return;
        await ref.read(authProvider.notifier).logout();
        if (!mounted) return;
        context.go('/login');
      } catch (_) {
        _showSnackBar('Failed to delete account. Please try again.');
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = ref.watch(authProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Settings'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(vertical: 8),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildAccountSection(user),
            _buildNotificationsSection(),
            _buildAppearanceSection(),
            _buildPrivacySecuritySection(),
            _buildSupportSection(),
            _buildAboutSection(),
            _buildDangerZone(),
            const SizedBox(height: 32),
          ],
        ),
      ),
    );
  }

  // ─── 1. Account Section ───────────────────────────────────────────────
  Widget _buildAccountSection(User? user) {
    final name = user?.name ?? 'Guest';
    final email = user?.email ?? '';
    final avatar = user?.avatar ?? '';

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _sectionHeader('Account'),
        Card(
          margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
          child: ListTile(
            onTap: () => context.push('/profile'),
            leading: CircleAvatar(
              radius: 24,
              backgroundColor: AppTheme.primaryColor.withValues(alpha: 0.1),
              backgroundImage: avatar.isNotEmpty
                  ? NetworkImage(avatar)
                  : null,
              child: avatar.isEmpty
                  ? Text(
                      name.isNotEmpty ? name[0].toUpperCase() : '?',
                      style: const TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.w600,
                        color: AppTheme.primaryColor,
                      ),
                    )
                  : null,
            ),
            title: Text(
              name,
              style: const TextStyle(fontWeight: FontWeight.w600),
            ),
            subtitle: Text(email),
            trailing: const Icon(Icons.chevron_right),
          ),
        ),
        _divider(),
        ListTile(
          leading: const Icon(Icons.lock_outline, color: AppTheme.primaryColor),
          title: const Text('Change Password'),
          subtitle: const Text('Update your account password'),
          trailing: const Icon(Icons.chevron_right),
          onTap: () => context.push('/change-password'),
        ),
        const SizedBox(height: 8),
      ],
    );
  }

  // ─── 2. Notifications Section ─────────────────────────────────────────
  Widget _buildNotificationsSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _sectionHeader('Notifications'),
        Card(
          margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
          child: Column(
            children: [
              SwitchListTile(
                secondary: const Icon(Icons.notifications_active, color: AppTheme.primaryColor),
                title: const Text('Push Notifications'),
                subtitle: const Text('Receive push alerts on this device'),
                value: _pushEnabled,
                onChanged: (val) {
                  setState(() => _pushEnabled = val);
                  _savePref('pref_push_notifications', val);
                  _postNotificationPreferences();
                },
              ),
              const Divider(height: 1, indent: 56),
              SwitchListTile(
                secondary: const Icon(Icons.email_outlined, color: AppTheme.primaryColor),
                title: const Text('Email Notifications'),
                subtitle: const Text('Receive updates via email'),
                value: _emailEnabled,
                onChanged: (val) {
                  setState(() => _emailEnabled = val);
                  _savePref('pref_email_notifications', val);
                  _postNotificationPreferences();
                },
              ),
              const Divider(height: 1, indent: 56),
              SwitchListTile(
                secondary: const Icon(Icons.sms_outlined, color: AppTheme.primaryColor),
                title: const Text('SMS Notifications'),
                subtitle: const Text('Receive alerts via SMS'),
                value: _smsEnabled,
                onChanged: (val) {
                  setState(() => _smsEnabled = val);
                  _savePref('pref_sms_notifications', val);
                  _postNotificationPreferences();
                },
              ),
            ],
          ),
        ),
        const SizedBox(height: 8),
      ],
    );
  }

  // ─── 3. Appearance Section ────────────────────────────────────────────
  Widget _buildAppearanceSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _sectionHeader('Appearance'),
        Card(
          margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
          child: Column(
            children: [
              ListTile(
                leading: const Icon(Icons.language, color: AppTheme.primaryColor),
                title: const Text('Language'),
                subtitle: Text(
                  _selectedLanguage == 'hi' ? 'हिंदी' : 'English',
                ),
              ),
              Padding(
                padding: const EdgeInsets.only(left: 56, right: 16, bottom: 12),
                child: Row(
                  children: [
                    Expanded(
                      child: _languageTile('English', 'en'),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: _languageTile('हिंदी', 'hi'),
                    ),
                  ],
                ),
              ),
              const Divider(height: 1, indent: 56),
              SwitchListTile(
                secondary: Icon(
                  _darkMode ? Icons.dark_mode : Icons.light_mode,
                  color: AppTheme.primaryColor,
                ),
                title: const Text('Dark Mode'),
                subtitle: Text(
                  _darkMode ? 'Dark theme enabled' : 'Light theme enabled',
                ),
                value: _darkMode,
                onChanged: (val) {
                  setState(() => _darkMode = val);
                  _savePref('pref_dark_mode', val);
                },
              ),
            ],
          ),
        ),
        const SizedBox(height: 8),
      ],
    );
  }

  Widget _languageTile(String label, String code) {
    final isSelected = _selectedLanguage == code;
    return GestureDetector(
      onTap: () {
        setState(() => _selectedLanguage = code);
        _savePref('pref_language', code);
        _postUserPreferences();
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(8),
          border: Border.all(
            color: isSelected ? AppTheme.primaryColor : Colors.grey.shade300,
            width: isSelected ? 2 : 1,
          ),
          color: isSelected
              ? AppTheme.primaryColor.withValues(alpha: 0.08)
              : Colors.transparent,
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              isSelected ? Icons.radio_button_checked : Icons.radio_button_unchecked,
              size: 18,
              color: isSelected ? AppTheme.primaryColor : Colors.grey,
            ),
            const SizedBox(width: 8),
            Text(
              label,
              style: TextStyle(
                fontSize: 14,
                fontWeight: isSelected ? FontWeight.w600 : FontWeight.w400,
                color: isSelected ? AppTheme.primaryColor : Colors.grey.shade700,
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ─── 4. Privacy & Security Section ────────────────────────────────────
  Widget _buildPrivacySecuritySection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _sectionHeader('Privacy & Security'),
        Card(
          margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
          child: Column(
            children: [
              SwitchListTile(
                secondary: const Icon(Icons.shield_outlined, color: AppTheme.primaryColor),
                title: const Text('Two-Factor Authentication'),
                subtitle: const Text('Add an extra layer of security'),
                value: _twoFactorEnabled,
                onChanged: (val) {
                  if (val) {
                    context.push('/user/two-factor');
                  } else {
                    setState(() => _twoFactorEnabled = false);
                    _savePref('pref_two_factor', false);
                  }
                },
              ),
              const Divider(height: 1, indent: 56),
              SwitchListTile(
                secondary: const Icon(Icons.fingerprint, color: AppTheme.primaryColor),
                title: const Text('Biometric Login'),
                subtitle: const Text('Use fingerprint or face to sign in'),
                value: _biometricEnabled,
                onChanged: (val) {
                  setState(() => _biometricEnabled = val);
                  _savePref('pref_biometric', val);
                },
              ),
              const Divider(height: 1, indent: 56),
              ListTile(
                leading: const Icon(Icons.cleaning_services_outlined, color: AppTheme.primaryColor),
                title: const Text('Clear Cache'),
                subtitle: const Text('Free up storage space'),
                trailing: const Icon(Icons.chevron_right),
                onTap: _clearCache,
              ),
            ],
          ),
        ),
        const SizedBox(height: 8),
      ],
    );
  }

  // ─── 5. Support Section ───────────────────────────────────────────────
  Widget _buildSupportSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _sectionHeader('Support'),
        Card(
          margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
          child: Column(
            children: [
              ListTile(
                leading: const Icon(Icons.help_outline, color: AppTheme.primaryColor),
                title: const Text('Help & Support'),
                subtitle: const Text('Contact us at +91 92771 21112'),
                trailing: const Icon(Icons.chevron_right),
                onTap: () async {
                  final uri = Uri.parse('tel:+919277121112');
                  if (await canLaunchUrl(uri)) {
                    await launchUrl(uri);
                  }
                },
              ),
              const Divider(height: 1, indent: 56),
              ListTile(
                leading: const Icon(Icons.bug_report_outlined, color: AppTheme.primaryColor),
                title: const Text('Report a Bug'),
                subtitle: const Text('Send us a detailed report'),
                trailing: const Icon(Icons.chevron_right),
                onTap: () async {
                  final uri = Uri(
                    scheme: 'mailto',
                    path: 'support@apsdreamhome.com',
                    query: 'subject=Bug Report - APS Dream Home App',
                  );
                  if (await canLaunchUrl(uri)) {
                    await launchUrl(uri);
                  }
                },
              ),
              const Divider(height: 1, indent: 56),
              ListTile(
                leading: const Icon(Icons.description_outlined, color: AppTheme.primaryColor),
                title: const Text('Terms & Conditions'),
                trailing: const Icon(Icons.chevron_right),
                onTap: () async {
                  final uri = Uri.parse('https://apsdreamhome.com/terms');
                  if (await canLaunchUrl(uri)) {
                    await launchUrl(uri, mode: LaunchMode.externalApplication);
                  }
                },
              ),
              const Divider(height: 1, indent: 56),
              ListTile(
                leading: const Icon(Icons.privacy_tip_outlined, color: AppTheme.primaryColor),
                title: const Text('Privacy Policy'),
                trailing: const Icon(Icons.chevron_right),
                onTap: () async {
                  final uri = Uri.parse('https://apsdreamhome.com/privacy');
                  if (await canLaunchUrl(uri)) {
                    await launchUrl(uri, mode: LaunchMode.externalApplication);
                  }
                },
              ),
            ],
          ),
        ),
        const SizedBox(height: 8),
      ],
    );
  }

  // ─── 6. About Section ─────────────────────────────────────────────────
  Widget _buildAboutSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _sectionHeader('About'),
        Card(
          margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
          child: Column(
            children: [
              ListTile(
                leading: const Icon(Icons.info_outline, color: AppTheme.primaryColor),
                title: const Text('App Version'),
                trailing: Text(
                  'v$_appVersion ($_buildNumber)',
                  style: TextStyle(
                    color: Colors.grey.shade600,
                    fontSize: 13,
                  ),
                ),
              ),
              const Divider(height: 1, indent: 56),
              const ListTile(
                leading: Icon(Icons.business, color: AppTheme.primaryColor),
                title: Text('APS Dream Home'),
                subtitle: Text('Real Estate & Colony Development'),
              ),
              const Divider(height: 1, indent: 56),
              const ListTile(
                leading: Icon(Icons.location_on_outlined, color: AppTheme.primaryColor),
                title: Text('Gorakhpur, Uttar Pradesh'),
                subtitle: Text('Head Office'),
              ),
            ],
          ),
        ),
        const SizedBox(height: 8),
      ],
    );
  }

  // ─── 7. Danger Zone ───────────────────────────────────────────────────
  Widget _buildDangerZone() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _sectionHeader('Danger Zone'),
        Card(
          margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
          child: ListTile(
            leading: const Icon(Icons.delete_forever, color: AppTheme.errorColor),
            title: const Text(
              'Delete Account',
              style: TextStyle(color: AppTheme.errorColor),
            ),
            subtitle: const Text(
              'Permanently remove your account and data',
              style: TextStyle(fontSize: 12),
            ),
            trailing: const Icon(Icons.chevron_right, color: AppTheme.errorColor),
            onTap: _deleteAccount,
          ),
        ),
      ],
    );
  }

  // ─── Helpers ──────────────────────────────────────────────────────────
  Widget _sectionHeader(String title) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 4),
      child: Text(
        title.toUpperCase(),
        style: TextStyle(
          fontSize: 12,
          fontWeight: FontWeight.w700,
          letterSpacing: 1.2,
          color: Colors.grey.shade600,
        ),
      ),
    );
  }

  Widget _divider() {
    return const Divider(height: 1, indent: 56);
  }
}

import 'dart:developer' as developer;
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:package_info_plus/package_info_plus.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

/// App Update Checker Service
/// Checks for new app versions and prompts update
class AppUpdateService {
  static final AppUpdateService _instance = AppUpdateService._internal();
  factory AppUpdateService() => _instance;
  AppUpdateService._internal();

  String _currentVersion = '';
  String _latestVersion = '';
  String? _updateUrl;
  String? _releaseNotes;
  bool _isUpdateRequired = false;

  /// Backend API endpoint for version check
  final String _versionCheckUrl = 'https://apsdreamhome.com/api/v1/app/version';

  /// Play Store URL
  final String _playStoreUrl =
      'https://play.google.com/store/apps/details?id=com.apsdreamhome.app';

  /// App Store URL
  final String _appStoreUrl =
      'https://apps.apple.com/app/aps-dream-home/id1234567890';

  /// Initialize
  Future<void> initialize() async {
    final packageInfo = await PackageInfo.fromPlatform();
    _currentVersion = packageInfo.version;
    developer.log('Current version: $_currentVersion',
        name: 'AppUpdateService');
  }

  /// Check for updates
  Future<UpdateCheckResult> checkForUpdate() async {
    try {
      if (_currentVersion.isEmpty) {
        await initialize();
      }

      // Call backend API
      final response = await http.get(
        Uri.parse(
            '$_versionCheckUrl?platform=${Platform.isIOS ? 'ios' : 'android'}&current_version=$_currentVersion'),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body) as Map<String, dynamic>;
        _latestVersion = (data['latest_version'] ?? _currentVersion) as String;
        _updateUrl = data['update_url'] as String?;
        _releaseNotes = data['release_notes'] as String?;
        _isUpdateRequired = (data['force_update'] ?? false) as bool;

        final needsUpdate = _shouldUpdate(_currentVersion, _latestVersion);

        return UpdateCheckResult(
          needsUpdate: needsUpdate,
          isRequired: _isUpdateRequired,
          currentVersion: _currentVersion,
          latestVersion: _latestVersion,
          releaseNotes: _releaseNotes,
          updateUrl: _updateUrl ?? _getStoreUrl(),
        );
      }

      return UpdateCheckResult(
        needsUpdate: false,
        currentVersion: _currentVersion,
        latestVersion: _currentVersion,
      );
    } catch (e) {
      developer.log('Update check error: $e', name: 'AppUpdateService');
      return UpdateCheckResult(
        needsUpdate: false,
        currentVersion: _currentVersion,
        latestVersion: _currentVersion,
        error: e.toString(),
      );
    }
  }

  /// Compare versions
  bool _shouldUpdate(String current, String latest) {
    try {
      final currentParts = current.split('.').map(int.parse).toList();
      final latestParts = latest.split('.').map(int.parse).toList();

      for (int i = 0; i < latestParts.length; i++) {
        final currentPart = i < currentParts.length ? currentParts[i] : 0;
        final latestPart = latestParts[i];

        if (latestPart > currentPart) return true;
        if (latestPart < currentPart) return false;
      }

      return false;
    } catch (e) {
      return false;
    }
  }

  /// Get appropriate store URL
  String _getStoreUrl() {
    if (Platform.isIOS) return _appStoreUrl;
    return _playStoreUrl;
  }

  /// Open store for update
  Future<void> openStore() async {
    final url = _updateUrl ?? _getStoreUrl();
    final uri = Uri.parse(url);

    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }

  /// Show update dialog
  Future<void> showUpdateDialog(
      BuildContext context, UpdateCheckResult result) async {
    if (!result.needsUpdate) return;

    await showDialog(
      context: context,
      barrierDismissible: !result.isRequired,
      builder: (context) => WillPopScope(
        onWillPop: () async => !result.isRequired,
        child: AlertDialog(
          title: Row(
            children: [
              Icon(
                result.isRequired ? Icons.error_outline : Icons.update,
                color: result.isRequired ? Colors.red : Colors.blue,
              ),
              const SizedBox(width: 8),
              Text(result.isRequired ? 'Update Required' : 'Update Available'),
            ],
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Current version: ${result.currentVersion}\n'
                'Latest version: ${result.latestVersion}',
              ),
              if (result.releaseNotes != null) ...[
                const SizedBox(height: 16),
                const Text(
                  'What\'s new:',
                  style: TextStyle(fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 8),
                Text(result.releaseNotes!),
              ],
            ],
          ),
          actions: [
            if (!result.isRequired)
              TextButton(
                onPressed: () => Navigator.pop(context),
                child: const Text('Later'),
              ),
            ElevatedButton(
              onPressed: () {
                openStore();
                if (result.isRequired) {
                  // Don't allow dismiss if required
                  return;
                }
                Navigator.pop(context);
              },
              child: const Text('Update Now'),
            ),
          ],
        ),
      ),
    );
  }

  /// Getters
  String get currentVersion => _currentVersion;
  String get latestVersion => _latestVersion;
  bool get isUpdateRequired => _isUpdateRequired;
}

/// Update check result
class UpdateCheckResult {
  final bool needsUpdate;
  final bool isRequired;
  final String currentVersion;
  final String latestVersion;
  final String? releaseNotes;
  final String? updateUrl;
  final String? error;

  UpdateCheckResult({
    required this.needsUpdate,
    this.isRequired = false,
    required this.currentVersion,
    required this.latestVersion,
    this.releaseNotes,
    this.updateUrl,
    this.error,
  });
}

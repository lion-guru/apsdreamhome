import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';

/// Storage Service for local data persistence
/// Works on both mobile and web platforms
class StorageService {
  static final StorageService _instance = StorageService._internal();
  factory StorageService() => _instance;
  StorageService._internal();

  SharedPreferences? _prefs;

  Future<void> init() async {
    _prefs = await SharedPreferences.getInstance();
  }

  // String operations
  Future<bool> setString(String key, String value) async {
    if (_prefs == null) await init();
    return await _prefs!.setString(key, value);
  }

  String? getString(String key) {
    if (_prefs == null) return null;
    return _prefs!.getString(key);
  }

  // Bool operations
  Future<bool> setBool(String key, bool value) async {
    if (_prefs == null) await init();
    return await _prefs!.setBool(key, value);
  }

  bool? getBool(String key) {
    if (_prefs == null) return null;
    return _prefs!.getBool(key);
  }

  // Int operations
  Future<bool> setInt(String key, int value) async {
    if (_prefs == null) await init();
    return await _prefs!.setInt(key, value);
  }

  int? getInt(String key) {
    if (_prefs == null) return null;
    return _prefs!.getInt(key);
  }

  // JSON operations
  Future<bool> setJson(String key, Map<String, dynamic> value) async {
    return await setString(key, jsonEncode(value));
  }

  Map<String, dynamic>? getJson(String key) {
    final String? jsonString = getString(key);
    if (jsonString == null) return null;
    try {
      return jsonDecode(jsonString) as Map<String, dynamic>;
    } catch (e) {
      return null;
    }
  }

  // Clear operations
  Future<bool> remove(String key) async {
    if (_prefs == null) await init();
    return await _prefs!.remove(key);
  }

  Future<bool> clear() async {
    if (_prefs == null) await init();
    return await _prefs!.clear();
  }

  // Check if key exists
  bool hasKey(String key) {
    if (_prefs == null) return false;
    return _prefs!.containsKey(key);
  }
}

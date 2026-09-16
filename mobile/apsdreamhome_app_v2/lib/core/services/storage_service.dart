import 'package:hive_flutter/hive_flutter.dart';

class StorageService {
  static final StorageService _instance = StorageService._internal();
  factory StorageService() => _instance;
  StorageService._internal();

  static const String _emiCacheBoxName = 'emi_tracker_cache';
  static const String _emiTimestampKey = 'emi_tracker_timestamp';
  static const String _emiDataKey = 'emi_data';

  Box<dynamic>? _cacheBox;

  Future<void> initialize() async {
    await Hive.initFlutter();
    _cacheBox = await Hive.openBox<dynamic>(_emiCacheBoxName);
  }

  // Cache EMI data with timestamp
  Future<void> cacheEmiData(List<Map<String, dynamic>> emis) async {
    if (_cacheBox == null) return;
    await _cacheBox!.put(_emiDataKey, emis);
    await _cacheBox!.put(_emiTimestampKey, DateTime.now().millisecondsSinceEpoch);
  }

  // Get cached EMI data
  List<Map<String, dynamic>>? getCachedEmiData() {
    if (_cacheBox == null) return null;
    final data = _cacheBox!.get(_emiDataKey);
    if (data == null) return null;
    return (data as List).map((e) => Map<String, dynamic>.from(e as Map)).toList();
  }

  // Get cache timestamp
  DateTime? getCacheTimestamp() {
    if (_cacheBox == null) return null;
    final timestamp = _cacheBox!.get(_emiTimestampKey);
    if (timestamp == null) return null;
    return DateTime.fromMillisecondsSinceEpoch(timestamp as int);
  }

  // Check if cache is fresh (within 15 minutes)
  bool isCacheFresh() {
    final timestamp = getCacheTimestamp();
    if (timestamp == null) return false;
    return DateTime.now().difference(timestamp).inMinutes < 15;
  }

  // Clear EMI cache
  Future<void> clearEmiCache() async {
    if (_cacheBox == null) return;
    await _cacheBox!.delete(_emiDataKey);
    await _cacheBox!.delete(_emiTimestampKey);
  }

  // Get cache info for display
  Map<String, dynamic> getCacheInfo() {
    final timestamp = getCacheTimestamp();
    final data = getCachedEmiData();
    return {
      'hasData': data != null && data.isNotEmpty,
      'count': data?.length ?? 0,
      'timestamp': timestamp?.toIso8601String(),
      'isFresh': isCacheFresh(),
    };
  }

  // Generic cache methods for other data types
  Future<void> cacheData(String key, dynamic data) async {
    if (_cacheBox == null) return;
    await _cacheBox!.put(key, data);
    await _cacheBox!.put('${key}_timestamp', DateTime.now().millisecondsSinceEpoch);
  }

  dynamic getCachedData(String key) {
    if (_cacheBox == null) return null;
    return _cacheBox!.get(key);
  }

  DateTime? getCacheTimestampForKey(String key) {
    if (_cacheBox == null) return null;
    final timestamp = _cacheBox!.get('${key}_timestamp');
    if (timestamp == null) return null;
    return DateTime.fromMillisecondsSinceEpoch(timestamp as int);
  }
}
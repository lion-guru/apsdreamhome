import 'dart:io';
import 'dart:typed_data';
import 'package:path_provider/path_provider.dart';
import 'package:http/http.dart' as http;
import 'package:crypto/crypto.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter/foundation.dart';

/// Service for caching images for offline viewing
class ImageCacheService {
  static final ImageCacheService _instance = ImageCacheService._internal();
  factory ImageCacheService() => _instance;
  ImageCacheService._internal();

  static const String _cacheDirName = 'image_cache';
  static const String _metadataKey = 'image_cache_metadata';
  static const int _maxCacheSizeMB = 200; // 200MB max cache
  static const int _maxCacheAgeDays = 30;

  Directory? _cacheDir;
  Map<String, _CacheMetadata> _metadata = {};

  Future<void> initialize() async {
    final appDir = await getApplicationDocumentsDirectory();
    _cacheDir = Directory('${_appDir.path}/$_cacheDirName');
    if (!await _cacheDir!.exists()) {
      await _cacheDir!.create(recursive: true);
    }
    await _loadMetadata();
    await _cleanupOldEntries();
  }

  Future<void> _loadMetadata() async {
    final prefs = await SharedPreferences.getInstance();
    final jsonString = prefs.getString(_metadataKey);
    if (jsonString != null) {
      final List<dynamic> jsonList = json.decode(jsonString);
      _metadata = {
        for (var item in jsonList)
          item['key'] as String: _CacheMetadata.fromJson(item)
      };
    }
  }

  Future<void> _saveMetadata() async {
    final prefs = await SharedPreferences.getInstance();
    final jsonList = _metadata.values.map((m) => m.toJson()).toList();
    await prefs.setString(_metadataKey, json.encode(jsonList));
  }

  /// Generate cache key from URL
  String _generateKey(String url) {
    return sha256.convert(utf8.encode(url)).toString();
  }

  /// Get cached file path for URL
  Future<File?> getCachedFile(String url) async {
    if (_cacheDir == null) await initialize();
    
    final key = _generateKey(url);
    final metadata = _metadata[key];
    
    if (metadata == null) return null;
    
    final file = File('${_cacheDir!.path}/${metadata.fileName}');
    if (await file.exists()) {
      // Update last accessed
      metadata.lastAccessed = DateTime.now();
      await _saveMetadata();
      return file;
    }
    
    // File missing, remove metadata
    _metadata.remove(key);
    await _saveMetadata();
    return null;
  }

  /// Cache an image from URL
  Future<File?> cacheImage(String url, {Duration? maxAge}) async {
    if (_cacheDir == null) await initialize();
    
    try {
      final response = await http.get(Uri.parse(url))
          .timeout(const Duration(seconds: 30));
      
      if (response.statusCode != 200) {
        debugPrint('Failed to cache image: HTTP ${response.statusCode}');
        return null;
      }

      final bytes = response.bodyBytes;
      final contentType = response.headers['content-type'] ?? 'image/jpeg';
      final extension = _getExtensionFromContentType(contentType);
      
      final key = _generateKey(url);
      final fileName = '${sha256.convert(utf8.encode(url)).toString().substring(0, 16)}$extension';
      final file = File('${_cacheDir!.path}/$fileName');
      
      await file.writeAsBytes(bytes, flush: true);
      
      // Store metadata
      _metadata[url] = _CacheMetadata(
        key: url,
        fileName: fileName,
        url: url,
        size: bytes.length,
        contentType: contentType,
        createdAt: DateTime.now(),
        lastAccessed: DateTime.now(),
        expiresAt: DateTime.now().add(maxAge ?? Duration(days: _maxCacheAgeDays)),
      );
      await _saveMetadata();
      
      await _enforceCacheSizeLimit();
      
      return File('${_cacheDir!.path}/$fileName');
    } catch (e) {
      debugPrint('Failed to cache image $url: $e');
      return null;
    }
  }

  /// Pre-cache multiple images
  Future<void> preCacheImages(List<String> urls, {Duration? maxAge}) async {
    for (final url in urls) {
      try {
        await cacheImage(url, maxAge: maxAge);
        // Small delay to avoid overwhelming the network
        await Future.delayed(const Duration(milliseconds: 100));
      } catch (e) {
        debugPrint('Failed to pre-cache $url: $e');
      }
    }
  }

  /// Get file as Uint8List for display
  Future<Uint8List?> getImageBytes(String url) async {
    final file = await getCachedFile(url);
    if (file == null) return null;
    return await file.readAsBytes();
  }

  /// Check if image is cached
  Future<bool> isCached(String url) async {
    final file = await getCachedFile(url);
    return file != null;
  }

  /// Remove cached image
  Future<void> removeCachedImage(String url) async {
    if (_cacheDir == null) await initialize();
    
    final key = _generateKey(url);
    final metadata = _metadata[url];
    
    if (metadata != null) {
      final file = File('${_cacheDir!.path}/${metadata.fileName}');
      if (await file.exists()) {
        await file.delete();
      }
      _metadata.remove(url);
      await _saveMetadata();
    }
  }

  /// Clear all cached images
  Future<void> clearCache() async {
    if (_cacheDir == null) await initialize();
    
    final files = _cacheDir!.listSync(recursive: true);
    for (final file in files) {
      if (file is File) {
        await file.delete();
      }
    }
    _metadata.clear();
    await _saveMetadata();
  }

  /// Get cache statistics
  Future<CacheStats> getStats() async {
    if (_cacheDir == null) await initialize();
    
    int totalSize = 0;
    int count = 0;
    
    for (final metadata in _metadata.values) {
      totalSize += metadata.size;
      count++;
    }
    
    return CacheStats(
      count: count,
      totalSizeBytes: totalSize,
      maxSizeBytes: _maxCacheSizeMB * 1024 * 1024,
    );
  }

  /// Clean up expired entries
  Future<void> _cleanupOldEntries() async {
    final now = DateTime.now();
    final toRemove = <String>[];
    
    for (final entry in _metadata.entries) {
      if (entry.value.expiresAt.isBefore(now) || 
          entry.value.lastAccessed.difference(now).inDays > _maxCacheAgeDays) {
        toRemove.add(entry.key);
      }
    }
    
    for (final key in toRemove) {
      await removeCachedImage(entry.value.url);
    }
  }

  /// Enforce cache size limit
  Future<void> _enforceCacheSizeLimit() async {
    final maxSize = _maxCacheSizeMB * 1024 * 1024;
    int totalSize = _metadata.values.fold(0, (sum, m) => sum + m.size);
    
    if (totalSize <= maxSize) return;
    
    // Sort by last accessed (oldest first)
    final entries = _metadata.entries.toList()
      ..sort((a, b) => a.value.lastAccessed.compareTo(b.value.lastAccessed));
    
    int currentSize = _metadata.values.fold(0, (sum, m) => sum + m.size);
    
    for (final entry in entries) {
      if (currentSize <= maxSize * 0.8) break; // Keep 80% buffer
      
      await removeCachedImage(entry.value.url);
      currentSize -= entry.value.size;
    }
  }

  String _getExtensionFromContentType(String contentType) {
    if (contentType.contains('jpeg') || contentType.contains('jpg')) return '.jpg';
    if (contentType.contains('png')) return '.png';
    if (contentType.contains('webp')) return '.webp';
    if (contentType.contains('gif')) return '.gif';
    return '.jpg';
  }
}

class _CacheMetadata {
  final String key;
  final String fileName;
  final String url;
  final int size;
  final String contentType;
  final DateTime createdAt;
  DateTime lastAccessed;
  DateTime expiresAt;

  _CacheMetadata({
    required this.key,
    required this.fileName,
    required this.url,
    required this.size,
    required this.contentType,
    required this.createdAt,
    required this.lastAccessed,
    required this.expiresAt,
  });

  Map<String, dynamic> toJson() => {
    'key': key,
    'fileName': fileName,
    'url': url,
    'size': size,
    'contentType': contentType,
    'createdAt': createdAt.toIso8601String(),
    'lastAccessed': lastAccessed.toIso8601String(),
    'expiresAt': expiresAt.toIso8601String(),
  };

  factory _CacheMetadata.fromJson(Map<String, dynamic> json) => _CacheMetadata(
    key: json['key'],
    fileName: json['fileName'],
    url: json['url'],
    size: json['size'],
    contentType: json['contentType'],
    createdAt: DateTime.parse(json['createdAt']),
    lastAccessed: DateTime.parse(json['lastAccessed']),
    expiresAt: DateTime.parse(json['expiresAt']),
  );
}

class CacheStats {
  final int count;
  final int totalSizeBytes;
  final int maxSizeBytes;

  CacheStats({
    required this.count,
    required this.totalSizeBytes,
    required this.maxSizeBytes,
  });

  double get usagePercent => (totalSizeBytes / maxSizeBytes * 100).clamp(0, 100);
  
  String get formattedSize => _formatBytes(totalSizeBytes);
  String get formattedMaxSize => _formatBytes(maxSizeBytes);

  String _formatBytes(int bytes) {
    if (bytes < 1024) return '$bytes B';
    if (bytes < 1024 * 1024) return '${(bytes / 1024).toStringAsFixed(1)} KB';
    if (bytes < 1024 * 1024 * 1024) return '${(bytes / (1024 * 1024)).toStringAsFixed(1)} MB';
    return '${(bytes / (1024 * 1024 * 1024)).toStringAsFixed(1)} GB';
  }
}
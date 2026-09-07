import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter/foundation.dart';
import '../../core/services/database_helper.dart';
import '../../core/services/api_service.dart';
import '../../core/constants/app_constants.dart';

/// Messages Repository - Handles local-first messaging with server sync
class MessagesRepository {
  final DatabaseHelper _dbHelper;
  final ApiService _apiService;

  MessagesRepository(this._dbHelper, this._apiService);

  /// Get all conversations for the current user
  Future<List<Map<String, dynamic>>> getConversations(int userId) async {
    return await _dbHelper.getConversations(userId);
  }

  /// Get messages for a specific conversation
  Future<List<Map<String, dynamic>>> getMessages(int userId, int otherUserId) async {
    return await _dbHelper.getMessages(userId, otherUserId);
  }

  /// Get unread message count for user
  Future<int> getUnreadCount(int userId) async {
    return await _dbHelper.getUnreadCount(userId);
  }

  /// Send a message - save locally first, then sync to server
  Future<Map<String, dynamic>> sendMessage({
    required int senderId,
    required int receiverId,
    required String content,
    String messageType = 'text',
  }) async {
    final localId = 'local_${DateTime.now().millisecondsSinceEpoch}';
    
    // Save locally first
    await _dbHelper.saveMessageLocal(
      senderId: senderId,
      receiverId: receiverId,
      content: content,
      localId: localId,
    );

    // Try to sync to server
    try {
      final api = ApiService();
      await api.initialize();
      final res = await api.post(
        AppConstants.sendMessageEndpoint,
        data: {'receiver_id': receiverId, 'message': content},
      );
      
      if (res['success'] == true && res['message_id'] != null) {
        // Mark local message as synced
        await _dbHelper.markMessageSynced(localId, (res['message_id'] as num).toInt());
        
        return {
          'success': true,
          'message_id': res['message_id'],
          'local_id': localId,
        };
      } else {
        await _dbHelper.markMessageFailed(localId);
        return {'success': false, 'error': res['message'] ?? 'Send failed'};
      }
    } catch (e) {
      // Network error - keep as unsynced, will retry later
      debugPrint('Message send failed (will retry): $e');
      return {'success': true, 'local_id': localId, 'pending': true};
    }
  }

  /// Mark messages as read
  Future<void> markMessagesRead(int userId, int otherUserId) async {
    await _dbHelper.markMessagesRead(userId, otherUserId);
    
    // Also sync to server
    try {
      final api = ApiService();
      await api.initialize();
      await api.post('${AppConstants.markReadEndpoint}/$otherUserId');
    } catch (_) {}
  }

  /// Sync unsynced messages to server
  Future<void> syncPendingMessages() async {
    final unsynced = await _dbHelper.getUnsyncedMessages();
    
    if (unsynced.isEmpty) return;
    
    try {
      final api = ApiService();
      await api.initialize();
      
      for (final msg in unsynced) {
        try {
          final res = await _apiService.post(
            AppConstants.sendMessageEndpoint,
            data: {
              'receiver_id': msg['receiver_id'],
              'message': msg['content'],
            },
          );
          
          if (res['success'] == true && res['message_id'] != null) {
            await _dbHelper.markMessageSynced(msg['local_id'] as String, (res['message_id'] as num).toInt());
          }
        } catch (e) {
          debugPrint('Failed to sync message ${msg['local_id']}: $e');
          // Continue with other messages
        }
      }
    } catch (e) {
      debugPrint('Sync pending messages failed: $e');
    }
  }
}

/// Provider for MessagesRepository
final messagesRepositoryProvider = Provider<MessagesRepository>((ref) {
  final dbHelper = DatabaseHelper();
  final apiService = ApiService();
  return MessagesRepository(dbHelper, apiService);
});
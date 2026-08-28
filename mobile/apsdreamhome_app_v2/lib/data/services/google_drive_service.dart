import 'dart:convert';
import 'dart:io';
import 'package:googleapis/drive/v3.dart' as drive;
import 'package:googleapis_auth/auth_io.dart';
import 'package:path_provider/path_provider.dart';

import '../../core/services/api_service.dart';
import '../../core/utils/logger.dart';

/// Google Drive Integration Service
/// Multi-drive support: Admin Drive, Colony Drives, Department Drives
class GoogleDriveService {
  final ApiService _api;
  
  // Google Drive API Credentials
  static const String _clientId = 'YOUR_CLIENT_ID.apps.googleusercontent.com';
  static const String _clientSecret = 'YOUR_CLIENT_SECRET';
  static const List<String> _scopes = [drive.DriveApi.driveScope];
  
  // Drive IDs (Multiple drives)
  static const String _adminDriveId = 'ADMIN_DRIVE_FOLDER_ID';
  static const String _documentsDriveId = 'DOCUMENTS_DRIVE_FOLDER_ID';
  static const String _receiptsDriveId = 'RECEIPTS_DRIVE_FOLDER_ID';
  static const String _coloniesDriveId = 'COLONIES_DRIVE_FOLDER_ID';
  
  late drive.DriveApi _driveApi;
  AuthClient? _authClient;

  GoogleDriveService({ApiService? api}) : _api = api ?? ApiService();

  // ==================== AUTHENTICATION ====================
  
  /// Initialize Google Drive API
  Future<void> initialize() async {
    try {
      final credentials = ClientId(_clientId, _clientSecret);
      _authClient = await clientViaUserConsent(
        credentials,
        _scopes,
        _promptUserConsent,
      );
      
      _driveApi = drive.DriveApi(_authClient!);
      AppLogger.info('Google Drive API initialized');
    } catch (e) {
      AppLogger.error('Error initializing Google Drive', e);
      rethrow;
    }
  }
  
  void _promptUserConsent(String url) {
    // In real app, open browser for user to authenticate
    AppLogger.info('Please authenticate: $url');
  }

  // ==================== FILE UPLOAD ====================
  
  /// Upload File to Google Drive
  Future<drive.File?> uploadFile({
    required File file,
    required String fileName,
    required DriveFolder folder,
    String? description,
    Map<String, String>? metadata,
  }) async {
    try {
      final folderId = _getFolderId(folder);
      
      // Create file metadata
      final driveFile = drive.File()
        ..name = fileName
        ..description = description
        ..parents = [folderId]
        ..properties = metadata ?? {};
      
      // Upload file
      final media = drive.Media(file.openRead(), file.lengthSync());
      
      final result = await _driveApi.files.create(
        driveFile,
        uploadMedia: media,
      );
      
      // Log upload
      await _logDriveActivity(
        action: 'upload',
        fileId: result.id!,
        fileName: fileName,
        folder: folder,
      );
      
      AppLogger.info('File uploaded: ${result.id}');
      return result;
    } catch (e) {
      AppLogger.error('Error uploading file to Drive', e);
      return null;
    }
  }
  
  /// Upload Document from App
  Future<String?> uploadDocument({
    required String localPath,
    required String documentType, // booking_agreement, payment_receipt, kyc, etc.
    required String relatedId, // booking_id, customer_id, etc.
    required DriveFolder folder,
  }) async {
    try {
      final file = File(localPath);
      if (!await file.exists()) {
        AppLogger.error('File not found: $localPath');
        return null;
      }
      
      // Generate filename
      final timestamp = DateTime.now().millisecondsSinceEpoch;
      final fileName = '${documentType}_${relatedId}_$timestamp.pdf';
      
      // Upload to Drive
      final result = await uploadFile(
        file: file,
        fileName: fileName,
        folder: folder,
        description: '$documentType for $relatedId',
        metadata: {
          'documentType': documentType,
          'relatedId': relatedId,
          'uploadedAt': DateTime.now().toIso8601String(),
        },
      );
      
      if (result != null) {
        // Save to Firestore for easy retrieval
        await _saveDocumentReference(
          driveFileId: result.id!,
          fileName: fileName,
          documentType: documentType,
          relatedId: relatedId,
          folder: folder,
        );
        
        return result.id;
      }
      
      return null;
    } catch (e) {
      AppLogger.error('Error uploading document', e);
      return null;
    }
  }
  
  /// Upload Receipt/Invoice
  Future<String?> uploadReceipt({
    required String receiptPath,
    required String receiptNumber,
    required String customerId,
    required String bookingId,
    required double amount,
  }) async {
    return uploadDocument(
      localPath: receiptPath,
      documentType: 'payment_receipt',
      relatedId: bookingId,
      folder: DriveFolder.receipts,
    );
  }
  
  /// Upload Colony Documents (Maps, Legal Papers)
  Future<String?> uploadColonyDocument({
    required String filePath,
    required String colonyId,
    required String documentType, // master_plan, legal_paper, brochure, etc.
  }) async {
    return uploadDocument(
      localPath: filePath,
      documentType: 'colony_$documentType',
      relatedId: colonyId,
      folder: DriveFolder.colonies,
    );
  }

  // ==================== FILE DOWNLOAD ====================
  
  /// Download File from Drive
  Future<String?> downloadFile({
    required String driveFileId,
    required String localFileName,
  }) async {
    try {
      // Download
      final media = await _driveApi.files.get(
        driveFileId,
        downloadOptions: drive.DownloadOptions.fullMedia,
      ) as drive.Media;
      
      // Save to local storage
      final directory = await getApplicationDocumentsDirectory();
      final localPath = '${directory.path}/$localFileName';
      final localFile = File(localPath);
      
      final sink = localFile.openWrite();
      await media.stream.pipe(sink);
      await sink.close();
      
      AppLogger.info('File downloaded: $localPath');
      return localPath;
    } catch (e) {
      AppLogger.error('Error downloading file', e);
      return null;
    }
  }
  
  /// Get Download URL (for viewing in browser/app)
  Future<String?> getDownloadUrl(String driveFileId) async {
    try {
      // Make file publicly readable
      final permission = drive.Permission()
        ..type = 'anyone'
        ..role = 'reader';
      
      await _driveApi.permissions.create(permission, driveFileId);
      
      // Generate download URL
      final url = 'https://drive.google.com/uc?export=download&id=$driveFileId';
      return url;
    } catch (e) {
      AppLogger.error('Error generating download URL', e);
      return null;
    }
  }
  
  /// Get View URL (for viewing in browser)
  String getViewUrl(String driveFileId) {
    return 'https://drive.google.com/file/d/$driveFileId/view';
  }

  // ==================== FILE MANAGEMENT ====================
  
  /// List Files in Folder
  Future<List<drive.File>> listFiles({
    required DriveFolder folder,
    String? query,
    int pageSize = 100,
  }) async {
    try {
      final folderId = _getFolderId(folder);
      
      String? q = "'$folderId' in parents and trashed = false";
      if (query != null && query.isNotEmpty) {
        q += " and name contains '$query'";
      }
      
      final result = await _driveApi.files.list(
        q: q,
        pageSize: pageSize,
        $fields: 'files(id, name, mimeType, size, createdTime, modifiedTime, webViewLink)',
      );
      
      return result.files ?? [];
    } catch (e) {
      AppLogger.error('Error listing files', e);
      return [];
    }
  }
  
  /// Search Files
  Future<List<drive.File>> searchFiles({
    required String query,
    DriveFolder? folder,
  }) async {
    try {
      String q = "name contains '$query' and trashed = false";
      
      if (folder != null) {
        final folderId = _getFolderId(folder);
        q += " and '$folderId' in parents";
      }
      
      final result = await _driveApi.files.list(
        q: q,
        pageSize: 50,
        $fields: 'files(id, name, mimeType, size, createdTime, modifiedTime)',
      );
      
      return result.files ?? [];
    } catch (e) {
      AppLogger.error('Error searching files', e);
      return [];
    }
  }
  
  /// Delete File
  Future<bool> deleteFile(String driveFileId) async {
    try {
      await _driveApi.files.delete(driveFileId);
      
      await _logDriveActivity(
        action: 'delete',
        fileId: driveFileId,
        fileName: '',
        folder: DriveFolder.admin,
      );
      
      return true;
    } catch (e) {
      AppLogger.error('Error deleting file', e);
      return false;
    }
  }
  
  /// Move File to Different Folder
  Future<bool> moveFile({
    required String driveFileId,
    required DriveFolder destinationFolder,
  }) async {
    try {
      final destinationId = _getFolderId(destinationFolder);
      
      // Get current parents
      final file = await _driveApi.files.get(driveFileId, $fields: 'parents') as drive.File;
      final previousParents = file.parents?.join(',') ?? '';
      
      // Move file
      await _driveApi.files.update(
        drive.File(),
        driveFileId,
        addParents: destinationId,
        removeParents: previousParents,
      );
      
      return true;
    } catch (e) {
      AppLogger.error('Error moving file', e);
      return false;
    }
  }

  // ==================== BACKUP & SYNC ====================
  
  /// Backup Database to Google Drive
  Future<bool> backupDatabase() async {
    try {
      // Export Firestore data
      final backupData = await _exportFirestoreData();
      
      // Create JSON file
      final directory = await getTemporaryDirectory();
      final timestamp = DateTime.now().toIso8601String().replaceAll(':', '-');
      final fileName = 'aps_backup_$timestamp.json';
      final file = File('${directory.path}/$fileName');
      await file.writeAsString(jsonEncode(backupData));
      
      // Upload to Drive
      final result = await uploadFile(
        file: file,
        fileName: fileName,
        folder: DriveFolder.admin,
        description: 'Database backup created at $timestamp',
        metadata: {
          'type': 'database_backup',
          'createdAt': timestamp,
        },
      );
      
      // Delete local temp file
      await file.delete();
      
      return result != null;
    } catch (e) {
      AppLogger.error('Error backing up database', e);
      return false;
    }
  }
  
  /// Sync Documents to Local Storage
  Future<void> syncDocumentsToLocal({
    required DriveFolder folder,
    required List<String> documentTypes,
  }) async {
    try {
      final files = await listFiles(folder: folder);
      final directory = await getApplicationDocumentsDirectory();
      
      for (final file in files) {
        if (file.id == null || file.name == null) continue;
        
        final localPath = '${directory.path}/${file.name}';
        final localFile = File(localPath);
        
        // Download if not exists or outdated
        if (!await localFile.exists()) {
          await downloadFile(
            driveFileId: file.id!,
            localFileName: file.name!,
          );
        }
      }
      
      AppLogger.info('Documents synced to local storage');
    } catch (e) {
      AppLogger.error('Error syncing documents', e);
    }
  }

  // ==================== HELPER METHODS ====================
  
  String _getFolderId(DriveFolder folder) {
    switch (folder) {
      case DriveFolder.admin:
        return _adminDriveId;
      case DriveFolder.documents:
        return _documentsDriveId;
      case DriveFolder.receipts:
        return _receiptsDriveId;
      case DriveFolder.colonies:
        return _coloniesDriveId;
      default:
        return _adminDriveId;
    }
  }
  
  Future<void> _saveDocumentReference({
    required String driveFileId,
    required String fileName,
    required String documentType,
    required String relatedId,
    required DriveFolder folder,
  }) async {
    try {
      await _api.request(
        method: 'POST',
        endpoint: 'documents',
        data: {
          'drive_file_id': driveFileId,
          'file_name': fileName,
          'document_type': documentType,
          'related_id': relatedId,
          'folder': folder.name,
          'uploaded_at': DateTime.now().toIso8601String(),
        },
      );
    } catch (e) {
      AppLogger.error('Error saving document reference', e);
    }
  }
  
  Future<void> _logDriveActivity({
    required String action,
    required String fileId,
    required String fileName,
    required DriveFolder folder,
  }) async {
    try {
      await _api.request(
        method: 'POST',
        endpoint: 'documents/activity',
        data: {
          'action': action,
          'file_id': fileId,
          'file_name': fileName,
          'folder': folder.name,
          'timestamp': DateTime.now().toIso8601String(),
        },
      );
    } catch (e) {
      AppLogger.error('Error logging drive activity', e);
    }
  }
  
  Future<Map<String, dynamic>> _exportFirestoreData() async {
    final backup = <String, dynamic>{};
    
    final endpoints = {
      'users': 'profile',
      'bookings': 'properties',
      'payments': 'mlm/payouts',
      'colonies': 'properties',
      'plots': 'properties',
    };
    
    for (final entry in endpoints.entries) {
      try {
        final result = await _api.request(
          method: 'GET',
          endpoint: entry.value,
        );
        backup[entry.key] = result['data'] ?? result;
      } catch (e) {
        AppLogger.error('Error exporting ${entry.key}', e);
        backup[entry.key] = [];
      }
    }
    
    return backup;
  }
}

enum DriveFolder {
  admin,
  documents,
  receipts,
  colonies,
  legal,
  marketing,
}

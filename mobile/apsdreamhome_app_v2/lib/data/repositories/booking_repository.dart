import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/services/api_service.dart';
import '../../core/services/database_helper.dart';
import '../models/booking_model.dart';

/// Booking Repository - Handles property bookings
/// Offline-first: Creates locally, syncs when online
class BookingRepository {
  final ApiService _apiService;
  final DatabaseHelper _dbHelper;

  BookingRepository(this._apiService, this._dbHelper);

  /// Get my bookings
  Future<List<BookingModel>> getMyBookings({
    String? status,
    DateTime? fromDate,
    DateTime? toDate,
  }) async {
    // Try local first
    final localData = await _dbHelper.getMyBookings(
      status: status,
      fromDate: fromDate,
      toDate: toDate,
    );
    final localBookings =
        localData.map((e) => BookingModel.fromJson(e)).toList();

    // If online, fetch from API
    if (await _apiService.isConnected()) {
      try {
        final filters = <String, dynamic>{};
        if (status != null) filters['status'] = status;
        if (fromDate != null) {
          filters['from_date'] = fromDate.toIso8601String();
        }
        if (toDate != null) {
          filters['to_date'] = toDate.toIso8601String();
        }

        final response = await _apiService.get(
          '/bookings',
          queryParameters: filters,
        );
        final bookings = (response['data'] as List)
            .map((json) => BookingModel.fromJson(json as Map<String, dynamic>))
            .toList();

        // Update local cache
        await _dbHelper
            .saveBookings(response['data'] as List<Map<String, dynamic>>);

        return bookings;
      } catch (e) {
        return localBookings;
      }
    }

    return localBookings;
  }

  /// Get booking details
  Future<BookingModel?> getBookingById(String bookingId) async {
    // Try local first
    final local = await _dbHelper.getBookingById(bookingId);
    if (local != null) {
      return BookingModel.fromJson(local);
    }

    // If online, fetch fresh
    if (await _apiService.isConnected()) {
      try {
        final response = await _apiService.get('/bookings/$bookingId');
        final booking =
            BookingModel.fromJson(response['data'] as Map<String, dynamic>);

        await _dbHelper.saveBooking(response['data'] as Map<String, dynamic>);
        return booking;
      } catch (e) {
        return local != null ? BookingModel.fromJson(local) : null;
      }
    }

    return local != null ? BookingModel.fromJson(local) : null;
  }

  /// Create booking (Offline-first)
  Future<BookingModel> createBooking({
    required String propertyId,
    required double amount,
    String? notes,
    DateTime? preferredDate,
  }) async {
    // Create local booking first
    final localBooking = {
      'server_id': null,
      'property_id': propertyId,
      'amount': amount,
      'notes': notes,
      'booking_date': preferredDate?.toIso8601String(),
      'status': 'pending',
      'is_synced': 0,
      'created_at': DateTime.now().toIso8601String(),
    };

    // Save locally
    await _dbHelper.saveBooking(localBooking);

    // If online, sync immediately
    if (await _apiService.isConnected()) {
      try {
        final response = await _apiService.post(
          '/bookings',
          data: {
            'property_id': propertyId,
            'amount': amount,
            'notes': notes,
            'preferred_date': preferredDate?.toIso8601String(),
          },
        );

        final serverBooking =
            BookingModel.fromJson(response['data'] as Map<String, dynamic>);

        // Update local with server data
        await _dbHelper.updateBookingWithServerData(
          localId: localBooking['id'].toString(),
          serverBooking: response['data'] as Map<String, dynamic>,
        );

        return serverBooking;
      } catch (e) {
        // Queue for later sync
        await _dbHelper.addToSyncQueue(
          entityType: 'booking',
          entityId: localBooking['id'].toString(),
          action: 'create',
          data: localBooking,
        );
        return BookingModel.fromJson(localBooking);
      }
    } else {
      // Queue for later sync
      await _dbHelper.addToSyncQueue(
        entityType: 'booking',
        entityId: localBooking['id'].toString(),
        action: 'create',
        data: localBooking,
      );
      return BookingModel.fromJson(localBooking);
    }
  }

  /// Update booking
  Future<BookingModel> updateBooking(
    String bookingId, {
    String? notes,
    DateTime? preferredDate,
    String? status,
  }) async {
    final data = <String, dynamic>{};
    if (notes != null) data['notes'] = notes;
    if (preferredDate != null) {
      data['booking_date'] = preferredDate.toIso8601String();
    }
    if (status != null) data['status'] = status;

    // Update locally
    await _dbHelper.updateBooking(bookingId, data);

    // If online, sync
    if (await _apiService.isConnected()) {
      try {
        final response = await _apiService.put(
          '/bookings/$bookingId',
          data: data,
        );
        return BookingModel.fromJson(response['data'] as Map<String, dynamic>);
      } catch (e) {
        // Queue for later
        await _dbHelper.addToSyncQueue(
          entityType: 'booking',
          entityId: bookingId,
          action: 'update',
          data: data,
        );
      }
    } else {
      // Queue for later
      await _dbHelper.addToSyncQueue(
        entityType: 'booking',
        entityId: bookingId,
        action: 'update',
        data: data,
      );
    }

    final updated = await _dbHelper.getBookingById(bookingId);
    return BookingModel.fromJson(updated!);
  }

  /// Cancel booking
  Future<void> cancelBooking(String bookingId, {String? reason}) async {
    // Update locally
    await _dbHelper.updateBooking(
      bookingId,
      {'status': 'cancelled', 'cancellation_reason': reason},
    );

    // If online, sync
    if (await _apiService.isConnected()) {
      try {
        await _apiService.delete('/bookings/$bookingId');
      } catch (e) {
        // Queue for later
        await _dbHelper.addToSyncQueue(
          entityType: 'booking',
          entityId: bookingId,
          action: 'delete',
          data: {'reason': reason},
        );
      }
    } else {
      // Queue for later
      await _dbHelper.addToSyncQueue(
        entityType: 'booking',
        entityId: bookingId,
        action: 'delete',
        data: {'reason': reason},
      );
    }
  }

  /// Get booking statistics
  Future<Map<String, int>> getBookingStats() async {
    return await _dbHelper.getBookingStats();
  }

  /// Get pending bookings count
  Future<int> getPendingCount() async {
    return await _dbHelper.getPendingBookingsCount();
  }

  /// Sync pending bookings
  Future<SyncResult> syncPendingBookings() async {
    final pending = await _dbHelper.getUnsyncedBookings();

    if (pending.isEmpty) {
      return SyncResult(success: true, message: 'No pending bookings');
    }

    if (!(await _apiService.isConnected())) {
      return SyncResult(
        success: false,
        message: 'No internet connection',
      );
    }

    int successCount = 0;
    int failCount = 0;

    for (final booking in pending) {
      try {
        final serverId = booking['server_id'];
        if (serverId == null) {
          // New booking - create on server
          final response = await _apiService.post(
            '/bookings',
            data: booking,
          );
          final serverBooking = response['data'] as Map<String, dynamic>;
          await _dbHelper.markBookingAsSynced(
            booking['id'].toString(),
            serverBooking['id'] as int,
          );
        } else {
          // Existing booking - update
          await _apiService.put(
            '/bookings/$serverId',
            data: booking,
          );
          await _dbHelper.markBookingAsSynced(
            booking['id'].toString(),
            serverId as int,
          );
        }
        successCount++;
      } catch (e) {
        failCount++;
        await _dbHelper.incrementBookingRetryCount(booking['id'].toString());
      }
    }

    return SyncResult(
      success: failCount == 0,
      message: 'Synced $successCount bookings, $failCount failed',
    );
  }
}

/// Sync result
class SyncResult {
  final bool success;
  final String message;

  SyncResult({required this.success, required this.message});
}

/// Provider for BookingRepository
final bookingRepositoryProvider = Provider<BookingRepository>((ref) {
  final apiService = ApiService();
  final dbHelper = DatabaseHelper();
  return BookingRepository(apiService, dbHelper);
});

/// Provider for my bookings
final myBookingsProvider = FutureProvider.autoDispose
    .family<List<BookingModel>, Map<String, dynamic>?>((
  ref,
  filters,
) async {
  final repository = ref.watch(bookingRepositoryProvider);
  return await repository.getMyBookings(
    status: filters?['status'] as String?,
    fromDate: filters?['from_date'] != null
        ? DateTime.tryParse(filters?['from_date'] as String)
        : null,
    toDate: filters?['to_date'] != null
        ? DateTime.tryParse(filters?['to_date'] as String)
        : null,
  );
});

/// Provider for booking details
final bookingDetailsProvider = FutureProvider.autoDispose
    .family<BookingModel?, String>((ref, bookingId) async {
  final repository = ref.watch(bookingRepositoryProvider);
  return await repository.getBookingById(bookingId);
});

/// Provider for booking stats
final bookingStatsProvider =
    FutureProvider.autoDispose<Map<String, int>>((ref) async {
  final repository = ref.watch(bookingRepositoryProvider);
  return await repository.getBookingStats();
});

/// Provider for pending bookings count
final pendingBookingsCountProvider =
    FutureProvider.autoDispose<int>((ref) async {
  final repository = ref.watch(bookingRepositoryProvider);
  return await repository.getPendingCount();
});

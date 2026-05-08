import '../../core/services/api_service.dart';
import '../../core/services/database_helper.dart';
import '../models/property_model.dart';

/// Property Repository - Offline-First Pattern
/// Uses SQLite for local cache, API for server sync
class PropertyRepository {
  final ApiService _apiService;
  final DatabaseHelper _dbHelper;

  PropertyRepository(this._apiService, this._dbHelper);

  /// Get all properties (Offline-First)
  /// 1. Returns cached data immediately
  /// 2. Fetches from API in background
  /// 3. Updates cache and notifies listeners
  Future<List<PropertyModel>> getProperties({
    String? type,
    String? status,
    double? minPrice,
    double? maxPrice,
    String? location,
    String? sortBy,
  }) async {
    // Build query filters
    final filters = <String, dynamic>{};
    if (type != null) filters['type'] = type;
    if (status != null) filters['status'] = status;
    if (minPrice != null) filters['min_price'] = minPrice;
    if (maxPrice != null) filters['max_price'] = maxPrice;
    if (location != null) filters['location'] = location;
    if (sortBy != null) filters['sort_by'] = sortBy;

    // Get from local DB first (fast)
    final localData = await _dbHelper.getProperties(
      type: type,
      status: status,
      minPrice: minPrice,
      maxPrice: maxPrice,
      location: location,
    );
    final localProperties = localData.map((e) => PropertyModel.fromJson(e)).toList();

    // If online, fetch from API and update cache
    if (await _apiService.isConnected()) {
      try {
        final response = await _apiService.getProperties(filters: filters);
        final apiProperties = response
            .map((json) => PropertyModel.fromJson(json))
            .toList();

        // Update local cache in background
        await _dbHelper.saveProperties(response);

        return apiProperties;
      } catch (e) {
        // If API fails, return cached data
        return localProperties;
      }
    }

    return localProperties;
  }

  /// Get single property by ID
  Future<PropertyModel?> getPropertyById(String id) async {
    // Try local first
    final local = await _dbHelper.getPropertyById(id);
    if (local != null) {
      return PropertyModel.fromJson(local);
    }

    // If online, fetch fresh data
    if (await _apiService.isConnected()) {
      try {
        final response = await _apiService.get('/properties/$id');
        final property = PropertyModel.fromJson(response['data'] as Map<String, dynamic>);

        // Update cache
        await _dbHelper.saveProperty(response['data'] as Map<String, dynamic>);

        return property;
      } catch (e) {
        return local != null ? PropertyModel.fromJson(local) : null;
      }
    }

    return local != null ? PropertyModel.fromJson(local) : null;
  }

  /// Search properties
  Future<List<PropertyModel>> searchProperties(String query) async {
    // Search local DB first
    final localData = await _dbHelper.searchProperties(query);
    final localResults = localData.map((e) => PropertyModel.fromJson(e)).toList();

    // If online, search API
    if (await _apiService.isConnected()) {
      try {
        final response = await _apiService.get(
          '/properties/search',
          queryParameters: {'q': query},
        );
        final results = (response['data'] as List)
            .map((json) => PropertyModel.fromJson(json as Map<String, dynamic>))
            .toList();

        // Cache results
        await _dbHelper.saveProperties(response['data'] as List<Map<String, dynamic>>);
        return results;
      } catch (e) {
        return localResults;
      }
    }

    return localResults;
  }

  /// Toggle favorite status
  Future<bool> toggleFavorite(String propertyId) async {
    // Get current property
    final propertyData = await _dbHelper.getPropertyById(propertyId);
    if (propertyData == null) return false;

    final currentFavorite = propertyData['is_favorite'] == 1;
    final newStatus = !currentFavorite;

    // Toggle locally
    await _dbHelper.updatePropertyFavorite(propertyId, newStatus);

    // If online, sync with server
    if (await _apiService.isConnected()) {
      try {
        await _apiService.post(
          '/properties/$propertyId/favorite',
          data: {'is_favorite': newStatus},
        );
      } catch (e) {
        // Queue for later sync
        await _dbHelper.addToSyncQueue(
          entityType: 'property_favorite',
          entityId: propertyId,
          action: 'update',
          data: {'is_favorite': newStatus},
        );
      }
    } else {
      // Queue for later
      await _dbHelper.addToSyncQueue(
        entityType: 'property_favorite',
        entityId: propertyId,
        action: 'update',
        data: {'is_favorite': newStatus},
      );
    }

    return newStatus;
  }

  /// Get favorite properties
  Future<List<PropertyModel>> getFavorites() async {
    final localData = await _dbHelper.getFavoriteProperties();
    return localData.map((e) => PropertyModel.fromJson(e)).toList();
  }

  /// Get properties by colony
  Future<List<PropertyModel>> getPropertiesByColony(String colonyId) async {
    // Try local first
    final localData = await _dbHelper.getPropertiesByColony(colonyId);
    final local = localData.map((e) => PropertyModel.fromJson(e)).toList();

    // If online, fetch from API
    if (await _apiService.isConnected()) {
      try {
        final response = await _apiService.get(
          '/colonies/$colonyId/properties',
        );
        final properties = (response['data'] as List)
            .map((json) => PropertyModel.fromJson(json as Map<String, dynamic>))
            .toList();

        await _dbHelper.saveProperties(response['data'] as List<Map<String, dynamic>>);
        return properties;
      } catch (e) {
        return local;
      }
    }

    return local;
  }

  /// Get similar properties
  Future<List<PropertyModel>> getSimilarProperties(
    String propertyId, {
    int limit = 5,
  }) async {
    if (await _apiService.isConnected()) {
      try {
        final response = await _apiService.get(
          '/properties/$propertyId/similar',
          queryParameters: {'limit': limit},
        );
        return (response['data'] as List)
            .map((json) => PropertyModel.fromJson(json as Map<String, dynamic>))
            .toList();
      } catch (e) {
        return [];
      }
    }
    return [];
  }

  /// Refresh properties (force API call)
  Future<List<PropertyModel>> refreshProperties() async {
    if (await _apiService.isConnected()) {
      try {
        final response = await _apiService.getProperties();
        final properties = response
            .map((json) => PropertyModel.fromJson(json))
            .toList();

        await _dbHelper.clearAndSaveProperties(response);
        return properties;
      } catch (e) {
        throw Exception('Failed to refresh: $e');
      }
    }
    throw Exception('No internet connection');
  }

  /// Get property count (cached)
  Future<int> getPropertyCount() async {
    return await _dbHelper.getPropertyCount();
  }

  /// Filter properties locally
  Future<List<PropertyModel>> filterProperties({
    List<String>? types,
    List<String>? statuses,
    double? minPrice,
    double? maxPrice,
    double? minArea,
    double? maxArea,
    String? facing,
  }) async {
    final localData = await _dbHelper.filterProperties(
      types: types,
      statuses: statuses,
      minPrice: minPrice,
      maxPrice: maxPrice,
      minArea: minArea,
      maxArea: maxArea,
    );
    return localData.map((e) => PropertyModel.fromJson(e)).toList();
  }
}



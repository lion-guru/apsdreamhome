import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:sqflite/sqflite.dart';
import '../../core/services/database_helper.dart';
import '../../core/services/sync_service.dart';
import '../../core/constants/app_constants.dart';
import '../../data/models/property_model.dart';

final propertyProvider = StateNotifierProvider<PropertyNotifier, AsyncValue<List<Property>>>((ref) {
  return PropertyNotifier();
});

class PropertyNotifier extends StateNotifier<AsyncValue<List<Property>>> {
  PropertyNotifier() : super(const AsyncValue.loading()) {
    _loadProperties();
  }
  
  Future<void> _loadProperties() async {
    try {
      final properties = await DatabaseHelper.query(AppConstants.propertiesTable);
      final propertyList = properties.map((json) => Property.fromJson(json)).toList();
      state = AsyncValue.data(propertyList);
    } catch (e) {
      state = AsyncValue.error(e, StackTrace.current);
    }
  }
  
  Future<void> refreshProperties() async {
    state = const AsyncValue.loading();
    await _loadProperties();
  }
  
  Future<void> updatePropertyStatus(Property property, String newStatus) async {
    try {
      // Update local database
      await DatabaseHelper.update(
        AppConstants.propertiesTable,
        {
          'status': newStatus,
          'updated_at': DateTime.now().toIso8601String(),
        },
        where: 'property_id = ?',
        whereArgs: [property.propertyId],
      );
      
      // Queue for sync
      await SyncService().queueChange(
        AppConstants.propertiesTable,
        property.propertyId,
        'update',
        {
          'property_id': property.propertyId,
          'status': newStatus,
        },
      );
      
      // Update local state
      final currentList = state.value ?? [];
      final updatedList = currentList.map((p) {
        if (p.propertyId == property.propertyId) {
          return p.copyWith(status: newStatus);
        }
        return p;
      }).toList();
      
      state = AsyncValue.data(updatedList);
    } catch (e) {
      state = AsyncValue.error(e, StackTrace.current);
    }
  }
  
  Future<void> addProperty(Property property) async {
    try {
      // Add to local database
      await DatabaseHelper.insert(AppConstants.propertiesTable, {
        'property_id': property.propertyId,
        'title': property.title,
        'description': property.description,
        'type': property.type,
        'price': property.price,
        'size': property.size,
        'location': property.location,
        'status': property.status,
        'image_url': property.imageUrl,
        'created_at': property.createdAt,
        'updated_at': property.updatedAt,
      });
      
      // Queue for sync
      await SyncService().queueChange(
        AppConstants.propertiesTable,
        property.propertyId,
        'create',
        property.toJson(),
      );
      
      // Update local state
      final currentList = state.value ?? [];
      final updatedList = [...currentList, property];
      state = AsyncValue.data(updatedList);
    } catch (e) {
      state = AsyncValue.error(e, StackTrace.current);
    }
  }
}

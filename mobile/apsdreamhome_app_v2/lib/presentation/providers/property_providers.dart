import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_riverpod/legacy.dart';
import '../../data/repositories/property_repository.dart';
import '../../data/models/property_model.dart';

/// Unified App Providers
/// Exports all providers for easy access

// Property Providers
final propertyRepositoryProvider = Provider<PropertyRepository>((ref) {
  throw UnimplementedError(
      'Override this in main.dart with actual implementation');
});

final propertiesProvider = FutureProvider.autoDispose
    .family<List<PropertyModel>, Map<String, dynamic>?>((ref, filters) async {
  final repository = ref.watch(propertyRepositoryProvider);
  return await repository.getProperties(
    type: filters?['type'] as String?,
    status: filters?['status'] as String?,
    minPrice: filters?['min_price'] as double?,
    maxPrice: filters?['max_price'] as double?,
    location: filters?['location'] as String?,
    sortBy: filters?['sort_by'] as String?,
  );
});

final propertyDetailsProvider = FutureProvider.autoDispose
    .family<PropertyModel?, String>((ref, propertyId) async {
  final repository = ref.watch(propertyRepositoryProvider);
  return await repository.getPropertyById(propertyId);
});

final favoritesProvider =
    FutureProvider.autoDispose<List<PropertyModel>>((ref) async {
  final repository = ref.watch(propertyRepositoryProvider);
  return await repository.getFavorites();
});

final propertySearchProvider = FutureProvider.autoDispose
    .family<List<PropertyModel>, String>((ref, query) async {
  final repository = ref.watch(propertyRepositoryProvider);
  return await repository.searchProperties(query);
});

final colonyPropertiesProvider = FutureProvider.autoDispose
    .family<List<PropertyModel>, String>((ref, colonyId) async {
  final repository = ref.watch(propertyRepositoryProvider);
  return await repository.getPropertiesByColony(colonyId);
});

/// Favorites State for UI
class FavoritesUIState {
  final List<PropertyModel> favorites;
  final bool isLoading;
  final String? error;
  final bool isOffline;

  FavoritesUIState({
    this.favorites = const [],
    this.isLoading = false,
    this.error,
    this.isOffline = false,
  });

  FavoritesUIState copyWith({
    List<PropertyModel>? favorites,
    bool? isLoading,
    String? error,
    bool? isOffline,
  }) {
    return FavoritesUIState(
      favorites: favorites ?? this.favorites,
      isLoading: isLoading ?? this.isLoading,
      error: error,
      isOffline: isOffline ?? this.isOffline,
    );
  }

  bool isFavorite(String propertyId) {
    return favorites.any((p) => p.id == propertyId);
  }
}

/// Favorites Notifier with Repository
class FavoritesNotifier extends StateNotifier<FavoritesUIState> {
  final PropertyRepository _repository;

  FavoritesNotifier(this._repository) : super(FavoritesUIState()) {
    loadFavorites();
  }

  Future<void> loadFavorites() async {
    state = state.copyWith(isLoading: true);
    try {
      final favorites = await _repository.getFavorites();
      state = FavoritesUIState(
        favorites: favorites,
        isLoading: false,
        isOffline: false,
      );
    } catch (e) {
      state = FavoritesUIState(
        isLoading: false,
        error: 'Failed to load favorites: $e',
        isOffline: true,
      );
    }
  }

  Future<void> toggleFavorite(PropertyModel property) async {
    final isCurrentlyFavorite = state.isFavorite(property.id);

    // Optimistic update
    final updatedFavorites = isCurrentlyFavorite
        ? state.favorites.where((p) => p.id != property.id).toList()
        : [...state.favorites, property];

    state = state.copyWith(favorites: updatedFavorites);

    try {
      await _repository.toggleFavorite(property.id);
    } catch (e) {
      // Revert on error
      state = state.copyWith(favorites: state.favorites);
      state = state.copyWith(error: 'Failed to update favorite: $e');
    }
  }

  Future<void> refresh() async {
    await loadFavorites();
  }
}

/// Favorites notifier provider
final favoritesNotifierProvider =
    StateNotifierProvider<FavoritesNotifier, FavoritesUIState>((ref) {
  final repository = ref.watch(propertyRepositoryProvider);
  return FavoritesNotifier(repository);
});

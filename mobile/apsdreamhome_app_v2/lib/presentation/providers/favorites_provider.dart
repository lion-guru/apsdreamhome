import 'package:flutter_riverpod/flutter_riverpod.dart';
// import 'package:hive/hive.dart'; // Incompatible with Flutter 3.41.6
// import 'package:hive_flutter/hive_flutter.dart'; // Incompatible with Flutter 3.41.6
import '../../data/models/plot_model.dart';

/// Favorites State
class FavoritesState {
  final List<PlotModel> favorites;
  final bool isLoading;
  final String? error;

  FavoritesState({
    this.favorites = const [],
    this.isLoading = false,
    this.error,
  });

  FavoritesState copyWith({
    List<PlotModel>? favorites,
    bool? isLoading,
    String? error,
  }) {
    return FavoritesState(
      favorites: favorites ?? this.favorites,
      isLoading: isLoading ?? this.isLoading,
      error: error,
    );
  }

  bool isFavorite(String plotId) {
    return favorites.any((plot) => plot.id == plotId);
  }
}

/// Favorites Notifier
class FavoritesNotifier extends StateNotifier<FavoritesState> {
  FavoritesNotifier() : super(FavoritesState()) {
    _loadFavorites();
  }

  /// Load favorites from local storage
  Future<void> _loadFavorites() async {
    state = state.copyWith(isLoading: true);

    try {
      // Hive offline storage disabled - using in-memory only
      state = state.copyWith(isLoading: false);
    } catch (e) {
      state = state.copyWith(
          isLoading: false, error: 'Failed to load favorites: $e');
    }
  }

  /// Add to favorites
  Future<void> addToFavorites(PlotModel plot) async {
    if (state.isFavorite(plot.id)) return;

    final updatedFavorites = [...state.favorites, plot];
    await _saveFavorites(updatedFavorites);
    state = state.copyWith(favorites: updatedFavorites);
  }

  /// Remove from favorites
  Future<void> removeFromFavorites(String plotId) async {
    final updatedFavorites =
        state.favorites.where((p) => p.id != plotId).toList();
    await _saveFavorites(updatedFavorites);
    state = state.copyWith(favorites: updatedFavorites);
  }

  /// Toggle favorite status
  Future<void> toggleFavorite(PlotModel plot) async {
    if (state.isFavorite(plot.id)) {
      await removeFromFavorites(plot.id);
    } else {
      await addToFavorites(plot);
    }
  }

  /// Save to local storage
  Future<void> _saveFavorites(List<PlotModel> favorites) async {
    try {
      // Hive offline storage disabled - using in-memory only
    } catch (e) {
      state = state.copyWith(error: 'Failed to save favorites: $e');
    }
  }

  /// Clear all favorites
  Future<void> clearFavorites() async {
    // Hive offline storage disabled - using in-memory only
    state = state.copyWith(favorites: []);
  }

  /// Sync with backend (for cross-device sync)
  Future<void> syncWithBackend(String userId) async {
    // TODO: Implement API call to sync favorites
    // POST /api/v1/users/favorites/sync
  }
}

/// Favorites Provider
final favoritesProvider =
    StateNotifierProvider<FavoritesNotifier, FavoritesState>((ref) {
  return FavoritesNotifier();
});

/// Helper to check if a plot is favorite
final isFavoriteProvider = Provider.family<bool, String>((ref, plotId) {
  return ref.watch(favoritesProvider).isFavorite(plotId);
});

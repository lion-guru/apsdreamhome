import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_riverpod/legacy.dart';
import '../../data/models/plot_model.dart';
import '../../core/services/api_service.dart';
import '../../core/constants/app_constants.dart';

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
  final ApiService _api = ApiService();
  bool _loaded = false;

  FavoritesNotifier() : super(FavoritesState());

  /// Load favorites from backend API
  Future<void> loadFavorites() async {
    if (_loaded) return;
    state = state.copyWith(isLoading: true);
    try {
      final res = await _api.get(AppConstants.favoritesEndpoint);
      if (res['success'] == true && res['data'] != null) {
        final list = res['data'] as List;
        final plots = list
            .map((e) => PlotModel.fromJson(e as Map<String, dynamic>))
            .toList();
        state = state.copyWith(favorites: plots, isLoading: false);
        _loaded = true;
      } else {
        state = state.copyWith(isLoading: false);
      }
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: 'Failed to load favorites: $e',
      );
    }
  }

  /// Add to favorites (calls backend API)
  Future<void> addToFavorites(PlotModel plot) async {
    if (state.isFavorite(plot.id)) return;
    try {
      await _api.post(
        AppConstants.favoritesEndpoint,
        data: {'property_id': plot.id},
      );
      final updated = [...state.favorites, plot];
      state = state.copyWith(favorites: updated);
    } catch (e) {
      state = state.copyWith(error: 'Failed to add favorite: $e');
    }
  }

  /// Remove from favorites (calls backend API)
  Future<void> removeFromFavorites(String plotId) async {
    try {
      await _api.delete('${AppConstants.favoritesEndpoint}/$plotId');
      final updated = state.favorites.where((p) => p.id != plotId).toList();
      state = state.copyWith(favorites: updated);
    } catch (e) {
      state = state.copyWith(error: 'Failed to remove favorite: $e');
    }
  }

  /// Toggle favorite status (calls backend toggle)
  Future<void> toggleFavorite(PlotModel plot) async {
    try {
      final res = await _api.post('/properties/${plot.id}/favorite');
      if (res['success'] == true) {
        final isFav = res['data']?['is_favorited'] == true;
        if (isFav) {
          if (!state.isFavorite(plot.id)) {
            state = state.copyWith(favorites: [...state.favorites, plot]);
          }
        } else {
          state = state.copyWith(
            favorites: state.favorites.where((p) => p.id != plot.id).toList(),
          );
        }
      }
    } catch (e) {
      // Fall back to local toggle if API fails
      if (state.isFavorite(plot.id)) {
        state = state.copyWith(
          favorites: state.favorites.where((p) => p.id != plot.id).toList(),
        );
      } else {
        state = state.copyWith(favorites: [...state.favorites, plot]);
      }
    }
  }

  /// Clear all favorites
  Future<void> clearFavorites() async {
    state = state.copyWith(favorites: []);
    _loaded = false;
  }

  /// Sync with backend (reloads from API)
  Future<void> syncWithBackend(String userId) async {
    _loaded = false;
    await loadFavorites();
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

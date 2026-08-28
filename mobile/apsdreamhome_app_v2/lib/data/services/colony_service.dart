import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/constants/app_constants.dart';
import '../../core/services/api_service.dart';
import '../../core/utils/logger.dart';
import '../models/colony_model.dart';
import '../models/plot_model.dart';

/// Colony Service - Colony & Plot Management (MySQL-first, REST API)
class ColonyService {
  final ApiService _api = ApiService();

  /// Get All Colonies via REST API
  Future<List<ColonyModel>> getColonies({
    String? status,
    String? state,
    String? district,
    bool forceRefresh = false,
  }) async {
    try {
      final params = <String, dynamic>{};
      if (status != null) params['status'] = status;
      if (state != null) params['state'] = state;
      if (district != null) params['district'] = district;

      final response = await _api.get(
        AppConstants.coloniesEndpoint,
        queryParameters: params,
      );

      AppLogger.info('[Colonies] Raw response keys: ${response.keys.toList()}');
      AppLogger.info('[Colonies] success=${response['success']}, data type=${response['data']?.runtimeType}');

      final data = response['data'] ?? [];
      if (data is! List) {
        AppLogger.error('[Colonies] data is not List, it is ${data.runtimeType}: $data', null, StackTrace.current);
        return [];
      }

      AppLogger.info('[Colonies] data.length=${data.length}, first item type=${data.isNotEmpty ? data.first.runtimeType : "empty"}');

      final colonies = <ColonyModel>[];
      for (var i = 0; i < data.length; i++) {
        try {
          final item = data[i] as Map<String, dynamic>;
          colonies.add(ColonyModel.fromJson(item));
        } catch (e, st) {
          AppLogger.error('[Colonies] Parse error at index $i', e, st);
          AppLogger.error('[Colonies] Item: ${data[i]}', null, null);
        }
      }

      AppLogger.info('[Colonies] Parsed ${colonies.length}/${data.length} colonies');
      return colonies;
    } catch (e, stackTrace) {
      AppLogger.error('[Colonies] FATAL Error fetching colonies', e, stackTrace);
      return [];
    }
  }

  /// Get Colony by ID via REST API
  Future<ColonyModel?> getColonyById(String colonyId) async {
    try {
      final response = await _api.get('${AppConstants.coloniesEndpoint}/$colonyId');
      final data = response['data'];
      if (data != null) {
        return ColonyModel.fromJson(data as Map<String, dynamic>);
      }
      return null;
    } catch (e, stackTrace) {
      AppLogger.error('Error fetching colony: $colonyId', e, stackTrace);
      return null;
    }
  }

  /// Get Colony Statistics via REST API
  Future<Map<String, dynamic>> getColonyStats(String colonyId) async {
    try {
      final response = await _api.get(
        '${AppConstants.coloniesEndpoint}/$colonyId/stats',
      );
      return (response['data'] as Map?)?.cast<String, dynamic>() ?? {};
    } catch (e, stackTrace) {
      AppLogger.error('Error getting colony stats: $colonyId', e, stackTrace);
      return {
        'total': 0,
        'available': 0,
        'hold': 0,
        'booked': 0,
        'sold': 0,
      };
    }
  }

  /// Get Plots by Colony via REST API
  Future<List<PlotModel>> getPlotsByColony(
    String colonyId, {
    PlotFilter? filter,
  }) async {
    try {
      final params = <String, dynamic>{};
      if (filter?.status != null) params['status'] = filter!.status;

      final response = await _api.get(
        '${AppConstants.coloniesEndpoint}/$colonyId/plots',
        queryParameters: params,
      );

      final data = response['data'] ?? [];
      var plots = (data as List).map((json) {
        return PlotModel.fromJson(json as Map<String, dynamic>);
      }).toList();

      // Apply additional filters in memory
      if (filter != null) {
        plots = _applyPlotFilters(plots, filter);
      }

      return plots;
    } catch (e, stackTrace) {
      AppLogger.error('Error fetching plots for colony: $colonyId', e, stackTrace);
      return [];
    }
  }

  /// Get Plot by ID via REST API
  Future<PlotModel?> getPlotById(String plotId) async {
    try {
      final response = await _api.get('${AppConstants.plotsEndpoint}/$plotId');
      final data = response['data'];
      if (data != null) {
        return PlotModel.fromJson(data as Map<String, dynamic>);
      }
      return null;
    } catch (e, stackTrace) {
      AppLogger.error('Error fetching plot: $plotId', e, stackTrace);
      return null;
    }
  }

  /// Get Available Plots via REST API
  Future<List<PlotModel>> getAvailablePlots(String colonyId) async {
    try {
      final response = await _api.get(
        '${AppConstants.coloniesEndpoint}/$colonyId/plots',
        queryParameters: {'status': 'available'},
      );

      final data = response['data'] ?? [];
      return (data as List).map((json) {
        return PlotModel.fromJson(json as Map<String, dynamic>);
      }).toList();
    } catch (e, stackTrace) {
      AppLogger.error('Error fetching available plots', e, stackTrace);
      return [];
    }
  }

  /// Hold Plot (Temporary Reservation) via REST API
  Future<bool> holdPlot({
    required String plotId,
    required String userId,
    Duration? holdDuration,
  }) async {
    try {
      final response = await _api.post(
        '${AppConstants.plotsEndpoint}/$plotId/hold',
        data: {
          'user_id': userId,
          'hold_duration_hours': (holdDuration ?? const Duration(hours: 24)).inHours,
        },
      );

      final success = (response['success'] ?? false) as bool;
      if (success) {
        AppLogger.info('Plot $plotId held by $userId');
      }
      return success;
    } catch (e, stackTrace) {
      AppLogger.error('Error holding plot: $plotId', e, stackTrace);
      return false;
    }
  }

  /// Release Plot Hold via REST API
  Future<bool> releasePlotHold(String plotId) async {
    try {
      final response = await _api.post(
        '${AppConstants.plotsEndpoint}/$plotId/release',
      );

      final success = (response['success'] ?? false) as bool;
      if (success) {
        AppLogger.info('Plot $plotId hold released');
      }
      return success;
    } catch (e, stackTrace) {
      AppLogger.error('Error releasing plot hold: $plotId', e, stackTrace);
      return false;
    }
  }

  /// Calculate Plot Price with Premiums
  double calculatePlotPrice(PlotModel plot, double basePricePerSqft) {
    double price = plot.areaSqft * basePricePerSqft;

    if (plot.isCorner == true) {
      price += price * 0.10; // 10% corner premium
    }

    if (plot.isParkFacing == true) {
      price += price * 0.05; // 5% park facing premium
    }

    if (plot.isMainRoadFacing == true) {
      price += price * 0.08; // 8% main road premium
    }

    return price;
  }

  /// Search Colonies via REST API
  Future<List<ColonyModel>> searchColonies(String query) async {
    try {
      final response = await _api.get(
        '${AppConstants.coloniesEndpoint}/search',
        queryParameters: {'q': query},
      );

      final data = response['data'] ?? [];
      return (data as List).map((json) {
        return ColonyModel.fromJson(json as Map<String, dynamic>);
      }).toList();
    } catch (e, stackTrace) {
      AppLogger.error('Error searching colonies', e, stackTrace);
      return [];
    }
  }

  /// Helper Methods
  List<PlotModel> _applyPlotFilters(List<PlotModel> plots, PlotFilter filter) {
    return plots.where((plot) {
      // Facing filter
      if (filter.facings != null && filter.facings!.isNotEmpty && !filter.facings!.contains(plot.facing)) {
        return false;
      }

      // Area filter
      if (filter.minArea != null && plot.areaSqft < filter.minArea!) {
        return false;
      }
      if (filter.maxArea != null && plot.areaSqft > filter.maxArea!) {
        return false;
      }

      // Price filter
      final price = plot.totalPrice;
      if (filter.minPrice != null && price < filter.minPrice!) {
        return false;
      }
      if (filter.maxPrice != null && price > filter.maxPrice!) {
        return false;
      }

      // Corner filter
      if (filter.cornerOnly == true && plot.isCorner != true) {
        return false;
      }

      // Park facing filter
      if (filter.parkFacingOnly == true && plot.isParkFacing != true) {
        return false;
      }

      return true;
    }).toList();
  }
}

// Colony Service Provider
final colonyServiceProvider = Provider<ColonyService>((ref) => ColonyService());

final coloniesProvider = FutureProvider<List<ColonyModel>>((ref) async {
  final colonyService = ref.watch(colonyServiceProvider);
  return colonyService.getColonies();
});

final colonyProvider = FutureProvider.family<ColonyModel?, String>(
  (ref, colonyId) async {
    final colonyService = ref.watch(colonyServiceProvider);
    return colonyService.getColonyById(colonyId);
  },
);

final plotsProvider = FutureProvider.family<List<PlotModel>, String>(
  (ref, colonyId) async {
    final colonyService = ref.watch(colonyServiceProvider);
    return colonyService.getPlotsByColony(colonyId);
  },
);

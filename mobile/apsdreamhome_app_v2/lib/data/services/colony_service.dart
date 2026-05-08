import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
// import 'package:hive/hive.dart'; // Incompatible with Flutter 3.41.6

import '../../core/constants/app_constants.dart';
import '../../core/utils/logger.dart';
import '../models/colony_model.dart';
import '../models/plot_model.dart';

/// Colony Service - Colony & Plot Management
class ColonyService {
  final FirebaseFirestore _firestore = FirebaseFirestore.instance;
  // final Box _cacheBox = Hive.box(AppConstants.cacheBoxName); // Disabled due to Flutter 3.41.6 incompatibility
  
  // Collection References
  CollectionReference get _colonies => 
      _firestore.collection(AppConstants.coloniesCollection);
  CollectionReference get _plots => 
      _firestore.collection(AppConstants.plotsCollection);
  
  /// Get All Colonies (with caching)
  Future<List<ColonyModel>> getColonies({
    String? status,
    String? state,
    String? district,
    bool forceRefresh = false,
  }) async {
    try {
      // Check cache first - Disabled due to Flutter 3.41.6 incompatibility
      // if (!forceRefresh) {
      //   final cached = _getCachedColonies();
      //   if (cached != null && cached.isNotEmpty) {
      //     AppLogger.debug('Returning cached colonies');
      //     return cached;
      //   }
      // }
      
      // Build query
      var query = _colonies.where('status', whereIn: [
        'active',
        'launching',
        'upcoming',
      ]).orderBy('createdAt', descending: true);
      
      if (status != null) {
        query = _colonies.where('status', isEqualTo: status);
      }
      
      if (state != null) {
        query = query.where('state', isEqualTo: state);
      }
      
      if (district != null) {
        query = query.where('district', isEqualTo: district);
      }
      
      final snapshot = await query.get();
      
      final colonies = snapshot.docs.map((doc) {
        return ColonyModel.fromJson({
          'id': doc.id,
          ...doc.data() as Map<String, dynamic>,
        });
      }).toList();
      
      // Cache the results - Disabled due to Flutter 3.41.6 incompatibility
      // _cacheColonies(colonies);
      
      AppLogger.info('Fetched ${colonies.length} colonies');
      return colonies;
    } catch (e, stackTrace) {
      AppLogger.error('Error fetching colonies', e, stackTrace);
      // Return cached data if available - Disabled due to Flutter 3.41.6 incompatibility
      // return _getCachedColonies() ?? [];
      return [];
    }
  }
  
  /// Get Colony by ID
  Future<ColonyModel?> getColonyById(String colonyId) async {
    try {
      // Check cache first - Disabled due to Flutter 3.41.6 incompatibility
      // final cached = _getCachedColony(colonyId);
      // if (cached != null) return cached;
      
      final doc = await _colonies.doc(colonyId).get();
      
      if (doc.exists) {
        final colony = ColonyModel.fromJson({
          'id': doc.id,
          ...doc.data() as Map<String, dynamic>,
        });
        
        // Cache it - Disabled due to Flutter 3.41.6 incompatibility
        // _cacheColony(colony);
        return colony;
      }
      return null;
    } catch (e, stackTrace) {
      AppLogger.error('Error fetching colony: $colonyId', e, stackTrace);
      // return _getCachedColony(colonyId); // Disabled due to Flutter 3.41.6 incompatibility
      return null;
    }
  }
  
  /// Get Colonies Stream (Real-time)
  Stream<List<ColonyModel>> getColoniesStream() {
    return _colonies
        .where('status', whereIn: ['active', 'launching'])
        .orderBy('createdAt', descending: true)
        .snapshots()
        .map((snapshot) {
          final colonies = snapshot.docs.map((doc) {
            return ColonyModel.fromJson({
              'id': doc.id,
              ...doc.data() as Map<String, dynamic>,
            });
          }).toList();
          
          // Update cache - Disabled due to Flutter 3.41.6 incompatibility
          // _cacheColonies(colonies);
          
          return colonies;
        });
  }
  
  /// Get Colony Statistics
  Future<Map<String, dynamic>> getColonyStats(String colonyId) async {
    try {
      final plots = await _plots
          .where('colonyId', isEqualTo: colonyId)
          .get();
      
      int available = 0;
      int hold = 0;
      int booked = 0;
      int sold = 0;
      
      for (var doc in plots.docs) {
        final status = (doc.data() as Map<String, dynamic>)['status'] as String?;
        switch (status) {
          case 'available':
            available++;
            break;
          case 'hold':
            hold++;
            break;
          case 'booked':
            booked++;
            break;
          case 'sold':
            sold++;
            break;
        }
      }
      
      return {
        'total': plots.docs.length,
        'available': available,
        'hold': hold,
        'booked': booked,
        'sold': sold,
      };
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
  
  /// Get Plots by Colony
  Future<List<PlotModel>> getPlotsByColony(
    String colonyId, {
    PlotFilter? filter,
  }) async {
    try {
      var query = _plots
          .where('colonyId', isEqualTo: colonyId)
          .orderBy('plotNumber');
      
      if (filter?.status != null) {
        query = query.where('status', isEqualTo: filter!.status);
      }
      
      final snapshot = await query.get();
      
      var plots = snapshot.docs.map((doc) {
        return PlotModel.fromJson({
          'id': doc.id,
          ...doc.data() as Map<String, dynamic>,
        });
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
  
  /// Get Plot by ID
  Future<PlotModel?> getPlotById(String plotId) async {
    try {
      final doc = await _plots.doc(plotId).get();
      
      if (doc.exists) {
        return PlotModel.fromJson({
          'id': doc.id,
          ...doc.data() as Map<String, dynamic>,
        });
      }
      return null;
    } catch (e, stackTrace) {
      AppLogger.error('Error fetching plot: $plotId', e, stackTrace);
      return null;
    }
  }
  
  /// Get Available Plots
  Future<List<PlotModel>> getAvailablePlots(String colonyId) async {
    try {
      final snapshot = await _plots
          .where('colonyId', isEqualTo: colonyId)
          .where('status', isEqualTo: 'available')
          .orderBy('plotNumber')
          .get();
      
      return snapshot.docs.map((doc) {
        return PlotModel.fromJson({
          'id': doc.id,
          ...doc.data() as Map<String, dynamic>,
        });
      }).toList();
    } catch (e, stackTrace) {
      AppLogger.error('Error fetching available plots', e, stackTrace);
      return [];
    }
  }
  
  /// Hold Plot (Temporary Reservation)
  Future<bool> holdPlot({
    required String plotId,
    required String userId,
    Duration? holdDuration,
  }) async {
    try {
      final plot = await getPlotById(plotId);
      if (plot == null || !plot.isAvailable) {
        return false;
      }
      
      final holdUntil = DateTime.now().add(holdDuration ?? const Duration(hours: 24));
      
      await _plots.doc(plotId).update({
        'status': 'hold',
        'holdBy': userId,
        'holdUntil': holdUntil.toIso8601String(),
        'updatedAt': DateTime.now().toIso8601String(),
      });
      
      AppLogger.info('Plot $plotId held by $userId until $holdUntil');
      return true;
    } catch (e, stackTrace) {
      AppLogger.error('Error holding plot: $plotId', e, stackTrace);
      return false;
    }
  }
  
  /// Release Plot Hold
  Future<bool> releasePlotHold(String plotId) async {
    try {
      await _plots.doc(plotId).update({
        'status': 'available',
        'holdBy': null,
        'holdUntil': null,
        'updatedAt': DateTime.now().toIso8601String(),
      });
      
      AppLogger.info('Plot $plotId hold released');
      return true;
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
  
  /// Get Plots Stream (Real-time updates)
  Stream<List<PlotModel>> getPlotsStream(String colonyId) {
    return _plots
        .where('colonyId', isEqualTo: colonyId)
        .orderBy('plotNumber')
        .snapshots()
        .map((snapshot) {
          return snapshot.docs.map((doc) {
            return PlotModel.fromJson({
              'id': doc.id,
              ...doc.data() as Map<String, dynamic>,
            });
          }).toList();
        });
  }
  
  /// Search Colonies
  Future<List<ColonyModel>> searchColonies(String query) async {
    try {
      final snapshot = await _colonies
          .where('status', whereIn: ['active', 'launching'])
          .get();
      
      final colonies = snapshot.docs.map((doc) {
        return ColonyModel.fromJson({
          'id': doc.id,
          ...doc.data() as Map<String, dynamic>,
        });
      }).where((colony) {
        final searchLower = query.toLowerCase();
        return colony.name.toLowerCase().contains(searchLower) ||
            colony.location.toLowerCase().contains(searchLower) ||
            colony.district.toLowerCase().contains(searchLower) ||
            colony.state.toLowerCase().contains(searchLower);
      }).toList();
      
      return colonies;
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
  
  /// Cache Methods - Disabled due to Flutter 3.41.6 incompatibility
  /*
  List<ColonyModel>? _getCachedColonies() {
    final cached = _cacheBox.get('colonies');
    if (cached != null) {
      return (cached as List)
          .map((json) => ColonyModel.fromJson(Map<String, dynamic>.from(json as Map)))
          .toList();
    }
    return null;
  }
  
  void _cacheColonies(List<ColonyModel> colonies) {
    _cacheBox.put('colonies', colonies.map((c) => c.toJson()).toList());
    _cacheBox.put('colonies_cache_time', DateTime.now().toIso8601String());
  }
  
  ColonyModel? _getCachedColony(String colonyId) {
    final cached = _cacheBox.get('colony_$colonyId');
    if (cached != null) {
      return ColonyModel.fromJson(Map<String, dynamic>.from(cached as Map));
    }
    return null;
  }
  
  void _cacheColony(ColonyModel colony) {
    _cacheBox.put('colony_${colony.id}', colony.toJson());
  }
  */
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

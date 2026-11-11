import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'offline_indicator.dart';

/// Base class for offline-first pages
abstract class OfflineFirstPage<T> extends ConsumerStatefulWidget {
  const OfflineFirstPage({super.key});
  
  /// Load data from local cache
  Future<T> loadFromCache();
  
  /// Load data from server
  Future<T> fetchFromServer();
  
  /// Build the UI with loaded data
  Widget buildContent(BuildContext context, T data);
  
  /// Optional: loading widget
  Widget? buildLoading();
  
  /// Optional: error widget
  Widget? buildError(Object error);
  
  /// Optional: empty state widget
  Widget? buildEmpty();
  
  /// Whether to auto-refresh on reconnect
  bool get refreshOnReconnect => true;
  
  /// Cache timeout
  Duration get cacheTimeout => const Duration(minutes: 5);
}

abstract class _OfflineFirstPageState<T, W extends OfflineFirstPage<T>> 
    extends ConsumerState<W> {
  T? _data;
  bool _isLoading = false;
  Object? _error;
  bool _isOnline = true;
  StreamSubscription? _connectivitySubscription;
  
  @override
  void initState() {
    super.initState();
    _initConnectivity();
    _loadData();
  }
  
  void _initConnectivity() {
    final connectivity = ref.read(connectivityProvider.stream);
    _connectivitySubscription = connectivity.listen((results) {
      final wasOnline = _isOnline;
      _isOnline = results.isNotEmpty && !results.contains(ConnectivityResult.none);
      
      if (mounted && !wasOnline && _isOnline && widget.refreshOnReconnect) {
        _refreshData();
      }
      
      if (mounted) setState(() => _isOnline = _isOnline);
    });
    
    // Initial check
    Connectivity().checkConnectivity().then((results) {
      if (mounted) {
        setState(() => _isOnline = results.isNotEmpty && !results.contains(ConnectivityResult.none));
      }
    });
  }
  
  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    
    try {
      // Try to load from cache first
      final cachedData = await widget.loadFromCache();
      if (mounted && cachedData != null) {
        setState(() {
          _data = cachedData;
          _error = null;
        });
      }
      
      // If online, try to fetch fresh data
      if (_isOnline) {
        await _refreshData();
      }
    } catch (e) {
      if (mounted) setState(() => _error = e);
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }
  
  Future<void> _refreshData() async {
    if (!_isOnline || _isLoading) return;
    
    try {
      final freshData = await widget.fetchFromServer();
      if (mounted) {
        setState(() {
          _data = freshData;
          _error = null;
        });
      }
    } catch (e) {
      if (mounted) setState(() => _error = e);
    }
  }
  
  Future<void> _refreshData() async {
    if (!_isOnline || _isLoading) return;
    
    try {
      final freshData = await widget.fetchFromServer();
      if (mounted) {
        setState(() {
          _data = freshData;
          _error = null;
        });
      }
    } catch (e) {
      if (mounted) setState(() => _error = e);
    }
  }
  
  @override
  void dispose() {
    _connectivitySubscription?.cancel();
    super.dispose();
  }
  
  @override
  Widget build(BuildContext context) {
    final isOnline = ref.watch(isOnlineProvider);
    
    return Scaffold(
      body: Stack(
        children: [
          // Main content
          _buildBody(),
          
          // Offline indicator
          if (!_isOnline)
            Positioned(
              bottom: 0,
              left: 0,
              right: 0,
              child: Material(
                elevation: 8,
                child: Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
                  color: Colors.orange.withValues(alpha: 0.9),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.wifi_off, color: Colors.white, size: 20),
                      const SizedBox(width: 12),
                      const Text(
                        'You are offline. Changes will sync when online.',
                        style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ],
        );
  }
  
  Widget _buildBody() {
    if (_isLoading && _data == null) {
      return widget.buildLoading() ?? const Center(child: CircularProgressIndicator());
    }
    
    if (_error != null && _data == null) {
      return widget.buildError(_error!) ?? 
          Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.error_outline, color: Colors.red, size: 48),
                const SizedBox(height: 16),
                Text(
                  'Error: $_error',
                  textAlign: TextAlign.center,
                  style: const TextStyle(color: Colors.red),
                ),
                const SizedBox(height: 16),
                ElevatedButton.icon(
                  onPressed: () => setState(() => _loadData()),
                  icon: const Icon(Icons.refresh),
                  label: const Text('Retry'),
                ),
              ],
            );
        );
      }
      
      if (_data == null) {
        return widget.buildEmpty() ?? 
            const Center(child: Text('No data available'));
      }
      
      return widget.buildContent(context, _data!);
    }
    
    @override
    void setState(VoidCallback fn) {
      if (mounted) super.setState(fn);
    }
}
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:connectivity_plus/connectivity_plus.dart';

final connectivityProvider = StreamProvider<List<ConnectivityResult>>((ref) {
  return Connectivity().onConnectivityChanged;
});

final isOnlineProvider = Provider<bool>((ref) {
  final connectivityAsync = ref.watch(connectivityProvider);
  return connectivityAsync.maybeWhen(
    data: (results) => results.isNotEmpty && !results.contains(ConnectivityResult.none),
    orElse: () => false,
  );
});

final connectivityControllerProvider = Provider<ConnectivityController>((ref) {
  return ConnectivityController(ref);
});

class ConnectivityController {
  final Ref _ref;
  ConnectivityController(this._ref);

  bool get isOnline {
    final connectivity = _ref.read(connectivityProvider);
    return connectivity.maybeWhen(
      data: (results) => results.isNotEmpty && !results.contains(ConnectivityResult.none),
      orElse: () => false,
    );
  }

  Future<void> waitForConnection({Duration? timeout}) async {
    if (isOnline) return;
    await _ref.read(connectivityProvider.future).timeout(
      timeout ?? const Duration(seconds: 30),
      onTimeout: () => <ConnectivityResult>[ConnectivityResult.none],
    );
  }
}
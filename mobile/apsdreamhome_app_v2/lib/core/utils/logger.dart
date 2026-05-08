import 'dart:developer' as developer;

class AppLogger {
  static bool _isDebugMode = true;
  
  static void setup({bool debugMode = true}) {
    _isDebugMode = debugMode;
  }
  
  static void info(String message) {
    if (_isDebugMode) {
      developer.log(
        message,
        name: 'INFO',
        time: DateTime.now(),
      );
    }
  }
  
  static void debug(String message) {
    if (_isDebugMode) {
      developer.log(
        message,
        name: 'DEBUG',
        time: DateTime.now(),
      );
    }
  }
  
  static void warning(String message) {
    developer.log(
      message,
      name: 'WARNING',
      time: DateTime.now(),
    );
  }
  
  static void error(String message, [Object? error, StackTrace? stackTrace]) {
    developer.log(
      message,
      name: 'ERROR',
      time: DateTime.now(),
      error: error,
      stackTrace: stackTrace,
    );
  }
}

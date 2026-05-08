abstract class Failure {
  const Failure(this.message);
  
  final String message;
}

class ServerFailure extends Failure {
  const ServerFailure(super.message);
}

class NetworkFailure extends Failure {
  const NetworkFailure(super.message);
}

class DatabaseFailure extends Failure {
  const DatabaseFailure(super.message);
}

class ValidationFailure extends Failure {
  const ValidationFailure(super.message);
}

class AuthenticationFailure extends Failure {
  const AuthenticationFailure(super.message);
}

class SyncFailure extends Failure {
  const SyncFailure(super.message);
}

class PermissionFailure extends Failure {
  const PermissionFailure(super.message);
}

class UnknownFailure extends Failure {
  const UnknownFailure(super.message);
}

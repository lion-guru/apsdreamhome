import 'package:flutter_test/flutter_test.dart';

import 'package:apsdreamhome_app_v2/core/router/app_router.dart';
import 'package:apsdreamhome_app_v2/data/models/user_model.dart';

User _user(String role) => User(
      userId: '1',
      name: 'RBAC Probe',
      email: 'rbac@example.com',
      rank: role,
      role: role,
      target: 0,
      createdAt: '2026-11-16',
      updatedAt: '2026-11-16',
    );

void main() {
  group('defaultRouteForRole', () {
    test('admin lands on /admin', () =>
        expect(defaultRouteForRole(_user('admin')), '/admin'));
    test('super_admin lands on /admin', () =>
        expect(defaultRouteForRole(_user('super_admin')), '/admin'));
    test('manager lands on /admin', () =>
        expect(defaultRouteForRole(_user('manager')), '/admin'));
    test('associate lands on /associate/dashboard', () =>
        expect(defaultRouteForRole(_user('associate')), '/associate/dashboard'));
    test('agent lands on /agent/dashboard', () =>
        expect(defaultRouteForRole(_user('agent')), '/agent/dashboard'));
    test('employee lands on /employee/dashboard', () =>
        expect(defaultRouteForRole(_user('employee')), '/employee/dashboard'));
    test('telecaller lands on /employee/dashboard', () =>
        expect(defaultRouteForRole(_user('telecaller')), '/employee/dashboard'));
    test('customer lands on /customer-dashboard', () =>
        expect(defaultRouteForRole(_user('customer')), '/customer-dashboard'));
    test('null user lands on /home', () =>
        expect(defaultRouteForRole(null), '/home'));
  });

  group('User role helpers (RBAC inputs)', () {
    test('admin sees isAdmin=true, isCustomer=false', () {
      final u = _user('admin');
      expect(u.isAdmin, isTrue);
      expect(u.isCustomer, isFalse);
      expect(u.isAssociate, isFalse);
    });
    test('associate does not inherit admin access', () {
      final u = _user('associate');
      expect(u.isAdmin, isFalse);
      expect(u.isAssociate, isTrue);
    });
    test('telecaller is classified as employee', () {
      final u = _user('telecaller');
      expect(u.isTelecaller, isTrue);
      expect(u.isEmployee, isTrue);
      expect(u.isAdmin, isFalse);
    });
  });
}

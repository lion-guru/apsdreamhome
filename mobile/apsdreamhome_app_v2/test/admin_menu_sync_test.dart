import 'package:flutter_test/flutter_test.dart';
import 'package:flutter/material.dart';

import 'package:apsdreamhome_app_v2/core/services/admin_menu_service.dart';

AdminMenuEntry entry(String url, [String section = 'crm', int order = 0]) =>
    AdminMenuEntry(
      id: '1',
      name: 'Item for $url',
      url: url,
      section: section,
      orderIndex: order,
    );

void main() {
  group('filterAppMenuEntries', () {
    test('keeps only implemented screens', () {
      final entries = [
        entry('/admin/crm'),
        entry('/admin/ai-calling/dashboard'), // not an app screen
        entry('/admin/bookings', 'sales', 5),
      ];
      final filtered = filterAppMenuEntries(entries);
      expect(filtered.map((e) => e.url), ['/admin/crm', '/admin/bookings']);
    });

    test('preserves server (DB) order', () {
      final entries = [
        entry('/admin/bookings', 'sales', 1),
        entry('/admin/crm', 'crm', 2),
        entry('/admin', 'main', 0),
      ];
      expect(filterAppMenuEntries(entries).map((e) => e.url), ['/admin/bookings', '/admin/crm', '/admin']);
    });

    test('drops everything when nothing the app implements is present', () {
      final entries = [entry('/admin/ivr-flows'), entry('/admin/rag')];
      expect(filterAppMenuEntries(entries), isEmpty);
    });
  });

  group('entry mappings', () {
    test('aliases resolve to app routes and icons', () {
      expect(routeForEntry(entry('/admin/crm')), '/admin/crm');
      expect(iconForEntry(entry('/admin/crm')), isA<IconData>());
      expect(routeForEntry(entry('/admin/payout-batches', 'finance')), '/admin/payout-batches');
    });

    test('section labels are human readable', () {
      expect(adminSectionLabel('ai_tech'), 'AI & TECH');
      expect(adminSectionLabel('township_ops'), 'TOWNSHIP OPS');
      expect(adminSectionLabel('mysterious'), 'MYSTERIOUS');
    });
  });
}

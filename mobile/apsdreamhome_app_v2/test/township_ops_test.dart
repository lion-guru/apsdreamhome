import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'package:apsdreamhome_app_v2/presentation/pages/customer/township_operations_page.dart';
import 'package:apsdreamhome_app_v2/presentation/pages/customer/township_utility_detail_page.dart';
import 'package:apsdreamhome_app_v2/presentation/pages/customer/solar_subsidy_page.dart';

void main() {
  group('Township Operations Widget Tests', () {
    testWidgets('TownshipOperationsPage renders without throwing', (tester) async {
      await tester.pumpWidget(
        const ProviderScope(
          child: MaterialApp(
            home: TownshipOperationsPage(),
          ),
        ),
      );

      // Bounded pumps — the page issues a network call that fails in tests.
      await tester.pump();
      await tester.pump(const Duration(milliseconds: 200));

      expect(tester.takeException(), isNull);
      expect(find.text('Township Operations'), findsOneWidget);
    });

    testWidgets('TownshipUtilityDetailPage renders without throwing', (tester) async {
      await tester.pumpWidget(
        const ProviderScope(
          child: MaterialApp(
            home: TownshipUtilityDetailPage(type: 'water'),
          ),
        ),
      );

      await tester.pump();
      await tester.pump(const Duration(milliseconds: 200));

      expect(tester.takeException(), isNull);
      // Either the label from the API or the fallback 'Utility Desk'.
      expect(find.byType(AppBar), findsOneWidget);
    });

    testWidgets('SolarSubsidyPage renders calculator and runs math', (tester) async {
      await tester.pumpWidget(
        const ProviderScope(
          child: MaterialApp(
            home: SolarSubsidyPage(),
          ),
        ),
      );

      await tester.pump();
      await tester.pump(const Duration(milliseconds: 200));

      expect(tester.takeException(), isNull);
      expect(find.text('PM Surya Ghar Solar'), findsOneWidget);
      expect(find.text('Solar Subsidy Calculator'), findsOneWidget);
      expect(find.text('Calculate Savings'), findsOneWidget);
      // Result card should appear for the default inputs (plot 2000, bill 2500).
      expect(find.textContaining('kW system recommended'), findsOneWidget);
      expect(find.text('Net payable'), findsOneWidget);
    });
  });
}

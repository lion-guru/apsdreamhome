import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'package:apsdreamhome_app_v2/presentation/pages/customer/customer_passbook_page.dart';
import 'package:apsdreamhome_app_v2/presentation/pages/customer/plot_transfers_page.dart';
import 'package:apsdreamhome_app_v2/presentation/pages/customer/loan_foreclosure_page.dart';
import 'package:apsdreamhome_app_v2/presentation/pages/customer/nri_desk_page.dart';
import 'package:apsdreamhome_app_v2/presentation/pages/customer/customer_journey_page.dart';
import 'package:apsdreamhome_app_v2/presentation/pages/associate/associate_id_card_page.dart';
import 'package:apsdreamhome_app_v2/presentation/pages/associate/associate_posters_page.dart';
import 'package:apsdreamhome_app_v2/presentation/pages/associate/associate_tds_page.dart';
import 'package:apsdreamhome_app_v2/presentation/pages/associate/associate_bonanza_page.dart';
import 'package:apsdreamhome_app_v2/presentation/pages/agent/agent_collection_page.dart';

// Deep Scan Feature Pages
import 'package:apsdreamhome_app_v2/presentation/pages/customer/registry_desk_page.dart';
import 'package:apsdreamhome_app_v2/presentation/pages/customer/colony_timelapse_page.dart';
import 'package:apsdreamhome_app_v2/presentation/pages/customer/allotment_acceptance_page.dart';
import 'package:apsdreamhome_app_v2/presentation/pages/associate/associate_advances_page.dart';
import 'package:apsdreamhome_app_v2/presentation/pages/associate/associate_documents_page.dart';
import 'package:apsdreamhome_app_v2/presentation/pages/customer/notification_preferences_page.dart';
import 'package:apsdreamhome_app_v2/presentation/pages/agent/cash_drawer_page.dart';
import 'package:apsdreamhome_app_v2/presentation/pages/property/property_auctions_page.dart';
import 'package:apsdreamhome_app_v2/presentation/pages/customer/farmer_desk_page.dart';
import 'package:apsdreamhome_app_v2/presentation/pages/customer/meeting_scheduler_page.dart';

void main() {

  group('Web Parity Customer Feature Widget Tests', () {
    testWidgets('CustomerPassbookPage renders without throwing', (tester) async {
      await tester.pumpWidget(
        const ProviderScope(
          child: MaterialApp(home: CustomerPassbookPage()),
        ),
      );
      await tester.pump();
      await tester.pump(const Duration(milliseconds: 200));

      expect(tester.takeException(), isNull);
      expect(find.byType(Scaffold), findsOneWidget);
      expect(find.byType(AppBar), findsOneWidget);
    });

    testWidgets('PlotTransfersPage renders without throwing', (tester) async {
      await tester.pumpWidget(
        const ProviderScope(
          child: MaterialApp(home: PlotTransfersPage()),
        ),
      );
      await tester.pump();
      await tester.pump(const Duration(milliseconds: 200));

      expect(tester.takeException(), isNull);
      expect(find.byType(Scaffold), findsOneWidget);
      expect(find.byType(AppBar), findsOneWidget);
    });

    testWidgets('LoanForeclosurePage renders without throwing', (tester) async {
      await tester.pumpWidget(
        const ProviderScope(
          child: MaterialApp(home: LoanForeclosurePage()),
        ),
      );
      await tester.pump();
      await tester.pump(const Duration(milliseconds: 200));

      expect(tester.takeException(), isNull);
      expect(find.byType(Scaffold), findsOneWidget);
      expect(find.byType(AppBar), findsOneWidget);
    });

    testWidgets('NriDeskPage renders without throwing', (tester) async {
      await tester.pumpWidget(
        const ProviderScope(
          child: MaterialApp(home: NriDeskPage()),
        ),
      );
      await tester.pump();
      await tester.pump(const Duration(milliseconds: 200));

      expect(tester.takeException(), isNull);
      expect(find.byType(Scaffold), findsOneWidget);
      expect(find.byType(AppBar), findsOneWidget);
    });

    testWidgets('CustomerJourneyPage renders without throwing', (tester) async {
      await tester.pumpWidget(
        const ProviderScope(
          child: MaterialApp(home: CustomerJourneyPage(bookingId: 1)),
        ),
      );
      await tester.pump();
      await tester.pump(const Duration(milliseconds: 200));

      expect(tester.takeException(), isNull);
      expect(find.byType(Scaffold), findsOneWidget);
      expect(find.byType(AppBar), findsOneWidget);
    });

    testWidgets('RegistryDeskPage renders without throwing', (tester) async {
      await tester.pumpWidget(
        const ProviderScope(
          child: MaterialApp(home: RegistryDeskPage()),
        ),
      );
      await tester.pump();
      await tester.pump(const Duration(milliseconds: 200));

      expect(tester.takeException(), isNull);
      expect(find.byType(Scaffold), findsOneWidget);
      expect(find.byType(AppBar), findsOneWidget);
    });

    testWidgets('ColonyTimelapsePage renders without throwing', (tester) async {
      await tester.pumpWidget(
        const ProviderScope(
          child: MaterialApp(home: ColonyTimelapsePage(colonyId: 1)),
        ),
      );
      await tester.pump();
      await tester.pump(const Duration(milliseconds: 200));

      expect(tester.takeException(), isNull);
      expect(find.byType(Scaffold), findsOneWidget);
      expect(find.byType(AppBar), findsOneWidget);
    });

    testWidgets('AllotmentAcceptancePage renders without throwing', (tester) async {
      await tester.pumpWidget(
        const ProviderScope(
          child: MaterialApp(home: AllotmentAcceptancePage()),
        ),
      );
      await tester.pump();
      await tester.pump(const Duration(milliseconds: 200));

      expect(tester.takeException(), isNull);
      expect(find.byType(Scaffold), findsOneWidget);
      expect(find.byType(AppBar), findsOneWidget);
    });

    testWidgets('NotificationPreferencesPage renders without throwing', (tester) async {
      await tester.pumpWidget(
        const ProviderScope(
          child: MaterialApp(home: NotificationPreferencesPage()),
        ),
      );
      await tester.pump();
      await tester.pump(const Duration(milliseconds: 200));

      expect(tester.takeException(), isNull);
      expect(find.byType(Scaffold), findsOneWidget);
      expect(find.byType(AppBar), findsOneWidget);
    });
  });

  group('Web Parity Associate & Agent Feature Widget Tests', () {
    testWidgets('AssociateIdCardPage renders without throwing', (tester) async {
      await tester.pumpWidget(
        const ProviderScope(
          child: MaterialApp(home: AssociateIdCardPage()),
        ),
      );
      await tester.pump();
      await tester.pump(const Duration(milliseconds: 200));

      expect(tester.takeException(), isNull);
      expect(find.byType(Scaffold), findsOneWidget);
      expect(find.byType(AppBar), findsOneWidget);
    });

    testWidgets('AssociatePostersPage renders without throwing', (tester) async {
      await tester.pumpWidget(
        const ProviderScope(
          child: MaterialApp(home: AssociatePostersPage()),
        ),
      );
      await tester.pump();
      await tester.pump(const Duration(milliseconds: 200));

      expect(tester.takeException(), isNull);
      expect(find.byType(Scaffold), findsOneWidget);
      expect(find.byType(AppBar), findsOneWidget);
    });

    testWidgets('AssociateTdsPage renders without throwing', (tester) async {
      await tester.pumpWidget(
        const ProviderScope(
          child: MaterialApp(home: AssociateTdsPage()),
        ),
      );
      await tester.pump();
      await tester.pump(const Duration(milliseconds: 200));

      expect(tester.takeException(), isNull);
      expect(find.byType(Scaffold), findsOneWidget);
      expect(find.byType(AppBar), findsOneWidget);
    });

    testWidgets('AssociateBonanzaPage renders without throwing', (tester) async {
      await tester.pumpWidget(
        const ProviderScope(
          child: MaterialApp(home: AssociateBonanzaPage()),
        ),
      );
      await tester.pump();
      await tester.pump(const Duration(milliseconds: 200));

      expect(tester.takeException(), isNull);
      expect(find.byType(Scaffold), findsOneWidget);
      expect(find.byType(AppBar), findsOneWidget);
    });

    testWidgets('AgentCollectionPage renders without throwing', (tester) async {
      await tester.pumpWidget(
        const ProviderScope(
          child: MaterialApp(home: AgentCollectionPage()),
        ),
      );
      await tester.pump();
      await tester.pump(const Duration(milliseconds: 200));

      expect(tester.takeException(), isNull);
      expect(find.byType(Scaffold), findsOneWidget);
      expect(find.byType(AppBar), findsOneWidget);
    });

    testWidgets('AssociateAdvancesPage renders without throwing', (tester) async {
      await tester.pumpWidget(
        const ProviderScope(
          child: MaterialApp(home: AssociateAdvancesPage()),
        ),
      );
      await tester.pump();
      await tester.pump(const Duration(milliseconds: 200));

      expect(tester.takeException(), isNull);
      expect(find.byType(Scaffold), findsOneWidget);
      expect(find.byType(AppBar), findsOneWidget);
    });

    testWidgets('AssociateDocumentsPage renders without throwing', (tester) async {
      await tester.pumpWidget(
        const ProviderScope(
          child: MaterialApp(home: AssociateDocumentsPage()),
        ),
      );
      await tester.pump();
      await tester.pump(const Duration(milliseconds: 200));

      expect(tester.takeException(), isNull);
      expect(find.byType(Scaffold), findsOneWidget);
      expect(find.byType(AppBar), findsOneWidget);
    });

    testWidgets('CashDrawerPage renders without throwing', (tester) async {
      await tester.pumpWidget(
        const ProviderScope(
          child: MaterialApp(home: CashDrawerPage()),
        ),
      );
      await tester.pump();
      await tester.pump(const Duration(milliseconds: 200));

      expect(tester.takeException(), isNull);
      expect(find.byType(Scaffold), findsOneWidget);
      expect(find.byType(AppBar), findsOneWidget);
    });

    testWidgets('PropertyAuctionsPage renders without throwing', (tester) async {
      await tester.pumpWidget(
        const ProviderScope(
          child: MaterialApp(home: PropertyAuctionsPage()),
        ),
      );
      await tester.pump();
      await tester.pump(const Duration(milliseconds: 200));

      expect(tester.takeException(), isNull);
      expect(find.byType(Scaffold), findsOneWidget);
      expect(find.byType(AppBar), findsOneWidget);
    });

    testWidgets('FarmerDeskPage renders without throwing', (tester) async {
      await tester.pumpWidget(
        const ProviderScope(
          child: MaterialApp(home: FarmerDeskPage()),
        ),
      );
      await tester.pump();
      await tester.pump(const Duration(milliseconds: 200));

      expect(tester.takeException(), isNull);
      expect(find.byType(Scaffold), findsOneWidget);
      expect(find.byType(AppBar), findsOneWidget);
    });

    testWidgets('MeetingSchedulerPage renders without throwing', (tester) async {
      await tester.pumpWidget(
        const ProviderScope(
          child: MaterialApp(home: MeetingSchedulerPage()),
        ),
      );
      await tester.pump();
      await tester.pump(const Duration(milliseconds: 200));

      expect(tester.takeException(), isNull);
      expect(find.byType(Scaffold), findsOneWidget);
      expect(find.byType(AppBar), findsOneWidget);
    });
  });
}


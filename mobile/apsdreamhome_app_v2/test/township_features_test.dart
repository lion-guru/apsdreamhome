import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'package:apsdreamhome_app_v2/presentation/pages/customer/book_site_visit_page.dart';
import 'package:apsdreamhome_app_v2/presentation/pages/customer/maintenance_dues_page.dart';
import 'package:apsdreamhome_app_v2/presentation/pages/customer/township_grievances_page.dart';

void main() {
  group('Township Features Widget Tests', () {
    testWidgets('BookSiteVisitPage renders township options and pickup toggle', (tester) async {
      await tester.pumpWidget(
        const ProviderScope(
          child: MaterialApp(
            home: BookSiteVisitPage(),
          ),
        ),
      );

      // Verify header and sections exist
      expect(find.text('Book Free Site Visit'), findsOneWidget);
      expect(find.text('1. Select Township / Colony'), findsOneWidget);
      expect(find.text('2. Preferred Date & Time Slot'), findsOneWidget);
      expect(find.text('3. AC Vehicle Pickup Options'), findsOneWidget);

      // Verify complimentary car pickup banner
      expect(find.text('Complimentary AC Car Pickup'), findsOneWidget);

      // Verify submit button
      expect(find.text('Confirm Free Site Visit'), findsOneWidget);
    });

    testWidgets('MaintenanceDuesPage renders title and summary cards', (tester) async {
      await tester.pumpWidget(
        ProviderScope(
          overrides: [
            maintenanceDuesProvider.overrideWith((ref) async => {
              'summary': {
                'total_pending': 2400.0,
                'total_paid': 12000.0,
                'bills_count': 2,
              },
              'dues': [
                {
                  'id': 1,
                  'bill_number': 'MNT-2026-001',
                  'month_year': 'October 2026',
                  'amount_due': 1200.0,
                  'paid_amount': 0.0,
                  'status': 'unpaid',
                  'due_date': '31 Oct 2026',
                  'colony_name': 'Suryoday Colony',
                }
              ]
            }),
          ],
          child: const MaterialApp(
            home: MaintenanceDuesPage(),
          ),
        ),
      );

      await tester.pumpAndSettle();

      expect(find.text('Society Maintenance Dues'), findsOneWidget);
      expect(find.text('Maintenance Invoices'), findsOneWidget);
      expect(find.text('Total Pending'), findsOneWidget);
      expect(find.text('Total Paid'), findsOneWidget);
      expect(find.text('Period: October 2026'), findsOneWidget);
      expect(find.text('PENDING'), findsOneWidget);
    });

    testWidgets('TownshipGrievancesPage renders resident helpdesk UI', (tester) async {
      await tester.pumpWidget(
        ProviderScope(
          overrides: [
            townshipGrievancesProvider.overrideWith((ref) async => [
              {
                'id': 101,
                'ticket_token': 'GRV-TKT-101',
                'title': 'Street light replacement near Plot 42',
                'category': 'street_lighting',
                'category_label': 'Street Lighting & Power',
                'priority': 'high',
                'status': 'in_progress',
                'created_at': '2026-10-21 10:00:00',
              }
            ]),
          ],
          child: const MaterialApp(
            home: TownshipGrievancesPage(),
          ),
        ),
      );

      await tester.pumpAndSettle();

      expect(find.text('Township Resident Helpdesk'), findsOneWidget);
      expect(find.text('Lodge Complaint'), findsOneWidget);
      expect(find.text('Street light replacement near Plot 42'), findsOneWidget);
      expect(find.text('IN PROGRESS'), findsOneWidget);
    });
  });
}

// Test for offline indicator widgets
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter/material.dart';

import 'package:apsdreamhome_app_v2/core/widgets/offline_indicator.dart';

void main() {
  group('OfflineIndicator', () {
    testWidgets('renders online indicator', (WidgetTester tester) async {
      await tester.pumpWidget(
        ProviderScope(
          overrides: [
            isOnlineProvider.overrideWithValue(true),
          ],
          child: const MaterialApp(
            home: Scaffold(
              body: OfflineIndicator(),
            ),
          ),
        ),
      );

      expect(find.byIcon(Icons.wifi), findsOneWidget);
      expect(find.text('Online'), findsOneWidget);
    });

    testWidgets('renders offline indicator', (WidgetTester tester) async {
      await tester.pumpWidget(
        ProviderScope(
          overrides: [
            isOnlineProvider.overrideWithValue(false),
          ],
          child: const MaterialApp(
            home: Scaffold(
              body: OfflineIndicator(),
            ),
          ),
        ),
      );

      expect(find.byIcon(Icons.wifi_off), findsOneWidget);
      expect(find.text('Offline'), findsOneWidget);
    });

    testWidgets('shows compact mode without text', (WidgetTester tester) async {
      await tester.pumpWidget(
        ProviderScope(
          overrides: [
            isOnlineProvider.overrideWithValue(true),
          ],
          child: const MaterialApp(
            home: Scaffold(
              body: OfflineIndicator(showText: false, showIcon: true),
            ),
          ),
        ),
      );

      expect(find.byIcon(Icons.wifi), findsOneWidget);
      expect(find.text('Online'), findsNothing);
    });
  });

  group('SyncIndicator', () {
    testWidgets('renders sync indicator', (WidgetTester tester) async {
      await tester.pumpWidget(
        ProviderScope(
          child: MaterialApp(
            home: Scaffold(
              body: SyncIndicator(
                onSyncPressed: () {},
              ),
            ),
          ),
        ),
      );

      expect(find.byIcon(Icons.sync_disabled), findsOneWidget);
      expect(find.text('Idle'), findsOneWidget);
    });

    testWidgets('renders sync indicator with sync pressed', (WidgetTester tester) async {
      bool syncCalled = false;
      await tester.pumpWidget(
        ProviderScope(
          overrides: [
            syncStatusProvider.overrideWith((ref) => SyncStatusNotifier()..setSyncing()),
          ],
          child: MaterialApp(
            home: Scaffold(
              body: SyncIndicator(
                onSyncPressed: () => syncCalled = true,
              ),
            ),
          ),
        ),
      );

      expect(find.byIcon(Icons.sync), findsOneWidget);
      expect(find.text('Syncing...'), findsOneWidget);
    });
  });

  group('OfflineDataBanner', () {
    testWidgets('shows banner when offline', (WidgetTester tester) async {
      await tester.pumpWidget(
        ProviderScope(
          overrides: [
            isOnlineProvider.overrideWithValue(false),
          ],
          child: const MaterialApp(
            home: Scaffold(
              body: OfflineDataBanner(),
            ),
          ),
        ),
      );

      expect(find.byIcon(Icons.wifi_off), findsOneWidget);
      expect(find.textContaining('Showing cached data'), findsOneWidget);
    });

    testWidgets('hides banner when online', (WidgetTester tester) async {
      await tester.pumpWidget(
        ProviderScope(
          overrides: [
            isOnlineProvider.overrideWithValue(true),
          ],
          child: const MaterialApp(
            home: Scaffold(
              body: OfflineDataBanner(),
            ),
          ),
        ),
      );

      expect(find.byIcon(Icons.wifi_off), findsNothing);
    });
  });

  group('OfflineDataBanner with trailing', () {
    testWidgets('shows trailing widget', (WidgetTester tester) async {
      await tester.pumpWidget(
        ProviderScope(
          overrides: [
            isOnlineProvider.overrideWithValue(false),
          ],
          child: const MaterialApp(
            home: Scaffold(
              body: OfflineDataBanner(
                trailing: Text('Retry'),
              ),
            ),
          ),
        ),
      );

      expect(find.text('Retry'), findsOneWidget);
    });
  });

  group('CompactSyncIndicator', () {
    testWidgets('renders compact sync indicator', (WidgetTester tester) async {
      await tester.pumpWidget(
        ProviderScope(
          child: MaterialApp(
            home: Scaffold(
              body: CompactSyncIndicator(
                onSyncPressed: () {},
              ),
            ),
          ),
        ),
      );

      expect(find.byIcon(Icons.wifi), findsOneWidget);
    });

    testWidgets('renders compact sync indicator when offline', (WidgetTester tester) async {
      await tester.pumpWidget(
        ProviderScope(
          overrides: [
            isOnlineProvider.overrideWithValue(false),
          ],
          child: MaterialApp(
            home: Scaffold(
              body: CompactSyncIndicator(
                onSyncPressed: () {},
              ),
            ),
          ),
        ),
      );

      expect(find.byIcon(Icons.wifi_off), findsOneWidget);
    });
  });

  group('FloatingSyncButton', () {
    testWidgets('renders floating sync button when online', (WidgetTester tester) async {
      await tester.pumpWidget(
        ProviderScope(
          child: MaterialApp(
            home: Scaffold(
              body: FloatingSyncButton(
                onPressed: () {},
              ),
            ),
          ),
        ),
      );

      expect(find.byIcon(Icons.sync), findsOneWidget);
      expect(find.text('Sync Now'), findsOneWidget);
    });

    testWidgets('hides floating sync button when offline', (WidgetTester tester) async {
      await tester.pumpWidget(
        ProviderScope(
          overrides: [
            isOnlineProvider.overrideWithValue(false),
          ],
          child: MaterialApp(
            home: Scaffold(
              body: FloatingSyncButton(
                onPressed: () {},
              ),
            ),
          ),
        ),
      );

      expect(find.byIcon(Icons.sync), findsNothing);
    });
  });
}
// Smoke test for the APS Dream Home app.
//
// NOTE: this test deliberately does NOT use `pumpAndSettle()`. The app ships
// indefinite animations on purpose — the splash screen has an indeterminate
// `CircularProgressIndicator` and the floating AI chatbot pulses on a loop —
// so a settle-based pump would time out forever even on a perfectly healthy
// build. We pump a bounded number of frames and assert the app booted without
// throwing instead.

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';

import 'package:apsdreamhome_app_v2/app.dart';

void main() {
  testWidgets('App boots and renders the splash screen', (WidgetTester tester) async {
    await tester.pumpWidget(
      const ProviderScope(
        child: APSDreamHomeApp(),
      ),
    );

    // Bounded pump: enough for the first frame + the start of the splash
    // animation, without waiting for an animation that never ends.
    await tester.pump();
    await tester.pump(const Duration(milliseconds: 200));

    // The app must not have thrown during boot.
    expect(tester.takeException(), isNull);

    // A MaterialApp (router) must be mounted.
    expect(find.byType(MaterialApp), findsOneWidget);

    // The splash screen brand must be on screen.
    expect(find.text('APS Dream Home'), findsOneWidget);

    // Let the splash auto-navigation timer (3s) fire so the test ends with no
    // pending timers — the framework fails the test while any timer is alive.
    await tester.pump(const Duration(seconds: 3));
    await tester.pump(const Duration(milliseconds: 100));

    expect(tester.takeException(), isNull);
  });
}

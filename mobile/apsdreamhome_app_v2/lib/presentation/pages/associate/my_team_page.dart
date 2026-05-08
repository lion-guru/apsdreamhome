import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class MyTeamPage extends ConsumerWidget {
  const MyTeamPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Scaffold(
      appBar: AppBar(title: const Text('My Team')),
      body: const Center(child: Text('My Team Page')),
    );
  }
}

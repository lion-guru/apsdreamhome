import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class PlotManagementPage extends ConsumerWidget {
  const PlotManagementPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Scaffold(
      appBar: AppBar(title: const Text('Plot Management')),
      body: const Center(child: Text('Plot Management Page')),
    );
  }
}

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class PayoutPage extends ConsumerWidget {
  const PayoutPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Scaffold(
      appBar: AppBar(title: const Text('Payout')),
      body: const Center(child: Text('Payout Page')),
    );
  }
}

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class CommissionApprovalsPage extends ConsumerWidget {
  const CommissionApprovalsPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Scaffold(
      appBar: AppBar(title: const Text('Commission Approvals')),
      body: const Center(child: Text('Commission Approvals Page')),
    );
  }
}

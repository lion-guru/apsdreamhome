import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class BookingApprovalsPage extends ConsumerWidget {
  const BookingApprovalsPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Scaffold(
      appBar: AppBar(title: const Text('Booking Approvals')),
      body: const Center(child: Text('Booking Approvals Page')),
    );
  }
}

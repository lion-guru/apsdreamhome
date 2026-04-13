import 'package:flutter/material.dart';

class PlotDetailPage extends StatelessWidget {
  final String plotId;

  const PlotDetailPage({
    super.key,
    required this.plotId,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Plot Details - $plotId'),
      ),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(
              Icons.landscape,
              size: 100,
              color: Colors.green,
            ),
            const SizedBox(height: 16),
            Text(
              'Plot ID: $plotId',
              style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            const Text(
              'Location: Paradise Residency Phase 1',
              style: TextStyle(fontSize: 16),
            ),
            const SizedBox(height: 24),
            ElevatedButton(
              onPressed: () {
                // TODO: Implement booking
              },
              child: const Text('Book Now'),
            ),
          ],
        ),
      ),
    );
  }
}

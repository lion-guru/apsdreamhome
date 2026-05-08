import 'package:flutter/material.dart';

class DocumentsPage extends StatelessWidget {
  const DocumentsPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('My Documents'),
      ),
      body: ListView.builder(
        itemCount: 5,
        padding: const EdgeInsets.all(16.0),
        itemBuilder: (context, index) {
          return Card(
            child: ListTile(
              leading: const Icon(Icons.picture_as_pdf, color: Colors.red),
              title: Text('Document ${index + 1}'),
              subtitle: Text('Added on: June ${index + 10}, 2024'),
              trailing: IconButton(
                icon: const Icon(Icons.download),
                onPressed: () {
                  // TODO: Implement download
                },
              ),
            ),
          );
        },
      ),
    );
  }
}

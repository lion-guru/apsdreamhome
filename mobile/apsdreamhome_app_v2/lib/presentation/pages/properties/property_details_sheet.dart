import 'package:flutter/material.dart';
import '../../../data/models/property_model.dart';

class PropertyDetailsSheet extends StatelessWidget {
  final PropertyModel property;

  const PropertyDetailsSheet({super.key, required this.property});

  @override
  Widget build(BuildContext context) {
    return DraggableScrollableSheet(
      initialChildSize: 0.5,
      minChildSize: 0.3,
      maxChildSize: 0.9,
      builder: (context, scrollController) {
        return Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Theme.of(context).scaffoldBackgroundColor,
            borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
          ),
          child: ListView(
            controller: scrollController,
            children: [
              Text(property.title,
                  style: const TextStyle(
                      fontSize: 20, fontWeight: FontWeight.bold)),
              const SizedBox(height: 8),
              Text(property.location,
                  style: TextStyle(color: Colors.grey.shade600)),
              const SizedBox(height: 16),
              Text('Price: ${property.formattedPrice}',
                  style: const TextStyle(
                      fontSize: 18, fontWeight: FontWeight.w600)),
              const SizedBox(height: 8),
              Text('Status: ${property.status}'),
              const SizedBox(height: 16),
              Text(property.description),
            ],
          ),
        );
      },
    );
  }
}

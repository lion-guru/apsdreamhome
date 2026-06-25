import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';

import '../../../core/theme/app_theme.dart';
import '../../../data/repositories/kyc_repository_provider.dart';

class PostPropertyPage extends ConsumerStatefulWidget {
  const PostPropertyPage({super.key});

  @override
  ConsumerState<PostPropertyPage> createState() => _PostPropertyPageState();
}

class _PostPropertyPageState extends ConsumerState<PostPropertyPage> {
  final _formKey = GlobalKey<FormState>();
  final _titleController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _priceController = TextEditingController();
  final _locationController = TextEditingController();
  final _areaController = TextEditingController();
  final _bedroomsController = TextEditingController();
  final _bathroomsController = TextEditingController();

  String _propertyType = 'Plot';
  String _listingType = 'Sell';
  String _furnishing = 'Unfurnished';
  bool _isSubmitting = false;
  List<File> _selectedImages = [];

  final _propertyTypes = ['Plot', 'House', 'Flat', 'Shop', 'Farmhouse', 'Commercial'];
  final _listingTypes = ['Sell', 'Rent'];
  final _furnishingTypes = ['Unfurnished', 'Semi-Furnished', 'Fully-Furnished'];

  @override
  void dispose() {
    _titleController.dispose();
    _descriptionController.dispose();
    _priceController.dispose();
    _locationController.dispose();
    _areaController.dispose();
    _bedroomsController.dispose();
    _bathroomsController.dispose();
    super.dispose();
  }

  Future<void> _pickImages() async {
    final picker = ImagePicker();
    final images = await picker.pickMultiImage(
      maxWidth: 1200,
      maxHeight: 1200,
      imageQuality: 85,
    );
    if (images.isNotEmpty) {
      setState(() {
        _selectedImages.addAll(images.map((x) => File(x.path)));
        if (_selectedImages.length > 10) {
          _selectedImages = _selectedImages.sublist(0, 10);
        }
      });
    }
  }

  void _removeImage(int index) {
    setState(() => _selectedImages.removeAt(index));
  }

  Future<void> _submitProperty() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSubmitting = true);

    try {
      final api = ref.read(apiServiceProvider);
      final result = await api.post(
        'properties/submit',
        data: {
          'title': _titleController.text.trim(),
          'description': _descriptionController.text.trim(),
          'price': _priceController.text.trim(),
          'property_type': _propertyType,
          'listing_type': _listingType,
          'location': _locationController.text.trim(),
          'area_sqft': _areaController.text.trim(),
          'bedrooms': _bedroomsController.text.trim(),
          'bathrooms': _bathroomsController.text.trim(),
          'furnishing': _furnishing,
          'images': [],
        },
      );

      if (!mounted) return;

      if (result['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text((result['message'] as String?) ?? 'Property submitted successfully!'),
            backgroundColor: AppTheme.successColor,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
          ),
        );
        context.pop();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text((result['message'] as String?) ?? 'Submission failed. Please try again.'),
            backgroundColor: AppTheme.errorColor,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
          ),
        );
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Error: $e'),
          backgroundColor: AppTheme.errorColor,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        ),
      );
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Post Property'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _buildSectionTitle('Property Details'),
              const SizedBox(height: 12),
              _buildTextField(
                controller: _titleController,
                label: 'Property Title',
                hint: 'e.g. 3 BHK House in Suryoday Heights',
                icon: Icons.title,
                validator: (v) => v == null || v.trim().isEmpty ? 'Title is required' : null,
              ),
              const SizedBox(height: 12),
              _buildTextField(
                controller: _descriptionController,
                label: 'Description',
                hint: 'Describe your property...',
                icon: Icons.description,
                maxLines: 3,
                validator: (v) => v == null || v.trim().isEmpty ? 'Description is required' : null,
              ),
              const SizedBox(height: 20),

              _buildSectionTitle('Type & Price'),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(child: _buildDropdown('Property Type', _propertyType, _propertyTypes, (v) => setState(() => _propertyType = v!))),
                  const SizedBox(width: 12),
                  Expanded(child: _buildDropdown('Listing', _listingType, _listingTypes, (v) => setState(() => _listingType = v!))),
                ],
              ),
              const SizedBox(height: 12),
              _buildTextField(
                controller: _priceController,
                label: 'Price (₹)',
                hint: 'e.g. 2500000',
                icon: Icons.currency_rupee,
                keyboardType: TextInputType.number,
                validator: (v) {
                  if (v == null || v.trim().isEmpty) return 'Price is required';
                  if (int.tryParse(v.trim()) == null) return 'Enter valid price';
                  return null;
                },
              ),
              const SizedBox(height: 20),

              _buildSectionTitle('Location & Size'),
              const SizedBox(height: 12),
              _buildTextField(
                controller: _locationController,
                label: 'Location',
                hint: 'e.g. Suryoday Heights, Gorakhpur',
                icon: Icons.location_on,
                validator: (v) => v == null || v.trim().isEmpty ? 'Location is required' : null,
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(child: _buildTextField(
                    controller: _areaController,
                    label: 'Area (sqft)',
                    hint: 'e.g. 1200',
                    icon: Icons.square_foot,
                    keyboardType: TextInputType.number,
                  )),
                  const SizedBox(width: 12),
                  Expanded(child: _buildDropdown('Furnishing', _furnishing, _furnishingTypes, (v) => setState(() => _furnishing = v!))),
                ],
              ),
              const SizedBox(height: 12),
              if (_propertyType != 'Plot' && _propertyType != 'Shop') ...[
                Row(
                  children: [
                    Expanded(child: _buildTextField(
                      controller: _bedroomsController,
                      label: 'Bedrooms',
                      hint: 'e.g. 3',
                      icon: Icons.bed,
                      keyboardType: TextInputType.number,
                    )),
                    const SizedBox(width: 12),
                    Expanded(child: _buildTextField(
                      controller: _bathroomsController,
                      label: 'Bathrooms',
                      hint: 'e.g. 2',
                      icon: Icons.bathroom,
                      keyboardType: TextInputType.number,
                    )),
                  ],
                ),
                const SizedBox(height: 12),
              ],
              const SizedBox(height: 8),

              _buildSectionTitle('Photos'),
              const SizedBox(height: 12),
              _buildImagePicker(),
              const SizedBox(height: 24),

              _buildSubmitButton(),
              const SizedBox(height: 24),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSectionTitle(String title) {
    return Text(
      title,
      style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppTheme.primaryColor),
    );
  }

  Widget _buildTextField({
    required TextEditingController controller,
    required String label,
    required String hint,
    required IconData icon,
    String? Function(String?)? validator,
    TextInputType? keyboardType,
    int maxLines = 1,
  }) {
    return TextFormField(
      controller: controller,
      validator: validator,
      keyboardType: keyboardType,
      maxLines: maxLines,
      decoration: InputDecoration(
        labelText: label,
        hintText: hint,
        prefixIcon: Icon(icon, color: AppTheme.primaryColor.withValues(alpha: 0.6)),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: AppTheme.primaryColor, width: 2),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey.shade300),
        ),
        filled: true,
        fillColor: Colors.white,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      ),
    );
  }

  Widget _buildDropdown(String label, String value, List<String> items, void Function(String?) onChanged) {
    return DropdownButtonFormField<String>(
      initialValue: value,
      onChanged: onChanged,
      decoration: InputDecoration(
        labelText: label,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: AppTheme.primaryColor, width: 2),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: Colors.grey.shade300),
        ),
        filled: true,
        fillColor: Colors.white,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      ),
      items: items.map((t) => DropdownMenuItem(value: t, child: Text(t))).toList(),
    );
  }

  Widget _buildImagePicker() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        if (_selectedImages.isNotEmpty) ...[
          SizedBox(
            height: 100,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              itemCount: _selectedImages.length,
              separatorBuilder: (_, __) => const SizedBox(width: 8),
              itemBuilder: (context, index) {
                return Stack(
                  children: [
                    ClipRRect(
                      borderRadius: BorderRadius.circular(8),
                      child: Image.file(
                        _selectedImages[index],
                        width: 100,
                        height: 100,
                        fit: BoxFit.cover,
                      ),
                    ),
                    Positioned(
                      top: 4,
                      right: 4,
                      child: GestureDetector(
                        onTap: () => _removeImage(index),
                        child: Container(
                          padding: const EdgeInsets.all(4),
                          decoration: const BoxDecoration(color: Colors.red, shape: BoxShape.circle),
                          child: const Icon(Icons.close, color: Colors.white, size: 16),
                        ),
                      ),
                    ),
                  ],
                );
              },
            ),
          ),
          const SizedBox(height: 8),
          Text('${_selectedImages.length}/10 photos selected', style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
          const SizedBox(height: 8),
        ],
        OutlinedButton.icon(
          onPressed: _selectedImages.length >= 10 ? null : _pickImages,
          icon: const Icon(Icons.camera_alt, color: AppTheme.primaryColor),
          label: Text(
            _selectedImages.isEmpty ? 'Add Photos' : 'Add More Photos',
            style: const TextStyle(color: AppTheme.primaryColor),
          ),
          style: OutlinedButton.styleFrom(
            minimumSize: const Size(double.infinity, 48),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            side: BorderSide(color: AppTheme.primaryColor.withValues(alpha: 0.4)),
          ),
        ),
      ],
    );
  }

  Widget _buildSubmitButton() {
    return SizedBox(
      width: double.infinity,
      height: 50,
      child: ElevatedButton(
        onPressed: _isSubmitting ? null : _submitProperty,
        style: ElevatedButton.styleFrom(
          backgroundColor: AppTheme.primaryColor,
          foregroundColor: Colors.white,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          elevation: 2,
        ),
        child: _isSubmitting
            ? const SizedBox(
                width: 24,
                height: 24,
                child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
              )
            : const Text(
                'Submit Property',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
      ),
    );
  }
}

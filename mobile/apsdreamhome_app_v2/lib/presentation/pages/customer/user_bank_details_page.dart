import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

class UserBankDetailsPage extends ConsumerStatefulWidget {
  const UserBankDetailsPage({super.key});

  @override
  ConsumerState<UserBankDetailsPage> createState() => _UserBankDetailsPageState();
}

class _UserBankDetailsPageState extends ConsumerState<UserBankDetailsPage> {
  Map<String, dynamic>? _bankDetails;
  bool _isLoading = true;
  bool _isEditing = false;
  final _formKey = GlobalKey<FormState>();
  final _accountHolderController = TextEditingController();
  final _accountNumberController = TextEditingController();
  final _ifscController = TextEditingController();
  final _bankNameController = TextEditingController();
  final _branchController = TextEditingController();
  final _upiIdController = TextEditingController();
  Dio get _dio => Dio(BaseOptions(baseUrl: AppConstants.baseUrl));

  @override
  void initState() {
    super.initState();
    _fetchBankDetails();
  }

  Future<void> _fetchBankDetails() async {
    setState(() => _isLoading = true);
    try {
      final token = await ref.read(authProvider.notifier).getToken();
      final response = await _dio.get(
        '/api/v2/mobile/user/bank-details',
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );
      final resData = response.data as Map<String, dynamic>;
      if (resData['success'] == true && resData['data'] != null) {
        setState(() {
          _bankDetails = resData['data'] as Map<String, dynamic>;
          _populateFields();
          _isLoading = false;
        });
      } else {
        setState(() => _isLoading = false);
      }
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  void _populateFields() {
    if (_bankDetails != null) {
      _accountHolderController.text = _bankDetails!['account_holder_name']?.toString() ?? '';
      _accountNumberController.text = _bankDetails!['account_number']?.toString() ?? '';
      _ifscController.text = _bankDetails!['ifsc_code']?.toString() ?? '';
      _bankNameController.text = _bankDetails!['bank_name']?.toString() ?? '';
      _branchController.text = _bankDetails!['branch']?.toString() ?? '';
      _upiIdController.text = _bankDetails!['upi_id']?.toString() ?? '';
    }
  }

  Future<void> _saveBankDetails() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);
    try {
      final token = await ref.read(authProvider.notifier).getToken();
      final response = await _dio.post(
        '/api/v2/mobile/user/bank-details',
        data: {
          'account_holder_name': _accountHolderController.text.trim(),
          'account_number': _accountNumberController.text.trim(),
          'ifsc_code': _ifscController.text.trim().toUpperCase(),
          'bank_name': _bankNameController.text.trim(),
          'branch': _branchController.text.trim(),
          'upi_id': _upiIdController.text.trim(),
        },
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );
      final resData = response.data as Map<String, dynamic>;
      if (mounted) {
        setState(() {
          _isLoading = false;
          _isEditing = false;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text((resData['message'] ?? 'Bank details saved successfully!').toString()),
            backgroundColor: AppTheme.successColor,
          ),
        );
        _fetchBankDetails();
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to save: $e'), backgroundColor: Colors.red),
        );
      }
    }
  }

  @override
  void dispose() {
    _accountHolderController.dispose();
    _accountNumberController.dispose();
    _ifscController.dispose();
    _bankNameController.dispose();
    _branchController.dispose();
    _upiIdController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: MeshGradientBackground(
        child: SafeArea(
          child: _isLoading && _bankDetails == null
              ? const Center(child: CircularProgressIndicator(color: Colors.white))
              : SingleChildScrollView(
                  padding: const EdgeInsets.fromLTRB(20, 20, 20, 40),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _buildHeader(),
                      const SizedBox(height: 24),
                      _buildBankDetailsCard(),
                      const SizedBox(height: 24),
                      if (!_isEditing) _buildActionButtons(),
                    ],
                  ),
                ),
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return Row(
      children: [
        GestureDetector(
          onTap: () => Navigator.pop(context),
          child: Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: const Icon(Icons.arrow_back, color: Colors.white, size: 22),
          ),
        ),
        const SizedBox(width: 16),
        Expanded(
          child: ShaderMask(
            shaderCallback: (bounds) => const LinearGradient(
              colors: [Colors.white, Color(0xFF5EEAD4)],
            ).createShader(bounds),
            child: Text(
              'Bank Details',
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                color: Colors.white,
                fontWeight: FontWeight.w700,
              ),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildBankDetailsCard() {
    return GlassCard(
      padding: const EdgeInsets.all(20),
      opacity: 0.1,
      blur: 10,
      child: Form(
        key: _formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                ShaderMask(
                  shaderCallback: (bounds) => const LinearGradient(
                    colors: [Colors.white, Color(0xFF5EEAD4)],
                  ).createShader(bounds),
                  child: Text(
                    'Bank Account Information',
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(
                      color: Colors.white,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ),
                if (!_isEditing)
                  TextButton.icon(
                    onPressed: () => setState(() => _isEditing = true),
                    icon: const Icon(Icons.edit_rounded, size: 18),
                    label: const Text('Edit'),
                    style: TextButton.styleFrom(foregroundColor: AppTheme.accentColor),
                  ),
              ],
            ),
            const SizedBox(height: 20),
            _buildField(
              controller: _accountHolderController,
              label: 'Account Holder Name *',
              icon: Icons.person_outline_rounded,
              validator: (v) => v?.trim().isEmpty == true ? 'Required' : null,
              enabled: _isEditing,
            ),
            const SizedBox(height: 16),
            _buildField(
              controller: _accountNumberController,
              label: 'Account Number *',
              icon: Icons.account_balance_rounded,
              keyboardType: TextInputType.number,
              validator: (v) => v?.trim().isEmpty == true ? 'Required' : null,
              enabled: _isEditing,
            ),
            const SizedBox(height: 16),
            _buildField(
              controller: _ifscController,
              label: 'IFSC Code *',
              icon: Icons.confirmation_number_rounded,
              validator: (v) {
                if (v?.trim().isEmpty == true) return 'Required';
                if (v!.trim().length != 11) return 'IFSC must be 11 characters';
                return null;
              },
              enabled: _isEditing,
            ),
            const SizedBox(height: 16),
            _buildField(
              controller: _bankNameController,
              label: 'Bank Name *',
              icon: Icons.business_rounded,
              validator: (v) => v?.trim().isEmpty == true ? 'Required' : null,
              enabled: _isEditing,
            ),
            const SizedBox(height: 16),
            _buildField(
              controller: _branchController,
              label: 'Branch',
              icon: Icons.location_on_outlined,
              enabled: _isEditing,
            ),
            const SizedBox(height: 16),
            _buildField(
              controller: _upiIdController,
              label: 'UPI ID',
              icon: Icons.qr_code_rounded,
              enabled: _isEditing,
            ),
            if (_isEditing)
              Column(
                children: [
                  const SizedBox(height: 24),
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton(
                          onPressed: () => setState(() => _isEditing = false),
                          style: OutlinedButton.styleFrom(
                            foregroundColor: Colors.white,
                            side: BorderSide(color: Colors.white.withValues(alpha: 0.3)),
                            padding: const EdgeInsets.symmetric(vertical: 16),
                          ),
                          child: const Text('Cancel'),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: ElevatedButton(
                          onPressed: _isLoading ? null : _saveBankDetails,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppTheme.accentColor,
                            foregroundColor: AppTheme.primaryColor,
                            padding: const EdgeInsets.symmetric(vertical: 16),
                          ),
                          child: _isLoading
                              ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: AppTheme.primaryColor))
                              : const Text('Save Changes'),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildField({
    required TextEditingController controller,
    required String label,
    required IconData icon,
    TextInputType? keyboardType,
    String? Function(String?)? validator,
    bool enabled = true,
  }) {
    return TextFormField(
      controller: controller,
      keyboardType: keyboardType,
      validator: validator,
      enabled: enabled,
      style: const TextStyle(color: Colors.white),
      decoration: InputDecoration(
        labelText: label,
        labelStyle: TextStyle(color: Colors.white.withValues(alpha: 0.6)),
        prefixIcon: Icon(icon, color: Colors.white.withValues(alpha: 0.5), size: 20),
        filled: true,
        fillColor: enabled ? Colors.white.withValues(alpha: 0.05) : Colors.white.withValues(alpha: 0.02),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.white.withValues(alpha: 0.1))),
        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.white.withValues(alpha: 0.1))),
        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppTheme.accentColor, width: 1.5)),
        disabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.white.withValues(alpha: 0.05))),
        errorBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Colors.red)),
      ),
    );
  }

  Widget _buildActionButtons() {
    return Column(
      children: [
        SizedBox(
          width: double.infinity,
          height: 52,
          child: ElevatedButton.icon(
            onPressed: () => setState(() => _isEditing = true),
            icon: const Icon(Icons.edit_rounded, size: 20),
            label: const Text('Edit Bank Details', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 16)),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.accentColor,
              foregroundColor: AppTheme.primaryColor,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
          ),
        ),
        const SizedBox(height: 12),
        GlassCard(
          padding: const EdgeInsets.all(16),
          opacity: 0.08,
          blur: 6,
          child: Row(
            children: [
              Icon(Icons.security_rounded, color: AppTheme.accentColor, size: 24),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Secure & Encrypted',
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 13),
                    ),
                    Text(
                      'Your bank details are encrypted and never shared with third parties.',
                      style: TextStyle(color: Colors.white.withValues(alpha: 0.6), fontSize: 11),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
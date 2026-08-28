import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/logger.dart';
import '../../../data/models/user_model.dart';
import '../../../data/services/auth_service.dart';
import '../../widgets/app_widgets.dart';
import '../../widgets/glass_card.dart';

class RegisterPage extends ConsumerStatefulWidget {
  const RegisterPage({super.key});

  @override
  ConsumerState<RegisterPage> createState() => _RegisterPageState();
}

class _RegisterPageState extends ConsumerState<RegisterPage> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  final _referralCodeController = TextEditingController();

  bool _isLoading = false;
  bool _obscurePassword = true;
  bool _obscureConfirmPassword = true;
  String _selectedRole = AppConstants.roleCustomer;

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    _referralCodeController.dispose();
    super.dispose();
  }

  Future<void> _register() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _isLoading = true);
    try {
      final authService = ref.read(authServiceProvider);
      final credential = await authService.registerWithEmailPassword(
        email: _emailController.text.trim(),
        password: _passwordController.text,
        name: _nameController.text.trim(),
        phone: _phoneController.text.trim(),
        role: _selectedRole,
        parentReferralCode: _referralCodeController.text.trim().isEmpty
            ? null
            : _referralCodeController.text.trim(),
      );
      if (credential != null && mounted) {
        AuthBridge.instance.currentUser.value = credential as User;
        final usedReferral = _referralCodeController.text.trim();
        AppWidgets.showSuccessSnackBar(
          context,
          'Registration successful! Welcome to APS Dream Home.',
        );
        if (usedReferral.isNotEmpty && mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(
                'Joined via referral: $usedReferral. Check your profile for your own code!',
              ),
              backgroundColor: AppTheme.infoColor,
              duration: const Duration(seconds: 4),
            ),
          );
        }
        if (mounted) {
          context.go(
            '/welcome',
            extra: {
              'userName': _nameController.text.trim(),
              'role': _selectedRole,
              'registeredOnMobile': true,
            },
          );
        }
      }
    } on Exception catch (e) {
      AppLogger.error('Registration error', e);
      if (mounted) AppWidgets.showErrorSnackBar(context, e.toString());
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: MeshGradientBackground(
        child: SafeArea(
          child: SingleChildScrollView(
            child: Padding(
              padding: const EdgeInsets.all(24),
              child: AnimatedOpacity(
                opacity: 1.0,
                duration: const Duration(milliseconds: 800),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    const SizedBox(height: 10),
                    _buildHeader(),
                    const SizedBox(height: 24),
                    _buildRoleSelection(),
                    const SizedBox(height: 24),
                    _buildForm(),
                    const SizedBox(height: 16),
                    _buildRegisterLink(),
                    const SizedBox(height: 16),
                    _buildTerms(),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return Column(
      children: [
        GestureDetector(
          onTap: () => context.pop(),
          child: Align(
            alignment: Alignment.centerLeft,
            child: Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(
                Icons.arrow_back,
                color: Colors.white,
                size: 22,
              ),
            ),
          ),
        ),
        const SizedBox(height: 16),
        TweenAnimationBuilder(
          tween: Tween<double>(begin: 0, end: 1),
          duration: const Duration(milliseconds: 1000),
          curve: Curves.elasticOut,
          builder: (context, value, child) {
            return Transform.scale(scale: value, child: child);
          },
          child: Container(
            width: 80,
            height: 80,
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(20),
              boxShadow: [
                BoxShadow(
                  color: AppTheme.primaryColor.withValues(alpha: 0.3),
                  blurRadius: 20,
                  offset: const Offset(0, 8),
                ),
              ],
            ),
            child: const Icon(
              Icons.person_add_rounded,
              size: 40,
              color: Colors.white,
            ),
          ),
        ),
        const SizedBox(height: 16),
        ShaderMask(
          shaderCallback: (bounds) => const LinearGradient(
            colors: [AppTheme.primaryColor, AppTheme.accentColor],
          ).createShader(bounds),
          child: Text(
            'Create Account',
            style: Theme.of(context).textTheme.headlineMedium?.copyWith(
              color: Colors.white,
              fontWeight: FontWeight.w800,
            ),
          ),
        ),
        const SizedBox(height: 8),
        Text(
          'Join us and find your dream property',
          style: Theme.of(context).textTheme.bodyMedium?.copyWith(
            color: Colors.white70,
            letterSpacing: 0.3,
          ),
        ),
      ],
    );
  }

  Widget _buildRoleSelection() {
    return GlassCard(
      padding: const EdgeInsets.all(16),
      opacity: 0.1,
      blur: 8,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'I want to join as:',
            style: Theme.of(context).textTheme.titleSmall?.copyWith(
              color: Colors.white.withValues(alpha: 0.9),
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: _buildRoleCard(
                  'Customer',
                  'Buy Plots',
                  Icons.home_outlined,
                  AppConstants.roleCustomer,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _buildRoleCard(
                  'Associate',
                  'Sell & Earn',
                  Icons.people_outline,
                  AppConstants.roleAssociate,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildRoleCard(
    String title,
    String subtitle,
    IconData icon,
    String value,
  ) {
    final isSelected = _selectedRole == value;
    return GestureDetector(
      onTap: () => setState(() => _selectedRole = value),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          gradient: isSelected
              ? const LinearGradient(
                  colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
                )
              : null,
          color: isSelected ? null : Colors.white.withValues(alpha: 0.08),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: isSelected
                ? AppTheme.accentColor.withValues(alpha: 0.6)
                : Colors.white.withValues(alpha: 0.15),
            width: 1.5,
          ),
        ),
        child: Column(
          children: [
            Icon(
              icon,
              color: isSelected ? AppTheme.accentColor : Colors.white60,
              size: 28,
            ),
            const SizedBox(height: 6),
            Text(
              title,
              style: TextStyle(
                fontWeight: FontWeight.bold,
                color: isSelected ? Colors.white : Colors.white70,
                fontSize: 14,
              ),
            ),
            Text(
              subtitle,
              style: TextStyle(
                fontSize: 11,
                color: isSelected ? Colors.white60 : Colors.white38,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildForm() {
    return Form(
      key: _formKey,
      child: GlassCard(
        padding: const EdgeInsets.all(24),
        opacity: 0.12,
        blur: 8,
        child: Column(
          children: [
            _buildTextField(
              controller: _nameController,
              label: 'Full Name',
              hint: 'Enter your full name',
              icon: Icons.person_outline,
              validator: (v) {
                if (v == null || v.isEmpty) return 'Please enter your name';
                if (v.length < 3) return 'Name must be at least 3 characters';
                return null;
              },
            ),
            const SizedBox(height: 16),
            _buildTextField(
              controller: _emailController,
              label: 'Email',
              hint: 'Enter your email address',
              icon: Icons.email_outlined,
              keyboardType: TextInputType.emailAddress,
              validator: (v) {
                if (v == null || v.isEmpty) return 'Please enter your email';
                if (!v.contains('@') || !v.contains('.')) {
                  return 'Please enter a valid email';
                }
                return null;
              },
            ),
            const SizedBox(height: 16),
            _buildTextField(
              controller: _phoneController,
              label: 'Phone Number',
              hint: 'Enter 10 digit phone number',
              icon: Icons.phone_outlined,
              keyboardType: TextInputType.phone,
              maxLength: 10,
              prefixText: '+91 ',
              validator: (v) {
                if (v == null || v.isEmpty) {
                  return 'Please enter your phone number';
                }
                if (v.length != 10) {
                  return 'Please enter a valid 10 digit number';
                }
                return null;
              },
            ),
            const SizedBox(height: 16),
            _buildTextField(
              controller: _passwordController,
              label: 'Password',
              hint: 'Create a strong password',
              icon: Icons.lock_outline,
              obscureText: _obscurePassword,
              suffixIcon: IconButton(
                onPressed: () =>
                    setState(() => _obscurePassword = !_obscurePassword),
                icon: Icon(
                  _obscurePassword
                      ? Icons.visibility_off_outlined
                      : Icons.visibility_outlined,
                  color: Colors.white70,
                ),
              ),
              validator: (v) {
                if (v == null || v.isEmpty) return 'Please enter a password';
                if (v.length < AppConstants.minPasswordLength) {
                  return 'Password must be at least ${AppConstants.minPasswordLength} characters';
                }
                return null;
              },
            ),
            const SizedBox(height: 16),
            _buildTextField(
              controller: _confirmPasswordController,
              label: 'Confirm Password',
              hint: 'Re-enter your password',
              icon: Icons.lock_outline,
              obscureText: _obscureConfirmPassword,
              suffixIcon: IconButton(
                onPressed: () => setState(
                  () => _obscureConfirmPassword = !_obscureConfirmPassword,
                ),
                icon: Icon(
                  _obscureConfirmPassword
                      ? Icons.visibility_off_outlined
                      : Icons.visibility_outlined,
                  color: Colors.white70,
                ),
              ),
              validator: (v) {
                if (v == null || v.isEmpty) {
                  return 'Please confirm your password';
                }
                if (v != _passwordController.text) {
                  return 'Passwords do not match';
                }
                return null;
              },
            ),
            const SizedBox(height: 16),
            _buildTextField(
              controller: _referralCodeController,
              label: 'Referral Code (Optional)',
              hint: 'Enter referral code if you have one',
              icon: Icons.card_giftcard_outlined,
              textCapitalization: TextCapitalization.characters,
            ),
            const SizedBox(height: 8),
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: AppTheme.infoColor.withValues(alpha: 0.15),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(
                  color: AppTheme.infoColor.withValues(alpha: 0.3),
                ),
              ),
              child: Row(
                children: [
                  const Icon(
                    Icons.info_outline,
                    color: AppTheme.infoColor,
                    size: 18,
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Have a referral code? Enter it to join under an associate and start earning!',
                      style: TextStyle(
                        fontSize: 11,
                        color: Colors.white.withValues(alpha: 0.8),
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              height: 52,
              child: DecoratedBox(
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(14),
                  gradient: const LinearGradient(
                    colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: AppTheme.primaryColor.withValues(alpha: 0.4),
                      blurRadius: 12,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: ElevatedButton(
                  onPressed: _isLoading ? null : _register,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.transparent,
                    shadowColor: Colors.transparent,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                  ),
                  child: _isLoading
                      ? const Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            SizedBox(
                              width: 20,
                              height: 20,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                valueColor: AlwaysStoppedAnimation<Color>(
                                  Colors.white,
                                ),
                              ),
                            ),
                            SizedBox(width: 12),
                            Text(
                              'Creating Account...',
                              style: TextStyle(color: Colors.white),
                            ),
                          ],
                        )
                      : Text(
                          'Create Account',
                          style: Theme.of(context).textTheme.titleMedium
                              ?.copyWith(
                                color: Colors.white,
                                fontWeight: FontWeight.w600,
                              ),
                        ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTextField({
    required TextEditingController controller,
    required String label,
    required String hint,
    required IconData icon,
    Widget? suffixIcon,
    bool obscureText = false,
    int maxLength = 100,
    String? prefixText,
    TextInputType? keyboardType,
    TextCapitalization textCapitalization = TextCapitalization.none,
    String? Function(String?)? validator,
  }) {
    return TextFormField(
      controller: controller,
      obscureText: obscureText,
      keyboardType: keyboardType,
      textCapitalization: textCapitalization,
      maxLength: maxLength == 100 ? null : maxLength,
      style: const TextStyle(color: Colors.white),
      decoration: InputDecoration(
        labelText: label,
        hintText: hint,
        hintStyle: TextStyle(color: Colors.white.withValues(alpha: 0.4)),
        labelStyle: const TextStyle(color: Colors.white70),
        prefixIcon: Icon(icon, color: Colors.white70, size: 20),
        prefixText: prefixText,
        prefixStyle: const TextStyle(color: Colors.white60),
        suffixIcon: suffixIcon,
        counterText: '',
        filled: true,
        fillColor: Colors.white.withValues(alpha: 0.08),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide(color: Colors.white.withValues(alpha: 0.15)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: AppTheme.accentColor, width: 1.5),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: Colors.redAccent),
        ),
      ),
      validator: validator,
    );
  }

  Widget _buildRegisterLink() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Text(
          'Already have an account? ',
          style: TextStyle(
            color: Colors.white.withValues(alpha: 0.7),
            fontSize: 14,
          ),
        ),
        TextButton(
          onPressed: () => context.pop(),
          style: TextButton.styleFrom(
            padding: EdgeInsets.zero,
            minimumSize: Size.zero,
            tapTargetSize: MaterialTapTargetSize.shrinkWrap,
          ),
          child: Text(
            'Sign In',
            style: TextStyle(
              color: AppTheme.accentColor.withValues(alpha: 0.9),
              fontWeight: FontWeight.w600,
              fontSize: 14,
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildTerms() {
    return Text(
      'By registering, you agree to our Terms of Service and Privacy Policy',
      style: TextStyle(
        color: Colors.white.withValues(alpha: 0.5),
        fontSize: 11,
      ),
      textAlign: TextAlign.center,
    );
  }
}

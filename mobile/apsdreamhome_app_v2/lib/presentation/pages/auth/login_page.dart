import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/router/app_router.dart';
import '../../../core/services/notification_service.dart';
import '../../../core/utils/logger.dart';
import '../../widgets/app_widgets.dart';
import '../../widgets/glass_card.dart';

/// Login Page - Connected to AuthRepository
class LoginPage extends ConsumerStatefulWidget {
  const LoginPage({super.key});

  @override
  ConsumerState<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends ConsumerState<LoginPage> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _isLoading = false;
  bool _obscurePassword = true;
  int _selectedTab = 0;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _login() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);

    try {
      AppLogger.info('[LoginPage] Starting login...');
      final authNotifier = ref.read(authProvider.notifier);

      final user = await authNotifier.login(
        _emailController.text.trim(),
        _passwordController.text,
      );

      AppLogger.info('[LoginPage] Login returned: userId=${user.userId}, rank=${user.rank}');

      if (!mounted) return;

      AppWidgets.showSuccessSnackBar(context, 'Login successful');

      // Register FCM token now that user is authenticated
      try {
        final token = await NotificationService().getToken();
        if (token != null) {
          await NotificationService().saveTokenToBackend(token);
        }
      } catch (_) {}

      // Force GoRouter to re-evaluate redirect with the new auth state.
      // Do NOT call context.go() or ref.invalidate() — the former would
      // redirect back to /login (old closure), and the latter destroys the
      // mounted GoRouter causing _dependents.isEmpty assertion failure.
      // router.refresh() re-evaluates the redirect with the current auth state.
      ref.read(appRouterProvider).refresh();
      AppLogger.info('[LoginPage] Router refreshed, redirect should handle navigation');
    } on Exception catch (e) {
      AppLogger.error('[LoginPage] Exception', e);
      if (mounted) {
        AppWidgets.showErrorSnackBar(context, e.toString());
      }
    } catch (e, stackTrace) {
      AppLogger.error('[LoginPage] Error', e, stackTrace);
      if (mounted) {
        AppWidgets.showErrorSnackBar(context, 'Unexpected error: $e');
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
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
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const SizedBox(height: 40),

                  // Logo and Title
                  _buildHeader(),

                  const SizedBox(height: 40),

                  // Tab Selection
                  _buildTabSelection(),

                  const SizedBox(height: 32),

                  // Login Form
                  if (_selectedTab == 0)
                    _buildEmailLoginForm()
                  else
                    _buildPhoneLoginForm(),

                  const SizedBox(height: 24),

                  // Forgot Password
                  _buildForgotPassword(),

                  const SizedBox(height: 24),

                  // Register Link
                  _buildRegisterLink(),

                  const SizedBox(height: 20),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildHeader() {
    final textTheme = Theme.of(context).textTheme;
    return Column(
      children: [
        Container(
          width: 80,
          height: 80,
          decoration: BoxDecoration(
            color: AppTheme.primaryColor,
            borderRadius: BorderRadius.circular(20),
          ),
          child: const Icon(Icons.home, size: 40, color: Colors.white),
        ),
        const SizedBox(height: 16),
        Text(
          'APS Dream Home',
          style: textTheme.displayMedium?.copyWith(
            color: AppTheme.primaryColor,
          ),
        ),
        const SizedBox(height: 8),
        Text(
          'Your Dream Property Awaits',
          style: textTheme.bodyLarge?.copyWith(
            color: AppTheme.textSecondaryLight,
          ),
        ),
      ],
    );
  }

  Widget _buildTabSelection() {
    return Container(
      height: 50,
      decoration: BoxDecoration(
        color: Colors.grey.shade100,
        borderRadius: BorderRadius.circular(25),
      ),
      child: Row(
        children: [
          Expanded(
            child: GestureDetector(
              onTap: () => setState(() => _selectedTab = 0),
              child: Container(
                height: 46,
                margin: const EdgeInsets.all(2),
                decoration: BoxDecoration(
                  color: _selectedTab == 0 ? AppTheme.primaryColor : Colors.transparent,
                  borderRadius: BorderRadius.circular(23),
                ),
                child: Center(
                  child: Text(
                    'Email',
                    style: Theme.of(context).textTheme.titleSmall?.copyWith(
                      color: _selectedTab == 0 ? Colors.white : AppTheme.textSecondaryLight,
                    ),
                  ),
                ),
              ),
            ),
          ),
          Expanded(
            child: GestureDetector(
              onTap: () => setState(() => _selectedTab = 1),
              child: Container(
                height: 46,
                margin: const EdgeInsets.all(2),
                decoration: BoxDecoration(
                  color: _selectedTab == 1 ? AppTheme.primaryColor : Colors.transparent,
                  borderRadius: BorderRadius.circular(23),
                ),
                child: Center(
                  child: Text(
                    'Phone',
                    style: Theme.of(context).textTheme.titleSmall?.copyWith(
                      color: _selectedTab == 1 ? Colors.white : AppTheme.textSecondaryLight,
                    ),
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEmailLoginForm() {
    return Form(
      key: _formKey,
      child: Column(
        children: [
          TextFormField(
            controller: _emailController,
            keyboardType: TextInputType.emailAddress,
            decoration: InputDecoration(
              labelText: 'Email Address',
              hintText: 'Enter your email',
              prefixIcon: const Icon(Icons.email_outlined),
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: BorderSide(color: Colors.grey.shade300),
              ),
              focusedBorder: const OutlineInputBorder(
                borderRadius: BorderRadius.all(Radius.circular(12)),
                borderSide: BorderSide(color: AppTheme.primaryColor),
              ),
            ),
            validator: (value) {
              if (value == null || value.isEmpty) return 'Please enter your email';
              if (!value.contains('@')) return 'Please enter a valid email';
              return null;
            },
          ),
          const SizedBox(height: 16),
          TextFormField(
            controller: _passwordController,
            obscureText: _obscurePassword,
            decoration: InputDecoration(
              labelText: 'Password',
              hintText: 'Enter your password',
              prefixIcon: const Icon(Icons.lock_outline),
              suffixIcon: IconButton(
                onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                icon: Icon(_obscurePassword ? Icons.visibility_off : Icons.visibility),
              ),
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: BorderSide(color: Colors.grey.shade300),
              ),
              focusedBorder: const OutlineInputBorder(
                borderRadius: BorderRadius.all(Radius.circular(12)),
                borderSide: BorderSide(color: AppTheme.primaryColor),
              ),
            ),
            validator: (value) {
              if (value == null || value.isEmpty) return 'Please enter your password';
              if (value.length < 6) return 'Password must be at least 6 characters';
              return null;
            },
          ),
          const SizedBox(height: 32),
          SizedBox(
            width: double.infinity,
            height: 50,
            child: ElevatedButton(
              onPressed: _isLoading ? null : _login,
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primaryColor,
                foregroundColor: Colors.white,
                elevation: 0,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
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
                            valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                          ),
                        ),
                        SizedBox(width: 12),
                        Text('Signing in...'),
                      ],
                    )
                   : Text(
                       'Sign In',
                       style: Theme.of(context).textTheme.titleMedium?.copyWith(
                         color: Colors.white,
                       ),
                     ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPhoneLoginForm() {
    return Column(
      children: [
        TextFormField(
          keyboardType: TextInputType.phone,
          decoration: InputDecoration(
            labelText: 'Phone Number',
            hintText: '+91 98765 43210',
            prefixIcon: const Icon(Icons.phone_outlined),
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide(color: Colors.grey.shade300),
            ),
            focusedBorder: const OutlineInputBorder(
              borderRadius: BorderRadius.all(Radius.circular(12)),
              borderSide: BorderSide(color: AppTheme.primaryColor),
            ),
          ),
          validator: (value) {
            if (value == null || value.isEmpty) return 'Please enter your phone number';
            if (value.length < 10) return 'Please enter a valid phone number';
            return null;
          },
        ),
        const SizedBox(height: 32),
        SizedBox(
          width: double.infinity,
          height: 50,
          child: ElevatedButton(
            onPressed: () {},
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.primaryColor,
              foregroundColor: Colors.white,
              elevation: 0,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            child: Text('Send OTP', style: Theme.of(context).textTheme.titleMedium?.copyWith(
              color: Colors.white,
            )),
          ),
        ),
        const SizedBox(height: 16),
        Text(
          'We will send a verification code to your phone number',
          style: Theme.of(context).textTheme.bodySmall?.copyWith(
            color: AppTheme.textSecondaryLight,
          ),
          textAlign: TextAlign.center,
        ),
      ],
    );
  }

  Widget _buildForgotPassword() {
    return Align(
      alignment: Alignment.centerRight,
      child: TextButton(
        onPressed: () => context.push('/forgot-password'),
        child: const Text(
          'Forgot Password?',
          style: TextStyle(color: AppTheme.primaryColor, fontWeight: FontWeight.w500),
        ),
      ),
    );
  }

  Widget _buildRegisterLink() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Text(
          "Don't have an account? ",
          style: Theme.of(context).textTheme.bodyMedium?.copyWith(
            color: AppTheme.textSecondaryLight,
          ),
        ),
        TextButton(
          onPressed: () => context.push('/register'),
          style: TextButton.styleFrom(
            padding: EdgeInsets.zero,
            minimumSize: Size.zero,
            tapTargetSize: MaterialTapTargetSize.shrinkWrap,
          ),
          child: Text(
            'Register Now',
            style: Theme.of(context).textTheme.titleSmall?.copyWith(
              color: AppTheme.primaryColor,
            ),
          ),
        ),
      ],
    );
  }
}

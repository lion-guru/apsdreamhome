import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/providers/auth_provider.dart';
import '../../../core/router/app_router.dart';
import '../../../core/services/api_service.dart';
import '../../../core/services/notification_service.dart';
import '../../../core/utils/logger.dart';
import '../../../data/models/user_model.dart';
import '../../../data/services/google_auth_service.dart';
import '../../widgets/app_widgets.dart';
import '../../widgets/glass_card.dart';

class LoginPage extends ConsumerStatefulWidget {
  const LoginPage({super.key});

  @override
  ConsumerState<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends ConsumerState<LoginPage> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _phoneController = TextEditingController();
  final _secureStorage = const FlutterSecureStorage();
  bool _isLoading = false;
  bool _obscurePassword = true;
  bool _rememberMe = false;
  int _selectedTab = 0;

  @override
  void initState() {
    super.initState();
    _loadSavedCredentials();
  }

  Future<void> _loadSavedCredentials() async {
    final savedEmail = await _secureStorage.read(key: 'remembered_email');
    final savedPassword = await _secureStorage.read(key: 'remembered_password');
    final rememberFlag = await _secureStorage.read(key: 'remember_me');
    if (savedEmail != null && savedPassword != null && rememberFlag == 'true') {
      _emailController.text = savedEmail;
      _passwordController.text = savedPassword;
      setState(() => _rememberMe = true);
    }
  }

  Future<void> _saveCredentials() async {
    if (_rememberMe) {
      await _secureStorage.write(
        key: 'remembered_email',
        value: _emailController.text.trim(),
      );
      await _secureStorage.write(
        key: 'remembered_password',
        value: _passwordController.text,
      );
      await _secureStorage.write(key: 'remember_me', value: 'true');
    } else {
      await _secureStorage.delete(key: 'remembered_email');
      await _secureStorage.delete(key: 'remembered_password');
      await _secureStorage.delete(key: 'remember_me');
    }
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    _phoneController.dispose();
    super.dispose();
  }

  Future<void> _login() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);

    try {
      final authNotifier = ref.read(authProvider.notifier);

      final user = await authNotifier.login(
        _emailController.text.trim(),
        _passwordController.text,
      );

      if (!mounted) return;

      await _saveCredentials();

      // Register FCM token
      try {
        final token = await NotificationService().getToken();
        if (token != null) {
          await NotificationService().saveTokenToBackend(token);
        }
      } catch (_) {}

      // Navigate to role-based dashboard using current router context
      final route = defaultRouteForRole(user);
      AppLogger.info(
        '[LoginPage] Navigating to $route for role=${user.role ?? user.rank}',
      );
      // Explicitly update GoRouter's auth bridge so redirect sees the user immediately
      AuthBridge.instance.currentUser.value = user;
      if (mounted) context.go(route);
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
              child: AnimatedOpacity(
                opacity: 1.0,
                duration: const Duration(milliseconds: 800),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    const SizedBox(height: 20),
                    _buildHeader(),
                    const SizedBox(height: 32),
                    _buildTabSelection(),
                    const SizedBox(height: 24),
                    AnimatedSwitcher(
                      duration: const Duration(milliseconds: 300),
                      child: _selectedTab == 0
                          ? _buildEmailLoginForm()
                          : _buildPhoneLoginForm(),
                      transitionBuilder:
                          (Widget child, Animation<double> animation) {
                            return FadeTransition(
                              opacity: animation,
                              child: child,
                            );
                          },
                    ),
                    const SizedBox(height: 16),
                    _buildForgotPassword(),
                    const SizedBox(height: 8),
                    _buildDividerWithText(),
                    _buildSocialButtons(),
                    const SizedBox(height: 16),
                    _buildRegisterLink(),
                    const SizedBox(height: 20),
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
        TweenAnimationBuilder(
          tween: Tween<double>(begin: 0, end: 1),
          duration: const Duration(milliseconds: 1000),
          curve: Curves.elasticOut,
          builder: (context, value, child) {
            return Transform.scale(scale: value, child: child);
          },
          child: Container(
            width: 88,
            height: 88,
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [AppTheme.primaryColor, AppTheme.secondaryColor],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(22),
              boxShadow: [
                BoxShadow(
                  color: AppTheme.primaryColor.withValues(alpha: 0.3),
                  blurRadius: 20,
                  offset: const Offset(0, 8),
                ),
              ],
            ),
            child: const Icon(
              Icons.home_rounded,
              size: 44,
              color: Colors.white,
            ),
          ),
        ),
        const SizedBox(height: 20),
        ShaderMask(
          shaderCallback: (bounds) => const LinearGradient(
            colors: [AppTheme.primaryColor, AppTheme.accentColor],
          ).createShader(bounds),
          child: Text(
            'APS Dream Home',
            style: Theme.of(context).textTheme.displayMedium?.copyWith(
              color: Colors.white,
              fontWeight: FontWeight.w800,
            ),
          ),
        ),
        const SizedBox(height: 8),
        Text(
          'Your Dream Property Awaits',
          style: Theme.of(context).textTheme.bodyLarge?.copyWith(
            color: Colors.white70,
            letterSpacing: 0.5,
          ),
        ),
      ],
    );
  }

  Widget _buildTabSelection() {
    return Container(
      height: 48,
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(24),
      ),
      child: Row(
        children: [
          Expanded(
            child: GestureDetector(
              onTap: () => setState(() => _selectedTab = 0),
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                height: 44,
                margin: const EdgeInsets.all(2),
                decoration: BoxDecoration(
                  gradient: _selectedTab == 0
                      ? const LinearGradient(
                          colors: [
                            AppTheme.primaryColor,
                            AppTheme.secondaryColor,
                          ],
                        )
                      : null,
                  borderRadius: BorderRadius.circular(22),
                ),
                child: Center(
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        Icons.email_outlined,
                        size: 16,
                        color: _selectedTab == 0
                            ? Colors.white
                            : Colors.white70,
                      ),
                      const SizedBox(width: 6),
                      Text(
                        'Email',
                        style: Theme.of(context).textTheme.titleSmall?.copyWith(
                          color: _selectedTab == 0
                              ? Colors.white
                              : Colors.white70,
                          fontWeight: _selectedTab == 0
                              ? FontWeight.w600
                              : FontWeight.normal,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
          Expanded(
            child: GestureDetector(
              onTap: () => setState(() => _selectedTab = 1),
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                height: 44,
                margin: const EdgeInsets.all(2),
                decoration: BoxDecoration(
                  gradient: _selectedTab == 1
                      ? const LinearGradient(
                          colors: [
                            AppTheme.primaryColor,
                            AppTheme.secondaryColor,
                          ],
                        )
                      : null,
                  borderRadius: BorderRadius.circular(22),
                ),
                child: Center(
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        Icons.phone_outlined,
                        size: 16,
                        color: _selectedTab == 1
                            ? Colors.white
                            : Colors.white70,
                      ),
                      const SizedBox(width: 6),
                      Text(
                        'Phone',
                        style: Theme.of(context).textTheme.titleSmall?.copyWith(
                          color: _selectedTab == 1
                              ? Colors.white
                              : Colors.white70,
                          fontWeight: _selectedTab == 1
                              ? FontWeight.w600
                              : FontWeight.normal,
                        ),
                      ),
                    ],
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
      child: GlassCard(
        padding: const EdgeInsets.all(24),
        opacity: 0.12,
        blur: 8,
        child: Column(
          children: [
            TextFormField(
              controller: _emailController,
              keyboardType: TextInputType.emailAddress,
              style: const TextStyle(color: Colors.white),
              decoration: InputDecoration(
                labelText: 'Email Address',
                hintText: 'Enter your email',
                hintStyle: TextStyle(
                  color: Colors.white.withValues(alpha: 0.4),
                ),
                labelStyle: const TextStyle(color: Colors.white70),
                prefixIcon: const Icon(
                  Icons.email_outlined,
                  color: Colors.white70,
                ),
                filled: true,
                fillColor: Colors.white.withValues(alpha: 0.08),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: BorderSide.none,
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: BorderSide(
                    color: Colors.white.withValues(alpha: 0.15),
                  ),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: const BorderSide(
                    color: AppTheme.accentColor,
                    width: 1.5,
                  ),
                ),
                errorBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: const BorderSide(color: Colors.redAccent),
                ),
              ),
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Please enter your email';
                }
                if (!value.contains('@')) return 'Please enter a valid email';
                return null;
              },
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _passwordController,
              obscureText: _obscurePassword,
              style: const TextStyle(color: Colors.white),
              decoration: InputDecoration(
                labelText: 'Password',
                hintText: 'Enter your password',
                hintStyle: TextStyle(
                  color: Colors.white.withValues(alpha: 0.4),
                ),
                labelStyle: const TextStyle(color: Colors.white70),
                prefixIcon: const Icon(
                  Icons.lock_outline,
                  color: Colors.white70,
                ),
                suffixIcon: IconButton(
                  onPressed: () =>
                      setState(() => _obscurePassword = !_obscurePassword),
                  icon: Icon(
                    _obscurePassword ? Icons.visibility_off : Icons.visibility,
                    color: Colors.white70,
                  ),
                ),
                filled: true,
                fillColor: Colors.white.withValues(alpha: 0.08),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: BorderSide.none,
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: BorderSide(
                    color: Colors.white.withValues(alpha: 0.15),
                  ),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: const BorderSide(
                    color: AppTheme.accentColor,
                    width: 1.5,
                  ),
                ),
                errorBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: const BorderSide(color: Colors.redAccent),
                ),
              ),
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Please enter your password';
                }
                if (value.length < 6) {
                  return 'Password must be at least 6 characters';
                }
                return null;
              },
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                SizedBox(
                  height: 28,
                  child: Checkbox(
                    value: _rememberMe,
                    onChanged: (val) =>
                        setState(() => _rememberMe = val ?? false),
                    activeColor: AppTheme.accentColor,
                    checkColor: AppTheme.primaryColor,
                    side: BorderSide(
                      color: Colors.white.withValues(alpha: 0.4),
                    ),
                  ),
                ),
                const SizedBox(width: 4),
                Text(
                  'Remember me',
                  style: TextStyle(color: Colors.white.withValues(alpha: 0.8)),
                ),
              ],
            ),
            const SizedBox(height: 20),
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
                  onPressed: _isLoading ? null : _login,
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
                              'Signing in...',
                              style: TextStyle(color: Colors.white),
                            ),
                          ],
                        )
                      : Text(
                          'Sign In',
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

  Widget _buildPhoneLoginForm() {
    return GlassCard(
      padding: const EdgeInsets.all(24),
      opacity: 0.12,
      blur: 8,
      child: Column(
        children: [
          TextFormField(
            keyboardType: TextInputType.phone,
            style: const TextStyle(color: Colors.white),
            decoration: InputDecoration(
              labelText: 'Phone Number',
              hintText: '+91 98765 43210',
              hintStyle: TextStyle(color: Colors.white.withValues(alpha: 0.4)),
              labelStyle: const TextStyle(color: Colors.white70),
              prefixIcon: const Icon(
                Icons.phone_outlined,
                color: Colors.white70,
              ),
              filled: true,
              fillColor: Colors.white.withValues(alpha: 0.08),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: BorderSide.none,
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: BorderSide(
                  color: Colors.white.withValues(alpha: 0.15),
                ),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: const BorderSide(
                  color: AppTheme.accentColor,
                  width: 1.5,
                ),
              ),
              errorBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: const BorderSide(color: Colors.redAccent),
              ),
            ),
            validator: (value) {
              if (value == null || value.isEmpty) {
                return 'Please enter your phone number';
              }
              if (value.length < 10) return 'Please enter a valid phone number';
              return null;
            },
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
                onPressed: _isLoading
                    ? null
                    : () async {
                        final phone = _phoneController.text.trim();
                        if (phone.isEmpty || phone.length < 10) {
                          AppWidgets.showErrorSnackBar(
                            context,
                            'Please enter a valid phone number',
                          );
                          return;
                        }
                          setState(() => _isLoading = true);
                        try {
                          await ref
                              .read(authProvider.notifier)
                              .requestAirLoginOtp(phone);
                          if (!mounted) return;
                          final otp = await AppWidgets.showOTPDialog(
                            context,
                            title: 'Enter OTP',
                            message: 'OTP sent to $phone',
                          );
                          if (otp != null && otp.length == 6) {
                            final user = await ref
                                .read(authProvider.notifier)
                                .verifyAirLoginOtp(otp);
                            if (!mounted) return;
                            if (user != null) {
                              final route = defaultRouteForRole(user);
                              AuthBridge.instance.currentUser.value = user;
                              context.go(route);
                            }
                          }
                        } catch (e) {
                          if (!mounted) return;
                          AppWidgets.showErrorSnackBar(
                            context,
                            e.toString(),
                          );
                        } finally {
                          if (mounted) setState(() => _isLoading = false);
                        }
                      },
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.transparent,
                  shadowColor: Colors.transparent,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14),
                  ),
                ),
                child: const Text(
                  'Send OTP',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ),
          ),
          const SizedBox(height: 12),
          Text(
            'We will send a verification code to your phone',
            style: TextStyle(
              color: Colors.white.withValues(alpha: 0.6),
              fontSize: 13,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildDividerWithText() {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 16),
      child: Row(
        children: [
          Expanded(child: Divider(color: Colors.white.withValues(alpha: 0.2))),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Text(
              'or continue with',
              style: TextStyle(
                color: Colors.white.withValues(alpha: 0.6),
                fontSize: 13,
              ),
            ),
          ),
          Expanded(child: Divider(color: Colors.white.withValues(alpha: 0.2))),
        ],
      ),
    );
  }

  Widget _buildSocialButtons() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        _socialButton(Icons.g_mobiledata_rounded, 'Google'),
        const SizedBox(width: 16),
        _socialButton(Icons.phone_iphone_rounded, 'Phone'),
      ],
    );
  }

  Widget _socialButton(IconData icon, String label) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: () async {
          if (label == 'Phone') {
            setState(() => _selectedTab = 1);
          } else if (label == 'Google') {
            setState(() => _isLoading = true);
            try {
              final result = await GoogleAuthService.signInWithGoogle();
              if (result == null) {
                if (mounted) {
                  AppWidgets.showInfoSnackBar(
                    context,
                    'Google Sign-In cancelled or failed. Try again.',
                  );
                }
                return;
              }
              final token = result['token'] as String?;
              final userData = result['user'] as Map<String, dynamic>?;
              if (token != null && userData != null) {
                // Save token
                final apiService = ApiService();
                await apiService.saveToken(token);
                // Create user model
                final user = User.fromJson(userData);
                // Set auth state
                AuthBridge.instance.currentUser.value = user;
                // Register FCM token
                try {
                  final fcmToken = await NotificationService().getToken();
                  if (fcmToken != null) {
                    await NotificationService().saveTokenToBackend(fcmToken);
                  }
                } catch (_) {}
                if (mounted) {
                  final route = defaultRouteForRole(user);
                  context.go(route);
                }
              }
            } catch (e) {
              if (mounted) {
                AppWidgets.showErrorSnackBar(
                  context,
                  'Google Sign-In failed: $e',
                );
              }
            } finally {
              if (mounted) setState(() => _isLoading = false);
            }
          } else {
            AppWidgets.showInfoSnackBar(
              context,
              '$label login coming soon. Use Email login for now.',
            );
          }
        },
        borderRadius: BorderRadius.circular(12),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
          decoration: BoxDecoration(
            color: Colors.white.withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: Colors.white.withValues(alpha: 0.2)),
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(icon, color: Colors.white70, size: 22),
              const SizedBox(width: 8),
              Text(
                label,
                style: TextStyle(
                  color: Colors.white.withValues(alpha: 0.8),
                  fontSize: 14,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildForgotPassword() {
    return Align(
      alignment: Alignment.centerRight,
      child: TextButton(
        onPressed: () => context.push('/forgot-password'),
        child: Text(
          'Forgot Password?',
          style: TextStyle(
            color: AppTheme.accentColor.withValues(alpha: 0.9),
            fontWeight: FontWeight.w500,
            fontSize: 14,
          ),
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
          style: TextStyle(
            color: Colors.white.withValues(alpha: 0.7),
            fontSize: 14,
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
}

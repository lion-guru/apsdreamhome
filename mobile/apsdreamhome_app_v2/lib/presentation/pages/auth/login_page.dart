import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';

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
                    const SizedBox(height: 14),
                    _buildExploreGuestButton(),
                    const SizedBox(height: 12),
                    _buildForgotPassword(),
                    const SizedBox(height: 8),
                    _buildDividerWithText(),
                    _buildSocialButtons(),
                    const SizedBox(height: 16),
                    _buildRegisterLink(),
                    const SizedBox(height: 14),
                    _buildWhatsAppHelpline(),
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
            width: 96,
            height: 96,
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(24),
              border: Border.all(
                color: AppTheme.accentColor.withValues(alpha: 0.6),
                width: 2,
              ),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF0B194C).withValues(alpha: 0.12),
                  blurRadius: 24,
                  offset: const Offset(0, 8),
                ),
                BoxShadow(
                  color: AppTheme.accentColor.withValues(alpha: 0.20),
                  blurRadius: 14,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(22),
              child: Image.asset(
                'assets/images/app_logo_luxury.png',
                fit: BoxFit.cover,
                errorBuilder: (context, error, stackTrace) => const Icon(
                  Icons.home_work_rounded,
                  size: 48,
                  color: AppTheme.primaryColor,
                ),
              ),
            ),
          ),
        ),
        const SizedBox(height: 18),
        Text(
          'APS DREAM HOME',
          style: Theme.of(context).textTheme.headlineMedium?.copyWith(
            color: AppTheme.primaryColor,
            fontWeight: FontWeight.w900,
            letterSpacing: 1.5,
          ),
        ),
        const SizedBox(height: 8),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(20),
            border: Border.all(
              color: AppTheme.accentColor.withValues(alpha: 0.6),
              width: 1,
            ),
            boxShadow: [
              BoxShadow(
                color: const Color(0xFFD4AF37).withValues(alpha: 0.15),
                blurRadius: 8,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: const Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(Icons.verified_rounded, size: 14, color: AppTheme.accentColor),
              SizedBox(width: 6),
              Text(
                'RERA APPROVED TOWNSHIPS • GORAKHPUR',
                style: TextStyle(
                  color: AppTheme.primaryColor,
                  fontSize: 10,
                  fontWeight: FontWeight.w800,
                  letterSpacing: 0.8,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildTabSelection() {
    return Container(
      height: 48,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: AppTheme.goldBorder, width: 1.2),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0B194C).withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
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
                            : const Color(0xFF64748B),
                      ),
                      const SizedBox(width: 6),
                      Text(
                        'Email',
                        style: Theme.of(context).textTheme.titleSmall?.copyWith(
                          color: _selectedTab == 0
                              ? Colors.white
                              : const Color(0xFF64748B),
                          fontWeight: _selectedTab == 0
                              ? FontWeight.w700
                              : FontWeight.w500,
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
                            : const Color(0xFF64748B),
                      ),
                      const SizedBox(width: 6),
                      Text(
                        'Phone',
                        style: Theme.of(context).textTheme.titleSmall?.copyWith(
                          color: _selectedTab == 1
                              ? Colors.white
                              : const Color(0xFF64748B),
                          fontWeight: _selectedTab == 1
                              ? FontWeight.w700
                              : FontWeight.w500,
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
      child: Container(
        padding: const EdgeInsets.all(24),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: AppTheme.goldBorder, width: 1.2),
          boxShadow: [
            BoxShadow(
              color: const Color(0xFF0B194C).withValues(alpha: 0.08),
              blurRadius: 20,
              offset: const Offset(0, 6),
            ),
          ],
        ),
        child: Column(
          children: [
            TextFormField(
              controller: _emailController,
              keyboardType: TextInputType.emailAddress,
              style: const TextStyle(
                color: AppTheme.primaryColor,
                fontWeight: FontWeight.w600,
              ),
              decoration: InputDecoration(
                labelText: 'Email Address',
                hintText: 'Enter your email',
                hintStyle: const TextStyle(color: Color(0xFF94A3B8)),
                labelStyle: const TextStyle(
                  color: Color(0xFF64748B),
                  fontWeight: FontWeight.w500,
                ),
                prefixIcon: const Icon(
                  Icons.email_outlined,
                  color: AppTheme.primaryColor,
                ),
                filled: true,
                fillColor: const Color(0xFFFAF7F0),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: BorderSide.none,
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: const BorderSide(color: Color(0xFFE8DFD0)),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: const BorderSide(
                    color: AppTheme.accentColor,
                    width: 1.8,
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
              style: const TextStyle(
                color: AppTheme.primaryColor,
                fontWeight: FontWeight.w600,
              ),
              decoration: InputDecoration(
                labelText: 'Password',
                hintText: 'Enter your password',
                hintStyle: const TextStyle(color: Color(0xFF94A3B8)),
                labelStyle: const TextStyle(
                  color: Color(0xFF64748B),
                  fontWeight: FontWeight.w500,
                ),
                prefixIcon: const Icon(
                  Icons.lock_outline,
                  color: AppTheme.primaryColor,
                ),
                suffixIcon: IconButton(
                  onPressed: () =>
                      setState(() => _obscurePassword = !_obscurePassword),
                  icon: Icon(
                    _obscurePassword ? Icons.visibility_off : Icons.visibility,
                    color: const Color(0xFF64748B),
                  ),
                ),
                filled: true,
                fillColor: const Color(0xFFFAF7F0),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: BorderSide.none,
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: const BorderSide(color: Color(0xFFE8DFD0)),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: const BorderSide(
                    color: AppTheme.accentColor,
                    width: 1.8,
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
                    checkColor: Colors.white,
                    side: const BorderSide(
                      color: Color(0xFF94A3B8),
                    ),
                  ),
                ),
                const SizedBox(width: 4),
                const Text(
                  'Remember me',
                  style: TextStyle(
                    color: Color(0xFF334155),
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 14),
            _buildDemoAccountsBar(),
            const SizedBox(height: 16),
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
                      color: AppTheme.primaryColor.withValues(alpha: 0.3),
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
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppTheme.goldBorder, width: 1.2),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0B194C).withValues(alpha: 0.08),
            blurRadius: 20,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        children: [
          TextFormField(
            controller: _phoneController,
            keyboardType: TextInputType.phone,
            style: const TextStyle(
              color: AppTheme.primaryColor,
              fontWeight: FontWeight.w600,
            ),
            decoration: InputDecoration(
              labelText: 'Phone Number',
              hintText: '+91 98765 43210',
              hintStyle: const TextStyle(color: Color(0xFF94A3B8)),
              labelStyle: const TextStyle(
                color: Color(0xFF64748B),
                fontWeight: FontWeight.w500,
              ),
              prefixIcon: const Icon(
                Icons.phone_outlined,
                color: AppTheme.primaryColor,
              ),
              filled: true,
              fillColor: const Color(0xFFFAF7F0),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: BorderSide.none,
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: const BorderSide(color: Color(0xFFE8DFD0)),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: const BorderSide(
                  color: AppTheme.accentColor,
                  width: 1.8,
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
                    color: AppTheme.primaryColor.withValues(alpha: 0.3),
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
                            final route = defaultRouteForRole(user);
                            AuthBridge.instance.currentUser.value = user;
                            context.go(route);
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
          const Text(
            'We will send a verification code to your phone',
            style: TextStyle(
              color: Color(0xFF64748B),
              fontSize: 13,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildDividerWithText() {
    return const Padding(
      padding: EdgeInsets.symmetric(vertical: 16),
      child: Row(
        children: [
          Expanded(
            child: Divider(color: Color(0xFFE2D5BE), thickness: 1),
          ),
          Padding(
            padding: EdgeInsets.symmetric(horizontal: 16),
            child: Text(
              'or continue with',
              style: TextStyle(
                color: Color(0xFF64748B),
                fontSize: 13,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
          Expanded(
            child: Divider(color: Color(0xFFE2D5BE), thickness: 1),
          ),
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
                    'Google Sign-In was cancelled. Please try again.',
                  );
                }
                return;
              }
              final token = result['token'] as String?;
              final userData = result['user'] as Map<String, dynamic>?;
              if (token != null && userData != null) {
                final apiService = ApiService();
                await apiService.saveToken(token);
                final user = User.fromJson(userData);
                AuthBridge.instance.currentUser.value = user;
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
                  'Google Sign-In failed: ${e.toString()}',
                );
              }
            } finally {
              if (mounted) setState(() => _isLoading = false);
            }
          }
        },
        borderRadius: BorderRadius.circular(12),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: const Color(0xFFE8DFD0)),
            boxShadow: [
              BoxShadow(
                color: const Color(0xFF0B194C).withValues(alpha: 0.04),
                blurRadius: 8,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(icon, color: AppTheme.primaryColor, size: 22),
              const SizedBox(width: 8),
              Text(
                label,
                style: const TextStyle(
                  color: AppTheme.primaryColor,
                  fontWeight: FontWeight.w600,
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
            color: AppTheme.accentColor.withValues(alpha: 0.95),
            fontWeight: FontWeight.w600,
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
        const Text(
          "Don't have an account? ",
          style: TextStyle(
            color: Color(0xFF475569),
            fontSize: 14,
            fontWeight: FontWeight.w500,
          ),
        ),
        TextButton(
          onPressed: () => context.push('/register'),
          style: TextButton.styleFrom(
            padding: EdgeInsets.zero,
            minimumSize: Size.zero,
            tapTargetSize: MaterialTapTargetSize.shrinkWrap,
          ),
          child: const Text(
            'Register Now',
            style: TextStyle(
              color: AppTheme.accentColor,
              fontWeight: FontWeight.w800,
              fontSize: 14,
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildExploreGuestButton() {
    return SizedBox(
      width: double.infinity,
      height: 52,
      child: OutlinedButton(
        onPressed: () => context.go('/home'),
        style: OutlinedButton.styleFrom(
          side: const BorderSide(
            color: AppTheme.accentColor,
            width: 1.6,
          ),
          backgroundColor: Colors.white,
          elevation: 2,
          shadowColor: const Color(0xFFD4AF37).withValues(alpha: 0.2),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(14),
          ),
        ),
        child: const Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.travel_explore_rounded,
              color: AppTheme.accentColor,
              size: 22,
            ),
            SizedBox(width: 10),
            Text(
              'Explore Townships as Guest • बिना लॉगिन देखे',
              style: TextStyle(
                color: AppTheme.primaryColor,
                fontSize: 13,
                fontWeight: FontWeight.w800,
                letterSpacing: 0.3,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDemoAccountsBar() {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: const Color(0xFFFAF7F0),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: const Color(0xFFE8DFD0),
          width: 1.2,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            children: [
              Icon(
                Icons.flash_on_rounded,
                size: 14,
                color: AppTheme.accentColor,
              ),
              SizedBox(width: 5),
              Text(
                'TEST ACCOUNTS (TAP TO AUTOFILL):',
                style: TextStyle(
                  color: AppTheme.primaryColor,
                  fontSize: 11,
                  fontWeight: FontWeight.w800,
                  letterSpacing: 0.5,
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                _buildDemoChip(
                  label: 'Customer',
                  email: 'testuser@example.com',
                  pass: 'Aps@2026',
                  icon: Icons.person_rounded,
                ),
                const SizedBox(width: 6),
                _buildDemoChip(
                  label: 'Associate',
                  email: 'testassociate@example.com',
                  pass: 'Aps@2026',
                  icon: Icons.handshake_rounded,
                ),
                const SizedBox(width: 6),
                _buildDemoChip(
                  label: 'Admin',
                  email: 'admin@apsdreamhome.com',
                  pass: 'Aps@2026',
                  icon: Icons.shield_rounded,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDemoChip({
    required String label,
    required String email,
    required String pass,
    required IconData icon,
  }) {
    final isSelected = _emailController.text == email;
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: () {
          setState(() {
            _selectedTab = 0;
            _emailController.text = email;
            _passwordController.text = pass;
          });
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('Filled $label credentials ($email)'),
              duration: const Duration(seconds: 2),
              backgroundColor: AppTheme.primaryColor,
              behavior: SnackBarBehavior.floating,
            ),
          );
        },
        borderRadius: BorderRadius.circular(10),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
          decoration: BoxDecoration(
            color: isSelected
                ? AppTheme.accentColor.withValues(alpha: 0.15)
                : Colors.white,
            borderRadius: BorderRadius.circular(10),
            border: Border.all(
              color: isSelected
                  ? AppTheme.accentColor
                  : const Color(0xFFD4AF37).withValues(alpha: 0.4),
              width: 1.2,
            ),
            boxShadow: [
              BoxShadow(
                color: const Color(0xFF0B194C).withValues(alpha: 0.04),
                blurRadius: 4,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                icon,
                size: 14,
                color: isSelected ? AppTheme.accentColor : const Color(0xFF64748B),
              ),
              const SizedBox(width: 5),
              Text(
                label,
                style: TextStyle(
                  color: isSelected ? AppTheme.primaryColor : const Color(0xFF334155),
                  fontSize: 12,
                  fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildWhatsAppHelpline() {
    return Center(
      child: InkWell(
        onTap: () async {
          final uri = Uri.parse(
            'https://wa.me/917007444842?text=Hello%20APS%20Dream%20Home%2C%20I%20need%20assistance%20with%20the%20app',
          );
          if (await canLaunchUrl(uri)) {
            await launchUrl(uri, mode: LaunchMode.externalApplication);
          }
        },
        borderRadius: BorderRadius.circular(20),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          decoration: BoxDecoration(
            color: const Color(0xFF25D366).withValues(alpha: 0.12),
            borderRadius: BorderRadius.circular(20),
            border: Border.all(
              color: const Color(0xFF25D366).withValues(alpha: 0.5),
              width: 1.2,
            ),
          ),
          child: const Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                Icons.chat_bubble_outline_rounded,
                size: 15,
                color: Color(0xFF1E824C),
              ),
              SizedBox(width: 8),
              Text(
                'WhatsApp Support: +91 7007444842',
                style: TextStyle(
                  color: Color(0xFF1E824C),
                  fontSize: 12,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

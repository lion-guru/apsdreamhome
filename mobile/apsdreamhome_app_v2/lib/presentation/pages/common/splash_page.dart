import 'dart:async';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/router/app_router.dart';
import '../../../core/theme/app_theme.dart';
import '../../../data/services/auth_service.dart';
import '../../widgets/glass_card.dart';

class SplashPage extends StatefulWidget {
  const SplashPage({super.key});

  @override
  State<SplashPage> createState() => _SplashPageState();
}

class _SplashPageState extends State<SplashPage>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _fadeAnimation;
  late Animation<double> _scaleAnimation;
  bool _navigated = false;
  Timer? _escapeTimer;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1500),
    );
    _fadeAnimation = CurvedAnimation(parent: _controller, curve: Curves.easeIn);
    _scaleAnimation = Tween<double>(
      begin: 0.6,
      end: 1.0,
    ).animate(CurvedAnimation(parent: _controller, curve: Curves.elasticOut));
    _controller.forward();

    // Guaranteed escape hatch: the splash must never be visible for long,
    // even if every storage read or network call hangs (e.g. secure-storage
    // wedge on emulators). Always fall back to '/home' after 10s.
    _escapeTimer = Timer(const Duration(seconds: 10), () {
      _navigate('/home');
    });

    Timer(const Duration(seconds: 3), () {
      _checkAuthAndNavigate();
    });
  }

  void _navigate(String route) {
    if (!mounted || _navigated) return;
    _navigated = true;
    _escapeTimer?.cancel();
    context.go(route);
  }

  Future<void> _checkAuthAndNavigate() async {
    try {
      final authService = AuthService();
      await authService.refreshLoginState();
      final isLoggedIn = authService.currentUser != null;
      if (isLoggedIn) {
        final userData = await authService.getCurrentUserData();
        if (mounted) {
          AuthBridge.instance.currentUser.value = userData;
          if (userData != null) {
            _navigate(defaultRouteForRole(userData));
          } else {
            _navigate('/home');
          }
        }
      } else {
        _navigate('/home');
      }
    } catch (_) {
      _navigate('/home');
    }
  }

  @override
  void dispose() {
    _controller.dispose();
    _escapeTimer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: MeshGradientBackground(
        child: FadeTransition(
          opacity: _fadeAnimation,
          child: Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                ScaleTransition(
                  scale: _scaleAnimation,
                  child: Container(
                    width: 130,
                    height: 130,
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Colors.white, Color(0xFFE8EAF6)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(32),
                      boxShadow: [
                        BoxShadow(
                          color: AppTheme.accentColor.withValues(alpha: 0.3),
                          blurRadius: 30,
                          offset: const Offset(0, 10),
                        ),
                      ],
                    ),
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(32),
                      child: Image.asset(
                        'assets/images/aps_logo.webp',
                        fit: BoxFit.contain,
                        errorBuilder: (_, _, _) => const Icon(
                          Icons.home_rounded,
                          size: 70,
                          color: AppTheme.primaryColor,
                        ),
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 36),
                ShaderMask(
                  shaderCallback: (bounds) => const LinearGradient(
                    colors: [Colors.white, AppTheme.accentColor],
                  ).createShader(bounds),
                  child: Text(
                    AppConstants.appName,
                    style: AppTheme.displayLarge.copyWith(
                      color: Colors.white,
                      fontWeight: FontWeight.w800,
                      letterSpacing: 1.5,
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                Text(
                  'Your Dream Home Awaits',
                  style: AppTheme.titleLarge.copyWith(
                    color: Colors.white70,
                    letterSpacing: 2,
                  ),
                ),
                const SizedBox(height: 80),
                SizedBox(
                  width: 32,
                  height: 32,
                  child: CircularProgressIndicator(
                    valueColor: AlwaysStoppedAnimation<Color>(
                      AppTheme.accentColor.withValues(alpha: 0.7),
                    ),
                    strokeWidth: 2.5,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

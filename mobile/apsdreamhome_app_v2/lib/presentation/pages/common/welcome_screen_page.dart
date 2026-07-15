import 'dart:math';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_theme.dart';
import '../../widgets/glass_card.dart';

/// Animated welcome screen shown after first registration on mobile.
/// Gives a "special feel" with celebration particles, feature highlights,
/// and notification channel confirmation (Email + SMS + WhatsApp + Push).
class WelcomeScreenPage extends StatefulWidget {
  final String userName;
  final String role;
  final bool registeredOnMobile;

  const WelcomeScreenPage({
    super.key,
    this.userName = 'User',
    this.role = 'customer',
    this.registeredOnMobile = true,
  });

  @override
  State<WelcomeScreenPage> createState() => _WelcomeScreenPageState();
}

class _WelcomeScreenPageState extends State<WelcomeScreenPage>
    with TickerProviderStateMixin {
  late AnimationController _particleController;
  late AnimationController _fadeController;
  late AnimationController _slideController;
  int _currentStep = 0;

  final List<_WelcomeFeature> _features = [
    _WelcomeFeature(
      icon: Icons.home_rounded,
      title: 'Explore Properties',
      description: 'Browse colonies, plots, and premium listings near you',
      color: AppTheme.primaryColor,
    ),
    _WelcomeFeature(
      icon: Icons.account_balance_wallet_rounded,
      title: 'Track Your Investments',
      description: 'Monitor EMI payments, commissions, and wallet balance',
      color: const Color(0xFF22C55E),
    ),
    _WelcomeFeature(
      icon: Icons.people_rounded,
      title: 'Build Your Network',
      description: 'Refer friends and earn through our MLM program',
      color: const Color(0xFFF59E0B),
    ),
    _WelcomeFeature(
      icon: Icons.notifications_active_rounded,
      title: 'Stay Updated',
      description:
          'Get real-time alerts on WhatsApp, SMS, and push notifications',
      color: const Color(0xFF3B82F6),
    ),
  ];

  @override
  void initState() {
    super.initState();
    _particleController = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 4),
    )..repeat();

    _fadeController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1200),
    )..forward();

    _slideController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 600),
    )..forward();
  }

  @override
  void dispose() {
    _particleController.dispose();
    _fadeController.dispose();
    _slideController.dispose();
    super.dispose();
  }

  void _nextStep() {
    if (_currentStep < _features.length - 1) {
      setState(() => _currentStep++);
      _slideController.reset();
      _slideController.forward();
    } else {
      _goToDashboard();
    }
  }

  void _goToDashboard() {
    final dashboardRoute = widget.role == 'associate'
        ? '/associate/dashboard'
        : widget.role == 'agent'
        ? '/agent/dashboard'
        : '/home';
    context.go(dashboardRoute);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: MeshGradientBackground(
        child: Stack(
          children: [
            // Celebration particles
            AnimatedBuilder(
              animation: _particleController,
              builder: (context, _) => CustomPaint(
                size: MediaQuery.of(context).size,
                painter: _CelebrationPainter(
                  progress: _particleController.value,
                ),
              ),
            ),

            SafeArea(
              child: FadeTransition(
                opacity: _fadeController,
                child: Column(
                  children: [
                    const SizedBox(height: 40),

                    // Celebration icon
                    _buildCelebrationIcon(),

                    const SizedBox(height: 24),

                    // Welcome text
                    _buildWelcomeText(),

                    const SizedBox(height: 8),

                    // Notification confirmation
                    _buildNotificationBadges(),

                    const Spacer(),

                    // Feature card
                    _buildFeatureCard(),

                    const SizedBox(height: 24),

                    // Navigation dots + buttons
                    _buildNavigation(),

                    const SizedBox(height: 32),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCelebrationIcon() {
    return TweenAnimationBuilder(
      tween: Tween<double>(begin: 0, end: 1),
      duration: const Duration(milliseconds: 1200),
      curve: Curves.elasticOut,
      builder: (context, value, child) =>
          Transform.scale(scale: value, child: child),
      child: Container(
        width: 100,
        height: 100,
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            colors: [Color(0xFF22C55E), Color(0xFF16A34A)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          shape: BoxShape.circle,
          boxShadow: [
            BoxShadow(
              color: const Color(0xFF22C55E).withValues(alpha: 0.3),
              blurRadius: 30,
              offset: const Offset(0, 10),
            ),
          ],
        ),
        child: const Icon(Icons.check_rounded, size: 48, color: Colors.white),
      ),
    );
  }

  Widget _buildWelcomeText() {
    return Column(
      children: [
        ShaderMask(
          shaderCallback: (bounds) => const LinearGradient(
            colors: [Colors.white, AppTheme.accentColor],
          ).createShader(bounds),
          child: Text(
            'Welcome, ${widget.userName}!',
            style: Theme.of(context).textTheme.headlineMedium?.copyWith(
              color: Colors.white,
              fontWeight: FontWeight.w800,
            ),
            textAlign: TextAlign.center,
          ),
        ),
        const SizedBox(height: 8),
        Text(
          'Your account is all set up',
          style: Theme.of(
            context,
          ).textTheme.bodyLarge?.copyWith(color: Colors.white70),
        ),
      ],
    );
  }

  Widget _buildNotificationBadges() {
    return GlassCard(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
      opacity: 0.08,
      blur: 6,
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          _NotificationBadge(
            icon: Icons.email_rounded,
            label: 'Email',
            color: const Color(0xFF3B82F6),
          ),
          const SizedBox(width: 12),
          _NotificationBadge(
            icon: Icons.sms_rounded,
            label: 'SMS',
            color: const Color(0xFF22C55E),
          ),
          const SizedBox(width: 12),
          _NotificationBadge(
            icon: Icons.chat_rounded,
            label: 'WhatsApp',
            color: const Color(0xFF25D366),
          ),
          const SizedBox(width: 12),
          _NotificationBadge(
            icon: Icons.notifications_active_rounded,
            label: 'Push',
            color: const Color(0xFFF59E0B),
          ),
        ],
      ),
    );
  }

  Widget _buildFeatureCard() {
    final feature = _features[_currentStep];

    return AnimatedBuilder(
      animation: _slideController,
      builder: (context, child) {
        final slideOffset =
            1.0 - Curves.easeOutCubic.transform(_slideController.value);
        final opacity = _slideController.value;
        return Transform.translate(
          offset: Offset(40 * slideOffset, 0),
          child: Opacity(opacity: opacity.toDouble(), child: child),
        );
      },
      child: GlassCard(
        padding: const EdgeInsets.all(24),
        opacity: 0.10,
        blur: 10,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 56,
              height: 56,
              decoration: BoxDecoration(
                color: feature.color.withValues(alpha: 0.15),
                borderRadius: BorderRadius.circular(16),
              ),
              child: Icon(feature.icon, size: 28, color: feature.color),
            ),
            const SizedBox(height: 16),
            Text(
              feature.title,
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                color: Colors.white,
                fontWeight: FontWeight.w700,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            Text(
              feature.description,
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                color: Colors.white70,
                height: 1.5,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildNavigation() {
    return Column(
      children: [
        // Dots
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: List.generate(_features.length, (i) {
            final isActive = i == _currentStep;
            return AnimatedContainer(
              duration: const Duration(milliseconds: 300),
              margin: const EdgeInsets.symmetric(horizontal: 4),
              width: isActive ? 24 : 8,
              height: 8,
              decoration: BoxDecoration(
                color: isActive
                    ? AppTheme.accentColor
                    : Colors.white.withValues(alpha: 0.3),
                borderRadius: BorderRadius.circular(4),
              ),
            );
          }),
        ),
        const SizedBox(height: 20),

        // Buttons
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 24),
          child: Row(
            children: [
              if (_currentStep < _features.length - 1)
                Expanded(
                  child: TextButton(
                    onPressed: _goToDashboard,
                    child: Text(
                      'Skip',
                      style: TextStyle(
                        color: Colors.white.withValues(alpha: 0.6),
                        fontSize: 15,
                      ),
                    ),
                  ),
                ),
              const SizedBox(width: 12),
              Expanded(
                flex: _currentStep < _features.length - 1 ? 1 : 2,
                child: SizedBox(
                  height: 52,
                  child: DecoratedBox(
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(14),
                      gradient: const LinearGradient(
                        colors: [
                          AppTheme.primaryColor,
                          AppTheme.secondaryColor,
                        ],
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
                      onPressed: _nextStep,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.transparent,
                        shadowColor: Colors.transparent,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14),
                        ),
                      ),
                      child: Text(
                        _currentStep < _features.length - 1
                            ? 'Next'
                            : 'Get Started',
                        style: Theme.of(context).textTheme.titleMedium
                            ?.copyWith(
                              color: Colors.white,
                              fontWeight: FontWeight.w600,
                            ),
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

/// Notification channel badge widget
class _NotificationBadge extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;

  const _NotificationBadge({
    required this.icon,
    required this.label,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 14, color: color),
        const SizedBox(width: 4),
        Text(
          label,
          style: TextStyle(
            color: color,
            fontSize: 12,
            fontWeight: FontWeight.w600,
          ),
        ),
      ],
    );
  }
}

/// Feature model for onboarding steps
class _WelcomeFeature {
  final IconData icon;
  final String title;
  final String description;
  final Color color;

  const _WelcomeFeature({
    required this.icon,
    required this.title,
    required this.description,
    required this.color,
  });
}

/// Confetti / celebration particle painter
class _CelebrationPainter extends CustomPainter {
  final double progress;
  final Random _random = Random(42);

  _CelebrationPainter({required this.progress});

  @override
  void paint(Canvas canvas, Size size) {
    final colors = [
      const Color(0xFFFFD700),
      const Color(0xFF3B82F6),
      const Color(0xFF22C55E),
      const Color(0xFFF59E0B),
      const Color(0xFFE040FB),
      const Color(0xFFEF4444),
    ];

    for (int i = 0; i < 40; i++) {
      final seed = i * 137.508;
      final x = (_random.nextDouble() * size.width);
      final startY = -20.0 + (seed % 100);
      final speed = 1.5 + (_random.nextDouble() * 2);
      final y = startY + (progress * size.height * speed) % (size.height + 40);
      final color = colors[i % colors.length];
      final radius = 2.0 + _random.nextDouble() * 3;
      final opacity =
          (0.3 + _random.nextDouble() * 0.5) *
          (1 - (y / size.height).clamp(0.0, 1.0));

      final paint = Paint()
        ..color = color.withValues(alpha: opacity)
        ..style = PaintingStyle.fill;

      canvas.drawCircle(Offset(x, y), radius, paint);
    }
  }

  @override
  bool shouldRepaint(covariant _CelebrationPainter oldDelegate) =>
      progress != oldDelegate.progress;
}

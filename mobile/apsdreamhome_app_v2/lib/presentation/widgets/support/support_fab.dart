import 'dart:async';
import 'dart:math';

import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/constants/app_constants.dart';

class SupportFAB extends StatefulWidget {
  const SupportFAB({super.key});

  @override
  State<SupportFAB> createState() => _SupportFABState();
}

class _SupportFABState extends State<SupportFAB>
    with SingleTickerProviderStateMixin {
  bool _isOpen = false;
  late final AnimationController _animController;
  Timer? _pulseTimer;
  bool _pulse = true;

  @override
  void initState() {
    super.initState();
    _animController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 300),
    );
    _pulseTimer = Timer.periodic(const Duration(seconds: 3), (_) {
      if (mounted && !_isOpen) setState(() => _pulse = !_pulse);
    });
  }

  @override
  void dispose() {
    _pulseTimer?.cancel();
    _animController.dispose();
    super.dispose();
  }

  void _toggle() {
    setState(() => _isOpen = !_isOpen);
    if (_isOpen) {
      _animController.forward();
    } else {
      _animController.reverse();
    }
  }

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 70,
      height: 300,
      child: Stack(
        alignment: Alignment.bottomRight,
        children: [
          if (_isOpen)
            GestureDetector(
              onTap: _toggle,
              child: Container(color: Colors.black26),
            ),
          ..._buildActionButtons(),
          Positioned(bottom: 0, right: 0, child: _buildMainFab()),
        ],
      ),
    );
  }

  Widget _buildMainFab() {
    return GestureDetector(
      onTap: _toggle,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        width: 64,
        height: 64,
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            colors: [Color(0xFF25D366), Color(0xFF128C7E)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(32),
          boxShadow: [
            BoxShadow(
              color: Color(0xFF25D366).withValues(alpha: _pulse ? 0.5 : 0.3),
              blurRadius: _pulse ? 20 : 12,
              spreadRadius: _pulse ? 2 : 0,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: AnimatedSwitcher(
          duration: const Duration(milliseconds: 200),
          child: Icon(
            _isOpen ? Icons.close : Icons.headset_mic_rounded,
            key: ValueKey(_isOpen),
            color: Colors.white,
            size: 30,
          ),
        ),
      ),
    );
  }

  List<Widget> _buildActionButtons() {
    final actions = [
      _ActionData(Icons.phone_rounded, 'Call Us', const Color(0xFF007AFF),
          () async {
        _toggle();
        final uri = Uri.parse('tel:${AppConstants.supportPhone}');
        if (await canLaunchUrl(uri)) await launchUrl(uri);
      }),
      _ActionData(Icons.chat_rounded, 'WhatsApp', const Color(0xFF25D366),
          () async {
        _toggle();
        final uri = Uri.parse(
          'https://wa.me/91${AppConstants.supportPhone}?text=Hi%20APS%20Dream%20Home%2C%20I%20need%20help',
        );
        if (await canLaunchUrl(uri)) await launchUrl(uri);
      }),
      _ActionData(Icons.support_agent_rounded, 'Live Chat',
          const Color(0xFF1A237E), () {
        _toggle();
        context.push('/live-chat');
      }),
      _ActionData(Icons.confirmation_num_outlined, 'Support Ticket',
          const Color(0xFFFF6B35), () {
        _toggle();
        context.push('/support-tickets');
      }),
    ];

    return List.generate(actions.length, (i) {
      final action = actions[i];
      final offset = (i + 1) * 68.0;
      return AnimatedBuilder(
        animation: _animController,
        builder: (_, __) {
          final delay = i * 0.1;
          final t =
              ((_animController.value - delay) / (1.0 - delay)).clamp(0.0, 1.0);
          final scale = _isOpen ? Curves.easeOutBack.transform(t) : 0.0;
          return Positioned(
            bottom: offset,
            right: 8,
            child: Transform.scale(
              scale: scale,
              child: Opacity(
                opacity: scale,
                child: GestureDetector(
                  onTap: action.onTap,
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 12, vertical: 8),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(8),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.1),
                              blurRadius: 8,
                            ),
                          ],
                        ),
                        child: Text(
                          action.label,
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w600,
                            color: action.color,
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Container(
                        width: 52,
                        height: 52,
                        decoration: BoxDecoration(
                          color: action.color,
                          borderRadius: BorderRadius.circular(26),
                          boxShadow: [
                            BoxShadow(
                              color: action.color.withValues(alpha: 0.3),
                              blurRadius: 8,
                              offset: const Offset(0, 2),
                            ),
                          ],
                        ),
                        child: Icon(action.icon, color: Colors.white, size: 24),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          );
        },
      );
    });
  }
}

class _ActionData {
  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;

  const _ActionData(this.icon, this.label, this.color, this.onTap);
}

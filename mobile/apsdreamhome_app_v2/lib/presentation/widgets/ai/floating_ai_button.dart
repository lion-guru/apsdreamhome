import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

/// Floating AI Button Widget
/// Can be added to any page for quick AI access
class FloatingAIButton extends StatelessWidget {
  final bool isMini;
  final VoidCallback? onTap;

  const FloatingAIButton({
    super.key,
    this.isMini = false,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap ?? () => context.push('/ai-chat'),
      child: Container(
        width: isMini ? 48 : 64,
        height: isMini ? 48 : 64,
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: isMini
                ? const [Color(0xFF7C3AED), Color(0xFF9333EA)]
                : const [Color(0xFF4285F4), Color(0xFF34A853)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(isMini ? 24 : 32),
          boxShadow: [
            BoxShadow(
              color: (isMini
                      ? const Color(0xFF7C3AED)
                      : const Color(0xFF4285F4))
                  .withValues(alpha: 0.4),
              blurRadius: 12,
              spreadRadius: 2,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Center(
          child: Icon(
            isMini ? Icons.auto_awesome : Icons.chat_bubble,
            color: Colors.white,
            size: isMini ? 22 : 28,
          ),
        ),
      ),
    );
  }
}

/// AI Quick Action Bar
/// Shows suggested actions based on context
class AIQuickActionBar extends StatelessWidget {
  final String context;
  final List<String> suggestions;

  const AIQuickActionBar({
    super.key,
    required this.context,
    required this.suggestions,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.all(16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [Colors.blue.shade50, Colors.white],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.blue.shade200),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: Colors.blue.shade100,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Icon(Icons.auto_awesome,
                    color: Colors.blue.shade700, size: 20),
              ),
              const SizedBox(width: 12),
              Text(
                'AI Assistant',
                style: TextStyle(
                  fontWeight: FontWeight.bold,
                  color: Colors.blue.shade700,
                ),
              ),
              const Spacer(),
              TextButton(
                onPressed: () => context.push('/ai-chat'),
                child: const Text('Ask AI'),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Text(
            'Quick suggestions for $context:',
            style: TextStyle(
              fontSize: 12,
              color: Colors.grey.shade600,
            ),
          ),
          const SizedBox(height: 8),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: suggestions.map((suggestion) {
              return ActionChip(
                label: Text(suggestion),
                onPressed: () {
                  // Navigate to AI chat with pre-filled message
                  context.push('/ai-chat', extra: {'prompt': suggestion});
                },
                backgroundColor: Colors.white,
                side: BorderSide(color: Colors.blue.shade200),
                labelStyle: TextStyle(
                  color: Colors.blue.shade700,
                  fontSize: 12,
                ),
              );
            }).toList(),
          ),
        ],
      ),
    );
  }
}

/// AI Chat Overlay
/// Shows a mini chat interface as overlay
class AIChatOverlay extends StatefulWidget {
  final VoidCallback onClose;

  const AIChatOverlay({super.key, required this.onClose});

  @override
  State<AIChatOverlay> createState() => _AIChatOverlayState();
}

class _AIChatOverlayState extends State<AIChatOverlay> {
  final TextEditingController _controller = TextEditingController();

  @override
  Widget build(BuildContext context) {
    return Positioned(
      right: 16,
      bottom: 100,
      child: Material(
        elevation: 8,
        borderRadius: BorderRadius.circular(16),
        child: Container(
          width: 320,
          height: 400,
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
          ),
          child: Column(
            children: [
              // Header
              Container(
                padding: const EdgeInsets.all(12),
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                    colors: [Color(0xFF4285F4), Color(0xFF34A853)],
                  ),
                  borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.auto_awesome, color: Colors.white),
                    const SizedBox(width: 8),
                    const Expanded(
                      child: Text(
                        'APS AI Assistant',
                        style: TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close,
                          color: Colors.white, size: 20),
                      onPressed: widget.onClose,
                    ),
                  ],
                ),
              ),

              // Quick prompts
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(12),
                  child: Column(
                    children: [
                      _buildQuickPrompt('Find plots in Gorakhpur'),
                      _buildQuickPrompt('Calculate EMI'),
                      _buildQuickPrompt('Book site visit'),
                      _buildQuickPrompt('Check my commission'),
                      _buildQuickPrompt('KYC status'),
                    ],
                  ),
                ),
              ),

              // Input
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  border: Border(top: BorderSide(color: Colors.grey.shade200)),
                ),
                child: Row(
                  children: [
                    Expanded(
                      child: TextField(
                        controller: _controller,
                        decoration: InputDecoration(
                          hintText: 'Ask me anything...',
                          filled: true,
                          fillColor: Colors.grey.shade100,
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(20),
                            borderSide: BorderSide.none,
                          ),
                          contentPadding: const EdgeInsets.symmetric(
                              horizontal: 16, vertical: 12),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    GestureDetector(
                      onTap: () {
                        if (_controller.text.isNotEmpty) {
                          context.push('/ai-chat',
                              extra: {'prompt': _controller.text});
                          widget.onClose();
                        }
                      },
                      child: Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          gradient: const LinearGradient(
                            colors: [Color(0xFF4285F4), Color(0xFF34A853)],
                          ),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: const Icon(Icons.send,
                            color: Colors.white, size: 20),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildQuickPrompt(String text) {
    return InkWell(
      onTap: () {
        context.push('/ai-chat', extra: {'prompt': text});
        widget.onClose();
      },
      child: Container(
        width: double.infinity,
        margin: const EdgeInsets.only(bottom: 8),
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: Colors.grey.shade50,
          borderRadius: BorderRadius.circular(8),
        ),
        child: Row(
          children: [
            Icon(Icons.chat_bubble_outline,
                size: 16, color: Colors.grey.shade600),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                text,
                style: TextStyle(
                  color: Colors.grey.shade700,
                  fontSize: 13,
                ),
              ),
            ),
            Icon(Icons.arrow_forward_ios,
                size: 14, color: Colors.grey.shade400),
          ],
        ),
      ),
    );
  }
}

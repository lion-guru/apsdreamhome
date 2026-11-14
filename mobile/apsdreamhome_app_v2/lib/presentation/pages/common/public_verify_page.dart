import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

import '../../../core/constants/app_constants.dart';
import '../../../core/theme/app_theme.dart';

/// Public (no-login) document verification page.
/// Twin of the website /verify/{type}/{ref} page - renders the same
/// green "verified" card or red "not found" card for payment receipts,
/// allotment letters, statements, NOCs, registry deeds, etc.
class PublicVerifyPage extends StatefulWidget {
  final String type;
  final String ref;

  const PublicVerifyPage({super.key, required this.type, required this.ref});

  @override
  State<PublicVerifyPage> createState() => _PublicVerifyPageState();
}

class _PublicVerifyPageState extends State<PublicVerifyPage> {
  Map<String, dynamic>? _doc;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _verify();
  }

  Future<void> _verify() async {
    try {
      AppConstants.initBaseUrl();
      final url =
          '${AppConstants.baseUrl}/api/v2/public-verify/${Uri.encodeComponent(widget.type)}/${Uri.encodeComponent(widget.ref)}';
      final resp = await http.get(Uri.parse(url)).timeout(const Duration(seconds: 12));
      if (resp.statusCode == 200) {
        final data = jsonDecode(resp.body) as Map<String, dynamic>;
        setState(() {
          _doc = data['valid'] == true ? (data['doc'] as Map<String, dynamic>? ?? {}) : null;
          _loading = false;
        });
        return;
      }
      setState(() {
        _loading = false;
        _error = 'Verification service error (${resp.statusCode}). Try again.';
      });
    } catch (e) {
      setState(() {
        _loading = false;
        _error = 'Unable to reach the verification service. Check your connection.';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final verified = _doc != null && _error == null;
    return Scaffold(
      appBar: AppBar(
        title: const Text('Document Verification'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: Colors.white,
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? _errorCard(_error!)
              : verified
                  ? _verifiedCard()
                  : _notFoundCard(),
    );
  }

  Widget _verifiedCard() {
    final doc = _doc!;
    String field(String k) => (doc[k] ?? '').toString();
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.verified_rounded, size: 96, color: Colors.green),
            const SizedBox(height: 16),
            Text('${field('label')} Verified',
                style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: Colors.green)),
            const SizedBox(height: 4),
            Text('APS Dream Home — authentic document',
                style: TextStyle(color: Colors.grey.shade700)),
            const SizedBox(height: 20),
            Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: Colors.green.shade50,
                border: Border.all(color: Colors.green.shade300, width: 1.5),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _row('Reference', field('ref')),
                  const SizedBox(height: 8),
                  _row('Date', field('date')),
                  const SizedBox(height: 8),
                  _row('Customer', field('who')),
                  const SizedBox(height: 8),
                  _row('Plot', field('plot')),
                  const SizedBox(height: 8),
                  _row('Details', field('amount')),
                ],
              ),
            ),
            const SizedBox(height: 18),
            Text('Reference ID: ${widget.ref}',
                style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
          ],
        ),
      ),
    );
  }

  Widget _notFoundCard() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.gpp_bad_rounded, size: 96, color: Colors.red),
            const SizedBox(height: 16),
            const Text('Not Verified',
                style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: Colors.red)),
            const SizedBox(height: 4),
            Text('This document was not found in APS Dream Home records.',
                style: TextStyle(color: Colors.grey.shade700), textAlign: TextAlign.center),
            const SizedBox(height: 16),
            Text('Ref: ${widget.ref}',
                style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
            const SizedBox(height: 20),
            Text('If this is genuine, contact your APS Dream Home associate or the office.',
                style: TextStyle(color: Colors.grey.shade600, fontSize: 13),
                textAlign: TextAlign.center),
          ],
        ),
      ),
    );
  }

  Widget _errorCard(String message) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.wifi_off_rounded, size: 80, color: Colors.orange),
            const SizedBox(height: 16),
            const Text('Service Unavailable',
                style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800)),
            const SizedBox(height: 8),
            Text(message, style: TextStyle(color: Colors.grey.shade700), textAlign: TextAlign.center),
            const SizedBox(height: 18),
            ElevatedButton.icon(
              onPressed: _verify,
              icon: const Icon(Icons.refresh),
              label: const Text('Retry'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _row(String label, String value) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SizedBox(
          width: 92,
          child: Text(label,
              style: TextStyle(fontWeight: FontWeight.w700, color: Colors.grey.shade800, fontSize: 13)),
        ),
        Expanded(child: Text(value, style: const TextStyle(fontSize: 14))),
      ],
    );
  }
}

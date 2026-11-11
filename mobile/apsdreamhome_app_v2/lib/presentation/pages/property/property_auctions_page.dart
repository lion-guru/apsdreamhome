import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/services/api_service.dart';

class PropertyAuctionsPage extends ConsumerStatefulWidget {
  const PropertyAuctionsPage({super.key});

  @override
  ConsumerState<PropertyAuctionsPage> createState() =>
      _PropertyAuctionsPageState();
}

class _PropertyAuctionsPageState extends ConsumerState<PropertyAuctionsPage>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  bool _isLoading = true;
  List<dynamic> _allAuctions = [];
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _fetchAuctions();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _fetchAuctions() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final api = ApiService();
      final data = await api.get('/api/v2/mobile/auctions');
      if (data['success'] == true) {

        setState(() {
          _allAuctions = (data['auctions'] as List?) ?? [];
          _isLoading = false;
        });
      } else {
        setState(() {
          _errorMessage = 'Failed to load property auctions';
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = 'Network error: $e';
        _isLoading = false;
      });
    }
  }

  List<dynamic> _filterByStatus(String status) {
    if (status == 'live') {
      return _allAuctions
          .where((a) => a['status'] == 'live' || a['status'] == 'active')
          .toList();
    } else if (status == 'upcoming') {
      return _allAuctions
          .where((a) => a['status'] == 'scheduled' || a['status'] == 'draft')
          .toList();
    } else {
      return _allAuctions
          .where((a) =>
              a['status'] == 'ended' ||
              a['status'] == 'sold' ||
              a['status'] == 'closed')
          .toList();
    }
  }

  void _showPlaceBidModal(BuildContext context, Map<String, dynamic> auction) {
    final currentBid = double.tryParse(auction['current_bid']?.toString() ??
            auction['start_price']?.toString() ??
            '0') ??
        0.0;
    final increment =
        double.tryParse(auction['bid_increment']?.toString() ?? '10000') ??
            10000.0;
    final minBid = currentBid + increment;
    final bidCtrl =
        TextEditingController(text: minBid.toStringAsFixed(0));

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return Container(
          decoration: const BoxDecoration(
            color: Color(0xFFFAF7F0),
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          ),
          padding: EdgeInsets.only(
            left: 20,
            right: 20,
            top: 24,
            bottom: MediaQuery.of(ctx).viewInsets.bottom + 24,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 48,
                  height: 4,
                  decoration: BoxDecoration(
                    color: Colors.grey.shade400,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              const Row(
                children: [
                  Icon(Icons.gavel_rounded,
                      color: Color(0xFFD4AF37), size: 28),
                  SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      'Place Official Bid',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF0B194C),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Text(
                auction['title']?.toString() ?? 'Auction Property',
                style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                    color: Colors.grey.shade700),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
              const SizedBox(height: 16),
              Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: const Color(0xFFD4AF37)),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Current Highest Bid',
                            style:
                                TextStyle(fontSize: 11, color: Colors.grey)),
                        const SizedBox(height: 2),
                        Text(
                          '₹${_formatCurrency(currentBid)}',
                          style: const TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                              color: Color(0xFF0B194C)),
                        ),
                      ],
                    ),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        const Text('Min Next Bid',
                            style:
                                TextStyle(fontSize: 11, color: Colors.grey)),
                        const SizedBox(height: 2),
                        Text(
                          '₹${_formatCurrency(minBid)}',
                          style: const TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                              color: Colors.green),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),
              const Text('Your Bid Amount (₹ INR)',
                  style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                      color: Color(0xFF0B194C))),
              const SizedBox(height: 8),
              TextField(
                controller: bidCtrl,
                keyboardType: TextInputType.number,
                decoration: InputDecoration(
                  prefixText: '₹ ',
                  prefixStyle: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF0B194C)),
                  hintText: 'Enter amount',
                  filled: true,
                  fillColor: Colors.white,
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: BorderSide(color: Colors.grey.shade300),
                  ),
                ),
              ),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: () async {
                    final enteredAmount = double.tryParse(bidCtrl.text.trim()) ?? 0.0;
                    if (enteredAmount < minBid) {
                      ScaffoldMessenger.of(ctx).showSnackBar(
                        SnackBar(
                          content: Text('Minimum bid required is ₹${_formatCurrency(minBid)}'),
                          backgroundColor: Colors.redAccent,
                        ),
                      );
                      return;
                    }

                    Navigator.pop(ctx);
                    await _submitBid(auction['id'], enteredAmount);
                  },
                  icon: const Icon(Icons.check_circle_outline, color: Colors.white),
                  label: const Text('Confirm & Place Bid',
                      style: TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.bold,
                          color: Colors.white)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF0B194C),
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12)),
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Future<void> _submitBid(dynamic auctionId, double amount) async {
    setState(() => _isLoading = true);
    try {
      final api = ApiService();
      final data = await api.post(
        '/api/v2/mobile/auctions/$auctionId/bid',
        data: {'auction_id': auctionId, 'bid_amount': amount},
      );
      if (data['success'] == true) {

        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(data['message']?.toString() ?? 'Bid placed!'),
            backgroundColor: const Color(0xFF0B194C),
          ),
        );
        _fetchAuctions();
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
      );
      setState(() => _isLoading = false);
    }
  }

  String _formatCurrency(double amt) {
    if (amt >= 10000000) {
      return '${(amt / 10000000).toStringAsFixed(2)} Cr';
    } else if (amt >= 100000) {
      return '${(amt / 100000).toStringAsFixed(2)} Lakh';
    } else {
      return amt.toStringAsFixed(0);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F0),
      appBar: AppBar(
        title: const Text(
          'Live Property Auctions',
          style: TextStyle(
            color: Color(0xFFFAF7F0),
            fontWeight: FontWeight.bold,
            letterSpacing: 0.5,
          ),
        ),
        backgroundColor: const Color(0xFF0B194C),
        elevation: 0,
        centerTitle: true,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Color(0xFFFAF7F0)),
          onPressed: () => context.pop(),
        ),
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: const Color(0xFFD4AF37),
          indicatorWeight: 3,
          labelColor: const Color(0xFFD4AF37),
          unselectedLabelColor: Colors.white70,
          tabs: const [
            Tab(text: 'LIVE NOW'),
            Tab(text: 'UPCOMING'),
            Tab(text: 'PAST AUCTIONS'),
          ],
        ),
      ),
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(color: Color(0xFFD4AF37)),
            )
          : _errorMessage != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.error_outline,
                          color: Colors.red, size: 48),
                      const SizedBox(height: 12),
                      Text(_errorMessage!),
                      const SizedBox(height: 12),
                      ElevatedButton(
                        onPressed: _fetchAuctions,
                        child: const Text('Retry'),
                      ),
                    ],
                  ),
                )
              : TabBarView(
                  controller: _tabController,
                  children: [
                    _buildAuctionList(_filterByStatus('live'), isLive: true),
                    _buildAuctionList(_filterByStatus('upcoming'),
                        isUpcoming: true),
                    _buildAuctionList(_filterByStatus('closed')),
                  ],
                ),
    );
  }

  Widget _buildAuctionList(List<dynamic> list,
      {bool isLive = false, bool isUpcoming = false}) {
    if (list.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.gavel_outlined, size: 64, color: Colors.grey.shade400),
            const SizedBox(height: 16),
            Text(
              isLive
                  ? 'No live auctions right now'
                  : isUpcoming
                      ? 'No upcoming auctions scheduled'
                      : 'No past auctions recorded',
              style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w600,
                  color: Colors.grey.shade600),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      color: const Color(0xFFD4AF37),
      onRefresh: _fetchAuctions,
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: list.length,
        itemBuilder: (context, index) {
          final a = list[index] as Map<String, dynamic>;
          final currentBid = double.tryParse(a['current_bid']?.toString() ??
                  a['start_price']?.toString() ??
                  '0') ??
              0.0;
          final bidsCount = a['bid_count'] ?? 0;

          return Card(
            margin: const EdgeInsets.only(bottom: 16),
            shape:
                RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            elevation: 3,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Stack(
                  children: [
                    Container(
                      height: 140,
                      decoration: const BoxDecoration(
                        color: Color(0xFF0B194C),
                        borderRadius: BorderRadius.vertical(
                            top: Radius.circular(16)),
                        gradient: LinearGradient(
                          colors: [
                            Color(0xFF0B194C),
                            Color(0xFF1E3A8A),
                          ],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                      ),
                      child: Center(
                        child: Icon(Icons.apartment_rounded,
                            size: 64,
                            color:
                                const Color(0xFFD4AF37).withValues(alpha: 0.6)),
                      ),
                    ),
                    if (isLive)
                      Positioned(
                        top: 12,
                        left: 12,
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 10, vertical: 5),
                          decoration: BoxDecoration(
                            color: Colors.redAccent,
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: const Row(
                            children: [
                              Icon(Icons.fiber_manual_record,
                                  color: Colors.white, size: 10),
                              SizedBox(width: 4),
                              Text('LIVE BIDDING',
                                  style: TextStyle(
                                      color: Colors.white,
                                      fontWeight: FontWeight.bold,
                                      fontSize: 11)),
                            ],
                          ),
                        ),
                      ),
                    Positioned(
                      top: 12,
                      right: 12,
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 10, vertical: 5),
                        decoration: BoxDecoration(
                          color: Colors.black.withValues(alpha: 0.6),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text(
                          '$bidsCount Bids',
                          style: const TextStyle(
                              color: Color(0xFFD4AF37),
                              fontWeight: FontWeight.bold,
                              fontSize: 11),
                        ),
                      ),
                    ),
                  ],
                ),
                Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        a['title']?.toString() ?? 'Auction Plot',
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: Color(0xFF0B194C),
                        ),
                      ),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          const Icon(Icons.location_on_outlined,
                              size: 14, color: Colors.grey),
                          const SizedBox(width: 4),
                          Expanded(
                            child: Text(
                              a['property_location']?.toString() ??
                                  'Gorakhpur, Uttar Pradesh',
                              style: TextStyle(
                                  fontSize: 12, color: Colors.grey.shade600),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: const Color(0xFFFAF7F0),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text('Highest Bid',
                                    style: TextStyle(
                                        fontSize: 11, color: Colors.grey)),
                                const SizedBox(height: 2),
                                Text(
                                  '₹${_formatCurrency(currentBid)}',
                                  style: const TextStyle(
                                    fontSize: 17,
                                    fontWeight: FontWeight.bold,
                                    color: Color(0xFF0B194C),
                                  ),
                                ),
                              ],
                            ),
                            if (isLive)
                              ElevatedButton.icon(
                                onPressed: () =>
                                    _showPlaceBidModal(context, a),
                                icon: const Icon(Icons.gavel_rounded,
                                    size: 16, color: Colors.white),
                                label: const Text('Place Bid',
                                    style: TextStyle(
                                        color: Colors.white,
                                        fontWeight: FontWeight.bold)),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: const Color(0xFFD4AF37),
                                  shape: RoundedRectangleBorder(
                                      borderRadius:
                                          BorderRadius.circular(8)),
                                ),
                              ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:apsdreamhome_app_v2/core/services/api_service.dart';

class FarmerDeskPage extends ConsumerStatefulWidget {
  const FarmerDeskPage({super.key});

  @override
  ConsumerState<FarmerDeskPage> createState() => _FarmerDeskPageState();
}

class _FarmerDeskPageState extends ConsumerState<FarmerDeskPage> {
  bool _isLoading = true;
  Map<String, dynamic>? _dashboardData;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _fetchFarmerData();
  }

  Future<void> _fetchFarmerData() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final api = ApiService();
      final data = await api.get('/api/v2/mobile/farmer/dashboard');
      if (data['success'] == true) {

        setState(() {
          _dashboardData = Map<String, dynamic>.from(data);
          _isLoading = false;
        });
      } else {
        setState(() {
          _errorMessage = 'Could not load landowner data';
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

  void _showOfferLandModal(BuildContext context) {
    final khasraCtrl = TextEditingController();
    final areaCtrl = TextEditingController();
    final villageCtrl = TextEditingController();
    final priceCtrl = TextEditingController();
    final remarksCtrl = TextEditingController();
    String selectedUnit = 'Bigha';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return StatefulBuilder(
          builder: (modalCtx, setModalState) {
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
              child: SingleChildScrollView(
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
                    Row(
                      children: const [
                        Icon(Icons.agriculture_rounded,
                            color: Color(0xFFD4AF37), size: 28),
                        SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            'Offer Land Parcel for Acquisition',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: Color(0xFF0B194C),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'Direct settlement with APS Dream Home at maximum market value.',
                      style: TextStyle(
                          fontSize: 12, color: Colors.grey.shade600),
                    ),
                    const SizedBox(height: 16),
                    TextField(
                      controller: khasraCtrl,
                      decoration: InputDecoration(
                        labelText: 'Khasra / Gata Number *',
                        hintText: 'e.g. 524/1 & 524/2',
                        filled: true,
                        fillColor: Colors.white,
                        border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          flex: 3,
                          child: TextField(
                            controller: areaCtrl,
                            keyboardType: TextInputType.number,
                            decoration: InputDecoration(
                              labelText: 'Land Area *',
                              hintText: 'e.g. 4.5',
                              filled: true,
                              fillColor: Colors.white,
                              border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12)),
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          flex: 2,
                          child: DropdownButtonFormField<String>(
                            value: selectedUnit,
                            decoration: InputDecoration(
                              labelText: 'Unit',
                              filled: true,
                              fillColor: Colors.white,
                              border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12)),
                            ),
                            items: ['Bigha', 'Acre', 'Sq.Ft', 'Dhur']
                                .map((u) => DropdownMenuItem(
                                    value: u, child: Text(u)))
                                .toList(),
                            onChanged: (v) {
                              if (v != null) {
                                setModalState(() => selectedUnit = v);
                              }
                            },
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: villageCtrl,
                      decoration: InputDecoration(
                        labelText: 'Village & Tehsil *',
                        hintText: 'e.g. Mohaddipur, Tehsil Sadar',
                        filled: true,
                        fillColor: Colors.white,
                        border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: priceCtrl,
                      keyboardType: TextInputType.number,
                      decoration: InputDecoration(
                        labelText: 'Expected Price (₹ Total / Acre)',
                        hintText: 'e.g. 3500000',
                        filled: true,
                        fillColor: Colors.white,
                        border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: remarksCtrl,
                      decoration: InputDecoration(
                        labelText: 'Remarks / Road Width',
                        hintText: 'e.g. 40-ft pitch road facing, single owner',
                        filled: true,
                        fillColor: Colors.white,
                        border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                    const SizedBox(height: 20),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton.icon(
                        onPressed: () async {
                          if (khasraCtrl.text.trim().isEmpty ||
                              areaCtrl.text.trim().isEmpty) {
                            ScaffoldMessenger.of(ctx).showSnackBar(
                              const SnackBar(
                                content: Text('Please enter Khasra number and area'),
                                backgroundColor: Colors.redAccent,
                              ),
                            );
                            return;
                          }

                          Navigator.pop(ctx);
                          await _submitLandOffer({
                            'khasra_number': khasraCtrl.text.trim(),
                            'land_area':
                                double.tryParse(areaCtrl.text.trim()) ?? 1.0,
                            'land_area_unit': selectedUnit,
                            'village': villageCtrl.text.trim(),
                            'expected_price':
                                double.tryParse(priceCtrl.text.trim()) ?? 0.0,
                            'remarks': remarksCtrl.text.trim(),
                          });
                        },
                        icon: const Icon(Icons.send_rounded,
                            color: Colors.white),
                        label: const Text('Submit Land for Inspection',
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
              ),
            );
          },
        );
      },
    );
  }

  Future<void> _submitLandOffer(Map<String, dynamic> data) async {
    setState(() => _isLoading = true);
    try {
      final api = ApiService();
      final resData = await api.post(
        '/api/v2/mobile/farmer/offer-land',
        data: data,
      );
      if (resData['success'] == true) {

        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(resData['message']?.toString() ?? 'Submitted!'),
            backgroundColor: const Color(0xFF0B194C),
          ),
        );
        _fetchFarmerData();
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
      );
      setState(() => _isLoading = false);
    }
  }

  String _formatAmount(double amt) {
    if (amt >= 10000000) {
      return '${(amt / 10000000).toStringAsFixed(2)} Cr';
    } else if (amt >= 100000) {
      return '${(amt / 100000).toStringAsFixed(2)} L';
    } else {
      return amt.toStringAsFixed(0);
    }
  }

  @override
  Widget build(BuildContext context) {
    final stats = (_dashboardData?['stats'] as Map<String, dynamic>?) ?? {};
    final holdings = (_dashboardData?['holdings'] as List?) ?? [];
    final benefits = (_dashboardData?['partnership_benefits'] as List?) ?? [];

    final totalArea = double.tryParse(stats['total_area']?.toString() ?? '0') ?? 0.0;
    final totalPaid = double.tryParse(stats['total_paid']?.toString() ?? '0') ?? 0.0;
    final totalPending = double.tryParse(stats['total_pending']?.toString() ?? '0') ?? 0.0;

    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F0),
      appBar: AppBar(
        title: const Text(
          'Farmer & Land Partner Desk',
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
                        onPressed: _fetchFarmerData,
                        child: const Text('Retry'),
                      ),
                    ],
                  ),
                )
              : RefreshIndicator(
                  color: const Color(0xFFD4AF37),
                  onRefresh: _fetchFarmerData,
                  child: SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Stats summary
                        Container(
                          padding: const EdgeInsets.all(18),
                          decoration: BoxDecoration(
                            gradient: const LinearGradient(
                              colors: [Color(0xFF0B194C), Color(0xFF1E3A8A)],
                              begin: Alignment.topLeft,
                              end: Alignment.bottomRight,
                            ),
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      const Text(
                                        'Total Land Partnered',
                                        style: TextStyle(
                                            color: Colors.white70,
                                            fontSize: 12),
                                      ),
                                      const SizedBox(height: 4),
                                      Text(
                                        '${totalArea.toStringAsFixed(1)} Bigha',
                                        style: const TextStyle(
                                          color: Color(0xFFD4AF37),
                                          fontSize: 22,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                    ],
                                  ),
                                  ElevatedButton.icon(
                                    onPressed: () =>
                                        _showOfferLandModal(context),
                                    icon: const Icon(Icons.add,
                                        size: 16, color: Color(0xFF0B194C)),
                                    label: const Text('Offer Land',
                                        style: TextStyle(
                                            color: Color(0xFF0B194C),
                                            fontWeight: FontWeight.bold)),
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor:
                                          const Color(0xFFD4AF37),
                                      shape: RoundedRectangleBorder(
                                          borderRadius:
                                              BorderRadius.circular(8)),
                                    ),
                                  ),
                                ],
                              ),
                              const Divider(
                                  color: Colors.white24, height: 24),
                              Row(
                                children: [
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        const Text('Compensation Received',
                                            style: TextStyle(
                                                color: Colors.white70,
                                                fontSize: 11)),
                                        const SizedBox(height: 2),
                                        Text(
                                          '₹${_formatAmount(totalPaid)}',
                                          style: const TextStyle(
                                              color: Colors.greenAccent,
                                              fontSize: 16,
                                              fontWeight: FontWeight.bold),
                                        ),
                                      ],
                                    ),
                                  ),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        const Text('Pending Disbursal',
                                            style: TextStyle(
                                                color: Colors.white70,
                                                fontSize: 11)),
                                        const SizedBox(height: 2),
                                        Text(
                                          '₹${_formatAmount(totalPending)}',
                                          style: const TextStyle(
                                              color: Colors.orangeAccent,
                                              fontSize: 16,
                                              fontWeight: FontWeight.bold),
                                        ),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 20),

                        // Partnership benefits
                        const Text(
                          'Landowner Guarantees',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                            color: Color(0xFF0B194C),
                          ),
                        ),
                        const SizedBox(height: 10),
                        Container(
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(
                                color: const Color(0xFFD4AF37)
                                    .withValues(alpha: 0.5)),
                          ),
                          child: Column(
                            children: benefits.map<Widget>((b) {
                              return Padding(
                                padding:
                                    const EdgeInsets.symmetric(vertical: 4),
                                child: Row(
                                  children: [
                                    const Icon(Icons.check_circle,
                                        color: Colors.green, size: 16),
                                    const SizedBox(width: 8),
                                    Expanded(
                                      child: Text(
                                        b.toString(),
                                        style: TextStyle(
                                            fontSize: 12,
                                            color: Colors.grey.shade800),
                                      ),
                                    ),
                                  ],
                                ),
                              );
                            }).toList(),
                          ),
                        ),
                        const SizedBox(height: 20),

                        // Land holdings list
                        const Text(
                          'Registered Land Holdings',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                            color: Color(0xFF0B194C),
                          ),
                        ),
                        const SizedBox(height: 10),
                        ...holdings.map((h) {
                          final khasra = h['khasra_number']?.toString() ?? 'N/A';
                          final area = h['land_area']?.toString() ?? '0';
                          final unit = h['land_area_unit']?.toString() ?? 'Bigha';
                          final village = h['village']?.toString() ?? 'Gorakhpur';
                          final status = h['acquisition_status']?.toString() ?? 'under_negotiation';

                          return Card(
                            margin: const EdgeInsets.only(bottom: 12),
                            shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(12)),
                            elevation: 2,
                            child: Padding(
                              padding: const EdgeInsets.all(14),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    mainAxisAlignment:
                                        MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(
                                        'Khasra: $khasra',
                                        style: const TextStyle(
                                          fontSize: 15,
                                          fontWeight: FontWeight.bold,
                                          color: Color(0xFF0B194C),
                                        ),
                                      ),
                                      Container(
                                        padding: const EdgeInsets.symmetric(
                                            horizontal: 8, vertical: 3),
                                        decoration: BoxDecoration(
                                          color: status == 'acquired'
                                              ? Colors.green.withValues(alpha: 0.1)
                                              : Colors.orange.withValues(alpha: 0.1),
                                          borderRadius:
                                              BorderRadius.circular(6),
                                        ),
                                        child: Text(
                                          status.toUpperCase(),
                                          style: TextStyle(
                                            fontSize: 11,
                                            fontWeight: FontWeight.bold,
                                            color: status == 'acquired'
                                                ? Colors.green
                                                : Colors.orange,
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 6),
                                  Text(
                                    '$area $unit • $village',
                                    style: TextStyle(
                                        fontSize: 13,
                                        color: Colors.grey.shade600),
                                  ),
                                  if (h['remarks'] != null) ...[
                                    const SizedBox(height: 6),
                                    Text(
                                      h['remarks'].toString(),
                                      style: TextStyle(
                                          fontSize: 11,
                                          fontStyle: FontStyle.italic,
                                          color: Colors.grey.shade500),
                                    ),
                                  ],
                                ],
                              ),
                            ),
                          );
                        }),
                      ],
                    ),
                  ),
                ),
    );
  }
}

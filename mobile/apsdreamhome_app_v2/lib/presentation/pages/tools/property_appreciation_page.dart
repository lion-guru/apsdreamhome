import 'package:flutter/material.dart';
import 'dart:math';
class PropertyAppreciationPage extends StatefulWidget {
  const PropertyAppreciationPage({super.key});
  @override
  State<PropertyAppreciationPage> createState() => _PropertyAppreciationPageState();
}
class _PropertyAppreciationPageState extends State<PropertyAppreciationPage> {
  double _current=5000000; double _rate=7; int _years=10; double _rent=3;
  double get future => _current * pow(1+_rate/100, _years);
  double get gain => future - _current;
  double get rentTotal => _current * (_rent/100) * _years;
  String fmt(double v){ if(v>=10000000) return '${(v/10000000).toStringAsFixed(2)} Cr'; if(v>=100000) return '${(v/100000).toStringAsFixed(2)} L'; return v.toStringAsFixed(0); }
  @override
  Widget build(BuildContext context){
    return Scaffold(
      appBar: AppBar(title: const Text('Property Appreciation'), backgroundColor: const Color(0xFF667EEA), foregroundColor: Colors.white),
      body: SingleChildScrollView(padding: const EdgeInsets.all(16), child: Column(children:[
        Container(padding: const EdgeInsets.all(20), decoration: BoxDecoration(gradient: const LinearGradient(colors:[Color(0xFF667EEA), Color(0xFF764BA2)]), borderRadius: BorderRadius.circular(16)), child: const Column(crossAxisAlignment:CrossAxisAlignment.start, children:[Icon(Icons.trending_up, size:48, color:Colors.white), SizedBox(height:12), Text('Property Appreciation Calculator', style:TextStyle(fontSize:22, fontWeight:FontWeight.bold, color:Colors.white)), Text('Future me aapki property kitni banegi dekho', style:TextStyle(color:Colors.white70)) ])),
        const SizedBox(height:20),
        Card(child: Padding(padding: const EdgeInsets.all(16), child: Column(children:[
          TextField(keyboardType: TextInputType.number, decoration: const InputDecoration(labelText:'Current Value (₹)', prefixIcon: Icon(Icons.home)), onChanged:(v){ setState(()=> _current=double.tryParse(v)??_current); }),
          const SizedBox(height:16),
          Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children:[const Text('Appreciation Rate', style:TextStyle(fontWeight:FontWeight.bold)), Container(padding: const EdgeInsets.symmetric(horizontal:12,vertical:6), decoration: BoxDecoration(color:const Color(0xFF667EEA).withOpacity(0.1), borderRadius: BorderRadius.circular(20)), child: Text('${_rate.toStringAsFixed(1)}%', style:const TextStyle(fontWeight:FontWeight.bold, color:Color(0xFF667EEA))))]),
          Slider(value:_rate, min:1, max:15, divisions:28, label:'${_rate.toStringAsFixed(1)}%', onChanged:(v)=> setState(()=> _rate=v)),
          const Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children:[Text('1%'), Text('15%')]),
          const SizedBox(height:12),
          Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children:[const Text('Years', style:TextStyle(fontWeight:FontWeight.bold)), Container(padding: const EdgeInsets.symmetric(horizontal:12,vertical:6), decoration: BoxDecoration(color:Colors.green.shade100, borderRadius: BorderRadius.circular(20)), child: Text('$_years yrs', style:TextStyle(fontWeight:FontWeight.bold, color:Colors.green.shade700)))]),
          Slider(value:_years.toDouble(), min:1, max:30, divisions:29, label:'$_years yrs', onChanged:(v)=> setState(()=> _years=v.round())),
          TextField(keyboardType: TextInputType.number, decoration: const InputDecoration(labelText:'Rent Yield % (optional)', prefixIcon: Icon(Icons.percent)), onChanged:(v){ setState(()=> _rent=double.tryParse(v)??_rent); }),
        ]))),
        const SizedBox(height:20),
        Row(children:[
          Expanded(child: Container(padding: const EdgeInsets.all(16), decoration: BoxDecoration(color:const Color(0xFF667EEA), borderRadius: BorderRadius.circular(12)), child: Column(children:[const Text('Future Value', style:TextStyle(color:Colors.white70)), const SizedBox(height:4), Text('₹${fmt(future)}', style:const TextStyle(color:Colors.white, fontSize:18, fontWeight:FontWeight.bold))]))),
          const SizedBox(width:12),
          Expanded(child: Container(padding: const EdgeInsets.all(16), decoration: BoxDecoration(color:Colors.green.shade50, borderRadius: BorderRadius.circular(12), border: Border.all(color:Colors.green.shade200)), child: Column(children:[Text('Total Gain', style:TextStyle(color:Colors.green.shade700, fontSize:12)), const SizedBox(height:4), Text('₹${fmt(gain)}', style:TextStyle(color:Colors.green.shade700, fontSize:18, fontWeight:FontWeight.bold))]))),
        ]),
        const SizedBox(height:12),
        Container(width: double.infinity, padding: const EdgeInsets.all(16), decoration: BoxDecoration(color:Colors.blue.shade50, borderRadius: BorderRadius.circular(12)), child: Column(children:[Text('Rent Income Total', style:TextStyle(color:Colors.blue.shade700, fontSize:12)), Text('₹${fmt(rentTotal)}', style:TextStyle(color:Colors.blue.shade700, fontSize:18, fontWeight:FontWeight.bold)), Text('$_years years @ $_rent%', style:TextStyle(color:Colors.grey.shade600, fontSize:11))])),
        const SizedBox(height:16),
        Container(padding: const EdgeInsets.all(12), decoration: BoxDecoration(color:Colors.amber.shade50, borderRadius: BorderRadius.circular(8), border: Border.all(color:Colors.amber.shade200)), child: Row(children:[Icon(Icons.info, color:Colors.amber.shade700, size:20), const SizedBox(width:8), Expanded(child: Text('Future = Current \u00D7 (1+rate)^years. Gorakhpur avg 5-7%.', style:TextStyle(fontSize:11)))])),
      ])),
    );
  }
}

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/app_theme.dart';

class AccountsPage extends ConsumerWidget {
  const AccountsPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Scaffold(
      body: Column(
        children: [
          // Header
          Container(
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              color: Colors.white,
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.05),
                  blurRadius: 10,
                ),
              ],
            ),
            child: Row(
              children: [
                const Icon(
                  Icons.account_balance,
                  size: 32,
                  color: AppTheme.primaryColor,
                ),
                const SizedBox(width: 16),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Accounts & Finance',
                        style: TextStyle(
                          fontSize: 24,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      SizedBox(height: 4),
                      Text(
                        'Manage payments, invoices, and financial records',
                        style: TextStyle(
                          color: Colors.grey,
                        ),
                      ),
                    ],
                  ),
                ),
                ElevatedButton.icon(
                  onPressed: () {},
                  icon: const Icon(Icons.add),
                  label: const Text('New Entry'),
                ),
              ],
            ),
          ),

          // Stats
          Container(
            padding: const EdgeInsets.all(24),
            child: Row(
              children: [
                _buildStatCard(
                    'Today\'s Collection', '₹1,25,000', Colors.green),
                _buildStatCard('Pending EMI', '₹45,00,000', Colors.orange),
                _buildStatCard('Total Outstanding', '₹2,50,00,000', Colors.red),
                _buildStatCard('Monthly Target', '₹80%', Colors.blue),
              ],
            ),
          ),

          // Tabs
          Expanded(
            child: DefaultTabController(
              length: 4,
              child: Column(
                children: [
                  const TabBar(
                    tabs: [
                      Tab(text: 'Daily Collection'),
                      Tab(text: 'EMI Schedule'),
                      Tab(text: 'Ledger'),
                      Tab(text: 'Invoices'),
                    ],
                  ),
                  Expanded(
                    child: TabBarView(
                      children: [
                        _buildDailyCollection(),
                        _buildEMISchedule(),
                        _buildLedger(),
                        _buildInvoices(),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatCard(String label, String value, Color color) {
    return Expanded(
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 8),
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: color.withValues(alpha: 0.3)),
        ),
        child: Column(
          children: [
            Text(
              value,
              style: TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.bold,
                color: color,
              ),
            ),
            const SizedBox(height: 4),
            Text(label),
          ],
        ),
      ),
    );
  }

  Widget _buildDailyCollection() {
    return ListView.builder(
      padding: const EdgeInsets.all(24),
      itemCount: 10,
      itemBuilder: (context, index) {
        return Card(
          child: ListTile(
            leading: CircleAvatar(
              backgroundColor: Colors.green.withValues(alpha: 0.1),
              child: const Icon(Icons.payment, color: Colors.green),
            ),
            title: Text('Payment #${1000 + index}'),
            subtitle: Text('Customer: Rahul Kumar • Plot: A-${45 + index}'),
            trailing: const Text(
              '₹25,000',
              style: TextStyle(
                fontWeight: FontWeight.bold,
                color: Colors.green,
              ),
            ),
          ),
        );
      },
    );
  }

  Widget _buildEMISchedule() {
    return ListView.builder(
      padding: const EdgeInsets.all(24),
      itemCount: 10,
      itemBuilder: (context, index) {
        final isOverdue = index < 3;
        return Card(
          child: ListTile(
            leading: CircleAvatar(
              backgroundColor: isOverdue
                  ? Colors.red.withValues(alpha: 0.1)
                  : Colors.blue.withValues(alpha: 0.1),
              child: Icon(
                isOverdue ? Icons.warning : Icons.calendar_today,
                color: isOverdue ? Colors.red : Colors.blue,
              ),
            ),
            title: const Text('EMI - Amit Singh'),
            subtitle:
                Text('Due: ${12 + index} May 2026 • Plot: B-${12 + index}'),
            trailing: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Text(
                  '₹25,000',
                  style: TextStyle(
                    fontWeight: FontWeight.bold,
                    color: isOverdue ? Colors.red : Colors.black,
                  ),
                ),
                if (isOverdue)
                  const Text(
                    'OVERDUE',
                    style: TextStyle(
                      color: Colors.red,
                      fontSize: 10,
                    ),
                  ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildLedger() {
    return const Center(
      child: Text('Ledger View - Coming Soon'),
    );
  }

  Widget _buildInvoices() {
    return const Center(
      child: Text('Invoices View - Coming Soon'),
    );
  }
}

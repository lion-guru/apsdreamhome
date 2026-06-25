import 'package:uuid/uuid.dart';

import '../../data/models/colony_model.dart';
import '../../data/models/geo_location.dart';
import '../../data/models/lead_model_extended.dart';
import '../../data/models/plot_model.dart';
import '../../data/models/property_listing_model.dart';
import '../../data/models/user_model.dart' as user_model;

import '../../data/models/daily_caller_model.dart';
import '../../data/models/emi_automation_model.dart';
import '../../data/models/emi_collection_model.dart';

/// Demo Data Generator for Testing
/// Creates sample data for all modules
class DemoDataGenerator {
  static const _uuid = Uuid();

  /// Generate Demo EMI Agents
  static List<EMICollectionAgent> generateDemoEMIAgents() {
    return [
      EMICollectionAgent(
        id: _uuid.v4(),
        name: 'Amit Collection Agent',
        phone: '+91 98765 43220',
        email: 'amit.agent@example.com',
        employeeId: 'EMP001',
        joiningDate: DateTime.now().subtract(const Duration(days: 365)),
        agentType: CollectionAgentType.fullTime,
        assignedArea: const CollectionArea(
          areaName: 'Gorakhpur North',
          state: 'Uttar Pradesh',
          district: 'Gorakhpur',
          city: 'Gorakhpur',
        ),
        monthlySalary: 20000,
        status: AgentStatus.active,
        createdAt: DateTime.now(),
        updatedAt: DateTime.now(),
      ),
    ];
  }

  /// Generate Demo Daily Callers
  static List<DailyCaller> generateDemoDailyCallers() {
    return [
      DailyCaller(
        id: _uuid.v4(),
        name: 'Suresh Telecaller',
        phone: '+91 98765 43221',
        email: 'suresh.caller@example.com',
        employeeId: 'TC001',
        joiningDate: DateTime.now().subtract(const Duration(days: 30)),
        callerType: CallerType.fullTime,
        salaryType: SalaryType.fixedPlusCommission,
        monthlySalary: 15000,
        status: CallerStatus.active,
        createdAt: DateTime.now(),
        updatedAt: DateTime.now(),
      ),
    ];
  }

  /// Generate Demo EMI Rules
  static List<AutomationRule> generateDemoEMIRules() {
    return [
      AutomationRule(
        id: _uuid.v4(),
        name: 'Early Reminder',
        type: 'reminder',
        trigger: 'days_before_due',
        triggerValue: 3,
        scheduleTime: '10:00',
        createdAt: DateTime.now(),
      ),
    ];
  }

  /// Generate Demo Colonies
  static List<ColonyModel> generateDemoColonies() {
    return [
      ColonyModel(
        id: 1,
        name: 'Suryoday Heights',
        location: 'Gorakhpur, Uttar Pradesh',
        district: 'Gorakhpur',
        districtId: 5,
        state: 'Uttar Pradesh',
        description: 'Premium residential plots with modern amenities',
        totalPlots: 100,
        availablePlots: 45,
        holdPlots: 5,
        bookedPlots: 30,
        soldPlots: 20,
        pricePerSqft: 1200,
        isActive: true,
        createdAt: DateTime.now().toIso8601String(),
      ),
      ColonyModel(
        id: 2,
        name: 'Raghunath City Center',
        location: 'Gorakhpur, Uttar Pradesh',
        district: 'Gorakhpur',
        districtId: 5,
        state: 'Uttar Pradesh',
        description: 'Commercial and residential mixed development',
        totalPlots: 200,
        availablePlots: 120,
        holdPlots: 10,
        bookedPlots: 40,
        soldPlots: 30,
        pricePerSqft: 1500,
        isActive: true,
        createdAt: DateTime.now().toIso8601String(),
      ),
      ColonyModel(
        id: 3,
        name: 'Braj Radha Enclave',
        location: 'Lucknow, Uttar Pradesh',
        district: 'Lucknow',
        districtId: 1,
        state: 'Uttar Pradesh',
        description: 'Luxury living with premium facilities',
        totalPlots: 80,
        availablePlots: 25,
        holdPlots: 5,
        bookedPlots: 30,
        soldPlots: 20,
        pricePerSqft: 2500,
        isActive: true,
        createdAt: DateTime.now().toIso8601String(),
      ),
    ];
  }

  /// Generate Demo Plots
  static List<PlotModel> generateDemoPlots(String colonyId) {
    return List.generate(10, (index) {
      final plotNumber = index + 1;
      return PlotModel(
        id: _uuid.v4(),
        colonyId: colonyId,
        colonyName: 'Sample Colony',
        plotNumber: 'P-$plotNumber',
        areaSqft: 1000.0 + (index * 100),
        facing: ['East', 'West', 'North', 'South'][index % 4],
        status: index < 5 ? 'available' : 'booked',
        basePrice: 1200.0,
        createdAt: DateTime.now(),
      );
    });
  }

  /// Generate Demo Users
  static List<user_model.User> generateDemoUsers() {
    return [
      user_model.User(
        userId: _uuid.v4(),
        name: 'Admin User',
        email: 'admin@apsdreamhome.com',
        phone: '+91 98765 43210',
        rank: 'admin',
        target: 0.0,
        createdAt: DateTime.now().toIso8601String(),
        updatedAt: DateTime.now().toIso8601String(),
      ),
      user_model.User(
        userId: _uuid.v4(),
        name: 'Rahul Kumar',
        email: 'rahul@example.com',
        phone: '+91 98765 43211',
        rank: 'customer',
        target: 500000.0,
        createdAt: DateTime.now().toIso8601String(),
        updatedAt: DateTime.now().toIso8601String(),
      ),
      user_model.User(
        userId: _uuid.v4(),
        name: 'Priya Sharma',
        email: 'priya@example.com',
        phone: '+91 98765 43212',
        rank: 'associate',
        target: 1000000.0,
        createdAt: DateTime.now().toIso8601String(),
        updatedAt: DateTime.now().toIso8601String(),
      ),
    ];
  }

  /// Generate Demo Leads
  static List<LeadModel> generateDemoLeads() {
    return [
      LeadModel(
        id: _uuid.v4(),
        name: 'Amit Singh',
        email: 'amit@example.com',
        phone: '+91 98765 43213',
        interestedIn: 'plot',
        budgetMax: 500000,
        status: 'new',
        followUpNotes: 'Looking for 1000 sqft plot in Gorakhpur',
        createdAt: DateTime.now(),
        updatedAt: DateTime.now(),
      ),
      LeadModel(
        id: _uuid.v4(),
        name: 'Sunita Devi',
        email: 'sunita@example.com',
        phone: '+91 98765 43214',
        interestedIn: 'house',
        budgetMax: 750000,
        status: 'contacted',
        followUpNotes: 'Interested in 2 BHK house in Lucknow',
        createdAt: DateTime.now().subtract(const Duration(days: 2)),
        updatedAt: DateTime.now(),
      ),
      LeadModel(
        id: _uuid.v4(),
        name: 'Vikram Patel',
        email: 'vikram@example.com',
        phone: '+91 98765 43215',
        interestedIn: 'commercial',
        budgetMax: 1000000,
        status: 'converted',
        followUpNotes: 'Booked plot in Suryoday Heights, Varanasi',
        createdAt: DateTime.now().subtract(const Duration(days: 5)),
        updatedAt: DateTime.now(),
      ),
    ];
  }

  /// Generate Demo Property Listings
  static List<PropertyListing> generateDemoPropertyListings() {
    return [
      PropertyListing(
        id: _uuid.v4(),
        title: 'Premium Plot for Sale',
        description: '1200 sqft corner plot in prime location',
        propertyType: PropertyType.plot,
        purpose: ListingPurpose.sell,
        ownerId: _uuid.v4(),
        ownerName: 'Rajesh Gupta',
        ownerPhone: '+91 98765 43216',
        ownerEmail: 'rajesh@example.com',
        ownerType: OwnerType.customer,
        state: 'Uttar Pradesh',
        district: 'Gorakhpur',
        city: 'Gorakhpur',
        locality: 'Civil Lines',
        address: 'Near Central School, Civil Lines',
        location: const GeoLocation(latitude: 26.7606, longitude: 83.3732),
        landmark: 'Central School',
        areaSqft: 1200.0,
        expectedPrice: 1440000.0,
        priceType: 'Fixed',
        images: [],
        status: ListingStatus.active,
        viewCount: 45,
        inquiryCount: 3,
        isFeatured: true,
        createdAt: DateTime.now(),
        updatedAt: DateTime.now(),
      ),
    ];
  }

  /// Get All Demo Data as Map
  static Map<String, dynamic> getAllDemoData() {
    final colonyId = _uuid.v4();
    return {
      'colonies': generateDemoColonies(),
      'plots': generateDemoPlots(colonyId),
      'users': generateDemoUsers(),
      'leads': generateDemoLeads(),
      'propertyListings': generateDemoPropertyListings(),
    };
  }
}

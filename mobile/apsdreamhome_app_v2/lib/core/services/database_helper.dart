import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';
import 'package:path_provider/path_provider.dart';

/// Enhanced DatabaseHelper with all repository methods
class DatabaseHelper {
  static final DatabaseHelper _instance = DatabaseHelper._internal();
  static Database? _database;

  factory DatabaseHelper() => _instance;
  DatabaseHelper._internal();

  Future<Database> get database async {
    if (_database != null) return _database!;
    _database = await _initDatabase();
    return _database!;
  }

  Future<Database> _initDatabase() async {
    final documentsDirectory = await getApplicationDocumentsDirectory();
    final path = join(documentsDirectory.path, 'aps_dream_home.db');

    return await openDatabase(
      path,
      version: 3,
      onCreate: _onCreate,
      onUpgrade: _onUpgrade,
    );
  }

  Future<void> _onCreate(Database db, int version) async {
    // Properties Table
    await db.execute('''
      CREATE TABLE properties (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        server_id INTEGER UNIQUE,
        title TEXT NOT NULL,
        description TEXT,
        price REAL,
        location TEXT,
        area_sqft REAL,
        property_type TEXT,
        status TEXT,
        image_url TEXT,
        is_favorite INTEGER DEFAULT 0,
        created_at TEXT,
        updated_at TEXT,
        is_synced INTEGER DEFAULT 0,
        is_deleted INTEGER DEFAULT 0
      )
    ''');

    // Users Table
    await db.execute('''
      CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        server_id INTEGER UNIQUE,
        firebase_uid TEXT UNIQUE,
        name TEXT,
        email TEXT,
        phone TEXT,
        role TEXT,
        profile_image TEXT,
        is_active INTEGER DEFAULT 1,
        last_sync TEXT
      )
    ''');

    // Bookings Table
    await db.execute('''
      CREATE TABLE bookings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        server_id INTEGER,
        property_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        booking_date TEXT,
        status TEXT DEFAULT 'pending',
        amount REAL,
        notes TEXT,
        is_synced INTEGER DEFAULT 0,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
      )
    ''');

    // Leads Table
    await db.execute('''
      CREATE TABLE leads (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        server_id INTEGER,
        name TEXT,
        phone TEXT,
        email TEXT,
        source TEXT,
        status TEXT DEFAULT 'new',
        priority TEXT,
        assigned_to INTEGER,
        notes TEXT,
        follow_up_date TEXT,
        is_synced INTEGER DEFAULT 0,
        created_at TEXT
      )
    ''');

    // Commissions Table
    await db.execute('''
      CREATE TABLE commissions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        server_id INTEGER,
        type TEXT,
        amount REAL,
        description TEXT,
        status TEXT,
        date TEXT,
        user_id INTEGER,
        is_synced INTEGER DEFAULT 0
      )
    ''');

    // Payouts Table
    await db.execute('''
      CREATE TABLE payouts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        server_id INTEGER,
        amount REAL,
        method TEXT,
        status TEXT,
        requested_at TEXT,
        processed_at TEXT,
        user_id INTEGER,
        is_synced INTEGER DEFAULT 0
      )
    ''');

    // Incentives Table
    await db.execute('''
      CREATE TABLE incentives (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        server_id INTEGER,
        name TEXT,
        description TEXT,
        value REAL,
        achieved_at TEXT,
        status TEXT,
        user_id INTEGER,
        is_synced INTEGER DEFAULT 0
      )
    ''');

    // MLM Summary Table
    await db.execute('''
      CREATE TABLE mlm_summary (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER UNIQUE,
        total_earnings REAL DEFAULT 0,
        current_balance REAL DEFAULT 0,
        this_month_earnings REAL DEFAULT 0,
        total_referrals INTEGER DEFAULT 0,
        active_referrals INTEGER DEFAULT 0,
        current_rank TEXT,
        updated_at TEXT
      )
    ''');

    // Call Logs Table
    await db.execute('''
      CREATE TABLE call_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        lead_id TEXT,
        call_type TEXT,
        duration INTEGER,
        notes TEXT,
        outcome TEXT,
        call_time TEXT,
        is_synced INTEGER DEFAULT 0
      )
    ''');

    // Sync Queue Table
    await db.execute('''
      CREATE TABLE sync_queue (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        entity_type TEXT NOT NULL,
        entity_id TEXT NOT NULL,
        action TEXT NOT NULL,
        data TEXT NOT NULL,
        status TEXT DEFAULT 'pending',
        retry_count INTEGER DEFAULT 0,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT
      )
    ''');

    // Indexes
    await db.execute('CREATE INDEX idx_properties_type ON properties(property_type)');
    await db.execute('CREATE INDEX idx_properties_status ON properties(status)');
    await db.execute('CREATE INDEX idx_bookings_user ON bookings(user_id)');
    await db.execute('CREATE INDEX idx_leads_status ON leads(status)');
    await db.execute('CREATE INDEX idx_leads_assigned ON leads(assigned_to)');
    await db.execute('CREATE INDEX idx_sync_queue_status ON sync_queue(status)');
  }

  Future<void> _onUpgrade(Database db, int oldVersion, int newVersion) async {
    if (oldVersion < 2) {
      // Version 2 migrations
    }
    if (oldVersion < 3) {
      // Version 3 migrations
    }
  }

  // ========== PROPERTIES METHODS ==========

  Future<List<Map<String, dynamic>>> getProperties({
    String? type,
    String? status,
    double? minPrice,
    double? maxPrice,
    String? location,
  }) async {
    final db = await database;
    
    String? where;
    List<dynamic>? whereArgs;
    
    final conditions = <String>[];
    final args = <dynamic>[];
    
    conditions.add('is_deleted = ?');
    args.add(0);
    
    if (type != null) {
      conditions.add('property_type = ?');
      args.add(type);
    }
    if (status != null) {
      conditions.add('status = ?');
      args.add(status);
    }
    if (minPrice != null) {
      conditions.add('price >= ?');
      args.add(minPrice);
    }
    if (maxPrice != null) {
      conditions.add('price <= ?');
      args.add(maxPrice);
    }
    if (location != null) {
      conditions.add('location LIKE ?');
      args.add('%$location%');
    }
    
    if (conditions.isNotEmpty) {
      where = conditions.join(' AND ');
      whereArgs = args;
    }
    
    return await db.query(
      'properties',
      where: where,
      whereArgs: whereArgs,
      orderBy: 'created_at DESC',
    );
  }

  Future<Map<String, dynamic>?> getPropertyById(String id) async {
    final db = await database;
    final results = await db.query(
      'properties',
      where: 'server_id = ? AND is_deleted = ?',
      whereArgs: [id, 0],
      limit: 1,
    );
    return results.isNotEmpty ? results.first : null;
  }

  Future<void> saveProperties(List<Map<String, dynamic>> properties) async {
    final db = await database;
    final batch = db.batch();
    
    for (final property in properties) {
      batch.insert(
        'properties',
        property,
        conflictAlgorithm: ConflictAlgorithm.replace,
      );
    }
    
    await batch.commit(noResult: true);
  }

  Future<void> saveProperty(Map<String, dynamic> property) async {
    final db = await database;
    await db.insert(
      'properties',
      property,
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  Future<void> clearAndSaveProperties(List<Map<String, dynamic>> properties) async {
    final db = await database;
    await db.delete('properties');
    await saveProperties(properties);
  }

  Future<void> updatePropertyFavorite(String id, bool isFavorite) async {
    final db = await database;
    await db.update(
      'properties',
      {'is_favorite': isFavorite ? 1 : 0, 'updated_at': DateTime.now().toIso8601String()},
      where: 'server_id = ?',
      whereArgs: [id],
    );
  }

  Future<List<Map<String, dynamic>>> searchProperties(String query) async {
    final db = await database;
    return await db.query(
      'properties',
      where: '(title LIKE ? OR location LIKE ? OR description LIKE ?) AND is_deleted = ?',
      whereArgs: ['%$query%', '%$query%', '%$query%', 0],
    );
  }

  Future<List<Map<String, dynamic>>> getFavoriteProperties() async {
    final db = await database;
    return await db.query(
      'properties',
      where: 'is_favorite = ? AND is_deleted = ?',
      whereArgs: [1, 0],
    );
  }

  Future<List<Map<String, dynamic>>> getPropertiesByColony(String colonyId) async {
    final db = await database;
    return await db.query(
      'properties',
      where: 'location LIKE ? AND is_deleted = ?',
      whereArgs: ['%$colonyId%', 0],
    );
  }

  Future<int> getPropertyCount() async {
    final db = await database;
    final result = await db.rawQuery(
      'SELECT COUNT(*) as count FROM properties WHERE is_deleted = 0'
    );
    return result.first['count'] as int? ?? 0;
  }

  Future<List<Map<String, dynamic>>> filterProperties({
    List<String>? types,
    List<String>? statuses,
    double? minPrice,
    double? maxPrice,
    double? minArea,
    double? maxArea,
  }) async {
    final db = await database;
    
    final conditions = <String>['is_deleted = 0'];
    final args = <dynamic>[];
    
    if (types != null && types.isNotEmpty) {
      conditions.add('property_type IN (${types.map((_) => '?').join(',')})');
      args.addAll(types);
    }
    if (statuses != null && statuses.isNotEmpty) {
      conditions.add('status IN (${statuses.map((_) => '?').join(',')})');
      args.addAll(statuses);
    }
    if (minPrice != null) {
      conditions.add('price >= ?');
      args.add(minPrice);
    }
    if (maxPrice != null) {
      conditions.add('price <= ?');
      args.add(maxPrice);
    }
    if (minArea != null) {
      conditions.add('area_sqft >= ?');
      args.add(minArea);
    }
    if (maxArea != null) {
      conditions.add('area_sqft <= ?');
      args.add(maxArea);
    }
    
    return await db.query(
      'properties',
      where: conditions.join(' AND '),
      whereArgs: args,
    );
  }

  // ========== USERS METHODS ==========

  Future<void> saveUser(Map<String, dynamic> user) async {
    final db = await database;
    // Map UserModel fields to SQLite columns
    final mapped = <String, dynamic>{
      'server_id': int.tryParse(user['userId']?.toString() ?? '') ?? user['id'],
      'name': user['name'],
      'email': user['email'],
      'phone': user['phone'],
      'role': user['rank'] ?? user['role'],
      'profile_image': user['avatar'] ?? user['profile_image'],
      'is_active': 1,
      'last_sync': DateTime.now().toIso8601String(),
    };
    await db.insert(
      'users',
      mapped,
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  Future<Map<String, dynamic>?> getCurrentUser() async {
    final db = await database;
    final results = await db.query(
      'users',
      limit: 1,
    );
    if (results.isEmpty) return null;
    final row = results.first;
    // Map SQLite columns back to UserModel keys
    return {
      'userId': row['server_id']?.toString() ?? '',
      'name': row['name'] ?? '',
      'email': row['email'] ?? '',
      'phone': row['phone'],
      'rank': row['role'] ?? 'Customer',
      'target': 0.0,
      'avatar': row['profile_image'],
      'createdAt': row['last_sync'] ?? DateTime.now().toIso8601String(),
      'updatedAt': row['last_sync'] ?? DateTime.now().toIso8601String(),
    };
  }

  Future<void> clearUserData() async {
    final db = await database;
    await db.delete('users');
  }

  // ========== BOOKINGS METHODS ==========

  Future<List<Map<String, dynamic>>> getMyBookings({
    String? status,
    DateTime? fromDate,
    DateTime? toDate,
  }) async {
    final db = await database;
    
    final conditions = <String>[];
    final args = <dynamic>[];
    
    if (status != null) {
      conditions.add('status = ?');
      args.add(status);
    }
    if (fromDate != null) {
      conditions.add('booking_date >= ?');
      args.add(fromDate.toIso8601String());
    }
    if (toDate != null) {
      conditions.add('booking_date <= ?');
      args.add(toDate.toIso8601String());
    }
    
    return await db.query(
      'bookings',
      where: conditions.isNotEmpty ? conditions.join(' AND ') : null,
      whereArgs: args.isNotEmpty ? args : null,
      orderBy: 'created_at DESC',
    );
  }

  Future<Map<String, dynamic>?> getBookingById(String id) async {
    final db = await database;
    final results = await db.query(
      'bookings',
      where: 'id = ? OR server_id = ?',
      whereArgs: [id, id],
      limit: 1,
    );
    return results.isNotEmpty ? results.first : null;
  }

  Future<void> saveBooking(Map<String, dynamic> booking) async {
    final db = await database;
    await db.insert(
      'bookings',
      booking,
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  Future<void> saveBookings(List<Map<String, dynamic>> bookings) async {
    final db = await database;
    final batch = db.batch();
    
    for (final booking in bookings) {
      batch.insert(
        'bookings',
        booking,
        conflictAlgorithm: ConflictAlgorithm.replace,
      );
    }
    
    await batch.commit(noResult: true);
  }

  Future<void> updateBooking(String id, Map<String, dynamic> data) async {
    final db = await database;
    await db.update(
      'bookings',
      data,
      where: 'id = ? OR server_id = ?',
      whereArgs: [id, id],
    );
  }

  Future<void> updateBookingWithServerData({
    required String localId,
    required Map<String, dynamic> serverBooking,
  }) async {
    final db = await database;
    await db.update(
      'bookings',
      {
        ...serverBooking,
        'is_synced': 1,
      },
      where: 'id = ?',
      whereArgs: [localId],
    );
  }

  Future<void> markBookingAsSynced(String localId, int serverId) async {
    final db = await database;
    await db.update(
      'bookings',
      {
        'server_id': serverId,
        'is_synced': 1,
        'updated_at': DateTime.now().toIso8601String(),
      },
      where: 'id = ?',
      whereArgs: [localId],
    );
  }

  Future<List<Map<String, dynamic>>> getUnsyncedBookings() async {
    final db = await database;
    return await db.query(
      'bookings',
      where: 'is_synced = ?',
      whereArgs: [0],
    );
  }

  Future<Map<String, int>> getBookingStats() async {
    final db = await database;
    final results = await db.rawQuery('''
      SELECT status, COUNT(*) as count 
      FROM bookings 
      GROUP BY status
    ''');
    
    final stats = <String, int>{};
    for (final row in results) {
      stats[row['status'] as String] = row['count'] as int;
    }
    return stats;
  }

  Future<int> getPendingBookingsCount() async {
    final db = await database;
    final result = await db.rawQuery(
      "SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'"
    );
    return result.first['count'] as int? ?? 0;
  }

  Future<void> incrementBookingRetryCount(String localId) async {
    final db = await database;
    await db.rawUpdate('''
      UPDATE bookings 
      SET retry_count = retry_count + 1,
          updated_at = ?
      WHERE id = ?
    ''', [DateTime.now().toIso8601String(), localId]);
  }

  // ========== LEADS METHODS ==========

  Future<List<Map<String, dynamic>>> getMyLeads({
    String? status,
    String? priority,
    DateTime? followUpDate,
  }) async {
    final db = await database;
    
    final conditions = <String>[];
    final args = <dynamic>[];
    
    if (status != null) {
      conditions.add('status = ?');
      args.add(status);
    }
    if (priority != null) {
      conditions.add('priority = ?');
      args.add(priority);
    }
    if (followUpDate != null) {
      conditions.add('follow_up_date LIKE ?');
      args.add('${followUpDate.toIso8601String().substring(0, 10)}%');
    }
    
    return await db.query(
      'leads',
      where: conditions.isNotEmpty ? conditions.join(' AND ') : null,
      whereArgs: args.isNotEmpty ? args : null,
      orderBy: 'created_at DESC',
    );
  }

  Future<Map<String, dynamic>?> getLeadById(String id) async {
    final db = await database;
    final results = await db.query(
      'leads',
      where: 'id = ? OR server_id = ?',
      whereArgs: [id, id],
      limit: 1,
    );
    return results.isNotEmpty ? results.first : null;
  }

  Future<void> saveLead(Map<String, dynamic> lead) async {
    final db = await database;
    await db.insert(
      'leads',
      lead,
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  Future<void> saveLeads(List<Map<String, dynamic>> leads) async {
    final db = await database;
    final batch = db.batch();
    
    for (final lead in leads) {
      batch.insert(
        'leads',
        lead,
        conflictAlgorithm: ConflictAlgorithm.replace,
      );
    }
    
    await batch.commit(noResult: true);
  }

  Future<void> updateLead(String id, Map<String, dynamic> data) async {
    final db = await database;
    await db.update(
      'leads',
      data,
      where: 'id = ? OR server_id = ?',
      whereArgs: [id, id],
    );
  }

  Future<void> updateLeadWithServerData({
    required String localId,
    required Map<String, dynamic> serverLead,
  }) async {
    final db = await database;
    await db.update(
      'leads',
      {
        ...serverLead,
        'is_synced': 1,
      },
      where: 'id = ?',
      whereArgs: [localId],
    );
  }

  Future<void> markLeadAsSynced(String localId, int serverId) async {
    final db = await database;
    await db.update(
      'leads',
      {
        'server_id': serverId,
        'is_synced': 1,
        'updated_at': DateTime.now().toIso8601String(),
      },
      where: 'id = ?',
      whereArgs: [localId],
    );
  }

  Future<List<Map<String, dynamic>>> getUnsyncedLeads() async {
    final db = await database;
    return await db.query(
      'leads',
      where: 'is_synced = ?',
      whereArgs: [0],
    );
  }

  Future<List<Map<String, dynamic>>> searchLeads(String query) async {
    final db = await database;
    return await db.query(
      'leads',
      where: 'name LIKE ? OR phone LIKE ? OR email LIKE ?',
      whereArgs: ['%$query%', '%$query%', '%$query%'],
    );
  }

  Future<void> addCallLog(Map<String, dynamic> callLog) async {
    final db = await database;
    await db.insert('call_logs', callLog);
  }

  Future<void> incrementLeadRetryCount(String localId) async {
    final db = await database;
    await db.rawUpdate('''
      UPDATE leads 
      SET retry_count = retry_count + 1,
          updated_at = ?
      WHERE id = ?
    ''', [DateTime.now().toIso8601String(), localId]);
  }

  // ========== MLM METHODS ==========

  Future<Map<String, dynamic>?> getMlmSummary() async {
    final db = await database;
    final results = await db.query(
      'mlm_summary',
      limit: 1,
    );
    return results.isNotEmpty ? results.first : null;
  }

  Future<void> saveMlmSummary(Map<String, dynamic> summary) async {
    final db = await database;
    await db.insert(
      'mlm_summary',
      summary,
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  Future<List<Map<String, dynamic>>> getCommissions({
    DateTime? fromDate,
    DateTime? toDate,
    String? status,
  }) async {
    final db = await database;
    
    final conditions = <String>[];
    final args = <dynamic>[];
    
    if (fromDate != null) {
      conditions.add('date >= ?');
      args.add(fromDate.toIso8601String());
    }
    if (toDate != null) {
      conditions.add('date <= ?');
      args.add(toDate.toIso8601String());
    }
    if (status != null) {
      conditions.add('status = ?');
      args.add(status);
    }
    
    return await db.query(
      'commissions',
      where: conditions.isNotEmpty ? conditions.join(' AND ') : null,
      whereArgs: args.isNotEmpty ? args : null,
      orderBy: 'date DESC',
    );
  }

  Future<void> saveCommissions(List<Map<String, dynamic>> commissions) async {
    final db = await database;
    final batch = db.batch();
    
    for (final commission in commissions) {
      batch.insert(
        'commissions',
        commission,
        conflictAlgorithm: ConflictAlgorithm.replace,
      );
    }
    
    await batch.commit(noResult: true);
  }

  Future<List<Map<String, dynamic>>> getPayouts() async {
    final db = await database;
    return await db.query(
      'payouts',
      orderBy: 'requested_at DESC',
    );
  }

  Future<void> savePayouts(List<Map<String, dynamic>> payouts) async {
    final db = await database;
    final batch = db.batch();
    
    for (final payout in payouts) {
      batch.insert(
        'payouts',
        payout,
        conflictAlgorithm: ConflictAlgorithm.replace,
      );
    }
    
    await batch.commit(noResult: true);
  }

  Future<List<Map<String, dynamic>>> getIncentives() async {
    final db = await database;
    return await db.query(
      'incentives',
      orderBy: 'achieved_at DESC',
    );
  }

  Future<void> saveIncentives(List<Map<String, dynamic>> incentives) async {
    final db = await database;
    final batch = db.batch();
    
    for (final incentive in incentives) {
      batch.insert(
        'incentives',
        incentive,
        conflictAlgorithm: ConflictAlgorithm.replace,
      );
    }
    
    await batch.commit(noResult: true);
  }

  // ========== SYNC QUEUE METHODS ==========

  Future<void> addToSyncQueue({
    required String entityType,
    required String entityId,
    required String action,
    required Map<String, dynamic> data,
  }) async {
    final db = await database;
    await db.insert('sync_queue', {
      'entity_type': entityType,
      'entity_id': entityId,
      'action': action,
      'data': data.toString(),
      'status': 'pending',
      'created_at': DateTime.now().toIso8601String(),
    });
  }

  Future<List<Map<String, dynamic>>> getSyncQueue({
    String? status,
    int? limit,
  }) async {
    final db = await database;
    return await db.query(
      'sync_queue',
      where: status != null ? 'status = ?' : null,
      whereArgs: status != null ? [status] : null,
      limit: limit,
      orderBy: 'created_at ASC',
    );
  }

  Future<void> updateSyncQueueStatus(int id, String status) async {
    final db = await database;
    await db.update(
      'sync_queue',
      {
        'status': status,
        'updated_at': DateTime.now().toIso8601String(),
      },
      where: 'id = ?',
      whereArgs: [id],
    );
  }

  // ========== GENERIC METHODS ==========

  static Future<List<Map<String, dynamic>>> query(
    String table, {
    bool? distinct,
    List<String>? columns,
    String? where,
    List<dynamic>? whereArgs,
    String? groupBy,
    String? having,
    String? orderBy,
    int? limit,
    int? offset,
  }) async {
    final db = await _instance.database;
    return await db.query(
      table,
      distinct: distinct,
      columns: columns,
      where: where,
      whereArgs: whereArgs,
      groupBy: groupBy,
      having: having,
      orderBy: orderBy,
      limit: limit,
      offset: offset,
    );
  }

  static Future<int> insert(
    String table,
    Map<String, dynamic> values, {
    ConflictAlgorithm? conflictAlgorithm,
  }) async {
    final db = await _instance.database;
    return await db.insert(
      table,
      values,
      conflictAlgorithm: conflictAlgorithm,
    );
  }

  static Future<int> update(
    String table,
    Map<String, dynamic> values, {
    String? where,
    List<dynamic>? whereArgs,
  }) async {
    final db = await _instance.database;
    return await db.update(table, values, where: where, whereArgs: whereArgs);
  }

  static Future<int> delete(
    String table, {
    String? where,
    List<dynamic>? whereArgs,
  }) async {
    final db = await _instance.database;
    return await db.delete(table, where: where, whereArgs: whereArgs);
  }

  Future<void> clearAll() async {
    final db = await database;
    await db.delete('properties');
    await db.delete('users');
    await db.delete('bookings');
    await db.delete('leads');
    await db.delete('commissions');
    await db.delete('payouts');
    await db.delete('incentives');
    await db.delete('mlm_summary');
    await db.delete('call_logs');
    await db.delete('sync_queue');
  }

  Future<void> close() async {
    final db = await database;
    await db.close();
    _database = null;
  }
}

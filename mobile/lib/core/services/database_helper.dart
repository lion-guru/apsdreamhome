import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';
import '../constants/app_constants.dart';

class DatabaseHelper {
  static Database? _database;
  
  static Future<Database> get database async {
    if (_database != null) return _database!;
    
    _database = await _initDatabase();
    return _database!;
  }
  
  static Future<Database> _initDatabase() async {
    final path = join(await getDatabasesPath(), AppConstants.databaseName);
    
    return await openDatabase(
      path,
      version: AppConstants.databaseVersion,
      onCreate: _onCreate,
      onUpgrade: _onUpgrade,
    );
  }
  
  static Future<void> _onCreate(Database db, int version) async {
    // Users table
    await db.execute('''
      CREATE TABLE ${AppConstants.usersTable} (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id TEXT UNIQUE NOT NULL,
        name TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        phone TEXT,
        rank TEXT NOT NULL,
        target REAL,
        avatar TEXT,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
      )
    ''');
    
    // Properties table
    await db.execute('''
      CREATE TABLE ${AppConstants.propertiesTable} (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        property_id TEXT UNIQUE NOT NULL,
        title TEXT NOT NULL,
        description TEXT,
        type TEXT NOT NULL,
        price REAL NOT NULL,
        size REAL,
        location TEXT NOT NULL,
        status TEXT NOT NULL,
        image_url TEXT,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL,
        last_synced_at TEXT
      )
    ''');
    
    // Leads table
    await db.execute('''
      CREATE TABLE ${AppConstants.leadsTable} (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        lead_id TEXT UNIQUE NOT NULL,
        name TEXT NOT NULL,
        email TEXT,
        phone TEXT NOT NULL,
        property_interest TEXT,
        budget REAL,
        status TEXT NOT NULL,
        notes TEXT,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL,
        last_synced_at TEXT,
        is_synced INTEGER DEFAULT 0
      )
    ''');
    
    // Commissions table
    await db.execute('''
      CREATE TABLE ${AppConstants.commissionsTable} (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        commission_id TEXT UNIQUE NOT NULL,
        user_id TEXT NOT NULL,
        source_id TEXT NOT NULL,
        source_type TEXT NOT NULL,
        amount REAL NOT NULL,
        percentage REAL NOT NULL,
        rank TEXT NOT NULL,
        status TEXT NOT NULL,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL,
        last_synced_at TEXT
      )
    ''');
    
    // Incentives table
    await db.execute('''
      CREATE TABLE ${AppConstants.incentivesTable} (
        id INTEGER PRIMARY KEY,
        user_id INTEGER NOT NULL,
        month INTEGER NOT NULL,
        year INTEGER NOT NULL,
        rank_at_time TEXT,
        target_business REAL,
        achieved_business REAL,
        incentive_amount REAL,
        status TEXT,
        remarks TEXT,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
      )
    ''');
    
    // Sync queue table
    await db.execute('''
      CREATE TABLE ${AppConstants.syncQueueTable} (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        entity_type TEXT NOT NULL,
        entity_id TEXT NOT NULL,
        action TEXT NOT NULL,
        data TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT 'pending',
        retry_count INTEGER DEFAULT 0,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
      )
    ''');
    
    // Create indexes
    await db.execute('CREATE INDEX idx_users_user_id ON ${AppConstants.usersTable}(user_id)');
    await db.execute('CREATE INDEX idx_properties_property_id ON ${AppConstants.propertiesTable}(property_id)');
    await db.execute('CREATE INDEX idx_leads_lead_id ON ${AppConstants.leadsTable}(lead_id)');
    await db.execute('CREATE INDEX idx_commissions_commission_id ON ${AppConstants.commissionsTable}(commission_id)');
    await db.execute('CREATE INDEX idx_incentives_user_id ON ${AppConstants.incentivesTable}(user_id)');
    await db.execute('CREATE INDEX idx_sync_queue_status ON ${AppConstants.syncQueueTable}(status)');
  }
  
  static Future<void> _onUpgrade(Database db, int oldVersion, int newVersion) async {
    // Handle database upgrades
    if (oldVersion < 2) {
      // Add incentives table for version 2
      await db.execute('''
        CREATE TABLE ${AppConstants.incentivesTable} (
          id INTEGER PRIMARY KEY,
          user_id INTEGER NOT NULL,
          month INTEGER NOT NULL,
          year INTEGER NOT NULL,
          rank_at_time TEXT,
          target_business REAL,
          achieved_business REAL,
          incentive_amount REAL,
          status TEXT,
          remarks TEXT,
          created_at TEXT NOT NULL,
          updated_at TEXT NOT NULL
        )
      ''');
      await db.execute('CREATE INDEX idx_incentives_user_id ON ${AppConstants.incentivesTable}(user_id)');
    }
  }
  
  // Generic query methods
  static Future<List<Map<String, dynamic>>> query(String table, {
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
    final db = await database;
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
  
  static Future<int> insert(String table, Map<String, dynamic> values) async {
    final db = await database;
    return await db.insert(table, values);
  }
  
  static Future<int> update(String table, Map<String, dynamic> values, {
    String? where,
    List<dynamic>? whereArgs,
  }) async {
    final db = await database;
    return await db.update(table, values, where: where, whereArgs: whereArgs);
  }
  
  static Future<int> delete(String table, {
    String? where,
    List<dynamic>? whereArgs,
  }) async {
    final db = await database;
    return await db.delete(table, where: where, whereArgs: whereArgs);
  }
  
  static Future<void> close() async {
    final db = await database;
    await db.close();
    _database = null;
  }
}

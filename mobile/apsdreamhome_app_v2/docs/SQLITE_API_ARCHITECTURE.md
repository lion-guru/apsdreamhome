# 📱 SQLite + PHP/MySQL Architecture Guide
## APS Dream Home Mobile App

---

## 🏗️ Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                    FLUTTER APP (Mobile)                     │
├─────────────────────────────────────────────────────────────┤
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐   │
│  │   SQLite     │  │  Providers   │  │  Repository  │   │
│  │  (Local DB)  │◄─┤  (Riverpod)  │◄─┤   Pattern    │   │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘   │
│         │                 │                 │          │
│         └────────┬────────┴────────┬────────┘          │
│                  │                 │                   │
│                  ▼                 ▼                   │
│  ┌──────────────────────────────────────────────┐   │
│  │         API Service (REST/HTTP)               │   │
│  └──────────────────────┬─────────────────────────┘   │
└─────────────────────────┼───────────────────────────────┘
                          │ HTTPS/JSON
                          ▼
┌─────────────────────────────────────────────────────────────┐
│                    PHP BACKEND (Server)                     │
├─────────────────────────────────────────────────────────────┤
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐   │
│  │   Router     │  │ Controllers  │  │    Models    │   │
│  │   (web.php)  │──┤   (API)      │──┤  (Eloquent)  │   │
│  └──────────────┘  └──────┬───────┘  └──────┬───────┘   │
│                           │                 │          │
│                           └────────┬────────┘          │
│                                    │                   │
│                                    ▼                   │
│  ┌──────────────────────────────────────────────┐   │
│  │              MySQL Database                   │   │
│  │  (Properties, Users, Bookings, Leads, etc.)  │   │
│  └──────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

---

## 📦 SQLite Setup (Flutter)

### 1. Add Dependencies

```yaml
# pubspec.yaml
dependencies:
  # SQLite
  sqflite: ^2.3.0
  path_provider: ^2.1.1
  path: ^1.8.3
  
  # HTTP Client
  dio: ^5.4.0
  retrofit: ^4.0.3
  
  # Connectivity
  connectivity_plus: ^5.0.2
  
  # JSON Serialization
  json_annotation: ^4.8.1
  freezed_annotation: ^2.4.1

dev_dependencies:
  retrofit_generator: ^8.0.6
  json_serializable: ^6.7.1
  build_runner: ^2.4.7
  freezed: ^2.4.5
```

### 2. Database Helper Class

```dart
// lib/core/database/database_helper.dart
import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';
import 'package:path_provider/path_provider.dart';

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
      version: 1,
      onCreate: _onCreate,
      onUpgrade: _onUpgrade,
    );
  }

  Future<void> _onCreate(Database db, int version) async {
    // Properties Table
    await db.execute('''
      CREATE TABLE properties (
        id INTEGER PRIMARY KEY,
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

    // Users Table (Cache)
    await db.execute('''
      CREATE TABLE users (
        id INTEGER PRIMARY KEY,
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

    // Bookings Table (Offline Support)
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

    // Leads Table (Telecalling)
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

    // Sync Queue (Pending Operations)
    await db.execute('''
      CREATE TABLE sync_queue (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        table_name TEXT NOT NULL,
        operation TEXT NOT NULL, -- CREATE, UPDATE, DELETE
        data TEXT NOT NULL, -- JSON
        retry_count INTEGER DEFAULT 0,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP,
        synced_at TEXT
      )
    ''');

    // Indexes for Performance
    await db.execute('CREATE INDEX idx_properties_type ON properties(property_type)');
    await db.execute('CREATE INDEX idx_properties_status ON properties(status)');
    await db.execute('CREATE INDEX idx_bookings_user ON bookings(user_id)');
    await db.execute('CREATE INDEX idx_leads_status ON leads(status)');
  }

  Future<void> _onUpgrade(Database db, int oldVersion, int newVersion) async {
    // Handle migrations
    if (oldVersion < 2) {
      // Add new columns or tables for version 2
    }
  }

  // Clear all data (Logout)
  Future<void> clearAllData() async {
    final db = await database;
    await db.delete('properties');
    await db.delete('users');
    await db.delete('bookings');
    await db.delete('leads');
    await db.delete('sync_queue');
  }
}
```

### 3. Property DAO (Data Access Object)

```dart
// lib/data/dao/property_dao.dart
import 'package:sqflite/sqflite.dart';
import '../../core/database/database_helper.dart';
import '../models/property_model.dart';

class PropertyDao {
  final DatabaseHelper _dbHelper = DatabaseHelper();

  // INSERT
  Future<int> insertProperty(PropertyModel property) async {
    final db = await _dbHelper.database;
    return await db.insert(
      'properties',
      property.toLocalDbMap(),
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  // INSERT BATCH (For API Sync)
  Future<void> insertPropertiesBatch(List<PropertyModel> properties) async {
    final db = await _dbHelper.database;
    final batch = db.batch();

    for (var property in properties) {
      batch.insert(
        'properties',
        property.toLocalDbMap(),
        conflictAlgorithm: ConflictAlgorithm.replace,
      );
    }

    await batch.commit(noResult: true);
  }

  // GET ALL
  Future<List<PropertyModel>> getAllProperties() async {
    final db = await _dbHelper.database;
    final List<Map<String, dynamic>> maps = await db.query(
      'properties',
      where: 'is_deleted = ?',
      whereArgs: [0],
      orderBy: 'created_at DESC',
    );
    return List.generate(maps.length, (i) => PropertyModel.fromLocalDb(maps[i]));
  }

  // GET BY ID
  Future<PropertyModel?> getPropertyById(int id) async {
    final db = await _dbHelper.database;
    final List<Map<String, dynamic>> maps = await db.query(
      'properties',
      where: 'id = ? AND is_deleted = ?',
      whereArgs: [id, 0],
      limit: 1,
    );
    if (maps.isNotEmpty) {
      return PropertyModel.fromLocalDb(maps.first);
    }
    return null;
  }

  // GET BY TYPE
  Future<List<PropertyModel>> getPropertiesByType(String type) async {
    final db = await _dbHelper.database;
    final List<Map<String, dynamic>> maps = await db.query(
      'properties',
      where: 'property_type = ? AND is_deleted = ?',
      whereArgs: [type, 0],
    );
    return List.generate(maps.length, (i) => PropertyModel.fromLocalDb(maps[i]));
  }

  // SEARCH
  Future<List<PropertyModel>> searchProperties(String query) async {
    final db = await _dbHelper.database;
    final List<Map<String, dynamic>> maps = await db.query(
      'properties',
      where: 'title LIKE ? OR location LIKE ? OR description LIKE ?',
      whereArgs: ['%$query%', '%$query%', '%$query%'],
    );
    return List.generate(maps.length, (i) => PropertyModel.fromLocalDb(maps[i]));
  }

  // UPDATE
  Future<int> updateProperty(PropertyModel property) async {
    final db = await _dbHelper.database;
    return await db.update(
      'properties',
      property.toLocalDbMap(),
      where: 'id = ?',
      whereArgs: [property.localId],
    );
  }

  // DELETE (Soft)
  Future<int> softDeleteProperty(int id) async {
    final db = await _dbHelper.database;
    return await db.update(
      'properties',
      {'is_deleted': 1, 'updated_at': DateTime.now().toIso8601String()},
      where: 'id = ?',
      whereArgs: [id],
    );
  }

  // GET UNSYNCED
  Future<List<PropertyModel>> getUnsyncedProperties() async {
    final db = await _dbHelper.database;
    final List<Map<String, dynamic>> maps = await db.query(
      'properties',
      where: 'is_synced = ?',
      whereArgs: [0],
    );
    return List.generate(maps.length, (i) => PropertyModel.fromLocalDb(maps[i]));
  }

  // MARK AS SYNCED
  Future<void> markAsSynced(int localId, int serverId) async {
    final db = await _dbHelper.database;
    await db.update(
      'properties',
      {
        'is_synced': 1,
        'server_id': serverId,
        'updated_at': DateTime.now().toIso8601String(),
      },
      where: 'id = ?',
      whereArgs: [localId],
    );
  }

  // GET COUNT
  Future<int> getPropertyCount() async {
    final db = await _dbHelper.database;
    final result = await db.rawQuery(
      'SELECT COUNT(*) as count FROM properties WHERE is_deleted = 0'
    );
    return Sqflite.firstIntValue(result) ?? 0;
  }

  // DELETE ALL (For refresh)
  Future<void> deleteAllProperties() async {
    final db = await _dbHelper.database;
    await db.delete('properties');
  }
}
```

### 4. Property Model with Local DB Support

```dart
// lib/data/models/property_model.dart
import 'package:freezed_annotation/freezed_annotation.dart';

part 'property_model.freezed.dart';
part 'property_model.g.dart';

@freezed
class PropertyModel with _$PropertyModel {
  const factory PropertyModel({
    int? localId, // SQLite ID
    int? serverId, // MySQL ID
    required String title,
    String? description,
    double? price,
    String? location,
    double? areaSqft,
    String? propertyType,
    String? status,
    String? imageUrl,
    @Default(false) bool isFavorite,
    DateTime? createdAt,
    DateTime? updatedAt,
    @Default(false) bool isSynced,
    @Default(false) bool isDeleted,
  }) = _PropertyModel;

  factory PropertyModel.fromJson(Map<String, dynamic> json) =>
      _$PropertyModelFromJson(json);

  // From Local DB (SQLite)
  factory PropertyModel.fromLocalDb(Map<String, dynamic> map) {
    return PropertyModel(
      localId: map['id'] as int?,
      serverId: map['server_id'] as int?,
      title: map['title'] as String,
      description: map['description'] as String?,
      price: map['price'] as double?,
      location: map['location'] as String?,
      areaSqft: map['area_sqft'] as double?,
      propertyType: map['property_type'] as String?,
      status: map['status'] as String?,
      imageUrl: map['image_url'] as String?,
      isFavorite: (map['is_favorite'] as int?) == 1,
      createdAt: map['created_at'] != null
          ? DateTime.parse(map['created_at'] as String)
          : null,
      updatedAt: map['updated_at'] != null
          ? DateTime.parse(map['updated_at'] as String)
          : null,
      isSynced: (map['is_synced'] as int?) == 1,
      isDeleted: (map['is_deleted'] as int?) == 1,
    );
  }

  // To Local DB (SQLite)
  Map<String, dynamic> toLocalDbMap() {
    return {
      'id': localId,
      'server_id': serverId,
      'title': title,
      'description': description,
      'price': price,
      'location': location,
      'area_sqft': areaSqft,
      'property_type': propertyType,
      'status': status,
      'image_url': imageUrl,
      'is_favorite': isFavorite ? 1 : 0,
      'created_at': createdAt?.toIso8601String(),
      'updated_at': updatedAt?.toIso8601String(),
      'is_synced': isSynced ? 1 : 0,
      'is_deleted': isDeleted ? 1 : 0,
    };
  }
}
```

---

## 🔌 PHP REST API Architecture

### 1. API Routes (Laravel Style)

```php
<?php
// routes/api.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SyncController;

// Auth Routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/auth/refresh', [AuthController::class, 'refreshToken']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    
    // User
    Route::get('/user/profile', [AuthController::class, 'profile']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    
    // Properties
    Route::get('/properties', [PropertyController::class, 'index']);
    Route::get('/properties/{id}', [PropertyController::class, 'show']);
    Route::get('/properties/search', [PropertyController::class, 'search']);
    Route::get('/properties/filter', [PropertyController::class, 'filter']);
    
    // Bookings
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{id}', [BookingController::class, 'show']);
    Route::put('/bookings/{id}', [BookingController::class, 'update']);
    Route::delete('/bookings/{id}', [BookingController::class, 'destroy']);
    
    // Leads
    Route::get('/leads', [LeadController::class, 'index']);
    Route::post('/leads', [LeadController::class, 'store']);
    Route::put('/leads/{id}', [LeadController::class, 'update']);
    
    // Sync (For offline support)
    Route::post('/sync/batch', [SyncController::class, 'batchSync']);
    Route::get('/sync/changes', [SyncController::class, 'getChanges']);
    Route::post('/sync/conflict-resolve', [SyncController::class, 'resolveConflict']);
});
```

### 2. API Response Format (JSON:API Standard)

```php
<?php
// app/Traits/ApiResponse.php

trait ApiResponse
{
    protected function successResponse($data, $message = null, $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toIso8601String(),
        ], $code);
    }

    protected function errorResponse($message, $code = 400, $errors = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => now()->toIso8601String(),
        ], $code);
    }

    protected function paginatedResponse($paginator, $message = null)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
```

### 3. Property API Controller

```php
<?php
// app/Http/Controllers/Api/PropertyController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    use ApiResponse;

    // GET /api/properties
    public function index(Request $request)
    {
        $query = Property::where('is_active', true)
            ->with(['colony', 'district', 'images']);

        // Filters
        if ($request->has('type')) {
            $query->where('property_type', $request->type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 20);
        $properties = $query->paginate($perPage);

        return $this->paginatedResponse($properties, 'Properties fetched successfully');
    }

    // GET /api/properties/{id}
    public function show($id)
    {
        $property = Property::with(['colony', 'district', 'images', 'amenities'])
            ->findOrFail($id);

        return $this->successResponse($property, 'Property details fetched');
    }

    // GET /api/properties/search
    public function search(Request $request)
    {
        $request->validate(['q' => 'required|string|min:2']);

        $query = $request->get('q');
        
        $properties = Property::where(function($q) use ($query) {
            $q->where('title', 'like', '%' . $query . '%')
              ->orWhere('description', 'like', '%' . $query . '%')
              ->orWhere('location', 'like', '%' . $query . '%');
        })
        ->where('is_active', true)
        ->limit(50)
        ->get();

        return $this->successResponse($properties, 'Search results');
    }
}
```

### 4. Sync Controller (For Offline Support)

```php
<?php
// app/Http/Controllers/Api/SyncController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\Booking;
use App\Models\Lead;

class SyncController extends Controller
{
    use ApiResponse;

    // POST /api/sync/batch
    public function batchSync(Request $request)
    {
        $request->validate([
            'operations' => 'required|array',
            'operations.*.table' => 'required|string',
            'operations.*.action' => 'required|in:create,update,delete',
            'operations.*.data' => 'required|array',
            'operations.*.local_id' => 'required|integer',
        ]);

        $operations = $request->get('operations');
        $results = [];
        $errors = [];

        foreach ($operations as $operation) {
            try {
                $result = $this->processOperation($operation);
                $results[] = [
                    'local_id' => $operation['local_id'],
                    'server_id' => $result['server_id'] ?? null,
                    'status' => 'success',
                    'action' => $operation['action'],
                ];
            } catch (\Exception $e) {
                $errors[] = [
                    'local_id' => $operation['local_id'],
                    'error' => $e->getMessage(),
                    'status' => 'failed',
                ];
            }
        }

        return $this->successResponse([
            'successful' => $results,
            'failed' => $errors,
            'total' => count($operations),
            'success_count' => count($results),
            'failed_count' => count($errors),
        ], 'Batch sync completed');
    }

    // GET /api/sync/changes?since=2024-01-01T00:00:00Z
    public function getChanges(Request $request)
    {
        $since = $request->get('since');
        
        $changes = [
            'properties' => [],
            'bookings' => [],
            'leads' => [],
            'deleted' => [],
        ];

        if ($since) {
            $sinceDate = \Carbon\Carbon::parse($since);

            $changes['properties'] = Property::where('updated_at', '>', $sinceDate)
                ->with(['colony', 'images'])
                ->get();

            $changes['bookings'] = Booking::where('updated_at', '>', $sinceDate)
                ->where('user_id', auth()->id())
                ->get();

            $changes['leads'] = Lead::where('updated_at', '>', $sinceDate)
                ->where('assigned_to', auth()->id())
                ->get();
        } else {
            // First sync - get all
            $changes['properties'] = Property::where('is_active', true)
                ->with(['colony', 'images'])
                ->get();
        }

        return $this->successResponse($changes, 'Sync data fetched');
    }

    private function processOperation($operation)
    {
        $table = $operation['table'];
        $action = $operation['action'];
        $data = $operation['data'];

        switch ($table) {
            case 'bookings':
                return $this->processBooking($action, $data);
            case 'leads':
                return $this->processLead($action, $data);
            default:
                throw new \Exception("Unknown table: $table");
        }
    }

    private function processBooking($action, $data)
    {
        switch ($action) {
            case 'create':
                $booking = Booking::create($data);
                return ['server_id' => $booking->id];
            case 'update':
                $booking = Booking::findOrFail($data['id']);
                $booking->update($data);
                return ['server_id' => $booking->id];
            case 'delete':
                Booking::destroy($data['id']);
                return ['server_id' => $data['id']];
        }
    }
}
```

---

## 📱 Flutter API Service

### 1. API Client (Dio + Retrofit)

```dart
// lib/data/services/api_service.dart
import 'package:dio/dio.dart';
import 'package:retrofit/retrofit.dart';
import '../models/property_model.dart';
import '../models/booking_model.dart';
import '../models/api_response.dart';

part 'api_service.g.dart';

@RestApi(baseUrl: "https://your-api.com/api")
abstract class ApiService {
  factory ApiService(Dio dio, {String baseUrl}) = _ApiService;

  // Auth
  @POST("/auth/login")
  Future<ApiResponse<Map<String, dynamic>>> login(@Body() Map<String, dynamic> body);

  // Properties
  @GET("/properties")
  Future<ApiResponse<PaginatedResponse<PropertyModel>>> getProperties(
    @Queries() Map<String, dynamic> queries,
  );

  @GET("/properties/{id}")
  Future<ApiResponse<PropertyModel>> getProperty(@Path("id") int id);

  @GET("/properties/search")
  Future<ApiResponse<List<PropertyModel>>> searchProperties(
    @Query("q") String query,
  );

  // Bookings
  @GET("/bookings")
  Future<ApiResponse<List<BookingModel>>> getBookings();

  @POST("/bookings")
  Future<ApiResponse<BookingModel>> createBooking(@Body() Map<String, dynamic> booking);

  @PUT("/bookings/{id}")
  Future<ApiResponse<BookingModel>> updateBooking(
    @Path("id") int id,
    @Body() Map<String, dynamic> booking,
  );

  // Sync
  @POST("/sync/batch")
  Future<ApiResponse<SyncResult>> batchSync(@Body() Map<String, dynamic> operations);

  @GET("/sync/changes")
  Future<ApiResponse<SyncChanges>> getChanges(
    @Query("since") String? since,
  );
}

// Dio Configuration
Dio createDio() {
  final dio = Dio(BaseOptions(
    baseUrl: 'https://your-api.com/api',
    connectTimeout: const Duration(seconds: 30),
    receiveTimeout: const Duration(seconds: 30),
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
  ));

  // Add interceptors
  dio.interceptors.add(InterceptorsWrapper(
    onRequest: (options, handler) async {
      // Add auth token
      final token = await getAuthToken();
      if (token != null) {
        options.headers['Authorization'] = 'Bearer $token';
      }
      return handler.next(options);
    },
    onError: (DioException e, handler) async {
      if (e.response?.statusCode == 401) {
        // Refresh token or logout
        await refreshToken();
      }
      return handler.next(e);
    },
  ));

  // Add logging in debug mode
  dio.interceptors.add(LogInterceptor(
    requestBody: true,
    responseBody: true,
  ));

  return dio;
}
```

### 2. Sync Service (SQLite ↔ API)

```dart
// lib/data/services/sync_service.dart
import 'package:connectivity_plus/connectivity_plus.dart';
import '../dao/property_dao.dart';
import '../dao/booking_dao.dart';
import 'api_service.dart';

class SyncService {
  final ApiService _apiService;
  final PropertyDao _propertyDao;
  final BookingDao _bookingDao;

  SyncService(this._apiService, this._propertyDao, this._bookingDao);

  // Check connectivity
  Future<bool> isOnline() async {
    final connectivity = await Connectivity().checkConnectivity();
    return connectivity != ConnectivityResult.none;
  }

  // Full Sync
  Future<SyncResult> performFullSync() async {
    if (!await isOnline()) {
      return SyncResult(
        success: false,
        message: 'No internet connection',
      );
    }

    try {
      // 1. Upload pending changes
      await _uploadPendingChanges();

      // 2. Download server changes
      await _downloadServerChanges();

      return SyncResult(
        success: true,
        message: 'Sync completed successfully',
      );
    } catch (e) {
      return SyncResult(
        success: false,
        message: 'Sync failed: $e',
      );
    }
  }

  // Upload pending changes
  Future<void> _uploadPendingChanges() async {
    // Get unsynced bookings
    final unsyncedBookings = await _bookingDao.getUnsyncedBookings();

    if (unsyncedBookings.isEmpty) return;

    final operations = unsyncedBookings.map((booking) => {
      'table': 'bookings',
      'action': booking.serverId == null ? 'create' : 'update',
      'data': booking.toJson(),
      'local_id': booking.localId,
    }).toList();

    final result = await _apiService.batchSync({'operations': operations});

    // Mark synced items
    for (var success in result.data.successful) {
      await _bookingDao.markAsSynced(
        success['local_id'],
        success['server_id'],
      );
    }
  }

  // Download server changes
  Future<void> _downloadServerChanges() async {
    // Get last sync timestamp
    final lastSync = await getLastSyncTime();

    // Fetch changes from server
    final response = await _apiService.getChanges(lastSync?.toIso8601String());
    final changes = response.data;

    // Update local database
    await _propertyDao.insertPropertiesBatch(changes.properties);
    // ... handle other entities

    // Save sync time
    await saveLastSyncTime(DateTime.now());
  }

  // Background sync (call periodically)
  Future<void> backgroundSync() async {
    if (!await isOnline()) return;
    
    await performFullSync();
  }
}
```

---

## 🎯 Implementation Roadmap

### Phase 1: SQLite Setup (2 hours)
1. Add dependencies
2. Create DatabaseHelper
3. Create DAO classes
4. Create models with local DB support

### Phase 2: API Setup (3 hours)
1. Create PHP API controllers
2. Set up authentication (Sanctum)
3. Create API routes
4. Test endpoints with Postman

### Phase 3: Flutter Integration (3 hours)
1. Create ApiService with Dio
2. Create SyncService
3. Connect UI to local DB
4. Add pull-to-refresh

### Phase 4: Offline Support (2 hours)
1. Add connectivity check
2. Queue pending operations
3. Background sync
4. Conflict resolution

**Total: ~10 hours for complete implementation**

---

## 💡 Best Practices

### 1. Data Consistency
```dart
// Always use transactions for related operations
await db.transaction((txn) async {
  final propertyId = await txn.insert('properties', propertyData);
  await txn.insert('property_images', {
    'property_id': propertyId,
    'url': imageUrl,
  });
});
```

### 2. Image Caching
```dart
// Cache images locally
cached_network_image: ^3.3.0

CachedNetworkImage(
  imageUrl: property.imageUrl,
  placeholder: (context, url) => CircularProgressIndicator(),
  errorWidget: (context, url, error) => Icon(Icons.error),
  cacheManager: CacheManager(
    Config(
      'propertyImages',
      stalePeriod: Duration(days: 7),
      maxNrOfCacheObjects: 100,
    ),
  ),
)
```

### 3. Optimistic Updates
```dart
// Update UI immediately, sync in background
Future<void> addToFavorites(PropertyModel property) async {
  // 1. Update local DB immediately
  await _propertyDao.updateProperty(property.copyWith(isFavorite: true));
  
  // 2. Notify UI
  ref.refresh(propertiesProvider);
  
  // 3. Sync to server (async)
  try {
    await _apiService.addToFavorites(property.serverId!);
  } catch (e) {
    // Handle error - maybe retry later
  }
}
```

---

## 📊 Comparison: SQLite vs Direct API

| Feature | SQLite + API | Direct API Only |
|---------|--------------|-----------------|
| **Offline Access** | ✅ Full | ❌ None |
| **Speed** | ✅ Fast (local) | ⚠️ Network dependent |
| **Data Usage** | ✅ Minimal | ❌ High |
| **Battery** | ✅ Better | ❌ More drain |
| **Complexity** | ⚠️ Higher | ✅ Simple |
| **Maintenance** | ⚠️ More | ✅ Less |

---

**Kya shuru karun? SQLite setup ya PHP API?** 💪


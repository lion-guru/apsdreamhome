# Database Schema Documentation

## 🗄️ Database Overview

The APS Dream Home application uses MySQL as its primary database. All database operations are tested for integrity, performance, and security.

## properties Table

### Structure

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| id | bigint(20) unsigned | NO | PRI | - | auto_increment |
| title | varchar(255) | NO | - | - | - |
| description | text | YES | - | - | - |
| price | decimal(10,2) | NO | MUL | - | - |
| location | varchar(255) | NO | MUL | - | - |
| type | enum('apartment','house','land','commercial') | NO | - | - | - |
| status | enum('available','sold','booked') | NO | MUL | available | - |
| created_by | bigint(20) unsigned | YES | MUL | - | - |
| updated_by | bigint(20) unsigned | YES | MUL | - | - |
| created_at | timestamp | NO | MUL | current_timestamp() | - |
| updated_at | timestamp | NO | MUL | current_timestamp() | on update current_timestamp() |

### Sample Data

```json
{
    "id": 1,
    "title": "Residential Plot 1500 sqft #1",
    "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. This property offers excellent value and modern amenities.",
    "price": "7000000.00",
    "location": "Sector-7, Kanpur, UP",
    "type": "apartment",
    "status": "sold",
    "created_by": 5,
    "updated_by": null,
    "created_at": "2025-10-29 20:59:23",
    "updated_at": "2025-10-21 05:02:35"
}
```

```json
{
    "id": 2,
    "title": "High-Street Retail Space #2",
    "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. This property offers excellent value and modern amenities.",
    "price": "17100000.00",
    "location": "Sector-15, Kanpur, UP",
    "type": "house",
    "status": "booked",
    "created_by": 5,
    "updated_by": null,
    "created_at": "2025-10-06 12:09:45",
    "updated_at": "2025-08-08 20:18:40"
}
```

```json
{
    "id": 3,
    "title": "Agricultural Land #3",
    "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. This property offers excellent value and modern amenities.",
    "price": "10400000.00",
    "location": "Sector-20, Lucknow, UP",
    "type": "land",
    "status": "booked",
    "created_by": 4,
    "updated_by": null,
    "created_at": "2025-08-28 16:33:28",
    "updated_at": "2025-08-07 22:57:08"
}
```

### Statistics

- **Total Records:** 51
- **Last Updated:** 2025-11-28 18:46:55

---

## projects Table

### Structure

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| id | int(11) | NO | PRI | - | auto_increment |
| name | varchar(200) | NO | - | - | - |
| project_code | varchar(50) | NO | UNI | - | - |
| description | text | YES | - | - | - |
| location | varchar(200) | YES | MUL | - | - |
| city | varchar(100) | YES | MUL | - | - |
| state | varchar(100) | YES | - | - | - |
| status | varchar(50) | YES | MUL | planning | - |
| project_type | varchar(50) | YES | MUL | - | - |
| total_units | int(11) | YES | - | - | - |
| available_units | int(11) | YES | - | - | - |
| starting_price | decimal(15,2) | YES | - | - | - |
| completion_date | date | YES | MUL | - | - |
| launch_date | date | YES | MUL | - | - |
| developer_name | varchar(200) | YES | - | - | - |
| contact_person | varchar(100) | YES | - | - | - |
| contact_phone | varchar(20) | YES | - | - | - |
| contact_email | varchar(100) | YES | - | - | - |
| address | text | YES | - | - | - |
| amenities | text | YES | - | - | - |
| images | text | YES | - | - | - |
| created_by | int(11) | YES | - | - | - |
| created_at | timestamp | NO | MUL | current_timestamp() | - |
| updated_at | timestamp | NO | MUL | current_timestamp() | on update current_timestamp() |
| is_active | tinyint(1) | NO | - | 1 | - |
| is_featured | tinyint(1) | NO | - | - | - |

### Sample Data

```json
{
    "id": 1,
    "name": "",
    "project_code": "PRJ-0001",
    "description": null,
    "location": null,
    "city": null,
    "state": null,
    "status": "planning",
    "project_type": null,
    "total_units": 0,
    "available_units": 0,
    "starting_price": null,
    "completion_date": null,
    "launch_date": null,
    "developer_name": null,
    "contact_person": null,
    "contact_phone": null,
    "contact_email": null,
    "address": null,
    "amenities": null,
    "images": null,
    "created_by": null,
    "created_at": "2025-11-08 22:07:57",
    "updated_at": "2025-11-21 00:05:05",
    "is_active": 1,
    "is_featured": 0
}
```

```json
{
    "id": 2,
    "name": "",
    "project_code": "PRJ-0002",
    "description": null,
    "location": null,
    "city": null,
    "state": null,
    "status": "planning",
    "project_type": null,
    "total_units": 0,
    "available_units": 0,
    "starting_price": null,
    "completion_date": null,
    "launch_date": null,
    "developer_name": null,
    "contact_person": null,
    "contact_phone": null,
    "contact_email": null,
    "address": null,
    "amenities": null,
    "images": null,
    "created_by": null,
    "created_at": "2025-11-08 22:07:57",
    "updated_at": "2025-11-21 00:05:05",
    "is_active": 1,
    "is_featured": 0
}
```

```json
{
    "id": 3,
    "name": "",
    "project_code": "PRJ-0003",
    "description": null,
    "location": null,
    "city": null,
    "state": null,
    "status": "planning",
    "project_type": null,
    "total_units": 0,
    "available_units": 0,
    "starting_price": null,
    "completion_date": null,
    "launch_date": null,
    "developer_name": null,
    "contact_person": null,
    "contact_phone": null,
    "contact_email": null,
    "address": null,
    "amenities": null,
    "images": null,
    "created_by": null,
    "created_at": "2025-11-08 22:07:57",
    "updated_at": "2025-11-21 00:05:05",
    "is_active": 1,
    "is_featured": 0
}
```

### Statistics

- **Total Records:** 5
- **Last Updated:** 2025-11-28 18:46:55

---

## users Table

### Structure

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| id | bigint(20) unsigned | NO | PRI | - | auto_increment |
| name | varchar(255) | NO | - | - | - |
| email | varchar(255) | NO | UNI | - | - |
| profile_picture | varchar(255) | YES | - | - | - |
| phone | varchar(20) | YES | MUL | - | - |
| type | enum('admin','agent','customer','employee') | NO | MUL | customer | - |
| password | varchar(255) | NO | - | - | - |
| status | enum('active','inactive','pending') | NO | MUL | active | - |
| created_at | timestamp | NO | MUL | current_timestamp() | - |
| updated_at | timestamp | NO | MUL | current_timestamp() | on update current_timestamp() |
| api_access | tinyint(1) | YES | - | - | - |
| api_rate_limit | int(11) | YES | - | 100 | - |

### Sample Data

```json
{
    "id": 1,
    "name": "Aarav Sharma",
    "email": "aarav@aps.com",
    "profile_picture": null,
    "phone": "9000011111",
    "type": "agent",
    "password": "$2y$10$tSZTaHhWWxaJ2\/WhiVdDX.YYqdQ117xKiyNSqofDKsXhxEhrPwGXS",
    "status": "active",
    "created_at": "2025-09-06 14:37:53",
    "updated_at": "2025-11-01 18:35:51",
    "api_access": 0,
    "api_rate_limit": 100
}
```

```json
{
    "id": 2,
    "name": "Diya Verma",
    "email": "diya@aps.com",
    "profile_picture": null,
    "phone": "9000022222",
    "type": "agent",
    "password": "$2y$10$AZo6ssTGFzJU3T6ReOnwk.rxvyID3D9NCKT9EDDLj8UII3eJxTuqm",
    "status": "active",
    "created_at": "2025-10-04 09:35:07",
    "updated_at": "2025-11-01 18:35:51",
    "api_access": 0,
    "api_rate_limit": 100
}
```

```json
{
    "id": 3,
    "name": "Kabir Singh",
    "email": "kabir@aps.com",
    "profile_picture": null,
    "phone": "9000033333",
    "type": "agent",
    "password": "$2y$10$wIb3KLTGDK3WYLMRfhdkNusSs3eUGQQ.7vLOp15kGh0q.2ngemTk.",
    "status": "active",
    "created_at": "2025-09-30 10:55:14",
    "updated_at": "2025-11-01 18:35:51",
    "api_access": 0,
    "api_rate_limit": 100
}
```

### Statistics

- **Total Records:** 36
- **Last Updated:** 2025-11-28 18:46:55

---

## inquiries Table

### Structure

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| id | int(11) | NO | PRI | - | auto_increment |
| name | varchar(255) | NO | - | - | - |
| email | varchar(255) | NO | MUL | - | - |
| phone | varchar(50) | NO | - | - | - |
| message | text | NO | - | - | - |
| property_id | int(11) | YES | MUL | - | - |
| project_id | int(11) | YES | MUL | - | - |
| type | enum('property','project','general') | NO | MUL | general | - |
| status | enum('pending','in_progress','completed','closed') | NO | MUL | pending | - |
| priority | enum('low','medium','high') | NO | - | medium | - |
| assigned_to | int(11) | YES | - | - | - |
| created_at | timestamp | NO | MUL | current_timestamp() | - |
| updated_at | timestamp | NO | - | current_timestamp() | on update current_timestamp() |

### Sample Data

```json
{
    "id": 1,
    "name": "Rahul Sharma",
    "email": "rahul.sharma@email.com",
    "phone": "9876543210",
    "message": "I am interested in buying a residential plot in Gorakhpur. Please provide more details.",
    "property_id": 1,
    "project_id": null,
    "type": "property",
    "status": "pending",
    "priority": "medium",
    "assigned_to": null,
    "created_at": "2025-11-28 22:35:08",
    "updated_at": "2025-11-28 22:35:08"
}
```

```json
{
    "id": 2,
    "name": "Priya Singh",
    "email": "priya.singh@email.com",
    "phone": "9123456789",
    "message": "Looking for information about the Dream Valley project.",
    "property_id": null,
    "project_id": 1,
    "type": "project",
    "status": "in_progress",
    "priority": "medium",
    "assigned_to": null,
    "created_at": "2025-11-28 22:35:08",
    "updated_at": "2025-11-28 22:35:08"
}
```

```json
{
    "id": 3,
    "name": "Amit Kumar",
    "email": "amit.kumar@email.com",
    "phone": "8899776655",
    "message": "General inquiry about investment opportunities in real estate.",
    "property_id": null,
    "project_id": null,
    "type": "general",
    "status": "pending",
    "priority": "medium",
    "assigned_to": null,
    "created_at": "2025-11-28 22:35:08",
    "updated_at": "2025-11-28 22:35:08"
}
```

### Statistics

- **Total Records:** 8
- **Last Updated:** 2025-11-28 18:46:55

---

## bookings Table

### Structure

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| id | bigint(20) unsigned | NO | PRI | - | auto_increment |
| property_id | bigint(20) unsigned | NO | MUL | - | - |
| customer_id | bigint(20) unsigned | YES | MUL | - | - |
| booking_date | date | NO | MUL | - | - |
| status | enum('pending','confirmed','cancelled') | NO | MUL | pending | - |
| amount | decimal(10,2) | NO | - | - | - |
| created_at | timestamp | NO | MUL | current_timestamp() | - |
| updated_at | timestamp | NO | MUL | current_timestamp() | on update current_timestamp() |

### Sample Data

```json
{
    "id": 1,
    "property_id": 19,
    "customer_id": null,
    "booking_date": "2025-10-24",
    "status": "cancelled",
    "amount": "98428.00",
    "created_at": "2025-10-22 03:02:24",
    "updated_at": "2025-11-02 01:21:37"
}
```

```json
{
    "id": 2,
    "property_id": 41,
    "customer_id": null,
    "booking_date": "2025-10-22",
    "status": "",
    "amount": "110666.00",
    "created_at": "2025-09-01 12:55:53",
    "updated_at": "2025-11-02 01:21:37"
}
```

```json
{
    "id": 3,
    "property_id": 20,
    "customer_id": null,
    "booking_date": "2025-11-22",
    "status": "cancelled",
    "amount": "75909.00",
    "created_at": "2025-08-30 03:32:20",
    "updated_at": "2025-11-02 01:21:37"
}
```

### Statistics

- **Total Records:** 16
- **Last Updated:** 2025-11-28 18:46:55

---


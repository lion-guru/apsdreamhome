# Query Plan Report
Generated: 2025-11-01T22:11:22+01:00

## bookings: status + booking_date order by booking_date,created_at
```json
{
  "query_block": {
    "select_id": 1,
    "table": {
      "table_name": "bookings",
      "access_type": "range",
      "possible_keys": [
        "idx_bookings_status",
        "ix_bookings_booking_date",
        "idx_bookings_status_date",
        "idx_bookings_status_booking_created"
      ],
      "key": "idx_bookings_status_booking_created",
      "key_length": "4",
      "used_key_parts": ["status", "booking_date"],
      "rows": 3,
      "filtered": 100,
      "attached_condition": "bookings.`status` <=> 'confirmed' and bookings.`status` = 'confirmed' and bookings.booking_date between '2023-01-01' and '2030-12-31'"
    }
  }
}
```

## bookings: property_id + booking_date order
```json
{
  "query_block": {
    "select_id": 1,
    "table": {
      "table_name": "bookings",
      "access_type": "ref",
      "possible_keys": [
        "property_id",
        "ix_bookings_property_id",
        "idx_bookings_property_date"
      ],
      "key": "idx_bookings_property_date",
      "key_length": "8",
      "used_key_parts": ["property_id"],
      "ref": ["const"],
      "rows": 1,
      "filtered": 100,
      "attached_condition": "bookings.property_id <=> 12345"
    }
  }
}
```

## commission_transactions: associate + date desc
```json
{
  "query_block": {
    "select_id": 1,
    "table": {
      "table_name": "commission_transactions",
      "access_type": "ref",
      "possible_keys": [
        "ix_commission_transactions_associate_id",
        "idx_commission_transactions_associate_date"
      ],
      "key": "idx_commission_transactions_associate_date",
      "key_length": "4",
      "used_key_parts": ["associate_id"],
      "ref": ["const"],
      "rows": 1,
      "filtered": 100,
      "attached_condition": "commission_transactions.associate_id <=> 1"
    }
  }
}
```

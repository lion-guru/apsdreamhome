# Database ER Diagram

## Overview
This document contains the Entity-Relationship (ER) diagram for the APS Dream Home database.

## Main Entities

### Users
- id (PK)
- first_name
- last_name
- email
- password (hashed)
- phone
- role (admin/agent/user)
- status

### Properties
- id (PK)
- title
- description
- address
- price
- bedrooms
- bathrooms
- area
- type
- status
- owner_id (FK to users)

### Customers
- id (PK)
- name
- email
- phone
- address
- created_at

### Leads
- id (PK)
- customer_id (FK to customers)
- property_id (FK to properties)
- source
- status
- notes
- created_at

### Property Visits
- id (PK)
- customer_id (FK to customers)
- property_id (FK to properties)
- lead_id (FK to leads)
- visit_date
- visit_time
- status
- feedback
- rating
- created_at

## Relationships
- One User can own many Properties (1:N)
- One Property can have many Leads (1:N)
- One Customer can have many Leads (1:N)
- One Lead is associated with one Property and one Customer
- One Property can have many Visits (1:N)
- One Customer can have many Visits (1:N)

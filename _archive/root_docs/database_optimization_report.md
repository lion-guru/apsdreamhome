# Database Query Optimization Report

Generated: 2026-05-17 15:31:26

## Executive Summary

Total optimizations identified: 25

### High Priority Optimizations

No high priority optimizations found.

### Medium Priority Optimizations

- **DATABASE**: users.email
  - CREATE INDEX idx_users_email ON users(email)

- **DATABASE**: users.phone
  - CREATE INDEX idx_users_phone ON users(phone)

- **DATABASE**: users.status
  - CREATE INDEX idx_users_status ON users(status)

- **DATABASE**: user_properties.user_id
  - CREATE INDEX idx_user_properties_user_id ON user_properties(user_id)

- **DATABASE**: user_properties.status
  - CREATE INDEX idx_user_properties_status ON user_properties(status)

- **DATABASE**: user_properties.property_type
  - CREATE INDEX idx_user_properties_property_type ON user_properties(property_type)

- **DATABASE**: user_properties.listing_type
  - CREATE INDEX idx_user_properties_listing_type ON user_properties(listing_type)

- **DATABASE**: user_properties.price
  - CREATE INDEX idx_user_properties_price ON user_properties(price)

- **DATABASE**: inquiries.user_id
  - CREATE INDEX idx_inquiries_user_id ON inquiries(user_id)

- **DATABASE**: inquiries.status
  - CREATE INDEX idx_inquiries_status ON inquiries(status)

- **DATABASE**: inquiries.created_at
  - CREATE INDEX idx_inquiries_created_at ON inquiries(created_at)

- **DATABASE**: projects.status
  - CREATE INDEX idx_projects_status ON projects(status)

- **DATABASE**: projects.district_id
  - CREATE INDEX idx_projects_district_id ON projects(district_id)

- **DATABASE**: projects.state_id
  - CREATE INDEX idx_projects_state_id ON projects(state_id)

- **DATABASE**: districts.state_id
  - CREATE INDEX idx_districts_state_id ON districts(state_id)

- **DATABASE**: districts.name
  - CREATE INDEX idx_districts_name ON districts(name)

- **DATABASE**: admin_menu_items.parent_id
  - CREATE INDEX idx_admin_menu_items_parent_id ON admin_menu_items(parent_id)

- **DATABASE**: admin_menu_items.sort_order
  - CREATE INDEX idx_admin_menu_items_sort_order ON admin_menu_items(sort_order)

- **DATABASE**: leads.status
  - CREATE INDEX idx_leads_status ON leads(status)

- **DATABASE**: leads.assigned_to
  - CREATE INDEX idx_leads_assigned_to ON leads(assigned_to)

- **DATABASE**: leads.created_at
  - CREATE INDEX idx_leads_created_at ON leads(created_at)

- **DATABASE**: bookings.user_id
  - CREATE INDEX idx_bookings_user_id ON bookings(user_id)

- **DATABASE**: bookings.property_id
  - CREATE INDEX idx_bookings_property_id ON bookings(property_id)

- **DATABASE**: bookings.status
  - CREATE INDEX idx_bookings_status ON bookings(status)

- **DATABASE**: bookings.created_at
  - CREATE INDEX idx_bookings_created_at ON bookings(created_at)

### Low Priority Optimizations

No low priority optimizations found.

## Recommended Database Indexes

```sql
-- Database Index Optimization SQL
-- Generated: 2026-05-17 15:31:26

-- Indexes for users.email
CREATE INDEX idx_users_email ON users(email);

-- Indexes for users.phone
CREATE INDEX idx_users_phone ON users(phone);

-- Indexes for users.status
CREATE INDEX idx_users_status ON users(status);

-- Indexes for user_properties.user_id
CREATE INDEX idx_user_properties_user_id ON user_properties(user_id);

-- Indexes for user_properties.status
CREATE INDEX idx_user_properties_status ON user_properties(status);

-- Indexes for user_properties.property_type
CREATE INDEX idx_user_properties_property_type ON user_properties(property_type);

-- Indexes for user_properties.listing_type
CREATE INDEX idx_user_properties_listing_type ON user_properties(listing_type);

-- Indexes for user_properties.price
CREATE INDEX idx_user_properties_price ON user_properties(price);

-- Indexes for inquiries.user_id
CREATE INDEX idx_inquiries_user_id ON inquiries(user_id);

-- Indexes for inquiries.status
CREATE INDEX idx_inquiries_status ON inquiries(status);

-- Indexes for inquiries.created_at
CREATE INDEX idx_inquiries_created_at ON inquiries(created_at);

-- Indexes for projects.status
CREATE INDEX idx_projects_status ON projects(status);

-- Indexes for projects.district_id
CREATE INDEX idx_projects_district_id ON projects(district_id);

-- Indexes for projects.state_id
CREATE INDEX idx_projects_state_id ON projects(state_id);

-- Indexes for districts.state_id
CREATE INDEX idx_districts_state_id ON districts(state_id);

-- Indexes for districts.name
CREATE INDEX idx_districts_name ON districts(name);

-- Indexes for admin_menu_items.parent_id
CREATE INDEX idx_admin_menu_items_parent_id ON admin_menu_items(parent_id);

-- Indexes for admin_menu_items.sort_order
CREATE INDEX idx_admin_menu_items_sort_order ON admin_menu_items(sort_order);

-- Indexes for leads.status
CREATE INDEX idx_leads_status ON leads(status);

-- Indexes for leads.assigned_to
CREATE INDEX idx_leads_assigned_to ON leads(assigned_to);

-- Indexes for leads.created_at
CREATE INDEX idx_leads_created_at ON leads(created_at);

-- Indexes for bookings.user_id
CREATE INDEX idx_bookings_user_id ON bookings(user_id);

-- Indexes for bookings.property_id
CREATE INDEX idx_bookings_property_id ON bookings(property_id);

-- Indexes for bookings.status
CREATE INDEX idx_bookings_status ON bookings(status);

-- Indexes for bookings.created_at
CREATE INDEX idx_bookings_created_at ON bookings(created_at);

```

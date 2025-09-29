# Lead Management System

This module provides a comprehensive lead management system for APS Dream Homes, allowing you to track and manage leads from initial contact through conversion to clients.

## Features

- **Lead Tracking**: Track leads from initial contact to conversion
- **Activity Logging**: Record all interactions with leads (calls, emails, meetings, notes)
- **Task Management**: Create and assign tasks related to leads
- **Client Conversion**: Convert qualified leads to clients with a single click
- **Role-Based Access**: Different access levels for admins, managers, and agents
- **Notifications**: Stay updated with real-time notifications
- **Reporting**: Track lead sources, conversion rates, and agent performance

## Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Composer (for PHP dependencies)

### Setup Instructions

1. **Clone the repository** (if not already done):
   ```bash
   git clone https://github.com/yourusername/apsdreamhomefinal.git
   cd apsdreamhomefinal
   ```

2. **Install PHP dependencies** (if any):
   ```bash
   composer install
   ```

3. **Set up the database**:
   - Create a new MySQL database
   - Import the database schema:
     ```bash
     mysql -u your_username -p your_database_name < database/lead_management_setup.sql
     ```
   - Or run the initialization script in your browser:
     ```
     http://localhost/apsdreamhomefinal/database/init_lead_management.php
     ```

4. **Configure the application**:
   - Copy `.env.example` to `.env` and update with your database credentials
   - Configure mail settings in `includes/config.php`
   - Set up Google OAuth if using Google Sign-In

5. **Set file permissions**:
   ```bash
   chmod -R 755 storage/
   chmod -R 755 uploads/
   chmod 755 includes/
   ```

6. **Access the application**:
   - Open your browser and navigate to:
     ```
     http://localhost/apsdreamhomefinal/leads/dashboard.php
     ```
   - Login with the default credentials:
     - Admin: admin@apsdreamhomes.com / admin@123
     - Manager: manager@apsdreamhomes.com / manager@123
     - Agent: agent@apsdreamhomes.com / agent@123

## User Roles

1. **Admin**: Full access to all features and settings
2. **Lead Manager**: Can manage all leads and assign to agents
3. **Sales Agent**: Can manage assigned leads and log activities
4. **User**: Basic access (if applicable)

## API Endpoints

- `POST /api/assign_lead.php`: Assign a lead to an agent
- `POST /api/log_activity.php`: Log an activity for a lead
- `POST /api/update_lead_status.php`: Update lead status
- `GET /api/get_lead_details.php`: Get lead details
- `GET /api/get_lead_activities.php`: Get lead activities

## Troubleshooting

- **Database connection issues**: Verify database credentials in `includes/config.php`
- **Permission errors**: Ensure the web server has write access to the `storage/` and `uploads/` directories
- **Missing tables**: Run the database initialization script again
- **Email not sending**: Check mail server configuration in `includes/config.php`

## Security

- Always use HTTPS in production
- Keep your PHP and server software up to date
- Regularly backup your database
- Use strong passwords for all user accounts
- Implement rate limiting for API endpoints
- Sanitize all user inputs

## Support

For support, please contact the development team at support@apsdreamhomes.com

## License

This project is proprietary software owned by APS Dream Homes. Unauthorized use, copying, or distribution is strictly prohibited.

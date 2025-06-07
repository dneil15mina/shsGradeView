# SHS Grade View System Documentation

## Milestone Checkpoint - June 7, 2025 (4:50 PM)

### Database Schema Updates
- Added semester column to classes table (ENUM: '1st', '2nd', 'summer')
- Established proper relationships between tables
- Implemented web-based schema migration system
- Added show_computed_grades setting to system_settings

### Completed Features
1. **Admin Panel**
   - Class management with semester support
   - User management (admin/teacher/student)
   - Grade period activation control
   - Computed grades visibility toggle

2. **Teacher Panel**
   - Class listing with semester information
   - Grade entry interface
   - Password management

3. **Student Panel**
   - Grade viewing interface
   - Midterm/Final grade calculations
   - Admin-controlled computed grades visibility

### Technical Improvements
- Fixed SQL queries to match current schema
- Added proper error handling
- Implemented web-based database initialization
- Standardized semester display formatting

### Next Steps
- Implement grade computation logic
- Add reporting features
- Enhance user interface
- Add data validation


## Project Overview
A web-based grade management system for Senior High Schools with:
- Admin, Teacher, and Student roles
- Grade encoding and viewing functionality
- User management system
- Secure authentication

## Technical Stack
- PHP 8.0+
- MySQL/MariaDB
- HTML5, CSS3
- Apache web server (via XAMPP)

## Key Features Implemented

### Authentication System
- Role-based access control
- Secure password hashing
- Session management
- Password change functionality

### Admin Panel
- User CRUD operations
  - Add/edit/delete users
  - Role assignment (admin/teacher/student)
  - Account activation control
- Paginated user listing
- Search and filtering

### Database Schema
- Users table with roles
- Grades table structure
- Enrollment relationships

## Progress Points

### Completed Modules
1. Authentication system
   - Login/logout
   - Session management
   - Password change
2. Admin user management
   - User listing with pagination
   - Add/edit/delete users
   - Role management
3. Grade period management
   - Midterm/finals activation control
4. Teacher grade management
   - Class assignment
   - Grade encoding with period restrictions
5. Student portal
   - Grade viewing
   - Final grade calculation

### Next Steps
1. Additional features
   - Grade reports
   - Data export
   - Grade analytics
2. System improvements
   - Bulk grade upload
   - Grade verification system

## Important Files
- `database/schema.sql` - Database structure
- `config/db_connect.php` - Database connection
- `includes/auth.php` - Authentication functions
- `admin/` - Admin panel files
- `teacher/` - Teacher portal files
- `student/` - Student portal files

## Restoration Points
To continue development from current state:
1. Run database initialization:
   ```bash
   php init_system.php
   ```
2. Key admin credentials:
   - Username: admin
   - Password: admin123
3. Key teacher credentials: 
   - Username: teacher1
   - Password: teacher123
4. Key student credentials:
   - Username: student1
   - Password: student123

## Development Notes
- All passwords are hashed using PHP's password_hash()
- Session variables are used for authentication
- Inputs are sanitized using htmlspecialchars()
- Database queries use prepared statements

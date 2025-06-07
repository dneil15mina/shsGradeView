<?php
// Initialize the entire system
echo "Initializing SHS Grade View System...\n";

// Run database initialization
echo "Creating database and tables...\n";
require_once 'scripts/init_db.php';

// Seed sample data
echo "Seeding sample data...\n";
require_once 'scripts/seed_db.php';

echo "System initialization complete!\n";
echo "You can now access the system at:\n";
echo "Admin: username 'admin', password 'admin123'\n";
echo "Teacher: username 'teacher1', password 'teacher123'\n";
echo "Student: username 'student1', password 'student123'\n";
?>

-- Database schema for SHS Grade View System
-- Tables creation with proper constraints

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role ENUM('admin', 'teacher', 'student') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE grade_levels (
    level_id INT AUTO_INCREMENT PRIMARY KEY,
    level_name VARCHAR(50) NOT NULL,
    level_order INT NOT NULL DEFAULT 0,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE sections (
    section_id INT AUTO_INCREMENT PRIMARY KEY,
    level_id INT NOT NULL,
    section_name VARCHAR(50) NOT NULL,
    max_students INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (level_id) REFERENCES grade_levels(level_id)
);

CREATE TABLE subjects (
    subject_id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(100) NOT NULL,
    subject_code VARCHAR(20) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE classes (
    class_id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    section_id INT NOT NULL,
    teacher_id INT NOT NULL,
    school_year VARCHAR(20) NOT NULL,
    semester ENUM('1st', '2nd', 'summer') NOT NULL,
    term_active ENUM('both', 'midterm', 'final', 'none') DEFAULT 'both',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id),
    FOREIGN KEY (section_id) REFERENCES sections(section_id),
    FOREIGN KEY (teacher_id) REFERENCES users(user_id),
    UNIQUE KEY (subject_id, section_id, school_year, semester)
);

CREATE TABLE enrollment (
    enrollment_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    date_enrolled DATE NOT NULL,
    FOREIGN KEY (student_id) REFERENCES users(user_id),
    FOREIGN KEY (class_id) REFERENCES classes(class_id),
    UNIQUE KEY (student_id, class_id)
);

CREATE TABLE grades (
    grade_id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT NOT NULL,
    midterm_grade DECIMAL(5,2),
    final_grade DECIMAL(5,2),
    final_computed_grade INT GENERATED ALWAYS AS (ROUND((COALESCE(midterm_grade, 0) + COALESCE(final_grade, 0))/2)) STORED,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT NOT NULL,
    FOREIGN KEY (enrollment_id) REFERENCES enrollment(enrollment_id),
    FOREIGN KEY (updated_by) REFERENCES users(user_id)
);

-- System settings table
CREATE TABLE system_settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    description TEXT
);

-- Sample data for testing
INSERT INTO grade_levels (level_name, description) VALUES 
('Grade 11', 'Senior High School Grade 11'),
('Grade 12', 'Senior High School Grade 12');

INSERT INTO sections (level_id, section_name, max_students) VALUES
(1, 'STEM A', 40),
(1, 'STEM B', 40),
(2, 'STEM A', 40),
(2, 'STEM B', 40);

INSERT INTO subjects (subject_name, subject_code) VALUES
('Mathematics', 'MATH101'),
('Science', 'SCI101'),
('English', 'ENG101');

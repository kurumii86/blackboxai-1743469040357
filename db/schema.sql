-- LMS Database Schema
CREATE TABLE IF NOT EXISTS users (
  user_id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('student', 'teacher', 'admin') DEFAULT 'student',
  year_level INT,
  semester ENUM('1st', '2nd'),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS subjects (
  subject_id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  code VARCHAR(50) UNIQUE NOT NULL,
  description TEXT,
  year_level INT NOT NULL,
  semester ENUM('1st', '2nd') NOT NULL,
  credits INT DEFAULT 3,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS enrollments (
  enrollment_id INT PRIMARY KEY AUTO_INCREMENT,
  student_id INT NOT NULL,
  subject_id INT NOT NULL,
  enrollment_status ENUM('Pending', 'Approved', 'Rejected', 'Dropped') DEFAULT 'Pending',
  enrollment_type ENUM('Regular', 'Irregular') NOT NULL,
  enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS enrollment_audit (
  audit_id INT PRIMARY KEY AUTO_INCREMENT,
  enrollment_id INT NOT NULL,
  changed_by INT NOT NULL,
  old_status VARCHAR(50),
  new_status VARCHAR(50) NOT NULL,
  changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (enrollment_id) REFERENCES enrollments(enrollment_id) ON DELETE CASCADE,
  FOREIGN KEY (changed_by) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Sample data for testing
INSERT INTO users (name, email, password, role, year_level, semester) VALUES
('Admin User', 'admin@lms.edu', '$2y$10$examplehash', 'admin', NULL, NULL),
('John Student', 'john@student.edu', '$2y$10$examplehash', 'student', 1, '1st'),
('Jane Teacher', 'jane@teacher.edu', '$2y$10$examplehash', 'teacher', NULL, NULL);

INSERT INTO subjects (name, code, description, year_level, semester, credits) VALUES
('Introduction to Programming', 'CS101', 'Basic programming concepts', 1, '1st', 3),
('Database Systems', 'CS201', 'Relational database design', 2, '1st', 4),
('Web Development', 'CS202', 'Modern web technologies', 2, '2nd', 4);
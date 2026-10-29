-- Import this file into an already-created database.
-- Hosting providers such as InfinityFree often do not allow CREATE DATABASE
-- privileges for the database user used by phpMyAdmin.

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS admin_activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    complaint_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================================
-- 1. ADMIN TABLE
-- =========================================

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- No default administrator is created by this schema.
--
-- After importing this file, if the admins table is empty:
-- 1. Generate a password hash with PHP on your own computer:
--      php -r '$p = readline("Admin password: "); echo password_hash($p, PASSWORD_DEFAULT), PHP_EOL;'
--    Do not use an online hash generator or put a plaintext password in this file.
-- 2. In phpMyAdmin, open the imported database, select the SQL tab, and run the
--    statement below after replacing the name, email, and hash with your values.
--    Keep the generated hash quoted and do not include the plaintext password.
--
-- INSERT INTO admins (name, email, password)
-- SELECT 'Institute Administrator', 'admin@example.edu', '$2y$REPLACE_WITH_THE_FULL_PHP_PASSWORD_HASH'
-- WHERE NOT EXISTS (SELECT 1 FROM admins);

-- =========================================
-- 2. STUDENTS TABLE
-- =========================================

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    roll_no VARCHAR(50) UNIQUE,
    phone VARCHAR(15),
    department VARCHAR(100),
    password VARCHAR(255) NOT NULL
);

-- =========================================
-- GRIEVANCES USED BY THE CURRENT APPLICATION
-- =========================================

CREATE TABLE IF NOT EXISTS grievances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT DEFAULT NULL,
    student VARCHAR(255) DEFAULT NULL,
    department VARCHAR(255) DEFAULT NULL,
    category VARCHAR(255) DEFAULT NULL,
    subject VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    priority VARCHAR(50) NOT NULL DEFAULT 'Low',
    verification_status VARCHAR(50) DEFAULT NULL,
    resolution_remarks TEXT DEFAULT NULL,
    escalation_level INT NOT NULL DEFAULT 1,
    escalation_reason TEXT DEFAULT NULL,
    KEY idx_grievances_student_id (student_id),
    KEY idx_grievances_status (status)
);

CREATE TABLE IF NOT EXISTS grievance_votes (
    vote_id INT AUTO_INCREMENT PRIMARY KEY,
    grievance_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_grievance_vote (grievance_id, user_id),
    KEY idx_grievance_votes_user_id (user_id)
);


-- =========================================
-- 3. COMPLAINTS TABLE
-- =========================================

CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) DEFAULT NULL,
    department_id INT DEFAULT NULL,
    status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_staff_department_id (department_id),
    CONSTRAINT fk_staff_department
        FOREIGN KEY (department_id) REFERENCES departments(id)
        ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT DEFAULT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(100) DEFAULT NULL,
    priority ENUM('Low','Medium','High') NOT NULL DEFAULT 'Medium',
    status ENUM('Pending','Under Review','In Progress','Resolved','Rejected','Escalated')
        NOT NULL DEFAULT 'Pending',
    image VARCHAR(255) DEFAULT NULL,
    department_id INT DEFAULT NULL,
    staff_id INT DEFAULT NULL,
    admin_remarks TEXT DEFAULT NULL,
    resolution_details TEXT DEFAULT NULL,
    resolution_date DATETIME DEFAULT NULL,
    student_verification ENUM('Pending','Resolved Successfully','Not Resolved')
        DEFAULT 'Pending',
    is_anonymous TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_complaints_student_id (student_id),
    KEY idx_complaints_department_id (department_id),
    KEY idx_complaints_staff_id (staff_id),
    CONSTRAINT fk_complaints_student
        FOREIGN KEY (student_id) REFERENCES students(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_complaints_department
        FOREIGN KEY (department_id) REFERENCES departments(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_complaints_staff
        FOREIGN KEY (staff_id) REFERENCES staff(id)
        ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS complaint_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    old_status VARCHAR(50) DEFAULT NULL,
    new_status VARCHAR(50) DEFAULT NULL,
    admin_id INT DEFAULT NULL,
    remarks TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_complaint_history_complaint_id (complaint_id),
    KEY idx_complaint_history_admin_id (admin_id),
    CONSTRAINT fk_complaint_history_complaint
        FOREIGN KEY (complaint_id) REFERENCES complaints(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_complaint_history_admin
        FOREIGN KEY (admin_id) REFERENCES admins(id)
        ON DELETE SET NULL
);


-- =========================================
-- 4. FEEDBACK TABLE
-- =========================================

CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_feedback_student_id (student_id),
    CONSTRAINT fk_feedback_student
        FOREIGN KEY (student_id) REFERENCES students(id)
        ON DELETE CASCADE
);


-- =========================================
-- 5. VOTES / SUPPORT A GRIEVANCE
-- =========================================

CREATE TABLE IF NOT EXISTS votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    student_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY unique_vote (complaint_id, student_id),

    FOREIGN KEY (complaint_id)
    REFERENCES complaints(id)
    ON DELETE CASCADE,

    FOREIGN KEY (student_id)
    REFERENCES students(id)
    ON DELETE CASCADE
);


-- =========================================
-- 6. ANONYMOUS COMPLAINTS
-- =========================================

CREATE TABLE IF NOT EXISTS anonymous_complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(100),
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('Pending','In Progress','Resolved')
    DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- =========================================
-- 7. RESOLUTION VERIFICATION
-- =========================================

CREATE TABLE IF NOT EXISTS resolution_verification (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    student_id INT NOT NULL,
    verified TINYINT(1) DEFAULT 0,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (complaint_id)
    REFERENCES complaints(id)
    ON DELETE CASCADE,

    FOREIGN KEY (student_id)
    REFERENCES students(id)
    ON DELETE CASCADE
);


-- =========================================
-- 8. SMART ESCALATION
-- =========================================

CREATE TABLE IF NOT EXISTS escalations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    student_id INT NOT NULL,
    reason TEXT,
    level INT DEFAULT 1,
    status VARCHAR(50) DEFAULT 'Escalated',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (complaint_id)
    REFERENCES complaints(id)
    ON DELETE CASCADE,

    FOREIGN KEY (student_id)
    REFERENCES students(id)
    ON DELETE CASCADE
);
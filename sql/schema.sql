CREATE DATABASE IF NOT EXISTS smart_waste_system;
USE smart_waste_system;

-- stores all users(residents, collectors, admins)
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('resident', 'collector', 'admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- stores each report submitted by residents
CREATE TABLE waste_reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    waste_type VARCHAR(50) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    location VARCHAR(255),
    status ENUM('pending', 'in_progress', 'resolved') DEFAULT 'pending',
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- links a collector to a report and tracks status updates
CREATE TABLE tasks (
    task_id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    collector_id INT NOT NULL,
    status_update ENUM('in_progress', 'resolved', 'unable_to_resolve') NOT NULL,
    notes TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES waste_reports(report_id) ON DELETE CASCADE,
    FOREIGN KEY (collector_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- admins upload collection schedules by area
CREATE TABLE collection_schedule (
    schedule_id INT AUTO_INCREMENT PRIMARY KEY,
    area VARCHAR(100) NOT NULL,
    day VARCHAR(20) NOT NULL,
    time TIME NOT NULL,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES users(user_id)
);

-- stores in-system alerts by users and type
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    type ENUM('system', 'task', 'report') NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

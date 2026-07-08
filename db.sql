CREATE DATABASE IF NOT EXISTS eams_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE eams_db;

DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS evacuation_events;
DROP TABLE IF EXISTS employees;
DROP TABLE IF EXISTS assembly_points;
DROP TABLE IF EXISTS buildings;

CREATE TABLE buildings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL
);

CREATE TABLE assembly_points (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  location VARCHAR(150) NOT NULL,
  capacity INT NOT NULL DEFAULT 100
);

CREATE TABLE employees (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employee_number VARCHAR(30) NOT NULL UNIQUE,
  name VARCHAR(150) NOT NULL,
  department VARCHAR(100) NOT NULL,
  building_id INT NOT NULL,
  emergency_contact VARCHAR(30) DEFAULT NULL,
  FOREIGN KEY (building_id) REFERENCES buildings(id)
);

CREATE TABLE evacuation_events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  emergency_type VARCHAR(50) NOT NULL,
  start_time DATETIME NOT NULL,
  end_time DATETIME DEFAULT NULL,
  affected_building_id INT DEFAULT NULL,
  status ENUM('active','ended') NOT NULL DEFAULT 'active',
  FOREIGN KEY (affected_building_id) REFERENCES buildings(id)
);

CREATE TABLE attendance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employee_id INT NOT NULL,
  event_id INT NOT NULL,
  assembly_point_id INT NOT NULL,
  check_in_time DATETIME NOT NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id),
  FOREIGN KEY (event_id) REFERENCES evacuation_events(id),
  FOREIGN KEY (assembly_point_id) REFERENCES assembly_points(id),
  UNIQUE KEY uniq_emp_event (employee_id, event_id)
);

INSERT INTO buildings (name) VALUES
  ('المبنى الرئيسي'),
  ('مبنى الإدارة'),
  ('مبنى العمليات');

INSERT INTO assembly_points (name, location, capacity) VALUES
  ('الساحة الشمالية', 'خلف المبنى الرئيسي', 180),
  ('موقف السيارات الغربي', 'الجهة الغربية', 150),
  ('الملعب الخارجي', 'بجانب مبنى العمليات', 140),
  ('الساحة الجنوبية', 'أمام مبنى الإدارة', 130);

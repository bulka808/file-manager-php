CREATE DATABASE IF NOT EXISTS file_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE file_manager;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user'
);

CREATE TABLE files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Добавим админа для теста (логин: admin, пароль: admin123)
INSERT INTO users (username, password_hash, role) VALUES (
    'admin',
    '$2a$12$N6Jzj6qlela6yWEbFVmqweonl7P2J.c6GKNalSdQzea075R8.3msC', 
    'admin'
);
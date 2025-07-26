<?php
$config = require 'config.php';
require_once 'Database.php';

$db = new Database($config['db']);

// جدول کاربران
$db->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chat_id BIGINT UNIQUE,
    full_name VARCHAR(255),
    goal VARCHAR(100),
    city VARCHAR(100),
    phone VARCHAR(30),
    level VARCHAR(10),
    score INT DEFAULT 0,
    answers_count INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// جدول سوالات
$db->query("CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    text TEXT,
    type ENUM('text','choice','voice'),
    score INT DEFAULT 0,
    options TEXT,
    correct_option INT,
    priority INT DEFAULT 0,
    has_score TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// جدول پاسخ‌ها
$db->query("CREATE TABLE IF NOT EXISTS answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    question_id INT,
    answer TEXT,
    voice_path VARCHAR(255),
    is_correct TINYINT(1),
    score INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$db->close();
echo "Database setup completed.\n";
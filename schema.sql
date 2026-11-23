CREATE DATABASE IF NOT EXISTS exam_pro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE exam_pro;
CREATE TABLE users (
 id INT AUTO_INCREMENT PRIMARY KEY, national_code CHAR(10) NOT NULL UNIQUE,
 full_name VARCHAR(150) NOT NULL,
 role ENUM('student','teacher') NOT NULL, city VARCHAR(100) NULL,
 class_name VARCHAR(100) NULL, is_active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE classes (
 id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(120) NOT NULL,
 city VARCHAR(100) NOT NULL, teacher_id INT NULL,
 FOREIGN KEY(teacher_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
CREATE TABLE user_classes (
 user_id INT NOT NULL, class_id INT NOT NULL,
 PRIMARY KEY(user_id,class_id),
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(class_id) REFERENCES classes(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE exams (
 id INT AUTO_INCREMENT PRIMARY KEY, teacher_id INT NOT NULL, class_id INT NULL,
 title VARCHAR(200) NOT NULL, description TEXT NULL,
 duration_minutes INT NOT NULL DEFAULT 30,
 starts_at DATETIME NOT NULL, ends_at DATETIME NOT NULL,
 status ENUM('draft','published','closed') NOT NULL DEFAULT 'draft',
 total_score DECIMAL(8,2) NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(teacher_id) REFERENCES users(id),
 FOREIGN KEY(class_id) REFERENCES classes(id) ON DELETE SET NULL,
 INDEX(status,starts_at,ends_at)
) ENGINE=InnoDB;
CREATE TABLE questions (
 id INT AUTO_INCREMENT PRIMARY KEY, exam_id INT NOT NULL,
 type ENUM('mcq','essay') NOT NULL, question_text TEXT NOT NULL,
 option_a TEXT NULL, option_b TEXT NULL, option_c TEXT NULL, option_d TEXT NULL,
 correct_option ENUM('A','B','C','D') NULL, score DECIMAL(8,2) NOT NULL DEFAULT 1,
 sort_order INT NOT NULL DEFAULT 0,
 FOREIGN KEY(exam_id) REFERENCES exams(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE attempts (
 id INT AUTO_INCREMENT PRIMARY KEY, exam_id INT NOT NULL, student_id INT NOT NULL,
 started_at DATETIME NOT NULL, submitted_at DATETIME NULL,
 status ENUM('in_progress','submitted','graded') NOT NULL DEFAULT 'in_progress',
 score DECIMAL(8,2) NOT NULL DEFAULT 0, needs_grading TINYINT(1) NOT NULL DEFAULT 0,
 UNIQUE KEY one_attempt(exam_id,student_id),
 FOREIGN KEY(exam_id) REFERENCES exams(id) ON DELETE CASCADE,
 FOREIGN KEY(student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE answers (
 id INT AUTO_INCREMENT PRIMARY KEY, attempt_id INT NOT NULL, question_id INT NOT NULL,
 answer_text TEXT NULL, score DECIMAL(8,2) NOT NULL DEFAULT 0, is_graded TINYINT(1) NOT NULL DEFAULT 0,
 UNIQUE KEY one_answer(attempt_id,question_id),
 FOREIGN KEY(attempt_id) REFERENCES attempts(id) ON DELETE CASCADE,
 FOREIGN KEY(question_id) REFERENCES questions(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE messages (
 id INT AUTO_INCREMENT PRIMARY KEY, sender_id INT NOT NULL, receiver_id INT NOT NULL,
 body TEXT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(sender_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(receiver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

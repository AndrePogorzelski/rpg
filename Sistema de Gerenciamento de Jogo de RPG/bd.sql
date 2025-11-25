CREATE DATABASE rpg_system;
USE rpg_system;
-- Tabela de usuários
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de personagens
CREATE TABLE characters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    class VARCHAR(50) NOT NULL,
    level INT DEFAULT 1,
    strength INT DEFAULT 10,
    agility INT DEFAULT 10,
    intelligence INT DEFAULT 10,
    health_points INT DEFAULT 100,
    max_health INT DEFAULT 100,
    experience INT DEFAULT 0,
    image_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabela de batalhas
CREATE TABLE battles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    character1_id INT NOT NULL,
    character2_id INT NOT NULL,
    winner_id INT,
    battle_log TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (character1_id) REFERENCES characters(id) ON DELETE CASCADE,
    FOREIGN KEY (character2_id) REFERENCES characters(id) ON DELETE CASCADE,
    FOREIGN KEY (winner_id) REFERENCES characters(id) ON DELETE SET NULL
);
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE,
    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE levels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    level_name VARCHAR(100) NOT NULL,
    unlock_order INT UNIQUE  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO levels (level_name, unlock_order) VALUES
('Livello 1: L Inizio', 1),
('Livello 2: Sfide Crescenti', 2);

CREATE TABLE scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    level_id INT NOT NULL,
    time_seconds DECIMAL(10, 3) NOT NULL,
    coins_collected INT DEFAULT 0,
    submission_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (level_id) REFERENCES levels(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE user_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    level_id_completed INT NOT NULL,
    first_completion_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (user_id, level_id_completed),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (level_id_completed) REFERENCES levels(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
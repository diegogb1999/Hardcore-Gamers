-- Creación de la BBDD

CREATE DATABASE Hardcore_Gamers;
USE Hardcore_Gamers;

-- Tabla de Juegos
CREATE TABLE games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
	info TEXT,
    release_date DATE,
    developer VARCHAR(255),
    img VARCHAR(255),
    purchase_url VARCHAR(255),
    slug VARCHAR(255) UNIQUE,
    rating DECIMAL(3,2),
    trailer VARCHAR(255),
    achievements_amount INT
);

-- Tabla de Logros (cada logro pertenece a un juego)
CREATE TABLE achievements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT,
    title VARCHAR(255) NOT NULL,
    info TEXT,
    img VARCHAR(255),
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
);

-- Tabla de Usuarios
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    mail VARCHAR(255) NOT NULL,
    pass VARCHAR(255) NOT NULL
);

-- Tabla de Relación para Logros Desbloqueados por Usuarios
CREATE TABLE users_achievements (
    user_id INT,
    achievement_id INT,
    unlock_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, achievement_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE
);

-- Tabla de Géneros
CREATE TABLE genres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    img VARCHAR(255)
);

-- Tabla de Plataformas
CREATE TABLE platforms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    img VARCHAR(255)
);

-- Tabla de Relación para Juegos y Géneros (Muchos a Muchos)
CREATE TABLE games_genres (
    game_id INT,
    genre_id INT,
    PRIMARY KEY (game_id, genre_id),
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE CASCADE
);

-- Tabla de Relación para Juegos y Plataformas (Muchos a Muchos)
CREATE TABLE games_platforms (
    game_id INT,
    platform_id INT,
    PRIMARY KEY (game_id, platform_id),
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    FOREIGN KEY (platform_id) REFERENCES platforms(id) ON DELETE CASCADE
);

-- Eliminar base de datos
-- DROP DATABASE hardcore_gamers;

DELIMITER //

-- Procedimiento para insertar un juego
CREATE PROCEDURE InsertGame(
    IN p_title VARCHAR(255),
    IN p_info TEXT,
    IN p_release_date DATE,
    IN p_developer VARCHAR(255),
    IN p_img VARCHAR(255),
    IN p_purchase_url VARCHAR(255),
    IN p_slug VARCHAR(255),
    IN p_rating DECIMAL(3,2),
    IN p_trailer VARCHAR(255),
    IN p_achievements_amount INT,
    OUT p_game_id INT
)
BEGIN
    INSERT INTO games (title, info, release_date, developer, img, purchase_url, slug, rating, trailer, achievements_amount)
    VALUES (p_title, p_info, p_release_date, p_developer, p_img, p_purchase_url, p_slug, p_rating, p_trailer, p_achievements_amount);
    
    SET p_game_id = LAST_INSERT_ID();
END //

-- Procedimiento para insertar un género
CREATE PROCEDURE InsertGenre(
    IN p_title VARCHAR(255),
    IN p_slug VARCHAR(255),
    IN p_img VARCHAR(255),
    OUT p_genre_id INT
)
BEGIN
    INSERT IGNORE INTO genres (title, slug, img) VALUES (p_title, p_slug, p_img);
    SET p_genre_id = LAST_INSERT_ID();
    
    IF p_genre_id = 0 THEN
        SELECT id INTO p_genre_id FROM genres WHERE slug = p_slug;
    END IF;
END //

-- Procedimiento para asociar un juego con un género
CREATE PROCEDURE InsertGameGenre(
    IN p_game_id INT,
    IN p_genre_id INT
)
BEGIN
    INSERT IGNORE INTO games_genres (game_id, genre_id)
    VALUES (p_game_id, p_genre_id);

END //

-- Procedimiento para insertar una plataforma
CREATE PROCEDURE InsertPlatform(
    IN p_title VARCHAR(255),
    IN p_slug VARCHAR(255),
    IN p_img VARCHAR(255),
    OUT p_platform_id INT
)
BEGIN
    INSERT IGNORE INTO platforms (title, slug, img) VALUES (p_title, p_slug, p_img);
    SET p_platform_id = LAST_INSERT_ID();
    
    IF p_platform_id = 0 THEN
        SELECT id INTO p_platform_id FROM platforms WHERE slug = p_slug;
    END IF;
END //

-- Procedimiento para asociar un juego con una plataforma
CREATE PROCEDURE InsertGamePlatform(
    IN p_game_id INT,
    IN p_platform_id INT
)
BEGIN
    INSERT IGNORE INTO games_platforms (game_id, platform_id)
    VALUES (p_game_id, p_platform_id);
END //

-- Procedimiento para insertar un logro
CREATE PROCEDURE InsertAchievement(
    IN p_game_id INT,
    IN p_title VARCHAR(255),
    IN p_info TEXT,
    IN p_img VARCHAR(255)
)
BEGIN
    INSERT INTO achievements (game_id, title, info, img)
    VALUES (p_game_id, p_title, p_info, p_img);
END //

DELIMITER ;



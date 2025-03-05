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
    -- Intentar obtener el ID si el juego ya existe
    SELECT id INTO p_game_id FROM games WHERE slug = p_slug;

    -- Si el juego no existe, insertarlo
    IF p_game_id IS NULL THEN
        INSERT INTO games (title, info, release_date, developer, img, purchase_url, slug, rating, trailer, achievements_amount)
        VALUES (p_title, p_info, p_release_date, p_developer, p_img, p_purchase_url, p_slug, p_rating, p_trailer, p_achievements_amount);

        SET p_game_id = LAST_INSERT_ID();
    ELSE
        -- Si el juego ya existía, actualizar sus datos
        UPDATE games 
        SET title = p_title, info = p_info, release_date = p_release_date, developer = p_developer,
            img = p_img, purchase_url = p_purchase_url, rating = p_rating, trailer = p_trailer, 
            achievements_amount = p_achievements_amount
        WHERE id = p_game_id;
    END IF;
END //

-- Procedimiento para insertar un género
CREATE PROCEDURE InsertGenre(
    IN p_title VARCHAR(255),
    IN p_slug VARCHAR(255),
    IN p_img VARCHAR(255),
    OUT p_genre_id INT
)
BEGIN
    -- Intentar obtener el ID si el género ya existe
    SELECT id INTO p_genre_id FROM genres WHERE slug = p_slug;

    -- Si el género no existe, insertarlo
    IF p_genre_id IS NULL THEN
        INSERT INTO genres (title, slug, img) 
        VALUES (p_title, p_slug, p_img);

        SET p_genre_id = LAST_INSERT_ID();
    ELSE
        -- Si el género ya existía, actualizar su información
        UPDATE genres SET img = p_img WHERE id = p_genre_id;
    END IF;
END //

-- Procedimiento para asociar un juego con un género
CREATE PROCEDURE InsertGameGenre(
    IN p_game_id INT,
    IN p_genre_id INT
)
BEGIN
    INSERT INTO games_genres (game_id, genre_id)
    VALUES (p_game_id, p_genre_id)
    ON DUPLICATE KEY UPDATE game_id = VALUES(game_id), genre_id = VALUES(genre_id);
END //

-- Procedimiento para insertar una plataforma
CREATE PROCEDURE InsertPlatform(
    IN p_title VARCHAR(255),
    IN p_slug VARCHAR(255),
    IN p_img VARCHAR(255),
    OUT p_platform_id INT
)
BEGIN
    -- Intentar obtener el ID si la plataforma ya existe
    SELECT id INTO p_platform_id FROM platforms WHERE slug = p_slug;

    -- Si la plataforma no existe, insertarla
    IF p_platform_id IS NULL THEN
        INSERT INTO platforms (title, slug, img) 
        VALUES (p_title, p_slug, p_img);

        SET p_platform_id = LAST_INSERT_ID();
    ELSE
        -- Si la plataforma ya existía, actualizar su información
        UPDATE platforms SET img = p_img WHERE id = p_platform_id;
    END IF;
END //

-- Procedimiento para asociar un juego con una plataforma
CREATE PROCEDURE InsertGamePlatform(
    IN p_game_id INT,
    IN p_platform_id INT
)
BEGIN
    INSERT INTO games_platforms (game_id, platform_id)
    VALUES (p_game_id, p_platform_id)
    ON DUPLICATE KEY UPDATE game_id = VALUES(game_id), platform_id = VALUES(platform_id);
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
    VALUES (p_game_id, p_title, p_info, p_img)
    ON DUPLICATE KEY UPDATE 
        info = VALUES(info),
        img = VALUES(img);
END //

DELIMITER ;
